/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * DX Local Reminders Engine (PWA & Offline Native)
 * 
 * Rispetta rigorosamente Human Welfare OS 4.0:
 * - 100% On-Device: nessun token cloud, nessun dato personale inviato online
 * - Attivazione esclusivamente su consenso informato della persona
 * - Notifiche gentili orientate alla de-escalation e all'incontro reale
 */

(function() {
  'use strict';

  const REMINDERS_KEY = 'dx_local_reminders_settings_v1';

  const DEFAULT_SETTINGS = {
    breathEnabled: false,
    breathTime: '20:30',
    clubEnabled: false,
    clubDay: 'Mercoledì',
    clubTime: '20:00',
    clubName: 'Club CAT Territoriale'
  };

  window.DxLocalReminders = {
    getSettings: function() {
      try {
        const data = localStorage.getItem(REMINDERS_KEY);
        return data ? Object.assign({}, DEFAULT_SETTINGS, JSON.parse(data)) : DEFAULT_SETTINGS;
      } catch (e) {
        return DEFAULT_SETTINGS;
      }
    },

    saveSettings: function(settings) {
      try {
        const merged = Object.assign({}, this.getSettings(), settings);
        localStorage.setItem(REMINDERS_KEY, JSON.stringify(merged));
        return merged;
      } catch (e) {
        console.warn('Errore salvataggio impostazioni promemoria:', e);
        return DEFAULT_SETTINGS;
      }
    },

    requestPermission: async function() {
      if (!('Notification' in window)) {
        alert('Il tuo browser o dispositivo non supporta le notifiche native.');
        return false;
      }
      if (Notification.permission === 'granted') {
        return true;
      }
      if (Notification.permission !== 'denied') {
        const perm = await Notification.requestPermission();
        return perm === 'granted';
      }
      return false;
    },

    sendNotification: function(title, body, actionUrl) {
      if (!('Notification' in window) || Notification.permission !== 'granted') return;
      try {
        const notif = new Notification(title, {
          body: body,
          icon: '/assets/logo.png',
          badge: '/assets/logo.png',
          tag: 'dx-gentle-reminder',
          renotify: true
        });
        notif.onclick = function() {
          window.focus();
          if (actionUrl) window.location.href = actionUrl;
          notif.close();
        };
      } catch (e) {
        console.warn('Errore invio notifica locale:', e);
      }
    },

    checkAndTrigger: function() {
      const settings = this.getSettings();
      const now = new Date();
      const currentHours = String(now.getHours()).padStart(2, '0');
      const currentMins = String(now.getMinutes()).padStart(2, '0');
      const currentTimeStr = `${currentHours}:${currentMins}`;

      // Giorni della settimana in italiano
      const daysIt = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
      const currentDayStr = daysIt[now.getDay()];

      const todayDateStr = now.toISOString().split('T')[0];
      const lastTriggerKey = 'dx_last_reminder_triggered_' + todayDateStr;
      const alreadyTriggered = localStorage.getItem(lastTriggerKey);

      // Promemoria Respiro Serale (4-7-8)
      if (settings.breathEnabled && currentTimeStr === settings.breathTime && alreadyTriggered !== 'breath') {
        this.sendNotification(
          'Un momento di calma per te 🌱',
          'Fermati solo per 3 minuti. Segui il cerchio di respirazione 4-7-8.',
          '/index.php?action=sos'
        );
        localStorage.setItem(lastTriggerKey, 'breath');
      }

      // Promemoria Riunione Settimanale del Club
      if (settings.clubEnabled && currentDayStr === settings.clubDay && currentTimeStr === settings.clubTime && alreadyTriggered !== 'club') {
        this.sendNotification(
          `Oggi c’è il tuo Club (${settings.clubName}) 🤝`,
          'Il cerchio multifamiliare si riunisce stasera. Una sedia aperta ti aspetta.',
          '/mappa-club.php'
        );
        localStorage.setItem(lastTriggerKey, 'club');
      }
    },

    renderModal: function() {
      let modal = document.getElementById('dx-reminders-modal');
      if (modal) {
        modal.remove();
      }

      const settings = this.getSettings();
      const permGranted = ('Notification' in window) && Notification.permission === 'granted';

      const html = `
        <div id="dx-reminders-modal" style="position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(8px); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 16px;">
          <div style="background: #0f172a; border: 1px solid rgba(0, 240, 255, 0.3); border-radius: 18px; padding: 22px; max-width: 480px; width: 100%; color: #ffffff; box-shadow: 0 10px 40px rgba(0,0,0,0.6);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <span style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: #00f0ff; letter-spacing: 0.08em;">
                Promemoria Gentili 100% Locali
              </span>
              <button type="button" onclick="document.getElementById('dx-reminders-modal').remove()" style="background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <h3 style="font-size: 1.25rem; font-weight: 850; margin-bottom: 8px;">
              Promemoria di Calma &amp; Comunità
            </h3>
            <p style="font-size: 0.84rem; color: #94a3b8; line-height: 1.45; margin-bottom: 16px;">
              Funzionano solo sul tuo dispositivo. Nessuna registrazione, nessun server cloud, privacy totale.
            </p>

            ${!permGranted ? `
              <div style="background: rgba(0, 240, 255, 0.08); border: 1px dashed #00f0ff; border-radius: 10px; padding: 12px; margin-bottom: 16px; text-align: center;">
                <div style="font-size: 0.82rem; color: #67e8f9; margin-bottom: 8px;">
                  Per ricevere i promemoria, abilita le notifiche su questo dispositivo:
                </div>
                <button type="button" id="dxReqPermBtn" style="background: linear-gradient(135deg, #00f0ff, #0077ff); color: #070a12; border: none; border-radius: 8px; padding: 8px 16px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                  Abilita Notifiche Private
                </button>
              </div>
            ` : ''}

            <!-- TOGGLE RESPIRO SERALE -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 14px; margin-bottom: 12px;">
              <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                <div>
                  <div style="font-weight: 750; font-size: 0.9rem; color: #ffffff;">Pausa Respiro Serale (4-7-8)</div>
                  <div style="font-size: 0.78rem; color: #94a3b8;">Un invito silenzioso a rilasciare le tensioni a fine giornata</div>
                </div>
                <input type="checkbox" id="dxBreathToggle" ${settings.breathEnabled ? 'checked' : ''} style="width: 20px; height: 20px; accent-color: #00f0ff;">
              </label>
              <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: #cbd5e1;">
                <span>Orario:</span>
                <input type="time" id="dxBreathTime" value="${settings.breathTime}" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15); color: #00f0ff; border-radius: 6px; padding: 4px 8px;">
              </div>
            </div>

            <!-- TOGGLE CLUB SETTIMANALE -->
            <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 14px; margin-bottom: 18px;">
              <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                <div>
                  <div style="font-weight: 750; font-size: 0.9rem; color: #ffffff;">Promemoria Incontro del Club</div>
                  <div style="font-size: 0.78rem; color: #94a3b8;">Ti ricorda il giorno della riunione del tuo cerchio</div>
                </div>
                <input type="checkbox" id="dxClubToggle" ${settings.clubEnabled ? 'checked' : ''} style="width: 20px; height: 20px; accent-color: #00ff88;">
              </label>
              <div style="margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap; font-size: 0.82rem; color: #cbd5e1;">
                <select id="dxClubDay" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15); color: #ffffff; border-radius: 6px; padding: 4px 8px;">
                  ${['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'].map(d => `<option value="${d}" ${settings.clubDay === d ? 'selected' : ''}>${d}</option>`).join('')}
                </select>
                <input type="time" id="dxClubTime" value="${settings.clubTime}" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.15); color: #00ff88; border-radius: 6px; padding: 4px 8px;">
              </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
              <button type="button" onclick="document.getElementById('dx-reminders-modal').remove()" style="background: none; border: 1px solid rgba(255,255,255,0.2); color: #94a3b8; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; cursor: pointer;">
                Chiudi
              </button>
              <button type="button" id="dxSaveSettingsBtn" style="background: linear-gradient(135deg, #00f0ff, #0077ff); color: #070a12; border: none; border-radius: 8px; padding: 8px 18px; font-weight: 800; font-size: 0.85rem; cursor: pointer;">
                Salva Impostazioni
              </button>
            </div>

          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', html);

      const reqBtn = document.getElementById('dxReqPermBtn');
      if (reqBtn) {
        reqBtn.onclick = async () => {
          const ok = await window.DxLocalReminders.requestPermission();
          if (ok) window.DxLocalReminders.renderModal();
        };
      }

      document.getElementById('dxSaveSettingsBtn').onclick = () => {
        const newSettings = {
          breathEnabled: document.getElementById('dxBreathToggle').checked,
          breathTime: document.getElementById('dxBreathTime').value,
          clubEnabled: document.getElementById('dxClubToggle').checked,
          clubDay: document.getElementById('dxClubDay').value,
          clubTime: document.getElementById('dxClubTime').value
        };
        window.DxLocalReminders.saveSettings(newSettings);
        alert('Impostazioni promemoria salvate con successo sul tuo dispositivo!');
        document.getElementById('dx-reminders-modal').remove();
      };
    }
  };

  // Controlla ogni minuto se c'è un promemoria da attivare
  setInterval(() => {
    window.DxLocalReminders.checkAndTrigger();
  }, 60000);

  // Controllo immediato all'avvio
  setTimeout(() => {
    window.DxLocalReminders.checkAndTrigger();
  }, 3000);
})();
