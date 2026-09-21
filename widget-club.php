<?php
/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * Widget Embed Iframe Gratuito "Trova Club Territoriale"
 * 
 * Rilasciato come Bene Comune Digitale per:
 * - Comuni e Consulte del Volontariato
 * - ASL, Ser.D e Distretti Sanitari
 * - Medici di Medicina Generale e Farmacie
 * - Parrocchie e Centri di Ascolto
 * 
 * Parametri URL supportati:
 * - region: Filtra per regione (es. Veneto, Lombardia)
 * - province: Filtra per provincia (es. RO, VR, MI, PD)
 * - q: Filtra per comune/città o via (es. Taglio di Po, Verona)
 * - theme: 'dark' (default) o 'light'
 */

require_once __DIR__ . '/bootstrap.php';
if (!headers_sent()) {
    header_remove('X-Frame-Options');
    header("Content-Security-Policy: frame-ancestors *;");
}

$pdo = db();

$query = trim($_GET['q'] ?? '');
$region = trim($_GET['region'] ?? '');
$province = trim($_GET['province'] ?? '');
$theme = ($_GET['theme'] ?? 'dark') === 'light' ? 'light' : 'dark';

$where = ["status != 'INACTIVE'"];
$params = [];

if ($province !== '') {
    $where[] = "LOWER(province) = LOWER(?)";
    $params[] = $province;
}

if ($region !== '' && strtolower($region) !== 'all') {
    $where[] = "LOWER(region) = LOWER(?)";
    $params[] = $region;
}

if ($query !== '') {
    $where[] = "(city LIKE ? OR entity_name LIKE ? OR address LIKE ?)";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
    $params[] = "%{$query}%";
}

$sql = "SELECT id, sic_id, entity_name, level, region, province, city, address, cap,
               meeting_day, meeting_time, phone, email, website, latitude, longitude, families_count
        FROM cat_clubs_italy
        WHERE " . implode(' AND ', $where) . "
        ORDER BY region ASC, city ASC, entity_name ASC
        LIMIT 15";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $clubs = [];
}

$totalClubsCount = (int)$pdo->query("SELECT COUNT(*) FROM cat_clubs_italy")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
  <meta name="description" content="Widget istituzionale per trovare il Club Alcologico Territoriale (CAT) Metodo Hudolin più vicino in tutta Italia. Aperto a tutti.">
  <title>Trova il Club Alcologico Territoriale (CAT) più vicino · DEPENDEX</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      font-size: 14px;
      line-height: 1.45;
      padding: 12px;
      min-height: 100%;
      background: <?= $theme === 'dark' ? '#070a12' : '#f8fafc' ?>;
      color: <?= $theme === 'dark' ? '#f1f5f9' : '#0f172a' ?>;
      overflow-x: hidden;
    }
    .widget-container {
      max-width: 650px;
      margin: 0 auto;
      border: 1px solid <?= $theme === 'dark' ? 'rgba(0, 240, 255, 0.25)' : 'rgba(0, 102, 204, 0.2)' ?>;
      border-radius: 14px;
      padding: 14px;
      background: <?= $theme === 'dark' ? 'rgba(15, 23, 42, 0.95)' : '#ffffff' ?>;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .widget-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
      padding-bottom: 10px;
      border-bottom: 1px solid <?= $theme === 'dark' ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)' ?>;
      flex-wrap: wrap;
      gap: 8px;
    }
    .widget-title {
      font-size: 1rem;
      font-weight: 800;
      color: <?= $theme === 'dark' ? '#00f0ff' : '#0066cc' ?>;
      display: flex;
      align-items: center;
      gap: 6px;
      margin: 0;
    }
    .widget-sub {
      font-size: 0.76rem;
      color: #94a3b8;
      margin-top: 2px;
    }
    .search-box {
      display: flex;
      gap: 8px;
      margin-bottom: 12px;
    }
    .search-box input {
      flex: 1;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid <?= $theme === 'dark' ? 'rgba(255,255,255,0.15)' : '#cbd5e1' ?>;
      background: <?= $theme === 'dark' ? 'rgba(0,0,0,0.3)' : '#ffffff' ?>;
      color: inherit;
      font-size: 0.88rem;
      outline: none;
      min-height: 44px;
    }
    .search-box button {
      padding: 0 16px;
      border-radius: 8px;
      border: none;
      background: <?= $theme === 'dark' ? 'linear-gradient(135deg, #00f0ff, #0077ff)' : '#0066cc' ?>;
      color: <?= $theme === 'dark' ? '#070a12' : '#ffffff' ?>;
      font-weight: 800;
      cursor: pointer;
      font-size: 0.88rem;
      min-height: 44px;
    }
    .clubs-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      max-height: 420px;
      overflow-y: auto;
      padding-right: 4px;
    }
    .club-item {
      background: <?= $theme === 'dark' ? 'rgba(255,255,255,0.03)' : '#f1f5f9' ?>;
      border: 1px solid <?= $theme === 'dark' ? 'rgba(255,255,255,0.06)' : '#e2e8f0' ?>;
      border-radius: 10px;
      padding: 10px 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
      transition: border-color 0.15s;
    }
    .club-item:hover {
      border-color: <?= $theme === 'dark' ? 'rgba(0,240,255,0.4)' : '#0066cc' ?>;
    }
    .club-info-main {
      flex: 1;
      min-width: 0;
    }
    .club-name {
      font-size: 0.92rem;
      font-weight: 700;
      color: <?= $theme === 'dark' ? '#ffffff' : '#0f172a' ?>;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .club-meta {
      font-size: 0.78rem;
      color: #94a3b8;
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 2px;
    }
    .club-actions {
      display: flex;
      gap: 6px;
      flex-shrink: 0;
    }
    .btn-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 700;
      text-decoration: none;
      min-height: 44px;
      min-width: 44px;
    }
    .btn-call {
      background: rgba(37, 211, 102, 0.15);
      border: 1px solid #25d366;
      color: #25d366;
    }
    .btn-view {
      background: rgba(0, 240, 255, 0.12);
      border: 1px solid #00f0ff;
      color: #00f0ff;
    }
    .widget-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 10px;
      padding-top: 8px;
      border-top: 1px solid <?= $theme === 'dark' ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)' ?>;
      font-size: 0.72rem;
      color: #64748b;
    }
    .widget-footer a {
      color: #00f0ff;
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="widget-container">
  <div class="widget-header">
    <div>
      <h1 class="widget-title">
        <span>📍 Trova un Club CAT</span>
      </h1>
      <div class="widget-sub">Rete Auto-Mutuo-Aiuto Hudolin · Gratuito e Aperto a Tutti</div>
    </div>
    <div style="font-size: 0.75rem; color: #a7f3d0; font-weight: 700;">
      <?= $totalClubsCount ?> Presidi in Italia
    </div>
  </div>

  <form method="GET" action="widget-club.php" class="search-box">
    <input type="hidden" name="theme" value="<?= htmlspecialchars($theme) ?>">
    <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Inserisci comune o provincia (es. Rovigo, Verona, Milano)..." autocomplete="off">
    <button type="submit">Cerca</button>
  </form>

  <div class="clubs-list">
    <?php if (empty($clubs)): ?>
      <div style="padding: 20px; text-align: center; color: #94a3b8; font-size: 0.85rem;">
        Nessun presidio trovato per "<?= htmlspecialchars($query) ?>".<br>
        <a href="widget-club.php?theme=<?= htmlspecialchars($theme) ?>" style="color: #00f0ff; font-weight: bold; margin-top: 6px; display: inline-block;">Mostra tutti i club d'Italia</a>
      </div>
    <?php else: ?>
      <?php foreach ($clubs as $c): 
          $families = (int)($c['families_count'] ?: 11);
          $isProv = strpos($c['entity_name'], 'APCAT') !== false || $c['level'] === 'PROVINCIAL_APCAT';
          $famLabel = $isProv ? "{$families} famiglie nella rete" : "{$families} famiglie nel cerchio";
      ?>
        <div class="club-item">
          <div class="club-info-main">
            <div class="club-name" title="<?= htmlspecialchars($c['entity_name']) ?>">
              <?= htmlspecialchars($c['entity_name']) ?>
            </div>
            <div class="club-meta">
              <?= htmlspecialchars($c['city']) ?> (<?= htmlspecialchars($c['province']) ?>)
              <?php if (!empty($c['meeting_day'])): ?>
                · <?= htmlspecialchars($c['meeting_day']) ?> <?= htmlspecialchars($c['meeting_time'] ?? '') ?>
              <?php endif; ?>
              · <span style="color: #67e8f9;"><?= $famLabel ?></span>
            </div>
          </div>
          <div class="club-actions">
            <?php if (!empty($c['phone'])): ?>
              <a href="tel:<?= htmlspecialchars(preg_replace('/[^\d+]/', '', $c['phone'])) ?>" class="btn-action btn-call" title="Chiama">📞</a>
            <?php endif; ?>
            <a href="https://dependex.social/world-club-explorer.php?id=<?= $c['id'] ?>" target="_blank" rel="noopener" class="btn-action btn-view" title="Apri scheda dettagli">Mappa ↗</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="widget-footer">
    <span>Dati aggiornati Censimento 2026</span>
    <div style="display:flex; gap:10px; align-items:center;">
      <a href="widget-generator.php" target="_blank" rel="noopener" style="color: #67e8f9; font-size: 0.72rem; text-decoration: none;">Incorpora nel tuo Comune ↗</a>
      <a href="https://dependex.social/mappa-club.php" target="_blank" rel="noopener">Mappa Completa 2D ↗</a>
    </div>
  </div>
</div>

</body>
</html>
