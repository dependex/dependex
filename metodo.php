<?php
require_once __DIR__.'/bootstrap.php';
$global = site_mode() === 'DEPENDEX';
$pageTitle = $global ? 'The Hudolin Method · Science & Community' : 'Il Metodo Hudolin · Oltre la Dipendenza';
$metaDesc = 'Il Metodo Hudolin: non una cura burocratica o farmacologica, ma un approccio ecologico-sociale per rigenerare legami, salute e dignità familiare.';
require '_header.php';
?>

<main class="container py-5">
  <!-- Header -->
  <section class="text-center mb-5">
    <div class="d-inline-block px-3 py-1 mb-3 border rounded-pill" style="border-color: rgba(201,168,76,0.3); background: rgba(201,168,76,0.06);">
      <span style="font-size: 0.8rem; font-weight: 700; letter-spacing: 0.12em; color: var(--color-gold); text-transform: uppercase;">
        ✦ L'APPROCCIO ECOLOGICO-SOCIALE
      </span>
    </div>
    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 700; line-height: 1.15; margin-bottom: 1rem;">
      Non è questione di forza di volontà.<br>
      È una questione di <span class="gold-gradient-text">legami e dignità</span>.
    </h1>
    <p class="mx-auto text-muted" style="max-width: 680px; font-size: 1.15rem; line-height: 1.65;">
      Il 95% di chi tenta di superare una dipendenza da solo ricade entro pochi mesi. Il Metodo Hudolin ha dimostrato da oltre 40 anni che quando la persona è accolta senza giudizio all'interno di una comunità di pari, la trasformazione diventa naturale e duratura.
    </p>
  </section>

  <!-- Key Insight Card -->
  <div class="luxury-hero-card p-4 p-md-5 mb-5">
    <div class="row align-items-center g-4">
      <div class="col-md-7">
        <h3 style="font-family: var(--font-serif); color: var(--color-gold-light); font-size: 1.8rem; margin-bottom: 1rem;">
          La Persona non coincide con il Problema
        </h3>
        <p class="text-muted" style="line-height: 1.6; font-size: 1.05rem;">
          Nella società moderna chi vive un disturbo da uso di alcol o sostanze viene marchiato con lo stigma della debolezza morale. Nel Club territoriale questa etichetta viene strappata via al primo minuto.
        </p>
        <p class="text-muted" style="line-height: 1.6; font-size: 1.05rem;">
          Non sei un paziente da ricoverare né un colpevole da punire: sei una persona con la sua storia, i suoi talenti e una famiglia che merita di ritrovare la pace.
        </p>
      </div>
      <div class="col-md-5">
        <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.5); border: 1px solid rgba(201,168,76,0.25);">
          <div style="font-size: 2.2rem; color: var(--color-gold); margin-bottom: 10px;">💬</div>
          <blockquote class="text-white mb-2" style="font-style: italic; font-size: 1rem; line-height: 1.5;">
            «L'alcolismo non è una malattia misteriosa dell'individuo, ma un comportamento legato allo stile di vita e alla cultura della comunità.»
          </blockquote>
          <small class="text-muted">— Prof. Vladimir Hudolin, Fondatore del Movimento dei Club</small>
        </div>
      </div>
    </div>
  </div>

  <!-- The 5 Steps of the Schema Logico -->
  <section class="mb-5">
    <div class="text-center mb-4">
      <h2 style="font-family: var(--font-serif); font-size: 2.2rem;">Lo Schema Logico della Rinascita</h2>
      <p class="text-muted">Il percorso progressivo che ti accompagna dal caos alla piena autorevolezza.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="p-4 h-100 rounded-4" style="background: var(--color-surface-card); border: 1px solid rgba(255,255,255,0.08);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge rounded-pill bg-warning text-dark fw-bold px-3 py-1">FASE 1</span>
            <span class="text-muted small">GIORNO 1 - 7</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #fff;">L'Accoglienza & Lo Stop al Panico</h4>
          <p class="text-muted small mb-0">Primo incontro in un Club territoriale o stanza digitale: ascolto puro, zero domande inquisitorie, sollievo immediato e patto di presenza.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 h-100 rounded-4" style="background: var(--color-surface-card); border: 1px solid rgba(255,255,255,0.08);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge rounded-pill bg-warning text-dark fw-bold px-3 py-1">FASE 2</span>
            <span class="text-muted small">SETTIMANA 2 - 4</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #fff;">La Ristrutturazione delle Abitudini</h4>
          <p class="text-muted small mb-0">Diario quotidiano di sobrietà, gestione delle ore critiche (le 18:00), riattivazione del ritmo del sonno e prime vittorie visibili in famiglia.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-4 h-100 rounded-4" style="background: var(--color-surface-card); border: 1px solid rgba(255,255,255,0.08);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge rounded-pill bg-warning text-dark fw-bold px-3 py-1">FASE 3</span>
            <span class="text-muted small">MESE 2 - 3</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #fff;">La Riconciliazione dei Legami</h4>
          <p class="text-muted small mb-0">Il coinvolgimento della famiglia: sanare le ferite emotive, riallacciare il dialogo con figli e partner e ristabilire la fiducia reciproca.</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="p-4 h-100 rounded-4" style="background: var(--color-surface-card); border: 1px solid rgba(255,255,255,0.08);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge rounded-pill bg-warning text-dark fw-bold px-3 py-1">FASE 4</span>
            <span class="text-muted small">MESE 4 - 6</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: #fff;">Consolidamento & Nuova Identità</h4>
          <p class="text-muted small mb-0">La sobrietà non è più una privazione o una battaglia: diventa la condizione naturale di benessere in cui esprimere il proprio potenziale professionale e personale.</p>
        </div>
      </div>

      <div class="col-md-6">
        <div class="p-4 h-100 rounded-4" style="background: var(--color-surface-card); border: 1px solid var(--color-surface-border);">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge rounded-pill bg-warning text-dark fw-bold px-3 py-1">FASE 5</span>
            <span class="text-warning small fw-bold">CONTINUA</span>
          </div>
          <h4 style="font-family: var(--font-serif); color: var(--color-gold-light);">L'Evoluzione in Servitore-Insegnante</h4>
          <p class="text-muted small mb-0">Academy e formazione per diventare facilitatore di Club: donare l'esperienza superata ad altre famiglie che stanno vivendo ciò che tu hai già vinto.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Box -->
  <section class="text-center my-5 p-4 rounded-4" style="background: rgba(201,168,76,0.06); border: 1px solid var(--color-surface-border);">
    <h3 style="font-family: var(--font-serif); color: #fff; margin-bottom: 0.5rem;">Fai il primo passo oggi stesso</h3>
    <p class="text-muted mx-auto mb-4" style="max-width: 580px;">
      Non devi decidere per tutta la vita. Devi solo decidere di farti ascoltare stasera.
    </p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="world-club-explorer.php" class="btn btn-warning fw-bold px-4 py-2" style="border-radius: 50px; background: var(--color-gold); color: #111; text-decoration: none;">
        Trova un Club nella tua zona ➔
      </a>
      <a href="offers.php" class="btn btn-outline-light px-4 py-2" style="border-radius: 50px; text-decoration: none;">
        Vedi le Offerte M.A.G.I.C.
      </a>
    </div>
  </section>
</main>

<?php require '_footer.php'; ?>