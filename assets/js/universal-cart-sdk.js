/**
 * UNIVERSAL COMMERCE SDK — MIRCO PREGNOLATO UNIVERSE
 * Drop-in client library for any external site to trigger Add to Cart, Buy Now, and Mini-Cart Drawer.
 * Visual aesthetics: Jet-Black (#070709), 24K Gold (#D4AF37), and Crisp White.
 */

(function (window, document) {
    'use strict';

    if (window.dxCommerce) {
        return; // already initialized
    }

    // Determine Commerce Origin (defaults to the script's origin or current domain)
    const scriptTag = document.currentScript || document.querySelector('script[src*="universal-cart-sdk"]');
    const commerceOrigin = (scriptTag && scriptTag.getAttribute('data-commerce-endpoint')) 
        ? scriptTag.getAttribute('data-commerce-endpoint').replace(/\/+$/, '') 
        : (window.location.origin || '');

    const currentDomain = window.location.hostname;

    // Helper to get cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Storage / session cart token
    let cartId = localStorage.getItem('dx_cart_id') || getCookie('dx_cart_id') || null;

    // SVG icon helper (Zero emoji)
    function getSvg(name) {
        switch (name) {
            case 'cart':
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>';
            case 'close':
                return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            case 'trash':
                return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
            case 'sparkles':
                return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/></svg>';
            case 'lock':
                return '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
            default:
                return '';
        }
    }

    // Core API Client
    const dxCommerce = {
        origin: commerceOrigin,
        cart: null,

        async api(endpoint, method = 'GET', data = null) {
            const url = `${this.origin}/api-cart.php?action=${encodeURIComponent(endpoint)}`;
            const headers = {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (cartId) {
                headers['X-DX-Cart-Id'] = cartId;
            }

            const options = {
                method,
                headers,
                credentials: 'include'
            };

            if (data && (method === 'POST' || method === 'PUT')) {
                options.body = JSON.stringify(data);
            }

            const res = await fetch(url, options);
            const json = await res.json();

            if (json.cart && json.cart.id) {
                cartId = json.cart.id;
                localStorage.setItem('dx_cart_id', cartId);
                this.cart = json.cart;
                this.updateUI();
            }

            return json;
        },

        async getCart() {
            const res = await this.api('get');
            return res.cart;
        },

        async addToCart(offerId, qty = 1, metadata = {}) {
            metadata.source_domain = currentDomain;
            metadata.referrer = document.referrer || '';
            const res = await this.api('add', 'POST', {
                offer_id: offerId,
                quantity: qty,
                metadata
            });
            this.openDrawer();
            return res;
        },

        async buyNow(offerId, options = {}) {
            await this.addToCart(offerId, 1, options);
            window.location.href = `${this.origin}/checkout.php`;
        },

        async updateQty(itemId, quantity) {
            return await this.api('update', 'POST', { item_id: itemId, quantity });
        },

        async removeItem(itemId) {
            return await this.api('remove', 'POST', { item_id: itemId });
        },

        async applyCoupon(code) {
            return await this.api('apply_coupon', 'POST', { code });
        },

        async removeCoupon() {
            return await this.api('remove_coupon', 'POST');
        },

        // UI Drawer & Floating Trigger Injection
        injectUI() {
            if (document.getElementById('dx-commerce-root')) return;

            const root = document.createElement('div');
            root.id = 'dx-commerce-root';
            root.innerHTML = `
                <style>
                    #dx-commerce-root {
                        --dx-gold: #D4AF37;
                        --dx-gold-glow: rgba(212, 175, 55, 0.35);
                        --dx-black: #070709;
                        --dx-card-bg: #101116;
                        --dx-border: rgba(212, 175, 55, 0.2);
                        --dx-text: #FFFFFF;
                        --dx-muted: #8E929E;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    }
                    /* Floating Cart Pill */
                    .dx-cart-floating-btn {
                        position: fixed;
                        bottom: 24px;
                        right: 24px;
                        z-index: 99990;
                        background: linear-gradient(135deg, #16171E 0%, #0B0C10 100%);
                        border: 1px solid var(--dx-border);
                        color: #FFF;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.8), 0 0 15px var(--dx-gold-glow);
                        padding: 12px 18px;
                        border-radius: 999px;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        cursor: pointer;
                        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                    }
                    .dx-cart-floating-btn:hover {
                        transform: translateY(-3px) scale(1.03);
                        border-color: var(--dx-gold);
                        box-shadow: 0 14px 40px rgba(0,0,0,0.9), 0 0 25px var(--dx-gold-glow);
                    }
                    .dx-cart-badge {
                        background: var(--dx-gold);
                        color: #000;
                        font-size: 11px;
                        font-weight: 800;
                        padding: 2px 7px;
                        border-radius: 10px;
                    }
                    /* Backdrop */
                    .dx-drawer-backdrop {
                        position: fixed;
                        inset: 0;
                        background: rgba(0,0,0,0.75);
                        backdrop-filter: blur(8px);
                        z-index: 99995;
                        opacity: 0;
                        pointer-events: none;
                        transition: opacity 0.3s ease;
                    }
                    .dx-drawer-backdrop.active {
                        opacity: 1;
                        pointer-events: auto;
                    }
                    /* Slide-over Drawer */
                    .dx-cart-drawer {
                        position: fixed;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        width: 100%;
                        max-width: 440px;
                        background: var(--dx-black);
                        border-left: 1px solid var(--dx-border);
                        z-index: 99999;
                        transform: translateX(100%);
                        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                        display: flex;
                        flex-direction: column;
                        color: var(--dx-text);
                        box-shadow: -15px 0 50px rgba(0,0,0,0.9);
                    }
                    .dx-cart-drawer.active {
                        transform: translateX(0);
                    }
                    .dx-drawer-header {
                        padding: 20px 24px;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        border-bottom: 1px solid rgba(255,255,255,0.08);
                    }
                    .dx-drawer-header h3 {
                        margin: 0;
                        font-size: 18px;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        color: var(--dx-gold);
                        letter-spacing: 0.5px;
                    }
                    .dx-close-btn {
                        background: none;
                        border: none;
                        color: var(--dx-muted);
                        cursor: pointer;
                        padding: 4px;
                        border-radius: 6px;
                    }
                    .dx-close-btn:hover {
                        color: #FFF;
                    }
                    .dx-drawer-body {
                        flex: 1;
                        overflow-y: auto;
                        padding: 20px 24px;
                    }
                    .dx-cart-item {
                        display: flex;
                        gap: 14px;
                        padding: 14px 0;
                        border-bottom: 1px solid rgba(255,255,255,0.06);
                    }
                    .dx-item-details {
                        flex: 1;
                    }
                    .dx-item-title {
                        font-size: 14px;
                        font-weight: 600;
                        margin-bottom: 4px;
                        color: #FFF;
                    }
                    .dx-item-biz {
                        font-size: 11px;
                        color: var(--dx-gold);
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                        margin-bottom: 6px;
                    }
                    .dx-item-price {
                        font-size: 14px;
                        font-weight: 700;
                        color: #FFF;
                    }
                    .dx-item-actions {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        margin-top: 8px;
                    }
                    .dx-qty-btn {
                        background: rgba(255,255,255,0.06);
                        border: 1px solid rgba(255,255,255,0.12);
                        color: #FFF;
                        width: 24px;
                        height: 24px;
                        border-radius: 4px;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .dx-qty-val {
                        font-size: 13px;
                        min-width: 16px;
                        text-align: center;
                    }
                    .dx-item-remove {
                        background: none;
                        border: none;
                        color: #FF5555;
                        cursor: pointer;
                        margin-left: auto;
                        padding: 4px;
                    }
                    .dx-drawer-footer {
                        padding: 20px 24px;
                        border-top: 1px solid rgba(255,255,255,0.08);
                        background: rgba(0,0,0,0.4);
                    }
                    .dx-summary-row {
                        display: flex;
                        justify-content: space-between;
                        font-size: 13px;
                        color: var(--dx-muted);
                        margin-bottom: 8px;
                    }
                    .dx-summary-total {
                        font-size: 18px;
                        font-weight: 700;
                        color: #FFF;
                        border-top: 1px solid rgba(255,255,255,0.1);
                        padding-top: 10px;
                        margin-top: 10px;
                    }
                    .dx-checkout-btn {
                        width: 100%;
                        background: linear-gradient(135deg, #D4AF37 0%, #AA8010 100%);
                        color: #000;
                        border: none;
                        font-size: 15px;
                        font-weight: 700;
                        padding: 14px;
                        border-radius: 10px;
                        cursor: pointer;
                        margin-top: 16px;
                        box-shadow: 0 4px 20px var(--dx-gold-glow);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 8px;
                        text-decoration: none;
                        transition: transform 0.2s, box-shadow 0.2s;
                    }
                    .dx-checkout-btn:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 25px var(--dx-gold-glow);
                    }
                    .dx-empty-cart {
                        text-align: center;
                        padding: 40px 0;
                        color: var(--dx-muted);
                    }
                </style>

                <!-- Floating button -->
                <div class="dx-cart-floating-btn" id="dx-floating-btn">
                    ${getSvg('cart')}
                    <span style="font-weight:600;font-size:13px;">Carrello</span>
                    <span class="dx-cart-badge" id="dx-badge-count">0</span>
                </div>

                <!-- Backdrop & Drawer -->
                <div class="dx-drawer-backdrop" id="dx-backdrop"></div>
                <div class="dx-cart-drawer" id="dx-drawer">
                    <div class="dx-drawer-header">
                        <h3>${getSvg('sparkles')} UNIVERSAL CART</h3>
                        <button class="dx-close-btn" id="dx-close-btn" aria-label="Chiudi">${getSvg('close')}</button>
                    </div>
                    <div class="dx-drawer-body" id="dx-drawer-items">
                        <div class="dx-empty-cart">Il carrello è vuoto</div>
                    </div>
                    <div class="dx-drawer-footer" id="dx-drawer-footer">
                        <div class="dx-summary-row">
                            <span>Subtotale</span>
                            <span id="dx-summary-subtotal">€0.00</span>
                        </div>
                        <div class="dx-summary-row">
                            <span>IVA inclusa / Tasse</span>
                            <span id="dx-summary-tax">€0.00</span>
                        </div>
                        <div class="dx-summary-row dx-summary-total">
                            <span>Totale Ordine</span>
                            <span id="dx-summary-total" style="color:var(--dx-gold);">€0.00</span>
                        </div>
                        <a href="${this.origin}/checkout.php" class="dx-checkout-btn" id="dx-checkout-link">
                            ${getSvg('lock')} Procedi al Checkout Sicuro
                        </a>
                    </div>
                </div>
            `;
            document.body.appendChild(root);

            // Bind UI events
            document.getElementById('dx-floating-btn').addEventListener('click', () => this.openDrawer());
            document.getElementById('dx-close-btn').addEventListener('click', () => this.closeDrawer());
            document.getElementById('dx-backdrop').addEventListener('click', () => this.closeDrawer());
        },

        openDrawer() {
            this.injectUI();
            document.getElementById('dx-drawer').classList.add('active');
            document.getElementById('dx-backdrop').classList.add('active');
        },

        closeDrawer() {
            const d = document.getElementById('dx-drawer');
            const b = document.getElementById('dx-backdrop');
            if (d) d.classList.remove('active');
            if (b) b.classList.remove('active');
        },

        updateUI() {
            this.injectUI();
            const cart = this.cart;
            const badge = document.getElementById('dx-badge-count');
            const itemsContainer = document.getElementById('dx-drawer-items');
            const subtotalEl = document.getElementById('dx-summary-subtotal');
            const taxEl = document.getElementById('dx-summary-tax');
            const totalEl = document.getElementById('dx-summary-total');

            if (!cart || !cart.items || cart.items.length === 0) {
                if (badge) badge.innerText = '0';
                if (itemsContainer) itemsContainer.innerHTML = '<div class="dx-empty-cart">Il carrello è vuoto</div>';
                if (subtotalEl) subtotalEl.innerText = '€0.00';
                if (taxEl) taxEl.innerText = '€0.00';
                if (totalEl) totalEl.innerText = '€0.00';
                return;
            }

            if (badge) badge.innerText = cart.items_count;
            if (subtotalEl) subtotalEl.innerText = `€${parseFloat(cart.subtotal).toFixed(2)}`;
            if (taxEl) taxEl.innerText = `€${parseFloat(cart.tax_total).toFixed(2)}`;
            if (totalEl) totalEl.innerText = `€${parseFloat(cart.total).toFixed(2)}`;

            let html = '';
            cart.items.forEach(item => {
                html += `
                    <div class="dx-cart-item" data-item-id="${item.id}">
                        <div class="dx-item-details">
                            <div class="dx-item-biz">${item.business_name || 'UNIVERSO MIRCO PREGNOLATO'}</div>
                            <div class="dx-item-title">${item.title}</div>
                            <div class="dx-item-price">€${parseFloat(item.unit_price).toFixed(2)}</div>
                            <div class="dx-item-actions">
                                <button class="dx-qty-btn" onclick="window.dxCommerce.updateQty('${item.id}', ${item.quantity - 1})">-</button>
                                <span class="dx-qty-val">${item.quantity}</span>
                                <button class="dx-qty-btn" onclick="window.dxCommerce.updateQty('${item.id}', ${item.quantity + 1})">+</button>
                                <button class="dx-item-remove" onclick="window.dxCommerce.removeItem('${item.id}')" title="Rimuovi">${getSvg('trash')}</button>
                            </div>
                        </div>
                    </div>
                `;
            });
            if (itemsContainer) itemsContainer.innerHTML = html;
        },

        // Auto-bind click listeners on buttons with data-dx attributes
        bindTriggers() {
            document.querySelectorAll('[data-dx-buy]').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    const offerId = el.getAttribute('data-dx-buy');
                    this.buyNow(offerId);
                });
            });

            document.querySelectorAll('[data-dx-cart-add]').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    const offerId = el.getAttribute('data-dx-cart-add');
                    this.addToCart(offerId, 1);
                });
            });
        },

        init() {
            this.injectUI();
            this.bindTriggers();
            this.getCart().catch(() => {});
        }
    };

    window.dxCommerce = dxCommerce;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => dxCommerce.init());
    } else {
        dxCommerce.init();
    }
})(window, document);
