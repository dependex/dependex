<?php
/**
 * DEPENDEX.SOCIAL - Social Impact Public Dashboard
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Community Value, Transparent Giving, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$projects = db()->query(
    "SELECT p.*, COALESCE(SUM(d.amount_eur),0) as donated,
            (SELECT COALESCE(SUM(hours),0) FROM volunteer_actions v WHERE v.project_sic_id = p.sic_id AND v.verified = 1) as verified_hours
     FROM projects p 
     LEFT JOIN donations d ON d.project_sic_id = p.sic_id AND d.status = 'CONFIRMED'
     WHERE p.status IN ('ACTIVE', 'PUBLISHED') 
     GROUP BY p.sic_id 
     ORDER BY p.id DESC"
)->fetchAll();

$totalDonated = (float)db()->query(
    "SELECT COALESCE(SUM(amount_eur),0) FROM donations WHERE status = 'CONFIRMED'"
)->fetchColumn();

$totalVolHours = (float)db()->query(
    "SELECT COALESCE(SUM(hours),0) FROM volunteer_actions WHERE verified = 1"
)->fetchColumn();

$pageTitle = 'Social Impact · Comunità e Solidarietà Territoriale';
$metaDesc = 'Progetti territoriali, volontariato multifamiliare e mutuo aiuto: la community restituisce valore reale.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Solidarity Engine · Territorio</span>
        <h1>La community restituisce valore reale.</h1>
        <p>Progetti territoriali, volontariato attivo e mutuo aiuto. Donare euro sostiene i costi vivi ma non compra status o titoli: il valore risiede nelle relazioni umane.</p>
    </div>
</section>

<section class="metric-grid">
    <div class="metric">
        <b>€ <?= number_format($totalDonated, 2, ',', '.') ?></b>
        <span>Fondi Solidali Convalidati</span>
    </div>
    <div class="metric">
        <b><?= number_format($totalVolHours, 1, ',', '.') ?> h</b>
        <span>Volontariato Convalidato</span>
    </div>
    <div class="metric">
        <b><?= count($projects) ?></b>
        <span>Progetti Attivi</span>
    </div>
</section>

<div class="course-list" style="margin-top: 2rem;">
    <?php if (empty($projects)): ?>
        <p style="opacity: 0.7; font-style: italic;">Nessun progetto sociale attivo in questo momento.</p>
    <?php else: ?>
        <?php foreach ($projects as $p): ?>
        <article class="course" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span class="course-cat"><?= h($p['category'] ?? 'SOLIDARIETÀ') ?></span>
                <h3 style="margin-top: 0.5rem;"><?= h($p['title']) ?></h3>
                <p style="opacity: 0.9;"><?= h($p['description']) ?></p>
            </div>
            
            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem; font-size: 0.9rem;">
                    <span><b>€ <?= number_format((float)$p['donated'], 2, ',', '.') ?></b> raccolti</span>
                    <?php if (!empty($p['goal_eur'])): ?>
                        <span style="opacity: 0.75;">Obiettivo: € <?= number_format((float)$p['goal_eur'], 0, ',', '.') ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ((float)$p['verified_hours'] > 0): ?>
                    <div style="font-size: 0.85rem; color: #4ade80; margin-bottom: 1rem;">
                        ✦ <?= (float)$p['verified_hours'] ?> ore di volontariato attivo
                    </div>
                <?php endif; ?>

                <a class="btn small primary" href="project.php?sic=<?= urlencode($p['sic_id']) ?>" style="display: block; text-align: center;">
                    Dettagli & Sostegno
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="club.php" class="btn secondary">Il Mio Club</a>
    <a href="social-admin.php" class="btn secondary">Pannello Convalida</a>
    <a href="finance.php" class="btn secondary">Tesoreria</a>
</div>

<?php require '_footer.php'; ?>