<?php
/**
 * DEPENDEX.SOCIAL - User Profile & Account Overview
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * SIC-ID Identity, Privacy by Design, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$roles = user_roles($u['sic_id']);
$clubSic = user_club_sic($u['sic_id']);
$clubName = '';
if ($clubSic) {
    $cst = db()->prepare('SELECT den_struttura, comune FROM cat_clubs_italy WHERE sic_id = ?');
    $cst->execute([$clubSic]);
    $crow = $cst->fetch();
    if ($crow) {
        $clubName = $crow['den_struttura'] . ' (' . $crow['comune'] . ')';
    }
}

$balance = drx_balance($u['sic_id']);

$pageTitle = 'Profilo Personale · Dependex';
$metaDesc = 'Gestione profilo utente, credenziali crittografiche SIC-ID, ruoli di rete e preferenze di riservatezza.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Identità Territoriale · SIC-ID</span>
        <h1>Il Mio Profilo</h1>
        <p>I tuoi dati sono protetti e crittografati. La tua identità è collegata al tuo codice SIC-ID univoco.</p>
    </div>
</section>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
    <section class="profile-card" style="border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 2rem; background: rgba(255,255,255,0.02); text-align: center;">
        <div class="profile-avatar" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #10b981); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #ffffff; margin: 0 auto 1rem auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
            <?= h(mb_substr($u['display_name'] ?? 'U', 0, 1)) ?>
        </div>
        
        <h2 style="margin: 0.5rem 0 0.25rem 0; font-size: 1.4rem;"><?= h($u['display_name']) ?></h2>
        <div style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.5rem;"><?= h($u['email']) ?></div>
        <div style="font-family: monospace; font-size: 0.8rem; background: rgba(0,0,0,0.25); padding: 0.3rem 0.6rem; border-radius: 4px; display: inline-block; margin-bottom: 1rem; color: #93c5fd;">
            <?= h($u['sic_id']) ?>
        </div>

        <div class="tag-row" style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <?php foreach ($roles as $r): ?>
                <span class="pill" style="background: rgba(96,165,250,0.15); color: #60a5fa; font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 12px; font-weight: 600;">
                    <?= h($r['role_code']) ?>
                </span>
            <?php endforeach; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; text-align: center;">
            <div>
                <b style="display: block; font-size: 1.3rem; color: #4ade80;"><?= number_format($balance, 0, ',', '.') ?></b>
                <span style="font-size: 0.8rem; opacity: 0.75;">Saldo DRX</span>
            </div>
            <div>
                <b style="display: block; font-size: 1.1rem; color: #ffffff;"><?= h($clubSic ? 'Attivo' : 'Nessuno') ?></b>
                <span style="font-size: 0.8rem; opacity: 0.75;">Club Territoriale</span>
            </div>
        </div>

        <?php if ($clubName): ?>
            <div style="margin-top: 1rem; font-size: 0.85rem; color: #60a5fa; text-align: center;">
                Appartieni a: <strong><?= h($clubName) ?></strong>
            </div>
        <?php endif; ?>
    </section>

    <section class="menu-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
        <a href="wallet.php" class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; text-decoration: none; color: inherit; transition: background 0.2s;">
            <span style="display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <?= dx_icon('wallet', '', 20) ?> Portafoglio DRX & Ricompense
            </span>
            <b style="opacity: 0.6;">&rsaquo;</b>
        </a>

        <a href="sobriety.php" class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; text-decoration: none; color: inherit; transition: background 0.2s;">
            <span style="display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <?= dx_icon('activity', '', 20) ?> Il Mio Cammino (Un Giorno alla Volta)
            </span>
            <b style="opacity: 0.6;">&rsaquo;</b>
        </a>

        <a href="journal.php" class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; text-decoration: none; color: inherit; transition: background 0.2s;">
            <span style="display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <?= dx_icon('edit', '', 20) ?> Diario Personale di Bordo
            </span>
            <b style="opacity: 0.6;">&rsaquo;</b>
        </a>

        <a href="leaderboard.php" class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; text-decoration: none; color: inherit; transition: background 0.2s;">
            <span style="display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <?= dx_icon('trophy', '', 20) ?> Comunità & Riconoscimenti
            </span>
            <b style="opacity: 0.6;">&rsaquo;</b>
        </a>

        <a href="documents.php" class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; text-decoration: none; color: inherit; transition: background 0.2s;">
            <span style="display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <?= dx_icon('award', '', 20) ?> Attestati & Documenti Firmati
            </span>
            <b style="opacity: 0.6;">&rsaquo;</b>
        </a>

        <a href="privacy-center.php" class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; text-decoration: none; color: inherit; transition: background 0.2s;">
            <span style="display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <?= dx_icon('lock', '', 20) ?> Privacy Center & Consensi GDPR
            </span>
            <b style="opacity: 0.6;">&rsaquo;</b>
        </a>

        <a href="logout.php" class="card" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; text-decoration: none; color: #f87171; transition: background 0.2s;">
            <span style="display: inline-flex; align-items: center; gap: 12px; font-weight: 600;">
                <?= dx_icon('log-out', '', 20) ?> Disconnetti Account
            </span>
            <b style="opacity: 0.6;">&rsaquo;</b>
        </a>
    </section>
</div>

<?php require '_footer.php'; ?>