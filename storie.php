<?php
require_once __DIR__ . '/bootstrap.php';
$u = current_user();
$brand = site_brand();

$pageTitle = 'Storie di Comunità · Persone, Famiglie, Cambiamenti';
$metaDesc = 'Testimonianze reali di chi ha vissuto la difficoltà e ha trovato nel Club Alcologico Territoriale uno spazio di ascolto, dignità e rinascita familiare.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/storie.php';

$breadcrumbs = [
    'Home' => '/',
    'Storie di Comunità' => 'storie.php'
];

$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "CollectionPage",
    "name" => "Storie di Comunità · DEPENDEX",
    "description" => $metaDesc,
    "url" => $canonicalUrl
];

require '_header.php';
?>

<div class="container py-4" style="max-width: 1140px; margin: 0 auto; padding: 0 1rem;">

  <!-- HERO STORIE -->
  <section class="human-hero-card text-center" style="margin-top: 1rem;">
    <div class="badge-human mb-3">
      <span class="dot"></span>
      <span>ESPERIENZE VISSUTE · RISPETTO & ANONIMATO</span>
    </div>

    <h1 class="human-hero-title">
      Persone. Famiglie. Cambiamenti.<br>
      <span class="text-amber">La forza del rispecchiamento.</span>
    </h1>

    <p class="human-hero-desc mx-auto">
      Al Club non ci sono maestri che danno lezioni o prescrizioni dall'alto. Ci sono persone che hanno attraversato 
      la stessa nebbia e che si ascoltano a vicenda. Leggi le storie di chi ha fatto il primo passo.
    </p>

    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
      <a href="world-club-explorer.php" class="btn-community-primary">
        <?=dx_icon('map-pin', '', 18)?>
        <span>Trova un Club vicino a te</span>
      </a>
      <a href="parla-con-noi.php" class="btn-community-outline">
        <?=dx_icon('message-circle', '', 18)?>
        <span>Parla prima con noi</span>
      </a>
    </div>
  </section>

  <!-- NOTA ETICA & TRASPARENZA -->
  <div style="background: rgba(224, 169, 109, 0.08); border-left: 4px solid var(--dx-amber); border-radius: var(--dx-radius-md); padding: 14px 20px; margin-bottom: 2.5rem; font-size: 0.88rem; color: var(--dx-text-subtle); line-height: 1.55;">
    <b>Nota di trasparenza e rispetto:</b> Le seguenti testimonianze riflettono esperienze vissute all'interno dei Club Alcologici Territoriali. A tutela della riservatezza, i nomi e i luoghi specifici sono stati resi anonimi. I percorsi di sobrietà e cambiamento sono individuali e familiari: non promettiamo risultati miracolosi né sostituiamo l'assistenza medica in caso di patologie cliniche.
  </div>

  <!-- GRIGLIA DELLE STORIE SECONDO IL PROTOCOLLO NARRATIVO -->
  <div class="story-card-grid">

    <!-- STORIA 1: MARCO (LA PERSONA) -->
    <article class="story-card">
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
          <span class="badge-human" style="font-size: 0.75rem;">Marco, 46 anni</span>
          <span style="font-size: 0.8rem; color: var(--dx-text-muted);">Cammino di 4 anni</span>
        </div>

        <p class="story-card-quote">
          «Pensavo che smettere significasse rinchiudersi in una gabbia e non vivere più. Al Club ho capito che la vera gabbia era quella in cui mi rinchiudevo ogni sera con il bicchiere in mano.»
        </p>

        <div class="story-card-timeline">
          <div><b>Prima:</b> L'ansia costante delle 18:00, le promesse non mantenute e la paura di non farcela da solo.</div>
          <div><b>La svolta:</b> Una discussione dolorosa in famiglia ha fatto crollare l'alibi del "smetto quando voglio".</div>
          <div><b>Il primo incontro:</b> «Tremavo sulla soglia. Nessuno mi ha chiesto perché fossi lì o quanto bevessi. Mi hanno semplicemente detto: benvenuto, siediti con noi.»</div>
          <div><b>Oggi:</b> Una lucidità ritrovata nel lavoro e lo sguardo sereno dei miei figli a tavola.</div>
        </div>
      </div>

      <div style="margin-top: 14px; pt-3; border-top: 1px solid var(--dx-night-border-subtle); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.82rem; color: var(--dx-text-muted);">Club in Veneto</span>
        <a href="world-club-explorer.php" class="text-amber" style="font-size: 0.86rem; font-weight: 700; text-decoration: none;">Cerca Club territoriali &rarr;</a>
      </div>
    </article>

    <!-- STORIA 2: ELENA E ROBERTO (LA FAMIGLIA) -->
    <article class="story-card">
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
          <span class="badge-human emerald" style="font-size: 0.75rem;">Elena (moglie) e Roberto</span>
          <span style="font-size: 0.8rem; color: var(--dx-text-muted);">Cammino di 2 anni</span>
        </div>

        <p class="story-card-quote">
          «Ero convinta che il problema fosse solo suo e che io dovessi solo controllarlo. Al Club ho capito che soffriva tutta la famiglia e che la guarigione è un cammino che si fa insieme.»
        </p>

        <div class="story-card-timeline">
          <div><b>Prima:</b> Notte passate a controllare orari, toni di voce, sguardi. Una tensione continua tra le mura di casa.</div>
          <div><b>La svolta:</b> Elena ha deciso di andare al Club per la prima volta da sola, senza aspettare che Roberto si convincesse.</div>
          <div><b>Il primo incontro:</b> «Ho pianto per un'ora sentendo altre mogli e madri raccontare esattamente quello che provavo io. Non ero più sola.»</div>
          <div><b>Oggi:</b> Roberto ha iniziato a frequentare il Club dopo due mesi. Oggi camminano insieme, con sincerità e rispetto reciproco.</div>
        </div>
      </div>

      <div style="margin-top: 14px; pt-3; border-top: 1px solid var(--dx-night-border-subtle); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.82rem; color: var(--dx-text-muted);">Club in Emilia-Romagna</span>
        <a href="guida-gratuita.php" class="text-emerald" style="font-size: 0.86rem; font-weight: 700; text-decoration: none;">Guida per la Famiglia &rarr;</a>
      </div>
    </article>

    <!-- STORIA 3: GIOVANNI (IL SERVITORE INSEGNANTE) -->
    <article class="story-card">
      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
          <span class="badge-human sky" style="font-size: 0.75rem;">Giovanni, 58 anni</span>
          <span style="font-size: 0.8rem; color: var(--dx-text-muted);">Servitore-Insegnante da 12 anni</span>
        </div>

        <p class="story-card-quote">
          «Il Club mi ha restituito la dignità. Quando ho completato i miei primi anni di sobrietà ho sentito il bisogno di restituire ciò che avevo ricevuto, mettendomi al servizio del cerchio.»
        </p>

        <div class="story-card-timeline">
          <div><b>Prima:</b> Una vita frammentata dalla dipendenza e dalla sensazione di essere un fallimento irreparabile.</div>
          <div><b>La svolta:</b> L'incontro con il Metodo Hudolin e la scoperta che la propria sofferenza poteva trasformarsi in risorsa per gli altri.</div>
          <div><b>La formazione:</b> La partecipazione ai Corsi di Sensibilizzazione e ai moduli dell'Academy per diventare facilitatore volontario.</div>
          <div><b>Oggi:</b> Ogni giovedì sera apre la sala del Club, accoglie chi bussa per la prima volta e garantisce che tutti abbiano voce senza giudizio.</div>
        </div>
      </div>

      <div style="margin-top: 14px; pt-3; border-top: 1px solid var(--dx-night-border-subtle); display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.82rem; color: var(--dx-text-muted);">Club in Lombardia</span>
        <a href="academy-public.php" class="text-sky" style="font-size: 0.86rem; font-weight: 700; text-decoration: none;">Diventa Servitore-Insegnante &rarr;</a>
      </div>
    </article>

  </div>

  <!-- GATEWAY DI CHIUSURA: INIZIA IL TUO PASSO -->
  <section class="talk-gateway-box text-center" style="margin-top: 3rem;">
    <h3 style="font-family: var(--dx-font-serif); color: #ffffff; font-size: clamp(1.6rem, 3.5vw, 2.2rem); margin-bottom: 10px;">
      Ogni storia comincia con una semplice sedia.
    </h3>
    <p style="color: var(--dx-text-subtle); max-width: 680px; margin: 0 auto 20px; font-size: 1.02rem; line-height: 1.6;">
      Non serve una decisione definitiva oggi. Basta la curiosità di venire a vedere con i tuoi occhi. C'è sempre un posto libero nel Club del tuo territorio.
    </p>
    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
      <a href="world-club-explorer.php" class="btn-community-primary">
        <?=dx_icon('map-pin', '', 18)?>
        <span>Trova il Club più vicino</span>
      </a>
      <a href="parla-con-noi.php" class="btn-community-outline">
        <?=dx_icon('message-circle', '', 18)?>
        <span>Inizia una conversazione riservata</span>
      </a>
    </div>
  </section>

</div>

<?php require '_footer.php'; ?>
