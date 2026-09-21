</main>
<?php if($u??null):
  $curScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
  <nav class="bottom-nav" aria-label="Navigazione principale">
    <a href="app.php" <?=$curScript==='app.php'?'class="active" aria-current="page"':''?>>
      <?=dx_icon('home', '', 20)?><span><?=h(tr('nav.home','Home'))?></span>
    </a>
    <a href="checkin.php" <?=$curScript==='checkin.php'?'class="active" aria-current="page"':''?>>
      <?=dx_icon('edit', '', 20)?><span>Check-in</span>
    </a>
    <a class="nav-plus" href="journal.php" title="Diario del giorno" <?=$curScript==='journal.php'?'class="active" aria-current="page"':''?>>
      <?=dx_icon('book-open', '', 22)?><span>Diario</span>
    </a>
    <a href="club.php" <?=$curScript==='club.php'?'class="active" aria-current="page"':''?>>
      <?=dx_icon('users', '', 20)?><span>Club</span>
    </a>
    <a href="profile.php" <?=$curScript==='profile.php'?'class="active" aria-current="page"':''?>>
      <?=dx_icon('crown', '', 20)?><span>Io</span>
    </a>
  </nav>
<?php endif;?>

<!-- FOOTER UNIVERSALE DELL'ECOSISTEMA SOVRANO DEPENDEX & OLTRE (LAYOUT ORIZZONTALE 4 COLONNE) -->
<footer class="site-footer" style="padding: 4rem 2rem 2.5rem; background: #1a1a2e; border-top: 1px solid rgba(212, 175, 55, 0.3); position: relative; overflow: hidden; color: #e2e8f0;">

  <!-- ACCENTO ARCOBALENO SUPERIORE -->
  <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #ff3344, #ff7700, #ffd700, #00ff77, #00d4ff, #3a55ff, #b829ff);"></div>

  <div style="max-width: 1280px; margin: 0 auto;">

    <!-- GRIGLIA ORIZZONTALE A 4 COLONNE CON SEPARATORI SOTTILI -->
    <div class="footer-4col-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 32px; padding-bottom: 3.5rem;">

      <!-- COLONNA 1 (IDENTITÀ) -->
      <div class="footer-col" style="display: flex; flex-direction: column; gap: 14px; padding-right: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
          <span style="width: 50px; height: 50px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; padding: 2px; background: linear-gradient(135deg, #00d4ff, #ffd700); box-shadow: 0 0 16px rgba(0, 212, 255, 0.3);">
            <img src="assets/img/dependex-rainbow-badge.jpg" alt="Logo DEPENDEX" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
          </span>
          <div>
            <b style="font-size: 1.45rem; color: #FFFFFF; letter-spacing: 0.04em; font-weight: 900; display: block; line-height: 1.1;">DEPENDEX</b>
            <span style="font-size: 0.82rem; font-weight: 800; color: #d4af37; letter-spacing: 0.1em; text-transform: uppercase;">AL CLUB. COL CLUB.</span>
          </div>
        </div>

        <p style="font-size: 0.86rem; color: #94a3b8; line-height: 1.55; margin: 4px 0 0;">
          Rete ecologico-sociale nazionale di accoglienza, auto-mutuo-aiuto multifamiliare e cammino di sobrietà secondo il Metodo Hudolin.
        </p>

        <div style="margin-top: 4px;">
          <a href="mailto:info@dependex.support" style="color: #00d4ff; text-decoration: none; font-size: 0.88rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
            <?=dx_icon('mail', '', 16)?> info@dependex.support
          </a>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px; font-size: 0.82rem;">
          <span style="color: #64748b; font-weight: 600;">Ecosistema:</span>
          <a href="https://oltre.social" target="_blank" rel="noopener" style="color: #cbd5e1; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
            OLTRE.SOCIAL <?=dx_icon('external-link', '', 12)?>
          </a>
          <span style="color: #475569;">·</span>
          <a href="https://beway.life" target="_blank" rel="noopener" style="color: #cbd5e1; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
            beway.life <?=dx_icon('external-link', '', 12)?>
          </a>
        </div>
      </div>

      <!-- COLONNA 2 (LINK RAPIDI) -->
      <div class="footer-col" style="padding-right: 16px;">
        <h4 style="font-size: 0.88rem; font-weight: 900; color: #ffd700; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('compass', 'text-amber', 16)?> Link Rapidi
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 11px; font-size: 0.88rem;">
          <li><a href="world-club-explorer.php" style="color: #ffd700; font-weight: 750; text-decoration: none;">Trova un Club Territoriale</a></li>
          <li><a href="mappa-club.php" style="color: #67e8f9; font-weight: 750; text-decoration: none;">Mappa 2D Italia (1.768 Club)</a></li>
          <li><a href="piramide-rovesciata.php" style="color: #ffffff; font-weight: 700; text-decoration: none;">Piramide Rovesciata (Organigramma)</a></li>
          <li><a href="metodo.php" style="color: #cbd5e1; text-decoration: none;">Il Metodo Hudolin</a></li>
          <li><a href="parla-con-noi.php" style="color: #25D366; font-weight: 750; text-decoration: none;">Parla con Noi (Ascolto Riservato)</a></li>
          <li><a href="playground.php" style="color: #cbd5e1; text-decoration: none;">Life Playground 6.0</a></li>
          <li><a href="domande-frequenti.php" style="color: #cbd5e1; text-decoration: none;">Domande che vuoi fare</a></li>
        </ul>
      </div>

      <!-- COLONNA 3 (LEGALE & TRASPARENZA) -->
      <div class="footer-col" style="padding-right: 16px;">
        <h4 style="font-size: 0.88rem; font-weight: 900; color: #00d4ff; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('shield-check', 'text-cyan', 16)?> Legale & Trasparenza
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0 0 16px; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="privacy.php" style="color: #cbd5e1; text-decoration: none;">Privacy Policy (GDPR)</a></li>
          <li><a href="terms.php" style="color: #cbd5e1; text-decoration: none;">Termini & Trasparenza ACAT</a></li>
          <li><a href="privacy-center.php" style="color: #cbd5e1; text-decoration: none;">Gestione Consensi & Cookie</a></li>
        </ul>

        <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 12px; font-size: 0.80rem; color: #94a3b8; line-height: 1.5;">
          <b style="color: #ffd700; display: block; margin-bottom: 4px;">Quote Volontarie & Costi Vivi:</b>
          I Club Alcologici Territoriali operano su base 100% volontaria e solidale. Eventuali contributi di partecipazione coprono esclusivamente i costi vivi di accoglienza, locali e materiale didattico.
        </div>
      </div>

      <!-- COLONNA 4 (ISTITUZIONALE) -->
      <div class="footer-col">
        <h4 style="font-size: 0.88rem; font-weight: 900; color: #a78bfa; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('award', 'text-violet', 16)?> Riferimento Istituzionale
        </h4>

        <div style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.5; display: flex; flex-direction: column; gap: 10px;">
          <div>
            <b>Quadro Normativo:</b><br>
            <span style="color: #94a3b8;">Riconoscimento ai sensi della <b>Legge 30 marzo 2001, n. 125</b>, <b>Piano Nazionale Prevenzione (PNP)</b> e monitoraggio <b>Osservatorio Nazionale Alcol (ONA) dell'Istituto Superiore di Sanità (ISS)</b>.</span>
          </div>

          <div style="background: rgba(212, 175, 55, 0.08); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 8px; padding: 8px 12px; color: #ffd700; font-weight: 750; font-size: 0.80rem;">
            Ente Attuatore: ACAT Basso Polesine O.D.V.
          </div>

          <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(0, 255, 119, 0.1); border: 1px solid rgba(0, 255, 119, 0.3); border-radius: 8px; padding: 6px 12px; color: #00ff77; font-weight: 800; font-size: 0.80rem;">
            <?=dx_icon('shield', '', 14)?> Zero Profilazione Invasiva
          </div>
        </div>
      </div>

    </div>

    <!-- SEPARATORE SOTTILE ORIZZONTALE -->
    <div style="height: 1px; background: rgba(255, 255, 255, 0.08); margin-bottom: 24px;"></div>

    <!-- COPYRIGHT IN BASSO CENTRATO -->
    <div style="text-align: center; font-size: 0.82rem; color: #94a3b8; line-height: 1.6;">
      <div>
        &copy; 2026 <b>DEPENDEX</b> · <a href="https://oltre.social" target="_blank" rel="noopener" style="color: #94a3b8; text-decoration: none;">OLTRE.SOCIAL</a>. Tutti i diritti riservati.
      </div>
      <div style="margin-top: 6px; font-size: 0.76rem; color: #64748b;">
        <span>RFC 8058 One-Click Unsubscribe</span>
        <span style="margin: 0 8px;">·</span>
        <span>Metodo Vladimir Hudolin (1928-1996)</span>
        <span style="margin: 0 8px;">·</span>
        <a href="mailto:info@dependex.support" style="color: #00d4ff; text-decoration: none;">info@dependex.support</a>
      </div>
    </div>

  </div>
</footer>

<!-- ==========================================================================
     MOBILE-FIRST SOVEREIGN BOTTOM BAR & SOS GROUNDING DRAWER
     ========================================================================== -->
<?php $currentFile = basename($_SERVER['SCRIPT_NAME'] ?? ''); ?>
<nav class="dx-mobile-bottom-bar" aria-label="Navigazione Mobile Sovrana">
  <a href="index.php" class="dx-bottom-nav-item <?=$currentFile==='index.php'?'active':''?>">
    <?=dx_icon('home', '', 20)?>
    <span>Home</span>
  </a>
  <a href="world-club-explorer.php" class="dx-bottom-nav-item <?=in_array($currentFile, ['world-club-explorer.php','mappa-club.php'], true)?'active':''?>">
    <?=dx_icon('map-pin', '', 20)?>
    <span>Trova Club</span>
  </a>
  <a href="playground.php" class="dx-bottom-nav-item <?=$currentFile==='playground.php'?'active':''?>" style="color: #00d4ff;">
    <?=dx_icon('sparkles', 'text-neon-cyan', 20)?>
    <span>Playground</span>
  </a>
  <a href="parla-con-noi.php" class="dx-bottom-nav-item <?=$currentFile==='parla-con-noi.php'?'active':''?>">
    <?=dx_icon('message-circle', '', 20)?>
    <span>Parla</span>
  </a>
  <a href="javascript:void(0)" class="dx-bottom-nav-item sos-badge" onclick="dxToggleSosModal(true)" aria-label="Apri SOS Calma e respirazione">
    <div class="sos-pulse">
      <?=dx_icon('shield', '', 18)?>
    </div>
    <span style="color:#ff6677;">SOS Calma</span>
  </a>
</nav>

<!-- PULSANTE FLOTTANTE DESKTOP (Nascosto su Mobile) -->
<div class="floating-quick-support" style="position: fixed; bottom: 24px; right: 24px; z-index: 998; display: none;">
  <a href="parla-con-noi.php" 
     class="btn-floating-support" 
     data-funnel-action="CLICK_FLOATING_SUPPORT"
     data-funnel-stage="ACTION"
     style="display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #00f0ff, #0077ff); color: #070a12; font-weight: 800; font-size: 0.86rem; padding: 12px 20px; border-radius: 999px; text-decoration: none; box-shadow: 0 6px 25px rgba(0,240,255,0.4); transition: transform 0.2s ease;">
    <?=dx_icon('message-circle', '', 18)?>
    <span>Ascolto & Orientamento Riservato</span>
  </a>
</div>
<style>
@media (min-width: 769px) {
  .floating-quick-support { display: block !important; }
}
</style>

<!-- MODAL SOS: DE-ESCALATION PSICOLOGICA & RESPIRAZIONE HUDOLIN (4-7-8) -->
<div id="dx-sos-modal" class="dx-sos-modal" role="dialog" aria-modal="true" aria-labelledby="dx-sos-title" onclick="if(event.target===this) dxToggleSosModal(false)">
  <div class="dx-sos-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
      <span style="font-size: 0.76rem; font-weight: 850; letter-spacing: 0.1em; color: #ff6677; text-transform: uppercase;">
        Spazio di De-Escalation & Ascolto
      </span>
      <button type="button" onclick="dxToggleSosModal(false)" style="background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; padding: 4px 8px; line-height: 1;">&times;</button>
    </div>

    <h3 id="dx-sos-title" style="font-size: 1.35rem; color: #ffffff; font-weight: 900; margin-bottom: 6px; letter-spacing: -0.01em;">
      Un momento difficile? Non sei solo.
    </h3>
    <p style="font-size: 0.88rem; color: #cbd5e1; margin-bottom: 16px; line-height: 1.5;">
      Non devi vincere tutta la vita oggi. Solo i prossimi cinque minuti. Segui il cerchio di respirazione:
    </p>

    <!-- Cerchio di respirazione interattivo 4-7-8 -->
    <div class="dx-breath-circle dx-breathing" id="dx-breath-circle">
      <span id="dx-breath-text">Respira</span>
    </div>

    <div style="background: rgba(255, 255, 255, 0.04); border-radius: 12px; padding: 10px 14px; margin-bottom: 20px; font-size: 0.82rem; color: #94a3b8;">
      <b style="color: #ffd700;">Tecnica 4-7-8:</b> 4 sec Inspira dal naso · 7 sec Trattieni l'aria · 8 sec Espira lentamente dalla bocca.
    </div>

    <!-- ASCOLTO VOCALE EMPATICO MAIEUTICO ON-DEVICE (100% PRIVATO NEL BROWSER) -->
    <div style="background: rgba(0, 240, 255, 0.05); border: 1px dashed rgba(0, 240, 255, 0.35); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; text-align: center;">
      <div style="font-size: 0.82rem; color: #94a3b8; margin-bottom: 8px;">
        Vuoi semplicemente dire a voce cosa provi adesso? (100% privato nel tuo telefono)
      </div>
      <button type="button" id="dxVoiceSosBtn" onclick="if(window.startVoiceSos) window.startVoiceSos();" style="background: rgba(0, 240, 255, 0.15); border: 1px solid #00f0ff; color: #00f0ff; border-radius: 999px; padding: 8px 18px; font-weight: 800; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.1rem;">🎙️</span>
        <span id="dxVoiceStatusTxt">Parla liberamente a voce</span>
      </button>
      <div id="dxVoiceTranscriptBox" style="display: none; margin-top: 10px; font-size: 0.84rem; text-align: left; background: rgba(0,0,0,0.3); border-radius: 8px; padding: 10px; color: #e2e8f0; line-height: 1.4;">
        <div id="dxUserSpeech" style="color: #67e8f9; font-style: italic; margin-bottom: 6px;"></div>
        <div id="dxMaieuticReply" style="color: #a7f3d0; font-weight: 650;"></div>
      </div>
    </div>

    <!-- Azioni Rapide a 1 Tocco -->
    <div style="display: flex; flex-direction: column; gap: 10px;">
      <a href="tel:800632000" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px 16px; background: rgba(37,211,102,0.18); border: 1px solid #25d366; color: #25d366; border-radius: 12px; font-weight: 850; font-size: 0.95rem; text-decoration: none;">
        <?=dx_icon('phone', '', 18)?>
        <span>Chiama Telefono Verde Alcol (800 632 000)</span>
      </a>

      <a href="parla-con-noi.php" onclick="dxToggleSosModal(false)" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px 16px; background: linear-gradient(135deg, #00f0ff, #0077ff); color: #070a12; border-radius: 12px; font-weight: 850; font-size: 0.95rem; text-decoration: none;">
        <?=dx_icon('message-circle', '', 18)?>
        <span>Scrivi a una Persona del Club (Anonimo)</span>
      </a>

      <a href="world-club-explorer.php" onclick="dxToggleSosModal(false)" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 10px 16px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #ffffff; border-radius: 12px; font-weight: 750; font-size: 0.85rem; text-decoration: none;">
        <?=dx_icon('map-pin', '', 16)?>
        <span>Trova il Club più vicino a te stasera</span>
      </a>
    </div>

    <button type="button" onclick="dxToggleSosModal(false)" style="margin-top: 16px; background: none; border: none; color: #64748b; font-size: 0.82rem; cursor: pointer; text-decoration: underline;">
      Sto meglio, torna alla pagina
    </button>
  </div>
</div>

<script>
function dxToggleSosModal(show) {
  const modal = document.getElementById('dx-sos-modal');
  if (!modal) return;
  if (show) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    startBreathingCycle();
    if (window.DxTelemetry) {
      window.DxTelemetry.logAction('OPEN_SOS_MODAL', { page: window.location.pathname });
    }
  } else {
    modal.classList.remove('active');
    document.body.style.overflow = '';
    stopBreathingCycle();
  }
}

let breathInterval = null;
function startBreathingCycle() {
  const txt = document.getElementById('dx-breath-text');
  if (!txt) return;
  let phase = 0; // 0 = Inspira (4s), 1 = Trattieni (7s), 2 = Espira (8s)
  const steps = [
    { label: 'Inspira (4s)', color: '#00d4ff', duration: 4000 },
    { label: 'Trattieni (7s)', color: '#ffd700', duration: 7000 },
    { label: 'Espira (8s)', color: '#00ff77', duration: 8000 }
  ];
  
  function nextStep() {
    const step = steps[phase];
    txt.textContent = step.label;
    txt.style.color = step.color;
    phase = (phase + 1) % steps.length;
    breathInterval = setTimeout(nextStep, step.duration);
  }
  nextStep();
}

function stopBreathingCycle() {
  if (breathInterval) {
    clearTimeout(breathInterval);
    breathInterval = null;
  }
}
</script>

<script src="assets/js/app.js?v=<?=filemtime(__DIR__.'/assets/js/app.js')?>"></script>
<script src="assets/js/universal-chat-ai.js?v=<?=filemtime(__DIR__.'/assets/js/universal-chat-ai.js')?>" data-brand="<?=h(site_brand()['name'])?>" data-domain="<?=h(site_brand()['domain'])?>"></script>
<script src="assets/js/universal-cart-checkout.js?v=<?=filemtime(__DIR__.'/assets/js/universal-cart-checkout.js')?>" data-brand="<?=h(site_brand()['name'])?>"></script>
<script src="assets/js/dx-telemetry.js?v=<?=filemtime(__DIR__.'/assets/js/dx-telemetry.js')?>"></script>
<script src="assets/js/dx-voice-sos.js?v=<?=filemtime(__DIR__.'/assets/js/dx-voice-sos.js')?>"></script>
<script src="assets/js/dx-micro-checkin.js?v=<?=filemtime(__DIR__.'/assets/js/dx-micro-checkin.js')?>"></script>
<script src="assets/js/dx-local-reminders.js?v=<?=filemtime(__DIR__.'/assets/js/dx-local-reminders.js')?>"></script>
<script src="assets/js/dx-pwa-companion.js?v=<?=filemtime(__DIR__.'/assets/js/dx-pwa-companion.js')?>"></script>
</body>
</html>