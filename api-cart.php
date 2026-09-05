<?php
/**
 * DEPENDEX & MIRCO UNIVERSE — UNIVERSAL COMMERCE CART API
 * Multi-domain REST endpoint for cart state, items, quantity, coupons.
 * Strict server-side pricing: NEVER trusts client prices.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Multi-Domain CORS handling
$allowedOrigins = [
    'https://dependex.social',
    'https://mircopregnolato.it',
    'https://oltre.social',
    'https://mywallet.business',
    'https://betterway.agency',
    'https://neuralog.pro',
    'http://localhost',
    'http://127.0.0.1',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    $parsedOrigin = parse_url($origin, PHP_URL_HOST);
    $matched = false;
    foreach ($allowedOrigins as $ao) {
        if ($origin === $ao || ($parsedOrigin && str_ends_with($parsedOrigin, parse_url($ao, PHP_URL_HOST) ?? ''))) {
            $matched = true;
            break;
        }
    }
    if ($matched) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-DX-Cart-Id');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/modules/commerce/UniversalCommerce.php';

use Dependex\Commerce\UniversalCommerce;

try {
    $commerce = UniversalCommerce::getInstance();

    // Resolve cart session token from cookie, header, or query
    $cartToken = $_COOKIE['dx_cart_id'] ?? $_SERVER['HTTP_X_DX_CART_ID'] ?? ($_GET['cart_id'] ?? null);
    $cart = $commerce->getOrCreateCart($cartToken);

    // Persist cart token in cookie (valid 30 days, secure, samesite none if cross-origin or lax)
    if (!isset($_COOKIE['dx_cart_id']) || $_COOKIE['dx_cart_id'] !== $cart['id']) {
        setcookie('dx_cart_id', $cart['id'], [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => false, // readable by JS SDK if needed
            'samesite' => 'Lax'
        ]);
    }

    $input = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $input = json_decode($raw, true) ?? [];
        } else {
            $input = $_POST;
        }
    }

    $action = $_GET['action'] ?? ($input['action'] ?? 'get');

    switch ($action) {
        case 'get':
            $cartData = $commerce->getCart($cart['id']);
            echo json_encode([
                'success' => true,
                'cart' => $cartData
            ]);
            break;

        case 'add':
            $offerId = trim((string)($input['offer_id'] ?? ''));
            $quantity = max(1, (int)($input['quantity'] ?? 1));
            $customMeta = is_array($input['metadata'] ?? null) ? $input['metadata'] : [];
            
            // Capture attribution if provided
            if (!empty($input['source_domain'])) {
                $customMeta['source_domain'] = trim((string)$input['source_domain']);
            }
            if (!empty($input['utm_source'])) {
                $customMeta['utm_source'] = trim((string)$input['utm_source']);
            }
            if (!empty($input['utm_campaign'])) {
                $customMeta['utm_campaign'] = trim((string)$input['utm_campaign']);
            }
            if (!empty($input['utm_medium'])) {
                $customMeta['utm_medium'] = trim((string)$input['utm_medium']);
            }

            if (!$offerId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'offer_id is required']);
                exit;
            }

            $item = $commerce->addToCart($cart['id'], $offerId, $quantity, $customMeta);
            $cartData = $commerce->getCart($cart['id']);

            echo json_encode([
                'success' => true,
                'message' => 'Item added to cart',
                'item' => $item,
                'cart' => $cartData
            ]);
            break;

        case 'update':
            $itemId = trim((string)($input['item_id'] ?? ''));
            $quantity = (int)($input['quantity'] ?? 1);

            if (!$itemId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'item_id is required']);
                exit;
            }

            $commerce->updateCartItemQuantity($cart['id'], $itemId, $quantity);
            $cartData = $commerce->getCart($cart['id']);

            echo json_encode([
                'success' => true,
                'cart' => $cartData
            ]);
            break;

        case 'remove':
            $itemId = trim((string)($input['item_id'] ?? ''));
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'item_id is required']);
                exit;
            }

            $commerce->removeCartItem($cart['id'], $itemId);
            $cartData = $commerce->getCart($cart['id']);

            echo json_encode([
                'success' => true,
                'cart' => $cartData
            ]);
            break;

        case 'clear':
            $commerce->clearCart($cart['id']);
            $cartData = $commerce->getCart($cart['id']);

            echo json_encode([
                'success' => true,
                'cart' => $cartData
            ]);
            break;

        case 'apply_coupon':
            $couponCode = trim((string)($input['code'] ?? ''));
            if (!$couponCode) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Coupon code is required']);
                exit;
            }

            $res = $commerce->applyCoupon($cart['id'], $couponCode);
            $cartData = $commerce->getCart($cart['id']);

            if (!$res['success']) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'error' => $res['error'] ?? 'Codice coupon non valido o scaduto.',
                    'cart' => $cartData
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Coupon applicato con successo!',
                'discount' => $res['discount'] ?? 0,
                'cart' => $cartData
            ]);
            break;

        case 'remove_coupon':
            $commerce->removeCoupon($cart['id']);
            $cartData = $commerce->getCart($cart['id']);

            echo json_encode([
                'success' => true,
                'message' => 'Coupon rimosso',
                'cart' => $cartData
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
