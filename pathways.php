<?php
/**
 * DEPENDEX.SOCIAL - Maieutic Pathways & Life Portals
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Trauma-Informed, Zero Stigma, Maieutic Choice, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$areas = db()->query("SELECT * FROM addiction_areas WHERE status = 'ACTIVE' ORDER BY id ASC")->fetchAll();

$pageTitle = 'Aree di Vita & Percorsi · Dependex';
$metaDesc = 'Da dove vuoi partire? Scegli liberamente l\'area della tua vita a cui desideri dedicare attenzione.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Pathway Engine · Libertà di Scelta</span>
        <h1>Da dove vuoi partire oggi?</h1>
        <p>Ogni persona vive situazioni uniche e può incontrare fatiche in aree diverse della propria vita. <strong>Nessuna etichetta pubblica</strong> viene applicata al tuo profilo: sei tu a scegliere liberamente da dove cominciare.</p>
    </div>
</section>

<div class="card" style="margin-bottom: 2rem; border-left: 4px solid #60a5fa; background: rgba(96,165,250,0.06);">
    <p style="font-size: 1.05rem; line-height: 1.6; margin: 0;">
        <em>"Questa parte della mia vita oggi chiede attenzione."</em><br>
        <strong>Partiamo da lì.</strong> Vediamo insieme cosa può aiutarti a rimettere in movimento le tue energie vitali e quali persone, famiglie e Club territoriali possono accompagnarti con discrezione.
    </p>
</div>

<section class="bubble-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
    <?php foreach ($areas as $a): ?>
    <a class="bubble green" href="profile-engine.php?area=<?= urlencode($a['code']) ?>" style="display: flex; align-items: center; gap: 0.75rem; padding: 1.2rem; border-radius: 12px; text-decoration: none; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.03); transition: all 0.2s;">
        <span style="display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 50%; background: rgba(34,197,94,0.15); color: #4ade80;">
            <?= dx_icon('sparkles', '', 22) ?>
        </span>
        <div>
            <b style="display: block; font-size: 1rem; color: #ffffff;"><?= h($a['label']) ?></b>
            <span style="font-size: 0.75rem; opacity: 0.75; text-transform: uppercase;"><?= h($a['category']) ?></span>
        </div>
    </a>
    <?php endforeach; ?>
</section>

<div style="margin-top: 2.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="lifestyle.php" class="btn secondary">Ruota della Vita</a>
    <a href="assessments.php" class="btn secondary">Strumenti di Autoconsapevolezza</a>
    <a href="club.php" class="btn secondary">Incontra il Club</a>
</div>

<?php require '_footer.php'; ?>