<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$id = trim((string)($_GET['id'] ?? $_POST['assessment_sic_id'] ?? ''));

$st = db()->prepare("SELECT * FROM assessments WHERE sic_id = ? AND status = 'ACTIVE'");
$st->execute([$id]);
$a = $st->fetch();

if (!$a) {
    http_response_code(404);
    $pageTitle = 'Questionario Non Trovato';
    $metaDesc = 'Il questionario richiesto non è disponibile.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 700px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #FFFFFF; font-size: 1.6rem; margin-bottom: 16px;">Questionario Non Trovato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px;">
          L'autovalutazione cercata non è attiva o è stata archiviata.
        </p>
        <a class="btn primary" href="assessments.php">← Torna all'Elenco Questionari</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

if ((int)$a['professional_only'] === 1 && !has_role($u['sic_id'], ['SUPERADMIN', 'PROFESSIONAL', 'SERVITORE'])) {
    http_response_code(403);
    $pageTitle = 'Area Riservata · Questionario';
    $metaDesc = 'Questionario riservato a professionisti e servitori abilitati.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 700px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #c084fc; font-size: 1.6rem; margin-bottom: 16px;">Strumento ad Uso Professionale</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px;">
          Questo questionario richiede l'abilitazione di Servitore-Insegnante o Operatore Professionale.
        </p>
        <a class="btn primary" href="assessments.php">← Torna all'Elenco Questionari</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$cfg = json_decode((string)$a['config_json'], true) ?: [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $answers = [];
    $sum = 0;
    $n = 0;
    foreach ($cfg['items'] ?? [] as $it) {
        $k = $it['key'];
        $v = max(1, min(10, (int)($_POST['q'][$k] ?? 0)));
        if ($v) {
            $answers[$k] = $v;
            $sum += $v;
            $n++;
        }
    }
    if ($n > 0) {
        $score = round($sum / $n, 2);
        $sic = sic_id();
        db()->prepare("
            INSERT INTO assessment_sessions(
                sic_id, assessment_sic_id, user_sic_id, completed_at, score, 
                interpretation_code, raw_json, visibility
            ) VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?, 'TREND_ONLY', ?, 'PRIVATE')
        ")->execute([
            $sic, $a['sic_id'], $u['sic_id'], $score,
            json_encode(['answers' => $answers, 'version' => 1], JSON_UNESCAPED_UNICODE)
        ]);
        audit($u['sic_id'], 'ASSESSMENT_COMPLETE', $sic, ['assessment' => $a['code']]);
        $saved = true;
    }
}

$hist = db()->prepare("
    SELECT score, completed_at 
    FROM assessment_sessions 
    WHERE assessment_sic_id = ? AND user_sic_id = ? AND completed_at IS NOT NULL 
    ORDER BY completed_at DESC 
    LIMIT 8
");
$hist->execute([$a['sic_id'], $u['sic_id']]);
$history = $hist->fetchAll();

$pageTitle = $a['title'] . ' · Autovalutazione';
$metaDesc = 'Compilazione privata di consapevolezza personale per ' . $a['title'] . '.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 800px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 28px;">
    <div style="margin-bottom: 8px;">
      <a href="assessments.php" style="color: #D4AF37; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        ← Torna ai Questionari
      </a>
    </div>
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Autovalutazione Privata · Non Diagnostica
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.4rem); font-weight: 800; margin: 8px 0 10px; color: #FFFFFF;">
      <?= h($a['title']) ?>
    </h1>
    <p style="color: var(--muted, #a1a1aa); font-size: 1rem; line-height: 1.6;">
      <?= h($cfg['disclaimer'] ?? 'Fotografia personale di consapevolezza. Compila liberamente secondo il tuo sentire odierno.') ?>
    </p>
  </section>

  <?php if ($saved): ?>
    <div class="alert success" style="background: rgba(74, 222, 128, 0.12); border: 1px solid rgba(74, 222, 128, 0.4); color: #4ade80; padding: 18px 20px; border-radius: 14px; margin-bottom: 28px; font-weight: 600;">
      ✓ Riflessione registrata nel tuo spazio personale. I dati rimangono riservati e non comportano giudizi o punteggi di merito.
    </div>
  <?php endif; ?>

  <form method="post" class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 32px 24px; margin-bottom: 32px; display: flex; flex-direction: column; gap: 24px;">
    <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="assessment_sic_id" value="<?= h($a['sic_id']) ?>">

    <?php foreach ($cfg['items'] ?? [] as $it): 
      $k = $it['key'];
    ?>
      <div style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 20px;">
        <label for="range-<?= h($k) ?>" style="display: block; color: #FFFFFF; font-weight: 600; font-size: 1rem; margin-bottom: 10px;">
          <?= h($it['label']) ?>
        </label>
        <div style="display: flex; align-items: center; gap: 16px;">
          <input 
            id="range-<?= h($k) ?>"
            type="range" 
            min="1" 
            max="10" 
            value="5" 
            name="q[<?= h($k) ?>]" 
            style="flex: 1; accent-color: #D4AF37; min-height: 44px;"
            oninput="document.getElementById('out-<?= h($k) ?>').textContent = this.value"
          >
          <span id="out-<?= h($k) ?>" style="font-size: 1.2rem; font-weight: 800; color: #FFF2B2; min-width: 32px; text-align: center;">
            5
          </span>
        </div>
      </div>
    <?php endforeach; ?>

    <button type="submit" class="btn primary" style="align-self: flex-start; min-height: 44px; padding: 0 28px; border-radius: 12px; font-weight: 700; cursor: pointer;">
      Salva Riflessione Personale
    </button>
  </form>

  <?php if (!empty($history)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 24px;">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">
        Storico Personale nel Tempo
      </h2>
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($history as $h): ?>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: rgba(255,255,255,0.02); border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
            <span style="color: #a1a1aa; font-size: 0.9rem;"><?= h(substr((string)$h['completed_at'], 0, 10)) ?></span>
            <span style="font-weight: 700; color: #FFF2B2;"><?= h((string)$h['score']) ?> / 10</span>
          </div>
        <?php endforeach; ?>
      </div>
      <p style="color: #71717a; font-size: 0.8rem; margin-top: 14px; margin-bottom: 0;">
        I dati indicano esclusivamente l'andamento soggettivo dichiarato nel tempo.
      </p>
    </section>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>
