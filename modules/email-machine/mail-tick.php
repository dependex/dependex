<?php
/* ============================================================================
   GENESYS / MAIL-TICK — Destino Randagio · 2026-08-07 · Cowork
   Auto-invio della coda email SUL TRAFFICO del sito, come il mirror MySQL.
   Prima l'invio dipendeva dal cron GitHub (email-queue.yml + secret DR_MKT_KEY):
   se il secret non combacia o l'Action non gira, le email restano in coda.
   Questo endpoint NON dipende da GitHub: chiama direttamente le funzioni interne
   del motore (avanza i flussi + accoda festivita' + svuota la coda).
   SICURO: nessun input utente, sola logica interna. THROTTLE: max 1 giro ogni
   ~2,5 min (lock file). Risponde subito e lavora in background.
   Innescato da un beacon in inc.js. Gira comunque anche il cron GitHub, senza
   conflitti (concorrenza gestita dal lock).
============================================================================ */
@ini_set('display_errors','0'); error_reporting(E_ALL); @set_time_limit(0);
require_once __DIR__.'/../db.php';        // $pdo (dr.sqlite)
require_once __DIR__.'/../mailer.php';    // definisce mkt_run_flows / mkt_run_feste / mkt_process_queue
$pdo = $GLOBALS['pdo'] ?? ($pdo ?? null);

$DATA = getenv('DR_DATA_DIR') ?: (__DIR__.'/../data');
$lock = $DATA.'/.mailtick_last';
$last = is_file($lock) ? (int)@filemtime($lock) : 0;
header('Content-Type: text/plain; charset=utf-8'); header('Cache-Control: no-store');
if (time() - $last < 150) { echo "skip\n"; exit; }   /* non ancora ora */
@touch($lock);
echo "ok\n";
if (function_exists('fastcgi_finish_request')) { @fastcgi_finish_request(); }
@ignore_user_abort(true);

if ($pdo instanceof PDO) {
  try { if (function_exists('mkt_run_flows'))     mkt_run_flows($pdo); }     catch (Throwable $e) {}
  try { if (function_exists('mkt_run_feste'))     mkt_run_feste($pdo); }     catch (Throwable $e) {}
  try { if (function_exists('mkt_process_queue')) mkt_process_queue($pdo); } catch (Throwable $e) {}
}
