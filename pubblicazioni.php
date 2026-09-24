<?php
/**
 * DEPENDEX — PUBBLICAZIONI UFFICIALI & COLLANA AMAZON KDP
 * Autore: Mirco Pregnolato · Metodo Hudolin, Scuole SAT, Famiglia e Club
 * Mobile-First Widescreen & 9:16 Adaptive · Conforme alla Governance AGENTS.md
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/books/BooksCatalogService.php';

use Dependex\Books\BooksCatalogService;

$pageTitle = 'Collana Libri e Pubblicazioni Ufficiali Amazon KDP · Mirco Pregnolato';
$metaDesc = 'I libri, manuali formativi SAT e diari di bordo del Metodo Hudolin scritti da Mirco Pregnolato. Copertine ufficiali, descrizioni magnetiche, prezzi Amazon Prime e formati digitali.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/pubblicazioni.php';

$books = BooksCatalogService::getAll();

// Costruzione Schema.org ItemList con Book metadata
$schemaItems = [];
$pos = 1;
foreach ($books as $bk) {
    $schemaItems[] = [
        "@type" => "ListItem",
        "position" => $pos++,
        "item" => [
            "@type" => "Book",
            "name" => $bk['title'],
            "headline" => $bk['subtitle'],
            "description" => $bk['synopsis'],
            "author" => [
                "@type" => "Person",
                "name" => $bk['author']
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Amazon KDP & Dependex"
            ],
            "numberOfPages" => $bk['pages'],
            "bookFormat" => "https://schema.org/Paperback",
            "inLanguage" => "it-IT",
            "offers" => [
                "@type" => "Offer",
                "priceCurrency" => "EUR",
                "price" => str_replace([' €', ','], ['', '.'], $bk['price_paperback']),
                "availability" => "https://schema.org/InStock",
                "url" => $bk['amazon_url']
            ]
        ]
    ];
}

$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "CollectionPage",
    "name" => $pageTitle,
    "description" => $metaDesc,
    "url" => $canonicalUrl,
    "mainEntity" => [
        "@type" => "ItemList",
        "itemListElement" => $schemaItems
    ]
];

require '_header.php';
?>

<div class="container-169 py-4">

  <!-- ============================================================== -->
  <!-- HERO BANNER PUBBLICAZIONI (SUPER WOW & MOBILE FIRST)          -->
  <!-- ============================================================== -->
  <header class="dx-pub-hero mb-5 p-4 p-md-5" style="background: linear-gradient(135deg, rgba(14, 20, 36, 0.98) 0%, rgba(9, 13, 26, 0.96) 100%); border: 1.5px solid rgba(255, 215, 0, 0.35); border-radius: 26px; box-shadow: 0 10px 40px rgba(0,0,0,0.7), 0 0 35px rgba(255, 215, 0, 0.08);">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="badge-neon-rainbow mb-3">
          <span class="dot"></span>
          <span style="color: #fde68a;">COLLANA UFFICIALE AMAZON KDP · METODO HUDOLIN</span>
        </div>

        <h1 style="font-family: var(--font-serif); font-size: clamp(2.1rem, 4.5vw, 3.4rem); font-weight: 900; line-height: 1.15; color: #ffffff; margin-bottom: 1rem;">
          Le Pubblicazioni di <br>
          <span class="text-rainbow">Mirco Pregnolato</span>
        </h1>

        <div class="p-3 mb-4" style="background: rgba(255, 255, 255, 0.03); border-left: 3px solid var(--neon-cyan); border-radius: 0 14px 14px 0;">
          <p style="font-size: 1.1rem; color: #f1f5f9; line-height: 1.6; margin: 0; font-weight: 500;">
            Non semplici libri di lettura passiva, ma <strong>strumenti di cammino</strong>: quaderni di dialogo familiare, diari annuali per i Club e workbook formativi per le Scuole Territoriali.
          </p>
        </div>

        <p style="font-size: 1.02rem; color: #cbd5e1; line-height: 1.7; margin-bottom: 1.8rem;">
          Ogni opera nasce dall'esperienza viva nei <strong>Club Alcologici Territoriali</strong> e nell'approccio ecologico-sociale: zero etichette cliniche, centralità della persona dentro la famiglia e la comunità, responsabilità condivisa senza colpa.
        </p>

        <!-- PUNTI DI FORZA DELLA COLLANA -->
        <div class="d-flex flex-wrap gap-2 mb-4">
          <div style="background: rgba(255, 153, 0, 0.12); border: 1px solid rgba(255, 153, 0, 0.35); padding: 8px 14px; border-radius: 12px; font-size: 0.84rem; color: #fed7aa; display: flex; align-items: center; gap: 8px;">
            <?=dx_icon('check-circle', '', 15)?>
            <span>Disponibili su <strong>Amazon Prime Italia</strong></span>
          </div>
          <div style="background: rgba(0, 240, 255, 0.1); border: 1px solid rgba(0, 240, 255, 0.3); padding: 8px 14px; border-radius: 12px; font-size: 0.84rem; color: #a5f3fc; display: flex; align-items: center; gap: 8px;">
            <?=dx_icon('book-open', '', 15)?>
            <span>Formato 6x9" su Carta Crema Deluxe</span>
          </div>
          <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); padding: 8px 14px; border-radius: 12px; font-size: 0.84rem; color: #bbf7d0; display: flex; align-items: center; gap: 8px;">
            <?=dx_icon('shield-check', '', 15)?>
            <span>Approccio Etico Ecologico-Sociale</span>
          </div>
        </div>

        <!-- QUICK ANCHORS -->
        <div class="d-flex flex-wrap gap-2">
          <a href="#catalogo" class="btn primary" style="text-decoration: none; border-radius: 14px; padding: 10px 18px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
            <?=dx_icon('arrow-down', '', 16)?>
            <span>Esplora Catalogo</span>
          </a>
          <a href="offers.php" class="btn-rainbow-outline" style="text-decoration: none; border-radius: 14px; padding: 10px 16px; font-weight: 750; border-color: rgba(255,255,255,0.25); color: #fff;">
            <span>Bundle e PDF</span>
          </a>
        </div>
      </div>

      <!-- VISUAL 3D SPOTLIGHT HERO -->
      <div class="col-lg-4 text-center d-none d-lg-block">
        <div class="position-relative" style="perspective: 1000px;">
          <div style="transform: rotateY(-12deg) rotateX(6deg); box-shadow: -15px 25px 45px rgba(0,0,0,0.8); border-radius: 14px; overflow: hidden; display: inline-block; border: 2px solid rgba(255,215,0,0.4); max-width: 260px;">
            <img src="assets/img/books/52_settimane_cover.webp" alt="52 Settimane di Cambiamento" style="width: 100%; height: auto; display: block;">
          </div>
          <div style="margin-top: 18px; font-size: 0.85rem; color: #fde68a; font-weight: 700;">
            Nuova Edizione Integrata 2026
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ============================================================== -->
  <!-- BARRA FILTRI DINAMICA INTERATTIVA (VANILLA JS ZERO DIPENDENZE) -->
  <!-- ============================================================== -->
  <section id="catalogo" class="mb-4" style="scroll-margin-top: 80px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div class="dx-filter-nav d-flex flex-wrap gap-2" role="tablist">
        <button class="dx-filter-btn active" data-filter="all">Tutti i Volumi (<?=count($books)?>)</button>
        <button class="dx-filter-btn" data-filter="hudolin">Metodo Hudolin</button>
        <button class="dx-filter-btn" data-filter="sat">Scuole SAT (I, II, III)</button>
        <button class="dx-filter-btn" data-filter="famiglia">Famiglia al Centro</button>
        <button class="dx-filter-btn" data-filter="club">Club & Facilitazione</button>
        <button class="dx-filter-btn" data-filter="crescita">Crescita Umana</button>
      </div>

      <div class="dx-search-box">
        <input type="search" id="bookSearchInput" placeholder="Cerca libro, tema o codice..." aria-label="Cerca libro" style="background: rgba(14, 18, 30, 0.9); border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 12px; padding: 10px 16px; font-size: 0.88rem; width: 100%; min-width: 240px;">
      </div>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- GRIGLIA SCHEDE LIBRO SUPER WOW                                  -->
  <!-- ============================================================== -->
  <section class="mb-5">
    <div class="row g-4" id="booksGrid">
      <?php foreach ($books as $b): ?>
        <div class="col-12 col-md-6 col-xl-6 book-item-col" data-cat="<?=$b['filter_category']?>" data-title="<?=strtolower(h($b['title'] . ' ' . $b['subtitle'] . ' ' . $b['code']))?>">
          <article class="dx-book-showcase-card <?=$b['color_theme']?>" id="<?=$b['id']?>">
            
            <div class="dx-book-card-top-header">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="dx-badge-series" style="color: <?=$b['badge_color']?>; border-color: <?=$b['badge_color']?>;">
                  <?=$b['code']?> · <?=$b['series']?>
                </span>
                <span class="dx-badge-author">
                  AUTORE: <strong><?=h($b['author'])?></strong>
                </span>
              </div>
            </div>

            <div class="dx-book-card-content-grid">
              
              <!-- COLONNA COPERTINA 3D REALISTICA -->
              <div class="dx-book-cover-stage">
                <div class="dx-3d-book-holder">
                  <a href="<?=h($b['amazon_url'])?>" target="_blank" rel="noopener" class="dx-3d-cover-link" aria-label="Acquista <?=h($b['title'])?> su Amazon">
                    <picture>
                      <source srcset="<?=h($b['cover_webp'])?>" type="image/webp">
                      <img src="<?=h($b['cover_jpg'])?>" 
                           alt="Copertina Ufficiale <?=h($b['title'])?>" 
                           class="dx-3d-cover-img" 
                           loading="lazy" 
                           width="220" 
                           height="332">
                    </picture>
                    <div class="dx-book-spine-simulation"></div>
                    <div class="dx-book-sheen"></div>
                  </a>
                </div>

                <div class="text-center mt-3">
                  <span class="dx-prime-pill">
                    <?=dx_icon('check', 'text-neon-cyan', 13)?> Spedizione Prime
                  </span>
                </div>
              </div>

              <!-- COLONNA DETTAGLI EDITORIALI E COPY MAGNETICO -->
              <div class="dx-book-details-stage">
                <h2 class="dx-book-h2">
                  <a href="<?=h($b['amazon_url'])?>" target="_blank" rel="noopener">
                    <?=h($b['title'])?>
                  </a>
                </h2>
                <h3 class="dx-book-h3" style="color: <?=$b['badge_color']?>;">
                  <?=h($b['subtitle'])?>
                </h3>

                <!-- SPECIFICHE TECNICHE ESSENZIALI -->
                <div class="dx-book-specs-row">
                  <span><?=dx_icon('book-open', '', 14)?> <strong><?=$b['pages']?> pagine</strong></span>
                  <span><?=dx_icon('layout', '', 14)?> <?=$b['trim']?></span>
                </div>

                <!-- HOOK MAGNETICO -->
                <div class="dx-book-hook-callout">
                  <p><strong>"</strong> <?=h($b['hook'])?> <strong>"</strong></p>
                </div>

                <!-- SINOSSI PERSUASIVA (ESPANDIBILE PER MOBILE) -->
                <div class="dx-book-synopsis-box">
                  <p class="dx-book-synopsis-text" id="desc-<?=$b['id']?>">
                    <?=nl2br(h($b['synopsis']))?>
                  </p>
                </div>

                <!-- HIGHLIGHTS / TRASFORMAZIONI CHIAVE -->
                <div class="dx-book-highlights mb-3">
                  <div class="dx-high-title">COSA TROVERAI ALL'INTERNO:</div>
                  <ul class="dx-high-list">
                    <?php foreach ($b['highlights'] as $hl): ?>
                      <li><?=dx_icon('check-circle', 'text-neon-green', 14)?> <span><?=h($hl)?></span></li>
                    <?php endforeach; ?>
                  </ul>
                </div>

                <!-- TARGET ETHICAL AUDIENCE -->
                <div class="dx-book-audience-box mb-3">
                  <div class="audience-row">
                    <strong style="color: var(--neon-cyan);">Per chi è:</strong>
                    <span><?=h($b['target_audience'])?></span>
                  </div>
                  <div class="audience-row text-muted" style="font-size: 0.78rem; margin-top: 4px;">
                    <strong>Deontologia:</strong> <?=h($b['not_for'])?>
                  </div>
                </div>

                <!-- PREZZO E CTA AMAZON KDP -->
                <div class="dx-book-action-footer">
                  <div class="dx-price-display">
                    <span class="dx-label">Cartaceo Amazon:</span>
                    <strong class="dx-val"><?=$b['price_paperback']?></strong>
                  </div>

                  <div class="dx-buttons-cluster">
                    <a href="<?=h($b['amazon_url'])?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="btn-amazon-buy-prime" 
                       aria-label="Acquista <?=h($b['title'])?> su Amazon">
                      <span>Amazon Prime</span>
                      <?=dx_icon('external-link', '', 14)?>
                    </a>
                    <a href="offers.php" 
                       class="btn-format-options" 
                       title="Vedi formati digitali e bundle">
                      <span>Bundle e PDF</span>
                    </a>
                  </div>
                </div>

              </div> <!-- /.dx-book-details-stage -->

            </div> <!-- /.dx-book-card-content-grid -->

          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- BANNER ESPERIENZIALE BEWAY.LIFE                                 -->
  <!-- ============================================================== -->
  <section class="lux-metallic-card p-4 p-md-5 my-5" style="border: 1.5px solid rgba(0, 212, 255, 0.4); background: rgba(11, 15, 26, 0.95); border-radius: 24px;">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.76rem;">
          <span class="dot"></span>
          <span class="text-rainbow">DALLE PAGINE DEL LIBRO ALL'ESPERIENZA VISSUTA</span>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: clamp(1.5rem, 3vw, 2.2rem); color: #FFFFFF; font-weight: 800; margin-bottom: 0.75rem;">
          Dalla Teoria alla Vita Reale con <span class="text-rainbow">BEWAY.LIFE</span>
        </h3>
        <p style="color: #cbd5e1; font-size: 1.02rem; line-height: 1.65; margin-bottom: 1.2rem;">
          I libri forniscono le mappe e i punti di osservazione. I viaggi e i ritiri residenziali trasformano le parole in carne, respiro, relazioni vive e rigenerazione corporea. Scopri la formula sobria in mare aperto con workshop intensivi condotti da facilitatori esperti.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="viaggi-esperienziali.php" class="btn primary" style="text-decoration: none; border-radius: 12px; padding: 10px 18px; font-weight: 750;">
            <?=dx_icon('compass', '', 16)?>
            <span>Viaggi Esperienziali</span>
          </a>
          <a href="crociera-benessere-masterclass.php" class="btn-rainbow-outline" style="text-decoration: none; border-radius: 12px; padding: 10px 16px; font-weight: 750; border-color: var(--neon-cyan); color: #fff;">
            <span>Crociera Benessere</span>
          </a>
        </div>
      </div>
      <div class="col-lg-4 text-center">
        <img src="assets/img/sponsors/beway-life.webp" alt="BeWay Life" style="max-width: 190px; width: 100%; border-radius: 18px; box-shadow: 0 8px 30px rgba(0, 240, 255, 0.25); border: 1px solid rgba(0, 240, 255, 0.3);">
      </div>
    </div>
  </section>

  <!-- ============================================================== -->
  <!-- GUIDA CORTEX AI                                               -->
  <!-- ============================================================== -->
  <section class="luxury-hero-card lux-metallic-card p-4 p-md-5 text-center my-5" style="border: 1px solid rgba(255, 215, 0, 0.35); border-radius: 24px;">
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">SUPPORTO MAIEUTICO CORTEX</span>
    </div>
    <h3 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 2.5vw, 1.9rem); color: #FFFFFF; font-weight: 800; margin-bottom: 0.6rem;">
      Quale libro fa al caso tuo oggi?
    </h3>
    <p style="color: #cbd5e1; max-width: 620px; margin: 0 auto 1.5rem; font-size: 0.98rem; line-height: 1.6;">
      Se sei un familiare, un facilitatore o una persona che muove i primi passi e non sai da quale testo partire, chiedi a <strong>Cortex</strong>: ti indicherà con rispetto la risorsa più adatta al tuo momento.
    </p>
    <a href="cortex.php?q=<?=urlencode("Vorrei capire quale libro di Mirco Pregnolato sul Metodo Hudolin è più indicato per la mia situazione.")?>" class="btn primary" style="padding: 12px 28px; text-decoration: none; border-radius: 14px; font-weight: 800;">
      <?=dx_icon('brain', '', 18)?>
      <span style="margin-left: 8px;">Chiedi a Cortex</span>
    </a>
  </section>

</div> <!-- /.container-169 -->

<style>
/* ============================================================== */
/* DX SHOWCASE PUBBLICAZIONI — SUPER WOW LUXURY & MOBILE FIRST    */
/* ============================================================== */
.dx-filter-btn {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  font-size: 0.84rem;
  font-weight: 750;
  padding: 8px 16px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 44px;
}
.dx-filter-btn:hover {
  background: rgba(255, 255, 255, 0.09);
  color: #fff;
  border-color: rgba(255, 255, 255, 0.25);
}
.dx-filter-btn.active {
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(0, 240, 255, 0.15));
  border-color: var(--neon-gold);
  color: #fde68a;
  box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
}

/* CARD SINGOLA MOSTRATA */
.dx-book-showcase-card {
  background: rgba(12, 16, 28, 0.95);
  border: 1.5px solid rgba(255, 255, 255, 0.12);
  border-radius: 22px;
  padding: 24px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6);
  transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.dx-book-showcase-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 215, 0, 0.45);
  box-shadow: 0 16px 45px rgba(0, 240, 255, 0.15), 0 0 25px rgba(255, 215, 0, 0.1);
}

.dx-book-card-top-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 12px;
  margin-bottom: 18px;
}
.dx-badge-series {
  font-size: 0.74rem;
  font-weight: 850;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 3px 10px;
  border-radius: 8px;
  border: 1px solid currentColor;
  background: rgba(255, 255, 255, 0.04);
}
.dx-badge-author {
  font-size: 0.74rem;
  color: #94a3b8;
  letter-spacing: 0.04em;
}

/* GRID INTERNA: COPERTINA + DETTAGLI */
.dx-book-card-content-grid {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 22px;
  flex: 1;
}

/* STAGE COPERTINA 3D */
.dx-book-cover-stage {
  perspective: 900px;
}
.dx-3d-book-holder {
  position: relative;
  display: inline-block;
  width: 100%;
}
.dx-3d-cover-link {
  display: block;
  position: relative;
  border-radius: 10px;
  overflow: hidden;
  transform: rotateY(-8deg) rotateX(2deg);
  transform-style: preserve-3d;
  box-shadow: -8px 12px 24px rgba(0, 0, 0, 0.75), 0 2px 8px rgba(0, 0, 0, 0.5);
  transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
}
.dx-3d-cover-link:hover {
  transform: rotateY(0deg) rotateX(0deg) scale(1.03);
  box-shadow: 0 16px 36px rgba(0, 240, 255, 0.35);
}
.dx-3d-cover-img {
  width: 100%;
  height: auto;
  aspect-ratio: 6 / 9;
  object-fit: cover;
  display: block;
  border-radius: 9px;
}
.dx-book-sheen {
  position: absolute;
  top: 0;
  left: 0;
  width: 25%;
  height: 100%;
  background: linear-gradient(to right, rgba(255,255,255,0.22), transparent);
  pointer-events: none;
}
.dx-prime-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(0, 240, 255, 0.1);
  border: 1px solid rgba(0, 240, 255, 0.25);
  color: #a5f3fc;
  font-size: 0.74rem;
  font-weight: 750;
  padding: 3px 10px;
  border-radius: 999px;
}

/* STAGE DETTAGLI */
.dx-book-h2 {
  font-family: var(--font-serif);
  font-size: 1.45rem;
  font-weight: 850;
  line-height: 1.25;
  margin: 0 0 6px;
}
.dx-book-h2 a {
  color: #ffffff;
  text-decoration: none;
  transition: color 0.2s ease;
}
.dx-book-h2 a:hover {
  color: var(--neon-gold);
}
.dx-book-h3 {
  font-size: 0.88rem;
  font-weight: 750;
  line-height: 1.4;
  margin: 0 0 12px;
}
.dx-book-specs-row {
  display: flex;
  gap: 16px;
  font-size: 0.8rem;
  color: #94a3b8;
  margin-bottom: 12px;
  flex-wrap: wrap;
}
.dx-book-specs-row span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.dx-book-hook-callout {
  background: rgba(255, 255, 255, 0.03);
  border-left: 2px solid var(--neon-gold);
  border-radius: 0 8px 8px 0;
  padding: 8px 12px;
  margin-bottom: 12px;
}
.dx-book-hook-callout p {
  margin: 0;
  font-size: 0.86rem;
  color: #e2e8f0;
  font-style: italic;
  line-height: 1.45;
}
.dx-book-synopsis-text {
  font-size: 0.88rem;
  line-height: 1.6;
  color: #cbd5e1;
  margin-bottom: 12px;
}

/* HIGHLIGHTS */
.dx-high-title {
  font-size: 0.72rem;
  font-weight: 850;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.dx-high-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.dx-high-list li {
  font-size: 0.82rem;
  color: #e2e8f0;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  line-height: 1.4;
}
.dx-high-list li svg {
  flex-shrink: 0;
  margin-top: 3px;
}

/* AUDIENCE */
.dx-book-audience-box {
  background: rgba(8, 12, 22, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 0.82rem;
  line-height: 1.45;
}

/* FOOTER AZIONI E PREZZO */
.dx-book-action-footer {
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.dx-price-display {
  display: flex;
  flex-direction: column;
}
.dx-price-display .dx-label {
  font-size: 0.7rem;
  color: #94a3b8;
  text-transform: uppercase;
}
.dx-price-display .dx-val {
  font-size: 1.3rem;
  color: #fde68a;
  font-weight: 900;
  line-height: 1;
}
.dx-buttons-cluster {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.btn-amazon-buy-prime {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: linear-gradient(135deg, #ff9900, #ff7700);
  color: #0c101c !important;
  font-weight: 850;
  font-size: 0.86rem;
  padding: 10px 18px;
  border-radius: 12px;
  text-decoration: none;
  box-shadow: 0 4px 15px rgba(255, 153, 0, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
  min-height: 44px;
  white-space: nowrap;
}
.btn-amazon-buy-prime:hover {
  transform: scale(1.02);
  filter: brightness(1.1);
  box-shadow: 0 6px 20px rgba(255, 153, 0, 0.5);
  color: #000000 !important;
}
.btn-format-options {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #e2e8f0;
  font-size: 0.8rem;
  font-weight: 750;
  padding: 10px 14px;
  border-radius: 12px;
  text-decoration: none;
  min-height: 44px;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.btn-format-options:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #fff;
  border-color: rgba(255, 255, 255, 0.3);
}

/* RESPONSIVE DESIGN PER SMARTPHONE (MOBILE FIRST) */
@media (max-width: 600px) {
  .dx-book-card-content-grid {
    grid-template-columns: 1fr;
  }
  .dx-book-cover-stage {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 12px;
  }
  .dx-3d-book-holder {
    max-width: 170px;
  }
  .dx-book-showcase-card {
    padding: 16px;
  }
  .dx-book-h2 {
    font-size: 1.25rem;
  }
  .dx-book-action-footer {
    flex-direction: column;
    align-items: stretch;
  }
  .dx-price-display {
    flex-direction: row;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 4px;
  }
  .dx-buttons-cluster {
    width: 100%;
  }
  .btn-amazon-buy-prime, .btn-format-options {
    width: 100%;
  }
}
</style>

<script>
// Filtro Client-side & Search Realtime senza dipendenze
document.addEventListener('DOMContentLoaded', function() {
  const filterBtns = document.querySelectorAll('.dx-filter-btn');
  const bookCols = document.querySelectorAll('.book-item-col');
  const searchInput = document.getElementById('bookSearchInput');

  function applyFilters() {
    const activeBtn = document.querySelector('.dx-filter-btn.active');
    const filterCat = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
    const searchQuery = (searchInput ? searchInput.value.toLowerCase().trim() : '');

    bookCols.forEach(function(col) {
      const colCat = col.getAttribute('data-cat') || '';
      const colTitle = col.getAttribute('data-title') || '';

      const matchesCat = (filterCat === 'all' || colCat === filterCat);
      const matchesSearch = (searchQuery === '' || colTitle.includes(searchQuery));

      if (matchesCat && matchesSearch) {
        col.style.display = '';
      } else {
        col.style.display = 'none';
      }
    });
  }

  filterBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      filterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      applyFilters();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }
});
</script>

<?php require '_footer.php'; ?>
