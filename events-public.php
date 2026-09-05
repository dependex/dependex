<?php 
require_once 'bootstrap.php';
$u = current_user();

$currentType = strtoupper(trim($_GET['type'] ?? 'ALL'));
$allowedTypes = ['ALL', 'INTERCLUB', 'SAT', 'WEBINAR', 'FORMAZIONE', 'CONGRESSO', 'LIFESTYLE'];
if (!in_array($currentType, $allowedTypes, true)) {
    $currentType = 'ALL';
}

// Automatically purge expired events and sync active web events
$events = EventSyncService::syncAndGetActiveEvents($currentType);

$pageTitle = 'Eventi Vivi dal Web · AICAT, ARCAT & Moduli SAT · DEPENDEX';
$metaDesc = 'Pagina eventi viva e aggiornata in tempo reale dal web: Interclub territoriali, Scuole Alcolologiche (SAT), Congressi e formazioni. Gli eventi scaduti vengono rimossi automaticamente.';
require '_header.php';

function formatEventDate(string $datetimeStr): array {
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
        'full' => "$day $month " . $dt->format('Y') . " · ore $time",
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
      <span>RETE NAZIONALE VIVA · SINCRONIZZAZIONE WEB H24</span>
    </div>
    <h1 style="font-family: var(--font-serif); color: #FFFFFF; font-size: clamp(1.9rem, 3.8vw, 2.8rem); margin-top: 6px;">
      Eventi, Moduli SAT & Interclub dal Vivo
    </h1>
    <p style="color: #a1a1aa; max-width: 720px; font-size: 1.05rem; line-height: 1.65;">
      Tutti gli appuntamenti della rete AICAT, ARCAT territoriali e dei Club aggregati dal web. 
      Questa è una <strong>pagina eventi viva</strong>: ogni evento concluso viene <em>rimosso in automatico</em>, garantendo date reali e zero link fantasma.
    </p>
  </div>
  <?php if(!$u):?>
    <a class="btn primary" href="register.php" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800; padding: 0 24px;">
      <?=dx_icon('sparkles', '', 16)?>
      <span style="margin-left: 6px;">Crea Account per Iscriverti</span>
    </a>
  <?php endif;?>
</section>

<!-- LIVE STATUS STRIP -->
<div class="lux-metallic-card p-3 mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border: 1px solid rgba(212,175,55,0.3); background: rgba(16,17,23,0.95);">
  <div style="display: flex; align-items: center; gap: 10px; font-size: 0.88rem; color: #FFFFFF;">
    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 10px #22c55e;"></span>
    <span><b>Sincronizzazione Web & RSS Attiva:</b> Eventi scaduti rimossi automaticamente (<?=count($events)?> appuntamenti attivi)</span>
  </div>
  <div style="font-size: 0.82rem; color: #D4AF37; display: flex; align-items: center; gap: 6px;">
    <?=dx_icon('shield-check', '', 14)?>
    <span>Zero eventi obsoleti · Pulizia oraria automatica</span>
  </div>
</div>

<!-- CATEGORY FILTERS -->
<nav class="event-filter-bar mb-4" style="display: flex; gap: 8px; flex-wrap: wrap;" aria-label="Filtro tipo evento">
  <a href="?type=ALL" class="btn small <?=$currentType==='ALL'?'primary':''?>" style="<?=$currentType==='ALL'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Tutti (<?=count($events)?>)
  </a>
  <a href="?type=INTERCLUB" class="btn small <?=$currentType==='INTERCLUB'?'primary':''?>" style="<?=$currentType==='INTERCLUB'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Interclub Territoriali
  </a>
  <a href="?type=SAT" class="btn small <?=$currentType==='SAT'?'primary':''?>" style="<?=$currentType==='SAT'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Moduli SAT
  </a>
  <a href="?type=FORMAZIONE" class="btn small <?=$currentType==='FORMAZIONE'?'primary':''?>" style="<?=$currentType==='FORMAZIONE'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Corsi Sensibilizzazione
  </a>
  <a href="?type=WEBINAR" class="btn small <?=$currentType==='WEBINAR'?'primary':''?>" style="<?=$currentType==='WEBINAR'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Webinar Online
  </a>
  <a href="?type=CONGRESSO" class="btn small <?=$currentType==='CONGRESSO'?'primary':''?>" style="<?=$currentType==='CONGRESSO'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Congressi Nazionali
  </a>
  <a href="?type=LIFESTYLE" class="btn small <?=$currentType==='LIFESTYLE'?'primary':''?>" style="<?=$currentType==='LIFESTYLE'?'background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;':'border-color: rgba(212,175,55,0.3); color: #FFFFFF;'?>">
    Lifestyle & Territorio
  </a>
</nav>

<!-- LIVE EVENTS LIST -->
<div class="event-list my-4" style="display: grid; gap: 18px;">
  <?php if(empty($events)): ?>
    <div class="lux-metallic-card p-5 text-center" style="border: 1px dashed rgba(212,175,55,0.3);">
      <div style="color: #D4AF37; margin-bottom: 12px;"><?=dx_icon('calendar', '', 40)?></div>
      <h3 style="color: #FFFFFF; font-family: var(--font-serif);">Nessun evento in questa categoria per i prossimi giorni</h3>
      <p style="color: #a1a1aa; max-width: 500px; margin: 0 auto 16px;">Tutti gli eventi passati sono stati cancellati automaticamente. Torna a consultare la pagina o visualizza tutti gli appuntamenti.</p>
      <a href="?type=ALL" class="btn primary" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">Mostra Tutti gli Eventi</a>
    </div>
  <?php else: ?>
    <?php foreach($events as $e):
      $dateInfo = formatEventDate($e['starts_at']);
    ?>
      <article class="lux-metallic-card p-4" style="display: flex; gap: 24px; align-items: stretch; border: 1px solid rgba(212,175,55,0.25);">
        <!-- Date Block Left -->
        <div style="width: 100px; min-width: 100px; background: rgba(212,175,55,0.06); border: 1px solid rgba(212,175,55,0.25); border-radius: 16px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 12px;">
          <span style="font-size: 0.72rem; font-weight: 800; letter-spacing: 0.1em; color: #D4AF37; text-transform: uppercase;"><?=h($dateInfo['month'])?></span>
          <span style="font-size: 2.2rem; font-weight: 900; color: #FFFFFF; line-height: 1; margin: 4px 0;"><?=h($dateInfo['day'])?></span>
          <span style="font-size: 0.75rem; color: #a1a1aa;"><?=h($dateInfo['time'])?></span>
        </div>

        <!-- Event Details Center -->
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
              <span class="dx-ticker-badge"><?=h($e['type'])?></span>
              <span style="font-size: 0.78rem; font-weight: 700; color: #D4AF37; background: rgba(212,175,55,0.1); padding: 3px 10px; border-radius: 999px;">
                <?=dx_icon('clock', '', 12)?> <?=h($dateInfo['countdown'])?>
              </span>
            </div>
            
            <h3 style="margin: 0.2rem 0 0.5rem; color: #FFFFFF; font-family: var(--font-serif); font-size: 1.35rem; font-weight: 800;">
              <?=h($e['title'])?>
            </h3>
            
            <p style="color: #d1d5db; font-size: 0.94rem; line-height: 1.6; margin-bottom: 12px;">
              <?=h($e['description'])?>
            </p>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 12px; font-size: 0.84rem;">
            <div style="display: flex; gap: 16px; color: #a1a1aa; align-items: center;">
              <span style="display: inline-flex; align-items: center; gap: 6px;">
                <?=dx_icon('map-pin', '', 14)?> <b style="color: #FFFFFF;"><?=h($e['venue'])?></b>
              </span>
              <span style="display: inline-flex; align-items: center; gap: 6px;">
                <?=dx_icon('users', '', 14)?> <?=h((string)$e['registrations'])?> iscritti
              </span>
              <span style="display: inline-flex; align-items: center; gap: 6px; color: #D4AF37;">
                <?=dx_icon('award', '', 14)?> +<?=h((string)$e['drx_reward'])?> DRX
              </span>
            </div>

            <div style="display: flex; gap: 8px;">
              <?php if(!empty($e['source_url'])): ?>
                <a href="<?=h($e['source_url'])?>" target="_blank" rel="noopener" class="btn small" style="border-color: rgba(212,175,55,0.4); color: #FFFFFF;">
                  Fonte Ufficiale <?=dx_icon('external-link', '', 12)?>
                </a>
              <?php endif; ?>
              
              <?php if($u): ?>
                <form method="post" action="action.php" style="display: inline;">
                  <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
                  <input type="hidden" name="action" value="event_register">
                  <input type="hidden" name="event_sic_id" value="<?=h($e['sic_id'])?>">
                  <input type="hidden" name="return" value="events-public.php">
                  <button class="btn primary small" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">
                    <?=dx_icon('check-circle', '', 14)?> Partecipa
                  </button>
                </form>
              <?php else: ?>
                <a class="btn primary small" href="register.php" style="background: linear-gradient(135deg, #FFF2B2, #D4AF37); color: #070709; font-weight: 800;">
                  <?=dx_icon('sparkles', '', 14)?> Iscriviti
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach;?>
  <?php endif; ?>
</div>

<?php require '_footer.php';?>