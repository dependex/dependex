<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$club = club_for_user($u['sic_id']);

$pageTitle = 'Il Mio Club Territoriale';
$metaDesc = 'Spazio della tua comunità multifamiliare: calendario incontri, presenze, continuità e risorse.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <?php if (!$club): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 40px 24px; text-align: center; margin: 40px auto; max-width: 600px;">
      <div style="font-size: 3rem; margin-bottom: 16px;">🏡</div>
      <h1 style="color: #FFFFFF; font-size: 1.6rem; font-weight: 800; margin-bottom: 12px;">Il Tuo Club Territoriale</h1>
      <p style="color: var(--muted, #a1a1aa); line-height: 1.6; margin-bottom: 24px;">
        Non risulti ancora associato a un Club di ecologia familiare attivo. Il Club è il luogo centrale di incontro settimanale, ascolto e solidarietà.
      </p>
      <a class="btn primary" href="mappa-club.php" style="display: inline-block; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700;">
        Trova il Club Più Vicino a Te →
      </a>
    </section>
  <?php else: 
    $snap = club_rank_snapshot($club['sic_id']);
  ?>
    <section class="section-head" style="margin-bottom: 28px;">
      <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
        Comunità Multifamiliare · Club Hub
      </span>
      <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 6px; color: #FFFFFF;">
        <?= h($club['entity_name']) ?>
      </h1>
      <p style="color: var(--muted, #a1a1aa); font-size: 1.05rem; margin-bottom: 4px;">
        <?= h($club['comune'] ?? '') ?> · <?= h($club['region'] ?? '') ?> · <?= h($club['country'] ?? 'Italia') ?>
      </p>
      <div style="font-family: monospace; font-size: 0.8rem; color: #71717a;">
        <?= h($club['sic_id']) ?>
      </div>
    </section>

    <!-- Metriche Attività Club -->
    <section class="metric-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
      <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 800; color: #FFFFFF;"><?= (int)($snap['families'] ?? 0) ?></div>
        <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Famiglie Aderenti</span>
      </div>
      <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 800; color: #FFF2B2;"><?= number_format((float)($snap['drx'] ?? 0), 0, ',', '.') ?></div>
        <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">DRX Partecipazione</span>
      </div>
      <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 800; color: #D4AF37;"><?= h((string)($snap['rank'] ?? 'SEME')) ?></div>
        <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Traguardo Maturità</span>
      </div>
      <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 800; color: #4ade80;"><?= round((float)($snap['compliance_score'] ?? 0)) ?>%</div>
        <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Completezza Dati</span>
      </div>
    </section>

    <!-- Notifiche Moltiplicazione secondo Regola Hudolin (max 10-12 famiglie) -->
    <?php if (!empty($snap['multiplication_required'])): ?>
      <section class="card" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <h2 style="color: #f87171; font-size: 1.2rem; font-weight: 700; margin: 0 0 8px;">🌳 Moltiplicazione Territoriale Consigliata</h2>
        <p style="color: #d1d5db; line-height: 1.6; margin-bottom: 16px;">
          Secondo il metodo Hudolin, quando un Club supera le 10–12 famiglie è opportuno generare un nuovo ramo territoriale per conservare intimità e ascolto.
        </p>
        <a class="btn primary small" href="club-admin.php">Gestisci Moltiplicazione →</a>
      </section>
    <?php elseif (!empty($snap['pre_multiplication'])): ?>
      <section class="card" style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.3); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
        <h2 style="color: #fde047; font-size: 1.1rem; font-weight: 700; margin: 0 0 6px;">🌱 Fase di Pre-Moltiplicazione (8–11 Famiglie)</h2>
        <p style="color: #d1d5db; font-size: 0.95rem; margin: 0;">
          La comunità sta crescendo armoniosamente. Il servitore-insegnante e il Club possono iniziare a formare nuovi servitori per l'apertura di un nuovo presidio.
        </p>
      </section>
    <?php endif; ?>

    <!-- Menu Azioni e Risorse -->
    <section class="menu-list" style="display: flex; flex-direction: column; gap: 12px;">
      <a href="events.php" class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; text-decoration: none; color: inherit;">
        <div>
          <strong style="color: #FFFFFF; font-size: 1.05rem;">📅 Calendario e Incontri Settimanali</strong>
          <div style="color: #a1a1aa; font-size: 0.85rem; margin-top: 2px;">Presenze, orari e date delle prossime riunioni</div>
        </div>
        <span style="color: #D4AF37; font-size: 1.3rem;">›</span>
      </a>

      <a href="academy.php" class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; text-decoration: none; color: inherit;">
        <div>
          <strong style="color: #FFFFFF; font-size: 1.05rem;">🎓 OLTRE Academy & Formazione</strong>
          <div style="color: #a1a1aa; font-size: 0.85rem; margin-top: 2px;">Corsi per servitori, famiglie e facilitatori</div>
        </div>
        <span style="color: #D4AF37; font-size: 1.3rem;">›</span>
      </a>

      <a href="documents.php" class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; text-decoration: none; color: inherit;">
        <div>
          <strong style="color: #FFFFFF; font-size: 1.05rem;">📄 Documenti e Materiali</strong>
          <div style="color: #a1a1aa; font-size: 0.85rem; margin-top: 2px;">Moduli, verbali e attestati digitali del Club</div>
        </div>
        <span style="color: #D4AF37; font-size: 1.3rem;">›</span>
      </a>

      <?php if (is_admin($u['sic_id'])): ?>
        <a href="club-admin.php" class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #101116; border: 1px solid rgba(212,175,55,0.25); border-radius: 16px; text-decoration: none; color: inherit;">
          <div>
            <strong style="color: #FFF2B2; font-size: 1.05rem;">⚙️ Pannello Gestione Club</strong>
            <div style="color: #a1a1aa; font-size: 0.85rem; margin-top: 2px;">Anagrafe famiglie, membership e moltiplicazione</div>
          </div>
          <span style="color: #D4AF37; font-size: 1.3rem;">›</span>
        </a>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/_footer.php'; ?>