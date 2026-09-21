<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$id = trim((string)($_GET['id'] ?? ''));

$st = db()->prepare('SELECT * FROM academy_courses WHERE sic_id = ?');
$st->execute([$id]);
$c = $st->fetch();

if (!$c) {
    http_response_code(404);
    $pageTitle = 'Corso Non Trovato · Academy';
    $metaDesc = 'Il corso richiesto non è presente nel catalogo o è stato aggiornato.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 800px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #FFFFFF; font-size: 1.8rem; margin-bottom: 16px;">Corso Non Trovato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px; line-height: 1.6;">
          Il percorso formativo richiesto non è disponibile o è stato rilocato. Puoi consultare il catalogo completo dell'Academy per scegliere un altro percorso.
        </p>
        <a class="btn primary" href="academy.php" style="display: inline-block; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700;">
          ← Torna al Catalogo Corsi
        </a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// Assicura iscrizione
db()->prepare('INSERT OR IGNORE INTO academy_enrollments(sic_id, user_sic_id, course_sic_id) VALUES(?, ?, ?)')
    ->execute([sic_id(), $u['sic_id'], $id]);

$en = db()->prepare('SELECT * FROM academy_enrollments WHERE user_sic_id = ? AND course_sic_id = ?');
$en->execute([$u['sic_id'], $id]);
$er = $en->fetch();

$ls = db()->prepare('
    SELECT al.*, CASE WHEN alp.status = "COMPLETED" THEN 1 ELSE 0 END as completed 
    FROM academy_lessons al 
    LEFT JOIN academy_lesson_progress alp ON alp.lesson_sic_id = al.sic_id AND alp.enrollment_sic_id = ? 
    WHERE al.course_sic_id = ? 
    ORDER BY al.lesson_order
');
$ls->execute([$er['sic_id'] ?? '', $id]);
$lessons = $ls->fetchAll();

$pageTitle = $c['title'] . ' · OLTRE Academy';
$metaDesc = mb_substr(strip_tags((string)$c['description']), 0, 155);

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 900px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 28px;">
    <div style="margin-bottom: 12px;">
      <a href="academy.php" style="color: #D4AF37; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        ← Torna all'Academy
      </a>
    </div>
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      <?= h($c['category']) ?>
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF; line-height: 1.25;">
      <?= h($c['title']) ?>
    </h1>
    <p style="color: var(--muted, #a1a1aa); font-size: 1.05rem; line-height: 1.6;">
      <?= h($c['description']) ?>
    </p>
  </section>

  <?php if (empty($lessons)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 32px; text-align: center;">
      <p style="color: #a1a1aa;">Le lezioni di questo modulo sono in fase di aggiornamento metodologico.</p>
    </section>
  <?php else: ?>
    <div class="lessons-stack" style="display: flex; flex-direction: column; gap: 20px;">
      <?php foreach ($lessons as $l): ?>
        <article class="lesson-card" style="background: #101116; border: 1px solid <?= $l['completed'] ? 'rgba(74, 222, 128, 0.3)' : 'rgba(255, 255, 255, 0.08)' ?>; border-radius: 16px; padding: 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #D4AF37; background: rgba(212, 175, 55, 0.12); padding: 4px 10px; border-radius: 12px;">
              Lezione <?= h((string)$l['lesson_order']) ?> · <?= h($l['lesson_type']) ?>
            </span>
            <span style="font-size: 0.85rem; color: #a1a1aa;">
              Durata: <?= h((string)$l['duration_min']) ?> min · +<?= h((string)$l['drx_reward']) ?> DRX
            </span>
          </div>

          <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 12px; color: #FFFFFF;">
            <?= h($l['title']) ?>
          </h2>

          <div class="lesson-content" style="color: #d1d5db; line-height: 1.65; margin-bottom: 20px;">
            <?= nl2br(strip_tags((string)$l['content_html'], '<p><br><b><strong><i><em><ul><ol><li><a><blockquote>')) ?>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; pt: 16px; border-top: 1px solid rgba(255,255,255,0.06);">
            <?php if ($l['completed']): ?>
              <div style="display: inline-flex; align-items: center; gap: 8px; color: #4ade80; font-weight: 700; font-size: 0.95rem;">
                <span>✓ Completata</span>
              </div>
            <?php else: ?>
              <form method="post" action="action.php" style="margin: 0;">
                <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="academy_lesson_complete">
                <input type="hidden" name="lesson_sic_id" value="<?= h($l['sic_id']) ?>">
                <input type="hidden" name="return" value="academy-course.php?id=<?= urlencode($id) ?>">
                <button type="submit" class="btn primary small" style="min-height: 40px; padding: 0 18px; border-radius: 10px; font-weight: 700; cursor: pointer;">
                  Segna come Completata ✓
                </button>
              </form>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>