<?php
/**
 * unsubscribe.php — Gestore Disiscrizione Conforme RFC 8058 One-Click
 * Gestisce sia richieste GET da browser che richieste POST automatiche RFC 8058 dai client email.
 */
require_once __DIR__ . '/bootstrap.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$success = false;
$clubName = '';

if ($token !== '') {
    try {
        $db = new PDO('sqlite:' . __DIR__ . '/data/acat_community.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec("PRAGMA busy_timeout = 10000;");

        $stmt = $db->prepare("SELECT id, entity_name, primary_email FROM crm_club_contacts WHERE unsubscribe_token = :t LIMIT 1");
        $stmt->execute([':t' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $clubName = $row['entity_name'];
            $upd = $db->prepare("UPDATE crm_club_contacts SET outreach_status = 'UNSUBSCRIBED', unsubscribed_at = CURRENT_TIMESTAMP WHERE id = :id");
            $upd->execute([':id' => $row['id']]);
            $success = true;

            // Se è richiesta RFC 8058 POST, rispondi con 200 OK pulito
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                http_response_code(200);
                header('Content-Type: text/plain; charset=utf-8');
                echo "Unsubscribed successfully (RFC 8058)";
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("Errore unsubscribe: " . $e->getMessage());
    }
}

$pageTitle = "Disiscrizione Comunicazioni Istituzionali · DEPENDEX";
include __DIR__ . '/_header.php';
?>

<main class="page-container" style="max-width:680px; margin:40px auto; padding:24px 16px;">
  <div style="background:#161b22; border:1px solid #30363d; border-radius:16px; padding:32px 24px; text-align:center;">
    <?php if ($success): ?>
      <div style="width:64px; height:64px; border-radius:50%; background:rgba(56,239,125,0.1); border:2px solid #38ef7d; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; font-size:28px;">
        ✓
      </div>
      <h1 style="font-size:22px; color:#ffffff; margin:0 0 12px 0;">Disiscrizione Confermata</h1>
      <p style="color:#c9d1d9; font-size:15px; line-height:1.6; margin:0 0 16px 0;">
        Il recapito associato a <strong style="color:#38ef7d;"><?=htmlspecialchars($clubName)?></strong> è stato rimosso dalla nostra lista di aggiornamenti istituzionali.
      </p>
      <p style="color:#8b949e; font-size:14px; line-height:1.6; margin:0 0 24px 0;">
        La scheda del vostro Club rimarrà comunque visibile gratuitamente sulla mappa pubblica per aiutare le famiglie del territorio, a meno che non ci richiediate esplicitamente la cancellazione totale scrivendo a <a href="mailto:info@dependex.support" style="color:#58a6ff;">info@dependex.support</a>.
      </p>
    <?php else: ?>
      <div style="width:64px; height:64px; border-radius:50%; background:rgba(248,81,73,0.1); border:2px solid #f85149; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; font-size:28px;">
        ℹ
      </div>
      <h1 style="font-size:22px; color:#ffffff; margin:0 0 12px 0;">Richiesta di Disiscrizione</h1>
      <p style="color:#c9d1d9; font-size:15px; line-height:1.6; margin:0 0 20px 0;">
        Il token di disiscrizione fornito non è valido o è già stato elaborato in precedenza.
      </p>
      <p style="color:#8b949e; font-size:14px; line-height:1.6; margin:0 0 24px 0;">
        Per qualsiasi necessità di cancellazione immediata, potete contattare direttamente la nostra segreteria scrivendo a <a href="mailto:info@dependex.support" style="color:#58a6ff;">info@dependex.support</a>.
      </p>
    <?php endif; ?>

    <a href="index.php" style="display:inline-block; padding:12px 24px; background:#21262d; border:1px solid #30363d; border-radius:8px; color:#e6edf3; text-decoration:none; font-weight:600; font-size:14px;">
      ← Torna alla Home di dependex.social
    </a>
  </div>
</main>

<?php include __DIR__ . '/_footer.php'; ?>
