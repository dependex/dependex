<?php
/* ============================================================
   DESTINO RANDAGIO — EMAIL/CRM ENGINE · DASHBOARD ADMIN
   Segmenti lead · Flussi attivi · Statistiche · Stato ordini.
   Stile brand nero/oro, coerente col resto del sito (admin-web3.php).
   Accesso: solo ruolo admin in sessione.
   ============================================================ */
session_start();
require_once __DIR__.'/crm-lib.php';                 // $pdo + funzioni
require_once __DIR__.'/flows.php';                    // dr_flow_* (+ seed)
require_once __DIR__.'/recurrences.php';              // dr_rec_run_all
dr_flow_seed($pdo);

$isAdmin = (($_SESSION['role']??'')==='admin');
if(!$isAdmin){ header('Location: /account.php'); exit; }
function e($s){ return htmlspecialchars((string)$s,ENT_QUOTES); }
function n($x){ return number_format((float)$x,0,',','.'); }

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $do=$_POST['do']??'';
  if($do==='runflows'){ $n=dr_flow_run($pdo); $msg="Flussi: $n email accodate."; }
  elseif($do==='runrec'){ $r=dr_rec_run_all($pdo); $msg="Ricorrenze — compleanni: {$r['compleanni']}, anniversari: {$r['anniversari']}, reminder: {$r['reminder']}."; }
  elseif($do==='resync'){ $k=dr_crm_resync_all($pdo); $msg="Risincronizzati $k contatti dai dati del sito."; }
  elseif($do==='rescore'){ $k=0; foreach($pdo->query("SELECT email FROM leads") as $l){ dr_lead_score($pdo,$l['email']); $k++; } $msg="Ricalcolati $k punteggi."; }
  elseif($do==='tag'){ $em=$_POST['email']??''; $tg=$_POST['tag']??''; if($em&&$tg){ dr_lead_tag($pdo,$em,$tg); $msg="Tag \"$tg\" aggiunto a $em."; } }
}

$S = dr_crm_stats($pdo);
$flows = $pdo->query("SELECT a.*, (SELECT COUNT(*) FROM automation_runs r WHERE r.automation_id=a.id AND r.stato='attivo') attivi FROM automations a ORDER BY a.id")->fetchAll(PDO::FETCH_ASSOC);
$seg   = $_GET['seg'] ?? 'caldi';
$leads = dr_lead_segment($pdo,$seg,200);

/* stato ordini collegato (KPI) */
$ord=['pagati'=>0,'incasso'=>0,'pending'=>0,'carrelli'=>0];
try{
  $ord['pagati']  =(int)$pdo->query("SELECT COUNT(*) FROM orders WHERE LOWER(status)='paid'")->fetchColumn();
  $ord['incasso'] =(float)$pdo->query("SELECT COALESCE(SUM(total_eur),0) FROM orders WHERE LOWER(status)='paid'")->fetchColumn();
  $ord['pending'] =(int)$pdo->query("SELECT COUNT(*) FROM orders WHERE LOWER(status)='pending'")->fetchColumn();
  $ord['carrelli']=(int)$pdo->query("SELECT COUNT(*) FROM carts WHERE status='open'")->fetchColumn();
}catch(Exception $ex){}
$conv = $S['con_consenso']>0 ? round(100*$S['clienti']/$S['con_consenso'],1) : 0;
?><!DOCTYPE html><html lang="it"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>CRM &amp; Email Engine — Destino Randagio</title>
<meta name="robots" content="noindex,nofollow">
<style>
:root{--gold:#D4AF37;--bg:#0A0A0A}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:#eee;font-family:'Segoe UI',Arial,sans-serif}
.w{max-width:1180px;margin:0 auto;padding:24px}
h1{color:var(--gold);letter-spacing:1px;margin:0 0 4px}
h2{color:var(--gold);font-size:1.1rem;margin:26px 0 12px;border-bottom:1px solid rgba(212,175,55,.2);padding-bottom:6px}
.sub{color:#9aa0a6;font-size:.85rem;margin-bottom:8px}
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px}
.kpi{background:linear-gradient(160deg,#1b1710,#0b0a07);border:1px solid rgba(212,175,55,.3);border-radius:14px;padding:14px;text-align:center}
.kpi b{display:block;font-size:1.55rem;color:#fff}
.kpi span{font-size:.66rem;color:#9aa0a6;text-transform:uppercase;letter-spacing:1px}
.hot b{color:#ff7a59}.warm b{color:#ffce6a}.cold b{color:#7fb2ff}
table{width:100%;border-collapse:collapse;font-size:.82rem;margin-top:6px}
th,td{text-align:left;padding:8px 6px;border-bottom:1px solid rgba(212,175,55,.15)}
th{color:var(--gold);font-weight:600}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:.7rem;font-weight:700}
.b-caldo{background:rgba(255,122,89,.18);color:#ff9c82;border:1px solid rgba(255,122,89,.4)}
.b-tiepido{background:rgba(255,206,106,.16);color:#ffce6a;border:1px solid rgba(255,206,106,.4)}
.b-freddo{background:rgba(127,178,255,.14);color:#9fc4ff;border:1px solid rgba(127,178,255,.35)}
.tag{display:inline-block;background:#161206;border:1px solid rgba(212,175,55,.35);color:#e9d8a0;border-radius:8px;padding:1px 7px;font-size:.68rem;margin:1px}
.tabs{display:flex;gap:8px;flex-wrap:wrap;margin:8px 0}
.tabs a{padding:7px 14px;border-radius:20px;border:1px solid rgba(212,175,55,.3);color:#cfcfcf;text-decoration:none;font-size:.8rem}
.tabs a.on{background:var(--gold);color:#160f00;font-weight:700;border-color:var(--gold)}
.card{background:linear-gradient(160deg,#141109,#0a0906);border:1px solid rgba(212,175,55,.25);border-radius:14px;padding:16px;margin-bottom:12px}
.btn{background:var(--gold);color:#160f00;border:0;border-radius:20px;padding:9px 16px;font-weight:700;cursor:pointer;font-size:.8rem}
.btn.ghost{background:transparent;color:var(--gold);border:1px solid var(--gold)}
.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
input,select{background:#0e0e0e;border:1px solid #333;color:#fff;border-radius:10px;padding:9px 12px;font-size:.82rem}
.msg{background:rgba(38,161,123,.12);border:1px solid rgba(38,161,123,.4);color:#7dffc0;border-radius:12px;padding:10px 14px;margin:10px 0}
.flow{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid rgba(212,175,55,.12)}
.mut{color:#9aa0a6;font-size:.75rem}
@media(max-width:640px){.hidem{display:none}}
</style>
</head><body>
<div class="w">
  <h1>CRM &amp; Email Engine ♛</h1>
  <div class="sub">Motore email marketing + CRM di Destino Randagio — segmenti, flussi, ricorrenze e ordini in un colpo d'occhio.</div>
  <?php if($msg): ?><div class="msg"><?=e($msg)?></div><?php endif; ?>

  <h2>Statistiche</h2>
  <div class="kpis">
    <div class="kpi"><b><?=n($S['lead_totali'])?></b><span>Lead totali</span></div>
    <div class="kpi hot"><b><?=n($S['caldi'])?></b><span>🔥 Caldi</span></div>
    <div class="kpi warm"><b><?=n($S['tiepidi'])?></b><span>🌤️ Tiepidi</span></div>
    <div class="kpi cold"><b><?=n($S['freddi'])?></b><span>❄️ Freddi</span></div>
    <div class="kpi"><b><?=n($S['clienti'])?></b><span>Clienti</span></div>
    <div class="kpi"><b><?=$conv?>%</b><span>Conversione</span></div>
    <div class="kpi"><b>€<?=n($S['ltv_totale'])?></b><span>LTV totale</span></div>
    <div class="kpi"><b><?=n($S['eventi_30g'])?></b><span>Eventi 30gg</span></div>
  </div>

  <h2>Stato ordini collegato</h2>
  <div class="kpis">
    <div class="kpi"><b><?=n($ord['pagati'])?></b><span>Ordini pagati</span></div>
    <div class="kpi"><b>€<?=n($ord['incasso'])?></b><span>Incasso</span></div>
    <div class="kpi"><b><?=n($ord['pending'])?></b><span>In attesa</span></div>
    <div class="kpi"><b><?=n($ord['carrelli'])?></b><span>Carrelli aperti</span></div>
  </div>

  <h2>Flussi automazione</h2>
  <div class="card">
    <?php foreach($flows as $f): ?>
      <div class="flow">
        <div><b style="color:#fff"><?=e($f['nome'])?></b>
          <span class="mut">· trigger: <?=e($f['trig'])?> · <?=count(json_decode($f['steps']?:'[]',true))?> step</span></div>
        <div><span class="badge <?=$f['active']?'b-caldo':'b-freddo'?>"><?=$f['active']?'attivo':'off'?></span>
          <span class="mut"><?=n($f['attivi'])?> in corso</span></div>
      </div>
    <?php endforeach; if(!$flows): ?><div class="mut">Nessun flusso: verranno creati al primo trigger o resync.</div><?php endif; ?>
  </div>

  <h2>Motore (esecuzione manuale)</h2>
  <div class="card row">
    <form method="post"><input type="hidden" name="do" value="runflows"><button class="btn">▶ Avanza flussi</button></form>
    <form method="post"><input type="hidden" name="do" value="runrec"><button class="btn ghost">🎂 Ricorrenze</button></form>
    <form method="post"><input type="hidden" name="do" value="rescore"><button class="btn ghost">🔁 Ricalcola score</button></form>
    <form method="post"><input type="hidden" name="do" value="resync"><button class="btn ghost">⬇ Importa contatti sito</button></form>
    <span class="mut">In produzione girano da soli via <code>visits.php</code> (vedi scheduler-hook.md).</span>
  </div>

  <h2>Segmenti lead</h2>
  <div class="tabs">
    <?php foreach(['caldi'=>'🔥 Caldi','tiepidi'=>'🌤️ Tiepidi','freddi'=>'❄️ Freddi','attivi'=>'Attivi','tutti'=>'Tutti'] as $k=>$lab): ?>
      <a class="<?=$seg===$k?'on':''?>" href="?seg=<?=$k?>"><?=$lab?></a>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <table>
      <tr><th>Email</th><th class="hidem">Nome</th><th>Stato</th><th>Score</th><th class="hidem">Tag</th><th>Ordini</th><th class="hidem">LTV</th><th class="hidem">Ultima interazione</th></tr>
      <?php foreach($leads as $l): $tags=json_decode($l['tags']??'[]',true); ?>
        <tr>
          <td><?=e($l['email'])?></td>
          <td class="hidem"><?=e($l['nome'])?></td>
          <td><span class="badge b-<?=e($l['stato_lead'])?>"><?=e($l['stato_lead'])?></span></td>
          <td><?=(int)$l['score']?></td>
          <td class="hidem"><?php foreach((array)$tags as $t) echo '<span class="tag">'.e($t).'</span>'; ?></td>
          <td><?=(int)$l['ordini']?></td>
          <td class="hidem">€<?=n($l['ltv_eur'])?></td>
          <td class="hidem mut"><?=e($l['ultima_interazione'])?></td>
        </tr>
      <?php endforeach; if(!$leads): ?><tr><td colspan="8" class="mut">Nessun lead in questo segmento.</td></tr><?php endif; ?>
    </table>
  </div>

  <h2>Aggiungi tag a un lead</h2>
  <div class="card">
    <form method="post" class="row">
      <input type="hidden" name="do" value="tag">
      <input name="email" placeholder="email@lead.it" style="min-width:240px">
      <input name="tag" placeholder="es. VIP, genesis, delta">
      <button class="btn">+ Aggiungi tag</button>
    </form>
  </div>

  <p class="mut" style="margin-top:22px">Destino Randagio · Email/CRM Engine · dashboard privata (noindex).</p>
</div>
</body></html>
