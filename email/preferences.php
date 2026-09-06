<?php
/**
 * DEPENDEX & OLTRE — EMAIL PREFERENCE CENTER
 * Permette ai contatti di selezionare argomenti, frequenza e canale di contatto.
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../email-engine.php';

$email = trim(strtolower($_GET['email'] ?? ($_POST['email'] ?? '')));
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $prefStories = isset($_POST['pref_stories']) ? 1 : 0;
    $prefEvents = isset($_POST['pref_events']) ? 1 : 0;
    $prefOffers = isset($_POST['pref_offers']) ? 1 : 0;
    $frequency = $_POST['frequency'] ?? 'weekly';

    try {
        $db = email_os_db();
        $db->exec("
            CREATE TABLE IF NOT EXISTS contact_preferences (
                email TEXT PRIMARY KEY,
                stories INTEGER DEFAULT 1,
                events INTEGER DEFAULT 1,
                offers INTEGER DEFAULT 1,
                frequency TEXT DEFAULT 'weekly',
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $stmt = $db->prepare("
            INSERT INTO contact_preferences (email, stories, events, offers, frequency, updated_at)
            VALUES (?, ?, ?, ?, ?, datetime('now'))
            ON CONFLICT(email) DO UPDATE SET
                stories = excluded.stories,
                events = excluded.events,
                offers = excluded.offers,
                frequency = excluded.frequency,
                updated_at = excluded.updated_at
        ");
        $stmt->execute([$email, $prefStories, $prefEvents, $prefOffers, $frequency]);
        $msg = 'Preferenze aggiornate con successo.';
    } catch (\Throwable $e) {
        $msg = 'Errore durante il salvataggio: ' . htmlspecialchars($e->getMessage());
    }
}

$pageTitle = 'Centro Preferenze Email · DEPENDEX';
require __DIR__ . '/../_header.php';
?>

<div class="luxury-backdrop" style="min-height: 80vh; padding: 40px 20px;">
  <div class="lux-metallic-card" style="max-width: 650px; margin: 0 auto; padding: 40px 32px; border: 1px solid rgba(212,175,55,0.3); border-radius: 16px; background: #0b0f19;">
    <div style="color: #D4AF37; font-size: 0.85rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px;">
      SOVRANITÀ DIGITALE & PRIVACY
    </div>
    
    <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; margin: 0 0 12px 0;">
      Centro Preferenze Comunicazioni
    </h1>
    
    <p style="color: #94a3b8; font-size: 1rem; line-height: 1.6; margin-bottom: 24px;">
      Decidi cosa ricevere e con quale frequenza. Sei tu a guidare il tuo percorso nel Club.
    </p>

    <?php if ($msg): ?>
      <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #4ade80; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-weight: 600;">
        <?=htmlspecialchars($msg)?>
      </div>
    <?php endif; ?>

    <form method="POST" action="preferences.php">
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 0.9rem; color: #cbd5e1; margin-bottom: 6px;">Il tuo indirizzo email:</label>
        <input type="email" name="email" value="<?=htmlspecialchars($email)?>" required style="width: 100%; padding: 12px 14px; background: #020617; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 0.95rem;">
      </div>

      <div style="margin-bottom: 24px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); padding: 18px; border-radius: 8px;">
        <div style="font-weight: 700; color: #f8fafc; margin-bottom: 12px; font-size: 0.95rem;">Argomenti di interesse:</div>
        
        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; color: #cbd5e1; cursor: pointer;">
          <input type="checkbox" name="pref_stories" value="1" checked style="accent-color: #d99a26; width: 18px; height: 18px;">
          <span><strong>Storie e Metodo Hudolin</strong> (Approfondimenti sui 5 passi, testimonianze reali e spunti di sobrietà)</span>
        </label>

        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; color: #cbd5e1; cursor: pointer;">
          <input type="checkbox" name="pref_events" value="1" checked style="accent-color: #d99a26; width: 18px; height: 18px;">
          <span><strong>Eventi e Moduli SAT</strong> (Webinar, congressi, riunioni e corsi di formazione per Servitori)</span>
        </label>

        <label style="display: flex; align-items: center; gap: 10px; color: #cbd5e1; cursor: pointer;">
          <input type="checkbox" name="pref_offers" value="1" checked style="accent-color: #d99a26; width: 18px; height: 18px;">
          <span><strong>Risorse della Vault e Offerte di Valore</strong> (Starter Kit, protocollo di accompagnamento e supporto)</span>
        </label>
      </div>

      <div style="margin-bottom: 28px;">
        <label style="display: block; font-size: 0.9rem; color: #cbd5e1; margin-bottom: 8px;">Frequenza massima preferita:</label>
        <select name="frequency" style="width: 100%; padding: 12px 14px; background: #020617; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 0.95rem;">
          <option value="weekly">Settimanale (Consigliata · 1 email ogni 7 giorni)</option>
          <option value="biweekly">Quindicinale (1 email ogni 14 giorni)</option>
          <option value="monthly">Mensile (1 digest riepilogativo al mese)</option>
        </select>
      </div>

      <button type="submit" class="btn primary" style="width: 100%; border-radius: 12px; min-height: 48px; font-size: 1rem;">
        Salva Preferenze
      </button>

      <div style="text-align: center; margin-top: 20px;">
        <a href="unsubscribe.php?email=<?=urlencode($email)?>" style="color: #94a3b8; font-size: 0.85rem; text-decoration: underline;">
          Preferisci non ricevere più alcuna email? Disiscriviti completamente con un click.
        </a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../_footer.php'; ?>
