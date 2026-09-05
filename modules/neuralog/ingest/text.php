<?php
/* ============================================================================
   COMPANY BRAIN — ingest/text.php
   L'ingresso PROGRAMMATICO: qualunque cosa sia testo puo' diventare
   conoscenza. Una scheda prodotto, un ticket, una riga di CRM, il verbale di
   una riunione, un articolo: si chiama brain_ingest_text() e il cervello se ne
   occupa (chunk, nodi, sinapsi, entita').
   Idempotente: stesso 'path' + stesso contenuto = nessun lavoro rifatto.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/schema.php';
require_once __DIR__ . '/../core/log.php';
require_once __DIR__ . '/../graph/nodes.php';
require_once __DIR__ . '/../graph/autolink.php';
require_once __DIR__ . '/chunker.php';

/**
 * @param string $text  testo grezzo
 * @param array  $opts  path,title,source,section,visibility,weight,id_prefix,
 *                      review_state,lang,autolink(bool),force(bool)
 * @return array ['ok','node_ids','chunks','links','skipped']
 */
function brain_ingest_text(string $text, array $opts = []): array {
    $out = ['ok' => false, 'node_ids' => [], 'chunks' => 0, 'links' => 0, 'skipped' => false];
    if (!brain_pdo()) { $out['error'] = 'nessun database'; return $out; }
    if (!brain_has_table(brain_t('nodes'))) { $out['error'] = 'schema non installato'; return $out; }

    $text = brain_clean_text($text);
    if ($text === '') { $out['error'] = 'testo vuoto'; return $out; }

    $source  = (string)($opts['source'] ?? 'api');
    $path    = (string)($opts['path'] ?? ($source . '/' . substr(hash('sha256', $text), 0, 12)));
    $title   = (string)($opts['title'] ?? basename($path));
    $section = (string)($opts['section'] ?? 'text');
    $vis     = (string)($opts['visibility'] ?? brain_cfg('ingest.default_visibility', 'admin'));
    $prefix  = (string)($opts['id_prefix'] ?? 'n');
    $hash    = substr(hash('sha256', $text), 0, 32);

    /* idempotenza: se il percorso ha gia' nodi con lo stesso hash, non si rifa' */
    if (empty($opts['force'])) {
        $old = brain_scalar('SELECT hash FROM ' . brain_t('nodes') . ' WHERE path=? ORDER BY weight DESC LIMIT 1', [$path], null);
        if ($old !== null && (string)$old === $hash) {
            $out['ok'] = true; $out['skipped'] = true;
            $out['node_ids'] = brain_node_ids_by_path($path);
            return $out;
        }
    }

    brain_node_delete_by_path($path);                 // reprocess pulito

    $chunks = brain_chunk($text);
    if (!$chunks) { $out['error'] = 'nessun chunk'; return $out; }
    $slug = brain_slug($path, 90);
    /* niente id tipo "demo-demo-listino": se il percorso comincia gia' col
       prefisso, non lo si ripete. */
    $base = str_starts_with($slug, $prefix . '-') ? $slug : $prefix . '-' . $slug;
    $ids = [];
    foreach ($chunks as $i => $c) {
        $id = $base . '-' . ($i + 1);
        $ids[] = brain_node_put([
            'id'           => $id,
            'section'      => $section,
            'weight'       => (int)($opts['weight'] ?? (count($chunks) - $i)),
            'path'         => $path,
            'title'        => $title,
            'content'      => brain_cut($c, 8000),
            'visibility'   => $vis,
            'source'       => $source,
            'hash'         => $hash,
            'lang'         => (string)($opts['lang'] ?? brain_cfg('brain.language', 'it')),
            'review_state' => (string)($opts['review_state'] ?? ''),
        ]);
    }
    $links = 0;
    if (($opts['autolink'] ?? true)) { $links = brain_autolink($ids, $text, $source, $path); }

    $out['ok'] = true;
    $out['node_ids'] = $ids;
    $out['chunks'] = count($ids);
    $out['links'] = $links;
    return $out;
}

/** Toglie dal cervello tutto cio' che veniva da un percorso. */
function brain_forget_path(string $path): int {
    $n = brain_node_delete_by_path($path);
    brain_exec('DELETE FROM ' . brain_t('files') . ' WHERE path=?', [$path]);
    brain_exec('DELETE FROM ' . brain_t('knowledge') . ' WHERE source_path=?', [$path]);
    if ($n > 0) { brain_activity('forget', $path . ' (' . $n . ' nodi)'); }
    return $n;
}
