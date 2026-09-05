#!/usr/bin/env php
<?php
$root=dirname(__DIR__);
passthru(PHP_BINARY.' '.escapeshellarg($root.'/bin/build-knowledge.php'),$kbCode);

require_once $root.'/modules/dependex-host-bridge.php';
require_once $root.'/modules/neuralog/brain.php';

$install=brain_schema_install();
$loops=0;$processed=0;$nodes=0;$links=0;
do{
    $r=brain_ingest_run([
      'roots'=>['../../knowledge/neuralog/inbox'],
      'batch'=>50,
      'source'=>'oltre_dependex_knowledge',
      'visibility'=>'admin'
    ]);
    $processed+=(int)($r['processed']??0);
    $nodes+=(int)($r['nodes']??0);
    $links+=(int)($r['links']??0);
    $remaining=(int)($r['remaining']??0);
    $loops++;
}while($remaining>0 && $loops<100);

/* Registry -> neuroni master */
$registry=0;
try{
 foreach(db()->query('SELECT * FROM dependex_world_registry ORDER BY network_rank DESC,country,entity_name') as $n){
    $content=implode("\n",array_filter([
      'SIC-ID: '.$n['sic_id'],'Tipo: '.$n['network_level'],'Nome: '.$n['entity_name'],
      'Paese: '.$n['country'],'Regione: '.$n['region'],'Provincia: '.$n['province'],'Città: '.$n['city'],
      'Indirizzo: '.$n['address'],'Status: '.$n['status'],'Incontri: '.$n['meeting'],
      'Contatti pubblici: '.$n['public_contact'],'Fonte: '.$n['source_url']
    ]));
    brain_ingest_text($content,[
      'path'=>'registry/'.$n['sic_id'],'title'=>$n['entity_name'],'source'=>'dependex_registry',
      'section'=>strtolower($n['network_level']),'visibility'=>'admin',
      'weight'=>max(1,(int)$n['network_rank']/10),'id_prefix'=>'registry','lang'=>$n['language']?:'en'
    ]);
    $registry++;
 }
 foreach(db()->query('SELECT parent_sic_id,child_sic_id,relation FROM dependex_world_edges') as $e){
    $a=brain_node_ids_by_path('registry/'.$e['parent_sic_id']);
    $b=brain_node_ids_by_path('registry/'.$e['child_sic_id']);
    if($a&&$b)brain_link($a[0],$b[0],$e['relation']);
 }
}catch(Throwable $e){}

$out=[
 'ok'=>($kbCode===0 && !empty($install['ok'])),
 'knowledge_builder'=>$kbCode,
 'schema'=>$install,
 'ingest'=>['processed'=>$processed,'nodes'=>$nodes,'links'=>$links],
 'registry'=>$registry,
 'brain'=>brain_stats()
];
echo json_encode($out,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
