<?php
/**
 * DEPENDEX.SOCIAL - Digital Treasury & Finance Management
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Full Audit-Trail, SIC-ID, Non-punitive, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_admin();

$msg = '';
$err = '';
$defaultScope = user_club_sic($u['sic_id']);

if ($defaultScope && !acl_can($u['sic_id'], 'finance', 'READ', $defaultScope, $u['sic_id']) && !has_role($u['sic_id'], 'SUPERADMIN')) {
    $pageTitle = 'Accesso Riservato · Tesoreria';
    require '_header.php';
    ?>
    <section class="section-head">
        <div>
            <span class="eyebrow">Finance OS</span>
            <h1>Accesso Non Autorizzato</h1>
            <p>La gestione della tesoreria richiede permessi di amministrazione o di tesoriere del Club.</p>
            <div style="margin-top: 1.5rem;">
                <a href="club.php" class="btn primary">Torna al Club</a>
            </div>
        </div>
    </section>
    <?php
    require '_footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $dir = $_POST['direction'] ?? 'IN';
        $amt = (float)($_POST['amount'] ?? 0);
        $cat = trim($_POST['category'] ?? 'OTHER');
        $desc = trim($_POST['description'] ?? '');
        $scope = trim($_POST['scope_sic_id'] ?? '') ?: ($defaultScope ?: '');

        if ($scope && !acl_can($u['sic_id'], 'finance', 'MANAGE', $scope, $u['sic_id']) && !has_role($u['sic_id'], 'SUPERADMIN')) {
            throw new RuntimeException('Non possiedi i permessi di scrittura contabile su questo ambito territoriale.');
        }

        if ($amt <= 0) {
            throw new InvalidArgumentException('Specificare un importo maggiore di zero.');
        }

        $acc = db()->prepare('SELECT sic_id FROM treasury_accounts WHERE owner_scope_sic_id = ? LIMIT 1');
        $acc->execute([$scope]);
        $account = $acc->fetchColumn();

        if (!$account) {
            $account = sic_id();
            db()->prepare(
                'INSERT INTO treasury_accounts(sic_id, owner_scope_sic_id, currency, account_type, label) VALUES(?,?,"EUR","OPERATING","Tesoreria Operativa")'
            )->execute([$account, $scope]);
        }

        db()->prepare(
            'INSERT INTO treasury_transactions(sic_id, account_sic_id, direction, amount, category, description, approved_by_sic_id) VALUES(?,?,?,?,?,?,?)'
        )->execute([sic_id(), $account, $dir, $amt, $cat, $desc, $u['sic_id']]);

        if ($dir === 'IN' && $cat === 'CLUB_MEMBERSHIP' && !empty($_POST['member_sic_id'])) {
            $memberSic = trim($_POST['member_sic_id']);
            $rate = (float)drx_setting('membership_eur_to_drx', 1);
            drx_post($memberSic, null, $amt * $rate, 'CLUB_MEMBERSHIP', false, 'membership:' . $memberSic . ':' . date('Ym') . ':' . $amt, null, ['eur' => $amt]);
        }

        $msg = 'Movimento contabile registrato con successo con hash di audit.';
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

$bal = (float)db()->query(
    "SELECT COALESCE(SUM(CASE WHEN direction='IN' THEN amount ELSE -amount END), 0) FROM treasury_transactions"
)->fetchColumn();

$moves = db()->query(
    'SELECT tt.*, ta.label as account_label FROM treasury_transactions tt JOIN treasury_accounts ta ON ta.sic_id = tt.account_sic_id ORDER BY tt.created_at DESC LIMIT 50'
)->fetchAll();

$pageTitle = 'Tesoreria Digitale · Finance OS';
$metaDesc = 'Gestione trasparente entrate, quote associative, spese e rimborsi con tracciabilità SIC-ID.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Finance OS · Amministrazione</span>
        <h1>Tesoreria Digitale Federata</h1>
        <p>Rendicontazione trasparente di entrate, quote di partecipazione, spese documentate e rimborsi con SIC-ID e registro audit.</p>
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
        <b>€ <?= number_format($bal, 2, ',', '.') ?></b>
        <span>Saldo di Cassa Registrato</span>
    </div>
    <div class="metric">
        <b><?= count($moves) ?></b>
        <span>Movimenti Recenti</span>
    </div>
    <div class="metric">
        <b style="font-size: 1.1rem;"><?= h($defaultScope ?: 'Globale') ?></b>
        <span>Ambito di Riferimento</span>
    </div>
</section>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
    <section class="card">
        <h2>Registra Movimento di Cassa</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
            Inserisci entrate (quote, donazioni) o uscite (spese vive per sedi, materiali, rimborsi).
        </p>
        
        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            
            <label>
                <span>Ambito Territoriale (Scope SIC-ID):</span>
                <input name="scope_sic_id" value="<?= h($defaultScope) ?>" placeholder="Es. SIC-CLUB-..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <label>
                    <span>Tipo Flusso:</span>
                    <select name="direction" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20,25,35,0.9); color: inherit;">
                        <option value="IN">Entrata (+)</option>
                        <option value="OUT">Uscita (-)</option>
                    </select>
                </label>
                
                <label>
                    <span>Importo (€):</span>
                    <input type="number" step="0.01" min="0.01" name="amount" required placeholder="0.00" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
                </label>
            </div>
            
            <label>
                <span>Categoria Contabile:</span>
                <select name="category" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20,25,35,0.9); color: inherit;">
                    <option value="CLUB_MEMBERSHIP">Quota di Partecipazione Club (CLUB_MEMBERSHIP)</option>
                    <option value="DONATION">Erogazione Liberale / Donazione (DONATION)</option>
                    <option value="EVENT">Evento / Assemblea Territoriale (EVENT)</option>
                    <option value="GRANT">Contributo Pubblico / Bando (GRANT)</option>
                    <option value="MATERIALS">Materiali Didattici / Cancelleria (MATERIALS)</option>
                    <option value="TRAVEL">Rimborsi Viaggio Documentati (TRAVEL)</option>
                    <option value="REFUND">Rimborso Spese (REFUND)</option>
                    <option value="OTHER">Altro Movimento Giustificato (OTHER)</option>
                </select>
            </label>
            
            <label>
                <span>SIC-ID Membro (necessario solo per quote Club):</span>
                <input name="member_sic_id" placeholder="Es. SIC-USER-..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label>
                <span>Causale / Descrizione Movimento:</span>
                <input name="description" placeholder="Descrizione del movimento contabile..." required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <button type="submit" class="btn primary" style="margin-top: 0.5rem;">Registra Movimento</button>
        </form>
    </section>

    <section class="card">
        <h2>Registro Movimenti</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Ultime 50 registrazioni contabili validate nel circuito.
        </p>
        
        <div style="max-height: 520px; overflow-y: auto; padding-right: 0.5rem;">
            <?php if (empty($moves)): ?>
                <p style="opacity: 0.7; font-style: italic;">Nessun movimento registrato finora.</p>
            <?php else: ?>
                <?php foreach ($moves as $m): ?>
                <div class="leader-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; background: <?= $m['direction'] === 'IN' ? 'rgba(34,197,94,0.2)' : 'rgba(239,68,68,0.2)' ?>; color: <?= $m['direction'] === 'IN' ? '#4ade80' : '#f87171' ?>;">
                            <?= h($m['direction']) ?>
                        </span>
                        <div>
                            <div style="font-weight: 600; font-size: 0.95rem;"><?= h($m['category']) ?></div>
                            <div style="font-size: 0.8rem; opacity: 0.75;"><?= h($m['description']) ?> · <small><?= h(substr($m['created_at'] ?? '', 0, 16)) ?></small></div>
                        </div>
                    </div>
                    <em style="font-style: normal; font-weight: 700; font-size: 1rem; color: <?= $m['direction'] === 'IN' ? '#4ade80' : '#f87171' ?>;">
                        <?= $m['direction'] === 'IN' ? '+' : '-' ?> € <?= number_format((float)$m['amount'], 2, ',', '.') ?>
                    </em>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="club-admin.php" class="btn secondary">Amministrazione Club</a>
    <a href="social-impact.php" class="btn secondary">Social Impact</a>
    <a href="vault.php" class="btn secondary">Community Vault</a>
</div>

<?php require '_footer.php'; ?>