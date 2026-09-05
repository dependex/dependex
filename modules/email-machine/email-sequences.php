<?php
/* ============================================================
   Destino Randagio — SEQUENZE EMAIL MARKETING
   Welcome · Compleanno · Nurturing (storytelling 5 capitoli) · Promo
   Dispatcher/cron idempotente (usa emails_log per non duplicare).
   Cron Hostinger settimanale/giornaliero:  /email-sequences.php?run=1&key=DRMAIL
   ============================================================ */
require_once __DIR__.'/db.php';
require_once __DIR__.'/email-templates.php';
require_once __DIR__.'/gamification.php';

/* promemoria missioni/rinnovo del mese */
function dr_email_reminder($name,$missing){
  $li=''; foreach($missing as $m){ $li.='<li>'.$m.'</li>'; }
  $b = h1('⏳ '.$name.', il tuo rango ha bisogno di te').
   '<p>Il mese sta per chiudersi. Per <b>confermare il tuo rango</b> nel Branco (e non scendere) ti mancano queste azioni:</p>
    <ul style="color:#dcdcdc;font-size:15px;line-height:1.8">'.$li.'</ul>
    <p style="color:#ffce9e"><b>Ricorda:</b> senza membership attiva esci dal Branco; completando tutte le missioni ricevi <b>+2.000 DRX</b>.</p>'.
   dr_btn('Completa le missioni →', DR_SITE.'/account.php').
   '<p style="color:#9aa0a6;font-size:13px">Un randagio non molla a fine mese. Prende quello che è suo. 🐺</p>';
  return dr_email_layout('Conferma il tuo rango nel Branco — ti mancano poche azioni', $b);
}

/* ---------- COMPLEANNO ---------- */
function dr_email_birthday($name){
  $b = h1('Buon compleanno, '.gold($name).' 🎂').
   '<p>Oggi è il tuo giorno — e il Branco non se lo dimentica.</p>
    <p>Chi cammina con noi merita un regalo: usa il codice qui sotto e prenditi qualcosa che ti somiglia. Perché festeggiare, per un randagio, è ricordarsi di quanta strada ha fatto.</p>
    <div style="text-align:center;background:linear-gradient(135deg,#1c1608,#0e0b04);border:2px solid #D4AF37;border-radius:16px;padding:22px;margin:16px 0">
      <div style="color:#fff">REGALO DI COMPLEANNO</div>
      <div style="color:#D4AF37;font-size:46px;font-weight:900;line-height:1">-15%</div>
      <div style="font-family:monospace;font-size:20px;color:#160f00;background:#D4AF37;border-radius:8px;padding:6px 16px;display:inline-block;margin-top:6px;font-weight:bold">DR-BDAY-15</div>
    </div>'.
   dr_btn('Festeggia nello Shop →', DR_SITE.'/shop.html').
   '<p style="color:#9aa0a6;font-size:13px">Tanti auguri dal Delta. — Destino Randagio ♛</p>';
  return dr_email_layout('Buon compleanno dal Branco — il tuo regalo è dentro', $b);
}

/* ---------- NURTURING / STORYTELLING (5 capitoli) ---------- */
function dr_email_nurture($name,$step){
  $S = [
   1=>['Capitolo I — Da dove vengo',
       '<p>'.$name.', prima di essere musica, ero fango.</p><p>Il Delta del Po è terra di mezzo: né fiume né mare, né sconfitta né vittoria. Un posto dove o affoghi o impari a nuotare contro corrente. Io ho imparato. E ogni canzone che scrivo parte da lì — dal punto più basso, quello da cui si può solo salire.</p><p>Nei prossimi giorni ti racconto come è nato Destino Randagio. Resta con me.</p>',
       'Ascolta il primo album', DR_SITE.'/index.html#album'],
   2=>['Capitolo II — Il lupo e il fiume',
       '<p>Un randagio non ha un padrone, ma non è solo: ha il suo istinto e la sua strada.</p><p>Ho scelto il lupo perché è ciò che siamo quando smettiamo di chiedere permesso. Il fiume insegna a scorrere; il lupo insegna a resistere. Insieme fanno una cosa sola: <b>chi cade nel Delta, si rialza più forte.</b></p>',
       'Scopri la Storia', DR_SITE.'/storia.html'],
   3=>['Capitolo III — La musica che cura',
       '<p>Non scrivo per le classifiche. Scrivo per chi è sveglio alle 3 di notte con troppi pensieri.</p><p>Ogni brano è una mano tesa: prende il caos, le ferite, i momenti neri, e li trasforma in ritornelli che restano. Se ti sei mai sentito fuori posto, questa musica è per te. Non prometto miracoli — prometto parole che risuonano.</p>',
       'Ascolta ora', DR_SITE.'/index.html#album'],
   4=>['Capitolo IV — Il Branco',
       '<p>Da solo sopravvivi. Nel Branco domini.</p><p>Il Branco è la nostra community privata: fratelli e sorelle, anteprime assolute, drop riservati, voto nella DAO e il token <b>DRX</b>. Non è un gruppo: è un patto. E il posto, dentro, non si compra — si merita.</p>',
       'Entra nel Branco →', DR_SITE.'/branco.html'],
   5=>['Capitolo V — Il tuo posto',
       '<p>'.$name.', sei arrivato fin qui. Non è un caso.</p><p>Ora scegli: resti spettatore, o prendi il tuo posto nella storia? Membership, NFT, la tua canzone su misura — ogni porta ti fa entrare più a fondo nel Branco, e ogni euro ti torna in <b>cashback e DRX</b>.</p><p><b>Un randagio non aspetta il momento giusto. Lo crea.</b></p>',
       'Prendi il tuo posto →', DR_SITE.'/membership.html'],
  ];
  $s=$S[$step] ?? $S[1];
  $b = h1($s[0]).'<div style="color:#dcdcdc;font-size:15px;line-height:1.75">'.$s[1].'</div>'.dr_btn($s[2].' →',$s[3]);
  return dr_email_layout($s[0], $b);
}

/* ---------- DISPATCHER (cron) ---------- */
if(isset($_GET['run']) && ($_GET['key']??'')==='DRMAIL'){
  header('Content-Type: application/json; charset=utf-8');
  @set_time_limit(0);
  $sent=['welcome'=>0,'birthday'=>0,'nurture'=>0];
  function logged($pdo,$kind,$dest){ $st=$pdo->prepare("SELECT 1 FROM emails_log WHERE kind=? AND dest=? AND created>date('now','-1 day')"); $st->execute([$kind,$dest]); return (bool)$st->fetchColumn(); }
  function logsend($pdo,$kind,$dest,$subj){ $pdo->prepare("INSERT INTO emails_log(kind,dest,subject,status) VALUES(?,?,?,'sent')")->execute([$kind,$dest,$subj]); }

  /* 1) WELCOME ai nuovi iscritti */
  foreach($pdo->query("SELECT id,email FROM subscribers WHERE COALESCE(welcomed,0)=0 LIMIT 100") as $s){
    $html=dr_email_welcome('Randagio');
    if(dr_send_html($s['email'],'Benvenuto nel Branco — il tuo regalo ti aspetta',$html)){ $sent['welcome']++; }
    $pdo->prepare("UPDATE subscribers SET welcomed=1 WHERE id=?")->execute([$s['id']]);
    logsend($pdo,'welcome',$s['email'],'welcome');
  }

  /* 2) COMPLEANNO (utenti con birth_date = oggi, mese-giorno) */
  $md=date('m-d');
  foreach($pdo->query("SELECT full_name,email,birth_date FROM users WHERE birth_date IS NOT NULL AND birth_date<>''") as $u){
    if(!$u['email']) continue;
    if(substr((string)$u['birth_date'],5,5)===$md && !logged($pdo,'birthday',$u['email'])){
      $nm=trim(explode(' ',$u['full_name']??'')[0]?:'Randagio');
      if(dr_send_html($u['email'],'🎂 Buon compleanno dal Branco — regalo dentro',dr_email_birthday($nm))){ $sent['birthday']++; logsend($pdo,'birthday',$u['email'],'birthday'); }
    }
  }

  /* 3) NURTURING: capitolo in base ai giorni dall'iscrizione (1,3,6,10,15 giorni) */
  $steps=[1=>1,3=>2,6=>3,10=>4,15=>5];
  foreach($pdo->query("SELECT email,created FROM subscribers WHERE COALESCE(welcomed,0)=1") as $s){
    if(!$s['email']) continue;
    $days=(int)floor((time()-strtotime($s['created']))/86400);
    if(isset($steps[$days])){
      $kind='nurture'.$steps[$days];
      if(!logged($pdo,$kind,$s['email'])){
        if(dr_send_html($s['email'],'Destino Randagio — '.($steps[$days]).'° capitolo',dr_email_nurture('Randagio',$steps[$days]))){ $sent['nurture']++; logsend($pdo,$kind,$s['email'],$kind); }
      }
    }
  }

  /* 4) PROMEMORIA missioni/rinnovo ai membri con azioni incomplete */
  foreach($pdo->query("SELECT id,email,full_name FROM users WHERE COALESCE(membership_active,0)=1 AND role<>'admin'") as $u){
    if(!$u['email'] || logged($pdo,'reminder',$u['email'])) continue;
    $miss=dr_month_missions($pdo,(int)$u['id']); $missing=[];
    foreach($miss as $m){ if(!$m['done']) $missing[]=$m['t']; }
    if($missing){
      $nm=trim(explode(' ',$u['full_name']??'')[0]?:'Randagio');
      if(dr_send_html($u['email'],'⏳ Conferma il tuo rango nel Branco',dr_email_reminder($nm,$missing))){ $sent['reminder']=($sent['reminder']??0)+1; logsend($pdo,'reminder',$u['email'],'reminder'); }
    }
  }
  echo json_encode(['ok'=>true,'sent'=>$sent], JSON_UNESCAPED_UNICODE);
  exit;
}
