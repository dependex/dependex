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
<!-- 1. HERO EMPATICA: "HAI BISOGNO DI PARLARNE?"                   -->
<!-- ============================================================== -->
<section class="human-hero-card my-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <div class="badge-human mb-3">
        <span class="dot"></span>
        <span>UNA COMUNITÀ MONDIALE DI PERSONE, FAMIGLIE E CLUB</span>
      </div>

      <h1 class="human-hero-title">
        Hai bisogno di parlarne?<br>
        <span class="text-amber">Non devi sapere già tutto. Puoi semplicemente iniziare.</span>
      </h1>

      <p class="human-hero-desc">
        Che tu stia vivendo una difficoltà legata all'alcol, ad altre sostanze, al gioco o a un momento di solitudine, o che tu sia un familiare che non sa più come aiutare chi ama: nei Club Alcologici Territoriali trovi persone che si incontrano ogni settimana per camminare insieme. Senza cartelle cliniche, senza costi, senza giudizio.
      </p>

      <div style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <a href="world-club-explorer.php" class="btn-community-primary" title="Cerca il Club più vicino alla tua zona">
          <?=dx_icon('map-pin', '', 18)?>
          <span>Trova il tuo Club</span>
        </a>
        <a href="parla-con-noi.php" class="btn-community-outline" title="Inizia una conversazione riservata">
          <?=dx_icon('message-circle', '', 18)?>
          <span>Parla con Noi</span>
        </a>
        <a href="#cosa-succede" class="btn-community-outline" style="border-color: rgba(224, 169, 109, 0.4);">
          <?=dx_icon('help-circle', 'text-amber', 18)?>
          <span>Cosa succede al Club?</span>
        </a>
      </div>
    </div>

    <div class="col-lg-4 text-center d-none d-lg-block">
      <div style="background: rgba(10, 15, 28, 0.9); border: 1px solid var(--dx-night-border); border-radius: var(--dx-radius-xl); padding: 32px 24px; box-shadow: var(--dx-shadow-soft);">
        <div style="margin-bottom: 18px; display: inline-flex; align-items: center; justify-content: center; width: 110px; height: 110px; border-radius: 50%; background: rgba(224, 169, 109, 0.12); border: 2px solid var(--dx-amber); box-shadow: 0 0 25px rgba(224,169,109,0.25);">
          <?=dx_icon('heart-handshake', 'text-amber', 56)?>
        </div>
        <h3 style="font-family: var(--dx-font-serif); color: #FFFFFF; font-size: 1.3rem; margin-bottom: 8px; font-weight: 800;">
          Porte Sempre Aperte
        </h3>
        <p style="font-size: 0.9rem; color: var(--dx-text-subtle); line-height: 1.6; margin: 0;">
          Non serve una ricetta medica né un'iscrizione formale. C'è una sedia pronta per te in ogni Club d'Italia e del mondo.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 2. SECTION 2: TROVA LA TUA COMUNITÀ (RICERCA & MAPPA)          -->
<!-- ============================================================== -->
<section class="community-search-container my-5" id="trova-club">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <div class="badge-human emerald mb-2">
        <span class="dot"></span>
        <span><?=$totalNodes?> NODI TERRITORIALI CENSITI & VERIFICATI</span>
      </div>
      <h2 style="font-family: var(--dx-font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #ffffff; margin: 6px 0 10px; font-weight: 800;">
        Trova la tua comunità
      </h2>
      <p style="color: var(--dx-text-subtle); font-size: 1.05rem; line-height: 1.6; margin: 0;">
        Cerca il Club più vicino a te. Puoi inserire la tua città, il CAP, la provincia o il territorio.
      </p>

      <form action="world-club-explorer.php" method="GET" class="community-search-form">
        <input type="text" 
               name="q" 
               required 
               placeholder="Inserisci la tua città o provincia (es. Rovigo, Milano, Napoli, Padova...)" 
               class="community-search-input"
               aria-label="Cerca Club per comune o provincia">
        <button type="submit" class="btn-community-primary" style="white-space: nowrap;">
          <?=dx_icon('search', '', 18)?>
          <span>Cerca Club</span>
        </button>
      </form>
      <small style="display: block; font-size: 0.82rem; color: var(--dx-text-muted); margin-top: 10px;">
        Nessun dato personale richiesto per la ricerca. Consultazione 100% libera e riservata.
      </small>
    </div>

    <div class="col-lg-5">
      <div style="background: rgba(9, 13, 26, 0.7); border: 1px solid var(--dx-night-border); border-radius: var(--dx-radius-lg); padding: 22px; text-align: center;">
        <div style="color: var(--dx-amber); margin-bottom: 8px;"><?=dx_icon('compass', '', 36)?></div>
        <h4 style="color: #ffffff; font-size: 1.15rem; font-weight: 800; margin-bottom: 6px;">Esplora la Mappa Mondiale</h4>
        <p style="color: var(--dx-text-subtle); font-size: 0.88rem; line-height: 1.5; margin-bottom: 16px;">
          Visualizza i presidi sul globo terrestre in modalità interattiva 2D e 3D.
        </p>
        <a href="world-map.php" class="btn-community-outline small" style="width: 100%;">
          <?=dx_icon('globe', '', 16)?>
          <span style="margin-left: 6px;">Apri Mappa Mondiale Club</span>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 3. SECTION 3: NON SEI MAI STATO IN UN CLUB?                    -->
<!-- ============================================================== -->
<section class="my-5" id="cosa-succede">
  <div class="text-center mb-4">
    <div class="badge-human mb-2">
      <span class="dot"></span>
      <span>SENZA PAROLE DIFFICILI · COSA ASPETTARSI</span>
    </div>
    <h2 style="font-family: var(--dx-font-serif); font-size: clamp(1.8rem, 3.8vw, 2.7rem); color: #ffffff; margin-top: 6px; font-weight: 800;">
      Non sei mai stato in un Club?
    </h2>
    <p style="color: var(--dx-text-subtle); max-width: 680px; margin: 0 auto; font-size: 1.05rem; line-height: 1.65;">
      La paura dell'ignoto è normale. Ti spieghiamo passo dopo passo cosa succede quando decidi di partecipare al tuo primo incontro.
    </p>
  </div>

  <!-- VIAGGIO IN 6 TAPPE -->
  <div class="journey-stepper-grid">
    <div class="journey-step-card">
      <span class="step-num">01</span>
      <h3 class="step-title">Arrivi</h3>
      <p class="step-desc">
        Entri in una sala semplice, ospitata in un centro civico o parrocchiale. Nessuna sala d'attesa medica, nessun bancone burocratico.
      </p>
    </div>

    <div class="journey-step-card">
      <span class="step-num">02</span>
      <h3 class="step-title">Trovi altre persone</h3>
      <p class="step-desc">
        Incontri persone di ogni età, famiglie e compagni di cammino che hanno attraversato le tue stesse fatiche e ti accolgono con calore.
      </p>
    </div>

    <div class="journey-step-card">
      <span class="step-num">03</span>
      <h3 class="step-title">Ascolti</h3>
      <p class="step-desc">
        Ci si dispone in cerchio. Ciascuno racconta come è andata la settimana, i momenti sereni e le difficoltà quotidiane, senza filtri.
      </p>
    </div>

    <div class="journey-step-card">
      <span class="step-num">04</span>
      <h3 class="step-title">Parli quando te la senti</h3>
      <p class="step-desc">
        Nessuno ti interroga. Se al primo incontro preferisci restare in silenzio e ascoltare, sei liberissimo di farlo. I tuoi tempi sono rispettati.
      </p>
    </div>

    <div class="journey-step-card">
      <span class="step-num">05</span>
      <h3 class="step-title">Conosci la comunità</h3>
      <p class="step-desc">
        È presente un Servitore-Insegnante formato secondo il Metodo Hudolin che facilita la conversazione e garantisce riservatezza e rispetto.
      </p>
    </div>

    <div class="journey-step-card">
      <span class="step-num">06</span>
      <h3 class="step-title">Decidi tu il tuo passo</h3>
      <p class="step-desc">
        A fine incontro non firmi nulla e non paghi nulla. Sei tu a scegliere liberamente se tornare la settimana successiva.
      </p>
    </div>
  </div>

  <!-- FAQ RASSICURANTI -->
  <div style="background: var(--dx-night-card); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-xl); padding: clamp(1.8rem, 3vw, 2.5rem); margin-top: 2rem;">
    <h3 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: 1.4rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
      <?=dx_icon('help-circle', 'text-amber', 22)?> Risposte ai dubbi più comuni
    </h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: 20px;">
      <div>
        <h4 style="color: var(--dx-amber); font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Posso venire anche se non sono sicuro di smettere?</h4>
        <p style="color: var(--dx-text-subtle); font-size: 0.9rem; line-height: 1.55; margin: 0;">Sì. Il Club non richiede un esame d'ingresso. Puoi venire a sentire le esperienze degli altri per capire quale cammino desideri intraprendere.</p>
      </div>

      <div>
        <h4 style="color: var(--dx-amber); font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Devo venire con la mia famiglia?</h4>
        <p style="color: var(--dx-text-subtle); font-size: 0.9rem; line-height: 1.55; margin: 0;">La famiglia è sempre benvenuta perché la serenità riguarda tutti. Ma puoi venire da solo, oppure la famiglia può venire anche senza di te se in questo momento non te la senti.</p>
      </div>

      <div>
        <h4 style="color: var(--dx-amber); font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Quanto costa partecipare?</h4>
        <p style="color: var(--dx-text-subtle); font-size: 0.9rem; line-height: 1.55; margin: 0;">È completamente gratuito. I Club si fondano sull'auto-mutuo-aiuto e sulla solidarietà comunitaria.</p>
      </div>

      <div>
        <h4 style="color: var(--dx-amber); font-size: 1rem; font-weight: 700; margin-bottom: 6px;">Posso contattare qualcuno prima di andare?</h4>
        <p style="color: var(--dx-text-subtle); font-size: 0.9rem; line-height: 1.55; margin: 0;">Certamente. Puoi scrivere su WhatsApp al 347 884 4271 per parlare con Grazia o chiamare il Numero Verde AICAT 800 974250.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 4. SECTION 4: PARLA CON NOI (GATEWAY DI CONVERSAZIONE)          -->
<!-- ============================================================== -->
<section class="talk-gateway-box text-center">
  <div class="badge-human mb-2">
    <span class="dot"></span>
    <span>NON RIMANERE NEL DUBBIO</span>
  </div>
  <h2 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: clamp(1.8rem, 3.8vw, 2.5rem); margin: 6px 0 10px; font-weight: 800;">
    Parla con noi
  </h2>
  <p style="color: var(--dx-text-subtle); max-width: 680px; margin: 0 auto 24px; font-size: 1.05rem; line-height: 1.65;">
    Hai una domanda specifica? Vuoi capire quale Club è più comodo per la tua famiglia? 
    Ti rispondiamo con garbo, riservatezza e senza alcuna pressione.
  </p>

  <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
    <a href="https://wa.me/393478844271?text=<?=urlencode('Buongiorno Grazia, vorrei informazioni riservate sui Club e sul Metodo.')?>" 
       target="_blank" rel="noopener" 
       class="btn-community-wa">
      <?=dx_icon('message-circle', '', 20)?>
      <span>Scrivi su WhatsApp a Grazia (347 884 4271)</span>
    </a>

    <a href="tel:800974250" class="btn-community-primary">
      <?=dx_icon('phone', '', 18)?>
      <span>Numero Verde AICAT: 800 974250</span>
    </a>

    <a href="parla-con-noi.php" class="btn-community-outline">
      <?=dx_icon('send', '', 18)?>
      <span>Invia un messaggio dal sito</span>
    </a>
  </div>
</section>

<!-- ============================================================== -->
<!-- 5. SECTION 5: VIVI LA COMUNITÀ (EVENTI & NOTIZIE)               -->
<!-- ============================================================== -->
<section class="my-5" id="vivi-comunita">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
      <div class="badge-human emerald mb-2">
        <span class="dot"></span>
        <span>INCONTRI, CORSI & VITA DEI CLUB</span>
      </div>
      <h2 style="font-family: var(--dx-font-serif); font-size: clamp(1.7rem, 3.5vw, 2.4rem); color: #ffffff; margin: 0; font-weight: 800;">
        Vivi la comunità
      </h2>
      <p style="color: var(--dx-text-subtle); margin: 6px 0 0; font-size: 0.98rem;">
        Il Club non è solo la riunione settimanale: è una rete viva di scambi, corsi e momenti di crescita condivisa.
      </p>
    </div>
    <div>
      <a href="events-public.php" class="btn-community-outline small">
        <span>Tutti gli eventi nazionali</span>
        <?=dx_icon('arrow-right', '', 14)?>
      </a>
    </div>
  </div>

  <!-- EVENTO PRINCIPALE IN EVIDENZA: CORSO TAGLIO DI PO -->
  <div class="card p-4 p-md-5 mb-4" style="background: var(--dx-night-card); border: 1.5px solid var(--dx-amber); border-radius: var(--dx-radius-xl); box-shadow: var(--dx-shadow-soft);">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="badge-human mb-2" style="font-size: 0.74rem;">
          <span class="dot"></span>
          <span>CORSO ESPERIENZIALE PER FAMIGLIE & CONDUTTORI</span>
        </div>
        <h3 style="font-family: var(--dx-font-serif); font-size: clamp(1.4rem, 3vw, 1.9rem); color: #ffffff; font-weight: 800; margin: 6px 0 10px;">
          A Scuola di Comunicazione e Resilienza — 1° Livello
        </h3>
        <p style="color: var(--dx-text-subtle); font-size: 1rem; line-height: 1.6; margin-bottom: 14px;">
          Tre giornate esperienziali a Taglio di Po (RO) condotte dal dott. <strong>Adelmo Di Salvatore</strong> (psichiatra e formatore Metodo Hudolin). Strumenti pratici per imparare a comunicare in famiglia senza litigare e non farsi schiacciare dai problemi altrui.
        </p>
        <div style="display: flex; gap: 14px; flex-wrap: wrap; font-size: 0.88rem; color: var(--dx-amber); margin-bottom: 16px;">
          <span><?=dx_icon('calendar', '', 14)?> 9-10-11 Ottobre 2026</span>
          <span><?=dx_icon('map-pin', '', 14)?> Oratorio San Francesco d'Assisi, Taglio di Po (RO)</span>
          <span><?=dx_icon('users', '', 14)?> Max 30 posti · Quota solidale 10€ con pranzo</span>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <a href="evento-ottobre-taglio-di-po.php" class="btn-community-primary small">
            <?=dx_icon('ticket', '', 16)?>
            <span>Scheda e Prenotazione Posto</span>
          </a>
          <a href="events-public.php" class="btn-community-outline small">
            <span>Dettagli Programma</span>
          </a>
        </div>
      </div>
      <div class="col-lg-4 text-center">
        <img src="assets/img/events/evento-ottobre-taglio-di-po.jpeg" alt="Locandina Corso Taglio di Po" style="max-width: 220px; width: 100%; border-radius: var(--dx-radius-md); box-shadow: var(--dx-shadow-soft); border: 1px solid rgba(255,255,255,0.15);">
      </div>
    </div>
  </div>

  <!-- NEWS TICKER RAPIDO DALLA RETE -->
  <?php if (!empty($newsCards)): ?>
    <div class="dx-ticker-header" style="margin-top: 1.5rem;">
      <h3 style="font-size: 1.05rem; color: #fff; display: flex; align-items: center; gap: 8px;">
        <?=dx_icon('newspaper', 'text-amber', 18)?>
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
</section>

<!-- ============================================================== -->
<!-- 6. SECTION 6: STORIE DI COMUNITÀ                               -->
<!-- ============================================================== -->
<section class="my-5" id="storie">
  <div class="text-center mb-4">
    <div class="badge-human mb-2">
      <span class="dot"></span>
      <span>PERSONE · FAMIGLIE · CAMBIAMENTI</span>
    </div>
    <h2 style="font-family: var(--dx-font-serif); font-size: clamp(1.8rem, 3.8vw, 2.6rem); color: #ffffff; margin-top: 6px; font-weight: 800;">
      Storie di comunità
    </h2>
    <p style="color: var(--dx-text-subtle); max-width: 680px; margin: 0 auto; font-size: 1.02rem; line-height: 1.65;">
      La sobrietà non è un'astratta vittoria della volontà solitaria: è il frutto di relazioni autentiche riscoperte nel tempo.
    </p>
  </div>

  <div class="story-card-grid">
    <article class="story-card">
      <div>
        <div class="badge-human mb-2" style="font-size: 0.74rem;">TESTIMONIANZA DIRETTA</div>
        <p class="story-card-quote">
          «Pensavo di essere l'unico a svegliarsi con quel peso sullo stomaco. Al Club ho trovato persone che mi hanno guardato negli occhi senza farmi sentire un fallito.»
        </p>
        <div class="story-card-timeline">
          <div><b>Prima:</b> Tentativi solitari, promesse infrante e isolamento a casa.</div>
          <div><b>Al Club:</b> Il sollievo di potersi sedere in cerchio e ascoltare senza l'obbligo di giustificarsi.</div>
          <div><b>Oggi:</b> Una ritrovata presenza con la famiglia e la gioia di accogliere i nuovi arrivati.</div>
        </div>
      </div>
      <div class="pt-3" style="border-top: 1px solid var(--dx-night-border-subtle); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.84rem; color: var(--dx-text-muted);">Marco, 4 anni nel Club</span>
        <a href="storie.php" class="text-amber" style="font-size: 0.86rem; font-weight: 700; text-decoration: none;">Leggi la storia &rarr;</a>
      </div>
    </article>

    <article class="story-card">
      <div>
        <div class="badge-human emerald mb-2" style="font-size: 0.74rem;">IL PUNTO DI VISTA FAMILIARE</div>
        <p class="story-card-quote">
          «Ero andata per capire come farlo smettere. Ho scoperto che il Club ha dato a me lo spazio per respirare e smettere di vivere nell'ansia costante.»
        </p>
        <div class="story-card-timeline">
          <div><b>Prima:</b> Notte insonni, controllo maniacale e solitudine profonda.</div>
          <div><b>Al Club:</b> La scoperta che anche i familiari hanno diritto a ritrovare pace e serenità.</div>
          <div><b>Oggi:</b> Il dialogo è tornato a essere sincero e il cammino si fa insieme ogni settimana.</div>
        </div>
      </div>
      <div class="pt-3" style="border-top: 1px solid var(--dx-night-border-subtle); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.84rem; color: var(--dx-text-muted);">Elena, familiare</span>
        <a href="storie.php" class="text-emerald" style="font-size: 0.86rem; font-weight: 700; text-decoration: none;">Leggi la storia &rarr;</a>
      </div>
    </article>
  </div>

  <div class="text-center mt-3">
    <a href="storie.php" class="btn-community-outline">
      <?=dx_icon('book-open', '', 16)?>
      <span>Leggi tutte le storie di comunità</span>
    </a>
  </div>
</section>

<!-- ============================================================== -->
<!-- 7. SECTION 7: IMPARA (I 3 LIVELLI DEL METODO HUDOLIN)           -->
<!-- ============================================================== -->
<section class="my-5" id="impara">
  <div class="text-center mb-4">
    <div class="badge-human mb-2">
      <span class="dot"></span>
      <span>CULTURA, SCIENZA & APPRENDIMENTO</span>
    </div>
    <h2 style="font-family: var(--dx-font-serif); font-size: clamp(1.8rem, 3.8vw, 2.6rem); color: #ffffff; margin-top: 6px; font-weight: 800;">
      Impara: l'Approccio Ecologico-Sociale
    </h2>
    <p style="color: var(--dx-text-subtle); max-width: 680px; margin: 0 auto; font-size: 1.02rem; line-height: 1.65;">
      Il metodo fondato dal Prof. Vladimir Hudolin concepisce la dipendenza non come una colpa morale o una malattia biologica ineluttabile, ma come uno stile di vita che si trasforma nel sistema delle relazioni.
    </p>
  </div>

  <div class="learn-levels-grid">
    <!-- LIVELLO 1: SCOPRI -->
    <div class="learn-level-card">
      <div>
        <span class="level-tag">LIVELLO 1 · SCOPRI</span>
        <h3 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: 1.25rem; margin: 6px 0 10px;">
          Cos'è il Club e perché la Famiglia
        </h3>
        <p style="color: var(--dx-text-subtle); font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
          I principi cardine dell'accoglienza: l'assenza di cartelle cliniche, il valore del cerchio multifamiliare e la gratuità della solidarietà.
        </p>
      </div>
      <a href="metodo.php#scopri" class="btn-community-outline small">
        <span>Scopri le basi</span> &rarr;
      </a>
    </div>

    <!-- LIVELLO 2: COMPRENDI -->
    <div class="learn-level-card">
      <div>
        <span class="level-tag">LIVELLO 2 · COMPRENDI</span>
        <h3 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: 1.25rem; margin: 6px 0 10px;">
          Vladimir Hudolin e la Rete
        </h3>
        <p style="color: var(--dx-text-subtle); font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
          La storia, l'esperienza nei reparti ospedalieri e la scelta rivoluzionaria di portare la salute nella comunità e nelle case delle famiglie.
        </p>
      </div>
      <a href="metodo.php#hudolin" class="btn-community-outline small">
        <span>Approfondisci il Metodo</span> &rarr;
      </a>
    </div>

    <!-- LIVELLO 3: FORMATI -->
    <div class="learn-level-card">
      <div>
        <span class="level-tag">LIVELLO 3 · FORMATI</span>
        <h3 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: 1.25rem; margin: 6px 0 10px;">
          Sovereign Academy & Servitori
        </h3>
        <p style="color: var(--dx-text-subtle); font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
          Moduli di formazione permanente, corsi di sensibilizzazione e aggiornamento per chi desidera facilitare un Club come Servitore-Insegnante.
        </p>
      </div>
      <a href="academy-public.php" class="btn-community-outline small">
        <span>Esplora l'Academy</span> &rarr;
      </a>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 8. SECTION 8: UNA RETE MONDIALE SOLIDALE                       -->
<!-- ============================================================== -->
<section class="my-5 p-4 p-md-5" style="background: var(--dx-night-card); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-xl);">
  <div class="row align-items-center g-4">
    <div class="col-lg-7">
      <div class="badge-human mb-2">
        <span class="dot"></span>
        <span>RETE FEDERATA ITALIANA & MONDIALE</span>
      </div>
      <h2 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: clamp(1.6rem, 3.2vw, 2.2rem); margin: 6px 0 12px; font-weight: 800;">
        Una rete mondiale di solidarietà
      </h2>
      <p style="color: var(--dx-text-subtle); font-size: 1rem; line-height: 1.65; margin-bottom: 14px;">
        I Club Alcologici Territoriali costituiscono un tessuto capillare presente in ogni regione d'Italia e in numerosi Paesi del mondo (collegati attraverso AICAT e WACAT).
      </p>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin: 20px 0;">
        <div style="background: rgba(9, 13, 26, 0.7); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-md); padding: 12px; text-align: center;">
          <b style="font-size: 1.4rem; color: var(--dx-amber); display: block;"><?=$totalNodes?></b>
          <span style="font-size: 0.8rem; color: var(--dx-text-muted);">Nodi Totali</span>
        </div>
        <div style="background: rgba(9, 13, 26, 0.7); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-md); padding: 12px; text-align: center;">
          <b style="font-size: 1.4rem; color: var(--dx-emerald); display: block;">100%</b>
          <span style="font-size: 0.8rem; color: var(--dx-text-muted);">Volontariato Solidale</span>
        </div>
        <div style="background: rgba(9, 13, 26, 0.7); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-md); padding: 12px; text-align: center;">
          <b style="font-size: 1.4rem; color: var(--dx-sky); display: block;">40+</b>
          <span style="font-size: 0.8rem; color: var(--dx-text-muted);">Anni di Cammino</span>
        </div>
      </div>
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="world-club-explorer.php" class="btn-community-primary small">
          <?=dx_icon('map-pin', '', 16)?>
          <span>Cerca nella Directory</span>
        </a>
        <a href="world-map.php" class="btn-community-outline small">
          <?=dx_icon('globe', '', 16)?>
          <span>Mappa dei Presidi</span>
        </a>
      </div>
    </div>
    <div class="col-lg-5 text-center">
      <div style="background: rgba(9, 13, 26, 0.9); border: 1px solid var(--dx-night-border); border-radius: var(--dx-radius-lg); padding: 24px;">
        <h4 style="color: #ffffff; font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;">Hai bisogno di assistenza o orientamento?</h4>
        <p style="color: var(--dx-text-subtle); font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Se non riesci a individuare il Club più vicino o vuoi parlare prima con un facilitatore della rete:
        </p>
        <a href="parla-con-noi.php" class="btn-community-primary" style="width: 100%;">
          <?=dx_icon('message-circle', '', 16)?>
          <span>Contatta la Segreteria di Accoglienza</span>
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
    <div class="badge-human mb-2">
      <span class="dot"></span>
      <span>RISORSE EDUCATIVE & FORMATIVE</span>
    </div>
    <h2 style="font-family: var(--dx-font-serif); font-size: clamp(1.6rem, 3.5vw, 2.2rem); color: #ffffff; font-weight: 800;">
      Approfondimenti per il cammino
    </h2>
    <p style="color: var(--dx-text-subtle); max-width: 640px; margin: 0 auto; font-size: 0.98rem;">
      Materiali didattici, guide per la famiglia e letture di consolidamento personale.
    </p>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 20px;">
    <div class="card p-4" style="background: var(--dx-night-card); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-lg); display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="color: var(--dx-amber); margin-bottom: 8px;"><?=dx_icon('sparkles', '', 28)?></div>
        <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 750; margin: 0 0 6px;">Guida Gratuita per la Famiglia</h3>
        <p style="color: var(--dx-text-subtle); font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Cosa dire e cosa non dire, come affrontare le prime serate difficili e come trovare aiuto anche prima che la persona sia pronta.
        </p>
      </div>
      <a href="guida-gratuita.php" class="btn-community-outline small">Scarica la Guida (PDF) &rarr;</a>
    </div>

    <div class="card p-4" style="background: var(--dx-night-card); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-lg); display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="color: var(--dx-emerald); margin-bottom: 8px;"><?=dx_icon('book-open', '', 28)?></div>
        <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 750; margin: 0 0 6px;">Collana Didattica & Manuali</h3>
        <p style="color: var(--dx-text-subtle); font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Diari dei primi 90 giorni, quaderni di dialogo familiare e manuali per Servitori-Insegnanti disponibili in formato digitale e cartaceo.
        </p>
      </div>
      <a href="offers.php" class="btn-community-outline small">Consulta la Collana &rarr;</a>
    </div>

    <div class="card p-4" style="background: var(--dx-night-card); border: 1px solid var(--dx-night-border-subtle); border-radius: var(--dx-radius-lg); display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="color: var(--dx-sky); margin-bottom: 8px;"><?=dx_icon('compass', '', 28)?></div>
        <h3 style="color: #ffffff; font-size: 1.15rem; font-weight: 750; margin: 0 0 6px;">Viaggi Esperienziali</h3>
        <p style="color: var(--dx-text-subtle); font-size: 0.88rem; line-height: 1.55; margin-bottom: 16px;">
          Percorsi residenziali di rigenerazione emotiva e relazione d'aiuto per famiglie e conduttori in formula analcolica.
        </p>
      </div>
      <a href="viaggi-esperienziali.php" class="btn-community-outline small">Scheda Informativa &rarr;</a>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- 10. SECTION 10: CHIUSURA IDENTITARIA                          -->
<!-- ============================================================== -->
<section class="text-center my-5 py-4" style="border-top: 1px solid var(--dx-night-border-subtle);">
  <div style="font-family: var(--dx-font-serif); font-size: clamp(1.5rem, 3.2vw, 2.2rem); color: #ffffff; font-weight: 900; letter-spacing: 0.04em; margin-bottom: 8px;">
    AL CLUB. COL CLUB.
  </div>
  <p style="color: var(--dx-amber); font-weight: 800; font-size: 1.05rem; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 24px;">
    Trova. Parla. Partecipa. Impara. Condividi.
  </p>
  <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
    <a href="world-club-explorer.php" class="btn-community-primary">
      <?=dx_icon('map-pin', '', 18)?>
      <span>Trova il tuo Club</span>
    </a>
    <a href="parla-con-noi.php" class="btn-community-outline">
      <?=dx_icon('message-circle', '', 18)?>
      <span>Parla con Noi</span>
    </a>
  </div>
</section>

<?php require '_footer.php'; ?>