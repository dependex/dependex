<?php
/**
 * DEPENDEX.SOCIAL — MAPPA GEOREFERENZIATA 2D CLUB CAT ITALIA
 * Censimento Ufficiale OSINT 2026 dei Club Alcologici Territoriali, ACAT, ARCAT e AICAT.
 * Mappa interattiva 2D con calcolo di prossimità GPS, filtri per regione, provincia e giorno.
 */

require_once __DIR__ . '/bootstrap.php';

$pdo = db();

$totalClubs = (int)$pdo->query("SELECT COUNT(*) FROM cat_clubs_italy")->fetchColumn();
$totalFamiliesNetwork = (int)$pdo->query("SELECT SUM(families_count) FROM cat_clubs_italy WHERE level = 'LOCAL_CLUB'")->fetchColumn();
if (!$totalFamiliesNetwork) { $totalFamiliesNetwork = 15588; }
$regions = $pdo->query("SELECT region, COUNT(*) as count FROM cat_clubs_italy WHERE region != '' GROUP BY region ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);
$levels = $pdo->query("SELECT level, COUNT(*) as count FROM cat_clubs_italy GROUP BY level ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);
$days = $pdo->query("SELECT meeting_day, COUNT(*) as count FROM cat_clubs_italy WHERE meeting_day != '' GROUP BY meeting_day ORDER BY count DESC")->fetchAll(PDO::FETCH_ASSOC);

// Pre-carica tutti i club per render ultra-reattivo
$clubsQuery = $pdo->query("
    SELECT id, sic_id, entity_name, level, region, province, city, address, cap,
           meeting_day, meeting_time, meeting_frequency, meeting_venue,
           servitore_insegnante, phone, phone_secondary, email, website,
           parent_entity, parent_sic_id, asl_serd_reference,
           latitude, longitude, geo_accuracy, status, source_url, source_type, notes, families_count
    FROM cat_clubs_italy
    ORDER BY region ASC, city ASC, entity_name ASC
");
$allClubs = $clubsQuery->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Mappa Georeferenziata Club Alcologici Territoriali d\'Italia (CAT, ACAT, ARCAT)';
$metaDesc = 'Censimento completo 2026 e mappa interattiva 2D georeferenziata dei Club CAT in Italia. Trova il club più vicino con il Metodo Hudolin, giorni di incontro, indirizzi e contatti diretti.';
$canonicalUrl = 'https://' . (site_brand()['domain'] ?? 'dependex.social') . '/mappa-club.php';

$breadcrumbs = [
    'Home' => '/',
    'Trova un Club' => 'world-club-explorer.php',
    'Mappa Georeferenziata Italia' => 'mappa-club.php'
];

require '_header.php';
?>

<!-- Foglio di stile Leaflet & MarkerCluster -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>

<style>
/* STILE DELLA MAPPA E DEI COMPONENTI GEOREFERENZIATI 2D */
:root {
  --map-cyan: #00f0ff;
  --map-gold: #ffd700;
  --map-amber: #ff7700;
  --map-green: #00ff88;
  --map-bg: #070a12;
  --panel-bg: rgba(12, 17, 29, 0.88);
  --panel-border: rgba(0, 240, 255, 0.22);
}

.map-page-hero {
  padding: 2.5rem 1.5rem 1.5rem;
  text-align: center;
  position: relative;
  background: radial-gradient(circle at 50% 0%, rgba(0, 240, 255, 0.08) 0%, rgba(7, 10, 18, 0) 70%);
}

.map-page-hero h1 {
  font-size: clamp(1.8rem, 3.4vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.02em;
  margin: 0.6rem 0;
  color: #ffffff;
}

.map-page-hero p {
  max-width: 820px;
  margin: 0 auto;
  color: #cbd5e1;
  font-size: 1.05rem;
  line-height: 1.6;
}

/* FILTRI E SEARCH BAR SUPERIORE */
.map-controls-panel {
  max-width: 1360px;
  margin: 0 auto 1.5rem;
  padding: 1.25rem;
  background: var(--panel-bg);
  border: 1px solid var(--panel-border);
  border-radius: 20px;
  backdrop-filter: blur(16px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 25px rgba(0, 240, 255, 0.06);
}

.controls-row-primary {
  display: grid;
  grid-template-columns: 1.8fr 1fr 1fr 1fr auto;
  gap: 12px;
  align-items: center;
}

@media (max-width: 1024px) {
  .controls-row-primary {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .controls-row-primary {
    grid-template-columns: 1fr;
  }
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-wrapper input {
  width: 100%;
  padding: 12px 14px 12px 42px;
  border-radius: 12px;
  background: rgba(18, 26, 43, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  font-size: 0.95rem;
  transition: all 0.25s ease;
}

.search-input-wrapper input:focus {
  outline: none;
  border-color: var(--map-cyan);
  box-shadow: 0 0 15px rgba(0, 240, 255, 0.35);
}

.search-input-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  pointer-events: none;
}

.filter-select {
  width: 100%;
  padding: 12px 14px;
  border-radius: 12px;
  background: rgba(18, 26, 43, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  font-size: 0.92rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.filter-select:focus {
  outline: none;
  border-color: var(--map-cyan);
}

.geo-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 18px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(0, 240, 255, 0.2), rgba(0, 119, 255, 0.3));
  border: 1px solid var(--map-cyan);
  color: #ffffff;
  font-weight: 600;
  font-size: 0.92rem;
  cursor: pointer;
  transition: all 0.25s ease;
  white-space: nowrap;
}

.geo-btn:hover {
  background: linear-gradient(135deg, rgba(0, 240, 255, 0.35), rgba(0, 119, 255, 0.5));
  box-shadow: 0 0 20px rgba(0, 240, 255, 0.4);
  transform: translateY(-1px);
}

.geo-btn.active {
  background: linear-gradient(135deg, #00f0ff, #0077ff);
  color: #070a12;
  font-weight: 700;
  box-shadow: 0 0 25px rgba(0, 240, 255, 0.6);
}

.btn-reset-filters {
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #94a3b8;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset-filters:hover {
  color: #ffffff;
  border-color: rgba(255, 255, 255, 0.4);
}

/* BADGES FILTRI SECONDARI (LIVELLO) */
.controls-row-secondary {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
}

.level-pills-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.level-pill {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  transition: all 0.2s ease;
}

.level-pill:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.level-pill.active {
  background: rgba(0, 240, 255, 0.18);
  border-color: var(--map-cyan);
  color: var(--map-cyan);
  box-shadow: 0 0 12px rgba(0, 240, 255, 0.25);
}

.level-pill[data-level="LOCAL_CLUB"].active {
  border-color: #00f0ff;
  color: #00f0ff;
}

.level-pill[data-level="TERRITORIAL"].active {
  border-color: #ffd700;
  color: #ffd700;
}

.level-pill[data-level="REGIONAL"].active {
  border-color: #ff7700;
  color: #ff7700;
}

.level-pill[data-level="NATIONAL"].active {
  border-color: #b829ff;
  color: #e0a9ff;
}

.stats-counter-tag {
  font-size: 0.88rem;
  color: #94a3b8;
}

.stats-counter-tag b {
  color: var(--map-cyan);
}

/* LAYOUT MAPPA 2D + LISTA LATERALE */
.map-main-layout {
  max-width: 1360px;
  margin: 0 auto 3rem;
  display: grid;
  grid-template-columns: 1fr 420px;
  gap: 20px;
  min-height: 720px;
}

@media (max-width: 1100px) {
  .map-main-layout {
    grid-template-columns: 1fr;
  }
}

.map-viewport-wrapper {
  position: relative;
  background: #080d1a;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(0, 240, 255, 0.25);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
  min-height: 550px;
}

#leafletMapContainer {
  width: 100%;
  height: 100%;
  min-height: 680px;
  background: #070a12;
  z-index: 1;
}

.btn-map-floating {
  position: absolute;
  top: 16px;
  right: 16px;
  z-index: 1000;
  background: rgba(12, 17, 29, 0.92);
  border: 1px solid rgba(0, 240, 255, 0.4);
  color: #00f0ff;
  border-radius: 12px;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.5);
  transition: all 0.2s ease;
}
.btn-map-floating:hover {
  background: rgba(0, 240, 255, 0.25);
  color: #ffffff;
  border-color: #00f0ff;
  transform: scale(1.05);
}

/* SIDEBAR DRAWER DEI CLUB */
.club-list-panel {
  background: var(--panel-bg);
  border: 1px solid var(--panel-border);
  border-radius: 20px;
  backdrop-filter: blur(16px);
  display: flex;
  flex-direction: column;
  max-height: 780px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}

.club-list-header {
  padding: 16px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(18, 24, 38, 0.7);
}

.club-list-header h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 8px;
}

.club-cards-scroll {
  padding: 14px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  scrollbar-width: thin;
  scrollbar-color: rgba(0, 240, 255, 0.3) transparent;
}

.club-cards-scroll::-webkit-scrollbar {
  width: 6px;
}
.club-cards-scroll::-webkit-scrollbar-thumb {
  background: rgba(0, 240, 255, 0.3);
  border-radius: 6px;
}

.club-card-item {
  background: rgba(18, 25, 41, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.club-card-item:hover {
  border-color: rgba(0, 240, 255, 0.5);
  background: rgba(22, 32, 54, 0.95);
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 240, 255, 0.15);
}

.club-card-item.selected {
  border-color: var(--map-cyan);
  box-shadow: 0 0 20px rgba(0, 240, 255, 0.35);
  background: rgba(26, 38, 64, 0.98);
}

.club-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 6px;
  gap: 8px;
}

.club-type-tag {
  font-size: 0.68rem;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.tag-local {
  background: rgba(0, 240, 255, 0.15);
  color: #00f0ff;
  border: 1px solid rgba(0, 240, 255, 0.3);
}

.tag-apcat {
  background: rgba(168, 85, 247, 0.15);
  color: #c084fc;
  border: 1px solid rgba(168, 85, 247, 0.35);
}

.tag-acat {
  background: rgba(255, 215, 0, 0.15);
  color: #ffd700;
  border: 1px solid rgba(255, 215, 0, 0.3);
}

.tag-arcat {
  background: rgba(255, 119, 0, 0.15);
  color: #ff7700;
  border: 1px solid rgba(255, 119, 0, 0.3);
}

.tag-aicat {
  background: linear-gradient(135deg, rgba(255, 51, 68, 0.2), rgba(184, 41, 255, 0.2));
  color: #e0a9ff;
  border: 1px solid rgba(184, 41, 255, 0.4);
}

.distance-badge {
  font-size: 0.75rem;
  font-weight: 700;
  color: #00ff88;
  background: rgba(0, 255, 136, 0.12);
  border: 1px solid rgba(0, 255, 136, 0.3);
  padding: 2px 7px;
  border-radius: 6px;
}

.club-card-name {
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px;
  line-height: 1.35;
}

.club-card-location {
  font-size: 0.85rem;
  color: #94a3b8;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.club-card-meeting {
  font-size: 0.82rem;
  color: #cbd5e1;
  background: rgba(0, 0, 0, 0.25);
  padding: 6px 9px;
  border-radius: 8px;
  margin-bottom: 10px;
  border-left: 3px solid var(--map-cyan);
}

.club-card-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.card-action-btn {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 5px 10px;
  border-radius: 7px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s ease;
}

.btn-phone {
  background: rgba(0, 255, 136, 0.15);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}
.btn-phone:hover {
  background: rgba(0, 255, 136, 0.3);
  color: #ffffff;
}

.btn-wa {
  background: rgba(37, 211, 102, 0.15);
  color: #25d366;
  border: 1px solid rgba(37, 211, 102, 0.3);
}
.btn-wa:hover {
  background: rgba(37, 211, 102, 0.3);
  color: #ffffff;
}

.btn-map-focus {
  background: rgba(0, 240, 255, 0.12);
  color: #00f0ff;
  border: 1px solid rgba(0, 240, 255, 0.25);
}
.btn-map-focus:hover {
  background: rgba(0, 240, 255, 0.25);
}

.btn-directions {
  background: rgba(255, 255, 255, 0.08);
  color: #e2e8f0;
  border: 1px solid rgba(255, 255, 255, 0.15);
}
.btn-directions:hover {
  background: rgba(255, 255, 255, 0.18);
  color: #ffffff;
}

/* CUSTOM LEAFLET MARKERS & POPUPS */
.custom-neon-marker {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #ffffff;
  font-size: 13px;
  font-weight: 800;
  box-shadow: 0 0 15px currentColor;
  transition: transform 0.2s ease;
  border: 2px solid rgba(255, 255, 255, 0.9);
}

.custom-neon-marker:hover {
  transform: scale(1.25);
}

.marker-local {
  background: radial-gradient(circle, #00f0ff 20%, #0077ff 100%);
  color: #00f0ff;
}

.marker-apcat {
  background: radial-gradient(circle, #c084fc 20%, #7e22ce 100%);
  color: #c084fc;
}

.marker-acat {
  background: radial-gradient(circle, #ffd700 20%, #ff8800 100%);
  color: #ffd700;
}

.marker-arcat {
  background: radial-gradient(circle, #ff7700 20%, #d42200 100%);
  color: #ff7700;
}

.marker-aicat {
  background: radial-gradient(circle, #ff3344 0%, #b829ff 100%);
  color: #e0a9ff;
}

.user-gps-marker {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #00f0ff;
  border: 3px solid #ffffff;
  box-shadow: 0 0 25px #00f0ff, 0 0 50px #00f0ff;
  position: relative;
}

.user-gps-marker::after {
  content: '';
  position: absolute;
  top: -12px;
  left: -12px;
  right: -12px;
  bottom: -12px;
  border-radius: 50%;
  border: 2px solid #00f0ff;
  animation: radarPulse 1.8s infinite cubic-bezier(0.2, 0.6, 0.35, 1);
}

@keyframes radarPulse {
  0% { transform: scale(0.6); opacity: 1; }
  100% { transform: scale(2.4); opacity: 0; }
}

/* LEAFLET POPUP CUSTOMIZATION */
.leaflet-popup-content-wrapper {
  background: rgba(10, 15, 26, 0.96) !important;
  color: #ffffff !important;
  border: 1px solid rgba(0, 240, 255, 0.4) !important;
  border-radius: 16px !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8), 0 0 20px rgba(0, 240, 255, 0.2) !important;
  backdrop-filter: blur(12px) !important;
  padding: 4px;
}

.leaflet-popup-tip {
  background: rgba(10, 15, 26, 0.96) !important;
  border: 1px solid rgba(0, 240, 255, 0.4) !important;
}

.popup-inner-card {
  padding: 8px 12px;
}

.popup-inner-card h4 {
  margin: 0 0 6px;
  font-size: 1.05rem;
  color: #ffffff;
  font-weight: 700;
}

.popup-inner-card p {
  margin: 4px 0;
  font-size: 0.85rem;
  color: #cbd5e1;
}

.popup-actions {
  display: flex;
  gap: 8px;
  margin-top: 10px;
  flex-wrap: wrap;
}

/* BOX STATISTICHE INFERIORI & OPEN DATA */
.map-bottom-grid {
  max-width: 1360px;
  margin: 0 auto 4rem;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

@media (max-width: 900px) {
  .map-bottom-grid {
    grid-template-columns: 1fr;
  }
}

.stats-card-box {
  background: var(--panel-bg);
  border: 1px solid var(--panel-border);
  border-radius: 20px;
  padding: 24px;
  backdrop-filter: blur(16px);
}

.kpi-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-top: 16px;
}

@media (max-width: 640px) {
  .kpi-row {
    grid-template-columns: 1fr 1fr;
  }
}

.kpi-item {
  background: rgba(18, 25, 41, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  padding: 16px;
  text-align: center;
}

.kpi-val {
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--map-cyan);
  display: block;
  margin-bottom: 4px;
}

.kpi-lbl {
  font-size: 0.78rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.opendata-card {
  background: var(--panel-bg);
  border: 1px solid rgba(224, 169, 109, 0.35);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
</style>

<main id="mainContent">

  <!-- HERO SECTION -->
  <section class="map-page-hero">
    <div class="gold-glow-badge mb-3">
      <?=dx_icon('compass', '', 14)?>
      <span>CENSIMENTO NAZIONALE OSINT 2026 · RETE HUDOLIN ITALIA</span>
    </div>
    <h1>
      Mappa Georeferenziata 2D dei Club CAT & ACAT d'Italia
    </h1>
    <p>
      Trova il Club Alcologico Territoriale o l'APCAT provinciale più vicina alla tua città. <b><?=$totalClubs?> Club ed APCAT censiti</b> con oltre <b><?=number_format($totalFamiliesNetwork, 0, ',', '.')?> famiglie</b> accolte nei cerchi (con una media di 10-12 famiglie per Club locale prima della gemmazione). Coordinate 2D verificate, recapiti telefonici e orari di incontro. Nessuna prescrizione o spesa: la sedia al Club è sempre aperta.
    </p>
  </section>

  <!-- CONTROLLI E FILTRI DINAMICI -->
  <div class="map-controls-panel">
    <div class="controls-row-primary">
      
      <!-- RICERCA TESTO -->
      <div class="search-input-wrapper">
        <span class="search-input-icon"><?=dx_icon('search', '', 18)?></span>
        <input type="text" id="filterQuery" placeholder="Cerca club, APCAT, comune, via, provincia o servitore..." autocomplete="off">
      </div>

      <!-- SELETTORE REGIONE -->
      <select id="filterRegion" class="filter-select">
        <option value="ALL">Tutte le Regioni (<?=$totalClubs?>)</option>
        <?php foreach ($regions as $r): ?>
          <option value="<?=h($r['region'])?>"><?=h($r['region'])?> (<?=$r['count']?>)</option>
        <?php endforeach; ?>
      </select>

      <!-- SELETTORE GIORNO DI INCONTRO -->
      <select id="filterDay" class="filter-select">
        <option value="ALL">Tutti i Giorni di Incontro</option>
        <option value="Lunedì">Lunedì</option>
        <option value="Martedì">Martedì</option>
        <option value="Mercoledì">Mercoledì</option>
        <option value="Giovedì">Giovedì</option>
        <option value="Venerdì">Venerdì</option>
        <option value="Sabato">Sabato</option>
        <option value="Domenica">Domenica</option>
      </select>

      <!-- BOTTONE GEOLOCALIZZAZIONE GPS -->
      <button type="button" id="btnGeolocate" class="btn-gps-proximity" title="Trova i Club più vicini alla tua posizione attuale">
        <?=dx_icon('navigation', '', 16)?>
        <span>Vicino a Me (GPS)</span>
      </button>

      <!-- RESET FILTRI -->
      <button type="button" id="btnResetFilters" class="btn-reset-filters" title="Reimposta visualizzazione iniziale">
        <?=dx_icon('refresh-cw', '', 16)?>
      </button>

    </div>

    <!-- RIGA INFORMATIVA FILTRI APPLICATI E LIVELLI -->
    <div class="controls-row-secondary">
      <div class="active-filters-summary">
        Visualizzati: <b id="displayedCount"><?=$totalClubs?></b> di <b><?=$totalClubs?> nodi</b> (<span id="userLocationStatus">Nessun punto GPS fissato</span>)
      </div>

      <!-- PILLS FILTRO LIVELLO -->
      <div class="level-pills-group" id="levelPills">
        <button type="button" class="level-pill active" data-level="ALL">Tutti</button>
        <button type="button" class="level-pill" data-level="LOCAL_CLUB">Club CAT</button>
        <button type="button" class="level-pill" data-level="PROVINCIAL_APCAT">APCAT</button>
        <button type="button" class="level-pill" data-level="TERRITORIAL">ACAT</button>
        <button type="button" class="level-pill" data-level="REGIONAL">ARCAT</button>
        <button type="button" class="level-pill" data-level="NATIONAL">AICAT</button>
      </div>

      <div class="legend-quick-tags">
        <span class="legend-item"><span class="legend-dot dot-local"></span> Club CAT</span>
        <span class="legend-item"><span class="legend-dot dot-apcat"></span> APCAT Provinciale</span>
        <span class="legend-item"><span class="legend-dot dot-acat"></span> Associazione ACAT</span>
        <span class="legend-item"><span class="legend-dot dot-arcat"></span> ARCAT Regionale</span>
        <span class="legend-item"><span class="legend-dot dot-aicat"></span> AICAT Nazionale</span>
      </div>
    </div>
  </div>

  <!-- CONTAINER CENTRALE: MAPPA 2D + SIDEBAR CARDS -->
  <div class="map-main-layout">
    
    <!-- MAPPA INTERATTIVA LEAFLET 2D -->
    <div class="map-viewport-wrapper">
      <div id="leafletMapContainer" role="region" aria-label="Mappa dei Club CAT in Italia"></div>
      
      <!-- Pulsante galleggiante per centrare su Italia -->
      <button type="button" id="btnResetView" class="btn-map-floating" title="Inquadra tutta Italia">
        <?=dx_icon('compass', '', 18)?>
      </button>
    </div>

    <!-- DRAWER LATERALE: LISTA CLUB E DETTAGLI -->
    <div class="club-list-panel" id="sidebarDrawer">
      <div class="club-list-header">
        <h3><?=dx_icon('users', 'text-cyan', 18)?> <span>Elenco Territoriale</span></h3>
        <span class="drawer-badge-total" id="drawerBadgeTotal"><?=$totalClubs?> Nodi</span>
      </div>
      <p style="padding:10px 14px 0;margin:0;font-size:0.80rem;color:#94a3b8;">Tocca un Club per centrarlo sulla mappa o avviare la chiamata diretta.</p>

      <div class="club-cards-scroll" id="clubCardsContainer">
        <!-- Generato dinamicamente via JS -->
      </div>
    </div>

  </div>

  <!-- SEZIONE STATISTICHE CENSIMENTO E OPEN DATA -->
  <div class="map-bottom-grid">
    
    <div class="stats-card-box">
      <div class="gold-glow-badge mb-2">
        <?=dx_icon('shield', '', 12)?> CENSIMENTO UFFICIALE & VERIFICA CONTINUA
      </div>
      <h3 style="font-size:1.3rem;font-weight:700;margin:0 0 10px;color:#ffffff;">
        Infrastruttura Georeferenziata dell'Alcologia Territoriale
      </h3>
      <p style="font-size:0.92rem;color:#94a3b8;line-height:1.6;margin:0;">
        Ogni Club Alcologico Territoriale opera secondo il <b>Metodo Hudolin</b> di auto-mutuo-aiuto multifamiliare: una comunità solidale di persone e famiglie che si riuniscono una volta a settimana per condividere il cammino di sobrietà e crescita personale.
      </p>

      <div class="kpi-row">
        <div class="kpi-item">
          <span class="kpi-val"><?=$totalClubs?></span>
          <span class="kpi-lbl">Club & APCAT</span>
        </div>
        <div class="kpi-item">
          <span class="kpi-val"><?=number_format($totalFamiliesNetwork, 0, ',', '.')?>+</span>
          <span class="kpi-lbl">Famiglie nei Cerchi</span>
        </div>
        <div class="kpi-item">
          <span class="kpi-val"><?=count($regions)?> / 20</span>
          <span class="kpi-lbl">Regioni d'Italia</span>
        </div>
        <div class="kpi-item">
          <span class="kpi-val">0&euro;</span>
          <span class="kpi-lbl">Gratuito & Solidale</span>
        </div>
      </div>
    </div>

    <div class="opendata-card">
      <div>
        <div class="badge-human mb-2" style="background:rgba(224,169,109,0.15);color:#ffd700;border-color:rgba(224,169,109,0.3);">
          <?=dx_icon('database', '', 12)?> OPEN DATA & CENSIMENTO
        </div>
        <h4 style="font-size:1.15rem;font-weight:700;color:#ffffff;margin:0 0 8px;">
          Database Scaricabile
        </h4>
        <p style="font-size:0.85rem;color:#cbd5e1;line-height:1.5;margin:0 0 16px;">
          I dati del censimento 2026 sono disponibili in formato CSV aperto e tramite endpoint JSON per ricercatori, istituzioni ed operatori sanitari.
        </p>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <a href="data/CENSIMENTO_CLUB_CAT_ITALIA_2026.csv" class="btn small primary" download style="text-align:center;">
          <?=dx_icon('download', '', 14)?> Scarica CSV Completo (2026)
        </a>
        <a href="api-opendata-geojson.php" target="_blank" class="btn small" style="text-align:center;border:1px solid rgba(0,240,255,0.3);color:#00f0ff;background:rgba(0,240,255,0.08);">
          <?=dx_icon('map-pin', '', 14)?> Standard GeoJSON (RFC 7946 per Ser.D/GIS)
        </a>
        <a href="api-feed-territorio.php" target="_blank" class="btn small" style="text-align:center;border:1px solid rgba(251,191,36,0.35);color:#fbbf24;background:rgba(251,191,36,0.08);">
          <?=dx_icon('rss', '', 14)?> Feed Territoriale Atom/GeoRSS (Ser.D & ASL)
        </a>
        <button type="button" onclick="dxShowEmbedModal()" class="btn small" style="text-align:center;border:1px solid rgba(168,85,247,0.45);color:#c084fc;background:rgba(168,85,247,0.12);cursor:pointer;">
          <?=dx_icon('code', '', 14)?> Incorpora Widget nei Siti di Comuni/ASL
        </button>
        <a href="api-clubs-italy.php?action=list" target="_blank" class="btn small" style="text-align:center;border:1px solid rgba(255,255,255,0.15);color:#ffffff;">
          <?=dx_icon('code', '', 14)?> Esplora API JSON
        </a>
      </div>
    </div>

  </div>

  <!-- MODALE CONDIVISIONE / EMBED WIDGET GRATUITO -->
  <div id="dxEmbedModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:16px;">
    <div style="background:#0f172a; border:1px solid rgba(168,85,247,0.4); border-radius:18px; max-width:600px; width:100%; padding:24px; color:#ffffff; box-shadow:0 10px 40px rgba(0,0,0,0.6);">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <span style="font-size:0.78rem; font-weight:800; color:#c084fc; text-transform:uppercase; letter-spacing:0.08em;">
          Bene Comune Digitale Gratuito
        </span>
        <button type="button" onclick="document.getElementById('dxEmbedModal').style.display='none'" style="background:none; border:none; color:#94a3b8; font-size:1.4rem; cursor:pointer;">&times;</button>
      </div>
      <h3 style="font-size:1.25rem; font-weight:800; margin-bottom:8px;">
        Incorpora il "Trova Club" sul tuo Sito
      </h3>
      <p style="font-size:0.85rem; color:#cbd5e1; line-height:1.5; margin-bottom:16px;">
        Comuni, consulte del volontariato, Ser.D, ASL e parrocchie possono integrare gratuitamente il motore di ricerca dei Club con 1 riga di codice:
      </p>
      <textarea id="dxEmbedCodeSnippet" readonly style="width:100%; height:90px; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.15); border-radius:8px; padding:10px; color:#00f0ff; font-family:monospace; font-size:0.8rem; resize:none; margin-bottom:12px;"><iframe src="https://dependex.social/widget-club.php" width="100%" height="440" style="border:none; border-radius:14px; max-width:650px; width:100%;" title="Trova Club Alcologico Territoriale CAT"></iframe></textarea>
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <a href="widget-club.php" target="_blank" style="color:#67e8f9; font-size:0.82rem; text-decoration:none;">Anteprima Widget Iframe ↗</a>
        <button type="button" onclick="dxCopyEmbedSnippet()" id="dxCopyEmbedBtn" style="background:linear-gradient(135deg, #a855f7, #6366f1); color:#ffffff; border:none; border-radius:8px; padding:8px 18px; font-weight:750; font-size:0.85rem; cursor:pointer;">
          Copia Codice HTML
        </button>
      </div>
    </div>
  </div>
  <script>
  function dxShowEmbedModal() {
    const m = document.getElementById('dxEmbedModal');
    if (m) m.style.display = 'flex';
  }
  function dxCopyEmbedSnippet() {
    const ta = document.getElementById('dxEmbedCodeSnippet');
    if (ta) {
      ta.select();
      navigator.clipboard.writeText(ta.value).then(() => {
        const btn = document.getElementById('dxCopyEmbedBtn');
        if (btn) btn.textContent = 'Copiato negli Appunti! ✓';
        setTimeout(() => { if (btn) btn.textContent = 'Copia Codice HTML'; }, 2500);
      });
    }
  }
  </script>

  <!-- DATI STRUTTURATI SCHEMA.ORG PER INDICIZZAZIONE TERRITORIALE (ITEMLIST / NGO) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Rete Nazionale Presidi Club Hudolin (CAT, ACAT, APCAT, ARCAT)",
    "description": "Censimento georeferenziato dei Club territoriali di auto-mutuo-aiuto per problemi alcolcorrelati e benessere multifamiliare in Italia",
    "numberOfItems": <?=count($allClubs)?>,
    "itemListElement": [
      <?php
      $ldItems = [];
      $pos = 1;
      foreach (array_slice($allClubs, 0, 50) as $c) {
          $ldItems[] = json_encode([
              "@type" => "ListItem",
              "position" => $pos++,
              "item" => [
                  "@type" => "NGO",
                  "name" => $c['entity_name'],
                  "address" => [
                      "@type" => "PostalAddress",
                      "streetAddress" => $c['address'],
                      "addressLocality" => $c['city'],
                      "addressRegion" => $c['region'],
                      "postalCode" => $c['cap'],
                      "addressCountry" => "IT"
                  ],
                  "geo" => [
                      "@type" => "GeoCoordinates",
                      "latitude" => (float)$c['latitude'],
                      "longitude" => (float)$c['longitude']
                  ],
                  "telephone" => !empty($c['phone']) ? $c['phone'] : null,
                  "url" => "https://dependex.social/world-club-explorer.php?id=" . $c['id']
              ]
          ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
      }
      echo implode(",\n      ", $ldItems);
      ?>
    ]
  }
  </script>

</main>

<!-- SCRIPT LEAFLET & LOGICA CLIENT-SIDE -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
// DATI PRECARICATI DAL DATABASE SQLITE (322 CLUB)
const ALL_CLUBS = <?=json_encode($allClubs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)?>;

let mapInstance = null;
let markerClusterGroup = null;
let markerLookup = new Map(); // id -> L.marker
let userGpsMarker = null;
let currentUserCoords = null; // [lat, lon]
let activeLevel = 'ALL';

// Inizializzazione Mappa Leaflet
function initMap() {
  const mapElem = document.getElementById('leafletMapContainer') || document.getElementById('catMap');
  if (!mapElem) {
    console.error('Container mappa non trovato!');
    return;
  }

  // Centro geometrico d'Italia [42.5, 12.5], zoom iniziale 6
  mapInstance = L.map(mapElem, {
    center: [42.5, 12.5],
    zoom: 6,
    minZoom: 5,
    maxZoom: 18,
    zoomControl: true,
    scrollWheelZoom: true
  });

  // Layer Dark Matter da CartoDB (Moderno, ad alto contrasto neon)
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://carto.com/">CARTO</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    subdomains: 'abcd',
    maxZoom: 19
  }).addTo(mapInstance);

  markerClusterGroup = L.markerClusterGroup({
    showCoverageOnHover: false,
    maxClusterRadius: 40,
    spiderfyOnMaxZoom: true,
    iconCreateFunction: function(cluster) {
      const count = cluster.getChildCount();
      return L.divIcon({
        html: `<div class="custom-neon-marker marker-local" style="width:36px;height:36px;font-size:12px;">${count}</div>`,
        className: 'custom-cluster-icon',
        iconSize: [36, 36]
      });
    }
  });

  mapInstance.addLayer(markerClusterGroup);

  // Render iniziale di tutti i club
  applyFilters();

  // Forza ricalcolo dimensioni viewport Leaflet
  setTimeout(() => {
    if (mapInstance) {
      mapInstance.invalidateSize();
    }
  }, 250);
}

// Creazione Icona Marker personalizzata in base al livello
function createMarkerIcon(level, entityName) {
  let markerClass = 'marker-local';
  let label = 'CAT';

  if (level === 'PROVINCIAL_APCAT' || (entityName && entityName.includes('APCAT'))) {
    markerClass = 'marker-apcat';
    label = 'APCAT';
  } else if (level === 'TERRITORIAL' || level === 'TERRITORIAL_ASSOCIATION' || level === 'PROVINCIAL') {
    markerClass = 'marker-acat';
    label = 'ACAT';
  } else if (level === 'REGIONAL') {
    markerClass = 'marker-arcat';
    label = 'REG';
  } else if (level === 'NATIONAL') {
    markerClass = 'marker-aicat';
    label = 'IT';
  }

  return L.divIcon({
    html: `<div class="custom-neon-marker ${markerClass}">${label}</div>`,
    className: 'custom-div-icon',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -18]
  });
}

// Calcolo distanza Haversine (km)
function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
    Math.sin(dLon/2) * Math.sin(dLon/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  return Math.round(R * c * 10) / 10;
}

// Filtro e Rendering dinamico
function applyFilters() {
  const query = document.getElementById('filterQuery').value.trim().toLowerCase();
  const region = document.getElementById('filterRegion').value;
  const day = document.getElementById('filterDay').value;

  markerClusterGroup.clearLayers();
  markerLookup.clear();

  let filtered = ALL_CLUBS.filter(club => {
    // Filtro livello
    if (activeLevel !== 'ALL') {
      if (activeLevel === 'LOCAL_CLUB' && club.level !== 'LOCAL_CLUB') return false;
      if (activeLevel === 'PROVINCIAL_APCAT' && club.level !== 'PROVINCIAL_APCAT' && !club.entity_name.includes('APCAT')) return false;
      if (activeLevel === 'TERRITORIAL' && !['TERRITORIAL','TERRITORIAL_ASSOCIATION'].includes(club.level)) return false;
      if (activeLevel === 'REGIONAL' && club.level !== 'REGIONAL') return false;
      if (activeLevel === 'NATIONAL' && club.level !== 'NATIONAL') return false;
    }

    // Filtro regione
    if (region !== 'ALL' && club.region.toLowerCase() !== region.toLowerCase()) {
      return false;
    }

    // Filtro giorno
    if (day !== 'ALL') {
      if (!club.meeting_day || !club.meeting_day.toLowerCase().includes(day.toLowerCase())) {
        return false;
      }
    }

    // Filtro ricerca testuale
    if (query !== '') {
      const haystack = [
        club.entity_name,
        club.city,
        club.province,
        club.address,
        club.servitore_insegnante,
        club.notes
      ].join(' ').toLowerCase();
      if (!haystack.includes(query)) return false;
    }

    return true;
  });

  // Se è attivo il GPS, calcola le distanze e ordina per vicinanza
  if (currentUserCoords) {
    filtered.forEach(c => {
      c._distance = calculateDistance(
        currentUserCoords[0], currentUserCoords[1],
        parseFloat(c.latitude), parseFloat(c.longitude)
      );
    });
    filtered.sort((a, b) => a._distance - b._distance);
  }

  // Aggiorna contatori UI
  document.getElementById('displayedCount').textContent = filtered.length;
  document.getElementById('drawerBadgeTotal').textContent = `${filtered.length} Nodi`;

  // Renderizzatore Markers su Mappa
  filtered.forEach(club => {
    const lat = parseFloat(club.latitude);
    const lon = parseFloat(club.longitude);
    if (isNaN(lat) || isNaN(lon)) return;

    const marker = L.marker([lat, lon], {
      icon: createMarkerIcon(club.level, club.entity_name),
      title: club.entity_name
    });

    // Costruzione Popup interattivo
    let levelLabel = 'Club CAT Territoriale';
    let popupTagClass = 'tag-local';
    if (club.level === 'PROVINCIAL_APCAT' || (club.entity_name && club.entity_name.includes('APCAT'))) {
      levelLabel = 'APCAT Provinciale';
      popupTagClass = 'tag-apcat';
    } else if (['TERRITORIAL','TERRITORIAL_ASSOCIATION','PROVINCIAL'].includes(club.level)) {
      levelLabel = 'Associazione ACAT';
      popupTagClass = 'tag-acat';
    } else if (club.level === 'REGIONAL') {
      levelLabel = 'ARCAT Regionale';
      popupTagClass = 'tag-arcat';
    } else if (club.level === 'NATIONAL') {
      levelLabel = 'AICAT Nazionale';
      popupTagClass = 'tag-aicat';
    }
    
    let popupHtml = `
      <div class="popup-inner-card">
        <span class="club-type-tag ${popupTagClass}" style="margin-bottom:4px;display:inline-block;">${levelLabel}</span>
        <h4>${escapeHtml(club.entity_name)}</h4>
        <p><b>Sede:</b> ${escapeHtml(club.address || club.city)} (${escapeHtml(club.province || '')})</p>
        <p style="color:#00f0ff; margin:3px 0 5px;"><b>Comunità:</b> ${club.families_count || 11} Famiglie ${(club.level==='PROVINCIAL_APCAT'||(club.entity_name&&club.entity_name.includes('APCAT')))?'nella Rete':'nel Cerchio'}</p>
        ${club.meeting_day ? `<p><b>Incontro:</b> ${escapeHtml(club.meeting_day)} ${escapeHtml(club.meeting_time || '')}</p>` : ''}
        ${club.servitore_insegnante ? `<p><b>Referente:</b> ${escapeHtml(club.servitore_insegnante)}</p>` : ''}
        ${club._distance !== undefined ? `<p style="color:#00ff88;"><b>Distanza:</b> ${club._distance} km da te</p>` : ''}
        
        <div class="popup-actions">
          ${club.phone ? `<a href="tel:${club.phone.replace(/[^0-9+]/g, '')}" class="card-action-btn btn-phone">Chiama</a>` : ''}
          <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lon}" target="_blank" class="card-action-btn btn-directions">Indicazioni</a>
          <button type="button" class="card-action-btn btn-share" 
                  data-club="${escapeHtml(club.entity_name)}" 
                  data-city="${escapeHtml(club.city)}" 
                  data-day="${escapeHtml(club.meeting_day || '')}" 
                  data-time="${escapeHtml(club.meeting_time || '')}" 
                  data-addr="${escapeHtml(club.address || '')}"
                  onclick="window.handleShareClubBtn ? window.handleShareClubBtn(this) : (window.shareClubWithFamily && window.shareClubWithFamily(this.dataset.club, this.dataset.city, this.dataset.day, this.dataset.time, this.dataset.addr))"
                  style="background:rgba(255,215,0,0.15); color:#ffd700; border:1px solid rgba(255,215,0,0.35); cursor:pointer;"
                  title="Condividi con un familiare">
            Ti accompagno io
          </button>
        </div>
      </div>
    `;

    marker.bindPopup(popupHtml);
    marker.on('click', () => {
      highlightClubInDrawer(club.id);
    });

    markerLookup.set(club.id, marker);
    markerClusterGroup.addLayer(marker);
  });

  // Render lista card nella sidebar drawer
  renderClubCards(filtered);
}

// Renderizzazione delle Card nella sidebar
function renderClubCards(clubs) {
  const container = document.getElementById('clubCardsContainer');
  container.innerHTML = '';

  if (clubs.length === 0) {
    container.innerHTML = `
      <div style="text-align:center;padding:2rem 1rem;color:#94a3b8;">
        <p style="font-size:1.1rem;margin-bottom:8px;">Nessun club trovato con i filtri attuali.</p>
        <button type="button" class="btn small" onclick="document.getElementById('btnResetFilters').click()" style="border:1px solid rgba(255,255,255,0.2);color:#ffffff;">Ripristina ricerca</button>
      </div>
    `;
    return;
  }

  clubs.forEach(c => {
    const lat = parseFloat(c.latitude);
    const lon = parseFloat(c.longitude);
    const card = document.createElement('div');
    card.className = 'club-card-item';
    card.id = `clubCard_${c.id}`;

    let tagClass = 'tag-local';
    let tagText = 'Club CAT';
    if (c.level === 'PROVINCIAL_APCAT' || (c.entity_name && c.entity_name.includes('APCAT'))) {
      tagClass = 'tag-apcat';
      tagText = 'APCAT';
    } else if (['TERRITORIAL','TERRITORIAL_ASSOCIATION','PROVINCIAL'].includes(c.level)) {
      tagClass = 'tag-acat';
      tagText = 'ACAT';
    } else if (c.level === 'REGIONAL') {
      tagClass = 'tag-arcat';
      tagText = 'ARCAT';
    } else if (c.level === 'NATIONAL') {
      tagClass = 'tag-aicat';
      tagText = 'AICAT';
    }

    const cleanPhone = (c.phone || '').replace(/[^0-9+]/g, '');

    card.innerHTML = `
      <div class="club-card-top">
        <span class="club-type-tag ${tagClass}">${tagText}</span>
        <span class="badge-families" style="font-size:0.75rem; font-weight:750; color:#38bdf8; background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); padding:2px 7px; border-radius:6px;">${c.families_count || 11} Famiglie</span>
        ${c._distance !== undefined ? `<span class="distance-badge">${c._distance} km</span>` : `<span style="font-size:0.75rem;color:#64748b;">${escapeHtml(c.province || '')}</span>`}
      </div>
      <div class="club-card-name">${escapeHtml(c.entity_name)}</div>
      <div class="club-card-location">
        <span>${escapeHtml(c.city)}${c.address ? ' · ' + escapeHtml(c.address) : ''} (${escapeHtml(c.region)})</span>
      </div>
      ${c.meeting_day ? `
        <div class="club-card-meeting">
          <b>${escapeHtml(c.meeting_day)}</b> ${c.meeting_time ? 'ore ' + escapeHtml(c.meeting_time) : ''}
          ${c.servitore_insegnante ? `<br><small style="color:#94a3b8;">Servitore: ${escapeHtml(c.servitore_insegnante)}</small>` : ''}
        </div>
      ` : ''}
      <div class="club-card-actions">
        <button type="button" class="card-action-btn btn-map-focus" onclick="focusOnClub(${c.id}, ${lat}, ${lon})">
          Mappa
        </button>
        ${cleanPhone ? `
          <a href="tel:${cleanPhone}" class="card-action-btn btn-phone">
            Chiama
          </a>
          <a href="https://wa.me/${cleanPhone.replace('+', '')}?text=Salve,%20ho%20trovato%20il%20vostro%20Club%20su%20Dependex%20e%20vorrei%20informazioni" target="_blank" class="card-action-btn btn-wa">
            WhatsApp
          </a>
        ` : ''}
        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lon}" target="_blank" class="card-action-btn btn-directions">
          Itinerario
        </a>
        <button type="button" class="card-action-btn btn-share" 
                data-club="${escapeHtml(c.entity_name)}" 
                data-city="${escapeHtml(c.city)}" 
                data-day="${escapeHtml(c.meeting_day || '')}" 
                data-time="${escapeHtml(c.meeting_time || '')}" 
                data-addr="${escapeHtml(c.address || '')}"
                onclick="window.handleShareClubBtn ? window.handleShareClubBtn(this) : (window.shareClubWithFamily && window.shareClubWithFamily(this.dataset.club, this.dataset.city, this.dataset.day, this.dataset.time, this.dataset.addr))"
                style="background:rgba(255,215,0,0.15); color:#ffd700; border:1px solid rgba(255,215,0,0.35); cursor:pointer;"
                title="Condividi con un familiare">
          Ti accompagno io
        </button>
      </div>
    `;

    container.appendChild(card);
  });
}

// Zoom e Focus su un club specifico
function focusOnClub(id, lat, lon) {
  if (mapInstance) {
    mapInstance.setView([lat, lon], 14, { animate: true, duration: 0.8 });
    const marker = markerLookup.get(id);
    if (marker) {
      setTimeout(() => {
        markerClusterGroup.zoomToShowLayer(marker, () => {
          marker.openPopup();
        });
      }, 300);
    }
  }
  highlightClubInDrawer(id);
}

// Evidenzia la card nella lista laterale
function highlightClubInDrawer(id) {
  document.querySelectorAll('.club-card-item').forEach(el => el.classList.remove('selected'));
  const card = document.getElementById(`clubCard_${id}`);
  if (card) {
    card.classList.add('selected');
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}

// Bottone galleggiante reset inquadratura Italia
const btnResetView = document.getElementById('btnResetView');
if (btnResetView) {
  btnResetView.addEventListener('click', () => {
    if (mapInstance) {
      mapInstance.setView([42.5, 12.5], 6);
    }
  });
}

// Geolocalizzazione Utente
const geoBtn = document.getElementById('btnGeolocate') || document.getElementById('btnGpsProximity');
if (geoBtn) {
  geoBtn.addEventListener('click', function() {
    const btn = this;
    if (!navigator.geolocation) {
      alert('La geolocalizzazione non è supportata dal tuo browser.');
      return;
    }

    btn.classList.add('active');
    btn.innerHTML = `<span>Localizzazione in corso...</span>`;

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;
        currentUserCoords = [lat, lon];

        // Aggiunge / sposta marker GPS utente
        if (userGpsMarker) {
          userGpsMarker.setLatLng([lat, lon]);
        } else {
          const userIcon = L.divIcon({
            html: `<div class="user-gps-marker"></div>`,
            className: 'custom-gps-icon',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
          });
          userGpsMarker = L.marker([lat, lon], { icon: userIcon, zIndexOffset: 1000 }).addTo(mapInstance);
          userGpsMarker.bindPopup('<b>La tua posizione attuale</b>').openPopup();
        }

        // Centra mappa sulla posizione utente
        mapInstance.setView([lat, lon], 10, { animate: true });

        btn.innerHTML = `<span>Posizione Rilevata</span>`;

        // Ri-applica filtri con ordinamento di prossimità
        applyFilters();
      },
      (err) => {
        btn.classList.remove('active');
        btn.innerHTML = `<span>Vicino a Me (GPS)</span>`;
        alert('Impossibile ottenere la posizione GPS: ' + err.message);
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
  });
}

// Event Listeners per filtri
const inputQuery = document.getElementById('filterQuery');
if (inputQuery) inputQuery.addEventListener('input', debounce(applyFilters, 250));

const selRegion = document.getElementById('filterRegion');
if (selRegion) selRegion.addEventListener('change', applyFilters);

const selDay = document.getElementById('filterDay');
if (selDay) selDay.addEventListener('change', applyFilters);

// Click sui pills di livello
const pillsContainer = document.getElementById('levelPills');
if (pillsContainer) {
  pillsContainer.addEventListener('click', (e) => {
    const btn = e.target.closest('.level-pill');
    if (btn) {
      document.querySelectorAll('.level-pill').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      activeLevel = btn.getAttribute('data-level');
      applyFilters();
    }
  });
}

// Reset filtri
const resetBtn = document.getElementById('btnResetFilters');
if (resetBtn) {
  resetBtn.addEventListener('click', () => {
    if (inputQuery) inputQuery.value = '';
    if (selRegion) selRegion.value = 'ALL';
    if (selDay) selDay.value = 'ALL';
    document.querySelectorAll('.level-pill').forEach(p => p.classList.remove('active'));
    const defaultPill = document.querySelector('.level-pill[data-level="ALL"]');
    if (defaultPill) defaultPill.classList.add('active');
    activeLevel = 'ALL';
    currentUserCoords = null;
    if (geoBtn) {
      geoBtn.classList.remove('active');
      geoBtn.innerHTML = `<span>Vicino a Me (GPS)</span>`;
    }
    if (userGpsMarker && mapInstance) {
      mapInstance.removeLayer(userGpsMarker);
      userGpsMarker = null;
    }
    if (mapInstance) {
      mapInstance.setView([42.5, 12.5], 6);
    }
    applyFilters();
  });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Avvio al caricamento del DOM
document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php require '_footer.php'; ?>
