<?php
require_once 'bootstrap.php';
$has=(int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$msg=''; $recovery='';
if($_SERVER['REQUEST_METHOD']==='POST' && !$has){
    csrf_check();
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $p=$_POST['password']??''; $p2=$_POST['password2']??'';
    if(!$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($p)<10 || $p!==$p2) $msg='Controlla i dati. Password: almeno 10 caratteri e conferma identica.';
    else {
        $sid=sic_id('USER'); $recovery=create_recovery_code();
        $st=db()->prepare('INSERT INTO users(sic_id,email,display_name,password_hash,recovery_code_hash,recovery_code_changed_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)');
        $st->execute([$sid,$email,$name,password_hash($p,PASSWORD_DEFAULT),password_hash($recovery,PASSWORD_DEFAULT)]);
        db()->prepare('INSERT INTO user_roles(user_sic_id,role_code,status) VALUES(?,?,?)')->execute([$sid,'SUPERADMIN','ACTIVE']);
        db()->prepare('INSERT INTO sic_registry(sic_id,entity_type,status) VALUES(?,?,?)')->execute([$sid,'USER','ACTIVE']);
        audit($sid,'INSTALL_SUPERADMIN',$sid);
        $has=1;
    }
}
$pageTitle='Installazione'; require '_header.php'; ?>
<section class="card"><h1>Installazione iniziale</h1>
<?php if($recovery): ?><div class="success"><h2>Super Admin creato</h2><p>Salva questo recovery code in un luogo sicuro. Verrà mostrato una sola volta.</p><div class="recovery-code"><?=h($recovery)?></div><a class="btn primary" href="login.php">Vai al login</a></div>
<?php elseif($has): ?><p>Il sistema risulta già inizializzato.</p><a class="btn primary" href="login.php">Login</a>
<?php else: ?><form method="post" class="stack">
<input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
<label>Nome<input name="name" required></label><label>Email<input type="email" name="email" required></label>
<label>Password<input type="password" name="password" minlength="10" required></label>
<label>Ripeti password<input type="password" name="password2" minlength="10" required></label>
<?php if($msg):?><div class="error"><?=h($msg)?></div><?php endif;?><button class="btn primary">Crea Super Admin</button></form><?php endif; ?></section>
<?php require '_footer.php'; ?>