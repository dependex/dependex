<?php 
declare(strict_types=1);
require_once 'bootstrap.php';
$u = current_user();

// Sincronizza ed estrae tutti gli eventi attivi (Nazionale, Regionale, Provinciale e di Club)
$events = EventSyncService::syncAndGetActiveEvents();

$eventTaglio = null;
$eventPortoTolle = null;
foreach ($events as $evt) {
    if ($evt['sic_id'] === 'SIC-EVT-ACAT-BP-2026-COMM' && !$eventTaglio) {
        $eventTaglio = $evt;
    } elseif ($evt['sic_id'] === 'SIC-EVT-ACAT-BP-2026-SAT2' && !$eventPortoTolle) {
        $eventPortoTolle = $evt;
    }
}

if (!$eventTaglio) {
    $stT = db()->prepare('SELECT * FROM events WHERE sic_id = "SIC-EVT-ACAT-BP-2026-COMM" LIMIT 1');
    $stT->execute();
    $eventTaglio = $stT->fetch(PDO::FETCH_ASSOC);
}
if (!$eventPortoTolle) {
    $stP = db()->prepare('SELECT * FROM events WHERE sic_id = "SIC-EVT-ACAT-BP-2026-SAT2" LIMIT 1');
    $stP->execute();
    $eventPortoTolle = $stP->fetch(PDO::FETCH_ASSOC);
}

// Calcolo presenze Taglio di Po e Porto Tolle
$sicTaglio = $eventTaglio['sic_id'] ?? 'SIC-EVT-ACAT-BP-2026-COMM';
$sicPT = $eventPortoTolle['sic_id'] ?? 'SIC-EVT-ACAT-BP-2026-SAT2';
$totalBookedTaglio = 0;
$totalBookedPT = 0;

try {
    ensure_core_schema(db());
    $countStmt = db()->prepare("
        SELECT (
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = ? AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
            (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = ? AND eb.status = 'CONFIRMED')
        ) as total_booked
    ");
    $countStmt->execute([$sicTaglio, $sicTaglio]);
    $totalBookedTaglio = (int)$countStmt->fetchColumn();

    $countStmt->execute([$sicPT, $sicPT]);
    $totalBookedPT = (int)$countStmt->fetchColumn();
} catch (Throwable $e) {
    $totalBookedTaglio = 0;
    $totalBookedPT = 0;
}

$capacityTaglio = (int)($eventTaglio['capacity'] ?? 30);
$seatsRemainingTaglio = max(0, $capacityTaglio - $totalBookedTaglio);
$isFullTaglio = ($seatsRemainingTaglio <= 0);
$percentBookedTaglio = $capacityTaglio > 0 ? min(100, round(($totalBookedTaglio / $capacityTaglio) * 100)) : 0;

$capacityPT = (int)($eventPortoTolle['capacity'] ?? 40);
$seatsRemainingPT = max(0, $capacityPT - $totalBookedPT);
$isFullPT = ($seatsRemainingPT <= 0);
$percentBookedPT = $capacityPT > 0 ? min(100, round(($totalBookedPT / $capacityPT) * 100)) : 0;

// Lista eventi filtrabile (esclusi i 2 eventi faro posti in evidenza in cima)
$allOtherEvents = array_values(array_filter($events, function($ev) {
    return !in_array($ev['sic_id'], ['SIC-EVT-ACAT-BP-2026-COMM', 'SIC-EVT-ACAT-BP-2026-SAT2'], true);
}));

// Ticker news e cards per tutti i livelli
$newsCards = AcatNewsService::getLatestCards(44);

// Regioni e province disponibili
$regionsList = EventSyncService::getActiveRegions();
$provincesList = array_values(array_unique(array_filter(array_column($events, 'province'))));
sort($provincesList);

// Conteggi per livello
$countTotal = count($events);
$countNat = count(array_filter($events, fn($e) => ($e['level'] ?? 'NATIONAL') === 'NATIONAL'));
$countReg = count(array_filter($events, fn($e) => ($e['level'] ?? '') === 'REGIONAL'));
$countProv = count(array_filter($events, fn($e) => ($e['level'] ?? '') === 'PROVINCIAL'));
$countClub = count(array_filter($events, fn($e) => ($e['level'] ?? '') === 'CLUB'));

// Campione iniziale di Club (1.761 totali disponibili)
$initialClubs = EventSyncService::searchClubsDirectory('Veneto', null, null, 12);
if (empty($initialClubs)) {
    $initialClubs = EventSyncService::searchClubsDirectory(null, null, null, 12);
}

$pageTitle = 'Eventi Rete Italia: Nazionale, Regioni, Province & Club · DEPENDEX';
$metaDesc = 'Calendario completo degli eventi e incontri sulle dipendenze in tutta Italia: Taglio di Po, Porto Tolle, ARCAT regionali, APCAT provinciali e i 1.761 Club territoriali.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/events-public.php';
$breadcrumbs = [
    'Home' => '/',
    'Eventi' => 'events-public.php'
];
$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "CollectionPage",
    "name" => $pageTitle,
    "description" => $metaDesc,
    "url" => $canonicalUrl
];
require '_header.php';
?>

<!-- MOBILE-FIRST 9:16 CONTAINER (Zero sbordature, responsive smartphone shell) -->
<div class="mobile-916-shell">

  <!-- TOP BRAND & PATRONAGE BADGE -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
    <span class="m-badge m-badge-gold">
      <?=dx_icon('activity', '', 12)?> ACAT BASSO POLESINE · RETE ITALIA
    </span>
    <span class="m-badge m-badge-cyan">
      <?=dx_icon('globe', '', 12)?> <?=$countTotal?> Iniziative · 1.761 Club Attivi
    </span>
  </div>

  <!-- ============================================================== -->
  <!-- TICKER SCORREVOLE EVENTI NAZIONALE, REGIONALE, PROVINCIALE, CLUB -->
  <!-- ============================================================== -->
  <?php if (!empty($newsCards)): ?>
  <div style="background: rgba(14, 18, 28, 0.95); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 16px; padding: 12px; margin-bottom: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.4);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
      <div style="display: flex; align-items: center; gap: 6px;">
        <span class="dot" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;"></span>
        <span style="font-size: 0.72rem; font-weight: 850; color: #fde68a; text-transform: uppercase; letter-spacing: 0.06em;">
          TICKER EVENTI LIVE · NAZIONALE, REGIONI & CLUB
        </span>
      </div>
      <span style="font-size: 0.68rem; color: #94a3b8;">Scorrimento continuo · Pausa al tocco</span>
    </div>

    <div class="dx-ticker-wrapper" style="padding: 4px 0;">
      <div class="dx-ticker-track">
        <?php foreach (array_merge($newsCards, $newsCards) as $tItem): 
          $badgeBg = match($tItem['level'] ?? '') {
            'NAZIONALE' => 'linear-gradient(135deg, #1e3a8a, #0284c7)',
            'REGIONALE' => 'linear-gradient(135deg, #065f46, #10b981)',
            'PROVINCIALE' => 'linear-gradient(135deg, #0e7490, #06b6d4)',
            'CLUB' => 'linear-gradient(135deg, #78350f, #d97706)',
            default => 'rgba(255,255,255,0.1)'
          };
        ?>
          <article class="dx-ticker-card" style="min-width: 310px; width: 310px; padding: 12px; background: rgba(18, 22, 34, 0.95); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <span class="dx-ticker-badge" style="background: <?=$badgeBg?>; color: #ffffff; font-size: 0.64rem; font-weight: 800; padding: 2px 7px; border-radius: 6px;">
                <?=h($tItem['tag_label'])?>
              </span>
              <span style="font-size: 0.7rem; color: #94a3b8; display: inline-flex; align-items: center; gap: 3px;">
                <?=dx_icon('calendar', '', 11)?> <?=h($tItem['published_date'])?>
              </span>
            </div>
            <h4 class="dx-ticker-title" style="font-size: 0.86rem; font-weight: 800; color: #ffffff; line-height: 1.3; margin: 0 0 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?=h($tItem['title'])?>
            </h4>
            <p class="dx-ticker-desc" style="font-size: 0.74rem; color: #94a3b8; line-height: 1.35; margin: 0 0 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?=h($tItem['summary'])?>
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 6px;">
              <span style="font-size: 0.7rem; color: #cbd5e1; font-weight: 600;">
                <?=h($tItem['source_name'])?>
              </span>
              <a href="<?=h($tItem['source_url'])?>" class="dx-ticker-link" style="color: #38bdf8; font-size: 0.72rem; font-weight: 750; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                <span>Vedi</span> <?=dx_icon('arrow-right', '', 11)?>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ============================================================== -->
  <!-- 1. EVENTO UFFICIALE FLAGSHIP #1: TAGLIO DI PO (9-11 OTT 2026)  -->
  <!-- ============================================================== -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
    <span class="m-badge m-badge-gold">
      <?=dx_icon('activity', '', 12)?> EVENTO FARO #1 · TAGLIO DI PO
    </span>
    <span class="m-badge <?=!$isFullTaglio ? 'm-badge-green' : 'm-badge-red'?>">
      <?=dx_icon('users', '', 12)?> <?=!$isFullTaglio ? "$seatsRemainingTaglio Posti Rimasti" : "30/30 Esauriti"?>
    </span>
  </div>

  <article class="m-card m-card-gold-glow text-center" id="evento-taglio-di-po">
    <div style="font-size: 0.74rem; font-weight: 800; color: #d4af37; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">
      A.C.A.T. Basso Polesine · Metodo Hudolin O.D.V.
    </div>
    
    <h1 style="font-family: var(--font-serif); font-size: clamp(1.45rem, 5vw, 1.85rem); color: #ffffff; line-height: 1.25; margin: 4px 0 10px; font-weight: 900;">
      A Scuola di Comunicazione e Resilienza
    </h1>

    <div style="display: inline-block; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.35); border-radius: 999px; padding: 3px 12px; font-size: 0.78rem; font-weight: 800; color: #fff2b2; margin-bottom: 12px;">
      1° Livello · Corso Esperienziale · 10,00 €
    </div>

    <div style="background: rgba(20, 24, 35, 0.95); border-left: 4px solid #d4af37; border-radius: 12px; padding: 12px 14px; text-align: left; margin-bottom: 14px;">
      <p style="font-size: 0.96rem; font-weight: 800; color: #ffffff; margin: 0 0 4px; line-height: 1.4;">
        "Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri."
      </p>
      <p style="font-size: 0.8rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
        Tre giornate con Adelmo Di Salvatore per famiglie, soci ACAT e operatori. Strumenti pratici e pranzo comunitario compreso nella quota.
      </p>
    </div>

    <!-- LOCANDINA VERTICALE 9:16 -->
    <div class="m-poster-box">
      <a href="evento-ottobre-taglio-di-po.php" title="Apri scheda ufficiale e modulo di iscrizione">
        <img src="assets/img/events/evento-ottobre-taglio-di-po.jpeg" alt="Locandina Ufficiale Taglio di Po" style="width: 100%; height: auto; display: block;">
      </a>
      <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); padding: 4px 8px; border-radius: 8px; font-size: 0.72rem; color: #fff; border: 1px solid rgba(255,255,255,0.2);">
        <?=dx_icon('zoom-in', '', 12)?> Tocca per dettagli
      </div>
    </div>

    <!-- DATI CHIAVE -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; text-align: left;">
      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quando</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.86rem; margin-top: 2px;">9-10-11 Ott. 2026</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Ven 14:30 – Dom 13:00</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Dove</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.86rem; margin-top: 2px;">Taglio di Po (RO)</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Oratorio S. Francesco</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quota Unica</div>
        <div style="color: #10b981; font-weight: 900; font-size: 1.05rem; margin-top: 2px;">10,00 €</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Pranzo sabato compreso</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Formatore</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.86rem; margin-top: 2px;">Adelmo Di Salvatore</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Psichiatra e Psicoterapeuta</div>
      </div>
    </div>

    <!-- BOTTONI AZIONE -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
      <a href="evento-ottobre-taglio-di-po.php#iscrizione-rapida" class="m-btn m-btn-primary">
        <?=dx_icon('check-circle', '', 18)?>
        <span>Iscriviti Online (10€)</span>
      </a>
      <a href="evento-ottobre-taglio-di-po.php" class="m-btn m-btn-outline" style="min-height: 46px;">
        <?=dx_icon('award', 'text-neon-gold', 18)?>
        <span>Scheda & Programma Dettagliato</span>
      </a>
    </div>
  </article>

  <!-- ============================================================== -->
  <!-- 2. EVENTO UFFICIALE FLAGSHIP #2: PORTO TOLLE (24 OTT 2026)     -->
  <!-- ============================================================== -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin: 26px 0 8px;">
    <span class="m-badge m-badge-gold">
      <?=dx_icon('activity', '', 12)?> EVENTO FARO #2 · PORTO TOLLE
    </span>
    <span class="m-badge <?=!$isFullPT ? 'm-badge-green' : 'm-badge-red'?>">
      <?=dx_icon('users', '', 12)?> <?=!$isFullPT ? "$seatsRemainingPT Posti Rimasti (Gratuito)" : "40/40 Esauriti"?>
    </span>
  </div>

  <article class="m-card m-card-gold-glow text-center" id="evento-porto-tolle">
    <div style="font-size: 0.74rem; font-weight: 800; color: #d4af37; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">
      A.C.A.T. Basso Polesine · Metodologia Hudolin
    </div>
    
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 5vw, 1.85rem); color: #ffffff; line-height: 1.25; margin: 4px 0 10px; font-weight: 900;">
      S.A.T. di 2° Modulo: Coraggio, Gratitudine, Vita
    </h2>

    <div style="display: inline-block; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.35); border-radius: 999px; padding: 3px 12px; font-size: 0.78rem; font-weight: 800; color: #6ee7b7; margin-bottom: 12px;">
      Scuola Alcologica Territoriale di Aggiornamento · Partecipazione Gratuita
    </div>

    <div style="background: rgba(20, 24, 35, 0.95); border-left: 4px solid var(--neon-gold); border-radius: 12px; padding: 12px 14px; text-align: left; margin-bottom: 14px;">
      <p style="font-size: 0.96rem; font-weight: 800; color: #ffffff; margin: 0 0 4px; line-height: 1.4;">
        «La Famiglia e l'Approccio Sistemico nella Metodologia Hudolin»
      </p>
      <p style="font-size: 0.8rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
        Aggiornamento esperienziale per Famiglie e Servitori-Insegnanti di Club. Con Grazia Nicosia al Centro "Un ponte per" di Porto Tolle.
      </p>
    </div>

    <!-- LOCANDINA VERTICALE 9:16 -->
    <div class="m-poster-box">
      <a href="evento-ottobre-porto-tolle.php" title="Apri scheda ufficiale e modulo di iscrizione">
        <picture>
          <source srcset="assets/img/events/evento-ottobre-porto-tolle.webp" type="image/webp">
          <img src="assets/img/events/evento-ottobre-porto-tolle.jpeg" alt="Locandina Ufficiale SAT 2° Modulo Porto Tolle" style="width: 100%; height: auto; display: block; aspect-ratio: 9/16; object-fit: cover;">
        </picture>
      </a>
      <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); padding: 4px 8px; border-radius: 8px; font-size: 0.72rem; color: #fff; border: 1px solid rgba(255,255,255,0.2);">
        <?=dx_icon('globe', '', 12)?> Tocca per scheda completa
      </div>
    </div>

    <!-- DATI CHIAVE -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; text-align: left;">
      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quando</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.86rem; margin-top: 2px;">Sabato 24 Ott. 2026</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Ore 09:00 – 16:30</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Dove</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.86rem; margin-top: 2px;">Porto Tolle (RO)</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Centro "Un ponte per"</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quota</div>
        <div style="color: #10b981; font-weight: 900; font-size: 1.05rem; margin-top: 2px;">GRATUITA</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Iscrizione obbligatoria</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Relatrice</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.86rem; margin-top: 2px;">Grazia Nicosia</div>
        <div style="color: #94a3b8; font-size: 0.72rem;">Servitrice Insegnante</div>
      </div>
    </div>

    <!-- BOTTONI AZIONE -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
      <a href="evento-ottobre-porto-tolle.php#iscrizione-gratuita" class="m-btn m-btn-primary">
        <?=dx_icon('check-circle', '', 18)?>
        <span>Iscriviti Online (Gratuito)</span>
      </a>
      <a href="evento-ottobre-porto-tolle.php" class="m-btn m-btn-outline" style="min-height: 46px;">
        <?=dx_icon('award', 'text-neon-gold', 18)?>
        <span>Scheda Completa & Programma</span>
      </a>
    </div>
  </article>

  <!-- ============================================================== -->
  <!-- 3. HUB EVENTI NAZIONALE, PER REGIONE, PER PROVINCIA E CLUB     -->
  <!-- ============================================================== -->
  <section class="m-card" style="border-color: rgba(56, 189, 248, 0.35); background: rgba(11, 15, 25, 0.95); margin-top: 26px;" id="catalogo-eventi">
    
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
      <div>
        <span class="m-badge m-badge-cyan" style="font-size: 0.7rem; letter-spacing: 0.06em;">
          <?=dx_icon('globe', '', 12)?> HUB NAZIONALE DIPENDENZE · RETE TERRITORIALE
        </span>
        <h2 style="font-size: 1.25rem; font-weight: 850; color: #ffffff; margin: 4px 0 2px;">
          Tutti gli Eventi per Regione, Provincia e Club
        </h2>
      </div>
      <span style="font-size: 0.74rem; color: #38bdf8; font-weight: 800; background: rgba(56,189,248,0.12); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(56,189,248,0.25);">
        <span id="eventsFoundCount"><?=count($allOtherEvents)?></span> Iniziative Attive
      </span>
    </div>

    <p style="font-size: 0.8rem; color: #94a3b8; line-height: 1.45; margin: 0 0 14px;">
      Sfoglia e filtra le iniziative aggregate di tutta Italia: convegni nazionali (AICAT, Ser.D, San Patrignano, CeIS, 12 Passi), incontri regionali ARCAT, corsi provinciali APCAT/ACAT e riunioni settimanali di Club.
    </p>

    <!-- BARRA FILTRI PER LIVELLO (PILL TABS) -->
    <div style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 12px; -webkit-overflow-scrolling: touch;" id="levelFilterTabs">
      <button type="button" class="filter-btn active" data-filter-level="ALL" style="white-space: nowrap !important; padding: 6px 12px; border-radius: 999px; font-size: 0.76rem; font-weight: 800; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.12); color: #fff; cursor: pointer;">
        Tutti (<?=$countTotal?>)
      </button>
      <button type="button" class="filter-btn" data-filter-level="NATIONAL" style="white-space: nowrap !important; padding: 6px 12px; border-radius: 999px; font-size: 0.76rem; font-weight: 800; border: 1px solid rgba(59,130,246,0.4); background: rgba(59,130,246,0.15); color: #93c5fd; cursor: pointer;">
        Nazionale (<?=$countNat?>)
      </button>
      <button type="button" class="filter-btn" data-filter-level="REGIONAL" style="white-space: nowrap !important; padding: 6px 12px; border-radius: 999px; font-size: 0.76rem; font-weight: 800; border: 1px solid rgba(16,185,129,0.4); background: rgba(16,185,129,0.15); color: #6ee7b7; cursor: pointer;">
        Regionale · ARCAT (<?=$countReg?>)
      </button>
      <button type="button" class="filter-btn" data-filter-level="PROVINCIAL" style="white-space: nowrap !important; padding: 6px 12px; border-radius: 999px; font-size: 0.76rem; font-weight: 800; border: 1px solid rgba(6,182,212,0.4); background: rgba(6,182,212,0.15); color: #67e8f9; cursor: pointer;">
        Provinciale · APCAT (<?=$countProv?>)
      </button>
      <button type="button" class="filter-btn" data-filter-level="CLUB" style="white-space: nowrap !important; padding: 6px 12px; border-radius: 999px; font-size: 0.76rem; font-weight: 800; border: 1px solid rgba(245,158,11,0.4); background: rgba(245,158,11,0.15); color: #fde68a; cursor: pointer;">
        Club Locali (<?=$countClub?>)
      </button>
    </div>

    <!-- SELETTORI DISCESA REGIONE E PROVINCIA + LIVE SEARCH -->
    <div style="background: rgba(18, 22, 34, 0.95); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 12px; margin-bottom: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
      
      <div style="grid-column: span 2;">
        <label for="eventSearchInput" style="font-size: 0.72rem; color: #94a3b8; font-weight: 750; text-transform: uppercase; display: block; margin-bottom: 4px;">
          Ricerca Libera per Comune o Parola Chiave
        </label>
        <input type="text" id="eventSearchInput" placeholder="Es. Bologna, Padova, Roma, Hudolin, Ser.D..." style="width: 100%; background: rgba(10, 14, 22, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #ffffff; font-size: 0.84rem; padding: 8px 12px; outline: none;">
      </div>

      <div>
        <label for="eventRegionSelect" style="font-size: 0.72rem; color: #94a3b8; font-weight: 750; text-transform: uppercase; display: block; margin-bottom: 4px;">
          Regione (20 Regioni)
        </label>
        <select id="eventRegionSelect" style="width: 100%; background: rgba(10, 14, 22, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #ffffff; font-size: 0.82rem; padding: 8px; outline: none;">
          <option value="ALL">Tutte le Regioni d'Italia</option>
          <?php foreach ($regionsList as $reg): ?>
            <option value="<?=htmlspecialchars($reg)?>"><?=htmlspecialchars($reg)?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="eventProvinceSelect" style="font-size: 0.72rem; color: #94a3b8; font-weight: 750; text-transform: uppercase; display: block; margin-bottom: 4px;">
          Provincia
        </label>
        <select id="eventProvinceSelect" style="width: 100%; background: rgba(10, 14, 22, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; color: #ffffff; font-size: 0.82rem; padding: 8px; outline: none;">
          <option value="ALL">Tutte le Province</option>
          <?php foreach ($provincesList as $prv): ?>
            <option value="<?=htmlspecialchars($prv)?>"><?=htmlspecialchars($prv)?></option>
          <?php endforeach; ?>
        </select>
      </div>

    </div>

    <!-- ELENCO DINAMICO DEGLI EVENTI -->
    <div style="display: flex; flex-direction: column; gap: 10px;" id="eventsContainer">
      <?php foreach ($allOtherEvents as $nev): 
        $lvl = $nev['level'] ?? 'NATIONAL';
        $typeColor = match($lvl) {
          'NATIONAL' => '#3b82f6',
          'REGIONAL' => '#10b981',
          'PROVINCIAL' => '#06b6d4',
          'CLUB' => '#f59e0b',
          default => '#8b5cf6'
        };
        $lvlBadgeText = match($lvl) {
          'NATIONAL' => 'NAZIONALE',
          'REGIONAL' => 'REGIONALE · ARCAT',
          'PROVINCIAL' => 'PROVINCIALE · APCAT',
          'CLUB' => 'CLUB LOCALE',
          default => 'EVENTO'
        };
        $formattedDate = date('d M Y', strtotime($nev['starts_at']));
        $searchTerms = strtolower(($nev['title'] ?? '') . ' ' . ($nev['comune'] ?? '') . ' ' . ($nev['province'] ?? '') . ' ' . ($nev['region'] ?? '') . ' ' . ($nev['organizer'] ?? '') . ' ' . ($nev['trainer'] ?? '') . ' ' . ($nev['venue'] ?? ''));
      ?>
        <article class="event-card-item" 
                 data-level="<?=htmlspecialchars($lvl)?>" 
                 data-region="<?=htmlspecialchars($nev['region'] ?? '')?>" 
                 data-province="<?=htmlspecialchars($nev['province'] ?? '')?>" 
                 data-search="<?=htmlspecialchars($searchTerms)?>"
                 style="background: rgba(20, 25, 38, 0.85); border: 1px solid rgba(255,255,255,0.08); border-left: 3px solid <?=$typeColor?>; border-radius: 12px; padding: 12px; transition: all 0.2s ease;">
          
          <div style="display: flex; justify-content: space-between; align-items: center; gap: 6px; margin-bottom: 4px; flex-wrap: wrap;">
            <div style="display: flex; gap: 6px; align-items: center;">
              <span style="font-size: 0.66rem; font-weight: 850; color: <?=$typeColor?>; background: rgba(255,255,255,0.06); padding: 2px 7px; border-radius: 4px;">
                <?=$lvlBadgeText?>
              </span>
              <?php if (!empty($nev['region'])): ?>
                <span style="font-size: 0.66rem; color: #cbd5e1; background: rgba(255,255,255,0.04); padding: 2px 6px; border-radius: 4px;">
                  <?=htmlspecialchars($nev['region'])?>
                </span>
              <?php endif; ?>
            </div>
            <span style="font-size: 0.74rem; font-weight: 750; color: #cbd5e1; display: inline-flex; align-items: center; gap: 4px;">
              <?=dx_icon('calendar', '', 12)?> <?=$formattedDate?>
            </span>
          </div>

          <h3 style="font-size: 0.92rem; font-weight: 850; color: #ffffff; line-height: 1.3; margin: 0 0 4px;">
            <?=htmlspecialchars($nev['title'], ENT_QUOTES, 'UTF-8')?>
          </h3>

          <div style="font-size: 0.74rem; color: #d4af37; font-weight: 750; margin-bottom: 6px; display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
            <?=dx_icon('map-pin', '', 12)?>
            <span>
              <?=htmlspecialchars($nev['comune'] ?? '', ENT_QUOTES, 'UTF-8')?>
              <?php if (!empty($nev['province'])): ?>(<?=htmlspecialchars($nev['province'])?>)<?php endif; ?>
              — <?=htmlspecialchars($nev['venue'] ?? '', ENT_QUOTES, 'UTF-8')?>
            </span>
            <?php if (!empty($nev['meeting_time'])): ?>
              <span style="color: #94a3b8; font-size: 0.7rem; margin-left: 6px;">
                <?=dx_icon('clock', '', 11)?> Ore <?=htmlspecialchars($nev['meeting_time'])?>
              </span>
            <?php endif; ?>
          </div>

          <p style="font-size: 0.76rem; color: #94a3b8; line-height: 1.4; margin: 0 0 8px;">
            <?=htmlspecialchars($nev['description'] ?? '', ENT_QUOTES, 'UTF-8')?>
          </p>

          <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 6px; font-size: 0.72rem; flex-wrap: wrap; gap: 6px;">
            <span style="color: #64748b; font-weight: 600;">
              <?=htmlspecialchars($nev['organizer'] ?? 'Rete Territoriale', ENT_QUOTES, 'UTF-8')?>
            </span>
            <?php if (!empty($nev['source_url'])): ?>
              <a href="<?=htmlspecialchars($nev['source_url'], ENT_QUOTES, 'UTF-8')?>" target="_blank" rel="noopener" style="color: #38bdf8; font-weight: 750; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                <span>Info & Dettagli</span> <?=dx_icon('external-link', '', 11)?>
              </a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div id="noEventsMessage" style="display: none; padding: 24px; text-align: center; color: #94a3b8; font-size: 0.84rem;">
      <?=dx_icon('info', '', 20)?>
      <p style="margin: 8px 0 0;">Nessun evento trovato con i filtri selezionati. Prova a reimpostare i filtri.</p>
    </div>

  </section>

  <!-- ============================================================== -->
  <!-- 4. ESPLORATORE DEI 1.761 CLUB D'ITALIA PER REGIONE E PROVINCIA -->
  <!-- ============================================================== -->
  <section class="m-card" style="border-color: rgba(245, 158, 11, 0.35); background: rgba(14, 18, 28, 0.95); margin-top: 24px;" id="trova-club">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
      <div>
        <span class="m-badge m-badge-gold" style="font-size: 0.7rem; letter-spacing: 0.06em;">
          <?=dx_icon('users', '', 12)?> CENSIMENTO NAZIONALE CLUB
        </span>
        <h2 style="font-size: 1.2rem; font-weight: 850; color: #ffffff; margin: 4px 0 2px;">
          Trova il Tuo Club Locale nei Territori
        </h2>
      </div>
      <span style="font-size: 0.72rem; color: #fde68a; font-weight: 800; background: rgba(245,158,11,0.12); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(245,158,11,0.25);">
        1.761 Club Attivi
      </span>
    </div>

    <p style="font-size: 0.8rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 12px;">
      In ogni regione e provincia d'Italia ci sono Club che si riuniscono ogni settimana. La partecipazione è sempre <strong>libera, gratuita e senza vincoli</strong>. Seleziona la tua regione per trovare il presidio più vicino:
    </p>

    <!-- FORM RICERCA CLUB DINAMICA -->
    <div style="background: rgba(20, 25, 38, 0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px; margin-bottom: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
      <div>
        <label for="clubRegionSelect" style="font-size: 0.7rem; color: #d4af37; font-weight: 750; text-transform: uppercase; display: block; margin-bottom: 4px;">
          Regione Club
        </label>
        <select id="clubRegionSelect" style="width: 100%; background: rgba(10, 14, 22, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #ffffff; font-size: 0.8rem; padding: 7px; outline: none;">
          <?php foreach ($regionsList as $cr): ?>
            <option value="<?=htmlspecialchars($cr)?>" <?=$cr==='Veneto'?'selected':''?>><?=htmlspecialchars($cr)?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="clubSearchInput" style="font-size: 0.7rem; color: #d4af37; font-weight: 750; text-transform: uppercase; display: block; margin-bottom: 4px;">
          Cerca Comune / Nome
        </label>
        <input type="text" id="clubSearchInput" placeholder="Es. Mestre, Verona, Rovigo..." style="width: 100%; background: rgba(10, 14, 22, 0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; color: #ffffff; font-size: 0.8rem; padding: 7px 10px; outline: none;">
      </div>
    </div>

    <!-- CONTENITORE CARDS CLUB -->
    <div id="clubsListContainer" style="display: flex; flex-direction: column; gap: 8px; max-height: 480px; overflow-y: auto; padding-right: 4px;">
      <?php foreach ($initialClubs as $club): 
        $meetingInfo = trim(($club['meeting_day'] ?? '') . ' ' . ($club['meeting_time'] ?? ''));
        if (empty($meetingInfo)) $meetingInfo = 'Incontro Settimanale';
      ?>
        <article class="club-item-card" style="background: rgba(18, 22, 34, 0.9); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 10px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 6px;">
            <div>
              <span style="font-size: 0.65rem; color: #fde68a; font-weight: 800; text-transform: uppercase;">
                <?=htmlspecialchars($club['level'] ?? 'CLUB')?> · <?=htmlspecialchars($club['city'] ?? '')?> (<?=htmlspecialchars($club['province'] ?? '')?>)
              </span>
              <h4 style="font-size: 0.86rem; font-weight: 850; color: #ffffff; margin: 2px 0 4px; line-height: 1.25;">
                <?=htmlspecialchars($club['entity_name'])?>
              </h4>
            </div>
            <span style="font-size: 0.7rem; color: #10b981; font-weight: 750; background: rgba(16,185,129,0.1); padding: 2px 6px; border-radius: 4px; white-space: nowrap;">
              Gratuito
            </span>
          </div>

          <?php if (!empty($club['address'])): ?>
            <div style="font-size: 0.74rem; color: #94a3b8; margin-bottom: 4px;">
              <?=dx_icon('map-pin', '', 12)?> <?=htmlspecialchars($club['address'])?>
            </div>
          <?php endif; ?>

          <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 6px; margin-top: 6px; font-size: 0.72rem;">
            <span style="color: #d4af37; font-weight: 750;">
              <?=dx_icon('clock', '', 12)?> <?=htmlspecialchars($meetingInfo)?>
            </span>
            <div style="display: flex; gap: 8px;">
              <?php if (!empty($club['phone'])): ?>
                <a href="tel:<?=htmlspecialchars($club['phone'])?>" style="color: #38bdf8; font-weight: 750; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                  <?=dx_icon('phone', '', 12)?> <span>Chiama</span>
                </a>
              <?php endif; ?>
              <?php if (!empty($club['email'])): ?>
                <a href="mailto:<?=htmlspecialchars($club['email'])?>" style="color: #6ee7b7; font-weight: 750; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                  <?=dx_icon('mail', '', 12)?> <span>Email</span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 10px;">
      <a href="mappa-club.php" class="m-btn m-btn-outline" style="min-height: 42px; font-size: 0.82rem;">
        <?=dx_icon('map', 'text-neon-gold', 16)?>
        <span>Mappa Interattiva Completa dei 1.761 Club d'Italia</span>
      </a>
    </div>

  </section>

  <!-- ============================================================== -->
  <!-- BANNER ADVISOR: INIZIATIVA RESIDENZIALE CIURMA                 -->
  <!-- ============================================================== -->
  <section class="m-card" style="background: radial-gradient(circle at top right, rgba(0,212,255,0.15), rgba(12,16,28,0.95)); border: 1px solid rgba(0,212,255,0.4); border-radius: 18px; padding: 20px; margin-top: 24px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
      <?=dx_icon('ship', 'text-neon-cyan', 20)?>
      <span class="m-cat-badge" style="background: rgba(0,212,255,0.2); color: #38bdf8; border-color: #38bdf8;">AVVISO COMMUNITY · INIZIATIVA CIURMA</span>
    </div>
    <h3 style="font-size: 1.15rem; font-weight: 850; color: #ffffff; margin: 0 0 8px;">
      Iniziativa Residenziale CIURMA: Percorso di Rinascita sul Mare
    </h3>
    <p style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.55; margin: 0 0 14px;">
      Iniziativa indipendente consigliata per famiglie e conduttori di gruppo. 8 giorni e 7 notti nel Mediterraneo con formula 100% analcolica e accompagnamento formativo con Mirco Pregnolato. Logistica e alloggio a cura di agenzia viaggi partner.
    </p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="https://mircopregnolato.it/ciurma.html" target="_blank" rel="noopener" class="m-btn m-btn-primary" style="flex: 1; min-height: 42px; font-size: 0.84rem; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
        <?=dx_icon('external-link', '', 14)?> Info Dettagliate
      </a>
      <a href="crociera-benessere-masterclass.php" class="m-btn" style="flex: 1; min-height: 42px; font-size: 0.84rem; border: 1px solid rgba(255,255,255,0.2); color: #fff; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center;">
        Scheda Advisor
      </a>
    </div>
  </section>

  <!-- GRIGLIA UFFICIALE SPONSOR & ASSET DELL'ECOSISTEMA -->
  <?php require_once __DIR__ . '/templates/_sponsor_grid.php'; ?>

  <!-- FOOTER DELLA SCHEDA MOBILE -->
  <div class="text-center" style="margin-top: 16px; font-size: 0.78rem; color: #94a3b8;">
    <p style="margin: 0 0 4px;">Per informazioni e iscrizioni: Segreteria ACAT Basso Polesine · <strong>info@dependex.support</strong></p>
    <p style="margin: 0; color: #d4af37;">100% Digitale · Zero carta · Zero sprechi · Posti certificati</p>
  </div>

</div>

<!-- STICKY BOTTOM ACTION BAR PER SMARTPHONE (9:16 SAFE-AREA) -->
<div class="m-sticky-bar">
  <div class="m-sticky-bar-inner">
    <a href="evento-ottobre-taglio-di-po.php" class="m-btn m-btn-primary" style="flex: 1; min-height: 48px; font-size: 0.85rem; padding: 0 8px;">
      <?=dx_icon('check-circle', '', 15)?> Taglio di Po (10€)
    </a>
    <a href="evento-ottobre-porto-tolle.php" class="m-btn m-btn-primary" style="flex: 1; min-height: 48px; font-size: 0.85rem; padding: 0 8px; background: linear-gradient(135deg, #10b981, #059669); border-color: #10b981;">
      <?=dx_icon('award', '', 15)?> Porto Tolle (Gratis)
    </a>
    <a href="mailto:info@dependex.support?subject=Informazioni%20Eventi%20ACAT" class="m-btn m-btn-outline" style="width: 48px; min-height: 48px; padding: 0; flex-shrink: 0;" title="Contatta la Segreteria via Email">
      <?=dx_icon('mail', '', 20)?>
    </a>
  </div>
</div>

<!-- SCRIPT CLIENT-SIDE: FILTRO EVENTI MULTI-LIVELLO E RICERCA CLUB DINAMICA -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. FILTRI EVENTI
  const filterTabs = document.querySelectorAll('#levelFilterTabs .filter-btn');
  const regionSelect = document.getElementById('eventRegionSelect');
  const provinceSelect = document.getElementById('eventProvinceSelect');
  const searchInput = document.getElementById('eventSearchInput');
  const eventCards = document.querySelectorAll('#eventsContainer .event-card-item');
  const countDisplay = document.getElementById('eventsFoundCount');
  const noEventsMsg = document.getElementById('noEventsMessage');

  let activeLevel = 'ALL';

  function applyEventFilters() {
    const selectedRegion = regionSelect ? regionSelect.value : 'ALL';
    const selectedProvince = provinceSelect ? provinceSelect.value : 'ALL';
    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

    let visibleCount = 0;

    eventCards.forEach(function(card) {
      const cardLevel = card.getAttribute('data-level') || '';
      const cardRegion = card.getAttribute('data-region') || '';
      const cardProvince = card.getAttribute('data-province') || '';
      const cardSearch = card.getAttribute('data-search') || '';

      const matchLevel = (activeLevel === 'ALL' || cardLevel === activeLevel);
      const matchRegion = (selectedRegion === 'ALL' || cardRegion === selectedRegion);
      const matchProvince = (selectedProvince === 'ALL' || cardProvince === selectedProvince);
      const matchQuery = (!query || cardSearch.indexOf(query) !== -1);

      if (matchLevel && matchRegion && matchProvince && matchQuery) {
        card.style.display = 'block';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (countDisplay) {
      countDisplay.textContent = visibleCount;
    }
    if (noEventsMsg) {
      noEventsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  filterTabs.forEach(function(tab) {
    tab.addEventListener('click', function() {
      filterTabs.forEach(t => {
        t.classList.remove('active');
        t.style.background = 'rgba(255,255,255,0.06)';
      });
      tab.classList.add('active');
      tab.style.background = 'rgba(255,255,255,0.2)';
      activeLevel = tab.getAttribute('data-filter-level') || 'ALL';
      applyEventFilters();
    });
  });

  if (regionSelect) regionSelect.addEventListener('change', applyEventFilters);
  if (provinceSelect) provinceSelect.addEventListener('change', applyEventFilters);
  if (searchInput) searchInput.addEventListener('input', applyEventFilters);

  // 2. RICERCA CLUB DINAMICA
  const clubRegion = document.getElementById('clubRegionSelect');
  const clubSearch = document.getElementById('clubSearchInput');
  const clubsContainer = document.getElementById('clubsListContainer');

  let clubDebounceTimer = null;

  function loadClubsAjax() {
    const reg = clubRegion ? clubRegion.value : '';
    const q = clubSearch ? clubSearch.value.trim() : '';

    const url = 'api-clubs-italy.php?action=list&region=' + encodeURIComponent(reg) + '&q=' + encodeURIComponent(q) + '&limit=15';

    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (!data || !data.items || !clubsContainer) return;
        if (data.items.length === 0) {
          clubsContainer.innerHTML = '<div style="padding:16px;text-align:center;color:#94a3b8;font-size:0.78rem;">Nessun club trovato per i filtri indicati.</div>';
          return;
        }

        let html = '';
        data.items.forEach(c => {
          const mInfo = (c.meeting_day || '') + ' ' + (c.meeting_time || '');
          const phoneBtn = c.phone ? '<a href="tel:' + encodeURIComponent(c.phone) + '" style="color:#38bdf8;font-weight:750;text-decoration:none;display:inline-flex;align-items:center;gap:3px;font-size:0.72rem;">Chiama</a>' : '';
          const mailBtn = c.email ? '<a href="mailto:' + encodeURIComponent(c.email) + '" style="color:#6ee7b7;font-weight:750;text-decoration:none;display:inline-flex;align-items:center;gap:3px;font-size:0.72rem;">Email</a>' : '';
          const addr = c.address ? '<div style="font-size:0.74rem;color:#94a3b8;margin-bottom:4px;">' + (c.address || '') + '</div>' : '';

          html += '<article class="club-item-card" style="background:rgba(18,22,34,0.9);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:10px;">' +
            '<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:6px;">' +
              '<div>' +
                '<span style="font-size:0.65rem;color:#fde68a;font-weight:800;text-transform:uppercase;">' + (c.level || 'CLUB') + ' · ' + (c.city || '') + ' (' + (c.province || '') + ')</span>' +
                '<h4 style="font-size:0.86rem;font-weight:850;color:#ffffff;margin:2px 0 4px;line-height:1.25;">' + (c.entity_name || '') + '</h4>' +
              '</div>' +
              '<span style="font-size:0.7rem;color:#10b981;font-weight:750;background:rgba(16,185,129,0.1);padding:2px 6px;border-radius:4px;white-space:nowrap;">Gratuito</span>' +
            '</div>' +
            addr +
            '<div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid rgba(255,255,255,0.06);padding-top:6px;margin-top:6px;font-size:0.72rem;">' +
              '<span style="color:#d4af37;font-weight:750;">' + (mInfo.trim() || 'Incontro Settimanale') + '</span>' +
              '<div style="display:flex;gap:8px;">' + phoneBtn + mailBtn + '</div>' +
            '</div>' +
          '</article>';
        });

        clubsContainer.innerHTML = html;
      })
      .catch(err => {
        console.error('Errore caricamento club:', err);
      });
  }

  if (clubRegion) {
    clubRegion.addEventListener('change', loadClubsAjax);
  }
  if (clubSearch) {
    clubSearch.addEventListener('input', function() {
      clearTimeout(clubDebounceTimer);
      clubDebounceTimer = setTimeout(loadClubsAjax, 300);
    });
  }
});
</script>

<?php require '_footer.php';?>
