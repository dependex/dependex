<?php
/**
 * DEPENDEX & OLTRE — LEAD CAPTURE & NEWSLETTER API
 * Endpoint per l'acquisizione di lead consensuali dal sito e avvio automatico del welcome flow.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

$email = trim(strtolower($_POST['email'] ?? ($_GET['email'] ?? '')));
$nome = trim((string)($_POST['nome'] ?? ($_GET['nome'] ?? '')));
$source = trim((string)($_POST['source'] ?? 'website_lead_magnet'));
$privacy = !empty($_POST['privacy_accepted']) || !empty($_GET['privacy_accepted']) || isset($_POST['email']);

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Inserisci un indirizzo email valido.']);
        exit;
    }
    header('Location: index.php?lead_status=invalid_email#lead-form');
    exit;
}

// Traccia l'evento e iscrive il contatto
$enrolled = email_os_track_event('lead_captured', $email, [
    'nome' => $nome,
    'source' => $source,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
]);

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Iscrizione completata con successo! Controlla la tua casella di posta per le risorse riservate.'
    ]);
    exit;
}

header('Location: index.php?lead_status=success#lead-form');
exit;
