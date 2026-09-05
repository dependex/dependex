<?php
require_once __DIR__.'/bootstrap.php';
if(current_user()){header('Location: app.php');exit;}
$msg=''; $success=false; $recovery='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    $name=trim((string)($_POST['display_name']??''));
    $email=strtolower(trim((string)($_POST['email']??'')));
    $password=(string)($_POST['password']??'');
    $password2=(string)($_POST['password_confirm']??'');
    $privacy=!empty($_POST['privacy_accept']);
    $terms=!empty($_POST['terms_accept']);
    $bucket='register:'.hash('sha256',($_SERVER['REMOTE_ADDR']??'').'|'.$email);

    if(!rate_limit($bucket,5,900)) $msg='Troppi tentativi di registrazione. Riprova più tardi.';
    elseif(mb_strlen($name)<2) $msg='Inserisci il tuo nome.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $msg='Inserisci un indirizzo email valido.';
    elseif(strlen($password)<10) $msg='La password deve contenere almeno 10 caratteri.';
    elseif($password!==$password2) $msg='Le due password non coincidono.';
    elseif(!$privacy||!$terms) $msg='Per creare l’account devi accettare Privacy e Condizioni di utilizzo.';
    else{
        $pdo=db();
        $st=$pdo->prepare('SELECT sic_id FROM users WHERE email=? COLLATE NOCASE');
        $st->execute([$email]);
        if($st->fetchColumn()) $msg='Esiste già un account associato a questa email.';
        else{
            $userSic=sic_id();
            $recoveryRaw=strtoupper(substr(bin2hex(random_bytes(10)),0,4).'-'.substr(bin2hex(random_bytes(10)),0,4).'-'.substr(bin2hex(random_bytes(10)),0,4));
            $pdo->beginTransaction();
            try{
                $pdo->prepare('INSERT INTO users(sic_id,email,display_name,password_hash,recovery_code_hash,recovery_code_changed_at,status,rank_name,drx_balance) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP,"ACTIVE","SEME",0)')
                    ->execute([$userSic,$email,$name,password_hash($password,PASSWORD_DEFAULT),password_hash($recoveryRaw,PASSWORD_DEFAULT)]);
                $pdo->prepare('INSERT INTO user_roles(user_sic_id,role_code,scope_sic_id,status) VALUES(?,"USER",NULL,"ACTIVE")')
                    ->execute([$userSic]);
                foreach([['PRIVACY','2026-08-18'],['TERMS','2026-08-18']] as [$type,$version]){
                    $pdo->prepare('INSERT INTO user_consents(sic_id,user_sic_id,consent_type,version,accepted,ip_hash,user_agent_hash) VALUES(?,?,?,?,1,?,?)')
                        ->execute([sic_id(),$userSic,$type,$version,hash('sha256',$_SERVER['REMOTE_ADDR']??''),hash('sha256',$_SERVER['HTTP_USER_AGENT']??'')]);
                }
                $pdo->prepare('INSERT INTO registration_events(sic_id,user_sic_id,email_hash,status,ip_hash) VALUES(?,?,?,"SUCCESS",?)')
                    ->execute([sic_id(),$userSic,hash('sha256',$email),hash('sha256',$_SERVER['REMOTE_ADDR']??'')]);
                $pdo->commit();

                session_regenerate_id(true);
                $_SESSION['user_sic_id']=$userSic;
                make_trusted_device($userSic);
                audit($userSic,'REGISTER',$userSic,['role'=>'USER']);
                $success=true;$recovery=$recoveryRaw;
            }catch(Throwable $e){
                if($pdo->inTransaction())$pdo->rollBack();
                $msg='Registrazione non completata. Riprova.';
            }
        }
    }
}
$pageTitle='Registrati'; require '_header.php';?>
<section class="auth-card register-card">
<?php if($success):?>
<div class="success-panel">
  <span class="eyebrow">ACCOUNT CREATO</span>
  <h1>Benvenuto in DEPENDEX</h1>
  <p>Il tuo account è attivo. Conserva questo recovery code in un luogo sicuro: serve per recuperare l’accesso senza email.</p>
  <div class="recovery-box"><code><?=h($recovery)?></code></div>
  <p><b>Importante:</b> questo codice viene mostrato ora in chiaro e non viene salvato in chiaro nel database.</p>
  <a class="btn primary" href="app.php">Entra nella dashboard</a>
</div>
<?php else:?>
<h1>Crea il tuo account</h1>
<p>Registrazione semplice. Dopo l’accesso OLTRE riconosce automaticamente ruoli, Club e permessi.</p>
<form method="post" class="stack" autocomplete="on">
  <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
  <label>Nome e cognome<input type="text" name="display_name" maxlength="120" autocomplete="name" required value="<?=h($_POST['display_name']??'')?>"></label>
  <label>Email<input type="email" name="email" maxlength="190" autocomplete="email" required value="<?=h($_POST['email']??'')?>"></label>
  <label>Password<input type="password" name="password" minlength="10" autocomplete="new-password" required><small>Almeno 10 caratteri.</small></label>
  <label>Ripeti password<input type="password" name="password_confirm" minlength="10" autocomplete="new-password" required></label>
  <label class="check-row"><input type="checkbox" name="privacy_accept" value="1" required><span>Ho letto e accetto la <a href="privacy.php" target="_blank">Privacy</a>.</span></label>
  <label class="check-row"><input type="checkbox" name="terms_accept" value="1" required><span>Accetto le <a href="terms.php" target="_blank">Condizioni di utilizzo</a>.</span></label>
  <?php if($msg):?><div class="error"><?=h($msg)?></div><?php endif;?>
  <button class="btn primary">Crea account</button>
</form>
<p class="auth-switch">Hai già un account? <a href="login.php">Accedi</a></p>
<?php endif;?>
</section>
<?php require '_footer.php';?>