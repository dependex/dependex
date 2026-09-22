<?php
/**
 * dashboard.php — Dashboard Personale, The Dependex Compass & Small Steps Engine
 * Percorso di crescita ecologico-sociale Metodo Vladimir Hudolin · DEPENDEX
 * Conforme alle specifiche Human Welfare OS 4.0 / 5.0 e Gamification 6.0
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$u = current_user();
$brand = site_brand();
$db = db();

$pageTitle = 'Dashboard Personale & The Dependex Compass · DEPENDEX';
$metaDesc = 'La tua dashboard personale per il cammino di sobrietà: The Dependex Compass a 9 raggi, Small Steps Engine e presidio territoriale del Club Hudolin.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/dashboard.php';

$breadcrumbs = [
    'Home' => '/',
    'Dashboard di Crescita' => 'dashboard.php'
];

$msg = null;
$msgType = 'info';

// Gestione Azioni Utente Autenticato (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrf_check();

    // 1. Aggiornamento Data Inizio Sobrietà
    if ($_POST['action'] === 'update_sobriety_date' && $u) {
        $newDate = trim($_POST['start_date'] ?? '');
        if ($newDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate)) {
            $stmt = $db->prepare("UPDATE users SET sobriety_start_date = ? WHERE sic_id = ?");
            $stmt->execute([$newDate, $u['sic_id']]);
            $u['sobriety_start_date'] = $newDate;
            $msg = "Data del tuo cammino aggiornata con successo: " . date('d/m/Y', strtotime($newDate));
            $msgType = 'success';
        }
    }

    // 2. Registrazione Riflessione Diario di Bordo ("Come ti senti oggi?")
    if ($_POST['action'] === 'save_journal_entry' && $u) {
        $note = trim($_POST['journal_note'] ?? '');
        $mood = max(1, min(5, (int)($_POST['mood'] ?? 3)));
        if ($note !== '') {
            $jSic = sic_id();
            $today = date('Y-m-d');
            $stmt = $db->prepare("
                INSERT INTO journal_entries (sic_id, user_sic_id, entry_date, mood, note, visibility)
                VALUES (?, ?, ?, ?, ?, 'PRIVATE')
            ");
            $stmt->execute([$jSic, $u['sic_id'], $today, $mood, $note]);

            // Accredito Maieutico Punti Vitalità per l'ascolto di sé (approccio maieutico puro, nessun giudizio)
            drx_post($u['sic_id'], null, 15, 'JOURNAL_ENTRY', true, 'journal:' . $u['sic_id'] . ':' . $today . ':' . time(), $jSic, [
                'type' => 'maieutic_reflection'
            ]);

            $msg = "Riflessione custodita nel tuo diario personale. Grazie per aver dedicato questo momento a te stesso.";
            $msgType = 'success';
        }
    }

    // 3. Completamento Micro-Azione (Small Steps Engine)
    if ($_POST['action'] === 'complete_small_step' && $u) {
        $stepCode = trim($_POST['step_code'] ?? '');
        $pv = max(5, min(30, (int)($_POST['step_pv'] ?? 10)));
        $today = date('Y-m-d');
        $idempKey = 'smallstep:' . $u['sic_id'] . ':' . $stepCode . ':' . $today;

        $res = drx_post($u['sic_id'], null, $pv, 'SMALL_STEP', true, $idempKey, null, [
            'step' => $stepCode,
            'date' => $today
        ]);

        if (!empty($res['duplicate'])) {
            $msg = "Hai già completato questo piccolo passo oggi! Ogni passo conta per costruire abitudini durature.";
            $msgType = 'info';
        } else {
            $msg = "Piccolo passo compiuto! Hai arricchito la tua giornata di +{$pv} Punti Vitalità.";
            $msgType = 'success';
            // Ricarica saldo utente
            $u['drx_balance'] = (float)$db->query("SELECT drx_balance FROM users WHERE sic_id=" . $db->quote($u['sic_id']))->fetchColumn();
        }
    }
}

// Calcolo Giorni Sobri se presenti a DB o fallback dimostrativo
$userStartDate = $u['sobriety_start_date'] ?? null;
$userRank = $u['rank_name'] ?? 'SEME';
$userBalance = (float)($u['drx_balance'] ?? 245.0);

// Club Territoriale di Riferimento o Campione (es. Taglio di Po / Rovigo)
$featuredClub = $db->query("SELECT * FROM crm_club_contacts WHERE sic_id = 'SIC-TAGLIODIPO-RO-001' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$featuredClub) {
    $featuredClub = $db->query("SELECT * FROM crm_club_contacts LIMIT 1")->fetch(PDO::FETCH_ASSOC);
}

// Ultime Riflessioni Diario Utente
$userJournals = [];
if ($u) {
    $stmt = $db->prepare("SELECT * FROM journal_entries WHERE user_sic_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$u['sic_id']]);
    $userJournals = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require '_header.php';
?>

<div class="container py-4" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

  <!-- TOP PROFILE & WELFARE BANNER -->
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
            <span class="badge-neon-rainbow" style="font-size: 0.72rem;">
              <span class="dot"></span>
              <span><?=h($userRank)?></span>
            </span>
          </div>
          <p style="color: #cbd5e1; font-size: 0.88rem; margin: 4px 0 0;">
            «Un giorno alla volta.» · Percorso di crescita ecologico-sociale Metodo Vladimir Hudolin
          </p>
        </div>
      </div>

      <div class="col-md-5 text-md-end">
        <div class="d-inline-flex align-items-center gap-3 p-2 px-3" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px;">
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase;">Punti Vitalità (PV)</div>
            <div id="userPvTotal" style="font-size: 1.35rem; font-weight: 800; color: #fde68a; font-family: var(--font-serif);">
              <?=number_format($userBalance, 0, ',', '.')?> PV
            </div>
          </div>
          <div style="height: 30px; width: 1px; background: rgba(255,255,255,0.15);"></div>
          <div>
            <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase;">Approccio Maieutico</div>
            <div style="font-size: 0.88rem; font-weight: 700; color: #67e8f9;">Nessun Punteggio Punitivo</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if ($msg): ?>
    <div class="p-3 mb-4 d-flex align-items-center gap-3" style="background: <?=$msgType==='success'?'rgba(34,197,94,0.15)':'rgba(6,182,212,0.15)'?>; border: 1px solid <?=$msgType==='success'?'#22c55e':'#06b6d4'?>; border-radius: 12px; color: #ffffff;">
      <?=dx_icon($msgType==='success'?'check-circle':'info', $msgType==='success'?'text-success':'text-neon-cyan', 20)?>
      <div style="font-size: 0.92rem; font-weight: 600;"><?=h($msg)?></div>
    </div>
  <?php endif; ?>

  <!-- CONTATORE SOBRIETÀ "UN GIORNO ALLA VOLTA" (SENZA STREAK PUNITIVI) -->
  <section class="p-4 p-md-5 mb-4" style="background: radial-gradient(circle at center, rgba(14,20,38,0.95) 0%, rgba(8,11,19,0.98) 100%); border: 2px solid rgba(6, 182, 212, 0.35); border-radius: 20px; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -30px; right: -30px; width: 160px; height: 160px; background: radial-gradient(circle, rgba(6,182,212,0.2) 0%, transparent 70%); pointer-events: none;"></div>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
      <div>
        <div class="badge-neon-rainbow mb-2">
          <span class="dot"></span>
          <span style="color:#fde68a;">UN GIORNO ALLA VOLTA · METODO HUDOLIN</span>
        </div>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.6rem, 3.5vw, 2.4rem); color: #ffffff; font-weight: 800; margin: 0;">
          I Tuoi Giorni di <span class="text-rainbow">Piena Lucidità</span>
        </h2>
        <p style="color: #cbd5e1; font-size: 0.95rem; margin: 4px 0 0;">
          Ogni giorno lucido è un dono alla propria vita e alla propria famiglia. Se ti capita di inciampare, la porta del Club è sempre aperta.
        </p>
      </div>

      <div>
        <button type="button" class="btn small" style="border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 10px; font-size: 0.82rem;" onclick="toggleDateEdit()">
          <?=dx_icon('calendar', 'text-neon-gold', 14)?>
          <span style="margin-left: 6px;">Modifica Data Inizio</span>
        </button>
      </div>
    </div>

    <!-- FORM SELEZIONE DATA -->
    <div id="dateEditBox" class="p-3 mb-4" style="display: none; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; max-width: 480px;">
      <form method="post">
        <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
        <input type="hidden" name="action" value="update_sobriety_date">
        <label style="font-size: 0.82rem; color: #cbd5e1; display: block; margin-bottom: 6px;">Data del tuo Nuovo Inizio</label>
        <div class="d-flex gap-2">
          <input type="date" name="start_date" id="inputStartDate" class="form-control" style="background: rgba(20,26,45,0.9); border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px; font-size: 0.9rem;" max="<?=date('Y-m-d')?>" value="<?=h($userStartDate ?: date('Y-m-d', strtotime('-94 days')))?>">
          <button type="submit" class="btn-rainbow-neon small" onclick="saveStartDateLocal()">Salva</button>
        </div>
      </form>
    </div>

    <!-- 4 CONTATORI NUMERICI -->
    <div class="row g-3 text-center mb-4">
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(253,230,138,0.25); border-radius: 14px;">
          <div id="streakDays" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #fde68a; font-family: var(--font-serif); line-height: 1;">--</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Giorni Sobri</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(6,182,212,0.25); border-radius: 14px;">
          <div id="streakHours" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #67e8f9; font-family: var(--font-serif); line-height: 1;">--</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Ore Lucide</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(34,197,94,0.25); border-radius: 14px;">
          <div id="moneySaved" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #86efac; font-family: var(--font-serif); line-height: 1;">-- €</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Denaro Risparmiato</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(236,72,153,0.25); border-radius: 14px;">
          <div id="drinksAvoided" style="font-size: clamp(2.2rem, 5vw, 3.2rem); font-weight: 800; color: #f472b6; font-family: var(--font-serif); line-height: 1;">--</div>
          <div style="font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; margin-top: 6px; font-weight: 700;">Bicchieri Evitati</div>
        </div>
      </div>
    </div>

    <!-- PROSSIMA PIETRA MILIARE E PROGRESS BAR -->
    <div class="p-3" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <span style="font-size: 0.85rem; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 6px;">
          <?=dx_icon('award', 'text-neon-gold', 16)?>
          <span>Prossimo Traguardo: <strong id="nextMilestoneName">...</strong></span>
        </span>
        <span id="milestoneDaysLeft" style="font-size: 0.82rem; color: #67e8f9; font-weight: 700;">Calcolo in corso...</span>
      </div>
      <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
        <div id="milestoneProgress" style="width: 0%; height: 100%; background: linear-gradient(90deg, var(--neon-cyan), var(--neon-gold)); transition: width 0.4s ease;"></div>
      </div>
    </div>

    <!-- CERTIFICATO DI TRAGUARDO SCARICABILE -->
    <div class="d-flex justify-content-end mt-3">
      <button type="button" class="btn-rainbow-outline small" onclick="generateCertificate()">
        <?=dx_icon('printer', 'text-neon-cyan', 14)?>
        <span style="margin-left: 6px;">Genera Attestato di Sobrietà</span>
      </button>
    </div>
  </section>

  <!-- ======================================================== -->
  <!-- SEZIONE FONDAMENTALE: THE DEPENDEX COMPASS (9 RAGGI)      -->
  <!-- ======================================================== -->
  <section class="p-4 p-md-5 mb-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.35); border-radius: 20px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <div class="badge-neon-rainbow mb-2">
          <span class="dot"></span>
          <span style="color:#fde68a;">THE DEPENDEX COMPASS · 9 RAGGI MAIEUTICI</span>
        </div>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 2.8vw, 2rem); color: #ffffff; font-weight: 800; margin: 0 0 6px;">
          La Bussola di Orientamento alla Vita
        </h2>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.5; margin: 0; max-width: 800px;">
          Al centro c'è il tuo <strong>IO</strong>. Attorno a te, 9 raggi che compongono l'esistenza. 
          Tocca un raggio per ascoltare la domanda guida maieutica. <em>Nessun voto, nessun punteggio clinico: solo consapevolezza.</em>
        </p>
      </div>
      <div>
        <span style="font-size: 0.78rem; color: #94a3b8; background: rgba(255,255,255,0.05); padding: 6px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
          Art. 8 Omni-Welfare Engine
        </span>
      </div>
    </div>

    <!-- GRIGLIA INTERATTIVA DELLA BUSSOLA A 9 RAGGI -->
    <div class="row g-3 mb-4">
      <?php
      $compassRays = [
        ['id' => 'corpo', 'name' => '1. Corpo', 'sub' => 'Vitalità, sonno, respiro', 'icon' => 'activity', 'color' => '#86efac', 'question' => 'Come sta riposando il tuo corpo in questi giorni? Hai donato a te stesso una passeggiata all’aria aperta?'],
        ['id' => 'mente', 'name' => '2. Mente', 'sub' => 'Lucidità, quiete interiore', 'icon' => 'smile', 'color' => '#67e8f9', 'question' => 'Quale pensiero ha occupato la tua mente oggi? Riesci a osservarlo senza giudicarti?'],
        ['id' => 'relazioni', 'name' => '3. Relazioni', 'sub' => 'Amicizia, ascolto, confini', 'icon' => 'heart', 'color' => '#f472b6', 'question' => 'C’è una persona cara a cui senti di voler dire grazie o con cui vorresti condividere dieci minuti sinceri?'],
        ['id' => 'famiglia', 'name' => '4. Famiglia', 'sub' => 'Riconciliazione e dialogo', 'icon' => 'home', 'color' => '#fde68a', 'question' => 'Quale piccolo gesto di cura puoi compiere oggi all’interno della tua casa?'],
        ['id' => 'lavoro', 'name' => '5. Lavoro', 'sub' => 'Dignità e cooperazione', 'icon' => 'briefcase', 'color' => '#93c5fd', 'question' => 'Il tuo tempo lavorativo rispetta la tua salute ed equilibrio o senti il bisogno di rallentare il ritmo?'],
        ['id' => 'risorse', 'name' => '6. Risorse', 'sub' => 'Semplicità e indipendenza', 'icon' => 'credit-card', 'color' => '#c084fc', 'question' => 'Come ha giovato la sobrietà al tuo bilancio personale e alla serenità economica?'],
        ['id' => 'comunita', 'name' => '7. Comunità', 'sub' => 'Club CAT e mutuo aiuto', 'icon' => 'users', 'color' => '#38ef7d', 'question' => 'La riunione settimanale al Club ti attende: quale parola puoi portare o ascoltare dai tuoi compagni?'],
        ['id' => 'significato', 'name' => '8. Significato', 'sub' => 'Valori, scopo, speranza', 'icon' => 'sun', 'color' => '#fbbf24', 'question' => 'Cosa dà senso profondo a questa tua giornata sobria?'],
        ['id' => 'territorio', 'name' => '9. Territorio', 'sub' => 'Natura e Delta del Po', 'icon' => 'compass', 'color' => '#2dd4bf', 'question' => 'Ti sei fermato un istante a guardare il cielo, il fiume, gli alberi o il paesaggio che ti circonda?']
      ];
      foreach ($compassRays as $ray):
      ?>
        <div class="col-6 col-md-4">
          <div class="p-3 h-100 compass-ray-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; cursor: pointer; transition: all 0.2s;" onclick="openCompassRay('<?=h($ray['id'])?>', '<?=h($ray['name'])?>', '<?=h(addslashes($ray['question']))?>', '<?=$ray['color']?>')">
            <div class="d-flex align-items-center gap-2 mb-1">
              <?=dx_icon($ray['icon'], '', 16, ['style' => 'color:' . $ray['color']])?>
              <div style="font-weight: 700; font-size: 0.88rem; color: #ffffff;"><?=h($ray['name'])?></div>
            </div>
            <div style="font-size: 0.74rem; color: #94a3b8;"><?=h($ray['sub'])?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- BOX MAIEUTICO RISPOSTA AL RAGGIO SELEZIONATO -->
    <div id="compassQuestionBox" class="p-4" style="display: none; background: rgba(0,0,0,0.45); border: 1px solid rgba(253,230,138,0.3); border-radius: 16px;">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h4 id="compassTitle" style="font-size: 1.15rem; font-weight: 800; color: #fde68a; margin: 0; font-family: var(--font-serif);"></h4>
        <button type="button" class="btn btn-sm text-secondary" onclick="document.getElementById('compassQuestionBox').style.display='none';">&times;</button>
      </div>
      <p id="compassQuestionText" style="font-size: 0.95rem; color: #cbd5e1; line-height: 1.6; margin-bottom: 12px; font-style: italic;"></p>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2" style="border-top: 1px solid rgba(255,255,255,0.08);">
        <div style="font-size: 0.8rem; color: #67e8f9;">
          «Questa parte della mia vita oggi chiede attenzione. Partiamo da lì.»
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <a id="compassDeepenLink" href="orientamento.php" class="btn-rainbow-outline small" style="font-size: 0.78rem;">
            Approfondisci nell'Orientamento &rarr;
          </a>
          <a href="playground.php" class="btn small" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; font-size: 0.78rem;">
            Life Playground &rarr;
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ======================================================== -->
  <!-- SMALL STEPS ENGINE (30s, 2m, 5m) & DIARIO DI BORDO       -->
  <!-- ======================================================== -->
  <div class="row g-4 mb-4">

    <!-- SMALL STEPS ENGINE (SCREEN OFF -> LIFE ON) -->
    <div class="col-lg-6">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <div class="badge-neon-rainbow mb-1" style="font-size: 0.72rem;">SMALL STEPS ENGINE · MAX 3 AZIONI</div>
            <h3 style="font-size: 1.25rem; color: #ffffff; font-weight: 800; font-family: var(--font-serif); margin: 0;">
              Micro-Passi del Giorno
            </h3>
          </div>
          <div style="font-size: 0.78rem; color: #86efac; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); padding: 4px 10px; border-radius: 20px;">
            Screen Off → Life On
          </div>
        </div>

        <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
          Nessuna pressione, nessun obbligo. Scegli se compiere uno di questi piccoli gesti di vita reale oggi:
        </p>

        <div id="dailyQuestsList" class="d-flex flex-column gap-3 mb-3">
          
          <!-- PASSO 1: 30 SECONDI -->
          <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span style="font-size: 0.76rem; font-weight: 700; color: #67e8f9; background: rgba(6,182,212,0.15); padding: 2px 8px; border-radius: 8px;">
                ⏱ 30 SECONDI · RESPIRO
              </span>
              <span style="font-size: 0.75rem; color: #fde68a; font-weight: 700;">+10 PV</span>
            </div>
            <div style="font-size: 0.88rem; color: #ffffff; font-weight: 600; margin-bottom: 6px;">
              Tre respiri profondi a occhi chiusi
            </div>
            <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.4; margin-bottom: 10px;">
              Senti il contatto dei piedi con la terra, rilassa le spalle e ringrazia te stesso per la lucidità di oggi.
            </div>
            <form method="post">
              <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
              <input type="hidden" name="action" value="complete_small_step">
              <input type="hidden" name="step_code" value="step_30s_respiro">
              <input type="hidden" name="step_pv" value="10">
              <button type="submit" class="btn-rainbow-outline small w-100" style="font-size: 0.78rem; justify-content: center;">
                <?=dx_icon('check', '', 12)?> Fatto con consapevolezza
              </button>
            </form>
          </div>

          <!-- PASSO 2: 2 MINUTI -->
          <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span style="font-size: 0.76rem; font-weight: 700; color: #f472b6; background: rgba(236,72,153,0.15); padding: 2px 8px; border-radius: 8px;">
                ⏱ 2 MINUTI · VICINANZA
              </span>
              <span style="font-size: 0.75rem; color: #fde68a; font-weight: 700;">+15 PV</span>
            </div>
            <div style="font-size: 0.88rem; color: #ffffff; font-weight: 600; margin-bottom: 6px;">
              Un messaggio a un compagno o persona cara
            </div>
            <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.4; margin-bottom: 10px;">
              Scrivi due righe di affetto a chi ti sostiene o a un membro del Club: «Pensavo a te, un caro saluto».
            </div>
            <form method="post">
              <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
              <input type="hidden" name="action" value="complete_small_step">
              <input type="hidden" name="step_code" value="step_2m_messaggio">
              <input type="hidden" name="step_pv" value="15">
              <button type="submit" class="btn-rainbow-outline small w-100" style="font-size: 0.78rem; justify-content: center;">
                <?=dx_icon('check', '', 12)?> Ho mandato il messaggio
              </button>
            </form>
          </div>

          <!-- PASSO 3: 5 MINUTI -->
          <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span style="font-size: 0.76rem; font-weight: 700; color: #38ef7d; background: rgba(34,197,94,0.15); padding: 2px 8px; border-radius: 8px;">
                ⏱ 5 MINUTI · VITA REALE
              </span>
              <span style="font-size: 0.75rem; color: #fde68a; font-weight: 700;">+20 PV</span>
            </div>
            <div style="font-size: 0.88rem; color: #ffffff; font-weight: 600; margin-bottom: 6px;">
              Passeggiata senza telefono all’aria aperta
            </div>
            <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.4; margin-bottom: 10px;">
              Esci per 5 minuti, guarda gli alberi, la via, il cielo. Respira la libertà dell’aria pulita.
            </div>
            <form method="post">
              <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
              <input type="hidden" name="action" value="complete_small_step">
              <input type="hidden" name="step_code" value="step_5m_camminata">
              <input type="hidden" name="step_pv" value="20">
              <button type="submit" class="btn-rainbow-outline small w-100" style="font-size: 0.78rem; justify-content: center;">
                <?=dx_icon('check', '', 12)?> Ho camminato nel mondo reale
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>

    <!-- DIARIO DI BORDO MAIEUTICO ("COME TI SENTI OGGI?") -->
    <div class="col-lg-6">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <span class="badge" style="background: rgba(224,169,109,0.15); color: #fde68a; border: 1px solid rgba(224,169,109,0.3); font-size: 0.72rem; padding: 4px 8px; border-radius: 12px;">
                SPAZIO PRIVATO DI RIFLESSIONE
              </span>
              <h3 style="font-size: 1.25rem; font-weight: 800; color: #ffffff; font-family: var(--font-serif); margin: 6px 0 0;">
                Diario di Bordo: Come ti senti oggi?
              </h3>
            </div>
            <?=dx_icon('edit-3', 'text-neon-gold', 20)?>
          </div>

          <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
            Scrivere pochi pensieri sinceri fissa i progressi invisibili. Il tuo diario è completamente privato e visibile solo a te.
          </p>

          <form method="post" class="mb-4">
            <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
            <input type="hidden" name="action" value="save_journal_entry">
            
            <div class="mb-3">
              <label style="font-size: 0.8rem; color: #94a3b8; display: block; margin-bottom: 6px;">
                Stato d'animo prevalente:
              </label>
              <div class="d-flex gap-2">
                <label style="flex:1; text-align:center; padding:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:8px; cursor:pointer;">
                  <input type="radio" name="mood" value="5" style="display:none;" checked>
                  <span style="font-size:1.2rem;">🌱</span><br><small style="font-size:0.7rem; color:#cbd5e1;">Sereno</small>
                </label>
                <label style="flex:1; text-align:center; padding:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:8px; cursor:pointer;">
                  <input type="radio" name="mood" value="4" style="display:none;">
                  <span style="font-size:1.2rem;">🌤️</span><br><small style="font-size:0.7rem; color:#cbd5e1;">Lucido</small>
                </label>
                <label style="flex:1; text-align:center; padding:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:8px; cursor:pointer;">
                  <input type="radio" name="mood" value="3" style="display:none;">
                  <span style="font-size:1.2rem;">🌊</span><br><small style="font-size:0.7rem; color:#cbd5e1;">Onde alte</small>
                </label>
                <label style="flex:1; text-align:center; padding:8px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); border-radius:8px; cursor:pointer;">
                  <input type="radio" name="mood" value="2" style="display:none;">
                  <span style="font-size:1.2rem;">🌧️</span><br><small style="font-size:0.7rem; color:#cbd5e1;">Faticoso</small>
                </label>
              </div>
            </div>

            <div class="mb-3">
              <textarea name="journal_note" rows="3" class="form-control" style="background: rgba(20,26,45,0.9); border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 12px; font-size: 0.9rem;" placeholder="Cosa custodisci oggi nel cuore? Anche solo una frase..." required></textarea>
            </div>

            <button type="submit" class="btn-rainbow-neon small w-100" style="justify-content: center;">
              <?=dx_icon('save', '', 14)?> Custodisci Riflessione (+15 PV)
            </button>
          </form>

          <?php if (!empty($userJournals)): ?>
            <div style="font-size: 0.78rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
              Ultime riflessioni custodite:
            </div>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($userJournals as $entry): ?>
                <div class="p-2 px-3 rounded" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); font-size: 0.82rem; color: #cbd5e1;">
                  <span style="color: #67e8f9; font-weight: 600;"><?=date('d/m/Y', strtotime($entry['entry_date']))?>:</span>
                  <?=h($entry['note'])?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="mt-3 pt-3 border-top border-secondary text-end">
          <a href="journal.php" style="font-size: 0.8rem; color: #fde68a; text-decoration: none; font-weight: 700;">
            Apri il Diario Completo ›
          </a>
        </div>
      </div>
    </div>

  </div>

  <!-- ======================================================== -->
  <!-- IL MIO PRESIDIO TERRITORIALE / CLUB CAT LOCALE           -->
  <!-- ======================================================== -->
  <section class="p-4 p-md-5 my-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(34, 197, 94, 0.35); border-radius: 20px;">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="badge-neon-rainbow mb-2">
          <span class="dot"></span>
          <span style="color:#38ef7d;">IL TUO PRESIDIO TERRITORIALE</span>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 2.5vw, 1.9rem); color: #ffffff; font-weight: 800; margin: 0 0 8px;">
          <?=h($featuredClub['entity_name'] ?? 'Club Alcologico Territoriale')?>
        </h3>
        <p style="color: #cbd5e1; font-size: 0.94rem; line-height: 1.6; margin: 0 0 12px;">
          Sede: <strong><?=h($featuredClub['city'] ?? 'Taglio di Po')?> (<?=h($featuredClub['province'] ?? 'RO')?>)</strong> · 
          Riunione settimanale: <strong><?=h($featuredClub['meeting_day'] ?: 'Ogni Giovedì')?> alle ore <?=h($featuredClub['meeting_time'] ?: '20:30')?></strong>.
        </p>
        <div style="font-size: 0.84rem; color: #94a3b8;">
          «Gli strumenti digitali ti aiutano a orientarti, ma il calore della comunità si vive guardando negli occhi i compagni di cammino nella stanza del Club.»
        </div>
      </div>
      <div class="col-lg-4 text-lg-end">
        <div class="d-flex flex-column gap-2">
          <a href="mappa-club.php" class="btn-rainbow-neon small" style="justify-content: center;">
            <?=dx_icon('compass', '', 14)?>
            <span style="margin-left: 6px;">Trova Altri Club (1.761 in Italia)</span>
          </a>
          <a href="parla-con-noi.php" class="btn small" style="border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 12px; justify-content: center;">
            <?=dx_icon('message-circle', 'text-neon-cyan', 14)?>
            <span style="margin-left: 6px;">Contatta un Servitore-Insegnante</span>
          </a>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
/* ================= LOGICA CONTATORE SOBRIETÀ ================= */
const SERVER_START_DATE = "<?=h($userStartDate ?: '')?>";

function getStartDate() {
  if (SERVER_START_DATE) {
    return new Date(SERVER_START_DATE);
  }
  const saved = localStorage.getItem('dx_sobriety_start_date');
  if (saved) return new Date(saved);
  const d = new Date();
  d.setDate(d.getDate() - 94); // fallback dimostrativo
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

  // Stime sobrie: 10€/giorno e 4 bicchieri evitati
  const money = diffDays * 10;
  const drinks = diffDays * 4;

  document.getElementById('moneySaved').innerText = money.toLocaleString('it-IT') + ' €';
  document.getElementById('drinksAvoided').innerText = drinks.toLocaleString('it-IT');

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

  const span = Math.max(1, nextMs.days - prevDays);
  const progressInSpan = Math.max(0, diffDays - prevDays);
  const pct = Math.min(100, Math.max(5, Math.round((progressInSpan / span) * 100)));
  document.getElementById('milestoneProgress').style.width = pct + '%';
}

function toggleDateEdit() {
  const box = document.getElementById('dateEditBox');
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
}

function saveStartDateLocal() {
  const val = document.getElementById('inputStartDate').value;
  if (val) {
    localStorage.setItem('dx_sobriety_start_date', val);
  }
}

function openCompassRay(rayId, title, question, color) {
  const box = document.getElementById('compassQuestionBox');
  const tEl = document.getElementById('compassTitle');
  const qEl = document.getElementById('compassQuestionText');
  const linkEl = document.getElementById('compassDeepenLink');

  tEl.innerText = title;
  tEl.style.color = color;
  qEl.innerText = '«' + question + '»';
  if (linkEl) {
    linkEl.href = 'orientamento.php?area=' + encodeURIComponent(rayId);
  }
  box.style.display = 'block';
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function generateCertificate() {
  const days = document.getElementById('streakDays').innerText;
  const name = "<?=h($u ? $u['display_name'] : 'Membro del Club')?>";
  const w = window.open('', '_blank');
  w.document.write(`
    <!doctype html>
    <html lang="it">
    <head>
      <title>Attestato di Sobrietà · DEPENDEX</title>
      <style>
        body { font-family: 'Cinzel', serif, Georgia; background: #0b0f19; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .cert-card { width: 700px; border: 4px double #fde68a; padding: 40px; text-align: center; background: radial-gradient(circle, #151c2e, #090c14); border-radius: 16px; box-shadow: 0 0 40px rgba(253,230,138,0.3); }
        h1 { font-size: 26px; color: #fde68a; margin-bottom: 8px; letter-spacing: 2px; }
        .sub { font-size: 13px; color: #67e8f9; text-transform: uppercase; margin-bottom: 24px; letter-spacing: 1px; }
        .name { font-size: 30px; font-weight: bold; color: #fff; border-bottom: 2px solid #fde68a; display: inline-block; padding: 0 20px 8px; margin-bottom: 20px; }
        .days { font-size: 52px; font-weight: 800; color: #86efac; margin: 10px 0; }
        p { font-size: 15px; color: #cbd5e1; line-height: 1.6; max-width: 540px; margin: 0 auto 24px; }
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

document.addEventListener('DOMContentLoaded', function() {
  updateStreakDisplay();
  setInterval(updateStreakDisplay, 60000);
});
</script>

<?php require '_footer.php'; ?>
