<?php
/* ============================================================================
   ESEMPIO 2 — il cervello da solo, senza nessuna applicazione intorno.
   Copia questo file nella cartella che sta sopra company-brain/ e aprilo con
   il browser: una pagina di ricerca sulla conoscenza pubblica, in 60 righe.
   Per provarlo subito:
       php bin/brain install && php bin/brain demo-seed
       php -S 127.0.0.1:8080
============================================================================ */
require_once __DIR__ . '/../brain.php';

if (!brain_schema_ready()) {
    exit('Prima installa: php bin/brain install');
}

$q = trim((string)($_GET['q'] ?? ''));
$rows = $q !== '' ? brain_search($q, ['admin' => false, 'n' => 6]) : [];
$e = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Cerca nella nostra conoscenza</title>
<style>
 body{font:16px/1.6 system-ui,sans-serif;max-width:720px;margin:40px auto;padding:0 16px;background:#111;color:#eee}
 input{width:70%;padding:10px;font:inherit;border-radius:8px;border:1px solid #444;background:#191919;color:#eee}
 button{padding:10px 16px;font:inherit;border-radius:8px;border:1px solid #444;background:#222;color:#eee;cursor:pointer}
 .r{border-top:1px solid #333;padding:14px 0}
 .s{font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#888}
</style></head><body>

<h1>Cerca nella nostra conoscenza</h1>
<form method="get">
  <input name="q" value="<?= $e($q) ?>" placeholder="scrivi una domanda" autofocus>
  <button type="submit">cerca</button>
</form>

<?php if ($q !== ''): ?>
  <?php if (!$rows): ?>
    <p>Su questo non risulta niente. Prova con altre parole, oppure scrivici.</p>
  <?php else: ?>
    <p class="s"><?= count($rows) ?> risultati</p>
    <?php foreach ($rows as $r): ?>
      <div class="r">
        <div class="s"><?= $e(brain_source_label($r)) ?></div>
        <div><?= $e(brain_cut(preg_replace('/\s+/u', ' ', (string)$r['content']), 400)) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
<?php endif; ?>

<p class="s" style="margin-top:40px">
  Vengono mostrati solo i contenuti marcati come pubblici.
  Nessuna risposta e' generata: quello che leggi e' scritto nei nostri documenti.
</p>
</body></html>
