<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';

// Gestione Disiscrizione One-Click conforme RFC 8058 (senza autenticazione obbligatoria)
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$targetEmail = trim($_GET['email'] ?? ($_POST['email'] ?? ''));

if ($action === 'unsubscribe' && $targetEmail && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
    email_os_unsubscribe($targetEmail, 'USER_ONE_CLICK_RFC8058');
    
    // Se è una richiesta POST da client di posta conforme RFC 8058 One-Click
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Disiscrizione completata con successo per $targetEmail";
        exit;
    }

    $pageTitle = 'Disiscrizione Confermata';
    require '_header.php';
    ?>
    <section class="auth-card" style="max-width: 600px; margin: 60px auto; text-align: center; padding: 40px 24px;">
      <div style="font-size: 3rem; margin-bottom: 12px; color: #10b981;">✓</div>
      <h1 style="color: #f8fafc; margin-bottom: 12px;">Disiscrizione Confermata</h1>
      <p style="color: #94a3b8; font-size: 1.05rem; line-height: 1.6;">
        L'indirizzo <strong><?=htmlspecialchars($targetEmail)?></strong> è stato rimosso con successo da tutte le comunicazioni di marketing del Club Dependex.
      </p>
      <div style="margin-top: 24px;">
        <a href="index.php" class="btn primary">Torna alla Home</a>
      </div>
    </section>
    <?php
    require '_footer.php';
    exit;
}

// Gestione Preferenze per utenti autenticati
$u = require_login();
$st = db()->prepare('SELECT * FROM user_preferences WHERE user_sic_id=?');
$st->execute([$u['sic_id']]);
$p = $st->fetch() ?: ['leaderboard_opt_in'=>0,'leaderboard_display'=>'NICKNAME','nickname'=>'','voice_enabled'=>1,'reduced_motion'=>0,'locale'=>'it'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $opt = isset($_POST['leaderboard_opt_in']) ? 1 : 0;
    $display = in_array($_POST['leaderboard_display'] ?? 'NICKNAME', ['NAME','NICKNAME','ANONYMOUS','HIDDEN'], true) ? $_POST['leaderboard_display'] : 'NICKNAME';
    $nick = trim($_POST['nickname'] ?? '');
    $voice = isset($_POST['voice_enabled']) ? 1 : 0;
    $motion = isset($_POST['reduced_motion']) ? 1 : 0;
    $loc = preg_replace('/[^a-z-]/', '', strtolower($_POST['locale'] ?? 'it'));

    db()->prepare('
        INSERT INTO user_preferences(user_sic_id,leaderboard_opt_in,leaderboard_display,nickname,voice_enabled,reduced_motion,locale) 
        VALUES(?,?,?,?,?,?,?) 
        ON CONFLICT(user_sic_id) DO UPDATE SET 
            leaderboard_opt_in=excluded.leaderboard_opt_in,
            leaderboard_display=excluded.leaderboard_display,
            nickname=excluded.nickname,
            voice_enabled=excluded.voice_enabled,
            reduced_motion=excluded.reduced_motion,
            locale=excluded.locale,
            updated_at=CURRENT_TIMESTAMP
    ')->execute([$u['sic_id'], $opt, $display, $nick, $voice, $motion, $loc]);

    db()->prepare('INSERT INTO consent_log(sic_id,user_sic_id,consent_code,value,version) VALUES(?,?,?,?,?)')
        ->execute([sic_id(), $u['sic_id'], 'SOBRIETY_LEADERBOARD', $opt, '1.0']);

    audit($u['sic_id'], 'PRIVACY_PREFERENCES', $u['sic_id'], ['leaderboard'=>$opt, 'display'=>$display]);
    $msg = 'Preferenze salvate.';
    $p = ['leaderboard_opt_in'=>$opt, 'leaderboard_display'=>$display, 'nickname'=>$nick, 'voice_enabled'=>$voice, 'reduced_motion'=>$motion, 'locale'=>$loc];
}

$pageTitle = 'Privacy Center';
require '_header.php';
?>
<section class="section-head">
  <div>
    <span class="eyebrow">Private by default</span>
    <h1>Privacy Center</h1>
    <p>Decidi cosa condividere e gestisci i consensi nel rispetto totale del GDPR.</p>
  </div>
</section>

<?php if ($msg): ?>
  <div class="success"><?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<section class="card">
  <form method="post" class="stack">
    <input type="hidden" name="<?=CSRF_KEY?>" value="<?=htmlspecialchars(csrf_token())?>">
    <label>
      <input type="checkbox" name="leaderboard_opt_in" <?=$p['leaderboard_opt_in'] ? 'checked' : ''?>> 
      Partecipa volontariamente alla leaderboard sobrietà
    </label>
    <label>Come apparire
      <select name="leaderboard_display">
        <?php foreach(['NICKNAME'=>'Nickname','ANONYMOUS'=>'Anonimo','NAME'=>'Nome','HIDDEN'=>'Nascosto'] as $v=>$l): ?>
          <option value="<?=$v?>" <?=$p['leaderboard_display']===$v ? 'selected' : ''?>><?=$l?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Nickname
      <input name="nickname" value="<?=htmlspecialchars($p['nickname'])?>">
    </label>
    <label>
      <input type="checkbox" name="voice_enabled" <?=$p['voice_enabled'] ? 'checked' : ''?>> Funzioni vocali
    </label>
    <label>
      <input type="checkbox" name="reduced_motion" <?=$p['reduced_motion'] ? 'checked' : ''?>> Riduci animazioni
    </label>
    <button class="btn primary">Salva preferenze</button>
  </form>
</section>

<?php require '_footer.php'; ?>