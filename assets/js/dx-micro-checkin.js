/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * DX Micro-Checkin Maieutico (PWA & Offline Native)
 * 
 * Rispetta rigorosamente Human Welfare OS 4.0 & Omni-Welfare 6.0:
 * - ZERO indici numerici di benessere o percentuali di salute
 * - ZERO diagnosi o giudizi clinici
 * - 100% Locale & Riservato (localStorage cifrato/locale, nessun invio dati)
 * - Approccio maieutico: ascolto dello stato presente e proposta di 1 micro-passo gentile
 */

(function() {
  'use strict';

  const STORAGE_KEY = 'dx_maieutic_checkins_v1';

  const STATES = [
    {
      id: 'overwhelmed',
      label: 'Sopraffatto / Confuso',
      icon: '🌊',
      quote: 'Non puoi fermare le onde, puoi imparare a galleggiare per i prossimi tre minuti.',
      action: 'Fai 3 respiri profondi con il cerchio 4-7-8, oppure bevi lentamente mezzo bicchiere d’acqua fresca.',
      ctaText: 'Apri Cerchio Respiro',
      ctaAction: 'BREATH'
    },
    {
      id: 'tired',
      label: 'Stanco / Scarico',
      icon: '🌱',
      quote: 'Anche la terra riposa d’inverno. Fermarsi non è perdere tempo, è custodire energie.',
      action: 'Chiudi gli occhi per 60 secondi e rilascia le spalle. Non devi risolvere nulla adesso.',
      ctaText: 'Ascolta una Clip di Calma',
      ctaAction: 'CLIP'
    },
    {
      id: 'lonely',
      label: 'Bisogno di ascolto',
      icon: '🤝',
      quote: 'Nessun problema è un segreto che devi portare da solo.',
      action: 'Nel cerchio del Club ci sono persone che hanno vissuto la tua stessa sensazione e ti ascoltano senza giudizio.',
      ctaText: 'Trova il Cerchio più vicino',
      ctaAction: 'CLUB'
    },
    {
      id: 'restless',
      label: 'Agitazione / Tensione',
      icon: '⚡',
      quote: 'L’energia agitata cerca solo una via di espressione sicura.',
      action: 'Muovi i piedi a terra, senti il pavimento solido sotto di te. Sei qui, sei al sicuro adesso.',
      ctaText: 'Parla con Noi (Anonimo)',
      ctaAction: 'TALK'
    },
    {
      id: 'serene',
      label: 'Sereno / In movimento',
      icon: '☀️',
      quote: 'La serenità di oggi è un seme per te e per chi ti sta accanto.',
      action: 'Custodisci questo momento: scrivi una parola di gratitudine o fai un gesto gentile verso qualcuno oggi.',
      ctaText: 'Esplora Life Playground',
      ctaAction: 'PLAY'
    }
  ];

  window.DxMicroCheckin = {
    getStates: function() {
      return STATES;
    },

    saveCheckin: function(stateId, note) {
      const item = {
        id: 'chk_' + Date.now(),
        timestamp: new Date().toISOString(),
        stateId: stateId,
        note: (note || '').trim().substring(0, 140)
      };
      try {
        const history = this.getHistory();
        history.unshift(item);
        // Tieni al massimo gli ultimi 30 giorni
        if (history.length > 30) history.pop();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(history));
        if (window.DxTelemetry) {
          // Registra solo l'avvenuto checkin anonimo, mai il testo privato
          window.DxTelemetry.logAction('MICRO_CHECKIN_DONE', { stateId: stateId });
        }
      } catch (e) {
        console.warn('Errore salvataggio checkin locale:', e);
      }
      return item;
    },

    getHistory: function() {
      try {
        const data = localStorage.getItem(STORAGE_KEY);
        return data ? JSON.parse(data) : [];
      } catch (e) {
        return [];
      }
    },

    getLastCheckin: function() {
      const hist = this.getHistory();
      return hist.length > 0 ? hist[0] : null;
    },

    renderWidget: function(containerId) {
      const container = document.getElementById(containerId);
      if (!container) return;

      const last = this.getLastCheckin();
      let lastHtml = '';
      if (last) {
        const matchingState = STATES.find(s => s.id === last.stateId);
        const dateStr = new Date(last.timestamp).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        lastHtml = `
          <div style="font-size: 0.76rem; color: #94a3b8; margin-top: 12px; text-align: center;">
            Ultimo momento custodito: <b style="color: #67e8f9;">${matchingState ? matchingState.label : ''}</b> (${dateStr})
          </div>
        `;
      }

      container.innerHTML = `
        <div class="dx-checkin-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 16px; padding: 20px; backdrop-filter: blur(10px); max-width: 600px; margin: 0 auto; box-shadow: 0 8px 30px rgba(0,0,0,0.4);">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <span style="font-size: 0.75rem; font-weight: 850; text-transform: uppercase; color: #00f0ff; letter-spacing: 0.08em;">
              Spazio Personale Riservato (100% on-device)
            </span>
            <span style="font-size: 0.72rem; color: #64748b;">Nessun dato inviato online</span>
          </div>
          
          <h4 style="font-size: 1.15rem; color: #ffffff; margin: 0 0 8px; font-weight: 800;">
            Come ti senti in questo momento?
          </h4>
          <p style="font-size: 0.85rem; color: #cbd5e1; margin: 0 0 16px; line-height: 1.45;">
            Non ci sono risposte giuste o sbagliate. Accogli semplicemente ciò che c'è.
          </p>

          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px;" id="dxCheckinBtnGrid">
            ${STATES.map(s => `
              <button type="button" onclick="window.DxMicroCheckin.handleSelect('${s.id}')" 
                      style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px 8px; color: #ffffff; font-size: 0.82rem; font-weight: 700; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: all 0.2s ease;">
                <span style="font-size: 1.3rem;">${s.icon}</span>
                <span style="text-align: center; line-height: 1.2;">${s.label}</span>
              </button>
            `).join('')}
          </div>

          <div id="dxCheckinResult" style="display: none; margin-top: 16px; background: rgba(0, 240, 255, 0.06); border: 1px solid rgba(0, 240, 255, 0.3); border-radius: 12px; padding: 14px;">
            <div id="dxResultQuote" style="font-style: italic; color: #e2e8f0; font-size: 0.88rem; margin-bottom: 8px;"></div>
            <div id="dxResultAction" style="color: #a7f3d0; font-size: 0.85rem; line-height: 1.45; margin-bottom: 12px;"></div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
              <button type="button" id="dxResultCta" style="background: linear-gradient(135deg, #00f0ff, #0077ff); color: #070a12; border: none; border-radius: 8px; padding: 8px 14px; font-weight: 800; font-size: 0.82rem; cursor: pointer;"></button>
              <button type="button" onclick="window.DxMicroCheckin.renderWidget('${containerId}')" style="background: none; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8; border-radius: 8px; padding: 8px 12px; font-size: 0.8rem; cursor: pointer;">Chiudi</button>
            </div>
          </div>

          ${lastHtml}
        </div>
      `;
    },

    handleSelect: function(stateId) {
      const s = STATES.find(x => x.id === stateId);
      if (!s) return;

      this.saveCheckin(stateId);

      const resBox = document.getElementById('dxCheckinResult');
      const quoteBox = document.getElementById('dxResultQuote');
      const actionBox = document.getElementById('dxResultAction');
      const ctaBtn = document.getElementById('dxResultCta');

      if (resBox && quoteBox && actionBox && ctaBtn) {
        quoteBox.textContent = `«${s.quote}»`;
        actionBox.innerHTML = `<strong>Un piccolo passo per te:</strong> ${s.action}`;
        ctaBtn.textContent = s.ctaText;
        ctaBtn.onclick = function() {
          if (s.ctaAction === 'BREATH') {
            if (window.dxToggleSosModal) window.dxToggleSosModal(true);
          } else if (s.ctaAction === 'CLIP') {
            window.location.href = 'clips.php';
          } else if (s.ctaAction === 'CLUB') {
            window.location.href = 'world-club-explorer.php';
          } else if (s.ctaAction === 'TALK') {
            window.location.href = 'parla-con-noi.php';
          } else if (s.ctaAction === 'PLAY') {
            window.location.href = 'playground.php?v=2';
          }
        };
        resBox.style.display = 'block';
      }
    }
  };

  // Se è presente il container dedicato nel DOM al caricamento, inizializza automaticamente
  document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('dx-micro-checkin-container');
    if (el) {
      window.DxMicroCheckin.renderWidget('dx-micro-checkin-container');
    }
  });
})();
