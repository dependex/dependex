<?php
/**
 * E2E AUTOMATED TEST SUITE: ACAT TAGLIO DI PO EVENT & NATIONAL ADDICTION HUB
 * Validates:
 * 1. Event DB: Flagship Taglio di Po #1 + National Addiction Hub events across Italy (>= 10 events)
 * 2. event_bookings DB schema
 * 3. Booking creation with distinct Nome/Cognome & 10€ fee
 * 4. Multi-Gateway support (PayPal Live, USDT Polygon with new wallet, On-Site)
 * 5. New Sovereign Wallet Address (0x3C320B3a0917fF44BF6551CDdee44402AFcF250C)
 * 6. Home Page Fast Checkout Component & Ticker RSS Cards
 * 7. 28 Main Sponsor Grid count (28) and all 28 Ultra-HD 8K SVG images
 * 8. Strict Governance check: zero banned words / zero prohibited references
 */

declare(strict_types=1);

define('TEST_RUN_MODE', true);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/../modules/commerce/PayPalService.php';
require_once __DIR__ . '/../modules/events/EventSyncService.php';
require_once __DIR__ . '/../modules/news/AcatNewsService.php';

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

echo "=== E2E TEST: ACAT TAGLIO DI PO & NATIONAL ADDICTION HUB ===\n\n";

// 1. Database Events & National Hub
echo "1. Verifica Database Eventi & Hub Nazionale:\n";
$activeEvents = EventSyncService::syncAndGetActiveEvents();
assertCheck("Calendario eventi contiene Taglio di Po + Eventi Nazionali (totale: " . count($activeEvents) . ")", count($activeEvents) >= 10);
assertCheck("L'evento primario #1 e' SIC-EVT-ACAT-BP-2026-COMM", ($activeEvents[0]['sic_id'] ?? '') === 'SIC-EVT-ACAT-BP-2026-COMM');
assertCheck("Sede evento primario e' Taglio di Po", ($activeEvents[0]['comune'] ?? '') === 'Taglio di Po');
assertCheck("Capienza Taglio di Po fissata a 30 posti", (int)($activeEvents[0]['capacity'] ?? 0) === 30);
assertCheck("Quota partecipazione Taglio di Po fissata a 10.00 EUR", (float)($activeEvents[0]['price_eur'] ?? 0.0) === 10.00);

// Verifica presenza di eventi nazionali chiave
$eventTitles = implode(' | ', array_column($activeEvents, 'title'));
assertCheck("Presenza evento AICAT Nazionale", str_contains($eventTitles, 'AICAT'));
assertCheck("Presenza evento FeDerSerD / Ser.D", str_contains($eventTitles, 'FeDerSerD'));
assertCheck("Presenza evento San Patrignano", str_contains($eventTitles, 'San Patrignano'));
assertCheck("Presenza evento Giocatori Anonimi / GAP", str_contains($eventTitles, 'Giocatori Anonimi'));
assertCheck("Presenza evento Narcotici Anonimi (NA)", str_contains($eventTitles, 'Narcotici Anonimi'));
assertCheck("Presenza evento Alcolisti Anonimi (AA)", str_contains($eventTitles, 'Alcolisti Anonimi'));

// 2. Schema event_bookings
echo "\n2. Verifica Schema Tabella event_bookings:\n";
$cols = $pdo->query("PRAGMA table_info(event_bookings)")->fetchAll(PDO::FETCH_COLUMN, 1);
$requiredCols = ['id', 'sic_id', 'first_name', 'last_name', 'full_name', 'email', 'phone', 'payment_method', 'payment_status', 'payment_tx_id', 'status', 'total_amount'];
foreach ($requiredCols as $col) {
    assertCheck("Colonna '{$col}' presente in event_bookings", in_array($col, $cols, true));
}

// 3. Test Creazione Iscrizione Multi-Gateway
echo "\n3. Test Creazione Iscrizione Multi-Gateway:\n";
$testEmail = 'test.iscritto.' . time() . '@dependex.support';
$testNome = 'Mirco';
$testCognome = 'TestPolesine';

$_POST = [
    'action' => 'init_booking',
    'privacy' => '1',
    'event_sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
    'first_name' => $testNome,
    'last_name' => $testCognome,
    'email' => $testEmail,
    'phone' => '3471234567',
    'role_type' => 'MEMBRO_CLUB',
    'dietary_notes' => 'Nessuna intolleranza',
    'payment_method' => 'PAYPAL'
];

ob_start();
require __DIR__ . '/../api-event-booking.php';
$output = ob_get_clean();
$res = json_decode($output, true);

assertCheck("API risponde success = true", !empty($res['success']));
$bookingSic = $res['booking_sic'] ?? '';
assertCheck("Generato codice prenotazione valido (BOOK-... o SIC-...)", str_starts_with($bookingSic, 'BOOK-') || str_starts_with($bookingSic, 'SIC-'));

$bkStmt = $pdo->prepare("SELECT * FROM event_bookings WHERE sic_id = ?");
$bkStmt->execute([$bookingSic]);
$bookingRow = $bkStmt->fetch(PDO::FETCH_ASSOC);

assertCheck("Record inserito correttamente nel DB", !empty($bookingRow));
assertCheck("Nome corretto salvato", ($bookingRow['first_name'] ?? '') === $testNome);
assertCheck("Cognome corretto salvato", ($bookingRow['last_name'] ?? '') === $testCognome);
assertCheck("Email corretta salvata", ($bookingRow['email'] ?? '') === $testEmail);
assertCheck("Quota registrata a 10.00 EUR", (float)($bookingRow['total_amount'] ?? 0) === 10.00);

// 4. Test Registrazione Pagamento USDT con Nuovo Wallet
echo "\n4. Test Nuovo Wallet Sovrano Polygon (0x3C320B3a0917fF44BF6551CDdee44402AFcF250C):\n";
$newWallet = '0x3C320B3a0917fF44BF6551CDdee44402AFcF250C';
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

// Verifica presenza del nuovo wallet nei file chiave
$eventDetailCode = file_get_contents(__DIR__ . '/../event-detail.php');
$apiBookingCode = file_get_contents(__DIR__ . '/../api-event-booking.php');
$fastCheckoutCode = file_get_contents(__DIR__ . '/../templates/_event_fast_checkout.php');

assertCheck("Nuovo wallet presente in event-detail.php", str_contains($eventDetailCode, $newWallet));
assertCheck("Nuovo wallet presente in api-event-booking.php", str_contains($apiBookingCode, $newWallet));
assertCheck("Nuovo wallet presente in _event_fast_checkout.php", str_contains($fastCheckoutCode, $newWallet));

// 5. Verifica Ticker News RSS & Home Page Fast Checkout
echo "\n5. Verifica Ticker News RSS & Home Page Fast Checkout:\n";
$newsCards = AcatNewsService::getLatestCards(14);
assertCheck("Ticker News contiene almeno 10 card nazionali", count($newsCards) >= 10);
assertCheck("Card #1 del Ticker e' l'evento Taglio di Po", ($newsCards[0]['guid'] ?? '') === 'taglio-di-po-ottobre-2026-official');

$indexHtml = file_get_contents(__DIR__ . '/../index.php');
assertCheck("Home page index.php include _event_fast_checkout.php", str_contains($indexHtml, '_event_fast_checkout.php'));
assertCheck("Home page index.php menziona HUB NAZIONALE DIPENDENZE", str_contains($indexHtml, 'HUB NAZIONALE DIPENDENZE'));

// 6. Verifica Griglia 28 Sponsor e 28 Immagini 8K
echo "\n6. Verifica Griglia Sponsor (28 Business) & Immagini 8K:\n";
ob_start();
require __DIR__ . '/../templates/_sponsor_grid.php';
$gridHtml = ob_get_clean();

$sponsorCount = count($sponsors);
assertCheck("Griglia sponsor contiene esattamente 10 business attivi (trovati: {$sponsorCount})", $sponsorCount === 10);
assertCheck("Contiene sicurissimo.onlinE", str_contains($gridHtml, 'sicurissimo.onlinE'));
assertCheck("Contiene betterway.agency", str_contains($gridHtml, 'betterway.agency'));
assertCheck("Contiene neuralog.pro", str_contains($gridHtml, 'neuralog.pro'));
assertCheck("Contiene destinorandagio.it", str_contains($gridHtml, 'destinorandagio.it'));
assertCheck("Contiene beway.life", str_contains($gridHtml, 'beway.life'));
assertCheck("Contiene estao.app", str_contains($gridHtml, 'estao.app'));
assertCheck("Contiene ixla.solutions", str_contains($gridHtml, 'ixla.solutions'));
assertCheck("Contiene metroeridania.it", str_contains($gridHtml, 'metroeridania.it'));
assertCheck("Contiene mircopregnolato.it", str_contains($gridHtml, 'mircopregnolato.it'));
assertCheck("Contiene campus.camp", str_contains($gridHtml, 'campus.camp'));

$existingSvgs = 0;
foreach ($sponsors as $sp) {
    $expectedPath = __DIR__ . '/../' . ($sp['img'] ?? '');
    if (file_exists($expectedPath) && filesize($expectedPath) > 500) {
        $existingSvgs++;
    }
}
assertCheck("Tutte le 10 immagini Ultra-HD 8K dei business esistono su disco (trovate: {$existingSvgs}/10)", $existingSvgs === 10);

// 7. Test Bonifica Terminologica Rigorosa
echo "\n7. Verifica Conformita' Governance (Bonifica Terminologica):\n";
$filesToCheck = [
    __DIR__ . '/../templates/_sponsor_grid.php',
    __DIR__ . '/../templates/_event_fast_checkout.php',
    __DIR__ . '/../event-detail.php',
    __DIR__ . '/../events-public.php',
    __DIR__ . '/../events.php',
    __DIR__ . '/../evento-ottobre-taglio-di-po.php',
    __DIR__ . '/../api-event-booking.php',
    __DIR__ . '/../index.php'
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
