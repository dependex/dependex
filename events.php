<?php 
require_once 'bootstrap.php';
$u = require_login();

$currentType = strtoupper(trim($_GET['type'] ?? 'ALL'));
$allowedTypes = ['ALL', 'INTERCLUB', 'SAT', 'WEBINAR', 'FORMAZIONE', 'CONGRESSO', 'LIFESTYLE'];
if (!in_array($currentType, $allowedTypes, true)) {
    $currentType = 'ALL';
}

// Automatically purge expired events and sync active web events
$events = EventSyncService::syncAndGetActiveEvents($currentType);

$pageTitle = 'Eventi & Calendario Rete · DEPENDEX';
$metaDesc = 'Calendario vivo con gli eventi aggiornati in tempo reale dal web. Partecipa agli incontri e guadagna DRX.';
require '_header.php';

function formatEventDateLogged(string $datetimeStr): array {
    $dt = new DateTime($datetimeStr, new DateTimeZone('Europe/Rome'));
    $now = new DateTime('now', new DateTimeZone('Europe/Rome'));
    $diff = $now->diff($dt);
    
    $days = $diff->days;
    $isToday = $dt->format('Y-m-d') === $now->format('Y-m-d');
    
    if ($isToday) {
        $countdown = 'Oggi alle ' . $dt->format('H:i');
    } elseif ($days === 1) {
        $countdown = 'Domani alle ' . $dt->format('H:i');
    } else {
        $countdown = 'Tra ' . $days . ' giorni';
    }

    $months = [
        1 => 'Gen', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mag', 6 => 'Giu',
        7 => 'Lug', 8 => 'Ago', 9 => 'Set', 10 => 'Ott', 11 => 'Nov', 12 => 'Dic'
    ];
    $day = $dt->format('d');
    $month = $months[(int)$dt->format('n')];
    $time = $dt->format('H:i');

    return [
        'day' => $day,
        'month' => $month,
        'time' => $time,
        'countdown' => $countdown
    ];
}
?>

<section class="section-head py-4">
  <div>
    <div class="gold-glow-badge mb-2">
      <?=dx_icon('activity', '', 14)?>
      <span>CALENDARIO COMUNITÀ VIVO · ZERO EVENTI SCADUTI</span>
    </div>
    <h1 style="font-family: var(--font-serif); color: #FFFFFF; font-size: clamp(1.8rem, 3.5vw, 2.6rem); margin-top: 6px;">
      Vivi la Community · I Miei Eventi
    </h1>
    <p style="color: #a1a1aa; max-width: 620px;">
      Interclub, Scuole Alcolologiche Territoriali (SAT), corsi di sensibilizzazione ed eventi di lifestyle. Gli appuntamenti passati vengono eliminati automaticamente dal sistema.
    </p>
  </div>
  <?php if(is_admin($u['sic_id'])):?>
    <a class="btn primary small" href="event-builder.php" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">
      <?=dx_icon('sparkles', '', 14)?> <span style="margin-left: 4px;">Crea Evento</span>
    </a>
  <?php endif;?>
</section>

<!-- CATEGORY FILTERS -->
<nav class="event-filter-bar mb-4" style="display: flex; gap: 8px; flex-wrap: wrap;">
  <a href="?type=ALL" class="btn small <?=$currentType==='ALL'?'primary':''?>" style="<?=$currentType==='ALL'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Tutti (<?=count($events)?>)
  </a>
  <a href="?type=INTERCLUB" class="btn small <?=$currentType==='INTERCLUB'?'primary':''?>" style="<?=$currentType==='INTERCLUB'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Interclub
  </a>
  <a href="?type=SAT" class="btn small <?=$currentType==='SAT'?'primary':''?>" style="<?=$currentType==='SAT'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Moduli SAT
  </a>
  <a href="?type=FORMAZIONE" class="btn small <?=$currentType==='FORMAZIONE'?'primary':''?>" style="<?=$currentType==='FORMAZIONE'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Formazione
  </a>
  <a href="?type=WEBINAR" class="btn small <?=$currentType==='WEBINAR'?'primary':''?>" style="<?=$currentType==='WEBINAR'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Webinar
  </a>
</nav>

<div class="event-list my-4" style="display: grid; gap: 18px;">
  <?php if(empty($events)): ?>
    <div class="lux-metallic-card p-5 text-center" style="border: 1px dashed rgba(212,175,55,0.3);">
      <p style="color: #a1a1aa; margin: 0;">Nessun evento futuro in questa categoria. Torna a visitare il calendario tra poche ore.</p>
    </div>
  <?php else: ?>
    <?php foreach($events as $e):
      $dateInfo = formatEventDateLogged($e['starts_at']);
    ?>
      <article class="lux-metallic-card p-4" style="display: flex; gap: 20px; align-items: stretch; border: 1px solid rgba(212,175,55,0.25);">
        <!-- Date Block Left -->
        <div style="width: 90px; min-width: 90px; background: rgba(212,175,55,0.06); border: 1px solid rgba(212,175,55,0.25); border-radius: 16px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 10px;">
          <span style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.1em; color: #D4AF37; text-transform: uppercase;"><?=h($dateInfo['month'])?></span>
          <span style="font-size: 2rem; font-weight: 900; color: #FFFFFF; line-height: 1; margin: 2px 0;"><?=h($dateInfo['day'])?></span>
          <span style="font-size: 0.72rem; color: #a1a1aa;"><?=h($dateInfo['time'])?></span>
        </div>

        <!-- Content -->
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 4px;">
              <span class="dx-ticker-badge"><?=h($e['type'])?></span>
              <span style="font-size: 0.75rem; font-weight: 700; color: #D4AF37; background: rgba(212,175,55,0.1); padding: 2px 8px; border-radius: 999px;">
                <?=dx_icon('clock', '', 12)?> <?=h($dateInfo['countdown'])?>
              </span>
            </div>
            <h3 style="margin: 0.2rem 0 0.4rem; color: #FFFFFF; font-family: var(--font-serif); font-size: 1.25rem;"><?=h($e['title'])?></h3>
            <p style="color: #d1d5db; font-size: 0.9rem; line-height: 1.55; margin-bottom: 8px;"><?=h($e['description'])?></p>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 10px; font-size: 0.82rem;">
            <div style="display: flex; gap: 14px; color: #a1a1aa; align-items: center;">
              <span><?=dx_icon('map-pin', '', 14)?> <b style="color: #FFFFFF;"><?=h($e['venue'])?></b></span>
              <span><?=dx_icon('users', '', 14)?> <?=h((string)$e['registrations'])?> iscritti</span>
              <span style="color: #D4AF37;"><?=dx_icon('award', '', 14)?> +<?=h((string)$e['drx_reward'])?> DRX</span>
            </div>

            <div style="display: flex; gap: 8px;">
              <form method="post" action="action.php" style="display: inline;">
                <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
                <input type="hidden" name="action" value="event_register">
                <input type="hidden" name="event_sic_id" value="<?=h($e['sic_id'])?>">
                <input type="hidden" name="return" value="events.php">
                <button class="btn primary small" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">
                  <?=dx_icon('check-circle', '', 14)?> Partecipa
                </button>
              </form>
              <a class="btn small" href="event-detail.php?event=<?=urlencode($e['sic_id'])?>" style="border-color: rgba(212,175,55,0.4); color: #FFFFFF;">
                Dettagli
              </a>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach;?>
  <?php endif; ?>
</div>

<?php require '_footer.php';?>