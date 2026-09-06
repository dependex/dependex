// Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('service-worker.js').catch(() => {});
  });
}

// Toast notification helper
function toast(t) {
  const e = document.createElement('div');
  e.className = 'toast';
  e.setAttribute('role', 'status');
  e.setAttribute('aria-live', 'polite');
  e.textContent = t;
  document.body.appendChild(e);
  setTimeout(() => e.remove(), 2800);
}

document.querySelectorAll('[data-toast]').forEach(b => {
  b.addEventListener('click', () => toast(b.dataset.toast));
});

// Prompt Fill Helpers
document.querySelectorAll('[data-fill]').forEach(b => {
  b.addEventListener('click', () => {
    const i = document.querySelector('#chatInput');
    if (i) {
      i.value = b.dataset.fill;
      i.focus();
    }
  });
});

// PWA Install Prompt & Offline Detection
let deferredInstallPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredInstallPrompt = e;
  const banner = document.querySelector('#pwaBanner');
  if (banner && !localStorage.getItem('pwa_prompt_dismissed')) {
    banner.style.display = 'flex';
  }
});

window.addEventListener('appinstalled', () => {
  deferredInstallPrompt = null;
  const banner = document.querySelector('#pwaBanner');
  if (banner) banner.style.display = 'none';
  toast('OLTRE installata con successo sulla schermata Home!');
});

document.addEventListener('click', async (e) => {
  if (e.target.closest('#pwaInstallBtn')) {
    if (deferredInstallPrompt) {
      deferredInstallPrompt.prompt();
      const { outcome } = await deferredInstallPrompt.userChoice;
      if (outcome === 'accepted') {
        deferredInstallPrompt = null;
        const banner = document.querySelector('#pwaBanner');
        if (banner) banner.style.display = 'none';
      }
    } else {
      toast('Per installare su iOS o altri browser: tocca Condividi o Menu e seleziona "Aggiungi alla schermata Home"');
    }
  }
  if (e.target.closest('#pwaDismissBtn')) {
    const banner = document.querySelector('#pwaBanner');
    if (banner) banner.style.display = 'none';
    localStorage.setItem('pwa_prompt_dismissed', '1');
  }
});

// Drawer & Burger Controller
const burgerBtn = document.querySelector('#burgerBtn');
const drawer = document.querySelector('#drawerNav');
const backdrop = document.querySelector('#drawerBackdrop');
const drawerCloseBtn = document.querySelector('#drawerCloseBtn');

function openDrawer() {
  if (!drawer) return;
  drawer.classList.add('is-open');
  if (backdrop) backdrop.classList.add('is-open');
  if (burgerBtn) {
    burgerBtn.classList.add('is-active');
    burgerBtn.setAttribute('aria-expanded', 'true');
  }
  document.body.style.overflow = 'hidden';
}

function closeDrawer() {
  if (!drawer) return;
  drawer.classList.remove('is-open');
  if (backdrop) backdrop.classList.remove('is-open');
  if (burgerBtn) {
    burgerBtn.classList.remove('is-active');
    burgerBtn.setAttribute('aria-expanded', 'false');
  }
  document.body.style.overflow = '';
}

if (burgerBtn) {
  burgerBtn.addEventListener('click', () => {
    if (drawer && drawer.classList.contains('is-open')) {
      closeDrawer();
    } else {
      openDrawer();
    }
  });
}
if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', closeDrawer);
if (backdrop) backdrop.addEventListener('click', closeDrawer);
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
    closeDrawer();
  }
});

// Auto-close drawer on internal link navigation
document.querySelectorAll('.drawer-link').forEach(link => {
  link.addEventListener('click', () => {
    const href = link.getAttribute('href');
    if (href && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
      closeDrawer();
    }
  });
});

// Touch swipe-to-close for drawer (iOS / Android HIG Pattern)
let touchStartX = 0;
let touchCurrentX = 0;
if (drawer) {
  drawer.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });
  drawer.addEventListener('touchmove', (e) => {
    touchCurrentX = e.changedTouches[0].screenX;
  }, { passive: true });
  drawer.addEventListener('touchend', () => {
    if (touchCurrentX > touchStartX + 60) {
      closeDrawer();
    }
  }, { passive: true });
}

// Ambient Cursor Glow for Luxury Cards (Emil Kowalski / Better UI Pattern)
if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
  const cards = document.querySelectorAll('.lux-metallic-card, .offer-card, .luxury-hero-card, .feature-card, .drawer-user-card');
  cards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      card.style.setProperty('--mouse-x', `${x}px`);
      card.style.setProperty('--mouse-y', `${y}px`);
    });
  });
}


function updateNetworkStatus() {
  if (navigator.onLine) {
    document.body.classList.remove('is-offline');
  } else {
    document.body.classList.add('is-offline');
    toast('Sei offline: i contenuti salvati restano utilizzabili');
  }
}
window.addEventListener('online', updateNetworkStatus);
window.addEventListener('offline', updateNetworkStatus);
updateNetworkStatus();

// Theme Controller (Light/Dark Mode)
function initTheme() {
  const saved = localStorage.getItem('oltre_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-theme', saved);
  document.querySelectorAll('.theme-toggle').forEach(btn => {
    btn.textContent = saved === 'dark' ? '☀️' : '🌙';
    btn.setAttribute('aria-label', saved === 'dark' ? 'Passa al tema chiaro' : 'Passa al tema scuro');
  });
}

document.addEventListener('click', (e) => {
  const btn = e.target.closest('.theme-toggle');
  if (!btn) return;
  const current = document.documentElement.getAttribute('data-theme') || 'light';
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('oltre_theme', next);
  initTheme();
});
initTheme();

// Check-in Slider value live preview
document.querySelectorAll('.checkin-slider').forEach(slider => {
  slider.addEventListener('input', () => {
    const target = document.querySelector('#val_' + slider.name);
    if (target) target.textContent = slider.value + '/10';
  });
});

// Network Graph Visualization
async function initNetwork() {
  const svg = document.querySelector('#networkSvg');
  if (!svg) return;
  let data = [];
  try {
    data = await fetch('api.php?action=network&scope=' + (window.NETWORK_SCOPE || 'ITALY')).then(r => r.json());
  } catch (e) {
    svg.innerHTML = '<text x="40" y="60">Impossibile caricare il network.</text>';
    return;
  }
  const reg = document.querySelector('#regionFilter');
  if (reg) {
    [...new Set(data.map(x => x.region).filter(Boolean))].sort().forEach(x => {
      let o = document.createElement('option');
      o.value = x;
      o.textContent = x;
      reg.appendChild(o);
    });
  }
  let mode = 'tree';
  const search = document.querySelector('#networkSearch');
  const card = document.querySelector('#nodeCard');
  function render() {
    const term = (search ? search.value : '').toLowerCase();
    const region = reg ? reg.value : '';
    let nodes = data.filter(n => (!region || n.region === region) && (!term || [n.entity_name, n.comune, n.province, n.region].join(' ').toLowerCase().includes(term)));
    if (!term && !region) nodes = data.slice(0, 180);
    const colors = { NATIONAL: '#e0a72f', REGIONAL: '#4a77a6', PROVINCIAL: '#6e67ad', TERRITORIAL: '#2c8d78', CLUB: '#23a878' };
    const levelY = { NATIONAL: 70, REGIONAL: 180, PROVINCIAL: 270, TERRITORIAL: 365, CLUB: 520 };
    const pos = {};
    const w = 1160;
    const groups = {};
    nodes.forEach(n => (groups[n.level] ??= []).push(n));
    Object.entries(groups).forEach(([lev, arr]) => arr.forEach((n, i) => {
      let x = 40 + (w - 80) * (i + 1) / (arr.length + 1);
      let y = levelY[lev] || 600;
      if (mode === 'graph') {
        x = 80 + ((i * 137) % 1040);
        y = 90 + ((i * 83) % 560);
      }
      pos[n.sic_id] = { x, y };
    }));
    let out = '';
    nodes.forEach(n => {
      if (n.parent_sic_id && pos[n.parent_sic_id] && pos[n.sic_id]) {
        let a = pos[n.parent_sic_id], b = pos[n.sic_id];
        out += `<path class="net-edge" d="M${a.x},${a.y} C${a.x},${(a.y+b.y)/2} ${b.x},${(a.y+b.y)/2} ${b.x},${b.y}"/>`;
      }
    });
    nodes.forEach(n => {
      const p = pos[n.sic_id];
      if (!p) return;
      const r = n.level === 'CLUB' ? 7 : n.level === 'TERRITORIAL' ? 10 : n.level === 'REGIONAL' ? 13 : 16;
      const label = (n.entity_name || '').replace(/&/g, '&amp;').replace(/</g, '&lt;');
      out += `<g class="net-node" data-id="${n.sic_id}"><circle cx="${p.x}" cy="${p.y}" r="${r}" fill="${colors[n.level] || '#777'}"></circle><text x="${p.x+12}" y="${p.y+4}">${label.slice(0,24)}</text></g>`;
    });
    svg.innerHTML = out;
    svg.querySelectorAll('.net-node').forEach(g => g.addEventListener('click', () => {
      const n = data.find(x => x.sic_id === g.dataset.id);
      if (card && n) {
        card.classList.remove('hidden');
        card.innerHTML = `<b>${n.entity_name}</b><br><span>${[n.comune, n.province, n.region].filter(Boolean).join(' · ')}</span><br><small>${n.address || 'Indirizzo non disponibile'}<br>${n.meeting_day || ''} ${n.meeting_time || ''}<br>${n.sic_id}</small>`;
      }
    }));
  }
  if (search) search.addEventListener('input', render);
  if (reg) reg.addEventListener('change', render);
  document.querySelectorAll('[data-network-view]').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('[data-network-view]').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    mode = b.dataset.networkView;
    render();
  }));
  render();
}
initNetwork();
