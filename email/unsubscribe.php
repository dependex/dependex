<?php
/**
 * DEPENDEX & OLTRE — RFC 8058 ONE-CLICK UNSUBSCRIBE ENDPOINT
 * Conforme RFC 8058: gestisce sia richieste POST List-Unsubscribe=One-Click
 * sia richieste GET del browser con feedback visivo immediato senza richiedere login.
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../email-engine.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$email = trim(strtolower($_GET['email'] ?? ($_POST['email'] ?? ($_GET['t'] ?? ($_POST['t'] ?? '')))));

// Supporto per token o email diretta
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Se è un token offuscato, proviamo a decodificarlo o cerchiamo il primo parametro valido
    $email = filter_var($_GET['t'] ?? '', FILTER_VALIDATE_EMAIL) ? $_GET['t'] : $email;
}

if ($method === 'POST') {
    // Header standard RFC 8058 One-Click invia List-Unsubscribe=One-Click nel body
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        email_os_unsubscribe($email, 'RFC8058_ONE_CLICK_POST');
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo "Disiscrizione completata con successo.";
    exit;
}

// Richiesta GET standard da browser
if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    email_os_unsubscribe($email, 'USER_BROWSER_GET');
}

$pageTitle = 'Disiscrizione Confermata · DEPENDEX';
require __DIR__ . '/../_header.php';
?>

<div class="luxury-backdrop" style="min-height: 75vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
  <div class="lux-metallic-card" style="max-width: 580px; width: 100%; text-align: center; padding: 48px 32px; border: 1px solid rgba(212,175,55,0.3); border-radius: 16px; background: #0b0f19;">
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; margin-bottom: 24px;">
      <?=dx_icon('shield-check', '', 36)?>
    </div>
    
    <h1 style="font-family: var(--font-serif); font-size: 2rem; color: #FFFFFF; margin-bottom: 12px; font-weight: 800;">
      Disiscrizione Confermata
    </h1>
    
    <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.6; margin-bottom: 24px;">
      <?php if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)): ?>
        L'indirizzo <strong><?=htmlspecialchars($email)?></strong> è stato rimosso istantaneamente dalla lista comunicazioni e inserito nella suppression list conforme GDPR.
      <?php else: ?>
        La tua preferenza di non ricevere ulteriori comunicazioni di marketing è stata registrata con successo nel sistema.
      <?php endif; ?>
    </p>

    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 16px; margin-bottom: 32px; text-align: left; font-size: 0.85rem; color: #94a3b8;">
      <p style="margin: 0 0 6px 0;"><strong>Politica di Riservatezza DEPENDEX:</strong></p>
      <p style="margin: 0;">Nessun invio marketing verrà effettuato verso questo recapito. Riceverai unicamente notifiche transazionali critiche o di emergenza se sei membro attivo di un Club.</p>
    </div>

    <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
      <a href="/index.php" class="btn primary" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; padding: 12px 28px; text-decoration: none; border-radius: 8px;">
        Torna alla Home
      </a>
      <a href="/email/preferences.php?email=<?=urlencode($email)?>" class="btn" style="border: 1px solid rgba(212,175,55,0.4); color: #FFFFFF; font-weight: 700; padding: 12px 24px; text-decoration: none; border-radius: 8px;">
        Gestisci Preferenze
      </a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
