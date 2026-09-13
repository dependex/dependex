<?php 
declare(strict_types=1);
require_once 'bootstrap.php';
$u = current_user();

// Sincronizza ed estrae l'evento unico e ufficiale di Taglio di Po
$events = EventSyncService::syncAndGetActiveEvents();
$event = !empty($events) ? $events[0] : null;

if (!$event) {
    // Fallback sicuro al record ACAT se il database fosse temporaneamente vuoto
    $st = db()->prepare('SELECT * FROM events WHERE sic_id = "SIC-EVT-ACAT-BP-2026-COMM" LIMIT 1');
    $st->execute();
    $event = $st->fetch(PDO::FETCH_ASSOC);
}

// Calcolo presenze confermate e posti rimasti
$sic = $event['sic_id'] ?? 'SIC-EVT-ACAT-BP-2026-COMM';
$countStmt = db()->prepare("
    SELECT (
        (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = ? AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
        (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = ? AND eb.status = 'CONFIRMED')
    ) as total_booked
");
$countStmt->execute([$sic, $sic]);
$totalBooked = (int)$countStmt->fetchColumn();

$capacity = (int)($event['capacity'] ?? 30);
$seatsRemaining = max(0, $capacity - $totalBooked);
$isFull = ($seatsRemaining <= 0);
$percentBooked = $capacity > 0 ? min(100, round(($totalBooked / $capacity) * 100)) : 0;

$pageTitle = 'Evento Taglio di Po: A Scuola di Comunicazione e Resilienza · ACAT Basso Polesine';
$metaDesc = '9-10-11 Ottobre 2026, Oratorio San Francesco d\'Assisi, Taglio di Po. Corso esperienziale con Adelmo Di Salvatore. Max 30 posti, quota 10€ con pranzo compreso.';
require '_header.php';
?>

<!-- MOBILE-FIRST 9:16 CONTAINER (Zero sbordature, responsive smartphone shell) -->
<div class="mobile-916-shell">

  <!-- TOP BRAND & PATRONAGE BADGE -->
  <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 12px;">
    <span class="m-badge m-badge-gold">
      <?=dx_icon('activity', '', 12)?> ACAT BASSO POLESINE
    </span>
    <span class="m-badge <?=!$isFull ? 'm-badge-green' : 'm-badge-red'?>">
      <?=dx_icon('users', '', 12)?> <?=!$isFull ? "$seatsRemaining Posti Rimasti" : "30/30 Esauriti (Waitlist)"?>
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
      <a href="event-detail.php?event=<?=urlencode($sic)?>" title="Apri locandina e pagina dedicata">
        <img src="assets/img/events/locandina-ufficiale-oratorio.jpeg" alt="Locandina Ufficiale Taglio di Po" style="width: 100%; height: auto; display: block;">
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
        <b style="color: <?=!$isFull ? '#10b981' : '#ef4444'?>;"><?=$totalBooked?> / <?=$capacity?> Iscritti</b>
      </div>
      <div class="m-progress-bar">
        <div class="m-progress-fill" style="width: <?=$percentBooked?>%;"></div>
      </div>
      <div style="font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between;">
        <span>Chiusura: 1° Ottobre 2026</span>
        <span><?=!$isFull ? "Ancora $seatsRemaining posti" : "Lista d'attesa attiva"?></span>
      </div>
    </div>

    <!-- BOTTONI DI AZIONE TOUCH (FULL-WIDTH 9:16) -->
    <div style="display: flex; flex-direction: column; gap: 8px;">
      <a href="event-detail.php?event=<?=urlencode($sic)?>" class="m-btn m-btn-primary">
        <?=dx_icon('check-circle', '', 18)?>
        <span>PAGINA EVENTO DEDICATA & PRENOTA</span>
      </a>

      <a href="https://wa.me/393478844271?text=<?=urlencode("Ciao Grazia, vorrei iscrivermi al corso 'A Scuola di Comunicazione e Resilienza' del 9-11 Ottobre a Taglio di Po.")?>" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp">
        <?=dx_icon('message-circle', '', 18)?>
        <span>Iscriviti Subito via WhatsApp (Grazia)</span>
      </a>

      <a href="event-ics.php?event=<?=urlencode($sic)?>" download class="m-btn m-btn-outline" style="min-height: 44px; font-size: 0.88rem;">
        <?=dx_icon('calendar', '', 16)?>
        <span>Salva sul Calendario dello Smartphone (.ics)</span>
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
  <!-- HUB NAZIONALE DIPENDENZE: TUTTI GLI EVENTI D'ITALIA            -->
  <!-- ============================================================== -->
  <?php 
  $nationalEventsList = array_slice($events, 1);
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
  <!-- BANNER VIAGGI ESPERIENZIALI & CROCIERA BEWAY.LIFE x DEPENDEX -->
  <section class="m-card" style="background: radial-gradient(circle at top right, rgba(0,212,255,0.15), rgba(12,16,28,0.95)); border: 1px solid rgba(0,212,255,0.4); border-radius: 18px; padding: 20px; margin-top: 24px;">
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
      <span style="font-size: 20px;">🌊</span>
      <span class="m-cat-badge" style="background: rgba(0,212,255,0.2); color: #38bdf8; border-color: #38bdf8;">BEWAY.LIFE x DEPENDEX</span>
    </div>
    <h3 style="font-size: 1.15rem; font-weight: 850; color: #ffffff; margin: 0 0 8px;">
      Oltre i Convegni: La Grande Crociera della Rinascita in Mare Aperto
    </h3>
    <p style="font-size: 0.86rem; color: #cbd5e1; line-height: 1.55; margin: 0 0 14px;">
      8 giorni e 7 notti nel Mediterraneo (Grecia e Montenegro) con formula 100% analcolica, cucina vitale e Masterclass intensive con Mirco Pregnolato e i facilitatori del Metodo Hudolin.
    </p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="crociera-benessere-masterclass.php" class="m-btn m-btn-primary" style="flex: 1; min-height: 42px; font-size: 0.84rem; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
        <?=dx_icon('compass', '', 14)?> Scopri la Crociera
      </a>
      <a href="viaggi-esperienziali.php" class="m-btn" style="flex: 1; min-height: 42px; font-size: 0.84rem; border: 1px solid rgba(255,255,255,0.2); color: #fff; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center;">
        Tutti i Viaggi BEWAY.LIFE
      </a>
    </div>
  </section>

  <!-- GRIGLIA UFFICIALE DEI 28 SPONSOR & ASSET DELL'ECOSISTEMA -->
  <?php require_once __DIR__ . '/templates/_sponsor_grid.php'; ?>

  <!-- FOOTER DELLA SCHEDA MOBILE -->
  <div class="text-center" style="margin-top: 16px; font-size: 0.78rem; color: #94a3b8;">
    <p style="margin: 0 0 4px;">Per informazioni e iscrizioni telefoniche: Grazia Nicosia (Servitrice-Insegnante) · <strong>Tel. 347 884 4271</strong></p>
    <p style="margin: 0; color: #d4af37;">100% Digitale · Zero carta · Zero sprechi · Posti certificati</p>
  </div>

</div>

<!-- STICKY BOTTOM ACTION BAR PER SMARTPHONE (9:16 SAFE-AREA) -->
<div class="m-sticky-bar">
  <div class="m-sticky-bar-inner">
    <a href="event-detail.php?event=<?=urlencode($sic)?>" class="m-btn m-btn-primary" style="flex: 1; min-height: 48px; font-size: 0.92rem; padding: 0 12px;">
      <?=dx_icon('check-circle', '', 16)?> Iscriviti (10€)
    </a>
    <a href="https://wa.me/393478844271?text=<?=urlencode("Ciao Grazia, vorrei iscrivermi al corso di Taglio di Po (9-11 Ottobre).")?>" target="_blank" rel="noopener" class="m-btn m-btn-whatsapp" style="width: 52px; min-height: 48px; padding: 0; flex-shrink: 0;" title="Contatta Grazia su WhatsApp">
      <?=dx_icon('message-circle', '', 20)?>
    </a>
  </div>
</div>

<?php require '_footer.php';?>