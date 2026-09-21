<?php
/**
 * DEPENDEX.SOCIAL - DAO Federated Governance & Voting
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * One-Member-One-Vote, Democratic Community Deliberation, Mobile-First
 */
require_once 'bootstrap.php';
$u = require_login();

$myClub = user_club_sic($u['sic_id']);
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['create_proposal'] ?? '') === '1') {
    try {
        csrf_check();
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $scopeChoice = $_POST['scope'] ?? 'CLUB';
        $scope = ($scopeChoice === 'GLOBAL' && dao_can_create_global($u['sic_id'])) ? null : $myClub;

        if (!$scope && $scopeChoice !== 'GLOBAL' && !dao_can_create_global($u['sic_id'])) {
            throw new RuntimeException('È richiesta una membership attiva di un Club territoriale per proporre una votazione.');
        }

        if ($title === '') {
            throw new InvalidArgumentException('Specificare un titolo per la proposta di delibera.');
        }

        $sid = sic_id();
        db()->prepare(
            'INSERT INTO dao_proposals(sic_id, scope_sic_id, title, body, proposal_type, voting_method, status, created_by_sic_id, opens_at, closes_at) VALUES(?,?,?,?,?,? ,"VOTING",?,CURRENT_TIMESTAMP,datetime("now","+7 days"))'
        )->execute([
            $sid,
            $scope,
            $title,
            $body,
            $_POST['proposal_type'] ?? 'GENERAL',
            'ONE_MEMBER_ONE_VOTE',
            $u['sic_id']
        ]);

        audit($u['sic_id'], 'DAO_PROPOSAL_CREATE', $sid, ['scope' => $scope]);
        $msg = 'Proposta pubblicata con successo. La votazione rimarrà aperta per 7 giorni.';
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

$all = db()->query(
    "SELECT p.*,
            SUM(CASE WHEN v.choice = 'YES' THEN v.weight ELSE 0 END) as yes_votes,
            SUM(CASE WHEN v.choice = 'NO' THEN v.weight ELSE 0 END) as no_votes,
            SUM(CASE WHEN v.choice = 'ABSTAIN' THEN v.weight ELSE 0 END) as abstain_votes,
            (SELECT choice FROM dao_proposal_votes WHERE proposal_sic_id = p.sic_id AND user_sic_id = " . db()->quote($u['sic_id']) . " LIMIT 1) as my_vote
     FROM dao_proposals p 
     LEFT JOIN dao_proposal_votes v ON v.proposal_sic_id = p.sic_id 
     GROUP BY p.sic_id 
     ORDER BY p.created_at DESC"
)->fetchAll();

$proposals = array_values(array_filter($all, fn($p) => dao_scope_access($u['sic_id'], $p['scope_sic_id'] ?? null)));

$pageTitle = 'DAO Forum · Governance Territoriale Federata';
$metaDesc = 'Partecipa alle votazioni comunitarie del tuo Club o della federazione con voto paritario One-Member-One-Vote.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Governance Federata · Democrazia Partecipativa</span>
        <h1>DAO Forum & Delibere Comunitarie</h1>
        <p>Uno spazio deliberativo paritario (One-Member-One-Vote). Vengono mostrati solo gli ambiti di voto associati al tuo Club o aperti a livello globale.</p>
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

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
    <section class="card">
        <h2>Nuova Proposta di Votazione</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
            Presenta una mozione o iniziativa al tuo Club o alla rete federata.
        </p>
        
        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="create_proposal" value="1">
            
            <label>
                <span>Titolo Proposta:</span>
                <input name="title" placeholder="Es. Organizzazione assemblea aperta o acquisto materiali" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label>
                <span>Descrizione & Motivazioni:</span>
                <textarea name="body" rows="4" placeholder="Spiega chiaramente l'obiettivo, i benefici per la comunità e i passi previsti..." required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;"></textarea>
            </label>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <label>
                    <span>Categoria:</span>
                    <select name="proposal_type" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20,25,35,0.9); color: inherit;">
                        <option value="GENERAL">Generale</option>
                        <option value="CLUB">Attività di Club</option>
                        <option value="EVENT">Evento / Assemblea</option>
                        <option value="SOCIAL_PROJECT">Progetto Sociale</option>
                        <option value="TRAINING">Formazione & Scuole</option>
                    </select>
                </label>
                
                <label>
                    <span>Ambito Territoriale:</span>
                    <select name="scope" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(20,25,35,0.9); color: inherit;">
                        <option value="CLUB">Il mio Club Territoriale</option>
                        <?php if (dao_can_create_global($u['sic_id'])): ?>
                            <option value="GLOBAL">Rete Globale Federata</option>
                        <?php endif; ?>
                    </select>
                </label>
            </div>
            
            <button type="submit" class="btn primary" style="margin-top: 0.5rem;">Pubblica Proposta</button>
        </form>
    </section>

    <section class="card">
        <h2>Proposte & Votazioni Attive</h2>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1.2rem;">
            Consulta le delibere in corso ed esprimi il tuo parere:
        </p>
        
        <?php if (empty($proposals)): ?>
            <p style="opacity: 0.7; font-style: italic; padding: 1.5rem 0; text-align: center;">Nessuna proposta aperta per il tuo ambito di partecipazione.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                <?php foreach ($proposals as $p): ?>
                <article style="border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 1.2rem; background: rgba(255,255,255,0.02);">
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                        <span class="pill" style="background: rgba(96,165,250,0.15); color: #60a5fa; font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 12px;">
                            <?= h($p['scope_sic_id'] ? 'CLUB / AMBITO LOCALE' : 'FEDERAZIONE GLOBALE') ?>
                        </span>
                        <span class="pill" style="background: rgba(34,197,94,0.15); color: #4ade80; font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 12px;">
                            <?= h($p['status'] ?? 'VOTING') ?>
                        </span>
                        <span class="pill vote" style="background: rgba(255,255,255,0.1); font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 12px;">
                            <?= h($p['voting_method'] ?? 'ONE_MEMBER_ONE_VOTE') ?>
                        </span>
                    </div>

                    <h3 style="margin: 0.4rem 0; font-size: 1.1rem;"><?= h($p['title']) ?></h3>
                    <p style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 1rem; line-height: 1.5;"><?= nl2br(h($p['body'])) ?></p>

                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-top: 0.8rem; border-top: 1px solid rgba(255,255,255,0.08);">
                        <div style="font-size: 0.9rem; display: flex; gap: 0.8rem;">
                            <span style="color: #4ade80;">👍 <?= (int)($p['yes_votes'] ?? 0) ?> Favorevoli</span>
                            <span style="color: #f87171;">👎 <?= (int)($p['no_votes'] ?? 0) ?> Contrari</span>
                            <span style="color: #9ca3af;">◯ <?= (int)($p['abstain_votes'] ?? 0) ?> Astenuti</span>
                        </div>

                        <?php if ($p['my_vote']): ?>
                            <span style="font-size: 0.85rem; color: #a78bfa; font-weight: 600;">Hai votato: <?= h($p['my_vote']) ?></span>
                        <?php else: ?>
                            <form method="post" action="action.php" style="display: flex; gap: 0.4rem;">
                                <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="dao_vote">
                                <input type="hidden" name="proposal_sic_id" value="<?= h($p['sic_id']) ?>">
                                <input type="hidden" name="return" value="dao.php">
                                <button name="choice" value="YES" class="btn small primary" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Sì</button>
                                <button name="choice" value="NO" class="btn small" style="padding: 0.3rem 0.6rem; font-size: 0.85rem; background: rgba(239,68,68,0.2); color: #f87171;">No</button>
                                <button name="choice" value="ABSTAIN" class="btn small secondary" style="padding: 0.3rem 0.6rem; font-size: 0.85rem;">Astensione</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="club.php" class="btn secondary">Il Mio Club</a>
    <a href="vault.php" class="btn secondary">Community Vault</a>
    <a href="finance.php" class="btn secondary">Tesoreria</a>
</div>

<?php require '_footer.php'; ?>