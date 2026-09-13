<?php
/**
 * E2E AUTOMATED TEST SUITE: ACAT TAGLIO DI PO EVENT & MULTI-GATEWAY CHECKOUT
 * Validates:
 * 1. Event DB purity (only Taglio di Po event exists)
 * 2. event_bookings DB schema
 * 3. Booking creation with distinct Nome/Cognome
 * 4. Multi-Gateway support (PayPal, USDT Polygon, On-Site)
 * 5. 28 Main Sponsor Grid count and integrity
 * 6. Strict Governance check: zero banned words / zero prohibited references
 */

declare(strict_types=1);

define('TEST_RUN_MODE', true);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/../modules/commerce/PayPalService.php';

$pdo = db();
$passed = 0;
$failed = 0;

function assertCheck(string $desc, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

echo "=== E2E TEST: ACAT TAGLIO DI PO & MULTI-GATEWAY CHECKOUT ===\n\n";

// 1. Database Events Purity
echo "1. Verifica Purezza Database Eventi:\n";
$events = $pdo->query("SELECT * FROM events")->fetchAll(PDO::FETCH_ASSOC);
assertCheck("Tabella events contiene esattamente 1 evento", count($events) === 1);
assertCheck("L'evento attivo e' SIC-EVT-ACAT-BP-2026-COMM", ($events[0]['sic_id'] ?? '') === 'SIC-EVT-ACAT-BP-2026-COMM');
assertCheck("Sede e' Taglio di Po", ($events[0]['comune'] ?? '') === 'Taglio di Po');
assertCheck("Capienza fissata a 30 posti", (int)($events[0]['capacity'] ?? 0) === 30);
assertCheck("Quota partecipazione fissata a 10.00 EUR", (float)($events[0]['price_eur'] ?? 0.0) === 10.00);

// 2. Schema event_bookings
echo "\n2. Verifica Schema Tabella event_bookings:\n";
$cols = $pdo->query("PRAGMA table_info(event_bookings)")->fetchAll(PDO::FETCH_COLUMN, 1);
$requiredCols = ['id', 'sic_id', 'first_name', 'last_name', 'full_name', 'email', 'phone', 'payment_method', 'payment_status', 'payment_tx_id', 'status', 'total_amount'];
foreach ($requiredCols as $rc) {
    assertCheck("Colonna '{$rc}' presente in event_bookings", in_array($rc, $cols, true));
}

// 3. Test Creazione Iscrizione via API in-process
echo "\n3. Test Creazione Iscrizione Multi-Gateway:\n";
$testNome = "Paolo" . rand(100, 999);
$testCognome = "Bianchi";
$testEmail = "e2e.test." . time() . "@example.com";
$testPhone = "3491234567";

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'action' => 'init_booking',
    'event_sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
    'nome' => $testNome,
    'cognome' => $testCognome,
    'email' => $testEmail,
    'phone' => $testPhone,
    'role_type' => 'Membro di Club (CAT)',
    'dietary_notes' => 'Vegetariano',
    'payment_method' => 'PAYPAL',
    'privacy_accepted' => '1'
];
$_GET = [];

ob_start();
require __DIR__ . '/../api-event-booking.php';
$output = ob_get_clean();
$res = json_decode($output, true);

assertCheck("API risponde success = true", !empty($res['success']) || !empty($res['ok']));
assertCheck("Generato codice prenotazione valido (BOOK-... o SIC-...)", !empty($res['booking_sic']));

$bookingSic = $res['booking_sic'] ?? '';
$bkStmt = $pdo->prepare("SELECT * FROM event_bookings WHERE sic_id = ?");
$bkStmt->execute([$bookingSic]);
$bookingRow = $bkStmt->fetch(PDO::FETCH_ASSOC);

assertCheck("Record inserito correttamente nel DB", !empty($bookingRow));
assertCheck("Nome corretto salvato", ($bookingRow['first_name'] ?? '') === $testNome);
assertCheck("Cognome corretto salvato", ($bookingRow['last_name'] ?? '') === $testCognome);
assertCheck("Email corretta salvata", ($bookingRow['email'] ?? '') === $testEmail);
assertCheck("Quota registrata a 10.00 EUR", (float)($bookingRow['total_amount'] ?? 0) === 10.00);

// 4. Test Registrazione Pagamento USDT Polygon
echo "\n4. Test Registrazione Notifica Pagamento USDT:\n";
$mockTxHash = '0x' . bin2hex(random_bytes(32));
$_POST = [
    'action' => 'confirm_usdt_payment',
    'booking_sic' => $bookingSic,
    'tx_hash' => $mockTxHash
];

ob_start();
require __DIR__ . '/../api-event-booking.php';
$outputUsdt = ob_get_clean();
$resUsdt = json_decode($outputUsdt, true);

assertCheck("Conferma USDT risponde success = true", !empty($resUsdt['success']));
$bkStmt->execute([$bookingSic]);
$updatedRow = $bkStmt->fetch(PDO::FETCH_ASSOC);
assertCheck("payment_method aggiornato a USDT", ($updatedRow['payment_method'] ?? '') === 'USDT');
assertCheck("payment_tx_id contiene la TX Hash Polygon", ($updatedRow['payment_tx_id'] ?? '') === $mockTxHash);
assertCheck("payment_status aggiornato a PAID_PENDING_CONFIRMATION", ($updatedRow['payment_status'] ?? '') === 'PAID_PENDING_CONFIRMATION');

// 5. Test Griglia 28 Sponsor
echo "\n5. Verifica Griglia Sponsor (28 Business):\n";
ob_start();
require __DIR__ . '/../templates/_sponsor_grid.php';
$gridHtml = ob_get_clean();

$sponsorCount = count($sponsors);
assertCheck("Griglia sponsor contiene esattamente 28 business (trovati: {$sponsorCount})", $sponsorCount === 28);
assertCheck("Contiene sicurissimo.online", str_contains($gridHtml, 'sicurissimo.online'));
assertCheck("Contiene betterway.agency", str_contains($gridHtml, 'betterway.agency'));
assertCheck("Contiene neuralog.pro", str_contains($gridHtml, 'neuralog.pro'));
assertCheck("Contiene mywallet.business", str_contains($gridHtml, 'mywallet.business'));
assertCheck("Contiene destinorandagio.it", str_contains($gridHtml, 'destinorandagio.it'));
assertCheck("Contiene mircopregnolato.it", str_contains($gridHtml, 'mircopregnolato.it'));
assertCheck("Contiene Amazon KDP Factory", str_contains($gridHtml, 'Amazon KDP Factory'));
assertCheck("Contiene YouTube Automation", str_contains($gridHtml, 'YouTube Automation'));

$svgDir = __DIR__ . '/../assets/img/sponsors';
$existingSvgs = 0;
foreach ($sponsors as $sp) {
    $expectedPath = __DIR__ . '/../' . ($sp['img'] ?? '');
    if (file_exists($expectedPath) && filesize($expectedPath) > 500) {
        $existingSvgs++;
    }
}
assertCheck("Tutte le 28 immagini Ultra-HD 8K dei business esistono su disco (trovate: {$existingSvgs}/28)", $existingSvgs === 28);

// 6. Test Bonifica Terminologica Rigorosa
echo "\n6. Verifica Conformita' Governance (Bonifica Terminologica):\n";
$filesToCheck = [
    __DIR__ . '/../templates/_sponsor_grid.php',
    __DIR__ . '/../event-detail.php',
    __DIR__ . '/../events-public.php',
    __DIR__ . '/../events.php',
    __DIR__ . '/../evento-ottobre-taglio-di-po.php',
    __DIR__ . '/../api-event-booking.php'
];

$bannedPatterns = ['/magic/i', '/magico/i', '/giorgian\s*putanu/i', '/81plus/i'];
$violations = 0;

foreach ($filesToCheck as $f) {
    $content = file_get_contents($f);
    foreach ($bannedPatterns as $pat) {
        if (preg_match($pat, $content)) {
            echo "  [VIOLATION] Pattern {$pat} trovato in " . basename($f) . "\n";
            $violations++;
        }
    }
}
assertCheck("Zero violazioni di parole proibite o stringhe vietate", $violations === 0);

// Cleanup test row
$pdo->prepare("DELETE FROM event_bookings WHERE sic_id = ?")->execute([$bookingSic]);

echo "\n=== RIEPILOGO TEST: {$passed} PASS, {$failed} FAIL ===\n";
if ($failed > 0) {
    exit(1);
}
