<?php
require_once __DIR__.'/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$pdo=db();
$sql="SELECT sic_id,entity_name,original_name,network_level,network_rank,status,country,region,province,city,address,
latitude,longitude,geo_accuracy,meeting,phone,email,website,parent_sic_id,
hudolin_confirmed,public_data_score,source_url,last_verified
FROM dependex_world_registry
WHERE status NOT LIKE '%DORMANT%'
ORDER BY country,city,entity_name";
$rows=$pdo->query($sql)->fetchAll();
echo json_encode(['ok'=>true,'count'=>count($rows),'items'=>$rows],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
