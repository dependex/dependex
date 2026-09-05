<?php
/** NETWORK — la rete vista con d3: 5 viste (albero/laterale/ventaglio/stella/anelli), pannello scheda,
 *  "cosa fare stasera", ricerca, isola/filtri/profondita', PNG, link. Porting di genesys/mia-rete.php.
 *  I dati arrivano SOLO da net-api.php (stesso contratto di dr-mia-rete-api.php). Qui non si scrive niente.
 *  Admin: interruttore MY BRANCH / WHOLE NETWORK (aggiunge &tutto=1 a ogni chiamata). */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
$IO = demo_io();
$TIT = 'Network';
$ADMIN = demo_admin_sessione();
require __DIR__ . '/_testa.php';
?>
<style>
  /* ---- NETWORK TOOL (d3) — stile DAO BRANCH: nero, oro, Inter ---- */
  #net{position:relative;padding-bottom:6px}
  #net .bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:10px 12px;margin-bottom:10px}
  #net .bar h1{font-family:Cinzel,serif;font-size:12px;letter-spacing:.18em;text-transform:uppercase;margin:0;
     background:linear-gradient(92deg,var(--oro-chiaro),var(--oro) 46%,var(--oro-scuro));
     -webkit-background-clip:text;background-clip:text;color:transparent;white-space:nowrap}
  #net .kpi{display:flex;gap:10px;font-size:10.5px;color:var(--tenue);flex-wrap:wrap;letter-spacing:.02em}
  #net .kpi b{color:var(--inchiostro);font-variant-numeric:tabular-nums}
  #net .right{margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;align-items:center}
  #net .nb{background:#0d0b08;color:var(--oro-chiaro);border:1px solid rgba(217,180,90,.42);border-radius:9px;
     padding:7px 10px;font:700 9.5px/1 Inter,sans-serif;letter-spacing:.08em;text-transform:uppercase;
     cursor:pointer;text-decoration:none;white-space:nowrap;transition:all .16s ease}
  #net .nb:hover{border-color:var(--oro);color:#fff4cf;background:rgba(217,180,90,.10)}
  #net .nb.on{background:linear-gradient(135deg,var(--oro),#b8933a);color:#120e07;border-color:transparent}
  #net input#q{background:#0d0b08;color:var(--inchiostro);border:1px solid rgba(217,180,90,.35);border-radius:9px;
     padding:7px 10px;font:400 12px Inter,sans-serif;width:170px;outline:none}
  #net input#q:focus{border-color:var(--oro)}
  #net input#q::placeholder{color:var(--tenue)}
  #net .segm{display:inline-flex;border:1px solid rgba(217,180,90,.42);border-radius:9px;overflow:hidden}
  #net .segm button{background:transparent;border:0;color:var(--tenue);font:700 9px Inter,sans-serif;letter-spacing:.08em;
     padding:7px 9px;cursor:pointer;text-transform:uppercase}
  #net .segm button.on{background:linear-gradient(135deg,var(--oro),#b8933a);color:#120e07}
  #net .link-classic{font-size:10px;color:var(--tenue);text-decoration:none;letter-spacing:.06em;white-space:nowrap}
  #net .link-classic:hover{color:var(--oro-chiaro)}

  #scena{position:relative;height:calc(100vh - 230px);min-height:420px;border-radius:14px;overflow:hidden;
     border:1px solid rgba(217,180,90,.42);background:#070605;
     box-shadow:inset 1px 1px 0 rgba(255,236,190,.16),inset -1px -1px 0 rgba(0,0,0,.6)}
  #scena svg{display:block;width:100%;height:100%;cursor:grab;touch-action:none;
     background:radial-gradient(115% 78% at 50% 106%, rgba(217,180,90,.17) 0%, rgba(217,180,90,.055) 34%, rgba(217,180,90,0) 66%),#070605}
  #scena svg.trascino{cursor:grabbing}
  .nodo{cursor:pointer}

  .tip{position:fixed;pointer-events:none;background:#0d0b08;border:1px solid rgba(217,180,90,.45);
       border-radius:10px;padding:7px 10px;font-size:12px;display:none;z-index:60;max-width:250px;line-height:1.45;
       box-shadow:0 8px 30px rgba(0,0,0,.7)}
  .tip b{color:var(--oro-chiaro)}

  /* pannelli fissi: sopra la barra in basso (100px) */
  #pan,#menu{position:fixed;top:72px;right:12px;bottom:100px;width:340px;max-width:88vw;z-index:40;
     background:#0d0b08;border:1px solid rgba(217,180,90,.35);border-radius:14px;
     box-shadow:-14px 0 44px rgba(0,0,0,.7);transform:translateX(110%);transition:transform .22s ease;
     overflow-y:auto;padding:16px 18px 24px;scrollbar-width:thin;scrollbar-color:rgba(217,180,90,.35) transparent}
  #pan.aperto,#menu.aperto{transform:none}
  #menu{width:320px;z-index:52}
  #pan h2,#menu h2{margin:0 0 4px;font-family:Cinzel,serif;font-size:14px;letter-spacing:.08em;color:var(--oro-chiaro);padding-right:30px}
  .chiudi-x{position:absolute;top:10px;right:12px;background:none;border:1px solid rgba(217,180,90,.3);color:var(--tenue);
     border-radius:8px;padding:3px 9px;font-size:14px;line-height:1;cursor:pointer}
  .chiudi-x:hover{color:var(--oro-chiaro);border-color:var(--oro)}
  .pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:10px;font-weight:700;letter-spacing:.04em;
        border:1px solid rgba(217,180,90,.3);margin:2px 4px 2px 0;color:var(--tenue)}
  .pill.vivo{background:rgba(217,180,90,.16);color:var(--oro-chiaro);border-color:rgba(217,180,90,.45)}
  .pill.io{background:rgba(242,233,216,.12);color:#fff4cf;border-color:rgba(242,233,216,.35)}
  .sez{margin-top:15px;border-top:1px solid rgba(217,180,90,.18);padding-top:11px}
  .sez h3{margin:0 0 8px;font-size:9px;letter-spacing:.24em;color:var(--tenue);text-transform:uppercase;font-weight:600}
  .rg{display:flex;justify-content:space-between;gap:10px;font-size:12.5px;padding:3px 0}
  .rg span:first-child{color:var(--tenue)}
  .rg span:last-child{font-weight:600;text-align:right;word-break:break-word}
  .griglia2{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:5px}
  .box{background:linear-gradient(180deg,rgba(217,180,90,.07),rgba(217,180,90,.02));
       border:1px solid rgba(217,180,90,.25);border-radius:9px;padding:9px;text-align:center}
  .box b{display:block;font-family:Cinzel,serif;font-size:18px;color:var(--oro);line-height:1.25}
  .box small{font-size:9.5px;color:var(--tenue);letter-spacing:.06em;text-transform:uppercase}
  .mono{font-family:Consolas,Menlo,monospace;font-size:11px;word-break:break-all}
  .nb2{background:#0d0b08;color:var(--oro-chiaro);border:1px solid rgba(217,180,90,.42);border-radius:9px;
     padding:8px 12px;font:700 9.5px/1 Inter,sans-serif;letter-spacing:.08em;text-transform:uppercase;cursor:pointer}
  .nb2:hover{border-color:var(--oro)}
  .nb2:disabled{opacity:.5}

  .briciole{position:absolute;left:10px;bottom:10px;z-index:5;font-size:11px;color:var(--tenue);
     background:rgba(13,11,8,.9);border:1px solid rgba(217,180,90,.3);
     border-radius:8px;padding:5px 9px;max-width:60%;display:flex;gap:5px;flex-wrap:wrap}
  .briciole a{color:var(--oro);cursor:pointer;text-decoration:none;font-weight:700}
  .aiuto{position:absolute;right:10px;bottom:10px;z-index:5;font-size:10px;color:var(--tenue);
     background:rgba(13,11,8,.9);border:1px solid rgba(217,180,90,.3);border-radius:8px;padding:5px 9px;max-width:46%}
  .risultati{position:absolute;top:100%;right:0;margin-top:6px;z-index:45;
     background:#0d0b08;border:1px solid rgba(217,180,90,.45);border-radius:10px;padding:6px;
     max-height:52vh;overflow-y:auto;min-width:260px;max-width:92vw;display:none;box-shadow:0 10px 38px rgba(0,0,0,.75)}
  .risultati div{padding:7px 10px;font-size:12px;cursor:pointer;border-radius:7px}
  .risultati div:hover{background:rgba(217,180,90,.12);color:var(--oro-chiaro)}
  .carico{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9;
     background:#0d0b08;border:1px solid rgba(217,180,90,.45);border-radius:10px;
     padding:10px 18px;font-size:12px;display:none;color:var(--oro-chiaro);letter-spacing:.06em;max-width:90%}
  .legenda{position:absolute;left:10px;bottom:44px;max-width:min(400px,80%);z-index:5;display:none;
     font-size:11px;line-height:1.5;color:var(--tenue);background:rgba(13,11,8,.9);
     border:1px solid rgba(217,180,90,.3);border-radius:9px;padding:8px 11px}
  .legenda b{color:var(--oro-chiaro)}
  .stato-vista{position:absolute;left:50%;transform:translateX(-50%);top:10px;z-index:6;display:none;
     font-size:11px;color:#0a0908;background:linear-gradient(180deg,#f2dba4,#d9b45a);
     border-radius:20px;padding:5px 12px;box-shadow:0 6px 22px rgba(0,0,0,.6);white-space:nowrap;max-width:94%;overflow:hidden;text-overflow:ellipsis}
  .stato-vista button{margin-left:9px;background:rgba(10,9,8,.18);border:1px solid rgba(10,9,8,.32);
     color:#0a0908;border-radius:12px;padding:2px 9px;font:700 9px Inter,sans-serif;letter-spacing:.06em;text-transform:uppercase;cursor:pointer}

  #lavoro{position:fixed;top:72px;left:12px;bottom:100px;width:400px;max-width:94vw;z-index:50;
     overflow:auto;background:#0d0b08;border:1px solid rgba(217,180,90,.35);border-radius:14px;padding:16px 18px;
     box-shadow:0 24px 70px rgba(0,0,0,.75);display:none;scrollbar-width:thin;scrollbar-color:rgba(217,180,90,.35) transparent}
  #lavoro.aperto{display:block}
  #lavoro h2{margin:0 0 4px;font-family:Cinzel,serif;font-size:14px;letter-spacing:.08em;color:var(--oro)}
  #lavoro h3{margin:16px 0 6px;font-size:9px;letter-spacing:.24em;text-transform:uppercase;
     color:var(--tenue);font-weight:600;border-top:1px solid rgba(217,180,90,.18);padding-top:11px}
  #lavoro .rg{padding:5px 0;border-bottom:1px solid rgba(217,180,90,.10)}
  #lavoro .rg a{color:var(--oro);cursor:pointer;text-decoration:none}
  #lavoro .rg a:hover{text-decoration:underline}
  #lavoro .rg .n{color:var(--oro-chiaro);font-variant-numeric:tabular-nums}
  #lavoro .spiega{font-size:11.5px;color:var(--tenue);line-height:1.55;margin:0 0 8px}
  button.sg{background:#0d0b08;border:1px solid rgba(217,180,90,.3);color:var(--tenue);
     border-radius:14px;padding:3px 10px;font:600 10px Inter,sans-serif;cursor:pointer}
  button.sg.on{background:linear-gradient(180deg,#f2dba4,#d9b45a);color:#0a0908;border-color:var(--oro)}
  .nota-n{font-size:11.5px;color:#e0a08c;background:rgba(224,100,79,.10);
     border:1px solid rgba(224,100,79,.3);border-radius:8px;padding:7px 10px;margin-top:9px;line-height:1.5}

  #menu .gr{margin-top:14px;border-top:1px solid rgba(217,180,90,.14);padding-top:10px}
  #menu .gr:first-of-type{border-top:0;padding-top:0}
  #menu .gr h3{margin:0 0 7px;font-size:9px;letter-spacing:.24em;color:var(--tenue);text-transform:uppercase;font-weight:600}
  #menu .cmd{display:flex;justify-content:space-between;align-items:center;gap:10px;width:100%;
     background:rgba(242,233,216,.03);border:1px solid rgba(217,180,90,.25);border-radius:9px;
     padding:9px 11px;margin-bottom:6px;font:600 12px Inter,sans-serif;color:var(--inchiostro);
     cursor:pointer;text-align:left;text-decoration:none}
  #menu .cmd:hover{border-color:var(--oro);color:var(--oro-chiaro);background:rgba(217,180,90,.09)}
  #menu .cmd kbd{font-family:Consolas,monospace;font-size:10px;color:var(--tenue);
     border:1px solid rgba(217,180,90,.3);border-radius:5px;padding:1px 6px;background:rgba(0,0,0,.35)}
  #menu .cmd.on{border-color:var(--oro);background:rgba(217,180,90,.14);color:var(--oro-chiaro)}
  #menu .spiega{font-size:11px;color:var(--tenue);margin:-2px 0 9px;line-height:1.45}
  .velo{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:51;display:none}
  .velo.on{display:block}

  @media(max-width:700px){
    #net .bar{padding:8px 10px;gap:6px}
    #net .bar h1{font-size:11px}
    #net .kpi{gap:8px;font-size:10px;width:100%}
    #net .right{margin-left:0;width:100%}
    #net input#q{flex:1;min-width:110px;width:auto}
    #net .nb{padding:7px 8px;font-size:9px}
    #scena{height:calc(100vh - 300px);min-height:420px}
    .aiuto{display:none}
    #pan,#menu,#lavoro{top:auto;left:8px;right:8px;width:auto;max-width:none;bottom:100px;max-height:62vh;
       transform:translateY(calc(100% + 120px));border-radius:14px;padding-bottom:36px}
    #lavoro{display:block;transform:translateY(calc(100% + 120px));transition:transform .22s ease}
    #lavoro.aperto{transform:none}
    .risultati{left:0;right:auto}
  }
</style>

<section class="vista on">
<?php require_once __DIR__ . '/_media.php'; echo media_hero('img_section_network', '118px', ['caption' => 'Your network — every node is a person', 'loading' => 'eager']); ?>
<div id="net">
  <div class="carta bar" style="margin:0 0 10px">
    <h1>Network</h1>
    <div class="kpi">
      <span>my position <b id="k-posto">#—</b></span>
      <span>direct people <b id="k-diretti">—</b></span>
      <span>active <b id="k-att">—</b></span>
      <span>free seats below you <b id="k-tot">—</b></span>
    </div>
    <div class="right" style="position:relative">
      <?php if ($ADMIN): ?>
      <div class="segm" id="ambito"><button type="button" class="on" data-t="0">My branch</button><button type="button" data-t="1">Whole network</button></div>
      <?php endif; ?>
      <input id="q" placeholder="search your network…" autocomplete="off">
      <button class="nb" id="vai">Search</button>
      <button class="nb" id="apri-menu" title="All commands (M)">☰ Menu</button>
      <button class="nb" id="forma" title="Tree (you at the bottom, branches rising) · Side · Fan · Star (you at the centre) · Rings">▲ Tree</button>
      <button class="nb" id="tutto" title="Opens your directs and their directs">⤢ Open 2 levels</button>
      <button class="nb" id="richiudi">⤡ Collapse</button>
      <a class="link-classic" href="network-classic.php">Classic 3D view ›</a>
      <button id="b-isola"  style="display:none"></button>
      <button id="b-tutto"  style="display:none"></button>
      <button id="b-filtro" style="display:none"></button>
      <button id="b-prof"   style="display:none"></button>
      <button id="b-link"   style="display:none"></button>
      <button id="b-png"    style="display:none"></button>
      <button id="b-centra" style="display:none"></button>
      <button id="b-lavoro" style="display:none"></button>
      <div class="risultati" id="ris"></div>
    </div>
  </div>

  <div id="scena">
    <svg id="svg"></svg>
    <div class="carico" id="carico">loading…</div>
    <div class="briciole" id="briciole"></div>
    <div class="legenda" id="legenda"></div>
    <div class="stato-vista" id="stato-vista"></div>
    <div class="aiuto">wheel = zoom · drag = pan · click a node = open it and see its card · arrows = move node by node</div>
  </div>
</div>
</section>

<div class="tip" id="tip"></div>

<div id="lavoro">
  <button class="chiudi-x" onclick="document.getElementById('lavoro').classList.remove('aperto')">✕</button>
  <h2>What to do tonight</h2>
  <div id="lavoro-corpo" style="color:var(--tenue);font-size:12.5px">loading…</div>
</div>

<div id="pan">
  <button class="chiudi-x" onclick="document.getElementById('pan').classList.remove('aperto')">✕</button>
  <div id="pan-corpo"></div>
</div>

<div class="velo" id="velo"></div>
<div id="menu">
  <button class="chiudi-x" onclick="chiudiMenu()">✕</button>
  <h2>Commands</h2>

  <div class="gr"><h3>View</h3>
    <p class="spiega">Five ways to look at your network. You are always the starting point.</p>
    <button class="cmd" data-vista="albero"><span>▲ Tree — you at the bottom</span><kbd>1</kbd></button>
    <button class="cmd" data-vista="orizzontale"><span>⇥ Side — you on the left</span><kbd>2</kbd></button>
    <button class="cmd" data-vista="ventaglio"><span>⌒ Fan — opens above you</span><kbd>3</kbd></button>
    <button class="cmd" data-vista="stella"><span>✦ Star — you at the centre</span><kbd>4</kbd></button>
    <button class="cmd" data-vista="anelli"><span>◎ Rings — how much each branch weighs</span><kbd>5</kbd></button>
    <p class="spiega">In the <b>Rings</b> a slice is as wide as the <b>people</b> below it:
    thin, dark branches are the idle ones to pick up again.</p>
  </div>
  <div class="gr"><h3>Move</h3>
    <button class="cmd" data-clic="tutto"><span>⤢ Open two levels</span><kbd>W</kbd></button>
    <button class="cmd" data-clic="richiudi"><span>⤡ Collapse all</span><kbd>R</kbd></button>
    <button class="cmd" data-az="fit"><span>⊹ Fit to screen</span><kbd>F</kbd></button>
    <button class="cmd" data-az="cerca"><span>⌕ Search your network</span><kbd>/</kbd></button>
    <button class="cmd" data-az="io"><span>◎ Back to you</span><kbd>T</kbd></button>
    <button class="cmd" data-clic="b-centra"><span>⊙ Centre on the selected person</span><kbd>C</kbd></button>
    <p class="spiega">With the <b>arrow keys</b> you move from person to person without a mouse.</p>
  </div>
  <div class="gr"><h3>Work</h3>
    <button class="cmd" data-clic="b-lavoro"><span>✚ What to do tonight</span><kbd>N</kbd></button>
    <p class="spiega">How your network is growing, and who has stopped and needs a call.</p>
  </div>
  <div class="gr"><h3>One piece at a time</h3>
    <button class="cmd" data-clic="b-isola"><span>⌗ Enter this branch</span><kbd>I</kbd></button>
    <button class="cmd" data-clic="b-tutto"><span>⤺ Back to the whole network</span><kbd>B</kbd></button>
    <button class="cmd" data-clic="b-filtro"><span>● People only (hide free seats)</span><kbd>O</kbd></button>
    <button class="cmd" data-clic="b-prof"><span>▤ How many levels to show</span><kbd>L</kbd></button>
  </div>
  <div class="gr"><h3>Export</h3>
    <button class="cmd" data-clic="b-link"><span>⧉ Copy the link to this view</span><kbd>K</kbd></button>
    <button class="cmd" data-clic="b-png"><span>▣ Download the image of your network</span><kbd>S</kbd></button>
    <p class="spiega">The image is your network as it is right now: send it, show it, put it in a story.</p>
  </div>
  <div class="gr"><h3>What you can do</h3>
    <p class="spiega">Click a person: their branch opens and you see their card.
    This is read-only: your network is not reorganised from here, and of the people below you
    you never see email, wallet or balances — they are people, not records.</p>
  </div>
</div>

<script src="../home/vendor/d3.v7.min.js"></script>
<script>
var NET_ADMIN = <?= $ADMIN ? 'true' : 'false' ?>;
</script>
<script>
(function(){
  const API = 'net-api.php';
  let MIO = 0;                       /* arriva dall'API (azione=mia → j.mio) */
  let TUTTO = false;                 /* admin: intera rete (&tutto=1) */
  const PASSO = 80;
  const LOC = 'en-US';

  const svg = d3.select('#svg');
  const svgEl = document.getElementById('svg');
  const dim = () => ({w: svgEl.clientWidth || 800, h: svgEl.clientHeight || 500});
  /* la scena riempie quello che resta dello schermo sopra la barra in basso (mai nascosta dietro) */
  function misura(){
    const sc = document.getElementById('scena');
    const top = sc.getBoundingClientRect().top + (document.querySelector('main') ? document.querySelector('main').scrollTop : 0);
    const h = Math.max(420, window.innerHeight - top - 104 - 8);
    sc.style.height = h + 'px';
  }
  misura();
  const largo = () => window.innerWidth > 700;
  const defs = svg.append('defs');
  (function(){
    const f = defs.append('filter').attr('id','bagliore')
                  .attr('x','-160%').attr('y','-160%').attr('width','420%').attr('height','420%');
    f.append('feGaussianBlur').attr('stdDeviation',3.4).attr('result','s');
    const m = f.append('feMerge');
    m.append('feMergeNode').attr('in','s'); m.append('feMergeNode').attr('in','s');
    m.append('feMergeNode').attr('in','SourceGraphic');
    const g = defs.append('linearGradient').attr('id','linfa')
                  .attr('x1','0').attr('y1','1').attr('x2','0').attr('y2','0');
    g.append('stop').attr('offset','0%').attr('stop-color','#d9b45a').attr('stop-opacity',.95);
    g.append('stop').attr('offset','55%').attr('stop-color','#c39a45').attr('stop-opacity',.6);
    g.append('stop').attr('offset','100%').attr('stop-color','#f2dba4').attr('stop-opacity',.35);
    const r = defs.append('radialGradient').attr('id','raggio');
    r.append('stop').attr('offset','0%').attr('stop-color','#f2dba4').attr('stop-opacity',.9);
    r.append('stop').attr('offset','45%').attr('stop-color','#d9b45a').attr('stop-opacity',.55);
    r.append('stop').attr('offset','100%').attr('stop-color','#8a6f38').attr('stop-opacity',.28);
  })();

  const gScia = svg.append('g').attr('fill','none').attr('stroke','#d9b45a')
                   .attr('stroke-linecap','round').attr('opacity',.30).attr('filter','url(#bagliore)');
  const gLink = svg.append('g').attr('fill','none').attr('stroke','url(#linfa)')
                   .attr('stroke-linecap','round');
  const gNodo = svg.append('g');
  const gArchi = svg.append('g');   /* gli Anelli: spicchi, non pallini */
  const tip = d3.select('#tip'), carico = d3.select('#carico');

  /* IO sono bianco caldo e piu' grande; gli altri sono oro. */
  const COL = {io:'#fff4cf', attivo:'#ffd166', fermo:'#b3862c', libero:'rgba(10,9,8,.85)'};
  const _qs = new URLSearchParams(location.search);
  const _f0 = _qs.get('forma');
  let radice = null, selezionato = null,
      forma = (['albero','orizzontale','ventaglio','stella','anelli'].includes(_f0) ? _f0 : 'albero'),
      passoX = 132, fitto = false;
  let soloOccupati = false;
  const _soloIniziale = (_qs.get('solo') === '1');
  const _postoIniziale = parseInt(_qs.get('posto') || '0', 10) || 0;
  let profMax = Math.max(0, Math.min(9, parseInt(_qs.get('liv') || '0', 10) || 0));
  const pilaIsola = [];
  const pesi = {};
  let pesiInCorso = false;
  let mappaPadre = new Map();
  let trasformazione = d3.zoomIdentity;

  async function chiedi(p){
    carico.style('display','block');
    try {
      const u = new URLSearchParams(p);
      if (TUTTO) u.set('tutto','1');
      const r = await fetch(API + '?' + u.toString(), {credentials:'same-origin'});
      const j = await r.json();
      if (!j.ok) throw new Error(j.err || 'error');
      return j;
    } finally { carico.style('display','none'); }
  }

  function prepara(n){ n._figli = n._figli || []; n.children = null; return n; }
  function trova(n, p){
    if (!n) return null;
    if (n.posto === p) return n;
    for (const c of (n._figli || [])) { const t = trova(c, p); if (t) return t; }
    return null;
  }
  function etichetta(d){
    if (d.piu) return '▾ more ' + d.restanti.toLocaleString(LOC);
    if (d.io)  return 'YOU';
    if (d.occupato) return (d.nome || ('#' + d.posto));
    return '#' + d.posto;
  }

  async function espandi(n){
    if (n.piu) return;
    if (n.children) { n.children = null; disegna(); return; }      // chiudi
    if (!n._figli.length && n.figli > 0) {
      const j = await chiedi({azione:'figli', posto:n.posto, limit:PASSO, offset:0,
                             solo_occupati: soloOccupati ? 1 : 0});
      n._figli = (j.figli || []).map(prepara);
      if ((j.totale || 0) > n._figli.length)
        n._figli.push({posto:-1000000-n.posto, piu:1, diChi:n.posto,
                       restanti:(j.totale - n._figli.length), tipo:'piu', figli:0, occupato:0});
    }
    n.children = n._figli.length ? n._figli : null;
    disegna(); adatta();
  }
  async function altraPagina(padre){
    const veri = (padre._figli || []).filter(x => !x.piu);
    const j = await chiedi({azione:'figli', posto:padre.posto, limit:PASSO, offset:veri.length,
                           solo_occupati: soloOccupati ? 1 : 0});
    const nuovi = (j.figli || []).map(prepara);
    padre._figli = veri.concat(nuovi);
    if ((j.totale || 0) > padre._figli.length)
      padre._figli.push({posto:-1000000-padre.posto, piu:1, diChi:padre.posto,
                         restanti:(j.totale - padre._figli.length), tipo:'piu', figli:0, occupato:0});
    padre.children = padre._figli;
    disegna(); adatta();
  }

  /* ---- GLI ANELLI — quanto pesa ogni ramo (persone sotto, non posti) ---- */
  const arco = d3.arc()
    .startAngle(d => d.x0).endAngle(d => d.x1)
    .padAngle(0.006).padRadius(90)
    .innerRadius(d => d.y0).outerRadius(d => Math.max(d.y0 + 1, d.y1 - 2));

  function disegnaAnelli(root){
    gLink.attr('display','none'); gScia.attr('display','none'); gNodo.attr('display','none');
    gArchi.attr('display', null);

    const senza = root.descendants().map(d => d.data.posto).filter(v => v >= 0 && !(v in pesi));
    if (senza.length && !pesiInCorso){
      pesiInCorso = true;
      chiedi({azione:'pesi', posti: senza.slice(0,300).join(',')})
        .then(j => { Object.assign(pesi, j.pesi || {}); })
        .catch(() => {})
        .finally(() => { pesiInCorso = false; if (forma === 'anelli') disegna(); });
    }

    const totVero = (pesi[root.data.posto] && pesi[root.data.posto].occupati) || 0;
    const senzaPeso = !(root.data.posto in pesi);
    let veri = 0;
    root.each(d => {
      const pz = pesi[d.data.posto];
      d.value = pz ? Math.max(0, pz.occupati) : (d.data.occupato ? 1 : 0);
      veri += d.value;
    });
    const briciola = Math.max(0.35, veri * 0.006);
    root.each(d => { if (d.value === 0) d.value = briciola; });
    root.eachAfter(d => {
      if (d.children){ const q = d3.sum(d.children, c => c.value); if (d.value < q) d.value = q; }
    });
    const perNodi = (!senzaPeso && totVero === 0);

    const D = dim();
    const R = Math.max(150, Math.min(D.w, D.h)/2 - 40);
    d3.partition().size([2*Math.PI, R])(root);
    const nodi = root.descendants();
    nodi.forEach(d => {
      const a = (d.x0 + d.x1)/2 - Math.PI/2, r = (d.y0 + d.y1)/2;
      d.px = Math.cos(a)*r; d.py = Math.sin(a)*r;
      d.data._xy = {x:d.px, y:d.py};
    });

    const a = gArchi.selectAll('path').data(nodi, d => d.data.posto);
    a.exit().remove();
    a.enter().append('path').attr('stroke','#0a0908').attr('stroke-width',.7)
      .merge(a)
      .attr('d', arco)
      .attr('fill', d => d.data.io ? COL.io
                       : (d.data.occupato ? (d.data.attivo ? COL.attivo : COL.fermo) : '#2a2418'))
      .attr('fill-opacity', d => d.depth === 0 ? 1 : Math.max(.32, .95 - d.depth*.13))
      .style('cursor','pointer')
      .on('mouseenter', (ev,d) => {
        const pz = pesi[d.data.posto];
        tip.style('display','block')
           .style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px')
           .html('<b>' + (d.data.io ? 'YOU' : (d.data.nome || ('seat '+d.data.posto))) + '</b><br>'
               + '<span style="font-size:19px;color:#f2dba4">'
               + Number(d.data.rete || (pz ? pz.occupati : 0)).toLocaleString(LOC) + '</span> '
               + '<span style="color:#a99a80">people in their network</span><br>'
               + '<span style="color:#a99a80">direct</span> <b>'
               + Number(d.data.rete_diretti||0).toLocaleString(LOC) + '</b>'
               + '<span style="color:#6d6153"> · </span>'
               + '<span style="color:#a99a80">active</span> <b>'
               + Number(d.data.rete_attivi||0).toLocaleString(LOC) + '</b>');
      })
      .on('mousemove', ev => tip.style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px'))
      .on('mouseleave', () => tip.style('display','none'))
      .on('click', async (ev,d) => {
        ev.stopPropagation();
        if (d.data.piu){ const pd = trova(radice, d.data.diChi); if (pd) await altraPagina(pd); return; }
        selezionato = d.data; scheda(d.data.posto); await espandi(d.data);
      });

    const et = gArchi.selectAll('text').data(
      nodi.filter(d => d.depth > 0 && (d.x1-d.x0) > .075 && (d.y1-d.y0) > 16), d => d.data.posto);
    et.exit().remove();
    et.enter().append('text')
      .attr('font-family','Georgia,serif').attr('font-size',10.5)
      .attr('text-anchor','middle').attr('dy','0.32em').attr('pointer-events','none')
      .merge(et)
      .attr('fill', d => d.data.occupato ? '#0a0908' : '#9a8c74')
      .attr('transform', d => {
        const ang = (d.x0+d.x1)/2*180/Math.PI - 90, r = (d.y0+d.y1)/2;
        return `rotate(${ang}) translate(${r},0) rotate(${ang>90?180:0})`;
      })
      .text(d => {
        const t = d.data.occupato ? (d.data.nome || ('#'+d.data.posto)) : ('#'+d.data.posto);
        const sp = Math.floor((d.x1-d.x0) * ((d.y0+d.y1)/2) / 6.2);
        return t.length > sp ? (sp > 3 ? t.slice(0,sp-1) + '…' : '') : t;
      });

    const c = gArchi.selectAll('text.cuore').data([root]);
    c.enter().append('text').attr('class','cuore').attr('text-anchor','middle')
      .attr('font-family','Georgia,serif').attr('pointer-events','none')
      .merge(c)
      .attr('font-size',13).attr('font-weight',700).attr('fill','#4a3a12').attr('dy','0.32em')
      .text(senzaPeso ? 'counting…'
            : (perNodi ? 'nobody yet' : (Number(totVero).toLocaleString(LOC) + ' people')));

    const lg = document.getElementById('legenda');
    if (lg){
      lg.style.display = 'block';
      lg.innerHTML = (perNodi || senzaPeso)
        ? 'Rings: each ring is a level. When someone joins, the width of the slice becomes '
          + 'the number of people below them.'
        : 'Rings: each ring is a level, the width of a slice is <b>how many people</b> '
          + 'sit below that branch. Total: <b>' + Number(totVero).toLocaleString(LOC)
          + '</b>. Branches with nobody yet stay <b>thin and dark</b>: those are the ones to pick up again.';
    }
    briciole();
  }

  /* il ramo morbido: esce dal padre salendo, si piega, arriva salendo */
  function ramo(d){
    const sx=d.source.px, sy=d.source.py, tx=d.target.px, ty=d.target.py, dy=ty-sy;
    return `M${sx},${sy} C${sx},${sy+dy*0.62} ${tx},${sy+dy*0.34} ${tx},${ty}`;
  }
  const spess = d => forma==='albero' ? Math.max(0.75, 3.8 - d.target.depth*0.82) : 1.4;
  const raggioN = d => d.data.piu ? 5 : (d.data.io ? 15 : (d.data.occupato ? 9 : 6));

  function disegna(){
    if (!radice) return;
    const root = d3.hierarchy(radice, d => d.children);
    if (profMax > 0) root.each(d => { if (d.depth >= profMax) d.children = null; });
    mappaPadre = new Map();
    root.each(d => { if (d.parent) mappaPadre.set(d.data.posto, d.parent.data); });

    if (forma === 'anelli') { disegnaAnelli(root); return; }
    gArchi.attr('display','none').selectAll('*').remove();
    gLink.attr('display', null); gScia.attr('display', null); gNodo.attr('display', null);
    const _lg = document.getElementById('legenda'); if (_lg) _lg.style.display = 'none';

    const nodi = root.descendants(), archi = root.links();
    const quanti = nodi.length;
    const D = dim();

    if (forma === 'ventaglio'){
      const prof = Math.max(1, d3.max(nodi, d => d.depth) || 1);
      const rg = 185 + prof*140 + Math.min(380, quanti*0.85);
      d3.tree().size([Math.PI, rg])
        .separation((a,b) => (a.parent===b.parent?1:2)/Math.max(1,a.depth))(root);
      nodi.forEach(d => { const th = Math.PI + d.x;
        d.px = Math.cos(th)*d.y; d.py = Math.sin(th)*d.y; d.ang = th + Math.PI/2; });
    } else if (forma === 'stella'){
      const prof = Math.max(1, d3.max(nodi, d => d.depth) || 1);
      const rg = 165 + prof*130 + Math.min(400, quanti*0.9);
      d3.tree().size([2*Math.PI, rg])
        .separation((a,b) => (a.parent===b.parent?1:2.2)/Math.max(1,a.depth))(root);
      nodi.forEach(d => { const a=d.x-Math.PI/2; d.px=Math.cos(a)*d.y; d.py=Math.sin(a)*d.y; d.ang=d.x; });
    } else if (forma === 'orizzontale'){
      const py = quanti>400?13:(quanti>150?17:26);
      d3.tree().nodeSize([py,260])(root);
      nodi.forEach(d => { d.px=d.y; d.py=d.x; d.ang=0; });
    } else {
      const foglie = Math.max(1, root.leaves().length);
      const panApr = largo() && document.getElementById('pan').classList.contains('aperto');
      const disp = Math.max(560, D.w - (panApr?380:40) - 40);
      passoX = Math.max(34, Math.min(132, disp/foglie));
      const prof = Math.max(1, d3.max(nodi, d => d.depth) || 1);
      const hD = Math.max(320, D.h - 90);
      const passoY = Math.max(96, Math.min(300, hD/(prof+0.55)));
      d3.tree().nodeSize([passoX, passoY]).separation((a,b)=>(a.parent===b.parent?1:1.4))(root);
      fitto = (passoX < 74);
      nodi.forEach(d => { d.px=d.x; d.py=-d.y; d.ang=0; });   // il meno: si sale
    }
    nodi.forEach(d => d.data._xy = {x:d.px, y:d.py});

    const l = gLink.selectAll('path').data(archi, d => d.target.data.posto);
    l.exit().remove();
    l.enter().append('path').merge(l)
      .attr('d', d => forma==='orizzontale' ? d3.linkHorizontal().x(p=>p.px).y(p=>p.py)(d)
            : ((forma==='stella' || forma==='ventaglio')
               ? `M${d.source.px},${d.source.py} Q${(d.source.px+d.target.px)/2*0.7},${(d.source.py+d.target.py)/2*0.7} ${d.target.px},${d.target.py}`
               : ramo(d)))
      .attr('stroke-width', spess)
      .attr('stroke-opacity', d => Math.max(.34, .95 - d.target.depth*.12));

    const sc = gScia.selectAll('path').data(quanti<320 && forma==='albero' ? archi : [], d => d.target.data.posto);
    sc.exit().remove();
    sc.enter().append('path').merge(sc)
      .attr('d', ramo).attr('stroke-width', d => spess(d)*2.6)
      .attr('stroke-opacity', d => Math.max(.10, .5 - d.target.depth*.09));

    const g = gNodo.selectAll('g.nodo').data(nodi, d => d.data.posto);
    g.exit().remove();
    const en = g.enter().append('g').attr('class','nodo');
    en.append('circle'); en.append('text').attr('class','et'); en.append('text').attr('class','pi');
    const tutti = en.merge(g);
    const alone = (quanti < 320);

    tutti.attr('transform', d => `translate(${d.px},${d.py})`);
    tutti.select('circle')
      .attr('r', raggioN)
      .attr('fill', d => d.data.piu ? 'rgba(217,180,90,.85)'
                       : (d.data.io ? COL.io
                       : (d.data.occupato ? (d.data.attivo ? COL.attivo : COL.fermo) : COL.libero)))
      .attr('stroke', d => d.data.io ? COL.io : (d.data.occupato ? (d.data.attivo?COL.attivo:COL.fermo) : '#8a7c68'))
      .attr('stroke-width', d => d.data.posto === (selezionato && selezionato.posto) ? 3.5 : 1.6)
      .attr('stroke-opacity', d => (d.data.piu || d.data.occupato) ? 1 : .5)
      .attr('stroke-dasharray', d => (d.data.piu || d.data.occupato) ? null : '3,2')
      .attr('filter', d => (alone && (d.data.io || d.data.occupato)) ? 'url(#bagliore)' : null);

    tutti.select('text.et')
      .attr('font-size', d => d.data.io ? 13 : 10.5)
      .attr('font-family','Georgia,serif')
      .attr('font-weight', d => d.data.io ? 700 : 400)
      .attr('fill', d => d.data.io ? '#fff4cf' : (d.data.occupato ? '#f2e9d8' : '#7e7263'))
      .each(function(d){
        const el = d3.select(this), rr = raggioN(d) + 6;
        if (forma === 'stella' || forma === 'ventaglio'){
          if (d.depth === 0){ el.attr('text-anchor','middle')
                                .attr('dy', forma==='ventaglio' ? 26 : -22)
                                .attr('x',0).attr('transform',null); return; }
          const gr = d.ang*180/Math.PI - 90, sx = (gr>90||gr<-90);
          el.attr('x',null).attr('dy','0.32em').attr('text-anchor', sx?'end':'start')
            .attr('transform', `rotate(${gr}) translate(${sx?-rr:rr},0)${sx?' rotate(180)':''}`);
        } else if (forma === 'orizzontale'){
          el.attr('transform',null).attr('dy','0.32em').attr('x', rr).attr('text-anchor','start');
        } else {
          el.attr('x',null).attr('dy',null);
          if (d.depth === 0){ el.attr('text-anchor','middle').attr('transform',`translate(0,${rr+15})`); return; }
          if (fitto) el.attr('text-anchor','start').attr('transform',`translate(0,${-(rr+3)}) rotate(-42)`);
          else       el.attr('text-anchor','middle').attr('transform',`translate(0,${-(rr+8)})`);
        }
      })
      .text(d => etichetta(d.data));

    tutti.select('text.pi')
      .attr('dy','0.32em').attr('text-anchor','middle')
      .attr('x', d => forma==='orizzontale' ? (raggioN(d)+5) : 0)
      .attr('y', d => forma==='orizzontale' ? 0 : -(raggioN(d)+6))
      .attr('font-size',9.5).attr('font-family','Georgia,serif').attr('fill','#d9b45a')
      .text(d => {
        const r = Number(d.data.rete || 0);
        if (r > 0) return '▾ ' + (r >= 1000 ? (r/1000).toFixed(r >= 10000 ? 0 : 1).replace('.0','') + 'k' : r);
        return (!d.children && d.data.figli > 0) ? '·' : '';
      });

    tutti
      .on('mouseenter', (ev,d) => {
        if (d.data.piu) return;
        const nn = v => Number(v||0).toLocaleString(LOC);
        const r = Number(d.data.rete || 0);
        tip.style('display','block').html(
          '<b>' + (d.data.io ? 'Your position' : (d.data.nome || 'free seat')) + '</b><br>' +
          (d.data.occupato
            ? ('<span style="font-size:19px;color:#f2dba4;line-height:1.5">' + nn(r) + '</span> '
               + '<span style="color:#a99a80">' + (r === 1 ? 'person' : 'people') + ' in their network</span><br>'
               + '<span style="color:#a99a80">direct</span> <b>' + nn(d.data.rete_diretti) + '</b>'
               + '<span style="color:#6d6153"> · </span>'
               + '<span style="color:#a99a80">active</span> <b>' + nn(d.data.rete_attivi) + '</b><br>')
            : '') +
          '<span style="color:#6d6153">seat #' + d.data.posto + ' · level ' + d.data.livello + ' · ' +
          (d.data.occupato ? (d.data.attivo ? 'active' : 'not active yet') : 'free seat') +
          '</span>' +
          (d.data.preso_il ? '<br><span style="color:#6d6153">joined on ' + d.data.preso_il + '</span>' : ''));
      })
      .on('mousemove', ev => tip.style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px'))
      .on('mouseleave', () => tip.style('display','none'))
      .on('click', async (ev,d) => {
        ev.stopPropagation();
        if (d.data.piu){ const p = trova(radice, d.data.diChi); if (p) await altraPagina(p); return; }
        selezionato = d.data;
        await scheda(d.data.posto);
        await espandi(d.data);
      });

    briciole();
  }

  const zoom = d3.zoom().scaleExtent([0.05, 5]).on('zoom', ev => {
    trasformazione = ev.transform;
    gScia.attr('transform', ev.transform);
    gLink.attr('transform', ev.transform);
    gNodo.attr('transform', ev.transform);
    gArchi.attr('transform', ev.transform);
  });
  svg.call(zoom).on('dblclick.zoom', null);
  svg.on('mousedown.cur', () => svgEl.classList.add('trascino'));
  window.addEventListener('mouseup', () => svgEl.classList.remove('trascino'));

  function adatta(){
    const nodi = (forma === 'anelli') ? gArchi.selectAll('path').data()
                                      : gNodo.selectAll('g.nodo').data();
    if (!nodi.length) return;
    const D = dim();
    const xs = nodi.map(d=>d.px), ys = nodi.map(d=>d.py);
    const x0=Math.min(...xs), x1=Math.max(...xs), y0=Math.min(...ys), y1=Math.max(...ys);
    const panApr = largo() && document.getElementById('pan').classList.contains('aperto');
    const bordo = 30, W = D.w - (panApr?360:0) - bordo*2, H = D.h - 110;
    const k = Math.max(.06, Math.min(1.6, .94*Math.min(W/(Math.max(1,x1-x0)+(largo()?200:110)), H/(Math.max(1,y1-y0)+80))));
    const cx = bordo + W/2, cy = 45 + H/2;
    const ty = (forma==='albero') ? (D.h - 64 - y1*k) : (cy - ((y0+y1)/2)*k);
    svg.transition().duration(450).call(zoom.transform,
      d3.zoomIdentity.translate(cx - ((x0+x1)/2)*k, ty).scale(k));
  }
  window.addEventListener('resize', () => { misura(); if (radice) { disegna(); adatta(); } });

  function briciole(){
    const b = document.getElementById('briciole');
    b.innerHTML = '<a data-p="' + MIO + '">YOU</a>' +
      (selezionato && selezionato.posto !== MIO ? ' <span>›</span> <b>#' + selezionato.posto + '</b>' : '');
    b.querySelectorAll('a').forEach(a => a.onclick = async () => {
      selezionato = radice; await scheda(MIO); adatta();
    });
  }

  /* ------------------------------------------------------------ la scheda */
  async function scheda(posto){
    const j = await chiedi({azione:'nodo', posto});
    const n = j.nodo, k = j.kpi || {};
    const num = v => (v||0).toLocaleString(LOC);
    const pan = document.getElementById('pan');
    document.getElementById('pan-corpo').innerHTML = `
      <h2>${n.io ? 'Your position' : (n.nome || 'Free seat')}</h2>
      <div style="margin:6px 0 2px">
        ${n.io ? '<span class="pill io">THIS IS YOU</span>' : ''}
        <span class="pill ${n.attivo ? 'vivo' : ''}">${n.occupato ? (n.attivo?'active':'not active') : 'free'}</span>
        <span class="pill">seat #${n.posto}</span>
        <span class="pill">level ${n.livello}</span>
      </div>

      <div class="sez"><h3>Grown above them</h3>
        <div class="griglia2">
          <div class="box"><b>${num(k.figli)}</b><small>direct branches</small></div>
          <div class="box"><b>${num(k.occupati)}</b><small>people</small></div>
          <div class="box"><b>${num(k.attivi)}</b><small>active</small></div>
          <div class="box"><b>${num(k.liberi)}</b><small>free seats</small></div>
        </div>
        <div style="margin-top:9px"><button class="nb2" id="b-ramo">Count the whole branch</button></div>
        <div id="ramo-esito" style="font-size:12px;margin-top:7px;color:var(--tenue)"></div>
      </div>

      ${n.occupato ? `
      <div class="sez"><h3>Who is there</h3>
        <div class="rg"><span>name</span><span>${n.nome || '—'}</span></div>
        <div class="rg"><span>joined on</span><span>${n.preso_il || '—'}</span></div>
      </div>` : ''}

      ${n.io ? `
      <div class="sez"><h3>Your invitation codes</h3>
        <div class="rg"><span>personal</span><span class="mono">${n.sic_personale || '—'}</span></div>
        <div class="rg"><span>of your seat</span><span class="mono">${n.sic || '—'}</span></div>
        <p style="font-size:11.5px;color:var(--tenue);margin:8px 0 0">
          Both work: whoever signs up with either of these two codes joins your network.</p>
      </div>` : `
      <div class="sez">
        <p style="font-size:11.5px;color:var(--tenue);margin:0">
          Of the people in your network you see what you need to grow it. Email, wallet and
          balances stay theirs: they are people, not records.</p>
      </div>`}
    `;
    pan.classList.add('aperto');
    const br = document.getElementById('b-ramo');
    if (br) br.onclick = async () => {
      br.disabled = true;
      try {
        const r = await chiedi({azione:'ramo', posto});
        document.getElementById('ramo-esito').innerHTML =
          'Whole branch: <b style="color:var(--oro)">' + num(r.discendenti) + '</b> positions · ' +
          num(r.occupati) + ' people · ' + num(r.attivi) + ' active';
      } finally { br.disabled = false; }
    };
    briciole();
  }

  /* ------------------------------------------------------------- comandi */
  const FORME = ['albero','orizzontale','ventaglio','stella','anelli'];
  const ETI = {albero:'▲ Tree', orizzontale:'⇥ Side',
               ventaglio:'⌒ Fan', stella:'✦ Star', anelli:'◎ Rings'};

  function centra(n){
    if (!n || !n._xy) return;
    const D = dim();
    const k = Math.max(trasformazione.k, .8);
    const panApr = largo() && document.getElementById('pan').classList.contains('aperto');
    svg.transition().duration(400).call(zoom.transform,
      d3.zoomIdentity.translate((D.w - (panApr?340:0))/2 - n._xy.x*k,
                                D.h/2 - n._xy.y*k).scale(k));
  }
  document.getElementById('b-centra').onclick = () => centra(selezionato);

  /* ---- COSA FARE STASERA — crescita e fermi ---- */
  const f0 = v => Number(v || 0).toLocaleString(LOC);
  const rg = (t, v) => '<div class="rg"><span>' + t + '</span><span class="n">' + v + '</span></div>';

  async function apriLavoro(){
    const box = document.getElementById('lavoro');
    box.classList.add('aperto');
    const c = document.getElementById('lavoro-corpo');
    c.innerHTML = '<h3>How it is growing</h3><div id="cr">reading…</div>'
                + '<h3>Who has stopped</h3>'
                + '<p class="spiega">People who have not brought anyone in for many days. '
                + 'Those who joined recently are not in this list: they are new, not idle.</p>'
                + '<div style="margin-bottom:8px">threshold: '
                + [7,14,30,60].map(g => '<button class="sg" data-g="' + g + '">' + g + ' days</button>').join(' ')
                + '</div><div id="fr">—</div>';

    chiedi({azione:'crescita', giorni:30}).then(k => {
      const el = document.getElementById('cr'); if (!el) return;
      const dati = (k.dati && !Array.isArray(k.dati)) ? k.dati : {};
      const g = dati.giorni || [];
      if (!g.length){
        el.innerHTML = '<div class="spiega">No data yet.</div>'
          + (dati.totale ? rg('in your whole network', f0(dati.totale)) : '');
        return;
      }
      const max = Math.max(1, ...g.map(x => x.entrate));
      const W = 340, H = 70, pw = W / Math.max(1, g.length);
      el.innerHTML =
        '<svg width="' + W + '" height="' + H + '" style="display:block;max-width:100%">'
        + g.map((x,i) => {
            const h = Math.round(x.entrate / max * (H - 16));
            return '<rect x="' + (i*pw+0.7).toFixed(1) + '" y="' + (H-h-12) + '" width="'
                 + (pw-1.4).toFixed(1) + '" height="' + Math.max(h, x.entrate ? 2 : 0)
                 + '" fill="' + (x.entrate ? '#d9b45a' : '#2a2418') + '"><title>'
                 + x.giorno + ': ' + x.entrate + '</title></rect>';
          }).join('')
        + '<text x="0" y="' + (H-2) + '" fill="#6d6153" font-size="10" font-family="Inter,sans-serif">'
        + (g[0] ? g[0].giorno : '') + '</text>'
        + '<text x="' + W + '" y="' + (H-2) + '" text-anchor="end" fill="#6d6153" font-size="10" '
        + 'font-family="Inter,sans-serif">today</text></svg>'
        + rg('joined in the last 30 days', f0(g.reduce((a,x) => a + x.entrate, 0)))
        + rg('in your whole network', f0(dati.totale))
        + (dati.senza_data > 0 ? '<div class="nota-n">' + f0(dati.senza_data)
           + ' people without a join date: not in the chart</div>' : '');
    }).catch(() => { const el = document.getElementById('cr'); if (el) el.textContent = 'cannot read the dates'; });

    async function caricaFermi(giorni){
      const el = document.getElementById('fr'); if (!el) return;
      el.textContent = 'searching…';
      c.querySelectorAll('.sg').forEach(b => b.classList.toggle('on', +b.dataset.g === giorni));
      let d;
      try { d = await chiedi({azione:'fermi', giorni}); }
      catch(e){ el.textContent = 'cannot read the dates'; return; }
      const fermi = d.fermi || [];
      if (!fermi.length){
        el.innerHTML = '<div class="spiega">Nobody idle for more than ' + giorni
          + ' days — or no data yet.</div>';
        return;
      }
      el.innerHTML = fermi.map(f =>
          '<div class="rg"><a data-p="' + f.posto + '">' + (f.nome || ('#' + f.posto)) + '</a>'
          + '<span class="n">' + f.fermo_da + ' d'
          + (f.mai ? ' <span style="color:#e0a08c;font-size:11px">never anyone</span>' : '')
          + '</span></div>').join('')
        + '<p class="spiega" style="margin-top:9px">They are ' + f0(d.quanti) + '. '
        + 'Click a name: I open it in the tree.</p>';
      el.querySelectorAll('a[data-p]').forEach(a => a.onclick = async () => {
        box.classList.remove('aperto');
        const n = trova(radice, +a.dataset.p);
        if (n){ selezionato = n; await scheda(n.posto); centra(n); }
        else { await cerca2(+a.dataset.p); }
      });
    }
    c.querySelectorAll('.sg').forEach(b => b.onclick = () => caricaFermi(+b.dataset.g));
    caricaFermi(14);
  }
  /* se quella persona non e' fra i rami gia' aperti, la si va a prendere */
  async function cerca2(posto){
    try {
      const j = await chiedi({azione:'nodo', posto});
      const strada = (j.catena || []).map(x => x.posto).concat([posto]);
      let cur = radice;
      for (const q of strada){
        if (q === radice.posto) { cur = radice; continue; }
        if (!cur.children) await espandi(cur);
        const succ = (cur.children || []).find(x => x.posto === q);
        if (!succ) break;
        cur = succ;
      }
      selezionato = cur; disegna(); centra(cur); await scheda(cur.posto);
    } catch(e){}
  }
  document.getElementById('b-lavoro').onclick = apriLavoro;

  /* ISOLA — un ramo per volta */
  async function isola(n){
    if (!n || n.piu || n.posto === radice.posto) return;
    pilaIsola.push(radice);
    radice = n;
    if (!n.children && n.figli > 0) { try { await espandi(n); } catch(e){} }
    selezionato = n; disegna(); adatta(); statoVista();
  }
  function tornaATutto(){
    if (!pilaIsola.length) return;
    radice = pilaIsola[0]; pilaIsola.length = 0;
    disegna(); adatta(); statoVista();
  }
  document.getElementById('b-isola').onclick = () => isola(selezionato);
  document.getElementById('b-tutto').onclick = tornaATutto;

  /* SOLO PERSONE — nasconde i posti liberi */
  function scorda(n){
    (n._figli || []).forEach(scorda);
    n._figli = []; n.children = null;
  }
  async function cambiaFiltro(){
    soloOccupati = !soloOccupati;
    scorda(radice);
    try { await espandi(radice); } catch(e){}
    disegna(); adatta(); statoVista();
  }
  document.getElementById('b-filtro').onclick = cambiaFiltro;

  document.getElementById('b-prof').onclick = () => {
    const giro = [0,2,3,4,5];
    profMax = giro[(giro.indexOf(profMax)+1) % giro.length];
    disegna(); adatta(); statoVista();
  };

  /* IL LINK A QUESTA VISTA */
  document.getElementById('b-link').onclick = async () => {
    const q = new URLSearchParams();
    q.set('forma', forma);
    if (selezionato && selezionato.posto > 0) q.set('posto', selezionato.posto);
    if (soloOccupati) q.set('solo','1');
    if (profMax) q.set('liv', String(profMax));
    const u = location.origin + location.pathname + '?' + q.toString();
    try { await navigator.clipboard.writeText(u); } catch(e){ window.prompt('Copy:', u); }
  };

  /* L'IMMAGINE — PNG con fondo nero */
  document.getElementById('b-png').onclick = () => {
    try{
      const n = document.getElementById('svg');
      const W = n.clientWidth || 1400, H = n.clientHeight || 860;
      const cl = n.cloneNode(true);
      cl.setAttribute('xmlns','http://www.w3.org/2000/svg');
      cl.setAttribute('width', W); cl.setAttribute('height', H);
      const img = new Image();
      img.onload = () => {
        const c = document.createElement('canvas'); c.width = W*2; c.height = H*2;
        const x = c.getContext('2d');
        x.fillStyle = '#070605'; x.fillRect(0,0,c.width,c.height);
        x.drawImage(img,0,0,c.width,c.height);
        const a = document.createElement('a');
        a.download = 'my-network-' + forma + '.png'; a.href = c.toDataURL('image/png'); a.click();
      };
      img.src = 'data:image/svg+xml;charset=utf-8,' +
                encodeURIComponent(new XMLSerializer().serializeToString(cl));
    }catch(e){}
  };

  /* LE FRECCE — nell'Albero i figli stanno SOPRA */
  async function vaiVicino(dir){
    if (!radice) return;
    if (!selezionato){ selezionato = radice; disegna(); centra(selezionato); return; }
    const versoFigli = (forma === 'albero') ? 'su' : 'giu';
    const p = mappaPadre.get(selezionato.posto);
    if (dir === versoFigli){
      if (!selezionato.children && selezionato.figli > 0) { try { await espandi(selezionato); } catch(e){} }
      const f = (selezionato.children || []).filter(x => !x.piu);
      if (!f.length) return;
      selezionato = f[0];
    } else if (dir === 'su' || dir === 'giu'){
      if (!p) return;
      selezionato = p;
    } else {
      if (!p) return;
      const f = (p.children || []).filter(x => !x.piu);
      const i = f.findIndex(x => x.posto === selezionato.posto);
      if (i < 0) return;
      selezionato = f[(i + (dir === 'dx' ? 1 : -1) + f.length) % f.length];
    }
    disegna(); centra(selezionato); scheda(selezionato.posto);
  }
  window.addEventListener('keydown', ev => {
    const t = ev.target;
    if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
    const m = {ArrowUp:'su', ArrowDown:'giu', ArrowLeft:'sx', ArrowRight:'dx'}[ev.key];
    if (!m) return;
    ev.preventDefault(); vaiVicino(m);
  });

  /* il cartellino che dice cosa e' acceso adesso */
  function statoVista(){
    const el = document.getElementById('stato-vista');
    if (!el) return;
    const v = [];
    if (pilaIsola.length) v.push('inside <b>' + (radice.nome || ('#'+radice.posto)) + '</b>');
    if (soloOccupati) v.push('<b>people only</b>');
    if (profMax) v.push('<b>' + profMax + ' levels</b>');
    if (!v.length){ el.style.display = 'none'; el.innerHTML = ''; return; }
    el.style.display = 'block';
    el.innerHTML = v.join(' · ') + '<button id="sv-azzera">whole network</button>';
    document.getElementById('sv-azzera').onclick = async () => {
      profMax = 0;
      if (pilaIsola.length) tornaATutto();
      if (soloOccupati) await cambiaFiltro();
      disegna(); adatta(); statoVista();
    };
  }
  document.getElementById('forma').textContent = ETI[forma];
  document.getElementById('forma').onclick = () => {
    forma = FORME[(FORME.indexOf(forma)+1) % FORME.length];
    document.getElementById('forma').textContent = ETI[forma];
    disegna(); adatta();
  };
  document.getElementById('richiudi').onclick = () => {
    if (!radice) return;
    (function chiudi(n){ n.children = null; (n._figli||[]).forEach(chiudi); })(radice);
    radice.children = radice._figli;
    disegna(); adatta();
  };
  document.getElementById('tutto').onclick = async () => {
    if (!radice) return;
    radice.children = radice._figli;
    for (const c of (radice._figli || [])) if (!c.piu && c.figli > 0) { try { await espandi(c); } catch(e){} }
    disegna(); adatta();
  };

  const boxRis = document.getElementById('ris');
  async function cerca(){
    const q = document.getElementById('q').value.trim();
    if (!q) return;
    const j = await chiedi({azione:'cerca', q});
    const r = j.risultati || [];
    boxRis.innerHTML = r.length
      ? r.map(x => `<div data-p="${x.posto}" data-path="${(x.percorso||[]).join(',')}">
           <b>${x.nome || ('seat #'+x.posto)}</b> · #${x.posto} · level ${x.livello}</div>`).join('')
      : '<div style="color:var(--tenue)">Nobody with this name in your network.</div>';
    boxRis.style.display = 'block';
    boxRis.querySelectorAll('[data-p]').forEach(d => d.onclick = async () => {
      boxRis.style.display = 'none';
      await vaiA(+d.dataset.p, (d.dataset.path||'').split(',').filter(Boolean).map(Number));
    });
  }
  document.getElementById('vai').onclick = cerca;
  document.getElementById('q').addEventListener('keydown', e => { if (e.key === 'Enter') cerca(); });
  document.addEventListener('click', e => {
    if (!boxRis.contains(e.target) && e.target.id !== 'q' && e.target.id !== 'vai') boxRis.style.display = 'none';
  });

  async function vaiA(posto, percorso){
    for (const p of (percorso || [])){
      const n = trova(radice, p);
      if (n && !n.children && n.figli > 0) { try { await espandi(n); } catch(e){} }
    }
    const n = trova(radice, posto);
    if (n) { selezionato = n; await scheda(posto); }
    disegna(); adatta();
  }

  /* --------------------------------------------------------------- avvio */
  async function avvio(){
    const j = await chiedi({azione:'mia'});
    if (j.senza_posto) {
      document.getElementById('carico').style.display = 'block';
      document.getElementById('carico').textContent = 'You do not have a seat in the network yet.';
      return;
    }
    MIO = Number(j.mio || (j.albero && j.albero.posto) || 0);
    document.getElementById('k-posto').textContent = '#' + MIO;
    pilaIsola.length = 0;
    for (const k in pesi) delete pesi[k];
    radice = prepara(j.albero);
    radice._figli = (radice._figli || []).map(prepara);
    radice.children = radice._figli;
    selezionato = radice;
    const k = j.kpi || {};
    document.getElementById('k-diretti').textContent = (k.occupati||0).toLocaleString(LOC);
    document.getElementById('k-att').textContent     = (k.attivi||0).toLocaleString(LOC);
    document.getElementById('k-tot').textContent     = (k.liberi||0).toLocaleString(LOC);
    disegna(); adatta();
    if (_soloIniziale && !soloOccupati) { try { await cambiaFiltro(); } catch(e){} }
    if (_postoIniziale > 0 && _postoIniziale !== MIO) { try { await cerca2(_postoIniziale); } catch(e){} }
    statoVista();
  }
  avvio().catch(err => {
    document.getElementById('carico').style.display = 'block';
    document.getElementById('carico').textContent = 'Cannot read your network: ' + err.message;
  });

  /* ADMIN: la mia rete / tutta la rete */
  const amb = document.getElementById('ambito');
  if (amb && window.NET_ADMIN){
    amb.querySelectorAll('button').forEach(b => b.onclick = async () => {
      const t = b.dataset.t === '1';
      if (t === TUTTO) return;
      TUTTO = t;
      amb.querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
      soloOccupati = false; profMax = 0;
      try { await avvio(); } catch(err){
        document.getElementById('carico').style.display = 'block';
        document.getElementById('carico').textContent = 'Cannot read the network: ' + err.message;
      }
    });
  }
})();
</script>

<script>
function chiudiMenu(){ document.getElementById('menu').classList.remove('aperto');
                       document.getElementById('velo').classList.remove('on'); }
(function(){
  const menu = document.getElementById('menu'), velo = document.getElementById('velo');
  const apri = () => { menu.classList.add('aperto'); velo.classList.add('on'); segna(); };
  document.getElementById('apri-menu').onclick = () => menu.classList.contains('aperto') ? chiudiMenu() : apri();
  velo.onclick = chiudiMenu;

  const NOMI = {albero:'Tree', orizzontale:'Side', ventaglio:'Fan', stella:'Star', anelli:'Rings'};
  const QUANTE = Object.keys(NOMI).length;
  const eLaVista = (v, testo) => testo.includes(NOMI[v]);
  function segna(){
    const b = document.getElementById('forma').textContent;
    menu.querySelectorAll('[data-vista]').forEach(x =>
      x.classList.toggle('on', eLaVista(x.dataset.vista, b)));
  }
  /* le viste: si preme il bottone della barra finche' non si arriva a quella chiesta */
  menu.querySelectorAll('[data-vista]').forEach(x => x.onclick = () => {
    const v = x.dataset.vista, b = document.getElementById('forma');
    for (let i = 0; i < QUANTE; i++) {
      if (eLaVista(v, b.textContent)) break;
      b.click();
    }
    segna();
  });
  menu.querySelectorAll('[data-clic]').forEach(x => x.onclick = () => {
    const b = document.getElementById(x.dataset.clic); if (b) b.click(); chiudiMenu();
  });
  menu.querySelectorAll('[data-az]').forEach(x => x.onclick = () => {
    const a = x.dataset.az;
    if (a === 'cerca') { chiudiMenu(); const q = document.getElementById('q'); if (q) { q.focus(); q.select(); } }
    if (a === 'fit')   { chiudiMenu(); window.dispatchEvent(new Event('resize')); }
    if (a === 'io')    { chiudiMenu(); const br = document.querySelector('.briciole a'); if (br) br.click(); }
  });

  /* scorciatoie: mai mentre si scrive in un campo */
  document.addEventListener('keydown', ev => {
    const t = (ev.target.tagName || '').toLowerCase();
    if (t === 'input' || t === 'textarea' || t === 'select' || ev.ctrlKey || ev.metaKey || ev.altKey) return;
    const k = ev.key.toLowerCase();
    const via = {'1':'albero','2':'orizzontale','3':'ventaglio','4':'stella','5':'anelli'}[ev.key];
    if (via) { menu.querySelector('[data-vista="'+via+'"]').click(); ev.preventDefault(); return; }
    const bott = {'w':'tutto','r':'richiudi',
                  'i':'b-isola','b':'b-tutto','o':'b-filtro','l':'b-prof',
                  'k':'b-link','s':'b-png','c':'b-centra','n':'b-lavoro'}[k];
    if (bott) { const b = document.getElementById(bott); if (b) { b.click(); ev.preventDefault(); } }
    if (k === 'f') { const x = menu.querySelector('[data-az="fit"]'); if (x) x.click(); ev.preventDefault(); }
    if (k === 't') { const x = menu.querySelector('[data-az="io"]'); if (x) x.click(); }
    if (ev.key === '/') { const q = document.getElementById('q'); if (q) { q.focus(); q.select(); ev.preventDefault(); } }
    if (k === 'm') { menu.classList.contains('aperto') ? chiudiMenu() : apri(); }
    if (ev.key === 'Escape') chiudiMenu();
  });
})();
</script>
<?php require __DIR__ . '/_piede.php'; ?>
