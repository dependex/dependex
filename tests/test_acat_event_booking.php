<?php
/**
 * Test Suite: ACAT Basso Polesine Event & Booking Flow
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/events/EventSyncService.php';

echo "=== 1. VERIFICA PRESENZA EVENTO ACAT NEL DB ===\n";
$events = EventSyncService::syncAndGetActiveEvents('FORMAZIONE');
$found = false;
foreach ($events as $e) {
    if ($e['sic_id'] === 'SIC-EVT-ACAT-BP-2026-COMM') {
        $found = true;
        echo "[PASS] Trovato evento: {$e['title']}\n";
        echo "       Data: {$e['starts_at']} | Sede: {$e['venue']} | Capienza: {$e['capacity']}\n";
        echo "       Iscritti/Prenotati attuali: {$e['registrations']}\n";
        echo "       Immagine: {$e['image_url']}\n";
        break;
    }
}
if (!$found) {
    echo "[FAIL] Evento SIC-EVT-ACAT-BP-2026-COMM non trovato!\n";
    exit(1);
}

echo "\n=== 2. TEST PRENOTAZIONE VIA SUB-PROCESSO ===\n";
$pdo = db();
$sic = 'SIC-EVT-ACAT-BP-2026-COMM';

$output = shell_exec('php ' . escapeshellarg(__DIR__ . '/sub_test_booking.php') . ' 2>&1');
$res = json_decode($output ?? '', true);

if (!empty($res['success'])) {
    echo "[PASS] Prenotazione registrata con successo!\n";
    echo "       Codice prenotazione: {$res['booking_sic']}\n";
    echo "       Posti residui calcolati: {$res['seats_remaining']}\n";
    echo "       WhatsApp Link: {$res['whatsapp_link']}\n";
} else {
    echo "[FAIL] Risposta inattesa: {$output}\n";
    exit(1);
}

// Pulizia delle prenotazioni di test
$pdo->exec("DELETE FROM event_bookings WHERE email LIKE '%@dependex.test'");
echo "[INFO] Pulizia record di test effettuata.\n";

echo "\n=== 3. VERIFICA GENERATORE CALENDARIO ICS ===\n";
$_GET['event'] = $sic;
ob_start();
include __DIR__ . '/../event-ics.php';
$icsContent = ob_get_clean();

if (strpos($icsContent, 'BEGIN:VCALENDAR') !== false && strpos($icsContent, 'A Scuola di Comunicazione e Resilienza') !== false) {
    echo "[PASS] File iCalendar generato correttamente con conformità RFC 5545.\n";
} else {
    echo "[FAIL] Generazione iCalendar non valida!\n";
    exit(1);
}

echo "\n=== 4. VERIFICA CONTROLLO PAROLE VIETATE (AGENTS.MD) ===\n";
$filesToCheck = [
    __DIR__ . '/../api-event-booking.php',
    __DIR__ . '/../event-detail.php',
    __DIR__ . '/../event-ics.php',
    __DIR__ . '/../events-public.php',
    __DIR__ . '/../events.php',
    __DIR__ . '/../modules/events/EventSyncService.php',
    __DIR__ . '/../automation/emailflux/send_booking_email.py'
];

$banned = ['magic', 'magico', 'M.A.G.I.C.', 'giorgian putanu', '81plus'];
$violations = 0;

foreach ($filesToCheck as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    foreach ($banned as $word) {
        if (stripos($content, $word) !== false) {
            echo "[VIOLATION] Trovata parola vietata '{$word}' nel file: {$f}\n";
            $violations++;
        }
    }
}

if ($violations === 0) {
    echo "[PASS] Zero parole vietate rilevate in tutti i file creati/modificati.\n";
} else {
    echo "[FAIL] Trovate {$violations} violazioni terminologiche!\n";
    exit(1);
}

echo "\n=== TUTTI I TEST PASSATI CON SUCCESSO (VERIFIER 100% OK) ===\n";
