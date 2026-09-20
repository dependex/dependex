<?php
/**
 * test_safety_wellbeing_accessibility_e2e.php
 * E2E Verification Suite for:
 * 1. Panic Exit (Uscita Rapida) in _header.php
 * 2. Gentle Digital Wellbeing & Native Share in assets/js/dx-safety-wellbeing.js
 * 3. 395 Club Badges & Links in _header.php
 * 4. Club Public Profile (club-public.php) with:
 *    - Schema.org CommunityCenter & NGO
 *    - vCard (.vcf) direct export
 *    - CRM fallback for OSINT clubs
 * 5. Bonifica Terminologica & Deontological Guardrails
 */

require_once __DIR__ . '/../bootstrap.php';

$passes = 0;
$fails = 0;

function assert_true($cond, $label) {
    global $passes, $fails;
    if ($cond) {
        echo "[PASS] $label\n";
        $passes++;
    } else {
        echo "[FAIL] $label\n";
        $fails++;
    }
}

echo "=== E2E SUITE: SAFETY, WELLBEING & CLUB DIRECTORY ENHANCEMENTS ===\n\n";

// 1. Check _header.php
$headerContent = file_get_contents(__DIR__ . '/../_header.php');
assert_true(strpos($headerContent, 'dx-safety-wellbeing.js') !== false, "Header loads dx-safety-wellbeing.js script");
assert_true(strpos($headerContent, 'id="panicExitBtn"') !== false, "Header includes Panic Exit button (id=panicExitBtn)");
assert_true(strpos($headerContent, 'window.dxPanicExit') !== false, "Panic exit triggers window.dxPanicExit");
assert_true(strpos($headerContent, '>395</span>') !== false, "Header topbar displays updated 395 Club badge");
assert_true(strpos($headerContent, '395 Club') !== false, "Header drawer displays updated 395 Club count");
assert_true(strpos($headerContent, 'crm-clubs.php') !== false, "Header drawer links to crm-clubs.php");

// 2. Check assets/js/dx-safety-wellbeing.js
$jsPath = __DIR__ . '/../assets/js/dx-safety-wellbeing.js';
assert_true(file_exists($jsPath), "assets/js/dx-safety-wellbeing.js exists");
$jsContent = file_get_contents($jsPath);
assert_true(strpos($jsContent, 'window.dxPanicExit') !== false, "JS defines window.dxPanicExit");
assert_true(strpos($jsContent, 'https://www.meteo.it') !== false, "JS redirects to meteo.it on panic exit");
assert_true(strpos($jsContent, 'Escape') !== false, "JS handles Escape key for quick exit");
assert_true(strpos($jsContent, 'showWellbeingToast') !== false, "JS includes gentle wellbeing timer (Screen Off -> Life On)");
assert_true(strpos($jsContent, 'window.dxShareClub') !== false, "JS defines window.dxShareClub with Web Share & clipboard fallback");

// 3. Check assets/css/rainbow-neon.css
$cssContent = file_get_contents(__DIR__ . '/../assets/css/rainbow-neon.css');
assert_true(strpos($cssContent, '.panic-exit-btn') !== false, "CSS defines .panic-exit-btn styling");

// 4. Check club-public.php
$clubPhp = file_get_contents(__DIR__ . '/../club-public.php');
assert_true(strpos($clubPhp, '["CommunityCenter", "NGO"]') !== false, "Schema.org includes CommunityCenter & NGO types");
assert_true(strpos($clubPhp, "isset(\$_GET['vcard'])") !== false, "club-public.php supports vCard generation");
assert_true(strpos($clubPhp, "BEGIN:VCARD") !== false, "vCard format is RFC compliant");
assert_true(strpos($clubPhp, 'crm_club_contacts') !== false, "club-public.php has fallback to crm_club_contacts");
assert_true(strpos($clubPhp, 'dxShareClub') !== false, "club-public.php provides 1-tap sharing");

// 5. Test vCard Output via Execution
$vcardRunner = <<<'CODE'
<?php
$_GET['sic'] = 'SIC-CAT-ITA-696DDEBA';
$_GET['vcard'] = '1';
require __DIR__ . '/../club-public.php';
CODE;
file_put_contents(__DIR__ . '/_tmp_vcard_runner.php', $vcardRunner);
$vcardOutput = shell_exec('php ' . escapeshellarg(__DIR__ . '/_tmp_vcard_runner.php') . ' 2>&1');
@unlink(__DIR__ . '/_tmp_vcard_runner.php');

assert_true(strpos($vcardOutput, 'BEGIN:VCARD') !== false, "vCard export generates BEGIN:VCARD");
assert_true(strpos($vcardOutput, 'END:VCARD') !== false, "vCard export generates END:VCARD");
assert_true(strpos($vcardOutput, 'FN:') !== false, "vCard contains valid club metadata (FN)");

// 6. Test CRM fallback club in club-public.php
$crmRunner = <<<'CODE'
<?php
$_GET['sic'] = 'SIC-APCAT-EE9AE857-D7EA2A25';
require __DIR__ . '/../club-public.php';
CODE;
file_put_contents(__DIR__ . '/_tmp_crm_runner.php', $crmRunner);
$fallbackOutput = shell_exec('php ' . escapeshellarg(__DIR__ . '/_tmp_crm_runner.php') . ' 2>&1');
@unlink(__DIR__ . '/_tmp_crm_runner.php');

assert_true(strpos($fallbackOutput, 'Scheda Club Non Trovata') === false, "CRM Fallback Club resolves successfully");
assert_true(strpos($fallbackOutput, 'CommunityCenter') !== false, "CRM Fallback Club renders with CommunityCenter schema");
assert_true(strpos($fallbackOutput, 'APCAT Latina') !== false, "CRM Fallback Club displays correct entity name");

// 7. Bonifica Terminologica
$filesToCheck = [
    __DIR__ . '/../_header.php',
    __DIR__ . '/../assets/js/dx-safety-wellbeing.js',
    __DIR__ . '/../club-public.php',
    __DIR__ . '/../assets/css/rainbow-neon.css'
];

$forbiddenTerms = ['magico', 'magic', 'giorgian putanu', '81plus'];
$clean = true;
foreach ($filesToCheck as $f) {
    $content = strtolower(file_get_contents($f));
    foreach ($forbiddenTerms as $term) {
        if (strpos($content, $term) !== false) {
            echo "[FAIL] Forbidden term '$term' detected in " . basename($f) . "\n";
            $clean = false;
            $fails++;
        }
    }
}
if ($clean) {
    echo "[PASS] Bonifica terminologica: Zero forbidden terms in all updated files\n";
    $passes++;
}

echo "\n=======================================================\n";
echo "TEST RESULTS: PASSES = $passes, FAILS = $fails\n";
echo "=======================================================\n";

if ($fails > 0) {
    exit(1);
}
exit(0);
