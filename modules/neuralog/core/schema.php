<?php
/* ============================================================================
   COMPANY BRAIN — core/schema.php
   Installatore idempotente dello schema. Stesso codice su SQLite e MySQL 8:
   le differenze di tipo (TEXT come chiave, AUTOINCREMENT, lunghezza indici)
   sono risolte qui e in nessun altro punto del modulo.
   La versione dello schema vive in <prefix>meta.schema_version: reinstallare
   e' sempre sicuro, non distrugge nulla.
============================================================================ */
require_once __DIR__ . '/db.php';

const BRAIN_SCHEMA_VERSION = 3;

/** Definizione delle tabelle, per driver. */
function brain_schema_ddl(string $drv): array {
    $sqlite = ($drv === 'sqlite');
    $ID     = $sqlite ? 'TEXT'    : 'VARCHAR(191)';
    $S64    = $sqlite ? 'TEXT'    : 'VARCHAR(64)';
    $S16    = $sqlite ? 'TEXT'    : 'VARCHAR(16)';
    $S32    = $sqlite ? 'TEXT'    : 'VARCHAR(32)';
    $S255   = $sqlite ? 'TEXT'    : 'VARCHAR(255)';
    $PATH   = $sqlite ? 'TEXT'    : 'VARCHAR(400)';
    $TXT    = $sqlite ? 'TEXT'    : 'MEDIUMTEXT';
    $PK     = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'BIGINT AUTO_INCREMENT PRIMARY KEY';
    $tail   = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC';

    $t = function (string $n) { return brain_t($n); };

    return [
        'nodes' => "CREATE TABLE IF NOT EXISTS {$t('nodes')} (
            id $ID NOT NULL PRIMARY KEY,
            section $S64,
            weight INTEGER DEFAULT 1,
            path $PATH,
            title $S255,
            content $TXT,
            visibility $S16 DEFAULT 'admin',
            source $S64,
            hash $S64,
            lang $S16,
            feedback_score INTEGER DEFAULT 0,
            review_state $S16,
            created_at $S32,
            updated_at $S32
        )$tail",

        'links' => "CREATE TABLE IF NOT EXISTS {$t('links')} (
            id $PK,
            node_a $ID NOT NULL,
            node_b $ID NOT NULL,
            kind $S32,
            weight INTEGER DEFAULT 1,
            created_at $S32
        )$tail",

        'entities' => "CREATE TABLE IF NOT EXISTS {$t('entities')} (
            name $ID NOT NULL PRIMARY KEY,
            kind $S64,
            hits INTEGER DEFAULT 0,
            created_at $S32
        )$tail",

        'node_entities' => "CREATE TABLE IF NOT EXISTS {$t('node_entities')} (
            node_id $ID NOT NULL,
            entity $ID NOT NULL,
            PRIMARY KEY (node_id, entity)
        )$tail",

        'files' => "CREATE TABLE IF NOT EXISTS {$t('files')} (
            path $ID NOT NULL PRIMARY KEY,
            hash $S64,
            size INTEGER,
            mtime INTEGER,
            nodes INTEGER DEFAULT 0,
            source_kind $S64,
            status $S32,
            last_processed $S32
        )$tail",

        'knowledge' => "CREATE TABLE IF NOT EXISTS {$t('knowledge')} (
            id $PK,
            source_path $ID NOT NULL UNIQUE,
            title $S255,
            md_path $PATH,
            summary $TXT,
            chars INTEGER,
            source_hash $S64,
            created_at $S32,
            updated_at $S32
        )$tail",

        'chat_log' => "CREATE TABLE IF NOT EXISTS {$t('chat_log')} (
            id $PK,
            question $TXT,
            answer $TXT,
            grounded INTEGER DEFAULT 0,
            source $S64,
            ip_hash $S64,
            created_at $S32
        )$tail",

        'feedback' => "CREATE TABLE IF NOT EXISTS {$t('feedback')} (
            id $PK,
            node_id $ID NOT NULL,
            vote INTEGER NOT NULL,
            question $TXT,
            correction $TXT,
            ip_hash $S64,
            day $S32,
            created_at $S32
        )$tail",

        'eval_runs' => "CREATE TABLE IF NOT EXISTS {$t('eval_runs')} (
            id $PK,
            ran_at $S32,
            questions INTEGER,
            hits INTEGER,
            hit_rate REAL,
            mrr REAL,
            detail $TXT
        )$tail",

        'eval_questions' => "CREATE TABLE IF NOT EXISTS {$t('eval_questions')} (
            id $PK,
            q $TXT,
            expected $TXT,
            tag $S64,
            active INTEGER DEFAULT 1,
            created_at $S32
        )$tail",

        'activity' => "CREATE TABLE IF NOT EXISTS {$t('activity')} (
            id $PK,
            kind $S64,
            detail $TXT,
            created_at $S32
        )$tail",

        'meta' => "CREATE TABLE IF NOT EXISTS {$t('meta')} (
            k $ID NOT NULL PRIMARY KEY,
            v $TXT
        )$tail",

        'jobs' => "CREATE TABLE IF NOT EXISTS {$t('jobs')} (
            id $PK,
            kind $S64,
            payload $TXT,
            state $S32 DEFAULT 'queued',
            attempts INTEGER DEFAULT 0,
            run_after $S32,
            created_at $S32,
            updated_at $S32
        )$tail",
    ];
}

/** Indici, con la lunghezza di prefisso richiesta da MySQL sulle colonne lunghe. */
function brain_schema_indexes(string $drv): array {
    $sqlite = ($drv === 'sqlite');
    $p = function (string $col, int $len) use ($sqlite) { return $sqlite ? $col : ($col . '(' . $len . ')'); };
    $t = function (string $n) { return brain_t($n); };
    return [
        "CREATE UNIQUE INDEX ux_brain_links ON {$t('links')} (node_a, node_b)",
        "CREATE INDEX ix_brain_links_b ON {$t('links')} (node_b)",
        "CREATE INDEX ix_brain_nodes_vis ON {$t('nodes')} (visibility)",
        "CREATE INDEX ix_brain_nodes_section ON {$t('nodes')} (section)",
        "CREATE INDEX ix_brain_nodes_source ON {$t('nodes')} (source)",
        "CREATE INDEX ix_brain_nodes_weight ON {$t('nodes')} (weight)",
        "CREATE INDEX ix_brain_nodes_path ON {$t('nodes')} (" . $p('path', 191) . ")",
        "CREATE INDEX ix_brain_ne_entity ON {$t('node_entities')} (entity)",
        "CREATE UNIQUE INDEX ux_brain_feedback_day ON {$t('feedback')} (node_id, ip_hash, day)",
        "CREATE INDEX ix_brain_activity_created ON {$t('activity')} (created_at)",
        "CREATE INDEX ix_brain_chat_created ON {$t('chat_log')} (created_at)",
        "CREATE INDEX ix_brain_jobs_state ON {$t('jobs')} (state)",
    ];
}

/** Colonne che possono mancare in installazioni vecchie (upgrade additivo). */
function brain_schema_addcols(string $drv): array {
    $sqlite = ($drv === 'sqlite');
    $S16 = $sqlite ? 'TEXT' : 'VARCHAR(16)';
    $S32 = $sqlite ? 'TEXT' : 'VARCHAR(32)';
    $S64 = $sqlite ? 'TEXT' : 'VARCHAR(64)';
    return [
        'nodes' => [
            'visibility'     => $S16,
            'source'         => $S64,
            'hash'           => $S64,
            'lang'           => $S16,
            'feedback_score' => 'INTEGER DEFAULT 0',
            'review_state'   => $S16,
            'created_at'     => $S32,
            'updated_at'     => $S32,
        ],
        'links' => ['kind' => $S32, 'weight' => 'INTEGER DEFAULT 1', 'created_at' => $S32],
    ];
}

/** Installa/aggiorna lo schema. Ritorna il resoconto. */
function brain_schema_install(?PDO $pdo = null): array {
    $pdo = $pdo ?: brain_pdo();
    $out = ['ok' => false, 'tables' => [], 'indexes' => 0, 'added_columns' => [], 'errors' => []];
    if (!$pdo) { $out['errors'][] = 'nessuna connessione al database'; return $out; }
    $drv = brain_driver($pdo);

    foreach (brain_schema_ddl($drv) as $name => $sql) {
        try { $pdo->exec($sql); $out['tables'][] = brain_t($name); }
        catch (Throwable $e) { $out['errors'][] = $name . ': ' . $e->getMessage(); }
    }
    /* colonne mancanti (installazioni precedenti) */
    foreach (brain_schema_addcols($drv) as $tbl => $cols) {
        $existing = brain_table_columns(brain_t($tbl), $pdo);
        foreach ($cols as $col => $type) {
            if (in_array($col, $existing, true)) { continue; }
            try {
                $pdo->exec('ALTER TABLE ' . brain_t($tbl) . ' ADD COLUMN ' . $col . ' ' . $type);
                $out['added_columns'][] = $tbl . '.' . $col;
            } catch (Throwable $e) { /* gia' presente o non aggiungibile: si prosegue */ }
        }
    }
    foreach (brain_schema_indexes($drv) as $sql) {
        try {
            if ($drv === 'sqlite') { $sql = preg_replace('/^CREATE (UNIQUE )?INDEX /', 'CREATE $1INDEX IF NOT EXISTS ', $sql); }
            $pdo->exec($sql);
            $out['indexes']++;
        } catch (Throwable $e) { /* MySQL non ha IF NOT EXISTS sugli indici: duplicato = ok */ }
    }
    brain_meta_set('schema_version', (string)BRAIN_SCHEMA_VERSION);
    brain_meta_set('installed_at', brain_meta_get('installed_at', brain_now()));
    brain_meta_set('module_version', brain_version());
    $out['ok'] = empty($out['errors']);
    $out['schema_version'] = BRAIN_SCHEMA_VERSION;
    return $out;
}

/** Elenco colonne di una tabella (per driver). */
function brain_table_columns(string $table, ?PDO $pdo = null): array {
    $pdo = $pdo ?: brain_pdo();
    if (!$pdo) { return []; }
    $cols = [];
    try {
        if (brain_driver($pdo) === 'sqlite') {
            foreach ($pdo->query('PRAGMA table_info(' . $table . ')') as $c) { $cols[] = (string)$c['name']; }
        } else {
            $st = $pdo->prepare('SELECT column_name FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name=?');
            $st->execute([$table]);
            foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $c) { $cols[] = (string)$c; }
        }
    } catch (Throwable $e) {}
    return $cols;
}

/** meta: lettura/scrittura chiave-valore. */
function brain_meta_get(string $k, $default = null) {
    if (!brain_has_table(brain_t('meta'))) { return $default; }
    $v = brain_scalar('SELECT v FROM ' . brain_t('meta') . ' WHERE k=?', [$k], null);
    return $v === null ? $default : $v;
}
function brain_meta_set(string $k, $v): bool {
    $pdo = brain_pdo();
    if (!$pdo || !brain_has_table(brain_t('meta'))) { return false; }
    return brain_upsert($pdo, brain_t('meta'), ['k' => $k, 'v' => (string)$v], ['k'], ['v']);
}

/** Lo schema e' installato e aggiornato? */
function brain_schema_ready(): bool {
    return brain_has_table(brain_t('nodes'))
        && (int)brain_meta_get('schema_version', 0) >= BRAIN_SCHEMA_VERSION;
}
