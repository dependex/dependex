<?php
/**
 * tests/test_data_integrity.php
 * Automated Data Integrity & Numerical Truth Test Suite per DEPENDEX.SOCIAL.
 * 
 * Verifica che ogni metrica pubblica derivi dalla Single Source of Truth,
 * rispetti i vincoli di non-duplicazione e sia coerente tra Database, API, UI e SEO.
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/clubs/ClubMetricsService.php';

use Dependex\Clubs\ClubMetricsService;

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$errors = [];

function assert_check(string $title, bool $condition, string $details = ''): void {
    global $totalTests, $passedTests, $failedTests, $errors;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$title}\n";
    } else {
        $failedTests++;
        $msg = "  [FAIL] {$title}" . ($details ? " -> {$details}" : "");
        echo $msg . "\n";
        $errors[] = $msg;
    }
}

echo "====================================================================\n";
echo "DEPENDEX.SOCIAL — DATA INTEGRITY & NUMERICAL TRUTH VERIFICATION\n";
echo "====================================================================\n\n";

$pdo = db();
$nat = ClubMetricsService::getNationalSummary($pdo);
$glob = ClubMetricsService::getGlobalSummary($pdo);

// TEST 1: Consistenza Presidi Nazionali
echo "[1] Controllo Presidi Nazionali...\n";
assert_check(
    "Totale presidi censiti in Italia è esattamente 1.761",
    $nat['total_presidi'] === 1761,
    "Rilevati: " . $nat['total_presidi']
);

// TEST 2: Scomposizione Gerarchica (Club Locali + Enti di Coordinamento)
echo "\n[2] Controllo Scomposizione Gerarchica...\n";
$sumLevels = $nat['local_clubs'] + $nat['coordination_entities'];
assert_check(
    "Club locali (1.414) + Coordinamenti (347) == Totale presidi (1.761)",
    $sumLevels === $nat['total_presidi'] && $nat['local_clubs'] === 1414 && $nat['coordination_entities'] === 347,
    "Locali: {$nat['local_clubs']}, Coordinamenti: {$nat['coordination_entities']}, Somma: {$sumLevels}"
);

// TEST 3: Trasparenza Stati di Incontro
echo "\n[3] Controllo Stati di Incontro...\n";
$sumMeetings = $nat['verified_meetings'] + $nat['tbd_meetings'];
assert_check(
    "Incontri verificati (285) + Da concordare (1.476) == Totale presidi (1.761)",
    $sumMeetings === $nat['total_presidi'] && $nat['verified_meetings'] === 285 && $nat['tbd_meetings'] === 1476,
    "Verificati: {$nat['verified_meetings']}, Da concordare: {$nat['tbd_meetings']}"
);

// TEST 4: Non-Duplicazione del Conteggio Famiglie
echo "\n[4] Controllo Stima Famiglie (Zero Duplicazioni)...\n";
assert_check(
    "Stima famiglie è compresa tra 14.000 e 18.000 (calcolata solo sui Club locali)",
    $nat['estimated_families'] >= 14000 && $nat['estimated_families'] <= 18000,
    "Rilevate: {$nat['estimated_families']} (deve escludere le duplicazioni da 50k-85k)"
);

// TEST 5: Copertura Geografica
echo "\n[5] Controllo Copertura Territoriale...\n";
assert_check(
    "Presenza in tutte le 20 Regioni d'Italia",
    $nat['regions_count'] === 20,
    "Regioni: {$nat['regions_count']}"
);
assert_check(
    "Copertura superiore a 100 Province e 800 Comuni",
    $nat['provinces_count'] >= 100 && $nat['cities_count'] >= 800,
    "Province: {$nat['provinces_count']}, Comuni: {$nat['cities_count']}"
);

// TEST 6: Rete Mondiale
echo "\n[6] Controllo Rete Mondiale...\n";
assert_check(
    "Nodi mondiali totali sono 2.064 in 38 Paesi",
    $glob['total_nodes'] === 2064 && $glob['countries_count'] === 38,
    "Nodi: {$glob['total_nodes']}, Paesi: {$glob['countries_count']}"
);

// TEST 7: Single Source of Truth API
echo "\n[7] Controllo API Public Metrics...\n";
$apiOutput = shell_exec('php ' . escapeshellarg(__DIR__ . '/../api-public-metrics.php'));
$apiJson = json_decode((string)$apiOutput, true);
assert_check(
    "api-public-metrics.php restituisce status OK e dati validati",
    !empty($apiJson['ok']) && ($apiJson['data']['italy']['total_presidi'] ?? 0) === 1761,
    "Output API: " . substr((string)$apiOutput, 0, 100)
);

// TEST 8: Assenza di Claim Obsoleti Hardcoded (542)
echo "\n[8] Controllo Assenza Residui Hardcoded (542)...\n";
$indexContent = file_get_contents(__DIR__ . '/../index.php');
$manifestContent = file_get_contents(__DIR__ . '/../manifest.webmanifest');
assert_check(
    "index.php non contiene fallback hardcoded 542",
    strpos($indexContent, '542') === false,
    "Rilevato '542' in index.php"
);
assert_check(
    "manifest.webmanifest non contiene '542 Club'",
    strpos($manifestContent, '542 Club') === false,
    "Rilevato '542 Club' in manifest.webmanifest"
);

// TEST 9: Assenza di Residui Hardcoded (395)
echo "\n[9] Controllo Assenza Residui Hardcoded (395)...\n";
$adminContent = file_get_contents(__DIR__ . '/../admin.php');
$dashContent = file_get_contents(__DIR__ . '/../dashboard.php');
$widgetContent = file_get_contents(__DIR__ . '/../templates/_club_locator_widget.php');
assert_check(
    "admin.php non contiene stringhe statiche '395 Club'",
    strpos($adminContent, '395 Club') === false && strpos($adminContent, '395 censiti') === false,
    "Rilevato 395 in admin.php"
);
assert_check(
    "dashboard.php non contiene '395 in Italia'",
    strpos($dashContent, '395 in Italia') === false,
    "Rilevato '395 in Italia' in dashboard.php"
);
assert_check(
    "templates/_club_locator_widget.php riporta 1.761 presidi",
    strpos($widgetContent, '1.761 PRESIDI') !== false,
    "Mancata corrispondenza nel widget"
);

// TEST 10: Parametri Etici e di Accessibilità Economica
echo "\n[10] Controllo Parametri Etici & Deontologici...\n";
assert_check(
    "Numero Verde Nazionale è 800 974250",
    $nat['toll_free_number'] === '800 974250'
);
assert_check(
    "Costo partecipazione Club per le famiglie è 0,00 €",
    $nat['cost_for_families'] === '0,00 €'
);

echo "\n====================================================================\n";
echo "RISULTATO SUITE TEST: {$passedTests} / {$totalTests} PASSATI";
if ($failedTests > 0) {
    echo " ({$failedTests} FALLITI)\n";
    echo "====================================================================\n";
    exit(1);
} else {
    echo " (100% SUCCESS — DATA INTEGRITY VERIFIED)\n";
    echo "====================================================================\n";
    exit(0);
}
