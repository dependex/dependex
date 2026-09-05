<?php
/* ============================================================================
   COMPANY BRAIN — rag/retrieve.php
   Recupero IBRIDO LESSICALE + GRAFO. In chiaro, senza vendere fumo:
     1) espansione multi-query dai sinonimi di config (deterministica)
     2) candidati per LIKE sui termini, con muro di visibilita' applicato in SQL
     3) punteggio in PHP: frequenza * IDF-lite (calcolata sul pool, zero query
        in piu') + bonus se il termine sta nel percorso + peso del nodo, capato
     4) espansione a 1 salto sulle SINAPSI dei migliori (GraphRAG povero ma
        vero): la conoscenza collegata che il lessicale da solo non trova
     5) rerank a piu' segnali (rag/rerank.php)
     6) diversita' di fonte: al massimo N chunk per percorso
   Nessun embedding, nessun servizio esterno, nessuna GPU. E' un motore
   lessicale con grafo: e' dichiarato cosi'.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/text.php';
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../graph/links.php';
require_once __DIR__ . '/rerank.php';

/** Frammento SQL del muro di visibilita'. */
function brain_visibility_sql(bool $admin): string {
    return $admin ? '1=1' : "(visibility = 'public')";      // fail-closed per il pubblico
}

/** Frammento SQL che esclude hub e concetti dal recupero. */
function brain_exclude_sql(): string {
    $parts = [];
    foreach ((array)brain_cfg('rag.exclude_id_prefixes', []) as $p) {
        $p = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$p);
        if ($p !== '') { $parts[] = "id NOT LIKE '" . $p . "%'"; }
    }
    $parts[] = "section NOT IN ('hub','concept')";
    return $parts ? ' AND ' . implode(' AND ', $parts) : '';
}

/**
 * @param string $query
 * @param array  $opts admin(bool), n(int), neighbors(bool)
 * @return array righe con id, path, title, content, weight, visibility, score
 */
function brain_retrieve(string $query, array $opts = []): array {
    $pdo = brain_pdo();
    if (!$pdo || !brain_has_table(brain_t('nodes'))) { return []; }
    $admin = !empty($opts['admin']);
    $n     = (int)($opts['n'] ?? brain_cfg('rag.top_k', 8));
    $n     = max(1, min(50, $n));
    $limit = max($n * 4, (int)brain_cfg('rag.candidate_limit', 150));

    $tmap  = brain_query_terms_map($query);
    $terms = array_keys($tmap);
    $vis   = brain_visibility_sql($admin);
    $excl  = brain_exclude_sql();
    $cols  = 'id, path, title, content, COALESCE(weight,1) AS weight, visibility, source, section, COALESCE(feedback_score,0) AS feedback_score, updated_at';
    $rows  = [];

    if ($terms) {
        $like = []; $args = [];
        foreach ($terms as $t) { $like[] = 'content LIKE ?'; $args[] = '%' . $t . '%'; }
        foreach ($terms as $t) { $like[] = 'path LIKE ?';    $args[] = '%' . $t . '%'; }
        $sql = 'SELECT ' . $cols . ' FROM ' . brain_t('nodes')
             . ' WHERE ' . $vis . $excl . ' AND (' . implode(' OR ', $like) . ')'
             . ' LIMIT ' . $limit;
        $rows = brain_rows($sql, $args);
    }
    if (!$rows) {
        $rows = brain_rows('SELECT ' . $cols . ' FROM ' . brain_t('nodes')
              . ' WHERE ' . $vis . $excl . ' ORDER BY weight DESC LIMIT ' . $n);
    }

    /* muro anti-fuga: vale anche sui nodi gia' pubblici. Doppia rete: se un
       segreto finisse per errore umano in un nodo promosso, qui si ferma. */
    if (!$admin && $rows) {
        $rows = array_values(array_filter($rows, static function ($r) {
            return !brain_looks_secret((string)($r['path'] ?? '') . ' ' . (string)($r['content'] ?? ''));
        }));
    }
    if (!$rows) { return []; }

    brain_score_rows($rows, $terms, $tmap);
    usort($rows, static function ($a, $b) { return $b['score'] <=> $a['score']; });

    /* espansione sulle sinapsi dei migliori */
    if (($opts['neighbors'] ?? brain_cfg('rag.neighbors', true)) && brain_has_table(brain_t('links'))) {
        $rows = brain_expand_neighbors($rows, $admin, $cols, $vis, $excl);
        if (!$admin) {
            $rows = array_values(array_filter($rows, static function ($r) {
                return !brain_looks_secret((string)($r['path'] ?? '') . ' ' . (string)($r['content'] ?? ''));
            }));
        }
    }

    brain_rerank($rows, $query);

    /* diversita': non piu' di N chunk dello stesso documento */
    $maxPer = max(1, (int)brain_cfg('rag.max_per_path', 2));
    $out = []; $per = [];
    foreach ($rows as $r) {
        $k = (string)($r['path'] ?? $r['id']);
        if (($per[$k] ?? 0) >= $maxPer) { continue; }
        $per[$k] = ($per[$k] ?? 0) + 1;
        $out[] = $r;
        if (count($out) >= $n) { break; }
    }
    return $out;
}

/** Punteggio lessicale con IDF-lite calcolata sul pool recuperato. */
function brain_score_rows(array &$rows, array $terms, array $weights = []): void {
    $N = max(1, count($rows));
    $idf = [];
    foreach ($terms as $t) {
        $c = 0;
        foreach ($rows as $r) {
            if (mb_stripos((string)($r['path'] ?? '') . ' ' . (string)($r['content'] ?? ''), $t) !== false) { $c++; }
        }
        $idf[$t] = log(1 + $N / (1 + $c));
    }
    $wCap  = (float)brain_cfg('rag.weight_cap', 10);
    $wFac  = (float)brain_cfg('rag.weight_factor', 0.25);
    $pBon  = (float)brain_cfg('rag.path_bonus', 3.0);
    $sCap  = (float)brain_cfg('rag.synonym_cap_ratio', 0.5);
    $sFloor= (float)brain_cfg('rag.synonym_floor', 1.0);
    foreach ($rows as &$r) {
        $path = brain_normalize((string)($r['path'] ?? '') . ' ' . (string)($r['title'] ?? ''));
        $txt  = $path . ' ' . brain_normalize((string)($r['content'] ?? ''));
        $seed = 0.0; $expa = 0.0;
        foreach ($terms as $t) {
            $tw  = (float)($weights[$t] ?? 1.0);
            $w   = ($idf[$t] ?? 1.0) * $tw;
            $add = 0.0;
            $c = substr_count($txt, $t);
            if ($c > 0) { $add += $c * $w; }
            if ($path !== '' && mb_strpos($path, $t) !== false) { $add += $pBon * $w; }
            if ($add === 0.0) { continue; }
            if ($tw >= 1.0) { $seed += $add; } else { $expa += $add; }
        }
        /* I SINONIMI ALLARGANO IL RECUPERO, NON DECIDONO LA CLASSIFICA: il loro
           contributo e' limitato a una frazione di quello delle parole scritte
           davvero dall'utente (piu' un minimo, per non buttare fuori i
           documenti che si trovano SOLO per sinonimo). Senza questo tetto, una
           voce di dizionario un po' larga bastava a far vincere il documento
           sbagliato. */
        $r['score'] = $seed + min($expa, $sCap * $seed + $sFloor)
                    + min((float)($r['weight'] ?? 1), $wCap) * $wFac;
    }
    unset($r);
}

/** Vicini a un salto dei primi risultati, con punteggio ereditato ridotto. */
function brain_expand_neighbors(array $rows, bool $admin, string $cols, string $vis, string $excl): array {
    $seeds = max(1, (int)brain_cfg('rag.neighbor_seeds', 3));
    $per   = max(1, (int)brain_cfg('rag.neighbors_per_seed', 6));
    $decay = (float)brain_cfg('rag.neighbor_decay', 0.5);
    $have  = array_flip(array_column($rows, 'id'));
    $cand  = [];
    foreach (array_slice($rows, 0, $seeds) as $t) {
        foreach (brain_neighbors((string)$t['id'], $per) as $vid) {
            if (isset($have[$vid]) || isset($cand[$vid])) { continue; }
            $cand[$vid] = (float)($t['score'] ?? 0);
        }
    }
    if (!$cand) { return $rows; }
    $ids = array_keys($cand);
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    foreach (brain_rows('SELECT ' . $cols . ' FROM ' . brain_t('nodes')
             . ' WHERE ' . $vis . $excl . ' AND id IN (' . $ph . ')', $ids) as $r) {
        $r['score'] = $cand[$r['id']] * $decay;
        $r['via_link'] = 1;
        $rows[] = $r;
    }
    return $rows;
}
