<?php
/**
 * DEPENDEX.SOCIAL - Social Impact Administration & Verification
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Auditable Volunteer Actions, Donation Confirmation, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_admin();

$msg = '';
$err = '';
$scope = user_club_sic($u['sic_id']);

if (!acl_can($u['sic_id'], 'project', 'READ', $scope, $u['sic_id']) && !has_role($u['sic_id'], 'SUPERADMIN')) {
    $pageTitle = 'Accesso Riservato · Social Impact Admin';
    require '_header.php';
    ?>
    <section class="section-head">
        <div>
            <span class="eyebrow">Social Impact</span>
            <h1>Accesso Non Autorizzato</h1>
            <p>La validazione delle azioni di volontariato e delle donazioni richiede permessi amministrativi territoriali.</p>
            <div style="margin-top: 1.5rem;">
                <a href="social-impact.php" class="btn primary">Vai a Social Impact</a>
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
        $act = $_POST['act'] ?? '';
        $sic = trim($_POST['sic'] ?? '');

        if ($act === 'verify_volunteer') {
            $st = db()->prepare('SELECT * FROM volunteer_actions WHERE sic_id = ?');
            $st->execute([$sic]);
            $v = $st->fetch();

            if ($v && !$v['verified']) {
                $reward = (int)round((float)$v['hours'] * 25);
                db()->prepare('UPDATE volunteer_actions SET verified = 1, drx_awarded = ? WHERE sic_id = ?')
                    ->execute([$reward, $sic]);
                drx_post($v['user_sic_id'], null, $reward, 'VOLUNTEERING', true, 'volunteer:' . $sic, $sic, ['hours' => $v['hours']]);
                $msg = "Attività di volontariato validata ({$v['hours']} ore). Assegnati {$reward} DRX al volontario.";
            } else {
                throw new RuntimeException('Azione di volontariato non trovata o già convalidata.');
            }
        } elseif ($act === 'confirm_donation') {
            $ref = trim($_POST['reference'] ?? '');
            if ($ref === '') {
                throw new InvalidArgumentException('Inserire il riferimento contabile del bonifico o della quietanza.');
            }
            db()->prepare("UPDATE donations SET status = 'CONFIRMED', payment_reference = ? WHERE sic_id = ?")
                ->execute([$ref, $sic]);
            $msg = 'Ricezione fondi confermata e registrata a bilancio di tesoreria.';
        }
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

$vol = db()->query(
    "SELECT v.*, u.display_name, p.title as project_title FROM volunteer_actions v LEFT JOIN users u ON u.sic_id = v.user_sic_id LEFT JOIN projects p ON p.sic_id = v.project_sic_id WHERE v.verified = 0 ORDER BY v.created_at ASC"
)->fetchAll();

$don = db()->query(
    "SELECT d.*, u.display_name, p.title as project_title FROM donations d LEFT JOIN users u ON u.sic_id = d.donor_user_sic_id LEFT JOIN projects p ON p.sic_id = d.project_sic_id WHERE d.status = 'PLEDGED' ORDER BY d.created_at ASC"
)->fetchAll();

$pageTitle = 'Amministrazione Social Impact · Dependex';
$metaDesc = 'Convalida delle ore di volontariato e conferme contabili di donazioni solidali.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Solidarity Engine · Convalida</span>
        <h1>Amministrazione Social Impact</h1>
        <p>Solo le azioni reali di volontariato convalidate generano attestazioni e DRX. Nessun euro donato acquista status o rank.</p>
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
        <b><?= count($vol) ?></b>
        <span>Attività Volontariato in Attesa</span>
    </div>
    <div class="metric">
        <b><?= count($don) ?></b>
        <span>Promesse Donazione da Confermare</span>
    </div>
</section>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
    <section class="card">
        <h2>Volontariato in Attesa di Verifica</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Verifica le ore sul campo e convalida per attestare l'azione sociale.
        </p>
        
        <?php if (empty($vol)): ?>
            <p style="opacity: 0.7; font-style: italic; padding: 1rem 0;">Nessuna attività in attesa di verifica.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($vol as $v): ?>
                <article style="border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 1rem; background: rgba(255,255,255,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <h4 style="margin: 0; font-size: 1.05rem;"><?= h($v['display_name'] ?: 'Membro della community') ?></h4>
                        <span style="font-weight: 700; color: #4ade80; background: rgba(34,197,94,0.15); padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                            <?= h((string)$v['hours']) ?> ore
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: #60a5fa; margin-bottom: 0.5rem;">Progetto: <?= h($v['project_title'] ?: 'Iniziativa') ?></div>
                    <p style="font-size: 0.9rem; opacity: 0.9; margin: 0.5rem 0;"><?= h($v['description']) ?></p>
                    <form method="post" style="margin-top: 0.75rem;">
                        <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="act" value="verify_volunteer">
                        <input type="hidden" name="sic" value="<?= h($v['sic_id']) ?>">
                        <button type="submit" class="btn primary small">Convalida & Assegna DRX</button>
                    </form>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Donazioni da Confermare</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Inserisci il codice di quietanza bonifico o cassa prima di registrare l'entrata effettiva.
        </p>
        
        <?php if (empty($don)): ?>
            <p style="opacity: 0.7; font-style: italic; padding: 1rem 0;">Nessun impegno di donazione in attesa di riscontro bancario.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($don as $d): ?>
                <article style="border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 1rem; background: rgba(255,255,255,0.03);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <h4 style="margin: 0; font-size: 1.05rem;"><?= h($d['anonymous'] ? 'Donatore Anonimo' : ($d['display_name'] ?: 'Donatore')) ?></h4>
                        <span style="font-weight: 700; color: #4ade80; font-size: 1.1rem;">
                            € <?= number_format((float)$d['amount_eur'], 2, ',', '.') ?>
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: #60a5fa; margin-bottom: 0.75rem;">Destinazione: <?= h($d['project_title'] ?: 'Fondo Solidale') ?></div>
                    <form method="post" class="stack">
                        <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="act" value="confirm_donation">
                        <input type="hidden" name="sic" value="<?= h($d['sic_id']) ?>">
                        <input name="reference" placeholder="CRO / Riferimento bonifico o ricevuta" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit; font-size: 0.85rem;">
                        <button type="submit" class="btn small" style="margin-top: 0.4rem;">Conferma Ricezione</button>
                    </form>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="social-impact.php" class="btn secondary">Schede Progetti</a>
    <a href="finance.php" class="btn secondary">Tesoreria Generale</a>
    <a href="club-admin.php" class="btn secondary">Club Admin</a>
</div>

<?php require '_footer.php'; ?>