<?php
/**
 * TEST E2E: EVENTO PORTO TOLLE SAT 2° MODULO (24 OTTOBRE 2026)
 * Verifica:
 * 1. Record Evento in SQLite con tutti i campi del PDF
 * 2. Esistenza e integrità locandina 9:16 (WebP & JPEG)
 * 3. Rendering e conformità Mobile-First di evento-ottobre-porto-tolle.php
 * 4. Aggiornamento events-public.php con Taglio di Po e Porto Tolle
 * 5. Ticker Eventi Nazionale con Taglio di Po (#1) e Porto Tolle (#2)
 * 6. Flusso API Iscrizione Gratuita (status CONFIRMED, quota 0.00)
 * 7. Bonifica Terminologica e Regole di Governance
 */

declare(strict_types=1);

define('TEST_RUN_MODE', true);
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/events/EventSyncService.php';
require_once __DIR__ . '/../modules/news/AcatNewsService.php';

echo "=== TEST E2E: EVENTO PORTO TOLLE SAT 2° MODULO & GOVERNANCE ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(string $label, bool $condition): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$label}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$label}\n";
        $failCount++;
    }
}

// =========================================================================
// 1. SINCRONIZZAZIONE ED ESISTENZA RECORD EVENTO IN SQLITE
// =========================================================================
echo "1. Verifica Sincronizzazione Record Evento in SQLite:\n";
EventSyncService::syncWebEvents();
$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM events WHERE sic_id = 'SIC-EVT-ACAT-BP-2026-SAT2'");
$stmt->execute();
$evt = $stmt->fetch(PDO::FETCH_ASSOC);

assertCheck("Evento Porto Tolle presente in DB", !empty($evt));
assertCheck("Titolo contiene 'S.A.T. di 2° Modulo'", str_contains($evt['title'] ?? '', 'S.A.T. di 2° Modulo'));
assertCheck("Comune è 'Porto Tolle'", ($evt['comune'] ?? '') === 'Porto Tolle');
assertCheck("Sede contiene 'Un ponte per'", str_contains($evt['venue'] ?? '', 'Un ponte per'));
assertCheck("Docente è 'Grazia Nicosia'", str_contains($evt['trainer'] ?? '', 'Grazia Nicosia'));
assertCheck("Data inizio è '2026-10-24 09:00:00'", ($evt['starts_at'] ?? '') === '2026-10-24 09:00:00');
assertCheck("Data fine è '2026-10-24 16:30:00'", ($evt['ends_at'] ?? '') === '2026-10-24 16:30:00');
assertCheck("Quota partecipazione è 0.00 (Gratuito)", (float)($evt['price_eur'] ?? -1) === 0.00);
assertCheck("Capienza aula è 40 posti", (int)($evt['capacity'] ?? 0) === 40);

// =========================================================================
// 2. VERIFICA LOCANDINA 9:16 (WEBP & JPEG)
// =========================================================================
echo "\n2. Verifica File Locandina 9:16:\n";
$webpPath = __DIR__ . '/../assets/img/events/evento-ottobre-porto-tolle.webp';
$jpegPath = __DIR__ . '/../assets/img/events/evento-ottobre-porto-tolle.jpeg';

assertCheck("Locandina WebP presente su disco", file_exists($webpPath) && filesize($webpPath) > 5000);
assertCheck("Locandina JPEG presente su disco", file_exists($jpegPath) && filesize($jpegPath) > 5000);

if (extension_loaded('gd') || function_exists('getimagesize')) {
    $sizes = @getimagesize($webpPath);
    if ($sizes) {
        $w = $sizes[0];
        $h = $sizes[1];
        assertCheck("Rapporto 9:16 verticale (h > w)", $h > $w);
    }
}

// =========================================================================
// 3. VERIFICA RENDERING PAGINA EVENTO-OTTOBRE-PORTO-TOLLE.PHP
// =========================================================================
echo "\n3. Verifica Rendering evento-ottobre-porto-tolle.php:\n";
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'dependex.social';
require __DIR__ . '/../evento-ottobre-porto-tolle.php';
$portoTolleHtml = ob_get_clean();

assertCheck("Pagina restituisce HTML valido", strlen($portoTolleHtml) > 1000);
assertCheck("Include badge A.C.A.T. BASSO POLESINE", str_contains($portoTolleHtml, 'A.C.A.T. BASSO POLESINE'));
assertCheck("Include titolo S.A.T. di 2° Modulo", str_contains($portoTolleHtml, 'S.A.T. di 2° Modulo'));
assertCheck("Include tema 'Coraggio, Gratitudine, Vita'", str_contains($portoTolleHtml, 'Coraggio, Gratitudine, Vita'));
assertCheck("Include relatrice Grazia Nicosia", str_contains($portoTolleHtml, 'Grazia Nicosia'));
assertCheck("Include telefono 347 8844271", str_contains($portoTolleHtml, '347 8844271') || str_contains($portoTolleHtml, '3478844271'));
assertCheck("Include email nicogra1959@gmail.com", str_contains($portoTolleHtml, 'nicogra1959@gmail.com'));
assertCheck("Include sede 'Un ponte per' e Via G. Matteotti 248", str_contains($portoTolleHtml, 'Un ponte per') && str_contains($portoTolleHtml, 'Matteotti'));
assertCheck("Include riferimento locandina WebP", str_contains($portoTolleHtml, 'evento-ottobre-porto-tolle.webp'));
assertCheck("Include form iscrizione online gratuita", str_contains($portoTolleHtml, 'portoTolleForm'));
assertCheck("Include Schema.org EducationEvent JSON-LD", str_contains($portoTolleHtml, 'EducationEvent'));
assertCheck("Include orari 09:00 - 16:30", str_contains($portoTolleHtml, '09:00') && str_contains($portoTolleHtml, '16:30'));

// =========================================================================
// 4. VERIFICA PAGINA PUBBLICA EVENTI (EVENTS-PUBLIC.PHP)
// =========================================================================
echo "\n4. Verifica Aggiornamento events-public.php:\n";
ob_start();
require __DIR__ . '/../events-public.php';
$eventsPublicHtml = ob_get_clean();

assertCheck("events-public.php contiene evento Taglio di Po", str_contains($eventsPublicHtml, 'A Scuola di Comunicazione e Resilienza'));
assertCheck("events-public.php contiene evento Porto Tolle", str_contains($eventsPublicHtml, 'S.A.T. di 2° Modulo'));
assertCheck("Taglio di Po compare prima di Porto Tolle nel DOM", strpos($eventsPublicHtml, 'A Scuola di Comunicazione e Resilienza') < strpos($eventsPublicHtml, 'S.A.T. di 2° Modulo'));
assertCheck("Porto Tolle compare prima dell'Hub Nazionale nel DOM", strpos($eventsPublicHtml, 'S.A.T. di 2° Modulo') < strpos($eventsPublicHtml, 'HUB NAZIONALE DIPENDENZE'));
assertCheck("Include link diretto a evento-ottobre-taglio-di-po.php", str_contains($eventsPublicHtml, 'evento-ottobre-taglio-di-po.php'));
assertCheck("Include link diretto a evento-ottobre-porto-tolle.php", str_contains($eventsPublicHtml, 'evento-ottobre-porto-tolle.php'));

// =========================================================================
// 5. VERIFICA TICKER EVENTI NAZIONALE (CARDS SCORREVOLI)
// =========================================================================
echo "\n5. Verifica Ticker Eventi Nazionale:\n";
$latestCards = AcatNewsService::getLatestCards(8);

assertCheck("Ticker restituisce almeno 6 card", count($latestCards) >= 6);
assertCheck("Card #1 del Ticker è l'evento di Taglio di Po", ($latestCards[0]['guid'] ?? '') === 'taglio-di-po-ottobre-2026-official');
assertCheck("Card #2 del Ticker è l'evento di Porto Tolle", ($latestCards[1]['guid'] ?? '') === 'porto-tolle-ottobre-2026-sat2-official');
assertCheck("Card #2 rimanda a evento-ottobre-porto-tolle.php", ($latestCards[1]['source_url'] ?? '') === 'evento-ottobre-porto-tolle.php');
assertCheck("Card #2 menziona Grazia Nicosia o SAT", str_contains($latestCards[1]['summary'] ?? '', 'Grazia Nicosia') || str_contains($latestCards[1]['title'] ?? '', 'S.A.T.'));

// =========================================================================
// 6. VERIFICA FLUSSO API ISCRIZIONE GRATUITA (BACKEND)
// =========================================================================
echo "\n6. Verifica Flusso API Iscrizione Gratuita:\n";
$testBookingEmail = 'test.sat.portotolle.' . time() . '@dependex.test';
$bookingInput = [
    'event_sic_id' => 'SIC-EVT-ACAT-BP-2026-SAT2',
    'nome' => 'Maria',
    'cognome' => 'Bianchi',
    'email' => $testBookingEmail,
    'phone' => '340 9876543',
    'role_type' => 'Familiare di Club',
    'dietary_notes' => 'Vegetariana',
    'payment_method' => 'FREE',
    'privacy_accepted' => '1'
];

ob_start();
$_POST = $bookingInput;
$_GET['action'] = 'save_booking';
require __DIR__ . '/../api-event-booking.php';
$apiOutput = ob_get_clean();

$apiRes = json_decode($apiOutput, true);
assertCheck("API registrazione gratuita risponde con success: true", !empty($apiRes['success']) && $apiRes['success'] === true);
assertCheck("Booking SIC generato", !empty($apiRes['booking_sic']));
assertCheck("Importo per Porto Tolle è 0.00", (float)($apiRes['amount'] ?? -1) === 0.00);

// Verifica salvataggio nel database
$checkStmt = $pdo->prepare("SELECT * FROM event_bookings WHERE email = ? ORDER BY id DESC LIMIT 1");
$checkStmt->execute([$testBookingEmail]);
$savedBooking = $checkStmt->fetch(PDO::FETCH_ASSOC);

assertCheck("Iscrizione salvata in event_bookings", !empty($savedBooking));
assertCheck("Stato iscrizione è 'CONFIRMED'", ($savedBooking['status'] ?? '') === 'CONFIRMED');
assertCheck("Payment status è 'FREE'", ($savedBooking['payment_status'] ?? '') === 'FREE');
assertCheck("Event SIC associato è 'SIC-EVT-ACAT-BP-2026-SAT2'", ($savedBooking['event_sic_id'] ?? '') === 'SIC-EVT-ACAT-BP-2026-SAT2');

// =========================================================================
// 7. VERIFICA BONIFICA TERMINOLOGICA E GOVERNANCE (RULE STRICT CHECK)
// =========================================================================
echo "\n7. Verifica Bonifica Terminologica & Hard Constraints:\n";
$bannedWords = ['magico', 'magic', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
$filesToCheck = [
    __DIR__ . '/../evento-ottobre-porto-tolle.php',
    __DIR__ . '/../events-public.php',
    __DIR__ . '/../modules/events/EventSyncService.php',
    __DIR__ . '/../modules/news/AcatNewsService.php',
    __DIR__ . '/../templates/_event_fast_checkout.php',
    __DIR__ . '/../api-event-booking.php'
];

$violations = 0;
foreach ($filesToCheck as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    foreach ($bannedWords as $bw) {
        if (stripos($content, $bw) !== false) {
            echo "  [VIOLATION] Trovata parola vietata '{$bw}' in " . basename($f) . "\n";
            $violations++;
        }
    }
}
assertCheck("Zero violazioni terminologiche riscontrate (Banned words count: 0)", $violations === 0);

echo "\n======================================================\n";
echo "ESITO FINALE: {$passCount} PASSATI, {$failCount} FALLITI.\n";
echo "======================================================\n";

if ($failCount > 0) {
    exit(1);
}
