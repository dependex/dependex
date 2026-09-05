<?php
/* ============================================================================
   COMPANY BRAIN — quality/health.php
   Il sistema immunitario: il cervello si controlla da solo e riferisce.
     - contatori e distribuzioni (il muro pubblico/privato in numeri)
     - ORFANI: neuroni senza nessuna sinapsi
     - PENDENTI: sinapsi verso nodi che non esistono
     - NON CANONICHE / DOPPIONI: coppie ripetute o con i capi invertiti
     - ENTITA' ORFANE: righe che puntano a nodi cancellati
     - SENZA MURO: nodi ingeriti rimasti senza visibilita'
   Con fix=true ripara. Ogni esecuzione lascia uno snapshot in meta: la storia
   della crescita si legge, non si racconta.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/schema.php';
require_once __DIR__ . '/../core/log.php';
require_once __DIR__ . '/../graph/nodes.php';
require_once __DIR__ . '/../graph/entities.php';

function brain_health(bool $fix = false): array {
    $out = ['ok' => true, 'when' => brain_now(), 'schema_version' => (int)brain_meta_get('schema_version', 0),
            'driver' => brain_driver(), 'fixed' => false];
    if (!brain_pdo()) { return ['ok' => false, 'error' => 'nessun database']; }

    $out['counts']       = brain_counts();
    $out['by_visibility'] = brain_group_count('visibility');
    $out['by_source']     = brain_group_count('source');
    $out['by_section']    = brain_group_count('section');

    $N = brain_t('nodes'); $L = brain_t('links'); $NE = brain_t('node_entities');
    $d = [];
    if (brain_has_table($N) && brain_has_table($L)) {
        $d['orphans'] = (int)brain_scalar(
            "SELECT COUNT(*) FROM $N n WHERE n.section <> 'hub' AND NOT EXISTS(SELECT 1 FROM $L l WHERE l.node_a=n.id OR l.node_b=n.id)");
        $d['dangling'] = (int)brain_scalar(
            "SELECT COUNT(*) FROM $L l WHERE l.node_a NOT IN (SELECT id FROM $N) OR l.node_b NOT IN (SELECT id FROM $N)");
        $d['non_canonical'] = (int)brain_scalar("SELECT COUNT(*) FROM $L WHERE node_a > node_b");
        $d['mirrored'] = (int)brain_scalar(
            "SELECT COUNT(*) FROM $L l1 JOIN $L l2 ON l1.node_a=l2.node_b AND l1.node_b=l2.node_a AND l1.node_a<l1.node_b");
        $d['self_links'] = (int)brain_scalar("SELECT COUNT(*) FROM $L WHERE node_a = node_b");
        $d['no_wall'] = (int)brain_scalar("SELECT COUNT(*) FROM $N WHERE visibility IS NULL OR visibility=''");
    }
    if (brain_has_table($NE) && brain_has_table($N)) {
        $d['orphan_entities'] = (int)brain_scalar("SELECT COUNT(*) FROM $NE WHERE node_id NOT IN (SELECT id FROM $N)");
    }
    $out['diagnosis'] = $d;
    $out['activity'] = brain_activity_recent(5);

    if ($fix) {
        $done = [];
        try {
            /* 1) sinapsi con i capi invertiti: se la canonica esiste gia', si
                  butta la copia; altrimenti si scambiano i capi. */
            $done['mirrored_removed'] = max(0, brain_exec(
                "DELETE FROM $L WHERE node_a > node_b AND EXISTS(SELECT 1 FROM (SELECT node_a a, node_b b FROM $L) c WHERE c.a = $L.node_b AND c.b = $L.node_a)"));
            $done['normalized'] = max(0, brain_exec(
                "UPDATE $L SET node_a = node_b, node_b = node_a WHERE node_a > node_b"));
            $done['self_removed'] = max(0, brain_exec("DELETE FROM $L WHERE node_a = node_b"));
            /* 2) doppioni esatti residui */
            $done['duplicates_removed'] = brain_dedup_links();
            /* 3) sinapsi pendenti */
            $done['dangling_removed'] = max(0, brain_exec(
                "DELETE FROM $L WHERE node_a NOT IN (SELECT id FROM $N) OR node_b NOT IN (SELECT id FROM $N)"));
            /* 4) muro sui nodi rimasti scoperti: si chiude, non si apre */
            $done['wall_applied'] = max(0, brain_exec(
                "UPDATE $N SET visibility='admin' WHERE visibility IS NULL OR visibility=''"));
            /* 5) righe entita' orfane */
            $done['orphan_entities_removed'] = brain_entities_drop_for(null);
            /* 6) ora l'indice unico puo' nascere anche su tabelle storiche */
            try {
                $pdo = brain_pdo();
                $sql = 'CREATE UNIQUE INDEX ' . (brain_driver() === 'sqlite' ? 'IF NOT EXISTS ' : '')
                     . 'ux_brain_links ON ' . $L . ' (node_a, node_b)';
                $pdo->exec($sql);
                $done['unique_index'] = 'ok';
            } catch (Throwable $e) { $done['unique_index'] = 'gia presente o non creabile'; }
        } catch (Throwable $e) { $done['error'] = $e->getMessage(); }
        $out['fixed'] = true;
        $out['repairs'] = $done;
        brain_activity('health-fix', json_encode($done, JSON_UNESCAPED_UNICODE));
        $out['diagnosis_after'] = brain_health(false)['diagnosis'] ?? [];
    }

    brain_meta_set('health_last', json_encode(['t' => time()] + $out['counts'], JSON_UNESCAPED_UNICODE));
    return $out;
}

/** Toglie le coppie duplicate mantenendone una (portabile fra i driver). */
function brain_dedup_links(): int {
    $L = brain_t('links');
    $pdo = brain_pdo();
    if (!$pdo) { return 0; }
    if (brain_driver() === 'sqlite') {
        return max(0, brain_exec("DELETE FROM $L WHERE rowid NOT IN (SELECT MIN(rowid) FROM $L GROUP BY node_a, node_b)"));
    }
    return max(0, brain_exec("DELETE l FROM $L l JOIN $L k ON l.node_a=k.node_a AND l.node_b=k.node_b AND l.id > k.id"));
}
