<?php
/** DEPOSIT — solo l'indirizzo e il QR. Rete Polygon, e basta. */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
$IO = demo_io();
$D = demo_indirizzo_deposito($IO);
$ADDR = $D['indirizzo'];
$G = demo_gettone();
$TIT = 'Deposit';
require __DIR__ . '/_testa.php';
?>
<section class="vista on" style="max-width:520px">
  <div class="carta" style="text-align:center;border-color:rgba(217,180,90,.45)">
    <div class="eti">Send USDT here</div>
    <div class="medio" style="justify-content:center;font-size:15px;margin:6px 0 2px;color:var(--oro-chiaro)"><?= dric_gettone('USDT', 26) ?> USDT · <b>POLYGON</b> network only</div>
    <div class="sotto" style="color:#f2dba4;font-weight:600">Polygon (POL) network. USDT sent from any other network is lost.</div>
    <div id="qr" style="background:#fff;padding:14px;border-radius:16px;width:236px;margin:16px auto 10px;display:grid;place-items:center"></div>
    <div id="addr" style="font:700 12.5px/1.6 Inter,monospace;word-break:break-all;color:var(--oro-chiaro);padding:0 4px"><?= e($ADDR) ?></div>
    <button type="button" class="b pieno" onclick="copia('<?= e($ADDR) ?>',this)"><?= dric_ui('copia', 18) ?>Copy address</button>
    <div class="sotto" style="margin-top:12px">
      <?php if ($D['personale']): ?>This address belongs to <b style="color:var(--oro-chiaro)">your account</b> (position #<?= (int)$D['posto'] ?>). Whatever arrives here is yours.
      <?php else: ?>Deposit address of BLOCKCHAINPLUS.DAO. Credit is matched to your account.<?php endif; ?>
      1 USDT = 1 DUX, credited after network confirmations.</div>
  </div>
  <a class="b" href="wallet.php" style="text-decoration:none"><?= dric_ui('wallet', 16) ?>Back to wallet</a>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>(function(){var q=document.getElementById('qr');if(q&&window.QRCode)new QRCode(q,{text:<?= json_encode($ADDR) ?>,width:208,height:208,colorDark:'#000',colorLight:'#fff',correctLevel:QRCode.CorrectLevel.M});})();</script>
<?php require __DIR__ . '/_piede.php'; ?>
