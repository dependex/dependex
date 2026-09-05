<?php
require_once __DIR__.'/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$pdo=db();
$sql="SELECT sic_id,entity_name,original_local_name,network_level,club_rank,status,country,region,province,city,address,
latitude,longitude,geo_accuracy,meeting_day,meeting_time,phone,email,website,parent_sic_id,parent_name,
hudolin_confirmed,completeness_score,source_url,last_verified
FROM dependex_world_registry
WHERE network_enabled=1
ORDER BY country,city,entity_name";
$rows=$pdo->query($sql)->fetchAll();
echo json_encode(['ok'=>true,'count'=>count($rows),'items'=>$rows],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
