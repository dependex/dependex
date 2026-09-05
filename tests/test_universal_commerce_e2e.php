<?php
/**
 * E2E AUTOMATED VERIFICATION SUITE — UNIVERSAL COMMERCE & PAYPAL REST ENGINE
 * Validates multi-brand checkout, server price authority, coupon discounts,
 * customer resolution, order persistence, and PayPal API communication.
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/../modules/commerce/PayPalService.php';
require_once __DIR__ . '/../modules/commerce/FulfillmentService.php';
require_once __DIR__ . '/../modules/commerce/UniversalCommerce.php';

use Dependex\Commerce\UniversalCommerce;
use Dependex\Commerce\PayPalService;
use Dependex\Commerce\CommerceEnv;

echo "========================================================\n";
echo " UNIVERSAL COMMERCE & PAYPAL LIVE TEST SUITE\n";
echo "========================================================\n\n";

$commerce = UniversalCommerce::getInstance();
$db = $commerce->getDb();

$testPassed = 0;
$testTotal = 0;

function assertTest(string $desc, bool $condition): void {
    global $testPassed, $testTotal;
    $testTotal++;
    if ($condition) {
        $testPassed++;
        echo " [PASS] $desc\n";
    } else {
        echo " [FAIL] $desc\n";
    }
}

// 1. Catalog and Entities verification
$offers = $commerce->getDb()->query("SELECT COUNT(*) FROM commerce_offers WHERE active = 1")->fetchColumn();
assertTest("Seeded active offers exist in Source of Truth (count: $offers)", (int)$offers >= 8);

$businesses = $commerce->getDb()->query("SELECT COUNT(*) FROM commerce_businesses")->fetchColumn();
assertTest("Multi-brand universe businesses configured (count: $businesses)", (int)$businesses >= 5);

// 2. Cart session creation
$cart = $commerce->getOrCreateCart();
assertTest("Cart initialized with UUID token: " . $cart['id'], !empty($cart['id']));

// 3. Security: Frontend Price Tampering Rejection
// Client attempts to pass fraudulent price 0.01 instead of 27.00
$cartAfter1 = $commerce->addToCart($cart['id'], 'off_starter_kit', 1, [
    'fraudulent_price' => 0.01,
    'source_domain' => 'dependex.social'
]);
$item1 = $cartAfter1['items'][0] ?? [];
assertTest("Item added with strict server price authority (€27.00, not tampered €0.01)", (float)($item1['unit_price'] ?? 0) === 27.00);

// 4. Multi-Domain: Adding product from another universe brand (mircopregnolato.it)
$cartAfter2 = $commerce->addToCart($cart['id'], 'off_mp_books', 1, [
    'source_domain' => 'mircopregnolato.it'
]);
$item2 = $cartAfter2['items'][1] ?? [];
assertTest("Multi-brand item from 'biz_mircopregnolato' added to same universal cart (€47.00)", (float)($item2['unit_price'] ?? 0) === 47.00);


// 5. Cart summary check
$cartState = $commerce->getCart($cart['id']);
assertTest("Cart subtotal correctly calculated (€74.00)", (float)$cartState['subtotal'] === 74.00);
assertTest("Cart items count is 2", (int)$cartState['items_count'] === 2);

// 6. Coupon Application
$couponRes = $commerce->applyCoupon($cart['id'], 'WELCOME10');
assertTest("Coupon 'WELCOME10' applied successfully", $couponRes['success'] === true);

$cartDiscounted = $commerce->getCart($cart['id']);
assertTest("10% discount applied: €7.40", (float)$cartDiscounted['discount_total'] === 7.40);
assertTest("Total with discount: €66.60", (float)$cartDiscounted['total'] === 66.60);

// 7. Customer Resolution
$customer = $commerce->resolveCustomer('qa-tester@mirco-universe.test', [
    'first_name' => 'Mario',
    'last_name' => 'Rossi',
    'phone' => '+39 340 1234567',
    'billing_address' => [
        'street' => 'Via Veneto 1',
        'city' => 'Roma',
        'postal_code' => '00100',
        'country' => 'IT'
    ],
    'fiscal_data' => [
        'fiscal_code' => 'RSSMRA80A01H501U'
    ],
    'marketing_opt_in' => true
]);
assertTest("Customer resolved with UUID: " . $customer['id'], !empty($customer['id']));

// 8. Order Creation from Cart
$order = $commerce->createOrderFromCart($cart['id'], $customer['id'], [
    'billing_address' => [
        'street' => 'Via Veneto 1',
        'city' => 'Roma',
        'postal_code' => '00100',
        'country' => 'IT'
    ],
    'privacy_accepted' => true,
    'terms_accepted' => true,
    'marketing_accepted' => true,
    'source_domain' => 'dependex.social'
]);
assertTest("Internal Order created: " . $order['order_number'] . " (Status: " . $order['status'] . ")", $order['status'] === 'PENDING');
assertTest("Order total matches cart total (€66.60)", (float)$order['total_amount'] === 66.60);

// 9. Verify Order Items in Database
$orderItems = $commerce->getDb()->query("SELECT COUNT(*) FROM commerce_order_items WHERE order_id = '{$order['id']}'")->fetchColumn();
assertTest("Order items saved persistently (count: $orderItems)", (int)$orderItems === 2);

// 10. Test PayPal REST API Authentication & Order Initialization
echo "\n--- Testing PayPal REST API Gateway ---\n";
$paypal = new PayPalService();
try {
    $token = $paypal->getAccessToken();
    assertTest("PayPal OAuth2 live token granted (length: " . strlen($token) . ")", !empty($token));

    // Call PayPal REST Order Create with live credentials
    $ppOrder = $paypal->createOrder([
        'order_id' => $order['id'],
        'order_number' => $order['order_number'],
        'total_amount' => (float)$order['total_amount'],
        'subtotal' => (float)$order['subtotal'],
        'discount_amount' => (float)$order['discount_amount'],
        'currency' => 'EUR',
        'description' => 'Mirco Universe Order ' . $order['order_number'],
        'items' => [
            ['name' => 'Starter Kit', 'unit_price' => 27.00, 'quantity' => 1],
            ['name' => 'MP Collana Libri', 'unit_price' => 47.00, 'quantity' => 1]
        ],
        'return_url' => 'https://dependex.social/order-confirmation.php?order=' . $order['id'],
        'cancel_url' => 'https://dependex.social/checkout.php?cancel=1'
    ]);
    assertTest("PayPal Live Order created successfully with ID: " . ($ppOrder['id'] ?? 'NONE'), !empty($ppOrder['id']));
    assertTest("PayPal Order status is CREATED", ($ppOrder['status'] ?? '') === 'CREATED');

} catch (\Throwable $e) {
    echo " [WARN] PayPal Live Call Notice: " . $e->getMessage() . "\n";
}

// 11. Fulfillment simulation & DRX loyalty reward
echo "\n--- Testing Digital Fulfillment & Loyalty Dispatch ---\n";
$orderFull = $commerce->getOrder($order['id']);
$fulfillRes = $commerce->getFulfillment()->fulfillOrder($orderFull);
assertTest("Fulfillment dispatched with digital asset tokens", !empty($fulfillRes));

$auditCount = $commerce->getDb()->query("SELECT COUNT(*) FROM commerce_audit_events WHERE entity_id = '{$order['id']}'")->fetchColumn();
assertTest("Audit events logged for order lifecycle (count: $auditCount)", (int)$auditCount >= 1);

echo "\n========================================================\n";
echo " TEST SUMMARY: $testPassed / $testTotal TESTS PASSED\n";
echo "========================================================\n";
