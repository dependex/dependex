<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$tpls = db()->query("SELECT * FROM document_templates WHERE status='ACTIVE' ORDER BY category, title")->fetchAll();

$pageTitle = 'Document Factory · Modulistica Digitale';
$metaDesc = 'Modulistica digitale standardizzata per Club, servitori-insegnanti e associazioni: zero carta, marcatura SIC-ID e conformità.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Digital Governance & Paperless
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      Document Factory & Modulistica
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
      Template uniformati per la vita associativa, verbali di assemblea, accordi di ospitalità e richieste istituzionali. Ogni documento generato riceve marcatura crittografica e identificativo SIC univoco.
    </p>
  </section>

  <?php if (empty($tpls)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 32px; text-align: center;">
      <p style="color: #a1a1aa;">Nessun modello documentale attivo al momento.</p>
    </section>
  <?php else: ?>
    <div class="doc-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-bottom: 32px;">
      <?php foreach ($tpls as $t): ?>
        <a class="card doc-card" href="document-create.php?template=<?= urlencode((string)$t['code']) ?>" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 16px; padding: 22px; text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s ease, border-color 0.2s ease;">
          <div>
            <div style="font-size: 2rem; margin-bottom: 10px;">📄</div>
            <span style="font-size: 0.75rem; color: #D4AF37; font-weight: 700; text-transform: uppercase;">
              <?= h($t['category'] ?? 'MODELLO') ?>
            </span>
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #FFFFFF; margin: 4px 0 8px; line-height: 1.3;">
              <?= h($t['title']) ?>
            </h2>
            <p style="color: #a1a1aa; font-size: 0.85rem; line-height: 1.5; margin-bottom: 16px;">
              <?= h($t['description'] ?? 'Compilazione guidata ed esportazione PDF.') ?>
            </p>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; pt: 12px; border-top: 1px solid rgba(255,255,255,0.06);">
            <span style="font-family: monospace; font-size: 0.75rem; color: #71717a;"><?= h($t['code']) ?></span>
            <span style="color: #FFF2B2; font-weight: 700; font-size: 0.9rem;">Compila →</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <section class="card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 16px; padding: 20px;">
    <strong style="color: #FFF2B2;">Integrità SHA-256 e Conservazione</strong>
    <p style="color: var(--muted, #a1a1aa); font-size: 0.9rem; line-height: 1.6; margin: 6px 0 0;">
      I documenti generati sono salvati con firma hash per attestarne la data certa e l'inalterabilità nei rapporti con Enti Locali e Terzo Settore.
    </p>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>