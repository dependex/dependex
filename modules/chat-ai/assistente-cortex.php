<?php
/* ============================================================================
   genesys/assistente-cortex.php — fallback CORTEX per l'Assistente del Branco.
   Destino Randagio · installazione organismo Neuralog · 2026-08-05 · Cowork.

   Quando il motore locale della chat (livello 1, KB in assistente-branco.php)
   NON trova una risposta, la chat interroga QUESTO endpoint: cerca nei nodi di
   conoscenza che l'organismo ha digerito dall'inbox (cortex_nodes) e, se trova
   qualcosa di pertinente, risponde "Dalla memoria del Cortex…" citando il
   documento sorgente. Se non trova nulla, la chat mostra il fallback finale
   (scrivi a info@dependex.social).

   Onestà: nessuna risposta inventata. Se il Cortex non ha il nodo, lo dice.
   Ogni interrogazione è loggata in dr_events (watchdog), inclusa la "fame"
   (domande senza match) = ciò che all'organismo manca ancora di imparare.

   POST/GET: q=<domanda> (max 500 char). Risposta JSON.
============================================================================ */
require_once __DIR__ . '/../db.php';                      // $pdo (DB unico)
require_once __DIR__ . '/../neuralog/neura-cortex.php';   // porta nlg_neura_cerca_cortex()
@require_once __DIR__ . '/../dr-log.php';                  // dr_log() se disponibile
@require_once __DIR__ . '/../dr-env.php';                  // dr_env() per la parola d'ordine
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();

header('Content-Type: application/json; charset=utf-8');
if (function_exists('dr_security_headers')) dr_security_headers();

$q = trim((string)($_REQUEST['q'] ?? ''));
if ($q === '' || mb_strlen($q) > 500) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'err' => 'domanda-non-valida']);
    exit;
}

/* ===== MODALITA' ADMIN — Cortex come cervello del progetto ==================
   Sblocco: "ciao sono destino randagio mirco" -> chiede la parola d'ordine;
   parola corretta (CORTEX_ADMIN_WORD in .env) -> sessione admin. In admin la
   chat risponde da TUTTA la conoscenza (progetto+inbox) + KPI LIVE del sistema.
   In modalità normale: comportamento invariato (aiuto agli utenti). ========== */
function cortex_admin_answer(PDO $pdo, string $q): string {
    $one=function($sql) use($pdo){ try{ return $pdo->query($sql)->fetchColumn(); }catch(Throwable $e){ return null; } };
    $has=function($t) use($pdo){ try{ return (bool)$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name=".$pdo->quote($t))->fetchColumn(); }catch(Throwable $e){ return false; } };
    $k=[];
    if($has('webinar_signups')){ $k['iscritti webinar']=(int)$one("SELECT COUNT(*) FROM webinar_signups"); $k['presenti']=(int)$one("SELECT COUNT(*) FROM webinar_signups WHERE attended=1"); }
    if($has('genesys_pionieri')) $k['pionieri (acquisti)']=(int)$one("SELECT COUNT(*) FROM genesys_pionieri");
    if($has('contacts')) $k['lead']=(int)$one("SELECT COUNT(*) FROM contacts");
    if($has('users')) $k['utenti']=(int)$one("SELECT COUNT(*) FROM users");
    if($has('orders')) $k['fatturato €']=(float)$one("SELECT COALESCE(SUM(total_eur),0) FROM orders WHERE status IN ('paid','pagato','completed','completato')");
    if($has('mkt_queue')){ $k['email in coda']=(int)$one("SELECT COUNT(*) FROM mkt_queue WHERE status IN ('pending','queued','')"); $k['email inviate']=(int)$one("SELECT COUNT(*) FROM mkt_queue WHERE status IN ('sent','inviato')"); }
    if($has('cortex_files')) $k['file appresi (Cortex)']=(int)$one("SELECT COUNT(*) FROM cortex_files");
    if($has('cortex_nodes')) $k['nodi conoscenza']=(int)$one("SELECT COUNT(*) FROM cortex_nodes");
    $kpi=[]; foreach($k as $lbl=>$v){ $kpi[]=ucfirst($lbl).': '.(is_float($v)?number_format($v,0,',','.'):$v); }
    $out = "📊 Stato ecosistema (live)\n- ".implode("\n- ",$kpi);
    /* conoscenza pertinente dai file di progetto/inbox */
    $hit = function_exists('nlg_neura_cerca_cortex') ? nlg_neura_cerca_cortex($q,3) : null;
    if($hit && ($hit['pertinenza']??0)>=1){
        $out .= "\n\n🧠 Dalla conoscenza del progetto (".($hit['fonte']??'cortex')."):\n".$hit['risposta'];
    } else {
        $out .= "\n\nSu questa domanda specifica non ho ancora un nodo di conoscenza: appena il file relativo viene digerito (ingestione live) potrò rispondere nel dettaglio.";
    }
    return $out;
}

$qn = mb_strtolower($q);
$ADMWORD = function_exists('dr_env') ? (string)dr_env('CORTEX_ADMIN_WORD','') : '';
if (strpos($qn,'sono destino randagio mirco')!==false){
    $_SESSION['cortex_admin_wait']=1;
    echo json_encode(['ok'=>true,'trovata'=>true,'admin'=>'wait','risposta'=>"Ciao Mirco. Qual è la parola d'ordine?"],JSON_UNESCAPED_UNICODE); exit;
}
if (!empty($_SESSION['cortex_admin_wait']) && $ADMWORD!=='' && hash_equals($ADMWORD, $q)){
    unset($_SESSION['cortex_admin_wait']); $_SESSION['cortex_admin']=1;
    echo json_encode(['ok'=>true,'trovata'=>true,'admin'=>'on','risposta'=>"Modalità ADMIN attiva. Chiedimi tutto dell'ecosistema: utenti, iscritti, vendite, pagamenti, stato Genesys, network, email, file appresi…"],JSON_UNESCAPED_UNICODE); exit;
}
if (!empty($_SESSION['cortex_admin_wait'])){
    unset($_SESSION['cortex_admin_wait']);
    echo json_encode(['ok'=>true,'trovata'=>true,'risposta'=>"Parola d'ordine errata."],JSON_UNESCAPED_UNICODE); exit;
}
if (!empty($_SESSION['cortex_admin'])){
    $risp = cortex_admin_answer($GLOBALS['pdo'] ?? $pdo, $q);
    try{ if(function_exists('dr_log')) @dr_log($GLOBALS['pdo']??$pdo,'assistente','cortex-admin',['q'=>mb_substr($q,0,200)]); }catch(Throwable $e){}
    echo json_encode(['ok'=>true,'trovata'=>true,'admin'=>'on','risposta'=>$risp],JSON_UNESCAPED_UNICODE); exit;
}

$hit = nlg_neura_cerca_cortex($q, 3);
$soglia = 2; // stessa soglia di pertinenza usata su neuralog.pro

// log (best-effort, mai bloccante)
try {
    if (function_exists('dr_log')) {
        global $pdo;
        $trovata = ($hit && $hit['pertinenza'] >= $soglia) ? 1 : 0;
        @dr_log($pdo, 'assistente', 'cortex', ['q' => mb_substr($q, 0, 200), 'trovata' => $trovata, 'pert' => $hit['pertinenza'] ?? 0]);
    }
} catch (Throwable $e) {}

if ($hit && $hit['pertinenza'] >= $soglia) {
    echo json_encode([
        'ok'        => true,
        'trovata'   => true,
        'risposta'  => 'Dalla memoria del Cortex — ' . $hit['risposta'],
        'fonte'     => $hit['fonte'],
        'pertinenza'=> $hit['pertinenza'],
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'ok'      => true,
        'trovata' => false,
        'risposta'=> 'Non ho ancora questa risposta nella memoria del Branco. Scrivi a info@dependex.social o apri un ticket dalla pagina Supporto.',
    ], JSON_UNESCAPED_UNICODE);
}
