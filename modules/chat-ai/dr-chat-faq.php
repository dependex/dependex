<?php
/* ============================================================================
   DR-CHAT-FAQ — Knowledge base FAQ + apprendimento per la Chat AI

   ESTENDE (non sostituisce) chat.php / dr-chat-kb.php / branco-config.php.
   Aggiunge quattro capacita' richieste dalla direttiva Mirco:

     1) FAQ deterministiche         -> tabella chat_faq (domanda, risposta, tag,
                                       hit, attiva). Numeri SEMPRE dalle fonti
                                       uniche (dr-economy-config.php, cataloghi):
                                       le risposte usano segnaposto {…} espansi a
                                       runtime, cosi' non divergono mai.
     2) Risponde alle FAQ           -> dr_faq_match() + dr_faq_answer() con CTA
                                       contestuale verso KIT/Membership/NFT.
     3) Impara dalle domande utente -> ogni domanda senza risposta sicura finisce
                                       in chat_domande; dr_faq_candidate() aggrega
                                       le simili -> "candidate FAQ".
     4) Assistente ADMIN            -> dr_faq_admin_answer() (risposte operative da
                                       dati reali, sola lettura) + snapshot KPI.

   TUTTO funziona anche SENZA chiavi LLM (modalita' FAQ/regole), senza errori.
   Sola lettura sui dati sensibili. Niente promesse finanziarie sui DRX.

   Migrazioni idempotenti (CREATE TABLE IF NOT EXISTS). Nessun armamento.
============================================================================ */

if (!defined('DR_FAQ_SOGLIA')) define('DR_FAQ_SOGLIA', 2); // punteggio minimo per una risposta "sicura"

/* --------------------------------------------------------------------------
   MIGRAZIONE + SEED (idempotenti). Chiamare dr_faq_boot($pdo) una volta.
--------------------------------------------------------------------------- */
function dr_faq_migrate($pdo){
  $pdo->exec("CREATE TABLE IF NOT EXISTS chat_faq(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug     TEXT UNIQUE,
    domanda  TEXT,
    risposta TEXT,
    tag      TEXT,
    cta      TEXT DEFAULT 'kit',
    lang     TEXT DEFAULT 'it',
    hit      INTEGER DEFAULT 0,
    attiva   INTEGER DEFAULT 1,
    created  TEXT DEFAULT (datetime('now')),
    updated  TEXT DEFAULT (datetime('now')))");

  $pdo->exec("CREATE TABLE IF NOT EXISTS chat_domande(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    testo    TEXT,
    norm     TEXT,
    uid      INTEGER DEFAULT 0,
    bot      TEXT DEFAULT 'site',
    data     TEXT DEFAULT (datetime('now')),
    risolta  INTEGER DEFAULT 0,
    risposta_suggerita TEXT DEFAULT '',
    faq_id   INTEGER DEFAULT 0)");
}

/* Precarica le FAQ chiave. Idempotente: inserisce solo gli slug mancanti, cosi'
   una FAQ modificata a mano dall'admin non viene sovrascritta. */
function dr_faq_seed($pdo){
  foreach (dr_faq_semi() as $s){
    $st = $pdo->prepare("SELECT 1 FROM chat_faq WHERE slug=?");
    $st->execute([$s['slug']]);
    if ($st->fetchColumn()) continue;
    $pdo->prepare("INSERT INTO chat_faq(slug,domanda,risposta,tag,cta,lang,attiva)
                   VALUES(?,?,?,?,?, 'it', 1)")
        ->execute([$s['slug'],$s['domanda'],$s['risposta'],$s['tag'],$s['cta']]);
  }
}

function dr_faq_boot($pdo){
  static $done = false;
  if ($done) return;
  try { dr_faq_migrate($pdo); dr_faq_seed($pdo); } catch (Throwable $e) { @error_log('[faq] boot: '.$e->getMessage()); }
  $done = true;
}

/* --------------------------------------------------------------------------
   I SEMI — le FAQ chiave del progetto. Le risposte usano SEGNAPOSTO {…} che
   dr_faq_expand() sostituisce coi numeri veri dalle fonti uniche. Nessun numero
   hardcoded divergente vive qui.
--------------------------------------------------------------------------- */
function dr_faq_semi(){
  return [
    ['slug'=>'drx_cosa','cta'=>'kit',
     'domanda'=>'Cosa sono i DRX?',
     'tag'=>'drx cosa sono moneta punti utility valuta interna',
     'risposta'=>"I DRX sono i punti interni del Branco: utility, non denaro. Si guadagnano con le azioni e gli acquisti e si spendono in sconti e premi dentro Destino Randagio. Non sono convertibili in euro e non sono un investimento."],

    ['slug'=>'drx_guadagno','cta'=>'kit',
     'domanda'=>'Come si guadagnano i DRX?',
     'tag'=>'drx guadagnare guadagno come si ottengono accumulare earn punti',
     'risposta'=>"Ogni euro speso vale {DRX_GUADAGNO} DRX. In piu' guadagni DRX con le azioni del Branco (login, ascolti, condivisioni, recensioni, voti DAO, UGC) e completando le missioni. Il profilo completo vale {DRX_PROFILO} DRX una sola volta."],

    ['slug'=>'drx_riscatto','cta'=>'kit',
     'domanda'=>'Quanto valgono i DRX / come si riscattano?',
     'tag'=>'drx riscatto valore sconto quanto valgono euro spendere convertire cap tetto',
     'risposta'=>"Il riscatto e' {DRX_RISCATTO} DRX = 1 euro di sconto, con un tetto del {DRX_SCONTO_MAX}% per ordine. Cosi' i DRX ti fanno risparmiare sul serio dentro il Branco restando punti fedelta', non moneta."],

    ['slug'=>'profilo_drx','cta'=>'kit',
     'domanda'=>'Come completo il profilo per avere i 1000 DRX?',
     'tag'=>'profilo completare completo 1000 drx bonus dati anagrafica registrazione',
     'risposta'=>"Completa tutti i campi del tuo profilo (anagrafica, contatti, dati richiesti) dall'area account: alla prima compilazione completa ricevi {DRX_PROFILO} DRX, una sola volta. E' il modo piu' veloce per partire col saldo giusto."],

    ['slug'=>'ranghi','cta'=>'kit',
     'domanda'=>'Quanti ranghi ci sono e come si sale?',
     'tag'=>'rango ranghi livelli salire scalare grado progressione',
     'risposta'=>"Ci sono {RANGHI_N} ranghi. Si sale accumulando DRX di merito con partecipazione, acquisti e mining. Piu' sali, piu' cresce il cashback (dall'1% fino al {DRX_SCONTO_MAX}%) e sblocchi vantaggi del Branco."],

    ['slug'=>'kit','cta'=>'kit',
     'domanda'=>'Cos\'e\' il KIT del Branco e quanto costa?',
     'tag'=>'kit branco prezzo costo entrare ingresso bundle porta come si entra',
     'risposta'=>"Il KIT del Branco e' l'unica porta d'ingresso: prodotti fisici reali + status di Membro (giochi con DRX, NFT gated, Experience, DAO, missioni, ranghi). Tre livelli:\n{KIT_LISTINO}"],

    ['slug'=>'membership','cta'=>'membership',
     'domanda'=>'Come funziona la membership?',
     'tag'=>'membership abbonamento mensile livelli anima guardiano leggenda ricorrente',
     'risposta'=>"La membership e' l'appartenenza ricorrente al Branco, su tre livelli:\n{MEMBERSHIP_LISTINO}\nTiene attivi i vantaggi (cashback per rango, contenuti e Experience riservate)."],

    ['slug'=>'nft','cta'=>'nft',
     'domanda'=>'Che NFT ci sono e quanto costano?',
     'tag'=>'nft guardians tier prezzo listino collezione mint genesis mythic',
     'risposta'=>"Gli NFT del Branco (tier e prezzi reali):\n{NFT_LISTINO}\nLe uscite stagionali LIMITED (Natale, Pasqua, Halloween) hanno pre-reveal con countdown e mint riservato ai Membri."],

    ['slug'=>'song','cta'=>'kit',
     'domanda'=>'Come funziona la canzone su misura?',
     'tag'=>'canzone song personalizzata su misura regalo brano musica',
     'risposta'=>"La canzone su misura ({SONG_PRICE}) e' scritta apposta sulla storia della persona: un regalo che resta. La trovi su song.html."],

    ['slug'=>'mining','cta'=>'kit',
     'domanda'=>'Come funziona il mining dei DRX?',
     'tag'=>'mining minare distribuzione 10 anni estrazione giornaliero',
     'risposta'=>"Il mining distribuisce DRX ai Membri attivi nel tempo: e' il canale principale per salire di rango, su un arco di {MINING_ANNI}. Un membro attivo mina ogni giorno entro un tetto giornaliero."],

    ['slug'=>'spedizioni_resi','cta'=>'kit',
     'domanda'=>'Come funzionano spedizioni e resi?',
     'tag'=>'spedizione spedizioni consegna reso resi rimborso tempi shipping',
     'risposta'=>"I prodotti fisici (wear, gadget, KIT) sono realizzati e spediti su ordine tramite il nostro partner di stampa. Per tempi, tracciamento o un reso scrivi a info@dependex.social: ti seguiamo caso per caso."],

    ['slug'=>'come_entrare','cta'=>'kit',
     'domanda'=>'Come entro nel Branco?',
     'tag'=>'entrare iscriversi registrarsi come si entra unirsi membro diventare',
     'risposta'=>"Si entra nel Branco con il KIT: e' il biglietto d'ingresso che ti da' lo status di Membro e sblocca giochi, DRX, NFT gated, Experience e DAO. Da li' cammini col Branco."],

    ['slug'=>'referral','cta'=>'kit',
     'domanda'=>'Come funziona il referral / invitare amici?',
     'tag'=>'referral invitare amici link network affiliazione porta un amico invito',
     'risposta'=>"Ogni Membro ha un link referral personale: invitando altri fai crescere il tuo Branco e guadagni DRX. Trovi il tuo link nell'area riservata una volta entrato col KIT."],
  ];
}

/* --------------------------------------------------------------------------
   ESPANSIONE SEGNAPOSTO — i numeri veri, presi dalle fonti uniche.
--------------------------------------------------------------------------- */
function dr_faq_expand($txt){
  if (strpos($txt, '{') === false) return $txt;
  @require_once __DIR__.'/dr-economy-config.php';

  $g   = defined('DRX_GUADAGNO_PER_EUR') ? DRX_GUADAGNO_PER_EUR : 10;
  $ris = defined('DRX_RISCATTO_PER_EUR') ? DRX_RISCATTO_PER_EUR : 10;
  $cap = defined('DRX_SCONTO_MAX_PCT')   ? DRX_SCONTO_MAX_PCT   : 10;
  $pro = defined('DRX_REWARD_PROFILO')   ? DRX_REWARD_PROFILO   : 1000;
  $rng = function_exists('dr_rank_soglie') ? count(dr_rank_soglie()) : 9;

  $rep = [
    '{DRX_GUADAGNO}'  => $g,
    '{DRX_RISCATTO}'  => $ris,
    '{DRX_SCONTO_MAX}'=> $cap,
    '{DRX_PROFILO}'   => number_format((int)$pro, 0, ',', '.'),
    '{RANGHI_N}'      => $rng,
    '{SONG_PRICE}'    => '59,90 EUR',
    '{MINING_ANNI}'   => '10 anni (2026–2036)',
    '{NFT_LISTINO}'   => dr_faq_nft_listino(),
    '{KIT_LISTINO}'   => dr_faq_kit_listino(),
    '{MEMBERSHIP_LISTINO}' => dr_faq_membership_listino(),
  ];
  return strtr($txt, $rep);
}

function dr_faq_nft_listino(){
  @include_once __DIR__.'/nft-catalogo.php';
  if (!function_exists('nft_ordine') || !function_exists('nft_tier')) {
    return "vedi il listino aggiornato su nft.html";
  }
  $parts = [];
  foreach (nft_ordine() as $t){
    $c = nft_tier($t); if (!$c) continue;
    $eu = $c['euro'] ?? null;
    $nome = $c['nome'] ?? $t;
    $parts[] = "- $t \"$nome\": ".($eu !== null ? number_format((float)$eu,0,',','.')." EUR" : "1/1, non in vendita");
  }
  return $parts ? implode("\n", $parts) : "vedi nft.html";
}

function dr_faq_kit_listino(){
  if (!function_exists('dr_kit_tiers')){ global $pdo; @require_once __DIR__.'/billing.php'; } // billing.php assume $pdo globale
  if (!function_exists('dr_kit_tiers')) {
    return "- tre livelli di KIT: vedi prezzi e ranghi su branco.html";
  }
  $out = [];
  foreach (dr_kit_tiers() as $n => $t){
    $out[] = "- KIT $n: ".number_format((float)$t['promo'],2,',','.')." EUR (pieno ".number_format((float)$t['full'],2,',','.')." EUR) · rango ".$t['rankname']." · membership ".$t['mem'];
  }
  return implode("\n", $out);
}

function dr_faq_membership_listino(){
  if (!function_exists('dr_kit_tiers')){ global $pdo; @require_once __DIR__.'/billing.php'; }
  if (!function_exists('dr_kit_tiers')) {
    return "- Anima Randagia, Guardiano del Branco, Leggenda del Branco (vedi membership.html)";
  }
  $out = [];
  foreach (dr_kit_tiers() as $t){
    $out[] = "- ".$t['mem'].": ".number_format((float)$t['memp'],2,',','.')." EUR/mese";
  }
  return implode("\n", $out);
}

/* --------------------------------------------------------------------------
   NORMALIZZAZIONE + MATCH
--------------------------------------------------------------------------- */
function dr_faq_normalize($s){
  $s = mb_strtolower(trim((string)$s), 'UTF-8');
  $s = strtr($s, [
    'à'=>'a','á'=>'a','è'=>'e','é'=>'e','ì'=>'i','í'=>'i',
    'ò'=>'o','ó'=>'o','ù'=>'u','ú'=>'u','ç'=>'c',
  ]);
  $s = preg_replace('/[^a-z0-9 ]+/u', ' ', $s);
  $s = preg_replace('/\s+/', ' ', $s);
  return trim($s);
}

function dr_faq_stopwords(){
  return array_flip(['il','lo','la','i','gli','le','un','uno','una','di','a','da','in','con','su','per','tra','fra',
    'e','o','ma','se','che','chi','cosa','come','quando','dove','quanto','quanti','quante','quale','quali','perche',
    'e','è','ci','si','mi','ti','vi','ne','non','piu','meno','al','allo','alla','ai','agli','alle','del','dello','della',
    'dei','degli','delle','nel','nella','sul','sulla','io','tu','lui','lei','noi','voi','loro','mio','tuo','suo','vuoi',
    'voglio','posso','fare','sono','sei','ho','hai','ha','the','my','me','you','is','are','how','what','do','can']);
}

function dr_faq_tokens($s){
  $norm = dr_faq_normalize($s);
  if ($norm === '') return [];
  $sw = dr_faq_stopwords();
  $out = [];
  foreach (explode(' ', $norm) as $t){
    if ($t === '' || strlen($t) < 2) continue;
    if (isset($sw[$t])) continue;
    $out[$t] = true;
  }
  return array_keys($out);
}

/* Ritorna [row|null, score]. Solo FAQ attive. */
function dr_faq_match($pdo, $text, $lang='it'){
  $q = dr_faq_tokens($text);
  if (!$q) return [null, 0];
  $qset = array_flip($q);

  $rows = $pdo->query("SELECT * FROM chat_faq WHERE attiva=1")->fetchAll(PDO::FETCH_ASSOC);
  $best = null; $bestScore = 0;
  foreach ($rows as $r){
    $tagTok = dr_faq_tokens($r['tag']);
    $domTok = dr_faq_tokens($r['domanda']);
    $score = 0;
    foreach ($tagTok as $t) if (isset($qset[$t])) $score += 2;   // i tag pesano di piu'
    foreach ($domTok as $t) if (isset($qset[$t])) $score += 1;
    if ($lang !== '' && isset($r['lang']) && $r['lang'] === $lang) $score += 0; // (spazio per pesi lingua futuri)
    if ($score > $bestScore){ $bestScore = $score; $best = $r; }
  }
  return [$best, $bestScore];
}

/* --------------------------------------------------------------------------
   CTA CONTESTUALE — tono del Branco, non invadente.
--------------------------------------------------------------------------- */
function dr_faq_cta($target, $isMember=false){
  if ($isMember){
    switch ($target){
      case 'nft':        return "→ La tua collezione ti aspetta: nft.html";
      case 'membership': return "→ Vuoi salire di livello? Aggiorna la tua membership: membership.html";
      default:           return "→ Continua a far crescere il tuo Branco: invita col tuo link dall'area riservata.";
    }
  }
  switch ($target){
    case 'nft':        return "→ Vuoi il tuo posto nella collezione? Guarda i tier su nft.html";
    case 'membership': return "→ Scegli il tuo livello nel Branco: membership.html";
    default:           return "→ La porta del Branco e' il KIT: entra su branco.html";
  }
}

/* Compone la risposta finale: testo FAQ (espanso) + CTA. */
function dr_faq_format($row, $ctx=[]){
  $isMember = !empty($ctx['is_member']);
  $body = dr_faq_expand($row['risposta']);
  $cta  = dr_faq_cta($row['cta'] ?? 'kit', $isMember);
  return $body."\n\n".$cta;
}

/* --------------------------------------------------------------------------
   APPRENDIMENTO — log domande + candidate FAQ
--------------------------------------------------------------------------- */
function dr_faq_saluto($norm){
  foreach (['ciao','salve','buongiorno','buonasera','hey','ehi','hello','hi','grazie','ok'] as $g){
    if ($norm === $g) return true;
  }
  return false;
}

function dr_faq_log_domanda($pdo, $text, $uid=0, $bot='site'){
  $norm = dr_faq_normalize($text);
  if ($norm === '' || strlen($norm) < 6 || dr_faq_saluto($norm)) return; // niente saluti/rumore
  try {
    $pdo->prepare("INSERT INTO chat_domande(testo,norm,uid,bot) VALUES(?,?,?,?)")
        ->execute([mb_substr($text,0,500), $norm, (int)$uid, $bot]);
  } catch (Throwable $e) { @error_log('[faq] log: '.$e->getMessage()); }
}

/* Aggrega le domande non risolte per testo normalizzato -> candidate FAQ. */
function dr_faq_candidate($pdo, $minCount=2){
  $min = max(1, (int)$minCount); // inline: SQLite tipizza male un placeholder in HAVING ('2' text > int)
  try {
    $st = $pdo->query(
      "SELECT norm, COUNT(*) n, MIN(testo) esempio, MAX(data) ultima
       FROM chat_domande WHERE risolta=0 AND norm<>''
       GROUP BY norm HAVING n >= $min ORDER BY n DESC, ultima DESC LIMIT 50");
    return $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e) { return []; }
}

/* Promuove una domanda (o un cluster) a FAQ. Scrive in chat_faq e segna risolte
   tutte le domande con lo stesso testo normalizzato. Ritorna l'id FAQ. */
function dr_faq_promote($pdo, $domanda, $risposta, $tag='', $cta='kit'){
  $slug = 'user_'.substr(sha1(dr_faq_normalize($domanda)), 0, 12);
  $tag  = $tag !== '' ? $tag : implode(' ', dr_faq_tokens($domanda));
  // upsert per slug
  $st = $pdo->prepare("SELECT id FROM chat_faq WHERE slug=?");
  $st->execute([$slug]);
  $id = $st->fetchColumn();
  if ($id){
    $pdo->prepare("UPDATE chat_faq SET domanda=?,risposta=?,tag=?,cta=?,attiva=1,updated=datetime('now') WHERE id=?")
        ->execute([$domanda,$risposta,$tag,$cta,$id]);
  } else {
    $pdo->prepare("INSERT INTO chat_faq(slug,domanda,risposta,tag,cta,lang,attiva) VALUES(?,?,?,?,?, 'it', 1)")
        ->execute([$slug,$domanda,$risposta,$tag,$cta]);
    $id = (int)$pdo->lastInsertId();
  }
  $norm = dr_faq_normalize($domanda);
  $pdo->prepare("UPDATE chat_domande SET risolta=1, faq_id=? WHERE norm=?")->execute([(int)$id,$norm]);
  return (int)$id;
}

function dr_faq_hit($pdo, $id){
  try { $pdo->prepare("UPDATE chat_faq SET hit=hit+1 WHERE id=?")->execute([(int)$id]); } catch (Throwable $e) {}
}

/* --------------------------------------------------------------------------
   ENTRY PRINCIPALE USER — ritorna una risposta sicura (FAQ+CTA) o null.
   Se null, chat.php prosegue con l'LLM (che ha comunque la KB nel system).
   Le domande non risolte vengono loggate per l'apprendimento.
--------------------------------------------------------------------------- */
function dr_faq_answer($pdo, $text, $ctx=[]){
  if (!$pdo) return null;
  $lang     = $ctx['lang'] ?? 'it';
  $llm      = !empty($ctx['llm']);       // l'LLM e' disponibile?
  $isMember = !empty($ctx['is_member']);
  $uid      = (int)($ctx['uid'] ?? 0);
  $bot      = $ctx['bot'] ?? 'site';

  [$row, $score] = dr_faq_match($pdo, $text, $lang);

  // Se l'LLM c'e' e la lingua non e' italiano, lascio rispondere l'LLM nella
  // lingua giusta (ma loggo comunque per imparare).
  $preferLLM = ($llm && $lang !== 'it');

  if ($row && $score >= DR_FAQ_SOGLIA && !$preferLLM){
    dr_faq_hit($pdo, $row['id']);
    return dr_faq_format($row, ['is_member'=>$isMember]);
  }

  // Non abbastanza sicuro: log per crescita KB.
  dr_faq_log_domanda($pdo, $text, $uid, $bot);
  return null;
}

/* --------------------------------------------------------------------------
   ADMIN — dati reali in SOLA LETTURA.
--------------------------------------------------------------------------- */
function dr_faq_kpi($pdo){
  $q = function($sql) use ($pdo){ try { return (int)$pdo->query($sql)->fetchColumn(); } catch (Throwable $e){ return null; } };
  if (!function_exists('mkt_armato')){ global $pdo; @require_once __DIR__.'/mailer.php'; } // mailer.php assume $pdo globale
  $armato = function_exists('mkt_armato') ? mkt_armato($pdo)
          : (($pdo->query("SELECT v FROM mkt_meta WHERE k='armato'")->fetchColumn() ?? '')==='1');
  return [
    'armato'        => $armato,
    'in_coda'       => $q("SELECT COUNT(*) FROM mkt_queue WHERE status='pending'"),
    'contatti'      => $q("SELECT COUNT(*) FROM contacts"),
    'contatti_att'  => $q("SELECT COUNT(*) FROM contacts WHERE status='attivo'"),
    'inviate'       => $q("SELECT COUNT(*) FROM emails_log WHERE status='sent'"),
    'iscritti'      => $q("SELECT COUNT(*) FROM subscribers"),
    'ordini'        => $q("SELECT COUNT(*) FROM orders"),
    'ordini_pagati' => $q("SELECT COUNT(*) FROM orders WHERE status='paid'"),
    'membri_kit'    => $q("SELECT COUNT(*) FROM users WHERE COALESCE(kit_owned,0)=1"),
    'membership'    => $q("SELECT COUNT(*) FROM users WHERE COALESCE(membership_active,0)=1"),
    'faq_attive'    => $q("SELECT COUNT(*) FROM chat_faq WHERE attiva=1"),
    'domande_open'  => $q("SELECT COUNT(*) FROM chat_domande WHERE risolta=0"),
  ];
}

/* Snapshot da iniettare nel system prompt admin (l'LLM risponde coi numeri veri). */
function dr_faq_admin_snapshot($pdo){
  $k = dr_faq_kpi($pdo);
  $v = function($x){ return $x === null ? 'n/d' : $x; };
  return
    "Macchina email: ".($k['armato'] ? 'ARMATA (le email partono)' : 'STAND-BY (niente parte')."; ".
    "in coda ".$v($k['in_coda'])."; inviate ".$v($k['inviate']).". ".
    "Contatti: ".$v($k['contatti'])." totali (".$v($k['contatti_att'])." attivi); iscritti newsletter ".$v($k['iscritti']).". ".
    "Ordini: ".$v($k['ordini'])." (".$v($k['ordini_pagati'])." pagati). ".
    "Membri col KIT: ".$v($k['membri_kit'])."; membership attive ".$v($k['membership']).". ".
    "Chat: ".$v($k['faq_attive'])." FAQ attive, ".$v($k['domande_open'])." domande utente da rivedere (admin-chat.php). ".
    "Dove armare/disarmare l'email: admin-email.php. KPI email: marketing-hub.php.";
}

/* Risposta operativa deterministica per l'admin (fallback senza LLM). null se
   nessun intent operativo riconosciuto -> lascia rispondere l'LLM. */
function dr_faq_admin_answer($pdo, $text){
  $n = dr_faq_normalize($text);
  if ($n === '') return null;
  $has = function(...$w) use ($n){ foreach ($w as $x){ if (strpos($n, $x) !== false) return true; } return false; };
  $k = dr_faq_kpi($pdo);
  $v = function($x){ return $x === null ? 'n/d' : $x; };

  if ($has('armat','macchina','stand by','standby','email part','motore email')){
    return "Macchina email: ".($k['armato']?'ARMATA — le email partono':'STAND-BY — niente parte').".\n".
           "In coda: ".$v($k['in_coda'])." · Inviate: ".$v($k['inviate'])." · Contatti attivi: ".$v($k['contatti_att']).".\n".
           "Interruttore (1 click): admin-email.php.";
  }
  if ($has('contatt','lead','iscritt','database','lista')){
    return "Contatti: ".$v($k['contatti'])." totali, ".$v($k['contatti_att'])." attivi. Iscritti newsletter: ".$v($k['iscritti']).".\n".
           "Import/gestione contatti dai tuoi tool admin; invii da marketing-hub.php.";
  }
  if ($has('ordini','vendite','acquisti','fatturat')){
    return "Ordini: ".$v($k['ordini'])." totali, ".$v($k['ordini_pagati'])." pagati.\n".
           "Membri col KIT: ".$v($k['membri_kit'])." · Membership attive: ".$v($k['membership']).".";
  }
  if ($has('membri','soci','kit','membership')){
    return "Membri col KIT: ".$v($k['membri_kit'])." · Membership attive: ".$v($k['membership']).".";
  }
  if ($has('domande','faq','imparare','apprendiment','chat non','candidat')){
    $cand = dr_faq_candidate($pdo, 2);
    return "Chat: ".$v($k['faq_attive'])." FAQ attive, ".$v($k['domande_open'])." domande da rivedere. ".
           "Candidate FAQ (domande ricorrenti): ".count($cand).".\n".
           "Rivedi e promuovi a FAQ da: admin-chat.php.";
  }
  if ($has('kpi','statistic','numeri','dati','quanti','riepilogo','stato','situazione','dashboard')){
    return "Riepilogo live:\n".
           "· Email: ".($k['armato']?'ARMATA':'STAND-BY').", ".$v($k['in_coda'])." in coda, ".$v($k['inviate'])." inviate\n".
           "· Contatti: ".$v($k['contatti'])." (".$v($k['contatti_att'])." attivi) · Iscritti: ".$v($k['iscritti'])."\n".
           "· Ordini: ".$v($k['ordini'])." (".$v($k['ordini_pagati'])." pagati) · Membri KIT: ".$v($k['membri_kit'])."\n".
           "· Chat: ".$v($k['faq_attive'])." FAQ, ".$v($k['domande_open'])." domande aperte";
  }
  return null;
}

/* Verifica se un LLM e' configurato (senza chiamarlo), per decidere il fallback. */
function dr_faq_llm_pronto(){
  @require_once __DIR__.'/inc/dr-ai.php';
  if (!function_exists('dr_ai_stato')) return false;
  foreach (dr_ai_stato() as $f){ if (!empty($f['chiave'])) return true; }
  return false;
}
