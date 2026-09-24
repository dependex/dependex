<?php 
declare(strict_types=1);
require_once 'bootstrap.php';
$u = current_user();

// Sincronizza ed estrae tutti gli eventi attivi (Taglio di Po, Porto Tolle e rete nazionale)
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

$pageTitle = 'Eventi ACAT Basso Polesine & Hub Nazionale Dipendenze';
$metaDesc = '9-11 Ottobre Taglio di Po e 24 Ottobre Porto Tolle (SAT 2° Modulo Hudolin). Calendario ed iscrizioni per i corsi ACAT e tutti gli eventi CAT e comunitari in Italia.';
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
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 12px;">
    <span class="m-badge m-badge-gold">
      <?=dx_icon('activity', '', 12)?> ACAT BASSO POLESINE · TAGLIO DI PO
    </span>
    <span class="m-badge <?=!$isFullTaglio ? 'm-badge-green' : 'm-badge-red'?>">
      <?=dx_icon('users', '', 12)?> <?=!$isFullTaglio ? "$seatsRemainingTaglio Posti Rimasti" : "30/30 Esauriti (Waitlist)"?>
    </span>
  </div>

  <!-- HERO CARD VERTICALE 9:16 -->
  <article class="m-card m-card-gold-glow text-center">
    
    <!-- Intestazione Istituzionale Compatta -->
    <div style="font-size: 0.74rem; font-weight: 800; color: #d4af37; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">
      A.C.A.T. Basso Polesine · Metodo Hudolin O.D.V.
    </div>
    
    <h1 style="font-family: var(--font-serif); font-size: clamp(1.45rem, 5vw, 1.85rem); color: #ffffff; line-height: 1.25; margin: 4px 0 10px; font-weight: 900;">
      A Scuola di Comunicazione e Resilienza
    </h1>

    <div style="display: inline-block; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.35); border-radius: 999px; padding: 3px 12px; font-size: 0.78rem; font-weight: 800; color: #fff2b2; margin-bottom: 12px;">
      1° Livello · Corso Esperienziale
    </div>

    <!-- SOTTOTITOLO BASATO SUL RISULTATO -->
    <div style="background: rgba(20, 24, 35, 0.95); border-left: 4px solid #d4af37; border-radius: 12px; padding: 12px 14px; text-align: left; margin-bottom: 14px;">
      <p style="font-size: 0.98rem; font-weight: 800; color: #ffffff; margin: 0 0 4px; line-height: 1.4;">
        "Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri."
      </p>
      <p style="font-size: 0.82rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
        Rivolto a chi vive in famiglia una situazione di dipendenza, operatori, volontari e membri dei Club. Strumenti pratici da usare già dal lunedì.
      </p>
    </div>

    <!-- LOCANDINA VERTICALE (ASPECT RATIO SMARTPHONE) -->
    <div class="m-poster-box">
      <a href="evento-ottobre-taglio-di-po.php" title="Apri locandina e pagina dedicata">
        <img src="assets/img/events/evento-ottobre-taglio-di-po.jpeg" alt="Locandina Ufficiale Taglio di Po" style="width: 100%; height: auto; display: block;">
      </a>
      <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); padding: 4px 8px; border-radius: 8px; font-size: 0.72rem; color: #fff; border: 1px solid rgba(255,255,255,0.2);">
        <?=dx_icon('zoom-in', '', 12)?> Tocca per dettagli
      </div>
    </div>

    <!-- DATI CHIAVE A COLPO D'OCCHIO (9:16 GRID) -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; text-align: left;">
      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quando</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">9-10-11 Ott. 2026</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Ven 14:30 – Dom 13:00</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Dove</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Taglio di Po (RO)</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Oratorio S. Francesco</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quota Unica</div>
        <div style="color: #10b981; font-weight: 900; font-size: 1.05rem; margin-top: 2px;">10,00 €</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Pranzo sabato compreso</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Formatore</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">A. Di Salvatore</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Psichiatra & Terapeuta</div>
      </div>
    </div>

    <!-- INDICATORE CAPIENZA 30 POSTI -->
    <div style="background: rgba(14, 17, 24, 0.9); border: 1px solid rgba(212,175,55,0.25); border-radius: 14px; padding: 10px 12px; margin-bottom: 14px; text-align: left;">
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 750;">
        <span style="color: #cbd5e1;">Capienza Aula (Numero Chiuso):</span>
        <b style="color: <?=!$isFullTaglio ? '#10b981' : '#ef4444'?>;"><?=$totalBookedTaglio?> / <?=$capacityTaglio?> Iscritti</b>
      </div>
      <div class="m-progress-bar">
        <div class="m-progress-fill" style="width: <?=$percentBookedTaglio?>%;"></div>
      </div>
      <div style="font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between;">
        <span>Chiusura: 1° Ottobre 2026</span>
        <span><?=!$isFullTaglio ? "Ancora $seatsRemainingTaglio posti" : "Lista d'attesa attiva"?></span>
      </div>
    </div>

    <!-- BOTTONI DI AZIONE TOUCH (FULL-WIDTH 9:16) -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
      <a href="evento-ottobre-taglio-di-po.php" class="m-btn m-btn-primary">
        <?=dx_icon('check-circle', '', 18)?>
        <span>Prenota Posto (10 €)</span>
      </a>

      <a href="mailto:info@dependex.support?subject=Richiesta%20informazioni%20evento%20Taglio%20di%20Po" class="m-btn m-btn-outline" style="min-height: 48px;">
        <?=dx_icon('mail', 'text-neon-cyan', 18)?>
        <span>Richiedi Informazioni</span>
      </a>

      <a href="https://chat.whatsapp.com/Bx6mGOuLBTmC2rxTPp4Gel" target="_blank" rel="noopener" class="m-btn" style="background: rgba(37, 211, 102, 0.18); border: 1px solid #25D366; color: #25D366; font-weight: 800; min-height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
        <?=dx_icon('users', '', 18)?>
        <span>Gruppo WhatsApp</span>
      </a>

      <a href="event-ics.php?event=<?=urlencode($sicTaglio)?>" download class="m-btn m-btn-outline" style="min-height: 44px; font-size: 0.88rem;">
        <?=dx_icon('calendar', '', 16)?>
        <span>Salva nel Calendario</span>
      </a>
    </div>

  </article>

  <!-- SEZIONE: COSA IMPARI (5 PUNTI CONCRETI) -->
  <section class="m-card">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
      <span style="color: #d4af37;"><?=dx_icon('award', '', 18)?></span>
      <h2 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">Cosa Saprai Fare dal Lunedì</h2>
    </div>
    
    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 0.86rem; color: #cbd5e1;">
      <li style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900; margin-top: 2px;">✓</span>
        <div><b>Disinnescare le provocazioni:</b> comunicare senza alzare la voce o farsi trascinare nel conflitto.</div>
      </li>
      <li style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900; margin-top: 2px;">✓</span>
        <div><b>Porre confini sani:</b> non farti carico delle scelte e delle ricadute altrui conservando la tua serenità.</div>
      </li>
      <li style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900; margin-top: 2px;">✓</span>
        <div><b>Ascolto attivo profondo:</b> capire davvero i bisogni inespressi senza dare giudizi prematuri.</div>
      </li>
      <li style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900; margin-top: 2px;">✓</span>
        <div><b>Metodo decisionale democratico:</b> trovare soluzioni condivise per i conflitti in famiglia e in club.</div>
      </li>
      <li style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900; margin-top: 2px;">✓</span>
        <div><b>Attestato ufficiale rilasciato:</b> riconosciuto nella rete dei Club Alcologici Territoriali.</div>
      </li>
    </ul>
  </section>

  <!-- PROGRAMMA SINTETICO 3 GIORNI -->
  <section class="m-card">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
      <span style="color: #d4af37;"><?=dx_icon('clock', '', 18)?></span>
      <h2 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">Programma in Sintesi</h2>
    </div>

    <div class="m-schedule-day">
      <div class="m-schedule-header">
        <b style="color: #ffffff; font-size: 0.86rem;">Venerdì 9 Ottobre</b>
        <span style="color: #d4af37; font-size: 0.78rem; font-weight: 750;">14:30 – 19:00</span>
      </div>
      <div style="font-size: 0.82rem; color: #cbd5e1; padding-left: 6px;">
        Accoglienza, presentazione del metodo "Le Persone Efficaci", motivazioni e prime esperienze pratiche.
      </div>
    </div>

    <div class="m-schedule-day">
      <div class="m-schedule-header">
        <b style="color: #ffffff; font-size: 0.86rem;">Sabato 10 Ottobre</b>
        <span style="color: #d4af37; font-size: 0.78rem; font-weight: 750;">09:00 – 19:00</span>
      </div>
      <div style="font-size: 0.82rem; color: #cbd5e1; padding-left: 6px;">
        Ascolto attivo in coppia, role-play, <b>pranzo comunitario compreso nella quota (13:00)</b>, risoluzione democratica dei problemi.
      </div>
    </div>

    <div class="m-schedule-day" style="margin-bottom: 0;">
      <div class="m-schedule-header">
        <b style="color: #ffffff; font-size: 0.86rem;">Domenica 11 Ottobre</b>
        <span style="color: #d4af37; font-size: 0.78rem; font-weight: 750;">09:00 – 13:00</span>
      </div>
      <div style="font-size: 0.82rem; color: #cbd5e1; padding-left: 6px;">
        Collisione di valori, plenaria "Cosa voglio migliorare", autovalutazione ante-post e consegna attestati.
      </div>
    </div>
  </section>

  <!-- FORMATORE & SEDE -->
  <section class="m-card">
    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 10px;">
      <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.35); display: grid; place-items: center; color: #d4af37;">
        <?=dx_icon('user', '', 22)?>
      </div>
      <div>
        <div style="font-size: 0.72rem; color: #d4af37; font-weight: 800; text-transform: uppercase;">Docente e Formatore</div>
        <h3 style="font-size: 0.98rem; color: #ffffff; margin: 2px 0 0; font-weight: 850;">Dott. Adelmo Di Salvatore</h3>
      </div>
    </div>
    <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 10px;">
      Psichiatra, Psicoterapeuta, formatore autorizzato Approccio Centrato sulla Persona, PNL e Servitore-Insegnante con esperienza ultratrentennale nei Club Alcologici Territoriali.
    </p>
    <div style="border-top: 1px solid rgba(255,255,255,0.06); padding-top: 10px; font-size: 0.82rem; color: #94a3b8;">
      <?=dx_icon('map-pin', '', 14)?> <b style="color: #ffffff;">Sede:</b> Oratorio San Francesco d'Assisi, Vicolo San Francesco 1, Taglio di Po (RO).
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- EVENTO UFFICIALE FLAGSHIP #2: PORTO TOLLE (24 OTTOBRE 2026)    -->
  <!-- ============================================================== -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin: 30px 0 12px;">
    <span class="m-badge m-badge-gold">
      <?=dx_icon('activity', '', 12)?> ACAT BASSO POLESINE · PORTO TOLLE
    </span>
    <span class="m-badge <?=!$isFullPT ? 'm-badge-green' : 'm-badge-red'?>">
      <?=dx_icon('users', '', 12)?> <?=!$isFullPT ? "$seatsRemainingPT Posti Rimasti (Gratuito)" : "40/40 Esauriti (Waitlist)"?>
    </span>
  </div>

  <article class="m-card m-card-gold-glow text-center" id="evento-porto-tolle">
    
    <!-- Intestazione Istituzionale Compatta -->
    <div style="font-size: 0.74rem; font-weight: 800; color: #d4af37; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px;">
      A.C.A.T. Basso Polesine · Metodologia Hudolin
    </div>
    
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 5vw, 1.85rem); color: #ffffff; line-height: 1.25; margin: 4px 0 10px; font-weight: 900;">
      S.A.T. di 2° Modulo: Coraggio, Gratitudine, Vita
    </h2>

    <div style="display: inline-block; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.35); border-radius: 999px; padding: 3px 12px; font-size: 0.78rem; font-weight: 800; color: #6ee7b7; margin-bottom: 12px;">
      Scuola Alcologica Territoriale di Aggiornamento · Partecipazione Gratuita
    </div>

    <!-- SOTTOTITOLO BASATO SUL TEMA -->
    <div style="background: rgba(20, 24, 35, 0.95); border-left: 4px solid var(--neon-gold); border-radius: 12px; padding: 12px 14px; text-align: left; margin-bottom: 14px;">
      <p style="font-size: 0.98rem; font-weight: 800; color: #ffffff; margin: 0 0 4px; line-height: 1.4;">
        «La Famiglia e l'Approccio Sistemico nella Metodologia Hudolin»
      </p>
      <p style="font-size: 0.82rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
        Aggiornamento esperienziale per Famiglie e Servitori-Insegnanti di Club. Confronto in plenaria, gruppi autogestiti, testimonianze e consegna attestati di partecipazione.
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

    <!-- DATI CHIAVE A COLPO D'OCCHIO (9:16 GRID) -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; text-align: left;">
      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quando</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Sabato 24 Ott. 2026</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Ore 09:00 – 16:30</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Dove</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Porto Tolle (RO)</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Centro "Un ponte per"</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Quota</div>
        <div style="color: #10b981; font-weight: 900; font-size: 1.05rem; margin-top: 2px;">GRATUITA</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Iscrizione obbligatoria</div>
      </div>

      <div style="background: rgba(22, 25, 36, 0.8); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 10px;">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;">Relatrice</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem; margin-top: 2px;">Grazia Nicosia</div>
        <div style="color: #94a3b8; font-size: 0.74rem;">Servitrice Insegnante</div>
      </div>
    </div>

    <!-- INDICATORE CAPIENZA 40 POSTI -->
    <div style="background: rgba(14, 17, 24, 0.9); border: 1px solid rgba(212,175,55,0.25); border-radius: 14px; padding: 10px 12px; margin-bottom: 14px; text-align: left;">
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 750;">
        <span style="color: #cbd5e1;">Posti Riservati per Famiglie e Club:</span>
        <b style="color: <?=!$isFullPT ? '#10b981' : '#ef4444'?>;"><?=$totalBookedPT?> / <?=$capacityPT?> Iscritti</b>
      </div>
      <div class="m-progress-bar">
        <div class="m-progress-fill" style="width: <?=$percentBookedPT?>%;"></div>
      </div>
      <div style="font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between;">
        <span>Scadenza: 15 Settembre 2026</span>
        <span><?=!$isFullPT ? "Ancora $seatsRemainingPT posti disponibili" : "Lista d'attesa attiva"?></span>
      </div>
    </div>

    <!-- BOTTONI DI AZIONE TOUCH (FULL-WIDTH 9:16) -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
      <a href="evento-ottobre-porto-tolle.php#iscrizione-gratuita" class="m-btn m-btn-primary">
        <?=dx_icon('check-circle', '', 18)?>
        <span>Iscriviti Online (Gratuito)</span>
      </a>

      <a href="evento-ottobre-porto-tolle.php" class="m-btn m-btn-outline" style="min-height: 48px;">
        <?=dx_icon('award', 'text-neon-gold', 18)?>
        <span>Scheda Completa & Programma Orario</span>
      </a>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
        <a href="tel:3478844271" class="m-btn m-btn-outline" style="min-height: 48px; font-size: 0.84rem;">
          <?=dx_icon('phone', 'text-neon-cyan', 16)?>
          <span>347 8844271</span>
        </a>
        <a href="mailto:nicogra1959@gmail.com?subject=Iscrizione%20SAT%20Porto%20Tolle" class="m-btn m-btn-outline" style="min-height: 48px; font-size: 0.84rem;">
          <?=dx_icon('mail', '', 16)?>
          <span>Email Docente</span>
        </a>
      </div>

      <a href="event-ics.php?event=<?=urlencode($sicPT)?>" download class="m-btn m-btn-outline" style="min-height: 44px; font-size: 0.88rem;">
        <?=dx_icon('calendar', '', 16)?>
        <span>Salva nel Calendario (.ics)</span>
      </a>
    </div>

  </article>

  <!-- SEZIONE: RELATRICE GRAZIA NICOSIA -->
  <section class="m-card">
    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 10px;">
      <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.35); display: grid; place-items: center; color: #10b981;">
        <?=dx_icon('users', '', 22)?>
      </div>
      <div>
        <div style="font-size: 0.72rem; color: #10b981; font-weight: 800; text-transform: uppercase;">Docente e Relatrice</div>
        <h3 style="font-size: 0.98rem; color: #ffffff; margin: 2px 0 0; font-weight: 850;">Servitrice-Insegnante Grazia Nicosia</h3>
      </div>
    </div>
    <p style="font-size: 0.82rem; color: #cbd5e1; line-height: 1.45; margin: 0 0 10px;">
      Esperta formatrice nei Club Alcologici Territoriali secondo l'Approccio Ecologico-Sociale di Vladimir Hudolin. Guida le famiglie e i servitori-insegnanti nell'elaborazione del vissuto, valorizzando la ricchezza delle testimonianze comunitarie.
    </p>
    <div style="border-top: 1px solid rgba(255,255,255,0.06); padding-top: 10px; font-size: 0.82rem; color: #94a3b8;">
      <?=dx_icon('map-pin', '', 14)?> <b style="color: #ffffff;">Sede:</b> Centro aggregativo "Un ponte per", Via G. Matteotti 248, Porto Tolle (RO) 45018.
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- HUB NAZIONALE DIPENDENZE: TUTTI GLI EVENTI D'ITALIA            -->
  <!-- ============================================================== -->
  <?php 
  $nationalEventsList = array_values(array_filter($events, function($ev) {
      return !in_array($ev['sic_id'], ['SIC-EVT-ACAT-BP-2026-COMM', 'SIC-EVT-ACAT-BP-2026-SAT2'], true);
  }));
  if (!empty($nationalEventsList)): 
  ?>
  <section class="m-card" style="border-color: rgba(56, 189, 248, 0.35); background: rgba(11, 15, 25, 0.95); margin-top: 14px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
      <div>
        <span class="m-badge m-badge-cyan" style="font-size: 0.7rem; letter-spacing: 0.06em;">
          <?=dx_icon('globe', '', 12)?> HUB NAZIONALE DIPENDENZE
        </span>
        <h2 style="font-size: 1.15rem; font-weight: 850; color: #ffffff; margin: 4px 0 2px;">
          Eventi, Congressi & Incontri in Tutta Italia
        </h2>
      </div>
      <span style="font-size: 0.72rem; color: #38bdf8; font-weight: 800; background: rgba(56,189,248,0.1); padding: 3px 8px; border-radius: 6px;">
        <?=count($nationalEventsList)?> Iniziative Attive
      </span>
    </div>

    <p style="font-size: 0.8rem; color: #94a3b8; line-height: 1.45; margin: 0 0 14px;">
      DEPENDEX aggrega le iniziative di prevenzione, cura e auto-mutuo aiuto di ACAT/AICAT, Ser.D, Comunità storiche (San Patrignano, CeIS, Gruppo Abele, Comunità Incontro) e gruppi 12 Passi (A.A., N.A., Giocatori Anonimi):
    </p>

    <div style="display: flex; flex-direction: column; gap: 10px;">
      <?php foreach ($nationalEventsList as $nev): 
        $typeColor = match($nev['type']) {
          'CONGRESSO' => '#3b82f6',
          'SEMINARIO' => '#8b5cf6',
          'INTERCLUB' => '#10b981',
          'ASSEMBLEA' => '#f59e0b',
          default => '#06b6d4'
        };
        $formattedDate = date('d M Y', strtotime($nev['starts_at']));
      ?>
        <article style="background: rgba(20, 25, 38, 0.85); border: 1px solid rgba(255,255,255,0.08); border-left: 3px solid <?=$typeColor?>; border-radius: 12px; padding: 12px; transition: all 0.2s ease;">
          <div style="display: flex; justify-content: space-between; align-items: center; gap: 6px; margin-bottom: 4px;">
            <span style="font-size: 0.68rem; font-weight: 800; color: <?=$typeColor?>; background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px;">
              <?=htmlspecialchars($nev['type'], ENT_QUOTES, 'UTF-8')?>
            </span>
            <span style="font-size: 0.74rem; font-weight: 750; color: #cbd5e1; display: inline-flex; align-items: center; gap: 4px;">
              <?=dx_icon('calendar', '', 12)?> <?=$formattedDate?>
            </span>
          </div>

          <h3 style="font-size: 0.92rem; font-weight: 850; color: #ffffff; line-height: 1.3; margin: 0 0 4px;">
            <?=htmlspecialchars($nev['title'], ENT_QUOTES, 'UTF-8')?>
          </h3>

          <div style="font-size: 0.74rem; color: #d4af37; font-weight: 750; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
            <?=dx_icon('map-pin', '', 12)?>
            <span><?=htmlspecialchars($nev['comune'] ?? '', ENT_QUOTES, 'UTF-8')?> (<?=htmlspecialchars($nev['venue'] ?? '', ENT_QUOTES, 'UTF-8')?>)</span>
          </div>

          <p style="font-size: 0.76rem; color: #94a3b8; line-height: 1.4; margin: 0 0 8px;">
            <?=htmlspecialchars($nev['description'] ?? '', ENT_QUOTES, 'UTF-8')?>
          </p>

          <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.06); pt: 6px; padding-top: 6px; font-size: 0.72rem;">
            <span style="color: #64748b; font-weight: 600;">
              <?=htmlspecialchars($nev['organizer'] ?? 'Organizzazione Nazionale', ENT_QUOTES, 'UTF-8')?>
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
  </section>
  <?php endif; ?>
  <!-- BANNER ADVISOR: INIZIATIVA RESIDENZIALE CIURMA (PROGETTO MIRCO PREGNOLATO) -->
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

  <!-- GRIGLIA UFFICIALE DEI 28 SPONSOR & ASSET DELL'ECOSISTEMA -->
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

<?php require '_footer.php';?>