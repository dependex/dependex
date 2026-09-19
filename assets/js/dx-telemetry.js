/**
 * DEPENDEX · TELEMETRIA & FUNNEL PSICOLOGICO (GDPR-COMPLIANT CLIENT ENGINE)
 * Monitoraggio etico: Dwell Time attivo, Scroll Depth, Scelta Porte d'Ingresso,
 * Consultazione FAQ intime ed interazione con Comunità e Club.
 */
(function() {
  'use strict';

  // Configurazione
  var ENDPOINT = 'api.php';
  var startTime = Date.now();
  var activeDwellSeconds = 0;
  var isVisible = !document.hidden;
  var maxScrollPercent = 0;
  var scrollMilestones = { 25: false, 50: false, 75: false, 90: false };
  var activePorta = null;
  var openedFaqCount = 0;

  // 1. Estrazione porta da URL se presente
  try {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('porta')) {
      activePorta = urlParams.get('porta');
    }
  } catch(e) {}

  // 2. Misurazione del tempo attivo (Dwell Time)
  document.addEventListener('visibilitychange', function() {
    isVisible = !document.hidden;
  });

  setInterval(function() {
    if (isVisible) {
      activeDwellSeconds += 1;
    }
  }, 1000);

  // 3. Invio beacon / ping periodico (ogni 30s) e su unload
  function sendPing() {
    var payload = {
      dwell: activeDwellSeconds,
      scroll: maxScrollPercent,
      page: window.location.pathname + window.location.search,
      porta: activePorta
    };

    var body = JSON.stringify(payload);
    var url = ENDPOINT + '?action=ping';

    if (navigator.sendBeacon) {
      var blob = new Blob([body], { type: 'application/json' });
      navigator.sendBeacon(url, blob);
    } else {
      try {
        fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: body,
          keepalive: true
        });
      } catch (err) {}
    }
  }

  // Ping ogni 30 secondi
  setInterval(sendPing, 30000);

  // Ping finale quando l'utente lascia la pagina
  window.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
      sendPing();
    }
  });
  window.addEventListener('pagehide', sendPing);

  // 4. Tracciamento Scroll Depth
  function checkScrollDepth() {
    var docHeight = Math.max(
      document.body.scrollHeight, document.documentElement.scrollHeight,
      document.body.offsetHeight, document.documentElement.offsetHeight,
      document.body.clientHeight, document.documentElement.clientHeight
    );
    var winHeight = window.innerHeight || document.documentElement.clientHeight;
    var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (docHeight <= winHeight) return;

    var currentPercent = Math.round(((scrollTop + winHeight) / docHeight) * 100);
    if (currentPercent > maxScrollPercent) {
      maxScrollPercent = Math.min(100, currentPercent);
    }

    [25, 50, 75, 90].forEach(function(m) {
      if (maxScrollPercent >= m && !scrollMilestones[m]) {
        scrollMilestones[m] = true;
        sendFunnelEvent('SCROLL_DEPTH_' + m, 'ENGAGEMENT', { depth: m, page: window.location.pathname });
      }
    });
  }

  window.addEventListener('scroll', checkScrollDepth, { passive: true });

  // 5. Funzione di invio evento funnel verso api.php
  function sendFunnelEvent(actionName, stage, meta) {
    stage = stage || 'ORIENTATION';
    meta = meta || {};
    meta.porta = meta.porta || activePorta;
    meta.page = window.location.pathname;

    var payload = {
      action_name: actionName,
      stage: stage,
      meta: meta
    };

    try {
      fetch(ENDPOINT + '?action=funnel_event', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).catch(function() {});
    } catch(e) {}
  }

  // Esponi helper su window.DependexTelemetry
  window.DependexTelemetry = {
    track: sendFunnelEvent,
    setPorta: function(p) { activePorta = p; }
  };

  // 6. Listener delegato per elementi con data-funnel-action
  document.addEventListener('click', function(e) {
    var target = e.target.closest('[data-funnel-action]');
    if (!target) return;

    var action = target.getAttribute('data-funnel-action');
    var stage = target.getAttribute('data-funnel-stage') || 'ORIENTATION';
    var porta = target.getAttribute('data-funnel-porta');

    if (porta) {
      activePorta = porta;
    }

    var meta = {};
    if (porta) meta.porta = porta;
    if (target.id) meta.element_id = target.id;
    if (target.textContent) meta.label = target.textContent.trim().substring(0, 60);

    // Se è un'apertura di FAQ intima
    if (action === 'FAQ_EXPAND' || target.classList.contains('accordion-toggle')) {
      openedFaqCount++;
      meta.faq_number = openedFaqCount;
      stage = 'REASSURANCE';
    }

    sendFunnelEvent(action, stage, meta);
  });

  // 7. Auto-tracking invio form di contatto / orientamento
  document.addEventListener('submit', function(e) {
    var form = e.target;
    if (form.matches('#modulo-orientamento') || form.querySelector('[name="action"][value="orientamento"]')) {
      sendFunnelEvent('FORM_SUBMIT_CONFIRMED', 'ACTION', {
        porta: activePorta,
        page: window.location.pathname
      });
    }
  });

})();
