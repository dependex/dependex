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
<!-- 1. HERO EMPATICA: 4 PORTE D'INGRESSO & ABBATTIMENTO BARRIERE   -->
<!-- ============================================================== -->
<section class="human-hero-card my-4" style="background: linear-gradient(180deg, rgba(14, 20, 36, 0.95) 0%, rgba(9, 13, 26, 0.98) 100%); border: 1.5px solid rgba(255, 215, 0, 0.3); border-radius: 24px; padding: 2.5rem 1.8rem; box-shadow: 0 0 35px rgba(255, 215, 0, 0.08);">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <div class="badge-neon-rainbow mb-3">
        <span class="dot"></span>
        <span style="color: #fde68a;">UNA COMUNITÀ MONDIALE DI PERSONE, FAMIGLIE E CLUB</span>
      </div>

      <h1 class="human-hero-title" style="font-family: var(--font-serif); font-size: clamp(2.1rem, 4.2vw, 3.3rem); line-height: 1.15; font-weight: 800; color: #ffffff; margin-bottom: 1rem;">
        Non devi sapere già tutto.<br>
        <span class="text-rainbow">Puoi semplicemente fare una domanda.</span>
      </h1>

      <div class="p-3 mb-4" style="background: rgba(255, 255, 255, 0.03); border-left: 3px solid var(--neon-cyan); border-radius: 0 12px 12px 0;">
        <p style="font-size: 1.12rem; color: #f1f5f9; line-height: 1.6; margin: 0; font-weight: 500;">
          Non devi conoscere il Metodo Hudolin. Non devi sapere se questo è "il posto giusto".<br>
          <strong style="color: var(--neon-gold);">Non serve avere un'etichetta per cercare una comunità.</strong>
        </p>
      </div>

      <p class="human-hero-desc" style="font-size: 1.05rem; color: #cbd5e1; line-height: 1.7; margin-bottom: 1.8rem;">
        Non siamo qui per giudicare né per combattere: siamo qui per <strong>ritrovare possibilità</strong>, rimettere in movimento la propria vita, incontrare altre persone e famiglie e <strong>non affrontare tutto da soli</strong>. Un luogo caldo dove incontrarsi ogni settimana, gratuitamente e nel rispetto totale della tua riservatezza.
      </p>

      <!-- MANIFESTO IL PRIMO PASSO -->
      <div class="p-3 mb-3" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(6, 182, 212, 0.08)); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 14px;">
        <div style="font-size: 0.96rem; color: #e2e8f0; line-height: 1.6;">
          <strong style="color: var(--neon-green);"><?=dx_icon('check-circle', 'text-neon-green', 16)?> Non devi cambiare tutta la tua vita oggi.</strong>
          Puoi semplicemente: fare una domanda · trovare un Club · conoscere qualcuno · andare a un incontro. <em>Poi decidi tu.</em>
        </div>
      </div>

      <!-- PRIMARY ACTION CLUSTER (CTA-ENGINE & MOBILE-FIRST-UX) -->
      <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="world-club-explorer.php" 
           class="btn-rainbow-glow d-inline-flex align-items-center justify-content-center text-decoration-none"
           style="background: linear-gradient(135deg, #00f0ff, #0077ff); color: #070a12; font-weight: 850; font-size: 0.96rem; padding: 14px 22px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0, 240, 255, 0.35); min-height: 48px; gap: 8px;">
          <?=dx_icon('map-pin', '', 18)?>
          <span>Trova il Tuo Club (322+ in Italia)</span>
        </a>
        <a href="playground.php" 
           class="d-inline-flex align-items-center justify-content-center text-decoration-none"
           style="background: rgba(212, 175, 55, 0.12); border: 1.5px solid #d4af37; color: #ffd700; font-weight: 800; font-size: 0.94rem; padding: 14px 20px; border-radius: 14px; min-height: 48px; gap: 8px; backdrop-filter: blur(8px);">
          <?=dx_icon('sparkles', 'text-neon-gold', 18)?>
          <span>Entra nel Life Playground</span>
        </a>
      </div>

      <!-- TRUST & REASSURANCE STRIP (TRUST-DESIGN) -->
      <div class="row g-2 mb-2">
        <div class="col-6 col-md-3">
          <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 8px 10px; font-size: 0.78rem; color: #cbd5e1; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('shield-check', 'text-neon-cyan', 15)?>
            <span><strong>100% Gratuito</strong> e solidale</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 8px 10px; font-size: 0.78rem; color: #cbd5e1; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('lock', 'text-neon-gold', 15)?>
            <span><strong>Anonimato</strong> garantito</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 8px 10px; font-size: 0.78rem; color: #cbd5e1; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('users', 'text-neon-purple', 15)?>
            <span><strong>Famiglie & Pari</strong> accoglienti</span>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 8px 10px; font-size: 0.78rem; color: #cbd5e1; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('heart', 'text-neon-green', 15)?>
            <span><strong>Zero Giudizio</strong> morale</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4 text-center d-none d-lg-block">
      <div class="rainbow-border p-4" style="background: rgba(8, 11, 20, 0.92); backdrop-filter: blur(16px); text-align: center;">
        <div style="margin-bottom: 16px; display: inline-block;">
          <img src="assets/img/dependex-rainbow-badge.webp" alt="Sigillo Cosmico Dependex" style="width: 140px; height: 140px; border-radius: 50%; box-shadow: var(--rainbow-glow); border: 2px solid rgba(255,215,0,0.45); animation: rainbow-pulse 4s ease-in-out infinite;">
        </div>
        <h2 style="font-family: var(--font-serif); color: #FFFFFF; font-size: 1.25rem; margin-bottom: 8px; font-weight: 800;">
          <span class="text-rainbow">DEPENDEX è digitale. La comunità è reale.</span>
        </h2>
        <p style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.6; margin: 0;">
          Il nostro lavoro online serve solo a farti trovare persone, famiglie e Club accoglienti nel mondo reale. Dalla persona alla comunità.
        </p>
      </div>
    </div>
  </div>

  <!-- LE 4 PORTE D'INGRESSO PSICOLOGICHE -->
  <div style="margin-top: 1.8rem; padding-top: 1.8rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
    <div style="font-size: 0.82rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--neon-gold); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
      <?=dx_icon('compass', 'text-neon-gold', 16)?>
      <span>COSA STAI CERCANDO IN QUESTO MOMENTO? SCEGLI LA TUA PORTA</span>
    </div>

    <div class="row g-3">
      <!-- PORTA 1 -->
      <div class="col-sm-6 col-lg-3">
        <a href="parla-con-noi.php?porta=aiuto" 
           data-funnel-action="CLICK_PORTA_AIUTO" 
           data-funnel-stage="ORIENTATION" 
           data-funnel-porta="aiuto"
           class="h-100 p-3 text-decoration-none d-block" 
           style="background: rgba(239, 68, 68, 0.08); border: 1.5px solid rgba(239, 68, 68, 0.4); border-radius: 16px; transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 0.78rem; font-weight: 800; color: #f87171; letter-spacing: 0.05em; text-transform: uppercase;">01 · ASCOLTO</span>
            <?=dx_icon('heart', 'text-neon-red', 18)?>
          </div>
          <div style="font-size: 1.12rem; font-weight: 800; color: #ffffff; margin-bottom: 6px;">CERCO AIUTO</div>
          <div style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.5;">Non so da dove cominciare. Vorrei solo parlare con qualcuno che capisce.</div>
        </a>
      </div>

      <!-- PORTA 2 -->
      <div class="col-sm-6 col-lg-3">
        <a href="mappa-club.php" 
           data-funnel-action="CLICK_PORTA_CLUB" 
           data-funnel-stage="EXPLORATION" 
           data-funnel-porta="club"
           class="h-100 p-3 text-decoration-none d-block" 
           style="background: rgba(6, 182, 212, 0.08); border: 1.5px solid rgba(6, 182, 212, 0.4); border-radius: 16px; transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 0.78rem; font-weight: 800; color: #38bdf8; letter-spacing: 0.05em; text-transform: uppercase;">02 · TERRITORIO</span>
            <?=dx_icon('map-pin', 'text-neon-cyan', 18)?>
          </div>
          <div style="font-size: 1.12rem; font-weight: 800; color: #ffffff; margin-bottom: 6px;">CERCO UN CLUB</div>
          <div style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.5;">Voglio conoscere una comunità reale di persone e famiglie vicino a me.</div>
        </a>
      </div>

      <!-- PORTA 3 -->
      <div class="col-sm-6 col-lg-3">
        <a href="metodo.php" 
           data-funnel-action="CLICK_PORTA_CAPIRE" 
           data-funnel-stage="ORIENTATION" 
           data-funnel-porta="capire"
           class="h-100 p-3 text-decoration-none d-block" 
           style="background: rgba(234, 179, 8, 0.08); border: 1.5px solid rgba(234, 179, 8, 0.4); border-radius: 16px; transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 0.78rem; font-weight: 800; color: #fde047; letter-spacing: 0.05em; text-transform: uppercase;">03 · CHIAREZZA</span>
            <?=dx_icon('book-open', 'text-neon-gold', 18)?>
          </div>
          <div style="font-size: 1.12rem; font-weight: 800; color: #ffffff; margin-bottom: 6px;">CERCO DI CAPIRE</div>
          <div style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.5;">Voglio sapere con parole semplici come funziona e cosa accade nel cerchio.</div>
        </a>
      </div>

      <!-- PORTA 4 -->
      <div class="col-sm-6 col-lg-3">
        <a href="domande-frequenti.php" 
           data-funnel-action="CLICK_PORTA_PARTECIPARE" 
           data-funnel-stage="REASSURANCE" 
           data-funnel-porta="partecipare"
           class="h-100 p-3 text-decoration-none d-block" 
           style="background: rgba(16, 185, 129, 0.08); border: 1.5px solid rgba(16, 185, 129, 0.4); border-radius: 16px; transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 0.78rem; font-weight: 800; color: #6ee7b7; letter-spacing: 0.05em; text-transform: uppercase;">04 · COMUNITÀ</span>
            <?=dx_icon('users', 'text-neon-green', 18)?>
          </div>
          <div style="font-size: 1.12rem; font-weight: 800; color: #ffffff; margin-bottom: 6px;">VOGLIO PARTECIPARE</div>
          <div style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.5;">Voglio entrare nella comunità, fare una prova o iniziare a frequentare.</div>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- FIRMA UX: DA DOVE VUOI INIZIARE? (6 PERCORSI SU MISURA)        -->
<!-- ============================================================== -->
<section class="my-4 p-4" style="background: rgba(11, 15, 27, 0.88); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px;">
  <div class="text-center mb-3">
    <div style="font-size: 0.82rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--neon-cyan); margin-bottom: 4px;">
      NAVIGAZIONE INTUITIVA
    </div>
    <h2 style="font-family: var(--font-serif); font-size: 1.5rem; color: #ffffff; font-weight: 800; margin: 0;">
      Da dove vuoi iniziare? <span class="text-rainbow">Scegli tu il tuo punto di partenza</span>
    </h2>
  </div>

  <div class="row g-3 text-center">
    <div class="col-4 col-md-2">
      <a href="dashboard.php" 
         data-funnel-action="CLICK_PATH_ME" 
         data-funnel-stage="ORIENTATION"
         class="p-3 d-block text-decoration-none h-100" 
         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s ease;">
        <div style="margin-bottom: 6px;"><?=dx_icon('user', 'text-neon-gold', 24)?></div>
        <div style="font-weight: 700; font-size: 0.92rem; color: #fff;">Da me</div>
        <div style="font-size: 0.76rem; color: #94a3b8;">La mia vita & sobrietà</div>
      </a>
    </div>
    <div class="col-4 col-md-2">
      <a href="guida-gratuita.php" 
         data-funnel-action="CLICK_PATH_FAMIGLIA" 
         data-funnel-stage="REASSURANCE"
         data-funnel-porta="famiglia"
         class="p-3 d-block text-decoration-none h-100" 
         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s ease;">
        <div style="margin-bottom: 6px;"><?=dx_icon('heart-handshake', 'text-neon-cyan', 24)?></div>
        <div style="font-weight: 700; font-size: 0.92rem; color: #fff;">Dalla famiglia</div>
        <div style="font-size: 0.76rem; color: #94a3b8;">Come sostenersi</div>
      </a>
    </div>
    <div class="col-4 col-md-2">
      <a href="mappa-club.php" 
         data-funnel-action="CLICK_PATH_TERRITORIO" 
         data-funnel-stage="EXPLORATION"
         data-funnel-porta="club"
         class="p-3 d-block text-decoration-none h-100" 
         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s ease;">
        <div style="margin-bottom: 6px;"><?=dx_icon('map-pin', 'text-neon-green', 24)?></div>
        <div style="font-weight: 700; font-size: 0.92rem; color: #fff;">Dal territorio</div>
        <div style="font-size: 0.76rem; color: #94a3b8;">Mappa e sedi reali</div>
      </a>
    </div>
    <div class="col-4 col-md-2">
      <a href="world-club-explorer.php" 
         data-funnel-action="CLICK_PATH_CLUB" 
         data-funnel-stage="EXPLORATION"
         class="p-3 d-block text-decoration-none h-100" 
         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s ease;">
        <div style="margin-bottom: 6px;"><?=dx_icon('home', 'text-neon-orange', 24)?></div>
        <div style="font-weight: 700; font-size: 0.92rem; color: #fff;">Dal Club</div>
        <div style="font-size: 0.76rem; color: #94a3b8;">Cos'è il cerchio</div>
      </a>
    </div>
    <div class="col-4 col-md-2">
      <a href="metodo.php" 
         data-funnel-action="CLICK_PATH_COME_FUNZIONA" 
         data-funnel-stage="ORIENTATION"
         data-funnel-porta="capire"
         class="p-3 d-block text-decoration-none h-100" 
         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s ease;">
        <div style="margin-bottom: 6px;"><?=dx_icon('book-open', 'text-neon-indigo', 24)?></div>
        <div style="font-weight: 700; font-size: 0.92rem; color: #fff;">Come funziona</div>
        <div style="font-size: 0.76rem; color: #94a3b8;">Il Metodo Hudolin</div>
      </a>
    </div>
    <div class="col-4 col-md-2">
      <a href="world-map.php" 
         data-funnel-action="CLICK_PATH_RETE" 
         data-funnel-stage="EXPLORATION"
         class="p-3 d-block text-decoration-none h-100" 
         style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s ease;">
        <div style="margin-bottom: 6px;"><?=dx_icon('globe', 'text-neon-violet', 24)?></div>
        <div style="font-weight: 700; font-size: 0.92rem; color: #fff;">Dalla rete</div>
        <div style="font-size: 0.76rem; color: #94a3b8;">540+ nodi mondiali</div>
      </a>
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

  <!-- ============================================================== -->
  <!-- LE DOMANDE CHE FORSE TI VERGOGNI A FARE (RASSICURAZIONE REALE)  -->
  <!-- ============================================================== -->
  <div class="lux-metallic-card p-4 p-md-5 my-5" style="border: 1.5px solid rgba(212, 175, 55, 0.4); background: rgba(10, 14, 25, 0.95); border-radius: 20px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
      <div>
        <div style="font-size: 0.8rem; font-weight: 800; color: var(--neon-gold); letter-spacing: 0.08em; text-transform: uppercase;">
          NESSUN GIUDIZIO · RISPOSTE CHIARE
        </div>
        <h3 style="font-family: var(--font-serif); color: #ffffff; font-size: clamp(1.3rem, 2.5vw, 1.8rem); font-weight: 800; margin: 4px 0 0;">
          <span class="text-rainbow">Le domande che forse ti vergogni a fare</span>
        </h3>
      </div>
      <a href="domande-frequenti.php" class="btn-rainbow-outline" style="border-color: var(--neon-gold); color: #fff; font-size: 0.88rem;">
        <?=dx_icon('help-circle', 'text-neon-gold', 16)?>
        <span style="margin-left: 6px;">Vedi tutte le 11 risposte &rarr;</span>
      </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 310px), 1fr)); gap: 20px;">
      <div class="p-3" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-cyan); border-radius: 0 10px 10px 0;">
        <h4 style="color: #38bdf8; font-size: 1rem; font-weight: 750; margin-bottom: 6px;">&ldquo;E se beve solo mio marito o mia moglie?&rdquo;</h4>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">Il Club è per le famiglie. Puoi venire tu da solo/a: la serenità ritrovata da un familiare è spesso il primo faro che illumina anche chi in questo momento non se la sente.</p>
      </div>

      <div class="p-3" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-green); border-radius: 0 10px 10px 0;">
        <h4 style="color: #34d399; font-size: 1rem; font-weight: 750; margin-bottom: 6px;">&ldquo;E se vengo ma non me la sento di parlare?&rdquo;</h4>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">Nessuno ti forzerà mai. Puoi sederti, ascoltare le storie degli altri e prendere un tè. I tuoi silenzi saranno protetti e rispettati per tutto il tempo di cui hai bisogno.</p>
      </div>

      <div class="p-3" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-gold); border-radius: 0 10px 10px 0;">
        <h4 style="color: #fde68a; font-size: 1rem; font-weight: 750; margin-bottom: 6px;">&ldquo;E se ho già provato altre volte ed è andata male?&rdquo;</h4>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">Non sei un caso clinico e non teniamo registri dei fallimenti. Ogni cammino umano conosce ricadute e ripartenze. Al Club conta solo la possibilità di ricominciare insieme oggi.</p>
      </div>

      <div class="p-3" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-orange); border-radius: 0 10px 10px 0;">
        <h4 style="color: #fb923c; font-size: 1rem; font-weight: 750; margin-bottom: 6px;">&ldquo;E se mi vergogno che qualcuno mi veda?&rdquo;</h4>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">La riservatezza nel Club è assoluta. Tutti quelli che oggi vedi sorridere e parlare liberamente hanno provato lo stesso identico timore prima di varcare quella porta.</p>
      </div>

      <div class="p-3" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-indigo); border-radius: 0 10px 10px 0;">
        <h4 style="color: #818cf8; font-size: 1rem; font-weight: 750; margin-bottom: 6px;">&ldquo;E se penso di non avere un problema grave?&rdquo;</h4>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">Non serve toccare il fondo né definirsi "alcolisti". Se desideri chiarire le tue abitudini o alleggerire le tensioni quotidiane, il Club è uno spazio aperto di confronto vitale.</p>
      </div>

      <div class="p-3" style="background: rgba(255,255,255,0.02); border-left: 3px solid var(--neon-violet); border-radius: 0 10px 10px 0;">
        <h4 style="color: #e879f9; font-size: 1rem; font-weight: 750; margin-bottom: 6px;">&ldquo;Mi giudicheranno per quello che ho fatto?&rdquo;</h4>
        <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6; margin: 0;">Nel cerchio non esistono cattedre né giudici. Troverai solo persone che riconoscono la fragilità umana e che hanno scelto di camminare alla pari, con dignità e rispetto.</p>
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
<!-- TICKER RECENSIONI & TESTIMONIANZE VERE DAI CLUB               -->
<!-- ============================================================== -->
<?php require __DIR__ . '/templates/_reviews_ticker.php'; ?>

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
<!-- 9.1 WIDGET 1-TAP CLUB & APCAT LOCATOR (GEODESICO GPS)          -->
<!-- ============================================================== -->
<?php require __DIR__ . '/templates/_club_locator_widget.php'; ?>

<!-- ============================================================== -->
<!-- 10. SECTION 10: CHIUSURA IDENTITARIA RAINBOW                   -->
<!-- ============================================================== -->
<section class="rainbow-border text-center my-5 p-5" style="background: rgba(10, 13, 24, 0.95); box-shadow: var(--rainbow-glow); border-radius: 24px;">
  <div class="badge-neon-rainbow mb-3">
    <span class="dot"></span>
    <span style="color: #fde68a;">DALLA PERSONA ALLA COMUNITÀ</span>
  </div>
  <h2 style="font-family: var(--font-serif); font-size: clamp(2.4rem, 5vw, 4rem); font-weight: 900; letter-spacing: 0.04em; margin-bottom: 10px;">
    <span class="text-rainbow">AL CLUB. COL CLUB.</span>
  </h2>
  <p style="color: #ffffff; font-weight: 800; font-size: 1.15rem; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 12px; text-shadow: 0 0 20px rgba(0,212,255,0.6);">
    Non devi sapere già tutto. Trova una comunità. Parla. Partecipa. Impara. Condividi.
  </p>
  <p style="color: var(--neon-cyan); font-size: 1.05rem; font-weight: 600; margin-bottom: 28px;">
    Dal digitale al reale. Dalla persona alla comunità.
  </p>
  <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
    <a href="mappa-club.php" class="btn-rainbow-neon">
      <?=dx_icon('map-pin', '', 18)?>
      <span style="margin-left: 8px;">Trova il tuo Club</span>
    </a>
    <a href="domande-frequenti.php" class="btn-rainbow-outline" style="border-color: var(--neon-gold); color: #fff;">
      <?=dx_icon('help-circle', 'text-neon-gold', 18)?>
      <span style="margin-left: 8px;">Le risposte ai tuoi dubbi</span>
    </a>
    <a href="parla-con-noi.php" class="btn-rainbow-outline" style="border-color: var(--neon-cyan); color: #fff;">
      <?=dx_icon('message-circle', 'text-neon-cyan', 18)?>
      <span style="margin-left: 8px;">Parla con Noi</span>
    </a>
  </div>
</section>

<?php require '_footer.php'; ?>