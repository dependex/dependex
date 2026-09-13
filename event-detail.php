<?php
/**
 * DEPENDEX & ACAT BASSO POLESINE — SCHEDA EVENTO DEDICATA & ISCRIZIONE RAPIDA
 * "A Scuola di Comunicazione e Resilienza — 1° Livello"
 * Design Mobile-First 9:16 Zero Sbordature
 * Sede: Oratorio San Francesco d'Assisi, Vicolo San Francesco 1, Taglio di Po (RO)
 * Formatore: Dott. Adelmo Di Salvatore
 */
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';
require_once __DIR__ . '/modules/commerce/CommerceEnv.php';

$paypalClientId = CommerceEnv::get('PAYPAL_CLIENT_ID', '');
$u = current_user();
$sic = trim((string)($_GET['event'] ?? 'SIC-EVT-ACAT-BP-2026-COMM'));

$pdo = db();
$st = $pdo->prepare('SELECT * FROM events WHERE sic_id = ?');
$st->execute([$sic]);
$e = $st->fetch(PDO::FETCH_ASSOC);

$defaultTaglioDiPo = [
    'sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
    'type' => 'FORMAZIONE',
    'title' => 'A Scuola di Comunicazione e Resilienza — 1° Livello',
    'description' => 'Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri. Corso di formazione esperienziale rivolto a chi vive in famiglia una situazione di dipendenza, operatori, volontari e membri dei Club Alcologici Territoriali. Tre giornate con Adelmo Di Salvatore per acquisire strumenti pratici da usare già dal lunedì.',
    'starts_at' => '2026-10-09 14:30:00',
    'ends_at' => '2026-10-11 13:00:00',
    'venue' => "Oratorio San Francesco d'Assisi",
    'comune' => 'Taglio di Po',
    'address' => 'Vicolo San Francesco 1, Taglio di Po (RO)',
    'visibility' => 'PUBLIC',
    'rank_required' => 'SEME',
    'drx_reward' => 100,
    'status' => 'PUBLISHED',
    'capacity' => 30,
    'price_eur' => 10.00,
    'source_url' => 'event-detail.php?event=SIC-EVT-ACAT-BP-2026-COMM',
    'image_url' => 'assets/img/events/evento-ottobre-taglio-di-po.jpeg',
    'organizer' => 'ACAT Basso Polesine O.D.V. & Coordinamento A.C.A.T. Polesane',
    'trainer' => 'Adelmo Di Salvatore (Psichiatra, Psicoterapeuta, Formatore Metodo Hudolin)',
    'registration_deadline' => '2026-10-01 23:59:59'
];

if (!$e) {
    $stFallback = $pdo->prepare('SELECT * FROM events WHERE sic_id = "SIC-EVT-ACAT-BP-2026-COMM" LIMIT 1');
    $stFallback->execute();
    $e = $stFallback->fetch(PDO::FETCH_ASSOC);
    if (!$e) {
        $e = $defaultTaglioDiPo;
    }
    $sic = $e['sic_id'];
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

    // Conteggio lista d'attesa
    $wlCountStmt = $pdo->prepare("SELECT COUNT(*) FROM event_bookings WHERE event_sic_id = ? AND status = 'WAITLIST'");
    $wlCountStmt->execute([$sic]);
    $waitlistCount = (int)$wlCountStmt->fetchColumn();
} catch (Throwable $e) {
    $totalBooked = 0;
    $waitlistCount = 0;
}

$capacity = (int)($e['capacity'] ?? 30);
$seatsRemaining = max(0, $capacity - $totalBooked);
$isFull = ($seatsRemaining <= 0);
$percentBooked = $capacity > 0 ? min(100, round(($totalBooked / $capacity) * 100)) : 0;

$pageTitle = 'A Scuola di Comunicazione e Resilienza · Taglio di Po · ACAT';
require '_header.php';
?>

<style id="event-cosmic-rainbow-styles">
  /* ============================================================== */
  /* COSMIC RAINBOW NEON LANDING EXPERIENCE                         */
  /* Sfondo Scuro Ossidiana con i 7 Colori dello Spettro Sovrano     */
  /* ============================================================== */
  .event-cosmic-landing {
    background-color: #060810 !important;
    background-image: 
      radial-gradient(circle at 10% 8%, rgba(255, 51, 68, 0.2) 0%, transparent 40%),
      radial-gradient(circle at 90% 15%, rgba(255, 119, 0, 0.18) 0%, transparent 42%),
      radial-gradient(circle at 50% 32%, rgba(255, 215, 0, 0.16) 0%, transparent 45%),
      radial-gradient(circle at 15% 55%, rgba(0, 255, 119, 0.17) 0%, transparent 42%),
      radial-gradient(circle at 85% 68%, rgba(0, 212, 255, 0.2) 0%, transparent 45%),
      radial-gradient(circle at 25% 88%, rgba(58, 85, 255, 0.18) 0%, transparent 42%),
      radial-gradient(circle at 80% 95%, rgba(184, 41, 255, 0.2) 0%, transparent 44%),
      radial-gradient(rgba(255, 255, 255, 0.08) 1.2px, transparent 1.2px) !important;
    background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 100% 100%, 28px 28px !important;
    background-attachment: fixed !important;
    min-height: 100vh;
    color: #f1f5f9 !important;
    position: relative;
    overflow-x: hidden;
  }

  /* 7 SFERE LUMINOSE AURORA (I 7 COLORI ARCOBALENO SOVRANI) */
  .event-cosmic-landing .aurora-orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(85px);
    pointer-events: none;
    z-index: 0;
    opacity: 0.75;
    animation: orbFloat 22s ease-in-out infinite alternate;
  }
  .event-cosmic-landing .orb-1-red { width: 420px; height: 420px; top: 20px; left: -120px; background: radial-gradient(circle, rgba(255,51,68,0.4) 0%, rgba(255,51,68,0.1) 60%, transparent 75%); animation-duration: 26s; }
  .event-cosmic-landing .orb-2-orange { width: 400px; height: 400px; top: 18%; right: -100px; background: radial-gradient(circle, rgba(255,119,0,0.36) 0%, rgba(255,119,0,0.1) 60%, transparent 75%); animation-duration: 22s; animation-delay: -4s; }
  .event-cosmic-landing .orb-3-gold { width: 440px; height: 440px; top: 38%; left: 30%; background: radial-gradient(circle, rgba(255,215,0,0.32) 0%, rgba(255,215,0,0.08) 60%, transparent 75%); animation-duration: 28s; animation-delay: -8s; }
  .event-cosmic-landing .orb-4-green { width: 420px; height: 420px; top: 52%; left: -90px; background: radial-gradient(circle, rgba(0,255,119,0.35) 0%, rgba(0,255,119,0.09) 60%, transparent 75%); animation-duration: 24s; animation-delay: -12s; }
  .event-cosmic-landing .orb-5-cyan { width: 460px; height: 460px; top: 68%; right: -110px; background: radial-gradient(circle, rgba(0,212,255,0.38) 0%, rgba(0,212,255,0.1) 60%, transparent 75%); animation-duration: 25s; animation-delay: -7s; }
  .event-cosmic-landing .orb-6-indigo { width: 400px; height: 400px; bottom: 120px; left: 10%; background: radial-gradient(circle, rgba(58,85,255,0.35) 0%, rgba(58,85,255,0.08) 60%, transparent 75%); animation-duration: 23s; animation-delay: -15s; }
  .event-cosmic-landing .orb-7-violet { width: 450px; height: 450px; bottom: 30px; right: -80px; background: radial-gradient(circle, rgba(184,41,255,0.4) 0%, rgba(184,41,255,0.1) 60%, transparent 75%); animation-duration: 27s; animation-delay: -10s; }

  @keyframes orbFloat {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    50% { transform: translate3d(40px, -35px, 0) scale(1.08); }
    100% { transform: translate3d(-30px, 25px, 0) scale(0.96); }
  }

  /* SHELL CONTAINER DUAL-RATIO: 9:16 SMARTPHONE / 16:9 PC-TABLET */
  .event-cosmic-landing .mobile-916-shell {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 520px;
    margin: 0 auto;
    padding: 12px 14px 110px;
    transition: max-width 0.3s ease, padding 0.3s ease;
  }
  @media (min-width: 992px) {
    .event-cosmic-landing .mobile-916-shell {
      max-width: 1440px !important;
      margin: 24px auto !important;
      padding: 28px 40px 70px !important;
      background: rgba(10, 14, 25, 0.72) !important;
      border: 1px solid rgba(255, 215, 0, 0.25) !important;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.9), 0 0 35px rgba(255, 215, 0, 0.1) !important;
      border-radius: 28px !important;
    }

    .event-cosmic-landing .adaptive-169-split {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr) !important;
      gap: 24px !important;
      align-items: stretch !important;
    }

    .event-cosmic-landing .split-col-left {
      display: flex !important;
      flex-direction: column !important;
      gap: 16px !important;
      justify-content: space-between !important;
    }

    .event-cosmic-landing .split-col-right {
      display: flex !important;
      flex-direction: column !important;
      gap: 16px !important;
    }

    .event-cosmic-landing .m-sticky-bar {
      display: none !important;
    }
  }

  @media (max-width: 991px) {
    .event-cosmic-landing .adaptive-169-split {
      display: flex !important;
      flex-direction: column !important;
      gap: 14px !important;
    }
    .event-cosmic-landing .split-col-left,
    .event-cosmic-landing .split-col-right {
      display: contents !important;
    }
    .order-m-1 { order: 1 !important; }
    .order-m-2 { order: 2 !important; }
    .order-m-3 { order: 3 !important; }
    .order-m-4 { order: 4 !important; }
    .order-m-5 { order: 5 !important; }
    .order-m-6 { order: 6 !important; }
    .order-m-7 { order: 7 !important; }
    .order-m-8 { order: 8 !important; }
  }

  /* FAST CHECKOUT FULL-WIDTH A 2 COLONNE INTELLIGENTI SU PC */
  .checkout-split-layout {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 24px;
    align-items: start;
  }
  @media (max-width: 991px) {
    .checkout-split-layout {
      grid-template-columns: 1fr;
      gap: 14px;
    }
  }

  /* GRIGLIA 5 RISULTATI A RIGA SINGOLA BILANCIATA (5x1 SU PC) */
  .results-smart-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
  }
  @media (max-width: 1200px) {
    .results-smart-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }
  @media (max-width: 768px) {
    .results-smart-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  @media (max-width: 480px) {
    .results-smart-grid {
      grid-template-columns: 1fr;
    }
  }

  /* PERFORMANCE ACCELERATION: RENDERING GRAFICO ASINCRONO */
  .event-cosmic-landing section {
    content-visibility: auto;
    contain-intrinsic-size: 400px;
  }

  /* CARD GLASSMORPHIC DARK COSMIC AD ALTO CONTRASTO */
  .event-cosmic-landing .m-card {
    background: rgba(13, 17, 28, 0.94) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6) !important;
    color: #f1f5f9 !important;
    border-radius: 18px !important;
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
  }

  .event-cosmic-landing .m-card-gold-glow {
    border-color: rgba(255, 215, 0, 0.45) !important;
    box-shadow: 0 16px 45px rgba(0, 0, 0, 0.7), 0 0 28px rgba(255, 215, 0, 0.14) !important;
  }

  /* TESTO GIUSTIFICATO DENTRO A CARDS E TABELLE */
  .event-cosmic-landing .m-card p,
  .event-cosmic-landing .m-card li,
  .event-cosmic-landing .m-card .m-schedule-desc,
  .event-cosmic-landing .m-card article p,
  .event-cosmic-landing .m-schedule-desc,
  .event-cosmic-landing table td,
  .event-cosmic-landing table th {
    text-align: justify !important;
    text-justify: inter-word !important;
    -webkit-hyphens: auto;
    -ms-hyphens: auto;
    hyphens: auto;
  }

  /* GERARCHIA TIPOGRAFICA AD ALTISSIMO CONTRASTO (ZERO BIANCO SU BIANCO) */
  .event-cosmic-landing h1,
  .event-cosmic-landing h2,
  .event-cosmic-landing h3,
  .event-cosmic-landing h4 {
    color: #ffffff !important;
    text-shadow: 0 2px 10px rgba(0,0,0,0.7);
  }

  /* INPUT FORM DARK CON FOCUS NEON CIANO/ORO */
  .event-cosmic-landing .m-input,
  .event-cosmic-landing .m-select {
    background: rgba(7, 10, 18, 0.92) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
    border-radius: 12px !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4) !important;
  }
  .event-cosmic-landing .m-input:focus,
  .event-cosmic-landing .m-select:focus {
    border-color: var(--neon-cyan) !important;
    box-shadow: 0 0 14px rgba(0, 212, 255, 0.4) !important;
    outline: none !important;
  }
  .event-cosmic-landing .m-form-group label {
    color: #f1f5f9 !important;
    font-weight: 750 !important;
    font-size: 0.85rem !important;
    margin-bottom: 6px !important;
    display: block !important;
  }

  /* TAB VIEWER LOCANDINE */
  .event-cosmic-landing .m-tab-bar {
    background: rgba(8, 11, 20, 0.9) !important;
    border: 1px solid rgba(255, 255, 255, 0.12) !important;
    border-radius: 12px !important;
  }
  .event-cosmic-landing .m-tab-btn {
    color: #94a3b8 !important;
  }
  .event-cosmic-landing .m-tab-btn.active {
    background: rgba(255, 215, 0, 0.18) !important;
    color: #ffd700 !important;
    border: 1px solid rgba(255, 215, 0, 0.4) !important;
    box-shadow: 0 0 14px rgba(255, 215, 0, 0.25) !important;
  }

  /* BOTTONI SOVRANI */
  .event-cosmic-landing .m-btn-primary {
    background: var(--rainbow-gradient) !important;
    color: #ffffff !important;
    font-weight: 900 !important;
    border: none !important;
    box-shadow: 0 8px 24px rgba(0, 212, 255, 0.35), 0 0 15px rgba(255, 119, 0, 0.3) !important;
    text-shadow: 0 2px 4px rgba(0,0,0,0.8) !important;
  }
  .event-cosmic-landing .m-btn-outline {
    background: rgba(15, 20, 32, 0.8) !important;
    border: 1.5px solid rgba(255, 215, 0, 0.35) !important;
    color: #ffffff !important;
  }
  .event-cosmic-landing .m-btn-whatsapp {
    background: #25D366 !important;
    color: #030712 !important;
    font-weight: 900 !important;
    box-shadow: 0 8px 22px rgba(37, 211, 102, 0.35) !important;
  }

  /* STICKY BOTTOM BAR SMARTPHONE */
  .event-cosmic-landing .m-sticky-bar {
    background: rgba(10, 13, 22, 0.95) !important;
    border-top: 1px solid rgba(255, 215, 0, 0.3) !important;
    box-shadow: 0 -8px 28px rgba(0, 0, 0, 0.85) !important;
    backdrop-filter: blur(20px) !important;
    -webkit-backdrop-filter: blur(20px) !important;
  }
</style>

<div class="event-cosmic-landing">
  <!-- 7 SFERE LUMINOSE AURORA (I 7 COLORI ARCOBALENO) -->
  <div class="aurora-orb orb-1-red" aria-hidden="true"></div>
  <div class="aurora-orb orb-2-orange" aria-hidden="true"></div>
  <div class="aurora-orb orb-3-gold" aria-hidden="true"></div>
  <div class="aurora-orb orb-4-green" aria-hidden="true"></div>
  <div class="aurora-orb orb-5-cyan" aria-hidden="true"></div>
  <div class="aurora-orb orb-6-indigo" aria-hidden="true"></div>
  <div class="aurora-orb orb-7-violet" aria-hidden="true"></div>

<!-- MOBILE-FIRST 9:16 CONTAINER (Zero sbordature, responsive smartphone shell) -->
<div class="mobile-916-shell">

  <!-- TOP NAV BAR MINIMAL -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
    <a href="events-public.php" class="m-btn m-btn-outline" style="min-height: 38px; padding: 0 12px; font-size: 0.8rem; border-radius: 12px; display: inline-flex; width: auto;">
      <?=dx_icon('arrow-left', '', 14)?> Tutti gli Eventi
    </a>
    <span class="m-badge <?=!$isFull ? 'm-badge-green' : 'm-badge-red'?>">
      <?=dx_icon('users', '', 12)?> <?=!$isFull ? "$seatsRemaining Posti Disponibili" : "Lista d'Attesa"?>
    </span>
  </div>

  <!-- PATROCINI COMPATTI -->
  <div class="m-card" style="padding: 10px 12px; margin-bottom: 12px; font-size: 0.72rem; color: #cbd5e1; background: rgba(14, 17, 24, 0.9);">
    <div style="color: #d4af37; font-weight: 800; text-transform: uppercase; margin-bottom: 3px;">
      Patrocini & Collaborazioni Ufficiali
    </div>
    <div>A.C.A.T. Basso Polesine · Coord. A.C.A.T. Polesane · A.M.A. Gruppi Azzardo · Dipartimento Dipendenze ULSS 5 Polesana · Comune e Parrocchia di Taglio di Po</div>
  </div>

  <!-- DUAL-RATIO ADAPTIVE SYSTEM: 16:9 PC/TABLET 2-COLUMNS & 9:16 MOBILE STACK -->
  <div class="adaptive-169-split">
    
    <!-- COLONNA SINISTRA PC/TABLET 16:9 (Visual, Media, Docente, Sede, Sponsor) -->
    <div class="split-col-left">
      
      <!-- TAB VIEWER LOCANDINE UFFICIALI HD -->
      <section class="m-card order-m-2" id="locandinaViewer">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
          <span style="color: #d4af37;"><?=dx_icon('image', '', 18)?></span>
          <h2 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">Locandine & Programma Ufficiale HD</h2>
        </div>

        <div class="m-tab-bar" id="mediaTabs">
          <button type="button" class="m-tab-btn active" onclick="switchMediaTab('locandina_fronte', this)">Locandina Ufficiale (Fronte)</button>
          <button type="button" class="m-tab-btn" onclick="switchMediaTab('programma_retro', this)">Programma Completo (Retro)</button>
          <button type="button" class="m-tab-btn" onclick="switchMediaTab('locandina_oratorio', this)">Locandina Oratorio</button>
        </div>

        <div class="m-poster-box" id="mediaDisplay">
          <img id="activeMediaImg" src="assets/img/events/evento-ottobre-taglio-di-po.jpeg" alt="Locandina Ufficiale Corso — Taglio di Po">
          <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.78); backdrop-filter: blur(8px); padding: 4px 8px; border-radius: 8px; font-size: 0.72rem; color: #fff; border: 1px solid rgba(255,255,255,0.2);">
            <?=dx_icon('image', '', 12)?> <span id="mediaCaption">Locandina Ufficiale (Fronte)</span>
          </div>
        </div>
      </section>

      <!-- GARANZIE & PUNTI CHIAVE RAPIDI (SOTTO LA LOCANDINA) -->
      <section class="m-card order-m-3" style="background: rgba(14, 18, 28, 0.94); border-left: 3px solid #d4af37;">
        <div style="font-size: 0.76rem; color: #d4af37; font-weight: 800; text-transform: uppercase; margin-bottom: 8px;">
          <?=dx_icon('shield-check', '', 14)?> Informazioni Chiave in Breve
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.82rem; color: #cbd5e1;">
          <div style="display: flex; gap: 8px; align-items: flex-start;">
            <span style="color: #10b981; font-weight: 900;">•</span>
            <div><b>Numero Chiuso (30 Posti Max):</b> per garantire l'efficacia del lavoro esperienziale e delle simulazioni in piccoli gruppi.</div>
          </div>
          <div style="display: flex; gap: 8px; align-items: flex-start;">
            <span style="color: #10b981; font-weight: 900;">•</span>
            <div><b>Quota Simbolica 10€:</b> include l'intero materiale didattico e il pranzo comunitario del sabato preparato dai volontari.</div>
          </div>
          <div style="display: flex; gap: 8px; align-items: flex-start;">
            <span style="color: #10b981; font-weight: 900;">•</span>
            <div><b>Attestato Ufficiale:</b> valido nel circuito dei Club Alcologici Territoriali e per la formazione personale e professionale.</div>
          </div>
        </div>
      </section>

    </div> <!-- /.split-col-left -->

    <!-- COLONNA DESTRA PC/TABLET 16:9 (Hero, Dati, Iscrizione, WhatsApp, Programma) -->
    <div class="split-col-right">

      <!-- HERO EVENTO (DATI & CLAIM) -->
      <article class="m-card m-card-gold-glow text-center order-m-1" style="border-radius: 22px; position: relative; overflow: hidden;">
        
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem; padding: 4px 14px;">
          <span class="dot"></span>
          <span class="text-rainbow">APPROCCIO ECOLOGICO-SOCIALE HUDOLIN · 1° LIVELLO</span>
        </div>

        <h1 style="font-family: var(--font-serif); font-size: clamp(1.65rem, 5vw, 2.3rem); color: #ffffff; line-height: 1.25; margin: 6px 0 10px; font-weight: 900;">
          A Scuola di <span class="rainbow-text">Comunicazione e Resilienza</span>
        </h1>

        <!-- I 7 COLORI DELLA TRASFORMAZIONE (FREQUENZE DI SOVRANITÀ) -->
        <div class="d-flex justify-content-center gap-1 my-3 flex-wrap" style="font-size: 0.68rem; font-weight: 800; letter-spacing: 0.04em;">
          <span style="color: var(--neon-red); background: rgba(255,51,68,0.12); border: 1px solid rgba(255,51,68,0.3); padding: 2px 8px; border-radius: 6px;">Senti</span>
          <span style="color: var(--neon-orange); background: rgba(255,119,0,0.12); border: 1px solid rgba(255,119,0,0.3); padding: 2px 8px; border-radius: 6px;">Agisci</span>
          <span style="color: var(--neon-gold); background: rgba(255,215,0,0.12); border: 1px solid rgba(255,215,0,0.3); padding: 2px 8px; border-radius: 6px;">Comunica</span>
          <span style="color: var(--neon-green); background: rgba(0,255,119,0.12); border: 1px solid rgba(0,255,119,0.3); padding: 2px 8px; border-radius: 6px;">Vedi</span>
          <span style="color: var(--neon-cyan); background: rgba(0,212,255,0.12); border: 1px solid rgba(0,212,255,0.3); padding: 2px 8px; border-radius: 6px;">Ama</span>
          <span style="color: var(--neon-indigo); background: rgba(58,85,255,0.12); border: 1px solid rgba(58,85,255,0.3); padding: 2px 8px; border-radius: 6px;">Costruisci</span>
          <span style="color: var(--neon-violet); background: rgba(184,41,255,0.12); border: 1px solid rgba(184,41,255,0.3); padding: 2px 8px; border-radius: 6px;">Sii</span>
        </div>

        <!-- SOTTOTITOLO ORIENTATO AL RISULTATO -->
        <div style="background: rgba(18, 24, 38, 0.95); border-left: 4px solid var(--neon-gold); border-radius: 12px; padding: 14px; text-align: left; margin: 12px 0 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.4);">
          <p style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0 0 6px; line-height: 1.35;">
            "Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri."
          </p>
          <p style="font-size: 0.85rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
            Corso rivolto a <strong>chi vive in famiglia una situazione di dipendenza</strong>, oltre a operatori, volontari e membri impegnati nei Club.
          </p>
        </div>

        <!-- DATI CHIAVE -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; text-align: left;">
          <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
            <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Date & Orari</div>
            <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">9-10-11 Ottobre 2026</div>
            <div style="color: #94a3b8; font-size: 0.74rem;">Ven 14:30 – Dom 13:00</div>
          </div>

          <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
            <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Sede del Corso</div>
            <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Taglio di Po (RO)</div>
            <div style="color: #94a3b8; font-size: 0.74rem;">Oratorio S. Francesco</div>
          </div>

          <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
            <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quota Partecipazione</div>
            <div style="color: #10b981; font-weight: 900; font-size: 1.05rem; margin-top: 2px;">10,00 €</div>
            <div style="color: #94a3b8; font-size: 0.74rem;">Pranzo sabato compreso</div>
          </div>

          <div style="background: rgba(22, 25, 36, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
            <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Formatore</div>
            <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">A. Di Salvatore</div>
            <div style="color: #94a3b8; font-size: 0.74rem;">Psichiatra & Terapeuta</div>
          </div>
        </div>

        <!-- STATO CAPIENZA POSTI (30 POSTI MAX) -->
        <div style="background: rgba(14, 17, 24, 0.95); border: 1px solid rgba(212,175,55,0.3); border-radius: 14px; padding: 10px 12px; margin-bottom: 12px; text-align: left;">
          <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 750;">
            <span style="color: #cbd5e1;">Posti Ufficiali (Numero Chiuso):</span>
            <b style="color: <?=!$isFull ? '#10b981' : '#ef4444'?>;"><?=$totalBooked?> / <?=$capacity?> Occupati</b>
          </div>
          <div class="m-progress-bar">
            <div class="m-progress-fill" style="width: <?=$percentBooked?>%;"></div>
          </div>
          <div style="font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between;">
            <span>Chiusura: 1° Ottobre 2026</span>
            <span><?=!$isFull ? "$seatsRemaining posti disponibili" : "Posti esauriti · Waitlist attiva"?></span>
          </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <a href="#prenotazione" class="m-btn m-btn-primary">
            <?=dx_icon('check-circle', '', 18)?>
            <span><?=!$isFull ? "COMPILA ISCRIZIONE ONLINE (10€)" : "ISCRIVITI IN LISTA D'ATTESA"?></span>
          </a>

          <a href="https://wa.me/393478844271?text=Ciao%20Grazia,%20sono%20[nome],%20mi%20interessa%20partecipare%20all'evento%20A%20Scuola%20di%20Comunicazione%20Resilienza%20a%20Taglio%20di%20Po." target="_blank" rel="noopener" class="m-btn m-btn-whatsapp">
            <?=dx_icon('message-circle', '', 18)?>
            <span>Scrivi a Grazia su WhatsApp (+39 347 884 4271)</span>
          </a>

          <a href="https://chat.whatsapp.com/Bx6mGOuLBTmC2rxTPp4Gel" target="_blank" rel="noopener" class="m-btn" style="background: rgba(37, 211, 102, 0.15); border: 1px solid #25D366; color: #25D366; font-weight: 800; min-height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
            <?=dx_icon('users', '', 18)?>
            <span>Entra nel Gruppo WhatsApp Ufficiale</span>
          </a>

          <a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 44px; font-size: 0.88rem;">
            <?=dx_icon('calendar', '', 16)?>
            <span>Aggiungi al Calendario (.ics)</span>
          </a>
        </div>

      </article>

    </div> <!-- /.split-col-right -->

  </div> <!-- /.adaptive-169-split (FINE DIVISIONE A 2 COLONNE: HERO E LOCANDINA ORA ALLINEATI SENZA SPAZIO VUOTO) -->

  <!-- FORM PRENOTAZIONE DIRETTA (A TUTTA PAGINA - FAST CHECKOUT A 2 COLONNE SU PC) -->
  <section class="m-card m-card-gold-glow" id="prenotazione" style="margin-top: 20px; border-radius: 20px; padding: 22px 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span style="color: #d4af37;"><?=dx_icon('edit', '', 20)?></span>
        <h2 style="font-size: 1.22rem; font-weight: 850; color: #ffffff; margin: 0;">
          <?=!$isFull ? "Iscrizione Online Immediata (Quota 10,00 €)" : "Iscrizione in Lista d'Attesa"?>
        </h2>
      </div>
      <span style="font-size: 0.74rem; font-weight: 800; color: #10b981; background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); padding: 4px 10px; border-radius: 8px;">
        Pranzo Comunitario del Sabato Compreso
      </span>
    </div>

    <div id="bookingAlertBox" style="display: none; padding: 14px; border-radius: 12px; margin-bottom: 14px; font-size: 0.86rem; line-height: 1.45;"></div>

    <form id="mobileBookingForm" onsubmit="handleMobileBooking(event)">
      <input type="hidden" name="event_sic_id" value="<?=h($sic)?>">

      <div class="checkout-split-layout">

        <!-- COLONNA 1: DATI PARTECIPANTE -->
        <div>
          <div style="font-size: 0.78rem; font-weight: 800; color: #d4af37; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('users', '', 14)?> 1. Dati del Partecipante
          </div>

          <!-- NOME E COGNOME IN 2 COLONNE -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div class="m-form-group">
              <label for="mb_nome">Nome <span>*</span></label>
              <input type="text" id="mb_nome" name="nome" class="m-input" required placeholder="Mario" autocomplete="given-name">
            </div>
            <div class="m-form-group">
              <label for="mb_cognome">Cognome <span>*</span></label>
              <input type="text" id="mb_cognome" name="cognome" class="m-input" required placeholder="Rossi" autocomplete="family-name">
            </div>
          </div>

          <div class="m-form-group" style="margin-top: 8px;">
            <label for="mb_email">Indirizzo Email (per ricevuta & promemoria) <span>*</span></label>
            <input type="email" id="mb_email" name="email" class="m-input" required placeholder="mario.rossi@email.it" autocomplete="email">
          </div>

          <div class="m-form-group" style="margin-top: 8px;">
            <label for="mb_phone">Numero di Telefono (WhatsApp per conferme rapide) <span>*</span></label>
            <input type="tel" id="mb_phone" name="phone" class="m-input" required placeholder="347 1234567" autocomplete="tel">
          </div>

          <div class="m-form-group" style="margin-top: 8px;">
            <label for="mb_attendee_type">Qual è il tuo ruolo di partecipazione? <span>*</span></label>
            <select id="mb_attendee_type" name="role_type" class="m-select" required>
              <option value="Operatore / Volontario">Operatore Sociale / Sanitario / Volontario</option>
              <option value="Familiare">Familiare di persona con problemi di dipendenza</option>
              <option value="Membro di Club (CAT)">Membro / Persona che frequenta un Club (CAT)</option>
              <option value="Servitore-Insegnante">Servitore-Insegnante di Club</option>
              <option value="Cittadino / Interessato">Cittadino / Persona interessata a vario titolo</option>
            </select>
          </div>

          <div class="m-form-group" style="margin-top: 8px;">
            <label for="mb_dietary_notes">Esigenze per il pranzo del sabato <small>(vegetariano, celiaco, allergie)</small></label>
            <input type="text" id="mb_dietary_notes" name="dietary_notes" class="m-input" placeholder="Nessuna o specifica intolleranze">
          </div>
        </div>

        <!-- COLONNA 2: MODALITÀ PAGAMENTO & CONFERMA -->
        <div>
          <div style="font-size: 0.78rem; font-weight: 800; color: #d4af37; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('credit-card', '', 14)?> 2. Modalità Quota & Invio
          </div>

          <!-- SELETTORE MODALITÀ DI PAGAMENTO 10,00 € -->
          <div style="background: rgba(22, 27, 40, 0.85); border: 1px solid rgba(212,175,55,0.3); border-radius: 12px; padding: 12px; margin-bottom: 12px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
              
              <label style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                <input type="radio" name="payment_method" value="PAYPAL" checked style="accent-color: #d4af37; width: 18px; height: 18px;">
                <div>
                  <div style="font-size: 0.88rem; font-weight: 850; color: #ffffff; display: flex; align-items: center; gap: 6px;">
                    <?=dx_icon('credit-card', 'text-neon-cyan', 16)?> Carta di Credito / Debito o PayPal
                  </div>
                  <div style="font-size: 0.72rem; color: #94a3b8;">Visa, Mastercard, PostePay o saldo PayPal · Conferma istantanea</div>
                </div>
              </label>

              <label style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                <input type="radio" name="payment_method" value="USDT" style="accent-color: #d4af37; width: 18px; height: 18px;">
                <div>
                  <div style="font-size: 0.88rem; font-weight: 850; color: #ffffff; display: flex; align-items: center; gap: 6px;">
                    <?=dx_icon('gem', 'text-neon-gold', 16)?> USDT (Rete Polygon)
                  </div>
                  <div style="font-size: 0.72rem; color: #94a3b8;">10 USDT su rete Polygon · Transazione verificata on-chain</div>
                </div>
              </label>

              <label style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                <input type="radio" name="payment_method" value="ON_SITE" style="accent-color: #d4af37; width: 18px; height: 18px;">
                <div>
                  <div style="font-size: 0.88rem; font-weight: 850; color: #ffffff; display: flex; align-items: center; gap: 6px;">
                    <?=dx_icon('banknote', 'text-neon-green', 16)?> Saldo in Contanti / POS all'Accoglienza
                  </div>
                  <div style="font-size: 0.72rem; color: #94a3b8;">Versamento all'arrivo venerdì 9 ottobre dalle 14:30</div>
                </div>
              </label>

            </div>
          </div>

          <div class="m-form-group" style="display: flex; gap: 8px; align-items: flex-start; margin-bottom: 12px;">
            <input type="checkbox" id="mb_consent" name="privacy_accepted" required style="margin-top: 3px; width: 18px; height: 18px; accent-color: #d4af37;">
            <label for="mb_consent" style="font-size: 0.74rem; color: #cbd5e1; line-height: 1.4; margin-bottom: 0;">
              Dichiaro di aver preso visione dell'informativa e acconsento al trattamento dei dati personali per l'organizzazione e accoglienza dell'evento ai sensi del GDPR.
            </label>
          </div>

          <button type="submit" id="mb_submit_btn" class="m-btn m-btn-primary" style="width: 100%; font-size: 0.95rem; min-height: 48px;">
            <?=dx_icon('check-circle', '', 18)?>
            <span><?=!$isFull ? "ISCRIVITI & PROCEDI (10,00 €)" : "ISCRIVITI IN LISTA D'ATTESA"?></span>
          </button>
        </div>

      </div>
    </form>

    <!-- CONTAINER CHECKOUT PAYPAL LIVE & CARTE (DINAMICO) -->
    <div id="paypalGatewayContainer" style="display: none; margin-top: 16px; background: rgba(14, 18, 28, 0.98); border: 1px solid #d4af37; border-radius: 14px; padding: 16px; text-align: center;">
      <div style="font-size: 0.76rem; font-weight: 800; color: #d4af37; text-transform: uppercase; margin-bottom: 6px;">
        <?=dx_icon('lock', '', 14)?> CHECKOUT SICURO PAYPAL & CARTE (10,00 €)
      </div>
      <p style="font-size: 0.82rem; color: #e2e8f0; margin: 0 0 14px;">
        Prenotazione registrata con codice: <b id="pp_booking_code" style="color:#d4af37;"></b>.<br>
        Completa il versamento della quota per riservare immediatamente il tuo posto in aula:
      </p>
      <div id="paypal-buttons-mount"></div>
    </div>

    <!-- CONTAINER CHECKOUT USDT POLYGON (DINAMICO) -->
    <div id="usdtGatewayContainer" style="display: none; margin-top: 16px; background: rgba(14, 18, 28, 0.98); border: 1px solid #10b981; border-radius: 14px; padding: 16px; text-align: center;">
      <div style="font-size: 0.76rem; font-weight: 800; color: #10b981; text-transform: uppercase; margin-bottom: 6px;">
        <?=dx_icon('check-circle', '', 14)?> VERSAMENTO QUOTA 10 USDT (POLYGON)
      </div>
      <p style="font-size: 0.82rem; color: #cbd5e1; margin: 0 0 12px;">
        Codice Prenotazione: <b id="usdt_booking_code" style="color: #d4af37;"></b><br>
        Invia esattamente <b>10 USDT</b> sulla rete <b>Polygon (PoS)</b> all'indirizzo del Tesoro:
      </p>
      
      <!-- QR CODE GENERATO AL VOLO -->
      <div style="background: #ffffff; padding: 10px; display: inline-block; border-radius: 12px; margin-bottom: 12px;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=0x3C320B3a0917fF44BF6551CDdee44402AFcF250C" alt="QR Code Polygon USDT" style="width: 160px; height: 160px; display: block;">
      </div>

      <!-- BOX INDIRIZZO CON COPIA RAPIDA -->
      <div style="background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 10px; font-family: monospace; font-size: 0.76rem; color: #e2e8f0; word-break: break-all; margin-bottom: 10px;">
        0x3C320B3a0917fF44BF6551CDdee44402AFcF250C
      </div>

      <button type="button" onclick="copyPolygonAddress()" class="m-btn m-btn-outline" style="min-height: 36px; font-size: 0.78rem; margin-bottom: 14px;">
        <?=dx_icon('copy', '', 14)?> <span id="copyAddrLabel">Copia Indirizzo Polygon</span>
      </button>

      <!-- FORM PER INSERIRE TX HASH -->
      <div style="text-align: left; background: rgba(0,0,0,0.3); border-radius: 10px; padding: 10px; border: 1px solid rgba(255,255,255,0.08); max-width: 500px; margin: 0 auto;">
        <label for="usdt_tx_hash" style="font-size: 0.76rem; font-weight: 750; color: #ffffff; display: block; margin-bottom: 4px;">
          Inserisci la TX Hash della transazione inviata:
        </label>
        <input type="text" id="usdt_tx_hash" class="m-input" placeholder="Es. 0x123abc456..." style="font-size: 0.8rem; margin-bottom: 8px;">
        <button type="button" onclick="submitUsdtTx()" id="usdt_confirm_btn" class="m-btn m-btn-primary" style="min-height: 40px; font-size: 0.84rem; width: 100%;">
          <?=dx_icon('send', '', 14)?> Conferma Notifica USDT
        </button>
      </div>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- SEZIONI A TUTTA PAGINA (FULL WIDTH WIDESCREEN & MOBILE STACK)  -->
  <!-- ============================================================== -->

  <!-- 1. I 5 RISULTATI CONCRETI (A TUTTA PAGINA - GRIGLIA A 5 CARDS BILANCIATA) -->
  <section class="m-card" style="margin-top: 20px; border-radius: 20px; padding: 20px 22px; border: 1px solid rgba(16, 185, 129, 0.35); background: rgba(13, 17, 28, 0.96);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <span style="color: #10b981;"><?=dx_icon('award', '', 22)?></span>
        <h2 style="font-size: clamp(1.15rem, 3.5vw, 1.45rem); font-weight: 850; color: #ffffff; margin: 0;">
          Cosa Saprai Fare dal Lunedì <span style="color: #10b981;">(I 5 Risultati Concreti)</span>
        </h2>
      </div>
      <span style="font-size: 0.74rem; font-weight: 800; color: #d4af37; background: rgba(212,175,55,0.12); border: 1px solid rgba(212,175,55,0.3); padding: 4px 12px; border-radius: 8px;">
        Competenze Pratiche Immediatamente Spendibili
      </span>
    </div>

    <div class="results-smart-grid">
      <div style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-top: 3px solid #10b981; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <span style="font-size: 1.1rem; font-weight: 900; color: #10b981;">#01</span>
          <h3 style="font-size: 0.92rem; font-weight: 850; color: #ffffff; margin: 4px 0 6px;">Comunicare senza litigare</h3>
          <p style="font-size: 0.78rem; color: #cbd5e1; line-height: 1.4; margin: 0; text-align: justify; text-justify: inter-word; hyphens: auto;">
            Disinnescare la rabbia, esprimere i propri sentimenti in modo chiaro e congruente senza aggredire né farsi calpestare in famiglia e sul lavoro.
          </p>
        </div>
      </div>

      <div style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-top: 3px solid #3b82f6; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <span style="font-size: 1.1rem; font-weight: 900; color: #3b82f6;">#02</span>
          <h3 style="font-size: 0.92rem; font-weight: 850; color: #ffffff; margin: 4px 0 6px;">Non farsi caricare dai problemi altrui</h3>
          <p style="font-size: 0.78rem; color: #cbd5e1; line-height: 1.4; margin: 0; text-align: justify; text-justify: inter-word; hyphens: auto;">
            Riconoscere le trappole del potere, proteggere i propri confini emotivi e superare il senso di colpa paralizzante che blocca le relazioni sane.
          </p>
        </div>
      </div>

      <div style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-top: 3px solid #8b5cf6; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <span style="font-size: 1.1rem; font-weight: 900; color: #8b5cf6;">#03</span>
          <h3 style="font-size: 0.92rem; font-weight: 850; color: #ffffff; margin: 4px 0 6px;">Ascolto attivo nei conflitti</h3>
          <p style="font-size: 0.78rem; color: #cbd5e1; line-height: 1.4; margin: 0; text-align: justify; text-justify: inter-word; hyphens: auto;">
            Riconoscere le fragilità altrui ed eliminare etichette, valutazioni affrettate e giudizi fuorvianti per riaprire canali di dialogo costruttivi.
          </p>
        </div>
      </div>

      <div style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-top: 3px solid #f59e0b; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <span style="font-size: 1.1rem; font-weight: 900; color: #f59e0b;">#04</span>
          <h3 style="font-size: 0.92rem; font-weight: 850; color: #ffffff; margin: 4px 0 6px;">Risoluzione democratica "Io vinco, Tu vinci"</h3>
          <p style="font-size: 0.78rem; color: #cbd5e1; line-height: 1.4; margin: 0; text-align: justify; text-justify: inter-word; hyphens: auto;">
            Applicare la scala dei bisogni e trovare soluzioni condivise senza vincitori né vinti, sia nei nuclei familiari che nei Club e gruppi di lavoro.
          </p>
        </div>
      </div>

      <div style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-top: 3px solid #ec4899; border-radius: 14px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <span style="font-size: 1.1rem; font-weight: 900; color: #ec4899;">#05</span>
          <h3 style="font-size: 0.92rem; font-weight: 850; color: #ffffff; margin: 4px 0 6px;">Attestato Ufficiale di Partecipazione</h3>
          <p style="font-size: 0.78rem; color: #cbd5e1; line-height: 1.4; margin: 0; text-align: justify; text-justify: inter-word; hyphens: auto;">
            Rilasciato a chi partecipa per intero al corso, riconosciuto nell'Approccio Ecologico-Sociale di V. Hudolin e valido per la formazione continua.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- 2. PROGRAMMA DETTAGLIATO ORA PER ORA (A TUTTA PAGINA - 3 COLONNE AFFIANCATE SU PC) -->
  <section class="m-card" style="margin-top: 20px; border-radius: 20px; padding: 20px 22px; border: 1px solid rgba(255, 215, 0, 0.35); background: rgba(13, 17, 28, 0.96);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <span style="color: #d4af37;"><?=dx_icon('clock', '', 22)?></span>
        <h2 style="font-size: clamp(1.15rem, 3.5vw, 1.45rem); font-weight: 850; color: #ffffff; margin: 0;">
          Programma Orario Completo <span style="color: #d4af37;">(3 Giornate Esperienziali)</span>
        </h2>
      </div>
      <span style="font-size: 0.74rem; font-weight: 800; color: #10b981; background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); padding: 4px 12px; border-radius: 8px;">
        Venerdì 9 – Sabato 10 – Domenica 11 Ottobre 2026
      </span>
    </div>

    <!-- GRIGLIA A 3 COLONNE AFFIANCATE SU PC / STACK SU SMARTPHONE -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(310px, 1fr)); gap: 16px; align-items: start;">

      <!-- VENERDÌ 9 OTTOBRE -->
      <div class="m-schedule-day" style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 14px; margin-bottom: 0;">
        <div class="m-schedule-header" style="background: rgba(255,255,255,0.06); padding: 10px 12px; border-radius: 8px; margin-bottom: 12px;">
          <b style="color: #ffffff; font-size: 0.92rem;">Venerdì 9 Ottobre 2026</b>
          <span style="color: #d4af37; font-size: 0.8rem; font-weight: 750;">14:30 – 19:00</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">14:30 - 15:00</span>
          <span class="m-schedule-desc">Accoglienza partecipanti, registrazione e consegna cartellina didattica</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">15:00 - 15:30</span>
          <span class="m-schedule-desc">Saluti istituzionali e presentazione della scuola</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">15:30 - 16:30</span>
          <span class="m-schedule-desc">La comunicazione e l'approccio ecologico-sociale: concetti cardine</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">16:45 - 17:45</span>
          <span class="m-schedule-desc">La relazione d'aiuto: accoglienza, empatia e non giudizio</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">17:45 - 19:00</span>
          <span class="m-schedule-desc">Simulazioni e confronto in plenaria: dubbi e aspettative</span>
        </div>
      </div>

      <!-- SABATO 10 OTTOBRE -->
      <div class="m-schedule-day" style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 14px; margin-bottom: 0;">
        <div class="m-schedule-header" style="background: rgba(255,255,255,0.06); padding: 10px 12px; border-radius: 8px; margin-bottom: 12px;">
          <b style="color: #ffffff; font-size: 0.92rem;">Sabato 10 Ottobre 2026</b>
          <span style="color: #d4af37; font-size: 0.8rem; font-weight: 750;">09:00 – 19:00</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">09:00 - 10:00</span>
          <span class="m-schedule-desc">I rettangoli del comportamento: teoria e pratica</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">10:00 - 11:00</span>
          <span class="m-schedule-desc">Riconoscere le fragilità: abilità di ascolto e barriere</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">11:15 - 12:00</span>
          <span class="m-schedule-desc">Esperienza di ascolto attivo in coppia e condivisione</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">12:00 - 13:00</span>
          <span class="m-schedule-desc">Come facilitare la soluzione di un problema</span>
        </div>
        <div class="m-schedule-item" style="background: rgba(16,185,129,0.14); border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; padding: 8px;">
          <span class="m-schedule-time" style="color: #10b981;">13:00 - 14:00</span>
          <span class="m-schedule-desc"><b style="color: #10b981;">Pausa Pranzo Comunitario</b> (compreso nella quota di 10€)</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">14:00 - 15:00</span>
          <span class="m-schedule-desc">Esprimere bisogni e sentimenti in modo chiaro · Autorivelazione</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">15:00 - 16:00</span>
          <span class="m-schedule-desc">Quando non mi piacciono i comportamenti degli altri: confronto</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">16:15 - 17:15</span>
          <span class="m-schedule-desc">La resistenza e il cambio di marcia: trappole del potere</span>
        </div>
        <div class="m-schedule-item">
          <span class="m-schedule-time">17:15 - 18:00</span>
          <span class="m-schedule-desc">Esperienza: Io vinco, Tu vinci · Integrazione e compiti</span>
        </div>
      </div>

      <!-- DOMENICA 11 OTTOBRE -->
      <div class="m-schedule-day" style="background: rgba(18, 22, 34, 0.92); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; padding: 14px; margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <div class="m-schedule-header" style="background: rgba(255,255,255,0.06); padding: 10px 12px; border-radius: 8px; margin-bottom: 12px;">
            <b style="color: #ffffff; font-size: 0.92rem;">Domenica 11 Ottobre 2026</b>
            <span style="color: #d4af37; font-size: 0.8rem; font-weight: 750;">09:00 – 13:00</span>
          </div>
          <div class="m-schedule-item">
            <span class="m-schedule-time">09:00 - 10:00</span>
            <span class="m-schedule-desc">Fasi della soluzione democratica dei problemi ed esperienza</span>
          </div>
          <div class="m-schedule-item">
            <span class="m-schedule-time">10:00 - 11:00</span>
            <span class="m-schedule-desc">La collisione di valori e come affrontarla</span>
          </div>
          <div class="m-schedule-item">
            <span class="m-schedule-time">11:15 - 12:00</span>
            <span class="m-schedule-desc">Le persone significative: i miei Maestri</span>
          </div>
          <div class="m-schedule-item">
            <span class="m-schedule-time">12:00 - 12:45</span>
            <span class="m-schedule-desc">Esperienza in plenaria: cosa voglio migliorare? Cosa ho imparato?</span>
          </div>
          <div class="m-schedule-item" style="background: rgba(212,175,55,0.12); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; padding: 8px;">
            <span class="m-schedule-time" style="color: #d4af37;">12:45 - 13:00</span>
            <span class="m-schedule-desc"><b style="color: #d4af37;">Questionario ante-post & Consegna Attestati</b></span>
          </div>
        </div>

        <div style="margin-top: 14px; padding: 10px 12px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px;">
          <div style="font-weight: 800; color: #10b981; font-size: 0.78rem; margin-bottom: 2px;">Attestato Ufficiale di Partecipazione</div>
          <div style="font-size: 0.72rem; color: #cbd5e1; line-height: 1.4;">Valido per la formazione continua e nel circuito dei Club Alcologici Territoriali (CAT).</div>
        </div>
      </div>

    </div>
  </section>

  <!-- 3. DOCENTE E FORMATORE + SEDE & LOGISTICA (A TUTTA PAGINA - 2 COLONNE AFFIANCATE SU PC) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 16px; margin-top: 20px; align-items: stretch;">

    <!-- DOCENTE & FORMATORE -->
    <section class="m-card" style="border-radius: 20px; padding: 20px 22px; background: rgba(13, 17, 28, 0.96); border: 1px solid rgba(255, 255, 255, 0.12); display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 14px;">
          <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.4); display: grid; place-items: center; color: #d4af37; flex-shrink: 0;">
            <?=dx_icon('award', '', 28)?>
          </div>
          <div>
            <div style="font-size: 0.74rem; color: #d4af37; font-weight: 800; text-transform: uppercase;">Docente e Formatore Ufficiale</div>
            <h3 style="font-size: 1.12rem; color: #ffffff; margin: 2px 0 0; font-weight: 850;">Dott. Adelmo Di Salvatore</h3>
          </div>
        </div>
        <p style="font-size: 0.84rem; color: #cbd5e1; line-height: 1.5; margin: 0 0 12px; text-align: justify; text-justify: inter-word; hyphens: auto;">
          Psichiatra e Psicoterapeuta, formatore autorizzato nell'Approccio Centrato sulla Persona (Carl Rogers), Approccio Motivazionale (Miller e Rollnick), Programmazione NeuroLinguistica (Bandler e Grinder), Approccio Ecologico-Sociale (Vladimir Hudolin), con esperienza ultratrentennale come Servitore-Insegnante nei Club Alcologici Territoriali e di Ecologia Familiare e Sociale.
        </p>
      </div>
      <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px; font-size: 0.74rem; color: #94a3b8;">
        Formatore nazionale di reti di auto-mutuo aiuto e relazioni comunitarie.
      </div>
    </section>

    <!-- SEDE & LOGISTICA -->
    <section class="m-card" style="border-radius: 20px; padding: 20px 22px; background: rgba(13, 17, 28, 0.96); border: 1px solid rgba(255, 255, 255, 0.12); display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
          <span style="color: #d4af37;"><?=dx_icon('map-pin', '', 22)?></span>
          <h2 style="font-size: 1.12rem; font-weight: 850; color: #ffffff; margin: 0;">Sede & Logistica</h2>
        </div>
        <div style="font-size: 0.95rem; color: #ffffff; font-weight: 800; margin-bottom: 4px;">
          Oratorio San Francesco d'Assisi
        </div>
        <div style="font-size: 0.84rem; color: #cbd5e1; margin-bottom: 10px;">
          Vicolo San Francesco 1, Taglio di Po (RO) · Ampio parcheggio gratuito adiacente.
        </div>
        <p style="font-size: 0.82rem; color: #94a3b8; line-height: 1.45; margin: 0 0 12px; text-align: justify; text-justify: inter-word; hyphens: auto;">
          La sede è facilmente raggiungibile dalla SS 309 Romea. I locali climatizzati dell'Oratorio garantiscono spazi confortevoli sia per le sessioni plenarie sia per i laboratori esperienziali in piccoli gruppi. Piano terra accessibile senza barriere architettoniche.
        </p>
      </div>
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="https://maps.google.com/?q=Oratorio+San+Francesco+d'Assisi+Taglio+di+Po" target="_blank" rel="noopener" class="m-btn m-btn-outline" style="flex: 1; min-height: 42px; font-size: 0.84rem; border-color: rgba(212,175,55,0.4);">
          <?=dx_icon('map-pin', '', 15)?> Navigatore Google Maps
        </a>
      </div>
    </section>

  </div>

  <!-- ============================================================== -->
  <!-- GRIGLIA SPONSOR DELL'EVENTO A TUTTO SCHERMO (16:9 E 9:16)      -->
  <!-- ============================================================== -->
  <div style="margin-top: 36px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
    <?php require_once __DIR__ . '/templates/_sponsor_grid.php'; ?>
    <div class="text-center" style="font-size: 0.82rem; color: #94a3b8; margin-top: 14px;">
      <p style="margin: 0 0 6px;">Organizzazione: <b style="color: #ffffff;">ACAT Basso Polesine O.D.V.</b></p>
      <p style="margin: 0;">Referente Iscrizioni: <b style="color: #ffffff;">Grazia Nicosia</b> · Tel. WhatsApp <strong style="color: #25D366;">347 884 4271</strong></p>
    </div>
  </div>

</div> <!-- /.mobile-916-shell -->

<!-- STICKY BOTTOM ACTION BAR PER SMARTPHONE (9:16 SAFE-AREA) -->
<div class="m-sticky-bar">
  <div class="m-sticky-bar-inner">
    <a href="#prenotazione" class="m-btn m-btn-primary" style="flex: 1; min-height: 48px; font-size: 0.92rem; padding: 0 12px;">
      <?=dx_icon('check-circle', '', 16)?> Prenota Quota 10€
    </a>
    <a href="https://wa.me/393478844271?text=Ciao%20Grazia,%20sono%20[nome],%20mi%20interessa%20partecipare%20all'evento%20A%20Scuola%20di%20Comunicazione%20Resilienza%20a%20Taglio%20di%20Po." target="_blank" rel="noopener" class="m-btn m-btn-whatsapp" style="width: 48px; min-height: 48px; padding: 0; flex-shrink: 0;" title="Scrivi a Grazia su WhatsApp">
      <?=dx_icon('message-circle', '', 20)?>
    </a>
    <a href="https://chat.whatsapp.com/Bx6mGOuLBTmC2rxTPp4Gel" target="_blank" rel="noopener" class="m-btn" style="width: 48px; min-height: 48px; padding: 0; flex-shrink: 0; background: rgba(37,211,102,0.18); border: 1px solid #25D366; color: #25D366;" title="Gruppo WhatsApp Evento">
      <?=dx_icon('users', '', 18)?>
    </a>
  </div>
</div>

<!-- PAYPAL JS SDK LIVE -->
<?php if (!empty($paypalClientId)): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?=urlencode($paypalClientId)?>&currency=EUR&locale=it_IT&components=buttons"></script>
<?php endif; ?>

<script>
let currentBookingSic = '';

function switchMediaTab(tab, btn) {
  document.querySelectorAll('#mediaTabs .m-tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const img = document.getElementById('activeMediaImg');
  const cap = document.getElementById('mediaCaption');
  
  if (tab === 'locandina_fronte' || tab === 'locandina' || tab === 'depliant_fronte') {
    img.src = 'assets/img/events/evento-ottobre-taglio-di-po.jpeg';
    img.alt = 'Locandina Ufficiale Corso — Taglio di Po';
    cap.innerText = 'Locandina Ufficiale (Fronte)';
  } else if (tab === 'programma_retro' || tab === 'depliant_retro') {
    img.src = 'assets/img/events/depliant-programma-completo.jpeg';
    img.alt = 'Programma Completo Oratorio & Sessioni';
    cap.innerText = 'Programma Dettagliato (Retro)';
  } else if (tab === 'locandina_oratorio') {
    img.src = 'assets/img/events/locandina-ufficiale-oratorio.jpeg';
    img.alt = 'Locandina Oratorio San Francesco';
    cap.innerText = 'Locandina Oratorio S. Francesco';
  }
}

function copyPolygonAddress() {
  const addr = '0x3C320B3a0917fF44BF6551CDdee44402AFcF250C';
  navigator.clipboard.writeText(addr).then(() => {
    const lbl = document.getElementById('copyAddrLabel');
    lbl.innerText = 'Indirizzo Copiato!';
    setTimeout(() => { lbl.innerText = 'Copia Indirizzo Polygon'; }, 3000);
  }).catch(() => {
    prompt("Copia l'indirizzo:", addr);
  });
}

async function handleMobileBooking(e) {
  e.preventDefault();
  const form = document.getElementById('mobileBookingForm');
  const btn = document.getElementById('mb_submit_btn');
  const alertBox = document.getElementById('bookingAlertBox');
  const ppContainer = document.getElementById('paypalGatewayContainer');
  const usdtContainer = document.getElementById('usdtGatewayContainer');
  
  btn.disabled = true;
  btn.innerHTML = 'Elaborazione in corso...';
  alertBox.style.display = 'none';

  const formData = new FormData(form);
  const paymentMethod = formData.get('payment_method') || 'PAYPAL';

  try {
    const res = await fetch('api-event-booking.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();

    if (data.success || data.ok) {
      currentBookingSic = data.booking_sic;
      const isWait = !!data.is_waitlist;

      if (isWait) {
        alertBox.style.display = 'block';
        alertBox.style.background = 'rgba(239, 68, 68, 0.15)';
        alertBox.style.border = '1px solid #ef4444';
        alertBox.style.color = '#ffffff';

        let html = '<div style="font-weight: 850; font-size: 1.05rem; margin-bottom: 6px;">' +
                   "Iscrizione Inserita in Lista d'Attesa (Posizione #" + (data.waitlist_position || 1) + ")</div>";
        html += '<p style="margin: 0 0 8px;">Codice Prenotazione: <strong style="color: #d4af37;">' + currentBookingSic + '</strong></p>';
        html += '<p style="margin: 0 0 10px; font-size: 0.84rem;">' + data.message + '</p>';
        if (data.whatsapp_link) {
          html += '<a href="' + data.whatsapp_link + '" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp" style="min-height: 44px; font-size: 0.88rem; margin-bottom: 8px;">' +
                  '<?=dx_icon("message-circle", "", 16)?> Apri WhatsApp e Avvisa Grazia</a>';
        }
        html += '<a href="https://chat.whatsapp.com/Bx6mGOuLBTmC2rxTPp4Gel" target="_blank" rel="noopener" class="m-btn" style="min-height: 44px; font-size: 0.88rem; margin-bottom: 8px; background: rgba(37,211,102,0.18); border: 1px solid #25d366; color: #25d366; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">' +
                '<?=dx_icon("users", "", 16)?> Entra nel Gruppo WhatsApp Ufficiale</a>';
        alertBox.innerHTML = html;
        form.reset();
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      // CASO A: PAGAMENTO CARTA O PAYPAL
      if (paymentMethod === 'PAYPAL') {
        form.style.display = 'none';
        ppContainer.style.display = 'block';
        document.getElementById('pp_booking_code').innerText = currentBookingSic;
        renderPayPalButtons(currentBookingSic);
        ppContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      // CASO B: PAGAMENTO USDT (POLYGON)
      if (paymentMethod === 'USDT') {
        form.style.display = 'none';
        usdtContainer.style.display = 'block';
        document.getElementById('usdt_booking_code').innerText = currentBookingSic;
        usdtContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      // CASO C: SALDO IN SEDE ALL'ACCOGLIENZA
      alertBox.style.display = 'block';
      alertBox.style.background = 'rgba(16, 185, 129, 0.15)';
      alertBox.style.border = '1px solid #10b981';
      alertBox.style.color = '#ffffff';

      let html = '<div style="font-weight: 850; font-size: 1.05rem; margin-bottom: 6px;">Iscrizione Registrata con Successo!</div>';
      html += '<p style="margin: 0 0 8px;">Codice Prenotazione: <strong style="color: #d4af37;">' + currentBookingSic + '</strong></p>';
      html += '<p style="margin: 0 0 10px; font-size: 0.84rem;">La tua iscrizione è stata memorizzata nel database. Verserai la quota di 10,00 € (pranzo compreso) direttamente all\'accoglienza venerdì 9 ottobre.</p>';
      
      if (data.whatsapp_link) {
        html += '<a href="' + data.whatsapp_link + '" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp" style="min-height: 44px; font-size: 0.88rem; margin-bottom: 8px;">' +
                '<?=dx_icon("message-circle", "", 16)?> Apri WhatsApp e Avvisa Grazia</a>';
      }

      html += '<a href="https://chat.whatsapp.com/Bx6mGOuLBTmC2rxTPp4Gel" target="_blank" rel="noopener" class="m-btn" style="min-height: 44px; font-size: 0.88rem; margin-bottom: 8px; background: rgba(37,211,102,0.18); border: 1px solid #25d366; color: #25d366; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">' +
              '<?=dx_icon("users", "", 16)?> Entra nel Gruppo WhatsApp Ufficiale</a>';

      html += '<a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 42px; font-size: 0.84rem;">' +
              '<?=dx_icon("calendar", "", 14)?> Salva Promemoria su Calendario (.ics)</a>';

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
    btn.innerHTML = '<?=dx_icon("check-circle", "", 18)?> <span>ISCRIVITI & PROCEDI (10,00 €)</span>';
  }
}

function renderPayPalButtons(bookingSic) {
  const mount = document.getElementById('paypal-buttons-mount');
  mount.innerHTML = '';

  if (typeof paypal === 'undefined') {
    mount.innerHTML = '<div style="color:#ef4444; padding:10px;">SDK PayPal temporaneamente non disponibile. Ricarica la pagina o scegli USDT / Saldo in Sede.</div>';
    return;
  }

  paypal.Buttons({
    style: {
      layout: 'vertical',
      color: 'gold',
      shape: 'rect',
      label: 'pay'
    },
    createOrder: async function() {
      const res = await fetch('api-event-booking.php?action=create_paypal_order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ booking_sic: bookingSic })
      });
      const orderData = await res.json();
      if (!orderData.success || !orderData.orderID) {
        throw new Error(orderData.error || 'Errore creazione ordine PayPal.');
      }
      return orderData.orderID;
    },
    onApprove: async function(data) {
      mount.innerHTML = '<div style="color:#d4af37; padding:14px; font-weight:750;">Conferma pagamento in corso...</div>';
      const res = await fetch('api-event-booking.php?action=capture_paypal_order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ orderID: data.orderID, booking_sic: bookingSic })
      });
      const captureData = await res.json();
      if (captureData.success) {
        const ppContainer = document.getElementById('paypalGatewayContainer');
        ppContainer.style.background = 'rgba(16, 185, 129, 0.15)';
        ppContainer.style.borderColor = '#10b981';
        
        let html = '<div style="font-weight: 850; font-size: 1.15rem; color: #10b981; margin-bottom: 8px;">Quota Pagata con Successo!</div>';
        html += '<p style="color: #ffffff; margin: 0 0 8px;">Codice Iscrizione: <strong style="color: #d4af37;">' + bookingSic + '</strong></p>';
        html += '<p style="color: #cbd5e1; font-size: 0.84rem; margin: 0 0 14px;">Abbiamo inviato la ricevuta e i dettagli al tuo indirizzo email. Il tuo posto a Taglio di Po è confermato al 100%.</p>';
        
        if (captureData.whatsapp_link) {
          html += '<a href="' + captureData.whatsapp_link + '" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp" style="min-height: 44px; font-size: 0.88rem; margin-bottom: 8px;">' +
                  '<?=dx_icon("message-circle", "", 16)?> Apri WhatsApp e Avvisa Grazia</a>';
        }
        
        html += '<a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 42px; font-size: 0.84rem;">' +
                '<?=dx_icon("calendar", "", 14)?> Salva Promemoria su Calendario (.ics)</a>';
        
        ppContainer.innerHTML = html;
        ppContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        alert('Errore durante la cattura del pagamento: ' + (captureData.error || 'Riprova'));
      }
    },
    onError: function(err) {
      alert('Si è verificato un errore con PayPal: ' + err);
    }
  }).render('#paypal-buttons-mount');
}

async function submitUsdtTx() {
  const hash = document.getElementById('usdt_tx_hash').value.trim();
  const btn = document.getElementById('usdt_confirm_btn');
  const usdtContainer = document.getElementById('usdtGatewayContainer');

  if (!hash || hash.length < 10) {
    alert('Inserisci un Transaction Hash valido della rete Polygon.');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = 'Salvataggio in corso...';

  try {
    const res = await fetch('api-event-booking.php?action=confirm_usdt_payment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ booking_sic: currentBookingSic, tx_hash: hash })
    });
    const data = await res.json();

    if (data.success) {
      usdtContainer.style.background = 'rgba(16, 185, 129, 0.15)';
      usdtContainer.style.borderColor = '#10b981';

      let html = '<div style="font-weight: 850; font-size: 1.15rem; color: #10b981; margin-bottom: 8px;">Notifica USDT Ricevuta!</div>';
      html += '<p style="color: #ffffff; margin: 0 0 8px;">Codice Iscrizione: <strong style="color: #d4af37;">' + currentBookingSic + '</strong></p>';
      html += '<p style="color: #cbd5e1; font-size: 0.84rem; margin: 0 0 14px;">La transazione è stata salvata nel database ed è in fase di verifica on-chain. Il tuo posto in aula è riservato.</p>';

      if (data.whatsapp_link) {
        html += '<a href="' + data.whatsapp_link + '" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp" style="min-height: 44px; font-size: 0.88rem; margin-bottom: 8px;">' +
                '<?=dx_icon("message-circle", "", 16)?> Invia TX Hash a Grazia su WhatsApp</a>';
      }

      html += '<a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 42px; font-size: 0.84rem;">' +
              '<?=dx_icon("calendar", "", 14)?> Salva Promemoria su Calendario (.ics)</a>';

      usdtContainer.innerHTML = html;
      usdtContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
      alert(data.error || 'Errore salvataggio TX Hash.');
    }
  } catch (err) {
    alert('Errore di connessione durante la verifica USDT.');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<?=dx_icon("send", "", 14)?> Conferma Notifica USDT';
  }
}
</script>
</div> <!-- /.event-cosmic-landing -->

<?php require '_footer.php';?>