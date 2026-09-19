<?php
/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * OMNI-WELFARE GAMIFICATION ENGINE 6.0 — LIFE PLAYGROUND
 * 
 * Interfaccia maieutica, giocabile, mobile-first per l'esplorazione del benessere,
 * delle pratiche contemplative, della natura e della comunità.
 * 
 * VINCOLI DEONTOLOGICI ASSOLUTI:
 * - Zero diagnosi cliniche, zero promesse terapeutiche o mediche.
 * - Zero "wellness score", zero classifiche o competizione.
 * - Non gamificare la sofferenza (nessun punteggio per dolore, ansia o traumi).
 * - Nessuno streak punitivo ("Bentornato").
 * - Attribuzione trasparente delle fonti (Hudolin, H+, ABC, BetterWay, Veda, Maslow).
 */

require_once __DIR__ . '/_inc.php';
require_once __DIR__ . '/modules/gamification/OmniWelfareGamificationEngine.php';

$pageTitle = "Life Playground 6.0 · Esplora, Gioca, Connettiti";
$metaDesc = "Un ambiente semplice e interattivo per ritrovare respiro, equilibrio e comunità. Nessun termine tecnico, nessuna diagnosi. Solo micro-esperienze a misura d'uomo.";

$engine = new OmniWelfareGamificationEngine();
$feelings = $engine->getFeelingEntries();
$quests = $engine->getMicroQuests();
$taxonomy = $engine->getMethodTaxonomy();
$levels = $engine->getParticipationLevels();

// Selezione iniziale feeling tramite query param opzionale
$selectedFeelingId = trim($_GET['feeling'] ?? '');
if (!$selectedFeelingId || !isset($feelings[$selectedFeelingId])) {
    $selectedFeelingId = null;
}
$activeRecommendations = $selectedFeelingId ? $engine->resolveExperienceByFeeling($selectedFeelingId) : [];

include __DIR__ . '/_header.php';
?>

<main id="mainContent" class="playground-master-wrap">
  <div class="playground-hero">
    <div class="container-mobile">
      <div class="playground-badge">
        <span class="badge-dot"></span> LIFE PLAYGROUND 6.0
      </div>
      <h1 class="playground-title">Come ti senti oggi?</h1>
      <p class="playground-subtitle">
        Non devi avere già le risposte. Non servono manuali o parole difficili.<br>
        Scegli da dove partire. Un respiro, una riflessione o un passo verso la comunità.
      </p>

      <!-- SELETTORE MAIEUTICO DELLE 11 PORTE D'INGRESSO -->
      <div class="feeling-grid" role="region" aria-label="Porte di ingresso esperienziali">
        <?php foreach ($feelings as $fId => $f): 
          $isActive = ($fId === $selectedFeelingId);
        ?>
          <a href="playground.php?feeling=<?=urlencode($fId)?>#focus-area" 
             class="feeling-card <?=$isActive ? 'active' : ''?>"
             style="--accent-color: <?=$f['color']?>;"
             title="<?=h($f['description'])?>">
            <span class="feeling-icon"><?=$f['icon']?></span>
            <span class="feeling-label"><?=h($f['label'])?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($selectedFeelingId && !empty($activeRecommendations)): ?>
        <div id="focus-area" class="focus-recommendation-panel">
          <div class="focus-header">
            <div class="focus-intro">
              <span class="focus-pill" style="background:<?=$feelings[$selectedFeelingId]['color']?>22; color:<?=$feelings[$selectedFeelingId]['color']?>; border:1px solid <?=$feelings[$selectedFeelingId]['color']?>66;">
                <?=$feelings[$selectedFeelingId]['icon']?> <?=h($feelings[$selectedFeelingId]['label'])?>
              </span>
              <h3>Partiamo da qui</h3>
              <p>Tre micro-passi suggeriti per questo momento. Nessuna imposizione, scegli tu.</p>
            </div>
            <a href="playground.php#mainContent" class="btn-clear-selection" title="Vedi tutte le esperienze">Mostra tutto ✕</a>
          </div>

          <div class="recommendation-cards-grid">
            <?php foreach ($activeRecommendations['quests'] as $recQuest): ?>
              <div class="rec-quest-card" style="border-top:3px solid <?=$recQuest['color']?>;">
                <div class="rec-quest-top">
                  <span class="rec-quest-icon"><?=$recQuest['icon']?></span>
                  <span class="rec-quest-duration"><?=h($recQuest['duration'])?></span>
                </div>
                <h4><?=h($recQuest['title'])?></h4>
                <p><?=h($recQuest['prompt'])?></p>
                <div class="rec-quest-meta">
                  <span class="rec-source-tag">Fonte: <?=h($recQuest['source_attribution'])?></span>
                  <button type="button" class="btn-launch-quest" onclick="openQuestModal('<?=h($recQuest['id'])?>')">
                    Sperimenta <?=dx_icon('arrow-right', '', 14)?>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- SEZIONE INTERATTIVA: 60 SECONDI DI RESPIRO CONSAPEVOLE -->
  <section class="playground-tool-section" id="breath-tool">
    <div class="container-mobile">
      <div class="section-card breath-card">
        <div class="section-card-header">
          <div class="badge-human"><span class="dot"></span> PRATICA SOMATICA DEL RESPIRO</div>
          <h2>60 Secondi di Respiro</h2>
          <p>Uno spazio per rallentare il ritmo. Segui il cerchio che si espande e si rilassa.</p>
        </div>

        <div class="breath-interactive-stage">
          <div class="breath-visualizer-container">
            <div class="breath-circle" id="breathCircle">
              <span class="breath-instruction" id="breathStateText">Pronto</span>
              <span class="breath-timer" id="breathCountdown">60s</span>
            </div>
          </div>

          <div class="breath-controls">
            <button type="button" id="btnToggleBreath" class="btn-breath-primary" onclick="toggleBreathExercise()">
              Avvia 60 Secondi
            </button>
            <button type="button" id="btnResetBreath" class="btn-breath-secondary" onclick="resetBreathExercise()">
              Ripristina
            </button>
          </div>

          <div class="breath-disclaimer">
            <small>
              <?=dx_icon('shield', 'text-neon-cyan', 13)?>
              Questa è una pratica di consapevolezza somatica (Fonte: <em>Metodo H+ — Mirco Pregnolato</em>). Se avverti disagio o giramenti di testa, interrompila naturalmente. Non costituisce terapia medica.
            </small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SEZIONE INTERATTIVA: LE 3 CARTE DELLA GRATITUDINE -->
  <section class="playground-tool-section" id="gratitude-tool">
    <div class="container-mobile">
      <div class="section-card gratitude-card">
        <div class="section-card-header">
          <div class="badge-human"><span class="dot"></span> MINDFULNESS & RIFLESSIONE PRIVATA</div>
          <h2>Le Tre Carte della Gratitudine</h2>
          <p>Un momento di ancoraggio. Scegli una carta e scrivi una sola parola o pensiero. Resta solo sul tuo dispositivo.</p>
        </div>

        <div class="gratitude-cards-container">
          <!-- CARTA 1 -->
          <div class="grat-card" onclick="selectGratCard(this, 'persona')">
            <div class="grat-icon">🤝</div>
            <h3>Una Persona</h3>
            <p>Qualcuno che oggi ti ha donato un sorriso, un ascolto o una presenza.</p>
            <input type="text" class="grat-input" placeholder="Scrivi un nome o pensiero..." onclick="event.stopPropagation()">
          </div>

          <!-- CARTA 2 -->
          <div class="grat-card" onclick="selectGratCard(this, 'cosa')">
            <div class="grat-icon">☕</div>
            <h3>Una Piccola Cosa</h3>
            <p>Un sapore, un momento di quiete, una luce, un rifugio sicuro.</p>
            <input type="text" class="grat-input" placeholder="Scrivi una piccola cosa..." onclick="event.stopPropagation()">
          </div>

          <!-- CARTA 3 -->
          <div class="grat-card" onclick="selectGratCard(this, 'momento')">
            <div class="grat-icon">🌅</div>
            <h3>Un Momento</h3>
            <p>Un istante della tua giornata in cui ti sei sentito presente o grato.</p>
            <input type="text" class="grat-input" placeholder="Scrivi questo momento..." onclick="event.stopPropagation()">
          </div>
        </div>

        <div class="gratitude-footer">
          <button type="button" class="btn-save-reflection" onclick="savePrivateReflection()">
            Conserva nel Diario Privato
          </button>
          <span id="gratitudeFeedback" class="gratitude-feedback"></span>
        </div>
      </div>
    </div>
  </section>

  <!-- SEZIONE MICRO-QUESTS: SCREEN OFF → LIFE ON -->
  <section class="playground-tool-section" id="quests-grid-tool">
    <div class="container-mobile">
      <div class="section-card">
        <div class="section-card-header">
          <div class="badge-human"><span class="dot"></span> MICRO-ESPERIENZE GUIDATE</div>
          <h2>Missioni dal Digitale al Mondo Reale</h2>
          <p>Dalla consapevolezza personale all'incontro sul territorio. Piccoli passi concreti.</p>
        </div>

        <div class="quests-full-grid">
          <?php foreach ($quests as $q): ?>
            <div class="quest-item-box" style="border-left:4px solid <?=$q['color']?>;">
              <div class="quest-item-top">
                <span class="quest-emoji"><?=$q['icon']?></span>
                <span class="quest-badge-dur"><?=$q['duration']?></span>
              </div>
              <h3 class="quest-item-title"><?=h($q['title'])?></h3>
              <p class="quest-item-prompt"><?=h($q['prompt'])?></p>
              
              <div class="quest-step-bullets">
                <?php foreach ($q['steps'] as $s): ?>
                  <div class="quest-step-row">
                    <span class="step-check">✓</span>
                    <span><?=h($s)?></span>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="quest-item-cta-box">
                <a href="<?=h($q['action_cta']['link'])?>" class="btn-quest-action">
                  <?=h($q['action_cta']['label'])?> <?=dx_icon('external-link', '', 12)?>
                </a>
                <span class="quest-source-pill">Fonte: <?=h($q['source_attribution'])?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- SEZIONE ETICA: TASSONOMIA TRASPARENTE DELLE LENTI E METODI -->
  <section class="playground-tool-section" id="taxonomy-section">
    <div class="container-mobile">
      <div class="section-card taxonomy-card">
        <div class="section-card-header">
          <div class="badge-human"><span class="dot"></span> DEONTOLOGIA & SEPARAZIONE RIGOROSA DELLE FONTI</div>
          <h2>Un Ecosistema di Lenti, Nessun Minestrone</h2>
          <p>
            Ogni tradizione, metodo o approccio mantiene la propria precisa identità. Nessuna disciplina viene fusa con un'altra né spacciata per terapia clinica.
          </p>
        </div>

        <div class="taxonomy-table-wrap">
          <table class="taxonomy-table">
            <thead>
              <tr>
                <th>Lente / Metodo</th>
                <th>Origine & Fonte</th>
                <th>Funzione in DEPENDEX</th>
                <th>Cosa NON è</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($taxonomy as $t): ?>
                <tr>
                  <td><strong><?=h($t['name'])?></strong></td>
                  <td><?=h($t['source'])?></td>
                  <td><?=h($t['role_in_dependex'])?></td>
                  <td class="text-danger-notice"><?=h($t['what_it_is_not'])?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <!-- SEZIONE PARTECIPAZIONE COMUNITARIA NON COMPETITIVA -->
  <section class="playground-tool-section" id="participation-section">
    <div class="container-mobile">
      <div class="section-card participation-card">
        <div class="section-card-header">
          <div class="badge-human"><span class="dot"></span> CRESCITA ETICA & COMUNITARIA</div>
          <h2>I Livelli di Partecipazione alla Piattaforma</h2>
          <p>
            I livelli rappresentano la tua familiarità e presenza nella comunità, <strong>mai il tuo valore come essere umano</strong>. Nessuna classifica, nessun punteggio di sofferenza.
          </p>
        </div>

        <div class="levels-step-grid">
          <?php foreach ($levels as $lvl): ?>
            <div class="level-card-step">
              <span class="level-num">Livello <?=$lvl['level']?></span>
              <h3 class="level-name"><?=h($lvl['title'])?></h3>
              <p class="level-desc"><?=h($lvl['meaning'])?></p>
              <div class="level-req">Azione: <?=h($lvl['requirement'])?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
</main>

<style>
/* MASTER CSS: LIFE PLAYGROUND 6.0 (MOBILE FIRST ASSOLUTO) */
.playground-master-wrap {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
  box-sizing: border-box;
  padding: 16px 12px 60px;
  background: radial-gradient(circle at 50% 0%, rgba(20, 30, 48, 0.4) 0%, rgba(7, 10, 18, 0.95) 75%);
  color: #f1f5f9;
}

.container-mobile {
  width: 100%;
  max-width: 1080px;
  margin: 0 auto;
  box-sizing: border-box;
}

.playground-hero {
  text-align: center;
  margin-bottom: 32px;
}

.playground-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 14px;
  border-radius: 999px;
  background: rgba(0, 240, 255, 0.1);
  border: 1px solid rgba(0, 240, 255, 0.35);
  color: #00f0ff;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  margin-bottom: 12px;
}

.playground-badge .badge-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #00f0ff;
  box-shadow: 0 0 8px #00f0ff;
}

.playground-title {
  font-size: clamp(1.8rem, 5vw, 2.7rem);
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 10px;
  line-height: 1.2;
}

.playground-subtitle {
  font-size: clamp(0.95rem, 2.5vw, 1.15rem);
  color: #94a3b8;
  max-width: 680px;
  margin: 0 auto 24px;
  line-height: 1.5;
}

/* 11 FEELING CARDS GRID */
.feeling-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
  gap: 10px;
  margin-bottom: 24px;
}

.feeling-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 14px 10px;
  border-radius: 16px;
  background: rgba(18, 24, 38, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  text-decoration: none;
  color: #cbd5e1;
  transition: all 0.25s ease;
  min-height: 84px;
}

.feeling-card:hover, .feeling-card:focus {
  transform: translateY(-2px);
  border-color: var(--accent-color);
  background: rgba(26, 36, 56, 0.95);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.4);
}

.feeling-card.active {
  border-color: var(--accent-color);
  background: rgba(26, 36, 56, 1);
  box-shadow: 0 0 16px var(--accent-color)33;
}

.feeling-icon {
  font-size: 1.5rem;
  margin-bottom: 6px;
}

.feeling-label {
  font-size: 0.78rem;
  font-weight: 600;
  line-height: 1.25;
}

/* FOCUS RECOMMENDATION PANEL */
.focus-recommendation-panel {
  margin: 20px 0 32px;
  padding: 20px;
  border-radius: 20px;
  background: rgba(14, 20, 32, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.12);
  text-align: left;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.45);
}

.focus-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 18px;
}

.focus-intro h3 {
  margin: 8px 0 4px;
  font-size: 1.3rem;
  color: #fff;
}

.focus-intro p {
  margin: 0;
  font-size: 0.88rem;
  color: #94a3b8;
}

.focus-pill {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
}

.btn-clear-selection {
  font-size: 0.8rem;
  color: #94a3b8;
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.05);
}

.recommendation-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 14px;
}

.rec-quest-card {
  background: rgba(20, 28, 44, 0.8);
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.rec-quest-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.rec-quest-icon {
  font-size: 1.4rem;
}

.rec-quest-duration {
  font-size: 0.72rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 2px 8px;
  border-radius: 6px;
  color: #e2e8f0;
}

.rec-quest-card h4 {
  margin: 0 0 6px;
  font-size: 1.05rem;
  color: #ffffff;
}

.rec-quest-card p {
  font-size: 0.85rem;
  color: #94a3b8;
  line-height: 1.4;
  margin: 0 0 14px;
}

.rec-quest-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.72rem;
}

.rec-source-tag {
  color: #64748b;
}

.btn-launch-quest {
  background: #00f0ff;
  color: #070a12;
  border: none;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 6px 12px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

/* GENERIC SECTION CARD */
.playground-tool-section {
  margin-bottom: 30px;
}

.section-card {
  background: rgba(14, 20, 32, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 24px 18px;
  box-sizing: border-box;
}

.section-card-header {
  margin-bottom: 20px;
  text-align: left;
}

.section-card-header h2 {
  font-size: clamp(1.3rem, 3.5vw, 1.8rem);
  font-weight: 800;
  color: #ffffff;
  margin: 6px 0 6px;
}

.section-card-header p {
  margin: 0;
  font-size: 0.9rem;
  color: #94a3b8;
  line-height: 1.45;
}

/* BREATH VISUALIZER */
.breath-interactive-stage {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 10px 0 20px;
}

.breath-visualizer-container {
  position: relative;
  width: 220px;
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 16px 0 24px;
}

.breath-circle {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 240, 255, 0.25) 0%, rgba(0, 240, 255, 0.05) 70%);
  border: 2px solid #00f0ff;
  box-shadow: 0 0 25px rgba(0, 240, 255, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: transform 3.8s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 3.8s ease;
}

.breath-circle.inhale {
  transform: scale(1.65);
  box-shadow: 0 0 50px rgba(0, 240, 255, 0.6);
  background: radial-gradient(circle, rgba(0, 240, 255, 0.45) 0%, rgba(0, 240, 255, 0.1) 70%);
}

.breath-circle.exhale {
  transform: scale(0.9);
  box-shadow: 0 0 15px rgba(0, 240, 255, 0.15);
  background: radial-gradient(circle, rgba(0, 240, 255, 0.15) 0%, rgba(0, 240, 255, 0.02) 70%);
}

.breath-circle.hold {
  transform: scale(1.65);
  box-shadow: 0 0 35px rgba(224, 169, 109, 0.5);
  border-color: #e0a96d;
}

.breath-instruction {
  font-size: 0.95rem;
  font-weight: 700;
  color: #ffffff;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.breath-timer {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-top: 4px;
}

.breath-controls {
  display: flex;
  gap: 12px;
  margin-bottom: 18px;
}

.btn-breath-primary {
  background: linear-gradient(135deg, #00f0ff, #0099ff);
  color: #070a12;
  border: none;
  font-weight: 700;
  padding: 12px 24px;
  border-radius: 999px;
  font-size: 0.95rem;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 240, 255, 0.3);
  min-height: 44px;
}

.btn-breath-secondary {
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.15);
  font-weight: 600;
  padding: 12px 18px;
  border-radius: 999px;
  font-size: 0.9rem;
  cursor: pointer;
  min-height: 44px;
}

.breath-disclaimer {
  max-width: 580px;
  color: #64748b;
  line-height: 1.4;
  margin-top: 8px;
}

/* GRATITUDE SECTION */
.gratitude-cards-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 16px;
  margin: 20px 0;
}

.grat-card {
  background: rgba(20, 28, 44, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.25s ease;
}

.grat-card:hover {
  border-color: #ffd700;
  transform: translateY(-2px);
}

.grat-icon {
  font-size: 2rem;
  margin-bottom: 10px;
}

.grat-card h3 {
  margin: 0 0 6px;
  font-size: 1.15rem;
  color: #fff;
}

.grat-card p {
  margin: 0 0 14px;
  font-size: 0.85rem;
  color: #94a3b8;
  line-height: 1.4;
}

.grat-input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  background: rgba(7, 10, 18, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #fff;
  font-size: 0.88rem;
  box-sizing: border-box;
}

.grat-input:focus {
  border-color: #ffd700;
  outline: none;
}

.gratitude-footer {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
  margin-top: 10px;
}

.btn-save-reflection {
  background: #ffd700;
  color: #070a12;
  border: none;
  font-weight: 700;
  padding: 10px 20px;
  border-radius: 999px;
  font-size: 0.88rem;
  cursor: pointer;
  min-height: 44px;
}

.gratitude-feedback {
  font-size: 0.85rem;
  color: #00ff77;
}

/* FULL QUESTS GRID */
.quests-full-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-top: 18px;
}

.quest-item-box {
  background: rgba(20, 28, 44, 0.8);
  border-radius: 14px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.quest-item-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.quest-emoji {
  font-size: 1.6rem;
}

.quest-badge-dur {
  font-size: 0.72rem;
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  padding: 2px 8px;
  border-radius: 6px;
  font-weight: 600;
}

.quest-item-title {
  margin: 0 0 6px;
  font-size: 1.1rem;
  color: #ffffff;
}

.quest-item-prompt {
  font-size: 0.88rem;
  color: #94a3b8;
  line-height: 1.45;
  margin: 0 0 12px;
}

.quest-step-bullets {
  margin-bottom: 16px;
}

.quest-step-row {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 0.82rem;
  color: #cbd5e1;
  margin-bottom: 6px;
  line-height: 1.35;
}

.step-check {
  color: #00ff77;
  font-weight: bold;
}

.quest-item-cta-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  padding-top: 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.btn-quest-action {
  font-size: 0.78rem;
  font-weight: 700;
  color: #00f0ff;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  border-radius: 8px;
  background: rgba(0, 240, 255, 0.1);
  min-height: 38px;
}

.quest-source-pill {
  font-size: 0.68rem;
  color: #64748b;
}

/* TAXONOMY TABLE */
.taxonomy-table-wrap {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  margin-top: 14px;
}

.taxonomy-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
  text-align: left;
}

.taxonomy-table th, .taxonomy-table td {
  padding: 10px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.taxonomy-table th {
  background: rgba(255, 255, 255, 0.04);
  color: #e2e8f0;
  font-weight: 700;
}

.taxonomy-table td {
  color: #94a3b8;
}

.text-danger-notice {
  color: #f87171 !important;
}

/* PARTICIPATION LEVELS */
.levels-step-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 14px;
  margin-top: 16px;
}

.level-card-step {
  background: rgba(20, 28, 44, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 16px;
}

.level-num {
  font-size: 0.7rem;
  text-transform: uppercase;
  color: #00f0ff;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.level-name {
  margin: 4px 0 6px;
  font-size: 1.05rem;
  color: #fff;
}

.level-desc {
  font-size: 0.82rem;
  color: #94a3b8;
  line-height: 1.4;
  margin: 0 0 10px;
}

.level-req {
  font-size: 0.72rem;
  color: #ffd700;
  font-weight: 600;
}

@media (max-width: 600px) {
  .feeling-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>

<script>
// BREATH SIMULATOR LOGIC (4s INHALE, 2s HOLD, 4s EXHALE, 2s PAUSE)
let breathTimer = null;
let breathTimeRemaining = 60;
let breathState = 'idle'; // 'inhale', 'hold', 'exhale', 'pause'
let breathCycleTimer = null;

function toggleBreathExercise() {
  const btn = document.getElementById('btnToggleBreath');
  if (breathTimer) {
    pauseBreathExercise();
    btn.textContent = 'Riprendi';
  } else {
    startBreathExercise();
    btn.textContent = 'Pausa';
  }
}

function startBreathExercise() {
  const circle = document.getElementById('breathCircle');
  const stateTxt = document.getElementById('breathStateText');
  const timerTxt = document.getElementById('breathCountdown');

  runBreathCycle();

  breathTimer = setInterval(() => {
    breathTimeRemaining--;
    timerTxt.textContent = breathTimeRemaining + 's';
    if (breathTimeRemaining <= 0) {
      finishBreathExercise();
    }
  }, 1000);
}

function runBreathCycle() {
  const circle = document.getElementById('breathCircle');
  const stateTxt = document.getElementById('breathStateText');

  // FASE 1: INSPIRA (4s)
  circle.className = 'breath-circle inhale';
  stateTxt.textContent = 'Inspira';

  breathCycleTimer = setTimeout(() => {
    if (!breathTimer) return;
    // FASE 2: TRATTIENI DOLCEMENTE (2s)
    circle.className = 'breath-circle hold';
    stateTxt.textContent = 'Trattieni';

    breathCycleTimer = setTimeout(() => {
      if (!breathTimer) return;
      // FASE 3: ESPIRA LENTAMENTE (4s)
      circle.className = 'breath-circle exhale';
      stateTxt.textContent = 'Espira';

      breathCycleTimer = setTimeout(() => {
        if (!breathTimer) return;
        // FASE 4: PAUSA NATURALE (2s)
        circle.className = 'breath-circle';
        stateTxt.textContent = 'Pausa';

        breathCycleTimer = setTimeout(() => {
          if (!breathTimer) return;
          runBreathCycle();
        }, 2000);
      }, 4000);
    }, 2000);
  }, 4000);
}

function pauseBreathExercise() {
  clearInterval(breathTimer);
  clearTimeout(breathCycleTimer);
  breathTimer = null;
}

function resetBreathExercise() {
  pauseBreathExercise();
  breathTimeRemaining = 60;
  document.getElementById('breathCountdown').textContent = '60s';
  document.getElementById('breathStateText').textContent = 'Pronto';
  document.getElementById('breathCircle').className = 'breath-circle';
  document.getElementById('btnToggleBreath').textContent = 'Avvia 60 Secondi';
}

function finishBreathExercise() {
  resetBreathExercise();
  document.getElementById('breathStateText').textContent = 'Fatto ✨';
  setTimeout(() => {
    document.getElementById('breathStateText').textContent = 'Pronto';
  }, 3000);
}

// GRATITUDE LOCAL STORAGE PERSISTENCE
function selectGratCard(cardEl, cardType) {
  const inp = cardEl.querySelector('.grat-input');
  if (inp) inp.focus();
}

function savePrivateReflection() {
  const inputs = document.querySelectorAll('.grat-input');
  const reflections = [];
  inputs.forEach(inp => {
    if (inp.value.trim().length > 0) {
      reflections.push(inp.value.trim());
    }
  });

  const feedback = document.getElementById('gratitudeFeedback');
  if (reflections.length === 0) {
    feedback.textContent = 'Scrivi almeno un pensiero su una carta.';
    feedback.style.color = '#f87171';
    return;
  }

  // Salvataggio sicuro in localStorage locale (anonimo e riservato)
  const existing = JSON.parse(localStorage.getItem('dependex_gratitude_log') || '[]');
  existing.push({
    date: new Date().toISOString(),
    entries: reflections
  });
  localStorage.setItem('dependex_gratitude_log', JSON.stringify(existing));

  feedback.textContent = 'Riflessione custodita nel tuo dispositivo.';
  feedback.style.color = '#00ff77';
  setTimeout(() => {
    feedback.textContent = '';
  }, 4000);
}

function openQuestModal(questId) {
  // Se esiste l'ancora nel tool o navigazione
  window.location.hash = 'quests-grid-tool';
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
