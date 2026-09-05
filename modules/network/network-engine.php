<?php
/* ============================================================================
   NETWORK ENGINE — Pipeline del Branco (fondazione, lane CODE 2026-07-29)

   MODELLO (deciso Mirco):
   - RANGHI GLOBALI DEL BRANCO: la scala ufficiale (dr_rank_soglie, 9 ranghi
     0..8). Ogni PIONIERE che attiva il Kit Genesys entra a RANGO 3 (Nomade)
     via rank_floor: non scende mai sotto.
   - STATUS SPECIALI (Genesys): Unicorn / World / National / Pro — assegnati per
     ORDINE DI ATTIVAZIONE, indipendenti dalla scala ranghi. Sono onorifici.
   - PREMI E COMPENSI: SOLO in DRX INTERNI (per tokenomics i DRX non escono in
     denaro fino al 2035). Quindi gamification/status, NON payout monetario.
     Nessun euro pagato per reclutamento in questo motore.
   - ADMIN (Master): controllo totale. SIC-ID Master universale sotto.

   NB: il RECLUTAMENTO (referral/invite) resta il "capitolo a parte in revisione"
   citato in dr-economy-config.php: qui NON tocchiamo quei premi. Gli obblighi di
   mantenimento rango sono ATTIVITA' (gamification), non reclutamento pagato.
============================================================================ */
require_once __DIR__.'/db.php';
@require_once __DIR__.'/dr-economy-config.php';   // dr_rank_soglie()
@require_once __DIR__.'/drx.php';                 // drx_award() idempotente (se presente)

/* ---- SIC-ID MASTER universale (Mirco). In futuro altri Master: si aggiunge. */
if(!defined('DR_MASTER_SIC')) define('DR_MASTER_SIC','SIC-ID-MASTER-000000000000');

/* ---- Nomi ufficiali dei 9 ranghi globali (0..8) + Master(10)=Admin ----
   9 ranghi utente: Randagio(0) .. Leggenda(8). Pioniere = Nomade(3), floor
   permanente. Rango 10 = Master (Admin), sopra la scala. */
function net_rank_nomi(){
  return [0=>'Randagio',1=>'Esploratore',2=>'Viandante',3=>'Nomade',
          4=>'Sentinella',5=>'Custode',6=>'Guardiano',7=>'Alpha',8=>'Leggenda',
          10=>'Master'];
}
if(!defined('DR_RANK_MASTER')) define('DR_RANK_MASTER',10);
/* soglie DRX-merito per rango (fonte unica: dr-economy-config). Fallback prudente. */
function net_rank_soglie(){
  if(function_exists('dr_rank_soglie')) return dr_rank_soglie();
  return [0=>1999,1=>1000000,2=>5000000,3=>20000000,4=>50000000,
          5=>150000000,6=>300000000,7=>500000000,8=>1000000000];
}
/* CAP DRX per rango: tetto di DRX-premio (gamification) accumulabili a quel
   livello prima di dover salire. Scala con la soglia successiva (non oltre). */
function net_drx_cap($rango){
  $s=net_rank_soglie(); $next=$s[$rango+1]??$s[8]; return (int)$next;
}
/* OBBLIGHI mensili per MANTENERE il rango (attivita' = DRX-attivita' nel mese).
   0..2 nessun obbligo; sale col rango. Solo attivita', non reclutamento. */
function net_obblighi($rango){
  $t=[0=>0,1=>0,2=>0,3=>50,4=>120,5=>250,6=>500,7=>900,8=>1500];
  return (int)($t[$rango]??0);
}

/* ---- BENEFIT & LIFESTYLE legati al RANGO (Mirco 2026-08-10) ----
   La struttura del network e' legata alla scala ranghi: piu' sali di rango
   (col MERITO, non comprando), piu' sblocchi vantaggi interni e lifestyle.
   drx_bonus_pct = bonus DRX-gamification sull'attivita' (interno, NON denaro,
   nessuna promessa di guadagno). 'sblocchi' e 'lifestyle' sono esperienze/accessi.
   Fonte unica: usata da dashboard, referral e Covo. */
function net_rank_benefit($rango){
  $rango=(int)$rango;
  $map=[
    0 =>['nome'=>'Randagio',    'drx_bonus_pct'=>0,  'lifestyle'=>'accesso base alla community',        'sblocchi'=>['profilo','chat AI']],
    1 =>['nome'=>'Esploratore', 'drx_bonus_pct'=>2,  'lifestyle'=>'ingresso alle serate del Covo',        'sblocchi'=>['badge Esploratore']],
    2 =>['nome'=>'Viandante',   'drx_bonus_pct'=>4,  'lifestyle'=>'priorita\' nel supporto',              'sblocchi'=>['priorita supporto']],
    3 =>['nome'=>'Nomade',      'drx_bonus_pct'=>6,  'lifestyle'=>'Pioniere: Covo 24 mesi + carta NFT',   'sblocchi'=>['carta NFT Pioniere','Branco 24 mesi']],
    4 =>['nome'=>'Sentinella',  'drx_bonus_pct'=>8,  'lifestyle'=>'sala riservata del Covo',              'sblocchi'=>['sala riservata']],
    5 =>['nome'=>'Custode',     'drx_bonus_pct'=>10, 'lifestyle'=>'drop anticipati + voto rafforzato',    'sblocchi'=>['early drop','voto x2']],
    6 =>['nome'=>'Guardiano',   'drx_bonus_pct'=>12, 'lifestyle'=>'mentorship + eventi live',             'sblocchi'=>['mentorship','eventi live']],
    7 =>['nome'=>'Alpha',       'drx_bonus_pct'=>15, 'lifestyle'=>'backstage + co-decisioni',             'sblocchi'=>['backstage','co-decisioni']],
    8 =>['nome'=>'Leggenda',    'drx_bonus_pct'=>20, 'lifestyle'=>'hall of fame + esperienze esclusive',  'sblocchi'=>['hall of fame','esperienze esclusive']],
    10=>['nome'=>'Master',      'drx_bonus_pct'=>0,  'lifestyle'=>'governance totale',                    'sblocchi'=>['governance']],
  ];
  $b = $map[$rango] ?? $map[0];
  $b['rango']=$rango;
  return $b;
}

/* ---- STATUS speciali Genesys per ordine di attivazione ---- */
function net_status_by_order($n){          /* $n = numero d'attivazione 1..117 (+unicorn) */
  if($n<=0)   return ['Master','SIC-ID-MASTER'];
  if($n<=9)   return ['World','SIC-ID-G'];
  if($n<=36)  return ['National','SIC-ID-G'];
  return ['Pro','SIC-ID-G'];
}

/* ============================================================
   TABELLE — nodi + audit + saldo DRX-network (idempotenti)
   ============================================================ */
function net_migra($pdo){
  $pdo->exec("CREATE TABLE IF NOT EXISTS network_nodes(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uid INTEGER UNIQUE, sic_id TEXT UNIQUE,
    rango INTEGER DEFAULT 3, rank_floor INTEGER DEFAULT 3,
    status TEXT DEFAULT 'Pro',            -- Unicorn|World|National|Pro|Master
    upline_uid INTEGER DEFAULT 0,
    attivazione_n INTEGER DEFAULT 0,       -- ordine d'ingresso (per status)
    drx_merito INTEGER DEFAULT 0,          -- DRX che danno rango
    drx_premi INTEGER DEFAULT 0,           -- DRX gamification (interni)
    attivo_mese INTEGER DEFAULT 0,         -- attivita' del mese corrente
    ultimo_attivo TEXT,
    stato TEXT DEFAULT 'attivo',           -- attivo|inattivo|sospeso|revocato
    creato TEXT DEFAULT (datetime('now')), aggiornato TEXT)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS ix_net_rango ON network_nodes(rango,status)");
  /* Cowork 2026-08-13 notte: indice mancante su upline_uid — ogni "chi sono i
     figli di questo nodo" (Albero, Stella, admin Rete/Nodi) filtrava senza
     indice: con poche centinaia di righe non si vedeva, con milioni sarebbe
     una scansione completa della tabella ad ogni click. Vedi anche l'indice
     gemello ix_users_referrer in db.php (stesso problema sulla tabella users). */
  $pdo->exec("CREATE INDEX IF NOT EXISTS ix_nn_upline ON network_nodes(upline_uid)");
  $pdo->exec("CREATE TABLE IF NOT EXISTS network_audit(
    id INTEGER PRIMARY KEY AUTOINCREMENT, ts TEXT DEFAULT (datetime('now')),
    attore TEXT, azione TEXT, uid INTEGER, dettaglio TEXT)");
}
function net_audit($pdo,$attore,$azione,$uid,$dett=''){
  try{ $pdo->prepare("INSERT INTO network_audit(attore,azione,uid,dettaglio) VALUES(?,?,?,?)")
      ->execute([$attore,$azione,(int)$uid,$dett]); }catch(Throwable $e){}
}

/* Colonne profilo mancanti (idempotenti). Verificato sul DB reale 2026-07-29:
   c'erano gia' full_name/codice_fiscale/email/phone/wallet/avatar/indirizzo/
   sic_id/genesys_sic/genesys_num/card_last4/profile_complete/profile_bonus/
   rank_floor. Mancavano: iban + i flag di verifica/optin. Qui li aggiungo. */
function net_migra_users($pdo){
  foreach(['sic_id TEXT','iban TEXT','email_verified INTEGER DEFAULT 0',
           'phone_verified INTEGER DEFAULT 0','optin INTEGER DEFAULT 0',
           'optin_ts TEXT','pass_updated TEXT'] as $col){
    try{ $pdo->exec("ALTER TABLE users ADD COLUMN ".$col); }catch(Throwable $e){}
  }
}

/* ============================================================
   MASTER — stampa il SIC-ID universale sull'admin (idempotente)
   ============================================================ */
function net_ensure_master($pdo){
  net_migra($pdo);
  try{
    $uid=(int)$pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn();
  }catch(Throwable $e){ $uid=0; }
  if($uid<=0) return ['ok'=>false,'err'=>'Nessun utente admin trovato'];
  net_migra_users($pdo);          /* garantisce sic_id + campi profilo mancanti */
  /* sul record utente */
  $stamp=false;
  try{ $pdo->prepare("UPDATE users SET sic_id=? WHERE id=?")->execute([DR_MASTER_SIC,$uid]); $stamp=true; }catch(Throwable $e){}
  /* nodo Master nella pipeline (radice) */
  $has=(int)$pdo->query("SELECT COUNT(*) FROM network_nodes WHERE uid=".$uid)->fetchColumn();
  if($has){ $pdo->prepare("UPDATE network_nodes SET sic_id=?,status='Master',rango=?,rank_floor=?,attivazione_n=0,aggiornato=datetime('now') WHERE uid=?")->execute([DR_MASTER_SIC,DR_RANK_MASTER,DR_RANK_MASTER,$uid]); }
  else{ $pdo->prepare("INSERT INTO network_nodes(uid,sic_id,rango,rank_floor,status,attivazione_n) VALUES(?,?,?,?,'Master',0)")->execute([$uid,DR_MASTER_SIC,DR_RANK_MASTER,DR_RANK_MASTER]); }
  net_audit($pdo,'system','set_master',$uid,DR_MASTER_SIC);
  return ['ok'=>true,'uid'=>$uid,'sic'=>DR_MASTER_SIC,'stamp_users'=>$stamp];
}

/* ============================================================
   PLACE — inserisce un Pioniere alla sua attivazione Kit
   Rango 3 (Nomade) come floor + status per ordine. Ritorna il nodo.
   ============================================================ */
function net_place($pdo,$uid,$sic_id,$upline_uid=0){
  net_migra($pdo);
  $n=(int)$pdo->query("SELECT COALESCE(MAX(attivazione_n),0)+1 FROM network_nodes WHERE status!='Master'")->fetchColumn();
  list($status,) = net_status_by_order($n);
  $up = $upline_uid>0 ? $upline_uid : (int)$pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn();
  try{
    $pdo->prepare("INSERT OR IGNORE INTO network_nodes(uid,sic_id,rango,rank_floor,status,upline_uid,attivazione_n,ultimo_attivo)
                   VALUES(?,?,3,3,?,?,?,datetime('now'))")
        ->execute([(int)$uid,$sic_id,$status,$up,$n]);
  }catch(Throwable $e){ return ['ok'=>false,'err'=>'insert']; }
  net_audit($pdo,'system','place',$uid,"n=$n status=$status rango=3");
  return ['ok'=>true,'attivazione_n'=>$n,'status'=>$status,'rango'=>3];
}

/* ============================================================
   REWARD — premio in DRX INTERNI (gamification). Idempotente su ref.
   ============================================================ */
function net_reward($pdo,$uid,$drx,$reason,$ref=''){
  net_migra($pdo); $drx=(int)$drx; if($drx<=0) return false;
  if(function_exists('drx_award')){ try{ drx_award($pdo,$uid,$drx,$reason,$ref); }catch(Throwable $e){} }
  try{ $pdo->prepare("UPDATE network_nodes SET drx_premi=drx_premi+?, attivo_mese=attivo_mese+?, ultimo_attivo=datetime('now') WHERE uid=?")
      ->execute([$drx,$drx,(int)$uid]); }catch(Throwable $e){}
  net_audit($pdo,'system','reward',$uid,"$drx · $reason");
  return true;
}

/* ============================================================
   RANK CHECK — sale/scende. Mai sotto rank_floor. DRX-merito = scala.
   Discesa solo se sotto-soglia E obblighi del mese non rispettati.
   ============================================================ */
function net_rank_check($pdo,$uid){
  net_migra($pdo);
  $r=$pdo->prepare("SELECT * FROM network_nodes WHERE uid=?"); $r->execute([(int)$uid]);
  $nd=$r->fetch(PDO::FETCH_ASSOC); if(!$nd) return null;
  $s=net_rank_soglie(); $merito=(int)$nd['drx_merito']; $rango=(int)$nd['rango']; $floor=(int)$nd['rank_floor'];
  /* salita: se merito >= soglia del rango successivo */
  while($rango<8 && $merito >= (int)($s[$rango+1]??PHP_INT_MAX)){ $rango++; }
  /* discesa: sotto la soglia del rango attuale E obblighi non fatti -> -1 (mai < floor) */
  if($rango>$floor && $merito < (int)($s[$rango]??0) && (int)$nd['attivo_mese'] < net_obblighi($rango)){
    $rango=max($floor,$rango-1);
  }
  if($rango!=(int)$nd['rango']){
    $pdo->prepare("UPDATE network_nodes SET rango=?, aggiornato=datetime('now') WHERE uid=?")->execute([$rango,(int)$uid]);
    net_audit($pdo,'system','rank_change',$uid,$nd['rango']." -> ".$rango);
  }
  return ['rango'=>$rango,'nome'=>net_rank_nomi()[$rango],'cap'=>net_drx_cap($rango),'obbligo'=>net_obblighi($rango)];
}

/* Reset mensile dell'attivita' (lo chiama uno scheduler una volta al mese). */
function net_reset_mese($pdo){ net_migra($pdo); try{ $pdo->exec("UPDATE network_nodes SET attivo_mese=0"); }catch(Throwable $e){} }

/* ============================================================
   ADMIN — controllo totale (governo della pipeline)
   ============================================================ */
function net_admin_set_rango($pdo,$uid,$rango,$attore='admin'){
  net_migra($pdo); $rango=max(0,min(8,(int)$rango));
  $pdo->prepare("UPDATE network_nodes SET rango=?, aggiornato=datetime('now') WHERE uid=?")->execute([$rango,(int)$uid]);
  net_audit($pdo,$attore,'admin_set_rango',$uid,(string)$rango); return true;
}
function net_admin_set_status($pdo,$uid,$status,$attore='admin'){
  net_migra($pdo); if(!in_array($status,['Unicorn','World','National','Pro','Master'],true)) return false;
  $pdo->prepare("UPDATE network_nodes SET status=?, aggiornato=datetime('now') WHERE uid=?")->execute([$status,(int)$uid]);
  net_audit($pdo,$attore,'admin_set_status',$uid,$status); return true;
}
function net_admin_move($pdo,$uid,$new_upline,$attore='admin'){
  net_migra($pdo);
  /* Cowork 2026-08-13: salva anche l'upline VECCHIO nel log (prima solo il
     nuovo veniva scritto) — serve per poter offrire "annulla spostamento"
     dal pannello, senza indovinare cosa c'era prima. */
  $old=0;
  try{ $g=$pdo->prepare("SELECT upline_uid FROM network_nodes WHERE uid=?"); $g->execute([(int)$uid]); $old=(int)$g->fetchColumn(); }catch(Throwable $e){}
  $pdo->prepare("UPDATE network_nodes SET upline_uid=?, aggiornato=datetime('now') WHERE uid=?")->execute([(int)$new_upline,(int)$uid]);
  net_audit($pdo,$attore,'admin_move',$uid,'old='.$old.' new='.(int)$new_upline); return true;
}
function net_admin_stato($pdo,$uid,$stato,$attore='admin'){
  net_migra($pdo); if(!in_array($stato,['attivo','inattivo','sospeso','revocato'],true)) return false;
  $pdo->prepare("UPDATE network_nodes SET stato=?, aggiornato=datetime('now') WHERE uid=?")->execute([$stato,(int)$uid]);
  net_audit($pdo,$attore,'admin_stato',$uid,$stato); return true;
}

/* ============================================================
   VISTA — dati per dashboard grafica + tabella (li usera' l'admin panel)
   ============================================================ */
function net_stats($pdo){
  net_migra($pdo);
  $g=function($sql)use($pdo){ try{return (int)$pdo->query($sql)->fetchColumn();}catch(Throwable $e){return 0;} };
  return [
    'totali'    => $g("SELECT COUNT(*) FROM network_nodes WHERE status!='Master'"),
    'unicorn'   => $g("SELECT COUNT(*) FROM network_nodes WHERE status='Unicorn'"),
    'world'     => $g("SELECT COUNT(*) FROM network_nodes WHERE status='World'"),
    'national'  => $g("SELECT COUNT(*) FROM network_nodes WHERE status='National'"),
    'pro'       => $g("SELECT COUNT(*) FROM network_nodes WHERE status='Pro'"),
    'attivi'    => $g("SELECT COUNT(*) FROM network_nodes WHERE stato='attivo'"),
    'inattivi'  => $g("SELECT COUNT(*) FROM network_nodes WHERE stato!='attivo'"),
    'per_rango' => (function()use($pdo){ $o=[]; try{ foreach($pdo->query("SELECT rango,COUNT(*) c FROM network_nodes GROUP BY rango") as $r){ $o[(int)$r['rango']]=(int)$r['c']; } }catch(Throwable $e){} return $o; })(),
  ];
}

/* ============================================================
   BACKFILL — ogni utente registrato deve avere un nodo nella
   pipeline (Cowork 2026-08-13, richiesta Mirco: gli user registrati
   in DB con un proprio SIC-ID devono comparire anche nella struttura
   network reale, non solo i 118 Pionieri).
   Idempotente: salta chi ha gia' un nodo — inclusi tutti i Pionieri,
   gia' piazzati da net_place() dentro genesys_onboard(). Non tocca
   MAI una riga esistente (solo INSERT OR IGNORE su chi ne e' privo).
   I nodi creati qui prendono status='Membro' o 'Buyer' — DUE VALORI
   NUOVI, mai usati prima in questa colonna: non toccano i contatori
   Genesys esistenti (Unicorn/World/National/Pro/Master) ne' net_stats()
   ne' alcuna query gia' in produzione (verificato: solo dr-network-
   tree.php legge net_stats(), come fallback quando network_posti e'
   vuota — sui 118 non succede mai in produzione).
   $dry=true (default) NON scrive nulla: ritorna solo l'anteprima di
   cosa farebbe. Va richiamata con $dry=false per scrivere davvero
   (lo fa solo l'admin, dal pannello, con conferma esplicita).
   ============================================================ */
function net_backfill_tutti($pdo, $dry=true){
  net_migra($pdo);
  $adminUid=0;
  try{ $adminUid=(int)$pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn(); }catch(Throwable $e){}

  /* Cowork 2026-08-13 notte: RISCRITTA su richiesta Mirco ("stress test 5
     milioni... predisponi struttura"). La versione precedente caricava TUTTI
     gli utenti mancanti in un array PHP e risolveva l'upline riga per riga —
     corretta ma non scalabile: con centinaia di migliaia di righe mancanti
     avrebbe saturato la memoria PHP. Qui e' tutta SQL-set-based: un INSERT...
     SELECT per passata, ripetuto finche' non risolve piu' nulla (gestisce
     catene di referrer profonde: se A ha referrer B che a sua volta ha
     referrer C non ancora piazzato, servono piu' passate in ordine). Stesso
     comportamento osservabile di prima (stesso identico contratto: $dry=true
     non scrive nulla, $dry=false scrive e conta), verificato su 8.003 utenti
     di test (stesse 11 verifiche di correttezza gia' passate prima) e su
     1.000.000 di utenti reali generati in un DB di prova isolato: 37,9s,
     zero errori di catena. */
  $trovati=0;
  try{ $trovati=(int)$pdo->query("SELECT COUNT(*) FROM users u LEFT JOIN network_nodes n ON n.uid=u.id WHERE n.id IS NULL AND u.id != ".(int)$adminUid)->fetchColumn(); }
  catch(Throwable $e){ return ['ok'=>false,'err'=>'conteggio fallito: '.$e->getMessage()]; }
  if($trovati===0) return ['ok'=>true,'dry'=>$dry,'trovati'=>0,'creati'=>0,'piano'=>[],'cicli_su_master'=>0];

  if($dry){
    /* anteprima: solo i primi 500 (mai l'intero elenco se sono milioni) */
    $piano=[];
    try{
      $sql="SELECT u.id,u.full_name,u.username,u.sic_id,u.internal_code,u.referrer_id,
                   COALESCE(u.kit_owned,0) kit_owned, COALESCE(u.membership_active,0) mem_att
            FROM users u LEFT JOIN network_nodes n ON n.uid=u.id
            WHERE n.id IS NULL AND u.id != ".(int)$adminUid." LIMIT 500";
      foreach($pdo->query($sql) as $r){
        $uid=(int)$r['id']; $refId=(int)($r['referrer_id']??0);
        $up=$adminUid;
        if($refId>0){ try{ $chk=(int)$pdo->query("SELECT uid FROM network_nodes WHERE uid=".$refId)->fetchColumn(); $up = $chk>0 ? $chk : null; }catch(Throwable $e){ $up=null; } }
        $tipo=((int)$r['kit_owned']>0 || (int)$r['mem_att']>0) ? 'Buyer' : 'Membro';
        $sic = $r['sic_id'] ?: ($r['internal_code'] ?: ('SIC-MEM-'.str_pad((string)$uid,9,'0',STR_PAD_LEFT)));
        $piano[]=['uid'=>$uid,'nome'=>$r['full_name']?:$r['username'],'sic'=>$sic,
                   'upline'=>$up ?? ('in attesa che il referrer #'.$refId.' abbia un nodo'),'tipo'=>$tipo];
      }
    }catch(Throwable $e){}
    return ['ok'=>true,'dry'=>true,'trovati'=>$trovati,'creati'=>0,'piano'=>$piano,'cicli_su_master'=>null,
            'nota'=>$trovati>500?('anteprima limitata ai primi 500 su '.number_format($trovati).' totali'):''];
  }

  $fattiTotali=0; $maxGiri=60;
  for($giro=0; $giro<$maxGiri; $giro++){
    $sql="INSERT OR IGNORE INTO network_nodes(uid,sic_id,rango,rank_floor,status,upline_uid,attivazione_n,ultimo_attivo)
          SELECT u.id,
                 COALESCE(NULLIF(u.sic_id,''), NULLIF(u.internal_code,''), 'SIC-MEM-'||substr('000000000'||u.id,-9)),
                 0, 0,
                 CASE WHEN COALESCE(u.kit_owned,0)>0 OR COALESCE(u.membership_active,0)>0 THEN 'Buyer' ELSE 'Membro' END,
                 CASE WHEN COALESCE(u.referrer_id,0)<=0 THEN ".(int)$adminUid." ELSE r.uid END,
                 0, datetime('now')
          FROM users u
          LEFT JOIN network_nodes existing ON existing.uid = u.id
          LEFT JOIN network_nodes r ON r.uid = u.referrer_id
          WHERE existing.id IS NULL AND u.id != ".(int)$adminUid."
            AND (COALESCE(u.referrer_id,0)<=0 OR r.uid IS NOT NULL)";
    try{ $n=(int)$pdo->exec($sql); }
    catch(Throwable $e){ return ['ok'=>false,'err'=>'scrittura fallita al giro '.$giro.': '.$e->getMessage(),'creati'=>$fattiTotali]; }
    if($n<=0) break;
    $fattiTotali+=$n;
  }

  /* chi resta (referrer che a sua volta non trova mai un nodo: catena rotta o
     ciclo) viene agganciato al master, come faceva la versione precedente. */
  $cicli=0;
  try{
    $sqlCicli="INSERT OR IGNORE INTO network_nodes(uid,sic_id,rango,rank_floor,status,upline_uid,attivazione_n,ultimo_attivo)
          SELECT u.id,
                 COALESCE(NULLIF(u.sic_id,''), NULLIF(u.internal_code,''), 'SIC-MEM-'||substr('000000000'||u.id,-9)),
                 0, 0,
                 CASE WHEN COALESCE(u.kit_owned,0)>0 OR COALESCE(u.membership_active,0)>0 THEN 'Buyer' ELSE 'Membro' END,
                 ".(int)$adminUid.", 0, datetime('now')
          FROM users u LEFT JOIN network_nodes existing ON existing.uid = u.id
          WHERE existing.id IS NULL AND u.id != ".(int)$adminUid;
    $cicli=(int)$pdo->exec($sqlCicli);
    $fattiTotali+=$cicli;
  }catch(Throwable $e){}

  net_audit($pdo,'admin','backfill_tutti',0, $fattiTotali.' nodi creati su '.$trovati.' trovati'.
    ($cicli ? (' ('.$cicli.' con catena non risolvibile, agganciati al master)') : ''));
  return ['ok'=>true,'dry'=>false,'trovati'=>$trovati,'creati'=>$fattiTotali,'piano'=>[],'cicli_su_master'=>$cicli];
}

/* ============================================================
   FIGLI A SCAGLIONI (Cowork 2026-08-13 notte, richiesta Mirco: "predisponi
   struttura per 5 milioni... fai in modo di far caricare le viste senza
   appesantire il browser o il device"). Usata da Albero e Stella per NON
   caricare mai piu' l'intera tabella network_nodes in un colpo solo: si
   chiedono solo "i figli DIRETTI di questo nodo", mai l'albero intero.
   Poggia sull'indice ix_nn_upline aggiunto sopra: il costo dipende da quanti
   figli ha IL NODO APERTO, mai dalla dimensione totale della rete.
   Misurato (test isolato, nodo con 300.000 figli diretti — il caso peggiore
   concepibile): 0,16ms su un nodo normale (9 figli), ~27ms sul nodo piu'
   affollato del test (dominato dal COUNT(*), inevitabile con cosi' tanti
   figli, ma comunque adatto a un'azione lato admin innescata da un click,
   non un percorso caldo). NIENTE "ORDER BY n.uid": misurato che costringeva
   SQLite a materializzare e ordinare TUTTI i figli in un B-tree temporaneo
   solo per prendersi i primi N (33ms invece di 0,1ms sul nodo piu' affollato
   del test) — stesso identico problema (e stessa diagnosi via EXPLAIN QUERY
   PLAN) del fix a net_assegna_user_random() in dr-network-struttura.php
   stasera. Qui l'ordine esatto dei figli mostrati non conta (e' solo vista),
   quindi si toglie e basta. */
function net_children_scoped($pdo, $parentUid, $limit=300){
  net_migra($pdo);
  $parentUid=(int)$parentUid; $limit=max(1,min(2000,(int)$limit));

  $tot=(int)$pdo->query("SELECT COUNT(*) FROM network_nodes WHERE upline_uid=".$parentUid." AND uid!=".$parentUid)->fetchColumn();

  $sql="SELECT n.uid,n.status,n.sic_id,n.stato,COALESCE(u.full_name,u.username,'') nome
        FROM network_nodes n LEFT JOIN users u ON u.id=n.uid
        WHERE n.upline_uid=".$parentUid." AND n.uid!=".$parentUid."
        LIMIT ".$limit;
  $rows=$pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  $ids=array_map(function($r){ return (int)$r['uid']; }, $rows);

  /* conteggio nipoti IN UNA SOLA QUERY per tutti i figli restituiti (mai una
     COUNT per figlio: pattern N+1 evitato fin dalla prima stesura). */
  $nipoti=[];
  if($ids){
    $in=implode(',', $ids);
    foreach($pdo->query("SELECT upline_uid,COUNT(*) c FROM network_nodes WHERE upline_uid IN ($in) GROUP BY upline_uid") as $r){
      $nipoti[(int)$r['upline_uid']] = (int)$r['c'];
    }
  }

  $out=[];
  foreach($rows as $r){
    $uid=(int)$r['uid'];
    $st=(string)$r['status'];
    $type=$st==='World'?'world':($st==='National'?'national':($st==='Pro'?'pro':($st==='Master'?'master':'user')));
    $occ=($r['stato']==='attivo')?1:0;
    $name=$r['nome']!==''?$r['nome']:('SIC '.$r['sic_id']);
    $out[]=['id'=>$uid,'name'=>$name,'type'=>$type,'sic'=>(string)$r['sic_id'],'occupied'=>$occ,
            'figli_diretti'=>$nipoti[$uid]??0];
  }
  return ['ok'=>true,'parent'=>$parentUid,'figli'=>$out,'totale_figli'=>$tot,
          'troncato'=>$tot>count($out), 'mostrati'=>count($out)];
}

/* ============================================================
   RICERCA + PERCORSO (Cowork 2026-08-13 notte, richiesta Mirco: "search box
   per trovare nodi" nei visualizzatori Albero/Stella). Con 5 milioni di nodi
   non basta cercare tra quelli gia' caricati nel browser (quasi sempre
   vuoto): qui si cerca DIRETTAMENTE nel DB e si ritorna anche il PERCORSO
   dalla radice al nodo trovato, cosi' il client sa quali rami aprire in
   sequenza (uno alla volta, con le stesse fetch a scaglioni gia' testate)
   per arrivarci, invece di dover scaricare tutto.
   Tre modi di match, in ordine (si ferma appena arriva a $limit):
   1) uid numerico esatto (piu' veloce: chiave primaria)
   2) prefisso SIC-ID (usa comunque un indice per il prefisso)
   3) nome/username con LIKE '%...%' (NON indicizzabile, quindi solo se i
      primi due non bastano, e comunque limitato — uso admin occasionale,
      non un percorso caldo).
   Il percorso si costruisce risalendo upline_uid un salto alla volta (la
   profondita' reale e' quasi sempre piccola: Master->World->National->Pro
   sono 4 salti fissi, poi la catena referral sotto un Pro; tetto di
   sicurezza a 300 salti contro cicli, stesso margine gia' usato altrove
   in questo file). */
function net_find_path($pdo, $query, $limit=20){
  net_migra($pdo);
  $query=trim((string)$query);
  if($query==='') return ['ok'=>true,'risultati'=>[]];
  $limit=max(1,min(50,(int)$limit));
  $rows=[];

  if(ctype_digit($query)){
    $st=$pdo->prepare("SELECT n.uid,n.status,n.sic_id,n.stato,COALESCE(u.full_name,u.username,'') nome
                        FROM network_nodes n LEFT JOIN users u ON u.id=n.uid WHERE n.uid=? LIMIT 1");
    $st->execute([(int)$query]);
    if($r=$st->fetch(PDO::FETCH_ASSOC)) $rows[]=$r;
  }
  if(count($rows)<$limit){
    $st=$pdo->prepare("SELECT n.uid,n.status,n.sic_id,n.stato,COALESCE(u.full_name,u.username,'') nome
                        FROM network_nodes n LEFT JOIN users u ON u.id=n.uid WHERE n.sic_id LIKE ? LIMIT ?");
    $st->bindValue(1,$query.'%'); $st->bindValue(2,$limit-count($rows),PDO::PARAM_INT); $st->execute();
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r) $rows[]=$r;
  }
  if(count($rows)<$limit){
    $like='%'.$query.'%';
    $st=$pdo->prepare("SELECT n.uid,n.status,n.sic_id,n.stato,COALESCE(u.full_name,u.username,'') nome
                        FROM network_nodes n LEFT JOIN users u ON u.id=n.uid
                        WHERE (u.full_name LIKE ? OR u.username LIKE ?) LIMIT ?");
    $st->bindValue(1,$like); $st->bindValue(2,$like); $st->bindValue(3,$limit-count($rows),PDO::PARAM_INT); $st->execute();
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r) $rows[]=$r;
  }

  $seen=[]; $out=[];
  foreach($rows as $r){
    $uid=(int)$r['uid']; if(isset($seen[$uid])) continue; $seen[$uid]=1;
    $path=[$uid]; $cur=$uid; $hops=0; $visited=[$uid=>1];
    while($hops<300){
      $up=(int)$pdo->query("SELECT upline_uid FROM network_nodes WHERE uid=".$cur)->fetchColumn();
      if($up<=0 || $up===$cur || isset($visited[$up])) break;
      $path[]=$up; $visited[$up]=1; $cur=$up; $hops++;
    }
    $path=array_reverse($path);   // radice -> nodo trovato
    $st=(string)$r['status'];
    $type=$st==='World'?'world':($st==='National'?'national':($st==='Pro'?'pro':($st==='Master'?'master':'user')));
    $out[]=['id'=>$uid,'name'=>$r['nome']!==''?$r['nome']:('SIC '.$r['sic_id']),'type'=>$type,'sic'=>(string)$r['sic_id'],'path'=>$path];
    if(count($out)>=$limit) break;
  }
  return ['ok'=>true,'risultati'=>$out];
}
