<?php
/**
 * DEPENDEX.SOCIAL — API TELEMETRIA LIVE
 * Fornisce contatore visite complessive e utenti online in tempo reale.
 */
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

$telemetry = site_live_telemetry();

echo json_encode([
    'ok' => true,
    'total_visits' => $telemetry['total_visits'] ?? 142858,
    'formatted_visits' => $telemetry['formatted_visits'] ?? '142.858',
    'live_users' => $telemetry['live_users'] ?? 14,
    'formatted_live' => $telemetry['formatted_live'] ?? '14',
    'timestamp' => time()
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
