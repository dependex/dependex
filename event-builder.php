<?php $u=require_once 'bootstrap.php';$u=require_login();$saved=false;
if($_SERVER['REQUEST_METHOD']==='POST'){csrf_check();$sid=sic_id('EVENT');db()->prepare('INSERT INTO events(sic_id,type,title,description,starts_at,venue,visibility,rank_required,drx_reward,status) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([$sid,$_POST['type'],trim($_POST['title']),trim($_POST['description']),$_POST['starts_at'],trim($_POST['venue']),$_POST['visibility'],$_POST['rank_required'],(int)$_POST['drx_reward'],'PUBLISHED']);audit($u['sic_id'],'CREATE_EVENT',$sid);$saved=true;}
$pageTitle='Crea evento';require '_header.php';?>
<section class="card"><span class="eyebrow">Event Factory</span><h1>Crea evento</h1><p>Una volta salvato, il Graphic Studio può produrre volantino, brochure e PDF stampa.</p>
<?php if($saved):?><div class="success">✓ Evento creato e registrato con SIC-ID.</div><?php endif;?>
<form method="post" class="stack"><input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
<label>Tipo<select name="type"><option>INTERCLUB</option><option>WEBINAR</option><option>VIAGGIO</option><option>SAT</option><option>VOLONTARIATO</option><option>LIFESTYLE</option></select></label>
<label>Titolo<input name="title" required></label><label>Descrizione<textarea name="description" rows="4"></textarea></label>
<label>Data e ora<input type="datetime-local" name="starts_at"></label><label>Luogo / Online<input name="venue"></label>
<label>Visibilità<select name="visibility"><option>PUBLIC</option><option>MEMBERS</option><option>CLUB</option></select></label>
<label>Rank minimo<select name="rank_required"><?php foreach(['SEME','GERMOGLIO','RADICI','RAMO','FIORE','ALBERO','BOSCO','GUIDA','MASTER'] as $r):?><option><?=$r?></option><?php endforeach;?></select></label>
<label>Reward DRX<input type="number" name="drx_reward" value="50" min="0"></label>
<button class="btn primary">Crea evento</button></form>
<a class="btn" href="graphic-studio.php">Apri AI Graphic Studio</a></section>
<?php require '_footer.php';?>