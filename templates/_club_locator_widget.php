<?php
/**
 * DEPENDEX.SOCIAL — WIDGET 1-TAP CLUB & APCAT LOCATOR
 * Permette all'utente di individuare all'istante il Club Multifamiliare più vicino con 1 tap GPS
 * o inserendo comune/CAP. Connesso ad api-club-locator.php.
 */
?>
<section class="dx-club-locator-widget-section my-5" id="trova-club-subito">
  <div class="locator-widget-card">
    <div class="locator-widget-header">
      <div class="badge-neon-cyan mb-2" style="display:inline-flex; align-items:center; gap:6px; font-size:0.8rem; padding:4px 12px; border-radius:999px; background:rgba(0,240,255,0.12); border:1px solid rgba(0,240,255,0.3); color:#00f0ff;">
        <?=dx_icon('map-pin', 'text-neon-cyan', 14)?>
        <span>1.761 PRESIDI GEOREFERENZIATI IN ITALIA</span>
      </div>
      <h2 style="font-size:clamp(1.5rem, 3vw, 2.2rem); color:#ffffff; font-weight:800; margin:0.4rem 0 0.6rem;">
        Trova il Club o l'APCAT <span style="background:linear-gradient(135deg, #00f0ff, #38bdf8); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">più vicino a te</span>
      </h2>
      <p style="color:#cbd5e1; max-width:640px; margin:0 auto; font-size:0.95rem; line-height:1.6;">
        Nessuna burocrazia né lista d'attesa. Clicca sul pulsante GPS o digita la tua città per vedere subito indirizzo, orari e contatti del cerchio multifamiliare più vicino.
      </p>
    </div>

    <div class="locator-widget-controls">
      <button type="button" id="widgetGpsBtn" class="btn-locator-gps">
        <?=dx_icon('navigation', '', 18)?>
        <span>Trova Vicino a Me (1 Tap GPS)</span>
      </button>

      <div class="locator-divider">oppure</div>

      <div class="locator-input-group">
        <input type="text" id="widgetCityInput" placeholder="Inserisci città o CAP..." autocomplete="off">
        <button type="button" id="widgetSearchBtn" class="btn-locator-search">
          <?=dx_icon('search', '', 16)?> Cerca
        </button>
      </div>
    </div>

    <!-- Container Risultati Dinamici -->
    <div id="locatorWidgetResults" class="locator-results-grid" style="display:none;">
      <!-- Popolato via JS -->
    </div>

    <div class="locator-widget-footer">
      <span style="color:#94a3b8; font-size:0.85rem;">
        La sedia al Club è sempre gratuita e aperta a tutta la famiglia.
      </span>
      <a href="mappa-club.php" class="link-full-map">
        Apri Mappa Interattiva Completa <?=dx_icon('arrow-right', '', 14)?>
      </a>
    </div>
  </div>
</section>

<style>
.dx-club-locator-widget-section {
  max-width: 1140px;
  margin: 3rem auto;
  padding: 0 1rem;
}
.locator-widget-card {
  background: radial-gradient(circle at 50% 0%, rgba(0, 240, 255, 0.08) 0%, rgba(13, 18, 30, 0.95) 100%);
  border: 1px solid rgba(0, 240, 255, 0.25);
  border-radius: 24px;
  padding: 2.5rem 1.75rem;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6), 0 0 30px rgba(0, 240, 255, 0.05);
  text-align: center;
}
.locator-widget-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
  margin: 2rem 0;
}
.btn-locator-gps {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 26px;
  border-radius: 14px;
  background: linear-gradient(135deg, #00f0ff, #0077ff);
  color: #070a12;
  font-weight: 800;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 0 25px rgba(0, 240, 255, 0.45);
  transition: all 0.25s ease;
  min-height: 48px;
}
.btn-locator-gps:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 35px rgba(0, 240, 255, 0.65);
}
.locator-divider {
  color: #64748b;
  font-size: 0.85rem;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.05em;
}
.locator-input-group {
  display: flex;
  align-items: center;
  background: rgba(18, 25, 41, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 14px;
  overflow: hidden;
  min-height: 48px;
}
.locator-input-group input {
  background: transparent;
  border: none;
  padding: 12px 18px;
  color: #ffffff;
  font-size: 0.95rem;
  outline: none;
  width: 220px;
}
.btn-locator-search {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #ffffff;
  padding: 12px 20px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background 0.2s;
  min-height: 48px;
}
.btn-locator-search:hover {
  background: rgba(0, 240, 255, 0.25);
  color: #00f0ff;
}
.locator-results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 16px;
  margin: 2rem 0;
  text-align: left;
}
.locator-result-card {
  background: rgba(18, 25, 41, 0.85);
  border: 1px solid rgba(0, 240, 255, 0.2);
  border-radius: 16px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 12px;
  transition: all 0.2s;
}
.locator-result-card:hover {
  border-color: #00f0ff;
  transform: translateY(-2px);
}
.locator-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}
.badge-dist {
  background: rgba(0, 255, 136, 0.15);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 800;
}
.locator-card-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 6px;
}
.btn-card-action {
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-card-call {
  background: rgba(0, 240, 255, 0.15);
  color: #00f0ff;
  border: 1px solid rgba(0, 240, 255, 0.3);
}
.btn-card-wa {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.3);
}
.btn-card-nav {
  background: rgba(255, 255, 255, 0.08);
  color: #e2e8f0;
  border: 1px solid rgba(255, 255, 255, 0.15);
}
.locator-widget-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  padding-top: 1.2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.link-full-map {
  color: #00f0ff;
  font-weight: 700;
  font-size: 0.92rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.link-full-map:hover {
  text-decoration: underline;
}
</style>

<script>
(function() {
  const gpsBtn = document.getElementById('widgetGpsBtn');
  const searchBtn = document.getElementById('widgetSearchBtn');
  const cityInput = document.getElementById('widgetCityInput');
  const resultsContainer = document.getElementById('locatorWidgetResults');

  function fetchAndRenderClubs(params) {
    gpsBtn.disabled = true;
    gpsBtn.innerHTML = '<span>Ricerca in corso...</span>';

    fetch('api-club-locator.php?' + new URLSearchParams(params).toString())
      .then(res => res.json())
      .then(data => {
        gpsBtn.disabled = false;
        gpsBtn.innerHTML = '<?=dx_icon("navigation", "", 18)?> <span>Trova Vicino a Me (1 Tap GPS)</span>';

        if (!data.ok || !data.items || data.items.length === 0) {
          resultsContainer.style.display = 'block';
          resultsContainer.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 1.5rem;">Nessun club trovato con questi parametri. Prova con una città vicina.</div>';
          return;
        }

        resultsContainer.style.display = 'grid';
        resultsContainer.innerHTML = data.items.map(club => {
          const isApcat = club.is_apcat;
          const tagClass = isApcat ? 'border:#c084fc; color:#c084fc;' : 'border:#00f0ff; color:#00f0ff;';
          const tagLabel = isApcat ? 'APCAT Provinciale' : (club.level === 'REGIONAL' ? 'ARCAT Regionale' : 'Club CAT');
          const distLabel = club.location.distance_km !== null ? `<span class="badge-dist">${club.location.distance_km} km</span>` : '';

          return `
            <div class="locator-result-card">
              <div>
                <div class="locator-card-top">
                  <span style="font-size:0.7rem; font-weight:800; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,0.06); ${tagClass}">${tagLabel}</span>
                  <span style="font-size:0.72rem; font-weight:750; color:#38bdf8; background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.25); padding:2px 6px; border-radius:4px;">${escapeHtml(club.families_label || (club.families_count + ' Famiglie'))}</span>
                  ${distLabel}
                </div>
                <h4 style="margin:8px 0 4px; color:#ffffff; font-size:1.05rem;">${escapeHtml(club.name)}</h4>
                <p style="margin:0; font-size:0.85rem; color:#94a3b8;">${escapeHtml(club.location.city)} (${escapeHtml(club.location.province || '')}) ${club.location.address ? '· ' + escapeHtml(club.location.address) : ''}</p>
                ${club.meeting.day ? `<p style="margin:4px 0 0; font-size:0.82rem; color:#cbd5e1;"><b>Incontro:</b> ${escapeHtml(club.meeting.day)} ${escapeHtml(club.meeting.time || '')}</p>` : ''}
              </div>
              <div class="locator-card-actions">
                ${club.actions.call_url ? `<a href="${club.actions.call_url}" class="btn-card-action btn-card-call">Chiama</a>` : ''}
                ${club.actions.whatsapp_url ? `<a href="${club.actions.whatsapp_url}" target="_blank" class="btn-card-action btn-card-wa">WhatsApp</a>` : ''}
                ${club.actions.map_directions_url ? `<a href="${club.actions.map_directions_url}" target="_blank" class="btn-card-action btn-card-nav">Mappa</a>` : ''}
                <button type="button" class="btn-card-action btn-card-share" 
                        data-club="${escapeHtml(club.name)}" 
                        data-city="${escapeHtml(club.location.city)}" 
                        data-day="${escapeHtml(club.meeting.day || '')}" 
                        data-time="${escapeHtml(club.meeting.time || '')}" 
                        data-addr="${escapeHtml(club.location.address || '')}"
                        onclick="window.handleShareClubBtn ? window.handleShareClubBtn(this) : (window.shareClubWithFamily && window.shareClubWithFamily(this.dataset.club, this.dataset.city, this.dataset.day, this.dataset.time, this.dataset.addr))"
                        style="background:rgba(255,215,0,0.12); color:#ffd700; border:1px solid rgba(255,215,0,0.35); cursor:pointer;" 
                        title="Condividi con un familiare">
                  Ti accompagno io
                </button>
              </div>
            </div>
          `;
        }).join('');
      })
      .catch(err => {
        gpsBtn.disabled = false;
        gpsBtn.innerHTML = '<?=dx_icon("navigation", "", 18)?> <span>Trova Vicino a Me (1 Tap GPS)</span>';
        resultsContainer.style.display = 'block';
        resultsContainer.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #ef4444; padding: 1rem;">Impossibile recuperare i dati. Riprova.</div>';
      });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  if (gpsBtn) {
    gpsBtn.addEventListener('click', function() {
      if (!navigator.geolocation) {
        alert('Geolocalizzazione non supportata dal tuo dispositivo.');
        return;
      }
      navigator.geolocation.getCurrentPosition(
        pos => fetchAndRenderClubs({ lat: pos.coords.latitude, lon: pos.coords.longitude, limit: 3 }),
        err => alert('Permesso posizione negato. Inserisci la tua città manualmente.')
      );
    });
  }

  if (searchBtn && cityInput) {
    searchBtn.addEventListener('click', function() {
      const q = cityInput.value.trim();
      if (!q) return;
      fetchAndRenderClubs({ q: q, limit: 3 });
    });
    cityInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        searchBtn.click();
      }
    });
  }
})();
</script>
