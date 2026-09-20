<?php
/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * Feed Territoriale RFC 4287 / GeoRSS Presidi Club Hudolin (ACAT / APCAT / ARCAT)
 * 
 * Interoperabilità per Ser.D, ASL, Servizi Sociali, Medici di Medicina Generale, Terzo Settore
 * Output: XML Atom 1.0 + GeoRSS (Punti Lat/Lon) + Metadati Famiglie
 */

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/atom+xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
header('Access-Control-Allow-Origin: *');

$db_file = __DIR__ . '/data/acat_community.sqlite';
$clubs = [];

if (file_exists($db_file)) {
    try {
        $db = new PDO("sqlite:" . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $region_filter = trim($_GET['region'] ?? '');
        $query_filter = trim($_GET['q'] ?? '');
        
        $sql = "SELECT id, entity_name as name, region, city, address, meeting_time, phone, email, latitude as lat, longitude as lon, families_count, updated_at 
                FROM cat_clubs_italy WHERE 1=1";
        $params = [];
        
        if (!empty($region_filter)) {
            $sql .= " AND region LIKE :region";
            $params[':region'] = "%{$region_filter}%";
        }
        
        if (!empty($query_filter)) {
            $sql .= " AND (city LIKE :q OR entity_name LIKE :q OR address LIKE :q)";
            $params[':q'] = "%{$query_filter}%";
        }
        
        $sql .= " ORDER BY region ASC, city ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $clubs = [];
    }
}

$now_iso = date(DATE_ATOM);
$total_clubs = count($clubs);
$feed_url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'dependex.social') . '/api-feed-territorio.php';

echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss">
  <title>Rete Presidi Territoriali Club Hudolin (ACAT / APCAT) · DEPENDEX</title>
  <subtitle>Feed ufficiale interoperabile per Ser.D, ASL, Servizi Sociali e Famiglie - <?= $total_clubs ?> presidi censiti</subtitle>
  <link rel="self" type="application/atom+xml" href="<?= htmlspecialchars($feed_url, ENT_QUOTES) ?>" />
  <link rel="alternate" type="text/html" href="https://dependex.social/mappa-club.php" />
  <id>urn:uuid:dependex-cat-network-feed-2026</id>
  <updated><?= $now_iso ?></updated>
  <author>
    <name>DEPENDEX &amp; OLTRE Rete Hudolin</name>
    <email>info@dependex.support</email>
    <uri>https://dependex.social</uri>
  </author>
  <rights>Open Data - Uso Sociale e Sanitario Territoriale Non Commerciale</rights>
  <category term="Salute Pubblica" label="Prevenzione e Riabilitazione Multifamiliare" />

<?php foreach ($clubs as $c): 
    $entry_id = 'urn:dependex:club:' . $c['id'];
    $entry_updated = !empty($c['updated_at']) ? date(DATE_ATOM, strtotime($c['updated_at'])) : $now_iso;
    $detail_url = "https://dependex.social/world-club-explorer.php?id=" . $c['id'];
    $families = (int)($c['families_count'] ?? 11);
    $lat = (float)($c['lat'] ?? 0);
    $lon = (float)($c['lon'] ?? 0);
?>
  <entry>
    <title><?= htmlspecialchars($c['name'] . ' (' . $c['city'] . ', ' . $c['region'] . ')', ENT_QUOTES) ?></title>
    <link rel="alternate" type="text/html" href="<?= htmlspecialchars($detail_url, ENT_QUOTES) ?>" />
    <id><?= htmlspecialchars($entry_id, ENT_QUOTES) ?></id>
    <updated><?= $entry_updated ?></updated>
    <summary type="text">Presidio territoriale Metodo Hudolin a <?= htmlspecialchars($c['city'], ENT_QUOTES) ?> (<?= htmlspecialchars($c['region'], ENT_QUOTES) ?>). Accoglie attivamente <?= $families ?> famiglie.</summary>
    <content type="html"><![CDATA[
      <div style="font-family: sans-serif; line-height: 1.5;">
        <p><strong>Comunità:</strong> <?= htmlspecialchars($c['name']) ?></p>
        <p><strong>Località:</strong> <?= htmlspecialchars($c['address']) ?>, <?= htmlspecialchars($c['city']) ?> (<?= htmlspecialchars($c['region']) ?>)</p>
        <p><strong>Incontri settimanali:</strong> <?= htmlspecialchars($c['meeting_time'] ?: 'Contattare il club per orario riunione') ?></p>
        <p><strong>Telefono di ascolto:</strong> <a href="tel:<?= htmlspecialchars($c['phone']) ?>"><?= htmlspecialchars($c['phone'] ?: 'N/D') ?></a></p>
        <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($c['email'] ?: 'info@dependex.support') ?>"><?= htmlspecialchars($c['email'] ?: 'info@dependex.support') ?></a></p>
        <p><strong>Dimensione Comunità:</strong> <?= $families ?> famiglie accompagnate nel cerchio multifamiliare</p>
        <p><a href="<?= htmlspecialchars($detail_url) ?>" style="color: #0088cc; font-weight: bold;">Accedi alla scheda completa e calcola percorso geodetico &raquo;</a></p>
      </div>
    ]]></content>
<?php if ($lat != 0 && $lon != 0): ?>
    <georss:point><?= sprintf("%.6f %.6f", $lat, $lon) ?></georss:point>
<?php endif; ?>
    <category term="<?= htmlspecialchars($c['region'], ENT_QUOTES) ?>" label="Regione" />
    <category term="Hudolin" label="Metodo Multifamiliare" />
  </entry>
<?php endforeach; ?>
</feed>
