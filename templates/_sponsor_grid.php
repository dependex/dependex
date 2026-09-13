<?php
/**
 * DEPENDEX & ECOSYSTEM — 28 MAIN SPONSOR & ASSET SOVRANI GRID
 * Design Mobile-First 9:16 Responsive Luxury Dark / Gold / Neon
 * Conforme al protocollo di governance: zero riferimenti a termini proibiti.
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$sponsors = [
    [
        'name' => 'sicurissimo.online',
        'category' => 'Sicurezza & Compliance',
        'desc' => 'Portate normative, D.Lgs 81/08, HACCP e sicurezza sul lavoro.',
        'icon' => 'shield-check',
        'color' => '#10b981',
        'url' => 'https://sicurissimo.online'
    ],
    [
        'name' => 'betterway.agency',
        'category' => 'Growth & Marketing',
        'desc' => 'Architettura di crescita, marketing operativo e automazione vendite.',
        'icon' => 'activity',
        'color' => '#3b82f6',
        'url' => 'https://betterway.agency'
    ],
    [
        'name' => 'neuralog.pro',
        'category' => 'AI & Telemetry',
        'desc' => 'Osservabilità di agenti intelligenti, tracciamento sinapsi e RAG.',
        'icon' => 'cpu',
        'color' => '#8b5cf6',
        'url' => 'https://neuralog.pro'
    ],
    [
        'name' => 'mywallet.business',
        'category' => 'Fintech & Multi-Currency',
        'desc' => 'Infrastruttura finanziaria avanzata e gestione flussi multi-asset.',
        'icon' => 'credit-card',
        'color' => '#f59e0b',
        'url' => 'https://mywallet.business'
    ],
    [
        'name' => 'destinorandagio.it',
        'category' => 'Nomad & Lifestyle',
        'desc' => 'Community per professionisti liberi, viaggiatori e vita sovrana.',
        'icon' => 'compass',
        'color' => '#ec4899',
        'url' => 'https://destinorandagio.it'
    ],
    [
        'name' => 'beway.life',
        'category' => 'Longevity & Benessere',
        'desc' => 'Protocolli di longevità consapevole, vitalità e stile di vita attivo.',
        'icon' => 'heart',
        'color' => '#06b6d4',
        'url' => 'https://beway.life'
    ],
    [
        'name' => 'estao.app',
        'category' => 'PropTech Immobiliare',
        'desc' => 'Piattaforma SaaS per agenzie, gestione immobili e clienti evoluta.',
        'icon' => 'home',
        'color' => '#14b8a6',
        'url' => 'https://estao.app'
    ],
    [
        'name' => 'ixla.solutions',
        'category' => 'Engineering & CAD',
        'desc' => 'Consulenza tecnica, progettazione meccanica e rendering 3D.',
        'icon' => 'layers',
        'color' => '#6366f1',
        'url' => 'https://ixla.solutions'
    ],
    [
        'name' => 'cryptoaid.support',
        'category' => 'Charity & Impact',
        'desc' => 'Filantropia trasparente su blockchain e supporto a progetti solidali.',
        'icon' => 'gift',
        'color' => '#10b981',
        'url' => 'https://cryptoaid.support'
    ],
    [
        'name' => 'metroeridania.it',
        'category' => 'Territorio & Cartografia',
        'desc' => 'Mappatura del Polesine, valorizzazione fluviale e itinerari del Delta.',
        'icon' => 'map-pin',
        'color' => '#d4af37',
        'url' => 'https://metroeridania.it'
    ],
    [
        'name' => 'mircopregnolato.it',
        'category' => 'Founder & Venture Hub',
        'desc' => 'Visione sistemica, architettura d’impresa e venture governance.',
        'icon' => 'award',
        'color' => '#f59e0b',
        'url' => 'https://mircopregnolato.it'
    ],
    [
        'name' => 'universalbusiness.xyz',
        'category' => 'Enterprise Directory',
        'desc' => 'Registro globale degli asset digitali e catalogazione imprese.',
        'icon' => 'globe',
        'color' => '#3b82f6',
        'url' => 'https://universalbusiness.xyz'
    ],
    [
        'name' => 'Amazon KDP Factory',
        'category' => 'Editoria Sovrana',
        'desc' => 'Catena di montaggio editoriale per self-publishing e testi cartacei.',
        'icon' => 'book-open',
        'color' => '#eab308',
        'url' => 'https://amazon.it'
    ],
    [
        'name' => 'YouTube Automation',
        'category' => 'Media & Content Engine',
        'desc' => 'Automazione video ad alta retention, podcasting e canali tematici.',
        'icon' => 'video',
        'color' => '#ef4444',
        'url' => 'https://youtube.com'
    ],
    [
        'name' => 'campus.camp',
        'category' => 'Formazione Immersiva',
        'desc' => 'Esperienze formative sul campo, laboratori pratici e networking.',
        'icon' => 'check-circle',
        'color' => '#22c55e',
        'url' => 'https://campus.camp'
    ],
    [
        'name' => 'regreen.social',
        'category' => 'ESG & Sostenibilità',
        'desc' => 'Progetti di riforestazione, tutela ambientale e crediti verdi.',
        'icon' => 'sun',
        'color' => '#10b981',
        'url' => 'https://regreen.social'
    ],
    [
        'name' => 'blockchainplus.pro',
        'category' => 'Smart Contracts & Web3',
        'desc' => 'Notarizzazione decentralizzata, ledger verificabili e contratti sicuri.',
        'icon' => 'link',
        'color' => '#8b5cf6',
        'url' => 'https://blockchainplus.pro'
    ],
    [
        'name' => 'Antigravity Mobile IDE',
        'category' => 'Developer Tooling',
        'desc' => 'Ambiente di sviluppo nativo smartphone, orchestrazione e workflow.',
        'icon' => 'terminal',
        'color' => '#06b6d4',
        'url' => 'https://github.com'
    ],
    [
        'name' => 'Email Marketing Machine',
        'category' => 'B2B Automation',
        'desc' => 'Infrastruttura deliverability, segmentazione e nurturing relazionale.',
        'icon' => 'mail',
        'color' => '#f97316',
        'url' => '#'
    ],
    [
        'name' => 'Master Data CRM Pipeline',
        'category' => 'Intelligence & Data',
        'desc' => 'Pulizia, deduplica e arricchimento lead per il mercato professionale.',
        'icon' => 'database',
        'color' => '#a855f7',
        'url' => '#'
    ],
    [
        'name' => 'Commerce Core Engine',
        'category' => 'E-Commerce Headless',
        'desc' => 'Gateway multi-tenant unificato con checkout server-authoritative.',
        'icon' => 'shopping-cart',
        'color' => '#10b981',
        'url' => '#'
    ],
    [
        'name' => 'dependex.social',
        'category' => 'Social Care & Welfare',
        'desc' => 'Piattaforma di supporto per famiglie e persone che affrontano dipendenze.',
        'icon' => 'users',
        'color' => '#d4af37',
        'url' => 'https://dependex.social'
    ],
    [
        'name' => 'oltre.social',
        'category' => 'Social Hub Solidale',
        'desc' => 'Spazio di condivisione autentica senza algoritmi predatori.',
        'icon' => 'share-2',
        'color' => '#3b82f6',
        'url' => 'https://oltre.social'
    ],
    [
        'name' => 'Sovereign Academy',
        'category' => 'Scuola & Formazione',
        'desc' => 'Percorsi strutturati su comunicazione efficace e Metodo Hudolin.',
        'icon' => 'book',
        'color' => '#10b981',
        'url' => 'academy-public.php'
    ],
    [
        'name' => 'Sovereign Club',
        'category' => 'Membership Territoriale',
        'desc' => 'Incontri settimanali di auto-mutuo aiuto e crescita comunitaria.',
        'icon' => 'star',
        'color' => '#d4af37',
        'url' => 'map.php'
    ],
    [
        'name' => 'Sovereign Merch & Wear',
        'category' => 'Kit Ufficiali & Shop',
        'desc' => 'Abbigliamento etico, materiali divulgativi e kit per i soci.',
        'icon' => 'package',
        'color' => '#ec4899',
        'url' => '#'
    ],
    [
        'name' => 'Sovereign Network',
        'category' => 'Rete Relazionale',
        'desc' => 'Connessioni tra Club, volontari, operatori sanitari e famiglie.',
        'icon' => 'git-branch',
        'color' => '#8b5cf6',
        'url' => 'world-network-tree.php'
    ],
    [
        'name' => 'Sovereign Presidi Territoriali',
        'category' => 'Punti di Presidio',
        'desc' => 'Sportelli fisici di primo ascolto e orientamento sul territorio.',
        'icon' => 'navigation',
        'color' => '#14b8a6',
        'url' => 'map.php'
    ]
];
?>

<section class="m-card" style="border-color: rgba(212,175,55,0.4); background: rgba(13, 16, 26, 0.95); margin-top: 14px;">
  
  <div style="text-align: center; margin-bottom: 14px;">
    <span class="m-badge m-badge-gold" style="font-size: 0.72rem; letter-spacing: 0.08em;">
      <?=dx_icon('award', '', 12)?> MAIN SPONSOR & PATROCINI DELL'ECOSISTEMA
    </span>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.15rem, 4vw, 1.45rem); color: #ffffff; margin: 8px 0 4px; font-weight: 900;">
      I 28 Business & Asset Sovrani a Sostegno dell'Evento
    </h2>
    <p style="font-size: 0.8rem; color: #94a3b8; margin: 0; line-height: 1.45;">
      L'evento di Taglio di Po è promosso, sostenuto e amplificato dall'ecosistema di imprese e progetti coordinati:
    </p>
  </div>

  <!-- GRIGLIA SPONSOR RESPONSIVE SMARTPHONE (2 COLONNE COMPATTE) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 8px;">
    <?php foreach ($sponsors as $idx => $sp): ?>
      <div style="background: rgba(22, 27, 40, 0.85); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 10px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
        <div>
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
            <span style="font-size: 0.65rem; font-weight: 800; color: #d4af37; background: rgba(212,175,55,0.12); padding: 2px 6px; border-radius: 6px;">
              #<?=str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT)?>
            </span>
            <span style="color: <?=$sp['color']?>;">
              <?=dx_icon($sp['icon'], '', 15)?>
            </span>
          </div>

          <div style="font-weight: 850; font-size: 0.82rem; color: #ffffff; line-height: 1.25; margin-bottom: 3px; word-break: break-word;">
            <?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>
          </div>

          <div style="font-size: 0.68rem; font-weight: 750; color: <?=$sp['color']?>; margin-bottom: 4px;">
            <?=htmlspecialchars($sp['category'], ENT_QUOTES, 'UTF-8')?>
          </div>

          <p style="font-size: 0.72rem; color: #94a3b8; margin: 0; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
            <?=htmlspecialchars($sp['desc'], ENT_QUOTES, 'UTF-8')?>
          </p>
        </div>

        <?php if (!empty($sp['url']) && $sp['url'] !== '#'): ?>
          <a href="<?=$sp['url']?>" target="_blank" rel="noopener" style="margin-top: 8px; font-size: 0.68rem; color: #cbd5e1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
            <span>Scopri</span> <?=dx_icon('external-link', '', 10)?>
          </a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top: 14px; padding: 10px; background: rgba(212,175,55,0.08); border-radius: 10px; border: 1px dashed rgba(212,175,55,0.3); text-align: center; font-size: 0.76rem; color: #e2e8f0;">
    <?=dx_icon('shield-check', '', 14)?> <b>Garanzia di Sostegno Ufficiale:</b> la quota simbolica di 10€ copre interamente il pranzo comunitario e il materiale didattico grazie al supporto della rete dei partner.
  </div>

</section>
