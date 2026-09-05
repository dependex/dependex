<?php
/** MY NETWORK — il tuo ramo: navighi in giu' livello per livello, vedi persone, ranghi, giro d'affari in $. */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
$IO = demo_io();
$S = demo_stato($IO); $G = demo_gettone();
$P = demo_persone();
if (!isset($P[$IO])) $P[$IO] = ['nome' => demo_nome($IO), 'sic' => (string)(demo_account($IO)['sic'] ?? '—'), 'nft' => 0];

// il nodo che stai guardando: deve stare nel TUO ramo, altrimenti torni a te
function net_nel_ramo(string $radice, string $x): bool {
    if ($radice === $x) return true;
    foreach (demo_figli($radice) as $f) if (net_nel_ramo($f, $x)) return true;
    return false;
}
function net_sotto(string $u): int { $n = 0; foreach (demo_figli($u) as $f) $n += 1 + net_sotto($f); return $n; }
function net_percorso(string $radice, string $x, array $acc = []): ?array {
    $acc[] = $radice; if ($radice === $x) return $acc;
    foreach (demo_figli($radice) as $f) { $r = net_percorso($f, $x, $acc); if ($r) return $r; }
    return null;
}
$qui = (string)($_GET['u'] ?? $IO);
if (!isset($P[$qui]) || !net_nel_ramo($IO, $qui)) $qui = $IO;
$SQ = demo_stato($qui);
$figli = demo_figli($qui);
$perc = net_percorso($IO, $qui) ?? [$IO];
$cerca = trim((string)($_GET['q'] ?? ''));
$trovati = [];
if ($cerca !== '') foreach ($P as $u => $p) if (net_nel_ramo($IO, $u) && (stripos($p['nome'], $cerca) !== false || stripos($p['sic'], $cerca) !== false || stripos($u, $cerca) !== false)) $trovati[] = $u;
// l'albero per il tool visuale: la radice e' il nodo che stai guardando, giu' fino a 4 livelli
function net_json(string $u, int $prof, array $P): array {
    $st = demo_stato($u);
    $o = ['id' => $u, 'n' => demo_nome($u), 'r' => (int)$st['rango']['livello'], 'rn' => (string)$st['rango']['nome'],
          'x' => (string)$st['xp']['totale'], 'xo' => (string)$st['xp']['proprio'], 'sotto' => net_sotto($u), 'dir' => count(demo_figli($u)),
          'sic' => (string)($P[$u]['sic'] ?? ''), 'nft' => (int)($P[$u]['nft'] ?? 0), 'pr' => (int)($st['prestigio']['livello'] ?? 0), 'prn' => (string)($st['prestigio']['nome'] ?? ''), 'f' => []];
    if ($prof > 0) foreach (demo_figli($u) as $f) $o['f'][] = net_json($f, $prof - 1, $P);
    return $o;
}
$ALBERO = net_json($qui, 9, $P);
/* ADMIN: la rete VERA del sito (fino a 5.000.000 di posizioni), a caricamento progressivo via net-admin-api.php */
$MASTER = demo_admin_sessione() && (($_GET['vista'] ?? '') !== 'mia');
$MASTER_OK = demo_env('DR_ADMIN_KEY', '') !== '';
require __DIR__ . '/_testa.php';
?>
<section class="vista on">
  <div class="carta" style="display:flex;gap:10px;align-items:center">
    <div style="flex:1"><div class="eti">Your branch</div>
      <div class="medio" style="font-size:19px"><?= net_sotto($IO) ?> people</div>
      <div class="sotto"><?= count(demo_figli($IO)) ?> direct · turnover <?= dollari($S['xp']['rete']) ?> · <a href="account.php?s=ref" style="color:var(--oro-chiaro)">your referral link →</a></div></div>
    <?= dric_ui('network', 34) ?>
  </div>
  <?php if (demo_admin_sessione()): ?>
  <div class="carta" style="display:flex;gap:8px;align-items:center;padding:9px 12px;flex-wrap:wrap">
    <div style="flex:1;min-width:160px"><div class="eti">Admin view</div><div class="sub"><?= $MASTER ? 'Whole network from the site database (Master · 9 World · 27 National · 82 Pro · up to 5,000,000 positions), loaded branch by branch as you tap.' : 'Your dapp branch only.' ?></div></div>
    <div class="segm"><a class="<?= $MASTER ? 'on' : '' ?>" href="network.php" style="text-decoration:none;padding:6px 10px;font:700 10px Inter,sans-serif;letter-spacing:.08em">WHOLE NETWORK</a><a class="<?= $MASTER ? '' : 'on' ?>" href="network.php?vista=mia" style="text-decoration:none;padding:6px 10px;font:700 10px Inter,sans-serif;letter-spacing:.08em">MY BRANCH</a></div>
    <?php if ($MASTER && !$MASTER_OK): ?><div class="sub" style="width:100%;color:#f2dba4">DR_ADMIN_KEY is not in the .env: the site network cannot be read yet — showing the dapp branch.</div><?php endif; ?>
  </div>
  <?php endif; ?>

  <form method="get" style="display:flex;gap:8px;margin-bottom:12px"><input name="q" value="<?= e($cerca) ?>" placeholder="Search a person in your branch (name, ID)" style="flex:1;min-width:0;width:auto"><button class="b mini" style="margin:0">Find</button></form>
  <?php if ($cerca !== ''): ?>
    <div class="eti" style="margin:0 2px 8px"><?= count($trovati) ?> found</div>
    <?php foreach ($trovati as $u): $st = demo_stato($u); ?>
      <a class="nodo" href="network.php?u=<?= e($u) ?>"><?= dric_rango($st['rango']['livello'], 26) ?><div><div class="n"><?= e(demo_nome($u)) ?></div><div class="s"><?= e($P[$u]['sic']) ?> · <?= e($st['rango']['nome']) ?></div></div>
        <div class="v"><b><?= dollari($st['xp']['totale']) ?></b><span><?= net_sotto($u) ?> below</span></div></a>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="brici"><?php foreach ($perc as $i => $u): ?><?= $i ? '<span>›</span>' : '' ?><a href="network.php?u=<?= e($u) ?>"><?= e($u === $IO ? 'You' : demo_nome($u)) ?></a><?php endforeach; ?></div>

  <div class="nodo me"><?= dric_rango($SQ['rango']['livello'], 30) ?>
    <div><div class="n"><?= e($qui === $IO ? 'You — ' . demo_nome($IO) : demo_nome($qui)) ?></div>
      <div class="s"><?= e($P[$qui]['sic']) ?> · <?= e($SQ['rango']['nome']) ?> · <?= (int)$P[$qui]['nft'] ?> NFT · <?= dric_prestigio($SQ['prestigio']['livello'] ?? 0, (int)$P[$qui]['nft'], 14) ?></div></div>
    <div class="v"><b><?= dollari($SQ['xp']['totale']) ?></b><span><?= dollari($SQ['xp']['proprio']) ?> own · <?= net_sotto($qui) ?> below</span></div>
  </div>

  <div class="carta" style="padding:10px 12px 12px">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap"><div class="eti" style="flex:1;min-width:120px">Network view — tap a name: card + branch opens</div>
      <div class="segm" role="group"><button type="button" class="on" id="v2d" onclick="alberoVista('2d')">2D</button><button type="button" id="v3d" onclick="alberoVista('3d')">3D</button></div>
      <button type="button" class="b mini" style="margin:0" onclick="alberoTutti()">Expand all</button>
      <button type="button" class="b mini" style="margin:0" onclick="alberoReset()">Reset</button></div>
    <div id="albero" style="height:440px;border:1px solid var(--bordo);border-radius:12px;background:radial-gradient(ellipse at 50% 45%,#141009 0%,#0a0806 70%);overflow:hidden;touch-action:none;position:relative">
      <svg id="alberoSvg" width="100%" height="100%" style="display:block;touch-action:none"><g id="alberoG"></g></svg>
      <div id="scheda" class="scheda" style="display:none"></div>
    </div>
    <div class="sotto" style="margin-top:6px">You at the centre. Names only on the map; tap one for its card (username, SIC-ID, rank, numbers) and to open or close its branch. Drag to pan (2D) or rotate (3D), pinch/wheel to zoom.</div>
  </div>
  <style>
    .segm{display:inline-flex;border:1px solid var(--bordo);border-radius:9px;overflow:hidden}
    .segm button{background:transparent;border:0;color:var(--tenue);font:700 10px Inter,sans-serif;letter-spacing:.08em;padding:6px 10px;cursor:pointer}
    .segm button.on,.segm a.on{background:linear-gradient(135deg,var(--oro),#b8933a);color:#120e07} .segm a{color:var(--tenue)}
    .scheda{position:absolute;left:8px;right:8px;bottom:8px;background:rgba(12,10,6,.94);border:1px solid rgba(217,180,90,.45);border-radius:12px;padding:10px 12px;backdrop-filter:blur(6px);box-shadow:0 8px 30px rgba(0,0,0,.6);z-index:3}
    .scheda .n{font-family:Cinzel,serif;font-size:14px;color:var(--oro-chiaro);letter-spacing:.06em}
    .scheda .k{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;margin-top:7px}
    .scheda .k div{background:rgba(217,180,90,.06);border:1px solid var(--bordo);border-radius:8px;padding:5px 7px;font-size:9px;color:var(--tenue)}
    .scheda .k b{display:block;font-size:12px;color:#f2e9d8;font-variant-numeric:tabular-nums}
    .scheda .az{display:flex;gap:6px;margin-top:8px}
    .scheda .az .b{margin:0;padding:8px 10px;font-size:10px;flex:1}
    .scheda .x{position:absolute;top:6px;right:8px;background:none;border:0;color:var(--tenue);font-size:16px;cursor:pointer}
    @media(min-width:640px){.scheda{left:auto;width:300px}}
  </style>

  <div class="eti" style="margin:10px 2px 8px">Direct line of <?= e($qui === $IO ? 'you' : demo_nome($qui)) ?> (<?= count($figli) ?>)</div>
  <?php if (!$figli): ?><div class="carta" style="text-align:center"><div class="sotto">No one below this position yet.</div></div><?php endif; ?>
  <div class="griglia-prod">
  <?php foreach ($figli as $u): $st = demo_stato($u); $sotto = net_sotto($u); ?>
    <a class="nodo" href="network.php?u=<?= e($u) ?>" style="margin:0"><?= dric_rango($st['rango']['livello'], 26) ?>
      <div><div class="n"><?= e(demo_nome($u)) ?></div><div class="s"><?= e($P[$u]['sic']) ?> · <?= e($st['rango']['nome']) ?> · <?= (int)$P[$u]['nft'] ?> NFT</div></div>
      <div class="v"><b><?= dollari($st['xp']['totale']) ?></b><span><?= $sotto ?> below<?= $sotto ? ' ›' : '' ?></span></div></a>
  <?php endforeach; ?>
  </div>

  <div class="carta" style="margin-top:12px">
    <div class="eti" style="margin-bottom:6px">Levels under you</div>
    <?php $liv = [$IO]; $d = 1; while ($liv && $d <= 9) { $next = []; foreach ($liv as $u) foreach (demo_figli($u) as $f) $next[] = $f; if (!$next) break;
      $tot = '0'; foreach ($next as $u) $tot = bigi_add($tot, (string)demo_stato($u)['xp']['proprio']);
      echo '<div class="riga"><span class="pal">L' . $d . '</span><div class="mid"><div class="tit2">' . count($next) . ' people</div></div><div class="val">' . dollari($tot) . '</div></div>';
      $liv = $next; $d++; } ?>
  </div>
  <div class="franco">You see <b>your branch only</b>, in full: every person, their rank and turnover. Turnover is DUX activated (own + network), shown in $. Balances and wallets are never shown.</div>
</section>
<script>
(function(){
  var D=<?= json_encode($ALBERO, JSON_UNESCAPED_UNICODE) ?>, ME=<?= json_encode($IO) ?>, MASTER=<?= json_encode($MASTER && $MASTER_OK) ?>;
  /* --- modalita' MASTER (admin): albero vero del sito, caricato a rami. Un nodo = {posto, n, sic, rn(tipo), sotto(rete), dir, x(rete_euro), stato, _car(caricato)} --- */
  function nodoDaSito(r){ var vuoto=!(r.uid>0)&&(r.stato==='libero'||!r.nome); return {id:'p'+r.posto, posto:r.posto, n:(r.piu?('+'+r.piu+' more'):(r.nome||('#'+r.posto+(vuoto?' free':'')))), sic:r.sic||'', rn:(r.tipo||'')+(r.stato&&r.stato!=='attivo'?' · '+r.stato:''), x:r.rete_euro||0, xo:0, sotto:r.rete||0, dir:r.rete_diretti||0, nft:0, pr:0, prn:'', stato:r.stato||'', livello:r.livello||0, piu:r.piu||0, _car:!!r.caricato, _pag:0, f:[]}; }
  function innesta(n, r){ n.f=(r.children||r._figli||[]).map(function(c){ var m=nodoDaSito(c); if(c.children||c._figli) innesta(m,c); return m; }); n._car=true; }
  function caricaFigli(n, cb){ var lim=60; fetch('net-admin-api.php?azione=figli&posto='+n.posto+'&limit='+lim+'&offset='+(n._pag*lim),{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(d){
      if(!d||!d.ok){ alert(d&&d.err?d.err:'network unreachable'); return; }
      var lista=(d.figli||[]).map(nodoDaSito); n.f=n.f.filter(function(x){return !x.piu;}).concat(lista); n._pag++;
      var tot=d.tot_figli||d.tot||0, resto=tot-n.f.length; if(resto>0){ var p=nodoDaSito({posto:-n.posto*100000-n._pag, nome:'', piu:resto}); p._padre=n; n.f.push(p); }
      n._car=true; n._ap=true; if(cb)cb(); }).catch(function(){ alert('network unreachable'); }); }
  if(MASTER){ D={id:'root',posto:0,n:'Loading the network…',sic:'',rn:'',x:0,xo:0,sotto:0,dir:0,nft:0,pr:0,f:[],_car:false,_pag:0,_ap:true};
    fetch('net-admin-api.php?azione=vista',{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(d){ if(!d||!d.ok){ D.n=(d&&d.err)||'unreachable'; disegna(); return; }
      var a=d.albero; D=nodoDaSito(a); D.n=a.nome||'MASTER NODE'; innesta(D,a); D._ap=true; D.f.forEach(function(w){ w._ap=true; }); disegna(); }).catch(function(){ D.n='network unreachable'; disegna(); }); }
  var svg=document.getElementById('alberoSvg'), g=document.getElementById('alberoG'), box=document.getElementById('albero'), sch=document.getElementById('scheda');
  var W=box.clientWidth||360, H=440, CX=W/2, CY=H/2, PASSO=Math.min(92, Math.max(64, Math.min(W,H)/2.6/4)), zoomAuto=false;
  var NS='http://www.w3.org/2000/svg'; function el(t,a){var e=document.createElementNS(NS,t);for(var k in a)e.setAttribute(k,a[k]);return e;}
  var oro='#d9b45a', chiaro='#f2dba4', tenue='rgba(242,233,216,.55)';
  var vista='2d', rot=0, tilt=0.95, sel=null, nodi=[], archi=[], MAP={};
  /* stato aperto/chiuso: la radice e la sua linea diretta aperte, il resto chiuso */
  (function init(n,liv){ n._ap = liv<1; n.f.forEach(function(c){ init(c,liv+1); }); })(D,0);
  function foglie(n){ return (n._ap&&n.f.length)? n.f.reduce(function(a,c){return a+foglie(c);},0) : 1; }
  function posa(n,a0,a1,liv){ var am=(a0+a1)/2, r=liv*PASSO; n._a=am; n._r=r; n._liv=liv; nodi.push(n);
    if(!n._ap) return; var tot=foglie(n), cur=a0; n.f.forEach(function(c){ var w=(a1-a0)*foglie(c)/tot; posa(c,cur,cur+w,liv+1); archi.push([n,c]); cur+=w; }); }
  /* proiezione: 2D piana, 3D = anelli su un piano inclinato che ruota (prospettiva) */
  function proietta(n){
    if(vista==='2d'){ n._x=CX+(n._liv?n._r*Math.cos(n._a):0); n._y=CY+(n._liv?n._r*Math.sin(n._a):0); n._z=0; n._k=1; return; }
    var a=n._a+rot, x=n._r*Math.cos(a), z=n._r*Math.sin(a), y=-n._liv*10;      // ogni livello sale un poco: una spirale di anelli
    var yc=y*Math.cos(tilt)-z*Math.sin(tilt), zc=y*Math.sin(tilt)+z*Math.cos(tilt);
    var F=560, k=F/(F+zc); n._x=CX+x*k; n._y=CY+yc*k; n._z=zc; n._k=k;
  }
  function disegna(){
    nodi=[]; archi=[]; posa(D,-Math.PI/2,Math.PI*1.5,0);
    var passoBase=Math.min(92, Math.max(64, Math.min(W,H)/2.6/4)); PASSO=nodi.length>48?passoBase*Math.min(2.2,1+nodi.length/120):passoBase; nodi.forEach(function(n){ n._r=n._liv*PASSO; }); nodi.forEach(proietta);
    while(g.firstChild) g.removeChild(g.firstChild);
    var maxL=0; nodi.forEach(function(n){ if(n._liv>maxL) maxL=n._liv; });
    for(var k=1;k<=Math.max(1,maxL);k++){
      if(vista==='2d') g.appendChild(el('circle',{cx:CX,cy:CY,r:k*PASSO,fill:'none',stroke:'rgba(217,180,90,.10)','stroke-width':1,'stroke-dasharray':'3 5'}));
      else { var pts=[]; for(var i=0;i<=48;i++){ var f={_a:i/48*Math.PI*2,_r:k*PASSO,_liv:k}; proietta(f); pts.push(f._x.toFixed(1)+','+f._y.toFixed(1)); }
             g.appendChild(el('polyline',{points:pts.join(' '),fill:'none',stroke:'rgba(217,180,90,.12)','stroke-width':1,'stroke-dasharray':'3 5'})); }
    }
    archi.forEach(function(a){ var p=a[0],c=a[1]; g.appendChild(el('line',{x1:p._x,y1:p._y,x2:c._x,y2:c._y,stroke:'rgba(217,180,90,'+(vista==='3d'?(0.18+0.25*c._k):0.35)+')','stroke-width':1.2})); });
    var ord=nodi.slice().sort(function(a,b){ return (b._z||0)-(a._z||0); });   // i lontani prima
    ord.forEach(function(n){ var base=Math.min(20,9+Math.sqrt(n.sotto)*2), r=base*(n._k||1), io=(n.id===ME), radice=(n===D), s=(sel===n);
      var grp=el('g',{style:'cursor:pointer','data-uid':n.id,opacity:vista==='3d'?String(0.55+0.45*n._k):'1'}); MAP[n.id]=n;
      grp.appendChild(el('circle',{cx:n._x,cy:n._y,r:r+4,fill:'none',stroke:s?'#ffffff':(io?oro:'rgba(217,180,90,.25)'),'stroke-width':(io||s)?2:1}));
      grp.appendChild(el('circle',{cx:n._x,cy:n._y,r:r,fill:radice?'#241d10':(n._ap&&n.f.length?'#1c160c':'#131009'),stroke:oro,'stroke-width':1}));
      if(n.f.length){ var t=el('text',{x:n._x,y:n._y+4*(n._k||1),'text-anchor':'middle',fill:chiaro,'font-size':String(10*(n._k||1)),'font-weight':'700','font-family':'Cinzel,serif'}); t.textContent=n._ap?'−':'+'+n.f.length; grp.appendChild(t); }
      /* con molti nodi (rete vera) le etichette si diradano: restano quelle dei rami aperti/con gente sotto e del nodo scelto */
      var molti=nodi.length>48, mostra=!molti||s||n===D||n._liv<=1||n.sotto>0||n.piu;
      if(mostra){ var t2=el('text',{x:n._x,y:n._y+r+14,'text-anchor':'middle',fill:s?'#ffffff':'#f2e9d8','font-size':String((molti?9:10.5)*(n._k||1)),'font-weight':'600','font-family':'Inter,sans-serif'}); t2.textContent=n.n.length>18?n.n.slice(0,17)+'…':n.n; grp.appendChild(t2); }
      g.appendChild(grp); });
    agg();
  }
  function scegli(n){
    if(MASTER){
      if(n.piu){ caricaFigli(n._padre, function(){ disegna(); scheda(n._padre); }); return; }
      if(!n._car){ sel=n; caricaFigli(n, function(){ disegna(); scheda(n); }); return; }
    }
    if(sel===n){ n._ap=!n._ap; } else { sel=n; if(n.f.length && !n._ap) n._ap=true; }
    disegna(); scheda(n);
  }
  function scheda(n){
    var fmt=function(v){ return '$'+Number(v).toLocaleString('en-US'); };
    sch.style.display='';
    sch.innerHTML='<button class="x" type="button" aria-label="close">×</button>'
      +'<div class="n">'+esc(n.n)+(n.id===ME?' <span style="font-size:10px;color:var(--tenue)">you</span>':'')+'</div>'
      +'<div class="sotto" style="margin-top:2px">'+esc(n.sic||'—')+' · '+esc(n.rn)+(n.pr?' · '+esc(n.prn):'')+'</div>'
      +(MASTER
        ? '<div class="k"><div>Position<b>#'+n.posto+'</b></div><div>Network €<b>'+Number(n.x).toLocaleString('en-US')+'</b></div><div>People below<b>'+Number(n.sotto).toLocaleString('en-US')+'</b></div><div>Direct<b>'+n.dir+'</b></div><div>Status<b>'+esc(n.stato||'—')+'</b></div><div>Level<b>L'+(n.livello||n._liv)+'</b></div></div>'
        : '<div class="k"><div>Turnover<b>'+fmt(n.x)+'</b></div><div>Own<b>'+fmt(n.xo)+'</b></div><div>NFT<b>'+n.nft+'</b></div><div>Direct<b>'+n.dir+'</b></div><div>Below<b>'+n.sotto+'</b></div><div>Level<b>L'+n._liv+'</b></div></div>')
      +'<div class="az">'+((n.f.length||(MASTER&&!n._car))?'<button type="button" class="b mini" id="schTog">'+(n._ap&&n._car?'Close branch':'Open branch'+(n.f.length?' ('+n.f.length+')':''))+'</button>':'')
      +(!MASTER&&(n.id!==ME||n!==D)?'<a class="b mini pieno" href="network.php?u='+encodeURIComponent(n.id)+'" style="text-align:center;text-decoration:none">Go to '+esc(n.n)+'</a>':'')
      +(MASTER&&n.posto>=0?'<a class="b mini pieno" href="https://destinorandagio.it/genesys/albero-network.php?posto='+n.posto+'" target="_blank" rel="noopener" style="text-align:center;text-decoration:none">Open on the project website</a>':'')+'</div>';
    sch.querySelector('.x').onclick=function(){ sch.style.display='none'; sel=null; disegna(); };
    var tg=document.getElementById('schTog'); if(tg) tg.onclick=function(){ if(MASTER&&!n._car){ caricaFigli(n,function(){disegna();scheda(n);}); return; } n._ap=!n._ap; disegna(); scheda(n); };
  }
  function esc(s){ return String(s).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
  /* pan & zoom (2D) · rotazione (3D) */
  var s=1,tx=0,ty=0; function agg(){ g.setAttribute('transform','translate('+tx+' '+ty+') scale('+s+')'); }
  window.alberoReset=function(){ s=1; tx=0; ty=0; rot=0; sel=null; sch.style.display='none'; (function init(n,liv){ n._ap = liv<1; n.f.forEach(function(c){ init(c,liv+1); }); })(D,0); disegna(); };
  window.alberoTutti=function(){ (function ap(n){ if(n.f.length) n._ap=true; n.f.forEach(ap); })(D); disegna(); };
  window.alberoVista=function(v){ vista=v; document.getElementById('v2d').className=v==='2d'?'on':''; document.getElementById('v3d').className=v==='3d'?'on':''; s=1; tx=0; ty=0; disegna(); if(v==='3d') gira(); };
  /* tap = seleziona il nodo (scheda + apre/chiude il ramo); trascinamento = pan/rotazione. Niente pointer capture: su telefono
     rubava il click ai nodi (la scheda non si apriva). Il tap si risolve su pointerup se il dito non si e' mosso. */
  var drag=null, mosso=false; box.addEventListener('pointerdown',function(e){ if(e.target.closest('#scheda')) return; drag={x:e.clientX,y:e.clientY,tx:tx,ty:ty,rot:rot}; mosso=false; });
  box.addEventListener('pointermove',function(e){ if(!drag)return; var dx=e.clientX-drag.x, dy=e.clientY-drag.y; if(Math.abs(dx)+Math.abs(dy)>3) mosso=true;
    if(vista==='2d'){ tx=drag.tx+dx; ty=drag.ty+dy; agg(); } else { rot=drag.rot+dx/140; ty=drag.ty+dy*0.5; disegna(); } });
  box.addEventListener('pointerup',function(e){ var era=drag; drag=null; if(era&&!mosso){ var g=e.target.closest&&e.target.closest('g[data-uid]'); if(!g){ var el2=document.elementFromPoint(e.clientX,e.clientY); g=el2&&el2.closest?el2.closest('g[data-uid]'):null; } if(g&&MAP[g.getAttribute('data-uid')]) scegli(MAP[g.getAttribute('data-uid')]); } });
  box.addEventListener('pointercancel',function(){drag=null;});
  box.addEventListener('click',function(e){ e.preventDefault(); },true);
  box.addEventListener('wheel',function(e){ e.preventDefault(); var k=e.deltaY<0?1.12:0.89; var r=box.getBoundingClientRect(), mx=e.clientX-r.left, my=e.clientY-r.top; tx=mx-(mx-tx)*k; ty=my-(my-ty)*k; s*=k; agg(); },{passive:false});
  var pinch=null; box.addEventListener('touchstart',function(e){ if(e.touches.length===2){ pinch=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY); drag=null; } },{passive:true});
  box.addEventListener('touchmove',function(e){ if(e.touches.length===2&&pinch){ var d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY); var k=d/pinch; pinch=d; var cx=W/2, cy=H/2; tx=cx-(cx-tx)*k; ty=cy-(cy-ty)*k; s*=k; agg(); } },{passive:true});
  box.addEventListener('touchend',function(){pinch=null;});
  /* 3D: rotazione lenta finche' non tocchi */
  var anim=null; function gira(){ if(anim) cancelAnimationFrame(anim); (function passo(){ if(vista!=='3d'){anim=null;return;} if(!drag) { rot+=0.0025; disegna(); } anim=requestAnimationFrame(passo); })(); }
  disegna();
})();
</script>
<?php require __DIR__ . '/_piede.php'; ?>
