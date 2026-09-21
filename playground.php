<?php
/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * LIFE PLAYGROUND — SPAZIO DI CALMA, CONSAPEVOLEZZA E CONNESSIONE REALE
 * 
 * Linee guida deontologiche:
 * - Zero diagnosi cliniche, zero promesse terapeutiche o mediche.
 * - Zero indici numerici di benessere, zero classifiche o competizione.
 * - Non gamificare la sofferenza (nessun punteggio per dolore o disagio).
 * - Screen Off → Life On: il percorso riporta sempre all'incontro e alla vita reale nei 1.770 Club.
 */

require_once __DIR__ . '/bootstrap.php';

$pageTitle = "Life Playground · Calma, Orientamento e Comunità Reale";
$metaDesc = "Uno spazio interattivo per ritrovare respiro, centratura con la Bussola a 9 Raggi e collegamento diretto con i 1.770 Club territoriali in tutta Italia.";

// I 9 Raggi di The Dependex Compass (senza emoji grezze, con icone di sistema)
$compassRays = [
    'corpo' => [
        'name' => 'Corpo',
        'icon' => 'activity',
        'color' => '#00f0ff',
        'desc' => 'Sensazioni fisiche, tensione, respiro e postura.',
        'osserva' => 'Appoggia i piedi a terra. In quale punto del corpo avverti tensione o stanchezza in questo momento?',
        'pratica' => 'Fai tre respiri lenti rilasciando le spalle verso il basso ad ogni espirazione.',
        'azione' => 'Bevi un bicchiere d’acqua e concediti 5 minuti di camminata senza guardare lo schermo.'
    ],
    'mente' => [
        'name' => 'Mente',
        'icon' => 'sun',
        'color' => '#ff007f',
        'desc' => 'Pensieri ricorrenti, lucidità e quiete interiore.',
        'osserva' => 'I tuoi pensieri corrono veloci verso il passato o verso il futuro? Riconoscili senza giudicarli.',
        'pratica' => 'Nomina mentalmente 3 oggetti fisici che vedi attorno a te per tornare al qui e ora.',
        'azione' => 'Dedica i prossimi 60 secondi al respiro guidato presente qui sotto.'
    ],
    'relazioni' => [
        'name' => 'Relazioni',
        'icon' => 'users',
        'color' => '#00ff77',
        'desc' => 'Legami significativi, ascolto sincero e confini protettivi.',
        'osserva' => 'C’è una persona con cui senti il bisogno di parlare con il cuore aperto senza dover fingere?',
        'pratica' => 'Manda un messaggio breve e gentile di gratitudine a una persona cara.',
        'azione' => 'Pianifica una telefonata o un caffè reale con qualcuno che ti fa sentire al sicuro.'
    ],
    'famiglia' => [
        'name' => 'Famiglia',
        'icon' => 'home',
        'color' => '#d4af37',
        'desc' => 'Clima domestico, fragilità condivise e mutuo rispetto.',
        'osserva' => 'Cosa potrebbe alleggerire l’atmosfera nella tua casa oggi?',
        'pratica' => 'Durante la prossima conversazione in famiglia, ascolta senza interrompere e senza dare consigli non richiesti.',
        'azione' => 'I Club alcologici territoriali sono aperti all’intera famiglia: scopri come funzionano.'
    ],
    'lavoro' => [
        'name' => 'Lavoro & Ritmo',
        'icon' => 'briefcase',
        'color' => '#7928ca',
        'desc' => 'Dignità dell’impegno quotidiano e giusto stacco serale.',
        'osserva' => 'Riesci a staccare la mente dagli impegni lavorativi quando chiudi la giornata?',
        'pratica' => 'Stabilisci un orario preciso di fine attività e rispetta il tuo tempo di riposo.',
        'azione' => 'Silenzia le notifiche professionali per le prossime due ore.'
    ],
    'risorse' => [
        'name' => 'Risorse & Sobrietà',
        'icon' => 'shield',
        'color' => '#00f0ff',
        'desc' => 'Custodia delle proprie energie, del tempo e delle spese.',
        'osserva' => 'Dove disperdi più energia mentale o emotiva durante la settimana?',
        'pratica' => 'Scegli una sola priorità essenziale per la giornata di oggi e lascia il resto in secondo piano.',
        'azione' => 'Fai una scelta sobria e consapevole che rispetti la tua serenità.'
    ],
    'comunita' => [
        'name' => 'Comunità & Club',
        'icon' => 'compass',
        'color' => '#00ff77',
        'desc' => 'Condivisione reale, fine dell’isolamento e accoglienza.',
        'osserva' => 'Nessuno guarisce da solo: da quanto tempo non partecipi a un cerchio di persone sincere?',
        'pratica' => 'Esplora la mappa dei 1.770 Club territoriali e individua il gruppo più vicino al tuo quartiere.',
        'azione' => 'Partecipa al prossimo incontro settimanale: è gratuito, riservato e senza burocrazia.'
    ],
    'significato' => [
        'name' => 'Significato & Valori',
        'icon' => 'sparkles',
        'color' => '#d4af37',
        'desc' => 'Ciò per cui vale la pena alzarsi al mattino.',
        'osserva' => 'Quale valore fondamentale (onestà, cura, libertà, generosità) vuoi onorare oggi?',
        'pratica' => 'Compi un gesto silenzioso coerente con questo valore senza aspettarti ricompense.',
        'azione' => 'Condividi la tua testimonianza di ripartenza con chi ne ha bisogno.'
    ],
    'territorio' => [
        'name' => 'Territorio & Natura',
        'icon' => 'map-pin',
        'color' => '#ff007f',
        'desc' => 'Radici locali, paesaggio e connessione con l’ambiente.',
        'osserva' => 'Quanto tempo trascorri all’aria aperta rispetto al tempo davanti a uno schermo?',
        'pratica' => 'Esci per 10 minuti, osserva gli alberi, il cielo o l’acqua del tuo territorio.',
        'azione' => 'Vivi il territorio: scopri le iniziative dei Club e le camminate comunitarie.'
    ]
];

include __DIR__ . '/_header.php';
?>

<main id="mainContent" class="playground-clean-wrap">
  <!-- HERO PRINCIPALE -->
  <section class="pg-hero">
    <div class="pg-container">
      <div class="pg-badge">
        <span class="pg-badge-dot"></span> SPAZIO MAIEUTICO INTERATTIVO
      </div>
      <h1 class="pg-title">Life Playground</h1>
      <p class="pg-lead">
        Uno spazio essenziale per rallentare, ascoltare il tuo ritmo interiore e ritrovare un contatto vivo con la realtà.
        Nessun punteggio, nessuna competizione, nessuna diagnosi. Solo strumenti pratici per orientarsi.
      </p>

      <div class="pg-hero-actions">
        <a href="#breath-section" class="btn-pg-primary">
          <?=dx_icon('activity', '', 18)?> <span>60s di Respiro Guidato</span>
        </a>
        <a href="#compass-section" class="btn-pg-secondary">
          <?=dx_icon('compass', '', 18)?> <span>La Bussola a 9 Raggi</span>
        </a>
        <a href="mappa-club.php" class="btn-pg-tertiary">
          <?=dx_icon('map-pin', '', 18)?> <span>Trova i 1.770 Club</span>
        </a>
      </div>
    </div>
  </section>

  <!-- TOOL 1: IL RESPIRO GUIDATO 4-7-8 -->
  <section class="pg-section" id="breath-section">
    <div class="pg-container">
      <div class="pg-card pg-breath-card">
        <div class="pg-card-header">
          <div class="pg-tag"><?=dx_icon('activity', 'text-neon-cyan', 16)?> RIEQUILIBRIO SOMATICO</div>
          <h2>60 Secondi di Respiro Consapevole</h2>
          <p>
            Segui il ritmo armonico per ridurre l’ansia e ritrovare lucidità. 
            Il cerchio ti guida nelle fasi: <strong>Inspira (4s)</strong> · <strong>Trattieni (7s)</strong> · <strong>Espira (8s)</strong>.
          </p>
        </div>

        <div class="breath-visualizer-box">
          <div class="breath-anim-circle" id="breathCircle">
            <span class="breath-anim-phase" id="breathPhase">Pronto</span>
            <span class="breath-anim-counter" id="breathCounter">60s</span>
          </div>

          <div class="breath-action-bar">
            <button type="button" class="btn-breath-main" id="btnToggleBreath" onclick="toggleBreathPractice()">
              <?=dx_icon('play', '', 18)?> <span id="btnBreathLabel">Avvia Sessione</span>
            </button>
            <button type="button" class="btn-breath-reset" onclick="resetBreathPractice()">
              <?=dx_icon('rotate-ccw', '', 16)?> Ripristina
            </button>
          </div>

          <div class="breath-tip">
            <?=dx_icon('shield', 'text-neon-cyan', 14)?>
            <span>Pratica di consapevolezza corporea non medica. Se avverti capogiri, respira normalmente a ritmo naturale.</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TOOL 2: LA BUSSOLA A 9 RAGGI (THE DEPENDEX COMPASS) -->
  <section class="pg-section" id="compass-section">
    <div class="pg-container">
      <div class="pg-card pg-compass-card">
        <div class="pg-card-header">
          <div class="pg-tag"><?=dx_icon('compass', 'text-neon-gold', 16)?> ORIENTAMENTO INTEGRALE</div>
          <h2>La Bussola della Vita (9 Raggi)</h2>
          <p>
            Al centro ci sei <strong>tu</strong>, circondato dalle dimensioni che compongono le tue giornate. 
            Seleziona un raggio per esplorare riflessioni e azioni pratiche senza ricaricare la pagina.
          </p>
        </div>

        <!-- SELETTORE DEI 9 RAGGI -->
        <div class="compass-rays-grid" role="tablist" aria-label="Dimensioni della Bussola">
          <?php 
          $first = true;
          foreach ($compassRays as $k => $ray): 
            $isActive = $first;
            $first = false;
          ?>
            <button type="button" 
                    class="ray-btn <?=$isActive ? 'active' : ''?>" 
                    id="ray-btn-<?=$k?>" 
                    role="tab"
                    aria-selected="<?=$isActive ? 'true' : 'false'?>"
                    onclick="switchCompassRay('<?=$k?>')"
                    style="--ray-accent: <?=$ray['color']?>;">
              <span class="ray-icon"><?=dx_icon($ray['icon'], '', 22)?></span>
              <span class="ray-name"><?=h($ray['name'])?></span>
            </button>
          <?php endforeach; ?>
        </div>

        <!-- PANNELLO DETTAGLIO DINAMICO DEL RAGGIO SELEZIONATO -->
        <div class="compass-active-panel" id="compassActivePanel">
          <div class="panel-header">
            <span class="panel-icon-wrap" id="panelIcon"><?=dx_icon('activity', 'text-neon-cyan', 28)?></span>
            <div>
              <h3 id="panelTitle">Corpo</h3>
              <p id="panelDesc">Sensazioni fisiche, tensione, respiro e postura.</p>
            </div>
          </div>

          <div class="panel-steps-grid">
            <div class="step-box step-osserva">
              <div class="step-label">
                <?=dx_icon('eye', 'text-neon-cyan', 14)?> <span>1. OSSERVA</span>
              </div>
              <p id="panelOsserva"><?=$compassRays['corpo']['osserva']?></p>
            </div>

            <div class="step-box step-pratica">
              <div class="step-label">
                <?=dx_icon('sparkles', 'text-neon-gold', 14)?> <span>2. PRATICA (2 MIN)</span>
              </div>
              <p id="panelPratica"><?=$compassRays['corpo']['pratica']?></p>
            </div>

            <div class="step-box step-azione">
              <div class="step-label">
                <?=dx_icon('check-circle', 'text-neon-green', 14)?> <span>3. AZIONE REALE</span>
              </div>
              <p id="panelAzione"><?=$compassRays['corpo']['azione']?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TOOL 3: SCREEN OFF → LIFE ON (IL CERCHIO REALE DEI CLUB) -->
  <section class="pg-section" id="real-life-section">
    <div class="pg-container">
      <div class="pg-card pg-real-card">
        <div class="pg-card-header">
          <div class="pg-tag"><?=dx_icon('users', 'text-neon-green', 16)?> SCREEN OFF → LIFE ON</div>
          <h2>Dallo Schermo alla Vita Reale</h2>
          <p>
            Il digitale serve unicamente da ponte. La vera trasformazione accade quando ci si incontra, 
            si ascoltano altre storie e si scopre di non essere soli.
          </p>
        </div>

        <div class="real-doors-grid">
          <!-- PORTA 1: MAPPA DEI 1.770 CLUB -->
          <div class="door-card">
            <div class="door-icon-box text-neon-cyan">
              <?=dx_icon('map-pin', '', 28)?>
            </div>
            <h3>1.770 Club Territoriali</h3>
            <p>Mappa georeferenziata completa in tutte le 20 regioni d'Italia. Trova l'indirizzo, il giorno di incontro e il contatto telefonico del gruppo a te più vicino.</p>
            <a href="mappa-club.php" class="btn-door-action">
              Apri la Mappa 2D <?=dx_icon('arrow-right', '', 14)?>
            </a>
          </div>

          <!-- PORTA 2: PARLA CON NOI -->
          <div class="door-card">
            <div class="door-icon-box text-neon-gold">
              <?=dx_icon('message-circle', '', 28)?>
            </div>
            <h3>Ascolto Riservato e Immediato</h3>
            <p>Un servitore insegnante o una famiglia con anni di sobrietà e cammino alle spalle è disponibile per accoglierti con riservatezza, rispetto e zero giudizi.</p>
            <a href="parla-con-noi.php" class="btn-door-action highlight-gold">
              Contatta la Segreteria <?=dx_icon('arrow-right', '', 14)?>
            </a>
          </div>

          <!-- PORTA 3: IL METODO HUDOLIN -->
          <div class="door-card">
            <div class="door-icon-box text-neon-green">
              <?=dx_icon('feather', '', 28)?>
            </div>
            <h3>Come Funziona un Club</h3>
            <p>Gruppi multifamiliari autogestiti, liberi e gratuiti. Riconosciuti dal Ministero della Salute e dall'Istituto Superiore di Sanità (Legge 125/2001).</p>
            <a href="metodo.php" class="btn-door-action">
              Approfondisci il Metodo <?=dx_icon('arrow-right', '', 14)?>
            </a>
          </div>
        </div>

        <!-- FOOTER ETICO ISTITUZIONALE -->
        <div class="pg-institutional-banner">
          <?=dx_icon('shield', 'text-neon-cyan', 18)?>
          <div>
            <strong>Garanzia Etica e Riferimento Istituzionale:</strong>
            Questo portale non effettua diagnosi mediche o psicologiche né richiede pagamenti per la partecipazione ai Club. 
            I Club alcologici e territoriali operano in sinergia con il Piano Nazionale della Prevenzione del Ministero della Salute.
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- STILI DEDICATI PULITI E RESPONSIVE PER LIFE PLAYGROUND -->
<style>
.playground-clean-wrap {
  padding: 40px 16px 80px;
  background: radial-gradient(circle at 50% 0%, rgba(0, 240, 255, 0.05) 0%, transparent 60%),
              radial-gradient(circle at 100% 40%, rgba(212, 175, 55, 0.04) 0%, transparent 50%),
              #070a12;
  min-height: calc(100dvh - 80px);
}

.pg-container {
  max-width: 1080px;
  margin: 0 auto;
}

/* HERO */
.pg-hero {
  text-align: center;
  margin-bottom: 50px;
  padding: 20px 0;
}

.pg-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(0, 240, 255, 0.1);
  border: 1px solid rgba(0, 240, 255, 0.3);
  padding: 6px 16px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #00f0ff;
  letter-spacing: 0.08em;
  margin-bottom: 18px;
}

.pg-badge-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #00f0ff;
  box-shadow: 0 0 10px #00f0ff;
  animation: pgPulse 2s infinite ease-in-out;
}

@keyframes pgPulse {
  0%, 100% { transform: scale(0.9); opacity: 0.8; }
  50% { transform: scale(1.3); opacity: 1; }
}

.pg-title {
  font-size: clamp(2rem, 5vw, 3.2rem);
  font-weight: 850;
  line-height: 1.15;
  margin: 0 0 16px;
  background: linear-gradient(135deg, #ffffff 30%, #cbd5e1 70%, #d4af37 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.pg-lead {
  max-width: 720px;
  margin: 0 auto 30px;
  font-size: 1.05rem;
  color: #94a3b8;
  line-height: 1.6;
}

.pg-hero-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 14px;
}

.btn-pg-primary, .btn-pg-secondary, .btn-pg-tertiary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 22px;
  border-radius: 14px;
  font-size: 0.92rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.25s ease;
}

.btn-pg-primary {
  background: linear-gradient(135deg, #00f0ff, #0088ff);
  color: #070a12;
  box-shadow: 0 0 20px rgba(0, 240, 255, 0.3);
}

.btn-pg-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 28px rgba(0, 240, 255, 0.5);
  color: #070a12;
}

.btn-pg-secondary {
  background: rgba(18, 24, 38, 0.8);
  border: 1px solid rgba(212, 175, 55, 0.4);
  color: #d4af37;
}

.btn-pg-secondary:hover {
  background: rgba(212, 175, 55, 0.15);
  border-color: #d4af37;
  color: #fff;
  transform: translateY(-2px);
}

.btn-pg-tertiary {
  background: rgba(18, 24, 38, 0.8);
  border: 1px solid rgba(0, 255, 119, 0.4);
  color: #00ff77;
}

.btn-pg-tertiary:hover {
  background: rgba(0, 255, 119, 0.15);
  border-color: #00ff77;
  color: #fff;
  transform: translateY(-2px);
}

/* SECTION & CARDS */
.pg-section {
  margin-bottom: 50px;
}

.pg-card {
  background: rgba(13, 17, 28, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 34px 28px;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

.pg-card-header {
  text-align: center;
  max-width: 680px;
  margin: 0 auto 34px;
}

.pg-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #94a3b8;
  margin-bottom: 10px;
}

.pg-card-header h2 {
  font-size: clamp(1.5rem, 3.5vw, 2.2rem);
  font-weight: 800;
  margin: 0 0 10px;
  color: #f8fafc;
}

.pg-card-header p {
  color: #94a3b8;
  font-size: 0.95rem;
  line-height: 1.55;
  margin: 0;
}

/* RESPIRO GUIDATO */
.breath-visualizer-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
}

.breath-anim-circle {
  width: 220px;
  height: 220px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 240, 255, 0.15) 0%, rgba(7, 10, 18, 0.6) 80%);
  border: 2px solid rgba(0, 240, 255, 0.4);
  box-shadow: 0 0 30px rgba(0, 240, 255, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: transform 1s ease-in-out, border-color 0.5s ease, box-shadow 0.5s ease;
}

.breath-anim-circle.inhale {
  transform: scale(1.28);
  border-color: #00f0ff;
  box-shadow: 0 0 45px rgba(0, 240, 255, 0.6);
}

.breath-anim-circle.hold {
  transform: scale(1.28);
  border-color: #d4af37;
  box-shadow: 0 0 45px rgba(212, 175, 55, 0.6);
}

.breath-anim-circle.exhale {
  transform: scale(0.92);
  border-color: #00ff77;
  box-shadow: 0 0 25px rgba(0, 255, 119, 0.3);
}

.breath-anim-phase {
  font-size: 1.25rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.01em;
}

.breath-anim-counter {
  font-size: 0.88rem;
  font-family: ui-monospace, SFMono-Regular, monospace;
  color: #94a3b8;
  margin-top: 4px;
}

.breath-action-bar {
  display: flex;
  gap: 12px;
}

.btn-breath-main {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #00f0ff;
  color: #070a12;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 750;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-breath-main:hover {
  background: #38bdf8;
  transform: translateY(-2px);
  box-shadow: 0 0 20px rgba(0, 240, 255, 0.4);
}

.btn-breath-reset {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #cbd5e1;
  padding: 12px 18px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-breath-reset:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.breath-tip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #94a3b8;
  font-size: 0.8rem;
  max-width: 560px;
  text-align: center;
}

/* BUSSOLA A 9 RAGGI */
.compass-rays-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(105px, 1fr));
  gap: 10px;
  margin-bottom: 24px;
}

.ray-btn {
  background: rgba(18, 24, 38, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 14px 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.ray-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: var(--ray-accent);
  transform: translateY(-2px);
}

.ray-btn.active {
  background: rgba(18, 24, 38, 0.95);
  border-color: var(--ray-accent);
  box-shadow: 0 0 16px var(--ray-accent);
}

.ray-btn.active .ray-icon {
  color: var(--ray-accent);
}

.ray-icon {
  color: #94a3b8;
  transition: color 0.2s ease;
}

.ray-name {
  font-size: 0.78rem;
  font-weight: 700;
  color: #e2e8f0;
  text-align: center;
  line-height: 1.2;
}

.compass-active-panel {
  background: rgba(18, 24, 38, 0.7);
  border: 1px solid rgba(0, 240, 255, 0.2);
  border-radius: 18px;
  padding: 24px;
  transition: all 0.3s ease;
}

.panel-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.panel-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: rgba(0, 240, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.panel-header h3 {
  font-size: 1.4rem;
  margin: 0 0 4px;
  color: #f8fafc;
}

.panel-header p {
  margin: 0;
  color: #94a3b8;
  font-size: 0.9rem;
}

.panel-steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

.step-box {
  background: rgba(10, 14, 24, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 16px;
}

.step-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  margin-bottom: 8px;
  color: #cbd5e1;
}

.step-box p {
  margin: 0;
  font-size: 0.88rem;
  line-height: 1.5;
  color: #e2e8f0;
}

/* SCREEN OFF → LIFE ON */
.real-doors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.door-card {
  background: rgba(18, 24, 38, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  transition: all 0.25s ease;
}

.door-card:hover {
  transform: translateY(-3px);
  border-color: rgba(0, 240, 255, 0.3);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
}

.door-icon-box {
  margin-bottom: 14px;
}

.door-card h3 {
  font-size: 1.2rem;
  margin: 0 0 10px;
  color: #f8fafc;
}

.door-card p {
  color: #94a3b8;
  font-size: 0.88rem;
  line-height: 1.5;
  margin: 0 0 18px;
  flex-grow: 1;
}

.btn-door-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #f8fafc;
  padding: 10px 16px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 700;
  font-size: 0.85rem;
  transition: all 0.2s ease;
}

.btn-door-action:hover {
  background: rgba(0, 240, 255, 0.15);
  border-color: #00f0ff;
  color: #00f0ff;
}

.btn-door-action.highlight-gold {
  border-color: rgba(212, 175, 55, 0.4);
  color: #d4af37;
}

.btn-door-action.highlight-gold:hover {
  background: rgba(212, 175, 55, 0.15);
  border-color: #d4af37;
  color: #fff;
}

.pg-institutional-banner {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  background: rgba(0, 240, 255, 0.05);
  border: 1px solid rgba(0, 240, 255, 0.2);
  border-radius: 14px;
  padding: 16px 20px;
  font-size: 0.84rem;
  line-height: 1.5;
  color: #cbd5e1;
}

@media (max-width: 640px) {
  .pg-card {
    padding: 24px 16px;
  }
  .compass-rays-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  .breath-anim-circle {
    width: 190px;
    height: 190px;
  }
}
</style>

<!-- SCRIPT DINAMICI COMPATTI E PULITI -->
<script>
// DATI RAGGI BUSSOLA IN JAVASCRIPT
const compassData = <?=json_encode($compassRays, JSON_UNESCAPED_UNICODE)?>;

function switchCompassRay(rayKey) {
  if (!compassData[rayKey]) return;
  const ray = compassData[rayKey];

  // Aggiorna pulsanti attivi
  document.querySelectorAll('.ray-btn').forEach(btn => {
    btn.classList.remove('active');
    btn.setAttribute('aria-selected', 'false');
  });
  const activeBtn = document.getElementById('ray-btn-' + rayKey);
  if (activeBtn) {
    activeBtn.classList.add('active');
    activeBtn.setAttribute('aria-selected', 'true');
  }

  // Aggiorna testi del pannello
  document.getElementById('panelTitle').textContent = ray.name;
  document.getElementById('panelDesc').textContent = ray.desc;
  document.getElementById('panelOsserva').textContent = ray.osserva;
  document.getElementById('panelPratica').textContent = ray.pratica;
  document.getElementById('panelAzione').textContent = ray.azione;

  // Stile dinamico
  const panel = document.getElementById('compassActivePanel');
  panel.style.borderColor = ray.color;
  const iconWrap = document.getElementById('panelIcon');
  iconWrap.style.background = ray.color + '22';
}

// LOGICA RESPIRO 4-7-8
let breathActive = false;
let breathTimer = null;
let breathSecondsRemaining = 60;
let breathPhaseIndex = 0; // 0: inhale (4s), 1: hold (7s), 2: exhale (8s)
let breathPhaseTime = 0;

const breathPhases = [
  { name: 'Inspira', duration: 4, class: 'inhale' },
  { name: 'Trattieni', duration: 7, class: 'hold' },
  { name: 'Espira', duration: 8, class: 'exhale' }
];

function toggleBreathPractice() {
  if (breathActive) {
    pauseBreathPractice();
  } else {
    startBreathPractice();
  }
}

function startBreathPractice() {
  breathActive = true;
  document.getElementById('btnBreathLabel').textContent = 'Pausa';
  breathPhaseIndex = 0;
  breathPhaseTime = 0;
  applyBreathPhase();

  breathTimer = setInterval(() => {
    breathSecondsRemaining--;
    breathPhaseTime++;

    if (breathSecondsRemaining <= 0) {
      finishBreathPractice();
      return;
    }

    document.getElementById('breathCounter').textContent = breathSecondsRemaining + 's';

    // Controllo cambio fase
    const curPhase = breathPhases[breathPhaseIndex];
    if (breathPhaseTime >= curPhase.duration) {
      breathPhaseIndex = (breathPhaseIndex + 1) % breathPhases.length;
      breathPhaseTime = 0;
      applyBreathPhase();
    }
  }, 1000);
}

function applyBreathPhase() {
  const p = breathPhases[breathPhaseIndex];
  const circle = document.getElementById('breathCircle');
  circle.className = 'breath-anim-circle ' + p.class;
  document.getElementById('breathPhase').textContent = p.name;
}

function pauseBreathPractice() {
  breathActive = false;
  clearInterval(breathTimer);
  document.getElementById('btnBreathLabel').textContent = 'Riprendi';
  document.getElementById('breathPhase').textContent = 'In pausa';
  document.getElementById('breathCircle').className = 'breath-anim-circle';
}

function resetBreathPractice() {
  breathActive = false;
  clearInterval(breathTimer);
  breathSecondsRemaining = 60;
  breathPhaseIndex = 0;
  breathPhaseTime = 0;
  document.getElementById('breathCounter').textContent = '60s';
  document.getElementById('breathPhase').textContent = 'Pronto';
  document.getElementById('btnBreathLabel').textContent = 'Avvia Sessione';
  document.getElementById('breathCircle').className = 'breath-anim-circle';
}

function finishBreathPractice() {
  resetBreathPractice();
  document.getElementById('breathPhase').textContent = 'Sessione conclusa ✨';
  setTimeout(() => {
    document.getElementById('breathPhase').textContent = 'Pronto';
  }, 4000);
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
