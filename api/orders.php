<?php
/**
 * DEPENDEX & OLTRE — SECURE ORDER DISPATCH API
 * Creazione e gestione ordini server-side con validazione rigida dei prezzi,
 * emissione evento checkout_started e integrazione con pagamenti reali (PayPal / Bonifico).
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../email-engine.php';
require_once __DIR__ . '/../modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/../modules/commerce/UniversalCommerce.php';

use Dependex\Commerce\UniversalCommerce;
use Dependex\Commerce\CommerceEnv;

// Prezzi ufficiali immutabili verificati server-side
const TIERS_CATALOG = [
    'starter' => [
        'name' => 'LIV. 1 · Starter Kit & Diagnosi',
        'price' => 27.00,
        'currency' => 'EUR',
        'type' => 'one_time'
    ],
    'core' => [
        'name' => 'LIV. 2 · Protocollo Completo & Trasformazione',
        'price' => 497.00,
        'currency' => 'EUR',
        'type' => 'one_time'
    ],
    'elite' => [
        'name' => 'LIV. 3 · Programma Elite & Affiancamento',
        'price' => 1997.00,
        'currency' => 'EUR',
        'type' => 'one_time'
    ],
    'membership' => [
        'name' => 'LIV. 4 · Club Permanente & Supporto',
        'price' => 39.00,
        'currency' => 'EUR',
        'type' => 'recurring'
    ]
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = $rawInput ? json_decode($rawInput, true) : $_POST;

$tierKey = strtolower(trim((string)($data['tier'] ?? '')));
$name = trim((string)($data['name'] ?? ''));
$email = trim(strtolower((string)($data['email'] ?? '')));
$paymentMethod = strtolower(trim((string)($data['payment_method'] ?? 'card')));

if (!isset(TIERS_CATALOG[$tierKey])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Livello di offerta non valido']);
    exit;
}

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Indirizzo email valido obbligatorio']);
    exit;
}

$tier = TIERS_CATALOG[$tierKey];
$orderId = 'ord_' . bin2hex(random_bytes(8));
$amount = $tier['price'];
$currency = $tier['currency'];

try {
    $commerce = UniversalCommerce::getInstance();
    $db = $commerce->getDb();

    // 1. Risolvi o crea il cliente
    $customer = $commerce->resolveCustomer($email, [
        'first_name' => $name,
        'marketing_opt_in' => true
    ]);

    // 2. Crea l'ordine nello stato PENDING
    $stmt = $db->prepare("
        INSERT INTO commerce_orders (
            id, customer_id, cart_id, order_number, status, payment_status,
            subtotal, discount_amount, tax_amount, total_amount, currency,
            created_at, updated_at
        ) VALUES (
            ?, ?, 'cart_direct', ?, 'PENDING', 'UNPAID',
            ?, 0.0, 0.0, ?, ?,
            datetime('now'), datetime('now')
        )
    ");
    $stmt->execute([
        $orderId,
        $customer['id'],
        strtoupper(substr($orderId, 4)),
        $amount,
        $amount,
        $currency
    ]);

    // 3. Inserisci la voce dell'ordine
    $stmtItem = $db->prepare("
        INSERT INTO commerce_order_items (id, order_id, product_id, title, unit_price, quantity, total_price)
        VALUES (?, ?, ?, ?, ?, 1, ?)
    ");
    $stmtItem->execute([
        'item_' . bin2hex(random_bytes(6)),
        $orderId,
        $tierKey,
        $tier['name'],
        $amount,
        $amount
    ]);

    // 4. Traccia l'evento comportamentale nell'Email Revenue OS
    email_os_track_event('checkout_started', $email, [
        'order_id' => $orderId,
        'tier' => $tierKey,
        'tier_name' => $tier['name'],
        'amount' => $amount,
        'currency' => $currency,
        'payment_method' => $paymentMethod
    ]);

    // 5. Iscrivi il contatto consensuale nel database lead
    email_os_enroll_contact($email, $name, '', 'order_checkout');

    // 6. Gestione in base al metodo di pagamento
    if ($paymentMethod === 'bonifico') {
        $iban = CommerceEnv::get('BANK_IBAN', 'IT60X0542811101000000123456');
        $causale = "Attivazione {$tier['name']} - Ordine {$orderId}";
        
        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'payment_method' => 'bonifico',
            'amount' => $amount,
            'currency' => $currency,
            'instructions' => [
                'beneficiario' => 'DEPENDEX Ecosystem',
                'iban' => $iban,
                'causale' => $causale,
                'importo' => "€ {$amount}"
            ],
            'redirect_url' => "/order-confirmation.php?order_id={$orderId}&method=bonifico"
        ]);
        exit;
    }

    // Se carta / PayPal
    $originUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'dependex.social');
    $returnUrl = $originUrl . '/order-confirmation.php?order_id=' . urlencode($orderId);
    $cancelUrl = $originUrl . '/offers.php?cancel=1&order_id=' . urlencode($orderId);

    $paypalRes = $commerce->createPayPalOrderForInternalOrder($orderId, $returnUrl, $cancelUrl);

    if ($paypalRes['success']) {
        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'payment_method' => 'paypal',
            'approval_url' => $paypalRes['approval_url'],
            'paypal_order_id' => $paypalRes['paypal_order_id'],
            'redirect_url' => $paypalRes['approval_url'] ?: "/checkout.php?order_id={$orderId}"
        ]);
        exit;
    }

    // Fallback sicuro se PayPal non configurato
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'payment_method' => 'manual',
        'redirect_url' => "/order-confirmation.php?order_id={$orderId}&notice=pending_review"
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore elaborazione ordine: ' . $e->getMessage()
    ]);
}
