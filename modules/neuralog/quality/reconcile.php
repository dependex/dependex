<?php
/* ============================================================================
   COMPANY BRAIN — quality/reconcile.php
   Riconciliazione: i registri dicono la stessa cosa del grafo?
     - file registrati con N nodi  vs  nodi realmente presenti per quel percorso
     - voci di conoscenza          vs  nodi corrispondenti
     - file spariti dal disco      vs  nodi ancora in memoria
   Non ripara di nascosto: elenca le differenze e, solo se glielo chiedi
   (fix=true), rimette in coda o dimentica cio' che non esiste piu'.
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../ingest/text.php';

function brain_reconcile(bool $fix = false): array {
    $out = ['ok' => true, 'when' => brain_now(), 'fixed' => $fix, 'mismatch' => [], 'missing_on_disk' => [], 'orphan_knowledge' => []];
    if (!brain_pdo() || !brain_has_table(brain_t('files'))) { return ['ok' => false, 'error' => 'schema non installato']; }

    $rows = brain_rows('SELECT path, nodes, source_kind FROM ' . brain_t('files'));
    foreach ($rows as $r) {
        $path = (string)$r['path'];
        $declared = (int)$r['nodes'];
        $real = (int)brain_scalar('SELECT COUNT(*) FROM ' . brain_t('nodes') . ' WHERE path=?', [$path], 0);
        if ($declared !== $real) {
            $out['mismatch'][] = ['path' => $path, 'declared' => $declared, 'real' => $real];
            if ($fix) {
                /* il registro torna coerente: il file rientra in coda al
                   prossimo lotto di ingestione (hash azzerato). */
                brain_exec('UPDATE ' . brain_t('files') . " SET hash='', nodes=?, status='requeued' WHERE path=?", [$real, $path]);
            }
        }
        $abs = brain_path($path);
        if (!is_file($abs)) {
            $out['missing_on_disk'][] = $path;
            if ($fix) { brain_forget_path($path); }
        }
    }
    if (brain_has_table(brain_t('knowledge'))) {
        foreach (brain_rows('SELECT source_path FROM ' . brain_t('knowledge')) as $k) {
            $p = (string)$k['source_path'];
            $n = (int)brain_scalar('SELECT COUNT(*) FROM ' . brain_t('nodes') . ' WHERE path=?', [$p], 0);
            if ($n === 0) {
                $out['orphan_knowledge'][] = $p;
                if ($fix) { brain_exec('DELETE FROM ' . brain_t('knowledge') . ' WHERE source_path=?', [$p]); }
            }
        }
    }
    $out['summary'] = [
        'files' => count($rows),
        'mismatch' => count($out['mismatch']),
        'missing_on_disk' => count($out['missing_on_disk']),
        'orphan_knowledge' => count($out['orphan_knowledge']),
    ];
    brain_activity('reconcile', json_encode($out['summary']));
    return $out;
}
