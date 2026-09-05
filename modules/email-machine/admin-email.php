<?php
/* ============================================================================
   ADMIN — INTERRUTTORE MACCHINA EMAIL (1 click)

   La macchina email nasce in STAND-BY: tutto pronto (flussi, template, coda,
   watcher, Actions) ma NIENTE parte. Da qui, con un click, si ARMA e le email
   iniziano a partire agli orari ottimali. Un altro click e torna in stand-by.
   Solo admin. Lo stato vero e' in mkt_meta('armato'); il .env DR_EMAIL_ARMATO
   puo' forzarlo (in quel caso il bottone lo segnala e non basta il click).
============================================================================ */
session_start();
require_once __DIR__.'/db.php';
require_once __DIR__.'/mailer.php';
require_once __DIR__.'/dr-security.php'; // CSRF + header sicuri (B5)
dr_security_headers();

$isAdmin = (($_SESSION['role']??'')==='admin') || !empty($_SESSION['is_admin']);
if(!$isAdmin){ header('Location: account.php'); exit; }

$pdo->exec("CREATE TABLE IF NOT EXISTS mkt_meta(k TEXT PRIMARY KEY, v TEXT)");

/* toggle — B5: protetto anche da CSRF (progressivo: se il token manca del tutto
   e DR_CSRF_ENFORCE non e' attiva, il toggle continua a funzionare). */
if(($_POST['azione']??'')==='toggle' && dr_csrf_ok()){
  $now = ($pdo->query("SELECT v FROM mkt_meta WHERE k='armato'")->fetchColumn()==='1');
  $pdo->prepare("REPLACE INTO mkt_meta(k,v) VALUES('armato',?)")->execute([$now?'0':'1']);
  header('Location: admin-email.php'); exit;
}

$armato = mkt_armato($pdo);
$envForce = function_exists('dr_env') ? dr_env('DR_EMAIL_ARMATO','') : '';
$inCoda = (int)$pdo->query("SELECT COUNT(*) FROM mkt_queue WHERE status='pending'")->fetchColumn();
$contatti = (int)$pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$attivi = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE status='attivo'")->fetchColumn();
$inviate = (int)$pdo->query("SELECT COUNT(*) FROM emails_log WHERE status='sent'")->fetchColumn();
?><!doctype html><html lang="it"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Macchina Email — Interruttore</title>
<style>
body{margin:0;background:#0d0d0d;color:#eee;font-family:system-ui,Segoe UI,Roboto,sans-serif}
.wrap{max-width:640px;margin:0 auto;padding:40px 20px}
h1{color:#D4AF37;font-size:1.5rem}
.card{background:#151006;border:1px solid rgba(212,175,55,.35);border-radius:16px;padding:26px;margin:18px 0}
.state{display:flex;align-items:center;gap:14px;font-size:1.3rem;font-weight:800}
.dot{width:16px;height:16px;border-radius:50%}
.on .dot{background:#3ad07a;box-shadow:0 0 14px #3ad07a}.off .dot{background:#888}
.on{color:#3ad07a}.off{color:#bbb}
.btn{display:inline-block;border:0;border-radius:40px;padding:16px 34px;font-weight:900;font-size:1.05rem;cursor:pointer;text-transform:uppercase;letter-spacing:.5px;margin-top:8px}
.arm{background:linear-gradient(135deg,#3ad07a,#1f9e57);color:#04220f}
.disarm{background:linear-gradient(135deg,#f3d77a,#b8942e);color:#160f00}
.kpi{display:flex;gap:22px;flex-wrap:wrap;margin-top:10px;color:#c9bd9e;font-size:.9rem}
.kpi b{color:#D4AF37;font-size:1.2rem;display:block}
.note{color:#9a8f78;font-size:.85rem;margin-top:14px;line-height:1.5}
.warn{color:#e0a83a}
a.back{color:#c8a24a;text-decoration:none;font-size:.9rem}
</style></head><body><div class="wrap">
<a class="back" href="dashboard-admin.php">← Dashboard admin</a>
<h1>🚀 Macchina Email — Interruttore</h1>

<div class="card">
  <div class="state <?= $armato?'on':'off' ?>"><span class="dot"></span>
    <?= $armato ? 'ARMATA — le email partono' : 'STAND-BY — niente parte' ?>
  </div>
  <div class="kpi">
    <div><b><?= $inCoda ?></b> in coda</div>
    <div><b><?= $attivi ?></b> contatti attivi</div>
    <div><b><?= $contatti ?></b> contatti totali</div>
    <div><b><?= $inviate ?></b> inviate</div>
  </div>

  <?php if($envForce==='1' || $envForce==='0'): ?>
    <p class="note warn">⚠️ Lo stato e' forzato dal file .env (DR_EMAIL_ARMATO=<?= htmlspecialchars($envForce) ?>).
       Per usare questo interruttore togli DR_EMAIL_ARMATO dal .env.</p>
  <?php else: ?>
    <form method="post" style="margin-top:16px">
      <?php dr_csrf_field(); ?>
      <input type="hidden" name="azione" value="toggle">
      <?php if($armato): ?>
        <button class="btn disarm" onclick="return confirm('Rimetto la macchina email in STAND-BY?')">⏸ Metti in stand-by</button>
      <?php else: ?>
        <button class="btn arm" onclick="return confirm('ARMO la macchina: le email inizieranno a partire agli orari ottimali. Procedo?')">▶ ARMA la macchina (1 click)</button>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <p class="note">
    In <b>stand-by</b> i flussi si preparano e la coda si riempie, ma nessuna email
    esce (restano <code>pending</code>). Quando <b>armi</b>, l'invio riparte agli
    orari ad alta conversione (mar/mer/gio 10-11 e 18-20) e la coda si svuota da
    sola tramite lo scheduler e le GitHub Actions. Il marketing va solo ai
    contatti con consenso (<code>status=attivo</code>); i <b>da confermare</b>
    ricevono prima la re-permission.
  </p>
</div>
</div></body></html>
