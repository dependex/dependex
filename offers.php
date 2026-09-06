<?php
require_once 'bootstrap.php';
$pageTitle = 'Collana Editoriale Ufficiale · Libri Amazon KDP';
$metaDesc = 'I manuali, i diari operativi e i trattati scientifici del Metodo Hudolin ed Ecologico-Sociale, stampati e distribuiti internazionalmente su Amazon KDP.';
require '_header.php';

// Catalogo Libri e Manuali Collana Editoriale Amazon KDP
// I link amazon_url saranno popolati direttamente appena pubblicati su KDP
$kdpBooks = [
    [
        'id' => 'kdp-diario-club',
        'code' => 'GP001',
        'category' => 'DIARIO OPERATIVO DEI 90 GIORNI',
        'title' => 'Il Diario del Club: 90 Giorni di Rinascita e Sobrietà',
        'subtitle' => 'Guida pratica quotidiana, riflessioni del cerchio e tracciamento della crescita personale',
        'description' => 'Lo strumento pratico indispensabile per il partecipante e la famiglia. 90 giorni di schede quotidiane, esercizi di auto-osservazione, frasi guida del Prof. Hudolin e spazio strutturato per gli appunti del Club settimanale.',
        'formats' => ['Copertina Flessibile (Paperback)', 'Copertina Rigida (Hardcover)', 'Formato Kindle eBook'],
        'pages' => '196 pagine · Formato 6x9"',
        'color_theme' => 'card-neon-gold',
        'badge_color' => 'var(--neon-gold)',
        'amazon_url' => '#', // Inserire il link Amazon KDP quando pubblicato
        'status' => 'IN ARRIVO SU AMAZON KDP',
        'icon' => 'book-open'
    ],
    [
        'id' => 'kdp-metodo-hudolin',
        'code' => 'GP002',
        'category' => 'MANUALE FONDATIVO & SCIENTIFICO',
        'title' => 'Il Metodo Hudolin: Trattato Ecologico-Sociale',
        'subtitle' => 'Dalla sofferenza multifamiliare all\'autonomia comunitaria nei Club Alcologici Territoriali',
        'description' => 'L\'opera teorica e metodologica di riferimento per comprendere l\'approccio sistemico-familiare di Vladimir Hudolin. Supera la visione dell\'alcolismo come malattia individuale per restituire dignità, responsabilità e salute relazionale.',
        'formats' => ['Copertina Flessibile (Paperback)', 'Formato Kindle eBook'],
        'pages' => '240 pagine · Formato 6x9"',
        'color_theme' => 'card-neon-green',
        'badge_color' => 'var(--neon-green)',
        'amazon_url' => '#',
        'status' => 'IN ARRIVO SU AMAZON KDP',
        'icon' => 'feather'
    ],
    [
        'id' => 'kdp-guida-famiglia',
        'code' => 'GP003',
        'category' => 'GUIDA PRATICA PER LA FAMIGLIA',
        'title' => 'La Famiglia al Centro: Riconquistare la Fiducia',
        'subtitle' => 'Manuale di sostegno per conviventi, figli e amici nel percorso di liberazione dalla dipendenza',
        'description' => 'Cosa fare quando un famigliare nega il problema? Come gestire le ricadute senza disperazione? Una guida empatica, chiara e priva di colpevolizzazioni per riportare armonia, verità e dialogo autentico nel nucleo domestico.',
        'formats' => ['Copertina Flessibile (Paperback)', 'Formato Kindle eBook'],
        'pages' => '160 pagine · Formato 6x9"',
        'color_theme' => 'card-neon-cyan',
        'badge_color' => 'var(--neon-cyan)',
        'amazon_url' => '#',
        'status' => 'IN ARRIVO SU AMAZON KDP',
        'icon' => 'heart'
    ],
    [
        'id' => 'kdp-quaderno-servitore',
        'code' => 'GP004',
        'category' => 'MANUALE PER SERVITORI-INSEGNANTI',
        'title' => 'Il Quaderno del Servitore-Insegnante e Facilitatore',
        'subtitle' => 'Dinamiche di gruppo, conduzione del cerchio e protocollo di accoglienza nei Club',
        'description' => 'La cassetta degli attrezzi per chi guida o affianca un Club Territoriale: gestione del silenzio, ascolto empatico, integrazione con i servizi territoriali (SAT, SerD, ARCAT) e deontologia del ruolo di facilitatore.',
        'formats' => ['Copertina Flessibile (Paperback)', 'Copertina Rigida (Hardcover)'],
        'pages' => '210 pagine · Formato 6x9"',
        'color_theme' => 'card-neon-orange',
        'badge_color' => 'var(--neon-orange)',
        'amazon_url' => '#',
        'status' => 'IN ARRIVO SU AMAZON KDP',
        'icon' => 'award'
    ],
    [
        'id' => 'kdp-sovranita-personale',
        'code' => 'GP005',
        'category' => 'CRESCITA PERSONALE & SOVRANITÀ',
        'title' => 'Sovranità Personale: La Via della Lucidità Radicale',
        'subtitle' => 'Come disinnescare la pressione sociale, ricostruire l\'identità e vivere senza dipendenze',
        'description' => 'Un saggio illuminante su come le abitudini tossiche contemporanee disconnettono dalla realtà e come riappropriarsi della propria mente, della salute biochimica e della libertà di decidere il proprio cammino.',
        'formats' => ['Copertina Flessibile (Paperback)', 'Formato Kindle eBook'],
        'pages' => '180 pagine · Formato 6x9"',
        'color_theme' => 'card-neon-violet',
        'badge_color' => 'var(--neon-violet)',
        'amazon_url' => '#',
        'status' => 'IN ARRIVO SU AMAZON KDP',
        'icon' => 'crown'
    ],
    [
        'id' => 'kdp-moduli-sat',
        'code' => 'GP006',
        'category' => 'FORMAZIONE TECNICA & INTERCLUB',
        'title' => 'Compendio dei Moduli SAT e Scuole Territoriali',
        'subtitle' => 'Programmi di sensibilizzazione, formazione continua e documentazione per operatori sociali',
        'description' => 'Raccolta organica dei moduli didattici, linee guida di intervento precoce e protocolli operativi sviluppati in oltre quattro decenni di esperienza pratica nei territori italiani ed europei.',
        'formats' => ['Copertina Flessibile (Paperback)', 'Copertina Rigida (Hardcover)'],
        'pages' => '280 pagine · Formato 7x10"',
        'color_theme' => 'card-neon-red',
        'badge_color' => 'var(--neon-red)',
        'amazon_url' => '#',
        'status' => 'IN ARRIVO SU AMAZON KDP',
        'icon' => 'academic'
    ]
];
?>

<main class="container py-5">

  <!-- ============================================================== -->
  <!-- HERO BANNER PANORAMICO                                         -->
  <!-- ============================================================== -->
  <div class="rainbow-panorama-banner mb-4">
    <img src="assets/img/rainbow-nebula-panorama.jpg" alt="Collana Editoriale Ufficiale Amazon KDP" style="max-height: 380px; object-fit: cover;">
  </div>

  <section class="mb-5">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">COLLANA EDITORIALE UFFICIALE · DISTRIBUZIONE GLOBALE AMAZON KDP</span>
    </div>
    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; color: #FFFFFF; margin-bottom: 1rem;">
      Libri, Diari Operativi & <span class="text-rainbow">Manuali Amazon KDP</span>
    </h1>
    <p style="font-size: 1.12rem; line-height: 1.7; color: #cbd5e1; max-width: 820px;">
      Tutte le pubblicazioni ufficiali dell'ecosistema, i diari quotidiani di sobrietà e i trattati metodologici del <strong>Prof. Vladimir Hudolin</strong> sono pubblicati e distribuiti a livello globale su <strong>Amazon KDP (Kindle Direct Publishing)</strong>. Disponibili in formato cartaceo con spedizione rapida Prime ed in formato digitale Kindle eBook.
    </p>
  </section>

  <!-- ============================================================== -->
  <!-- STRISCIA GARANZIA AMAZON PRIME & KINDLE                        -->
  <!-- ============================================================== -->
  <div class="lux-metallic-card p-4 mb-5" style="border: 1px solid rgba(0, 212, 255, 0.35); background: rgba(12, 16, 28, 0.92); border-radius: 20px;">
    <div class="row align-items-center g-4">
      <div class="col-md-8">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
          <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: var(--neon-cyan); box-shadow: 0 0 14px var(--neon-cyan);"></span>
          <h3 style="margin: 0; font-size: 1.25rem; color: #FFFFFF; font-weight: 800; font-family: var(--font-serif);">
            Acquisto Sicuro & Spedizione Diretta su Amazon
          </h3>
        </div>
        <p style="margin: 0; color: #cbd5e1; font-size: 0.94rem; line-height: 1.6;">
          Nessuna transazione o vendita diretta su questo sito: gli ordini vengono gestiti interamente dall'infrastruttura ufficiale di <strong>Amazon</strong> con le massime garanzie di consegna, reso e protezione del cliente.
        </p>
      </div>
      <div class="col-md-4 text-md-end">
        <div style="display: inline-flex; flex-direction: column; gap: 6px; text-align: left;">
          <span style="color: var(--neon-gold); font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <?=dx_icon('check-circle', '', 14)?> Spedizione Prime 24/48h
          </span>
          <span style="color: var(--neon-green); font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <?=dx_icon('check-circle', '', 14)?> Formato Cartaceo & Copertina Rigida
          </span>
          <span style="color: var(--neon-cyan); font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
            <?=dx_icon('check-circle', '', 14)?> eBook istantaneo su Kindle
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================================== -->
  <!-- GRIGLIA CARD PRODOTTI AMAZON KDP (7 COLORI NEON)               -->
  <!-- ============================================================== -->
  <section class="mb-5">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
      <?php foreach($kdpBooks as $b): ?>
        <article class="card <?=$b['color_theme']?> p-4" style="display: flex; flex-direction: column; justify-content: space-between; border-radius: 20px; background: rgba(12, 16, 26, 0.92); border: 1px solid rgba(255, 255, 255, 0.1);">
          
          <div>
            <!-- Header Card -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 8px;">
              <span class="dx-ticker-badge" style="color: <?=$b['badge_color']?>; border-color: <?=$b['badge_color']?>;">
                <?=$b['code']?> · <?=$b['category']?>
              </span>
              <span style="font-size: 0.72rem; font-weight: 800; color: #cbd5e1; background: rgba(255,255,255,0.06); padding: 4px 8px; border-radius: 6px;">
                KDP
              </span>
            </div>

            <!-- Titolo e Sottotitolo -->
            <h3 style="color: #FFFFFF; font-family: var(--font-serif); font-size: 1.35rem; font-weight: 800; margin: 0.4rem 0 0.6rem; line-height: 1.3;">
              <?=h($b['title'])?>
            </h3>
            <p style="color: <?=$b['badge_color']?>; font-size: 0.88rem; font-weight: 700; margin-bottom: 12px; line-height: 1.45;">
              <?=h($b['subtitle'])?>
            </p>

            <!-- Sinossi -->
            <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin-bottom: 18px;">
              <?=h($b['description'])?>
            </p>

            <!-- Specifiche Formati -->
            <div style="background: rgba(16, 20, 32, 0.85); border-radius: 12px; padding: 12px; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.06);">
              <div style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: #cbd5e1; letter-spacing: 0.06em; margin-bottom: 6px;">
                FORMATI DISPONIBILI:
              </div>
              <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.84rem; color: #f8fafc;">
                <?php foreach($b['formats'] as $fmt): ?>
                  <li style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                    <span style="color: <?=$b['badge_color']?>;">▪</span> <?=h($fmt)?>
                  </li>
                <?php endforeach; ?>
              </ul>
              <div style="margin-top: 8px; font-size: 0.78rem; color: #94a3b8; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 6px;">
                <?=h($b['pages'])?>
              </div>
            </div>
          </div>

          <!-- Pulsante CTA Amazon -->
          <div>
            <?php if($b['amazon_url'] !== '#'): ?>
              <a href="<?=h($b['amazon_url'])?>" target="_blank" rel="noopener" class="btn primary" style="width: 100%; border-radius: 14px; min-height: 48px; text-decoration: none;">
                <?=dx_icon('external-link', '', 16)?>
                <span style="margin-left: 8px;">Acquista su Amazon KDP</span>
              </a>
            <?php else: ?>
              <button type="button" class="btn-rainbow-outline" style="width: 100%; border-radius: 14px; min-height: 48px; font-weight: 800; color: #ffffff;" onclick="alert('I link ufficiali Amazon KDP per \'<?=addslashes($b['title'])?>\' verranno attivati non appena completata la pubblicazione su Amazon.')">
                <?=dx_icon('book-open', '', 16)?>
                <span style="margin-left: 8px;">Disponibile a breve su Amazon</span>
              </button>
            <?php endif; ?>
          </div>

        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- SUPPORTO ORIENTAMENTO CORTEX                                   -->
  <!-- ============================================================== -->
  <section class="luxury-hero-card lux-metallic-card p-4 p-md-5 text-center my-5" style="border: 1px solid rgba(0, 212, 255, 0.35);">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">INTELLIGENZA ARTIFICIALE & BIBLIOTECA DIGITALE</span>
    </div>
    <h3 style="font-family: var(--font-serif); font-size: 1.8rem; color: #FFFFFF; margin-bottom: 0.75rem;">
      Quale libro o manuale risponde meglio al tuo momento attuale?
    </h3>
    <p style="color: #cbd5e1; max-width: 640px; margin: 0 auto 1.5rem; font-size: 1rem; line-height: 1.6;">
      Chiedi a <strong>Cortex</strong>, l'assistente cognitivo dell'ecosistema: ti indicherà i capitoli, gli esercizi o il volume più indicato per la tua situazione personale o famigliare.
    </p>
    <a href="cortex.php?q=<?=urlencode("Quale libro della collana Amazon KDP è più adatto al mio percorso?")?>" class="btn primary" style="padding: 0 28px; text-decoration: none;">
      <?=dx_icon('brain', '', 18)?>
      <span style="margin-left: 8px;">Chiedi Consiglio a Cortex</span>
    </a>
  </section>

</main>

<?php require '_footer.php'; ?>
