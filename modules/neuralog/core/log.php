<?php
/* ============================================================================
   COMPANY BRAIN — core/log.php
   Due tracce: 'activity' nel database (cio' che il cervello ha fatto, visibile
   in console e nel feed del grafo) e un file di log testuale per la diagnosi.
   Entrambe passano dalla redazione dei segreti: nel log non finisce mai una
   chiave, neanche per sbaglio.
============================================================================ */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

/** Registra un evento nel database (silenzioso se la tabella non c'e' ancora). */
function brain_activity(string $kind, string $detail = ''): void {
    $pdo = brain_pdo();
    if (!$pdo || !brain_has_table(brain_t('activity'))) { return; }
    try {
        $st = $pdo->prepare('INSERT INTO ' . brain_t('activity') . ' (kind, detail, created_at) VALUES (?,?,?)');
        $st->execute([brain_cut($kind, 60), brain_redact(brain_cut($detail, 900)), brain_now()]);
    } catch (Throwable $e) {}
}

/** Ultimi eventi. */
function brain_activity_recent(int $n = 12): array {
    if (!brain_has_table(brain_t('activity'))) { return []; }
    return brain_rows('SELECT kind, detail, created_at FROM ' . brain_t('activity') . ' ORDER BY id DESC LIMIT ' . max(1, min(200, $n)));
}

/** Revisione: cambia ad ogni attivita' -> serve al polling del grafo. */
function brain_rev(): int {
    if (!brain_has_table(brain_t('activity'))) { return 0; }
    return (int)brain_scalar('SELECT COALESCE(MAX(id),0) FROM ' . brain_t('activity'), [], 0);
}

/** Log su file, redatto, con rotazione semplice a 2 MB. */
function brain_log(string $level, string $msg): void {
    $dir = brain_data_dir();
    $f = $dir . '/brain.log';
    if (is_file($f) && @filesize($f) > 2 * 1024 * 1024) { @rename($f, $f . '.1'); }
    $line = '[' . brain_now() . '] ' . strtoupper($level) . ' ' . brain_redact(str_replace(["\r", "\n"], ' ', $msg)) . "\n";
    @file_put_contents($f, $line, FILE_APPEND | LOCK_EX);
}
