<?php 
require_once 'bootstrap.php';
$public = isset($_GET['public']);
if (!$public) $u = current_user(); else $u = null;

$pdo = db();
$natMetrics = \Dependex\Clubs\ClubMetricsService::getNationalSummary($pdo);
$globMetrics = \Dependex\Clubs\ClubMetricsService::getGlobalSummary($pdo);

$totalNodes = $globMetrics['total_nodes'];
$totalClubs = $natMetrics['local_clubs'];
$totalAcat  = (int)$pdo->query("SELECT COUNT(*) FROM dependex_world_registry WHERE network_level IN ('TERRITORIAL','PROVINCIAL','TERRITORIAL_ASSOCIATION','TERRITORIAL_ACAT') AND country='Italy'")->fetchColumn();
$totalArcat = (int)$pdo->query("SELECT COUNT(*) FROM dependex_world_registry WHERE network_level='REGIONAL' AND country='Italy'")->fetchColumn();
$totalFamilies = $natMetrics['estimated_families'];

// Recupero entità strutturate
$nationalEntities = $pdo->query("SELECT * FROM dependex_world_registry WHERE network_level IN ('NATIONAL','WORLD','CONTINENT') AND (country='Italy' OR network_level='WORLD' OR entity_name LIKE '%Eurocare%') ORDER BY network_rank DESC, entity_name")->fetchAll(PDO::FETCH_ASSOC);
$arcatEntities = $pdo->query("SELECT * FROM dependex_world_registry WHERE network_level='REGIONAL' AND country='Italy' ORDER BY region, entity_name")->fetchAll(PDO::FETCH_ASSOC);
$acatEntities  = $pdo->query("SELECT * FROM dependex_world_registry WHERE network_level IN ('TERRITORIAL','PROVINCIAL','TERRITORIAL_ASSOCIATION') AND country='Italy' ORDER BY region, province, entity_name")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Trova il tuo Club Territoriale · Portale Ufficiale AICAT, ARCAT & ACAT';
$metaDesc = 'Elenco completo, mappa 2D/3D e portali web dei Club Alcologici Territoriali (CAT), Associazioni Regionali (ARCAT) e Territoriali (ACAT). Metodo Hudolin, 100% gratuito e riservato.';
$breadcrumbs = [
    'Home' => '/',
    'Trova un Club' => 'world-club-explorer.php'
];
$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "NGO",
    "name" => "Rete dei Club Alcologici Territoriali (CAT)",
    "description" => $metaDesc,
    "url" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/world-club-explorer.php",
    "areaServed" => [
        "@type" => "Country",
        "name" => "Italia"
    ],
    "serviceType" => "Accoglienza, Auto-Mutuo-Aiuto, Ecologia Familiare e Sobrietà",
    "offers" => [
        "@type" => "Offer",
        "price" => "0.00",
        "priceCurrency" => "EUR",
        "availability" => "https://schema.org/InStock",
        "description" => "Servizio completamente gratuito, aperto a famiglie e singoli individui."
    ]
];
require '_header.php';
require '_dependex-world-map.php';
?>

<section class="hero compact" style="text-align:center;padding:3rem 1.5rem 2rem;">
  <div class="gold-glow-badge mb-3">
    <?=dx_icon('compass', '', 14)?>
    <span>RETE ECOLOGICO-SOCIALE HUDOLIN · <?=$totalNodes?> NODI GLOBALI (<?=$natMetrics['total_presidi']?> IN ITALIA) · STIMA <?=number_format($totalFamilies, 0, ',', '.')?> FAMIGLIE ACCOLTE</span>
  </div>
  <h1 style="font-size:clamp(1.8rem, 3.5vw, 2.6rem);font-weight:800;letter-spacing:-0.02em;margin:0.5rem 0 0.8rem;color:#FFFFFF;">
    Trova il Club più vicino a casa tua.<br>
    <span class="gold-foil-text">
      C’è una sedia pronta per te, senza formalità né burocrazia.
    </span>
  </h1>
  <p style="max-width:750px;margin:0 auto;color:#d1d5db;font-size:1.05rem;line-height:1.65;">
    Nei Club Alcologici Territoriali (CAT) non ci sono cartelle cliniche esposte né diagnosi punitive. C’è una comunità multifamiliare che ha conosciuto la stessa sofferenza e che oggi cammina insieme per il benessere e la sobrietà, una settimana alla volta.
  </p>
  
  <div style="display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap;margin-top:1.5rem;font-size:0.9rem;color:#D4AF37;">
    <span style="display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('check-circle', '', 16)?> <b style="color:#FFFFFF;">Completamente Gratuito</b>
    </span>
    <span style="display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('check-circle', '', 16)?> <b style="color:#FFFFFF;">Aperto a Famigliari e Amici</b>
    </span>
    <span style="display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('check-circle', '', 16)?> <b style="color:#FFFFFF;">Riservatezza & Dignità Assoluta</b>
    </span>
  </div>

  <div style="margin-top:1.8rem;display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
    <a href="mappa-club.php" class="btn primary glow" style="font-size:1.02rem;padding:12px 24px;border-radius:14px;background:linear-gradient(135deg, #00f0ff, #0077ff);color:#070a12;font-weight:800;box-shadow:0 0 25px rgba(0,240,255,0.45);text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
      <?=dx_icon('map-pin', '', 18)?> <b>Mappa Georeferenziata 2D d'Italia (Club & APCAT con GPS)</b>
    </a>
  </div>

  <!-- NUMERO VERDE NAZIONALE DIRETTO -->
  <div style="max-width:680px;margin:1.8rem auto 0;background:rgba(212,175,55,0.12);border:1px solid rgba(212,175,55,0.5);border-radius:14px;padding:12px 20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:14px;">
    <div style="display:flex;align-items:center;gap:8px;">
      <?=dx_icon('phone', 'text-neon-gold', 20)?>
      <span style="color:#FFFFFF;font-size:0.95rem;font-weight:700;">Numero Verde Nazionale AICAT:</span>
    </div>
    <a href="tel:800974250" style="color:#ffd700;font-size:1.3rem;font-weight:900;text-decoration:none;letter-spacing:0.05em;display:inline-flex;align-items:center;gap:6px;">
      800 974250
    </a>
    <span style="color:#94a3b8;font-size:0.8rem;background:rgba(255,255,255,0.06);padding:2px 8px;border-radius:6px;">Chiamata Gratuita da tutta Italia</span>
  </div>

  <!-- KPI QUICK METRICS -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;max-width:800px;margin:1.8rem auto 0;">
    <div class="metric" style="background:rgba(255,255,255,0.03);border:1px solid rgba(212,175,55,0.25);border-radius:12px;padding:12px;">
      <b style="font-size:1.5rem;color:#ffd700;display:block;"><?=$totalClubs?></b>
      <span style="font-size:0.8rem;color:#cbd5e1;">Club Locali (CAT)</span>
    </div>
    <div class="metric" style="background:rgba(255,255,255,0.03);border:1px solid rgba(212,175,55,0.25);border-radius:12px;padding:12px;">
      <b style="font-size:1.5rem;color:#00ff77;display:block;"><?=$totalAcat?></b>
      <span style="font-size:0.8rem;color:#cbd5e1;">Associazioni Territoriali (ACAT)</span>
    </div>
    <div class="metric" style="background:rgba(255,255,255,0.03);border:1px solid rgba(212,175,55,0.25);border-radius:12px;padding:12px;">
      <b style="font-size:1.5rem;color:#00d4ff;display:block;"><?=$totalArcat?></b>
      <span style="font-size:0.8rem;color:#cbd5e1;">Associazioni Regionali (ARCAT)</span>
    </div>
    <div class="metric" style="background:rgba(255,255,255,0.03);border:1px solid rgba(212,175,55,0.25);border-radius:12px;padding:12px;">
      <b style="font-size:1.5rem;color:#ff7700;display:block;">1</b>
      <span style="font-size:0.8rem;color:#cbd5e1;">Coordinamento Nazionale AICAT</span>
    </div>
  </div>
</section>

<!-- GUIDA PRATICA ALL'ORIENTAMENTO TERRITORIALE -->
<section style="max-width:1100px;margin:0 auto 1.5rem;padding:0 1rem;">
  <div class="card p-3 p-md-4" style="background:rgba(15,20,32,0.92);border:1px solid rgba(212,175,55,0.3);border-radius:14px;">
    <h3 style="color:#FFFFFF;font-size:1.05rem;margin:0 0 10px;font-weight:800;display:flex;align-items:center;gap:8px;">
      <?=dx_icon('compass', 'text-neon-cyan', 18)?> Guida Rapida: Come Trovare il Club Più Vicino a Te
    </h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;font-size:0.86rem;color:#cbd5e1;line-height:1.45;">
      <div style="background:rgba(255,255,255,0.02);padding:10px;border-radius:8px;border-left:3px solid #00d4ff;">
        <b style="color:#00d4ff;display:block;margin-bottom:4px;">1. Portale Regionale (ARCAT)</b>
        Inizia consultando il sito ARCAT della tua regione (es. Veneto, FVG, Lombardia, Toscana, Emilia-Romagna, Liguria, Puglia, Abruzzo, Molise, Umbria).
      </div>
      <div style="background:rgba(255,255,255,0.02);padding:10px;border-radius:8px;border-left:3px solid #00ff77;">
        <b style="color:#00ff77;display:block;margin-bottom:4px;">2. Sezione "ACAT e CAT"</b>
        Consulta gli elenchi dei club di zona (es. <a href="https://www.arcatfvg.it/acat-e-cat" target="_blank" rel="noopener" style="color:#00ff77;text-decoration:underline;">arcatfvg.it/acat-e-cat</a>) per recapiti e orari degli incontri.
      </div>
      <div style="background:rgba(255,255,255,0.02);padding:10px;border-radius:8px;border-left:3px solid #ffd700;">
        <b style="color:#ffd700;display:block;margin-bottom:4px;">3. Numero Verde o Ser.D / ASL</b>
        Se nella tua provincia non compare un sito web dedicato, chiama il <b>800 974250</b> o contatta il Ser.D / ASL locale che conserva i registri aggiornati.
      </div>
      <div style="background:rgba(255,255,255,0.02);padding:10px;border-radius:8px;border-left:3px solid #ff7700;">
        <b style="color:#ff7700;display:block;margin-bottom:4px;">4. Canali Territoriali</b>
        Molte ACAT e singoli Club mantengono pagine informative locali attive per aggiornare orari e sedi settimanali.
      </div>
    </div>
  </div>
</section>

<!-- SELETTORE MODALITÀ: MAPPA 2D/3D VS DIRECTORY PORTALI -->
<div style="max-width:1100px;margin:1rem auto 2rem;padding:0 1rem;">
  <div class="segmented" style="display:flex;justify-content:center;gap:8px;background:rgba(15,20,32,0.8);padding:6px;border-radius:14px;border:1px solid rgba(212,175,55,0.3);max-width:560px;margin:0 auto;">
    <button type="button" id="tabBtnMap" class="active" style="flex:1;padding:10px 16px;border-radius:10px;border:none;background:rgba(212,175,55,0.2);color:#FFFFFF;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;">
      <?=dx_icon('compass', '', 16)?> Mappa 2D / 3D
    </button>
    <button type="button" id="tabBtnDir" style="flex:1;padding:10px 16px;border-radius:10px;border:none;background:transparent;color:#94a3b8;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:6px;">
      <?=dx_icon('book-open', '', 16)?> Elenco Portali & Hub AICAT · ARCAT · ACAT
    </button>
  </div>
</div>

<!-- VISTA 1: MAPPA MONDIALE INTERATTIVA -->
<div id="sectionMapStage" style="margin-top:0.5rem;">
  <?=dependex_world_map_card('70vh')?>
</div>

<!-- VISTA 2: DIRECTORY NAZIONALE, REGIONALE E TERRITORIALE DEI PORTALI WEB -->
<div id="sectionDirectoryStage" style="max-width:1200px;margin:1rem auto 4rem;padding:0 1.2rem;display:none;">

  <!-- BARRA DI RICERCA E FILTRI DELLA DIRECTORY -->
  <div class="card p-3 mb-4" style="background:rgba(18,24,38,0.95);border:1px solid rgba(212,175,55,0.35);border-radius:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
      <div style="flex:2;min-width:240px;position:relative;">
        <input type="text" id="dirSearchInput" placeholder="Cerca associazione, città, provincia, telefono..." 
               style="width:100%;padding:10px 14px;border-radius:10px;background:rgba(10,13,22,0.9);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;font-size:0.95rem;">
      </div>
      <div style="flex:1;min-width:180px;">
        <select id="dirRegionSelect" style="width:100%;padding:10px 12px;border-radius:10px;background:rgba(10,13,22,0.9);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;font-size:0.92rem;">
          <option value="">Tutte le Regioni</option>
          <option value="Abruzzo">Abruzzo</option>
          <option value="Basilicata">Basilicata</option>
          <option value="Calabria">Calabria</option>
          <option value="Campania">Campania</option>
          <option value="Emilia-Romagna">Emilia-Romagna</option>
          <option value="Friuli-Venezia Giulia">Friuli Venezia Giulia</option>
          <option value="Lazio">Lazio</option>
          <option value="Liguria">Liguria</option>
          <option value="Lombardia">Lombardia</option>
          <option value="Marche">Marche</option>
          <option value="Molise">Molise</option>
          <option value="Piemonte">Piemonte</option>
          <option value="Puglia">Puglia</option>
          <option value="Sardegna">Sardegna</option>
          <option value="Sicilia">Sicilia</option>
          <option value="Toscana">Toscana</option>
          <option value="Trentino-Alto Adige">Trentino-Alto Adige</option>
          <option value="Umbria">Umbria</option>
          <option value="Valle d'Aosta">Valle d'Aosta</option>
          <option value="Veneto">Veneto</option>
        </select>
      </div>
      <div style="flex:1;min-width:160px;">
        <select id="dirLevelSelect" style="width:100%;padding:10px 12px;border-radius:10px;background:rgba(10,13,22,0.9);border:1px solid rgba(255,255,255,0.15);color:#FFFFFF;font-size:0.92rem;">
          <option value="">Tutti i Livelli</option>
          <option value="NATIONAL">Nazionale / AICAT</option>
          <option value="REGIONAL">Regionale / ARCAT</option>
          <option value="TERRITORIAL">Territoriale / ACAT</option>
        </select>
      </div>
      <div style="display:flex;align-items:center;gap:8px;">
        <label style="font-size:0.85rem;color:#cbd5e1;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
          <input type="checkbox" id="dirOnlyWebCheck"> Solo con sito web
        </label>
      </div>
      <div>
        <button type="button" id="btnGeolocate" class="btn" style="background:linear-gradient(135deg,#00d4ff,#0066cc);color:#FFFFFF;border:none;font-weight:700;padding:9px 15px;border-radius:10px;display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:0.88rem;box-shadow:0 4px 12px rgba(0,212,255,0.25);">
          <?=dx_icon('navigation', '', 14)?> Trova Vicino a Me
        </button>
        <button type="button" id="btnResetGeo" class="btn small" style="display:none;background:rgba(255,255,255,0.08);color:#cbd5e1;border:1px solid rgba(255,255,255,0.2);padding:8px 12px;border-radius:10px;cursor:pointer;font-size:0.82rem;margin-left:6px;">
          Ripristina
        </button>
      </div>
    </div>
    <div style="margin-top:10px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
      <div style="font-size:0.85rem;color:#D4AF37;" id="dirCountLabel">
        Visualizzazione di tutti i portali censiti
      </div>
      <div id="geoStatusLabel" style="font-size:0.82rem;color:#00ff77;display:none;"></div>
    </div>
  </div>

  <!-- SEZIONE A: LIVELLO NAZIONALE & INTERNAZIONALE -->
  <div class="dir-group-block mb-5" data-group="national">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.2rem;border-bottom:1px solid rgba(212,175,55,0.3);padding-bottom:8px;">
      <span class="gold-glow-badge"><?=dx_icon('globe', '', 14)?></span>
      <h2 style="font-size:1.35rem;color:#FFFFFF;margin:0;font-family:var(--font-serif);">1. Livello Nazionale & Internazionale</h2>
    </div>
    <div class="dir-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;">
      <?php foreach ($nationalEntities as $ent): ?>
        <article class="card p-4 dir-card" 
                 data-level="<?=h($ent['network_level'])?>" 
                 data-region="<?=h($ent['region']?:'Internazionale')?>"
                 data-hasweb="<?=!empty($ent['website'])?'1':'0'?>"
                 data-lat="<?=h($ent['latitude']??'')?>"
                 data-lng="<?=h($ent['longitude']??'')?>"
                 data-keywords="<?=h(strtolower($ent['entity_name'].' '.$ent['region'].' '.$ent['city'].' '.$ent['phone'].' '.$ent['notes']))?>"
                 style="background:rgba(20,26,42,0.9);border:1px solid rgba(212,175,55,0.4);border-radius:14px;display:flex;flex-direction:column;justify-content:space-between;">
          <div>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:10px;" class="dir-card-header">
              <span class="badge" style="background:rgba(212,175,55,0.2);color:#ffd700;border:1px solid #ffd700;font-size:0.75rem;padding:2px 8px;border-radius:6px;">
                <?=h($ent['network_level'])?>
              </span>
              <small style="color:#94a3b8;font-family:monospace;font-size:0.75rem;"><?=h($ent['sic_id'])?></small>
            </div>
            <h3 style="color:#FFFFFF;font-size:1.15rem;margin:0 0 8px;font-weight:750;"><?=h($ent['entity_name'])?></h3>
            <p style="color:#cbd5e1;font-size:0.88rem;line-height:1.5;margin-bottom:12px;">
              <?=h($ent['notes']?:'Organismo di riferimento per i Club e la formazione ecologico-sociale.')?>
            </p>
            <div style="font-size:0.84rem;color:#94a3b8;display:flex;flex-direction:column;gap:4px;margin-bottom:14px;">
              <?php if($ent['city']): ?>
                <span>📍 <?=h($ent['address']? $ent['address'].' - ': '')?><?=h($ent['city'])?> (<?=h($ent['province']?:'IT')?>)</span>
              <?php endif; ?>
              <?php if($ent['phone']): ?>
                <span>☎ <b><?=h($ent['phone'])?></b></span>
              <?php endif; ?>
              <?php if($ent['email']): ?>
                <span>✉ <a href="mailto:<?=h($ent['email'])?>" style="color:#00d4ff;text-decoration:none;"><?=h($ent['email'])?></a></span>
              <?php endif; ?>
            </div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,0.08);padding-top:12px;">
            <?php if($ent['website']): ?>
              <a href="<?=h($ent['website'])?>" target="_blank" rel="noopener" class="btn small primary" style="flex:1;text-align:center;">
                <?=dx_icon('external-link', '', 14)?> Visita Portale ↗
              </a>
            <?php endif; ?>
            <a href="/club/<?=urlencode($ent['sic_id'])?>" class="btn small" style="background:rgba(255,255,255,0.05);color:#cbd5e1;border:1px solid rgba(255,255,255,0.15);">
              Scheda Dependex
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SEZIONE B: LE 20 ASSOCIAZIONI REGIONALI (ARCAT & APCAT) -->
  <div class="dir-group-block mb-5" data-group="regional">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.2rem;border-bottom:1px solid rgba(0,212,255,0.3);padding-bottom:8px;">
      <span class="gold-glow-badge" style="border-color:#00d4ff;color:#00d4ff;"><?=dx_icon('map-pin', '', 14)?></span>
      <h2 style="font-size:1.35rem;color:#FFFFFF;margin:0;font-family:var(--font-serif);">2. Livello Regionale (ARCAT · Associazioni Regionali)</h2>
    </div>
    <div class="dir-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;">
      <?php foreach ($arcatEntities as $ent): ?>
        <article class="card p-4 dir-card" 
                 data-level="REGIONAL" 
                 data-region="<?=h($ent['region'])?>"
                 data-hasweb="<?=!empty($ent['website'])?'1':'0'?>"
                 data-lat="<?=h($ent['latitude']??'')?>"
                 data-lng="<?=h($ent['longitude']??'')?>"
                 data-keywords="<?=h(strtolower($ent['entity_name'].' '.$ent['region'].' '.$ent['city'].' '.$ent['province'].' '.$ent['phone'].' '.$ent['notes']))?>"
                 style="background:rgba(18,24,38,0.92);border:1px solid rgba(0,212,255,0.35);border-radius:14px;display:flex;flex-direction:column;justify-content:space-between;">
          <div>
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;" class="dir-card-header">
              <span class="badge" style="background:rgba(0,212,255,0.15);color:#00d4ff;border:1px solid rgba(0,212,255,0.4);font-size:0.75rem;padding:2px 8px;border-radius:6px;">
                REGIONE <?=strtoupper(h($ent['region']))?>
              </span>
              <?php if($ent['direct_children'] > 0): ?>
                <span style="font-size:0.75rem;color:#D4AF37;"><b><?=$ent['direct_children']?></b> Nodi affiliati</span>
              <?php endif; ?>
            </div>
            <h3 style="color:#FFFFFF;font-size:1.15rem;margin:0 0 6px;font-weight:750;"><?=h($ent['entity_name'])?></h3>
            <p style="color:#cbd5e1;font-size:0.86rem;line-height:1.45;margin-bottom:10px;">
              <?=h($ent['notes']?:'Coordinamento regionale dei Club Alcologici Territoriali e delle ACAT di zona.')?>
            </p>
            <div style="font-size:0.82rem;color:#94a3b8;display:flex;flex-direction:column;gap:3px;margin-bottom:12px;">
              <?php if($ent['city']): ?>
                <span>📍 <?=h($ent['address']? $ent['address'].' - ': '')?><?=h($ent['city'])?> <?=h($ent['province']?'('.$ent['province'].')':'')?></span>
              <?php endif; ?>
              <?php if($ent['phone']): ?>
                <span>☎ <b><?=h($ent['phone'])?></b></span>
              <?php endif; ?>
              <?php if($ent['email']): ?>
                <span>✉ <a href="mailto:<?=h($ent['email'])?>" style="color:#00d4ff;text-decoration:none;"><?=h($ent['email'])?></a></span>
              <?php endif; ?>
            </div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,0.08);padding-top:10px;">
            <?php if($ent['website']): ?>
              <a href="<?=h($ent['website'])?>" target="_blank" rel="noopener" class="btn small primary" style="flex:1;text-align:center;">
                <?=dx_icon('external-link', '', 14)?> Sito Ufficiale ↗
              </a>
            <?php else: ?>
              <span style="font-size:0.75rem;color:#94a3b8;align-self:center;font-style:italic;">Contatto istituzionale AICAT</span>
            <?php endif; ?>
            <a href="/club/<?=urlencode($ent['sic_id'])?>" class="btn small" style="background:rgba(255,255,255,0.05);color:#cbd5e1;border:1px solid rgba(255,255,255,0.15);">
              Scheda Dependex
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- SEZIONE C: LIVELLO TERRITORIALE E PROVINCIALE (ACAT & APCAT) -->
  <div class="dir-group-block" data-group="territorial">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:1.2rem;border-bottom:1px solid rgba(0,255,119,0.3);padding-bottom:8px;">
      <span class="gold-glow-badge" style="border-color:#00ff77;color:#00ff77;"><?=dx_icon('users', '', 14)?></span>
      <h2 style="font-size:1.35rem;color:#FFFFFF;margin:0;font-family:var(--font-serif);">3. Livello Territoriale & Provinciale (ACAT & APCAT)</h2>
    </div>
    <div class="dir-cards-grid" id="acatCardsGrid">
      <?php foreach ($acatEntities as $ent): ?>
        <article class="card p-3 dir-card" 
                 data-level="TERRITORIAL" 
                 data-region="<?=h($ent['region'])?>"
                 data-hasweb="<?=!empty($ent['website'])?'1':'0'?>"
                 data-lat="<?=h($ent['latitude']??'')?>"
                 data-lng="<?=h($ent['longitude']??'')?>"
                 data-keywords="<?=h(strtolower($ent['entity_name'].' '.$ent['region'].' '.$ent['city'].' '.$ent['province'].' '.$ent['phone'].' '.$ent['notes']))?>"
                 style="background:rgba(15,20,32,0.92);border:1px solid rgba(0,255,119,0.3);border-radius:14px;display:flex;flex-direction:column;justify-content:space-between;">
          <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;" class="dir-card-header">
              <span class="badge" style="background:rgba(0,255,119,0.15);color:#34d399;border:1px solid rgba(0,255,119,0.4);font-size:0.8125rem;padding:3px 8px;border-radius:6px;font-weight:750;">
                <?=h($ent['region'])?> · <?=h($ent['province']?:'Provincia')?>
              </span>
              <small style="color:#cbd5e1;font-size:0.78rem;font-family:monospace;"><?=h(substr($ent['sic_id'],0,12))?>…</small>
            </div>
            <h3 style="color:#FFFFFF;font-size:1.1rem;margin:0 0 8px;font-weight:750;line-height:1.35;"><?=h($ent['entity_name'])?></h3>
            <p style="color:#e2e8f0;font-size:0.88rem;line-height:1.5;margin-bottom:10px;">
              <?=h($ent['notes']?:'Associazione locale per il coordinamento dei Club Alcologici Territoriali.')?>
            </p>
            <div style="font-size:0.85rem;color:#cbd5e1;display:flex;flex-direction:column;gap:4px;margin-bottom:12px;">
              <?php if($ent['city']): ?>
                <span>📍 <strong style="color:#FFFFFF;"><?=h($ent['city'])?></strong> <?=h($ent['province']?'('.$ent['province'].')':'')?></span>
              <?php endif; ?>
              <?php if($ent['phone']): ?>
                <span>☎ <b style="color:#FFFFFF;"><?=h($ent['phone'])?></b></span>
              <?php endif; ?>
              <?php if($ent['email']): ?>
                <span>✉ <a href="mailto:<?=h($ent['email'])?>" style="color:#38bdf8;text-decoration:none;font-weight:600;"><?=h($ent['email'])?></a></span>
              <?php endif; ?>
            </div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;border-top:1px solid rgba(255,255,255,0.1);padding-top:10px;">
            <?php if($ent['website']): ?>
              <a href="<?=h($ent['website'])?>" target="_blank" rel="noopener" class="btn small primary" style="flex:1 1 120px;text-align:center;padding:8px 12px;font-size:0.85rem;min-height:42px;">
                <?=dx_icon('external-link', '', 14)?> Portale Ufficiale ↗
              </a>
            <?php endif; ?>
            <a href="/club/<?=urlencode($ent['sic_id'])?>" class="btn small" style="flex:1 1 120px;background:rgba(255,255,255,0.06);color:#f1f5f9;border:1px solid rgba(255,255,255,0.2);padding:8px 12px;font-size:0.85rem;text-align:center;min-height:42px;display:inline-flex;align-items:center;justify-content:center;">
              Scheda Dependex
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:24px;" id="loadMoreAcatWrap">
      <button type="button" id="btnLoadMoreAcat" class="btn" style="background:rgba(0,255,119,0.12);color:#34d399;border:1px solid #34d399;font-weight:800;border-radius:12px;padding:12px 24px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-size:0.95rem;">
        <?=dx_icon('plus-circle', '', 18)?> Mostra Altri Club Territoriali (+30)
      </button>
    </div>
    <!-- STATO VUOTO NATURALE ED EMPATICO (WCAG 2.2 AA) -->
    <div id="dirEmptyState" style="display:none;padding:3rem 1.5rem;text-align:center;background:rgba(20,26,42,0.85);border:1px solid rgba(224,169,109,0.3);border-radius:18px;margin:2rem 0;">
      <div style="font-size:2.5rem;margin-bottom:1rem;">🌱</div>
      <h3 style="color:#ffffff;font-size:1.4rem;font-weight:700;margin-bottom:0.75rem;font-family:var(--font-serif);">
        Non abbiamo trovato un Club con questa ricerca.
      </h3>
      <p style="color:#cbd5e1;max-width:550px;margin:0 auto 1.5rem;font-size:1rem;line-height:1.6;">
        Non preoccuparti: puoi provare con un'altra città, provincia o regione vicina, oppure iniziare una conversazione riservata con un servitore insegnante della nostra comunità.
      </p>
      <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
        <a href="parla-con-noi.php" class="btn-community-primary" style="padding:12px 24px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
          <?=dx_icon('message-circle', '', 18)?> Parla con Noi
        </a>
        <a href="mailto:info@dependex.support" class="btn-community-wa" style="padding:12px 24px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;background:rgba(0,212,255,0.15);border:1px solid #00d4ff;color:#00d4ff;">
          <?=dx_icon('mail', '', 18)?> Scrivi a info@dependex.support
        </a>
      </div>
    </div>
  </div>

</div>

<!-- BOX ORIENTAMENTO E CONTATTO RISERVATO -->
<section class="lux-metallic-card p-4 p-md-5 my-5 text-center" style="border: 1px solid rgba(212,175,55,0.3);max-width:1100px;margin-left:auto;margin-right:auto;">
  <div class="gold-glow-badge mb-2">
    <?=dx_icon('shield', '', 14)?>
    <span>RETE GRATUITA · SUPPORTO RISERVATO</span>
  </div>
  <h3 style="margin-top:0.4rem;font-size:1.4rem;color:#FFFFFF;font-family:var(--font-serif);">
    Non riesci a raggiungere un Club fisico o preferisci un primo contatto riservato?
  </h3>
  <p style="color:#cbd5e1;max-width:680px;margin:0.5rem auto 1.5rem;font-size:0.96rem;line-height:1.6;">
    Oltre alla presenza fisica sul territorio, puoi approfondire con i manuali e i diari ufficiali Amazon KDP o dialogare in totale anonimato con un Servitore-Insegnante della rete di volontariato.
  </p>
  <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap;">
    <a class="btn primary" href="offers.php" style="padding:0 24px;">
      <?=dx_icon('book-open', '', 16)?>
      <span style="margin-left:6px;">Libri & Manuali Amazon KDP</span>
    </a>
    <a class="btn-rainbow-outline" href="parla-con-noi.php" style="padding:0 22px;">
      <?=dx_icon('message-circle', '', 16)?>
      <span style="margin-left:6px;">Richiedi Orientamento Riservato</span>
    </a>
    <a class="btn small" href="tel:800974250" 
       style="background:rgba(212,175,55,0.2);border:1px solid #ffd700;color:#ffd700;font-weight:800;border-radius:10px;padding:0 18px;display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('phone', '', 16)?> Numero Verde 800 974250
    </a>
  </div>
</section>

<!-- SCRIPT DI GESTIONE TAB & FILTRI DIRECTORY -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabMap = document.getElementById('tabBtnMap');
  const tabDir = document.getElementById('tabBtnDir');
  const secMap = document.getElementById('sectionMapStage');
  const secDir = document.getElementById('sectionDirectoryStage');

  tabMap.addEventListener('click', function() {
    tabMap.classList.add('active');
    tabMap.style.background = 'rgba(212,175,55,0.2)';
    tabMap.style.color = '#FFFFFF';
    tabDir.classList.remove('active');
    tabDir.style.background = 'transparent';
    tabDir.style.color = '#94a3b8';
    secMap.style.display = 'block';
    secDir.style.display = 'none';
  });

  tabDir.addEventListener('click', function() {
    tabDir.classList.add('active');
    tabDir.style.background = 'rgba(212,175,55,0.2)';
    tabDir.style.color = '#FFFFFF';
    tabMap.classList.remove('active');
    tabMap.style.background = 'transparent';
    tabMap.style.color = '#94a3b8';
    secMap.style.display = 'none';
    secDir.style.display = 'block';
  });

  // Filtro in tempo reale della Directory & Geolocation
  const searchInput = document.getElementById('dirSearchInput');
  const regionSelect = document.getElementById('dirRegionSelect');
  const levelSelect = document.getElementById('dirLevelSelect');
  const webCheck = document.getElementById('dirOnlyWebCheck');
  const cards = document.querySelectorAll('.dir-card');
  const countLabel = document.getElementById('dirCountLabel');
  const btnGeo = document.getElementById('btnGeolocate');
  const btnResetGeo = document.getElementById('btnResetGeo');
  const geoStatus = document.getElementById('geoStatusLabel');
  const grids = document.querySelectorAll('.dir-cards-grid');
  const emptyState = document.getElementById('dirEmptyState');
  const groupBlocks = document.querySelectorAll('.dir-group-block');

  // Memorizza l'ordine originale dei nodi
  cards.forEach((card, index) => {
    card.dataset.origOrder = index;
  });

  function calculateHaversine(lat1, lon1, lat2, lon2) {
    const R = 6371; // raggio terrestre in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  let acatLimit = 30;
  const btnLoadMore = document.getElementById('btnLoadMoreAcat');
  const wrapLoadMore = document.getElementById('loadMoreAcatWrap');
  let isGeoActive = false;

  function filterCards() {
    const q = (searchInput.value || '').toLowerCase().trim();
    const reg = (regionSelect.value || '').toLowerCase().trim();
    const lev = (levelSelect.value || '').toUpperCase().trim();
    const onlyWeb = webCheck.checked;
    const isFiltered = Boolean(q || reg || lev || onlyWeb || isGeoActive);

    let visibleCount = 0;
    let acatIndex = 0;

    cards.forEach(card => {
      const cKeywords = card.getAttribute('data-keywords') || '';
      const cRegion = (card.getAttribute('data-region') || '').toLowerCase();
      const cLevel = (card.getAttribute('data-level') || '').toUpperCase();
      const cHasWeb = card.getAttribute('data-hasweb') === '1';

      let match = true;
      if (q && !cKeywords.includes(q)) match = false;
      if (reg && !cRegion.includes(reg)) match = false;
      if (lev && cLevel !== lev) match = false;
      if (onlyWeb && !cHasWeb) match = false;

      if (match) {
        if (cLevel === 'TERRITORIAL' && !isFiltered) {
          acatIndex++;
          if (acatIndex > acatLimit) {
            card.style.display = 'none';
            return;
          }
        }
        card.style.display = 'flex';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (wrapLoadMore) {
      if (!isFiltered && acatIndex > acatLimit) {
        wrapLoadMore.style.display = 'block';
      } else {
        wrapLoadMore.style.display = 'none';
      }
    }

    // Gestione visibilità sezioni e stato vuoto empatico
    if (groupBlocks) {
      groupBlocks.forEach(group => {
        const anyVisible = Array.from(group.querySelectorAll('.dir-card')).some(c => c.style.display === 'flex');
        group.style.display = (isFiltered && !anyVisible) ? 'none' : 'block';
      });
    }

    if (emptyState) {
      emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
    }

    if (countLabel) {
      if (visibleCount === 0) {
        countLabel.textContent = 'Nessun risultato per i filtri selezionati';
      } else {
        countLabel.textContent = 'Mostrati ' + visibleCount + ' nodi su ' + cards.length + ' totali' + (!isFiltered ? ' (anteprima progressiva dei primi ' + Math.min(acatLimit, acatIndex) + ' Club territoriali)' : '');
      }
    }
  }

  if (btnLoadMore) {
    btnLoadMore.addEventListener('click', function() {
      acatLimit += 30;
      filterCards();
    });
  }

  if (btnGeo) {
    btnGeo.addEventListener('click', function() {
      if (!navigator.geolocation) {
        alert('Geolocalizzazione non supportata dal tuo browser.');
        return;
      }
      const origText = btnGeo.innerHTML;
      btnGeo.innerHTML = 'Rilevamento in corso...';
      btnGeo.disabled = true;

      navigator.geolocation.getCurrentPosition(
        function(pos) {
          const userLat = pos.coords.latitude;
          const userLng = pos.coords.longitude;

          cards.forEach(card => {
            const cLat = parseFloat(card.getAttribute('data-lat'));
            const cLng = parseFloat(card.getAttribute('data-lng'));
            let distBadge = card.querySelector('.badge-distance');
            if (distBadge) distBadge.remove();

            if (!isNaN(cLat) && !isNaN(cLng) && cLat !== 0 && cLng !== 0) {
              const d = calculateHaversine(userLat, userLng, cLat, cLng);
              card.dataset.distance = d;
              const header = card.querySelector('.dir-card-header') || card.firstElementChild;
              if (header) {
                const b = document.createElement('span');
                b.className = 'badge badge-distance';
                b.style.cssText = 'background:rgba(0,212,255,0.2);color:#00d4ff;border:1px solid #00d4ff;font-size:0.75rem;padding:2px 8px;border-radius:6px;font-weight:700;';
                b.textContent = '📍 a ' + (d < 10 ? d.toFixed(1) : Math.round(d)) + ' km';
                header.appendChild(b);
              }
            } else {
              card.dataset.distance = 999999;
            }
          });

          // Ordina le card all'interno di ciascuna griglia per distanza crescente
          grids.forEach(grid => {
            const gridCards = Array.from(grid.querySelectorAll('.dir-card'));
            gridCards.sort((a, b) => {
              const dA = parseFloat(a.dataset.distance) || 999999;
              const dB = parseFloat(b.dataset.distance) || 999999;
              return dA - dB;
            });
            gridCards.forEach(c => grid.appendChild(c));
          });

          btnGeo.innerHTML = origText;
          btnGeo.disabled = false;
          isGeoActive = true;
          if (btnResetGeo) btnResetGeo.style.display = 'inline-block';
          if (geoStatus) {
            geoStatus.style.display = 'block';
            geoStatus.textContent = '📍 Posizione rilevata: Club ordinati dal più vicino a te';
          }
          filterCards();
        },
        function(err) {
          btnGeo.innerHTML = origText;
          btnGeo.disabled = false;
          alert('Impossibile ottenere la posizione: ' + (err.message || 'autorizzazione negata.'));
        },
        { enableHighAccuracy: false, timeout: 8000 }
      );
    });
  }

  if (btnResetGeo) {
    btnResetGeo.addEventListener('click', function() {
      isGeoActive = false;
      // Ripristina ordine originale e rimuovi badge
      cards.forEach(card => {
        delete card.dataset.distance;
        const b = card.querySelector('.badge-distance');
        if (b) b.remove();
      });
      grids.forEach(grid => {
        const gridCards = Array.from(grid.querySelectorAll('.dir-card'));
        gridCards.sort((a, b) => {
          return (parseInt(a.dataset.origOrder, 10) || 0) - (parseInt(b.dataset.origOrder, 10) || 0);
        });
        gridCards.forEach(c => grid.appendChild(c));
      });
      btnResetGeo.style.display = 'none';
      if (geoStatus) geoStatus.style.display = 'none';
      filterCards();
    });
  }

  searchInput.addEventListener('input', filterCards);
  regionSelect.addEventListener('change', filterCards);
  levelSelect.addEventListener('change', filterCards);
  webCheck.addEventListener('change', filterCards);
});
</script>

<?php require '_footer.php';?>