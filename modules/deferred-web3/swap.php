<?php
/** SWAP & GAMIFICATION — DUX/DRX/81X interni, fee 0, e lo sblocco degli 81X in DUX vincolati. */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
require_once __DIR__ . '/_azioni.php';
demo_esigi();
demo_semina();
$IO = demo_io();
demo_azioni($IO, 'swap.php');
$S = demo_stato($IO); $G = demo_gettone();
$vinc = demo_saldo_vincolato($IO);
$POL = led_db()->query('SELECT da,a,num,den,fee_bp,acceso FROM led_swap_politica')->fetchAll();
require __DIR__ . '/_testa.php';
?>
<section class="vista on">
  <div class="carta">
    <div class="eti" style="margin-bottom:8px">Balances</div>
    <?php foreach (['DUX','DRX','81X'] as $t): ?>
      <div class="riga"><?= dric_gettone($t, 28) ?><div class="mid"><div class="tit2"><?= $t ?></div></div><div class="val"><?= e(soldi($S['saldi'][$t], $t)) ?></div></div>
    <?php endforeach; ?>
    <div class="riga riga-off"><?= dric_gettone('DUX', 28) ?><div class="mid"><div class="tit2">DUX <span class="tag-off">offset</span></div><div class="sub">membership only</div></div><div class="val"><?= e(soldi($vinc, 'DUX')) ?></div></div>
  </div>

  <div class="carta">
    <div class="eti">Swap — fee 0</div>
    <form method="post" data-pin="Confirm the swap"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="swap">
      <div class="due">
        <div><label for="da">From</label><select id="da" name="da"><option>DUX</option><option>DRX</option><option>81X</option></select></div>
        <div><label for="ad">To</label><select id="ad" name="ad"><option>DRX</option><option>DUX</option><option>81X</option></select></div>
      </div>
      <label for="q">Amount</label><input id="q" name="q" type="number" step="0.000001" min="0.000001" value="10" required>
      <div class="sotto" id="tasso" style="margin-top:8px"></div>
      <button class="b pieno"><?= dric_ui('lucchetto', 16) ?>Swap — asks your PIN</button>
    </form>
    <div class="franco">Rates: <?php foreach ($POL as $p) echo '<b>' . e($p['da']) . '→' . e($p['a']) . '</b> ' . e((string)$p['num']) . ':' . e((string)$p['den']) . ($p['acceso'] ? '' : ' (off)') . ' · '; ?>fee 0 everywhere. All internal, instant, no gas.</div>
  </div>

  <div class="carta">
    <div class="eti">Gamification — unlock 81X into membership DUX</div>
    <div class="sotto" style="margin:6px 0 4px">1 81X → 100 <span style="color:#e9e2d3">restricted DUX</span>. Offset DUX are a voucher: up to 10% off each activation. They never leave the dapp.</div>
    <form method="post" data-pin="Confirm the unlock"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="gamif">
      <label for="g">81X to unlock</label><input id="g" name="q" type="number" step="0.000001" min="1" value="1" required>
      <div class="sotto" id="gOut" style="margin-top:8px">→ 100 restricted DUX</div>
      <button class="b"><?= dric_ui('lucchetto', 16) ?>Unlock — asks your PIN</button>
    </form>
    <div class="sotto" style="margin-top:8px"><a href="restricted.php" style="color:var(--oro-chiaro)">Go to Restricted DUX →</a></div>
  </div>
</section>
<script>
(function(){var R={};<?php foreach ($POL as $p) echo 'R["' . e($p['da']) . '>' . e($p['a']) . '"]=[' . (int)$p['num'] . ',' . (int)$p['den'] . ',' . (int)$p['acceso'] . '];'; ?>
 var da=document.getElementById('da'),ad=document.getElementById('ad'),q=document.getElementById('q'),t=document.getElementById('tasso');
 function agg(){var k=da.value+'>'+ad.value;var r=R[k];if(da.value===ad.value){t.textContent='Pick two different tokens.';return;}if(!r){t.textContent='No direct pair — swap through DUX.';return;}if(!r[2]){t.textContent='This pair is switched off.';return;}
  var v=(parseFloat(q.value)||0)*r[0]/r[1];t.innerHTML='You get <b style="color:var(--oro-chiaro)">'+v.toLocaleString('en-US',{maximumFractionDigits:6})+' '+ad.value+'</b> · rate '+r[0]+':'+r[1]+' · fee 0';}
 [da,ad,q].forEach(function(x){x.addEventListener('input',agg);x.addEventListener('change',agg);});agg();
 var g=document.getElementById('g');g.addEventListener('input',function(){document.getElementById('gOut').textContent='→ '+(Math.floor(parseFloat(g.value)||0)*100).toLocaleString('en-US')+' restricted DUX';});
})();
</script>
<?php require __DIR__ . '/_piede.php'; ?>
