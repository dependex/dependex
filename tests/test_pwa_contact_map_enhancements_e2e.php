<?php
/**
 * DEPENDEX.SOCIAL — TEST SUITE: POTENZIAMENTI 1, 2, 3 E2E VERIFICATION
 * 1. PWA Offline Shell Caching per Mappe e Numeri di Emergenza (800 974250)
 * 2. Contact Bridge "Zero Barriere" con Notifica Telegram/Email per i Club
 * 3. Lazy-Loading Progressivo GeoJSON & MarkerCluster per la Mappa 2D
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

echo "====================================================================\n";
echo "DEPENDEX.SOCIAL — VERIFICA POTENZIAMENTI 1, 2 E 3 E2E\n";
echo "====================================================================\n\n";

$tests = 0;
$errors = 0;

function assertCheck(string $desc, bool $cond, int &$errors, int &$tests): void {
    $tests++;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
        $errors++;
    }
}

// -----------------------------------------------------------------------------
// [POTENZIAMENTO 1] PWA Offline Shell Caching & Numeri Emergenza
// -----------------------------------------------------------------------------
echo "[POTENZIAMENTO 1] PWA Offline Shell Caching per Mappe e Numeri di Emergenza\n";

$offlineHtml = file_get_contents(__DIR__ . '/../offline.html');
assertCheck("offline.html contiene Numero Verde Nazionale AICAT 800 974250", strpos($offlineHtml, '800 974250') !== false || strpos($offlineHtml, '800974250') !== false, $errors, $tests);
assertCheck("offline.html contiene pulsante a 1 tocco tel:800974250", strpos($offlineHtml, 'href="tel:800974250"') !== false, $errors, $tests);
assertCheck("offline.html contiene motore di ricerca offline per presidi territoriali", strpos($offlineHtml, 'dxFilterOfflineClubs') !== false, $errors, $tests);
assertCheck("offline.html include dx-pwa-companion.js", strpos($offlineHtml, 'dx-pwa-companion.js') !== false, $errors, $tests);

$swJs = file_get_contents(__DIR__ . '/../service-worker.js');
assertCheck("service-worker.js pre-carica offline.html", strpos($swJs, "'offline.html'") !== false, $errors, $tests);
assertCheck("service-worker.js pre-carica mappa-club.php", strpos($swJs, "'mappa-club.php'") !== false, $errors, $tests);
assertCheck("service-worker.js pre-carica world-club-explorer.php", strpos($swJs, "'world-club-explorer.php'") !== false, $errors, $tests);
assertCheck("service-worker.js pre-carica dx-pwa-companion.js", strpos($swJs, "'assets/js/dx-pwa-companion.js'") !== false, $errors, $tests);

$companionJs = file_get_contents(__DIR__ . '/../assets/js/dx-pwa-companion.js');
assertCheck("dx-pwa-companion.js memorizza presidi in localStorage", strpos($companionJs, 'localStorage.setItem') !== false && strpos($companionJs, 'dx_cached_clubs') !== false, $errors, $tests);
assertCheck("dx-pwa-companion.js contiene numero verde 800 974250", strpos($companionJs, '800 974250') !== false, $errors, $tests);

// -----------------------------------------------------------------------------
// [POTENZIAMENTO 2] Contact Bridge Zero Barriere con Notifica Telegram/Email
// -----------------------------------------------------------------------------
echo "\n[POTENZIAMENTO 2] Contact Bridge Zero Barriere con Notifica Telegram/Email per i Club\n";

$db = db();
$tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='crm_club_inquiries'")->fetchColumn();
assertCheck("Tabella crm_club_inquiries presente nel database", $tableCheck === 'crm_club_inquiries', $errors, $tests);

$clubPublic = file_get_contents(__DIR__ . '/../club-public.php');
assertCheck("club-public.php contiene scheda Contact Bridge Zero Barriere", strpos($clubPublic, 'id="contact-bridge-card"') !== false, $errors, $tests);
assertCheck("club-public.php contiene titolo 'Vorrei partecipare al prossimo incontro'", strpos($clubPublic, 'Vorrei partecipare al prossimo incontro') !== false, $errors, $tests);
assertCheck("club-public.php contiene pulsante rapido WhatsApp con testo rassicurante", strpos($clubPublic, 'wa.me') !== false && strpos($clubPublic, 'Scrivi su WhatsApp al Referente') !== false, $errors, $tests);
assertCheck("club-public.php gestisce azione POST contact_bridge_request", strpos($clubPublic, "\$_POST['action'] === 'contact_bridge_request'") !== false, $errors, $tests);
assertCheck("club-public.php salva richiesta in crm_club_inquiries", strpos($clubPublic, 'INSERT INTO crm_club_inquiries') !== false, $errors, $tests);
assertCheck("club-public.php invia notifica email al referente del Club", strpos($clubPublic, 'Nuova Richiesta di Partecipazione al Club') !== false, $errors, $tests);
assertCheck("club-public.php invia notifica Telegram", strpos($clubPublic, 'send_telegram_notification') !== false, $errors, $tests);

$bootstrap = file_get_contents(__DIR__ . '/../bootstrap.php');
assertCheck("bootstrap.php definisce funzione send_telegram_notification", strpos($bootstrap, 'function send_telegram_notification') !== false, $errors, $tests);

// -----------------------------------------------------------------------------
// [POTENZIAMENTO 3] Lazy-Loading Progressivo GeoJSON & MarkerCluster Mappa 2D
// -----------------------------------------------------------------------------
echo "\n[POTENZIAMENTO 3] Lazy-Loading Progressivo GeoJSON per la Mappa 2D\n";

$mappaClub = file_get_contents(__DIR__ . '/../mappa-club.php');
assertCheck("mappa-club.php abilita chunkedLoading in markerClusterGroup", strpos($mappaClub, 'chunkedLoading: true') !== false, $errors, $tests);
assertCheck("mappa-club.php definisce chunkInterval e chunkDelay", strpos($mappaClub, 'chunkInterval: 50') !== false && strpos($mappaClub, 'chunkDelay: 10') !== false, $errors, $tests);
assertCheck("mappa-club.php rimuove marker fuori bounds (removeOutsideVisibleBounds)", strpos($mappaClub, 'removeOutsideVisibleBounds: true') !== false, $errors, $tests);
assertCheck("mappa-club.php aggiunge cluster in batch con addLayers", strpos($mappaClub, 'markerClusterGroup.addLayers(clusterMarkers)') !== false, $errors, $tests);
assertCheck("mappa-club.php implementa caricamento progressivo card a blocchi", strpos($mappaClub, 'CARD_BATCH_SIZE = 30') !== false, $errors, $tests);
assertCheck("mappa-club.php include pulsante di caricamento rapido successivi club", strpos($mappaClub, 'btnLoadMoreClubs') !== false, $errors, $tests);
assertCheck("mappa-club.php include listener infinite scroll su .club-list-panel", strpos($mappaClub, "querySelector('.club-list-panel')") !== false && strpos($mappaClub, 'scrollHeight') !== false, $errors, $tests);

// -----------------------------------------------------------------------------
// [GOVERNANCE] Bonifica Lessicale & Hard Constraints
// -----------------------------------------------------------------------------
echo "\n[GOVERNANCE] Verifica Conformità & Hard Constraints\n";
$bannedWords = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
$filesToCheck = [
    'offline.html' => $offlineHtml,
    'service-worker.js' => $swJs,
    'assets/js/dx-pwa-companion.js' => $companionJs,
    'club-public.php' => $clubPublic,
    'mappa-club.php' => $mappaClub,
    'bootstrap.php' => $bootstrap
];

foreach ($filesToCheck as $fn => $content) {
    foreach ($bannedWords as $word) {
        $found = (stripos($content, $word) !== false);
        assertCheck("Zero occorrenze di '{$word}' in {$fn}", !$found, $errors, $tests);
    }
}

echo "\n--------------------------------------------------------------------\n";
echo "Risultati: {$tests} test eseguiti. Errori: {$errors}\n";
echo "--------------------------------------------------------------------\n";

if ($errors === 0) {
    echo "ESITO: PASSED — Tutti i 3 potenziamenti sono verificati e conformi al 100%!\n";
    exit(0);
} else {
    echo "ESITO: FAILED\n";
    exit(1);
}
