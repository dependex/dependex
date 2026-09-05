<?php
/* ============================================================================
   COMPANY BRAIN — graph/autolink.php
   Le quattro sinapsi automatiche, tutte O(n*k) e mai O(n^2):
     1) SEQUENZIALI  — chunk i <-> chunk i+1 dello stesso documento
     2) A CONCETTO   — rappresentante <-> nodo-concetto kw-*  (i documenti che
                       parlano della stessa cosa si trovano attraverso il
                       concetto, senza confrontarsi tutti con tutti)
     3) DIRETTE      — rappresentante <-> pochi neuroni esistenti che contengono
                       la stessa parola chiave (tetto da config)
     4) HUB          — rappresentante <-> hub della fonte <-> radice
   Piu' le sinapsi per ENTITA' condivise (graph/entities.php).
============================================================================ */
require_once __DIR__ . '/nodes.php';
require_once __DIR__ . '/links.php';
require_once __DIR__ . '/hub.php';
require_once __DIR__ . '/entities.php';
require_once __DIR__ . '/../core/text.php';

/** Nodo-concetto per una parola chiave. */
function brain_concept_node(string $kw): string {
    $kw = brain_slug($kw, 40);
    if ($kw === '') { return ''; }
    $id = 'kw-' . $kw;
    brain_node_ensure([
        'id'         => $id,
        'section'    => 'concept',
        'weight'     => 500,
        'path'       => 'concept/' . $kw,
        'title'      => $kw,
        'content'    => 'Concetto: ' . $kw,
        'visibility' => 'admin',
        'source'     => 'system',
    ]);
    return $id;
}

/**
 * Collega un gruppo di chunk appena creati.
 * $ids  = id dei chunk in ordine; $text = testo completo del documento;
 * $source = fonte logica; $path = percorso di provenienza (per escludersi).
 * Ritorna il numero di sinapsi nuove.
 */
function brain_autolink(array $ids, string $text, string $source = 'manual', string $path = ''): int {
    $ids = array_values(array_filter($ids));
    if (!$ids) { return 0; }
    $s = 0;

    /* 1) sequenziali */
    for ($i = 0, $n = count($ids) - 1; $i < $n; $i++) { $s += brain_link($ids[$i], $ids[$i + 1], 'seq'); }

    $rep = $ids[0];                                  // neurone rappresentante
    $cap = (int)brain_cfg('graph.max_links_per_node', 64);

    /* 4) hub della fonte */
    $hub = brain_hub_for($source);
    if ($hub !== '') { $s += brain_link($rep, $hub, 'hub'); }

    /* 2) + 3) concetti */
    if (brain_cfg('graph.concept_nodes', true)) {
        $perKw = max(0, (int)brain_cfg('graph.direct_links_per_keyword', 3));
        foreach (brain_keywords($text) as $kw) {
            if ($cap > 0 && brain_link_count($rep) >= $cap) { break; }
            $cid = brain_concept_node($kw);
            if ($cid === '') { continue; }
            $s += brain_link($rep, $cid, 'concept');
            if ($perKw <= 0) { continue; }
            /* sinapsi dirette verso pochi neuroni che citano la stessa parola */
            $rows = brain_rows(
                'SELECT id FROM ' . brain_t('nodes') .
                " WHERE section NOT IN ('concept','hub') AND path <> ? AND content LIKE ?" .
                ' ORDER BY weight DESC LIMIT ' . $perKw,
                [$path, '%' . $kw . '%']);
            foreach ($rows as $r) { $s += brain_link($rep, (string)$r['id'], 'kw'); }
        }
    }

    /* entita' condivise, su ogni chunk */
    foreach ($ids as $id) {
        $node = brain_node_get($id);
        if (!$node) { continue; }
        [, $se] = brain_entities_link($id, (string)$node['content']);
        $s += $se;
    }
    return $s;
}
