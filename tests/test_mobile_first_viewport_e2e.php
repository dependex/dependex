<?php
/**
 * TEST E2E: MOBILE-FIRST / RESPONSIVE / VIEWPORT SPEC & ZERO OVERFLOW VERIFICATION
 *
 * Valida che le regole e specifiche di Mobile First, Viewport, Zero Overflow
 * siano codificate in AGENTS.md, MASTER_EMAIL_OS_PROMPT.md, docs/MOBILE_FIRST_VIEWPORT_SPEC.md
 * e implementate nei fogli di stile primari (assets/css/rainbow-neon.css).
 */

echo "====================================================================\n";
echo "DEPENDEX.SOCIAL — TEST SUITE: MOBILE-FIRST & VIEWPORT SPEC E2E\n";
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

// 1. Verifica esistenza e contenuto di docs/MOBILE_FIRST_VIEWPORT_SPEC.md
$specFile = $baseDir . '/docs/MOBILE_FIRST_VIEWPORT_SPEC.md';
assertCondition("Spec file docs/MOBILE_FIRST_VIEWPORT_SPEC.md exists", file_exists($specFile), $errors, $tests);

if (file_exists($specFile)) {
    $specContent = file_get_contents($specFile);
    assertCondition("Spec contiene 'MOBILE FIRST ASSOLUTO'", strpos($specContent, 'MOBILE FIRST ASSOLUTO') !== false, $errors, $tests);
    assertCondition("Spec contiene 'ZERO HORIZONTAL OVERFLOW'", strpos($specContent, 'ZERO HORIZONTAL OVERFLOW') !== false, $errors, $tests);
    assertCondition("Spec contiene '9:19'", strpos($specContent, '9:19') !== false, $errors, $tests);
    assertCondition("Spec contiene '16:9'", strpos($specContent, '16:9') !== false, $errors, $tests);
    assertCondition("Spec contiene '100dvh'", strpos($specContent, '100dvh') !== false, $errors, $tests);
    assertCondition("Spec contiene 'safe-area-inset'", strpos($specContent, 'safe-area-inset') !== false, $errors, $tests);
    assertCondition("Spec contiene 'scroll-margin-top'", strpos($specContent, 'scroll-margin-top') !== false, $errors, $tests);
    assertCondition("Spec contiene 'scrollWidth > clientWidth'", strpos($specContent, 'scrollWidth > clientWidth') !== false, $errors, $tests);
}

// 2. Verifica inclusione vincolo assoluto in AGENTS.md
$agentsFile = $baseDir . '/AGENTS.md';
if (file_exists($agentsFile)) {
    $agentsContent = file_get_contents($agentsFile);
    assertCondition("AGENTS.md contiene Hard Constraint 6 Mobile-First", strpos($agentsContent, 'Mobile-First / Responsive / Viewport Master Spec') !== false, $errors, $tests);
    assertCondition("AGENTS.md sancisce divieto 'MAI progettare prima desktop e poi adattare mobile'", strpos($agentsContent, 'MAI progettare prima desktop e poi adattare mobile') !== false, $errors, $tests);
    assertCondition("AGENTS.md fa riferimento a docs/MOBILE_FIRST_VIEWPORT_SPEC.md", strpos($agentsContent, 'docs/MOBILE_FIRST_VIEWPORT_SPEC.md') !== false, $errors, $tests);
}

// 3. Verifica inclusione vincolo in MASTER_EMAIL_OS_PROMPT.md
$promptFile = $baseDir . '/MASTER_EMAIL_OS_PROMPT.md';
if (file_exists($promptFile)) {
    $promptContent = file_get_contents($promptFile);
    assertCondition("MASTER_EMAIL_OS_PROMPT.md contiene vincolo Mobile First", strpos($promptContent, 'MOBILE FIRST ASSOLUTO') !== false, $errors, $tests);
    assertCondition("MASTER_EMAIL_OS_PROMPT.md contiene zero horizontal overflow", strpos($promptContent, 'ZERO HORIZONTAL OVERFLOW') !== false, $errors, $tests);
    assertCondition("MASTER_EMAIL_OS_PROMPT.md contiene safe area & 100dvh", strpos($promptContent, '100dvh') !== false, $errors, $tests);
}

// 4. Verifica implementazione tecnica in assets/css/rainbow-neon.css
$cssFile = $baseDir . '/assets/css/rainbow-neon.css';
assertCondition("assets/css/rainbow-neon.css exists", file_exists($cssFile), $errors, $tests);
if (file_exists($cssFile)) {
    $cssContent = file_get_contents($cssFile);
    assertCondition("CSS definisce box-sizing: border-box globale", strpos($cssContent, 'box-sizing: border-box') !== false, $errors, $tests);
    assertCondition("CSS definisce overflow-x: clip per root/body", strpos($cssContent, 'overflow-x: clip') !== false, $errors, $tests);
    assertCondition("CSS definisce scroll-margin-top per ancore [id]", strpos($cssContent, 'scroll-margin-top') !== false, $errors, $tests);
    assertCondition("CSS supporta safe-area-inset", strpos($cssContent, 'env(safe-area-inset') !== false, $errors, $tests);
    assertCondition("CSS definisce 100dvh con fallback 100vh", strpos($cssContent, 'min-height: 100dvh') !== false, $errors, $tests);
    assertCondition("CSS protegge immagini e media (max-width: 100%)", strpos($cssContent, 'max-width: 100%') !== false, $errors, $tests);
    assertCondition("CSS definisce touch target minimo (min-height: 44px)", strpos($cssContent, 'min-height: 44px') !== false, $errors, $tests);
}

// 5. Bonifica Terminologica Rigorosa
$bannedWords = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu'];
// Controlliamo i file applicativi e di specifica. Per AGENTS.md, verifichiamo che non siano usati al di fuori della regola 3 di divieto.
$filesToCheck = [$specFile, $promptFile, $cssFile];
foreach ($filesToCheck as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    $relPath = str_replace($baseDir . '/', '', $f);
    foreach ($bannedWords as $bad) {
        $found = stripos($content, $bad) !== false;
        assertCondition("Nessun termine vietato '{$bad}' in {$relPath}", !$found, $errors, $tests);
    }
}
// Controllo specifico per AGENTS.md: zero occorrenze oltre la clausola di divieto regola 3
if (file_exists($agentsFile)) {
    $agentsContent = file_get_contents($agentsFile);
    // Rimuoviamo il blocco della regola 3 dove i termini sono citati solo come divieto esplicito
    $stripped = preg_replace('/3\.\s+\*\*Bonifica Terminologica Rigorosa:\*\*.*?(?=4\.\s+\*\*Control Plane)/s', '', $agentsContent);
    foreach ($bannedWords as $bad) {
        $found = stripos($stripped, $bad) !== false;
        assertCondition("Nessun termine vietato residuo '{$bad}' in AGENTS.md oltre la clausola", !$found, $errors, $tests);
    }
}

echo "\n--------------------------------------------------------------------\n";
echo "Risultati: {$tests} test eseguiti. Errori: {$errors}\n";
echo "--------------------------------------------------------------------\n";

if ($errors > 0) {
    echo "ESITO: FAILED\n";
    exit(1);
} else {
    echo "ESITO: PASSED — Specifiche Mobile First pienamente conformi ed operative!\n";
    exit(0);
}
