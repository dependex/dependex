<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_admin();
$forms = db()->query("SELECT * FROM form_templates WHERE status='ACTIVE' ORDER BY title")->fetchAll();

$pageTitle = 'Form Builder · Moduli No-Code';
$metaDesc = 'Gestione e compilazione moduli no-code per la vita associativa, il tesseramento e le comunicazioni di rete.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      No-Code Form Engine
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      Form Factory & Raccolta Dati
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
      Moduli standardizzati, accessibili e mobile-first, collegati direttamente ai workflow di iscrizione, feedback e gestione territoriale.
    </p>
  </section>

  <?php if (empty($forms)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 32px; text-align: center;">
      <p style="color: #a1a1aa;">Nessun modulo configurato al momento.</p>
    </section>
  <?php else: ?>
    <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
      <?php foreach ($forms as $f): 
        $s = json_decode((string)$f['schema_json'], true) ?: [];
        $fieldsCount = count($s['fields'] ?? []);
      ?>
        <article class="card form-card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 16px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <span style="font-size: 0.75rem; font-weight: 700; color: #D4AF37; background: rgba(212, 175, 55, 0.12); padding: 4px 10px; border-radius: 12px;">
                <?= h($f['code']) ?>
              </span>
              <span style="font-size: 0.8rem; color: #a1a1aa;"><?= $fieldsCount ?> campi</span>
            </div>

            <h2 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin: 0 0 8px; line-height: 1.3;">
              <?= h($f['title']) ?>
            </h2>
            <p style="color: #a1a1aa; font-size: 0.9rem; margin-bottom: 20px;">
              Tipo di output: <strong style="color: #d1d5db;"><?= h((string)$f['output_type']) ?></strong>
            </p>
          </div>

          <div>
            <a class="btn primary small" href="form-fill.php?form=<?= urlencode((string)$f['sic_id']) ?>" style="display: block; text-align: center; width: 100%; min-height: 40px; line-height: 40px; border-radius: 10px; text-decoration: none; font-weight: 700;">
              Apri e Compila Modulo →
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>