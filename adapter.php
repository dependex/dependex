<?php
require_once 'bootstrap.php';header('Content-Type: application/json; charset=utf-8');
$module=strtoupper($_GET['module']??'');$action=$_GET['action']??'status';
$st=db()->prepare('SELECT * FROM integration_adapters WHERE code=?');$st->execute([$module]);$a=$st->fetch();
if(!$a){http_response_code(404);echo json_encode(['ok'=>false,'error'=>'unknown_module']);exit;}
echo json_encode(['ok'=>true,'module'=>$a,'action'=>$action,'contract'=>[
 'identity'=>'SIC-ID-compatible reference expected','auth'=>'session/RBAC scope','payload'=>'JSON','version'=>$a['interface_version']
]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);