<?php
/* ============================================================================
   COMPANY BRAIN — api/v1/graph.php
   Il grafo per la UI (3D e 2D). 'rev' cambia ad ogni attivita': il client fa
   polling leggero con ?stat=1 e ridisegna solo quando serve.
   Senza chiave si vedono solo i nodi 'public' (piu' hub e concetti, che sono
   tessuto connettivo e non contengono conoscenza).
   GET ?n=800 | ?full=1 | ?stat=1
============================================================================ */
require_once __DIR__ . '/_boot.php';
$admin = brain_api_gate('graph');

$rev = brain_rev();
$counts = brain_counts();
$visSql = $admin ? '1=1' : "(visibility='public' OR section IN ('hub','concept'))";

if (brain_param('stat', '') !== '') {
    brain_json(['ok' => true, 'rev' => $rev, 'stats' => [
        'nodes' => $counts['nodes'], 'links' => $counts['links'], 'files' => $counts['files'],
    ]]);
}

$max = (int)brain_cfg('ui.graph_limit', 1200);
$N = brain_param('full', '') !== '' ? $max : min($max, max(20, (int)brain_param('n', 400)));

/* prima hub e concetti (tessuto connettivo), poi i neuroni piu' pesanti */
$nodes = []; $idset = [];
foreach (brain_rows('SELECT id, section, weight, path, title FROM ' . brain_t('nodes')
        . " WHERE section IN ('hub','concept') ORDER BY weight DESC LIMIT " . $N) as $r) {
    $idset[$r['id']] = 1; $nodes[] = $r;
}
$rest = max(0, $N - count($nodes));
if ($rest > 0) {
    foreach (brain_rows('SELECT id, section, weight, path, title FROM ' . brain_t('nodes')
            . " WHERE section NOT IN ('hub','concept') AND " . $visSql
            . ' ORDER BY weight DESC, id DESC LIMIT ' . $rest) as $r) {
        if (isset($idset[$r['id']])) { continue; }
        $idset[$r['id']] = 1; $nodes[] = $r;
    }
}

$outNodes = [];
foreach ($nodes as $r) {
    $sec = (string)($r['section'] ?: 'text');
    if ($sec === 'concept') { $label = '#' . str_replace('concept/', '', (string)$r['path']); }
    elseif ($sec === 'hub')  { $label = strtoupper((string)($r['title'] ?: 'HUB')); }
    else { $label = (string)($r['title'] ?: basename((string)$r['path'])); }
    $outNodes[] = [
        'id' => $r['id'],
        'g'  => (int)$r['weight'],
        's'  => $sec,
        'l'  => brain_cut($label, 60),
        'p'  => $admin ? (string)$r['path'] : basename((string)$r['path']),
    ];
}

$outLinks = [];
if ($idset) {
    $ids = array_keys($idset);
    $seen = [];
    foreach (array_chunk($ids, 300) as $chunk) {
        $ph = implode(',', array_fill(0, count($chunk), '?'));
        foreach (brain_rows('SELECT node_a, node_b FROM ' . brain_t('links') . ' WHERE node_a IN (' . $ph . ')', $chunk) as $l) {
            if (!isset($idset[$l['node_b']])) { continue; }
            $k = $l['node_a'] . '|' . $l['node_b'];
            if (isset($seen[$k])) { continue; }
            $seen[$k] = 1;
            $outLinks[] = ['a' => $l['node_a'], 'b' => $l['node_b']];
            if (count($outLinks) >= 30000) { break 2; }
        }
    }
}

brain_json([
    'ok' => true,
    'rev' => $rev,
    'admin' => $admin,
    'stats' => ['nodes' => $counts['nodes'], 'links' => $counts['links'], 'files' => $counts['files'],
                'shown' => count($outNodes), 'links_shown' => count($outLinks)],
    'colors' => brain_cfg('ui.node_colors', []),
    'nodes' => $outNodes,
    'links' => $outLinks,
    'feed'  => $admin ? brain_activity_recent(12) : [],
]);
