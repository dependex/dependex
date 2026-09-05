<?php
require_once __DIR__.'/bootstrap.php';
$u = current_user();
$pageTitle = $pageTitle ?? APP_NAME;
$brand = site_brand();
$locale = site_locale();
$metaDesc = $metaDesc ?? 'DEPENDEX — AL CLUB. COL CLUB. Cammino di sobrietà, Club Alcologici Territoriali, Academy e supporto continuo.';
?>
<!doctype html>
<html lang="<?=h($locale)?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#070709">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="<?=h($brand['name'])?>">
  <meta name="description" content="<?=h($metaDesc)?>">

  <!-- OpenGraph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?=h($pageTitle)?> · <?=h($brand['name'])?>">
  <meta property="og:description" content="<?=h($metaDesc)?>">
  <meta property="og:site_name" content="<?=h($brand['name'])?>">
  <meta property="og:image" content="assets/img/app-icon.svg">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="<?=h($pageTitle)?> · <?=h($brand['name'])?>">
  <meta name="twitter:description" content="<?=h($metaDesc)?>">
  <meta name="twitter:image" content="assets/img/app-icon.svg">

  <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
  <link rel="shortcut icon" href="assets/img/favicon.svg">
  <link rel="apple-touch-icon" href="assets/img/app-icon.svg">
  <link rel="manifest" href="manifest.webmanifest">
  <link rel="stylesheet" href="assets/css/app.css">
  <link rel="stylesheet" href="assets/css/luxury-patterns.css">
  <title><?=h($pageTitle)?> · <?=h($brand['name'])?></title>

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "NGO",
    "name": "<?=h($brand['name'])?>",
    "alternateName": "DIPENDEX Social",
    "description": "<?=h($metaDesc)?>",
    "url": "https://<?=h($brand['domain'])?>",
    "email": "info@dependex.social",
    "logo": "https://<?=h($brand['domain'])?>/assets/img/app-icon.svg"
  }
  </script>

  <script>
    (function(){
      const t = localStorage.getItem('oltre_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
</head>
<?php
$curScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<body class="site-<?=strtolower($brand['name'])?>">
  <a href="#mainContent" class="skip-link">Salta al contenuto principale</a>
  <header class="topbar">
    <a class="brand" href="<?=$u ? 'app.php' : 'index.php'?>">
      <span class="brand-mark"><img src="assets/img/app-icon.svg" alt="Logo DEPENDEX"></span>
      <span><b><?=h($brand['name'])?></b><small><?=h(APP_PAYOFF)?></small></span>
    </a>

    <!-- DESKTOP 16:9 TOP NAVIGATION -->
    <nav class="desktop-nav" aria-label="Navigazione desktop">
      <?php if($u):?>
        <a href="app.php" <?=$curScript==='app.php'?'class="active"':''?>>Home</a>
        <a href="checkin.php" <?=$curScript==='checkin.php'?'class="active"':''?>>Check-in</a>
        <a href="journal.php" <?=$curScript==='journal.php'?'class="active"':''?>>Diario</a>
        <a href="club.php" <?=$curScript==='club.php'?'class="active"':''?>>Il mio Club</a>
        <a href="world-map.php" <?=$curScript==='world-map.php'?'class="active"':''?>>Mappa</a>
        <a href="academy.php" <?=$curScript==='academy.php'?'class="active"':''?>>Academy</a>
        <a href="offers.php" <?=$curScript==='offers.php'?'class="active"':''?>>Offerte</a>
        <a href="cart.php" <?=$curScript==='cart.php'?'class="active"':''?>>Carrello</a>
        <a href="events.php" <?=$curScript==='events.php'?'class="active"':''?>>Eventi</a>
        <a href="cortex.php" <?=$curScript==='cortex.php'?'class="active"':''?>>Cortex AI</a>
      <?php else:?>
        <a href="index.php" <?=$curScript==='index.php'?'class="active"':''?>>Home</a>
        <a href="offers.php" <?=$curScript==='offers.php'?'class="active"':''?>>Offerte</a>
        <a href="cart.php" <?=$curScript==='cart.php'?'class="active"':''?>>Carrello</a>
        <a href="world-map.php" <?=$curScript==='world-map.php'?'class="active"':''?>>Mappa</a>
        <a href="world-club-explorer.php" <?=$curScript==='world-club-explorer.php'?'class="active"':''?>>Trova Club</a>
        <a href="academy-public.php" <?=$curScript==='academy-public.php'?'class="active"':''?>>Academy</a>
        <a href="events-public.php" <?=$curScript==='events-public.php'?'class="active"':''?>>Eventi</a>
        <a href="metodo.php" <?=$curScript==='metodo.php'?'class="active"':''?>>Metodo</a>
      <?php endif;?>
    </nav>

    <div class="header-actions">
      <a href="cart.php" class="cart-pill" aria-label="Carrello" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;border:1px solid rgba(212,175,55,0.3);background:rgba(212,175,55,0.08);color:var(--gold-primary);text-decoration:none;font-weight:700;font-size:12px;">
        <?=dx_icon('shopping-cart','',15)?> <span class="d-none d-sm-inline">Carrello</span>
      </a>
      <?php if(site_mode()==='DEPENDEX'):?>
        <select aria-label="Lingua" onchange="location.href='?lang='+this.value" class="lang-select">
          <?php foreach(supported_locales() as $l):?>
            <option value="<?=$l?>" <?=$locale===$l?'selected':''?>><?=strtoupper($l)?></option>
          <?php endforeach;?>
        </select>
      <?php endif;?>
      <button type="button" class="theme-toggle" aria-label="Cambia tema"><?=dx_icon('sun', '', 18)?></button>
      <?php if($u):?>
        <a class="avatar" href="profile.php" aria-label="Profilo"><?=h(mb_substr($u['display_name'],0,1))?></a>
      <?php else:?>
        <a class="register-pill" href="register.php"><?=h($locale==='it'?'Registrati':'Register')?></a>
        <a class="login-pill" href="login.php"><?=h($locale==='it'?'Accedi':'Login')?></a>
      <?php endif;?>
      <!-- BURGER MENU BUTTON -->
      <button type="button" class="burger-btn" id="burgerBtn" aria-label="Menu" aria-expanded="false" aria-controls="drawerNav">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- OFF-CANVAS DRAWER NAVIGATION -->
  <div class="drawer-backdrop" id="drawerBackdrop" aria-hidden="true"></div>
  <aside class="drawer" id="drawerNav" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Menu navigazione completo">
    <div class="drawer-header">
      <div class="brand">
        <span class="brand-mark"><img src="assets/img/app-icon.svg" alt="DEPENDEX"></span>
        <span><b>DEPENDEX</b><small>AL CLUB. COL CLUB.</small></span>
      </div>
      <button type="button" class="drawer-close" id="drawerCloseBtn" aria-label="Chiudi menu">&times;</button>
    </div>
    <div class="drawer-content">
      <?php if($u):?>
        <div class="drawer-user-card">
          <div class="drawer-user-avatar"><?=h(mb_substr($u['display_name'],0,1))?></div>
          <div class="drawer-user-info">
            <b><?=h($u['display_name'])?></b>
            <small>Rank <?=h($u['rank_name']??'SEME')?> · <span class="tabular-nums"><?=number_format((float)($u['drx_balance']??0),0,',','.')?> DRX</span></small>
            <span class="drawer-sic"><?=h($u['sic_id']??'')?></span>
          </div>
        </div>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">QUOTIDIANO</span>
          <a href="app.php" class="drawer-link <?=$curScript==='app.php'?'active':''?>"><?=dx_icon('home','',18)?> Home Dashboard</a>
          <a href="checkin.php" class="drawer-link <?=$curScript==='checkin.php'?'active':''?>"><?=dx_icon('edit','',18)?> Daily Check-in (+5 DRX)</a>
          <a href="journal.php" class="drawer-link <?=$curScript==='journal.php'?'active':''?>"><?=dx_icon('book-open','',18)?> Diario & Gratitudine</a>
          <a href="sobriety.php" class="drawer-link <?=$curScript==='sobriety.php'?'active':''?>"><?=dx_icon('activity','',18)?> Sobrietà & Traguardi</a>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">COMUNITÀ & APPRENDIMENTO</span>
          <a href="club.php" class="drawer-link <?=$curScript==='club.php'?'active':''?>"><?=dx_icon('users','',18)?> Il mio Club</a>
          <a href="world-map.php" class="drawer-link <?=$curScript==='world-map.php'?'active':''?>"><?=dx_icon('compass','',18)?> Mappa Mondiale 2D/3D</a>
          <a href="academy.php" class="drawer-link <?=$curScript==='academy.php'?'active':''?>"><?=dx_icon('academic','',18)?> Academy Corsi</a>
          <a href="offers.php" class="drawer-link <?=$curScript==='offers.php'?'active':''?>"><?=dx_icon('sparkles','',18)?> Offerte & Percorsi</a>
          <a href="cart.php" class="drawer-link <?=$curScript==='cart.php'?'active':''?>"><?=dx_icon('shopping-cart','',18)?> Carrello Universale</a>
          <a href="events.php" class="drawer-link <?=$curScript==='events.php'?'active':''?>"><?=dx_icon('calendar','',18)?> Calendario Eventi</a>
          <a href="dao.php" class="drawer-link <?=$curScript==='dao.php'?'active':''?>"><?=dx_icon('scale','',18)?> DAO Community</a>
          <a href="cortex.php" class="drawer-link <?=$curScript==='cortex.php'?'active':''?>"><?=dx_icon('brain','',18)?> Cortex AI</a>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">PROFILO & STRUMENTI</span>
          <a href="profile.php" class="drawer-link <?=$curScript==='profile.php'?'active':''?>"><?=dx_icon('users','',18)?> Il mio Profilo</a>
          <a href="rank.php" class="drawer-link <?=$curScript==='rank.php'?'active':''?>"><?=dx_icon('trophy','',18)?> Rank DRX & Privilegi</a>
          <a href="certificates.php" class="drawer-link <?=$curScript==='certificates.php'?'active':''?>"><?=dx_icon('award','',18)?> Certificati</a>
          <a href="lifestyle.php" class="drawer-link <?=$curScript==='lifestyle.php'?'active':''?>"><?=dx_icon('feather','',18)?> Lifestyle Tracker</a>
          <a href="help.php" class="drawer-link <?=$curScript==='help.php'?'active':''?>"><?=dx_icon('shield','',18)?> Supporto & Aiuto</a>
          <a href="logout.php" class="drawer-link drawer-logout"><?=dx_icon('log-out','',18)?> Esci dall'App</a>
        </nav>
      <?php else:?>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">NAVIGAZIONE</span>
          <a href="index.php" class="drawer-link <?=$curScript==='index.php'?'active':''?>"><?=dx_icon('home','',18)?> Pagina Principale</a>
          <a href="offers.php" class="drawer-link <?=$curScript==='offers.php'?'active':''?>"><?=dx_icon('sparkles','',18)?> Offerte & Percorsi</a>
          <a href="cart.php" class="drawer-link <?=$curScript==='cart.php'?'active':''?>"><?=dx_icon('shopping-cart','',18)?> Carrello Universale</a>
          <a href="world-map.php" class="drawer-link <?=$curScript==='world-map.php'?'active':''?>"><?=dx_icon('compass','',18)?> Mappa Mondiale Club</a>
          <a href="world-club-explorer.php" class="drawer-link <?=$curScript==='world-club-explorer.php'?'active':''?>"><?=dx_icon('map-pin','',18)?> Cerca un Club</a>
          <a href="academy-public.php" class="drawer-link <?=$curScript==='academy-public.php'?'active':''?>"><?=dx_icon('academic','',18)?> Academy</a>
          <a href="events-public.php" class="drawer-link <?=$curScript==='events-public.php'?'active':''?>"><?=dx_icon('calendar','',18)?> Eventi</a>
          <a href="metodo.php" class="drawer-link <?=$curScript==='metodo.php'?'active':''?>"><?=dx_icon('feather','',18)?> Metodo Hudolin</a>
          <a href="help.php" class="drawer-link <?=$curScript==='help.php'?'active':''?>"><?=dx_icon('shield','',18)?> Aiuto & Supporto</a>
          <a href="privacy.php" class="drawer-link <?=$curScript==='privacy.php'?'active':''?>"><?=dx_icon('lock','',18)?> Privacy & Sicurezza</a>
        </nav>
        <div style="margin-top:14px;">
          <a class="btn primary" href="login.php" style="width:100%;margin-bottom:8px;">Accedi</a>
          <a class="btn" href="register.php" style="width:100%;">Registrati</a>
        </div>
      <?php endif;?>
    </div>
    <div class="drawer-footer">
      <button type="button" class="theme-toggle btn small" style="width:100%;margin-bottom:8px;border-radius:12px;display:flex;align-items:center;justify-content:center;gap:8px;"><?=dx_icon('sun','',16)?> Cambia Tema</button>
      <?php if(site_mode()==='DEPENDEX'):?>
        <select aria-label="Lingua" onchange="location.href='?lang='+this.value" class="lang-select" style="width:100%;">
          <?php foreach(supported_locales() as $l):?>
            <option value="<?=$l?>" <?=$locale===$l?'selected':''?>><?=strtoupper($l)?> — Lingua</option>
          <?php endforeach;?>
        </select>
      <?php endif;?>
      <div style="font-size:12px;text-align:center;margin-top:10px;">
        <a href="mailto:info@dependex.social" style="color:var(--text-muted);text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:6px;"><?=dx_icon('mail','',14)?> info@dependex.social</a>
      </div>
    </div>
  </aside>

  <?php if(!empty($_SESSION['flash'])):?>
    <div class="toast flash" role="status" aria-live="polite"><?=h($_SESSION['flash'])?></div>
    <?php unset($_SESSION['flash']);?>
  <?php endif;?>
  <div class="offline-pill" role="status" aria-live="polite"><?=dx_icon('wifi','',14)?> Modalità offline attiva: dati salvati disponibili</div>
  <main class="page" id="mainContent">