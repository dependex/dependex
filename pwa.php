<?php
/**
 * DEPENDEX.SOCIAL — PROMEMORIA PRIVATI (PWA)
 * Gestione locale offline di promemoria e check-in quotidiano per la sobrietà.
 */
require_once __DIR__ . '/bootstrap.php';

$pageTitle = 'Promemoria Privati PWA · Cammino di Sobrietà';
$metaDesc = 'Configura promemoria privati e notifiche offline per gli incontri del Club CAT, check-in di sobrietà ed esercizi di respirazione.';
$canonicalUrl = 'https://' . (site_brand()['domain'] ?? 'dependex.social') . '/pwa.php';

$breadcrumbs = [
    'Home' => '/',
    'Promemoria Privati PWA' => 'pwa.php'
];

require '_header.php';
?>

<div class="container" style="max-width:800px;margin:2rem auto;padding:1rem;">
  <div class="card" style="background:#121826;border:1px solid rgba(0,240,255,0.3);border-radius:20px;padding:2rem;box-shadow:0 10px 30px rgba(0,0,0,0.5);">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:1.5rem;">
      <span style="width:48px;height:48px;border-radius:14px;background:rgba(0,240,255,0.15);border:1px solid rgba(0,240,255,0.4);display:inline-flex;align-items:center;justify-content:center;color:#00f0ff;">
        <?=dx_icon('bell', '', 24)?>
      </span>
      <div>
        <h1 style="font-size:1.5rem;margin:0;color:#ffffff;">Promemoria Privati (PWA)</h1>
        <p style="margin:4px 0 0;color:#94a3b8;font-size:0.88rem;">100% locali nel tuo dispositivo · Zero tracciamento su server</p>
      </div>
    </div>

    <p style="color:#cbd5e1;line-height:1.6;font-size:0.95rem;">
      Imposta promemoria discreti per non dimenticare l'incontro settimanale del tuo Club, il check-in giornaliero o un momento di respiro e consapevolezza. I dati restano custoditi nella memoria del tuo browser (LocalStorage).
    </p>

    <div style="margin:2rem 0;display:flex;flex-wrap:wrap;gap:12px;">
      <button type="button" class="btn primary" onclick="if(window.DxLocalReminders)window.DxLocalReminders.renderModal();else alert('Modulo promemoria attivo.');" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;border-radius:10px;background:#00f0ff;color:#070a12;font-weight:700;border:none;cursor:pointer;">
        <?=dx_icon('bell', '', 18)?> <span>Apri Gestione Promemoria</span>
      </button>
      <a href="world-club-explorer.php" class="btn secondary" style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;border-radius:10px;border:1px solid rgba(255,255,255,0.2);color:#ffffff;text-decoration:none;font-weight:600;">
        <?=dx_icon('map-pin', '', 18)?> <span>Trova il tuo Club</span>
      </a>
    </div>

    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:16px;font-size:0.85rem;color:#94a3b8;line-height:1.5;">
      <b style="color:#ffd700;display:block;margin-bottom:6px;">Installazione PWA su Smartphone:</b>
      Tocca l'icona di condivisione del browser e seleziona <i>"Aggiungi alla schermata Home"</i> per utilizzare DEPENDEX come applicazione nativa, anche in assenza di connessione internet.
    </div>
  </div>
</div>

<?php require '_footer.php'; ?>
