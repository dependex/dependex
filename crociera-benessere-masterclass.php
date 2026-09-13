<?php
/**
 * LA GRANDE CROCIERA DELLA RINASCITA & SOBRIETÀ · BEWAY.LIFE x DEPENDEX
 * Masterclass & Workshop in Mare Aperto con Mirco Pregnolato
 * 8 Giorni / 7 Notti nel Mediterraneo · Formule Cabina Smart, Comfort e Suite VIP
 */

declare(strict_types=1);

require_once 'bootstrap.php';

$pageTitle = 'Grande Crociera della Rinascita: Masterclass & Workshop in Mare Aperto · BEWAY.LIFE x DEPENDEX';
$metaDesc = '8 giorni e 7 notti nel Mediterraneo in formula 100% analcolica. Workshop quotidiani con Mirco Pregnolato, biohacking, riconnessione familiare e cerchi al tramonto. Prenota la tua cabina.';
require '_header.php';

$cabinTiers = [
    [
        'tier' => 1,
        'code' => 'CRUISE-SMART',
        'title' => 'Cabina Smart Interna + Workshop Pass',
        'badge' => 'SCELTA ACCESSIBILE',
        'badge_color' => 'var(--neon-cyan)',
        'price_full' => '890,00 €',
        'offer_id_full' => 'off_cruise_smart',
        'price_deposit' => '190,00 €',
        'offer_id_deposit' => 'off_cruise_smart_deposit',
        'features' => [
            'Soggiorno 8 giorni / 7 notti in elegante cabina doppia interna',
            'Pensione completa sobria gourmet (colazione, pranzo, cena)',
            'Bevande analcoliche, Mocktail d\'autore e tisane biologiche illimitate',
            'Accesso a tutti i Workshop pomeridiani del Metodo Hudolin',
            'Partecipazione ai cerchi di ascolto e condivisione al tramonto',
            'Kit di benvenuto con Diario del Viaggiatore'
        ]
    ],
    [
        'tier' => 2,
        'code' => 'CRUISE-COMFORT',
        'title' => 'Cabina Comfort Balcone + Masterclass Full Pass',
        'badge' => 'PIÙ POPOLARE',
        'badge_color' => 'var(--neon-gold)',
        'price_full' => '1.290,00 €',
        'offer_id_full' => 'off_cruise_comfort',
        'price_deposit' => '290,00 €',
        'offer_id_deposit' => 'off_cruise_comfort_deposit',
        'features' => [
            'Cabina privata con ampio balcone panoramico affacciato sul mare',
            'Pensione completa sobria con servizio colazione in camera su richiesta',
            'Sessioni speciali mattutine di Risveglio Bioenergetico & Coerenza Cardiaca',
            'Full Pass Masterclass intensiva con Mirco Pregnolato e facilitatori',
            'Laboratori avanzati per coppie e dinamiche familiari',
            'In omaggio copia cartacea autografata del libro "Diario di Crescita Esponenziale"'
        ]
    ],
    [
        'tier' => 3,
        'code' => 'CRUISE-SUITE-VIP',
        'title' => 'Gran Suite VIP + Mentoring Esclusivo 1-a-1',
        'badge' => 'ESPERIENZA VIP (MAX 6 CABINE)',
        'badge_color' => 'var(--neon-violet)',
        'price_full' => '1.990,00 €',
        'offer_id_full' => 'off_cruise_suite_vip',
        'price_deposit' => '490,00 €',
        'offer_id_deposit' => 'off_cruise_suite_deposit',
        'features' => [
            'Lussuosa Gran Suite con terrazzo privato vista mare e vasca idromassaggio',
            'Maggiordomo dedicato e imbarco prioritario VIP senza attese',
            'Formula All-Inclusive totale con tavolo riservato',
            '3 Sessioni di Mentoring Strategico 1-a-1 individuale con Mirco Pregnolato',
            'Accesso prioritario al tavolo relatori e momenti di confronto esclusivo',
            'Cofanetto Deluxe con tutti i 6 libri della collana editoriale autografati'
        ]
    ]
];

$itinerary = [
    [
        'day' => 'Giorno 1',
        'port' => 'Civitavecchia / Partenza',
        'theme' => 'L\'Imbarco & Il Patto del Mare',
        'desc' => 'Accoglienza al porto, sistemazione nelle cabine riservate. Ore 18:00: Cerchio di Benvenuto sul ponte di poppa con aperitivo analcolico d\'autore e presentazione dei docenti e del gruppo.'
    ],
    [
        'day' => 'Giorno 2',
        'port' => 'Navigazione nel Mediterraneo',
        'theme' => 'Workshop 1: Decodificare i Meccanismi Dopaminergici',
        'desc' => 'Ore 07:30: Respirazione dell\'alba con vista sulle onde. Ore 10:30-13:00: Workshop intensivo condotto da Mirco Pregnolato su come disinnescare i trigger della dipendenza e ristabilire il controllo della volontà. Pomeriggio di relax e piscina termale.'
    ],
    [
        'day' => 'Giorno 3',
        'port' => 'Santorini (Grecia)',
        'theme' => 'La Rinascita della Bellezza & Dialogo di Coppia',
        'desc' => 'Mattinata dedicata alla visita libera o guidata tra le cupole bianche e blu della caldera. Ore 16:30: Laboratorio per famiglie e coppie: ricostruire la fiducia e superare i risentimenti passati con il Metodo Hudolin.'
    ],
    [
        'day' => 'Giorno 4',
        'port' => 'Mykonos (Grecia)',
        'theme' => 'Workshop 2: La Riprogrammazione delle Abitudini Quotidiane',
        'desc' => 'Dalla teoria alla pratica: come strutturare le prime 2 ore del mattino e l\'ultima ora serale per blindare la propria lucidità. Dimostrazione ed esercizi dal "Diario di Crescita Esponenziale".'
    ],
    [
        'day' => 'Giorno 5',
        'port' => 'Atene / Pireo (Grecia)',
        'theme' => 'Filosofia Stoica & Sovranità Personale',
        'desc' => 'Escursione all\'Acropoli di Atene. Ore 17:00 sul ponte nave: Masterclass sulla saggezza stoica antica applicata alla libertà moderna dalle sostanze e dalle abitudini tossiche.'
    ],
    [
        'day' => 'Giorno 6',
        'port' => 'Kotor (Montenegro)',
        'theme' => 'Il Silenzio dei Fiordi & Scudo dal Rientro',
        'desc' => 'Navigazione mozzafiato tra le gole montuose delle Bocche di Cattaro. Workshop pratico: "Cosa fare dal giorno 9 in poi: come proteggere la propria sobrietà dalle pressioni sociali, aperitivi e vecchie abitudini".'
    ],
    [
        'day' => 'Giorno 7',
        'port' => 'Navigazione verso l\'Italia',
        'theme' => 'Il Cerchio della Gratitudine & Festa della Luce',
        'desc' => 'Giornata di consolidamento. Sessioni individuali di feedback, cerchio conclusivo con condivisione delle trasformazioni vissute, consegna degli attestati e grande Gala Sobrio della Luce.'
    ],
    [
        'day' => 'Giorno 8',
        'port' => 'Civitavecchia / Sbarco',
        'theme' => 'Il Ritorno al Mondo con una Nuova Identità',
        'desc' => 'Colazione di saluto. Sbarco ore 09:30. Rientro a casa con l\'inclusione permanente nel cerchio digitale della community e dell\'Academy.'
    ]
];
?>

<main class="container py-5">

  <!-- ============================================================== -->
  <!-- HERO BANNER CINEMATICO                                         -->
  <!-- ============================================================== -->
  <section class="mb-5 text-center">
    <div class="badge-neon-rainbow mb-3" style="font-size: 0.8rem; padding: 6px 18px;">
      <span class="dot"></span>
      <span class="text-rainbow">BEWAY.LIFE x DEPENDEX · MASTERCLASS CRUISE 2026</span>
    </div>

    <h1 style="font-family: var(--font-serif); font-size: clamp(2.3rem, 5.5vw, 3.8rem); font-weight: 900; color: #FFFFFF; line-height: 1.15; margin-bottom: 1.2rem;">
      La Grande Crociera della Rinascita <br>
      <span class="text-rainbow">Masterclass & Workshop in Mare Aperto</span>
    </h1>

    <p style="font-size: 1.2rem; line-height: 1.7; color: #cbd5e1; max-width: 880px; margin: 0 auto 2rem;">
      8 Giorni e 7 Notti nel Mediterraneo Orientale a bordo di una prestigiosa nave da crociera. Un'esperienza trasformativa al <strong>100% analcolica</strong> con <strong>Mirco Pregnolato</strong> e i migliori facilitatori del <strong>Metodo Hudolin</strong>. Impara, rigenerati e riconquista la sovranità della tua vita navigando tra le meraviglie del mare.
    </p>

    <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
      <a href="#prenota-cabina" class="btn primary" style="padding: 14px 34px; font-weight: 800; font-size: 1.05rem; border-radius: 14px; text-decoration: none;">
        <?=dx_icon('compass', '', 18)?> Scegli la tua Cabina (Da 890 € o Caparra 190 €)
      </a>
      <a href="https://wa.me/393478844271?text=<?=urlencode('Buongiorno, desidero parlare con il concierge per la Crociera della Rinascita.')?>" target="_blank" rel="noopener" class="btn" style="background: #25D366; color: #000; font-weight: 800; padding: 14px 26px; border-radius: 14px; text-decoration: none; border: none; display: inline-flex; align-items: center; gap: 8px;">
        <?=dx_icon('whatsapp', '', 18)?> Concierge WhatsApp (+39 347 884 4271)
      </a>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- BARRA SPECIFICHE CHIAVE                                        -->
  <!-- ============================================================== -->
  <div class="lux-metallic-card p-4 mb-5" style="border: 1px solid rgba(0, 212, 255, 0.35); background: rgba(12, 16, 28, 0.94); border-radius: 20px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; text-align: center;">
      <div>
        <small style="color: var(--neon-gold); font-weight: 800; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">DURATA</small>
        <b style="color: #FFFFFF; font-size: 1.1rem;">8 Giorni / 7 Notti</b>
      </div>
      <div>
        <small style="color: var(--neon-cyan); font-weight: 800; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">ITINERARIO</small>
        <b style="color: #FFFFFF; font-size: 1.1rem;">Grecia & Montenegro</b>
      </div>
      <div>
        <small style="color: var(--neon-green); font-weight: 800; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">FORMULA</small>
        <b style="color: #FFFFFF; font-size: 1.1rem;">100% Analcolica & Gourmet</b>
      </div>
      <div>
        <small style="color: var(--neon-violet); font-weight: 800; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">CAPIENZA MASTERCLASS</small>
        <b style="color: #FFFFFF; font-size: 1.1rem;">Max 40 Partecipanti</b>
      </div>
    </div>
  </div>

  <!-- ============================================================== -->
  <!-- IL PROGRAMMA DEGLI 8 GIORNI                                    -->
  <!-- ============================================================== -->
  <section class="mb-5">
    <div class="text-center mb-4">
      <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
        <span class="dot"></span>
        <span class="text-rainbow">ITINERARIO & CONTENUTI DIDATTICI</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; font-weight: 800;">
        Cosa Faremo Giorno per Giorno
      </h2>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px; max-width: 960px; margin: 0 auto;">
      <?php foreach($itinerary as $it): ?>
        <div class="card p-3 p-md-4" style="background: rgba(12, 16, 26, 0.94); border-radius: 18px; border: 1px solid rgba(255,255,255,0.08); display: grid; grid-template-columns: 140px 1fr; gap: 18px; align-items: center;">
          <div style="border-right: 1px solid rgba(255,255,255,0.08); padding-right: 12px;">
            <span style="color: var(--neon-gold); font-weight: 900; font-size: 1.1rem; display: block;"><?=h($it['day'])?></span>
            <small style="color: var(--neon-cyan); font-weight: 700; font-size: 0.8rem; display: block;"><?=h($it['port'])?></small>
          </div>
          <div>
            <h3 style="color: #FFFFFF; font-size: 1.1rem; font-weight: 800; margin: 0 0 6px;"><?=h($it['theme'])?></h3>
            <p style="margin: 0; color: #cbd5e1; font-size: 0.9rem; line-height: 1.55;"><?=h($it['desc'])?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- TABELLA TIER DI CABINA & PREZZI                                -->
  <!-- ============================================================== -->
  <section id="prenota-cabina" class="mb-5">
    <div class="text-center mb-4">
      <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
        <span class="dot"></span>
        <span class="text-rainbow">QUOTE TRASPARENTI & FORMULE RATEIZZABILI</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; font-weight: 800;">
        Scegli il tuo Livello di Cabina (Tier)
      </h2>
      <p style="color: #cbd5e1; max-width: 680px; margin: 0 auto; font-size: 0.98rem;">
        Puoi saldare la quota intera o bloccare il tuo alloggio con una <strong>caparra confirmatoria</strong>. Il saldo potrà essere versato con calma fino a 30 giorni prima della partenza.
      </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 26px;">
      <?php foreach($cabinTiers as $cb): ?>
        <article class="card p-4" style="display: flex; flex-direction: column; justify-content: space-between; border-radius: 22px; background: rgba(12, 16, 26, 0.94); border: 1px solid rgba(255, 255, 255, 0.12); box-shadow: 0 12px 40px rgba(0,0,0,0.65);">
          
          <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <span class="dx-ticker-badge" style="color: <?=$cb['badge_color']?>; border-color: <?=$cb['badge_color']?>; font-size: 0.74rem;">
                <?=$cb['badge']?>
              </span>
              <span style="font-size: 0.74rem; font-weight: 800; color: #94a3b8;">
                TIER <?=$cb['tier']?>
              </span>
            </div>

            <h3 style="color: #FFFFFF; font-family: var(--font-serif); font-size: 1.4rem; font-weight: 800; margin: 0.4rem 0 0.8rem; line-height: 1.3;">
              <?=h($cb['title'])?>
            </h3>

            <!-- Prezzi -->
            <div style="background: rgba(8, 12, 22, 0.9); border-radius: 14px; padding: 14px; margin-bottom: 18px; border: 1px solid rgba(255,255,255,0.08);">
              <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                <span style="color: #94a3b8; font-size: 0.84rem;">Quota Completa:</span>
                <b style="color: var(--neon-gold); font-size: 1.5rem; font-weight: 900;"><?=h($cb['price_full'])?></b>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: baseline; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 6px;">
                <span style="color: var(--neon-cyan); font-size: 0.84rem; font-weight: 700;">Blocca con Caparra:</span>
                <b style="color: #FFFFFF; font-size: 1.15rem; font-weight: 800;"><?=h($cb['price_deposit'])?></b>
              </div>
            </div>

            <!-- Features -->
            <div style="margin-bottom: 22px;">
              <span style="font-size: 0.74rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 8px;">Cosa include la cabina:</span>
              <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.84rem; color: #cbd5e1; display: flex; flex-direction: column; gap: 6px;">
                <?php foreach($cb['features'] as $f): ?>
                  <li style="display: flex; align-items: flex-start; gap: 8px;">
                    <span style="color: <?=$cb['badge_color']?>;">✓</span>
                    <span><?=h($f)?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <!-- Pulsanti Prenotazione Rapida -->
          <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="checkout.php?offer_id=<?=urlencode($cb['offer_id_deposit'])?>" class="btn primary" style="width: 100%; border-radius: 12px; font-weight: 800; font-size: 0.92rem; text-decoration: none; text-align: center;">
              <?=dx_icon('lock', '', 14)?> Blocca con Caparra (<?=h($cb['price_deposit'])?>)
            </a>
            <a href="checkout.php?offer_id=<?=urlencode($cb['offer_id_full'])?>" class="btn" style="width: 100%; border-radius: 12px; font-weight: 700; font-size: 0.86rem; border: 1px solid rgba(255,255,255,0.2); color: #fff; text-decoration: none; text-align: center;">
              Salda Quota Completa (<?=h($cb['price_full'])?>)
            </a>
          </div>

        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- METODI DI PAGAMENTO E TESORERIA SOVRANA USDT                    -->
  <!-- ============================================================== -->
  <section class="lux-metallic-card p-4 p-md-5 my-5" style="border: 1px solid rgba(0, 212, 255, 0.35); background: rgba(12, 16, 28, 0.94); border-radius: 22px;">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.72rem;">
          <span class="dot"></span>
          <span class="text-rainbow">SISTEMA DI PAGAMENTO MULTI-GATEWAY CRITTOGRAFATO</span>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: 1.6rem; color: #FFFFFF; margin-bottom: 0.8rem;">
          Come Finalizzare la Tua Prenotazione
        </h3>
        <p style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.65; margin-bottom: 1.2rem;">
          Tutte le transazioni beneficiano della massima protezione fiscale e crittografica. Puoi scegliere di pagare con:
        </p>
        <ul style="list-style: none; padding: 0; margin: 0 0 1.2rem; color: #cbd5e1; font-size: 0.9rem; display: flex; flex-direction: column; gap: 6px;">
          <li style="display: flex; align-items: center; gap: 8px;">
            <span style="color: var(--neon-gold); font-weight: 800;">✓</span>
            <span><strong>PayPal Live & Carte:</strong> Visa, Mastercard, American Express, Postepay con opzione rateale "Paga in 3 rate".</span>
          </li>
          <li style="display: flex; align-items: center; gap: 8px;">
            <span style="color: var(--neon-cyan); font-weight: 800;">✓</span>
            <span><strong>USDT Sovrano (Rete Polygon):</strong> Pagamento istantaneo Web3 all'indirizzo di tesoreria con zero commissioni nascoste.</span>
          </li>
          <li style="display: flex; align-items: center; gap: 8px;">
            <span style="color: var(--neon-green); font-weight: 800;">✓</span>
            <span><strong>Bonifico Bancario:</strong> Generazione automatica distinta con IBAN aziendale dedicato.</span>
          </li>
        </ul>
      </div>

      <div class="col-lg-5">
        <div style="background: rgba(8, 12, 22, 0.9); border: 1px solid var(--neon-cyan); border-radius: 18px; padding: 20px; text-align: center;">
          <div style="font-size: 0.76rem; font-weight: 800; color: var(--neon-cyan); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
            WALLET UFFICIALE TESORERIA POLYGON
          </div>
          <code style="display: block; background: rgba(0,0,0,0.5); padding: 8px; border-radius: 8px; color: #FFFFFF; font-size: 0.78rem; word-break: break-all; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.1);">
            0x3C320B3a0917fF44BF6551CDdee44402AFcF250C
          </code>
          <div style="display: flex; justify-content: center; margin-bottom: 12px;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=0x3C320B3a0917fF44BF6551CDdee44402AFcF250C" alt="QR Wallet Polygon" style="width: 120px; height: 120px; border-radius: 10px; background: #FFF; padding: 4px;">
          </div>
          <button type="button" class="btn small" style="border: 1px solid var(--neon-cyan); color: var(--neon-cyan); border-radius: 8px; font-size: 0.8rem;" onclick="navigator.clipboard.writeText('0x3C320B3a0917fF44BF6551CDdee44402AFcF250C');alert('Indirizzo wallet copiato negli appunti!');">
            Copia Indirizzo Wallet
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- CONCIERGE WHATSAPP RISERVATO                                   -->
  <!-- ============================================================== -->
  <section class="luxury-hero-card lux-metallic-card p-4 p-md-5 text-center my-5" style="border: 1px solid rgba(0, 212, 255, 0.35); border-radius: 22px;">
    <div class="badge-neon-rainbow mb-2" style="font-size: 0.74rem;">
      <span class="dot"></span>
      <span class="text-rainbow">SERVIZIO CONCIERGE DEDICATO</span>
    </div>
    <h3 style="font-family: var(--font-serif); font-size: 1.8rem; color: #FFFFFF; margin-bottom: 0.75rem;">
      Hai domande specifiche sulla cabina, sulla rotta o sull'accompagnamento?
    </h3>
    <p style="color: #cbd5e1; max-width: 660px; margin: 0 auto 1.5rem; font-size: 1rem; line-height: 1.6;">
      Contatta direttamente il team di bordo <strong>BEWAY.LIFE</strong> tramite WhatsApp per una chiamata conoscitiva riservata senza alcun impegno.
    </p>
    <a href="https://wa.me/393478844271?text=<?=urlencode('Buongiorno, vorrei informazioni personalizzate sulla Crociera della Rinascita BEWAY.LIFE.')?>" target="_blank" rel="noopener" class="btn" style="background: #25D366; color: #000; font-weight: 800; padding: 14px 32px; border-radius: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
      <?=dx_icon('whatsapp', '', 20)?> Chatta con il Concierge (+39 347 884 4271)
    </a>
  </section>

</main>

<?php require '_footer.php'; ?>
