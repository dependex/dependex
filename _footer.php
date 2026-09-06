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
<footer class="site-footer" style="padding: 48px 20px 32px; position: relative;">
  <div style="max-width: 1100px; margin: 0 auto 36px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; align-items: center;">
    <div>
      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="brand-mark-rainbow" style="width: 32px; height: 32px;"><img src="assets/img/dependex-rainbow-badge.jpg" alt="Logo"></span>
        <b style="font-size: 1.35rem; color: #FFFFFF;"><?=h(site_brand()['name'])?></b>
      </div>
      <div class="badge-neon-rainbow mb-2" style="font-size: 0.72rem;">
        <span class="dot"></span>
        <span class="text-rainbow"><?=h(site_brand()['subtitle'])?></span>
      </div>
      <p style="font-size: 0.9rem; color: #cbd5e1; margin: 10px 0 0; line-height: 1.6;">
        I 7 Rami del Cammino · Riconquista lucidità, dignità e serenità familiare con la rete libera dei 542 Club territoriali e l'approccio ecologico-sociale.
      </p>
    </div>
    <div>
      <form action="api-lead.php" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="hidden" name="source" value="footer_newsletter">
        <input type="email" name="email" required placeholder="La tua email migliore..." style="flex: 1; min-width: 220px; padding: 12px 16px; background: rgba(14, 18, 30, 0.9); border: 1px solid rgba(0, 212, 255, 0.35); border-radius: 12px; color: #f8fafc; font-size: 0.92rem; outline: none; box-shadow: 0 0 14px rgba(0, 212, 255, 0.15);">
        <button type="submit" class="btn primary small" style="min-height: 46px; border-radius: 12px;">
          Iscriviti al Cerchio
        </button>
      </form>
      <small style="display: block; font-size: 0.78rem; color: #94a3b8; margin-top: 8px;">
        Zero spam. Privacy protetta al 100%. Disiscrizione in 1 click in ogni momento.
      </small>
    </div>
  </div>
  <div style="max-width: 1100px; margin: 0 auto; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 24px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 16px; align-items: center; font-size: 0.9rem;">
    <div style="display: flex; gap: 18px; flex-wrap: wrap;">
      <a href="metodo.php" class="text-neon-gold" style="font-weight: 700;">Metodo Hudolin</a>
      <a href="offers.php" class="text-neon-green" style="font-weight: 700;">Libri Amazon KDP</a>
      <a href="events-public.php" class="text-neon-cyan" style="font-weight: 700;">Eventi & Moduli</a>
      <a href="world-club-explorer.php" class="text-neon-orange" style="font-weight: 700;"><?=h(tr('club.find','Trova Club'))?></a>
      <a href="privacy.php" style="color: #94a3b8;">Privacy</a>
      <a href="terms.php" style="color: #94a3b8;">Condizioni</a>
    </div>
    <a href="mailto:info@dependex.social" title="Scrivici un'email" style="color: var(--neon-cyan); display: inline-flex; align-items: center; gap: 6px; font-weight: 700;"><?=dx_icon('mail', '', 14)?> info@dependex.social</a>
  </div>
</footer>
<script src="assets/js/app.js"></script>
</body>
</html>