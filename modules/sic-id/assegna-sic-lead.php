<?php
/* ============================================================================
   GENESYS / ASSEGNA-SIC-LEAD — Destino Randagio · 2026-08-07 · Cowork
   Assegna un SIC-ID a TUTTI i lead in `contacts` che non ne hanno uno, così
   l'anagrafe lead è completa PRIMA del re-permissioning. Idempotente:
   - se esiste un user con la stessa email -> usa il suo SIC (sic_id/internal_code);
   - altrimenti genera SIC-ID-<12 cifre> UNICO (no collisioni con contacts/users).
   Gate admin o ?key=DR_ADMIN_KEY. Sola scrittura sulla colonna sic_id dei lead.
============================================================================ */
@ini_set('display_errors','1'); error_reporting(E_ALL); @set_time_limit(0);
require_once __DIR__.'/../db.php';           // $pdo
@require_once __DIR__.'/../dr-env.php';
if(session_status()!==PHP_SESSION_ACTIVE) @session_start();
$adminKey = function_exists('dr_env') ? dr_env('DR_ADMIN_KEY', dr_env('DR_ADMIN_PASS','')) : '';
$isAdmin = ((($_SESSION['role']??'')==='admin') && !empty($_SESSION['uid']))
        || ($adminKey!=='' && isset($_GET['key']) && hash_equals((string)$adminKey,(string)$_GET['key']));
if(!$isAdmin && php_sapi_name()!=='cli'){ http_response_code(403); exit('403 — solo admin o ?key=DR_ADMIN_KEY.'); }
header('Content-Type: text/plain; charset=utf-8');
$DRY = isset($_GET['dry']);

$pdo=$GLOBALS['pdo']??$pdo;
function say($s){ echo $s."\n"; }

/* set di SIC già in uso (contacts + users) per garantire unicità */
$used=[];
foreach($pdo->query("SELECT sic_id FROM contacts WHERE sic_id IS NOT NULL AND sic_id<>''") as $r){ $used[$r['sic_id']]=1; }
try{ foreach($pdo->query("SELECT sic_id FROM users WHERE sic_id IS NOT NULL AND sic_id<>''") as $r){ $used[$r['sic_id']]=1; } }catch(Throwable $e){}
try{ foreach($pdo->query("SELECT internal_code FROM users WHERE internal_code IS NOT NULL AND internal_code<>''") as $r){ $used[$r['internal_code']]=1; } }catch(Throwable $e){}

function gen_sic(&$used){
  do{ $s='SIC-ID-'.str_pad((string)random_int(1,999999999999),12,'0',STR_PAD_LEFT); }while(isset($used[$s]));
  $used[$s]=1; return $s;
}

/* lead senza sic_id */
$todo=$pdo->query("SELECT id,email FROM contacts WHERE sic_id IS NULL OR sic_id=''")->fetchAll(PDO::FETCH_ASSOC);
say('Lead senza SIC-ID: '.count($todo).($DRY?'  (DRY-RUN, nessuna scrittura)':''));

$fromUser=0;$generati=0;$err=0;
$uStmt=$pdo->prepare("SELECT COALESCE(NULLIF(sic_id,''),NULLIF(internal_code,'')) FROM users WHERE email=? LIMIT 1");
$upd=$pdo->prepare("UPDATE contacts SET sic_id=? WHERE id=?");
if(!$DRY) $pdo->beginTransaction();
try{
  foreach($todo as $c){
    $sic=''; $em=strtolower(trim((string)$c['email']));
    if($em!==''){ $uStmt->execute([$em]); $u=$uStmt->fetchColumn(); if($u){ $sic=(string)$u; $fromUser++; } }
    if($sic===''){ $sic=gen_sic($used); $generati++; }
    if(!$DRY) $upd->execute([$sic,(int)$c['id']]);
  }
  if(!$DRY) $pdo->commit();
}catch(Throwable $e){ if(!$DRY && $pdo->inTransaction()) $pdo->rollBack(); say('ERRORE: '.$e->getMessage()); $err=1; }

say('');
say('Da user esistente : '.$fromUser);
say('Generati nuovi    : '.$generati);
say('Totale assegnati  : '.($fromUser+$generati));
if(!$err && !$DRY){
  $rest=(int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE sic_id IS NULL OR sic_id=''")->fetchColumn();
  $tot=(int)$pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
  say(''); say("Lead totali: $tot · ancora senza SIC: $rest");
  say($rest===0 ? 'OK — ogni lead ha il suo SIC-ID. Anagrafe completa.' : 'ATTENZIONE: restano lead senza SIC.');
}
