<?php
require_once 'bootstrap.php';
$u=require_admin();
if(!has_role($u['sic_id'],['NATIONAL_ADMIN','INTERNATIONAL_ADMIN','CONTINENTAL_ADMIN','WORLD_ADMIN','SUPERADMIN'])){
    http_response_code(403);exit('Accesso non autorizzato.');
}
$pdo=db();
$stats=[
 'docs'=>(int)$pdo->query("SELECT COUNT(*) FROM ncke_documents WHERE status='ACTIVE'")->fetchColumn(),
 'chunks'=>(int)$pdo->query("SELECT COUNT(*) FROM ncke_chunks")->fetchColumn(),
 'queries'=>(int)$pdo->query("SELECT COUNT(*) FROM ncke_queries")->fetchColumn(),
 'reviews'=>(int)$pdo->query("SELECT COUNT(*) FROM ncke_human_review WHERE status='OPEN'")->fetchColumn(),
];
try{$neurons=(int)$pdo->query("SELECT COUNT(*) FROM brain_nodes")->fetchColumn();}catch(Throwable $e){$neurons=0;}
try{$edges=(int)$pdo->query("SELECT COUNT(*) FROM dependex_world_edges")->fetchColumn();}catch(Throwable $e){$edges=0;}
$status=$pdo->query("SELECT * FROM ncke_runtime_status WHERE id=1")->fetch() ?: [];
$recent=$pdo->query("SELECT sic_id,query_text,intent,confidence,provider,created_at FROM ncke_queries ORDER BY id DESC LIMIT 10")->fetchAll();
$reviews=$pdo->query("SELECT * FROM ncke_human_review WHERE status='OPEN' ORDER BY priority DESC,id DESC LIMIT 20")->fetchAll();
$pageTitle='Company Brain / NCKE';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">NEURALOG · CORTEX · NCKE</span><h1>Company Brain</h1>
<p>Conoscenza viva del progetto, retrieval ibrido, grafo, AI routing e governance.</p></div></section>

<section class="metric-grid">
<div class="metric"><b><?=$stats['docs']?></b><span>Documenti knowledge</span></div>
<div class="metric"><b><?=$stats['chunks']?></b><span>Chunk FTS</span></div>
<div class="metric"><b><?=$neurons?></b><span>Neuroni</span></div>
<div class="metric"><b><?=$edges?></b><span>Sinapsi</span></div>
</section>

<section class="card"><h2>Stato motore</h2>
<p><b><?=h($status['status']??'UNKNOWN')?></b> · modalità <?=h($status['mode']??'adaptive')?></p>
<p class="muted">Il motore parte con SQLite FTS5 + Neuralog Graph e attiva provider/vector/realtime tramite adapter quando disponibili.</p>
<div class="hero-actions"><a class="btn primary" href="ncke-console.php">Apri Query Console</a><a class="btn" href="integrations.php">Adapter</a></div>
</section>

<section class="card"><h2>Human Review Queue</h2>
<p>Le risposte a bassa confidenza o conflitto possono essere escalated a un operatore autorizzato.</p>
<?php if(!$reviews):?><p class="muted">Nessuna revisione aperta.</p><?php else:?><div class="table-wrap"><table><thead><tr><th>Query</th><th>Motivo</th><th>Priorità</th></tr></thead><tbody>
<?php foreach($reviews as $r):?><tr><td><?=h($r['query_sic_id'])?></td><td><?=h($r['reason'])?></td><td><?=h((string)$r['priority'])?></td></tr><?php endforeach;?>
</tbody></table></div><?php endif;?>
</section>

<section class="card"><h2>Query recenti</h2>
<?php if(!$recent):?><p class="muted">Ancora nessuna query.</p><?php else:?><div class="table-wrap"><table><thead><tr><th>Query</th><th>Intent</th><th>Conf.</th><th>Provider</th></tr></thead><tbody>
<?php foreach($recent as $r):?><tr><td><?=h($r['query_text'])?></td><td><?=h($r['intent'])?></td><td><?=h((string)$r['confidence'])?></td><td><?=h($r['provider'])?></td></tr><?php endforeach;?>
</tbody></table></div><?php endif;?>
</section>
<?php require '_footer.php';?>