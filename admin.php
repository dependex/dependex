<?php
/**
 * admin.php — Executive Control Center · DEPENDEX OS
 * Control plane unificato per censimento Club CAT, rete mondiale, welfare Hudolin,
 * flussi email marketing (FLUX100 / EMM+), telemetria H24 e governance.
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$u = require_admin();

$db = db();
$msg = null;
$msgType = 'info';

// Azione Rapida: Invio Email di Test Prioritaria a labomobile.lm@gmail.com
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_test_labomobile') {
    csrf_check();
    $step = max(1, min(5, (int)($_POST['step'] ?? 1)));
    $targetEmail = 'labomobile.lm@gmail.com';
    $sicId = trim($_POST['sic_id'] ?? 'SIC-TAGLIODIPO-RO-001');

    $cmd = 'python ' . escapeshellarg(__DIR__ . '/bin/crm_clubs_dispatcher.py') . ' --send-test --recipient ' . escapeshellarg($targetEmail) . ' --step ' . $step . ' --sic ' . escapeshellarg($sicId);
    $output = shell_exec($cmd . ' 2>&1');

    if ($output && (str_contains(strtolower($output), 'success') || str_contains(strtolower($output), 'inviata') || str_contains(strtolower($output), 'ok'))) {
        $msg = "Email Step {$step} inviata con successo a {$targetEmail} via Hostinger SMTP.";
        $msgType = 'success';
    } else {
        $msg = "Invio test completato: " . htmlspecialchars(substr((string)$output, 0, 200));
        $msgType = 'info';
    }
}

// Azione Rapida: Esecuzione Batch Outreach Giornaliero (FLUX100 / EMM+)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batch_outreach_dispatch') {
    csrf_check();
    $cap = max(1, min(50, (int)($_POST['daily_cap'] ?? 25)));
    $step = max(1, min(5, (int)($_POST['step'] ?? 1)));
    $isDry = !empty($_POST['dry_run']);
    $dryFlag = $isDry ? ' --dry-run' : '';

    $cmd = 'python ' . escapeshellarg(__DIR__ . '/bin/crm_clubs_dispatcher.py') . ' --dispatch-batch --daily-cap ' . $cap . ' --step ' . $step . $dryFlag;
    $output = shell_exec($cmd . ' 2>&1');

    $prefix = $isDry ? '[SIMULAZIONE] ' : '[ESECUZIONE] ';
    $msg = $prefix . "Batch Outreach Step {$step} (Cap {$cap}): " . htmlspecialchars(substr((string)$output, 0, 350));
    $msgType = $isDry ? 'info' : 'success';
}

// 1. KPI Aggregati Globali
$kpis = [
    'Club CAT Italia' => [
        'val' => (int)$db->query("SELECT COUNT(*) FROM crm_club_contacts")->fetchColumn(),
        'sub' => '395 censiti su suolo nazionale',
        'icon' => 'compass',
        'color' => '#38ef7d'
    ],
    'Nodi World Registry' => [
        'val' => (int)$db->query("SELECT COUNT(*) FROM dependex_world_registry")->fetchColumn(),
        'sub' => 'Dalla cellula locale a Oltre.social',
        'icon' => 'globe',
        'color' => '#67e8f9'
    ],
    'Regioni Coperte' => [
        'val' => (int)$db->query("SELECT COUNT(DISTINCT region) FROM crm_club_contacts WHERE region<>''")->fetchColumn(),
        'sub' => 'Presidi territoriali attivi',
        'icon' => 'map-pin',
        'color' => '#fde68a'
    ],
    'Geocoding in Coda' => [
        'val' => (int)$db->query("SELECT COUNT(*) FROM geocode_queue WHERE status='PENDING'")->fetchColumn(),
        'sub' => 'In attesa di coordinate esatte',
        'icon' => 'navigation',
        'color' => '#f472b6'
    ],
    'Utenti Piattaforma' => [
        'val' => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'sub' => 'Account e referenti registrati',
        'icon' => 'users',
        'color' => '#93c5fd'
    ],
    'Eventi Pubblicati' => [
        'val' => (int)$db->query("SELECT COUNT(*) FROM events WHERE status='PUBLISHED'")->fetchColumn(),
        'sub' => 'Interclub, scuole, masterclass',
        'icon' => 'calendar',
        'color' => '#c084fc'
    ]
];

// 2. Censimento Club: Ripartizione Regionale (Top 8)
$regionalStats = $db->query("
    SELECT region, COUNT(*) as club_count 
    FROM crm_club_contacts 
    WHERE region <> '' 
    GROUP BY region 
    ORDER BY club_count DESC 
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

$totalItalianClubs = (int)$db->query("SELECT COUNT(*) FROM crm_club_contacts")->fetchColumn();
$directEmailsCount = (int)$db->query("SELECT COUNT(*) FROM crm_club_contacts WHERE email_type = 'DIRECT'")->fetchColumn();
$meetingsDefinedCount = (int)$db->query("SELECT COUNT(*) FROM crm_club_contacts WHERE meeting_day IS NOT NULL AND meeting_day <> ''")->fetchColumn();

// 3. Gerarchia Mondiale (network_level)
$levels = $db->query("
    SELECT network_level, COUNT(*) as n 
    FROM dependex_world_registry 
    GROUP BY network_level 
    ORDER BY network_rank DESC
")->fetchAll(PDO::FETCH_ASSOC);

// 4. Coda Email e Watchdog
$emailQueueCount = (int)$db->query("SELECT COUNT(*) FROM email_queue WHERE status='PENDING'")->fetchColumn();
$emailSentCount = (int)$db->query("SELECT COUNT(*) FROM email_queue WHERE status='SENT'")->fetchColumn();
$outreachPending = (int)$db->query("SELECT COUNT(*) FROM crm_club_contacts WHERE (outreach_status = 'UNCONTACTED' OR outreach_status IS NULL) AND unsubscribed_at IS NULL")->fetchColumn();
$outreachContacted = (int)$db->query("SELECT COUNT(*) FROM crm_club_contacts WHERE outreach_status LIKE 'CONTACTED_%'")->fetchColumn();

$pageTitle = 'Control Center Unificato · DEPENDEX OS';
require '_header.php';
?>

<div class="container py-4" style="max-width: 1400px; margin: 0 auto; padding: 0 1rem;">

  <!-- BANNER HEADER CON STATO SISTEMA LIVE -->
  <section class="p-4 mb-4" style="background: linear-gradient(135deg, rgba(20,26,45,0.95), rgba(10,13,22,0.98)); border: 1px solid rgba(224, 169, 109, 0.35); border-radius: 20px; box-shadow: var(--rainbow-glow);">
    <div class="row align-items-center g-3">
      <div class="col-lg-8">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <span class="badge-neon-rainbow" style="font-size: 0.72rem;">
            <span class="dot"></span>
            <span>CONTROL PLANE ATTIVO</span>
          </span>
          <span style="font-size: 0.76rem; color: #86efac; background: rgba(34,197,94,0.12); padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(34,197,94,0.3);">
            ● SQLite WAL Mode
          </span>
          <span style="font-size: 0.76rem; color: #67e8f9; background: rgba(6,182,212,0.12); padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(6,182,212,0.3);">
            SMTP: smtp.hostinger.com:465
          </span>
          <span style="font-size: 0.76rem; color: #fde68a; background: rgba(253,230,138,0.12); padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(253,230,138,0.3);">
            Mittente: info@dependex.support
          </span>
        </div>
        <h1 style="font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: #ffffff; margin: 0 0 6px 0; font-family: var(--font-serif);">
          Executive Control Center
        </h1>
        <p style="color: #cbd5e1; font-size: 0.92rem; margin: 0; line-height: 1.5;">
          Monitoraggio centralizzato: Censimento dei 395 Club CAT, Rete mondiale Vladimir Hudolin, Nurturing FLUX100, Telemetria H24 e Governance.
        </p>
      </div>

      <div class="col-lg-4 text-lg-end">
        <div class="d-inline-flex flex-column gap-2 text-start p-3" style="background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; min-width: 240px;">
          <div style="font-size: 0.74rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Amministratore Connesso</div>
          <div style="font-size: 1rem; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px;">
            <?=dx_icon('shield', 'text-neon-gold', 16)?>
            <span><?=h($u['display_name'])?></span>
          </div>
          <div style="font-size: 0.74rem; color: #67e8f9; font-family: monospace; word-break: break-all;">
            <?=h($u['sic_id'])?>
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

  <!-- 1. KPI GRID IN TEMPO REALE -->
  <div class="row g-3 mb-4">
    <?php foreach ($kpis as $label => $data): ?>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="p-3 h-100" style="background: rgba(14, 20, 38, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, border-color 0.2s;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-size: 0.76rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;"><?=h($label)?></span>
            <?=dx_icon($data['icon'], '', 16, ['style' => 'color:' . $data['color']])?>
          </div>
          <div>
            <div style="font-size: clamp(1.6rem, 2.5vw, 2.1rem); font-weight: 800; color: #ffffff; font-family: var(--font-serif); line-height: 1.1;">
              <?=number_format($data['val'], 0, ',', '.')?>
            </div>
            <div style="font-size: 0.72rem; color: #64748b; margin-top: 4px;"><?=h($data['sub'])?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 2. QUICK OPERATIONS BAR (AZIONI IMMEDIATE) -->
  <section class="p-4 mb-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(6, 182, 212, 0.25); border-radius: 20px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #ffffff; margin: 0; font-family: var(--font-serif); display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('zap', 'text-neon-cyan', 18)?>
          <span>Azioni Rapide di Controllo & Outreach</span>
        </h3>
        <p style="font-size: 0.82rem; color: #94a3b8; margin: 4px 0 0;">
          Esegui operazioni istantanee di prova, sincronizzazione geocodifica ed esportazione dati.
        </p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="crm-clubs.php" class="btn-rainbow-neon small" style="padding: 8px 14px; font-size: 0.82rem;">
          <?=dx_icon('search', '', 14)?>
          <span>Apri CRM Club Completo</span>
        </a>
        <a href="api-opendata-geojson.php" target="_blank" class="btn small" style="border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 10px; font-size: 0.82rem; padding: 8px 14px;">
          <?=dx_icon('globe', 'text-neon-gold', 14)?>
          <span>OpenData GeoJSON (RFC 7946)</span>
        </a>
        <a href="data/CRM_CLUB_CONTATTI_MASTER_2026.csv" download class="btn small" style="border: 1px solid rgba(255,255,255,0.25); color: #fff; border-radius: 10px; font-size: 0.82rem; padding: 8px 14px;">
          <?=dx_icon('download', 'text-neon-cyan', 14)?>
          <span>Scarica Master CSV (395 Club)</span>
        </a>
        <a href="widget-generator.php" target="_blank" class="btn small" style="border: 1px solid rgba(253,230,138,0.3); color: #fde68a; border-radius: 10px; font-size: 0.82rem; padding: 8px 14px;">
          <?=dx_icon('code', 'text-neon-gold', 14)?>
          <span>Generatore Widget Comuni & ASL</span>
        </a>
      </div>
    </div>

    <!-- FORM TEST EMAIL RAPIDO SU LABOMOBILE -->
    <div class="p-3" style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
      <form method="post" class="row g-2 align-items-center">
        <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
        <input type="hidden" name="action" value="quick_test_labomobile">
        <div class="col-md-4">
          <label style="font-size: 0.75rem; color: #cbd5e1; display: block; margin-bottom: 2px;">Test Deliverability FLUX100 / EMM+</label>
          <div style="font-size: 0.85rem; font-weight: 700; color: #fde68a;">labomobile.lm@gmail.com</div>
        </div>
        <div class="col-md-3">
          <label style="font-size: 0.75rem; color: #94a3b8; display: block; margin-bottom: 2px;">Step Nurturing</label>
          <select name="step" class="form-select form-select-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;">
            <option value="1">Step 1 — Censimento Aperto 2026</option>
            <option value="2">Step 2 — Widget Trova-Club</option>
            <option value="3">Step 3 — OpenData & Ser.D</option>
            <option value="4">Step 4 — PWA & Privacy</option>
            <option value="5">Step 5 — Dialogo & Eventi</option>
          </select>
        </div>
        <div class="col-md-3">
          <label style="font-size: 0.75rem; color: #94a3b8; display: block; margin-bottom: 2px;">Club Campione (SIC-ID)</label>
          <input type="text" name="sic_id" value="SIC-TAGLIODIPO-RO-001" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; font-family: monospace;">
        </div>
        <div class="col-md-2 text-md-end">
          <label style="display: block; font-size: 0.75rem; visibility: hidden;">Azione</label>
          <button type="submit" class="btn-rainbow-outline small w-100" style="padding: 6px 12px; font-size: 0.8rem; justify-content: center;">
            <?=dx_icon('send', '', 12)?>
            <span>Invia Prova</span>
          </button>
        </div>
      </form>
    </div>
  </section>

  <!-- 3. DETTAGLIO CENSIMENTO CLUB ITALIA & GERARCHIA MONDIALE -->
  <div class="row g-4 mb-4">
    <!-- CENSIMENTO REGIONALE ITALIA -->
    <div class="col-lg-7">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <span class="badge" style="background: rgba(56,239,125,0.12); color: #38ef7d; border: 1px solid rgba(56,239,125,0.3); font-size: 0.72rem; padding: 4px 8px; border-radius: 12px;">
              CENSIMENTO NAZIONALE ATTIVO
            </span>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #ffffff; font-family: var(--font-serif); margin: 6px 0 0;">
              Distribuzione Club CAT per Regione
            </h3>
          </div>
          <span style="font-size: 0.82rem; color: #fde68a; font-weight: 700;">
            <?=$totalItalianClubs?> Club Totali
          </span>
        </div>

        <p style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 16px;">
          Stato di completezza anagrafica: <strong><?=$directEmailsCount?></strong> con email diretta accertata, 
          <strong><?=$meetingsDefinedCount?></strong> con orario settimanale convalidato.
        </p>

        <!-- BARRE PERCENTUALI REGIONALI -->
        <div class="d-flex flex-column gap-3">
          <?php foreach ($regionalStats as $r): 
            $pct = $totalItalianClubs > 0 ? round(($r['club_count'] / $totalItalianClubs) * 100, 1) : 0;
          ?>
            <div>
              <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.84rem;">
                <span style="color: #ffffff; font-weight: 600;"><?=h((string)$r['region'])?></span>
                <span style="color: #67e8f9; font-weight: 700;"><?=number_format((int)$r['club_count'])?> Club <small style="color:#94a3b8;">(<?=$pct?>%)</small></span>
              </div>
              <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.08); border-radius: 4px; overflow: hidden;">
                <div style="width: <?=$pct * 2.5?>%; max-width: 100%; height: 100%; background: linear-gradient(90deg, var(--neon-cyan), var(--neon-gold)); border-radius: 4px;"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="mt-4 pt-3 border-top border-secondary d-flex justify-content-between align-items-center" style="font-size: 0.8rem; color: #94a3b8;">
          <span>Riferimento istituzionale: AICAT / ARCAT / ACAT / APCAT</span>
          <a href="crm-clubs.php" style="color: #67e8f9; text-decoration: none; font-weight: 700;">Vedi tutti i 395 Club ›</a>
        </div>
      </div>
    </div>

    <!-- GERARCHIA MONDIALE & TELEMETRIA CODA EMAIL -->
    <div class="col-lg-5">
      <div class="d-flex flex-column gap-4 h-100">

        <!-- CARD GERARCHIA MONDIALE -->
        <div class="p-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 20px;">
          <h3 style="font-size: 1.15rem; font-weight: 800; color: #ffffff; font-family: var(--font-serif); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <?=dx_icon('globe', 'text-neon-cyan', 18)?>
            <span>Gerarchia Mondiale (World Registry)</span>
          </h3>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($levels as $l): ?>
              <div class="d-flex justify-content-between align-items-center p-2 px-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
                <span style="font-size: 0.85rem; color: #cbd5e1; font-weight: 600;"><?=h($l['network_level'])?></span>
                <span style="font-size: 0.88rem; color: #fde68a; font-weight: 800; font-family: var(--font-serif);"><?=number_format((int)$l['n'], 0, ',', '.')?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3 text-end">
            <a href="world-club-explorer.php" style="font-size: 0.8rem; color: #67e8f9; text-decoration: none; font-weight: 700;">World Club Explorer ›</a>
          </div>
        </div>

        <!-- CARD MONITOR EMAIL MARKETING & CODE -->
        <div class="p-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 20px;">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #ffffff; font-family: var(--font-serif); margin: 0; display: flex; align-items: center; gap: 8px;">
              <?=dx_icon('mail', 'text-neon-gold', 18)?>
              <span>Deliverability & Coda FLUX100</span>
            </h3>
            <span class="badge" style="background: rgba(245,158,11,0.15); color: #fde68a; font-size: 0.72rem; border: 1px solid rgba(245,158,11,0.3); border-radius: 10px;">
              RFC 8058 Ready
            </span>
          </div>

          <!-- 4 BOX METRICHE EMAIL & OUTREACH -->
          <div class="row g-2 text-center mb-3">
            <div class="col-6">
              <div class="p-2" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;">
                <div style="font-size: 1.25rem; font-weight: 800; color: #fde68a; font-family: var(--font-serif);"><?=number_format($outreachPending)?></div>
                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Club da Contattare</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;">
                <div style="font-size: 1.25rem; font-weight: 800; color: #38ef7d; font-family: var(--font-serif);"><?=number_format($outreachContacted)?></div>
                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Club Raggiunti</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;">
                <div style="font-size: 1.25rem; font-weight: 800; color: #67e8f9; font-family: var(--font-serif);"><?=number_format($emailQueueCount)?></div>
                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">In Coda Fallback</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-2" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px;">
                <div style="font-size: 1.25rem; font-weight: 800; color: #86efac; font-family: var(--font-serif);"><?=number_format($emailSentCount)?></div>
                <div style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase;">Inviate Con Successo</div>
              </div>
            </div>
          </div>

          <!-- MINI FORM PER BATCH OUTREACH GIORNALIERO (FLUX100 / EMM+) -->
          <div class="p-2 px-3 mb-3" style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
            <div style="font-size: 0.74rem; font-weight: 700; color: #fde68a; margin-bottom: 6px; text-transform: uppercase;">
              Outreach Censimento Giornaliero
            </div>
            <form method="post" class="d-flex align-items-center gap-2 flex-wrap">
              <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
              <input type="hidden" name="action" value="batch_outreach_dispatch">
              <input type="hidden" name="step" value="1">
              <div class="d-flex align-items-center gap-1" style="font-size: 0.78rem; color: #cbd5e1;">
                <span>Cap:</span>
                <input type="number" name="daily_cap" value="25" min="1" max="50" class="form-control form-control-sm" style="width: 60px; background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 2px 6px;">
              </div>
              <label class="d-flex align-items-center gap-1" style="font-size: 0.74rem; color: #94a3b8; cursor: pointer;">
                <input type="checkbox" name="dry_run" value="1" checked> Simula
              </label>
              <button type="submit" class="btn-rainbow-outline small ms-auto" style="padding: 4px 10px; font-size: 0.75rem;">
                <?=dx_icon('play', '', 11)?> Esegui Batch
              </button>
            </form>
          </div>

          <div style="font-size: 0.76rem; color: #94a3b8; line-height: 1.4;">
            Tracciamento consensi GDPR conforme, Unsubscribe one-click RFC 8058 e rispetto reputazione IP Hostinger con throttle a 3s.
          </div>
          <div class="mt-3 text-end">
            <a href="email-admin.php" style="font-size: 0.8rem; color: #fde68a; text-decoration: none; font-weight: 700;">Gestione Email Machine ›</a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- 4. MODULI OPERATIVI DI GOVERNANCE (HUB UNIFICATO) -->
  <section class="mb-4">
    <div class="mb-3">
      <h2 style="font-size: 1.35rem; font-weight: 800; color: #ffffff; font-family: var(--font-serif); margin: 0 0 4px;">
        Architettura Modulare Ecosistema
      </h2>
      <p style="font-size: 0.85rem; color: #94a3b8; margin: 0;">
        Tutti i moduli operativi classificati per aree di pertinenza.
      </p>
    </div>

    <div class="row g-3">

      <!-- CATEGORIA 1: CENSIMENTO & RETE TERRITORIALE -->
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(14, 20, 38, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
          <div style="font-size: 0.75rem; color: #38ef7d; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
            🌍 Censimento & Rete
          </div>
          <div class="d-flex flex-column gap-2">
            <a href="crm-clubs.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>CRM Club Italia</strong> (395 Presidi)</span>
              <span style="color:#38ef7d;">›</span>
            </a>
            <a href="world-club-explorer.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>World Club Explorer</strong></span>
              <span style="color:#67e8f9;">›</span>
            </a>
            <a href="world-network-tree.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Albero della Rete Mondiale</strong></span>
              <span style="color:#67e8f9;">›</span>
            </a>
            <a href="registry.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Anagrafe SIC</strong> (User & Famiglie)</span>
              <span style="color:#fde68a;">›</span>
            </a>
            <a href="geo-admin.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Geo Admin & Geocoding Queue</strong></span>
              <span style="color:#f472b6;">›</span>
            </a>
          </div>
        </div>
      </div>

      <!-- CATEGORIA 2: WELFARE OS & FORMAZIONE -->
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(14, 20, 38, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
          <div style="font-size: 0.75rem; color: #fde68a; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
            🕊 Welfare & Metodo
          </div>
          <div class="d-flex flex-column gap-2">
            <a href="hudolin-core.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Hudolin Core</strong> (Regole & Manuale)</span>
              <span style="color:#fde68a;">›</span>
            </a>
            <a href="academy.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Academy Gestione Corsi</strong></span>
              <span style="color:#fde68a;">›</span>
            </a>
            <a href="event-builder.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Event Factory</strong> (Interclub & Scuole)</span>
              <span style="color:#67e8f9;">›</span>
            </a>
            <a href="documents.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Document Factory</strong> (Attestati & Verbali)</span>
              <span style="color:#cbd5e1;">›</span>
            </a>
            <a href="graphic-studio.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Graphic Studio</strong></span>
              <span style="color:#c084fc;">›</span>
            </a>
          </div>
        </div>
      </div>

      <!-- CATEGORIA 3: OUTREACH, MARKETING & EMAIL -->
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(14, 20, 38, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
          <div style="font-size: 0.75rem; color: #67e8f9; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
            ✉ Outreach & Email
          </div>
          <div class="d-flex flex-column gap-2">
            <a href="email-admin.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Email Machine</strong> (Code & Template)</span>
              <span style="color:#67e8f9;">›</span>
            </a>
            <a href="form-builder.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Form Builder</strong></span>
              <span style="color:#cbd5e1;">›</span>
            </a>
            <a href="social-admin.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Social Impact Admin</strong></span>
              <span style="color:#f472b6;">›</span>
            </a>
            <a href="admin-orders.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Ordini & Donazioni</strong></span>
              <span style="color:#86efac;">›</span>
            </a>
          </div>
        </div>
      </div>

      <!-- CATEGORIA 4: GOVERNANCE, FINANZA & SICUREZZA -->
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(14, 20, 38, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
          <div style="font-size: 0.75rem; color: #c084fc; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
            🏛 Governance & Finanza
          </div>
          <div class="d-flex flex-column gap-2">
            <a href="dao.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>DAO Governance</strong></span>
              <span style="color:#c084fc;">›</span>
            </a>
            <a href="finance.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Finance OS & Tesoreria</strong></span>
              <span style="color:#86efac;">›</span>
            </a>
            <a href="vault-admin.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>DRX Vault Admin</strong></span>
              <span style="color:#fde68a;">›</span>
            </a>
            <a href="acl-admin.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>ACL & Permessi Ruoli</strong></span>
              <span style="color:#f472b6;">›</span>
            </a>
            <a href="telemetria.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Telemetria & Watchdog H24</strong></span>
              <span style="color:#67e8f9;">›</span>
            </a>
          </div>
        </div>
      </div>

      <!-- CATEGORIA 5: INTELLIGENZA CORTEX & CONOSCENZA -->
      <div class="col-md-6 col-lg-4">
        <div class="p-3 h-100" style="background: rgba(14, 20, 38, 0.7); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
          <div style="font-size: 0.75rem; color: #67e8f9; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">
            🧠 Company Brain & AI
          </div>
          <div class="d-flex flex-column gap-2">
            <a href="company-brain.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Company Brain Core</strong></span>
              <span style="color:#67e8f9;">›</span>
            </a>
            <a href="ncke-admin.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>NCKE Console / RAG Engine</strong></span>
              <span style="color:#67e8f9;">›</span>
            </a>
            <a href="cortex-dashboard.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>CORTEX Knowledge Graph</strong></span>
              <span style="color:#fde68a;">›</span>
            </a>
            <a href="ai-providers.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>AI Providers & LLM Keys</strong></span>
              <span style="color:#f472b6;">›</span>
            </a>
            <a href="workflow.php" class="d-flex justify-content-between align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); color: #fff; text-decoration: none; font-size: 0.85rem;">
              <span><strong>Workflow Engine</strong></span>
              <span style="color:#cbd5e1;">›</span>
            </a>
          </div>
        </div>
      </div>

    </div>
  </section>

</div>

<?php require '_footer.php'; ?>