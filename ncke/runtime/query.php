<?php
declare(strict_types=1);
require_once __DIR__.'/../../bootstrap.php';
require_once __DIR__.'/NckeGraphSearch.php';
header('Content-Type: application/json; charset=utf-8');

$q=trim($_POST['q']??$_GET['q']??'');
if($q===''){http_response_code(400);echo json_encode(['ok'=>false,'error'=>'empty_query']);exit;}
$u=current_user();$pdo=db();$started=microtime(true);

$terms=preg_split('/\s+/u',$q) ?: [];
$clean=array_values(array_filter(array_map(fn($x)=>preg_replace('/[^\pL\pN_-]/u','',$x),$terms),fn($x)=>mb_strlen($x)>=2));
$fts=implode(' OR ',array_map(fn($x)=>'"'.str_replace('"','',$x).'"',array_slice($clean,0,12)));

$ftsHits=[];
if($fts!==''){
 $st=$pdo->prepare("SELECT c.sic_id,c.section,c.content,d.title,d.source_path,bm25(ncke_chunks_fts) score
 FROM ncke_chunks_fts JOIN ncke_chunks c ON c.sic_id=ncke_chunks_fts.chunk_sic_id
 JOIN ncke_documents d ON d.sic_id=c.document_sic_id
 WHERE ncke_chunks_fts MATCH ? ORDER BY score LIMIT 14");
 try{$st->execute([$fts]);foreach($st->fetchAll() as $h){$ftsHits[]=[
  'kind'=>'knowledge','id'=>$h['sic_id'],'title'=>$h['title'],'section'=>$h['section'],
  'content'=>$h['content'],'source'=>$h['source_path'],'score'=>70+max(0,min(20,(int)round(abs((float)$h['score']))))
 ];}}catch(Throwable $e){$ftsHits=[];}
}

$graph=new NckeGraphSearch($pdo);
$brainHits=$graph->search($q,10);
$worldHits=$graph->worldEntities($q,12);

$intent='FACTUAL';
if(preg_match('/\b(trend|andamento|ultimi|mese|anno|timeline|storico)\b/ui',$q))$intent='TEMPORAL';
elseif(preg_match('/\b(relazione|collegat|figli|rete|network|gerarchia|parent|club)\b/ui',$q))$intent='RELATIONAL';
elseif(preg_match('/\b(calcola|quanto|percent|media|correlazione|kpi|statistic)\b/ui',$q))$intent='STATISTICAL';
elseif(preg_match('/\b(preved|scenario|cosa succede|what if|forecast)\b/ui',$q))$intent='PREDICTIVE';
elseif(preg_match('/\b(come|procedura|passaggi|workflow)\b/ui',$q))$intent='PROCEDURAL';

$strategies=['full_text','metadata'];
if($brainHits)$strategies[]='graph';
if($worldHits)$strategies[]='world_registry';
if($intent==='TEMPORAL')$strategies[]='time_series';
if($intent==='STATISTICAL')$strategies[]='statistical';
$strategies[]='hybrid_rrf';

$all=array_merge($ftsHits,$brainHits,$worldHits);
usort($all,fn($a,$b)=>($b['score']??0)<=>($a['score']??0));
$seen=[];$hits=[];
foreach($all as $h){
 $key=($h['kind']??'').'|'.($h['id']??'').'|'.($h['title']??'');
 if(isset($seen[$key]))continue;$seen[$key]=1;$hits[]=$h;if(count($hits)>=15)break;
}

$confidence=min(98,max(25,40+count($ftsHits)*3+count($brainHits)*2+count($worldHits)*2));
$citations=[];$i=1;
foreach(array_slice($hits,0,8) as &$h){$h['citation']='[s'.$i.']';$citations[]=[
 'id'=>'s'.$i,'title'=>$h['title']??'Fonte','source'=>$h['source']??'','kind'=>$h['kind']??'unknown'
];$i++;}unset($h);

$answer=$hits
 ? "Retrieval ibrido completato su knowledge base, Neuralog e registry. Sono emerse ".count($hits)." evidenze; usa le fonti [s1]… per verificare i dettagli. Il layer generativo multi-provider verrà applicato quando un provider runtime autorizzato è disponibile."
 : "Non risultano evidenze sufficienti nelle fonti indicizzate. La query viene registrata per migliorare ingest e ricerca.";

$latency=(int)round((microtime(true)-$started)*1000);
$qid='NCKE-Q-'.strtoupper(substr(hash('sha256',$q.'|'.microtime(true)),0,16));
$pdo->prepare("INSERT INTO ncke_queries(sic_id,user_sic_id,query_text,intent,language,complexity,strategies_json,confidence,provider,latency_ms) VALUES(?,?,?,?,?,?,?,?,?,?)")
 ->execute([$qid,$u['sic_id']??null,$q,$intent,'it',min(10,max(1,(int)ceil(mb_strlen($q)/120))),json_encode($strategies),$confidence,'adaptive-retrieval',$latency]);

if($confidence<70){
 $hr='NCKE-HR-'.strtoupper(substr(hash('sha256',$qid),0,16));
 $exists=$pdo->prepare("SELECT 1 FROM ncke_human_review WHERE query_sic_id=? AND status='OPEN'");$exists->execute([$qid]);
 if(!$exists->fetchColumn())$pdo->prepare("INSERT INTO ncke_human_review(sic_id,query_sic_id,reason,priority) VALUES(?,?,?,?)")->execute([$hr,$qid,'LOW_CONFIDENCE',70]);
}

echo json_encode([
 'ok'=>true,'id'=>$qid,'intent'=>$intent,'confidence'=>$confidence,'strategies'=>$strategies,
 'answer'=>$answer,'hits'=>$hits,'sources'=>$citations,'latency_ms'=>$latency,
 'xai'=>[
   'decision_path'=>['normalize_query','classify_intent','fts5_search','neuralog_graph_search','world_registry_search','hybrid_fusion','confidence'],
   'evidence_counts'=>['fts'=>count($ftsHits),'brain'=>count($brainHits),'world_registry'=>count($worldHits)],
   'human_review'=>$confidence<70
 ]
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
