<?php
declare(strict_types=1);
require_once __DIR__.'/../../bootstrap.php';
require_once __DIR__.'/SecureEnvDiscovery.php';
require_once __DIR__.'/AiProviderResolver.php';
header('Content-Type: application/json; charset=utf-8');

$roots=[
    dirname(__DIR__,2),
    dirname(__DIR__,3),
    dirname(__DIR__,2).'/config',
    dirname(__DIR__,2).'/secrets'
];
$env=new SecureEnvDiscovery($roots);$env->load();
$resolver=new AiProviderResolver($_ENV);
$pdo=db();
$integrity=$pdo->query("PRAGMA integrity_check")->fetchColumn();
$stats=[
 'documents'=>(int)$pdo->query("SELECT COUNT(*) FROM ncke_documents WHERE status='ACTIVE'")->fetchColumn(),
 'chunks'=>(int)$pdo->query("SELECT COUNT(*) FROM ncke_chunks")->fetchColumn(),
 'neurons'=>(int)$pdo->query("SELECT COUNT(*) FROM nl_nodes")->fetchColumn(),
 'synapses'=>(int)$pdo->query("SELECT COUNT(*) FROM nl_edges")->fetchColumn(),
];
echo json_encode([
 'status'=>$integrity==='ok'?'healthy':'degraded',
 'database'=>$integrity,
 'ncke'=>$stats,
 'providers'=>$resolver->status(),
 'env_files'=>$env->discover()
],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
