<?php
/**
 * DEPENDEX.SOCIAL - DRX Community Vault Administration
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Audit-Ready, No Blockchain Illusion, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_admin();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $amount = max(0, (float)($_POST['amount'] ?? 0));
        if ($amount <= 0) {
            throw new InvalidArgumentException('Specificare un importo DRX valido maggiore di zero.');
        }
        vault_community_allocate($amount, 'DAO_TREASURY_ALLOCATION', $u['sic_id']);
        $msg = "Allocazione di {$amount} DRX al Community Pool registrata con successo nel registro di audit.";
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

vault_reserve_sync();
$reserve = vault_pool_balance('RESERVE');
$community = vault_pool_balance('COMMUNITY');

$pageTitle = 'Amministrazione Vault DRX · Community Economy';
$metaDesc = 'Gestione contabile interna della riserva DRX e del pool comunitario.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">DRX Vault Control · Amministrazione</span>
        <h1>Amministrazione DRX Vault</h1>
        <p>Sistema di contabilità interna audit-ready. Nessuna speculazione finanziaria: i punti DRX attestano attività e partecipazione comunitaria.</p>
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
        <b><?= number_format($reserve, 0, ',', '.') ?></b>
        <span>DRX Riserva Passiva (Liability Utenti)</span>
    </div>
    <div class="metric">
        <b><?= number_format($community, 0, ',', '.') ?></b>
        <span>DRX Pool Comunitario Attivo</span>
    </div>
</section>

<section class="card" style="margin-top: 1.5rem;">
    <h2>Allocazione da Tesoreria al Community Pool</h2>
    <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
        Trasferisci DRX dal fondo generale per rifornire le erogazioni dei Community Drop e i riconoscimenti per i Club territoriali.
    </p>
    
    <form method="post" class="stack" style="max-width: 480px;">
        <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
        
        <label>
            <span>Quantità DRX da allocare:</span>
            <input type="number" step="1" min="1" name="amount" placeholder="Es. 500" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
        </label>
        
        <button type="submit" class="btn primary" style="margin-top: 0.5rem;">Registra Allocazione</button>
    </form>
</section>

<section class="card" style="margin-top: 1.5rem;">
    <h3>Principio di Separazione Contabile</h3>
    <p style="font-size: 0.9rem; line-height: 1.6; opacity: 0.9;">
        <strong>RESERVE ≠ COMMUNITY:</strong> I reward non ancora riscossi rimangono vincolati a favore dell'utente titolare. Il sistema impedisce matematicamente la doppia spesa, garantendo che ogni punto promesso sia coperto senza inflazione artificiosa.
    </p>
</section>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="vault.php" class="btn secondary">Visualizzazione Vault Pubblico</a>
    <a href="finance.php" class="btn secondary">Tesoreria</a>
    <a href="dao.php" class="btn secondary">DAO Governance</a>
</div>

<?php require '_footer.php'; ?>