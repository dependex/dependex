<?php
/* ============================================================================
   COMPANY BRAIN — ui/brain-3d.php
   IL CERVELLO VIVO. Disegna i numeri veri: i neuroni (nodi) e le sinapsi
   (collegamenti) che ci sono davvero nel database, non una animazione finta.
   I neuroni pulsano, gli impulsi corrono lungo le sinapsi e si propagano.

   Tre livelli, in ordine di preferenza:
     1) Three.js locale  (ui/vendor/three.min.js)  — nessuna rete richiesta
     2) Three.js da CDN  (configurabile, con timeout)
     3) RIPIEGO INTEGRATO: proiezione 3D disegnata su canvas 2D, senza WebGL e
        senza librerie. Funziona ovunque, anche dove WebGL e' spento.
   Nessun colore di marca nel codice: tutto da config (ui.theme, ui.node_colors).
   Senza chiave admin si vede solo la parte pubblica del grafo.
============================================================================ */
require_once __DIR__ . '/_ui.php';

$admin = brain_is_admin();
if (!$admin && !brain_public_api_enabled()) { http_response_code(403); exit('Accesso riservato.'); }
brain_ui_headers();

$key   = brain_key_qs();
$api   = '../api/v1/graph.php' . $key;
$local = (string)brain_cfg('ui.three_local', 'ui/vendor/three.min.js');
$hasLocal = is_file(brain_path($local));
$cdn   = (string)brain_cfg('ui.three_cdn', '');
$label = (string)brain_cfg('ui.brand_label', 'Company Brain');
?><!doctype html>
<html lang="it"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= brain_e($label) ?> — cervello vivo</title>
<style>
<?= brain_base_css() ?>
html,body{height:100%;overflow:hidden}
#cv{display:block;width:100vw;height:100vh;touch-action:none;cursor:grab}
#cv:active{cursor:grabbing}
.hud{position:fixed;z-index:5;pointer-events:none}
#top{top:0;left:0;right:0;padding:12px 16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;
  background:linear-gradient(var(--brain-bg),transparent)}
#top .pill{pointer-events:auto}
#ctrl{top:12px;right:16px;display:flex;gap:8px;pointer-events:auto}
#zoom{position:fixed;right:16px;bottom:18px;z-index:6;display:flex;flex-direction:column;gap:8px}
#zoom button{width:40px;height:40px;font-size:17px}
#feed{bottom:26px;left:0;right:110px;max-height:92px;overflow:hidden;padding:10px 16px;font-size:12px;color:var(--brain-muted);
  background:linear-gradient(transparent,var(--brain-bg))}
#feed div{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#tip{position:fixed;z-index:7;background:var(--brain-panel);border:1px solid var(--brain-line);
  border-radius:8px;padding:6px 10px;font-size:12px;max-width:320px;display:none;pointer-events:none}
#sel{position:fixed;z-index:7;left:16px;top:62px;max-width:300px;display:none;pointer-events:auto;
  background:var(--brain-panel);border:1px solid var(--brain-line);border-radius:10px;padding:10px 12px;font-size:12px}
#empty,#msg{position:fixed;inset:0;display:none;place-items:center;text-align:center;padding:30px;
  color:var(--brain-muted);z-index:4}
#mode{position:fixed;left:16px;bottom:6px;z-index:5;font-size:11px;color:var(--brain-muted);pointer-events:none}
</style>
</head><body>
<canvas id="cv"></canvas>

<div class="hud" id="top">
  <strong><?= brain_e($label) ?></strong>
  <span class="pill"><span class="dot"></span> vivo</span>
  <span class="pill">neuroni <b id="sN">—</b></span>
  <span class="pill">sinapsi <b id="sL">—</b></span>
  <span class="pill">in scena <b id="sM">—</b></span>
  <?php if (!$admin): ?><span class="pill">vista pubblica</span><?php endif; ?>
</div>
<div class="hud" id="ctrl">
  <button id="bSpin">rotazione</button>
  <button id="bPause">pausa</button>
  <button id="bReload">aggiorna</button>
</div>
<div id="zoom">
  <button id="bIn" title="zoom +">+</button>
  <button id="bOut" title="zoom −">−</button>
  <button id="bFit" title="reinquadra">⌂</button>
</div>
<div class="hud" id="feed"></div>
<div id="tip"></div><div id="sel"></div>
<div id="empty">Il cervello e' vuoto.<br><span class="mut">Digerisci qualche documento (<code>php bin/brain ingest</code>) oppure semina i dati di prova (<code>php bin/brain demo-seed</code>).</span></div>
<div id="msg"></div>
<div id="mode">trascina = ruota · rotella = zoom · clic = accendi un neurone · <span id="renderer">—</span></div>

<script>
"use strict";
const API   = <?= json_encode($api) ?>;
const APIF  = API + (API.includes('?') ? '&' : '?') + 'full=1';
const APIS  = API + (API.includes('?') ? '&' : '?') + 'stat=1';
const THREE_SOURCES = <?= json_encode(array_values(array_filter([$hasLocal ? '../' . ltrim($local, '/') : null, $cdn ?: null]))) ?>;

const cv = document.getElementById('cv');
const fmt = n => (n==null ? '—' : (''+n).replace(/\B(?=(\d{3})+(?!\d))/g,'.'));
const $ = id => document.getElementById(id);
const esc = s => String(s==null?'':s).replace(/[<>&]/g, c => ({'<':'&lt;','>':'&gt;','&':'&amp;'}[c]));

/* ---------- geometria: forma di cervello, deterministica per id ---------- */
const HEMI = {w:96, h:112, l:168, gap:20, cy:6};
function hashId(s){ let h=2166136261>>>0; for(let i=0;i<s.length;i++){ h^=s.charCodeAt(i); h=Math.imul(h,16777619);} return h>>>0; }
function fold(x,y,z){                        /* corteccia corrugata: giri e solchi */
  return 1 + 0.060*Math.sin(9*x+2.1*y) + 0.052*Math.cos(10*y+3*z)
           + 0.050*Math.sin(11*z+1.6*x) + 0.030*Math.sin(17*x*z+4)
           + 0.028*Math.cos(19*y*x+1)  + 0.022*Math.sin(23*z*y);
}
function posFor(id, isHub){
  if (isHub) return {x:0, y:HEMI.cy+HEMI.h*0.58, z:HEMI.l*0.05};
  const h=hashId(id);
  const u=(h&2047)/2047, v=((h>>>11)&2047)/2047, w=((h>>>22)&511)/511;
  const th=u*Math.PI*2, ph=Math.acos(2*v-1);
  const dx=Math.sin(ph)*Math.cos(th), dy=Math.cos(ph), dz=Math.sin(ph)*Math.sin(th);
  const side=(h&1)?1:-1, f=fold(dx,dy,dz), inn=0.85+w*0.15;
  let Y=HEMI.cy + dy*HEMI.h*f*inn; if (Y<HEMI.cy) Y=HEMI.cy+(Y-HEMI.cy)*0.74;
  return { x: side*(HEMI.gap+Math.abs(dx)*HEMI.w*f)*inn, y: Y, z: dz*HEMI.l*f*inn };
}

/* ---------- stato ---------- */
let COLORS = <?= json_encode(brain_cfg('ui.node_colors', [])) ?>;
let nodes=[], links=[], pos=[], idx={}, adj=[], excite=null, sparks=[];
let rotX=0.18, rotY=0.62, dist=380, spin=true, paused=false, rev=-1, sel=-1;
let velX=0, velY=0, renderer=null, mode='—';
const KMIN=140, KMAX=3400;

function colorOf(sec){ const c=COLORS[sec]||COLORS.default||[0.6,0.6,0.65]; return c; }
function css(c,a){ return 'rgba('+Math.round(c[0]*255)+','+Math.round(c[1]*255)+','+Math.round(c[2]*255)+','+a+')'; }
function sizeOf(n){ return n.s==='hub' ? 9 : Math.max(1.1, Math.min(3.4, 1+Math.log10(Math.max(n.g||1,1))*0.95)); }

function build(data){
  nodes = data.nodes||[]; COLORS = data.colors||COLORS;
  idx={}; pos=[]; adj=new Array(nodes.length); excite=new Float32Array(nodes.length);
  nodes.forEach((n,i)=>{ idx[n.id]=i; pos.push(posFor(n.id, n.s==='hub')); });
  links = (data.links||[]).filter(l=> idx[l.a]!==undefined && idx[l.b]!==undefined)
                          .map(l=>({ai:idx[l.a], bi:idx[l.b]}));
  links.forEach((l,k)=>{ (adj[l.ai]=adj[l.ai]||[]).push(k); (adj[l.bi]=adj[l.bi]||[]).push(k); });
  const NP = Math.max(0, Math.min(240, Math.round(links.length*0.10)));
  sparks = [];
  for(let i=0;i<NP;i++){ sparks.push({li:(i*7919)%Math.max(1,links.length), t:(i*0.137)%1, sp:0.5+((i*37)%100)/100*1.1}); }
  $('empty').style.display = nodes.length ? 'none' : 'grid';
  $('sM').textContent = fmt(nodes.length)+' · '+fmt(links.length)+' sin';
  if (renderer && renderer.rebuild) renderer.rebuild();
}

function stepSparks(dt){
  if (!links.length) return;
  for(let i=0;i<excite.length;i++){ excite[i] = excite[i]>0.002 ? excite[i]*0.90 : 0; }
  if (sel>=0){ excite[sel]=1; (adj[sel]||[]).forEach(k=>{ const l=links[k]; excite[l.ai]=Math.max(excite[l.ai],0.7); excite[l.bi]=Math.max(excite[l.bi],0.7); }); }
  for(let i=0;i<sparks.length;i++){
    const s=sparks[i]; s.t += s.sp*dt;
    if (s.t>=1){
      const L=links[s.li];
      excite[L.bi]=1;                                  /* il neurone d'arrivo si accende */
      const out=adj[L.bi];
      s.li = (out&&out.length) ? out[(i+Math.floor(s.t*97))%out.length] : (s.li+1)%links.length;
      s.t=0;
    }
  }
}
function sparkPos(s){
  const L=links[s.li], A=pos[L.ai], B=pos[L.bi], t=s.t*s.t*(3-2*s.t);
  return {x:A.x+(B.x-A.x)*t, y:A.y+(B.y-A.y)*t, z:A.z+(B.z-A.z)*t};
}

/* ---------- dati ---------- */
async function poll(full){
  try{
    const r = await fetch(full?APIF:APIS, {cache:'no-store'});
    const d = await r.json();
    if (!d.ok){ $('msg').textContent='API non disponibile: '+(d.error||'errore'); $('msg').style.display='grid'; return; }
    if (full){ rev=d.rev; apply(d); return; }
    if (d.rev!==rev){ rev=d.rev; const r2=await fetch(APIF,{cache:'no-store'}); const d2=await r2.json(); if(d2.ok) apply(d2); }
    else if (d.stats){ $('sN').textContent=fmt(d.stats.nodes); $('sL').textContent=fmt(d.stats.links); }
  }catch(e){ $('msg').textContent='Nessuna risposta dal cervello.'; $('msg').style.display='grid'; }
}
function apply(d){
  $('sN').textContent=fmt(d.stats.nodes); $('sL').textContent=fmt(d.stats.links);
  build(d);
  const f=$('feed'); f.innerHTML='';
  (d.feed||[]).forEach(a=>{ const el=document.createElement('div');
    el.textContent='• '+(a.created_at||'')+'  '+(a.kind||'')+'  '+(a.detail||''); f.appendChild(el); });
}

/* ---------- interazione (comune ai due renderer) ---------- */
const ptrs=new Map(); let pinch0=0, dist0=0, moved=0;
function pdown(e){ ptrs.set(e.pointerId,{x:e.clientX,y:e.clientY}); spin=false; velX=velY=0;
  if(ptrs.size===1) moved=0;
  if(ptrs.size===2){ const a=[...ptrs.values()]; pinch0=Math.hypot(a[0].x-a[1].x,a[0].y-a[1].y); dist0=dist; } }
function pup(e){ const m=moved; ptrs.delete(e.pointerId); if(ptrs.size<2) pinch0=0;
  if(ptrs.size===0 && m<6) select(pick(e.clientX,e.clientY)); }
function pmove(e){ const p=ptrs.get(e.pointerId); if(!p) return;
  if(ptrs.size>=2){ const a=[...ptrs.values()]; const dd=Math.hypot(a[0].x-a[1].x,a[0].y-a[1].y);
    if(pinch0>0) dist=Math.max(KMIN,Math.min(KMAX,dist0*(pinch0/Math.max(dd,1)))); p.x=e.clientX;p.y=e.clientY; return; }
  const dx=e.clientX-p.x, dy=e.clientY-p.y; moved+=Math.abs(dx)+Math.abs(dy);
  velY=dx*0.005; velX=dy*0.005; rotY+=velY; rotX=Math.max(-1.45,Math.min(1.45,rotX+velX));
  p.x=e.clientX; p.y=e.clientY; hover(e.clientX,e.clientY); }
cv.addEventListener('pointerdown',pdown); cv.addEventListener('pointermove',pmove);
addEventListener('pointerup',pup); addEventListener('pointercancel',pup);
cv.addEventListener('wheel',e=>{ e.preventDefault(); dist=Math.max(KMIN,Math.min(KMAX,dist+e.deltaY*0.8)); },{passive:false});
cv.addEventListener('mousemove',e=>{ if(!ptrs.size) hover(e.clientX,e.clientY); });
addEventListener('keydown',e=>{
  const k=e.key;
  if(k==='ArrowLeft'){rotY-=0.08;spin=false;} else if(k==='ArrowRight'){rotY+=0.08;spin=false;}
  else if(k==='ArrowUp'){rotX=Math.max(-1.45,rotX-0.06);spin=false;} else if(k==='ArrowDown'){rotX=Math.min(1.45,rotX+0.06);spin=false;}
  else if(k==='+'||k==='='){dist=Math.max(KMIN,dist*0.9);} else if(k==='-'||k==='_'){dist=Math.min(KMAX,dist*1.1);}
  else if(k===' '){paused=!paused; $('bPause').textContent=paused?'riprendi':'pausa'; e.preventDefault();}
  else if(k==='r'||k==='R'){ reset(); }
});
function reset(){ rotX=0.18; rotY=0.62; dist=380; spin=true; select(-1); }
$('bSpin').onclick=()=>{ spin=!spin; };
$('bPause').onclick=()=>{ paused=!paused; $('bPause').textContent=paused?'riprendi':'pausa'; };
$('bReload').onclick=()=> poll(true);
$('bIn').onclick=()=>{ spin=false; dist=Math.max(KMIN,dist*0.82); };
$('bOut').onclick=()=>{ spin=false; dist=Math.min(KMAX,dist*1.22); };
$('bFit').onclick=reset;

function select(i){
  sel=i; const el=$('sel');
  if(i<0 || !nodes[i]){ el.style.display='none'; return; }
  const n=nodes[i], deg=(adj[i]||[]).length;
  el.style.display='block';
  el.innerHTML='<div style="font-weight:700;margin-bottom:4px">'+esc(n.l)+'</div>'
    +'<div class="mut">tipo <b>'+esc(n.s)+'</b> · sinapsi <b>'+deg+'</b></div>'
    +(n.p?'<div class="mut" style="margin-top:3px">'+esc(n.p)+'</div>':'')
    +'<div style="margin-top:8px"><button id="selX">chiudi</button></div>';
  const b=$('selX'); if(b) b.onclick=()=>select(-1);
}
function hover(x,y){
  const i=pick(x,y), t=$('tip');
  if(i>=0 && nodes[i]){ t.style.display='block'; t.style.left=(x+12)+'px'; t.style.top=(y+12)+'px';
    t.innerHTML='<b>'+esc(nodes[i].l)+'</b><br><span class="mut">'+esc(nodes[i].s)+(nodes[i].p?' · '+esc(nodes[i].p):'')+'</span>'; }
  else t.style.display='none';
}

/* ---------- proiezione condivisa ---------- */
let camX=0,camY=0,camZ=0;
function updateCam(){
  camX = dist*Math.cos(rotX)*Math.sin(rotY);
  camY = dist*Math.sin(rotX);
  camZ = dist*Math.cos(rotX)*Math.cos(rotY);
}

/* ======================= RENDERER 2: canvas 2D (sempre disponibile) ======= */
function makeCanvasRenderer(){
  const ctx = cv.getContext('2d');
  let W=0,H=0,DPR=1, proj=[], order=[];
  function resize(){ DPR=Math.min(devicePixelRatio||1,2); W=innerWidth; H=innerHeight;
    cv.width=W*DPR; cv.height=H*DPR; cv.style.width=W+'px'; cv.style.height=H+'px'; ctx.setTransform(DPR,0,0,DPR,0,0); }
  resize(); addEventListener('resize',resize);

  /* rotazione del mondo attorno all'origine, poi prospettiva */
  function project(p){
    const cy=Math.cos(rotY), sy=Math.sin(rotY), cx=Math.cos(rotX), sx=Math.sin(rotX);
    let x = p.x*cy - p.z*sy;
    let z = p.x*sy + p.z*cy;
    let y = (p.y-HEMI.cy)*cx - z*sx;
    z     = (p.y-HEMI.cy)*sx + z*cx;
    const d = dist - z;
    if (d <= 1) return null;
    const f = (H*0.9)/d;
    return {x:W/2 + x*f, y:H/2 - y*f, z:d, f:f};
  }
  function rebuild(){ proj=new Array(nodes.length); order=nodes.map((_,i)=>i); }
  /* guscio: due emisferi corrugati disegnati a paralleli e meridiani.
     Costa poco (poche centinaia di punti) e da' la forma al cervello anche
     dove WebGL non c'e'. */
  function shellPoint(side,u,v){
    const th=u*Math.PI*2, ph=v*Math.PI;
    const dx=Math.sin(ph)*Math.cos(th), dy=Math.cos(ph), dz=Math.sin(ph)*Math.sin(th);
    const f=fold(dx,dy,dz);
    return {x: side*(HEMI.gap+Math.abs(dx)*HEMI.w*f), y: HEMI.cy+dy*HEMI.h*f, z: dz*HEMI.l*f};
  }
  function drawShell(){
    ctx.strokeStyle='rgba(150,165,190,0.075)'; ctx.lineWidth=1;
    for(const side of [-1,1]){
      for(let i=1;i<9;i++){                       /* paralleli */
        ctx.beginPath(); let started=false;
        for(let j=0;j<=44;j++){
          const P=project(shellPoint(side,j/44,i/9)); if(!P){started=false;continue;}
          if(!started){ ctx.moveTo(P.x,P.y); started=true; } else ctx.lineTo(P.x,P.y);
        }
        ctx.stroke();
      }
      for(let i=0;i<10;i++){                      /* meridiani */
        ctx.beginPath(); let started=false;
        for(let j=0;j<=30;j++){
          const P=project(shellPoint(side,i/10,j/30)); if(!P){started=false;continue;}
          if(!started){ ctx.moveTo(P.x,P.y); started=true; } else ctx.lineTo(P.x,P.y);
        }
        ctx.stroke();
      }
    }
  }
  function draw(){
    ctx.fillStyle = getComputedStyle(document.body).backgroundColor || '#0b0c0e';
    ctx.fillRect(0,0,W,H);
    if (!nodes.length) return;
    drawShell();
    for(let i=0;i<nodes.length;i++) proj[i]=project(pos[i]);
    /* sinapsi: tante, quindi tenui e in numero limitato */
    const maxL = Math.min(links.length, 2200);
    ctx.lineWidth=1;
    ctx.strokeStyle='rgba(150,160,180,0.055)';
    ctx.beginPath();
    for(let k=0;k<maxL;k++){
      const A=proj[links[k].ai], B=proj[links[k].bi];
      if(!A||!B) continue;
      ctx.moveTo(A.x,A.y); ctx.lineTo(B.x,B.y);
    }
    ctx.stroke();
    /* neuroni, dal fondo verso la telecamera */
    order.sort((a,b)=>{ const A=proj[a],B=proj[b]; return (B?B.z:0)-(A?A.z:0); });
    for(const i of order){
      const P=proj[i]; if(!P) continue;
      const n=nodes[i], c=colorOf(n.s), ex=excite[i]||0;
      const t = performance.now()*0.001;
      const pulse = 0.65 + 0.35*Math.sin(t*(1.2+(i%7)*0.23) + (i%13));
      const r = Math.max(0.7, sizeOf(n)*P.f*0.55*(0.8+0.4*pulse)*(1+1.8*ex));
      const depth = Math.max(0.25, Math.min(1, 520/P.z));
      const col = ex>0.02 ? [Math.min(1,c[0]+ex*0.6), Math.min(1,c[1]+ex*0.6), Math.min(1,c[2]+ex*0.6)] : c;
      ctx.beginPath(); ctx.arc(P.x,P.y,r,0,6.2832);
      ctx.fillStyle = css(col, (0.55+0.45*pulse)*depth);
      ctx.fill();
      if (ex>0.3){ ctx.beginPath(); ctx.arc(P.x,P.y,r*2.6,0,6.2832);
        ctx.fillStyle=css(col, 0.10*ex); ctx.fill(); }
    }
    /* impulsi */
    for(const s of sparks){
      const P=project(sparkPos(s)); if(!P) continue;
      ctx.beginPath(); ctx.arc(P.x,P.y,Math.max(1,2.2*P.f*0.7),0,6.2832);
      ctx.fillStyle='rgba(255,255,255,0.75)'; ctx.fill();
    }
  }
  function pickAt(x,y){
    let best=-1, bd=14*14;
    for(let i=0;i<nodes.length;i++){ const P=proj[i]; if(!P) continue;
      const dx=P.x-x, dy=P.y-y, d=dx*dx+dy*dy; if(d<bd){ bd=d; best=i; } }
    return best;
  }
  rebuild();
  return {rebuild, draw, pickAt, name:'canvas 2D (ripiego senza WebGL)'};
}

/* ======================= RENDERER 1: Three.js ============================ */
function makeThreeRenderer(){
  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(0x000000, 0.0012);
  const cam = new THREE.PerspectiveCamera(56, innerWidth/innerHeight, 1, 9000);
  const gl  = new THREE.WebGLRenderer({canvas:cv, antialias:true, alpha:true});
  gl.setPixelRatio(Math.min(devicePixelRatio||1,2));
  gl.setClearColor(0x000000, 0);
  const group = new THREE.Group(); scene.add(group);

  /* guscio: due emisferi corrugati, colore neutro trasparente */
  function shell(side){
    const geo=new THREE.SphereGeometry(1,48,34), p=geo.attributes.position, v=new THREE.Vector3();
    for(let i=0;i<p.count;i++){ v.set(p.getX(i),p.getY(i),p.getZ(i)).normalize();
      const r=fold(v.x,v.y,v.z); p.setXYZ(i,v.x*r,v.y*r,v.z*r); }
    const m=new THREE.Mesh(geo,new THREE.MeshBasicMaterial({color:0x8899aa,wireframe:true,transparent:true,opacity:0.10}));
    m.scale.set(HEMI.w,HEMI.h,HEMI.l); m.position.set(side*(HEMI.gap+HEMI.w*0.5),HEMI.cy,0); return m;
  }
  group.add(shell(-1)); group.add(shell(1));

  let pts=null, lines=null, spk=null, geoN=null, geoS=null;
  function resize(){ const w=innerWidth,h=innerHeight; gl.setSize(w,h,false); cam.aspect=w/h; cam.updateProjectionMatrix(); }
  resize(); addEventListener('resize',resize);

  function rebuild(){
    if(pts){ group.remove(pts); geoN.dispose(); pts=null; }
    if(lines){ group.remove(lines); lines.geometry.dispose(); lines=null; }
    if(spk){ group.remove(spk); geoS.dispose(); spk=null; }
    const N=nodes.length; if(!N) return;
    const pa=new Float32Array(N*3), ca=new Float32Array(N*3), sa=new Float32Array(N);
    nodes.forEach((n,i)=>{ const P=pos[i], c=colorOf(n.s);
      pa[i*3]=P.x; pa[i*3+1]=P.y; pa[i*3+2]=P.z;
      ca[i*3]=c[0]; ca[i*3+1]=c[1]; ca[i*3+2]=c[2];
      sa[i]=sizeOf(n); });
    geoN=new THREE.BufferGeometry();
    geoN.setAttribute('position',new THREE.BufferAttribute(pa,3));
    geoN.setAttribute('color',new THREE.BufferAttribute(ca,3));
    pts=new THREE.Points(geoN,new THREE.PointsMaterial({size:4.2,vertexColors:true,sizeAttenuation:true,transparent:true,opacity:0.95}));
    pts.frustumCulled=false; group.add(pts);

    if(links.length){
      const lp=new Float32Array(links.length*6);
      links.forEach((l,k)=>{ const A=pos[l.ai],B=pos[l.bi]; lp.set([A.x,A.y,A.z,B.x,B.y,B.z],k*6); });
      const g=new THREE.BufferGeometry(); g.setAttribute('position',new THREE.BufferAttribute(lp,3));
      lines=new THREE.LineSegments(g,new THREE.LineBasicMaterial({color:0x9aa6bb,transparent:true,opacity:0.06}));
      lines.frustumCulled=false; group.add(lines);
    }
    if(sparks.length){
      geoS=new THREE.BufferGeometry();
      geoS.setAttribute('position',new THREE.BufferAttribute(new Float32Array(sparks.length*3),3));
      spk=new THREE.Points(geoS,new THREE.PointsMaterial({size:6,color:0xffffff,transparent:true,opacity:0.9,
        blending:THREE.AdditiveBlending,depthWrite:false}));
      spk.frustumCulled=false; group.add(spk);
    }
  }
  function draw(){
    if(spk && sparks.length){ const a=geoS.attributes.position.array;
      sparks.forEach((s,i)=>{ const P=sparkPos(s); a[i*3]=P.x; a[i*3+1]=P.y; a[i*3+2]=P.z; });
      geoS.attributes.position.needsUpdate=true; }
    updateCam(); cam.position.set(camX,camY,camZ); cam.lookAt(0,HEMI.cy,0);
    gl.render(scene,cam);
  }
  const ray=new THREE.Raycaster(); ray.params.Points.threshold=6;
  const m2=new THREE.Vector2();
  function pickAt(x,y){
    if(!pts) return -1;
    m2.x=(x/innerWidth)*2-1; m2.y=-(y/innerHeight)*2+1;
    ray.setFromCamera(m2,cam);
    const hit=ray.intersectObject(pts,false);
    return hit.length ? hit[0].index : -1;
  }
  rebuild();
  return {rebuild, draw, pickAt, name:'Three.js (WebGL)'};
}

function pick(x,y){ return renderer && renderer.pickAt ? renderer.pickAt(x,y) : -1; }

/* ---------- avvio: si prova WebGL, altrimenti si ripiega ---------- */
function webglOk(){
  try{ const c=document.createElement('canvas');
    return !!(window.WebGLRenderingContext && (c.getContext('webgl')||c.getContext('experimental-webgl'))); }
  catch(e){ return false; }
}
function loadScript(src, ms){
  return new Promise((res,rej)=>{
    const s=document.createElement('script'); s.src=src; s.async=true;
    const t=setTimeout(()=>{ s.onload=s.onerror=null; rej(new Error('timeout')); }, ms||3500);
    s.onload=()=>{ clearTimeout(t); res(true); };
    s.onerror=()=>{ clearTimeout(t); rej(new Error('errore')); };
    document.head.appendChild(s);
  });
}
async function boot(){
  let ok=false;
  if (webglOk()){
    for (const src of THREE_SOURCES){
      try{ await loadScript(src); if(window.THREE){ ok=true; break; } }catch(e){}
    }
  }
  try { renderer = ok ? makeThreeRenderer() : makeCanvasRenderer(); }
  catch(e){ renderer = makeCanvasRenderer(); }
  $('renderer').textContent = renderer.name;
  await poll(true);
  let last=performance.now();
  (function loop(now){
    requestAnimationFrame(loop);
    const dt=Math.min(0.05,(now-last)/1000)||0.016; last=now;
    if(paused) return;
    if(!ptrs.size && !spin){ rotY+=velY; rotX=Math.max(-1.45,Math.min(1.45,rotX+velX));
      velX*=0.92; velY*=0.92; if(Math.abs(velX)<2e-4) velX=0; if(Math.abs(velY)<2e-4) velY=0; }
    if(spin) rotY += 0.0012;
    stepSparks(dt);
    updateCam();
    renderer.draw();
  })(performance.now());
  setInterval(()=>{ if(!paused) poll(false); }, 8000);
  document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) poll(false); });
}
boot();
</script>
</body></html>
