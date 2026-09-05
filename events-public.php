<?php 
require_once 'bootstrap.php';
$u = current_user();
$events = db()->query("
    SELECT e.*, (SELECT COUNT(*) FROM event_registrations er WHERE er.event_sic_id=e.sic_id AND er.status='REGISTERED') registrations 
    FROM events e 
    WHERE status='PUBLISHED' 
    ORDER BY starts_at
")->fetchAll();
$pageTitle = 'Eventi, Moduli SAT & Formazione · DEPENDEX';
$metaDesc = 'Calendario completo di Interclub territoriali, Scuole Alcolologiche Territoriali (SAT), corsi di sensibilizzazione ed eventi.';
require '_header.php';
?>

<section class="section-head py-4">
  <div>
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('calendar', '', 14)?>
      <span>CALENDARIO ATTIVITÀ & FORMAZIONE</span>
    </div>
    <h1 style="font-family: var(--font-serif); color: #FFFFFF; font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-top: 6px;">
      Vivi la Community dal Vivo
    </h1>
    <p style="color: #a1a1aa; max-width: 620px;">
      Club settimanali, Interclub regionali, Moduli SAT, Corsi di Sensibilizzazione all'Approccio Ecologico-Sociale e tavole rotonde territoriali.
    </p>
  </div>
  <?php if(!$u):?>
    <a class="btn primary" href="register.php" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">
      <?=dx_icon('sparkles', '', 16)?>
      <span style="margin-left: 6px;">Partecipa agli Eventi</span>
    </a>
  <?php endif;?>
</section>

<div class="event-list my-4">
  <?php if(empty($events)): ?>
    <div class="lux-metallic-card p-4 text-center">
      <p style="color: #a1a1aa; margin: 0;">Nessun evento in programma al momento. Consulta il ticker news in home per gli aggiornamenti settimanali.</p>
    </div>
  <?php else: ?>
    <?php foreach($events as $e):?>
      <article class="event-card lux-metallic-card p-4" style="display: flex; gap: 20px; align-items: center; border: 1px solid rgba(212,175,55,0.25);">
        <div class="event-icon" style="color: #D4AF37; flex-shrink: 0; background: rgba(212,175,55,0.08); border: 1px solid rgba(212,175,55,0.2); border-radius: 16px; width: 64px; height: 64px; display: grid; place-items: center;">
          <?=match($e['type']){
            'WEBINAR' => dx_icon('sparkles', '', 28),
            'VIAGGIO' => dx_icon('compass', '', 28),
            'INTERCLUB' => dx_icon('users', '', 28),
            'VOLONTARIATO' => dx_icon('heart-handshake', '', 28),
            default => dx_icon('calendar', '', 28)
          }?>
        </div>
        <div style="flex: 1;">
          <span class="dx-ticker-badge" style="margin-bottom: 6px;"><?=h($e['type'])?></span>
          <h3 style="margin: 0.3rem 0; color: #FFFFFF; font-family: var(--font-serif); font-size: 1.25rem;"><?=h($e['title'])?></h3>
          <p style="color: #a1a1aa; margin-bottom: 8px; font-size: 0.92rem;"><?=h($e['venue'])?> · <?=h($e['starts_at'])?></p>
          <div style="display: flex; gap: 16px; align-items: center; font-size: 0.82rem; color: #71717a;">
            <span><?=dx_icon('users', '', 14)?> <?=h((string)$e['registrations'])?> iscritti</span>
            <span><?=dx_icon('award', '', 14)?> <?=h((string)$e['drx_reward'])?> DRX</span>
          </div>
        </div>
        <div>
          <a class="btn" href="event-detail.php?event=<?=urlencode($e['sic_id'])?>" style="border-color: rgba(212,175,55,0.4); color: #FFFFFF; font-weight: 700;">
            Dettagli
          </a>
        </div>
      </article>
    <?php endforeach;?>
  <?php endif; ?>
</div>

<?php require '_footer.php';?>