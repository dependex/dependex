<?php
require_once __DIR__.'/bootstrap.php';
$pageTitle=site_mode()==='DEPENDEX'?'Global Community':'La Community per andare oltre'; require '_header.php'; $global=site_mode()==='DEPENDEX'; ?>
<section class="hero oltre-hero"><div class="hero-logo"><img src="assets/img/dipendex-logo.svg" alt="<?=h(site_brand()['name'])?>"></div><div class="eyebrow">DEPENDEX · AL CLUB. COL CLUB.</div><h1><?=$global?'One network.<br>Many communities.':'Dalla dipendenza<br>alla vita.'?></h1><p class="lead"><?=$global?'Explore Hudolin/CAT networks, clubs, events, learning and community across countries.':'Una APP semplice, sicura e visuale per Club, famiglie, Academy, eventi, lifestyle, DAO, DRX e rete di supporto.'?></p><div class="hero-actions"><a class="btn primary" href="login.php"><?=$global?'Enter DEPENDEX':'Entra in DEPENDEX'?></a><a class="btn" href="world-club-explorer.php"><?=h(tr('club.find','Find a Club'))?></a></div><div class="payoff-card"><b>AL CLUB. COL CLUB.</b><span><?=$global?'Dialogue · Relationships · eXperience':'ALCOL: Ascolto e Legami Creano Orientamento e Libertà.'?></span></div></section>

<section class="card public-map-home">
  <div class="section-head compact"><div>
    <span class="eyebrow">WORLD CLUB EXPLORER · PUBBLICO</span>
    <h2>Trova un Club nel mondo</h2>
    <p>Cerca per Club, città, Paese o SIC-ID. I POI pubblici mostrano solo dati organizzativi e contatti già pubblici.</p>
  </div></div>
  <div class="map-home-actions">
    <a class="btn primary" href="world-map.php">Apri mappa mondiale 2D/3D</a>
    <a class="btn" href="global-network.php">Apri directory globale</a>
  </div>
  <div class="map-preview-shell">
    <iframe src="world-map.php?embed=1" title="DEPENDEX World Club Explorer" loading="lazy"></iframe>
  </div>
</section>
<?php require '_dependex-world-map.php';?>
<section class="bubble-grid"><a class="bubble green" href="world-club-explorer.php"><b>🌍</b><span><?=h(tr('club.find','Find a Club'))?></span></a><a class="bubble blue" href="help.php"><b>💬</b><span><?=$global?'Talk / Help':'Voglio parlare'?></span></a><a class="bubble violet" href="metodo.php"><b>✦</b><span>DIPENDEX</span></a><a class="bubble amber" href="events-public.php"><b>📅</b><span><?=h(tr('nav.events','Events'))?></span></a><a class="bubble coral" href="academy-public.php"><b>🎓</b><span>Academy</span></a><a class="bubble teal" href="privacy.php"><b>🔐</b><span>Privacy</span></a></section>
<?php require '_footer.php';?>