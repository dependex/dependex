<?php
/* Bus degli agenti: messaggi, stati, lock, conflitti, dashboard, brief. */

t_group('bus — messaggi');
$init = bus_init();
t_ok($init['ok'], 'il bus si inizializza');
t_ok(is_dir($init['root']), 'la cartella del bus esiste');

$m = bus_message('AGENT_A', 'AGENT_B', 'TASK', 'preparare il rilascio', ['files' => ['app/deploy.php'], 'priority' => 'HIGH']);
t_eq(bus_validate($m), [], 'un messaggio ben formato passa la validazione');
t_gt(count(bus_validate(['id' => 'x'])), 2, 'un messaggio incompleto viene respinto con le ragioni');
t_gt(count(bus_validate(bus_message('SCONOSCIUTO', 'AGENT_B', 'TASK', 't'))), 0, 'mittente non previsto respinto');
t_gt(count(bus_validate(bus_message('AGENT_A', 'AGENT_B', 'TIPO_INVENTATO', 't'))), 0, 'tipo non previsto respinto');

$res = bus_append($m);
t_ok($res['ok'], 'il messaggio viene accodato');
$msgs = bus_read();
t_gt(count($msgs), 0, 'i messaggi si rileggono');
$ids = array_column($msgs, 'id');
t_ok(in_array($m['id'], $ids, true), 'il messaggio appena scritto c e');

t_group('bus — stato dei task');
$tasks = bus_active_tasks($msgs);
t_gt(count($tasks), 0, 'il task risulta attivo');
bus_append(bus_message('AGENT_B', 'AGENT_A', 'DONE', 'rilascio preparato', ['ref' => $m['id'], 'state' => 'DONE']));
$msgs2 = bus_read();
$stillOpen = false;
foreach (bus_active_tasks($msgs2) as $t) { if ($t['id'] === $m['id']) { $stillOpen = true; } }
t_ok(!$stillOpen, 'un messaggio nuovo che referenzia il task lo chiude (log in sola aggiunta)');
t_eq(count(bus_read()), count($msgs) + 1, 'niente viene riscritto: si aggiunge soltanto');

t_group('bus — lock');
$l1 = bus_lock('AGENT_A', 'app/pagamenti.php', 1);
t_ok($l1['ok'], 'primo lock ottenuto');
$l2 = bus_lock('AGENT_B', 'app/pagamenti.php', 1);
t_ok(!$l2['ok'], 'il secondo agente non puo prendere lo stesso file');
t_contains((string)$l2['error'], 'AGENT_A', 'l errore dice chi lo tiene');
$l3 = bus_lock('AGENT_A', 'app/pagamenti.php', 1);
t_ok($l3['ok'], 'chi ha gia il lock puo rinnovarlo');
t_ok(bus_unlock('AGENT_A', 'app/pagamenti.php')['ok'], 'il lock si rilascia');
t_ok(!bus_unlock('AGENT_A', 'app/pagamenti.php')['ok'], 'rilasciare due volte non inventa un successo');
$scaduto = ['id' => 'x', 'file' => 'a.php', 'owner' => 'AGENT_A',
            'acquired_at' => '2020-01-01T00:00:00Z', 'expires_at' => '2020-01-01T01:00:00Z', 'status' => 'ACTIVE'];
t_ok(bus_lock_expired($scaduto), 'un lock scaduto e riconosciuto come tale');

t_group('bus — anomalie');
$a = bus_message('AGENT_A', 'AGENT_A', 'TASK', 'toccare il file condiviso', ['files' => ['shared/x.php']]);
$b = bus_message('AGENT_B', 'AGENT_B', 'TASK', 'toccare anche io il file condiviso', ['files' => ['shared/x.php']]);
bus_append($a); bus_append($b);
$msgs3 = bus_read();
$conf = bus_conflicts($msgs3, bus_read_locks());
t_gt(count($conf), 0, 'due task su due agenti diversi sullo stesso file = conflitto');
t_eq($conf[0]['kind'], 'TASK_FILE', 'il conflitto e classificato');

$dup1 = bus_message('AGENT_A', 'AGENT_B', 'TASK', 'Stesso identico titolo!');
$dup2 = bus_message('HUMAN', 'AGENT_B', 'TASK', 'stesso identico titolo');
bus_append($dup1); bus_append($dup2);
t_gt(count(bus_duplicates(bus_read())), 0, 'i doppioni si riconoscono dal titolo normalizzato');
t_eq(count(bus_orphans(bus_read(), 24)), 0, 'niente orfani: i task sono appena stati creati');
t_gt(count(bus_orphans(bus_read(), 0)), 0, 'con soglia zero tutti i task senza risposta risultano orfani');

t_group('bus — dashboard, brief, decisioni');
$d = bus_dashboard();
t_ok($d['ok'], 'la dashboard viene generata');
t_ok(is_file($d['dashboard']), 'DASHBOARD.md esiste su disco');
t_contains((string)file_get_contents($d['dashboard']), 'Task attivi', 'la dashboard elenca i task');
$brief = bus_brief('AGENT_B', bus_read(), bus_read_locks());
t_contains($brief, 'AGENT_B', 'il brief e intestato all agente');
t_contains($brief, 'Cosa hai in mano', 'il brief dice cosa deve fare');
t_ok(is_file(bus_path('BRIEF_AGENT_A.md')), 'i brief vengono scritti per ogni attore');
$dec = bus_decision('HUMAN', 'si usa SQLite in sviluppo e MySQL in produzione', 'motivo: stesso codice, zero servizi');
t_ok($dec['ok'], 'la decisione viene registrata');
t_contains((string)file_get_contents(bus_path('DECISIONS.md')), 'SQLite', 'la decisione finisce in DECISIONS.md');
t_ok(bus_rotate(100000)['ok'], 'la rotazione dei log gira senza errori');
