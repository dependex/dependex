-- ==============================================================================
-- UNIVERSAL COMMERCE CORE DATABASE SCHEMA (SQLite / MySQL Compatible DDL)
-- Mirco Pregnolato Universe Commerce System
-- ==============================================================================

-- 1. BUSINESSES (Registered brands/domains in the Universe)
CREATE TABLE IF NOT EXISTS commerce_businesses (
    id VARCHAR(64) PRIMARY KEY,
    code VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(120) NOT NULL,
    domain VARCHAR(190) NOT NULL,
    allowed_origins TEXT,
    brand_color VARCHAR(16) DEFAULT '#D4AF37',
    support_email VARCHAR(190),
    default_currency VARCHAR(8) DEFAULT 'EUR',
    active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. PRODUCTS
CREATE TABLE IF NOT EXISTS commerce_products (
    id VARCHAR(64) PRIMARY KEY,
    business_id VARCHAR(64) NOT NULL,
    sku VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    product_type VARCHAR(32) DEFAULT 'DIGITAL',
    default_price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(8) DEFAULT 'EUR',
    vat_rate DECIMAL(5,2) DEFAULT 0.00,
    image_url VARCHAR(255),
    fulfillment_type VARCHAR(64) DEFAULT 'ACCESS_GRANT',
    metadata_json TEXT,
    active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES commerce_businesses(id)
);

-- 3. OFFERS (M.A.G.I.C. Tiers & Buyable Catalog Items)
CREATE TABLE IF NOT EXISTS commerce_offers (
    id VARCHAR(64) PRIMARY KEY,
    business_id VARCHAR(64) NOT NULL,
    product_id VARCHAR(64) NOT NULL,
    offer_code VARCHAR(64) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(8) DEFAULT 'EUR',
    vat_rate DECIMAL(5,2) DEFAULT 0.00,
    offer_type VARCHAR(32) DEFAULT 'ONE_SHOT',
    billing_interval VARCHAR(32),
    magic_tier INTEGER DEFAULT 1,
    badge VARCHAR(64),
    bonus_text TEXT,
    guarantee_text TEXT,
    return_url VARCHAR(255),
    active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES commerce_businesses(id),
    FOREIGN KEY (product_id) REFERENCES commerce_products(id)
);

-- 4. COUPONS
CREATE TABLE IF NOT EXISTS commerce_coupons (
    id VARCHAR(64) PRIMARY KEY,
    code VARCHAR(64) UNIQUE NOT NULL,
    business_id VARCHAR(64),
    discount_type VARCHAR(16) DEFAULT 'PERCENT',
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    max_uses INTEGER DEFAULT 0,
    uses_count INTEGER DEFAULT 0,
    active INTEGER DEFAULT 1,
    expires_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. CUSTOMERS
CREATE TABLE IF NOT EXISTS commerce_customers (
    id VARCHAR(64) PRIMARY KEY,
    email VARCHAR(190) UNIQUE NOT NULL,
    first_name VARCHAR(120),
    last_name VARCHAR(120),
    phone VARCHAR(64),
    company_name VARCHAR(190),
    vat_number VARCHAR(64),
    fiscal_code VARCHAR(64),
    address_line1 VARCHAR(255),
    city VARCHAR(120),
    postal_code VARCHAR(32),
    country VARCHAR(8) DEFAULT 'IT',
    marketing_consent INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 6. CARTS
CREATE TABLE IF NOT EXISTS commerce_carts (
    id VARCHAR(64) PRIMARY KEY,
    session_token VARCHAR(128) UNIQUE NOT NULL,
    business_id VARCHAR(64) NOT NULL,
    currency VARCHAR(8) DEFAULT 'EUR',
    coupon_id VARCHAR(64),
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    customer_id VARCHAR(64),
    customer_email VARCHAR(190),
    source_domain VARCHAR(190),
    attribution_json TEXT,
    status VARCHAR(32) DEFAULT 'ACTIVE',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 7. CART ITEMS
CREATE TABLE IF NOT EXISTS commerce_cart_items (
    id VARCHAR(64) PRIMARY KEY,
    cart_id VARCHAR(64) NOT NULL,
    offer_id VARCHAR(64) NOT NULL,
    quantity INTEGER DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES commerce_carts(id) ON DELETE CASCADE,
    FOREIGN KEY (offer_id) REFERENCES commerce_offers(id)
);

-- 8. ORDERS
CREATE TABLE IF NOT EXISTS commerce_orders (
    id VARCHAR(64) PRIMARY KEY,
    order_number VARCHAR(64) UNIQUE NOT NULL,
    business_id VARCHAR(64) NOT NULL,
    cart_id VARCHAR(64),
    customer_id VARCHAR(64) NOT NULL,
    currency VARCHAR(8) DEFAULT 'EUR',
    subtotal DECIMAL(10,2) NOT NULL,
    vat_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(32) DEFAULT 'PENDING',
    fulfillment_status VARCHAR(32) DEFAULT 'PENDING',
    payment_method VARCHAR(32) DEFAULT 'PAYPAL',
    source_domain VARCHAR(190),
    source_url TEXT,
    attribution_json TEXT,
    customer_notes TEXT,
    idempotency_key VARCHAR(128) UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES commerce_businesses(id),
    FOREIGN KEY (customer_id) REFERENCES commerce_customers(id)
);

-- 9. ORDER ITEMS
CREATE TABLE IF NOT EXISTS commerce_order_items (
    id VARCHAR(64) PRIMARY KEY,
    order_id VARCHAR(64) NOT NULL,
    offer_id VARCHAR(64) NOT NULL,
    product_id VARCHAR(64) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INTEGER DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    vat_rate DECIMAL(5,2) DEFAULT 0.00,
    line_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id) ON DELETE CASCADE
);

-- 10. PAYMENTS
CREATE TABLE IF NOT EXISTS commerce_payments (
    id VARCHAR(64) PRIMARY KEY,
    order_id VARCHAR(64) NOT NULL,
    provider VARCHAR(32) DEFAULT 'PAYPAL',
    paypal_order_id VARCHAR(128),
    paypal_capture_id VARCHAR(128),
    status VARCHAR(32) DEFAULT 'CREATED',
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(8) DEFAULT 'EUR',
    payer_email VARCHAR(190),
    payer_id VARCHAR(64),
    payer_name VARCHAR(190),
    merchant_id VARCHAR(64),
    raw_payload_json TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id)
);

-- 11. PAYMENT EVENTS (Webhook audits & raw events)
CREATE TABLE IF NOT EXISTS commerce_payment_events (
    id VARCHAR(64) PRIMARY KEY,
    payment_id VARCHAR(64),
    event_type VARCHAR(128) NOT NULL,
    event_id VARCHAR(128) UNIQUE NOT NULL,
    payload_json TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 12. SUBSCRIPTIONS
CREATE TABLE IF NOT EXISTS commerce_subscriptions (
    id VARCHAR(64) PRIMARY KEY,
    business_id VARCHAR(64) NOT NULL,
    customer_id VARCHAR(64) NOT NULL,
    offer_id VARCHAR(64) NOT NULL,
    external_sub_id VARCHAR(128) UNIQUE NOT NULL,
    status VARCHAR(32) DEFAULT 'ACTIVE',
    plan_id VARCHAR(128),
    current_period_start DATETIME,
    current_period_end DATETIME,
    metadata_json TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES commerce_businesses(id),
    FOREIGN KEY (customer_id) REFERENCES commerce_customers(id),
    FOREIGN KEY (offer_id) REFERENCES commerce_offers(id)
);

-- 13. FULFILLMENTS
CREATE TABLE IF NOT EXISTS commerce_fulfillments (
    id VARCHAR(64) PRIMARY KEY,
    order_id VARCHAR(64) NOT NULL,
    fulfillment_type VARCHAR(64) NOT NULL,
    status VARCHAR(32) DEFAULT 'PENDING',
    access_token VARCHAR(128),
    content_json TEXT,
    fulfilled_at DATETIME,
    FOREIGN KEY (order_id) REFERENCES commerce_orders(id)
);

-- 14. AUDIT EVENTS
CREATE TABLE IF NOT EXISTS commerce_audit_events (
    id VARCHAR(64) PRIMARY KEY,
    event_type VARCHAR(64) NOT NULL,
    entity_type VARCHAR(64) NOT NULL,
    entity_id VARCHAR(64) NOT NULL,
    ip_address VARCHAR(64),
    user_agent TEXT,
    details_json TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- INDICES FOR HIGH PERFORMANCE & IDEMPOTENCY
CREATE INDEX IF NOT EXISTS idx_comm_offers_code ON commerce_offers(offer_code);
CREATE INDEX IF NOT EXISTS idx_comm_carts_token ON commerce_carts(session_token);
CREATE INDEX IF NOT EXISTS idx_comm_orders_num ON commerce_orders(order_number);
CREATE INDEX IF NOT EXISTS idx_comm_orders_cust ON commerce_orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_comm_payments_paypal ON commerce_payments(paypal_order_id);
CREATE INDEX IF NOT EXISTS idx_comm_pay_events_id ON commerce_payment_events(event_id);
