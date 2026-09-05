<?php
require_once 'bootstrap.php'; $public=isset($_GET['public']); if(!$public) $u=require_login(); else $u=null;
$pageTitle='Network';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">Network Tree</span><h1>La rete che cresce</h1><p>Ogni Club è un ramo. Ogni moltiplicazione genera un nuovo ramo senza perdere la storia.</p></div>
<div class="segmented"><button data-network-view="tree" class="active">Albero</button><button data-network-view="graph">Grafo</button></div></section>
<div class="network-tools"><input id="networkSearch" placeholder="Cerca Club, città, regione…"><select id="regionFilter"><option value="">Tutta Italia</option></select></div>
<section class="network-stage"><svg id="networkSvg" viewBox="0 0 1200 760" role="img" aria-label="Mappa network Club"></svg><div id="nodeCard" class="node-card hidden"></div></section>
<section class="card"><h2>Legenda</h2><div class="legend"><span>● Nazionale</span><span>● Regionale</span><span>● Territoriale</span><span>● Club</span></div></section>
<script>window.NETWORK_PUBLIC=<?= $public?'true':'false' ?>;window.NETWORK_SCOPE='ITALY';</script>
<?php require '_footer.php';?>