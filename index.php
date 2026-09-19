<?php
require_once __DIR__ . '/bootstrap.php';

$pageTitle = 'Hai Bisogno di Parlarne? · Trova la tua Comunità nei Club Territoriali';
$metaDesc = 'Non devi sapere già tutto. Puoi semplicemente iniziare. Oltre 540 Club Alcologici Territoriali gratuiti e aperti alle famiglie. Metodo Hudolin, zero giudizio.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/';

$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "WebSite",
    "name" => "DEPENDEX · AL CLUB. COL CLUB.",
    "description" => $metaDesc,
    "url" => $canonicalUrl,
    "potentialAction" => [
        "@type" => "SearchAction",
        "target" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/world-club-explorer.php?q={search_term_string}",
        "query-input" => "required name=search_term_string"
    ]
];

require '_header.php';

// Notizie dalla rete per il ticker
$newsCards = AcatNewsService::getLatestCards(8);
$totalNodes = 542;
try {
    $totalNodes = (int)db()->query("SELECT COUNT(*) FROM dependex_world_registry")->fetchColumn() ?: 542;
} catch (Throwable $e) {}
?>

<!-- ============================================================== -->
<!-- 1. HERO EMPATICA CON BORDO RAINBOW & GLOW NEON                 -->
<!-- ============================================================== -->
<section class="human-hero-card my-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <div class="badge-neon-rainbow mb-3">
        <span class="dot"></span>
        <span style="color: #fde68a;">UNA COMUNITÀ MONDIALE DI PERSONE, FAMIGLIE E CLUB</span>
      </div>

      <h1 class="human-hero-title">
        Hai bisogno di parlarne?<br>
        <span class="text-rainbow">Non devi sapere già tutto. Puoi semplicemente iniziare.</span>
      </h1>

      <p class="human-hero-desc">
        Che tu stia vivendo una difficoltà legata all'alcol, ad altre sostanze, al gioco o a un momento di solitudine, o che tu sia un familiare che non sa più come aiutare chi ama: nei Club Alcologici Territoriali trovi persone che si incontrano ogni settimana per camminare insieme. Senza cartelle cliniche, senza costi, senza giudizio.
      </p>

      <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <a href="mappa-club.php" class="btn-rainbow-neon" title="Cerca il Club più vicino alla tua zona su Mappa Georeferenziata 2D">
          <?=dx_icon('map-pin', '', 18)?>
          <span style="margin-left: 8px;">Mappa Club 2D (322 Nodi)</span>
        </a>
        <a href="world-club-explorer.php" class="btn-rainbow-outline" style="border-color: var(--neon-cyan); color: #ffffff;" title="Cerca il Club nell'elenco alfabetico">
          <?=dx_icon('search', 'text-neon-cyan', 18)?>
          <span style="margin-left: 8px;">Elenco Club</span>
        </a>
        <a href="parla-con-noi.php" class="btn-rainbow-outline" style="border-color: var(--neon-gold); color: #ffffff;" title="Inizia una conversazione riservata">
          <?=dx_icon('message-circle', 'text-neon-gold', 18)?>
          <span style="margin-left: 8px;">Parla con Noi</span>
        </a>
      </div>
    </div>

    <div class="col-lg-4 text-center d-none d-lg-block">
      <div class="rainbow-border p-4" style="background: rgba(8, 11, 20, 0.88); backdrop-filter: blur(16px); text-align: center;">
        <div style="margin-bottom: 18px; display: inline-block;">
          <img src="assets/img/dependex-rainbow-badge.webp" alt="Sigillo Cosmico Dependex" style="width: 150px; height: 150px; border-radius: 50%; box-shadow: var(--rainbow-glow); border: 2px solid rgba(255,215,0,0.45); animation: rainbow-pulse 4s ease-in-out infinite;">
        </div>
        <h3 style="font-family: var(--font-serif); color: #FFFFFF; font-size: 1.3rem; margin-bottom: 8px; font-weight: 800;">
          <span class="text-rainbow">Porte Sempre Aperte</span>
        </h3>
        <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.6; margin: 0;">
          Non serve una ricetta medica né un'iscrizione formale. C'è una sedia pronta per te in ogni Club d'Italia e del mondo.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 7 PILASTRI DELL'ARCOBALENO: FREQUENZE DI SOVRANITÀ E RINASCITA -->
<!-- ============================================================== -->
<section class="my-4">
  <div class="rainbow-pillars-grid">
    <div class="rainbow-pillar-card pillar-red">
      <span class="pillar-icon"><?=dx_icon('lotus', 'text-neon-red', 32)?></span>
      <div class="pillar-title text-neon-red">Senti</div>
      <div class="pillar-sub">Radicarsi · Ascoltare · Sentire</div>
    </div>
    <div class="rainbow-pillar-card pillar-orange">
      <span class="pillar-icon"><?=dx_icon('waves', 'text-neon-orange', 32)?></span>
      <div class="pillar-title text-neon-orange">Agisci</div>
      <div class="pillar-sub">Fluire · Muovere · Creare</div>
    </div>
    <div class="rainbow-pillar-card pillar-gold">
      <span class="pillar-icon"><?=dx_icon('mic', 'text-neon-gold', 32)?></span>
      <div class="pillar-title text-neon-gold">Comunica</div>
      <div class="pillar-sub">Esprimere · Dire · Manifestare</div>
    </div>
    <div class="rainbow-pillar-card pillar-green">
      <span class="pillar-icon"><?=dx_icon('mountain', 'text-neon-green', 32)?></span>
      <div class="pillar-title text-neon-green">Vedi</div>
      <div class="pillar-sub">Osservare · Scegliere · Orientarsi</div>
    </div>
    <div class="rainbow-pillar-card pillar-cyan">
      <span class="pillar-icon"><?=dx_icon('heart-handshake', 'text-neon-cyan', 32)?></span>
      <div class="pillar-title text-neon-cyan">Ama</div>
      <div class="pillar-sub">Amare · Relazionare · Accogliere</div>
    </div>
    <div class="rainbow-pillar-card pillar-indigo">
      <span class="pillar-icon"><?=dx_icon('feather', 'text-neon-indigo', 32)?></span>
      <div class="pillar-title text-neon-indigo">Costruisci</div>
      <div class="pillar-sub">Strutturare · Creare · Costruire</div>
    </div>
    <div class="rainbow-pillar-card pillar-violet">
      <span class="pillar-icon"><?=dx_icon('crown', 'text-neon-violet', 32)?></span>
      <div class="pillar-title text-neon-violet">Sii</div>
      <div class="pillar-sub">Integrare · Trascendere · Diventare</div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- PANORAMA ARCOBALENO DEI 7 PORTALI                              -->
<!-- ============================================================== -->
<div class="rainbow-panorama-banner my-4">
  <img src="assets/img/rainbow-portals.webp" alt="I 7 Portali dell'Arcobaleno e Frequenze di Rinascita" loading="lazy">
</div>

<!-- ============================================================== -->
<!-- 2. SECTION 2: TROVA LA TUA COMUNITÀ (RICERCA & MAPPA)          -->
<!-- ============================================================== -->
<section class="rainbow-border p-4 p-md-5 my-5" id="trova-club" style="background: rgba(11, 15, 27, 0.92); backdrop-filter: blur(20px);">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <div class="gold-glow-badge mb-2">
        <?=dx_icon('map-pin', 'text-neon-gold', 14)?>
        <span><?=$totalNodes?> NODI TERRITORIALI CENSITI & VERIFICATI</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.7rem); color: #ffffff; margin: 6px 0 10px; font-weight: 800;">
        <span class="text-rainbow">Trova la tua comunità</span>
      </h2>
      <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.6; margin: 0;">
        Cerca il Club più vicino a te. Puoi inserire la tua città, il CAP, la provincia o il territorio.
      </p>

      <form action="world-club-explorer.php" method="GET" class="community-search-form" style="margin-top: 1.4rem;">
        <input type="text" 
               name="q" 
               required 
               placeholder="Inserisci la tua città o provincia (es. Rovigo, Milano, Napoli, Padova...)" 
               class="community-search-input"
               style="border: 1.5px solid rgba(0, 212, 255, 0.4); box-shadow: 0 0 16px rgba(0, 212, 255, 0.15);"
               aria-label="Cerca Club per comune o provincia">
        <button type="submit" class="btn-rainbow-neon" style="white-space: nowrap;">
          <?=dx_icon('search', '', 18)?>
          <span style="margin-left: 6px;">Cerca Club</span>
        </button>
      </form>
      <small style="display: block; font-size: 0.82rem; color: #94a3b8; margin-top: 10px;">
        Nessun dato personale richiesto per la ricerca. Consultazione 100% libera e riservata.
      </small>
    </div>

    <div class="col-lg-5">
      <div class="card p-4 text-center" style="background: rgba(14, 18, 30, 0.85); border: 1.5px solid rgba(0, 212, 255, 0.35); box-shadow: 0 0 25px rgba(0, 212, 255, 0.2);">
        <div style="color: var(--neon-cyan); margin-bottom: 8px;"><?=dx_icon('compass', 'text-neon-cyan', 40)?></div>
        <h3 style="color: #ffffff; font-size: 1.2rem; font-weight: 800; margin-bottom: 6px;">Esplora la Mappa Mondiale</h3>
        <p style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.5; margin-bottom: 16px;">
          Visualizza i presidi sul globo terrestre in modalità interattiva 2D e 3D.
        </p>
        <a href="world-map.php" class="btn-rainbow-outline" style="border-color: var(--neon-cyan); color: #fff; width: 100%; display: inline-flex; justify-content: center; align-items: center;">
          <?=dx_icon('globe', 'text-neon-cyan', 16)?>
          <span style="margin-left: 8px;">Apri Mappa Mondiale Club</span>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 3. SECTION 3: NON SEI MAI STATO IN UN CLUB? (I 6 PASSI)        -->
<!-- ============================================================== -->
<section class="my-5" id="cosa-succede">
  <div class="text-center mb-4">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span style="color: #fde68a;">SENZA PAROLE DIFFICILI · COSA ASPETTARSI</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.8vw, 2.7rem); color: #ffffff; margin-top: 6px; font-weight: 800;">
      Non sei mai stato in un Club? <span class="text-rainbow">Cosa puoi aspettarti</span>
    </h2>
    <p style="color: #cbd5e1; max-width: 680px; margin: 0 auto; font-size: 1.05rem; line-height: 1.65;">
      La paura dell'ignoto è normale. Ti spieghiamo passo dopo passo cosa succede quando decidi di partecipare al tuo primo incontro.
    </p>
  </div>

  <!-- VIAGGIO IN 6 TAPPE CON 7 COLORI NEON CICLICI -->
  <div class="journey-stepper-grid">
    <div class="journey-step-card card-neon-cyan">
      <span class="step-num text-neon-cyan">01</span>
      <h3 class="step-title">Arrivi</h3>
      <p class="step-desc">
        Entri in una sala semplice, ospitata in un centro civico o parrocchiale. Nessuna sala d'attesa medica, nessun bancone burocratico.
      </p>
    </div>

    <div class="journey-step-card card-neon-green">
      <span class="step-num text-neon-green">02</span>
      <h3 class="step-title">Trovi altre persone</h3>
      <p class="step-desc">
        Incontri persone di ogni età, famiglie e compagni di cammino che hanno attraversato le tue stesse fatiche e ti accolgono con calore.
      </p>
    </div>

    <div class="journey-step-card card-neon-gold">
      <span class="step-num text-neon-gold">03</span>
      <h3 class="step-title">Ascolti</h3>
      <p class="step-desc">
        Ci si dispone in cerchio. Ciascuno racconta come è andata la settimana, i momenti sereni e le difficoltà quotidiane, senza filtri.
      </p>
    </div>

    <div class="journey-step-card card-neon-orange">
      <span class="step-num text-neon-orange">04</span>
      <h3 class="step-title">Parli quando te la senti</h3>
      <p class="step-desc">
        Nessuno ti interroga. Se al primo incontro preferisci restare in silenzio e ascoltare, sei liberissimo di farlo. I tuoi tempi sono rispettati.
      </p>
    </div>

    <div class="journey-step-card card-neon-indigo">
      <span class="step-num text-neon-indigo">05</span>
      <h3 class="step-title">Conosci la comunità</h3>
      <p class="step-desc">
        È presente un Servitore-Insegnante formato secondo il Metodo Hudolin che facilita la conversazione e garantisce riservatezza e rispetto.
      </p>
    </div>

    <div class="journey-step-card card-neon-violet">
      <span class="step-num text-neon-violet">06</span>
      <h3 class="step-title">Decidi tu il tuo passo</h3>
      <p class="step-desc">
        A fine incontro non firmi nulla e non paghi nulla. Sei tu a scegliere liberamente se tornare la settimana successiva.
      </p>
    </div>
  </div>

  <!-- FAQ RASSICURANTI IN LUXURY CARDS -->
  <div class="lux-metallic-card p-4 p-md-5 my-4" style="border: 1px solid rgba(212, 175, 55, 0.35);">
    <h3 style="font-family: var(--font-serif); color: #ffffff; font-size: 1.4rem; margin-bottom: 22px; display: flex; align-items: center; gap: 10px;">
      <?=dx_icon('help-circle', 'text-neon-gold', 24)?> 
      <span>Risposte ai dubbi più comuni</span>
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: 24px;">
      <div style="border-left: 3px solid var(--neon-cyan); padding-left: 14px;">
        <h4 style="color: #38bdf8; font-size: 1.05rem; font-weight: 750; margin-bottom: 6px;">Posso venire anche se non sono sicuro di smettere?</h4>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin: 0;">Sì. Il Club non richiede un esame d'ingresso. Puoi venire a sentire le esperienze degli altri per capire quale cammino desideri intraprendere.</p>
      </div>

      <div style="border-left: 3px solid var(--neon-green); padding-left: 14px;">
        <h4 style="color: #34d399; font-size: 1.05rem; font-weight: 750; margin-bottom: 6px;">Devo venire con la mia famiglia?</h4>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin: 0;">La famiglia è sempre benvenuta perché la serenità riguarda tutti. Ma puoi venire da solo, oppure la famiglia può venire anche senza di te se in questo momento non te la senti.</p>
      </div>

      <div style="border-left: 3px solid var(--neon-gold); padding-left: 14px;">
        <h4 style="color: #fde68a; font-size: 1.05rem; font-weight: 750; margin-bottom: 6px;">Quanto costa partecipare?</h4>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin: 0;">È completamente gratuito. I Club si fondano sull'auto-mutuo-aiuto e sulla solidarietà comunitaria.</p>
      </div>

      <div style="border-left: 3px solid var(--neon-violet); padding-left: 14px;">
        <h4 style="color: #e879f9; font-size: 1.05rem; font-weight: 750; margin-bottom: 6px;">Posso contattare qualcuno prima di andare?</h4>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin: 0;">Certamente. Puoi contattare la segreteria di accoglienza dalla pagina "Parla con Noi", scrivere via email a info@dependex.support o chiamare il Numero Verde AICAT 800 974250.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 4. SECTION 4: PARLA CON NOI (GATEWAY LASER RAINBOW)             -->
<!-- ============================================================== -->
<section class="talk-gateway-box text-center">
  <div class="badge-neon-rainbow mb-2">
    <span class="dot"></span>
    <span style="color: #fde68a;">NON RIMANERE NEL DUBBIO</span>
  </div>
  <h2 style="font-family: var(--font-serif); color: #ffffff; font-size: clamp(1.8rem, 3.8vw, 2.7rem); margin: 6px 0 10px; font-weight: 800;">
    <span class="text-rainbow">Parla con noi</span>
  </h2>
  <p style="color: #cbd5e1; max-width: 680px; margin: 0 auto 24px; font-size: 1.05rem; line-height: 1.65;">
    Hai una domanda specifica? Vuoi capire quale Club è più comodo per la tua famiglia? 
    Ti rispondiamo con garbo, riservatezza e senza alcuna pressione.
  </p>

  <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
    <a href="parla-con-noi.php" class="btn-rainbow-neon" style="box-shadow: var(--glow-cyan);">
      <?=dx_icon('message-circle', '', 20)?>
      <span style="margin-left: 8px;">Scrivi alla Segreteria di Accoglienza</span>
    </a>

    <a href="tel:800974250" class="btn-rainbow-neon" style="box-shadow: var(--glow-gold);">
      <?=dx_icon('phone', '', 18)?>
      <span style="margin-left: 8px;">Numero Verde AICAT: 800 974250</span>
    </a>

    <a href="parla-con-noi.php" class="btn-rainbow-outline" style="border-color: var(--neon-cyan); color: #fff;">
      <?=dx_icon('send', 'text-neon-cyan', 18)?>
      <span style="margin-left: 8px;">Invia un messaggio dal sito</span>
    </a>
  </div>
</section>

<!-- ============================================================== -->
<!-- 5. SECTION 5: VIVI LA COMUNITÀ (EVENTI & NOTIZIE)               -->
<!-- ============================================================== -->
<section class="my-5" id="vivi-comunita">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <div class="gold-glow-badge mb-2">
        <?=dx_icon('calendar', 'text-neon-gold', 14)?>
        <span>INCONTRI, CORSI, VITA DEI CLUB & HUB NAZIONALE DIPENDENZE</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: clamp(1.7rem, 3.5vw, 2.5rem); color: #ffffff; margin: 0; font-weight: 800;">
        <span class="text-rainbow">Vivi la comunità</span>
      </h2>
      <p style="color: #cbd5e1; margin: 6px 0 0; font-size: 0.98rem;">
        Il Club non è solo la riunione settimanale: è una rete viva di scambi, corsi e momenti di crescita condivisa.
      </p>
    </div>
    <div>
      <a href="events-public.php" class="btn-rainbow-outline small" style="border-color: var(--neon-cyan); color: #fff;">
        <span>Tutti gli eventi nazionali</span>
        <?=dx_icon('arrow-right', 'text-neon-cyan', 14)?>
      </a>
    </div>
  </div>

  <!-- EVENTO PRINCIPALE IN EVIDENZA: CORSO TAGLIO DI PO CON BORDO RAINBOW -->
  <div class="rainbow-border p-4 p-md-5 mb-4" style="background: rgba(12, 16, 28, 0.92);">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
          <span class="dot"></span>
          <span style="color:#fde68a;">CORSO ESPERIENZIALE PER FAMIGLIE & CONDUTTORI</span>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 3vw, 2rem); color: #ffffff; font-weight: 800; margin: 6px 0 10px;">
          A Scuola di Comunicazione e Resilienza — 1° Livello
        </h3>
        <p style="color: #cbd5e1; font-size: 1.02rem; line-height: 1.6; margin-bottom: 14px;">
          Tre giornate esperienziali a Taglio di Po (RO) condotte dal dott. <strong>Adelmo Di Salvatore</strong> (psichiatra e formatore Metodo Hudolin). Strumenti pratici per imparare a comunicare in famiglia senza litigare e non farsi schiacciare dai problemi altrui.
        </p>
        <div style="display: flex; gap: 14px; flex-wrap: wrap; font-size: 0.88rem; color: #fde68a; margin-bottom: 18px;">
          <span><?=dx_icon('calendar', '', 14)?> 9-10-11 Ottobre 2026</span>
          <span><?=dx_icon('map-pin', '', 14)?> Oratorio San Francesco d'Assisi, Taglio di Po (RO)</span>
          <span><?=dx_icon('users', '', 14)?> Max 30 posti · Quota solidale 10€ con pranzo</span>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="evento-ottobre-taglio-di-po.php" class="btn-rainbow-neon small">
            <?=dx_icon('ticket', '', 16)?>
            <span style="margin-left: 6px;">Scheda e Prenotazione Posto</span>
          </a>
          <a href="events-public.php" class="btn-rainbow-outline small" style="border-color: var(--neon-cyan); color: #fff;">
            <span>Dettagli Programma</span>
          </a>
        </div>
      </div>
      <div class="col-lg-4 text-center">
        <img src="assets/img/events/evento-ottobre-taglio-di-po.jpeg" alt="Locandina Corso Taglio di Po" style="max-width: 220px; width: 100%; border-radius: var(--dx-radius-md); box-shadow: var(--rainbow-glow); border: 2px solid rgba(255,215,0,0.4);">
      </div>
    </div>
  </div>

  <!-- COMPONENTE FAST CHECKOUT IMMEDIATO -->
  <?php require __DIR__ . '/templates/_event_fast_checkout.php'; ?>

  <!-- NEWS TICKER RAPIDO DALLA RETE -->
  <div class="dx-news-ticker-section">
    <?php if (!empty($newsCards)): ?>
      <div class="dx-ticker-header" style="margin-top: 1.5rem;">
        <h3 style="font-size: 1.05rem; color: #fff; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('newspaper', 'text-neon-gold', 18)?>
          <span>Notizie e Aggiornamenti dalle ACAT e dai Territori</span>
        </h3>
      </div>
      <div class="dx-ticker-wrapper" style="margin-top: 10px;">
        <div class="dx-ticker-track">
          <?php foreach (array_merge($newsCards, $newsCards) as $item): ?>
            <article class="dx-ticker-card">
              <div>
                <span class="dx-ticker-badge"><?=h($item['tag_label'])?></span>
                <h4 class="dx-ticker-title"><?=h($item['title'])?></h4>
                <p class="dx-ticker-desc"><?=h($item['summary'])?></p>
              </div>
              <div class="dx-ticker-meta">
                <span><?=dx_icon('calendar', '', 12)?> <?=h($item['published_date'])?></span>
                <a href="<?=h($item['source_url'])?>" target="_blank" rel="noopener" class="dx-ticker-link">
                  <?=h($item['source_name'])?> <?=dx_icon('external-link', '', 12)?>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ============================================================== -->
<!-- 6. SECTION 6: STORIE DI COMUNITÀ                               -->
<!-- ============================================================== -->
<section class="my-5" id="storie">
  <div class="text-center mb-4">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span style="color:#fde68a;">PERSONE · FAMIGLIE · CAMBIAMENTI</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.8vw, 2.7rem); color: #ffffff; margin-top: 6px; font-weight: 800;">
      <span class="text-rainbow">Storie di comunità</span>
    </h2>
    <p style="color: #cbd5e1; max-width: 680px; margin: 0 auto; font-size: 1.02rem; line-height: 1.65;">
      La sobrietà non è un'astratta vittoria della volontà solitaria: è il frutto di relazioni autentiche riscoperte nel tempo.
    </p>
  </div>

  <div class="story-card-grid">
    <article class="story-card card-neon-cyan">
      <div>
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">TESTIMONIANZA DIRETTA</div>
        <p class="story-card-quote">
          «Pensavo di essere l'unico a svegliarsi con quel peso sullo stomaco. Al Club ho trovato persone che mi hanno guardato negli occhi senza farmi sentire un fallito.»
        </p>
        <div class="story-card-timeline">
          <div><b>Prima:</b> Tentativi solitari, promesse infrante e isolamento a casa.</div>
          <div><b>Al Club:</b> Il sollievo di potersi sedere in cerchio e ascoltare senza l'obbligo di giustificarsi.</div>
          <div><b>Oggi:</b> Una ritrovata presenza con la famiglia e la gioia di accogliere i nuovi arrivati.</div>
        </div>
      </div>
      <div class="pt-3" style="border-top: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.84rem; color: #94a3b8;">Marco, 4 anni nel Club</span>
        <a href="storie.php" class="text-neon-cyan" style="font-size: 0.86rem; font-weight: 700; text-decoration: none;">Leggi la storia &rarr;</a>
      </div>
    </article>

    <article class="story-card card-neon-green">
      <div>
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">IL PUNTO DI VISTA FAMILIARE</div>
        <p class="story-card-quote">
          «Ero andata per capire come farlo smettere. Ho scoperto che il Club ha dato a me lo spazio per respirare e smettere di vivere nell'ansia costante.»
        </p>
        <div class="story-card-timeline" style="border-left-color: var(--neon-green);">
          <div><b>Prima:</b> Notte insonni, controllo maniacale e solitudine profonda.</div>
          <div><b>Al Club:</b> La scoperta che anche i familiari hanno diritto a ritrovare pace e serenità.</div>
          <div><b>Oggi:</b> Il dialogo è tornato a essere sincero e il cammino si fa insieme ogni settimana.</div>
        </div>
      </div>
      <div class="pt-3" style="border-top: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.84rem; color: #94a3b8;">Elena, familiare</span>
        <a href="storie.php" class="text-neon-green" style="font-size: 0.86rem; font-weight: 700; text-decoration: none;">Leggi la storia &rarr;</a>
      </div>
    </article>
  </div>

  <div class="text-center mt-3">
    <a href="storie.php" class="btn-rainbow-outline">
      <?=dx_icon('book-open', 'text-neon-gold', 16)?>
      <span style="margin-left: 8px;">Leggi tutte le storie di comunità</span>
    </a>
  </div>
</section>

<!-- ============================================================== -->
<!-- 7. SECTION 7: IMPARA (I 3 LIVELLI DEL METODO HUDOLIN)           -->
<!-- ============================================================== -->
<section class="my-5" id="impara">
  <div class="text-center mb-4">
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('book-open', 'text-neon-gold', 14)?>
      <span>CULTURA, SCIENZA & APPRENDIMENTO</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.8vw, 2.7rem); color: #ffffff; margin-top: 6px; font-weight: 800;">
      <span class="text-rainbow">Impara: l'Approccio Ecologico-Sociale</span>
    </h2>
    <p style="color: #cbd5e1; max-width: 680px; margin: 0 auto; font-size: 1.02rem; line-height: 1.65;">
      Il metodo fondato dal Prof. Vladimir Hudolin concepisce la dipendenza non come una colpa morale o una malattia biologica ineluttabile, ma come uno stile di vita che si trasforma nel sistema delle relazioni.
    </p>
  </div>

  <div class="learn-levels-grid">
    <!-- LIVELLO 1: SCOPRI -->
    <div class="learn-level-card card-neon-cyan">
      <div>
        <span class="level-tag text-neon-cyan">LIVELLO 1 · SCOPRI</span>
        <h3 style="font-family: var(--font-serif); color: #ffffff; font-size: 1.25rem; margin: 6px 0 10px;">
          Cos'è il Club e perché la Famiglia
        </h3>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
          I principi cardine dell'accoglienza: l'assenza di cartelle cliniche, il valore del cerchio multifamiliare e la gratuità della solidarietà.
        </p>
      </div>
      <a href="metodo.php#scopri" class="btn-rainbow-outline small" style="border-color: var(--neon-cyan); color: #fff;">
        <span>Scopri le basi</span> &rarr;
      </a>
    </div>

    <!-- LIVELLO 2: COMPRENDI -->
    <div class="learn-level-card card-neon-gold">
      <div>
        <span class="level-tag text-neon-gold">LIVELLO 2 · COMPRENDI</span>
        <h3 style="font-family: var(--font-serif); color: #ffffff; font-size: 1.25rem; margin: 6px 0 10px;">
          Vladimir Hudolin e la Rete
        </h3>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
          La storia, l'esperienza nei reparti ospedalieri e la scelta rivoluzionaria di portare la salute nella comunità e nelle case delle famiglie.
        </p>
      </div>
      <a href="metodo.php#hudolin" class="btn-rainbow-outline small" style="border-color: var(--neon-gold); color: #fff;">
        <span>Approfondisci il Metodo</span> &rarr;
      </a>
    </div>

    <!-- LIVELLO 3: FORMATI -->
    <div class="learn-level-card card-neon-violet">
      <div>
        <span class="level-tag text-neon-violet">LIVELLO 3 · FORMATI</span>
        <h3 style="font-family: var(--font-serif); color: #ffffff; font-size: 1.25rem; margin: 6px 0 10px;">
          Sovereign Academy & Servitori
        </h3>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
          Moduli di formazione permanente, corsi di sensibilizzazione e aggiornamento per chi desidera facilitare un Club come Servitore-Insegnante.
        </p>
      </div>
      <a href="academy-public.php" class="btn-rainbow-outline small" style="border-color: var(--neon-violet); color: #fff;">
        <span>Esplora l'Academy</span> &rarr;
      </a>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 8. SECTION 8: UNA RETE MONDIALE SOLIDALE                       -->
<!-- ============================================================== -->
<section class="lux-metallic-card my-5 p-4 p-md-5" style="border: 1px solid rgba(0, 212, 255, 0.35);">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <div class="gold-glow-badge mb-2">
        <?=dx_icon('globe', 'text-neon-gold', 14)?>
        <span>RETE FEDERATA ITALIANA & MONDIALE</span>
      </div>
      <h2 style="font-family: var(--font-serif); color: #ffffff; font-size: clamp(1.6rem, 3.2vw, 2.3rem); margin: 6px 0 12px; font-weight: 800;">
        <span class="text-rainbow">Una rete mondiale di solidarietà</span>
      </h2>
      <p style="color: #cbd5e1; font-size: 1.02rem; line-height: 1.65; margin-bottom: 14px;">
        I Club Alcologici Territoriali costituiscono un tessuto capillare presente in ogni regione d'Italia e in numerosi Paesi del mondo (collegati attraverso AICAT e WACAT).
      </p>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 14px; margin: 24px 0;">
        <div class="card p-3 text-center" style="background: rgba(9, 13, 26, 0.85); border-top: 3px solid var(--neon-cyan); box-shadow: var(--glow-cyan);">
          <b class="text-neon-cyan" style="font-size: 2.2rem; display: block; font-family: monospace;"><?=$totalNodes?></b>
          <span style="font-size: 0.82rem; color: #94a3b8; font-weight: 700;">Nodi Totali</span>
        </div>
        <div class="card p-3 text-center" style="background: rgba(9, 13, 26, 0.85); border-top: 3px solid var(--neon-green); box-shadow: var(--glow-green);">
          <b class="text-neon-green" style="font-size: 2.2rem; display: block; font-family: monospace;">100%</b>
          <span style="font-size: 0.82rem; color: #94a3b8; font-weight: 700;">Volontariato Solidale</span>
        </div>
        <div class="card p-3 text-center" style="background: rgba(9, 13, 26, 0.85); border-top: 3px solid var(--neon-gold); box-shadow: var(--glow-gold);">
          <b class="text-neon-gold" style="font-size: 2.2rem; display: block; font-family: monospace;">40+</b>
          <span style="font-size: 0.82rem; color: #94a3b8; font-weight: 700;">Anni di Cammino</span>
        </div>
      </div>
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="world-club-explorer.php" class="btn-rainbow-neon small">
          <?=dx_icon('map-pin', '', 16)?>
          <span style="margin-left: 6px;">Cerca nella Directory</span>
        </a>
        <a href="world-map.php" class="btn-rainbow-outline small" style="border-color: var(--neon-cyan); color: #fff;">
          <?=dx_icon('globe', 'text-neon-cyan', 16)?>
          <span style="margin-left: 6px;">Mappa dei Presidi</span>
        </a>
      </div>
    </div>
    <div class="col-lg-5 text-center">
      <div class="card p-4" style="background: rgba(10, 14, 25, 0.9); border: 1.5px solid rgba(0, 212, 255, 0.4); box-shadow: 0 0 25px rgba(0, 212, 255, 0.25);">
        <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Hai bisogno di assistenza o orientamento?</h3>
        <p style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Se non riesci a individuare il Club più vicino o vuoi parlare prima con un facilitatore della rete:
        </p>
        <a href="parla-con-noi.php" class="btn-rainbow-neon" style="width: 100%; display: inline-flex; justify-content: center; align-items: center;">
          <?=dx_icon('message-circle', '', 16)?>
          <span style="margin-left: 6px;">Contatta la Segreteria di Accoglienza</span>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 9. SECTION 9: RISORSE & APPROFONDIMENTI                        -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span style="color:#fde68a;">RISORSE EDUCATIVE & FORMATIVE</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.6rem, 3.5vw, 2.3rem); color: #ffffff; font-weight: 800;">
      <span class="text-rainbow">Approfondimenti per il cammino</span>
    </h2>
    <p style="color: #cbd5e1; max-width: 640px; margin: 0 auto; font-size: 0.98rem;">
      Materiali didattici, guide per la famiglia e letture di consolidamento personale.
    </p>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 20px;">
    <div class="card card-neon-green p-4" style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="color: var(--neon-green); margin-bottom: 8px;"><?=dx_icon('sparkles', 'text-neon-green', 32)?></div>
        <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 750; margin: 0 0 6px;">Guida Gratuita per la Famiglia</h3>
        <p style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Cosa dire e cosa non dire, come affrontare le prime serate difficili e come trovare aiuto anche prima che la persona sia pronta.
        </p>
      </div>
      <a href="guida-gratuita.php" class="btn-rainbow-outline small" style="border-color: var(--neon-green); color: #fff;">Scarica la Guida (PDF) &rarr;</a>
    </div>

    <div class="card card-neon-gold p-4" style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="color: var(--neon-gold); margin-bottom: 8px;"><?=dx_icon('book-open', 'text-neon-gold', 32)?></div>
        <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 750; margin: 0 0 6px;">Collana Didattica & Manuali KDP</h3>
        <p style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Diari dei primi 90 giorni, quaderni di dialogo familiare e manuali per Servitori-Insegnanti disponibili in formato digitale e cartaceo.
        </p>
      </div>
      <a href="offers.php" class="btn-rainbow-outline small" style="border-color: var(--neon-gold); color: #fff;">Consulta la Collana &rarr;</a>
    </div>

    <div class="card card-neon-cyan p-4" style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="color: var(--neon-cyan); margin-bottom: 8px;"><?=dx_icon('compass', 'text-neon-cyan', 32)?></div>
        <span class="badge mb-2" style="background: rgba(0,212,255,0.15); color: var(--neon-cyan); font-size: 0.72rem; font-weight: 800; border: 1px solid rgba(0,212,255,0.3); padding: 2px 8px; border-radius: 6px; display: inline-block;">BEWAY.LIFE x DEPENDEX</span>
        <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 750; margin: 0 0 6px;">Viaggi Esperienziali</h3>
        <p style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Percorsi residenziali e la <a href="crociera-benessere-masterclass.php" style="color: var(--neon-cyan); text-decoration: underline;">Crociera Benessere Masterclass</a> nel Mediterraneo per famiglie e conduttori in formula sobria.
        </p>
      </div>
      <a href="viaggi-esperienziali.php" class="btn-rainbow-outline small" style="border-color: var(--neon-cyan); color: #fff;">Scheda Informativa &rarr;</a>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 10. SECTION 10: CHIUSURA IDENTITARIA RAINBOW                   -->
<!-- ============================================================== -->
<section class="rainbow-border text-center my-5 p-5" style="background: rgba(10, 13, 24, 0.95); box-shadow: var(--rainbow-glow);">
  <h2 style="font-family: var(--font-serif); font-size: clamp(2.4rem, 5vw, 4rem); font-weight: 900; letter-spacing: 0.04em; margin-bottom: 10px;">
    <span class="text-rainbow">AL CLUB. COL CLUB.</span>
  </h2>
  <p style="color: #ffffff; font-weight: 800; font-size: 1.15rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 28px; text-shadow: 0 0 20px rgba(0,212,255,0.6);">
    Trova · Parla · Partecipa · Impara · Condividi
  </p>
  <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
    <a href="world-club-explorer.php" class="btn-rainbow-neon">
      <?=dx_icon('map-pin', '', 18)?>
      <span style="margin-left: 8px;">Trova il tuo Club</span>
    </a>
    <a href="parla-con-noi.php" class="btn-rainbow-outline" style="border-color: var(--neon-cyan); color: #fff;">
      <?=dx_icon('message-circle', 'text-neon-cyan', 18)?>
      <span style="margin-left: 8px;">Parla con Noi</span>
    </a>
  </div>
</section>

<?php require '_footer.php'; ?>