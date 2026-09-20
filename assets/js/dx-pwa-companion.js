/**
 * DEPENDEX.SOCIAL — PWA COMPANION & INSTANT SOS CONTROLLER
 * Gestione registrazione Service Worker, stato offline/online, install prompt discreto
 * e apertura modale SOS 1-tap conforme a MOBILE_FIRST_VIEWPORT_SPEC.md.
 */

(function() {
  'use strict';

  // 1. Registrazione Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('service-worker.js', { scope: './' })
        .then(function(reg) {
          // Registrazione attiva
        })
        .catch(function(err) {
          console.warn('[PWA] Service worker non registrato:', err);
        });
    });
  }

  // 2. Banner discreto di connettività offline / online
  function createConnectivityToast() {
    let toast = document.getElementById('dxConnectivityToast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'dxConnectivityToast';
      toast.style.cssText = `
        position: fixed;
        top: calc(env(safe-area-inset-top, 0px) + 12px);
        left: 50%;
        transform: translateX(-50%) translateY(-100px);
        background: rgba(18, 24, 38, 0.95);
        color: #ffffff;
        border: 1px solid rgba(0, 240, 255, 0.4);
        border-radius: 999px;
        padding: 8px 18px;
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 99999;
        box-shadow: 0 10px 30px rgba(0,0,0,0.8), 0 0 20px rgba(0,240,255,0.25);
        backdrop-filter: blur(12px);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        pointer-events: none;
      `;
      document.body.appendChild(toast);
    }
    return toast;
  }

  function showConnectivityStatus(isOnline) {
    const toast = createConnectivityToast();
    if (!isOnline) {
      toast.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;"></span> Modalità Offline · Mappa e SOS Disponibili';
      toast.style.borderColor = 'rgba(245, 158, 11, 0.5)';
      toast.style.transform = 'translateX(-50%) translateY(0)';
    } else {
      toast.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span> Connessione Ristabilita';
      toast.style.borderColor = 'rgba(16, 185, 129, 0.5)';
      toast.style.transform = 'translateX(-50%) translateY(0)';
      setTimeout(function() {
        toast.style.transform = 'translateX(-50%) translateY(-100px)';
      }, 3500);
    }
  }

  window.addEventListener('offline', function() { showConnectivityStatus(false); });
  window.addEventListener('online', function() { showConnectivityStatus(true); });

  // 3. Modale SOS 1-Tap (armonizzata con dxToggleSosModal)
  window.openSosModal = function() {
    if (typeof window.dxToggleSosModal === 'function') {
      window.dxToggleSosModal(true);
      return;
    }
    let modal = document.getElementById('dxSosModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'dxSosModal';
      modal.style.cssText = `
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(4, 7, 14, 0.88);
        backdrop-filter: blur(16px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100000;
        padding: 16px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.25s ease;
      `;

      modal.innerHTML = `
        <div style="background: radial-gradient(circle at 50% 0%, rgba(224, 169, 109, 0.12) 0%, rgba(14, 18, 30, 0.98) 100%); border: 1.5px solid rgba(212, 175, 55, 0.4); border-radius: 24px; padding: 24px; max-width: 480px; width: 100%; box-shadow: 0 25px 50px rgba(0,0,0,0.85); position: relative; text-align: center;">
          <button type="button" onclick="closeSosModal()" style="position: absolute; top: 14px; right: 14px; background: transparent; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; padding: 4px 8px; line-height: 1;">&times;</button>
          
          <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(212, 175, 55, 0.15); color: #ffd700; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 1.4rem;">
            &#x1F6E1;
          </div>
          <h3 style="color: #ffffff; font-size: 1.35rem; font-weight: 800; margin: 0 0 6px;">Ascolto & Emergenza Immediata</h3>
          <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.5; margin: 0 0 20px;">
            Non sei solo. In qualsiasi momento puoi parlare gratuitamente e in totale riservatezza con chi ti comprende.
          </p>

          <div style="display: flex; flex-direction: column; gap: 10px; text-align: left; margin-bottom: 20px;">
            <a href="tel:800632000" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; background: rgba(0, 240, 255, 0.1); border: 1px solid rgba(0, 240, 255, 0.35); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 0.95rem;">
              <span>&#128222; Telefono Verde Alcol (ISS)</span>
              <span style="color: #00f0ff; font-weight: 800;">800 632 000</span>
            </a>
            <a href="tel:0223272327" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; background: rgba(255, 215, 0, 0.1); border: 1px solid rgba(255, 215, 0, 0.35); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 0.95rem;">
              <span>&#128172; Telefono Amico Italia</span>
              <span style="color: #ffd700; font-weight: 800;">02 2327 2327</span>
            </a>
            <a href="tel:112" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 0.95rem;">
              <span>&#128657; Numero Unico Emergenze</span>
              <span style="color: #f87171; font-weight: 800;">112</span>
            </a>
          </div>

          <div style="display: flex; gap: 8px;">
            <a href="mappa-club.php" style="flex: 1; padding: 12px; border-radius: 12px; background: linear-gradient(135deg, #00f0ff, #0077ff); color: #070a12; font-weight: 800; text-decoration: none; font-size: 0.9rem; display: inline-block;">
              Trova Club (GPS)
            </a>
            <button type="button" onclick="closeSosModal()" style="padding: 12px 20px; border-radius: 12px; background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #ffffff; font-weight: 600; font-size: 0.9rem; cursor: pointer;">
              Chiudi
            </button>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
    }
    modal.style.opacity = '1';
    modal.style.visibility = 'visible';
  };

  window.closeSosModal = function() {
    if (typeof window.dxToggleSosModal === 'function') {
      window.dxToggleSosModal(false);
    }
    const modal = document.getElementById('dxSosModal');
    if (modal) {
      modal.style.opacity = '0';
      modal.style.visibility = 'hidden';
    }
  };

  // 4. Funzione di condivisione maieutica per familiari "Ti accompagno io"
  window.shareClubWithFamily = function(clubName, city, meetingDay, meetingTime, address) {
    const text = `Ciao, ho trovato il cerchio di supporto del Club più vicino a noi: "${clubName}" a ${city}${address ? ' in ' + address : ''}. Si ritrovano il ${meetingDay || 'ogni settimana'}${meetingTime ? ' alle ' + meetingTime : ''}. È aperto a tutta la famiglia, gratuito e senza giudizio. Se vuoi, ti accompagno io: https://dependex.social/mappa-club.php`;
    
    if (navigator.share) {
      navigator.share({
        title: 'Club Multifamiliare Territoriale',
        text: text,
        url: 'https://dependex.social/mappa-club.php'
      }).catch(function() {});
    } else {
      const waUrl = 'https://wa.me/?text=' + encodeURIComponent(text);
      window.open(waUrl, '_blank');
    }
  };

  window.handleShareClubBtn = function(btn) {
    if (!btn || !btn.dataset) return;
    const { club, city, day, time, addr } = btn.dataset;
    window.shareClubWithFamily(club || '', city || '', day || '', time || '', addr || '');
  };

})();
