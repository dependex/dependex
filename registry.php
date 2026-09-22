<?php
/**
 * DEPENDEX.SOCIAL - Global Registry (Anagrafe Utenti, Famiglie e Professionisti)
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Full Separation, SIC-ID, Zero Punteggi Clinici (Divieto Punteggio Benessere), Mobile-First
 */
require_once 'bootstrap.php';
$u = require_admin();

$msg = '';
$err = '';
$scope = user_club_sic($u['sic_id']);

if (!acl_can($u['sic_id'], 'registry', 'READ', $scope, $u['sic_id']) && !has_role($u['sic_id'], 'SUPERADMIN')) {
    $pageTitle = 'Accesso Riservato · Anagrafe';
    require '_header.php';
    ?>
    <section class="section-head">
        <div>
            <span class="eyebrow">Global Registry</span>
            <h1>Accesso Non Autorizzato</h1>
            <p>La gestione dell'anagrafe territoriale richiede permessi amministrativi di coordinatore o segreteria.</p>
            <div style="margin-top: 1.5rem;">
                <a href="club.php" class="btn primary">Torna al Club</a>
            </div>
        </div>
    </section>
    <?php
    require '_footer.php';
    exit;
}

$recovery = '';
$newCredentials = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();
        $kind = $_POST['kind'] ?? '';

        if ($kind === 'USER') {
            $email = trim($_POST['email'] ?? '');
            $name = trim($_POST['name'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
                throw new InvalidArgumentException('Specificare un indirizzo email valido e un nominativo non vuoto.');
            }

            $pwd = bin2hex(random_bytes(6));
            $recovery = create_recovery_code();
            $sid = sic_id();

            db()->prepare(
                'INSERT INTO users(sic_id, email, display_name, password_hash, recovery_code_hash, recovery_code_changed_at) VALUES(?,?,?,?,?,CURRENT_TIMESTAMP)'
            )->execute([
                $sid,
                $email,
                $name,
                password_hash($pwd, PASSWORD_DEFAULT),
                password_hash($recovery, PASSWORD_DEFAULT)
            ]);

            db()->prepare("INSERT INTO user_roles(user_sic_id, role_code, status) VALUES(?, 'USER', 'ACTIVE')")->execute([$sid]);
            audit($u['sic_id'], 'CREATE_USER', $sid);

            $newCredentials = [
                'name' => $name,
                'email' => $email,
                'sic_id' => $sid,
                'pwd' => $pwd,
                'recovery' => $recovery
            ];
            $msg = 'Nuovo utente registrato con successo.';
        } elseif ($kind === 'FAMILY') {
            $name = trim($_POST['name'] ?? '');
            $clubSic = trim($_POST['club_sic_id'] ?? '') ?: null;

            if ($name === '') {
                throw new InvalidArgumentException('Specificare la denominazione del nucleo familiare.');
            }

            $sid = sic_id();
            db()->prepare(
                'INSERT INTO families(sic_id, name, club_sic_id) VALUES(?,?,?)'
            )->execute([$sid, $name, $clubSic]);

            audit($u['sic_id'], 'CREATE_FAMILY', $sid);
            $msg = "Nucleo familiare registrato con SIC-ID: {$sid}";
        } elseif ($kind === 'PROFESSIONAL') {
            $prof = trim($_POST['profession'] ?? '');
            $org = trim($_POST['organization'] ?? '');
            $territory = trim($_POST['territory'] ?? '');

            if ($prof === '') {
                throw new InvalidArgumentException('Specificare la qualifica professionale.');
            }

            $sid = sic_id();
            db()->prepare(
                'INSERT INTO professionals(sic_id, profession, organization, territory, verification_status) VALUES(?,?,?,?,?)'
            )->execute([$sid, $prof, $org, $territory, 'UNVERIFIED']);

            audit($u['sic_id'], 'CREATE_PROFESSIONAL', $sid);
            $msg = "Professionista registrato con status UNVERIFIED (SIC-ID: {$sid}). Richiede verifica accreditamento.";
        }
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

$counts = [
    'Utenti Registrati' => (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'Famiglie' => (int)db()->query('SELECT COUNT(*) FROM families')->fetchColumn(),
    'Professionisti' => (int)db()->query('SELECT COUNT(*) FROM professionals')->fetchColumn(),
    'Club Locali' => (int)db()->query("SELECT COUNT(*) FROM dependex_world_registry WHERE network_level = 'LOCAL_CLUB'")->fetchColumn()
];

$pageTitle = 'Anagrafe Generale · Global Registry';
$metaDesc = 'Anagrafe federata di utenti, famiglie, Club e professionisti con identità crittografica SIC-ID.';
require '_header.php';
?>

<section class="section-head">
    <div>
        <span class="eyebrow">Global Registry · Amministrazione</span>
        <h1>Anagrafe Territoriale Federata</h1>
        <p>Gestione separata di utenti, nuclei familiari, Club locali e professionisti di rete con tracciabilità crittografica SIC-ID.</p>
    </div>
</section>

<section class="metric-grid">
    <?php foreach ($counts as $k => $v): ?>
    <div class="metric">
        <b><?= number_format($v, 0, ',', '.') ?></b>
        <span><?= h($k) ?></span>
    </div>
    <?php endforeach; ?>
</section>

<?php if ($msg): ?>
    <div class="success" role="alert" style="margin-top: 1.5rem; margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #4ade80;">
        <?= h($msg) ?>
    </div>
<?php endif; ?>

<?php if ($err): ?>
    <div class="error" role="alert" style="margin-top: 1.5rem; margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171;">
        <?= h($err) ?>
    </div>
<?php endif; ?>

<?php if ($newCredentials): ?>
<div class="card" style="border: 1px solid rgba(34,197,94,0.4); background: rgba(34,197,94,0.06); margin-bottom: 1.5rem;">
    <h3 style="color: #4ade80; margin-top: 0;">Credenziali Iniziali Generate</h3>
    <p style="font-size: 0.9rem;">Copia e trasmetti queste informazioni all'utente in modo sicuro. La password è visibile solo adesso:</p>
    <ul style="list-style: none; padding: 0; font-family: monospace; font-size: 0.95rem;">
        <li><strong>Nome:</strong> <?= h($newCredentials['name']) ?></li>
        <li><strong>Email:</strong> <?= h($newCredentials['email']) ?></li>
        <li><strong>SIC-ID:</strong> <?= h($newCredentials['sic_id']) ?></li>
        <li><strong>Password Provvisoria:</strong> <span style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem; border-radius: 4px;"><?= h($newCredentials['pwd']) ?></span></li>
        <li><strong>Recovery Code:</strong> <span style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem; border-radius: 4px;"><?= h($newCredentials['recovery']) ?></span></li>
    </ul>
</div>
<?php endif; ?>

<section class="home-modules">
    <article class="card">
        <h3>Nuovo Utente</h3>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Registra una persona nella community con password provvisoria e recovery code.
        </p>
        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="kind" value="USER">
            
            <label>
                <span>Nome e Cognome / Pseudonimo:</span>
                <input name="name" placeholder="Mario Rossi" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label>
                <span>Indirizzo Email:</span>
                <input type="email" name="email" placeholder="mario.rossi@example.org" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <button type="submit" class="btn primary" style="margin-top: 0.5rem;">Crea Utente</button>
        </form>
    </article>

    <article class="card">
        <h3>Nuovo Nucleo Familiare</h3>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Registra una famiglia collegata a un Club per il programma multifamiliare Hudolin.
        </p>
        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="kind" value="FAMILY">
            
            <label>
                <span>Nome / Codice Nucleo:</span>
                <input name="name" placeholder="Famiglia R." required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label>
                <span>SIC-ID Club Associato:</span>
                <input name="club_sic_id" value="<?= h($scope) ?>" placeholder="Es. SIC-CLUB-..." style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <button type="submit" class="btn primary" style="margin-top: 0.5rem;">Crea Famiglia</button>
        </form>
    </article>

    <article class="card">
        <h3>Professionista di Rete</h3>
        <p style="font-size: 0.9rem; opacity: 0.85; margin-bottom: 1rem;">
            Medici, assistenti sociali, psicologi o educatori che collaborano col territorio.
        </p>
        <form method="post" class="stack">
            <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="kind" value="PROFESSIONAL">
            
            <label>
                <span>Professione / Titolo:</span>
                <input name="profession" placeholder="Es. Medico di Medicina Generale, Assistente Sociale" required style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label>
                <span>Ente / Servizio di Appartenenza:</span>
                <input name="organization" placeholder="Es. SerD ASL, Consultorio Familiare" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <label>
                <span>Territorio Operativo:</span>
                <input name="territory" placeholder="Es. Provincia di Rovigo, Veneto" style="width: 100%; padding: 0.6rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: inherit;">
            </label>
            
            <button type="submit" class="btn primary" style="margin-top: 0.5rem;">Registra Professionista</button>
        </form>
    </article>
</section>

<div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <a href="club-admin.php" class="btn secondary">Gestione Club</a>
    <a href="finance.php" class="btn secondary">Tesoreria</a>
    <a href="documents.php" class="btn secondary">Documenti</a>
</div>

<?php require '_footer.php'; ?>