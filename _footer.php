</main>
<?php if($u??null):
  $curScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
  <nav class="bottom-nav" aria-label="Navigazione principale">
    <a href="app.php" <?=$curScript==='app.php'?'class="active" aria-current="page"':''?>>
      ⌂<span><?=h(tr('nav.home','Home'))?></span>
    </a>
    <a href="checkin.php" <?=$curScript==='checkin.php'?'class="active" aria-current="page"':''?>>
      ✍<span>Check-in</span>
    </a>
    <a class="nav-plus" href="journal.php" title="Diario del giorno" <?=$curScript==='journal.php'?'class="active" aria-current="page"':''?>>
      ＋<span>Diario</span>
    </a>
    <a href="club.php" <?=$curScript==='club.php'?'class="active" aria-current="page"':''?>>
      🤝<span>Club</span>
    </a>
    <a href="profile.php" <?=$curScript==='profile.php'?'class="active" aria-current="page"':''?>>
      ●<span><?=h($locale==='it'?'Io':'Me')?></span>
    </a>
  </nav>
<?php endif;?>
<footer class="site-footer">
  <div>
    <b><?=h(site_brand()['name'])?></b>
    <small><?=h(site_brand()['subtitle'])?></small>
  </div>
  <div>
    <a href="metodo.php">DIPENDEX</a>
    <a href="privacy.php">Privacy</a>
    <a href="terms.php">Condizioni</a>
    <a href="world-club-explorer.php"><?=h(tr('club.find','Find a Club'))?></a>
    <a href="mailto:info@dependex.social" title="Scrivici un'email">✉ info@dependex.social</a>
  </div>
</footer>
<script src="assets/js/app.js"></script>
</body>
</html>