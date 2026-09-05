<?php 
$pageTitle = 'Aiuto e Supporto Immediato · Zero Giudizio';
$metaDesc = 'Non sei solo e non sei giudicato. Spazio protetto in puro oro e nero per persone e famiglie: orientamento immediato, 542 Club e supporto empatico.';
require '_header.php';
?>

<section class="hero compact" style="text-align:center;padding:3.5rem 1.5rem 2.5rem;">
  <div class="gold-glow-badge mb-3">
    <?=dx_icon('shield', '', 14)?>
    <span>UNO SPAZIO DI SOVRANITÀ PERSONALE · ZERO MORALE</span>
  </div>
  <h1 style="font-size:clamp(1.9rem, 3.8vw, 2.8rem);font-weight:800;letter-spacing:-0.03em;margin:0.6rem 0 1rem;color:#FFFFFF;">
    Basta combattere una guerra solitaria.<br>
    <span class="gold-foil-text">
      Non sei un problema da riparare, sei una persona da rialzare.
    </span>
  </h1>
  <p style="max-width:700px;margin:0 auto;color:#d1d5db;font-size:1.1rem;line-height:1.7;">
    Siamo onesti: quante volte hai detto <em>"questa è l'ultima volta"</em>? E quante volte ti sei svegliato con quella morsa al petto e la vergogna negli occhi? 
    Smettila di fustigarti con sensi di colpa che non servono a nulla. Qui trovi chi conosce a memoria ogni scusa della mente, perché ci è già passato e l'ha disarmata.
  </p>
</section>

<!-- 3 SITUAZIONI DI PARTENZA CON ICONE SVG E CARDS METALLICHE -->
<section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:1.5rem;margin:2rem 0;">
  <div class="lux-metallic-card p-4" style="display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="color:#D4AF37;margin-bottom:1rem;"><?=dx_icon('feather', '', 36)?></div>
      <span class="dx-ticker-badge">PER TE STESSO</span>
      <h3 style="margin:0.5rem 0 0.8rem;font-size:1.3rem;color:#FFFFFF;font-family:var(--font-serif);">"Voglio smettere di svegliarmi con l'angoscia"</h3>
      <p style="color:#a1a1aa;font-size:0.92rem;line-height:1.6;">
        Se sei stanco di nascondere le prove, inventare giustificazioni al lavoro o sentirti in trappola, non sei debole: sei solo schiavo di una molecola che fa il suo lavoro neurochimico. Nei Club non trovi inquisitori, ma pari pronti a tenderti la mano.
      </p>
    </div>
    <div style="margin-top:1.5rem;">
      <a class="btn primary" href="world-club-explorer.php" style="width:100%;text-align:center;background:linear-gradient(135deg, #FFF2B2, #D4AF37);color:#070709;font-weight:800;">
        <?=dx_icon('map-pin', '', 16)?>
        <span style="margin-left:6px;">Trova una sedia nel Club</span>
      </a>
    </div>
  </div>

  <div class="lux-metallic-card p-4" style="display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="color:#D4AF37;margin-bottom:1rem;"><?=dx_icon('heart-handshake', '', 36)?></div>
      <span class="dx-ticker-badge">PER LA FAMIGLIA</span>
      <h3 style="margin:0.5rem 0 0.8rem;font-size:1.3rem;color:#FFFFFF;font-family:var(--font-serif);">"Amo qualcuno che si sta distruggendo"</h3>
      <p style="color:#a1a1aa;font-size:0.92rem;line-height:1.6;">
        Le notti insonni ad aspettare il rumore della chiave nella toppa, la paura per i figli, la rabbia che si mescola alla disperazione. Nel Metodo Hudolin la famiglia non è un pubblico impotente: è il fulcro del cambiamento. Puoi venire anche da solo.
      </p>
    </div>
    <div style="margin-top:1.5rem;">
      <a class="btn" href="metodo.php" style="width:100%;text-align:center;border-color:rgba(212,175,55,0.4);color:#FFFFFF;font-weight:700;">
        <?=dx_icon('feather', '', 16)?>
        <span style="margin-left:6px;">Il Metodo per i Familiari</span>
      </a>
    </div>
  </div>

  <div class="lux-metallic-card p-4" style="display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="color:#D4AF37;margin-bottom:1rem;"><?=dx_icon('brain', '', 36)?></div>
      <span class="dx-ticker-badge">ASCOLTO H24 ANONIMO</span>
      <h3 style="margin:0.5rem 0 0.8rem;font-size:1.3rem;color:#FFFFFF;font-family:var(--font-serif);">"Ho bisogno di chiarirmi la mente subito"</h3>
      <p style="color:#a1a1aa;font-size:0.92rem;line-height:1.6;">
        Se la crisi è adesso e non vuoi esporti con nessuno, dialoga con <b>Cortex AI</b>. È addestrato sui principi ecologico-sociali di Vladimir Hudolin: non ti darà giudizi morali, ma ti aiuterà a smontare il panico un minuto alla volta.
      </p>
    </div>
    <div style="margin-top:1.5rem;">
      <a class="btn" href="cortex.php" style="width:100%;text-align:center;border-color:rgba(212,175,55,0.4);color:#D4AF37;font-weight:700;">
        <?=dx_icon('brain', '', 16)?>
        <span style="margin-left:6px;">Parla con Cortex AI</span>
      </a>
    </div>
  </div>
</section>

<!-- SOCCORSO MEDICO D'URGENZA VS PERCORSO ECOLOGICO-SOCIALE -->
<section class="lux-metallic-card p-4 my-5" style="border-left:4px solid #D4AF37;background:#101116;">
  <h3 style="margin-top:0;display:flex;align-items:center;gap:0.75rem;color:#FFFFFF;font-size:1.2rem;">
    <span style="color:#D4AF37;"><?=dx_icon('alert-triangle', '', 22)?></span>
    <span>In caso di emergenza medica acuta o pericolo per l'incolumità</span>
  </h3>
  <p style="font-size:0.95rem;line-height:1.6;color:#a1a1aa;margin-bottom:1rem;">
    I Club Alcologici Territoriali offrono una comunità relazionale continuativa e permanente nel tempo, ma <b>non possono e non devono sostituire il soccorso ospedaliero d'urgenza in caso di crisi di astinenza acuta o pericolo clinico imminente</b>.
  </p>
  <div style="display:flex;flex-wrap:wrap;gap:1.5rem;font-size:0.95rem;">
    <div style="color:#FFFFFF;"><b>Numero Unico Europeo Emergenze:</b> <a href="tel:112" style="font-weight:800;color:#D4AF37;text-decoration:underline;">112</a></div>
    <div style="color:#FFFFFF;"><b>Telefono Verde Alcol ISS:</b> <a href="tel:800632000" style="font-weight:800;color:#D4AF37;text-decoration:underline;">800 632 000</a> (Lun-Ven 10-15)</div>
  </div>
</section>

<!-- CONTATTO DIRETTO & RISERVATEZZA -->
<section class="lux-metallic-card p-4 p-md-5 mb-5" style="background:radial-gradient(ellipse at 80% 20%, rgba(212,175,55,0.08) 0%, #070709 70%);">
  <div style="max-width:720px;">
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('compass', '', 14)?>
      <span>ACCOGLIENZA TERRITORIALE DIRETTA</span>
    </div>
    <h2 style="font-size:1.6rem;margin:0.5rem 0 0.8rem;color:#FFFFFF;font-family:var(--font-serif);">
      Preferisci che sia un facilitatore a indicarti il Club giusto?
    </h2>
    <p style="color:#a1a1aa;font-size:0.96rem;line-height:1.65;">
      Inviaci un messaggio in totale anonimato. Nessun bot di vendita, nessuna pressione commerciale. Ti risponderà un Servitore-Insegnante con l'esperienza necessaria per darti le coordinate esatte.
    </p>
    <p style="font-size:1.15rem;margin:1.2rem 0 0;">
      <a href="mailto:info@dependex.social" style="font-weight:700;color:#D4AF37;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
        <?=dx_icon('mail', '', 18)?>
        <span>info@dependex.social</span>
      </a>
    </p>
    <div style="margin-top:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;">
      <a class="btn primary" href="offers.php" style="background:linear-gradient(135deg, #FFF2B2, #D4AF37);color:#070709;font-weight:800;">
        <?=dx_icon('sparkles', '', 16)?>
        <span style="margin-left:6px;">Scopri il Protocollo M.A.G.I.C.</span>
      </a>
      <a class="btn" href="world-map.php" style="border-color:rgba(212,175,55,0.4);color:#FFFFFF;">
        <?=dx_icon('compass', '', 16)?>
        <span style="margin-left:6px;">Esplora i 542 Club</span>
      </a>
    </div>
  </div>
</section>

<?php require '_footer.php';?>