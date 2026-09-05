<?php
require_once 'bootstrap.php';$u=require_admin();
require_once __DIR__.'/ncke/runtime/SecureEnvDiscovery.php';
require_once __DIR__.'/ncke/runtime/AiProviderResolver.php';
if(!has_role($u['sic_id'],['WORLD_ADMIN','SUPERADMIN'])){http_response_code(403);exit('Accesso non autorizzato.');}
$roots=[__DIR__,dirname(__DIR__),__DIR__.'/config',__DIR__.'/secrets'];
$env=new SecureEnvDiscovery($roots);$env->load();$resolver=new AiProviderResolver($_ENV);$status=$resolver->status();
$pageTitle='AI Providers';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">SECRET-SAFE RUNTIME</span><h1>AI Provider Resolver</h1>
<p>Mostra solo disponibilità, nome variabile e fingerprint. I valori reali non vengono mai esposti.</p></div></section>
<section class="course-list">
<?php if(!$status):?><article class="course"><h3>Nessun provider rilevato</h3><p>Carica i file .env autorizzati sull'host: il resolver li individuerà senza richiedere nomi standard.</p></article>
<?php else: foreach($status as $name=>$s):?><article class="course"><span class="course-cat"><?=h(strtoupper($name))?></span><h3><?=$s['ready']?'READY':'NOT READY'?></h3>
<?php foreach($s['credentials'] as $c):?><p><?=h($c['env_name'])?> · fingerprint <?=h($c['fingerprint'])?></p><?php endforeach;?></article><?php endforeach; endif;?>
</section><?php require '_footer.php';?>