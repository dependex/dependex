<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$dims = db()->query('SELECT * FROM lifestyle_dimensions ORDER BY id')->fetchAll();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($dims as $d) {
        $score = max(0, min(10, (float)($_POST[$d['code']] ?? 0)));
        db()->prepare('INSERT INTO lifestyle_scores(sic_id, user_sic_id, dimension_code, score, source) VALUES(?, ?, ?, ?, ?)')
            ->execute([sic_id(), $u['sic_id'], $d['code'], $score, 'WHEEL_OF_LIFE']);
    }
    drx_post($u['sic_id'], null, 20, 'LIFESTYLE_WHEEL', true, 'life-wheel:' . $u['sic_id'] . ':' . date('Y-m-d'), null);
    $msg = 'La tua Ruota della Vita è stata salvata con successo nel tuo spazio privato.';
}

$latest = [];
foreach ($dims as $d) {
    $s = db()->prepare('SELECT score FROM lifestyle_scores WHERE user_sic_id = ? AND dimension_code = ? ORDER BY recorded_at DESC, id DESC LIMIT 1');
    $s->execute([$u['sic_id'], $d['code']]);
    $latest[$d['code']] = $s->fetchColumn() ?: 5;
}

$pageTitle = 'La Mia Ruota della Vita · Lifestyle';
$metaDesc = 'Spazio privato per osservare l\'equilibrio tra le dimensioni della vita: salute, relazioni, sonno, comunità e scopo.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 900px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 32px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Lifestyle & Well-being Compass
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 12px; color: #FFFFFF;">
      La Vita che Costruisci
    </h1>
    <p style="color: var(--muted, #a1a1aa); max-width: 720px; line-height: 1.6; font-size: 1.05rem;">
      Salute, riposo, relazioni, lavoro, significato e comunità: nessuna singola dimensione definisce chi sei. Esplora dove oggi senti di voler portare cura e attenzione.
    </p>
  </section>

  <?php if ($msg): ?>
    <div class="alert success" style="background: rgba(74, 222, 128, 0.12); border: 1px solid rgba(74, 222, 128, 0.4); color: #4ade80; padding: 18px 20px; border-radius: 14px; margin-bottom: 28px; font-weight: 600;">
      ✓ <?= h($msg) ?>
    </div>
  <?php endif; ?>

  <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 32px 24px; margin-bottom: 32px;">
    <form method="post" style="display: flex; flex-direction: column; gap: 24px;">
      <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">

      <?php foreach ($dims as $d): 
        $val = (float)($latest[$d['code']] ?? 5);
      ?>
        <div style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 18px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label for="dim-<?= h($d['code']) ?>" style="color: #FFFFFF; font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
              <span><?= h($d['icon']) ?></span>
              <span><?= h($d['label']) ?></span>
            </label>
            <span id="out-<?= h($d['code']) ?>" style="font-size: 1.2rem; font-weight: 800; color: #FFF2B2;">
              <?= $val ?>
            </span>
          </div>
          <input 
            id="dim-<?= h($d['code']) ?>"
            type="range" 
            min="0" 
            max="10" 
            step="1" 
            name="<?= h($d['code']) ?>" 
            value="<?= $val ?>"
            style="width: 100%; accent-color: #D4AF37; min-height: 44px;"
            oninput="document.getElementById('out-<?= h($d['code']) ?>').textContent = this.value"
          >
        </div>
      <?php endforeach; ?>

      <button type="submit" class="btn primary" style="align-self: flex-start; min-height: 44px; padding: 0 28px; border-radius: 12px; font-weight: 700; cursor: pointer;">
        Salva la Mia Ruota della Vita (+20 DRX)
      </button>
    </form>
  </section>

  <!-- Strumenti Rapidi di Cammino -->
  <section class="quick-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
    <a href="journal.php" class="card" style="padding: 20px; background: #101116; border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 16px; text-decoration: none; text-align: center; color: inherit;">
      <div style="font-size: 2rem; margin-bottom: 6px;">📓</div>
      <strong style="color: #FFFFFF; display: block;">Diario Personale</strong>
      <span style="color: #a1a1aa; font-size: 0.85rem;">Pensieri e traguardi</span>
    </a>

    <a href="checkin.php" class="card" style="padding: 20px; background: #101116; border: 1px solid rgba(74, 222, 128, 0.3); border-radius: 16px; text-decoration: none; text-align: center; color: inherit;">
      <div style="font-size: 2rem; margin-bottom: 6px;">🧭</div>
      <strong style="color: #FFFFFF; display: block;">Check-in Quotidiano</strong>
      <span style="color: #a1a1aa; font-size: 0.85rem;">Come ti senti oggi?</span>
    </a>

    <a href="missions.php" class="card" style="padding: 20px; background: #101116; border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 16px; text-decoration: none; text-align: center; color: inherit;">
      <div style="font-size: 2rem; margin-bottom: 6px;">🎯</div>
      <strong style="color: #FFFFFF; display: block;">Micro-Missioni</strong>
      <span style="color: #a1a1aa; font-size: 0.85rem;">Piccoli passi pratici</span>
    </a>

    <a href="sobriety.php" class="card" style="padding: 20px; background: #101116; border: 1px solid rgba(234, 179, 8, 0.3); border-radius: 16px; text-decoration: none; text-align: center; color: inherit;">
      <div style="font-size: 2rem; margin-bottom: 6px;">🌱</div>
      <strong style="color: #FFFFFF; display: block;">Il Mio Cammino</strong>
      <span style="color: #a1a1aa; font-size: 0.85rem;">Continuità e traguardi</span>
    </a>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>