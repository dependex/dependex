<?php
/* ============================================================================
   COMPANY BRAIN — graph/entities.php
   Riconoscimento entita' a DIZIONARIO CURATO (config/dictionary*.json).
   Non e' un NER statistico: non c'e' modello, non c'e' GPU, non c'e' servizio
   esterno. Precisione alta, copertura pari al vocabolario che scrivi. E'
   dichiarato cosi', senza gonfiarlo.
   Due neuroni che condividono almeno N entita' (graph.entity_min_shared)
   vengono collegati da soli.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/text.php';
require_once __DIR__ . '/links.php';

/** Dizionario normalizzato: ['entita' => 'entita canonica'] incluse le alias. */
function brain_entity_dictionary(): array {
    static $dict = null;
    if ($dict !== null) { return $dict; }
    $dict = [];
    $j = brain_json_file((string)brain_cfg('entities.dictionary', 'config/dictionary.sample.json'));
    foreach ((array)($j['entities'] ?? []) as $e) {
        $n = brain_normalize((string)$e);
        if ($n !== '') { $dict[$n] = $n; }
    }
    foreach ((array)($j['aliases'] ?? []) as $canon => $list) {
        $c = brain_normalize((string)$canon);
        if ($c === '') { continue; }
        $dict[$c] = $c;
        foreach ((array)$list as $al) {
            $a = brain_normalize((string)$al);
            if ($a !== '') { $dict[$a] = $c; }
        }
    }
    return $dict;
}

/** Entita' presenti in un testo (canoniche, deduplicate). */
function brain_entities_extract(string $text): array {
    $dict = brain_entity_dictionary();
    if (!$dict) { return []; }
    $t = ' ' . brain_normalize($text) . ' ';
    $found = [];
    foreach ($dict as $needle => $canon) {
        if (mb_strpos($t, $needle) !== false) { $found[$canon] = 1; }
    }
    return array_keys($found);
}

/**
 * Registra le entita' del nodo e lo collega ai neuroni che ne condividono
 * almeno graph.entity_min_shared. Ritorna [n_entita, n_sinapsi_nuove].
 */
function brain_entities_link(string $nodeId, string $text): array {
    $pdo = brain_pdo();
    if (!$pdo || $nodeId === '') { return [0, 0]; }
    $ents = brain_entities_extract($text);
    if (!$ents) { return [0, 0]; }
    $now = brain_now();
    try {
        $del = $pdo->prepare('DELETE FROM ' . brain_t('node_entities') . ' WHERE node_id=?');
        $del->execute([$nodeId]);
        foreach ($ents as $e) {
            brain_insert_ignore($pdo, brain_t('node_entities'), ['node_id' => $nodeId, 'entity' => $e]);
            brain_insert_ignore($pdo, brain_t('entities'), ['name' => $e, 'kind' => 'dict', 'hits' => 0, 'created_at' => $now]);
            brain_exec('UPDATE ' . brain_t('entities') . ' SET hits = COALESCE(hits,0)+1 WHERE name=?', [$e]);
        }
    } catch (Throwable $e) { return [count($ents), 0]; }

    $minShared = max(1, (int)brain_cfg('graph.entity_min_shared', 2));
    $maxLinks  = max(1, (int)brain_cfg('graph.entity_max_links', 5));
    $ph = implode(',', array_fill(0, count($ents), '?'));
    /* EXISTS sui nodi: le righe orfane (nodo cancellato da un reprocess) non
       devono generare sinapsi verso id fantasma che gonfiano la centralita'. */
    $sql = 'SELECT ne.node_id, COUNT(*) c FROM ' . brain_t('node_entities') . ' ne'
         . ' WHERE ne.entity IN (' . $ph . ') AND ne.node_id <> ?'
         . '   AND EXISTS(SELECT 1 FROM ' . brain_t('nodes') . ' n WHERE n.id = ne.node_id)'
         . ' GROUP BY ne.node_id HAVING COUNT(*) >= ' . $minShared
         . ' ORDER BY c DESC LIMIT ' . $maxLinks;
    $s = 0;
    foreach (brain_rows($sql, array_merge($ents, [$nodeId])) as $r) {
        $s += brain_link($nodeId, (string)$r['node_id'], 'entity');
    }
    return [count($ents), $s];
}

/** Toglie le righe entita' di certi nodi (o tutte quelle orfane se $ids=null). */
function brain_entities_drop_for(?array $ids = null): int {
    if (is_array($ids)) {
        if (!$ids) { return 0; }
        $ph = implode(',', array_fill(0, count($ids), '?'));
        return max(0, brain_exec('DELETE FROM ' . brain_t('node_entities') . ' WHERE node_id IN (' . $ph . ')', $ids));
    }
    return max(0, brain_exec('DELETE FROM ' . brain_t('node_entities') .
        ' WHERE node_id NOT IN (SELECT id FROM ' . brain_t('nodes') . ')'));
}
