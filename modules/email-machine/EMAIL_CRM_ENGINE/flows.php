<?php
/* ============================================================
   DESTINO RANDAGIO — EMAIL/CRM ENGINE · MOTORE DEI FLUSSI
   Automazioni multi-step (stile Brevo/GHL) definite come array di step:
     ['wait_h'=>ore_di_attesa, 'tpl'=>template, 'subject'=>opz, 'cond'=>opz]
   - dr_flow_seed()     inserisce i flussi di default (idempotente)
   - dr_flow_trigger()  iscrive un lead a tutti i flussi con quel trigger
   - dr_flow_run()      avanza i lead maturi: valuta la condizione, accoda
                        l'email (riusando il motore mailer.php) e programma
                        lo step successivo.
   Riusa: mkt_render() + mkt_enqueue() del sito (mailer.php) -> NIENTE
   duplicazione dell'invio, stessa coda, stesso tracking aperture/click.
   ============================================================ */

require_once __DIR__.'/crm-lib.php';                 // $pdo + funzioni lead
require_once dirname(__DIR__, 2).'/mailer.php';      // mkt_render, mkt_enqueue, mkt_contact_add

/* =====================================================================
   DEFINIZIONE DEI FLUSSI DI DEFAULT
   Ogni step usa un template già presente in mailer.php::mkt_render().
   cond (facoltativa) = array valutata prima dell'invio dello step:
     'stato_diverso' => 'caldo'   -> salta se il lead È già caldo
     'min_score'     => 20        -> invia solo se score >= 20
     'no_order'      => true       -> invia solo se NON ha ancora acquistato
   ===================================================================== */
function dr_flow_defs(){
  return [
    // BENVENUTO + nurturing storytelling in 5 capitoli
    ['nome'=>'Benvenuto + Nurturing','trig'=>'signup','steps'=>[
      ['wait_h'=>0,   'tpl'=>'welcome'],
      ['wait_h'=>48,  'tpl'=>'nurture1'],
      ['wait_h'=>120, 'tpl'=>'nurture2'],
      ['wait_h'=>192, 'tpl'=>'nurture3'],
      ['wait_h'=>264, 'tpl'=>'nurture4', 'cond'=>['no_order'=>true]],
      ['wait_h'=>336, 'tpl'=>'nurture5', 'cond'=>['no_order'=>true]],
    ]],
    // CARRELLO ABBANDONATO (2 solleciti, solo se non ha comprato)
    ['nome'=>'Carrello abbandonato','trig'=>'abandoned','steps'=>[
      ['wait_h'=>1,  'tpl'=>'abandoned', 'cond'=>['no_order'=>true]],
      ['wait_h'=>24, 'tpl'=>'abandoned', 'cond'=>['no_order'=>true]],
    ]],
    // POST-ACQUISTO (grazie + spinta all'ecosistema)
    ['nome'=>'Post-acquisto','trig'=>'purchase','steps'=>[
      ['wait_h'=>0,  'tpl'=>'purchase'],
      ['wait_h'=>72, 'tpl'=>'nurture4'],
    ]],
    // RIATTIVAZIONE FREDDI (win-back, solo lead diventati freddi)
    ['nome'=>'Riattivazione freddi','trig'=>'winback','steps'=>[
      ['wait_h'=>0,   'tpl'=>'winback',  'cond'=>['stato_diverso'=>'caldo']],
      ['wait_h'=>240, 'tpl'=>'promo',    'cond'=>['stato_diverso'=>'caldo']],
    ]],
  ];
}

/* inserisce i flussi mancanti (per trigger). NON tocca quelli già presenti. */
function dr_flow_seed($pdo){
  foreach(dr_flow_defs() as $f){
    $ex=$pdo->prepare("SELECT id FROM automations WHERE trig=? AND nome=? LIMIT 1");
    $ex->execute([$f['trig'],$f['nome']]);
    if($ex->fetchColumn()) continue;
    $pdo->prepare("INSERT INTO automations(nome,trig,steps,active) VALUES(?,?,?,1)")
        ->execute([$f['nome'],$f['trig'],json_encode($f['steps'],JSON_UNESCAPED_UNICODE)]);
  }
  return true;
}

/* delay del primo step di un flusso */
function dr_flow_first_delay($steps){
  $s=json_decode($steps,true); return is_array($s)&&isset($s[0]['wait_h']) ? (float)$s[0]['wait_h'] : 0;
}

/* =====================================================================
   dr_flow_trigger — iscrive un lead a tutti i flussi attivi col trigger.
   Evita doppioni attivi sullo stesso flusso.
   ===================================================================== */
function dr_flow_trigger($pdo,$trig,$email,$nome='',$ctx=[]){
  dr_flow_seed($pdo);
  $email=dr_lead_norm_email($email);
  $lead=dr_lead_upsert($pdo,$email,$nome,$trig);
  if(!$lead || (int)$lead['consenso']===0) return 0;     // no consenso -> stop
  $lid=(int)$lead['id'];
  $flows=$pdo->prepare("SELECT * FROM automations WHERE trig=? AND active=1"); $flows->execute([$trig]);
  $n=0;
  foreach($flows->fetchAll(PDO::FETCH_ASSOC) as $f){
    $ex=$pdo->prepare("SELECT id FROM automation_runs WHERE automation_id=? AND email=? AND stato='attivo'");
    $ex->execute([$f['id'],$email]); if($ex->fetchColumn()) continue;
    $d=dr_flow_first_delay($f['steps']);
    $pdo->prepare("INSERT INTO automation_runs(automation_id,lead_id,email,step,next_at,stato,ctx)
                   VALUES(?,?,?,0,datetime('now',?),'attivo',?)")
        ->execute([$f['id'],$lid,$email,'+'.$d.' hours',json_encode($ctx,JSON_UNESCAPED_UNICODE)]);
    $n++;
  }
  dr_crm_cortex_log('flow_trigger',['email'=>$email,'trigger'=>$trig,'iscrizioni'=>$n]);
  return $n;
}

/* valuta la condizione di uno step sul lead corrente. true = si invia. */
function dr_flow_cond_ok($pdo,$email,$cond){
  if(empty($cond)||!is_array($cond)) return true;
  $lead=dr_lead_get($pdo,$email); if(!$lead) return false;
  if(isset($cond['stato_diverso']) && $lead['stato_lead']===$cond['stato_diverso']) return false;
  if(isset($cond['stato']) && $lead['stato_lead']!==$cond['stato']) return false;
  if(isset($cond['min_score']) && (int)$lead['score'] < (int)$cond['min_score']) return false;
  if(!empty($cond['no_order']) && (int)$lead['ordini'] > 0) return false;
  if(isset($cond['has_tag']) && !dr_lead_has_tag($lead,$cond['has_tag'])) return false;
  return true;
}

/* =====================================================================
   dr_flow_run — esecutore: avanza tutte le run mature.
   Per ogni run "attivo" con next_at<=now:
     - se la condizione dello step passa -> accoda l'email (mkt_enqueue)
       altrimenti salta lo step (senza inviare)
     - programma lo step successivo, oppure chiude la run
   Non invia direttamente: mette in coda; la coda gira già via mailer.php.
   ===================================================================== */
function dr_flow_run($pdo,$limit=300){
  dr_flow_seed($pdo);
  $due=$pdo->query("SELECT * FROM automation_runs WHERE stato='attivo' AND next_at<=datetime('now') LIMIT ".(int)$limit)
           ->fetchAll(PDO::FETCH_ASSOC);
  $accodate=0;
  foreach($due as $run){
    $a=$pdo->query("SELECT steps FROM automations WHERE id=".(int)$run['automation_id'])->fetchColumn();
    $steps=json_decode($a?:'[]',true); if(!is_array($steps)||!$steps){
      $pdo->prepare("UPDATE automation_runs SET stato='chiuso',updated=datetime('now') WHERE id=?")->execute([$run['id']]); continue;
    }
    $i=(int)$run['step'];
    if($i>=count($steps)){ $pdo->prepare("UPDATE automation_runs SET stato='completato',updated=datetime('now') WHERE id=?")->execute([$run['id']]); continue; }

    /* lead disiscritto? -> chiudi la run */
    if(mkt_is_unsub($pdo,$run['email'])){ $pdo->prepare("UPDATE automation_runs SET stato='annullato',updated=datetime('now') WHERE id=?")->execute([$run['id']]); continue; }

    $step=$steps[$i];
    $ctx=json_decode($run['ctx']??'',true) ?: [];
    $lead=dr_lead_get($pdo,$run['email']);
    $ctx['name']=$ctx['name'] ?? ($lead && $lead['nome'] ? trim(explode(' ',$lead['nome'])[0]) : 'randagio');

    if(dr_flow_cond_ok($pdo,$run['email'],$step['cond']??null)){
      list($subj,$html)=mkt_render($step['tpl'],$ctx);
      $subj = $step['subject'] ?? $subj;
      mkt_enqueue($pdo,$run['email'],$ctx['name'],$subj,$html,null,'crmflow'.$run['automation_id'].':'.$step['tpl']);
      dr_lead_event($pdo,$run['email'],'invio','flow:'.$step['tpl']);
      $accodate++;
    }

    /* programma lo step successivo o chiudi */
    if($i+1 < count($steps)){
      $nd=(float)($steps[$i+1]['wait_h']??0);
      $pdo->prepare("UPDATE automation_runs SET step=step+1, next_at=datetime('now',?), updated=datetime('now') WHERE id=?")
          ->execute(['+'.$nd.' hours',$run['id']]);
    } else {
      $pdo->prepare("UPDATE automation_runs SET step=step+1, stato='completato', updated=datetime('now') WHERE id=?")->execute([$run['id']]);
    }
  }
  dr_crm_cortex_log('flow_run',['maturi'=>count($due),'accodate'=>$accodate]);
  return $accodate;
}

/* =====================================================================
   SCHEDULER ENDPOINT (senza cron, chiamato da visits.php via drFire).
   Chiave letta da .env: DR_CRM_KEY (NIENTE segreti nel codice).
   Uso:  flows.php?run=1&key=XXXX
   ===================================================================== */
if(isset($_GET['run']) && ($_GET['key']??'')!=='' && hash_equals((string)dr_env('DR_CRM_KEY',''), (string)($_GET['key']??''))){
  header('Content-Type: text/plain; charset=utf-8');
  $n=dr_flow_run($pdo);
  echo "crm-flows accodate:$n\n";
  exit;
}
