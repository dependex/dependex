<?php
/**
 * DEPENDEX & ECOSYSTEM — SPONSOR DELL'EVENTO
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
        'img' => 'assets/img/sponsors/sicurissimo-online.webp',
        'url' => 'https://sicurissimo.online/'
    ],
    [
        'name' => 'betterway.agency',
        'slug' => 'betterway-agency',
        'category' => 'Growth & Marketing',
        'desc' => 'Fai compiere alla tua visione il salto decisivo. Sistemi di espansione ad alto impatto che convertono l\'attenzione in fatturato costante.',
        'icon' => 'activity',
        'color' => '#3b82f6',
        'img' => 'assets/img/sponsors/betterway-agency.webp',
        'url' => 'https://betterway.agency/'
    ],
    [
        'name' => 'neuralog.pro',
        'slug' => 'neuralog-pro',
        'category' => 'Company Brain',
        'desc' => 'Vedi in tempo reale ciò che sfugge agli altri. Telemetria e sinapsi intelligenti che orchestrano decisioni veloci, lucide e sicure per il tuo lavoro sereno.',
        'icon' => 'cpu',
        'color' => '#8b5cf6',
        'img' => 'assets/img/sponsors/neuralog-pro.webp',
        'url' => 'https://neuralog.pro/'
    ],
    [
        'name' => 'destinorandagio.it',
        'slug' => 'destinorandagio-it',
        'category' => 'Nomad & Lifestyle',
        'desc' => 'Sperimenta la vera libertà geografica. Unisciti a nomadi digitali e professionisti liberi che hanno scelto di vivere alle proprie condizioni.',
        'icon' => 'compass',
        'color' => '#ec4899',
        'img' => 'assets/img/sponsors/destinorandagio-it.webp',
        'url' => 'https://destinorandagio.it/'
    ],
    [
        'name' => 'beway.life',
        'slug' => 'beway-life',
        'category' => 'Longevity & Benessere',
        'desc' => 'Riaccendi la tua vitalità più autentica. Protocolli di longevità, biohacking ed energia vitale per una mente lucida e un corpo rigenerato.',
        'icon' => 'heart',
        'color' => '#06b6d4',
        'img' => 'assets/img/sponsors/beway-life.webp',
        'url' => 'https://beway.life/'
    ],
    [
        'name' => 'estao.app',
        'slug' => 'estao-app',
        'category' => 'Real Estate Intelligence',
        'desc' => 'Massimizza il rendimento del tuo patrimonio immobiliare. Valutazioni analitiche, gestione trasparente e strategie di messa a reddito continua.',
        'icon' => 'home',
        'color' => '#84cc16',
        'img' => 'assets/img/sponsors/estao-app.webp',
        'url' => 'https://estao.app/'
    ],
    [
        'name' => 'ixla.solutions',
        'slug' => 'ixla-solutions',
        'category' => 'Laser & Engineering',
        'desc' => 'Precisione industriale senza compromessi. Hardware d\'avanguardia e marcatura ad alta sicurezza per standard d\'eccellenza globale.',
        'icon' => 'zap',
        'color' => '#f97316',
        'img' => 'assets/img/sponsors/ixla-solutions.webp',
        'url' => 'https://ixla.solutions/'
    ],
    [
        'name' => 'metroeridania.it',
        'slug' => 'metroeridania-it',
        'category' => 'Mobilità & Ecosistemi',
        'desc' => 'Connetti territori, comunità e futuro sostenibile. Infrastrutture snelle e mobilità integrata lungo l\'asta del grande fiume Po.',
        'icon' => 'navigation',
        'color' => '#14b8a6',
        'img' => 'assets/img/sponsors/metroeridania-it.webp',
        'url' => 'https://metroeridania.it/'
    ],
    [
        'name' => 'mircopregnolato.it',
        'slug' => 'mircopregnolato-it',
        'category' => 'Holistic & Venture',
        'desc' => 'Sblocca il tuo potenziale strategico inespresso. Visione sistemica e venture governance per guidare persone e progetti verso l\'eccellenza sovrana.',
        'icon' => 'award',
        'color' => '#f59e0b',
        'img' => 'assets/img/sponsors/mircopregnolato-it.webp',
        'url' => 'https://mircopregnolato.it/'
    ],
    [
        'name' => 'campus.camp',
        'slug' => 'campus-camp',
        'category' => 'Formazione Immersiva',
        'desc' => 'Vivi l\'esperienza che riscrive i tuoi schemi mentali. Laboratori immersivi e confronto tra pari per una padronanza pratica immediata.',
        'icon' => 'check-circle',
        'color' => '#22c55e',
        'img' => 'assets/img/sponsors/campus-camp.webp',
        'url' => 'https://campus.camp/'
    ]
];
?>

<?php
$rainbowPalette = ['#ff3344', '#ff7700', '#ffd700', '#00ff77', '#00d4ff', '#3a55ff', '#b829ff'];
?>
<section class="m-card" style="border-color: rgba(212,175,55,0.45); background: rgba(13, 16, 26, 0.96); margin-top: 16px; border-radius: 20px; box-shadow: 0 16px 45px rgba(0,0,0,0.7);">

  <style>
    .sponsor-smart-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 14px;
    }
    @media (max-width: 1200px) {
      .sponsor-smart-grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }
    @media (max-width: 768px) {
      .sponsor-smart-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    @media (max-width: 480px) {
      .sponsor-smart-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <!-- GRIGLIA SPONSOR A 5 COLONNE BILANCIATE (5x2 ESATTE SU PC) -->
  <div class="sponsor-smart-grid">
    <?php foreach ($sponsors as $idx => $sp): 
      $spColor = $rainbowPalette[$idx % 7];
    ?>
      <article style="background: rgba(18, 22, 34, 0.94); border: 1px solid rgba(255, 255, 255, 0.12); border-top: 3px solid <?=$spColor?>; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 16px rgba(0,0,0,0.45); transition: transform 0.2s ease, box-shadow 0.2s ease;">
        
        <!-- IMMAGINE BRAND 16:9 WEBP ULTRA-LIGHT -->
        <div style="width: 100%; aspect-ratio: 16/9; background: #030712; border-bottom: 1px solid rgba(255,255,255,0.08); overflow: hidden; position: relative;">
          <a href="<?=htmlspecialchars($sp['url'], ENT_QUOTES, 'UTF-8')?>" target="_blank" rel="noopener" style="display: block; width: 100%; height: 100%;">
            <img src="<?=htmlspecialchars($sp['img'], ENT_QUOTES, 'UTF-8')?>?v=20260913v10" 
                 alt="<?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>" 
                 loading="lazy" 
                 decoding="async" 
                 width="960" 
                 height="540"
                 style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s ease;">
          </a>
        </div>

        <!-- CONTENUTO PNL & LINK -->
        <div style="padding: 10px 12px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; gap: 8px;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 6px; margin-bottom: 4px;">
              <h3 style="font-weight: 850; font-size: 0.88rem; color: #ffffff; margin: 0; line-height: 1.25; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <span style="color: <?=$spColor?>; font-size: 0.74rem; margin-right: 3px;">#<?=str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT)?></span>
                <?=htmlspecialchars($sp['name'], ENT_QUOTES, 'UTF-8')?>
              </h3>
              <span style="font-size: 0.62rem; font-weight: 800; color: <?=$spColor?>; text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap;">
                <?=htmlspecialchars($sp['category'], ENT_QUOTES, 'UTF-8')?>
              </span>
            </div>

            <p style="font-size: 0.78rem; color: #cbd5e1; margin: 0; line-height: 1.4; text-align: justify; text-justify: inter-word; hyphens: auto;">
              <?=htmlspecialchars($sp['desc'], ENT_QUOTES, 'UTF-8')?>
            </p>
          </div>

          <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 8px; margin-top: 2px;">
            <a href="<?=htmlspecialchars($sp['url'], ENT_QUOTES, 'UTF-8')?>" target="_blank" rel="noopener" class="btn" style="width: 100%; background: rgba(212,175,55,0.12); border: 1px solid <?=$spColor?>; color: #fff; font-size: 0.76rem; font-weight: 750; padding: 6px 10px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 5px; transition: all 0.2s ease;">
              <span>Visita il Sito</span>
              <?=dx_icon('external-link', '', 11)?>
            </a>
          </div>
        </div>

      </article>
    <?php endforeach; ?>
  </div>

  <div style="margin-top: 18px; padding: 12px 16px; background: rgba(212,175,55,0.08); border-radius: 10px; border: 1px dashed rgba(212,175,55,0.35); text-align: justify; text-justify: inter-word; hyphens: auto; font-size: 0.78rem; color: #e2e8f0; line-height: 1.45;">
    <?=dx_icon('shield-check', '', 14)?> <b>Garanzia di Sostegno Ufficiale:</b> la quota simbolica di 10€ copre interamente il pranzo comunitario e il materiale didattico grazie al supporto della rete.
  </div>

</section>
