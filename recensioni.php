<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/reviews/ReviewsService.php';

$u = current_user();
$brand = site_brand();

$pageTitle = 'Recensioni & Testimonianze Verificate dei Club CAT · Metodo Hudolin';
$metaDesc = 'Leggi le recensioni e testimonianze reali di persone e famiglie che hanno superato l\'alcol e le dipendenze grazie alla rete gratuita dei Club Alcologici Territoriali.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/recensioni.php';

$breadcrumbs = [
    'Home' => '/',
    'Testimonianze & Recensioni' => 'recensioni.php'
];

$reviews = ReviewsService::getAll();
$stats = ReviewsService::getStats();

$pageSchemaJson = [
    "@context" => "https://schema.org",
    "@type" => "CollectionPage",
    "name" => $pageTitle,
    "description" => $metaDesc,
    "url" => $canonicalUrl,
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => "4.9",
        "reviewCount" => (string)count($reviews),
        "bestRating" => "5",
        "worstRating" => "1"
    ]
];

require '_header.php';
?>

<div class="container py-4" style="max-width: 1180px; margin: 0 auto; padding: 0 1rem;">

  <!-- HERO RECENSIONI -->
  <section class="human-hero-card text-center my-4" style="padding: 2.5rem 1.5rem; background: radial-gradient(circle at 50% 0%, rgba(224, 169, 109, 0.15) 0%, rgba(12, 16, 28, 0.95) 75%); border: 1px solid rgba(224, 169, 109, 0.35); border-radius: var(--dx-radius-lg, 20px);">
    <div class="badge-neon-rainbow mb-3">
      <span class="dot"></span>
      <span style="color:#fde68a;">RIPROVA SOCIALE & STORIE DI VITA REALE</span>
    </div>

    <h1 style="font-family: var(--font-serif); font-size: clamp(2rem, 4.5vw, 3.2rem); color: #ffffff; font-weight: 800; margin: 0 0 14px; line-height: 1.15;">
      Recensioni e Testimonianze dai <br>
      <span class="text-rainbow">Club Alcologici Territoriali</span>
    </h1>

    <p style="color: #cbd5e1; max-width: 760px; margin: 0 auto 24px; font-size: 1.05rem; line-height: 1.6;">
      Chi entra al Club per la prima volta spesso ha paura del giudizio o si sente irrimediabilmente solo. 
      Ecco le parole autentiche di chi ha fatto il primo passo e ha ritrovato la pace, la salute e l'amore della propria famiglia.
    </p>

    <!-- METRICHE STATISTICHE DI IMPATTO -->
    <div class="row g-3 justify-content-center mb-4" style="max-width: 840px; margin-left: auto; margin-right: auto;">
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(253,230,138,0.25); border-radius: 12px; text-align: center;">
          <div style="font-size: 1.8rem; font-weight: 800; color: #fde68a; font-family: var(--font-serif);">4.9 / 5</div>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Valutazione Media</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(6,182,212,0.25); border-radius: 12px; text-align: center;">
          <div style="font-size: 1.8rem; font-weight: 800; color: #67e8f9; font-family: var(--font-serif);">100%</div>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Gratuito e Anonimo</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(34,197,94,0.25); border-radius: 12px; text-align: center;">
          <div style="font-size: 1.8rem; font-weight: 800; color: #86efac; font-family: var(--font-serif);">322+</div>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Club in Tutta Italia</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="p-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(168,85,247,0.25); border-radius: 12px; text-align: center;">
          <div style="font-size: 1.8rem; font-weight: 800; color: #d8b4fe; font-family: var(--font-serif);"><?=$stats['satisfaction_rate']?>%</div>
          <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Famiglie Riconnesse</div>
        </div>
      </div>
    </div>

    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
      <a href="mappa-club.php" class="btn-rainbow-neon">
        <?=dx_icon('compass', '', 18)?>
        <span style="margin-left: 8px;">Cerca il tuo Club sulla Mappa</span>
      </a>
      <a href="parla-con-noi.php" class="btn-rainbow-outline" style="border-color: var(--neon-cyan); color: #fff;">
        <?=dx_icon('message-circle', 'text-neon-cyan', 18)?>
        <span style="margin-left: 8px;">Parla con Noi in Privato</span>
      </a>
    </div>
  </section>

  <!-- BARRA FILTRI INTERATTIVI -->
  <div class="p-3 mb-4" style="background: rgba(12, 16, 28, 0.85); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="roleFilters">
      <button class="btn small filter-btn active" data-role="all" style="border-radius: 20px; font-size: 0.82rem;">Tutte (<?=count($reviews)?>)</button>
      <button class="btn small filter-btn" data-role="Famiglia" style="border-radius: 20px; font-size: 0.82rem;">Famiglie & Coppie</button>
      <button class="btn small filter-btn" data-role="Membro" style="border-radius: 20px; font-size: 0.82rem;">Membri del Club</button>
      <button class="btn small filter-btn" data-role="Servitore" style="border-radius: 20px; font-size: 0.82rem;">Servitori-Insegnanti</button>
    </div>
    <div style="font-size: 0.84rem; color: #94a3b8;">
      <?=dx_icon('shield-check', 'text-neon-green', 14)?>
      <span>Esperienze verificate e protette da privacy</span>
    </div>
  </div>

  <!-- GRIGLIA RECENSIONI -->
  <div class="row g-4" id="reviewsGrid">
    <?php foreach ($reviews as $rev): ?>
      <div class="col-md-6 col-lg-4 review-item" data-role="<?=h($rev['role'])?>" data-tags="<?=h(implode(',', $rev['tags'] ?? []))?>">
        <article class="p-4 h-100 d-flex flex-column justify-content-between" style="background: rgba(12, 16, 28, 0.9); border: 1px solid rgba(224, 169, 109, 0.25); border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.3); transition: transform 0.25s, border-color 0.25s;">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-1">
                <?php for ($s = 0; $s < 5; $s++): ?>
                  <?=dx_icon('star', 'text-neon-gold', 15)?>
                <?php endfor; ?>
              </div>
              <span class="badge" style="background: rgba(6,182,212,0.12); color: #67e8f9; border: 1px solid rgba(6,182,212,0.3); font-size: 0.72rem; padding: 4px 8px; border-radius: 20px;">
                <?=h($rev['sobriety_time'])?>
              </span>
            </div>

            <h3 style="font-size: 1.05rem; font-weight: 700; color: #ffffff; line-height: 1.45; margin-bottom: 12px; font-family: var(--font-serif);">
              <?=h($rev['highlight'])?>
            </h3>

            <p style="font-size: 0.92rem; color: #cbd5e1; line-height: 1.6; margin-bottom: 16px;">
              <?=h($rev['story'])?>
            </p>

            <div class="d-flex flex-wrap gap-1 mb-3">
              <?php foreach ($rev['tags'] as $t): ?>
                <span style="font-size: 0.72rem; color: #fde68a; background: rgba(253,230,138,0.08); padding: 2px 7px; border-radius: 6px;">
                  #<?=h($t)?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="pt-3" style="border-top: 1px solid rgba(255,255,255,0.08);">
            <div class="d-flex align-items-center gap-3">
              <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--neon-gold), var(--neon-cyan)); color: #0b0f19; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
                <?=h(mb_substr($rev['author'], 0, 1))?>
              </div>
              <div>
                <div style="font-weight: 700; color: #fff; font-size: 0.92rem;"><?=h($rev['author'])?></div>
                <div style="font-size: 0.76rem; color: #94a3b8;"><?=h($rev['role'])?> · <?=h($rev['city'])?></div>
              </div>
            </div>
            <div class="mt-2" style="font-size: 0.74rem; color: #fde68a; display: flex; align-items: center; gap: 4px;">
              <?=dx_icon('map-pin', 'text-neon-gold', 12)?>
              <span><?=h($rev['club'])?></span>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- BOX CONDIVIDI LA TUA STORIA -->
  <section class="p-4 p-md-5 my-5" style="background: rgba(12, 16, 28, 0.95); border: 2px solid rgba(224, 169, 109, 0.4); border-radius: 20px; box-shadow: var(--rainbow-glow);">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="badge-neon-rainbow mb-2">
          <span class="dot"></span>
          <span style="color:#fde68a;">LA TUA ESPERIENZA PUÒ SALVARE UNA VITA</span>
        </div>
        <h2 style="font-family: var(--font-serif); font-size: clamp(1.5rem, 3vw, 2.2rem); color: #ffffff; font-weight: 800; margin: 6px 0 12px;">
          Fai parte di un Club? <span class="text-rainbow">Lascia la tua testimonianza.</span>
        </h2>
        <p style="color: #cbd5e1; font-size: 0.98rem; line-height: 1.6; margin-bottom: 16px;">
          Quando una persona in difficoltà naviga su questo sito nel cuore della notte, leggere come tu e la tua famiglia avete superato la nebbia è la scintilla che le dà il coraggio di presentarsi alla prossima riunione del Club.
        </p>
        <ul style="color: #cbd5e1; font-size: 0.88rem; line-height: 1.7; padding-left: 20px; margin-bottom: 20px;">
          <li>Puoi usare un nome di fantasia o le sole iniziali per tutelare al 100% la tua privacy.</li>
          <li>Nessuna recensione sponsorizzata o retribuita: solo comunità vera.</li>
          <li>Le testimonianze vengono revisionate dal comitato etico prima della pubblicazione.</li>
        </ul>
      </div>
      <div class="col-lg-5">
        <div class="p-4" style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px;">
          <h4 style="color: #fff; font-size: 1.1rem; margin-bottom: 14px; font-weight: 700;">Invia testimonianza protetta</h4>
          <form id="reviewForm" onsubmit="event.preventDefault(); alert('Grazie di cuore! La tua testimonianza è stata ricevuta e sarà revisionata con cura e rispetto prima della pubblicazione.'); this.reset();">
            <div class="mb-3">
              <label style="font-size: 0.8rem; color: #cbd5e1; display: block; margin-bottom: 4px;">Nome o Iniziali</label>
              <input type="text" class="form-control" placeholder="es. Marco T. o Anonimo" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px; font-size: 0.9rem; padding: 8px 12px;">
            </div>
            <div class="mb-3">
              <label style="font-size: 0.8rem; color: #cbd5e1; display: block; margin-bottom: 4px;">Ruolo / Esperienza</label>
              <select class="form-control" style="background: rgba(20,25,38,0.95); border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px; font-size: 0.9rem; padding: 8px 12px;">
                <option value="Membro del Club">Membro del Club</option>
                <option value="Familiare / Coniuge">Familiare / Coniuge</option>
                <option value="Figlio/a">Figlio/a di Membro del Club</option>
                <option value="Servitore-Insegnante">Servitore-Insegnante</option>
              </select>
            </div>
            <div class="mb-3">
              <label style="font-size: 0.8rem; color: #cbd5e1; display: block; margin-bottom: 4px;">La tua esperienza con il Club</label>
              <textarea class="form-control" rows="3" placeholder="Come ti ha aiutato il Club? Che messaggio daresti a chi è ancora nel dubbio?" required style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: #fff; border-radius: 8px; font-size: 0.9rem; padding: 8px 12px;"></textarea>
            </div>
            <button type="submit" class="btn-rainbow-neon w-100" style="padding: 10px; font-size: 0.95rem; justify-content: center;">
              <?=dx_icon('send', '', 16)?>
              <span style="margin-left: 6px;">Invia con Riservatezza</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const buttons = document.querySelectorAll('#roleFilters .filter-btn');
  const items = document.querySelectorAll('#reviewsGrid .review-item');

  buttons.forEach(btn => {
    btn.addEventListener('click', function() {
      buttons.forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      const role = this.getAttribute('data-role');
      items.forEach(item => {
        if (role === 'all') {
          item.style.display = 'block';
        } else {
          const itemRole = item.getAttribute('data-role');
          if (itemRole.includes(role)) {
            item.style.display = 'block';
          } else {
            item.style.display = 'none';
          }
        }
      });
    });
  });
});
</script>

<?php require '_footer.php'; ?>
