<?php
require_once __DIR__ . '/bootstrap.php';
$u = current_user();
$brand = site_brand();

$pageTitle = 'Dashboard Personale & Gamification Metodo Hudolin · DEPENDEX';
$metaDesc = 'La tua dashboard personale per il monitoraggio della sobrietà, missioni quotidiane del Metodo Hudolin, Ruota della Vita e Piramide di Maslow.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/dashboard.php';

$breadcrumbs = [
    'Home' => '/',
    'Dashboard di Crescita' => 'dashboard.php'
];

require '_header.php';
?>

<div class="container py-4" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

  <!-- TOP PROFILE & GAMIFICATION BANNER -->
  <section class="p-4 mb-4" style="background: linear-gradient(135deg, rgba(20,26,45,0.95), rgba(10,13,22,0.98)); border: 1px solid rgba(224, 169, 109, 0.35); border-radius: 20px; box-shadow: var(--rainbow-glow);">
    <div class="row align-items-center g-3">
      <div class="col-md-7 d-flex align-items-center gap-3">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, var(--neon-gold), var(--neon-cyan)); color: #0b0f19; font-weight: 800; font-size: 1.8rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 20px rgba(253,230,138,0.4); flex-shrink: 0;">
          <?=h($u ? mb_substr($u['display_name'], 0, 1) : 'V')?>
        </div>
        <div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <h1 style="font-size: 1.45rem; font-weight: 800; color: #ffffff; margin: 0; font-family: var(--font-serif);">
              Ciao, <?=h($u ? $u['display_name'] : 'Viandante di Speranza')?>
            </h1>
            <span id="rankBadge" class="badge-neon-rainbow" style="font-size: 0.72rem;">
              <span class="dot"></span>
              <span id="rankName">Custode della Famiglia</span>
            </span>
          </div>
          <p style="color: #cbd5e1; font-size: 0.88rem; margin: 4px 0 0;">
            «Un giorno alla volta.» · Percorso di crescita ecologico-sociale Metodo Hudolin
          </p>
        </div>
      </div>

      <div class="col-md-5 text-md-end">
        <div class="d-inline-flex align-items-center gap-3 p-2 px-3" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px;">
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase;">Punti Vitalità (PV)</div>
            <div id="userPvTotal" style="font-size: 1.35rem; font-weight: 800; color: #fde68a; font-family: var(--font-serif);">245 PV</div>
          </div>
          <div style="height: 30px; width: 1px; background: rgba(255,255,255,0.15);"></div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase;">Prossimo Grado</div>
            <div style="font-size: 0.88rem; font-weight: 700; color: #67e8f9;">Compagno (+105 PV)</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTATORE SOBRIETÀ IN PRIMO PIANO -->
  <section class="p-4 p-md-5 mb-4" style="background: radial-gradient(circle at center, rgba(14,20,38,0.95) 0%, rgba(8,11,19,0.98) 100%); border: 2px solid rgba(6, 182, 212, 0.35); border-radius: 20px; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -30px; right: -30px; width: 160px; height: 160px; background: radial-gradient(circle, rgba(6,182,212,0.2) 0%, transparent 70%); pointer-events: none;"></div>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
      <div>
        <div class="badge-neon-rainbow mb-2">
          <span class="dot"></span>
          <span style="color:#fde68a;">SOBRIETY & VITALITY STREAK COUNTER</span>
        </div>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.6rem, 3.5vw, 2.4rem); color: #ffffff; font-weight: 800; margin: 0;">
          I Tuoi Giorni di <span class="text-rainbow">Piena Libertà</span>
        </h2>
        <p style="color: #cbd5e1; font-size: 0.95rem; margin: 4px 0 0;">
          Ogni singolo secondo lucido è una vittoria strappata alla nebbia e donata a chi ti vuole bene.
        </p>
      </div>

      <div>
        <button type="button" class="btn small" style="border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 10px; font-size: 0.82rem;" onclick="toggleDateEdit()">
          <?=dx_icon('calendar', 'text-neon-gold', 14)?>
          <span style="margin-left: 6px;">Modifica Data Inizio</span>
        </button>
      </div>
    </div>

    <!-- FORM SELEZIONE DATA (NASCOSTO DI DEFAULT) -->
    <div id="dateEditBox" class="p-3 mb-4" style="display: none; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-width: 480px;">
      <label style="font-size: 0.82rem; color: #cbd5e1; display: block; margin-bottom: 6px;">Data del tuo Nuovo Inizio</label>
      <div class="d-flex gap-2">
        <input type="date" id="inputStartDate" class="form-control" style="background: rgba(20,26,45,0.9); border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px; font-size: 0.9rem;" max="<?=date('Y-m-d')?>">
        <button type="button" class="btn-rainbow-neon small" onclick="saveStartDate()">Salva</button>
      </div>
    </div>

    <!-- 4 CONTATORI NUMERICI -->
    <div class="row g-3 text-center mb-4">
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(253,230,138,0.25); border-radius: 14px;">
          <div id="streakDays" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #fde68a; font-family: var(--font-serif); line-height: 1;">94</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Giorni Sobri</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(6,182,212,0.25); border-radius: 14px;">
          <div id="streakHours" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #67e8f9; font-family: var(--font-serif); line-height: 1;">2.256</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Ore Lucide</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(34,197,94,0.25); border-radius: 14px;">
          <div id="moneySaved" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #86efac; font-family: var(--font-serif); line-height: 1;">940 €</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Denaro Risparmiato</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(236,72,153,0.25); border-radius: 14px;">
          <div id="drinksAvoided" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #f472b6; font-family: var(--font-serif); line-height: 1;">376</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Bicchieri Evitati</div>
        </div>
      </div>
    </div>

    <!-- PROSSIMA PIETRA MILIARE E PROGRESS BAR -->
    <div class="p-3" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span style="font-size: 0.85rem; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 6px;">
          <?=dx_icon('award', 'text-neon-gold', 16)?>
          <span>Prossimo Traguardo: <strong id="nextMilestoneName">180 Giorni (Chiave d'Oro)</strong></span>
        </span>
        <span id="milestoneDaysLeft" style="font-size: 0.82rem; color: #67e8f9; font-weight: 700;">Mancano 86 giorni</span>
      </div>
      <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
        <div id="milestoneProgress" style="width: 52%; height: 100%; background: linear-gradient(90deg, var(--neon-cyan), var(--neon-gold)); transition: width 0.4s ease;"></div>
      </div>
    </div>

    <!-- CERTIFICATO DI TRAGUARDO SCARICABILE -->
    <div class="d-flex justify-content-end mt-3">
      <button type="button" class="btn-rainbow-outline small" onclick="generateCertificate()">
        <?=dx_icon('printer', 'text-neon-cyan', 14)?>
        <span style="margin-left: 6px;">Genera Certificato di Sobrietà</span>
      </button>
    </div>
  </section>

  <!-- SEZIONE DUE COLONNE: MISSIONI METODO HUDOLIN & STRUMENTI INTERATTIVI -->
  <div class="row g-4 mb-4">
    
    <!-- MISSIONI QUOTIDIANE (GAMIFICATION HUDOLIN) -->
    <div class="col-lg-6">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <div class="badge-neon-rainbow mb-1" style="font-size: 0.72rem;">HUDOLIN QUEST SYSTEM</div>
            <h3 style="font-size: 1.25rem; color: #ffffff; font-weight: 800; font-family: var(--font-serif); margin: 0;">
              Missioni del Giorno
            </h3>
          </div>
          <div style="font-size: 0.82rem; color: #86efac; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); padding: 4px 10px; border-radius: 20px;">
            <?=dx_icon('check-circle', '', 12)?> Reset ogni 24h
          </div>
        </div>

        <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
          Completa le azioni quotidiane ispirate ai 12 passi ecologico-sociali del Metodo per guadagnare Punti Vitalità e rafforzare la tua mente.
        </p>

        <div id="dailyQuestsList" style="display: flex; flex-direction: column; gap: 10px;">
          <!-- Generato dinamicamente via JS -->
        </div>

        <div class="mt-4 p-3" style="background: rgba(253,230,138,0.05); border: 1px solid rgba(253,230,138,0.2); border-radius: 12px; font-size: 0.82rem; color: #cbd5e1;">
          <?=dx_icon('info', 'text-neon-gold', 14)?>
          <span>La perseveranza batte l'intensità. Non serve fare tutto: basta scegliere una sola buona azione oggi.</span>
        </div>
      </div>
    </div>

    <!-- STRUMENTI 2D/3D & COLLEGAMENTI -->
    <div class="col-lg-6">
      <div class="d-flex flex-column gap-4 h-100">
        
        <!-- CARD RUOTA DELLA VITA -->
        <div class="p-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 20px;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge" style="background: rgba(6,182,212,0.12); color: #67e8f9; border: 1px solid rgba(6,182,212,0.3); font-size: 0.72rem; padding: 4px 8px; border-radius: 12px;">
              COACHING ECOLOGICO-SOCIALE
            </span>
            <span style="font-size: 0.8rem; color: #fde68a; font-weight: 700;">12 Aree della Vita</span>
          </div>

          <h3 style="font-size: 1.25rem; font-weight: 800; color: #ffffff; font-family: var(--font-serif); margin-bottom: 8px;">
            La Ruota della Vita 2D & 3D
          </h3>

          <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 14px;">
            Scopri l'equilibrio complessivo tra salute, famiglia, relazioni, finanze e crescita spirituale. Regola i 12 raggi ed esplora il modello interattivo.
          </p>

          <a href="ruota-della-vita.php" class="btn-rainbow-neon small" style="width: 100%; justify-content: center;">
            <?=dx_icon('compass', '', 14)?>
            <span style="margin-left: 6px;">Apri la Ruota della Vita Interattiva</span>
          </a>
        </div>

        <!-- CARD PIRAMIDE DI MASLOW -->
        <div class="p-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 20px;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge" style="background: rgba(245,158,11,0.12); color: #fde68a; border: 1px solid rgba(245,158,11,0.3); font-size: 0.72rem; padding: 4px 8px; border-radius: 12px;">
              GERARCHIA DEI BISOGNI HUDOLIN
            </span>
            <span style="font-size: 0.8rem; color: #67e8f9; font-weight: 700;">5 Livelli di Salita</span>
          </div>

          <h3 style="font-size: 1.25rem; font-weight: 800; color: #ffffff; font-family: var(--font-serif); margin-bottom: 8px;">
            La Piramide di Maslow 2D & 3D
          </h3>

          <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 14px;">
            Dalla stabilizzazione biologica dei primi giorni fino al ruolo di servitore-insegnante per gli altri. Riconosci le trappole e sblocca il livello superiore.
          </p>

          <a href="piramide-maslow.php" class="btn-rainbow-outline small" style="width: 100%; justify-content: center; border-color: var(--neon-gold); color: #fde68a;">
            <?=dx_icon('layers', '', 14)?>
            <span style="margin-left: 6px;">Scala la Piramide Interattiva</span>
          </a>
        </div>

      </div>
    </div>

  </div>

  <!-- SUPPORTO & COLLEGAMENTO RAPIDO AL CLUB -->
  <section class="p-4 p-md-5 my-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px;">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="badge-neon-rainbow mb-2">
          <span class="dot"></span>
          <span style="color:#fde68a;">RETE TERRITORIALE DEI 322+ CLUB</span>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 2.5vw, 1.9rem); color: #ffffff; font-weight: 800; margin: 0 0 8px;">
          La riunione settimanale è il carburante della tua sobrietà
        </h3>
        <p style="color: #cbd5e1; font-size: 0.94rem; line-height: 1.6; margin: 0;">
          Gli strumenti digitali ti aiutano a tenere traccia della tua crescita, ma il cuore del cambiamento avviene nella stanza del Club, guardando negli occhi i compagni di cammino.
        </p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <div class="d-flex flex-column gap-2">
          <a href="mappa-club.php" class="btn-rainbow-neon small" style="justify-content: center;">
            <?=dx_icon('compass', '', 14)?>
            <span style="margin-left: 6px;">Cerca il tuo Club sulla Mappa 2D</span>
          </a>
          <a href="parla-con-noi.php" class="btn small" style="border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 12px; justify-content: center;">
            <?=dx_icon('message-circle', 'text-neon-cyan', 14)?>
            <span style="margin-left: 6px;">Parla con un Servitore</span>
          </a>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
/* ================= LOGICA CONTATORE SOBRIETÀ ================= */
const DEFAULT_START_DAYS = 94; // fallback dimostrativo

function getStartDate() {
  const saved = localStorage.getItem('dx_sobriety_start_date');
  if (saved) return new Date(saved);
  const d = new Date();
  d.setDate(d.getDate() - DEFAULT_START_DAYS);
  return d;
}

function updateStreakDisplay() {
  const start = getStartDate();
  const now = new Date();
  const diffMs = Math.max(0, now - start);

  const diffSec = Math.floor(diffMs / 1000);
  const diffDays = Math.floor(diffSec / 86400);
  const diffHours = Math.floor(diffSec / 3600);

  document.getElementById('streakDays').innerText = diffDays;
  document.getElementById('streakHours').innerText = diffHours.toLocaleString('it-IT');

  // Stime: 10€ al giorno di risparmio alcol zero e 4 bicchieri/porzioni evitate
  const money = diffDays * 10;
  const drinks = diffDays * 4;

  document.getElementById('moneySaved').innerText = money.toLocaleString('it-IT') + ' €';
  document.getElementById('drinksAvoided').innerText = drinks.toLocaleString('it-IT');

  // Calcolo Milestone
  const milestones = [
    { days: 1, name: '1 Giorno (La Scintilla)' },
    { days: 7, name: '7 Giorni (La Settimana di Luce)' },
    { days: 30, name: '30 Giorni (La Chiave di Bronzo)' },
    { days: 90, name: '90 Giorni (La Chiave d\'Argento)' },
    { days: 180, name: '180 Giorni (La Chiave d\'Oro)' },
    { days: 365, name: '1 Anno (La Stella di Cristallo)' },
    { days: 1825, name: '5 Anni (Il Faro della Comunità)' }
  ];

  let nextMs = milestones[milestones.length - 1];
  let prevDays = 0;
  for (let m of milestones) {
    if (m.days > diffDays) {
      nextMs = m;
      break;
    }
    prevDays = m.days;
  }

  const left = Math.max(0, nextMs.days - diffDays);
  document.getElementById('nextMilestoneName').innerText = nextMs.name;
  document.getElementById('milestoneDaysLeft').innerText = left === 0 ? 'Traguardo Raggiunto!' : `Mancano ${left} giorni`;

  const span = nextMs.days - prevDays;
  const progressInSpan = diffDays - prevDays;
  const pct = Math.min(100, Math.max(5, Math.round((progressInSpan / span) * 100)));
  document.getElementById('milestoneProgress').style.width = pct + '%';
}

function toggleDateEdit() {
  const box = document.getElementById('dateEditBox');
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
  const start = getStartDate();
  const yyyy = start.getFullYear();
  const mm = String(start.getMonth() + 1).padStart(2, '0');
  const dd = String(start.getDate()).padStart(2, '0');
  document.getElementById('inputStartDate').value = `${yyyy}-${mm}-${dd}`;
}

function saveStartDate() {
  const val = document.getElementById('inputStartDate').value;
  if (val) {
    localStorage.setItem('dx_sobriety_start_date', val);
    toggleDateEdit();
    updateStreakDisplay();
  }
}

function generateCertificate() {
  const days = document.getElementById('streakDays').innerText;
  const name = "<?=h($u ? $u['display_name'] : 'Membro del Club')?>";
  const w = window.open('', '_blank');
  w.document.write(`
    <!doctype html>
    <html lang="it">
    <head>
      <title>Certificato di Sobrietà · DEPENDEX</title>
      <style>
        body { font-family: 'Cinzel', serif, Georgia; background: #0b0f19; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .cert-card { width: 700px; border: 4px double #fde68a; padding: 40px; text-align: center; background: radial-gradient(circle, #151c2e, #090c14); border-radius: 16px; box-shadow: 0 0 40px rgba(253,230,138,0.3); }
        h1 { font-size: 28px; color: #fde68a; margin-bottom: 8px; letter-spacing: 2px; }
        .sub { font-size: 14px; color: #67e8f9; text-transform: uppercase; margin-bottom: 24px; letter-spacing: 1px; }
        .name { font-size: 32px; font-weight: bold; color: #fff; border-bottom: 2px solid #fde68a; display: inline-block; padding: 0 20px 8px; margin-bottom: 20px; }
        .days { font-size: 56px; font-weight: 800; color: #86efac; margin: 10px 0; }
        p { font-size: 16px; color: #cbd5e1; line-height: 1.6; max-width: 540px; margin: 0 auto 24px; }
        .footer { font-size: 12px; color: #94a3b8; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 16px; display: flex; justify-content: space-between; }
      </style>
    </head>
    <body>
      <div class="cert-card">
        <h1>ATTESTATO DI CORAGGIO E SOBRIETÀ</h1>
        <div class="sub">Metodo Ecologico-Sociale Vladimir Hudolin · DEPENDEX</div>
        <div class="name">${name}</div>
        <div class="days">${days} GIORNI</div>
        <p>Ha scelto di camminare un giorno alla volta nella piena lucidità, testimoniando che la rinascita è possibile e che la vera forza nasce dalla solidarietà tra pari.</p>
        <div class="footer">
          <span>Data: ${new Date().toLocaleDateString('it-IT')}</span>
          <span>Rete dei Club Alcologici Territoriali</span>
        </div>
      </div>
      <script>window.print();<\/script>
    </body>
    </html>
  `);
}

/* ================= MISSIONI QUOTIDIANE GAMIFICATION ================= */
const DAILY_QUESTS = [
  { id: 'q1', text: 'Dichiarazione del Mattino: oggi scelgo la lucidità, un giorno alla volta', pv: 10 },
  { id: 'q2', text: 'Gratitudine Serale: scrivi 3 cose belle vissute oggi senza alcol', pv: 15 },
  { id: 'q3', text: 'Presenza al Club: partecipa alla riunione settimanale o ascolta una testimonianza', pv: 30 },
  { id: 'q4', text: 'Fratellanza: manda un messaggio di incoraggiamento a un compagno del Club', pv: 20 },
  { id: 'q5', text: 'Passeggiata Consapevole: 20 minuti nella natura senza distrazioni', pv: 15 }
];

function getDoneQuests() {
  const saved = localStorage.getItem('dx_done_quests_' + new Date().toDateString());
  return saved ? JSON.parse(saved) : [];
}

function renderQuests() {
  const box = document.getElementById('dailyQuestsList');
  box.innerHTML = '';
  const done = getDoneQuests();

  let totalPv = 245;

  DAILY_QUESTS.forEach(q => {
    const isDone = done.includes(q.id);
    if (isDone) totalPv += q.pv;

    const item = document.createElement('div');
    item.className = 'p-3 d-flex justify-content-between align-items-center gap-3';
    item.style.background = isDone ? 'rgba(34,197,94,0.08)' : 'rgba(255,255,255,0.02)';
    item.style.border = isDone ? '1px solid rgba(34,197,94,0.3)' : '1px solid rgba(255,255,255,0.08)';
    item.style.borderRadius = '12px';
    item.style.cursor = 'pointer';
    item.style.transition = 'all 0.2s';

    item.innerHTML = `
      <div class="d-flex align-items-center gap-3">
        <input type="checkbox" ${isDone ? 'checked' : ''} style="width: 18px; height: 18px; accent-color: #22c55e; pointer-events: none;">
        <div>
          <div style="font-size: 0.88rem; font-weight: 600; color: ${isDone ? '#86efac' : '#fff'}; ${isDone ? 'text-decoration: line-through;' : ''}">${q.text}</div>
          <div style="font-size: 0.74rem; color: #94a3b8;">+${q.pv} Punti Vitalità</div>
        </div>
      </div>
      <div>
        <span class="badge" style="background: rgba(253,230,138,0.1); color: #fde68a; font-size: 0.72rem; border: 1px solid rgba(253,230,138,0.25);">
          +${q.pv} PV
        </span>
      </div>
    `;

    item.addEventListener('click', () => {
      let currentDone = getDoneQuests();
      if (currentDone.includes(q.id)) {
        currentDone = currentDone.filter(x => x !== q.id);
      } else {
        currentDone.push(q.id);
      }
      localStorage.setItem('dx_done_quests_' + new Date().toDateString(), JSON.stringify(currentDone));
      renderQuests();
    });

    box.appendChild(item);
  });

  document.getElementById('userPvTotal').innerText = totalPv + ' PV';
}

document.addEventListener('DOMContentLoaded', function() {
  updateStreakDisplay();
  renderQuests();
  setInterval(updateStreakDisplay, 60000);
});
</script>

<?php require '_footer.php'; ?>
