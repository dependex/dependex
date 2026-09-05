<?php
/* Salute, banco di prova, feedback, riconciliazione, sicurezza. */

t_group('salute del grafo');
$h = brain_health(false);
t_ok($h['ok'], 'la diagnosi gira');
t_ok(isset($h['diagnosis']['orphans']), 'conta gli orfani');
t_ok(isset($h['diagnosis']['dangling']), 'conta le sinapsi pendenti');

/* si sporca il grafo di proposito, poi si controlla che la riparazione pulisca */
$pdo = brain_pdo();
$pdo->prepare('INSERT INTO ' . brain_t('links') . ' (node_a, node_b, kind, created_at) VALUES (?,?,?,?)')
    ->execute(['zzz-inesistente', 'test-1', 'manuale', brain_now()]);
$pdo->prepare('INSERT INTO ' . brain_t('links') . ' (node_a, node_b, kind, created_at) VALUES (?,?,?,?)')
    ->execute(['test-3', 'test-2', 'manuale', brain_now()]);   // capi invertiti (test-2 < test-3)
$pdo->prepare('INSERT INTO ' . brain_t('nodes') . ' (id, content, visibility, created_at) VALUES (?,?,?,?)')
    ->execute(['senza-muro', 'nodo senza visibilita', '', brain_now()]);

$h2 = brain_health(false);
t_gt($h2['diagnosis']['dangling'], 0, 'la sinapsi verso un nodo inesistente viene vista');
t_gt($h2['diagnosis']['non_canonical'], 0, 'la sinapsi con i capi invertiti viene vista');
t_gt($h2['diagnosis']['no_wall'], 0, 'il nodo senza visibilita viene visto');

$h3 = brain_health(true);
t_ok($h3['fixed'], 'la riparazione e stata eseguita');
t_eq($h3['diagnosis_after']['dangling'], 0, 'sinapsi pendenti rimosse');
t_eq($h3['diagnosis_after']['non_canonical'], 0, 'sinapsi rimesse in ordine canonico');
t_eq($h3['diagnosis_after']['no_wall'], 0, 'il muro viene applicato ai nodi scoperti');
t_eq(brain_node_get('senza-muro')['visibility'], 'admin', 'in caso di dubbio si chiude, non si apre');

t_group('banco di prova');
brain_ingest_text('Il prodotto Alfa costa 249 euro.', ['path' => 'eval/prezzo.md', 'source' => 'test', 'visibility' => 'public']);
$pdo->exec('DELETE FROM ' . brain_t('eval_questions'));
$pdo->prepare('INSERT INTO ' . brain_t('eval_questions') . ' (q, expected, tag, active, created_at) VALUES (?,?,?,1,?)')
    ->execute(['quanto costa il prodotto alfa', '249', 'test', brain_now()]);
$pdo->prepare('INSERT INTO ' . brain_t('eval_questions') . ' (q, expected, tag, active, created_at) VALUES (?,?,?,1,?)')
    ->execute(['domanda impossibile su un tema mai scritto', 'stringa-che-non-esiste-da-nessuna-parte', 'test', brain_now()]);
$e = brain_eval_run();
t_ok($e['ok'], 'il banco di prova gira');
t_eq($e['questions'], 2, 'due domande eseguite');
t_eq($e['hits'], 1, 'una trovata, una no: il numero e onesto');
t_eq($e['hit_rate'], 0.5, 'hit-rate calcolata');
t_gt($e['mrr'], 0, 'MRR-lite calcolata');
t_gt(count($e['trend']), 0, 'la tendenza viene salvata per il confronto nel tempo');
$pdo->exec('DELETE FROM ' . brain_t('eval_questions'));
t_gt(count(brain_eval_questions()), 5, 'senza domande in tabella si ricade sul file di benchmark');

t_group('feedback');
$v = brain_feedback_vote('test-1', 1, 'una domanda', '');
t_ok($v['ok'], 'il voto viene registrato');
t_eq($v['feedback_score'], 1, 'il punteggio del nodo sale');
$v2 = brain_feedback_vote('test-1', 1);
t_ok(!empty($v2['already_voted']), 'un secondo voto lo stesso giorno viene respinto (indice unico)');
t_ok(!brain_feedback_vote('nodo-inesistente', 1)['ok'], 'non si vota un nodo che non esiste');
t_ok(!brain_feedback_vote('test-1', 5)['ok'], 'sono ammessi solo +1 e -1');
brain_feedback_vote('test-2', -1, 'domanda', 'questa risposta e sbagliata, il valore giusto e un altro');
$list = brain_feedback_list(10);
t_gt(count($list), 0, 'le correzioni si leggono (solo lato admin)');
$hasCorrection = false;
foreach ($list as $f) { if (trim((string)$f['correction']) !== '') { $hasCorrection = true; } }
t_ok($hasCorrection, 'la correzione resta testo grezzo, non pubblicato da nessuna parte');
t_gt(count(brain_feedback_worst(5)), 0, 'i nodi bocciati sono elencabili');

t_group('riconciliazione');
$rc = brain_reconcile(false);
t_ok($rc['ok'], 'la riconciliazione gira');
t_ok(isset($rc['summary']['files']), 'produce un riepilogo');

t_group('sicurezza');
t_eq(brain_visibility_sql(false), "(visibility = 'public')", 'il muro pubblico e esplicito nel SQL');
t_eq(brain_visibility_sql(true), '1=1', 'in area riservata si vede tutto');
t_contains(brain_exclude_sql(), 'NOT LIKE', 'gli hub e i concetti sono esclusi via SQL');
putenv('BRAIN_ADMIN_KEY=');
t_eq(brain_admin_key(), '', 'senza variabile d ambiente non c e nessuna chiave');
putenv('BRAIN_ADMIN_KEY=chiave-di-prova');
t_eq(brain_admin_key(), 'chiave-di-prova', 'la chiave si legge solo dall ambiente');
putenv('BRAIN_ADMIN_KEY=');
t_ok(brain_ip_hash('x') !== (string)($_SERVER['REMOTE_ADDR'] ?? 'cli'), 'l impronta del chiamante non e l IP in chiaro');
t_eq(strlen(brain_ip_hash('x')), 24, 'impronta di lunghezza fissa');
t_ok(brain_ip_hash('a') !== brain_ip_hash('b'), 'impronte diverse per contesti diversi');
t_ok(brain_rate_limit('test-bucket', 5, 60), 'il rate limit lascia passare le prime richieste');
t_ok(brain_is_admin(), 'da riga di comando si e sempre admin (chi ha la shell ha gia tutto)');
