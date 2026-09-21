<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$scope = user_club_sic($u['sic_id']);

if (!acl_can($u['sic_id'], 'workflow', 'READ', $scope, $u['sic_id']) && !has_role($u['sic_id'], 'SUPERADMIN')) {
    http_response_code(403);
    $pageTitle = 'Accesso Riservato · Workflow';
    $metaDesc = 'Permessi insufficienti per accedere ai workflow operativi.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 700px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #f87171; font-size: 1.6rem; margin-bottom: 12px;">Accesso Riservato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px;">
          Non disponi dei privilegi per visualizzare o eseguire i workflow di questo presidio.
        </p>
        <a class="btn primary" href="dashboard.php">← Torna alla Dashboard</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$forms = db()->query("SELECT * FROM form_templates WHERE status='ACTIVE' ORDER BY title")->fetchAll();

$pageTitle = 'Workflow Operativi · Process Engine';
$metaDesc = 'Flussi di lavoro, protocolli di iscrizione e automazioni territoriali DEPENDEX.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Process & Task Engine
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      Workflow & Flussi di Lavoro
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
      Procedure guidate per l'accoglienza di nuove famiglie, protocolli di ospitalità, certificazione delle presenze e passaggi di governance.
    </p>
  </section>

  <div class="workflow-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 32px;">
    <?php foreach ($forms as $f): ?>
      <article class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 16px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <span style="font-size: 0.75rem; font-weight: 700; color: #D4AF37; background: rgba(212, 175, 55, 0.12); padding: 4px 10px; border-radius: 12px;">
            <?= h($f['code']) ?>
          </span>
          <h2 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin: 12px 0 8px; line-height: 1.3;">
            <?= h($f['title']) ?>
          </h2>
          <p style="color: #a1a1aa; font-size: 0.9rem; margin-bottom: 20px;">
            Tipo di flusso: <strong style="color: #d1d5db;"><?= h((string)$f['output_type']) ?></strong>
          </p>
        </div>
        <div>
          <a class="btn primary small" href="form-fill.php?form=<?= urlencode((string)$f['sic_id']) ?>" style="display: block; text-align: center; width: 100%; min-height: 40px; line-height: 40px; border-radius: 10px; text-decoration: none; font-weight: 700;">
            Avvia Flusso di Lavoro →
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<?php require __DIR__ . '/_footer.php'; ?>