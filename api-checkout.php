<?php
/**
 * DEPENDEX & MIRCO UNIVERSE — UNIVERSAL CHECKOUT & PAYPAL API
 * Server-side payment initialization, verification and capture.
 * CRITICAL RULE: NEVER trust frontend totals or payment status without server-side verification.
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

    // Resolve cart
    $cartToken = $_COOKIE['dx_cart_id'] ?? $_SERVER['HTTP_X_DX_CART_ID'] ?? ($_GET['cart_id'] ?? null);
    $cart = $commerce->getOrCreateCart($cartToken);

    $raw = file_get_contents('php://input');
    $input = $raw ? (json_decode($raw, true) ?? []) : $_POST;

    $action = $_GET['action'] ?? ($input['action'] ?? '');

    switch ($action) {
        case 'create_paypal_order':
            // 1. Validate cart contents
            $cartData = $commerce->getCart($cart['id']);
            if (empty($cartData['items'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Il carrello è vuoto. Impossibile procedere al checkout.']);
                exit;
            }

            // 2. Validate Customer & Billing input
            $email = trim((string)($input['email'] ?? ''));
            $firstName = trim((string)($input['first_name'] ?? ''));
            $lastName = trim((string)($input['last_name'] ?? ''));
            $phone = trim((string)($input['phone'] ?? ''));
            
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Indirizzo email valido obbligatorio per l\'ordine.']);
                exit;
            }

            // Legal consent checks
            $privacy = !empty($input['privacy_accepted']);
            $terms = !empty($input['terms_accepted']);
            $marketing = !empty($input['marketing_accepted']);

            if (!$privacy || !$terms) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'È necessario accettare i Termini di Servizio e l\'Informativa Privacy per procedere.']);
                exit;
            }

            // Billing address & fiscal details
            $billingAddress = [
                'street' => trim((string)($input['billing_street'] ?? '')),
                'city' => trim((string)($input['billing_city'] ?? '')),
                'state' => trim((string)($input['billing_state'] ?? '')),
                'postal_code' => trim((string)($input['billing_postal_code'] ?? '')),
                'country' => strtoupper(trim((string)($input['billing_country'] ?? 'IT')))
            ];

            $fiscalData = [
                'company_name' => trim((string)($input['company_name'] ?? '')),
                'vat_number' => trim((string)($input['vat_number'] ?? '')),
                'fiscal_code' => trim((string)($input['fiscal_code'] ?? '')),
                'sdi_pec' => trim((string)($input['sdi_pec'] ?? '')),
            ];

            // 3. Resolve customer
            $customer = $commerce->resolveCustomer($email, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $phone,
                'billing_address' => $billingAddress,
                'fiscal_data' => $fiscalData,
                'marketing_opt_in' => $marketing
            ]);

            // 4. Create internal order
            $order = $commerce->createOrderFromCart($cart['id'], $customer['id'], [
                'billing_address' => $billingAddress,
                'fiscal_data' => $fiscalData,
                'privacy_accepted' => $privacy,
                'terms_accepted' => $terms,
                'marketing_accepted' => $marketing,
                'source_domain' => $input['source_domain'] ?? $cartData['source_domain'] ?? $_SERVER['HTTP_HOST'] ?? 'dependex.social',
                'utm_source' => $input['utm_source'] ?? null,
                'utm_campaign' => $input['utm_campaign'] ?? null,
                'utm_medium' => $input['utm_medium'] ?? null,
            ]);

            // 5. Create PayPal Order server-side
            $originUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'dependex.social');
            $returnUrl = $originUrl . '/order-confirmation.php?order_id=' . urlencode($order['id']);
            $cancelUrl = $originUrl . '/checkout.php?cancel=1&order_id=' . urlencode($order['id']);

            $paypalRes = $commerce->createPayPalOrderForInternalOrder($order['id'], $returnUrl, $cancelUrl);

            if (!$paypalRes['success']) {
                http_response_code(502);
                echo json_encode([
                    'success' => false,
                    'error' => 'Errore di inizializzazione PayPal: ' . ($paypalRes['error'] ?? 'Impossibile creare ordine PayPal.')
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'paypal_order_id' => $paypalRes['paypal_order_id'],
                'internal_order_id' => $order['id'],
                'order_number' => $order['order_number'],
                'total_amount' => $order['total_amount'],
                'currency' => $order['currency']
            ]);
            break;

        case 'capture_paypal_order':
            $paypalOrderId = trim((string)($input['paypal_order_id'] ?? ''));
            $internalOrderId = trim((string)($input['internal_order_id'] ?? ''));

            if (!$paypalOrderId || !$internalOrderId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'paypal_order_id e internal_order_id sono obbligatori.']);
                exit;
            }

            // Capture and verify on server
            $captureRes = $commerce->captureAndVerifyPayPalOrder($internalOrderId, $paypalOrderId);

            if (!$captureRes['success']) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Verifica pagamento fallita: ' . ($captureRes['error'] ?? 'Transazione non confermata da PayPal.')
                ]);
                exit;
            }

            // Clear the cart on successful payment
            $commerce->clearCart($cart['id']);

            echo json_encode([
                'success' => true,
                'message' => 'Pagamento verificato e ordine confermato!',
                'order_id' => $internalOrderId,
                'order_number' => $captureRes['order']['order_number'] ?? '',
                'redirect_url' => 'order-confirmation.php?order_id=' . urlencode($internalOrderId)
            ]);
            break;

        case 'get_order_status':
            $internalOrderId = trim((string)($input['internal_order_id'] ?? $_GET['order_id'] ?? ''));
            if (!$internalOrderId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'order_id is required']);
                exit;
            }

            $order = $commerce->getOrder($internalOrderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Order not found']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'order' => $order
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
