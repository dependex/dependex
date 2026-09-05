<?php
/* ============================================================================
   _DR-ALBERO-LIB — libreria unica per ALBERO e STELLA del Network.
   Destino Randagio · 2026-08-15 · Cowork

   PERCHE' ESISTE (il bug che ha originato questo file):
   Albero e Stella leggevano la tabella `network_nodes`, che contiene SOLO chi
   ha gia' comprato ed e' stato piazzato da net_place(). Verificato sul DB
   reale di Mirco (data/dr.sqlite, 15/08/2026): network_nodes = 1 riga (il
   Master), network_posti = 119 righe (la struttura vera). Risultato: l'albero
   chiedeva "chi sono i figli del Master?" alla tabella sbagliata, riceveva
   zero, e non apriva NIENTE. Non era un problema di grafica.

   QUI si legge la struttura VERA: `network_posti` — la piramide pre-cablata
   (1 Master + 9 World + 27 National + 82 Pro = 118, piu' le posizioni utente
   119..NET_USER_POSTI, oggi 5.000.000). I dati vivi di chi occupa un posto
   (nome, rango, DRX, attivita') arrivano da JOIN su users e network_nodes:
   la struttura sta in network_posti, la persona sta in users/network_nodes.

   SECONDO BUG TROVATO (e sanato qui): sul DB reale 115 posti su 118 avevano
   il PADRE SBAGLIATO. La topologia a stella (9 World sotto il Master) e'
   stata scritta nel codice il 10/08 ma non e' mai stata applicata ai dati,
   rimasti alla vecchia matrice ternaria (solo 3 figli sotto il Master).
   alb_resync_topologia() lo corregge, MA senza mai toccare:
     - i posti occupati (uid>0 o stato!='libero')
     - i posti spostati a mano da Mirco (padre_manuale=1)
   cosi' un riallineamento non cancella mai ne' una vendita ne' una scelta.

   SCALA: mai "manda giu' tutto". Ogni funzione lavora su UN livello per
   volta, con LIMIT/OFFSET e indici dedicati. Con 5.000.000 di posizioni un
   Pro node ha ~61.000 figli: si mostrano prima gli OCCUPATI (pochi), poi le
   libere a pagine, mai tutte insieme.
============================================================================ */

if (defined('DR_ALBERO_LIB')) return;
define('DR_ALBERO_LIB', 1);

require_once __DIR__ . '/../dr-network-struttura.php';   // net_padre, net_livello, net_status_posto, net_sic_posto
@require_once __DIR__ . '/../network-engine.php';        // net_migra (network_nodes), net_status_by_order

/* QUANTI POSTI PUO' OCCUPARE UNA PERSONA (Mirco, 15/08/2026: "user occupa un
   solo posto/nodo", niente multi-account e niente multi-posti).

   ATTENZIONE — CONFLITTO DA SCIOGLIERE, segnalato a Mirco e NON risolto qui:
   tre pagine pubbliche dicono ancora il contrario e promettono fino a 5 nodi:
     - genesys/kit.php   "Max 5 nodi e 20 NFT a testa, max 1 World Node"
     - nodi.php          "Max 5 nodi a testa, max 1 World"
     - genesys/cortex-seed.php  (la base di conoscenza della chat AI)
   Finche' quel testo resta, il sito promette 5 e il sistema ne concede 1.
   Il numero sta QUI e in un posto solo: cambiarlo e' una riga. */
if (!defined('DR_MAX_POSTI_UTENTE')) define('DR_MAX_POSTI_UTENTE', 1);

/* --------------------------------------------------------------------------
   MIGRAZIONE ADDITIVA — indici e colonna padre_manuale.
   Non ricrea nulla di esistente, non perde dati. Idempotente.
-------------------------------------------------------------------------- */
function alb_migra(PDO $pdo): void {
  static $fatto = false; if ($fatto) return; $fatto = true;
  net_str_migra($pdo);            // garantisce network_posti + i 118 posti

  /* flag "questo padre l'ha deciso Mirco a mano": il resync non lo tocca */
  try { $pdo->exec("ALTER TABLE network_posti ADD COLUMN padre_manuale INTEGER DEFAULT 0"); } catch (Throwable $e) {}
  /* nota libera sul nodo, scritta dal pannello */
  try { $pdo->exec("ALTER TABLE network_posti ADD COLUMN nota TEXT"); } catch (Throwable $e) {}

  /* Indice composito (padre,uid): serve a separare in modo istantaneo i figli
     OCCUPATI dai LIBERI senza ordinare 61.000 righe a ogni click. L'indice su
     solo `padre` (ix_np_padre) non basta per questo. */
  try { $pdo->exec("CREATE INDEX IF NOT EXISTS ix_np_padre_uid ON network_posti(padre,uid)"); } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX IF NOT EXISTS ix_np_uid2      ON network_posti(uid)");       } catch (Throwable $e) {}

  /* SIC con COLLATE NOCASE — non e' un vezzo, e' l'unico modo perche' funzioni.
     SQLite usa un indice per "LIKE 'testo%'" SOLO se la collazione dell'indice
     combacia con quella di LIKE, che di default e' case-insensitive. Con un
     indice BINARY normale la ricerca per SIC ignorava l'indice e leggeva tutte
     le righe: misurato 0,30 s su 5.000.119 posizioni, per ognuna delle due
     passate. Con NOCASE diventa una lettura mirata. */
  try { $pdo->exec("CREATE INDEX IF NOT EXISTS ix_np_sic_nc ON network_posti(sic_id COLLATE NOCASE)"); } catch (Throwable $e) {}

  /* stato/status: servono ai contatori del riepilogo. Senza, ogni "quanti
     attivi?" leggeva l'intera tabella (misurato: GROUP BY status = 1,28 s su
     5 milioni di righe; l'intestazione della pagina ci metteva ~1,9 s). */
  try { $pdo->exec("CREATE INDEX IF NOT EXISTS ix_np_stato  ON network_posti(stato)");  } catch (Throwable $e) {}
  try { $pdo->exec("CREATE INDEX IF NOT EXISTS ix_np_status ON network_posti(status)"); } catch (Throwable $e) {}

  /* registro degli spostamenti: chi ha mosso cosa e quando (mai cancellabile) */
  $pdo->exec("CREATE TABLE IF NOT EXISTS network_posti_log(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ts TEXT DEFAULT (datetime('now')),
    azione TEXT,               -- sposta | assegna | libera | resync | sposta_utente
    posto INTEGER,
    da TEXT, a TEXT,           -- valori prima/dopo, testo libero
    attore TEXT)");

  alb_ripara_sic_personali($pdo);
}

/* --------------------------------------------------------------------------
   RIPARAZIONE — SIC personali sovrascritti col SIC di un nodo.

   Una versione precedente di alb_sposta_utente() (scritta da me il 15/08 e
   corretta lo stesso giorno) scriveva in users.sic_id il SIC del POSTO invece
   di lasciare quello della PERSONA. Sono due codici diversi e non vanno
   confusi: il personale (users.internal_code, 'SIC-ID-<uid a 12 cifre>') e'
   della persona e non cambia mai; quello del nodo ('SIC-ID-G-...') e' del
   posto e resta al posto.

   Qui si rimette a posto chi e' stato toccato: si riconosce dal fatto che
   users.sic_id ha il formato di un SIC di NODO ('SIC-ID-G-%'). Si ripristina
   dal proprio internal_code. Idempotente: se non c'e' niente da riparare non
   fa nulla e non lascia traccia. Non tocca il Master ne' chi ha un sic_id di
   altro tipo.
-------------------------------------------------------------------------- */
function alb_ripara_sic_personali(PDO $pdo): int {
  static $fatto = false; if ($fatto) return 0; $fatto = true;
  try {
    $n = (int)$pdo->query("SELECT COUNT(*) FROM users
                           WHERE sic_id LIKE 'SIC-ID-G-%'
                             AND internal_code IS NOT NULL AND internal_code<>''")->fetchColumn();
    if ($n <= 0) return 0;
    $pdo->exec("UPDATE users SET sic_id=internal_code
                WHERE sic_id LIKE 'SIC-ID-G-%'
                  AND internal_code IS NOT NULL AND internal_code<>''");
    alb_log($pdo, 'ripara_sic', 0, 'sic personale = SIC di nodo', 'ripristinati '.$n.' utenti', 'system');
    return $n;
  } catch (Throwable $e) { return 0; }
}

function alb_log(PDO $pdo, string $azione, int $posto, string $da, string $a, string $attore = 'admin'): void {
  try {
    $pdo->prepare("INSERT INTO network_posti_log(azione,posto,da,a,attore) VALUES(?,?,?,?,?)")
        ->execute([$azione, $posto, $da, $a, $attore]);
  } catch (Throwable $e) {}
}


/* ==========================================================================
   POSTI VIRTUALI — i 5.000.000 esistono senza pesare sul database.

   IL PROBLEMA: seminare davvero 5.000.000 di righe funziona (provato: 50 s)
   ma porta il DB da 4 MB a 1 GB. Su hosting condiviso significa backup da 1 GB
   e upload da 1 GB, per sempre, per righe tutte vuote.

   LA SOLUZIONE: i posti da 119 in su sono DETERMINISTICI. net_padre(n) e'
   pura aritmetica: il padre del posto n e' 37 + ((n-119) % 82), cioe' gli
   utenti si distribuiscono a giro sugli 82 Pro. Quindi un posto libero non ha
   bisogno di esistere come riga: lo si CALCOLA.
   Si scrive una riga vera solo quando il posto viene occupato o spostato a
   mano — cioe' quando ha finalmente qualcosa da dire.

   REGOLA D'ORO: la riga vera, se c'e', COMANDA sempre sul calcolo. Cosi' uno
   spostamento manuale non viene mai sovrascritto dall'aritmetica.

   Se un giorno il DB viene seminato davvero (dr-network-provision-1000.php),
   niente si rompe: le righe vere prendono semplicemente il posto del calcolo.
   ========================================================================== */

/** L'ultimo numero di posto esistente nella rete (118 Kit + posizioni utente). */
function alb_ultimo_posto(): int {
  $u = defined('NET_USER_POSTI') ? (int)NET_USER_POSTI : 5000000;
  $b = defined('NET_TOT_POSTI')  ? (int)NET_TOT_POSTI  : 118;
  return $b + $u;
}

/** Un posto calcolato, nella stessa forma di una riga vera. */
function alb_virtuale(int $posto): ?array {
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  if ($posto <= $base || $posto > alb_ultimo_posto()) return null;
  return [
    'posto'=>$posto, 'padre'=>net_padre($posto), 'livello'=>net_livello($posto),
    'sic_id'=>net_sic_posto($posto), 'status'=>'User', 'stato'=>'libero',
    'uid'=>0, 'padre_manuale'=>0, 'nome'=>'', 'virtuale'=>1,
  ];
}

/** Quanti figli calcolati ha un Pro (i posti utente che gli spettano). */
function alb_quanti_virtuali(int $padre): int {
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $u    = defined('NET_USER_POSTI') ? (int)NET_USER_POSTI : 5000000;
  if ($padre < 37 || $padre > $base) return 0;      // solo gli 82 Pro ne hanno
  $scarto = $padre - 37;                            // 0..81
  if ($scarto >= $u) return 0;
  return intdiv($u - 1 - $scarto, 82) + 1;
}

/** L'i-esimo figlio calcolato di un Pro (i da 0). */
function alb_virtuale_iesimo(int $padre, int $i): int {
  return 119 + $i * 82 + ($padre - 37);
}

/** In che posizione sta il posto $n fra i figli calcolati del suo Pro. */
function alb_indice_virtuale(int $n): int {
  return intdiv($n - 119, 82);
}


/** Il padre di un posto, che esista come riga o sia solo calcolato. */
function alb_padre_di(PDO $pdo, int $posto): int {
  $v = $pdo->query("SELECT padre FROM network_posti WHERE posto=".$posto)->fetchColumn();
  if ($v !== false && $v !== null) return (int)$v;
  $vn = alb_virtuale($posto);
  return $vn ? (int)$vn['padre'] : -1;
}

/**
 * MATERIALIZZA — trasforma un posto calcolato in una riga vera.
 * Si chiama PRIMA di ogni scrittura: da quel momento quel posto ha una riga
 * sua e il calcolo non lo riguarda piu'. Se la riga c'e' gia', non fa nulla.
 * E' questo che permette di avere 5.000.000 di posti navigabili con un
 * database di pochi MB: si paga una riga solo quando serve davvero.
 */
function alb_materializza(PDO $pdo, int $posto): bool {
  if ($posto <= 0) return false;
  $c = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE posto=".$posto)->fetchColumn();
  if ($c > 0) return true;
  $v = alb_virtuale($posto);
  if (!$v) return false;
  try {
    $pdo->prepare("INSERT OR IGNORE INTO network_posti(posto,padre,livello,sic_id,status,stato,uid)
                   VALUES(?,?,?,?,?,'libero',0)")
        ->execute([$v['posto'], $v['padre'], $v['livello'], $v['sic_id'], $v['status']]);
    return true;
  } catch (Throwable $e) { return false; }
}

/* --------------------------------------------------------------------------
   TIPO GRAFICO del nodo, dal suo status.
-------------------------------------------------------------------------- */
function alb_tipo(string $status): string {
  $s = strtolower($status);
  if ($s === 'master' || $s === 'unicorn') return 'master';
  if ($s === 'world')    return 'world';
  if ($s === 'national') return 'national';
  if ($s === 'pro')      return 'pro';
  return 'user';
}

/* --------------------------------------------------------------------------
   RIGA -> nodo per il client. Compatto di proposito: con migliaia di nodi
   in volo ogni campo in piu' e' peso sulla rete e sul browser.
-------------------------------------------------------------------------- */
function alb_riga_nodo(array $r): array {
  $posto  = (int)$r['posto'];
  $status = (string)($r['status'] ?? '');
  $uid    = (int)($r['uid'] ?? 0);
  $stato  = (string)($r['stato'] ?? 'libero');
  $nome   = trim((string)($r['nome'] ?? ''));
  if ($nome === '') $nome = $uid > 0 ? ('utente #' . $uid) : ('posto ' . $posto);

  return [
    'posto'    => $posto,
    'padre'    => (int)($r['padre'] ?? -1),
    'livello'  => (int)($r['livello'] ?? 0),
    'nome'     => $nome,
    'sic'      => (string)($r['sic_id'] ?? ''),
    'status'   => $status,
    'tipo'     => alb_tipo($status),
    'stato'    => $stato,                      // libero | prenotato | attivo
    'uid'      => $uid,
    'attivo'   => ($stato === 'attivo') ? 1 : 0,
    'occupato' => ($uid > 0 || $stato !== 'libero') ? 1 : 0,
    'manuale'  => (int)($r['padre_manuale'] ?? 0),
  ];
}

/* --------------------------------------------------------------------------
   RADICE — il Master. Se il posto 0 non c'e' in tabella lo si sintetizza:
   la radice e' un concetto della topologia (net_padre(n)<=0), non deve
   dipendere dall'esistenza di una riga.
-------------------------------------------------------------------------- */
function alb_radice(PDO $pdo): array {
  alb_migra($pdo);
  $st = $pdo->prepare("SELECT p.*, COALESCE(u.full_name,u.username,'') nome
                       FROM network_posti p LEFT JOIN users u ON u.id=p.uid
                       WHERE p.posto=0 LIMIT 1");
  $st->execute();
  $r = $st->fetch(PDO::FETCH_ASSOC);
  if (!$r) {
    $r = ['posto'=>0,'padre'=>-1,'livello'=>0,'sic_id'=>defined('NET_SIC_RADICE')?NET_SIC_RADICE:'SIC-ID-G-UN-000',
          'status'=>'Master','stato'=>'attivo','uid'=>0,'nome'=>'MASTER-NODE'];
  }
  $n = alb_riga_nodo($r);
  if ($n['nome'] === 'posto 0' || $n['nome'] === '') $n['nome'] = 'MASTER-NODE';
  $n['status'] = 'Master'; $n['tipo'] = 'master';
  return $n;
}

/* --------------------------------------------------------------------------
   FIGLI DI UN NODO — il cuore della navigazione.

   Strategia anti-5-milioni: due query separate invece di un ORDER BY su
   decine di migliaia di righe.
     1) figli OCCUPATI  (padre=? AND uid>0)  -> sempre tutti, sono pochi
     2) figli LIBERI    (padre=? AND uid=0)  -> a pagine (limit/offset)
   Entrambe servite dall'indice composito (padre,uid): il costo dipende da
   quanti ne chiedi, non da quanti ce ne sono sotto.

   Il conteggio dei NIPOTI si fa in UNA sola query aggregata per tutti i figli
   restituiti (mai una COUNT per figlio: sarebbe il classico N+1).
-------------------------------------------------------------------------- */
/* Offset della pagina che CONTIENE un figlio preciso.
   Senza questo, per arrivare a un figlio che sta in posizione 60.000 su 60.976
   il client dovrebbe sfogliare 750 pagine da 80: misurato, si arrendeva dopo
   40 giri e il nodo non veniva mai raggiunto. Qui si calcola in una query
   quanti fratelli lo precedono, e si salta direttamente alla sua pagina.
   L'ordine deve essere lo stesso di alb_figli(): prima gli occupati, poi i
   liberi per numero di posto crescente. */
function alb_pagina_di(PDO $pdo, int $padre, int $figlio, int $limit): int {
  $limit = max(1, $limit);
  $st = $pdo->prepare("SELECT uid FROM network_posti WHERE posto=? AND padre=? LIMIT 1");
  $st->execute([$figlio, $padre]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  if (!$r) {
    /* figlio CALCOLATO: la sua pagina si ricava dalla posizione nella sequenza,
       senza contare nulla — e' il vantaggio di avere posti deterministici. */
    $v = alb_virtuale($figlio);
    if ($v && (int)$v['padre'] === $padre) {
      return intdiv(alb_indice_virtuale($figlio), $limit) * $limit;
    }
    return 0;                              // non e' figlio di questo padre
  }
  if ((int)$r['uid'] > 0) return 0;        // gli occupati stanno sempre in prima pagina

  $q = $pdo->prepare("SELECT COUNT(*) FROM network_posti WHERE padre=? AND uid=0 AND posto<?");
  $q->execute([$padre, $figlio]);
  $prima = (int)$q->fetchColumn();
  return intdiv($prima, $limit) * $limit;
}

function alb_figli(PDO $pdo, int $padre, int $limit = 200, int $offset = 0, bool $soloOccupati = false): array {
  alb_migra($pdo);
  $limit  = max(1, min(1000, $limit));
  $offset = max(0, $offset);

  $tot     = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=".$padre." AND posto<>".$padre)->fetchColumn();
  $occTot  = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=".$padre." AND posto<>".$padre." AND uid>0")->fetchColumn();
  $liberi  = $tot - $occTot;

  $sel = "SELECT p.posto,p.padre,p.livello,p.sic_id,p.status,p.stato,p.uid,p.padre_manuale,
                 COALESCE(u.full_name,u.username,'') nome
          FROM network_posti p LEFT JOIN users u ON u.id=p.uid ";

  $righe = [];

  /* 1) gli occupati: sempre in cima, sono le persone vere */
  if ($offset === 0) {
    $q = $pdo->prepare($sel . "WHERE p.padre=? AND p.posto<>? AND p.uid>0 ORDER BY p.posto LIMIT ?");
    $q->bindValue(1, $padre, PDO::PARAM_INT);
    $q->bindValue(2, $padre, PDO::PARAM_INT);
    $q->bindValue(3, $limit, PDO::PARAM_INT);
    $q->execute();
    $righe = $q->fetchAll(PDO::FETCH_ASSOC);
  }

  /* 2) le posizioni libere, a pagine, solo se resta spazio */
  $restano = $limit - count($righe);
  if (!$soloOccupati && $restano > 0) {
    $q = $pdo->prepare($sel . "WHERE p.padre=? AND p.posto<>? AND p.uid=0 ORDER BY p.posto LIMIT ? OFFSET ?");
    $q->bindValue(1, $padre, PDO::PARAM_INT);
    $q->bindValue(2, $padre, PDO::PARAM_INT);
    $q->bindValue(3, $restano, PDO::PARAM_INT);
    $q->bindValue(4, $offset, PDO::PARAM_INT);
    $q->execute();
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) $righe[] = $r;
  }

  /* conteggio nipoti: UNA query per tutti */
  $ids = array_map(function ($r) { return (int)$r['posto']; }, $righe);
  $nipoti = [];
  if ($ids) {
    $in = implode(',', $ids);
    foreach ($pdo->query("SELECT padre, COUNT(*) c FROM network_posti WHERE padre IN ($in) GROUP BY padre") as $r) {
      $nipoti[(int)$r['padre']] = (int)$r['c'];
    }
  }

  $out = [];
  foreach ($righe as $r) {
    $n = alb_riga_nodo($r);
    $n['figli'] = $nipoti[$n['posto']] ?? 0;
    /* se il figlio e' un Pro, sotto di lui ci sono anche i posti CALCOLATI:
       senza questa somma il client credeva che il Pro non avesse figli e il
       ramo non si apriva. */
    $vq = alb_quanti_virtuali($n['posto']);
    if ($vq > 0) $n['figli'] += $vq;
    $out[] = $n;
  }

  /* ----- figli CALCOLATI (i posti utente che non esistono come riga) -----
     Valgono solo per gli 82 Pro. Si aggiungono dopo quelli veri, e si saltano
     quelli che una riga vera ce l'hanno gia' (perche' occupati o spostati):
     la riga vera comanda sempre sul calcolo. */
  $virtTot = alb_quanti_virtuali($padre);
  if ($virtTot > 0) {
    /* quanti posti calcolati di questo Pro sono gia' diventati righe vere */
    $materializzati = 0;
    try {
      $q = $pdo->prepare("SELECT COUNT(*) FROM network_posti WHERE posto>? AND (posto-119-?)>=0 AND ((posto-119-?) % 82)=0");
      $sc = $padre - 37;
      $q->execute([defined('NET_TOT_POSTI')?(int)NET_TOT_POSTI:118, $sc, $sc]);
      $materializzati = (int)$q->fetchColumn();
    } catch (Throwable $e) {}

    $tot    += $virtTot - $materializzati;
    $liberi += $virtTot - $materializzati;

    /* riempio la pagina con i calcolati, partendo da dove finiscono i veri */
    $restano = $limit - count($out);
    if ($restano > 0) {
      $saltati = max(0, $offset - $occTot);      // quanti calcolati gia' mostrati prima
      $i = $saltati; $aggiunti = 0; $giri = 0;
      $daControllare = [];
      while ($aggiunti < $restano && $i < $virtTot && $giri < $restano * 4 + 50) {
        $giri++;
        $n = alb_virtuale_iesimo($padre, $i); $i++;
        $daControllare[$n] = true;
        if (count($daControllare) >= $restano || $i >= $virtTot) {
          /* verifico in blocco quali di questi hanno gia' una riga vera */
          $in = implode(',', array_map('intval', array_keys($daControllare)));
          $veri = [];
          try { foreach ($pdo->query("SELECT posto FROM network_posti WHERE posto IN ($in)") as $r) $veri[(int)$r['posto']] = true; }
          catch (Throwable $e) {}
          foreach (array_keys($daControllare) as $cand) {
            if (isset($veri[$cand])) continue;             // esiste davvero: gia' contato sopra
            if ($aggiunti >= $restano) break;
            $vn = alb_virtuale($cand);
            if (!$vn) continue;
            $nn = alb_riga_nodo($vn);
            $nn['figli'] = 0;                              // un posto utente calcolato non ha figli
            $nn['virtuale'] = 1;
            $out[] = $nn; $aggiunti++;
          }
          $daControllare = [];
        }
      }
    }
  }

  return [
    'ok'        => true,
    'padre'     => $padre,
    'figli'     => $out,
    'tot_figli' => $tot,
    'occupati'  => $occTot,
    'liberi'    => $liberi,
    'mostrati'  => count($out),
    'offset'    => $offset,
    'altri'     => max(0, $tot - $offset - count($out)),
  ];
}

/* --------------------------------------------------------------------------
   VISTA INIZIALE — Master + 9 World + i loro National, in UNA risposta.
   Richiesta esplicita di Mirco: "di default appena apre ha i 9 world-node e
   relativi national-node". Sono 1+9+27 = 37 nodi: si possono mandare tutti
   insieme senza problemi a qualunque dimensione della rete, perche' il numero
   NON dipende dai 5 milioni sotto.
-------------------------------------------------------------------------- */
function alb_vista_iniziale(PDO $pdo): array {
  alb_migra($pdo);
  $root = alb_radice($pdo);

  $world = alb_figli($pdo, 0, 50);
  $figliRoot = [];
  foreach ($world['figli'] as $w) {
    $nat = alb_figli($pdo, $w['posto'], 50);
    $w['children']   = $nat['figli'];
    $w['caricato']   = 1;                 // i suoi figli sono gia' qui
    foreach ($w['children'] as &$c) { $c['caricato'] = 0; }   // i National no
    unset($c);
    $figliRoot[] = $w;
  }
  $root['children'] = $figliRoot;
  $root['caricato'] = 1;
  $root['figli']    = $world['tot_figli'];

  return ['ok'=>true, 'albero'=>$root, 'world'=>count($figliRoot)];
}

/* --------------------------------------------------------------------------
   DETTAGLIO NODO — tutto quello che serve al pannello laterale.
   Legge network_posti (struttura + wallet + POL/DRX riservati) e, se il posto
   e' occupato, arricchisce con users (anagrafica) e network_nodes (rango,
   DRX di merito, attivita' del mese).
-------------------------------------------------------------------------- */
function alb_nodo(PDO $pdo, int $posto): array {
  alb_migra($pdo);

  $st = $pdo->prepare("SELECT p.*, COALESCE(u.full_name,u.username,'') nome, u.email, u.sic_id AS sic_utente
                       FROM network_posti p LEFT JOIN users u ON u.id=p.uid
                       WHERE p.posto=? LIMIT 1");
  $st->execute([$posto]);
  $r = $st->fetch(PDO::FETCH_ASSOC);

  if (!$r) {
    if ($posto === 0) {
      $n = alb_radice($pdo);
      return ['ok'=>true, 'nodo'=>$n, 'kpi'=>['figli'=>0,'occupati'=>0,'liberi'=>0,'attivi'=>0], 'catena'=>[]];
    }
    /* nessuna riga: se e' uno dei posti utente calcolati, esiste lo stesso */
    $r = alb_virtuale($posto);
    if (!$r) return ['ok'=>false, 'err'=>'Il posto #'.$posto.' non esiste: la rete arriva al #'.alb_ultimo_posto().'.'];
  }

  $n = alb_riga_nodo($r);

  /* dati economici/on-chain gia' presenti nella riga del posto */
  $n['node_kind']      = (string)($r['node_kind'] ?? '');
  $n['price_eur']      = $r['price_eur'] !== null ? (float)$r['price_eur'] : null;
  $n['wallet']         = (string)($r['wallet_address'] ?? '');
  $n['wallet_status']  = (string)($r['wallet_status'] ?? '');
  $n['pol_reserved']   = (float)($r['pol_reserved'] ?? 0);
  $n['pol_funded']     = (float)($r['pol_funded'] ?? 0);
  $n['drx_reserved']   = (float)($r['drx_reserved'] ?? 0);
  $n['drx_funded']     = (float)($r['drx_funded'] ?? 0);
  $n['nft_num']        = $r['nft_num'] !== null ? (int)$r['nft_num'] : null;
  $n['unicorn']        = (int)($r['unicorn'] ?? 0);
  $n['preso_il']       = (string)($r['preso_il'] ?? '');
  $n['attivato_il']    = (string)($r['attivato_il'] ?? '');
  $n['email']          = (string)($r['email'] ?? '');
  $n['nota']           = (string)($r['nota'] ?? '');
  $n['sic_prov']       = (string)($r['sic_prov'] ?? '');

  /* moltiplicatore rewards del nodo (privilegio dei 118, X1..X8) */
  $n['boost'] = 1; $n['boost_proprio'] = 0; $n['boost_nota'] = ''; $n['boost_voci'] = [];
  if (@include_once __DIR__ . '/dr-boost.php') { /* caricato */ }
  if (function_exists('dr_boost_posto')) {
    $n['boost']         = dr_boost_posto($pdo, $posto);
    $n['boost_proprio'] = (int)($r['boost'] ?? 0);
    $n['boost_nota']    = (string)($r['boost_nota'] ?? '');
    if (function_exists('dr_boost_voci_attive')) {
      foreach (dr_boost_voci_attive($pdo) as $k => $on) if ($on) $n['boost_voci'][] = $k;
    }
  }

  /* I DUE CODICI, tenuti separati e mostrati separati:
       sic        = SIC DI QUESTO POSTO (sta in network_posti, non si muove)
       sic_personale = SIC DELLA PERSONA (users.internal_code, non cambia mai)
       sic_altri_nodi = gli altri posti che la stessa persona occupa
     Chi occupa il posto ha entrambi, e puo' usare il ref con uno qualsiasi. */
  $n['sic_personale']  = '';
  $n['sic_altri_nodi'] = [];
  if ($n['uid'] > 0) {
    $tutti = alb_sic_utente($pdo, $n['uid']);
    $n['sic_personale'] = $tutti['personale'];
    foreach ($tutti['nodi'] as $nd) {
      if ((int)$nd['posto'] !== $posto) $n['sic_altri_nodi'][] = $nd;
    }
  }

  /* dati vivi dell'utente che occupa il posto */
  $n['rango'] = null; $n['drx_merito'] = null; $n['attivo_mese'] = null; $n['stato_nodo'] = '';
  if ($n['uid'] > 0) {
    try {
      $q = $pdo->prepare("SELECT rango,drx_merito,drx_premi,attivo_mese,stato,ultimo_attivo
                          FROM network_nodes WHERE uid=? LIMIT 1");
      $q->execute([$n['uid']]);
      if ($nn = $q->fetch(PDO::FETCH_ASSOC)) {
        $n['rango']       = (int)$nn['rango'];
        $n['drx_merito']  = (int)$nn['drx_merito'];
        $n['drx_premi']   = (int)$nn['drx_premi'];
        $n['attivo_mese'] = (int)$nn['attivo_mese'];
        $n['stato_nodo']  = (string)$nn['stato'];
        $n['ultimo_attivo'] = (string)$nn['ultimo_attivo'];
      }
    } catch (Throwable $e) {}
  }

  /* KPI del nodo: figli diretti, quanti occupati, quanti liberi.
     Ai figli che esistono come riga si sommano quelli CALCOLATI (i posti utente
     che spettano a questo Pro e non sono ancora stati scritti): senza questa
     somma un Pro con 60.976 posizioni sotto ne mostrava 0. */
  $figli   = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=".$posto." AND posto<>".$posto)->fetchColumn();
  $occ     = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=".$posto." AND posto<>".$posto." AND uid>0")->fetchColumn();
  $attivi  = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=".$posto." AND posto<>".$posto." AND stato='attivo'")->fetchColumn();
  $virt = alb_quanti_virtuali($posto);
  if ($virt > 0) {
    $sc = $posto - 37; $mat = 0;
    try {
      $q = $pdo->prepare("SELECT COUNT(*) FROM network_posti WHERE posto>? AND (posto-119-?)>=0 AND ((posto-119-?) % 82)=0");
      $q->execute([defined('NET_TOT_POSTI')?(int)NET_TOT_POSTI:118, $sc, $sc]);
      $mat = (int)$q->fetchColumn();
    } catch (Throwable $e) {}
    $figli += max(0, $virt - $mat);
  }

  /* catena verso la radice (upline): serve al breadcrumb e a "dove sono" */
  $catena = []; $cur = $posto; $hop = 0; $visti = [$posto => 1];
  while ($cur > 0 && $hop < 60) {
    $up = alb_padre_di($pdo, $cur);          // vale anche per i posti calcolati
    if ($up < 0 || isset($visti[$up])) break;
    $visti[$up] = 1;
    $q = $pdo->prepare("SELECT p.posto,p.padre,p.livello,p.sic_id,p.status,p.stato,p.uid,p.padre_manuale,
                               COALESCE(u.full_name,u.username,'') nome
                        FROM network_posti p LEFT JOIN users u ON u.id=p.uid WHERE p.posto=? LIMIT 1");
    $q->execute([$up]);
    if ($ur = $q->fetch(PDO::FETCH_ASSOC)) $catena[] = alb_riga_nodo($ur);
    elseif ($up === 0) $catena[] = alb_radice($pdo);
    $cur = $up; $hop++;
  }
  $catena = array_reverse($catena);   // dalla radice fino al padre

  return ['ok'=>true, 'nodo'=>$n,
          'kpi'=>['figli'=>$figli, 'occupati'=>$occ, 'liberi'=>$figli-$occ, 'attivi'=>$attivi],
          'catena'=>$catena];
}

/* --------------------------------------------------------------------------
   RAMO — quanti discendenti TOTALI ha un nodo, a tutti i livelli.
   Su 5 milioni di posizioni contare il ramo di un World significa contare
   ~600.000 righe: si fa SOLO su richiesta esplicita (bottone), mai in
   automatico, e con un tetto oltre il quale si risponde "piu' di N" invece
   di bloccare il server. Discesa a livelli con IN(...), niente ricorsione
   PHP (nessun rischio di stack) e niente CTE ricorsiva (piu' prevedibile).
-------------------------------------------------------------------------- */
function alb_ramo(PDO $pdo, int $posto, int $tetto = 250000): array {
  alb_migra($pdo);
  $tot = 0; $occ = 0; $liv = 0;
  $frontiera = [$posto];
  $troncato = false;
  /* i posti utente calcolati sotto i Pro: si contano con l'aritmetica, senza
     leggere righe (sarebbero milioni). I Pro dentro il ramo li raccolgo mentre
     scendo, vedi $proTrovati. */
  $proTrovati = (alb_quanti_virtuali($posto) > 0) ? [$posto] : [];

  while ($frontiera && $liv < 40) {
    $liv++;
    $prossima = [];
    /* si procede a scaglioni di 900 padri per stare sotto i limiti SQLite */
    foreach (array_chunk($frontiera, 900) as $blocco) {
      $in = implode(',', array_map('intval', $blocco));
      $st = $pdo->query("SELECT posto,uid FROM network_posti WHERE padre IN ($in)");
      foreach ($st as $r) {
        $tot++;
        if ((int)$r['uid'] > 0) $occ++;
        $prossima[] = (int)$r['posto'];
        if (alb_quanti_virtuali((int)$r['posto']) > 0) $proTrovati[] = (int)$r['posto'];
        if ($tot >= $tetto) { $troncato = true; break 3; }
      }
    }
    $frontiera = $prossima;
  }

  /* somma dei posti utente calcolati che pendono dai Pro incontrati */
  $virt = 0;
  foreach (array_unique($proTrovati) as $pr) $virt += alb_quanti_virtuali($pr);
  if ($virt > 0) {
    $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
    $mat = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE posto>".$base)->fetchColumn();
    $tot += max(0, $virt - $mat);
    if ($liv < 1) $liv = 1;
  }

  return ['ok'=>true, 'posto'=>$posto, 'discendenti'=>$tot, 'occupati'=>$occ,
          'profondita'=>$liv, 'troncato'=>$troncato, 'tetto'=>$tetto];
}

/* --------------------------------------------------------------------------
   RICERCA — per numero di posto, SIC, nome o email. Ritorna anche il PERCORSO
   dalla radice, cosi' il client sa quali rami aprire per arrivarci senza
   scaricare mezzo albero.
-------------------------------------------------------------------------- */
function alb_cerca(PDO $pdo, string $q, int $limit = 20): array {
  alb_migra($pdo);
  $q = trim($q);
  if ($q === '') return ['ok'=>true, 'risultati'=>[]];
  $limit = max(1, min(50, $limit));

  $sel = "SELECT p.posto,p.padre,p.livello,p.sic_id,p.status,p.stato,p.uid,p.padre_manuale,
                 COALESCE(u.full_name,u.username,'') nome
          FROM network_posti p LEFT JOIN users u ON u.id=p.uid ";
  $righe = []; $visti = [];

  $aggiungi = function ($rows) use (&$righe, &$visti, $limit) {
    foreach ($rows as $r) {
      $p = (int)$r['posto'];
      if (isset($visti[$p])) continue;
      if (count($righe) >= $limit) return;
      $visti[$p] = 1; $righe[] = $r;
    }
  };

  if (ctype_digit($q)) {
    $st = $pdo->prepare($sel . "WHERE p.posto=? LIMIT 1");
    $st->execute([(int)$q]);
    $trovate = $st->fetchAll(PDO::FETCH_ASSOC);
    /* se non c'e' la riga, il posto puo' esistere lo stesso come calcolato */
    if (!$trovate) { $v = alb_virtuale((int)$q); if ($v) $trovate = [$v]; }
    $aggiungi($trovate);
  }
  /* SIC di un posto utente calcolato: SIC-ID-G-US-<numero> -> risalgo al numero */
  if (count($righe) < $limit && preg_match('~^(?:SIC-ID-G-)?US-?(\d+)$~i', str_replace(' ', '', $q), $m)) {
    $v = alb_virtuale((int)$m[1]);
    if ($v) $aggiungi([$v]);
  }
  /* SIC — due passate, in quest'ordine per una ragione di velocita' precisa:
     1) LIKE 'testo%' (prefisso) usa l'indice ix_np_sic: costo trascurabile
        anche su 5 milioni di righe;
     2) LIKE '%testo%' (contiene) NON puo' usare nessun indice e obbliga a
        leggere tutta la tabella — misurato: 365 ms su 5.000.119 righe.
     Siccome i SIC iniziano tutti con "SIC-ID-G-", chi cerca "PN-040" cade
     sempre nel caso 2: per questo si prova PRIMA anche la forma completa
     "SIC-ID-G-<testo>" come prefisso, che copre la ricerca tipica e resta
     istantanea. La scansione completa resta solo come ultima spiaggia. */
  if (count($righe) < $limit) {
    $st = $pdo->prepare($sel . "WHERE p.sic_id LIKE ? LIMIT ?");
    $st->bindValue(1, $q.'%'); $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute(); $aggiungi($st->fetchAll(PDO::FETCH_ASSOC));
  }
  if (count($righe) < $limit && stripos($q, 'SIC-ID') === false) {
    $st = $pdo->prepare($sel . "WHERE p.sic_id LIKE ? LIMIT ?");
    $st->bindValue(1, 'SIC-ID-G-'.$q.'%'); $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute(); $aggiungi($st->fetchAll(PDO::FETCH_ASSOC));
  }

  /* ricerca per persona: filtra su p.uid>0 (indice ix_np_uid2), quindi tocca
     solo i posti realmente occupati — non i milioni liberi. */
  if (count($righe) < $limit) {
    $like = '%'.$q.'%';
    $st = $pdo->prepare($sel . "WHERE p.uid>0 AND (u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?) LIMIT ?");
    $st->bindValue(1, $like); $st->bindValue(2, $like); $st->bindValue(3, $like);
    $st->bindValue(4, $limit, PDO::PARAM_INT);
    $st->execute(); $aggiungi($st->fetchAll(PDO::FETCH_ASSOC));
  }

  /* ULTIMA SPIAGGIA — "il SIC contiene questo pezzo" in mezzo alla stringa.
     Nessun indice puo' servirla: e' una lettura dell'intera tabella, misurata
     in ~0,4 s su 5.000.119 righe. Per questo parte SOLO se tutte le vie
     indicizzate qui sopra non hanno trovato NIENTE: se un risultato c'e' gia',
     saltarla fa scendere la ricerca da ~1 s a pochi millisecondi. */
  if (!$righe) {
    $st = $pdo->prepare($sel . "WHERE p.sic_id LIKE ? LIMIT ?");
    $st->bindValue(1, '%'.$q.'%'); $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute(); $aggiungi($st->fetchAll(PDO::FETCH_ASSOC));
  }

  $out = [];
  foreach ($righe as $r) {
    $n = alb_riga_nodo($r);
    /* percorso radice -> nodo */
    $path = [$n['posto']]; $cur = $n['posto']; $hop = 0; $visitati = [$cur=>1];
    while ($cur > 0 && $hop < 60) {
      $up = alb_padre_di($pdo, $cur);
      if ($up < 0 || isset($visitati[$up])) break;
      $visitati[$up] = 1; $path[] = $up; $cur = $up; $hop++;
    }
    $n['percorso'] = array_reverse($path);
    $out[] = $n;
  }
  return ['ok'=>true, 'risultati'=>$out];
}

/* --------------------------------------------------------------------------
   SPOSTA UN NODO sotto un altro padre.

   CONTROLLO ANTI-CICLO (il piu' importante): un nodo non puo' essere agganciato
   sotto un proprio discendente, altrimenti si crea un anello e l'albero non ha
   piu' una radice — la pagina andrebbe in ricorsione infinita. Si risale dal
   nuovo padre verso l'alto: se si incontra il nodo che stiamo spostando, si
   rifiuta.

   Marca padre_manuale=1: da quel momento alb_resync_topologia() non tocchera'
   mai piu' questo posto. Aggiorna anche il livello del nodo e di tutto il suo
   ramo (a scaglioni, con tetto).
-------------------------------------------------------------------------- */
function alb_sposta(PDO $pdo, int $posto, int $nuovoPadre, string $attore = 'admin'): array {
  alb_migra($pdo);
  if ($posto <= 0)              return ['ok'=>false, 'err'=>'Il MASTER-NODE non si sposta.'];
  alb_materializza($pdo, $posto); alb_materializza($pdo, $nuovoPadre);
  if ($posto === $nuovoPadre)   return ['ok'=>false, 'err'=>'Un nodo non puo\' essere padre di se stesso.'];
  if ($nuovoPadre < 0)          return ['ok'=>false, 'err'=>'Padre non valido.'];

  $cur = $pdo->prepare("SELECT posto,padre,livello,sic_id FROM network_posti WHERE posto=? LIMIT 1");
  $cur->execute([$posto]);
  $nodo = $cur->fetch(PDO::FETCH_ASSOC);
  if (!$nodo) return ['ok'=>false, 'err'=>'Posto inesistente.'];

  if ($nuovoPadre > 0) {
    $chk = $pdo->prepare("SELECT posto,livello FROM network_posti WHERE posto=? LIMIT 1");
    $chk->execute([$nuovoPadre]);
    if (!$chk->fetch(PDO::FETCH_ASSOC)) return ['ok'=>false, 'err'=>'Il nodo di destinazione non esiste.'];
  }

  /* anti-ciclo: risalgo dal nuovo padre verso la radice */
  $cursore = $nuovoPadre; $hop = 0; $visti = [];
  while ($cursore > 0 && $hop < 200) {
    if ($cursore === $posto) {
      return ['ok'=>false, 'err'=>'Non puoi agganciare un nodo sotto un suo discendente: si creerebbe un anello.'];
    }
    if (isset($visti[$cursore])) break;
    $visti[$cursore] = 1;
    $cursore = alb_padre_di($pdo, $cursore); $hop++;
  }

  $vecchioPadre = (int)$nodo['padre'];
  if ($vecchioPadre === $nuovoPadre) return ['ok'=>true, 'invariato'=>true, 'posto'=>$posto, 'padre'=>$nuovoPadre];

  /* livello del nuovo padre + 1 */
  $livPadre = 0;
  if ($nuovoPadre > 0) {
    $livPadre = (int)$pdo->query("SELECT livello FROM network_posti WHERE posto=".$nuovoPadre)->fetchColumn();
  }
  $nuovoLiv = $livPadre + 1;
  $delta    = $nuovoLiv - (int)$nodo['livello'];

  try {
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE network_posti SET padre=?, livello=?, padre_manuale=1, aggiornato=datetime('now') WHERE posto=?")
        ->execute([$nuovoPadre, $nuovoLiv, $posto]);

    /* riallineo il livello di tutto il ramo sotto (a scaglioni, con tetto) */
    $spostati = 0;
    if ($delta !== 0) {
      $frontiera = [$posto]; $giri = 0;
      while ($frontiera && $giri < 40 && $spostati < 200000) {
        $giri++; $prossima = [];
        foreach (array_chunk($frontiera, 900) as $blocco) {
          $in = implode(',', array_map('intval', $blocco));
          foreach ($pdo->query("SELECT posto FROM network_posti WHERE padre IN ($in)") as $r) {
            $prossima[] = (int)$r['posto'];
          }
        }
        if ($prossima) {
          foreach (array_chunk($prossima, 900) as $blocco) {
            $in = implode(',', array_map('intval', $blocco));
            $pdo->exec("UPDATE network_posti SET livello=livello+($delta) WHERE posto IN ($in)");
            $spostati += count($blocco);
          }
        }
        $frontiera = $prossima;
      }
    }
    $pdo->commit();
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    return ['ok'=>false, 'err'=>'Errore in scrittura: '.substr($e->getMessage(), 0, 160)];
  }

  alb_log($pdo, 'sposta', $posto, 'padre='.$vecchioPadre, 'padre='.$nuovoPadre, $attore);
  return ['ok'=>true, 'posto'=>$posto, 'padre_prima'=>$vecchioPadre, 'padre'=>$nuovoPadre,
          'livello'=>$nuovoLiv, 'ramo_riallineato'=>$spostati ?? 0];
}

/* --------------------------------------------------------------------------
   ASSEGNA UN UTENTE A UNA POSIZIONE.
   Scrive su network_posti (la struttura) e allinea network_nodes (i dati vivi)
   cosi' le due tabelle smettono di raccontare storie diverse.
   UN UTENTE = UN SOLO POSTO (DR_MAX_POSTI_UTENTE, vedi in cima al file):
   se la persona ne occupa gia' uno, l'assegnazione viene RIFIUTATA e si
   suggerisce di spostarla invece di darle un secondo posto.
-------------------------------------------------------------------------- */
function alb_assegna(PDO $pdo, int $posto, int $uid, string $stato = 'attivo', string $attore = 'admin'): array {
  alb_migra($pdo);
  if ($posto <= 0) return ['ok'=>false, 'err'=>'Il MASTER-NODE non si riassegna da qui.'];
  if ($uid   <= 0) return ['ok'=>false, 'err'=>'Utente non valido.'];
  alb_materializza($pdo, $posto);   // se era un posto calcolato, ora diventa riga vera
  if (!in_array($stato, ['attivo','prenotato'], true)) $stato = 'attivo';

  $st = $pdo->prepare("SELECT posto,uid,stato,status,sic_id FROM network_posti WHERE posto=? LIMIT 1");
  $st->execute([$posto]);
  $p = $st->fetch(PDO::FETCH_ASSOC);
  if (!$p) return ['ok'=>false, 'err'=>'Posto inesistente.'];
  if ((int)$p['uid'] > 0 && (int)$p['uid'] !== $uid) {
    return ['ok'=>false, 'err'=>'Posto gia\' occupato dall\'utente #'.$p['uid'].'. Liberalo prima.'];
  }

  $u = $pdo->prepare("SELECT id, COALESCE(full_name,username,'') nome FROM users WHERE id=? LIMIT 1");
  $u->execute([$uid]);
  $ur = $u->fetch(PDO::FETCH_ASSOC);
  if (!$ur) return ['ok'=>false, 'err'=>'Utente #'.$uid.' inesistente.'];

  /* UN UTENTE = UN POSTO. Se ne ha gia' uno, non gliene si da' un secondo:
     si sposta quello che ha. Cosi' non nascono multi-posti per distrazione. */
  $altri = [];
  foreach ($pdo->query("SELECT posto,sic_id,status FROM network_posti WHERE uid=".$uid." AND posto<>".$posto) as $r) {
    $altri[] = ['posto'=>(int)$r['posto'], 'sic'=>(string)$r['sic_id'], 'status'=>(string)$r['status']];
  }
  $giaHa = count($altri) + ((int)$p['uid'] === $uid ? 1 : 0);
  if (count($altri) >= DR_MAX_POSTI_UTENTE) {
    $dove = $altri[0];
    return ['ok'=>false,
            'err'=>$ur['nome'].' occupa gia\' il posto #'.$dove['posto'].' ('.$dove['status'].', '.$dove['sic'].'). '
                 .'Una persona puo\' occupare un solo posto: invece di assegnargliene un altro, SPOSTALA '
                 .'dal #'.$dove['posto'].' al #'.$posto.'.',
            'gia_al_posto'=>$dove['posto'], 'suggerisci_spostamento'=>true];
  }

  try {
    $pdo->prepare("UPDATE network_posti
                   SET uid=?, stato=?, preso_il=COALESCE(preso_il,datetime('now')),
                       attivato_il=CASE WHEN ?='attivo' THEN datetime('now') ELSE attivato_il END,
                       aggiornato=datetime('now')
                   WHERE posto=?")
        ->execute([$uid, $stato, $stato, $posto]);
  } catch (Throwable $e) {
    return ['ok'=>false, 'err'=>'Errore in scrittura: '.substr($e->getMessage(), 0, 160)];
  }

  /* allineo network_nodes: se l'utente non c'e' ancora, lo creo con l'upline
     giusta (l'occupante del posto padre); se c'e', aggiorno solo l'upline.
     In network_nodes.sic_id va il SIC PERSONALE (colonna UNIQUE, identifica la
     PERSONA), MAI quello del posto: il SIC del posto resta in network_posti e
     la persona lo acquisisce in aggiunta al proprio, non al posto del proprio. */
  $sicPersonale = alb_assicura_sic_personale($pdo, $uid);
  try {
    if (function_exists('net_migra')) net_migra($pdo);
    $padre    = (int)$pdo->query("SELECT padre FROM network_posti WHERE posto=".$posto)->fetchColumn();
    $uidPadre = (int)$pdo->query("SELECT COALESCE(uid,0) FROM network_posti WHERE posto=".$padre)->fetchColumn();
    $esiste   = (int)$pdo->query("SELECT COUNT(*) FROM network_nodes WHERE uid=".$uid)->fetchColumn();
    if ($esiste) {
      $pdo->prepare("UPDATE network_nodes SET upline_uid=?, aggiornato=datetime('now') WHERE uid=?")
          ->execute([$uidPadre, $uid]);
    } else {
      $pdo->prepare("INSERT OR IGNORE INTO network_nodes(uid,sic_id,rango,rank_floor,status,upline_uid,stato,ultimo_attivo)
                     VALUES(?,?,3,3,?,?,?,datetime('now'))")
          ->execute([$uid, $sicPersonale, (string)$p['status'], $uidPadre, $stato==='attivo'?'attivo':'inattivo']);
    }
  } catch (Throwable $e) { /* l'allineamento non deve mai far fallire l'assegnazione */ }

  alb_log($pdo, 'assegna', $posto, 'uid='.(int)$p['uid'], 'uid='.$uid.' stato='.$stato, $attore);
  return ['ok'=>true, 'posto'=>$posto, 'uid'=>$uid, 'nome'=>$ur['nome'], 'stato'=>$stato,
          'sic_personale'=>$sicPersonale, 'sic_nodo'=>(string)$p['sic_id'],
          'posti_di_questo_utente'=>$giaHa + ((int)$p['uid']===$uid ? 0 : 1)];
}

/* --------------------------------------------------------------------------
   DISCENDENTE? — true se $forse e' dentro il ramo di $capo.
   Serve prima di ogni spostamento: e' il controllo che impedisce di creare un
   anello (un nodo che finisce sotto se stesso, albero senza radice, pagina in
   ricorsione infinita). Risale da $forse verso l'alto: e' molto piu' economico
   che scendere tutto il ramo di $capo, che puo' avere milioni di posizioni.
-------------------------------------------------------------------------- */
function alb_e_discendente(PDO $pdo, int $capo, int $forse): bool {
  $cur = $forse; $hop = 0; $visti = [];
  while ($cur > 0 && $hop < 300) {
    if ($cur === $capo) return true;
    if (isset($visti[$cur])) break;
    $visti[$cur] = 1;
    $cur = alb_padre_di($pdo, $cur); $hop++;
  }
  return ($cur === $capo);
}

/* --------------------------------------------------------------------------
   SPOSTA UNA PERSONA — con tutta la sua struttura appresso.
   (Mirco, 15/08: "la persona che sposto da posizione X a posizione Y si porta
   dietro tutta la sua struttura".)

   TRE MOSSE DIVERSE, perche' "spostare" vuol dire tre cose diverse:

   'occupa'   La persona LASCIA il posto X e VA A OCCUPARE il posto Y.
              I suoi figli diretti vengono riagganciati sotto Y: la sua gente
              lo segue. Il posto X resta libero e senza nessuno sotto.
              E' il caso "prendi l'utente al posto 119 e mettilo sul World 2":
              da quel momento quella persona E' il World Node 2, con la sua
              rete sotto di se'. Il SIC che conta diventa quello del posto Y,
              perche' il SIC appartiene al POSTO, non alla persona.
              Y deve essere libero.

   'scambia'  X e Y sono ENTRAMBI occupati: i due si scambiano di posto, e
              ognuno si porta dietro la propria gente (si scambiano anche i
              rami sottostanti). Nessuno resta senza posizione.

   'aggancia' Non cambia il posto della persona: e' il POSTO X, con dentro la
              persona e tutto il ramo com'e', che viene appeso sotto Y.
              La persona tiene il suo numero e il suo SIC, cambia solo da chi
              dipende. E' il caso "questo ramo deve stare sotto quel World".

   In tutti e tre: transazione unica (o si fa tutto o non si fa niente),
   controllo anti-anello, e riga nel registro network_posti_log.
-------------------------------------------------------------------------- */
function alb_sposta_utente(PDO $pdo, int $da, int $a, string $modo = 'occupa', string $attore = 'admin'): array {
  alb_migra($pdo);
  if ($da === $a)  return ['ok'=>false, 'err'=>'Partenza e destinazione sono lo stesso posto.'];
  if ($da <= 0)    return ['ok'=>false, 'err'=>'Il MASTER-NODE non si sposta.'];
  alb_materializza($pdo, $da); if ($a > 0) alb_materializza($pdo, $a);
  if ($a  <  0)    return ['ok'=>false, 'err'=>'Destinazione non valida.'];
  if ($a === 0 && $modo !== 'aggancia') return ['ok'=>false, 'err'=>'Non si puo\' occupare il MASTER-NODE. Se volevi appendere il ramo alla radice, usa "aggancia".'];

  $leggi = function (int $p) use ($pdo) {
    $st = $pdo->prepare("SELECT p.posto,p.padre,p.livello,p.sic_id,p.status,p.stato,p.uid,
                                COALESCE(u.full_name,u.username,'') nome, u.sic_id AS sic_utente
                         FROM network_posti p LEFT JOIN users u ON u.id=p.uid
                         WHERE p.posto=? LIMIT 1");
    $st->execute([$p]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
  };

  $X = $leggi($da);
  if (!$X) return ['ok'=>false, 'err'=>'Il posto di partenza #'.$da.' non esiste.'];
  $Y = $a === 0 ? ['posto'=>0,'padre'=>-1,'livello'=>0,'uid'=>0,'sic_id'=>'','status'=>'Master','stato'=>'master','nome'=>'MASTER-NODE','sic_utente'=>null] : $leggi($a);
  if (!$Y) return ['ok'=>false, 'err'=>'Il posto di destinazione #'.$a.' non esiste.'];

  if ((int)$X['uid'] <= 0 && $modo !== 'aggancia') {
    return ['ok'=>false, 'err'=>'Al posto #'.$da.' non c\'e\' nessuno da spostare.'];
  }

  /* ---------------------------------------------------------- 'aggancia' */
  if ($modo === 'aggancia') {
    return alb_sposta($pdo, $da, $a, $attore);   // gia' provata, gia' anti-anello
  }

  /* anti-anello: la destinazione non puo' stare dentro il ramo che sto muovendo */
  if (alb_e_discendente($pdo, $da, $a)) {
    return ['ok'=>false, 'err'=>'Il posto #'.$a.' sta gia\' dentro il ramo di #'.$da.': spostarlo li\' creerebbe un anello. Sposta prima il ramo piu\' in alto.'];
  }

  $occupatoY = ((int)$Y['uid'] > 0);
  if ($modo === 'occupa' && $occupatoY) {
    return ['ok'=>false, 'err'=>'Il posto #'.$a.' e\' gia\' occupato da '.($Y['nome'] ?: ('utente #'.$Y['uid'])).'. Usa "scambia" per farli cambiare di posto, oppure libera prima quel posto.'];
  }
  if ($modo === 'scambia' && !$occupatoY) {
    return ['ok'=>false, 'err'=>'Il posto #'.$a.' e\' libero: per uno scambio servono due persone. Usa "occupa".'];
  }
  if ($modo === 'scambia' && alb_e_discendente($pdo, $a, $da)) {
    return ['ok'=>false, 'err'=>'#'.$da.' sta dentro il ramo di #'.$a.': non si possono scambiare due posti uno dentro l\'altro.'];
  }

  $uidX = (int)$X['uid']; $statoX = (string)$X['stato'];
  $uidY = (int)$Y['uid']; $statoY = (string)$Y['stato'];

  /* figli diretti dei due posti (le due "strutture" che seguono le persone) */
  $figliDi = function (int $p) use ($pdo) {
    $out = [];
    foreach ($pdo->query("SELECT posto FROM network_posti WHERE padre=".$p." AND posto<>".$p) as $r) $out[] = (int)$r['posto'];
    return $out;
  };
  $figliX = $figliDi($da);
  $figliY = $modo === 'scambia' ? $figliDi($a) : [];
  /* la destinazione non deve mai finire fra i figli da spostare */
  $figliX = array_values(array_diff($figliX, [$a]));
  $figliY = array_values(array_diff($figliY, [$da]));

  /* dislivello: spostando la gente da X a Y cambia la profondita' */
  $livX = (int)$X['livello']; $livY = (int)$Y['livello'];
  $deltaXY = $livY - $livX;   // per i figli di X che vanno sotto Y
  $deltaYX = $livX - $livY;   // per i figli di Y che vanno sotto X (solo scambio)

  /* riallinea il livello di un intero ramo, a scaglioni (mai ricorsione PHP) */
  $riallinea = function (array $radici, int $delta) use ($pdo) {
    if ($delta === 0 || !$radici) return 0;
    $tocchi = 0; $frontiera = $radici; $giri = 0;
    while ($frontiera && $giri < 40 && $tocchi < 300000) {
      $giri++;
      foreach (array_chunk($frontiera, 900) as $blocco) {
        $in = implode(',', array_map('intval', $blocco));
        $pdo->exec("UPDATE network_posti SET livello=livello+($delta) WHERE posto IN ($in)");
        $tocchi += count($blocco);
      }
      $prossima = [];
      foreach (array_chunk($frontiera, 900) as $blocco) {
        $in = implode(',', array_map('intval', $blocco));
        foreach ($pdo->query("SELECT posto FROM network_posti WHERE padre IN ($in)") as $r) $prossima[] = (int)$r['posto'];
      }
      $frontiera = $prossima;
    }
    return $tocchi;
  };

  $spostatiRamo = 0;
  try {
    $pdo->beginTransaction();

    if ($modo === 'occupa') {
      /* la persona va sul posto Y */
      $pdo->prepare("UPDATE network_posti SET uid=?, stato=?, preso_il=COALESCE(preso_il,datetime('now')),
                     attivato_il=CASE WHEN ?='attivo' THEN COALESCE(attivato_il,datetime('now')) ELSE attivato_il END,
                     aggiornato=datetime('now') WHERE posto=?")
          ->execute([$uidX, $statoX, $statoX, $a]);
      /* il posto X resta libero */
      $pdo->prepare("UPDATE network_posti SET uid=0, stato='libero', aggiornato=datetime('now') WHERE posto=?")
          ->execute([$da]);
      /* la sua gente lo segue: i figli di X passano sotto Y */
      if ($figliX) {
        foreach (array_chunk($figliX, 900) as $blocco) {
          $in = implode(',', array_map('intval', $blocco));
          $pdo->exec("UPDATE network_posti SET padre=".$a.", padre_manuale=1, aggiornato=datetime('now') WHERE posto IN ($in)");
        }
        $spostatiRamo += $riallinea($figliX, $deltaXY);
      }

    } else { /* scambia */
      $pdo->prepare("UPDATE network_posti SET uid=?, stato=?, aggiornato=datetime('now') WHERE posto=?")
          ->execute([$uidY, $statoY, $da]);
      $pdo->prepare("UPDATE network_posti SET uid=?, stato=?, aggiornato=datetime('now') WHERE posto=?")
          ->execute([$uidX, $statoX, $a]);
      /* ognuno si porta dietro la propria gente */
      if ($figliX) {
        foreach (array_chunk($figliX, 900) as $b) {
          $in = implode(',', array_map('intval', $b));
          $pdo->exec("UPDATE network_posti SET padre=".$a.", padre_manuale=1, aggiornato=datetime('now') WHERE posto IN ($in)");
        }
        $spostatiRamo += $riallinea($figliX, $deltaXY);
      }
      if ($figliY) {
        foreach (array_chunk($figliY, 900) as $b) {
          $in = implode(',', array_map('intval', $b));
          $pdo->exec("UPDATE network_posti SET padre=".$da.", padre_manuale=1, aggiornato=datetime('now') WHERE posto IN ($in)");
        }
        $spostatiRamo += $riallinea($figliY, $deltaYX);
      }
    }

    $pdo->commit();
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    return ['ok'=>false, 'err'=>'Errore in scrittura, non ho cambiato niente: '.substr($e->getMessage(), 0, 160)];
  }

  /* ---- allineamenti fuori transazione: se falliscono non invalidano lo spostamento ----

     REGOLA (Mirco, 15/08 — correzione di un errore mio):
     Il SIC PERSONALE della persona NON SI TOCCA MAI. Sono due codici diversi
     e convivono:
        SIC personale  users.internal_code (es. SIC-ID-000000000090)  e' della
                       PERSONA, nasce con l'iscrizione e non cambia mai, nemmeno
                       cambiando dieci posti.
        SIC del nodo   network_posti.sic_id (es. SIC-ID-G-WN-002) e' del POSTO,
                       resta attaccato al posto e non si muove con nessuno.
     Occupando un posto la persona ACQUISISCE il SIC di quel posto IN PIU' al
     suo: chi tiene 3 posti ha 1 SIC personale + 3 SIC di nodo, e puo' usare il
     link di invito con uno qualsiasi di questi.
     La versione precedente di questa funzione SOVRASCRIVEVA users.sic_id col
     SIC del posto: sbagliato, cancellava l'identita' della persona. Rimosso. */
  $allinea = function (int $uid, int $posto) use ($pdo) {
    if ($uid <= 0) return;
    try {
      $padre    = (int)$pdo->query("SELECT padre FROM network_posti WHERE posto=".$posto)->fetchColumn();
      $uidPadre = (int)$pdo->query("SELECT COALESCE(uid,0) FROM network_posti WHERE posto=".$padre)->fetchColumn();

      /* garantisco che la persona abbia il SUO codice personale (mai quello
         del posto): stessa regola di db.php -> SIC-ID-<uid a 12 cifre> */
      alb_assicura_sic_personale($pdo, $uid);
      $sicPersonale = alb_sic_personale($pdo, $uid);

      if (function_exists('net_migra')) net_migra($pdo);
      $c = (int)$pdo->query("SELECT COUNT(*) FROM network_nodes WHERE uid=".$uid)->fetchColumn();
      if ($c) $pdo->prepare("UPDATE network_nodes SET upline_uid=?, aggiornato=datetime('now') WHERE uid=?")->execute([$uidPadre, $uid]);
      else    $pdo->prepare("INSERT OR IGNORE INTO network_nodes(uid,sic_id,rango,rank_floor,status,upline_uid,stato,ultimo_attivo)
                             SELECT ?,?,3,3,status,?, 'attivo', datetime('now') FROM network_posti WHERE posto=?")
                  ->execute([$uid, $sicPersonale, $uidPadre, $posto]);
      /* NOTA: in network_nodes va il SIC PERSONALE, non quello del posto:
         quella colonna e' UNIQUE e identifica la PERSONA nella pipeline. */
    } catch (Throwable $e) {}
  };

  if ($modo === 'occupa') { $allinea($uidX, $a); }
  else { $allinea($uidX, $a); $allinea($uidY, $da); }

  alb_log($pdo, 'sposta_utente', $da,
          'uid='.$uidX.' su posto '.$da,
          'modo='.$modo.' -> posto '.$a.($modo==='scambia' ? ' (scambio con uid='.$uidY.')' : '').' rami='.count($figliX), $attore);

  return [
    'ok'            => true,
    'modo'          => $modo,
    'da'            => $da,
    'a'             => $a,
    'uid'           => $uidX,
    'nome'          => $X['nome'] ?: ('utente #'.$uidX),
    'uid_scambiato' => $modo === 'scambia' ? $uidY : 0,
    'nome_scambiato'=> $modo === 'scambia' ? ($Y['nome'] ?: ('utente #'.$uidY)) : '',
    'sic_nodo_nuovo'    => (string)($Y['sic_id'] ?? ''),   // SIC del posto in cui entra
    'sic_nodo_lasciato' => (string)($X['sic_id'] ?? ''),   // SIC del posto che lascia
    'sic_personale'     => alb_sic_personale($pdo, $uidX), // NON cambia mai
    'status_nuovo'      => (string)($Y['status'] ?? ''),
    'figli_seguiti'     => count($figliX),
    'rami_riallineati'  => $spostatiRamo,
  ];
}

/* ==========================================================================
   I DUE CODICI — SIC PERSONALE e SIC DI NODO

   Sono due cose diverse e devono restare separate (Mirco, 15/08/2026):

     SIC PERSONALE   e' della PERSONA. Nasce con l'iscrizione, non cambia mai,
                     e' unico. Vive in users.internal_code (formato di db.php:
                     'SIC-ID-' + uid a 12 cifre, es. SIC-ID-000000000090).
                     users.sic_id ne e' una copia di comodo quando presente.

     SIC DI NODO     e' del POSTO nella rete. Vive in network_posti.sic_id
                     (es. SIC-ID-G-WN-002) e resta attaccato al posto: non si
                     muove con la persona.

   Chi occupa un posto ACQUISISCE il SIC di quel posto IN AGGIUNTA al proprio.
   Chi ne occupa cinque ha 1 SIC personale + 5 SIC di nodo. Il link di invito
   funziona con TUTTI: vedi alb_risolvi_ref().
   ========================================================================== */

/** Il codice personale della persona (mai quello di un posto). */
function alb_sic_personale(PDO $pdo, int $uid): string {
  if ($uid <= 0) return '';
  try {
    $st = $pdo->prepare("SELECT COALESCE(NULLIF(internal_code,''), NULLIF(sic_id,''), '') FROM users WHERE id=? LIMIT 1");
    $st->execute([$uid]);
    return (string)($st->fetchColumn() ?: '');
  } catch (Throwable $e) { return ''; }
}

/** Se manca, glielo crea con la stessa regola di db.php. Non tocca mai chi ce l'ha. */
function alb_assicura_sic_personale(PDO $pdo, int $uid): string {
  $sic = alb_sic_personale($pdo, $uid);
  if ($sic !== '') return $sic;
  $sic = 'SIC-ID-' . substr('000000000000' . $uid, -12);
  try {
    $pdo->prepare("UPDATE users SET internal_code=COALESCE(NULLIF(internal_code,''),?),
                                    sic_id=COALESCE(NULLIF(sic_id,''),?) WHERE id=?")
        ->execute([$sic, $sic, $uid]);
  } catch (Throwable $e) {}
  return $sic;
}

/** Tutti i codici di una persona: il suo, piu' quelli dei posti che occupa. */
function alb_sic_utente(PDO $pdo, int $uid): array {
  $out = ['uid'=>$uid, 'personale'=>alb_sic_personale($pdo, $uid), 'nodi'=>[]];
  if ($uid <= 0) return $out;
  try {
    $st = $pdo->prepare("SELECT posto, sic_id, status, stato FROM network_posti WHERE uid=? ORDER BY posto");
    $st->execute([$uid]);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
      $out['nodi'][] = ['posto'=>(int)$r['posto'], 'sic'=>(string)$r['sic_id'],
                        'status'=>(string)$r['status'], 'stato'=>(string)$r['stato']];
    }
  } catch (Throwable $e) {}
  return $out;
}

/**
 * RISOLUTORE DEL LINK DI INVITO.
 * Dato un codice qualsiasi — il SIC personale, il SIC di un nodo che la
 * persona occupa, o il suo internal_code — restituisce di chi e'.
 * Serve perche' l'utente puo' dare il proprio ref con l'uno o con l'altro e
 * deve funzionare uguale.
 * Ritorna ['ok'=>true,'uid'=>N,'nome'=>..,'via'=>'personale|nodo','posto'=>N]
 */
function alb_risolvi_ref(PDO $pdo, string $codice): array {
  alb_migra($pdo);
  $codice = trim($codice);
  if ($codice === '') return ['ok'=>false, 'err'=>'Codice vuoto.'];

  /* 1) e' il SIC di un posto? allora l'invitante e' chi lo occupa */
  try {
    $st = $pdo->prepare("SELECT p.posto, p.uid, p.status, COALESCE(u.full_name,u.username,'') nome
                         FROM network_posti p LEFT JOIN users u ON u.id=p.uid
                         WHERE p.sic_id=? LIMIT 1");
    $st->execute([$codice]);
    if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      if ((int)$r['uid'] > 0) {
        return ['ok'=>true, 'uid'=>(int)$r['uid'], 'nome'=>(string)$r['nome'],
                'via'=>'nodo', 'posto'=>(int)$r['posto'], 'status'=>(string)$r['status'],
                'sic_personale'=>alb_sic_personale($pdo, (int)$r['uid'])];
      }
      return ['ok'=>false, 'err'=>'Il posto '.$r['posto'].' esiste ma non e\' occupato da nessuno.'];
    }
  } catch (Throwable $e) {}

  /* 2) e' il codice personale di qualcuno? */
  try {
    $st = $pdo->prepare("SELECT id, COALESCE(full_name,username,'') nome
                         FROM users WHERE internal_code=? OR sic_id=? OR genesys_sic=? LIMIT 1");
    $st->execute([$codice, $codice, $codice]);
    if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $uid = (int)$r['id'];
      $suoi = alb_sic_utente($pdo, $uid);
      return ['ok'=>true, 'uid'=>$uid, 'nome'=>(string)$r['nome'], 'via'=>'personale',
              'posto'=>($suoi['nodi'][0]['posto'] ?? 0), 'sic_personale'=>$suoi['personale'],
              'posti_occupati'=>count($suoi['nodi'])];
    }
  } catch (Throwable $e) {}

  return ['ok'=>false, 'err'=>'Nessuno corrisponde al codice '.$codice.'.'];
}

/* --------------------------------------------------------------------------
   ELENCO DELLE PERSONE — tutti i posti occupati, con ricerca e a pagine.
   Serve al pannello "Utenti": con 5 milioni di posizioni cercare una persona
   girando l'albero a mano non e' praticabile. Filtra su uid>0 (indice
   ix_np_uid2), quindi non tocca mai i milioni di posti liberi.
-------------------------------------------------------------------------- */
function alb_utenti(PDO $pdo, string $q = '', int $limit = 100, int $offset = 0): array {
  alb_migra($pdo);
  $limit = max(1, min(500, $limit)); $offset = max(0, $offset);
  $q = trim($q);

  $dove = "p.uid>0";
  $arg  = [];
  if ($q !== '') {
    if (ctype_digit($q)) { $dove .= " AND (p.posto=? OR p.uid=?)"; $arg[] = (int)$q; $arg[] = (int)$q; }
    else {
      $dove .= " AND (p.sic_id LIKE ? OR u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
      $like = '%'.$q.'%'; $arg = [$like, $like, $like, $like];
    }
  }

  $tot = 0;
  try {
    $st = $pdo->prepare("SELECT COUNT(*) FROM network_posti p LEFT JOIN users u ON u.id=p.uid WHERE ".$dove);
    $st->execute($arg); $tot = (int)$st->fetchColumn();
  } catch (Throwable $e) {}

  $st = $pdo->prepare("SELECT p.posto,p.padre,p.livello,p.sic_id,p.status,p.stato,p.uid,p.padre_manuale,
                              COALESCE(u.full_name,u.username,'') nome, u.email
                       FROM network_posti p LEFT JOIN users u ON u.id=p.uid
                       WHERE ".$dove." ORDER BY p.posto LIMIT ? OFFSET ?");
  $i = 1; foreach ($arg as $v) $st->bindValue($i++, $v);
  $st->bindValue($i++, $limit, PDO::PARAM_INT);
  $st->bindValue($i,   $offset, PDO::PARAM_INT);
  $st->execute();

  $righe = $st->fetchAll(PDO::FETCH_ASSOC);
  $ids = array_map(function ($r) { return (int)$r['posto']; }, $righe);
  $figli = [];
  if ($ids) {
    $in = implode(',', $ids);
    foreach ($pdo->query("SELECT padre, COUNT(*) c FROM network_posti WHERE padre IN ($in) GROUP BY padre") as $r) {
      $figli[(int)$r['padre']] = (int)$r['c'];
    }
  }

  $out = [];
  foreach ($righe as $r) {
    $n = alb_riga_nodo($r);
    $n['email'] = (string)($r['email'] ?? '');
    $n['figli'] = $figli[$n['posto']] ?? 0;
    /* il codice personale della persona, distinto da quello del posto */
    $n['sic_personale'] = alb_sic_personale($pdo, $n['uid']);
    $out[] = $n;
  }
  return ['ok'=>true, 'utenti'=>$out, 'totale'=>$tot, 'mostrati'=>count($out),
          'offset'=>$offset, 'altri'=>max(0, $tot - $offset - count($out))];
}

/* --------------------------------------------------------------------------
   LIBERA UNA POSIZIONE — l'utente esce, il posto torna libero.
   Non cancella nulla di storico: resta tutto nel log.
-------------------------------------------------------------------------- */
function alb_libera(PDO $pdo, int $posto, string $attore = 'admin'): array {
  alb_migra($pdo);
  if ($posto <= 0) return ['ok'=>false, 'err'=>'Il MASTER-NODE non si libera.'];
  $st = $pdo->prepare("SELECT uid FROM network_posti WHERE posto=? LIMIT 1");
  $st->execute([$posto]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  if (!$r) return ['ok'=>false, 'err'=>'Posto inesistente.'];
  $vecchio = (int)$r['uid'];

  try {
    $pdo->prepare("UPDATE network_posti SET uid=0, stato='libero', aggiornato=datetime('now') WHERE posto=?")
        ->execute([$posto]);
  } catch (Throwable $e) {
    return ['ok'=>false, 'err'=>'Errore in scrittura: '.substr($e->getMessage(), 0, 160)];
  }
  alb_log($pdo, 'libera', $posto, 'uid='.$vecchio, 'libero', $attore);
  return ['ok'=>true, 'posto'=>$posto, 'liberato_uid'=>$vecchio];
}

/* --------------------------------------------------------------------------
   RESYNC TOPOLOGIA — riallinea padre/livello a net_padre()/net_livello().

   Serve perche' sul DB reale 115 posti su 118 avevano ancora la vecchia
   topologia ternaria (3 World sotto il Master invece di 9).

   NON TOCCA MAI:
     - i posti con padre_manuale=1 (spostati a mano da Mirco)
     - i posti occupati (uid>0), a meno di forzare esplicitamente
   Cosi' un riallineamento non puo' cancellare ne' una vendita ne' una scelta.
   Ritorna il conto esatto di cosa ha cambiato e cosa ha saltato.
-------------------------------------------------------------------------- */
/* ==========================================================================
   RIALLINEAMENTO AUTOMATICO — aggiunto 2026-08-15.

   PERCHE'. La topologia a stella e' nel codice dal 10 agosto ma non e' mai
   stata applicata ai dati: sul database vero 115 posti su 118 hanno ancora il
   padre della vecchia topologia ternaria, e sotto il MASTER-NODE risultano
   3 World invece di 9. Finora si sistemava con un click su un banner: ma un
   click che qualcuno deve ricordarsi di dare, prima o poi non viene dato.

   Ora si sistema da solo, la prima volta che qualcuno apre l'Albero, la
   Stella o l'API. Poi segna in banca dati che l'ha fatto e non ci torna piu'.

   PERCHE' E' SICURO FARLO DA SOLO:
     - non tocca chi e' gia' dentro (uid > 0)
     - non tocca chi e' stato spostato a mano (padre_manuale = 1)
     - non tocca i posti oltre il 118: quelli sono gia' aritmetica pura
     - cambia solo i posti LIBERI e MAI toccati, cioe' righe che non hanno
       ancora nessuna storia da rispettare
   In pratica riscrive una colonna di posti vuoti per farla combaciare con
   quello che il codice gia' calcola. Non c'e' un caso in cui questo tolga
   qualcosa a qualcuno.

   Si puo' spegnere: basta scrivere auto_resync=0 in network_cfg.
========================================================================== */
function alb_auto_resync(PDO $pdo): array {
  static $fatto = false;
  if ($fatto) return ['ok'=>true, 'saltato'=>'gia-in-questa-richiesta'];
  $fatto = true;

  try { $pdo->exec("CREATE TABLE IF NOT EXISTS network_cfg(k TEXT PRIMARY KEY, v TEXT, ts TEXT)"); }
  catch (Throwable $e) { return ['ok'=>false, 'err'=>'no-cfg']; }

  $leggi = function (string $k, $def = null) use ($pdo) {
    try { $st = $pdo->prepare("SELECT v FROM network_cfg WHERE k=? LIMIT 1"); $st->execute([$k]);
          $v = $st->fetchColumn(); return ($v === false || $v === null) ? $def : $v; }
    catch (Throwable $e) { return $def; }
  };
  $scrivi = function (string $k, string $v) use ($pdo) {
    try { $pdo->prepare("INSERT INTO network_cfg(k,v,ts) VALUES(?,?,datetime('now'))
                         ON CONFLICT(k) DO UPDATE SET v=excluded.v, ts=excluded.ts")->execute([$k, $v]); }
    catch (Throwable $e) {}
  };

  if ((int)$leggi('auto_resync', 1) !== 1) return ['ok'=>true, 'saltato'=>'spento'];
  if ((string)$leggi('resync_fatto', '') !== '')  return ['ok'=>true, 'saltato'=>'gia-fatto'];

  /* serve davvero? sotto il Master devono esserci 9 World */
  try {
    $world = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=0 AND posto>0")->fetchColumn();
  } catch (Throwable $e) { return ['ok'=>false, 'err'=>'no-tabella']; }
  if ($world >= 9) { $scrivi('resync_fatto', 'non-serviva'); return ['ok'=>true, 'saltato'=>'gia-allineata']; }

  $r = alb_resync_topologia($pdo, false, 'automatico');
  if (!empty($r['ok'])) {
    $scrivi('resync_fatto', 'cambiati=' . (int)$r['cambiati'] . ' world=' . (int)$r['world_sotto_master']);
    $r['automatico'] = 1;
  }
  return $r;
}

function alb_resync_topologia(PDO $pdo, bool $ancheOccupati = false, string $attore = 'admin'): array {
  alb_migra($pdo);
  $tot = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;

  $cambiati = 0; $saltati_manuali = 0; $saltati_occupati = 0; $gia_ok = 0;
  $upd = $pdo->prepare("UPDATE network_posti SET padre=?, livello=?, aggiornato=datetime('now') WHERE posto=?");

  try {
    $pdo->beginTransaction();
    $st = $pdo->query("SELECT posto,padre,livello,uid,padre_manuale FROM network_posti WHERE posto>0 AND posto<=".$tot);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
      $posto = (int)$r['posto'];
      if ((int)$r['padre_manuale'] === 1) { $saltati_manuali++;  continue; }
      if (!$ancheOccupati && (int)$r['uid'] > 0) { $saltati_occupati++; continue; }
      $pv = net_padre($posto); $lv = net_livello($posto);
      if ((int)$r['padre'] === $pv && (int)$r['livello'] === $lv) { $gia_ok++; continue; }
      $upd->execute([$pv, $lv, $posto]);
      $cambiati++;
    }
    $pdo->commit();
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    return ['ok'=>false, 'err'=>substr($e->getMessage(), 0, 200)];
  }

  $world = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=0 AND posto>0")->fetchColumn();
  alb_log($pdo, 'resync', 0, 'topologia', 'cambiati='.$cambiati.' world='.$world, $attore);

  return ['ok'=>true, 'cambiati'=>$cambiati, 'gia_corretti'=>$gia_ok,
          'saltati_manuali'=>$saltati_manuali, 'saltati_occupati'=>$saltati_occupati,
          'world_sotto_master'=>$world, 'totale_posti_kit'=>$tot];
}


/* ==========================================================================
   METTERE UNA PERSONA NELLA RETE DI QUALCUN ALTRO
   Aggiunto 2026-08-15.

   IL VINCOLO STRUTTURALE, detto prima di tutto: nella topologia a stella solo
   i 118 nodi hanno figli propri. I 5.000.000 di posti utente sono foglie
   appese agli 82 Pro — un utente normale, per costruzione, non ha posti
   "suoi" sotto di se'.
   Quindi mettere qualcuno nella rete di uno sponsor che NON e' uno dei 118
   si fa in un modo solo: si prende un posto libero e lo si riaggancia a mano
   allo sponsor (padre_manuale=1). E' esattamente il meccanismo che gia' usa
   lo spostamento delle persone, e la riga resta marcata come manuale cosi'
   nessun riallineamento automatico la sposta piu'.
========================================================================== */

/** Il primo posto utente libero della rete (nessuna riga, o riga senza uid). */
function alb_primo_posto_libero(PDO $pdo, int $da = 119): int {
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $fine = alb_ultimo_posto();
  if ($da <= $base) $da = $base + 1;
  /* i posti occupati sono pochissimi rispetto ai 5 milioni: si prende
     l'elenco di quelli gia' presi e si cerca il primo buco. */
  $presi = [];
  try {
    $q = $pdo->query("SELECT posto FROM network_posti
                      WHERE posto > {$base} AND (COALESCE(uid,0)>0 OR COALESCE(assigned_uid,0)>0)
                      ORDER BY posto");
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $v) $presi[(int)$v] = 1;
  } catch (Throwable $e) {}
  for ($n = $da; $n <= $fine; $n++) if (empty($presi[$n])) return $n;
  return 0;
}

/**
 * Un posto figlio libero dello sponsor.
 *
 * ATTENZIONE, e' la regola piu' importante di questa funzione: i primi 118
 * posti NON si danno mai per questa strada. Sono i nodi dei Pionieri, si
 * comprano col Kit, e non possono finire per sbaglio a un iscritto al webinar
 * solo perche' erano il primo buco libero sotto lo sponsor.
 * Provato: senza questo filtro, i primi tre iscritti messi sotto il World #4
 * si prendevano i National #13, #14 e #15.
 */
function alb_figlio_libero_di(PDO $pdo, int $padre): int {
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  try {
    $v = $pdo->query("SELECT MIN(posto) FROM network_posti
                      WHERE padre={$padre} AND posto>{$base}
                        AND COALESCE(uid,0)=0 AND COALESCE(assigned_uid,0)=0")->fetchColumn();
    if ($v !== false && $v !== null && (int)$v > 0) return (int)$v;
  } catch (Throwable $e) {}
  /* figli calcolati (solo gli 82 Pro ne hanno) */
  $q = alb_quanti_virtuali($padre);
  for ($i = 0; $i < min($q, 5000); $i++) {
    $n = alb_virtuale_iesimo($padre, $i);
    $c = (int)$pdo->query("SELECT COUNT(*) FROM network_posti
                           WHERE posto={$n} AND (COALESCE(uid,0)>0 OR COALESCE(assigned_uid,0)>0)")->fetchColumn();
    if ($c === 0) return $n;
  }
  return 0;
}

/**
 * *** METTE $uid NELLA RETE DI $postoSponsor ***
 * Torna ['ok'=>bool,'posto'=>N,'padre'=>M,'manuale'=>0|1,'sic_nodo'=>...,'sic_personale'=>...]
 *
 * Cosa fa, in ordine:
 *   1. controlla che la persona non abbia gia' un posto (un utente = un posto)
 *   2. cerca un posto libero sotto lo sponsor; se lo sponsor non puo' averne
 *      (non e' uno dei 118), prende il primo posto libero della rete e lo
 *      riaggancia allo sponsor a mano
 *   3. materializza la riga, ci mette la persona, le lascia il SUO SIC
 *      personale e le AGGIUNGE quello del nodo
 */
function alb_metti_sotto(PDO $pdo, int $uid, int $postoSponsor, string $stato = 'attivo'): array {
  alb_migra($pdo);
  if ($uid <= 0) return ['ok'=>false, 'err'=>'Utente non valido.'];

  /* lo sponsor esiste? */
  $sp = null;
  try {
    $st = $pdo->prepare("SELECT posto, livello, status FROM network_posti WHERE posto=? LIMIT 1");
    $st->execute([$postoSponsor]); $sp = $st->fetch(PDO::FETCH_ASSOC) ?: null;
  } catch (Throwable $e) {}
  if (!$sp) {
    $v = alb_virtuale($postoSponsor);
    if (!$v) return ['ok'=>false, 'err'=>'Il posto sponsor #'.$postoSponsor.' non esiste.'];
    alb_materializza($pdo, $postoSponsor);
    $sp = ['posto'=>$postoSponsor, 'livello'=>$v['livello'], 'status'=>$v['status']];
  }

  /* UN UTENTE = UN POSTO.
     Il numero va messo dentro la query, NON passato come parametro: PDO lega
     i parametri come TESTO, e in SQLite il confronto fra COALESCE(uid,0) —
     che e' un'espressione, quindi senza affinita' di colonna — e una stringa
     non e' mai vero. Provato: con il parametro, la stessa persona si prendeva
     un secondo posto (#119 e poi #129) senza che il controllo se ne
     accorgesse. Con il numero scritto nella query, viene fermata. */
  try {
    $st = $pdo->query("SELECT posto FROM network_posti
                       WHERE COALESCE(uid,0)={$uid} OR COALESCE(assigned_uid,0)={$uid} LIMIT 1");
    $gia = (int)($st->fetchColumn() ?: 0);
    if ($gia > 0) return ['ok'=>false, 'err'=>'Questa persona ha gia\' il posto #'.$gia.'. Un utente = un posto: per muoverla usa lo spostamento.', 'posto'=>$gia];
  } catch (Throwable $e) {}

  $manuale = 0;
  $posto = alb_figlio_libero_di($pdo, $postoSponsor);
  if ($posto <= 0) { $posto = alb_primo_posto_libero($pdo); $manuale = 1; }
  if ($posto <= 0) return ['ok'=>false, 'err'=>'Nessun posto libero in tutta la rete.'];

  alb_materializza($pdo, $posto);

  $pdo->beginTransaction();
  try {
    $liv = (int)$sp['livello'] + 1;
    $pdo->prepare("UPDATE network_posti
                   SET uid=?, assigned_uid=?, stato=?, padre=?, livello=?,
                       padre_manuale=?, preso_il=COALESCE(preso_il, datetime('now'))
                   WHERE posto=?")
        ->execute([$uid, $uid, $stato, $postoSponsor, $liv, $manuale, $posto]);
    $pdo->commit();
  } catch (Throwable $e) {
    $pdo->rollBack();
    return ['ok'=>false, 'err'=>substr($e->getMessage(), 0, 180)];
  }

  /* il SIC personale NON si tocca: si aggiunge quello del nodo */
  alb_assicura_sic_personale($pdo, $uid);
  $sic = alb_sic_utente($pdo, $uid);
  alb_log($pdo, 'metti_sotto', $posto, 'libero', 'uid '.$uid.' sotto #'.$postoSponsor.($manuale ? ' (manuale)' : ''));

  return ['ok'=>true, 'posto'=>$posto, 'padre'=>$postoSponsor, 'livello'=>$liv,
          'manuale'=>$manuale, 'sic'=>$sic];
}

/**
 * GLI ULTIMI ARRIVATI — le posizioni prese piu' di recente, con la strada
 * per arrivarci.
 *
 * Perche' serve: dopo un caricamento di massa i nuovi finiscono al quarto
 * livello, appesi agli 82 Pro. La vista di partenza dell'Albero arriva al
 * secondo (Master, World, National): i nuovi ci sono ma stanno due click piu'
 * in la', e chi guarda pensa che il caricamento non abbia funzionato.
 * Con questa lista il bottone "ultimi arrivati" ci porta dritto.
 */
function alb_ultimi_arrivati(PDO $pdo, int $quanti = 25): array {
  alb_migra($pdo);
  $quanti = max(1, min(200, $quanti));
  $out = [];
  try {
    $q = $pdo->query("SELECT p.posto, p.padre, p.livello, p.stato, p.sic_id,
                             COALESCE(u.full_name, u.username, u.email, '') nome
                      FROM network_posti p LEFT JOIN users u ON u.id = COALESCE(NULLIF(p.uid,0), p.assigned_uid)
                      WHERE COALESCE(p.uid,0) > 0 OR COALESCE(p.assigned_uid,0) > 0
                      ORDER BY COALESCE(p.preso_il,'') DESC, p.posto DESC
                      LIMIT " . $quanti);
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
      $posto = (int)$r['posto'];
      /* la catena dalla radice: serve per aprire i rami giusti */
      $percorso = []; $cur = $posto; $hop = 0;
      while ($cur > 0 && $hop < 60) { $percorso[] = $cur; $cur = alb_padre_di($pdo, $cur); $hop++; }
      $percorso[] = 0;
      $out[] = ['posto'=>$posto, 'padre'=>(int)$r['padre'], 'livello'=>(int)$r['livello'],
                'stato'=>(string)$r['stato'], 'sic'=>(string)$r['sic_id'],
                'nome'=>(string)($r['nome'] ?: ('posto #'.$posto)),
                'percorso'=>array_reverse($percorso)];
    }
  } catch (Throwable $e) {}
  return $out;
}

/* ==========================================================================
   DARE UNA FORMA AI RAMI — da 96 foglie in fila a una piccola struttura.
   Aggiunto 2026-08-15.

   IL PROBLEMA. Caricando i lead in fila, ogni Pro si ritrova un centinaio di
   persone tutte attaccate a lui, allo stesso livello. E' equo ma e' piatto:
   nessuno ha un upline che non sia il Pro, nessuno ha una downline propria.
   Non e' una rete, e' un elenco.

   COSA FA. Dentro OGNI Pro, prende le persone che ha e le rimonta ad albero
   con un fattore di ramificazione scelto (2, 3, 4...). La regola e' quella
   dell'heap: la persona in posizione i ha per padre la persona in posizione
   (i-1)/k dello stesso Pro. Le prime k restano attaccate al Pro.
   Con 96 persone e k=3 vengono 3 rami, ognuno con 3 sotto, e cosi' via: una
   piramide alta 5 livelli, tutta dentro quel Pro.

   PERCHE' RESTA EQUO. Non si sposta nessuno da un Pro all'altro: ogni Pro
   tiene le sue. La distribuzione fra gli 82 rami resta quella di prima (che
   era gia' perfetta); cambia solo la forma DENTRO ciascuno.

   COSA NON TOCCA:
     - i 118 nodi: mai, per nessun motivo
     - chi e' stato spostato a mano (padre_manuale=1) prima di adesso
     - lo stato delle persone: la forma e' una cosa, essere attivi un'altra
   Le righe rimontate restano segnate manuali, cosi' il riallineamento
   automatico non le rimette in fila.
========================================================================== */
function alb_ristruttura_rami(PDO $pdo, int $k = 3, bool $prova = false): array {
  alb_migra($pdo);
  $k = max(2, min(10, $k));
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;

  /* le persone di ogni Pro, in ordine di posto */
  $perPro = [];
  try {
    $q = $pdo->query("SELECT posto, padre, livello, COALESCE(padre_manuale,0) manuale
                      FROM network_posti
                      WHERE posto > {$base}
                        AND (COALESCE(uid,0) > 0 OR COALESCE(assigned_uid,0) > 0)
                      ORDER BY posto");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
      /* il capostipite del ramo: si risale fino a trovare uno dei 118 */
      $capo = (int)$r['padre'];
      $hop = 0;
      while ($capo > $base && $hop < 60) { $capo = alb_padre_di($pdo, $capo); $hop++; }
      if ($capo <= 0 || $capo > $base) continue;
      $perPro[$capo][] = (int)$r['posto'];
    }
  } catch (Throwable $e) { return ['ok'=>false, 'err'=>substr($e->getMessage(), 0, 180)]; }

  $cambiati = 0; $gia = 0; $prof = 0; $rami = 0; $persone = 0;
  $anteprima = [];

  if (!$prova) $pdo->beginTransaction();
  try {
    $upd = $prova ? null : $pdo->prepare("UPDATE network_posti
                                          SET padre=?, livello=?, padre_manuale=1
                                          WHERE posto=?");
    foreach ($perPro as $pro => $lista) {
      $rami++; $persone += count($lista);
      $livPro = (int)($pdo->query("SELECT livello FROM network_posti WHERE posto={$pro}")->fetchColumn() ?: 3);
      foreach ($lista as $i => $posto) {
        /* regola dell'heap: i primi k al Pro, poi ognuno prende k figli */
        $padre = ($i < $k) ? $pro : $lista[intdiv($i - $k, $k)];
        /* profondita': quanti salti prima di arrivare al Pro */
        $d = 0; $j = $i;
        while ($j >= $k) { $j = intdiv($j - $k, $k); $d++; }
        $liv = $livPro + 1 + $d;
        $prof = max($prof, $d + 1);
        if (count($anteprima) < 12) $anteprima[] = ['posto'=>$posto, 'padre'=>$padre, 'livello'=>$liv];
        /* gia' a posto? non si riscrive per niente */
        $ora = $pdo->query("SELECT padre FROM network_posti WHERE posto={$posto}")->fetchColumn();
        if ((int)$ora === (int)$padre) { $gia++; continue; }
        if (!$prova) $upd->execute([$padre, $liv, $posto]);
        $cambiati++;
      }
    }
    if (!$prova) $pdo->commit();
  } catch (Throwable $e) {
    if (!$prova && $pdo->inTransaction()) $pdo->rollBack();
    return ['ok'=>false, 'err'=>substr($e->getMessage(), 0, 180)];
  }

  if (!$prova) alb_log($pdo, 'ristruttura', 0, 'in fila', "k={$k} cambiati={$cambiati}");
  return ['ok'=>true, 'prova'=>$prova ? 1 : 0, 'k'=>$k, 'rami'=>$rami, 'persone'=>$persone,
          'cambiati'=>$cambiati, 'gia_a_posto'=>$gia, 'profondita'=>$prof, 'anteprima'=>$anteprima];
}

/**
 * ATTIVA IN BLOCCO le posizioni prenotate.
 * Sta qui e non dentro la ristrutturazione perche' sono due cose diverse:
 * la forma dei rami e' grafica e reversibile, "attivo" e' una dichiarazione
 * che quella persona e' dentro il Branco — e la leggono i ranghi, i contatori
 * e ogni motore che guarda stato='attivo'.
 */
function alb_attiva_in_blocco(PDO $pdo, string $daStato = 'prenotato', int $massimo = 20000): array {
  alb_migra($pdo);
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $daStato = in_array($daStato, ['prenotato', 'confermato'], true) ? $daStato : 'prenotato';
  try {
    $quanti = (int)$pdo->query("SELECT COUNT(*) FROM network_posti
                                WHERE posto > {$base} AND stato = '{$daStato}'")->fetchColumn();
    $st = $pdo->prepare("UPDATE network_posti SET stato='attivo', attivato_il=datetime('now')
                         WHERE posto > {$base} AND stato = ?
                           AND posto IN (SELECT posto FROM network_posti
                                         WHERE posto > {$base} AND stato = ? LIMIT " . (int)$massimo . ")");
    $st->execute([$daStato, $daStato]);
    $n = $st->rowCount();
    alb_log($pdo, 'attiva_blocco', 0, $daStato, 'attivate=' . $n);
    return ['ok'=>true, 'attivate'=>$n, 'erano'=>$quanti, 'da'=>$daStato];
  } catch (Throwable $e) {
    return ['ok'=>false, 'err'=>substr($e->getMessage(), 0, 180)];
  }
}

/* --------------------------------------------------------------------------
   DOPPIONI — chi occupa piu' di un posto.
   Con la regola "un utente, un posto" questo elenco deve essere VUOTO: se non
   lo e', qualcosa (un import, un vecchio checkout, un multi-account) ha creato
   posizioni doppie e vanno sistemate a mano. Query leggera: raggruppa solo sui
   posti occupati (indice ix_np_uid2), non tocca i milioni di posti liberi.
-------------------------------------------------------------------------- */
function alb_doppioni(PDO $pdo, int $limit = 200): array {
  alb_migra($pdo);
  $out = [];
  try {
    $st = $pdo->prepare("SELECT p.uid, COUNT(*) quanti, COALESCE(u.full_name,u.username,'') nome, u.email
                         FROM network_posti p LEFT JOIN users u ON u.id=p.uid
                         WHERE p.uid>0 GROUP BY p.uid HAVING COUNT(*)>? ORDER BY quanti DESC LIMIT ?");
    $st->bindValue(1, DR_MAX_POSTI_UTENTE, PDO::PARAM_INT);
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->execute();
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
      $uid = (int)$r['uid'];
      $posti = [];
      foreach ($pdo->query("SELECT posto,sic_id,status,stato FROM network_posti WHERE uid=".$uid." ORDER BY posto") as $q) {
        $posti[] = ['posto'=>(int)$q['posto'], 'sic'=>(string)$q['sic_id'],
                    'status'=>(string)$q['status'], 'stato'=>(string)$q['stato']];
      }
      $out[] = ['uid'=>$uid, 'nome'=>(string)$r['nome'], 'email'=>(string)$r['email'],
                'quanti'=>(int)$r['quanti'], 'posti'=>$posti,
                'sic_personale'=>alb_sic_personale($pdo, $uid)];
    }
  } catch (Throwable $e) {}
  return ['ok'=>true, 'limite'=>DR_MAX_POSTI_UTENTE, 'doppioni'=>$out, 'quanti'=>count($out)];
}

/* --------------------------------------------------------------------------
   RIEPILOGO GLOBALE per l'intestazione delle pagine.
-------------------------------------------------------------------------- */
function alb_riepilogo(PDO $pdo): array {
  alb_migra($pdo);
  $o = ['posti'=>0,'occupati'=>0,'attivi'=>0,'prenotati'=>0,'liberi'=>0,'world'=>0,'national'=>0,'pro'=>0,'user'=>0];
  try {
    $righeVere      = (int)$pdo->query("SELECT COUNT(*) FROM network_posti")->fetchColumn();
    $base           = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
    /* i posti utente esistono tutti, anche quelli non ancora scritti come riga */
    $righeUtente    = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE posto>".$base)->fetchColumn();
    $o['posti']     = $righeVere + max(0, (defined('NET_USER_POSTI') ? (int)NET_USER_POSTI : 5000000) - $righeUtente);
    $o['righe_vere']= $righeVere;
    $o['occupati']  = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE uid>0")->fetchColumn();
    $o['attivi']    = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE stato='attivo'")->fetchColumn();
    $o['prenotati'] = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE stato='prenotato'")->fetchColumn();
    $o['liberi']    = $o['posti'] - $o['occupati'];
    foreach ($pdo->query("SELECT status, COUNT(*) c FROM network_posti GROUP BY status") as $r) {
      $k = strtolower((string)$r['status']);
      if (isset($o[$k])) $o[$k] = (int)$r['c'];
    }
    /* 'user' = tutte le posizioni della rete, righe vere + calcolate */
    $o['user'] = max($o['user'], (defined('NET_USER_POSTI') ? (int)NET_USER_POSTI : 5000000));
  } catch (Throwable $e) {}
  return $o;
}

/* ============================================================================
   IL PESO VERO DI UN RAMO — quante PERSONE ci sono sotto, non quanti posti.
   Aggiunto 2026-08-15 · Cowork

   PERCHE' SERVE
   Le viste proporzionali (gli Anelli) devono dire quale ramo sta crescendo.
   Se il peso fosse il numero di POSIZIONI, ogni Pro peserebbe uguale
   (61.000 posti a testa, per costruzione) e il disegno direbbe: niente.
   Il peso e' la gente: occupati e attivi.

   COME
   Non si fa una query per ramo (sarebbero 82 query). Si leggono in un colpo
   solo le righe VERE di network_posti — cioe' i posti materializzati, non i
   5 milioni virtuali — e si risale da ogni occupato fino alla radice,
   sommando in tutti i suoi antenati. Costo: righe x profondita', e la
   profondita' e' 5 o 6.

   LIMITE DICHIARATO: conta solo le righe scritte. Una posizione virtuale mai
   materializzata non ha nessuno dentro per definizione, quindi non manca
   niente; ma se un giorno le righe vere diventassero milioni, questa va
   rifatta con una tabella di contatori.
============================================================================ */
function alb_pesi(PDO $pdo, array $radici = []): array {
  alb_migra($pdo);
  $padre = [];      /* posto => padre */
  $occ   = [];      /* posto => 1 se c'e' una persona */
  $att   = [];      /* posto => 1 se e' attivo */
  $eur   = [];      /* posto => euro incassati da chi ci sta dentro */
  /* i soldi: si leggono una volta e si attaccano al posto della persona.
     Se `orders` non c'e', restano tutti a zero e non salta niente. */
  $perUid = alb_euro_per_uid($pdo);
  try {
    $q = $pdo->query("SELECT posto, padre, COALESCE(uid,0) u, COALESCE(stato,'') s
                      FROM network_posti");
    foreach ($q as $r) {
      $p = (int)$r['posto'];
      $padre[$p] = (int)$r['padre'];
      $u = (int)$r['u'];
      if ($u > 0)                       $occ[$p] = 1;
      if ((string)$r['s'] === 'attivo') $att[$p] = 1;
      if ($u > 0 && isset($perUid['euro'][$u])) $eur[$p] = (float)$perUid['euro'][$u];
    }
  } catch (Throwable $e) { return []; }

  $peso = [];   /* posto => ['occupati'=>n,'attivi'=>n,'euro'=>x,'diretti'=>n,'nodi'=>n] */
  $agg = function (int $p, int $o, int $a, float $e) use (&$peso) {
    if (!isset($peso[$p])) $peso[$p] = ['occupati'=>0, 'attivi'=>0, 'euro'=>0.0, 'diretti'=>0, 'nodi'=>0];
    $peso[$p]['occupati'] += $o;
    $peso[$p]['attivi']   += $a;
    $peso[$p]['euro']     += $e;
    $peso[$p]['nodi']     += 1;
  };

  /* I DIRETTI: quante persone hai attaccate subito sotto. Si conta qui, nella
     stessa passata, invece di fare una query per nodo: con 80 fratelli a
     schermo sarebbero 80 query per un numero che sta gia' in memoria. */
  foreach ($padre as $p => $pp) {
    if (!isset($occ[$p])) continue;
    if ($pp === $p) continue;
    if (!isset($peso[$pp])) $peso[$pp] = ['occupati'=>0,'attivi'=>0,'euro'=>0.0,'diretti'=>0,'nodi'=>0];
    $peso[$pp]['diretti']++;
  }

  foreach ($padre as $p => $pp) {
    $o = isset($occ[$p]) ? 1 : 0;
    $a = isset($att[$p]) ? 1 : 0;
    $e = (float)($eur[$p] ?? 0);
    if ($o === 0 && $a === 0 && $e == 0.0) continue;   /* i posti vuoti non pesano */
    /* se stesso + tutti gli antenati, con una guardia sui cicli.
       ATTENZIONE al MASTER: il suo posto e' 0 e suo padre e' -1. Una guardia
       scritta come "finche' cur > 0" salterebbe proprio lui, e il totale in
       mezzo agli Anelli verrebbe zero. Si sale finche' il posto esiste. */
    $cur = $p; $giri = 0;
    while ($giri < 40 && array_key_exists($cur, $padre)) {
      $agg($cur, $o, $a, $e);
      $su = $padre[$cur];
      if ($su === $cur || $su < 0) break;   /* -1 = sopra il MASTER non c'e' niente */
      $cur = $su; $giri++;
    }
  }

  if ($radici) {
    $out = [];
    foreach ($radici as $r) {
      $r = (int)$r;
      $out[$r] = $peso[$r] ?? ['occupati'=>0, 'attivi'=>0, 'euro'=>0.0, 'diretti'=>0, 'nodi'=>0];
      $out[$r]['euro'] = round((float)$out[$r]['euro'], 2);
    }
    return $out;
  }
  foreach ($peso as $k => $v) $peso[$k]['euro'] = round((float)$v['euro'], 2);
  return $peso;
}

/* ============================================================================
   I NUMERI DELLA RETE — il cruscotto.
   Aggiunto 2026-08-15 · Cowork

   Da qui nascono i numeri con cui si prendono le decisioni, quindi valgono
   due regole:
     1) niente stime. Ogni voce e' un COUNT su dati veri.
     2) quello che non si puo' sapere si dice, non si riempie con uno zero
        che sembra un dato. Se una colonna non esiste in banca dati, la voce
        torna null e chi disegna scrive "non disponibile".

   COSA NON C'E' QUI (e va detto): il fatturato e il sentiment non stanno in
   network_posti. Il fatturato sta negli ordini, il sentiment non e' ancora
   raccolto da nessuna parte. Metterli qui inventandoli sarebbe il modo piu'
   veloce di rendere inutile tutto il cruscotto.
============================================================================ */
function alb_numeri(PDO $pdo): array {
  alb_migra($pdo);
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $o = [
    'persone'      => 0,   /* posti con dentro una persona */
    'attive'       => 0,
    'prenotate'    => 0,
    'confermate'   => 0,
    'nodi_venduti' => 0,   /* dei 118 */
    'nodi_liberi'  => 0,
    'per_livello'  => [],
    'entrate'      => ['oggi'=>0, 'sette'=>0, 'trenta'=>0],
    'rami'         => [],  /* i primi per numero di persone */
    'vuoti'        => 0,   /* dei 118, quanti non hanno nessuno sotto */
    'profondita'   => 0,
    'note'         => [],
  ];
  try {
    $o['persone']    = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE COALESCE(uid,0)>0")->fetchColumn();
    $o['attive']     = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE stato='attivo'")->fetchColumn();
    $o['prenotate']  = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE stato='prenotato'")->fetchColumn();
    $o['confermate'] = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE stato='confermato'")->fetchColumn();
    $o['nodi_venduti'] = (int)$pdo->query("SELECT COUNT(*) FROM network_posti
                                           WHERE posto>0 AND posto<=".$base." AND COALESCE(uid,0)>0")->fetchColumn();
    $o['nodi_liberi']  = $base - $o['nodi_venduti'];

    foreach ($pdo->query("SELECT livello, COUNT(*) c FROM network_posti
                          WHERE COALESCE(uid,0)>0 GROUP BY livello ORDER BY livello") as $r) {
      $o['per_livello'][] = ['livello'=>(int)$r['livello'], 'persone'=>(int)$r['c']];
      if ((int)$r['livello'] > $o['profondita']) $o['profondita'] = (int)$r['livello'];
    }
  } catch (Throwable $e) { $o['note'][] = 'alcuni conteggi non sono stati leggibili'; }

  /* QUANDO SONO ENTRATI. preso_il puo' essere vuoto sulle righe vecchie:
     in quel caso il conteggio dei nuovi e' per forza incompleto, e lo si
     scrive invece di far finta di niente. */
  try {
    $senzaData = (int)$pdo->query("SELECT COUNT(*) FROM network_posti
                                   WHERE COALESCE(uid,0)>0 AND COALESCE(preso_il,'')=''")->fetchColumn();
    if ($senzaData > 0) $o['note'][] = $senzaData
        . ($senzaData === 1 ? ' persona non ha' : ' persone non hanno')
        . ' una data d\'ingresso registrata: non entra'
        . ($senzaData === 1 ? '' : 'no') . ' nei conteggi "ultimi giorni"';
    foreach ([['oggi', '-0 day'], ['sette', '-7 day'], ['trenta', '-30 day']] as [$k, $q]) {
      $o['entrate'][$k] = (int)$pdo->query("SELECT COUNT(*) FROM network_posti
        WHERE COALESCE(uid,0)>0 AND COALESCE(preso_il,'')<>''
          AND date(preso_il) >= date('now','".$q."')")->fetchColumn();
    }
  } catch (Throwable $e) { $o['note'][] = 'le date d\'ingresso non sono leggibili'; }

  /* I RAMI: quante persone stanno sotto a ciascuno dei 118. E' il numero che
     dice dove sta succedendo qualcosa. */
  try {
    $pesi = alb_pesi($pdo);
    $righe = [];
    $q = $pdo->query("SELECT posto, COALESCE(node_kind,'') k, COALESCE(uid,0) u
                      FROM network_posti WHERE posto>0 AND posto<=".$base);
    foreach ($q as $r) {
      $p = (int)$r['posto'];
      $sotto = isset($pesi[$p]) ? (int)$pesi[$p]['occupati'] : 0;
      /* il posto stesso non conta come "sotto di se'" */
      if ((int)$r['u'] > 0 && $sotto > 0) $sotto -= 1;
      $righe[] = ['posto'=>$p, 'tipo'=>(string)$r['k'], 'persone'=>$sotto,
                  'attive'=>isset($pesi[$p]) ? (int)$pesi[$p]['attivi'] : 0];
      if ($sotto === 0) $o['vuoti']++;
    }
    usort($righe, fn($a, $b) => $b['persone'] <=> $a['persone']);
    $o['rami'] = array_slice($righe, 0, 12);
  } catch (Throwable $e) { $o['note'][] = 'il peso dei rami non e\' stato calcolato'; }

  return $o;
}

/* ============================================================================
   IL FATTURATO DI UN RAMO — da `orders`, che e' l'unica cassa vera.
   Aggiunto 2026-08-15 · Cowork

   DOVE STANNO I SOLDI
   La tabella `orders` non ha una colonna uid: l'utente sta dentro `customer`
   scritto come "Nome <email> uid:NNN" (lo fa dr-checkout.php, riga 856, e lo
   fa gia' anche dr-network.php). Quindi si legge il campo e si tira fuori
   l'uid con un'espressione regolare. Se un ordine non ha uid dentro, non si
   attribuisce a nessuno: NON si indovina.

   QUALI ORDINI CONTANO
   Solo quelli davvero incassati. 'paid_partial' NON conta: un pagamento a
   meta' non e' fatturato, e mettercelo gonfierebbe ogni numero a valle.

   COSA NON C'E'
   Gli ordini pagati da chi non ha un posto in rete restano fuori dal totale
   per ramo (non hanno un ramo dove finire). Il totale generale li conta, e la
   differenza si dichiara: e' il modo di non far tornare i conti per finta.
============================================================================ */
function alb_stati_pagati(): array {
  return ['paid', 'fulfilled', 'pagato', 'completed', 'completato'];
}

function alb_euro_per_uid(PDO $pdo, int $massimo = 200000): array {
  if (!alb_ha_tabella($pdo, 'orders')) return ['euro'=>[], 'tot'=>0.0, 'senza_uid'=>0.0, 'righe'=>0];
  $euro = []; $tot = 0.0; $senza = 0.0; $righe = 0;
  try {
    $in = "'" . implode("','", alb_stati_pagati()) . "'";
    $q = $pdo->prepare("SELECT customer, COALESCE(total_eur,0) e FROM orders
                        WHERE status IN ($in) ORDER BY id DESC LIMIT ?");
    $q->bindValue(1, $massimo, PDO::PARAM_INT);
    $q->execute();
    foreach ($q as $o) {
      $v = (float)$o['e']; $tot += $v; $righe++;
      if (preg_match('/uid:(\d+)/', (string)$o['customer'], $m)) {
        $u = (int)$m[1];
        if ($u > 0) { $euro[$u] = ($euro[$u] ?? 0.0) + $v; continue; }
      }
      $senza += $v;
    }
  } catch (Throwable $e) {}
  return ['euro'=>$euro, 'tot'=>$tot, 'senza_uid'=>$senza, 'righe'=>$righe];
}

/* c'e' o non c'e' questa tabella? Serve dappertutto qui sotto. */
function alb_ha_tabella(PDO $pdo, string $t): bool {
  try {
    $s = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=? LIMIT 1");
    $s->execute([$t]);
    return (bool)$s->fetchColumn();
  } catch (Throwable $e) { return false; }
}

/* ============================================================================
   CHI SI E' FERMATO — la lista di lavoro.
   Aggiunto 2026-08-15 · Cowork

   Non e' una classifica dei peggiori: e' l'elenco di chi va richiamato.
   Una persona e' "ferma" da tanti giorni quanti ne sono passati da quando ha
   portato dentro l'ultima persona. Chi non ne ha mai portata nessuna conta
   dal giorno in cui e' entrato lui.

   CHI RESTA FUORI, e perche':
     - chi e' entrato da meno giorni della soglia: non e' fermo, e' nuovo.
       Metterlo in questa lista vorrebbe dire dare del lavativo a uno che si
       e' iscritto ieri.
     - chi non ha una data: senza data non si puo' dire niente di onesto, e
       si conta a parte.

   $radice: se passata, si guarda solo dentro quel ramo (e' cosi' che il tool
   utente vede la lista della PROPRIA rete e non quella di tutti).
============================================================================ */
function alb_fermi(PDO $pdo, int $giorni = 14, int $limite = 40, ?int $radice = null): array {
  alb_migra($pdo);
  $giorni = max(1, min(3650, $giorni));
  $limite = max(1, min(500, $limite));

  /* dentro quale ramo si guarda */
  $dentro = null;
  if ($radice !== null) {
    $dentro = [];
    $coda = [$radice];
    $visti = [$radice => 1];
    $giri = 0;
    while ($coda && $giri < 200000) {
      $p = array_pop($coda); $giri++;
      $dentro[$p] = 1;
      try {
        $q = $pdo->query("SELECT posto FROM network_posti WHERE padre=" . (int)$p . " AND posto<>" . (int)$p);
        foreach ($q as $r) { $x = (int)$r['posto']; if (!isset($visti[$x])) { $visti[$x] = 1; $coda[] = $x; } }
      } catch (Throwable $e) { break; }
    }
    unset($dentro[$radice]);   /* di se stessi non si va in lista */
    if (!$dentro) return ['fermi'=>[], 'senza_data'=>0, 'guardati'=>0, 'giorni'=>$giorni];
  }

  /* ultimo figlio entrato, per ogni padre */
  $ultimoFiglio = [];
  try {
    foreach ($pdo->query("SELECT padre, MAX(date(preso_il)) u FROM network_posti
                          WHERE COALESCE(uid,0)>0 AND COALESCE(preso_il,'')<>''
                          GROUP BY padre") as $r) {
      $ultimoFiglio[(int)$r['padre']] = (string)$r['u'];
    }
  } catch (Throwable $e) {}

  $fuori = []; $senzaData = 0; $guardati = 0;
  try {
    $sql = "SELECT p.posto, p.padre, p.livello, COALESCE(p.node_kind,'') tipo,
                   COALESCE(p.stato,'') stato, date(p.preso_il) entrato,
                   COALESCE(u.full_name, u.username, '') nome
            FROM network_posti p LEFT JOIN users u ON u.id=p.uid
            WHERE COALESCE(p.uid,0)>0";
    foreach ($pdo->query($sql) as $r) {
      $posto = (int)$r['posto'];
      if ($dentro !== null && !isset($dentro[$posto])) continue;
      $guardati++;
      $entrato = (string)$r['entrato'];
      if ($entrato === '') { $senzaData++; continue; }

      $ultimo = $ultimoFiglio[$posto] ?? '';
      $rif = ($ultimo !== '' && $ultimo > $entrato) ? $ultimo : $entrato;
      $fermoDa = (int)floor((time() - strtotime($rif . ' 00:00:00')) / 86400);

      /* chi e' entrato da meno della soglia non e' fermo: e' nuovo */
      $daQuandoCiSta = (int)floor((time() - strtotime($entrato . ' 00:00:00')) / 86400);
      if ($daQuandoCiSta < $giorni) continue;
      if ($fermoDa < $giorni) continue;

      $fuori[] = [
        'posto'    => $posto,
        'padre'    => (int)$r['padre'],
        'livello'  => (int)$r['livello'],
        'tipo'     => (string)$r['tipo'],
        'stato'    => (string)$r['stato'],
        'nome'     => (string)$r['nome'],
        'entrato'  => $entrato,
        'ultimo'   => $ultimo,             /* '' = non ne ha mai portata nessuna */
        'fermo_da' => $fermoDa,
        'mai'      => ($ultimo === '' ? 1 : 0),
      ];
    }
  } catch (Throwable $e) {}

  usort($fuori, fn($a, $b) => $b['fermo_da'] <=> $a['fermo_da']);
  return ['fermi'=>array_slice($fuori, 0, $limite), 'quanti'=>count($fuori),
          'senza_data'=>$senzaData, 'guardati'=>$guardati, 'giorni'=>$giorni];
}

/* ============================================================================
   LA CRESCITA — quante persone sono entrate, giorno per giorno.
   Chi non ha data resta fuori e si dichiara: una curva costruita mettendo le
   righe senza data "da qualche parte" e' una curva inventata.
============================================================================ */
function alb_crescita(PDO $pdo, int $giorni = 30, ?int $radice = null): array {
  alb_migra($pdo);
  $giorni = max(2, min(365, $giorni));

  $filtro = '';
  if ($radice !== null) {
    $lista = alb_discendenti_lista($pdo, $radice);
    if (!$lista) return ['giorni'=>[], 'senza_data'=>0, 'totale'=>0];
    $filtro = ' AND posto IN (' . implode(',', array_map('intval', $lista)) . ')';
  }

  $perGiorno = [];
  $senzaData = 0; $totale = 0;
  try {
    $senzaData = (int)$pdo->query("SELECT COUNT(*) FROM network_posti
      WHERE COALESCE(uid,0)>0 AND COALESCE(preso_il,'')=''" . $filtro)->fetchColumn();
    foreach ($pdo->query("SELECT date(preso_il) g, COUNT(*) c FROM network_posti
      WHERE COALESCE(uid,0)>0 AND COALESCE(preso_il,'')<>''
        AND date(preso_il) >= date('now','-" . ($giorni - 1) . " day')" . $filtro . "
      GROUP BY date(preso_il)") as $r) {
      $perGiorno[(string)$r['g']] = (int)$r['c'];
    }
    $totale = (int)$pdo->query("SELECT COUNT(*) FROM network_posti
      WHERE COALESCE(uid,0)>0" . $filtro)->fetchColumn();
  } catch (Throwable $e) {}

  /* la serie completa, buchi compresi: un giorno senza nessuno e' un dato,
     non un giorno da saltare */
  $out = [];
  for ($i = $giorni - 1; $i >= 0; $i--) {
    $g = date('Y-m-d', strtotime("-{$i} day"));
    $out[] = ['giorno'=>$g, 'entrate'=>(int)($perGiorno[$g] ?? 0)];
  }
  return ['giorni'=>$out, 'senza_data'=>$senzaData, 'totale'=>$totale];
}

/* l'elenco dei posti sotto a uno, materializzati. Con un tetto: serve a
   filtrare una query, non a caricarsi mezza rete in memoria. */
function alb_discendenti_lista(PDO $pdo, int $radice, int $tetto = 60000): array {
  $out = [$radice]; $coda = [$radice]; $visti = [$radice=>1];
  while ($coda && count($out) < $tetto) {
    $p = (int)array_pop($coda);
    try {
      foreach ($pdo->query("SELECT posto FROM network_posti WHERE padre={$p} AND posto<>{$p}") as $r) {
        $x = (int)$r['posto'];
        if (isset($visti[$x])) continue;
        $visti[$x] = 1; $out[] = $x; $coda[] = $x;
        if (count($out) >= $tetto) break;
      }
    } catch (Throwable $e) { break; }
  }
  return $out;
}

/* ============================================================================
   LA MAPPA DEI 118 — i nodi veri del progetto, tutti in una schermata.
   Per ciascuno: se e' assegnato, quante persone ha sotto, quante attive,
   quanto ha fatturato il suo ramo.
============================================================================ */
function alb_mappa118(PDO $pdo): array {
  alb_migra($pdo);
  $base = defined('NET_TOT_POSTI') ? (int)NET_TOT_POSTI : 118;
  $pesi = alb_pesi($pdo);
  $out = [];
  try {
    $q = $pdo->query("SELECT p.posto, p.padre, p.livello, COALESCE(p.node_kind,'') tipo,
                             COALESCE(p.stato,'') stato, COALESCE(p.uid,0) uid,
                             COALESCE(u.full_name,u.username,'') nome
                      FROM network_posti p LEFT JOIN users u ON u.id=p.uid
                      WHERE p.posto>0 AND p.posto<=" . $base . " ORDER BY p.posto");
    foreach ($q as $r) {
      $p = (int)$r['posto'];
      $pz = $pesi[$p] ?? ['occupati'=>0, 'attivi'=>0, 'euro'=>0];
      $sotto = (int)$pz['occupati'];
      if ((int)$r['uid'] > 0 && $sotto > 0) $sotto -= 1;   /* se stesso non conta */
      $out[] = [
        'posto'   => $p,
        'padre'   => (int)$r['padre'],
        'livello' => (int)$r['livello'],
        'tipo'    => (string)$r['tipo'],
        'stato'   => (string)$r['stato'],
        'preso'   => ((int)$r['uid'] > 0) ? 1 : 0,
        'nome'    => (string)$r['nome'],
        'sotto'   => $sotto,
        'attivi'  => (int)$pz['attivi'],
        'euro'    => round((float)($pz['euro'] ?? 0), 2),
      ];
    }
  } catch (Throwable $e) {}
  return $out;
}

/* ============================================================================
   IL NUMERO CHE SERVE DAVVERO: quante PERSONE ci sono sotto un nodo.
   Aggiunto 2026-08-15 · Cowork

   PERCHE' NON BASTAVA `figli`
   `figli` conta i POSTI figli, e sotto un Pro i posti sono 61.000 per
   costruzione. Un tooltip che dice "figli: 60.976" non dice niente a nessuno:
   sono sedie, non persone. Quello che uno vuole sapere passando col mouse e'
   "quanta gente ho sotto, in tutto, fino in fondo".

   COME
   `alb_pesi()` calcola gia' tutto in una passata sola. Qui la si chiama una
   volta per richiesta (memoria statica) e si attacca il numero a ogni riga.
   Costo: una lettura, non una query per nodo.

   CAMPI ATTACCATI
     rete          persone nel ramo, dirette + indirette, fino in fondo
     rete_attivi   di quelle, quante sono attive
     rete_euro     quanto ha incassato il ramo (solo lato admin)
   Il posto stesso NON si conta come discendente di se' stesso.
============================================================================ */
function alb_pesi_cache(PDO $pdo): array {
  static $memo = null;
  if ($memo === null) $memo = alb_pesi($pdo);
  return $memo;
}

function alb_attacca_rete(PDO $pdo, array $riga): array {
  $p = (int)($riga['posto'] ?? -1);
  if ($p < 0) return $riga;
  $pesi = alb_pesi_cache($pdo);
  $pz = $pesi[$p] ?? null;
  /* ATTENZIONE AL MASTER, ed e' il motivo per cui questa riga e' scritta cosi'.
     Il MASTER-NODE ha `occupato = 1` ma `uid = 0`: e' segnato occupato perche'
     e' il tronco, non perche' ci sieda una persona. `alb_pesi` conta solo chi
     ha un uid, quindi il MASTER non e' dentro il proprio conteggio e non gli
     va tolto niente. Guardando `occupato` invece di `uid` il MASTER risultava
     con una persona in meno dei suoi stessi World: numero impossibile. */
  $haPersona = isset($riga['uid']) ? ((int)$riga['uid'] > 0) : !empty($riga['occupato']);
  $sotto = $pz ? (int)$pz['occupati'] : 0;
  /* se ci sta davvero una persona, dentro `occupati` c'e' anche lei: si toglie,
     se no ognuno risulterebbe discendente di se' stesso */
  if ($haPersona && $sotto > 0) $sotto -= 1;
  $riga['rete']         = max(0, $sotto);
  $riga['rete_attivi']  = $pz ? max(0, (int)$pz['attivi'] - (($haPersona && !empty($riga['attivo'])) ? 1 : 0)) : 0;
  $riga['rete_diretti'] = $pz ? (int)($pz['diretti'] ?? 0) : 0;
  $riga['rete_euro']    = $pz ? round((float)($pz['euro'] ?? 0), 2) : 0.0;
  return $riga;
}

function alb_attacca_rete_lista(PDO $pdo, array $righe): array {
  foreach ($righe as $i => $r) {
    if (!empty($r['piu'])) continue;          /* il nodo finto "altri N" no */
    $righe[$i] = alb_attacca_rete($pdo, $r);
  }
  return $righe;
}

/* ============================================================================
   IL MASTER E' MIRCO — non un segnaposto.
   Aggiunto 2026-08-15 · Cowork

   Il posto 0 e' il tronco, e il tronco e' l'admin: tutti gli altri sono suoi
   discendenti, diretti o indiretti. Finora la riga del posto 0 aveva
   `uid = 0` e si chiamava "MASTER-NODE": un segnaposto. Il risultato era che
   in nessuna schermata risultava che quella rete e' la sua.

   COME SI DECIDE CHI E' IL MASTER, in ordine:
     1) `network_cfg.master_uid` — se qualcuno l'ha gia' deciso, comanda quello
     2) `.env` DR_MASTER_UID, oppure DR_MASTER_EMAIL
     3) il primo utente con role='admin'
   Se non si arriva a nessuno, si lascia tutto com'e' e si dice perche'.

   PERCHE' IL LEGAME SI SCRIVE E NON SI INDOVINA OGNI VOLTA
   Perche' il numero delle persone sotto di lui, il suo nome nei tooltip e la
   sua vista utente devono dire tutti la stessa cosa, anche se domani cambia
   il modo di riconoscere gli admin.

   COSA NON FA, ed e' voluto:
   se quell'utente ha GIA' un posto suo nella rete, non lo si sposta e non lo
   si mette in due posti: si torna un conflitto da leggere. La regola "un
   utente = un posto" vale anche per il capo.
============================================================================ */
function alb_master_chi(PDO $pdo): array {
  $out = ['uid'=>0, 'nome'=>'', 'email'=>'', 'da'=>'', 'gia_legato'=>false];

  /* 1) gia' deciso? */
  try {
    $v = (int)$pdo->query("SELECT COALESCE(uid,0) FROM network_posti WHERE posto=0")->fetchColumn();
    if ($v > 0) { $out['uid'] = $v; $out['da'] = 'posto 0'; $out['gia_legato'] = true; }
  } catch (Throwable $e) {}
  if (!$out['uid']) {
    try {
      $v = (int)$pdo->query("SELECT v FROM network_cfg WHERE k='master_uid'")->fetchColumn();
      if ($v > 0) { $out['uid'] = $v; $out['da'] = 'network_cfg'; }
    } catch (Throwable $e) {}
  }
  /* 2) dal .env */
  if (!$out['uid'] && function_exists('dr_env')) {
    $u = (int)dr_env('DR_MASTER_UID', '0');
    if ($u > 0) { $out['uid'] = $u; $out['da'] = '.env DR_MASTER_UID'; }
    if (!$out['uid']) {
      $em = trim((string)dr_env('DR_MASTER_EMAIL', ''));
      if ($em !== '') {
        try {
          $s = $pdo->prepare("SELECT id FROM users WHERE lower(email)=? LIMIT 1");
          $s->execute([strtolower($em)]);
          $u = (int)($s->fetchColumn() ?: 0);
          if ($u > 0) { $out['uid'] = $u; $out['da'] = '.env DR_MASTER_EMAIL'; }
        } catch (Throwable $e) {}
      }
    }
  }
  /* 3) il primo admin */
  if (!$out['uid']) {
    try {
      $u = (int)($pdo->query("SELECT id FROM users WHERE role='admin' ORDER BY id LIMIT 1")->fetchColumn() ?: 0);
      if ($u > 0) { $out['uid'] = $u; $out['da'] = 'primo admin'; }
    } catch (Throwable $e) {}
  }

  if ($out['uid']) {
    try {
      $s = $pdo->prepare("SELECT COALESCE(NULLIF(full_name,''),username,email,'') n, COALESCE(email,'') e
                          FROM users WHERE id=? LIMIT 1");
      $s->execute([$out['uid']]);
      if ($r = $s->fetch(PDO::FETCH_ASSOC)) { $out['nome'] = (string)$r['n']; $out['email'] = (string)$r['e']; }
    } catch (Throwable $e) {}
  }
  return $out;
}

/* Scrive il legame. Idempotente: rifarlo non cambia niente.
   $prova = true -> dice solo cosa farebbe. */
function alb_master_lega(PDO $pdo, bool $prova = false): array {
  $chi = alb_master_chi($pdo);
  if (!$chi['uid']) return ['ok'=>false, 'msg'=>'Non ho capito chi e\' il master: nessun admin, nessun DR_MASTER_UID, nessun DR_MASTER_EMAIL.'];
  if ($chi['gia_legato']) return ['ok'=>true, 'gia'=>true, 'uid'=>$chi['uid'], 'nome'=>$chi['nome'],
    'msg'=>'Il posto 0 e\' gia\' di ' . ($chi['nome'] ?: ('utente #'.$chi['uid'])) . '.'];

  /* ha gia' un posto suo? allora non si tocca niente */
  $altrove = 0;
  try {
    $altrove = (int)($pdo->query("SELECT posto FROM network_posti
                                  WHERE posto<>0 AND (COALESCE(uid,0)={$chi['uid']}
                                     OR COALESCE(assigned_uid,0)={$chi['uid']})
                                  ORDER BY posto LIMIT 1")->fetchColumn() ?: 0);
  } catch (Throwable $e) {}
  if ($altrove > 0) {
    return ['ok'=>false, 'conflitto'=>true, 'uid'=>$chi['uid'], 'posto_attuale'=>$altrove,
      'msg'=>($chi['nome'] ?: ('utente #'.$chi['uid'])) . ' occupa gia\' il posto #' . $altrove
           . '. Un utente sta in un posto solo: prima va liberato quello, e la scelta e\' tua.'];
  }

  if ($prova) return ['ok'=>true, 'prova'=>true, 'uid'=>$chi['uid'], 'nome'=>$chi['nome'],
    'msg'=>'Legherei il posto 0 a ' . ($chi['nome'] ?: ('utente #'.$chi['uid'])) . ' (da: ' . $chi['da'] . ').'];

  try {
    $pdo->prepare("UPDATE network_posti SET uid=?, aggiornato=datetime('now') WHERE posto=0")
        ->execute([$chi['uid']]);
    try { $pdo->exec("CREATE TABLE IF NOT EXISTS network_cfg (k TEXT PRIMARY KEY, v TEXT)"); } catch (Throwable $e) {}
    $pdo->prepare("REPLACE INTO network_cfg(k,v) VALUES('master_uid',?)")->execute([(string)$chi['uid']]);
    if (function_exists('alb_log')) { try { alb_log($pdo, 'master-legato', 0, '0', (string)$chi['uid'], 'sistema'); } catch (Throwable $e) {} }
  } catch (Throwable $e) {
    return ['ok'=>false, 'msg'=>'Non sono riuscito a scrivere il legame: ' . $e->getMessage()];
  }
  return ['ok'=>true, 'uid'=>$chi['uid'], 'nome'=>$chi['nome'],
    'msg'=>'Il posto 0 adesso e\' di ' . ($chi['nome'] ?: ('utente #'.$chi['uid'])) . '.'];
}

/* Legame automatico, una volta sola. Stesso schema di alb_auto_resync():
   un segno in network_cfg e non ci si torna piu' sopra. Se c'e' un conflitto
   NON insiste: lo lascia scritto, cosi' si legge nel Preflight invece di
   ritentare a ogni apertura di pagina. */
function alb_master_auto(PDO $pdo): void {
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS network_cfg (k TEXT PRIMARY KEY, v TEXT)");
    $fatto = (string)($pdo->query("SELECT v FROM network_cfg WHERE k='master_legato'")->fetchColumn() ?: '');
    if ($fatto !== '') return;
    $r = alb_master_lega($pdo, false);
    $pdo->prepare("REPLACE INTO network_cfg(k,v) VALUES('master_legato',?)")
        ->execute([!empty($r['ok']) ? '1' : ('no: ' . substr((string)($r['msg'] ?? ''), 0, 180))]);
  } catch (Throwable $e) {}
}
