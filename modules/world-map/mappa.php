<?php
/** WORLD MAP — pagina intera: la mappa 2D/3D di BLOCKCHAINPLUS.DAO (associations, forests, nodes) a tutta altezza. */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
$IO = demo_io();
$TIT = 'World Map';
require __DIR__ . '/_testa.php';
require_once __DIR__ . '/_mappa.php';
?>
<section class="vista on">
  <?php require_once __DIR__ . '/_media.php'; echo media_hero('img_world_map_hero', '150px', ['caption' => 'The world of BLOCKCHAINPLUS.DAO', 'loading' => 'eager']); ?>
  <style>.segm{display:inline-flex;border:1px solid var(--bordo);border-radius:9px;overflow:hidden}.segm button{background:transparent;border:0;color:var(--tenue);font:700 9px Inter,sans-serif;letter-spacing:.08em;padding:6px 8px;cursor:pointer}.segm button.on{background:linear-gradient(135deg,var(--oro),#b8933a);color:#120e07}</style>
  <div class="carta" style="display:flex;gap:10px;align-items:center;padding:10px 12px;margin-bottom:11px">
    <div style="flex:1"><div class="eti">BLOCKCHAINPLUS.DAO World Map</div><div class="sotto">Planisphere or globe, countries in gold wire. Four layers: the elderly associations we support, the forests planted, the 118 nodes and the global view. Tap a point for its card · drag to move · pinch or wheel to zoom · CO₂ saved counts live.</div></div>
    <a class="b mini" href="app.php">← Home</a>
  </div>
  <?= mappa_card('calc(100vh - 330px)', '140vw') ?>
  <div class="sub" style="margin-top:8px">Data from the public charity registry of the dapp: no wallets, no personal data.</div>
</section>
<?php require __DIR__ . '/_piede.php'; ?>
