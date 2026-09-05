<?php
declare(strict_types=1);

require_once __DIR__ . '/CommerceEnv.php';
require_once __DIR__ . '/PayPalService.php';
require_once __DIR__ . '/FulfillmentService.php';

/**
 * UNIVERSAL COMMERCE CORE ENGINE
 * Centralized, multi-brand, headless-ready eCommerce architecture
 */
class UniversalCommerce {
    private static ?self $instance = null;
    private PDO $db;
    private PayPalService $paypal;
    private FulfillmentService $fulfillment;

    public static function getInstance(?PDO $pdo = null): self {
        if (self::$instance === null) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    public function __construct(?PDO $pdo = null) {
        CommerceEnv::load();
        if ($pdo) {
            $this->db = $pdo;
        } else if (function_exists('db')) {
            $this->db = db();
        } else {
            $dbPath = __DIR__ . '/../../data/acat_community.sqlite';
            $this->db = new PDO('sqlite:' . $dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $this->db->exec('PRAGMA foreign_keys=ON; PRAGMA journal_mode=WAL;');
        }

        $this->paypal = new PayPalService();
        $this->fulfillment = new FulfillmentService($this->db);
        $this->initSchema();
        $this->seedCatalog();
    }

    public function getDb(): PDO {
        return $this->db;
    }

    public function getPayPal(): PayPalService {
        return $this->paypal;
    }

    public function getFulfillment(): FulfillmentService {
        return $this->fulfillment;
    }

    /**
     * Initializes database tables if missing
     */
    private function initSchema(): void {
        $sqlPath = __DIR__ . '/schema.sql';
        if (file_exists($sqlPath)) {
            $sql = (string)file_get_contents($sqlPath);
            // Execute statements individually to ensure SQLite compatibility
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($queries as $q) {
                if ($q !== '') {
                    try {
                        $this->db->exec($q);
                    } catch (Throwable $e) {
                        // Table or index may already exist
                    }
                }
            }
        }
    }

    /**
     * Pre-seeds Universe Brands, Products and M.A.G.I.C. Offers
     */
    public function seedCatalog(): void {
        // 1. Businesses
        $businesses = [
            ['biz_dependex', 'DEPENDEX', 'DEPENDEX & OLTRE', 'dependex.social', 'https://dependex.social,https://oltre.social,http://localhost:8000', '#D4AF37', 'info@dependex.social'],
            ['biz_mircopregnolato', 'MIRCOPREGNOLATO', 'Mirco Pregnolato Advisory', 'mircopregnolato.it', 'https://mircopregnolato.it,http://localhost:8000', '#D4AF37', 'info@mircopregnolato.it'],
            ['biz_mywallet', 'MYWALLET', 'MyWallet Business Web3', 'mywallet.business', 'https://mywallet.business,http://localhost:8000', '#D4AF37', 'contact@mywallet.business'],
            ['biz_betterway', 'BETTERWAY', 'BetterWay Agency', 'betterway.agency', 'https://betterway.agency,http://localhost:8000', '#D4AF37', 'growth@betterway.agency'],
            ['biz_neuralog', 'NEURALOG', 'NeuraLog Governance', 'neuralog.pro', 'https://neuralog.pro,http://localhost:8000', '#D4AF37', 'security@neuralog.pro']
        ];

        $bStmt = $this->db->prepare("INSERT OR IGNORE INTO commerce_businesses(id, code, name, domain, allowed_origins, brand_color, support_email) VALUES(?,?,?,?,?,?,?)");
        foreach ($businesses as $b) {
            $bStmt->execute($b);
        }

        // 2. Products
        $products = [
            ['prd_starter_kit', 'biz_dependex', 'DX-STARTER-01', 'Starter Kit & Diagnosi', 'Orientamento rapido, test iniziale e strumenti per il cammino di sobrietà.', 'COURSE', 27.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'ACCESS_GRANT'],
            ['prd_core_proto', 'biz_dependex', 'DX-CORE-02', 'Protocollo Completo & Trasformazione', 'Il metodo proprietario in 5 fasi operative certificate per lautonomia definitiva.', 'COURSE', 497.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'ACCESS_GRANT'],
            ['prd_elite_mentor', 'biz_dependex', 'DX-ELITE-03', 'Programma Elite & Affiancamento', 'Affiancamento intensivo 1-a-1 con facilitatori senior e accesso prioritario.', 'SERVICE', 1997.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'ONBOARDING_CALL'],
            ['prd_club_continuity', 'biz_dependex', 'DX-CLUB-04', 'Club Permanente & Cortex AI', 'Abbonamento mensile con sessioni settimanali continuative e Company Brain AI.', 'SUBSCRIPTION', 39.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'ACCESS_GRANT'],
            
            ['prd_mp_books', 'biz_mircopregnolato', 'MP-BOOKS-01', 'Collana Libri Strategici Mirco Pregnolato', 'Raccolta completa di saggi, modelli mentali ed architetture d impresa.', 'DIGITAL', 47.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'DOWNLOAD'],
            ['prd_mp_advisory', 'biz_mircopregnolato', 'MP-ADVISORY-02', 'Sessione Strategica 1-a-1 Mirco Pregnolato', 'Consulenza riservata di 90 minuti per architettura business ed intelligenza artificiale.', 'SERVICE', 490.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'ONBOARDING_CALL'],
            
            ['prd_mw_suite', 'biz_mywallet', 'MW-SUITE-01', 'Suite Wallet Istituzionale & Self-Custody', 'Framework Web3 per gestione tesoreria aziendale e cold storage.', 'DIGITAL', 297.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'ACCESS_GRANT'],
            ['prd_bw_audit', 'biz_betterway', 'BW-AUDIT-01', 'Growth Audit & Funnel Strategy', 'Analisi chirurgica dei colli di bottiglia e ottimizzazione tasso di conversione.', 'SERVICE', 350.00, 'EUR', 0.00, 'assets/img/app-icon.svg', 'ONBOARDING_CALL']
        ];

        $pStmt = $this->db->prepare("INSERT OR IGNORE INTO commerce_products(id, business_id, sku, name, description, product_type, default_price, currency, vat_rate, image_url, fulfillment_type) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
        foreach ($products as $p) {
            $pStmt->execute($p);
        }

        // 3. Offers (Source of Truth for Prices)
        $offers = [
            ['off_starter_kit', 'biz_dependex', 'prd_starter_kit', 'starter-kit', 'Starter Kit & Diagnosi', 'Il primo passo per entrare in contatto con il metodo', 'Accesso a strumenti diagnostici, primo inventario personale e orientamento al Club.', 27.00, 'EUR', 0.00, 'ONE_SHOT', null, 1, 'LIV. 1 · DIAGNOSI', 'Cassetta Attrezzi Primo Giorno (Valore € 47)', 'Garanzia 14 Giorni Soddisfatti o Rimborsati al 100%'],
            ['off_core_proto', 'biz_dependex', 'prd_core_proto', 'core-transformation', 'Protocollo Completo & Trasformazione', 'Il cuore operativo dellevoluzione personale', 'Il metodo proprietario in 5 fasi operative certificate per lautonomia definitiva.', 497.00, 'EUR', 0.00, 'ONE_SHOT', null, 2, 'LIV. 2 · CORE OFFER', 'Masterclass Ingranaggi Cognitivi + Supporto Dedicato', 'Garanzia Integrale 30 Giorni Risultato Garantito'],
            ['off_elite_mentor', 'biz_dependex', 'prd_elite_mentor', 'elite-mentorship', 'Programma Elite & Affiancamento', 'Supporto intensivo e diretto per risultati rapidi', 'Sessioni personalizzate 1-a-1, linea diretta e piano d azione su misura.', 1997.00, 'EUR', 0.00, 'ONE_SHOT', null, 3, 'LIV. 3 · ALTO CONTATTO', 'Accesso Vita Intero Ecosistema + Protocolli Avanzati', 'Garanzia di Affiancamento fino al raggiungimento degli obiettivi'],
            ['off_club_continuity', 'biz_dependex', 'prd_club_continuity', 'club-continuity', 'Club Permanente & Cortex AI', 'Continuità relazionale e intelligenza collettiva 24/7', 'Partecipazione continuativa ai Club settimanali e accesso illimitato a Cortex AI.', 39.00, 'EUR', 0.00, 'RECURRING', 'MONTH', 4, 'LIV. 4 · CONTINUITÀ', 'Aggiornamenti continui e biblioteca casi studio', 'Disdici quando vuoi con un solo click'],
            
            ['off_mp_books', 'biz_mircopregnolato', 'prd_mp_books', 'mp-books', 'Collana Libri Mirco Pregnolato', 'I testi fondamentali per l indipendenza e l automazione', 'Raccolta completa di guide operative e principi.', 47.00, 'EUR', 0.00, 'ONE_SHOT', null, 1, 'BESTSELLER', 'Workbook operativo scaricabile', 'Garanzia 30 giorni'],
            ['off_mp_advisory', 'biz_mircopregnolato', 'prd_mp_advisory', 'mp-advisory', 'Sessione Strategica con Mirco Pregnolato', 'Consulenza esecutiva 90 minuti', 'Audit strategico della tua azienda, architettura AI e posizionamento.', 490.00, 'EUR', 0.00, 'ONE_SHOT', null, 2, 'CONSULENZA VIP', 'Report scritto di sintesi con piano d azione', 'Garanzia di chiarezza assoluta'],
            
            ['off_mw_suite', 'biz_mywallet', 'prd_mw_suite', 'mw-suite', 'Suite MyWallet Istituzionale', 'Strumenti Web3 e cold storage aziendale', 'Software e procedure di sicurezza per tesoreria digitale.', 297.00, 'EUR', 0.00, 'ONE_SHOT', null, 1, 'SECURITY SUITE', 'Template policy interna e backup phrase vault', 'Garanzia conformità'],
            ['off_bw_audit', 'biz_betterway', 'prd_bw_audit', 'bw-audit', 'Growth & CRO Audit', 'Analisi conversioni e funnel optimization', 'Checkup completo del tuo funnel con identificazione delle perdite di fatturato.', 350.00, 'EUR', 0.00, 'ONE_SHOT', null, 1, 'AUDIT OPERATIVO', 'Roadmap prioritaria di implementazione in 14 giorni', 'Garanzia valore moltiplicato']
        ];

        $oStmt = $this->db->prepare("INSERT OR IGNORE INTO commerce_offers(id, business_id, product_id, offer_code, title, subtitle, description, price, currency, vat_rate, offer_type, billing_interval, magic_tier, badge, bonus_text, guarantee_text) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        foreach ($offers as $o) {
            $oStmt->execute($o);
        }

        // 4. Default Coupon
        $this->db->prepare("INSERT OR IGNORE INTO commerce_coupons(id, code, discount_type, discount_value, min_order_amount, max_uses, active) VALUES('cpn_welcome10', 'WELCOME10', 'PERCENT', 10.00, 20.00, 1000, 1)")->execute();
        $this->db->prepare("INSERT OR IGNORE INTO commerce_coupons(id, code, discount_type, discount_value, min_order_amount, max_uses, active) VALUES('cpn_mirco50', 'MIRCO50', 'FIXED', 50.00, 200.00, 500, 1)")->execute();
    }

    /**
     * Source of Truth: retrieve offer by ID or code
     */
    public function getOffer(string $idOrCode): ?array {
        $st = $this->db->prepare("SELECT o.*, b.name business_name, b.domain business_domain, b.support_email, p.name product_name, p.product_type, p.image_url, p.fulfillment_type FROM commerce_offers o JOIN commerce_businesses b ON o.business_id = b.id JOIN commerce_products p ON o.product_id = p.id WHERE (o.id = ? OR o.offer_code = ?) AND o.active = 1");
        $st->execute([$idOrCode, $idOrCode]);
        return $st->fetch() ?: null;
    }

    /**
     * List all offers for a given business
     */
    public function listOffers(?string $businessId = null): array {
        if ($businessId) {
            $st = $this->db->prepare("SELECT o.*, p.name product_name, p.image_url FROM commerce_offers o JOIN commerce_products p ON o.product_id = p.id WHERE o.business_id = ? AND o.active = 1 ORDER BY o.magic_tier ASC, o.price ASC");
            $st->execute([$businessId]);
        } else {
            $st = $this->db->query("SELECT o.*, b.name business_name, p.name product_name, p.image_url FROM commerce_offers o JOIN commerce_businesses b ON o.business_id = b.id JOIN commerce_products p ON o.product_id = p.id WHERE o.active = 1 ORDER BY o.business_id, o.magic_tier ASC, o.price ASC");
        }
        return $st->fetchAll();
    }

    /**
     * Get or create a shopping cart linked to a session token
     */
    public function getOrCreateCart(?string $sessionToken = null, string $businessId = 'biz_dependex', ?string $sourceDomain = null): array {
        if (!$sessionToken) {
            if (empty($_SESSION['commerce_cart_token'])) {
                $_SESSION['commerce_cart_token'] = bin2hex(random_bytes(24));
            }
            $sessionToken = $_SESSION['commerce_cart_token'];
        }

        $st = $this->db->prepare("SELECT * FROM commerce_carts WHERE session_token = ? AND status = 'ACTIVE'");
        $st->execute([$sessionToken]);
        $cart = $st->fetch();

        if (!$cart) {
            $cartId = 'crt_' . bin2hex(random_bytes(12));
            $ins = $this->db->prepare("INSERT INTO commerce_carts(id, session_token, business_id, currency, source_domain, status, created_at, updated_at) VALUES(?,?,?,'EUR',?,'ACTIVE',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
            $ins->execute([$cartId, $sessionToken, $businessId, $sourceDomain ?: ($_SERVER['HTTP_HOST'] ?? 'dependex.social')]);
            $st->execute([$sessionToken]);
            $cart = $st->fetch();
        }

        return $this->getCartDetails($cart['id']);
    }

    /**
     * Add an offer to cart with server-enforced price
     */
    public function addToCart(string $cartId, string $offerIdOrCode, int $quantity = 1, string|array|null $sourceDomainOrMeta = null, array $attribution = []): array {
        $offer = $this->getOffer($offerIdOrCode);
        if (!$offer) {
            throw new InvalidArgumentException("Offerta '{$offerIdOrCode}' non trovata o non attiva.");
        }

        $sourceDomain = null;
        if (is_array($sourceDomainOrMeta)) {
            $sourceDomain = $sourceDomainOrMeta['source_domain'] ?? null;
            $attribution = array_merge($attribution, $sourceDomainOrMeta);
        } elseif (is_string($sourceDomainOrMeta)) {
            $sourceDomain = $sourceDomainOrMeta;
        }

        $quantity = max(1, $quantity);
        $price = (float)$offer['price']; // SERVER-SIDE SOURCE OF TRUTH

        // Check if item already exists in cart
        $check = $this->db->prepare("SELECT id, quantity FROM commerce_cart_items WHERE cart_id = ? AND offer_id = ?");
        $check->execute([$cartId, $offer['id']]);
        $existing = $check->fetch();

        if ($existing) {
            // For one-shot or recurring packages, adjust quantity
            $newQty = $existing['quantity'] + $quantity;
            $lineTotal = $newQty * $price;
            $upd = $this->db->prepare("UPDATE commerce_cart_items SET quantity = ?, unit_price = ?, line_total = ? WHERE id = ?");
            $upd->execute([$newQty, $price, $lineTotal, $existing['id']]);
        } else {
            $itemId = 'itm_' . bin2hex(random_bytes(10));
            $lineTotal = $quantity * $price;
            $ins = $this->db->prepare("INSERT INTO commerce_cart_items(id, cart_id, offer_id, quantity, unit_price, line_total, created_at) VALUES(?,?,?,?,?,?,CURRENT_TIMESTAMP)");
            $ins->execute([$itemId, $cartId, $offer['id'], $quantity, $price, $lineTotal]);
        }

        // Update cart business and attribution if provided
        if ($sourceDomain || !empty($attribution)) {
            $updCart = $this->db->prepare("UPDATE commerce_carts SET business_id = ?, source_domain = COALESCE(?, source_domain), attribution_json = COALESCE(?, attribution_json), updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updCart->execute([
                $offer['business_id'],
                $sourceDomain,
                !empty($attribution) ? json_encode($attribution, JSON_UNESCAPED_UNICODE) : null,
                $cartId
            ]);
        }

        $this->auditLog('CART_ITEM_ADDED', 'CART', $cartId, ['offer_id' => $offer['id'], 'qty' => $quantity, 'price' => $price]);
        return $this->getCartDetails($cartId);
    }

    /**
     * Update item quantity in cart
     */
    public function updateCartItem(string $cartId, string $offerIdOrCode, int $quantity): array {
        $offer = $this->getOffer($offerIdOrCode);
        if (!$offer) {
            throw new InvalidArgumentException("Offerta non valida.");
        }

        if ($quantity <= 0) {
            return $this->removeFromCart($cartId, $offer['id']);
        }

        $price = (float)$offer['price'];
        $lineTotal = $quantity * $price;

        $upd = $this->db->prepare("UPDATE commerce_cart_items SET quantity = ?, unit_price = ?, line_total = ? WHERE cart_id = ? AND offer_id = ?");
        $upd->execute([$quantity, $price, $lineTotal, $cartId, $offer['id']]);

        return $this->getCartDetails($cartId);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(string $cartId, string $offerIdOrCode): array {
        $offer = $this->getOffer($offerIdOrCode);
        $offerId = $offer ? $offer['id'] : $offerIdOrCode;

        $del = $this->db->prepare("DELETE FROM commerce_cart_items WHERE cart_id = ? AND offer_id = ?");
        $del->execute([$cartId, $offerId]);

        return $this->getCartDetails($cartId);
    }

    /**
     * Clear all items from a cart
     */
    public function clearCart(string $cartId): void {
        $this->db->prepare("DELETE FROM commerce_cart_items WHERE cart_id = ?")->execute([$cartId]);
        $this->db->prepare("UPDATE commerce_carts SET coupon_id = NULL, discount_amount = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$cartId]);
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(string $cartId, string $code): array {
        $code = strtoupper(trim($code));
        $st = $this->db->prepare("SELECT * FROM commerce_coupons WHERE code = ? AND active = 1 AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)");
        $st->execute([$code]);
        $coupon = $st->fetch();

        if (!$coupon) {
            throw new InvalidArgumentException("Codice coupon non valido o scaduto.");
        }

        $cart = $this->getCartDetails($cartId);
        if ($cart['subtotal'] < (float)$coupon['min_order_amount']) {
            throw new InvalidArgumentException("Questo coupon richiede una spesa minima di € " . number_format((float)$coupon['min_order_amount'], 2));
        }

        $discount = 0.0;
        if ($coupon['discount_type'] === 'PERCENT') {
            $discount = round($cart['subtotal'] * ((float)$coupon['discount_value'] / 100), 2);
        } else {
            $discount = min($cart['subtotal'], (float)$coupon['discount_value']);
        }

        $upd = $this->db->prepare("UPDATE commerce_carts SET coupon_id = ?, discount_amount = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $upd->execute([$coupon['id'], $discount, $cartId]);
        $cartData = $this->getCartDetails($cartId);
        return array_merge($cartData, [
            'success' => true,
            'discount' => $discount,
            'message' => 'Coupon applicato con successo'
        ]);
    }

    /**
     * Complete calculation of cart totals, breakdown, and items
     */
    public function getCartDetails(string $cartId): array {
        $cSt = $this->db->prepare("SELECT c.*, b.name business_name, b.brand_color, cp.code coupon_code FROM commerce_carts c LEFT JOIN commerce_businesses b ON c.business_id = b.id LEFT JOIN commerce_coupons cp ON c.coupon_id = cp.id WHERE c.id = ?");
        $cSt->execute([$cartId]);
        $cart = $cSt->fetch();

        if (!$cart) {
            throw new RuntimeException("Carrello non trovato.");
        }

        $iSt = $this->db->prepare("SELECT ci.*, o.title, o.subtitle, o.offer_code, o.offer_type, o.badge, p.image_url, p.product_type FROM commerce_cart_items ci JOIN commerce_offers o ON ci.offer_id = o.id JOIN commerce_products p ON o.product_id = p.id WHERE ci.cart_id = ? ORDER BY ci.created_at ASC");
        $iSt->execute([$cartId]);
        $items = $iSt->fetchAll();

        $subtotal = 0.0;
        $totalItemsCount = 0;
        foreach ($items as $it) {
            $subtotal += (float)$it['line_total'];
            $totalItemsCount += (int)$it['quantity'];
        }

        $discount = min($subtotal, (float)($cart['discount_amount'] ?? 0.0));
        $taxable = max(0.0, $subtotal - $discount);
        $vat = 0.0; // By default training / non-profit health courses or exempt, or standard
        $grandTotal = round($taxable + $vat, 2);

        return [
            'id' => $cart['id'],
            'session_token' => $cart['session_token'],
            'business_id' => $cart['business_id'],
            'business_name' => $cart['business_name'] ?? 'DEPENDEX',
            'currency' => $cart['currency'] ?? 'EUR',
            'items_count' => $totalItemsCount,
            'items' => $items,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'discount_total' => $discount,
            'coupon_code' => $cart['coupon_code'] ?? null,
            'vat_amount' => $vat,
            'tax_total' => $vat,
            'total_amount' => $grandTotal,
            'total' => $grandTotal,
            'source_domain' => $cart['source_domain'] ?? 'dependex.social',
            'status' => $cart['status']
        ];
    }

    /**
     * Find or register Customer record
     */
    public function findOrCreateCustomer(array $data): array {
        $email = strtolower(trim((string)($data['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email non valida: " . htmlspecialchars($email));
        }

        $st = $this->db->prepare("SELECT * FROM commerce_customers WHERE email = ?");
        $st->execute([$email]);
        $customer = $st->fetch();

        if ($customer) {
            // Update fields if provided
            $upd = $this->db->prepare("UPDATE commerce_customers SET first_name = COALESCE(?, first_name), last_name = COALESCE(?, last_name), phone = COALESCE(?, phone), company_name = COALESCE(?, company_name), vat_number = COALESCE(?, vat_number), fiscal_code = COALESCE(?, fiscal_code), address_line1 = COALESCE(?, address_line1), city = COALESCE(?, city), postal_code = COALESCE(?, postal_code), country = COALESCE(?, country), marketing_consent = COALESCE(?, marketing_consent) WHERE id = ?");
            $upd->execute([
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['phone'] ?? null,
                $data['company_name'] ?? null,
                $data['vat_number'] ?? null,
                $data['fiscal_code'] ?? null,
                $data['address_line1'] ?? null,
                $data['city'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? null,
                isset($data['marketing_consent']) ? (int)$data['marketing_consent'] : null,
                $customer['id']
            ]);
            $st->execute([$email]);
            return $st->fetch();
        }

        $cid = 'cus_' . bin2hex(random_bytes(10));
        $ins = $this->db->prepare("INSERT INTO commerce_customers(id, email, first_name, last_name, phone, company_name, vat_number, fiscal_code, address_line1, city, postal_code, country, marketing_consent, created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)");
        $ins->execute([
            $cid,
            $email,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['phone'] ?? '',
            $data['company_name'] ?? '',
            $data['vat_number'] ?? '',
            $data['fiscal_code'] ?? '',
            $data['address_line1'] ?? '',
            $data['city'] ?? '',
            $data['postal_code'] ?? '',
            $data['country'] ?? 'IT',
            (int)($data['marketing_consent'] ?? 0)
        ]);

        $st->execute([$email]);
        return $st->fetch();
    }

    /**
     * Create an internal Order from a Cart in status PENDING
     */
    public function createOrderFromCart(string $cartId, array|string $customerDataOrId, array $meta = []): array {
        $cart = $this->getCartDetails($cartId);
        if (empty($cart['items'])) {
            throw new RuntimeException("Il carrello è vuoto.");
        }

        if (is_string($customerDataOrId)) {
            $cSt = $this->db->prepare("SELECT * FROM commerce_customers WHERE id = ?");
            $cSt->execute([$customerDataOrId]);
            $customer = $cSt->fetch();
            if (!$customer) {
                throw new RuntimeException("Cliente '{$customerDataOrId}' non trovato.");
            }
        } else {
            $customer = $this->findOrCreateCustomer($customerDataOrId);
        }

        // Generate unique order number (e.g. DX-2026-0001)
        $prefix = strtoupper(substr($cart['business_id'], 4, 2));
        if (!$prefix) $prefix = 'ORD';
        $orderNumber = $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $orderId = 'ord_' . bin2hex(random_bytes(12));
        $idempotencyKey = hash('sha256', $cartId . ':' . $customer['id'] . ':' . date('YmdH'));

        $this->db->beginTransaction();
        try {
            // Check if order already exists for this idempotency key
            $idempCheck = $this->db->prepare("SELECT id, order_number FROM commerce_orders WHERE idempotency_key = ?");
            $idempCheck->execute([$idempotencyKey]);
            if ($existingOrder = $idempCheck->fetch()) {
                $this->db->commit();
                return $this->getOrderDetails($existingOrder['id']);
            }

            $ins = $this->db->prepare("INSERT INTO commerce_orders(id, order_number, business_id, cart_id, customer_id, currency, subtotal, vat_amount, discount_amount, total_amount, payment_status, fulfillment_status, payment_method, source_domain, source_url, attribution_json, customer_notes, idempotency_key, created_at, updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
            $ins->execute([
                $orderId,
                $orderNumber,
                $cart['business_id'],
                $cartId,
                $customer['id'],
                $cart['currency'],
                $cart['subtotal'],
                $cart['vat_amount'],
                $cart['discount_amount'],
                $cart['total_amount'],
                'PENDING',
                'PENDING',
                $meta['payment_method'] ?? 'PAYPAL',
                $cart['source_domain'],
                $meta['source_url'] ?? '',
                !empty($meta['attribution']) ? json_encode($meta['attribution'], JSON_UNESCAPED_UNICODE) : null,
                $customerData['notes'] ?? '',
                $idempotencyKey
            ]);

            // Insert items
            $itIns = $this->db->prepare("INSERT INTO commerce_order_items(id, order_id, offer_id, product_id, product_name, quantity, unit_price, vat_rate, line_total) VALUES(?,?,?,?,?,?,?,?,?)");
            foreach ($cart['items'] as $it) {
                $itId = 'oit_' . bin2hex(random_bytes(10));
                $itIns->execute([
                    $itId,
                    $orderId,
                    $it['offer_id'],
                    $it['offer_id'],
                    $it['title'],
                    $it['quantity'],
                    $it['unit_price'],
                    0.00,
                    $it['line_total']
                ]);
            }

            $this->db->commit();
            $this->auditLog('ORDER_CREATED', 'ORDER', $orderId, ['order_number' => $orderNumber, 'total' => $cart['total_amount']]);
            return $this->getOrderDetails($orderId);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get detailed order by ID or order_number
     */
    public function getOrderDetails(string $orderIdOrNumber): ?array {
        $st = $this->db->prepare("SELECT o.*, c.email customer_email, c.first_name, c.last_name, c.phone, c.company_name, c.vat_number, c.fiscal_code, c.address_line1, c.city, c.postal_code, c.country, b.name business_name, b.domain business_domain, b.support_email FROM commerce_orders o JOIN commerce_customers c ON o.customer_id = c.id JOIN commerce_businesses b ON o.business_id = b.id WHERE o.id = ? OR o.order_number = ?");
        $st->execute([$orderIdOrNumber, $orderIdOrNumber]);
        $order = $st->fetch();

        if (!$order) {
            return null;
        }

        $order['status'] = $order['payment_status'] ?? 'PENDING';

        $itSt = $this->db->prepare("SELECT * FROM commerce_order_items WHERE order_id = ?");
        $itSt->execute([$order['id']]);
        $order['items'] = $itSt->fetchAll();

        $paySt = $this->db->prepare("SELECT * FROM commerce_payments WHERE order_id = ?");
        $paySt->execute([$order['id']]);
        $order['payments'] = $paySt->fetchAll();

        $fulSt = $this->db->prepare("SELECT * FROM commerce_fulfillments WHERE order_id = ?");
        $fulSt->execute([$order['id']]);
        $order['fulfillments'] = $fulSt->fetchAll();

        return $order;
    }

    /**
     * Record verified PayPal capture and mark order PAID (Idempotent)
     */
    public function recordPaymentAndFulfill(string $orderId, array $paypalCaptureData): array {
        $order = $this->getOrderDetails($orderId);
        if (!$order) {
            throw new RuntimeException("Ordine {$orderId} non trovato.");
        }

        // Verify status from capture
        $captureStatus = $paypalCaptureData['status'] ?? '';
        $paypalOrderId = $paypalCaptureData['id'] ?? '';
        
        // Find capture details inside purchase units
        $purchaseUnit = $paypalCaptureData['purchase_units'][0] ?? [];
        $captureDetails = $purchaseUnit['payments']['captures'][0] ?? [];
        $captureId = $captureDetails['id'] ?? ($paypalCaptureData['capture_id'] ?? $paypalOrderId);
        $amountReceived = (float)($captureDetails['amount']['value'] ?? ($purchaseUnit['amount']['value'] ?? $paypalCaptureData['amount'] ?? 0.0));
        $currencyReceived = strtoupper($captureDetails['amount']['currency_code'] ?? ($purchaseUnit['amount']['currency_code'] ?? 'EUR'));

        // Strict Server-Side Validation: Ensure paid amount matches expected order total
        if (abs($amountReceived - (float)$order['total_amount']) > 0.05) {
            throw new RuntimeException("Mismatch importo: atteso € {$order['total_amount']}, ricevuto € {$amountReceived}");
        }

        if ($currencyReceived !== strtoupper($order['currency'])) {
            throw new RuntimeException("Mismatch valuta: atteso {$order['currency']}, ricevuto {$currencyReceived}");
        }

        if ($captureStatus !== 'COMPLETED') {
            throw new RuntimeException("Stato pagamento PayPal non completato: '{$captureStatus}'");
        }

        $this->db->beginTransaction();
        try {
            // Check if payment already recorded
            $payCheck = $this->db->prepare("SELECT id FROM commerce_payments WHERE paypal_capture_id = ?");
            $payCheck->execute([$captureId]);
            $existingPaymentId = $payCheck->fetchColumn();

            if (!$existingPaymentId) {
                $payId = 'pay_' . bin2hex(random_bytes(10));
                $payer = $paypalCaptureData['payer'] ?? [];
                $payerEmail = $payer['email_address'] ?? $order['customer_email'];
                $payerId = $payer['payer_id'] ?? '';
                $payerName = trim(($payer['name']['given_name'] ?? '') . ' ' . ($payer['name']['surname'] ?? ''));

                $payIns = $this->db->prepare("INSERT INTO commerce_payments(id, order_id, provider, paypal_order_id, paypal_capture_id, status, amount, currency, payer_email, payer_id, payer_name, raw_payload_json, created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,CURRENT_TIMESTAMP)");
                $payIns->execute([
                    $payId,
                    $order['id'],
                    'PAYPAL',
                    $paypalOrderId,
                    $captureId,
                    'COMPLETED',
                    $amountReceived,
                    $currencyReceived,
                    $payerEmail,
                    $payerId,
                    $payerName,
                    json_encode($paypalCaptureData, JSON_UNESCAPED_UNICODE)
                ]);
            }

            // Update order status to PAID
            $updOrder = $this->db->prepare("UPDATE commerce_orders SET payment_status = 'PAID', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updOrder->execute([$order['id']]);

            // Close active cart
            if (!empty($order['cart_id'])) {
                $this->db->prepare("UPDATE commerce_carts SET status = 'COMPLETED', updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$order['cart_id']]);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        // Trigger fulfillment
        $fulfillmentResult = $this->fulfillment->fulfill($order['id']);
        $this->auditLog('PAYMENT_VERIFIED_AND_FULFILLED', 'ORDER', $order['id'], ['capture_id' => $captureId, 'amount' => $amountReceived]);

        return [
            'ok' => true,
            'order_id' => $order['id'],
            'order_number' => $order['order_number'],
            'payment_status' => 'PAID',
            'fulfillment' => $fulfillmentResult
        ];
    }

    public function getCart(string $cartId): array {
        return $this->getCartDetails($cartId);
    }

    public function getOrder(string $orderId): ?array {
        return $this->getOrderDetails($orderId);
    }

    public function listActiveOffers(?string $businessId = null): array {
        return $this->listOffers($businessId);
    }

    public function resolveCustomer(string $email, array $data = []): array {
        $data['email'] = $email;
        if (!empty($data['billing_address'])) {
            $data['address_line1'] = $data['billing_address']['street'] ?? '';
            $data['city'] = $data['billing_address']['city'] ?? '';
            $data['postal_code'] = $data['billing_address']['postal_code'] ?? '';
            $data['country'] = $data['billing_address']['country'] ?? 'IT';
        }
        if (!empty($data['fiscal_data'])) {
            $data['company_name'] = $data['fiscal_data']['company_name'] ?? '';
            $data['vat_number'] = $data['fiscal_data']['vat_number'] ?? '';
            $data['fiscal_code'] = $data['fiscal_data']['fiscal_code'] ?? '';
        }
        return $this->findOrCreateCustomer($data);
    }

    public function updateCartItemQuantity(string $cartId, string $itemId, int $quantity): array {
        if ($quantity <= 0) {
            return $this->removeCartItem($cartId, $itemId);
        }
        $st = $this->db->prepare("SELECT unit_price FROM commerce_cart_items WHERE id = ? AND cart_id = ?");
        $st->execute([$itemId, $cartId]);
        $price = (float)$st->fetchColumn();
        if ($price > 0) {
            $lineTotal = $quantity * $price;
            $upd = $this->db->prepare("UPDATE commerce_cart_items SET quantity = ?, line_total = ? WHERE id = ? AND cart_id = ?");
            $upd->execute([$quantity, $lineTotal, $itemId, $cartId]);
        }
        return $this->getCartDetails($cartId);
    }

    public function removeCartItem(string $cartId, string $itemId): array {
        $del = $this->db->prepare("DELETE FROM commerce_cart_items WHERE id = ? AND cart_id = ?");
        $del->execute([$itemId, $cartId]);
        return $this->getCartDetails($cartId);
    }

    public function removeCoupon(string $cartId): array {
        $this->db->prepare("UPDATE commerce_carts SET coupon_id = NULL, discount_amount = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$cartId]);
        return $this->getCartDetails($cartId);
    }

    public function createPayPalOrderForInternalOrder(string $orderId, string $returnUrl, string $cancelUrl): array {
        $order = $this->getOrderDetails($orderId);
        if (!$order) {
            return ['success' => false, 'error' => 'Ordine non trovato'];
        }

        $items = [];
        foreach ($order['items'] as $it) {
            $items[] = [
                'name' => mb_substr($it['product_name'], 0, 127),
                'unit_amount' => (float)$it['unit_price'],
                'quantity' => (int)$it['quantity']
            ];
        }

        try {
            $ppOrder = $this->paypal->createOrder([
                'order_id' => $order['id'],
                'order_number' => $order['order_number'],
                'business_id' => $order['business_id'],
                'total_amount' => (float)$order['total_amount'],
                'subtotal' => (float)$order['subtotal'],
                'vat_amount' => (float)$order['vat_amount'],
                'discount_amount' => (float)$order['discount_amount'],
                'currency' => $order['currency'],
                'description' => 'Ordine ' . $order['order_number'] . ' - Mirco Universe',
                'brand_name' => mb_substr($order['business_name'] ?? 'Mirco Universe', 0, 127),
                'items' => array_map(fn($it) => [
                    'name' => mb_substr($it['product_name'], 0, 127),
                    'unit_price' => (float)$it['unit_price'],
                    'quantity' => (int)$it['quantity']
                ], $order['items']),
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl
            ]);

            if (!empty($ppOrder['id'])) {
                $this->db->prepare("UPDATE commerce_orders SET updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$orderId]);
                $this->auditLog('PAYPAL_ORDER_CREATED', 'ORDER', $orderId, ['paypal_order_id' => $ppOrder['id']]);
                return [
                    'success' => true,
                    'paypal_order_id' => $ppOrder['id'],
                    'order' => $order
                ];
            }

            return ['success' => false, 'error' => 'Risposta PayPal non valida: ' . json_encode($ppOrder)];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function captureAndVerifyPayPalOrder(string $internalOrderId, string $paypalOrderId): array {
        try {
            $captureData = $this->paypal->captureOrder($paypalOrderId);
            $res = $this->recordPaymentAndFulfill($internalOrderId, $captureData);
            return [
                'success' => true,
                'order' => $this->getOrderDetails($internalOrderId),
                'capture' => $captureData
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Audit logger
     */
    public function auditLog(string $eventType, string $entityType, string $entityId, array $details = []): void {
        try {
            $id = 'adt_' . bin2hex(random_bytes(10));
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $st = $this->db->prepare("INSERT INTO commerce_audit_events(id, event_type, entity_type, entity_id, ip_address, user_agent, details_json, created_at) VALUES(?,?,?,?,?,?,?,CURRENT_TIMESTAMP)");
            $st->execute([$id, $eventType, $entityType, $entityId, $ip, $ua, json_encode($details, JSON_UNESCAPED_UNICODE)]);
        } catch (Throwable $e) {
            error_log("Audit log failure: " . $e->getMessage());
        }
    }
}

if (!class_exists('Dependex\\Commerce\\UniversalCommerce', false)) {
    class_alias('UniversalCommerce', 'Dependex\\Commerce\\UniversalCommerce');
}

