<?php
/* Config, testo, chunker — la base su cui poggia tutto il resto. */

t_group('config');
t_eq(brain_cfg('db.table_prefix'), 'brain_', 'lettura chiave a punti');
t_eq(brain_cfg('non.esiste', 'x'), 'x', 'default su chiave assente');
t_ok(str_starts_with(brain_path('config'), BRAIN_ROOT), 'brain_path resta dentro il modulo');
t_eq(brain_path('/tmp/x'), '/tmp/x', 'brain_path lascia stare i percorsi assoluti');
t_gt(count(brain_json_file('config/brain.config.json')), 3, 'il file di config si legge');
t_ok(is_dir(brain_data_dir()), 'la cartella dati esiste o viene creata');

t_group('testo e lingua');
t_eq(brain_normalize('  Città   PERÒ  '), 'citta pero', 'minuscolo, accenti piegati, spazi compressi');
$tk = brain_tokens("l'orario dell'ufficio e' quello");
t_ok(in_array('orario', $tk, true), "l'elisione viene spezzata: orario trovato");
t_ok(!in_array('dell', $tk, true), 'i prefissi di elisione vengono scartati');
t_ok(!in_array('quello', $tk, true), 'le stopwords vengono scartate');
$terms = brain_query_terms('quanto costa il servizio');
t_ok(in_array('costo', $terms, true) || in_array('tariffa', $terms, true), 'espansione dai sinonimi di config');
t_ok(count(brain_query_terms(str_repeat('parola' . mt_rand() . ' ', 40))) <= (int)brain_cfg('rag.max_terms', 16), 'i termini sono limitati dalla config');
$kw = brain_keywords('garanzia garanzia garanzia fatturazione fatturazione spedizione');
t_eq($kw[0] ?? '', 'garanzia', 'la parola chiave piu frequente viene prima');
t_eq(brain_slug('Prodotto Alfa / 2026!'), 'prodotto-alfa-2026', 'slug pulito');
t_ok(mb_check_encoding(brain_clean_text("testo\x80 sporco"), 'UTF-8'), 'la pulizia restituisce UTF-8 valido');

t_group('chunker');
$long = str_repeat("riga di prova con abbastanza testo per riempire il chunk\n", 200);
$ch = brain_chunk($long, 500, 100, 10);
t_gt(count($ch), 1, 'il testo lungo viene spezzato');
t_ok(count($ch) <= 10, 'il tetto di chunk viene rispettato');
$overlapFound = false;
if (count($ch) > 1) {
    $tail = mb_substr($ch[0], -40);
    $overlapFound = mb_strpos($ch[1], mb_substr($tail, 0, 20)) !== false;
}
t_ok($overlapFound, 'i chunk si sovrappongono (niente concetti tagliati a meta)');
t_eq(brain_chunk('   '), [], 'testo vuoto = nessun chunk');
$mono = brain_chunk(str_repeat('parola ', 1000), 500, 50, 20);   // 7000 caratteri su UNA riga
t_gt(count($mono), 1, 'anche una riga unica gigante viene spezzata');
t_ok(mb_strlen($mono[0]) <= 750, 'nessun chunk esce dalla misura prevista');
t_eq(brain_clean_text(str_repeat('a', 400)), '', 'i blob senza separatori (base64/hex) vengono buttati');
