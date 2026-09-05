<?php
/* ============================================================================
   COMPANY BRAIN — ui/console.php
   La console dell'amministratore: contatori, diagnosi, banco di prova, ultime
   attivita', ricerca di prova, ingestione a lotti, revisione di cio' che il
   cervello ha imparato dalle conversazioni.
   SOLO admin. Le azioni che scrivono passano in POST.
============================================================================ */
require_once __DIR__ . '/_ui.php';
require_once dirname(__DIR__) . '/ingest/demo.php';

if (!brain_is_admin()) {
    brain_ui_headers();
    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><style>' . brain_base_css() . '</style>'
       . '<div class="wrap"><div class="card"><h1>Accesso riservato</h1>'
       . '<p class="mut">Serve la chiave amministratore. Impostala nell\'ambiente come <code>'
       . brain_e((string)brain_cfg('security.admin_key_env', 'BRAIN_ADMIN_KEY'))
       . '</code> e richiama questa pagina con <code>?key=...</code>, oppure definisci nell\'applicazione ospite '
       . '<code>brain_host_is_admin()</code>.</p></div></div>';
    exit;
}
brain_ui_headers();

$keyQs = brain_key_qs();
$msg = '';
$searchRows = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    switch ($action) {
        case 'install':   $r = brain_install();      $msg = 'Schema installato (v' . ($r['schema_version'] ?? '?') . '), ' . count($r['tables']) . ' tabelle.'; break;
        case 'ingest':    $r = brain_ingest_run(['batch' => (int)($_POST['batch'] ?? 20)]);
                          $msg = 'Ingestione: ' . $r['processed'] . ' file, ' . $r['nodes'] . ' nodi, ' . $r['links'] . ' sinapsi, restano ' . $r['remaining'] . '.'; break;
        case 'health':    $r = brain_health(!empty($_POST['fix'])); $msg = 'Diagnosi eseguita' . (!empty($_POST['fix']) ? ' con riparazione.' : '.'); break;
        case 'eval':      $r = brain_eval_run(); $msg = 'Banco di prova: hit-rate ' . ($r['hit_rate'] ?? '?') . ', MRR ' . ($r['mrr'] ?? '?') . '.'; break;
        case 'reconcile': $r = brain_reconcile(!empty($_POST['fix'])); $msg = 'Riconciliazione: ' . json_encode($r['summary'] ?? []); break;
        case 'demo':      $r = brain_demo_seed(); $msg = 'Dati di prova seminati: ' . $r['nodes_created'] . ' neuroni.'; break;
        case 'demo-clear':$r = brain_demo_clear(); $msg = 'Dati di prova rimossi: ' . $r['removed'] . ' neuroni.'; break;
        case 'approve':   brain_learn_approve((string)($_POST['id'] ?? ''), !empty($_POST['public'])); $msg = 'Nodo approvato.'; break;
        case 'reject':    brain_learn_reject((string)($_POST['id'] ?? '')); $msg = 'Nodo rifiutato.'; break;
        case 'publish':   brain_node_set_visibility((string)($_POST['id'] ?? ''), 'public'); $msg = 'Nodo reso pubblico.'; break;
    }
}
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') { $searchRows = brain_retrieve($q, ['admin' => true, 'n' => 10]); }

$stats  = brain_stats();
$health = brain_health(false);
$counts = $stats['counts'];
$lastEval = $stats['last_eval'][0] ?? null;
$pending = brain_learn_pending(10);
$gaps = brain_memory_gaps(8);
$label = (string)brain_cfg('ui.brand_label', 'Company Brain');
?><!doctype html>
<html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= brain_e($label) ?> — console</title>
<style><?= brain_base_css() ?></style>
</head><body>
<div class="wrap">

  <div class="row" style="justify-content:space-between;margin-bottom:14px">
    <h1><?= brain_e($label) ?> <span class="mut" style="font-weight:400">console</span></h1>
    <div class="row">
      <span class="pill"><span class="dot"></span> <?= brain_e($stats['driver']) ?></span>
      <span class="pill">schema v<?= (int)$stats['schema_version'] ?></span>
      <span class="pill">modulo <?= brain_e($stats['version']) ?></span>
      <a class="btn" href="brain-3d.php<?= brain_e($keyQs) ?>">cervello 3D</a>
      <a class="btn" href="graph-2d.php<?= brain_e($keyQs) ?>">grafo 2D</a>
    </div>
  </div>

  <?php if ($msg !== ''): ?><div class="card"><b class="ok">✓</b> <?= brain_e($msg) ?></div><?php endif; ?>

  <div class="grid g4">
    <div class="card"><div class="k">neuroni</div><div class="kpi"><?= number_format($counts['nodes'], 0, ',', '.') ?></div></div>
    <div class="card"><div class="k">sinapsi</div><div class="kpi"><?= number_format($counts['links'], 0, ',', '.') ?></div></div>
    <div class="card"><div class="k">documenti</div><div class="kpi"><?= number_format($counts['files'], 0, ',', '.') ?></div></div>
    <div class="card"><div class="k">entita'</div><div class="kpi"><?= number_format($counts['entities'], 0, ',', '.') ?></div></div>
    <div class="card"><div class="k">conversazioni</div><div class="kpi"><?= number_format($counts['chat_log'], 0, ',', '.') ?></div></div>
    <div class="card"><div class="k">voti</div><div class="kpi"><?= number_format($counts['feedback'], 0, ',', '.') ?></div></div>
    <div class="card"><div class="k">hit-rate</div><div class="kpi"><?= $lastEval ? brain_e($lastEval['hit_rate']) : '—' ?></div>
      <div class="mut" style="font-size:11px"><?= $lastEval ? 'MRR ' . brain_e($lastEval['mrr']) . ' · ' . brain_e($lastEval['ran_at']) : 'mai eseguito' ?></div></div>
    <div class="card"><div class="k">da rivedere</div><div class="kpi"><?= (int)$stats['pending_learned'] ?></div>
      <div class="mut" style="font-size:11px">nodi appresi in attesa</div></div>
  </div>

  <div class="grid g2">

    <div class="card">
      <h2>Azioni</h2>
      <form method="post" class="row" style="gap:8px">
        <input type="hidden" name="action" value="ingest">
        <input type="number" name="batch" value="20" min="1" max="200" style="width:90px" title="file per lotto">
        <button type="submit">digerisci un lotto</button>
      </form>
      <div class="row" style="margin-top:8px">
        <form method="post"><input type="hidden" name="action" value="health"><button>diagnosi</button></form>
        <form method="post"><input type="hidden" name="action" value="health"><input type="hidden" name="fix" value="1"><button>diagnosi + riparazione</button></form>
        <form method="post"><input type="hidden" name="action" value="eval"><button>banco di prova</button></form>
        <form method="post"><input type="hidden" name="action" value="reconcile"><button>riconcilia</button></form>
        <form method="post"><input type="hidden" name="action" value="demo"><button>semina dati di prova</button></form>
        <form method="post"><input type="hidden" name="action" value="demo-clear"><button>togli dati di prova</button></form>
        <form method="post"><input type="hidden" name="action" value="install"><button>reinstalla schema</button></form>
      </div>
      <p class="mut" style="font-size:12px;margin:10px 0 0">Le stesse cose da riga di comando:
        <code>php bin/brain ingest --all</code> · <code>health --fix</code> · <code>eval</code>.</p>
    </div>

    <div class="card">
      <h2>Diagnosi del grafo</h2>
      <table>
        <?php foreach (($health['diagnosis'] ?? []) as $k => $v): ?>
        <tr><td class="mut"><?= brain_e($k) ?></td>
            <td style="text-align:right"><b class="<?= $v > 0 ? ($k === 'orphans' ? 'warn' : 'err') : 'ok' ?>"><?= (int)$v ?></b></td></tr>
        <?php endforeach; ?>
      </table>
      <h2 style="margin-top:14px">Muro di visibilita'</h2>
      <table>
        <?php foreach (($health['by_visibility'] ?? []) as $k => $v): ?>
        <tr><td class="mut"><?= brain_e($k) ?></td><td style="text-align:right"><b><?= (int)$v ?></b></td></tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="card">
      <h2>Prova una domanda</h2>
      <form method="get" class="row">
        <?php if ($keyQs !== ''): ?><input type="hidden" name="key" value="<?= brain_e($_GET['key'] ?? '') ?>"><?php endif; ?>
        <input name="q" value="<?= brain_e($q) ?>" placeholder="es. quali sono gli orari" style="flex:1;min-width:200px">
        <button type="submit">cerca</button>
      </form>
      <?php if ($q !== ''): ?>
      <table style="margin-top:10px">
        <tr><th>#</th><th>fonte</th><th class="nw">vis.</th><th class="nw">punteggio</th></tr>
        <?php foreach ($searchRows as $i => $r): ?>
        <tr><td><?= $i + 1 ?></td>
            <td><?= brain_e($r['path'] ?: $r['id']) ?><div class="mut" style="font-size:11px"><?= brain_e(brain_cut(preg_replace('/\s+/', ' ', (string)$r['content']), 120)) ?></div></td>
            <td class="nw"><?= brain_e($r['visibility']) ?></td>
            <td class="nw"><?= number_format((float)$r['score'], 2) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$searchRows): ?><tr><td colspan="4" class="mut">nessun risultato</td></tr><?php endif; ?>
      </table>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2>Ultime attivita'</h2>
      <table>
        <?php foreach (brain_activity_recent(10) as $a): ?>
        <tr><td class="mut" style="white-space:nowrap"><?= brain_e($a['created_at']) ?></td>
            <td><b><?= brain_e($a['kind']) ?></b> <span class="mut"><?= brain_e(brain_cut((string)$a['detail'], 110)) ?></span></td></tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="card">
      <h2>Imparato dalle conversazioni (da rivedere)</h2>
      <p class="mut" style="font-size:12px;margin-top:0">Niente di tutto questo e' visibile al pubblico finche' non lo approvi tu.</p>
      <table>
        <?php foreach ($pending as $p): ?>
        <tr>
          <td><?= brain_e(brain_cut((string)$p['title'], 90)) ?>
              <div class="mut" style="font-size:11px"><?= brain_e(brain_cut((string)$p['content'], 150)) ?></div></td>
          <td style="white-space:nowrap">
            <form method="post" style="display:inline"><input type="hidden" name="action" value="approve">
              <input type="hidden" name="id" value="<?= brain_e($p['id']) ?>"><button>approva</button></form>
            <form method="post" style="display:inline"><input type="hidden" name="action" value="approve">
              <input type="hidden" name="id" value="<?= brain_e($p['id']) ?>"><input type="hidden" name="public" value="1"><button>+ pubblica</button></form>
            <form method="post" style="display:inline"><input type="hidden" name="action" value="reject">
              <input type="hidden" name="id" value="<?= brain_e($p['id']) ?>"><button>rifiuta</button></form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$pending): ?><tr><td class="mut">niente in attesa</td></tr><?php endif; ?>
      </table>
    </div>

    <div class="card">
      <h2>Dove manca conoscenza</h2>
      <p class="mut" style="font-size:12px;margin-top:0">Domande a cui il cervello non ha saputo rispondere con una fonte.</p>
      <table>
        <?php foreach ($gaps as $g): ?>
        <tr><td><?= brain_e(brain_cut((string)$g['question'], 120)) ?></td>
            <td class="mut" style="white-space:nowrap"><?= brain_e($g['created_at']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$gaps): ?><tr><td class="mut">nessun buco registrato</td></tr><?php endif; ?>
      </table>
    </div>

  </div>

  <p class="mut" style="font-size:12px">Company Brain <?= brain_e(brain_version()) ?> · nessun dato di progetto nel motore: tutto sta in <code>config/</code>.</p>
</div>
</body></html>
