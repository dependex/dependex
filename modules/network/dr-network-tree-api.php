<?php
/* ============================================================================
   DR-NETWORK-TREE-API — endpoint JSON di Albero e Stella.
   Destino Randagio · riscritto 2026-08-15 · Cowork

   COSA E' CAMBIATO E PERCHE'
   Prima leggeva `network_nodes` (solo chi ha gia' comprato): sul DB reale
   quella tabella ha 1 riga, quindi l'albero riceveva zero figli e non apriva
   niente. Ora legge la struttura VERA `network_posti` tramite _dr-albero-lib.php
   (1 Master + 9 World + 27 National + 82 Pro + le posizioni utente, fino a
   NET_USER_POSTI = 5.000.000).

   AZIONI (?azione=...)
     vista      vista iniziale: Master + 9 World + i loro National (1 sola chiamata)
     figli      figli di un posto, a pagine     &posto=N&limit=200&offset=0
     nodo       dettaglio + KPI + catena upline &posto=N
     ramo       discendenti totali del ramo     &posto=N   (pesante: su richiesta)
     cerca      ricerca + percorso dalla radice &q=testo
     riepilogo  contatori globali
     --- scrittura: SOLO POST ---
     sposta     &posto=N&padre=M     riaggancia un nodo (anti-ciclo)
     assegna    &posto=N&uid=U&stato=attivo|prenotato
     libera     &posto=N
     resync     riallinea padre/livello alla topologia a stella

   GATE: sessione admin OPPURE ?key=DR_ADMIN_KEY (stesso pattern delle altre
   pagine di rete). Le azioni di SCRITTURA richiedono POST: un link o un
   prefetch del browser non puo' spostare un nodo per sbaglio.
============================================================================ */
require_once __DIR__ . '/../db.php';
@require_once __DIR__ . '/../dr-env.php';
require_once __DIR__ . '/_dr-albero-lib.php';

$pdo = $GLOBALS['pdo'] ?? ($pdo ?? null);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
if (!($pdo instanceof PDO)) { http_response_code(500); exit('{"ok":false,"err":"no-db"}'); }

if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
$KEY = function_exists('dr_env') ? (string)dr_env('DR_ADMIN_KEY', '') : '';
$admin = (($_SESSION['role'] ?? '') === 'admin');
$conKey = ($KEY !== '' && isset($_REQUEST['key']) && hash_equals($KEY, (string)$_REQUEST['key']));
if (!($admin || $conKey)) { http_response_code(403); exit('{"ok":false,"err":"forbidden"}'); }

/* la struttura si allinea da sola, una volta sola: vedi alb_auto_resync() */
if (function_exists('alb_auto_resync')) { try { alb_auto_resync($pdo); } catch (Throwable $e) {} }

$azione = (string)($_REQUEST['azione'] ?? 'vista');
$posto  = isset($_REQUEST['posto']) ? (int)$_REQUEST['posto'] : 0;
$post   = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

/* Spostare una persona che ha mezza rete sotto significa riscrivere il livello
   di centinaia di migliaia di righe: misurato 5,3 s su un ramo da 548.801
   posizioni. Col limite di tempo di default PHP taglierebbe l'operazione a
   meta', lasciando l'albero in uno stato incoerente. Le scritture non hanno
   limite di tempo; le letture restano com'erano (sono tutte sotto i 250 ms). */
if ($post) { @set_time_limit(0); @ignore_user_abort(true); }

function api_out($a) { echo json_encode($a, JSON_UNESCAPED_UNICODE); exit; }
function api_err($msg, $code = 400) { http_response_code($code); api_out(['ok'=>false, 'err'=>$msg]); }

try {
  switch ($azione) {

    /* ---------------------------------------------------------- LETTURA */
    /* A OGNI NODO SI ATTACCA QUANTA GENTE HA SOTTO.
       Cosi' il numero e' gia' li' quando il mouse ci passa sopra: non serve
       una seconda chiamata per nodo, che con 80 fratelli vorrebbe dire 80
       richieste. Costo: una lettura sola per richiesta. */
    case 'vista': {
      $v = alb_vista_iniziale($pdo);
      /* la vista iniziale annida i figli sotto 'children' (non '_figli': quello
         e' il nome che usa il disegno nel browser). Sbagliare chiave qui vuol
         dire tooltip a zero su tutti i World, ed e' successo. */
      if (!empty($v['albero'])) {
        $giu = function (&$n) use (&$giu, $pdo) {
          $n = alb_attacca_rete($pdo, $n);
          foreach (['children', '_figli'] as $k) {
            if (!empty($n[$k]) && is_array($n[$k])) {
              foreach ($n[$k] as $i => $f) { $giu($f); $n[$k][$i] = $f; }
            }
          }
        };
        $giu($v['albero']);
      }
      api_out($v);
    }

    case 'figli':
      $lim = isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 200;
      /* &contiene=N -> invece dell'offset dato, torna la pagina in cui sta
         DAVVERO il figlio N. Serve per raggiungere un nodo che sta in fondo a
         decine di migliaia di fratelli senza sfogliare tutte le pagine. */
      $off = isset($_REQUEST['offset']) ? (int)$_REQUEST['offset'] : 0;
      if (!empty($_REQUEST['contiene'])) {
        $off = alb_pagina_di($pdo, $posto, (int)$_REQUEST['contiene'], $lim);
      }
      $ff = alb_figli($pdo, $posto, $lim, $off, !empty($_REQUEST['solo_occupati']));
      if (!empty($ff['figli'])) $ff['figli'] = alb_attacca_rete_lista($pdo, $ff['figli']);
      api_out($ff);

    case 'nodo': {
      $nn = alb_nodo($pdo, $posto);
      if (!empty($nn['nodo'])) $nn['nodo'] = alb_attacca_rete($pdo, $nn['nodo']);
      api_out($nn);
    }

    case 'ramo':
      api_out(alb_ramo($pdo, $posto));

    /* IL PESO DEI RAMI — quante persone (non quante posizioni) stanno sotto
       ai posti chiesti. Serve alla vista Anelli: senza, ogni Pro peserebbe
       uguale e il disegno non direbbe niente. &posti=1,2,3 oppure niente
       per averli tutti. */
    case 'pesi': {
      $lista = trim((string)($_REQUEST['posti'] ?? ''));
      $r = $lista === '' ? []
         : array_values(array_filter(array_map('intval', explode(',', $lista)), fn($x) => $x >= 0));
      /* >= 0 e non > 0: il MASTER e' il posto zero, e senza di lui il totale
         della rete verrebbe vuoto proprio nel punto in cui si guarda. */
      api_out(['ok'=>true, 'pesi'=>alb_pesi($pdo, $r)]);
    }

    case 'cerca':
      api_out(alb_cerca($pdo, (string)($_REQUEST['q'] ?? ''),
                        isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 20));

    /* le posizioni prese di recente: dopo un caricamento di massa i nuovi
       stanno al quarto livello e "non si vedono" senza sapere dove cercare */
    case 'ultimi':
      api_out(['ok'=>true, 'ultimi'=>alb_ultimi_arrivati($pdo,
        isset($_REQUEST['quanti']) ? (int)$_REQUEST['quanti'] : 25)]);

    /* IL CRUSCOTTO — i numeri da cui nascono le decisioni. Solo COUNT su
       dati veri; quello che non si sa esce come nota, non come zero. */
    case 'numeri':
      api_out(['ok'=>true, 'numeri'=>alb_numeri($pdo)]);

    /* CHI SI E' FERMATO — la lista di lavoro, non una classifica dei peggiori */
    case 'fermi':
      api_out(['ok'=>true, 'dati'=>alb_fermi($pdo,
        isset($_REQUEST['giorni']) ? (int)$_REQUEST['giorni'] : 14,
        isset($_REQUEST['limite']) ? (int)$_REQUEST['limite'] : 40)]);

    /* LA CRESCITA — quante persone al giorno */
    case 'crescita':
      api_out(['ok'=>true, 'dati'=>alb_crescita($pdo,
        isset($_REQUEST['giorni']) ? (int)$_REQUEST['giorni'] : 30)]);

    /* LA MAPPA DEI 118 */
    case 'mappa':
      api_out(['ok'=>true, 'mappa'=>alb_mappa118($pdo)]);

    /* IL FATTURATO, in chiaro: totale, quanto e' attribuito a una persona e
       quanto no. La differenza si dichiara, non si nasconde. */
    case 'cassa': {
      $c = alb_euro_per_uid($pdo);
      api_out(['ok'=>true, 'totale'=>round($c['tot'],2),
               'senza_persona'=>round($c['senza_uid'],2),
               'ordini'=>$c['righe'], 'stati'=>alb_stati_pagati()]);
    }

    case 'riepilogo':
      api_out(['ok'=>true, 'riepilogo'=>alb_riepilogo($pdo)]);

    /* Chi occupa piu' di un posto: con la regola "un utente = un posto" questo
       elenco deve essere vuoto. Se non lo e', sono doppioni da sistemare. */
    case 'doppioni':
      api_out(alb_doppioni($pdo));

    /* Tutti i codici di una persona: il suo personale + quello del suo nodo. */
    case 'sic':
      api_out(['ok'=>true, 'sic'=>alb_sic_utente($pdo, (int)($_REQUEST['uid'] ?? 0))]);

    /* Chi e' il proprietario di questo codice di invito? Funziona sia col SIC
       personale sia col SIC di un nodo occupato. &codice=... */
    case 'ref':
      api_out(alb_risolvi_ref($pdo, (string)($_REQUEST['codice'] ?? '')));

    /* elenco di TUTTE le persone piazzate, con ricerca e a pagine: con 5
       milioni di posizioni trovare una persona girando l'albero non si puo'. */
    case 'utenti':
      api_out(alb_utenti(
        $pdo, (string)($_REQUEST['q'] ?? ''),
        isset($_REQUEST['limit'])  ? (int)$_REQUEST['limit']  : 100,
        isset($_REQUEST['offset']) ? (int)$_REQUEST['offset'] : 0
      ));

    /* -------------------------------------------------------- SCRITTURA */
    case 'sposta':
      if (!$post) api_err('Questa azione richiede POST.', 405);
      api_out(alb_sposta($pdo, $posto, (int)($_REQUEST['padre'] ?? -1)));

    case 'assegna':
      if (!$post) api_err('Questa azione richiede POST.', 405);
      api_out(alb_assegna($pdo, $posto, (int)($_REQUEST['uid'] ?? 0),
                          (string)($_REQUEST['stato'] ?? 'attivo')));

    /* SPOSTA UNA PERSONA con tutta la sua struttura appresso.
       modo=occupa   la persona lascia 'da' e va a occupare 'a' (che dev'essere
                     libero); i suoi figli diretti la seguono sotto 'a'
       modo=scambia  due persone si scambiano di posto, ognuna con la sua gente
       modo=aggancia il posto 'da' (persona + ramo com'e') va appeso sotto 'a' */
    case 'sposta_utente':
      if (!$post) api_err('Questa azione richiede POST.', 405);
      api_out(alb_sposta_utente(
        $pdo,
        isset($_REQUEST['da']) ? (int)$_REQUEST['da'] : $posto,
        (int)($_REQUEST['a'] ?? -1),
        (string)($_REQUEST['modo'] ?? 'occupa')
      ));

    /* moltiplicatore rewards di un nodo (X1..X8, solo i primi 118) */
    case 'boost':
      if (!$post) api_err('Questa azione richiede POST.', 405);
      if (is_file(__DIR__ . '/dr-boost.php')) require_once __DIR__ . '/dr-boost.php';
      if (!function_exists('dr_boost_imposta')) api_err('Modulo rewards non disponibile.', 500);
      api_out(dr_boost_imposta($pdo, $posto, (int)($_REQUEST['valore'] ?? 0), (string)($_REQUEST['nota'] ?? '')));

    case 'libera':
      if (!$post) api_err('Questa azione richiede POST.', 405);
      api_out(alb_libera($pdo, $posto));

    case 'resync':
      if (!$post) api_err('Questa azione richiede POST.', 405);
      api_out(alb_resync_topologia($pdo, !empty($_REQUEST['anche_occupati'])));

    default:
      api_err('Azione sconosciuta: ' . $azione);
  }
} catch (Throwable $e) {
  http_response_code(500);
  api_out(['ok'=>false, 'err'=>substr($e->getMessage(), 0, 250)]);
}
