<?php
require_once 'bootstrap.php';
$u = current_user();
require_once __DIR__ . '/modules/cortex/cortex_service.php';

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode((string)$raw, true);

$q = trim((string)($data['message'] ?? $data['q'] ?? $_POST['q'] ?? ''));
if ($q === '') {
    echo json_encode(['ok' => false, 'error' => 'Messaggio vuoto']);
    exit;
}

$cortex = new \Dependex\Cortex\CortexService();
$res = $cortex->processMessage($q, $u ? (int)$u['id'] : null);

$text = $res['message'] ?? 'Sono il punto di ascolto e orientamento dei Club Alcologici Territoriali. Come posso esserti d\'aiuto oggi?';
$actions = [];

$qLower = mb_strtolower($q, 'UTF-8');
if (str_contains($qLower, 'club') || str_contains($qLower, 'trova') || str_contains($qLower, 'vicino') || str_contains($qLower, 'mappa')) {
    $actions[] = ['label' => 'Mappa 2D dei Club in Italia', 'url' => 'mappa-club.php', 'type' => 'link'];
    $actions[] = ['label' => 'Trova un Club Territoriale', 'url' => 'world-club-explorer.php', 'type' => 'link'];
}
if (str_contains($qLower, 'guida') || str_contains($qLower, 'famiglia') || str_contains($qLower, 'gratis')) {
    $actions[] = ['label' => 'Scarica la Guida Famiglia (PDF)', 'url' => 'guida-gratuita.php', 'type' => 'link'];
}
if (str_contains($qLower, 'parla') || str_contains($qLower, 'aiuto') || str_contains($qLower, 'contatt')) {
    $actions[] = ['label' => 'Parla con Noi (Ascolto Riservato)', 'url' => 'parla-con-noi.php', 'type' => 'link'];
}

echo json_encode([
    'ok' => !empty($res['success']),
    'answer' => $text,
    'text' => $text,
    'actions' => $actions,
    'role' => 'Accoglienza & Orientamento Ecologico-Sociale',
    'agent' => $res['agent'] ?? 'support',
    'conversation_sic_id' => $data['conversation_sic_id'] ?? ''
]);
