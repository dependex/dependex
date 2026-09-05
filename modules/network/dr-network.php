<?php
/* ============================================================================
   DESTINO RANDAGIO — NETWORK ENGINE del Branco (Community Growth Program)

   Cosa fa, in una riga: LEGGE e RIPORTA la rete di membri (referral, ranghi,
   Branco, volume reale) e REGISTRA le transizioni di rango. Non crea economia,
   non muove DRX, non inventa un piano compensi.

   ─────────────────────────────────────────────────────────────────────────
   COMPLIANCE (il perche' questo NON e' un MLM a provvigioni sul reclutamento)
   ─────────────────────────────────────────────────────────────────────────
   Questo motore e' un PROGRAMMA DI CRESCITA DELLA COMUNITA', non un piano di
   compensi piramidale. Le regole non negoziabili:

   1) I PREMI RESTANO LEGATI AGLI ACQUISTI REALI. L'unico payout di rete e'
      dr_referral_reward() (referral.php): DRX all'upline SOLO su euro DAVVERO
      spesi dalla rete, a tassi decrescenti su 8 livelli. Questo motore NON lo
      tocca e NON introduce alcun pagamento per il solo reclutamento.
      (dr_set_referrer() da' solo un piccolo DRX di BENVENUTO al NUOVO iscritto,
      mai all'invitante: nessuna provvigione da iscrizione.)

   2) IL RANGO E' MERITO, NON SI COMPRA. Il rango e' SEMPRE drx_effective()
      (drx.php) — l'unica fonte. Qui si LEGGE, mai si ricalcola, mai si scrive.

   3) IL "PASS DEL BRANCO" E' UNO STATO, NON UN LIVELLO A PAGAMENTO. E' derivato
      dal rango di merito (dr_net_pass($rank_idx)): niente nuovo pagamento,
      niente sblocco per reclutamento, nessuna meccanica monetaria. E' solo
      un'etichetta di traguardo (achievement-derived) restituita come DATO.

   4) LE UNICHE SCRITTURE di questo file sono: la tabella rank_history (storia
      delle promozioni/retrocessioni, per il Command Center) e i log/notifiche
      via dr_log(source='network'). Wallet, ledger, saldi, ranghi: MAI toccati.

   ─────────────────────────────────────────────────────────────────────────
   SICUREZZA
   ─────────────────────────────────────────────────────────────────────────
   Solo prepared statement (nessuna variabile interpolata in SQL; gli unici
   valori inline sono interi gia' castati (int), come nel resto del codebase per
   l'affinity SQLite). Nessuna credenziale in chiaro. Key-guard con hash_equals.
   Sessione per i dati del singolo utente. Nessuna PII (email) negli output:
   solo iniziali/handle (riusa dr_lb_alias di leaderboard.php).

   ─────────────────────────────────────────────────────────────────────────
   RIUSO (non si reimplementa l'economia)
   ─────────────────────────────────────────────────────────────────────────
   drx_effective/drx_ranks (drx.php) · dr_rank_soglie (dr-economy-config.php) ·
   dr_upline/dr_user_by_sic (referral.php) · dr_can_enter_branco/
   dr_membership_state (billing.php) · dr_leaderboard/dr_lb_alias
   (leaderboard.php) · dr_log (dr-log.php). Il motore qualita' (dr-qualita.php)
   audita gia' profondita' referral e coerenza rango: NON si duplica qui.

   ─────────────────────────────────────────────────────────────────────────
   ENDPOINT (mirror di dr-qualita.php / promo-motore.php)
   ─────────────────────────────────────────────────────────────────────────
     dr-network.php?json=stato                      -> snapshot del membro loggato ($_SESSION['uid'])
     dr-network.php?json=stato&uid=&key=<DR_CRON_KEY>-> snapshot di un uid (solo con key server)
     dr-network.php?json=albero                     -> albero downline del membro loggato
     dr-network.php?json=albero&uid=&key=<DR_CRON_KEY>-> albero di un uid (solo con key server)
     dr-network.php?json=command&key=<DR_CRON_KEY>  -> overview admin (403 senza key)
     dr-network.php?run=network&key=<DR_CRON_KEY>   -> dr_net_scan (scheduler, da visits.php)
   Key: hash_equals(dr_env('DR_CRON_KEY','DRCRON')). Incluso come libreria
   (realpath-guard): non stampa nulla, espone solo funzioni.
============================================================================ */

if (!function_exists('dr_net_stato')) {

require_once __DIR__ . '/db.php';                 // $pdo, users/orders
require_once __DIR__ . '/drx.php';                // drx_effective, drx_ranks (rango = unica fonte)
require_once __DIR__ . '/dr-economy-config.php';  // dr_rank_soglie (soglie rango)
require_once __DIR__ . '/referral.php';           // dr_upline, dr_user_by_sic (catena referrer_id)
@require_once __DIR__ . '/billing.php';           // dr_can_enter_branco, dr_membership_state
@require_once __DIR__ . '/dr-pass.php';           // dr_pass_stato (Pass del Branco: stato REALE, se presente)
@require_once __DIR__ . '/leaderboard.php';       // dr_leaderboard, dr_lb_alias (privacy)
@require_once __DIR__ . '/dr-log.php';            // dr_log (notifiche/watchdog)
@require_once __DIR__ . '/dr-env.php';            // dr_env

/* --- tetti di sicurezza: mai ricorsione/scansione senza freno --------------- */
if (!defined('DR_NET_MAXDEPTH')) define('DR_NET_MAXDEPTH', 8);   // profondita' downline max
if (!defined('DR_NET_MAXNODES')) define('DR_NET_MAXNODES', 500); // nodi totali max per traversata
if (!defined('DR_NET_BATCH'))    define('DR_NET_BATCH', 500);    // membri per lotto (scan/command)
if (!defined('DR_NET_ORDERS_LIMIT')) define('DR_NET_ORDERS_LIMIT', 20000); // tetto righe orders lette

/* base del sito per il link referral: SITE_URL da .env, poi config/site.php */
function dr_net_site() {
  $s = function_exists('dr_env') ? (string)dr_env('SITE_URL', '') : '';
  if ($s === '' && function_exists('dr_site')) { $c = dr_site(); $s = (string)($c['base_url'] ?? ''); }
  if ($s === '') $s = 'https://destinorandagio.it';
  return rtrim($s, '/');
}

/* --- helper schema-driven (PRAGMA), come dr-qualita.php: niente assunzioni --- */
function dr_net_ha_tabella($pdo, $tab) {
  try {
    $st = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=?");
    $st->execute([$tab]);
    return (bool)$st->fetchColumn();
  } catch (Throwable $e) { return false; }
}
function dr_net_ha_colonna($pdo, $tab, $col) {
  try {
    $st = $pdo->query("PRAGMA table_info(" . preg_replace('/[^A-Za-z0-9_]/', '', $tab) . ")");
    foreach ($st as $r) { if (strcasecmp($r['name'], $col) === 0) return true; }
  } catch (Throwable $e) {}
  return false;
}

/* --- nome anonimizzato: riusa dr_lb_alias (leaderboard) se c'e', altrimenti
   fallback a sole iniziali. MAI email negli output. --- */
function dr_net_alias($full, $user, $sic, $isVirtual = false) {
  if (function_exists('dr_lb_alias')) return dr_lb_alias($full, $user, $sic, (bool)$isVirtual);
  $base = trim((string)$full); if ($base === '') $base = trim((string)$user);
  if ($base === '') return 'Randagio ' . substr((string)$sic, -4);
  $parts = preg_split('/\s+/', $base);
  if (isset($parts[1])) return mb_substr($parts[0], 0, 1) . '. ' . mb_substr($parts[1], 0, 1) . '.';
  return mb_substr($parts[0], 0, 3) . '…';
}

/* ============================================================================
   MIGRAZIONE — rank_history (storia promozioni/retrocessioni). Additiva,
   idempotente. E' l'UNICA tabella che questo motore scrive (oltre a dr_log).
============================================================================ */
function dr_net_migra($pdo) {
  static $done = false; if ($done) return; $done = true;
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS rank_history(
      id        INTEGER PRIMARY KEY AUTOINCREMENT,
      uid       INTEGER,
      rank_idx  INTEGER,
      rank_name TEXT,
      direzione TEXT,                 -- 'promozione' | 'retrocessione' | 'iniziale'
      motivo    TEXT,
      creato    TEXT DEFAULT (datetime('now')))");
    $pdo->exec("CREATE INDEX IF NOT EXISTS ix_rankhist_uid ON rank_history(uid, id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS ix_rankhist_creato ON rank_history(creato)");
  } catch (Throwable $e) {}
  /* is_virtual: la aggiunge di norma seed-virtual-users.php, ma il network engine
     la usa in piu' query. Garantiamo la colonna (additiva, idempotente) cosi' non
     va MAI in fatal su un DB che non ha ancora girato il seed. */
  try { $pdo->exec("ALTER TABLE users ADD COLUMN is_virtual INTEGER DEFAULT 0"); } catch (Throwable $e) {}
}

/* ============================================================================
   3) PASS DEL BRANCO — status DERIVATO DAL RANGO DI MERITO. Solo DATO.
   Nessun pagamento, nessuno sblocco per reclutamento, nessuna moneta.
============================================================================ */
function dr_net_pass($rank_idx) {
  $i = (int)$rank_idx;
  /* soglie di merito (idx del rango), non prezzi: il Pass si guadagna salendo. */
  if ($i >= 8) {
    $tier = 'eternal'; $name = 'Eternal Pass';
    $benefits = ['Accesso totale ai drop', 'Consiglio del Branco', 'Peso di voto massimo nella DAO', 'Priorita' . "'" . ' assoluta eventi live'];
  } elseif ($i >= 7) {
    $tier = 'alpha'; $name = 'Alpha Pass';
    $benefits = ['Whitelist prioritaria NFT', 'Governance estesa', 'Eventi privati', 'Premi referral top (su acquisti reali)'];
  } elseif ($i >= 6) {
    $tier = 'explorer'; $name = 'Explorer Pass';
    $benefits = ['Anteprime assolute', 'Drop riservati', 'Peso di voto x2'];
  } else {
    $tier = 'none'; $name = '';
    $benefits = [];
  }
  return [
    'tier'       => $tier,          // none|explorer|alpha|eternal
    'name'       => $name,          // etichetta pubblica ('' se nessun pass)
    'rank_idx'   => $i,
    'benefits'   => $benefits,      // set di benefici (solo descrittivi)
    'compliant'  => true,
    'nota'       => 'Status derivato dal rango di merito (drx_effective). Non e\' acquistabile ne\' sbloccabile col reclutamento: nessuna meccanica monetaria.',
  ];
}

/* progresso % verso il rango successivo, dalle soglie ufficiali (riusa la
   logica next-threshold di drx_effective: rank/next portano gia' 'min'). */
function dr_net_progress($eff) {
  $cur  = $eff['rank'] ?? null;
  $next = $eff['next'] ?? null;
  $bal  = (float)($eff['bal'] ?? 0);
  if (!$next || !isset($next['min'])) return 100.0;          // rango massimo
  $curMin  = (float)($cur['min'] ?? 0);
  $nextMin = (float)$next['min'];
  if ($nextMin <= $curMin) return 100.0;
  $p = ($bal - $curMin) / ($nextMin - $curMin) * 100.0;
  if ($p < 0) $p = 0.0; if ($p > 100) $p = 100.0;
  return round($p, 1);
}

/* ============================================================================
   DOWNLINE (referrer_id, NON binaria) — insieme degli uid della rete, con
   freno rigido su profondita' E numero di nodi. Niente ricorsione infinita:
   BFS iterativa con visited (anti-ciclo) e budget di nodi condiviso.
   Ritorna: ['ids'=>[uid...], 'directs'=>N, 'truncated'=>bool, 'maxDepth'=>D]
============================================================================ */
function dr_net_downline_ids($pdo, $uid, $maxDepth = DR_NET_MAXDEPTH, $maxNodes = DR_NET_MAXNODES) {
  $uid = (int)$uid;
  $maxDepth = max(1, min((int)$maxDepth, 32));
  $maxNodes = max(1, min((int)$maxNodes, 5000));
  $ids = [];
  $visited = [$uid => true];
  $directs = 0;
  $truncated = false;

  $st = $pdo->prepare("SELECT id FROM users WHERE referrer_id=? ORDER BY id");
  /* livello per livello: $frontier sono gli uid del livello corrente */
  $frontier = [$uid];
  for ($depth = 1; $depth <= $maxDepth; $depth++) {
    $next = [];
    foreach ($frontier as $parent) {
      $st->execute([(int)$parent]);
      foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $child) {
        $child = (int)$child;
        if (isset($visited[$child])) continue;              // anti-ciclo
        if (count($ids) >= $maxNodes) { $truncated = true; break 3; } // freno nodi
        $visited[$child] = true;
        $ids[] = $child;
        $next[] = $child;
        if ($depth === 1) $directs++;
      }
    }
    if (!$next) break;
    $frontier = $next;
  }
  return ['ids' => $ids, 'directs' => $directs, 'truncated' => $truncated, 'maxDepth' => $maxDepth];
}

/* conteggio referral DIRETTI (rapido, indicizzato) */
function dr_net_directs_count($pdo, $uid) {
  try {
    $st = $pdo->prepare("SELECT COUNT(*) FROM users WHERE referrer_id=?");
    $st->execute([(int)$uid]);
    return (int)$st->fetchColumn();
  } catch (Throwable $e) { return 0; }
}

/* ============================================================================
   TEAM VOLUME — euro REALI spesi dalla downline. Fonte: orders PAGATI
   (status paid/fulfilled), attribuiti al membro dal tag 'uid:NNN' nel campo
   customer (convenzione di album-checkout.php / drx-ricarica.php). Aggiunge
   nft_ordini pagati (che hanno colonna uid). Sola lettura, LIMIT-guarded.
============================================================================ */
function dr_net_team_volume($pdo, $ids) {
  $ids = array_values(array_unique(array_map('intval', (array)$ids)));
  if (!$ids) return 0.0;
  $set = array_fill_keys($ids, true);
  $tot = 0.0;

  /* (a) orders: nessuna colonna uid -> l'uid sta in customer come "...uid:NNN".
     Si legge un lotto guardato di ordini pagati e si somma per gli uid del set.
     Prepared statement; nessun valore utente interpolato. */
  if (dr_net_ha_tabella($pdo, 'orders')
      && dr_net_ha_colonna($pdo, 'orders', 'customer')
      && dr_net_ha_colonna($pdo, 'orders', 'total_eur')) {
    try {
      $st = $pdo->prepare("SELECT customer, total_eur FROM orders
                           WHERE status IN('paid','fulfilled') ORDER BY id DESC LIMIT ?");
      $st->execute([(int)DR_NET_ORDERS_LIMIT]);
      foreach ($st as $o) {
        if (preg_match('/uid:(\d+)/', (string)$o['customer'], $m)) {
          $ou = (int)$m[1];
          if (isset($set[$ou])) $tot += (float)$o['total_eur'];
        }
      }
    } catch (Throwable $e) {}
  }

  /* (b) nft_ordini: colonna uid + euro, stato 'pagato'. IN(...) con SOLI interi
     gia' castati (safe). */
  if (dr_net_ha_tabella($pdo, 'nft_ordini')
      && dr_net_ha_colonna($pdo, 'nft_ordini', 'uid')
      && dr_net_ha_colonna($pdo, 'nft_ordini', 'euro')) {
    try {
      $in = implode(',', $ids);   // solo interi castati sopra
      $tot += (float)$pdo->query("SELECT COALESCE(SUM(euro),0) FROM nft_ordini
                                  WHERE stato='pagato' AND uid IN ($in)")->fetchColumn();
    } catch (Throwable $e) {}
  }

  return round($tot, 2);
}

/* ============================================================================
   1) SNAPSHOT DEL MEMBRO — sola lettura. Rango SEMPRE da drx_effective.
============================================================================ */
function dr_net_stato($pdo, $uid) {
  $uid = (int)$uid;
  if ($uid <= 0) return ['ok' => false, 'error' => 'uid non valido'];
  dr_net_migra($pdo);   /* garantisce rank_history + colonna is_virtual */

  /* anagrafica minima (nessuna email negli output) */
  $st = $pdo->prepare("SELECT COALESCE(internal_code,'') ic, COALESCE(full_name,'') fn,
                              COALESCE(username,'') un, COALESCE(is_virtual,0) iv
                       FROM users WHERE id=?");
  $st->execute([$uid]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if (!$u) return ['ok' => false, 'error' => 'utente inesistente'];

  $eff  = drx_effective($pdo, $uid);                    // UNICA fonte del rango
  $idx  = (int)$eff['idx'];
  $name = (string)($eff['rank']['name'] ?? '');

  $down = dr_net_downline_ids($pdo, $uid, DR_NET_MAXDEPTH, DR_NET_MAXNODES);
  $teamVol = dr_net_team_volume($pdo, $down['ids']);

  $inBranco = function_exists('dr_can_enter_branco') ? (bool)dr_can_enter_branco($pdo, $uid) : false;

  return [
    'ok'            => true,
    'uid'           => $uid,
    'sicid'         => (string)$u['ic'],                 // SIC-ID (internal_code)
    'alias'         => dr_net_alias($u['fn'], $u['un'], $u['ic'], (int)$u['iv'] === 1),
    'rank_idx'      => $idx,
    'rank_name'     => $name,
    'rank_progress' => dr_net_progress($eff),            // % verso il prossimo rango
    'drx_merito'    => (int)round((float)$eff['bal']),
    'directs'       => dr_net_directs_count($pdo, $uid), // referral diretti
    'team_size'     => count($down['ids']),              // downline totale (cap DR_NET_MAXNODES)
    'team_truncated'=> $down['truncated'],
    'team_volume'   => $teamVol,                         // euro REALI spesi dalla downline
    'membership'    => $inBranco,                        // gate Branco (kit + membership)
    'ref_link'      => dr_net_site() . '/r/' . rawurlencode((string)$u['ic']),
    'pass'          => dr_net_pass($idx),                // etichetta derivata dal merito (retro-compat)
    /* PASS DEL BRANCO — stato REALE dell'abbonamento (dr-pass.php), se presente.
       Non sostituisce dr_net_pass: lo affianca con il dato di sottoscrizione vero. */
    'pass_reale'    => function_exists('dr_pass_stato') ? dr_pass_stato($pdo, $uid) : null,
  ];
}

/* ============================================================================
   2) DOWNLINE EXPLORER — albero referrer_id, cap profondita' + nodi. Privacy:
   iniziali/handle, mai email. Ogni nodo: sicid, name, rank_idx, rank_name,
   attivo, directs.
============================================================================ */
function dr_net_albero($pdo, $uid, $maxDepth = 5, $maxNodes = 500) {
  $uid = (int)$uid;
  $maxDepth = max(1, min((int)$maxDepth, DR_NET_MAXDEPTH));
  $maxNodes = max(1, min((int)$maxNodes, DR_NET_MAXNODES));

  $stChildren = $pdo->prepare(
    "SELECT id, COALESCE(internal_code,'') ic, COALESCE(full_name,'') fn,
            COALESCE(username,'') un, COALESCE(is_virtual,0) iv,
            COALESCE(membership_active,0) ma
     FROM users WHERE referrer_id=? ORDER BY id");

  $budget = ['n' => 0, 'max' => $maxNodes, 'truncated' => false];
  $visited = [$uid => true];

  $build = function ($parentUid, $depth) use (&$build, $pdo, $stChildren, $maxDepth, &$budget, &$visited) {
    if ($depth > $maxDepth) return [];
    $stChildren->execute([(int)$parentUid]);
    $rows = $stChildren->fetchAll(PDO::FETCH_ASSOC);
    $out = [];
    foreach ($rows as $r) {
      $cid = (int)$r['id'];
      if (isset($visited[$cid])) continue;               // anti-ciclo
      if ($budget['n'] >= $budget['max']) { $budget['truncated'] = true; break; }
      $visited[$cid] = true; $budget['n']++;
      $eff = drx_effective($pdo, $cid);                  // rango = unica fonte
      $node = [
        'sicid'     => (string)$r['ic'],
        'name'      => dr_net_alias($r['fn'], $r['un'], $r['ic'], (int)$r['iv'] === 1),
        'rank_idx'  => (int)$eff['idx'],
        'rank_name' => (string)($eff['rank']['name'] ?? ''),
        'attivo'    => ((int)$r['ma'] === 1),
        'children'  => $build($cid, $depth + 1),
      ];
      $node['directs'] = count($node['children']);
      $out[] = $node;
    }
    return $out;
  };

  $tree = $build($uid, 1);
  return [
    'ok'        => true,
    'uid'       => $uid,
    'maxDepth'  => $maxDepth,
    'maxNodes'  => $maxNodes,
    'nodes'     => $budget['n'],
    'truncated' => $budget['truncated'],
    'tree'      => $tree,
  ];
}

/* ============================================================================
   4) REGISTRA RANGO — legge il rango live (drx_effective), lo confronta con
   l'ultimo loggato per l'uid; se cambia inserisce una riga in rank_history e
   notifica via dr_log(source='network'). Idempotente (nessun doppione se
   invariato). Il primo rilevamento crea una riga 'iniziale' (baseline) SENZA
   notifica: il rango esiste gia' per merito, qui si LOGGA solo la transizione.
============================================================================ */
function dr_net_registra_rango($pdo, $uid) {
  $uid = (int)$uid;
  if ($uid <= 0) return ['ok' => false, 'cambiato' => false];
  dr_net_migra($pdo);

  $eff  = drx_effective($pdo, $uid);
  $idx  = (int)$eff['idx'];
  $name = (string)($eff['rank']['name'] ?? '');

  try {
    $st = $pdo->prepare("SELECT rank_idx FROM rank_history WHERE uid=? ORDER BY id DESC LIMIT 1");
    $st->execute([$uid]);
    $last = $st->fetchColumn();
  } catch (Throwable $e) { return ['ok' => false, 'cambiato' => false]; }

  if ($last === false) {
    /* primo rilevamento: baseline, nessuna notifica (non e' una transizione) */
    try {
      $pdo->prepare("INSERT INTO rank_history(uid,rank_idx,rank_name,direzione,motivo)
                     VALUES(?,?,?,?,?)")
          ->execute([$uid, $idx, $name, 'iniziale', 'primo rilevamento']);
    } catch (Throwable $e) {}
    return ['ok' => true, 'cambiato' => false, 'idx' => $idx, 'direzione' => 'iniziale'];
  }

  $last = (int)$last;
  if ($idx === $last) return ['ok' => true, 'cambiato' => false, 'idx' => $idx]; // invariato: idempotente

  $dir = ($idx > $last) ? 'promozione' : 'retrocessione';
  try {
    $pdo->prepare("INSERT INTO rank_history(uid,rank_idx,rank_name,direzione,motivo)
                   VALUES(?,?,?,?,?)")
        ->execute([$uid, $idx, $name, $dir, 'da idx ' . $last . ' a idx ' . $idx]);
  } catch (Throwable $e) {}

  /* notifica/watchdog: evento di rete, source='network'. Niente PII. */
  if (function_exists('dr_log')) {
    @dr_log($pdo, 'network', 'rango',
      ['direzione' => $dir, 'da' => $last, 'a' => $idx, 'rank' => $name],
      $uid, null, 'network');
  }
  return ['ok' => true, 'cambiato' => true, 'idx' => $idx, 'da' => $last, 'direzione' => $dir];
}

/* ============================================================================
   dr_net_scan — passa i membri a lotti, registra le transizioni di rango e
   "rinfresca" la leaderboard richiamando la funzione esistente. LIMIT-guarded.
============================================================================ */
function dr_net_scan($pdo, $batch = DR_NET_BATCH) {
  dr_net_migra($pdo);
  $batch = max(1, min((int)$batch, 5000));

  $membri = 0; $promozioni = 0; $retrocessioni = 0; $baseline = 0;
  try {
    $st = $pdo->prepare("SELECT id FROM users WHERE COALESCE(role,'user')<>'admin' ORDER BY id LIMIT ?");
    $st->execute([$batch]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $id) {
      $r = dr_net_registra_rango($pdo, (int)$id);
      $membri++;
      if (!empty($r['cambiato'])) {
        if (($r['direzione'] ?? '') === 'promozione') $promozioni++;
        elseif (($r['direzione'] ?? '') === 'retrocessione') $retrocessioni++;
      } elseif (($r['direzione'] ?? '') === 'iniziale') {
        $baseline++;
      }
    }
  } catch (Throwable $e) {}

  /* rinfresco leaderboard: si richiama la funzione esistente (unica fonte),
     non si ricalcola nulla. dr_leaderboard e' read-only/live. */
  $lb = 0;
  if (function_exists('dr_leaderboard')) {
    try { $lb = count(dr_leaderboard($pdo, 20, false)); } catch (Throwable $e) {}
  }

  return [
    'generato'      => gmdate('Y-m-d H:i:s'),
    'membri'        => $membri,
    'promozioni'    => $promozioni,
    'retrocessioni' => $retrocessioni,
    'baseline'      => $baseline,
    'leaderboard'   => $lb,
  ];
}

/* storia recente promozioni/retrocessioni (per il Command Center) */
function dr_net_storia($pdo, $limit = 50) {
  dr_net_migra($pdo);
  $limit = max(1, min((int)$limit, 500));
  try {
    $st = $pdo->prepare(
      "SELECT h.uid, h.rank_idx, h.rank_name, h.direzione, h.creato,
              COALESCE(u.internal_code,'') ic, COALESCE(u.full_name,'') fn,
              COALESCE(u.username,'') un, COALESCE(u.is_virtual,0) iv
       FROM rank_history h LEFT JOIN users u ON u.id=h.uid
       WHERE h.direzione IN('promozione','retrocessione')
       ORDER BY h.id DESC LIMIT ?");
    $st->execute([$limit]);
    $out = [];
    foreach ($st as $r) {
      $out[] = [
        'sicid'     => (string)$r['ic'],
        'alias'     => dr_net_alias($r['fn'], $r['un'], $r['ic'], (int)$r['iv'] === 1),
        'rank_idx'  => (int)$r['rank_idx'],
        'rank_name' => (string)$r['rank_name'],
        'direzione' => (string)$r['direzione'],
        'creato'    => (string)$r['creato'],
      ];
    }
    return $out;
  } catch (Throwable $e) { return []; }
}

/* ============================================================================
   5) COMMAND CENTER (admin) — overview della rete da DATI REALI. Read-only,
   LIMIT-guarded. Distribuzione per rango calcolata sul lotto scansionato.
============================================================================ */
function dr_net_command($pdo, $batch = DR_NET_BATCH, $topN = 10) {
  $batch = max(1, min((int)$batch, 5000));
  $topN  = max(1, min((int)$topN, 100));
  dr_net_migra($pdo);

  $tot = 0; $attivi = 0; $inattivi = 0; $virtuali = 0;
  $dist = array_fill(0, 9, 0);              // per-rank idx 0..8

  /* lotto membri non-admin: totale, attivi/inattivi (membership), distribuzione rango */
  try {
    $st = $pdo->prepare("SELECT id, COALESCE(membership_active,0) ma, COALESCE(is_virtual,0) iv
                         FROM users WHERE COALESCE(role,'user')<>'admin' ORDER BY id LIMIT ?");
    $st->execute([$batch]);
    foreach ($st as $r) {
      $tot++;
      if ((int)$r['iv'] === 1) $virtuali++;
      if ((int)$r['ma'] === 1) $attivi++; else $inattivi++;
      $eff = drx_effective($pdo, (int)$r['id']);
      $i = (int)$eff['idx']; if ($i < 0) $i = 0; if ($i > 8) $i = 8;
      $dist[$i]++;
    }
  } catch (Throwable $e) {}

  /* attivi negli ultimi 30 giorni (recent activity) da dr_events */
  $attivi30 = 0;
  if (dr_net_ha_tabella($pdo, 'dr_events')) {
    try {
      $attivi30 = (int)$pdo->query(
        "SELECT COUNT(DISTINCT uid) FROM dr_events
         WHERE uid>0 AND COALESCE(source,'sito')<>'stress'
           AND creato>=datetime('now','-30 days')")->fetchColumn();
    } catch (Throwable $e) {}
  }

  /* top per referral DIRETTI */
  $topDirects = [];
  try {
    $st = $pdo->prepare(
      "SELECT r.referrer_id AS rid, COUNT(*) n,
              COALESCE(u.internal_code,'') ic, COALESCE(u.full_name,'') fn,
              COALESCE(u.username,'') un, COALESCE(u.is_virtual,0) iv
       FROM users r JOIN users u ON u.id=r.referrer_id
       WHERE r.referrer_id IS NOT NULL AND r.referrer_id>0
       GROUP BY r.referrer_id ORDER BY n DESC LIMIT ?");
    $st->execute([$topN]);
    foreach ($st as $r) {
      $topDirects[] = [
        'sicid'   => (string)$r['ic'],
        'alias'   => dr_net_alias($r['fn'], $r['un'], $r['ic'], (int)$r['iv'] === 1),
        'directs' => (int)$r['n'],
      ];
    }
  } catch (Throwable $e) {}

  /* top per drx_effective: riusa la leaderboard (unica fonte) */
  $topMerito = [];
  if (function_exists('dr_leaderboard')) {
    try { $topMerito = dr_leaderboard($pdo, $topN, false); } catch (Throwable $e) {}
  }

  /* volume di rete REALE totale: orders pagati + nft_ordini pagati */
  $volTot = 0.0;
  if (dr_net_ha_tabella($pdo, 'orders') && dr_net_ha_colonna($pdo, 'orders', 'total_eur')) {
    try {
      $volTot += (float)$pdo->query("SELECT COALESCE(SUM(total_eur),0) FROM orders
                                     WHERE status IN('paid','fulfilled')")->fetchColumn();
    } catch (Throwable $e) {}
  }
  if (dr_net_ha_tabella($pdo, 'nft_ordini') && dr_net_ha_colonna($pdo, 'nft_ordini', 'euro')) {
    try {
      $volTot += (float)$pdo->query("SELECT COALESCE(SUM(euro),0) FROM nft_ordini WHERE stato='pagato'")->fetchColumn();
    } catch (Throwable $e) {}
  }

  return [
    'generato'          => gmdate('Y-m-d H:i:s'),
    'membri_totali'     => $tot,
    'membri_reali'      => max(0, $tot - $virtuali),
    'membri_virtuali'   => $virtuali,
    'attivi_membership' => $attivi,
    'inattivi'          => $inattivi,           // alert: membership non attiva
    'attivi_30gg'       => $attivi30,           // recent activity
    'distribuzione'     => $dist,               // count per rango idx 0..8
    'top_directs'       => $topDirects,
    'top_merito'        => $topMerito,
    'transizioni'       => dr_net_storia($pdo, $topN),
    'volume_reale_tot'  => round($volTot, 2),
    'batch'             => $batch,
  ];
}

} /* fine guardia function_exists('dr_net_stato') */

/* ============================================================================
   ENDPOINT — solo se eseguito come pagina diretta (realpath-guard come
   dr-qualita.php / promo-motore.php). Incluso come libreria: NON stampa nulla.
============================================================================ */
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {

  require_once __DIR__ . '/dr-env.php';
  require_once __DIR__ . '/db.php';   // $pdo

  $atteso = function_exists('dr_env') ? (string)dr_env('DR_CRON_KEY', 'DRCRON') : 'DRCRON';
  $key    = (string)($_GET['key'] ?? $_POST['key'] ?? '');
  $keyOk  = ($key !== '') && hash_equals($atteso, $key);

  /* --- scheduler: dr_net_scan (come dr-qualita ?run) --- */
  if (isset($_GET['run']) && $_GET['run'] === 'network') {
    header('Content-Type: text/plain; charset=utf-8');
    if (!$keyOk) { http_response_code(403); echo "forbidden\n"; exit; }
    $r = dr_net_scan($pdo, DR_NET_BATCH);
    echo "network scan generato:" . $r['generato']
       . " membri:" . $r['membri']
       . " promozioni:" . $r['promozioni']
       . " retrocessioni:" . $r['retrocessioni']
       . " baseline:" . $r['baseline']
       . " leaderboard:" . $r['leaderboard'] . "\n";
    exit;
  }

  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  /* NIENTE CORS aperto sui dati autenticati: stato/albero sono per-utente e
     command e' admin. Nessun header Access-Control-Allow-Origin. */

  $json = (string)($_GET['json'] ?? '');

  /* --- COMMAND CENTER (admin): key obbligatoria, 403 senza --- */
  if ($json === 'command') {
    if (!$keyOk) { http_response_code(403); echo json_encode(['ok' => false, 'error' => 'forbidden']); exit; }
    echo json_encode(['ok' => true, 'ts' => time()] + dr_net_command($pdo), JSON_UNESCAPED_UNICODE);
    exit;
  }

  /* --- STATO / ALBERO (per-utente): sessione propria, oppure uid+key server ---
     Mai i dati di un altro utente senza chiave admin/server. */
  if ($json === 'stato' || $json === 'albero') {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $sessUid = (int)($_SESSION['uid'] ?? 0);
    $target  = 0;
    if ($keyOk && isset($_GET['uid'])) {
      $target = (int)$_GET['uid'];           // chiamata server: qualsiasi uid, ma solo con key
    } elseif ($sessUid > 0) {
      $target = $sessUid;                     // sessione: SOLO il proprio network
    }
    if ($target <= 0) {
      http_response_code(403);
      echo json_encode(['ok' => false, 'error' => 'auth richiesta (login o key server)']);
      exit;
    }
    if ($json === 'stato') {
      echo json_encode(['ok' => true, 'ts' => time(), 'stato' => dr_net_stato($pdo, $target)], JSON_UNESCAPED_UNICODE);
    } else {
      echo json_encode(['ok' => true, 'ts' => time()] + dr_net_albero($pdo, $target), JSON_UNESCAPED_UNICODE);
    }
    exit;
  }

  /* default: nessuna azione riconosciuta -> 400 (niente dump di dati) */
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'parametro json non valido (stato|albero|command) o run=network']);
  exit;
}
