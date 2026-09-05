<?php
/* ============================================================
   DESTINO RANDAGIO — EMAIL/CRM ENGINE · API JSON (admin)
   Endpoint di sola lettura + azioni leggere, protetti da chiave .env.
   Autenticazione: ?key=XXXX  con  XXXX == dr_env('DR_CRM_KEY')
   (NIENTE segreti nel codice: la chiave sta solo nel .env)
   Azioni (?action=):
     stats                              -> conteggi caldi/tiepidi/freddi, conversioni
     segment&seg=caldi|tiepidi|freddi|attivi|tag:VIP|fonte:signup
     lead&email=...                     -> dettaglio lead + eventi + ordini
     tag&email=...&tag=...&remove=0|1   -> aggiunge/rimuove un tag
     event&email=...&tipo=...&ref=...   -> registra un comportamento
     resync                             -> ricostruisce i lead dai dati sito
   ============================================================ */

require_once __DIR__.'/crm-lib.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');

/* ---- auth ---- */
$key = (string)($_GET['key'] ?? ($_POST['key'] ?? ''));
$secret = (string)dr_env('DR_CRM_KEY','');
if($secret==='' || !hash_equals($secret,$key)){
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'chiave non valida o DR_CRM_KEY non configurata nel .env']);
  exit;
}

$action = $_GET['action'] ?? 'stats';
$out = ['ok'=>true,'action'=>$action];

try{
  switch($action){

    case 'stats':
      $out['stats'] = dr_crm_stats($pdo);
      break;

    case 'segment':
      $seg = $_GET['seg'] ?? 'tutti';
      $lim = min(2000,(int)($_GET['limit'] ?? 500));
      $rows = dr_lead_segment($pdo,$seg,$lim);
      /* alleggerisce l'output */
      $out['segment']=$seg;
      $out['count']=count($rows);
      $out['leads']=array_map(function($r){
        return ['email'=>$r['email'],'nome'=>$r['nome'],'stato'=>$r['stato_lead'],
                'score'=>(int)$r['score'],'tags'=>json_decode($r['tags']??'[]',true),
                'fonte'=>$r['fonte'],'ordini'=>(int)$r['ordini'],'ltv_eur'=>(float)$r['ltv_eur'],
                'ultima_interazione'=>$r['ultima_interazione']];
      },$rows);
      break;

    case 'lead':
      $email = dr_lead_norm_email($_GET['email'] ?? '');
      $lead = dr_lead_get($pdo,$email);
      if(!$lead){ $out['ok']=false; $out['error']='lead non trovato'; break; }
      $lead['tags']=json_decode($lead['tags']??'[]',true);
      $ev=$pdo->prepare("SELECT tipo,ref,valore,created FROM lead_events WHERE email=? ORDER BY id DESC LIMIT 50");
      $ev->execute([$email]);
      $ord=$pdo->prepare("SELECT ref,total_eur,status,created FROM orders WHERE LOWER(customer)=? OR LOWER(customer) LIKE ? ORDER BY id DESC LIMIT 50");
      $ord->execute([$email,'%'.$email.'%']);
      $out['lead']=$lead;
      $out['eventi']=$ev->fetchAll(PDO::FETCH_ASSOC);
      $out['ordini']=$ord->fetchAll(PDO::FETCH_ASSOC);
      $out['punteggio']=dr_lead_score($pdo,$email);
      break;

    case 'tag':
      $email = dr_lead_norm_email($_GET['email'] ?? '');
      $tag   = trim((string)($_GET['tag'] ?? ''));
      $rem   = (int)($_GET['remove'] ?? 0)===1;
      if(!$email || !$tag){ $out['ok']=false; $out['error']='email/tag mancanti'; break; }
      $out['tags']=dr_lead_tag($pdo,$email,$tag,$rem);
      break;

    case 'event':
      $email = dr_lead_norm_email($_GET['email'] ?? '');
      $tipo  = trim((string)($_GET['tipo'] ?? ''));
      $ref   = (string)($_GET['ref'] ?? '');
      $val   = (float)($_GET['valore'] ?? 0);
      if(!$email || !$tipo){ $out['ok']=false; $out['error']='email/tipo mancanti'; break; }
      dr_lead_event($pdo,$email,$tipo,$ref,$val);
      $out['lead']=dr_lead_get($pdo,$email);
      break;

    case 'resync':
      $out['sincronizzati']=dr_crm_resync_all($pdo);
      $out['stats']=dr_crm_stats($pdo);
      break;

    default:
      $out['ok']=false; $out['error']='azione sconosciuta';
  }
}catch(Exception $e){
  http_response_code(500);
  $out=['ok'=>false,'error'=>'errore interno'];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
