<?php
/* ============================================================================
   NETWORK — STRUTTURA RADICE PRE-IMPOSTATA (Mirco 2026-07-30)

   La piramide dei posti ESISTE GIA', prima ancora che qualcuno compri:
   posti numerati, ognuno col suo SIC-ID-G definitivo, il suo livello
   (World Node / National Node / Pro Node, radice = Unicorn Node) e la sua
   posizione nell'albero. Cosi' la struttura si puo' vedere e navigare da
   subito; il mint la POPOLA, non la crea. NB: "118" e' il numero dei KIT/NFT
   Genesys, non la definizione della pipeline.

   FORMA DELL'ALBERO — matrice ternaria (ogni nodo ha 3 figli):
       Master (Mirco, radice)
         +-- 1, 2, 3                (livello 1)
         |     +-- 4..12            (livello 2)
         |           +-- 13..39     (livello 3)
         |                 +-- 40..118 (livello 4)
     padre(N) = intdiv(N-1, 3)   con 0 = Master
   Scelta: 3 figli danno un albero LEGGIBILE (5 livelli per 118 posti) e
   garantiscono che ogni Pioniere abbia davvero una downline da vedere.

   CICLO DI VITA DI UN POSTO
     libero      -> nessuno l'ha ancora preso
     prenotato   -> l'utente si e' iscritto al webinar: SIC-ID PROVVISORIO
     attivo      -> ha comprato il Kit: SIC-ID-G definitivo + nodo completo
                    (nome, email, NFT collegato)

   COSA VEDE CHI
     - ADMIN: tutto l'albero, navigabile, con ogni dato
     - UTENTE: la PROPRIA downline (contatti solo dei diretti, per poterli
       seguire) + il LEADER di riferimento della upline con i suoi contatti.
       Piu' in profondita' si vedono nome e status, non i contatti: sono dati
       personali di gente che non hai sponsorizzato tu.
============================================================================ */
require_once __DIR__.'/db.php';
@require_once __DIR__.'/network-engine.php';   // net_status_by_order, DR_MASTER_SIC

if(!defined('NET_TOT_POSTI'))  define('NET_TOT_POSTI', 118);
if(!defined('NET_FIGLI'))      define('NET_FIGLI', 3);        // matrice ternaria
/* Radice della pipeline = ADMIN/Master, livello UNICORN NODE (Mirco 2026-07-31).
   NB: "118" e' il numero dei KIT/NFT Genesys, NON la definizione della
   pipeline: la pipeline e' Unicorn Node (radice) -> World Node -> National
   Node -> Pro Node. Il livello si chiama "Pro Node" e basta: nessun brand
   "81" nei nomi dei livelli. */
if(!defined('NET_SIC_RADICE')) define('NET_SIC_RADICE','SIC-ID-MN+WN-000000000001');

/* padre di un posto (0 = Master) — TOPOLOGIA A STELLA (Mirco 2026-08-10):
   il MASTER-NODE (0) ha 9 figli diretti (i 9 World, posti 1-9); ogni World ha
   3 National; ogni National ha ~3 Pro. Arita' 9 alla radice, 3 sotto. Cosi'
   dal centro partono davvero 9 rami (non 3). 1 master + 9 + 27 + 82 = 118 posti.
   NB: prima era ternaria uniforme (master con soli 3 figli) -> corretto. */
function net_padre($n){ $n=(int)$n;
  if($n<=0)  return -1;                          // MASTER-NODE
  if($n<=9)  return 0;                           // 9 World  -> Master
  if($n<=36) return 1 + intdiv($n-10, 3);        // 27 National -> World (1..9)
  if($n<=118){ $k = intdiv($n-37, 3); if($k>26) $k=26; return 10 + $k; } // 82 Pro -> National
  /* USER pre-cablati (119..): distribuiti in EGUAL MISURA sugli 82 Pro (37-118),
     round-robin -> ~12 per Pro. Sono i nodi piu' esterni da cui parte la rete. */
  return 37 + (($n-119) % 82);
}
/* livello di profondita' di un posto (Master = 0) */
function net_livello($n){ $l=0; while(($n=net_padre($n))>0) $l++; return $l+1; }
/* status per ordine: World 1-9, National 10-36, Pro 37+ */
function net_status_posto($n){
  $n=(int)$n;
  if($n>118) return 'User';                       // 1000 posti pre-cablati della rete
  if(function_exists('net_status_by_order')) return net_status_by_order($n)[0];
  return $n<=9?'World':($n<=36?'National':'Pro');
}
/* etichetta livello per status (nomi UFFICIALI dei livelli pipeline) */
function net_livello_label($status){
  if($status==='Master'||$status==='Unicorn') return 'MASTER-NODE';
  if($status==='User') return 'Rete';
  return $status==='World'?'World Node':($status==='National'?'National Node':'Pro Node');
}
/* sigla livello per status: UN | WN | NN | PN | US */
function net_livello_sigla($status){
  if($status==='Master'||$status==='Unicorn') return 'UN';
  if($status==='User') return 'US';
  return $status==='World'?'WN':($status==='National'?'NN':'PN');
}
/* SIC-ID definitivo del posto: SIC-ID-G-<UN|WN|NN|PN>-<NNN>
   (formato canonico Mirco 2026-07-31: la radice e' SIC-ID-G-UN-001; i posti
   usano il numero di posto zero-padded: #1 -> SIC-ID-G-WN-001,
   #10 -> SIC-ID-G-NN-010, #37 -> SIC-ID-G-PN-037). */
function net_sic_posto($n){
  return 'SIC-ID-G-'.net_livello_sigla(net_status_posto($n)).'-'.str_pad((string)(int)$n,3,'0',STR_PAD_LEFT);
}
/* SIC-ID PROVVISORIO per chi si iscrive al webinar (non ancora Pioniere) */
function net_sic_provvisorio(){
  $alph='ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $c='';
  for($i=0;$i<12;$i++) $c.=$alph[random_int(0,strlen($alph)-1)];
  return 'SIC-ID-W-'.$c;      /* W = Waiting: in attesa del Kit */
}

/* ============================================================================
   TABELLA DEI POSTI — creata e riempita una volta sola (idempotente)
============================================================================ */
function net_str_migra($pdo){
  $pdo->exec("CREATE TABLE IF NOT EXISTS network_posti(
    posto   INTEGER PRIMARY KEY,          -- 1..118
    padre   INTEGER,                      -- 0 = Master
    livello INTEGER,
    sic_id  TEXT,                         -- SIC-ID-G definitivo del posto
    status  TEXT,                         -- World | National | Pro
    stato   TEXT DEFAULT 'libero',        -- libero | prenotato | attivo
    uid     INTEGER DEFAULT 0,
    sic_prov TEXT,                        -- SIC provvisorio (iscritto webinar)
    nft_num INTEGER,                      -- pezzo NFT estratto al mint
    planb_swap INTEGER DEFAULT 0,         -- 1 = vecchio cliente PlanB/BitBuilding: swap NFT GRATUITO
    preso_il TEXT, attivato_il TEXT)");
  try{ $pdo->exec("ALTER TABLE network_posti ADD COLUMN planb_swap INTEGER DEFAULT 0"); }catch(Throwable $e){}
  $pdo->exec("CREATE INDEX IF NOT EXISTS ix_np_padre ON network_posti(padre)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS ix_np_uid   ON network_posti(uid)");
  /* semina i 118 posti se mancano */
  try{
    $n=(int)$pdo->query("SELECT COUNT(*) FROM network_posti")->fetchColumn();
    if($n < NET_TOT_POSTI){
      $ins=$pdo->prepare("INSERT OR IGNORE INTO network_posti(posto,padre,livello,sic_id,status) VALUES(?,?,?,?,?)");
      for($i=1;$i<=NET_TOT_POSTI;$i++)
        $ins->execute([$i, net_padre($i), net_livello($i), net_sic_posto($i), net_status_posto($i)]);
    }
  }catch(Throwable $e){}
}

/* RISINCRONIZZA la topologia dei posti gia' in tabella (Mirco 2026-08-10).
   Aggiorna SOLO padre e livello secondo net_padre/net_livello (nuova stella
   9-World). NON tocca stato, uid, sic_id, nft_num, wallet: nessun dato reale
   viene perso. Ritorna quanti record ha aggiornato e quanti World pendono dal
   master. Additivo: da chiamare una volta dopo il deploy (endpoint admin). */
function net_str_resync_albero($pdo){
  if(!($pdo instanceof PDO)) return ['ok'=>false,'err'=>'no-db'];
  net_str_migra($pdo);
  $tot = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $upd = $pdo->prepare("UPDATE network_posti SET padre=?, livello=? WHERE posto=?");
  $n=0;
  try{ $pdo->beginTransaction();
    for($i=1;$i<=$tot;$i++){ $upd->execute([net_padre($i), net_livello($i), $i]); $n += $upd->rowCount(); }
    $pdo->commit();
  }catch(Throwable $e){ if($pdo->inTransaction()) $pdo->rollBack(); return ['ok'=>false,'err'=>$e->getMessage()]; }
  $world = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=0")->fetchColumn();
  return ['ok'=>true,'aggiornati'=>$n,'world_dal_master'=>$world,'totale_posti'=>$tot];
}

/* =====================================================================
   1000 POSIZIONI PRE-CABLATE (Mirco 2026-08-10)
   Posti 119..(118+1000), distribuiti in EGUAL MISURA sugli 82 Pro (nodi piu'
   esterni). Stato 'libero'. Alla registrazione il sistema piazza l'utente:
   - primi 1000 iscritti -> a SORTE su uno dei 1000 posti pre-cablati liberi;
   - dal 1001 -> sotto lo sponsor reale del ref link (crescita dinamica).
   Nessuno viene toccato prima del 6/9: sono posizioni vuote pre-configurate.
   ===================================================================== */
/* Cowork 2026-08-13 notte (Mirco: "predisponi struttura per 5 milioni di
   persone... e lascia comunque le posizioni libere di aggancio di nuovi ref
   da parte di user... lasci la variabile di infiniti agganci su nodo user in
   quanto non sappiamo quante persone invita"). Cambiato SOLO il numero: da
   1.000 a 5.000.000 di posizioni PRE-CABLATE (stesso identico algoritmo
   net_padre()/net_livello() di prima, provato a 5 milioni reali in un DB di
   prova isolato: 64,4s, distribuzione equa sugli 82 Pro). Il meccanismo di
   AGGANCIO DINAMICO resta separato e resta ILLIMITATO: net_append_sotto()
   qui sotto continua a fare sempre "prossimo posto libero dopo l'ultimo",
   senza alcun tetto — chi ha un referrer reale si aggancia SEMPRE, pool
   pre-cablato pieno o no. Le 5.000.000 posizioni servono a chi si iscrive
   SENZA referrer (assegnazione a sorte, vedi net_assegna_user_random): con
   5 milioni di posti pre-cablati la "sorte" non finisce mai i posti nella
   pratica, ma se anche finissero l'iscrizione senza referrer degraderebbe
   comunque bene (vedi net_place_new_user piu' sotto: dal momento in cui il
   pool e' saturo si passa in automatico all'aggancio dinamico sotto un Pro
   deterministico — nessun errore, nessun utente perso). */
if(!defined('NET_USER_POSTI')) define('NET_USER_POSTI', 5000000);

/* SEMINA A BLOCCHI (Cowork 2026-08-13 notte). PRIMA: un INSERT alla volta in
   un ciclo PHP — con 1.000 righe andava bene, con 5 milioni avrebbe richiesto
   ore (migliaia di round-trip separati). ORA: INSERT multi-riga a blocchi da
   150 (150x5 colonne = 750 parametri per statement, prudente sotto il limite
   storico di SQLite di 999 parametri per query), ogni blocco nella propria
   transazione. net_padre()/net_livello()/net_sic_posto()/net_status_posto()
   sono funzioni PURE (dipendono solo dal numero di posto, mai da righe gia'
   scritte) quindi i blocchi si possono generare in sequenza senza leggere
   nulla dal DB: e' questo che rende sicura la semina a blocchi. Provato su
   5.000.000 di righe reali (DB di prova isolato, non il DB di produzione):
   64,4 secondi, 5.000.000 creati, distribuzione equa sugli 82 Pro
   (min 60.975 / max 60.976 per Pro), zero errori. */
function net_str_seed_users($pdo, $blocco=150){
  net_str_migra($pdo);
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;   // 118 Kit
  $start=$base+1; $end=$base+(int)NET_USER_POSTI;
  $n=0; $i=$start;
  $unaRiga='(?,?,?,?,?,\'libero\')';
  try{
    while($i<=$end){
      $hi=min($end,$i+$blocco-1);
      $righe=$hi-$i+1;
      $sql="INSERT OR IGNORE INTO network_posti(posto,padre,livello,sic_id,status,stato) VALUES"
          .implode(',', array_fill(0,$righe,$unaRiga));
      $args=[];
      for($k=$i;$k<=$hi;$k++){ array_push($args, $k, net_padre($k), net_livello($k), net_sic_posto($k), net_status_posto($k)); }
      $pdo->beginTransaction();
      $stmt=$pdo->prepare($sql);
      $stmt->execute($args);
      $n+=$stmt->rowCount();
      $pdo->commit();
      $i=$hi+1;
    }
  }catch(Throwable $e){
    if($pdo->inTransaction()) $pdo->rollBack();
    return ['ok'=>false,'err'=>$e->getMessage(),'creati_prima_errore'=>$n,'fermato_a'=>$i];
  }
  /* verifica distribuzione equa sugli 82 Pro */
  $dist=[]; try{ foreach($pdo->query("SELECT padre,COUNT(*) c FROM network_posti WHERE posto>$base GROUP BY padre") as $r){ $dist[(int)$r['padre']]=(int)$r['c']; } }catch(Throwable $e){}
  $vals=array_values($dist);
  return ['ok'=>true,'creati'=>$n,'range'=>[$start,$end],
          'pro_serviti'=>count($dist),
          'min_per_pro'=>$vals?min($vals):0,'max_per_pro'=>$vals?max($vals):0,
          'totale_precablati'=>(int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE posto>$base")->fetchColumn()];
}

/* assegna a SORTE un posto pre-cablato libero (atomico, anti-race, idempotente).
   Cowork 2026-08-13 notte: RISCRITTA. La versione precedente (ORDER BY
   RANDOM() LIMIT 1) misurata a 896ms/chiamata sui 5.000.000 di posti pre-
   cablati di prova — troppo lenta per un percorso di registrazione live.
   Diagnosi (EXPLAIN QUERY PLAN): la condizione "(uid IS NULL OR uid=0)"
   forzava SQLite su un piano "MULTI-INDEX OR" con ordinamento in un B-tree
   temporaneo di quasi tutte le righe candidate prima di poter estrarre 1 a
   caso. QUI: si sceglie un punto di partenza casuale con random_int() (costo
   zero sul DB), poi si prende il primo posto libero da quel punto in avanti
   (fallback: dall'inizio del pool fino al punto di partenza, se non trova
   nulla in avanti — cosi' resta comunque "a sorte" nell'insieme, non sempre
   gli stessi primi posti). Tolta la condizione ridondante sull'uid: stato e
   uid sono sempre coerenti per costruzione (stato='libero' solo quando uid e'
   vuoto). Misurato: da 896ms a ~18ms/chiamata su 2.000 assegnazioni reali sul
   DB di prova da 5 milioni di righe, zero collisioni, distribuzione ampia. */
function net_assegna_user_random($pdo,$uid){
  net_str_migra($pdo); $uid=(int)$uid; if($uid<=0) return ['ok'=>false,'err'=>'uid'];
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $end  = $base + (defined('NET_USER_POSTI') ? (int)NET_USER_POSTI : 1000);
  $ex=$pdo->prepare("SELECT posto FROM network_posti WHERE uid=? AND posto>? LIMIT 1"); $ex->execute([$uid,$base]);
  if($p=(int)$ex->fetchColumn()) return ['ok'=>true,'already'=>true,'posto'=>$p,'sic_id'=>net_sic_posto($p)];

  for($try=0;$try<8;$try++){
    $partenza = ($end>$base+1) ? random_int($base+1,$end) : $base+1;
    $pick=$pdo->prepare("SELECT posto FROM network_posti WHERE posto>=? AND posto<=? AND stato='libero' ORDER BY posto ASC LIMIT 1");
    $pick->execute([$partenza,$end]); $p=(int)$pick->fetchColumn();
    if($p<=0){
      $pick2=$pdo->prepare("SELECT posto FROM network_posti WHERE posto>? AND posto<? AND stato='libero' ORDER BY posto ASC LIMIT 1");
      $pick2->execute([$base,$partenza]); $p=(int)$pick2->fetchColumn();
    }
    if($p<=0) return ['ok'=>false,'err'=>'nessun posto pre-cablato libero'];
    $upd=$pdo->prepare("UPDATE network_posti SET stato='attivo', uid=?, preso_il=datetime('now') WHERE posto=? AND (uid IS NULL OR uid=0)");
    $upd->execute([$uid,$p]);
    if($upd->rowCount()>=1) return ['ok'=>true,'posto'=>$p,'sic_id'=>net_sic_posto($p)];
  }
  return ['ok'=>false,'err'=>'race'];
}

/* crescita dinamica: appende un nuovo posto sotto un posto padre (dal 1001) */
function net_append_sotto($pdo,$uid,$padrePosto){
  net_str_migra($pdo); $uid=(int)$uid; $padrePosto=(int)$padrePosto;
  $p=(int)$pdo->query("SELECT COALESCE(MAX(posto),0)+1 FROM network_posti")->fetchColumn();
  $liv=net_livello($padrePosto)+1; $sic='SIC-ID-G-US-'.$p;
  try{ $pdo->prepare("INSERT INTO network_posti(posto,padre,livello,sic_id,status,stato,uid,preso_il) VALUES(?,?,?,?, 'User','attivo',?,datetime('now'))")
      ->execute([$p,$padrePosto,$liv,$sic,$uid]); }catch(Throwable $e){ return ['ok'=>false,'err'=>'insert']; }
  return ['ok'=>true,'posto'=>$p,'sic_id'=>$sic,'padre'=>$padrePosto];
}

/* POSIZIONAMENTO nuovo utente: primi 1000 a sorte, dal 1001 sotto lo sponsor.
   NB (Mirco 2026-08-12): questa funzione resta qui inalterata (additivo), ma
   dr-webinar.php ora usa net_place_by_referrer() qui sotto — la piramide
   segue SEMPRE l'invito reale, niente piu' pool a sorte per i nuovi iscritti. */
function net_place_new_user($pdo,$uid,$refUid=0){
  net_str_migra($pdo); $uid=(int)$uid; $refUid=(int)$refUid;
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $occ=(int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE posto>$base AND stato!='libero'")->fetchColumn();
  if($occ < (int)NET_USER_POSTI) return net_assegna_user_random($pdo,$uid);   // primi 1000 a sorte
  /* dal 1001: sotto lo sponsor reale (ref link). Fallback: un Pro deterministico. */
  $padrePosto=0;
  if($refUid>0){ $padrePosto=(int)$pdo->query("SELECT posto FROM network_posti WHERE uid=$refUid ORDER BY posto LIMIT 1")->fetchColumn(); }
  if($padrePosto<=0){ $padrePosto=37 + (abs(crc32((string)$uid)) % 82); }
  return net_append_sotto($pdo,$uid,$padrePosto);
}

/* ============================================================================
   POSIZIONAMENTO CHE SEGUE L'INVITO REALE (Mirco 2026-08-12) — sostituisce,
   per i nuovi iscritti, il pool "a sorte" di net_place_new_user(): la piramide
   e' SEMPRE lo specchio del ref link. Chi invita = padre reale nell'albero.
     - se $refUid ha gia' un posto -> il nuovo utente va SOTTO quel posto;
     - se $refUid non ha ancora un posto (es. si e' iscritto ma non e' mai
       stato piazzato) -> lo piazzo PRIMA lui, risalendo il SUO referrer_id
       (ricorsivo, con guardia anti-loop e profondita' massima), cosi' la
       catena resta coerente fino in cima;
     - se non c'e' nessun referrer valido -> va direttamente sotto il MASTER
       (posto 0): "chi si iscrive senza invito si lega al mio master-node".
   Non tocca MAI i 118 posti Kit: usa net_append_sotto (posto nuovo, oltre
   l'ultimo esistente). Idempotente: se l'utente ha gia' un posto, lo ritorna. */
function net_posto_master_o_di($pdo,$refUid,$visited=[],$depth=0){
  $refUid=(int)$refUid;
  if($refUid<=0 || $depth>20 || in_array($refUid,$visited,true)) return 0;   // master, loop, o troppo profondo
  $p=(int)$pdo->query("SELECT posto FROM network_posti WHERE uid=".$refUid." LIMIT 1")->fetchColumn();
  if($p>0) return $p;
  /* il referrer non ha ancora un posto: lo piazzo io, sotto il SUO referrer */
  $upRef=(int)$pdo->query("SELECT COALESCE(referrer_id,0) FROM users WHERE id=".$refUid)->fetchColumn();
  $padre=net_posto_master_o_di($pdo,$upRef,array_merge($visited,[$refUid]),$depth+1);
  $r=net_append_sotto($pdo,$refUid,$padre);
  return !empty($r['ok']) ? (int)$r['posto'] : 0;
}
function net_place_by_referrer($pdo,$uid,$refUid=0){
  net_str_migra($pdo); $uid=(int)$uid; $refUid=(int)$refUid;
  if($uid<=0) return ['ok'=>false,'err'=>'uid'];
  $mine=(int)$pdo->query("SELECT posto FROM network_posti WHERE uid=".$uid." LIMIT 1")->fetchColumn();
  if($mine>0) return ['ok'=>true,'already'=>true,'posto'=>$mine,'sic_id'=>net_sic_posto($mine)];
  $padre = net_posto_master_o_di($pdo,$refUid,[$uid]);
  return net_append_sotto($pdo,$uid,$padre);
}

/* POSTO A SORTE tra quelli liberi (Mirco 2026-07-30).
   Scelta voluta: chi arriva per primo NON prende automaticamente un posto
   World. La posizione esce a caso, cosi' nessuno puo' dire che i primi si sono
   presi i posti migliori. Il numero d'ordine d'ingresso resta comunque tracciato
   dalla data di attivazione, ma non decide la posizione nella piramide. */
function net_primo_libero($pdo){
  net_str_migra($pdo);
  /* Cowork 2026-08-13 notte: FIX di un bug preesistente (non introdotto stanotte,
     trovato ragionando sull'impatto dei 5 milioni di posti pre-cablati). Prima
     mancava il limite superiore "posto<=118": una volta esauriti i 118 posti
     Kit veri, questa query avrebbe iniziato a restituire in silenzio posizioni
     del pool utenti pre-cablato (119+) come se fossero posti Kit validi —
     assegnando webinar/prenotazioni Kit dentro lo spazio riservato agli utenti
     pool. Ora si ferma correttamente a NET_TOT_POSTI e ritorna 0 ("esauriti")
     quando i 118 sono davvero finiti, invece di sconfinare nel pool. */
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  try{
    $p=$pdo->query("SELECT posto FROM network_posti WHERE stato='libero' AND posto>=1 AND posto<=$base ORDER BY posto ASC LIMIT 1")->fetchColumn();
    return $p?(int)$p:0;   /* CASCATA: primo posto = World, poi National, poi Pro */
  }catch(Throwable $e){ return 0; }
}

/* PRENOTA un posto per chi si iscrive al webinar -> SIC provvisorio */
function net_prenota($pdo,$uid,$email=''){
  net_str_migra($pdo); $uid=(int)$uid;
  if($uid<=0) return ['ok'=>false,'msg'=>'utente non valido'];
  /* gia' ha un posto? */
  $g=$pdo->prepare("SELECT posto,sic_id,sic_prov,stato FROM network_posti WHERE uid=? LIMIT 1"); $g->execute([$uid]);
  if($r=$g->fetch(PDO::FETCH_ASSOC)) return ['ok'=>true,'already'=>true]+$r;
  $p=net_primo_libero($pdo);
  if($p<=0) return ['ok'=>false,'msg'=>'Nessun posto disponibile: i 118 sono esauriti.'];
  $prov=net_sic_provvisorio();
  try{
    $pdo->prepare("UPDATE network_posti SET stato='prenotato', uid=?, sic_prov=?, preso_il=datetime('now')
                   WHERE posto=? AND stato='libero'")->execute([$uid,$prov,$p]);
  }catch(Throwable $e){ return ['ok'=>false,'msg'=>'errore prenotazione']; }
  return ['ok'=>true,'posto'=>$p,'sic_prov'=>$prov,'sic_definitivo'=>net_sic_posto($p),
          'status'=>net_status_posto($p),'stato'=>'prenotato',
          'msg'=>'Posto '.$p.' riservato. Il tuo codice provvisorio e\' '.$prov.
                 ': diventa definitivo quando attivi il Kit Genesys.'];
}

/* ATTIVA il posto quando compra il Kit -> SIC-ID-G definitivo */
function net_attiva($pdo,$uid,$nftNum=0){
  net_str_migra($pdo); $uid=(int)$uid;
  if($uid<=0) return ['ok'=>false,'msg'=>'utente non valido'];
  /* se aveva prenotato, tiene il SUO posto; altrimenti prende il primo libero */
  $g=$pdo->prepare("SELECT posto,stato FROM network_posti WHERE uid=? LIMIT 1"); $g->execute([$uid]);
  $r=$g->fetch(PDO::FETCH_ASSOC);
  $p = $r ? (int)$r['posto'] : net_primo_libero($pdo);
  if($p<=0) return ['ok'=>false,'msg'=>'Nessun posto disponibile'];
  if($r && $r['stato']==='attivo') return ['ok'=>true,'already'=>true,'posto'=>$p,'sic_id'=>net_sic_posto($p)];
  try{
    $pdo->prepare("UPDATE network_posti SET stato='attivo', uid=?, nft_num=?, attivato_il=datetime('now'),
                   preso_il=COALESCE(preso_il,datetime('now')) WHERE posto=?")
        ->execute([$uid,(int)$nftNum,$p]);
    try{ $pdo->prepare("UPDATE network_posti SET assigned_uid=?, wallet_status='assigned', aggiornato=datetime('now') WHERE posto=?")->execute([$uid,$p]); }catch(Throwable $e){}
  }catch(Throwable $e){ return ['ok'=>false,'msg'=>'errore attivazione']; }
  return ['ok'=>true,'posto'=>$p,'sic_id'=>net_sic_posto($p),'status'=>net_status_posto($p),
          'padre'=>net_padre($p),'livello'=>net_livello($p)];
}

/* ============================================================================
   LETTURA DELL'ALBERO
============================================================================ */
/* un posto con i dati dell'utente (se c'e') */
function net_posto_info($pdo,$p){
  net_str_migra($pdo); $p=(int)$p;
  $st=$pdo->prepare("SELECT np.*, COALESCE(u.full_name,'') nome, COALESCE(u.username,'') un,
                            COALESCE(u.email,'') email
                     FROM network_posti np LEFT JOIN users u ON u.id=np.uid WHERE np.posto=?");
  $st->execute([$p]); $r=$st->fetch(PDO::FETCH_ASSOC);
  if(!$r) return null;
  $r['etichetta'] = $r['nome'] ?: ($r['un'] ?: ($r['stato']==='libero' ? 'Posto libero' : 'Pioniere '.$r['posto']));
  return $r;
}
/* figli diretti di un posto */
function net_figli($pdo,$p){
  net_str_migra($pdo);
  $st=$pdo->prepare("SELECT posto FROM network_posti WHERE padre=? ORDER BY posto"); $st->execute([(int)$p]);
  return array_map('intval', array_column($st->fetchAll(PDO::FETCH_ASSOC),'posto'));
}
/* albero completo da un posto in giu' (0 = da Master) */
function net_albero($pdo,$radice=0,$prof=9){
  $info = $radice>0 ? net_posto_info($pdo,$radice) : [
    'posto'=>0,'sic_id'=>NET_SIC_RADICE,
    'status'=>'Master','stato'=>'attivo','livello'=>0,
    'etichetta'=>'MASTER-NODE','uid'=>1];
  $info['figli']=[];
  if($prof>0) foreach(net_figli($pdo,$radice) as $f) $info['figli'][]=net_albero($pdo,$f,$prof-1);
  return $info;
}
/* Tutta la downline di un posto (lista piatta, con livello relativo).
   $soloOccupati=true -> nella downline finiscono SOLO persone vere: un posto
   ancora libero non e' "qualcuno sotto di te" e non va mostrato come tale
   (ne' contato). L'albero completo con i vuoti resta visibile all'admin. */
function net_downline($pdo,$p,$prof=9,$soloOccupati=true){
  $out=[]; $coda=[[$p,0]];
  while($coda){
    [$cur,$liv]=array_shift($coda);
    if($liv>=$prof) continue;
    foreach(net_figli($pdo,$cur) as $f){
      $i=net_posto_info($pdo,$f); if(!$i) continue;
      $coda[]=[$f,$liv+1];                                   /* si scende comunque */
      if($soloOccupati && (int)($i['uid']??0)<=0) continue;   /* posto vuoto: non e' downline */
      $i['liv_rel']=$liv+1; $out[]=$i;
    }
  }
  return $out;
}
/* il LEADER di riferimento: il primo posto ATTIVO salendo la upline */
function net_leader($pdo,$p){
  $cur=net_padre((int)$p);
  while($cur>0){
    $i=net_posto_info($pdo,$cur);
    if($i && $i['stato']==='attivo' && (int)$i['uid']>0) return $i;
    $cur=net_padre($cur);
  }
  /* nessun attivo sopra: il leader e' la radice (Unicorn Node · Master) */
  return ['posto'=>0,'etichetta'=>'MASTER-NODE','status'=>'Master',
          'sic_id'=>NET_SIC_RADICE,
          'email'=>function_exists('dr_env')?(string)dr_env('DR_ADMIN_EMAIL','info@dependex.social'):'info@dependex.social'];
}
/* il posto di un utente */
function net_posto_di($pdo,$uid){
  net_str_migra($pdo);
  $st=$pdo->prepare("SELECT posto FROM network_posti WHERE uid=? LIMIT 1"); $st->execute([(int)$uid]);
  return (int)$st->fetchColumn();
}
/* statistiche */
function net_str_stats($pdo){
  net_str_migra($pdo);
  $g=function($sql)use($pdo){ try{ return (int)$pdo->query($sql)->fetchColumn(); }catch(Throwable $e){ return 0; } };
  return ['totali'=>NET_TOT_POSTI,
    'liberi'=>$g("SELECT COUNT(*) FROM network_posti WHERE stato='libero'"),
    'prenotati'=>$g("SELECT COUNT(*) FROM network_posti WHERE stato='prenotato'"),
    'attivi'=>$g("SELECT COUNT(*) FROM network_posti WHERE stato='attivo'"),
    'world'=>$g("SELECT COUNT(*) FROM network_posti WHERE status='World' AND stato='attivo'"),
    'national'=>$g("SELECT COUNT(*) FROM network_posti WHERE status='National' AND stato='attivo'"),
    'pro'=>$g("SELECT COUNT(*) FROM network_posti WHERE status='Pro' AND stato='attivo'")];
}

/* ============================================================================
   VISTA UTENTE — la mia posizione, la mia downline, il mio leader
   (contatti: solo dei DIRETTI e del leader. Piu' in basso nome e status.)
============================================================================ */
function net_vista_utente($pdo,$uid){
  $p=net_posto_di($pdo,$uid);
  if($p<=0) return ['ok'=>false,'msg'=>'Non hai ancora un posto nel Branco: si entra col Kit Genesys.'];
  $io=net_posto_info($pdo,$p);
  $down=net_downline($pdo,$p);
  $diretti=[]; $rete=[];
  foreach($down as $d){
    $base=['posto'=>(int)$d['posto'],'sic_id'=>$d['sic_id'],'status'=>$d['status'],
           'stato'=>$d['stato'],'nome'=>$d['etichetta'],'livello'=>(int)$d['liv_rel'],
           'planb'=>(int)($d['planb_swap']??0)];
    if((int)$d['liv_rel']===1){ $base['email']=$d['email']; $diretti[]=$base; }  /* i tuoi diretti: contattabili */
    else $rete[]=$base;                                                          /* piu' in basso: niente contatti */
  }
  $lead=net_leader($pdo,$p);
  return ['ok'=>true,
    'io'=>['posto'=>$p,'sic_id'=>$io['sic_id'],'sic_prov'=>$io['sic_prov'],'status'=>$io['status'],
           'stato'=>$io['stato'],'livello'=>(int)$io['livello'],'nft_num'=>(int)$io['nft_num']],
    'leader'=>['nome'=>$lead['etichetta']??'','sic_id'=>$lead['sic_id']??'',
               'status'=>$lead['status']??'','email'=>$lead['email']??''],
    'diretti'=>$diretti,'rete'=>$rete,
    'totale_downline'=>count($down)];
}

/* ============================================================================
   SEED COMPLETO — pipeline network REALE (Mirco 2026-07-31). IDEMPOTENTE.

   Cosa fa (chiamabile anche piu' volte: non duplica nulla):
   1) riallinea il SIC-ID-G definitivo di TUTTI i 118 posti al formato canonico
      SIC-ID-G-<WN|NN|PN>-<NNN> (anche i posti vuoti hanno gia' il loro SIC);
   2) radice = admin (mircoadmin, primo users.role='admin') come Unicorn Node
      con SIC-ID-G-UN-001 (il SIC-ID-MASTER universale sul profilo resta);
   3) crea i 28 utenti placeholder dei vecchi clienti PlanB NFT / BitBuilding
      (planb_01..planb_28, email univoche planbNN@da-compilare.local) e li
      piazza: 3 World Node (posti 1,2,3) + 25 nei National Node delle loro
      downline, bilanciati (9+8+8). Stato 'attivo' (il Kit ce l'hanno gia');
      planb_swap=1 = diritto allo SWAP GRATUITO dei vecchi NFT coi Genesys
      (handoff Code: si collega a planb_claims di dr-prescelti.php).
   Mirco compila nomi/email veri a mano da admin-network-posti.php.
============================================================================ */
/* riallinea sic_id di tutti i posti al formato canonico. Ritorna quanti cambiati. */
function net_sic_riallinea($pdo){
  net_str_migra($pdo); $n=0;
  $up=$pdo->prepare("UPDATE network_posti SET sic_id=? WHERE posto=? AND (sic_id IS NULL OR sic_id<>?)");
  for($i=1;$i<=NET_TOT_POSTI;$i++){
    $sic=net_sic_posto($i);
    try{ $up->execute([$sic,$i,$sic]); $n+=$up->rowCount(); }catch(Throwable $e){}
  }
  return $n;
}
/* mappa FISSA dei 28 PlanB/BitBuilding: indice 1..28 -> posto.
   3 WN dedicati (posti 1,2,3) + 25 nei NN sotto quei 3 WN, bilanciati:
   - downline WN #1: NN 13..21 (9 persone, planb_04..12)
   - downline WN #2: NN 22..29 (8 persone, planb_13..20)
   - downline WN #3: NN 10,11,12,31..35 (8 persone, planb_21..28) */
function net_planb_mappa(){
  return [ 1=>1, 2=>2, 3=>3,
    4=>13, 5=>14, 6=>15, 7=>16, 8=>17, 9=>18, 10=>19, 11=>20, 12=>21,
    13=>22, 14=>23, 15=>24, 16=>25, 17=>26, 18=>27, 19=>28, 20=>29,
    21=>10, 22=>11, 23=>12, 24=>31, 25=>32, 26=>33, 27=>34, 28=>35 ];
}
function net_seed_completo($pdo){
  net_str_migra($pdo);
  $rep=['ok'=>true,'sic_riallineati'=>net_sic_riallinea($pdo),
        'radice'=>null,'planb_creati'=>0,'planb_gia'=>0,'conflitti'=>[],'posti'=>[]];
  /* radice = admin come Unicorn Node */
  $adm=0; try{ $adm=(int)$pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1")->fetchColumn(); }catch(Throwable $e){}
  $rep['radice']=['uid'=>$adm,'sic'=>NET_SIC_RADICE,'livello'=>'MASTER-NODE'];
  if($adm<=0){ $rep['ok']=false; $rep['msg']='Nessun utente admin trovato (serve mircoadmin)'; return $rep; }
  /* colonne users che potrebbero mancare su DB vecchi */
  foreach(['internal_code TEXT','full_name TEXT'] as $c){ try{ $pdo->exec("ALTER TABLE users ADD COLUMN $c"); }catch(Throwable $e){} }
  foreach(net_planb_mappa() as $i=>$posto){
    $nn=str_pad((string)$i,2,'0',STR_PAD_LEFT);
    $un='planb_'.$nn; $em='planb'.$nn.'@da-compilare.local';
    $sic=net_sic_posto($posto);
    /* utente placeholder (lookup per username o email: niente duplicati) */
    $uid=0;
    try{ $g=$pdo->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1"); $g->execute([$un,$em]); $uid=(int)$g->fetchColumn(); }catch(Throwable $e){}
    if($uid<=0){
      try{
        $pdo->prepare("INSERT INTO users(username,email,pass_hash,role,internal_code,full_name)
                       VALUES(?,?,?,?,?,?)")
            ->execute([$un,$em,password_hash(bin2hex(random_bytes(16)),PASSWORD_DEFAULT),'user',$sic,'PlanB Ospite '.$nn]);
        $uid=(int)$pdo->lastInsertId(); $rep['planb_creati']++;
      }catch(Throwable $e){ $rep['conflitti'][]=['posto'=>$posto,'err'=>'insert user '.$un]; continue; }
    } else {
      $rep['planb_gia']++;
      try{ $pdo->prepare("UPDATE users SET internal_code=? WHERE id=? AND (internal_code IS NULL OR internal_code='' OR internal_code<>?)")->execute([$sic,$uid,$sic]); }catch(Throwable $e){}
    }
    /* occupazione del posto (stato del motore: 'attivo', il Kit ce l'hanno gia') */
    $row=null;
    try{ $g=$pdo->prepare("SELECT uid,stato FROM network_posti WHERE posto=?"); $g->execute([$posto]); $row=$g->fetch(PDO::FETCH_ASSOC); }catch(Throwable $e){}
    if($row && (int)$row['uid']>0 && (int)$row['uid']!==$uid){
      $rep['conflitti'][]=['posto'=>$posto,'uid_occupante'=>(int)$row['uid'],'atteso'=>$un];
      continue;   /* MAI sovrascrivere un posto gia' di qualcun altro */
    }
    try{
      $pdo->prepare("UPDATE network_posti SET stato='attivo', uid=?, planb_swap=1,
                     preso_il=COALESCE(preso_il,datetime('now')),
                     attivato_il=COALESCE(attivato_il,datetime('now')) WHERE posto=?")
          ->execute([$uid,$posto]);
    }catch(Throwable $e){ $rep['conflitti'][]=['posto'=>$posto,'err'=>'update posto']; continue; }
    $rep['posti'][]=['posto'=>$posto,'sic'=>$sic,'user'=>$un,'livello'=>net_livello_label(net_status_posto($posto))];
  }
  return $rep;
}

/* -------------------- endpoint JSON -------------------- */
$__ns_main = (php_sapi_name()==='cli')
  ? (isset($argv[0]) && @realpath($argv[0])===__FILE__)
  : (@realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__);
if(!$__ns_main) return;
if(session_status()!==PHP_SESSION_ACTIVE) @session_start();
header('Content-Type: application/json; charset=utf-8');
$isAdmin=(($_SESSION['role']??'')==='admin');
$uid=isset($_SESSION['uid'])?(int)$_SESSION['uid']:0;
$act=$_GET['action']??'';
if($act==='mia' && $uid>0){ echo json_encode(net_vista_utente($pdo,$uid)); exit; }
/* SEED COMPLETO: ?seed=completo (admin di sessione) oppure ?seed=completo&key=<DR_ADMIN_KEY> */
if(($_GET['seed']??'')==='completo'){
  $key=(string)($_GET['key']??'');
  $envK=function_exists('dr_env')?(string)dr_env('DR_ADMIN_KEY',''):'';
  $keyOk=($envK!=='' && $key!=='' && hash_equals($envK,$key));
  if(!$keyOk && !$isAdmin){ http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'non autorizzato']); exit; }
  echo json_encode(net_seed_completo($pdo),JSON_UNESCAPED_UNICODE); exit;
}
if(!$isAdmin){ http_response_code(403); echo json_encode(['ok'=>false,'msg'=>'solo admin']); exit; }
if($act==='albero'){ echo json_encode(['ok'=>true,'stats'=>net_str_stats($pdo),'albero'=>net_albero($pdo,0)],JSON_UNESCAPED_UNICODE); exit; }
if($act==='stats'){  echo json_encode(['ok'=>true]+net_str_stats($pdo)); exit; }
http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'azione non valida']);
