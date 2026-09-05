<?php require_once 'bootstrap.php';$public=true;$u=current_user();$pageTitle='Global Network';require '_header.php';?>
<section class="section-head"><div><span class="eyebrow">World Hudolin/CAT Registry</span><h1>La rete oltre i confini.</h1><p>Club verificati, reti nazionali e presenze storiche sono distinti per affidabilità.</p></div></section>
<section class="card"><div id="globalStats">Caricamento…</div></section>
<div class="network-tools"><input id="globalSearch" placeholder="Paese, Club, città…"><select id="countryFilter"><option value="">Tutti i Paesi</option></select></div>
<div id="globalList" class="course-list"></div>
<script>
fetch('api.php?action=network&scope=GLOBAL').then(r=>r.json()).then(data=>{
 const countries=[...new Set(data.map(x=>x.country))].sort(),sel=document.querySelector('#countryFilter'),list=document.querySelector('#globalList'),q=document.querySelector('#globalSearch');
 countries.forEach(c=>{let o=document.createElement('option');o.value=c;o.textContent=c;sel.appendChild(o)});
 document.querySelector('#globalStats').innerHTML='<b>'+data.length+'</b> record internazionali · <b>'+data.filter(x=>x.level==='CLUB').length+'</b> Club individuali';
 function render(){let t=(q.value||'').toLowerCase(),c=sel.value;let a=data.filter(x=>(!c||x.country===c)&&(!t||[x.country,x.entity_name,x.comune,x.region].join(' ').toLowerCase().includes(t))).slice(0,250);list.innerHTML=a.map(x=>`<article class="course"><span class="course-cat">${x.country} · ${x.verification_status}</span><h3>${x.entity_name}</h3><p>${[x.comune,x.region,x.address].filter(Boolean).join(' · ')}</p><small>${x.meeting||''}</small></article>`).join('')}
 q.oninput=render;sel.onchange=render;render();
});
</script><?php require '_footer.php';?>