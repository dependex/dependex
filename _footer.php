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

      <!-- CONTATTI RAPIDI GOVERNANCE & WHATSAPP -->
      <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
        <a href="https://wa.me/393478844271?text=<?=urlencode('Buongiorno Grazia, vorrei informazioni riservate sull\'evento e sui Club.')?>" 
           target="_blank" rel="noopener" 
           class="btn small" 
           style="background: rgba(37,211,102,0.15); border: 1px solid #25D366; color: #25D366; font-weight: 750; font-size: 0.82rem; padding: 8px 16px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
          <?=dx_icon('message-circle', '', 15)?>
          <span>Grazia Nicosia · WhatsApp 347 884 4271</span>
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
        <h4 style="font-size: 0.85rem; font-weight: 900; color: #ff7700; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('users', 'text-orange', 16)?> Rete Territoriale
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="index.php" style="color: #cbd5e1; text-decoration: none; transition: color 0.2s ease;">Pagina Principale</a></li>
          <li><a href="world-club-explorer.php" style="color: #f59e0b; font-weight: 750; text-decoration: none;">Trova un Club Territoriale</a></li>
          <li><a href="world-map.php" style="color: #cbd5e1; text-decoration: none;">Mappa Mondiale 2D/3D</a></li>
          <li><a href="events-public.php" style="color: #cbd5e1; text-decoration: none;">Hub Nazionale Eventi</a></li>
          <li><a href="evento-ottobre-taglio-di-po.php" style="color: #ffd700; font-weight: 800; text-decoration: none;">Corso Taglio di Po (10€)</a></li>
        </ul>
      </div>

      <!-- COLONNA 2: METODO & FORMAZIONE -->
      <div>
        <h4 style="font-size: 0.85rem; font-weight: 900; color: #00ff77; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('book-open', 'text-green', 16)?> Metodo & Formazione
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="metodo.php" style="color: #cbd5e1; text-decoration: none;">Il Metodo Hudolin</a></li>
          <li><a href="academy-public.php" style="color: #cbd5e1; text-decoration: none;">Sovereign Academy</a></li>
          <li><a href="metodo.php#senti" style="color: #cbd5e1; text-decoration: none;">I 7 Rami del Cammino</a></li>
          <li><a href="guida-gratuita.php" style="color: #00ff77; font-weight: 750; text-decoration: none;">Guida Gratuita 7 Giorni</a></li>
          <li><a href="cortex.php" style="color: #cbd5e1; text-decoration: none;">Cortex AI (Supporto 24/7)</a></li>
        </ul>
      </div>

      <!-- COLONNA 3: ASSET SOVRANI & VIAGGI -->
      <div>
        <h4 style="font-size: 0.85rem; font-weight: 900; color: #00d4ff; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('compass', 'text-cyan', 16)?> Asset Sovrani & Viaggi
        </h4>
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
          <li><a href="offers.php" style="color: #cbd5e1; text-decoration: none;">Libri Amazon KDP</a></li>
          <li><a href="viaggi-esperienziali.php" style="color: #00d4ff; font-weight: 750; text-decoration: none;">Viaggi BEWAY.LIFE</a></li>
          <li><a href="crociera-benessere-masterclass.php" style="color: #cbd5e1; text-decoration: none;">Crociera della Rinascita</a></li>
          <li><a href="https://beway.life" target="_blank" rel="noopener" style="color: #cbd5e1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">beway.life <?=dx_icon('external-link', '', 11)?></a></li>
          <li><a href="https://oltre.social" target="_blank" rel="noopener" style="color: #cbd5e1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">Oltre.social <?=dx_icon('external-link', '', 11)?></a></li>
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
          <li><a href="terms.php" style="color: #cbd5e1; text-decoration: none;">Termini & Condizioni</a></li>
          <li><a href="help.php" style="color: #cbd5e1; text-decoration: none;">Aiuto & Supporto Utenti</a></li>
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

<!-- PULSANTE FLOTTANTE DI CONTATTO RISERVATO WHATSAPP / AIUTO -->
<div class="floating-quick-support" style="position: fixed; bottom: <?=($u??null)?'74px':'24px'?>; right: 20px; z-index: 999;">
  <a href="https://wa.me/393478844271?text=<?=urlencode('Buongiorno Grazia, vorrei un orientamento riservato su un Club o sul Metodo Hudolin.')?>" 
     target="_blank" rel="noopener" 
     class="btn-floating-support" 
     style="display: inline-flex; align-items: center; gap: 8px; background: #25D366; color: #000000; font-weight: 800; font-size: 0.86rem; padding: 10px 18px; border-radius: 999px; text-decoration: none; box-shadow: 0 4px 20px rgba(37,211,102,0.4); transition: transform 0.2s ease;">
    <?=dx_icon('whatsapp', '', 18)?>
    <span class="d-none d-sm-inline">Orientamento Riservato WhatsApp</span>
    <span class="d-inline d-sm-none">Aiuto</span>
  </a>
</div>

<script src="assets/js/app.js?v=<?=filemtime(__DIR__.'/assets/js/app.js')?>"></script>
</body>
</html>