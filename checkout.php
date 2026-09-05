<?php
/**
 * UNIVERSAL COMMERCE — CHECKOUT PAGE
 * Guest-first checkout with PayPal Smart Buttons (Card & PayPal), server-side verification,
 * legal compliance, and multi-domain attribution.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/modules/commerce/UniversalCommerce.php';

use Dependex\Commerce\UniversalCommerce;
use Dependex\Commerce\CommerceEnv;

$commerce = UniversalCommerce::getInstance();
$cartToken = $_COOKIE['dx_cart_id'] ?? null;
$cart = $commerce->getOrCreateCart($cartToken);
$cartData = $commerce->getCart($cart['id']);

// If cart is empty, redirect to cart
if (empty($cartData['items'])) {
    header('Location: cart.php');
    exit;
}

$u = current_user();
$paypalClientId = CommerceEnv::get('PAYPAL_CLIENT_ID', '');
$paypalMode = CommerceEnv::get('PAYPAL_MODE', 'live');

$pageTitle = 'Checkout Sicuro · Mirco Universe';
include __DIR__ . '/_header.php';
?>

<div class="luxury-backdrop" style="min-height:90vh;padding:40px 16px;">
  <div class="content-container" style="max-width:1100px;margin:0 auto;">

    <!-- TOP BAR -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:30px;flex-wrap:wrap;gap:14px;">
      <div>
        <a href="cart.php" style="color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:13px;margin-bottom:6px;">
          <?=dx_icon('arrow-left', '', 14)?> Modifica carrello
        </a>
        <h1 style="font-size:28px;font-weight:800;color:var(--gold-primary);margin:0;letter-spacing:-0.5px;display:flex;align-items:center;gap:10px;">
          <?=dx_icon('lock', '', 26)?> Checkout Sicuro & Crittografato
        </h1>
        <p style="color:var(--text-muted);font-size:14px;margin:4px 0 0 0;">
          Completamento transazione protetta su circuito certificato
        </p>
      </div>

      <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;background:rgba(255,255,255,0.04);padding:6px 14px;border-radius:20px;border:1px solid rgba(255,255,255,0.1);">
          <?=dx_icon('shield-check', '', 15)?> Crittografia SSL 256-bit
        </span>
      </div>
    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div style="display:grid;grid-template-columns:1fr 400px;gap:32px;" class="checkout-layout-grid">

      <!-- LEFT: CUSTOMER DETAILS & BILLING FORM -->
      <div>
        <form id="checkoutForm" onsubmit="return false;">
          
          <!-- STEP 1: CONTACT INFO -->
          <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);border-radius:18px;padding:26px;margin-bottom:24px;box-shadow:0 8px 30px rgba(0,0,0,0.5);">
            <h2 style="font-size:17px;font-weight:700;color:#FFF;margin:0 0 18px 0;display:flex;align-items:center;gap:10px;">
              <span style="width:24px;height:24px;border-radius:50%;background:var(--gold-primary);color:#000;font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">1</span>
              Dati del Cliente & Accesso
            </h2>

            <div style="margin-bottom:16px;">
              <label for="cust_email" style="display:block;font-size:13px;font-weight:600;color:#FFF;margin-bottom:6px;">
                Indirizzo Email (ricezione ricevuta e accessi) *
              </label>
              <input type="email" id="cust_email" name="email" required
                value="<?=h($u['email'] ?? '')?>"
                placeholder="nome@dominio.it"
                style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 14px;color:#FFF;font-size:14px;">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
              <div>
                <label for="cust_fname" style="display:block;font-size:13px;font-weight:600;color:#FFF;margin-bottom:6px;">Nome *</label>
                <input type="text" id="cust_fname" name="first_name" required
                  value="<?=h($u['display_name'] ?? '')?>"
                  placeholder="Mario"
                  style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 14px;color:#FFF;font-size:14px;">
              </div>
              <div>
                <label for="cust_lname" style="display:block;font-size:13px;font-weight:600;color:#FFF;margin-bottom:6px;">Cognome *</label>
                <input type="text" id="cust_lname" name="last_name" required
                  placeholder="Rossi"
                  style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 14px;color:#FFF;font-size:14px;">
              </div>
            </div>

            <div>
              <label for="cust_phone" style="display:block;font-size:13px;font-weight:600;color:var(--text-muted);margin-bottom:6px;">
                Numero di Telefono (Opzionale per supporto dedicato)
              </label>
              <input type="tel" id="cust_phone" name="phone"
                placeholder="+39 340 0000000"
                style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 14px;color:#FFF;font-size:14px;">
            </div>
          </div>

          <!-- STEP 2: BILLING ADDRESS & FISCAL DATA -->
          <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);border-radius:18px;padding:26px;margin-bottom:24px;box-shadow:0 8px 30px rgba(0,0,0,0.5);">
            <h2 style="font-size:17px;font-weight:700;color:#FFF;margin:0 0 18px 0;display:flex;align-items:center;gap:10px;">
              <span style="width:24px;height:24px;border-radius:50%;background:var(--gold-primary);color:#000;font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">2</span>
              Indirizzo di Fatturazione
            </h2>

            <div style="margin-bottom:14px;">
              <label for="bill_street" style="display:block;font-size:13px;font-weight:600;color:#FFF;margin-bottom:6px;">Indirizzo e N. Civico</label>
              <input type="text" id="bill_street" name="billing_street" placeholder="Via / Piazza, 12"
                style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 14px;color:#FFF;font-size:14px;">
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;margin-bottom:18px;">
              <div>
                <label for="bill_city" style="display:block;font-size:13px;font-weight:600;color:#FFF;margin-bottom:6px;">Città</label>
                <input type="text" id="bill_city" name="billing_city" placeholder="Roma"
                  style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 14px;color:#FFF;font-size:14px;">
              </div>
              <div>
                <label for="bill_postal" style="display:block;font-size:13px;font-weight:600;color:#FFF;margin-bottom:6px;">CAP</label>
                <input type="text" id="bill_postal" name="billing_postal_code" placeholder="00100"
                  style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 14px;color:#FFF;font-size:14px;">
              </div>
              <div>
                <label for="bill_country" style="display:block;font-size:13px;font-weight:600;color:#FFF;margin-bottom:6px;">Paese</label>
                <select id="bill_country" name="billing_country" style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:12px 8px;color:#FFF;font-size:14px;">
                  <option value="IT" selected>Italia</option>
                  <option value="CH">Svizzera</option>
                  <option value="SM">San Marino</option>
                  <option value="FR">Francia</option>
                  <option value="DE">Germania</option>
                  <option value="ES">Spagna</option>
                  <option value="GB">Regno Unito</option>
                  <option value="US">Stati Uniti</option>
                  <option value="OTHER">Altro</option>
                </select>
              </div>
            </div>

            <!-- FISCAL TOGGLE -->
            <details style="border-top:1px solid rgba(255,255,255,0.08);padding-top:14px;margin-top:14px;">
              <summary style="cursor:pointer;color:var(--gold-primary);font-size:13px;font-weight:600;user-select:none;">
                + Richiedi fattura aziendale / inserisci Partita IVA o Codice Fiscale
              </summary>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px;">
                <div>
                  <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px;">Ragione Sociale</label>
                  <input type="text" id="company_name" name="company_name" placeholder="Azienda Srl"
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:8px;padding:10px;color:#FFF;font-size:13px;">
                </div>
                <div>
                  <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px;">Partita IVA</label>
                  <input type="text" id="vat_number" name="vat_number" placeholder="IT12345678901"
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:8px;padding:10px;color:#FFF;font-size:13px;">
                </div>
                <div>
                  <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px;">Codice Fiscale</label>
                  <input type="text" id="fiscal_code" name="fiscal_code" placeholder="RSSMRA80A01H501U"
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:8px;padding:10px;color:#FFF;font-size:13px;">
                </div>
                <div>
                  <label style="display:block;font-size:12px;color:var(--text-muted);margin-bottom:4px;">Codice SDI o PEC</label>
                  <input type="text" id="sdi_pec" name="sdi_pec" placeholder="M5UXCR1 / pec@azienda.it"
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.18);border-radius:8px;padding:10px;color:#FFF;font-size:13px;">
                </div>
              </div>
            </details>
          </div>

          <!-- STEP 3: LEGAL & CONSENT -->
          <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);border-radius:18px;padding:26px;margin-bottom:24px;box-shadow:0 8px 30px rgba(0,0,0,0.5);">
            <h2 style="font-size:17px;font-weight:700;color:#FFF;margin:0 0 16px 0;display:flex;align-items:center;gap:10px;">
              <span style="width:24px;height:24px;border-radius:50%;background:var(--gold-primary);color:#000;font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">3</span>
              Consensi & Termini Legali
            </h2>

            <div style="display:flex;flex-direction:column;gap:12px;">
              <label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#FFF;cursor:pointer;">
                <input type="checkbox" id="legal_terms" name="terms_accepted" value="1" required checked style="margin-top:3px;accent-color:var(--gold-primary);">
                <span>
                  Ho letto e accetto i <a href="terms.php" target="_blank" style="color:var(--gold-primary);text-decoration:underline;">Termini e Condizioni di Vendita</a> e l'<a href="privacy.php" target="_blank" style="color:var(--gold-primary);text-decoration:underline;">Informativa sulla Privacy</a>. *
                </span>
              </label>

              <label style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--text-muted);cursor:pointer;">
                <input type="checkbox" id="legal_marketing" name="marketing_accepted" value="1" style="margin-top:3px;accent-color:var(--gold-primary);">
                <span>
                  (Facoltativo) Desidero ricevere comunicazioni riservate, aggiornamenti sui percorsi e inviti esclusivi agli eventi del Mirco Universe.
                </span>
              </label>
            </div>
          </div>

          <!-- STEP 4: PAYMENT SELECTION (PAYPAL & CREDIT CARD) -->
          <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.3);border-radius:18px;padding:26px;box-shadow:0 12px 40px rgba(0,0,0,0.7);">
            <h2 style="font-size:17px;font-weight:700;color:#FFF;margin:0 0 8px 0;display:flex;align-items:center;gap:10px;">
              <span style="width:24px;height:24px;border-radius:50%;background:var(--gold-primary);color:#000;font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">4</span>
              Metodo di Pagamento Certificato
            </h2>
            <p style="color:var(--text-muted);font-size:13px;margin:0 0 20px 0;">
              Paga in sicurezza con conto PayPal oppure con qualsiasi Carta di Credito/Debito (Visa, Mastercard, Amex).
            </p>

            <!-- Error message container -->
            <div id="checkout-alert" style="display:none;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:16px;"></div>

            <!-- PAYPAL PAY IN 3 RATE / PAYLATER BANNER -->
            <div data-pp-message data-pp-placement="payment" data-pp-amount="<?=number_format((float)$cartData['total'], 2, '.', '')?>" data-pp-style-layout="text" data-pp-style-text-color="white" style="margin-bottom:14px;padding:8px 12px;background:rgba(212,175,55,0.06);border:1px solid rgba(212,175,55,0.2);border-radius:8px;font-size:12px;"></div>

            <!-- PAYPAL BUTTON CONTAINER -->
            <div id="paypal-button-container" style="min-height:50px;"></div>

            <!-- FALLBACK IF SDK UNAVAILABLE -->
            <div id="paypal-loading" style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px;">
              <?=dx_icon('lock', '', 18)?> Caricamento gateway di pagamento certificato...
            </div>
          </div>

        </form>
      </div>

      <!-- RIGHT: ORDER SUMMARY STICKY SIDEBAR -->
      <div>
        <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.22);border-radius:18px;padding:24px;box-shadow:0 10px 35px rgba(0,0,0,0.6);position:sticky;top:90px;">
          <h2 style="font-size:17px;font-weight:700;color:#FFF;margin:0 0 16px 0;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;gap:8px;">
            <?=dx_icon('shopping-cart', '', 18)?> Riepilogo Ordine
          </h2>

          <div style="max-height:280px;overflow-y:auto;margin-bottom:16px;padding-right:4px;">
            <?php foreach ($cartData['items'] as $it): ?>
              <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:13px;">
                <div style="flex:1;">
                  <div style="color:#FFF;font-weight:600;"><?=h($it['title'])?></div>
                  <div style="color:var(--gold-primary);font-size:11px;">Qtà: <?=$it['quantity']?> · <?=h($it['business_name'])?></div>
                </div>
                <div style="color:#FFF;font-weight:700;white-space:nowrap;">
                  €<?=number_format((float)$it['line_total'], 2)?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- TOTALS -->
          <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:var(--text-muted);padding-top:12px;border-top:1px solid rgba(255,255,255,0.08);">
            <div style="display:flex;justify-content:space-between;">
              <span>Subtotale</span>
              <span style="color:#FFF;font-weight:600;">€<?=number_format((float)$cartData['subtotal'], 2)?></span>
            </div>

            <?php if ((float)$cartData['discount_total'] > 0): ?>
              <div style="display:flex;justify-content:space-between;color:#44FF88;">
                <span>Sconto applicato</span>
                <span style="font-weight:700;">-€<?=number_format((float)$cartData['discount_total'], 2)?></span>
              </div>
            <?php endif; ?>

            <div style="display:flex;justify-content:space-between;">
              <span>IVA inclusa</span>
              <span style="color:#FFF;font-weight:600;">€<?=number_format((float)$cartData['tax_total'], 2)?></span>
            </div>

            <div style="display:flex;justify-content:space-between;font-size:19px;font-weight:800;color:#FFF;padding-top:14px;border-top:1px solid rgba(255,255,255,0.12);margin-top:6px;">
              <span>Totale da Pagare</span>
              <span style="color:var(--gold-primary);">€<?=number_format((float)$cartData['total'], 2)?></span>
            </div>
          </div>

          <div style="margin-top:24px;padding:16px;background:rgba(212,175,55,0.05);border:1px solid var(--gold-border);border-radius:12px;">
            <div style="display:flex;align-items:center;gap:8px;color:var(--gold-primary);font-weight:700;font-size:13px;margin-bottom:6px;">
              <?=dx_icon('shield-check', '', 16)?> Garanzia Mirco Universe
            </div>
            <p style="font-size:11px;color:var(--text-muted);margin:0;line-height:1.4;">
              Accesso immediato ai contenuti digitali e ricevuta fiscale certificata inviata via email subito dopo la verifica del pagamento.
            </p>
          </div>

        </div>
      </div>

    </div>

  </div>
</div>

<!-- PAYPAL JS SDK with PayLater and Card components -->
<script src="https://www.paypal.com/sdk/js?client-id=<?=urlencode($paypalClientId)?>&currency=EUR&intent=capture&components=buttons,messages&enable-funding=paylater,card"></script>

<script>
let internalOrderId = null;

function showAlert(msg, isError = true) {
  const box = document.getElementById('checkout-alert');
  box.style.display = 'block';
  box.style.background = isError ? 'rgba(255,60,60,0.15)' : 'rgba(60,255,100,0.15)';
  box.style.border = isError ? '1px solid #FF4444' : '1px solid #44FF88';
  box.style.color = isError ? '#FF8888' : '#66FFAA';
  box.innerText = msg;
}

function validateForm() {
  const email = document.getElementById('cust_email').value.trim();
  const fname = document.getElementById('cust_fname').value.trim();
  const terms = document.getElementById('legal_terms').checked;

  if (!email || !email.includes('@')) {
    showAlert('Inserisci un indirizzo email valido.');
    document.getElementById('cust_email').focus();
    return false;
  }
  if (!fname) {
    showAlert('Inserisci il tuo nome.');
    document.getElementById('cust_fname').focus();
    return false;
  }
  if (!terms) {
    showAlert('È necessario accettare i Termini e Condizioni e la Privacy Policy.');
    return false;
  }
  return true;
}

function getFormData() {
  return {
    email: document.getElementById('cust_email').value.trim(),
    first_name: document.getElementById('cust_fname').value.trim(),
    last_name: document.getElementById('cust_lname').value.trim(),
    phone: document.getElementById('cust_phone').value.trim(),
    billing_street: document.getElementById('bill_street').value.trim(),
    billing_city: document.getElementById('bill_city').value.trim(),
    billing_postal_code: document.getElementById('bill_postal').value.trim(),
    billing_country: document.getElementById('bill_country').value,
    company_name: document.getElementById('company_name').value.trim(),
    vat_number: document.getElementById('vat_number').value.trim(),
    fiscal_code: document.getElementById('fiscal_code').value.trim(),
    sdi_pec: document.getElementById('sdi_pec').value.trim(),
    terms_accepted: document.getElementById('legal_terms').checked ? 1 : 0,
    privacy_accepted: document.getElementById('legal_terms').checked ? 1 : 0,
    marketing_accepted: document.getElementById('legal_marketing').checked ? 1 : 0,
    source_domain: window.location.hostname
  };
}

if (window.paypal) {
  document.getElementById('paypal-loading').style.display = 'none';

  paypal.Buttons({
    style: {
      layout: 'vertical',
      color: 'gold',
      shape: 'rect',
      label: 'paypal',
      tagline: false
    },

    // 1. Create order on SERVER side
    createOrder: async function(data, actions) {
      if (!validateForm()) {
        throw new Error('Campi obbligatori mancanti');
      }

      const payload = getFormData();
      payload.action = 'create_paypal_order';

      const response = await fetch('api-checkout.php?action=create_paypal_order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const orderData = await response.json();

      if (!orderData.success || !orderData.paypal_order_id) {
        showAlert(orderData.error || 'Impossibile inizializzare l\'ordine PayPal.');
        throw new Error(orderData.error || 'PayPal Init Failed');
      }

      internalOrderId = orderData.internal_order_id;
      return orderData.paypal_order_id;
    },

    // 2. Capture and verify on SERVER side
    onApprove: async function(data, actions) {
      showAlert('Verifica e registrazione pagamento in corso...', false);

      const captureResponse = await fetch('api-checkout.php?action=capture_paypal_order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          paypal_order_id: data.orderID,
          internal_order_id: internalOrderId
        })
      });

      const captureData = await captureResponse.json();

      if (captureData.success && captureData.redirect_url) {
        window.location.href = captureData.redirect_url;
      } else {
        showAlert(captureData.error || 'Verifica pagamento fallita. Contatta il supporto con ID: ' + data.orderID);
      }
    },

    onCancel: function(data) {
      showAlert('Hai annullato la procedura di pagamento PayPal. I tuoi articoli sono ancora nel carrello.');
    },

    onError: function(err) {
      console.error('PayPal Buttons Error:', err);
      showAlert('Si è verificato un errore durante la connessione con PayPal: ' + (err.message || 'Riprova tra poco'));
    }
  }).render('#paypal-button-container');

} else {
  document.getElementById('paypal-loading').innerHTML = '<span style="color:#FF6666;">Errore caricamento SDK PayPal. Ricarica la pagina.</span>';
}
</script>

<style>
@media (max-width: 860px) {
  .checkout-layout-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php include __DIR__ . '/_footer.php'; ?>
