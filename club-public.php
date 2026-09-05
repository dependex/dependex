<?php
require_once 'bootstrap.php';$u=current_user();$sic=trim((string)($_GET['id']??$_GET['club']??''));
$st=db()->prepare("SELECT * FROM dependex_world_registry WHERE sic_id=? LIMIT 1");$st->execute([$sic]);$c=$st->fetch();
if(!$c){http_response_code(404);$pageTitle='Club non trovato';require '_header.php';echo '<section class="card"><h1>Club non trovato</h1></section>';require '_footer.php';exit;}
$pageTitle=$c['entity_name'];require '_header.php';?>
<section class="section-head"><div><span class="eyebrow"><?=h($c['network_level'])?> · <?=h($c['status'])?></span><h1><?=h($c['entity_name'])?></h1><p><?=h(implode(' · ',array_filter([$c['city'],$c['region'],$c['country']])))?></p></div></section>
<section class="card club-public-card"><div class="detail-grid">
<div><small>SIC-ID</small><b><?=h($c['sic_id'])?></b></div>
<div><small>Livello</small><b><?=h($c['network_level'])?></b></div>
<div><small>Rank Club</small><b><?=h($c['club_rank']?:'—')?></b></div>
<div><small>Status</small><b><?=h($c['status'])?></b></div>
<div><small>Indirizzo</small><b><?=h($c['address']?:'—')?></b></div>
<div><small>Incontri</small><b><?=h(trim(($c['meeting_day']??'').' '.($c['meeting_time']??''))?:'—')?></b></div>
<div><small>Telefono</small><b><?=h($c['phone']?:'—')?></b></div>
<div><small>Email</small><b><?=h($c['email']?:'—')?></b></div>
<div><small>Sito</small><b><?=h($c['website']?:'—')?></b></div>
<div><small>Geo</small><b><?=h($c['geo_accuracy']?:'—')?></b></div>
</div>
<div class="hero-actions"><?php if($c['website']):?><a class="btn" href="<?=h($c['website'])?>" rel="noopener" target="_blank">Sito</a><?php endif;?><?php if(in_array($c['geo_accuracy'],['CITY','MUNICIPALITY','POSTAL_CODE','STREET','EXACT'],true)&&$c['latitude']!==null&&$c['longitude']!==null):?><a class="btn" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?=urlencode($c['latitude'].','.$c['longitude'])?>">Indicazioni</a><?php endif;?><a class="btn" href="world-map.php">Torna alla mappa</a></div>
</section>
<?php require '_footer.php';?>