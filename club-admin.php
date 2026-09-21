<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$u = require_admin();
$club = club_for_user($u['sic_id']);

if ($club && !acl_can($u['sic_id'], 'club', 'MANAGE', $club['sic_id'], $u['sic_id']) && !has_role($u['sic_id'], 'SUPERADMIN')) {
    http_response_code(403);
    $pageTitle = 'Accesso Riservato · Gestione Club';
    $metaDesc = 'Permessi insufficienti per la gestione amministrativa di questo Club.';
    require __DIR__ . '/_header.php';
    ?>
    <main class="page-container" style="max-width: 700px; margin: 40px auto; padding: 24px 16px; text-align: center;">
      <section class="card" style="background: #101116; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 20px; padding: 40px 24px;">
        <h1 style="color: #f87171; font-size: 1.6rem; margin-bottom: 12px;">Accesso Riservato</h1>
        <p style="color: var(--muted, #a1a1aa); margin-bottom: 24px; line-height: 1.6;">
          Non disponi dei privilegi di gestione per questo presidio territoriale. Contatta il servitore-insegnante o l'amministratore del Club.
        </p>
        <a class="btn primary" href="club.php">← Torna al tuo Club</a>
      </section>
    </main>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $club) {
    csrf_check();
    try {
        $newName = trim((string)($_POST['new_name'] ?? 'Nuovo Club'));
        if ($newName === '') {
            throw new InvalidArgumentException('Inserisci un nome valido per il nuovo Club.');
        }
        $new = create_club_multiplication($club['sic_id'], $newName, $u['sic_id']);
        $msg = 'Nuovo presidio Club creato con successo con identificativo: ' . $new;
    } catch (Throwable $e) {
        $err = 'Errore durante la creazione: ' . $e->getMessage();
    }
}

$pageTitle = 'Gestione Amministrativa Club';
$metaDesc = 'Pannello di gestione per servitori-insegnanti: anagrafe famiglie, moltiplicazione e risorse.';

require __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width: 900px; margin: 0 auto; padding: 24px 16px;">
  <section class="section-head" style="margin-bottom: 28px;">
    <span class="eyebrow" style="color: var(--neon-gold, #D4AF37); font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.08em;">
      Club Administration Panel
    </span>
    <h1 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight: 800; margin: 8px 0 6px; color: #FFFFFF;">
      Gestione Club Territoriale
    </h1>
    <p style="color: var(--muted, #a1a1aa); font-size: 1.05rem;">
      Supervisione delle famiglie, continuità degli incontri, tesoreria e moltiplicazione secondo il Metodo Hudolin.
    </p>
  </section>

  <?php if ($msg): ?>
    <div class="alert success" style="background: rgba(74, 222, 128, 0.12); border: 1px solid rgba(74, 222, 128, 0.4); color: #4ade80; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
      <?= h($msg) ?>
    </div>
  <?php endif; ?>

  <?php if ($err): ?>
    <div class="alert danger" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; font-weight: 600;">
      <?= h($err) ?>
    </div>
  <?php endif; ?>

  <?php if ($club): 
    $s = club_rank_snapshot($club['sic_id']);
  ?>
    <!-- Sezione Moltiplicazione Territoriale -->
    <section class="card" style="background: #101116; border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 20px; padding: 28px; margin-bottom: 28px;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
        <div>
          <h2 style="font-size: 1.3rem; font-weight: 700; color: #FFFFFF; margin: 0 0 6px;">
            Moltiplicazione del Presidio
          </h2>
          <p style="color: var(--muted, #a1a1aa); font-size: 0.95rem; margin: 0;">
            Famiglie attive censite: <strong style="color: #FFF2B2;"><?= (int)($s['families'] ?? 0) ?></strong> · Soglia pre-moltiplicazione: 8 · Limite consigliato Hudolin: 12
          </p>
        </div>
        <span style="font-size: 0.8rem; background: rgba(255,255,255,0.06); padding: 4px 10px; border-radius: 8px; color: #a1a1aa;">
          Nodo: <?= h($club['sic_id']) ?>
        </span>
      </div>

      <form method="post" style="display: flex; flex-direction: column; gap: 16px;">
        <input type="hidden" name="<?= CSRF_KEY ?>" value="<?= h(csrf_token()) ?>">
        <div>
          <label for="new_name" style="display: block; color: #d1d5db; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px;">
            Nome del Nuovo Ramo Club da Generare:
          </label>
          <input 
            id="new_name" 
            name="new_name" 
            type="text" 
            placeholder="Es. Club 2 San Francesco, CAT Il Gelso..." 
            required 
            style="width: 100%; min-height: 44px; padding: 10px 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; color: #FFFFFF; font-size: 1rem;"
          >
        </div>
        <button type="submit" class="btn primary" style="align-self: flex-start; min-height: 44px; padding: 0 24px; border-radius: 12px; font-weight: 700; cursor: pointer;">
          Genera Nuovo Ramo Territoriale
        </button>
      </form>
    </section>
  <?php else: ?>
    <section class="card" style="background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 24px; margin-bottom: 28px;">
      <p style="color: #a1a1aa; margin: 0;">Nessun Club attualmente collegato al tuo account amministrativo.</p>
    </section>
  <?php endif; ?>

  <!-- Strumenti di Gestione e Risorse Correlate -->
  <section class="menu-list" style="display: flex; flex-direction: column; gap: 12px;">
    <a href="hudolin-core.php" class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; text-decoration: none; color: inherit;">
      <div>
        <strong style="color: #FFFFFF; font-size: 1.05rem;">📜 Hudolin Core & Metodologia</strong>
        <div style="color: #a1a1aa; font-size: 0.85rem; margin-top: 2px;">Linee guida ecologico-sociali e protocolli di conduzione</div>
      </div>
      <span style="color: #D4AF37; font-size: 1.3rem;">›</span>
    </a>

    <a href="finance.php" class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; text-decoration: none; color: inherit;">
      <div>
        <strong style="color: #FFFFFF; font-size: 1.05rem;">💶 Tesoreria & Quote di Partecipazione</strong>
        <div style="color: #a1a1aa; font-size: 0.85rem; margin-top: 2px;">Gestione trasparente delle spese vive della sede e donazioni</div>
      </div>
      <span style="color: #D4AF37; font-size: 1.3rem;">›</span>
    </a>

    <a href="documents.php" class="card" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; background: #101116; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; text-decoration: none; color: inherit;">
      <div>
        <strong style="color: #FFFFFF; font-size: 1.05rem;">📄 Documenti e Verbali Digitali</strong>
        <div style="color: #a1a1aa; font-size: 0.85rem; margin-top: 2px;">Archivio documentale sicuro con marcatura SHA-256</div>
      </div>
      <span style="color: #D4AF37; font-size: 1.3rem;">›</span>
    </a>
  </section>
</main>

<?php require __DIR__ . '/_footer.php'; ?>