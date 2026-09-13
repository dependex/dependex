<?php
/**
 * DEPENDEX & ACAT — EVENT BOOKING & REGISTRATION API
 * Gestisce l'iscrizione online per l'evento "A Scuola di Comunicazione e Resilienza"
 * Conforme GDPR, tracking consensi, integrazione Email Marketing OS.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito. Richiesto POST.']);
    exit;
}

$u = current_user();

// Lettura e sanitizzazione input
$eventSic = trim((string)($_POST['event_sic_id'] ?? 'SIC-EVT-ACAT-BP-2026-COMM'));
$nome = trim((string)($_POST['nome'] ?? ''));
$cognome = trim((string)($_POST['cognome'] ?? ''));
$fullName = trim($nome . ' ' . $cognome);
if (empty($fullName) && !empty($_POST['full_name'])) {
    $fullName = trim((string)$_POST['full_name']);
}
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$phone = trim((string)($_POST['phone'] ?? ''));
$roleType = trim((string)($_POST['role_type'] ?? 'Operatore / Volontario'));
$dietaryNotes = trim((string)($_POST['dietary_notes'] ?? 'Nessuna'));
$notes = trim((string)($_POST['notes'] ?? ''));
$numSeats = max(1, min(5, (int)($_POST['num_seats'] ?? 1)));
$privacy = !empty($_POST['privacy_accepted']);

// Validazioni di base
if (empty($fullName) || mb_strlen($fullName) < 3) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Inserisci Nome e Cognome validi.']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Inserisci un indirizzo email valido per la conferma.']);
    exit;
}

if (empty($phone) || mb_strlen(preg_replace('/\D/', '', $phone)) < 6) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Inserisci un recapito telefonico o WhatsApp valido.']);
    exit;
}

if (!$privacy) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'È necessario accettare l\'informativa privacy per procedere con la prenotazione.']);
    exit;
}

$pdo = db();

// Verifica esistenza evento e capienza
$evtStmt = $pdo->prepare("SELECT * FROM events WHERE sic_id = ?");
$evtStmt->execute([$eventSic]);
$event = $evtStmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Evento non trovato.']);
    exit;
}

$capacity = (int)($event['capacity'] ?? 30);
$pricePerSeat = (float)($event['price_eur'] ?? 10.00);

// Calcolo prenotazioni attuali confermate
$countStmt = $pdo->prepare("
    SELECT (
        (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = ? AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
        (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = ? AND eb.status = 'CONFIRMED')
    ) as total_booked
");
$countStmt->execute([$eventSic, $eventSic]);
$currentBooked = (int)$countStmt->fetchColumn();

$isWaitlist = ($capacity > 0 && $currentBooked >= $capacity);
$waitlistPosition = 0;

if ($isWaitlist) {
    $wlStmt = $pdo->prepare("SELECT COUNT(*) FROM event_bookings WHERE event_sic_id = ? AND status = 'WAITLIST'");
    $wlStmt->execute([$eventSic]);
    $waitlistPosition = (int)$wlStmt->fetchColumn() + 1;
}

// Inserimento prenotazione (CONFIRMED o WAITLIST secondo Protocollo Overbooking)
$bookingSic = sic_id($isWaitlist ? 'WAIT' : 'BOOK');
$totalAmount = $pricePerSeat * $numSeats;
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$status = $isWaitlist ? 'WAITLIST' : 'CONFIRMED';

$insBooking = $pdo->prepare("
    INSERT INTO event_bookings (
        sic_id, event_sic_id, user_sic_id, full_name, email, phone, 
        role_type, dietary_notes, num_seats, total_amount, payment_method, 
        status, notes, ip_address
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ON_SITE', ?, ?, ?)
");

$insBooking->execute([
    $bookingSic,
    $eventSic,
    $u['sic_id'] ?? null,
    $fullName,
    $email,
    $phone,
    $roleType,
    $dietaryNotes,
    $numSeats,
    $totalAmount,
    $status,
    $notes . ($isWaitlist ? " [LISTA D'ATTESA POSIZIONE #{$waitlistPosition}]" : ''),
    $ip
]);

// Se l'utente è autenticato su dependex.social, crea anche il record in event_registrations
if ($u && !empty($u['sic_id'])) {
    $regSic = sic_id('EVTREG');
    $insReg = $pdo->prepare("
        INSERT OR IGNORE INTO event_registrations (sic_id, event_sic_id, user_sic_id, status)
        VALUES (?, ?, ?, 'REGISTERED')
    ");
    $insReg->execute([$regSic, $eventSic, $u['sic_id']]);
}

// Tracciamento nel sistema di Email Marketing Automation & GDPR Ledger
email_os_track_event('event_booked', $email, [
    'booking_sic' => $bookingSic,
    'event_sic' => $eventSic,
    'event_title' => $event['title'],
    'full_name' => $fullName,
    'phone' => $phone,
    'num_seats' => $numSeats,
    'total_amount' => $totalAmount,
    'role_type' => $roleType,
    'dietary_notes' => $dietaryNotes,
    'ip' => $ip
]);

// Invio email di notifica/conferma
if ($isWaitlist) {
    $mailSubject = "Lista d'Attesa (N° {$waitlistPosition}): " . $event['title'] . " (ACAT Basso Polesine)";
    $mailBody = "Ciao {$fullName},\n\nGrazie per la tua richiesta. I 30 posti in aula per il corso \"{$event['title']}\" si sono chiusi e sei il numero [{$waitlistPosition}] in lista d'attesa numerata (registrata per data e ora).\n\n"
        . "COSA SUCCEDE ADESSO:\n"
        . "• Se qualcuno rinuncia ti contattiamo subito con priorità (capita quasi sempre!).\n"
        . "• Intanto ti segnaliamo che stiamo già programmando la seconda edizione: hai la precedenza garantita per il posto.\n\n"
        . "RIEPILOGO:\n"
        . "• Corso: {$event['title']}\n"
        . "• Sede: {$event['venue']} - " . ($event['address'] ?? 'Taglio di Po') . "\n"
        . "• Formatore: " . ($event['trainer'] ?? 'Adelmo Di Salvatore') . "\n"
        . "• Codice lista d'attesa: {$bookingSic}\n\n"
        . "CONTATTI SEGRETERIA:\n"
        . "Grazia Nicosia: 347 884 4271\n"
        . "ACAT Basso Polesine: 376 151 6301 - acat.bassop@tiscali.it\n\n"
        . "Segreteria DEPENDEX & ACAT Basso Polesine";
    $feedbackMsg = "I 30 posti per questa edizione sono al completo, ma la tua iscrizione è stata registrata con successo al NUMERO {$waitlistPosition} nella LISTA D'ATTESA NUMERATA! Se si libera un posto o all'apertura della 2ª edizione ti contatteremo con priorità assoluta.";
} else {
    $mailSubject = "Conferma Iscrizione: " . $event['title'] . " (ACAT Basso Polesine)";
    $mailBody = "Gentile {$fullName},\n\nLa tua prenotazione per il corso \"{$event['title']}\" è stata registrata con successo!\n\n"
        . "RIEPILOGO DETTAGLI:\n"
        . "• Organizzatore: " . ($event['organizer'] ?? 'ACAT Basso Polesine O.D.V.') . "\n"
        . "• Date: Venerdì 9, Sabato 10 (pranzo incluso) e Domenica 11 Ottobre 2026\n"
        . "• Sede: {$event['venue']} - " . ($event['address'] ?? 'Taglio di Po') . "\n"
        . "• Formatore: " . ($event['trainer'] ?? 'Adelmo Di Salvatore') . "\n"
        . "• Posti riservati: {$numSeats}\n"
        . "• Quota di partecipazione: " . number_format($totalAmount, 2, ',', '.') . "€ (comprensiva del pranzo di sabato, da versare all'accoglienza)\n"
        . "• Codice prenotazione: {$bookingSic}\n\n"
        . "CONTATTI E COORDINAMENTO:\n"
        . "• Grazia Nicosia (Iscrizioni & Info): 347 884 4271\n"
        . "• Sede ACAT Basso Polesine: 376 151 6301 - acat.bassop@tiscali.it\n\n"
        . "Ti aspettiamo per condividere questo percorso formativo esperienziale!\n\n"
        . "Segreteria DEPENDEX & ACAT Basso Polesine\ninfo@dependex.support";
    $feedbackMsg = "Iscrizione completata con successo per {$fullName}! Riceverai una conferma via email con tutti i dettagli logistici.";
}

// Tentativo invio via Python transport Hostinger SMTP (porta 465 SSL)
$pyScript = __DIR__ . '/automation/emailflux/send_booking_email.py';
if (file_exists($pyScript)) {
    $nullDevice = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'NUL' : '/dev/null';
    $descriptors = [
        0 => ['file', $nullDevice, 'r'],
        1 => ['file', $nullDevice, 'w'],
        2 => ['file', $nullDevice, 'w']
    ];
    $cmd = 'python ' . escapeshellarg($pyScript) . ' '
        . escapeshellarg($email) . ' '
        . escapeshellarg($mailSubject) . ' '
        . escapeshellarg($mailBody);
    $p = proc_open($cmd, $descriptors, $pipes);
    if (is_resource($p)) {
        proc_close($p);
    }
}

// Calcola posti residui aggiornati
$newBooked = $isWaitlist ? $currentBooked : ($currentBooked + $numSeats);
$newRemaining = max(0, $capacity - $newBooked);

$waLeadText = $isWaitlist
    ? "Ciao Grazia, mi sono registrato online in lista d'attesa (Posizione #{$waitlistPosition}, Codice {$bookingSic}) per il corso di Ottobre. Nome: {$fullName}, Tel: {$phone}."
    : "Ciao Grazia, ho appena completato l'iscrizione online per 'A Scuola di Comunicazione e Resilienza'. Codice: {$bookingSic}, Nome: {$fullName}, Tel: {$phone}.";

echo json_encode([
    'success' => true,
    'is_waitlist' => $isWaitlist,
    'waitlist_position' => $waitlistPosition,
    'message' => $feedbackMsg,
    'booking_sic' => $bookingSic,
    'seats_booked' => $numSeats,
    'seats_remaining' => $newRemaining,
    'event_title' => $event['title'],
    'venue' => $event['venue'],
    'whatsapp_link' => "https://wa.me/393478844271?text=" . urlencode($waLeadText)
]);
exit;
