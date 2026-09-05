<?php
/** CHARITY — 10% of the economy to elderly associations (worldwide · Italy national + ASL), 5% to DAO BRANCH forests with Treedom.
 *  The map (same as home) + the full lists + how the money moves. English only. Nothing is paid without a DAO vote. */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
require_once __DIR__ . '/_mappa.php';
demo_esigi();
$IO = demo_io();
$TIT = 'Charity';
$TES = demo_tesoreria_live();
$tesU = (float)bigi_div((string)$TES['usdt'], '1000000');
$A = charity_associazioni(); $IT = charity_italia(); $FO = charity_foreste(); $NO = charity_nodi();
require __DIR__ . '/_testa.php';
?>
<section class="vista on">
  <style>.segm{display:inline-flex;border:1px solid var(--bordo);border-radius:9px;overflow:hidden;flex:none;flex-wrap:wrap}.segm a{color:var(--tenue);font:700 9.5px Inter,sans-serif;letter-spacing:.06em;padding:7px 8px;text-decoration:none;text-transform:uppercase}.segm a.on{background:linear-gradient(135deg,var(--oro),#b8933a);color:#120e07}@media(max-width:520px){.carta:has(>.segm){flex-direction:column;align-items:stretch}.segm{justify-content:center}}</style>
  <?php require_once __DIR__ . '/_media.php'; echo media_hero('img_promo_impact', '124px', ['caption' => 'Every action plants a tree', 'loading' => 'eager']); ?>
  <div class="carta" style="display:flex;gap:10px;align-items:center;padding:10px 12px">
    <div style="flex:1"><div class="eti">Charity of BRANCH</div><div class="sotto">15% of what the economy produces goes out of BRANCH: <b>10% to the elderly</b>, <b>5% to forests</b>. Decided by vote, paid in USDT from the treasury, verifiable on Polygonscan.</div></div>
    <div class="segm"><a class="on" href="#how">How</a><a href="#assoc">Associations</a><a href="#italy">Italy</a><a href="#forest">Forests</a><a href="#nodes">Nodes</a></div>
  </div>

  <div class="quattro" style="margin-bottom:11px">
    <div class="carta" style="margin:0"><div class="eti">Elderly fund</div><div class="medio">10%</div><div class="sub">of tool & membership flows</div></div>
    <div class="carta" style="margin:0"><div class="eti">Forest fund</div><div class="medio">5%</div><div class="sub">Treedom · BLOCKCHAINPLUS.DAO forests</div></div>
    <div class="carta" style="margin:0"><div class="eti">Treasury today</div><div class="medio" style="font-size:16px"><?= e(number_format($tesU, 0, '.', ',')) ?> <small>USDT</small></div><div class="sub">live from Polygon</div></div>
    <div class="carta" style="margin:0"><div class="eti">Paid so far</div><div class="medio">0 <small>USDT</small></div><div class="sub">first vote after V1 · 6 Sep 2026</div></div>
  </div>

  <?= mappa_card('380px') ?>

  <div class="carta" id="how">
    <div class="eti">How the money moves</div>
    <div class="sotto" style="margin-top:6px">
      <b>1 · Accrual.</b> Every month the DAO computes 10% + 5% of the flows that entered the economy (memberships, prestige, mining, vault, staking). The number is written in the ledger and shown here — you can check it against the treasury chart.<br><br>
      <b>2 · Choice.</b> Members vote (DAO page, costs DRX from Rewards) which associations and which forest projects receive that month. The lists below are the <b>candidates</b>: nothing is promised to anyone until an agreement is signed and a vote passes.<br><br>
      <b>3 · Payment.</b> The treasury sends USDT on Polygon. The transaction hash is published in the ledger and in the news of BRANCH. No cash, no intermediaries, no “trust us”: <b>you can see it on Polygonscan</b>.<br><br>
      <b>What it is not.</b> It is not a tax deduction service and not a guarantee for anyone. If a month has zero flows, the fund is zero. Agreements with the associations, the ASL and Treedom are being written now: until they are signed the map shows candidates, not partners.
    </div>
  </div>

  <div class="carta" id="assoc">
    <div class="eti">Elderly associations — worldwide candidates (<?= count($A) ?>)</div>
    <div class="scorri-lista">
    <?php foreach ($A as $a): ?>
      <div class="riga"><div class="mid"><div class="tit2"><?= e($a[0]) ?></div><div class="sub"><?= e($a[1]) ?> · <?= e($a[2]) ?> — <?= e($a[5]) ?></div></div>
        <?php if ($a[6]): ?><a class="val" href="https://<?= e($a[6]) ?>" target="_blank" rel="noopener" style="font-size:11px;color:var(--oro-chiaro)"><?= e($a[6]) ?> ↗</a><?php endif; ?></div>
    <?php endforeach; ?>
    </div>
  </div>

  <div class="carta" id="italy">
    <div class="eti">Italy — national associations (<?= count($IT['nazionali']) ?>) and local health authorities by region (<?= count($IT['asl']) ?>)</div>
    <div class="scorri-lista">
    <?php foreach ($IT['nazionali'] as $a): ?>
      <div class="riga"><div class="mid"><div class="tit2"><?= e($a[0]) ?></div><div class="sub"><?= e($a[1]) ?> — <?= e($a[4]) ?></div></div>
        <?php if ($a[5]): ?><a class="val" href="https://<?= e($a[5]) ?>" target="_blank" rel="noopener" style="font-size:11px;color:var(--oro-chiaro)"><?= e($a[5]) ?> ↗</a><?php endif; ?></div>
    <?php endforeach; ?>
    <?php foreach ($IT['asl'] as $a): ?>
      <div class="riga"><div class="mid"><div class="tit2"><?= e($a[0]) ?> <small class="sub">· <?= e($a[2]) ?></small></div><div class="sub"><?= e($a[1]) ?></div></div><div class="val" style="font-size:10px;color:var(--tenue)">ASL / ATS / ASP</div></div>
    <?php endforeach; ?>
    </div>
    <div class="sub" style="margin-top:6px">Local health authorities receive through their elderly-care services (home care, day centres) under a written agreement — never as a generic donation.</div>
  </div>

  <div class="carta" id="forest">
    <div class="eti">BLOCKCHAINPLUS.DAO forests with Treedom (<?= count($FO) ?>)</div>
    <div class="scorri-lista">
    <?php foreach ($FO as $f): ?>
      <div class="riga"><div class="mid"><div class="tit2"><?= e($f[0]) ?></div><div class="sub"><?= e($f[1]) ?> · <?= e($f[2]) ?> — <?= e($f[5]) ?></div></div>
        <?php if ($f[6]): ?><a class="val" href="https://<?= e($f[6]) ?>" target="_blank" rel="noopener" style="font-size:11px;color:var(--oro-chiaro)"><?= e($f[6]) ?> ↗</a><?php endif; ?></div>
    <?php endforeach; ?>
    </div>
    <div class="sub" style="margin-top:6px">Every tree planted through Treedom is geolocated and photographed: the BRANCH forest will have its own public page with every tree.</div>
  </div>

  <div class="carta" id="nodes">
    <div class="eti">The 118 nodes on the map — 9 World · 27 National · 82 Pro</div>
    <div class="scorri-lista">
    <?php foreach ($NO as $n): ?>
      <div class="riga"><span class="pal" style="font-size:10px">#<?= (int)$n['n'] ?></span><div class="mid"><div class="tit2"><?= e($n['nome']) ?></div><div class="sub"><?= e($n['tipo']) ?><?= $n['nome'] !== $n['citta'] ? ' · ' . e($n['citta']) : '' ?></div></div></div>
    <?php endforeach; ?>
    </div>
    <div class="sub" style="margin-top:6px">Positions are assigned by draw. The city on the map is the seat of the node, not the home of the Pioneer.</div>
  </div>
  <style>.scorri-lista{max-height:320px;overflow-y:auto;scrollbar-width:thin;margin-top:6px;padding-right:4px}.scorri-lista .riga{padding:5px 0}</style>
</section>
<?php require __DIR__ . '/_piede.php'; ?>
