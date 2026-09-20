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

<!-- FOOTER UNIVERSALE DELL'ECOSISTEMA SOVRANO DEPENDEX & OLTRE -->
<footer class="site-footer" style="padding: 56px 20px 36px; background: rgba(8, 11, 20, 0.98); border-top: 2px solid rgba(212, 175, 55, 0.35); position: relative; overflow: hidden; box-shadow: 0 -10px 40px rgba(0,0,0,0.6);">

  <!-- ACCENTO ARCOBALENO SUPERIORE -->
  <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #ff3344, #ff7700, #ffd700, #00ff77, #00d4ff, #3a55ff, #b829ff);"></div>

  <div style="max-width: 1280px; margin: 0 auto;">

    <!-- TOP BRAND BAR: IDENTITA' & CONTATTO DIRETTO -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 24px; padding-bottom: 32px; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
      
      <div style="display: flex; align-items: center; gap: 16px;">
        <span class="brand-mark-rainbow" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; padding: 2px; background: linear-gradient(135deg, #ff3344, #00d4ff, #b829ff);">
          <img src="assets/img/dependex-rainbow-badge.jpg" alt="Logo DEPENDEX" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </span>
        <div>
          <div style="display: flex; align-items: baseline; gap: 8px;">
            <b style="font-size: 1.45rem; color: #FFFFFF; letter-spacing: 0.05em; font-weight: 900;"><?=h(site_brand()['name'])?></b>
            <span style="font-size: 0.8rem; font-weight: 850; color: #f59e0b; letter-spacing: 0.1em; text-transform: uppercase;"><?=h(APP_PAYOFF)?></span>
          </div>
          <div class="badge-neon-rainbow" style="font-size: 0.68rem; padding: 2px 10px; margin-top: 4px; display: inline-flex;">
            <span class="dot"></span>
            <span class="text-rainbow">ECOSISTEMA SOVRANO · METODO HUDOLIN · RETE 542 CLUB</span>
          </div>
        </div>
      </div>

      <!-- CONTATTI RAPIDI GOVERNANCE & ACCOGLIENZA -->
      <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
        <a href="parla-con-noi.php" 
           class="btn small" 
           style="background: rgba(37,211,102,0.15); border: 1px solid #25D366; color: #25D366; font-weight: 750; font-size: 0.82rem; padding: 8px 16px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
          <?=dx_icon('message-circle', '', 15)?>
          <span>Segreteria di Accoglienza · Parla con Noi</span>
        </a>

        <a href="mailto:info@dependex.support" 
           class="btn small" 
           style="background: rgba(0, 212, 255, 0.12); border: 1px solid rgba(0, 212, 255, 0.5); color: #00d4ff; font-weight: 750; font-size: 0.82rem; padding: 8px 16px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
          <?=dx_icon('mail', '', 15)?>
          <span>info@dependex.support</span>
        </a>
      </div>

    </div>

    <!-- GRIGLIA UNIVERSALE A 4 COLONNE -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr)); gap: 36px; padding: 40px 0 32px;">

      <!-- COLONNA 1: RETE & TERRITORIO -->
      <div>
        <h4 style="font-size: 0.85rem; font-weight: 900; color: var(--dx-amber); text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('map-pin', 'text-amber', 16)?> Rete & Territorio
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="index.php" style="color: #cbd5e1; text-decoration: none;">Pagina Principale</a></li>
          <li><a href="world-club-explorer.php" style="color: var(--dx-amber); font-weight: 750; text-decoration: none;">Trova un Club Territoriale</a></li>
          <li><a href="mappa-club.php" style="color: #67e8f9; font-weight: 750; text-decoration: none;">Mappa 2D Italia (322 Club)</a></li>
          <li><a href="domande-frequenti.php" style="color: #fde68a; font-weight: 750; text-decoration: none;">Domande che vuoi fare</a></li>
          <li><a href="recensioni.php" style="color: var(--dx-amber); font-weight: 750; text-decoration: none;">Recensioni & Testimonianze</a></li>
          <li><a href="parla-con-noi.php" style="color: #ffffff; font-weight: 700; text-decoration: none;">Parla con Noi (Ascolto)</a></li>
          <li><a href="events-public.php" style="color: #cbd5e1; text-decoration: none;">Vivi la Comunità (Eventi)</a></li>
          <li><a href="world-map.php" style="color: #cbd5e1; text-decoration: none;">Mappa Mondiale 2D/3D</a></li>
        </ul>
      </div>

      <!-- COLONNA 2: METODO & COMUNITÀ -->
      <div>
        <h4 style="font-size: 0.85rem; font-weight: 900; color: var(--dx-emerald); text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('book-open', 'text-green', 16)?> Metodo & Comunità
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="playground.php" style="color: #00f0ff; font-weight: 800; text-decoration: none;">Life Playground 6.0</a></li>
          <li><a href="orientamento.php" style="color: #67e8f9; font-weight: 750; text-decoration: none;">Mappa del Benessere 4.0</a></li>
          <li><a href="dashboard.php" style="color: #86efac; font-weight: 750; text-decoration: none;">Dashboard & Sobrietà</a></li>
          <li><a href="ruota-della-vita.php" style="color: #cbd5e1; text-decoration: none;">Ruota della Vita 2D/3D</a></li>
          <li><a href="piramide-maslow.php" style="color: #cbd5e1; text-decoration: none;">Piramide di Maslow 2D/3D</a></li>
          <li><a href="metodo.php" style="color: #cbd5e1; text-decoration: none;">Il Metodo Hudolin (3 Livelli)</a></li>
          <li><a href="storie.php" style="color: #ffffff; font-weight: 700; text-decoration: none;">Storie di Comunità</a></li>
          <li><a href="academy-public.php" style="color: #cbd5e1; text-decoration: none;">Sovereign Academy</a></li>
          <li><a href="guida-gratuita.php" style="color: var(--dx-emerald); font-weight: 750; text-decoration: none;">Guida Gratuita Famiglia</a></li>
          <li><a href="evento-ottobre-taglio-di-po.php" style="color: #cbd5e1; text-decoration: none;">Corso Esperienziale Taglio di Po</a></li>
        </ul>
      </div>

      <!-- COLONNA 3: ASSET & APPROFONDIMENTI -->
      <div>
        <h4 style="font-size: 0.85rem; font-weight: 900; color: var(--dx-sky); text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('compass', 'text-cyan', 16)?> Risorse & Approfondimenti
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="clips.php" style="color: #fbbf24; font-weight: 750; text-decoration: none;">Clip Motivazionali 9:16</a></li>
          <li><a href="offers.php" style="color: #cbd5e1; text-decoration: none;">Collana Libri KDP</a></li>
          <li><a href="viaggi-esperienziali.php" style="color: #cbd5e1; text-decoration: none;">Viaggi Esperienziali</a></li>
          <li><a href="https://oltre.social" target="_blank" rel="noopener" style="color: #cbd5e1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">OLTRE.SOCIAL <?=dx_icon('external-link', '', 11)?></a></li>
          <li><a href="https://beway.life" target="_blank" rel="noopener" style="color: #cbd5e1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">beway.life <?=dx_icon('external-link', '', 11)?></a></li>
        </ul>
      </div>

      <!-- COLONNA 4: GOVERNANCE & PRIVACY -->
      <div>
        <h4 style="font-size: 0.85rem; font-weight: 900; color: #b829ff; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('shield-check', 'text-violet', 16)?> Governance & Privacy
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="privacy.php" style="color: #cbd5e1; text-decoration: none;">Privacy Policy (GDPR)</a></li>
          <li><a href="privacy-center.php" style="color: #cbd5e1; text-decoration: none;">Gestione Consensi & Cookie</a></li>
          <li><a href="terms.php" style="color: #cbd5e1; text-decoration: none;">Termini & Trasparenza ACAT</a></li>
          <li><a href="telemetria.php" style="color: #00f0ff; text-decoration: none; font-weight: 600;">Console Telemetria & Watchdog</a></li>
          <li><a href="help.php" style="color: #cbd5e1; text-decoration: none;">Aiuto & Emergenze (112)</a></li>
          <li><a href="mailto:info@dependex.support" style="color: #b829ff; font-weight: 750; text-decoration: none;">info@dependex.support</a></li>
        </ul>
      </div>

    </div>

    <!-- BANNER GARANZIA DI SOSTEGNO & TRASPARENZA VOLONTARIATO -->
    <div style="margin: 8px 0 28px; padding: 14px 20px; background: rgba(212, 175, 55, 0.07); border-radius: 12px; border: 1px dashed rgba(212, 175, 55, 0.4); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; font-size: 0.82rem; color: #e2e8f0; line-height: 1.5;">
      <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px;">
        <?=dx_icon('shield-check', 'text-gold', 20)?>
        <span><b>Garanzia di Trasparenza & Servizio Solidale:</b> I Club Alcologici Territoriali operano su base 100% volontaria secondo il Metodo Hudolin. Eventuali quote di partecipazione coprono esclusivamente i costi vivi di accoglienza, pranzo comunitario e materiali didattici.</span>
      </div>
      <div style="font-weight: 800; color: #d4af37; white-space: nowrap;">
        Organizzazione: ACAT Basso Polesine O.D.V.
      </div>
    </div>

    <!-- BARRA COPYRIGHT DI CHIUSURA -->
    <div style="padding-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; font-size: 0.78rem; color: #94a3b8;">
      <div>
        &copy; <?=date('Y')?> <b><?=h(site_brand()['name'])?></b> · <a href="https://oltre.social" target="_blank" rel="noopener" style="color: #94a3b8; text-decoration: none;">OLTRE.SOCIAL</a>. Tutti i diritti riservati.
      </div>
      <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
        <span>Zero Profilazione Invasiva</span>
        <span>·</span>
        <span>RFC 8058 One-Click Unsubscribe</span>
        <span>·</span>
        <a href="mailto:info@dependex.support" style="color: #00d4ff; text-decoration: none; font-weight: 600;">info@dependex.support</a>
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
<script src="assets/js/dx-pwa-companion.js?v=<?=filemtime(__DIR__.'/assets/js/dx-pwa-companion.js')?>"></script>
</body>
</html>