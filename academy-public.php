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
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('academic', '', 14)?>
      <span>ACADEMY & FORMAZIONE CONTINUA</span>
    </div>
    <h1 style="font-family: var(--font-serif); color: #FFFFFF; font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-top: 6px;">
      Dalla Consapevolezza all'Abilitazione
    </h1>
    <p style="color: #a1a1aa; max-width: 640px;">
      Scopri i percorsi di crescita e i moduli formativi accreditati. Raggiungi la sovranità personale e formati per diventare Servitore-Insegnante di Club.
    </p>
  </div>
  <?php if(!$u):?>
    <a class="btn primary" href="register.php" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">
      <?=dx_icon('sparkles', '', 16)?>
      <span style="margin-left: 6px;">Registrati per Iniziare</span>
    </a>
  <?php endif;?>
</section>

<div class="course-list my-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
  <?php foreach($courses as $c):?>
    <article class="course lux-metallic-card p-4" style="display: flex; flex-direction: column; justify-content: space-between; border: 1px solid rgba(212,175,55,0.25);">
      <div>
        <span class="dx-ticker-badge" style="margin-bottom: 8px;"><?=h($c['category'])?></span>
        <h3 style="margin: 0.4rem 0 0.8rem; color: #FFFFFF; font-family: var(--font-serif); font-size: 1.25rem;"><?=h($c['title'])?></h3>
        <p style="color: #a1a1aa; font-size: 0.9rem; line-height: 1.55; margin-bottom: 1.2rem;"><?=h($c['description'])?></p>
      </div>
      <div>
        <div class="course-foot py-2 mb-3" style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #71717a; border-top: 1px solid rgba(255,255,255,0.06);">
          <span style="color: #D4AF37; display: inline-flex; align-items: center; gap: 4px;">
            <?=dx_icon('award', '', 14)?> <?=h((string)$c['drx_reward'])?> DRX al completamento
          </span>
          <span>Rank <?=h($c['rank_required'])?></span>
        </div>
        <?php if($u):?>
          <a class="btn small" href="academy.php" style="width: 100%; text-align: center; border-color: rgba(212,175,55,0.4); color: #FFFFFF; font-weight: 700;">
            <?=dx_icon('book-open', '', 14)?> <span style="margin-left: 4px;">Apri Academy</span>
          </a>
        <?php else:?>
          <a class="btn small" href="register.php" style="width: 100%; text-align: center; border-color: rgba(212,175,55,0.4); color: #FFFFFF; font-weight: 700;">
            <?=dx_icon('sparkles', '', 14)?> <span style="margin-left: 4px;">Inizia il Percorso</span>
          </a>
        <?php endif;?>
      </div>
    </article>
  <?php endforeach;?>
</div>

<?php require '_footer.php';?>