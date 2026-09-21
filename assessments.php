<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();

$items = db()->query("SELECT * FROM assessments WHERE status='ACTIVE' ORDER BY professional_only, category, title")->fetchAll();

$pageTitle = 'Autovalutazioni e Consapevolezza';
$metaDesc = 'Spazio privato di osservazione personale e consapevolezza. Strumenti non diagnostici per accompagnare il cammino di crescita.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Assessment & Reflection Engine
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      Osserva il Tuo Cammino, Senza Etichette
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
      Questi questionari di consapevolezza personale sono lenti maieutiche private per osservare cambiamenti nel tempo. Non costituiscono diagnosi clinica e non assegnano punteggi di merito o demerito.
    </p>
  </section>

  <?php if (empty($items)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 32px; text-align: center;">
      <p style="color: #a1a1aa;">Nessun questionario attivo al momento.</p>
    </section>
  <?php else: ?>
    <div class="assessments-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 32px;">
      <?php foreach ($items as $a): 
        $isProf = (int)$a['professional_only'] === 1;
      ?>
        <article class="card assessment-card" style="background: #101116; border: 1px solid <?= $isProf ? 'rgba(168, 85, 247, 0.3)' : 'rgba(212, 175, 55, 0.25)' ?>; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <span style="font-size: 0.75rem; font-weight: 700; color: #D4AF37; background: rgba(212, 175, 55, 0.12); padding: 4px 10px; border-radius: 12px;">
                <?= h($a['evidence_level']) ?>
              </span>
              <span style="font-size: 0.8rem; color: <?= $isProf ? '#c084fc' : '#a1a1aa' ?>;">
                <?= $isProf ? '🔒 Uso Professionale' : '🌱 Privato' ?>
              </span>
            </div>

            <h2 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin: 0 0 8px; line-height: 1.3;">
              <?= h($a['title']) ?>
            </h2>
            <p style="color: #a1a1aa; font-size: 0.9rem; margin-bottom: 20px;">
              Ambito: <strong style="color: #d1d5db;"><?= h($a['category']) ?></strong>
            </p>
          </div>

          <div>
            <a class="btn primary small" href="assessment-run.php?id=<?= urlencode($a['sic_id']) ?>" style="display: block; text-align: center; width: 100%; min-height: 40px; line-height: 40px; border-radius: 10px; text-decoration: none; font-weight: 700;">
              Compila Questionario →
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <section class="card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 16px; padding: 24px;">
    <h3 style="color: #FFF2B2; font-size: 1.1rem; font-weight: 700; margin: 0 0 8px;">
      Garanzia di Riservatezza & Approccio Salutogenico
    </h3>
    <p style="color: var(--muted, #a1a1aa); font-size: 0.95rem; line-height: 1.6; margin: 0;">
      I dati inseriti rimangono confidenziali nel tuo profilo personale. Eventuali test clinici validati vengono attivati esclusivamente in accordo con professionisti sanitari o servitori qualificati, senza sovrapporsi alla vita comunitaria del Club.
    </p>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>
