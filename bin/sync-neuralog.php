#!/usr/bin/env php
<?php
require_once dirname(__DIR__).'/modules/dependex-host-bridge.php';
require_once dirname(__DIR__).'/modules/neuralog/brain.php';
brain_schema_install();
$nodes=db()->query('SELECT * FROM dependex_world_registry ORDER BY network_rank DESC,country,entity_name')->fetchAll();$made=0;foreach($nodes as $n){$content=implode("\n",array_filter(['SIC-ID: '.$n['sic_id'],'Tipo: '.$n['network_level'],'Nome: '.$n['entity_name'],'Paese: '.$n['country'],'Regione: '.$n['region'],'Provincia: '.$n['province'],'Città: '.$n['city'],'Indirizzo: '.$n['address'],'Status: '.$n['status'],'Incontri: '.$n['meeting'],'Contatti pubblici: '.$n['public_contact'],'Fonte: '.$n['source_url']]));brain_node_put(['id'=>$n['sic_id'],'section'=>strtolower($n['network_level']),'weight'=>max(1,(int)$n['network_rank']/10),'path'=>'registry/'.$n['country'].'/'.$n['sic_id'],'title'=>$n['entity_name'],'content'=>$content,'visibility'=>'admin','source'=>'dependex_registry','lang'=>$n['language']?:'en']);$made++;}
foreach(db()->query('SELECT parent_sic_id,child_sic_id,relation FROM dependex_world_edges')->fetchAll() as $e)brain_link($e['parent_sic_id'],$e['child_sic_id'],$e['relation']);
$c=brain_counts();echo json_encode(['ok'=>true,'synced'=>$made,'brain'=>$c],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";