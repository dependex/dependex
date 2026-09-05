<?php
/**
 * UNIVERSAL COMMERCE — CART VIEW
 * Responsive, luxury black & gold aesthetic, multi-domain return link, coupon support.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/modules/commerce/UniversalCommerce.php';

use Dependex\Commerce\UniversalCommerce;

$commerce = UniversalCommerce::getInstance();
$cartToken = $_COOKIE['dx_cart_id'] ?? null;
$cart = $commerce->getOrCreateCart($cartToken);

// Handle direct query actions if needed
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'add' && !empty($_GET['offer_id'])) {
        $offerId = trim((string)$_GET['offer_id']);
        $source = trim((string)($_GET['source'] ?? ''));
        $commerce->addToCart($cart['id'], $offerId, 1, ['source_domain' => $source]);
        header('Location: cart.php');
        exit;
    }
}

$cartData = $commerce->getCart($cart['id']);
$pageTitle = 'Carrello Universale · Mirco Universe';
include __DIR__ . '/_header.php';
?>

<div class="luxury-backdrop" style="min-height:85vh;padding:40px 16px;">
  <div class="content-container" style="max-width:1050px;margin:0 auto;">

    <!-- HEADER / BREADCRUMB -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
      <div>
        <a href="<?=h($_SERVER['HTTP_REFERER'] ?? 'offers.php')?>" style="color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-size:13px;margin-bottom:8px;">
          <?=dx_icon('arrow-left', '', 14)?> Torna allo shopping
        </a>
        <h1 style="font-size:28px;font-weight:800;color:var(--gold-primary);margin:0;letter-spacing:-0.5px;display:flex;align-items:center;gap:12px;">
          <?=dx_icon('shopping-cart', '', 28)?> Carrello Universale
        </h1>
        <p style="color:var(--text-muted);font-size:14px;margin:4px 0 0 0;">
          Ecosistema Mirco Pregnolato · Checkout unico, sicuro e certificato
        </p>
      </div>

      <div style="display:flex;align-items:center;gap:12px;">
        <span class="badge" style="background:rgba(212,175,55,0.1);border:1px solid var(--gold-border);color:var(--gold-primary);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
          <?=dx_icon('shield-check', '', 14)?> Pagamenti Protetti SSL
        </span>
      </div>
    </div>

    <?php if (empty($cartData['items'])): ?>
      <!-- EMPTY CART STATE -->
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);padding:60px 24px;text-align:center;border-radius:18px;box-shadow:0 12px 40px rgba(0,0,0,0.6);">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(212,175,55,0.08);border:1px solid var(--gold-border);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:var(--gold-primary);">
          <?=dx_icon('shopping-cart', '', 36)?>
        </div>
        <h2 style="font-size:22px;color:#FFF;margin:0 0 8px 0;font-weight:700;">Il tuo carrello è attualmente vuoto</h2>
        <p style="color:var(--text-muted);max-width:440px;margin:0 auto 24px;font-size:14px;">
          Esplora i percorsi di trasformazione, i percorsi di sobrietà, le membership esclusive e gli strumenti dell'universo Mirco Pregnolato.
        </p>
        <div style="display:flex;justify-content:center;gap:14px;flex-wrap:wrap;">
          <a href="offers.php" class="btn primary" style="padding:12px 28px;font-weight:700;display:inline-flex;align-items:center;gap:8px;">
            <?=dx_icon('sparkles', '', 16)?> Scopri le Offerte
          </a>
          <a href="academy.php" class="btn" style="padding:12px 28px;display:inline-flex;align-items:center;gap:8px;">
            <?=dx_icon('academic', '', 16)?> Esplora Academy
          </a>
        </div>
      </div>
    <?php else: ?>
      <!-- CART WITH ITEMS -->
      <div style="display:grid;grid-template-columns:1fr 360px;gap:28px;" class="cart-layout-grid">
        
        <!-- ITEMS LIST -->
        <div>
          <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.18);border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.5);">
            <div style="padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;justify-content:space-between;align-items:center;">
              <span style="font-weight:700;color:#FFF;font-size:15px;">Articoli nel Carrello (<?=count($cartData['items'])?>)</span>
              <button type="button" onclick="clearCart()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:12px;display:inline-flex;align-items:center;gap:4px;">
                <?=dx_icon('trash', '', 14)?> Svuota carrello
              </button>
            </div>

            <div style="padding:0 24px;">
              <?php foreach ($cartData['items'] as $item): ?>
                <div style="padding:22px 0;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;" id="cart-row-<?=h($item['id'])?>">
                  
                  <div style="flex:1;min-width:240px;">
                    <span style="display:inline-block;font-size:11px;font-weight:700;color:var(--gold-primary);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:4px;">
                      <?=h($item['business_name'] ?? 'Mirco Universe')?>
                    </span>
                    <h3 style="font-size:17px;font-weight:700;color:#FFF;margin:0 0 6px 0;">
                      <?=h($item['title'])?>
                    </h3>
                    <div style="font-size:12px;color:var(--text-muted);display:flex;gap:12px;align-items:center;">
                      <span>Tipo: <?=h(strtoupper($item['offer_type']))?></span>
                      <span>·</span>
                      <span>Prezzo unitario: €<?=number_format((float)$item['unit_price'], 2)?></span>
                    </div>
                  </div>

                  <!-- QTY CONTROLS -->
                  <div style="display:flex;align-items:center;gap:10px;">
                    <div style="display:inline-flex;align-items:center;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);border-radius:8px;overflow:hidden;">
                      <button type="button" onclick="updateQty('<?=h($item['id'])?>', <?=max(0, $item['quantity'] - 1)?>)" style="background:none;border:none;color:#FFF;padding:8px 12px;cursor:pointer;">
                        <?=dx_icon('minus', '', 12)?>
                      </button>
                      <span style="min-width:28px;text-align:center;font-weight:700;font-size:14px;color:#FFF;">
                        <?=$item['quantity']?>
                      </span>
                      <button type="button" onclick="updateQty('<?=h($item['id'])?>', <?=$item['quantity'] + 1?>)" style="background:none;border:none;color:#FFF;padding:8px 12px;cursor:pointer;">
                        <?=dx_icon('plus', '', 12)?>
                      </button>
                    </div>

                    <div style="min-width:90px;text-align:right;">
                      <div style="font-size:17px;font-weight:800;color:var(--gold-primary);">
                        €<?=number_format((float)$item['line_total'], 2)?>
                      </div>
                    </div>

                    <button type="button" onclick="removeItem('<?=h($item['id'])?>')" style="background:none;border:none;color:#FF5555;padding:6px;cursor:pointer;" title="Elimina">
                      <?=dx_icon('trash', '', 16)?>
                    </button>
                  </div>

                </div>
              <?php endforeach; ?>
            </div>

            <!-- ORIGIN DOMAIN NOTICE -->
            <div style="padding:14px 24px;background:rgba(0,0,0,0.3);font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:8px;">
              <?=dx_icon('globe', '', 14)?>
              <span>Gli acquisti effettuati sono sincronizzati automaticamente col tuo account centrale dell'ecosistema.</span>
            </div>
          </div>
        </div>

        <!-- ORDER SUMMARY & COUPON SIDEBAR -->
        <div>
          <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.22);border-radius:18px;padding:24px;box-shadow:0 10px 35px rgba(0,0,0,0.6);position:sticky;top:90px;">
            <h2 style="font-size:18px;font-weight:700;color:#FFF;margin:0 0 18px 0;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;gap:8px;">
              <?=dx_icon('credit-card', '', 18)?> Riepilogo Ordine
            </h2>

            <!-- COUPON BOX -->
            <div style="margin-bottom:20px;">
              <label for="coupon-code" style="font-size:12px;color:var(--text-muted);display:block;margin-bottom:6px;font-weight:600;">
                Codice Promo / Coupon
              </label>
              <div style="display:flex;gap:8px;">
                <input type="text" id="coupon-code" placeholder="Es. VIP2026" value="<?=h($cartData['coupon_code'] ?? '')?>" style="flex:1;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.15);border-radius:8px;padding:8px 12px;color:#FFF;font-size:13px;text-transform:uppercase;">
                <button type="button" onclick="applyCoupon()" class="btn" style="padding:8px 14px;font-size:12px;font-weight:700;">
                  Applica
                </button>
              </div>
              <div id="coupon-feedback" style="font-size:11px;margin-top:4px;"></div>
            </div>

            <!-- AMOUNTS BREAKDOWN -->
            <div style="display:flex;flex-direction:column;gap:10px;font-size:14px;color:var(--text-muted);margin-bottom:20px;">
              <div style="display:flex;justify-content:space-between;">
                <span>Subtotale</span>
                <span style="color:#FFF;font-weight:600;">€<?=number_format((float)$cartData['subtotal'], 2)?></span>
              </div>

              <?php if ((float)$cartData['discount_total'] > 0): ?>
                <div style="display:flex;justify-content:space-between;color:#44FF88;">
                  <span>Sconto Coupon</span>
                  <span style="font-weight:700;">-€<?=number_format((float)$cartData['discount_total'], 2)?></span>
                </div>
              <?php endif; ?>

              <div style="display:flex;justify-content:space-between;">
                <span>IVA / Tasse</span>
                <span style="color:#FFF;font-weight:600;">€<?=number_format((float)$cartData['tax_total'], 2)?></span>
              </div>

              <div style="display:flex;justify-content:space-between;font-size:19px;font-weight:800;color:#FFF;padding-top:14px;border-top:1px solid rgba(255,255,255,0.1);margin-top:4px;">
                <span>Totale</span>
                <span style="color:var(--gold-primary);">€<?=number_format((float)$cartData['total'], 2)?></span>
              </div>
            </div>

            <!-- CHECKOUT CTA -->
            <a href="checkout.php" class="btn primary" style="width:100%;padding:14px;font-size:15px;font-weight:800;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 25px rgba(212,175,55,0.35);text-decoration:none;">
              <?=dx_icon('lock', '', 16)?> Procedi al Checkout
            </a>

            <div style="margin-top:16px;text-align:center;font-size:11px;color:var(--text-muted);display:flex;align-items:center;justify-content:center;gap:6px;">
              <?=dx_icon('shield-check', '', 14)?> PayPal & Carte Protetti · Garanzia Mirco Universe
            </div>
          </div>
        </div>

      </div>
    <?php endif; ?>

  </div>
</div>

<script>
async function updateQty(itemId, qty) {
  if (qty <= 0) {
    if (!confirm("Rimuovere questo articolo dal carrello?")) return;
  }
  const res = await fetch('api-cart.php?action=update', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ item_id: itemId, quantity: qty })
  });
  if (res.ok) location.reload();
}

async function removeItem(itemId) {
  if (!confirm("Rimuovere questo articolo dal carrello?")) return;
  const res = await fetch('api-cart.php?action=remove', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ item_id: itemId })
  });
  if (res.ok) location.reload();
}

async function clearCart() {
  if (!confirm("Sei sicuro di voler svuotare il carrello?")) return;
  const res = await fetch('api-cart.php?action=clear', { method: 'POST' });
  if (res.ok) location.reload();
}

async function applyCoupon() {
  const code = document.getElementById('coupon-code').value.trim();
  const feedback = document.getElementById('coupon-feedback');
  if (!code) return;

  const res = await fetch('api-cart.php?action=apply_coupon', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ code })
  });
  const data = await res.json();
  if (data.success) {
    feedback.innerHTML = '<span style="color:#44FF88;">' + data.message + '</span>';
    setTimeout(() => location.reload(), 500);
  } else {
    feedback.innerHTML = '<span style="color:#FF5555;">' + (data.error || 'Coupon non valido') + '</span>';
  }
}
</script>

<style>
@media (max-width: 820px) {
  .cart-layout-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php include __DIR__ . '/_footer.php'; ?>
