<?php
/**
 * DEPENDEX.SOCIAL - Private Daily Journal & Reflection
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Trauma-Informed, Private by Design, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$date = date('Y-m-d');
$st = db()->prepare('SELECT * FROM journal_entries WHERE user_sic_id = ? AND entry_date = ?');
$st->execute([$u['sic_id'], $date]);
$row = $st->fetch() ?: ['mood' => 5, 'gratitude' => '', 'note' => ''];

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $m = max(1, min(10, (int)($_POST['mood'] ?? 5)));
        $g = trim($_POST['gratitude'] ?? '');
        $n = trim($_POST['note'] ?? '');

        db()->prepare(
            'INSERT INTO journal_entries(sic_id, user_sic_id, entry_date, mood, gratitude, note) 
             VALUES(?,?,?,?,?,?) 
             ON CONFLICT(user_sic_id, entry_date) 
             DO UPDATE SET mood = excluded.mood, gratitude = excluded.gratitude, note = excluded.note, updated_at = CURRENT_TIMESTAMP'
        )->execute([sic_id(), $u['sic_id'], $date, $m, $g, $n]);

        drx_post($u['sic_id'], null, 5, 'JOURNAL', true, 'journal:' . $u['sic_id'] . ':' . $date, null, ['date' => $date]);
        
        $row = ['mood' => $m, 'gratitude' => $g, 'note' => $n];
        $msg = 'Riflessione salvata con cura. +5 DRX assegnati per la costanza di oggi.';
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

$hist = db()->prepare('SELECT entry_date, mood FROM journal_entries WHERE user_sic_id = ? ORDER BY entry_date DESC LIMIT 30');
$hist->execute([$u['sic_id']]);
$history = array_reverse($hist->fetchAll());

$pageTitle = 'Diario Personale · Riflessione Quotidiana';
$metaDesc = 'Spazio privato di ascolto e gratitudine. I tuoi appunti rimangono strettamente confidenziali sul tuo dispositivo.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Private Journal · Spazio Protetto</span>
        <h1>Diario di Bordo Personale</h1>
        <p>Uno spazio intimo per ascoltarti, un giorno alla volta. Le tue note sono strettamente personali e non vengono mai condivise né usate per profilazioni.</p>
    </div>
</section>

<?php if ($msg): ?>
    <div class="success" role="alert" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #4ade80;">
        <?= h($msg) ?>
    </div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="error" role="alert" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171;">
        <?= h($err) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
    <section class="card">
        <h2>Riflessione di Oggi (<?= date('d/m/Y') ?>)</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
            Prenditi due minuti per te. Nessun giudizio, solo accoglienza.
        </p>

        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            
            <label>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                    <span>Come ti senti oggi (da 1 a 10)?</span>
                    <b id="moodVal" style="color: #60a5fa; font-size: 1.1rem;"><?= h((string)$row['mood']) ?> / 10</b>
                </div>
                <input type="range" min="1" max="10" name="mood" value="<?= h((string)$row['mood']) ?>" id="moodSlider" 
                       oninput="document.getElementById('moodVal').innerText = this.value + ' / 10'" 
                       style="width: 100%; height: 8px; cursor: pointer; accent-color: #60a5fa;">
            </label>
            
            <label style="margin-top: 1rem;">
                <span>Un momento o una cosa per cui provi gratitudine oggi:</span>
                <textarea name="gratitude" rows="3" placeholder="Un gesto, una parola di incoraggiamento, una tazza di tè..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;"><?= h($row['gratitude']) ?></textarea>
            </label>
            
            <label style="margin-top: 1rem;">
                <span>Scrivi liberamente pensieri, fatiche o traguardi:</span>
                <textarea name="note" rows="6" placeholder="Cosa è successo oggi? Come hai navigato le tue onde emotive..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;"><?= h($row['note']) ?></textarea>
            </label>
            
            <button type="submit" class="btn primary" style="margin-top: 0.75rem;">Salva Riflessione di Oggi</button>
        </form>
    </section>

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <section class="card">
            <h2>Cammino Ultimi 30 Giorni</h2>
            <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
                Traccia visiva delle tue sensazioni. Oscillare fa parte della vita.
            </p>
            
            <div id="moodChart" data-values='<?= h(json_encode($history, JSON_UNESCAPED_UNICODE)) ?>' style="padding: 1rem 0;"></div>
            
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; opacity: 0.7; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem;">
                <span>← 30 giorni fa</span>
                <span>Oggi →</span>
            </div>
        </section>

        <section class="card">
            <h3>Spazio di Ascolto Protetto</h3>
            <p style="font-size: 0.9rem; line-height: 1.5; opacity: 0.85;">
                <em>"Non puoi fermare le onde, ma puoi imparare a navigarle."</em> Se senti il bisogno di condividere una difficoltà con il Club, puoi parlarne durante l'incontro settimanale con le altre famiglie.
            </p>
            <div style="margin-top: 1rem;">
                <a href="club.php" class="btn secondary small">Vedi Orari del Club</a>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var e = document.getElementById('moodChart');
    if (!e) return;
    var a = JSON.parse(e.dataset.values || '[]');
    if (!a.length) {
        e.innerHTML = '<p style="opacity: 0.7; font-style: italic;">Nessuna registrazione recente. Inizia salvando la giornata di oggi!</p>';
        return;
    }
    var html = '<div style="display: flex; align-items: flex-end; gap: 4px; height: 120px; background: rgba(0,0,0,0.2); border-radius: 6px; padding: 8px;">';
    html += a.map(function(x) {
        var pct = Math.max(10, Math.min(100, x.mood * 10));
        var color = x.mood >= 7 ? '#4ade80' : (x.mood >= 4 ? '#60a5fa' : '#fb923c');
        return '<div title="' + x.entry_date + ': ' + x.mood + '/10" style="flex: 1; min-width: 4px; height: ' + pct + '%; background: ' + color + '; border-radius: 3px; transition: height 0.3s;" tabindex="0" role="img" aria-label="' + x.entry_date + ': ' + x.mood + ' su 10"></div>';
    }).join('');
    html += '</div>';
    e.innerHTML = html;
});
</script>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="checkin.php" class="btn secondary">Check-in Veloce</a>
    <a href="sobriety.php" class="btn secondary">Il Mio Cammino</a>
    <a href="lifestyle.php" class="btn secondary">Ruota della Vita</a>
</div>

<?php require '_footer.php'; ?>