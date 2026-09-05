<?php
/* ============================================================================
   COMPANY BRAIN — core/config.php
   Configurazione: un solo file JSON di default (config/brain.config.json) piu'
   un eventuale config/brain.local.json che sovrascrive SOLO le chiavi presenti.
   Nessun dato di dominio vive nel motore: tutto passa da qui.
   Accesso a punti: brain_cfg('rag.top_k', 8).
============================================================================ */
if (defined('BRAIN_CONFIG_LOADED')) { return; }
define('BRAIN_CONFIG_LOADED', 1);

if (!defined('BRAIN_ROOT')) { define('BRAIN_ROOT', dirname(__DIR__)); }

/** Fonde ricorsivamente $over dentro $base (array associativi). */
function brain_cfg_merge(array $base, array $over): array {
    foreach ($over as $k => $v) {
        if (is_array($v) && isset($base[$k]) && is_array($base[$k])
            && array_keys($base[$k]) !== range(0, count($base[$k]) - 1)) {
            $base[$k] = brain_cfg_merge($base[$k], $v);
        } else {
            $base[$k] = $v;
        }
    }
    return $base;
}

/** Config completa (memoizzata). */
function brain_config(): array {
    static $cfg = null;
    if ($cfg !== null) { return $cfg; }
    $cfg = [];
    $main = BRAIN_ROOT . '/config/brain.config.json';
    if (is_file($main)) {
        $j = json_decode((string)@file_get_contents($main), true);
        if (is_array($j)) { $cfg = $j; }
    }
    $local = BRAIN_ROOT . '/config/brain.local.json';
    if (is_file($local)) {
        $j = json_decode((string)@file_get_contents($local), true);
        if (is_array($j)) { $cfg = brain_cfg_merge($cfg, $j); }
    }
    /* override d'ambiente per le poche cose che cambiano fra macchine */
    $envMap = [
        'BRAIN_DB_DSN'       => 'db.dsn',
        'BRAIN_DB_USER'      => 'db.user',
        'BRAIN_DB_PASS'      => 'db.pass',
        'BRAIN_DB_PREFIX'    => 'db.table_prefix',
        'BRAIN_SQLITE_PATH'  => 'db.sqlite_path',
        'BRAIN_LANGUAGE'     => 'brain.language',
    ];
    foreach ($envMap as $env => $path) {
        $v = getenv($env);
        if ($v !== false && $v !== '') { $cfg = brain_cfg_set($cfg, $path, $v); }
    }
    return $cfg;
}

/** Scrive una chiave a punti dentro un array (uso interno). */
function brain_cfg_set(array $cfg, string $path, $value): array {
    $parts = explode('.', $path);
    $ref = &$cfg;
    foreach ($parts as $p) {
        if (!isset($ref[$p]) || !is_array($ref[$p])) { $ref[$p] = []; }
        $ref = &$ref[$p];
    }
    $ref = $value;
    unset($ref);
    return $cfg;
}

/** Legge una chiave a punti. */
function brain_cfg(string $path, $default = null) {
    $cur = brain_config();
    foreach (explode('.', $path) as $p) {
        if (!is_array($cur) || !array_key_exists($p, $cur)) { return $default; }
        $cur = $cur[$p];
    }
    return $cur;
}

/** Percorso assoluto a partire da una voce di config relativa a BRAIN_ROOT. */
function brain_path(string $rel): string {
    if ($rel === '') { return BRAIN_ROOT; }
    if ($rel[0] === '/' || preg_match('#^[A-Za-z]:[\\\\/]#', $rel)) { return $rel; }
    return BRAIN_ROOT . '/' . ltrim($rel, '/');
}

/** Cartella dati, creata se manca. */
function brain_data_dir(): string {
    $d = brain_path((string)brain_cfg('paths.data_dir', 'data'));
    if (!is_dir($d)) { @mkdir($d, 0775, true); }
    return $d;
}

/** Carica un JSON dal modulo, [] se manca o e' rotto. */
function brain_json_file(string $rel): array {
    $p = brain_path($rel);
    if (!is_file($p)) { return []; }
    $j = json_decode((string)@file_get_contents($p), true);
    return is_array($j) ? $j : [];
}

/** Versione del modulo (file VERSION). */
function brain_version(): string {
    $f = BRAIN_ROOT . '/VERSION';
    return is_file($f) ? trim((string)@file_get_contents($f)) : '0';
}
