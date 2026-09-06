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
  <link rel="stylesheet" href="assets/css/app.css?v=<?=filemtime(__DIR__.'/assets/css/app.css')?>">
  <link rel="stylesheet" href="assets/css/luxury-patterns.css?v=<?=filemtime(__DIR__.'/assets/css/luxury-patterns.css')?>">
  <link rel="stylesheet" href="assets/css/rainbow-neon.css?v=<?=filemtime(__DIR__.'/assets/css/rainbow-neon.css')?>">
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
      <span class="brand-mark brand-mark-rainbow"><img src="assets/img/dependex-rainbow-badge.jpg" alt="Logo DEPENDEX"></span>
      <span><b><?=h($brand['name'])?></b><small><?=h(APP_PAYOFF)?></small></span>
    </a>

    <div class="header-actions">
      <button type="button" class="theme-toggle" aria-label="Cambia tema" title="Cambia tema"><?=dx_icon('sun', '', 18)?></button>
      <!-- BURGER MENU BUTTON (UNICA ED ESCLUSIVA NAVIGAZIONE PRINCIPALE) -->
      <button type="button" class="burger-btn" id="burgerBtn" aria-label="Menu di Navigazione" aria-expanded="false" aria-controls="drawerNav" title="Apri menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- OFF-CANVAS DRAWER NAVIGATION -->
  <div class="drawer-backdrop" id="drawerBackdrop" aria-hidden="true"></div>
  <aside class="drawer" id="drawerNav" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Menu navigazione completo">
    <div class="drawer-header">
      <div class="brand">
        <span class="brand-mark brand-mark-rainbow"><img src="assets/img/dependex-rainbow-badge.jpg" alt="DEPENDEX"></span>
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
          <span class="drawer-section-title">QUOTIDIANO & PERCORSO</span>
          <a href="app.php" class="drawer-link <?=$curScript==='app.php'?'active':''?>"><?=dx_icon('home','',18)?> Home Dashboard</a>
          <a href="checkin.php" class="drawer-link <?=$curScript==='checkin.php'?'active':''?>"><?=dx_icon('edit','',18)?> Daily Check-in (+5 DRX)</a>
          <a href="journal.php" class="drawer-link <?=$curScript==='journal.php'?'active':''?>"><?=dx_icon('book-open','',18)?> Diario & Gratitudine</a>
          <a href="sobriety.php" class="drawer-link <?=$curScript==='sobriety.php'?'active':''?>"><?=dx_icon('activity','',18)?> Sobrietà & Traguardi</a>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">COMMUNITY DEI CLUB (100% VOLONTARIATO)</span>
          <a href="club.php" class="drawer-link <?=$curScript==='club.php'?'active':''?>"><?=dx_icon('users','',18)?> Il mio Club</a>
          <a href="world-map.php" class="drawer-link <?=$curScript==='world-map.php'?'active':''?>"><?=dx_icon('compass','',18)?> Mappa Mondiale 2D/3D</a>
          <a href="world-club-explorer.php" class="drawer-link <?=$curScript==='world-club-explorer.php'?'active':''?>"><?=dx_icon('map-pin','',18)?> Trova un Club Territoriale</a>
          <a href="metodo.php" class="drawer-link <?=$curScript==='metodo.php'?'active':''?>"><?=dx_icon('feather','',18)?> Metodo Hudolin</a>
          <a href="academy.php" class="drawer-link <?=$curScript==='academy.php'?'active':''?>"><?=dx_icon('academic','',18)?> Formazione Servitori</a>
          <a href="events.php" class="drawer-link <?=$curScript==='events.php'?'active':''?>"><?=dx_icon('calendar','',18)?> Calendario Eventi & Moduli</a>
          <a href="dao.php" class="drawer-link <?=$curScript==='dao.php'?'active':''?>"><?=dx_icon('scale','',18)?> Partecipazione Comunitaria</a>
          <a href="cortex.php" class="drawer-link <?=$curScript==='cortex.php'?'active':''?>"><?=dx_icon('brain','',18)?> Cortex AI (Supporto 24/7)</a>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title text-rainbow">I 7 RAMI DEL METODO (FREQUENZE ARCOBALENO)</span>
          <div class="drawer-rainbow-branches">
            <a href="metodo.php#senti" class="branch-item red"><span class="branch-dot"></span> 1. SENTI (Radici & Ascolto)</a>
            <a href="metodo.php#agisci" class="branch-item orange"><span class="branch-dot"></span> 2. AGISCI (Flusso & Volontà)</a>
            <a href="metodo.php#comunica" class="branch-item gold"><span class="branch-dot"></span> 3. COMUNICA (Voce & Verità)</a>
            <a href="metodo.php#vedi" class="branch-item green"><span class="branch-dot"></span> 4. VEDI (Sobrietà & Visione)</a>
            <a href="metodo.php#ama" class="branch-item cyan"><span class="branch-dot"></span> 5. AMA (Relazione & Cerchio)</a>
            <a href="metodo.php#costruisci" class="branch-item indigo"><span class="branch-dot"></span> 6. COSTRUISCI (Dignità & Azione)</a>
            <a href="metodo.php#sii" class="branch-item violet"><span class="branch-dot"></span> 7. SII (Sovranità & Trascendenza)</a>
          </div>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">PROFILO, SUPPORTO & STRUMENTI</span>
          <a href="profile.php" class="drawer-link <?=$curScript==='profile.php'?'active':''?>"><?=dx_icon('users','',18)?> Il mio Profilo</a>
          <a href="certificates.php" class="drawer-link <?=$curScript==='certificates.php'?'active':''?>"><?=dx_icon('award','',18)?> Attestati & Corsi</a>
          <a href="offers.php" class="drawer-link <?=$curScript==='offers.php'?'active':''?>"><?=dx_icon('sparkles','',18)?> Offerte di Valore</a>
          <a href="help.php" class="drawer-link <?=$curScript==='help.php'?'active':''?>"><?=dx_icon('shield','',18)?> Supporto & Aiuto</a>
          <a href="privacy.php" class="drawer-link <?=$curScript==='privacy.php'?'active':''?>"><?=dx_icon('lock','',18)?> Riservatezza & Anonimato</a>
          <a href="logout.php" class="drawer-link drawer-logout"><?=dx_icon('log-out','',18)?> Esci dall'App</a>
        </nav>
      <?php else:?>
        <div class="drawer-auth-card" style="padding:16px;border-radius:18px;background:rgba(12,16,26,0.9);border:1px solid rgba(0,212,255,0.35);box-shadow:0 0 20px rgba(0,212,255,0.2);margin-bottom:14px;">
          <div class="badge-neon-rainbow mb-2" style="font-size:0.72rem;"><span class="dot"></span> RETE GRATUITA DEI CLUB</div>
          <p style="font-size:0.84rem;color:#cbd5e1;line-height:1.45;margin:0 0 12px;">542 Club territoriali, metodo Hudolin e supporto continuativo senza giudizio.</p>
          <div style="display:flex;gap:8px;">
            <a class="btn primary small" href="login.php" style="flex:1;text-align:center;">Accedi</a>
            <a class="btn small" href="register.php" style="flex:1;border:1px solid rgba(0,212,255,0.4);color:#ffffff;border-radius:12px;text-align:center;">Registrati</a>
          </div>
        </div>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title text-rainbow">I 7 RAMI DEL METODO (FREQUENZE ARCOBALENO)</span>
          <div class="drawer-rainbow-branches">
            <a href="metodo.php#senti" class="branch-item red"><span class="branch-dot"></span> 1. SENTI (Radici & Ascolto)</a>
            <a href="metodo.php#agisci" class="branch-item orange"><span class="branch-dot"></span> 2. AGISCI (Flusso & Volontà)</a>
            <a href="metodo.php#comunica" class="branch-item gold"><span class="branch-dot"></span> 3. COMUNICA (Voce & Verità)</a>
            <a href="metodo.php#vedi" class="branch-item green"><span class="branch-dot"></span> 4. VEDI (Sobrietà & Visione)</a>
            <a href="metodo.php#ama" class="branch-item cyan"><span class="branch-dot"></span> 5. AMA (Relazione & Cerchio)</a>
            <a href="metodo.php#costruisci" class="branch-item indigo"><span class="branch-dot"></span> 6. COSTRUISCI (Dignità & Azione)</a>
            <a href="metodo.php#sii" class="branch-item violet"><span class="branch-dot"></span> 7. SII (Sovranità & Trascendenza)</a>
          </div>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">COMMUNITY DEI CLUB (100% VOLONTARIATO)</span>
          <a href="index.php" class="drawer-link <?=$curScript==='index.php'?'active':''?>"><?=dx_icon('home','',18)?> Pagina Principale</a>
          <a href="world-club-explorer.php" class="drawer-link <?=$curScript==='world-club-explorer.php'?'active':''?>"><?=dx_icon('map-pin','',18)?> Trova un Club Territoriale</a>
          <a href="world-map.php" class="drawer-link <?=$curScript==='world-map.php'?'active':''?>"><?=dx_icon('compass','',18)?> Mappa Mondiale Club</a>
          <a href="metodo.php" class="drawer-link <?=$curScript==='metodo.php'?'active':''?>"><?=dx_icon('feather','',18)?> Il Metodo Hudolin</a>
          <a href="events-public.php" class="drawer-link <?=$curScript==='events-public.php'?'active':''?>"><?=dx_icon('calendar','',18)?> Eventi, Corsi & Moduli SAT</a>
          <a href="academy-public.php" class="drawer-link <?=$curScript==='academy-public.php'?'active':''?>"><?=dx_icon('academic','',18)?> Academy Servitori-Insegnanti</a>
          <a href="cortex.php" class="drawer-link <?=$curScript==='cortex.php'?'active':''?>"><?=dx_icon('brain','',18)?> Cortex AI (Supporto 24/7 Anonimo)</a>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">RISORSE & TRASPARENZA</span>
          <a href="offers.php" class="drawer-link <?=$curScript==='offers.php'?'active':''?>"><?=dx_icon('sparkles','',18)?> Offerte di Valore & Starter Kit</a>
          <a href="help.php" class="drawer-link <?=$curScript==='help.php'?'active':''?>"><?=dx_icon('shield','',18)?> Aiuto Immediato & Numeri Utili</a>
          <a href="privacy.php" class="drawer-link <?=$curScript==='privacy.php'?'active':''?>"><?=dx_icon('lock','',18)?> Riservatezza & Anonimato</a>
          <a href="terms.php" class="drawer-link <?=$curScript==='terms.php'?'active':''?>"><?=dx_icon('file-text','',18)?> Termini e Condizioni</a>
        </nav>
      <?php endif;?>
    </div>
    <div class="drawer-footer">
      <button type="button" class="theme-toggle btn small" style="width:100%;margin-bottom:8px;border-radius:12px;display:flex;align-items:center;justify-content:center;gap:8px;"><?=dx_icon('sun','',16)?> Cambia Tema</button>
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