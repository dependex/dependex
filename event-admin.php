<?php
/**
 * DEPENDEX.SOCIAL - Event Attendance & Check-in Administration
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Auditable Attendance Verification, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_admin();

$sic = trim($_GET['event'] ?? $_POST['event'] ?? '');
$event = null;

if ($sic !== '') {
    $st = db()->prepare('SELECT * FROM events WHERE sic_id = ?');
    $st->execute([$sic]);
    $event = $st->fetch();
}

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event) {
    try {
        csrf_check();
        $reg = trim($_POST['registration'] ?? '');
        $r = db()->prepare('SELECT * FROM event_registrations WHERE sic_id = ? AND event_sic_id = ?');
        $r->execute([$reg, $sic]);
        $row = $r->fetch();

        if ($row && $row['status'] !== 'CHECKED_IN') {
            db()->prepare(
                "UPDATE event_registrations SET status = 'CHECKED_IN', checked_in_at = CURRENT_TIMESTAMP, verified_by_sic_id = ? WHERE sic_id = ?"
            )->execute([$u['sic_id'], $reg]);

            $reward = (float)($event['drx_reward'] ?? 0);
            if ($reward > 0) {
                drx_post(
                    $row['user_sic_id'],
                    null,
                    $reward,
                    'EVENT_ATTENDANCE',
                    true,
                    'event-checkin:' . $sic . ':' . $row['user_sic_id'],
                    $sic,
                    ['event' => $event['title']]
                );
            }
            audit($u['sic_id'], 'EVENT_CHECKIN', $reg, ['event' => $sic]);
            $msg = 'Presenza confermata con successo. Assegnazione DRX registrata.';
        } else {
            throw new RuntimeException('Registrazione non trovata o presenza già confermata.');
        }
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

if ($event) {
    $regs = db()->prepare(
        'SELECT er.*, u.display_name, u.email FROM event_registrations er JOIN users u ON u.sic_id = er.user_sic_id WHERE er.event_sic_id = ? ORDER BY er.created_at ASC'
    );
    $regs->execute([$sic]);
    $rows = $regs->fetchAll();

    $pageTitle = 'Presenze: ' . $event['title'] . ' · Dependex';
    $metaDesc = 'Gestione presenze e convalida check-in per ' . $event['title'];
} else {
    $allEvents = db()->query("SELECT * FROM events ORDER BY starts_at DESC")->fetchAll();
    $pageTitle = 'Gestione Presenze Eventi · Dependex';
    $metaDesc = 'Pannello amministrativo per la convalida delle presenze agli eventi e corsi territoriali.';
}

require '_header.php';
?>

<?php if (!$event): ?>
<section class="section-head">
    <div>
        <span class="eyebrow">Event Manager · Check-in</span>
        <h1>Gestione Presenze Eventi</h1>
        <p>Seleziona un evento o corso territoriale per verificare e confermare le presenze dei partecipanti registrati.</p>
    </div>
</section>

<div class="course-list">
    <?php if (empty($allEvents)): ?>
        <p style="opacity: 0.7; font-style: italic;">Nessun evento registrato nel sistema.</p>
    <?php else: ?>
        <?php foreach ($allEvents as $e): ?>
        <article class="course" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span class="course-cat"><?= h($e['type'] ?? 'EVENTO') ?></span>
                <h3 style="margin-top: 0.5rem;"><?= h($e['title']) ?></h3>
                <p style="font-size: 0.9rem; opacity: 0.85;"><?= h($e['starts_at'] ?? '') ?> · <?= h($e['venue'] ?? '') ?> (<?= h($e['comune'] ?? '') ?>)</p>
            </div>
            <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a class="btn primary small" href="event-admin.php?event=<?= urlencode($e['sic_id']) ?>">Gestisci Presenze</a>
                <a class="btn secondary small" target="_blank" href="event-visual.php?event=<?= urlencode($e['sic_id']) ?>&format=A4">Locandina A4</a>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="margin-top: 2rem;">
    <a href="graphic-studio.php" class="btn secondary">Studio Grafico Locandine</a>
    <a href="events.php" class="btn secondary">Calendario Pubblico</a>
</div>

<?php else: ?>

<section class="section-head">
    <div>
        <span class="eyebrow">Check-in Convalidato · <?= h($event['type'] ?? 'EVENTO') ?></span>
        <h1><?= h($event['title']) ?></h1>
        <p><?= h($event['starts_at'] ?? '') ?> · <?= h($event['venue'] ?? '') ?> (<?= h($event['comune'] ?? '') ?>)</p>
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
        <b><?= count($rows) ?></b>
        <span>Partecipanti Iscritti</span>
    </div>
    <div class="metric">
        <b><?= count(array_filter($rows, fn($r) => $r['status'] === 'CHECKED_IN')) ?></b>
        <span>Presenze Convalidate</span>
    </div>
    <div class="metric">
        <b><?= (float)($event['drx_reward'] ?? 0) ?> DRX</b>
        <span>Quota Presenza Partecipante</span>
    </div>
</section>

<section class="card" style="margin-top: 1.5rem;">
    <h2>Elenco Iscritti e Check-in</h2>
    <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
        La conferma presenza assegna la ricompensa DRX stabilita per l'evento.
    </p>

    <?php if (empty($rows)): ?>
        <p style="opacity: 0.7; font-style: italic; padding: 1rem 0;">Nessun partecipante iscritto a questo evento.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($rows as $r): ?>
            <article style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 1rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.3rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 4px; background: <?= $r['status'] === 'CHECKED_IN' ? 'rgba(34,197,94,0.2)' : 'rgba(234,179,8,0.2)' ?>; color: <?= $r['status'] === 'CHECKED_IN' ? '#4ade80' : '#facc15' ?>;">
                            <?= h($r['status']) ?>
                        </span>
                        <h4 style="margin: 0; font-size: 1.05rem;"><?= h($r['display_name']) ?></h4>
                    </div>
                    <div style="font-size: 0.85rem; opacity: 0.8;"><?= h($r['email']) ?></div>
                </div>

                <div>
                    <?php if ($r['status'] !== 'CHECKED_IN'): ?>
                        <form method="post" style="margin: 0;">
                            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="event" value="<?= h($sic) ?>">
                            <input type="hidden" name="registration" value="<?= h($r['sic_id']) ?>">
                            <button type="submit" class="btn primary small">Conferma Presenza</button>
                        </form>
                    <?php else: ?>
                        <span style="color: #4ade80; font-size: 0.9rem; font-weight: 600;">✓ Presente (<?= h(substr($r['checked_in_at'] ?? '', 0, 16)) ?>)</span>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="event-admin.php" class="btn secondary">Tutti gli Eventi</a>
    <a href="graphic-studio.php" class="btn secondary">Graphic Studio</a>
    <a href="club-admin.php" class="btn secondary">Club Admin</a>
</div>

<?php endif; ?>

<?php require '_footer.php'; ?>