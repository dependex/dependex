<?php
require_once 'bootstrap.php';
if(current_user()){
    header('Location: app.php');
    exit;
}
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();
    $email=trim($_POST['email']??'');
    $p=$_POST['password']??'';
    $bucket='login:'.hash('sha256',($_SERVER['REMOTE_ADDR']??'').'|'.strtolower($email));
    if(!rate_limit($bucket,8,300)){
        $msg='Troppi tentativi. Riprova tra qualche minuto.';
    }else{
        $st=db()->prepare('SELECT * FROM users WHERE email=? COLLATE NOCASE AND status="ACTIVE"');
        $st->execute([$email]);
        $u=$st->fetch();
        if($u&&password_verify($p,$u['password_hash'])){
            session_regenerate_id(true);
            $_SESSION['user_sic_id']=$u['sic_id'];
            db()->prepare('UPDATE users SET last_login_at=CURRENT_TIMESTAMP WHERE sic_id=?')->execute([$u['sic_id']]);
            make_trusted_device($u['sic_id']);
            daily_access_claim($u['sic_id']);
            sobriety_sync($u['sic_id']);
            audit($u['sic_id'],'LOGIN',$u['sic_id']);
            header('Location: app.php');
            exit;
        }
        $msg='Email o password non corretti.';
    }
}
$pageTitle='Accedi';
require '_header.php';
?>

<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px;">
  <section class="auth-card rainbow-border" style="max-width: 480px; width: 100%; padding: 36px 28px; margin: 0 auto; box-shadow: 0 20px 60px rgba(0,0,0,0.9), var(--rainbow-glow);">
    <div style="text-align: center; margin-bottom: 24px;">
      <div class="brand-mark-rainbow" style="width: 64px; height: 64px; margin-bottom: 14px;">
        <img src="assets/img/dependex-rainbow-badge.jpg" alt="Logo DEPENDEX">
      </div>
      <div class="badge-neon-rainbow mb-2">
        <span class="dot"></span>
        <span class="text-rainbow">ACCESSO PROTETTO SSL</span>
      </div>
      <h1 style="font-size: 2rem; color: #FFFFFF; margin: 8px 0 6px;">Bentornato 👋</h1>
      <p style="color: #94a3b8; font-size: 0.92rem; line-height: 1.5; margin: 0;">
        Un solo accesso sicuro. DEPENDEX riconosce automaticamente Club, ruoli, permessi e percorso.
      </p>
    </div>

    <form method="post" class="stack">
      <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
      <label>
        <span>Indirizzo Email</span>
        <input type="email" name="email" autocomplete="email" required placeholder="la.tua@email.com">
      </label>
      <label>
        <span>Password</span>
        <input type="password" name="password" autocomplete="current-password" required placeholder="••••••••••••">
      </label>
      <?php if($msg):?>
        <div class="error" style="background: rgba(255, 51, 68, 0.15); border: 1px solid var(--neon-red); color: #ff8088;">
          <?=h($msg)?>
        </div>
      <?php endif;?>
      <button class="btn primary" style="width: 100%; margin-top: 8px;">
        Accedi al tuo Cammino
      </button>
    </form>

    <a class="text-link" href="recovery.php" style="color: var(--neon-cyan); margin-top: 18px; font-size: 0.88rem;">
      Hai dimenticato la password?
    </a>
    <p class="auth-switch" style="font-size: 0.92rem; color: #94a3b8; margin-top: 16px;">
      Non hai ancora un account? <a href="register.php" style="color: var(--neon-gold); font-weight: 800;"><b>Registrati ora</b></a>
    </p>
  </section>
</div>

<?php require '_footer.php';?>