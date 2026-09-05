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
      <?=dx_icon('crown', '', 20)?><span><?=h($locale==='it'?'Io':'Me')?></span>
    </a>
  </nav>
<?php endif;?>
<footer class="site-footer" style="border-top: 1px solid rgba(212,175,55,0.2); color: #a1a1aa; background: #070709;">
  <div>
    <b style="color: #FFFFFF;"><?=h(site_brand()['name'])?></b>
    <small style="color: #D4AF37;"><?=h(site_brand()['subtitle'])?></small>
  </div>
  <div>
    <a href="metodo.php">DIPENDEX</a>
    <a href="offers.php">Offerte</a>
    <a href="privacy.php">Privacy</a>
    <a href="terms.php">Condizioni</a>
    <a href="world-club-explorer.php"><?=h(tr('club.find','Trova Club'))?></a>
    <a href="mailto:info@dependex.social" title="Scrivici un'email" style="display: inline-flex; align-items: center; gap: 4px;"><?=dx_icon('mail', '', 14)?> info@dependex.social</a>
  </div>
</footer>
<script src="assets/js/app.js"></script>
</body>
</html>