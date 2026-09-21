<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_admin();

$rules = db()->query("
    SELECT * FROM hudolin_rules 
    WHERE status = 'ACTIVE' 
    ORDER BY CASE policy_class 
        WHEN 'OFFICIAL_REFERENCE' THEN 1 
        WHEN 'PROJECT_POLICY' THEN 2 
        ELSE 3 
    END, id
")->fetchAll();

$pageTitle = 'Hudolin Core · Metodologia e Regole';
$metaDesc = 'Registro metodologico e governance delle regole ecologico-sociali del Metodo Hudolin.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Methodology Core & Governance
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      Hudolin Core & Fonti Metodologiche
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
      Il sistema garantisce la trasparenza e la separazione rigorosa delle fonti: le linee guida ufficiali dell'Approccio Ecologico-Sociale sono distinte dalle policy operative del portale.
    </p>
  </section>

  <!-- Griglia Regole e Fonti -->
  <div class="rules-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 32px;">
    <?php foreach ($rules as $r): 
      $isOfficial = ($r['policy_class'] === 'OFFICIAL_REFERENCE');
    ?>
      <article class="card" style="background: #101116; border: 1px solid <?= $isOfficial ? 'rgba(74, 222, 128, 0.3)' : 'rgba(212, 175, 55, 0.25)' ?>; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 0.75rem; font-weight: 700; color: <?= $isOfficial ? '#4ade80' : '#D4AF37' ?>; background: <?= $isOfficial ? 'rgba(74, 222, 128, 0.12)' : 'rgba(212, 175, 55, 0.12)' ?>; padding: 4px 10px; border-radius: 12px;">
              <?= h($r['policy_class'] ?: 'REGOLA') ?> · v<?= h((string)$r['version']) ?>
            </span>
            <span style="font-family: monospace; font-size: 0.75rem; color: #71717a;"><?= h($r['code']) ?></span>
          </div>

          <h2 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin: 0 0 10px; line-height: 1.3;">
            <?= h($r['title']) ?>
          </h2>
          <p style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px;">
            <?= h($r['description']) ?>
          </p>
        </div>

        <div>
          <div style="font-size: 0.85rem; color: #a1a1aa; margin-bottom: 10px;">
            Autorità/Fonte: <strong style="color: #FFF2B2;"><?= h($r['authority'] ?: 'Metodologia Hudolin') ?></strong>
          </div>
          <?php if (!empty($r['source_url'])): ?>
            <a href="<?= h($r['source_url']) ?>" target="_blank" rel="noopener" style="color: #D4AF37; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
              Fonte metodologica accreditata ↗
            </a>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <section class="card" style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.3); border-radius: 16px; padding: 24px;">
    <h3 style="color: #fde047; font-size: 1.1rem; font-weight: 700; margin: 0 0 8px;">
      Principio di Non Confusione delle Discipline
    </h3>
    <p style="color: #d1d5db; font-size: 0.95rem; line-height: 1.6; margin: 0;">
      Ogni metodo, approccio somatico, pratica di respiro o protocollo formativo mantiene esplicita la propria origine. Il Metodo Hudolin è fondato sulla solidarietà e corresponsabilità tra famiglie nei Club territoriali.
    </p>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>
