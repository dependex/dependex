<?php
require_once 'bootstrap.php';
$u = require_login();

// Daily triggers
$daily = daily_access_claim($u['sic_id']);
$sob = sobriety_sync($u['sic_id']);
$tot = drx_totals($u['sic_id']);
$rank = rank_for_drx($tot['qualifying']);
$club = club_for_user($u['sic_id']);
$milestone = next_sobriety_milestone((int)$sob['days']);
$checkinToday = user_daily_checkin_status($u['sic_id']);
$reflection = daily_reflection();
$sos = emergency_sos_contact($u['sic_id']);

$m = db()->query("SELECT * FROM missions WHERE status='ACTIVE' ORDER BY RANDOM() LIMIT 1")->fetch();

$pageTitle = 'Il mio Cammino';
require '_header.php';
?>

<!-- PWA INSTALL BANNER (Appare se l'app non è ancora installata) -->
<div class="pwa-banner" id="pwaBanner" style="display:none;">
  <div class="pwa-banner-text">
    <b>📲 Aggiungi DEPENDEX alla Home</b>
    <small>Accedi in un tocco ogni giorno e ricevi il promemoria di percorso.</small>
  </div>
  <button type="button" class="pwa-install-btn" id="pwaInstallBtn">Installa</button>
  <button type="button" class="pwa-close-btn" id="pwaDismissBtn" aria-label="Chiudi">×</button>
</div>

<!-- WELCOME HEADER -->
<section class="welcome" style="margin-bottom: 8px;">
  <div>
    <span class="muted"><?=h($locale==='it'?'Bentornato nel cerchio':'Welcome back')?></span>
    <h1><?=h($u['display_name'])?> 👋</h1>
  </div>
  <div class="drx-chip tabular-nums">◆ <?=number_format($tot['total'], 0, ',', '.')?> DRX</div>
</section>

<!-- WIDESCREEN 16:9 & MOBILE 9:16 RESPONSIVE GRID -->
<div class="dashboard-grid-16-9">

  <!-- LEFT COLUMN (CAMMINO, CHECK-IN, RIFLESSIONE, MISSIONI) -->
  <div class="dash-left">
    <!-- SOBRIETY STREAK & MILESTONE HERO -->
    <section class="sobriety-hero">
      <span class="eyebrow" style="color: #a7f3d0;">Il mio cammino</span>
      <div class="sobriety-days-row">
        <strong><?=number_format($sob['days'], 0, ',', '.')?></strong>
        <span><?=h($locale==='it'?'giorni':'days')?></span>
      </div>
      <small style="opacity: .9;"><?=h($locale==='it'?'Un giorno alla volta verso una salute completa.':'One day at a time.')?></small>

      <!-- Next Milestone Progress Bar -->
      <div class="milestone-box">
        <div class="milestone-head">
          <span>🎯 Prossimo traguardo: <b><?=h($milestone['title'])?></b></span>
          <span><?php if($milestone['remaining_days'] > 0):?>mancano <b><?=$milestone['remaining_days']?> gg</b> (+<?=$milestone['drx_reward']?> DRX)<?php else:?>Completato!<?php endif;?></span>
        </div>
        <div class="milestone-track" role="progressbar" aria-valuenow="<?=$milestone['progress_pct']?>" aria-valuemin="0" aria-valuemax="100">
          <div class="milestone-fill" style="width: <?=$milestone['progress_pct']?>%;"></div>
        </div>
      </div>

      <div class="mini-row">
        <span>Grado <b>Rank <?=h($rank)?></b></span>
        <span><?= $daily['new'] ? '✨ +1 DRX presenza di oggi' : '✓ Presenza registrata' ?></span>
      </div>
    </section>

    <!-- QUICK 1-TAP DAILY CHECK-IN WIDGET -->
    <section class="quick-checkin-card <?= $checkinToday ? 'completed' : '' ?>">
      <div class="section-head" style="padding: 0 0 10px 0;">
        <div>
          <span class="eyebrow"><?=h($locale==='it'?'Automonitoraggio Quotidiano':'Daily Check-in')?></span>
          <h2 style="margin: 4px 0;"><?= $checkinToday ? 'Check-in di oggi completato ✓' : 'Come stai oggi?' ?></h2>
        </div>
        <?php if ($checkinToday): ?>
          <span class="pill" style="background: var(--green-light); color: var(--green);">+5 DRX Assegnati</span>
        <?php endif; ?>
      </div>

      <?php if ($checkinToday): ?>
        <p class="muted" style="margin: 6px 0 14px 0; font-size: .9rem;">
          Hai registrato: Umore <b><?=$checkinToday['mood']?>/10</b> · Livello Stress <b><?=$checkinToday['stress']?>/10</b>. 
          <?php if (!empty($checkinToday['note'])): ?>
            <br><em>"<?=h($checkinToday['note'])?>"</em>
          <?php endif; ?>
        </p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
          <a class="btn small" href="checkin.php">Modifica Check-in</a>
          <a class="btn small primary" href="journal.php">✍ Scrivi nel Diario Privato</a>
        </div>
      <?php else: ?>
        <p class="muted" style="margin: 0 0 14px 0; font-size: .88rem;">
          Dedica 30 secondi a te stesso per fermare come ti senti. Guadagni <b>+5 DRX</b>.
        </p>
        <form method="post" action="action.php" class="checkin-row">
          <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
          <input type="hidden" name="action" value="quick_checkin">
          <input type="hidden" name="return" value="app.php">

          <div class="checkin-item">
            <div class="checkin-item-header">
              <span>😊 Umore generale</span>
              <b id="val_mood">7/10</b>
            </div>
            <input type="range" min="1" max="10" value="7" name="mood" class="checkin-slider" aria-label="Umore da 1 a 10">
          </div>

          <div class="checkin-item">
            <div class="checkin-item-header">
              <span>🧘 Livello di Serenità vs Stress</span>
              <b id="val_stress">3/10</b>
            </div>
            <input type="range" min="0" max="10" value="3" name="stress" class="checkin-slider" aria-label="Livello di stress da 0 a 10">
          </div>

          <div class="checkin-item">
            <div class="checkin-item-header">
              <span>🛡️ Desiderio / Impulso (Craving)</span>
              <b id="val_craving">0/10</b>
            </div>
            <input type="range" min="0" max="10" value="0" name="craving" class="checkin-slider" aria-label="Impulso craving da 0 a 10">
          </div>

          <button class="btn primary" style="width: 100%; margin-top: 6px;">
            Salva Check-in di Oggi · +5 DRX
          </button>
        </form>
      <?php endif; ?>
    </section>

    <!-- RIFLESSIONE DEL GIORNO (METODO HUDOLIN) -->
    <section class="reflection-card">
      <span class="eyebrow" style="color: var(--blue);">Pensiero del Giorno</span>
      <blockquote>"<?=h($reflection['quote'])?>"</blockquote>
      <cite>— <?=h($reflection['author'])?></cite>
    </section>

    <!-- DAILY MISSION -->
    <?php if ($m): ?>
      <section class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
          <div>
            <span class="eyebrow">Missione del Giorno</span>
            <h2 style="margin: 4px 0 8px;"><?=h($m['title'])?></h2>
          </div>
          <span class="pill" style="background: var(--green-light); color: var(--green);">+<?=h((string)$m['drx_reward'])?> DRX</span>
        </div>
        <p class="muted" style="font-size: .9rem; margin-bottom: 16px;"><?=h($m['description'])?></p>
        <form method="post" action="action.php">
          <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
          <input type="hidden" name="action" value="complete_mission">
          <input type="hidden" name="mission_sic_id" value="<?=h($m['sic_id'])?>">
          <input type="hidden" name="return" value="app.php">
          <button class="btn primary">Completata · Ricevi +<?=h((string)$m['drx_reward'])?> DRX</button>
        </form>
      </section>
    <?php endif; ?>
  </div>

  <!-- RIGHT COLUMN (CLUB, DRX QUALIFICANTI, QUICK SHORTCUTS, SOS) -->
  <div class="dash-right">
    <!-- CLUB HUB WIDGET -->
    <section class="card" style="margin-top: 0;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
          <span class="eyebrow">La mia Comunità</span>
          <h2 style="margin: 4px 0 8px;"><?= $club ? h($club['entity_name']) : 'Nessun Club associato' ?></h2>
        </div>
        <span class="pill" style="background: var(--orange-glow); color: var(--orange2);">🤝 CLUB</span>
      </div>
      <?php if ($club): ?>
        <p class="muted" style="font-size: .88rem; margin-bottom: 12px;">
          📍 <?=h($club['address'] ?: $club['comune'])?><br>
          🕒 Incontro: <b><?=h($club['meeting_day'] ?? 'Settimanale')?> <?=h($club['meeting_time'] ?? '')?></b>
        </p>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <a class="btn small primary" href="club.php">Apri Club Hub</a>
          <a class="btn small" href="world-map.php">Vedi sulla Mappa</a>
        </div>
      <?php else: ?>
        <p class="muted" style="font-size: .88rem; margin-bottom: 12px;">
          Trova un Club vicino a te per iniziare il cammino di gruppo.
        </p>
        <a class="btn primary small" href="world-club-explorer.php">Cerca un Club Territoriale</a>
      <?php endif; ?>
    </section>

    <!-- DRX QUALIFICANTI & RANK -->
    <section class="card">
      <div style="display: flex; justify-content: space-between; align-items: baseline;">
        <div>
          <span class="eyebrow">Avanzamento di Grado</span>
          <h2 style="margin: 4px 0;">Rank <?=h($rank)?></h2>
        </div>
        <span class="tabular-nums" style="font-size: 1.5rem; font-weight: 900; color: var(--green);"><?=number_format($tot['qualifying'], 0, ',', '.')?> <small style="font-size: .8rem;">DRX</small></span>
      </div>
      <p class="muted" style="font-size: .86rem; margin: 8px 0 14px;">
        I DRX qualificanti derivano da presenze, check-in, lezioni completate e ore di volontariato.
      </p>
      <a class="btn small" href="rank.php" style="width: 100%; text-align: center;">Tutti i Rank & Privilegi</a>
    </section>

    <!-- QUICK SHORTCUTS TILES — I 7 RAMI NEON DELL'ARCOBALENO -->
    <section class="quick-grid" style="margin: 0; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));">
      <a class="quick red" href="sobriety.php" title="1. Senti · Presenza">
        <b>🪷</b>
        <span>Cammino</span>
      </a>
      <a class="quick orange" href="journal.php" title="2. Agisci · Movimento">
        <b>✍</b>
        <span>Diario</span>
      </a>
      <a class="quick amber" href="events.php" title="3. Comunica · Voce">
        <b>📅</b>
        <span>Eventi</span>
      </a>
      <a class="quick green" href="club.php" title="4. Vedi · Visione">
        <b>🤝</b>
        <span>Club</span>
      </a>
      <a class="quick blue" href="cortex.php" title="5. Ama · Relazione">
        <b>✨</b>
        <span>Cortex AI</span>
      </a>
      <a class="quick indigo" href="academy.php" title="6. Costruisci · Struttura">
        <b>🎓</b>
        <span>Academy</span>
      </a>
      <a class="quick violet" href="offers.php" title="7. Sii · Sovranità">
        <b>👑</b>
        <span>Offerte</span>
      </a>
    </section>

    <!-- SOS / ASCOLTO RAPIDO -->
    <section class="sos-card" style="margin-top: 6px;">
      <div class="sos-info">
        <h3>Serve ascolto immediato?</h3>
        <p>Non restare solo. C'è sempre una mano tesa nel cerchio.</p>
      </div>
      <?php if ($sos['phone']): ?>
        <a class="sos-btn" href="tel:<?=h($sos['phone'])?>">📞 Chiama</a>
      <?php else: ?>
        <a class="sos-btn" href="world-club-explorer.php">🤝 Trova Club</a>
      <?php endif; ?>
    </section>
  </div>

</div>

<?php require '_footer.php'; ?>