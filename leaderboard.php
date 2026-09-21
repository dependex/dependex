<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$public = true;
$pageTitle = 'Community & Club Impact · DEPENDEX';
$metaDesc = 'Classifica trasparente dell’impatto generato dai Club CAT locali e testimonianze comunitarie nel rispetto assoluto della privacy personale.';
$canonicalUrl = 'https://dependex.social/leaderboard.php';

$db = db();
$sob = $db->query("
    SELECT sr.current_streak, sr.lifetime_days, up.leaderboard_display, u.display_name 
    FROM sobriety_records sr 
    JOIN users u ON u.sic_id = sr.user_sic_id 
    LEFT JOIN user_preferences up ON up.user_sic_id = u.sic_id 
    WHERE (up.sobriety_leaderboard = 1 OR up.sobriety_leaderboard IS NULL) 
      AND (up.leaderboard_display IS NULL OR up.leaderboard_display <> 'HIDDEN') 
    ORDER BY sr.current_streak DESC 
    LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);

$clubs = $db->query("
    SELECT w.sic_id, w.entity_name, w.country, 
           COALESCE(SUM(CASE WHEN d.rank_eligible = 1 AND d.status = 'POSTED' THEN d.amount ELSE 0 END), 0) as qdrx 
    FROM dependex_world_registry w 
    LEFT JOIN drx_ledger d ON d.club_sic_id = w.sic_id 
    WHERE w.network_level = 'LOCAL_CLUB' 
    GROUP BY w.sic_id 
    ORDER BY qdrx DESC, w.entity_name ASC 
    LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);

require '_header.php';
?>
<div class="container py-4" style="max-width: 1100px; margin: 0 auto; padding: 0 1rem;">
  <section class="section-head mb-4 text-center">
    <span class="badge-neon-rainbow mb-2">
      <span class="dot"></span>
      <span>COMMUNITY IMPACT · CONDIVISIONE SOLIDALE</span>
    </span>
    <h1 style="font-family: var(--font-serif); font-size: clamp(1.6rem, 3.2vw, 2.4rem); color: #ffffff; font-weight: 800; margin: 0 0 8px;">
      Presidi e Comunità in Cammino
    </h1>
    <p style="color: #cbd5e1; font-size: 0.95rem; max-width: 700px; margin: 0 auto;">
      La condivisione comunitaria valorizza l'impegno collettivo dei Club territoriali. Il cammino personale è libero, intimo e protetto.
    </p>
  </section>

  <div class="row g-4">
    <div class="col-md-6">
      <section class="card p-4 h-100" style="background: rgba(14, 20, 38, 0.85); border: 1px solid rgba(253,230,138,0.25); border-radius: 18px;">
        <h2 style="font-size: 1.25rem; color: #fde68a; font-family: var(--font-serif); font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('users', 'text-neon-gold', 18)?>
          <span>Cammino Personale (Condivisione Volontaria)</span>
        </h2>
        <?php if (!$sob): ?>
          <p style="color: #94a3b8; font-size: 0.88rem;">Nessun partecipante ha attivato la condivisione pubblica.</p>
        <?php else: ?>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($sob as $i => $r):
              $name = ($r['leaderboard_display'] ?? '') === 'ANONYMOUS' ? 'Partecipante Riservato' : (string)($r['display_name'] ?? 'Membro del Club');
            ?>
              <div class="d-flex justify-content-between align-items-center p-2 px-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
                <div class="d-flex align-items-center gap-2">
                  <b style="color: #fde68a; font-size: 0.85rem; width: 24px;"><?=$i + 1?></b>
                  <span style="color: #ffffff; font-size: 0.88rem; font-weight: 600;"><?=h($name)?></span>
                </div>
                <em style="color: #86efac; font-style: normal; font-size: 0.85rem; font-weight: 700;"><?=number_format((int)$r['current_streak'], 0, ',', '.')?> giorni</em>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <div class="col-md-6">
      <section class="card p-4 h-100" style="background: rgba(14, 20, 38, 0.85); border: 1px solid rgba(6, 182, 212, 0.25); border-radius: 18px;">
        <h2 style="font-size: 1.25rem; color: #67e8f9; font-family: var(--font-serif); font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
          <?=dx_icon('award', 'text-neon-cyan', 18)?>
          <span>Impegno Solidale Presidi Territoriali</span>
        </h2>
        <?php if (!$clubs): ?>
          <p style="color: #94a3b8; font-size: 0.88rem;">Nessun dato registrato al momento.</p>
        <?php else: ?>
          <div class="d-flex flex-column gap-2">
            <?php foreach ($clubs as $i => $r): ?>
              <div class="d-flex justify-content-between align-items-center p-2 px-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;">
                <div class="d-flex align-items-center gap-2">
                  <b style="color: #67e8f9; font-size: 0.85rem; width: 24px;"><?=$i + 1?></b>
                  <span style="color: #ffffff; font-size: 0.88rem; font-weight: 600;">
                    <?=h($r['entity_name'])?>
                    <small style="color: #94a3b8; font-size: 0.75rem;"> · <?=h($r['country'])?></small>
                  </span>
                </div>
                <em style="color: #fde68a; font-style: normal; font-size: 0.85rem; font-weight: 700;"><?=number_format((float)$r['qdrx'], 0, ',', '.')?> PV</em>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</div>
<?php require '_footer.php'; ?>