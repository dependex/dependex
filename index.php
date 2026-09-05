<?php
require_once __DIR__.'/bootstrap.php';
$global = site_mode() === 'DEPENDEX';
$pageTitle = $global ? 'Global Community · Reclaim Life & Dignity' : 'Dalla dipendenza alla vita · Riconquista la tua serenità';
$metaDesc = $global
  ? 'You are not looking for an app. You are reclaiming your life, dignity, and relationships. Join 542+ Clubs worldwide with the proven Hudolin method and Cortex AI.'
  : 'Non stai cercando una semplice applicazione. Stai cercando di tornare a vivere davvero, senza vergogna né ansia. Oltre 540 Club, il Metodo Hudolin e Cortex AI al tuo fianco.';
require '_header.php';
?>

<!-- ============================================================== -->
<!-- HERO: THE DEEP EMOTIONAL TRANSFORMATION                         -->
<!-- ============================================================== -->
<section class="luxury-hero-card p-4 p-md-5 my-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <div class="d-inline-block px-3 py-1 mb-3 border rounded-pill" style="border-color: rgba(201,168,76,0.3); background: rgba(201,168,76,0.06);">
        <span style="font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; color: var(--color-gold); text-transform: uppercase;">
          ✦ <?= $global ? 'HUDOLIN METHOD · GLOBAL COMMUNITY NETWORK' : 'METODO HUDOLIN · COMUNITÀ, FAMIGLIE E CLUB TERRITORIALI' ?>
        </span>
      </div>

      <h1 style="font-family: var(--font-serif); font-size: clamp(2.2rem, 5vw, 3.8rem); font-weight: 700; line-height: 1.12; margin-bottom: 1.25rem;">
        <?= $global
          ? 'You are not looking for an app.<br>You are reclaiming <span class="gold-gradient-text">your life & dignity</span>.'
          : 'Non stai cercando un’applicazione.<br>Stai cercando di <span class="gold-gradient-text">tornare a vivere davvero</span>.'
        ?>
      </h1>

      <p class="text-muted mb-4" style="font-size: 1.15rem; line-height: 1.65; max-width: 680px;">
        <?= $global
          ? 'Stop the sleepless nights, guilt, and isolation. Connect immediately with over 540 Clubs, experienced facilitators, and Cortex Company Brain to protect yourself and those you love.'
          : 'Basta notti insonni con il nodo allo stomaco, promesse non mantenute e paura dello sguardo di chi ami. Oltre 540 Club accoglienti, facilitatori che non ti giudicano mai e l’assistente cognitivo Cortex sempre al tuo fianco.'
        ?>
      </p>

      <div class="d-flex gap-3 flex-wrap align-items-center">
        <a href="world-club-explorer.php" class="btn btn-warning fw-bold px-4 py-3" style="border-radius: 50px; background: linear-gradient(135deg, var(--color-gold-light), var(--color-gold)); color: #111; font-size: 1.05rem; box-shadow: 0 8px 24px rgba(201,168,76,0.3); text-decoration: none;">
          <?= $global ? 'Find a Club Tonight ➔' : 'Trova il Club più vicino a te stasera ➔' ?>
        </a>
        <a href="offers.php" class="btn btn-outline-light px-4 py-3" style="border-radius: 50px; font-weight: 600; text-decoration: none;">
          <?= $global ? 'View Value Ladder Offers' : 'Scopri la Scala Valore & Offerte' ?>
        </a>
        <a href="cortex.php" class="btn btn-link text-warning px-2" style="text-decoration: none; font-weight: 600;">
          💬 <?= $global ? 'Ask Cortex (Private & 24/7)' : 'Parla con Cortex (Anonimo & Immediato)' ?>
        </a>
      </div>
    </div>

    <div class="col-lg-4 text-center d-none d-lg-block">
      <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.4); border: 1px solid var(--color-surface-border);">
        <div style="font-size: 3.5rem; margin-bottom: 10px;">🛡️</div>
        <h4 style="font-family: var(--font-serif); color: var(--color-gold-light); margin-bottom: 8px;">Zero Giudizio. Zero Rischio.</h4>
        <p class="small text-muted mb-0">La persona non coincide con il problema. Il nostro compito è restituirti forza, strumenti e relazioni sane.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- TRUST STRIP · LEVERAGING SOCIAL PROOF & AUTHENTICITY          -->
<!-- ============================================================== -->
<div class="trust-strip">
  <div class="trust-item">
    <span class="trust-number">542+</span>
    <span class="trust-label"><?= $global ? 'Verified Clubs Globally' : 'Club Attivi nel Mondo' ?></span>
  </div>
  <div class="trust-item">
    <span class="trust-number">40+</span>
    <span class="trust-label"><?= $global ? 'Years Proven Methodology' : 'Anni di Metodo Hudolin' ?></span>
  </div>
  <div class="trust-item">
    <span class="trust-number">100%</span>
    <span class="trust-label"><?= $global ? 'Privacy & Data Sovereignty' : 'Riservatezza & Anonimato' ?></span>
  </div>
  <div class="trust-item">
    <span class="trust-number">24/7</span>
    <span class="trust-label"><?= $global ? 'AI Cognitive Brain Support' : 'Company Brain Attivo H24' ?></span>
  </div>
</div>

<!-- ============================================================== -->
<!-- BEFORE / AFTER TRANSFORMATION GRID                             -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <span style="font-size: 0.8rem; font-weight: 700; letter-spacing: 0.12em; color: var(--color-gold); text-transform: uppercase;">
      <?= $global ? 'THE DIFFERENCE IN YOUR DAILY LIFE' : 'LA TRASFORMAZIONE REALE NELLA TUA VITA' ?>
    </span>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-top: 6px;">
      <?= $global ? 'From Isolation to Strength & Peace of Mind' : 'Dall’Angoscia dell’Isolamento alla Forza della Serenità' ?>
    </h2>
    <p class="text-muted mx-auto" style="max-width: 620px;">
      <?= $global
        ? 'No one buys a recovery app for the features. You choose this because you want your mornings back, your family smiling, and your head held high.'
        : 'Nessuno cerca una piattaforma per i suoi moduli tecnici. La scegli perché vuoi tornare a svegliarti con il sorriso, senza l’ansia di cos’è successo ieri sera e con l’orgoglio di guardare tutti negli occhi.'
      ?>
    </p>
  </div>

  <div class="transformation-grid">
    <!-- BEFORE -->
    <div class="transformation-card before">
      <span class="transformation-badge">❌ SENZA UN METODO E UNA RETE VIVA</span>
      <ul class="transformation-list">
        <li>
          <span>💔</span>
          <div><strong>Il logorio mentale costante:</strong> La paura delle 18:00, il continuo promettersi "da domani basta" e il fallire di nuovo da soli.</div>
        </li>
        <li>
          <span>💸</span>
          <div><strong>Emorragia di denaro e tempo:</strong> Migliaia di euro spesi in tentativi isolati, cliniche a pagamento e percorsi che promettono miracoli senza comunità.</div>
        </li>
        <li>
          <span>😔</span>
          <div><strong>Vergogna e solitudine familiare:</strong> Tendersi a tavola, evitare lo sguardo dei figli o dei genitori, la sensazione di essere diventato un peso.</div>
        </li>
        <li>
          <span>🕳️</span>
          <div><strong>Nessun piano quando arriva la crisi:</strong> Essere in balia della tempesta emotiva senza sapere a chi telefonare o dove andare.</div>
        </li>
      </ul>
    </div>

    <!-- AFTER -->
    <div class="transformation-card after">
      <span class="transformation-badge">✨ CON DEPENDEX & IL METODO AL CLUB</span>
      <ul class="transformation-list">
        <li>
          <span>🧠</span>
          <div><strong>Pace mentale e lucidità:</strong> Svegliarsi ogni giorno sereni, con le energie piene e la mente libera dall’ossessione.</div>
        </li>
        <li>
          <span>🤝</span>
          <div><strong>Una comunità di pari che non ti giudica:</strong> 542 Club territoriali dove sei ascoltato e compreso istantaneamente da chi ci è già passato.</div>
        </li>
        <li>
          <span>👑</span>
          <div><strong>Riconquista del rispetto e dello status:</strong> Tornare a essere la persona affidabile, forte e stimata su cui la tua famiglia può contare.</div>
        </li>
        <li>
          <span>⚡</span>
          <div><strong>Company Brain Cortex 24/7 al tuo fianco:</strong> In qualsiasi istante, giorno e notte, hai una guida orientativa che ti ricorda i tuoi traguardi.</div>
        </li>
      </ul>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- THE 6 PSYCHOLOGICAL LEVERS (WHY IT WORKS)                      -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <h2 style="font-family: var(--font-serif); font-size: 2.2rem;">Perché Funziona Quando Tutto il Resto Ha Fallito</h2>
    <p class="text-muted mx-auto" style="max-width: 600px;">
      Non chiediamo forza di volontà sovrumana. Ti diamo una struttura collaudata che assorbe la fatica e rimuove ogni ostacolo.
    </p>
  </div>

  <div class="levers-grid">
    <div class="lever-card">
      <span class="lever-icon">⏰</span>
      <h3 class="lever-title">Risparmi Mesi di Ricerche</h3>
      <p class="lever-desc">Con il nostro censimento globale trovi subito l'orario, il giorno d'incontro e i contatti diretti del Club più vicino a casa tua.</p>
    </div>
    <div class="lever-card">
      <span class="lever-icon">💸</span>
      <h3 class="lever-title">Risparmi Migliaia di Euro</h3>
      <p class="lever-desc">La solidarietà dei Club e i nostri percorsi hanno costi simbolici o accessibili, azzerando le speculazioni delle cliniche private.</p>
    </div>
    <div class="lever-card">
      <span class="lever-icon">🧠</span>
      <h3 class="lever-title">Cancelli l'Ansia del Futuro</h3>
      <p class="lever-desc">Il protocollo in 5 fasi ti dice cosa fare lunedì, martedì e nei momenti critici. Non devi più improvvisare nulla.</p>
    </div>
    <div class="lever-card">
      <span class="lever-icon">👑</span>
      <h3 class="lever-title">Da Problema a Guida</h3>
      <p class="lever-desc">Attraverso l'Academy puoi formarti come Servitore-Insegnante e trasformare la tua esperienza passata in un dono per la società.</p>
    </div>
    <div class="lever-card">
      <span class="lever-icon">❤️</span>
      <h3 class="lever-title">Famiglie Riconciliate</h3>
      <p class="lever-desc">Il Metodo Hudolin coinvolge l'intero nucleo relazionale: non si cura solo l'individuo, si rigenerano i legami d'amore.</p>
    </div>
    <div class="lever-card">
      <span class="lever-icon">🛡️</span>
      <h3 class="lever-title">Nessun Rischio di Esposizione</h3>
      <p class="lever-desc">Nessuna condivisione a terzi, crittografia sicura e possibilità di partecipazione anonima nel pieno rispetto della tua dignità.</p>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- WORLD CLUB EXPLORER (INTERACTIVE PREVIEW)                       -->
<!-- ============================================================== -->
<section class="card public-map-home my-5" style="border: 1px solid var(--color-surface-border); background: var(--color-surface-card);">
  <div class="section-head compact p-4">
    <div>
      <span class="eyebrow" style="color: var(--color-gold);">WORLD CLUB EXPLORER · RETE APERTA</span>
      <h2 style="font-family: var(--font-serif); color: #fff;">Trova il tuo punto di ancoraggio nel mondo</h2>
      <p class="text-muted mb-0">Mappa interattiva 2D e 3D con 542 nodi territoriali verificati, orari di riunione e contatti pubblici.</p>
    </div>
  </div>
  <div class="map-home-actions px-4 pb-3">
    <a class="btn primary" href="world-map.php" style="background: var(--color-gold); color: #111; font-weight: 700;">Apri mappa mondiale 2D/3D</a>
    <a class="btn" href="global-network.php" style="border-color: rgba(255,255,255,0.2); color: #fff;">Esplora directory alfabetica</a>
  </div>
  <div class="map-preview-shell">
    <iframe src="world-map.php?embed=1" title="DEPENDEX World Club Explorer" loading="lazy" style="border-radius: 0 0 var(--radius-md) var(--radius-md);"></iframe>
  </div>
</section>

<?php require '_dependex-world-map.php'; ?>

<!-- ============================================================== -->
<!-- SCALA VALORE M.A.G.I.C. OFFER CALLOUT BANNER                   -->
<!-- ============================================================== -->
<section class="luxury-hero-card p-4 p-md-5 my-5 text-center">
  <div class="d-inline-block px-3 py-1 mb-2 border rounded-pill" style="border-color: rgba(201,168,76,0.3); background: rgba(201,168,76,0.06);">
    <span style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; color: var(--color-gold); text-transform: uppercase;">
      ✦ LA SCALA VALORE M.A.G.I.C. OFFER
    </span>
  </div>
  <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #fff; margin-bottom: 0.75rem;">
    Non sei a centinaia di follower di distanza.<br>Sei a <span class="gold-gradient-text">un’Offerta Irrifiutabile</span> di distanza.
  </h2>
  <p class="text-muted mx-auto mb-4" style="max-width: 640px; font-size: 1.05rem; line-height: 1.6;">
    Dal <strong>Kit Diagnostico Iniziale a € 27</strong> fino al <strong>Protocollo Completo con garanzia totale a € 497</strong> (valore reale € 2.588 con bonus chirurgici e supporto 1-a-1). Rischio azzerato, trasparenza assoluta.
  </p>
  <div class="d-flex justify-content-center gap-3 flex-wrap">
    <a href="offers.php" class="btn btn-warning fw-bold px-4 py-3" style="border-radius: 50px; background: linear-gradient(135deg, var(--color-gold-light), var(--color-gold)); color: #111; text-decoration: none; font-size: 1.05rem;">
      Esplora le Pricing Cards M.A.G.I.C. ➔
    </a>
    <a href="cortex.php" class="btn btn-outline-light px-4 py-3" style="border-radius: 50px; text-decoration: none; font-weight: 600;">
      Parla con Cortex Company Brain
    </a>
  </div>
</section>

<!-- ============================================================== -->
<!-- QUICK BUBBLE NAVIGATION                                        -->
<!-- ============================================================== -->
<section class="bubble-grid my-4">
  <a class="bubble green" href="world-club-explorer.php"><b>🌍</b><span><?=h(tr('club.find','Find a Club'))?></span></a>
  <a class="bubble blue" href="help.php"><b>💬</b><span><?=$global?'Talk / Help':'Voglio parlare'?></span></a>
  <a class="bubble violet" href="metodo.php"><b>✦</b><span>Metodo</span></a>
  <a class="bubble amber" href="events-public.php"><b>📅</b><span><?=h(tr('nav.events','Events'))?></span></a>
  <a class="bubble coral" href="academy-public.php"><b>🎓</b><span>Academy</span></a>
  <a class="bubble teal" href="privacy.php"><b>🔐</b><span>Privacy</span></a>
</section>

<?php require '_footer.php'; ?>