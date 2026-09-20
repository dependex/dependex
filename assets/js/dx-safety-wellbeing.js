/**
 * dx-safety-wellbeing.js — Sicurezza Psicologica, Condivisione & Benessere Digitale Sovrano
 * 1. Panic Exit (Uscita Rapida con 1-Click o tasto ESC): reindirizza istantaneamente a meteo.it e cancella la sessione.
 * 2. Gentle Digital Wellbeing Alert ("Screen Off -> Life On", Art. 12 Gamification 6.0):
 *    avvisa con gentilezza dopo 20 minuti di sessione continuativa, ricordando che la vera vita accade fuori dallo schermo.
 * 3. Condivisione Rapida (Web Share API con fallback clipboard): permette alle famiglie di condividere la scheda di un Club.
 */

(function() {
  'use strict';

  // 1. PANIC EXIT / USCITA RAPIDA
  window.dxPanicExit = function(e) {
    if (e && typeof e.preventDefault === 'function') {
      e.preventDefault();
    }
    try {
      if (window.sessionStorage) {
        window.sessionStorage.clear();
      }
      if (window.localStorage) {
        window.localStorage.removeItem('dx_active_session');
      }
    } catch(err) {}

    // Rimpiazza la cronologia per impedire il tasto Indietro
    window.location.replace('https://www.meteo.it');
  };

  // Scorciatoia da tastiera globale: Tasto ESC
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      // Se non ci sono modali aperte nel DOM da chiudere prima
      var openModal = document.querySelector('.modal.active, .drawer.active, [aria-modal="true"][aria-hidden="false"]');
      if (!openModal) {
        window.dxPanicExit();
      }
    }
  });

  // 2. GENTLE DIGITAL WELLBEING REMINDER ("Screen Off -> Life On")
  var SESSION_KEY = 'dx_session_start_ts';
  var ALERT_DISMISSED_KEY = 'dx_wellbeing_dismissed_session';

  function initWellbeingTimer() {
    try {
      if (!window.sessionStorage) return;

      var dismissed = sessionStorage.getItem(ALERT_DISMISSED_KEY);
      if (dismissed === '1') return;

      var startTs = sessionStorage.getItem(SESSION_KEY);
      var now = Date.now();
      if (!startTs) {
        sessionStorage.setItem(SESSION_KEY, now.toString());
        startTs = now;
      } else {
        startTs = parseInt(startTs, 10);
      }

      var elapsedMinutes = (now - startTs) / 60000;
      if (elapsedMinutes >= 20) {
        showWellbeingToast();
      } else {
        var remainingMs = (20 - elapsedMinutes) * 60000;
        setTimeout(showWellbeingToast, Math.max(1000, remainingMs));
      }
    } catch(e) {}
  }

  function showWellbeingToast() {
    try {
      if (sessionStorage.getItem(ALERT_DISMISSED_KEY) === '1') return;
      if (document.getElementById('dx-wellbeing-toast')) return;

      var toast = document.createElement('div');
      toast.id = 'dx-wellbeing-toast';
      toast.setAttribute('role', 'alert');
      toast.setAttribute('aria-live', 'polite');
      toast.innerHTML = 
        '<div style="position:fixed;bottom:24px;right:20px;max-width:360px;background:rgba(22,27,34,0.98);border:1px solid #38ef7d;border-radius:12px;padding:18px 20px;box-shadow:0 12px 32px rgba(0,0,0,0.5);z-index:99998;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;color:#e6edf3;-webkit-font-smoothing:antialiased;backdrop-filter:blur(10px);">' +
          '<div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:10px;">' +
            '<span style="font-size:20px;line-height:1;">🌿</span>' +
            '<div>' +
              '<strong style="font-size:14px;color:#38ef7d;display:block;margin-bottom:4px;">Prenditi Cura del Tuo Tempo</strong>' +
              '<p style="font-size:12px;line-height:1.5;color:#c9d1d9;margin:0;">' +
                'Hai dedicato del tempo prezioso a te stesso oggi. Ricorda che la vera vita accade fuori dallo schermo: se vuoi, prenditi una pausa, respira l\'aria fresca o chiama un amico o familiare del tuo Club.' +
              '</p>' +
            '</div>' +
          '</div>' +
          '<div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">' +
            '<button id="dx-wellbeing-close-btn" style="padding:6px 14px;background:#238636;color:#ffffff;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">' +
              'Ho capito, grazie' +
            '</button>' +
          '</div>' +
        '</div>';

      document.body.appendChild(toast);

      var closeBtn = document.getElementById('dx-wellbeing-close-btn');
      if (closeBtn) {
        closeBtn.addEventListener('click', function() {
          try {
            sessionStorage.setItem(ALERT_DISMISSED_KEY, '1');
          } catch(e) {}
          if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
          }
        });
      }
    } catch(e) {}
  }

  // 3. NATIVE SHARE CON FALLBACK CLIPBOARD
  window.dxShareClub = function(title, text, url) {
    url = url || window.location.href;
    if (navigator.share) {
      navigator.share({
        title: title || document.title,
        text: text || '',
        url: url
      }).catch(function(err) {
        if (err.name !== 'AbortError') {
          copyToClipboardFallback(url);
        }
      });
    } else {
      copyToClipboardFallback(url);
    }
  };

  function copyToClipboardFallback(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function() {
        showShareFeedback('Link copiato negli appunti!');
      }).catch(function() {
        showShareFeedback('Copia link: ' + text);
      });
    } else {
      showShareFeedback('Copia link: ' + text);
    }
  }

  function showShareFeedback(msg) {
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:#1f2937;color:#38ef7d;border:1px solid #38ef7d;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,0.4);';
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(function() {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 2500);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWellbeingTimer);
  } else {
    initWellbeingTimer();
  }

})();
