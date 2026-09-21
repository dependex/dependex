<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();

$st = db()->prepare('SELECT * FROM certificates WHERE owner_sic_id = ? ORDER BY issued_at DESC');
$st->execute([$u['sic_id']]);
$certs = $st->fetchAll();

$pageTitle = 'I Miei Attestati e Traguardi';
$metaDesc = 'Registro personale dei traguardi formativi, attestati e milestone conseguiti nei percorsi DEPENDEX.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Achievement & Certificate Engine
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      I Miei Traguardi e Attestati
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 700px; line-height: 1.6; font-size: 1.05rem;">
      Tutti gli attestati digitali rilasciati a tuo nome per percorsi di formazione, seminari e milestone nella Rete Hudolin.
    </p>
  </section>

  <?php if (empty($certs)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 40px 24px; text-align: center;">
      <div style="font-size: 3rem; margin-bottom: 16px;">📜</div>
      <h2 style="color: #FFFFFF; font-size: 1.3rem; margin-bottom: 8px;">Nessun attestato ancora conseguito</h2>
      <p style="color: var(--muted, #a1a1aa); max-width: 500px; margin: 0 auto 24px; line-height: 1.6;">
        Gli attestati digitali compariranno automaticamente qui al completamento dei percorsi formativi dell'Academy o delle tappe di partecipazione.
      </p>
      <a class="btn primary" href="academy.php" style="display: inline-block; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700;">
        Esplora i Corsi Academy →
      </a>
    </section>
  <?php else: ?>
    <div class="certs-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
      <?php foreach ($certs as $c): ?>
        <article class="cert-card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 16px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <span style="display: inline-block; font-size: 0.75rem; font-weight: 700; color: #D4AF37; background: rgba(212, 175, 55, 0.12); padding: 4px 10px; border-radius: 12px; margin-bottom: 12px;">
              <?= h($c['certificate_type']) ?>
            </span>
            <h2 style="font-size: 1.2rem; font-weight: 700; color: #FFFFFF; margin: 0 0 8px; line-height: 1.3;">
              <?= h($c['title']) ?>
            </h2>
            <p style="color: #a1a1aa; font-size: 0.85rem; margin-bottom: 12px;">
              Emesso il: <strong style="color: #d1d5db;"><?= h((string)$c['issued_at']) ?></strong>
            </p>
            <div style="font-family: monospace; font-size: 0.75rem; color: #71717a; margin-bottom: 16px; word-break: break-all;">
              <?= h($c['sic_id']) ?>
            </div>
          </div>

          <div>
            <?php if (!empty($c['verify_token'])): ?>
              <a class="btn small primary" href="certificate-verify.php?token=<?= urlencode($c['verify_token']) ?>" target="_blank" style="display: block; text-align: center; width: 100%; min-height: 40px; line-height: 40px; padding: 0 16px; border-radius: 10px; text-decoration: none; font-weight: 700;">
                Visualizza e Stampa 🖨️
              </a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>