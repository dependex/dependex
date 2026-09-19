<?php
/**
 * Generatore automatico della sitemap per tutti i 572 Club Alcologici Territoriali, ACAT, ARCAT e AICAT.
 * Genera sitemap-clubs.xml valido per Google Search Console e motori di ricerca.
 */

require_once __DIR__ . '/../bootstrap.php';

echo "=== Generazione Sitemap Dinamica dei Club ===\n";

$db = db();
$st = $db->query("SELECT sic_id, entity_name, region, updated_at FROM dependex_world_registry ORDER BY id ASC");
$clubs = $st->fetchAll();

$domain = "https://dependex.social";
$today = date('Y-m-d');

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
$xml .= "        xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";

$count = 0;
foreach ($clubs as $c) {
    $sicId = $c['sic_id'];
    if (empty($sicId)) continue;
    
    $loc = $domain . "/club/" . urlencode($sicId);
    $lastmod = !empty($c['updated_at']) ? substr($c['updated_at'], 0, 10) : $today;
    
    $xml .= "  <url>\n";
    $xml .= "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
    $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
    $xml .= "    <changefreq>weekly</changefreq>\n";
    $xml .= "    <priority>0.80</priority>\n";
    $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"it\" href=\"" . htmlspecialchars($loc, ENT_XML1) . "\"/>\n";
    $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"x-default\" href=\"" . htmlspecialchars($loc, ENT_XML1) . "\"/>\n";
    $xml .= "  </url>\n";
    $count++;
}

$xml .= "</urlset>\n";

$targetPath = __DIR__ . '/../sitemap-clubs.xml';
file_put_contents($targetPath, $xml);

echo "Sitemap generata con successo: $targetPath\n";
echo "Totale URL Club indicizzati: $count\n";
