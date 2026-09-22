<?php
/**
 * DEPENDEX.SOCIAL - Human Profile & Self-Awareness Map
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Maieutic Self-Reflection, Zero Punteggi Clinici (Divieto Punteggio Benessere), Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$selectedArea = trim($_GET['area'] ?? '');
$areaLabel = '';
if ($selectedArea !== '') {
    $ast = db()->prepare('SELECT label FROM addiction_areas WHERE code = ? LIMIT 1');
    $ast->execute([$selectedArea]);
    $areaLabel = $ast->fetchColumn() ?: '';
}

$pageTitle = 'Mappa di Autoconsapevolezza · Human Profile';
$metaDesc = 'Questionari, check-in e dati dichiarati sono tenuti separati dalle diagnosi cliniche e usati per l\'orientamento personale.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Human Welfare Engine · Autoconsapevolezza</span>
        <h1>La Mia Mappa Personale</h1>
        <p>Uno strumento maieutico per orientarti con gentilezza. <strong>Nessun punteggio numerico di benessere</strong>, nessuna etichetta o diagnosi clinica: solo una fotografia del tuo sentire per decidere liberamente i tuoi prossimi passi.</p>
    </div>
</section>

<?php if ($areaLabel): ?>
<div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid #4ade80; background: rgba(34,197,94,0.06);">
    <h3 style="color: #4ade80; margin-top: 0;">Area Selezionata: <?= h($areaLabel) ?></h3>
    <p style="font-size: 0.95rem; margin-bottom: 0;">
        Hai indicato che quest'area oggi richiede attenzione. <em>"Partiamo da lì. Vediamo cosa può aiutarti a rimettere in movimento le tue energie e quali persone e comunità possono accompagnarti."</em>
    </p>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
    <section class="card">
        <h2>Come ti senti oggi?</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
            Seleziona la sensazione che più si avvicina al tuo momento presente:
        </p>

        <div class="mood-row" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem; text-align: center;">
            <a href="checkin.php?mood=ottimo" class="btn secondary" title="Sensazione di pienezza ed energia" style="padding: 0.75rem 0.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                <?= dx_icon('smile', '', 24) ?>
                <span style="font-size: 0.75rem;">Energico</span>
            </a>
            <a href="checkin.php?mood=bene" class="btn secondary" title="Sereno e tranquillo" style="padding: 0.75rem 0.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                <?= dx_icon('sun', '', 24) ?>
                <span style="font-size: 0.75rem;">Sereno</span>
            </a>
            <a href="checkin.php?mood=equilibrato" class="btn secondary" title="Equilibrato e costante" style="padding: 0.75rem 0.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                <?= dx_icon('activity', '', 24) ?>
                <span style="font-size: 0.75rem;">Stabile</span>
            </a>
            <a href="checkin.php?mood=affanno" class="btn secondary" title="In affanno o stanchezza" style="padding: 0.75rem 0.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                <?= dx_icon('alert-triangle', '', 24) ?>
                <span style="font-size: 0.75rem;">In salita</span>
            </a>
            <a href="checkin.php?mood=supporto" class="btn secondary" title="Desidero ascolto o supporto" style="padding: 0.75rem 0.25rem; display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                <?= dx_icon('shield', '', 24) ?>
                <span style="font-size: 0.75rem;">Bisogno</span>
            </a>
        </div>

        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="journal.php" class="btn primary small">Apri Diario di Bordo Dettagliato</a>
        </div>
    </section>

    <section class="card">
        <h2>Fotografia delle Aree di Vita (Ruota)</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
            Percezione soggettiva del livello di equilibrio per ciascuna dimensione:
        </p>

        <div class="wheel" style="display: flex; flex-direction: column; gap: 0.75rem;">
            <?php 
            $dimensions = [
                'Salute & Corporeità' => 7,
                'Sonno & Riposo' => 6,
                'Famiglia & Affetti' => 7,
                'Relazioni & Amicizie' => 6,
                'Lavoro & Realizzazione' => 5,
                'Tempo Libero & Natura' => 4,
                'Senso & Significato' => 6,
                'Comunità & Club' => 7
            ];
            foreach ($dimensions as $dim => $val): 
            ?>
            <div class="wheel-row" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                <span style="font-size: 0.9rem; min-width: 140px;"><?= h($dim) ?></span>
                <div class="bar" style="flex: 1; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
                    <i style="display: block; height: 100%; width: <?= $val * 10 ?>%; background: #60a5fa; border-radius: 4px;"></i>
                </div>
                <b style="font-size: 0.85rem; color: #93c5fd; min-width: 28px; text-align: right;"><?= $val ?>/10</b>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="lifestyle.php" class="btn secondary small">Aggiorna la mia Ruota della Vita</a>
        </div>
    </section>
</div>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="assessments.php" class="btn primary">Questionari di Autovalutazione</a>
    <a href="pathways.php" class="btn secondary">Tutte le Aree</a>
    <a href="club.php" class="btn secondary">Trova un Club</a>
</div>

<?php require '_footer.php'; ?>