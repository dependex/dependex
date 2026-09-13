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
    http_response_code(404);
    exit("Evento non trovato.");
}

$dtStart = new DateTime($e['starts_at'], new DateTimeZone('Europe/Rome'));
$dtEnd = !empty($e['ends_at']) ? new DateTime($e['ends_at'], new DateTimeZone('Europe/Rome')) : (clone $dtStart)->modify('+3 hours');

$icsStart = $dtStart->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
$icsEnd = $dtEnd->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
$now = (new DateTime('now', new DateTimeZone('UTC')))->format('Ymd\THis\Z');

$title = preg_replace('/[\r\n]+/', ' ', $e['title']);
$venue = preg_replace('/[\r\n]+/', ' ', $e['venue'] . ' - ' . ($e['address'] ?? 'Taglio di Po'));
$description = preg_replace('/[\r\n]+/', '\n', $e['description'] . '\nQuota: 10€ | Iscrizioni entro 1 Ottobre a Grazia Nicosia: 347 884 4271');

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
