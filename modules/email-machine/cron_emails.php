<?php
/* ============================================================
   Email machine — CRON
   Su Hostinger crea un Cron Job orario:
     php /home/USER/domains/destinorandagio.it/public_html/cron_emails.php key=DRCRON
   Invia i reminder dei CARRELLI ABBANDONATI (aperti da >1h, non ancora ricordati).
   ============================================================ */
require __DIR__.'/db.php';
require __DIR__.'/email.php';        // dr_newsletter (parte newsletter manuale)
require_once __DIR__.'/mailer.php';  // macchina email canonica: mkt_trigger / mkt_cancel_flow / mkt_can_market

$key = $_GET['key'] ?? ($argv[1]??'');
$key = str_replace('key=','',$key);
if (!hash_equals(dr_env('DR_CRON_KEY','DRCRON'),(string)$key)) { http_response_code(403); exit("forbidden"); }

/* ============================================================================
   CARRELLI ABBANDONATI — arruola nel DRIP a 12 email 'cart_abandoned'
   (flusso carrello_abbandonato: cadenza [0,1,2,3,5,7,10,13,16,19,22,25] giorni,
   copy PNL onesto). NON piu' un solo reminder: la macchina canonica manda la
   sequenza e la ferma da sola a fine flusso. STOP-ALL'ACQUISTO: se l'utente
   compra dopo aver salvato il carrello, si chiude il carrello e si annulla il
   drip (non si scrive a chi ha gia' concluso). Gate unsub/da-confermare.
============================================================================ */
$SITE = function_exists('dr_env') ? rtrim(dr_env('DR_SITE','https://destinorandagio.it'),'/') : 'https://destinorandagio.it';
$arruolati = 0; $fermati = 0;
$rows = $pdo->query("SELECT * FROM carts WHERE status='open' AND datetime(updated) <= datetime('now','-1 hours')");
foreach ($rows as $c) {
  $email = strtolower(trim((string)($c['email'] ?? ''))); if ($email==='') continue;
  $items = json_decode($c['items'] ?? '[]', true) ?: [];
  $names = array_values(array_filter(array_map(function($i){ return $i['name'] ?? ''; }, $items)));

  /* CONVERSIONE: ha comprato dopo aver salvato il carrello? -> chiudi + stop drip */
  $ha = false;
  try {
    $q = $pdo->prepare("SELECT 1 FROM orders WHERE status IN('paid','fulfilled')
                        AND instr(lower(COALESCE(customer,'')), ?) > 0
                        AND datetime(created) >= datetime(?) LIMIT 1");
    $q->execute([$email, (string)($c['updated'] ?? 'now')]); $ha = (bool)$q->fetchColumn();
  } catch (Exception $e) { $ha = false; }
  if ($ha) {
    $pdo->prepare("UPDATE carts SET status='converted' WHERE id=?")->execute([$c['id']]);
    if (function_exists('mkt_cancel_flow')) $fermati += (int)mkt_cancel_flow($pdo, $email, 'cart_abandoned');
    continue;
  }

  /* ARRUOLA UNA VOLTA nel drip (reminded=1 = gia' arruolato) */
  if ((int)($c['reminded'] ?? 0) === 0) {
    if (function_exists('mkt_can_market') && !mkt_can_market($pdo, $email)) { continue; } // rispetta unsub / da-confermare
    $name = trim((string)($c['name'] ?? '')) ?: 'Randagio';
    if (function_exists('mkt_trigger')) {
      mkt_trigger($pdo, 'cart_abandoned', $email, $name,
                  ['link'=>$SITE.'/checkout.php', 'items'=>$names, 'nome'=>$name]);
      $pdo->prepare("UPDATE carts SET reminded=1 WHERE id=?")->execute([$c['id']]);
      $arruolati++;
    }
  }
}
echo "Carrelli abbandonati: arruolati nel drip $arruolati, fermati (convertiti) $fermati\n";

/* ============================================================================
   MOTORE EMAIL MARKETING (70+ Template) — ESECUZIONE DRIP & CODA SMTP
   Ogni ora il cron Hostinger avanza le sequenze e svuota la coda con rate-limit.
============================================================================ */
if (function_exists('mkt_run_flows')) {
  $flussi = mkt_run_flows($pdo);
  echo "Flussi marketing eseguiti: " . intval($flussi) . "\n";
}
if (function_exists('mkt_run_recurrences')) {
  mkt_run_recurrences($pdo);
  echo "Ricorrenze e anniversari controllati\n";
}
if (function_exists('mkt_process_queue')) {
  $inviate = mkt_process_queue($pdo, 30);
  echo "Coda SMTP processata (inviate: " . intval($inviate) . ")\n";
}

/* Newsletter manuale: cron_emails.php?key=DRCRON&news=1&subject=...&body=... (invia a tutti gli iscritti) */
if (!empty($_GET['news']) && !empty($_GET['subject'])) {
  $subj=$_GET['subject']; $body=$_GET['body']??''; $n=0;
  foreach ($pdo->query("SELECT DISTINCT email FROM subscribers") as $s) { if(dr_newsletter($s['email'],$subj,$body)) $n++; }
  echo "Newsletter inviate: $n\n";
}
