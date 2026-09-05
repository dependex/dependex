<?php
/* ============================================================================
   GENESYS/WALLET-BRANCO — il Wallet del Branco, pagina utente
   Destino Randagio · 2026-08-01 · destinazione: genesys/wallet-branco.php

   COSA MOSTRA (flusso di Mirco, punto 4):
   - indirizzo Polygon on-chain (link Polygonscan, bottone copia, QR in SVG
     generato IN LOCALE da walletlib — nessun CDN esterno);
   - saldo POL live (RPC con cache breve: gli RPC pubblici non si martellano);
   - saldi MIRROR letti dai MOTORI REALI del sito: drx_balance (drx.php),
     x81_balance (dr-swap.php), dr_wbal (dr-swap.php, colonna usdt/eur di
     wallet_balances). NESSUN secondo ledger: qui si LEGGE e basta;
   - le transazioni di funding gas (wallet_fundings);
   - il box seed: la seed NON e' visibile qui, MAI. E' stata inviata via
     email una volta sola. Guida import MetaMask passo-passo.

   Privacy: noindex,nofollow (dati personali on-chain). dr_log all'apertura.
============================================================================ */

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/dr-log.php';
require_once __DIR__ . '/wallet-hybrid.php';
require_once __DIR__ . '/_dr-seo.php';
require_once __DIR__ . '/_dr-footer.php';

/* motori reali dei saldi interni (guardati: se un include manca, fallback
   sulla STESSA tabella che quel motore usa — mai un ledger nuovo) */
@include_once dirname(__DIR__) . '/drx.php';       // drx_balance()
@include_once dirname(__DIR__) . '/dr-swap.php';   // x81_balance(), dr_wbal()

if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (empty($_SESSION['uid'])) { header('Location: ../accedi.php'); exit; }
$uid = (int)$_SESSION['uid'];

wh_tables($pdo);
dr_log($pdo, 'pagina', 'view', ['pagina'=>'wallet-branco'], $uid);

$w = wh_get_wallet($pdo, $uid);
$pol = $w ? wh_pol_balance($w['address']) : null;

/* ---- saldi mirror dai motori reali. Se un motore non e' includibile (raro),
   si legge la STESSA tabella che quel motore usa: mai un secondo ledger. ---- */
function wb_sql1(PDO $pdo, string $sql, int $uid): float {
  try { $st = $pdo->prepare($sql); $st->execute([$uid]); return (float)($st->fetchColumn() ?: 0); }
  catch (Throwable $e) { return 0.0; }
}
try { $drx = function_exists('drx_balance') ? (float)drx_balance($pdo, $uid)
           : wb_sql1($pdo, "SELECT balance FROM drx_balances WHERE uid=?", $uid); }
catch (Throwable $e) { $drx = wb_sql1($pdo, "SELECT balance FROM drx_balances WHERE uid=?", $uid); }
try { $x81 = function_exists('x81_balance') ? (float)x81_balance($pdo, $uid)
           : wb_sql1($pdo, "SELECT bal FROM x81_balances WHERE uid=?", $uid); }
catch (Throwable $e) { $x81 = wb_sql1($pdo, "SELECT bal FROM x81_balances WHERE uid=?", $uid); }
$wb = ['eur'=>0.0, 'usdt'=>0.0];
try {
  if (function_exists('dr_wbal')) { $wb = dr_wbal($pdo, $uid); }
  else {
    $st = $pdo->prepare("SELECT eur,usdt FROM wallet_balances WHERE uid=?"); $st->execute([$uid]);
    if ($r = $st->fetch(PDO::FETCH_ASSOC)) $wb = ['eur'=>(float)$r['eur'], 'usdt'=>(float)$r['usdt']];
  }
} catch (Throwable $e) {}

/* ---- transazioni funding gas ---- */
$fund = [];
try {
  $st = $pdo->prepare("SELECT order_id,amount_pol,tx_hash,status,created,updated
                       FROM wallet_fundings WHERE uid=? ORDER BY id DESC LIMIT 20");
  $st->execute([$uid]); $fund = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

/* ---- QR SVG locale (bacon-qr-code dentro walletlib): opzionale, mai fatale */
$qr = '';
if ($w) {
  try {
    $writer = new \BaconQrCode\Writer(
      new \BaconQrCode\Renderer\ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(148, 1),
        new \BaconQrCode\Renderer\Image\SvgImageBackEnd()));
    $qr = $writer->writeString($w['address']);
    $qr = preg_replace('/^<\?xml[^>]*\?>\s*/', '', $qr);
  } catch (Throwable $e) { $qr = ''; }
}

function e2($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$nf = fn($v, $d=2) => number_format((float)$v, $d, ',', '.');
?><!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php dr_seo_head([
  'title'  => 'Il tuo Wallet del Branco — Destino Randagio',
  'desc'   => 'Wallet Polygon personale del Pioniere: indirizzo on-chain, POL, DRX, 81X, USDT.',
  'url'    => 'https://destinorandagio.it/genesys/wallet-branco.php',
  'robots' => 'noindex,nofollow',   /* PRIVATA: indirizzi e saldi personali, mai su Google */
]); ?>
<style>
  body{background:#0D0B0A;color:#EDE3CE;font-family:Georgia,'Times New Roman',serif;margin:0}
  .wb-wrap{max-width:980px;margin:0 auto;padding:34px 16px 60px}
  h1{color:#D4AF37;font-weight:600;letter-spacing:.05em;font-size:1.7rem;margin:0 0 4px}
  .wb-sub{color:#8a7a55;font-size:.85rem;margin:0 0 26px}
  .wb-card{border:1px solid #3a3125;border-radius:16px;background:linear-gradient(180deg,#151210,#100D0B);padding:20px 22px;margin-bottom:16px}
  .wb-card--oro{border-color:#D4AF37}
  .wb-tit{color:#D4AF37;font-size:.8rem;letter-spacing:.12em;margin:0 0 12px;text-transform:uppercase}
  .wb-addr{font-family:Consolas,Menlo,monospace;font-size:.95rem;word-break:break-all;color:#EDE3CE}
  .wb-fila{display:flex;gap:18px;flex-wrap:wrap;align-items:center}
  .wb-qr{background:#fff;border-radius:12px;padding:6px;line-height:0;flex:0 0 auto}
  .wb-bt{display:inline-block;border:1px solid #D4AF37;color:#D4AF37;background:transparent;border-radius:999px;padding:7px 16px;font-size:.8rem;cursor:pointer;text-decoration:none;font-family:inherit}
  .wb-bt:hover{background:#D4AF37;color:#0D0B0A}
  .wb-griglia{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
  .wb-saldo{border:1px solid #3a3125;border-radius:12px;padding:14px 16px;background:#0f0d0b}
  .wb-saldo b{display:block;font-size:1.35rem;color:#EDE3CE;margin-top:4px;font-family:Consolas,Menlo,monospace}
  .wb-saldo span{color:#8a7a55;font-size:.75rem;letter-spacing:.1em}
  .wb-note{color:#bfae8c;font-size:.82rem;line-height:1.65}
  table.wb-tx{width:100%;border-collapse:collapse;font-size:.82rem}
  .wb-tx th{color:#8a7a55;text-align:left;font-weight:normal;letter-spacing:.08em;padding:6px 8px;border-bottom:1px solid #3a3125}
  .wb-tx td{padding:8px;border-bottom:1px solid #221d17;font-family:Consolas,Menlo,monospace}
  .st-confirmed{color:#8fd18f}.st-pending{color:#e8c76a}.st-failed,.st-created{color:#e08585}
  ol.wb-guida li{margin-bottom:9px}
  .wb-alert{border:1px solid #7a2e2e;background:#140d0d;border-radius:12px;padding:14px 16px;color:#d8c9ab;font-size:.84rem;line-height:1.65}
</style>
</head>
<body>
<div class="wb-wrap">
  <h1>♛ Il tuo Wallet del Branco</h1>
  <p class="wb-sub">Polygon on-chain + saldi interni dell'ecosistema. Un lupo solo sopravvive, un Branco prospera.</p>

<?php if (!$w): ?>
  <div class="wb-card wb-card--oro">
    <p class="wb-tit">Wallet in preparazione</p>
    <p class="wb-note">Il tuo wallet on-chain non risulta ancora creato. Succede agli account nati prima
    del sistema wallet: verra' generato a breve in automatico (backfill). Se hai fretta, scrivi a
    <a href="mailto:info@dependex.social" style="color:#D4AF37">info@dependex.social</a>.</p>
  </div>
<?php else: ?>
  <div class="wb-card wb-card--oro">
    <p class="wb-tit">Indirizzo Polygon on-chain</p>
    <div class="wb-fila">
      <?php if ($qr): ?><div class="wb-qr"><?= $qr ?></div><?php endif; ?>
      <div style="flex:1;min-width:240px">
        <p class="wb-addr" id="wbAddr"><?= e2($w['address']) ?></p>
        <p style="margin:14px 0 0;display:flex;gap:10px;flex-wrap:wrap">
          <button class="wb-bt" type="button" onclick="wbCopia()">📋 Copia indirizzo</button>
          <a class="wb-bt" target="_blank" rel="noopener"
             href="https://polygonscan.com/address/<?= e2($w['address']) ?>">Vedi su Polygonscan ↗</a>
        </p>
        <p class="wb-note" style="margin:12px 0 0">Percorso di derivazione: <?= e2($w['derivation_path']) ?> (lo standard di MetaMask).</p>
      </div>
    </div>
  </div>

  <div class="wb-card">
    <p class="wb-tit">I tuoi saldi</p>
    <div class="wb-griglia">
      <div class="wb-saldo"><span>POL · on-chain</span>
        <b><?= $pol ? e2($pol['pol']) : '—' ?></b>
        <?php if(!$pol): ?><small class="wb-note">rete non raggiungibile ora, riprova</small><?php endif; ?>
      </div>
      <div class="wb-saldo"><span>DRX · interno</span><b><?= $nf($drx, 0) ?></b></div>
      <div class="wb-saldo"><span>81X · interno</span><b><?= $nf($x81, 2) ?></b></div>
      <div class="wb-saldo"><span>USDT · interno</span><b><?= $nf($wb['usdt'], 2) ?></b></div>
    </div>
    <p class="wb-note" style="margin:12px 0 0">I saldi DRX/81X/USDT sono i tuoi saldi interni reali del sito
    (gli stessi della dashboard): questo wallet li rispecchia, non li duplica.</p>
  </div>

  <div class="wb-card">
    <p class="wb-tit">Movimenti gas del Branco</p>
    <?php if (!$fund): ?>
      <p class="wb-note">Nessun movimento ancora. Quando il tuo Kit Genesys viene confermato,
      il Branco ti invia 1 POL di gas: lo vedrai qui e su Polygonscan.</p>
    <?php else: ?>
      <table class="wb-tx">
        <tr><th>Quando</th><th>Ordine</th><th>POL</th><th>Stato</th><th>Transazione</th></tr>
        <?php foreach ($fund as $f): ?>
        <tr>
          <td><?= e2($f['created']) ?></td>
          <td><?= e2(mb_strimwidth((string)$f['order_id'], 0, 22, '…')) ?></td>
          <td><?= $nf($f['amount_pol'], 2) ?></td>
          <td class="st-<?= e2($f['status']) ?>"><?= e2($f['status']) ?></td>
          <td><?php if ($f['tx_hash']): ?>
            <a style="color:#D4AF37" target="_blank" rel="noopener"
               href="https://polygonscan.com/tx/<?= e2($f['tx_hash']) ?>"><?= e2(substr($f['tx_hash'],0,12)) ?>…</a>
          <?php else: ?>—<?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>

  <div class="wb-alert">
    <b style="color:#e08585">🔑 La tua seed phrase (12 parole)</b><br>
    Ti e' stata inviata <b>via email UNA sola volta, alla creazione del wallet</b>.
    Non e' visibile in questa pagina e non lo sara' mai: nessuno del Branco puo' rileggertela,
    e <b>nessuno di Destino Randagio te la chiedera' MAI</b>. Se non l'hai gia' fatto:
    trascrivila su carta, conservala offline e cancella quell'email.
  </div>

  <div class="wb-card">
    <p class="wb-tit">Portarlo in MetaMask — passo passo</p>
    <ol class="wb-guida wb-note">
      <li>Installa <b>MetaMask</b> da <span style="font-family:monospace">metamask.io</span> (estensione o app). Mai da link ricevuti in chat.</li>
      <li>Scegli <b>«Importa un wallet esistente»</b>.</li>
      <li>Inserisci le <b>12 parole</b> ricevute via email, nell'ordine esatto, tutte minuscole.</li>
      <li>Crea la password locale di MetaMask (protegge solo quel dispositivo).</li>
      <li>Aggiungi la rete <b>Polygon</b> (in MetaMask: Reti → Aggiungi → Polygon Mainnet, chainId 137).</li>
      <li>Controlla che l'indirizzo mostrato sia <b>esattamente</b> quello qui sopra: se coincide, il wallet e' tuo anche fuori dal sito.</li>
    </ol>
  </div>
<?php endif; ?>
</div>

<script>
function wbCopia(){
  var t = document.getElementById('wbAddr'); if(!t) return;
  var v = t.textContent.trim();
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(v).then(function(){ wbOk(); });
  } else {
    var ta = document.createElement('textarea'); ta.value = v; document.body.appendChild(ta);
    ta.select(); try { document.execCommand('copy'); } catch(e){} document.body.removeChild(ta); wbOk();
  }
}
function wbOk(){
  var b = document.querySelector('.wb-bt'); if(!b) return;
  var old = b.textContent; b.textContent = '✓ Copiato'; setTimeout(function(){ b.textContent = old; }, 1600);
}
</script>

<?php dr_footer_luxury(''); ?>
</body>
</html>
