<?php
declare(strict_types=1);

/**
 * UNIVERSAL FULFILLMENT SERVICE
 * Manages digital product delivery, access token issuance, onboarding dispatch, and email delivery
 */
class FulfillmentService {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function fulfillOrder(array|string $orderOrId): array {
        $orderId = is_array($orderOrId) ? ($orderOrId['id'] ?? '') : $orderOrId;
        return $this->fulfill($orderId);
    }

    public function fulfill(string $orderId): array {
        $st = $this->db->prepare("SELECT o.*, c.email customer_email, c.first_name, c.last_name FROM commerce_orders o JOIN commerce_customers c ON o.customer_id = c.id WHERE o.id = ?");
        $st->execute([$orderId]);
        $order = $st->fetch();

        if (!$order) {
            return ['ok' => false, 'error' => 'Ordine non trovato'];
        }

        if ($order['fulfillment_status'] === 'FULFILLED') {
            return ['ok' => true, 'already_fulfilled' => true];
        }

        // Fetch items
        $itSt = $this->db->prepare("SELECT oi.*, p.fulfillment_type, p.product_type, p.metadata_json FROM commerce_order_items oi LEFT JOIN commerce_products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $itSt->execute([$orderId]);
        $items = $itSt->fetchAll();

        $deliveries = [];
        foreach ($items as $item) {
            $fType = $item['fulfillment_type'] ?? 'ACCESS_GRANT';
            $accessToken = bin2hex(random_bytes(20));
            $fulId = 'ful_' . bin2hex(random_bytes(12));

            $content = [
                'item_name' => $item['product_name'],
                'access_token' => $accessToken,
                'instructions' => 'Accesso immediato sbloccato per ' . $item['product_name'],
                'url' => '/app.php?access=' . $accessToken
            ];

            // If user exists in users table, award DRX credits!
            if (!empty($order['customer_email']) && function_exists('drx_post')) {
                $uSt = $this->db->prepare("SELECT sic_id FROM users WHERE email = ?");
                $uSt->execute([$order['customer_email']]);
                $userSic = $uSt->fetchColumn();
                if ($userSic) {
                    $drxReward = (float)($order['total_amount'] * 2); // 2 DRX for every € spent!
                    try {
                        drx_post($userSic, null, $drxReward, 'ORDER_PURCHASE', true, 'order_drx:' . $orderId, null, [
                            'order_number' => $order['order_number'],
                            'amount_eur' => $order['total_amount']
                        ]);
                        $content['drx_awarded'] = $drxReward;
                    } catch (Throwable $e) {
                        error_log("DRX reward error: " . $e->getMessage());
                    }
                }
            }

            $ins = $this->db->prepare("INSERT INTO commerce_fulfillments(id, order_id, fulfillment_type, status, access_token, content_json, fulfilled_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP)");
            $ins->execute([$fulId, $orderId, $fType, 'SUCCESS', $accessToken, json_encode($content, JSON_UNESCAPED_UNICODE)]);

            $deliveries[] = $content;
        }

        // Mark order fulfilled
        $upd = $this->db->prepare("UPDATE commerce_orders SET fulfillment_status = 'FULFILLED', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $upd->execute([$orderId]);

        // Send confirmation email / notification log
        $this->sendConfirmationNotification($order, $items, $deliveries);

        return [
            'ok' => true,
            'order_number' => $order['order_number'],
            'deliveries' => $deliveries
        ];
    }

    private function sendConfirmationNotification(array $order, array $items, array $deliveries): void {
        $to = $order['customer_email'];
        $subject = "Conferma Ordine {$order['order_number']} - DEPENDEX Commerce";
        
        $itemLines = [];
        foreach ($items as $it) {
            $itemLines[] = "• " . $it['product_name'] . " x " . $it['quantity'] . " - € " . number_format((float)$it['line_total'], 2);
        }

        $body = "Gentile " . ($order['first_name'] ?: 'Cliente') . ",\n\n"
              . "Grazie per il tuo acquisto su DEPENDEX Universe!\n\n"
              . "Dettagli Ordine:\n"
              . "Numero Ordine: {$order['order_number']}\n"
              . "Data: " . date('d/m/Y H:i') . "\n"
              . "Totale Verificato: € " . number_format((float)$order['total_amount'], 2) . " {$order['currency']}\n\n"
              . "Articoli Acquistati:\n"
              . implode("\n", $itemLines) . "\n\n"
              . "I tuoi accessi digitali sono immediatamente attivi sulla piattaforma:\n"
              . CommerceEnv::get('COMMERCE_BASE_URL') . "/order-confirmation.php?order=" . urlencode($order['order_number']) . "\n\n"
              . "Team DEPENDEX & Mirco Pregnolato Universe\n"
              . "Supporto: info@dependex.social\n";

        // Try sending via mail() if available or log
        @mail($to, $subject, $body, "From: " . CommerceEnv::get('APP_EMAIL', 'info@dependex.social'));
        error_log("[COMMERCE_EMAIL_SENT] Order {$order['order_number']} to {$to}");

        // Real-time Push to Telegram / WhatsApp Admin Channels
        $this->sendInstantAdminAlerts($order, $items);
    }

    private function sendInstantAdminAlerts(array $order, array $items): void {
        $telegramToken = CommerceEnv::get('TELEGRAM_BOT_TOKEN', '');
        $telegramChatId = CommerceEnv::get('TELEGRAM_ADMIN_CHAT_ID', '');
        $whatsappWebhook = CommerceEnv::get('WHATSAPP_WEBHOOK_URL', '');

        $customerName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')) ?: 'Ospite';
        $summary = "💰 *NUOVO ORDINE CONFERMATO*\n\n"
                 . "📦 *Ordine:* `{$order['order_number']}`\n"
                 . "👤 *Cliente:* {$customerName} ({$order['customer_email']})\n"
                 . "💶 *Importo:* € " . number_format((float)$order['total_amount'], 2) . " {$order['currency']}\n"
                 . "🌐 *Origine:* " . ($order['source_domain'] ?? 'dependex.social') . "\n\n"
                 . "*Articoli:*\n";

        foreach ($items as $it) {
            $summary .= "• {$it['product_name']} x {$it['quantity']}\n";
        }

        // 1. Telegram Push
        if (!empty($telegramToken) && !empty($telegramChatId)) {
            try {
                $tgUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
                $tgData = json_encode([
                    'chat_id' => $telegramChatId,
                    'text' => $summary,
                    'parse_mode' => 'Markdown'
                ]);
                $ctx = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $tgData,
                        'timeout' => 5
                    ]
                ]);
                @file_get_contents($tgUrl, false, $ctx);
                error_log("[TELEGRAM_ALERT_SENT] Order {$order['order_number']}");
            } catch (\Throwable $e) {
                error_log("[TELEGRAM_ALERT_FAIL] " . $e->getMessage());
            }
        }

        // 2. WhatsApp / Webhook Notification
        if (!empty($whatsappWebhook)) {
            try {
                $waData = json_encode([
                    'event' => 'commerce.order_paid',
                    'order_number' => $order['order_number'],
                    'customer_email' => $order['customer_email'],
                    'total' => $order['total_amount'],
                    'currency' => $order['currency'],
                    'message' => $summary
                ]);
                $ctx = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/json\r\n",
                        'content' => $waData,
                        'timeout' => 5
                    ]
                ]);
                @file_get_contents($whatsappWebhook, false, $ctx);
                error_log("[WHATSAPP_WEBHOOK_SENT] Order {$order['order_number']}");
            } catch (\Throwable $e) {
                error_log("[WHATSAPP_WEBHOOK_FAIL] " . $e->getMessage());
            }
        }
    }
}

if (!class_exists('Dependex\\Commerce\\FulfillmentService', false)) {
    class_alias('FulfillmentService', 'Dependex\\Commerce\\FulfillmentService');
}

