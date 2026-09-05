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
http_response_code(404);echo json_encode(['error'=>'Not found']);