<?php
require_once 'bootstrap.php';$u=require_login();$pageTitle='NCKE Query Console';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">Adaptive Knowledge Retrieval</span><h1>Cortex Query Console</h1>
<p>Interroga la conoscenza viva. Senza provider AI il sistema lavora in retrieval-only con fonti; con provider disponibili attiverà generazione/reranking.</p></div></section>
<section class="card">
<form id="nckeForm" class="stack"><label>Domanda<textarea id="nckeQ" rows="4" placeholder="Es. Qual è la gerarchia mondiale dei Club?"></textarea></label>
<button class="btn primary">Interroga NCKE</button></form>
<div id="nckeResult" class="ncke-result"></div>
</section>
<script>
document.querySelector('#nckeForm').addEventListener('submit',async e=>{
 e.preventDefault();const q=document.querySelector('#nckeQ').value.trim(),box=document.querySelector('#nckeResult');if(!q)return;
 box.innerHTML='<p>Ricerca in corso…</p>';
 try{
  const body=new URLSearchParams({q});
  const r=await fetch('ncke/runtime/query.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body});
  const d=await r.json();
  if(!d.ok){box.textContent=d.error||'Errore';return}
  box.innerHTML=`<div class="success"><b>${d.intent}</b> · confidenza ${d.confidence}% · ${d.strategies.join(' + ')}</div><p>${d.answer}</p>`+
   (d.hits||[]).map(h=>`<article class="course"><span class="course-cat">${h.title}</span><h3>${h.section||'Knowledge'}</h3><p>${h.content.slice(0,700)}</p><small>${h.source_path}</small></article>`).join('');
 }catch(err){box.textContent='NCKE non raggiungibile: '+err.message}
});
</script>
<?php require '_footer.php';?>