/* ============================================================
   Destino Randagio — Widget chat AI (Groq via chat.php)
   Due bot, un solo widget:
     • Pubblico  → "Destino Randagio" (logo DR corona) — vende KIT/album/wear
     • Riservato → "Noemi" (logo Branco) — SOLO membri, coaching del Branco.
   Attiva Noemi sulle pagine dashboard con, PRIMA di caricare lo script:
        <script>window.DR_CHAT_BOT='branco';</script>
   ============================================================ */
(function(){
  if (window.__drChat) return; window.__drChat = true;

  var BOT   = (window.DR_CHAT_BOT==='branco'||window.DR_CHAT_BOT==='admin') ? window.DR_CHAT_BOT : 'site';
  var IS_BR = BOT==='branco';
  var IS_AD = BOT==='admin';
  var LOGO  = IS_BR ? 'assets/noemi-logo.png' : 'assets/logo-corona.webp';
  var NAME  = IS_BR ? 'Noemi' : (IS_AD ? 'DR Admin' : 'Destino Randagio');
  var SUB   = IS_BR ? 'La tua guida nel Branco · riservata' : (IS_AD ? 'Assistente operativo · interno' : 'Assistente del sito · sempre online');
  var PH    = IS_BR ? 'Chiedi a Noemi…' : (IS_AD ? 'Chiedi all’assistente admin…' : 'Scrivi a Destino Randagio…');
  var HELLO = IS_BR
    ? 'Ciao, sono Noemi 🐺 la tua guida nel Branco. Vuoi salire di rango, invitare qualcuno o prenotare un’Experience? Dimmi da dove partiamo.'
    : (IS_AD
      ? 'Assistente admin pronto. Chiedimi KPI, come funziona un pezzo del sistema, o un testo/annuncio da preparare.'
      : 'Ciao, sono Destino Randagio. Chiedimi di album, wear, canzone su misura, NFT o del KIT del Branco — ti mostro tutto.');
  var hist = [];

  var css = document.createElement('style');
  css.textContent = [
    '#drChatBtn{position:fixed;right:20px;bottom:20px;z-index:9999;width:64px;height:64px;border-radius:50%;cursor:pointer;',
    'background:radial-gradient(circle at 30% 30%,#1a1a1a,#0a0a0a);border:2px solid #D4AF37;box-shadow:0 6px 24px rgba(212,175,55,.45);',
    'display:flex;align-items:center;justify-content:center;transition:transform .2s}',
    '#drChatBtn:hover{transform:scale(1.08)}',
    '#drChatBtn img{width:44px;height:44px;object-fit:contain;border-radius:50%}',
    '#drChatBtn .pulse{position:absolute;inset:-2px;border-radius:50%;border:2px solid #D4AF37;animation:drpulse 2.4s infinite;opacity:.6}',
    '@keyframes drpulse{0%{transform:scale(1);opacity:.6}100%{transform:scale(1.5);opacity:0}}',
    '#drChatWin{position:fixed;right:20px;bottom:96px;z-index:9999;width:360px;max-width:92vw;height:520px;max-height:76vh;',
    'background:linear-gradient(180deg,#141414,#0b0b0b);border:1px solid rgba(212,175,55,.5);border-radius:18px;overflow:hidden;',
    'display:none;flex-direction:column;box-shadow:0 18px 50px rgba(0,0,0,.6)}',
    '#drChatWin.open{display:flex}',
    '#drChatHead{display:flex;align-items:center;gap:10px;padding:12px 14px;background:linear-gradient(135deg,#1c1608,#0e0b04);border-bottom:1px solid rgba(212,175,55,.4)}',
    '#drChatHead img{width:36px;height:36px;object-fit:contain;border-radius:50%}',
    '#drChatHead b{color:#D4AF37;font-size:.95rem;letter-spacing:.5px}',
    '#drChatHead span{color:#8a8a8a;font-size:.7rem;display:block}',
    '#drChatHead .x{margin-left:auto;color:#9aa0a6;cursor:pointer;font-size:1.2rem;background:none;border:0}',
    '#drChatBody{flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;background:url(assets/pattern.png);background-size:200px}',
    '.drMsg{max-width:82%;padding:9px 12px;border-radius:14px;font-size:.9rem;line-height:1.4;white-space:pre-wrap}',
    '.drMsg.bot{align-self:flex-start;background:linear-gradient(180deg,#1c1608,#0e0b04);border:1px solid rgba(212,175,55,.4);color:#f0e6cf}',
    '.drMsg.me{align-self:flex-end;background:linear-gradient(135deg,#D4AF37,#b8942e);color:#160f00;font-weight:600}',
    '#drChatFoot{display:flex;gap:8px;padding:10px;border-top:1px solid rgba(212,175,55,.3);background:#0d0d0d}',
    '#drChatInput{flex:1;background:#0e0e0e;color:#fff;border:1px solid #333;border-radius:20px;padding:10px 14px;font-size:.9rem;outline:none}',
    '#drChatSend{background:linear-gradient(135deg,#D4AF37,#b8942e);color:#160f00;border:0;border-radius:20px;padding:0 16px;font-weight:800;cursor:pointer}'
  ].join('');
  document.head.appendChild(css);

  var btn = document.createElement('div');
  btn.id = 'drChatBtn';
  btn.innerHTML = '<span class="pulse"></span><img src="'+LOGO+'" alt="'+NAME+'" onerror="this.src=\'assets/emblem_gold.png\'">';
  document.body.appendChild(btn);

  var win = document.createElement('div');
  win.id = 'drChatWin';
  win.innerHTML =
    '<div id="drChatHead"><img src="'+LOGO+'" onerror="this.src=\'assets/emblem_gold.png\'"><div><b>'+NAME+'</b><span>'+SUB+'</span></div><button class="x" title="Chiudi">×</button></div>'+
    '<div id="drChatBody"></div>'+
    '<div id="drChatFoot"><input id="drChatInput" placeholder="'+PH+'" autocomplete="off"><button id="drChatSend">Invia</button></div>';
  document.body.appendChild(win);

  var body = win.querySelector('#drChatBody');
  var input = win.querySelector('#drChatInput');

  function add(role, text){
    var d = document.createElement('div');
    d.className = 'drMsg ' + (role==='me'?'me':'bot');
    d.textContent = text;
    body.appendChild(d); body.scrollTop = body.scrollHeight;
    return d;
  }
  function open(){ win.classList.add('open'); if(!body.childElementCount){ add('bot', HELLO); } input.focus(); }
  function close(){ win.classList.remove('open'); }

  btn.onclick = function(){ win.classList.contains('open') ? close() : open(); };
  win.querySelector('.x').onclick = close;

  function send(){
    var t = (input.value||'').trim(); if(!t) return;
    input.value=''; add('me', t); hist.push({role:'user',content:t});
    var wait = add('bot','…');
    fetch('chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:t,history:hist,bot:BOT})})
      .then(function(r){return r.json();})
      .then(function(j){ var rep=(j&&j.reply)?j.reply:'Sono qui, fratello. Riprova tra un attimo.'; wait.textContent=rep; hist.push({role:'assistant',content:rep}); body.scrollTop=body.scrollHeight; })
      .catch(function(){ wait.textContent='Connessione debole verso il Delta. Riprova.'; });
  }
  win.querySelector('#drChatSend').onclick = send;
  input.addEventListener('keydown', function(e){ if(e.key==='Enter') send(); });
})();
