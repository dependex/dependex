<?php
require_once 'bootstrap.php';
$u = require_login();
require_once __DIR__ . '/modules/cortex/CortexService.php';

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
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <h1 class="h2 mb-1">🧠 Cortex Dashboard</h1>
      <p class="text-muted mb-0">Monitoraggio del cervello digitale e dello stato cognitivo dell'ecosistema.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="cortex.php" class="btn btn-outline-primary">💬 Apri Chat</a>
      <button id="btnLearnTrigger" class="btn btn-primary">🔄 Forza Apprendimento</button>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100 shadow-sm border-0">
        <span class="text-muted small">Interazioni Totali</span>
        <h3 class="mt-2 mb-0"><?=number_format((int)($stats['total_interactions'] ?? 0))?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100 shadow-sm border-0">
        <span class="text-muted small">Interazioni Oggi</span>
        <h3 class="mt-2 mb-0 text-primary"><?=number_format((int)($stats['today_interactions'] ?? 0))?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100 shadow-sm border-0">
        <span class="text-muted small">Feedback Positivo</span>
        <h3 class="mt-2 mb-0 text-success"><?=number_format((int)($stats['positive_feedback'] ?? 0))?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card p-3 h-100 shadow-sm border-0">
        <span class="text-muted small">Nodi di Conoscenza</span>
        <h3 class="mt-2 mb-0 text-warning"><?=number_format((int)($knowledge['graph_nodes'] ?? 0))?></h3>
      </div>
    </div>
  </div>

  <!-- Knowledge Status & Top Agents -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-transparent border-0 pt-3 px-3">
          <h5 class="card-title mb-0">📚 Stato della Conoscenza</h5>
        </div>
        <div class="card-body px-3">
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span>Pillastri di Contesto (Markdown)</span>
              <strong><?= (int)($knowledge['context_files'] ?? 0) ?> files</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span>Nodi Grafo Cognitivo</span>
              <strong><?= (int)($knowledge['graph_nodes'] ?? 0) ?> nodi</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span>File di Apprendimento Autonomo</span>
              <strong><?= (int)($knowledge['learnings'] ?? 0) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span>Ultimo Aggiornamento</span>
              <span class="text-muted small"><?= h($knowledge['last_update'] ?? 'N/A') ?></span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-transparent border-0 pt-3 px-3">
          <h5 class="card-title mb-0">🤖 Agenti Cognitivi Più Usati</h5>
        </div>
        <div class="card-body px-3">
          <?php if (empty($stats['top_agents'])): ?>
            <p class="text-muted">Nessuna interazione registrata al momento.</p>
          <?php else: ?>
            <?php foreach ($stats['top_agents'] as $ag): ?>
              <?php
                $pct = $stats['total_interactions'] > 0
                  ? min(100, round(($ag['count'] / $stats['total_interactions']) * 100))
                  : 0;
              ?>
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="fw-semibold text-capitalize"><?= h($ag['agent']) ?></span>
                  <span class="small text-muted"><?= (int)$ag['count'] ?> (<?= $pct ?>%)</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct ?>%"></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-transparent border-0 pt-3 px-3">
      <h5 class="card-title mb-0">⚡ Azioni e Skill Rapide</h5>
    </div>
    <div class="card-body px-3">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <a href="cortex.php?q=<?=urlencode("Mostrami l'impronta aziendale")?>" class="btn btn-light w-100 p-3 text-start border">
            <span class="fs-4 d-block mb-1">✍️</span>
            <strong>Impronta</strong>
            <small class="d-block text-muted">Contesto aziendale</small>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="cortex.php?q=<?=urlencode("Mappa del tesoro e automazioni")?>" class="btn btn-light w-100 p-3 text-start border">
            <span class="fs-4 d-block mb-1">🗺️</span>
            <strong>Mappa Tesoro</strong>
            <small class="d-block text-muted">Mappatura automazioni</small>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="cortex.php?q=<?=urlencode("Attiva il primo ingranaggio")?>" class="btn btn-light w-100 p-3 text-start border">
            <span class="fs-4 d-block mb-1">⚙️</span>
            <strong>1° Ingranaggio</strong>
            <small class="d-block text-muted">Prima skill attiva</small>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="cortex.php" class="btn btn-light w-100 p-3 text-start border">
            <span class="fs-4 d-block mb-1">💬</span>
            <strong>Chat Cortex</strong>
            <small class="d-block text-muted">Dialogo Company Brain</small>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Interactions Log -->
  <div class="card shadow-sm border-0">
    <div class="card-header bg-transparent border-0 pt-3 px-3">
      <h5 class="card-title mb-0">🔄 Interazioni Recenti</h5>
    </div>
    <div class="card-body px-3">
      <?php if (empty($recent)): ?>
        <p class="text-muted">Nessuna interazione recente registrata.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr class="text-muted small">
                <th>Agente</th>
                <th>Task / Domanda</th>
                <th>Feedback</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $item): ?>
                <tr>
                  <td><span class="badge bg-secondary text-uppercase"><?= h($item['agent'] ?? 'support') ?></span></td>
                  <td><span class="text-truncate d-inline-block" style="max-width: 320px;"><?= h($item['task']) ?></span></td>
                  <td>
                    <?php if (($item['feedback'] ?? 0) > 0): ?>
                      <span class="text-success">👍</span>
                    <?php elseif (($item['feedback'] ?? 0) < 0): ?>
                      <span class="text-danger">👎</span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="small text-muted"><?= h(substr($item['created_at'], 0, 16)) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
document.getElementById('btnLearnTrigger')?.addEventListener('click', async function() {
    const btn = this;
    const oldText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ Apprendimento in corso…';

    try {
        const formData = new FormData();
        formData.append('action', 'learn');
        const res = await fetch('cortex-dashboard.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ Ciclo di apprendimento completato!');
            location.reload();
        } else {
            alert('⚠️ Nota: ' + (data.error || data.reason || 'Completato con avvisi'));
            location.reload();
        }
    } catch(err) {
        alert('❌ Errore durante la chiamata di apprendimento.');
    } finally {
        btn.disabled = false;
        btn.textContent = oldText;
    }
});
</script>

<?php require '_footer.php'; ?>
