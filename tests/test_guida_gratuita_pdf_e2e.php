<?php
/**
 * Test E2E per Guida Gratuita PDF & Salvataggio nel Database Unico di DEPENDEX
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../email-engine.php';

echo "=== TEST SUITE: GUIDA GRATUITA PDF & DATABASE UNICO DEPENDEX ===\n\n";

$passCount = 0;
$failCount = 0;

function assert_test(bool $cond, string $msg): void {
    global $passCount, $failCount;
    if ($cond) {
        echo "  [PASS] $msg\n";
        $passCount++;
    } else {
        echo "  [FAIL] $msg\n";
        $failCount++;
    }
}

// 1. Verifica esistenza file PDF fisico compilato
$pdfPath = __DIR__ . '/../assets/docs/Guida_Primi_7_Giorni_Famiglia_DEPENDEX.pdf';
assert_test(file_exists($pdfPath), "File PDF fisico presente in assets/docs: " . basename($pdfPath));
$pdfSize = file_exists($pdfPath) ? filesize($pdfPath) : 0;
assert_test($pdfSize > 10000, "Dimensione del file PDF adeguata ($pdfSize bytes > 10KB)");

$headerBytes = file_exists($pdfPath) ? substr((string)file_get_contents($pdfPath, false, null, 0, 5), 0, 5) : '';
assert_test($headerBytes === '%PDF-', "Magic bytes PDF validi (%PDF-)");

// 2. Simula invio form con dati utente reali
$testEmail = 'test_famiglia_' . time() . '@dependex.support';
$testNome = 'Marco e Giulia';
$testCitta = 'Rovigo';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = $testEmail;
$_POST['nome'] = $testNome;
$_POST['citta'] = $testCitta;
$_POST['privacy_accepted'] = '1';

ob_start();
include __DIR__ . '/../guida-gratuita.php';
$htmlOutput = ob_get_clean();

assert_test(strpos($htmlOutput, 'La tua Guida') !== false, "Pagina restituisce conferma avvenuta registrazione");
assert_test(strpos($htmlOutput, 'guida-gratuita.php?download=pdf') !== false, "Pulsante di download PDF diretto presente nell'output HTML");
assert_test(strpos($htmlOutput, 'Scarica PDF Diretto') !== false, "Etichetta del pulsante 'Scarica PDF Diretto' corretta");

// 3. Verifica salvataggio nel database unico di dependex (acat_community.sqlite)
$pdo = db();

// Tabella form_submissions
$st = $pdo->prepare("SELECT * FROM form_submissions WHERE payload_json LIKE ? ORDER BY id DESC LIMIT 1");
$st->execute(['%' . $testEmail . '%']);
$sub = $st->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($sub), "Record salvato con successo in form_submissions");
if (!empty($sub)) {
    $payload = json_decode($sub['payload_json'], true);
    assert_test(($payload['email'] ?? '') === $testEmail, "Payload form_submissions contiene email corretta");
    assert_test(($payload['nome'] ?? '') === $testNome, "Payload form_submissions contiene nome corretto");
    assert_test(($payload['citta'] ?? '') === $testCitta, "Payload form_submissions contiene citta corretta");
    assert_test($sub['form_sic_id'] === 'FORM-GUIDA-7-GIORNI', "form_sic_id corretto (FORM-GUIDA-7-GIORNI)");
}

// Tabella crm_club_inquiries
$stInq = $pdo->prepare("SELECT * FROM crm_club_inquiries WHERE contact_email = ? ORDER BY id DESC LIMIT 1");
$stInq->execute([$testEmail]);
$inq = $stInq->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($inq), "Lead registrato con successo nel CRM (crm_club_inquiries)");
if (!empty($inq)) {
    assert_test($inq['contact_name'] === $testNome, "CRM contact_name corretto ($testNome)");
    assert_test($inq['entity_name'] === 'GUIDA_GRATUITA_7_GIORNI', "CRM entity_name corretto (GUIDA_GRATUITA_7_GIORNI)");
}

// Tabella consent_log
$stCons = $pdo->prepare("SELECT * FROM consent_log WHERE user_sic_id = ? ORDER BY id DESC LIMIT 1");
$stCons->execute([$testEmail]);
$cons = $stCons->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($cons), "Consenso GDPR salvato in consent_log");

// Tabella funnel_events
$stFun = $pdo->prepare("SELECT * FROM funnel_events WHERE page = 'guida-gratuita.php' AND action = 'DOWNLOAD_PDF' AND event_data LIKE ? LIMIT 1");
$stFun->execute(['%' . $testEmail . '%']);
$fun = $stFun->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($fun), "Evento di funnel salvato in funnel_events");

// 4. Verifica arruolamento nel database dell'Email Marketing OS (emailflux.db)
$emPdo = email_os_db();
$stEm = $emPdo->prepare("SELECT * FROM ghl_contact WHERE email = ? LIMIT 1");
$stEm->execute([$testEmail]);
$contact = $stEm->fetch(PDO::FETCH_ASSOC);
assert_test(!empty($contact), "Contatto arruolato in emailflux.db (ghl_contact)");
if (!empty($contact)) {
    assert_test($contact['status'] === 'ATTIVO', "Status contatto 'ATTIVO' in ghl_contact");
    assert_test($contact['flusso'] === 'FLOW_WELCOME', "Flusso benvenuto assegnato in ghl_contact");
}

// 5. Verifica bonifica lessicale su guida-gratuita.php
$content = file_get_contents(__DIR__ . '/../guida-gratuita.php');
$forbidden = ['magico', 'magic', 'giorgian putanu', '81plus'];
$clean = true;
foreach ($forbidden as $w) {
    if (stripos($content, $w) !== false) {
        $clean = false;
        break;
    }
}
assert_test($clean, "Zero termini vietati in guida-gratuita.php");

echo "\n============================================\n";
echo "ESITO TEST GUIDA GRATUITA: $passCount PASS, $failCount FAIL\n";

if ($failCount > 0) {
    exit(1);
}
