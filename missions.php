<?php
/**
 * DEPENDEX.SOCIAL - Micro-Missions & Real Life Movement
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Screen Off -> Life On, Small Steps Engine, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $sic = trim($_POST['mission_sic'] ?? '');
        $st = db()->prepare('SELECT * FROM missions WHERE sic_id = ? AND status = "ACTIVE"');
        $st->execute([$sic]);
        $mission = $st->fetch();

        if ($mission) {
            $reward = (float)($mission['drx_reward'] ?? 0);
            $today = date('Y-m-d');
            $idempotencyKey = 'mission:' . $u['sic_id'] . ':' . $sic . ':' . $today;

            if ($reward > 0) {
                drx_post(
                    $u['sic_id'],
                    null,
                    $reward,
                    'MISSION_COMPLETION',
                    true,
                    $idempotencyKey,
                    $sic,
                    ['mission' => $mission['title']]
                );
            }
            audit($u['sic_id'], 'MISSION_COMPLETE', $sic, ['title' => $mission['title']]);
            $msg = "Bravissimo! Hai completato: \"{$mission['title']}\". Assegnati +{$reward} DRX.";
        } else {
            throw new RuntimeException('Missione non trovata o non più attiva.');
        }
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

$missions = db()->query("SELECT * FROM missions WHERE status = 'ACTIVE' ORDER BY category, title")->fetchAll();

$pageTitle = 'Piccoli Passi · Micro-Missioni nella Vita Reale';
$metaDesc = 'Micro-azioni per riconnetterti con te stesso, con gli altri e con il territorio: Screen Off, Life On.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Small Steps Engine · Vita Reale</span>
        <h1>Piccoli Passi. Vita Reale.</h1>
        <p><em>Screen Off → Life On:</em> le micro-missioni non ti trattengono sullo schermo, ma ti spingono all'incontro, all'azione concreta e al respiro nel mondo reale.</p>
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

<div class="course-list" style="margin-top: 2rem;">
    <?php if (empty($missions)): ?>
        <p style="opacity: 0.7; font-style: italic;">Nessuna missione attiva al momento.</p>
    <?php else: ?>
        <?php foreach ($missions as $m): ?>
        <article class="course" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span class="course-cat"><?= h($m['category'] ?? 'AZIONE') ?></span>
                <h3 style="margin-top: 0.5rem;"><?= h($m['title']) ?></h3>
                <p style="opacity: 0.9; line-height: 1.5;"><?= h($m['description']) ?></p>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 700; color: #4ade80; font-size: 0.95rem;">
                    ✦ +<?= h((string)$m['drx_reward']) ?> DRX
                </span>

                <form method="post" style="margin: 0;">
                    <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="mission_sic" value="<?= h($m['sic_id']) ?>">
                    <button type="submit" class="btn small primary">Completa Passo</button>
                </form>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="journal.php" class="btn secondary">Diario Personale</a>
    <a href="sobriety.php" class="btn secondary">Il Mio Cammino</a>
    <a href="lifestyle.php" class="btn secondary">Ruota della Vita</a>
    <a href="club.php" class="btn secondary">Il Mio Club</a>
</div>

<?php require '_footer.php'; ?>