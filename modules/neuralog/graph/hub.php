<?php
/* ============================================================================
   COMPANY BRAIN — graph/hub.php
   Gli hub tengono il grafo CONNESSO senza pagare O(n^2): un hub globale
   (radice) e un hub per fonte (filesystem, inbox, api, chat...). Ogni neurone
   rappresentante si aggancia al proprio hub; gli hub si agganciano alla radice.
   Gli hub non sono risposte: il recupero li esclude (rag.exclude_id_prefixes).
============================================================================ */
require_once __DIR__ . '/nodes.php';
require_once __DIR__ . '/links.php';

const BRAIN_HUB_ROOT = 'hub-root';

/** Crea (una volta) l'hub radice. */
function brain_hub_root(): string {
    if (!brain_cfg('graph.hub_global', true)) { return ''; }
    brain_node_ensure([
        'id'         => BRAIN_HUB_ROOT,
        'section'    => 'hub',
        'weight'     => 999,
        'path'       => '',
        'title'      => (string)brain_cfg('brain.label', 'Cervello'),
        'content'    => 'Radice del grafo di conoscenza. Tutti i neuroni convergono qui.',
        'visibility' => 'admin',
        'source'     => 'system',
    ]);
    return BRAIN_HUB_ROOT;
}

/** Hub di una fonte ('filesystem', 'inbox', 'api', 'chat', ...). */
function brain_hub_for(string $source): string {
    if (!brain_cfg('graph.hub_per_source', true)) { return brain_hub_root(); }
    $slug = brain_slug($source, 40);
    $id = 'hub-' . $slug;
    brain_node_ensure([
        'id'         => $id,
        'section'    => 'hub',
        'weight'     => 900,
        'path'       => 'hub/' . $slug,
        'title'      => 'Hub ' . $slug,
        'content'    => 'Hub della fonte "' . $slug . '".',
        'visibility' => 'admin',
        'source'     => 'system',
    ]);
    $root = brain_hub_root();
    if ($root !== '') { brain_link($id, $root, 'hub'); }
    return $id;
}
