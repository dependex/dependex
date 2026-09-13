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

if (!$e) {
    $stFallback = $pdo->prepare('SELECT * FROM events WHERE sic_id = "SIC-EVT-ACAT-BP-2026-COMM" LIMIT 1');
    $stFallback->execute();
    $e = $stFallback->fetch(PDO::FETCH_ASSOC);
    if (!$e) {
        http_response_code(404);
        exit('Evento non trovato.');
    }
    $sic = $e['sic_id'];
}

// Conteggio iscritti confermati
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

$capacity = (int)($e['capacity'] ?? 30);
$seatsRemaining = max(0, $capacity - $totalBooked);
$isFull = ($seatsRemaining <= 0);
$percentBooked = $capacity > 0 ? min(100, round(($totalBooked / $capacity) * 100)) : 0;

$pageTitle = 'A Scuola di Comunicazione e Resilienza · Taglio di Po · ACAT';
$metaDesc = 'Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri. 9-11 Ottobre 2026, Taglio di Po. Iscrizione online in 10 secondi.';
require '_header.php';
?>

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

  <!-- HERO EVENTO 9:16 -->
  <article class="m-card m-card-gold-glow text-center">
    
    <div style="font-size: 0.72rem; font-weight: 800; color: #d4af37; text-transform: uppercase; letter-spacing: 0.06em;">
      Corso Esperienziale 1° Livello · Metodo Hudolin
    </div>

    <h1 style="font-family: var(--font-serif); font-size: clamp(1.5rem, 5.5vw, 1.95rem); color: #ffffff; line-height: 1.25; margin: 6px 0 10px; font-weight: 900;">
      A Scuola di Comunicazione e Resilienza
    </h1>

    <!-- SOTTOTITOLO ORIENTATO AL RISULTATO -->
    <div style="background: rgba(20, 24, 35, 0.95); border-left: 4px solid #d4af37; border-radius: 12px; padding: 12px; text-align: left; margin: 10px 0 14px;">
      <p style="font-size: 1rem; font-weight: 850; color: #ffffff; margin: 0 0 4px; line-height: 1.35;">
        "Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri."
      </p>
      <p style="font-size: 0.82rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
        Corso rivolto a <strong>chi vive in famiglia una situazione di dipendenza</strong>, oltre a operatori, volontari e membri impegnati nei Club.
      </p>
    </div>

    <!-- TAB SWITCHER LOCANDINE (HD VIEWER) -->
    <div class="m-tab-bar" id="mediaTabs">
      <button type="button" class="m-tab-btn active" onclick="switchMediaTab('locandina', this)">Locandina Oratorio</button>
      <button type="button" class="m-tab-btn" onclick="switchMediaTab('depliant_fronte', this)">Depliant Fronte</button>
      <button type="button" class="m-tab-btn" onclick="switchMediaTab('depliant_retro', this)">Depliant Retro</button>
    </div>

    <!-- IMMAGINI LOCANDINE 9:16 -->
    <div class="m-poster-box" id="mediaDisplay">
      <img id="activeMediaImg" src="assets/img/events/locandina-ufficiale-oratorio.jpeg" alt="Locandina Ufficiale Oratorio Taglio di Po">
      <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.78); backdrop-filter: blur(8px); padding: 4px 8px; border-radius: 8px; font-size: 0.72rem; color: #fff; border: 1px solid rgba(255,255,255,0.2);">
        <?=dx_icon('image', '', 12)?> <span id="mediaCaption">Locandina Ufficiale</span>
      </div>
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

      <a href="https://wa.me/393478844271?text=<?=urlencode("Ciao Grazia, vorrei iscrivermi al corso 'A Scuola di Comunicazione e Resilienza' (Taglio di Po, 9-11 Ottobre).")?>" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp">
        <?=dx_icon('message-circle', '', 18)?>
        <span>Iscriviti via WhatsApp a Grazia</span>
      </a>

      <a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 44px; font-size: 0.88rem;">
        <?=dx_icon('calendar', '', 16)?>
        <span>Aggiungi al Calendario (.ics)</span>
      </a>
    </div>

  </article>

  <!-- FORM PRENOTAZIONE DIRETTA (ONE-THUMB MOBILE FLOW) -->
  <section class="m-card m-card-gold-glow" id="prenotazione">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
      <span style="color: #d4af37;"><?=dx_icon('edit', '', 18)?></span>
      <h2 style="font-size: 1.15rem; font-weight: 850; color: #ffffff; margin: 0;">
        <?=!$isFull ? "Iscrizione Online Immediata" : "Iscrizione in Lista d'Attesa"?>
      </h2>
    </div>

    <p style="font-size: 0.82rem; color: #cbd5e1; margin: 0 0 14px; line-height: 1.45;">
      Bastano 10 secondi. Riceverai conferma immediata con codice SIC e il messaggio WhatsApp pronto per Grazia Nicosia.
    </p>

    <div id="bookingAlertBox" style="display: none; padding: 14px; border-radius: 12px; margin-bottom: 14px; font-size: 0.86rem; line-height: 1.45;"></div>

    <form id="mobileBookingForm" onsubmit="handleMobileBooking(event)">
      <input type="hidden" name="event_sic_id" value="<?=h($sic)?>">

      <!-- NOME E COGNOME IN 2 COLONNE COMPATTE -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
        <div class="m-form-group">
          <label for="mb_nome">Nome <span>*</span></label>
          <input type="text" id="mb_nome" name="nome" class="m-input" required placeholder="Mario" autocomplete="given-name">
        </div>
        <div class="m-form-group">
          <label for="mb_cognome">Cognome <span>*</span></label>
          <input type="text" id="mb_cognome" name="cognome" class="m-input" required placeholder="Rossi" autocomplete="family-name">
        </div>
      </div>

      <div class="m-form-group">
        <label for="mb_email">Indirizzo Email (per ricevuta & promemoria) <span>*</span></label>
        <input type="email" id="mb_email" name="email" class="m-input" required placeholder="mario.rossi@email.it" autocomplete="email">
      </div>

      <div class="m-form-group">
        <label for="mb_phone">Numero di Telefono (WhatsApp per conferme rapide) <span>*</span></label>
        <input type="tel" id="mb_phone" name="phone" class="m-input" required placeholder="347 1234567" autocomplete="tel">
      </div>

      <div class="m-form-group">
        <label for="mb_attendee_type">Qual è il tuo ruolo di partecipazione? <span>*</span></label>
        <select id="mb_attendee_type" name="role_type" class="m-select" required>
          <option value="Operatore / Volontario">Operatore Sociale / Sanitario / Volontario</option>
          <option value="Familiare">Familiare di persona con problemi di dipendenza</option>
          <option value="Membro di Club (CAT)">Membro / Persona che frequenta un Club (CAT)</option>
          <option value="Servitore-Insegnante">Servitore-Insegnante di Club</option>
          <option value="Cittadino / Interessato">Cittadino / Persona interessata a vario titolo</option>
        </select>
      </div>

      <div class="m-form-group">
        <label for="mb_dietary_notes">Esigenze per il pranzo del sabato <small>(vegetariano, celiaco, allergie)</small></label>
        <input type="text" id="mb_dietary_notes" name="dietary_notes" class="m-input" placeholder="Nessuna o specifica intolleranze">
      </div>

      <!-- SELETTORE MODALITÀ DI PAGAMENTO 10,00 € -->
      <div style="background: rgba(22, 27, 40, 0.85); border: 1px solid rgba(212,175,55,0.3); border-radius: 12px; padding: 12px; margin: 14px 0 10px;">
        <div style="font-size: 0.82rem; font-weight: 800; color: #d4af37; text-transform: uppercase; margin-bottom: 8px;">
          Modalità Pagamento Quota di 10,00 € <span style="color:#10b981;">(Pranzo Compreso)</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          
          <label style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
            <input type="radio" name="payment_method" value="PAYPAL" checked style="accent-color: #d4af37; width: 18px; height: 18px;">
            <div>
              <div style="font-size: 0.88rem; font-weight: 850; color: #ffffff;">💳 Carta di Credito / Debito o PayPal</div>
              <div style="font-size: 0.72rem; color: #94a3b8;">Visa, Mastercard, PostePay o saldo PayPal · Conferma istantanea</div>
            </div>
          </label>

          <label style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
            <input type="radio" name="payment_method" value="USDT" style="accent-color: #d4af37; width: 18px; height: 18px;">
            <div>
              <div style="font-size: 0.88rem; font-weight: 850; color: #ffffff;">💎 USDT (Rete Polygon)</div>
              <div style="font-size: 0.72rem; color: #94a3b8;">10 USDT su rete Polygon · Transazione verificata on-chain</div>
            </div>
          </label>

          <label style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
            <input type="radio" name="payment_method" value="ON_SITE" style="accent-color: #d4af37; width: 18px; height: 18px;">
            <div>
              <div style="font-size: 0.88rem; font-weight: 850; color: #ffffff;">💵 Saldo in Contanti / POS all'Accoglienza</div>
              <div style="font-size: 0.72rem; color: #94a3b8;">Versamento all'arrivo venerdì 9 ottobre dalle 14:30</div>
            </div>
          </label>

        </div>
      </div>

      <div class="m-form-group" style="display: flex; gap: 8px; align-items: flex-start; margin-top: 10px;">
        <input type="checkbox" id="mb_consent" name="privacy_accepted" required style="margin-top: 3px; width: 18px; height: 18px; accent-color: #d4af37;">
        <label for="mb_consent" style="font-size: 0.76rem; color: #cbd5e1; line-height: 1.4; margin-bottom: 0;">
          Dichiaro di aver preso visione dell'informativa e acconsento al trattamento dei dati personali per l'organizzazione e accoglienza dell'evento ai sensi del GDPR.
        </label>
      </div>

      <button type="submit" id="mb_submit_btn" class="m-btn m-btn-primary" style="margin-top: 10px; font-size: 0.95rem;">
        <?=dx_icon('check-circle', '', 18)?>
        <span><?=!$isFull ? "ISCRIVITI & PROCEDI (10,00 €)" : "ISCRIVITI IN LISTA D'ATTESA"?></span>
      </button>
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
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=0xbde2aaa9e8d0afb90d42679c6e391e5c72be5f39" alt="QR Code Polygon USDT" style="width: 160px; height: 160px; display: block;">
      </div>

      <!-- BOX INDIRIZZO CON COPIA RAPIDA -->
      <div style="background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 8px 10px; font-family: monospace; font-size: 0.76rem; color: #e2e8f0; word-break: break-all; margin-bottom: 10px;">
        0xbde2aaa9e8d0afb90d42679c6e391e5c72be5f39
      </div>

      <button type="button" onclick="copyPolygonAddress()" class="m-btn m-btn-outline" style="min-height: 36px; font-size: 0.78rem; margin-bottom: 14px;">
        <?=dx_icon('copy', '', 14)?> <span id="copyAddrLabel">Copia Indirizzo Polygon</span>
      </button>

      <!-- FORM PER INSERIRE TX HASH -->
      <div style="text-align: left; background: rgba(0,0,0,0.3); border-radius: 10px; padding: 10px; border: 1px solid rgba(255,255,255,0.08);">
        <label for="usdt_tx_hash" style="font-size: 0.76rem; font-weight: 750; color: #ffffff; display: block; margin-bottom: 4px;">
          Inserisci la TX Hash della transazione inviata:
        </label>
        <input type="text" id="usdt_tx_hash" class="m-input" placeholder="Es. 0x123abc456..." style="font-size: 0.8rem; margin-bottom: 8px;">
        <button type="button" onclick="submitUsdtTx()" id="usdt_confirm_btn" class="m-btn m-btn-primary" style="min-height: 40px; font-size: 0.84rem;">
          <?=dx_icon('send', '', 14)?> Conferma Notifica USDT
        </button>
      </div>
    </div>
  </section>

  <!-- 5 RISULTATI CONCRETI -->
  <section class="m-card">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
      <span style="color: #d4af37;"><?=dx_icon('award', '', 18)?></span>
      <h2 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">Cosa Saprai Fare dal Lunedì</h2>
    </div>

    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.86rem; color: #cbd5e1;">
      <div style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900;">1.</span>
        <div><b>Comunicare senza litigare:</b> disinnescare la rabbia, esprimere i propri sentimenti in modo chiaro e congruente senza aggredire né farsi calpestare.</div>
      </div>
      <div style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900;">2.</span>
        <div><b>Non farti caricare dai problemi altrui:</b> riconoscere le trappole del potere, proteggere i propri confini emotivi e superare il senso di colpa paralizzante.</div>
      </div>
      <div style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900;">3.</span>
        <div><b>Ascolto attivo in situazioni conflittuali:</b> riconoscere le fragilità altrui ed eliminare etichette, valutazioni affrettate e giudizi fuorvianti.</div>
      </div>
      <div style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900;">4.</span>
        <div><b>Risoluzione democratica dei problemi:</b> applicare la scala dei bisogni e trovare soluzioni condivise "Io vinco, Tu vinci" sia in famiglia che nel Club.</div>
      </div>
      <div style="display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: #10b981; font-weight: 900;">5.</span>
        <div><b>Attestato Ufficiale di Partecipazione:</b> rilasciato a chi partecipa per intero al corso, riconosciuto nell'Approccio Ecologico-Sociale di V. Hudolin.</div>
      </div>
    </div>
  </section>

  <!-- PROGRAMMA DETTAGLIATO ORA PER ORA -->
  <section class="m-card">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
      <span style="color: #d4af37;"><?=dx_icon('clock', '', 18)?></span>
      <h2 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">Programma Orario Completo</h2>
    </div>

    <!-- VENERDÌ -->
    <div class="m-schedule-day">
      <div class="m-schedule-header">
        <b style="color: #ffffff; font-size: 0.86rem;">Venerdì 9 Ottobre 2026</b>
        <span style="color: #d4af37; font-size: 0.78rem; font-weight: 750;">14:30 – 19:00</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">14:30 - 15:00</span>
        <span class="m-schedule-desc">Iscrizione e saluto delle Autorità</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">15:00 - 15:30</span>
        <span class="m-schedule-desc">Presentazione del corso e vantaggi di "Le Persone efficaci"</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">15:30 - 16:30</span>
        <span class="m-schedule-desc">Esperienza: cominciamo a conoscerci</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">16:30 - 16:45</span>
        <span class="m-schedule-desc">Per CHI io sono qui? Chi è la persona più importante della mia vita?</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">16:45 - 17:00</span>
        <span class="m-schedule-desc">Motivazioni e attese dei partecipanti</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">17:15 - 18:00</span>
        <span class="m-schedule-desc">Esperienza: le mie qualità più importanti · Integrazione</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">18:00 - 19:00</span>
        <span class="m-schedule-desc">Esperienza multisensoriale: come stare subito bene · Compiti a casa</span>
      </div>
    </div>

    <!-- SABATO -->
    <div class="m-schedule-day">
      <div class="m-schedule-header">
        <b style="color: #ffffff; font-size: 0.86rem;">Sabato 10 Ottobre 2026</b>
        <span style="color: #d4af37; font-size: 0.78rem; font-weight: 750;">09:00 – 19:00</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">09:00 - 10:00</span>
        <span class="m-schedule-desc">I rettangoli del comportamento: teoria e pratica</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">10:00 - 11:00</span>
        <span class="m-schedule-desc">Riconoscere le fragilità: abilità di ascolto e barriere alla comunicazione</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">11:15 - 12:00</span>
        <span class="m-schedule-desc">Esperienza di ascolto attivo in coppia e condivisione</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">12:00 - 13:00</span>
        <span class="m-schedule-desc">Come facilitare la soluzione di un problema</span>
      </div>
      <div class="m-schedule-item" style="background: rgba(16,185,129,0.1); border-radius: 8px; padding: 8px;">
        <span class="m-schedule-time" style="color: #10b981;">13:00 - 14:00</span>
        <span class="m-schedule-desc"><b style="color: #10b981;">Pausa Pranzo Comunitario</b> (incluso nella quota di 10€)</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">14:00 - 15:00</span>
        <span class="m-schedule-desc">Esprimere bisogni e sentimenti in modo chiaro · Autorivelazione</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">15:00 - 16:00</span>
        <span class="m-schedule-desc">Quando non mi piacciono i comportamenti degli altri: come confrontarsi</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">16:15 - 17:15</span>
        <span class="m-schedule-desc">La resistenza e il cambio di marcia: bisogni e trappole del potere</span>
      </div>
      <div class="m-schedule-item">
        <span class="m-schedule-time">17:15 - 18:00</span>
        <span class="m-schedule-desc">Esperienza: Io vinco, Tu vinci · Integrazione e compiti a casa</span>
      </div>
    </div>

    <!-- DOMENICA -->
    <div class="m-schedule-day" style="margin-bottom: 0;">
      <div class="m-schedule-header">
        <b style="color: #ffffff; font-size: 0.86rem;">Domenica 11 Ottobre 2026</b>
        <span style="color: #d4af37; font-size: 0.78rem; font-weight: 750;">09:00 – 13:00</span>
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
      <div class="m-schedule-item">
        <span class="m-schedule-time">12:45 - 13:00</span>
        <span class="m-schedule-desc">Questionario di verifica ante-post, consegna attestati e conclusioni</span>
      </div>
    </div>
  </section>

  <!-- DOCENTE & FORMATORE -->
  <section class="m-card">
    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 12px;">
      <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.4); display: grid; place-items: center; color: #d4af37;">
        <?=dx_icon('award', '', 26)?>
      </div>
      <div>
        <div style="font-size: 0.72rem; color: #d4af37; font-weight: 800; text-transform: uppercase;">Docente e Formatore</div>
        <h3 style="font-size: 1.05rem; color: #ffffff; margin: 2px 0 0; font-weight: 850;">Dott. Adelmo Di Salvatore</h3>
      </div>
    </div>
    <p style="font-size: 0.84rem; color: #cbd5e1; line-height: 1.5; margin: 0 0 10px;">
      Psichiatra e Psicoterapeuta, formatore autorizzato nell'Approccio Centrato sulla Persona (Carl Rogers), Approccio Motivazionale (Miller e Rollnick), Programmazione NeuroLinguistica (Bandler e Grinder), Approccio Ecologico-Sociale (Vladimir Hudolin), con esperienza ultratrentennale come Servitore-Insegnante nei Club Alcologici Territoriali e di Ecologia Familiare e Sociale.
    </p>
  </section>

  <!-- SEDE & LOGISTICA -->
  <section class="m-card">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
      <span style="color: #d4af37;"><?=dx_icon('map-pin', '', 18)?></span>
      <h2 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">Sede & Logistica</h2>
    </div>
    <div style="font-size: 0.86rem; color: #ffffff; font-weight: 750; margin-bottom: 4px;">
      Oratorio San Francesco d'Assisi
    </div>
    <div style="font-size: 0.82rem; color: #cbd5e1; margin-bottom: 12px;">
      Vicolo San Francesco 1, Taglio di Po (RO)
    </div>
    <a href="https://maps.google.com/?q=Oratorio+San+Francesco+d'Assisi+Taglio+di+Po" target="_blank" rel="noopener" class="m-btn m-btn-outline" style="min-height: 42px; font-size: 0.84rem;">
      <?=dx_icon('map-pin', '', 14)?> Apri Navigatore Google Maps
    </a>
  </section>

  <!-- SEZIONE OPERATIVA CLUB: ISTRUZIONI PER IL PIENISSIMO -->
  <section class="m-card" style="border-color: rgba(212,175,55,0.4); background: rgba(14, 18, 28, 0.95);">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
      <span style="color: #d4af37;"><?=dx_icon('shield-check', '', 18)?></span>
      <h2 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">Kit Club: Istruzioni per il Pienissimo</h2>
    </div>

    <div style="background: rgba(212,175,55,0.1); border-left: 3px solid #d4af37; padding: 10px 12px; border-radius: 10px; margin-bottom: 12px; font-size: 0.82rem; color: #e2e8f0; line-height: 1.45;">
      <b>Manifesto 100% Digitale:</b> No carta, no spese, no giri a vuoto = tempo libero, cassa solida, club pieni e gratitudine. Le persone e i gruppi WhatsApp ci sono già.
    </div>

    <div style="font-size: 0.84rem; color: #cbd5e1; display: flex; flex-direction: column; gap: 10px;">
      <div>
        <b style="color: #ffffff;">1. La Regola dei 3 Nomi:</b>
        <p style="margin: 2px 0 0; font-size: 0.8rem; color: #94a3b8;">
          Ogni servitore-insegnante scrive 3 nomi di persone che hanno bisogno di questo corso e le invita una per una. Chi accetta si iscrive subito davanti a te con lo smartphone.
        </p>
      </div>

      <div>
        <b style="color: #ffffff;">2. Pitch di 40 secondi in riunione di Club:</b>
        <p style="margin: 2px 0 0; font-size: 0.8rem; color: #fff2b2; font-style: italic; background: rgba(0,0,0,0.4); padding: 8px 10px; border-radius: 8px;">
          "Il 9, 10 e 11 ottobre facciamo un corso qui a Taglio di Po su come si parla quando la conversazione si fa difficile — in famiglia, in club, quando sale il conflitto e non sai più cosa dire. Tre giorni con Adelmo Di Salvatore. Dieci euro col pranzo. I posti sono 30. Adesso chiedo a ognuno di voi se viene, così so a chi tenere il posto."
        </p>
      </div>

      <div>
        <b style="color: #ffffff;">3. Monitoraggio entro Mercoledì 24 Settembre:</b>
        <p style="margin: 2px 0 0; font-size: 0.8rem; color: #94a3b8;">
          Invio foglio dei 3 nomi a Cristiana. Il 26 richiamo a tutti i "ci penso". Il 30 decisione e conferma.
        </p>
      </div>

      <div>
        <b style="color: #ffffff;">4. Protocollo Overbooking (Oltre i 30):</b>
        <p style="margin: 2px 0 0; font-size: 0.8rem; color: #94a3b8;">
          Il tetto di 30 è rigoroso per la qualità dei role-play. Dal 31° scatta la lista d'attesa numerata per timestamp. Da 8 in lista si apre subito la 2ª edizione con data prefissata.
        </p>
      </div>
    </div>
  </section>

  <!-- GRIGLIA UFFICIALE DEI 28 SPONSOR & ASSET DELL'ECOSISTEMA -->
  <?php require_once __DIR__ . '/templates/_sponsor_grid.php'; ?>

  <!-- CONTATTI UFFICIALI -->
  <div class="text-center" style="font-size: 0.78rem; color: #94a3b8; margin-top: 14px;">
    <p style="margin: 0 0 4px;">Organizzazione: <b>ACAT Basso Polesine O.D.V.</b></p>
    <p style="margin: 0;">Referente Iscrizioni: <b>Grazia Nicosia</b> · Tel. WhatsApp <strong>347 884 4271</strong></p>
  </div>

</div>

<!-- STICKY BOTTOM ACTION BAR PER SMARTPHONE (9:16 SAFE-AREA) -->
<div class="m-sticky-bar">
  <div class="m-sticky-bar-inner">
    <a href="#prenotazione" class="m-btn m-btn-primary" style="flex: 1; min-height: 48px; font-size: 0.92rem; padding: 0 12px;">
      <?=dx_icon('check-circle', '', 16)?> Prenota Quota 10€
    </a>
    <a href="https://wa.me/393478844271?text=<?=urlencode("Ciao Grazia, vorrei iscrivermi al corso 'A Scuola di Comunicazione e Resilienza' di Taglio di Po.")?>" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp" style="width: 52px; min-height: 48px; padding: 0; flex-shrink: 0;" title="WhatsApp Diretto Grazia">
      <?=dx_icon('message-circle', '', 20)?>
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
  
  if (tab === 'locandina') {
    img.src = 'assets/img/events/locandina-ufficiale-oratorio.jpeg';
    img.alt = 'Locandina Ufficiale Oratorio Taglio di Po';
    cap.innerText = 'Locandina Oratorio';
  } else if (tab === 'depliant_fronte') {
    img.src = 'assets/img/events/evento-ottobre-taglio-di-po.jpeg';
    img.alt = 'Depliant Fronte Pieghevole';
    cap.innerText = 'Depliant Pieghevole Fronte';
  } else if (tab === 'depliant_retro') {
    img.src = 'assets/img/events/depliant-programma-completo.jpeg';
    img.alt = 'Depliant Retro con Programma Orario';
    cap.innerText = 'Depliant Programma Completo';
  }
}

function copyPolygonAddress() {
  const addr = '0xbde2aaa9e8d0afb90d42679c6e391e5c72be5f39';
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

<?php require '_footer.php';?>