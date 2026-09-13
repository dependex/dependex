<?php
/**
 * DEPENDEX & ECOSYSTEM — 28 SPONSOR DELL'EVENTO
 * Design Mobile-First 9:16 e Widescreen 16:9 con Immagini Dirette, Copywriting Magnetico PNL e Link al Sito.
 * Conforme al protocollo di governance: zero riferimenti a termini proibiti e zero emoji.
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$sponsors = [
    [
        'name' => 'sicurissimo.online',
        'slug' => 'sicurissimo-online',
        'category' => 'Sicurezza & Compliance',
        'desc' => 'Proteggi ciò che hai costruito. Azzeri i rischi operativi e trasformi la conformità D.Lgs 81/08 in serenità tangibile per la tua impresa.',
        'icon' => 'shield-check',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/sicurissimo-online.svg',
        'url' => 'https://sicurissimo.online'
    ],
    [
        'name' => 'betterway.agency',
        'slug' => 'betterway-agency',
        'category' => 'Growth & Marketing',
        'desc' => 'Fai compiere alla tua visione il salto decisivo. Sistemi di espansione ad alto impatto che convertono l\'attenzione in fatturato costante.',
        'icon' => 'activity',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/betterway-agency.svg',
        'url' => 'https://betterway.agency'
    ],
    [
        'name' => 'neuralog.pro',
        'slug' => 'neuralog-pro',
        'category' => 'AI & Telemetry',
        'desc' => 'Vedi in tempo reale ciò che sfugge agli altri. Telemetria e sinapsi intelligenti che orchestrano decisioni veloci, lucide e sicure.',
        'icon' => 'cpu',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/neuralog-pro.svg',
        'url' => 'https://neuralog.pro'
    ],
    [
        'name' => 'mywallet.business',
        'slug' => 'mywallet-business',
        'category' => 'Fintech & Multi-Currency',
        'desc' => 'Prendi il comando assoluto della tua liquidità. Flussi multi-valuta e tesoreria blindata per una sovranità economica senza intermediari.',
        'icon' => 'credit-card',
        'color' => '#f59e0b',
        'img' => 'assets/img/sponsors/mywallet-business.svg',
        'url' => 'https://mywallet.business'
    ],
    [
        'name' => 'destinorandagio.it',
        'slug' => 'destinorandagio-it',
        'category' => 'Nomad & Lifestyle',
        'desc' => 'Sperimenta la vera libertà geografica. Unisciti a nomadi digitali e professionisti liberi che hanno scelto di vivere alle proprie condizioni.',
        'icon' => 'compass',
        'color' => '#ec4899',
        'img' => 'assets/img/sponsors/destinorandagio-it.svg',
        'url' => 'https://destinorandagio.it'
    ],
    [
        'name' => 'beway.life',
        'slug' => 'beway-life',
        'category' => 'Longevity & Benessere',
        'desc' => 'Riaccendi la tua vitalità più autentica. Protocolli di longevità, biohacking ed energia vitale per una mente lucida e un corpo rigenerato.',
        'icon' => 'heart',
        'color' => '#06b6d4',
        'img' => 'assets/img/sponsors/beway-life.svg',
        'url' => 'https://beway.life'
    ],
    [
        'name' => 'estao.app',
        'slug' => 'estao-app',
        'category' => 'PropTech Immobiliare',
        'desc' => 'Semplifica, velocizza, concludi. La piattaforma immobiliare avanzata che anticipa le mosse del mercato e trasforma contatti in accordi chiusi.',
        'icon' => 'home',
        'color' => '#14b8a6',
        'img' => 'assets/img/sponsors/estao-app.svg',
        'url' => 'https://estao.app'
    ],
    [
        'name' => 'ixla.solutions',
        'slug' => 'ixla-solutions',
        'category' => 'Engineering & CAD',
        'desc' => 'Dal concetto alla materia con precisione millimetrica. Ingegneria d\'avanguardia e modellazione tridimensionale per le tue idee più ambiziose.',
        'icon' => 'layers',
        'color' => '#6366f1',
        'img' => 'assets/img/sponsors/ixla-solutions.svg',
        'url' => 'https://ixla.solutions'
    ],
    [
        'name' => 'cryptoaid.support',
        'slug' => 'cryptoaid-support',
        'category' => 'Charity & Impact',
        'desc' => 'Trasforma l\'innovazione in speranza concreta. Filantropia trasparente e aiuto diretto che arriva esattamente dove serve, senza filtri.',
        'icon' => 'gift',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/cryptoaid-support.svg',
        'url' => 'https://cryptoaid.support'
    ],
    [
        'name' => 'metroeridania.it',
        'slug' => 'metroeridania-it',
        'category' => 'Territorio & Cartografia',
        'desc' => 'Riconnettiti alle radici della bellezza fluviale. Itinerari esclusivi, cartografia viva ed ecologia comunitaria nel cuore autentico del Delta del Po.',
        'icon' => 'map-pin',
        'color' => '#d4af37',
        'img' => 'assets/img/sponsors/metroeridania-it.svg',
        'url' => 'https://metroeridania.it'
    ],
    [
        'name' => 'mircopregnolato.it',
        'slug' => 'mircopregnolato-it',
        'category' => 'Founder & Venture Hub',
        'desc' => 'Sblocca il tuo potenziale strategico inespresso. Visione sistemica e venture governance per guidare persone e progetti verso l\'eccellenza sovrana.',
        'icon' => 'award',
        'color' => '#f59e0b',
        'img' => 'assets/img/sponsors/mircopregnolato-it.svg',
        'url' => 'https://mircopregnolato.it'
    ],
    [
        'name' => 'universalbusiness.xyz',
        'slug' => 'universalbusiness-xyz',
        'category' => 'Enterprise Directory',
        'desc' => 'Fai brillare il tuo valore sulla mappa globale. Il registro internazionale che certifica e posiziona gli asset digitali e le imprese virtuose.',
        'icon' => 'globe',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/universalbusiness-xyz.svg',
        'url' => 'https://universalbusiness.xyz'
    ],
    [
        'name' => 'Amazon KDP Factory',
        'slug' => 'amazon-kdp-factory',
        'category' => 'Editoria Sovrana',
        'desc' => 'Incidi il tuo messaggio nel mondo. Catena di produzione editoriale che trasforma la tua competenza in volumi cartacei distribuiti ovunque.',
        'icon' => 'book-open',
        'color' => '#eab308',
        'img' => 'assets/img/sponsors/amazon-kdp-factory.svg',
        'url' => 'offers.php'
    ],
    [
        'name' => 'YouTube Automation',
        'slug' => 'youtube-automation',
        'category' => 'Media & Content Engine',
        'desc' => 'Cattura l\'attenzione e domina l\'interesse del pubblico. Format video ad altissima ritenzione che costruiscono autorevolezza e seguito fedele.',
        'icon' => 'video',
        'color' => '#ef4444',
        'img' => 'assets/img/sponsors/youtube-automation.svg',
        'url' => 'https://youtube.com'
    ],
    [
        'name' => 'campus.camp',
        'slug' => 'campus-camp',
        'category' => 'Formazione Immersiva',
        'desc' => 'Vivi l\'esperienza che riscrive i tuoi schemi mentali. Laboratori immersivi e confronto tra pari per una padronanza pratica immediata.',
        'icon' => 'check-circle',
        'color' => '#22c55e',
        'img' => 'assets/img/sponsors/campus-camp.svg',
        'url' => 'https://campus.camp'
    ],
    [
        'name' => 'regreen.social',
        'slug' => 'regreen-social',
        'category' => 'ESG & Sostenibilità',
        'desc' => 'Lascia un\'impronta positiva indelebile. Custodia attiva della natura e crediti ambientali verificati per un futuro solido e rigenerato.',
        'icon' => 'sun',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/regreen-social.svg',
        'url' => 'https://regreen.social'
    ],
    [
        'name' => 'blockchainplus.pro',
        'slug' => 'blockchainplus-pro',
        'category' => 'Smart Contracts & Web3',
        'desc' => 'Certifica la verità con certezza crittografica assoluta. Contratti intelligenti e registri immutabili per accordi blindati a prova di futuro.',
        'icon' => 'link',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/blockchainplus-pro.svg',
        'url' => 'https://blockchainplus.pro'
    ],
    [
        'name' => 'Antigravity Mobile IDE',
        'slug' => 'antigravity-mobile-ide',
        'category' => 'Developer Tooling',
        'desc' => 'Crea e rilascia soluzioni ovunque ti trovi. L\'ambiente di sviluppo agile che elimina ogni attrito tra intuizione e applicazione reale.',
        'icon' => 'terminal',
        'color' => '#06b6d4',
        'img' => 'assets/img/sponsors/antigravity-mobile-ide.svg',
        'url' => 'https://github.com'
    ],
    [
        'name' => 'Email Marketing Machine',
        'slug' => 'email-marketing-machine',
        'category' => 'B2B Automation',
        'desc' => 'Entra in contatto con le persone con messaggi che toccano le corde giuste. Flussi di relazione che costruiscono fiducia e risposte immediate.',
        'icon' => 'mail',
        'color' => '#f97316',
        'img' => 'assets/img/sponsors/email-marketing-machine.svg',
        'url' => 'contact.php'
    ],
    [
        'name' => 'Master Data CRM Pipeline',
        'slug' => 'master-data-crm-pipeline',
        'category' => 'Intelligence & Data',
        'desc' => 'Trasforma i contatti in relazioni solide e durature. Struttura dati intelligente che ti consente di agire sempre al momento opportuno.',
        'icon' => 'database',
        'color' => '#a855f7',
        'img' => 'assets/img/sponsors/master-data-crm-pipeline.svg',
        'url' => 'world-club-explorer.php'
    ],
    [
        'name' => 'Commerce Core Engine',
        'slug' => 'commerce-core-engine',
        'category' => 'E-Commerce Headless',
        'desc' => 'Offri un\'esperienza di partecipazione senza barriere. Flussi di adesione istantanei e protetti che massimizzano la serenità di chi sostiene.',
        'icon' => 'shopping-cart',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/commerce-core-engine.svg',
        'url' => 'offers.php'
    ],
    [
        'name' => 'dependex.social',
        'slug' => 'dependex-social',
        'category' => 'Social Care & Welfare',
        'desc' => 'Sciogli l\'illusione della dipendenza e ritrova il tuo centro. Percorsi ecologico-sociali per recuperare lucidità, rispetto e calore familiare.',
        'icon' => 'users',
        'color' => '#d4af37',
        'img' => 'assets/img/sponsors/dependex-social.svg',
        'url' => 'https://dependex.social'
    ],
    [
        'name' => 'oltre.social',
        'slug' => 'oltre-social',
        'category' => 'Social Hub Solidale',
        'desc' => 'Respira in uno spazio di connessione autentica. Condivisione libera senza algoritmi tossici, dove la persona viene sempre prima dei numeri.',
        'icon' => 'share-2',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/oltre-social.svg',
        'url' => 'https://oltre.social'
    ],
    [
        'name' => 'Sovereign Academy',
        'slug' => 'sovereign-academy',
        'category' => 'Scuola & Formazione',
        'desc' => 'Allena la tua comunicazione a creare armonia e coesione. Percorsi guidati con il Metodo Hudolin per dialogare con ascolto profondo e autorevolezza.',
        'icon' => 'book',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/sovereign-academy.svg',
        'url' => 'academy-public.php'
    ],
    [
        'name' => 'Sovereign Club',
        'slug' => 'sovereign-club',
        'category' => 'Membership Territoriale',
        'desc' => 'Siediti in un cerchio dove il giudizio non esiste. Incontri settimanali di accoglienza e reciproco coraggio per progredire insieme ogni giorno.',
        'icon' => 'star',
        'color' => '#d4af37',
        'img' => 'assets/img/sponsors/sovereign-club.svg',
        'url' => 'world-club-explorer.php'
    ],
    [
        'name' => 'Sovereign Merch & Wear',
        'slug' => 'sovereign-merch-wear',
        'category' => 'Kit Ufficiali & Shop',
        'desc' => 'Indossa con orgoglio la tua scelta di lucidità e sobrietà. Materiali etici e simboli che testimoniano la tua sovranità quotidiana.',
        'icon' => 'package',
        'color' => '#ec4899',
        'img' => 'assets/img/sponsors/sovereign-merch-wear.svg',
        'url' => 'offers.php'
    ],
    [
        'name' => 'Sovereign Network',
        'slug' => 'sovereign-network',
        'category' => 'Rete Relazionale',
        'desc' => 'Moltiplica la tua forza attraverso una rete solidale viva. Connessioni continue tra Club, famiglie, formatori e volontari pronti a sostenerti.',
        'icon' => 'git-branch',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/sovereign-network.svg',
        'url' => 'world-network-tree.php'
    ],
    [
        'name' => 'Sovereign Presidi Territoriali',
        'slug' => 'sovereign-presidi-territoriali',
        'category' => 'Punti di Presidio',
        'desc' => 'Trova un porto sicuro e una guida accogliente vicino a te. Sportelli fisici di primo ascolto per orientare chi cerca aiuto verso la rinascita.',
        'icon' => 'navigation',
        'color' => '#14b8a6',
        'img' => 'assets/img/sponsors/sovereign-presidi-territoriali.svg',
        'url' => 'world-club-explorer.php'
    ]
];
?>

<?php
$rainbowPalette = ['#ff3344', '#ff7700', '#ffd700', '#00ff77', '#00d4ff', '#3a55ff', '#b829ff'];
?>
<section class="m-card" style="border-color: rgba(212,175,55,0.45); background: rgba(13, 16, 26, 0.96); margin-top: 16px; border-radius: 20px; box-shadow: 0 16px 45px rgba(0,0,0,0.7);">
  
  <div style="text-align: center; margin-bottom: 20px;">
    <div class="badge-neon-rainbow mb-2" style="font-size: 0.72rem; padding: 4px 14px;">
      <span class="dot"></span>
      <span class="text-rainbow">28 ASSET SOVRANI A SOSTEGNO DELL'EVENTO</span>
    </div>
    <h2 style="font-family: var(--font-serif); font-size: clamp(1.35rem, 4.5vw, 1.85rem); color: #ffffff; margin: 0; font-weight: 900; letter-spacing: 0.04em;">
      <?=dx_icon('award', 'text-gold', 20)?> SPONSOR DELL'EVENTO
    </h2>
  </div>

  <!-- GRIGLIA SPONSOR (RESPONSIVE ADATTIVA 9:16 E 16:9) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 270px), 1fr)); gap: 18px;">
    <?php foreach ($sponsors as $idx => $sp): 
      $spColor = $rainbowPalette[$idx % 7];
    ?>
      <article style="background: rgba(18, 22, 34, 0.94); border: 1px solid rgba(255, 255, 255, 0.12); border-top: 3px solid <?=$spColor?>; border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 6px 20px rgba(0,0,0,0.45); transition: transform 0.25s ease, box-shadow 0.25s ease;">
        
        <!-- IMMAGINE BRAND -->
        <div style="width: 100%; aspect-ratio: 16/9; background: #030712; border-bottom: 1px solid rgba(255,255,255,0.08); overflow: hidden; position: relative;">
          <a href="<?=htmlspecialchars($sp['url'], ENT_QUOTES, 'UTF-8')?>" target="_blank" rel="noopener" style="display: block; width: 100%; height: 100%;">
            <img src="<?=htmlspecialchars($sp['img'], ENT_QUOTES, 'UTF-8')?>" 
                 alt="<?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>" 
                 width="1200" 
                 height="675"
                 loading="lazy"
                 style="width: 100%; height: 100%; aspect-ratio: 16/9; object-fit: cover; display: block; transition: transform 0.3s ease;">
          </a>
          <div style="position: absolute; top: 6px; left: 6px; font-size: 0.62rem; font-weight: 900; color: #fff; background: rgba(3,7,18,0.85); border: 1px solid <?=$spColor?>; padding: 2px 6px; border-radius: 4px;">
            #<?=str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT)?>
          </div>
        </div>

        <!-- CONTENUTO PNL & LINK -->
        <div style="padding: 14px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; gap: 10px;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 6px;">
              <h3 style="font-weight: 850; font-size: 0.95rem; color: #ffffff; margin: 0; line-height: 1.3;">
                <?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>
              </h3>
              <span style="font-size: 0.68rem; font-weight: 800; color: <?=$spColor?>; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap;">
                <?=htmlspecialchars($sp['category'], ENT_QUOTES, 'UTF-8')?>
              </span>
            </div>

            <p style="font-size: 0.82rem; color: #cbd5e1; margin: 0; line-height: 1.45;">
              <?=htmlspecialchars($sp['desc'], ENT_QUOTES, 'UTF-8')?>
            </p>
          </div>

          <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px; margin-top: 4px;">
            <a href="<?=htmlspecialchars($sp['url'], ENT_QUOTES, 'UTF-8')?>" target="_blank" rel="noopener" class="btn" style="width: 100%; background: rgba(212,175,55,0.12); border: 1px solid <?=$spColor?>; color: #fff; font-size: 0.8rem; font-weight: 750; padding: 8px 12px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s ease;">
              <span>Visita il Sito</span>
              <?=dx_icon('external-link', '', 12)?>
            </a>
          </div>
        </div>

      </article>
    <?php endforeach; ?>
  </div>

  <div style="margin-top: 18px; padding: 12px 16px; background: rgba(212,175,55,0.08); border-radius: 10px; border: 1px dashed rgba(212,175,55,0.35); text-align: center; font-size: 0.78rem; color: #e2e8f0; line-height: 1.45;">
    <?=dx_icon('shield-check', '', 14)?> <b>Garanzia di Sostegno Ufficiale:</b> la quota simbolica di 10€ copre interamente il pranzo comunitario e il materiale didattico grazie al supporto della rete.
  </div>

</section>
