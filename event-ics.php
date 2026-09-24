<?php
/**
 * DEPENDEX & ACAT — iCalendar (.ics) Generator
 * Genera il file .ics per sincronizzare l'evento nel calendario personale (Google, Apple, Outlook).
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$sic = trim((string)($_GET['event'] ?? 'SIC-EVT-ACAT-BP-2026-COMM'));

$pdo = db();
$st = $pdo->prepare("SELECT * FROM events WHERE sic_id = ?");
$st->execute([$sic]);
$e = $st->fetch(PDO::FETCH_ASSOC);

if (!$e) {
    if ($sic === 'SIC-EVT-ACAT-BP-2026-COMM' || empty($sic)) {
        $e = [
            'sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
            'type' => 'FORMAZIONE',
            'title' => 'A Scuola di Comunicazione e Resilienza — 1° Livello',
            'description' => 'Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri. Corso esperienziale di 3 giornate con Adelmo Di Salvatore per chi vive situazioni di dipendenza in famiglia.',
            'starts_at' => '2026-10-09 14:30:00',
            'ends_at' => '2026-10-11 13:00:00',
            'venue' => "Oratorio San Francesco d'Assisi",
            'comune' => 'Taglio di Po',
            'address' => 'Vicolo San Francesco 1, Taglio di Po (RO)'
        ];
    } else {
        http_response_code(404);
        exit("Evento non trovato.");
    }
}

$dtStart = new DateTime($e['starts_at'], new DateTimeZone('Europe/Rome'));
$dtEnd = !empty($e['ends_at']) ? new DateTime($e['ends_at'], new DateTimeZone('Europe/Rome')) : (clone $dtStart)->modify('+3 hours');

$icsStart = $dtStart->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
$icsEnd = $dtEnd->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
$now = (new DateTime('now', new DateTimeZone('UTC')))->format('Ymd\THis\Z');

$title = preg_replace('/[\r\n]+/', ' ', $e['title']);
$priceText = ((float)($e['price_eur'] ?? 0) > 0) ? ((float)$e['price_eur'] . '€') : 'Iscrizione Gratuita';
$deadlineText = !empty($e['registration_deadline']) ? (' | Iscrizioni entro: ' . date('d/m/Y', strtotime($e['registration_deadline']))) : '';
$description = preg_replace('/[\r\n]+/', '\n', ($e['description'] ?? '') . '\nQuota: ' . $priceText . $deadlineText . ' | ' . ($e['organizer'] ?? 'ACAT Basso Polesine') . ' (info@dependex.support)');

$filename = 'evento-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', strtolower($title)) . '.ics';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//DEPENDEX//ACAT Basso Polesine Calendar//IT\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "METHOD:PUBLISH\r\n";
echo "BEGIN:VEVENT\r\n";
echo "UID:" . md5($e['sic_id']) . "@dependex.social\r\n";
echo "DTSTAMP:" . $now . "\r\n";
echo "DTSTART:" . $icsStart . "\r\n";
echo "DTEND:" . $icsEnd . "\r\n";
echo "SUMMARY:" . $title . "\r\n";
echo "DESCRIPTION:" . $description . "\r\n";
echo "LOCATION:" . $venue . "\r\n";
echo "ORGANIZER;CN=\"ACAT Basso Polesine\":mailto:acat.bassop@tiscali.it\r\n";
echo "STATUS:CONFIRMED\r\n";
echo "END:VEVENT\r\n";
echo "END:VCALENDAR\r\n";
exit;
