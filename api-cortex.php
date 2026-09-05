<?php
require_once 'bootstrap.php';
$u = require_login();
require_once __DIR__ . '/modules/cortex/cortex_service.php';

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode((string)$raw, true);

$q = trim((string)($data['q'] ?? $_POST['q'] ?? ''));
if ($q === '') {
    echo json_encode(['ok' => false, 'error' => 'Messaggio vuoto']);
    exit;
}

$cortex = new \Dependex\Cortex\CortexService();
$res = $cortex->processMessage($q, (int)($u['id'] ?? 0));

echo json_encode([
    'ok' => !empty($res['success']),
    'answer' => $res['message'] ?? 'Nessuna risposta disponibile.',
    'agent' => $res['agent'] ?? 'support',
    'conversation_sic_id' => $data['conversation_sic_id'] ?? ''
]);
