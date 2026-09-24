<?php
/**
 * DEPENDEX & ACAT BASSO POLESINE — SCHEDA EVENTO DEDICATA & ISCRIZIONI
 * "S.A.T. di 2° Modulo: La Famiglia e l'Approccio Sistemico nella Metodologia Hudolin"
 * Data: Sabato 24 Ottobre 2026 (Ore 09:00 - 16:30)
 * Sede: Centro aggregativo "Un ponte per", Via G. Matteotti 248, Porto Tolle (RO) 45018
 * Relatrice: Servitrice-Insegnante Grazia Nicosia (cell. 347 8844271 - nicogra1959@gmail.com)
 * Costo: Iscrizioni Gratuite
 * Design: Mobile-First 9:16 Shell + Dual-Ratio Desktop (Zero Sbordature)
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';

$u = current_user();
$sic = 'SIC-EVT-ACAT-BP-2026-SAT2';

$pdo = db();
$st = $pdo->prepare('SELECT * FROM events WHERE sic_id = ?');
$st->execute([$sic]);
$e = $st->fetch(PDO::FETCH_ASSOC);

if (!$e) {
    EventSyncService::syncWebEvents();
    $st->execute([$sic]);
    $e = $st->fetch(PDO::FETCH_ASSOC);
    if (!$e) {
        $e = [
            'sic_id' => 'SIC-EVT-ACAT-BP-2026-SAT2',
            'type' => 'FORMAZIONE',
            'title' => 'S.A.T. di 2° Modulo: La Famiglia e l\'Approccio Sistemico nella Metodologia Hudolin',
            'description' => 'Scuola Alcologica Territoriale di 2° Modulo per Famiglie e Servitori-Insegnanti di Club. Approccio sistemico multifamiliare secondo il Metodo Vladimir Hudolin. Tema: Coraggio, Gratitudine, Vita.',
            'starts_at' => '2026-10-24 09:00:00',
            'ends_at' => '2026-10-24 16:30:00',
            'venue' => 'Centro aggregativo "Un ponte per"',
            'comune' => 'Porto Tolle',
            'address' => 'Via G. Matteotti 248, Porto Tolle (RO) 45018',
            'capacity' => 40,
            'price_eur' => 0.00,
            'organizer' => 'A.C.A.T. BASSO POLESINE (Associazione dei Club Alcologici Territoriali)',
            'trainer' => 'Grazia Nicosia (Servitrice Insegnante)',
            'registration_deadline' => '2026-09-15 23:59:59'
        ];
    }
}

// Conteggio iscritti confermati con auto-riparazione schema
$totalBooked = 0;
$waitlistCount = 0;
try {
    ensure_core_schema($pdo);
    $countStmt = $pdo->prepare("
        SELECT (
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = ? AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
            (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = ? AND eb.status = 'CONFIRMED')
        ) as total_booked
    ");
    $countStmt->execute([$sic, $sic]);
    $totalBooked = (int)$countStmt->fetchColumn();

    $wlCountStmt = $pdo->prepare("SELECT COUNT(*) FROM event_bookings WHERE event_sic_id = ? AND status = 'WAITLIST'");
    $wlCountStmt->execute([$sic]);
    $waitlistCount = (int)$wlCountStmt->fetchColumn();
} catch (Throwable $err) {
    $totalBooked = 0;
    $waitlistCount = 0;
}

$capacity = (int)($e['capacity'] ?? 40);
$seatsRemaining = max(0, $capacity - $totalBooked);
$isFull = ($seatsRemaining <= 0);
$percentBooked = $capacity > 0 ? min(100, round(($totalBooked / $capacity) * 100)) : 0;

$pageTitle = 'S.A.T. di 2° Modulo · Porto Tolle · ACAT Basso Polesine';
$metaDesc = 'Sabato 24 Ottobre 2026, Centro Un ponte per, Porto Tolle (RO). S.A.T. di 2° Modulo con Grazia Nicosia: La Famiglia e l\'Approccio Sistemico nella Metodologia Hudolin. Iscrizioni Gratuite.';
$ogImage = 'assets/img/events/evento-ottobre-porto-tolle.webp';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/evento-ottobre-porto-tolle.php';
$breadcrumbs = [
    'Home' => '/',
    'Eventi' => 'events-public.php',
    'SAT 2° Modulo Porto Tolle (Gratuito)' => 'evento-ottobre-porto-tolle.php'
];

$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "EducationEvent",
    "name" => $e['title'] ?? 'S.A.T. di 2° Modulo: La Famiglia e l\'Approccio Sistemico nella Metodologia Hudolin',
    "description" => $e['description'] ?? 'Scuola Alcologica Territoriale di Aggiornamento per Famiglie e Servitori Insegnanti di Club.',
    "startDate" => "2026-10-24T09:00:00+02:00",
    "endDate" => "2026-10-24T16:30:00+02:00",
    "eventStatus" => "https://schema.org/EventScheduled",
    "eventAttendanceMode" => "https://schema.org/OfflineEventAttendanceMode",
    "location" => [
        "@type" => "Place",
        "name" => $e['venue'] ?? 'Centro aggregativo "Un ponte per"',
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "Via G. Matteotti 248",
            "addressLocality" => "Porto Tolle",
            "addressRegion" => "Rovigo",
            "postalCode" => "45018",
            "addressCountry" => "IT"
        ]
    ],
    "image" => [
        "https://" . ($brand['domain'] ?? 'dependex.social') . "/assets/img/events/evento-ottobre-porto-tolle.webp"
    ],
    "performer" => [
        "@type" => "Person",
        "name" => "Grazia Nicosia",
        "jobTitle" => "Servitrice Insegnante Metodo Hudolin"
    ],
    "organizer" => [
        "@type" => "Organization",
        "name" => $e['organizer'] ?? "A.C.A.T. BASSO POLESINE",
        "url" => "https://" . ($brand['domain'] ?? 'dependex.social')
    ],
    "offers" => [
        "@type" => "Offer",
        "url" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/evento-ottobre-porto-tolle.php",
        "price" => "0.00",
        "priceCurrency" => "EUR",
        "availability" => $isFull ? "https://schema.org/SoldOut" : "https://schema.org/InStock",
        "validFrom" => "2026-08-01T00:00:00+02:00"
    ]
];

require '_header.php';
?>

<style id="porto-tolle-rainbow-styles">
  .porto-tolle-landing {
    background-color: #060810 !important;
    background-image: 
      radial-gradient(circle at 10% 8%, rgba(255, 51, 68, 0.22) 0%, transparent 40%),
      radial-gradient(circle at 90% 15%, rgba(255, 119, 0, 0.2) 0%, transparent 42%),
      radial-gradient(circle at 50% 32%, rgba(255, 215, 0, 0.18) 0%, transparent 45%),
      radial-gradient(circle at 15% 55%, rgba(0, 255, 119, 0.19) 0%, transparent 42%),
      radial-gradient(circle at 85% 68%, rgba(0, 212, 255, 0.22) 0%, transparent 45%),
      radial-gradient(circle at 25% 88%, rgba(58, 85, 255, 0.2) 0%, transparent 42%),
      radial-gradient(circle at 80% 95%, rgba(184, 41, 255, 0.22) 0%, transparent 44%),
      radial-gradient(rgba(255, 255, 255, 0.08) 1.2px, transparent 1.2px) !important;
    background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 28px 28px !important;
    background-attachment: fixed !important;
    min-height: 100vh;
    color: #f1f5f9 !important;
    position: relative;
    overflow-x: hidden;
  }

  .porto-tolle-landing .mobile-916-shell {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    padding: 12px 14px 110px;
    transition: max-width 0.3s ease, padding 0.3s ease;
  }

  @media (min-width: 992px) {
    .porto-tolle-landing .mobile-916-shell {
      max-width: 1440px !important;
      margin: 24px auto !important;
      padding: 28px 40px 70px !important;
      background: rgba(10, 14, 25, 0.76) !important;
      border: 1px solid rgba(255, 215, 0, 0.25) !important;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.9), 0 0 35px rgba(255, 215, 0, 0.1) !important;
      border-radius: 28px !important;
    }

    .porto-tolle-landing .adaptive-169-split {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr) !important;
      gap: 28px !important;
      align-items: stretch !important;
    }

    .porto-tolle-landing .split-col-left {
      display: flex !important;
      flex-direction: column !important;
      gap: 18px !important;
      justify-content: flex-start !important;
    }

    .porto-tolle-landing .split-col-right {
      display: flex !important;
      flex-direction: column !important;
      gap: 18px !important;
    }

    .porto-tolle-landing .m-sticky-bar {
      display: none !important;
    }
  }

  @media (max-width: 991px) {
    .porto-tolle-landing .adaptive-169-split {
      display: flex !important;
      flex-direction: column !important;
      gap: 14px !important;
    }
    .porto-tolle-landing .split-col-left,
    .porto-tolle-landing .split-col-right {
      display: contents !important;
    }
    .order-m-1 { order: 1 !important; }
    .order-m-2 { order: 2 !important; }
    .order-m-3 { order: 3 !important; }
    .order-m-4 { order: 4 !important; }
    .order-m-5 { order: 5 !important; }
  }

  .m-poster-box {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    border: 1.5px solid rgba(255, 215, 0, 0.35);
    box-shadow: 0 16px 45px rgba(0, 0, 0, 0.8), 0 0 25px rgba(255, 215, 0, 0.15);
    background: #000;
  }
  .m-poster-box img {
    width: 100%;
    height: auto;
    display: block;
    aspect-ratio: 9 / 16;
    object-fit: cover;
  }

  .schedule-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(22, 27, 40, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.06);
  }
  .schedule-time {
    font-weight: 850;
    color: #d4af37;
    font-size: 0.85rem;
    white-space: nowrap;
    min-width: 92px;
  }
  .schedule-content {
    font-size: 0.84rem;
    color: #cbd5e1;
    line-height: 1.45;
  }

  .m-btn {
    white-space: nowrap !important;
  }
</style>

<div class="porto-tolle-landing">
  <div class="mobile-916-shell">

    <!-- BREADCRUMBS & BADGES -->
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 14px; flex-wrap: wrap;">
      <span class="m-badge m-badge-gold">
        <?=dx_icon('activity', '', 12)?> A.C.A.T. BASSO POLESINE
      </span>
      <span class="m-badge <?=!$isFull ? 'm-badge-green' : 'm-badge-red'?>">
        <?=dx_icon('users', '', 12)?> <?=!$isFull ? "$seatsRemaining Posti Rimasti (Gratuito)" : "40/40 Posti Esauriti (Waitlist)"?>
      </span>
    </div>

    <!-- DUAL-RATIO SPLIT LAYOUT -->
    <div class="adaptive-169-split">

      <!-- COLONNA SINISTRA: LOCANDINA 9:16 + CONTATTI DOCENTE -->
      <div class="split-col-left">

        <section class="m-card order-m-2" style="background: rgba(14, 18, 28, 0.95); border: 1.5px solid rgba(212,175,55,0.4); padding: 14px; border-radius: 20px;">
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px;">
            <div style="font-size: 0.74rem; font-weight: 850; color: #d4af37; text-transform: uppercase; letter-spacing: 0.06em;">
              <?=dx_icon('award', '', 14)?> Locandina Ufficiale 9:16
            </div>
            <span style="font-size: 0.7rem; color: #10b981; font-weight: 800; background: rgba(16,185,129,0.14); padding: 2px 8px; border-radius: 6px;">
              Formato Smartphone HD
            </span>
          </div>

          <div class="m-poster-box">
            <a href="assets/img/events/evento-ottobre-porto-tolle.webp" target="_blank" rel="noopener" title="Visualizza locandina ingrandita">
              <picture>
                <source srcset="assets/img/events/evento-ottobre-porto-tolle.webp" type="image/webp">
                <img src="assets/img/events/evento-ottobre-porto-tolle.jpeg" alt="Locandina Ufficiale SAT 2° Modulo Porto Tolle" loading="eager">
              </picture>
            </a>
            <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); padding: 4px 8px; border-radius: 8px; font-size: 0.72rem; color: #fff; border: 1px solid rgba(255,255,255,0.2);">
              <?=dx_icon('globe', '', 12)?> Tocca per ingrandire
            </div>
          </div>
        </section>

        <!-- CONTATTO DOCENTE & ORGANIZZAZIONE -->
        <section class="m-card order-m-4" style="background: rgba(14, 18, 28, 0.94); border-left: 3px solid #10b981;">
          <div style="font-size: 0.76rem; color: #10b981; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('phone', '', 14)?> Contatti Diretti & Segreteria
          </div>
          <div style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.5; display: flex; flex-direction: column; gap: 8px;">
            <div>
              <b style="color: #ffffff;">Relatrice:</b> Servitrice-Insegnante <strong>Grazia Nicosia</strong>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
              <a href="tel:3478844271" class="m-btn m-btn-outline" style="min-height: 44px; font-size: 0.82rem; padding: 6px 14px; text-decoration: none;">
                <?=dx_icon('phone', '', 14)?> <span>347 8844271</span>
              </a>
              <a href="mailto:nicogra1959@gmail.com?subject=Iscrizione%20SAT%202%20Modulo%20Porto%20Tolle" class="m-btn m-btn-outline" style="min-height: 44px; font-size: 0.82rem; padding: 6px 14px; text-decoration: none;">
                <?=dx_icon('mail', '', 14)?> <span>nicogra1959@gmail.com</span>
              </a>
            </div>
            <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">
              <?=dx_icon('clock', '', 12)?> Termine iscrizioni: <strong>15 Settembre 2026</strong>
            </div>
          </div>
        </section>

      </div> <!-- /.split-col-left -->

      <!-- COLONNA DESTRA: HERO, DATI CHIAVE, PROGRAMMA & FORM DI ISCRIZIONE -->
      <div class="split-col-right">

        <!-- HERO EVENTO -->
        <article class="m-card m-card-gold-glow text-center order-m-1" style="border-radius: 22px; position: relative; overflow: hidden;">
          
          <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem; padding: 4px 14px;">
            <span class="dot"></span>
            <span class="text-rainbow">APPROCCIO ECOLOGICO-SOCIALE HUDOLIN · 2° MODULO</span>
          </div>

          <h1 style="font-family: var(--font-serif); font-size: clamp(1.6rem, 4.5vw, 2.25rem); color: #ffffff; line-height: 1.25; margin: 6px 0 10px; font-weight: 900;">
            S.A.T. di 2° Modulo: <span class="rainbow-text">Coraggio, Gratitudine, Vita</span>
          </h1>

          <div style="background: rgba(18, 24, 38, 0.95); border-left: 4px solid var(--neon-gold); border-radius: 12px; padding: 14px; text-align: left; margin: 12px 0 16px;">
            <p style="font-size: 1.02rem; font-weight: 850; color: #ffffff; margin: 0 0 6px; line-height: 1.35;">
              «La Famiglia e l'Approccio Sistemico nella Metodologia Hudolin»
            </p>
            <p style="font-size: 0.84rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
              Scuola Alcologica Territoriale di Aggiornamento per <strong>Famiglie e Servitori-Insegnanti di Club</strong>. Aperta a tutte le A.C.A.T. per riscoprire e consolidare i punti cardine del nostro cammino comune.
            </p>
          </div>

          <!-- DATI CHIAVE (4 BOX) -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; text-align: left;">
            <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
              <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quando</div>
              <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Sabato 24 Ott. 2026</div>
              <div style="color: #94a3b8; font-size: 0.74rem;">Ore 09:00 – 16:30</div>
            </div>

            <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
              <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Sede del Corso</div>
              <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Porto Tolle (RO)</div>
              <div style="color: #94a3b8; font-size: 0.74rem;">Centro "Un ponte per"</div>
            </div>

            <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
              <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quota Partecipazione</div>
              <div style="color: #10b981; font-weight: 900; font-size: 1.05rem; margin-top: 2px;">GRATUITA</div>
              <div style="color: #94a3b8; font-size: 0.74rem;">Iscrizione obbligatoria</div>
            </div>

            <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
              <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Docente</div>
              <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Grazia Nicosia</div>
              <div style="color: #94a3b8; font-size: 0.74rem;">Servitrice Insegnante</div>
            </div>
          </div>

          <!-- INDICATORE CAPIENZA 40 POSTI -->
          <div style="background: rgba(14, 17, 24, 0.95); border: 1px solid rgba(212,175,55,0.3); border-radius: 14px; padding: 10px 12px; margin-bottom: 12px; text-align: left;">
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 750;">
              <span style="color: #cbd5e1;">Posti Riservati per Famiglie & Club:</span>
              <b style="color: <?=!$isFull ? '#10b981' : '#ef4444'?>;"><?=$totalBooked?> / <?=$capacity?> Occupati</b>
            </div>
            <div class="m-progress-bar">
              <div class="m-progress-fill" style="width: <?=$percentBooked?>%;"></div>
            </div>
            <div style="font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between;">
              <span>Termine: 15 Settembre 2026</span>
              <span><?=!$isFull ? "$seatsRemaining posti disponibili" : "Posti esauriti · Waitlist attiva"?></span>
            </div>
          </div>

          <!-- AZIONI RAPIDE -->
          <div style="display: flex; flex-direction: column; gap: 8px;">
            <a href="#iscrizione-gratuita" class="m-btn m-btn-primary">
              <?=dx_icon('check-circle', '', 18)?>
              <span><?=!$isFull ? "Iscriviti Online (Gratuito)" : "Iscriviti in Lista d'Attesa"?></span>
            </a>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
              <a href="tel:3478844271" class="m-btn m-btn-outline" style="min-height: 48px;">
                <?=dx_icon('phone', 'text-neon-cyan', 16)?>
                <span>Chiama Docente</span>
              </a>
              <a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 48px;">
                <?=dx_icon('calendar', '', 16)?>
                <span>Salva Calendario</span>
              </a>
            </div>
          </div>

        </article>

        <!-- PROGRAMMA DELLA GIORNATA (8 SESSIONI DAL PDF) -->
        <section class="m-card order-m-3" style="background: rgba(14, 18, 28, 0.95); border-radius: 18px; padding: 18px;">
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px;">
            <div style="font-size: 0.82rem; font-weight: 850; color: #d4af37; text-transform: uppercase; display: flex; align-items: center; gap: 6px;">
              <?=dx_icon('clock', '', 16)?> Programma Ufficiale della Giornata
            </div>
            <span style="font-size: 0.7rem; color: #94a3b8;">Attestati a fine corso</span>
          </div>

          <div style="display: flex; flex-direction: column; gap: 8px;">
            <div class="schedule-row">
              <span class="schedule-time">09:00 – 09:30</span>
              <span class="schedule-content"><strong>Accoglienza ed iscrizioni al Corso</strong> presso il Centro aggregativo "Un ponte per"</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-time">09:30 – 10:30</span>
              <span class="schedule-content"><strong>Saluti di benvenuto</strong> e presentazione interattiva degli obiettivi e della metodologia del Corso</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-time">10:30 – 11:00</span>
              <span class="schedule-content"><strong>Pausa</strong> e momento di convivialità</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-time">11:00 – 12:45</span>
              <span class="schedule-content"><strong>Lavori in gruppi autogestiti:</strong> confronto sulle esperienze vissute e la risorsa della famiglia</span>
            </div>
            <div class="schedule-row" style="background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3);">
              <span class="schedule-time" style="color: #10b981;">13:00 – 14:00</span>
              <span class="schedule-content"><strong>Pausa pranzo comunitario:</strong> momento centrale di condivisione e relazione</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-time">14:00 – 15:30</span>
              <span class="schedule-content"><strong>Condivisione ed elaborazione in plenaria:</strong> sintesi dei lavori di gruppo ed approfondimenti</span>
            </div>
            <div class="schedule-row">
              <span class="schedule-time">15:30 – 16:30</span>
              <span class="schedule-content">
                <strong>Riflessioni finali:</strong><br>
                • <em>«Come mi sono sentito/a all'interno del gruppo»</em><br>
                • <em>«Quanto mi è servito questo Corso e cosa ho appreso»</em><br>
                • <em>«Cosa posso fare per essere una risorsa per il club e non solo...»</em><br>
                • <em>«Proposte per il futuro»</em>
              </span>
            </div>
            <div class="schedule-row" style="background: rgba(212, 175, 55, 0.1); border-color: rgba(212, 175, 55, 0.35);">
              <span class="schedule-time" style="color: #fde047;">16:30</span>
              <span class="schedule-content"><strong>Chiusura del Corso e Consegna degli Attestati di Partecipazione</strong></span>
            </div>
          </div>
        </section>

        <!-- FORM ISCRIZIONE ONLINE GRATUITA -->
        <section class="m-card m-card-gold-glow order-m-5" id="iscrizione-gratuita" style="border-radius: 20px; padding: 22px;">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="color: #d4af37;"><?=dx_icon('edit', '', 20)?></span>
              <h2 style="font-size: 1.15rem; font-weight: 850; color: #ffffff; margin: 0;">
                <?=!$isFull ? "Iscrizione Online Gratuita" : "Iscrizione in Lista d'Attesa"?>
              </h2>
            </div>
            <span style="font-size: 0.74rem; font-weight: 800; color: #10b981; background: rgba(16,185,129,0.14); border: 1px solid rgba(16,185,129,0.3); padding: 4px 10px; border-radius: 8px;">
              Partecipazione Gratuita · Posti Limitati
            </span>
          </div>

          <div id="bookingAlertBox" style="display: none; padding: 14px; border-radius: 12px; margin-bottom: 14px; font-size: 0.86rem; line-height: 1.45;"></div>

          <form id="portoTolleForm" onsubmit="handlePortoTolleBooking(event)">
            <input type="hidden" name="event_sic_id" value="<?=h($sic)?>">
            <input type="hidden" name="payment_method" value="FREE">

            <div style="display: flex; flex-direction: column; gap: 10px;">
              
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="m-form-group">
                  <label for="pt_nome" style="font-size: 0.78rem; color: #cbd5e1; font-weight: 750;">Nome <span style="color: #ef4444;">*</span></label>
                  <input type="text" id="pt_nome" name="nome" class="m-input" required placeholder="Mario" autocomplete="given-name">
                </div>
                <div class="m-form-group">
                  <label for="pt_cognome" style="font-size: 0.78rem; color: #cbd5e1; font-weight: 750;">Cognome <span style="color: #ef4444;">*</span></label>
                  <input type="text" id="pt_cognome" name="cognome" class="m-input" required placeholder="Rossi" autocomplete="family-name">
                </div>
              </div>

              <div class="m-form-group">
                <label for="pt_email" style="font-size: 0.78rem; color: #cbd5e1; font-weight: 750;">Indirizzo Email (per conferma & attestato) <span style="color: #ef4444;">*</span></label>
                <input type="email" id="pt_email" name="email" class="m-input" required placeholder="mario.rossi@email.it" autocomplete="email">
              </div>

              <div class="m-form-group">
                <label for="pt_phone" style="font-size: 0.78rem; color: #cbd5e1; font-weight: 750;">Numero Cellulare / WhatsApp <span style="color: #ef4444;">*</span></label>
                <input type="tel" id="pt_phone" name="phone" class="m-input" required placeholder="347 1234567" autocomplete="tel">
              </div>

              <div class="m-form-group">
                <label for="pt_role" style="font-size: 0.78rem; color: #cbd5e1; font-weight: 750;">Ruolo di partecipazione <span style="color: #ef4444;">*</span></label>
                <select id="pt_role" name="role_type" class="m-select" required>
                  <option value="Familiare di Club">Familiare di persona in Club Alcologico (CAT)</option>
                  <option value="Membro di Club (CAT)">Membro di Club Alcologico Territoriale (CAT)</option>
                  <option value="Servitore-Insegnante">Servitore-Insegnante di Club</option>
                  <option value="Operatore / Volontario">Operatore Sociale / Sanitario / Volontario</option>
                  <option value="Cittadino / Interessato">Cittadino interessato alla Metodologia Hudolin</option>
                </select>
              </div>

              <div class="m-form-group">
                <label for="pt_diet" style="font-size: 0.78rem; color: #cbd5e1; font-weight: 750;">Note per il pranzo comunitario <small>(vegetariano, celiaco, allergie)</small></label>
                <input type="text" id="pt_diet" name="dietary_notes" class="m-input" placeholder="Nessuna esigenza particolare oppure specifica">
              </div>

              <div class="m-form-group" style="display: flex; gap: 8px; align-items: flex-start; margin-top: 6px;">
                <input type="checkbox" id="pt_consent" name="privacy_accepted" required style="margin-top: 3px; width: 18px; height: 18px; accent-color: #d4af37;">
                <label for="pt_consent" style="font-size: 0.74rem; color: #cbd5e1; line-height: 1.4; margin-bottom: 0;">
                  Dichiaro di aver preso visione dell'informativa e acconsento al trattamento dei dati personali per l'organizzazione e accoglienza dell'evento ai sensi del GDPR.
                </label>
              </div>

              <button type="submit" id="pt_submit_btn" class="m-btn m-btn-primary" style="width: 100%; font-size: 0.95rem; min-height: 48px; margin-top: 8px;">
                <?=dx_icon('check-circle', '', 18)?>
                <span><?=!$isFull ? "Conferma Iscrizione Gratuita" : "Iscriviti in Lista d'Attesa"?></span>
              </button>

            </div>
          </form>

        </section>

      </div> <!-- /.split-col-right -->

    </div> <!-- /.adaptive-169-split -->

    <!-- SEZIONE PEDAGOGICA: IL VALORE DEL METODO HUDOLIN -->
    <section class="m-card" style="margin-top: 24px; background: rgba(11, 15, 25, 0.95); border: 1px solid rgba(255, 215, 0, 0.25); border-radius: 18px; padding: 22px;">
      <div style="font-size: 0.82rem; font-weight: 850; color: #d4af37; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
        <?=dx_icon('users', '', 16)?> Perché Questo Corso è Fondamentale
      </div>
      <p style="font-size: 0.88rem; color: #e2e8f0; line-height: 1.6; margin-bottom: 10px;">
        Al corso le famiglie imparano come, applicando il <strong>Metodo Hudolin</strong> e frequentando il club, si può adottare un nuovo stile di vita. Inoltre al club impariamo come le famiglie che frequentano da più tempo siano una risorsa insostituibile, capaci di trasmettere la propria esperienza ed il proprio vissuto, divenendo un punto di riferimento per le nuove famiglie.
      </p>
      <p style="font-size: 0.85rem; color: #cbd5e1; line-height: 1.6; margin-bottom: 12px;">
        Attraverso la condivisione e il confronto, nel gruppo ed in plenaria, l'obiettivo è quello di rispolverare la metodologia che portiamo avanti, facendola conoscere alle nuove famiglie e ricordandola a quelle con più cammino, mettendo in risalto i punti cardine del nostro approccio ecologico-sociale. Inoltre, l'apertura a tutte le A.C.A.T. dà un valore aggiunto al corso, perché attraverso un confronto più ampio fra le famiglie si possono approfondire maggiormente gli aspetti del Metodo.
      </p>
      <div style="background: rgba(22, 27, 40, 0.7); border-left: 3px solid #10b981; padding: 10px 14px; border-radius: 8px; font-size: 0.82rem; color: #f1f5f9;">
        <em>"Sarà compito di tutti i corsisti ri-portare all'interno del club, in famiglia ed anche nella società le proposte per un lavoro futuro."</em>
      </div>
    </section>

    <!-- SPONSOR GRID -->
    <?php require_once __DIR__ . '/templates/_sponsor_grid.php'; ?>

    <!-- FOOTER DI PAGINA -->
    <div class="text-center" style="margin-top: 20px; font-size: 0.78rem; color: #94a3b8;">
      <p style="margin: 0 0 4px;">Organizzazione: A.C.A.T. Basso Polesine · <strong>info@dependex.support</strong></p>
      <p style="margin: 0; color: #d4af37;">100% Digitale · Zero carta · Zero sprechi · Posti certificati</p>
    </div>

  </div> <!-- /.mobile-916-shell -->
</div> <!-- /.porto-tolle-landing -->

<!-- STICKY BOTTOM BAR SMARTPHONE -->
<div class="m-sticky-bar">
  <div class="m-sticky-bar-inner">
    <a href="#iscrizione-gratuita" class="m-btn m-btn-primary" style="flex: 1; min-height: 48px; font-size: 0.92rem; padding: 0 12px;">
      <?=dx_icon('check-circle', '', 16)?> Iscrizione Gratuita
    </a>
    <a href="tel:3478844271" class="m-btn m-btn-outline" style="width: 48px; min-height: 48px; padding: 0; flex-shrink: 0;" title="Chiama Grazia Nicosia">
      <?=dx_icon('phone', '', 18)?>
    </a>
    <a href="mailto:nicogra1959@gmail.com?subject=Iscrizione%20SAT%20Porto%20Tolle" class="m-btn m-btn-outline" style="width: 48px; min-height: 48px; padding: 0; flex-shrink: 0;" title="Invia Email">
      <?=dx_icon('mail', '', 18)?>
    </a>
  </div>
</div>

<script>
async function handlePortoTolleBooking(event) {
  event.preventDefault();
  const form = document.getElementById('portoTolleForm');
  const alertBox = document.getElementById('bookingAlertBox');
  const btn = document.getElementById('pt_submit_btn');

  const formData = new FormData(form);
  const payload = {};
  formData.forEach((val, key) => { payload[key] = val; });
  payload.payment_method = 'FREE';

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <span>Registrazione in corso...</span>';

  try {
    const res = await fetch('api-event-booking.php?action=save_booking', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (data.success) {
      alertBox.style.display = 'block';
      alertBox.style.background = 'rgba(16, 185, 129, 0.16)';
      alertBox.style.border = '1px solid #10b981';
      alertBox.style.color = '#ffffff';

      let html = '<div style="font-weight: 850; font-size: 1.1rem; color: #10b981; margin-bottom: 6px;">Iscrizione Gratuita Confermata!</div>';
      html += '<p style="margin: 0 0 8px;">Codice Iscrizione: <strong style="color: #d4af37;">' + (data.booking_sic || 'CONFERMATO') + '</strong></p>';
      html += '<p style="margin: 0 0 12px; font-size: 0.85rem; color: #cbd5e1;">La tua presenza per il <strong>24 Ottobre 2026</strong> a Porto Tolle è riservata. Ti abbiamo inviato i dettagli via email.</p>';
      
      html += '<div style="display: flex; gap: 8px; flex-wrap: wrap;">';
      html += '<a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 42px; font-size: 0.84rem;">' +
              '<?=dx_icon("calendar", "", 14)?> Salva Promemoria Calendario (.ics)</a>';
      if (data.whatsapp_group_link) {
        html += '<a href="' + data.whatsapp_group_link + '" target="_blank" rel="noopener" class="m-btn" style="min-height: 42px; font-size: 0.84rem; background: rgba(37,211,102,0.18); border: 1px solid #25d366; color: #25d366; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;">' +
                '<?=dx_icon("users", "", 14)?> Entra nel Gruppo WhatsApp</a>';
      }
      html += '</div>';

      alertBox.innerHTML = html;
      form.reset();
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      alertBox.style.display = 'block';
      alertBox.style.background = 'rgba(239, 68, 68, 0.2)';
      alertBox.style.border = '1px solid #ef4444';
      alertBox.style.color = '#fff';
      alertBox.innerText = data.error || data.message || 'Errore durante la registrazione. Riprova.';
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  } catch (err) {
    alertBox.style.display = 'block';
    alertBox.style.background = 'rgba(239, 68, 68, 0.2)';
    alertBox.style.border = '1px solid #ef4444';
    alertBox.style.color = '#fff';
    alertBox.innerText = 'Impossibile completare la richiesta. Controlla la connessione.';
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<?=dx_icon("check-circle", "", 18)?> <span>Conferma Iscrizione Gratuita</span>';
  }
}
</script>

<?php require '_footer.php'; ?>
