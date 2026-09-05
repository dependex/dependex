<?php
/**
 * MAPPA DEL MONDO DAO BRANCH — una card a larghezza pagina, 2D (planisfero) e 3D (globo), paesi in filo d'oro.
 * Quattro strati: Associations (anziani, mondo + Italia con ASL) · Forests (Treedom / DAO BRANCH forests) · Nodes (i 118) · Global.
 * Tap su un punto -> scheda sotto la mappa. Dati da _charity-data.php; contorni da home/assets/world.json (177 paesi).
 * Uso: require '_mappa.php'; poi echo mappa_card();  Additivo, nessuna dipendenza da librerie.
 */
declare(strict_types=1);
require_once __DIR__ . '/_charity-data.php';

function mappa_dati(): array
{
    $P = [];
    foreach (charity_associazioni() as $a) $P[] = ['l' => 'assoc', 'n' => $a[0], 'c' => $a[1] . ' · ' . $a[2], 'la' => $a[3], 'lo' => $a[4], 'd' => $a[5], 'w' => $a[6]];
    $it = charity_italia();
    foreach ($it['nazionali'] as $a) $P[] = ['l' => 'assoc', 'n' => $a[0], 'c' => 'Italy · ' . $a[1], 'la' => $a[2], 'lo' => $a[3], 'd' => $a[4], 'w' => $a[5]];
    foreach ($it['asl'] as $a) $P[] = ['l' => 'assoc', 'n' => 'ASL · ' . $a[0], 'c' => 'Italy · ' . $a[2], 'la' => $a[3], 'lo' => $a[4], 'd' => 'local health authorities: ' . $a[1], 'w' => ''];
    foreach (charity_foreste() as $f) $P[] = ['l' => 'forest', 'n' => $f[0], 'c' => $f[1] . ' · ' . $f[2], 'la' => $f[3], 'lo' => $f[4], 'd' => $f[5], 'w' => $f[6]];
    foreach (charity_nodi() as $n) $P[] = ['l' => 'node', 't' => $n['tipo'], 'k' => (int)$n['n'], 'n' => '#' . $n['n'] . ' · ' . $n['tipo'], 'c' => $n['nome'] . ($n['nome'] !== $n['citta'] ? ' · ' . $n['citta'] : ''), 'la' => $n['lat'], 'lo' => $n['lon'], 'd' => 'position in the 118 · assigned by draw', 'w' => ''];
    return $P;
}

function mappa_card(string $altezza = '380px', string $maxh = '62vw'): string
{
    $P = mappa_dati();
    $na = count(array_filter($P, fn($x) => $x['l'] === 'assoc')); $nf = count(array_filter($P, fn($x) => $x['l'] === 'forest')); $nn = count(array_filter($P, fn($x) => $x['l'] === 'node'));
    $json = json_encode($P, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ob_start(); ?>
<div class="carta" id="mappaCard" style="margin:0 0 11px;padding:12px 12px 10px">
  <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px">
    <div style="flex:1;min-width:170px"><div class="eti">BLOCKCHAINPLUS.DAO World</div>
      <div class="sub" id="mappaLeg"><?= $na ?> elderly associations · <?= $nf ?> forests · <?= $nn ?> nodes — tap a point</div></div>
    <div style="text-align:right;flex:none"><div class="eti" style="font-size:8px">CO₂ saved · live</div><div class="medio" style="font-size:15px;color:#9ccf7a"><span data-conta="<?= charity_co2_kg() ?>"><?= number_format(charity_co2_kg(), 0, '', ',') ?></span> <small style="font-size:9px;color:var(--tenue)">kg</small></div></div>
    <div class="segm" id="mappaVista"><button type="button" class="on" data-v="2d">2D</button><button type="button" data-v="3d">3D</button></div>
  </div>
  <div class="segm" id="mappaStrati" style="display:flex;width:100%;margin-bottom:8px"><button type="button" data-l="assoc" style="flex:1">Associations</button><button type="button" data-l="forest" style="flex:1">Forests</button><button type="button" data-l="node" style="flex:1">Nodes</button><button type="button" data-l="all" class="on" style="flex:1">Global view</button></div>
  <div id="mappaBox" style="position:relative;height:<?= e($altezza) ?>;max-height:<?= e($maxh) ?>;min-height:230px;border-radius:12px;overflow:hidden;background:radial-gradient(ellipse at 50% 45%,#12100a,#070605 75%);border:1px solid rgba(217,180,90,.25);touch-action:none">
    <canvas id="mappaCv" style="position:absolute;inset:0;width:100%;height:100%;display:block;cursor:grab"></canvas>
    <div id="mappaHint" class="sub" style="position:absolute;left:10px;bottom:8px;pointer-events:none;color:rgba(242,233,216,.55)">drag to move · pinch or wheel to zoom</div>
    <div style="position:absolute;right:8px;bottom:8px;display:flex;flex-direction:column;gap:5px"><button type="button" id="mappaPiu" class="mz" title="Zoom in">+</button><button type="button" id="mappaMeno" class="mz" title="Zoom out">−</button><button type="button" id="mappaReset" class="mz" title="Reset view" style="font-size:11px">⟲</button></div>
    <style>.mz{width:30px;height:30px;border-radius:50%;border:1px solid rgba(217,180,90,.6);background:rgba(10,8,6,.85);color:var(--oro-chiaro);font:700 16px/1 Inter,sans-serif;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.6)}.mz:active{background:rgba(217,180,90,.25)}</style>
  </div>
  <div id="mappaScheda" class="carta" style="display:none;margin:9px 0 0;padding:10px 12px;border-color:rgba(242,219,164,.6)"></div>
  <div class="sub" style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;align-items:center"><span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#f2dba4;vertical-align:middle"></i> associations</span><span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#9ccf7a;vertical-align:middle"></i> forests</span><span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#fff4cf;box-shadow:0 0 6px #f2dba4;vertical-align:middle"></i> World · <i style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#d9b45a;vertical-align:middle"></i> National · <i style="display:inline-block;width:5px;height:5px;border-radius:50%;background:#b8933a;vertical-align:middle"></i> Pro nodes</span><a href="charity.php" style="margin-left:auto;color:var(--oro-chiaro)">10% elderly · 5% forests — how it works ›</a></div>
</div>
<script>
(function(){
  var P=<?= $json ?>, cv=document.getElementById('mappaCv'), box=document.getElementById('mappaBox'), sch=document.getElementById('mappaScheda'), leg=document.getElementById('mappaLeg');
  if(!cv||!cv.getContext) return;
  var x=cv.getContext('2d'), W=0,H=0,DPR=Math.min(devicePixelRatio||1,2), MONDO=null, vista='2d', strato='all', sel=null;
  var s2=1, ox=0, oy=0;                 // 2D: zoom e spostamento
  var lam=15, phi=18, auto=true, s3=1;  // 3D: rotazione (gradi), inclinazione, zoom
  var RID=matchMedia('(prefers-reduced-motion:reduce)').matches; if(RID) auto=false;
  function misura(){ var r=box.getBoundingClientRect(); W=Math.max(10,r.width); H=Math.max(10,r.height); cv.width=Math.round(W*DPR); cv.height=Math.round(H*DPR); x.setTransform(DPR,0,0,DPR,0,0); disegna(); }
  fetch('../home/assets/world.json').then(function(r){return r.json();}).then(function(d){ MONDO=d; disegna(); }).catch(function(){});
  var D2R=Math.PI/180;
  /* 2D: equirettangolare, la mappa riempie la larghezza e si centra */
  function p2(lo,la){ var base=Math.max(W/360,H/175); return [W/2+(lo*base*s2)+ox, H/2-(la*base*s2)+oy]; }
  /* 3D: ortografica su una sfera di raggio R; torna null se il punto sta dietro */
  function R(){ return Math.min(W,H)*0.44*s3; }
  function p3(lo,la){ var l=(lo-lam)*D2R, f=la*D2R, f0=phi*D2R; var cosc=Math.sin(f0)*Math.sin(f)+Math.cos(f0)*Math.cos(f)*Math.cos(l); var r=R();
    var px=W/2+r*Math.cos(f)*Math.sin(l), py=H/2-r*(Math.cos(f0)*Math.sin(f)-Math.sin(f0)*Math.cos(f)*Math.cos(l)); return [px,py,cosc]; }
  function visibile(p){ if(strato==='all') return true; return p.l===strato; }
  function colore(p){ if(p.l==='forest') return '#9ccf7a'; if(p.l==='node') return p.t==='World Node'?'#fff4cf':(p.t==='National Node'?'#d9b45a':'#b8933a'); return '#f2dba4'; }
  function conA(h,a){ return 'rgba('+parseInt(h.substr(1,2),16)+','+parseInt(h.substr(3,2),16)+','+parseInt(h.substr(5,2),16)+','+a+')'; }
  function raggio(p){ if(p.l==='node') return p.t==='World Node'?4.2:(p.t==='National Node'?3.2:2.2); return p.l==='forest'?3:2.6; }
  var T0=performance.now();
  function disegna(){
    x.clearRect(0,0,W,H); var t=(performance.now()-T0)/1000;
    if(vista==='3d'){ var r=R(); var g=x.createRadialGradient(W/2-r*0.35,H/2-r*0.4,r*0.1,W/2,H/2,r); g.addColorStop(0,'rgba(60,48,26,.55)'); g.addColorStop(1,'rgba(8,7,5,.9)'); x.beginPath(); x.arc(W/2,H/2,r,0,Math.PI*2); x.fillStyle=g; x.fill();
      x.strokeStyle='rgba(242,219,164,.55)'; x.lineWidth=1.2; x.stroke(); x.shadowColor='rgba(217,180,90,.5)'; x.shadowBlur=18; x.stroke(); x.shadowBlur=0; }
    // graticola leggera
    x.strokeStyle='rgba(217,180,90,.09)'; x.lineWidth=1;
    for(var la=-60;la<=60;la+=30) linea(function(i){ return [-180+i*6,la]; },61);
    for(var lo=-180;lo<180;lo+=30) linea(function(i){ return [lo,-90+i*6]; },31);
    // paesi in filo d'oro
    if(MONDO){ x.lineWidth=0.9; x.strokeStyle='rgba(217,180,90,.62)'; x.fillStyle='rgba(217,180,90,.07)';
      for(var c=0;c<MONDO.length;c++){ var rings=MONDO[c].r; for(var k=0;k<rings.length;k++){ var rg=rings[k]; x.beginPath(); var apri=true; for(var i=0;i<rg.length;i++){ var q=proj(rg[i][0],rg[i][1]); if(!q){apri=true;continue;} if(apri){x.moveTo(q[0],q[1]);apri=false;} else x.lineTo(q[0],q[1]); }
        if(vista==='2d'){ x.closePath(); x.fill(); } x.stroke(); } } }
    // punti
    var pulsa=0.5+0.5*Math.sin(t*2.2);
    for(var j=0;j<P.length;j++){ var p=P[j]; if(!visibile(p)) continue; var q=proj(p.lo,p.la); if(!q) {p._sx=null;continue;} p._sx=q[0]; p._sy=q[1]; var rr=raggio(p)*(vista==='2d'?Math.min(1.6,Math.max(1,s2*0.8)):1); var col=colore(p);
      x.beginPath(); x.arc(q[0],q[1],rr+2+pulsa*1.6,0,Math.PI*2); x.fillStyle=conA(col,.13); x.fill();
      x.beginPath(); x.arc(q[0],q[1],rr,0,Math.PI*2); x.fillStyle=col; x.shadowColor=col; x.shadowBlur=4; x.fill(); x.shadowBlur=0;
      if(sel===p){ x.beginPath(); x.arc(q[0],q[1],rr+6+pulsa*3,0,Math.PI*2); x.strokeStyle='#fff4cf'; x.lineWidth=1.5; x.stroke(); } }
    // etichette dei World Node quando si guarda i nodi (poche, leggibili)
    if(strato==='node'||vista==='3d'){ x.font='600 9px Inter,sans-serif'; x.fillStyle='rgba(242,233,216,.8)'; x.textAlign='left'; for(var j2=0;j2<P.length;j2++){ var p2_=P[j2]; if(p2_.l!=='node'||p2_.t!=='World Node'||p2_._sx==null) continue; x.fillText(p2_.c.split(' · ')[0],p2_._sx+7,p2_._sy+3); } }
  }
  function proj(lo,la){ if(vista==='2d') return p2(lo,la); var q=p3(lo,la); return q[2]>0.02?q:null; }
  function linea(f,n){ x.beginPath(); var apri=true; for(var i=0;i<n;i++){ var g=f(i), q=proj(g[0],g[1]); if(!q){apri=true;continue;} if(apri){x.moveTo(q[0],q[1]);apri=false;} else x.lineTo(q[0],q[1]); } x.stroke(); }
  /* interazione: drag = sposta (2D) o ruota (3D); rotella/pinch = zoom 2D; tap = scheda */
  var giu=null, mosso=false, pt={};
  cv.addEventListener('pointerdown',function(e){ pt[e.pointerId]=[e.clientX,e.clientY]; giu=[e.clientX,e.clientY,ox,oy,lam,phi]; mosso=false; auto=false; cv.style.cursor='grabbing'; });
  cv.addEventListener('pointermove',function(e){ if(!giu||!(e.pointerId in pt)) return; pt[e.pointerId]=[e.clientX,e.clientY]; var ids=Object.keys(pt);
    if(ids.length>=2){ var a=pt[ids[0]],b=pt[ids[1]]; var d=Math.hypot(a[0]-b[0],a[1]-b[1]); if(giu.d){ if(vista==='2d') s2=Math.max(1,Math.min(8,s2*d/giu.d)); else s3=Math.max(0.6,Math.min(6,s3*d/giu.d)); } giu.d=d; mosso=true; disegna(); return; }
    var dx=e.clientX-giu[0], dy=e.clientY-giu[1]; if(Math.abs(dx)+Math.abs(dy)>4) mosso=true;
    if(vista==='2d'){ ox=giu[2]+dx; oy=giu[3]+dy; } else { lam=giu[4]+dx*0.5/s3; phi=Math.max(-80,Math.min(80,giu[5]+dy*0.4/s3)); } disegna(); });
  function su(e){ delete pt[e.pointerId]; cv.style.cursor='grab'; if(!giu) return; if(!mosso) tocca(e.clientX,e.clientY); giu=null; if(!RID&&vista==='3d') setTimeout(function(){ if(!giu) auto=true; },2500); }
  cv.addEventListener('pointerup',su); cv.addEventListener('pointercancel',function(e){ delete pt[e.pointerId]; giu=null; });
  function zooma(k,mx,my){ if(vista==='3d'){ s3=Math.max(0.6,Math.min(6,s3*k)); disegna(); return; } if(mx==null){mx=0;my=0;} var n=Math.max(1,Math.min(8,s2*k)); k=n/s2; ox=mx-(mx-ox)*k; oy=my-(my-oy)*k; s2=n; disegna(); }
  cv.addEventListener('wheel',function(e){ e.preventDefault(); var r=cv.getBoundingClientRect(); zooma(e.deltaY<0?1.15:1/1.15, e.clientX-r.left-W/2, e.clientY-r.top-H/2); },{passive:false});
  cv.addEventListener('dblclick',function(e){ var r=cv.getBoundingClientRect(); zooma(1.6, e.clientX-r.left-W/2, e.clientY-r.top-H/2); });
  document.getElementById('mappaPiu').addEventListener('click',function(){ zooma(1.3); }); document.getElementById('mappaMeno').addEventListener('click',function(){ zooma(1/1.3); });
  document.getElementById('mappaReset').addEventListener('click',function(){ s2=1;ox=0;oy=0;s3=1;lam=15;phi=18; disegna(); });
  function tocca(cx,cy){ var r=cv.getBoundingClientRect(), mx=cx-r.left, my=cy-r.top, best=null, bd=18*18; for(var j=0;j<P.length;j++){ var p=P[j]; if(p._sx==null) continue; var d=(p._sx-mx)*(p._sx-mx)+(p._sy-my)*(p._sy-my); if(d<bd){bd=d;best=p;} } sel=best; scheda(best); disegna(); }
  function esc(s){ return String(s).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
  function scheda(p){ if(!p){ sch.style.display='none'; return; } var tipo=p.l==='forest'?'Forest':(p.l==='node'?p.t:'Elderly association'); var col=colore(p);
    sch.innerHTML='<div style="display:flex;gap:10px;align-items:flex-start"><span style="width:12px;height:12px;border-radius:50%;background:'+col+';box-shadow:0 0 10px '+col+';flex:none;margin-top:3px"></span><div style="flex:1;min-width:0"><div class="eti">'+esc(tipo)+'</div><div class="medio" style="font-size:15px;margin:2px 0">'+esc(p.n)+'</div><div class="sub">'+esc(p.c)+' · '+esc(p.d)+(p.w?' · <a href="https://'+esc(p.w)+'" target="_blank" rel="noopener" style="color:var(--oro-chiaro)">'+esc(p.w)+' ↗</a>':'')+'</div>'+(p.l==='assoc'?'<div class="sub" style="margin-top:4px">Candidate for the 10% elderly fund — agreements to be confirmed and voted by the DAO before any payment.</div>':p.l==='forest'?'<div class="sub" style="margin-top:4px">BLOCKCHAINPLUS.DAO forest with Treedom — 5% of the economy, every tree geolocated.</div>':'')+'</div><button type="button" class="b" style="padding:4px 8px" onclick="this.closest(\'#mappaScheda\').style.display=\'none\'">✕</button></div>';
    sch.style.display=''; }
  document.querySelectorAll('#mappaStrati button').forEach(function(b){ b.addEventListener('click',function(){ document.querySelectorAll('#mappaStrati button').forEach(function(z){z.classList.remove('on');}); b.classList.add('on'); strato=b.dataset.l; sel=null; sch.style.display='none';
    var n=P.filter(visibile).length; leg.textContent=(strato==='all'?'Global view — ':strato==='assoc'?'Elderly associations — ':strato==='forest'?'BLOCKCHAINPLUS.DAO forests — ':'The 118 nodes — ')+n+' points · tap a point'; disegna(); }); });
  document.querySelectorAll('#mappaVista button').forEach(function(b){ b.addEventListener('click',function(){ document.querySelectorAll('#mappaVista button').forEach(function(z){z.classList.remove('on');}); b.classList.add('on'); vista=b.dataset.v; s2=1;ox=0;oy=0; auto=!RID&&vista==='3d'; document.getElementById('mappaHint').textContent=vista==='3d'?'drag to spin · pinch, wheel or + − to zoom':'drag to move · pinch, wheel or + − to zoom'; disegna(); }); });
  (function anima(){ requestAnimationFrame(anima); if(vista==='3d'&&auto){ lam+=0.12; disegna(); } else if(!RID&&sel){ disegna(); } })();
  addEventListener('resize',misura,{passive:true}); misura();
  if(!RID){ var lento=setInterval(function(){ if(vista==='2d'&&!sel) disegna(); },90); }   // pulsazione dei punti in 2D
})();
</script>
<?php return (string)ob_get_clean();
}
