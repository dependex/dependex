<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$public = isset($_GET['public']);
$u = $public ? null : current_user();

$courses = db()->query("SELECT * FROM academy_courses WHERE status='ACTIVE' ORDER BY category, title")->fetchAll();

$pageTitle = 'Academy Formazione Continua';
$metaDesc = 'Percorsi di formazione continua per famiglie, servitori-insegnanti e comunità su Metodo Hudolin, benessere e stili di vita sobri.';
$canonicalUrl = 'https://' . (site_brand()['domain'] ?? 'dependex.social') . '/academy.php';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1200px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <div>
      <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
        OLTRE Academy · Bene Comune
      </span>
      <h1 style="font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
        Impara. Applica. Condividi.
      </h1>
      <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
        Percorsi formativi ed esperienziali su Metodo Hudolin, ecologia delle relazioni, ascolto attivo, famiglia e stili di vita sobri.
      </p>
    </div>
  </section>

  <?php if (empty($courses)): ?>
    <section class="card" style="background: rgba(16, 17, 22, 0.85); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 16px; padding: 32px; text-align: center;">
      <p style="color: var(--muted, #a1a1aa);">Al momento non ci sono corsi attivi in catalogo. Torna presto a trovarci.</p>
    </section>
  <?php else: ?>
    <div class="course-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
      <?php foreach ($courses as $c): 
        $en = null;
        if ($u) {
          $st = db()->prepare('SELECT * FROM academy_enrollments WHERE user_sic_id = ? AND course_sic_id = ?');
          $st->execute([$u['sic_id'], $c['sic_id']]);
          $en = $st->fetch();
        }
        $progressPct = (int)($en['progress_pct'] ?? 0);
      ?>
        <article class="course-card" style="background: #101116; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s ease, border-color 0.2s ease;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <span class="course-cat" style="background: rgba(212, 175, 55, 0.12); color: #D4AF37; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(212, 175, 55, 0.3);">
                <?= h($c['category']) ?>
              </span>
              <span style="font-size: 0.8rem; color: #a1a1aa;">Rank <?= h($c['rank_required']) ?></span>
            </div>

            <h2 style="font-size: 1.3rem; font-weight: 700; margin: 0 0 10px; color: #FFFFFF; line-height: 1.3;">
              <?= h($c['title']) ?>
            </h2>
            <p style="color: #d1d5db; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px;">
              <?= h($c['description']) ?>
            </p>
          </div>

          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 0.85rem; color: #a1a1aa;">
              <span>Riconoscimento: <strong style="color: #FFF2B2;">+<?= h((string)$c['drx_reward']) ?> DRX</strong></span>
              <?php if ($u && $en): ?>
                <span>Progresso: <strong style="color: #D4AF37;"><?= $progressPct ?>%</strong></span>
              <?php endif; ?>
            </div>

            <?php if ($u): ?>
              <?php if ($en): ?>
                <div style="background: rgba(255,255,255,0.08); border-radius: 8px; height: 6px; overflow: hidden; margin-bottom: 16px;">
                  <div style="background: linear-gradient(90deg, #D4AF37, #FFF2B2); width: <?= min(100, max(0, $progressPct)) ?>%; height: 100%;"></div>
                </div>
                <a class="btn primary" href="academy-course.php?id=<?= urlencode($c['sic_id']) ?>" style="display: block; text-align: center; width: 100%; min-height: 44px; line-height: 44px; padding: 0 16px; border-radius: 12px; text-decoration: none; font-weight: 700;">
                  Continua Corso →
                </a>
              <?php else: ?>
                <form method="post" action="action.php" style="margin: 0;">
                  <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
                  <input type="hidden" name="action" value="academy_enroll">
                  <input type="hidden" name="course_sic_id" value="<?= h($c['sic_id']) ?>">
                  <input type="hidden" name="return" value="academy-course.php?id=<?= urlencode($c['sic_id']) ?>">
                  <button type="submit" class="btn primary" style="width: 100%; min-height: 44px; padding: 0 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                    Iscriviti e Inizia
                  </button>
                </form>
              <?php endif; ?>
            <?php else: ?>
              <a class="btn primary" href="login.php?redirect=academy-course.php?id=<?= urlencode($c['sic_id']) ?>" style="display: block; text-align: center; width: 100%; min-height: 44px; line-height: 44px; padding: 0 16px; border-radius: 12px; text-decoration: none; font-weight: 700;">
                Accedi per Frequentare
              </a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>