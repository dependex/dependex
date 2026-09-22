<?php
/**
 * DEPENDEX.SOCIAL — PUBLIC METRICS API
 * Single Source of Truth per tutte le statistiche e indicatori pubblici di rete.
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/clubs/ClubMetricsService.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Cache-Control: public, max-age=3600');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $metrics = \Dependex\Clubs\ClubMetricsService::getPublicMetrics(db());
    echo json_encode(['ok' => true, 'data' => $metrics], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
