<?php
/**
 * Test E2E per DEPENDEX Human Welfare Engine 5.0
 * Metodo Karpathy (Spec -> Verifier -> Environment)
 * Verifica:
 * 1. HumanWelfareEngine 5.0 (Sense of Coherence, Self-Determination, COM-B, Micro-Journeys, Small Steps <= 3, Welfare Compass, Community Capital, Contribution Engine)
 * 2. orientamento.php & welfare-compass.php (compilazione e integrazione)
 * 3. Divieto assoluto di "Wellness Score", classifiche o percentuali di rischio
 * 4. Hard Constraints & Bonifica Terminologica: zero 'chakra', zero 'magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'
 * 5. Governance: docs/HUMAN_WELFARE_ENGINE_5.md, AGENTS.md, MASTER_EMAIL_OS_PROMPT.md
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

echo "=== INIZIO TEST SUITE E2E: DEPENDEX HUMAN WELFARE ENGINE 5.0 ===\n\n";

$root = dirname(__DIR__);

// 1. Verifica Modelli Motore HumanWelfareEngine
echo "[TEST SET 1: HumanWelfareEngine 5.0 Multi-Model Framework]\n";
$engineFile = $root . '/modules/welfare/HumanWelfareEngine.php';
assert_test("HumanWelfareEngine.php exists", file_exists($engineFile));

require_once $engineFile;
assert_test("Class HumanWelfareEngine loaded", class_exists('HumanWelfareEngine'));

// Salutogenesi: Sense of Coherence
$soc = HumanWelfareEngine::getSenseOfCoherence();
assert_test("Sense of Coherence returns 3 dimensions", count($soc) === 3);
assert_test("SoC contains comprensibilita", isset($soc['comprensibilita']));
assert_test("SoC contains gestibilita", isset($soc['gestibilita']));
assert_test("SoC contains significato", isset($soc['significato']));

// Self-Determination Theory
$sdt = HumanWelfareEngine::getSelfDetermination();
assert_test("Self-Determination returns 3 pillars", count($sdt) === 3);
assert_test("SDT contains autonomia", isset($sdt['autonomia']));
assert_test("SDT contains competenza", isset($sdt['competenza']));
assert_test("SDT contains relazione", isset($sdt['relazione']));

// COM-B Architecture
$comb = HumanWelfareEngine::getComBAnalysis('club');
assert_test("COM-B contains capability", isset($comb['capability']));
assert_test("COM-B contains opportunity", isset($comb['opportunity']));
assert_test("COM-B contains motivation", isset($comb['motivation']));

// Micro-Journeys Engine
echo "\n[TEST SET 2: Micro-Journeys & Small Steps Engine]\n";
$journeys = HumanWelfareEngine::getMicroJourneys();
assert_test("Returns 3 micro-journeys", count($journeys) === 3);
assert_test("Contains 'non_so_da_dove_iniziare'", isset($journeys['non_so_da_dove_iniziare']));
assert_test("Contains 'cerco_un_club'", isset($journeys['cerco_un_club']));
assert_test("Contains 'aiuto_una_persona'", isset($journeys['aiuto_una_persona']));

// Small Steps Engine: massimo 3 opzioni (vincolo anti-overwhelm)
$smallSteps = HumanWelfareEngine::getSmallSteps('radicamento');
assert_test("Small steps count is strictly <= 3", count($smallSteps) <= 3 && count($smallSteps) > 0);

// Welfare Compass
echo "\n[TEST SET 3: Welfare Compass & Community Capital]\n";
$compass = HumanWelfareEngine::getWelfareCompassAreas();
assert_test("Welfare Compass contains exactly 12 areas", count($compass) === 12);
assert_test("Compass contains 'corpo' and 'sicurezza'", isset($compass['corpo']) && isset($compass['sicurezza']));
assert_test("Compass contains 'relazioni' and 'autonomia'", isset($compass['relazioni']) && isset($compass['autonomia']));
assert_test("Compass contains 'partecipazione'", isset($compass['partecipazione']));

// Explainable Recommendations
$explanation = HumanWelfareEngine::explainRecommendation('club', 'Club Rovigo Centro', 'Rovigo martedi sera');
assert_test("Explainable recommendation contains transparency phrase", strpos($explanation, 'Ti mostriamo') !== false);

// Community Capital
$capital = HumanWelfareEngine::getCommunityCapitalOverview();
assert_test("Community Capital includes 1.770+ clubs", strpos($capital['clubs_count'], '1.770+') !== false);
assert_test("Community Capital includes free access 100%", $capital['free_access'] === '100%');

// Contribution Paths
$contrib = HumanWelfareEngine::getContributionPaths();
assert_test("Contribution Engine returns 6 stages", count($contrib) === 6);
assert_test("First stage is RICEVERE", $contrib[0]['stage'] === 'RICEVERE');
assert_test("Last stage is GENERARE COMUNITÀ", end($contrib)['stage'] === 'GENERARE COMUNITÀ');

// 4. Verifica Frontend orientamento.php & welfare-compass.php
echo "\n[TEST SET 4: Frontend UI & Trauma-Informed UX]\n";
$orientFile = $root . '/orientamento.php';
$orientContent = file_get_contents($orientFile);
assert_test("orientamento.php contains Micro-Journey Engine", strpos($orientContent, 'MICRO-JOURNEY ENGINE') !== false);
assert_test("orientamento.php contains Welfare Compass", strpos($orientContent, 'WELFARE COMPASS') !== false);
assert_test("orientamento.php contains Sense of Coherence", strpos($orientContent, 'Sense of Coherence') !== false);
assert_test("orientamento.php contains Self-Determination", strpos($orientContent, 'SELF-DETERMINATION THEORY') !== false);
assert_test("orientamento.php contains Small Steps Engine", strpos($orientContent, 'SMALL STEPS ENGINE') !== false);
assert_test("orientamento.php contains Contribution Engine", strpos($orientContent, 'CONTRIBUTION ENGINE') !== false);
assert_test("orientamento.php contains Community Capital", strpos($orientContent, 'COMMUNITY CAPITAL') !== false);

$welfareAlias = $root . '/welfare-compass.php';
assert_test("welfare-compass.php exists", file_exists($welfareAlias));

// 5. Divieto Assoluto Wellness Score & Chakra (Hard Constraints)
echo "\n[TEST SET 5: Divieto Assoluto Wellness Score & Chakra]\n";
assert_test("orientamento.php does NOT contain 'wellness score'", stripos($orientContent, 'wellness score') === false || stripos($orientContent, 'Nessun "wellness score"') !== false);
assert_test("HumanWelfareEngine.php does NOT contain 'wellness score'", stripos(file_get_contents($engineFile), 'wellness score') === false);
assert_test("orientamento.php does NOT contain 'chakra'", stripos($orientContent, 'chakra') === false);
assert_test("HumanWelfareEngine.php does NOT contain 'chakra'", stripos(file_get_contents($engineFile), 'chakra') === false);

// Bonifica terminologica
$forbiddenTerms = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
foreach ($forbiddenTerms as $term) {
    assert_test("orientamento.php does not contain '{$term}'", stripos($orientContent, $term) === false);
}

// 6. Governance & Documentazione
echo "\n[TEST SET 6: Governance & Specifica Engine 5.0]\n";
$specFile = $root . '/docs/HUMAN_WELFARE_ENGINE_5.md';
assert_test("docs/HUMAN_WELFARE_ENGINE_5.md exists", file_exists($specFile));
$specContent = file_get_contents($specFile);
assert_test("HUMAN_WELFARE_ENGINE_5.md contains Multi-Model Human Framework", strpos($specContent, 'MULTI-MODEL HUMAN FRAMEWORK') !== false);
assert_test("HUMAN_WELFARE_ENGINE_5.md contains Salutogenesi", strpos($specContent, 'SALUTOGENESI') !== false);
assert_test("HUMAN_WELFARE_ENGINE_5.md contains Small Steps Engine", strpos($specContent, 'SMALL STEPS ENGINE') !== false);

$agentsContent = file_get_contents($root . '/AGENTS.md');
assert_test("AGENTS.md references Human Welfare Engine 5.0", strpos($agentsContent, 'Human Welfare Engine 5.0') !== false);

$promptContent = file_get_contents($root . '/MASTER_EMAIL_OS_PROMPT.md');
assert_test("MASTER_EMAIL_OS_PROMPT.md references Human Welfare Engine 5.0", strpos($promptContent, 'ENGINE 5.0') !== false);

echo "\n======================================================\n";
echo "RISULTATO FINALE: {$passedCount}/{$testCount} test superati con successo.\n";

if ($passedCount === $testCount) {
    echo "VERIFIER PASS: Human Welfare Engine 5.0 verificato al 100%!\n";
    exit(0);
} else {
    echo "VERIFIER FAIL: Alcuni test sono falliti.\n";
    exit(1);
}
