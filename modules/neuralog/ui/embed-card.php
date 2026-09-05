<?php
/* ============================================================================
   COMPANY BRAIN — ui/embed-card.php
   Una scheda piccola da mettere in un iframe dentro qualunque pagina: cerca
   nella conoscenza PUBBLICA e mostra le fonti, con i pulsanti di voto.
   Non chiama nessun modello: mostra quello che il cervello sa, con la fonte.
   Parametri: ?q=... (precompila)  &title=... (intestazione)
============================================================================ */
require_once __DIR__ . '/_ui.php';

$admin = brain_is_admin();
if (!$admin && !brain_public_api_enabled()) { http_response_code(403); exit('non disponibile'); }
brain_ui_headers();
$q = trim((string)($_GET['q'] ?? ''));
$title = trim((string)($_GET['title'] ?? (string)brain_cfg('ui.brand_label', 'Cerca nella conoscenza')));
$rows = $q !== '' ? brain_retrieve($q, ['admin' => $admin, 'n' => 5]) : [];
?><!doctype html>
<html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= brain_e($title) ?></title>
<style><?= brain_base_css() ?>
body{background:transparent}
.wrap{padding:12px;max-width:640px}
.res{border-top:1px solid var(--brain-line);padding:10px 0}
.res:first-of-type{border-top:0}
.src{font-size:11px;color:var(--brain-muted);text-transform:uppercase;letter-spacing:.6px}
.vote{border:0;background:none;color:var(--brain-muted);cursor:pointer;font-size:12px;padding:2px 6px;width:auto}
.vote:hover{color:var(--brain-accent)}
</style></head><body>
<div class="wrap">
  <div class="card">
    <form method="get" class="row">
      <input name="q" value="<?= brain_e($q) ?>" placeholder="<?= brain_e($title) ?>" style="flex:1">
      <button type="submit">cerca</button>
    </form>
    <?php if ($q !== ''): ?>
      <?php if (!$rows): ?>
        <p class="mut" style="margin:12px 0 0">Non risulta niente su questo. <?php
          $c = (string)brain_cfg('brain.public_contact', '');
          if ($c !== '') { echo 'Scrivi a <b>' . brain_e($c) . '</b>.'; } ?></p>
      <?php else: foreach ($rows as $r): ?>
        <div class="res">
          <div class="src"><?= brain_e(brain_source_label($r)) ?></div>
          <div><?= brain_e(brain_cut(preg_replace('/\s+/u', ' ', (string)$r['content']), 320)) ?></div>
          <div class="row" style="margin-top:6px">
            <button class="vote" data-n="<?= brain_e($r['id']) ?>" data-v="1">utile</button>
            <button class="vote" data-n="<?= brain_e($r['id']) ?>" data-v="-1">non utile</button>
            <span class="mut" style="font-size:11px" id="fb-<?= brain_e($r['id']) ?>"></span>
          </div>
        </div>
      <?php endforeach; endif; ?>
    <?php endif; ?>
  </div>
</div>
<script>
document.querySelectorAll('.vote').forEach(b=>b.addEventListener('click',async ()=>{
  const id=b.dataset.n, v=b.dataset.v, out=document.getElementById('fb-'+id);
  try{
    const r=await fetch('../api/v1/feedback.php',{method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'node_id='+encodeURIComponent(id)+'&vote='+encodeURIComponent(v)+'&question='+encodeURIComponent(<?= json_encode($q) ?>)});
    const d=await r.json();
    out.textContent = d.ok ? (d.already_voted ? 'gia\' votato oggi' : 'grazie') : (d.error||'errore');
  }catch(e){ out.textContent='errore di rete'; }
}));
</script>
</body></html>
