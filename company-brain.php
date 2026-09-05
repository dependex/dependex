<?php
require_once 'bootstrap.php';
$u=require_login();
if(!has_role($u['sic_id'],['SUPERADMIN','WORLD_ADMIN','CONTINENTAL_ADMIN','INTERNATIONAL_ADMIN','NATIONAL_ADMIN'])){http_response_code(403);exit('Accesso non autorizzato.');}
$stats=['nodes'=>0,'links'=>0,'knowledge'=>0,'files'=>0,'chat'=>0];
foreach(['nodes','links','knowledge','files','chat_log'] as $k){try{$stats[$k==='chat_log'?'chat':$k]=(int)db()->query('SELECT COUNT(*) FROM brain_'.$k)->fetchColumn();}catch(Throwable $e){}}
$last='';try{$last=(string)db()->query("SELECT detail||' · '||created_at FROM brain_activity ORDER BY id DESC LIMIT 1")->fetchColumn();}catch(Throwable $e){}
$pageTitle='Company Brain';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">NEURALOG / CORTEX</span><h1>Company Brain</h1><p>La conoscenza viva di OLTRE / DEPENDEX: Markdown → neuroni → sinapsi → RAG → Cortex.</p></div></section>
<section class="metric-grid"><div class="metric"><b><?=number_format($stats['nodes'],0,',','.')?></b><span>Neuroni</span></div><div class="metric"><b><?=number_format($stats['links'],0,',','.')?></b><span>Sinapsi</span></div><div class="metric"><b><?=number_format($stats['knowledge'],0,',','.')?></b><span>Documenti knowledge</span></div><div class="metric"><b><?=number_format($stats['files'],0,',','.')?></b><span>Fonti ingerite</span></div></section>
<section class="card"><h2>Pipeline cognitiva</h2><p><b>Codice / DB / ricerca / policy</b> → Knowledge Builder → Markdown Archive → Neuralog Inbox → chunk → nodi → sinapsi → RAG → Cortex.</p><p><b>Ultima attività:</b> <?=h($last?:'Nessuna attività registrata')?></p></section>
<section class="menu-list"><a href="modules/neuralog/ui/console.php">🧠 Console Neuralog <b>›</b></a><a href="modules/neuralog/ui/brain-3d.php">🌐 Cervello 3D <b>›</b></a><a href="modules/neuralog/ui/graph-2d.php">🕸 Grafo 2D <b>›</b></a><a href="cortex.php">✨ Apri Cortex <b>›</b></a></section>
<?php require '_footer.php';?>
