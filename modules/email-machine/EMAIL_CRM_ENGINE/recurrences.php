<?php
/* ============================================================
   DESTINO RANDAGIO — EMAIL/CRM ENGINE · RICORRENZE
   Compleanni (auguri + bonus DRX) · Anniversari iscrizione · Reminder.
   IDEMPOTENTE: 1 solo invio per occasione (marker su lead_events,
   una riga per email+ricorrenza+anno). Riusa mailer.php per l'invio.
   ============================================================ */

require_once __DIR__.'/crm-lib.php';                 // $pdo + funzioni lead
require_once dirname(__DIR__, 2).'/mailer.php';      // mkt_render, mkt_enqueue
@require_once dirname(__DIR__, 2).'/drx.php';        // drx_award (opzionale)

/* segna/verifica un'occasione già gestita (per anno). true = già fatto */
function dr_rec_done($pdo,$email,$chiave){
  $ref='ricorrenza:'.$chiave;
  $s=$pdo->prepare("SELECT 1 FROM lead_events WHERE email=? AND tipo='ricorrenza' AND ref=? LIMIT 1");
  $s->execute([$email,$ref]); return (bool)$s->fetchColumn();
}
function dr_rec_mark($pdo,$email,$chiave){
  dr_lead_event($pdo,$email,'ricorrenza','ricorrenza:'.$chiave);
}

/* bonus DRX opzionale e sicuro (solo se il lead è un utente registrato) */
function dr_rec_bonus_drx($pdo,$lead,$amt,$reason){
  if($amt<=0) return false;
  $uid=(int)($lead['uid']??0);
  if($uid>0 && function_exists('drx_award')){
    try{ drx_award($pdo,$uid,$amt,$reason,'crm-recurrence'); return true; }catch(Exception $e){}
  }
  return false;
}

/* =====================================================================
   1) COMPLEANNI — auguri + bonus DRX (1 volta l'anno)
   ===================================================================== */
function dr_rec_birthdays($pdo){
  $anno=date('Y'); $inviati=0;
  foreach(dr_lead_segment($pdo,'compleanno:oggi',1000) as $lead){
    $email=$lead['email'];
    if(mkt_is_unsub($pdo,$email)) continue;
    if(dr_rec_done($pdo,$email,'birthday:'.$anno)) continue;   // già fatto quest'anno
    $nome=$lead['nome']?trim(explode(' ',$lead['nome'])[0]):'randagio';
    list($subj,$html)=mkt_render('birthday',['name'=>$nome]);
    mkt_enqueue($pdo,$email,$nome,$subj,$html,null,'crm-birthday');
    dr_rec_bonus_drx($pdo,$lead,1000,'bday-gift');
    dr_rec_mark($pdo,$email,'birthday:'.$anno);
    $inviati++;
  }
  return $inviati;
}

/* =====================================================================
   2) ANNIVERSARI ISCRIZIONE — stesso giorno/mese del "created" (1/anno)
   Usa il template 'newsletter' con copy dedicato.
   ===================================================================== */
function dr_rec_anniversaries($pdo){
  $anno=date('Y'); $md=date('m-d'); $inviati=0;
  $q=$pdo->prepare("SELECT * FROM leads
      WHERE consenso=1 AND created IS NOT NULL
        AND substr(created,6,5)=?
        AND substr(created,1,4)<>?");           // non nel primo giorno stesso
  $q->execute([$md,$anno]);
  foreach($q->fetchAll(PDO::FETCH_ASSOC) as $lead){
    $email=$lead['email'];
    if(mkt_is_unsub($pdo,$email)) continue;
    if(dr_rec_done($pdo,$email,'anniv:'.$anno)) continue;
    $anni=max(1,(int)$anno-(int)substr($lead['created'],0,4));
    $nome=$lead['nome']?trim(explode(' ',$lead['nome'])[0]):'randagio';
    list($subj,$html)=mkt_render('newsletter',[
      'name'=>$nome,
      'subject'=>'🐺 '.$anni.' anni nel Branco, '.$nome,
      'title'=>$anni.($anni==1?' anno':' anni').' con Destino Randagio',
      'body'=>'Un randagio non dimentica chi cammina con lui. Grazie per questi '.$anni.($anni==1?' anno':' anni').' nel Branco — il meglio deve ancora arrivare.',
      'url'=>DR_SITE.'/account.php',
    ]);
    mkt_enqueue($pdo,$email,$nome,$subj,$html,null,'crm-anniv');
    dr_rec_bonus_drx($pdo,$lead,500,'anniversary-gift');
    dr_rec_mark($pdo,$email,'anniv:'.$anno);
    $inviati++;
  }
  return $inviati;
}

/* =====================================================================
   3) REMINDER RIATTIVAZIONE — lead diventati FREDDI da oltre 60gg,
   con consenso, che non ricevono un reminder da almeno 30gg.
   Un colpo di reminder (win-back leggero), max 1 al mese.
   ===================================================================== */
function dr_rec_reminders($pdo){
  $mese=date('Y-m'); $inviati=0;
  $q=$pdo->query("SELECT * FROM leads
      WHERE stato_lead='freddo' AND consenso=1
        AND ultima_interazione < datetime('now','-60 days')
      LIMIT 300");
  foreach($q->fetchAll(PDO::FETCH_ASSOC) as $lead){
    $email=$lead['email'];
    if(mkt_is_unsub($pdo,$email)) continue;
    if(dr_rec_done($pdo,$email,'reminder:'.$mese)) continue;    // max 1/mese
    $nome=$lead['nome']?trim(explode(' ',$lead['nome'])[0]):'randagio';
    list($subj,$html)=mkt_render('winback',['name'=>$nome]);
    mkt_enqueue($pdo,$email,$nome,$subj,$html,null,'crm-reminder');
    dr_rec_mark($pdo,$email,'reminder:'.$mese);
    $inviati++;
  }
  return $inviati;
}

/* esegue tutte le ricorrenze (una passata) */
function dr_rec_run_all($pdo){
  $r=[
    'compleanni'   => dr_rec_birthdays($pdo),
    'anniversari'  => dr_rec_anniversaries($pdo),
    'reminder'     => dr_rec_reminders($pdo),
  ];
  dr_crm_cortex_log('recurrences_run',$r);
  return $r;
}

/* =====================================================================
   SCHEDULER ENDPOINT (senza cron, da visits.php via drFire).
   Chiave da .env: DR_CRM_KEY. Uso: recurrences.php?run=1&key=XXXX
   ===================================================================== */
if(isset($_GET['run']) && ($_GET['key']??'')!=='' && hash_equals((string)dr_env('DR_CRM_KEY',''), (string)($_GET['key']??''))){
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(dr_rec_run_all($pdo), JSON_UNESCAPED_UNICODE);
  exit;
}
