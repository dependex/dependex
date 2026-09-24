<?php
/**
 * DEPENDEX — COMPONENTE TICKER LIBRI & PUBBLICAZIONI AMAZON KDP
 * Card scorrevoli con velocità calibrata per la lettura, pausa al tocco/hover
 * Conforme alla Viewport Master Spec e Governance AGENTS.md
 */

declare(strict_types=1);

require_once __DIR__ . '/../modules/books/BooksCatalogService.php';

use Dependex\Books\BooksCatalogService;

$tickerBooks = BooksCatalogService::getTickerItems();
?>
<section class="dx-books-ticker-section my-5" id="collana-libri-ticker" aria-label="Collana Libri e Pubblicazioni di Mirco Pregnolato">
  <div class="dx-books-ticker-header d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
      <div class="badge-neon-rainbow mb-2">
        <span class="dot"></span>
        <span style="color:#fde68a;">PUBBLICAZIONI UFFICIALI SU AMAZON KDP</span>
      </div>
      <h3 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 2.8vw, 2.2rem); color: #ffffff; margin: 0; font-weight: 800; line-height: 1.25;">
        I Libri e i Diari del <span class="text-rainbow">Metodo Hudolin</span>
      </h3>
      <p style="color: #cbd5e1; margin: 6px 0 0; font-size: 0.95rem; max-width: 680px;">
        Guide operative, quaderni di dialogo per le famiglie e manuali di scuola territoriale scritti da <strong>Mirco Pregnolato</strong>. Scorri le schede con calma o fermati per leggere i dettagli.
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="pubblicazioni.php" class="btn-rainbow-outline small" style="border-color: var(--neon-gold); color: #fde68a; text-decoration: none;">
        <span>Vedi Tutte le Schede</span>
        <?=dx_icon('arrow-right', 'text-neon-gold', 14)?>
      </a>
    </div>
  </div>

  <?php if (!empty($tickerBooks)): ?>
    <div class="dx-books-ticker-wrapper" tabindex="0" role="region" aria-label="Carosello scorrevole libri">
      <div class="dx-books-ticker-track">
        <?php foreach (array_merge($tickerBooks, $tickerBooks) as $bk): ?>
          <article class="dx-book-ticker-card">
            <div class="dx-book-card-inner">
              
              <!-- COPERTINA 3D REALISTICA -->
              <div class="dx-book-cover-wrap">
                <a href="<?=h($bk['amazon_url'])?>" target="_blank" rel="noopener" class="dx-book-cover-link" aria-label="Acquista <?=h($bk['title'])?> su Amazon">
                  <picture>
                    <source srcset="<?=h($bk['cover_webp'])?>" type="image/webp">
                    <img src="<?=h($bk['cover_jpg'])?>" 
                         alt="Copertina <?=h($bk['title'])?>" 
                         class="dx-book-cover-img" 
                         loading="lazy" 
                         width="110" 
                         height="166">
                  </picture>
                  <div class="dx-book-3d-shine"></div>
                </a>
              </div>

              <!-- CONTENUTI SCHEDA -->
              <div class="dx-book-card-body">
                <div class="dx-book-badge-row">
                  <span class="dx-book-series-badge" style="color: <?=$bk['badge_color']?>; border-color: <?=$bk['badge_color']?>;">
                    <?=h($bk['series'])?>
                  </span>
                  <span class="dx-book-pages-tag">
                    <?=dx_icon('book-open', '', 12)?> <?=h((string)$bk['pages'])?> pag.
                  </span>
                </div>

                <h4 class="dx-book-card-title">
                  <a href="pubblicazioni.php#<?=h($bk['id'])?>" class="dx-book-title-link">
                    <?=h($bk['title'])?>
                  </a>
                </h4>

                <p class="dx-book-card-hook">
                  <?=h($bk['hook'])?>
                </p>

                <!-- PREZZO E AZIONI -->
                <div class="dx-book-card-bottom">
                  <div class="dx-book-price-box">
                    <span class="dx-book-price-label">Cartaceo Amazon</span>
                    <strong class="dx-book-price-val"><?=h($bk['price_paperback'])?></strong>
                  </div>

                  <div class="dx-book-cta-group">
                    <a href="<?=h($bk['amazon_url'])?>" 
                       target="_blank" 
                       rel="noopener" 
                       class="btn-amazon-kdp" 
                       title="Acquista <?=h($bk['title'])?> su Amazon">
                      <span>Amazon Prime</span>
                      <?=dx_icon('external-link', '', 12)?>
                    </a>
                  </div>
                </div>

              </div> <!-- /.dx-book-card-body -->

            </div> <!-- /.dx-book-card-inner -->
          </article>
        <?php endforeach; ?>
      </div> <!-- /.dx-books-ticker-track -->
    </div> <!-- /.dx-books-ticker-wrapper -->
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 px-2">
    <small style="color: #94a3b8; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px;">
      <?=dx_icon('info', 'text-neon-cyan', 13)?>
      <span>Passa il mouse o tocca una scheda per <strong>fermare lo scorrimento</strong> e leggere con calma.</span>
    </small>
    <small style="color: #94a3b8; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px;">
      <?=dx_icon('shield-check', 'text-neon-green', 13)?>
      <span>Spedizione rapida e reso garantito Amazon Prime Italia</span>
    </small>
  </div>
</section>

<style>
/* ============================================================== */
/* DX BOOKS TICKER — VELOCITÀ LETTURA CALIBRATA & MOBILE-FIRST     */
/* ============================================================== */
.dx-books-ticker-section {
  position: relative;
  overflow: hidden;
  margin-left: auto;
  margin-right: auto;
  width: 100%;
}

.dx-books-ticker-wrapper {
  overflow-x: auto;
  overflow-y: hidden;
  position: relative;
  padding: 12px 0;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  mask-image: linear-gradient(to right, transparent, black 3%, black 97%, transparent);
  -webkit-mask-image: linear-gradient(to right, transparent, black 3%, black 97%, transparent);
}
.dx-books-ticker-wrapper::-webkit-scrollbar {
  display: none;
}

/* Animazione fluida calibrata su 58s (velocità pacata di lettura) */
.dx-books-ticker-track {
  display: flex;
  gap: 20px;
  width: max-content;
  animation: dxBooksScrollCalm 58s linear infinite;
  will-change: transform;
}

/* Pausa immediata al passaggio del cursore, al tocco o al focus */
.dx-books-ticker-wrapper:hover .dx-books-ticker-track,
.dx-books-ticker-wrapper:focus-within .dx-books-ticker-track,
.dx-books-ticker-track:hover,
.dx-books-ticker-track:active {
  animation-play-state: paused !important;
}

@keyframes dxBooksScrollCalm {
  0% { transform: translateX(0); }
  100% { transform: translateX(calc(-50% - 10px)); }
}

/* CARD SINGOLA DEL LIBRO */
.dx-book-ticker-card {
  width: 380px;
  max-width: 88vw;
  flex-shrink: 0;
  background: rgba(14, 18, 30, 0.94);
  border: 1px solid rgba(255, 215, 0, 0.22);
  border-radius: 18px;
  padding: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.55);
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease, box-shadow 0.3s ease;
}

.dx-book-ticker-card:hover {
  transform: translateY(-4px);
  border-color: rgba(255, 215, 0, 0.55);
  box-shadow: 0 14px 40px rgba(0, 240, 255, 0.18), 0 0 20px rgba(255, 215, 0, 0.15);
}

.dx-book-card-inner {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

/* COPERTINA DEL LIBRO CON EFFETTO 3D ELEVATION */
.dx-book-cover-wrap {
  width: 104px;
  flex-shrink: 0;
  position: relative;
  perspective: 700px;
}

.dx-book-cover-link {
  display: block;
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: -4px 6px 16px rgba(0, 0, 0, 0.7), 2px 2px 8px rgba(0,0,0,0.4);
  transition: transform 0.35s ease, box-shadow 0.35s ease;
  transform: rotateY(-4deg);
}

.dx-book-cover-link:hover {
  transform: rotateY(0deg) scale(1.04);
  box-shadow: 0 12px 28px rgba(0, 240, 255, 0.3);
}

.dx-book-cover-img {
  width: 100%;
  height: auto;
  aspect-ratio: 6 / 9;
  object-fit: cover;
  display: block;
  border-radius: 7px;
}

.dx-book-3d-shine {
  position: absolute;
  top: 0;
  left: 0;
  width: 20%;
  height: 100%;
  background: linear-gradient(to right, rgba(255,255,255,0.22), transparent);
  pointer-events: none;
}

/* CONTENUTI SCHEDA */
.dx-book-card-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.dx-book-badge-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 6px;
}

.dx-book-series-badge {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 2px 8px;
  border-radius: 6px;
  border: 1px solid currentColor;
  background: rgba(255, 255, 255, 0.04);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 175px;
}

.dx-book-pages-tag {
  font-size: 0.72rem;
  color: #94a3b8;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.dx-book-card-title {
  font-family: var(--font-serif);
  font-size: 1.08rem;
  font-weight: 800;
  line-height: 1.25;
  margin: 0 0 6px;
  color: #ffffff;
}

.dx-book-title-link {
  color: #ffffff;
  text-decoration: none;
  transition: color 0.2s ease;
}

.dx-book-title-link:hover {
  color: var(--neon-gold);
}

.dx-book-card-hook {
  font-size: 0.82rem;
  color: #cbd5e1;
  line-height: 1.45;
  margin: 0 0 12px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* BOTTOM AREA: PREZZO E CTA AMAZON */
.dx-book-card-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-top: auto;
  padding-top: 8px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.dx-book-price-box {
  display: flex;
  flex-direction: column;
}

.dx-book-price-label {
  font-size: 0.68rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.dx-book-price-val {
  color: #fde68a;
  font-size: 1.05rem;
  font-weight: 850;
  line-height: 1;
}

.btn-amazon-kdp {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, #ff9900, #ff7700);
  color: #0c101c !important;
  font-size: 0.78rem;
  font-weight: 850;
  padding: 8px 14px;
  border-radius: 10px;
  text-decoration: none;
  box-shadow: 0 3px 12px rgba(255, 153, 0, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
  min-height: 38px;
  white-space: nowrap;
}

.btn-amazon-kdp:hover {
  transform: scale(1.03);
  filter: brightness(1.1);
  box-shadow: 0 5px 18px rgba(255, 153, 0, 0.5);
  color: #000000 !important;
}

/* OTTIMIZZAZIONI MOBILE FIRST (<576px) */
@media (max-width: 576px) {
  .dx-book-ticker-card {
    width: 320px;
    padding: 12px;
  }
  .dx-book-cover-wrap {
    width: 88px;
  }
  .dx-book-card-title {
    font-size: 0.98rem;
  }
  .dx-book-card-hook {
    font-size: 0.78rem;
    -webkit-line-clamp: 2;
  }
  .btn-amazon-kdp {
    padding: 6px 10px;
    font-size: 0.74rem;
  }
}
</style>
