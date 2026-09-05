<?php
/* ============================================================================
   COMPANY BRAIN — ingest/files.php
   Il camminatore del filesystem. Sola lettura, sempre. Regole:
     - include/exclude a glob presi dalla config (nessun percorso nel codice)
     - GUARDIA SEGRETI: .env, *secret*, *.pem, chiavi, database... non vengono
       mai aperti, nemmeno per contarli
     - tetto di dimensione per file
     - idempotenza per hash (size|mtime): si ridigerisce solo cio' che cambia
     - lotti con ripresa: si chiama finche' 'remaining' e' 0, cosi' non si
       muore sul time limit di un hosting condiviso
============================================================================ */
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/../core/log.php';
require_once __DIR__ . '/documents.php';
require_once __DIR__ . '/text.php';

/** Il percorso relativo passa i filtri di config? */
function brain_ingest_accepts(string $rel, string $base): bool {
    $rel = str_replace('\\', '/', $rel);
    if (brain_is_secret_path($rel)) { return false; }
    foreach ((array)brain_cfg('ingest.exclude', []) as $g) {
        if (fnmatch((string)$g, $rel, FNM_CASEFOLD) || fnmatch((string)$g, '/' . $rel, FNM_CASEFOLD)) { return false; }
    }
    $inc = (array)brain_cfg('ingest.include', []);
    if (!$inc) { return true; }
    $name = basename($rel);
    foreach ($inc as $g) {
        if (fnmatch((string)$g, $name, FNM_CASEFOLD) || fnmatch((string)$g, $rel, FNM_CASEFOLD)) { return true; }
    }
    return false;
}

/** Elenca i candidati sotto una radice. */
function brain_ingest_scan(string $root): array {
    $root = realpath($root) ?: $root;
    if (!is_dir($root)) { return []; }
    $maxDoc  = (int)brain_cfg('ingest.max_doc_bytes', 6000000);
    $maxFile = (int)brain_cfg('ingest.max_file_bytes', 800000);
    $heavy   = ['pdf', 'docx', 'xlsx', 'pptx'];
    $out = [];
    try {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD);
        foreach ($it as $f) {
            if (!$f->isFile()) { continue; }
            $full = $f->getPathname();
            $rel  = ltrim(str_replace('\\', '/', substr($full, strlen($root))), '/');
            if ($rel === '' || !brain_ingest_accepts($rel, $root)) { continue; }
            $ext  = strtolower($f->getExtension());
            $cap  = in_array($ext, $heavy, true) ? $maxDoc : $maxFile;
            $size = (int)$f->getSize();
            if ($size <= 0 || $size > $cap) { continue; }
            $out[$rel] = ['full' => $full, 'rel' => $rel, 'ext' => $ext, 'size' => $size, 'mtime' => (int)$f->getMTime()];
        }
    } catch (Throwable $e) { brain_log('warn', 'scan parziale: ' . $e->getMessage()); }
    return $out;
}

/** Scrive il .md di conoscenza (traccia leggibile da un umano). */
function brain_knowledge_write(string $rel, string $title, string $text, string $ext): string {
    if (!brain_cfg('ingest.write_knowledge_md', true)) { return ''; }
    $dir = brain_path((string)brain_cfg('paths.knowledge_dir', 'data/knowledge'));
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $slug = brain_slug($rel, 70) . '-' . substr(md5($rel), 0, 6);
    $md  = '# ' . $title . "\n\n";
    $md .= '- Fonte: `' . $rel . "`\n- Tipo: " . $ext . "\n- Caratteri: " . mb_strlen($text) . "\n- Digerito: " . brain_now() . "\n\n";
    $md .= "## Testo estratto\n\n" . brain_cut($text, 60000) . "\n";
    $file = $dir . '/' . $slug . '.md';
    @file_put_contents($file, $md);
    return 'knowledge/' . $slug . '.md';
}

/**
 * Un lotto di ingestione.
 * @param array $opts roots[], batch, dry(bool), source, visibility, full(bool)
 */
function brain_ingest_run(array $opts = []): array {
    $roots = $opts['roots'] ?? (array)brain_cfg('ingest.roots', []);
    $roots = array_values(array_filter(array_map('strval', (array)$roots)));
    $batch = (int)($opts['batch'] ?? brain_cfg('ingest.batch', 20));
    $dry   = !empty($opts['dry']);
    $src   = (string)($opts['source'] ?? 'filesystem');
    $vis   = (string)($opts['visibility'] ?? brain_cfg('ingest.default_visibility', 'admin'));

    $res = ['ok' => true, 'roots' => [], 'scanned' => 0, 'queued' => 0, 'processed' => 0,
            'nodes' => 0, 'links' => 0, 'remaining' => 0, 'dry' => $dry, 'files' => []];

    $cands = [];
    foreach ($roots as $r) {
        $abs = brain_path($r);
        $res['roots'][] = $abs;
        foreach (brain_ingest_scan($abs) as $rel => $m) {
            $m['rel'] = $r . '/' . $rel;
            $cands[$m['rel']] = $m;
        }
    }
    $res['scanned'] = count($cands);

    /* registro: si lavora solo su nuovi o modificati */
    $reg = [];
    if (brain_has_table(brain_t('files'))) {
        foreach (brain_rows('SELECT path, hash FROM ' . brain_t('files')) as $r) { $reg[(string)$r['path']] = (string)$r['hash']; }
    }
    $todo = [];
    foreach ($cands as $rel => $m) {
        $h = md5($m['size'] . '|' . $m['mtime']);
        if (!isset($reg[$rel]) || $reg[$rel] !== $h) { $m['hash'] = $h; $todo[$rel] = $m; }
    }
    $res['queued'] = count($todo);
    if ($batch > 0) { $todo = array_slice($todo, 0, $batch, true); }

    if ($dry) {
        $res['files'] = array_keys($todo);
        $res['remaining'] = max(0, $res['queued'] - count($todo));
        return $res;
    }

    $pdo = brain_pdo();
    foreach ($todo as $rel => $m) {
        $text = '';
        try { $text = brain_extract_text($m['full'], $m['ext']); } catch (Throwable $e) { $text = ''; }
        $nodes = 0;
        if (trim($text) !== '') {
            if (brain_looks_secret($text) && brain_cfg('ingest.skip_secret_content', true)) {
                brain_log('warn', 'saltato per contenuto sensibile: ' . $rel);
            } else {
                $title = basename($rel);
                $md = brain_knowledge_write($rel, $title, $text, $m['ext']);
                $r = brain_ingest_text($text, [
                    'path' => $rel, 'title' => $title, 'source' => $src,
                    'section' => 'document', 'visibility' => $vis, 'id_prefix' => 'doc', 'force' => true,
                ]);
                $nodes = (int)($r['chunks'] ?? 0);
                $res['nodes'] += $nodes;
                $res['links'] += (int)($r['links'] ?? 0);
                if ($pdo && brain_has_table(brain_t('knowledge'))) {
                    brain_upsert($pdo, brain_t('knowledge'), [
                        'source_path' => $rel, 'title' => $title, 'md_path' => $md,
                        'summary' => brain_cut(preg_replace('/\s+/', ' ', $text) ?? '', 400),
                        'chars' => mb_strlen($text), 'source_hash' => $m['hash'],
                        'created_at' => brain_now(), 'updated_at' => brain_now(),
                    ], ['source_path'], ['title', 'md_path', 'summary', 'chars', 'source_hash', 'updated_at']);
                }
            }
        }
        /* anche il file vuoto/illeggibile va registrato: altrimenti resta in
           coda per sempre e blocca il lotto ad ogni giro. */
        if ($pdo && brain_has_table(brain_t('files'))) {
            brain_upsert($pdo, brain_t('files'), [
                'path' => $rel, 'hash' => $m['hash'], 'size' => $m['size'], 'mtime' => $m['mtime'],
                'nodes' => $nodes, 'source_kind' => $src,
                'status' => $nodes > 0 ? 'ok' : 'empty', 'last_processed' => brain_now(),
            ], ['path'], ['hash', 'size', 'mtime', 'nodes', 'source_kind', 'status', 'last_processed']);
        }
        $res['processed']++;
        $res['files'][] = $rel;
    }
    $res['remaining'] = max(0, $res['queued'] - $res['processed']);
    brain_activity('ingest', 'file:' . $res['processed'] . ' nodi:' . $res['nodes'] . ' sinapsi:' . $res['links'] . ' restano:' . $res['remaining']);
    return $res;
}
