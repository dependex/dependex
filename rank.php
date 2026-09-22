<?php
/**
 * DEPENDEX.SOCIAL - DRX Rank Progression Road
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Non-Punitive, Merit-Based, Zero Punteggi Clinici (Divieto Punteggio Benessere), Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$ranks = db()->query('SELECT * FROM ranks ORDER BY rank_order ASC')->fetchAll();
$currentDrx = drx_balance($u['sic_id']);

$pageTitle = 'Progressione Rank DRX · Dependex';
$metaDesc = 'Il Rank DRX si conquista con la presenza, lo studio e il volontariato. Euro e donazioni non comprano status.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">DRX Merit Engine · Riconoscimenti</span>
        <h1>Il Rank si conquista sul campo.</h1>
        <p>I punti DRX premiano solo la costanza reale: presenza agli incontri del Club, completamento dei corsi Academy e ore di volontariato convalidate. <strong>Euro, quote e donazioni non comprano status o titoli comunitari.</strong></p>
    </div>
</section>

<section class="metric-grid">
    <div class="metric">
        <b><?= number_format($currentDrx, 0, ',', '.') ?> DRX</b>
        <span>Il Tuo Saldo Attuale</span>
    </div>
    <div class="metric">
        <b>9 Livelli</b>
        <span>Percorso Evolutivo Naturale</span>
    </div>
    <div class="metric">
        <b style="color: #4ade80;">100% Etico</b>
        <span>Nessun Rank a Pagamento</span>
    </div>
</section>

<div class="course-list" style="margin-top: 2rem;">
    <?php foreach ($ranks as $r): 
        $unlocks = json_decode($r['unlocks_json'] ?? '{}', true);
        $isUnlocked = $currentDrx >= (float)$r['threshold_drx'];
    ?>
    <article class="course" style="border-left: 4px solid <?= $isUnlocked ? '#4ade80' : 'rgba(255,255,255,0.2)' ?>; background: <?= $isUnlocked ? 'rgba(34,197,94,0.03)' : 'rgba(255,255,255,0.02)' ?>;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-weight: 800; font-size: 1.1rem; color: <?= $isUnlocked ? '#4ade80' : '#a1a1aa' ?>;">
                    #<?= $r['rank_order'] ?>
                </span>
                <h3 style="margin: 0; font-size: 1.25rem;"><?= h($r['name']) ?></h3>
            </div>
            
            <span style="font-weight: 700; font-size: 1.05rem; color: <?= $isUnlocked ? '#4ade80' : '#60a5fa' ?>;">
                <?= number_format((int)$r['threshold_drx'], 0, ',', '.') ?> DRX
            </span>
        </div>

        <p style="margin: 0.75rem 0; opacity: 0.85; font-size: 0.95rem;">
            <?= h($unlocks['description'] ?? 'Funzionalità avanzate ed eventi comunitari riservati.') ?>
        </p>

        <div style="font-size: 0.8rem; opacity: 0.7;">
            <?php if ($isUnlocked): ?>
                <span style="color: #4ade80; font-weight: 600;">✓ Livello Raggiunto</span>
            <?php else: ?>
                <span>Mancano <?= number_format(max(0, (float)$r['threshold_drx'] - $currentDrx), 0, ',', '.') ?> DRX</span>
            <?php endif; ?>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<div style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="wallet.php" class="btn secondary">Portafoglio DRX</a>
    <a href="missions.php" class="btn secondary">Missioni Attive</a>
    <a href="academy.php" class="btn secondary">Corsi Academy</a>
</div>

<?php require '_footer.php'; ?>