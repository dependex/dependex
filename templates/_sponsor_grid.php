<?php
/**
 * DEPENDEX & ECOSYSTEM — 28 MAIN SPONSOR & ASSET SOVRANI GRID
 * Design Mobile-First 9:16 Responsive Luxury Dark / Gold / Neon
 * Con 28 Immagini Grafiche Ultra-HD / 8K Vector per ogni singolo Brand.
 * Conforme al protocollo di governance: zero riferimenti a termini proibiti.
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$sponsors = [
    [
        'name' => 'sicurissimo.online',
        'slug' => 'sicurissimo-online',
        'category' => 'Sicurezza & Compliance',
        'desc' => 'Portate normative, D.Lgs 81/08, HACCP e sicurezza sul lavoro.',
        'icon' => 'shield-check',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/sicurissimo-online.svg',
        'url' => 'https://sicurissimo.online'
    ],
    [
        'name' => 'betterway.agency',
        'slug' => 'betterway-agency',
        'category' => 'Growth & Marketing',
        'desc' => 'Architettura di crescita, marketing operativo e automazione vendite.',
        'icon' => 'activity',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/betterway-agency.svg',
        'url' => 'https://betterway.agency'
    ],
    [
        'name' => 'neuralog.pro',
        'slug' => 'neuralog-pro',
        'category' => 'AI & Telemetry',
        'desc' => 'Osservabilità di agenti intelligenti, tracciamento sinapsi e RAG.',
        'icon' => 'cpu',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/neuralog-pro.svg',
        'url' => 'https://neuralog.pro'
    ],
    [
        'name' => 'mywallet.business',
        'slug' => 'mywallet-business',
        'category' => 'Fintech & Multi-Currency',
        'desc' => 'Infrastruttura finanziaria avanzata e gestione flussi multi-asset.',
        'icon' => 'credit-card',
        'color' => '#f59e0b',
        'img' => 'assets/img/sponsors/mywallet-business.svg',
        'url' => 'https://mywallet.business'
    ],
    [
        'name' => 'destinorandagio.it',
        'slug' => 'destinorandagio-it',
        'category' => 'Nomad & Lifestyle',
        'desc' => 'Community per professionisti liberi, viaggiatori e vita sovrana.',
        'icon' => 'compass',
        'color' => '#ec4899',
        'img' => 'assets/img/sponsors/destinorandagio-it.svg',
        'url' => 'https://destinorandagio.it'
    ],
    [
        'name' => 'beway.life',
        'slug' => 'beway-life',
        'category' => 'Longevity & Benessere',
        'desc' => 'Protocolli di longevità consapevole, vitalità e stile di vita attivo.',
        'icon' => 'heart',
        'color' => '#06b6d4',
        'img' => 'assets/img/sponsors/beway-life.svg',
        'url' => 'https://beway.life'
    ],
    [
        'name' => 'estao.app',
        'slug' => 'estao-app',
        'category' => 'PropTech Immobiliare',
        'desc' => 'Piattaforma SaaS per agenzie, gestione immobili e clienti evoluta.',
        'icon' => 'home',
        'color' => '#14b8a6',
        'img' => 'assets/img/sponsors/estao-app.svg',
        'url' => 'https://estao.app'
    ],
    [
        'name' => 'ixla.solutions',
        'slug' => 'ixla-solutions',
        'category' => 'Engineering & CAD',
        'desc' => 'Consulenza tecnica, progettazione meccanica e rendering 3D.',
        'icon' => 'layers',
        'color' => '#6366f1',
        'img' => 'assets/img/sponsors/ixla-solutions.svg',
        'url' => 'https://ixla.solutions'
    ],
    [
        'name' => 'cryptoaid.support',
        'slug' => 'cryptoaid-support',
        'category' => 'Charity & Impact',
        'desc' => 'Filantropia trasparente su blockchain e supporto a progetti solidali.',
        'icon' => 'gift',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/cryptoaid-support.svg',
        'url' => 'https://cryptoaid.support'
    ],
    [
        'name' => 'metroeridania.it',
        'slug' => 'metroeridania-it',
        'category' => 'Territorio & Cartografia',
        'desc' => 'Mappatura del Polesine, valorizzazione fluviale e itinerari del Delta.',
        'icon' => 'map-pin',
        'color' => '#d4af37',
        'img' => 'assets/img/sponsors/metroeridania-it.svg',
        'url' => 'https://metroeridania.it'
    ],
    [
        'name' => 'mircopregnolato.it',
        'slug' => 'mircopregnolato-it',
        'category' => 'Founder & Venture Hub',
        'desc' => 'Visione sistemica, architettura d’impresa e venture governance.',
        'icon' => 'award',
        'color' => '#f59e0b',
        'img' => 'assets/img/sponsors/mircopregnolato-it.svg',
        'url' => 'https://mircopregnolato.it'
    ],
    [
        'name' => 'universalbusiness.xyz',
        'slug' => 'universalbusiness-xyz',
        'category' => 'Enterprise Directory',
        'desc' => 'Registro globale degli asset digitali e catalogazione imprese.',
        'icon' => 'globe',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/universalbusiness-xyz.svg',
        'url' => 'https://universalbusiness.xyz'
    ],
    [
        'name' => 'Amazon KDP Factory',
        'slug' => 'amazon-kdp-factory',
        'category' => 'Editoria Sovrana',
        'desc' => 'Catena di montaggio editoriale per self-publishing e testi cartacei.',
        'icon' => 'book-open',
        'color' => '#eab308',
        'img' => 'assets/img/sponsors/amazon-kdp-factory.svg',
        'url' => 'https://amazon.it'
    ],
    [
        'name' => 'YouTube Automation',
        'slug' => 'youtube-automation',
        'category' => 'Media & Content Engine',
        'desc' => 'Automazione video ad alta retention, podcasting e canali tematici.',
        'icon' => 'video',
        'color' => '#ef4444',
        'img' => 'assets/img/sponsors/youtube-automation.svg',
        'url' => 'https://youtube.com'
    ],
    [
        'name' => 'campus.camp',
        'slug' => 'campus-camp',
        'category' => 'Formazione Immersiva',
        'desc' => 'Esperienze formative sul campo, laboratori pratici e networking.',
        'icon' => 'check-circle',
        'color' => '#22c55e',
        'img' => 'assets/img/sponsors/campus-camp.svg',
        'url' => 'https://campus.camp'
    ],
    [
        'name' => 'regreen.social',
        'slug' => 'regreen-social',
        'category' => 'ESG & Sostenibilità',
        'desc' => 'Progetti di riforestazione, tutela ambientale e crediti verdi.',
        'icon' => 'sun',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/regreen-social.svg',
        'url' => 'https://regreen.social'
    ],
    [
        'name' => 'blockchainplus.pro',
        'slug' => 'blockchainplus-pro',
        'category' => 'Smart Contracts & Web3',
        'desc' => 'Notarizzazione decentralizzata, ledger verificabili e contratti sicuri.',
        'icon' => 'link',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/blockchainplus-pro.svg',
        'url' => 'https://blockchainplus.pro'
    ],
    [
        'name' => 'Antigravity Mobile IDE',
        'slug' => 'antigravity-mobile-ide',
        'category' => 'Developer Tooling',
        'desc' => 'Ambiente di sviluppo nativo smartphone, orchestrazione e workflow.',
        'icon' => 'terminal',
        'color' => '#06b6d4',
        'img' => 'assets/img/sponsors/antigravity-mobile-ide.svg',
        'url' => 'https://github.com'
    ],
    [
        'name' => 'Email Marketing Machine',
        'slug' => 'email-marketing-machine',
        'category' => 'B2B Automation',
        'desc' => 'Infrastruttura deliverability, segmentazione e nurturing relazionale.',
        'icon' => 'mail',
        'color' => '#f97316',
        'img' => 'assets/img/sponsors/email-marketing-machine.svg',
        'url' => '#'
    ],
    [
        'name' => 'Master Data CRM Pipeline',
        'slug' => 'master-data-crm-pipeline',
        'category' => 'Intelligence & Data',
        'desc' => 'Pulizia, deduplica e arricchimento lead per il mercato professionale.',
        'icon' => 'database',
        'color' => '#a855f7',
        'img' => 'assets/img/sponsors/master-data-crm-pipeline.svg',
        'url' => '#'
    ],
    [
        'name' => 'Commerce Core Engine',
        'slug' => 'commerce-core-engine',
        'category' => 'E-Commerce Headless',
        'desc' => 'Gateway multi-tenant unificato con checkout server-authoritative.',
        'icon' => 'shopping-cart',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/commerce-core-engine.svg',
        'url' => '#'
    ],
    [
        'name' => 'dependex.social',
        'slug' => 'dependex-social',
        'category' => 'Social Care & Welfare',
        'desc' => 'Piattaforma di supporto per famiglie e persone che affrontano dipendenze.',
        'icon' => 'users',
        'color' => '#d4af37',
        'img' => 'assets/img/sponsors/dependex-social.svg',
        'url' => 'https://dependex.social'
    ],
    [
        'name' => 'oltre.social',
        'slug' => 'oltre-social',
        'category' => 'Social Hub Solidale',
        'desc' => 'Spazio di condivisione autentica senza algoritmi predatori.',
        'icon' => 'share-2',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/oltre-social.svg',
        'url' => 'https://oltre.social'
    ],
    [
        'name' => 'Sovereign Academy',
        'slug' => 'sovereign-academy',
        'category' => 'Scuola & Formazione',
        'desc' => 'Percorsi strutturati su comunicazione efficace e Metodo Hudolin.',
        'icon' => 'book',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/sovereign-academy.svg',
        'url' => 'academy-public.php'
    ],
    [
        'name' => 'Sovereign Club',
        'slug' => 'sovereign-club',
        'category' => 'Membership Territoriale',
        'desc' => 'Incontri settimanali di auto-mutuo aiuto e crescita comunitaria.',
        'icon' => 'star',
        'color' => '#d4af37',
        'img' => 'assets/img/sponsors/sovereign-club.svg',
        'url' => 'map.php'
    ],
    [
        'name' => 'Sovereign Merch & Wear',
        'slug' => 'sovereign-merch-wear',
        'category' => 'Kit Ufficiali & Shop',
        'desc' => 'Abbigliamento etico, materiali divulgativi e kit per i soci.',
        'icon' => 'package',
        'color' => '#ec4899',
        'img' => 'assets/img/sponsors/sovereign-merch-wear.svg',
        'url' => '#'
    ],
    [
        'name' => 'Sovereign Network',
        'slug' => 'sovereign-network',
        'category' => 'Rete Relazionale',
        'desc' => 'Connessioni tra Club, volontari, operatori sanitari e famiglie.',
        'icon' => 'git-branch',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/sovereign-network.svg',
        'url' => 'world-network-tree.php'
    ],
    [
        'name' => 'Sovereign Presidi Territoriali',
        'slug' => 'sovereign-presidi-territoriali',
        'category' => 'Punti di Presidio',
        'desc' => 'Sportelli fisici di primo ascolto e orientamento sul territorio.',
        'icon' => 'navigation',
        'color' => '#14b8a6',
        'img' => 'assets/img/sponsors/sovereign-presidi-territoriali.svg',
        'url' => 'map.php'
    ]
];
?>

<section class="m-card" style="border-color: rgba(212,175,55,0.45); background: rgba(13, 16, 26, 0.96); margin-top: 16px;">
  
  <div style="text-align: center; margin-bottom: 16px;">
    <span class="m-badge m-badge-gold" style="font-size: 0.72rem; letter-spacing: 0.08em;">
      <?=dx_icon('award', '', 12)?> MAIN SPONSOR & PATROCINI DELL'ECOSISTEMA
    </span>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.2rem, 4.2vw, 1.55rem); color: #ffffff; margin: 8px 0 4px; font-weight: 900;">
      I 28 Business & Asset Sovrani a Sostegno dell'Evento
    </h2>
    <p style="font-size: 0.82rem; color: #94a3b8; margin: 0; line-height: 1.45;">
      Ogni card include la grafica ufficiale Ultra-HD 8K con logo, badge di conformità e colori identificativi dell'ecosistema:
    </p>
  </div>

  <!-- GRIGLIA SPONSOR RESPONSIVE SMARTPHONE (2 COLONNE / 1 COLONNA ADATTIVA) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px;">
    <?php foreach ($sponsors as $idx => $sp): ?>
      <div style="background: rgba(20, 25, 38, 0.9); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s ease, border-color 0.2s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.35);">
        
        <!-- IMMAGINE GRAFICA BRAND ULTRA-HD 8K -->
        <div style="position: relative; width: 100%; aspect-ratio: 16/9; background: #030712; border-bottom: 1px solid rgba(255,255,255,0.08); cursor: pointer;" onclick="openSponsorModal('<?=htmlspecialchars($sp['img'], ENT_QUOTES, 'UTF-8')?>', '<?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>')">
          <img src="<?=htmlspecialchars($sp['img'], ENT_QUOTES, 'UTF-8')?>" 
               alt="<?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?> - Sponsor Ufficiale" 
               loading="lazy"
               style="width: 100%; height: 100%; object-fit: cover; display: block;">
          <div style="position: absolute; top: 6px; left: 6px; font-size: 0.6rem; font-weight: 900; color: #fef08a; background: rgba(3,7,18,0.8); border: 1px solid <?=$sp['color']?>; padding: 1px 5px; border-radius: 4px;">
            #<?=str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT)?>
          </div>
          <div style="position: absolute; bottom: 4px; right: 6px; font-size: 0.55rem; font-weight: 800; color: #cbd5e1; background: rgba(0,0,0,0.65); padding: 1px 4px; border-radius: 3px;">
            🔍 8K ZOOM
          </div>
        </div>

        <!-- INFO SPONSOR -->
        <div style="padding: 9px 10px 10px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between;">
          <div>
            <div style="font-weight: 850; font-size: 0.82rem; color: #ffffff; line-height: 1.25; margin-bottom: 2px; word-break: break-word;">
              <?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>
            </div>

            <div style="font-size: 0.68rem; font-weight: 750; color: <?=$sp['color']?>; margin-bottom: 4px;">
              <?=htmlspecialchars($sp['category'], ENT_QUOTES, 'UTF-8')?>
            </div>

            <p style="font-size: 0.72rem; color: #94a3b8; margin: 0 0 6px; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?=htmlspecialchars($sp['desc'], ENT_QUOTES, 'UTF-8')?>
            </p>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 6px; margin-top: 4px;">
            <button type="button" onclick="openSponsorModal('<?=htmlspecialchars($sp['img'], ENT_QUOTES, 'UTF-8')?>', '<?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>')" style="background: none; border: none; padding: 0; color: #d4af37; font-size: 0.68rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
              <?=dx_icon('eye', '', 11)?> <span>Vedi 8K</span>
            </button>
            <?php if (!empty($sp['url']) && $sp['url'] !== '#'): ?>
              <a href="<?=$sp['url']?>" target="_blank" rel="noopener" style="font-size: 0.68rem; color: #38bdf8; text-decoration: none; display: inline-flex; align-items: center; gap: 3px; font-weight: 700;">
                <span>Visita</span> <?=dx_icon('external-link', '', 10)?>
              </a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top: 14px; padding: 10px; background: rgba(212,175,55,0.08); border-radius: 10px; border: 1px dashed rgba(212,175,55,0.35); text-align: center; font-size: 0.76rem; color: #e2e8f0; line-height: 1.4;">
    <?=dx_icon('shield-check', '', 14)?> <b>Garanzia di Sostegno Ufficiale:</b> la quota simbolica di 10€ copre interamente il pranzo comunitario e il materiale didattico grazie al supporto della rete dei 28 business partner.
  </div>

</section>

<!-- MODAL LIGHTBOX VIEWER 8K GRAFICA SPONSOR -->
<div id="sponsor8kModal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(3, 7, 18, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 14px; align-items: center; justify-content: center;" onclick="closeSponsorModal()">
  <div style="max-width: 960px; width: 100%; background: #0b101d; border: 2px solid #d4af37; border-radius: 16px; overflow: hidden; box-shadow: 0 0 40px rgba(212,175,55,0.3);" onclick="event.stopPropagation()">
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; background: #030712; border-bottom: 1px solid rgba(255,255,255,0.1);">
      <div style="display: flex; align-items: center; gap: 8px;">
        <span style="color: #d4af37;"><?=dx_icon('award', '', 16)?></span>
        <span id="sponsorModalTitle" style="font-size: 0.9rem; font-weight: 850; color: #ffffff;">Brand 8K Ultra-HD</span>
      </div>
      <button type="button" onclick="closeSponsorModal()" style="background: rgba(255,255,255,0.1); border: none; color: #ffffff; border-radius: 8px; width: 28px; height: 28px; font-weight: 900; font-size: 1rem; cursor: pointer;">&times;</button>
    </div>
    <div style="padding: 10px; text-align: center; background: #02050e;">
      <img id="sponsorModalImg" src="" alt="Brand Sponsor 8K" style="max-width: 100%; height: auto; border-radius: 10px; display: block; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.6);">
    </div>
    <div style="padding: 8px 16px; font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between; align-items: center; background: #090d18;">
      <span>Grafica Vettoriale Ultra-HD Scalabile a 8K</span>
      <button type="button" onclick="closeSponsorModal()" style="background: #d4af37; color: #030712; font-weight: 800; font-size: 0.72rem; border: none; border-radius: 6px; padding: 4px 10px; cursor: pointer;">Chiudi</button>
    </div>
  </div>
</div>

<script>
function openSponsorModal(imgUrl, brandName) {
  var modal = document.getElementById('sponsor8kModal');
  var modalImg = document.getElementById('sponsorModalImg');
  var modalTitle = document.getElementById('sponsorModalTitle');
  if (modal && modalImg) {
    modalImg.src = imgUrl;
    if (modalTitle) modalTitle.textContent = brandName + ' — Asset Brand 8K Ultra-HD';
    modal.style.display = 'flex';
  }
}
function closeSponsorModal() {
  var modal = document.getElementById('sponsor8kModal');
  if (modal) modal.style.display = 'none';
}
</script>
