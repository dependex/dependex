<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/welfare/HumanWelfareEngine.php';

$u = current_user();
$brand = site_brand();

$pageTitle = 'Mappa del Benessere & Orientamento alla Vita · Human Welfare OS';
$metaDesc = 'Un sistema di orientamento e consapevolezza personale senza diagnosi né giudizi. Esplora le 7 dimensioni del benessere e trova la comunità reale più vicina a te.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/orientamento.php';

$breadcrumbs = [
    'Home' => '/',
    'Mappa del Benessere' => 'orientamento.php'
];

$dimensions = HumanWelfareEngine::getDimensions();
$purposes = HumanWelfareEngine::getLifePurposes();
$hudolinCore = HumanWelfareEngine::getHudolinPillars();

// Parametro opzionale da query string per pre-selezionare una dimensione
$activeKey = isset($_GET['area']) && isset($dimensions[$_GET['area']]) ? $_GET['area'] : 'radicamento';
$activeData = $dimensions[$activeKey] ?? $dimensions['radicamento'];
$orientationManifesto = HumanWelfareEngine::orientate($activeKey);

require '_header.php';
?>

<div class="container py-4" style="max-width: 1180px; margin: 0 auto; padding: 0 1rem;">

  <!-- HERO UMANA & NON DIAGNOSTICA -->
  <section class="human-hero-card text-center my-4" style="padding: clamp(1.8rem, 4vw, 3rem) clamp(1rem, 3vw, 2rem); background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, rgba(12, 16, 28, 0.95) 80%); border: 1px solid rgba(129, 140, 248, 0.35); border-radius: var(--dx-radius-lg, 20px);">
    <div class="badge-neon-rainbow mb-3">
      <span class="dot"></span>
      <span style="color:#c7d2fe;">HUMAN WELFARE OS · MAPPA DI ORIENTAMENTO</span>
    </div>

    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4.5vw, 3.2rem); color: #ffffff; font-weight: 800; margin: 0 0 16px; line-height: 1.18;">
      Non devi sapere già tutto.<br>
      <span class="text-rainbow">Da dove vuoi iniziare?</span>
    </h1>

    <p style="color: #cbd5e1; max-width: 760px; margin: 0 auto 20px; font-size: clamp(0.98rem, 2vw, 1.15rem); line-height: 1.6;">
      Nessuna diagnosi. Nessun voto alla persona. Nessun test clinico.<br>
      Questa mappa ti aiuta a dare voce a ciò che senti:
      <em>“Questa parte della mia vita oggi chiede attenzione.”</em>
    </p>

    <!-- MANIFESTO DI ACCOGLIENZA (4 PILASTRI UMANI) -->
    <div class="row g-2 justify-content-center text-start" style="max-width: 920px; margin: 1.5rem auto 0;">
      <div class="col-12 col-sm-6 col-md-3">
        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; height: 100%;">
          <div style="font-size: 0.8rem; font-weight: 700; color: #38bdf8; margin-bottom: 2px;">ZERO ETICHETTE</div>
          <div style="font-size: 0.82rem; color: #94a3b8; line-height: 1.35;">La persona viene prima di qualsiasi problema o passato.</div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; height: 100%;">
          <div style="font-size: 0.8rem; font-weight: 700; color: #4ade80; margin-bottom: 2px;">ZERO GIUDIZIO</div>
          <div style="font-size: 0.82rem; color: #94a3b8; line-height: 1.35;">Non c'è una "normalità" a cui conformarsi né un voto da prendere.</div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; height: 100%;">
          <div style="font-size: 0.8rem; font-weight: 700; color: #fbbf24; margin-bottom: 2px;">COMUNITÀ REALE</div>
          <div style="font-size: 0.82rem; color: #94a3b8; line-height: 1.35;">Il digitale orienta, ma la vera forza nasce nell'incontro umano.</div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-md-3">
        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px 14px; height: 100%;">
          <div style="font-size: 0.8rem; font-weight: 700; color: #c084fc; margin-bottom: 2px;">SEMPRE GRATUITO</div>
          <div style="font-size: 0.82rem; color: #94a3b8; line-height: 1.35;">Tutti i Club territoriali sono aperti e accessibili a tutti.</div>
        </div>
      </div>
    </div>
  </section>

  <!-- LE 7 DIMENSIONI DEL BENESSERE (INTERATTIVE & TOUCH FIRST) -->
  <section class="my-5" id="esplora-dimensioni">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
      <div>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 3vw, 2rem); color: #ffffff; margin: 0 0 6px;">
          Le 7 Dimensioni dell'Esperienza Umana
        </h2>
        <p style="color: #94a3b8; font-size: 0.95rem; margin: 0;">
          Tocca una dimensione per ascoltare la domanda maieutica e scoprire come la comunità può accompagnarti.
        </p>
      </div>
      <div style="font-size: 0.85rem; color: #818cf8; font-weight: 600;">
        7/7 Dimensioni Disponibili
      </div>
    </div>

    <!-- SELETTORE TOUCH A GRIGLIA -->
    <div class="row g-2 g-md-3 mb-4">
      <?php foreach ($dimensions as $key => $dim): 
        $isSelected = ($key === $activeKey);
      ?>
        <div class="col-6 col-md-4 col-lg-auto flex-lg-fill">
          <a href="?area=<?= htmlspecialchars($key) ?>#orientamento-focus"
             class="d-block text-decoration-none p-3 h-100 transition-all dimension-card"
             style="background: <?= $isSelected ? 'rgba(255,255,255,0.08)' : 'rgba(255,255,255,0.02)' ?>; 
                    border: 2px solid <?= $isSelected ? htmlspecialchars($dim['color']) : 'rgba(255,255,255,0.08)' ?>; 
                    border-radius: 14px; 
                    box-shadow: <?= $isSelected ? '0 0 16px ' . htmlspecialchars($dim['color']) . '44' : 'none' ?>;
                    min-height: 100px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?= htmlspecialchars($dim['color']) ?>;"></span>
              <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Livello <?= $dim['level'] ?></span>
            </div>
            <div style="font-size: 1.05rem; font-weight: 700; color: <?= $isSelected ? '#ffffff' : '#e2e8f0' ?>; margin-bottom: 2px;">
              <?= htmlspecialchars($dim['name']) ?>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; line-height: 1.25;">
              <?= htmlspecialchars($dim['tagline']) ?>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- FOCUS AREA ATTIVA: "PARTIAMO DA LÌ" -->
    <div id="orientamento-focus" class="p-4 p-md-5" 
         style="background: rgba(15, 23, 42, 0.85); border: 1px solid <?= htmlspecialchars($activeData['color']) ?>; border-radius: var(--dx-radius-lg, 20px); box-shadow: 0 12px 32px rgba(0,0,0,0.4);">
      
      <div class="row g-4 align-items-center">
        <div class="col-12 col-lg-7">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3" style="background: rgba(255,255,255,0.05); border-radius: 20px; border: 1px solid <?= htmlspecialchars($activeData['color']) ?>88;">
            <span style="width: 8px; height: 8px; border-radius: 50%; background: <?= htmlspecialchars($activeData['color']) ?>;"></span>
            <span style="font-size: 0.82rem; font-weight: 700; color: #f8fafc;">DIMENSIONE <?= $activeData['level'] ?>: <?= strtoupper(htmlspecialchars($activeData['name'])) ?></span>
          </div>

          <div style="font-family: var(--font-serif); font-size: clamp(1.5rem, 3.2vw, 2.2rem); color: #ffffff; font-weight: 700; margin-bottom: 12px; line-height: 1.25;">
            “Questa parte della mia vita oggi chiede attenzione.”
          </div>

          <div class="p-3 mb-3" style="background: rgba(255,255,255,0.03); border-left: 4px solid <?= htmlspecialchars($activeData['color']) ?>; border-radius: 0 8px 8px 0;">
            <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 4px;">DOMANDA MAIEUTICA DI AUTO-RIFLESSIONE</div>
            <div style="font-size: 1.15rem; color: #f1f5f9; font-weight: 600; font-style: italic;">
              “<?= htmlspecialchars($activeData['question']) ?>”
            </div>
          </div>

          <p style="color: #cbd5e1; font-size: 1rem; line-height: 1.6; margin-bottom: 16px;">
            <?= htmlspecialchars($activeData['description']) ?>
          </p>

          <div class="d-flex flex-wrap gap-2 mb-4">
            <span style="font-size: 0.8rem; color: #94a3b8; margin-right: 4px;">Aree correlate:</span>
            <?php foreach ($activeData['facets'] as $facet): ?>
              <span class="badge" style="background: rgba(255,255,255,0.06); color: #e2e8f0; font-weight: 500; font-size: 0.78rem; padding: 6px 10px; border-radius: 6px;">
                <?= htmlspecialchars($facet) ?>
              </span>
            <?php endforeach; ?>
          </div>

          <!-- RISPOSTA DI DEPENDEX: PARTIAMO DA LÌ -->
          <div class="p-3 p-md-4" style="background: radial-gradient(circle at 10% 20%, rgba(34, 197, 94, 0.12) 0%, rgba(15, 23, 42, 0.9) 100%); border: 1px solid rgba(34, 197, 94, 0.35); border-radius: 12px;">
            <div style="font-size: 0.8rem; font-weight: 800; color: #4ade80; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
              LA RISPOSTA DI DEPENDEX & DELLA COMUNITÀ
            </div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #ffffff; margin-bottom: 6px; font-family: var(--font-serif);">
              “Partiamo da lì.”
            </div>
            <div style="font-size: 0.95rem; color: #cbd5e1; line-height: 1.55;">
              <?= htmlspecialchars($orientationManifesto['message']) ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-5">
          <!-- CARD RELAZIONI & RISORSE APERTE -->
          <div class="p-4 h-100 d-flex flex-column justify-content-between" 
               style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px;">
            
            <div>
              <div style="font-size: 0.78rem; font-weight: 700; color: #818cf8; text-transform: uppercase; margin-bottom: 8px;">
                I PASSI CONCRETI DELLA COMUNITÀ
              </div>
              <h3 style="font-size: 1.2rem; color: #ffffff; font-weight: 700; margin-bottom: 14px;">
                Cosa può aiutarti a rimettere in moto la tua vita
              </h3>

              <div class="mb-3 d-flex gap-3 align-items-start">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">1</div>
                <div>
                  <div style="font-size: 0.92rem; font-weight: 700; color: #f1f5f9;">Incontro Settimanale al Club</div>
                  <div style="font-size: 0.82rem; color: #94a3b8; line-height: 1.4;">90 minuti di ascolto circolare e reciprocità. Con la famiglia o da soli. Gratuito e anonimo.</div>
                </div>
              </div>

              <div class="mb-3 d-flex gap-3 align-items-start">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(74, 222, 128, 0.15); color: #4ade80; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">2</div>
                <div>
                  <div style="font-size: 0.92rem; font-weight: 700; color: #f1f5f9;">Parla con un Servitore Insegnante</div>
                  <div style="font-size: 0.82rem; color: #94a3b8; line-height: 1.4;">Un facilitatore formato con esperienza di vita che ti accoglie senza giudicare.</div>
                </div>
              </div>

              <div class="mb-4 d-flex gap-3 align-items-start">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(244, 114, 182, 0.15); color: #f472b6; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">3</div>
                <div>
                  <div style="font-size: 0.92rem; font-weight: 700; color: #f1f5f9;">Risorsa Pratica Consigliata</div>
                  <div style="font-size: 0.82rem; color: #94a3b8; line-height: 1.4;"><?= htmlspecialchars($orientationManifesto['resource_recommended']) ?></div>
                </div>
              </div>
            </div>

            <div class="pt-3 border-top border-secondary border-opacity-25">
              <a href="cerca-club.php" class="btn btn-primary w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); border: none; border-radius: 12px; font-size: 1rem; min-height: 48px;">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                Trova il Club più vicino a te
              </a>
              <div class="text-center mt-2" style="font-size: 0.78rem; color: #64748b;">
                Oltre 322 Club gratuiti e attivi in tutta Italia
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- I 4 SCOPI UNIVERSALI DI VITA (DHARMA, ARTHA, KAMA, MOKSHA) -->
  <section class="my-5 p-4 p-md-5" style="background: rgba(255,255,255,0.015); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--dx-radius-lg, 20px);">
    <div class="text-center mb-4" style="max-width: 780px; margin-left: auto; margin-right: auto;">
      <div class="badge-neon-rainbow mb-2">
        <span class="dot"></span>
        <span style="color:#fde68a;">DIREZIONE, EQUILIBRIO E RESPONSABILITÀ</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: clamp(1.5rem, 3.2vw, 2.2rem); color: #ffffff; font-weight: 700; margin: 0 0 10px;">
        I 4 Scopi della Vita: Mappa Filosofica e Umana
      </h2>
      <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin: 0;">
        Ispirati ai principi etici universali delle antiche tradizioni sapienzali (senza dogmi religiosi), 
        questi 4 cardini orientano l'equilibrio tra doveri, risorse, gioia di vivere e libertà interiore.
      </p>
    </div>

    <div class="row g-3">
      <?php foreach ($purposes as $p): ?>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="p-4 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span style="font-size: 1.15rem; font-weight: 800; color: #f8fafc; font-family: var(--font-serif);"><?= htmlspecialchars($p['name']) ?></span>
                <span style="font-size: 0.72rem; font-weight: 700; color: #a5b4fc; background: rgba(99,102,241,0.2); padding: 2px 8px; border-radius: 10px;"><?= htmlspecialchars($p['term']) ?></span>
              </div>
              <div style="font-size: 0.88rem; font-weight: 600; color: #e2e8f0; margin-bottom: 8px;">
                <?= htmlspecialchars($p['meaning']) ?>
              </div>
              <p style="font-size: 0.82rem; color: #94a3b8; line-height: 1.45; margin-bottom: 12px;">
                <?= htmlspecialchars($p['description']) ?>
              </p>
            </div>
            <div class="pt-2 border-top border-secondary border-opacity-25" style="font-size: 0.8rem; font-style: italic; color: #cbd5e1;">
              “<?= htmlspecialchars($p['hudolin_connection']) ?>”
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- IL CUORE COMUNITARIO DI HUDOLIN (RELAZIONE & RETE SOCIALE) -->
  <section class="my-5 p-4 p-md-5" style="background: radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.12) 0%, rgba(15, 23, 42, 0.95) 85%); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: var(--dx-radius-lg, 20px);">
    <div class="row g-4 align-items-center">
      <div class="col-12 col-lg-6">
        <div style="font-size: 0.8rem; font-weight: 800; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
          L'APPROCCIO ECOLOGICO-SOCIALE
        </div>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.5rem, 3.2vw, 2.2rem); color: #ffffff; font-weight: 700; margin: 0 0 14px; line-height: 1.25;">
          Il Metodo Hudolin:<br>
          <span style="color: #38bdf8;">La Comunità è la Risorsa.</span>
        </h2>
        <p style="color: #cbd5e1; font-size: 0.98rem; line-height: 1.6; margin-bottom: 16px;">
          Vladimir Hudolin ha dimostrato che i problemi legati all'alcol e alle dipendenze non sono malattie isolate dell'individuo, ma nodi di sofferenza dell'intero sistema relazionale e culturale.
        </p>
        <p style="color: #94a3b8; font-size: 0.9rem; line-height: 1.55; margin-bottom: 24px;">
          Nel cerchio del Club (CAT), famiglie e persone condividono le proprie vite senza etichette sanitarie. Il cambiamento non è imposto dall'alto, ma fiorisce attraverso l'amicizia solidale e la sobrietà condivisa.
        </p>
        
        <div class="d-flex flex-wrap gap-2">
          <a href="cerca-club.php" class="btn btn-primary px-4 py-3 fw-bold" style="border-radius: 12px; min-height: 44px; display: inline-flex; align-items: center;">
            Trova un Club nella tua zona
          </a>
          <a href="come-funziona-il-club.php" class="btn btn-outline-light px-4 py-3 fw-bold" style="border-radius: 12px; min-height: 44px; display: inline-flex; align-items: center;">
            Come Funziona una Riunione
          </a>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="row g-2">
          <div class="col-12 col-sm-6">
            <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
              <div style="font-size: 0.9rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">Famiglia & Relazione</div>
              <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.4;">Non si cura un malato, si rigenera il tessuto relazionale familiare e amicale.</div>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
              <div style="font-size: 0.9rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">Incontro Circolare</div>
              <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.4;">Tutti allo stesso livello, senza cattedre o gerarchie di potere.</div>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
              <div style="font-size: 0.9rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">Servitore Insegnante</div>
              <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.4;">Un membro della comunità formato, facilitatore dell'empatia e dell'ascolto.</div>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
              <div style="font-size: 0.9rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">Ecologia Sociale</div>
              <div style="font-size: 0.8rem; color: #94a3b8; line-height: 1.4;">Protezione della salute pubblica e trasformazione della cultura del bere nel territorio.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ MAIEUTICHE & DOMANDE CHE NON HAI IL CORAGGIO DI FARE -->
  <section class="my-5 p-4 p-md-5" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--dx-radius-lg, 20px);">
    <div class="text-center mb-4">
      <h2 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 3vw, 2rem); color: #ffffff; margin: 0 0 6px;">
        Le Domande che Forse Non Hai il Coraggio di Fare
      </h2>
      <p style="color: #94a3b8; font-size: 0.95rem; margin: 0;">
        Dubbi legittimi e naturali quando si pensa di avvicinarsi per la prima volta.
      </p>
    </div>

    <div class="row g-3" style="max-width: 960px; margin: 0 auto;">
      <div class="col-12 col-md-6">
        <div class="p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
          <div style="font-weight: 700; color: #f8fafc; font-size: 0.95rem; margin-bottom: 4px;">E se al Club non voglio parlare?</div>
          <div style="color: #94a3b8; font-size: 0.85rem; line-height: 1.45;">Nessuno ti obbligherà mai a parlare. Puoi venire, sederti in cerchio e semplicemente ascoltare le storie degli altri per tutto il tempo che desideri.</div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
          <div style="font-weight: 700; color: #f8fafc; font-size: 0.95rem; margin-bottom: 4px;">E se penso di non avere un vero problema?</div>
          <div style="color: #94a3b8; font-size: 0.85rem; line-height: 1.45;">Al Club non si viene per dimostrare di avere una patologia. Si viene per confrontarsi sullo stile di vita, sulle relazioni familiari e sul proprio benessere.</div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
          <div style="font-weight: 700; color: #f8fafc; font-size: 0.95rem; margin-bottom: 4px;">Può venire solo un familiare?</div>
          <div style="color: #94a3b8; font-size: 0.85rem; line-height: 1.45;">Assolutamente sì. Moltissimi familiari iniziano a frequentare il Club anche se la persona coinvolta non è ancora pronta. Il beneficio sulla serenità di casa è immediato.</div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="p-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
          <div style="font-weight: 700; color: #f8fafc; font-size: 0.95rem; margin-bottom: 4px;">Quanto costa partecipare?</div>
          <div style="color: #94a3b8; font-size: 0.85rem; line-height: 1.45;">È completamente gratuito per sempre. Non ci sono quote di iscrizione, rette o consulenze a pagamento. I Club sono beni comuni del territorio.</div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA FINALE VERSO IL CLUB FINDER -->
  <section class="text-center my-5 p-4 p-md-5" style="background: radial-gradient(circle at 50% 50%, rgba(79, 70, 229, 0.15) 0%, rgba(12, 16, 28, 0.95) 90%); border: 1px solid rgba(99, 102, 241, 0.4); border-radius: var(--dx-radius-lg, 20px);">
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.8rem, 4vw, 2.6rem); color: #ffffff; font-weight: 800; margin: 0 0 12px;">
      Dal Digitale alla Comunità Reale.
    </h2>
    <p style="color: #cbd5e1; max-width: 640px; margin: 0 auto 24px; font-size: 1.05rem; line-height: 1.6;">
      Qualsiasi sia la parte della tua vita che oggi chiede attenzione, non affrontarla da solo. 
      C'è una comunità accogliente pronta ad ascoltarti questa settimana.
    </p>
    <a href="cerca-club.php" class="btn btn-primary px-5 py-3 fw-bold" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); border: none; border-radius: 14px; font-size: 1.05rem; min-height: 50px; display: inline-flex; align-items: center; gap: 8px;">
      <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
      Trova il Tuo Club Locale Ora
    </a>
  </section>

</div>

<?php require '_footer.php'; ?>
