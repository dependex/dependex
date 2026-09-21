<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$sic = trim((string)($_GET['sic'] ?? ''));
$c = null;

if ($sic !== '') {
    $st = db()->prepare('SELECT * FROM dependex_world_registry WHERE sic_id = ?');
    $st->execute([$sic]);
    $c = $st->fetch();
}

if (!$c) {
    http_response_code(404);
    $pageTitle = 'Club Territoriale Non Trovato · Registro';
    $metaDesc = 'Il presidio o Club cercato non è presente o il codice identificativo è incompleto.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 800px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #FFFFFF; font-size: 1.8rem; margin-bottom: 16px;">Presidio Non Trovato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px; line-height: 1.6;">
          Il codice presidio specificato non corrisponde a nessun nodo attivo nel Registro Mondiale o Censimento Nazionale.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
          <a class="btn primary" href="mappa-club.php" style="padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 700;">
            🗺️ Esplora la Mappa dei Club
          </a>
          <a class="btn" href="world-club-explorer.php" style="padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600;">
            🔍 Cerca nel Registro Mondiale
          </a>
        </div>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$children = db()->prepare('
    SELECT sic_id, entity_name, network_level, status 
    FROM dependex_world_registry 
    WHERE parent_sic_id = ? 
    ORDER BY network_rank DESC, entity_name 
    LIMIT 100
');
$children->execute([$sic]);
$kids = $children->fetchAll();

$drxSt = db()->prepare("
    SELECT COALESCE(SUM(CASE WHEN rank_eligible = 1 AND status = 'POSTED' THEN amount ELSE 0 END), 0) 
    FROM drx_ledger 
    WHERE club_sic_id = ?
");
$drxSt->execute([$sic]);
$qdrx = (float)$drxSt->fetchColumn();

$snap = $c['network_level'] === 'LOCAL_CLUB' ? club_rank_snapshot($sic) : null;
$clubRank = $snap['rank'] ?? rank_for_drx($qdrx);

$pageTitle = $c['entity_name'] . ' · Scheda Presidio Territoriale';
$metaDesc = 'Scheda ufficiale e recapiti del presidio ' . $c['entity_name'] . ' a ' . ($c['city'] ?? '') . ' (' . ($c['province'] ?? '') . ').';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 1000px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 28px;">
    <div style="margin-bottom: 8px;">
      <a href="mappa-club.php" style="color: #D4AF37; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        ← Torna alla Mappa
      </a>
    </div>
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      <?= h($c['network_level']) ?> · <?= h($c['country']) ?>
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 6px; color: #FFFFFF; line-height: 1.25;">
      <?= h($c['entity_name']) ?>
    </h1>
    <div style="font-family: monospace; color: #a1a1aa; font-size: 0.85rem;">
      <?= h($c['sic_id']) ?>
    </div>
  </section>

  <!-- Metriche del Presidio -->
  <section class="metric-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.4rem; font-weight: 800; color: #FFF2B2;"><?= h($clubRank) ?></div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Rank DRX Presidio</span>
    </div>
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.4rem; font-weight: 800; color: #FFFFFF;"><?= h((string)$c['direct_children']) ?></div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Nodi Figli Diretti</span>
    </div>
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.4rem; font-weight: 800; color: #FFFFFF;"><?= h((string)$c['network_descendants']) ?></div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Presidi Discendenti</span>
    </div>
    <div class="metric-card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; text-align: center;">
      <div style="font-size: 1.4rem; font-weight: 800; color: #4ade80;"><?= h((string)$c['public_data_score']) ?>%</div>
      <span style="font-size: 0.8rem; color: #a1a1aa; text-transform: uppercase;">Completezza Censimento</span>
    </div>
  </section>

  <!-- Dati Logistici e Contatti -->
  <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 28px; margin-bottom: 28px;">
    <h2 style="font-size: 1.3rem; font-weight: 700; color: #FFFFFF; margin: 0 0 20px;">
      Dati e Informazioni di Contatto
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; font-size: 0.95rem; color: #d1d5db; line-height: 1.6;">
      <div><strong>Stato Operativo:</strong> <span style="color: #4ade80;"><?= h($c['status']) ?></span></div>
      <div><strong>Località:</strong> <?= h(implode(' · ', array_filter([$c['city'], $c['province'], $c['region'], $c['country']]))) ?></div>
      <div><strong>Indirizzo:</strong> <?= h($c['address'] ?: '—') ?></div>
      <div><strong>Orario Incontri:</strong> <?= h($c['meeting'] ?: '—') ?></div>
      <div><strong>Telefono:</strong> <?= h($c['phone'] ?: '—') ?></div>
      <div><strong>Email:</strong> <?= h($c['email'] ?: '—') ?></div>
      <div><strong>Accuratezza GPS:</strong> <?= h($c['geo_accuracy'] ?: 'PENDING') ?> (confidenza <?= h((string)$c['geo_confidence']) ?>%)</div>
    </div>

    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08);">
      <?php if (!empty($c['phone'])): ?>
        <a class="btn primary" href="tel:<?= h(preg_replace('/[^0-9+]/', '', $c['phone'])) ?>" style="padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 700;">
          📞 Chiama
        </a>
      <?php endif; ?>

      <?php if (!empty($c['email'])): ?>
        <a class="btn" href="mailto:<?= h($c['email']) ?>" style="padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600;">
          ✉️ Scrivi Email
        </a>
      <?php endif; ?>

      <?php if (!empty($c['website'])): ?>
        <a class="btn" target="_blank" rel="noopener" href="<?= h($c['website']) ?>" style="padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600;">
          🌐 Sito Web Ufficiale
        </a>
      <?php endif; ?>

      <?php if (!$c['is_synthetic'] && $c['latitude'] !== null && $c['longitude'] !== null): ?>
        <a class="btn" target="_blank" rel="noopener" href="https://www.openstreetmap.org/?mlat=<?= urlencode((string)$c['latitude']) ?>&mlon=<?= urlencode((string)$c['longitude']) ?>" style="padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 600;">
          📍 Indicazioni Stradali
        </a>
      <?php endif; ?>
    </div>
  </section>

  <!-- Nodi Figli (se presenti) -->
  <?php if (!empty($kids)): ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; padding: 28px; margin-bottom: 28px;">
      <h2 style="font-size: 1.3rem; font-weight: 700; color: #FFFFFF; margin: 0 0 16px;">
        Nodi e Club Territoriali Coordinati (<?= count($kids) ?>)
      </h2>
      <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($kids as $k): ?>
          <a href="club-card.php?sic=<?= urlencode($k['sic_id']) ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; text-decoration: none; color: inherit;">
            <strong style="color: #FFFFFF;"><?= h($k['entity_name']) ?></strong>
            <span style="font-size: 0.85rem; color: #a1a1aa;"><?= h($k['network_level']) ?> · <?= h($k['status']) ?> ›</span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- Fonte Dati -->
  <section class="card" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 20px; font-size: 0.85rem; color: #71717a;">
    <div>Fonte di Censimento: <?= h($c['source_type'] ?: 'Rete Territoriale Hudolin 2026') ?></div>
    <?php if (!empty($c['source_url'])): ?>
      <div style="margin-top: 6px;">
        <a target="_blank" rel="noopener" href="<?= h($c['source_url']) ?>" style="color: #a1a1aa;">
          Consulta fonte pubblica originale ↗
        </a>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>