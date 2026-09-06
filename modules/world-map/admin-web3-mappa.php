<?php
/* ============================================================================
   ADMIN · MAPPA WEB3 — panoramica unica di TUTTO il Web3 di Destino Randagio
   Perche' / cosa / come · cosa e' vivo, deployato, in corso, da fare · doppioni.
   Additivo: nuova pagina, non tocca nulla. Voce nel menu admin (gruppo Web3).
   Gate: role=admin+uid  OPPURE  ?key=DR_ADMIN_KEY  (come gli altri moduli).
   Sola LETTURA/catalogo: nessuna azione, nessun segreto, indirizzi pubblici.
============================================================================ */
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (is_file(__DIR__.'/dr-env.php')) require_once __DIR__.'/dr-env.php';
$K = function_exists('dr_env') ? (string)dr_env('DR_ADMIN_KEY','') : (string)(getenv('DR_ADMIN_KEY')?:'');
$isAdmin = ((($_SESSION['role'] ?? '') === 'admin') && !empty($_SESSION['uid']))
        || ($K!=='' && isset($_REQUEST['key']) && hash_equals($K,(string)$_REQUEST['key']));
if(!$isAdmin){ http_response_code(403); exit('403 — solo admin'); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function scan($a){ return $a ? 'https://polygonscan.com/address/'.$a : '#'; }

/* ---- STATI (badge) ---- */
$ST = [
  'vivo'    => ['VIVO on-chain', '#5fd68a'],
  'deploy'  => ['PRONTO AL DEPLOY', '#e8c15a'],
  'corso'   => ['IN SVILUPPO', '#7ab8ff'],
  'todo'    => ['DA FARE', '#c9a86a'],
  'dismesso'=> ['DISMESSO — mai pubblicare', '#e0655a'],
];
function badge($k){ global $ST; $b=$ST[$k]??['?','#888']; return '<span class="bdg" style="border-color:'.$b[1].';color:'.$b[1].'">'.h($b[0]).'</span>'; }

$TES='0x3C320B3a0917fF44BF6551CDdee44402AFcF250C';

/* ============================ CATALOGO (config-driven) ============================ */
$TOKENS=[
 ['DRX','0x933767F8493f0AEB11A5f47f3BC28ab9072b1D27','18','vivo','Utility interna del Branco, NON convertibile.','Token ERC-20 (cap 100B, ~50B in circolo). 1 DRX = 0,10€ di credito d\'uso.','Si guadagna/spende dentro l\'ecosistema; letto on-chain, mai promesse di rendita.'],
 ['81X','0xE79cF87f9c3e377f113B6910d7e0d3D09D477bf1','18','vivo','Buono sconto / utility con liquidità reale su DEX.','ERC-20, cap 81M. Sconto fino al 10% sugli acquisti.','Si ottiene dal converter (1 ogni 1.000 DRX −0,5%) o dai tool; scambiabile su DEX.'],
 ['DUX','0x983dD7fAfAbD3C6e179607AE46e8ce586099228b','6','vivo','Moneta d\'uscita verso il mercato + reward dei tool.','ERC-20 6 dec, cap 1B a halving (32 epoche). Deployato+verificato.','Dentro la dapp gira DRX; il DUX si preleva e si vende su DEX. Reward dei tool NFT.'],
 ['DRX doppione','0x039F6B9818A675f8C113eF9f284a08C336863C28','18','dismesso','Secondo contratto DRX identico, mai usare.','Duplicato dello stesso bytecode.','NON pubblicare: qualcuno comprerebbe il token sbagliato.'],
];
$NFTS=[
 ['Genesys (Pionieri)','0x5DdF7e28C372CC862e3a413AEe7c7C0aD3e51253','ERC-721','vivo','La collezione dei 118 Pionieri fondatori.','DRGP, numerazione legata al nodo/SIC-ID.','Si integra l\'esistente, non si rideploya.'],
 ['Preda','0xE5434E27c3551c3D016647B0B063340ACcBB5D89','ERC-1155','vivo','Collezione con i reward più alti.','ERC-1155, usata come posta/utility nei tool.','Staking + buyback pool già on-chain.'],
 ['Thrinwulf (DR118)','0x65411FbA529BeB65d8761A8b9d93726CDF9fc5Be','ERC-721','corso','Clip video, terza collezione dei 118.','DR118Clips, stessa numerazione.','Contratto deployato; mint differito + setMinter/reveal/freeze da fare a mano.'],
];
$TOOLSCH=[
 ['Staking DRX (V3)','0x1C8c8A2Fa8e84223C9f5160B774EE5d11102D095','vivo','50.000.000 DRX di riserva. 7,90% netto/anno, pareggio 73,74 gg.'],
 ['Staking 81X (V3)','0xa6d933367Dda3D60E1c81eEB385540Fad4890a14','vivo','8.100.000 81X di riserva. Entrare non costa, uscita 2% / premi 1%.'],
 ['Convertitore DRX→81X (V3)','0x8a5Fab8bac26684997908b03d455908B92817c6c','corso','8,1M 81X di riserva. Conversioni CHIUSE: da aprire quando il sito è pronto.'],
 ['Caveau / DRVault (V3)','0x2201c1ba3e4c0353d1fEfc751396eC5b4f2670e7','vivo','100.000.000 DRX. Fasce 12/15/18/22% a 3/6/12/24 mesi.'],
 ['Preda staking','0x09c0eC8a934817C34833A33A2C44984e0E5686f3','vivo','Staking dedicato della collezione Preda.'],
 ['Preda buyback pool','0x9C6F56860E2d2b420068fd847740e970880c25B8','vivo','Unico pool on-chain esistente (buyback Preda).'],
];
$NUOVI=[
 ['DRWithdrawGuard.sol','deploy','Porta d\'uscita del DUX blindata: 48h, min 150, limiti 1000/g·5000/sett·25000/mese, fee 0,5%. Audit DR-SECURITY chiuso (45/45 test).'],
 ['DRTreasury.sol','deploy','Cassaforte riserva POL + fee: timelock, pavimento pubblico, handover 2 passi (Gnosis Safe).'],
 ['DRMembership.sol','corso','Tool #1: acquisto Membership in DUX (300/500/1.000). In sviluppo (DR-PROTOCOL).'],
 ['DRNFTUtilityLock.sol','corso','Tool #2-3-4: lock NFT Preda/Genesys/Thrinwulf → reward DUX a scadenza. In sviluppo (DR-PROTOCOL).'],
];
/* i 4 NUOVI tool Genesys (perché/cosa/come + numeri fissati) */
$G4=[
 ['DR Membership','corso','Dare accesso a Community/Academy/Shop/Travel/Rank/benefit pagando in DUX.',
   'MEMBER 300 · ALPHA 500 · LEGEND 1.000 DUX. Acquisti multipli, ogni posizione ha ID+timestamp.',
   'USDT → DUX (marcato MEMBERSHIP_DUX, solo per membership) → consumato dal contratto → sblocca i benefit.'],
 ['Preda NFT Power','corso','Reward DUX più alto, premia chi concentra più NFT e lock lunghi.',
   'Base 10 DUX. Tier 1/5/10/20 = ×1/×6/×15/×40. Lock 90/180/360 = ×1/×2,25/×5. Max 20 NFT / 360gg = 2.000 DUX.',
   'Lock NFT → timer → completion → claim DUX. Early unlock: NFT indietro, niente reward. Coefficienti congelati alla firma.'],
 ['Genesys NFT Power','corso','Stesso motore, potenza 25% di Preda.',
   'Base 2,5 DUX. Stessi tier/lock. Max 20/360 = 500 DUX.',
   'Come Preda; benefit XP/Rank/badge Genesys.'],
 ['Thrinwulf NFT Power','corso','Livello più leggero, potenza 10% di Preda.',
   'Base 1 DUX. Stessi tier/lock. Max 20/360 = 200 DUX.',
   'Come Preda; il più accessibile. Combo TRINITY (Preda+Genesys+Thrinwulf) = XP/Rank/badge, niente DUX extra.'],
];
/* 32 tools hub: [nome, stato, motore/nota] — doppioni evidenziati */
$T32_new=['DRX Savings','Time Vault','Quest-to-Earn','Achievement','XP Booster','Creator Bounty','Treasure Hunt','Burn-for-Glory','Fee Cashback'];
$T32_reuse=[
 ['Staking DRX','staking.php'],['Vault','dr-web3.php'],['Liquidity Pool','dr-web3.php (pool interna)'],['Farming','dr-yield.php'],
 ['Fan Pass','dr-pass.php'],['Music Pass','dr-pass.php'],['Travel Pass','dr-pass.php'],['Alpha Access','dr-pass.php'],
 ['Branco Club','dr-network.php'],['DRX Lottery','wheel.php'],['Luck Wheel','wheel.php'],['Mystery Box','mystery-box.php'],
 ['Merch Drop','mystery-box.php'],['Random Drop','mystery-box.php'],['Governance','dao.php'],['Referral','referral.php'],
];
$T32_locked=['NFT Locker','Marketplace','Story Mint','Dynamic NFT','NFT Renting','Subscription NFT','Escrow'];
$GIOCHI=[
 ['Caccia del Branco','1 DRX'],['Forgia del Destino','2 DRX'],['Eco del Delta','3 DRX'],['Carte del Branco','5 DRX'],
 ['Arena degli NFT','1 NFT Preda (NFT vs NFT)'],['Ruota degli NFT','1 NFT Preda'],['DAO Pool NFT','collettore/staking NFT'],
];
$DOPPIONI=[
 ['DRX doppione','0x039F6B9818A675f8C113eF9f284a08C336863C28','copia identica di DRX'],
 ['Staking DRX V1','0x7A393e8ABD4761EA4481aE6e7B8C4aBe2dB4a1c6','sostituito da V3'],
 ['Staking 81X V1','0x3D6D3BF262c9dfF298e7AD89A8B89F1AC50Ca773','sostituito da V3'],
 ['Staking DRX V2','0x90dA4c01C43B39244B75F7d1E817714f974f6BbB','difettoso, sostituito da V3'],
 ['Staking 81X V2','0x427893b944F676fC5a26819a5368c1c8BE4E3155','difettoso, sostituito da V3'],
 ['Preda abbandonati','0xd16143a0…/0x4c1287fA…/0x85453AfE…','primo set Preda, non usare'],
];
?><!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>DR · Mappa Web3</title>
<style>
:root{--oro:#D4AF37;--oro2:#E8CE7A;--nero:#0a0908;--panel:#100e0a;--line:#2a2113;--txt:#e8e0d4;--dim:#a89880}
*{box-sizing:border-box}body{margin:0;background:var(--nero);color:var(--txt);font-family:system-ui,Segoe UI,Arial;padding:18px 16px 60px}
h1{color:var(--oro);font-size:22px;margin:0 0 2px}h2{color:var(--oro2);font-size:15px;letter-spacing:.5px;margin:26px 0 10px;border-bottom:1px solid var(--line);padding-bottom:6px}
.sub{color:var(--dim);font-size:12.5px;margin:0 0 6px}
.leg{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0 4px}
.bdg{font-size:10.5px;font-weight:800;border:1px solid;border-radius:99px;padding:1px 8px;white-space:nowrap}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:11px}
.card{background:var(--panel);border:1px solid var(--line);border-radius:12px;padding:13px 14px}
.card .nm{font-weight:800;color:#fff;font-size:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.card .ad{font-family:ui-monospace,monospace;font-size:11px}
.card .ad a{color:var(--oro2);text-decoration:none}.card .ad a:hover{text-decoration:underline}
.card .row{margin:5px 0;font-size:12.5px;color:var(--txt)}
.card .k{color:var(--dim);font-weight:700;margin-right:4px}
table{width:100%;border-collapse:collapse;font-size:12.5px}
td,th{border-bottom:1px solid var(--line);padding:7px 8px;text-align:left;vertical-align:top}
th{color:var(--dim);font-weight:700;font-size:11px;text-transform:uppercase}
.pill{display:inline-block;background:#171208;border:1px solid var(--line);border-radius:99px;padding:2px 9px;margin:3px 4px 0 0;font-size:12px}
.warn{background:#1a0f0a;border:1px solid #5a2a20;border-radius:10px;padding:10px 12px;color:#f0b7a8;font-size:12.5px;margin:8px 0}
a.pscan{color:var(--oro2);font-size:11px;text-decoration:none}
</style></head><body>
<h1>♛ Mappa Web3 — Destino Randagio</h1>
<div class="sub">Panoramica unica di token, contratti, tool e stato (vivo / deploy / in sviluppo / da fare). Indirizzi verificabili su Polygonscan. Tesoreria: <span class="ad"><a class="pscan" href="<?=scan($TES)?>" target="_blank"><?=h($TES)?></a></span>. Sola lettura.</div>
<div class="leg"><?php foreach($ST as $k=>$v){ echo badge($k); } ?></div>

<h2>1 · TOKEN (perché / cosa / come)</h2>
<div class="grid"><?php foreach($TOKENS as $t){ [$n,$a,$d,$s,$why,$what,$how]=$t; ?>
  <div class="card"><div class="nm"><?=h($n)?> <?=badge($s)?></div>
    <div class="ad">·<a href="<?=scan($a)?>" target="_blank"><?=h($a)?></a> · <?=h($d)?> dec</div>
    <div class="row"><span class="k">Perché:</span><?=h($why)?></div>
    <div class="row"><span class="k">Cosa:</span><?=h($what)?></div>
    <div class="row"><span class="k">Come:</span><?=h($how)?></div>
  </div><?php } ?></div>

<h2>2 · NFT (collezioni)</h2>
<div class="grid"><?php foreach($NFTS as $t){ [$n,$a,$ty,$s,$why,$what,$how]=$t; ?>
  <div class="card"><div class="nm"><?=h($n)?> <?=badge($s)?></div>
    <div class="ad">·<a href="<?=scan($a)?>" target="_blank"><?=h($a)?></a> · <?=h($ty)?></div>
    <div class="row"><span class="k">Perché:</span><?=h($why)?></div>
    <div class="row"><span class="k">Cosa:</span><?=h($what)?></div>
    <div class="row"><span class="k">Come:</span><?=h($how)?></div>
  </div><?php } ?></div>

<h2>3 · STAKING · POOL · VAULT · CONVERTER (tool on-chain, versione VIVA = V3)</h2>
<table><tr><th>Strumento</th><th>Indirizzo</th><th>Stato</th><th>Note</th></tr>
<?php foreach($TOOLSCH as $t){ [$n,$a,$s,$note]=$t; ?>
<tr><td><?=h($n)?></td><td class="ad"><a href="<?=scan($a)?>" target="_blank"><?=h(substr($a,0,10).'…'.substr($a,-6))?></a></td><td><?=badge($s)?></td><td><?=h($note)?></td></tr>
<?php } ?></table>
<div class="warn">⚠️ Un <b>pool 81X reale non esiste on-chain</b> (l'unico pool è il buyback Preda): va scritto da zero.</div>

<h2>4 · CONTRATTI NUOVI (questa fase)</h2>
<table><tr><th>Contratto</th><th>Stato</th><th>Cosa fa</th></tr>
<?php foreach($NUOVI as $t){ [$n,$s,$note]=$t; ?>
<tr><td><?=h($n)?></td><td><?=badge($s)?></td><td><?=h($note)?></td></tr><?php } ?></table>

<h2>5 · I 4 TOOL GENESYS — UTILITY SUITE (perché / cosa / come)</h2>
<div class="sub">Reward in DUX pagati da una <b>riserva reward pre-finanziata</b> (non conio): un nuovo lock è consentito solo se la copertura DUX regge (Tokenomics Safety Engine). Coefficienti congelati alla firma.</div>
<div class="grid"><?php foreach($G4 as $t){ [$n,$s,$why,$what,$how]=$t; ?>
  <div class="card"><div class="nm"><?=h($n)?> <?=badge($s)?></div>
    <div class="row"><span class="k">Perché:</span><?=h($why)?></div>
    <div class="row"><span class="k">Cosa:</span><?=h($what)?></div>
    <div class="row"><span class="k">Come:</span><?=h($how)?></div>
  </div><?php } ?></div>

<h2>6 · WEB3 TOOLS HUB (32 strumenti — <code>dr-web3-hub.php</code>)</h2>
<div class="sub"><b>9 nuovi implementati</b> (DRX interni):</div>
<div><?php foreach($T32_new as $x){ echo '<span class="pill">'.h($x).'</span>'; } ?></div>
<div class="sub" style="margin-top:8px"><b>16 che riusano motori esistenti</b> — ⚠️ <i>simil-doppioni</i> (stesso motore per più card):</div>
<table><tr><th>Tool</th><th>Motore riusato</th></tr>
<?php $eng=[]; foreach($T32_reuse as $t){ $eng[$t[1]][]=$t[0]; }
foreach($T32_reuse as $t){ [$n,$e]=$t; $dup=count($eng[$e])>1; ?>
<tr><td><?=h($n)?></td><td><?=h($e)?> <?=$dup?'<span class="bdg" style="border-color:#e0655a;color:#e0655a">doppione motore</span>':''?></td></tr>
<?php } ?></table>
<div class="sub" style="margin-top:8px"><b>7 on-chain bloccati</b> (stub onesti, <?=badge('todo')?>):</div>
<div><?php foreach($T32_locked as $x){ echo '<span class="pill">'.h($x).'</span>'; } ?></div>

<h2>7 · GIOCHI DEL BRANCO (<?=badge('todo')?>)</h2>
<div class="warn">⚠️ Meccaniche da rivedere compliance prima di costruire (esito pilotato / "non perde mai" / rendita 0,1%gg / puntate NFT a valore USDT ⟶ contrastano con le regole del progetto e con la normativa gioco).</div>
<div><?php foreach($GIOCHI as $g){ echo '<span class="pill">'.h($g[0]).' · '.h($g[1]).'</span>'; } ?></div>

<h2>8 · DOPPIONI / DA NON PUBBLICARE MAI</h2>
<table><tr><th>Cosa</th><th>Indirizzo</th><th>Nota</th></tr>
<?php foreach($DOPPIONI as $t){ [$n,$a,$note]=$t; ?>
<tr><td><?=h($n)?></td><td class="ad"><?=h($a)?></td><td><?=badge('dismesso')?> <?=h($note)?></td></tr><?php } ?></table>
<div class="warn">Questi indirizzi sono ancora citati in alcuni file del sito (<code>.env</code> riga 84, <code>genesys/web3-config-migra.php</code>, pagine <code>doc-*.html</code>): vanno bonificati e portati ai V3 prima di andare pubblici.</div>

<h2>9 · WALLET & BACKEND</h2>
<div class="sub">118 wallet custodial (<code>GENESYS_118_NODE_MATRIX.csv</code>) · bind con firma (<code>dr-wallet-bind.php</code>) · firma-sul-PC (<code>funding/</code>). Motori: <code>dr-web3-onchain.php</code> (saldi live), <code>dr-web3-hub.php</code> (32 tool), <code>dr-ledger.php</code> + <code>dr-withdraw-queue.php</code> (Blocco 2), <code>dr-block1-onchain.php</code>. Documenti: whitepaper (ecosistema/DRX/81X/DUX), tokenomics, litepaper, hellopaper, roadmap, audit.</div>

<div class="sub" style="margin-top:20px;color:#6b5f4a">Fonte: letture on-chain verificate + audit dei 2.332 file web3 + documenti di progetto. Pagina di sola lettura, aggiornabile modificando gli array in cima al file.</div>
</body></html>
