<?php
/* ============================================================================
   COMPANY BRAIN — api/v1/health.php
   Diagnosi del grafo. SOLO admin. La riparazione (?fix=1) richiede POST o
   chiave esplicita: un link aperto per sbaglio non deve far scattare un fix.
============================================================================ */
require_once __DIR__ . '/_boot.php';
brain_api_gate('health', true);

$fix = (string)brain_param('fix', '') === '1'
    && (PHP_SAPI === 'cli' || isset($_REQUEST['key']) || ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST');

brain_json(brain_health($fix));
