<?php
/**
 * DEPENDEX.SOCIAL — ORGANIGRAMMA A PIRAMIDE ROVESCIATA
 * Rete Ecologico-Sociale dei Club Alcologici Territoriali (Metodo Hudolin)
 * Visualizzazione Interattiva ad Albero D3.js:
 * Apice in Alto: 1.768 Club Locali & Famiglie
 * Supporto Intermedio: Presidi ACAT Territoriali & Federazioni ARCAT Regionali
 * Base in Basso: AICAT Coordinamento Nazionale
 */

require_once __DIR__ . '/bootstrap.php';

$pageTitle = 'Organigramma a Piramide Rovesciata · Rete Nazionale Club Hudolin';
$metaDesc = 'Esplora la struttura ecologico-sociale della Rete dei Club Hudolin. Visualizzazione interattiva a piramide rovesciata: famiglie e club al vertice, supportati da ACAT, ARCAT e AICAT.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/piramide-rovesciata.php';

$breadcrumbs = [
    'Home' => '/',
    'Rete & Territorio' => 'world-club-explorer.php',
    'Organigramma Piramide Rovesciata' => 'piramide-rovesciata.php'
];

require '_header.php';
?>

<style>
/* ==========================================================================
   ORGANIGRAMMA PIRAMIDE ROVESCIATA — STILI DEDICATI
   ========================================================================== */
:root {
  --tree-bg: #07090f;
  --node-national: #d4af37;
  --node-regional: #3b82f6;
  --node-acat: #00d4ff;
  --node-club: #10b981;
  --node-family: #f59e0b;
}

.tree-container-wrapper {
  position: relative;
  width: 100%;
  height: calc(100vh - 160px);
  min-height: 600px;
  background: radial-gradient(circle at 50% 20%, rgba(20, 30, 50, 0.6) 0%, rgba(7, 9, 15, 0.98) 80%);
  border: 1px solid rgba(212, 175, 55, 0.25);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
}

#treeSvg {
  width: 100%;
  height: 100%;
  cursor: grab;
  user-select: none;
}
#treeSvg:active {
  cursor: grabbing;
}

/* NODI E LINK */
.node-circle {
  cursor: pointer;
  transition: r 0.2s cubic-bezier(0.4, 0, 0.2, 1), stroke-width 0.2s;
}
.node-circle:hover {
  filter: drop-shadow(0 0 8px currentColor);
}
.node-text {
  font-family: var(--font-sans, system-ui, -apple-system, sans-serif);
  font-size: 11px;
  font-weight: 600;
  fill: #e2e8f0;
  pointer-events: none;
  text-shadow: 0 1px 3px rgba(0,0,0,0.9);
}
.node-subtext {
  font-size: 9px;
  fill: #94a3b8;
  pointer-events: none;
}
.node-badge {
  font-size: 9px;
  font-weight: 800;
  fill: #07090f;
  pointer-events: none;
}

.link {
  fill: none;
  stroke: rgba(255, 255, 255, 0.15);
  stroke-width: 1.5px;
  transition: stroke 0.3s, stroke-width 0.3s;
}
.link.active {
  stroke: rgba(0, 212, 255, 0.7);
  stroke-width: 2.5px;
  filter: drop-shadow(0 0 4px rgba(0, 212, 255, 0.5));
}

/* BARRA DI CONTROLLO SUPERIORE */
.tree-toolbar {
  position: absolute;
  top: 14px;
  left: 14px;
  right: 14px;
  z-index: 10;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  align-items: center;
  justify-content: space-between;
  pointer-events: none;
}
.tree-toolbar > * {
  pointer-events: auto;
}

.tree-search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(15, 22, 38, 0.92);
  border: 1px solid rgba(212, 175, 55, 0.35);
  border-radius: 12px;
  padding: 6px 14px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.5);
  backdrop-filter: blur(8px);
  min-width: 260px;
  max-width: 380px;
  flex: 1;
}
.tree-search-box input {
  background: transparent;
  border: none;
  outline: none;
  color: #FFFFFF;
  font-size: 0.88rem;
  width: 100%;
}
.tree-search-box input::placeholder {
  color: #64748b;
}

.tree-btn-group {
  display: flex;
  gap: 6px;
  background: rgba(15, 22, 38, 0.92);
  border: 1px solid rgba(212, 175, 55, 0.35);
  border-radius: 12px;
  padding: 4px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.5);
  backdrop-filter: blur(8px);
}
.tree-btn {
  background: transparent;
  border: none;
  color: #cbd5e1;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
  min-height: 36px;
}
.tree-btn:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #FFFFFF;
}
.tree-btn.active {
  background: linear-gradient(135deg, rgba(0,212,255,0.25), rgba(0,102,204,0.3));
  color: #00d4ff;
  border: 1px solid rgba(0,212,255,0.5);
}

/* SIDEBAR DETTAGLI NODO */
.tree-node-drawer {
  position: absolute;
  top: 0;
  right: -420px;
  width: 380px;
  max-width: 100%;
  height: 100%;
  background: rgba(12, 17, 29, 0.98);
  border-left: 1px solid rgba(212, 175, 55, 0.35);
  box-shadow: -10px 0 40px rgba(0,0,0,0.8);
  backdrop-filter: blur(16px);
  z-index: 20;
  display: flex;
  flex-direction: column;
  transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  overflow-y: auto;
  padding: 24px;
}
.tree-node-drawer.open {
  right: 0;
}
.tree-drawer-close {
  align-self: flex-end;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.15);
  color: #cbd5e1;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  transition: all 0.2s;
}
.tree-drawer-close:hover {
  background: rgba(239,68,68,0.2);
  color: #ef4444;
  border-color: #ef4444;
}

/* LEGENDA IN BASSO */
.tree-legend {
  position: absolute;
  bottom: 12px;
  left: 14px;
  z-index: 10;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  background: rgba(10, 14, 24, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 6px 14px;
  font-size: 0.76rem;
  color: #94a3b8;
  backdrop-filter: blur(8px);
  pointer-events: none;
}
.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 4px;
  vertical-align: middle;
}

@media (max-width: 768px) {
  .tree-container-wrapper {
    height: calc(100dvh - 120px);
    border-radius: 0;
    border-left: none;
    border-right: none;
  }
  .tree-toolbar {
    top: 8px;
    left: 8px;
    right: 8px;
  }
  .tree-node-drawer {
    width: 100%;
    right: -100%;
    padding: 18px;
  }
  .tree-legend {
    display: none;
  }
}
</style>

<div style="max-width: 1440px; margin: 0 auto; padding: 1.5rem 1rem 3rem;">

  <!-- TITOLO & PREMESSA MAIEUTICA METODO HUDOLIN -->
  <div style="text-align: center; margin-bottom: 1.5rem;">
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('layers', '', 14)?>
      <span>STRUTTURA ECOLOGICO-SOCIALE HUDOLIN · 1.768 NODI · 15.588 FAMIGLIE</span>
    </div>
    <h1 style="font-size: clamp(1.6rem, 3.2vw, 2.4rem); font-weight: 850; color: #FFFFFF; margin: 0 0 0.5rem; letter-spacing: -0.02em;">
      Organigramma a Piramide Rovesciata
    </h1>
    <p style="max-width: 820px; margin: 0 auto; color: #cbd5e1; font-size: 0.95rem; line-height: 1.55;">
      Nel Metodo Hudolin la gerarchia tradizionale è <b>capovolta</b>: al vertice più alto siedono le <b>Famiglie e i Club territoriali</b>, protagonisti sovrani del cammino di cambiamento. I presidi territoriali (ACAT), le associazioni regionali (ARCAT) e la segreteria nazionale (AICAT) si collocano in basso come <b>base di supporto e servizio</b>, mai come potere direttivo.
    </p>
  </div>

  <!-- AREA DI VISUALIZZAZIONE D3 -->
  <div class="tree-container-wrapper" id="treeWrapper">

    <!-- TOOLBAR DI CONTROLLO -->
    <div class="tree-toolbar">
      <div class="tree-search-box">
        <?=dx_icon('search', 'text-amber', 16)?>
        <input type="text" id="treeSearchInput" placeholder="Cerca Club, Comune, Provincia o Regione..." aria-label="Cerca nodo nell'organigramma">
        <span id="searchResultsCount" style="font-size:0.75rem;color:#00d4ff;display:none;"></span>
      </div>

      <div class="tree-btn-group">
        <button type="button" class="tree-btn active" id="btnModeInverted" title="Vista Piramide Rovesciata (Famiglie in alto, AICAT di supporto in basso)">
          <?=dx_icon('arrow-down', '', 14)?> Piramide Rovesciata
        </button>
        <button type="button" class="tree-btn" id="btnModeTree" title="Vista Albero Orizzontale Dinamico">
          <?=dx_icon('git-branch', '', 14)?> Vista Rete
        </button>
      </div>

      <div class="tree-btn-group">
        <button type="button" class="tree-btn" id="btnZoomIn" title="Ingrandisci">+</button>
        <button type="button" class="tree-btn" id="btnZoomOut" title="Riduci">-</button>
        <button type="button" class="tree-btn" id="btnResetView" title="Centra e adatta alla finestra"><?=dx_icon('maximize-2', '', 14)?> Adatta</button>
        <button type="button" class="tree-btn" id="btnExpandAll" title="Espandi rami">Espandi</button>
        <button type="button" class="tree-btn" id="btnCollapseAll" title="Comprimi rami">Comprimi</button>
      </div>
    </div>

    <!-- SVG CANVAS -->
    <svg id="treeSvg">
      <g id="treeRootGroup"></g>
    </svg>

    <!-- LEGENDA DEI LIVELLI -->
    <div class="tree-legend">
      <span><span class="legend-dot" style="background:var(--node-family);"></span> Famiglie nel Cerchio</span>
      <span><span class="legend-dot" style="background:var(--node-club);"></span> Club Locali (CAT)</span>
      <span><span class="legend-dot" style="background:var(--node-acat);"></span> ACAT Territoriali</span>
      <span><span class="legend-dot" style="background:var(--node-regional);"></span> ARCAT Regionali</span>
      <span><span class="legend-dot" style="background:var(--node-national);"></span> AICAT Nazionale (Base di Supporto)</span>
    </div>

    <!-- SIDEBAR INFORMATIVA DEL NODO CLICCATO -->
    <aside class="tree-node-drawer" id="nodeDrawer" aria-label="Dettagli del presidio">
      <button type="button" class="tree-drawer-close" id="drawerClose" aria-label="Chiudi dettagli">&times;</button>
      
      <div id="drawerContent" style="margin-top:12px;">
        <!-- Popolato dinamicamente da JS -->
      </div>
    </aside>

  </div>

  <!-- NOTE METODOLOGICHE & RIFERIMENTO ISTITUZIONALE -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-top: 1.8rem;">
    <div class="card p-3 p-md-4" style="background:rgba(18,24,38,0.85);border:1px solid rgba(212,175,55,0.25);border-radius:14px;">
      <h3 style="color:#00ff77;font-size:1rem;margin:0 0 8px;display:flex;align-items:center;gap:8px;">
        <?=dx_icon('users', '', 16)?> Il Primato delle Famiglie
      </h3>
      <p style="font-size:0.86rem;color:#cbd5e1;line-height:1.5;margin:0;">
        Nel Club non ci sono esperti né pazienti. Ogni famiglia accoglie e viene accolta nel cerchio paritario. Le decisioni formative e programmatiche nascono dall'esperienza reale dei Club e risalgono verso il coordinamento.
      </p>
    </div>

    <div class="card p-3 p-md-4" style="background:rgba(18,24,38,0.85);border:1px solid rgba(212,175,55,0.25);border-radius:14px;">
      <h3 style="color:#00d4ff;font-size:1rem;margin:0 0 8px;display:flex;align-items:center;gap:8px;">
        <?=dx_icon('shield', '', 16)?> Sussidiarietà Territoriale
      </h3>
      <p style="font-size:0.86rem;color:#cbd5e1;line-height:1.5;margin:0;">
        Le ACAT provinciali e le ARCAT regionali supportano la logistica, i corsi di sensibilizzazione di 50 ore e i contatti con i Ser.D / ASL e Comuni ai sensi della Legge quadro 125/2001.
      </p>
    </div>

    <div class="card p-3 p-md-4" style="background:rgba(18,24,38,0.85);border:1px solid rgba(212,175,55,0.25);border-radius:14px;">
      <h3 style="color:#d4af37;font-size:1rem;margin:0 0 8px;display:flex;align-items:center;gap:8px;">
        <?=dx_icon('award', '', 16)?> Unico Presidio Nazionale AICAT
      </h3>
      <p style="font-size:0.86rem;color:#cbd5e1;line-height:1.5;margin:0;">
        L'Associazione Italiana dei Club Alcologici Territoriali funge da garanzia scientifica e custode dell'eredità del Prof. Vladimir Hudolin, gestendo il Numero Verde Nazionale <b>800 974250</b>.
      </p>
    </div>
  </div>

</div>

<!-- CARICAMENTO D3.JS v7 -->
<script src="https://unpkg.com/d3@7/dist/d3.min.js"></script>

<script>
(function(){
  'use strict';

  let rawTreeData = null;
  let rootNode = null;
  let currentLayoutMode = 'inverted'; // 'inverted' | 'horizontal'
  let svg, gRoot, treeLayout, zoomBehavior;
  let width, height;

  const wrapper = document.getElementById('treeWrapper');
  const svgEl = document.getElementById('treeSvg');
  const drawer = document.getElementById('nodeDrawer');
  const drawerContent = document.getElementById('drawerContent');
  const drawerClose = document.getElementById('drawerClose');
  const searchInput = document.getElementById('treeSearchInput');

  // Inizializzazione dimensioni
  function updateDimensions() {
    width = wrapper.clientWidth;
    height = wrapper.clientHeight;
    svgEl.setAttribute('viewBox', `0 0 ${width} ${height}`);
  }

  // Setup D3 Zoom & Pan
  function setupZoom() {
    svg = d3.select('#treeSvg');
    gRoot = d3.select('#treeRootGroup');

    zoomBehavior = d3.zoom()
      .scaleExtent([0.15, 3.5])
      .on('zoom', (event) => {
        gRoot.attr('transform', event.transform);
      });

    svg.call(zoomBehavior);
  }

  // Caricamento Dati Gerarchici da API
  async function loadTreeData() {
    try {
      const resp = await fetch('/api-organigramma.php');
      const data = await resp.json();
      if (!data.ok || !data.tree) {
        throw new Error(data.error || 'Errore di caricamento dati.');
      }
      rawTreeData = data.tree;
      initHierarchy();
    } catch (err) {
      console.error('Errore API Organigramma:', err);
      wrapper.innerHTML = `
        <div style="padding:40px;text-align:center;color:#ef4444;">
          <h3>Impossibile caricare l'organigramma</h3>
          <p style="color:#94a3b8;font-size:0.9rem;">${err.message}</p>
          <button onclick="location.reload()" class="btn primary small mt-3">Riprova</button>
        </div>
      `;
    }
  }

  // Inizializzazione della gerarchia con nodi collassati di default sotto il livello regionale
  function initHierarchy() {
    rootNode = d3.hierarchy(rawTreeData);

    // Identificatori univoci per ogni nodo
    let idCounter = 0;
    rootNode.each(d => {
      d.id = ++idCounter;
      // Inizialmente espandiamo AICAT e le ARCAT, ma teniamo collassati i Club locali per fluidità visiva
      if (d.depth >= 2) {
        d._children = d.children;
        d.children = null;
      }
    });

    renderTree();
    resetView();
  }

  // Colori per livello
  function getNodeColor(type) {
    switch(type) {
      case 'FAMILY': return '#f59e0b';
      case 'LOCAL_CLUB': return '#10b981';
      case 'TERRITORIAL_ACAT': return '#00d4ff';
      case 'REGIONAL': return '#3b82f6';
      case 'NATIONAL': return '#d4af37';
      default: return '#94a3b8';
    }
  }

  function getNodeRadius(type) {
    switch(type) {
      case 'NATIONAL': return 16;
      case 'REGIONAL': return 12;
      case 'TERRITORIAL_ACAT': return 9;
      case 'LOCAL_CLUB': return 7;
      case 'FAMILY': return 4;
      default: return 6;
    }
  }

  // Rendering dell'Albero
  function renderTree(sourceNode) {
    updateDimensions();

    const duration = 350;

    if (currentLayoutMode === 'inverted') {
      // PIRAMIDE ROVESCIATA:
      // La radice (AICAT) è posizionata in basso (Y elevata), mentre le foglie (Club e Famiglie) salgono verso l'alto (Y bassa)
      treeLayout = d3.tree()
        .nodeSize([60, 120])
        .separation((a, b) => (a.parent === b.parent ? 1.2 : 1.8));

      treeLayout(rootNode);

      // Invertiamo l'asse Y per ottenere la piramide rovesciata (Base in basso, Apice in alto)
      rootNode.each(d => {
        d.targetY = -d.depth * 130; // Sale verso l'alto
        d.targetX = d.x;
      });
    } else {
      // VISTA RETE ORIZZONTALE
      treeLayout = d3.tree()
        .nodeSize([40, 220])
        .separation((a, b) => (a.parent === b.parent ? 1.1 : 1.6));

      treeLayout(rootNode);

      rootNode.each(d => {
        d.targetX = d.y;
        d.targetY = d.x;
      });
    }

    const nodes = rootNode.descendants();
    const links = rootNode.links();

    // ----------------------------------------------------
    // LINKS
    // ----------------------------------------------------
    const linkSelection = gRoot.selectAll('path.link')
      .data(links, d => d.target.id);

    const linkEnter = linkSelection.enter().append('path')
      .attr('class', 'link')
      .attr('d', d => {
        const o = { x: sourceNode ? sourceNode.x0 : 0, y: sourceNode ? sourceNode.y0 : 0 };
        return diagonal(o, o);
      });

    const linkUpdate = linkEnter.merge(linkSelection);

    linkUpdate.transition().duration(duration)
      .attr('d', d => diagonal(
        { x: d.source.targetX, y: d.source.targetY },
        { x: d.target.targetX, y: d.target.targetY }
      ));

    linkSelection.exit().transition().duration(duration)
      .attr('d', d => {
        const o = { x: sourceNode ? sourceNode.targetX : 0, y: sourceNode ? sourceNode.targetY : 0 };
        return diagonal(o, o);
      })
      .remove();

    // ----------------------------------------------------
    // NODES
    // ----------------------------------------------------
    const nodeSelection = gRoot.selectAll('g.tree-node')
      .data(nodes, d => d.id);

    const nodeEnter = nodeSelection.enter().append('g')
      .attr('class', 'tree-node')
      .attr('transform', d => `translate(${sourceNode ? sourceNode.x0 : 0},${sourceNode ? sourceNode.y0 : 0})`)
      .on('click', (event, d) => {
        event.stopPropagation();
        handleNodeClick(d);
      });

    // Cerchio principale
    nodeEnter.append('circle')
      .attr('class', 'node-circle')
      .attr('r', 1e-6)
      .attr('fill', d => d._children ? getNodeColor(d.data.type) : '#0a0e1a')
      .attr('stroke', d => getNodeColor(d.data.type))
      .attr('stroke-width', 2.5);

    // Badge contatore figli collassati (+N)
    nodeEnter.append('text')
      .attr('class', 'node-badge')
      .attr('text-anchor', 'middle')
      .attr('dy', '0.35em')
      .style('opacity', 0);

    // Etichetta del nodo
    nodeEnter.append('text')
      .attr('class', 'node-text')
      .attr('dy', d => currentLayoutMode === 'inverted' ? (d.data.type === 'NATIONAL' ? 30 : -14) : 4)
      .attr('x', d => currentLayoutMode === 'inverted' ? 0 : (d.children || d._children ? -16 : 16))
      .attr('text-anchor', d => currentLayoutMode === 'inverted' ? 'middle' : (d.children || d._children ? 'end' : 'start'))
      .text(d => truncateName(d.data.name, 28));

    // UPDATE
    const nodeUpdate = nodeEnter.merge(nodeSelection);

    nodeUpdate.transition().duration(duration)
      .attr('transform', d => `translate(${d.targetX},${d.targetY})`);

    nodeUpdate.select('circle.node-circle')
      .transition().duration(duration)
      .attr('r', d => getNodeRadius(d.data.type))
      .attr('fill', d => d._children ? getNodeColor(d.data.type) : '#0a0e1a')
      .attr('stroke', d => getNodeColor(d.data.type));

    nodeUpdate.select('text.node-badge')
      .transition().duration(duration)
      .text(d => d._children ? `+${d._children.length}` : '')
      .style('opacity', d => d._children ? 1 : 0);

    nodeUpdate.select('text.node-text')
      .transition().duration(duration)
      .attr('dy', d => currentLayoutMode === 'inverted' ? (d.data.type === 'NATIONAL' ? 30 : -14) : 4)
      .attr('x', d => currentLayoutMode === 'inverted' ? 0 : (d.children || d._children ? -16 : 16))
      .attr('text-anchor', d => currentLayoutMode === 'inverted' ? 'middle' : (d.children || d._children ? 'end' : 'start'));

    // EXIT
    const nodeExit = nodeSelection.exit().transition().duration(duration)
      .attr('transform', d => `translate(${sourceNode ? sourceNode.targetX : 0},${sourceNode ? sourceNode.targetY : 0})`)
      .remove();

    nodeExit.select('circle').attr('r', 1e-6);

    // Memorizza coordinate precedenti
    nodes.forEach(d => {
      d.x0 = d.targetX;
      d.y0 = d.targetY;
    });
  }

  // Curve per i link
  function diagonal(s, d) {
    if (currentLayoutMode === 'inverted') {
      return `M ${s.x} ${s.y}
              C ${s.x} ${(s.y + d.y) / 2},
                ${d.x} ${(s.y + d.y) / 2},
                ${d.x} ${d.y}`;
    } else {
      return `M ${s.x} ${s.y}
              C ${(s.x + d.x) / 2} ${s.y},
                ${(s.x + d.x) / 2} ${d.y},
                ${d.x} ${d.y}`;
    }
  }

  function truncateName(str, maxLen) {
    if (!str) return '';
    return str.length > maxLen ? str.slice(0, maxLen) + '…' : str;
  }

  // Gestione Click Nodo: espandi/comprimi e apri sidebar dettagli
  function handleNodeClick(d) {
    // Toggle espansione rami
    if (d.children) {
      d._children = d.children;
      d.children = null;
    } else if (d._children) {
      d.children = d._children;
      d._children = null;
    }

    renderTree(d);
    showNodeDetails(d);
  }

  // Mostra dettagli nella Sidebar Destra
  function showNodeDetails(d) {
    const item = d.data;
    const color = getNodeColor(item.type);

    let html = `
      <div style="border-left: 4px solid ${color}; padding-left: 12px; margin-bottom: 16px;">
        <span style="font-size:0.75rem;font-weight:800;text-transform:uppercase;color:${color};letter-spacing:0.05em;">
          ${item.level_label || item.type}
        </span>
        <h2 style="font-size:1.25rem;color:#FFFFFF;margin:4px 0 6px;line-height:1.3;font-weight:800;">
          ${item.name}
        </h2>
        ${item.sic_id ? `
          <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.06);padding:2px 8px;border-radius:6px;font-family:monospace;font-size:0.75rem;color:#94a3b8;">
            <span>${item.sic_id}</span>
          </div>
        ` : ''}
      </div>
    `;

    // Metriche / Contatori associati
    if (item.total_clubs || item.total_families || item.families_count) {
      html += `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px;">
          ${item.total_clubs ? `
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px;text-align:center;">
              <b style="font-size:1.3rem;color:#00ff77;display:block;">${item.total_clubs}</b>
              <span style="font-size:0.75rem;color:#cbd5e1;">Club Coordinati</span>
            </div>
          ` : ''}
          ${item.total_families || item.families_count ? `
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px;text-align:center;">
              <b style="font-size:1.3rem;color:#ffd700;display:block;">${item.total_families || item.families_count}</b>
              <span style="font-size:0.75rem;color:#cbd5e1;">Famiglie nel Cerchio</span>
            </div>
          ` : ''}
        </div>
      `;
    }

    // Informazioni logistiche & recapiti
    html += `<div style="display:flex;flex-direction:column;gap:12px;font-size:0.86rem;color:#cbd5e1;line-height:1.45;margin-bottom:20px;">`;

    if (item.address || item.city) {
      html += `<div><b>📍 Sede / Territorio:</b><br>${item.address ? item.address + ' · ' : ''}${item.city || ''} (${item.province || item.region || 'IT'})</div>`;
    }

    if (item.meeting_day || item.meeting_time) {
      html += `<div><b>🕒 Incontro di Comunità:</b><br>${item.meeting_day || 'Settimanale'} ${item.meeting_time ? 'ore ' + item.meeting_time : ''} ${item.meeting_venue ? 'presso ' + item.meeting_venue : ''}</div>`;
    }

    if (item.servitore_insegnante) {
      html += `<div><b>🤝 Servitore Insegnante:</b><br>${item.servitore_insegnante}</div>`;
    }

    if (item.phone) {
      html += `<div><b>☎ Telefono di Riferimento:</b><br><a href="tel:${item.phone.replace(/[^0-9+]/g,'')}" style="color:#00ff77;font-weight:700;text-decoration:none;">${item.phone}</a></div>`;
    }

    if (item.email) {
      html += `<div><b>✉ Email Ufficiale:</b><br><a href="mailto:${item.email}" style="color:#00d4ff;text-decoration:none;">${item.email}</a></div>`;
    }

    if (item.website) {
      html += `<div><b>🌐 Portale Web:</b><br><a href="${item.website}" target="_blank" rel="noopener" style="color:#ffd700;text-decoration:underline;">${item.website}</a></div>`;
    }

    if (item.notes) {
      html += `<div style="background:rgba(255,255,255,0.03);padding:10px;border-radius:8px;font-size:0.82rem;color:#94a3b8;border:1px dashed rgba(255,255,255,0.1);"><b>Note:</b> ${item.notes}</div>`;
    }

    html += `</div>`;

    // Azioni rapide
    html += `
      <div style="display:flex;flex-direction:column;gap:8px;margin-top:auto;">
        <a href="mappa-club.php?q=${encodeURIComponent(item.name)}" class="btn primary small" style="text-align:center;justify-content:center;">
          Visualizza su Mappa 2D Italia ↗
        </a>
        <a href="parla-con-noi.php" class="btn small" style="text-align:center;justify-content:center;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.2);color:#cbd5e1;">
          Richiedi Contatto con la Comunità
        </a>
      </div>
    `;

    drawerContent.innerHTML = html;
    drawer.classList.add('open');
  }

  // Chiusura Drawer
  drawerClose.addEventListener('click', () => {
    drawer.classList.remove('open');
  });

  // Centratura e adattamento vista (Fit Screen)
  function resetView() {
    if (!rootNode) return;
    updateDimensions();

    if (currentLayoutMode === 'inverted') {
      // In piramide rovesciata posizioniamo AICAT in basso al centro
      const initialTransform = d3.zoomIdentity
        .translate(width / 2, height - 90)
        .scale(0.85);
      svg.transition().duration(500).call(zoomBehavior.transform, initialTransform);
    } else {
      const initialTransform = d3.zoomIdentity
        .translate(80, height / 2)
        .scale(0.75);
      svg.transition().duration(500).call(zoomBehavior.transform, initialTransform);
    }
  }

  // Controlli Zoom
  document.getElementById('btnZoomIn').addEventListener('click', () => {
    svg.transition().duration(250).call(zoomBehavior.scaleBy, 1.3);
  });
  document.getElementById('btnZoomOut').addEventListener('click', () => {
    svg.transition().duration(250).call(zoomBehavior.scaleBy, 0.75);
  });
  document.getElementById('btnResetView').addEventListener('click', resetView);

  // Switch modalità Layout
  const btnInv = document.getElementById('btnModeInverted');
  const btnTree = document.getElementById('btnModeTree');

  btnInv.addEventListener('click', () => {
    if (currentLayoutMode === 'inverted') return;
    currentLayoutMode = 'inverted';
    btnInv.classList.add('active');
    btnTree.classList.remove('active');
    renderTree(rootNode);
    resetView();
  });

  btnTree.addEventListener('click', () => {
    if (currentLayoutMode === 'horizontal') return;
    currentLayoutMode = 'horizontal';
    btnTree.classList.add('active');
    btnInv.classList.remove('active');
    renderTree(rootNode);
    resetView();
  });

  // Espandi tutto / Comprimi tutto
  document.getElementById('btnExpandAll').addEventListener('click', () => {
    rootNode.each(d => {
      if (d._children) {
        d.children = d._children;
        d._children = null;
      }
    });
    renderTree(rootNode);
    resetView();
  });

  document.getElementById('btnCollapseAll').addEventListener('click', () => {
    rootNode.each(d => {
      if (d.depth >= 2 && d.children) {
        d._children = d.children;
        d.children = null;
      }
    });
    renderTree(rootNode);
    resetView();
  });

  // Ricerca Nodi & Auto-Focus
  searchInput.addEventListener('input', (e) => {
    const q = e.target.value.trim().toLowerCase();
    const countBadge = document.getElementById('searchResultsCount');

    if (!q) {
      countBadge.style.display = 'none';
      gRoot.selectAll('circle.node-circle').attr('stroke-width', 2.5);
      return;
    }

    let matchCount = 0;
    let firstMatch = null;

    // Espandiamo i nodi padri di quelli corrispondenti per renderli visibili
    rootNode.each(d => {
      const name = (d.data.name || '').toLowerCase();
      const city = (d.data.city || '').toLowerCase();
      const reg = (d.data.region || '').toLowerCase();
      const prov = (d.data.province || '').toLowerCase();

      if (name.includes(q) || city.includes(q) || reg.includes(q) || prov.includes(q)) {
        matchCount++;
        if (!firstMatch) firstMatch = d;

        // Apri tutti i genitori
        let p = d.parent;
        while (p) {
          if (p._children) {
            p.children = p._children;
            p._children = null;
          }
          p = p.parent;
        }
      }
    });

    countBadge.textContent = `${matchCount} trovati`;
    countBadge.style.display = 'inline';

    renderTree(rootNode);

    // Evidenziazione visiva e focus sul primo match
    if (firstMatch) {
      gRoot.selectAll('circle.node-circle')
        .attr('stroke-width', d => {
          const match = (d.data.name || '').toLowerCase().includes(q) || (d.data.city || '').toLowerCase().includes(q);
          return match ? 6 : 1.5;
        });

      // Zoom morbido sul primo match
      const transform = d3.zoomIdentity
        .translate(width / 2 - firstMatch.targetX * 1.2, height / 2 - firstMatch.targetY * 1.2)
        .scale(1.2);
      svg.transition().duration(600).call(zoomBehavior.transform, transform);
      showNodeDetails(firstMatch);
    }
  });

  // Click fuori per chiudere drawer
  svgEl.addEventListener('click', () => {
    drawer.classList.remove('open');
  });

  // Inizializzazione all'avvio
  window.addEventListener('resize', () => {
    updateDimensions();
  });

  setupZoom();
  loadTreeData();

})();
</script>

<?php require '_footer.php'; ?>
