<?php
/* API DASHBOARD — dati REALI per dashboard-user e dashboard-admin.
   ?scope=user  (sessione utente)   ?scope=admin (sessione admin)
   Nessun numero inventato: tutto da DB. Se un dato non c'e', torna 0/lista vuota. */
session_start();
require_once __DIR__.'/db.php';
require_once __DIR__.'/drx.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
function q1($pdo,$sql,$d=0){ try{ $v=$pdo->query($sql)->fetchColumn(); return $v===false?$d:$v; }catch(Exception $e){ return $d; } }
function qa($pdo,$sql){ try{ return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC)?:[]; }catch(Exception $e){ return []; } }
function eur($drx){ $r = defined('DRX_RISCATTO_PER_EUR')?DRX_RISCATTO_PER_EUR:10; return round($drx/max(1,$r),2); }

$scope = $_GET['scope'] ?? 'user';

if($scope==='admin'){
  if(empty($_SESSION['is_admin']) && (($_SESSION['role']??'')!=='admin')){ http_response_code(403); echo '{"ok":false}'; exit; }
  $tot=(int)q1($pdo,"SELECT COUNT(*) FROM users");
  $att=(int)q1($pdo,"SELECT COUNT(*) FROM users WHERE membership_active=1");
  $act=(int)q1($pdo,"SELECT COUNT(DISTINCT uid) FROM drx_ledger WHERE created >= date('now','-30 day')");
  $ven=(float)q1($pdo,"SELECT COALESCE(SUM(total_eur),0) FROM orders WHERE status IN ('paid','completed','shipped','delivered')");
  $emes=(float)q1($pdo,"SELECT COALESCE(SUM(delta),0) FROM drx_ledger WHERE delta>0");
  $saldo=(float)q1($pdo,"SELECT COALESCE(SUM(balance),0) FROM drx_balances");
  $pub=(int)q1($pdo,"SELECT COUNT(*) FROM fan_reviews WHERE verification_status='PUBLISHED'");
  $mod=(int)q1($pdo,"SELECT COUNT(*) FROM fan_reviews WHERE verification_status='PENDING'");
  $TARGET=8181;
  $modItems=[]; foreach(qa($pdo,"SELECT display_name,target,created FROM fan_reviews WHERE verification_status='PENDING' ORDER BY id DESC LIMIT 5") as $r)
    $modItems[]=['autore'=>$r['display_name']??'—','prodotto'=>$r['target']??'—','quando'=>$r['created']??''];
  $sales=[]; foreach(qa($pdo,"SELECT id,order_number,total_eur,created FROM orders ORDER BY id DESC LIMIT 5") as $r)
    $sales[]=['id'=>$r['order_number']??('#'.$r['id']),'prodotto'=>'Ordine','importo'=>'€ '.number_format((float)$r['total_eur'],2,',','.'),'quando'=>$r['created']??''];
  $acts=[]; foreach(qa($pdo,"SELECT uid,delta,reason,created FROM drx_ledger ORDER BY id DESC LIMIT 5") as $r)
    $acts[]=['titolo'=>$r['reason'],'dettaglio'=>($r['delta']>0?'+':'').(int)$r['delta'].' DRX · utente #'.$r['uid'],'quando'=>$r['created']];
  /* ---------------------------------------------------------------------
     STATO REALE, NON DICHIARATO.
     Prima questi due blocchi erano array scritti a mano: la dashboard diceva
     "Backup giornaliero: Attiva" mentre il job non esisteva nemmeno. Una
     dashboard che mente e' peggio di una dashboard vuota, perche' ci si fa
     affidamento. Ora ogni riga viene da una verifica.
     --------------------------------------------------------------------- */
  $dataDir = __DIR__.'/data';

  /* Un'automazione e' "attiva" se il suo file di flag esiste ed e' recente:
     e' lo scheduler stesso a scriverlo dopo aver lanciato il job. */
  $vis = function($flag,$maxOre) use($dataDir){
    $p = $dataDir.'/'.$flag;
    if(!is_file($p)) return ['Mai eseguita','—'];
    $t = @filemtime($p) ?: 0;
    $ore = $t ? (time()-$t)/3600 : 999;
    return [$ore <= $maxOre ? 'Attiva' : 'In ritardo',
            $t ? date('d/m H:i', $t) : '—'];
  };

  $dbOk = false;
  try { $pdo->query("SELECT 1"); $dbOk = true; } catch(Exception $e){}

  /* Telegram: lo stato vero sta nelle impostazioni del bot, non in una stringa. */
  $tgStato='DA CONFIGURARE'; $tgNota='bot non collegato';
  try{
    if(is_file(__DIR__.'/inc/telegram-lib.php')){
      require_once __DIR__.'/inc/telegram-lib.php';
      if(function_exists('tg_enabled')){
        $on = tg_enabled($pdo);
        $dry = function_exists('tg_dry_run') ? tg_dry_run($pdo) : true;
        $tgStato = !$on ? 'SPENTO' : ($dry ? 'DRY-RUN' : 'OK');
        $tgNota  = !$on ? 'disattivato' : ($dry ? 'invii solo all\'admin' : 'pubblica sul canale');
      }
    }
  }catch(Exception $e){}

  /* Web3: se non c'e' un indirizzo di contratto, non e' "TESTNET": e' spento. */
  $w3Stato='NON DEPLOYATO'; $w3Nota='nessun contratto in web3_config';
  try{
    /* La colonna e' 'v', non 'valore': con il nome sbagliato la query falliva
       dentro il catch e la dashboard avrebbe detto per sempre "NON DEPLOYATO",
       che e' il difetto che questo blocco doveva togliere. */
    $n=(int)q1($pdo,"SELECT COUNT(*) FROM web3_config WHERE v IS NOT NULL AND v<>''");
    if($n>0){ $w3Stato='TESTNET'; $w3Nota=$n.' indirizzi configurati'; }
  }catch(Exception $e){}

  $smtp = getenv('SMTP_HOST') ?: (defined('MKT_SMTP_HOST') ? MKT_SMTP_HOST : '');

  $health=[['modulo'=>'Database','stato'=>$dbOk?'OK':'ERRORE','nota'=>$dbOk?'operativo':'query fallita'],
           ['modulo'=>'Email Service','stato'=>$smtp?'OK':'DA CONFIGURARE','nota'=>$smtp?'SMTP configurato':'nessuna chiave SMTP nel .env'],
           ['modulo'=>'Pagamenti','stato'=>(defined('PAYPAL_CLIENT_ID')&&PAYPAL_CLIENT_ID)?'OK':'DA CONFIGURARE','nota'=>'PayPal/USDT'],
           ['modulo'=>'Printful','stato'=>(defined('PRINTFUL_TOKEN')&&PRINTFUL_TOKEN)?'OK':'DA CONFIGURARE','nota'=>'shop'],
           ['modulo'=>'Telegram','stato'=>$tgStato,'nota'=>$tgNota],
           ['modulo'=>'Web3 Polygon','stato'=>$w3Stato,'nota'=>$w3Nota]];

  $autos=[];
  foreach([['Blog automatico','sched_blog.txt',36],
           ['Nomads sync','sched_nomads.txt',36],
           ['Rendita / Fedeltà','sched_yield.txt',36],
           ['Backup giornaliero','sched_backup.txt',36],
           ['Carrelli abbandonati','sched_carrelli.txt',2],
           ['Telegram programmati','sched_telegram.txt',2],
           ['Email marketing','sched_mkt.txt',2],
           ['Tavola a fumetti','sched_tales.txt',36]] as $a){
    [$st,$quando] = $vis($a[1],$a[2]);
    $autos[] = ['nome'=>$a[0],'stato'=>$st,'ultima'=>$quando];
  }
  echo json_encode(['ok'=>true,
    'kpis'=>[
      'utentiTotali'=>['valore'=>number_format($tot,0,',','.'),'delta'=>''],
      'utentiAttivi'=>['valore'=>number_format($act,0,',','.'),'delta'=>'ultimi 30gg'],
      'membershipAttive'=>['valore'=>number_format($att,0,',','.'),'delta'=>''],
      'venditeTotali'=>['valore'=>'€ '.number_format($ven,0,',','.'),'delta'=>''],
      'drxEmessi'=>['valore'=>number_format($emes,0,',','.'),'delta'=>''],
      'recensioniPubblicate'=>['valore'=>number_format($pub,0,',','.'),'delta'=>'obiettivo '.number_format($TARGET,0,',','.')],
    ],
    'reviewsProgress'=>['pubblicate'=>$pub,'obiettivo'=>$TARGET,'percentuale'=>$TARGET?round($pub/$TARGET*100,1):0],
    'moderationQueue'=>['totale'=>$mod,'items'=>$modItems],
    'sales'=>$sales,'activities'=>$acts,'health'=>$health,'automations'=>$autos,
    'errors'=>['critici'=>0,'items'=>[]],
    'wallet'=>['saldoTotaleDrx'=>number_format($saldo,0,',','.'),'controvaloreEur'=>'≈ € '.number_format(eur($saldo),2,',','.')],
    'usersTrend'=>['periodo'=>'30G'],
  ], JSON_UNESCAPED_UNICODE); exit;
}

/* ---------- USER ---------- */
$uid=(int)($_SESSION['uid'] ?? 0);
if(!$uid){ http_response_code(401); echo '{"ok":false}'; exit; }
$u=$pdo->query("SELECT * FROM users WHERE id=$uid")->fetch(PDO::FETCH_ASSOC)?:[];
$bal = function_exists('drx_balance')? drx_balance($pdo,$uid):0;
$e   = function_exists('drx_effective')? drx_effective($pdo,$uid):['idx'=>0,'rank'=>['name'=>'🐣 Randagio'],'next'=>null];
$next= $e['next'] ?? null;
$cur = $e['rank'];
$span= $next? max(1,$next['min']-$cur['min']) :1;
$pct = $next? max(0,min(100, (($bal-$cur['min'])/$span)*100)) :100;
$streak=(int)q1($pdo,"SELECT streak FROM gm_streak WHERE user_id=$uid");
$badge =(int)q1($pdo,"SELECT COUNT(*) FROM gm_badge WHERE user_id=$uid");
$membri=(int)q1($pdo,"SELECT COUNT(*) FROM users WHERE membership_active=1");
$misTot=8; $misFatte=(int)q1($pdo,"SELECT COUNT(*) FROM gm_mission_log WHERE user_id=$uid AND period_key=date('now')");
echo json_encode(['ok'=>true,
  'user'=>['nome'=>$u['full_name'] ?? $u['username'] ?? 'randagio'],
  'rank'=>['attuale'=>$cur['name'],'prossimo'=>$next['name']??'—',
           'drxAttuali'=>number_format($bal,0,',','.'),
           'drxProssimoRank'=>$next? number_format($next['min'],0,',','.') : '—',
           'drxMancanti'=>$next? number_format(max(0,$next['min']-$bal),0,',','.') : '0'],
  'wallet'=>['saldoDrx'=>number_format($bal,0,',','.'),'controvaloreEur'=>'≈ € '.number_format(eur($bal),2,',','.')],
  'kpi'=>['streakGiorni'=>$streak,'missioniAttive'=>$misFatte,'missioniTotali'=>$misTot,
          'badgeOttenuti'=>$badge,'membriBranco'=>$membri],
  'prossimoObiettivo'=>['titolo'=>$next['name']??'Rango massimo',
    'descrizione'=>$next? ('Mancano '.number_format(max(0,$next['min']-$bal),0,',','.').' DRX') : 'Hai raggiunto il vertice del Branco',
    'percentuale'=>round($pct,1)],
], JSON_UNESCAPED_UNICODE);
