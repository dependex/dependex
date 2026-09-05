<?php
/* Database, schema, neuroni, sinapsi, entita', hub. */

t_group('database e schema');
$r = brain_schema_install();
t_ok($r['ok'], 'installazione dello schema senza errori');
t_eq($r['schema_version'], BRAIN_SCHEMA_VERSION, 'versione dello schema registrata');
$r2 = brain_schema_install();
t_ok($r2['ok'], 'reinstallare e sicuro (idempotente)');
t_ok(brain_schema_ready(), 'lo schema risulta pronto');
t_ok(brain_has_table(brain_t('nodes')), 'la tabella dei nodi esiste');
t_ok(!brain_has_table(brain_t('tabella_che_non_esiste')), 'una tabella inesistente e riconosciuta come tale');
t_gt(count(brain_table_columns(brain_t('nodes'))), 10, 'la tabella nodi ha tutte le colonne');

brain_meta_set('prova', 'valore');
t_eq(brain_meta_get('prova'), 'valore', 'meta: scrittura e rilettura');
brain_meta_set('prova', 'nuovo');
t_eq(brain_meta_get('prova'), 'nuovo', 'meta: sovrascrittura');
t_eq(brain_meta_get('mai_scritto', 'def'), 'def', 'meta: default');

t_group('neuroni');
$id = brain_node_put(['id' => 'test-1', 'content' => 'contenuto di prova', 'path' => 'test/uno.md',
                      'section' => 'document', 'visibility' => 'public', 'source' => 'test']);
t_eq($id, 'test-1', 'creazione nodo');
$n = brain_node_get('test-1');
t_eq($n['visibility'], 'public', 'visibilita salvata');
t_eq($n['path'], 'test/uno.md', 'percorso salvato');
brain_node_put(['id' => 'test-1', 'content' => 'contenuto aggiornato', 'path' => 'test/uno.md']);
t_contains((string)brain_node_get('test-1')['content'], 'aggiornato', 'aggiornamento nodo (upsert)');
t_eq(brain_node_get('mai-esistito'), null, 'nodo inesistente = null');
brain_node_set_visibility('test-1', 'admin');
t_eq(brain_node_get('test-1')['visibility'], 'admin', 'cambio di visibilita');
brain_node_set_visibility('test-1', 'inventata');
t_eq(brain_node_get('test-1')['visibility'], 'admin', 'una visibilita inventata non passa (si chiude, non si apre)');

t_group('sinapsi');
brain_node_put(['id' => 'test-2', 'content' => 'secondo nodo', 'path' => 'test/due.md']);
brain_node_put(['id' => 'test-3', 'content' => 'terzo nodo', 'path' => 'test/tre.md']);
t_eq(brain_link('test-2', 'test-1'), 1, 'prima sinapsi creata');
t_eq(brain_link('test-1', 'test-2'), 0, 'la stessa coppia al contrario non crea un doppione');
t_eq(brain_link('test-1', 'test-1'), 0, 'niente auto-sinapsi');
t_eq(brain_link('', 'test-1'), 0, 'id vuoto rifiutato');
$row = brain_rows('SELECT node_a, node_b FROM ' . brain_t('links') . " WHERE node_a='test-1' OR node_b='test-1'");
t_eq($row[0]['node_a'], 'test-1', 'la coppia e salvata in ordine canonico (a < b)');
brain_link('test-1', 'test-3');
t_eq(count(brain_neighbors('test-1', 10)), 2, 'i vicini si leggono nei due versi');
$deg = brain_degrees(['test-1', 'test-2']);
t_eq((int)$deg['test-1'], 2, 'grado del nodo calcolato');
t_eq(brain_link_count('test-1'), 2, 'conteggio sinapsi del nodo');

t_group('entita');
t_gt(count(brain_entity_dictionary()), 5, 'il dizionario si carica');
$e = brain_entities_extract('Il prodotto Alfa e la garanzia sono gestiti da Maria Rossi');
t_ok(in_array('prodotto alfa', $e, true), 'entita multi-parola riconosciuta');
t_ok(in_array('maria rossi', $e, true), 'nome proprio dal dizionario riconosciuto');
t_eq(brain_entities_extract('testo senza nessuna entita conosciuta qui'), [], 'nessun falso positivo');
$e2 = brain_entities_extract('parliamo del CUSTOMER CARE e della garanzia');
t_ok(in_array('assistenza', $e2, true), 'alias risolto sulla forma canonica');

brain_node_put(['id' => 'ent-a', 'content' => 'prodotto alfa con garanzia estesa', 'path' => 'test/ent-a.md']);
brain_node_put(['id' => 'ent-b', 'content' => 'la garanzia del prodotto alfa dura due anni', 'path' => 'test/ent-b.md']);
brain_entities_link('ent-a', 'prodotto alfa con garanzia estesa');
[$nEnt, $nLink] = brain_entities_link('ent-b', 'la garanzia del prodotto alfa dura due anni');
t_gt($nEnt, 1, 'piu entita estratte dal nodo');
t_eq($nLink, 1, 'due nodi con 2 entita in comune si collegano da soli');
brain_node_put(['id' => 'ent-c', 'content' => 'solo garanzia qui', 'path' => 'test/ent-c.md']);
[, $nLink2] = brain_entities_link('ent-c', 'solo garanzia qui');
t_eq($nLink2, 0, 'una sola entita in comune non basta (soglia di config)');

t_group('hub');
$hub = brain_hub_for('test');
t_eq($hub, 'hub-test', 'hub della fonte creato');
t_ok(in_array(BRAIN_HUB_ROOT, brain_neighbors($hub, 10), true), 'hub di fonte agganciato alla radice');
