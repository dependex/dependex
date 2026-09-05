<?php
require_once 'bootstrap.php';
$pageTitle = 'Offerte Irrifiutabili · Protocollo M.A.G.I.C. Offer';
$metaDesc = 'Scala Valore e Offerte Irrifiutabili costruite secondo il Protocollo M.A.G.I.C. Offer: pacchetti trasparenti, bonus chirurgici e garanzia totale in puro oro e nero.';
require '_header.php';
?>

<main class="container py-5">
  <!-- Hero Section -->
  <section class="text-center mb-5">
    <div class="gold-glow-badge mb-3">
      <?=dx_icon('sparkles', '', 14)?>
      <span>ARCHITETTURA DELLE OFFERTE IRRIFIUTABILI · M.A.G.I.C. PROTOCOL</span>
    </div>
    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4.2vw, 3.2rem); font-weight: 800; line-height: 1.15; margin-bottom: 1rem; color: #FFFFFF;">
      Non sei a centinaia di promesse di distanza.<br>
      Sei a <span class="gold-foil-text">un’Offerta Irrifiutabile</span> di distanza.
    </h1>
    <p class="mx-auto" style="max-width: 720px; font-size: 1.1rem; line-height: 1.7; color: #d1d5db;">
      Facciamo due conti: quanto ti è costato finora rimandare? Tra alcol, serate di cui vergognarsi, cali di produttività e liti in famiglia, la dipendenza ti addebita migliaia di euro ogni anno. Con il <strong>Protocollo M.A.G.I.C. Offer</strong> invertiamo l'equazione: pacchetti di valore concreto, bonus chirurgici che eliminano ogni scusa e garanzie blindate che assorbono tutto il rischio.
    </p>
  </section>

  <!-- Value Ladder / Pricing Cards Grid -->
  <section class="mb-5">
    <div class="offers-ladder-grid">

      <!-- TIER 1: Offerta Magnetica Iniziale -->
      <div class="offer-card lux-metallic-card">
        <div class="offer-header">
          <span class="dx-ticker-badge" style="margin-bottom: 10px;">LIV. 1 · TRIPWIRE DIAGNOSTICO</span>
          <h3 class="offer-tier-title" style="color: #FFFFFF; font-family: var(--font-serif);">Starter Kit & Diagnosi</h3>
          <p class="offer-tier-sub" style="color: #a1a1aa;">Il primo passo per entrare in contatto con il metodo, testare la rete e sbloccare gli strumenti essenziali.</p>
          <div class="offer-pricing-box" style="border-color: rgba(212,175,55,0.25);">
            <span class="offer-anchor-val" style="color: #71717a;">Valore reale: € 190</span>
            <div class="offer-price" style="color: #FFFFFF;">€ 27 <small style="color: #D4AF37;">/ una tantum</small></div>
          </div>
        </div>

        <ul class="offer-stack" style="list-style: none; padding: 0;">
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Check-up diagnostico iniziale & guida pratica al metodo</span>
            <span class="item-val" style="color: #71717a;">€ 70</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Accesso completo 30gg all'assistente AI Cortex</span>
            <span class="item-val" style="color: #71717a;">€ 50</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Mappa operativa dei 542 Club e canali territoriali</span>
            <span class="item-val" style="color: #71717a;">€ 70</span>
          </li>
        </ul>

        <div class="offer-bonus-box" style="background: rgba(212,175,55,0.06); border-left: 3px solid #D4AF37;">
          <div class="offer-bonus-tag" style="color: #D4AF37;"><?=dx_icon('sparkles', '', 12)?> BONUS CHIRURGICO INCLUSO</div>
          <div class="offer-bonus-desc" style="color: #e5e7eb;"><strong>Cassetta Attrezzi Primo Giorno:</strong> Checklist di orientamento rapido per non sentirti mai disorientato. (Valore: € 47)</div>
        </div>

        <div class="offer-guarantee-box" style="background: rgba(16,17,23,0.9); border: 1px solid rgba(212,175,55,0.3); color: #d1d5db;">
          <strong style="color: #FFFFFF; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
            <?=dx_icon('shield-check', '', 16)?> Garanzia "Zero Rischio Totale"
          </strong>
          Se entro 30 giorni non riscontri chiarezza immediata, ti rimborsiamo al 100% con un semplice messaggio. Senza fare domande.
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;">
          <button class="btn-magic-cta outline" style="border-color: rgba(212,175,55,0.5); color: #FFFFFF; flex:1;" onclick="window.dxCommerce.buyNow('off_starter_kit')">
            Acquista Ora · € 27
          </button>
          <button class="btn" style="border-color: rgba(212,175,55,0.3); color: #FFF; padding:12px 14px;" onclick="window.dxCommerce.addToCart('off_starter_kit')" title="Aggiungi al carrello">
            <?=dx_icon('shopping-cart', '', 18)?>
          </button>
        </div>
      </div>

      <!-- TIER 2: Offerta Principale (Core Offer) -->
      <div class="offer-card featured lux-metallic-card" style="border: 2px solid #D4AF37; box-shadow: 0 0 35px rgba(212,175,55,0.25);">
        <div class="offer-badge" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; display: flex; align-items: center; gap: 4px;">
          <?=dx_icon('award', '', 14)?> SCELTA DAL 78% DEI MEMBRI
        </div>
        <div class="offer-header">
          <span class="dx-ticker-badge" style="margin-bottom: 10px; background: rgba(212,175,55,0.25); color: #FFF2B2;">LIV. 2 · CORE OFFER TRASFORMATIVA</span>
          <h3 class="offer-tier-title" style="color: #FFFFFF; font-family: var(--font-serif);">Protocollo Completo & Trasformazione</h3>
          <p class="offer-tier-sub" style="color: #a1a1aa;">Il cuore operativo dell'ecosistema: il metodo proprietario in 5 fasi per il cambiamento radicale e duraturo.</p>
          <div class="offer-pricing-box" style="border-color: rgba(212,175,55,0.4);">
            <span class="offer-anchor-val" style="color: #D4AF37;">Valore totale accorpato: € 2.588</span>
            <div class="offer-price" style="color: #FFFFFF;">€ 497 <small style="color: #D4AF37;">o 3 rate da € 185</small></div>
          </div>
        </div>

        <ul class="offer-stack" style="list-style: none; padding: 0;">
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span><strong style="color: #FFFFFF;">Piattaforma Operativa & Academy:</strong> Accesso illimitato a moduli formativi, diario e registro</span>
            <span class="item-val" style="color: #71717a;">€ 997</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span><strong style="color: #FFFFFF;">Schema Logico Proprietario:</strong> Le 5 fasi operative certificate per l'autonomia</span>
            <span class="item-val" style="color: #71717a;">€ 497</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span><strong style="color: #FFFFFF;">Sessioni di Revisione & Supporto:</strong> Affiancamento costante con facilitatori e pari esperti</span>
            <span class="item-val" style="color: #71717a;">€ 600</span>
          </li>
        </ul>

        <div class="offer-bonus-box" style="background: rgba(212,175,55,0.08); border-left: 3px solid #D4AF37;">
          <div class="offer-bonus-tag" style="color: #D4AF37;"><?=dx_icon('sparkles', '', 12)?> 2 BONUS CHIRURGICI ANTI-OBIEZIONE</div>
          <div class="offer-bonus-desc mb-2" style="color: #e5e7eb;">
            <strong>1. Audit & Diagnosi 1-a-1:</strong> Sessione strategica personale per sbloccare ogni punto cieco. <em>(Elimina la paura di fallire da soli · Valore: € 297)</em>
          </div>
          <div class="offer-bonus-desc" style="color: #e5e7eb;">
            <strong>2. Cassetta Script & Procedure Pronte:</strong> Formati già compilati per risparmiare tempo prezioso. <em>(Elimina l'obiezione "non ho tempo" · Valore: € 197)</em>
          </div>
        </div>

        <div class="offer-guarantee-box" style="background: rgba(16,17,23,0.95); border: 1px solid rgba(212,175,55,0.45); color: #d1d5db;">
          <strong style="color: #FFFFFF; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
            <?=dx_icon('shield-check', '', 16)?> Garanzia "Trasformazione o Rimborso Integrale"
          </strong>
          Se segui il protocollo per 60 giorni e non ottieni progressi concreti, ti restituiamo il 100% dell'importo e ti regaliamo 1 ora di consulenza specialistica.
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;">
          <button class="btn-magic-cta primary" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; flex:1;" onclick="window.dxCommerce.buyNow('off_core_proto')">
            Inizia Ora il Percorso Completo · € 497
          </button>
          <button class="btn" style="border-color: rgba(212,175,55,0.4); color: #FFF; padding:12px 14px;" onclick="window.dxCommerce.addToCart('off_core_proto')" title="Aggiungi al carrello">
            <?=dx_icon('shopping-cart', '', 18)?>
          </button>
        </div>
      </div>

      <!-- TIER 3: Offerta Massimizzante (High-Ticket) -->
      <div class="offer-card lux-metallic-card">
        <div class="offer-header">
          <span class="dx-ticker-badge" style="margin-bottom: 10px;">LIV. 3 · ALTO CONTATTO & DEDICATO</span>
          <h3 class="offer-tier-title" style="color: #FFFFFF; font-family: var(--font-serif);">Programma Elite & Affiancamento</h3>
          <p class="offer-tier-sub" style="color: #a1a1aa;">Per organizzazioni, responsabili e figure guida che necessitano di implementazione assistita e tempi dimezzati.</p>
          <div class="offer-pricing-box" style="border-color: rgba(212,175,55,0.25);">
            <span class="offer-anchor-val" style="color: #71717a;">Valore reale: € 6.500</span>
            <div class="offer-price" style="color: #FFFFFF;">€ 1.997 <small style="color: #D4AF37;">/ programma completo</small></div>
          </div>
        </div>

        <ul class="offer-stack" style="list-style: none; padding: 0;">
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Tutto ciò che è incluso nel Protocollo Completo</span>
            <span class="item-val" style="color: #71717a;">€ 2.588</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Team dedicato con canale di comunicazione diretto prioritario</span>
            <span class="item-val" style="color: #71717a;">€ 2.000</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Audit trimestrale e supervisione personalizzata continua</span>
            <span class="item-val" style="color: #71717a;">€ 1.200</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Integrazione Web3 & notarizzazione certificata della conformità</span>
            <span class="item-val" style="color: #71717a;">€ 712</span>
          </li>
        </ul>

        <div class="offer-bonus-box" style="background: rgba(212,175,55,0.06); border-left: 3px solid #D4AF37;">
          <div class="offer-bonus-tag" style="color: #D4AF37;"><?=dx_icon('crown', '', 12)?> BONUS RISERVATO</div>
          <div class="offer-bonus-desc" style="color: #e5e7eb;"><strong>Masterclass Riservata Leaders:</strong> Accesso prioritario a workshop e tavoli di lavoro ristretti. (Valore: € 500)</div>
        </div>

        <div class="offer-guarantee-box" style="background: rgba(16,17,23,0.9); border: 1px solid rgba(212,175,55,0.3); color: #d1d5db;">
          <strong style="color: #FFFFFF; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
            <?=dx_icon('shield-check', '', 16)?> Garanzia "Patto d'Onore & Risultato"
          </strong>
          Lavoriamo con te fino a quando il tuo Club o team non raggiunge la piena operatività e autosufficienza certificata.
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;">
          <button class="btn-magic-cta outline" style="border-color: rgba(212,175,55,0.5); color: #FFFFFF; flex:1;" onclick="window.dxCommerce.buyNow('off_elite_mentor')">
            Candidati al Programma Elite · € 1.997
          </button>
          <button class="btn" style="border-color: rgba(212,175,55,0.3); color: #FFF; padding:12px 14px;" onclick="window.dxCommerce.addToCart('off_elite_mentor')" title="Aggiungi al carrello">
            <?=dx_icon('shopping-cart', '', 18)?>
          </button>
        </div>
      </div>

      <!-- TIER 4: Offerta Ricorrente (Continuity) -->
      <div class="offer-card lux-metallic-card">
        <div class="offer-header">
          <span class="dx-ticker-badge" style="margin-bottom: 10px;">LIV. 4 · MEMBERSHIP CONTINUA</span>
          <h3 class="offer-tier-title" style="color: #FFFFFF; font-family: var(--font-serif);">Club Permanente & Supporto</h3>
          <p class="offer-tier-sub" style="color: #a1a1aa;">La sicurezza di una presenza continua, aggiornamenti normativi e intelligenza collettiva sempre al tuo fianco.</p>
          <div class="offer-pricing-box" style="border-color: rgba(212,175,55,0.25);">
            <span class="offer-anchor-val" style="color: #71717a;">Presenza costante 24/7</span>
            <div class="offer-price" style="color: #FFFFFF;">€ 39 <small style="color: #D4AF37;">/ mese (o € 390 / anno)</small></div>
          </div>
        </div>

        <ul class="offer-stack" style="list-style: none; padding: 0;">
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Accesso continuo agli incontri e alle stanze settimanali protette</span>
            <span class="item-val" style="color: #71717a;">Mensile</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Company Brain Cortex sempre sincronizzato e aggiornato</span>
            <span class="item-val" style="color: #71717a;">24/7</span>
          </li>
          <li class="offer-stack-item">
            <span style="color: #D4AF37;"><?=dx_icon('check-circle', '', 16)?></span>
            <span>Nuove procedure, schede metodologiche e webinar mensili</span>
            <span class="item-val" style="color: #71717a;">Incluso</span>
          </li>
        </ul>

        <div class="offer-bonus-box" style="background: rgba(212,175,55,0.06); border-left: 3px solid #D4AF37;">
          <div class="offer-bonus-tag" style="color: #D4AF37;"><?=dx_icon('sparkles', '', 12)?> VANTAGGIO PIANO ANNUALE</div>
          <div class="offer-bonus-desc" style="color: #e5e7eb;">Con il piano annuale ricevi <strong>2 mesi gratis</strong> e la consulenza di orientamento individuale inclusa.</div>
        </div>

        <div class="offer-guarantee-box" style="background: rgba(16,17,23,0.9); border: 1px solid rgba(212,175,55,0.3); color: #d1d5db;">
          <strong style="color: #FFFFFF; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
            <?=dx_icon('shield-check', '', 16)?> Libertà Assoluta
          </strong>
          Nessun vincolo. Puoi mettere in pausa o disdire in qualsiasi momento con 1 solo click dalla tua dashboard personale.
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;">
          <button class="btn-magic-cta outline" style="border-color: rgba(212,175,55,0.5); color: #FFFFFF; flex:1;" onclick="window.dxCommerce.buyNow('off_club_continuity')">
            Attiva Membership Mensile · € 39/m
          </button>
          <button class="btn" style="border-color: rgba(212,175,55,0.3); color: #FFF; padding:12px 14px;" onclick="window.dxCommerce.addToCart('off_club_continuity')" title="Aggiungi al carrello">
            <?=dx_icon('shopping-cart', '', 18)?>
          </button>
        </div>
      </div>

    </div>
  </section>

  <!-- EPPPA Objections-Killing Accordion -->
  <section class="epppa-section lux-metallic-card p-4 p-md-5 my-5" style="border: 1px solid rgba(212,175,55,0.25);">
    <div class="text-center mb-4">
      <div class="gold-glow-badge mb-2">
        <?=dx_icon('shield', '', 14)?>
        <span>EPPPA OBJECTIONS FRAMEWORK</span>
      </div>
      <h2 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF;">Domande Frequenti & Risposte Chiare</h2>
      <p style="color: #a1a1aa; max-width: 600px; margin: 0 auto;">Risolviamo subito i dubbi più comuni prima di iniziare.</p>
    </div>

    <div class="epppa-item" style="border-color: rgba(212,175,55,0.2);">
      <div class="epppa-header" onclick="toggleFaq(this)" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; color: #FFFFFF; font-weight: 700;">
        <span>È un corso teorico o un percorso operativo concreto?</span>
        <span style="color: #D4AF37;"><?=dx_icon('arrow-right', '', 16)?></span>
      </div>
      <div class="epppa-body" style="color: #d1d5db; line-height: 1.6; padding-top: 12px;">
        <strong>Zero teoria astratta.</strong> È un protocollo operativo strutturato in checklist, guide passo-passo e strumenti che applichi fin dal primo giorno, supportato dai facilitatori e dal nostro assistente cognitivo Cortex.
      </div>
    </div>

    <div class="epppa-item" style="border-color: rgba(212,175,55,0.2);">
      <div class="epppa-header" onclick="toggleFaq(this)" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; color: #FFFFFF; font-weight: 700;">
        <span>E se ho poco tempo a disposizione durante la settimana?</span>
        <span style="color: #D4AF37;"><?=dx_icon('arrow-right', '', 16)?></span>
      </div>
      <div class="epppa-body" style="color: #d1d5db; line-height: 1.6; padding-top: 12px;">
        Abbiamo progettato il metodo tenendo conto di chi lavora o ha impegni familiari: il materiale è fruibile in moduli concisi da 15-20 minuti e la cassetta degli attrezzi contiene modelli e script pronti per evitare di reinventare la ruota.
      </div>
    </div>

    <div class="epppa-item" style="border-color: rgba(212,175,55,0.2);">
      <div class="epppa-header" onclick="toggleFaq(this)" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; color: #FFFFFF; font-weight: 700;">
        <span>Come funziona esattamente la garanzia di rimborso?</span>
        <span style="color: #D4AF37;"><?=dx_icon('arrow-right', '', 16)?></span>
      </div>
      <div class="epppa-body" style="color: #d1d5db; line-height: 1.6; padding-top: 12px;">
        Le nostre garanzie sono indistruttibili: se per qualsiasi ragione ritieni che il percorso non faccia per te, ti basta inviare un messaggio entro il periodo stabilito (30 o 60 giorni a seconda dell'offerta) e l'intero importo versato ti sarà riaccreditato immediatamente.
      </div>
    </div>

    <div class="epppa-item" style="border-color: rgba(212,175,55,0.2);">
      <div class="epppa-header" onclick="toggleFaq(this)" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; color: #FFFFFF; font-weight: 700;">
        <span>Come posso pagare? Posso dividere l'importo a rate?</span>
        <span style="color: #D4AF37;"><?=dx_icon('arrow-right', '', 16)?></span>
      </div>
      <div class="epppa-body" style="color: #d1d5db; line-height: 1.6; padding-top: 12px;">
        Accettiamo carte di credito, debito, bonifico e pagamenti Web3 (criptovalute). Per l'Offerta Principale è disponibile l'opzione in 3 rate mensili senza interessi aggiuntivi.
      </div>
    </div>
  </section>

  <!-- Cortex Advisor Callout -->
  <section class="luxury-hero-card lux-metallic-card p-4 p-md-5 text-center my-5" style="border: 1px solid rgba(212,175,55,0.3);">
    <h3 style="font-family: var(--font-serif); font-size: 1.8rem; color: #FFFFFF; margin-bottom: 0.75rem;">
      Non sai quale livello della Scala Valore scegliere?
    </h3>
    <p style="color: #a1a1aa; max-width: 620px; margin: 0 auto 1.5rem; font-size: 1rem; line-height: 1.6;">
      Parla con <strong>Cortex</strong>, il Company Brain dell'ecosistema: analizzerà il tuo punto di partenza e ti consiglierà l'opzione più adatta al tuo obiettivo con assoluta sincerità.
    </p>
    <a href="cortex.php?q=<?=urlencode("Aiutami a scegliere l'offerta più adatta alla mia situazione")?>" class="btn primary" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; padding: 0 28px; text-decoration: none;">
      <?=dx_icon('brain', '', 18)?>
      <span style="margin-left: 8px;">Consulta Cortex in Tempo Reale</span>
    </a>
  </section>
</main>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true" style="display:none; background: rgba(0,0,0,0.85);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: #101116; border: 1px solid #D4AF37; border-radius: 20px; color: #fff; box-shadow: 0 20px 60px rgba(0,0,0,0.9);">
      <div class="modal-header border-0 pb-0" style="padding: 24px 24px 10px;">
        <h5 class="modal-title" id="checkoutTitle" style="font-family: var(--font-serif); color: #FFF2B2; font-weight: 800;">Conferma Selezione Offerta</h5>
        <button type="button" class="btn-close btn-close-white" onclick="closeModal()" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body" style="padding: 10px 24px 24px;">
        <p style="color: #a1a1aa; margin-bottom: 12px; font-size: 0.9rem;">Hai selezionato:</p>
        <div class="p-3 mb-3 rounded-3" style="background: rgba(212,175,55,0.08); border-left: 3px solid #D4AF37;">
          <strong id="modalTierName" class="d-block text-white fs-5"></strong>
          <span id="modalTierPrice" style="color: #D4AF37; font-weight: 800; font-size: 1.5rem;"></span>
        </div>
        <form id="orderForm" onsubmit="submitOrder(event)">
          <div class="mb-3">
            <label class="form-label small" style="color: #a1a1aa;">Nome e Cognome</label>
            <input type="text" class="form-control" id="orderName" required style="background: #070709; border-color: rgba(212,175,55,0.3); color: #fff; border-radius: 12px; padding: 12px;">
          </div>
          <div class="mb-3">
            <label class="form-label small" style="color: #a1a1aa;">Email di Contatto</label>
            <input type="email" class="form-control" id="orderEmail" required style="background: #070709; border-color: rgba(212,175,55,0.3); color: #fff; border-radius: 12px; padding: 12px;">
          </div>
          <div class="mb-3">
            <label class="form-label small" style="color: #a1a1aa;">Metodo di Pagamento</label>
            <select class="form-select" id="orderMethod" style="background: #070709; border-color: rgba(212,175,55,0.3); color: #fff; border-radius: 12px; padding: 12px;">
              <option value="carta">Carta di Credito / Debito</option>
              <option value="bonifico">Bonifico Bancario</option>
              <option value="crypto">Criptovaluta / Web3</option>
            </select>
          </div>
          <button type="submit" class="btn-magic-cta primary mt-3" style="width: 100%; background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; border-radius: 14px; padding: 14px;">
            Procedi all'Attivazione
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
let currentTier = { name: '', price: 0 };

function selectTier(name, price) {
  currentTier = { name, price };
  document.getElementById('modalTierName').textContent = name;
  document.getElementById('modalTierPrice').textContent = '€ ' + price;
  const m = document.getElementById('checkoutModal');
  m.style.display = 'block';
  m.classList.add('show');
}

function closeModal() {
  const m = document.getElementById('checkoutModal');
  m.style.display = 'none';
  m.classList.remove('show');
}

function toggleFaq(btn) {
  const body = btn.nextElementSibling;
  const isShown = body.style.display === 'block';
  document.querySelectorAll('.epppa-body').forEach(el => el.style.display = 'none');
  body.style.display = isShown ? 'none' : 'block';
}

async function submitOrder(e) {
  e.preventDefault();
  const name = document.getElementById('orderName').value;
  const email = document.getElementById('orderEmail').value;
  alert(`Richiesta per "${currentTier.name}" registrata con successo per ${name} (${email}). Riceverai tutti i dettagli via email.`);
  closeModal();
}
</script>

<script src="assets/js/universal-cart-sdk.js"></script>

<?php require '_footer.php'; ?>

