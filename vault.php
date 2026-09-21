<?php
/**
 * DEPENDEX.SOCIAL - DRX Community Vault & Transparency
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Auditable Reserves, Anti-Speculation, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

vault_reserve_sync();
$reserve = vault_pool_balance('RESERVE');
$community = vault_pool_balance('COMMUNITY');

$rules = db()->query('SELECT * FROM community_drop_rules WHERE active = 1 ORDER BY threshold_drx ASC')->fetchAll();
$next = null;
foreach ($rules as $r) {
    if ($reserve < (float)$r['threshold_drx']) {
        $next = $r;
        break;
    }
}

$pageTitle = 'DRX Community Vault · Trasparenza Contabile';
$metaDesc = 'Trasparenza contabile e riserve garantite: i reward DRX non reclamati sono vincolati e non riutilizzabili.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Community Economy · Trasparenza</span>
        <h1>DRX Community Vault</h1>
        <p>I reward non reclamati restano una riserva vincolata dovuta agli utenti: non possono essere spesi due volte. Il fondo comunitario finanzia i traguardi collettivi della rete territoriale.</p>
    </div>
</section>

<section class="metric-grid">
    <div class="metric">
        <b><?= number_format($reserve, 0, ',', '.') ?></b>
        <span>DRX Riserva Vincolata (Liability Utenti)</span>
    </div>
    <div class="metric">
        <b><?= number_format($community, 0, ',', '.') ?></b>
        <span>DRX Community Pool Disponibile</span>
    </div>
    <?php if ($next): ?>
    <div class="metric">
        <b><?= number_format((int)$next['threshold_drx'], 0, ',', '.') ?></b>
        <span>Prossima Soglia Drop Comunitario</span>
    </div>
    <div class="metric">
        <b><?= number_format(max(0, (float)$next['threshold_drx'] - $reserve), 0, ',', '.') ?></b>
        <span>DRX Rimanenti alla Soglia</span>
    </div>
    <?php endif; ?>
</section>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
    <section class="card">
        <h2>Soglie e Regole di Erogazione</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Traguardi comunitari programmati al raggiungimento di volumi di partecipazione territoriale:
        </p>
        
        <?php if (empty($rules)): ?>
            <p style="opacity: 0.7; font-style: italic;">Nessuna soglia attiva configurata.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <?php foreach ($rules as $r): ?>
                <div class="leader-row" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="color: #60a5fa; font-size: 1.1rem;">◆</span>
                        <div>
                            <div style="font-weight: 600; font-size: 0.95rem;"><?= h($r['label']) ?></div>
                            <div style="font-size: 0.8rem; opacity: 0.75;">Soglia di attivazione</div>
                        </div>
                    </div>
                    <em style="font-style: normal; font-weight: 700; color: #4ade80;">
                        <?= number_format((int)$r['threshold_drx'], 0, ',', '.') ?> DRX
                    </em>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Garanzie Contabili Fondamentali</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem; line-height: 1.6;">
            <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 8px; border-left: 3px solid #60a5fa;">
                <strong>Separazione Assoluta (RESERVE ≠ COMMUNITY):</strong>
                <p style="margin: 0.4rem 0 0 0; opacity: 0.85;">Un DRX ancora dovuto come ricompensa a un partecipante non può finanziare contemporaneamente il premio o il badge di un altro membro.</p>
            </div>
            
            <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 8px; border-left: 3px solid #4ade80;">
                <strong>Nessun Acquisto di Status:</strong>
                <p style="margin: 0.4rem 0 0 0; opacity: 0.85;">I punti DRX premiano solo costanza, apprendimento nei corsi Academy e volontariato documentato. Non possono essere comprati con denaro.</p>
            </div>

            <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 8px; border-left: 3px solid #fbbf24;">
                <strong>Audit Immutabile:</strong>
                <p style="margin: 0.4rem 0 0 0; opacity: 0.85;">Ogni transazione genera un identificativo SIC-ID con data certa, garantendo trasparenza verso i Club, le famiglie e gli enti partner.</p>
            </div>
        </div>
    </section>
</div>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="finance.php" class="btn secondary">Tesoreria</a>
    <a href="dao.php" class="btn secondary">DAO Governance</a>
    <?php if (has_role($u['sic_id'], 'ADMIN') || has_role($u['sic_id'], 'SUPERADMIN')): ?>
        <a href="vault-admin.php" class="btn secondary">Pannello Vault Admin</a>
    <?php endif; ?>
</div>

<?php require '_footer.php'; ?>