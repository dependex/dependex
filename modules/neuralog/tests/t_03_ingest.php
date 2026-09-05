<?php
/* Ingestione: testo programmatico, file, guardia segreti, idempotenza. */

t_group('ingestione di testo');
$txt = "Scheda tecnica del prodotto Beta.\nAlimentazione a 12 volt, peso 4 chili.\nGaranzia di 24 mesi.";
$r = brain_ingest_text($txt, ['path' => 'test/scheda-beta.md', 'title' => 'Scheda Beta', 'source' => 'test', 'visibility' => 'public']);
t_ok($r['ok'], 'ingestione riuscita');
t_gt($r['chunks'], 0, 'almeno un neurone creato');
t_gt($r['links'], 0, 'sinapsi automatiche create');
$r2 = brain_ingest_text($txt, ['path' => 'test/scheda-beta.md', 'title' => 'Scheda Beta', 'source' => 'test']);
t_ok($r2['skipped'], 'stesso contenuto, stesso percorso: non si rifa il lavoro');
$r3 = brain_ingest_text($txt . "\nAggiunta una riga.", ['path' => 'test/scheda-beta.md', 'source' => 'test', 'visibility' => 'public']);
t_ok(!$r3['skipped'], 'contenuto cambiato: si ridigerisce');
t_eq(count(brain_node_ids_by_path('test/scheda-beta.md')), $r3['chunks'], 'il reprocess non lascia nodi vecchi in giro');
t_ok(!brain_ingest_text('   ', ['path' => 'test/vuoto.md'])['ok'], 'testo vuoto rifiutato');

$before = brain_counts()['nodes'];
brain_forget_path('test/scheda-beta.md');
t_ok(brain_counts()['nodes'] < $before, 'dimenticare un percorso rimuove i suoi neuroni');
t_eq(brain_node_ids_by_path('test/scheda-beta.md'), [], 'nessun nodo residuo dopo forget');

t_group('guardia segreti e filtri');
t_ok(brain_is_secret_path('config/.env'), '.env riconosciuto come segreto');
t_ok(brain_is_secret_path('certs/server.pem'), '.pem riconosciuto come segreto');
t_ok(brain_is_secret_path('data/my-secret-notes.txt'), 'nome con "secret" riconosciuto');
t_ok(!brain_is_secret_path('docs/manuale.md'), 'un file normale non e segreto');
t_ok(brain_ingest_accepts('docs/manuale.md', ''), 'estensione ammessa dagli include');
t_ok(!brain_ingest_accepts('docs/foto.png', ''), 'estensione non ammessa scartata');
t_ok(!brain_ingest_accepts('vendor/libreria/file.php', ''), 'cartella esclusa scartata');
t_ok(brain_looks_secret('la mia private key e questa'), 'muro anti-fuga: pattern riconosciuto');
t_ok(!brain_looks_secret('la chiave di lettura del catalogo'), 'muro anti-fuga: nessun falso positivo su testo normale');
t_contains(brain_redact('api_key: 12345'), 'REDATTO', 'la redazione nasconde il segreto');

t_group('ingestione da filesystem');
$dir = brain_data_dir() . '/test-inbox';
@exec('rm -rf ' . escapeshellarg($dir));
@mkdir($dir, 0775, true);
file_put_contents($dir . '/manuale.md', "# Manuale\nIl prodotto Gamma si accende con il tasto laterale.\nLa manutenzione e annuale.");
file_put_contents($dir . '/dati.csv', "nome,valore\nsoglia,42\nlimite,99");
file_put_contents($dir . '/.env', "SECRET_KEY=non-deve-entrare");
file_put_contents($dir . '/foto.png', "\x89PNG finto");

$dry = brain_ingest_run(['roots' => [$dir], 'dry' => true]);
t_eq($dry['scanned'], 2, 'lo scanner vede solo i file ammessi (.env e .png esclusi)');
t_eq($dry['processed'], 0, 'in prova non si scrive niente');

$run = brain_ingest_run(['roots' => [$dir], 'batch' => 10]);
t_eq($run['processed'], 2, 'due file digeriti');
t_gt($run['nodes'], 1, 'neuroni creati dai file');
t_eq((int)brain_scalar('SELECT COUNT(*) FROM ' . brain_t('nodes') . " WHERE content LIKE '%non-deve-entrare%'", [], 0), 0,
     'il contenuto del .env non e mai entrato nel cervello');
$run2 = brain_ingest_run(['roots' => [$dir], 'batch' => 10]);
t_eq($run2['processed'], 0, 'seconda passata: niente da rifare (idempotenza per hash)');
t_gt((int)brain_scalar('SELECT COUNT(*) FROM ' . brain_t('files'), [], 0), 1, 'i file sono registrati');
t_gt((int)brain_scalar('SELECT COUNT(*) FROM ' . brain_t('knowledge'), [], 0), 1, 'la conoscenza e indicizzata');

t_group('estrattori di documenti');
t_contains(brain_html_to_text('<html><body><h1>Titolo</h1><script>var x=1</script><p>Testo &amp; prova</p></body></html>'),
           'Testo & prova', 'html: tag via, entita decodificate');
t_ok(mb_strpos(brain_html_to_text('<script>segreto()</script><p>ok</p>'), 'segreto') === false, 'html: gli script non entrano');
t_eq(brain_extract_text('/percorso/che/non/esiste.md'), '', 'file inesistente = stringa vuota, nessun errore');
if (class_exists('ZipArchive')) {
    $docx = brain_data_dir() . '/test.docx';
    $z = new ZipArchive();
    $z->open($docx, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $z->addFromString('word/document.xml', '<w:document><w:body><w:p><w:r><w:t>Contratto di fornitura</w:t></w:r></w:p></w:body></w:document>');
    $z->close();
    t_contains(brain_extract_text($docx, 'docx'), 'Contratto di fornitura', 'docx letto con ZipArchive nativa');
    @unlink($docx);
} else {
    t_pass('docx saltato: estensione zip assente su questa macchina');
}

t_group('ingestione da URL');
t_ok(!brain_url_allowed('https://example.com/pagina'), 'per difetto l\'ingestione da URL e spenta');
t_ok(!brain_ingest_url('https://example.com/x')['ok'], 'chiamare l\'URL spento non fa niente');
