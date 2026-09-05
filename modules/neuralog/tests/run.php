<?php
/* ============================================================================
   COMPANY BRAIN — tests/run.php
   Banco di prova del codice. Gira su un database SQLite USA-E-GETTA
   (data/test-brain.sqlite): non tocca mai i dati veri.
       php tests/run.php            tutti i test
       php tests/run.php t_rag      solo un gruppo
   Uscita 0 = tutto verde. Uscita 1 = almeno un test rosso.
============================================================================ */
if (PHP_SAPI !== 'cli') { exit("solo da riga di comando\n"); }

$root = dirname(__DIR__);
$dbFile = $root . '/data/test-brain.sqlite';
@unlink($dbFile);
@unlink($dbFile . '-wal');
@unlink($dbFile . '-shm');
putenv('BRAIN_SQLITE_PATH=' . $dbFile);
putenv('BRAIN_BUS_ROOT=' . $root . '/data/test-bus');
@exec('rm -rf ' . escapeshellarg($root . '/data/test-bus'));

require_once $root . '/brain.php';
require_once $root . '/ingest/demo.php';
require_once $root . '/bus/bus.php';

$GLOBALS['T'] = ['ok' => 0, 'fail' => 0, 'errors' => [], 'group' => ''];

function t_group(string $name): void { $GLOBALS['T']['group'] = $name; echo "\n— $name\n"; }
function t_pass(string $what): void { $GLOBALS['T']['ok']++; echo "  ok    $what\n"; }
function t_failed(string $what, string $why): void {
    $GLOBALS['T']['fail']++;
    $GLOBALS['T']['errors'][] = $GLOBALS['T']['group'] . ' :: ' . $what . ' — ' . $why;
    echo "  FAIL  $what   ($why)\n";
}
function t_ok($cond, string $what): void { $cond ? t_pass($what) : t_failed($what, 'atteso vero'); }
function t_eq($a, $b, string $what): void {
    if ($a === $b) { t_pass($what); return; }
    t_failed($what, 'atteso ' . var_export($b, true) . ', ottenuto ' . var_export($a, true));
}
function t_gt($a, $b, string $what): void {
    if ($a > $b) { t_pass($what); return; }
    t_failed($what, var_export($a, true) . ' non e maggiore di ' . var_export($b, true));
}
function t_contains(string $hay, string $needle, string $what): void {
    if (mb_stripos($hay, $needle) !== false) { t_pass($what); return; }
    t_failed($what, '"' . mb_substr($needle, 0, 40) . '" non trovato');
}

$only = $argv[1] ?? '';
$files = glob(__DIR__ . '/t_*.php') ?: [];
sort($files);
$t0 = microtime(true);
echo "Company Brain " . brain_version() . " — test (" . brain_driver() . ")\n";
foreach ($files as $f) {
    if ($only !== '' && strpos(basename($f, '.php'), $only) === false) { continue; }
    require $f;
}
/* pulizia: i test non lasciano rifiuti nella cartella dati */
@unlink($dbFile); @unlink($dbFile . '-wal'); @unlink($dbFile . '-shm');
@exec('rm -rf ' . escapeshellarg($root . '/data/test-bus') . ' ' . escapeshellarg($root . '/data/test-inbox'));
foreach (glob($root . '/data/knowledge/*test-inbox*.md') ?: [] as $f) { @unlink($f); }

$ms = (int)round((microtime(true) - $t0) * 1000);
$T = $GLOBALS['T'];
echo "\n" . str_repeat('=', 60) . "\n";
echo 'PASSATI: ' . $T['ok'] . '   FALLITI: ' . $T['fail'] . '   (' . $ms . " ms)\n";
if ($T['fail']) {
    echo "\nDettaglio dei fallimenti:\n";
    foreach ($T['errors'] as $e) { echo '  - ' . $e . "\n"; }
}
exit($T['fail'] ? 1 : 0);
