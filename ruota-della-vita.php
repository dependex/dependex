<?php
require_once __DIR__ . '/bootstrap.php';
$u = current_user();
$brand = site_brand();

$pageTitle = 'Ruota della Vita Interattiva 2D/3D · Strumento di Coaching Metodo Hudolin';
$metaDesc = 'Valuta l\'equilibrio delle 12 aree della tua vita con la Ruota della Vita interattiva in 2D e 3D. Strumento pedagogico ecologico-sociale del Metodo Hudolin.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/ruota-della-vita.php';

$breadcrumbs = [
    'Home' => '/',
    'Strumenti di Crescita' => 'dashboard.php',
    'Ruota della Vita 2D/3D' => 'ruota-della-vita.php'
];

require '_header.php';
?>

<div class="container py-4" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

  <!-- HERO HEADER -->
  <section class="human-hero-card text-center my-3" style="padding: 2.2rem 1.5rem; background: radial-gradient(circle at 50% 0%, rgba(224, 169, 109, 0.15) 0%, rgba(12, 16, 28, 0.95) 75%); border: 1px solid rgba(224, 169, 109, 0.35); border-radius: var(--dx-radius-lg, 20px);">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span style="color:#fde68a;">STRUMENTO DI AUTO-VALUTAZIONE ECOLOGICO-SOCIALE</span>
    </div>

    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4.5vw, 3rem); color: #ffffff; font-weight: 800; margin: 0 0 12px;">
      La Ruota della Vita <span class="text-rainbow">Interattiva 2D & 3D</span>
    </h1>

    <p style="color: #cbd5e1; max-width: 780px; margin: 0 auto 20px; font-size: 1.02rem; line-height: 1.6;">
      Una ruota con raggi diseguali non gira dritta: crea sobbalzi nella vita quotidiana. 
      Valuta con onestà il tuo livello di soddisfazione nelle 12 aree fondamentali. Scopri subito dove sei forte e quale area necessita di cura e sostegno comunitario.
    </p>

    <!-- SWITCH MODALITA 2D / 3D -->
    <div class="d-inline-flex p-1 mb-2" style="background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.15); border-radius: 30px;">
      <button type="button" id="btnMode2D" class="btn small active" style="border-radius: 24px; padding: 6px 18px; font-weight: 700; font-size: 0.85rem;" onclick="setMode('2d')">
        <?=dx_icon('compass', '', 14)?>
        <span style="margin-left: 6px;">Visualizzazione 2D Radar</span>
      </button>
      <button type="button" id="btnMode3D" class="btn small" style="border-radius: 24px; padding: 6px 18px; font-weight: 700; font-size: 0.85rem;" onclick="setMode('3d')">
        <?=dx_icon('sparkles', '', 14)?>
        <span style="margin-left: 6px;">Esperienza 3D Spaziale WebGL</span>
      </button>
    </div>
  </section>

  <!-- CONTENITORE PRINCIPALE: GRAFICO + CONTROLLI -->
  <div class="row g-4 align-items-stretch">
    
    <!-- COLONNA GRAFICO (2D O 3D) -->
    <div class="col-lg-7">
      <div class="p-3 p-md-4 h-100 d-flex flex-column" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 18px; min-height: 520px; position: relative;">
        
        <!-- HEADER GRAFICO & SCORE -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <span style="font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Indice di Equilibrio</span>
            <div id="balanceScore" style="font-size: 1.8rem; font-weight: 800; color: #fde68a; font-family: var(--font-serif);">72%</div>
          </div>
          <div class="text-end">
            <span id="balanceLevel" class="badge" style="background: rgba(34,197,94,0.15); color: #86efac; border: 1px solid rgba(34,197,94,0.3); font-size: 0.82rem; padding: 6px 12px; border-radius: 20px;">
              Armonia in Crescita
            </span>
          </div>
        </div>

        <!-- VISTA 2D: CANVAS RADAR -->
        <div id="container2D" style="flex-grow: 1; display: flex; align-items: center; justify-content: center; position: relative; min-height: 420px;">
          <canvas id="wheelCanvas2D" width="460" height="460" style="max-width: 100%; height: auto;"></canvas>
        </div>

        <!-- VISTA 3D: THREE.JS WEBGL -->
        <div id="container3D" style="flex-grow: 1; display: none; position: relative; min-height: 420px; border-radius: 12px; overflow: hidden; background: radial-gradient(circle at center, #151a2d 0%, #080a10 100%);">
          <div id="webglCanvas" style="width: 100%; height: 420px;"></div>
          <div style="position: absolute; bottom: 12px; left: 16px; font-size: 0.76rem; color: #94a3b8; pointer-events: none; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('compass', 'text-neon-cyan', 12)?> Trascina con il mouse o touch per ruotare a 360° nello spazio
          </div>
        </div>

        <!-- FOOTER GRAFICO -->
        <div class="pt-3 mt-2 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.82rem; color: #94a3b8;">
          <span><?=dx_icon('shield-check', 'text-neon-green', 14)?> Salvataggio locale automatico</span>
          <button type="button" class="btn small" style="border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px;" onclick="resetValues()">
            <?=dx_icon('refresh-cw', '', 12)?> Reimposta Valori
          </button>
        </div>
      </div>
    </div>

    <!-- COLONNA REGOLATORI E AREE (12 RAGGI) -->
    <div class="col-lg-5">
      <div class="p-3 p-md-4 h-100 d-flex flex-column" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 18px;">
        <h3 style="font-size: 1.15rem; color: #ffffff; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('sliders', 'text-neon-gold', 18)?>
          <span>Regola i 12 Raggi (Scala 1 - 10)</span>
        </h3>
        
        <div id="slidersList" style="overflow-y: auto; max-height: 440px; padding-right: 6px;">
          <!-- Generato via JavaScript -->
        </div>

        <div class="mt-auto pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
          <div class="p-3" style="background: rgba(253,230,138,0.06); border: 1px solid rgba(253,230,138,0.2); border-radius: 12px; margin-bottom: 12px;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #fde68a; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
              <?=dx_icon('alert-circle', '', 14)?> Suggerimento Metodo Hudolin
            </div>
            <p id="coachingHint" style="font-size: 0.84rem; color: #cbd5e1; margin: 0; line-height: 1.5;">
              Caricamento analisi...
            </p>
          </div>

          <div class="d-flex gap-2">
            <a href="mappa-club.php" class="btn-rainbow-neon small w-100" style="justify-content: center;">
              <?=dx_icon('users', '', 14)?>
              <span style="margin-left: 6px;">Porta la tua Ruota al Club</span>
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- GUIDA PEDAGOGICA METODO HUDOLIN ALLA RUOTA DELLA VITA -->
  <section class="p-4 p-md-5 my-5" style="background: rgba(12, 16, 28, 0.9); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px;">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span style="color:#fde68a;">FILOSOFIA ECOLOGICO-SOCIALE</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.5rem, 3vw, 2.2rem); color: #ffffff; font-weight: 800; margin: 6px 0 14px;">
      Perché la sobrietà riguarda <span class="text-rainbow">tutte le 12 aree</span>
    </h2>
    <p style="color: #cbd5e1; font-size: 0.98rem; line-height: 1.65; max-width: 900px; margin-bottom: 20px;">
      Nel pensiero del prof. Vladimir Hudolin, la dipendenza non è una malattia isolata nel cervello o nel fegato di una singola persona. È uno stile di vita che si è incagliato, coinvolgendo la famiglia, le amicizie, l'ambiente di lavoro e il senso spirituale dell'esistenza.
    </p>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-cyan); border-radius: 0 12px 12px 0;">
          <h4 style="color: #fff; font-size: 1rem; margin-bottom: 6px;">1. Il Rispecchiamento</h4>
          <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.5; margin: 0;">
            Al Club vedi negli altri le parti della tua ruota che ancora non riesci a guardare. Ascoltare chi ha già rimesso in sesto quell'area ti dona la mappa per fare lo stesso.
          </p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-gold); border-radius: 0 12px 12px 0;">
          <h4 style="color: #fff; font-size: 1rem; margin-bottom: 6px;">2. Senza Giudizio né Voto</h4>
          <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.5; margin: 0;">
            I punteggi della ruota non sono pagelle scolastiche. Sono una fotografia serena del momento presente. Riconoscere un'area a livello 2 o 3 è il primo atto di libertà e coraggio.
          </p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-green); border-radius: 0 12px 12px 0;">
          <h4 style="color: #fff; font-size: 1rem; margin-bottom: 6px;">3. Azioni Concrete Settimanali</h4>
          <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.5; margin: 0;">
            Non si cambiano 12 aree in una notte. Si sceglie una sola micro-azione ogni settimana: una camminata, una telefonata, una serata di ascolto al Club, un abbraccio non scontato.
          </p>
        </div>
      </div>
    </div>
  </section>

</div>

<!-- THREE.JS PER MODALITA 3D -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<script>
const AREAS = [
  { id: 'salute', label: 'Salute & Corpo', color: '#22c55e', desc: 'Energia vitale, sonno rigenerante, nutrizione, disintossicazione.' },
  { id: 'ansia', label: 'Calma & Serenità', color: '#06b6d4', desc: 'Capacità di gestire l\'ansia senza ricorrere ad anestetici.' },
  { id: 'famiglia', label: 'Famiglia & Coppia', color: '#f59e0b', desc: 'Dialogo sincero, rispetto reciproco, affetti, figli protetti.' },
  { id: 'club', label: 'Presenza al Club CAT', color: '#8b5cf6', desc: 'Partecipazione attiva, rispecchiamento empatico, ascolto tra pari.' },
  { id: 'crescita', label: 'Crescita Personale', color: '#ec4899', desc: 'Studio del Metodo, lettura, diario personale di riflessione.' },
  { id: 'lavoro', label: 'Lavoro & Scopo', color: '#3b82f6', desc: 'Stabilità professionale, responsabilità, dignità del fare.' },
  { id: 'casa', label: 'Spazio & Casa Serena', color: '#10b981', desc: 'Ordine, armonia negli spazi di vita, atmosfera accogliente.' },
  { id: 'amicizie', label: 'Amicizie Sobrie', color: '#eab308', desc: 'Rete sociale positiva, compagni di cammino con cui ridere.' },
  { id: 'finanze', label: 'Economia & Risparmio', color: '#14b8a6', desc: 'Gestione sobria del denaro, risparmio alcol-zero accumulato.' },
  { id: 'natura', label: 'Natura & Movimento', color: '#84cc16', desc: 'Cammini, aria pulita, rigenerazione lontano dal cemento.' },
  { id: 'spirito', label: 'Spirito & Valori', color: '#a855f7', desc: 'Gratitudine quotidiana, ricerca di senso profondo, etica.' },
  { id: 'servizio', label: 'Solidarietà & Servizio', color: '#f43f5e', desc: 'Aiuto sincero ai nuovi arrivati, testimonianza donata.' }
];

let values = {};

function initValues() {
  const saved = localStorage.getItem('dx_life_wheel_values');
  if (saved) {
    try {
      values = JSON.parse(saved);
    } catch(e) { values = {}; }
  }
  AREAS.forEach(a => {
    if (typeof values[a.id] === 'undefined') {
      values[a.id] = 6;
    }
  });
}

function saveValues() {
  localStorage.setItem('dx_life_wheel_values', JSON.stringify(values));
}

function renderSliders() {
  const list = document.getElementById('slidersList');
  list.innerHTML = '';

  AREAS.forEach((a, idx) => {
    const val = values[a.id];
    const item = document.createElement('div');
    item.className = 'mb-3 p-2';
    item.style.background = 'rgba(255,255,255,0.02)';
    item.style.border = '1px solid rgba(255,255,255,0.06)';
    item.style.borderRadius = '10px';

    item.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span style="font-size: 0.88rem; font-weight: 700; color: ${a.color}; display: flex; align-items: center; gap: 6px;">
          <span style="width: 8px; height: 8px; border-radius: 50%; background: ${a.color};"></span>
          ${a.label}
        </span>
        <span id="valLabel_${a.id}" style="font-size: 0.95rem; font-weight: 800; color: #fff; font-family: var(--font-serif);">${val}/10</span>
      </div>
      <input type="range" class="form-range" min="1" max="10" step="1" value="${val}" id="slider_${a.id}" style="accent-color: ${a.color}; width: 100%;">
      <div style="font-size: 0.72rem; color: #94a3b8; line-height: 1.3;">${a.desc}</div>
    `;

    list.appendChild(item);

    const input = item.querySelector(`#slider_${a.id}`);
    input.addEventListener('input', function() {
      const n = parseInt(this.value, 10);
      values[a.id] = n;
      document.getElementById(`valLabel_${a.id}`).innerText = n + '/10';
      saveValues();
      updateRadar2D();
      updateMesh3D();
      calculateScore();
    });
  });
}

function calculateScore() {
  let total = 0;
  let minArea = AREAS[0];
  let maxArea = AREAS[0];
  let minVal = 11;
  let maxVal = -1;

  AREAS.forEach(a => {
    const v = values[a.id];
    total += v;
    if (v < minVal) { minVal = v; minArea = a; }
    if (v > maxVal) { maxVal = v; maxArea = a; }
  });

  const percent = Math.round((total / (AREAS.length * 10)) * 100);
  document.getElementById('balanceScore').innerText = percent + '%';

  const lvlBadge = document.getElementById('balanceLevel');
  if (percent >= 80) {
    lvlBadge.innerText = 'Armonia Sovrana';
    lvlBadge.style.color = '#86efac';
    lvlBadge.style.borderColor = 'rgba(34,197,94,0.4)';
  } else if (percent >= 60) {
    lvlBadge.innerText = 'Cammino in Crescita';
    lvlBadge.style.color = '#fde68a';
    lvlBadge.style.borderColor = 'rgba(253,230,138,0.4)';
  } else {
    lvlBadge.innerText = 'Richiede Cura al Club';
    lvlBadge.style.color = '#fca5a5';
    lvlBadge.style.borderColor = 'rgba(239,68,68,0.4)';
  }

  const hint = document.getElementById('coachingHint');
  hint.innerHTML = `Punto di forza: <strong>${maxArea.label} (${maxVal}/10)</strong>. Area che richiede ascolto: <strong>${minArea.label} (${minVal}/10)</strong>. Porta questo spunto al prossimo incontro del Club per condividere con i compagni come riequilibrarla.`;
}

function resetValues() {
  AREAS.forEach(a => { values[a.id] = 6; });
  saveValues();
  renderSliders();
  updateRadar2D();
  updateMesh3D();
  calculateScore();
}

/* ================= 2D RADAR CANVAS ================= */
function updateRadar2D() {
  const canvas = document.getElementById('wheelCanvas2D');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width;
  const H = canvas.height;
  const cx = W / 2;
  const cy = H / 2;
  const maxR = (W / 2) - 45;
  const N = AREAS.length;

  ctx.clearRect(0, 0, W, H);

  // Cerchi concentrici di riferimento
  for (let lvl = 1; lvl <= 5; lvl++) {
    const r = (maxR / 5) * lvl;
    ctx.beginPath();
    ctx.arc(cx, cy, r, 0, Math.PI * 2);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
    ctx.lineWidth = 1;
    ctx.stroke();
  }

  // Raggi divisori
  for (let i = 0; i < N; i++) {
    const angle = (Math.PI * 2 / N) * i - (Math.PI / 2);
    const x = cx + maxR * Math.cos(angle);
    const y = cy + maxR * Math.sin(angle);

    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.lineTo(x, y);
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.12)';
    ctx.lineWidth = 1;
    ctx.stroke();

    // Etichette esterne
    const tx = cx + (maxR + 25) * Math.cos(angle);
    const ty = cy + (maxR + 25) * Math.sin(angle);
    ctx.fillStyle = AREAS[i].color;
    ctx.font = 'bold 10px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(AREAS[i].label.split(' ')[0], tx, ty);
  }

  // Spicchi colorati sfumati
  for (let i = 0; i < N; i++) {
    const a1 = (Math.PI * 2 / N) * i - (Math.PI / 2);
    const a2 = (Math.PI * 2 / N) * (i + 1) - (Math.PI / 2);
    const v = values[AREAS[i].id];
    const r = (maxR / 10) * v;

    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, a1, a2);
    ctx.closePath();

    ctx.fillStyle = AREAS[i].color + '44'; // trasparenza esadecimale
    ctx.fill();
    ctx.strokeStyle = AREAS[i].color;
    ctx.lineWidth = 2;
    ctx.stroke();
  }

  // Centro luminoso
  ctx.beginPath();
  ctx.arc(cx, cy, 6, 0, Math.PI * 2);
  ctx.fillStyle = '#fde68a';
  ctx.shadowColor = '#f59e0b';
  ctx.shadowBlur = 12;
  ctx.fill();
  ctx.shadowBlur = 0;
}

/* ================= 3D THREE.JS WEBGL ================= */
let scene, camera, renderer, wheelGroup, meshes = [];
let isDragging = false, prevMouseX = 0, prevMouseY = 0;

function init3D() {
  const container = document.getElementById('webglCanvas');
  if (!container || scene) return;

  const width = container.clientWidth || 600;
  const height = container.clientHeight || 420;

  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
  camera.position.set(0, 3.5, 9);
  camera.lookAt(0, 0, 0);

  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  renderer.setSize(width, height);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  container.appendChild(renderer.domElement);

  // Luci
  const ambient = new THREE.AmbientLight(0xffffff, 0.9);
  scene.add(ambient);
  const dirLight1 = new THREE.DirectionalLight(0xffd27a, 1.8);
  dirLight1.position.set(5, 10, 7);
  scene.add(dirLight1);
  const dirLight2 = new THREE.DirectionalLight(0x06b6d4, 1.2);
  dirLight2.position.set(-5, -6, -5);
  scene.add(dirLight2);

  // Particelle di polvere stellare
  const pGeo = new THREE.BufferGeometry();
  const pCount = 300;
  const pCoords = new Float32Array(pCount * 3);
  for (let i = 0; i < pCount * 3; i++) {
    pCoords[i] = (Math.random() - 0.5) * 30;
  }
  pGeo.setAttribute('position', new THREE.BufferAttribute(pCoords, 3));
  const pMat = new THREE.PointsMaterial({ size: 0.08, color: 0xfde68a, transparent: true, opacity: 0.7 });
  const starField = new THREE.Points(pGeo, pMat);
  scene.add(starField);

  // Gruppo Ruota
  wheelGroup = new THREE.Group();
  wheelGroup.rotation.x = 0.55;
  scene.add(wheelGroup);

  const N = AREAS.length;
  const angleStep = (Math.PI * 2) / N;
  const gap = 0.04;

  AREAS.forEach((a, i) => {
    const colorHex = parseInt(a.color.replace('#', ''), 16);
    const startAngle = i * angleStep + gap / 2;
    const sweepAngle = angleStep - gap;

    // Cilindro a spicchio
    const geo = new THREE.CylinderGeometry(3.2, 3.2, 0.4, 16, 1, false, startAngle, sweepAngle);
    const mat = new THREE.MeshStandardMaterial({
      color: colorHex,
      metalness: 0.8,
      roughness: 0.25,
      emissive: colorHex,
      emissiveIntensity: 0.15
    });
    const mesh = new THREE.Mesh(geo, mat);
    mesh.rotation.x = Math.PI / 2;
    wheelGroup.add(mesh);
    meshes.push(mesh);
  });

  // Mozzo Centrale
  const hubGeo = new THREE.CylinderGeometry(0.8, 0.8, 0.6, 32);
  const hubMat = new THREE.MeshStandardMaterial({
    color: 0x1a2035,
    metalness: 0.9,
    roughness: 0.2,
    emissive: 0xfde68a,
    emissiveIntensity: 0.3
  });
  const hub = new THREE.Mesh(hubGeo, hubMat);
  hub.rotation.x = Math.PI / 2;
  wheelGroup.add(hub);

  updateMesh3D();

  // Eventi Drag Rotazione
  container.addEventListener('mousedown', e => { isDragging = true; prevMouseX = e.clientX; prevMouseY = e.clientY; });
  window.addEventListener('mouseup', () => { isDragging = false; });
  window.addEventListener('mousemove', e => {
    if (!isDragging) return;
    const dx = e.clientX - prevMouseX;
    const dy = e.clientY - prevMouseY;
    prevMouseX = e.clientX;
    prevMouseY = e.clientY;
    wheelGroup.rotation.y += dx * 0.01;
    wheelGroup.rotation.x += dy * 0.01;
  });

  // Touch per mobile
  container.addEventListener('touchstart', e => {
    if (e.touches.length === 1) {
      isDragging = true;
      prevMouseX = e.touches[0].clientX;
      prevMouseY = e.touches[0].clientY;
    }
  });
  window.addEventListener('touchend', () => { isDragging = false; });
  window.addEventListener('touchmove', e => {
    if (!isDragging || e.touches.length !== 1) return;
    const dx = e.touches[0].clientX - prevMouseX;
    const dy = e.touches[0].clientY - prevMouseY;
    prevMouseX = e.touches[0].clientX;
    prevMouseY = e.touches[0].clientY;
    wheelGroup.rotation.y += dx * 0.01;
    wheelGroup.rotation.x += dy * 0.01;
  });

  // Render Loop
  function animate() {
    requestAnimationFrame(animate);
    if (!isDragging) {
      wheelGroup.rotation.z += 0.003;
    }
    renderer.render(scene, camera);
  }
  animate();
}

function updateMesh3D() {
  if (!meshes || meshes.length === 0) return;
  AREAS.forEach((a, i) => {
    const val = values[a.id];
    const scaleFactor = 0.2 + (val / 10) * 0.8;
    meshes[i].scale.set(scaleFactor, 0.4 + (val / 10) * 1.2, scaleFactor);
  });
}

function setMode(mode) {
  const btn2D = document.getElementById('btnMode2D');
  const btn3D = document.getElementById('btnMode3D');
  const c2D = document.getElementById('container2D');
  const c3D = document.getElementById('container3D');

  if (mode === '3d') {
    btn2D.classList.remove('active');
    btn3D.classList.add('active');
    c2D.style.display = 'none';
    c3D.style.display = 'block';
    if (!scene) {
      init3D();
    } else {
      updateMesh3D();
    }
  } else {
    btn3D.classList.remove('active');
    btn2D.classList.add('active');
    c3D.style.display = 'none';
    c2D.style.display = 'flex';
    updateRadar2D();
  }
}

document.addEventListener('DOMContentLoaded', function() {
  initValues();
  renderSliders();
  updateRadar2D();
  calculateScore();
});
</script>

<?php require '_footer.php'; ?>
