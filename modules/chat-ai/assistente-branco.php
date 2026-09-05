<?php
/* ============================================================================
   GENESYS/ASSISTENTE-BRANCO — chat di assistenza del lancio Genesys
   Destino Randagio · 2026-08-01 · creato da Cowork

   ONESTÀ SU COME FUNZIONA (dichiarata anche all'utente, nella pagina):
   - LIVELLO 1 (sempre attivo, zero API esterne): motore di retrieval
     keyword+fuzzy in PHP+JS su una knowledge base locale di 35 Q&A REALI,
     costruita SOLO da fatti presenti nei file del sito (FAQ bastarde
     integrali di genesys.php, tokenomics, prezzi kit/sigilli, ranghi,
     referral, DAO). La KB vive in genesys/assistente-api.php (ab_kb()):
     UNA fonte sola, condivisa col proxy.
   - LIVELLO 2 (opzionale): se l'admin configura DR_AI_KEY (vuota di
     default), la chat diventa generativa via genesys/assistente-api.php
     (proxy server-side, chiave MAI esposta al client). Se il proxy
     risponde 503/errore, si torna in automatico al livello 1.
   - Fallback finale sempre presente: info@dependex.social.

   PRIVACY: cronologia SOLO in sessionStorage del browser dell'utente.
   dr_log() registra la domanda (troncata) — nessun dato personale extra.
   Pagina riservata ai membri loggati -> robots noindex,nofollow.
============================================================================ */
if (session_status() === PHP_SESSION_NONE) @session_start();
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../dr-security.php';
require_once __DIR__.'/../dr-log.php';
require_once __DIR__.'/assistente-api.php';   // KB condivisa (ab_kb) + ab_ai_attivo — l'endpoint NON parte se incluso
require_once __DIR__.'/_dr-seo.php';
require_once __DIR__.'/_dr-footer.php';

if (function_exists('dr_security_headers')) dr_security_headers();

$logged = !empty($_SESSION['uid']);
$uid    = (int)($_SESSION['uid'] ?? 0);

/* --- endpoint leggero di LOG domande (livello 1): il client segnala la
   domanda posta al motore locale, il server la logga con dr_log(). CSRF +
   rate limit. Non blocca mai la chat: se fallisce, la chat funziona lo
   stesso (il log è un di più, non una dipendenza). --- */
if ($logged && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && (($_POST['act'] ?? '') === 'logq')) {
  header('Content-Type: text/plain');
  if (!empty($_POST['dr_csrf']) && dr_csrf_ok() && dr_rate_limit('assistente-logq', 60, 3600)) {
    $q = mb_substr(trim((string)($_POST['q'] ?? '')), 0, 200);
    $liv = (($_POST['liv'] ?? '') === '2') ? 2 : 1;
    $hit = mb_substr(trim((string)($_POST['hit'] ?? '')), 0, 120);
    if ($q !== '') dr_log($pdo, 'assistente', 'domanda', ['q'=>$q, 'livello'=>$liv, 'match'=>$hit], $uid);
  }
  http_response_code(204); exit;
}

$AI_ON = ab_ai_attivo();   // true SOLO se DR_AI_KEY è configurata (mai la chiave al client, solo il flag)
$KB    = ab_kb();
if ($logged) dr_log($pdo, 'assistente', 'ui-apri', ['ai'=>$AI_ON ? 1 : 0], $uid);
function abu_e($s){ return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
?><!doctype html><html lang="it"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<?php dr_seo_head([
  'title'  => 'Assistente del Branco — supporto Genesys · Destino Randagio',
  'desc'   => 'La chat di assistenza del lancio Genesys: risposte oneste su Kit, Sigilli, DRX, DAO e roadmap.',
  'url'    => 'https://destinorandagio.it/genesys/assistente-branco.php',
  'robots' => 'noindex,nofollow',  /* PRIVATA: riservata a utenti loggati */
]); ?>
<style>
:root{--oro:#D4AF37;--oro2:#E8CE7A;--nero:#0D0B0A;--grigio:#A89880}
*{box-sizing:border-box}
body{background:var(--nero);color:#E8E0D4;font-family:'Segoe UI',Arial,sans-serif;margin:0}
.wrap{max-width:760px;margin:0 auto;padding:30px 16px 60px}
.hd{text-align:center;margin-bottom:14px}
.hd h1{font-size:1.7rem;letter-spacing:3px;color:var(--oro);text-transform:uppercase;margin:0}
.hd .sub{color:var(--grigio);font-size:.8rem;letter-spacing:1.5px;margin-top:6px}
.lvl{display:inline-block;margin-top:10px;padding:4px 14px;border-radius:20px;font-size:.68rem;font-weight:800;letter-spacing:1px}
.lvl.l1{background:rgba(212,175,55,.1);color:var(--oro2);border:1px solid rgba(212,175,55,.45)}
.lvl.l2{background:rgba(61,220,151,.08);color:#3ddc97;border:1px solid rgba(61,220,151,.5)}
.honest{background:rgba(212,175,55,.07);border:1px solid rgba(212,175,55,.35);border-radius:12px;
  padding:11px 16px;margin:14px 0;color:#c9bfa8;font-size:.78rem;line-height:1.6}
.honest b{color:var(--oro2)}
.chatbox{padding:0;overflow:hidden}
.log{height:56vh;min-height:340px;overflow-y:auto;padding:20px 18px;display:flex;flex-direction:column;gap:12px;scroll-behavior:smooth}
.msg{max-width:82%;padding:11px 15px;border-radius:16px;font-size:.9rem;line-height:1.6;white-space:pre-wrap;word-wrap:break-word}
.msg.user{align-self:flex-end;background:linear-gradient(135deg,#D4AF37,#b8942e);color:#160f00;font-weight:600;border-bottom-right-radius:4px}
.msg.bot{align-self:flex-start;background:#17140e;border:1px solid rgba(212,175,55,.3);color:#E8E0D4;border-bottom-left-radius:4px}
.msg.bot .src{display:block;margin-top:8px;font-size:.66rem;color:#8d8267;letter-spacing:.5px}
.msg.bot .also{display:block;margin-top:8px;font-size:.74rem;color:var(--oro2)}
.msg.bot .also a{color:var(--oro2);text-decoration:underline;cursor:pointer}
.typing{align-self:flex-start;background:#17140e;border:1px solid rgba(212,175,55,.3);border-radius:16px;padding:13px 18px;display:none}
.typing.on{display:block}
.typing i{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--oro);margin:0 2px;animation:abp 1.1s infinite}
.typing i:nth-child(2){animation-delay:.18s}.typing i:nth-child(3){animation-delay:.36s}
@keyframes abp{0%,60%,100%{opacity:.25;transform:translateY(0)}30%{opacity:1;transform:translateY(-4px)}}
.inrow{display:flex;gap:10px;padding:14px;border-top:1px solid rgba(212,175,55,.25);background:#0f0d09}
.inrow input{flex:1;background:#12100c;color:#fff;border:1px solid rgba(212,175,55,.35);border-radius:22px;padding:12px 18px;font-size:.92rem;font-family:inherit}
.inrow input:focus{outline:none;border-color:var(--oro)}
.inrow button{background:linear-gradient(135deg,#D4AF37,#b8942e);color:#160f00;font-weight:800;border:0;border-radius:22px;padding:12px 22px;cursor:pointer;font-size:.9rem}
.inrow button:disabled{opacity:.5;cursor:default}
.chips{display:flex;gap:8px;flex-wrap:wrap;margin:14px 0 0}
.chip{background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.4);color:var(--oro2);
  border-radius:18px;padding:7px 14px;font-size:.75rem;cursor:pointer;transition:background .2s}
.chip:hover{background:rgba(212,175,55,.2)}
.gate{max-width:540px;margin:8vh auto;text-align:center;padding:38px 30px}
.gate h1{color:var(--oro);letter-spacing:2px}
.gbtn{display:inline-block;background:linear-gradient(135deg,#D4AF37,#b8942e);color:#160f00;font-weight:800;border-radius:24px;padding:12px 26px;text-decoration:none;margin:6px}
.gbtn.s{background:transparent;border:2px solid var(--oro);color:var(--oro)}
.muted{color:#8d8267;font-size:.74rem;line-height:1.6}
a{color:var(--oro)}
</style></head>
<body>
<div class="wrap">

<?php if(!$logged): ?>
  <div class="dr-card lx gate">
    <h1>💬 Assistente del Branco</h1>
    <p style="color:#cfc9bb;line-height:1.7">L'assistenza del lancio Genesys è riservata ai membri. Accedi per fare le tue domande — anche le più bastarde.</p>
    <p style="margin-top:18px"><a class="gbtn" href="../account.php">Accedi</a> <a class="gbtn s" href="../genesys.php">Scopri Genesys</a></p>
    <p class="muted" style="margin-top:14px">In alternativa scrivi a <a href="mailto:info@dependex.social">info@dependex.social</a></p>
  </div>
<?php else: ?>

  <header class="hd">
    <h1>💬 Assistente <span style="color:#E8E0D4;font-weight:300">del Branco</span></h1>
    <div class="sub">SUPPORTO GENESYS · RISPOSTE ONESTE, ANCHE ALLE DOMANDE BASTARDE</div>
    <?php if($AI_ON): ?>
      <span class="lvl l2">● AI GENERATIVA ATTIVA · con fallback locale</span>
    <?php else: ?>
      <span class="lvl l1">● MOTORE LOCALE · knowledge base ufficiale, nessuna AI esterna</span>
    <?php endif; ?>
  </header>

  <div class="honest">🐺 <b>Come funziona, senza fumo:</b>
    <?php if($AI_ON): ?>
      questa chat usa un'AI generativa istruita SOLO sulla knowledge base ufficiale del Branco; se l'AI non risponde, subentra il motore locale (ricerca nelle <?=count($KB)?> risposte ufficiali).
    <?php else: ?>
      questa chat NON è un'AI generativa: è un motore di ricerca sulle <?=count($KB)?> risposte ufficiali del Branco (fatti reali del sito, niente inventato).
    <?php endif; ?>
    Non dà consigli finanziari: DRX e 81X sono utilità interne, non investimenti. Se non trovi la risposta: <b>info@dependex.social</b> o un <a href="ticket.php">ticket di supporto</a>. La cronologia resta solo nel tuo browser.</div>

  <div class="dr-card lx chatbox">
    <div class="log" id="abLog"></div>
    <div class="typing" id="abTyping"><i></i><i></i><i></i></div>
    <form class="inrow" id="abForm" autocomplete="off">
      <input type="text" id="abIn" maxlength="500" placeholder="Chiedi del Kit, dei Sigilli, dei DRX, della DAO…" required>
      <button type="submit" id="abSend">Invia</button>
    </form>
  </div>

  <div class="chips" id="abChips">
    <span class="chip">Quanto costa il Kit Genesys?</span>
    <span class="chip">I DRX sono un investimento?</span>
    <span class="chip">Come funziona il mint dei Sigilli?</span>
    <span class="chip">La DAO conta davvero o è teatro?</span>
    <span class="chip">Qual è la roadmap 2026-2035?</span>
    <span class="chip">Avete un audit di terze parti?</span>
  </div>

  <p class="muted" style="margin-top:16px;text-align:center">Le domande vengono registrate (solo il testo, per migliorare le risposte) — nessun dato personale extra. <a href="dao-genesys.php">DAO Genesys</a> · <a href="ticket.php">Supporto ticket</a> · <a href="../genesys.php">Genesys</a></p>

<script>
/* ==========================================================================
   ASSISTENTE DEL BRANCO — livello 1: retrieval keyword+fuzzy, tutto client.
   KB generata server-side (PHP, fonte unica ab_kb in assistente-api.php).
========================================================================== */
var AB_KB    = <?=json_encode(array_map(function($e){ return ['q'=>$e['q'],'a'=>$e['a'],'k'=>$e['k']]; }, $KB), JSON_UNESCAPED_UNICODE)?>;
var AB_AI    = <?=$AI_ON ? 'true' : 'false'?>;
var AB_CSRF  = <?=json_encode(dr_csrf_token())?>;
var AB_FALL  = "Su questo non ho una risposta ufficiale nella mia base di conoscenza — e preferisco dirtelo invece di inventare. Scrivi a info@dependex.social oppure apri un ticket dalla pagina Supporto: un umano del Branco ti risponde.";
var AB_HELLO = "Benvenuto nel Branco. 🐺 Sono l'Assistente Genesys: chiedimi del Kit (397€), degli 8.000 Sigilli, dei DRX, della DAO o della roadmap. Rispondo con i fatti ufficiali — comprese le risposte scomode. Cosa vuoi sapere?";

var $log=document.getElementById('abLog'), $typ=document.getElementById('abTyping'),
    $form=document.getElementById('abForm'), $in=document.getElementById('abIn'), $send=document.getElementById('abSend');
var hist=[];  /* [{role,content}] per il livello 2 */

/* ---- normalizzazione: minuscole, niente accenti, niente punteggiatura ---- */
var STOP={'il':1,'lo':1,'la':1,'i':1,'gli':1,'le':1,'un':1,'una':1,'uno':1,'di':1,'a':1,'da':1,'in':1,'con':1,'su':1,'per':1,'tra':1,'fra':1,'e':1,'o':1,'ma':1,'che':1,'chi':1,'cui':1,'non':1,'si':1,'se':1,'del':1,'della':1,'dei':1,'delle':1,'al':1,'alla':1,'ai':1,'alle':1,'nel':1,'nella':1,'sono':1,'sei':1,'è':1,'come':1,'cosa':1,'cos':1,'quanto':1,'quanti':1,'quante':1,'quale':1,'quali':1,'quando':1,'dove':1,'perche':1,'posso':1,'puo':1,'mi':1,'ti':1,'ci':1,'vi':1,'io':1,'tu':1,'voi':1,'noi':1,'questo':1,'questa':1,'c':1,'l':1,'d':1,'ce':1,'me':1,'più':1,'piu':1};
function norm(s){
  s=(s||'').toLowerCase();
  try{ s=s.normalize('NFD').replace(/[\u0300-\u036f]/g,''); }catch(e){}
  return s.replace(/[^a-z0-9\s]/g,' ').replace(/\s+/g,' ').trim();
}
function toks(s){ return norm(s).split(' ').filter(function(t){ return t.length>1 && !STOP[t]; }); }

/* ---- distanza di Levenshtein limitata (fuzzy: tollera piccoli refusi) ---- */
function lev(a,b,max){
  if(Math.abs(a.length-b.length)>max) return max+1;
  var prev=[],cur=[],i,j;
  for(j=0;j<=b.length;j++) prev[j]=j;
  for(i=1;i<=a.length;i++){
    cur=[i]; var best=i;
    for(j=1;j<=b.length;j++){
      cur[j]=Math.min(prev[j]+1,cur[j-1]+1,prev[j-1]+(a[i-1]===b[j-1]?0:1));
      if(cur[j]<best) best=cur[j];
    }
    if(best>max) return max+1;
    prev=cur;
  }
  return prev[b.length];
}
function tokMatch(qt,kt){ /* match esatto, prefisso, o fuzzy su parole lunghe:
  1 refuso ammesso; 2 solo su parole molto lunghe (>=8) — evita falsi cugini
  tipo "referal"~"reveal" tenendo "sigili"~"sigilli" */
  if(qt===kt) return 1;
  if(kt.length>3 && (qt.indexOf(kt)===0||kt.indexOf(qt)===0)) return .85;
  if(qt.length>4 && kt.length>4){
    var mx=(qt.length>=8&&kt.length>=8)?2:1;
    if(lev(qt,kt,mx)<=mx) return .7;
  }
  return 0;
}

/* ---- scoring di una entry KB contro la domanda ---- */
function scoreEntry(qtoks,qnorm,e){
  var s=0,i,j;
  var ktoks=[]; for(i=0;i<e.k.length;i++){ var kt=toks(e.k[i]); for(j=0;j<kt.length;j++) ktoks.push(kt[j]);
    if(kt.length>1 && qnorm.indexOf(norm(e.k[i]))>=0) s+=2.5; /* keyword multi-parola presente per intero */
  }
  var qtq=toks(e.q);
  for(i=0;i<qtoks.length;i++){
    var best=0;
    for(j=0;j<ktoks.length;j++){ var m=tokMatch(qtoks[i],ktoks[j]); m=(m>=.85)?m*1.6:m; /* il boost keyword solo su match solidi, non sul fuzzy */ if(m>best) best=m; }
    for(j=0;j<qtq.length;j++){ var m2=tokMatch(qtoks[i],qtq[j]); if(m2>best) best=m2; }
    s+=best;
  }
  return s;
}
function retrieve(q){
  var qn=norm(q),qt=toks(q),res=[];
  for(var i=0;i<AB_KB.length;i++) res.push({i:i,s:scoreEntry(qt,qn,AB_KB[i])});
  res.sort(function(a,b){ return b.s-a.s; });
  var need = qt.length<=1 ? 0.65 : Math.min(2.6, Math.max(1.6, qt.length*0.5)); /* soglia: sotto, meglio il fallback onesto; cap per le domande lunghe */
  return { best: res[0].s>=need ? AB_KB[res[0].i] : null,
           more: res.slice(1,3).filter(function(r){ return r.s>=need*0.8; }).map(function(r){ return AB_KB[r.i]; }) };
}

/* ---- UI ---- */
function esc(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
function bubble(role,text,srcLabel,also){
  var m=document.createElement('div'); m.className='msg '+(role==='user'?'user':'bot');
  m.innerHTML=esc(text);
  if(role!=='user'&&srcLabel) m.innerHTML+='<span class="src">'+esc(srcLabel)+'</span>';
  if(role!=='user'&&also&&also.length){
    var h='<span class="also">Forse cercavi anche: ';
    for(var i=0;i<also.length;i++) h+=(i?' · ':'')+'<a data-q="'+esc(also[i].q)+'">'+esc(also[i].q)+'</a>';
    m.innerHTML+=h+'</span>';
  }
  $log.appendChild(m); $log.scrollTop=$log.scrollHeight;
  return m;
}
function saveHist(){ try{ sessionStorage.setItem('dr_ab_history', JSON.stringify(hist.slice(-24))); }catch(e){} }
function typing(on){ $typ.className='typing'+(on?' on':''); if(on){ $log.appendChild($typ); $log.scrollTop=$log.scrollHeight; } }

/* log domanda al server (fire-and-forget: se fallisce, la chat vive lo stesso) */
function logQ(q,liv,hit){
  try{
    var fd=new FormData(); fd.append('act','logq'); fd.append('dr_csrf',AB_CSRF);
    fd.append('q',q); fd.append('liv',liv); fd.append('hit',hit||'');
    fetch('assistente-branco.php',{method:'POST',body:fd,keepalive:true}).catch(function(){});
  }catch(e){}
}

function answerLocal(q){
  var r=retrieve(q), delay=420+Math.random()*480; /* piccola pausa: leggibilità, non finzione — il badge dice già che è un motore locale */
  typing(true);
  setTimeout(function(){
    if(r.best){
      typing(false);
      bubble('bot',r.best.a,'Motore locale · knowledge base ufficiale del Branco',r.more);
      hist.push({role:'assistant',content:r.best.a}); saveHist();
      logQ(q,1,r.best.q);
      return;
    }
    /* nessun match locale -> interroga la memoria del Cortex (organismo Neuralog).
       Se il Cortex ha digerito un documento pertinente, risponde citando la fonte;
       altrimenti mostra il fallback finale (info@ / ticket). */
    fetch('assistente-cortex.php?q='+encodeURIComponent(q),{keepalive:true})
      .then(function(res){ return res.json(); })
      .then(function(j){
        typing(false);
        if(j&&j.ok&&j.trovata&&j.risposta){
          bubble('bot',j.risposta, j.fonte || 'Cortex · memoria dell’organismo');
          hist.push({role:'assistant',content:j.risposta}); saveHist();
          logQ(q,1,'cortex');
        } else {
          bubble('bot',AB_FALL,'Motore locale · nessuna corrispondenza');
          hist.push({role:'assistant',content:AB_FALL}); saveHist();
          logQ(q,1,'nessun-match');
        }
      })
      .catch(function(){
        typing(false);
        bubble('bot',AB_FALL,'Motore locale · nessuna corrispondenza');
        hist.push({role:'assistant',content:AB_FALL}); saveHist();
        logQ(q,1,'nessun-match');
      });
  },delay);
}

function answerAI(q){
  typing(true);
  var payload={messages:hist.slice(-10)};
  fetch('assistente-api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
    .then(function(r){ return r.json().then(function(j){ return {st:r.status,j:j}; }); })
    .then(function(o){
      typing(false);
      if(o.st===200&&o.j&&o.j.ok&&o.j.text){
        bubble('bot',o.j.text,'AI generativa · istruita sulla KB ufficiale — verifica sempre i dati chiave sulle pagine del sito');
        hist.push({role:'assistant',content:o.j.text}); saveHist();
        logQ(q,2,'ai');
      } else if(o.st===429){
        bubble('bot',(o.j&&o.j.msg)||'Limite orario AI raggiunto: continuo col motore locale.','Limite AI');
        answerLocal(q);
      } else {
        /* 503 chiave non configurata, 502 errore API, altro -> livello 1 */
        answerLocal(q);
      }
    })
    .catch(function(){ typing(false); answerLocal(q); });
}

function ask(q){
  q=(q||'').trim(); if(!q) return;
  bubble('user',q);
  hist.push({role:'user',content:q}); saveHist();
  $in.value=''; $send.disabled=true;
  setTimeout(function(){ $send.disabled=false; },600);
  if(AB_AI) answerAI(q); else answerLocal(q);
}

$form.addEventListener('submit',function(ev){ ev.preventDefault(); ask($in.value); });
document.getElementById('abChips').addEventListener('click',function(ev){
  var c=ev.target.closest('.chip'); if(c) ask(c.textContent);
});
$log.addEventListener('click',function(ev){
  var a=ev.target.closest('a[data-q]'); if(a){ ev.preventDefault(); ask(a.getAttribute('data-q')); }
});

/* ripristino cronologia della sessione (solo browser dell'utente) */
(function(){
  try{
    var h=JSON.parse(sessionStorage.getItem('dr_ab_history')||'[]');
    if(h.length){ hist=h; for(var i=0;i<h.length;i++) bubble(h[i].role,h[i].content); return; }
  }catch(e){}
  bubble('bot',AB_HELLO,'Assistente del Branco');
  hist.push({role:'assistant',content:AB_HELLO});
})();
</script>

<?php endif; ?>
</div>
<?php dr_footer_luxury(''); ?>
</body></html>
