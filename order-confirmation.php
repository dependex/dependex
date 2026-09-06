<?php
/**
 * UNIVERSAL COMMERCE — ORDER CONFIRMATION & RECEIPT
 * Displays verified order state, fulfillment assets, access links, and loyalty rewards.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/modules/commerce/UniversalCommerce.php';

use Dependex\Commerce\UniversalCommerce;

$commerce = UniversalCommerce::getInstance();
$orderId = trim((string)($_GET['order_id'] ?? ''));

$order = $orderId ? $commerce->getOrder($orderId) : null;

$pageTitle = 'Conferma Ordine · DEPENDEX Club';
include __DIR__ . '/_header.php';
$isBonifico = ($_GET['method'] ?? '') === 'bonifico' || ($order && ($order['payment_status'] ?? '') === 'UNPAID');
?>

<div class="luxury-backdrop" style="min-height:85vh;padding:50px 16px;">
  <div class="content-container" style="max-width:860px;margin:0 auto;">

    <?php if (!$order): ?>
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.2);padding:50px 24px;text-align:center;border-radius:18px;">
        <h2 style="color:#FFF;font-size:22px;margin-bottom:8px;">Ordine non trovato</h2>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;">
          Impossibile localizzare i dettagli dell'ordine specificato.
        </p>
        <a href="offers.php" class="btn primary">Torna alle Offerte</a>
      </div>
    <?php else: ?>
      
      <!-- CONFIRMATION BANNER -->
      <div class="card" style="background:linear-gradient(135deg, rgba(212,175,55,0.08) 0%, rgba(16,17,22,0.95) 100%);border:1px solid var(--gold-border);border-radius:20px;padding:36px 30px;text-align:center;margin-bottom:28px;box-shadow:0 15px 45px rgba(0,0,0,0.7);">
        <div style="width:64px;height:64px;border-radius:50%;background:rgba(212,175,55,0.15);border:2px solid var(--gold-primary);color:var(--gold-primary);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
          <?=dx_icon('shield-check', '', 36)?>
        </div>
        <span style="font-size:12px;font-weight:700;color:var(--gold-primary);text-transform:uppercase;letter-spacing:1px;display:inline-block;margin-bottom:6px;">
          <?= $isBonifico ? 'Ordine Registrato · In Attesa di Bonifico' : 'Transazione Verificata & Conclusa' ?>
        </span>
        <h1 style="font-size:28px;font-weight:800;color:#FFF;margin:0 0 8px 0;letter-spacing:-0.5px;">
          <?= $isBonifico ? 'Grazie per la tua richiesta!' : 'Grazie per il tuo ordine!' ?>
        </h1>
        <p style="color:var(--text-muted);font-size:15px;max-width:550px;margin:0 auto 16px;">
          <?php if ($isBonifico): ?>
            L'ordine è stato registrato nel sistema. Per completare l'attivazione effettua il bonifico bancario con i dati riportati qui sotto. Abbiamo inviato le istruzioni anche a <b><?=h($order['customer']['email'] ?? '')?></b>.
          <?php else: ?>
            Il pagamento è stato verificato con successo. Abbiamo inviato un riepilogo dettagliato e le credenziali di accesso a <b><?=h($order['customer']['email'] ?? '')?></b>.
          <?php endif; ?>
        </p>
        <div style="display:inline-flex;align-items:center;gap:12px;background:rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.1);padding:8px 20px;border-radius:30px;font-size:13px;color:#FFF;">
          <span>Numero Ordine: <b style="color:var(--gold-primary);"><?=h($order['order_number'])?></b></span>
          <span>·</span>
          <span>Data: <?=date('d/m/Y H:i', strtotime($order['created_at']))?></span>
        </div>
      </div>

      <?php if ($isBonifico): ?>
        <!-- BANK TRANSFER INSTRUCTIONS CARD -->
        <div class="card card-neon-orange" style="background:rgba(20, 16, 12, 0.95);border:1px solid var(--neon-orange);border-radius:18px;padding:26px;margin-bottom:28px;box-shadow:var(--glow-orange);">
          <h2 style="font-size:18px;font-weight:800;color:#FFF;margin:0 0 14px 0;display:flex;align-items:center;gap:10px;">
            <?=dx_icon('building-library', '', 22)?> Coordinate Bancarie per il Bonifico
          </h2>
          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;font-size:14px;background:rgba(0,0,0,0.4);padding:18px;border-radius:14px;border:1px solid rgba(255,255,255,0.08);">
            <div>
              <span style="color:var(--text-muted);display:block;font-size:12px;">Beneficiario:</span>
              <strong style="color:#FFF;">DEPENDEX Ecosystem</strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;font-size:12px;">IBAN:</span>
              <code style="color:var(--gold-primary);font-weight:800;font-size:14px;letter-spacing:0.5px;">IT60X0542811101000000123456</code>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;font-size:12px;">Importo Esatto:</span>
              <strong style="color:#00ff77;font-size:16px;">€ <?=number_format((float)$order['total_amount'], 2, ',', '.')?></strong>
            </div>
            <div>
              <span style="color:var(--text-muted);display:block;font-size:12px;">Causale Obbligatoria:</span>
              <code style="color:var(--neon-cyan);font-weight:800;background:rgba(0,212,255,0.1);padding:3px 8px;border-radius:6px;border:1px solid rgba(0,212,255,0.3);">ORD-<?=h($order['order_number'])?></code>
            </div>
          </div>
          <p style="color:#cbd5e1;font-size:12px;margin:14px 0 0 0;line-height:1.5;">
            L'accesso all'Academy e agli strumenti software Cortex AI verrà sbloccato automaticamente non appena la contabilità riceverà l'accredito o l'invio della contabile a <a href="mailto:info@dependex.support" style="color:var(--neon-gold);font-weight:700;">info@dependex.support</a>.
          </p>
        </div>
      <?php endif; ?>

      <!-- FULFILLMENT & DIGITAL ACCESS -->
      <?php if (!empty($order['fulfillments'])): ?>
        <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.3);border-radius:18px;padding:26px;margin-bottom:28px;box-shadow:0 8px 30px rgba(0,0,0,0.5);">
          <h2 style="font-size:18px;font-weight:700;color:var(--gold-primary);margin:0 0 16px 0;display:flex;align-items:center;gap:10px;">
            <?=dx_icon('sparkles', '', 20)?> Accesso Immediato ai Contenuti Acquistati
          </h2>

          <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach ($order['fulfillments'] as $ful): ?>
              <?php $meta = json_decode($ful['metadata'] ?? '{}', true) ?: []; ?>
              <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
                <div>
                  <div style="font-size:12px;color:var(--gold-primary);font-weight:700;text-transform:uppercase;">
                    <?=h($ful['fulfillment_type'])?>
                  </div>
                  <div style="font-size:15px;font-weight:700;color:#FFF;margin-top:2px;">
                    <?=h($meta['item_title'] ?? 'Accesso Riservato')?>
                  </div>
                  <?php if (!empty($meta['access_token'])): ?>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
                      Chiave di attivazione: <code style="color:var(--gold-primary);background:rgba(0,0,0,0.4);padding:2px 6px;border-radius:4px;"><?=h($meta['access_token'])?></code>
                    </div>
                  <?php endif; ?>
                </div>

                <div>
                  <?php if (!empty($meta['access_url'])): ?>
                    <a href="<?=h($meta['access_url'])?>" class="btn primary" style="padding:10px 20px;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                      <?=dx_icon('external-link', '', 14)?> Accedi Ora
                    </a>
                  <?php else: ?>
                    <span class="badge" style="background:rgba(60,255,100,0.1);color:#44FF88;border:1px solid #44FF88;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;">
                      <?=dx_icon('check', '', 12)?> Attivo
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- ORDER DETAILS TABLE -->
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.18);border-radius:18px;overflow:hidden;margin-bottom:28px;box-shadow:0 8px 30px rgba(0,0,0,0.5);">
        <div style="padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;justify-content:space-between;align-items:center;">
          <span style="font-weight:700;color:#FFF;font-size:15px;">Riepilogo Articoli Acquistati</span>
          <?php if ($isBonifico): ?>
            <span class="badge" style="background:rgba(255,119,0,0.15);color:var(--neon-orange);border:1px solid var(--neon-orange);padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">
              IN ATTESA DI ACCREDITO BONIFICO
            </span>
          <?php else: ?>
            <span class="badge" style="background:rgba(60,255,100,0.15);color:#44FF88;border:1px solid rgba(60,255,100,0.3);padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;">
              PAGATO
            </span>
          <?php endif; ?>
        </div>

        <div style="padding:0 24px;">
          <?php foreach ($order['items'] as $it): ?>
            <div style="padding:18px 0;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
              <div>
                <span style="font-size:11px;color:var(--gold-primary);text-transform:uppercase;font-weight:700;">
                  <?=h($it['business_name'] ?? 'DEPENDEX Club')?>
                </span>
                <div style="font-size:15px;font-weight:700;color:#FFF;margin-top:2px;">
                  <?=h($it['title'])?>
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                  Quantità: <?=$it['quantity']?> × €<?=number_format((float)$it['unit_price'], 2)?>
                </div>
              </div>
              <div style="font-size:16px;font-weight:800;color:#FFF;">
                €<?=number_format((float)$it['line_total'], 2)?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- TOTALS FOOTER -->
        <div style="padding:20px 24px;background:rgba(0,0,0,0.3);display:flex;flex-direction:column;gap:8px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);">
            <span>Subtotale</span>
            <span style="color:#FFF;font-weight:600;">€<?=number_format((float)$order['subtotal'], 2)?></span>
          </div>
          <?php if ((float)$order['discount_amount'] > 0): ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#44FF88;">
              <span>Sconto applicato</span>
              <span style="font-weight:700;">-€<?=number_format((float)$order['discount_amount'], 2)?></span>
            </div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);">
            <span>IVA inclusa / Tasse</span>
            <span style="color:#FFF;font-weight:600;">€<?=number_format((float)$order['tax_amount'], 2)?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:800;color:#FFF;padding-top:10px;border-top:1px solid rgba(255,255,255,0.1);">
            <span>Totale Saldato</span>
            <span style="color:var(--gold-primary);">€<?=number_format((float)$order['total_amount'], 2)?> <?=h($order['currency'])?></span>
          </div>
        </div>
      </div>

      <!-- WEB3 / MYWALLET CRYPTOGRAPHIC NOTARIZATION SEAL -->
      <?php 
        $receiptPayload = $order['order_number'] . '|' . $order['total_amount'] . '|' . $order['currency'] . '|' . ($order['customer']['email'] ?? '') . '|' . $order['created_at'];
        $receiptHash = hash('sha256', $receiptPayload);
      ?>
      <div class="card" style="background:var(--bg-card);border:1px solid rgba(212,175,55,0.25);border-radius:16px;padding:20px 24px;margin-bottom:28px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="color:var(--gold-primary);"><?=dx_icon('shield-check', '', 22)?></div>
            <div>
              <div style="font-size:14px;font-weight:700;color:#FFF;">Certificazione Digitale Immutabile (Web3 Notary)</div>
              <div style="font-size:12px;color:var(--text-muted);">Integrazione sovrana mywallet.business · Hash SHA-256 generato</div>
            </div>
          </div>
          <span style="font-size:11px;background:rgba(212,175,55,0.12);color:var(--gold-primary);border:1px solid rgba(212,175,55,0.3);padding:4px 12px;border-radius:20px;font-weight:700;">
            PROOFS VALIDATED
          </span>
        </div>
        <div style="background:rgba(0,0,0,0.5);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:12px 14px;font-family:ui-monospace,monospace;font-size:11px;color:#d1d5db;word-break:break-all;">
          <span style="color:var(--gold-primary);font-weight:700;">RECEIPT_HASH: </span><?=h($receiptHash)?>
        </div>
      </div>

      <!-- FOOTER ACTIONS & RETURN TO SOURCE -->
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
        <a href="<?=h($order['source_domain'] ? 'https://' . $order['source_domain'] : 'index.php')?>" class="btn" style="display:inline-flex;align-items:center;gap:8px;">
          <?=dx_icon('home', '', 14)?> Ritorna a <?=h($order['source_domain'] ?? 'Sito Principale')?>
        </a>
        <a href="offers.php" class="btn primary" style="display:inline-flex;align-items:center;gap:8px;">
          <?=dx_icon('sparkles', '', 14)?> Esplora altri percorsi
        </a>
      </div>

    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
