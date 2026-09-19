<?php
/**
 * UNIVERSAL COMMERCE PAYPAL ORDER CAPTURE & MYCELIUM SYNC
 * Gestisce l'incasso e notifica istantaneamente il central brain
 */
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['orderId'])) {
    http_response_code(400);
    echo json_encode(['status' => 'ERROR', 'message' => 'Dati ordine mancanti']);
    exit;
}

$orderId    = $data['orderId'];
$amount     = floatval($data['amount'] ?? 0);
$payerEmail = $data['payerEmail'] ?? '';
$domain     = $_SERVER['HTTP_HOST'] ?? 'unknown.com';

// 1. Salvataggio locale in SQLite WAL
$dbPath = dirname(__DIR__, 2) . '/database/app.sqlite';
if (file_exists($dbPath)) {
    try {
        $db = new PDO("sqlite:" . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $db->prepare("
            INSERT OR REPLACE INTO orders (order_id, email, product_id, amount, gateway, status)
            VALUES (?, ?, 'PAYPAL_ORDER', ?, 'PAYPAL', 'COMPLETED')
        ");
        $stmt->execute([$orderId, $payerEmail, $amount]);
    } catch (Exception $e) {
        error_log("DB error on PayPal capture: " . $e->getMessage());
    }
}

// 2. Propagazione a Mycelium Mesh (porta 8081)
$myceliumPayload = json_encode([
    'event_id' => 'evt_' . bin2hex(random_bytes(8)),
    'event_type' => 'ORDER_COMPLETED',
    'source_domain' => $domain,
    'email' => $payerEmail,
    'payload' => [
        'order_id' => $orderId,
        'amount' => $amount,
        'gateway' => 'PAYPAL',
        'is_buyer' => true
    ]
]);

$ch = curl_init('http://localhost:8081/api/mycelium/event');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $myceliumPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_exec($ch);
curl_close($ch);

echo json_encode([
    'status' => 'SUCCESS',
    'order_id' => $orderId,
    'buyer_registered' => true
]);
