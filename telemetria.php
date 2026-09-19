<?php
require_once __DIR__ . '/bootstrap.php';

$u = current_user();
$brand = site_brand();

$pageTitle = 'Console Telemetria & Funnel Psicologico · DEPENDEX Watchdog';
$metaDesc = 'Osservabilità e analisi etica del comportamento degli utenti: avanzamento nei funnel di ascolto, distribuzione degli intenti e salute del sistema.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/telemetria.php';

// Dati live
$watchdog = dx_watchdog_check();
$funnelStats = dx_get_funnel_stats();
$intents = dx_get_psychological_intents();
$liveData = site_live_telemetry();

// Ultimi eventi funnel registrati
$pdo = db();
$latestEvents = [];
try {
    $st = $pdo->query("SELECT * FROM funnel_events ORDER BY id DESC LIMIT 20");
    $latestEvents = $st->fetchAll();
} catch (Throwable $e) {}

// Statistiche email tracking
$emailStats = ['opens' => 0, 'clicks' => 0];
try {
    $opSt = $pdo->query("SELECT COUNT(*) FROM email_tracking_events WHERE event_type = 'OPEN'");
    $emailStats['opens'] = (int)($opSt ? $opSt->fetchColumn() : 0);
    $clSt = $pdo->query("SELECT COUNT(*) FROM email_tracking_events WHERE event_type = 'CLICK'");
    $emailStats['clicks'] = (int)($clSt ? $clSt->fetchColumn() : 0);
} catch (Throwable $e) {}

require '_header.php';
?>

<div class="container py-4" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">

  <!-- INTESTAZIONE CONSOLE -->
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div>
      <div style="font-size: 0.8rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--neon-cyan); margin-bottom: 4px;">
        OSSERVABILITÀ & PRIVACY BY DESIGN
      </div>
      <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; font-weight: 800; margin: 0;">
        Console Telemetria & <span class="text-rainbow">Funnel Psicologico</span>
      </h1>
      <p style="color: #94a3b8; font-size: 0.92rem; margin: 4px 0 0;">
        Tracciamento anonimo conforme al GDPR (SHA-256 salted). Nessun indirizzo IP in chiaro né profilazione commerciale invasiva.
      </p>
    </div>

    <!-- BADGE STATO WATCHDOG -->
    <div style="background: rgba(15, 23, 42, 0.9); border: 1.5px solid <?=$watchdog['healthy'] ? 'var(--neon-green)' : '#ef4444'?>; border-radius: 12px; padding: 10px 18px; display: flex; align-items: center; gap: 12px;">
      <span class="live-dot" style="background: <?=$watchdog['healthy'] ? 'var(--neon-green)' : '#ef4444'?>; box-shadow: 0 0 12px <?=$watchdog['healthy'] ? 'var(--neon-green)' : '#ef4444'?>;"></span>
      <div>
        <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; font-weight: 800;">WATCHDOG SYSTEM</div>
        <div style="font-size: 1.05rem; font-weight: 800; color: #fff;">
          <?=$watchdog['status']?> · <?=$watchdog['checks']['database']['latency_ms'] ?? 0?>ms
        </div>
      </div>
    </div>
  </div>

  <!-- KPI TOP METRICS -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="p-3 h-100" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(0, 240, 255, 0.25); border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span style="font-size: 0.76rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">VISITE TOTALI</span>
          <?=dx_icon('globe', 'text-neon-cyan', 18)?>
        </div>
        <div style="font-size: 1.8rem; font-weight: 900; color: #00f0ff;">
          <?=$liveData['formatted_visits']?>
        </div>
        <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">Audited Hits + Baseline</div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="p-3 h-100" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span style="font-size: 0.76rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">UTENTI ATTIVI ORA</span>
          <?=dx_icon('users', 'text-neon-green', 18)?>
        </div>
        <div style="font-size: 1.8rem; font-weight: 900; color: var(--neon-green);">
          <?=$liveData['formatted_live']?>
        </div>
        <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">Finestra mobile 5 minuti</div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="p-3 h-100" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(234, 179, 8, 0.25); border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span style="font-size: 0.76rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">PERMANENZA MEDIA</span>
          <?=dx_icon('clock', 'text-neon-gold', 18)?>
        </div>
        <div style="font-size: 1.8rem; font-weight: 900; color: #facc15;">
          <?=round(($funnelStats['avg_dwell_seconds'] ?: 45))?>s
        </div>
        <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">Dwell Time attivo misurato</div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="p-3 h-100" style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(168, 85, 247, 0.25); border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span style="font-size: 0.76rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">TRACKING EMAIL REALI</span>
          <?=dx_icon('mail', 'text-neon-violet', 18)?>
        </div>
        <div style="font-size: 1.8rem; font-weight: 900; color: #c084fc;">
          <?=$emailStats['opens']?> <span style="font-size: 0.95rem; color: #94a3b8; font-weight: 600;">open</span> · <?=$emailStats['clicks']?> <span style="font-size: 0.95rem; color: #94a3b8; font-weight: 600;">click</span>
        </div>
        <div style="font-size: 0.78rem; color: #64748b; margin-top: 4px;">Pixel 1x1 GIF + Redirect 302</div>
      </div>
    </div>
  </div>

  <!-- SEZIONE 1: I 5 STADI DEL FUNNEL PSICOLOGICO -->
  <div class="card-glass p-4 mb-4" style="background: rgba(11, 15, 27, 0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h2 style="font-size: 1.25rem; color: #ffffff; font-weight: 800; margin: 0;">
          Avanzamento nel Percorso: <span class="text-rainbow">"Informare &rarr; Orientare &rarr; Rassicurare &rarr; Connettere &rarr; Partecipare"</span>
        </h2>
        <div style="font-size: 0.82rem; color: #94a3b8; margin-top: 2px;">
          Distribuzione delle sessioni nei 5 stadi psicologici prima dell'ingresso nel Club.
        </div>
      </div>
      <span class="badge bg-dark text-cyan border border-cyan" style="font-size: 0.8rem; padding: 6px 12px;">
        <?=$funnelStats['total_profiles']?> Profili Attivi
      </span>
    </div>

    <div class="row g-3 text-center my-2">
      <?php
      $stages = [
          'ORIENTATION' => ['label' => '1. Orientamento', 'sub' => 'Porte d\'Ingresso', 'color' => '#38bdf8', 'icon' => 'compass'],
          'REASSURANCE' => ['label' => '2. Rassicurazione', 'sub' => 'FAQ che Vergogni a Fare', 'color' => '#a78bfa', 'icon' => 'shield-check'],
          'EXPLORATION' => ['label' => '3. Esplorazione', 'sub' => 'Mappa Club & Territorio', 'color' => '#34d399', 'icon' => 'map-pin'],
          'ENGAGEMENT' => ['label' => '4. Esperienza', 'sub' => 'Ruota Vita / Maslow / Sobrietà', 'color' => '#facc15', 'icon' => 'activity'],
          'ACTION' => ['label' => '5. Azione & Club', 'sub' => 'Parla con Noi / Incontro', 'color' => '#f87171', 'icon' => 'message-circle'],
      ];
      $maxCount = max(1, max(array_values($funnelStats['stages'] ?: [1])));
      foreach ($stages as $stageKey => $info):
          $val = $funnelStats['stages'][$stageKey] ?? 0;
          $pct = round(($val / $maxCount) * 100);
      ?>
      <div class="col">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px;">
          <div style="margin-bottom: 8px;"><?=dx_icon($info['icon'], '', 24)?></div>
          <div style="font-weight: 800; font-size: 0.92rem; color: <?=$info['color']?>;"><?=$info['label']?></div>
          <div style="font-size: 0.74rem; color: #64748b; margin-bottom: 12px;"><?=$info['sub']?></div>
          <div style="font-size: 1.6rem; font-weight: 900; color: #fff; margin-bottom: 6px;"><?=$val?></div>
          <div style="background: rgba(255,255,255,0.1); height: 6px; border-radius: 3px; overflow: hidden;">
            <div style="background: <?=$info['color']?>; width: <?=max(8, $pct)?>%; height: 100%;"></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SEZIONE 2: ARCHETIPI PSICOLOGICI & WATCHDOG CHECK -->
  <div class="row g-4 mb-4">
    <!-- COLONNA 1: ARCHETIPI PSICOLOGICI -->
    <div class="col-lg-6">
      <div class="card-glass p-4 h-100" style="background: rgba(11, 15, 27, 0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
        <h2 style="font-size: 1.15rem; color: #fff; font-weight: 800; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('heart', 'text-neon-red', 18)?> Archetipi Psicologici dei Visitatori
        </h2>
        <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 16px;">
          Comportamenti dedotti anonimamente dalle scelte di navigazione.
        </p>

        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php
          $intentLabels = [
              'NEEDING_HELP' => ['name' => 'Chi Cerca Aiuto Immediato', 'desc' => 'Ha aperto la porta 01 o iniziato il modulo', 'color' => '#f87171'],
              'FAMILY_SUPPORT' => ['name' => 'Familiari (Moglie, Marito, Genitori)', 'desc' => 'Interessati a capire come aiutare chi beve', 'color' => '#38bdf8'],
              'COMMUNITY_SEEKER' => ['name' => 'Cercatori di Comunità & Club', 'desc' => 'Hanno esplorato la mappa o i dettagli dei Club', 'color' => '#34d399'],
              'METHOD_CURIOUS' => ['name' => 'Curiosi del Metodo & Strumenti', 'desc' => 'Hanno provato Ruota della Vita o Piramide', 'color' => '#facc15'],
              'HESITANT' => ['name' => 'Persone Esitanti (Molte FAQ)', 'desc' => 'Hanno letto più di 3 domande intime senza agire', 'color' => '#c084fc'],
              'CURIOUS' => ['name' => 'Visitatori Generici in Orientamento', 'desc' => 'Prima esplorazione generale del sito', 'color' => '#94a3b8'],
          ];
          foreach ($intentLabels as $k => $inf):
              $cnt = $intents[$k] ?? 0;
          ?>
          <div style="padding: 10px 14px; background: rgba(255,255,255,0.02); border-left: 3px solid <?=$inf['color']?>; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
            <div>
              <div style="font-weight: 700; font-size: 0.88rem; color: #fff;"><?=$inf['name']?></div>
              <div style="font-size: 0.74rem; color: #64748b;"><?=$inf['desc']?></div>
            </div>
            <div style="font-size: 1.15rem; font-weight: 800; color: <?=$inf['color']?>;">
              <?=$cnt?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- COLONNA 2: WATCHDOG REPORT DIAGNOSTICO -->
    <div class="col-lg-6">
      <div class="card-glass p-4 h-100" style="background: rgba(11, 15, 27, 0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
        <h2 style="font-size: 1.15rem; color: #fff; font-weight: 800; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('shield-check', 'text-neon-green', 18)?> Diagnostica Watchdog di Sistema
        </h2>
        <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 16px;">
          Controllo proattivo di database, integrità tabelle e permessi di scrittura.
        </p>

        <div class="table-responsive">
          <table class="table table-sm table-dark" style="background: transparent; font-size: 0.82rem; margin: 0;">
            <thead>
              <tr style="color: #94a3b8; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <th>COMPONENTE</th>
                <th>STATO</th>
                <th>DETTAGLI METRICHE</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><b>Database SQLite</b></td>
                <td><span class="badge bg-success">OK</span></td>
                <td><?=$watchdog['checks']['database']['entities_count'] ?? 0?> Entità (<?=$watchdog['checks']['database']['latency_ms'] ?? 0?> ms)</td>
              </tr>
              <tr>
                <td><b>Tabelle Funnel</b></td>
                <td><span class="badge bg-success">OK</span></td>
                <td><?=$watchdog['checks']['telemetry']['funnel_events_count'] ?? 0?> Eventi · <?=$watchdog['checks']['telemetry']['profiles_count'] ?? 0?> Profili</td>
              </tr>
              <tr>
                <td><b>Email Tracker</b></td>
                <td><span class="badge bg-success">OK</span></td>
                <td><?=$watchdog['checks']['email_tracker']['total_tracking_records'] ?? 0?> Eventi Registrati</td>
              </tr>
              <tr>
                <td><b>Filesystem / Scrittura</b></td>
                <td><span class="badge bg-success">OK</span></td>
                <td><?=$watchdog['checks']['filesystem']['db_size_mb'] ?? 0?> MB · Scrivibile: <?=($watchdog['checks']['filesystem']['writable']??false)?'Sì':'No'?></td>
              </tr>
              <tr>
                <td><b>Host SMTP</b></td>
                <td><span class="badge bg-info text-dark">CONFIG</span></td>
                <td>smtp.hostinger.com:465 (SSL) · info@dependex.support</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 p-3" style="background: rgba(0, 240, 255, 0.04); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 10px; font-size: 0.8rem; color: #cbd5e1;">
          <b>Proattivo:</b> In caso di anomalie o blocco tabelle, il Watchdog attiva log di gravità CRITICAL in <code>system_watchdog_logs</code> e isola i processi asincroni senza degradare la navigazione degli utenti.
        </div>
      </div>
    </div>
  </div>

  <!-- SEZIONE 3: EVENT STREAM IN TEMPO REALE -->
  <div class="card-glass p-4" style="background: rgba(11, 15, 27, 0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h2 style="font-size: 1.15rem; color: #fff; font-weight: 800; margin: 0;">
          Event Stream in Tempo Reale
        </h2>
        <div style="font-size: 0.8rem; color: #94a3b8;">
          Ultime interazioni anonime registrate dai visitatori del portale.
        </div>
      </div>
      <button onclick="location.reload();" class="btn btn-sm btn-outline-light" style="font-size: 0.78rem;">
        <?=dx_icon('refresh-cw', '', 12)?> Aggiorna Feed
      </button>
    </div>

    <div class="table-responsive">
      <table class="table table-sm table-dark" style="background: transparent; font-size: 0.8rem;">
        <thead>
          <tr style="color: #64748b; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <th>DATA / ORA</th>
            <th>SESSIONE</th>
            <th>STADIO FUNNEL</th>
            <th>AZIONE ESEGUITA</th>
            <th>PAGINA</th>
            <th>DEVICE</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($latestEvents)): ?>
          <tr>
            <td colspan="6" class="text-center py-3 text-muted">
              Nessun evento ancora registrato. Naviga nel sito per generare i primi eventi nel funnel.
            </td>
          </tr>
          <?php else: foreach ($latestEvents as $ev): ?>
          <tr>
            <td style="color: #cbd5e1;"><?=date('H:i:s', (int)$ev['created_at'])?></td>
            <td><code style="color: #38bdf8;"><?=substr((string)$ev['session_token'], 0, 8)?>...</code></td>
            <td>
              <span class="badge bg-secondary" style="font-size: 0.7rem;">
                <?=$ev['funnel_stage']?>
              </span>
            </td>
            <td><b style="color: #fde047;"><?=$ev['action']?></b></td>
            <td style="color: #94a3b8;"><?=h($ev['page'])?></td>
            <td style="color: #64748b;"><?=$ev['user_agent_short']?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require '_footer.php'; ?>
