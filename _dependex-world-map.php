<?php
function dependex_world_map_card(string $height='620px'): string { ob_start(); ?>
<section class="dx-map-card" id="dxMapRoot">
  <div class="dx-map-head"><div><span class="eyebrow">DEPENDEX WORLD CLUB EXPLORER</span><h2>La rete mondiale, viva.</h2><p id="dxMapStats">Caricamento rete…</p></div><div class="segmented" id="dxView"><button class="active" data-view="2d">2D</button><button data-view="3d">3D</button></div></div>
  <div class="dx-map-toolbar">
    <input id="dxSearch" placeholder="Cerca Club, città, nazione, SIC-ID…">
    <select id="dxLevel"><option value="">Tutti i rank</option><option value="LOCAL_CLUB">Locale</option><option value="TERRITORIAL">Territoriale</option><option value="PROVINCIAL">Provinciale</option><option value="REGIONAL">Regionale</option><option value="NATIONAL">Nazionale</option><option value="CONTINENT">Continentale</option><option value="WORLD">World</option></select>
    <select id="dxCountry"><option value="">Tutte le nazioni</option></select>
    <select id="dxStatus"><option value="">Tutti gli status</option><option value="ACTIVE">Attivi/verificati</option><option value="HISTORICAL">Storici</option><option value="DORMANT">Dormienti</option></select>
  </div>
  <div class="dx-map-stage" style="height:<?=htmlspecialchars($height)?>"><canvas id="dxMapCanvas"></canvas><div class="dx-map-controls"><button id="dxZoomIn">＋</button><button id="dxZoomOut">−</button><button id="dxReset">⟲</button></div><div class="dx-map-hint">drag · zoom · tap POI</div></div>
  <div class="dx-legend"><span><i style="--c:#22C55E"></i>Locale</span><span><i style="--c:#14B8A6"></i>Territoriale</span><span><i style="--c:#3B82F6"></i>Provinciale</span><span><i style="--c:#8B5CF6"></i>Regionale</span><span><i style="--c:#F59E0B"></i>Nazionale</span><span><i style="--c:#F97316"></i>Continentale</span><span><i style="--c:#EF4444"></i>World</span></div>
  <div id="dxClubCard" class="dx-club-card hidden"></div>
</section>
<script src="assets/js/dependex-world-map.js"></script>
<?php return ob_get_clean(); }
