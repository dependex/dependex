<?php
/* ============================================================================
   COMPANY BRAIN — install.php
   Installazione dal browser, per chi su un hosting condiviso non ha la shell.
   Serve comunque la chiave amministratore (variabile d'ambiente): un
   installatore aperto a tutti sarebbe una porta sul database.
   Da riga di comando il modo giusto resta:  php bin/brain install
============================================================================ */
require_once __DIR__ . '/ui/_ui.php';

if (!brain_is_admin()) {
    brain_ui_headers();
    http_response_code(403);
    $env = brain_e((string)brain_cfg('security.admin_key_env', 'BRAIN_ADMIN_KEY'));
    echo '<!doctype html><meta charset="utf-8"><style>' . brain_base_css() . '</style><div class="wrap"><div class="card">'
       . '<h1>Installazione — accesso riservato</h1>'
       . '<p>Imposta la chiave nell\'ambiente e richiama questa pagina con <code>?key=...</code>:</p>'
       . '<pre>export ' . $env . '="una-chiave-lunga-e-casuale"</pre>'
       . '<p class="mut">Oppure, molto meglio, dalla riga di comando:</p><pre>php bin/brain install</pre>'
       . '</div></div>';
    exit;
}
brain_ui_headers();

$did = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$res = $did ? brain_schema_install() : null;
$seed = ($did && !empty($_POST['demo']));
if ($seed) { require_once __DIR__ . '/ingest/demo.php'; $seedRes = brain_demo_seed(); }

$checks = [
    ['PHP >= 8.1', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION],
    ['estensione PDO', extension_loaded('pdo'), ''],
    ['estensione mbstring', extension_loaded('mbstring'), ''],
    ['driver database', brain_driver() !== '', brain_driver()],
    ['cartella dati scrivibile', is_writable(brain_data_dir()), brain_data_dir()],
    ['ZipArchive (docx/xlsx)', class_exists('ZipArchive'), 'facoltativa'],
    ['pdftotext (PDF)', trim((string)@shell_exec('command -v pdftotext 2>/dev/null')) !== '', 'facoltativo'],
];
$keyQs = brain_key_qs();
?><!doctype html><html lang="it"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title>Company Brain — installazione</title><style><?= brain_base_css() ?></style></head><body>
<div class="wrap">
  <h1>Company Brain <?= brain_e(brain_version()) ?> — installazione</h1>

  <?php if ($res): ?>
    <div class="card">
      <h2>Risultato</h2>
      <p class="<?= $res['ok'] ? 'ok' : 'err' ?>"><?= $res['ok'] ? 'Schema installato senza errori.' : 'Installazione incompleta.' ?></p>
      <p class="mut">tabelle: <?= count($res['tables']) ?> · indici: <?= (int)$res['indexes'] ?> · versione schema: <?= (int)$res['schema_version'] ?></p>
      <?php if (!empty($res['errors'])): ?><pre><?= brain_e(implode("\n", $res['errors'])) ?></pre><?php endif; ?>
      <?php if ($seed): ?><p class="ok">Dati di prova seminati: <?= (int)$seedRes['nodes_created'] ?> neuroni, <?= (int)$seedRes['links_created'] ?> sinapsi.</p><?php endif; ?>
      <p><a class="btn" href="ui/console.php<?= brain_e($keyQs) ?>">vai alla console</a>
         <a class="btn" href="ui/brain-3d.php<?= brain_e($keyQs) ?>">guarda il cervello</a></p>
    </div>
  <?php endif; ?>

  <div class="card">
    <h2>Controlli d'ambiente</h2>
    <table>
      <?php foreach ($checks as [$name, $ok, $note]): ?>
      <tr><td><?= brain_e($name) ?></td>
          <td class="<?= $ok ? 'ok' : 'warn' ?>"><?= $ok ? 'ok' : 'assente' ?></td>
          <td class="mut"><?= brain_e($note) ?></td></tr>
      <?php endforeach; ?>
    </table>
  </div>

  <div class="card">
    <h2>Installa</h2>
    <p class="mut">Reinstallare e' sempre sicuro: lo schema e' idempotente e non cancella niente.</p>
    <form method="post">
      <?php if ($keyQs !== ''): ?><input type="hidden" name="key" value="<?= brain_e($_GET['key'] ?? '') ?>"><?php endif; ?>
      <label class="row" style="margin-bottom:10px">
        <input type="checkbox" name="demo" value="1" style="width:auto"> semina anche ~200 neuroni finti, per vedere subito il cervello funzionare
      </label>
      <button type="submit">installa lo schema</button>
    </form>
  </div>

  <div class="card">
    <h2>Poi</h2>
    <ol class="mut">
      <li>Scrivi il tuo <code>config/brain.local.json</code> (sinonimi, dizionario, cartelle da ingerire).</li>
      <li>Metti i documenti in <code><?= brain_e((string)brain_cfg('paths.inbox_dir', 'data/inbox')) ?></code> e lancia <code>php bin/brain ingest --all</code>.</li>
      <li>Controlla con <code>php bin/brain health</code> e misura con <code>php bin/brain eval</code>.</li>
      <li>Promuovi a pubblico solo i nodi che vuoi far vedere: nascono tutti riservati.</li>
    </ol>
  </div>
</div>
</body></html>
