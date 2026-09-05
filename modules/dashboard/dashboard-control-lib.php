<?php
/* ============================================================================
   DASHBOARD CONTROL — Destino Randagio · 2026-08-12 · Cowork
   destinazione: genesys/dashboard-control-lib.php

   Libreria (nessun output, solo funzioni + schema) per accendere/spegnere le
   sezioni della dashboard-user.php per singolo utente, per gruppo o per tutti,
   raggruppate in "fasi" del progetto, con log di ogni modifica.

   ADATTAMENTI rispetto alla richiesta originale (dichiarati, non nascosti):
   - Le sezioni gestibili qui sono le 11 REALI di dashboard-user.php (quelle
     con un vero pannello <section id="p-...">, trovate leggendo il file):
     overview, wallet, rank, missions, network, staking, nft, membership,
     shop, music, games. "Community" e "Web3 & Shop" nella richiesta erano
     etichette di RAGGRUPPAMENTO nel menu, non sezioni vere: non esiste un
     pannello proprio da accendere/spegnere per loro, quindi non sono qui.
     "Intrattenimento" nel file reale sono DUE pannelli distinti (Musica e
     Giochi): li ho tenuti separati invece di forzarli in un interruttore
     solo — piu' controllo, non meno, e rispecchia il codice vero.
   - "Panoramica" e il profilo/account NON sono gestibili: sono la vista di
     base a cui torna la SPA e le impostazioni personali. Toglierle a un
     utente lo lascerebbe con una dashboard vuota. Restano sempre visibili
     (is_locked=1 per overview; profilo non e' proprio nell'elenco).
   - Niente tabella dashboard_defaults separata (ridondante): il default
     vive gia' su dashboard_sections.default_status.
   - Niente step "applica default al nuovo utente" da agganciare alla
     registrazione: is_section_active() risolve il default AL VOLO se
     l'utente non ha una riga propria in dashboard_user_states. Stesso
     risultato pratico, zero modifiche al flusso di registrazione/onboarding
     esistente (piu' sicuro su un sito con utenti veri).
   - Schema creato qui con CREATE TABLE IF NOT EXISTS, come fa il resto del
     progetto (vedi db.php) — nessun file di migrazione a parte, che qui non
     esisteva ancora e nessuno avrebbe eseguito.
============================================================================ */

/* ---------------- SCHEMA (idempotente) ---------------- */
function dashctl_schema(PDO $pdo){
  static $done=false; if($done) return; $done=true;
  $pdo->exec("CREATE TABLE IF NOT EXISTS dashboard_fasi(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    status TEXT DEFAULT 'pianificata',
    position INTEGER DEFAULT 0,
    created TEXT DEFAULT (datetime('now')))");
  $pdo->exec("CREATE TABLE IF NOT EXISTS dashboard_sections(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    description TEXT,
    icon TEXT,
    default_status INTEGER DEFAULT 0,
    is_locked INTEGER DEFAULT 0,
    fase_id INTEGER,
    position INTEGER DEFAULT 0,
    created TEXT DEFAULT (datetime('now')))");
  $pdo->exec("CREATE TABLE IF NOT EXISTS dashboard_user_states(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    section_id INTEGER NOT NULL,
    is_active INTEGER DEFAULT 0,
    updated TEXT DEFAULT (datetime('now')),
    UNIQUE(user_id, section_id))");
  $pdo->exec("CREATE TABLE IF NOT EXISTS dashboard_log(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id INTEGER,
    user_id INTEGER,
    action_type TEXT NOT NULL,
    section_id INTEGER,
    fase_id INTEGER,
    old_status INTEGER,
    new_status INTEGER,
    details TEXT,
    created TEXT DEFAULT (datetime('now')))");
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dashctl_states_user ON dashboard_user_states(user_id)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS idx_dashctl_log_created ON dashboard_log(created)");
  dashctl_seed($pdo);
}

/* semina le 11 sezioni reali + 3 fasi di base, SOLO se la tabella e' vuota
   (idempotente: rilanciarlo non duplica e non tocca chi ha gia' toccato gli
   stati). Fasi e default rispecchiano l'ordine gia' visibile nel menu di
   dashboard-user.php. */
function dashctl_seed(PDO $pdo){
  $n = (int)$pdo->query("SELECT COUNT(*) FROM dashboard_sections")->fetchColumn();
  if($n>0) return;
  $fasi = [
    ['name'=>'Fase 1 - Lancio',        'description'=>'Nucleo base, visibile a tutti dalla registrazione.', 'status'=>'in_corso',    'position'=>1],
    ['name'=>'Fase 2 - Web3',          'description'=>'Staking, membership, shop: dopo Kit Genesys / acquisti.', 'status'=>'pianificata', 'position'=>2],
    ['name'=>'Fase 3 - Community avanzata', 'description'=>'NFT trading, intrattenimento.', 'status'=>'pianificata', 'position'=>3],
  ];
  $insF = $pdo->prepare("INSERT INTO dashboard_fasi(name,description,status,position) VALUES(?,?,?,?)");
  $faseId=[];
  foreach($fasi as $i=>$f){ $insF->execute([$f['name'],$f['description'],$f['status'],$f['position']]); $faseId[$i+1]=(int)$pdo->lastInsertId(); }

  $sez = [
    ['overview',   'Panoramica',        '📊', 1, 1, 1, 1],
    ['wallet',     'Wallet',            '💰', 1, 0, 1, 2],
    ['rank',       'Rango & Carriera',  '📈', 1, 0, 1, 3],
    ['missions',   'Missioni',          '🎯', 1, 0, 1, 4],
    ['network',    'Rete / Network',    '🌐', 1, 0, 1, 5],
    ['staking',    'Staking & Rendita', '⛏️', 0, 0, 2, 6],
    ['membership', 'Membership',        '🎫', 0, 0, 2, 7],
    ['shop',       'Shop & Ordini',     '🛍️', 0, 0, 2, 8],
    ['nft',        'NFT / Web3',        '🖼️', 0, 0, 3, 9],
    ['music',      'Musica & Fumetti',  '🎵', 0, 0, 3, 10],
    ['games',      'Giochi',            '🎮', 0, 0, 3, 11],
  ];
  $insS = $pdo->prepare("INSERT INTO dashboard_sections(slug,name,icon,default_status,is_locked,fase_id,position) VALUES(?,?,?,?,?,?,?)");
  foreach($sez as $s){ $insS->execute([$s[0],$s[1],$s[2],$s[3],$s[4],$faseId[$s[5]],$s[6]]); }
}

/* ---------------- LETTURA ---------------- */
function dashctl_sections(PDO $pdo){
  dashctl_schema($pdo);
  return $pdo->query("SELECT * FROM dashboard_sections ORDER BY position, id")->fetchAll(PDO::FETCH_ASSOC);
}
function dashctl_fasi(PDO $pdo){
  dashctl_schema($pdo);
  return $pdo->query("SELECT * FROM dashboard_fasi ORDER BY position, id")->fetchAll(PDO::FETCH_ASSOC);
}
function dashctl_section_by_slug(PDO $pdo, $slug){
  dashctl_schema($pdo);
  $st=$pdo->prepare("SELECT * FROM dashboard_sections WHERE slug=?"); $st->execute([(string)$slug]);
  return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* stato risolto per un utente: slug => bool, per TUTTE le sezioni note.
   Priorita': locked (sempre true) > riga propria in dashboard_user_states >
   default_status della sezione. */
function dashctl_user_state(PDO $pdo, $user_id){
  dashctl_schema($pdo);
  $sections = dashctl_sections($pdo);
  $overrides = [];
  try{
    $st=$pdo->prepare("SELECT section_id,is_active FROM dashboard_user_states WHERE user_id=?");
    $st->execute([(int)$user_id]);
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r){ $overrides[(int)$r['section_id']] = (int)$r['is_active']; }
  }catch(Throwable $e){}
  $out=[];
  foreach($sections as $s){
    if(!empty($s['is_locked'])){ $out[$s['slug']]=true; continue; }
    if(array_key_exists((int)$s['id'],$overrides)){ $out[$s['slug']] = (bool)$overrides[(int)$s['id']]; continue; }
    $out[$s['slug']] = (bool)$s['default_status'];
  }
  return $out;
}

/* usata da dashboard-user.php: is_section_active($pdo,$uid,'wallet').
   Fail-open apposta: uno slug sconosciuto/non ancora seminato non fa
   sparire nulla (meglio mostrare una sezione di troppo che romperne una
   che funzionava). */
function dashctl_is_active(PDO $pdo, $user_id, $slug){
  try{
    $state = dashctl_user_state($pdo, $user_id);
    return array_key_exists((string)$slug, $state) ? $state[(string)$slug] : true;
  }catch(Throwable $e){ return true; }
}

/* ---------------- SCRITTURA (con log) ---------------- */
function dashctl_log(PDO $pdo, $admin_id, $user_id, $action_type, $section_id, $fase_id, $old, $new, $details=null){
  try{
    $pdo->prepare("INSERT INTO dashboard_log(admin_id,user_id,action_type,section_id,fase_id,old_status,new_status,details) VALUES(?,?,?,?,?,?,?,?)")
        ->execute([(int)$admin_id, $user_id!==null?(int)$user_id:null, (string)$action_type,
                   $section_id!==null?(int)$section_id:null, $fase_id!==null?(int)$fase_id:null,
                   $old===null?null:(int)$old, $new===null?null:(int)$new,
                   $details!==null?json_encode($details, JSON_UNESCAPED_UNICODE):null]);
  }catch(Throwable $e){}
}

/* singolo utente */
function dashctl_set_user_state(PDO $pdo, $admin_id, $user_id, $slug, $active){
  dashctl_schema($pdo);
  $sec = dashctl_section_by_slug($pdo,$slug);
  if(!$sec) return ['ok'=>false,'err'=>'sezione sconosciuta'];
  if(!empty($sec['is_locked'])) return ['ok'=>false,'err'=>'sezione bloccata, non si spegne'];
  $before = dashctl_user_state($pdo,$user_id);
  $old = isset($before[$slug]) ? (int)$before[$slug] : (int)$sec['default_status'];
  $new = $active ? 1 : 0;
  $pdo->prepare("INSERT INTO dashboard_user_states(user_id,section_id,is_active,updated) VALUES(?,?,?,datetime('now'))
                 ON CONFLICT(user_id,section_id) DO UPDATE SET is_active=excluded.is_active, updated=datetime('now')")
      ->execute([(int)$user_id,(int)$sec['id'],$new]);
  dashctl_log($pdo,$admin_id,$user_id,'single',(int)$sec['id'],null,$old,$new);
  return ['ok'=>true];
}

/* gruppo di utenti (id gia' risolti da dashctl_search_users/filter) */
function dashctl_set_bulk_state(PDO $pdo, $admin_id, array $user_ids, $slug, $active){
  $sec = dashctl_section_by_slug($pdo,$slug);
  if(!$sec) return ['ok'=>false,'err'=>'sezione sconosciuta'];
  if(!empty($sec['is_locked'])) return ['ok'=>false,'err'=>'sezione bloccata, non si spegne'];
  $n=0;
  foreach($user_ids as $uid){
    $r = dashctl_set_user_state($pdo,$admin_id,(int)$uid,$slug,$active);
    if(!empty($r['ok'])) $n++;
  }
  dashctl_log($pdo,$admin_id,null,'bulk',(int)$sec['id'],null,null,$active?1:0,['utenti'=>count($user_ids),'applicati'=>$n]);
  return ['ok'=>true,'applicati'=>$n,'totale'=>count($user_ids)];
}

/* tutti gli utenti ESISTENTI + il default per i FUTURI (stessa sezione) */
function dashctl_set_global_state(PDO $pdo, $admin_id, $slug, $active){
  $sec = dashctl_section_by_slug($pdo,$slug);
  if(!$sec) return ['ok'=>false,'err'=>'sezione sconosciuta'];
  if(!empty($sec['is_locked'])) return ['ok'=>false,'err'=>'sezione bloccata, non si spegne'];
  $new = $active ? 1 : 0;
  $pdo->prepare("UPDATE dashboard_sections SET default_status=? WHERE id=?")->execute([$new,(int)$sec['id']]);
  /* le righe con un override ESPLICITO restano come sono (una modifica singola
     fatta apposta non deve sparire sotto un'azione "per tutti" successiva):
     qui aggiorniamo solo chi NON ha mai avuto un override, cioe' tutti gli
     utenti esistenti che dipendono ancora dal default. Semplice: basta non
     toccare dashboard_user_states, la risoluzione al volo fa il resto. */
  $totUtenti = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
  dashctl_log($pdo,$admin_id,null,'all',(int)$sec['id'],null,null,$new,['utenti_totali'=>$totUtenti]);
  return ['ok'=>true,'utenti_totali'=>$totUtenti];
}

/* fase intera: cascata su tutte le sue sezioni (globale, come sopra) */
function dashctl_set_fase_state(PDO $pdo, $admin_id, $fase_id, $active){
  dashctl_schema($pdo);
  $st=$pdo->prepare("SELECT slug FROM dashboard_sections WHERE fase_id=?"); $st->execute([(int)$fase_id]);
  $slugs = $st->fetchAll(PDO::FETCH_COLUMN);
  if(!$slugs) return ['ok'=>false,'err'=>'fase senza sezioni'];
  foreach($slugs as $slug){ dashctl_set_global_state($pdo,$admin_id,$slug,$active); }
  dashctl_log($pdo,$admin_id,null,'fase',null,(int)$fase_id,null,$active?1:0,['sezioni'=>$slugs]);
  return ['ok'=>true,'sezioni'=>$slugs];
}

function dashctl_set_fase_status(PDO $pdo, $admin_id, $fase_id, $status){
  $status = in_array($status,['pianificata','in_corso','completata'],true) ? $status : 'pianificata';
  $pdo->prepare("UPDATE dashboard_fasi SET status=? WHERE id=?")->execute([$status,(int)$fase_id]);
  dashctl_log($pdo,$admin_id,null,'fase_status',null,(int)$fase_id,null,null,['status'=>$status]);
  return ['ok'=>true];
}

/* ---------------- RICERCA / FILTRI UTENTI ---------------- */
function dashctl_search_users(PDO $pdo, $q, $limit=20){
  $q = trim((string)$q);
  if($q===''){
    return $pdo->query("SELECT id,username,email,full_name,COALESCE(sic_id,genesys_sic,'') sic,role,
                                COALESCE(rank_floor,0) rank_floor, COALESCE(membership_active,0) membership_active
                         FROM users ORDER BY id DESC LIMIT ".(int)$limit)->fetchAll(PDO::FETCH_ASSOC);
  }
  $like = '%'.$q.'%';
  $st = $pdo->prepare("SELECT id,username,email,full_name,COALESCE(sic_id,genesys_sic,'') sic,role,
                               COALESCE(rank_floor,0) rank_floor, COALESCE(membership_active,0) membership_active
                        FROM users
                        WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?
                           OR sic_id LIKE ? OR genesys_sic LIKE ? OR internal_code LIKE ?
                        ORDER BY id DESC LIMIT ".(int)$limit);
  $st->execute([$like,$like,$like,$like,$like,$like]);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

/* filtro per gruppo (3.2.1). Criteri supportati (i piu' verificabili sui
   campi reali di users): rank_min (rank_floor>=), membership ('active'|'none'),
   role, registrato_da/registrato_a (date 'YYYY-MM-DD'), diretti_di (uid: solo
   i suoi referrer_id diretti). NON implementato: filtro per "posizione nel
   network" oltre i diretti (livelli piu' profondi) — la rete a piramide vive
   in network_posti/network_nodi con una logica di risalita che non ho
   voluto improvvisare qui senza vederla testata: si puo' aggiungere dopo,
   partendo da net_downline() gia' presente in dr-network-struttura.php. */
function dashctl_filter_users(PDO $pdo, array $criteri, $limit=500){
  /* NOTA (bug trovato col test, non a occhio): PDO su SQLite con emulazione
     prepared (default) manda i valori di execute([...]) come TESTO. SQLite
     confronta per storage class quando i due lati non hanno affinita' gia'
     compatibile: un INTEGER e' SEMPRE "minore" di un TEXT, quindi
     "rank_floor >= ?" con ? bindato come stringa '4' era SEMPRE falso, anche
     per rank_floor=8. Fix: bindValue esplicito con PDO::PARAM_INT sui
     confronti numerici (rank_min, diretti_di). Le date/i testi restano
     execute() normale: li' il confronto e' testo-contro-testo, corretto. */
  $where=[]; $args=[]; $intBinds=[];
  if(!empty($criteri['rank_min'])){ $where[]="COALESCE(rank_floor,0) >= ?"; $args[]=null; $intBinds[count($args)]=(int)$criteri['rank_min']; }
  if(!empty($criteri['membership'])){
    if($criteri['membership']==='active'){ $where[]="COALESCE(membership_active,0)=1"; }
    elseif($criteri['membership']==='none'){ $where[]="COALESCE(membership_active,0)=0"; }
  }
  if(!empty($criteri['role'])){ $where[]="role=?"; $args[]=(string)$criteri['role']; }
  if(!empty($criteri['registrato_da'])){ $where[]="date(created) >= date(?)"; $args[]=(string)$criteri['registrato_da']; }
  if(!empty($criteri['registrato_a'])){ $where[]="date(created) <= date(?)"; $args[]=(string)$criteri['registrato_a']; }
  if(!empty($criteri['diretti_di'])){ $where[]="referrer_id=?"; $args[]=null; $intBinds[count($args)]=(int)$criteri['diretti_di']; }
  $sql = "SELECT id,username,email,full_name,COALESCE(sic_id,genesys_sic,'') sic FROM users";
  if($where) $sql .= " WHERE ".implode(' AND ',$where);
  $sql .= " ORDER BY id DESC LIMIT ".(int)$limit;
  $st=$pdo->prepare($sql);
  foreach($args as $i=>$v){
    $pos=$i+1;
    if(isset($intBinds[$pos])) $st->bindValue($pos,$intBinds[$pos],PDO::PARAM_INT);
    else $st->bindValue($pos,$v,PDO::PARAM_STR);
  }
  $st->execute();
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------------- LOG ---------------- */
function dashctl_get_log(PDO $pdo, array $filters=[], $limit=200){
  dashctl_schema($pdo);
  $where=[]; $args=[];
  if(!empty($filters['user_id'])){ $where[]="l.user_id=?"; $args[]=(int)$filters['user_id']; }
  if(!empty($filters['admin_id'])){ $where[]="l.admin_id=?"; $args[]=(int)$filters['admin_id']; }
  if(!empty($filters['action_type'])){ $where[]="l.action_type=?"; $args[]=(string)$filters['action_type']; }
  if(!empty($filters['da'])){ $where[]="date(l.created) >= date(?)"; $args[]=(string)$filters['da']; }
  if(!empty($filters['a'])){ $where[]="date(l.created) <= date(?)"; $args[]=(string)$filters['a']; }
  $sql = "SELECT l.*, au.username admin_nome, uu.username user_nome, s.name sezione_nome, f.name fase_nome
          FROM dashboard_log l
          LEFT JOIN users au ON au.id=l.admin_id
          LEFT JOIN users uu ON uu.id=l.user_id
          LEFT JOIN dashboard_sections s ON s.id=l.section_id
          LEFT JOIN dashboard_fasi f ON f.id=l.fase_id";
  if($where) $sql .= " WHERE ".implode(' AND ',$where);
  $sql .= " ORDER BY l.id DESC LIMIT ".(int)$limit;
  $st=$pdo->prepare($sql); $st->execute($args);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}
