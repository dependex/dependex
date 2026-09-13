<?php
require_once __DIR__.'/bootstrap.php';
$pageTitle = 'Dalla dipendenza alla sovranità personale · Riconquista la tua lucidità';
$metaDesc = 'Basta con la favola del "smetto quando voglio". Riconquista lucidità mentale, rispetto e serenità familiare con la rete di oltre 540 Club e il Metodo Hudolin.';
require '_header.php';

// Fetch 360° ACAT, ARCAT, SAT News for the CSS Ticker
$newsCards = AcatNewsService::getLatestCards(10);
?>
<!-- ============================================================== -->
<!-- HERO: COSMIC RAINBOW NEON HERO SECTION                         -->
<!-- ============================================================== -->
<section class="hero-cosmic-section rainbow-border p-4 p-md-5 my-4" style="border-radius: 24px;">
  <div class="row align-items-center g-4 position-relative" style="z-index: 2; width: 100%;">
    <div class="col-lg-8">
      <div class="badge-neon-rainbow mb-3">
        <span class="dot"></span>
        <span class="text-rainbow">APPROCCIO ECOLOGICO-SOCIALE HUDOLIN · 361 CLUB VERIFICATI</span>
      </div>

      <h1 style="font-family: var(--font-serif); font-size: clamp(2.3rem, 5.5vw, 4.1rem); font-weight: 900; line-height: 1.1; margin-bottom: 1.25rem; color: #FFFFFF; text-shadow: 0 4px 20px rgba(0,0,0,0.9);">
        Basta raccontarti la favola del "smetto quando voglio".<br>
        <span class="rainbow-text">Riconquista la tua sovranità personale.</span>
      </h1>

      <p style="font-size: 1.18rem; line-height: 1.7; max-width: 720px; color: #e2e8f0; margin-bottom: 2rem; text-shadow: 0 2px 10px rgba(0,0,0,0.8);">
        La dipendenza ti ha venduto l’illusione di rilassarti, ma ti ha addebitato il conto: notti insonni, liti a tavola, promesse infrante e quel velo di vergogna nello stomaco. Non sei rotto e non sei una causa persa: sei solo dentro un loop cognitivo. Nei 546 Club territoriali trovi persone e famiglie che hanno già smontato quel trucco.
      </p>

      <div class="d-flex gap-3 flex-wrap align-items-center">
        <a href="world-club-explorer.php" class="btn-rainbow-neon">
          <?=dx_icon('map-pin', '', 18)?>
          <span style="margin-left: 8px;">Trova una sedia nel Club più vicino</span>
        </a>
        <a href="guida-gratuita.php" class="btn-rainbow-outline" style="border-color: var(--neon-gold); color: #FFFFFF;">
          <?=dx_icon('sparkles', '', 18)?>
          <span style="margin-left: 8px;">Guida Gratuita Famiglia</span>
        </a>
        <a href="metodo.php" class="btn-rainbow-outline">
          <?=dx_icon('feather', '', 18)?>
          <span style="margin-left: 8px;">Lo Schema dei 5 Passi</span>
        </a>
        <a href="offers.php" class="btn-rainbow-outline" style="border-color: var(--neon-cyan);">
          <?=dx_icon('book-open', '', 18)?>
          <span style="margin-left: 8px;">Libri Amazon KDP</span>
        </a>
      </div>
    </div>

    <div class="col-lg-4 text-center d-none d-lg-block">
      <div class="rainbow-border p-4" style="background: rgba(8, 11, 20, 0.85); backdrop-filter: blur(16px); text-align: center;">
        <div style="margin-bottom: 18px; display: inline-block;">
          <img src="assets/img/dependex-rainbow-badge.jpg" alt="Sigillo Cosmico Dependex" style="width: 170px; height: 170px; border-radius: 50%; box-shadow: var(--rainbow-glow); border: 2px solid rgba(255,215,0,0.4);">
        </div>
        <h4 style="font-family: var(--font-serif); color: #FFFFFF; margin-bottom: 8px; font-weight: 800; font-size: 1.25rem;">
          <span class="text-rainbow">Zero Giudizio. Solo Presenza.</span>
        </h4>
        <p style="font-size: 0.88rem; color: #cbd5e1; line-height: 1.55; margin-bottom: 0;">
          Nessuna etichetta clinica. Sei un essere umano con un potenziale intatto pronto a riprendersi stima, rispetto e serenità.
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
  <img src="assets/img/rainbow-portals.jpg" alt="I 7 Portali dell'Arcobaleno e Frequenze di Rinascita" loading="lazy">
</div>

<!-- ============================================================== -->
<!-- QUICK LEAD CAPTURE & CLUB FINDER BOX                           -->
<!-- ============================================================== -->
<section class="rainbow-border p-4 my-4" style="background: rgba(13, 18, 31, 0.85); backdrop-filter: blur(16px);">
  <div class="row align-items-center g-3">
    <div class="col-lg-6">
      <div style="color: #D4AF37; font-size: 0.8rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;">
        <?=dx_icon('map-pin', '', 14)?> ORIENTAMENTO RISERVATO · 100% GRATUITO
      </div>
      <h3 style="color: #FFFFFF; font-size: 1.35rem; margin: 4px 0 8px 0; font-family: var(--font-serif);">
        Ricevi i 3 Club territoriali più vicini e la Guida del 1° Giorno
      </h3>
      <p style="color: #cbd5e1; font-size: 0.92rem; margin: 0; line-height: 1.6;">
        Inserisci la tua città e la tua email: ti invieremo l'indirizzo esatto, il giorno di riunione e la checklist delle 7 azioni per non restare solo.
      </p>
    </div>
    <div class="col-lg-6">
      <form action="lead.php?magnet=club" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="text" name="citta" required placeholder="La tua città o provincia..." style="flex: 1; min-width: 170px; padding: 12px 14px; background: rgba(14, 18, 28, 0.9); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 10px; color: #fff; font-size: 0.95rem;">
        <input type="email" name="email" required placeholder="La tua email riservata..." style="flex: 1.2; min-width: 200px; padding: 12px 14px; background: rgba(14, 18, 28, 0.9); border: 1px solid rgba(0, 212, 255, 0.3); border-radius: 10px; color: #fff; font-size: 0.95rem;">
        <button type="submit" class="btn primary" style="white-space: nowrap; font-size: 0.95rem; min-height: 48px; border-radius: 12px; padding: 0 20px;">
          Invia i 3 Club
        </button>
      </form>
      <small style="display: block; font-size: 0.78rem; color: #cbd5e1; margin-top: 6px;">
        Nessun archivio pubblico. Riservatezza assoluta. Disiscrizione garantita in 1 click.
      </small>
    </div>
  </div>
</section>
<!-- ============================================================== -->
<!-- CSS NEWS TICKER: HUB NAZIONALE DIPENDENZE (ACAT, SER.D, COMUNITÀ, GAP) -->
<!-- ============================================================== -->
<section class="dx-news-ticker-section my-4">
  <div class="dx-ticker-header">
    <h3>
      <?=dx_icon('newspaper', 'text-neon-cyan', 22)?>
      <span>HUB NAZIONALE DIPENDENZE · EVENTI & NOTIZIE D'ITALIA</span>
    </h3>
    <a href="events-public.php" class="dx-ticker-link" style="font-size: 0.85rem;">
      Tutti gli eventi e congressi nazionali <?=dx_icon('arrow-right', '', 14)?>
    </a>
  </div>

  <div class="dx-ticker-wrapper" aria-label="News ticker Hub Nazionale Dipendenze">
    <div class="dx-ticker-track">
      <?php 
      // Double the array for seamless infinite looping
      $loopNews = array_merge($newsCards, $newsCards);
      foreach($loopNews as $item): 
        $isPinned = !empty($item['is_pinned']);
      ?>
        <article class="dx-ticker-card" style="<?=$isPinned ? 'border: 2px solid #d4af37; background: rgba(212,175,55,0.12); box-shadow: 0 0 20px rgba(212,175,55,0.25);' : ''?>">
          <div>
            <span class="dx-ticker-badge" style="<?=$isPinned ? 'background: #d4af37; color: #030712; font-weight: 900;' : ''?>"><?=h($item['tag_label'])?></span>
            <h4 class="dx-ticker-title" style="<?=$isPinned ? 'color: #fef08a;' : ''?>"><?=h($item['title'])?></h4>
            <p class="dx-ticker-desc"><?=h($item['summary'])?></p>
          </div>
          <div class="dx-ticker-meta">
            <span><?=dx_icon('calendar', '', 12)?> <?=h($item['published_date'])?></span>
            <a href="<?=h($item['source_url'])?>" target="<?=$isPinned ? '_self' : '_blank'?>" rel="noopener" class="dx-ticker-link" style="<?=$isPinned ? 'color: #fef08a; font-weight: 850;' : ''?>">
              <?=h($item['source_name'])?> <?=dx_icon('external-link', '', 12)?>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- EVENTO UFFICIALE FLAGSHIP & FAST CHECKOUT (TAGLIO DI PO)        -->
<!-- ============================================================== -->
<?php require_once __DIR__ . '/templates/_event_fast_checkout.php'; ?>

<!-- ============================================================== -->
<!-- TRUST STRIP · PURE GOLD, BLACK & WHITE                        -->
<!-- ============================================================== -->
<!-- TRUST NUMBERS STRIP                                            -->
<!-- ============================================================== -->
<div class="trust-strip rainbow-border p-3" style="background: rgba(11, 12, 16, 0.85); margin: 30px 0;">
  <div class="trust-item">
    <span class="trust-number text-neon-gold">361+</span>
    <span class="trust-label" style="color: #cbd5e1;">Club Attivi & Verificati in Italia</span>
  </div>
  <div class="trust-item">
    <span class="trust-number text-neon-green">40+</span>
    <span class="trust-label" style="color: #cbd5e1;">Anni di Metodo Hudolin</span>
  </div>
  <div class="trust-item">
    <span class="trust-number text-neon-cyan">100%</span>
    <span class="trust-label" style="color: #cbd5e1;">Riservatezza & Anonimato</span>
  </div>
  <div class="trust-item">
    <span class="trust-number text-neon-violet">24/7</span>
    <span class="trust-label" style="color: #cbd5e1;">Cortex AI & Rete Attiva H24</span>
  </div>
</div>

<!-- ============================================================== -->
<!-- BEFORE / AFTER TRANSFORMATION GRID (PNL REFRAMING)            -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">IL REFRAMING: COSA CAMBIA DAL PRIMO GIORNO</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #FFFFFF; margin-top: 6px;">
      Dalla Trappola dell'Autogiustificazione alla <span class="text-rainbow">Padronanza Mentale</span>
    </h2>
    <p style="color: #cbd5e1; max-width: 640px; margin: 0 auto; font-size: 1.05rem; line-height: 1.6;">
      Nessuno cerca una piattaforma per collezionare statistiche. La cerchi per spegnere l'ansia delle 18:00, smettere di mentire a chi ami e risvegliarti la mattina fiero di chi sei.
    </p>
  </div>

  <div class="transformation-grid">
    <!-- BEFORE -->
    <div class="transformation-card before card-neon-red" style="background: rgba(18, 12, 16, 0.85); border-radius: 18px; padding: 28px; border: 1px solid rgba(255, 51, 68, 0.35);">
      <span class="transformation-badge" style="background: rgba(255, 51, 68, 0.15); color: #ff3344; border: 1px solid rgba(255, 51, 68, 0.4); font-weight: 800;">
        <?=dx_icon('alert-triangle', '', 14)?> LA VECCHIA FAVOLA: DA SOLI NEL LOOP
      </span>
      <ul class="transformation-list" style="margin-top: 20px; list-style: none; padding-left: 0;">
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: var(--neon-red); flex-shrink: 0;"><?=dx_icon('alert-triangle', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">La scusa del "controllo io":</strong> Raccontarsi che bere o giocare sia una libera scelta, mentre in realtà è un pilota automatico che prosciuga energia e autostima.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: var(--neon-red); flex-shrink: 0;"><?=dx_icon('clock', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">L'ansia e la stanchezza cronica:</strong> Svegliarsi col batticuore, la nebbia nel cervello e il senso di colpa per quello che è sfuggito di mano la sera prima.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: var(--neon-red); flex-shrink: 0;"><?=dx_icon('heart', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Tensioni e sguardi feriti a casa:</strong> I silenzi pesanti a tavola, la delusione negli occhi del partner o dei figli e la paura costante della prossima litigata.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start;">
          <span style="color: var(--neon-red); flex-shrink: 0;"><?=dx_icon('lock', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Migliaia di euro buttati:</strong> Soldi bruciati in bottiglie, giocate compulsive o tentativi terapeutici solitari senza una comunità di riferimento.</div>
        </li>
      </ul>
    </div>

    <!-- AFTER -->
    <div class="transformation-card after card-neon-green" style="background: rgba(10, 20, 16, 0.85); border-radius: 18px; padding: 28px; border: 1px solid rgba(0, 255, 119, 0.35);">
      <span class="transformation-badge" style="background: rgba(0, 255, 119, 0.15); color: #00ff77; border: 1px solid rgba(0, 255, 119, 0.4); font-weight: 800;">
        <?=dx_icon('crown', '', 14)?> CON DEPENDEX & LA RETE DEI CLUB
      </span>
      <ul class="transformation-list" style="margin-top: 20px; list-style: none; padding-left: 0;">
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: var(--neon-green); flex-shrink: 0;"><?=dx_icon('brain', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Mente lucida come un diamante:</strong> Svegliarsi col pieno di energia, concentrati sul lavoro, pronti ad affrontare le sfide senza bisogno di anestetici chimici.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: var(--neon-green); flex-shrink: 0;"><?=dx_icon('users', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">La protezione dei pari (546 Club):</strong> Sedersi in cerchio una volta a settimana dove nessuno ti giudica perché tutti conoscono la strada.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
          <span style="color: var(--neon-green); flex-shrink: 0;"><?=dx_icon('crown', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Rispetto e orgoglio familiare riconquistati:</strong> Tornare a essere la roccia della famiglia, una persona integra di cui andare profondamente fieri.</div>
        </li>
        <li style="display: flex; gap: 14px; align-items: flex-start;">
          <span style="color: var(--neon-green); flex-shrink: 0;"><?=dx_icon('sparkles', '', 20)?></span>
          <div><strong style="color: #FFFFFF;">Company Brain Cortex 24/7:</strong> Intelligenza artificiale addestrata sull'approccio ecologico-sociale che ti orienta e supporta giorno e notte.</div>
        </li>
      </ul>
    </div>
  </div>

  <!-- BANNER LEAD MAGNET GUIDA GRATUITA FAMIGLIA -->
  <div class="lux-metallic-card p-4 p-md-5 mt-4 text-center" style="background: rgba(12, 16, 28, 0.95); border: 2px solid var(--neon-gold); border-radius: 20px; box-shadow: 0 0 30px rgba(212,175,55,0.15);">
    <div class="badge-neon-rainbow mb-2" style="font-size: 0.72rem;">
      <span class="dot"></span>
      <span class="text-rainbow">RISORSA GRATUITA PER LA FAMIGLIA · DOWNLOAD IMMEDIATO</span>
    </div>
    <h3 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 3vw, 1.9rem); color: #FFFFFF; margin: 8px 0 12px;">
      Non sai come parlare a chi ami o hai paura della prossima crisi?
    </h3>
    <p style="color: #cbd5e1; max-width: 680px; margin: 0 auto 20px; font-size: 1rem; line-height: 1.6;">
      Scarica subito la guida pratica <strong>"I Primi 7 Giorni"</strong>: cosa non dire mai stasera, come superare la negazione e come la famiglia può farsi aiutare nei Club anche da sola.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="guida-gratuita.php" class="btn primary" style="padding: 12px 28px; text-decoration: none; border-radius: 12px;">
        <?=dx_icon('sparkles', '', 18)?>
        <span style="margin-left: 8px;">Scarica Gratis la Guida (PDF)</span>
      </a>
      <a href="guida-gratuita.php?view=document" target="_blank" class="btn-rainbow-outline" style="padding: 12px 24px; text-decoration: none; border-radius: 12px;">
        <?=dx_icon('book-open', '', 18)?>
        <span style="margin-left: 8px;">Leggi Anteprima Senza Iscrizione</span>
      </a>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- THE 6 PSYCHOLOGICAL LEVERS (SUPER WOW LUXURY NEON CARDS)       -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">LEVE DI POTERE PERSONALE</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF;">
      Perché Questo Percorso Funziona Senza Sforzi Inutili
    </h2>
    <p style="color: #cbd5e1; max-width: 620px; margin: 0 auto; font-size: 1rem;">
      Non serve una forza di volontà disumana. Serve una struttura intelligente che assorbe la fatica e toglie potere alla dipendenza.
    </p>
  </div>

  <div class="levers-grid">
    <div class="lever-card card-neon-red p-4" style="background: rgba(14, 16, 26, 0.85); border-radius: 16px;">
      <div style="color: var(--neon-red); margin-bottom: 12px;"><?=dx_icon('clock', '', 32)?></div>
      <h3 class="lever-title" style="color: #FFFFFF;">Risparmi Anni di Tentativi a Vuoto</h3>
      <p class="lever-desc" style="color: #cbd5e1;">Con il censimento globale trovi subito l'orario e il contatto del Club più vicino. Basta brancolare nel buio.</p>
    </div>
    <div class="lever-card card-neon-orange p-4" style="background: rgba(14, 16, 26, 0.85); border-radius: 16px;">
      <div style="color: var(--neon-orange); margin-bottom: 12px;"><?=dx_icon('shield-check', '', 32)?></div>
      <h3 class="lever-title" style="color: #FFFFFF;">Risparmi Migliaia di Euro</h3>
      <p class="lever-desc" style="color: #cbd5e1;">La rete dei Club territoriali è solidale e accessibile, distruggendo le speculazioni delle cliniche private a pagamento.</p>
    </div>
    <div class="lever-card card-neon-gold p-4" style="background: rgba(14, 16, 26, 0.85); border-radius: 16px;">
      <div style="color: var(--neon-gold); margin-bottom: 12px;"><?=dx_icon('brain', '', 32)?></div>
      <h3 class="lever-title" style="color: #FFFFFF;">Disinneschi l'Ansia da Prestazione</h3>
      <p class="lever-desc" style="color: #cbd5e1;">Un protocollo in 5 fasi collaudato da 40 anni che ti dice esattamente cosa fare, un giorno alla volta.</p>
    </div>
    <div class="lever-card card-neon-green p-4" style="background: rgba(14, 16, 26, 0.85); border-radius: 16px;">
      <div style="color: var(--neon-green); margin-bottom: 12px;"><?=dx_icon('crown', '', 32)?></div>
      <h3 class="lever-title" style="color: #FFFFFF;">Da Persona Fragile a Guida Rispettata</h3>
      <p class="lever-desc" style="color: #cbd5e1;">Con i moduli SAT e l'Academy puoi abilitarti come Servitore-Insegnante e trasformare la tua esperienza in risorsa per gli altri.</p>
    </div>
    <div class="lever-card card-neon-cyan p-4" style="background: rgba(14, 16, 26, 0.85); border-radius: 16px;">
      <div style="color: var(--neon-cyan); margin-bottom: 12px;"><?=dx_icon('heart-handshake', '', 32)?></div>
      <h3 class="lever-title" style="color: #FFFFFF;">Pace e Unione nel Nucleo Familiare</h3>
      <p class="lever-desc" style="color: #cbd5e1;">Il Metodo Hudolin coinvolge tutta la famiglia. La sofferenza condivisa diventa complicità e nuova serenità domestica.</p>
    </div>
    <div class="lever-card card-neon-violet p-4" style="background: rgba(14, 16, 26, 0.85); border-radius: 16px;">
      <div style="color: var(--neon-violet); margin-bottom: 12px;"><?=dx_icon('lock', '', 32)?></div>
      <h3 class="lever-title" style="color: #FFFFFF;">Riservatezza & Anonimato Totale</h3>
      <p class="lever-desc" style="color: #cbd5e1;">Nessun dato venduto a terzi, zero profilazione commerciale. Sovranità assoluta della tua privacy.</p>
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
      <p style="color: #cbd5e1; margin-bottom: 0;">542 nodi territoriali attivi, indirizzi verificati, giorni di riunione e contatti diretti.</p>
    </div>
  </div>
  <div class="map-home-actions px-4 pb-3" style="display: flex; gap: 12px; flex-wrap: wrap;">
    <a class="btn primary" href="world-map.php">
      <?=dx_icon('compass', '', 18)?>
      <span style="margin-left: 8px;">Apri Mappa Mondiale 2D/3D</span>
    </a>
    <a class="btn-rainbow-outline" href="world-club-explorer.php">
      <?=dx_icon('map-pin', '', 18)?>
      <span style="margin-left: 8px;">Cerca per Città o Regione</span>
    </a>
  </div>
  <div class="map-preview-shell">
    <iframe src="world-map.php?embed=1" title="DEPENDEX World Club Explorer" loading="lazy" style="border-radius: 0 0 var(--radius-md) var(--radius-md); border-top: 1px solid rgba(212,175,55,0.2);"></iframe>
  </div>
</section>

<!-- ============================================================== -->
<!-- SOLIDARIETÀ & VOLONTARIATO DEI CLUB                            -->
<!-- ============================================================== -->
<section class="luxury-hero-card lux-metallic-card p-4 p-md-5 my-5 text-center" style="border: 1px solid rgba(212,175,55,0.35); background: #0B0C10;">
  <div class="gold-glow-badge mb-3">
    <?=dx_icon('shield-check', '', 14)?>
    <span>100% VOLONTARIATO SOCIALE · GRATUITO · SOLIDARIETÀ MULTIFAMILIARE</span>
  </div>
  <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.6rem); color: #FFFFFF; margin-bottom: 0.75rem;">
    Nei Club non si compra la guarigione.<br>
    <span class="gold-foil-text">Si cammina insieme, senza spendere un solo centesimo.</span>
  </h2>
  <p style="color: #cbd5e1; max-width: 720px; margin: 0 auto 1.75rem; font-size: 1.05rem; line-height: 1.65;">
    I 542 Club Alcologici Territoriali accolgono chiunque voglia liberarsi dalle dipendenze e tutte le famiglie coinvolte. Non vendiamo soluzioni illusorie, non abbiamo abbonamenti né pacchetti a pagamento: offriamo una comunità viva, il metodo scientifico del Prof. Hudolin e una sedia sempre pronta per te.
  </p>
  <div class="d-flex justify-content-center gap-3 flex-wrap">
    <a href="world-club-explorer.php" class="btn primary" style="padding: 0 28px; text-decoration: none;">
      <?=dx_icon('map-pin', '', 18)?>
      <span style="margin-left: 8px;">Trova il Club più vicino alla tua città</span>
    </a>
    <a href="metodo.php" class="btn-rainbow-outline" style="text-decoration: none; padding: 0 24px;">
      <?=dx_icon('feather', '', 18)?>
      <span style="margin-left: 8px;">Come Funziona il Metodo</span>
    </a>
  </div>
</section>

<!-- ============================================================== -->
<!-- BEWAY.LIFE x DEPENDEX: VIAGGI ESPERIENZIALI & CROCIERE A TEMA  -->
<!-- ============================================================== -->
<section class="my-5 p-4 p-md-5 lux-metallic-card rainbow-border" style="background: radial-gradient(circle at top right, rgba(0,212,255,0.14), rgba(12,16,28,0.96)); border-radius: 24px; box-shadow: 0 16px 50px rgba(0,0,0,0.8);">
  <div class="row align-items-center g-4">
    <div class="col-lg-8">
      <div class="badge-neon-rainbow mb-2">
        <span class="dot"></span>
        <span class="text-rainbow">BEWAY.LIFE x DEPENDEX · IL VIAGGIO TRASFORMATIVO</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.5rem); color: #FFFFFF; font-weight: 900; margin-bottom: 0.8rem; line-height: 1.2;">
        Dalla Sobrietà Quotidiana alla <span class="text-rainbow">Grande Crociera della Rinascita</span>
      </h2>
      <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.65; margin-bottom: 1.2rem;">
        La trasformazione si consolida quando vivi la bellezza del mondo in un ambiente protetto e privo di stimoli tossici. Scopri i <strong>Viaggi Esperienziali di BEWAY.LIFE</strong>: 8 giorni e 7 notti nel Mediterraneo con formula 100% analcolica, cucina gourmet vitale, risveglio all'alba sul ponte e <strong>Masterclass intensive con Mirco Pregnolato</strong>.
      </p>

      <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 1.4rem;">
        <span class="dx-ticker-badge" style="color: var(--neon-gold); border-color: var(--neon-gold); font-size: 0.8rem;">
          Crociera Mediterraneo: Santorini · Mykonos · Atene · Kotor
        </span>
        <span class="dx-ticker-badge" style="color: var(--neon-cyan); border-color: var(--neon-cyan); font-size: 0.8rem;">
          Cabine da 890 € (o Caparra 190 €)
        </span>
        <span class="dx-ticker-badge" style="color: var(--neon-green); border-color: var(--neon-green); font-size: 0.8rem;">
          Ritiro Biohacking Dolomiti (450 €)
        </span>
      </div>

      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="crociera-benessere-masterclass.php" class="btn primary" style="text-decoration: none; border-radius: 12px; font-weight: 800; padding: 12px 26px;">
          <?=dx_icon('compass', '', 16)?> Scopri la Grande Crociera
        </a>
        <a href="viaggi-esperienziali.php" class="btn" style="text-decoration: none; border-radius: 12px; font-weight: 700; border: 1px solid rgba(255,255,255,0.25); color: #fff; padding: 12px 22px;">
          Tutti i Viaggi & Ritiri BEWAY.LIFE
        </a>
      </div>
    </div>

    <div class="col-lg-4 text-center">
      <div style="background: rgba(8,12,22,0.9); border: 1px solid rgba(0,212,255,0.3); border-radius: 18px; padding: 22px; box-shadow: 0 10px 30px rgba(0,212,255,0.2);">
        <div style="margin-bottom: 12px; display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%; background: rgba(0, 212, 255, 0.12); border: 1px solid rgba(0, 212, 255, 0.35); box-shadow: 0 0 16px rgba(0, 212, 255, 0.3);">
          <?=dx_icon('ship', 'text-neon-cyan', 32)?>
        </div>
        <h4 style="color: #FFF; font-size: 1.15rem; font-weight: 800; margin-bottom: 6px;">Partnership Ufficiale</h4>
        <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.5; margin-bottom: 12px;">
          L'unione perfetta tra il supporto comunitario di <strong>DEPENDEX</strong> e lo stile di vita consapevole di <strong>BEWAY.LIFE</strong>.
        </p>
        <a href="https://beway.life" target="_blank" rel="noopener" style="color: var(--neon-cyan); font-weight: 800; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
          Visita beway.life <?=dx_icon('external-link', '', 14)?>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================== -->
<!-- VETRINA EDITORIALE: LIBRI AMAZON KDP A 3 TIER (M. PREGNOLATO)  -->
<!-- ============================================================== -->
<section class="my-5">
  <div class="text-center mb-4">
    <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
      <span class="dot"></span>
      <span class="text-rainbow">COLLANA EDITORIALE UFFICIALE DIRETTA</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 3.5vw, 2.5rem); color: #FFFFFF; font-weight: 900;">
      I Libri & Diari di Mirco Pregnolato <span class="text-rainbow">a 3 Tier</span>
    </h2>
    <p style="color: #cbd5e1; max-width: 720px; margin: 0 auto; font-size: 1rem; line-height: 1.6;">
      Quaderno della Famiglia, Diario del Club 90 Giorni, Diario Servitore Insegnante, Crescita Esponenziale, Trilogia SAT e 52 Settimane di Cambiamento. Scegli tra <strong>PDF Digitale</strong>, <strong>Cartaceo Amazon Prime</strong> e <strong>Bundle con Masterclass</strong>.
    </p>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 2rem;">
    <div class="card card-neon-gold p-4" style="background: rgba(12,16,26,0.92); border-radius: 18px; border: 1px solid rgba(255,255,255,0.1);">
      <small style="color: var(--neon-gold); font-weight: 800; font-size: 0.75rem;">BEST SELLER</small>
      <h4 style="color: #FFF; font-size: 1.15rem; font-weight: 800; margin: 4px 0 8px;">Il Diario del Club: 90 Giorni</h4>
      <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.5; margin-bottom: 12px;">Il diario operativo per consolidare la sobrietà nei primi tre mesi del percorso.</p>
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="color: var(--neon-gold); font-weight: 800; font-size: 0.95rem;">Da 14,90 €</span>
        <a href="offers.php" class="btn primary small" style="text-decoration: none; font-size: 0.8rem; border-radius: 8px;">Vedi i 3 Tier</a>
      </div>
    </div>

    <div class="card card-neon-cyan p-4" style="background: rgba(12,16,26,0.92); border-radius: 18px; border: 1px solid rgba(255,255,255,0.1);">
      <small style="color: var(--neon-cyan); font-weight: 800; font-size: 0.75rem;">FAMIGLIA & DIALOGO</small>
      <h4 style="color: #FFF; font-size: 1.15rem; font-weight: 800; margin: 4px 0 8px;">Quaderno della Famiglia</h4>
      <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.5; margin-bottom: 12px;">Lo strumento per ricostruire il patto di fiducia e dialogo a casa.</p>
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="color: var(--neon-cyan); font-weight: 800; font-size: 0.95rem;">Da 14,90 €</span>
        <a href="offers.php" class="btn primary small" style="text-decoration: none; font-size: 0.8rem; border-radius: 8px;">Vedi i 3 Tier</a>
      </div>
    </div>

    <div class="card card-neon-orange p-4" style="background: rgba(12,16,26,0.92); border-radius: 18px; border: 1px solid rgba(255,255,255,0.1);">
      <small style="color: var(--neon-orange); font-weight: 800; font-size: 0.75rem;">CRESCITA ESPONENZIALE</small>
      <h4 style="color: #FFF; font-size: 1.15rem; font-weight: 800; margin: 4px 0 8px;">Diario di Crescita Esponenziale</h4>
      <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.5; margin-bottom: 12px;">386 pagine per 365 giorni di potenziamento personale e sovranità mentale.</p>
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="color: var(--neon-orange); font-weight: 800; font-size: 0.95rem;">Da 19,90 €</span>
        <a href="offers.php" class="btn primary small" style="text-decoration: none; font-size: 0.8rem; border-radius: 8px;">Vedi i 3 Tier</a>
      </div>
    </div>
  </div>

  <div class="text-center">
    <a href="offers.php" class="btn primary" style="padding: 12px 32px; border-radius: 14px; font-weight: 800; text-decoration: none;">
      <?=dx_icon('book-open', '', 16)?> Esplora Tutti i 6 Libri & Scegli il tuo Formato
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
  <a class="bubble lux-metallic-card" href="viaggi-esperienziali.php" style="color: #FFFFFF; border: 1px solid rgba(0,212,255,0.4);">
    <span style="color: var(--neon-cyan);"><?=dx_icon('compass', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Viaggi BEWAY</span>
  </a>
  <a class="bubble lux-metallic-card" href="offers.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('book-open', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Libri KDP (3 Tier)</span>
  </a>
  <a class="bubble lux-metallic-card" href="metodo.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('feather', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Metodo Hudolin</span>
  </a>
  <a class="bubble lux-metallic-card" href="events-public.php" style="color: #FFFFFF; border: 1px solid rgba(212,175,55,0.3);">
    <span style="color: #D4AF37;"><?=dx_icon('calendar', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Eventi & Moduli SAT</span>
  </a>
  <a class="bubble lux-metallic-card" href="cart.php" style="color: #FFFFFF; border: 1px solid rgba(0,255,100,0.3);">
    <span style="color: var(--neon-green);"><?=dx_icon('shopping-cart', '', 32)?></span>
    <span style="font-weight: 800; margin-top: 10px; font-size: 0.95rem;">Carrello Acquisti</span>
  </a>
</section>

<?php require '_footer.php'; ?>