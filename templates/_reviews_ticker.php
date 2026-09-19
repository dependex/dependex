<?php
/**
 * Componente Ticker Recensioni e Testimonianze Verificate dei Club CAT
 * Inclusione in Home Page e Landing di Conversione
 */
require_once __DIR__ . '/../modules/reviews/ReviewsService.php';
$reviewsCards = ReviewsService::getTickerCards(12);
$stats = ReviewsService::getStats();
?>
<section class="dx-reviews-ticker-section my-5" id="recensioni-ticker">
  <div class="dx-reviews-ticker-header d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
      <div class="badge-neon-rainbow mb-2">
        <span class="dot"></span>
        <span style="color:#fde68a;">ESPERIENZE REALI DAI CLUB D'ITALIA</span>
      </div>
      <h3 style="font-family: var(--font-serif); font-size: clamp(1.4rem, 2.8vw, 2.1rem); color: #ffffff; margin: 0; font-weight: 800;">
        Cosa dice chi ha fatto il <span class="text-rainbow">primo passo</span>
      </h3>
      <p style="color: #cbd5e1; margin: 4px 0 0; font-size: 0.95rem;">
        Ascolta la voce di chi ha vissuto la stessa nebbia ed è tornato a sorridere grazie al cerchio del Club.
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="badge-neon-green" style="font-size: 0.8rem; padding: 6px 14px; border-radius: 999px; background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #86efac; display: flex; align-items: center; gap: 6px;">
        <?=dx_icon('check-circle', 'text-neon-green', 14)?>
        <span><?=$stats['satisfaction_rate']?>% Famiglie Riconnesse</span>
      </div>
      <a href="recensioni.php" class="btn-rainbow-outline small" style="border-color: var(--neon-gold); color: #fde68a;">
        <span>Tutte le recensioni</span>
        <?=dx_icon('arrow-right', 'text-neon-gold', 14)?>
      </a>
    </div>
  </div>

  <?php if (!empty($reviewsCards)): ?>
    <div class="dx-reviews-ticker-wrapper">
      <div class="dx-reviews-ticker-track">
        <?php foreach (array_merge($reviewsCards, $reviewsCards) as $rev): ?>
          <article class="dx-review-card">
            <div class="dx-review-card-top">
              <div class="dx-review-stars">
                <?php for ($s = 0; $s < 5; $s++): ?>
                  <?=dx_icon('star', 'text-neon-gold', 14)?>
                <?php endfor; ?>
                <span class="dx-review-score">5.0</span>
              </div>
              <span class="dx-review-streak-badge">
                <?=dx_icon('shield-check', 'text-neon-cyan', 12)?>
                <?=h($rev['sobriety_time'])?>
              </span>
            </div>

            <p class="dx-review-quote">
              <?=h($rev['highlight'])?>
            </p>

            <div class="dx-review-author-box">
              <div class="dx-review-avatar">
                <?=h(mb_substr($rev['author'], 0, 1))?>
              </div>
              <div class="dx-review-meta">
                <h4 class="dx-review-name"><?=h($rev['author'])?></h4>
                <div class="dx-review-sub">
                  <span><?=h($rev['role'])?></span> · <span style="color: #cbd5e1;"><?=h($rev['city'])?></span>
                </div>
              </div>
            </div>

            <div class="dx-review-club-tag">
              <?=dx_icon('map-pin', 'text-neon-gold', 12)?>
              <span><?=h($rev['club'])?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="text-center mt-3">
    <small style="color: #94a3b8; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px;">
      <?=dx_icon('lock', '', 12)?> Testimonianze raccolte con consenso informato. Partecipazione al Club sempre 100% gratuita e anonima.
    </small>
  </div>
</section>

<style>
.dx-reviews-ticker-section {
  position: relative;
  overflow: hidden;
}
.dx-reviews-ticker-wrapper {
  overflow: hidden;
  position: relative;
  padding: 10px 0;
  mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
  -webkit-mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);
}
.dx-reviews-ticker-track {
  display: flex;
  gap: 16px;
  width: max-content;
  animation: dxReviewsScroll 48s linear infinite;
}
.dx-reviews-ticker-track:hover {
  animation-play-state: paused;
}
@keyframes dxReviewsScroll {
  0% { transform: translateX(0); }
  100% { transform: translateX(calc(-50% - 8px)); }
}
.dx-review-card {
  width: 320px;
  flex-shrink: 0;
  background: rgba(12, 16, 28, 0.92);
  border: 1px solid rgba(224, 169, 109, 0.28);
  border-radius: var(--dx-radius-md, 14px);
  padding: 18px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}
.dx-review-card:hover {
  transform: translateY(-4px);
  border-color: var(--neon-gold, #fde68a);
  box-shadow: 0 12px 30px rgba(224, 169, 109, 0.25);
}
.dx-review-card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.dx-review-stars {
  display: flex;
  align-items: center;
  gap: 3px;
}
.dx-review-score {
  font-size: 0.76rem;
  font-weight: 700;
  color: #fde68a;
  margin-left: 4px;
}
.dx-review-streak-badge {
  font-size: 0.72rem;
  color: #67e8f9;
  background: rgba(6, 182, 212, 0.12);
  border: 1px solid rgba(6, 182, 212, 0.3);
  padding: 2px 8px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.dx-review-quote {
  font-size: 0.92rem;
  line-height: 1.5;
  color: #ffffff;
  font-weight: 500;
  margin-bottom: 14px;
  flex-grow: 1;
}
.dx-review-author-box {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.dx-review-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--neon-gold, #fde68a), var(--neon-cyan, #06b6d4));
  color: #0b0f19;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.95rem;
  flex-shrink: 0;
}
.dx-review-meta {
  overflow: hidden;
}
.dx-review-name {
  font-size: 0.88rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
.dx-review-sub {
  font-size: 0.76rem;
  color: #94a3b8;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
.dx-review-club-tag {
  font-size: 0.74rem;
  color: #fde68a;
  background: rgba(253, 230, 138, 0.08);
  border-radius: 6px;
  padding: 4px 8px;
  display: flex;
  align-items: center;
  gap: 5px;
}
</style>
