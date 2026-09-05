<?php
/* ============================================================
   DESTINO RANDAGIO — EMAIL/CRM ENGINE · LIBRERIA CORE
   Funzioni procedurali coerenti col sito. Richiede $pdo da db.php.
   API principali:
     dr_lead_upsert()   crea/aggiorna un lead (e lo linka a users)
     dr_lead_event()    registra un comportamento e ricalcola lo score
     dr_lead_score()    ricalcola stato (freddo|tiepido|caldo) + punteggio
     dr_lead_tag()      aggiunge/rimuove un tag (JSON)
     dr_lead_segment()  ritorna i lead di un segmento
   Collega lo status ORDINI (tabella orders) al profilo lead.
   ============================================================ */

$__DR_ROOT = dirname(__DIR__, 2);            // .../destinorandagio.it
require_once $__DR_ROOT.'/db.php';            // $pdo, tabelle core
require_once $__DR_ROOT.'/dr-env.php';        // dr_env() per i segreti
require_once __DIR__.'/schema.php';           // tabelle CRM
dr_crm_schema($pdo);

/* Log opzionale verso il "cervello" _cortex (mai fatale, mai pubblico). */
if(!function_exists('dr_crm_cortex_log')){
  function dr_crm_cortex_log($tipo,$payload){
    $root = dirname(__DIR__, 2);
    $dir  = $root.'/_cortex/signals';
    if(!is_dir($dir)){ if(!@mkdir($dir,0775,true)) return false; }
    $line = json_encode(['ts'=>date('c'),'engine'=>'email_crm','tipo'=>$tipo,'data'=>$payload], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    return @file_put_contents($dir.'/crm-'.date('Y-m').'.log', $line."\n", FILE_APPEND|LOCK_EX)!==false;
  }
}

/* ---- helper interni --------------------------------------------------- */
if(!function_exists('dr_lead_norm_email')){
  function dr_lead_norm_email($e){ return strtolower(trim((string)$e)); }
}
function dr_lead_get($pdo,$email){
  $email=dr_lead_norm_email($email);
  $s=$pdo->prepare("SELECT * FROM leads WHERE email=? LIMIT 1"); $s->execute([$email]);
  return $s->fetch(PDO::FETCH_ASSOC) ?: null;
}
/* trova l'utente registrato collegato a questa email (se esiste) */
function dr_lead_find_uid($pdo,$email){
  $s=$pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1"); $s->execute([$email]);
  return (int)$s->fetchColumn();
}

/* =====================================================================
   dr_lead_upsert — crea o aggiorna un lead. Non distrugge dati esistenti.
   $extra: ['compleanno'=>'YYYY-MM-DD','consenso'=>1,'uid'=>N]
   ===================================================================== */
function dr_lead_upsert($pdo,$email,$nome='',$fonte='',$extra=[]){
  $email=dr_lead_norm_email($email);
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return false;
  $uid = (int)($extra['uid'] ?? 0); if(!$uid) $uid=dr_lead_find_uid($pdo,$email);
  $ex=dr_lead_get($pdo,$email);
  if(!$ex){
    $pdo->prepare("INSERT INTO leads(email,nome,fonte,uid,compleanno,consenso,ultima_interazione)
                   VALUES(?,?,?,?,?,?,datetime('now'))")
        ->execute([$email,$nome,$fonte,$uid,
                   $extra['compleanno']??null,
                   isset($extra['consenso'])?(int)$extra['consenso']:1]);
    dr_crm_cortex_log('lead_new',['email'=>$email,'fonte'=>$fonte]);
  } else {
    /* aggiorna solo i campi vuoti / forniti, senza sovrascrivere il buono */
    $pdo->prepare("UPDATE leads SET
        nome       = CASE WHEN ?<>''  THEN ? ELSE nome END,
        fonte      = COALESCE(NULLIF(fonte,''), NULLIF(?, '')),
        uid        = CASE WHEN ?>0 THEN ? ELSE uid END,
        compleanno = COALESCE(compleanno, ?),
        consenso   = ?,
        updated    = datetime('now')
       WHERE email=?")
      ->execute([$nome,$nome, $fonte, $uid,$uid,
                 $extra['compleanno']??null,
                 isset($extra['consenso'])?(int)$extra['consenso']:(int)$ex['consenso'],
                 $email]);
  }
  dr_lead_link_orders($pdo,$email);
  dr_lead_score($pdo,$email);
  return dr_lead_get($pdo,$email);
}

/* =====================================================================
   dr_lead_event — registra un comportamento e aggiorna il profilo.
   $tipo: apertura|click|acquisto|visita|iscrizione|carrello|login|...
   ===================================================================== */
function dr_lead_event($pdo,$email,$tipo,$ref='',$valore=0,$meta=[]){
  $email=dr_lead_norm_email($email);
  if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return false;
  $lead=dr_lead_get($pdo,$email); if(!$lead){ $lead=dr_lead_upsert($pdo,$email,'','evento'); }
  $lid=(int)($lead['id']??0);
  $pdo->prepare("INSERT INTO lead_events(lead_id,email,tipo,ref,valore,meta)
                 VALUES(?,?,?,?,?,?)")
      ->execute([$lid,$email,$tipo,$ref,(float)$valore, $meta?json_encode($meta,JSON_UNESCAPED_UNICODE):null]);
  $pdo->prepare("UPDATE leads SET ultimo_comportamento=?, ultima_interazione=datetime('now'), updated=datetime('now') WHERE email=?")
      ->execute([$tipo,$email]);
  dr_crm_cortex_log('lead_event',['email'=>$email,'tipo'=>$tipo,'ref'=>$ref,'valore'=>$valore]);
  dr_lead_score($pdo,$email);
  return true;
}

/* =====================================================================
   dr_lead_link_orders — collega gli ORDINI (tabella orders) al lead.
   Aggiorna ordini pagati + LTV. orders.customer contiene spesso l'email
   o un JSON con l'email: cerchiamo in modo tollerante.
   ===================================================================== */
function dr_lead_link_orders($pdo,$email){
  $email=dr_lead_norm_email($email);
  try{
    $q=$pdo->prepare("SELECT COALESCE(SUM(total_eur),0) tot, COUNT(*) n
        FROM orders
        WHERE LOWER(status)='paid'
          AND ( LOWER(customer)=? OR LOWER(customer) LIKE ? )");
    $q->execute([$email, '%'.$email.'%']);
    $r=$q->fetch(PDO::FETCH_ASSOC) ?: ['tot'=>0,'n'=>0];
    $pdo->prepare("UPDATE leads SET ltv_eur=?, ordini=? WHERE email=?")
        ->execute([(float)$r['tot'],(int)$r['n'],$email]);
    return ['ltv'=>(float)$r['tot'],'ordini'=>(int)$r['n']];
  }catch(Exception $e){ return ['ltv'=>0,'ordini'=>0]; }
}

/* =====================================================================
   dr_lead_score — RICALCOLO stato + punteggio.
   Regola DR:
     CALDO   = ha acquistato di recente (<=120gg) OPPURE score>=60 con
               interazione negli ultimi 14gg
     TIEPIDO = ha aperto/cliccato di recente OPPURE iscritto attivo,
               interazione negli ultimi 45gg (score>=20)
     FREDDO  = nessuna interazione recente (o consenso revocato)
   Il punteggio pesa: acquisti, click, aperture, visite, recency, LTV.
   ===================================================================== */
function dr_lead_score($pdo,$email){
  $email=dr_lead_norm_email($email);
  $lead=dr_lead_get($pdo,$email); if(!$lead) return null;

  /* eventi per tipo negli ultimi 90 giorni */
  $ev=$pdo->prepare("SELECT tipo, COUNT(*) c, MAX(created) last
       FROM lead_events WHERE email=? AND created>datetime('now','-90 days') GROUP BY tipo");
  $ev->execute([$email]);
  $cnt=['apertura'=>0,'click'=>0,'acquisto'=>0,'visita'=>0,'iscrizione'=>0,'carrello'=>0,'login'=>0];
  foreach($ev as $row){ $cnt[$row['tipo']]=(int)$row['c']; }

  /* recency: giorni dall'ultima interazione */
  $li=$lead['ultima_interazione'] ?: $lead['created'];
  $days = $li ? max(0,(int)floor((time()-strtotime($li))/86400)) : 999;

  /* acquisto pagato recente (dal profilo ordini + eventi acquisto) */
  $ordN = (int)$lead['ordini'];
  $ltv  = (float)$lead['ltv_eur'];
  $buyRecent = false;
  try{
    $b=$pdo->prepare("SELECT 1 FROM orders WHERE LOWER(status)='paid'
        AND (LOWER(customer)=? OR LOWER(customer) LIKE ?)
        AND created>datetime('now','-120 days') LIMIT 1");
    $b->execute([$email,'%'.$email.'%']); $buyRecent=(bool)$b->fetchColumn();
  }catch(Exception $e){}
  if($cnt['acquisto']>0) $buyRecent=true;

  /* iscritto attivo? (subscribers.status) */
  $subActive=false;
  try{
    $s=$pdo->prepare("SELECT 1 FROM subscribers WHERE email=? AND COALESCE(status,'active')<>'unsub' LIMIT 1");
    $s->execute([$email]); $subActive=(bool)$s->fetchColumn();
  }catch(Exception $e){}

  /* ---- punteggio numerico ---- */
  $score  = 0;
  $score += $cnt['acquisto']*40;
  $score += min(30, $cnt['click']*8);
  $score += min(20, $cnt['apertura']*3);
  $score += min(10, $cnt['visita']*2);
  $score += min(10, $cnt['login']*2);
  $score += ($subActive?8:0);
  $score += min(20, (int)floor($ltv/50));          // +1 ogni 50€ di LTV (max 20)
  /* decadimento per inattività */
  if($days<=14)      $score += 10;
  elseif($days<=45)  $score += 4;
  elseif($days>120)  $score -= 15;
  if((int)$lead['consenso']===0) $score = 0;         // niente consenso -> freddo
  $score = max(0, min(120, $score));

  /* ---- stato dal punteggio + regole di recency ---- */
  if(((int)$lead['consenso'])===0){
    $stato='freddo';
  } elseif( ($buyRecent) || ($score>=60 && $days<=14) ){
    $stato='caldo';
  } elseif( ($score>=20 && $days<=45) || ($subActive && ($cnt['apertura']+$cnt['click'])>0 && $days<=60) ){
    $stato='tiepido';
  } else {
    $stato='freddo';
  }

  $pdo->prepare("UPDATE leads SET score=?, stato_lead=?, updated=datetime('now') WHERE email=?")
      ->execute([(int)$score,$stato,$email]);
  return ['score'=>(int)$score,'stato'=>$stato,'giorni_inattivo'=>$days];
}

/* =====================================================================
   dr_lead_tag — aggiunge o rimuove un tag (array JSON in leads.tags)
   ===================================================================== */
function dr_lead_tag($pdo,$email,$tag,$remove=false){
  $email=dr_lead_norm_email($email); $tag=trim((string)$tag); if($tag==='') return false;
  $lead=dr_lead_get($pdo,$email); if(!$lead){ $lead=dr_lead_upsert($pdo,$email,'','tag'); }
  $tags=json_decode($lead['tags']??'[]',true); if(!is_array($tags)) $tags=[];
  if($remove){ $tags=array_values(array_filter($tags,function($t)use($tag){ return $t!==$tag; })); }
  elseif(!in_array($tag,$tags,true)){ $tags[]=$tag; }
  $pdo->prepare("UPDATE leads SET tags=?, updated=datetime('now') WHERE email=?")
      ->execute([json_encode(array_values($tags),JSON_UNESCAPED_UNICODE),$email]);
  return $tags;
}
function dr_lead_has_tag($lead,$tag){
  $tags=json_decode($lead['tags']??'[]',true); return is_array($tags)&&in_array($tag,$tags,true);
}

/* =====================================================================
   dr_lead_segment — ritorna i lead di un segmento.
   $seg: 'caldi'|'tiepidi'|'freddi'|'tutti'|'attivi'
         'tag:VIP'  'fonte:signup'  'compleanno:oggi'
   ===================================================================== */
function dr_lead_segment($pdo,$seg='tutti',$limit=500){
  $seg=trim((string)$seg); $limit=(int)$limit;
  $map=['caldi'=>'caldo','tiepidi'=>'tiepido','freddi'=>'freddo'];
  if(isset($map[$seg])){
    $q=$pdo->prepare("SELECT * FROM leads WHERE stato_lead=? AND consenso=1 ORDER BY score DESC, updated DESC LIMIT ".$limit);
    $q->execute([$map[$seg]]); return $q->fetchAll(PDO::FETCH_ASSOC);
  }
  if(strpos($seg,'tag:')===0){
    $tag=substr($seg,4);
    $q=$pdo->prepare("SELECT * FROM leads WHERE consenso=1 AND tags LIKE ? ORDER BY score DESC LIMIT ".$limit);
    $q->execute(['%"'.$tag.'"%']); return $q->fetchAll(PDO::FETCH_ASSOC);
  }
  if(strpos($seg,'fonte:')===0){
    $f=substr($seg,6);
    $q=$pdo->prepare("SELECT * FROM leads WHERE fonte=? ORDER BY updated DESC LIMIT ".$limit);
    $q->execute([$f]); return $q->fetchAll(PDO::FETCH_ASSOC);
  }
  if($seg==='compleanno:oggi'){
    $md=date('m-d');
    $q=$pdo->prepare("SELECT * FROM leads WHERE compleanno IS NOT NULL AND compleanno<>'' AND substr(compleanno,6,5)=? AND consenso=1 LIMIT ".$limit);
    $q->execute([$md]); return $q->fetchAll(PDO::FETCH_ASSOC);
  }
  if($seg==='attivi'){
    return $pdo->query("SELECT * FROM leads WHERE consenso=1 AND stato_lead<>'freddo' ORDER BY score DESC LIMIT ".$limit)->fetchAll(PDO::FETCH_ASSOC);
  }
  return $pdo->query("SELECT * FROM leads ORDER BY updated DESC LIMIT ".$limit)->fetchAll(PDO::FETCH_ASSOC);
}

/* =====================================================================
   dr_crm_stats — conteggi per la dashboard/API
   ===================================================================== */
function dr_crm_stats($pdo){
  $g=function($q,$a=[])use($pdo){ $s=$pdo->prepare($q); $s->execute($a); return $s->fetchColumn(); };
  return [
    'lead_totali'  => (int)$g("SELECT COUNT(*) FROM leads"),
    'caldi'        => (int)$g("SELECT COUNT(*) FROM leads WHERE stato_lead='caldo'"),
    'tiepidi'      => (int)$g("SELECT COUNT(*) FROM leads WHERE stato_lead='tiepido'"),
    'freddi'       => (int)$g("SELECT COUNT(*) FROM leads WHERE stato_lead='freddo'"),
    'con_consenso' => (int)$g("SELECT COUNT(*) FROM leads WHERE consenso=1"),
    'clienti'      => (int)$g("SELECT COUNT(*) FROM leads WHERE ordini>0"),
    'ltv_totale'   => (float)$g("SELECT COALESCE(SUM(ltv_eur),0) FROM leads"),
    'eventi_30g'   => (int)$g("SELECT COUNT(*) FROM lead_events WHERE created>datetime('now','-30 days')"),
    'run_attivi'   => (int)$g("SELECT COUNT(*) FROM automation_runs WHERE stato='attivo'"),
    'flussi'       => (int)$g("SELECT COUNT(*) FROM automations WHERE active=1"),
  ];
}

/* =====================================================================
   dr_crm_resync_all — ricostruisce i lead dai dati esistenti del sito
   (subscribers + users + orders). Idempotente: usa upsert.
   Comodo al primo avvio del motore.
   ===================================================================== */
function dr_crm_resync_all($pdo,$limit=5000){
  $n=0;
  try{ foreach($pdo->query("SELECT email,name,list FROM subscribers WHERE email IS NOT NULL AND email<>'' LIMIT ".(int)$limit) as $s){
        dr_lead_upsert($pdo,$s['email'],$s['name']??'', $s['list']?('lista:'.$s['list']):'newsletter'); $n++;
      } }catch(Exception $e){}
  try{ foreach($pdo->query("SELECT email,full_name,birth_date FROM users WHERE email IS NOT NULL AND email<>'' LIMIT ".(int)$limit) as $u){
        dr_lead_upsert($pdo,$u['email'],$u['full_name']??'','utente',['compleanno'=>$u['birth_date']?:null]); $n++;
      } }catch(Exception $e){}
  return $n;
}
