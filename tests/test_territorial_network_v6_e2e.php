<?php
/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * Test E2E Suite: Territorial Network & Sovereign PWA Engine V6.3
 * 
 * Verifica automatizzata end-to-end:
 * 1. Database presidi (395 Club territoriali & famiglie)
 * 2. GeoJSON RFC 7946 Standard Endpoint
 * 3. Feed Territoriale Atom 1.0 + GeoRSS
 * 4. Widget Iframe Embed Trova-Club
 * 5. Moduli PWA (Voice SOS, Micro-Checkin, Local Reminders, Service Worker v6.3)
 * 6. Conformità Deontologica (Zero punteggi di benessere, zero parole vietate)
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $desc, bool $condition, string $details = ''): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        $testsPassed++;
        echo "  [PASS] {$desc}\n";
    } else {
        $testsFailed++;
        echo "  [FAIL] {$desc}" . ($details ? " -> {$details}" : '') . "\n";
    }
}

echo "=== TEST E2E: RETE TERRITORIALE, OPEN DATA & PWA SOVRANA V6.3 ===\n\n";

// TEST 1: Database Presidi e Conteggio Famiglie
echo "[1] Controllo Database SQLite & Censimento Famiglie...\n";
$dbFile = __DIR__ . '/../data/acat_community.sqlite';
$pdo = new PDO("sqlite:{$dbFile}", null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
$pdo->exec('PRAGMA busy_timeout = 10000;');
$clubCount = (int)$pdo->query("SELECT COUNT(*) FROM cat_clubs_italy")->fetchColumn();
assertTest("Censimento nazionale ha almeno 395 Club", $clubCount >= 395, "Trovati {$clubCount}");

$familiesSum = (int)$pdo->query("SELECT SUM(families_count) FROM cat_clubs_italy WHERE level != 'NATIONAL'")->fetchColumn();
assertTest("Totale famiglie censite nella rete supera 50.000", $familiesSum >= 50000, "Totale calcolato: {$familiesSum}");

$sampleClub = $pdo->query("SELECT * FROM cat_clubs_italy WHERE entity_name LIKE '%Taglio di Po%' AND level = 'LOCAL_CLUB' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
assertTest("Presidio locale Taglio di Po presente con coordinate e 11 famiglie", 
    $sampleClub && (float)$sampleClub['latitude'] > 44 && (int)$sampleClub['families_count'] === 11,
    "Club: " . json_encode($sampleClub)
);

// TEST 2: Endpoint GeoJSON RFC 7946 (Subprocess per isolamento headers)
echo "\n[2] Controllo Endpoint OpenData GeoJSON RFC 7946...\n";
$geoJsonOut = shell_exec('php ' . escapeshellarg(__DIR__ . '/../api-opendata-geojson.php'));
$geoData = json_decode((string)$geoJsonOut, true);
assertTest("GeoJSON produce JSON valido", is_array($geoData));
assertTest("GeoJSON ha type FeatureCollection", ($geoData['type'] ?? '') === 'FeatureCollection');
assertTest("Features contengono almeno 395 elementi", count($geoData['features'] ?? []) >= 395);

$firstFeature = $geoData['features'][0] ?? null;
assertTest("Feature ha coordinate valide [lon, lat]", 
    isset($firstFeature['geometry']['coordinates'][0], $firstFeature['geometry']['coordinates'][1]) &&
    $firstFeature['geometry']['coordinates'][0] > 6 && $firstFeature['geometry']['coordinates'][0] < 19
);
assertTest("Feature include metadati famiglie (families_count)", isset($firstFeature['properties']['families_count']));

// TEST 3: Feed Territoriale Atom 1.0 + GeoRSS (Subprocess per isolamento headers)
echo "\n[3] Controllo Feed Territoriale Atom/GeoRSS...\n";
$atomOut = shell_exec('php ' . escapeshellarg(__DIR__ . '/../api-feed-territorio.php'));
assertTest("Feed produce XML Atom valido", strpos((string)$atomOut, 'xmlns="http://www.w3.org/2005/Atom"') !== false);
assertTest("Feed include namespace GeoRSS", strpos((string)$atomOut, 'xmlns:georss="http://www.georss.org/georss"') !== false);
assertTest("Feed contiene tag <georss:point>", strpos((string)$atomOut, '<georss:point>') !== false);
assertTest("Feed dichiara presidi censiti (>= 395)", preg_match('/(\d+)\s+presidi censiti/', (string)$atomOut, $m) && intval($m[1]) >= 395);

// TEST 4: Widget Iframe Embed Trova-Club
echo "\n[4] Controllo Widget Embed Iframe...\n";
ob_start();
$_GET['q'] = 'Rovigo';
require __DIR__ . '/../widget-club.php';
$widgetOut = ob_get_clean();

assertTest("Widget compila HTML valido", strpos($widgetOut, '<!DOCTYPE html>') !== false);
assertTest("Widget contiene container dedicato", strpos($widgetOut, 'widget-container') !== false);
assertTest("Widget applica filtro query e trova Rovigo", strpos($widgetOut, 'Rovigo') !== false);
assertTest("Widget contiene pulsanti di chiamata diretta tel:", strpos($widgetOut, 'href="tel:') !== false);

// TEST 5: Moduli PWA & Asset Sovrani
echo "\n[5] Controllo File e Moduli PWA On-Device...\n";
$swPath = __DIR__ . '/../service-worker.js';
$swContent = file_get_contents($swPath);
assertTest("Service Worker aggiornato alla v6.3+", preg_match('/dependex-pwa-v6\.[3-9]/', $swContent) === 1);
assertTest("Service Worker ha caching per GeoJSON e Feed Territoriale", 
    strpos($swContent, 'api-opendata-geojson.php') !== false && strpos($swContent, 'api-feed-territorio.php') !== false
);

$voiceSosPath = __DIR__ . '/../assets/js/dx-voice-sos.js';
assertTest("Modulo dx-voice-sos.js esiste ed è non-vuoto", file_exists($voiceSosPath) && filesize($voiceSosPath) > 1000);

$checkinPath = __DIR__ . '/../assets/js/dx-micro-checkin.js';
assertTest("Modulo dx-micro-checkin.js esiste ed è non-vuoto", file_exists($checkinPath) && filesize($checkinPath) > 1000);

$remindersPath = __DIR__ . '/../assets/js/dx-local-reminders.js';
assertTest("Modulo dx-local-reminders.js esiste ed è non-vuoto", file_exists($remindersPath) && filesize($remindersPath) > 1000);

// TEST 6: Bonifica Terminologica e Conformità Deontologica
echo "\n[6] Controllo Deontologico & Bonifica Terminologica...\n";
$bannedTerms = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
$filesToCheck = [
    __DIR__ . '/../widget-club.php',
    __DIR__ . '/../api-opendata-geojson.php',
    __DIR__ . '/../api-feed-territorio.php',
    __DIR__ . '/../assets/js/dx-voice-sos.js',
    __DIR__ . '/../assets/js/dx-micro-checkin.js',
    __DIR__ . '/../assets/js/dx-local-reminders.js',
    __DIR__ . '/../service-worker.js'
];

$allClean = true;
foreach ($filesToCheck as $f) {
    $c = file_get_contents($f);
    foreach ($bannedTerms as $term) {
        if (stripos($c, $term) !== false) {
            $allClean = false;
            echo "  [FAIL] Termine vietato '{$term}' trovato in " . basename($f) . "\n";
        }
    }
}
assertTest("Zero termini vietati nei nuovi moduli e template", $allClean);

// RIEPILOGO FINALE
echo "\n=======================================================\n";
echo "ESITO FINALE: {$testsPassed} PASSATI, {$testsFailed} FALLITI\n";
echo "=======================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
