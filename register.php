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
                require_once __DIR__ . '/email-engine.php';
                email_os_track_event('lead_captured', $email, ['nome' => $name, 'source' => 'register_form', 'user_sic' => $userSic]);
                $success=true;$recovery=$recoveryRaw;
            }catch(Throwable $e){
                if($pdo->inTransaction())$pdo->rollBack();
                $msg='Registrazione non completata. Riprova.';
            }
        }
    }
}
$pageTitle='Registrati'; require '_header.php';?>
<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px;">
  <section class="auth-card register-card rainbow-border" style="max-width: 520px; width: 100%; padding: 36px 28px; margin: 0 auto; box-shadow: 0 20px 60px rgba(0,0,0,0.9), var(--rainbow-glow);">
    <div style="text-align: center; margin-bottom: 24px;">
      <div class="brand-mark-rainbow" style="width: 64px; height: 64px; margin-bottom: 14px;">
        <img src="assets/img/dependex-rainbow-badge.jpg" alt="Logo DEPENDEX">
      </div>
      <div class="badge-neon-rainbow mb-2">
        <span class="dot"></span>
        <span class="text-rainbow">REGISTRAZIONE PROTETTA</span>
      </div>
      <h1 style="font-size: 2rem; color: #FFFFFF; margin: 8px 0 6px;">Crea il tuo Account</h1>
      <p style="color: #94a3b8; font-size: 0.92rem; line-height: 1.5; margin: 0;">
        Registrazione semplice e riservata. DEPENDEX riconosce automaticamente ruoli, Club e permessi.
      </p>
    </div>

    <?php if($success):?>
      <div class="success-panel text-center">
        <span class="badge-neon-rainbow mb-3"><span class="dot"></span> ACCOUNT CREATO</span>
        <h2 style="color: #FFFFFF; margin: 12px 0;">Benvenuto in DEPENDEX</h2>
        <p style="color: #cbd5e1; font-size: 0.95rem;">Il tuo account è attivo. Conserva questo recovery code in un luogo sicuro: serve per recuperare l’accesso senza email.</p>
        <div class="recovery-box" style="border-color: var(--neon-cyan); color: var(--neon-cyan); box-shadow: var(--glow-cyan);"><code><?=h($recovery)?></code></div>
        <p style="color: #94a3b8; font-size: 0.85rem;"><b>Importante:</b> questo codice viene mostrato ora in chiaro e non viene salvato in chiaro nel database.</p>
        <a class="btn primary" href="app.php" style="width: 100%; margin-top: 14px;">Entra nella dashboard</a>
      </div>
    <?php else:?>
      <form method="post" class="stack" autocomplete="on">
        <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
        <label><span>Nome e cognome</span><input type="text" name="display_name" maxlength="120" autocomplete="name" required value="<?=h($_POST['display_name']??'')?>" placeholder="Mario Rossi"></label>
        <label><span>Email</span><input type="email" name="email" maxlength="190" autocomplete="email" required value="<?=h($_POST['email']??'')?>" placeholder="mario.rossi@email.com"></label>
        <label><span>Password</span><input type="password" name="password" minlength="10" autocomplete="new-password" required placeholder="Minimo 10 caratteri"><small style="color:#94a3b8;">Almeno 10 caratteri.</small></label>
        <label><span>Ripeti password</span><input type="password" name="password_confirm" minlength="10" autocomplete="new-password" required placeholder="Ripeti la password"></label>
        <label class="check-row"><input type="checkbox" name="privacy_accept" value="1" required><span style="color:#cbd5e1; font-size: 0.88rem;">Ho letto e accetto la <a href="privacy.php" target="_blank" style="color:var(--neon-cyan); text-decoration: underline;">Privacy</a>.</span></label>
        <label class="check-row"><input type="checkbox" name="terms_accept" value="1" required><span style="color:#cbd5e1; font-size: 0.88rem;">Accetto le <a href="terms.php" target="_blank" style="color:var(--neon-cyan); text-decoration: underline;">Condizioni di utilizzo</a>.</span></label>
        <?php if($msg):?><div class="error" style="background: rgba(255, 51, 68, 0.15); border: 1px solid var(--neon-red); color: #ff8088;"><?=h($msg)?></div><?php endif;?>
        <button class="btn primary" style="width: 100%; margin-top: 8px;">Crea il mio account</button>
      </form>
      <p class="auth-switch" style="font-size: 0.92rem; color: #94a3b8; margin-top: 18px; text-align: center;">Hai già un account? <a href="login.php" style="color: var(--neon-gold); font-weight: 800;">Accedi qui</a></p>
    <?php endif;?>
  </section>
</div>
<?php require '_footer.php';?>