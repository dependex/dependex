<?php
require_once __DIR__.'/bootstrap.php';
$global = site_mode() === 'DEPENDEX';
$pageTitle = $global ? 'Global Community · Reclaim Life & Sovereignty' : 'Dalla dipendenza alla sovranità personale · Riconquista la tua lucidità';
$metaDesc = $global
  ? 'Reclaim your life, mental clarity, and family dignity. Over 542+ Clubs worldwide with the proven Hudolin ecological-social method and Cortex AI.'
  : 'Basta con la favola del "smetto quando voglio". Riconquista lucidità mentale, rispetto e serenità familiare con la rete di oltre 540 Club e il Metodo Hudolin.';
require '_header.php';

// Fetch 360° ACAT, ARCAT, SAT News for the CSS Ticker
$newsCards = AcatNewsService::getLatestCards(10);
?>

<!-- ============================================================== -->
<!-- HERO: PNL / NEUROLINGUISTICA & METALLIC WOW                    -->
<!-- ============================================================== -->
<section class="luxury-hero-card lux-metallic-card p-4 p-md-5 my-4">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <div class="gold-glow-badge mb-3">
        <?=dx_icon('sparkles', '', 16)?>
        <span><?= $global ? 'HUDOLIN METHOD · GLOBAL LIVING NETWORK' : 'METODO ECOLOGICO-SOCIALE HUDOLIN · RETE DEI 542 CLUB' ?></span>
      </div>

      <h1 style="font-family: var(--font-serif); font-size: clamp(2.2rem, 5vw, 3.8rem); font-weight: 800; line-height: 1.12; margin-bottom: 1.25rem; color: #FFFFFF;">
        <?= $global
          ? 'Stop telling yourself "I can quit anytime".<br><span class="gold-foil-text">Reclaim your sovereignty.</span>'
          : 'Basta raccontarti la favola del "smetto quando voglio".<br><span class="gold-foil-text">Riprenditi il comando della tua vita.</span>'
        ?>
      </h1>

      <p style="font-size: 1.15rem; line-height: 1.7; max-width: 700px; color: #d1d5db; margin-bottom: 1.75rem;">
        <?= $global
          ? 'Alcohol promised to take away your stress, but it charged you double: your mornings, your family’s trust, and your dignity. We are not here to lecture you. We are here to dismantle the mental illusion together.'
          : 'La dipendenza ti ha venduto l’illusione di rilassarti, ma ti ha addebitato il conto: notti insonni, liti a tavola, promesse infrante e quel velo di vergogna nello stomaco. Spoiler: non sei rotto e non sei una causa persa. Sei solo finito dentro un loop cognitivo che da soli non si vince. Nei 542 Club non trovi prediche: trovi chi quel trucco lo ha già smontato.'
        ?>
      </p>

      <div class="d-flex gap-3 flex-wrap align-items-center">
        <a href="world-club-explorer.php" class="btn primary" style="background: linear-gradient(135deg, #FFF2B2 0%, #D4AF37 50%, #996515 100%); color: #070709; font-weight: 800; border: 1px solid #FFF0B8; box-shadow: 0 8px 25px rgba(212,175,55,0.35); text-decoration: none; padding: 0 26px;">
          <?=dx_icon('map-pin', '', 18)?>
          <span style="margin-left: 8px;"><?= $global ? 'Find Your Nearest Club' : 'Trova una sedia nel Club più vicino' ?></span>
        </a>
        <a href="offers.php" class="btn" style="background: rgba(16,17,23,0.9); border: 1px solid rgba(212,175,55,0.4); color: #FFFFFF; font-weight: 700; text-decoration: none; padding: 0 22px;">
          <?=dx_icon('sparkles', '', 18)?>
          <span style="margin-left: 8px;"><?= $global ? 'Explore Value Protocol' : 'Protocollo M.A.G.I.C. & Percorsi' ?></span>
        </a>
        <a href="cortex.php" class="btn" style="background: transparent; border: 1px solid rgba(255,255,255,0.15); color: #D4AF37; font-weight: 700; text-decoration: none; padding: 0 20px;">
          <?=dx_icon('brain', '', 18)?>
          <span style="margin-left: 8px;"><?= $global ? 'Cortex AI 24/7' : 'Parla con Cortex AI (Anonimo)' ?></span>
        </a>
      </div>
    </div>

    <div class="col-lg-4 text-center d-none d-lg-block">
      <div class="lux-metallic-card p-4" style="border: 1px solid rgba(212,175,55,0.35);">
        <div style="color: #D4AF37; margin-bottom: 14px; display: flex; justify-content: center;">
          <?=dx_icon('shield-check', '', 54)?>
        </div>
        <h4 style="font-family: var(--font-serif); color: #FFFFFF; margin-bottom: 10px; font-weight: 800;">Zero Giudizio. Solo Lucidità.</h4>
        <p style="font-size: 0.9rem; color: #a1a1aa; line-height: 1.55; margin-bottom: 0;">
          Non sei un'etichetta clinica da marchiare. Sei una persona con un potenziale intatto che merita di rialzare la testa e farsi stimare di nuovo da chi ama.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- CSS NEWS TICKER (ACAT, CAT, ARCAT, AICAT ITALIA, MODULI SAT)   -->
<!-- ============================================================== -->
<section class="dx-news-ticker-section">
  <div class="dx-ticker-header">
    <h3>
      <?=dx_icon('newspaper', '', 22)?>
      <span>NOTIZIE DAL NETWORK ACAT · 360° DAL TERRITORIO</span>
    </h3>
    <a href="events-public.php" class="dx-ticker-link" style="font-size: 0.85rem;">
      Tutti gli eventi e moduli <?=dx_icon('arrow-right', '', 14)?>
    </a>
  </div>

  <div class="dx-ticker-wrapper" aria-label="News ticker ACAT e ARCAT">
    <div class="dx-ticker-track">
      <?php 
      // Double the array for seamless infinite looping
      $loopNews = array_merge($newsCards, $newsCards);
      foreach($loopNews as $item): 
      ?>
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
</section>

<!-- ============================================================== -->
<!-- TRUST STRIP · PURE GOLD, BLACK & WHITE                        -->
<!-- ============================================================== -->
<div class="trust-strip" style="background: #0B0C10; border: 1px solid rgba(212,175,55,0.22); border-radius: 20px; margin: 30px 0;">
  <div class="trust-item">
    <span class="trust-number gold-foil-text">542+</span>
    <span class="trust-label" style="color: #a1a1aa;"><?= $global ? 'Verified Clubs Worldwide' : 'Club Attivi nel Territorio' ?></span>
  </div>
  <div class="trust-item">
    <span class="trust-number gold-foil-text">40+</span>
    <span class="trust-label" style="color: #a1a1aa;"><?= $global ? 'Years of Solid Results' : 'Anni di Metodo Hudolin' ?></span>
  </div>
  <div class="trust-item">
    <span class="trust-number gold-foil-text">100%</span>
    <span class="trust-label" style="color: #a1a1aa;"><?= $global ? 'Anonymity & Dignity' : 'Riservatezza & Anonimato' ?></span>
  </div>
  <div class="trust-item">
    <span class="trust-number gold-foil-text">24/7</span>
    <span class="trust-label" style="color: #a1a1aa;"><?= $global ? 'Cortex Cognitive Brain' : 'Company Brain Attivo H24' ?></span>
  </div>
</div>

<!-- ============================================================== -->
<!-- BEFORE / AFTER TRANSFORMATION GRID (PNL REFRAMING)            -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('trending-up', '', 14)?>
      <span>IL REFRAMING: COSA CAMBIA DAL PRIMO GIORNO</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #FFFFFF; margin-top: 6px;">
      Dalla Trappola dell'Autogiustificazione alla Padronanza Mentale
    </h2>
    <p style="color: #9ca3af; max-width: 640px; margin: 0 auto; font-size: 1.05rem; line-height: 1.6;">
      Nessuno cerca una piattaforma per collezionare statistiche. La cerchi per spegnere l'ansia delle 18:00, smettere di mentire a chi ami e risvegliarti la mattina fiero di chi sei.
    </p>
  </div>

  <div class="transformation-grid">
    <!-- BEFORE -->
    <div class="transformation-card before lux-metallic-card" style="border-color: rgba(239, 68, 68, 0.35);">
      <span class="transformation-badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4);">
        <?=dx_icon('alert-triangle', '', 14)?> LA VECCHIA FAVOLA: DA SOLI NEL LOOP
      </span>
      <ul class="transformation-list" style="margin-top: 20px;">
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: #f87171; flex-shrink: 0;"><?=dx_icon('alert-triangle', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">La scusa del "controllo io":</strong> Raccontarsi che bere o giocare sia una libera scelta, mentre in realtà è un pilota automatico che prosciuga energia e autostima.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: #f87171; flex-shrink: 0;"><?=dx_icon('clock', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">L'ansia e la stanchezza cronica:</strong> Svegliarsi col batticuore, la nebbia nel cervello e il senso di colpa per quello che è sfuggito di mano la sera prima.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: #f87171; flex-shrink: 0;"><?=dx_icon('heart', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Tensioni e sguardi feriti a casa:</strong> I silenzi pesanti a tavola, la delusione negli occhi del partner o dei figli e la paura costante della prossima litigata.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start;">
          <span style="color: #f87171; flex-shrink: 0;"><?=dx_icon('lock', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Migliaia di euro buttati:</strong> Soldi bruciati in bottiglie, giocate compulsive o tentativi terapeutici solitari senza una comunità di riferimento.</div>
        </li>
      </ul>
    </div>

    <!-- AFTER -->
    <div class="transformation-card after lux-metallic-card" style="border-color: rgba(212, 175, 55, 0.5);">
      <span class="transformation-badge" style="background: rgba(212, 175, 55, 0.18); color: #D4AF37; border: 1px solid rgba(212, 175, 55, 0.5);">
        <?=dx_icon('crown', '', 14)?> CON DEPENDEX & LA RETE DEI CLUB
      </span>
      <ul class="transformation-list" style="margin-top: 20px;">
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: #D4AF37; flex-shrink: 0;"><?=dx_icon('brain', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Mente lucida come un diamante:</strong> Svegliarsi col pieno di energia, concentrati sul lavoro, pronti ad affrontare le sfide senza bisogno di anestetici chimici.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: #D4AF37; flex-shrink: 0;"><?=dx_icon('users', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">La protezione dei pari (542 Club):</strong> Sedersi in cerchio una volta a settimana dove nessuno ti giudica perché tutti conoscono la strada.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: #D4AF37; flex-shrink: 0;"><?=dx_icon('crown', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Rispetto e orgoglio familiare riconquistati:</strong> Tornare a essere la roccia della famiglia, una persona integra di cui andare profondamente fieri.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start;">
          <span style="color: #D4AF37; flex-shrink: 0;"><?=dx_icon('sparkles', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Company Brain Cortex 24/7:</strong> Intelligenza artificiale addestrata sull'approccio ecologico-sociale che ti orienta e supporta giorno e notte.</div>
        </li>
      </ul>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- THE 6 PSYCHOLOGICAL LEVERS (SUPER WOW LUXURY CARDS)            -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('award', '', 14)?>
      <span>LEVE DI POTERE PERSONALE</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF;">
      Perché Questo Percorso Funziona Senza Sforzi Inutili
    </h2>
    <p style="color: #a1a1aa; max-width: 620px; margin: 0 auto; font-size: 1rem;">
      Non serve una forza di volontà disumana. Serve una struttura intelligente che assorbe la fatica e toglie potere alla dipendenza.
    </p>
  </div>

  <div class="levers-grid">
    <div class="lever-card lux-metallic-card">
      <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('clock', '', 32)?></div>
      <h3 class="lever-title">Risparmi Anni di Tentativi a Vuoto</h3>
      <p class="lever-desc">Con il censimento globale trovi subito l'orario e il contatto del Club più vicino. Basta brancolare nel buio.</p>
    </div>
    <div class="lever-card lux-metallic-card">
      <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('shield-check', '', 32)?></div>
      <h3 class="lever-title">Risparmi Migliaia di Euro</h3>
      <p class="lever-desc">La rete dei Club territoriali è solidale e accessibile, distruggendo le speculazioni delle cliniche private a pagamento.</p>
    </div>
    <div class="lever-card lux-metallic-card">
      <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('brain', '', 32)?></div>
      <h3 class="lever-title">Disinneschi l'Ansia da Prestazione</h3>
      <p class="lever-desc">Un protocollo in 5 fasi collaudato da 40 anni che ti dice esattamente cosa fare, un giorno alla volta.</p>
    </div>
    <div class="lever-card lux-metallic-card">
      <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('crown', '', 32)?></div>
      <h3 class="lever-title">Da Persona Fragile a Guida Rispettata</h3>
      <p class="lever-desc">Con i moduli SAT e l'Academy puoi abilitarti come Servitore-Insegnante e trasformare la tua esperienza in risorsa per gli altri.</p>
    </div>
    <div class="lever-card lux-metallic-card">
      <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('heart-handshake', '', 32)?></div>
      <h3 class="lever-title">Pace e Unione nel Nucleo Familiare</h3>
      <p class="lever-desc">Il Metodo Hudolin coinvolge tutta la famiglia. La sofferenza condivisa diventa complicità e nuova serenità domestica.</p>
    </div>
    <div class="lever-card lux-metallic-card">
      <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('lock', '', 32)?></div>
      <h3 class="lever-title">Riservatezza & Anonimato Totale</h3>
      <p class="lever-desc">Nessun dato venduto a terzi, zero profilazione commerciale. Sovranità assoluta della tua privacy.</p>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- WORLD CLUB EXPLORER (INTERACTIVE MAP PREVIEW)                   -->
<!-- ============================================================== -->
<section class="card public-map-home lux-metallic-card my-5" style="border: 1px solid rgba(212,175,55,0.25); background: #0B0C10;">
  <div class="section-head compact p-4">
    <div>
      <div class="gold-glow-badge mb-2">
        <?=dx_icon('compass', '', 14)?>
        <span>WORLD CLUB EXPLORER · RETE APERTA</span>
      </div>
      <h2 style="font-family: var(--font-serif); color: #FFFFFF; margin-top: 4px;">Trova il tuo punto di ancoraggio nel mondo</h2>
      <p style="color: #9ca3af; margin-bottom: 0;">542 nodi territoriali attivi, indirizzi verificati, giorni di riunione e contatti diretti.</p>
    </div>
  </div>
  <div class="map-home-actions px-4 pb-3" style="display: flex; gap: 12px; flex-wrap: wrap;">
    <a class="btn primary" href="world-map.php" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">
      <?=dx_icon('compass', '', 18)?>
      <span style="margin-left: 8px;">Apri Mappa Mondiale 2D/3D</span>
    </a>
    <a class="btn" href="world-club-explorer.php" style="border-color: rgba(212,175,55,0.4); color: #FFFFFF;">
      <?=dx_icon('map-pin', '', 18)?>
      <span style="margin-left: 8px;">Cerca per Città o Regione</span>
    </a>
  </div>
  <div class="map-preview-shell">
    <iframe src="world-map.php?embed=1" title="DEPENDEX World Club Explorer" loading="lazy" style="border-radius: 0 0 var(--radius-md) var(--radius-md); border-top: 1px solid rgba(212,175,55,0.2);"></iframe>
  </div>
</section>

<!-- ============================================================== -->
<!-- SCALA VALORE M.A.G.I.C. OFFER BANNER                           -->
<!-- ============================================================== -->
<section class="luxury-hero-card lux-metallic-card p-4 p-md-5 my-5 text-center">
  <div class="gold-glow-badge mb-3">
    <?=dx_icon('diamond', '', 14)?>
    <span>OFFERTE IRRIFIUTABILI · SCALA VALORE M.A.G.I.C.</span>
  </div>
  <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #FFFFFF; margin-bottom: 0.75rem;">
    Non ti serve più tempo. Ti serve una decisione netta.<br>
    <span class="gold-foil-text">Scegli il tuo livello di accompagnamento.</span>
  </h2>
  <p style="color: #d1d5db; max-width: 660px; margin: 0 auto 1.75rem; font-size: 1.05rem; line-height: 1.65;">
    Dallo <strong>Starter Kit a € 27</strong> (con cassetta attrezzi primo giorno) fino al <strong>Protocollo Completo a € 497</strong> (valore reale € 2.588, con audit 1-a-1 e Garanzia Trasformazione o Rimborso Integrale). Nessun rischio, zero alibi.
  </p>
  <div class="d-flex justify-content-center gap-3 flex-wrap">
    <a href="offers.php" class="btn primary" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; padding: 0 28px; text-decoration: none;">
      <?=dx_icon('sparkles', '', 18)?>
      <span style="margin-left: 8px;">Esplora la Scala Valore Completa</span>
    </a>
    <a href="cortex.php" class="btn" style="border: 1px solid rgba(212,175,55,0.4); color: #FFFFFF; font-weight: 700; text-decoration: none; padding: 0 24px;">
      <?=dx_icon('brain', '', 18)?>
      <span style="margin-left: 8px;">Chiedi a Cortex AI</span>
    </a>
  </div>
</section>

<!-- ============================================================== -->
<!-- QUICK NAVIGATION TILES (PURE GOLD/BLACK/WHITE SVG)             -->
<!-- ============================================================== -->
<section class="bubble-grid my-4">
  <a class="bubble lux-metallic-card" href="world-club-explorer.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('compass', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;"><?=h(tr('club.find','Trova Club'))?></span>
  </a>
  <a class="bubble lux-metallic-card" href="help.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('shield', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;"><?=$global?'Assistance':'Aiuto Immediato'?></span>
  </a>
  <a class="bubble lux-metallic-card" href="metodo.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('feather', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Metodo Hudolin</span>
  </a>
  <a class="bubble lux-metallic-card" href="events-public.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('calendar', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Eventi & Moduli SAT</span>
  </a>
  <a class="bubble lux-metallic-card" href="academy-public.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('academic', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Academy</span>
  </a>
  <a class="bubble lux-metallic-card" href="privacy.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('lock', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Privacy & Sovranità</span>
  </a>
</section>

<?php require '_footer.php'; ?>