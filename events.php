<?php 
declare(strict_types=1);
require_once 'bootstrap.php';
$u = require_login();

// Sincronizza ed estrae l'evento unico e ufficiale di Taglio di Po
$events = EventSyncService::syncAndGetActiveEvents();
$event = !empty($events) ? $events[0] : null;

if (!$event) {
    $st = db()->prepare('SELECT * FROM events WHERE sic_id = "SIC-EVT-ACAT-BP-2026-COMM" LIMIT 1');
    $st->execute();
    $event = $st->fetch(PDO::FETCH_ASSOC);
}

// Conteggi con auto-riparazione schema
$sic = $event['sic_id'] ?? 'SIC-EVT-ACAT-BP-2026-COMM';
$totalBooked = 0;
try {
    ensure_core_schema(db());
    $countStmt = db()->prepare("
        SELECT (
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = ? AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
            (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = ? AND eb.status = 'CONFIRMED')
        ) as total_booked
    ");
    $countStmt->execute([$sic, $sic]);
    $totalBooked = (int)$countStmt->fetchColumn();
} catch (Throwable $e) {
    $totalBooked = 0;
}

$capacity = (int)($event['capacity'] ?? 30);
$seatsRemaining = max(0, $capacity - $totalBooked);
$isFull = ($seatsRemaining <= 0);
$percentBooked = $capacity > 0 ? min(100, round(($totalBooked / $capacity) * 100)) : 0;

$pageTitle = 'Eventi & Calendario Rete · DEPENDEX';
$metaDesc = 'Calendario vivo con l\'evento ufficiale di ACAT Basso Polesine a Taglio di Po.';
require '_header.php';
?>

<div class="mobile-916-shell">

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
    <span class="m-badge m-badge-gold">
      <?=dx_icon('activity', '', 12)?> AREA MEMBRI · EVENTI
    </span>
    <span class="m-badge <?=!$isFull ? 'm-badge-green' : 'm-badge-red'?>">
      <?=!$isFull ? "$seatsRemaining Posti Rimasti" : "Waitlist Attiva"?>
    </span>
  </div>

  <article class="m-card m-card-gold-glow text-center">
    <div style="font-size: 0.72rem; font-weight: 800; color: #d4af37; text-transform: uppercase; margin-bottom: 4px;">
      ACAT Basso Polesine · Taglio di Po
    </div>
    
    <h1 style="font-family: var(--font-serif); font-size: clamp(1.45rem, 5vw, 1.85rem); color: #ffffff; line-height: 1.25; margin: 4px 0 10px; font-weight: 900;">
      A Scuola di Comunicazione e Resilienza
    </h1>

    <div class="m-poster-box">
      <a href="event-detail.php?event=<?=urlencode($sic)?>">
        <img src="assets/img/events/evento-ottobre-taglio-di-po.jpeg" alt="Locandina Taglio di Po">
      </a>
    </div>

    <!-- DATI RAPIDI -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin: 12px 0; text-align: left;">
      <div style="background: rgba(22, 25, 36, 0.85); border-radius: 12px; padding: 10px; border: 1px solid rgba(255,255,255,0.08);">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800;">QUANDO</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem;">9-10-11 Ott. 2026</div>
      </div>
      <div style="background: rgba(22, 25, 36, 0.85); border-radius: 12px; padding: 10px; border: 1px solid rgba(255,255,255,0.08);">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800;">DOVE</div>
        <div style="color: #ffffff; font-weight: 850; font-size: 0.88rem;">Taglio di Po (RO)</div>
      </div>
      <div style="background: rgba(22, 25, 36, 0.85); border-radius: 12px; padding: 10px; border: 1px solid rgba(255,255,255,0.08);">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800;">QUOTA</div>
        <div style="color: #10b981; font-weight: 900; font-size: 1.05rem;">10,00 € <small style="color: #cbd5e1; font-size: 0.7rem;">(pranzo inc.)</small></div>
      </div>
      <div style="background: rgba(22, 25, 36, 0.85); border-radius: 12px; padding: 10px; border: 1px solid rgba(255,255,255,0.08);">
        <div style="color: #d4af37; font-size: 0.72rem; font-weight: 800;">REWARD</div>
        <div style="color: #d4af37; font-weight: 900; font-size: 1.05rem;">+100 DRX</div>
      </div>
    </div>

    <!-- POSTI OCCUPATI -->
    <div style="background: rgba(14, 17, 24, 0.95); border: 1px solid rgba(212,175,55,0.3); border-radius: 14px; padding: 10px 12px; margin-bottom: 12px; text-align: left;">
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 750;">
        <span style="color: #cbd5e1;">Posti Aula:</span>
        <b style="color: <?=!$isFull ? '#10b981' : '#ef4444'?>;"><?=$totalBooked?> / <?=$capacity?></b>
      </div>
      <div class="m-progress-bar">
        <div class="m-progress-fill" style="width: <?=$percentBooked?>%;"></div>
      </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 8px;">
      <a href="event-detail.php?event=<?=urlencode($sic)?>" class="m-btn m-btn-primary">
        <?=dx_icon('check-circle', '', 18)?>
        <span>DETTAGLI COMPLETI & PRENOTAZIONE</span>
      </a>

      <a href="https://wa.me/393478844271?text=Ciao%20Grazia,%20sono%20[nome],%20mi%20interessa%20partecipare%20all'evento%20A%20Scuola%20di%20Comunicazione%20Resilienza%20a%20Taglio%20di%20Po." target="_blank" rel="noopener" class="m-btn m-btn-whatsapp">
        <?=dx_icon('message-circle', '', 18)?>
        <span>Scrivi a Grazia su WhatsApp (+39 347 884 4271)</span>
      </a>

      <a href="https://chat.whatsapp.com/Bx6mGOuLBTmC2rxTPp4Gel" target="_blank" rel="noopener" class="m-btn" style="background: rgba(37, 211, 102, 0.18); border: 1px solid #25D366; color: #25D366; font-weight: 800; min-height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
        <?=dx_icon('users', '', 18)?>
        <span>Entra nel Gruppo WhatsApp Ufficiale</span>
      </a>
    </div>

  </article>

  <!-- GRIGLIA UFFICIALE DEI 28 SPONSOR & ASSET DELL'ECOSISTEMA -->
  <?php require_once __DIR__ . '/templates/_sponsor_grid.php'; ?>

</div>

<?php require '_footer.php';?>