<?php
/**
 * DEPENDEX & MIRCO UNIVERSE — PAYPAL WEBHOOK HANDLER
 * Cryptographically verifies PayPal signatures and processes payment state changes idempotently.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/modules/commerce/UniversalCommerce.php';

use Dependex\Commerce\UniversalCommerce;
use Dependex\Commerce\PayPalService;
use Dependex\Commerce\CommerceEnv;

header('Content-Type: application/json; charset=utf-8');

$rawPayload = file_get_contents('php://input');
if (!$rawPayload) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Empty payload']);
    exit;
}

$headers = getallheaders();
$webhookId = CommerceEnv::get('PAYPAL_WEBHOOK_ID', '');

$paypalService = new PayPalService();
$commerce = UniversalCommerce::getInstance();

// Signature verification if webhook ID is configured
if ($webhookId) {
    $isValid = $paypalService->verifyWebhookSignature($headers, $rawPayload, $webhookId);
    if (!$isValid) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid webhook signature']);
        exit;
    }
}

$event = json_decode($rawPayload, true);
if (!$event || empty($event['event_type'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Malformed JSON event']);
    exit;
}

$eventId = $event['id'] ?? ('evt_' . hash('sha256', $rawPayload));
$eventType = $event['event_type'] ?? 'UNKNOWN';
$resource = $event['resource'] ?? [];

try {
    // Check idempotency in commerce_payment_events
    $db = $commerce->getDb();
    $stmt = $db->prepare("SELECT id FROM commerce_payment_events WHERE external_event_id = ?");
    $stmt->execute([$eventId]);
    if ($stmt->fetch()) {
        // Already processed
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'message' => 'Event already processed']);
        exit;
    }

    // Process event types
    switch ($eventType) {
        case 'PAYMENT.CAPTURE.COMPLETED':
            $captureId = $resource['id'] ?? null;
            $customId = $resource['custom_id'] ?? null; // Internal Order ID
            $amount = (float)($resource['amount']['value'] ?? 0);
            $currency = $resource['amount']['currency_code'] ?? 'EUR';

            if ($customId) {
                $order = $commerce->getOrder($customId);
                if ($order && $order['payment_status'] !== 'PAID') {
                    // Update order and trigger fulfillment
                    $commerce->recordPaymentSuccess($order['id'], $captureId, $amount, $currency, $resource);
                    
                    // Notifica Marketing Automation OS
                    require_once __DIR__ . '/email-engine.php';
                    $customerEmail = $order['customer_email'] ?? ($resource['payer']['email_address'] ?? '');
                    if ($customerEmail) {
                        email_os_track_event('order_completed', $customerEmail, [
                            'order_id' => $order['id'],
                            'value' => $amount,
                            'currency' => $currency,
                            'provider' => 'paypal'
                        ]);
                    }
                }
            }
            break;

        case 'PAYMENT.CAPTURE.REFUNDED':
            $captureId = $resource['id'] ?? null;
            $customId = $resource['custom_id'] ?? null;
            if ($customId) {
                $stmtUpdate = $db->prepare("UPDATE commerce_orders SET status = 'REFUNDED', payment_status = 'REFUNDED', updated_at = datetime('now') WHERE id = ?");
                $stmtUpdate->execute([$customId]);
                $commerce->logAudit('ORDER_REFUNDED', 'Order refunded via PayPal', 'commerce_orders', $customId, $resource);
            }
            break;

        case 'PAYMENT.CAPTURE.DENIED':
            $customId = $resource['custom_id'] ?? null;
            if ($customId) {
                $stmtUpdate = $db->prepare("UPDATE commerce_orders SET status = 'FAILED', payment_status = 'FAILED', updated_at = datetime('now') WHERE id = ?");
                $stmtUpdate->execute([$customId]);
                $commerce->logAudit('PAYMENT_DENIED', 'Payment denied by PayPal', 'commerce_orders', $customId, $resource);
            }
            break;

        case 'BILLING.SUBSCRIPTION.ACTIVATED':
        case 'BILLING.SUBSCRIPTION.CANCELLED':
        case 'BILLING.SUBSCRIPTION.EXPIRED':
            $subId = $resource['id'] ?? null;
            $status = $resource['status'] ?? 'UNKNOWN';
            if ($subId) {
                $stmtSub = $db->prepare("UPDATE commerce_subscriptions SET status = ?, updated_at = datetime('now') WHERE paypal_subscription_id = ?");
                $stmtSub->execute([strtoupper($status), $subId]);
                $commerce->logAudit('SUBSCRIPTION_STATUS_CHANGE', "Subscription status changed to $status", 'commerce_subscriptions', $subId, $resource);
            }
            break;
    }

    // Record processed event
    $stmtEvent = $db->prepare("
        INSERT INTO commerce_payment_events (id, payment_id, event_type, external_event_id, payload, created_at)
        VALUES (?, ?, ?, ?, ?, datetime('now'))
    ");
    $stmtEvent->execute([
        'pevt_' . bin2hex(random_bytes(8)),
        $resource['id'] ?? null,
        $eventType,
        $eventId,
        json_encode($event)
    ]);

    http_response_code(200);
    echo json_encode(['status' => 'ok', 'processed' => true]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
