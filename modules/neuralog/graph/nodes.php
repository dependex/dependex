<?php
/* ============================================================================
   COMPANY BRAIN — graph/nodes.php
   I neuroni. Un nodo = un pezzo di conoscenza con: id stabile, percorso di
   provenienza, contenuto, peso (weight), visibilita' e fonte.
   Regola: ogni nodo nasce con la visibilita' che dice la config (default
   'admin'). La promozione a 'public' e' una scelta umana, mai automatica.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/schema.php';
require_once __DIR__ . '/../core/text.php';

/** Crea o aggiorna un nodo. Ritorna l'id. */
function brain_node_put(array $n): string {
    $pdo = brain_pdo();
    if (!$pdo) { return ''; }
    $id = (string)($n['id'] ?? '');
    if ($id === '') { $id = 'n-' . substr(hash('sha256', (string)($n['content'] ?? '') . microtime(true)), 0, 20); }
    $now  = brain_now();
    $data = [
        'id'             => brain_cut($id, 180),
        'section'        => brain_cut((string)($n['section'] ?? 'text'), 60),
        'weight'         => (int)($n['weight'] ?? 1),
        'path'           => brain_cut((string)($n['path'] ?? ''), 380),
        'title'          => brain_cut((string)($n['title'] ?? ''), 240),
        'content'        => (string)($n['content'] ?? ''),
        'visibility'     => brain_cut((string)($n['visibility'] ?? brain_cfg('ingest.default_visibility', 'admin')), 16),
        'source'         => brain_cut((string)($n['source'] ?? 'manual'), 60),
        'hash'           => brain_cut((string)($n['hash'] ?? substr(hash('sha256', (string)($n['content'] ?? '')), 0, 32)), 64),
        'lang'           => brain_cut((string)($n['lang'] ?? brain_cfg('brain.language', 'it')), 8),
        'review_state'   => brain_cut((string)($n['review_state'] ?? ''), 16),
        'created_at'     => $now,
        'updated_at'     => $now,
    ];
    $update = ['section','weight','path','title','content','visibility','source','hash','lang','review_state','updated_at'];
    brain_upsert($pdo, brain_t('nodes'), $data, ['id'], $update);
    return $data['id'];
}

/** Assicura l'esistenza di un nodo senza sovrascriverlo se c'e' gia'. */
function brain_node_ensure(array $n): string {
    $pdo = brain_pdo();
    if (!$pdo) { return ''; }
    $id = (string)($n['id'] ?? '');
    if ($id === '') { return ''; }
    if (brain_scalar('SELECT 1 FROM ' . brain_t('nodes') . ' WHERE id=?', [$id], null)) { return $id; }
    return brain_node_put($n);
}

/** Un nodo per id. */
function brain_node_get(string $id): ?array {
    $r = brain_rows('SELECT * FROM ' . brain_t('nodes') . ' WHERE id=? LIMIT 1', [$id]);
    return $r ? $r[0] : null;
}

/** Gli id dei nodi di un percorso (per il reprocess pulito di un file). */
function brain_node_ids_by_path(string $path): array {
    $rows = brain_rows('SELECT id FROM ' . brain_t('nodes') . ' WHERE path=?', [$path]);
    return array_column($rows, 'id');
}

/** Cancella i nodi di un percorso (e le loro sinapsi/entita'). */
function brain_node_delete_by_path(string $path): int {
    $ids = brain_node_ids_by_path($path);
    if (!$ids) { return 0; }
    brain_links_drop_for($ids);
    brain_entities_drop_for($ids);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $n = brain_exec('DELETE FROM ' . brain_t('nodes') . ' WHERE id IN (' . $ph . ')', $ids);
    return max(0, $n);
}

/** Cambia visibilita' a un nodo (promozione a pubblico: gesto umano). */
function brain_node_set_visibility(string $id, string $vis): bool {
    $vis = in_array($vis, ['public', 'private', 'admin'], true) ? $vis : 'admin';
    return brain_exec('UPDATE ' . brain_t('nodes') . ' SET visibility=?, updated_at=? WHERE id=?', [$vis, brain_now(), $id]) >= 0;
}

/** Conteggi rapidi per la console. */
function brain_counts(): array {
    $c = [];
    foreach (['nodes','links','entities','node_entities','files','knowledge','chat_log','feedback','activity','eval_runs','jobs'] as $t) {
        $c[$t] = brain_has_table(brain_t($t)) ? (int)brain_scalar('SELECT COUNT(*) FROM ' . brain_t($t)) : 0;
    }
    return $c;
}

/** Distribuzione per colonna (visibility, source, section). */
function brain_group_count(string $col): array {
    if (!in_array($col, ['visibility', 'source', 'section', 'review_state'], true)) { return []; }
    $out = [];
    foreach (brain_rows('SELECT COALESCE(NULLIF(' . $col . ", ''), '(vuoto)') g, COUNT(*) c FROM " . brain_t('nodes') . ' GROUP BY 1 ORDER BY 2 DESC') as $r) {
        $out[(string)$r['g']] = (int)$r['c'];
    }
    return $out;
}
