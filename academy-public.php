<?php 
require_once 'bootstrap.php';
$u = current_user();
$courses = db()->query("SELECT * FROM academy_courses WHERE status='ACTIVE' ORDER BY category,title")->fetchAll();
$pageTitle = 'Academy · Formazione Continua & Servitori-Insegnanti';
$metaDesc = 'Percorsi accreditati per famiglie, membri e Servitori-Insegnanti. Impara l’approccio ecologico-sociale Hudolin, applica gli strumenti e condividi.';
require '_header.php';
?>

<section class="section-head py-4">
  <div>
    <div class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span class="text-rainbow">ACADEMY & FORMAZIONE CONTINUA</span>
    </div>
    <h1 style="font-family: var(--font-serif); color: #FFFFFF; font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-top: 6px;">
      Dalla Consapevolezza all'<span class="text-rainbow">Abilitazione Sovrana</span>
    </h1>
    <p style="color: #cbd5e1; max-width: 640px;">
      Scopri i percorsi di crescita e i moduli formativi accreditati. Raggiungi la sovranità personale e formati per diventare Servitore-Insegnante di Club.
    </p>
  </div>
  <?php if(!$u):?>
    <a class="btn-rainbow-neon" href="register.php">
      <?=dx_icon('sparkles', '', 16)?>
      <span style="margin-left: 6px;">Registrati per Iniziare</span>
    </a>
  <?php endif;?>
</section>

<!-- Banner Panoramico Arcobaleno -->
<div class="rainbow-panorama-banner mb-4">
  <img src="assets/img/rainbow-portals.jpg" alt="Academy dei 7 Portali Dependex" style="max-height: 320px; object-fit: cover;">
</div>

<div class="course-list my-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
  <?php 
  $neonColors = ['card-neon-gold', 'card-neon-green', 'card-neon-cyan', 'card-neon-violet', 'card-neon-orange', 'card-neon-red'];
  $idx = 0;
  foreach($courses as $c):
    $neonClass = $neonColors[$idx % count($neonColors)];
    $idx++;
  ?>
    <article class="course <?=$neonClass?> p-4" style="display: flex; flex-direction: column; justify-content: space-between; background: rgba(12, 16, 26, 0.85); border-radius: 18px; border: 1px solid rgba(255,255,255,0.08);">
      <div>
        <span class="dx-ticker-badge" style="margin-bottom: 8px;"><?=h($c['category'])?></span>
        <h3 style="margin: 0.4rem 0 0.8rem; color: #FFFFFF; font-family: var(--font-serif); font-size: 1.25rem;"><?=h($c['title'])?></h3>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin-bottom: 1.2rem;"><?=h($c['description'])?></p>
      </div>
      <div>
        <div class="course-foot py-2 mb-3" style="display: flex; justify-content: space-between; font-size: 0.84rem; color: #cbd5e1; border-top: 1px solid rgba(255,255,255,0.08);">
          <span style="color: var(--neon-gold); font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
            <?=dx_icon('award', '', 14)?> <?=h((string)$c['drx_reward'])?> DRX al completamento
          </span>
          <span style="color: #cbd5e1; font-weight: 600;">Rank <?=h($c['rank_required'])?></span>
        </div>
        <?php if($u):?>
          <a class="btn-rainbow-outline small" href="academy.php" style="width: 100%; text-align: center; font-weight: 800;">
            <?=dx_icon('book-open', '', 14)?> <span style="margin-left: 4px;">Apri Academy</span>
          </a>
        <?php else:?>
          <a class="btn-rainbow-outline small" href="register.php" style="width: 100%; text-align: center; font-weight: 800;">
            <?=dx_icon('sparkles', '', 14)?> <span style="margin-left: 4px;">Inizia il Percorso</span>
          </a>
        <?php endif;?>
      </div>
    </article>
  <?php endforeach;?>
</div>

<?php require '_footer.php';?>