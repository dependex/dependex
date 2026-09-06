<?php
require_once 'bootstrap.php';
$u = require_login();
require_once __DIR__ . '/modules/cortex/cortex_service.php';

$cortex = new \App\Services\CortexService();

// Handle manual learning trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'learn') {
    header('Content-Type: application/json');
    $res = $cortex->triggerLearning();
    echo json_encode($res);
    exit;
}

$stats = $cortex->getStats();
$knowledge = $cortex->getKnowledgeStatus();
$recent = $cortex->getRecentInteractions(12);

$pageTitle = 'Cortex Dashboard · Company Brain';
require '_header.php';
?>
<main class="page py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <h1 class="h2 mb-1" style="display:flex;align-items:center;gap:10px;color:#FFFFFF;font-family:var(--font-serif);">
        <?=dx_icon('brain', '', 28)?> Cortex Dashboard
      </h1>
      <p style="color:var(--text-muted);margin:0;">Monitoraggio del cervello digitale e dello stato cognitivo dell'ecosistema.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="cortex.php" class="btn" style="border:1px solid var(--border);color:var(--gold-primary);display:inline-flex;align-items:center;gap:6px;">
        <?=dx_icon('message-circle', '', 16)?> Apri Chat
      </a>
      <button id="btnLearnTrigger" class="btn primary" style="display:inline-flex;align-items:center;gap:6px;">
        <?=dx_icon('refresh', '', 16)?> Forza Apprendimento
      </button>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100" style="background:var(--card);border:1px solid var(--border);">
        <span style="color:var(--text-muted);font-size:12px;">Interazioni Totali</span>
        <h3 class="mt-2 mb-0" style="color:#FFFFFF;font-weight:800;"><?=number_format((int)($stats['total_interactions'] ?? 0))?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100" style="background:var(--card);border:1px solid var(--border);">
        <span style="color:var(--text-muted);font-size:12px;">Interazioni Oggi</span>
        <h3 class="mt-2 mb-0" style="color:var(--gold-primary);font-weight:800;"><?=number_format((int)($stats['today_interactions'] ?? 0))?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100" style="background:var(--card);border:1px solid var(--border);">
        <span style="color:var(--text-muted);font-size:12px;">Feedback Positivo</span>
        <h3 class="mt-2 mb-0" style="color:#FFFFFF;font-weight:800;"><?=number_format((int)($stats['positive_feedback'] ?? 0))?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100" style="background:var(--card);border:1px solid var(--border);">
        <span style="color:var(--text-muted);font-size:12px;">Nodi di Conoscenza</span>
        <h3 class="mt-2 mb-0" style="color:var(--gold-primary);font-weight:800;"><?=number_format((int)($knowledge['graph_nodes'] ?? 0))?></h3>
      </div>
    </div>
  </div>

  <!-- Knowledge Status & Top Agents -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card h-100 p-4" style="background:var(--card);border:1px solid var(--border);">
        <h5 style="color:var(--gold-primary);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <?=dx_icon('book-open', '', 20)?> Stato della Conoscenza
        </h5>
        <div style="display:flex;flex-direction:column;gap:12px;">
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:8px;">
            <span style="color:#FFFFFF;">Pilastri di Contesto (Markdown)</span>
            <strong style="color:var(--gold-light);"><?= (int)($knowledge['context_files'] ?? 0) ?> files</strong>
          </div>
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:8px;">
            <span style="color:#FFFFFF;">Nodi Grafo Cognitivo</span>
            <strong style="color:var(--gold-light);"><?= (int)($knowledge['graph_nodes'] ?? 0) ?> nodi</strong>
          </div>
          <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.06);padding-bottom:8px;">
            <span style="color:#FFFFFF;">File di Apprendimento Autonomo</span>
            <strong style="color:var(--gold-light);"><?= (int)($knowledge['learnings'] ?? 0) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span style="color:#FFFFFF;">Ultimo Aggiornamento</span>
            <span style="color:var(--text-muted);font-size:12px;"><?= h($knowledge['last_update'] ?? 'N/A') ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 p-4" style="background:var(--card);border:1px solid var(--border);">
        <h5 style="color:var(--gold-primary);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <?=dx_icon('brain', '', 20)?> Agenti Cognitivi Più Usati
        </h5>
        <div>
          <?php if (empty($stats['top_agents'])): ?>
            <p style="color:var(--text-muted);">Nessuna interazione registrata al momento.</p>
          <?php else: ?>
            <?php foreach ($stats['top_agents'] as $ag): ?>
              <?php
                $pct = $stats['total_interactions'] > 0
                  ? min(100, round(($ag['count'] / $stats['total_interactions']) * 100))
                  : 0;
              ?>
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                  <span style="color:#FFFFFF;text-transform:capitalize;font-weight:600;"><?= h($ag['agent']) ?></span>
                  <span style="color:var(--text-muted);"><?= (int)$ag['count'] ?> (<?= $pct ?>%)</span>
                </div>
                <div class="progress" style="height: 6px;background:rgba(255,255,255,0.1);border-radius:999px;">
                  <div style="height:100%;width: <?= $pct ?>%;background:var(--gold-primary);border-radius:999px;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card mb-4 p-4" style="background:var(--card);border:1px solid var(--border);">
    <h5 style="color:var(--gold-primary);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
      <?=dx_icon('zap', '', 20)?> Azioni e Skill Rapide
    </h5>
    <div class="row g-3">
      <div class="col-6 col-md-3">
        <a href="cortex.php?q=<?=urlencode("Mostrami l'impronta aziendale")?>" class="card p-3 text-start h-100" style="background:#16171f;border:1px solid var(--border);text-decoration:none;color:inherit;">
          <div style="color:var(--gold-primary);margin-bottom:8px;"><?=dx_icon('compass', '', 24)?></div>
          <strong style="color:#FFFFFF;">Impronta</strong>
          <small style="display:block;color:var(--text-muted);margin-top:4px;">Contesto aziendale</small>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="cortex.php?q=<?=urlencode("Mappa del tesoro e automazioni")?>" class="card p-3 text-start h-100" style="background:#16171f;border:1px solid var(--border);text-decoration:none;color:inherit;">
          <div style="color:var(--gold-primary);margin-bottom:8px;"><?=dx_icon('sparkles', '', 24)?></div>
          <strong style="color:#FFFFFF;">Mappa Tesoro</strong>
          <small style="display:block;color:var(--text-muted);margin-top:4px;">Mappatura automazioni</small>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="cortex.php?q=<?=urlencode("Attiva il primo ingranaggio")?>" class="card p-3 text-start h-100" style="background:#16171f;border:1px solid var(--border);text-decoration:none;color:inherit;">
          <div style="color:var(--gold-primary);margin-bottom:8px;"><?=dx_icon('activity', '', 24)?></div>
          <strong style="color:#FFFFFF;">1° Ingranaggio</strong>
          <small style="display:block;color:var(--text-muted);margin-top:4px;">Prima skill attiva</small>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="cortex.php" class="card p-3 text-start h-100" style="background:#16171f;border:1px solid var(--border);text-decoration:none;color:inherit;">
          <div style="color:var(--gold-primary);margin-bottom:8px;"><?=dx_icon('message-circle', '', 24)?></div>
          <strong style="color:#FFFFFF;">Chat Cortex</strong>
          <small style="display:block;color:var(--text-muted);margin-top:4px;">Dialogo Company Brain</small>
        </a>
      </div>
    </div>
  </div>

  <!-- Recent Interactions Log -->
  <div class="card p-4" style="background:var(--card);border:1px solid var(--border);">
    <h5 style="color:var(--gold-primary);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
      <?=dx_icon('clock', '', 20)?> Interazioni Recenti
    </h5>
    <?php if (empty($recent)): ?>
      <p style="color:var(--text-muted);">Nessuna interazione recente registrata.</p>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
          <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,0.1);color:var(--text-muted);text-align:left;">
              <th style="padding:10px 12px;">Agente</th>
              <th style="padding:10px 12px;">Task / Domanda</th>
              <th style="padding:10px 12px;">Feedback</th>
              <th style="padding:10px 12px;">Data</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $item): ?>
              <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                <td style="padding:10px 12px;">
                  <span class="badge" style="background:rgba(212,175,55,0.15);border:1px solid var(--border);color:var(--gold-primary);padding:3px 8px;border-radius:12px;font-size:11px;">
                    <?= h($item['agent'] ?? 'support') ?>
                  </span>
                </td>
                <td style="padding:10px 12px;color:#FFFFFF;max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                  <?= h($item['task']) ?>
                </td>
                <td style="padding:10px 12px;">
                  <?php if (($item['feedback'] ?? 0) > 0): ?>
                    <span style="color:var(--gold-primary);"><?=dx_icon('check-circle', '', 14)?> Positivo</span>
                  <?php elseif (($item['feedback'] ?? 0) < 0): ?>
                    <span style="color:var(--text-muted);"><?=dx_icon('alert-triangle', '', 14)?> Da rivedere</span>
                  <?php else: ?>
                    <span style="color:var(--text-muted);">—</span>
                  <?php endif; ?>
                </td>
                <td style="padding:10px 12px;color:var(--text-muted);"><?= h(substr($item['created_at'], 0, 16)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>

<script>
document.getElementById('btnLearnTrigger')?.addEventListener('click', async function() {
    const btn = this;
    const oldText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<?=dx_icon('clock', '', 16)?> Apprendimento in corso…';

    try {
        const formData = new FormData();
        formData.append('action', 'learn');
        const res = await fetch('cortex-dashboard.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alert('Ciclo di apprendimento completato con successo.');
            location.reload();
        } else {
            alert('Nota: ' + (data.error || data.reason || 'Completato con avvisi'));
            location.reload();
        }
    } catch(err) {
        alert('Errore durante la chiamata di apprendimento.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = oldText;
    }
});
</script>

<?php require '_footer.php'; ?>
