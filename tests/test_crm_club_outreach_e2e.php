<?php
/**
 * tests/test_crm_club_outreach_e2e.php
 * Suite di Test E2E per il CRM Club Italia e il Motore di Email Marketing FLUX100 / EMM+
 */

$passCount = 0;
$failCount = 0;

function assertTest($name, $condition, $details = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$name}" . ($details ? " — {$details}" : "") . "\n";
        $failCount++;
    }
}

echo "=== TEST E2E: CRM CLUB ITALIA & EMAIL MARKETING FLUX100 / EMM+ ===\n\n";

$dbPath = __DIR__ . '/../data/acat_community.sqlite';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("PRAGMA busy_timeout = 10000;");

// 1. Controllo Database & Censimento CRM
echo "[1] Controllo Tabella CRM crm_club_contacts...\n";
$totalClubs = (int)$db->query("SELECT count(*) FROM crm_club_contacts")->fetchColumn();
assertTest("Tabella crm_club_contacts contiene tutti i 395 Club", $totalClubs >= 395, "Trovati: {$totalClubs}");

$directEmails = (int)$db->query("SELECT count(*) FROM crm_club_contacts WHERE email_type = 'DIRECT'")->fetchColumn();
assertTest("Rilevate almeno 175 email dirette", $directEmails >= 175, "Trovate: {$directEmails}");

$inheritedEmails = (int)$db->query("SELECT count(*) FROM crm_club_contacts WHERE email_type = 'COORDINATION_INHERITED'")->fetchColumn();
assertTest("Rilevate almeno 220 email ereditate da APCAT/ACAT/ARCAT/AICAT", $inheritedEmails >= 220, "Trovate: {$inheritedEmails}");

$totalPhones = (int)$db->query("SELECT count(*) FROM crm_club_contacts WHERE primary_phone IS NOT NULL AND primary_phone != ''")->fetchColumn();
assertTest("Copertura telefonica al 100% (395 su 395)", $totalPhones === $totalClubs, "Telefoni: {$totalPhones}/{$totalClubs}");

$uniqueTokens = (int)$db->query("SELECT count(DISTINCT unsubscribe_token) FROM crm_club_contacts")->fetchColumn();
assertTest("395 Token RFC 8058 univoci generati", $uniqueTokens === $totalClubs, "Token univoci: {$uniqueTokens}/{$totalClubs}");

// 2. Controllo Famiglie e Gerarchia
echo "\n[2] Controllo Famiglie e Gerarchia Territoriale...\n";
$totalFamilies = (int)$db->query("SELECT sum(families_count) FROM crm_club_contacts")->fetchColumn();
assertTest("Totale famiglie censite nel CRM supera 50.000", $totalFamilies > 50000, "Famiglie: {$totalFamilies}");

$catCount = (int)$db->query("SELECT count(*) FROM crm_club_contacts WHERE category = 'CAT'")->fetchColumn();
$apcatCount = (int)$db->query("SELECT count(*) FROM crm_club_contacts WHERE category = 'APCAT'")->fetchColumn();
$arcatCount = (int)$db->query("SELECT count(*) FROM crm_club_contacts WHERE category = 'ARCAT'")->fetchColumn();
assertTest("Presenza simultanea di CAT, APCAT e ARCAT", $catCount > 0 && $apcatCount > 0 && $arcatCount > 0, "CAT: {$catCount}, APCAT: {$apcatCount}, ARCAT: {$arcatCount}");

// 3. Controllo Template Email FLUX100 / EMM+
echo "\n[3] Controllo Template di Nurturing (Step 1 - 5)...\n";
$tplDir = __DIR__ . '/../automation/emailflux/templates';
for ($s = 1; $s <= 5; $s++) {
    $files = glob("{$tplDir}/club_step{$s}_*.html");
    assertTest("Template Step {$s} esiste", count($files) > 0);
    if (count($files) > 0) {
        $content = file_get_contents($files[0]);
        assertTest("Template Step {$s} contiene placeholder {{entity_name}} e {{unsubscribe_token}}", 
            strpos($content, '{{entity_name}}') !== false && strpos($content, '{{unsubscribe_token}}') !== false);
        assertTest("Template Step {$s} contiene riferimento a dependex.social", 
            strpos($content, 'dependex.social') !== false);
    }
}

// 4. Controllo Esportazione CSV Master
echo "\n[4] Controllo File CSV Master CRM...\n";
$csvPath = __DIR__ . '/../data/CRM_CLUB_CONTATTI_MASTER_2026.csv';
assertTest("File CSV Master CRM esiste", file_exists($csvPath));
if (file_exists($csvPath)) {
    $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    assertTest("File CSV contiene header e almeno 395 righe", count($lines) >= 396, "Righe: " . count($lines));
}

// 5. Controllo Endpoint Unsubscribe & Console Web CRM
echo "\n[5] Controllo Pagine Web CRM & Unsubscribe RFC 8058...\n";
$sample = $db->query("SELECT unsubscribe_token, entity_name FROM crm_club_contacts LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$testToken = $sample['unsubscribe_token'];

$unsubOut = shell_exec("php -d display_errors=1 unsubscribe.php \"token={$testToken}\" 2>&1");
assertTest("unsubscribe.php risponde senza errori fatali", strpos($unsubOut, 'Fatal error') === false);

$crmOut = shell_exec("php -d display_errors=1 crm-clubs.php 2>&1");
assertTest("crm-clubs.php compila senza errori fatali", strpos($crmOut, 'Fatal error') === false);
assertTest("crm-clubs.php contiene dicitura DATABASE CRM NAZIONALE", strpos($crmOut, 'DATABASE CRM NAZIONALE') !== false);

// 6. Controllo Deontologico & Bonifica Terminologica
echo "\n[6] Controllo Deontologico & Bonifica Terminologica...\n";
$forbiddenWords = ['magico', 'magic', 'm.a.g.i.c.', 'giorgian putanu', '81plus'];
$filesToCheck = [
    __DIR__ . '/../crm-clubs.php',
    __DIR__ . '/../unsubscribe.php',
    __DIR__ . '/../docs/STRATEGIA_EMAIL_MARKETING_CRM_CLUB_FLUX100_EMM.md',
    __DIR__ . '/../bin/populate_crm_clubs.py',
    __DIR__ . '/../bin/crm_clubs_dispatcher.py'
];

$clean = true;
foreach ($filesToCheck as $f) {
    if (file_exists($f)) {
        $text = strtolower(file_get_contents($f));
        foreach ($forbiddenWords as $bad) {
            if (strpos($text, $bad) !== false) {
                $clean = false;
                echo "  [FAIL] Parola vietata '{$bad}' trovata in " . basename($f) . "\n";
            }
        }
    }
}
assertTest("Zero termini vietati nei nuovi moduli CRM ed email marketing", $clean);

echo "\n=======================================================\n";
echo "ESITO FINALE: {$passCount} PASSATI, {$failCount} FALLITI\n";
echo "=======================================================\n";

exit($failCount === 0 ? 0 : 1);
