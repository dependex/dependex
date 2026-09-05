<?php
declare(strict_types=1);

require_once __DIR__ . '/CommerceEnv.php';

/**
 * PAYPAL REST API V2 CLIENT SERVICE
 * Handles OAuth2 Tokens, Order Creation, Server-Side Order Capture, Verification & Webhooks
 */
class PayPalService {
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private string $currency;
    private ?string $webhookId;

    public function __construct() {
        CommerceEnv::load();
        $this->clientId = (string)CommerceEnv::get('PAYPAL_CLIENT_ID', '');
        $this->clientSecret = (string)CommerceEnv::get('PAYPAL_CLIENT_SECRET', '');
        $this->baseUrl = CommerceEnv::getPayPalBaseUrl();
        $this->currency = (string)CommerceEnv::get('PAYPAL_CURRENCY', 'EUR');
        $this->webhookId = CommerceEnv::get('PAYPAL_WEBHOOK_ID', null);
    }

    public function getClientId(): string {
        return $this->clientId;
    }

    public function isConfigured(): bool {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    private function getCaBundlePath(): ?string {
        $path = dirname(__DIR__, 2) . '/data/cacert.pem';
        return file_exists($path) ? realpath($path) : null;
    }

    /**
     * Retrieve or refresh an OAuth2 Bearer Access Token with TTL caching
     */
    public function getAccessToken(): string {
        if (!$this->isConfigured()) {
            throw new RuntimeException("Credenziali PayPal non configurate nel file .env.");
        }

        static $cachedToken = null;
        static $expiryTime = 0;

        $now = time();
        if ($cachedToken && $now < ($expiryTime - 120)) {
            return $cachedToken;
        }

        // Check file cache in data/
        $cacheFile = dirname(__DIR__, 2) . '/data/paypal_token_cache.json';
        if (file_exists($cacheFile)) {
            $data = json_decode((string)file_get_contents($cacheFile), true);
            if (is_array($data) && !empty($data['token']) && ($data['expires_at'] ?? 0) > ($now + 120)) {
                $cachedToken = $data['token'];
                $expiryTime = $data['expires_at'];
                return $cachedToken;
            }
        }

        $verifyPeer = CommerceEnv::getBool('COMMERCE_CURL_VERIFY_PEER', true);

        $url = $this->baseUrl . '/v1/oauth2/token';
        $ch = curl_init($url);
        $opts = [
            CURLOPT_USERPWD => $this->clientId . ':' . $this->clientSecret,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: en_US'
            ],
            CURLOPT_SSL_VERIFYPEER => $verifyPeer
        ];

        if ($verifyPeer && ($ca = $this->getCaBundlePath())) {
            $opts[CURLOPT_CAINFO] = $ca;
        }

        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new RuntimeException("Errore autenticazione PayPal OAuth2 [HTTP {$httpCode}]: {$curlErr} {$response}");
        }

        $json = json_decode((string)$response, true);
        if (empty($json['access_token'])) {
            throw new RuntimeException("Risposta PayPal non valida: token mancante.");
        }

        $cachedToken = $json['access_token'];
        $expiresIn = (int)($json['expires_in'] ?? 3600);
        $expiryTime = $now + $expiresIn;

        @file_put_contents($cacheFile, json_encode([
            'token' => $cachedToken,
            'expires_at' => $expiryTime,
            'created_at' => date('c')
        ]));

        return $cachedToken;
    }

    /**
     * Create PayPal Order v2 (intent: CAPTURE)
     */
    public function createOrder(array $orderData): array {
        $token = $this->getAccessToken();
        $url = $this->baseUrl . '/v2/checkout/orders';

        $totalAmount = number_format((float)$orderData['total_amount'], 2, '.', '');
        $subtotal = number_format((float)($orderData['subtotal'] ?? $orderData['total_amount']), 2, '.', '');
        $taxTotal = number_format((float)($orderData['vat_amount'] ?? 0.0), 2, '.', '');
        $discount = number_format((float)($orderData['discount_amount'] ?? 0.0), 2, '.', '');
        $currency = strtoupper($orderData['currency'] ?? $this->currency);

        $items = [];
        if (!empty($orderData['items'])) {
            foreach ($orderData['items'] as $it) {
                $items[] = [
                    'name' => mb_substr((string)$it['name'], 0, 127),
                    'quantity' => (string)($it['quantity'] ?? 1),
                    'unit_amount' => [
                        'currency_code' => $currency,
                        'value' => number_format((float)$it['unit_price'], 2, '.', '')
                    ]
                ];
            }
        }

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $orderData['order_number'],
                    'description' => mb_substr((string)($orderData['description'] ?? 'Ordine ' . $orderData['order_number']), 0, 127),
                    'custom_id' => json_encode([
                        'order_id' => $orderData['order_id'],
                        'order_number' => $orderData['order_number'],
                        'business_id' => $orderData['business_id'] ?? 'biz_dependex'
                    ]),
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $totalAmount,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $currency,
                                'value' => $subtotal
                            ]
                        ]
                    ]
                ]
            ],
            'application_context' => [
                'brand_name' => mb_substr((string)($orderData['brand_name'] ?? 'DEPENDEX Universe'), 0, 127),
                'locale' => 'it-IT',
                'landing_page' => 'NO_PREFERENCE',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
                'return_url' => $orderData['return_url'] ?? (CommerceEnv::get('COMMERCE_BASE_URL') . '/order-confirmation.php'),
                'cancel_url' => $orderData['cancel_url'] ?? (CommerceEnv::get('COMMERCE_BASE_URL') . '/cart.php?status=cancelled')
            ]
        ];

        if (!empty($items)) {
            $payload['purchase_units'][0]['items'] = $items;
        }

        if ((float)$taxTotal > 0) {
            $payload['purchase_units'][0]['amount']['breakdown']['tax_total'] = [
                'currency_code' => $currency,
                'value' => $taxTotal
            ];
        }

        if ((float)$discount > 0) {
            $payload['purchase_units'][0]['amount']['breakdown']['discount'] = [
                'currency_code' => $currency,
                'value' => $discount
            ];
        }

        $response = $this->apiCall('POST', $url, $payload, $token);
        if (empty($response['id'])) {
            throw new RuntimeException("Creazione ordine PayPal fallita: " . json_encode($response));
        }

        return $response;
    }

    /**
     * Server-side capture of a previously approved PayPal Order
     */
    public function captureOrder(string $paypalOrderId): array {
        $token = $this->getAccessToken();
        $url = $this->baseUrl . "/v2/checkout/orders/{$paypalOrderId}/capture";

        $response = $this->apiCall('POST', $url, new stdClass(), $token);
        return $response;
    }

    /**
     * Get detailed status of a PayPal Order
     */
    public function getOrderDetails(string $paypalOrderId): array {
        $token = $this->getAccessToken();
        $url = $this->baseUrl . "/v2/checkout/orders/{$paypalOrderId}";

        return $this->apiCall('GET', $url, null, $token);
    }

    /**
     * Verify official PayPal Webhook Signature
     */
    public function verifyWebhookSignature(array $headers, string $body): bool {
        if (empty($this->webhookId)) {
            return true; // Webhook id not configured yet, skip signature check in dev/initial setup
        }

        try {
            $token = $this->getAccessToken();
            $url = $this->baseUrl . '/v1/notifications/verify-webhook-signature';

            $payload = [
                'auth_algo' => $headers['PAYPAL-AUTH-ALGO'] ?? $headers['paypal-auth-algo'] ?? '',
                'cert_url' => $headers['PAYPAL-CERT-URL'] ?? $headers['paypal-cert-url'] ?? '',
                'transmission_id' => $headers['PAYPAL-TRANSMISSION-ID'] ?? $headers['paypal-transmission-id'] ?? '',
                'transmission_sig' => $headers['PAYPAL-TRANSMISSION-SIG'] ?? $headers['paypal-transmission-sig'] ?? '',
                'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? $headers['paypal-transmission-time'] ?? '',
                'webhook_id' => $this->webhookId,
                'webhook_event' => json_decode($body, true)
            ];

            $res = $this->apiCall('POST', $url, $payload, $token);
            return ($res['verification_status'] ?? '') === 'SUCCESS';
        } catch (Throwable $e) {
            error_log("Webhook verification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generic PayPal REST API cURL dispatcher
     */
    private function apiCall(string $method, string $url, mixed $payload = null, ?string $token = null): array {
        $ch = curl_init($url);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'PayPal-Request-Id: ' . bin2hex(random_bytes(16))
        ];

        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $opts = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => CommerceEnv::getBool('COMMERCE_CURL_VERIFY_PEER', true)
        ];

        if (CommerceEnv::getBool('COMMERCE_CURL_VERIFY_PEER', true) && ($ca = $this->getCaBundlePath())) {
            $opts[CURLOPT_CAINFO] = $ca;
        }

        if ($payload !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $opts[CURLOPT_POSTFIELDS] = is_string($payload) ? $payload : json_encode($payload);
        }

        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($res === false) {
            throw new RuntimeException("cURL PayPal error ({$code}): {$err}");
        }

        $decoded = json_decode((string)$res, true);
        if ($decoded === null && !empty($res)) {
            throw new RuntimeException("PayPal response parsing failed: " . $res);
        }

        return $decoded ?? [];
    }
}

if (!class_exists('Dependex\\Commerce\\PayPalService', false)) {
    class_alias('PayPalService', 'Dependex\\Commerce\\PayPalService');
}

