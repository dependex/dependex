<?php
require_once __DIR__.'/bootstrap.php';
$global = site_mode() === 'DEPENDEX';
$pageTitle = $global ? 'The Hudolin Method · Science & Social Ecology' : 'Il Metodo Hudolin · Scienza, Comunità e Sovranità';
$metaDesc = 'Il Metodo Hudolin: basta con l’illusione della forza di volontà solitaria. Approccio ecologico-sociale in 5 fasi per riconquistare lucidità, famiglia e dignità.';
require '_header.php';
?>

<main class="container py-5">
  <!-- Header -->
  <section class="text-center mb-5">
    <div class="gold-glow-badge mb-3">
      <?=dx_icon('feather', '', 14)?>
      <span>APPROCCIO ECOLOGICO-SOCIALE VLADIMIR HUDOLIN</span>
    </div>
    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4.2vw, 3.2rem); font-weight: 800; line-height: 1.15; margin-bottom: 1rem; color: #FFFFFF;">
      La "forza di volontà" da sola è un'illusione tossica.<br>
      La libertà nasce da <span class="gold-foil-text">legami autentici e dignità</span>.
    </h1>
    <p class="mx-auto" style="max-width: 720px; font-size: 1.15rem; line-height: 1.7; color: #d1d5db;">
      Quante volte hai provato a "stringere i denti" per poi cedere alla prima giornata storta? La chimica della dipendenza batte sempre la solitudine. Da oltre 40 anni e in oltre 540 Club, il Metodo Hudolin dimostra che quando smetti di fare l'eroe solitario e ti siedi in un cerchio di pari senza giudizio, la sobrietà smette di essere una rinuncia faticosa e diventa la tua condizione naturale di benessere.
    </p>
  </section>

  <!-- Key Insight Card -->
  <div class="luxury-hero-card lux-metallic-card p-4 p-md-5 mb-5">
    <div class="row align-items-center g-4">
      <div class="col-md-7">
        <h3 style="font-family: var(--font-serif); color: #FFFFFF; font-size: 1.8rem; margin-bottom: 1rem; font-weight: 800;">
          La Persona non coincide con il Problema
        </h3>
        <p style="line-height: 1.65; font-size: 1.05rem; color: #a1a1aa;">
          La cultura dominante ti ha fatto credere due bugie opposte: o che sei un debole senza carattere, oppure che sei un "malato cronico incurabile" da internare. Entrambe le narrazioni servono solo a toglierti la responsabilità e il potere di agire.
        </p>
        <p style="line-height: 1.65; font-size: 1.05rem; color: #a1a1aa;">
          Nei Club Alcologici Territoriali non usiamo etichette umilianti. Sei una persona con una storia, dei talenti e una famiglia che ha il diritto di tornare a guardarti negli occhi con fierezza.
        </p>
      </div>
      <div class="col-md-5">
        <div class="p-4 rounded-4" style="background: rgba(10,11,16,0.9); border: 1px solid rgba(212,175,55,0.3);">
          <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('message-circle', '', 32)?></div>
          <blockquote style="font-style: italic; font-size: 1rem; line-height: 1.6; color: #FFFFFF; margin-bottom: 12px;">
            «L'alcolismo non è una malattia misteriosa dell'individuo, ma un comportamento legato allo stile di vita e alla cultura della comunità.»
          </blockquote>
          <small style="color: #D4AF37; font-weight: 700;">— Prof. Vladimir Hudolin, Psichiatra OMS e Fondatore dei Club</small>
        </div>
      </div>
    </div>
  </div>

  <!-- The 5 Steps of the Schema Logico -->
  <section class="mb-5">
    <div class="text-center mb-4">
      <div class="gold-glow-badge mb-2">
        <?=dx_icon('sparkles', '', 14)?>
        <span>SCHEMA LOGICO DEI 5 PASSI</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF;">Il Percorso Scientifico di Rigenerazione</h2>
      <p style="color: #9ca3af; max-width: 600px; margin: 0 auto;">Dalla nebbia mentale del primo giorno alla piena padronanza della propria esistenza.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="lux-metallic-card p-4 h-100">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="dx-ticker-badge" style="margin-bottom:0;">FASE 1</span>
            <span style="color: #71717a; font-size: 0.78rem; font-weight: 700;">GIORNO 1 - 7</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #FFFFFF; font-weight: 800; font-size: 1.2rem;">L'Accoglienza & Lo Stop al Panico</h4>
          <p style="color: #a1a1aa; font-size: 0.88rem; line-height: 1.55; margin-bottom: 0;">
            Primo incontro nel Club territoriale o su stanza protetta: disarmo immediato del senso di colpa, nessun interrogatorio, patto di presenza reciproca e serenità immediata.
          </p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="lux-metallic-card p-4 h-100">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="dx-ticker-badge" style="margin-bottom:0;">FASE 2</span>
            <span style="color: #71717a; font-size: 0.78rem; font-weight: 700;">SETTIMANA 2 - 4</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #FFFFFF; font-weight: 800; font-size: 1.2rem;">Riprogrammazione delle Abitudini</h4>
          <p style="color: #a1a1aa; font-size: 0.88rem; line-height: 1.55; margin-bottom: 0;">
            Disinnescare l'ancora delle 18:00, monitoraggio del ritmo sonno-veglia con il diario quotidiano, e prime risposte tangibili sul piano della salute e del portafoglio.
          </p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="lux-metallic-card p-4 h-100">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="dx-ticker-badge" style="margin-bottom:0;">FASE 3</span>
            <span style="color: #71717a; font-size: 0.78rem; font-weight: 700;">MESE 2 - 3</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #FFFFFF; font-weight: 800; font-size: 1.2rem;">Riconciliazione dei Legami Familiari</h4>
          <p style="color: #a1a1aa; font-size: 0.88rem; line-height: 1.55; margin-bottom: 0;">
            Coinvolgimento del partner e dei figli: guarire le ferite del passato, sostituire i risentimenti con un patto di lealtà e ricostruire la credibilità giorno dopo giorno.
          </p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="lux-metallic-card p-4 h-100">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="dx-ticker-badge" style="margin-bottom:0;">FASE 4</span>
            <span style="color: #71717a; font-size: 0.78rem; font-weight: 700;">MESE 4 - 6</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #FFFFFF; font-weight: 800; font-size: 1.2rem;">Consolidamento & Sovranità Personale</h4>
          <p style="color: #a1a1aa; font-size: 0.88rem; line-height: 1.55; margin-bottom: 0;">
            La sobrietà non è più un tabù o uno sforzo: è la tua armatura invisibile. Mente lucida, massima produttività sul lavoro e gestione impeccabile delle emozioni e degli stress test.
          </p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="lux-metallic-card p-4 h-100" style="border-color: rgba(212,175,55,0.6);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="dx-ticker-badge" style="margin-bottom:0; background: rgba(212,175,55,0.25); color: #FFF2B2;">FASE 5</span>
            <span style="color: #D4AF37; font-size: 0.78rem; font-weight: 800;">CONTINUITÀ & CRESCITA</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #FFFFFF; font-weight: 800; font-size: 1.2rem;">Da Ex-Vittima a Servitore-Insegnante</h4>
          <p style="color: #a1a1aa; font-size: 0.88rem; line-height: 1.55; margin-bottom: 0;">
            Partecipazione ai moduli SAT e abilitazione come facilitatore: donare la propria testimonianza alle nuove famiglie, diventando un faro di speranza viva nel tuo territorio.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Box -->
  <section class="lux-metallic-card text-center my-5 p-4 p-md-5" style="border: 1px solid rgba(212,175,55,0.35);">
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('compass', '', 14)?>
      <span>IL TUO MOMENTO È ADESSO</span>
    </div>
    <h3 style="font-family: var(--font-serif); color: #FFFFFF; margin-bottom: 0.75rem; font-size: 1.8rem;">
      Non devi promettere nulla per sempre. Devi solo sederti stasera.
    </h3>
    <p style="color: #a1a1aa; max-width: 600px; margin: 0 auto 1.5rem; font-size: 1rem; line-height: 1.6;">
      Unisciti a una riunione di Club vicino a te o inizia con il nostro protocollo digitale guidato. Nessun modulo burocratico da compilare, solo accoglienza vera.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="world-club-explorer.php" class="btn primary" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; padding: 0 24px; text-decoration: none;">
        <?=dx_icon('map-pin', '', 16)?>
        <span style="margin-left:6px;">Trova un Club nella tua zona</span>
      </a>
      <a href="offers.php" class="btn" style="border-color: rgba(212,175,55,0.4); color: #FFFFFF; font-weight: 700; text-decoration: none; padding: 0 22px;">
        <?=dx_icon('sparkles', '', 16)?>
        <span style="margin-left:6px;">Vedi le Offerte M.A.G.I.C.</span>
      </a>
    </div>
  </section>
</main>

<?php require '_footer.php'; ?>