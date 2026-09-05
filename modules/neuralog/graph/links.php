<?php
/* ============================================================================
   COMPANY BRAIN — graph/links.php
   Le sinapsi. Sempre NON ORIENTATE e canoniche (node_a < node_b): cosi' la
   stessa coppia non puo' esistere due volte in due versi diversi, e l'indice
   unico basta a garantirlo. Niente self-link.
============================================================================ */
require_once __DIR__ . '/../core/db.php';

/** Crea una sinapsi. Ritorna 1 se nuova, 0 se gia' c'era o non valida. */
function brain_link(string $a, string $b, string $kind = 'auto'): int {
    if ($a === '' || $b === '' || $a === $b) { return 0; }
    if (strcmp($a, $b) > 0) { $t = $a; $a = $b; $b = $t; }
    static $seen = [];
    $k = $a . '|' . $b;
    if (isset($seen[$k])) { return 0; }
    $seen[$k] = 1;
    if (count($seen) > 50000) { $seen = []; }        // la cache non cresce all'infinito
    $pdo = brain_pdo();
    if (!$pdo) { return 0; }
    return brain_insert_ignore($pdo, brain_t('links'), [
        'node_a' => $a, 'node_b' => $b, 'kind' => $kind, 'weight' => 1, 'created_at' => brain_now(),
    ]) > 0 ? 1 : 0;
}

/** Vicini a un salto (nei due versi). */
function brain_neighbors(string $id, int $limit = 8): array {
    $limit = max(1, min(500, $limit));
    $rows = brain_rows(
        'SELECT CASE WHEN node_a=? THEN node_b ELSE node_a END AS v FROM ' . brain_t('links') .
        ' WHERE node_a=? OR node_b=? LIMIT ' . $limit, [$id, $id, $id]);
    return array_column($rows, 'v');
}

/** Grado (numero di sinapsi) per un insieme di nodi. */
function brain_degrees(array $ids): array {
    if (!$ids) { return []; }
    $ids = array_values(array_unique($ids));
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $set = array_flip($ids);
    $deg = [];
    foreach (brain_rows('SELECT node_a, node_b FROM ' . brain_t('links') .
                        ' WHERE node_a IN (' . $ph . ') OR node_b IN (' . $ph . ')',
                        array_merge($ids, $ids)) as $r) {
        $a = (string)$r['node_a']; $b = (string)$r['node_b'];
        if ($a === $b) { if (isset($set[$a])) { $deg[$a] = ($deg[$a] ?? 0) + 1; } continue; }
        if (isset($set[$a])) { $deg[$a] = ($deg[$a] ?? 0) + 1; }
        if (isset($set[$b])) { $deg[$b] = ($deg[$b] ?? 0) + 1; }
    }
    return $deg;
}

/** Toglie tutte le sinapsi di un insieme di nodi (reprocess pulito). */
function brain_links_drop_for(array $ids): int {
    if (!$ids) { return 0; }
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $n = brain_exec('DELETE FROM ' . brain_t('links') . ' WHERE node_a IN (' . $ph . ') OR node_b IN (' . $ph . ')',
                    array_merge($ids, $ids));
    return max(0, $n);
}

/** Quante sinapsi ha gia' questo nodo? (per il tetto max_links_per_node) */
function brain_link_count(string $id): int {
    return (int)brain_scalar('SELECT COUNT(*) FROM ' . brain_t('links') . ' WHERE node_a=? OR node_b=?', [$id, $id], 0);
}
