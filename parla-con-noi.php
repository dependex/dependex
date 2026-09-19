<?php
require_once __DIR__ . '/bootstrap.php';
$u = current_user();
$brand = site_brand();

$pageTitle = 'Parla con Noi · Uno Spazio Protetto per te e la tua Famiglia';
$metaDesc = 'Hai una domanda? Non sai quale Club contattare? Vuoi parlare prima con qualcuno? Dialoga via WhatsApp, telefono verde o email riservata.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/parla-con-noi.php';

$breadcrumbs = [
    'Home' => '/',
    'Parla con Noi' => 'parla-con-noi.php'
];

$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "ContactPage",
    "name" => "Parla con Noi · DEPENDEX & Comunità dei Club",
    "description" => $metaDesc,
    "url" => $canonicalUrl,
    "contactPoint" => [
        [
            "@type" => "ContactPoint",
            "telephone" => "+39-800-974-250",
            "contactType" => "Numero Verde Nazionale AICAT",
            "areaServed" => "IT",
            "availableLanguage" => ["Italian"]
        ],
        [
            "@type" => "ContactPoint",
            "email" => "info@dependex.support",
            "contactType" => "Ascolto Riservato e Segreteria Solidale",
            "areaServed" => "IT",
            "availableLanguage" => ["Italian"]
        ]
    ]
];

// Gestione invio modulo di orientamento riservato
$messageSent = false;
$errorMsg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'orientamento') {
    csrf_check();
    $nome = trim((string)($_POST['nome'] ?? 'Anonimo'));
    $contatto = trim((string)($_POST['contatto'] ?? ''));
    $citta = trim((string)($_POST['citta'] ?? ''));
    $ruolo = trim((string)($_POST['ruolo'] ?? 'PERSONA'));
    $messaggio = trim((string)($_POST['messaggio'] ?? ''));

    if (empty($contatto) || empty($citta)) {
        $errorMsg = 'Per poterti rispondere abbiamo bisogno di una città/provincia e di un recapito (email o telefono).';
    } else {
        try {
            // Registrazione audit e memorizzazione protetta della richiesta
            $meta = [
                'nome' => $nome,
                'contatto' => $contatto,
                'citta' => $citta,
                'ruolo' => $ruolo,
                'messaggio' => $messaggio,
                'data' => date('c')
            ];
            audit(null, 'PARLA_CON_NOI_RICHIESTA', null, $meta);
            $messageSent = true;
        } catch (Throwable $e) {
            $errorMsg = 'Qualcosa non ha funzionato. Puoi contattarci direttamente su WhatsApp o via email.';
        }
    }
}

require '_header.php';
?>

<div class="container py-4" style="max-width: 1040px; margin: 0 auto; padding: 0 1rem;">

  <!-- HERO DELLA PAGINA PARLA CON NOI -->
  <section class="human-hero-card text-center" style="margin-top: 1rem;">
    <div class="badge-human mb-3">
      <span class="dot"></span>
      <span>SPAZIO PROTETTO · ASCOLTO SENZA GIUDIZIO</span>
    </div>

    <h1 class="human-hero-title" style="font-size: clamp(2rem, 4.5vw, 3rem);">
      Non devi affrontare tutto questo da solo.<br>
      <span class="text-rainbow">Siamo qui per ascoltarti.</span>
    </h1>

    <p class="human-hero-desc mx-auto">
      Hai una domanda? Non sai a quale Club rivolgerti? Vuoi capire come si svolge un incontro prima di presentarti? 
      Puoi iniziare con una telefonata al Numero Verde, una semplice email o inviando una richiesta tramite il modulo qui sotto.
    </p>

    <!-- CANALI RAPIDI IN EVIDENZA -->
    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
      <a href="#modulo-orientamento" 
         class="btn-community-wa" 
         style="background: rgba(0, 240, 255, 0.15); border: 1px solid var(--neon-cyan); color: #00f0ff;"
         title="Scrivi direttamente dal sito">
        <?=dx_icon('send', '', 18)?>
        <span>Modulo Riservato Online</span>
      </a>

      <a href="tel:800974250" class="btn-rainbow-neon" style="box-shadow: var(--glow-gold);" title="Chiama il numero verde AICAT">
        <?=dx_icon('phone', '', 18)?>
        <span style="margin-left: 8px;">Numero Verde AICAT: 800 974250</span>
      </a>

      <a href="mailto:info@dependex.support" class="btn-rainbow-outline" style="border-color: var(--neon-cyan); color: #fff;" title="Invia una email riservata">
        <?=dx_icon('mail', 'text-neon-cyan', 18)?>
        <span style="margin-left: 8px;">info@dependex.support</span>
      </a>
    </div>
  </section>

  <!-- GRIGLIA A 2 COLONNE: CANALI & MODULO DI ORIENTAMENTO -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 1fr)); gap: 28px; margin: 3rem 0;">

    <!-- COLONNA 1: CON CHI PARLI -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      
      <div class="card p-4" style="background: var(--dx-night-card); border: 1px solid var(--dx-night-border); border-radius: var(--dx-radius-lg);">
        <h2 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: 1.4rem; margin: 0 0 12px; display: flex; align-items: center; gap: 10px;">
          <?=dx_icon('heart-handshake', 'text-amber', 22)?> Chi troverai ad ascoltarti?
        </h2>
        <p style="color: var(--dx-text-subtle); font-size: 0.96rem; line-height: 1.65; margin-bottom: 14px;">
          Dall'altra parte non c'è un call center commerciale né una voce automatica. Troverai persone che conoscono da vicino l'esperienza dei Club Alcologici Territoriali e delle famiglie:
        </p>
        <ul style="color: var(--dx-text-subtle); font-size: 0.92rem; line-height: 1.6; padding-left: 1.2rem; margin: 0;">
          <li style="margin-bottom: 8px;"><b>Segreteria di Accoglienza:</b> referente per l'orientamento iniziale e l'accoglienza empatica di famiglie e persone in cammino.</li>
          <li style="margin-bottom: 8px;"><b>Servitori-Insegnanti del territorio:</b> volontari formati secondo il Metodo Hudolin disponibili a indicarti il Club più vicino a casa tua.</li>
          <li><b>Riservatezza totale:</b> non ti verrà mai chiesto di rivelare dati che non desideri condividere.</li>
        </ul>
      </div>

      <!-- BOX SOCCORSO MEDICO D'URGENZA -->
      <div class="card p-4" style="background: rgba(231, 111, 81, 0.08); border-left: 4px solid var(--dx-coral); border-radius: var(--dx-radius-md);">
        <h3 style="color: #ffffff; font-size: 1.1rem; margin: 0 0 8px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('alert-triangle', 'text-coral', 20)?> In caso di emergenza medica acuta
        </h3>
        <p style="color: var(--dx-text-subtle); font-size: 0.88rem; line-height: 1.55; margin: 0 0 10px;">
          I Club offrono una comunità relazionale e di amicizia, ma non possono sostituire il pronto soccorso ospedaliero in caso di intossicazione acuta o pericolo per l'incolumità:
        </p>
        <div style="font-size: 0.92rem; color: #ffffff;">
          <b>Numero Unico Europeo Emergenze:</b> <a href="tel:112" style="color: var(--dx-amber); font-weight: 800; text-decoration: underline;">112</a>
        </div>
      </div>

    </div>

    <!-- COLONNA 2: MODULO DI ASCOLTO E CONTATTO RISERVATO -->
    <div class="card p-4 p-md-5" style="background: var(--dx-night-card); border: 1px solid var(--dx-night-border); border-radius: var(--dx-radius-lg);">
      <h2 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: 1.4rem; margin: 0 0 6px;">
        Invia una richiesta riservata
      </h2>
      <p style="color: var(--dx-text-subtle); font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">
        Se preferisci non chiamare adesso, lasciaci un messaggio. Ti ricontatteremo con la massima delicatezza.
      </p>

      <?php if ($messageSent): ?>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--dx-emerald); border-radius: var(--dx-radius-md); padding: 18px; color: #ffffff; margin-bottom: 20px;">
          <h4 style="color: #a7f3d0; margin: 0 0 6px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
            <?=dx_icon('check-circle', '', 18)?> Grazie per averci scritto
          </h4>
          <p style="font-size: 0.92rem; line-height: 1.5; margin: 0;">
            Abbiamo ricevuto la tua richiesta. Un nostro referente si metterà in contatto con te al recapito indicato nel più breve tempo possibile.
          </p>
        </div>
      <?php endif; ?>

      <?php if ($errorMsg): ?>
        <div style="background: rgba(231, 111, 81, 0.15); border: 1px solid var(--dx-coral); border-radius: var(--dx-radius-md); padding: 14px; color: #ffffff; margin-bottom: 20px; font-size: 0.9rem;">
          <?=h($errorMsg)?>
        </div>
      <?php endif; ?>

      <form action="parla-con-noi.php" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
        <input type="hidden" name="<?=h(CSRF_KEY)?>" value="<?=h(csrf_token())?>">
        <input type="hidden" name="action" value="orientamento">

        <div>
          <label style="display: block; font-size: 0.88rem; font-weight: 700; color: #ffffff; margin-bottom: 6px;">Come possiamo chiamarti? (Anche solo il nome di battesimo)</label>
          <input type="text" name="nome" placeholder="Il tuo nome..." style="width: 100%; padding: 12px 14px; background: rgba(9, 13, 26, 0.8); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--dx-radius-md); color: #fff; font-size: 0.95rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.88rem; font-weight: 700; color: #ffffff; margin-bottom: 6px;">Un recapito a cui possiamo risponderti *</label>
          <input type="text" name="contatto" required placeholder="Numero di telefono o email riservata..." style="width: 100%; padding: 12px 14px; background: rgba(9, 13, 26, 0.8); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--dx-radius-md); color: #fff; font-size: 0.95rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.88rem; font-weight: 700; color: #ffffff; margin-bottom: 6px;">La tua città o provincia *</label>
          <input type="text" name="citta" required placeholder="Es. Rovigo, Milano, Napoli, Treviso..." style="width: 100%; padding: 12px 14px; background: rgba(9, 13, 26, 0.8); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--dx-radius-md); color: #fff; font-size: 0.95rem;">
        </div>

        <div>
          <label style="display: block; font-size: 0.88rem; font-weight: 700; color: #ffffff; margin-bottom: 6px;">Stai cercando aiuto per te o per una persona cara?</label>
          <select name="ruolo" style="width: 100%; padding: 12px 14px; background: rgba(9, 13, 26, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--dx-radius-md); color: #fff; font-size: 0.95rem;">
            <option value="PERSONA">Per me stesso / me stessa</option>
            <option value="FAMIGLIA">Per un familiare o il partner</option>
            <option value="AMICO">Per un amico o conoscente</option>
            <option value="INFORMAZIONE">Desidero solo informazioni generali</option>
          </select>
        </div>

        <div>
          <label style="display: block; font-size: 0.88rem; font-weight: 700; color: #ffffff; margin-bottom: 6px;">Vuoi dirci qualcosa in più? (Facoltativo)</label>
          <textarea name="messaggio" rows="3" placeholder="Scrivi quello che ti senti, senza timore..." style="width: 100%; padding: 12px 14px; background: rgba(9, 13, 26, 0.8); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--dx-radius-md); color: #fff; font-size: 0.95rem;"></textarea>
        </div>

        <button type="submit" class="btn-rainbow-neon" style="width: 100%; margin-top: 8px; justify-content: center;">
          <?=dx_icon('send', '', 18)?>
          <span style="margin-left: 8px;">Invia la richiesta riservata</span>
        </button>

        <small style="display: block; font-size: 0.78rem; color: var(--dx-text-muted); text-align: center; margin-top: 6px;">
          I tuoi dati sono protetti e trattati esclusivamente per fornirti risposta di orientamento. Nessuna profilazione commerciale.
        </small>
      </form>
    </div>

  </div>

</div>

<?php require '_footer.php'; ?>
