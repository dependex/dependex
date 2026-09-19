/**
 * ═════════════════════════════════════════════════════════════════════════
 * UNIVERSAL CART & CHECKOUT ENGINE (MODULO COMMERCE)
 * ═════════════════════════════════════════════════════════════════════════
 * Standard Universale per i portali dell'Ecosistema Sovrano.
 * - Supporta PayPal Smart Buttons SDK, Stripe Checkout e Web3 Polygon
 * - Gestione Order Bump dinamico in tempo reale
 * - Auto-branding da tenant_profile.json
 * - Zero dipendenze, ultra-veloce (<18KB), PWA ready.
 */
(function() {
  if (window.__UNIVERSAL_CART_LOADED__) return;
  window.__UNIVERSAL_CART_LOADED__ = true;

  const scriptTag = document.currentScript || document.querySelector('script[src*="universal-cart-checkout"]');
  const customBrand = scriptTag ? scriptTag.getAttribute('data-brand') : 'DEPENDEX';
  const customPrimary = scriptTag ? scriptTag.getAttribute('data-primary') : '#10b981';
  const paypalClientId = scriptTag ? scriptTag.getAttribute('data-paypal-client-id') : 'sb';

  // Configurazione Carrello
  let cart = {
    items: [],
    hasBump: false,
    bumpPrice: 17.00,
    bumpName: 'Accesso Vault Prioritario VIP',
    activeGateway: 'paypal', // 'paypal', 'stripe', 'web3'
    tenant: {
      brand_name: customBrand,
      support_whatsapp: '+393388771737',
      legal_company: 'LABO TECNIC STUDIO di Mirco Pregnolato (P.IVA IT01504180298)'
    }
  };

  // Carica tenant_profile se presente
  fetch('/tenant_profile.json')
    .then(r => r.ok ? r.json() : null)
    .then(tp => {
      if (tp) {
        cart.tenant = Object.assign(cart.tenant, tp);
        if (tp.primary_color) {
          document.documentElement.style.setProperty('--u-cart-primary', tp.primary_color);
        }
      }
    }).catch(() => {});

  // Costruzione DOM Carrello
  function buildCartUI() {
    const backdrop = document.createElement('div');
    backdrop.className = 'u-cart-backdrop';
    backdrop.id = 'u-cart-backdrop';

    const drawer = document.createElement('div');
    drawer.className = 'u-cart-drawer';
    drawer.id = 'u-cart-drawer';
    drawer.innerHTML = `
      <div class="u-cart-header">
        <h3>🛒 Carrello Sicuro &bull; ${cart.tenant.brand_name}</h3>
        <button class="u-cart-close" id="u-cart-close">&times;</button>
      </div>

      <div class="u-cart-body">
        <div id="u-cart-items-container">
          <p style="color:var(--u-cart-muted); text-align:center; padding:30px 0;">Il carrello e vuoto.</p>
        </div>

        <!-- Order Bump -->
        <div class="u-cart-bump" id="u-cart-bump-box" style="display:none;">
          <input type="checkbox" id="u-cart-bump-check">
          <div class="u-cart-bump-text">
            <span class="u-cart-bump-tag">OFFERTA SPECIALE UNICA</span><br>
            <strong>Aggiungi ${cart.bumpName} a soli €${cart.bumpPrice.toFixed(2)}</strong><br>
            <span style="color:var(--u-cart-muted); font-size:12px;">Sblocca contenuti riservati e canali prioritari con risposta garantita in 2h.</span>
          </div>
        </div>

        <!-- Selettore Metodo di Pagamento -->
        <label style="font-size:13px; color:var(--u-cart-muted); font-weight:600; display:block; margin-top:20px;">SELEZIONA METODO DI PAGAMENTO:</label>
        <div class="u-cart-tabs">
          <div class="u-cart-tab active" data-gw="paypal">PayPal</div>
          <div class="u-cart-tab" data-gw="stripe">Carta di Credito</div>
          <div class="u-cart-tab" data-gw="web3">Web3 Crypto</div>
        </div>

        <div id="u-paypal-button-container" style="margin-top:20px;"></div>
      </div>

      <div class="u-cart-footer">
        <div class="u-cart-row">
          <span>Subtotale</span>
          <span id="u-cart-subtotal">€0.00</span>
        </div>
        <div class="u-cart-row" id="u-cart-bump-row" style="display:none;">
          <span>Order Bump VIP</span>
          <span>€${cart.bumpPrice.toFixed(2)}</span>
        </div>
        <div class="u-cart-row u-cart-total">
          <span>Totale da Pagare</span>
          <span id="u-cart-total-amount">€0.00</span>
        </div>
        <button class="u-cart-checkout-btn" id="u-cart-pay-btn">CONFERMA E PAGA ORA</button>
        <div class="u-cart-legal">
          Pagamento crittografato SSL 256-bit &bull; Titolare: ${cart.tenant.legal_company} &bull; Assistenza WhatsApp: +39 338 877 1737
        </div>
      </div>
    `;

    document.body.appendChild(backdrop);
    document.body.appendChild(drawer);

    // Event listeners
    backdrop.addEventListener('click', closeCart);
    document.getElementById('u-cart-close').addEventListener('click', closeCart);

    // Bump check
    const bumpCheck = document.getElementById('u-cart-bump-check');
    bumpCheck.addEventListener('change', (e) => {
      cart.hasBump = e.target.checked;
      renderTotals();
    });

    // Tabs Gateway
    drawer.querySelectorAll('.u-cart-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        drawer.querySelectorAll('.u-cart-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        cart.activeGateway = tab.getAttribute('data-gw');
        renderGatewayView();
      });
    });

    // Checkout click
    document.getElementById('u-cart-pay-btn').addEventListener('click', handleCheckout);
  }

  function renderGatewayView() {
    const paypalBox = document.getElementById('u-paypal-button-container');
    const payBtn = document.getElementById('u-cart-pay-btn');

    if (cart.activeGateway === 'paypal') {
      payBtn.style.display = 'none';
      paypalBox.style.display = 'block';
      loadPayPalSDK();
    } else if (cart.activeGateway === 'stripe') {
      paypalBox.style.display = 'none';
      payBtn.style.display = 'block';
      payBtn.textContent = 'PAGA CON CARTA (STRIPE)';
    } else if (cart.activeGateway === 'web3') {
      paypalBox.style.display = 'none';
      payBtn.style.display = 'block';
      payBtn.textContent = 'PAGA CON CRIPTO (POLYGON)';
    }
  }

  function loadPayPalSDK() {
    const container = document.getElementById('u-paypal-button-container');
    if (!container) return;
    container.innerHTML = '';

    if (!window.paypal) {
      const script = document.createElement('script');
      script.src = `https://www.paypal.com/sdk/js?client-id=${paypalClientId}&currency=EUR`;
      script.onload = initPayPalButtons;
      document.head.appendChild(script);
    } else {
      initPayPalButtons();
    }
  }

  function initPayPalButtons() {
    if (!window.paypal || !document.getElementById('u-paypal-button-container')) return;
    const total = calculateTotal();
    if (total <= 0) return;

    window.paypal.Buttons({
      createOrder: function(data, actions) {
        return actions.order.create({
          purchase_units: [{
            amount: { value: total.toFixed(2), currency_code: 'EUR' },
            description: `${cart.tenant.brand_name} - Ordine Online`
          }]
        });
      },
      onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
          onPaymentSuccess({
            gateway: 'PAYPAL',
            orderId: details.id,
            payerEmail: details.payer.email_address,
            amount: total
          });
        });
      }
    }).render('#u-paypal-button-container');
  }

  function onPaymentSuccess(orderData) {
    // 1. Invio a Mycelium e Database Server
    fetch('/api/paypal_capture.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(orderData)
    }).catch(() => {});

    alert(`Pagamento completato con successo! Grazie ${orderData.payerEmail || ''}. Riceverai l'accesso immediato via email.`);
    cart.items = [];
    cart.hasBump = false;
    closeCart();
  }

  function handleCheckout() {
    const total = calculateTotal();
    if (cart.activeGateway === 'stripe') {
      window.location.href = `/api/checkout_stripe.php?total=${total.toFixed(2)}`;
    } else if (cart.activeGateway === 'web3') {
      if (window.Web3Payment) {
        window.Web3Payment.pay({ amount: total });
      } else {
        alert('Modulo Web3 in caricamento o installa MetaMask.');
      }
    }
  }

  function calculateTotal() {
    const itemsTotal = cart.items.reduce((sum, it) => sum + (it.price * (it.qty || 1)), 0);
    return itemsTotal + (cart.hasBump ? cart.bumpPrice : 0);
  }

  function renderTotals() {
    const total = calculateTotal();
    const itemsTotal = cart.items.reduce((sum, it) => sum + (it.price * (it.qty || 1)), 0);

    const subEl = document.getElementById('u-cart-subtotal');
    const totEl = document.getElementById('u-cart-total-amount');
    const bumpRow = document.getElementById('u-cart-bump-row');

    if (subEl) subEl.textContent = `€${itemsTotal.toFixed(2)}`;
    if (totEl) totEl.textContent = `€${total.toFixed(2)}`;
    if (bumpRow) bumpRow.style.display = cart.hasBump ? 'flex' : 'none';

    if (cart.activeGateway === 'paypal') {
      loadPayPalSDK();
    }
  }

  function renderCartItems() {
    const container = document.getElementById('u-cart-items-container');
    const bumpBox = document.getElementById('u-cart-bump-box');
    if (!container) return;

    if (cart.items.length === 0) {
      container.innerHTML = '<p style="color:var(--u-cart-muted); text-align:center; padding:30px 0;">Il carrello e vuoto.</p>';
      if (bumpBox) bumpBox.style.display = 'none';
      renderTotals();
      return;
    }

    if (bumpBox) bumpBox.style.display = 'flex';

    container.innerHTML = cart.items.map((it, idx) => `
      <div class="u-cart-item">
        <div class="u-cart-item-info">
          <h4>${escapeHtml(it.name)}</h4>
          <span>€${(it.price * (it.qty || 1)).toFixed(2)}</span>
        </div>
        <button class="u-cart-remove" data-idx="${idx}">&times; Rimuovi</button>
      </div>
    `).join('');

    container.querySelectorAll('.u-cart-remove').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const idx = parseInt(e.target.getAttribute('data-idx'));
        cart.items.splice(idx, 1);
        renderCartItems();
      });
    });

    renderTotals();
  }

  function openCart() {
    document.getElementById('u-cart-backdrop').classList.add('active');
    document.getElementById('u-cart-drawer').classList.add('active');
    renderCartItems();
    renderGatewayView();
  }

  function closeCart() {
    document.getElementById('u-cart-backdrop').classList.remove('active');
    document.getElementById('u-cart-drawer').classList.remove('active');
  }

  function escapeHtml(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  // API Pubblica Globale
  window.UniversalCart = {
    open: openCart,
    close: closeCart,
    addItem: function(product) {
      cart.items.push(product);
      openCart();
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    buildCartUI();

    // Aggancia tutti i pulsanti con classe .u-buy-btn
    document.querySelectorAll('.u-buy-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const p = {
          id: btn.getAttribute('data-product-id') || 'prod_default',
          name: btn.getAttribute('data-name') || 'Prodotto Selezionato',
          price: parseFloat(btn.getAttribute('data-price') || '97.00'),
          qty: 1
        };
        window.UniversalCart.addItem(p);
      });
    });
  });
})();
