<?php
require_once __DIR__ . '/bootstrap.php';
$u = current_user();
$brand = site_brand();

$pageTitle = 'Piramide di Maslow Hudolin 2D/3D · I 5 Livelli della Rinascita';
$metaDesc = 'Esplora la Piramide di Maslow reinterpretata secondo il Metodo Hudolin in 2D e 3D. Dalla disintossicazione fisiologica alla trascendenza nel servizio alla comunità.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/piramide-maslow.php';

$breadcrumbs = [
    'Home' => '/',
    'Strumenti di Crescita' => 'dashboard.php',
    'Piramide di Maslow' => 'piramide-maslow.php'
];

require '_header.php';
?>

<div class="container py-4" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

  <!-- HERO HEADER -->
  <section class="human-hero-card text-center my-3" style="padding: 2.2rem 1.5rem; background: radial-gradient(circle at 50% 0%, rgba(224, 169, 109, 0.15) 0%, rgba(12, 16, 28, 0.95) 75%); border: 1px solid rgba(224, 169, 109, 0.35); border-radius: var(--dx-radius-lg, 20px);">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span style="color:#fde68a;">EVOLUZIONE DEI BISOGNI UMANI NELLA SOBRIETÀ</span>
    </div>

    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4.5vw, 3rem); color: #ffffff; font-weight: 800; margin: 0 0 12px;">
      La Piramide di Maslow <span class="text-rainbow">Hudolin 2D & 3D</span>
    </h1>

    <p style="color: #cbd5e1; max-width: 780px; margin: 0 auto 20px; font-size: 1.02rem; line-height: 1.6;">
      La sobrietà non è solo togliere una sostanza: è scalare i gradoni della propria dignità umana. 
      Dai bisogni fisiologici di base fino alla piena trascendenza nel servizio verso gli altri, scopri su quale gradino ti trovi oggi.
    </p>

    <!-- SWITCH MODALITA 2D / 3D -->
    <div class="d-inline-flex p-1 mb-2" style="background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.15); border-radius: 30px;">
      <button type="button" id="btnMode2D" class="btn small active" style="border-radius: 24px; padding: 6px 18px; font-weight: 700; font-size: 0.85rem;" onclick="setMaslowMode('2d')">
        <?=dx_icon('layers', '', 14)?>
        <span style="margin-left: 6px;">Visualizzazione 2D Interattiva</span>
      </button>
      <button type="button" id="btnMode3D" class="btn small" style="border-radius: 24px; padding: 6px 18px; font-weight: 700; font-size: 0.85rem;" onclick="setMaslowMode('3d')">
        <?=dx_icon('sparkles', '', 14)?>
        <span style="margin-left: 6px;">Piramide 3D Spaziale WebGL</span>
      </button>
    </div>
  </section>

  <!-- CONTENITORE PRINCIPALE: PIRAMIDE + DETTAGLIO LIVELLO -->
  <div class="row g-4 align-items-stretch">
    
    <!-- COLONNA PIRAMIDE VISIVA -->
    <div class="col-lg-7">
      <div class="p-3 p-md-4 h-100 d-flex flex-column" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 18px; min-height: 520px; position: relative;">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <span style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Il Tuo Livello Attuale</span>
            <div id="currentLevelName" style="font-size: 1.4rem; font-weight: 800; color: #fde68a; font-family: var(--font-serif);">Livello 3: Appartenenza & Club</div>
          </div>
          <div class="text-end">
            <span id="levelBadge" class="badge" style="background: rgba(6,182,212,0.15); color: #67e8f9; border: 1px solid rgba(6,182,212,0.3); font-size: 0.82rem; padding: 6px 12px; border-radius: 20px;">
              Gradino 3 di 5
            </span>
          </div>
        </div>

        <!-- VISTA 2D: PIRAMIDE A GRADONI SVG/HTML -->
        <div id="maslowContainer2D" style="flex-grow: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px 0;">
          <div class="maslow-pyramid-stack" style="width: 100%; max-width: 500px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
            
            <!-- LIVELLO 5 -->
            <div class="maslow-tier" data-lvl="5" onclick="selectLevel(5)" style="width: 32%; background: linear-gradient(135deg, rgba(236,72,153,0.35), rgba(236,72,153,0.15)); border: 1px solid #ec4899; color: #fbcfe8; cursor: pointer; padding: 12px; border-radius: 12px; text-align: center; transition: all 0.25s;">
              <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #f472b6;">Livello 5</div>
              <div style="font-size: 0.88rem; font-weight: 800; color: #fff;">Trascendenza & Servizio</div>
            </div>

            <!-- LIVELLO 4 -->
            <div class="maslow-tier" data-lvl="4" onclick="selectLevel(4)" style="width: 48%; background: linear-gradient(135deg, rgba(245,158,11,0.35), rgba(245,158,11,0.15)); border: 1px solid #f59e0b; color: #fde68a; cursor: pointer; padding: 12px; border-radius: 12px; text-align: center; transition: all 0.25s;">
              <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #fbbf24;">Livello 4</div>
              <div style="font-size: 0.88rem; font-weight: 800; color: #fff;">Autostima & Dignità</div>
            </div>

            <!-- LIVELLO 3 -->
            <div class="maslow-tier active" data-lvl="3" onclick="selectLevel(3)" style="width: 64%; background: linear-gradient(135deg, rgba(6,182,212,0.35), rgba(6,182,212,0.15)); border: 2px solid #06b6d4; box-shadow: 0 0 16px rgba(6,182,212,0.4); color: #a5f3fc; cursor: pointer; padding: 12px; border-radius: 12px; text-align: center; transition: all 0.25s;">
              <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #67e8f9;">Livello 3</div>
              <div style="font-size: 0.88rem; font-weight: 800; color: #fff;">Appartenenza & Il Cerchio del Club</div>
            </div>

            <!-- LIVELLO 2 -->
            <div class="maslow-tier" data-lvl="2" onclick="selectLevel(2)" style="width: 80%; background: linear-gradient(135deg, rgba(59,130,246,0.35), rgba(59,130,246,0.15)); border: 1px solid #3b82f6; color: #bfdbfe; cursor: pointer; padding: 12px; border-radius: 12px; text-align: center; transition: all 0.25s;">
              <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #60a5fa;">Livello 2</div>
              <div style="font-size: 0.88rem; font-weight: 800; color: #fff;">Sicurezza & Confini Protetti</div>
            </div>

            <!-- LIVELLO 1 -->
            <div class="maslow-tier" data-lvl="1" onclick="selectLevel(1)" style="width: 96%; background: linear-gradient(135deg, rgba(34,197,94,0.35), rgba(34,197,94,0.15)); border: 1px solid #22c55e; color: #bbf7d0; cursor: pointer; padding: 12px; border-radius: 12px; text-align: center; transition: all 0.25s;">
              <div style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; color: #4ade80;">Livello 1 (La Base)</div>
              <div style="font-size: 0.88rem; font-weight: 800; color: #fff;">Fisiologia & Disintossicazione</div>
            </div>

          </div>
        </div>

        <!-- VISTA 3D: THREE.JS WEBGL PIRAMIDE -->
        <div id="maslowContainer3D" style="flex-grow: 1; display: none; position: relative; min-height: 420px; border-radius: 12px; overflow: hidden; background: radial-gradient(circle at center, #151a2d 0%, #080a10 100%);">
          <div id="maslowWebglCanvas" style="width: 100%; height: 420px;"></div>
          <div style="position: absolute; bottom: 12px; left: 16px; font-size: 0.76rem; color: #94a3b8; pointer-events: none; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('compass', 'text-neon-cyan', 12)?> Trascina con il mouse o touch per ruotare la piramide 3D
          </div>
        </div>

        <!-- FOOTER VISUALE -->
        <div class="pt-3 mt-2 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.82rem; color: #94a3b8;">
          <span><?=dx_icon('info', 'text-neon-cyan', 14)?> Clicca su un gradino per esplorare la scheda pedagogica</span>
          <a href="dashboard.php" class="btn small" style="border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px;">
            <?=dx_icon('arrow-left', '', 12)?> Torna alla Dashboard
          </a>
        </div>
      </div>
    </div>

    <!-- COLONNA SCHEDA DETTAGLIO LIVELLO E MISSIONI -->
    <div class="col-lg-5">
      <div class="p-3 p-md-4 h-100 d-flex flex-column" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 18px;" id="levelCardDetail">
        
        <div class="badge-neon-rainbow mb-2" id="tierBadge">
          <span class="dot"></span>
          <span id="tierSubtitle">LIVELLO 3 · IL CUORE DELLA COMUNITÀ</span>
        </div>

        <h3 id="tierTitle" style="font-size: 1.35rem; color: #ffffff; font-weight: 800; font-family: var(--font-serif); margin-bottom: 10px;">
          Appartenenza & Il Cerchio del Club
        </h3>

        <p id="tierDesc" style="color: #cbd5e1; font-size: 0.94rem; line-height: 1.6; margin-bottom: 16px;">
          Nessuno si libera dalla dipendenza in una stanza vuota. Quando entri al Club, il senso di isolamento crolla. Non sei più un 'caso clinico' o una 'pecora nera': sei un compagno di cammino ascoltato da chi comprende esattamente le tue prove.
        </p>

        <!-- OSTACOLO TIPICO -->
        <div class="p-3 mb-3" style="background: rgba(239,68,68,0.08); border-left: 3px solid #ef4444; border-radius: 0 10px 10px 0;">
          <div style="font-size: 0.78rem; font-weight: 700; color: #fca5a5; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('alert-triangle', '', 14)?> La Trappola Comune su Questo Gradino
          </div>
          <div id="tierTrap" style="font-size: 0.84rem; color: #cbd5e1; line-height: 1.45;">
            Pensare di poter fare tutto da soli dopo poche settimane di astinenza e smettere di frequentare le riunioni.
          </div>
        </div>

        <!-- MISSIONI PER SBLOCCARE QUESTO GRADINO -->
        <div class="mb-3">
          <div style="font-size: 0.85rem; font-weight: 700; color: #fde68a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('check-circle', 'text-neon-gold', 14)?> Missioni di Consolidamento Gradino
          </div>
          <div id="tierQuests" style="display: flex; flex-direction: column; gap: 8px;">
            <!-- Riempito da JS -->
          </div>
        </div>

        <!-- CTA CONVERSIONE -->
        <div class="mt-auto pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
          <div class="d-flex gap-2">
            <a href="mappa-club.php" class="btn-rainbow-neon small w-100" style="justify-content: center;">
              <?=dx_icon('map-pin', '', 14)?>
              <span style="margin-left: 6px;">Cerca il Club per Questo Gradino</span>
            </a>
          </div>
        </div>

      </div>
    </div>

  </div>

  <!-- SEZIONE TEORICA: VLADIMIR HUDOLIN E LA GERARCHIA DEI BISOGNI -->
  <section class="p-4 p-md-5 my-5" style="background: rgba(12, 16, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px;">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span style="color:#fde68a;">PEDAGOGIA DELLA TRASFORMAZIONE</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.5rem, 3vw, 2.2rem); color: #ffffff; font-weight: 800; margin: 6px 0 14px;">
      Come si sale la Piramide: <span class="text-rainbow">la dinamica dei 5 passi</span>
    </h2>
    <p style="color: #cbd5e1; font-size: 0.98rem; line-height: 1.65; max-width: 900px; margin-bottom: 24px;">
      Abraham Maslow postulava che non si possono soddisfare i bisogni superiori se quelli inferiori sono in allarme. 
      Nel Metodo Hudolin questa gerarchia diventa la mappa esatta per non ricadere:
    </p>

    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
          <div style="font-size: 0.75rem; font-weight: 800; color: #22c55e; margin-bottom: 6px;">GRADINO 1 & 2</div>
          <h4 style="color: #fff; font-size: 1rem; margin-bottom: 8px;">Stabilizzare il Corpo e l'Ambiente</h4>
          <p style="color: #94a3b8; font-size: 0.86rem; line-height: 1.5; margin: 0;">
            Nei primi 30 giorni il corpo elimina i veleni. È prioritario dormire, idratarsi, allontanarsi dai bar e dai vecchi compagni di bevuta. Nessuna decisione dirompente: solo protezione biologica.
          </p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
          <div style="font-size: 0.75rem; font-weight: 800; color: #06b6d4; margin-bottom: 6px;">GRADINO 3 & 4</div>
          <h4 style="color: #fff; font-size: 1rem; margin-bottom: 8px;">Riconnettersi e Riconquistare Stima</h4>
          <p style="color: #94a3b8; font-size: 0.86rem; line-height: 1.5; margin: 0;">
            Frequentando il Club ogni settimana, le ferite con i figli e il partner iniziano a rimarginarsi. Non c'è più bisogno di bugie. La parola data riacquista peso e la stima rifiorisce naturalmente.
          </p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
          <div style="font-size: 0.75rem; font-weight: 800; color: #ec4899; margin-bottom: 6px;">GRADINO 5</div>
          <h4 style="color: #fff; font-size: 1rem; margin-bottom: 8px;">Dalla Cura al Dono: Il Servitore</h4>
          <p style="color: #94a3b8; font-size: 0.86rem; line-height: 1.5; margin: 0;">
            Il livello più alto è accorgersi che il tuo dolore passato è diventato la tua risorsa più preziosa per salvare un'altra famiglia. Donare il proprio tempo chiude il cerchio della rinascita.
          </p>
        </div>
      </div>
    </div>
  </section>

</div>

<!-- THREE.JS PER MODALITA 3D -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<script>
const MASLOW_DATA = {
  1: {
    lvl: 1,
    name: 'Fisiologia & Disintossicazione',
    subtitle: 'LIVELLO 1 · LA BASE BIOLOGICA',
    color: '#22c55e',
    desc: 'Il corpo deve eliminare la tossicità. Senza un sonno regolare, idratazione corretta e una nutrizione pulita, la corteccia prefrontale rimane esausta e la mente cede all\'impulso.',
    trap: 'Voler risolvere grandi questioni esistenziali nei primi 7 giorni quando il cervello è ancora in astinenza biochimica.',
    quests: [
      'Bevi 2 litri d\'acqua al giorno ed evita bevande stimolanti eccitanti',
      'Cammina 20 minuti al giorno per ossigenare i tessuti muscolari',
      'Dichiara la tua intenzione serena: oggi scelgo la lucidità'
    ]
  },
  2: {
    lvl: 2,
    name: 'Sicurezza & Confini Protetti',
    subtitle: 'LIVELLO 2 · SPAZIO E TEMPO SICURO',
    color: '#3b82f6',
    desc: 'Creare una bolla di sicurezza attorno a te e alla tua famiglia. Eliminare ogni bottiglia dalla casa, evitare aperitivi lavorativi ad alto rischio e stabilire una routine rassicurante.',
    trap: 'Sfoggiare falsa sicurezza: "Ormai sono forte, posso andare al bar con gli amici senza bere". È la causa principale di ricaduta precoce.',
    quests: [
      'Bonifica la dispensa di casa: via ogni alcolico e calice da vista',
      'Identifica i 3 orari a rischio della giornata e pianifica un\'attività alternativa',
      'Memorizza sul cellulare il numero del servitore o dell\'amico fidato del Club'
    ]
  },
  3: {
    lvl: 3,
    name: 'Appartenenza & Il Cerchio del Club',
    subtitle: 'LIVELLO 3 · IL CUORE DELLA COMUNITÀ',
    color: '#06b6d4',
    desc: 'Nessuno si libera dalla dipendenza in una stanza vuota. Quando entri al Club, il senso di isolamento crolla. Non sei più un \'caso clinico\' o una \'pecora nera\': sei un compagno di cammino ascoltato da chi comprende esattamente le tue prove.',
    trap: 'Pensare di poter fare tutto da soli dopo poche settimane di astinenza e smettere di frequentare le riunioni settimanali.',
    quests: [
      'Partecipa alla riunione settimanale del Club e condividi il tuo stato d\'animo',
      'Coinvolgi un familiare o una persona cara nella partecipazione',
      'Incontra un compagno di cammino per un caffè o una camminata sobria'
    ]
  },
  4: {
    lvl: 4,
    name: 'Autostima & Dignità Ritrovata',
    subtitle: 'LIVELLO 4 · RICONQUISTA DI SE STESSI',
    color: '#f59e0b',
    desc: 'La fine del senso di colpa paralizzante. Si torna a mantenere le promesse fatte ai figli e al partner. Si riprende il controllo del proprio lavoro e delle proprie finanze con fierezza tranquilla.',
    trap: 'L\'orgoglio risentito: pretendere che la famiglia cancelli istantaneamente le paure del passato senza concedere loro il tempo di fidarsi di nuovo.',
    quests: [
      'Chiedi scusa senza giustificazioni per un errore passato e mantieni la coerenza',
      'Dedica una serata di serenità totale e ascolto esclusivo ai tuoi figli o partner',
      'Celebra il tuo traguardo con un acquisto utile finanziato dai soldi non spesi in alcol'
    ]
  },
  5: {
    lvl: 5,
    name: 'Trascendenza & Servizio alla Comunità',
    subtitle: 'LIVELLO 5 · LA PIENA REALIZZAZIONE UMANA',
    color: '#ec4899',
    desc: 'Il dolore superato diventa faro per chi è ancora nella tempesta. Frequentare il corso di sensibilizzazione, diventare servitore-insegnante, donare ascolto compassionevole e contribuire alla crescita della comunità.',
    trap: 'Mettersi in cattedra e dare lezioni invece di continuare a rispecchiarsi con umiltà.',
    quests: [
      'Accogli un nuovo arrivato al Club e offrigli il tuo ascolto senza giudizio',
      'Iscriviti a un modulo formativo del Metodo Hudolin per servitori',
      'Condividi la tua testimonianza anonima sul portale per dare speranza a chi cerca online'
    ]
  }
};

let currentLevel = 3;

function selectLevel(lvl) {
  currentLevel = lvl;
  const d = MASLOW_DATA[lvl];

  // Aggiorna classi bottoni 2D
  document.querySelectorAll('.maslow-tier').forEach(el => {
    const l = parseInt(el.getAttribute('data-lvl'), 10);
    if (l === lvl) {
      el.classList.add('active');
      el.style.borderWidth = '2px';
      el.style.boxShadow = `0 0 16px ${d.color}66`;
    } else {
      el.classList.remove('active');
      el.style.borderWidth = '1px';
      el.style.boxShadow = 'none';
    }
  });

  // Aggiorna card dettaglio
  document.getElementById('currentLevelName').innerText = `Livello ${lvl}: ${d.name}`;
  document.getElementById('levelBadge').innerText = `Gradino ${lvl} di 5`;
  document.getElementById('levelBadge').style.color = d.color;
  document.getElementById('tierSubtitle').innerText = d.subtitle;
  document.getElementById('tierTitle').innerText = d.name;
  document.getElementById('tierDesc').innerText = d.desc;
  document.getElementById('tierTrap').innerText = d.trap;

  const questsBox = document.getElementById('tierQuests');
  questsBox.innerHTML = '';
  d.quests.forEach(q => {
    const item = document.createElement('div');
    item.style.padding = '8px 12px';
    item.style.background = 'rgba(255,255,255,0.03)';
    item.style.border = '1px solid rgba(255,255,255,0.08)';
    item.style.borderRadius = '8px';
    item.style.fontSize = '0.82rem';
    item.style.color = '#cbd5e1';
    item.style.display = 'flex';
    item.style.alignItems = 'center';
    item.style.gap = '8px';
    item.innerHTML = `<span style="width: 6px; height: 6px; border-radius: 50%; background: ${d.color}; flex-shrink: 0;"></span><span>${q}</span>`;
    questsBox.appendChild(item);
  });

  // Aggiorna colore mesh 3D se attivo
  if (maslowScene && pyramidLayers.length > 0) {
    pyramidLayers.forEach((m, idx) => {
      const isCur = (idx + 1) === lvl;
      m.material.emissiveIntensity = isCur ? 0.8 : 0.2;
    });
  }
}

/* ================= THREE.JS PIRAMIDE 3D ================= */
let maslowScene, maslowCamera, maslowRenderer, maslowGroup, pyramidLayers = [];
let mDragging = false, mPrevX = 0, mPrevY = 0;

function initMaslow3D() {
  const container = document.getElementById('maslowWebglCanvas');
  if (!container || maslowScene) return;

  const width = container.clientWidth || 600;
  const height = container.clientHeight || 420;

  maslowScene = new THREE.Scene();
  maslowCamera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
  maslowCamera.position.set(0, 3, 10);
  maslowCamera.lookAt(0, 0, 0);

  maslowRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  maslowRenderer.setSize(width, height);
  maslowRenderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  container.appendChild(maslowRenderer.domElement);

  const ambient = new THREE.AmbientLight(0xffffff, 0.8);
  maslowScene.add(ambient);
  const dirLight1 = new THREE.DirectionalLight(0xffd27a, 1.8);
  dirLight1.position.set(5, 8, 6);
  maslowScene.add(dirLight1);
  const dirLight2 = new THREE.DirectionalLight(0x06b6d4, 1.2);
  dirLight2.position.set(-6, -4, -6);
  maslowScene.add(dirLight2);

  maslowGroup = new THREE.Group();
  maslowScene.add(maslowGroup);

  const colors = [0x22c55e, 0x3b82f6, 0x06b6d4, 0xf59e0b, 0xec4899];
  let currentY = -2.2;
  const layerHeight = 0.85;

  for (let i = 0; i < 5; i++) {
    const bottomR = 3.2 - (i * 0.55);
    const topR = 3.2 - ((i + 1) * 0.55);
    const col = colors[i];

    const geo = new THREE.CylinderGeometry(topR, bottomR, layerHeight, 4, 1);
    const mat = new THREE.MeshStandardMaterial({
      color: col,
      metalness: 0.85,
      roughness: 0.2,
      emissive: col,
      emissiveIntensity: (i + 1 === currentLevel) ? 0.8 : 0.25
    });

    const mesh = new THREE.Mesh(geo, mat);
    mesh.position.y = currentY + layerHeight / 2;
    mesh.rotation.y = Math.PI / 4;
    maslowGroup.add(mesh);
    pyramidLayers.push(mesh);

    // Wireframe luminescente
    const wireGeo = new THREE.EdgesGeometry(geo);
    const wireMat = new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.4 });
    const wire = new THREE.LineSegments(wireGeo, wireMat);
    wire.rotation.y = Math.PI / 4;
    wire.position.y = mesh.position.y;
    maslowGroup.add(wire);

    currentY += layerHeight + 0.06;
  }

  // Eventi Drag rotazione
  container.addEventListener('mousedown', e => { mDragging = true; mPrevX = e.clientX; mPrevY = e.clientY; });
  window.addEventListener('mouseup', () => { mDragging = false; });
  window.addEventListener('mousemove', e => {
    if (!mDragging) return;
    const dx = e.clientX - mPrevX;
    const dy = e.clientY - mPrevY;
    mPrevX = e.clientX;
    mPrevY = e.clientY;
    maslowGroup.rotation.y += dx * 0.01;
    maslowGroup.rotation.x += dy * 0.01;
  });

  // Touch mobile
  container.addEventListener('touchstart', e => {
    if (e.touches.length === 1) {
      mDragging = true;
      mPrevX = e.touches[0].clientX;
      mPrevY = e.touches[0].clientY;
    }
  });
  window.addEventListener('touchend', () => { mDragging = false; });
  window.addEventListener('touchmove', e => {
    if (!mDragging || e.touches.length !== 1) return;
    const dx = e.touches[0].clientX - mPrevX;
    const dy = e.touches[0].clientY - mPrevY;
    mPrevX = e.touches[0].clientX;
    mPrevY = e.touches[0].clientY;
    maslowGroup.rotation.y += dx * 0.01;
    maslowGroup.rotation.x += dy * 0.01;
  });

  function renderLoop() {
    requestAnimationFrame(renderLoop);
    if (!mDragging) {
      maslowGroup.rotation.y += 0.004;
    }
    maslowRenderer.render(maslowScene, maslowCamera);
  }
  renderLoop();
}

function setMaslowMode(mode) {
  const btn2D = document.getElementById('btnMode2D');
  const btn3D = document.getElementById('btnMode3D');
  const c2D = document.getElementById('maslowContainer2D');
  const c3D = document.getElementById('maslowContainer3D');

  if (mode === '3d') {
    btn2D.classList.remove('active');
    btn3D.classList.add('active');
    c2D.style.display = 'none';
    c3D.style.display = 'block';
    if (!maslowScene) {
      initMaslow3D();
    }
  } else {
    btn3D.classList.remove('active');
    btn2D.classList.add('active');
    c3D.style.display = 'none';
    c2D.style.display = 'flex';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  selectLevel(3);
});
</script>

<?php require '_footer.php'; ?>
