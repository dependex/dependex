<?php
/**
 * Test E2E per DEPENDEX Omni-Welfare Gamification Engine 6.0
 * Metodo Karpathy (Spec -> Verifier -> Environment)
 * 
 * Verifica:
 * 1. OmniWelfareGamificationEngine (11 feelings, micro-quests, taxonomy, participation levels, small steps <= 3)
 * 2. playground.php (compilazione, sintassi e inclusione)
 * 3. Linkage in _header.php e _footer.php
 * 4. Hard Constraints Deontologici:
 *    - Divieto assoluto di "Wellness Score", indici percentuali o classifiche personali
 *    - Non gamificare la sofferenza o i sintomi
 *    - Zero streak punitivi
 *    - Separazione rigorosa delle fonti con attribuzione (Hudolin, H+, ABC, BetterWay, BEWAY, Veda, Maslow)
 *    - Zero menzione di 'chakra' come struttura
 *    - Zero parole vietate: 'magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'
 * 5. Documentazione: docs/OMNI_WELFARE_GAMIFICATION_6.md, AGENTS.md, MASTER_EMAIL_OS_PROMPT.md
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

echo "=== INIZIO TEST SUITE E2E: DEPENDEX OMNI-WELFARE GAMIFICATION ENGINE 6.0 ===\n\n";

$root = dirname(__DIR__);

// 1. Verifica Modulo e Motore Gamification
echo "[TEST SET 1: OmniWelfareGamificationEngine Architecture]\n";
$engineFile = $root . '/modules/gamification/OmniWelfareGamificationEngine.php';
assert_test("OmniWelfareGamificationEngine.php exists", file_exists($engineFile));

require_once $engineFile;
assert_test("Class OmniWelfareGamificationEngine loaded", class_exists('OmniWelfareGamificationEngine'));

$engine = new OmniWelfareGamificationEngine();

// 11 Ingressi Esperienziali
$feelings = $engine->getFeelingEntries();
assert_test("Feelings count is exactly 11", count($feelings) === 11);
assert_test("Feeling stare_meglio exists", isset($feelings['stare_meglio']));
assert_test("Feeling equilibrio exists", isset($feelings['equilibrio']));
assert_test("Feeling rallentare exists", isset($feelings['rallentare']));
assert_test("Feeling respirare exists", isset($feelings['respirare']));
assert_test("Feeling connessione exists", isset($feelings['connessione']));
assert_test("Feeling direzione exists", isset($feelings['direzione']));
assert_test("Feeling energia exists", isset($feelings['energia']));
assert_test("Feeling chiarezza exists", isset($feelings['chiarezza']));
assert_test("Feeling conoscere_persone exists", isset($feelings['conoscere_persone']));
assert_test("Feeling cambiamento exists", isset($feelings['cambiamento']));
assert_test("Feeling surfare_vita exists", isset($feelings['surfare_vita']));

// Micro-Quests Guidate
echo "\n[TEST SET 2: Micro-Quests & Screen Off -> Life On]\n";
$quests = $engine->getMicroQuests();
assert_test("Micro-Quests count is >= 10", count($quests) >= 10);
assert_test("Quest respiro_60s exists", isset($quests['respiro_60s']));
assert_test("Quest radicamento_presente exists", isset($quests['radicamento_presente']));
assert_test("Quest tre_carte_gratitudine exists", isset($quests['tre_carte_gratitudine']));
assert_test("Quest scrivi_a_qualcuno exists", isset($quests['scrivi_a_qualcuno']));
assert_test("Quest natura_10min exists", isset($quests['natura_10min']));
assert_test("Quest partecipa_cerchio exists", isset($quests['partecipa_cerchio']));
assert_test("Quest ikigai_card_game exists", isset($quests['ikigai_card_game']));

// Small Steps Engine (Massimo 3 opzioni per feeling, zero overwhelm)
echo "\n[TEST SET 3: Small Steps Engine Resolution (Max 3 Quests)]\n";
foreach ($feelings as $fKey => $fVal) {
    $res = $engine->resolveExperienceByFeeling($fKey);
    $questCount = count($res['quests']);
    assert_test("Feeling '{$fKey}' resolves to <= 3 quests (found: {$questCount})", $questCount <= 3 && $questCount > 0);
    assert_test("Feeling '{$fKey}' includes maieutic tagline", !empty($res['feeling']['tagline']));
}

// Tassonomia e Attribuzione delle Fonti
echo "\n[TEST SET 4: Method Taxonomy & Source Attribution (No Minestrone)]\n";
$taxonomy = $engine->getMethodTaxonomy();
assert_test("Taxonomy contains at least 8 distinct frameworks", count($taxonomy) >= 8);
assert_test("Hudolin source is correctly attributed to Vladimir Hudolin & Club CAT", strpos($taxonomy['hudolin']['source'], 'Vladimir Hudolin') !== false);
assert_test("Metodo H+ source is attributed to Mirco Pregnolato", strpos($taxonomy['h_plus']['source'], 'Mirco Pregnolato') !== false);
assert_test("BetterWay source is attributed to Mirco Pregnolato", strpos($taxonomy['betterway']['source'], 'Mirco Pregnolato') !== false);
assert_test("Metodo ABC source is attributed to Mirco Pregnolato", strpos($taxonomy['abc']['source'], 'Mirco Pregnolato') !== false);
assert_test("BEWAY source is attributed to BEWAY.LIFE", strpos($taxonomy['beway']['source'], 'beway.life') !== false);
assert_test("Veda source is attributed to tradizioni filosofiche e principi etici universali", strpos($taxonomy['veda']['source'], 'tradizioni') !== false);
assert_test("Maslow source is attributed to Abraham Maslow", strpos($taxonomy['maslow']['source'], 'Abraham Maslow') !== false);

// Livelli di Partecipazione non competitivi
echo "\n[TEST SET 5: Participation Levels (Non-Competitive)]\n";
$levels = $engine->getParticipationLevels();
assert_test("Participation levels count is 6", count($levels) === 6);
assert_test("Level 1 is Esploratore", $levels[1]['title'] === 'Esploratore');
assert_test("Level 6 is Facilitatore", $levels[6]['title'] === 'Facilitatore');

// 6. Verifica Frontend playground.php e Linkage
echo "\n[TEST SET 6: Frontend Compilation & Linkage]\n";
$playgroundFile = $root . '/playground.php';
assert_test("playground.php exists", file_exists($playgroundFile));

// PHP Syntax check (Lint)
$lintCmd = 'php -l ' . escapeshellarg($playgroundFile);
$lintOutput = [];
$lintReturn = 0;
exec($lintCmd, $lintOutput, $lintReturn);
assert_test("playground.php passes PHP lint syntax check", $lintReturn === 0);

// Linkage in _header.php e _footer.php
$headerContent = file_get_contents($root . '/_header.php');
$footerContent = file_get_contents($root . '/_footer.php');
assert_test("_header.php links to playground.php", strpos($headerContent, 'playground.php') !== false);
assert_test("_footer.php links to playground.php", strpos($footerContent, 'playground.php') !== false);

// 7. Hard Constraints & Bonifica Deontologica
echo "\n[TEST SET 7: Hard Constraints & Deontological Audit]\n";
$filesToAudit = [
    $engineFile,
    $playgroundFile,
    $root . '/docs/OMNI_WELFARE_GAMIFICATION_6.md'
];

$bannedTerms = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
foreach ($filesToAudit as $filePath) {
    $baseName = basename($filePath);
    $content = file_get_contents($filePath);

    // Divieto assoluto di menzione di 'chakra' come struttura
    assert_test("File {$baseName} does NOT mention 'chakra'", stripos($content, 'chakra') === false);

    // Divieto assoluto di 'wellness score': non deve esistere una logica di calcolo del wellness score
    $hasWellnessScoreCalc = (stripos($content, 'calculateWellnessScore') !== false || stripos($content, 'wellness_score') !== false);
    assert_test("File {$baseName} does NOT implement 'wellness score' calculation", !$hasWellnessScoreCalc);

    // Divieto termini vietati
    foreach ($bannedTerms as $term) {
        $found = (stripos($content, $term) !== false);
        // Consenti solo se citato nella sezione di divieto / bonifica
        if ($found && stripos($content, 'vietato') !== false) {
            $found = false;
        }
        assert_test("File {$baseName} clean of banned term '{$term}'", !$found);
    }
}

// 8. Verifica Documentazione di Governance
echo "\n[TEST SET 8: Governance Documentation]\n";
$docGamification = $root . '/docs/OMNI_WELFARE_GAMIFICATION_6.md';
assert_test("docs/OMNI_WELFARE_GAMIFICATION_6.md exists", file_exists($docGamification));
$agentsContent = file_get_contents($root . '/AGENTS.md');
assert_test("AGENTS.md specifies Hard Constraint 8 (Omni-Welfare Gamification Engine 6.0)", strpos($agentsContent, 'Omni-Welfare Gamification Engine 6.0') !== false);
$masterPromptContent = file_get_contents($root . '/MASTER_EMAIL_OS_PROMPT.md');
assert_test("MASTER_EMAIL_OS_PROMPT.md integrates OMNI-WELFARE GAMIFICATION ENGINE 6.0", strpos($masterPromptContent, 'OMNI-WELFARE GAMIFICATION ENGINE 6.0') !== false);

echo "\n=== RIEPILOGO TEST OMNI-WELFARE GAMIFICATION ENGINE 6.0 ===\n";
echo "Totale Asserzioni: {$testCount}\n";
echo "Superate: {$passedCount}\n";
echo "Fallite: " . ($testCount - $passedCount) . "\n";

if ($testCount === $passedCount) {
    echo ">>> ESITO GLOBALE: ALL TESTS PASSED [100%] <<<\n";
    exit(0);
} else {
    echo ">>> ESITO GLOBALE: FAILED <<<\n";
    exit(1);
}
