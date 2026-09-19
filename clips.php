<?php
/**
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 * CLIPS MOTIVAZIONALI (9:16 SHORTS & REELS)
 * 
 * Serie di micro-video motivazionali da 8-10 secondi sui temi cardine:
 * - Accoglienza senza giudizio ("Non devi avere già le risposte")
 * - Comunità e Club Hudolin ("Nessuno si salva da solo")
 * - Respiro somatico e calma ("Fermati e Respira")
 * - Resilienza e navigazione ("Navigare le Onde della Vita")
 * - Sobrietà e libertà interiore ("Un giorno alla volta")
 */

require_once __DIR__ . '/bootstrap.php';

$pageTitle = "Video Motivazionali · Clip e Shorts di Comunità";
$metaDesc = "Micro-video da 8-10 secondi per ritrovare calma, coraggio e connessione umana. Condividili o guardali per un minuto di serenità.";

$clips = [
    [
        'id' => 'clip1_non_devi_sapere_tutto',
        'title' => 'Non devi avere già le risposte',
        'duration' => '10s',
        'theme' => 'Accoglienza & Sollievo',
        'mp4' => 'assets/clips/clip1_non_devi_sapere_tutto.mp4',
        'poster' => 'assets/clips/clip1_bg.jpg',
        'caption' => 'Non devi sapere già tutto. Non devi avere già le risposte. Fai solo il primo passo: il cerchio è qui per te.',
        'cta_link' => 'parla-con-noi.php',
        'cta_text' => 'Parla con Noi'
    ],
    [
        'id' => 'clip2_nessuno_e_solo',
        'title' => 'Nessuno si salva da solo',
        'duration' => '8s',
        'theme' => 'Comunità & Club CAT',
        'mp4' => 'assets/clips/clip2_nessuno_e_solo.mp4',
        'poster' => 'assets/clips/clip2_bg.jpg',
        'caption' => 'La solitudine è un\'illusione. Nel cerchio non ci sono etichette, solo persone che camminano insieme.',
        'cta_link' => 'world-club-explorer.php',
        'cta_text' => 'Trova un Club'
    ],
    [
        'id' => 'clip3_fermati_e_respira',
        'title' => 'Fermati e Respira',
        'duration' => '10s',
        'theme' => 'Presenza & Respiro Somatico',
        'mp4' => 'assets/clips/clip3_fermati_e_respira.mp4',
        'poster' => 'assets/clips/clip3_bg.jpg',
        'caption' => 'Ferma tutto per un minuto. Espira l\'ansia, inspira la calma. Sei qui, sei al sicuro. Riparti dal tuo respiro.',
        'cta_link' => 'playground.php#breath-tool',
        'cta_text' => 'Fai 60s di Respiro'
    ],
    [
        'id' => 'clip4_navigare_le_onde',
        'title' => 'Navigare le Onde della Vita',
        'duration' => '9s',
        'theme' => 'Resilienza & Vita',
        'mp4' => 'assets/clips/clip4_navigare_le_onde.mp4',
        'poster' => 'assets/clips/clip4_bg.jpg',
        'caption' => 'Non puoi fermare le onde della vita. Ma puoi imparare a navigarle. Osserva, scegli, rinasci.',
        'cta_link' => 'playground.php',
        'cta_text' => 'Esplora Playground'
    ],
    [
        'id' => 'clip5_rinascita_quotidiana',
        'title' => 'Un giorno alla volta',
        'duration' => '8s',
        'theme' => 'Sobrietà & Libertà',
        'mp4' => 'assets/clips/clip5_rinascita_quotidiana.mp4',
        'poster' => 'assets/clips/clip5_bg.jpg',
        'caption' => 'Un giorno alla volta. La tua storia ha un valore immenso. Oltre ogni caduta c\'è la tua libertà.',
        'cta_link' => 'storie.php',
        'cta_text' => 'Leggi le Storie'
    ]
];

include __DIR__ . '/_header.php';
?>

<main id="mainContent" class="clips-master-wrap">
  <div class="container-mobile">
    <div class="clips-hero-header">
      <div class="badge-human"><span class="dot"></span> VIDEO SHORTS & MOTIVAZIONE 9:16</div>
      <h1 class="clips-main-title">8-10 Secondi per Ricordare Chi Sei</h1>
      <p class="clips-main-subtitle">
        Micro-video verticali ad alta definizione pronti per essere vissuti, riprodotti o condivisi su WhatsApp, Telegram e Stories.
      </p>
    </div>

    <!-- REEL CAROUSEL SELECTOR -->
    <div class="clips-reel-nav">
      <?php foreach ($clips as $idx => $c): ?>
        <button type="button" class="reel-nav-btn <?=$idx === 0 ? 'active' : ''?>" onclick="switchClip(<?=$idx?>)" id="btn-tab-<?=$idx?>">
          <span class="tab-num"><?=$c['duration']?></span>
          <span class="tab-title"><?=h($c['title'])?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- MAIN PLAYER STAGE (9:16 VERTICAL CINEMA) -->
    <div class="clips-stage-container">
      <div class="clip-video-card" id="activeVideoCard">
        <div class="clip-viewport-frame">
          <video id="mainReelPlayer" 
                 src="<?=h($clips[0]['mp4'])?>" 
                 poster="<?=h($clips[0]['poster'])?>" 
                 playsinline 
                 loop 
                 preload="auto"
                 controls>
            Il tuo browser non supporta i video HTML5.
          </video>
        </div>

        <div class="clip-info-overlay">
          <div class="clip-meta-top">
            <span class="clip-theme-pill" id="clipThemePill"><?=h($clips[0]['theme'])?></span>
            <span class="clip-dur-pill" id="clipDurPill"><?=h($clips[0]['duration'])?></span>
          </div>
          <h2 class="clip-title-display" id="clipTitleDisplay"><?=h($clips[0]['title'])?></h2>
          <p class="clip-caption-display" id="clipCaptionDisplay"><?=h($clips[0]['caption'])?></p>

          <div class="clip-actions-bar">
            <a href="<?=h($clips[0]['cta_link'])?>" id="clipCtaBtn" class="btn-clip-primary">
              <span id="clipCtaText"><?=h($clips[0]['cta_text'])?></span> <?=dx_icon('arrow-right', '', 14)?>
            </a>
            <a href="<?=h($clips[0]['mp4'])?>" id="clipDownloadBtn" class="btn-clip-secondary" download title="Scarica video MP4">
              <?=dx_icon('download', '', 16)?> Scarica MP4
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ALL CLIPS GRID (GALLERIA COMPLETA) -->
    <div class="clips-gallery-section">
      <div class="gallery-header">
        <h2>Tutte le 5 Clip della Serie</h2>
        <p>Scegli una clip per avviarla subito o salvarla per condividerla.</p>
      </div>

      <div class="clips-cards-grid">
        <?php foreach ($clips as $idx => $c): ?>
          <div class="clip-thumb-card" onclick="switchClip(<?=$idx?>)">
            <div class="clip-thumb-visual" style="background-image: url('<?=h($c['poster'])?>');">
              <span class="thumb-play-icon"><?=dx_icon('play', '', 24)?></span>
              <span class="thumb-dur-badge"><?=h($c['duration'])?></span>
            </div>
            <div class="clip-thumb-info">
              <span class="thumb-theme"><?=h($c['theme'])?></span>
              <h3><?=h($c['title'])?></h3>
              <p><?=h($c['caption'])?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</main>

<style>
/* CSS CLIPS MOTIVAZIONALI (MOBILE-FIRST 9:16) */
.clips-master-wrap {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
  box-sizing: border-box;
  padding: 16px 12px 60px;
  background: radial-gradient(circle at 50% 0%, rgba(18, 28, 46, 0.5) 0%, rgba(7, 10, 18, 0.98) 80%);
  color: #f1f5f9;
}

.container-mobile {
  width: 100%;
  max-width: 960px;
  margin: 0 auto;
  box-sizing: border-box;
}

.clips-hero-header {
  text-align: center;
  margin-bottom: 24px;
}

.clips-main-title {
  font-size: clamp(1.7rem, 4.5vw, 2.5rem);
  font-weight: 800;
  color: #ffffff;
  margin: 10px 0 8px;
  line-height: 1.2;
}

.clips-main-subtitle {
  font-size: clamp(0.92rem, 2.2vw, 1.1rem);
  color: #94a3b8;
  max-width: 620px;
  margin: 0 auto;
  line-height: 1.45;
}

/* REEL SELECTOR TABS */
.clips-reel-nav {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  padding: 8px 4px 16px;
  margin-bottom: 16px;
  scrollbar-width: none;
}
.clips-reel-nav::-webkit-scrollbar {
  display: none;
}

.reel-nav-btn {
  background: rgba(20, 28, 44, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 999px;
  padding: 8px 16px;
  color: #cbd5e1;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 40px;
  transition: all 0.2s ease;
}

.reel-nav-btn:hover, .reel-nav-btn.active {
  background: rgba(0, 240, 255, 0.15);
  border-color: #00f0ff;
  color: #00f0ff;
  box-shadow: 0 0 12px rgba(0, 240, 255, 0.2);
}

.tab-num {
  font-size: 0.7rem;
  background: rgba(255, 255, 255, 0.12);
  padding: 2px 6px;
  border-radius: 4px;
}

/* MAIN STAGE (VERTICAL 9:16) */
.clips-stage-container {
  display: flex;
  justify-content: center;
  margin-bottom: 40px;
}

.clip-video-card {
  width: 100%;
  max-width: 420px;
  background: rgba(14, 20, 32, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
}

.clip-viewport-frame {
  position: relative;
  width: 100%;
  aspect-ratio: 9 / 16;
  background: #000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clip-viewport-frame video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.clip-info-overlay {
  padding: 20px;
  text-align: left;
}

.clip-meta-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.clip-theme-pill {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #00f0ff;
  background: rgba(0, 240, 255, 0.1);
  padding: 3px 8px;
  border-radius: 6px;
}

.clip-dur-pill {
  font-size: 0.72rem;
  color: #ffd700;
  font-weight: 700;
}

.clip-title-display {
  margin: 4px 0 8px;
  font-size: 1.25rem;
  color: #fff;
  font-weight: 800;
}

.clip-caption-display {
  font-size: 0.88rem;
  color: #94a3b8;
  line-height: 1.45;
  margin: 0 0 18px;
}

.clip-actions-bar {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-clip-primary {
  flex: 1;
  min-width: 140px;
  background: linear-gradient(135deg, #00f0ff, #0088ff);
  color: #070a12;
  font-weight: 750;
  text-decoration: none;
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-height: 44px;
}

.btn-clip-secondary {
  background: rgba(255, 255, 255, 0.08);
  color: #e2e8f0;
  border: 1px solid rgba(255, 255, 255, 0.15);
  font-weight: 600;
  text-decoration: none;
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-height: 44px;
}

/* GALLERY SECTION */
.clips-gallery-section {
  margin-top: 20px;
  text-align: left;
}

.gallery-header h2 {
  font-size: 1.4rem;
  color: #fff;
  margin: 0 0 6px;
}

.gallery-header p {
  font-size: 0.88rem;
  color: #94a3b8;
  margin: 0 0 18px;
}

.clips-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}

.clip-thumb-card {
  background: rgba(20, 28, 44, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.25s ease;
  display: flex;
  flex-direction: column;
}

.clip-thumb-card:hover {
  border-color: #00f0ff;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.clip-thumb-visual {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.clip-thumb-visual::after {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(7, 10, 18, 0.35);
}

.thumb-play-icon {
  position: relative;
  z-index: 2;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(0, 240, 255, 0.85);
  color: #070a12;
  display: flex;
  align-items: center;
  justify-content: center;
}

.thumb-dur-badge {
  position: absolute;
  bottom: 8px;
  right: 8px;
  z-index: 2;
  background: rgba(0, 0, 0, 0.75);
  color: #ffd700;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
}

.clip-thumb-info {
  padding: 14px;
}

.thumb-theme {
  font-size: 0.68rem;
  color: #00f0ff;
  font-weight: 700;
  text-transform: uppercase;
}

.clip-thumb-info h3 {
  margin: 4px 0 6px;
  font-size: 1.05rem;
  color: #fff;
}

.clip-thumb-info p {
  margin: 0;
  font-size: 0.82rem;
  color: #94a3b8;
  line-height: 1.35;
}
</style>

<script>
const clipsData = <?=json_encode($clips, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)?>;

function switchClip(index) {
  const clip = clipsData[index];
  if (!clip) return;

  // Aggiorna bottoni reel nav
  document.querySelectorAll('.reel-nav-btn').forEach((b, i) => {
    b.classList.toggle('active', i === index);
  });

  // Aggiorna player
  const player = document.getElementById('mainReelPlayer');
  player.src = clip.mp4;
  player.poster = clip.poster;
  player.load();
  player.play().catch(e => {
    // Autoplay policy: se bloccato, l'utente preme play manualmente
  });

  // Aggiorna info
  document.getElementById('clipThemePill').textContent = clip.theme;
  document.getElementById('clipDurPill').textContent = clip.duration;
  document.getElementById('clipTitleDisplay').textContent = clip.title;
  document.getElementById('clipCaptionDisplay').textContent = clip.caption;

  const ctaBtn = document.getElementById('clipCtaBtn');
  ctaBtn.href = clip.cta_link;
  document.getElementById('clipCtaText').textContent = clip.cta_text;

  const dlBtn = document.getElementById('clipDownloadBtn');
  dlBtn.href = clip.mp4;

  // Scroll morbido allo stage se cliccato dalla galleria
  document.getElementById('activeVideoCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
