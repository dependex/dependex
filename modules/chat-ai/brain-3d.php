<?php
/* ============================================================================
   NEURAL CORTEX 3D — BLOCKCHAINPLUS.DAO · adattato da neuralog/cortex-live.php (il modello unico del cervello, 8 ago).
   Cervello umano in wireframe (corteccia corrugata + cervelletto), neuroni che pulsano, impulsi che si propagano.
   Palette nero / bianco / oro. Dati: brain-api.php (grafo dell'ecosistema della dapp: hub, token, tool, membri, ledger).
   Giocabile: trascina = ruota · rotella/pinch = zoom · click = accendi un neurone · doppio click = messa a fuoco · frecce/±/spazio/R.
   Standalone (iframe-embeddable con ?embed=1). Gate: sessione dapp.
============================================================================ */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (!function_exists('demo_io') || demo_io() === '' ) { http_response_code(403); exit('Sign in first.'); }
$EMBED = !empty($_GET['embed']);
$qkey = '';
header('Content-Type: text/html; charset=utf-8');
?><!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>BLOCKCHAINPLUS.DAO — Neural Cortex 3D</title>
<style>
  :root{--bg:#050505;--fg:#f5efe0;--mut:#9c8f6a;--acc:#f3d77a;--acc2:#D4AF37;--ok:#e8d9a0;--warn:#ffcc55}
  *{box-sizing:border-box} html,body{margin:0;height:100%;background:var(--bg);color:var(--fg);font:14px/1.4 system-ui,Segoe UI,Roboto,sans-serif;overflow:hidden}
  #wrap{position:fixed;inset:0}
  canvas{display:block;width:100%;height:100%;touch-action:none}
  .hud{position:absolute;z-index:5;pointer-events:none}
  #top{top:0;left:0;right:0;padding:12px 16px;display:flex;gap:14px;align-items:center;flex-wrap:wrap;background:linear-gradient(#050505cc,#05050500)}
  #top b{color:#fff} .pill{pointer-events:auto;background:rgba(20,17,8,.82);border:1px solid #4a3d16;border-radius:999px;padding:6px 12px;display:flex;gap:8px;align-items:center}
  .k{color:var(--mut)} .v{color:var(--acc);font-weight:700;font-variant-numeric:tabular-nums}
  .v2{color:var(--acc2);font-weight:700} .vok{color:var(--ok);font-weight:700}
  #dot{width:9px;height:9px;border-radius:50%;background:var(--ok);box-shadow:0 0 10px var(--ok);animation:beat 1.4s infinite}
  @keyframes beat{0%,100%{opacity:.35;transform:scale(.8)}50%{opacity:1;transform:scale(1.25)}}
  #feed{bottom:0;left:0;right:96px;max-height:120px;padding:10px 16px;background:linear-gradient(#05050500,#050505);font-size:12px;color:var(--mut)}
  #feed div{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  #tip{position:absolute;z-index:6;background:rgba(23,19,10,.95);border:1px solid #4a3d16;border-radius:8px;padding:6px 10px;font-size:12px;max-width:340px;display:none;pointer-events:none}
  #ctrl{top:12px;right:16px;display:flex;gap:8px}
  #zoombar{position:absolute;z-index:6;right:16px;bottom:18px;display:flex;flex-direction:column;gap:8px}
  button{pointer-events:auto;background:#1c1710;color:var(--fg);border:1px solid #4a3d16;border-radius:8px;padding:6px 10px;cursor:pointer;font-size:13px}
  #zoombar button{width:40px;height:40px;font-size:18px;border-radius:10px}
  button:hover{border-color:var(--acc)}
  #empty{position:absolute;inset:0;display:none;place-items:center;text-align:center;color:var(--mut);z-index:4}
  #warnthree{position:absolute;inset:0;display:none;place-items:center;text-align:center;color:var(--warn);z-index:7;padding:24px}
  #hint{position:absolute;z-index:5;left:16px;bottom:8px;color:#8a7c52;font-size:11px;pointer-events:none}
<?php if ($EMBED): ?>#feed,#hint{display:none!important}#top{padding:8px 10px;gap:8px;font-size:12px}#top .pill{padding:4px 9px}#ctrl{top:8px;right:10px}#zoombar{bottom:10px;right:10px}#zoombar button{width:34px;height:34px}<?php endif; ?>
</style></head>
<body>
<div id="wrap"><canvas id="cv"></canvas></div>
<div class="hud" id="top">
  <b>Neural Cortex 3D</b>
  <span class="pill"><span id="dot"></span><span class="k">live 3D</span></span>
  <span class="pill"><span class="k">neurons</span><span class="v" id="sN">—</span></span>
  <span class="pill"><span class="k">synapses</span><span class="v2" id="sL">—</span></span>
  <span class="pill"><span class="k">objects learned</span><span class="vok" id="sF">—</span></span>
  <span class="pill"><span class="k">on stage</span><span class="v" id="sM">—</span></span>
</div>
<div class="hud" id="ctrl">
  <button id="bSpin">⟲ auto</button>
  <button id="bPause">⏸ pause</button>
  <button id="bRefresh">↻ refresh</button>
</div>
<div id="zoombar">
  <button id="bZin" title="Zoom +">+</button>
  <button id="bZout" title="Zoom −">−</button>
  <button id="bFit" title="Fit">⌂</button>
</div>
<div class="hud" id="feed"></div>
<div class="hud" id="hint">drag = rotate · wheel/pinch = zoom · click = fire a neuron · double-click = focus · arrows/±/space/R = keyboard</div>
<div id="tip"></div>
<div id="selpanel" style="position:absolute;z-index:6;left:16px;top:64px;max-width:300px;display:none;pointer-events:auto;background:rgba(23,19,10,.96);border:1px solid #6b571f;border-radius:10px;padding:10px 12px;font-size:12px"></div>
<div id="empty">The cortex is still empty.<br>As the ecosystem moves, neurons appear here in real time.</div>
<div id="warnthree">3D engine not loaded: the model cannot start on this device.</div>
<script src="../home/vendor/three.min.js"></script>
<script>
"use strict";
const API     = "brain-api.php";
const APIFULL = "brain-api.php?full=1";
const APISTAT = "brain-api.php?stat=1";
const cv = document.getElementById('cv');
const fmt = n => (n==null?'—':(''+n).replace(/\B(?=(\d{3})+(?!\d))/g,','));

/* PALETTE BRAND: nero, bianco, oro (+ varianti). Ogni tipo di neurone e' una
   variante di oro/bianco su fondo nero. */
const COL = {
  hub:      [1.00,0.96,0.78],   // bianco-oro (piu' luminoso)
  concetto: [0.83,0.69,0.22],   // oro brand #D4AF37
  progetto: [0.95,0.84,0.48],   // oro chiaro #f3d77a
  inbox:    [0.91,0.88,0.83],   // bianco caldo / crema
  def:      [0.62,0.50,0.20]    // oro profondo
};
const colOf = s => COL[s] || COL.def;

if(typeof THREE === 'undefined'){
  document.getElementById('warnthree').style.display='grid';
}else{
/* ===================== SCENA ===================== */
const scena = new THREE.Scene();
scena.fog = new THREE.FogExp2(0x050505, 0.0013);       // fondo NERO brand
const cam = new THREE.PerspectiveCamera(56, innerWidth/innerHeight, 1, 8000);
const rend = new THREE.WebGLRenderer({canvas: cv, antialias:true});
rend.setPixelRatio(Math.min(devicePixelRatio||1, 2));
rend.setClearColor(0x050505, 1);
let PR = rend.getPixelRatio();
function fitRenderer(){ const w=cv.clientWidth||innerWidth, h=cv.clientHeight||innerHeight;
  rend.setSize(w,h,false); cam.aspect=w/h; cam.updateProjectionMatrix(); }
scena.add(new THREE.AmbientLight(0xffffff, 0.7));

const HEMI = { ew:96, eh:112, el:168, gap:20, cy:6 };
const gruppo = new THREE.Group(); scena.add(gruppo);

/* corteccia corrugata (giri/solchi) */
function foldR(x,y,z){
  return 1
    + 0.060*Math.sin(9.0*x + 2.1*y) + 0.052*Math.cos(10.0*y + 3.0*z)
    + 0.050*Math.sin(11.0*z + 1.6*x) + 0.030*Math.sin(17.0*x*z + 4.0)
    + 0.028*Math.cos(19.0*y*x + 1.0) + 0.022*Math.sin(23.0*z*y);
}
function makeHemisphere(side){
  const geo = new THREE.SphereGeometry(1, 64, 44);
  const pos = geo.attributes.position; const v = new THREE.Vector3();
  for(let i=0;i<pos.count;i++){ v.set(pos.getX(i),pos.getY(i),pos.getZ(i)).normalize();
    const r = foldR(v.x, v.y, v.z); pos.setXYZ(i, v.x*r, v.y*r, v.z*r); }
  geo.computeVertexNormals();
  const m = new THREE.Mesh(geo, new THREE.MeshBasicMaterial({ color:0xC9A227, wireframe:true, transparent:true, opacity:0.13 }));
  m.scale.set(HEMI.ew, HEMI.eh, HEMI.el);
  m.position.set(side*(HEMI.gap + HEMI.ew*0.5), HEMI.cy, 0);
  return m;
}
[-1,1].forEach(side => gruppo.add(makeHemisphere(side)));
(function(){ /* cervelletto */
  const geo = new THREE.SphereGeometry(1, 40, 26); const pos=geo.attributes.position, v=new THREE.Vector3();
  for(let i=0;i<pos.count;i++){ v.set(pos.getX(i),pos.getY(i),pos.getZ(i)).normalize();
    const r=1+0.10*Math.sin(22*v.y)+0.06*Math.cos(20*v.x); pos.setXYZ(i,v.x*r,v.y*r,v.z*r); }
  const m=new THREE.Mesh(geo,new THREE.MeshBasicMaterial({color:0xB9962F,wireframe:true,transparent:true,opacity:0.12}));
  m.scale.set(78,42,58); m.position.set(0, HEMI.cy-78, -HEMI.el*0.72); gruppo.add(m);
})();
(function(){ /* tronco encefalico */
  const g=new THREE.CylinderGeometry(10,16,60,12,1,true);
  const m=new THREE.Mesh(g,new THREE.MeshBasicMaterial({color:0xB9962F,wireframe:true,transparent:true,opacity:0.10}));
  m.position.set(0, HEMI.cy-108, -HEMI.el*0.5); m.rotation.x=0.5; gruppo.add(m);
})();

function hashId(id){ let h=2166136261>>>0; for(let i=0;i<id.length;i++){ h^=id.charCodeAt(i); h=Math.imul(h,16777619); } return h>>>0; }
function brainPosFor(id, isHub){
  if(isHub) return new THREE.Vector3(0, HEMI.cy+HEMI.eh*0.55, HEMI.el*0.05);
  const h=hashId(id);
  const u=((h)&2047)/2047, vv=((h>>>11)&2047)/2047, w=((h>>>22)&511)/511;
  const theta=u*Math.PI*2, phi=Math.acos(2*vv-1);
  let dx=Math.sin(phi)*Math.cos(theta), dy=Math.cos(phi), dz=Math.sin(phi)*Math.sin(theta);
  const side=(h&1)?1:-1; const fold=foldR(dx,dy,dz); const inn=0.85 + w*0.15;  /* neuroni sulla corteccia (guscio) */
  let X=side*(HEMI.gap + Math.abs(dx)*HEMI.ew*fold)*inn;
  let Y=HEMI.cy + dy*HEMI.eh*fold*inn; if(Y<HEMI.cy) Y=HEMI.cy+(Y-HEMI.cy)*0.74;
  let Z=dz*HEMI.el*fold*inn;
  return new THREE.Vector3(X,Y,Z);
}

/* ===================== NEURONI (Points+shader: pulsano e si accendono) ===================== */
const neuroShader = {
  uniforms:{ uTime:{value:0}, uPR:{value:PR} },
  vertexShader:`
    attribute float aSize; attribute float aPhase; attribute float aFreq; attribute float aExcite; attribute vec3 aColor;
    uniform float uTime; uniform float uPR;
    varying vec3 vColor; varying float vPulse; varying float vEx; varying float vFar;
    void main(){
      vColor=aColor; vEx=aExcite;
      float pulse = 0.6 + 0.4*sin(uTime*aFreq + aPhase*6.2831);
      vPulse=pulse;
      vec4 mv = modelViewMatrix*vec4(position,1.0);
      vFar = clamp((-mv.z-300.0)/520.0, 0.0, 1.0);      /* i neuroni sul retro si scuriscono: niente blob */
      float sz = aSize*(0.8+0.4*pulse)*(1.0+2.2*aExcite);
      gl_PointSize = sz*uPR*(300.0/-mv.z);
      gl_Position = projectionMatrix*mv;
    }`,
  fragmentShader:`
    precision mediump float; varying vec3 vColor; varying float vPulse; varying float vEx; varying float vFar;
    void main(){
      vec2 uv=gl_PointCoord-0.5; float d=length(uv);
      float core=smoothstep(0.5,0.12,d);
      if(core<0.02) discard;
      vec3 base=vColor*(0.7+0.7*vPulse);
      vec3 c=mix(base, vec3(1.0), vEx*0.85);
      float dim=mix(1.0,0.35,vFar);
      float a=core*(0.85*dim + vEx*0.6);
      gl_FragColor=vec4(c*(0.9+0.5*vEx), min(a,1.0));
    }`
};
let neuroPts=null, neuroGeo=null, exciteArr=null;
const neuroMat = new THREE.ShaderMaterial({ uniforms:neuroShader.uniforms, vertexShader:neuroShader.vertexShader, fragmentShader:neuroShader.fragmentShader,
  transparent:true, depthTest:true, depthWrite:true, blending:THREE.NormalBlending });

/* ===================== SINAPSI ===================== */
let synLines=null;
/* ===================== IMPULSI ELETTRICI ===================== */
const sparkShader = {
  uniforms:{ uTime:{value:0}, uPR:{value:PR} },
  vertexShader:`
    attribute float aSize; attribute vec3 aColor; attribute float aSeed;
    uniform float uTime; uniform float uPR;
    varying vec3 vColor; varying float vF;
    void main(){
      vColor=aColor;
      float flick = 0.7 + 0.3*sin(uTime*34.0 + aSeed*8.0);
      vF=flick;
      vec4 mv = modelViewMatrix*vec4(position,1.0);
      gl_PointSize = aSize*flick*uPR*(390.0/-mv.z);
      gl_Position = projectionMatrix*mv;
    }`,
  fragmentShader:`
    precision mediump float; varying vec3 vColor; varying float vF;
    void main(){
      vec2 uv=gl_PointCoord-0.5; float d=length(uv);
      float core=smoothstep(0.5,0.0,d); float a=pow(core,1.8);
      if(a<0.02) discard;
      vec3 c = mix(vColor, vec3(1.0), 0.6)*(1.0+1.7*vF);
      gl_FragColor=vec4(c,a);
    }`
};
let sparkPts=null, sparkGeo=null, sparks=[], linkPairs=[], adj=[];
const sparkMat = new THREE.ShaderMaterial({ uniforms:sparkShader.uniforms, vertexShader:sparkShader.vertexShader, fragmentShader:sparkShader.fragmentShader,
  transparent:true, depthTest:false, depthWrite:false, blending:THREE.AdditiveBlending });

let neuroni=[], byId={};
const SEED = i => ((Math.sin(i*12.9898)*43758.5453)%1+1)%1;

function raggioBase(n){
  const g=(typeof n.g==='number')?n.g:1;
  if(n.s==='hub') return 9.0;
  return Math.max(1.0, Math.min(3.2, 1.0 + Math.log10(Math.max(g,1))*0.95));
}

function costruisci(data){
  const nodes=(data.nodes||[]); const N=nodes.length;
  selIdx=-1; selN=[]; { const sp=document.getElementById('selpanel'); if(sp) sp.style.display='none'; }
  neuroni=[]; byId={};
  const posA=new Float32Array(N*3), sizeA=new Float32Array(N), phA=new Float32Array(N), frA=new Float32Array(N), coA=new Float32Array(N*3);
  exciteArr=new Float32Array(N);
  const idxOf={}, posArr=[];
  nodes.forEach((n,i)=>{
    const p=brainPosFor(n.id, n.s==='hub');
    posA[i*3]=p.x; posA[i*3+1]=p.y; posA[i*3+2]=p.z;
    const c=colOf(n.s); coA[i*3]=c[0]; coA[i*3+1]=c[1]; coA[i*3+2]=c[2];
    sizeA[i]=raggioBase(n);
    const h=hashId(n.id); phA[i]=(h&1023)/1023; frA[i]=1.0+((h>>>10)&255)/255*2.4;
    idxOf[n.id]=i; posArr.push(p); neuroni.push({n,p}); byId[n.id]=p;
  });
  if(neuroPts){ gruppo.remove(neuroPts); neuroGeo.dispose(); }
  neuroGeo=new THREE.BufferGeometry();
  neuroGeo.setAttribute('position',new THREE.BufferAttribute(posA,3));
  neuroGeo.setAttribute('aSize',new THREE.BufferAttribute(sizeA,1));
  neuroGeo.setAttribute('aPhase',new THREE.BufferAttribute(phA,1));
  neuroGeo.setAttribute('aFreq',new THREE.BufferAttribute(frA,1));
  neuroGeo.setAttribute('aExcite',new THREE.BufferAttribute(exciteArr,1));
  neuroGeo.setAttribute('aColor',new THREE.BufferAttribute(coA,3));
  neuroPts=new THREE.Points(neuroGeo,neuroMat); neuroPts.frustumCulled=false; gruppo.add(neuroPts);

  /* sinapsi + adiacenza (per la propagazione degli impulsi) */
  const links=(data.links||[]).filter(l=>idxOf[l.a]!==undefined && idxOf[l.b]!==undefined);
  linkPairs=[]; adj=new Array(N);
  const lp=new Float32Array(links.length*6), lc=new Float32Array(links.length*6);
  links.forEach((l,k)=>{
    const ai=idxOf[l.a], bi=idxOf[l.b], A=posArr[ai], B=posArr[bi];
    const c=colOf(nodes[ai].s);
    lp.set([A.x,A.y,A.z,B.x,B.y,B.z],k*6);
    lc.set([c[0]*0.45,c[1]*0.45,c[2]*0.45, c[0]*0.45,c[1]*0.45,c[2]*0.45],k*6);
    linkPairs.push({a:A,b:B,ai,bi,c});
    (adj[ai]=adj[ai]||[]).push(k);
  });
  if(synLines){ gruppo.remove(synLines); synLines.geometry.dispose(); }
  if(links.length){
    const g=new THREE.BufferGeometry();
    g.setAttribute('position',new THREE.BufferAttribute(lp,3));
    g.setAttribute('color',new THREE.BufferAttribute(lc,3));
    synLines=new THREE.LineSegments(g,new THREE.LineBasicMaterial({vertexColors:true,transparent:true,opacity:0.05,blending:THREE.AdditiveBlending,depthWrite:false}));
    synLines.frustumCulled=false; gruppo.add(synLines);
  }

  /* impulsi: quantita' proporzionale, ma limitata per fluidita' */
  const NP = Math.max(0, Math.min(260, Math.round(linkPairs.length*0.12)));
  sparks=[];
  for(let i=0;i<NP;i++){ const li=linkPairs.length?(i*7919)%linkPairs.length:0;
    sparks.push({li, t:SEED(i*3+1), sp:0.5+SEED(i*7+2)*1.2, seed:SEED(i*5+3)}); }
  if(sparkPts){ gruppo.remove(sparkPts); sparkGeo.dispose(); }
  sparkGeo=new THREE.BufferGeometry();
  const sp=new Float32Array(NP*3), sc=new Float32Array(NP*3), ss=new Float32Array(NP), sd=new Float32Array(NP);
  for(let i=0;i<NP;i++){ ss[i]=5.0+SEED(i*11)*3.5; sd[i]=sparks[i].seed;
    const c=linkPairs.length?linkPairs[sparks[i].li].c:[0.4,0.9,1.0]; sc[i*3]=c[0];sc[i*3+1]=c[1];sc[i*3+2]=c[2]; }
  sparkGeo.setAttribute('position',new THREE.BufferAttribute(sp,3));
  sparkGeo.setAttribute('aColor',new THREE.BufferAttribute(sc,3));
  sparkGeo.setAttribute('aSize',new THREE.BufferAttribute(ss,1));
  sparkGeo.setAttribute('aSeed',new THREE.BufferAttribute(sd,1));
  sparkPts=new THREE.Points(sparkGeo,sparkMat); sparkPts.frustumCulled=false; gruppo.add(sparkPts);

  setStat('sM', fmt(N)+' · '+fmt(links.length)+' syn');
}

function stepSparks(dt){
  if(!sparkPts || !linkPairs.length){ return; }
  const arr=sparkGeo.attributes.position.array, col=sparkGeo.attributes.aColor.array;
  // decadimento del "firing" di tutti i neuroni
  if(exciteArr){ for(let i=0;i<exciteArr.length;i++){ if(exciteArr[i]>0.001) exciteArr[i]*=0.90; else exciteArr[i]=0; } }
  if(exciteArr && selIdx>=0){ exciteArr[selIdx]=1; for(let j=0;j<selN.length;j++){ if(exciteArr[selN[j]]<0.7) exciteArr[selN[j]]=0.7; } }
  for(let i=0;i<sparks.length;i++){
    const s=sparks[i]; s.t += s.sp*dt;
    if(s.t>=1){
      const L=linkPairs[s.li];
      if(exciteArr) exciteArr[L.bi]=1.0;                 // il neurone d'arrivo si ACCENDE
      // propagazione: continua da b lungo una sinapsi uscente, se c'e'
      const out=adj[L.bi];
      s.li = (out&&out.length) ? out[(Math.floor(SEED(i+s.t*97)*out.length))%out.length]
                               : (s.li+1+((i*2654435761)>>>0)%linkPairs.length)%linkPairs.length;
      s.t=0;
      const c=linkPairs[s.li].c; col[i*3]=c[0];col[i*3+1]=c[1];col[i*3+2]=c[2];
    }
    const L=linkPairs[s.li], A=L.a, B=L.b, tt=s.t*s.t*(3-2*s.t);
    arr[i*3]=A.x+(B.x-A.x)*tt; arr[i*3+1]=A.y+(B.y-A.y)*tt; arr[i*3+2]=A.z+(B.z-A.z)*tt;
  }
  sparkGeo.attributes.position.needsUpdate=true;
  sparkGeo.attributes.aColor.needsUpdate=true;
  if(neuroGeo && neuroGeo.attributes.aExcite){ neuroGeo.attributes.aExcite.needsUpdate=true; }
}

/* ===================== DATI LIVE (numeri veri) ===================== */
let rev=-1, paused=false;
function setStat(id,val){ const e=document.getElementById(id); if(e) e.textContent=val; }
function ingest(data){
  const s=data.stats||{};
  setStat('sN', fmt(s.neuroni));
  setStat('sL', fmt(s.sinapsi));
  setStat('sF', fmt(s.file));
  document.getElementById('empty').style.display=((s.neuroni||0)>0)?'none':'grid';
  costruisci(data);
  const f=document.getElementById('feed'); if(f){ f.innerHTML='';
    (data.feed||[]).forEach(a=>{ const d=document.createElement('div');
      d.textContent='• '+(a.creato||'')+'  '+(a.tipo||'')+'  '+(a.dettaglio||''); f.appendChild(d); }); }
}
async function poll(full){
  try{
    const r=await fetch(full?APIFULL:APISTAT,{cache:'no-store'});
    const d=await r.json(); if(!d.ok) return;
    if(full){ rev=d.rev; ingest(d); return; }
    if(d.rev!==rev){ rev=d.rev; const r2=await fetch(APIFULL,{cache:'no-store'}); const d2=await r2.json(); if(d2.ok) ingest(d2); }
    else if(d.stats){ setStat('sN',fmt(d.stats.neuroni)); setStat('sL',fmt(d.stats.sinapsi)); setStat('sF',fmt(d.stats.file)); }
  }catch(e){}
}

/* ===================== ORBITA: mouse + touch + rotella + pinch + inerzia + click ===================== */
let rotX=0.18, rotY=0.6, dist=560, giraDaSola=true;
let velX=0, velY=0;                          // inerzia dopo il trascinamento
const DEF={rotX:0.18,rotY:0.6,dist:560};
const KMIN=140, KMAX=3400;
let selIdx=-1, selN=[];                       // neurone selezionato + vicini (restano accesi)
const pts=new Map(); let pinchD0=0, pinchDist0=0, moved=0;
const ray=new THREE.Raycaster(); ray.params.Points.threshold=5;
const mouse=new THREE.Vector2(), tip=document.getElementById('tip'), selpanel=document.getElementById('selpanel');
function esc(s){ return String(s==null?'':s).replace(/[<>&]/g,c=>({'<':'&lt;','>':'&gt;','&':'&amp;'}[c])); }

function pickAt(cx,cy){
  if(!neuroPts) return -1;
  mouse.x=(cx/innerWidth)*2-1; mouse.y=-(cy/innerHeight)*2+1;
  ray.setFromCamera(mouse, cam);
  const hit=ray.intersectObject(neuroPts,false);
  return hit.length? hit[0].index : -1;
}
function burstFrom(idx){ const out=adj[idx]; if(!out||!out.length||!sparks.length) return;
  for(let i=0;i<sparks.length && i<80;i++){ sparks[i].li=out[i%out.length]; sparks[i].t=0; } }
function selectNeuron(idx){
  selIdx=idx;
  if(idx<0){ selN=[]; selpanel.style.display='none'; return; }
  const out=adj[idx]||[]; const nb=[];
  for(let k=0;k<out.length && nb.length<400;k++){ nb.push(linkPairs[out[k]].bi); }
  selN=nb;
  if(exciteArr){ exciteArr[idx]=1; nb.forEach(j=>exciteArr[j]=1); if(neuroGeo) neuroGeo.attributes.aExcite.needsUpdate=true; }
  burstFrom(idx);
  const o=neuroni[idx], n=o&&o.n;
  if(n){ selpanel.style.display='block';
    selpanel.innerHTML='<div style="color:#fff;font-weight:700;margin-bottom:4px">'+esc(n.l)+'</div>'
      +'<div style="color:#9c8f6a">type: <b style="color:#f3d77a">'+esc(n.s)+'</b> · connections: <b style="color:#f3d77a">'+out.length+'</b></div>'
      +(n.p?'<div style="color:#9c8f6a;margin-top:3px">'+esc(n.p)+'</div>':'')
      +'<div style="margin-top:8px"><button id="selClose" style="font-size:11px;padding:4px 8px">deselect ✕</button></div>';
    const b=document.getElementById('selClose'); if(b) b.onclick=()=>selectNeuron(-1);
  }
}

function pd(e){ pts.set(e.pointerId,{x:e.clientX,y:e.clientY}); giraDaSola=false; velX=0; velY=0;
  if(pts.size===1){ moved=0; }
  if(pts.size===2){ const a=[...pts.values()]; pinchD0=Math.hypot(a[0].x-a[1].x,a[0].y-a[1].y); pinchDist0=dist; } }
function pu(e){ const wasMoved=moved; pts.delete(e.pointerId); if(pts.size<2) pinchD0=0;
  if(pts.size===0 && wasMoved<6){ const idx=pickAt(e.clientX,e.clientY); selectNeuron(idx); } }
function pm(e){
  const p=pts.get(e.pointerId); if(!p) return;
  if(pts.size>=2){ const a=[...pts.values()]; const dd=Math.hypot(a[0].x-a[1].x,a[0].y-a[1].y);
    if(pinchD0>0) dist=Math.max(KMIN,Math.min(KMAX, pinchDist0*(pinchD0/Math.max(dd,1)))); p.x=e.clientX; p.y=e.clientY; return; }
  const dx=(e.clientX-p.x), dy=(e.clientY-p.y);
  moved += Math.abs(dx)+Math.abs(dy);
  velY=dx*0.005; velX=dy*0.005;
  rotY += velY; rotX=Math.max(-1.45,Math.min(1.45, rotX+velX));
  p.x=e.clientX; p.y=e.clientY;
}
cv.addEventListener('pointerdown', pd);
cv.addEventListener('pointermove', pm);
addEventListener('pointerup', pu); addEventListener('pointercancel', pu);
cv.addEventListener('wheel', e=>{ e.preventDefault(); dist=Math.max(KMIN,Math.min(KMAX, dist+e.deltaY*0.8)); }, {passive:false});
cv.addEventListener('dblclick', e=>{ const idx=pickAt(e.clientX,e.clientY); if(idx>=0){ selectNeuron(idx); } dist=Math.max(KMIN, dist*0.55); });
addEventListener('keydown', e=>{
  const k=e.key;
  if(k==='ArrowLeft'){ rotY-=0.08; giraDaSola=false; }
  else if(k==='ArrowRight'){ rotY+=0.08; giraDaSola=false; }
  else if(k==='ArrowUp'){ rotX=Math.max(-1.45,rotX-0.06); giraDaSola=false; }
  else if(k==='ArrowDown'){ rotX=Math.min(1.45,rotX+0.06); giraDaSola=false; }
  else if(k==='+'||k==='='){ dist=Math.max(KMIN,dist*0.9); }
  else if(k==='-'||k==='_'){ dist=Math.min(KMAX,dist*1.1); }
  else if(k===' '){ paused=!paused; const b=document.getElementById('bPause'); if(b) b.textContent=paused?'▶ riprendi':'⏸ pause'; e.preventDefault(); }
  else if(k==='r'||k==='R'){ rotX=DEF.rotX; rotY=DEF.rotY; dist=DEF.dist; giraDaSola=true; selectNeuron(-1); }
  else if(k==='a'||k==='A'){ giraDaSola=!giraDaSola; }
  else return;
});
document.getElementById('bZin').onclick  = ()=>{ giraDaSola=false; dist=Math.max(KMIN, dist*0.82); };
document.getElementById('bZout').onclick = ()=>{ giraDaSola=false; dist=Math.min(KMAX, dist*1.22); };
document.getElementById('bFit').onclick  = ()=>{ rotX=DEF.rotX; rotY=DEF.rotY; dist=DEF.dist; giraDaSola=true; selectNeuron(-1); };
cv.addEventListener('mousemove', e=>{
  if(pts.size || !neuroPts){ tip.style.display='none'; return; }
  mouse.x=(e.clientX/innerWidth)*2-1; mouse.y=-(e.clientY/innerHeight)*2+1;
  ray.setFromCamera(mouse, cam);
  const hit=ray.intersectObject(neuroPts,false);
  if(hit.length){ const o=neuroni[hit[0].index], n=o&&o.n;
    if(n){ tip.style.display='block'; tip.style.left=(e.clientX+12)+'px'; tip.style.top=(e.clientY+12)+'px';
      tip.innerHTML='<b>'+esc(n.l)+'</b><br><span style="color:#9c8f6a">'+esc(n.s)+(n.p?' · '+esc(n.p):'')+'</span>'; } }
  else tip.style.display='none';
});

addEventListener('resize', ()=>{ fitRenderer(); PR=rend.getPixelRatio(); neuroShader.uniforms.uPR.value=PR; sparkShader.uniforms.uPR.value=PR; });
document.getElementById('bSpin').onclick   = e=>{ giraDaSola=!giraDaSola; e.target.style.borderColor=giraDaSola?'#4de1ff':'#2b3a72'; };
document.getElementById('bPause').onclick  = e=>{ paused=!paused; e.target.textContent=paused?'▶ riprendi':'⏸ pause'; };
document.getElementById('bRefresh').onclick= ()=> poll(true);

let last=performance.now();
(function anima(now){
  requestAnimationFrame(anima);
  const dt=Math.min(0.05,(now-last)/1000)||0.016; last=now;
  if(paused) return;
  const tsec=now*0.001; neuroShader.uniforms.uTime.value=tsec; sparkShader.uniforms.uTime.value=tsec;
  stepSparks(dt);
  if(pts.size===0 && !giraDaSola){ rotY+=velY; rotX=Math.max(-1.45,Math.min(1.45,rotX+velX)); velX*=0.92; velY*=0.92;
    if(Math.abs(velX)<0.0002) velX=0; if(Math.abs(velY)<0.0002) velY=0; }
  if(giraDaSola) rotY += 0.0010;
  cam.position.set(dist*Math.cos(rotX)*Math.sin(rotY), dist*Math.sin(rotX), dist*Math.cos(rotX)*Math.cos(rotY));
  cam.lookAt(0, HEMI.cy, 0);
  rend.render(scena, cam);
})(performance.now());

fitRenderer(); PR=rend.getPixelRatio(); neuroShader.uniforms.uPR.value=PR; sparkShader.uniforms.uPR.value=PR;
poll(true);
setInterval(()=>{ if(!paused) poll(false); }, 6000);
document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) poll(false); });
} /* fine blocco THREE presente */
</script>
</body></html>
