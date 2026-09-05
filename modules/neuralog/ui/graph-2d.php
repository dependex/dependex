<?php
/* ============================================================================
   COMPANY BRAIN — ui/graph-2d.php
   Il grafo in due dimensioni: nessun WebGL, nessuna libreria, solo canvas.
   Serve a due cose che il 3D non fa bene: leggere le etichette e capire chi
   sta vicino a chi. Layout a molle semplificato, fermo quando si e' assestato.
============================================================================ */
require_once __DIR__ . '/_ui.php';

$admin = brain_is_admin();
if (!$admin && !brain_public_api_enabled()) { http_response_code(403); exit('Accesso riservato.'); }
brain_ui_headers();
$api = '../api/v1/graph.php' . brain_key_qs();
$label = (string)brain_cfg('ui.brand_label', 'Company Brain');
?><!doctype html>
<html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= brain_e($label) ?> — grafo 2D</title>
<style>
<?= brain_base_css() ?>
html,body{height:100%;overflow:hidden}
#cv{display:block;width:100vw;height:100vh;cursor:grab}
#cv:active{cursor:grabbing}
#bar{position:fixed;top:0;left:0;right:0;z-index:5;padding:10px 14px;display:flex;gap:10px;align-items:center;
  flex-wrap:wrap;background:linear-gradient(var(--brain-bg),transparent)}
#q{max-width:280px}
#info{position:fixed;left:14px;bottom:12px;z-index:5;font-size:12px;color:var(--brain-muted)}
</style></head><body>
<canvas id="cv"></canvas>
<div id="bar">
  <strong><?= brain_e($label) ?></strong>
  <span class="pill">nodi <b id="sN">—</b></span>
  <span class="pill">archi <b id="sL">—</b></span>
  <input id="q" placeholder="evidenzia (etichetta o percorso)">
  <button id="bRe">ricalcola</button>
  <button id="bFit">reinquadra</button>
  <a class="btn" href="brain-3d.php<?= brain_e(brain_key_qs()) ?>">vista 3D</a>
</div>
<div id="info">trascina = sposta la vista · rotella = zoom · clic su un nodo = dettagli</div>
<script>
"use strict";
const API=<?= json_encode($api) ?>;
const APIF=API+(API.includes('?')?'&':'?')+'full=1';
const cv=document.getElementById('cv'), ctx=cv.getContext('2d'), $=id=>document.getElementById(id);
let W,H,DPR, N=[], L=[], COL={}, ox=0, oy=0, zoom=1, drag=null, hot='', sel=null, alpha=1, running=true;
/* reinquadratura automatica quando il layout si e' assestato: senza,
   con qualche migliaio di archi il grafo finisce fuori schermo. */
function fit(){
  if(!N.length) return;
  let x0=Infinity,y0=Infinity,x1=-Infinity,y1=-Infinity;
  for(const n of N){ if(n.x<x0)x0=n.x; if(n.y<y0)y0=n.y; if(n.x>x1)x1=n.x; if(n.y>y1)y1=n.y; }
  const w=Math.max(1,x1-x0), h=Math.max(1,y1-y0);
  zoom=Math.max(0.15,Math.min(2.4, Math.min((W-120)/w,(H-140)/h)));
  ox=-((x0+x1)/2)*zoom; oy=-((y0+y1)/2)*zoom;
}
function resize(){ DPR=Math.min(devicePixelRatio||1,2); W=innerWidth; H=innerHeight;
  cv.width=W*DPR; cv.height=H*DPR; cv.style.width=W+'px'; cv.style.height=H+'px'; ctx.setTransform(DPR,0,0,DPR,0,0); }
resize(); addEventListener('resize',resize);
const colorOf=s=>{ const c=COL[s]||COL.default||[0.6,0.6,0.65];
  return 'rgb('+Math.round(c[0]*255)+','+Math.round(c[1]*255)+','+Math.round(c[2]*255)+')'; };

async function load(){
  const r=await fetch(APIF,{cache:'no-store'}); const d=await r.json();
  if(!d.ok) return;
  COL=d.colors||{};
  $('sN').textContent=d.stats.nodes; $('sL').textContent=d.stats.links;
  const idx={};
  N=(d.nodes||[]).slice(0,600).map((n,i)=>{ idx[n.id]=i;
    const a=(i/Math.max(1,Math.min(600,d.nodes.length)))*Math.PI*2, rr=90+((i*37)%220);
    return {...n, x:Math.cos(a)*rr, y:Math.sin(a)*rr, vx:0, vy:0, deg:0}; });
  L=(d.links||[]).filter(l=>idx[l.a]!==undefined&&idx[l.b]!==undefined).map(l=>({a:idx[l.a],b:idx[l.b]}));
  L.forEach(l=>{ N[l.a].deg++; N[l.b].deg++; });
  alpha=1; running=true; fit();
}
function step(){
  if(!running||!N.length) return;
  const K=42, R=2400;
  for(let i=0;i<N.length;i++){
    const a=N[i];
    for(let j=i+1;j<N.length;j++){
      const b=N[j]; let dx=a.x-b.x, dy=a.y-b.y, d2=dx*dx+dy*dy;
      if(d2<1){ d2=1; dx=(i-j)||1; dy=1; }
      if(d2>90000) continue;
      const f=R/d2, d=Math.sqrt(d2);
      const fx=f*dx/d, fy=f*dy/d;
      a.vx+=fx; a.vy+=fy; b.vx-=fx; b.vy-=fy;
    }
  }
  for(const l of L){
    const a=N[l.a], b=N[l.b]; const dx=b.x-a.x, dy=b.y-a.y;
    const d=Math.max(1,Math.hypot(dx,dy)); const f=(d-K)*0.006;
    const fx=f*dx/d, fy=f*dy/d; a.vx+=fx; a.vy+=fy; b.vx-=fx; b.vy-=fy;
  }
  for(const n of N){
    n.vx-=n.x*0.0020; n.vy-=n.y*0.0020;             /* richiamo al centro */
    n.x+=n.vx*alpha; n.y+=n.vy*alpha; n.vx*=0.82; n.vy*=0.82;
    /* recinto: oltre la repulsione si spegne (taglio a 300px) e i nodi isolati
       schizzerebbero via, costringendo la reinquadratura a zoomare su niente. */
    const rr=Math.hypot(n.x,n.y);
    if(rr>760){ n.x*=760/rr; n.y*=760/rr; n.vx*=0.25; n.vy*=0.25; }
  }
  alpha*=0.995; if(alpha<0.02){ running=false; fit(); }
}
function draw(){
  ctx.fillStyle=getComputedStyle(document.body).backgroundColor||'#0b0c0e'; ctx.fillRect(0,0,W,H);
  ctx.save(); ctx.translate(W/2+ox,H/2+oy); ctx.scale(zoom,zoom);
  ctx.strokeStyle='rgba(150,160,180,.10)'; ctx.lineWidth=1/zoom; ctx.beginPath();
  for(const l of L){ const a=N[l.a],b=N[l.b]; ctx.moveTo(a.x,a.y); ctx.lineTo(b.x,b.y); }
  ctx.stroke();
  for(const n of N){
    const r=Math.max(2.2,Math.min(9,2+Math.log2(1+n.deg)*1.4));
    const on = hot && ((n.l||'').toLowerCase().includes(hot) || (n.p||'').toLowerCase().includes(hot));
    ctx.beginPath(); ctx.arc(n.x,n.y,r,0,6.2832);
    ctx.fillStyle=colorOf(n.s); ctx.globalAlpha = hot ? (on?1:0.18) : 0.92; ctx.fill();
    if(on||sel===n){ ctx.globalAlpha=1; ctx.lineWidth=2/zoom; ctx.strokeStyle='#fff'; ctx.stroke(); }
    ctx.globalAlpha=1;
    if(zoom>0.9 && (n.deg>6 || on || sel===n)){
      ctx.fillStyle='rgba(230,230,235,.75)'; ctx.font=(11/zoom)+'px system-ui';
      ctx.fillText((n.l||'').slice(0,28), n.x+r+3, n.y+3);
    }
  }
  ctx.restore();
  if(sel){
    ctx.fillStyle='rgba(20,22,26,.94)'; ctx.strokeStyle='rgba(120,130,150,.4)';
    const w=300,h=76,x=14,y=H-h-42; ctx.beginPath(); ctx.roundRect(x,y,w,h,8); ctx.fill(); ctx.stroke();
    ctx.fillStyle='#e9e6df'; ctx.font='13px system-ui'; ctx.fillText((sel.l||'').slice(0,40), x+12, y+24);
    ctx.fillStyle='#8b8f98'; ctx.font='12px system-ui';
    ctx.fillText('tipo '+sel.s+' · sinapsi '+sel.deg, x+12, y+44);
    ctx.fillText((sel.p||'').slice(0,42), x+12, y+62);
  }
}
function toWorld(mx,my){ return {x:(mx-W/2-ox)/zoom, y:(my-H/2-oy)/zoom}; }
cv.addEventListener('pointerdown',e=>{ drag={x:e.clientX,y:e.clientY,ox,oy,moved:0}; });
addEventListener('pointerup',e=>{
  if(drag && drag.moved<5){
    const p=toWorld(e.clientX,e.clientY); let best=null,bd=225;
    for(const n of N){ const d=(n.x-p.x)**2+(n.y-p.y)**2; if(d<bd){bd=d;best=n;} }
    sel=best;
  }
  drag=null;
});
addEventListener('pointermove',e=>{ if(!drag) return;
  drag.moved+=Math.abs(e.clientX-drag.x)+Math.abs(e.clientY-drag.y);
  ox=drag.ox+(e.clientX-drag.x); oy=drag.oy+(e.clientY-drag.y); });
cv.addEventListener('wheel',e=>{ e.preventDefault(); zoom=Math.max(0.15,Math.min(4, zoom*(e.deltaY>0?0.9:1.11))); },{passive:false});
$('q').addEventListener('input',e=>{ hot=e.target.value.trim().toLowerCase(); });
$('bRe').onclick=()=>{ alpha=1; running=true; };
$('bFit').onclick=fit;
(function loop(){ requestAnimationFrame(loop); step(); draw(); })();
load();
</script>
</body></html>
