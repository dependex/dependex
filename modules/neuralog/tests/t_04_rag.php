<?php
/* Recupero, muro di visibilita', rerank, contesto, prompt, memoria, apprendimento. */

t_group('recupero');
brain_ingest_text("Il listino del prodotto Alfa parte da 249 euro IVA esclusa. Lo sconto quantita parte da cinque pezzi.",
    ['path' => 'rag/listino.md', 'title' => 'Listino', 'source' => 'test', 'visibility' => 'public']);
brain_ingest_text("Gli uffici aprono dalle 9:00 alle 18:00 dal lunedi al venerdi. Il sabato e chiuso.",
    ['path' => 'rag/orari.md', 'title' => 'Orari', 'source' => 'test', 'visibility' => 'public']);
brain_ingest_text("Nota riservata: il margine sul prodotto Alfa e del quaranta per cento.",
    ['path' => 'rag/margini.md', 'title' => 'Margini', 'source' => 'test', 'visibility' => 'admin']);

$pub = brain_retrieve('quanto costa il prodotto alfa', ['admin' => false, 'n' => 5]);
t_gt(count($pub), 0, 'la ricerca pubblica trova qualcosa');
t_contains((string)($pub[0]['content'] ?? ''), '249', 'il primo risultato contiene la risposta');
foreach ($pub as $r) {
    if ($r['visibility'] !== 'public') { t_failed('muro pubblico', 'e uscito un nodo ' . $r['visibility']); break; }
}
t_ok(true, 'la ricerca pubblica non restituisce nodi riservati');

$adm = brain_retrieve('margine sul prodotto alfa', ['admin' => true, 'n' => 5]);
$found = false;
foreach ($adm as $r) { if ($r['path'] === 'rag/margini.md') { $found = true; break; } }
t_ok($found, 'la ricerca admin vede anche i nodi riservati');
$pub2 = brain_retrieve('margine sul prodotto alfa', ['admin' => false, 'n' => 5]);
$leak = false;
foreach ($pub2 as $r) { if ($r['path'] === 'rag/margini.md') { $leak = true; break; } }
t_ok(!$leak, 'la stessa domanda dal pubblico non tira fuori la nota riservata');

t_ok(count(brain_retrieve('', ['admin' => true, 'n' => 3])) >= 0, 'domanda vuota non rompe niente');
$hub = brain_retrieve('concetto', ['admin' => true, 'n' => 10]);
$hubLeak = false;
foreach ($hub as $r) { if (($r['section'] ?? '') === 'hub' || ($r['section'] ?? '') === 'concept') { $hubLeak = true; } }
t_ok(!$hubLeak, 'hub e nodi-concetto restano fuori dalle risposte');

t_group('muro anti-fuga');
brain_node_put(['id' => 'leak-1', 'content' => 'La private key del server e AAA-BBB-CCC', 'path' => 'rag/leak.md',
                'visibility' => 'public', 'source' => 'test']);
$res = brain_retrieve('private key del server', ['admin' => false, 'n' => 5]);
$leaked = false;
foreach ($res as $r) { if ($r['id'] === 'leak-1') { $leaked = true; } }
t_ok(!$leaked, 'un segreto finito per errore in un nodo pubblico non esce comunque');
$resAdm = brain_retrieve('private key del server', ['admin' => true, 'n' => 5]);
$seen = false;
foreach ($resAdm as $r) { if ($r['id'] === 'leak-1') { $seen = true; } }
t_ok($seen, 'in area riservata il nodo si vede (serve per poterlo correggere)');

t_group('rerank');
$rows = [
    ['id' => 'a', 'content' => 'testo generico', 'score' => 1.0, 'feedback_score' => 0, 'updated_at' => brain_now()],
    ['id' => 'b', 'content' => 'la frase esatta cercata', 'score' => 1.0, 'feedback_score' => 0, 'updated_at' => brain_now()],
];
brain_rerank($rows, 'la frase esatta cercata');
t_eq($rows[0]['id'], 'b', 'il match di frase esatta sale in cima');
$rows2 = [
    ['id' => 'a', 'content' => 'x', 'score' => 1.0, 'feedback_score' => -5, 'updated_at' => brain_now()],
    ['id' => 'b', 'content' => 'x', 'score' => 1.0, 'feedback_score' => 5, 'updated_at' => brain_now()],
];
brain_rerank($rows2, 'q');
t_eq($rows2[0]['id'], 'b', 'il feedback della community sposta l ordine');
$delta = abs($rows2[0]['rerank_bonus'] - $rows2[1]['rerank_bonus']);
t_ok($delta <= 2 * (float)brain_cfg('rag.feedback_clamp', 2.0) + 0.01, 'il feedback resta clampato: nessuno ribalta il motore a furia di clic');

t_group('contesto e prompt');
$block = brain_context_block($pub);
t_contains($block, '[', 'ogni riga del contesto porta la sua fonte');
$srcs = brain_context_sources($pub);
t_ok(isset($srcs[0]['node_id']), 'le fonti sono strutturate (servono ai pulsanti di voto)');
$prompt = brain_build_prompt($block, ['admin' => false]);
t_contains($prompt, 'CONTESTO', 'il prompt contiene il blocco di contesto');
t_contains($prompt, 'NON inventare', 'il prompt vieta di inventare');
t_contains($prompt, 'CITA la fonte', 'il prompt chiede di citare la fonte');
t_contains(brain_build_prompt('', []), 'Nessun contenuto pertinente', 'senza contesto il prompt lo dichiara');
$pack = brain_ask('quali sono gli orari', ['admin' => false]);
t_ok($pack['grounded'], 'la domanda con risposta risulta ancorata');
t_gt(count($pack['sources']), 0, 'il pacchetto porta le fonti');

t_group('memoria');
brain_memory_log('domanda pubblica di prova', 'risposta', true, 'public');
brain_memory_log('domanda riservata di prova', 'risposta', true, 'admin');
brain_memory_log('domanda senza risposta', '', false, 'public');
$rec = brain_recent_topics(5, false);
t_contains($rec, 'domanda pubblica', 'i temi recenti pubblici entrano nel prompt');
t_ok(mb_strpos($rec, 'domanda riservata') === false, 'le domande dell area riservata non finiscono nel prompt pubblico');
t_contains($rec, 'non istruzione', 'i temi recenti sono etichettati come dati, non come ordini');
$gaps = brain_memory_gaps(10);
t_gt(count($gaps), 0, 'le domande senza risposta finiscono nell elenco dei buchi');

t_group('apprendimento');
$long = str_repeat('Questa e una risposta ancorata e sufficientemente lunga da essere memorizzata. ', 2);
$id = brain_learn('come funziona la garanzia estesa', $long, true);
t_ok($id !== '', 'una risposta ancorata diventa un nodo');
$node = brain_node_get($id);
t_eq($node['review_state'], 'pending', 'nasce in attesa di revisione');
t_ok($node['visibility'] !== 'public', 'non nasce mai pubblico');
t_eq(brain_learn('q', $long, true), '', 'domanda troppo corta: non si impara');
t_eq(brain_learn('domanda lunga abbastanza', 'corta', true), '', 'risposta troppo corta: non si impara');
t_eq(brain_learn('domanda lunga abbastanza', $long, false), '', 'risposta non ancorata: non si impara');
t_eq(brain_learn('domanda con segreto', 'La private key e questa: ' . $long, true), '', 'una risposta che contiene un segreto non viene mai imparata');
t_gt(count(brain_learn_pending(10)), 0, 'i nodi appresi compaiono nella coda di revisione');
brain_learn_approve($id, true);
t_eq(brain_node_get($id)['visibility'], 'public', 'la promozione a pubblico e un gesto esplicito');
brain_learn_reject($id);
t_eq(brain_node_get($id), null, 'il rifiuto cancella il nodo');
