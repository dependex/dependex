<?php
require_once 'bootstrap.php';
$pageTitle = 'Offerte Irrifiutabili · Protocollo M.A.G.I.C. Offer';
$metaDesc = 'Scopri la Scala Valore e le Offerte Irrifiutabili costruite secondo il Protocollo M.A.G.I.C. Offer: pacchetti trasparenti, bonus chirurgici e garanzia totale.';
require '_header.php';
?>

<main class="container py-5">
  <!-- Hero Section -->
  <section class="text-center mb-5">
    <div class="d-inline-block px-3 py-1 mb-3 border rounded-pill" style="border-color: rgba(201,168,76,0.3); background: rgba(201,168,76,0.06);">
      <span style="font-size: 0.8rem; font-weight: 700; letter-spacing: 0.12em; color: var(--color-gold); text-transform: uppercase;">
        ✦ Architettura delle Offerte Irrifiutabili
      </span>
    </div>
    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 700; line-height: 1.15; margin-bottom: 1rem;">
      Non sei a centinaia di follower di distanza.<br>
      Sei a <span class="gold-gradient-text">un’Offerta Irrifiutabile</span> di distanza.
    </h1>
    <p class="mx-auto text-muted" style="max-width: 680px; font-size: 1.1rem; line-height: 1.6;">
      Costruite con il <strong>Protocollo M.A.G.I.C. Offer</strong>: niente sconti al ribasso, ma pacchetti di valore autentico, bonus chirurgici anti-obiezione e garanzie indistruttibili con nome proprio.
    </p>
  </section>

  <!-- Value Ladder / Pricing Cards Grid -->
  <section class="mb-5">
    <div class="offers-ladder-grid">

      <!-- TIER 1: Offerta Magnetica Iniziale -->
      <div class="offer-card">
        <div class="offer-header">
          <span class="badge mb-2" style="background: rgba(255,255,255,0.08); color: #cbd5e1; font-weight: 600;">LIV. 1 · TRIPWIRE</span>
          <h3 class="offer-tier-title">Starter Kit & Diagnosi</h3>
          <p class="offer-tier-sub">Il primo passo per entrare in contatto con il metodo, testare la rete e sbloccare gli strumenti essenziali.</p>
          <div class="offer-pricing-box">
            <span class="offer-anchor-val">Valore reale: € 190</span>
            <div class="offer-price">€ 27 <small>/ una tantum</small></div>
          </div>
        </div>

        <ul class="offer-stack">
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Check-up diagnostico iniziale & guida pratica al metodo</span>
            <span class="item-val">€ 70</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Accesso completo 30gg all'assistente AI Cortex</span>
            <span class="item-val">€ 50</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Mappa operativa dei Club e canali territoriali</span>
            <span class="item-val">€ 70</span>
          </li>
        </ul>

        <div class="offer-bonus-box">
          <div class="offer-bonus-tag">🎁 BONUS CHIRURGICO INCLUSO</div>
          <div class="offer-bonus-desc"><strong>Cassetta Attrezzi Primo Giorno:</strong> Checklist di orientamento rapido per non sentirti disorientato. (Valore: € 47)</div>
        </div>

        <div class="offer-guarantee-box">
          <strong>🛡️ Garanzia "Zero Rischio Totale"</strong>
          Se entro 30 giorni non trovi chiarezza immediata, ti rimborsiamo al 100% con un semplice messaggio. Senza domande.
        </div>

        <button class="btn-magic-cta outline" onclick="selectTier('Starter Kit & Diagnosi', 27)">
          Accedi con € 27
        </button>
      </div>

      <!-- TIER 2: Offerta Principale (Core Offer) -->
      <div class="offer-card featured">
        <div class="offer-badge">⭐ PIÙ SCELTA DAL 78% DEI MEMBRI</div>
        <div class="offer-header">
          <span class="badge mb-2" style="background: rgba(201,168,76,0.2); color: var(--color-gold-light); font-weight: 700;">LIV. 2 · CORE OFFER</span>
          <h3 class="offer-tier-title">Protocollo Completo & Trasformazione</h3>
          <p class="offer-tier-sub">Il cuore economico e operativo dell'ecosistema: il metodo proprietario passo-passo per il cambiamento definitivo.</p>
          <div class="offer-pricing-box">
            <span class="offer-anchor-val">Valore totale accorpato: € 2.588</span>
            <div class="offer-price">€ 497 <small>o 3 rate da € 185</small></div>
          </div>
        </div>

        <ul class="offer-stack">
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span><strong>Piattaforma Operativa & Academy:</strong> Accesso illimitato a moduli formativi, diario e registro</span>
            <span class="item-val">€ 997</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span><strong>Schema Logico Proprietario:</strong> Le 5 fasi operative certificate per l'autonomia</span>
            <span class="item-val">€ 497</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span><strong>Sessioni di Revisione & Supporto Continuo:</strong> Affiancamento con facilitatori esperti</span>
            <span class="item-val">€ 600</span>
          </li>
        </ul>

        <div class="offer-bonus-box">
          <div class="offer-bonus-tag">🎁 2 BONUS CHIRURGICI ANTI-OBIEZIONE</div>
          <div class="offer-bonus-desc mb-2">
            <strong>1. Audit & Diagnosi 1-a-1:</strong> Sessione strategica personale per sbloccare ogni punto cieco. <em>(Uccide la paura di fallire da soli · Valore: € 297)</em>
          </div>
          <div class="offer-bonus-desc">
            <strong>2. Cassetta Script & Procedure Pronte:</strong> Formati già compilati per risparmiare tempo prezioso. <em>(Uccide l'obiezione "non ho tempo" · Valore: € 197)</em>
          </div>
        </div>

        <div class="offer-guarantee-box">
          <strong>🛡️ Garanzia "Trasformazione o Rimborso Integrale"</strong>
          Se segui il protocollo per 60 giorni e non ottieni progressi concreti, ti restituiamo il 100% dell'importo e ti regaliamo 1 ora di consulenza specialistica.
        </div>

        <button class="btn-magic-cta primary" onclick="selectTier('Protocollo Completo & Trasformazione', 497)">
          Inizia Ora il Percorso Completo
        </button>
      </div>

      <!-- TIER 3: Offerta Massimizzante (High-Ticket) -->
      <div class="offer-card">
        <div class="offer-header">
          <span class="badge mb-2" style="background: rgba(255,255,255,0.08); color: #cbd5e1; font-weight: 600;">LIV. 3 · ALTO CONTATTO</span>
          <h3 class="offer-tier-title">Programma Elite & Affiancamento</h3>
          <p class="offer-tier-sub">Per organizzazioni, responsabili e figure guida che necessitano di implementazione assistita e tempi dimezzati.</p>
          <div class="offer-pricing-box">
            <span class="offer-anchor-val">Valore reale: € 6.500</span>
            <div class="offer-price">€ 1.997 <small>/ programma completo</small></div>
          </div>
        </div>

        <ul class="offer-stack">
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Tutto ciò che è incluso nel Protocollo Completo</span>
            <span class="item-val">€ 2.588</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Team dedicato con canale di comunicazione diretto</span>
            <span class="item-val">€ 2.000</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Audit trimestrale e supervisione personalizzata</span>
            <span class="item-val">€ 1.200</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Integrazione Web3 & notarizzazione certificata</span>
            <span class="item-val">€ 712</span>
          </li>
        </ul>

        <div class="offer-bonus-box">
          <div class="offer-bonus-tag">🎁 BONUS RISERVATO</div>
          <div class="offer-bonus-desc"><strong>Masterclass Riservata Leaders:</strong> Accesso prioritario a workshop e tavoli di lavoro ristretti. (Valore: € 500)</div>
        </div>

        <div class="offer-guarantee-box">
          <strong>🛡️ Garanzia "Patto d'Onore & Risultato"</strong>
          Lavoriamo con te fino a quando il tuo Club o team non raggiunge la piena operatività e autosufficienza certificata.
        </div>

        <button class="btn-magic-cta outline" onclick="selectTier('Programma Elite & Affiancamento', 1997)">
          Candidati al Programma Elite
        </button>
      </div>

      <!-- TIER 4: Offerta Ricorrente (Continuity) -->
      <div class="offer-card">
        <div class="offer-header">
          <span class="badge mb-2" style="background: rgba(255,255,255,0.08); color: #cbd5e1; font-weight: 600;">LIV. 4 · CONTINUITY</span>
          <h3 class="offer-tier-title">Club Permanente & Supporto</h3>
          <p class="offer-tier-sub">La sicurezza di una presenza continua, aggiornamenti normativi e intelligenza collettiva sempre al tuo fianco.</p>
          <div class="offer-pricing-box">
            <span class="offer-anchor-val">Fatturato a picchi ➔ Prevedibile</span>
            <div class="offer-price">€ 39 <small>/ mese (o € 390 / anno)</small></div>
          </div>
        </div>

        <ul class="offer-stack">
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Accesso continuo agli incontri e alle stanze settimanali</span>
            <span class="item-val">Mensile</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Company Brain Cortex sempre sincronizzato e aggiornato</span>
            <span class="item-val">24/7</span>
          </li>
          <li class="offer-stack-item">
            <span class="item-icon">✓</span>
            <span>Nuove procedure, schede metodologiche e webinar mensili</span>
            <span class="item-val">Incluso</span>
          </li>
        </ul>

        <div class="offer-bonus-box">
          <div class="offer-bonus-tag">🎁 VANTAGGIO ABBONAMENTO</div>
          <div class="offer-bonus-desc">Con il piano annuale ricevi <strong>2 mesi gratis</strong> e la consulenza di orientamento inclusa.</div>
        </div>

        <div class="offer-guarantee-box">
          <strong>🛡️ Libertà Assoluta</strong>
          Nessun vincolo temporale. Puoi mettere in pausa o disdire in qualsiasi momento con 1 solo click dalla tua dashboard.
        </div>

        <button class="btn-magic-cta outline" onclick="selectTier('Club Permanente & Supporto', 39)">
          Attiva Membership Mensile
        </button>
      </div>

    </div>
  </section>

  <!-- EPPPA Objections-Killing Accordion -->
  <section class="epppa-section">
    <div class="text-center mb-4">
      <h2 style="font-family: var(--font-serif); font-size: 2rem;">Domande Frequenti & Risposte Chiare</h2>
      <p class="text-muted">Risolviamo subito i dubbi più comuni prima di iniziare.</p>
    </div>

    <div class="epppa-item">
      <div class="epppa-header" onclick="toggleFaq(this)">
        <span>È un corso teorico o un percorso operativo concreto?</span>
        <span>▼</span>
      </div>
      <div class="epppa-body">
        <strong>Zero teoria astratta.</strong> È un protocollo operativo strutturato in checklist, guide passo-passo e strumenti che applichi fin dal primo giorno, supportato dai facilitatori e dal nostro assistente cognitivo Cortex.
      </div>
    </div>

    <div class="epppa-item">
      <div class="epppa-header" onclick="toggleFaq(this)">
        <span>E se ho poco tempo a disposizione durante la settimana?</span>
        <span>▼</span>
      </div>
      <div class="epppa-body">
        Abbiamo progettato il metodo tenendo conto di chi lavora o ha impegni familiari: il materiale è fruibile in moduli concisi da 15-20 minuti e la cassetta degli attrezzi contiene modelli e script pronti per evitare di reinventare la ruota.
      </div>
    </div>

    <div class="epppa-item">
      <div class="epppa-header" onclick="toggleFaq(this)">
        <span>Come funziona esattamente la garanzia di rimborso?</span>
        <span>▼</span>
      </div>
      <div class="epppa-body">
        Le nostre garanzie sono indistruttibili: se per qualsiasi ragione ritieni che il percorso non faccia per te, ti basta inviare un messaggio entro il periodo stabilito (30 o 60 giorni a seconda dell'offerta) e l'intero importo versato ti sarà riaccreditato immediatamente.
      </div>
    </div>

    <div class="epppa-item">
      <div class="epppa-header" onclick="toggleFaq(this)">
        <span>Come posso pagare? Posso dividere l'importo a rate?</span>
        <span>▼</span>
      </div>
      <div class="epppa-body">
        Accettiamo carte di credito, debito, bonifico e pagamenti Web3 (criptovalute). Per l'Offerta Principale è disponibile l'opzione in 3 rate mensili senza interessi aggiuntivi.
      </div>
    </div>
  </section>

  <!-- Cortex Advisor Callout -->
  <section class="luxury-hero-card p-4 text-center my-5">
    <h3 style="font-family: var(--font-serif); font-size: 1.6rem; color: #fff;">Non sai quale livello della Scala Valore scegliere?</h3>
    <p class="text-muted mx-auto mb-3" style="max-width: 600px;">
      Parla con <strong>Cortex</strong>, il Company Brain dell'ecosistema: analizzerà il tuo punto di partenza e ti consiglierà l'opzione più adatta al tuo obiettivo.
    </p>
    <a href="cortex.php?q=<?=urlencode("Aiutami a scegliere l'offerta più adatta alla mia situazione")?>" class="btn btn-warning fw-bold px-4 py-2" style="border-radius: 50px;">
      🧠 Consulta Cortex in Tempo Reale
    </a>
  </section>
</main>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true" style="display:none; background: rgba(0,0,0,0.85);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: var(--color-surface-card); border: 1px solid var(--color-gold); border-radius: var(--radius-md); color: #fff;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" id="checkoutTitle" style="font-family: var(--font-serif); color: var(--color-gold-light);">Conferma Selezione Offerta</h5>
        <button type="button" class="btn-close btn-close-white" onclick="closeModal()"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted mb-3">Hai selezionato:</p>
        <div class="p-3 mb-3 rounded" style="background: rgba(255,255,255,0.04); border-left: 3px solid var(--color-gold);">
          <strong id="modalTierName" class="d-block text-white fs-5"></strong>
          <span id="modalTierPrice" class="text-warning fw-bold fs-4"></span>
        </div>
        <form id="orderForm" onsubmit="submitOrder(event)">
          <div class="mb-3">
            <label class="form-label text-muted small">Nome e Cognome</label>
            <input type="text" class="form-control" id="orderName" required style="background: rgba(0,0,0,0.4); border-color: rgba(255,255,255,0.15); color: #fff;">
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small">Email di Contatto</label>
            <input type="email" class="form-control" id="orderEmail" required style="background: rgba(0,0,0,0.4); border-color: rgba(255,255,255,0.15); color: #fff;">
          </div>
          <div class="mb-3">
            <label class="form-label text-muted small">Metodo di Pagamento</label>
            <select class="form-select" id="orderMethod" style="background: rgba(0,0,0,0.4); border-color: rgba(255,255,255,0.15); color: #fff;">
              <option value="carta">Carta di Credito / Debito</option>
              <option value="bonifico">Bonifico Bancario</option>
              <option value="crypto">Criptovaluta / Web3</option>
            </select>
          </div>
          <button type="submit" class="btn-magic-cta primary mt-3">Procedi all'Attivazione</button>
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
  const method = document.getElementById('orderMethod').value;

  alert(`✅ Richiesta per "${currentTier.name}" registrata con successo per ${name} (${email}). Riceverai tutti i dettagli via email.`);
  closeModal();
}
</script>

<?php require '_footer.php'; ?>
