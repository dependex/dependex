<?php
/* ============================================================================
   COMPANY BRAIN — bus/bus.php
   BUS DEGLI AGENTI: orchestrazione su file, senza server, senza code esterne.
   Serve quando piu' agenti (o piu' persone, o un agente e una persona) lavorano
   sullo stesso progetto e devono non pestarsi i piedi.

   Come funziona, in tre righe:
   - i messaggi sono JSONL in SOLA AGGIUNTA: non si riscrive mai il passato,
     si aggiunge un messaggio nuovo che cambia lo stato;
   - i LOCK dichiarano "questo file lo sto toccando io", con una scadenza:
     un lock dimenticato si spegne da solo;
   - la DASHBOARD e i BRIEF si rigenerano dai messaggi: sono derivati, non
     verita' (la verita' e' il log).

   Tipi: TASK, DONE, QUESTION, ANSWER, ALERT, DECISION, LOCK, FORWARD.
   Stati: OPEN, IN_PROGRESS, DONE, BLOCKED, CANCELLED.
   Attori e cartella radice arrivano da config (bus.actors, bus.root).
   Scritture atomiche (file temporaneo + rename) e lock di scrittura su file.
============================================================================ */
require_once dirname(__DIR__) . '/core/config.php';

const BUS_TYPES  = ['TASK', 'DONE', 'QUESTION', 'ANSWER', 'ALERT', 'DECISION', 'LOCK', 'FORWARD'];
const BUS_STATES = ['OPEN', 'IN_PROGRESS', 'DONE', 'BLOCKED', 'CANCELLED'];
const BUS_FINAL  = ['DONE', 'CANCELLED'];
const BUS_PRIOS  = ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'];

/** Radice del bus: variabile d'ambiente BRAIN_BUS_ROOT, altrimenti config. */
function bus_root(): string {
    $env = getenv('BRAIN_BUS_ROOT');
    $root = ($env !== false && $env !== '') ? $env : brain_path((string)brain_cfg('bus.root', 'data/bus'));
    if (!is_dir($root)) { @mkdir($root, 0775, true); }
    return rtrim($root, '/');
}
function bus_path(string $rel): string { return bus_root() . '/' . ltrim($rel, '/'); }
function bus_actors(): array { return array_values(array_filter(array_map('strval', (array)brain_cfg('bus.actors', ['AGENT_A', 'AGENT_B', 'HUMAN', 'SYSTEM'])))); }
function bus_targets(): array {
    $t = (array)brain_cfg('bus.targets', []);
    return $t ? array_values(array_map('strval', $t)) : array_merge(bus_actors(), ['BOTH']);
}
function bus_now(): string { return gmdate('Y-m-d\TH:i:s\Z'); }

/** Scrittura atomica: tmp + rename, cosi' un lettore non vede mai un file a meta'. */
function bus_atomic_write(string $file, string $text): bool {
    $dir = dirname($file);
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $tmp = $file . '.' . getmypid() . '.tmp';
    if (@file_put_contents($tmp, $text) === false) { return false; }
    return @rename($tmp, $file);
}

/** Lock di scrittura su file, con scadenza: un lock morto non blocca per sempre. */
function bus_with_lock(string $target, callable $fn, float $timeout = 10.0) {
    $lock = $target . '.write.lock';
    $dir = dirname($lock);
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $deadline = microtime(true) + $timeout;
    $fh = null;
    while (true) {
        $fh = @fopen($lock, 'x');
        if ($fh !== false) { break; }
        $age = @filemtime($lock);
        if ($age !== false && (time() - $age) > 60) { @unlink($lock); continue; }
        if (microtime(true) >= $deadline) { throw new RuntimeException('lock di scrittura non ottenuto: ' . $lock); }
        usleep(80000);
    }
    fwrite($fh, getmypid() . '|' . bus_now());
    try { return $fn(); }
    finally { fclose($fh); @unlink($lock); }
}

/** Prepara le cartelle e i file del bus. */
function bus_init(): array {
    $root = bus_root();
    foreach (['', '/_archive', '/_errors', '/outbox'] as $d) {
        if (!is_dir($root . $d)) { @mkdir($root . $d, 0775, true); }
    }
    foreach (bus_actors() as $a) {
        $f = bus_path('outbox/' . strtolower($a) . '.jsonl');
        if (!is_file($f)) { @file_put_contents($f, ''); }
    }
    if (!is_file(bus_path('LOCKS.json'))) { bus_write_locks([]); }
    if (!is_file(bus_path('DECISIONS.md'))) { bus_atomic_write(bus_path('DECISIONS.md'), "# Decisioni\n\n"); }
    if (!is_file(bus_path('PROTOCOL.md'))) { bus_atomic_write(bus_path('PROTOCOL.md'), bus_protocol_text()); }
    return ['ok' => true, 'root' => $root, 'actors' => bus_actors()];
}

function bus_protocol_text(): string {
    return "# Protocollo del bus\n\n"
        . "- I log sono JSONL in sola aggiunta: non si modifica una riga scritta.\n"
        . "- Per cambiare lo stato di un TASK si scrive un messaggio nuovo con `ref` = id del task.\n"
        . "- Tipi: " . implode(', ', BUS_TYPES) . "\n"
        . "- Stati: " . implode(', ', BUS_STATES) . "\n"
        . "- Prima di toccare un file condiviso si prende un LOCK; i lock scadono da soli.\n"
        . "- DASHBOARD.md e i brief sono generati: non si scrivono a mano.\n";
}

/** Valida un messaggio. Ritorna la lista degli errori (vuota = valido). */
function bus_validate(array $m): array {
    $e = [];
    foreach (['id', 'ts', 'from', 'to', 'type', 'title'] as $k) {
        if (!isset($m[$k]) || trim((string)$m[$k]) === '') { $e[] = 'campo mancante: ' . $k; }
    }
    if (isset($m['type']) && !in_array($m['type'], BUS_TYPES, true)) { $e[] = 'tipo sconosciuto: ' . $m['type']; }
    if (isset($m['state']) && $m['state'] !== '' && !in_array($m['state'], BUS_STATES, true)) { $e[] = 'stato sconosciuto: ' . $m['state']; }
    if (isset($m['from']) && !in_array($m['from'], bus_actors(), true)) { $e[] = 'mittente sconosciuto: ' . $m['from']; }
    if (isset($m['to']) && !in_array($m['to'], bus_targets(), true)) { $e[] = 'destinatario sconosciuto: ' . $m['to']; }
    if (isset($m['priority']) && $m['priority'] !== '' && !in_array($m['priority'], BUS_PRIOS, true)) { $e[] = 'priorita sconosciuta'; }
    if (isset($m['files']) && !is_array($m['files'])) { $e[] = 'files deve essere una lista'; }
    return $e;
}

/** Costruisce un messaggio completo. */
function bus_message(string $from, string $to, string $type, string $title, array $opt = []): array {
    return [
        'id'       => (string)($opt['id'] ?? ('m-' . gmdate('Ymd\THis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8))),
        'ts'       => bus_now(),
        'from'     => strtoupper($from),
        'to'       => strtoupper($to),
        'type'     => strtoupper($type),
        'title'    => trim($title),
        'detail'   => (string)($opt['detail'] ?? ''),
        'files'    => array_values(array_map('strval', (array)($opt['files'] ?? []))),
        'priority' => strtoupper((string)($opt['priority'] ?? 'MEDIUM')),
        'state'    => strtoupper((string)($opt['state'] ?? 'OPEN')),
        'ref'      => isset($opt['ref']) ? (string)$opt['ref'] : null,
    ];
}

/** Accoda un messaggio nella casella del mittente. */
function bus_append(array $m): array {
    bus_init();
    $err = bus_validate($m);
    if ($err) { return ['ok' => false, 'errors' => $err]; }
    $file = bus_path('outbox/' . strtolower($m['from']) . '.jsonl');
    $line = json_encode($m, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    bus_with_lock($file, static function () use ($file, $line) {
        $fh = fopen($file, 'a');
        if ($fh) { fwrite($fh, $line . "\n"); fflush($fh); fclose($fh); }
    });
    return ['ok' => true, 'id' => $m['id'], 'file' => $file];
}

/** Legge tutti i messaggi validi, in ordine di tempo. Le righe rotte finiscono in _errors. */
function bus_read(): array {
    bus_init();
    $msgs = []; $bad = [];
    foreach (glob(bus_path('outbox/*.jsonl')) ?: [] as $f) {
        $n = 0;
        foreach (file($f, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $n++;
            if (trim($line) === '') { continue; }
            $j = json_decode($line, true);
            if (!is_array($j) || bus_validate($j)) {
                $bad[] = ['file' => basename($f), 'line' => $n, 'raw' => mb_substr($line, 0, 300)];
                continue;
            }
            $j['_file'] = basename($f);
            $msgs[] = $j;
        }
    }
    if ($bad) { @file_put_contents(bus_path('_errors/invalid.jsonl'), implode("\n", array_map(static fn($b) => json_encode($b, JSON_UNESCAPED_UNICODE), $bad)) . "\n"); }
    usort($msgs, static fn($a, $b) => strcmp((string)$a['ts'], (string)$b['ts']));
    return $msgs;
}

/** Stato attuale di un task = stato dell'ultimo messaggio che lo referenzia. */
function bus_task_state(array $task, array $msgs): array {
    $rel = array_values(array_filter($msgs, static fn($m) => ($m['ref'] ?? null) === $task['id']));
    if (!$rel) { return [$task['state'], null, 0]; }
    usort($rel, static fn($a, $b) => strcmp((string)$a['ts'], (string)$b['ts']));
    $last = $rel[count($rel) - 1];
    return [$last['state'], $last, count($rel)];
}

/** I task ancora vivi. */
function bus_active_tasks(array $msgs): array {
    $out = [];
    foreach ($msgs as $m) {
        if (!in_array($m['type'], ['TASK', 'FORWARD'], true)) { continue; }
        [$state, $last, $n] = bus_task_state($m, $msgs);
        if (in_array($state, BUS_FINAL, true)) { continue; }
        $m['effective_state'] = $state;
        $m['last_event'] = $last;
        $m['replies'] = $n;
        $out[] = $m;
    }
    return $out;
}

/* ------------------------------ LOCK ------------------------------------ */

function bus_read_locks(): array {
    $f = bus_path('LOCKS.json');
    if (!is_file($f)) { return []; }
    $j = json_decode((string)@file_get_contents($f), true);
    return is_array($j['locks'] ?? null) ? $j['locks'] : [];
}
function bus_write_locks(array $locks): bool {
    return bus_atomic_write(bus_path('LOCKS.json'),
        json_encode(['version' => 1, 'updated_at' => bus_now(), 'locks' => array_values($locks)],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
}
function bus_lock_expired(array $l): bool {
    $t = strtotime((string)($l['expires_at'] ?? ''));
    return $t === false || $t <= time();
}
function bus_lock(string $actor, string $file, ?float $hours = null, ?string $ref = null): array {
    bus_init();
    $hours = $hours ?? (float)brain_cfg('bus.lock_hours', 2);
    $actor = strtoupper($actor);
    $res = bus_with_lock(bus_path('LOCKS.json'), static function () use ($actor, $file, $hours, $ref) {
        $locks = bus_read_locks();
        $keep = [];
        foreach ($locks as $l) {
            if (bus_lock_expired($l)) { continue; }
            if (bus_norm_path((string)$l['file']) === bus_norm_path($file) && strtoupper((string)$l['owner']) !== $actor) {
                return ['ok' => false, 'error' => 'file gia bloccato da ' . $l['owner'], 'lock' => $l];
            }
            if (bus_norm_path((string)$l['file']) === bus_norm_path($file)) { continue; }
            $keep[] = $l;
        }
        $lock = ['id' => 'lock-' . substr(hash('sha256', $actor . '|' . $file), 0, 16),
                 'file' => $file, 'owner' => $actor,
                 'acquired_at' => bus_now(),
                 'expires_at' => gmdate('Y-m-d\TH:i:s\Z', time() + (int)round($hours * 3600)),
                 'task_ref' => $ref, 'status' => 'ACTIVE'];
        $keep[] = $lock;
        bus_write_locks($keep);
        return ['ok' => true, 'lock' => $lock];
    });
    if (!empty($res['ok'])) { bus_append(bus_message($actor, 'SYSTEM', 'LOCK', 'lock su ' . $file, ['files' => [$file], 'ref' => $ref, 'state' => 'IN_PROGRESS'])); }
    return $res;
}
function bus_unlock(string $actor, string $file): array {
    $actor = strtoupper($actor);
    return bus_with_lock(bus_path('LOCKS.json'), static function () use ($actor, $file) {
        $locks = bus_read_locks(); $keep = []; $found = false;
        foreach ($locks as $l) {
            if (bus_norm_path((string)$l['file']) === bus_norm_path($file) && strtoupper((string)$l['owner']) === $actor) { $found = true; continue; }
            if (bus_lock_expired($l)) { continue; }
            $keep[] = $l;
        }
        bus_write_locks($keep);
        return ['ok' => $found, 'released' => $found];
    });
}
function bus_norm_path(string $p): string { return strtolower(str_replace('\\', '/', trim($p))); }

/* --------------------- conflitti, orfani, doppioni ---------------------- */

function bus_conflicts(array $msgs, array $locks): array {
    $out = [];
    $tasks = bus_active_tasks($msgs);
    for ($i = 0; $i < count($tasks); $i++) {
        $L = $tasks[$i];
        $lf = [];
        foreach ((array)$L['files'] as $f) { if (trim((string)$f) !== '') { $lf[bus_norm_path($f)] = $f; } }
        for ($j = $i + 1; $j < count($tasks); $j++) {
            $R = $tasks[$j];
            if ($R['to'] === $L['to']) { continue; }
            foreach ((array)$R['files'] as $f) {
                $k = bus_norm_path((string)$f);
                if (isset($lf[$k])) {
                    $out[] = ['kind' => 'TASK_FILE', 'file' => $lf[$k], 'left' => $L['id'], 'right' => $R['id'],
                              'owners' => $L['to'] . '/' . $R['to']];
                }
            }
        }
    }
    $byFile = [];
    foreach ($locks as $l) {
        if (($l['status'] ?? '') !== 'ACTIVE' || bus_lock_expired($l)) { continue; }
        $k = bus_norm_path((string)$l['file']);
        if (isset($byFile[$k]) && strtoupper((string)$byFile[$k]['owner']) !== strtoupper((string)$l['owner'])) {
            $out[] = ['kind' => 'LOCK', 'file' => $l['file'], 'left' => $byFile[$k]['id'], 'right' => $l['id'],
                      'owners' => $byFile[$k]['owner'] . '/' . $l['owner']];
        } else { $byFile[$k] = $l; }
    }
    return $out;
}

function bus_orphans(array $msgs, ?int $hours = null): array {
    $hours = $hours ?? (int)brain_cfg('bus.orphan_hours', 24);
    $cut = time() - $hours * 3600;
    return array_values(array_filter(bus_active_tasks($msgs),
        static fn($t) => (strtotime((string)$t['ts']) ?: 0) <= $cut && (int)$t['replies'] === 0));
}

function bus_duplicates(array $msgs): array {
    $groups = [];
    foreach (bus_active_tasks($msgs) as $t) {
        $k = preg_replace('/[^a-z0-9 ]/', '', mb_strtolower((string)$t['title']));
        $k = trim(preg_replace('/\s+/', ' ', (string)$k));
        if ($k !== '') { $groups[$k][] = $t; }
    }
    $out = [];
    foreach ($groups as $g) {
        if (count($g) < 2) { continue; }
        $refs = array_unique(array_filter(array_column($g, 'ref')));
        if (count($refs) === 1) { continue; }
        $out[] = $g;
    }
    return $out;
}

/* ------------------------ dashboard e brief ----------------------------- */

function bus_dashboard(): array {
    $msgs = bus_read();
    $locks = bus_read_locks();
    $tasks = bus_active_tasks($msgs);
    $conf = bus_conflicts($msgs, $locks);
    $orph = bus_orphans($msgs);
    $dups = bus_duplicates($msgs);

    $md = "# Dashboard del bus\n\n_Generata il " . bus_now() . " — file derivato, non modificarlo a mano._\n\n";
    $md .= "## Numeri\n\n- messaggi: " . count($msgs) . "\n- task attivi: " . count($tasks)
         . "\n- lock attivi: " . count(array_filter($locks, static fn($l) => !bus_lock_expired($l)))
         . "\n- conflitti: " . count($conf) . "\n- orfani: " . count($orph) . "\n- possibili doppioni: " . count($dups) . "\n\n";

    $md .= "## Task attivi\n\n";
    if (!$tasks) { $md .= "_nessuno_\n\n"; }
    else {
        $md .= "| id | da | a | stato | priorita | titolo |\n|---|---|---|---|---|---|\n";
        foreach ($tasks as $t) {
            $md .= '| ' . $t['id'] . ' | ' . $t['from'] . ' | ' . $t['to'] . ' | ' . $t['effective_state']
                 . ' | ' . $t['priority'] . ' | ' . str_replace('|', '/', mb_substr((string)$t['title'], 0, 80)) . " |\n";
        }
        $md .= "\n";
    }
    if ($conf) {
        $md .= "## Conflitti\n\n";
        foreach ($conf as $c) { $md .= '- **' . $c['kind'] . '** su `' . $c['file'] . '` — ' . $c['left'] . ' vs ' . $c['right'] . ' (' . $c['owners'] . ")\n"; }
        $md .= "\n";
    }
    if ($orph) {
        $md .= "## Task senza risposta\n\n";
        foreach ($orph as $o) { $md .= '- ' . $o['id'] . ' — ' . mb_substr((string)$o['title'], 0, 90) . ' (dal ' . $o['ts'] . ")\n"; }
        $md .= "\n";
    }
    bus_atomic_write(bus_path('DASHBOARD.md'), $md);
    bus_write_briefs($msgs, $locks);
    return ['ok' => true, 'messages' => count($msgs), 'tasks' => count($tasks),
            'conflicts' => count($conf), 'orphans' => count($orph), 'duplicates' => count($dups),
            'dashboard' => bus_path('DASHBOARD.md')];
}

/** Un brief per attore: cosa deve fare lui, adesso. */
function bus_brief(string $actor, array $msgs, array $locks): string {
    $actor = strtoupper($actor);
    $mine = array_values(array_filter(bus_active_tasks($msgs),
        static fn($t) => $t['to'] === $actor || $t['to'] === 'BOTH'));
    usort($mine, static function ($a, $b) {
        $order = array_flip(['CRITICAL', 'HIGH', 'MEDIUM', 'LOW']);
        return ($order[$a['priority']] ?? 9) <=> ($order[$b['priority']] ?? 9);
    });
    $s = "# Brief — $actor\n\n_" . bus_now() . "_\n\n";
    $s .= "## Cosa hai in mano (" . count($mine) . ")\n\n";
    if (!$mine) { $s .= "_niente di aperto_\n"; }
    foreach ($mine as $t) {
        $s .= '- **[' . $t['priority'] . '] ' . mb_substr((string)$t['title'], 0, 100) . "**  \n";
        $s .= '  id `' . $t['id'] . '` · da ' . $t['from'] . ' · stato ' . $t['effective_state'];
        if ($t['files']) { $s .= ' · file: `' . implode('`, `', array_slice($t['files'], 0, 5)) . '`'; }
        $s .= "\n";
    }
    $myLocks = array_values(array_filter($locks, static fn($l) => strtoupper((string)$l['owner']) === $actor && !bus_lock_expired($l)));
    $s .= "\n## File che stai tenendo bloccati (" . count($myLocks) . ")\n\n";
    foreach ($myLocks as $l) { $s .= '- `' . $l['file'] . '` fino a ' . $l['expires_at'] . "\n"; }
    if (!$myLocks) { $s .= "_nessuno_\n"; }
    $others = array_values(array_filter($locks, static fn($l) => strtoupper((string)$l['owner']) !== $actor && !bus_lock_expired($l)));
    if ($others) {
        $s .= "\n## Non toccare: bloccati da altri\n\n";
        foreach ($others as $l) { $s .= '- `' . $l['file'] . '` — ' . $l['owner'] . "\n"; }
    }
    return $s;
}

function bus_write_briefs(?array $msgs = null, ?array $locks = null): void {
    $msgs = $msgs ?? bus_read();
    $locks = $locks ?? bus_read_locks();
    foreach (bus_actors() as $a) {
        bus_atomic_write(bus_path('BRIEF_' . strtoupper($a) . '.md'), bus_brief($a, $msgs, $locks));
    }
}

/** Registra una decisione (append-only, anche su DECISIONS.md). */
function bus_decision(string $actor, string $title, string $detail = '', ?string $ref = null): array {
    bus_init();
    $r = bus_append(bus_message($actor, 'SYSTEM', 'DECISION', $title, ['detail' => $detail, 'ref' => $ref, 'state' => 'DONE']));
    if (!empty($r['ok'])) {
        $f = bus_path('DECISIONS.md');
        $line = "\n## " . bus_now() . " — " . $title . "\n\n- deciso da: " . strtoupper($actor) . "\n"
              . ($ref ? "- riferimento: $ref\n" : '') . ($detail !== '' ? "\n$detail\n" : '');
        bus_with_lock($f, static function () use ($f, $line) { @file_put_contents($f, $line, FILE_APPEND); });
    }
    return $r;
}

/** Rotazione dei log: le righe vecchie finiscono in _archive. */
function bus_rotate(?int $maxLines = null): array {
    $maxLines = $maxLines ?? (int)brain_cfg('bus.max_lines_per_log', 5000);
    $moved = [];
    foreach (glob(bus_path('outbox/*.jsonl')) ?: [] as $f) {
        $lines = file($f, FILE_IGNORE_NEW_LINES) ?: [];
        if (count($lines) <= $maxLines) { continue; }
        $keep = array_slice($lines, -$maxLines);
        $old  = array_slice($lines, 0, count($lines) - $maxLines);
        $arch = bus_path('_archive/' . basename($f, '.jsonl') . '-' . gmdate('Ymd\THis') . '.jsonl');
        bus_atomic_write($arch, implode("\n", $old) . "\n");
        bus_atomic_write($f, implode("\n", $keep) . "\n");
        $moved[] = ['file' => basename($f), 'archived' => count($old)];
    }
    return ['ok' => true, 'rotated' => $moved];
}

/* ------------------------------- CLI ------------------------------------ */
if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $cmd = $argv[1] ?? 'help';
    $opt = []; $pos = [];
    foreach (array_slice($argv, 2) as $a) {
        if (str_starts_with($a, '--')) { $p = explode('=', substr($a, 2), 2); $opt[$p[0]] = $p[1] ?? true; }
        else { $pos[] = $a; }
    }
    $say = static function ($v) { echo is_string($v) ? $v . "\n" : json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"; };
    switch ($cmd) {
        case 'init':      $say(bus_init()); break;
        case 'send':      $say(bus_append(bus_message(
                              (string)($opt['from'] ?? 'HUMAN'), (string)($opt['to'] ?? 'SYSTEM'),
                              (string)($opt['type'] ?? 'TASK'), (string)($pos[0] ?? ($opt['title'] ?? 'senza titolo')),
                              ['detail' => (string)($opt['detail'] ?? ''),
                               'files' => array_filter(explode(',', (string)($opt['files'] ?? ''))),
                               'priority' => (string)($opt['priority'] ?? 'MEDIUM'),
                               'state' => (string)($opt['state'] ?? 'OPEN'),
                               'ref' => $opt['ref'] ?? null]))); break;
        case 'list':      $say(array_map(static fn($m) => [$m['ts'], $m['type'], $m['from'] . '->' . $m['to'], $m['state'], $m['title']], bus_read())); break;
        case 'tasks':     $say(array_map(static fn($t) => [$t['id'], $t['to'], $t['effective_state'], $t['title']], bus_active_tasks(bus_read()))); break;
        case 'lock':      $say(bus_lock((string)($opt['actor'] ?? 'HUMAN'), (string)($pos[0] ?? ''), isset($opt['hours']) ? (float)$opt['hours'] : null, $opt['ref'] ?? null)); break;
        case 'unlock':    $say(bus_unlock((string)($opt['actor'] ?? 'HUMAN'), (string)($pos[0] ?? ''))); break;
        case 'locks':     $say(bus_read_locks()); break;
        case 'decision':  $say(bus_decision((string)($opt['actor'] ?? 'HUMAN'), (string)($pos[0] ?? ''), (string)($opt['detail'] ?? ''), $opt['ref'] ?? null)); break;
        case 'dashboard': $say(bus_dashboard()); break;
        case 'brief':     echo bus_brief((string)($pos[0] ?? 'HUMAN'), bus_read(), bus_read_locks()); break;
        case 'rotate':    $say(bus_rotate(isset($opt['max']) ? (int)$opt['max'] : null)); break;
        case 'doctor':    $m = bus_read(); $l = bus_read_locks();
                          $say(['messages' => count($m), 'active_tasks' => count(bus_active_tasks($m)),
                                'locks' => count($l), 'conflicts' => bus_conflicts($m, $l),
                                'orphans' => count(bus_orphans($m)), 'duplicates' => count(bus_duplicates($m))]); break;
        default:
            echo "bus.php — orchestrazione fra agenti su file\n"
               . "  init | send --from=A --to=B --type=TASK \"titolo\" [--files=a,b --priority=HIGH --ref=id]\n"
               . "  list | tasks | lock FILE --actor=A [--hours=2] | unlock FILE --actor=A | locks\n"
               . "  decision \"titolo\" --actor=A [--detail=...] | dashboard | brief ATTORE | rotate | doctor\n";
    }
}
