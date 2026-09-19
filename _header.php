<?php
require_once __DIR__.'/bootstrap.php';
$u = current_user();
$pageTitle = $pageTitle ?? APP_NAME;
$brand = site_brand();
$locale = site_locale();
$metaDesc = $metaDesc ?? 'DEPENDEX — AL CLUB. COL CLUB. Cammino di sobrietà, Club Alcologici Territoriali, Academy e supporto continuo.';
$curScript = basename($_SERVER['SCRIPT_NAME'] ?? '');

// Canonical URL calculation
if (!isset($canonicalUrl)) {
    $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
    $reqPath = parse_url($rawUri, PHP_URL_PATH) ?: '/';
    if ($reqPath === '/index.php') {
        $reqPath = '/';
    }
    $canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . $reqPath;
}

// Dynamic Robots Meta Tag to protect private panels & conserve crawl budget
$privateScripts = [
    'admin.php', 'app.php', 'checkin.php', 'journal.php', 'profile.php', 
    'cortex.php', 'cortex-dashboard.php', 'email-admin.php', 'vault-admin.php', 
    'geo-admin.php', 'ncke-admin.php', 'ncke-console.php', 'club-admin.php', 
    'admin-orders.php', 'event-admin.php', 'event-builder.php', 'social-admin.php',
    'finance.php', 'acl-admin.php', 'company-brain.php', 'company-brain-start.php',
    'order-confirmation.php', 'checkout.php', 'cart.php', 'wallet.php', 'preferences.php'
];
$isNoIndex = ($u !== null) || in_array($curScript, $privateScripts, true) || !empty($noIndexPage);
$metaRobotsDirective = $isNoIndex ? 'noindex, nofollow, noarchive' : ($metaRobots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');

// Absolute OpenGraph & Twitter Card Image
$defaultOgImg = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/assets/img/dependex-rainbow-badge.jpg';
$ogImageResolved = !empty($ogImage) ? $ogImage : $defaultOgImg;
if (strpos($ogImageResolved, 'http') !== 0) {
    $ogImageResolved = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/' . ltrim($ogImageResolved, '/');
}
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
  <meta name="robots" content="<?=h($metaRobotsDirective)?>">

  <!-- SEO Canonical & Multilingual Hreflang -->
  <link rel="canonical" href="<?=h($canonicalUrl)?>">
  <link rel="alternate" hreflang="it" href="<?=h($canonicalUrl)?>">
  <link rel="alternate" hreflang="x-default" href="<?=h($canonicalUrl)?>">
  <link rel="help" type="text/plain" href="https://<?=h($brand['domain'] ?? 'dependex.social')?>/llms.txt" title="LLM Knowledge Context">

  <!-- OpenGraph / Facebook -->
  <meta property="og:type" content="<?=$ogType ?? 'website'?>">
  <meta property="og:title" content="<?=h($pageTitle)?> · <?=h($brand['name'])?>">
  <meta property="og:description" content="<?=h($metaDesc)?>">
  <meta property="og:url" content="<?=h($canonicalUrl)?>">
  <meta property="og:site_name" content="<?=h($brand['name'])?>">
  <meta property="og:locale" content="it_IT">
  <meta property="og:image" content="<?=h($ogImageResolved)?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="<?=h($pageTitle)?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?=h($pageTitle)?> · <?=h($brand['name'])?>">
  <meta name="twitter:description" content="<?=h($metaDesc)?>">
  <meta name="twitter:image" content="<?=h($ogImageResolved)?>">

  <!-- Favicon & Icons -->
  <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
  <link rel="shortcut icon" href="assets/img/favicon.svg">
  <link rel="apple-touch-icon" href="assets/img/app-icon.svg">
  <link rel="manifest" href="manifest.webmanifest">

  <!-- Performance Resource Hints -->
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="dns-prefetch" href="https://fonts.googleapis.com">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="assets/css/app.css?v=<?=filemtime(__DIR__.'/assets/css/app.css')?>">
  <link rel="stylesheet" href="assets/css/luxury-patterns.css?v=<?=filemtime(__DIR__.'/assets/css/luxury-patterns.css')?>">
  <link rel="stylesheet" href="assets/css/rainbow-neon.css?v=<?=filemtime(__DIR__.'/assets/css/rainbow-neon.css')?>">
  <link rel="stylesheet" href="assets/css/mobile-916.css?v=<?=filemtime(__DIR__.'/assets/css/mobile-916.css')?>">
  <link rel="stylesheet" href="assets/css/dependex-human-community.css?v=<?=filemtime(__DIR__.'/assets/css/dependex-human-community.css')?>">
  <link rel="stylesheet" href="assets/css/universal-cart-checkout.css?v=<?=filemtime(__DIR__.'/assets/css/universal-cart-checkout.css')?>">
  <link rel="stylesheet" href="assets/css/universal-chat-ai.css?v=<?=filemtime(__DIR__.'/assets/css/universal-chat-ai.css')?>">
  <title><?=h($pageTitle)?> · <?=h($brand['name'])?></title>

  <!-- Schema.org Global JSON-LD (Organization & WebSite) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "NGO",
        "@id": "https://<?=h($brand['domain'])?>/#organization",
        "name": "<?=h($brand['name'])?>",
        "alternateName": ["OLTRE Social", "Club Alcologici Territoriali", "ACAT Basso Polesine O.D.V."],
        "description": "<?=h($metaDesc)?>",
        "url": "https://<?=h($brand['domain'])?>/",
        "logo": {
          "@type": "ImageObject",
          "url": "https://<?=h($brand['domain'])?>/assets/img/dependex-rainbow-badge.jpg"
        },
        "email": "info@dependex.support",
        "telephone": "+39-800-974-250",
        "contactPoint": [
          {
            "@type": "ContactPoint",
            "telephone": "+39-800-974-250",
            "contactType": "Numero Verde AICAT",
            "areaServed": "IT",
            "availableLanguage": ["Italian"]
          },
          {
            "@type": "ContactPoint",
            "email": "info@dependex.support",
            "contactType": "supporto tecnico e istituzionale",
            "areaServed": "IT",
            "availableLanguage": ["Italian"]
          }
        ],
        "sameAs": [
          "https://oltre.social",
          "https://beway.life"
        ]
      },
      {
        "@type": "WebSite",
        "@id": "https://<?=h($brand['domain'])?>/#website",
        "url": "https://<?=h($brand['domain'])?>/",
        "name": "<?=h($brand['name'])?>",
        "description": "Piattaforma ecologico-sociale per la sobrietà e rete dei Club Alcologici Territoriali",
        "publisher": {
          "@id": "https://<?=h($brand['domain'])?>/#organization"
        },
        "potentialAction": {
          "@type": "SearchAction",
          "target": "https://<?=h($brand['domain'])?>/world-club-explorer.php?q={search_term_string}",
          "query-input": "required name=search_term_string"
        },
        "inLanguage": "it-IT"
      }
    ]
  }
  </script>

  <?php if (!empty($breadcrumbs) && is_array($breadcrumbs)): ?>
  <!-- BreadcrumbList JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      <?php 
      $bItems = [];
      $bPos = 1;
      foreach ($breadcrumbs as $bName => $bUrl) {
          $bTarget = strpos($bUrl, 'http') === 0 ? $bUrl : ('https://' . ($brand['domain'] ?? 'dependex.social') . '/' . ltrim($bUrl, '/'));
          $bItems[] = json_encode([
              "@type" => "ListItem",
              "position" => $bPos++,
              "name" => $bName,
              "item" => $bTarget
          ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      }
      echo implode(",\n      ", $bItems);
      ?>
    ]
  }
  </script>
  <?php endif; ?>

  <?php if (!empty($pageSchemaJson)): ?>
  <!-- Page Specific JSON-LD -->
  <script type="application/ld+json">
  <?=is_string($pageSchemaJson) ? $pageSchemaJson : json_encode($pageSchemaJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?>
  </script>
  <?php endif; ?>

  <script>
    (function(){
      const t = localStorage.getItem('oltre_theme') || 'dark';
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
</head>
<?php
$curScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<body class="site-<?=strtolower($brand['name'])?>">
  <!-- GLOBAL SVG RAINBOW GRADIENT DEFINITIONS -->
  <svg width="0" height="0" style="position:absolute;visibility:hidden;" aria-hidden="true">
    <defs>
      <linearGradient id="dxGlobalRainbowGrad" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#ff3344" />
        <stop offset="16%" stop-color="#ff7700" />
        <stop offset="33%" stop-color="#ffd700" />
        <stop offset="50%" stop-color="#00ff77" />
        <stop offset="66%" stop-color="#00d4ff" />
        <stop offset="83%" stop-color="#3a55ff" />
        <stop offset="100%" stop-color="#b829ff" />
      </linearGradient>
    </defs>
  </svg>

  <a href="#mainContent" class="skip-link">Salta al contenuto principale</a>
  <header class="topbar">
    <a class="brand" href="<?=$u ? 'app.php' : 'index.php'?>">
      <span class="brand-mark brand-mark-rainbow"><img src="assets/img/dependex-badge-icon.webp" alt="Logo DEPENDEX" width="44" height="44"></span>
      <span><b><?=h($brand['name'])?></b><small><?=h(APP_PAYOFF)?></small></span>
    </a>

    <!-- BARRA DI NAVIGAZIONE PRIMARIA DESKTOP (6 PILASTRI + PARLA CON NOI) -->
    <nav class="topbar-nav" aria-label="Navigazione principale">
      <a href="index.php" class="topbar-nav-link <?=($curScript==='index.php'||$curScript==='')?'active':''?>">Home</a>
      <a href="world-club-explorer.php" class="topbar-nav-link <?=$curScript==='world-club-explorer.php'||$curScript==='club-public.php'?'active':''?>">Trova un Club</a>
      <a href="mappa-club.php" class="topbar-nav-link <?=$curScript==='mappa-club.php'?'active':''?>" style="position:relative;" title="Mappa Georeferenziata 2D dei 322 Club Italiani con GPS">
        <span style="color:#00f0ff;font-weight:700;">Mappa 2D</span>
        <span style="font-size:0.62rem;background:#00f0ff;color:#070a12;font-weight:800;padding:1px 5px;border-radius:6px;margin-left:2px;">322</span>
      </a>
      <a href="events-public.php" class="topbar-nav-link <?=$curScript==='events-public.php'||$curScript==='event-detail.php'?'active':''?>">Vivi la Comunità</a>
      <a href="storie.php" class="topbar-nav-link <?=$curScript==='storie.php'?'active':''?>">Storie</a>
      <a href="metodo.php" class="topbar-nav-link <?=$curScript==='metodo.php'||$curScript==='academy-public.php'?'active':''?>">Impara</a>
      <a href="world-map.php" class="topbar-nav-link <?=$curScript==='world-map.php'?'active':''?>">Rete</a>
      <a href="orientamento.php" class="topbar-nav-link <?=$curScript==='orientamento.php'?'active':''?>" title="Mappa del Benessere e Orientamento">Orientamento</a>
      <a href="parla-con-noi.php" class="topbar-nav-btn <?=$curScript==='parla-con-noi.php'?'active':''?>">
        <?=dx_icon('message-circle', '', 14)?> Parla con Noi
      </a>
    </nav>

    <!-- TOPBAR METRICS (VISITATORI TOTALI & UTENTI LIVE CON PULSE GLOW) -->
    <?php $dxTelemetry = site_live_telemetry(); ?>
    <div class="topbar-live-counters" aria-label="Statistiche del portale in tempo reale">
      <div class="counter-badge counter-badge-visits" title="Visitatori complessivi della piattaforma">
        <span class="counter-icon"><?=dx_icon('eye', 'text-neon-cyan', 15)?></span>
        <div class="counter-text">
          <span class="counter-val text-neon-cyan" id="dxTotalVisits"><?=$dxTelemetry['formatted_visits']?></span>
          <span class="counter-lbl">visite</span>
        </div>
      </div>
      <div class="counter-badge counter-badge-live" title="Utenti connessi in questo istante">
        <span class="live-pulse-dot"></span>
        <div class="counter-text">
          <span class="counter-val text-neon-green" id="dxLiveUsers"><?=$dxTelemetry['formatted_live']?></span>
          <span class="counter-lbl">online</span>
        </div>
      </div>
    </div>

    <div class="header-actions">
      <a href="cart.php" class="topbar-cart-btn" title="Carrello Acquisti" aria-label="Carrello">
        <?=dx_icon('shopping-cart', 'text-neon-gold', 18)?>
      </a>
      <button type="button" class="theme-toggle" aria-label="Cambia tema" title="Cambia tema"><?=dx_icon('sun', '', 18)?></button>
      <!-- BURGER MENU BUTTON PER MENU COMPLETO MOBILE E DRAWER -->
      <button type="button" class="burger-btn" id="burgerBtn" aria-label="Menu di Navigazione" aria-expanded="false" aria-controls="drawerNav" title="Apri menu completo">
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
          <a href="dashboard.php" class="drawer-link <?=$curScript==='dashboard.php'?'active':''?>"><?=dx_icon('activity','text-neon-cyan',18)?> <b>Dashboard & Sobrietà</b></a>
          <a href="orientamento.php" class="drawer-link <?=$curScript==='orientamento.php'?'active':''?>"><?=dx_icon('compass','text-neon-cyan',18)?> <b>Mappa del Benessere 4.0</b></a>
          <a href="ruota-della-vita.php" class="drawer-link <?=$curScript==='ruota-della-vita.php'?'active':''?>"><?=dx_icon('compass','text-neon-gold',18)?> Ruota della Vita 2D/3D</a>
          <a href="piramide-maslow.php" class="drawer-link <?=$curScript==='piramide-maslow.php'?'active':''?>"><?=dx_icon('layers','text-neon-purple',18)?> Piramide Maslow 2D/3D</a>
          <a href="app.php" class="drawer-link <?=$curScript==='app.php'?'active':''?>"><?=dx_icon('home','',18)?> Home Utente</a>
          <a href="checkin.php" class="drawer-link <?=$curScript==='checkin.php'?'active':''?>"><?=dx_icon('edit','',18)?> Daily Check-in (+5 DRX)</a>
          <a href="journal.php" class="drawer-link <?=$curScript==='journal.php'?'active':''?>"><?=dx_icon('book-open','',18)?> Diario & Gratitudine</a>
          <a href="sobriety.php" class="drawer-link <?=$curScript==='sobriety.php'?'active':''?>"><?=dx_icon('activity','',18)?> Sobrietà & Traguardi</a>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">I PILASTRI DELLA COMUNITÀ</span>
          <a href="world-club-explorer.php" class="drawer-link <?=$curScript==='world-club-explorer.php'||$curScript==='club-public.php'?'active':''?>"><?=dx_icon('map-pin','',18)?> Trova un Club Territoriale</a>
          <a href="mappa-club.php" class="drawer-link <?=$curScript==='mappa-club.php'?'active':''?>"><?=dx_icon('compass','text-neon-cyan',18)?> <b>Mappa 2D Italia (322 Club)</b></a>
          <a href="recensioni.php" class="drawer-link <?=$curScript==='recensioni.php'?'active':''?>"><?=dx_icon('star','text-neon-gold',18)?> <b>Recensioni & Testimonianze</b></a>
          <a href="parla-con-noi.php" class="drawer-link <?=$curScript==='parla-con-noi.php'?'active':''?>"><?=dx_icon('message-circle','text-neon-cyan',18)?> <b>Parla con Noi (Ascolto Riservato)</b></a>
          <a href="events-public.php" class="drawer-link <?=$curScript==='events-public.php'?'active':''?>"><?=dx_icon('calendar','',18)?> Vivi la Comunità & Eventi</a>
          <a href="storie.php" class="drawer-link <?=$curScript==='storie.php'?'active':''?>"><?=dx_icon('users','',18)?> Storie di Comunità</a>
          <a href="world-map.php" class="drawer-link <?=$curScript==='world-map.php'?'active':''?>"><?=dx_icon('compass','',18)?> Mappa Mondiale 2D/3D</a>
          <a href="metodo.php" class="drawer-link <?=$curScript==='metodo.php'?'active':''?>"><?=dx_icon('feather','',18)?> Il Metodo Hudolin</a>
          <a href="academy.php" class="drawer-link <?=$curScript==='academy.php'?'active':''?>"><?=dx_icon('academic','',18)?> Formazione Servitori</a>
          <a href="club.php" class="drawer-link <?=$curScript==='club.php'?'active':''?>"><?=dx_icon('users','',18)?> Il mio Club</a>
        </nav>
        <nav class="drawer-nav-group">
          <span class="drawer-section-title">RISORSE & SERVIZI</span>
          <a href="profile.php" class="drawer-link <?=$curScript==='profile.php'?'active':''?>"><?=dx_icon('users','',18)?> Il mio Profilo</a>
          <a href="guida-gratuita.php" class="drawer-link <?=$curScript==='guida-gratuita.php'?'active':''?>"><?=dx_icon('sparkles','',18)?> Guida Gratuita Famiglia</a>
          <a href="offers.php" class="drawer-link <?=$curScript==='offers.php'?'active':''?>"><?=dx_icon('book-open','',18)?> Collana Didattica KDP</a>
          <a href="cart.php" class="drawer-link <?=$curScript==='cart.php'?'active':''?>"><?=dx_icon('shopping-cart','',18)?> Carrello Acquisti</a>
          <a href="help.php" class="drawer-link <?=$curScript==='help.php'?'active':''?>"><?=dx_icon('shield','',18)?> Supporto Immediato</a>
          <a href="privacy.php" class="drawer-link <?=$curScript==='privacy.php'?'active':''?>"><?=dx_icon('lock','',18)?> Riservatezza & Anonimato</a>
          <a href="logout.php" class="drawer-link drawer-logout"><?=dx_icon('log-out','',18)?> Esci dall'App</a>
        </nav>
      <?php else:?>
        <div class="drawer-auth-card" style="padding:16px;border-radius:18px;background:rgba(12,16,26,0.9);border:1px solid rgba(224,169,109,0.35);box-shadow:0 0 20px rgba(224,169,109,0.15);margin-bottom:14px;">
          <div class="badge-human mb-2" style="font-size:0.72rem;"><span class="dot"></span> RETE GRATUITA DEI CLUB</div>
          <p style="font-size:0.84rem;color:#cbd5e1;line-height:1.45;margin:0 0 12px;">Oltre 540 Club territoriali, metodo Hudolin e supporto continuativo senza giudizio.</p>
          <div style="display:flex;gap:8px;">
            <a class="btn primary small" href="login.php" style="flex:1;text-align:center;">Accedi</a>
            <a class="btn small" href="register.php" style="flex:1;border:1px solid rgba(224,169,109,0.4);color:#ffffff;border-radius:12px;text-align:center;">Registrati</a>
          </div>
        </div>

        <nav class="drawer-nav-group">
          <span class="drawer-section-title text-amber">I PILASTRI DELLA COMUNITÀ</span>
          <a href="index.php" class="drawer-link <?=$curScript==='index.php'?'active':''?>"><?=dx_icon('home','',18)?> Home</a>
          <a href="world-club-explorer.php" class="drawer-link <?=$curScript==='world-club-explorer.php'||$curScript==='club-public.php'?'active':''?>"><?=dx_icon('map-pin','',18)?> <b>Trova un Club Territoriale</b></a>
          <a href="mappa-club.php" class="drawer-link <?=$curScript==='mappa-club.php'?'active':''?>"><?=dx_icon('compass','text-neon-cyan',18)?> <b>Mappa 2D Italia (322 Club)</b></a>
          <a href="recensioni.php" class="drawer-link <?=$curScript==='recensioni.php'?'active':''?>"><?=dx_icon('star','text-neon-gold',18)?> <b>Recensioni dei Club</b></a>
          <a href="parla-con-noi.php" class="drawer-link highlight-gold <?=$curScript==='parla-con-noi.php'?'active':''?>"><?=dx_icon('message-circle','text-neon-gold',18)?> <b>Parla con Noi (Ascolto)</b></a>
          <a href="storie.php" class="drawer-link <?=$curScript==='storie.php'?'active':''?>"><?=dx_icon('users','',18)?> Storie di Comunità</a>
          <a href="events-public.php" class="drawer-link <?=$curScript==='events-public.php'?'active':''?>"><?=dx_icon('calendar','',18)?> Vivi la Comunità (Eventi)</a>
          <a href="world-map.php" class="drawer-link <?=$curScript==='world-map.php'?'active':''?>"><?=dx_icon('compass','',18)?> Mappa Mondiale Club</a>
        </nav>

        <nav class="drawer-nav-group">
          <span class="drawer-section-title">CRESCITA PERSONALE & STRUMENTI</span>
          <a href="orientamento.php" class="drawer-link <?=$curScript==='orientamento.php'?'active':''?>"><?=dx_icon('compass','text-neon-cyan',18)?> <b>Mappa del Benessere 4.0</b></a>
          <a href="dashboard.php" class="drawer-link <?=$curScript==='dashboard.php'?'active':''?>"><?=dx_icon('activity','text-neon-cyan',18)?> <b>Dashboard & Contatore Sobrietà</b></a>
          <a href="ruota-della-vita.php" class="drawer-link <?=$curScript==='ruota-della-vita.php'?'active':''?>"><?=dx_icon('compass','text-neon-gold',18)?> Ruota della Vita 2D/3D</a>
          <a href="piramide-maslow.php" class="drawer-link <?=$curScript==='piramide-maslow.php'?'active':''?>"><?=dx_icon('layers','text-neon-purple',18)?> Piramide Maslow 2D/3D</a>
          <a href="metodo.php" class="drawer-link <?=$curScript==='metodo.php'?'active':''?>"><?=dx_icon('feather','',18)?> Il Metodo Hudolin (3 Livelli)</a>
          <a href="academy-public.php" class="drawer-link <?=$curScript==='academy-public.php'?'active':''?>"><?=dx_icon('academic','',18)?> Sovereign Academy</a>
          <a href="guida-gratuita.php" class="drawer-link <?=$curScript==='guida-gratuita.php'?'active':''?>"><?=dx_icon('sparkles','',18)?> Guida Gratuita Famiglia</a>
          <a href="evento-ottobre-taglio-di-po.php" class="drawer-link <?=$curScript==='evento-ottobre-taglio-di-po.php'||$curScript==='event-detail.php'?'active':''?>"><?=dx_icon('award','',18)?> Corso Esperienziale Taglio di Po</a>
          <a href="offers.php" class="drawer-link <?=$curScript==='offers.php'?'active':''?>"><?=dx_icon('book-open','',18)?> Libri & Collana KDP</a>
          <a href="viaggi-esperienziali.php" class="drawer-link <?=$curScript==='viaggi-esperienziali.php'||$curScript==='crociera-benessere-masterclass.php'?'active':''?>"><?=dx_icon('compass','',18)?> Viaggi Esperienziali (BEWAY.LIFE)</a>
          <a href="cortex.php" class="drawer-link <?=$curScript==='cortex.php'?'active':''?>"><?=dx_icon('cpu','',18)?> Cortex AI & Ascolto</a>
        </nav>

        <nav class="drawer-nav-group">
          <span class="drawer-section-title">SUPPORTO & RASSICURAZIONE</span>
          <a href="domande-frequenti.php" class="drawer-link <?=$curScript==='domande-frequenti.php'?'active':''?>"><?=dx_icon('help-circle','text-neon-gold',18)?> <b>Domande che vuoi fare</b></a>
          <a href="help.php" class="drawer-link <?=$curScript==='help.php'?'active':''?>"><?=dx_icon('shield','',18)?> Aiuto Immediato & Emergenze</a>
          <a href="privacy.php" class="drawer-link <?=$curScript==='privacy.php'?'active':''?>"><?=dx_icon('lock','',18)?> Riservatezza & Anonimato</a>
          <a href="terms.php" class="drawer-link <?=$curScript==='terms.php'?'active':''?>"><?=dx_icon('file-text','',18)?> Termini e Trasparenza</a>
        </nav>
      <?php endif;?>
    </div>
    <div class="drawer-footer">
      <button type="button" class="theme-toggle btn small" style="width:100%;margin-bottom:8px;border-radius:12px;display:flex;align-items:center;justify-content:center;gap:8px;"><?=dx_icon('sun','',16)?> Cambia Tema</button>
      <div style="font-size:12px;text-align:center;margin-top:10px;">
        <a href="mailto:info@dependex.support" style="color:var(--text-muted);text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:6px;"><?=dx_icon('mail','',14)?> info@dependex.support</a>
      </div>
    </div>
  </aside>

  <?php if(!empty($_SESSION['flash'])):?>
    <div class="toast flash" role="status" aria-live="polite"><?=h($_SESSION['flash'])?></div>
    <?php unset($_SESSION['flash']);?>
  <?php endif;?>
  <div class="offline-pill" role="status" aria-live="polite"><?=dx_icon('wifi','',14)?> Modalità offline attiva: dati salvati disponibili</div>
  <main class="page" id="mainContent">