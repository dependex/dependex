<?php
require_once 'bootstrap.php';
$u=require_login();
if(!acl_can($u['sic_id'],'research','MANAGE',null,$u['sic_id'])&&!has_role($u['sic_id'],'SUPERADMIN')){http_response_code(403);exit('Accesso non autorizzato');}
$summary=[
 'countries'=>(int)db()->query('SELECT COUNT(*) FROM country_research_status')->fetchColumn(),
 'sources'=>(int)db()->query('SELECT COUNT(*) FROM osint_sources')->fetchColumn(),
 'open_tasks'=>(int)db()->query("SELECT COUNT(*) FROM research_tasks WHERE status='OPEN'")->fetchColumn(),
 'clubs'=>(int)db()->query("SELECT COUNT(*) FROM dependex_world_registry WHERE network_level='LOCAL_CLUB'")->fetchColumn(),
 'real_geo'=>(int)db()->query("SELECT COUNT(*) FROM dependex_world_registry WHERE network_level='LOCAL_CLUB' AND geo_accuracy IN ('EXACT','STREET','POSTAL_CODE','CITY','MUNICIPALITY')")->fetchColumn(),
 'terms'=>(int)db()->query('SELECT COUNT(*) FROM research_terms WHERE active=1')->fetchColumn(),
 'geo_pending'=>(int)db()->query("SELECT COUNT(*) FROM geocode_queue WHERE status='PENDING'")->fetchColumn(),
];
$countries=db()->query("SELECT * FROM country_research_status ORDER BY completeness_score DESC,country")->fetchAll();
$tasks=db()->query("SELECT rt.*,d.entity_name,d.city FROM research_tasks rt LEFT JOIN dependex_world_registry d ON d.sic_id=rt.entity_sic_id WHERE rt.status='OPEN' ORDER BY CASE priority WHEN 'P0' THEN 0 WHEN 'P1' THEN 1 ELSE 2 END,id LIMIT 100")->fetchAll();
$pageTitle='Research Intelligence';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">DEPENDEX OSINT CORE</span><h1>Research Intelligence</h1><p>Fonti, copertura mondiale, geocoding, conflitti e backlog confluiscono direttamente nel registry operativo.</p></div></section>
<section class="metric-grid">
<div class="metric"><b><?=$summary['countries']?></b><span>Paesi</span></div><div class="metric"><b><?=$summary['clubs']?></b><span>Club locali</span></div><div class="metric"><b><?=$summary['sources']?></b><span>Fonti</span></div><div class="metric"><b><?=$summary['terms']?></b><span>Termini OSINT</span></div><div class="metric"><b><?=$summary['open_tasks']?></b><span>Task ricerca</span></div><div class="metric"><b><?=$summary['geo_pending']?></b><span>Geo pending</span></div>
</section>
<section class="card"><h2>Motore ricorsivo</h2><p>Ogni nuova denominazione locale alimenta il dizionario e genera nuove query per Paese/lingua. I task restano nel DB fino a verifica, deduplica o chiusura.</p></section><section class="card"><h2>Geo coverage reale</h2><p><b><?=$summary['real_geo']?></b> Club con geocodifica verificata/city-level. Le coordinate sintetiche rimangono solo fallback visual e non abilitano indicazioni stradali.</p><a class="btn" href="world-club-explorer.php">Apri World Explorer</a></section>
<section class="card"><h2>Copertura per Paese</h2><div class="table-scroll"><table class="data-table"><thead><tr><th>Paese</th><th>Score</th><th>Verificati</th><th>Probabili</th><th>Storici</th><th>Status ricerca</th></tr></thead><tbody><?php foreach($countries as $c):?><tr><td><?=h($c['country'])?></td><td><b><?=h((string)$c['completeness_score'])?>%</b></td><td><?=h((string)$c['verified_clubs'])?></td><td><?=h((string)$c['probable_clubs'])?></td><td><?=h((string)$c['historical_clubs'])?></td><td><?=h($c['current_network_status'])?></td></tr><?php endforeach;?></tbody></table></div></section>
<section class="card"><h2>Backlog prioritario</h2><div class="course-list"><?php foreach($tasks as $t):?><article class="course"><span class="course-cat"><?=h($t['priority'])?> · <?=h($t['task_type'])?></span><h3><?=h($t['entity_name'] ?: $t['country'])?></h3><p><?=h($t['query_text'])?></p><small><?=h($t['reason'])?></small></article><?php endforeach;?></div></section>
<?php require '_footer.php';?>
