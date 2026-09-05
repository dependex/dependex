<?php
require_once 'bootstrap.php';
$u = require_login();
$date = date('Y-m-d');
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = [];
    foreach (['mood', 'stress', 'craving', 'sleep'] as $k) {
        $v[$k] = max(0, min(10, (int)($_POST[$k] ?? 5)));
    }
    $note = trim($_POST['note'] ?? '');
    db()->prepare('INSERT INTO checkins(sic_id,user_sic_id,checkin_date,mood,stress,craving,sleep,note) VALUES(?,?,?,?,?,?,?,?) ON CONFLICT(user_sic_id,checkin_date) DO UPDATE SET mood=excluded.mood,stress=excluded.stress,craving=excluded.craving,sleep=excluded.sleep,note=excluded.note')->execute([sic_id(), $u['sic_id'], $date, $v['mood'], $v['stress'], $v['craving'], $v['sleep'], $note]);
    drx_post($u['sic_id'], null, 5, 'CHECKIN', true, 'checkin:'.$u['sic_id'].':'.$date, null, ['date' => $date]);
    $msg = 'Check-in registrato con successo! +5 DRX';
}

$st = db()->prepare('SELECT * FROM checkins WHERE user_sic_id=? AND checkin_date=?');
$st->execute([$u['sic_id'], $date]);
$curr = $st->fetch() ?: ['mood' => 7, 'stress' => 3, 'craving' => 0, 'sleep' => 7, 'note' => ''];

$pageTitle = 'Daily Check-in';
require '_header.php';
?>

<section class="section-head">
  <div>
    <span class="eyebrow">Automonitoraggio Quotidiano</span>
    <h1>Come stai oggi?</h1>
    <p class="muted">È un momento di consapevolezza e ascolto personale, non una diagnosi medica.</p>
  </div>
</section>

<?php if ($msg): ?>
  <div class="success" role="status" style="margin-bottom: 14px;"><?=h($msg)?></div>
<?php endif; ?>

<section class="card">
  <form method="post" class="stack">
    <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">

    <?php
    $fields = [
      'mood' => ['label' => '😊 Umore generale', 'desc' => '1 = Molto abbattuto · 10 = Pieno di energia e fiducia'],
      'stress' => ['label' => '🧘 Livello di Stress', 'desc' => '0 = Massima serenità · 10 = Stress molto intenso'],
      'craving' => ['label' => '🛡️ Desiderio / Impulso (Craving)', 'desc' => '0 = Nessun impulso · 10 = Impulso forte da condividere'],
      'sleep' => ['label' => '🌙 Qualità del riposo e sonno', 'desc' => '0 = Sonno pessimo o insonnia · 10 = Riposo ristoratore']
    ];
    foreach ($fields as $k => $f):
      $val = (int)($curr[$k] ?? 5);
    ?>
      <div class="checkin-item">
        <div class="checkin-item-header">
          <label for="f_<?=$k?>"><?=$f['label']?></label>
          <b id="val_<?=$k?>"><?=$val?>/10</b>
        </div>
        <small class="muted" style="font-size: .78rem;"><?=$f['desc']?></small>
        <input type="range" min="0" max="10" name="<?=$k?>" id="f_<?=$k?>" value="<?=$val?>" class="checkin-slider" style="margin-top: 6px;">
      </div>
    <?php endforeach; ?>

    <label>
      <span>Pensiero o nota personale di oggi (opzionale)</span>
      <small class="muted" style="display:block; margin-bottom: 4px;">Solo per te, non visibile pubblicamente.</small>
      <textarea name="note" rows="3" placeholder="Come ti sei sentito oggi? C'è qualcosa che vuoi ricordare?"><?=h($curr['note'])?></textarea>
    </label>

    <button class="btn primary" style="width: 100%; margin-top: 10px;">
      Salva Check-in · Guadagna +5 DRX
    </button>
  </form>
</section>

<section class="card" style="text-align: center;">
  <p class="muted" style="font-size: .9rem; margin-bottom: 12px;">
    Vuoi approfondire con parole tue?
  </p>
  <a class="btn" href="journal.php">✍ Vai al Diario Personale</a>
</section>

<?php require '_footer.php'; ?>