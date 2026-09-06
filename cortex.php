<?php
require_once 'bootstrap.php';
$u = require_login();
$pageTitle = 'Cortex · Company Brain';
require '_header.php';
?>
<section class="cortex-head">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div></div>
    <a href="cortex-dashboard.php" class="btn btn-sm btn-outline-warning" style="border-radius:20px;display:inline-flex;align-items:center;gap:6px;">
      <?=dx_icon('activity', '', 14)?> <span>Dashboard Cervello</span>
    </a>
  </div>
  <div class="cortex-orb" style="background: var(--rainbow-gradient); box-shadow: 0 0 35px rgba(0, 212, 255, 0.5), 0 0 50px rgba(184, 41, 255, 0.3); color: #ffffff;">
    <?=dx_icon('brain', '', 42)?>
  </div>
  <h1 style="color:#FFFFFF;font-family:var(--font-serif);margin-top:12px;">Ciao <?=h($u['display_name'])?>.</h1>
  <p style="color:#cbd5e1;">Cortex Company Brain · Assistente cognitivo ed esecutivo dell'ecosistema.</p>
</section>

<div class="prompt-grid">
  <button data-fill="Vorrei parlare di come sto oggi" style="display:flex;align-items:center;gap:8px;">
    <?=dx_icon('message-circle', '', 16)?> <span>Parla con me</span>
  </button>
  <button data-fill="Trova il Club più vicino a me" style="display:flex;align-items:center;gap:8px;">
    <?=dx_icon('map-pin', '', 16)?> <span>Trova Club</span>
  </button>
  <button data-fill="Mostrami l'impronta aziendale" style="display:flex;align-items:center;gap:8px;">
    <?=dx_icon('compass', '', 16)?> <span>Impronta</span>
  </button>
  <button data-fill="Mappa del tesoro e automazioni" style="display:flex;align-items:center;gap:8px;">
    <?=dx_icon('sparkles', '', 16)?> <span>Mappa Tesoro</span>
  </button>
  <button data-fill="Attiva il primo ingranaggio" style="display:flex;align-items:center;gap:8px;">
    <?=dx_icon('zap', '', 16)?> <span>1° Ingranaggio</span>
  </button>
  <button data-fill="Spiegami il metodo Hudolin" style="display:flex;align-items:center;gap:8px;">
    <?=dx_icon('feather', '', 16)?> <span>Metodo</span>
  </button>
</div>

<section class="chat-shell" style="border: 1px solid rgba(212,175,55,0.25); background: #101116;">
  <div id="chatMessages">
    <div class="msg ai" style="background: rgba(212,175,55,0.08); border: 1px solid rgba(212,175,55,0.2); color: #FFFFFF;">
      Sono Cortex, il Company Brain di DEPENDEX. Conosco la struttura, i Club, l'Academy, le procedure e la Scala Valore dell'ecosistema. Come posso orientarti oggi?
    </div>
  </div>
  <form id="chatForm" style="border-top: 1px solid rgba(212,175,55,0.2);">
    <button type="button" id="voiceBtn" aria-label="Parla" style="background:transparent;color:#D4AF37;border:0;">
      <?=dx_icon('message-circle', '', 18)?>
    </button>
    <input id="chatInput" maxlength="1000" placeholder="Chiedi a Cortex…" autocomplete="off" style="background:transparent;color:#FFFFFF;">
    <button aria-label="Invia" style="background:linear-gradient(135deg, #FFF2B2, #D4AF37);color:#070709;border:0;">
      <?=dx_icon('arrow-right', '', 18)?>
    </button>
  </form>
</section>

<script>
let dxConv = '';
const form = document.querySelector('#chatForm');
const inp = document.querySelector('#chatInput');
const box = document.querySelector('#chatMessages');

function formatText(t) {
  return t
    .replace(/\n/g, '<br>')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/• (.*?)(<br>|$)/g, '<li>$1</li>');
}

function add(role, text, isHtml = false) {
  const d = document.createElement('div');
  d.className = 'msg ' + role;
  if (isHtml) {
    d.innerHTML = formatText(text);
  } else {
    d.textContent = text;
  }
  box.appendChild(d);
  box.scrollTop = box.scrollHeight;
  return d;
}

form.addEventListener('submit', async e => {
  e.preventDefault();
  let q = inp.value.trim();
  if (!q) return;

  add('user', q);
  inp.value = '';
  let wait = add('ai', 'Sto elaborando nel Company Brain…');

  try {
    let r = await fetch('api-cortex.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ q, conversation_sic_id: dxConv })
    });
    let j = await r.json();
    wait.remove();

    if (j.ok) {
      dxConv = j.conversation_sic_id;
      add('ai', j.answer, true);
      if ('speechSynthesis' in window && document.body.dataset.voice === 'on') {
        speechSynthesis.speak(new SpeechSynthesisUtterance(j.answer.replace(/[*#]/g, '')));
      }
    } else {
      add('ai', 'Non riesco a rispondere in questo momento.');
    }
  } catch(err) {
    wait.textContent = 'Connessione non disponibile.';
  }
});

document.querySelectorAll('[data-fill]').forEach(b => {
  b.onclick = () => {
    inp.value = b.dataset.fill;
    inp.focus();
  };
});

// Auto-fill from query string parameter ?q=...
window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const initialQ = params.get('q');
  if (initialQ) {
    inp.value = initialQ;
    form.dispatchEvent(new Event('submit'));
  }
});

const vb = document.querySelector('#voiceBtn');
const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
if (SR) {
  const rec = new SR();
  rec.lang = 'it-IT';
  rec.interimResults = false;
  vb.onclick = () => rec.start();
  rec.onresult = e => {
    inp.value = e.results[0][0].transcript;
    inp.focus();
  };
} else {
  vb.disabled = true;
}
</script>

<?php require '_footer.php'; ?>