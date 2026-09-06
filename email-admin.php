<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';

$u = require_admin();
$pageTitle = 'Universal Email Revenue OS — Control Plane';

$feedback = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'test_send') {
        $targetEmail = trim($_POST['test_email'] ?? 'labomobile.lm@gmail.com');
        $res = email_os_send_test_email($targetEmail);
        $feedback = [
            'type' => $res['success'] ? 'success' : 'error',
            'msg' => $res['success'] 
                ? "Email di prova recapitata con successo a {$res['recipient']} con mittente info@dependex.support!" 
                : "Errore durante l'invio della prova: " . htmlspecialchars($res['output'])
        ];
    } elseif ($action === 'run_dispatch') {
        $dryRun = isset($_POST['dry_run']) && $_POST['dry_run'] === '1';
        $limit = max(1, min(50, (int)($_POST['batch_limit'] ?? 10)));
        $cmd = 'python ' . escapeshellarg(__DIR__ . '/automation/emailflux/worker.py') . ' 2>&1';
        putenv("DRY_RUN=" . ($dryRun ? 'true' : 'false'));
        putenv("BATCH_LIMIT={$limit}");
        $output = shell_exec($cmd);
        $feedback = [
            'type' => 'success',
            'msg' => "Esecuzione dispatch completata (" . ($dryRun ? 'DRY-RUN' : 'LIVE') . "). Risultato: " . htmlspecialchars(substr($output ?? '', 0, 300))
        ];
    }
}

$stats = email_os_get_stats();
require '_header.php';
?>

<div class="cortex-dashboard">
  <div class="cortex-header">
    <div class="cortex-eyebrow">UNIVERSAL EMAIL REVENUE OS · GITHUB-NATIVE AUTOMATION</div>
    <h1 style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.5px; margin: 0 0 8px 0; color: #f8fafc;">
      Email Marketing Control Plane
    </h1>
    <p style="color: #94a3b8; max-width: 800px; margin: 0; line-height: 1.6;">
      Infrastruttura di marketing automation event-driven e auto-apprendente. Conforme GDPR e RFC 8058, governata da GitHub Actions e alimentata da 7.445 lead qualificati.
    </p>
  </div>

  <?php if ($feedback): ?>
    <div style="margin: 20px 0; padding: 16px 20px; border-radius: 8px; font-weight: 600; <?=$feedback['type'] === 'success' ? 'background: rgba(34, 197, 94, 0.15); border: 1px solid #22c55e; color: #4ade80;' : 'background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171;'?>">
      <?=htmlspecialchars($feedback['msg'])?>
    </div>
  <?php endif; ?>

  <!-- METRICHE GLOBALI -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 28px 0;">
    <div style="background: #0f172a; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; text-align: center;">
      <div style="font-size: 2.4rem; font-weight: 800; color: #d99a26;"><?=$stats['total_contacts']?></div>
      <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-top: 4px;">Lead Qualificati</div>
    </div>
    <div style="background: #0f172a; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; text-align: center;">
      <div style="font-size: 2.4rem; font-weight: 800; color: #38bdf8;"><?=$stats['total_workflows']?></div>
      <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-top: 4px;">Flussi Attivi</div>
    </div>
    <div style="background: #0f172a; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; text-align: center;">
      <div style="font-size: 2.4rem; font-weight: 800; color: #a855f7;"><?=$stats['total_events']?></div>
      <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-top: 4px;">Eventi Tracciati</div>
    </div>
    <div style="background: #0f172a; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; text-align: center;">
      <div style="font-size: 2.4rem; font-weight: 800; color: #10b981;"><?=$stats['recent_sends']?></div>
      <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-top: 4px;">Spedizioni Riuscite</div>
    </div>
    <div style="background: #0f172a; border: 1px solid #1e293b; padding: 20px; border-radius: 12px; text-align: center;">
      <div style="font-size: 2.4rem; font-weight: 800; color: #f43f5e;"><?=$stats['total_suppressed']?></div>
      <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-top: 4px;">Soppressioni / Unsub</div>
    </div>
  </div>

  <!-- PANNELLO AZIONI OPERATIVE RAPIDE -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 32px;">
    
    <!-- INVIO TEST VELOCE -->
    <div style="background: #0b1120; border: 1px solid #1e293b; border-radius: 12px; padding: 24px;">
      <h3 style="margin-top: 0; color: #f8fafc; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
        <span style="color: #d99a26;">⚡</span> Invio di Prova (SMTP Live)
      </h3>
      <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.5;">
        Esegue un invio istantaneo verificando l'autenticazione SSL con <code>info@dependex.support</code> e la conformità degli header RFC 8058.
      </p>
      <form method="POST">
        <input type="hidden" name="action" value="test_send">
        <div style="margin-bottom: 16px;">
          <label style="display: block; font-size: 0.85rem; color: #cbd5e1; margin-bottom: 6px;">Destinatario di Prova Prioritario</label>
          <input type="email" name="test_email" value="labomobile.lm@gmail.com" required style="width: 100%; padding: 10px 14px; background: #020617; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 0.95rem;">
        </div>
        <button type="submit" style="background: linear-gradient(135deg, #d99a26 0%, #b87d16 100%); color: #020617; border: none; font-weight: 700; padding: 12px 24px; border-radius: 6px; cursor: pointer; width: 100%;">
          Invia Prova a labomobile.lm@gmail.com
        </button>
      </form>
    </div>

    <!-- DISPATCH BATCH WORKER -->
    <div style="background: #0b1120; border: 1px solid #1e293b; border-radius: 12px; padding: 24px;">
      <h3 style="margin-top: 0; color: #f8fafc; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
        <span style="color: #38bdf8;">⚙️</span> Dispatch Governor (Batch)
      </h3>
      <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.5;">
        Elabora la coda contatti, calcola i ritardi programmabili e spedisce rispettando il rate limiting di warming e le quiet hours.
      </p>
      <form method="POST">
        <input type="hidden" name="action" value="run_dispatch">
        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
          <div style="flex: 1;">
            <label style="display: block; font-size: 0.85rem; color: #cbd5e1; margin-bottom: 6px;">Dimensione Batch</label>
            <input type="number" name="batch_limit" value="10" min="1" max="50" style="width: 100%; padding: 10px 14px; background: #020617; border: 1px solid #334155; border-radius: 6px; color: #f8fafc;">
          </div>
          <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-end;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #cbd5e1; cursor: pointer; margin-bottom: 12px;">
              <input type="checkbox" name="dry_run" value="1" checked> Modalità Dry-Run
            </label>
          </div>
        </div>
        <button type="submit" style="background: #1e293b; color: #f8fafc; border: 1px solid #475569; font-weight: 700; padding: 12px 24px; border-radius: 6px; cursor: pointer; width: 100%;">
          Esegui Batch Dispatch Ora
        </button>
      </form>
    </div>

  </div>

  <!-- I 4 FLUSSI AUTOMATIZZATI -->
  <div style="background: #0b1120; border: 1px solid #1e293b; border-radius: 12px; padding: 24px; margin-bottom: 32px;">
    <h3 style="margin-top: 0; color: #f8fafc; font-size: 1.2rem;">Flussi di Automazione Attivi</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-top: 16px;">
      
      <div style="background: #020617; border: 1px solid #1e293b; padding: 16px; border-radius: 8px;">
        <div style="color: #d99a26; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Sequenza 01</div>
        <h4 style="margin: 6px 0 8px 0; color: #f8fafc;">Accoglienza & Onboarding</h4>
        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0 0 12px 0;">3 step: credenziali immediate, metodo operativo (+24h), invito tavolo (+48h).</p>
        <span style="display: inline-block; background: rgba(34, 197, 94, 0.2); color: #4ade80; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">ATTIVO · 7.445 iscritti</span>
      </div>

      <div style="background: #020617; border: 1px solid #1e293b; padding: 16px; border-radius: 8px;">
        <div style="color: #38bdf8; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Sequenza 02</div>
        <h4 style="margin: 6px 0 8px 0; color: #f8fafc;">Recupero Carrello Abbandonato</h4>
        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0 0 12px 0;">Trigger su checkout pendente: promemoria dopo 1 ora, offerta supporto dopo 24 ore.</p>
        <span style="display: inline-block; background: rgba(56, 189, 248, 0.2); color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">EVENT-DRIVEN</span>
      </div>

      <div style="background: #020617; border: 1px solid #1e293b; padding: 16px; border-radius: 8px;">
        <div style="color: #a855f7; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Sequenza 03</div>
        <h4 style="margin: 6px 0 8px 0; color: #f8fafc;">Nurturing ad Alto Valore</h4>
        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0 0 12px 0;">Casi studio reali di autonomia e nuovi asset riservati nella Vault del Club.</p>
        <span style="display: inline-block; background: rgba(168, 85, 247, 0.2); color: #c084fc; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">SEGMENT: ENGAGED</span>
      </div>

      <div style="background: #020617; border: 1px solid #1e293b; padding: 16px; border-radius: 8px;">
        <div style="color: #f59e0b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Sequenza 04</div>
        <h4 style="margin: 6px 0 8px 0; color: #f8fafc;">Winback & Pulizia Dormienti</h4>
        <p style="font-size: 0.85rem; color: #94a3b8; margin: 0 0 12px 0;">Tutela della reputazione mittente: riattivazione o pulizia dopo 60gg di inattività.</p>
        <span style="display: inline-block; background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">SCHEDULE: LUNEDÌ</span>
      </div>

    </div>
  </div>

  <!-- ULTIMI EVENTI DEL FUNNEL -->
  <div style="background: #0b1120; border: 1px solid #1e293b; border-radius: 12px; padding: 24px;">
    <h3 style="margin-top: 0; color: #f8fafc; font-size: 1.2rem;">Registro Eventi in Tempo Reale (Event Store)</h3>
    <?php if (empty($stats['latest_events'])): ?>
      <p style="color: #64748b; font-size: 0.9rem;">Nessun evento recente registrato. Il motore è pronto a catturare interazioni.</p>
    <?php else: ?>
      <table style="width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 0.9rem;">
        <thead>
          <tr style="border-bottom: 1px solid #1e293b; text-align: left; color: #94a3b8;">
            <th style="padding: 10px;">Evento</th>
            <th style="padding: 10px;">Identificatore Utente</th>
            <th style="padding: 10px;">Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($stats['latest_events'] as $ev): ?>
            <tr style="border-bottom: 1px solid #0f172a; color: #cbd5e1;">
              <td style="padding: 10px;"><code style="color: #38bdf8;"><?=htmlspecialchars($ev['event_name'])?></code></td>
              <td style="padding: 10px;"><?=htmlspecialchars($ev['user_identifier'])?></td>
              <td style="padding: 10px; color: #64748b;"><?=htmlspecialchars($ev['timestamp'])?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>

<?php require '_footer.php'; ?>