<?php
/**
 * FAST REGISTRATION & MULTI-GATEWAY CHECKOUT COMPONENT
 * Evento: Taglio di Po (9-10-11 Ottobre 2026) — 10.00 EUR
 * Gateways: PayPal Live (Carte/Conto), USDT Polygon (0x3C320B3a0917fF44BF6551CDdee44402AFcF250C), On-Site/WhatsApp
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/commerce/CommerceEnv.php';
require_once __DIR__ . '/../modules/commerce/PayPalService.php';

use Dependex\Commerce\PayPalService;

$eventSic = 'SIC-EVT-ACAT-BP-2026-COMM';
$pdo = db();
$evtStmt = $pdo->prepare("SELECT * FROM events WHERE sic_id = ?");
$evtStmt->execute([$eventSic]);
$fastEvt = $evtStmt->fetch(PDO::FETCH_ASSOC);

// Conteggi con fallback difensivo
$fastBooked = 0;
try {
    ensure_core_schema($pdo);
    $cntStmt = $pdo->prepare("
        SELECT (
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id = ? AND er.status IN ('REGISTERED', 'CHECKED_IN')) +
            (SELECT COALESCE(SUM(num_seats), 0) FROM event_bookings eb WHERE eb.event_sic_id = ? AND eb.status = 'CONFIRMED')
        ) as total_booked
    ");
    $cntStmt->execute([$eventSic, $eventSic]);
    $fastBooked = (int)$cntStmt->fetchColumn();
} catch (Throwable $e) {
    $fastBooked = 0;
}
$fastCap = (int)($fastEvt['capacity'] ?? 30);
$fastRemaining = max(0, $fastCap - $fastBooked);
$fastIsFull = ($fastRemaining <= 0);
$fastPercent = $fastCap > 0 ? min(100, round(($fastBooked / $fastCap) * 100)) : 0;
$paypalClientId = (string)CommerceEnv::get('PAYPAL_CLIENT_ID', '');
?>

<div class="fast-checkout-card" id="iscrizione-taglio-po" style="background: rgba(13, 17, 27, 0.95); border: 2px solid #d4af37; border-radius: 20px; padding: 22px; margin: 30px 0; box-shadow: 0 10px 40px rgba(0,0,0,0.6); position: relative; overflow: hidden;">
  
  <!-- GLOW AMBIENTALE -->
  <div style="position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; background: rgba(212,175,55,0.15); filter: blur(50px); pointer-events: none;"></div>

  <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 14px;">
    <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.4); padding: 4px 10px; border-radius: 8px; font-size: 0.76rem; font-weight: 850; color: #fef08a;">
      <?=dx_icon('award', '', 14)?> EVENTO UFFICIALE · TAGLIO DI PO (9-11 OTTOBRE 2026)
    </div>
    <div style="font-size: 0.8rem; font-weight: 800; color: <?=!$fastIsFull ? '#10b981' : '#ef4444'?>; background: rgba(0,0,0,0.5); padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
      <?=!$fastIsFull ? "{$fastRemaining} Posti Disponibili su {$fastCap}" : "Posti Esauriti (Waitlist Attiva)"?>
    </div>
  </div>

  <div class="row align-items-center g-4">
    <div class="col-lg-6">
      <h2 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 3.2vw, 1.95rem); color: #ffffff; font-weight: 900; line-height: 1.2; margin-bottom: 8px;">
        A Scuola di Comunicazione e Resilienza
      </h2>
      <p style="font-size: 0.94rem; color: #cbd5e1; line-height: 1.55; margin-bottom: 14px;">
        Tre giornate esperienziali con il <b>Dott. Adelmo Di Salvatore</b> (Psichiatra, Formatore Metodo Hudolin) per imparare a comunicare senza conflitti e non farsi travolgere dai problemi altrui.
      </p>

      <div style="background: rgba(22, 27, 40, 0.85); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 12px; margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; text-align: center;">
          <div>
            <div style="font-size: 0.68rem; color: #d4af37; font-weight: 800; text-transform: uppercase;">DATE</div>
            <div style="font-size: 0.82rem; font-weight: 850; color: #fff;">9-10-11 Ottobre</div>
          </div>
          <div>
            <div style="font-size: 0.68rem; color: #d4af37; font-weight: 800; text-transform: uppercase;">SEDE</div>
            <div style="font-size: 0.82rem; font-weight: 850; color: #fff;">Taglio di Po (RO)</div>
          </div>
          <div>
            <div style="font-size: 0.68rem; color: #d4af37; font-weight: 800; text-transform: uppercase;">QUOTA</div>
            <div style="font-size: 0.86rem; font-weight: 900; color: #10b981;">10,00 €</div>
          </div>
        </div>
        <div style="font-size: 0.72rem; color: #94a3b8; text-align: center; margin-top: 6px; border-top: 1px solid rgba(255,255,255,0.06); pt: 4px;">
          ✓ Include pranzo comunitario di sabato 10 e materiali didattici
        </div>
      </div>

      <!-- BARRA PROGRESSO POSTI -->
      <div style="margin-bottom: 14px;">
        <div style="display: flex; justify-content: space-between; font-size: 0.76rem; color: #cbd5e1; margin-bottom: 4px;">
          <span>Stato Iscrizioni Aula:</span>
          <b><?=$fastBooked?> / <?=$fastCap?> Iscritti</b>
        </div>
        <div class="m-progress-bar" style="background: rgba(255,255,255,0.1); border-radius: 999px; height: 8px; overflow: hidden;">
          <div style="width: <?=$fastPercent?>%; height: 100%; background: linear-gradient(90deg, #d4af37, #10b981); border-radius: 999px;"></div>
        </div>
      </div>

      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="evento-ottobre-taglio-di-po.php" class="btn-rainbow-outline" style="font-size: 0.82rem; padding: 6px 14px; border-color: rgba(212,175,55,0.5);">
          <?=dx_icon('info', '', 14)?> Dettagli Programma Completo
        </a>
        <a href="https://wa.me/393478844271?text=Ciao%20Grazia,%20sono%20[nome],%20mi%20interessa%20partecipare%20all'evento%20A%20Scuola%20di%20Comunicazione%20Resilienza%20a%20Taglio%20di%20Po." target="_blank" rel="noopener" class="btn-rainbow-outline" style="border-color: #25d366; color: #25d366; font-size: 0.82rem; padding: 6px 14px;">
          <?=dx_icon('message-circle', '', 14)?> Contatta Grazia (WhatsApp)
        </a>
        <a href="https://chat.whatsapp.com/Bx6mGOuLBTmC2rxTPp4Gel" target="_blank" rel="noopener" class="btn-rainbow-outline" style="border-color: #25d366; color: #25d366; font-size: 0.82rem; padding: 6px 14px;">
          <?=dx_icon('users', '', 14)?> Gruppo WhatsApp Evento
        </a>
      </div>
    </div>

    <!-- FORM DI ISCRIZIONE E CHECKOUT IMMEDIATO -->
    <div class="col-lg-6">
      <div style="background: rgba(18, 23, 36, 0.95); border: 1px solid rgba(212,175,55,0.3); border-radius: 16px; padding: 18px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
          <h3 style="font-size: 1.05rem; font-weight: 850; color: #ffffff; margin: 0;">
            <?=dx_icon('check-circle', '', 18)?> Iscrizione & Checkout Rapido (10 €)
          </h3>
          <span style="font-size: 0.72rem; color: #d4af37; font-weight: 800;">POSTI LIMITATI</span>
        </div>

        <div id="homeBookingAlert" style="display: none; padding: 10px 12px; border-radius: 10px; margin-bottom: 12px; font-size: 0.84rem; line-height: 1.45;"></div>

        <form id="homeFastBookingForm" onsubmit="handleHomeFastBooking(event)">
          <input type="hidden" name="action" value="init_booking">
          <input type="hidden" name="privacy" value="1">
          <input type="hidden" name="event_sic_id" value="<?=$eventSic?>">

          <div class="row g-2 mb-2">
            <div class="col-6">
              <label style="display: block; font-size: 0.72rem; font-weight: 750; color: #cbd5e1; margin-bottom: 2px;">Nome *</label>
              <input type="text" name="first_name" required placeholder="Mario" style="width: 100%; background: #0b0f19; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 10px; color: #fff; font-size: 0.84rem;">
            </div>
            <div class="col-6">
              <label style="display: block; font-size: 0.72rem; font-weight: 750; color: #cbd5e1; margin-bottom: 2px;">Cognome *</label>
              <input type="text" name="last_name" required placeholder="Rossi" style="width: 100%; background: #0b0f19; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 10px; color: #fff; font-size: 0.84rem;">
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col-6">
              <label style="display: block; font-size: 0.72rem; font-weight: 750; color: #cbd5e1; margin-bottom: 2px;">Telefono / WhatsApp *</label>
              <input type="tel" name="phone" required placeholder="Es. 347 1234567" style="width: 100%; background: #0b0f19; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 10px; color: #fff; font-size: 0.84rem;">
            </div>
            <div class="col-6">
              <label style="display: block; font-size: 0.72rem; font-weight: 750; color: #cbd5e1; margin-bottom: 2px;">Email *</label>
              <input type="email" name="email" required placeholder="mario@rossi.it" style="width: 100%; background: #0b0f19; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 10px; color: #fff; font-size: 0.84rem;">
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col-6">
              <label style="display: block; font-size: 0.72rem; font-weight: 750; color: #cbd5e1; margin-bottom: 2px;">Ruolo / Relazione</label>
              <select name="role_type" style="width: 100%; background: #0b0f19; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 10px; color: #fff; font-size: 0.82rem;">
                <option value="FAMILIARE">Familiare / Cittadino</option>
                <option value="MEMBRO_CLUB">Membro di un Club ACAT</option>
                <option value="SERVITORE">Servitore-Insegnante</option>
                <option value="OPERATORE">Operatore Sociale / Sanitario</option>
                <option value="ALTRO">Altro</option>
              </select>
            </div>
            <div class="col-6">
              <label style="display: block; font-size: 0.72rem; font-weight: 750; color: #cbd5e1; margin-bottom: 2px;">Note Pranzo (Sabato)</label>
              <input type="text" name="dietary_notes" placeholder="Vegetariano, celiaco, ecc." style="width: 100%; background: #0b0f19; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px 10px; color: #fff; font-size: 0.84rem;">
            </div>
          </div>

          <!-- SELETTORE MODALITÀ PAGAMENTO -->
          <div style="margin: 10px 0 12px;">
            <label style="display: block; font-size: 0.72rem; font-weight: 750; color: #cbd5e1; margin-bottom: 4px;">Metodo di Pagamento Quota (10,00 €):</label>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
              <label style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 6px; text-align: center; cursor: pointer; font-size: 0.76rem; color: #fff;">
                <input type="radio" name="payment_method" value="PAYPAL" checked style="margin-right: 3px;"> Carta/PayPal
              </label>
              <label style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 6px; text-align: center; cursor: pointer; font-size: 0.76rem; color: #8247e5; font-weight: 700;">
                <input type="radio" name="payment_method" value="USDT" style="margin-right: 3px;"> USDT Polygon
              </label>
              <label style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; padding: 6px; text-align: center; cursor: pointer; font-size: 0.76rem; color: #10b981; font-weight: 700;">
                <input type="radio" name="payment_method" value="ON_SITE" style="margin-right: 3px;"> All'Arrivo
              </label>
            </div>
          </div>

          <button type="submit" id="homeFastSubmitBtn" class="btn-rainbow-neon" style="width: 100%; justify-content: center; font-size: 0.88rem; padding: 10px;">
            <?=dx_icon('check-circle', '', 16)?>
            <span>ISCRIVITI & CONFERMA POSTO (10 €)</span>
          </button>
        </form>

        <!-- CONTAINER PAYPAL HOMEPAGE -->
        <div id="homePayPalContainer" style="display: none; margin-top: 14px; padding: 12px; background: rgba(0,0,0,0.5); border-radius: 12px; border: 1px solid rgba(212,175,55,0.3); text-align: center;">
          <div style="font-size: 0.82rem; color: #d4af37; font-weight: 800; margin-bottom: 8px;">
            Completa con Carta di Credito o Conto PayPal (10,00 €)
          </div>
          <div id="home_paypal_button_render"></div>
        </div>

        <!-- CONTAINER USDT HOMEPAGE -->
        <div id="homeUsdtContainer" style="display: none; margin-top: 14px; padding: 14px; background: rgba(130, 71, 229, 0.1); border-radius: 12px; border: 1px solid #8247e5; text-align: center;">
          <div style="font-size: 0.84rem; font-weight: 850; color: #ffffff; margin-bottom: 4px;">
            Invia 10 USDT su Rete Polygon
          </div>
          <div style="background: #fff; padding: 8px; display: inline-block; border-radius: 8px; margin-bottom: 8px;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=0x3C320B3a0917fF44BF6551CDdee44402AFcF250C" alt="QR USDT" style="width: 140px; height: 140px; display: block;">
          </div>
          <div style="background: rgba(0,0,0,0.6); padding: 6px; border-radius: 6px; font-family: monospace; font-size: 0.72rem; color: #fff; word-break: break-all; margin-bottom: 8px;">
            0x3C320B3a0917fF44BF6551CDdee44402AFcF250C
          </div>
          <button type="button" onclick="navigator.clipboard.writeText('0x3C320B3a0917fF44BF6551CDdee44402AFcF250C'); alert('Indirizzo Polygon copiato!');" class="btn-rainbow-outline" style="font-size: 0.74rem; padding: 4px 10px; margin-bottom: 10px;">
            <?=dx_icon('copy', '', 12)?> Copia Indirizzo
          </button>
          
          <div style="text-align: left; background: rgba(0,0,0,0.4); padding: 8px; border-radius: 8px;">
            <label style="font-size: 0.7rem; color: #cbd5e1; display: block; margin-bottom: 2px;">Inserisci la Tx Hash Polygon inviata:</label>
            <div style="display: flex; gap: 4px;">
              <input type="text" id="homeUsdtTxHash" placeholder="0x..." style="flex: 1; background: #000; border: 1px solid #555; border-radius: 6px; padding: 4px 8px; color: #fff; font-size: 0.76rem;">
              <button type="button" onclick="submitHomeUsdtTx()" class="btn-rainbow-neon" style="font-size: 0.74rem; padding: 4px 10px;">Conferma</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- SCRIPT PAYPAL SDK & FAST-BOOKING -->
<?php if (!empty($paypalClientId)): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?=htmlspecialchars($paypalClientId, ENT_QUOTES, 'UTF-8')?>&currency=EUR&intent=capture&enable-funding=card&disable-funding=paylater"></script>
<?php endif; ?>

<script>
let homeCurrentBookingSic = '';

async function handleHomeFastBooking(e) {
  e.preventDefault();
  const form = document.getElementById('homeFastBookingForm');
  const btn = document.getElementById('homeFastSubmitBtn');
  const alertBox = document.getElementById('homeBookingAlert');
  const ppContainer = document.getElementById('homePayPalContainer');
  const usdtContainer = document.getElementById('homeUsdtContainer');

  btn.disabled = true;
  btn.innerHTML = 'Registrazione in corso...';
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
      homeCurrentBookingSic = data.booking_sic;

      if (paymentMethod === 'PAYPAL') {
        form.style.display = 'none';
        ppContainer.style.display = 'block';
        renderHomePayPalButtons(homeCurrentBookingSic);
        return;
      }

      if (paymentMethod === 'USDT') {
        form.style.display = 'none';
        usdtContainer.style.display = 'block';
        return;
      }

      // On-site
      alertBox.style.display = 'block';
      alertBox.style.background = 'rgba(16, 185, 129, 0.2)';
      alertBox.style.border = '1px solid #10b981';
      alertBox.style.color = '#ffffff';
      alertBox.innerHTML = '<b>Iscrizione Registrata!</b> Codice: <strong style="color:#d4af37;">' + homeCurrentBookingSic + '</strong>. Quota di 10€ versabile all\'accoglienza. ' +
        (data.whatsapp_link ? '<br><a href="' + data.whatsapp_link + '" target="_blank" rel="noopener" style="color:#25d366; font-weight:bold; display:inline-block; margin-top:6px;">Avvisa Grazia su WhatsApp →</a>' : '');
      form.reset();

    } else {
      alertBox.style.display = 'block';
      alertBox.style.background = 'rgba(239, 68, 68, 0.2)';
      alertBox.style.border = '1px solid #ef4444';
      alertBox.style.color = '#ffffff';
      alertBox.innerText = data.error || data.message || 'Errore nella registrazione.';
    }
  } catch (err) {
    alertBox.style.display = 'block';
    alertBox.style.background = 'rgba(239, 68, 68, 0.2)';
    alertBox.style.border = '1px solid #ef4444';
    alertBox.style.color = '#ffffff';
    alertBox.innerText = 'Errore di connessione. Riprova.';
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<?=dx_icon("check-circle", "", 16)?> <span>ISCRIVITI & CONFERMA POSTO (10 €)</span>';
  }
}

function renderHomePayPalButtons(bookingSic) {
  if (typeof paypal === 'undefined') {
    document.getElementById('home_paypal_button_render').innerHTML = '<p style="color:#cbd5e1;">PayPal SDK non disponibile al momento. <a href="evento-ottobre-taglio-di-po.php" style="color:#d4af37;">Procedi dalla pagina evento →</a></p>';
    return;
  }
  document.getElementById('home_paypal_button_render').innerHTML = '';
  paypal.Buttons({
    createOrder: async function() {
      const resp = await fetch('api-event-booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'create_paypal_order', booking_sic: bookingSic })
      });
      const orderData = await resp.json();
      if (!orderData.success) throw new Error(orderData.error || 'Errore creazione ordine');
      return orderData.order_id;
    },
    onApprove: async function(data) {
      const captureResp = await fetch('api-event-booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'capture_paypal_order',
          paypal_order_id: data.orderID,
          booking_sic: bookingSic
        })
      });
      const res = await captureResp.json();
      if (res.success) {
        document.getElementById('homePayPalContainer').innerHTML = '<div style="color:#10b981; font-weight:bold; font-size:1.05rem;">Pagamento Ricevuto con Successo!</div><p style="color:#cbd5e1; font-size:0.86rem; margin:6px 0;">Il tuo posto è confermato. Codice: <b>' + bookingSic + '</b>.</p>';
      } else {
        alert(res.error || 'Errore durante la conferma.');
      }
    }
  }).render('#home_paypal_button_render');
}

async function submitHomeUsdtTx() {
  const tx = document.getElementById('homeUsdtTxHash').value.trim();
  if (!tx || tx.length < 10) {
    alert('Inserisci una Tx Hash Polygon valida.');
    return;
  }
  try {
    const res = await fetch('api-event-booking.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'confirm_usdt_payment',
        booking_sic: homeCurrentBookingSic,
        tx_hash: tx
      })
    });
    const d = await res.json();
    if (d.success) {
      document.getElementById('homeUsdtContainer').innerHTML = '<div style="color:#10b981; font-weight:bold;">Transazione USDT Registrata!</div><p style="color:#cbd5e1; font-size:0.84rem; margin-top:4px;">Verificheremo i blocchi. Il tuo posto è riservato!</p>';
    } else {
      alert(d.error || 'Errore invio transazione.');
    }
  } catch (e) {
    alert('Errore di connessione.');
  }
}
</script>
