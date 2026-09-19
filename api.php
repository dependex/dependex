<?php
require_once 'bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$action=$_GET['action']??'';
$scope=strtoupper($_GET['scope']??'ITALY');
if($action==='network'){
 $where = $scope==='GLOBAL'
   ? "network_enabled=1"
   : "site_scope='OLTRE_ITALY' AND network_enabled=1";
 $q="SELECT sic_id,level,entity_name,country,region,province,comune,address,parent_sic_id,parent_name,meeting_day,meeting_time,verification_status,site_scope FROM network_entities WHERE $where ORDER BY level,country,region,province,comune,entity_name";
 echo json_encode(db()->query($q)->fetchAll(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
}
if($action==='stats'){
 $italy=(int)db()->query("SELECT COUNT(*) FROM network_entities WHERE site_scope='OLTRE_ITALY'")->fetchColumn();
 $italyClubs=(int)db()->query("SELECT COUNT(*) FROM network_entities WHERE site_scope='OLTRE_ITALY' AND level='CLUB'")->fetchColumn();
 $global=(int)db()->query("SELECT COUNT(*) FROM network_entities WHERE network_enabled=1")->fetchColumn();
 $globalClubs=(int)db()->query("SELECT COUNT(*) FROM network_entities WHERE network_enabled=1 AND level='CLUB'")->fetchColumn();
 echo json_encode(compact('italy','italyClubs','global','globalClubs'));exit;
}

// 1. Tracciamento Apertura Reale Email (Pixel GIF 1x1 trasparente)
if($action==='track_open'){
 $sendId = trim((string)($_GET['s'] ?? 'anonymous'));
 $token = trim((string)($_GET['t'] ?? ''));
 dx_record_email_open($sendId, $token);
 dx_output_transparent_pixel();
}

// 2. Tracciamento Click Reale Email (Redirect sicuro 302)
if($action==='track_click'){
 $sendId = trim((string)($_GET['s'] ?? 'anonymous'));
 $targetUrl = trim((string)($_GET['u'] ?? '/index.php'));
 $tag = trim((string)($_GET['tag'] ?? 'email_link'));
 $destination = dx_record_email_click($sendId, $targetUrl, $tag);
 header('Location: ' . $destination, true, 302);
 exit;
}

// 3. Heartbeat / Ping Client-Side (Dwell time & Scroll depth)
if($action==='ping'){
 $input = json_decode((string)file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
 $dwell = max(0, (int)($input['dwell'] ?? 0));
 $scroll = min(100, max(0, (int)($input['scroll'] ?? 0)));
 $page = trim((string)($input['page'] ?? ($_SERVER['HTTP_REFERER'] ?? '/')));
 $porta = !empty($input['porta']) ? trim((string)$input['porta']) : null;
 
 $res = dx_process_ping($dwell, $scroll, $page, $porta);
 echo json_encode($res, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
 exit;
}

// 4. Tracciamento Eventi Funnel Psicologico
if($action==='funnel_event'){
 $input = json_decode((string)file_get_contents('php://input'), true) ?: $_POST ?: $_GET;
 $evtAction = trim((string)($input['action_name'] ?? ($input['event'] ?? 'VISIT')));
 $stage = strtoupper(trim((string)($input['stage'] ?? 'ORIENTATION')));
 $meta = is_array($input['meta'] ?? null) ? $input['meta'] : [];
 
 $res = dx_track_funnel_event($evtAction, $stage, $meta);
 echo json_encode($res, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
 exit;
}

// 5. Watchdog Diagnostic Probe
if($action==='watchdog'){
 $report = dx_watchdog_check();
 echo json_encode($report, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
 exit;
}

// 6. Funnel Stats & Psychological Intents Distribution
if($action==='funnel_stats'){
 $funnel = dx_get_funnel_stats();
 $intents = dx_get_psychological_intents();
 echo json_encode(['ok' => true, 'funnel' => $funnel, 'intents' => $intents], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
 exit;
}

http_response_code(404);echo json_encode(['error'=>'Not found']);