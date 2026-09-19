/**
 * ═════════════════════════════════════════════════════════════════════════
 * UNIVERSAL CHAT AI & EMPATHIC LISTENER — CLIENT COMPONENT
 * ═════════════════════════════════════════════════════════════════════════
 * Punto di Ascolto & Orientamento Solidale per i Club Territoriali.
 * - Connessione diretta con api-cortex.php
 * - Fallback empatico integrato (0 downtime)
 * - Nessuna profilazione invasiva, 100% rispetto della privacy (GDPR)
 * - Risposte immediate su Metodo Hudolin, ricerca Club e supporto familiari
 */
(function() {
  if (window.__UNIVERSAL_CHAT_AI_LOADED__) return;
  window.__UNIVERSAL_CHAT_AI_LOADED__ = true;

  const scriptTag = document.currentScript || document.querySelector('script[src*="universal-chat-ai"]');
  const customBrand = scriptTag ? scriptTag.getAttribute('data-brand') : 'DEPENDEX';
  const customDomain = scriptTag ? scriptTag.getAttribute('data-domain') : window.location.hostname.replace('www.', '');

  const apiEndpoint = '/api-cortex.php';

  let tenantConfig = {
    brand_name: customBrand || 'DEPENDEX',
    brand_domain: customDomain || 'dependex.social',
    primary_color: '#00f0ff',
    secondary_color: '#ffd700',
    support_email: 'info@dependex.support',
    toll_free_number: '800 974250',
    role_title: 'Punto di Ascolto & Orientamento'
  };

  // Carica foglio di stile se non già presente
  const cssId = 'universal-chat-ai-css';
  if (!document.getElementById(cssId)) {
    const link = document.createElement('link');
    link.id = cssId;
    link.rel = 'stylesheet';
    link.href = 'assets/css/universal-chat-ai.css';
    document.head.appendChild(link);
  }

  // Costruzione DOM Widget
  const container = document.createElement('div');
  container.className = 'u-chat-container';
  container.innerHTML = `
    <button type="button" class="u-chat-trigger" id="uChatTrigger" aria-label="Apri punto di ascolto e orientamento">
      <svg class="u-chat-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
      <span class="u-chat-badge-live"></span>
    </button>

    <div class="u-chat-window" id="uChatWindow" style="display:none;" role="dialog" aria-modal="true" aria-label="Sportello di Ascolto Online">
      <div class="u-chat-header">
        <div class="u-chat-header-info">
          <div class="u-chat-avatar">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z"/><path d="M12 7v5l3 3"/></svg>
          </div>
          <div>
            <strong id="uChatTitle">${escapeHtml(tenantConfig.brand_name)}</strong>
            <small id="uChatSubtitle">${escapeHtml(tenantConfig.role_title)}</small>
          </div>
        </div>
        <button type="button" class="u-chat-close" id="uChatClose" aria-label="Chiudi finestra">&times;</button>
      </div>

      <div class="u-chat-messages" id="uChatMessages">
        <div class="u-chat-msg u-chat-bot">
          <div class="u-chat-bubble">
            Ciao. Questo è uno spazio protetto e riservato.<br><br>
            Puoi chiedermi come funzionano i <strong>Club Alcologici Territoriali</strong>, come trovare un gruppo vicino a te o come affrontare una situazione difficile in famiglia. Nessun giudizio, solo accoglienza.
          </div>
        </div>
      </div>

      <form class="u-chat-input-row" id="uChatForm">
        <input type="text" id="uChatInput" placeholder="Scrivi una domanda o una richiesta..." autocomplete="off" required aria-label="Messaggio di richiesta">
        <button type="submit" id="uChatSend" aria-label="Invia messaggio">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
      </form>
    </div>
  `;

  document.body.appendChild(container);

  const trigger = document.getElementById('uChatTrigger');
  const windowEl = document.getElementById('uChatWindow');
  const closeBtn = document.getElementById('uChatClose');
  const form = document.getElementById('uChatForm');
  const input = document.getElementById('uChatInput');
  const history = document.getElementById('uChatMessages');
  const agentBadge = document.getElementById('uChatSubtitle');

  trigger.addEventListener('click', () => {
    const isHidden = windowEl.style.display === 'none';
    windowEl.style.display = isHidden ? 'flex' : 'none';
    if (isHidden) {
      setTimeout(() => input.focus(), 150);
    }
  });

  closeBtn.addEventListener('click', () => {
    windowEl.style.display = 'none';
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const userMsg = input.value.trim();
    if (!userMsg) return;

    appendMsg(userMsg, 'user');
    input.value = '';
    const typingId = showTyping();

    try {
      const res = await fetch(apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: userMsg,
          q: userMsg,
          domain: tenantConfig.brand_domain
        })
      });

      removeTyping(typingId);

      if (res.ok) {
        const data = await res.json();
        if (data.role) agentBadge.textContent = data.role;
        appendBotResponse(data.text || data.answer || 'Grazie per il tuo messaggio. Un facilitatore della rete ti risponderà al più presto.', data.actions);
      } else {
        throw new Error('API status not ok');
      }

    } catch (err) {
      removeTyping(typingId);
      handleEdgeFallback(userMsg);
    }
  });

  function handleEdgeFallback(userMsg) {
    const q = userMsg.toLowerCase();
    let reply = '';
    let actions = [];

    if (q.includes('club') || q.includes('dove') || q.includes('trova') || q.includes('vicino') || q.includes('mappa') || q.includes('città')) {
      reply = `In tutta Italia sono attivi centinaia di **Club Alcologici Territoriali** (Metodo Hudolin). Puoi localizzare subito il gruppo più vicino a te tramite la nostra mappa georeferenziata o la directory completa.`;
      actions.push({ label: 'Apri la Mappa 2D dei Club', url: 'mappa-club.php', type: 'link' });
      actions.push({ label: 'Cerca per Regione e Comune', url: 'world-club-explorer.php', type: 'link' });
    }
    else if (q.includes('costo') || q.includes('costa') || q.includes('pagare') || q.includes('gratis') || q.includes('prezzo')) {
      reply = `La partecipazione ai Club è **completamente gratuita**. I Club operano sul principio della solidarietà, della reciprocità e dell'approccio ecologico-sociale, senza rette né barriere economiche.`;
      actions.push({ label: 'Scopri come Funziona il Club', url: 'metodo.php', type: 'link' });
      actions.push({ label: 'Parla con Noi', url: 'parla-con-noi.php', type: 'link' });
    }
    else if (q.includes('famiglia') || q.includes('moglie') || q.includes('marito') || q.includes('figlio') || q.includes('genitore')) {
      reply = `Nel Metodo Hudolin la famiglia è al centro del percorso: i problemi alcolcorrelati non sono mai una colpa individuale ma una dinamica dell'intero sistema relazionale. Puoi iniziare scaricando la nostra guida riservata per i familiari.`;
      actions.push({ label: 'Scarica Guida Famiglia (PDF)', url: 'guida-gratuita.php', type: 'link' });
      actions.push({ label: 'Sportello di Ascolto', url: 'parla-con-noi.php', type: 'link' });
    }
    else if (q.includes('urgente') || q.includes('emergenza') || q.includes('ospedale') || q.includes('male') || q.includes('112')) {
      reply = `Se c'è un rischio clinico immediato o un malessere acuto, chiama senza esitazione il **112 (Numero Unico Emergenze)**. Per un supporto telefonico nazionale puoi contattare il **Telefono Verde Alcol ISS (800 632 000)** o il Numero Verde AICAT (800 974250).`;
      actions.push({ label: 'Numeri di Emergenza & Aiuto', url: 'help.php', type: 'link' });
    }
    else if (q.includes('contatt') || q.includes('parla') || q.includes('scrivi') || q.includes('segreteria')) {
      reply = `Puoi dialogare con la Segreteria di Accoglienza tramite il modulo riservato o inviando un'email a **info@dependex.support**. Rispondiamo sempre con il massimo rispetto e totale riservatezza.`;
      actions.push({ label: 'Compila Modulo di Ascolto', url: 'parla-con-noi.php', type: 'link' });
    }
    else {
      reply = `Grazie per aver condiviso questo pensiero. Nei Club nessuno è solo e non c'è situazione che non possa essere affrontata insieme. Vuoi cercare un Club sul tuo territorio o preferisci consultare la guida gratuita?`;
      actions.push({ label: 'Trova il Club più Vicino', url: 'mappa-club.php', type: 'link' });
      actions.push({ label: 'Parla con la Segreteria', url: 'parla-con-noi.php', type: 'link' });
    }

    appendBotResponse(reply, actions);
  }

  function appendMsg(text, type) {
    const div = document.createElement('div');
    div.className = `u-chat-msg u-chat-${type}`;
    div.innerHTML = `<div class="u-chat-bubble">${escapeHtml(text)}</div>`;
    history.appendChild(div);
    history.scrollTop = history.scrollHeight;
  }

  function appendBotResponse(text, actions) {
    const div = document.createElement('div');
    div.className = 'u-chat-msg u-chat-bot';

    let actHtml = '';
    if (actions && actions.length > 0) {
      actHtml = '<div class="u-chat-actions">' + actions.map(a => 
        `<a href="${a.url}" class="u-chat-action-btn u-chat-btn-${a.type || 'link'}">${escapeHtml(a.label)}</a>`
      ).join('') + '</div>';
    }

    div.innerHTML = `
      <div class="u-chat-bubble">
        ${formatMarkdown(text)}
        ${actHtml}
      </div>
    `;
    history.appendChild(div);
    history.scrollTop = history.scrollHeight;
  }

  function showTyping() {
    const id = 'typing_' + Date.now();
    const div = document.createElement('div');
    div.id = id;
    div.className = 'u-chat-msg u-chat-bot';
    div.innerHTML = `<div class="u-chat-bubble u-chat-typing"><span></span><span></span><span></span></div>`;
    history.appendChild(div);
    history.scrollTop = history.scrollHeight;
    return id;
  }

  function removeTyping(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
  }

  function escapeHtml(str) {
    return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function formatMarkdown(str) {
    return (str || '')
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/\n/g, '<br/>');
  }
})();
