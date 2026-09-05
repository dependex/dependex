<?php
/* ============================================================================
   ADMIN — CHAT AI: FAQ & APPRENDIMENTO

   Da qui l'admin fa crescere la conoscenza della chat:
   - vede le domande utente NON risolte (chat_domande) e le "candidate FAQ"
     (domande ricorrenti aggregate);
   - promuove una domanda a FAQ (chat_faq) scrivendo la risposta;
   - rivede/attiva/disattiva le FAQ esistenti e ne vede gli hit.

   Sola gestione FAQ: nessuna azione che muove soldi o arma la macchina email.
   Solo admin. CSRF via dr-security.php (progressivo).
============================================================================ */
session_start();
require_once __DIR__.'/db.php';
require_once __DIR__.'/dr-chat-faq.php';
@require_once __DIR__.'/dr-security.php';
if (function_exists('dr_security_headers')) dr_security_headers();

$isAdmin = (($_SESSION['role']??'')==='admin') || !empty($_SESSION['is_admin']);
if(!$isAdmin){ header('Location: account.php'); exit; }

dr_faq_boot($pdo);

$csrfOk = function(){ return function_exists('dr_csrf_ok') ? dr_csrf_ok() : true; };
$msgFlash = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && $csrfOk()){
  $az = $_POST['azione'] ?? '';
  if ($az==='promuovi'){
    $domanda  = trim($_POST['domanda'] ?? '');
    $risposta = trim($_POST['risposta'] ?? '');
    $tag      = trim($_POST['tag'] ?? '');
    $cta      = in_array($_POST['cta'] ?? 'kit', ['kit','membership','nft'], true) ? $_POST['cta'] : 'kit';
    if ($domanda!=='' && $risposta!==''){
      $id = dr_faq_promote($pdo, $domanda, $risposta, $tag, $cta);
      $msgFlash = "FAQ #$id salvata e domande simili segnate come risolte.";
    } else {
      $msgFlash = "Servono sia la domanda sia la risposta.";
    }
  } elseif ($az==='toggle_faq'){
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE chat_faq SET attiva = CASE attiva WHEN 1 THEN 0 ELSE 1 END, updated=datetime('now') WHERE id=?")->execute([$id]);
    $msgFlash = "FAQ #$id aggiornata.";
  } elseif ($az==='scarta'){
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE chat_domande SET risolta=1 WHERE id=?")->execute([$id]);
    $msgFlash = "Domanda #$id archiviata (senza creare FAQ).";
  }
  header('Location: admin-chat.php?ok='.rawurlencode($msgFlash)); exit;
}
if (isset($_GET['ok'])) $msgFlash = $_GET['ok'];

$kpi       = dr_faq_kpi($pdo);
$candidate = dr_faq_candidate($pdo, 2);
$open      = $pdo->query("SELECT id,testo,uid,bot,data FROM chat_domande WHERE risolta=0 ORDER BY data DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
$faqs      = $pdo->query("SELECT id,slug,domanda,tag,cta,hit,attiva FROM chat_faq ORDER BY attiva DESC, hit DESC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$h = fn($s)=>htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?><!doctype html><html lang="it"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Chat AI — FAQ & Apprendimento</title>
<style>
body{margin:0;background:#0d0d0d;color:#eee;font-family:system-ui,Segoe UI,Roboto,sans-serif}
.wrap{max-width:1000px;margin:0 auto;padding:32px 18px}
h1{color:#D4AF37;font-size:1.5rem}h2{color:#D4AF37;font-size:1.1rem;margin-top:28px}
a.back{color:#c8a24a;text-decoration:none;font-size:.9rem}
.card{background:#151006;border:1px solid rgba(212,175,55,.35);border-radius:14px;padding:18px;margin:14px 0}
.kpi{display:flex;gap:20px;flex-wrap:wrap;color:#c9bd9e;font-size:.85rem}
.kpi b{color:#D4AF37;font-size:1.15rem;display:block}
table{width:100%;border-collapse:collapse;font-size:.85rem}
th,td{text-align:left;padding:8px 10px;border-bottom:1px solid rgba(212,175,55,.15);vertical-align:top}
th{color:#c8a24a;font-weight:700}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:700}
.on{background:rgba(58,208,122,.15);color:#3ad07a}.off{background:rgba(224,67,31,.15);color:#ffb199}
input,textarea,select{background:#0e0e0e;color:#fff;border:1px solid #333;border-radius:8px;padding:8px 10px;font-size:.85rem;width:100%;box-sizing:border-box}
textarea{min-height:70px;resize:vertical}
.btn{border:0;border-radius:20px;padding:8px 16px;font-weight:800;cursor:pointer;background:linear-gradient(135deg,#D4AF37,#b8942e);color:#160f00}
.btn.sm{padding:5px 12px;font-size:.78rem}
.btn.ghost{background:transparent;border:1px solid rgba(212,175,55,.5);color:#D4AF37}
.flash{background:rgba(58,208,122,.12);border:1px solid rgba(58,208,122,.4);color:#bfeecf;padding:10px 14px;border-radius:10px;margin:12px 0}
.muted{color:#9a8f78;font-size:.8rem}
.grid{display:grid;grid-template-columns:1fr;gap:10px}
label{display:block;font-size:.78rem;color:#c8a24a;margin:8px 0 3px}
form.inline{display:inline}
</style></head><body><div class="wrap">
<a class="back" href="dashboard-admin.php">← Dashboard admin</a>
<h1>💬 Chat AI — FAQ &amp; Apprendimento</h1>
<?php if($msgFlash): ?><div class="flash"><?=$h($msgFlash)?></div><?php endif; ?>

<div class="card"><div class="kpi">
  <div><b><?= (int)$kpi['faq_attive'] ?></b> FAQ attive</div>
  <div><b><?= (int)$kpi['domande_open'] ?></b> domande aperte</div>
  <div><b><?= count($candidate) ?></b> candidate FAQ</div>
</div><p class="muted">Le FAQ rispondono con numeri sempre aggiornati dalle fonti uniche (economia, cataloghi). Le domande senza risposta sicura vengono loggate qui: promuovile a FAQ e la chat impara.</p></div>

<h2>🔥 Candidate FAQ — domande ricorrenti</h2>
<div class="card">
<?php if(!$candidate): ?><p class="muted">Nessuna domanda ricorrente per ora. Cresceranno con l'uso.</p>
<?php else: ?>
<table><tr><th>#</th><th>Esempio domanda</th><th>Volte</th><th>Ultima</th><th></th></tr>
<?php foreach($candidate as $i=>$c): ?>
<tr>
  <td><?= $i+1 ?></td>
  <td><?=$h($c['esempio'])?></td>
  <td><span class="badge on"><?= (int)$c['n'] ?>×</span></td>
  <td class="muted"><?=$h($c['ultima'])?></td>
  <td><button class="btn sm ghost" type="button" onclick="prefill(<?=json_encode($c['esempio'])?>)">Promuovi ↓</button></td>
</tr>
<?php endforeach; endif; ?>
</table>
</div>

<h2>📝 Promuovi a FAQ</h2>
<div class="card">
<form method="post" class="grid">
  <?php if(function_exists('dr_csrf_field')) dr_csrf_field(); ?>
  <input type="hidden" name="azione" value="promuovi">
  <div><label>Domanda</label><input id="fDom" name="domanda" placeholder="Es. Posso pagare a rate?" required></div>
  <div><label>Risposta (puoi usare i segnaposto {DRX_GUADAGNO}, {NFT_LISTINO}, {KIT_LISTINO}…)</label><textarea name="risposta" placeholder="Scrivi la risposta del Branco…" required></textarea></div>
  <div><label>Tag / parole chiave (per il match)</label><input name="tag" placeholder="rate pagamento dilazione"></div>
  <div><label>CTA</label><select name="cta"><option value="kit">KIT (branco.html)</option><option value="membership">Membership</option><option value="nft">NFT</option></select></div>
  <div><button class="btn" type="submit">Salva FAQ</button></div>
</form>
</div>

<h2>📥 Domande utente da rivedere</h2>
<div class="card">
<?php if(!$open): ?><p class="muted">Nessuna domanda aperta.</p>
<?php else: ?>
<table><tr><th>#</th><th>Testo</th><th>Bot</th><th>Uid</th><th>Data</th><th></th></tr>
<?php foreach($open as $d): ?>
<tr>
  <td><?= (int)$d['id'] ?></td>
  <td><?=$h($d['testo'])?></td>
  <td class="muted"><?=$h($d['bot'])?></td>
  <td class="muted"><?= (int)$d['uid'] ?: '—' ?></td>
  <td class="muted"><?=$h($d['data'])?></td>
  <td>
    <button class="btn sm ghost" type="button" onclick="prefill(<?=json_encode($d['testo'])?>)">Promuovi</button>
    <form method="post" class="inline" onsubmit="return confirm('Archivio senza creare FAQ?')">
      <?php if(function_exists('dr_csrf_field')) dr_csrf_field(); ?>
      <input type="hidden" name="azione" value="scarta"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
      <button class="btn sm ghost" type="submit">Scarta</button>
    </form>
  </td>
</tr>
<?php endforeach; endif; ?>
</table>
</div>

<h2>📚 FAQ esistenti</h2>
<div class="card">
<table><tr><th>#</th><th>Domanda</th><th>Tag</th><th>CTA</th><th>Hit</th><th>Stato</th><th></th></tr>
<?php foreach($faqs as $f): ?>
<tr>
  <td><?= (int)$f['id'] ?></td>
  <td><?=$h($f['domanda'])?><br><span class="muted"><?=$h($f['slug'])?></span></td>
  <td class="muted"><?=$h($f['tag'])?></td>
  <td class="muted"><?=$h($f['cta'])?></td>
  <td><?= (int)$f['hit'] ?></td>
  <td><span class="badge <?= $f['attiva']?'on':'off' ?>"><?= $f['attiva']?'attiva':'off' ?></span></td>
  <td>
    <form method="post" class="inline">
      <?php if(function_exists('dr_csrf_field')) dr_csrf_field(); ?>
      <input type="hidden" name="azione" value="toggle_faq"><input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
      <button class="btn sm ghost" type="submit"><?= $f['attiva']?'Disattiva':'Attiva' ?></button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</table>
</div>

<script>
function prefill(t){ var el=document.getElementById('fDom'); if(el){ el.value=t; el.scrollIntoView({behavior:'smooth',block:'center'}); el.focus(); } }
</script>
</div></body></html>
