<?php
/**
 * DEPENDEX.SOCIAL - Graphic Studio & Event Asset Generator
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Multi-Format Visual Generator: A4, A5, Story, Square
 */
require_once 'bootstrap.php';
$u = require_login();

$events = db()->query(
    "SELECT * FROM events WHERE status IN ('PUBLISHED', 'DRAFT') ORDER BY starts_at DESC"
)->fetchAll();

$pageTitle = 'Graphic Studio & Locandine · Dependex';
$metaDesc = 'Generatore di locandine e formati visual per eventi comunitari, corsi territoriali e assemblee dei Club.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Visual Generator · Territorio</span>
        <h1>Studio Grafico & Locandine Eventi</h1>
        <p>Genera layout grafici coerenti e pronti per la stampa o la condivisione social (A4, A5, Instagram Story, Post Quadrato) a partire dai dati convalidati dell'evento.</p>
    </div>
</section>

<div class="course-list" style="margin-top: 2rem;">
    <?php if (empty($events)): ?>
        <p style="opacity: 0.7; font-style: italic;">Nessun evento in programma al momento.</p>
    <?php else: ?>
        <?php foreach ($events as $e): ?>
        <article class="course" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <span class="course-cat"><?= h($e['type'] ?? 'EVENTO') ?></span>
                <h3 style="margin-top: 0.5rem;"><?= h($e['title']) ?></h3>
                <p style="font-size: 0.9rem; opacity: 0.85; margin: 0.5rem 0;">
                    📅 <?= h($e['starts_at'] ?? '') ?><br>
                    📍 <?= h($e['venue'] ?? '') ?> (<?= h($e['comune'] ?? '') ?>)
                </p>
                <?php if (!empty($e['description'])): ?>
                    <p style="font-size: 0.85rem; opacity: 0.75; line-height: 1.4;"><?= h(mb_substr($e['description'], 0, 130)) ?>...</p>
                <?php endif; ?>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 0.6rem; font-weight: 600;">Scegli il formato di stampa/social:</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.4rem;">
                    <?php foreach (['A4', 'A5', 'STORY', 'SQUARE'] as $fmt): ?>
                        <a class="btn small" target="_blank" href="event-visual.php?event=<?= urlencode($e['sic_id']) ?>&format=<?= $fmt ?>" style="text-align: center; padding: 0.4rem 0.2rem; font-size: 0.8rem;">
                            <?= $fmt ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="events.php" class="btn secondary">Calendario Eventi</a>
    <a href="event-admin.php" class="btn secondary">Gestione Presenze</a>
    <a href="club.php" class="btn secondary">Il Mio Club</a>
</div>

<?php require '_footer.php'; ?>