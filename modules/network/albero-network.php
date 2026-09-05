<?php
/* ============================================================================
   ALBERO NETWORK — navigabile davvero, sulla struttura VERA.
   Destino Randagio · riscritto 2026-08-15 · Cowork

   IL BUG CHE QUESTA RISCRITTURA CHIUDE
   La versione precedente leggeva `network_nodes` (solo chi ha gia' comprato).
   Sul DB reale di Mirco quella tabella ha 1 riga: l'albero chiedeva "i figli
   del Master" e riceveva zero, quindi non apriva nulla. In piu' i 118 posti in
   `network_posti` avevano ancora la topologia ternaria vecchia (3 World sotto
   il Master invece di 9): 115 posti su 118 col padre sbagliato.
   Ora si legge `network_posti` via dr-network-tree-api.php, e se la topologia
   e' disallineata la pagina lo dice e offre il riallineamento (che non tocca
   mai posti venduti ne' spostamenti fatti a mano).

   COSA FA
   - apre su MASTER + 9 World + i 27 National (1 sola chiamata, 37 nodi)
   - ogni ramo si apre a richiesta: click sul nodo, o zoom-in verso di lui
   - click su un nodo -> pannello destro con dati, KPI, attivo/passivo, numeri
   - trascina un nodo su un altro -> lo riaggancia li' (con anti-ciclo)
   - assegna/libera un utente su una posizione
   - ricerca per posto, SIC, nome o email: apre da sola la strada per arrivarci
   - pronto per 5.000.000 di posizioni: un Pro con 61.000 figli mostra prima
     gli occupati, poi le libere a pagine da 200 (mai tutte insieme)

   Gate: sessione admin OPPURE ?key=DR_ADMIN_KEY.
============================================================================ */
require_once __DIR__ . '/../db.php';
@require_once __DIR__ . '/../dr-env.php';
require_once __DIR__ . '/_dr-albero-lib.php';

$pdo = $GLOBALS['pdo'] ?? ($pdo ?? null);
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
$KEY   = function_exists('dr_env') ? (string)dr_env('DR_ADMIN_KEY', '') : '';
$admin = (($_SESSION['role'] ?? '') === 'admin');
$conKey = ($KEY !== '' && isset($_GET['key']) && hash_equals($KEY, (string)$_GET['key']));
if (!($admin || $conKey)) { http_response_code(403); header('Location: ../accedi.php'); exit; }

/* la chiave viaggia con le chiamate API solo se e' cosi' che sei entrato */
$APIKEY = $conKey ? (string)$_GET['key'] : '';

/* la struttura si allinea da sola, una volta sola: vedi alb_auto_resync() */
if ($pdo instanceof PDO && function_exists('alb_auto_resync')) { try { alb_auto_resync($pdo); } catch (Throwable $e) {} }
/* il posto 0 e' l'admin: legame scritto una volta sola (alb_master_auto) */
if ($pdo instanceof PDO && function_exists('alb_master_auto')) { try { alb_master_auto($pdo); } catch (Throwable $e) {} }

$RIEP = ['posti'=>0,'occupati'=>0,'attivi'=>0,'liberi'=>0,'world'=>0,'national'=>0,'pro'=>0,'user'=>0];
$WORLD_SOTTO_MASTER = 0;
$TOPOLOGIA_OK = true;
if ($pdo instanceof PDO) {
  try {
    $RIEP = alb_riepilogo($pdo);
    $WORLD_SOTTO_MASTER = (int)$pdo->query("SELECT COUNT(*) FROM network_posti WHERE padre=0 AND posto>0")->fetchColumn();
    /* attesi 9 World sotto il Master: se sono meno, la topologia e' vecchia */
    $TOPOLOGIA_OK = ($WORLD_SOTTO_MASTER >= 9);
  } catch (Throwable $e) {}
}
$fmt = function ($n) { return number_format((float)$n, 0, ',', '.'); };
?><!DOCTYPE html>
<html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Albero Network — Destino Randagio</title>
<style>
  /* NERO, BIANCO, ORO — i colori di Destino Randagio.
     Il fondo non e' nero assoluto ma quasi: sul nero puro l'oro sbatte e
     stanca gli occhi dopo due minuti; su un nero appena caldo l'oro respira.
     I tipi di nodo non usano piu' cinque colori a caso: sono una scala d'oro
     che schiarisce salendo di grado (Master quasi bianco, Pro oro scuro) e
     bianco per i 5 milioni di posizioni normali. Il colore dice il rango. */
  :root{
    --bg:#0a0908; --carta:#141210; --carta2:#1c1815;
    --inchiostro:#f2e9d8; --oro:#d9b45a; --oro-chiaro:#f2dba4; --oro-scuro:#8a6f38;
    --bordo:rgba(217,180,90,.20); --tenue:#a3907170; --tenue:#a99a80; --spento:#6d6153;
    --master:#fff4cf; --world:#ffd166; --national:#dcaa3c; --pro:#b3862c; --user:#e6e0d4;
    --vivo:#d9b45a; --fermo:#4b4238;
    --rosso:#e0644f;
  }
  *{box-sizing:border-box}
  html,body{margin:0;height:100%;overflow:hidden;background:var(--bg);color:var(--inchiostro);
            font-family:Georgia,'Times New Roman',serif}
  #scena{position:absolute;inset:0}
  /* la luce viene dal basso, da dove parte il tronco: e' il punto da cui
     nasce tutto, ed e' l'unica cosa che illumina la scena */
  svg{display:block;width:100%;height:100%;cursor:grab;
      background:
        radial-gradient(115% 78% at 50% 106%, rgba(217,180,90,.17) 0%, rgba(217,180,90,.055) 34%, rgba(217,180,90,0) 66%),
        radial-gradient(90% 60% at 50% -10%, rgba(242,233,216,.05) 0%, rgba(0,0,0,0) 60%),
        var(--bg)}
  svg.trascino{cursor:grabbing}

  .bar{position:fixed;top:0;left:0;right:0;display:flex;align-items:center;gap:10px;
       padding:10px 14px;z-index:6;background:linear-gradient(180deg,rgba(10,9,8,.97),rgba(10,9,8,.72));
       backdrop-filter:blur(7px);border-bottom:1px solid var(--bordo);flex-wrap:wrap}
  h1{font-size:15px;margin:0;font-weight:700;letter-spacing:.5px;
     background:linear-gradient(92deg,var(--oro-chiaro),var(--oro) 46%,var(--oro-scuro));
     -webkit-background-clip:text;background-clip:text;color:transparent;
     text-shadow:0 0 26px rgba(217,180,90,.28)}
  .kpi{display:flex;gap:12px;font-size:12px;color:var(--tenue);flex-wrap:wrap}
  .kpi b{color:var(--inchiostro)}
  .right{margin-left:auto;display:flex;gap:6px;flex-wrap:wrap}
  a.btn,button,input,select{background:linear-gradient(180deg,var(--carta2),var(--carta));
       color:var(--inchiostro);border:1px solid var(--bordo);
       border-radius:8px;padding:7px 11px;font-size:12.5px;font-weight:600;cursor:pointer;
       text-decoration:none;font-family:inherit;outline:none;transition:all .16s ease}
  a.btn:hover,button:hover{border-color:var(--oro);color:var(--oro-chiaro);
       box-shadow:0 0 0 1px rgba(217,180,90,.18),0 4px 16px rgba(217,180,90,.14)}
  input::placeholder{color:var(--spento)}
  button:disabled{opacity:.45;cursor:not-allowed}
  input{cursor:text;font-weight:400}
  .cerca{display:flex;gap:5px} .cerca input{width:190px}

  .avviso{position:fixed;top:52px;left:14px;right:14px;z-index:7;
     background:linear-gradient(180deg,rgba(60,44,14,.96),rgba(32,25,12,.96));
     border:1px solid rgba(217,180,90,.5);border-radius:10px;padding:10px 14px;font-size:13px;
     display:flex;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:0 6px 26px rgba(0,0,0,.6)}
  .avviso b{color:var(--oro-chiaro)}
  .avviso button{background:var(--oro);color:#14110c;border-color:var(--oro);font-weight:700}

  .briciole{position:fixed;left:14px;bottom:12px;z-index:5;font-size:12px;color:var(--tenue);
     background:rgba(20,18,16,.86);backdrop-filter:blur(6px);border:1px solid var(--bordo);border-radius:8px;
     padding:6px 10px;max-width:64vw;display:flex;gap:5px;flex-wrap:wrap;align-items:center}
  .briciole a{color:var(--oro);cursor:pointer;text-decoration:none} .briciole a:hover{text-decoration:underline}
  .briciole span.sep{color:var(--spento)}
  .legenda{position:fixed;left:14px;bottom:44px;max-width:430px;z-index:5;display:none;
           font-size:12px;line-height:1.5;color:var(--tenue);background:rgba(10,9,8,.82);
           border:1px solid var(--bordo);border-radius:9px;padding:8px 11px}
  .legenda b{color:var(--oro-chiaro)}
  .stato-vista{position:fixed;left:50%;transform:translateX(-50%);top:96px;z-index:6;display:none;
               font-size:12px;color:#0a0908;background:linear-gradient(180deg,#f2dba4,#d9b45a);
               border-radius:20px;padding:5px 14px;box-shadow:0 6px 22px rgba(0,0,0,.6)}
  .stato-vista b{font-weight:700}
  /* IL CRUSCOTTO — i numeri della rete, aperti sopra il disegno. Sta qui e
     non in un'altra pagina perche' i numeri e la forma della rete si guardano
     insieme: un ramo grosso e un ramo fermo si capiscono guardando tutti e
     due. */
  #numeri{position:fixed;inset:64px 14px 14px auto;width:430px;max-width:94vw;z-index:14;
          overflow:auto;background:linear-gradient(180deg,#1c1815,#100e0b);
          border:1px solid var(--bordo);border-radius:14px;padding:16px 18px;
          box-shadow:0 24px 70px rgba(0,0,0,.75);display:none}
  #numeri.aperto{display:block}
  #numeri h2{margin:0 0 3px;font-size:17px;color:var(--oro);font-weight:400;letter-spacing:.4px}
  #numeri h3{margin:16px 0 6px;font-size:11px;letter-spacing:.1em;text-transform:uppercase;
             color:var(--spento);font-weight:400;border-top:1px solid var(--bordo);padding-top:11px}
  #numeri .chiudi{position:absolute;top:9px;right:11px;background:none;border:none;
                  color:var(--spento);font-size:19px;cursor:pointer}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .cella{background:rgba(10,9,8,.55);border:1px solid var(--bordo);border-radius:10px;padding:9px 11px}
  .cella .v{font-size:22px;color:var(--oro-chiaro);line-height:1.15}
  .cella .n{font-size:11px;color:var(--tenue);margin-top:2px}
  .riga{display:flex;justify-content:space-between;gap:10px;font-size:13px;padding:5px 0;
        border-bottom:1px solid rgba(217,180,90,.10)}
  .riga a{color:var(--oro);cursor:pointer;text-decoration:none}
  .riga a:hover{text-decoration:underline}
  .riga .n{color:var(--oro-chiaro);font-variant-numeric:tabular-nums}
  .barretta{height:5px;border-radius:3px;background:rgba(217,180,90,.14);margin-top:4px;overflow:hidden}
  .barretta i{display:block;height:100%;background:linear-gradient(90deg,#8a6f38,#f2dba4)}
  .nota-n{font-size:12px;color:#e0a08c;background:rgba(224,100,79,.10);
          border:1px solid rgba(224,100,79,.3);border-radius:8px;padding:7px 10px;margin-top:9px;line-height:1.5}
  .franco{font-size:12px;color:var(--spento);margin-top:12px;line-height:1.55;
          border-top:1px solid var(--bordo);padding-top:10px}
  #numeri code{font-family:Consolas,Menlo,monospace;font-size:11px;color:var(--oro-chiaro)}
  button.sg{background:rgba(10,9,8,.6);border:1px solid var(--bordo);color:var(--tenue);
            border-radius:14px;padding:3px 10px;font-size:11.5px;cursor:pointer;font-family:inherit}
  button.sg:hover{border-color:var(--oro);color:var(--oro-chiaro)}
  button.sg.on{background:linear-gradient(180deg,#f2dba4,#d9b45a);color:#0a0908;border-color:var(--oro)}
  .stato-vista button{margin-left:9px;background:rgba(10,9,8,.18);border:1px solid rgba(10,9,8,.32);
                      color:#0a0908;border-radius:12px;padding:2px 9px;font-size:11px;cursor:pointer;
                      font-family:inherit}

  .aiuto{position:fixed;right:14px;bottom:12px;z-index:5;font-size:11px;color:var(--spento);
     background:rgba(20,18,16,.86);backdrop-filter:blur(6px);border:1px solid var(--bordo);border-radius:8px;padding:6px 10px}

  /* pannello del nodo */
  #pan{position:fixed;top:0;right:0;bottom:0;width:370px;max-width:88vw;z-index:8;
       background:linear-gradient(180deg,var(--carta2),var(--carta) 30%);
       border-left:1px solid var(--bordo);box-shadow:-14px 0 44px rgba(0,0,0,.7);
       transform:translateX(102%);transition:transform .22s ease;overflow-y:auto;padding:16px 18px 40px}
  #pan.aperto{transform:none}
  #pan h2{margin:0 0 2px;font-size:17px;color:var(--oro)}
  #pan .sic{font-family:Consolas,Menlo,monospace;font-size:12px;color:var(--tenue);word-break:break-all}
  #pan .chiudi{position:absolute;top:10px;right:12px;padding:3px 9px;font-size:15px;line-height:1}
  .pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:700;
        border:1px solid var(--bordo);margin:2px 4px 2px 0}
  .pill.vivo{background:rgba(217,180,90,.16);color:var(--oro-chiaro);border-color:rgba(217,180,90,.45)}
  .pill.fermo{background:rgba(242,233,216,.05);color:var(--spento)}
  .sez{margin-top:16px;border-top:1px solid var(--bordo);padding-top:11px}
  .sez h3{margin:0 0 8px;font-size:11.5px;letter-spacing:.09em;color:var(--spento);text-transform:uppercase}
  .riga{display:flex;justify-content:space-between;gap:10px;font-size:13px;padding:3px 0}
  .riga span:first-child{color:var(--tenue)} .riga span:last-child{font-weight:600;text-align:right;word-break:break-word}
  .griglia{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:5px}
  .box{background:linear-gradient(180deg,rgba(217,180,90,.07),rgba(217,180,90,.02));
       border:1px solid var(--bordo);border-radius:9px;padding:8px 10px;text-align:center}
  .box b{display:block;font-size:19px;color:var(--oro);line-height:1.25}
  .box small{font-size:10.5px;color:var(--tenue)}
  .azioni{display:flex;gap:6px;flex-wrap:wrap;margin-top:9px}
  .azioni input{width:96px}
  .esito{font-size:12px;margin-top:8px;padding:7px 9px;border-radius:8px;display:none}
  .esito.ok{display:block;background:rgba(217,180,90,.12);border:1px solid rgba(217,180,90,.4);color:var(--oro-chiaro)}
  .esito.ko{display:block;background:rgba(224,100,79,.12);border:1px solid rgba(224,100,79,.4);color:var(--rosso)}

  .risultati{position:fixed;top:46px;left:50%;transform:translateX(-50%);z-index:9;background:var(--carta);
     border:1px solid rgba(217,180,90,.45);border-radius:10px;padding:6px;max-height:52vh;overflow-y:auto;
     min-width:330px;display:none;box-shadow:0 10px 38px rgba(0,0,0,.75)}
  .risultati div{padding:7px 10px;font-size:12.5px;cursor:pointer;border-radius:7px}
  .risultati div:hover{background:rgba(217,180,90,.12);color:var(--oro-chiaro)}
  .risultati .sicm{font-family:Consolas,monospace;font-size:11px;color:var(--tenue)}

  .tip{position:fixed;pointer-events:none;background:rgba(20,18,16,.96);border:1px solid rgba(217,180,90,.45);
       border-radius:8px;padding:7px 10px;font-size:12px;display:none;z-index:10;max-width:250px;
       box-shadow:0 8px 30px rgba(0,0,0,.7)}
  .tip b{color:var(--oro)}
  .carico{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:9;background:rgba(20,18,16,.96);
     border:1px solid rgba(217,180,90,.45);border-radius:10px;padding:11px 20px;font-size:13px;display:none;
     color:var(--oro-chiaro);box-shadow:0 0 40px rgba(217,180,90,.18)}
  /* avviso che SOPRAVVIVE al ridisegno del pannello: dopo un'assegnazione la
     scheda si ricarica e un messaggio scritto dentro il pannello sparirebbe
     prima di essere letto. */
  #toast{position:fixed;left:50%;bottom:56px;transform:translateX(-50%) translateY(14px);z-index:12;
     background:linear-gradient(180deg,#241d12,#14110c);color:var(--oro-chiaro);
     border:1px solid rgba(217,180,90,.4);border-radius:10px;padding:11px 18px;font-size:13.5px;
     box-shadow:0 10px 40px rgba(0,0,0,.8),0 0 30px rgba(217,180,90,.12);
     opacity:0;pointer-events:none;transition:all .2s ease;max-width:70vw}
  #toast.on{opacity:1;transform:translateX(-50%) translateY(0)}
  #toast.ko{background:linear-gradient(180deg,#3a1a14,#1a0d0a);color:#f0b9ad;border-color:rgba(224,100,79,.45)}
  .nodo{cursor:pointer}
  /* modalita' PRENDI E POSA: quando hai una persona "in mano", ogni nodo
     valido come destinazione si illumina, cosi' sai dove puoi posarla. */
  body.in-mano .nodo circle{stroke-dasharray:none}
  body.in-mano svg{cursor:copy}
  .nodo.in-mano circle{stroke:var(--rosso) !important;stroke-width:4px !important}
  .barra-mano{position:fixed;left:50%;top:52px;transform:translateX(-50%);z-index:11;
     background:linear-gradient(180deg,#241d12,#14110c);border:1px solid rgba(217,180,90,.4);
     color:var(--inchiostro);border-radius:10px;padding:9px 16px;font-size:13.5px;display:none;align-items:center;gap:12px;
     box-shadow:0 10px 40px rgba(0,0,0,.8),0 0 30px rgba(217,180,90,.12)}
  .barra-mano.on{display:flex}
  .barra-mano b{color:var(--oro-chiaro)}
  .barra-mano button{background:var(--oro);color:#14110c;border:none;padding:5px 11px;font-size:12.5px;font-weight:700}
  /* elenco persone */
  #utenti{position:fixed;top:0;left:0;bottom:0;width:430px;max-width:92vw;z-index:9;background:var(--carta);
     border-right:1px solid var(--bordo);box-shadow:14px 0 44px rgba(0,0,0,.7);
     transform:translateX(-102%);transition:transform .22s ease;overflow-y:auto;padding:14px 16px 40px}
  #utenti.aperto{transform:none}
  #utenti h2{margin:0 0 8px;font-size:16px;color:var(--oro)}
  #utenti .chiudi{position:absolute;top:10px;right:12px;padding:3px 9px;font-size:15px;line-height:1}
  .ut-riga{border:1px solid var(--bordo);border-radius:9px;padding:9px 11px;margin-bottom:7px;font-size:13px}
  .ut-riga.sel{border-color:rgba(217,180,90,.55);background:rgba(217,180,90,.09)}
  .ut-nome{font-weight:700}
  .ut-sic{font-family:Consolas,monospace;font-size:11px;color:var(--tenue)}
  .ut-az{display:flex;gap:5px;margin-top:7px;flex-wrap:wrap;align-items:center}
  .ut-az input{width:88px;padding:5px 8px;font-size:12px}
  .ut-az button{padding:5px 9px;font-size:12px}
  .ut-cerca{display:flex;gap:5px;margin-bottom:10px}
  .ut-cerca input{flex:1;width:auto}
  .modi{display:flex;gap:4px;margin:7px 0 0;flex-wrap:wrap}
  .modi label{font-size:11.5px;display:flex;align-items:center;gap:3px;background:rgba(242,233,216,.05);
     border:1px solid var(--bordo);border-radius:7px;padding:4px 8px;cursor:pointer}
  .modi input{width:auto;margin:0}
  .nodo.trascinato{opacity:.45}
  .bersaglio circle{stroke:var(--rosso) !important;stroke-width:3.5px !important}
  /* la barra di scorrimento dei pannelli, altrimenti resta bianca sul nero */
  #pan::-webkit-scrollbar,#utenti::-webkit-scrollbar,.risultati::-webkit-scrollbar{width:9px}
  #pan::-webkit-scrollbar-track,#utenti::-webkit-scrollbar-track{background:transparent}
  #pan::-webkit-scrollbar-thumb,#utenti::-webkit-scrollbar-thumb,
  .risultati::-webkit-scrollbar-thumb{background:rgba(217,180,90,.22);border-radius:6px}

  /* ---- MENU COMANDI (☰) — aggiunto 2026-08-15 -------------------------
     Un tool solo con tre viste ha piu' comandi di quanti ne stiano in una
     barra: sopra i sette bottoni la barra va a capo e diventa illeggibile.
     I comandi stanno qui, raggruppati e con la scorciatoia scritta accanto;
     nella barra restano solo la ricerca e le tre viste. */
  #menu{position:fixed;top:0;right:0;bottom:0;width:310px;max-width:86vw;z-index:11;
        background:linear-gradient(180deg,var(--carta2),var(--carta) 30%);
        border-left:1px solid var(--bordo);box-shadow:-14px 0 44px rgba(0,0,0,.75);
        transform:translateX(102%);transition:transform .2s ease;overflow-y:auto;padding:16px 16px 40px}
  #menu.aperto{transform:none}
  #menu h2{margin:0 0 12px;font-size:16px;color:var(--oro-chiaro)}
  #menu .gr{margin-top:16px;border-top:1px solid rgba(217,180,90,.14);padding-top:10px}
  #menu .gr:first-of-type{border-top:0;padding-top:0}
  #menu .gr h3{margin:0 0 7px;font-size:10.5px;letter-spacing:.1em;color:var(--spento);text-transform:uppercase}
  #menu .cmd{display:flex;justify-content:space-between;align-items:center;gap:10px;width:100%;
     background:rgba(242,233,216,.03);border:1px solid var(--bordo);border-radius:9px;
     padding:9px 11px;margin-bottom:6px;font-size:13px;color:var(--inchiostro);
     font-family:inherit;font-weight:600;cursor:pointer;text-align:left;text-decoration:none}
  #menu .cmd:hover{border-color:var(--oro);color:var(--oro-chiaro);background:rgba(217,180,90,.09)}
  #menu .cmd kbd{font-family:Consolas,monospace;font-size:10.5px;color:var(--spento);
     border:1px solid var(--bordo);border-radius:5px;padding:1px 6px;background:rgba(0,0,0,.35)}
  #menu .cmd.on{border-color:var(--oro);background:rgba(217,180,90,.14);color:var(--oro-chiaro)}
  #menu .spiega{font-size:11.5px;color:var(--spento);margin:-2px 0 9px;line-height:1.45}
  #menu .chiudi{position:absolute;top:10px;right:12px;padding:3px 9px;font-size:15px;line-height:1}
  .velo{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:10;display:none}
  .velo.on{display:block}
</style></head><body>

<div class="bar">
  <h1>▚ ALBERO NETWORK</h1>
  <div class="kpi">
    <span>posti <b id="k-posti"><?= $fmt($RIEP['posti']) ?></b></span>
    <span>occupati <b id="k-occ"><?= $fmt($RIEP['occupati']) ?></b></span>
    <span>attivi <b id="k-att"><?= $fmt($RIEP['attivi']) ?></b></span>
    <span>World <b><?= $fmt($RIEP['world']) ?></b></span>
    <span>National <b><?= $fmt($RIEP['national']) ?></b></span>
    <span>Pro <b><?= $fmt($RIEP['pro']) ?></b></span>
    <span>rete <b id="k-user"><?= $fmt($RIEP['user']) ?></b></span>
  </div>
  <div class="cerca">
    <input id="q" placeholder="posto, SIC, nome o email…">
    <button id="vai">Cerca</button>
  </div>
  <div class="right">
    <button id="apri-utenti" title="Elenco di tutte le persone piazzate, con spostamento diretto">👤 Persone</button>
    <button id="apri-menu" title="Tutti i comandi (M)">☰ Comandi</button>
    <button id="forma" title="Cambia la disposizione: albero (il tronco in basso e i rami che salgono), orizzontale (da sinistra, comoda con tanti figli), stella (a raggiera dal centro)">🌳 Albero</button>
    <button id="tutti-world" title="Apre tutti i 9 World e i loro National">⤢ Apri i World</button>
    <button id="ultimi" title="Porta l'albero sugli ultimi entrati: dopo un caricamento di massa i nuovi stanno al quarto livello e non si vedono dalla vista di partenza">⚡ Ultimi arrivati</button>
    <button id="richiudi">⤡ Richiudi</button>
    <a class="btn" href="../admin.php">← Dashboard</a>
    <!-- comandi senza posto nella barra: il menu ☰ e la tastiera li premono
         da soli. Stanno qui e non altrove per non avere due logiche. -->
    <button id="b-isola"  style="display:none"></button>
    <button id="b-tutto"  style="display:none"></button>
    <button id="b-filtro" style="display:none"></button>
    <button id="b-prof"   style="display:none"></button>
    <button id="b-link"   style="display:none"></button>
    <button id="b-png"    style="display:none"></button>
    <button id="b-centra" style="display:none"></button>
    <button id="b-numeri" style="display:none"></button>
    <button id="b-peso"   style="display:none"></button>
  </div>
</div>

<?php if (!$TOPOLOGIA_OK): ?>
<div class="avviso" id="avviso">
  <b>⚠ La struttura in banca dati non e' allineata.</b>
  <span>Sotto il MASTER-NODE risultano <b><?= (int)$WORLD_SOTTO_MASTER ?></b> World invece di 9:
  i posti hanno ancora la vecchia topologia ternaria. Il riallineamento non tocca i posti gia'
  venduti ne' quelli che hai spostato a mano.</span>
  <button id="fix-topo">Riallinea adesso</button>
</div>
<?php endif; ?>

<div id="scena"><svg id="svg"></svg></div>
<div class="briciole" id="briciole"></div>
<!-- la legenda degli Anelli: una vista proporzionale senza legenda si legge
     a caso, e leggere a caso un grafico di soldi e persone e' peggio che non
     leggerlo. Compare solo quando serve. -->
<div class="legenda" id="legenda"></div>
<!-- cosa e' acceso adesso: filtro, ramo isolato, limite di profondita'.
     Se una vista mente per omissione, deve dirlo lei stessa. -->
<div class="stato-vista" id="stato-vista"></div>
<div class="aiuto">rotella = zoom · click = apri/chiudi + scheda · trascina un nodo su un altro = appendi il RAMO · <b>ALT + trascina = sposta la PERSONA</b> · 👤 Persone = elenco con spostamento diretto</div>
<div class="tip" id="tip"></div>
<div id="numeri">
  <button class="chiudi" onclick="document.getElementById('numeri').classList.remove('aperto')">✕</button>
  <h2>I numeri della rete</h2>
  <div id="numeri-corpo" style="color:#a99a80;font-size:13px">carico…</div>
</div>
<div class="carico" id="carico">carico…</div>
<div id="toast"></div>
<div class="barra-mano" id="barra-mano">
  <span>Hai in mano <b id="mano-chi"></b> — clicca il nodo dove vuoi posarla</span>
  <button id="mano-annulla">Annulla</button>
</div>
<aside id="utenti">
  <button class="chiudi" id="ut-chiudi">×</button>
  <h2>Le persone nella rete</h2>
  <div class="ut-cerca"><input id="ut-q" placeholder="nome, email, SIC o posto…"><button id="ut-vai">Cerca</button></div>
  <div id="ut-lista"></div>
  <div style="text-align:center;margin-top:8px"><button id="ut-altri" style="display:none">carica altri</button></div>
</aside>
<div class="risultati" id="risultati"></div>

<aside id="pan">
  <button class="chiudi" id="pan-chiudi">×</button>
  <div id="pan-corpo"></div>
</aside>

<script>
window.DR_API  = 'dr-network-tree-api.php';
window.DR_KEY  = <?= json_encode($APIKEY) ?>;
</script>
<!-- d3 servito dal NOSTRO sito, non da un CDN esterno: se jsdelivr e' lento,
     bloccato da una rete aziendale o irraggiungibile, la pagina resterebbe
     bianca (provato: senza d3 il disegno non parte affatto). Il CDN resta
     come rete di sicurezza solo se il file locale mancasse. -->
<script src="vendor/d3.v7.min.js"></script>
<script>
if (!window.d3) {
  document.write('<script src="https://cdn.jsdelivr.net/npm/d3@7"><\/script>');
}
</script>
<script>
/* ==========================================================================
   ALBERO — d3 v7.

   IL VERSO E' ROVESCIATO, ED E' VOLUTO.
   Ogni network del mondo si disegna a piramide: il capo in cima e la gente
   che scende. Il discorso che ne esce e' sempre lo stesso — "sotto di me ho
   tot persone" — e mette chi entra all'ultimo posto.
   Qui no. Il MASTER-NODE e' il TRONCO, sta in basso, e i rami salgono. Chi
   entra non finisce sotto qualcuno: cresce sopra. E la frase cambia da sola:
   "sopra di me ne ho fatti crescere tot". Stessa struttura, stessi dati,
   significato opposto.

   Col bottone si passa a ORIZZONTALE (da sinistra: comoda quando un nodo ha
   centinaia di figli) e a STELLA (a raggiera). Il tool Stella e' un'altra
   pagina e resta com'e'.

   Regole di scala rispettate qui dentro:
   - non si chiede MAI l'albero intero: solo "i figli di questo nodo"
   - un nodo con molti figli mostra i primi 200 e un bottone "altri N"
   - il conteggio dei figli arriva col nodo, cosi' si sa se e' espandibile
     senza chiedere niente
   ========================================================================== */
(function(){
  const API = window.DR_API, KEY = window.DR_KEY || '';
  /* figli caricati per pagina. 80 e' un compromesso misurato: abbastanza da
     vedere un ramo intero in un colpo, poco abbastanza da restare leggibile
     sullo schermo (con 200 i nodi finivano fuori vista) e leggero da disegnare. */
  const PASSO = 80;

  const svg = d3.select('#svg');

  /* LUCE. Un pallino d'oro su fondo nero e' solo un pallino; con un alone
     diventa una cosa accesa. L'alone e' un filtro solo: si applica ai nodi
     occupati e si spegne da solo quando a schermo ce ne sono troppi, perche'
     un filtro per nodo su cinquecento nodi fa arrancare il disegno. */
  const defs = svg.append('defs');
  (function(){
    const f = defs.append('filter').attr('id','bagliore')
                  .attr('x','-160%').attr('y','-160%').attr('width','420%').attr('height','420%');
    f.append('feGaussianBlur').attr('stdDeviation', 3.4).attr('result','sfocato');
    const m = f.append('feMerge');
    m.append('feMergeNode').attr('in','sfocato');
    m.append('feMergeNode').attr('in','sfocato');
    m.append('feMergeNode').attr('in','SourceGraphic');

    /* i rami sbiadiscono salendo: in basso sono legno, in cima sono luce */
    const g = defs.append('linearGradient').attr('id','linfa')
                  .attr('x1','0').attr('y1','1').attr('x2','0').attr('y2','0');
    g.append('stop').attr('offset','0%').attr('stop-color','#d9b45a').attr('stop-opacity',.95);
    g.append('stop').attr('offset','55%').attr('stop-color','#c39a45').attr('stop-opacity',.6);
    g.append('stop').attr('offset','100%').attr('stop-color','#f2dba4').attr('stop-opacity',.35);
  })();

  /* due strati di rami: sotto la scia sfocata, sopra il ramo vero. Insieme
     danno la sensazione della linfa che sale, non di un filo disegnato. */
  const gScia  = svg.append('g').attr('fill','none').attr('stroke','#d9b45a')
                    .attr('stroke-linecap','round').attr('opacity',.30)
                    .attr('filter','url(#bagliore)');
  const gLink  = svg.append('g').attr('fill','none').attr('stroke','url(#linfa)')
                    .attr('stroke-linecap','round').attr('stroke-width',1.6);
  const gNodo = svg.append('g');
  /* gli Anelli si disegnano su uno strato loro: e' un disegno diverso
     (spicchi, non pallini e fili) e non deve mescolarsi con gli altri */
  const gArchi = svg.append('g');
  /* la mappa dei 118: caselle, non pallini ne' spicchi. Strato suo. */
  const gMappa = svg.append('g');
  const tip = d3.select('#tip'), carico = d3.select('#carico');

  /* Una sola scala: piu' sali di grado, piu' il colore va verso il bianco.
     Il Master e' quasi bianco perche' e' il tronco; i cinque milioni di
     posizioni normali sono bianco caldo, senza oro: l'oro e' dei 118. */
  const COLORI = {master:'#fff4cf', world:'#ffd166', national:'#dcaa3c', pro:'#b3862c', user:'#e6e0d4'};
  const RAGGIO = {master:16, world:12, national:10, pro:8, user:6};

  /* Nodo finto "▾ altri N": non esiste in banca dati, serve solo a dare un
     posto dove cliccare per chiedere la pagina successiva di figli. Ha posto
     negativo cosi' non puo' mai collidere con un posto vero (che parte da 0). */
  function nodoAltri(padre, restanti){
    return {posto: -1000000 - padre.posto, piu: true, diChi: padre.posto,
            restanti: restanti, tipo: 'piu', figli: 0, nome: '', sic: '', occupato: 0};
  }

  let radice = null;           // gerarchia dati grezzi
  let selezionato = null;
  let trasformazione = d3.zoomIdentity;
  let autoZoomAttivo = true;

  /* --------------------------------------------------------------- avvisi */
  let toastTimer = null;
  function avvisa(msg, ok){
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'on' + (ok === false ? ' ko' : '');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.className = ''; }, ok === false ? 6000 : 3800);
  }

  /* ---------------------------------------------------------------- rete */
  function url(params){
    const p = new URLSearchParams(params);
    if (KEY) p.set('key', KEY);
    return API + '?' + p.toString();
  }
  async function chiedi(params){
    carico.style('display','block');
    try {
      const r = await fetch(url(params), {credentials:'same-origin'});
      const j = await r.json();
      if (!j.ok) throw new Error(j.err || 'errore');
      return j;
    } finally { carico.style('display','none'); }
  }
  async function scrivi(params){
    carico.style('display','block');
    try {
      const body = new URLSearchParams(params);
      if (KEY) body.set('key', KEY);
      const r = await fetch(API, {method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/x-www-form-urlencoded'}, body});
      return await r.json();
    } finally { carico.style('display','none'); }
  }

  /* --------------------------------------------------- struttura in memoria */
  function prepara(n){
    n._figli = n.children || null;      // figli caricati ma nascosti
    n.children = null;
    n.caricato = n.caricato || 0;
    n.offset = 0;
    if (n._figli) n._figli.forEach(prepara);
    return n;
  }
  function trova(n, posto){
    if (n.posto === posto) return n;
    const lista = n.children || n._figli || [];
    for (const c of lista){ const t = trova(c, posto); if (t) return t; }
    return null;
  }
  function apertoQ(n){ return !!n.children; }

  /* rimette in fondo alla lista il nodo "altri N" (o lo toglie se non serve) */
  function aggiornaAltri(n){
    n._figli = (n._figli || []).filter(f => !f.piu);
    if (n.altri > 0) n._figli.push(nodoAltri(n, n.altri));
  }

  async function espandi(n, forza){
    if (n.piu || n.figli === 0) return;
    if (!n.caricato){
      const j = await chiedi({azione:'figli', posto:n.posto, limit:PASSO, offset:0,
                             solo_occupati: soloOccupati ? 1 : 0});
      n._figli = j.figli.map(f => prepara(f));
      n.caricato = 1;
      n.tot_figli = j.tot_figli; n.occupati = j.occupati; n.altri = j.altri;
      aggiornaAltri(n);
    }
    n.children = n._figli;
    if (forza) n._apertoDaZoom = true;
    disegna();
    /* se il ramo appena aperto e' grande, riquadro tutto: altrimenti i figli
       finiscono fuori schermo e sembra che la pagina si sia rotta */
    if ((n._figli || []).length > 40) adatta();
  }
  function chiudi(n){ n.children = null; disegna(); }

  async function altraPagina(n){
    const veri = (n._figli || []).filter(f => !f.piu);
    const j = await chiedi({azione:'figli', posto:n.posto, limit:PASSO, offset:veri.length,
                           solo_occupati: soloOccupati ? 1 : 0});
    const nuovi = j.figli.map(f => prepara(f)).filter(f => !veri.some(x => x.posto === f.posto));
    n._figli = veri.concat(nuovi);
    n.altri = j.altri;
    aggiornaAltri(n);
    n.children = n._figli;
    disegna();
  }

  /* --------------------------------------------------------------- disegno */
  const zoom = d3.zoom().scaleExtent([0.06, 5]).on('zoom', ev => {
    trasformazione = ev.transform;
    gScia.attr('transform', ev.transform);
    gLink.attr('transform', ev.transform);
    gNodo.attr('transform', ev.transform);
    gArchi.attr('transform', ev.transform);
    gMappa.attr('transform', ev.transform);
    if (autoZoomAttivo && ev.sourceEvent && ev.sourceEvent.type === 'wheel') apriPerZoom();
  });
  svg.call(zoom)
     .on('mousedown.cur', () => svg.classed('trascino', true))
     .on('mouseup.cur',   () => svg.classed('trascino', false));

  /* ZOOM-ESPANSIONE: avvicinandosi, i nodi visibili che hanno figli non ancora
     aperti si aprono da soli — ma uno alla volta e solo se sono davvero dentro
     lo schermo, per non scaricare mezza rete con una rotellata. */
  let zoomInCorso = false;
  async function apriPerZoom(){
    if (zoomInCorso || !radice) return;
    const k = trasformazione.k;
    if (k < 0.9) return;                       // troppo lontano: non aprire nulla
    const W = window.innerWidth, H = window.innerHeight;
    const candidati = [];
    (function scendi(n){
      if (!n._xy) return;
      if (!apertoQ(n) && n.figli > 0){
        const x = trasformazione.applyX(n._xy.x), y = trasformazione.applyY(n._xy.y);
        if (x > 60 && x < W - 380 && y > 60 && y < H - 60) candidati.push(n);
      }
      (n.children || []).forEach(scendi);
    })(radice);
    if (!candidati.length) return;
    zoomInCorso = true;
    try { await espandi(candidati[0], true); } finally { zoomInCorso = false; }
  }

  /* TRE DISPOSIZIONI, stesso albero: cambia solo dove finiscono i punti.
       'albero'      DEFAULT. Un albero vero: il MASTER-NODE e' il tronco e sta
                     IN BASSO, i rami salgono. Non e' un vezzo grafico: e' il
                     rovesciamento del discorso MLM. Nessuno sta sotto a
                     nessuno, si cresce verso l'alto.
       'orizzontale' da sinistra a destra. Serve quando un nodo ha centinaia di
                     figli: in colonna ci stanno, in riga no.
       'stella'      a raggiera dal centro. Bella per capire la forma d'insieme
                     della rete; per il lavoro quotidiano resta l'albero.
     Il tool Stella (stella-network.php) resta com'e': questa e' solo una vista
     in piu' dentro l'Albero. */
  /* ?forma=stella|orizzontale|albero — la vecchia pagina Stella ora manda qui
     con ?forma=stella, cosi' l'indirizzo che avevi gia' pubblicato continua a
     funzionare e il tool resta uno solo. */
  const _qs = new URLSearchParams(location.search);
  const _f0 = _qs.get('forma');
  let forma = (['albero','orizzontale','ventaglio','stella','anelli','griglia'].includes(_f0)
               ? _f0 : 'albero');

  /* FILTRO — con il filtro acceso l'API manda solo i posti con una persona
     dentro. Non e' un nascondere lato disegno: sono proprio meno righe che
     viaggiano, e con 8.000 persone dentro 5 milioni di posti cambia tutto. */
  let soloOccupati = false;
  const _soloIniziale = (_qs.get('solo') === '1');
  /* PROFONDITA' — 0 = nessun limite. Serve a guardare "solo i primi due
     piani" senza richiudere a mano venti rami. */
  let profMax = Math.max(0, Math.min(9, parseInt(_qs.get('liv') || '0', 10) || 0));
  /* ISOLAMENTO — quando si isola un ramo, la radice vera finisce qui e si
     torna indietro con un comando. La pila non e' un vezzo: si puo' isolare
     dentro un isolamento. */
  const pilaIsola = [];
  /* PESI — quante PERSONE stanno sotto a ogni posto. Li usa la vista Anelli;
     arrivano dall'API, non si inventano. */
  const pesi = {};
  let pesiInCorso = false;
  /* per muoversi con le frecce serve sapere chi e' il padre di chi a schermo */
  let mappaPadre = new Map();
  /* PESO DEGLI ANELLI: persone o euro. Due domande diverse — "dove c'e' piu'
     gente" e "dove ci sono piu' soldi" — non danno la stessa risposta, ed e'
     esattamente per questo che serve poter cambiare. */
  let pesoPer = (_qs.get('peso') === 'euro') ? 'euro' : 'persone';
  let datiMappa = null;

  /* IL RAMO. Una cubica con i due punti di controllo sulla verticale del
     padre e del figlio: il ramo esce dal padre salendo dritto, poi si piega
     e arriva al figlio salendo dritto. E' il modo in cui cresce un ramo vero.
     0.62/0.34 e non 0.5/0.5: cosi' il tronco sale piu' a lungo prima di
     aprirsi, invece di piegarsi subito a meta' strada. */
  function ramo(d){
    const sx = d.source.px, sy = d.source.py, tx = d.target.px, ty = d.target.py;
    const dy = ty - sy;
    return `M${sx},${sy} C${sx},${sy + dy * 0.62} ${tx},${sy + dy * 0.34} ${tx},${ty}`;
  }
  /* Il ramo si assottiglia salendo, come tutti i rami. Tronco spesso, ramo
     piu' sottile, rametto sottilissimo. */
  function spessore(d){
    if (forma !== 'albero') return 1.4;
    return Math.max(0.75, 3.8 - d.target.depth * 0.82);
  }

  function disegna(){
    if (!radice) return;
    const root = d3.hierarchy(radice, d => d.children);
    /* PROFONDITA' MASSIMA — si taglia qui, dopo aver costruito la gerarchia:
       i rami restano aperti in memoria (richiudendo il limite ritornano senza
       ricaricare niente), semplicemente non si disegnano. */
    if (profMax > 0) root.each(d => { if (d.depth >= profMax) d.children = null; });

    /* chi e' padre di chi, a schermo: serve alle frecce della tastiera */
    mappaPadre = new Map();
    root.each(d => { if (d.parent) mappaPadre.set(d.data.posto, d.parent.data); });

    if (forma === 'anelli') { gMappa.attr('display','none'); disegnaAnelli(root); return; }
    if (forma === 'griglia') { gArchi.attr('display','none').selectAll('*').remove();
                               disegnaMappa(); return; }
    gMappa.attr('display','none').selectAll('*').remove();
    gArchi.attr('display','none').selectAll('*').remove();
    gLink.attr('display', null); gScia.attr('display', null); gNodo.attr('display', null);
    const _lg = document.getElementById('legenda'); if (_lg) _lg.style.display = 'none';

    const quanti = root.descendants().length;
    const nodi = root.descendants(), archi = root.links();
    /* Le etichette dell'albero si inclinano quando i rami sono vicini:
       il valore vero di passoX lo sa solo il ramo 'albero' qui sotto. */
    let passoX = 132, fitto = false;

    if (forma === 'stella'){
      /* i fratelli si spartiscono l'angolo giro; il raggio cresce col livello.
         La separazione si divide per la profondita' cosi' gli anelli esterni,
         che hanno piu' spazio, non sprecano mezzo cerchio. */
      const prof = Math.max(1, d3.max(nodi, d => d.depth) || 1);
      const raggio = 175 + prof * 135 + Math.min(420, quanti * 0.9);
      d3.tree()
        .size([2 * Math.PI, raggio])
        .separation((a, b) => (a.parent === b.parent ? 1 : 2.2) / Math.max(1, a.depth))(root);

      nodi.forEach(d => {
        const ang = d.x - Math.PI / 2, r = d.y;
        d.px = Math.cos(ang) * r;
        d.py = Math.sin(ang) * r;
        d.ang = d.x;
        d.data._xy = {x: d.px, y: d.py};
      });
    } else if (forma === 'ventaglio'){
      /* IL VENTAGLIO — mezza stella, aperta verso l'alto.
         La stella intera e' bella ma manda meta' dei rami verso il basso, e
         verso il basso in Destino Randagio non va niente. Il ventaglio tiene
         il senso dell'albero (si sale) e la leggibilita' della stella (i
         fratelli si spartiscono l'angolo invece di stringersi in riga). */
      const prof = Math.max(1, d3.max(nodi, d => d.depth) || 1);
      const raggio = 195 + prof * 145 + Math.min(400, quanti * 0.85);
      d3.tree()
        .size([Math.PI, raggio])
        .separation((a, b) => (a.parent === b.parent ? 1 : 2) / Math.max(1, a.depth))(root);
      nodi.forEach(d => {
        /* da PI (sinistra, orizzontale) a 2PI (destra), passando per l'alto:
           il seno resta negativo, quindi in SVG e' tutto sopra la radice */
        const th = Math.PI + d.x;
        d.px = Math.cos(th) * d.y;
        d.py = Math.sin(th) * d.y;
        d.ang = th + Math.PI / 2;   /* cosi' l'etichetta usa la stessa formula della stella */
        d.data._xy = {x: d.px, y: d.py};
      });
    } else if (forma === 'orizzontale'){
      const passoY = quanti > 400 ? 13 : (quanti > 150 ? 17 : 26);
      d3.tree().nodeSize([passoY, 260])(root);
      nodi.forEach(d => { d.px = d.y; d.py = d.x; d.ang = 0; d.data._xy = {x: d.px, y: d.py}; });
    } else {
      /* ALBERO — si sale, non si scende.
         Il passo orizzontale si stringe quando i nodi a schermo sono tanti,
         altrimenti un livello con 80 fratelli diventa largo un chilometro;
         il passo verticale resta fisso perche' i piani devono restare
         riconoscibili come piani. */
      /* Il passo NON e' fisso: si calcola sulla larghezza che c'e' davvero e
         su quante punte ci sono. Con un passo fisso l'albero di partenza
         (tronco + 9 World + i loro National = 32 punte) veniva largo 3.300 px,
         lo zoom automatico lo riduceva a meno di meta' e i nomi diventavano
         illeggibili. Cosi' invece l'albero riempie lo schermo e si legge. */
      const foglie = Math.max(1, root.leaves().length);
      const panApr = document.getElementById('pan').classList.contains('aperto');
      const disp   = Math.max(560, window.innerWidth - (panApr ? 400 : 40) - 80);
      passoX = Math.max(34, Math.min(132, disp / foglie));
      /* anche l'altezza dei piani si adatta: con tre soli livelli un passo
         fisso lasciava due terzi di schermo vuoti e l'albero sembrava
         schiacciato in fondo; con dieci livelli, al contrario, non ci stava.
         Cosi' l'albero occupa l'altezza che ha, sempre. */
      const prof   = Math.max(1, d3.max(nodi, d => d.depth) || 1);
      const hDisp  = Math.max(320, window.innerHeight - 210);
      const passoY = Math.max(96, Math.min(300, hDisp / (prof + 0.55)));
      d3.tree().nodeSize([passoX, passoY])
               .separation((a, b) => (a.parent === b.parent ? 1 : 1.4))(root);
      fitto = (passoX < 74);
      /* IL MENO DAVANTI ALLA Y E' TUTTO IL RIBALTAMENTO.
         d3 calcola l'albero come lo calcolano tutti, con la radice a zero e i
         livelli che crescono. Cambiando segno, i livelli crescono verso
         l'alto: il tronco resta a zero e i rami salgono. Tre caratteri. */
      nodi.forEach(d => { d.px = d.x; d.py = -d.y; d.ang = 0; d.data._xy = {x: d.px, y: d.py}; });
    }

    /* archi */
    const l = gLink.selectAll('path').data(archi, d => d.target.data.posto);
    l.exit().remove();
    l.enter().append('path').merge(l)
      .attr('d', d => {
        if (forma === 'stella' || forma === 'ventaglio'){
          /* curva che passa vicino al centro: si legge da quale ramo viene */
          const mx = (d.source.px + d.target.px) / 2 * 0.68;
          const my = (d.source.py + d.target.py) / 2 * 0.68;
          return `M${d.source.px},${d.source.py} Q${mx},${my} ${d.target.px},${d.target.py}`;
        }
        if (forma === 'orizzontale') return d3.linkHorizontal().x(p => p.px).y(p => p.py)(d);
        return ramo(d);
      })
      .attr('stroke-width', d => spessore(d))
      .attr('stroke-opacity', d => forma === 'albero' ? Math.max(.34, .95 - d.target.depth * .12) : .55);

    /* la scia: stesso disegno, piu' grosso e sfocato, sotto */
    const sc = gScia.selectAll('path').data(quanti < 320 && forma === 'albero' ? archi : [], d => d.target.data.posto);
    sc.exit().remove();
    sc.enter().append('path').merge(sc)
      .attr('d', d => ramo(d))
      .attr('stroke-width', d => spessore(d) * 2.6)
      .attr('stroke-opacity', d => Math.max(.10, .5 - d.target.depth * .09));

    /* nodi */
    const g = gNodo.selectAll('g.nodo').data(nodi, d => d.data.posto);
    g.exit().remove();
    const gEnter = g.enter().append('g').attr('class','nodo');
    gEnter.append('circle');
    gEnter.append('text').attr('class','et');
    gEnter.append('text').attr('class','pi');

    const tutti = gEnter.merge(g);
    tutti.attr('transform', d => `translate(${d.px},${d.py})`)
         .classed('trascinato', d => trascina.attivo && trascina.nodo === d.data)
         .classed('in-mano', d => !!inMano && d.data.posto === inMano.posto);

    /* l'alone costa: sopra i 320 nodi a schermo si spegne e restano i colori */
    const alone = (quanti < 320);
    tutti.select('circle')
      .attr('r', d => d.data.piu ? 5 : (RAGGIO[d.data.tipo] || 6))
      /* occupato = pieno e acceso · libero = vuoto, solo il contorno.
         Su fondo nero il "vuoto" e' nero, non bianco: un posto libero non
         deve tirare l'occhio piu' di una persona vera. */
      .attr('fill', d => d.data.piu ? 'rgba(217,180,90,.85)'
                       : (d.data.occupato ? (COLORI[d.data.tipo] || '#e6e0d4') : 'rgba(10,9,8,.85)'))
      .attr('stroke', d => d.data.piu ? '#d9b45a' : (COLORI[d.data.tipo] || '#e6e0d4'))
      .attr('stroke-width', d => d.data.posto === (selezionato && selezionato.posto) ? 3.5 : 1.6)
      .attr('stroke-opacity', d => (d.data.piu || d.data.occupato) ? 1 : .55)
      .attr('stroke-dasharray', d => (d.data.piu || d.data.occupato) ? null : '3,2')
      .attr('filter', d => (alone && (d.data.occupato || d.data.tipo === 'master')) ? 'url(#bagliore)' : null);

    tutti.select('text.et')
      .attr('font-size', d => d.data.tipo === 'master' ? 13 : (d.data.tipo === 'user' ? 9.5 : 11))
      .attr('font-family','Georgia,serif')
      .attr('font-weight', d => d.data.piu ? 700 : 400)
      .attr('fill', d => d.data.piu ? '#d9b45a'
                       : (d.data.tipo === 'master' ? '#fff4cf'
                       : (d.data.occupato ? '#f2e9d8' : '#7e7263')))
      .each(function(d){
        const el = d3.select(this), rr = (d.data.piu ? 5 : (RAGGIO[d.data.tipo] || 6)) + 6;
        if (forma === 'stella' || forma === 'ventaglio'){
          if (d.depth === 0){ el.attr('text-anchor','middle').attr('dy', forma === 'ventaglio' ? 26 : -22).attr('x',0).attr('transform',null); return; }
          /* etichetta appoggiata al raggio, raddrizzata sul lato sinistro */
          const gr = d.ang * 180 / Math.PI - 90;
          const sx = (gr > 90 || gr < -90);
          el.attr('x', null).attr('dy','0.32em')
            .attr('text-anchor', sx ? 'end' : 'start')
            .attr('transform', `rotate(${gr}) translate(${sx ? -rr : rr},0)${sx ? ' rotate(180)' : ''}`);
        } else if (forma === 'orizzontale') {
          const haFigli = (d.data.figli > 0 || d.children);
          el.attr('transform', null).attr('dy','0.32em')
            .attr('x', haFigli ? -rr : rr)
            .attr('text-anchor', haFigli ? 'end' : 'start');
        } else {
          /* albero: il nome sta SOPRA la punta, dove il ramo va a finire.
             Quando i rami sono vicini le scritte dritte si sovrappongono: si
             inclinano, cosi' salgono in diagonale e non si toccano. */
          el.attr('x', null).attr('dy', null);
          /* il tronco fa eccezione: il suo nome sta SOTTO, alla base, perche'
             sopra di lui passa tutto il resto dell'albero */
          if (d.depth === 0){
            el.attr('text-anchor','middle').attr('transform', `translate(0,${rr + 15})`);
            return;
          }
          if (fitto){
            el.attr('text-anchor','start')
              .attr('transform', `translate(0,${-(rr + 3)}) rotate(-42)`);
          } else {
            el.attr('text-anchor','middle')
              .attr('transform', `translate(0,${-(rr + 8)})`);
          }
        }
      })
      .text(d => etichetta(d.data));

    /* pallino "+N" sui rami chiusi */
    tutti.select('text.pi')
      .attr('dy','0.32em')
      .attr('x', d => forma === 'orizzontale' ? (RAGGIO[d.data.tipo] + 5) : 0)
      .attr('y', d => forma === 'orizzontale' ? 0 : -(RAGGIO[d.data.tipo] + 6))
      .attr('text-anchor','middle')
      .attr('font-size', 9.5).attr('font-family','Georgia,serif').attr('fill','#d9b45a')
      /* PRIMA qui c'era '+' e il numero dei POSTI figli: su un Pro usciva
         "+61k", che e' il numero delle sedie e non serve a niente. Adesso e'
         il numero delle PERSONE nel ramo, che e' quello che uno guarda. */
      .text(d => {
        const r = Number(d.data.rete || 0);
        if (r > 0) return '▾ ' + abbrevia(r);
        return (!d.children && d.data.figli > 0) ? '·' : '';
      });

    tutti
      .on('mouseenter', (ev,d) => mostraTip(ev,d.data))
      .on('mousemove',  ev => tip.style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px'))
      .on('mouseleave', () => tip.style('display','none'))
      .on('click', async (ev,d) => {
        ev.stopPropagation();
        if (trascina.appenaFinito) { trascina.appenaFinito = false; return; }
        /* click sul nodo finto "altri N": carica la pagina successiva */
        if (d.data.piu){
          const padre = trova(radice, d.data.diChi);
          if (padre) await altraPagina(padre);
          return;
        }
        /* se ho una persona "in mano", questo click la posa qui */
        if (inMano){ await posa(d.data); return; }
        selezionato = d.data;
        scheda(d.data.posto);
        if (apertoQ(d.data)) chiudi(d.data);
        else if (d.data.figli > 0) await espandi(d.data);
        else disegna();
      })
      .call(trascinaNodo());

    briciole();
  }

  /* ==========================================================================
     GLI ANELLI — la vista che dice QUANTO, non DOVE.
     Ogni anello e' un livello; l'ampiezza dello spicchio e' quante PERSONE
     stanno sotto a quel ramo. Non quante posizioni: le posizioni sono 61.000
     per Pro per costruzione, quindi peserebbero tutte uguale e il disegno non
     direbbe niente. Con le persone invece si vede in un colpo d'occhio quale
     ramo sta crescendo e quale e' fermo.

     I pesi arrivano dall'API (azione=pesi). Se non ci sono ancora, si chiedono
     e si ridisegna: mai inventati.
     Se nella rete non c'e' ancora nessuno, si ripiega sul contare i nodi —
     e lo si dice nella legenda, invece di mostrare un cerchio vuoto.
  ========================================================================== */
  const arco = d3.arc()
    .startAngle(d => d.x0).endAngle(d => d.x1)
    .padAngle(0.006).padRadius(90)
    .innerRadius(d => d.y0).outerRadius(d => Math.max(d.y0 + 1, d.y1 - 2));

  function disegnaAnelli(root){
    gLink.attr('display','none'); gScia.attr('display','none'); gNodo.attr('display','none');
    gArchi.attr('display', null);

    /* i pesi che mancano: si chiedono una volta sola e si ridisegna */
    /* il MASTER e' il posto 0: con 'v > 0' il suo peso non si chiedeva mai */
    const senza = root.descendants().map(d => d.data.posto)
                      .filter(v => v >= 0 && !(v in pesi));
    if (senza.length && !pesiInCorso){
      pesiInCorso = true;
      chiedi({azione:'pesi', posti: senza.slice(0, 400).join(',')})
        .then(j => { Object.assign(pesi, j.pesi || {}); })
        .catch(() => {})
        /* stessa regola: si ridisegna solo se si e' ancora sugli Anelli */
        .finally(() => { pesiInCorso = false; if (forma === 'anelli') disegna(); });
    }

    /* IL NUMERO VERO, tenuto da parte.
       root.value dopo le correzioni non e' piu' il conto delle persone: ci
       sono dentro le briciole dei rami vuoti. Il numero che si scrive in
       mezzo dev'essere quello vero, se no il tool mente sul dato piu'
       importante che ha. */
    const inEuro = (pesoPer === 'euro');
    const val = pz => inEuro ? Math.max(0, +pz.euro || 0) : Math.max(0, +pz.occupati || 0);
    const totVero = (pesi[root.data.posto]) ? val(pesi[root.data.posto]) : 0;
    let veri = 0;
    root.each(d => {
      const pz = pesi[d.data.posto];
      d.value = pz ? val(pz) : (inEuro ? 0 : (d.data.occupato ? 1 : 0));
      veri += d.value;
    });
    /* I RAMI VUOTI NON SPARISCONO.
       Con peso zero uno spicchio e' largo zero, quindi un World dove non c'e'
       ancora nessuno scomparirebbe dal disegno: chi guarda penserebbe che non
       esiste. Invece esiste, ed e' proprio quello su cui c'e' da lavorare.
       Gli si da' una fetta sottile, scura, dichiarata nella legenda. */
    const briciola = Math.max(0.35, veri * 0.006);
    root.each(d => { if (d.value === 0) d.value = briciola; });
    /* il padre non puo' pesare meno dei figli che gli si vedono sotto:
       senza questa riga gli spicchi sbordano e girano intorno */
    root.eachAfter(d => {
      if (d.children){
        const somma = d3.sum(d.children, c => c.value);
        if (d.value < somma) d.value = somma;
      }
    });
    /* se il peso della radice non e' ancora arrivato non si scrive un numero
       inventato: si scrive che sta arrivando. `veri` NON e' il totale — e' la
       somma dei pesi di tutti i nodi, e ogni persona ci sta dentro una volta
       per ogni antenato. Scriverlo come totale sarebbe un numero gonfiato. */
    const senzaPeso = !(root.data.posto in pesi);
    const perNodi = (!senzaPeso && totVero === 0);
    if (perNodi){
      root.eachAfter(d => { d.value = 1 + (d.children ? d3.sum(d.children, c => c.value) : 0); });
    }

    const R = Math.max(240, Math.min(window.innerWidth, window.innerHeight) / 2 - 90);
    d3.partition().size([2 * Math.PI, R])(root);
    const nodi = root.descendants();
    /* il punto di ogni spicchio: serve a inquadrare la vista e a centrare su
       un nodo, esattamente come per i pallini delle altre viste */
    nodi.forEach(d => {
      const a = (d.x0 + d.x1) / 2 - Math.PI / 2, r = (d.y0 + d.y1) / 2;
      d.px = Math.cos(a) * r; d.py = Math.sin(a) * r;
      d.data._xy = {x: d.px, y: d.py};
    });

    const a = gArchi.selectAll('path').data(nodi, d => d.data.posto);
    a.exit().remove();
    a.enter().append('path')
      .attr('stroke','#0a0908').attr('stroke-width',0.7)
      .merge(a)
      .attr('d', arco)
      .attr('fill', d => d.depth === 0 ? '#fff4cf'
                        : (d.data.occupato || (pesi[d.data.posto] && pesi[d.data.posto].occupati)
                           ? (COLORI[d.data.tipo] || '#e6e0d4') : '#2a2418'))
      .attr('fill-opacity', d => d.depth === 0 ? 1 : Math.max(.30, .95 - d.depth * .13))
      .attr('stroke-opacity', .8)
      .style('cursor','pointer')
      .on('mouseenter', (ev,d) => mostraTipAnello(ev, d))
      .on('mousemove',  ev => tip.style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px'))
      .on('mouseleave', () => tip.style('display','none'))
      .on('click', async (ev,d) => {
        ev.stopPropagation();
        if (d.data.piu){ const pd = trova(radice, d.data.diChi); if (pd) await altraPagina(pd); return; }
        selezionato = d.data;
        scheda(d.data.posto);
        if (apertoQ(d.data)) chiudi(d.data);
        else if (d.data.figli > 0) await espandi(d.data);
        else disegna();
      });

    /* le scritte solo dove ci stanno davvero: uno spicchio sottile con dentro
       un nome tagliato a meta' e' peggio di uno spicchio muto */
    const et = gArchi.selectAll('text').data(nodi.filter(d => d.depth > 0 &&
                     (d.x1 - d.x0) > 0.075 && (d.y1 - d.y0) > 16), d => d.data.posto);
    et.exit().remove();
    et.enter().append('text')
      .attr('font-family','Georgia,serif').attr('font-size',10.5)
      .attr('text-anchor','middle').attr('dy','0.32em')
      .attr('pointer-events','none')
      .merge(et)
      .attr('fill', d => d.data.occupato ? '#0a0908' : '#9a8c74')
      .attr('transform', d => {
        const ang = (d.x0 + d.x1) / 2 * 180 / Math.PI - 90;
        const r = (d.y0 + d.y1) / 2;
        return `rotate(${ang}) translate(${r},0) rotate(${ang > 90 ? 180 : 0})`;
      })
      .text(d => {
        const t = d.data.occupato ? (d.data.nome || ('#'+d.data.posto)) : ('#'+d.data.posto);
        const spazio = Math.floor((d.x1 - d.x0) * ((d.y0+d.y1)/2) / 6.2);
        return t.length > spazio ? (spazio > 3 ? t.slice(0, spazio-1) + '…' : '') : t;
      });

    /* il cuore: il nodo di partenza, con il totale */
    const c = gArchi.selectAll('text.cuore').data([root]);
    c.enter().append('text').attr('class','cuore')
      .attr('text-anchor','middle').attr('font-family','Georgia,serif')
      .attr('pointer-events','none')
      .merge(c)
      .attr('font-size', 13).attr('fill','#4a3a12').attr('font-weight',700)
      .attr('dy','0.32em')
      .text(senzaPeso ? 'conto in corso…'
            : (perNodi ? (inEuro ? 'ancora zero incassato' : 'nessuno ancora in rete')
            : (inEuro
               ? ('€ ' + Number(totVero).toLocaleString('it-IT', {maximumFractionDigits:0}))
               : (Number(totVero).toLocaleString('it-IT') + ' persone'))));

    legendaAnelli(perNodi || senzaPeso, totVero, inEuro);
    briciole();
  }

  /* ==========================================================================
     LA MAPPA DEI 118 — tutto il progetto in una schermata.
     Non e' l'albero rimpicciolito: e' l'elenco dei 118 nodi veri messi in tre
     file (9 World, 27 National, 82 Pro), colorati per quanta gente hanno
     sotto. In dieci secondi si vede quali si muovono e quali sono fermi, che
     nell'albero richiederebbe di aprire 118 rami a mano.
     Click su una casella: si entra in quel ramo nell'albero.
  ========================================================================== */
  async function disegnaMappa(){
    gMappa.attr('display', null);
    const lg = document.getElementById('legenda');
    if (!datiMappa){
      if (lg){ lg.style.display='block'; lg.textContent = 'carico la mappa dei 118…'; }
      try { datiMappa = (await chiedi({azione:'mappa'})).mappa || []; }
      catch(e){ if (lg) lg.textContent = 'non riesco a leggere la mappa'; return; }
    }
    /* NEL FRATTEMPO POTRESTI AVER CAMBIATO VISTA.
       Il giro delle viste passa dalla Mappa per arrivare all'Albero: se questa
       funzione finisse il suo lavoro dopo, disegnerebbe caselle e legenda
       sopra un albero. Chi arriva tardi si ferma sulla porta. */
    if (forma !== 'griglia'){ gMappa.attr('display','none').selectAll('*').remove(); return; }
    const righe = [
      {t:'World · 9',    v: datiMappa.filter(x => x.posto >= 1  && x.posto <= 9),   perRiga: 9},
      {t:'National · 27', v: datiMappa.filter(x => x.posto >= 10 && x.posto <= 36),  perRiga: 14},
      {t:'Pro · 82',      v: datiMappa.filter(x => x.posto >= 37 && x.posto <= 118), perRiga: 14},
    ];
    const L = 74, G = 9;         /* lato casella e spazio */
    const maxSotto = Math.max(1, ...datiMappa.map(x => x.sotto));

    const celle = []; let y = 0;
    for (const r of righe){
      celle.push({titolo: r.t, x: 0, y: y, testata: 1});
      y += 26;
      r.v.forEach((c, i) => {
        celle.push(Object.assign({}, c, {
          x: (i % r.perRiga) * (L + G),
          y: y + Math.floor(i / r.perRiga) * (L + G)
        }));
      });
      y += (Math.ceil(r.v.length / r.perRiga)) * (L + G) + 22;
    }

    const g = gMappa.selectAll('g.cas').data(celle, d => d.testata ? ('t'+d.titolo) : ('c'+d.posto));
    g.exit().remove();
    const en = g.enter().append('g').attr('class','cas');
    en.append('rect'); en.append('text').attr('class','n');
    en.append('text').attr('class','q'); en.append('text').attr('class','e');
    const tutte = en.merge(g).attr('transform', d => `translate(${d.x},${d.y})`);

    tutte.select('rect')
      .attr('width',  d => d.testata ? 0 : L).attr('height', d => d.testata ? 0 : L)
      .attr('rx', 9)
      .attr('fill', d => d.testata ? 'none'
            : (d.sotto > 0
               ? d3.interpolateRgb('#2c2313', '#f2dba4')(Math.pow(d.sotto / maxSotto, .55))
               : (d.preso ? '#241d10' : 'rgba(10,9,8,.6)')))
      .attr('stroke', d => d.testata ? 'none' : (d.preso ? '#d9b45a' : 'rgba(217,180,90,.22)'))
      .attr('stroke-width', d => (selezionato && d.posto === selezionato.posto) ? 3 : 1.2)
      .attr('stroke-dasharray', d => d.preso ? null : '3,2')
      .style('cursor', d => d.testata ? 'default' : 'pointer');

    /* il numero del nodo, sempre leggibile: su una casella chiara serve
       inchiostro scuro, su una scura serve oro */
    const chiara = d => d.sotto > 0 && (d.sotto / maxSotto) > 0.42;
    tutte.select('text.n')
      .attr('x', d => d.testata ? 0 : 9).attr('y', d => d.testata ? 13 : 21)
      .attr('font-family','Georgia,serif')
      .attr('font-size', d => d.testata ? 13 : 14)
      .attr('fill', d => d.testata ? '#8a7c62' : (chiara(d) ? '#231a08' : '#f2dba4'))
      .attr('letter-spacing', d => d.testata ? '.09em' : null)
      .text(d => d.testata ? d.titolo.toUpperCase() : ('#' + d.posto));
    tutte.select('text.q')
      .attr('x', 9).attr('y', 42).attr('font-family','Georgia,serif').attr('font-size', 15)
      .attr('fill', d => d.testata ? 'none' : (chiara(d) ? '#231a08' : '#e6e0d4'))
      .text(d => d.testata ? '' : (d.sotto > 0 ? abbrevia(d.sotto) : '—'));
    tutte.select('text.e')
      .attr('x', 9).attr('y', 60).attr('font-family','Georgia,serif').attr('font-size', 11)
      .attr('fill', d => d.testata ? 'none' : (chiara(d) ? '#4a3a12' : '#a99a80'))
      .text(d => d.testata ? '' : (d.euro > 0 ? ('€ ' + abbrevia(Math.round(d.euro))) : ''));

    tutte
      .on('mouseenter', (ev,d) => {
        if (d.testata) return;
        tip.style('display','block')
           .style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px')
           .html('<b>#' + d.posto + ' · ' + (d.tipo || 'nodo') + '</b><br>'
               + (d.preso ? (d.nome || 'assegnato') : '<i>non ancora assegnato</i>') + '<br>'
               + 'persone sotto: <b>' + Number(d.sotto).toLocaleString('it-IT') + '</b><br>'
               + 'attive: ' + Number(d.attivi).toLocaleString('it-IT') + '<br>'
               + 'incassato dal ramo: € ' + Number(d.euro).toLocaleString('it-IT',
                   {minimumFractionDigits:2, maximumFractionDigits:2}));
      })
      .on('mousemove', ev => tip.style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px'))
      .on('mouseleave', () => tip.style('display','none'))
      .on('click', async (ev,d) => {
        if (d.testata) return;
        ev.stopPropagation();
        forma = 'albero';
        document.getElementById('forma').textContent = ETICHETTA_FORMA.albero;
        try { await vaiA(d.posto); } catch(e){}
      });

    if (lg){
      lg.style.display = 'block';
      const pieni = datiMappa.filter(x => x.sotto > 0).length;
      const presi = datiMappa.filter(x => x.preso).length;
      lg.innerHTML = 'Mappa dei 118: piu\' la casella e\' chiara, piu\' gente c\'e\' sotto quel nodo. '
        + 'Il contorno pieno vuol dire <b>assegnato</b>, quello tratteggiato <b>ancora libero</b>. '
        + '<b>' + presi + '</b> assegnati, <b>' + pieni + '</b> con almeno una persona sotto. '
        + 'Click su una casella: entri in quel ramo nell\'albero.';
    }
    adatta();
  }

  function mostraTipAnello(ev, d){
    const pz = pesi[d.data.posto] || null;
    tip.style('display','block')
       .style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px')
       .html(`<b>${d.data.tipo === 'master'
                    ? ((d.data.nome && d.data.nome !== 'MASTER-NODE') ? d.data.nome : 'MASTER-NODE')
                    : (d.data.nome || ('posto '+d.data.posto))}</b><br>
              posto ${d.data.posto}<br>
              ${pz ? ('persone sotto: <b>' + Number(pz.occupati).toLocaleString('it-IT') + '</b><br>attive: '
                      + Number(pz.attivi).toLocaleString('it-IT'))
                   : 'peso non ancora caricato'}`);
  }

  function legendaAnelli(perNodi, tot, inEuro){
    const el = document.getElementById('legenda');
    if (!el) return;
    el.style.display = 'block';
    el.innerHTML = perNodi
      ? 'Anelli: ogni anello e\' un livello. Qui gli spicchi sono tutti uguali perche\' '
        + 'in questo ramo non c\'e\' ancora nessuno: appena entra qualcuno, l\'ampiezza '
        + 'diventa il numero di persone.'
      : 'Anelli: ogni anello e\' un livello, l\'ampiezza dello spicchio e\' '
        + (inEuro ? '<b>quanto ha incassato</b> quel ramo (ordini pagati, con la persona riconosciuta)'
                  : '<b>quante persone</b> stanno sotto a quel ramo (non quante posizioni)')
        + '. Totale mostrato: <b>' + (inEuro ? '€ ' : '')
        + Number(tot).toLocaleString('it-IT', inEuro ? {maximumFractionDigits:0} : {}) + '</b>. '
        + 'I rami dove non c\'e\' ancora nessuno restano come <b>fette sottili e scure</b>: '
        + 'esistono, e sono quelli su cui c\'e\' da lavorare.';
  }

  function etichetta(n){
    if (n.piu) return '▾ altri ' + abbrevia(n.restanti) + ' — clicca';
    /* IL TRONCO HA UN NOME. Il posto 0 non e' un segnaposto: e' l'admin, e
       tutti gli altri sono suoi discendenti. Se il legame c'e', si scrive il
       suo nome; se non c'e' ancora, resta la vecchia scritta. */
    if (n.tipo === 'master') return (n.nome && n.nome !== 'MASTER-NODE')
                                    ? (n.nome + ' · MASTER') : 'MASTER-NODE';
    let t = n.occupato ? n.nome : ('#' + n.posto);
    if (n.occupato) t += ' · #' + n.posto;
    return t.length > 30 ? t.slice(0,29) + '…' : t;
  }
  function abbrevia(v){
    if (v >= 1000000) return (v/1000000).toFixed(1).replace('.0','') + 'M';
    if (v >= 1000)    return (v/1000).toFixed(v >= 10000 ? 0 : 1).replace('.0','') + 'k';
    return String(v);
  }
  function mostraTip(ev, n){
    if (n.piu){
      tip.style('display','block')
         .style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px')
         .html(`<b>Altre ${Number(n.restanti).toLocaleString('it-IT')} posizioni</b><br>
                clicca per caricarne altre ${PASSO}`);
      return;
    }
    const stato = n.occupato ? (n.attivo ? 'attivo' : 'prenotato') : 'libero';
    const num = v => Number(v||0).toLocaleString('it-IT');
    /* IL NUMERO CHE UNO CERCA QUANDO PASSA COL MOUSE sta in cima e in grande:
       quante PERSONE ci sono sotto, fino in fondo. Non `figli`, che conta i
       POSTI: sotto un Pro sono 61.000 per costruzione, e sono sedie vuote. */
    const rete = Number(n.rete || 0);
    tip.style('display','block')
       .style('left',(ev.clientX+14)+'px').style('top',(ev.clientY+14)+'px')
       .html(`<b>${n.tipo === 'master'
                    ? ((n.nome && n.nome !== 'MASTER-NODE') ? (n.nome + ' — il tronco') : 'MASTER-NODE')
                    : (n.nome||('posto '+n.posto))}</b><br>
              <span style="font-size:19px;color:#f2dba4;line-height:1.5">${num(rete)}</span>
              <span style="color:#a99a80">person${rete === 1 ? 'a' : 'e'} in questo ramo</span><br>
              <span style="color:#a99a80">di cui attive</span> <b>${num(n.rete_attivi)}</b>
              <span style="color:#6d6153"> · </span>
              <span style="color:#a99a80">dirette</span> <b>${num(n.rete_diretti)}</b><br>
              ${n.rete_euro > 0 ? '<span style="color:#a99a80">incassato dal ramo</span> <b>€ '
                 + Number(n.rete_euro).toLocaleString('it-IT',{maximumFractionDigits:0}) + '</b><br>' : ''}
              <span style="color:#6d6153">posto ${n.posto} · ${n.status||''} · ${stato}</span><br>
              <span style="font-family:monospace;font-size:11px;color:#8a7c62">${n.sic||''}</span>
              ${n.preso_il ? '<br><span style="color:#6d6153">entrato il ' + n.preso_il + '</span>' : ''}
              ${n.manuale ? '<br><i>spostato a mano</i>' : ''}`);
  }

  /* ------------------------------------------------- trascina per riagganciare */
  const trascina = {attivo:false, nodo:null, bersaglio:null, appenaFinito:false, conAlt:false};
  function trascinaNodo(){
    return d3.drag()
      .filter(ev => !ev.button && ev.shiftKey === false)   /* ALT ammesso: cambia il significato del trascinamento */
      .on('start', function(ev,d){
        if (d.data.posto === 0 || d.data.piu) return;   // Master e nodi finti non si spostano
        trascina.attivo = true; trascina.nodo = d.data; trascina.bersaglio = null;
        trascina.conAlt = !!(ev.sourceEvent && ev.sourceEvent.altKey);
      })
      .on('drag', function(ev){
        if (!trascina.attivo) return;
        const px = trasformazione.invertX(ev.sourceEvent.clientX);
        const py = trasformazione.invertY(ev.sourceEvent.clientY);
        let piuVicino = null, minD = 34;
        gNodo.selectAll('g.nodo').each(function(d){
          if (d.data === trascina.nodo || d.data.piu) return;
          const dist = Math.hypot(d.px - px, d.py - py);
          if (dist < minD){ minD = dist; piuVicino = d.data; }
        });
        trascina.bersaglio = piuVicino;
        gNodo.selectAll('g.nodo').classed('bersaglio', d => d.data === piuVicino);
      })
      .on('end', async function(ev){
        gNodo.selectAll('g.nodo').classed('bersaglio', false);
        const mosso = trascina.nodo, dove = trascina.bersaglio;
        const conAlt = trascina.conAlt;
        trascina.attivo = false; trascina.nodo = null; trascina.bersaglio = null; trascina.conAlt = false;
        if (!mosso || !dove || mosso === dove) { disegna(); return; }
        trascina.appenaFinito = true;

        /* ALT premuto = sposta la PERSONA (che si porta dietro la sua gente).
           Senza ALT = sposta il POSTO con tutto il ramo, sotto il bersaglio. */
        if (conAlt){
          if (!mosso.occupato){ avvisa('Su quel posto non c\'e\' nessuno: senza ALT sposti il ramo.', false); disegna(); return; }
          const modo = dove.occupato ? 'scambia' : 'occupa';
          const dom = modo === 'scambia'
            ? `SCAMBIO\n\n${mosso.nome} (#${mosso.posto}) e ${dove.nome} (#${dove.posto}) si scambiano di posto,\nognuno con la sua gente.\n\nProcedo?`
            : `SPOSTA ${mosso.nome}\n\nda #${mosso.posto} a #${dove.posto} (${dove.status}).\nSi porta dietro la sua struttura.\nAcquisisce anche il SIC del nodo ${dove.sic}; il suo codice personale non cambia.\n\nProcedo?`;
          if (!confirm(dom)){ disegna(); return; }
          const r = await scrivi({azione:'sposta_utente', da:mosso.posto, a:dove.posto, modo});
          if (!r.ok){ avvisa(r.err || 'Non spostato', false); disegna(); return; }
          avvisa(descriviSpostamento(r), true);
          await ricarica(); return;
        }

        if (!confirm(`Innestare il posto #${mosso.posto} (con tutto il suo ramo) su #${dove.posto} (${dove.tipo === 'master' ? 'MASTER' : dove.status})?\n\nLa persona tiene il suo numero e il suo SIC.\nPer spostare invece la PERSONA su quel posto, trascina tenendo premuto ALT.`)){
          disegna(); return;
        }
        const r = await scrivi({azione:'sposta', posto:mosso.posto, padre:dove.posto});
        if (!r.ok){ avvisa('Non spostato: ' + (r.err || 'errore'), false); disegna(); return; }
        avvisa('Posto #' + mosso.posto + ' innestato su #' + dove.posto, true);
        await ricarica();
      });
  }


  /* ==========================================================================
     QUATTRO MODI DI SPOSTARE UNA PERSONA (Mirco, 15/08).

     Prima serve una distinzione che confondeva tutto:
       SPOSTARE IL POSTO   il numero di posizione, con dentro chi c'e' e tutto
                           il ramo, viene innestato su un altro nodo. La persona
                           tiene il suo numero e il suo SIC.  -> modo 'aggancia'
       SPOSTARE LA PERSONA la persona LASCIA il suo posto e ne OCCUPA un altro,
                           portandosi dietro la sua gente. Prende il SIC del
                           posto nuovo, perche' il SIC appartiene al POSTO.
                           -> modo 'occupa' (o 'scambia' se l'altro e' occupato)

     I quattro modi per farlo:
       1. PRENDI E POSA — click su "Prendi", poi click sul nodo di arrivo
       2. dal pannello del nodo, scrivendo il numero di posto
       3. trascinando il nodo con ALT premuto (senza ALT sposta il ramo)
       4. dall'elenco "Persone", riga per riga
     ========================================================================== */
  let inMano = null;     // {posto, uid, nome, sic, status}

  function prendi(n){
    if (!n || !n.occupato){ avvisa('Su quel posto non c\'e\' nessuno da prendere.', false); return; }
    inMano = {posto:n.posto, uid:n.uid, nome:n.nome, sic:n.sic, status:n.status};
    document.body.classList.add('in-mano');
    document.getElementById('mano-chi').textContent = n.nome + ' (posto #' + n.posto + ')';
    document.getElementById('barra-mano').classList.add('on');
    disegna();
  }
  function lasciaLaMano(){
    inMano = null;
    document.body.classList.remove('in-mano');
    document.getElementById('barra-mano').classList.remove('on');
    disegna();
  }
  document.getElementById('mano-annulla').onclick = lasciaLaMano;
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && inMano) lasciaLaMano(); });

  /* posa la persona che hai in mano sul nodo di destinazione */
  async function posa(dest){
    if (!inMano) return;
    if (dest.posto === inMano.posto){ lasciaLaMano(); return; }
    const daNome = inMano.nome, daPosto = inMano.posto;
    let modo = 'occupa';
    let domanda;
    if (dest.occupato){
      modo = 'scambia';
      domanda = `SCAMBIO\n\n${daNome} (posto #${daPosto}) e ${dest.nome} (posto #${dest.posto})\n`
              + `si scambiano di posto, ognuno con la sua gente.\n\nProcedo?`;
    } else {
      domanda = `SPOSTA ${daNome}\n\nda posto #${daPosto} a posto #${dest.posto} (${dest.status})\n`
              + `Si porta dietro tutta la sua struttura.\n`
              + `Acquisisce ANCHE il SIC del nodo ${dest.sic} (il suo codice personale non cambia).\n`
              + `Il posto #${daPosto} torna libero.\n\nProcedo?`;
    }
    if (!confirm(domanda)) return;
    const r = await scrivi({azione:'sposta_utente', da:daPosto, a:dest.posto, modo});
    lasciaLaMano();
    if (!r.ok){ avvisa(r.err || 'Non spostato', false); return; }
    avvisa(descriviSpostamento(r), true);
    await ricarica();
    scheda(dest.posto);
  }

  function descriviSpostamento(r){
    if (r.modo === 'scambia')
      return `${r.nome} e ${r.nome_scambiato} si sono scambiati di posto (#${r.da} ⇄ #${r.a})`;
    let t = `${r.nome} ora e' al posto #${r.a}`;
    if (r.status_nuovo) t += ' (' + r.status_nuovo + ')';
    /* il SIC del NODO si aggiunge; quello personale resta com'era */
    if (r.sic_nodo_nuovo) t += ' · prende anche il SIC ' + r.sic_nodo_nuovo;
    if (r.sic_personale)  t += ' · il suo resta ' + r.sic_personale;
    if (r.figli_seguiti) t += ` · ${r.figli_seguiti} rami lo hanno seguito`;
    return t;
  }

  /* ------------------------------------------------- elenco delle persone */
  const boxUt = document.getElementById('utenti');
  let utOffset = 0, utQuery = '';
  document.getElementById('apri-utenti').onclick = () => { boxUt.classList.add('aperto'); caricaUtenti(true); };
  document.getElementById('ut-chiudi').onclick   = () => boxUt.classList.remove('aperto');
  document.getElementById('ut-vai').onclick      = () => { utQuery = document.getElementById('ut-q').value.trim(); caricaUtenti(true); };
  document.getElementById('ut-q').addEventListener('keydown', e => {
    if (e.key === 'Enter'){ utQuery = e.target.value.trim(); caricaUtenti(true); }
  });
  document.getElementById('ut-altri').onclick = () => caricaUtenti(false);

  async function caricaUtenti(daCapo){
    if (daCapo) { utOffset = 0; document.getElementById('ut-lista').innerHTML = ''; }
    const j = await chiedi({azione:'utenti', q:utQuery, limit:60, offset:utOffset});
    const lista = document.getElementById('ut-lista');
    if (daCapo && !j.utenti.length){
      lista.innerHTML = '<p style="color:#a89482;font-size:13px">Nessuna persona piazzata'
        + (utQuery ? ' con questo criterio.' : ' ancora nella rete.') + '</p>';
      document.getElementById('ut-altri').style.display = 'none';
      return;
    }
    for (const u of j.utenti){
      const d = document.createElement('div');
      d.className = 'ut-riga'; d.dataset.posto = u.posto;
      d.innerHTML = `
        <div class="ut-nome">${u.nome} <span style="font-weight:400;color:#6b5544">· posto #${u.posto} · ${u.status}</span></div>
        <div class="ut-sic">nodo: ${u.sic}${u.sic_personale ? ' &nbsp;·&nbsp; suo: ' + u.sic_personale : ''}</div>
        <div class="ut-sic">${u.email || ''}</div>
        <div style="font-size:11.5px;color:#6b5544;margin-top:3px">
          ${u.attivo ? 'attivo' : u.stato} · livello ${u.livello} · ${u.figli} cresciuti sopra</div>
        <div class="ut-az">
          <button data-az="vai">Vedi nell'albero</button>
          <button data-az="prendi">Prendi</button>
          <input type="number" min="0" placeholder="→ posto">
          <button data-az="sposta">Sposta</button>
        </div>
        <div class="modi">
          <label><input type="radio" name="modo-${u.posto}" value="occupa" checked> va a occupare</label>
          <label><input type="radio" name="modo-${u.posto}" value="scambia"> scambia</label>
          <label><input type="radio" name="modo-${u.posto}" value="aggancia"> innesta sopra</label>
        </div>`;
      lista.appendChild(d);

      d.querySelector('[data-az="vai"]').onclick    = async () => { boxUt.classList.remove('aperto'); await vaiA(u.posto); };
      d.querySelector('[data-az="prendi"]').onclick = () => { boxUt.classList.remove('aperto'); prendi(u); };
      d.querySelector('[data-az="sposta"]').onclick = async () => {
        const v = d.querySelector('input[type=number]').value;
        if (v === ''){ avvisa('Scrivi il numero del posto di arrivo.', false); return; }
        const modo = d.querySelector(`input[name="modo-${u.posto}"]:checked`).value;
        const etichetta = modo === 'aggancia'
          ? `Innestare il posto #${u.posto} (con ${u.nome} e tutto il ramo) su #${v}?`
          : `Spostare ${u.nome} dal posto #${u.posto} al posto #${v}?\nSi porta dietro la sua struttura.`;
        if (!confirm(etichetta)) return;
        const r = await scrivi({azione:'sposta_utente', da:u.posto, a:+v, modo});
        if (!r.ok){ avvisa(r.err || 'Non spostato', false); return; }
        avvisa(modo === 'aggancia' ? `Posto #${u.posto} innestato su #${r.padre}` : descriviSpostamento(r), true);
        await ricarica(); caricaUtenti(true);
      };
    }
    utOffset += j.utenti.length;
    document.getElementById('ut-altri').style.display = j.altri > 0 ? 'inline-block' : 'none';
    document.getElementById('ut-altri').textContent = 'carica altri ' + Math.min(60, j.altri);
  }

  /* ------------------------------------------------------------- briciole */
  function briciole(){
    const el = document.getElementById('briciole');
    if (!selezionato){ el.innerHTML = '<span class="sep">nessun nodo selezionato</span>'; return; }
    const strada = [];
    (function risali(n, cammino){
      if (n.piu) return false;
      const c = cammino.concat([n]);
      if (n.posto === selezionato.posto){ strada.push(...c); return true; }
      for (const f of (n.children || [])) if (risali(f, c)) return true;
      return false;
    })(radice, []);
    el.innerHTML = strada.map((n,i) =>
      (i ? '<span class="sep">›</span>' : '') +
      `<a data-p="${n.posto}">${n.tipo === 'master' ? 'MASTER' : (n.occupato ? n.nome : '#'+n.posto)}</a>`
    ).join('');
    el.querySelectorAll('a').forEach(a => a.onclick = () => {
      const n = trova(radice, +a.dataset.p); if (n){ selezionato = n; scheda(n.posto); centra(n); }
    });
  }

  function centra(n){
    if (!n._xy) return;
    const W = window.innerWidth, H = window.innerHeight;
    const k = Math.max(trasformazione.k, 0.85);
    svg.transition().duration(420).call(zoom.transform,
      d3.zoomIdentity.translate(W/2 - 130 - n._xy.x*k, H/2 - n._xy.y*k).scale(k));
  }

  /* -------------------------------------------------------------- pannello */
  const pan = document.getElementById('pan'), corpo = document.getElementById('pan-corpo');
  document.getElementById('pan-chiudi').onclick = () => pan.classList.remove('aperto');

  async function scheda(posto){
    pan.classList.add('aperto');
    corpo.innerHTML = '<p style="color:#a89482">carico…</p>';
    let j;
    try { j = await chiedi({azione:'nodo', posto}); }
    catch(e){ corpo.innerHTML = '<p style="color:#8f1d13">Errore: '+e.message+'</p>'; return; }
    const n = j.nodo, k = j.kpi;
    const stato = n.occupato ? (n.attivo ? 'attivo' : 'prenotato') : 'libero';
    const num = v => (v === null || v === undefined || v === '') ? '—' : Number(v).toLocaleString('it-IT');
    const txt = v => (v === null || v === undefined || v === '') ? '—' : String(v);

    corpo.innerHTML = `
      <h2>${n.tipo === 'master' ? 'MASTER-NODE' : (n.occupato ? n.nome : 'Posizione libera')}</h2>
      <div class="sic"><span style="color:#a89482">SIC del posto:</span> ${txt(n.sic)}</div>
      <div style="margin-top:7px">
        <span class="pill">${txt(n.status)}</span>
        <span class="pill ${n.attivo ? 'vivo' : 'fermo'}">${stato}</span>
        <span class="pill">posto #${n.posto}</span>
        <span class="pill">livello ${n.livello}</span>
        ${n.manuale ? '<span class="pill">spostato a mano</span>' : ''}
        ${n.unicorn ? '<span class="pill">🦄 Unicorno</span>' : ''}
      </div>

      <div class="sez"><h3>Cresciuti sopra di lui</h3>
        <div class="griglia">
          <div class="box"><b>${num(k.figli)}</b><small>rami diretti</small></div>
          <div class="box"><b>${num(k.occupati)}</b><small>occupati</small></div>
          <div class="box"><b>${num(k.liberi)}</b><small>posizioni libere</small></div>
          <div class="box"><b>${num(k.attivi)}</b><small>attivi</small></div>
        </div>
        <div class="azioni">
          <button id="b-ramo">Conta tutto il ramo</button>
          <button id="b-apri">Apri qui nell'albero</button>
        </div>
        <div id="ramo-esito" style="font-size:12.5px;margin-top:7px;color:#6b5544"></div>
      </div>

      ${n.uid > 0 ? `
      <div class="sez"><h3>I due codici di questa persona</h3>
        <p style="font-size:12px;color:#6b5544;margin:0 0 8px">
          Sono <b>due cose diverse e convivono</b>: il codice personale è della persona e non cambia mai,
          nemmeno cambiando posto. Il codice del nodo è del posto e resta lì.
          Il link di invito funziona con <b>tutti e due</b>.
          Una persona occupa <b>un solo posto</b>.</p>
        <div class="riga"><span>SIC personale</span>
          <span style="font-family:monospace;font-size:11.5px">${txt(n.sic_personale)}</span></div>
        <div class="riga"><span>SIC di questo nodo</span>
          <span style="font-family:monospace;font-size:11.5px">${txt(n.sic)}</span></div>
        ${(n.sic_altri_nodi||[]).length ? `
          <div class="esito ko" style="display:block;margin-top:8px">
            <b>⚠ Questa persona occupa anche altri posti.</b> Non dovrebbe: la regola è un utente, un posto.
            ${n.sic_altri_nodi.map(x => `<br>· posto #${x.posto} (${x.status}) ${x.sic}`).join('')}
            <br>Liberane uno per rimettere le cose a posto.
          </div>` : ''}
        <div class="azioni">
          <button id="b-ref-pers">Copia ref personale</button>
          <button id="b-ref-nodo">Copia ref di questo nodo</button>
        </div>
        <div class="esito" id="es-ref"></div>
      </div>` : ''}

      <div class="sez"><h3>Chi lo occupa</h3>
        <div class="riga"><span>utente</span><span>${n.uid > 0 ? (txt(n.nome)+' (#'+n.uid+')') : '— nessuno —'}</span></div>
        <div class="riga"><span>email</span><span>${txt(n.email)}</span></div>
        <div class="riga"><span>rango</span><span>${txt(n.rango)}</span></div>
        <div class="riga"><span>DRX di merito</span><span>${num(n.drx_merito)}</span></div>
        <div class="riga"><span>attivo nel mese</span><span>${n.attivo_mese === null ? '—' : (n.attivo_mese ? 'sì' : 'no')}</span></div>
        <div class="riga"><span>preso il</span><span>${txt(n.preso_il)}</span></div>
        <div class="riga"><span>attivato il</span><span>${txt(n.attivato_il)}</span></div>
        <div class="azioni">
          <input id="i-uid" type="number" min="1" placeholder="uid utente" value="${n.uid > 0 ? n.uid : ''}">
          <button id="b-assegna">Assegna</button>
          <button id="b-libera" ${n.uid > 0 ? '' : 'disabled'}>Libera</button>
        </div>
        <div class="esito" id="es-utente"></div>
      </div>

      ${n.posto > 0 && n.posto <= 118 ? `
      <div class="sez"><h3>Rewards del nodo (privilegio dei 118)</h3>
        <p style="font-size:12px;color:#6b5544;margin:0 0 8px">
          Moltiplicatore da X1 a X8 su premi e compensi. Vale solo per i primi 118 nodi:
          dal #119 in poi resta sempre X1.</p>
        <div class="riga"><span>vale adesso</span>
          <span style="font-size:16px;color:#8b4513">X${n.boost}${n.boost_proprio ? '' : ' <small style="color:#a89482">(dalla fascia)</small>'}</span></div>
        <div class="riga"><span>voci accese</span>
          <span>${(n.boost_voci||[]).length ? n.boost_voci.join(', ') : '<span style="color:#a89482">nessuna</span>'}</span></div>
        <div class="azioni">
          <select id="i-boost">
            <option value="0"${!n.boost_proprio ? ' selected' : ''}>— dalla fascia</option>
            ${[1,2,3,4,5,6,7,8].map(v => `<option value="${v}"${n.boost_proprio===v?' selected':''}>X${v}</option>`).join('')}
          </select>
          <input id="i-boost-nota" placeholder="nota" value="${(n.boost_nota||'').replace(/"/g,'&quot;')}" style="width:110px">
          <button id="b-boost">Imposta</button>
        </div>
        <p style="font-size:11.5px;color:#a89482;margin:7px 0 0">
          Le voci su cui agisce si accendono nel <a href="admin-boost-nodi.php${KEY?('?key='+encodeURIComponent(KEY)):''}" style="color:#8b4513">pannello Rewards</a>.</p>
        <div class="esito" id="es-boost"></div>
      </div>` : ''}

      <div class="sez"><h3>Nodo e wallet</h3>
        <div class="riga"><span>tipo nodo</span><span>${txt(n.node_kind)}</span></div>
        <div class="riga"><span>listino</span><span>${n.price_eur ? num(n.price_eur)+' €' : '—'}</span></div>
        <div class="riga"><span>NFT</span><span>${txt(n.nft_num)}</span></div>
        <div class="riga"><span>wallet</span><span style="font-family:monospace;font-size:11px">${txt(n.wallet)}</span></div>
        <div class="riga"><span>stato wallet</span><span>${txt(n.wallet_status)}</span></div>
        <div class="riga"><span>POL riservati / inviati</span><span>${num(n.pol_reserved)} / ${num(n.pol_funded)}</span></div>
        <div class="riga"><span>DRX riservati / inviati</span><span>${num(n.drx_reserved)} / ${num(n.drx_funded)}</span></div>
      </div>

      <div class="sez"><h3>Sposta questa PERSONA</h3>
        ${n.uid > 0 ? `
        <p style="font-size:12px;color:#6b5544;margin:0 0 8px">
          La persona lascia il posto #${n.posto} e va a occuparne un altro,
          <b>portandosi dietro tutta la sua struttura</b>. Prende il SIC del posto nuovo.</p>
        <div class="azioni">
          <button id="b-prendi">✋ Prendi e posa</button>
          <input id="i-dove" type="number" min="0" placeholder="→ posto">
          <button id="b-vai-persona">Sposta qui</button>
        </div>
        <div class="modi" style="margin-top:7px">
          <label><input type="radio" name="modo-p" value="occupa" checked> va a occupare (dev'essere libero)</label>
          <label><input type="radio" name="modo-p" value="scambia"> scambia con chi c'e'</label>
        </div>
        <div class="esito" id="es-persona"></div>
        ` : `<p style="font-size:12px;color:#a89482;margin:0">Qui non c'e' nessuno: prima assegna un utente.</p>`}
      </div>

      <div class="sez"><h3>Sposta il POSTO (con tutto il ramo)</h3>
        <p style="font-size:12px;color:#6b5544;margin:0 0 8px">
          Il posto #${n.posto} — con chi ci sta dentro e tutto quello che gli e' cresciuto sopra —
          viene appeso a un altro nodo. <b>Numero e SIC restano gli stessi</b>: cambia solo da chi dipende.</p>
        <div class="riga"><span>padre attuale</span><span>${n.padre < 0 ? '— radice —' : '#'+n.padre}</span></div>
        <div class="riga"><span>catena</span><span>${(j.catena||[]).map(c => '#'+c.posto).join(' › ') || '—'}</span></div>
        <div class="azioni">
          <input id="i-padre" type="number" min="0" placeholder="nuovo padre" value="${n.padre >= 0 ? n.padre : ''}">
          <button id="b-sposta" ${n.posto === 0 ? 'disabled' : ''}>Appendi qui</button>
        </div>
        <div class="esito" id="es-sposta"></div>
      </div>`;

    const esito = (id, ok, msg) => {
      const e = document.getElementById(id);
      e.className = 'esito ' + (ok ? 'ok' : 'ko'); e.textContent = msg;
    };

    const copiaRef = (codice, quale) => {
      const url = location.origin + '/?ref=' + encodeURIComponent(codice);
      const mostra = (ok) => {
        const e = document.getElementById('es-ref');
        e.className = 'esito ' + (ok ? 'ok' : 'ko');
        e.innerHTML = ok ? ('Copiato il ref <b>' + quale + '</b>:<br><span style="font-family:monospace;font-size:11px">' + url + '</span>')
                         : ('Non sono riuscito a copiarlo. Eccolo:<br><span style="font-family:monospace;font-size:11px">' + url + '</span>');
      };
      if (navigator.clipboard && navigator.clipboard.writeText)
        navigator.clipboard.writeText(url).then(() => mostra(true)).catch(() => mostra(false));
      else mostra(false);
    };
    const bRefP = document.getElementById('b-ref-pers');
    if (bRefP) bRefP.onclick = () => copiaRef(n.sic_personale, 'personale');
    const bRefN = document.getElementById('b-ref-nodo');
    if (bRefN) bRefN.onclick = () => copiaRef(n.sic, 'del nodo #' + n.posto);

    const bBoost = document.getElementById('b-boost');
    if (bBoost) bBoost.onclick = async () => {
      const v = +document.getElementById('i-boost').value;
      const nota = document.getElementById('i-boost-nota').value;
      const r = await scrivi({azione:'boost', posto:n.posto, valore:v, nota});
      const msg = r.ok ? ('Posto #' + r.posto + ': ora vale X' + r.boost + (r.proprio ? '' : ' (dalla fascia)')) : r.err;
      esito('es-boost', !!r.ok, msg); avvisa(msg, !!r.ok);
      if (r.ok) scheda(n.posto);
    };

    document.getElementById('b-ramo').onclick = async () => {
      const box = document.getElementById('ramo-esito');
      box.textContent = 'conto…';
      const r = await chiedi({azione:'ramo', posto:n.posto});
      box.innerHTML = `discendenti: <b>${r.discendenti.toLocaleString('it-IT')}</b>` +
        (r.troncato ? ` <i>(fermato al tetto di ${r.tetto.toLocaleString('it-IT')}: il ramo e' piu' grande)</i>` : '') +
        ` · occupati: <b>${r.occupati.toLocaleString('it-IT')}</b> · profondita': ${r.profondita}`;
    };
    document.getElementById('b-apri').onclick = async () => {
      const nodo = trova(radice, n.posto);
      if (nodo){ selezionato = nodo; if (!apertoQ(nodo) && nodo.figli > 0) await espandi(nodo); centra(nodo); }
      else await vaiA(n.posto);
    };
    document.getElementById('b-assegna').onclick = async () => {
      const uid = +document.getElementById('i-uid').value;
      if (!uid){ esito('es-utente', false, 'Scrivi lo uid dell\'utente.'); return; }
      const r = await scrivi({azione:'assegna', posto:n.posto, uid, stato:'attivo'});
      const msg = r.ok ? ('Posto #' + n.posto + ' assegnato a ' + r.nome + ' — ora ha ' + r.posti_di_questo_utente + ' posti') : r.err;
      esito('es-utente', !!r.ok, msg); avvisa(msg, !!r.ok);
      if (r.ok){ await ricarica(); scheda(n.posto); }
    };
    document.getElementById('b-libera').onclick = async () => {
      if (!confirm('Liberare il posto #' + n.posto + '?')) return;
      const r = await scrivi({azione:'libera', posto:n.posto});
      const msg = r.ok ? ('Posto #' + n.posto + ' liberato.') : r.err;
      esito('es-utente', !!r.ok, msg); avvisa(msg, !!r.ok);
      if (r.ok){ await ricarica(); scheda(n.posto); }
    };
    document.getElementById('b-sposta').onclick = async () => {
      const p = document.getElementById('i-padre').value;
      if (p === ''){ esito('es-sposta', false, 'Scrivi il numero del nuovo padre.'); return; }
      const r = await scrivi({azione:'sposta', posto:n.posto, padre:+p});
      const msg = r.ok ? ('Posto #' + n.posto + ' innestato su #' + r.padre + ' (livello ' + r.livello + ')') : r.err;
      esito('es-sposta', !!r.ok, msg); avvisa(msg, !!r.ok);
      if (r.ok){ await ricarica(); scheda(n.posto); }
    };

    /* --- spostare la PERSONA (si porta dietro la sua struttura) --- */
    const bPrendi = document.getElementById('b-prendi');
    if (bPrendi) bPrendi.onclick = () => { pan.classList.remove('aperto'); prendi(n); };
    const bVaiP = document.getElementById('b-vai-persona');
    if (bVaiP) bVaiP.onclick = async () => {
      const dove = document.getElementById('i-dove').value;
      if (dove === ''){ esito('es-persona', false, 'Scrivi il numero del posto di arrivo.'); return; }
      const modo = document.querySelector('input[name="modo-p"]:checked').value;
      if (!confirm(`Spostare ${n.nome} dal posto #${n.posto} al posto #${dove}?\n\nSi porta dietro tutta la sua struttura.`)) return;
      const r = await scrivi({azione:'sposta_utente', da:n.posto, a:+dove, modo});
      const msg = r.ok ? descriviSpostamento(r) : r.err;
      esito('es-persona', !!r.ok, msg); avvisa(msg, !!r.ok);
      if (r.ok){ await ricarica(); scheda(+dove); }
    };
  }

  /* ------------------------------------------------------- ricerca e percorso */
  const boxRis = document.getElementById('risultati');
  async function cerca(){
    const q = document.getElementById('q').value.trim();
    if (!q){ boxRis.style.display = 'none'; return; }
    const j = await chiedi({azione:'cerca', q, limit:20});
    if (!j.risultati.length){ boxRis.innerHTML = '<div>nessun risultato</div>'; boxRis.style.display='block'; return; }
    boxRis.innerHTML = j.risultati.map(r =>
      `<div data-p="${r.posto}" data-path="${r.percorso.join(',')}">
         <b>${r.occupato ? r.nome : ('posto #'+r.posto)}</b> · ${r.status}
         <div class="sicm">${r.sic}</div></div>`).join('');
    boxRis.style.display = 'block';
    boxRis.querySelectorAll('div[data-p]').forEach(d => d.onclick = async () => {
      boxRis.style.display = 'none';
      await vaiA(+d.dataset.p, d.dataset.path.split(',').map(Number));
    });
  }
  document.getElementById('vai').onclick = cerca;
  document.getElementById('q').addEventListener('keydown', e => { if (e.key === 'Enter') cerca(); });
  document.addEventListener('click', e => {
    if (!boxRis.contains(e.target) && e.target.id !== 'q' && e.target.id !== 'vai') boxRis.style.display = 'none';
  });

  /* apre in sequenza tutti i rami del percorso, poi centra */
  async function vaiA(posto, percorso){
    if (!percorso){
      const j = await chiedi({azione:'nodo', posto});
      percorso = (j.catena || []).map(c => c.posto).concat([posto]);
    }
    let cur = radice;
    for (const p of percorso){
      if (p === radice.posto) { cur = radice; continue; }
      if (!apertoQ(cur) && cur.figli > 0) await espandi(cur);
      let succ = (cur.children || []).find(c => c.posto === p);
      /* Se il figlio non e' nella pagina caricata NON si sfoglia a vuoto: si
         chiede all'API in quale pagina sta (&contiene=) e ci si salta sopra.
         Prima si sfogliava avanti fino a 40 volte e un figlio in posizione
         60.000 su 60.976 non veniva MAI raggiunto: la navigazione si fermava
         al padre e disegnava 3.300 nodi inutili. */
      if (!succ){
        const j = await chiedi({azione:'figli', posto:cur.posto, limit:PASSO, contiene:p});
        const trovati = j.figli.map(f => prepara(f));
        const gia = new Set((cur._figli||[]).filter(x=>!x.piu).map(x=>x.posto));
        cur._figli = (cur._figli||[]).filter(x=>!x.piu).concat(trovati.filter(f => !gia.has(f.posto)));
        cur.altri = j.altri;
        aggiornaAltri(cur);
        cur.children = cur._figli;
        succ = cur.children.find(c => c.posto === p);
      }
      if (!succ) break;
      cur = succ;
    }
    selezionato = cur;
    if (!apertoQ(cur) && cur.figli > 0) await espandi(cur);
    disegna(); centra(cur); scheda(cur.posto);
  }

  /* --------------------------------------------------------------- comandi */
  const FORME = ['albero', 'orizzontale', 'ventaglio', 'stella', 'anelli', 'griglia'];
  const ETICHETTA_FORMA = {albero:'🌳 Albero', orizzontale:'⇥ Orizzontale',
                           ventaglio:'⌒ Ventaglio', stella:'✦ Stella', anelli:'◎ Anelli',
                           griglia:'▦ Mappa 118'};

  /* il peso degli Anelli: persone o euro */
  document.getElementById('b-peso').onclick = () => {
    pesoPer = (pesoPer === 'euro') ? 'persone' : 'euro';
    if (forma !== 'anelli'){
      forma = 'anelli';
      document.getElementById('forma').textContent = ETICHETTA_FORMA.anelli;
    }
    disegna(); adatta();
    avvisa(pesoPer === 'euro' ? 'Anelli pesati sugli incassi.' : 'Anelli pesati sulle persone.', true);
  };
  /* il bottone dice sempre la verita': arrivando da ?forma=stella deve gia'
     leggersi "Stella", se no il menu accende la voce sbagliata */
  document.getElementById('forma').textContent = ETICHETTA_FORMA[forma];
  document.getElementById('forma').onclick = () => {
    forma = FORME[(FORME.indexOf(forma) + 1) % FORME.length];
    document.getElementById('forma').textContent = ETICHETTA_FORMA[forma];
    disegna(); adatta();
  };

  /* ======================================================================
     ISOLA IL RAMO — dentro cinque milioni di posizioni, lavorare "su tutto"
     non si puo'. Isolando, il nodo scelto diventa la radice: sullo schermo
     resta solo casa sua. Non si perde niente e non si ricarica niente: la
     radice vera aspetta nella pila.
  ====================================================================== */
  async function isola(n){
    if (!n || n.piu) return;
    if (n.posto === radice.posto) { avvisa('Sei gia\' qui.', false); return; }
    pilaIsola.push(radice);
    radice = n;
    if (!apertoQ(n) && n.figli > 0) { try { await espandi(n); } catch(e){} }
    selezionato = n;
    disegna(); adatta(); statoVista();
  }
  function tornaATutto(){
    if (!pilaIsola.length) return;
    radice = pilaIsola[0];
    pilaIsola.length = 0;
    disegna(); adatta(); statoVista();
  }
  document.getElementById('b-isola').onclick  = () => isola(selezionato);
  document.getElementById('b-tutto').onclick  = tornaATutto;

  /* ======================================================================
     SOLO PERSONE — il filtro. Acceso, l'API manda solo i posti occupati.
     Cambiando filtro si dimentica quello che era gia' stato caricato: se no
     restano a schermo i posti liberi della richiesta di prima e il filtro
     sembrerebbe rotto.
  ====================================================================== */
  function scorda(n){
    (n._figli || []).forEach(scorda);
    n._figli = null; n.children = null; n.caricato = 0; n.offset = 0;
  }
  async function cambiaFiltro(){
    soloOccupati = !soloOccupati;
    const eraQui = selezionato ? selezionato.posto : 0;
    scorda(radice);
    try { await espandi(radice, true); } catch(e){}
    if (soloOccupati){
      /* con il filtro acceso si aprono subito due piani: e' il motivo per cui
         lo si accende, vedere le persone senza scavare */
      for (const w of (radice.children || [])) {
        if (!apertoQ(w) && w.figli > 0) { try { await espandi(w); } catch(e){} }
      }
    }
    if (eraQui) { const n = trova(radice, eraQui); if (n) selezionato = n; }
    disegna(); adatta(); statoVista();
    avvisa(soloOccupati ? 'Filtro acceso: solo le posizioni con una persona dentro.'
                        : 'Filtro spento: si vedono anche le posizioni libere.', true);
  }
  document.getElementById('b-filtro').onclick = cambiaFiltro;

  /* PROFONDITA' — quanti piani mostrare. Gira 0 (tutto) → 2 → 3 → 4 → 5 → 0. */
  document.getElementById('b-prof').onclick = () => {
    const giro = [0, 2, 3, 4, 5];
    profMax = giro[(giro.indexOf(profMax) + 1) % giro.length];
    disegna(); adatta(); statoVista();
    avvisa(profMax ? ('Mostro ' + profMax + ' piani.') : 'Nessun limite di profondita\'.', true);
  };

  /* ======================================================================
     IL LINK A QUESTA VISTA — vista, filtro, profondita' e nodo scelto dentro
     un indirizzo. Serve a mandare a qualcuno esattamente quello che stai
     guardando, invece di spiegarglielo a parole.
  ====================================================================== */
  function linkVista(){
    const q = new URLSearchParams();
    q.set('forma', forma);
    if (forma === 'anelli' && pesoPer === 'euro') q.set('peso', 'euro');
    if (KEY) q.set('key', KEY);
    if (selezionato && selezionato.posto > 0) q.set('posto', selezionato.posto);
    if (soloOccupati) q.set('solo', '1');
    if (profMax) q.set('liv', String(profMax));
    return location.origin + location.pathname + '?' + q.toString();
  }
  document.getElementById('b-link').onclick = async () => {
    const u = linkVista();
    try { await navigator.clipboard.writeText(u); avvisa('Link copiato.', true); }
    catch(e){ window.prompt('Copia il link:', u); }
  };

  /* L'IMMAGINE — l'SVG diventa un PNG. Serve per metterla in una slide o in
     un messaggio senza fare uno screenshot storto. Il fondo lo dipingo io:
     un SVG trasparente su carta bianca diventa illeggibile. */
  document.getElementById('b-png').onclick = () => {
    try{
      const nodo = document.getElementById('svg');
      const W = nodo.clientWidth || 1600, H = nodo.clientHeight || 900;
      const clone = nodo.cloneNode(true);
      clone.setAttribute('xmlns','http://www.w3.org/2000/svg');
      clone.setAttribute('width', W); clone.setAttribute('height', H);
      const testo = new XMLSerializer().serializeToString(clone);
      const img = new Image();
      img.onload = () => {
        const c = document.createElement('canvas');
        c.width = W * 2; c.height = H * 2;
        const x = c.getContext('2d');
        x.fillStyle = '#0a0908'; x.fillRect(0,0,c.width,c.height);
        x.drawImage(img, 0, 0, c.width, c.height);
        const a = document.createElement('a');
        a.download = 'rete-' + forma + '.png';
        a.href = c.toDataURL('image/png');
        a.click();
        avvisa('Immagine scaricata.', true);
      };
      img.onerror = () => avvisa('Non sono riuscito a fare l\'immagine.', false);
      img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(testo);
    }catch(e){ avvisa('Non sono riuscito a fare l\'immagine.', false); }
  };

  document.getElementById('b-centra').onclick = () => { if (selezionato) centra(selezionato); };

  /* ======================================================================
     IL CRUSCOTTO — i numeri della rete, accanto al disegno.
     Regola dietro a ogni riga: quello che non si sa NON si scrive come zero.
     Le note in fondo dicono cosa manca e perche', invece di lasciare che uno
     legga un numero incompleto pensando che sia completo.
  ====================================================================== */
  const f0 = v => Number(v || 0).toLocaleString('it-IT');
  async function apriNumeri(){
    const box = document.getElementById('numeri');
    box.classList.add('aperto');
    const c = document.getElementById('numeri-corpo');
    c.textContent = 'carico…';
    let n;
    try { n = (await chiedi({azione:'numeri'})).numeri; }
    catch(e){ c.textContent = 'Non riesco a leggere i numeri: ' + e.message; return; }

    const maxRamo = Math.max(1, ...(n.rami || []).map(r => r.persone));
    const tassoAtt = n.persone ? Math.round(n.attive / n.persone * 100) : 0;

    c.innerHTML =
      '<div class="grid">'
      + cella(f0(n.persone), 'persone in rete')
      + cella(f0(n.attive) + ' <span style="font-size:13px;color:#a99a80">(' + tassoAtt + '%)</span>', 'di cui attive')
      + cella(f0(n.nodi_venduti) + '<span style="font-size:13px;color:#a99a80">/118</span>', 'nodi assegnati')
      + cella(f0(n.entrate.trenta), 'entrate negli ultimi 30 giorni')
      + '</div>'

      + '<h3>Come stanno messe</h3>'
      + riga('confermate (email verificata)', f0(n.confermate))
      + riga('prenotate, non ancora confermate', f0(n.prenotate))
      + riga('attive (dentro premi e ranghi)', f0(n.attive))

      + '<h3>Quando sono entrate</h3>'
      + riga('oggi', f0(n.entrate.oggi))
      + riga('ultimi 7 giorni', f0(n.entrate.sette))
      + riga('ultimi 30 giorni', f0(n.entrate.trenta))

      + '<h3>Su quanti piani</h3>'
      + (n.per_livello || []).map(l =>
          riga('livello ' + l.livello, f0(l.persone))).join('')
      + (n.per_livello && n.per_livello.length ? '' : '<div class="riga">nessuno ancora</div>')

      + '<h3>I rami che si muovono</h3>'
      + (n.rami || []).filter(r => r.persone > 0).map(r =>
          '<div class="riga"><a data-p="' + r.posto + '">#' + r.posto + ' · '
          + (r.tipo || 'nodo') + '</a><span class="n">' + f0(r.persone) + '</span></div>'
          + '<div class="barretta"><i style="width:' + Math.round(r.persone / maxRamo * 100) + '%"></i></div>'
        ).join('')
      + ((n.rami || []).filter(r => r.persone > 0).length ? ''
         : '<div class="riga">nessun ramo ha ancora qualcuno sotto</div>')

      + '<h3>Dove non succede niente</h3>'
      + riga('nodi dei 118 senza nessuno sotto', f0(n.vuoti))
      + riga('nodi ancora da assegnare', f0(n.nodi_liberi))

      + '<h3>La cassa</h3><div id="cassa">leggo gli ordini…</div>'
      + '<h3>La crescita</h3><div id="crescita">leggo le date…</div>'
      + '<h3>Chi si e\' fermato</h3>'
      + '<div style="font-size:12px;color:#a99a80;margin-bottom:7px">Non e\' una classifica dei '
      + 'peggiori: e\' l\'elenco di chi va richiamato. Chi e\' entrato da meno giorni della '
      + 'soglia non ci finisce dentro: e\' nuovo, non fermo.</div>'
      + '<div style="margin-bottom:8px">soglia: '
      + [7,14,30,60].map(g => '<button class="sg" data-g="' + g + '">' + g + ' giorni</button>').join(' ')
      + '</div><div id="fermi">—</div>'

      + (n.note || []).map(x => '<div class="nota-n">' + x + '</div>').join('')

      + '<div class="franco">Qui dentro ci sono solo conteggi su dati veri. '
      + 'Il <b>sentiment</b> non c\'e\' perche\' non lo raccoglie ancora nessuno: quando ci sara\' '
      + 'un voto nel Covo, comparira\' qui. Inventarlo adesso renderebbe inutile tutto il resto.</div>';

    /* dai numeri si salta dentro l'albero: e' il senso di averli qui */
    function collega(){
      c.querySelectorAll('a[data-p]').forEach(a => a.onclick = async () => {
        box.classList.remove('aperto');
        try { await vaiA(+a.dataset.p); } catch(e){}
      });
    }
    collega();

    /* --- LA CASSA. Il totale, e quanto di quel totale NON e' attribuito a
       nessuno: senza quella riga uno somma i rami, non trova il totale e
       pensa che il tool sbagli. --- */
    chiedi({azione:'cassa'}).then(k => {
      const el = document.getElementById('cassa'); if (!el) return;
      const eur = v => '€ ' + Number(v||0).toLocaleString('it-IT', {minimumFractionDigits:2, maximumFractionDigits:2});
      el.innerHTML = riga('incassato (ordini ' + k.stati.slice(0,2).join('/') + ')', eur(k.totale))
        + riga('ordini contati', f0(k.ordini))
        + (k.senza_persona > 0
            ? riga('<span style="color:#e0a08c">di cui non attribuito a nessuno</span>', eur(k.senza_persona))
            : '')
        + '<div style="font-size:12px;color:#6d6153;margin-top:6px;line-height:1.5">'
        + 'Un ordine finisce su un ramo solo se dentro <code>customer</code> c\'e\' <code>uid:</code>. '
        + 'Quelli senza restano nel totale ma non in nessun ramo — per questo la somma dei rami '
        + 'puo\' essere piu\' bassa del totale. I pagamenti a meta\' (<code>paid_partial</code>) '
        + 'non contano.</div>';
    }).catch(() => { const el = document.getElementById('cassa'); if (el) el.textContent = 'ordini non leggibili'; });

    /* --- LA CRESCITA. Un grafico a barre, un giorno per barra. I giorni a
       zero restano: un giorno senza nessuno e' un dato. --- */
    chiedi({azione:'crescita', giorni:30}).then(k => {
      const el = document.getElementById('crescita'); if (!el) return;
      const g = k.dati.giorni || [];
      const max = Math.max(1, ...g.map(x => x.entrate));
      const W = 380, H = 74, pw = W / Math.max(1, g.length);
      el.innerHTML =
        '<svg width="' + W + '" height="' + H + '" style="display:block">'
        + g.map((x,i) => {
            const h = Math.round(x.entrate / max * (H - 16));
            return '<rect x="' + (i*pw+0.7).toFixed(1) + '" y="' + (H-h-12) + '" width="' + (pw-1.4).toFixed(1)
                 + '" height="' + Math.max(h, x.entrate ? 2 : 0) + '" fill="' + (x.entrate ? '#d9b45a' : '#2a2418')
                 + '"><title>' + x.giorno + ': ' + x.entrate + '</title></rect>';
          }).join('')
        + '<text x="0" y="' + (H-2) + '" fill="#6d6153" font-size="10" font-family="Georgia,serif">'
        + (g[0] ? g[0].giorno : '') + '</text>'
        + '<text x="' + W + '" y="' + (H-2) + '" text-anchor="end" fill="#6d6153" font-size="10" '
        + 'font-family="Georgia,serif">oggi</text></svg>'
        + riga('entrate in 30 giorni', f0(g.reduce((a,x) => a + x.entrate, 0)))
        + riga('massimo in un giorno', f0(max))
        + (k.dati.senza_data > 0
            ? '<div class="nota-n">' + f0(k.dati.senza_data) + ' persone senza data d\'ingresso: '
              + 'non sono in questo grafico</div>' : '');
    }).catch(() => { const el = document.getElementById('crescita'); if (el) el.textContent = 'date non leggibili'; });

    /* --- CHI SI E' FERMATO --- */
    async function caricaFermi(giorni){
      const el = document.getElementById('fermi'); if (!el) return;
      el.textContent = 'cerco…';
      c.querySelectorAll('.sg').forEach(b => b.classList.toggle('on', +b.dataset.g === giorni));
      let d;
      try { d = (await chiedi({azione:'fermi', giorni, limite:40})).dati; }
      catch(e){ el.textContent = 'non riesco a leggere le date'; return; }
      if (!d.fermi.length){
        el.innerHTML = '<div style="font-size:13px;color:#a99a80">Nessuno fermo da piu\' di '
          + giorni + ' giorni. Su ' + f0(d.guardati) + ' persone guardate.</div>';
        return;
      }
      el.innerHTML =
        '<div style="font-size:12px;color:#a99a80;margin-bottom:6px">'
        + f0(d.quanti) + ' ferm' + (d.quanti === 1 ? 'a' : 'e') + ' da piu\' di ' + giorni
        + ' giorni, su ' + f0(d.guardati) + ' guardate'
        + (d.fermi.length < d.quanti ? ' — qui i primi ' + d.fermi.length : '') + '</div>'
        + d.fermi.map(f =>
            '<div class="riga"><a data-p="' + f.posto + '">'
            + (f.nome || ('#' + f.posto)) + ' <span style="color:#6d6153">#' + f.posto + '</span></a>'
            + '<span class="n">' + f.fermo_da + ' gg'
            + (f.mai ? ' <span style="color:#e0a08c;font-size:11px">mai nessuno</span>' : '')
            + '</span></div>').join('')
        + (d.senza_data > 0 ? '<div class="nota-n">' + f0(d.senza_data)
            + ' persone senza data: non si puo\' dire se sono ferme, quindi restano fuori</div>' : '');
      collega();
    }
    c.querySelectorAll('.sg').forEach(b => b.onclick = () => caricaFermi(+b.dataset.g));
    caricaFermi(14);
  }
  const cella = (v, n) => '<div class="cella"><div class="v">' + v + '</div><div class="n">' + n + '</div></div>';
  const riga  = (t, v) => '<div class="riga"><span>' + t + '</span><span class="n">' + v + '</span></div>';
  document.getElementById('b-numeri').onclick = apriNumeri;

  /* ======================================================================
     LE FRECCE — muoversi di nodo in nodo senza mouse.
     Attenzione al verso: nella vista Albero i figli stanno SOPRA, quindi la
     freccia su va verso le punte e la freccia giu' verso il tronco. Nelle
     altre viste vale il verso normale (su = chi ti ha portato). Se le frecce
     andassero sempre "verso il padre" nell'albero rovesciato sembrerebbero
     rotte.
  ====================================================================== */
  async function vaiVicino(dir){
    if (!radice) return;
    if (!selezionato){ selezionato = radice; disegna(); centra(selezionato); return; }
    const versoFigli = (forma === 'albero') ? 'su' : 'giu';
    const p = mappaPadre.get(selezionato.posto);
    if (dir === versoFigli){
      if (!apertoQ(selezionato) && selezionato.figli > 0) { try { await espandi(selezionato); } catch(e){} }
      const f = (selezionato.children || []).filter(x => !x.piu);
      if (f.length) selezionato = f[0]; else { avvisa('Qui non c\'e\' nessuno sotto.', false); return; }
    } else if (dir === 'su' || dir === 'giu'){
      if (!p) { avvisa('Sei alla radice della vista.', false); return; }
      selezionato = p;
    } else {
      if (!p) return;
      const f = (p.children || []).filter(x => !x.piu);
      const i = f.findIndex(x => x.posto === selezionato.posto);
      if (i < 0) return;
      selezionato = f[(i + (dir === 'dx' ? 1 : -1) + f.length) % f.length];
    }
    disegna(); centra(selezionato); scheda(selezionato.posto);
  }
  window.addEventListener('keydown', ev => {
    const t = ev.target;
    if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
    const m = {ArrowUp:'su', ArrowDown:'giu', ArrowLeft:'sx', ArrowRight:'dx'}[ev.key];
    if (!m) return;
    ev.preventDefault();
    vaiVicino(m);
  });

  /* il cartellino che dice cosa e' acceso: filtro, isolamento, profondita' */
  function statoVista(){
    const el = document.getElementById('stato-vista');
    if (!el) return;
    const voci = [];
    if (pilaIsola.length) voci.push('ramo isolato su <b>' +
      (radice.tipo === 'master' ? 'MASTER' : (radice.nome || ('#' + radice.posto))) + '</b>');
    if (soloOccupati) voci.push('<b>solo persone</b>');
    if (profMax) voci.push('<b>' + profMax + ' piani</b>');
    if (!voci.length){ el.style.display = 'none'; el.innerHTML = ''; return; }
    el.style.display = 'block';
    el.innerHTML = voci.join(' · ') + '<button id="sv-azzera">torna a tutto</button>';
    document.getElementById('sv-azzera').onclick = async () => {
      profMax = 0;
      if (pilaIsola.length) tornaATutto();
      if (soloOccupati) await cambiaFiltro();
      disegna(); adatta(); statoVista();
    };
  }

  document.getElementById('tutti-world').onclick = async () => {
    if (!apertoQ(radice)) await espandi(radice);
    for (const w of (radice.children || [])) if (!apertoQ(w) && w.figli > 0) await espandi(w);
    disegna(); adatta();
  };
  /* ULTIMI ARRIVATI — apre la strada fino all'ultimo entrato.
     Serve perche' dopo un caricamento di massa i nuovi finiscono al quarto
     livello, appesi agli 82 Pro, e la vista di partenza arriva al secondo:
     ci sono, ma sembra di no. */
  document.getElementById('ultimi').onclick = async () => {
    const b = document.getElementById('ultimi');
    b.disabled = true;
    try {
      const j = await chiedi({azione:'ultimi', quanti:25});
      const u = j.ultimi || [];
      if (!u.length) { alert('Nessuna posizione risulta ancora presa da qualcuno.'); return; }
      const primo = u[0];
      await vaiA(primo.posto, primo.percorso);
      const righe = u.slice(0, 12).map(x => '#' + x.posto + '  ' + x.nome + (x.stato ? '  (' + x.stato + ')' : ''));
      avvisaUltimi('Ultimi ' + u.length + ' entrati. Ti ho portato sul piu\' recente:\n\n' + righe.join('\n'));
    } catch (e) {
      alert('Non riesco a leggere gli ultimi arrivati: ' + e.message);
    } finally { b.disabled = false; }
  };
  function avvisaUltimi(t){
    if (typeof avvisa === 'function') { avvisa(t.split('\n')[0], true); console.log(t); }
    else alert(t);
  }

  document.getElementById('richiudi').onclick = () => {
    (function chiudiTutto(n){ if (n.children){ n.children.forEach(chiudiTutto); if (n !== radice) n.children = null; } })(radice);
    disegna(); adatta();
  };
  const avviso = document.getElementById('fix-topo');
  if (avviso) avviso.onclick = async () => {
    avviso.disabled = true; avviso.textContent = 'riallineo…';
    const r = await scrivi({azione:'resync'});
    if (r.ok){
      alert(`Struttura riallineata.\n\nposti corretti: ${r.cambiati}\ngia' a posto: ${r.gia_corretti}\nsaltati perche' spostati a mano: ${r.saltati_manuali}\nsaltati perche' venduti: ${r.saltati_occupati}\n\nWorld sotto il MASTER: ${r.world_sotto_master}`);
      location.reload();
    } else { alert('Non riuscito: ' + (r.err||'')); avviso.disabled = false; avviso.textContent = 'Riallinea adesso'; }
  };

  /* Incornicia l'albero visibile: lo scala per farlo stare tutto e lo CENTRA
     nello spazio utile (che e' lo schermo meno il pannello di destra quando e'
     aperto). Il tetto di k a 1.6 evita che un albero con 3 soli nodi diventi
     una gigantografia. */
  function adatta(){
    /* gli Anelli non hanno pallini: i loro punti stanno sugli spicchi.
       Senza questa riga la vista restava incastrata nell'angolo in alto a
       sinistra, perche' qui non trovava niente da inquadrare e usciva. */
    let nodi;
    if (forma === 'anelli')      nodi = gArchi.selectAll('path').data();
    else if (forma === 'griglia'){
      /* si inquadrano i centri delle caselle PIU' una spanna sopra: se no la
         scritta "WORLD · 9" della prima fila finisce sotto la barra e sembra
         che manchi una riga. */
      nodi = gMappa.selectAll('g.cas').data().filter(d => !d.testata)
                   .map(d => ({px: d.x + 37, py: d.y + 37}));
      if (nodi.length) nodi = nodi.concat([{px: nodi[0].px, py: Math.min(...nodi.map(n => n.py)) - 52}]);
    }
    else                          nodi = gNodo.selectAll('g.nodo').data();
    if (!nodi.length) return;
    const xs = nodi.map(d => d.px), ys = nodi.map(d => d.py);
    const x0 = Math.min(...xs), x1 = Math.max(...xs);
    const y0 = Math.min(...ys), y1 = Math.max(...ys);

    const panAperto = document.getElementById('pan').classList.contains('aperto');
    const margineDx = panAperto ? 390 : 30;
    const bordo = 120;                       // spazio per le etichette a sinistra
    const Wutile = window.innerWidth - margineDx - bordo - 40;
    const Hutile = window.innerHeight - 150;

    const largh = Math.max(1, x1 - x0);
    const alt   = Math.max(1, y1 - y0);
    const k = Math.max(0.08, Math.min(1.6, 0.94 * Math.min(Wutile / (largh + 260), Hutile / (alt + 70))));

    /* centro orizzontale e verticale dello spazio utile */
    const cx = bordo + Wutile / 2;
    const cy = 70 + Hutile / 2;

    /* L'albero poggia in BASSO, come un albero vero: il tronco appoggiato al
       fondo dello schermo e lo spazio libero sopra, dove i rami cresceranno.
       y1 e' la quota del tronco (la piu' alta in valore, perche' salendo le
       y diventano negative). Le altre due forme restano centrate. */
    const ty = (forma === 'albero')
      ? (window.innerHeight - 118 - y1 * k)
      : (cy - ((y0 + y1) / 2) * k);

    autoZoomAttivo = false;
    svg.transition().duration(480).call(zoom.transform,
      d3.zoomIdentity.translate(cx - ((x0 + x1) / 2) * k, ty).scale(k))
      .on('end', () => { autoZoomAttivo = true; });
  }

  async function ricarica(){
    const apertiPrima = [];
    (function raccogli(n){ if (n.children){ apertiPrima.push(n.posto); n.children.forEach(raccogli); } })(radice);
    const j = await chiedi({azione:'vista'});
    radice = prepara(j.albero);
    radice.children = radice._figli;
    for (const p of apertiPrima){
      const n = trova(radice, p);
      if (n && !apertoQ(n) && n.figli > 0) { try { await espandi(n); } catch(e){} }
    }
    disegna();
    aggiornaKpi();
  }

  async function aggiornaKpi(){
    try{
      const j = await chiedi({azione:'riepilogo'});
      const r = j.riepilogo, f = v => Number(v).toLocaleString('it-IT');
      document.getElementById('k-posti').textContent = f(r.posti);
      document.getElementById('k-occ').textContent   = f(r.occupati);
      document.getElementById('k-att').textContent   = f(r.attivi);
      document.getElementById('k-user').textContent  = f(r.user);
    }catch(e){}
  }

  /* ------------------------------------------------------------------ avvio */
  (async function(){
    try{
      const j = await chiedi({azione:'vista'});
      radice = prepara(j.albero);
      radice.children = radice._figli;        // Master gia' aperto sui 9 World
      /* i World arrivano coi loro National gia' dentro: li apro subito, come
         chiesto ("di default i 9 world-node e relativi national-node") */
      for (const w of (radice.children || [])) if (w._figli) w.children = w._figli;
      disegna();
      adatta();
      /* il link a una vista riporta esattamente a quella vista: filtro,
         profondita' e nodo. Nell'ordine giusto: prima il filtro (ricarica i
         figli), poi il nodo, se no il nodo si perde nel ricaricamento. */
      if (_soloIniziale) { try { await cambiaFiltro(); } catch(e){} }
      const _p0 = parseInt(_qs.get('posto') || '0', 10);
      if (_p0 > 0) { try { await vaiA(_p0); } catch(e){} }
      statoVista();
    }catch(e){
      document.getElementById('carico').style.display = 'block';
      document.getElementById('carico').textContent = 'Non riesco a leggere la struttura: ' + e.message;
    }
  })();

  window.addEventListener('resize', () => disegna());
})();
</script>

<div class="velo" id="velo"></div>
<div id="menu">
  <button class="chiudi" onclick="chiudiMenu()">✕</button>
  <h2>Comandi</h2>

  <div class="gr"><h3>Vista</h3>
    <p class="spiega">Cinque modi di guardare la stessa rete. E' un tool solo: la vecchia
    pagina Stella ora porta qui.</p>
    <button class="cmd" data-vista="albero"><span>🌳 Albero — dal basso</span><kbd>1</kbd></button>
    <button class="cmd" data-vista="orizzontale"><span>⇥ Laterale — da sinistra</span><kbd>2</kbd></button>
    <button class="cmd" data-vista="ventaglio"><span>⌒ Ventaglio — mezza stella, verso l'alto</span><kbd>3</kbd></button>
    <button class="cmd" data-vista="stella"><span>✦ Stella — dal centro</span><kbd>4</kbd></button>
    <button class="cmd" data-vista="anelli"><span>◎ Anelli — quanto pesa ogni ramo</span><kbd>5</kbd></button>
    <button class="cmd" data-vista="griglia"><span>▦ Mappa dei 118 — tutto in una schermata</span><kbd>6</kbd></button>
    <button class="cmd" data-clic="b-peso"><span>€ Anelli: pesa sugli incassi invece che sulle persone</span><kbd>X</kbd></button>
    <p class="spiega">Negli <b>Anelli</b> l'ampiezza dello spicchio e' quante <b>persone</b>
    stanno sotto a quel ramo — non quante posizioni, se no peserebbero tutte uguale.</p>
  </div>
  <div class="gr"><h3>Muoversi</h3>
    <button class="cmd" data-clic="tutti-world"><span>⤢ Apri i World e i National</span><kbd>W</kbd></button>
    <button class="cmd" data-clic="ultimi"><span>⚡ Vai agli ultimi arrivati</span><kbd>U</kbd></button>
    <button class="cmd" data-clic="richiudi"><span>⤡ Richiudi tutto</span><kbd>R</kbd></button>
    <button class="cmd" data-az="fit"><span>⊹ Incornicia lo schermo</span><kbd>F</kbd></button>
    <button class="cmd" data-az="cerca"><span>🔍 Vai alla ricerca</span><kbd>/</kbd></button>
    <button class="cmd" data-clic="b-centra"><span>◎ Centra sul nodo scelto</span><kbd>C</kbd></button>
    <p class="spiega">Con le <b>frecce</b> ti muovi di nodo in nodo: nell'Albero ↑ va verso
    le punte e ↓ verso il tronco, perche' li' i figli stanno sopra.</p>
  </div>
  <div class="gr"><h3>Restringere il campo</h3>
    <p class="spiega">Cinque milioni di posizioni non si guardano tutte insieme. Questi tre
    comandi servono a togliere di mezzo quello che adesso non ti serve.</p>
    <button class="cmd" data-clic="b-isola"><span>⛶ Isola questo ramo</span><kbd>I</kbd></button>
    <button class="cmd" data-clic="b-tutto"><span>⤺ Torna a tutta la rete</span><kbd>B</kbd></button>
    <button class="cmd" data-clic="b-filtro"><span>👣 Solo le posizioni con una persona</span><kbd>O</kbd></button>
    <button class="cmd" data-clic="b-prof"><span>▤ Quanti piani mostrare</span><kbd>L</kbd></button>
  </div>
  <div class="gr"><h3>Portare fuori</h3>
    <button class="cmd" data-clic="b-link"><span>🔗 Copia il link a questa vista</span><kbd>K</kbd></button>
    <button class="cmd" data-clic="b-png"><span>🖼 Scarica l'immagine (PNG)</span><kbd>S</kbd></button>
    <p class="spiega">Il link porta chi lo apre esattamente qui: stessa vista, stesso filtro,
    stesso nodo.</p>
  </div>
  <div class="gr"><h3>I numeri</h3>
    <button class="cmd" data-clic="b-numeri"><span>📊 Il cruscotto della rete</span><kbd>N</kbd></button>
    <p class="spiega">Persone, attive, entrate degli ultimi giorni, i rami che si muovono e
    quelli fermi. Da ogni riga si salta dentro l'albero.</p>
  </div>
  <div class="gr"><h3>Persone</h3>
    <button class="cmd" data-clic="apri-utenti"><span>👤 Elenco persone e spostamenti</span><kbd>P</kbd></button>
    <p class="spiega">Trascinare un nodo su un altro appende il RAMO. Con ALT premuto sposta
    la PERSONA e basta. Click su un nodo: apre e mostra la scheda.</p>
  </div>
  <div class="gr"><h3>Altri strumenti</h3>
    <a class="cmd" href="admin-struttura-rami.php<?= $APIKEY ? '?key='.rawurlencode($APIKEY) : '' ?>"><span>🌿 Forma dei rami</span><kbd>→</kbd></a>
    <a class="cmd" href="admin-lead-in-rete.php<?= $APIKEY ? '?key='.rawurlencode($APIKEY) : '' ?>"><span>📥 Lead in rete</span><kbd>→</kbd></a>
    <a class="cmd" href="admin-iscritti-rete.php<?= $APIKEY ? '?key='.rawurlencode($APIKEY) : '' ?>"><span>👥 Iscritti → rete</span><kbd>→</kbd></a>
    <a class="cmd" href="admin-premi-gate.php<?= $APIKEY ? '?key='.rawurlencode($APIKEY) : '' ?>"><span>🔒 Membership e premi</span><kbd>→</kbd></a>
    <a class="cmd" href="admin-boost-nodi.php<?= $APIKEY ? '?key='.rawurlencode($APIKEY) : '' ?>"><span>⚡ Moltiplicatore dei 118</span><kbd>→</kbd></a>
    <a class="cmd" href="admin-preflight.php<?= $APIKEY ? '?key='.rawurlencode($APIKEY) : '' ?>"><span>🚦 Preflight</span><kbd>→</kbd></a>
    <a class="cmd" href="../admin.php"><span>← Dashboard</span><kbd>→</kbd></a>
  </div>
</div>

<script>
function chiudiMenu(){ document.getElementById('menu').classList.remove('aperto');
                       document.getElementById('velo').classList.remove('on'); }
(function(){
  const menu = document.getElementById('menu'), velo = document.getElementById('velo');
  const apri = () => { menu.classList.add('aperto'); velo.classList.add('on'); segna(); };
  document.getElementById('apri-menu').onclick = () => menu.classList.contains('aperto') ? chiudiMenu() : apri();
  velo.onclick = chiudiMenu;

  /* i nomi delle viste stanno scritti in un posto solo: aggiungerne una
     domani vuol dire aggiungere una riga qui e una nel menu, non toccare
     tre if annidati */
  const NOMI = {albero:'Albero', orizzontale:'Orizzontale', ventaglio:'Ventaglio',
                stella:'Stella', anelli:'Anelli', griglia:'Mappa'};
  const QUANTE = Object.keys(NOMI).length;
  const eLaVista = (v, testo) => testo.includes(NOMI[v]);

  /* la voce della vista in uso resta accesa: senza, in un menu con cinque
     viste non si capisce quale si sta guardando */
  function segna(){
    const b = document.getElementById('forma').textContent;
    menu.querySelectorAll('[data-vista]').forEach(x =>
      x.classList.toggle('on', eLaVista(x.dataset.vista, b)));
  }
  /* le viste: si preme il bottone della barra finche' non si arriva a quella
     chiesta. Cosi' la logica resta una sola, in un posto solo. */
  menu.querySelectorAll('[data-vista]').forEach(x => x.onclick = () => {
    const v = x.dataset.vista, b = document.getElementById('forma');
    for (let i = 0; i < QUANTE; i++) {
      if (eLaVista(v, b.textContent)) break;
      b.click();
    }
    segna();
  });
  menu.querySelectorAll('[data-clic]').forEach(x => x.onclick = () => {
    const b = document.getElementById(x.dataset.clic); if (b) b.click(); chiudiMenu();
  });
  menu.querySelectorAll('[data-az]').forEach(x => x.onclick = () => {
    const a = x.dataset.az;
    if (a === 'cerca') { chiudiMenu(); const q = document.getElementById('q'); if (q) { q.focus(); q.select(); } }
    if (a === 'fit')   { chiudiMenu(); if (typeof adatta === 'function') adatta(); else window.dispatchEvent(new Event('resize')); }
    if (a === 'io')    { chiudiMenu(); const br = document.querySelector('.briciole a'); if (br) br.click(); }
  });

  /* scorciatoie: mai mentre si scrive in un campo */
  document.addEventListener('keydown', ev => {
    const t = (ev.target.tagName || '').toLowerCase();
    if (t === 'input' || t === 'textarea' || t === 'select' || ev.ctrlKey || ev.metaKey || ev.altKey) return;
    const k = ev.key.toLowerCase();
    const via = {'1':'albero','2':'orizzontale','3':'ventaglio','4':'stella','5':'anelli',
                 '6':'griglia'}[ev.key];
    if (via) { menu.querySelector('[data-vista="'+via+'"]').click(); ev.preventDefault(); return; }
    const bott = {'w':'tutti-world','u':'ultimi','r':'richiudi','p':'apri-utenti',
                  'i':'b-isola','b':'b-tutto','o':'b-filtro','l':'b-prof',
                  'k':'b-link','s':'b-png','c':'b-centra','n':'b-numeri','x':'b-peso'}[k];
    if (bott) { const b = document.getElementById(bott); if (b) { b.click(); ev.preventDefault(); } }
    if (k === 'f') { const x = menu.querySelector('[data-az="fit"]'); if (x) x.click(); ev.preventDefault(); }
    if (k === 't') { const x = menu.querySelector('[data-az="io"]'); if (x) x.click(); }
    if (ev.key === '/') { const q = document.getElementById('q'); if (q) { q.focus(); q.select(); ev.preventDefault(); } }
    if (k === 'm') { menu.classList.contains('aperto') ? chiudiMenu() : apri(); }
    if (ev.key === 'Escape') chiudiMenu();
  });
})();
</script>

</body></html>
