<?php
/**
 * DEPENDEX.SOCIAL - Project Details & Social Impact Action
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Trauma-Informed, Zero Punteggi Clinici (Divieto Punteggio Benessere), Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$sic = trim($_GET['sic'] ?? $_POST['sic'] ?? '');
$project = null;

if ($sic !== '') {
    $st = db()->prepare('SELECT * FROM projects WHERE sic_id = ?');
    $st->execute([$sic]);
    $project = $st->fetch();
}

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $project) {
    try {
        csrf_check();
        $act = $_POST['act'] ?? '';

        if ($act === 'volunteer') {
            $hours = max(0, (float)($_POST['hours'] ?? 0));
            $desc = trim($_POST['description'] ?? '');
            if ($hours <= 0 || $desc === '') {
                throw new InvalidArgumentException('Specificare un numero valido di ore e una descrizione sintetica.');
            }
            $vs = sic_id();
            db()->prepare(
                'INSERT INTO volunteer_actions(sic_id, project_sic_id, user_sic_id, hours, description, verified) VALUES(?,?,?,?,?,0)'
            )->execute([$vs, $sic, $u['sic_id'], $hours, $desc]);
            $msg = 'Attività di volontariato inviata con successo. La convalida avverrà a cura del comitato.';
        } elseif ($act === 'donate') {
            $amount = max(0, (float)($_POST['amount'] ?? 0));
            if ($amount <= 0) {
                throw new InvalidArgumentException('Specificare un importo valido in Euro per l\'impegno di donazione.');
            }
            db()->prepare(
                'INSERT INTO donations(sic_id, project_sic_id, donor_user_sic_id, amount_eur, anonymous) VALUES(?,?,?,?,?)'
            )->execute([sic_id(), $sic, $u['sic_id'], $amount, isset($_POST['anonymous']) ? 1 : 0]);
            $msg = 'Impegno di donazione registrato. La quietanza contabile sarà emessa a ricezione fondi confermata.';
        }
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

if ($project) {
    $sum = db()->prepare('SELECT COALESCE(SUM(amount_eur),0) FROM donations WHERE project_sic_id = ?');
    $sum->execute([$sic]);
    $donated = (float)$sum->fetchColumn();

    $volCountSt = db()->prepare('SELECT COUNT(*), COALESCE(SUM(hours),0) FROM volunteer_actions WHERE project_sic_id = ? AND verified = 1');
    $volCountSt->execute([$sic]);
    [$volCount, $volHours] = $volCountSt->fetch(PDO::FETCH_NUM);

    $pageTitle = $project['title'] . ' · Social Impact';
    $metaDesc = 'Progetto comunitario: ' . mb_substr($project['description'] ?? '', 0, 140);
} else {
    $allProjects = db()->query("SELECT p.*, COALESCE(SUM(d.amount_eur), 0) as total_donated FROM projects p LEFT JOIN donations d ON d.project_sic_id = p.sic_id GROUP BY p.sic_id ORDER BY p.id DESC")->fetchAll();
    $pageTitle = 'Progetti Social Impact · Dependex';
    $metaDesc = 'Esplora e sostieni i progetti solidali delle comunità multifamiliari territoriali.';
}

require '_header.php';
?>

<?php if (!$project): ?>
<section class="section-head">
    <div>
        <span class="eyebrow">Solidarity Engine</span>
        <h1>Progetti Social Impact</h1>
        <p>Iniziative comunitarie, mutuo aiuto e volontariato territoriale. Seleziona un progetto da esplorare.</p>
    </div>
</section>

<div class="course-list">
    <?php foreach ($allProjects as $p): ?>
    <article class="course">
        <span class="course-cat"><?= h($p['category'] ?? 'SOLIDARITY') ?></span>
        <h3><?= h($p['title']) ?></h3>
        <p><?= h($p['description']) ?></p>
        <p><b>€ <?= number_format((float)$p['total_donated'], 2, ',', '.') ?></b> raccolti<?= !empty($p['goal_eur']) ? ' su obiettivo di € ' . number_format((float)$p['goal_eur'], 2, ',', '.') : '' ?></p>
        <div style="margin-top: 1rem;">
            <a class="btn primary small" href="project.php?sic=<?= urlencode($p['sic_id']) ?>">Apri Scheda Progetto</a>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<div style="margin-top: 2rem;">
    <a href="social-impact.php" class="btn secondary">Torna a Social Impact Dashboard</a>
</div>

<?php else: ?>

<section class="section-head">
    <div>
        <span class="eyebrow">Social Impact · <?= h($project['category'] ?? 'SOLIDARITY') ?></span>
        <h1><?= h($project['title']) ?></h1>
        <p><?= h($project['description']) ?></p>
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

<section class="metric-grid">
    <div class="metric">
        <b>€ <?= number_format($donated, 2, ',', '.') ?></b>
        <span>Raccolti<?= !empty($project['goal_eur']) ? ' (Ob. € ' . number_format((float)$project['goal_eur'], 0, ',', '.') . ')' : '' ?></span>
    </div>
    <div class="metric">
        <b><?= (float)$volHours ?> h</b>
        <span>Volontariato convalidato</span>
    </div>
    <div class="metric">
        <b><?= h($project['status'] ?? 'ACTIVE') ?></b>
        <span>Stato Iniziativa</span>
    </div>
    <div class="metric">
        <b style="font-size: 1rem; word-break: break-all;"><?= h($project['sic_id']) ?></b>
        <span>Identificativo SIC-ID</span>
    </div>
</section>

<section class="home-modules">
    <article class="card">
        <h3>Attività di Volontariato</h3>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Dona il tuo tempo sul territorio. Il volontariato convalidato dal Club alimenta la reputazione comunitaria.
        </p>
        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="sic" value="<?= h($sic) ?>">
            <input type="hidden" name="act" value="volunteer">
            
            <label>
                <span>Ore dedicate (minimo 0.5):</span>
                <input type="number" step="0.5" min="0.5" name="hours" placeholder="Es. 2.5" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label>
                <span>Descrizione attività svolta:</span>
                <textarea name="description" placeholder="Descrivi brevemente l'attività comunitaria svolta..." required rows="3" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;"></textarea>
            </label>
            
            <button type="submit" class="btn primary" style="margin-top: 0.5rem;">Invia per Convalida</button>
        </form>
    </article>

    <article class="card">
        <h3>Impegno di Donazione Solidale</h3>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Sostieni i costi vivi del progetto. La contabilità viene registrata con trasparenza nel registro di tesoreria.
        </p>
        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="sic" value="<?= h($sic) ?>">
            <input type="hidden" name="act" value="donate">
            
            <label>
                <span>Importo Donazione (€):</span>
                <input type="number" step="1" min="1" name="amount" placeholder="Es. 25" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="anonymous" value="1" style="width: 18px; height: 18px;">
                <span>Mostra la donazione in forma anonima nel rendiconto pubblico</span>
            </label>
            
            <button type="submit" class="btn" style="margin-top: 0.5rem;">Registra Impegno Donazione</button>
        </form>
    </article>
</section>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="social-impact.php" class="btn secondary">Tutti i Progetti</a>
    <a href="finance.php" class="btn secondary">Rendiconto Tesoreria</a>
</div>

<?php endif; ?>

<?php require '_footer.php'; ?>