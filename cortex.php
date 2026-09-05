<?php
require_once 'bootstrap.php';
$u = require_login();
$pageTitle = 'Cortex · Company Brain';
require '_header.php';
?>
<section class="cortex-head">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div></div>
    <a href="cortex-dashboard.php" class="btn btn-sm btn-outline-warning" style="border-radius:20px;">📊 Dashboard Cervello</a>
  </div>
  <div class="cortex-orb">🧠</div>
  <h1>Ciao <?=h($u['display_name'])?>.</h1>
  <p>Cortex Company Brain · Assistente cognitivo ed esecutivo dell'ecosistema.</p>
</section>

<div class="prompt-grid">
  <button data-fill="Vorrei parlare di come sto oggi">💬 Parla con me</button>
  <button data-fill="Trova il Club più vicino a me">🤝 Trova Club</button>
  <button data-fill="Mostrami l'impronta aziendale">🧠 Impronta</button>
  <button data-fill="Mappa del tesoro e automazioni">🗺️ Mappa Tesoro</button>
  <button data-fill="Attiva il primo ingranaggio">⚙️ 1° Ingranaggio</button>
  <button data-fill="Spiegami il metodo Hudolin">🎓 Metodo</button>
</div>

<section class="chat-shell">
  <div id="chatMessages">
    <div class="msg ai">Sono Cortex, il cervello digitale di DEPENDEX e OLTRE. Conosco la struttura, i Club, l'Academy, le procedure e le automazioni dell'ecosistema. Come posso aiutarti oggi?</div>
  </div>
  <form id="chatForm">
    <button type="button" id="voiceBtn" aria-label="Parla">🎙</button>
    <input id="chatInput" maxlength="1000" placeholder="Chiedi a Cortex…" autocomplete="off">
    <button aria-label="Invia">➤</button>
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
    let r = await fetch('cortex-api.php', {
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