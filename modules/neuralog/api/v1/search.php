<?php
/* ============================================================================
   COMPANY BRAIN — api/v1/search.php
   Ricerca nel cervello. Senza chiave si vedono SOLO i nodi 'public'.
   GET /api/v1/search.php?q=...&n=8
============================================================================ */
require_once __DIR__ . '/_boot.php';
$admin = brain_api_gate('search');

$q = (string)brain_param('q', '');
if ($q === '') { brain_json(['ok' => false, 'error' => 'parametro q obbligatorio'], 400); }
$n = max(1, min(25, (int)brain_param('n', (int)brain_cfg('rag.top_k', 8))));

$t0 = microtime(true);
$rows = brain_retrieve($q, ['admin' => $admin, 'n' => $n]);
$out = [];
foreach ($rows as $r) {
    $out[] = [
        'id'      => $r['id'],
        'title'   => $r['title'] ?: brain_source_label($r),
        'path'    => $admin ? $r['path'] : basename((string)$r['path']),
        'section' => $r['section'],
        'score'   => round((float)$r['score'], 3),
        'snippet' => brain_cut(preg_replace('/\s+/u', ' ', (string)$r['content']) ?? '', 400),
        'via_link' => !empty($r['via_link']),
    ];
}
brain_json([
    'ok' => true, 'query' => $q, 'admin' => $admin, 'count' => count($out),
    'ms' => (int)round((microtime(true) - $t0) * 1000), 'results' => $out,
]);
