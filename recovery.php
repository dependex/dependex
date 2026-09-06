<?php
require_once 'bootstrap.php';
$stage='identify';$msg='';$ok='';$newRecovery='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check(); $action=$_POST['action']??'identify';
 if($action==='identify'){
   $email=trim($_POST['email']??''); $code=trim($_POST['recovery_code']??'');
   $st=db()->prepare('SELECT * FROM users WHERE email=? COLLATE NOCASE AND status="ACTIVE"');$st->execute([$email]);$u=$st->fetch();
   $trusted=$u?trusted_device_user($email):null;
   $valid=$u && (($code && password_verify(strtoupper($code),$u['recovery_code_hash']??'')) || $trusted);
   if($valid){ $_SESSION['password_reset_user']=$u['sic_id']; $_SESSION['password_reset_until']=time()+600; $stage='reset';}
   else $msg='Non riesco a verificare l’identità. Usa il recovery code personale oppure un dispositivo già riconosciuto. Se non li hai, contatta l’Admin del tuo Club.';
 } elseif($action==='reset'){
   if(empty($_SESSION['password_reset_user']) || ($_SESSION['password_reset_until']??0)<time()) {$msg='Verifica scaduta.';$stage='identify';}
   else {
     $p=$_POST['password']??'';$p2=$_POST['password2']??'';
     if(strlen($p)<10 || $p!==$p2){$msg='Le password devono coincidere e avere almeno 10 caratteri.';$stage='reset';}
     else {
       $sid=$_SESSION['password_reset_user']; $newRecovery=create_recovery_code();
       db()->prepare('UPDATE users SET password_hash=?,recovery_code_hash=?,recovery_code_changed_at=CURRENT_TIMESTAMP WHERE sic_id=?')
         ->execute([password_hash($p,PASSWORD_DEFAULT),password_hash($newRecovery,PASSWORD_DEFAULT),$sid]);
       db()->prepare('UPDATE trusted_devices SET revoked_at=CURRENT_TIMESTAMP WHERE user_sic_id=?')->execute([$sid]);
       audit($sid,'PASSWORD_RESET',$sid); unset($_SESSION['password_reset_user'],$_SESSION['password_reset_until']);$stage='done';
     }
   }
 }
}
$pageTitle='Recupera password';require '_header.php';?>
<section class="auth-card">
<?php if($stage==='identify'):?><h1>Recupera password</h1><p>Nessuna email da ricevere. Il reset avviene direttamente nel sistema.</p>
<form method="post" class="stack"><input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="identify">
<label>Email<input type="email" name="email" required></label>
<label>Recovery code <small>Se sei su un dispositivo già riconosciuto puoi lasciarlo vuoto.</small><input name="recovery_code" placeholder="XXXX-XXXX-XXXX-XXXX"></label>
<?php if($msg):?><div class="error"><?=h($msg)?></div><?php endif;?>
<button class="btn primary">Continua</button></form>
<?php elseif($stage==='reset'):?><h1>Crea nuova password</h1>
<form method="post" class="stack"><input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="reset">
<label>Nuova password<input type="password" name="password" minlength="10" required></label>
<label>Ripeti password<input type="password" name="password2" minlength="10" required></label>
<?php if($msg):?><div class="error"><?=h($msg)?></div><?php endif;?><button class="btn primary">Salva password</button></form>
<?php else:?><div class="success"><h1><?=dx_icon('check-circle','',24)?> Password registrata</h1><p>La nuova password è attiva.</p><p>Salva anche il nuovo recovery code:</p><div class="recovery-code"><?=h($newRecovery)?></div><a class="btn primary" href="login.php">Torna al login</a></div><?php endif;?>
</section><?php require '_footer.php';?>