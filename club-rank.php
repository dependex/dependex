<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_login();
$club = trim((string)($_GET['club'] ?? user_club_sic($u['sic_id'])));

// Se non ha un club associato, seleziona il primo club attivo come default anziché andare in errore
if ($club === '') {
    $firstClub = db()->query("SELECT sic_id FROM cat_clubs_italy WHERE status != 'INACTIVE' LIMIT 1")->fetchColumn();
    $club = $firstClub ?: '';
}

if ($club === '') {
    $pageTitle = 'Traguardi Club Territoriali';
    $metaDesc = 'Visualizzazione del livello di maturità e traguardi raggiunti dai Club della rete.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 800px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #FFFFFF; font-size: 1.6rem; font-weight: 800; margin-bottom: 12px;">Seleziona un Club</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px;">
          Nessun presidio attualmente selezionato. Puoi trovare un Club dalla mappa o dall'elenco nazionale.
        </p>
        <a class="btn primary" href="mappa-club.php">Esplora Mappa Club →</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$st = db()->prepare('SELECT entity_name FROM dependex_world_registry WHERE sic_id = ? UNION ALL SELECT entity_name FROM network_entities WHERE sic_id = ? LIMIT 1');
$st->execute([$club, $club]);
$name = $st->fetchColumn() ?: 'Club Territoriale';

$snap = club_rank_snapshot($club);
$req = db()->query('SELECT * FROM club_rank_requirements ORDER BY threshold_drx')->fetchAll();

$un = db()->prepare('SELECT * FROM club_rank_unlocks WHERE rank_name = ? ORDER BY id');
$un->execute([$snap['rank']]);
$unlocks = $un->fetchAll();

$pageTitle = $name . ' · Traguardi e Rank Club';
$metaDesc = 'Traguardi, attività, ore di volontariato e maturità metodologica del presidio ' . $name . '.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 28px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Club Rank & Maturity Engine
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 6px; color: #FFFFFF;">
      <?= h($name) ?>
    </h1>
    <p style="color: var(--muted, #a1a1aa); font-size: 0.95rem; margin-bottom: 8px;">
      Identificativo SIC: <code style="color: #FFF2B2;"><?= h($club) ?></code>
    </p>
    <div style="display: inline-block; background: rgba(212, 175, 55, 0.15); border: 1px solid rgba(212, 175, 55, 0.4); border-radius: 20px; padding: 6px 16px; color: #FFF2B2; font-weight: 700; font-size: 0.9rem;">
      ◆ <?= number_format((float)($snap['drx'] ?? 0), 0, ',', '.') ?> DRX · Traguardo: <?= h((string)($snap['rank'] ?? 'SEME')) ?>
    </div>
  </section>

  <!-- Metriche Attività e Presenze -->
  <section class="metric-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.8rem; font-weight: 800; color: #FFFFFF;"><?= (int)($snap['families'] ?? 0) ?></div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Famiglie Attive</span>
    </div>
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.8rem; font-weight: 800; color: #FFF2B2;"><?= (int)($snap['events_365'] ?? 0) ?></div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Incontri Ultimi 365g</span>
    </div>
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.8rem; font-weight: 800; color: #FFFFFF;"><?= (int)($snap['checkins_365'] ?? 0) ?></div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Presenze Registrate</span>
    </div>
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.8rem; font-weight: 800; color: #4ade80;"><?= round((float)($snap['compliance_score'] ?? 0)) ?>%</div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Completezza Censimento</span>
    </div>
  </section>

  <!-- Indicatori Qualificanti -->
  <section class="card" style="background: #101116; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 28px; margin-bottom: 28px;">
    <h2 style="font-size: 1.3rem; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">
      Indicatori di Continuità Territoriale
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; font-size: 0.95rem; color: #d1d5db;">
      <div>Corsi Academy completati dai membri: <strong style="color: #FFFFFF;"><?= (int)($snap['academy_completions_365'] ?? 0) ?></strong></div>
      <div>Ore di volontariato comunitario: <strong style="color: #FFFFFF;"><?= (float)($snap['volunteer_hours_365'] ?? 0) ?> h</strong></div>
      <div>Adesione linee guida Hudolin: <strong style="color: #4ade80;"><?= (int)($snap['hudolin_compliance'] ?? 100) ?>%</strong></div>
    </div>
  </section>

  <!-- Privilegi Sbloccati -->
  <?php if (!empty($unlocks)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 28px; margin-bottom: 28px;">
      <h2 style="font-size: 1.3rem; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">
        Funzionalità e Risorse Attive per questo Traguardo
      </h2>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($unlocks as $x): ?>
          <div style="padding: 14px 18px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid rgba(255,255,255,0.06);">
            <strong style="color: #FFF2B2;"><?= h($x['label']) ?></strong>
            <p style="color: #a1a1aa; font-size: 0.9rem; margin: 4px 0 0;"><?= h($x['description']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- Scala dei Traguardi -->
  <section class="card" style="background: #101116; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 28px;">
    <h2 style="font-size: 1.3rem; font-weight: 700; color: #FFFFFF; margin: 0 0 20px;">
      Scala Evolutiva dei Traguardi Club
    </h2>
    <div style="display: flex; flex-direction: column; gap: 10px;">
      <?php foreach ($req as $r): 
        $isCurrent = ($r['rank_name'] === ($snap['rank'] ?? ''));
      ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: <?= $isCurrent ? 'rgba(212, 175, 55, 0.12)' : 'rgba(255,255,255,0.02)' ?>; border: 1px solid <?= $isCurrent ? 'rgba(212, 175, 55, 0.4)' : 'rgba(255,255,255,0.05)' ?>; border-radius: 12px;">
          <div>
            <strong style="color: <?= $isCurrent ? '#FFF2B2' : '#FFFFFF' ?>; font-size: 1rem;"><?= h($r['rank_name']) ?></strong>
            <?php if ($isCurrent): ?>
              <span style="font-size: 0.75rem; background: #D4AF37; color: #070709; padding: 2px 8px; border-radius: 10px; font-weight: 800; margin-left: 8px;">LIVELLO ATTUALE</span>
            <?php endif; ?>
          </div>
          <span style="font-size: 0.85rem; color: #a1a1aa;">
            <?= number_format((int)$r['threshold_drx'], 0, ',', '.') ?> DRX · min <?= (int)$r['min_active_families'] ?> fam
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>