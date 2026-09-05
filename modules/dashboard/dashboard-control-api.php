<?php
/* ============================================================================
   DASHBOARD CONTROL — API AJAX · 2026-08-12 · Cowork
   destinazione: genesys/dashboard-control-api.php

   Riceve le azioni dal pannello admin-dashboard-control.php via fetch().
   Solo POST, solo admin, CSRF di sessione (stesso schema di
   admin-network-posti.php: $_SESSION['csrf_dashctl'] + hash_equals).
   GET e' permesso SOLO per le azioni di sola lettura (search/log/export),
   sempre dietro lo stesso gate admin.
============================================================================ */
if (session_status() === PHP_SESSION_NONE) @session_start();
require __DIR__.'/../db.php';
require_once __DIR__.'/dashboard-control-lib.php';
if (is_file(__DIR__.'/../dr-env.php')) require_once __DIR__.'/../dr-env.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$K = function_exists('dr_env') ? (string)dr_env('DR_ADMIN_KEY','') : '';
$isAdmin = ((($_SESSION['role']??'')==='admin') && !empty($_SESSION['uid']))
        || ($K!=='' && isset($_REQUEST['key']) && hash_equals($K,(string)$_REQUEST['key']));
if(!$isAdmin){ http_response_code(403); echo json_encode(['ok'=>false,'err'=>'403']); exit; }
$adminId = (int)($_SESSION['uid'] ?? 0);

dashctl_schema($pdo);

function dc_csrf_ok(){ return isset($_POST['csrf']) && hash_equals($_SESSION['csrf_dashctl']??'', (string)$_POST['csrf']); }

$act = (string)($_REQUEST['act'] ?? '');

try{

  /* --------- sola lettura (GET o POST) --------- */
  if($act==='sections'){ echo json_encode(['ok'=>true,'sezioni'=>dashctl_sections($pdo)]); exit; }

  if($act==='fasi'){ echo json_encode(['ok'=>true,'fasi'=>dashctl_fasi($pdo)]); exit; }

  if($act==='search'){
    $rows = dashctl_search_users($pdo, (string)($_REQUEST['q']??''), 25);
    echo json_encode(['ok'=>true,'utenti'=>$rows]); exit;
  }

  if($act==='filter'){
    $criteri = [
      'rank_min'      => $_REQUEST['rank_min'] ?? null,
      'membership'    => $_REQUEST['membership'] ?? null,
      'role'          => $_REQUEST['role'] ?? null,
      'registrato_da' => $_REQUEST['registrato_da'] ?? null,
      'registrato_a'  => $_REQUEST['registrato_a'] ?? null,
      'diretti_di'    => $_REQUEST['diretti_di'] ?? null,
    ];
    $rows = dashctl_filter_users($pdo, array_filter($criteri, fn($v)=>$v!==null && $v!==''));
    echo json_encode(['ok'=>true,'utenti'=>$rows,'trovati'=>count($rows)]); exit;
  }

  if($act==='user_state'){
    $uid = (int)($_REQUEST['user_id'] ?? 0);
    if($uid<=0){ echo json_encode(['ok'=>false,'err'=>'user_id mancante']); exit; }
    $u = $pdo->prepare("SELECT id,username,email,full_name,COALESCE(sic_id,genesys_sic,'') sic,role,
                                COALESCE(rank_floor,0) rank_floor, COALESCE(membership_active,0) membership_active
                         FROM users WHERE id=?");
    $u->execute([$uid]); $u=$u->fetch(PDO::FETCH_ASSOC);
    if(!$u){ echo json_encode(['ok'=>false,'err'=>'utente non trovato']); exit; }
    echo json_encode(['ok'=>true,'utente'=>$u,'stato'=>dashctl_user_state($pdo,$uid)]); exit;
  }

  if($act==='log'){
    $filters = array_filter([
      'user_id'     => $_REQUEST['user_id'] ?? null,
      'admin_id'    => $_REQUEST['admin_id'] ?? null,
      'action_type' => $_REQUEST['action_type'] ?? null,
      'da'          => $_REQUEST['da'] ?? null,
      'a'           => $_REQUEST['a'] ?? null,
    ], fn($v)=>$v!==null && $v!=='');
    $rows = dashctl_get_log($pdo, $filters, (int)($_REQUEST['limit'] ?? 200));
    echo json_encode(['ok'=>true,'log'=>$rows]); exit;
  }

  if($act==='export'){
    $formato = (string)($_REQUEST['formato'] ?? 'json');
    $rows = dashctl_get_log($pdo, [], 5000);
    if($formato==='csv'){
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="dashboard-log.csv"');
      $out = fopen('php://output','w');
      fputcsv($out, ['data','admin','utente','azione','sezione','fase','vecchio','nuovo','dettagli']);
      foreach($rows as $r){ fputcsv($out, [$r['created'],$r['admin_nome'],$r['user_nome'],$r['action_type'],$r['sezione_nome'],$r['fase_nome'],$r['old_status'],$r['new_status'],$r['details']]); }
      fclose($out); exit;
    }
    header('Content-Disposition: attachment; filename="dashboard-log.json"');
    echo json_encode($rows, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); exit;
  }

  if($act==='csrf'){ /* la pagina admin lo prende gia' da sessione, questo e' solo un rifornimento se serve via JS */
    if(empty($_SESSION['csrf_dashctl'])) $_SESSION['csrf_dashctl']=bin2hex(random_bytes(16));
    echo json_encode(['ok'=>true,'csrf'=>$_SESSION['csrf_dashctl']]); exit;
  }

  /* --------- azioni MUTANTI: solo POST + CSRF --------- */
  if($_SERVER['REQUEST_METHOD']!=='POST'){ echo json_encode(['ok'=>false,'err'=>'usa POST']); exit; }
  if(!dc_csrf_ok()){ echo json_encode(['ok'=>false,'err'=>'token scaduto, ricarica la pagina']); exit; }

  if($act==='set_single'){
    $r = dashctl_set_user_state($pdo,$adminId,(int)($_POST['user_id']??0),(string)($_POST['slug']??''), (int)($_POST['active']??0)===1);
    echo json_encode($r); exit;
  }

  if($act==='set_bulk'){
    $ids = array_filter(array_map('intval', explode(',', (string)($_POST['user_ids']??''))));
    $r = dashctl_set_bulk_state($pdo,$adminId,$ids,(string)($_POST['slug']??''), (int)($_POST['active']??0)===1);
    echo json_encode($r); exit;
  }

  if($act==='set_global'){
    if((string)($_POST['conferma']??'')!=='SI'){ echo json_encode(['ok'=>false,'err'=>'conferma mancante']); exit; }
    $r = dashctl_set_global_state($pdo,$adminId,(string)($_POST['slug']??''), (int)($_POST['active']??0)===1);
    echo json_encode($r); exit;
  }

  if($act==='set_fase'){
    if((string)($_POST['conferma']??'')!=='SI'){ echo json_encode(['ok'=>false,'err'=>'conferma mancante']); exit; }
    $r = dashctl_set_fase_state($pdo,$adminId,(int)($_POST['fase_id']??0), (int)($_POST['active']??0)===1);
    echo json_encode($r); exit;
  }

  if($act==='set_fase_status'){
    $r = dashctl_set_fase_status($pdo,$adminId,(int)($_POST['fase_id']??0),(string)($_POST['status']??''));
    echo json_encode($r); exit;
  }

  echo json_encode(['ok'=>false,'err'=>'azione sconosciuta']);

}catch(Throwable $e){
  http_response_code(500);
  echo json_encode(['ok'=>false,'err'=>'errore interno']);
}
