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
        'name' => 'sicurissimo.onlinE',
        'slug' => 'sicurissimo-online',
        'category' => 'Compliance Aziendale',
        'desc' => 'Proteggi ciò che hai costruito. Azzeri i rischi operativi e trasformi la conformità in serenità tangibile per la tua impresa.',
        'icon' => 'shield-check',
        'color' => '#10b981',
        'img' => 'assets/img/sponsors/sicurissimo-online.svg',
        'url' => 'https://sicurissimo.online/'
    ],
    [
        'name' => 'betterway.agency',
        'slug' => 'betterway-agency',
        'category' => 'Growth & Marketing',
        'desc' => 'Fai compiere alla tua visione il salto decisivo. Sistemi di espansione ad alto impatto che convertono l\'attenzione in fatturato costante.',
        'icon' => 'activity',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/betterway-agency.svg',
        'url' => 'https://betterway.agency/'
    ],
    [
        'name' => 'neuralog.pro',
        'slug' => 'neuralog-pro',
        'category' => 'Company Brain',
        'desc' => 'Vedi in tempo reale ciò che sfugge agli altri. Telemetria e sinapsi intelligenti che orchestrano decisioni veloci, lucide e sicure per il tuo lavoro sereno.',
        'icon' => 'cpu',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/neuralog-pro.svg',
        'url' => 'https://neuralog.pro/'
    ],
    [
        'name' => 'destinorandagio.it',
        'slug' => 'destinorandagio-it',
        'category' => 'Nomad & Lifestyle',
        'desc' => 'Sperimenta la vera libertà geografica. Unisciti a nomadi digitali e professionisti liberi che hanno scelto di vivere alle proprie condizioni.',
        'icon' => 'compass',
        'color' => '#ec4899',
        'img' => 'assets/img/sponsors/destinorandagio-it.svg',
        'url' => 'https://destinorandagio.it/'
    ],
    [
        'name' => 'beway.life',
        'slug' => 'beway-life',
        'category' => 'Longevity & Benessere',
        'desc' => 'Riaccendi la tua vitalità più autentica. Protocolli di longevità, biohacking ed energia vitale per una mente lucida e un corpo rigenerato.',
        'icon' => 'heart',
        'color' => '#06b6d4',
        'img' => 'assets/img/sponsors/beway-life.svg',
        'url' => 'https://beway.life/'
    ],
    [
        'name' => 'estao.app',
        'slug' => 'estao-app',
        'category' => 'PropTech Immobiliare',
        'desc' => 'Semplifica, velocizza, concludi. La piattaforma immobiliare avanzata che anticipa le mosse del mercato e trasforma contatti in accordi chiusi.',
        'icon' => 'home',
        'color' => '#14b8a6',
        'img' => 'assets/img/sponsors/estao-app.svg',
        'url' => 'https://estao.app/'
    ],
    [
        'name' => 'ixla.solutions',
        'slug' => 'ixla-solutions',
        'category' => 'Supreme Engineering',
        'desc' => 'Dal concetto alla materia con precisione millimetrica. Moduli abitativi per ambienti estremi.',
        'icon' => 'layers',
        'color' => '#6366f1',
        'img' => 'assets/img/sponsors/ixla-solutions.svg',
        'url' => 'https://ixla.solutions/'
    ],
    [
        'name' => 'metroeridania.it',
        'slug' => 'metroeridania-it',
        'category' => 'Territorio & Cartografia',
        'desc' => 'Riconnettiti alle radici della bellezza fluviale. Itinerari esclusivi, cartografia viva ed ecologia comunitaria nel cuore autentico del Delta del Po.',
        'icon' => 'map-pin',
        'color' => '#d4af37',
        'img' => 'assets/img/sponsors/metroeridania-it.svg',
        'url' => 'https://metroeridania.it/'
    ],
    [
        'name' => 'mircopregnolato.it',
        'slug' => 'mircopregnolato-it',
        'category' => 'Holistic & Venture',
        'desc' => 'Sblocca il tuo potenziale strategico inespresso. Visione sistemica e venture governance per guidare persone e progetti verso l\'eccellenza sovrana.',
        'icon' => 'award',
        'color' => '#f59e0b',
        'img' => 'assets/img/sponsors/mircopregnolato-it.svg',
        'url' => 'https://mircopregnolato.it/'
    ],
    [
        'name' => 'campus.camp',
        'slug' => 'campus-camp',
        'category' => 'Formazione Immersiva',
        'desc' => 'Vivi l\'esperienza che riscrive i tuoi schemi mentali. Laboratori immersivi e confronto tra pari per una padronanza pratica immediata.',
        'icon' => 'check-circle',
        'color' => '#22c55e',
        'img' => 'assets/img/sponsors/campus-camp.svg',
        'url' => 'https://campus.camp/'
    ]
];
?>

<?php
$rainbowPalette = ['#ff3344', '#ff7700', '#ffd700', '#00ff77', '#00d4ff', '#3a55ff', '#b829ff'];
?>
<section class="m-card" style="border-color: rgba(212,175,55,0.45); background: rgba(13, 16, 26, 0.96); margin-top: 16px; border-radius: 20px; box-shadow: 0 16px 45px rgba(0,0,0,0.7);">

  <!-- GRIGLIA SPONSOR (RESPONSIVE ADATTIVA 9:16 E 16:9) -->
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 270px), 1fr)); gap: 18px;">
    <?php foreach ($sponsors as $idx => $sp): 
      $spColor = $rainbowPalette[$idx % 7];
    ?>
      <article style="background: rgba(18, 22, 34, 0.94); border: 1px solid rgba(255, 255, 255, 0.12); border-top: 3px solid <?=$spColor?>; border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 6px 20px rgba(0,0,0,0.45); transition: transform 0.25s ease, box-shadow 0.25s ease;">
        
        <!-- IMMAGINE BRAND -->
        <div style="width: 100%; aspect-ratio: 16/9; background: #030712; border-bottom: 1px solid rgba(255,255,255,0.08); overflow: hidden; position: relative;">
          <a href="<?=htmlspecialchars($sp['url'], ENT_QUOTES, 'UTF-8')?>" target="_blank" rel="noopener" style="display: block; width: 100%; height: 100%;">
            <img src="<?=htmlspecialchars($sp['img'], ENT_QUOTES, 'UTF-8')?>?v=20260913v3" 
                 alt="<?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>" 
                 width="1200" 
                 height="675"
                 style="width: 100%; height: auto; aspect-ratio: 16/9; object-fit: cover; display: block; transition: transform 0.3s ease;">
          </a>
        </div>

        <!-- CONTENUTO PNL & LINK -->
        <div style="padding: 14px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; gap: 10px;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px; margin-bottom: 6px;">
              <h3 style="font-weight: 850; font-size: 0.95rem; color: #ffffff; margin: 0; line-height: 1.3;">
                <span style="color: <?=$spColor?>; font-size: 0.8rem; margin-right: 4px;">#<?=str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT)?></span>
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
