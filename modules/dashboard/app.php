<?php
/**
 * DEMO DAO BRANCH — l'applicazione
 * Ogni pulsante scrive nel registro vero. I numeri girano davvero.
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
demo_semina();

$IO = demo_io();

/* ================================================================
   LE AZIONI — tutte in POST, tutte col gettone, tutte nel registro
   ================================================================ */
require_once __DIR__ . '/_azioni.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['a'] ?? '', ['conferma','reset'], true)) {
    if (!demo_gettone_ok()) { demo_dico('no', 'Session expired.'); header('Location: app.php'); exit; }
    if ($_POST['a'] === 'conferma') { $_SESSION['conferma'][$IO] = time(); demo_dico('ok', 'Quarterly figures confirmed. Rank hold released.'); }
    else { @unlink(LED_DB); @unlink(LED_DB . '-wal'); @unlink(LED_DB . '-shm'); unset($_SESSION['conferma'], $_SESSION['fascia']); demo_dico('ok', 'Demo ledger wiped and reseeded.'); }
    header('Location: app.php'); exit;
}
demo_azioni($IO, 'app.php');   // tutte le altre azioni: un solo posto per tutta la dapp

/* ================================================================ dati */
try { $S  = demo_stato($IO); }
catch (Throwable $x) {
    // Un 500 muto sull'host e' costato un pomeriggio: qui si dice cosa manca.
    header('Content-Type: text/plain; charset=utf-8'); http_response_code(500);
    echo "BLOCKCHAINPLUS.DAO demo — the ledger could not start.\n\n", $x->getMessage(), "\n\n",
         "Open diagnosi.php: it lists every missing file.\n"; exit;
}
$CL = demo_classifica();
$V  = led_verifica();
$MOV = led_estratto($IO);
$CR  = demo_cruscotto($IO);
$PRODOTTI = demo_prodotti($IO);
$CODA = led_db()->query("SELECT * FROM led_coda ORDER BY id DESC LIMIT 8")->fetchAll();
$G  = demo_gettone();
$FASCIA = $_SESSION['fascia'] ?? 'None';

$SALDO_DUX = soldi($S['saldi']['DUX'], 'DUX');
$vale = bigi_add($S['saldi']['DUX'], $S['saldi']['USDT']);
if (isset($_GET['tes'])) { header('Content-Type: application/json');
    // V4 (17-08-2026): &t=shield|flow|recovery sceglie una delle tre tesorerie; senza t = comportamento di prima (Treasury 2 / Flow)
    $__T4 = demo_tesorerie(); $__k = (string)($_GET['t'] ?? ''); $__addr = isset($__T4[$__k]) ? $__T4[$__k]['addr'] : null;
    $__t = demo_tesoreria_live($__addr); $__g = demo_tesoreria_serie_grafico(max(1, min(1825, (int)$_GET['tes'])), $__addr); $__s = $__g['serie']; if ($__t['ok']) $__s[] = [time(), (float)bigi_div((string)$__t['usdt'], '1000000')]; $__g['serie'] = $__s; $__g['t'] = $__k; echo json_encode($__g); exit; }
require __DIR__ . '/_testa.php';
?>


<!-- WOW layer: lucciole oro sullo sfondo della home + numeri che contano; leggero, si spegne con prefers-reduced-motion -->
<canvas id="lucciole" style="position:fixed;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;opacity:.32"></canvas>
<div id="aurora" aria-hidden="true"><i></i><i></i><i></i></div>
<style>main{position:relative;z-index:1}.medio.conta{transition:none}@media(prefers-reduced-motion:reduce){#lucciole{display:none}#aurora{display:none}}
/* WOW: aurora d'oro che respira dietro le card, ingresso a scalare delle card, luccichio sui numeri grandi, rilievo al passaggio */
#aurora{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;filter:blur(70px);opacity:.55}
#aurora i{position:absolute;border-radius:50%;background:radial-gradient(circle,rgba(217,180,90,.35),rgba(217,180,90,0) 70%);width:60vmax;height:60vmax;animation:aur 26s ease-in-out infinite alternate}
#aurora i:nth-child(1){left:-20vmax;top:-10vmax}#aurora i:nth-child(2){right:-25vmax;top:30vh;animation-duration:33s;background:radial-gradient(circle,rgba(242,219,164,.22),rgba(242,219,164,0) 70%)}#aurora i:nth-child(3){left:20vw;bottom:-30vmax;animation-duration:41s}
@keyframes aur{0%{transform:translate(0,0) scale(1)}50%{transform:translate(8vw,-6vh) scale(1.15)}100%{transform:translate(-6vw,5vh) scale(.95)}}
main .carta{animation:entra .7s cubic-bezier(.2,.8,.2,1) both}
main .carta:nth-of-type(2){animation-delay:.06s}main .due>.carta:nth-child(2){animation-delay:.1s}main .quattro>.carta:nth-child(2){animation-delay:.08s}main .quattro>.carta:nth-child(3){animation-delay:.14s}main .quattro>.carta:nth-child(4){animation-delay:.2s}
@keyframes entra{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
[data-conta],.medio.oro-lucc,#tesCard>div>div>.medio{background:linear-gradient(100deg,#f2e9d8 0%,#f2dba4 35%,#fff7dd 50%,#d9b45a 65%,#f2e9d8 100%);background-size:250% 100%;-webkit-background-clip:text;background-clip:text;color:transparent;animation:lucc 6s linear infinite}
@keyframes lucc{to{background-position:-250% 0}}
@media(hover:hover){main .carta{transition:transform .25s ease,box-shadow .25s ease}main .carta:hover{transform:translateY(-2px);box-shadow:0 14px 34px -14px rgba(217,180,90,.55),0 0 0 1px rgba(242,219,164,.35) inset}}
</style>
<script>
(function(){var c=document.getElementById('lucciole');if(!c||matchMedia('(prefers-reduced-motion:reduce)').matches)return;var x=c.getContext('2d'),W,H,P=[],N=innerWidth<640?22:48;
function rs(){W=c.width=innerWidth*devicePixelRatio;H=c.height=innerHeight*devicePixelRatio;}rs();addEventListener('resize',rs);
for(var i=0;i<N;i++)P.push({x:Math.random(),y:Math.random(),r:(1+Math.random()*2.2)*devicePixelRatio,v:(0.02+Math.random()*0.06)/100,f:Math.random()*6.28,s:0.01+Math.random()*0.02});
(function t(){x.clearRect(0,0,W,H);for(var i=0;i<N;i++){var p=P[i];p.y-=p.v;p.f+=p.s;if(p.y<-0.02){p.y=1.02;p.x=Math.random();}var a=0.25+0.55*(0.5+0.5*Math.sin(p.f));var g=x.createRadialGradient(p.x*W,p.y*H,0,p.x*W,p.y*H,p.r*4);g.addColorStop(0,'rgba(242,219,164,'+a+')');g.addColorStop(1,'rgba(217,180,90,0)');x.fillStyle=g;x.beginPath();x.arc(p.x*W,p.y*H,p.r*4,0,6.28);x.fill();}requestAnimationFrame(t);})();
/* numeri che contano: turnover e tesoreria */
document.querySelectorAll('[data-conta]').forEach(function(el){var fin=parseFloat(el.dataset.conta),dec=parseInt(el.dataset.dec||'0'),pre=el.dataset.pre||'',suf=el.dataset.suf||'',t0=null;function f(t){if(!t0)t0=t;var k=Math.min(1,(t-t0)/1400);k=1-Math.pow(1-k,3);el.textContent=pre+(fin*k).toLocaleString('en-US',{minimumFractionDigits:dec,maximumFractionDigits:dec})+suf;if(k<1)requestAnimationFrame(f);}requestAnimationFrame(f);});
})();
</script>
<!-- ================= HOME (V1 GENESYS layout, 17 ago) ================= -->
<section class="vista on" data-v="dashboard">
  <?php
    $nome = demo_nome($IO); $primo = !empty($_SESSION['benvenuto']); unset($_SESSION['benvenuto']);
    $NFT = (int)(demo_persone()[$IO]['nft'] ?? 0); $PL = (int)($S['prestigio']['livello'] ?? 0);
    $TES = demo_tesoreria_live(); $TT = demo_totali_token($IO); $TL = demo_totali_tool($IO);
    if (!function_exists('mod_missioni')) require_once __DIR__ . '/moduli.php'; mod_tab();
    $MIS = mod_missioni(); $mk = array_keys($MIS)[(int)gmdate('z') % count($MIS)]; $MI = $MIS[$mk]; $misFatta = mod_missione_fatta($IO, $mk); $misOk = $MI[3]($IO);
    $tesU = bigi_div($TES['usdt'], '1000000');
  ?>
  <?php require_once __DIR__ . '/_media.php'; echo media_hero('img_promo_ecosystem', '128px', ['caption' => 'One ecosystem. Grow your impact.', 'loading' => 'eager']); ?>
  <div class="due" style="margin-bottom:11px">
    <div class="carta benv<?= $primo ? ' primo' : '' ?>" style="margin:0;display:flex;gap:10px;align-items:center">
      <span class="lupoW<?= $NFT ? '' : ' bianco' ?>"><?= dric_lupo($PL, 44) ?></span>
      <div style="flex:1;min-width:0"><div class="eti">Welcome<?= $primo ? '' : ' back' ?></div>
        <div class="medio" style="font-size:19px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($nome) ?></div>
        <div class="sotto" style="font-style:italic;line-height:1.35"><?= e(demo_frase_giorno()) ?></div></div>
    </div>
    <?php require_once __DIR__ . '/_media.php'; $__RK = [0=>'plankton',1=>'shrimp',2=>'crab',3=>'octopus',4=>'fish',5=>'dolphin',6=>'shark',7=>'whale',8=>'humpback',9=>'leviathan']; $__lvb = (int)$S['rango']['livello']; ?>
    <?php $__cr = media_get('rank_' . ($__RK[$__lvb] ?? 'plankton') . '_hero'); $__bg = ($__cr && media_has($__cr, 'source_original')) ? "background-image:linear-gradient(90deg,rgba(8,7,5,.94) 40%,rgba(8,7,5,.35)),url('" . e(media_url((string)$__cr['source_original'])) . "');background-size:cover;background-position:right center;" : ''; ?>
    <div class="carta" style="margin:0;display:flex;flex-direction:column;justify-content:center;gap:4px;<?= $__bg ?>">
      <div class="eti">Rank</div>
      <?php $da = (float)($S['rango']['soglia_attuale'] ?? 0); $al = (float)($S['rango']['xp_prossimo'] ?? 0); $ora = (float)$S['xp']['totale']; $pc = $al > $da ? max(0, min(100, 100 * ($ora - $da) / ($al - $da))) : 100; $lv = (int)$S['rango']['livello']; ?>
      <div style="display:flex;align-items:center;gap:6px">
        <div style="display:flex;align-items:center;gap:6px;min-width:0"><?= dric_rango($lv, 34) ?><?= $PL > 0 ? dric_prestigio($PL, $NFT, 26) : '' ?><b style="font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($S['rango']['nome']) ?></b></div>
        <div style="flex:1"></div>
        <div style="display:flex;align-items:center;gap:4px;min-width:0;opacity:.75"><b style="font-size:11.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e((string)($S['rango']['prossimo'] ?? 'Top')) ?></b><?= $S['rango']['prossimo'] ? dric_rango($lv + 1, 20) : dric_ui('corona', 18) ?></div>
      </div>
      <div class="barra" style="margin:2px 0"><i style="width:<?= number_format($pc, 1, '.', '') ?>%"></i></div>
      <div class="sub" style="display:flex;justify-content:space-between"><span><?= number_format($pc, 0) ?>%</span><span><?= $al ? dollari($S['rango']['manca']) . ' to go' : 'top rank' ?></span></div>
    </div>
    <?php $PRM = demo_promo(); if (!empty($PRM['on'])): ?>
    <a class="carta promo viva" href="prodotti.php" style="margin:0;display:flex;flex-direction:column;justify-content:center;gap:4px;text-decoration:none;color:inherit">
      <div class="eti" style="display:flex;align-items:center;gap:6px"><span class="livepill">PROMO</span><?= e($PRM['titolo']) ?></div>
      <div class="sotto" style="line-height:1.35"><?= e($PRM['testo']) ?></div>
      <div class="sub" style="display:flex;gap:8px;flex-wrap:wrap"><?php if ((int)$PRM['boost'] > 1): ?><b style="color:var(--oro-chiaro)">×<?= (int)$PRM['boost'] ?> booster</b><?php endif; ?><?php if ($PRM['fino']): ?><span>until <?= e($PRM['fino']) ?></span><?php endif; ?><span style="margin-left:auto;color:var(--oro-chiaro)">see products ›</span></div>
    </a>
    <?php endif; ?>
  </div>
  <style>.due:has(>.promo){grid-template-columns:repeat(3,minmax(0,1fr))}@media(max-width:700px){.due:has(>.promo){grid-template-columns:1fr 1fr}.due:has(>.promo)>.promo{grid-column:1/-1}}
    .carta.promo{border-color:rgba(242,219,164,.55);animation:promoPulsa 3s ease-in-out infinite}@keyframes promoPulsa{0%,100%{box-shadow:0 0 0 0 rgba(217,180,90,.0)}50%{box-shadow:0 0 18px -2px rgba(217,180,90,.55)}}
    .lupoW{display:grid;place-items:center;width:52px;height:52px;border-radius:50%;background:radial-gradient(circle at 40% 30%,#241d10,#0a0806);border:1px solid var(--oro-scuro);flex:none}
    .lupoW.bianco svg path[fill^="url"]{fill:#ffffff!important} .lupoW.bianco svg circle,.lupoW.bianco svg path[fill="#6f675c"]{fill:#8a8177!important}</style>

  <?php if (!demo_pin_c_e($IO)): ?>
  <div class="avviso" style="border-color:rgba(217,180,90,.6);color:var(--oro-chiaro)">
    <b>Create your transaction PIN</b> before moving funds — <a href="account.php?s=pin" style="color:inherit">Account Settings →</a></div>
  <?php endif; ?>

  <div class="due" style="margin-bottom:11px">
    <div class="carta" style="margin:0;padding:12px">
      <div class="eti">Total turnover</div>
      <div class="medio" style="font-size:19px;margin:2px 0"><span data-conta="<?= e((string)round((float)$S['xp']['totale'])) ?>" data-pre="$"><?= dollari($S['xp']['totale']) ?></span></div>
      <div class="sub"><?= dollari($S['xp']['proprio']) ?> yours · <?= dollari($S['xp']['rete']) ?> from <?= (int)$S['xp']['persone'] ?> below</div>
    </div>
    <?php /* V4 (17-08-2026): GLOBAL TREASURY = somma delle tre tesorerie esterne; il semaforo conta SOLO le verificate */ $T4 = v4_tesorerie_live(); $SEM = v4_semaforo($T4); ?>
    <?php $__tc = media_get('img_treasury_card'); $__tcbg = ($__tc && media_has($__tc, 'source_original')) ? "background-image:linear-gradient(180deg,rgba(8,7,5,.93),rgba(8,7,5,.97)),url('" . e(media_url((string)$__tc['source_original'])) . "');background-size:cover;background-position:center;" : ''; ?>
      <div class="carta" style="<?= $__tcbg ?>margin:0;padding:12px;border-color:rgba(217,180,90,.45)">
      <div class="eti" style="display:flex;align-items:center;gap:6px">GLOBAL TREASURY — 3 vaults <span class="livepill<?= $T4['ok'] ? '' : ' off' ?>"><?= $T4['ok'] ? 'LIVE' : 'OFFLINE' ?></span><span class="livepill" style="background:<?= e($SEM['colore']) ?>;animation:none;margin-left:auto" title="coverage = verified funds / funded cash obligations next 90 days"><?= e($SEM['stato']) ?></span></div>
      <div class="medio" style="font-size:19px;margin:2px 0;color:var(--oro-chiaro)"><span data-conta="<?= e((string)round($T4['totale'])) ?>" data-dec="0"><?= e(number_format($T4['totale'], 0, '.', ',')) ?></span> <span style="font-size:10px;color:var(--tenue)">USDT</span></div>
      <div class="sub">verified &amp; counted: <b style="color:var(--oro-chiaro)"><?= e(number_format($T4['verificato'], 0, '.', ',')) ?> USDT</b> · coverage <?= e((string)$SEM['coverage']) ?> · <?= e($SEM['azione']) ?></div>
      <div class="sub">Polygon USDT · read live from the chain · balances below are shown as they are; unverified vaults weigh 0 in the risk engine</div>
    </div>
  </div>
  <?php /* V4: le TRE TESORERIE affiancate (nome, ruolo, saldo, verificata/non, Polygonscan). Mobile: in colonna. */ ?>
  <div class="tre-tes" style="margin-bottom:11px">
    <?php foreach ($T4['tes'] as $k => $t): ?>
    <div class="carta" style="margin:0;padding:10px 12px;border-color:<?= $t['verificata'] ? 'rgba(126,208,138,.55)' : 'rgba(217,180,90,.35)' ?>">
      <div class="eti" style="display:flex;align-items:center;gap:6px">Treasury <?= (int)$t['n'] ?> · <?= e($t['nome']) ?> <span class="livepill<?= $t['live'] ? '' : ' off' ?>" style="margin-left:auto"><?= $t['live'] ? 'LIVE' : 'OFFLINE' ?></span></div>
      <div class="sub" style="line-height:1.35;min-height:26px"><?= e($t['ruolo']) ?></div>
      <div class="medio" style="font-size:17px;margin:3px 0;color:var(--oro-chiaro)"><?= e(number_format($t['usdt'], 2, '.', ',')) ?> <span style="font-size:10px;color:var(--tenue)">USDT</span></div>
      <div class="sub" style="font-weight:700;letter-spacing:.06em;color:<?= $t['verificata'] ? '#7fd08a' : '#f2dba4' ?>"><?= e($t['etichetta']) ?></div>
      <a class="sub" href="<?= e($t['polygonscan']) ?>" target="_blank" rel="noopener" style="display:block;color:var(--oro-chiaro);word-break:break-all;font-family:Inter,monospace;font-size:9.5px;line-height:1.35"><?= e($t['addr']) ?> ↗</a>
    </div>
    <?php endforeach; ?>
  </div>
  <style>.tre-tes{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}@media(max-width:700px){.tre-tes{grid-template-columns:1fr}}</style>
  <style>.livepill{font:700 8px Inter,sans-serif;letter-spacing:.12em;padding:2px 6px;border-radius:99px;background:#1f7a3a;color:#fff;animation:blink 1.6s infinite}.livepill.off{background:#5a4a2a;animation:none}@keyframes blink{50%{opacity:.55}}</style>

  <div class="quattro">
    <a class="carta metal" href="membership.php"><div class="eti">In memberships</div><div class="medio" style="font-size:16px"><?= dollari_base($TL['membership'], 'DUX') ?></div><div class="sub"><?= (int)$CR['mem_attive'] ?> active</div></a>
    <a class="carta metal" href="<?= demo_modulo_on('staking') ? 'stake81x.php' : 'soon.php?m=staking' ?>"><div class="eti">In staking</div><div class="medio" style="font-size:16px"><?= dollari_base($TL['staking'], 'DUX') ?></div><div class="sub"><?= demo_modulo_on('staking') ? 'NFT · DRX · 81X' : 'coming soon' ?></div></a>
    <a class="carta metal" href="<?= demo_modulo_on('vault') ? 'vault.php' : 'soon.php?m=vault' ?>"><div class="eti">In vault</div><div class="medio" style="font-size:16px"><?= dollari_base($TL['vault'], 'DUX') ?></div><div class="sub"><?= demo_modulo_on('vault') ? 'DRX · 81X' : 'coming soon' ?></div></a>
    <a class="carta metal" href="<?= demo_modulo_on('mining') ? 'mining.php' : 'soon.php?m=mining' ?>"><div class="eti">In mining</div><div class="medio" style="font-size:16px"><?= dollari_base($TL['mining'], 'DUX') ?></div><div class="sub"><?= demo_modulo_on('mining') ? 'rigs' : 'coming soon' ?></div></a>
  </div>
  <?php /* V3 (17-08-2026): strumenti utente/networker + Academy airdrop + (admin) Control Room */ ?>
  <div class="quattro">
    <a class="carta metal" href="strumenti.php"><div class="eti">My tools</div><div class="medio" style="font-size:14px">Simulator · rank · pool · claims · withdrawals</div><div class="sub">the 50% cap explained, system health public</div></a>
    <a class="carta metal" href="airdrop.php"><div class="eti">Academy airdrop</div><div class="medio" style="font-size:14px">Community vault draw</div><div class="sub">to your Deposit wallet — usable, not withdrawable</div></a>
    <?php if (demo_admin_sessione()): ?><a class="carta metal" href="admin-controllo.php"><div class="eti">Control Room (admin)</div><div class="medio" style="font-size:14px">Stop · blocks · freeze · schedule</div><div class="sub">V3 risk switches, read by every action</div></a>
    <a class="carta metal" href="admin-motore.php"><div class="eti">Business Model Engine (admin)</div><div class="medio" style="font-size:14px">240-month simulation</div><div class="sub">v1 · v2 · v3 · Monte Carlo · live risk state</div></a><?php endif; ?>
  </div>
  <style>.quattro{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-bottom:11px}.quattro .carta{margin:0;padding:10px 12px;text-decoration:none;color:inherit}@media(min-width:640px){.quattro{grid-template-columns:repeat(4,1fr)}}</style>

  <?php $PV = demo_prossima_versione(); if ($PV): $gg = max(0, (int)ceil(($PV['quando'] - time()) / 86400)); ?>
  <a class="carta" href="docs.php#roadmap" style="text-decoration:none;color:inherit;display:flex;gap:10px;align-items:center;padding:10px 12px;border-color:rgba(217,180,90,.35)">
    <span class="pal"><?= dric_ui('boost', 16) ?></span><div style="flex:1;min-width:0"><div class="eti">Next release · <?= e($PV['sigla']) ?> <?= e($PV['nome']) ?></div><div class="sub"><?= e(gmdate('j M Y', $PV['quando'])) ?> at 20:30 — in <b style="color:var(--oro-chiaro)"><?= $gg ?> days</b> · see what is coming ›</div></div></a>
  <?php endif; ?>
  <?php require_once __DIR__ . '/_media.php'; echo media_vertical('dapp_v001'); /* renders nothing while the clip is missing */ ?>
  <?php /* NEURAL CORTEX + WORLD MAP: due card con immagine, descrizione e link alle pagine intere (brain.php, mappa.php) */
    require_once __DIR__ . '/_media.php'; require_once __DIR__ . '/_mappa.php';
    $__mp = mappa_dati(); $__na = count(array_filter($__mp, fn($x) => $x['l'] === 'assoc')); $__nf = count(array_filter($__mp, fn($x) => $x['l'] === 'forest')); $__nn = count(array_filter($__mp, fn($x) => $x['l'] === 'node'));
    $__bs = demo_cfg('brain_stats'); $__bs = is_array($__bs) ? $__bs : (is_string($__bs) ? (json_decode($__bs, true) ?: []) : []);
    $__pimg = function (string $id): string { $r = media_get($id); if (!$r || !media_has($r, 'source_original')) return '';
      return '<div class="pimg"><img src="' . e(media_url((string)$r['source_original'])) . '" alt="" loading="lazy" decoding="async"></div>'; };
  ?>
  <div class="griglia3 due-x2 tools2" style="margin:0 0 11px">
    <a class="prod viva" href="brain.php"><?= $__pimg('img_cortex_strip') ?><div class="ic"><?= dric_ui('network', 30) ?></div><div class="n">Neural Cortex 3D</div>
      <div class="d">the living brain of BLOCKCHAINPLUS.DAO, playable in 3D. Every member, token, tool, rank, ledger movement and charity project is a neuron; every real relation a synapse. Spin it, zoom it, tap a neuron and watch the impulse travel. It grows every day with the ecosystem.</div>
      <span class="live">LIVE</span></a>
    <a class="prod viva" href="mappa.php"><?= $__pimg('img_world_map_strip') ?><div class="ic"><?= dric_ui('globo', 30) ?></div><div class="n">World Map</div>
      <div class="d">planisphere and globe in gold wire: the elderly associations we support, the forests planted in Europe, Africa and Latin America, the 118 nodes and the global view. Drag, zoom, tap a point for its card — CO₂ saved counts live.</div>
      <span class="live">LIVE</span></a>
  </div>
  <style>.tools2 .prod{position:relative;padding-top:24px;height:100%;min-height:210px;align-items:center;text-align:center}.tools2 .prod .pimg{align-self:stretch;margin:-24px -10px 8px;height:96px;overflow:hidden;border-radius:14px 14px 0 0;position:relative}.tools2 .prod .pimg img{width:100%;height:100%;object-fit:cover;display:block}.tools2 .prod .pimg:after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(5,5,5,0),rgba(5,5,5,.85))}.tools2 .prod .pimg+.ic{margin-top:-30px;position:relative;z-index:1;color:var(--oro)}.tools2 .prod .d{flex:1;font-size:10.5px;line-height:1.45;text-align:left}.tools2 .prod .nact{display:block;margin-top:8px;font:700 9.5px Inter,sans-serif;letter-spacing:.12em;color:var(--oro-chiaro)}.tools2 .prod .live{position:absolute;top:8px;right:8px;font:800 9px Inter,sans-serif;letter-spacing:.16em;padding:4px 10px 4px 20px;border-radius:99px;color:#120e07;background:linear-gradient(135deg,var(--oro-chiaro),var(--oro) 55%,#b8933a);border:1px solid rgba(255,236,190,.8);animation:t2Pulsa 1.6s ease-in-out infinite;z-index:2}.tools2 .prod .live:before{content:'';position:absolute;left:8px;top:50%;width:6px;height:6px;margin-top:-3px;border-radius:50%;background:#120e07;box-shadow:0 0 0 0 rgba(18,14,7,.6);animation:t2Punto 1.6s ease-in-out infinite}@keyframes t2Pulsa{0%,100%{box-shadow:0 0 0 0 rgba(242,219,164,.9),0 0 8px rgba(217,180,90,.6)}50%{box-shadow:0 0 0 8px rgba(242,219,164,0),0 0 22px rgba(242,219,164,1)}}@keyframes t2Punto{0%,100%{transform:scale(1)}50%{transform:scale(1.5)}}@media(prefers-reduced-motion:reduce){.tools2 .prod .live,.tools2 .prod .live:before{animation:none}}.tools2 .prod.viva{border-color:rgba(242,219,164,.85)}@media(max-width:400px){.tools2 .prod .pimg{margin:-12px -6px 6px;height:72px}}</style>
  <?php /* LEADERBOARD e LEDGER: due card affiancate, altezza fissa, scorrimento interno */
    $LEDU = led_db()->query("SELECT s.quando, s.causale, s.token, s.importo, c.genere FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.genere='utente' ORDER BY s.id DESC LIMIT 60")->fetchAll(); ?>
  <div class="due fissa2" style="margin-bottom:11px">
    <div class="carta metal" id="cardLB" style="margin:0"><div class="eti" style="display:flex;align-items:center;gap:6px"><img src="../home/assets/brand/bplus-coin-96.png" alt="" width="26" height="26" style="width:26px;height:26px;object-fit:contain;filter:drop-shadow(0 0 4px rgba(217,180,90,.6))"> Leaderboard — Top of BRANCH</div>
      <div class="scorri">
      <?php $i = 0; foreach ($CL as $r): $i++; ?>
        <div class="riga"><span class="pal" style="<?= $r['posizione'] <= 3 ? 'color:var(--oro-chiaro)' : '' ?>"><?= (int)$r['posizione'] ?></span>
          <?= dric_prestigio((int)$r['prest_liv'], 0, 18) ?>
          <div class="mid"><div class="tit2"><?= e($r['nome']) ?></div><div class="sub"><?= e($r['rango']) ?> · <?= (int)$r['persone'] ?> below</div></div>
          <div class="val"><?= dollari($r['xp']) ?></div></div>
      <?php endforeach; if (!$i): ?><div class="sotto">No one on the board yet.</div><?php endif; ?>
      </div>
      <div class="sub" style="margin-top:6px">Name, rank, prestige, turnover. No balances, no wallets, no addresses.</div></div>
    <div class="carta metal" id="cardLED" style="margin:0"><div class="eti" style="display:flex;align-items:center;gap:6px"><?= dric_ui('ledger', 16) ?> Ledger — public registry <span class="livepill<?= !empty($V['catena_integra']) ? '' : ' off' ?>" style="margin-left:auto"><?= !empty($V['catena_integra']) ? 'CHAIN INTACT' : 'CHECK' ?></span></div>
      <div class="scorri">
      <?php foreach ($LEDU as $r): $neg = str_starts_with((string)$r['importo'], '-'); ?>
        <div class="riga"><div class="mid"><div class="tit2"><?= e(causale_en((string)$r['causale'])) ?></div><div class="sub"><?= e(gmdate('d M H:i', (int)$r['quando'])) ?> UTC · <?= e((string)$r['token']) ?></div></div>
          <div class="val <?= $neg ? 'meno' : 'piu' ?>"><?= $neg ? '−' : '+' ?><?= e(soldi(ltrim((string)$r['importo'], '-'), (string)$r['token'])) ?></div></div>
      <?php endforeach; if (!$LEDU): ?><div class="sotto">No movements yet.</div><?php endif; ?>
      </div>
      <div class="sub" style="margin-top:6px"><?= (int)($V['righe'] ?? 0) ?> entries · append-only, double entry · <a href="#ledger" onclick="vai('ledger');return false" style="color:var(--oro-chiaro)">integrity ›</a></div></div>
  </div>
  <style>.fissa2>.carta{height:340px;display:flex;flex-direction:column;min-width:0}.fissa2 .scorri{flex:1;min-height:0;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(217,180,90,.5) transparent;padding-right:4px;margin-top:6px}.fissa2 .scorri::-webkit-scrollbar{width:5px}.fissa2 .scorri::-webkit-scrollbar-thumb{background:rgba(217,180,90,.5);border-radius:4px}.fissa2 .riga{padding:5px 0}@media(max-width:520px){.fissa2>.carta{height:300px}}</style>

  <div class="due" style="margin-bottom:11px">
    <a class="carta" href="wallet.php" style="margin:0;text-decoration:none;color:inherit">
      <div class="eti" style="margin-bottom:6px">Wallet — all your tokens</div>
      <?php foreach (['DUX','DRX','81X','ERIDAN','BTC'] as $t): ?><div class="riga" style="padding:5px 0"><?= dric_gettone($t, 22) ?><div class="mid"><div class="tit2" style="font-size:12px"><?= $t ?></div></div><div class="val" style="font-size:12px"><?= e(soldi($TT[$t], $t)) ?></div></div><?php endforeach; ?>
      <div class="sub" style="margin-top:4px">totals across Deposit · Rewards · Withdrawal · Offset — details in Wallet ›</div>
    </a>
    <div class="carta" style="margin:0;display:flex;flex-direction:column">
      <div class="eti">Mission</div>
      <?php $catM = ['Daily' => 'Today', 'Weekly' => 'Weekly', 'Monthly' => 'Monthly', 'Epic' => 'Epic']; $perCat = []; foreach ($MIS as $k => $m) $perCat[$m[0]][] = [$k, $m]; ?>
      <div class="wrows" style="max-height:150px;margin-top:4px">
      <?php foreach (['Daily','Weekly','Monthly','Epic'] as $c): if (empty($perCat[$c])) continue; ?>
        <div class="sub" style="font-weight:700;letter-spacing:.14em;color:var(--oro-chiaro);margin-top:6px"><?= e(strtoupper($catM[$c])) ?> MISSION<?= count($perCat[$c]) > 1 ? 'S' : '' ?></div>
        <?php foreach ($perCat[$c] as [$k, $m]): $ok = $m[3]($IO); $fatta = mod_missione_fatta($IO, $k); ?>
          <div style="display:flex;align-items:center;gap:6px;padding:3px 0"><span style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:11px;color:<?= $fatta ? 'var(--tenue)' : '#f2e9d8' ?>;<?= $fatta ? 'text-decoration:line-through' : '' ?>"><?= e($m[1]) ?></span><span class="sub">+<?= e($m[2]) ?> DRX</span><?= $fatta ? '<span class="sub" style="color:var(--oro)">✓</span>' : ($ok ? '<span class="sub" style="color:var(--oro-chiaro)">ready</span>' : '') ?></div>
        <?php endforeach; endforeach; ?>
      </div>
      <a class="b mini pieno" href="gamification.php" style="margin-top:8px;text-decoration:none;text-align:center">Open missions ›</a>
    </div>
  </div>

  <?php /* V4 (17-08-2026): TRE card grafico, una per tesoreria (stesso componente Day/Week/Month/Year/3Y/5Y, performance, contatori); poi le news */
    $H81 = demo_holders_token('0xE79cF87f9c3e377f113B6910d7e0d3D09D477bf1'); $HDR = demo_holders_token('0x933767F8493f0AEB11A5f47f3BC28ab9072b1D27'); ?>
  <div class="tre-graf">
  <?php foreach ($T4['tes'] as $k => $t): $TC = demo_tesoreria_contatori($t['addr']); ?>
  <div class="carta tesCard" id="tesCard_<?= e($k) ?>" data-t="<?= e($k) ?>" style="margin:0">
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px"><div style="flex:1;min-width:160px"><div class="eti">Treasury <?= (int)$t['n'] ?> · <?= e($t['nome']) ?> — live from Polygon</div>
      <div class="medio" style="font-size:17px;margin:2px 0 0;color:var(--oro-chiaro)"><?= e(number_format($t['usdt'], 2, '.', ',')) ?> <span style="font-size:10px;color:var(--tenue)">USDT</span> <span class="livepill<?= $t['live'] ? '' : ' off' ?>" style="vertical-align:middle"><?= $t['live'] ? 'LIVE' : 'OFFLINE' ?></span></div>
      <a href="<?= e($t['polygonscan']) ?>" target="_blank" rel="noopener" class="sub" style="display:block;color:var(--oro-chiaro);word-break:break-all;font-family:Inter,monospace;font-size:9.5px"><?= e($t['addr']) ?> ↗</a></div>
      <div class="segm tesRange"><button type="button" class="on" data-r="1">Day</button><button type="button" data-r="7">Week</button><button type="button" data-r="30">Month</button><button type="button" data-r="365">Year</button><button type="button" data-r="1095">3Y</button><button type="button" data-r="1825">5Y</button></div></div>
    <div class="tesPerf sub" style="margin:0 0 4px;min-height:14px"></div>
    <svg class="tesSvg" viewBox="0 0 600 160" width="100%" height="120" preserveAspectRatio="none" style="display:block"></svg>
    <div class="tesAssi sub" style="display:flex;justify-content:space-between;gap:6px;font-size:9px;margin-top:2px"></div>
    <div class="quattro" style="margin:8px 0 0;grid-template-columns:repeat(4,1fr)">
      <div class="carta" style="padding:8px 10px"><div class="sub">Treasury value</div><b style="font-size:13px;color:var(--oro-chiaro)"><?= e(number_format($t['usdt'], 2, '.', ',')) ?> USDT</b></div>
      <div class="carta" style="padding:8px 10px"><div class="sub">Control</div><b style="font-size:11px;color:<?= $t['verificata'] ? '#7fd08a' : '#f2dba4' ?>"><?= $t['verificata'] ? 'VERIFIED' : 'NOT YET VERIFIED' ?></b><div class="sub"><?= $t['verificata'] ? 'weight 100% in coverage' : 'weight 0% until proof of control' ?></div></div>
      <div class="carta" style="padding:8px 10px"><div class="sub">Treasury wallet</div><b style="font-size:13px"><?= number_format((int)$TC['tx'], 0, '', ',') ?> tx</b><div class="sub"><?= number_format((int)$TC['trasferimenti'], 0, '', ',') ?> transfers · <?= (int)$TC['token'] ?> tokens</div></div>
      <div class="carta" style="padding:8px 10px"><div class="sub">Polygon status</div><b style="font-size:13px;color:<?= $t['live'] ? '#7fd08a' : '#f2dba4' ?>"><?= $t['live'] ? 'online' : 'unreachable' ?></b><div class="sub">block <?= number_format((int)$t['blocco'], 0, '', ',') ?> · <?= e((string)$t['gas_gwei']) ?> gwei</div></div>
    </div>
    <div class="sub" style="margin-top:6px"><span class="tesFonte">Loading history…</span> Role: <?= e($t['ruolo']) ?>. Recommended custody: <?= e($t['multisig']) ?>. The number is what the chain says, not a database.</div>
  </div>
  <?php endforeach; ?>
  </div>
  <div class="sub" style="margin:-4px 0 11px">DAO holders on-chain: <b><?= number_format((int)$H81['holders'] + (int)$HDR['holders'], 0, '', ',') ?></b> (81X <?= (int)$H81['holders'] ?> · DRX <?= (int)$HDR['holders'] ?>) · GLOBAL TREASURY <b style="color:var(--oro-chiaro)"><?= e(number_format($T4['totale'], 2, '.', ',')) ?> USDT</b> · verified and counted by the risk engine <b><?= e(number_format($T4['verificato'], 2, '.', ',')) ?> USDT</b> · semaphore <b style="color:<?= e($SEM['colore']) ?>"><?= e($SEM['stato']) ?></b> (coverage <?= e((string)$SEM['coverage']) ?> = verified funds ÷ funded cash obligations of the next 90 days).</div>
  <style>.segm{display:inline-flex;border:1px solid var(--bordo);border-radius:9px;overflow:hidden}.segm button{background:transparent;border:0;color:var(--tenue);font:700 9px Inter,sans-serif;letter-spacing:.08em;padding:6px 8px;cursor:pointer}.segm button.on{background:linear-gradient(135deg,var(--oro),#b8933a);color:#120e07}
    .tre-graf{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin-bottom:11px}.tre-graf>.carta{min-width:0}@media(max-width:1000px){.tre-graf{grid-template-columns:1fr}}
    .tre-graf .tesCard .quattro{grid-template-columns:1fr 1fr!important;gap:6px}.tre-graf .tesCard .quattro .carta{padding:6px 8px;min-width:0}.tre-graf .tesCard .quattro b{font-size:11.5px;word-break:break-word}.tre-graf .tesCard .medio{font-size:15px}.tre-graf .tesCard .segm button{padding:5px 6px;font-size:8.5px}</style>
  <script>
  (function(){
    /* stesso componente grafico di prima, ora per ogni card .tesCard (data-t = shield|flow|recovery) */
    document.querySelectorAll('.tesCard').forEach(function(card){
      var svg=card.querySelector('.tesSvg'),bt=card.querySelectorAll('.tesRange button'),tkey=card.dataset.t;
      function draw(days){fetch('app.php?tes='+days+'&t='+encodeURIComponent(tkey)).then(function(r){return r.json();}).then(function(d){var P=d.serie||[];while(svg.firstChild)svg.removeChild(svg.firstChild);var NS='http://www.w3.org/2000/svg';
        var W=600,H=160,pad=6; if(P.length===1){P=[[P[0][0]-days*86400,P[0][1]],P[0]];} if(P.length<2){var t=document.createElementNS(NS,'text');t.setAttribute('x',300);t.setAttribute('y',84);t.setAttribute('text-anchor','middle');t.setAttribute('fill','rgba(242,233,216,.5)');t.setAttribute('font-size','11');t.textContent=P.length?'Collecting history — come back in an hour.':'No data yet.';svg.appendChild(t);
          if(P.length===1){var c=document.createElementNS(NS,'circle');c.setAttribute('cx',300);c.setAttribute('cy',60);c.setAttribute('r',4);c.setAttribute('fill','#d9b45a');svg.appendChild(c);} return;}
        var xs=P.map(function(p){return p[0];}),ys=P.map(function(p){return p[1];});var x0=Math.min.apply(null,xs),x1=Math.max.apply(null,xs),y0=Math.min.apply(null,ys),y1=Math.max.apply(null,ys);if(y1===y0){y1+=1;y0-=1;}
        var pts=P.map(function(p){return [pad+(p[0]-x0)/(x1-x0)*(W-2*pad),H-pad-(p[1]-y0)/(y1-y0)*(H-2*pad)];});
        var gid='tg_'+tkey;var g=document.createElementNS(NS,'linearGradient');g.id=gid;g.setAttribute('x1','0');g.setAttribute('y1','0');g.setAttribute('x2','0');g.setAttribute('y2','1');g.innerHTML='<stop offset="0" stop-color="#d9b45a" stop-opacity=".45"/><stop offset="1" stop-color="#d9b45a" stop-opacity="0"/>';var df=document.createElementNS(NS,'defs');df.appendChild(g);svg.appendChild(df);
        var a=document.createElementNS(NS,'path');a.setAttribute('d','M'+pts.map(function(p){return p[0].toFixed(1)+','+p[1].toFixed(1);}).join(' L')+' L'+pts[pts.length-1][0].toFixed(1)+','+(H-pad)+' L'+pts[0][0].toFixed(1)+','+(H-pad)+' Z');a.setAttribute('fill','url(#'+gid+')');svg.appendChild(a);
        var l=document.createElementNS(NS,'path');l.setAttribute('d','M'+pts.map(function(p){return p[0].toFixed(1)+','+p[1].toFixed(1);}).join(' L'));l.setAttribute('fill','none');l.setAttribute('stroke','#f2dba4');l.setAttribute('stroke-width','2');svg.appendChild(l);
        function fm(v){return v>=1e6?(v/1e6).toFixed(2)+'M':v>=1e3?(v/1e3).toFixed(1)+'k':v.toFixed(0);} function fd(t){var d=new Date(t*1000);return days<=3?d.toLocaleTimeString('en-GB',{hour:'2-digit',minute:'2-digit'}):d.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:days>=365?'2-digit':undefined});}
        var pf=card.querySelector('.tesPerf');if(pf){var v0=P[0][1],v1=P[P.length-1][1],dl=v1-v0,pc=v0?dl/v0*100:0;pf.innerHTML='<span style="color:'+(dl>=0?'#7fd08a':'#e07b7b')+';font-weight:700">'+(dl>=0?'▲ +':'▼ ')+dl.toLocaleString('en-US',{maximumFractionDigits:0})+' USDT'+(v0>0?' ('+(dl>=0?'+':'')+pc.toFixed(2)+'%)':'')+'</span> <span>over '+(days===1?'24h':days===7?'7 days':days===30?'30 days':days===365?'1 year':days===1095?'3 years':'5 years')+' · '+(d.fonte==='archive'?'balance sampled on-chain at '+P.length+' blocks':'from '+d.fonte)+'</span>';}
        var ax=card.querySelector('.tesAssi');if(ax){ax.innerHTML='<span>'+fd(x0)+'</span><span>high '+fm(Math.max.apply(null,ys))+' · low '+fm(Math.min.apply(null,ys))+' USDT</span><span>'+fd(x1)+'</span>';}
        var nota=card.querySelector('.tesFonte');if(nota){nota.textContent=d.fonte==='archive'?('On-chain balance history · Polygon archive node ('+(d.rpc||'rpc')+') · no database, no estimate.'):d.fonte==='chain'?('On-chain history · '+(d.ntx||0)+' USDT transfers'+(d.completo?'':' (partial)')+' · reconstructed from Polygonscan.'):('Hourly snapshots only — add POLYGONSCAN_API_KEY to .env for the full on-chain history'+(d.motivo?' ('+d.motivo+')':'')+'.');}
      }).catch(function(){});}
      bt.forEach(function(b){b.addEventListener('click',function(){bt.forEach(function(x){x.classList.remove('on');});b.classList.add('on');draw(+b.dataset.r);});});draw(1);
    });
  })();
  </script>

  <div class="duo2">
  <div class="carta nws" style="margin:0">
    <div class="eti" style="margin-bottom:9px">Crypto news — live from the journals</div>
    <div class="carte scorri-auto" id="news"><div class="sotto">Loading…</div></div>
  </div>
  </div>
  <style>
    .duo2{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:9px;align-items:stretch} .duo2>*{min-width:0;max-width:100%} .duo2 .nws{display:flex;flex-direction:column;height:0;min-height:100%} .duo2 .nws .carte{flex-direction:column;flex:1;min-height:0;overflow-y:auto;scrollbar-width:thin} .duo2 .nws .nw{width:auto;flex:none}
    @media(max-width:640px){.duo2{grid-template-columns:1fr}.duo2 .nws{height:auto;min-height:0;max-height:360px}}@media(max-width:520px){.tesCard .quattro{grid-template-columns:1fr 1fr!important}.tesRange button{padding:5px 5px;font-size:8px}.tesCard .medio{font-size:14px!important}}
    .tesSvg{height:120px} @media(min-width:760px){.tesSvg{height:140px}}
    .tesCard .quattro .carta{padding:6px 8px} .tesCard .quattro b{font-size:12px}
    /* V4: le tre card grafico stanno sopra; le news da sole a tutta larghezza */
    .duo2{grid-template-columns:1fr} .duo2 .nws{height:auto;min-height:0;max-height:380px}
    @media(max-width:1000px){.tesCard .quattro{grid-template-columns:repeat(4,1fr)}}
  </style>
</section>

<!-- ================= LEADERBOARD ================= -->
<section class="vista" data-v="leaderboard">
  <div class="carta">
    <?php foreach ($CL as $r): ?>
      <div class="riga"><span class="pal" style="<?= $r['posizione'] <= 3 ? 'color:var(--oro-chiaro)' : '' ?>"><?= (int)$r['posizione'] ?></span>
        <?= dric_prestigio((int)$r['prest_liv'], 0, 20) ?>
        <div class="mid"><div class="tit2"><?= e($r['nome']) ?></div>
          <div class="sub"><?= e($r['rango']) ?> · <?= (int)$r['persone'] ?> below</div></div>
        <div class="val"><?= dollari($r['xp']) ?></div></div>
    <?php endforeach; ?>
  </div>
  <div class="franco">Name, rank, prestige and turnover. <b>No balances, no wallets,
    no addresses</b> — a leaderboard with balances is a shopping list for whoever goes hunting.</div>
</section>

<!-- ================= LEDGER ================= -->
<section class="vista" data-v="ledger">
  <div class="carta">
    <div class="eti" style="margin-bottom:8px">Ledger integrity</div>
    <div class="riga"><div class="mid"><div class="tit2">Hash chain</div>
      <div class="sub"><?= (int)$V['righe'] ?> entries checked</div></div>
      <div class="val <?= $V['catena_integra'] ? 'piu' : 'meno' ?>"><?= $V['catena_integra'] ? 'intact' : 'BROKEN' ?></div></div>
    <div class="riga"><div class="mid"><div class="tit2">Double entry</div>
      <div class="sub">every token must sum to zero</div></div>
      <div class="val <?= $V['partita_doppia_ok'] ? 'piu' : 'meno' ?>"><?= $V['partita_doppia_ok'] ? 'balanced' : 'OFF' ?></div></div>
  </div>
  <div class="franco">Every movement you just made is in there, and none of it can be
    edited or deleted — the database itself refuses. If anyone tampers with a row,
    the chain breaks and this page says <b>which row</b>.</div>

  <h4 class="sez">Danger zone</h4>
  <form method="post" onsubmit="return confirm('Wipe the demo ledger and start over?')">
    <input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="reset">
    <button class="b" style="color:var(--no);border-color:rgba(242,219,164,.4)">Reset demo</button>
  </form>
</section>
</main>

<?php require __DIR__ . '/_piede.php'; ?>
