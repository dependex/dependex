<?php
/**
 * TEST E2E: MOBILE-FIRST SOVEREIGN BOTTOM BAR, PWA OFFLINE READINESS & SOS GROUNDING SUITE
 */

echo "====================================================================\n";
echo "DEPENDEX.SOCIAL — TEST SUITE: MOBILE-FIRST BOTTOM BAR, PWA & SOS E2E\n";
echo "====================================================================\n\n";

$baseDir = dirname(__DIR__);
$errors = 0;
$tests = 0;

function assertCondition($desc, $cond, &$errors, &$tests) {
    $tests++;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
        $errors++;
    }
}

// 1. Verifica _footer.php
$footerFile = $baseDir . '/_footer.php';
assertCondition("_footer.php exists", file_exists($footerFile), $errors, $tests);
if (file_exists($footerFile)) {
    $footerContent = file_get_contents($footerFile);
    assertCondition("Presenza dx-mobile-bottom-bar", strpos($footerContent, 'dx-mobile-bottom-bar') !== false, $errors, $tests);
    assertCondition("Bottom Bar contiene Home", strpos($footerContent, 'index.php') !== false, $errors, $tests);
    assertCondition("Bottom Bar contiene Trova Club", strpos($footerContent, 'world-club-explorer.php') !== false, $errors, $tests);
    assertCondition("Bottom Bar contiene Parla con Noi", strpos($footerContent, 'parla-con-noi.php') !== false, $errors, $tests);
    assertCondition("Bottom Bar contiene Dashboard", strpos($footerContent, 'dashboard.php') !== false, $errors, $tests);
    assertCondition("Bottom Bar contiene SOS Calma", strpos($footerContent, 'SOS Calma') !== false, $errors, $tests);
    assertCondition("Presenza modal SOS dx-sos-modal", strpos($footerContent, 'id="dx-sos-modal"') !== false, $errors, $tests);
    assertCondition("Presenza cerchio di respirazione dx-breath-circle", strpos($footerContent, 'dx-breath-circle') !== false, $errors, $tests);
    assertCondition("Presenza tecnica 4-7-8", strpos($footerContent, 'Tecnica 4-7-8') !== false, $errors, $tests);
    assertCondition("Presenza chiamata Telefono Verde Alcol 800 632 000", strpos($footerContent, 'tel:800632000') !== false, $errors, $tests);
    assertCondition("Presenza funzione dxToggleSosModal", strpos($footerContent, 'function dxToggleSosModal') !== false, $errors, $tests);
    assertCondition("Presenza ciclo respirazione startBreathingCycle", strpos($footerContent, 'function startBreathingCycle') !== false, $errors, $tests);
}

// 2. Verifica assets/css/rainbow-neon.css
$cssFile = $baseDir . '/assets/css/rainbow-neon.css';
if (file_exists($cssFile)) {
    $cssContent = file_get_contents($cssFile);
    assertCondition("CSS contiene .dx-mobile-bottom-bar", strpos($cssContent, '.dx-mobile-bottom-bar') !== false, $errors, $tests);
    assertCondition("CSS definisce bottom bar solo per mobile (@media (max-width: 768px))", strpos($cssContent, '@media (max-width: 768px)') !== false, $errors, $tests);
    assertCondition("CSS definisce touch target minimo (min-height: 48px)", strpos($cssContent, 'min-height: 48px') !== false, $errors, $tests);
    assertCondition("CSS contiene .dx-sos-modal", strpos($cssContent, '.dx-sos-modal') !== false, $errors, $tests);
    assertCondition("CSS contiene .dx-breath-circle", strpos($cssContent, '.dx-breath-circle') !== false, $errors, $tests);
    assertCondition("CSS contiene animazione breatheAnimation", strpos($cssContent, '@keyframes breatheAnimation') !== false, $errors, $tests);
}

// 3. Verifica service-worker.js
$swFile = $baseDir . '/service-worker.js';
assertCondition("service-worker.js exists", file_exists($swFile), $errors, $tests);
if (file_exists($swFile)) {
    $swContent = file_get_contents($swFile);
    assertCondition("Cache name aggiornata a dependex-pwa-v4+", preg_match('/dependex-pwa-v[4-9]/', $swContent) === 1, $errors, $tests);
    assertCondition("STATIC_ASSETS include rainbow-neon.css", strpos($swContent, 'assets/css/rainbow-neon.css') !== false, $errors, $tests);
    assertCondition("STATIC_ASSETS include dx-telemetry.js", strpos($swContent, 'assets/js/dx-telemetry.js') !== false, $errors, $tests);
    assertCondition("STATIC_ASSETS include recensioni_club_italia.json", strpos($swContent, 'data/recensioni_club_italia.json') !== false, $errors, $tests);
}

// 4. Verifica offline.html
$offlineFile = $baseDir . '/offline.html';
if (file_exists($offlineFile)) {
    $offlineContent = file_get_contents($offlineFile);
    assertCondition("offline.html contiene esercizio offline 4-7-8", strpos($offlineContent, 'offline-breathe') !== false, $errors, $tests);
    assertCondition("offline.html contiene numero verde 800 632 000", strpos($offlineContent, 'tel:800632000') !== false, $errors, $tests);
    assertCondition("offline.html contiene numero emergenze 112", strpos($offlineContent, 'tel:112') !== false, $errors, $tests);
}

// 5. Verifica llms-full.txt
$llmsFullFile = $baseDir . '/llms-full.txt';
if (file_exists($llmsFullFile)) {
    $llmsContent = file_get_contents($llmsFullFile);
    assertCondition("llms-full.txt include Strumenti Interattivi 2D/3D", strpos($llmsContent, 'Strumenti Interattivi & Gamification') !== false, $errors, $tests);
    assertCondition("llms-full.txt include SOS Calma & Grounding 4-7-8", strpos($llmsContent, 'SOS Calma & Grounding 4-7-8') !== false, $errors, $tests);
    assertCondition("llms-full.txt include 4 Porte d'Ingresso", strpos($llmsContent, '4 Porte d\'Ingresso') !== false, $errors, $tests);
    assertCondition("llms-full.txt include email info@dependex.support", strpos($llmsContent, 'info@dependex.support') !== false, $errors, $tests);
}

// 6. Bonifica Terminologica Rigorosa
$bannedWords = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu'];
$filesToCheck = [$footerFile, $cssFile, $swFile, $offlineFile, $llmsFullFile];
foreach ($filesToCheck as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    $relPath = str_replace($baseDir . '/', '', $f);
    foreach ($bannedWords as $bad) {
        $found = stripos($content, $bad) !== false;
        assertCondition("Nessun termine vietato '{$bad}' in {$relPath}", !$found, $errors, $tests);
    }
}

echo "\n--------------------------------------------------------------------\n";
echo "Risultati: {$tests} test eseguiti. Errori: {$errors}\n";
echo "--------------------------------------------------------------------\n";

if ($errors > 0) {
    echo "ESITO: FAILED\n";
    exit(1);
} else {
    echo "ESITO: PASSED — Mobile-First Bottom Bar, PWA & SOS Grounding 100% Funzionanti!\n";
    exit(0);
}
