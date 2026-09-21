<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $date = trim((string)($_POST['sobriety_start_date'] ?? ''));
    if ($date !== '') {
        db()->prepare('UPDATE users SET sobriety_start_date = ? WHERE sic_id = ?')
            ->execute([$date, $u['sic_id']]);
        db()->prepare('
            INSERT INTO sobriety_records(sic_id, user_sic_id, start_date, current_streak, lifetime_days) 
            VALUES(?, ?, ?, 0, 0) 
            ON CONFLICT(user_sic_id) DO UPDATE SET start_date = excluded.start_date, updated_at = CURRENT_TIMESTAMP
        ')->execute([sic_id(), $u['sic_id'], $date]);
        $msg = 'Data di inizio del tuo cammino aggiornata. La tua storia e i DRX maturati rimangono al sicuro.';
    }
}

$u = current_user();
$sync = sobriety_sync($u['sic_id']);

db()->prepare('
    UPDATE sobriety_records 
    SET current_streak = ?, lifetime_days = MAX(lifetime_days, ?), updated_at = CURRENT_TIMESTAMP 
    WHERE user_sic_id = ?
')->execute([$sync['days'], $sync['days'], $u['sic_id']]);

$ms = db()->query('SELECT * FROM sobriety_milestones ORDER BY days')->fetchAll();

$pageTitle = 'Il Mio Cammino · Un Giorno alla Volta';
$metaDesc = 'Spazio personale di continuità e celebrazione del cammino sobrio: ogni giorno ha valore, senza colpevolizzazioni.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 900px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Sobriety & Recovery Journey
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      Un Giorno alla Volta
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
      Ogni singolo giorno vissuto in pienezza è una vittoria per te e per chi ti sta accanto. Nessuna difficoltà cancella il valore, la forza e il percorso che hai già costruito.
    </p>
  </section>

  <?php if ($msg): ?>
    <div class="alert success" style="background: rgba(74, 222, 128, 0.12); border: 1px solid rgba(74, 222, 128, 0.4); color: #4ade80; padding: 18px 20px; border-radius: 14px; margin-bottom: 28px; font-weight: 600;">
      ✓ <?= h($msg) ?>
    </div>
  <?php endif; ?>

  <!-- Contatore Principale -->
  <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 24px; padding: 36px 24px; text-align: center; margin-bottom: 32px; box-shadow: 0 16px 40px rgba(0,0,0,0.5);">
    <span style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: #D4AF37; font-weight: 700;">
      Continuità del Cammino
    </span>
    <div style="font-size: clamp(3rem, 8vw, 5rem); font-weight: 900; color: #FFFFFF; line-height: 1; margin: 16px 0 8px; font-variant-numeric: tabular-nums;">
      <?= number_format((float)$sync['days'], 0, ',', '.') ?>
    </div>
    <div style="font-size: 1.2rem; color: #FFF2B2; font-weight: 700; margin-bottom: 24px;">
      giorni di nuova vita e libertà
    </div>

    <!-- Modifica data inizio -->
    <form method="post" style="max-width: 440px; margin: 0 auto; display: flex; flex-direction: column; gap: 14px; text-align: left; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08);">
      <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
      <label for="sobriety_start_date" style="color: #d1d5db; font-size: 0.9rem; font-weight: 600;">
        Data di Inizio del Percorso:
      </label>
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <input 
          id="sobriety_start_date" 
          type="date" 
          name="sobriety_start_date" 
          value="<?= h($u['sobriety_start_date'] ?? '') ?>" 
          max="<?= date('Y-m-d') ?>"
          style="flex: 1; min-height: 44px; padding: 8px 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #FFFFFF;"
        >
        <button type="submit" class="btn primary small" style="min-height: 44px; padding: 0 20px; border-radius: 10px; font-weight: 700; cursor: pointer;">
          Salva Data
        </button>
      </div>
    </form>
  </section>

  <!-- Traguardi e Pietre Miliari -->
  <section class="card" style="background: #101116; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 28px;">
    <h2 style="font-size: 1.3rem; font-weight: 700; color: #FFFFFF; margin: 0 0 20px;">
      Traguardi e Pietre Miliari del Cammino
    </h2>
    <div style="display: flex; flex-direction: column; gap: 12px;">
      <?php foreach ($ms as $m): 
        $reached = ($sync['days'] >= (int)$m['days']);
      ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: <?= $reached ? 'rgba(74, 222, 128, 0.08)' : 'rgba(255,255,255,0.02)' ?>; border: 1px solid <?= $reached ? 'rgba(74, 222, 128, 0.3)' : 'rgba(255,255,255,0.05)' ?>; border-radius: 12px;">
          <div>
            <span style="font-weight: 800; color: <?= $reached ? '#4ade80' : '#FFFFFF' ?>; font-size: 1.05rem;">
              <?= $reached ? '✓ ' : '○ ' ?><?= number_format((int)$m['days'], 0, ',', '.') ?> Giorni
            </span>
            <span style="color: #d1d5db; margin-left: 8px; font-weight: 600;">
              <?= h($m['title']) ?>
            </span>
          </div>
          <span style="font-size: 0.85rem; color: #FFF2B2; font-weight: 700;">
            +<?= number_format((int)$m['drx_reward'], 0, ',', '.') ?> DRX
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>