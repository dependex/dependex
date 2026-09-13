<?php
/**
 * DEPENDEX & ACAT — EVENT BOOKING & MULTI-GATEWAY CHECKOUT API
 * Gestisce:
 * 1. Registrazione anagrafica iscritti (Nome, Cognome, Email, Telefono, Ruolo, Dieta)
 * 2. Checkout 10€ via PayPal Live (Smart Buttons / Carte di Credito/Debito)
 * 3. Checkout 10€ via USDT (Polygon Network: 0x3C320B3a0917fF44BF6551CDdee44402AFcF250C)
 * 4. Pagamento all'accoglienza (On-Site)
 * 5. Gestione Overbooking e Lista d'Attesa (limite 30 posti)
 * 6. Invio email transazionali via SMTP SSL e notifica WhatsApp
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/modules/commerce/PayPalService.php';

use Dependex\Commerce\PayPalService;

if (!function_exists('event_api_exit')) {
    function event_api_exit(): void {
        if (!defined('TEST_RUN_MODE')) {
            exit;
        }
    }
}

if (!function_exists('event_set_status')) {
    function event_set_status(int $code): void {
        if (!headers_sent()) {
            http_response_code($code);
        }
    }
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

// Multi-Domain CORS handling
$allowedOrigins = [
    'https://dependex.social',
    'https://mircopregnolato.it',
    'https://oltre.social',
    'http://localhost',
    'http://127.0.0.1',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $allowedOrigins, true) && !headers_sent()) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (!headers_sent()) {
        event_set_status(204);
    }
    event_api_exit(); return;
}

$raw = file_get_contents('php://input');
$input = $raw ? (json_decode($raw, true) ?? []) : $_POST;

$action = $_GET['action'] ?? ($input['action'] ?? 'init_booking');
$pdo = db();
$u = current_user();

if (!function_exists('send_event_email_async')) {
    function send_event_email_async(string $toEmail, string $subject, string $body): void {
        $pyScript = __DIR__ . '/automation/emailflux/send_booking_email.py';
        if (file_exists($pyScript)) {
            $nullDevice = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'NUL' : '/dev/null';
            $descriptors = [
                0 => ['file', $nullDevice, 'r'],
                1 => ['file', $nullDevice, 'w'],
                2 => ['file', $nullDevice, 'w']
            ];
            $cmd = 'python ' . escapeshellarg($pyScript) . ' '
                . escapeshellarg($toEmail) . ' '
                . escapeshellarg($subject) . ' '
                . escapeshellarg($body);
            $p = proc_open($cmd, $descriptors, $pipes);
            if (is_resource($p)) {
                proc_close($p);
            }
        }
    }
}

try {
    switch ($action) {

        // =========================================================================
        // 1. INIZIALIZZAZIONE ISCRIZIONE / PRENOTAZIONE FORM
        // =========================================================================
        case 'init_booking':
        case 'save_booking':
            $eventSic = trim((string)($input['event_sic_id'] ?? 'SIC-EVT-ACAT-BP-2026-COMM'));
            $nome = trim((string)($input['nome'] ?? ($input['first_name'] ?? '')));
            $cognome = trim((string)($input['cognome'] ?? ($input['last_name'] ?? '')));
            $fullName = trim($nome . ' ' . $cognome);
            if (empty($fullName) && !empty($input['full_name'])) {
                $fullName = trim((string)$input['full_name']);
                $parts = explode(' ', $fullName, 2);
                $nome = $parts[0] ?? '';
                $cognome = $parts[1] ?? '';
            }

            $email = strtolower(trim((string)($input['email'] ?? '')));
            $phone = trim((string)($input['phone'] ?? ''));
            $roleType = trim((string)($input['role_type'] ?? 'Operatore / Volontario'));
            $dietaryNotes = trim((string)($input['dietary_notes'] ?? 'Nessuna'));
            $paymentMethod = strtoupper(trim((string)($input['payment_method'] ?? 'ON_SITE')));
            if (!in_array($paymentMethod, ['CARD', 'PAYPAL', 'USDT', 'ON_SITE'], true)) {
                $paymentMethod = 'ON_SITE';
            }
            $notes = trim((string)($input['notes'] ?? ''));
            $numSeats = 1; // 1 partecipante per iscrizione con pranzo
            $privacy = !empty($input['privacy_accepted']) || !empty($input['consent']) || !empty($input['privacy']);

            if (empty($nome) || empty($cognome) || mb_strlen($fullName) < 3) {
                event_set_status(422);
                echo json_encode(['success' => false, 'error' => 'Inserisci Nome e Cognome validi.']);
                event_api_exit(); return;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                event_set_status(422);
                echo json_encode(['success' => false, 'error' => 'Inserisci un indirizzo email valido.']);
                event_api_exit(); return;
            }

            if (empty($phone) || mb_strlen(preg_replace('/\D/', '', $phone)) < 6) {
                event_set_status(422);
                echo json_encode(['success' => false, 'error' => 'Inserisci un numero di telefono WhatsApp valido.']);
                event_api_exit(); return;
            }

            if (!$privacy) {
                event_set_status(422);
                echo json_encode(['success' => false, 'error' => 'È necessario accettare l\'informativa sul trattamento dei dati.']);
                event_api_exit(); return;
            }

            // Verifica evento e capienza
            $evtStmt = $pdo->prepare("SELECT * FROM events WHERE sic_id = ?");
            $evtStmt->execute([$eventSic]);
            $event = $evtStmt->fetch(PDO::FETCH_ASSOC);
            if (!$event) {
                if ($eventSic === 'SIC-EVT-ACAT-BP-2026-COMM' || empty($eventSic)) {
                    ensure_core_schema($pdo);
                    $evtStmt->execute(['SIC-EVT-ACAT-BP-2026-COMM']);
                    $event = $evtStmt->fetch(PDO::FETCH_ASSOC);
                    if (!$event) {
                        $event = [
                            'sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
                            'title' => 'A Scuola di Comunicazione e Resilienza — 1° Livello',
                            'capacity' => 30,
                            'price_eur' => 10.00
                        ];
                    }
                } else {
                    event_set_status(404);
                    echo json_encode(['success' => false, 'error' => 'Evento non trovato nel database.']);
                    event_api_exit(); return;
                }
            }

            $capacity = (int)($event['capacity'] ?? 30);
            $price = (float)($event['price_eur'] ?? 10.00);

            // Conteggio iscritti confermati con auto-riparazione schema
            $currentBooked = 0;
            $waitlistPosition = 0;
            ensure_core_schema($pdo);
            try {
                $countStmt = $pdo->prepare("
                    SELECT (
                        (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = ? AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
                        (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = ? AND eb.status = 'CONFIRMED')
                    ) as total_booked
                ");
                $countStmt->execute([$eventSic, $eventSic]);
                $currentBooked = (int)$countStmt->fetchColumn();
            } catch (Throwable $e) {
                $currentBooked = 0;
            }

            $isWaitlist = ($capacity > 0 && $currentBooked >= $capacity);
            if ($isWaitlist) {
                try {
                    $wlStmt = $pdo->prepare("SELECT COUNT(*) FROM event_bookings WHERE event_sic_id = ? AND status = 'WAITLIST'");
                    $wlStmt->execute([$eventSic]);
                    $waitlistPosition = (int)$wlStmt->fetchColumn() + 1;
                } catch (Throwable $e) {
                    $waitlistPosition = 1;
                }
            }

            $bookingSic = sic_id($isWaitlist ? 'WAIT' : 'BOOK');
            $status = $isWaitlist ? 'WAITLIST' : 'CONFIRMED';
            $paymentStatus = $isWaitlist ? 'WAITLIST' : 'PENDING';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $insStmt = $pdo->prepare("
                INSERT INTO event_bookings (
                    sic_id, event_sic_id, user_sic_id, first_name, last_name, 
                    full_name, email, phone, role_type, dietary_notes, 
                    num_seats, total_amount, payment_method, payment_status, 
                    status, notes, ip_address, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
            ");

            $insStmt->execute([
                $bookingSic,
                $eventSic,
                $u['sic_id'] ?? null,
                $nome,
                $cognome,
                $fullName,
                $email,
                $phone,
                $roleType,
                $dietaryNotes,
                $numSeats,
                $price,
                $paymentMethod,
                $paymentStatus,
                $status,
                $notes . ($isWaitlist ? " [LISTA D'ATTESA #{$waitlistPosition}]" : ''),
                $ip
            ]);

            // Tracciamento evento nel ledger
            email_os_track_event('event_booking_started', $email, [
                'booking_sic' => $bookingSic,
                'event_sic' => $eventSic,
                'full_name' => $fullName,
                'phone' => $phone,
                'payment_method' => $paymentMethod,
                'is_waitlist' => $isWaitlist
            ]);

            // Se pagamento in sede o lista d'attesa, invia subito email
            if ($paymentMethod === 'ON_SITE' || $isWaitlist) {
                if ($isWaitlist) {
                    $subject = "Lista d'Attesa (#{$waitlistPosition}): " . $event['title'];
                    $body = "Ciao {$fullName},\n\nI 30 posti in aula per \"{$event['title']}\" sono al completo e sei in lista d'attesa al posto [{$waitlistPosition}].\n\nCodice Prenotazione: {$bookingSic}\nQualora si liberasse un posto sarai contattato/a prioritariamente.\n\nContatti Segreteria: Grazia Nicosia (347 884 4271)\nACAT Basso Polesine";
                } else {
                    $subject = "Conferma Iscrizione: " . $event['title'];
                    $body = "Gentile {$fullName},\n\nLa tua iscrizione per il corso \"{$event['title']}\" (Taglio di Po, 9-11 Ottobre 2026) è stata registrata con successo!\n\nCodice Iscrizione: {$bookingSic}\nQuota: 10,00 € (pranzo del sabato incluso, saldo al desk d'accoglienza).\n\nSede: Oratorio San Francesco, Taglio di Po (RO)\nFormatore: Adelmo Di Salvatore\n\nReferente: Grazia Nicosia (347 884 4271)\nACAT Basso Polesine";
                }
                send_event_email_async($email, $subject, $body);
            }

            $waText = $isWaitlist
                ? "Ciao Grazia, mi sono registrato in lista d'attesa (#{$waitlistPosition}, Codice: {$bookingSic}) per il corso di Taglio di Po. Nome: {$fullName}, Tel: {$phone}."
                : "Ciao Grazia, ho appena completato l'iscrizione per il corso di Taglio di Po (9-11 Ottobre). Codice: {$bookingSic}, Nome: {$fullName}, Quota: 10€.";

            echo json_encode([
                'success' => true,
                'ok' => true,
                'booking_sic' => $bookingSic,
                'is_waitlist' => $isWaitlist,
                'waitlist_position' => $waitlistPosition,
                'amount' => $price,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'message' => $isWaitlist ? "Sei in Lista d'Attesa (Posizione #{$waitlistPosition})" : "Iscrizione registrata con successo!",
                'whatsapp_link' => "https://wa.me/393478844271?text=" . urlencode($waText)
            ]);
            event_api_exit(); return;

        // =========================================================================
        // 2. CREAZIONE ORDINE PAYPAL LIVE (10.00 EUR)
        // =========================================================================
        case 'create_paypal_order':
            $bookingSic = trim((string)($input['booking_sic'] ?? ''));
            $bStmt = $pdo->prepare("SELECT * FROM event_bookings WHERE sic_id = ?");
            $bStmt->execute([$bookingSic]);
            $booking = $bStmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                event_set_status(404);
                echo json_encode(['success' => false, 'error' => 'Prenotazione non trovata.']);
                event_api_exit(); return;
            }

            $amount = (float)($booking['total_amount'] ?? 10.00);
            if ($amount <= 0) {
                $amount = 10.00;
            }

            $paypalService = new PayPalService();
            $orderData = [
                'order_id' => $booking['id'],
                'order_number' => $booking['sic_id'],
                'total_amount' => $amount,
                'currency' => 'EUR',
                'description' => 'Iscrizione Taglio di Po 9-11 Ottobre - ' . $booking['full_name'],
                'brand_name' => 'ACAT Basso Polesine & DEPENDEX',
                'items' => [
                    [
                        'name' => 'Quota Partecipazione Corso Taglio di Po (Pranzo Inc.)',
                        'quantity' => 1,
                        'unit_price' => $amount
                    ]
                ],
                'return_url' => 'https://dependex.social/event-detail.php?payment_success=1',
                'cancel_url' => 'https://dependex.social/event-detail.php?payment_cancel=1'
            ];

            $order = $paypalService->createOrder($orderData);

            // Aggiorna booking con metodo PAYPAL
            $upd = $pdo->prepare("UPDATE event_bookings SET payment_method = 'PAYPAL', payment_tx_id = ?, updated_at = datetime('now') WHERE sic_id = ?");
            $upd->execute([$order['id'], $bookingSic]);

            echo json_encode([
                'success' => true,
                'orderID' => $order['id']
            ]);
            event_api_exit(); return;

        // =========================================================================
        // 3. CATTURA & CONFERMA ORDINE PAYPAL LIVE
        // =========================================================================
        case 'capture_paypal_order':
            $paypalOrderId = trim((string)($input['orderID'] ?? ''));
            $bookingSic = trim((string)($input['booking_sic'] ?? ''));

            if (!$paypalOrderId || !$bookingSic) {
                event_set_status(422);
                echo json_encode(['success' => false, 'error' => 'Parametri mancanti per la cattura dell\'ordine.']);
                event_api_exit(); return;
            }

            $paypalService = new PayPalService();
            $captureResult = $paypalService->captureOrder($paypalOrderId);

            $status = $captureResult['status'] ?? '';
            if ($status === 'COMPLETED') {
                // Aggiornamento stato pagamento nel DB
                $upd = $pdo->prepare("
                    UPDATE event_bookings 
                    SET payment_status = 'PAID', 
                        payment_tx_id = ?, 
                        status = 'CONFIRMED',
                        updated_at = datetime('now')
                    WHERE sic_id = ?
                ");
                $upd->execute([$paypalOrderId, $bookingSic]);

                // Recupero dati booking per email
                $bStmt = $pdo->prepare("SELECT * FROM event_bookings WHERE sic_id = ?");
                $bStmt->execute([$bookingSic]);
                $bk = $bStmt->fetch(PDO::FETCH_ASSOC);

                if ($bk) {
                    $subject = "Ricevuta Pagamento Quota (10,00 €) — Corso Taglio di Po";
                    $body = "Gentile {$bk['full_name']},\n\nAbbiamo ricevuto con successo il pagamento di 10,00 € con Carta/PayPal per la tua iscrizione al corso \"A Scuola di Comunicazione e Resilienza\" (Taglio di Po, 9-11 Ottobre 2026).\n\n"
                          . "DETTAGLI TRANSAZIONE:\n"
                          . "• Codice Prenotazione: {$bk['sic_id']}\n"
                          . "• ID Transazione PayPal: {$paypalOrderId}\n"
                          . "• Importo Corrisposto: 10,00 € (Pranzo del sabato compreso)\n"
                          . "• Stato: CONFERMATO AL 100%\n\n"
                          . "Sede: Oratorio San Francesco d'Assisi, Vicolo San Francesco 1, Taglio di Po (RO)\n"
                          . "Docente: Dott. Adelmo Di Salvatore\n\n"
                          . "Referente Iscrizioni: Grazia Nicosia (347 884 4271)\n\n"
                          . "Ci vediamo venerdì 9 ottobre alle 14:30 al desk accoglienza!";
                    send_event_email_async($bk['email'], $subject, $body);

                    email_os_track_event('event_payment_completed', $bk['email'], [
                        'booking_sic' => $bk['sic_id'],
                        'amount' => 10.00,
                        'provider' => 'paypal',
                        'tx_id' => $paypalOrderId
                    ]);
                }

                $waText = "Ciao Grazia, ho appena versato la quota di 10€ con Carta/PayPal per il corso di Taglio di Po (Codice: {$bookingSic}, ID PayPal: {$paypalOrderId}).";

                echo json_encode([
                    'success' => true,
                    'status' => 'COMPLETED',
                    'message' => 'Pagamento di 10,00 € confermato con successo! Posto riservato in aula.',
                    'booking_sic' => $bookingSic,
                    'tx_id' => $paypalOrderId,
                    'whatsapp_link' => "https://wa.me/393478844271?text=" . urlencode($waText)
                ]);
            } else {
                event_set_status(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Stato pagamento PayPal non completato: ' . $status,
                    'details' => $captureResult
                ]);
            }
            event_api_exit(); return;

        // =========================================================================
        // 4. CONFERMA PAGAMENTO IN USDT (RETE POLYGON)
        // =========================================================================
        case 'confirm_usdt_payment':
            $bookingSic = trim((string)($input['booking_sic'] ?? ''));
            $txHash = trim((string)($input['tx_hash'] ?? ''));

            if (!$bookingSic || !$txHash) {
                event_set_status(422);
                echo json_encode(['success' => false, 'error' => 'Codice prenotazione e Transaction Hash (TX Hash) obbligatori.']);
                event_api_exit(); return;
            }

            // Normalizza formato hash esadecimale (0x...)
            if (!str_starts_with($txHash, '0x')) {
                $txHash = '0x' . $txHash;
            }

            $bStmt = $pdo->prepare("SELECT * FROM event_bookings WHERE sic_id = ?");
            $bStmt->execute([$bookingSic]);
            $bk = $bStmt->fetch(PDO::FETCH_ASSOC);

            if (!$bk) {
                event_set_status(404);
                echo json_encode(['success' => false, 'error' => 'Prenotazione non trovata.']);
                event_api_exit(); return;
            }

            $upd = $pdo->prepare("
                UPDATE event_bookings 
                SET payment_method = 'USDT',
                    payment_status = 'PAID_PENDING_CONFIRMATION',
                    payment_tx_id = ?,
                    status = 'CONFIRMED',
                    updated_at = datetime('now')
                WHERE sic_id = ?
            ");
            $upd->execute([$txHash, $bookingSic]);

            $subject = "Notifica Pagamento 10 USDT Ricevuto — Corso Taglio di Po";
            $body = "Gentile {$bk['full_name']},\n\nAbbiamo registrato la tua transazione di 10 USDT su rete Polygon per l'evento di Taglio di Po.\n\n"
                  . "• Codice Prenotazione: {$bk['sic_id']}\n"
                  . "• Polygon Tx Hash: {$txHash}\n"
                  . "• Importo: 10 USDT (Polygon)\n"
                  . "• Destinazione: 0x3C320B3a0917fF44BF6551CDdee44402AFcF250C\n\n"
                  . "Il nostro team verificherà le conferme del blocco e il tuo posto è già tenuto da parte!\n\n"
                  . "ACAT Basso Polesine & DEPENDEX";
            send_event_email_async($bk['email'], $subject, $body);

            $waText = "Ciao Grazia, ho registrato il pagamento di 10 USDT su Polygon per il corso di Taglio di Po. Codice Prenotazione: {$bookingSic}, Tx Hash: {$txHash}.";

            echo json_encode([
                'success' => true,
                'message' => 'Transazione USDT registrata! Il tuo posto è confermato.',
                'booking_sic' => $bookingSic,
                'tx_hash' => $txHash,
                'whatsapp_link' => "https://wa.me/393478844271?text=" . urlencode($waText)
            ]);
            event_api_exit(); return;

        default:
            event_set_status(400);
            echo json_encode(['success' => false, 'error' => 'Azione non riconosciuta: ' . $action]);
            event_api_exit(); return;
    }

} catch (Throwable $e) {
    event_set_status(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore server durante l\'operazione: ' . $e->getMessage()
    ]);
    event_api_exit(); return;
}
