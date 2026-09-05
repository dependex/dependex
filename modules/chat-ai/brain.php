<?php
/** NEURAL CORTEX 3D — pagina intera (tool giocabile): il cervello dell'ecosistema a schermo pieno dentro la dapp. */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
$IO = demo_io();
$TIT = 'Neural Cortex';
require __DIR__ . '/_testa.php';
?>
<section class="vista on">
  <div class="carta" style="display:flex;gap:10px;align-items:center;padding:10px 12px;margin-bottom:11px">
    <div style="flex:1"><div class="eti">Neural Cortex 3D</div><div class="sotto">A human brain in gold wireframe. Every neuron is a real object of BLOCKCHAINPLUS.DAO — members, tokens, tools, ranks, ledger movements, the 118 nodes, the charity projects — and every synapse a real relation. Impulses travel along the synapses; tap a neuron to fire it and light up its connections. Drag = rotate · wheel/pinch = zoom · double tap = focus · keyboard: arrows, +/−, space, R.</div></div>
    <a class="b mini" href="prodotti.php">← Products</a>
  </div>
  <div style="position:relative;height:calc(100vh - 250px);min-height:460px;border-radius:14px;overflow:hidden;border:1px solid rgba(217,180,90,.35);background:#050505">
    <iframe src="brain-3d.php" title="Neural Cortex 3D" style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block" allow="fullscreen"></iframe>
  </div>
  <div class="sub" style="margin-top:8px">No balances, no wallets, no e-mails inside the brain: only what is already public in the dapp. Data refresh every 5 seconds.</div>
</section>
<?php require __DIR__ . '/_piede.php'; ?>
