<?php
/* ============================================================================
   COMPANY BRAIN — core/db.php
   REGOLA "UN SOLO DATABASE": se l'applicazione ospite ha gia' un PDO, il
   cervello ci vive dentro; non apre mai un secondo database alle spalle di
   nessuno. Ordine di risoluzione:
     1) brain_set_pdo($pdo)                 — iniezione esplicita
     2) $GLOBALS['BRAIN_PDO']               — variabile globale dell'ospite
     3) funzione brain_host_pdo()           — callback definita dall'ospite
     4) db.dsn in config (MySQL/altro)      — connessione propria
     5) db.sqlite_path                      — file SQLite proprio (default)
   Tutto il resto del modulo passa da qui e da brain_t() per i nomi tabella.
   Le differenze fra SQLite e MySQL sono confinate in questo file.
============================================================================ */
require_once __DIR__ . '/config.php';

if (!function_exists('brain_set_pdo')) {
function brain_set_pdo(PDO $pdo): void { $GLOBALS['BRAIN_PDO_INJECTED'] = $pdo; }
}

if (!function_exists('brain_pdo')) {
function brain_pdo(): ?PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) { return $pdo; }

    if (isset($GLOBALS['BRAIN_PDO_INJECTED']) && $GLOBALS['BRAIN_PDO_INJECTED'] instanceof PDO) {
        return $pdo = $GLOBALS['BRAIN_PDO_INJECTED'];
    }
    if (brain_cfg('db.use_host_pdo', true)) {
        if (isset($GLOBALS['BRAIN_PDO']) && $GLOBALS['BRAIN_PDO'] instanceof PDO) {
            return $pdo = $GLOBALS['BRAIN_PDO'];
        }
        if (function_exists('brain_host_pdo')) {
            try { $p = brain_host_pdo(); if ($p instanceof PDO) { return $pdo = $p; } }
            catch (Throwable $e) { /* si prosegue con la connessione propria */ }
        }
    }
    $dsn = (string)brain_cfg('db.dsn', '');
    try {
        if ($dsn !== '') {
            $pdo = new PDO($dsn, (string)brain_cfg('db.user', ''), (string)brain_cfg('db.pass', ''), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } else {
            $file = brain_path((string)brain_cfg('db.sqlite_path', 'data/brain.sqlite'));
            $dir  = dirname($file);
            if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
            $pdo = new PDO('sqlite:' . $file, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            try { $pdo->exec('PRAGMA journal_mode=WAL'); } catch (Throwable $e) {}
            try { $pdo->exec('PRAGMA synchronous=NORMAL'); } catch (Throwable $e) {}
            try { $pdo->exec('PRAGMA busy_timeout=8000'); } catch (Throwable $e) {}
        }
    } catch (Throwable $e) {
        $pdo = null;
        return null;
    }
    return $pdo;
}
}

/** Nome driver: 'sqlite', 'mysql', ... */
function brain_driver(?PDO $pdo = null): string {
    $pdo = $pdo ?: brain_pdo();
    if (!$pdo) { return ''; }
    try { return (string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME); }
    catch (Throwable $e) { return ''; }
}

/** Nome tabella con prefisso configurabile. */
function brain_t(string $name): string {
    static $prefix = null;
    if ($prefix === null) {
        $prefix = (string)brain_cfg('db.table_prefix', 'brain_');
        if (!preg_match('/^[A-Za-z0-9_]*$/', $prefix)) { $prefix = 'brain_'; }
    }
    return $prefix . $name;
}

/** Timestamp UTC portabile (mai datetime('now')/NOW(): li scrive PHP). */
function brain_now(): string { return gmdate('Y-m-d H:i:s'); }

/** La tabella esiste? (per-driver, senza eccezioni rumorose) */
function brain_has_table(string $table, ?PDO $pdo = null): bool {
    $pdo = $pdo ?: brain_pdo();
    if (!$pdo) { return false; }
    $drv = brain_driver($pdo);
    try {
        if ($drv === 'sqlite') {
            $st = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $st->execute([$table]);
            return (bool)$st->fetchColumn();
        }
        $st = $pdo->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?");
        $st->execute([$table]);
        return (bool)$st->fetchColumn();
    } catch (Throwable $e) { return false; }
}

/** INSERT che ignora i duplicati, in modo portabile. */
function brain_insert_ignore(PDO $pdo, string $table, array $data): int {
    $cols = array_keys($data);
    $ph   = implode(',', array_fill(0, count($cols), '?'));
    $sql  = (brain_driver($pdo) === 'sqlite')
        ? 'INSERT OR IGNORE INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . $ph . ')'
        : 'INSERT IGNORE INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . $ph . ')';
    try {
        $st = $pdo->prepare($sql);
        $st->execute(array_values($data));
        return $st->rowCount();
    } catch (Throwable $e) { return 0; }
}

/**
 * UPSERT portabile. $keys = colonne della chiave (PK o indice unico),
 * $update = colonne da aggiornare in caso di conflitto.
 */
function brain_upsert(PDO $pdo, string $table, array $data, array $keys, array $update): bool {
    $cols = array_keys($data);
    $ph   = implode(',', array_fill(0, count($cols), '?'));
    $drv  = brain_driver($pdo);
    if ($drv === 'sqlite') {
        $set = [];
        foreach ($update as $c) { $set[] = $c . '=excluded.' . $c; }
        $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . $ph . ')';
        $sql .= $set
            ? ' ON CONFLICT(' . implode(',', $keys) . ') DO UPDATE SET ' . implode(',', $set)
            : ' ON CONFLICT(' . implode(',', $keys) . ') DO NOTHING';
    } else {
        $set = [];
        foreach ($update as $c) { $set[] = $c . '=VALUES(' . $c . ')'; }
        if (!$set) { $set[] = $keys[0] . '=' . $keys[0]; }
        $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . $ph . ')'
             . ' ON DUPLICATE KEY UPDATE ' . implode(',', $set);
    }
    try { $st = $pdo->prepare($sql); $st->execute(array_values($data)); return true; }
    catch (Throwable $e) { return false; }
}

/** SELECT di un singolo valore, con default se qualcosa va storto. */
function brain_scalar(string $sql, array $args = [], $default = 0) {
    $pdo = brain_pdo();
    if (!$pdo) { return $default; }
    try { $st = $pdo->prepare($sql); $st->execute($args); $v = $st->fetchColumn(); return $v === false ? $default : $v; }
    catch (Throwable $e) { return $default; }
}

/** SELECT multipla tollerante agli errori. */
function brain_rows(string $sql, array $args = []): array {
    $pdo = brain_pdo();
    if (!$pdo) { return []; }
    try { $st = $pdo->prepare($sql); $st->execute($args); return $st->fetchAll(PDO::FETCH_ASSOC) ?: []; }
    catch (Throwable $e) { return []; }
}

/** Esecuzione tollerante: ritorna righe toccate o -1 in caso d'errore. */
function brain_exec(string $sql, array $args = []): int {
    $pdo = brain_pdo();
    if (!$pdo) { return -1; }
    try { $st = $pdo->prepare($sql); $st->execute($args); return $st->rowCount(); }
    catch (Throwable $e) { return -1; }
}
