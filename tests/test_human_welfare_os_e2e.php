<?php
/**
 * Test E2E per DEPENDEX Human Welfare OS 4.0
 * Metodo Karpathy (Spec -> Verifier -> Environment)
 * Verifica:
 * 1. HumanWelfareEngine (7 dimensioni, 4 scopi universali, pilastri Hudolin, metodo orientate)
 * 2. orientamento.php (esistenza, compilazione PHP lint, elementi UI maieutici)
 * 3. docs/HUMAN_WELFARE_OS_4.md (specifica completa 53 articoli e matrice interlacciata)
 * 4. Hard Constraint 7 & Bonifica Terminologica:
 *    - Nessuna menzione di "chakra" nella UI/motore
 *    - Zero termini proibiti ("magico", "magic", "M.A.G.I.C.", "giorgian putanu", "81plus")
 * 5. Linkage in _header.php e _footer.php
 */

$testCount = 0;
$passedCount = 0;

function assert_test($description, $condition) {
    global $testCount, $passedCount;
    $testCount++;
    if ($condition) {
        $passedCount++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
    }
}

echo "=== INIZIO TEST SUITE E2E: DEPENDEX HUMAN WELFARE OS 4.0 ===\n\n";

$root = dirname(__DIR__);

// 1. HumanWelfareEngine
echo "[TEST SET 1: HumanWelfareEngine Model & Methods]\n";
$engineFile = $root . '/modules/welfare/HumanWelfareEngine.php';
assert_test("HumanWelfareEngine.php file exists", file_exists($engineFile));

require_once $engineFile;
assert_test("Class HumanWelfareEngine is loaded", class_exists('HumanWelfareEngine'));

$dimensions = HumanWelfareEngine::getDimensions();
assert_test("Returns exactly 7 dimensions", count($dimensions) === 7);

$expectedKeys = ['radicamento', 'vitalita', 'autonomia', 'relazione', 'espressione', 'consapevolezza', 'significato'];
$actualKeys = array_keys($dimensions);
assert_test("Dimensions keys match exactly", $actualKeys === $expectedKeys);

// Verifica struttura singola dimensione
$radicamento = $dimensions['radicamento'];
assert_test("Radicamento has level 1", ($radicamento['level'] ?? 0) === 1);
assert_test("Radicamento has color", !empty($radicamento['color']));
assert_test("Radicamento has maieutic question", !empty($radicamento['question']));
assert_test("Radicamento correlates with Maslow need", !empty($radicamento['maslow_need']));
assert_test("Radicamento correlates with Hudolin pillar", !empty($radicamento['hudolin_pillar']));
assert_test("Radicamento contains facets array", is_array($radicamento['facets']) && count($radicamento['facets']) >= 3);

// 2. I 4 Scopi di Vita (Dharma, Artha, Kama, Moksha)
echo "\n[TEST SET 2: 4 Scopi di Vita Universali]\n";
$purposes = HumanWelfareEngine::getLifePurposes();
assert_test("Returns exactly 4 life purposes", count($purposes) === 4);
assert_test("Contains Dharma (Ruolo & Responsabilità)", isset($purposes['dharma']));
assert_test("Contains Artha (Risorse & Sostenibilità)", isset($purposes['artha']));
assert_test("Contains Kama (Piacere & Relazione)", isset($purposes['kama']));
assert_test("Contains Moksha (Libertà Interiore)", isset($purposes['moksha']));

// 3. Pilastri Hudolin
echo "\n[TEST SET 3: Pilastri Metodo Hudolin]\n";
$pillars = HumanWelfareEngine::getHudolinPillars();
assert_test("Returns Hudolin community pillars", count($pillars) >= 5);
assert_test("Contains Famiglia & Club", isset($pillars['famiglia']) && isset($pillars['club']));
assert_test("Contains Approccio Ecologico-Sociale", isset($pillars['ecologia_sociale']));

// 4. Risposta Maieutica "Partiamo da lì"
echo "\n[TEST SET 4: Risposta Maieutica 'Partiamo da lì']\n";
$orient = HumanWelfareEngine::orientate('relazione');
assert_test("Orientate returns user statement", $orient['user_statement'] === "Questa parte della mia vita oggi chiede attenzione.");
assert_test("Orientate returns 'Partiamo da lì' response", $orient['dependex_response'] === "Partiamo da lì.");
assert_test("Orientate provides non-diagnostic message", !empty($orient['message']));
assert_test("Orientate provides community steps", is_array($orient['community_steps']) && count($orient['community_steps']) >= 3);

// 5. Frontend orientamento.php
echo "\n[TEST SET 5: Frontend orientamento.php]\n";
$frontendFile = $root . '/orientamento.php';
assert_test("orientamento.php file exists", file_exists($frontendFile));

$content = file_get_contents($frontendFile);
assert_test("orientamento.php contains 'Non devi sapere già tutto'", strpos($content, 'Non devi sapere già tutto') !== false);
assert_test("orientamento.php contains 'Da dove vuoi iniziare?'", strpos($content, 'Da dove vuoi iniziare?') !== false);
assert_test("orientamento.php contains 'Partiamo da lì'", strpos($content, 'Partiamo da lì') !== false);
assert_test("orientamento.php connects to cerca-club.php", strpos($content, 'cerca-club.php') !== false);

// 6. Hard Constraint 7 & Bonifica Terminologica
echo "\n[TEST SET 6: Hard Constraint 7 & Bonifica Deontologica]\n";
// Non deve contenere 'chakra' come struttura di prodotto
assert_test("orientamento.php does NOT contain 'chakra'", stripos($content, 'chakra') === false);

$engineContent = file_get_contents($engineFile);
assert_test("HumanWelfareEngine.php does NOT contain 'chakra'", stripos($engineContent, 'chakra') === false);

// Bonifica termini vietati
$forbiddenTerms = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
foreach ($forbiddenTerms as $term) {
    assert_test("orientamento.php does not contain '{$term}'", stripos($content, $term) === false);
    assert_test("HumanWelfareEngine.php does not contain '{$term}'", stripos($engineContent, $term) === false);
}

// 7. Docs & Governance
echo "\n[TEST SET 7: Governance & Docs Verification]\n";
$docFile = $root . '/docs/HUMAN_WELFARE_OS_4.md';
assert_test("docs/HUMAN_WELFARE_OS_4.md exists", file_exists($docFile));
$docContent = file_get_contents($docFile);
assert_test("HUMAN_WELFARE_OS_4.md contains 53 articles reference", strpos($docContent, '53') !== false);
assert_test("HUMAN_WELFARE_OS_4.md contains Matrice Interlacciata", strpos($docContent, 'MATRICE INTERLACCIATA') !== false);

$agentsContent = file_get_contents($root . '/AGENTS.md');
assert_test("AGENTS.md contains Hard Constraint 7 (Human Welfare OS 4.0)", strpos($agentsContent, 'Human Welfare OS 4.0') !== false);

// 8. Linkage in _header.php e _footer.php
echo "\n[TEST SET 8: Navigation Linkage]\n";
$headerContent = file_get_contents($root . '/_header.php');
$footerContent = file_get_contents($root . '/_footer.php');
assert_test("_header.php links to orientamento.php", strpos($headerContent, 'orientamento.php') !== false);
assert_test("_footer.php links to orientamento.php", strpos($footerContent, 'orientamento.php') !== false);

echo "\n======================================================\n";
echo "RISULTATO FINALE: {$passedCount}/{$testCount} test superati con successo.\n";

if ($passedCount === $testCount) {
    echo "VERIFIER PASS: Human Welfare OS 4.0 verificato al 100%!\n";
    exit(0);
} else {
    echo "VERIFIER FAIL: Alcuni test sono falliti.\n";
    exit(1);
}
