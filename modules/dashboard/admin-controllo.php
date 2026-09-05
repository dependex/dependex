<?php
/**
 * ADMIN — CONTROL ROOM (V3, 17-08-2026). Admin only.
 *  · STOP / BLOCKS / FREEZE reali via demo_cfg (letti da _azioni.php → v3_blocchi_check): deposits, withdrawals, activations, release pause, freeze per token.
 *  · Price list with a date (v3_sched): pd / molt / cost changes that apply to NEW activations from a given date.
 *  · Rank points / pool caps / V3 cap (demo_cfg) — informational for the engine + strumenti.php.
 *  · Academy Vault: rate, price, winners, run the draw (airdrop-run.php), history.
 *  · Forecast on live numbers: runs bmm-engine.js in the browser with users = live users.
 *  · Log of every action (v3_log). Every change asks a confirmation. Nothing here signs or moves USDT.
 */
declare(strict_types=1);
require_once __DIR__ . '/_academy-vault.php';
demo_esigi();
$IO = demo_io();
if (!demo_admin_sessione()) { http_response_code(403); echo 'Admin only.'; exit; }
$G = demo_gettone(); v3_tab(); demo_cfg_tab();
$FLAGS = ['blocco_depositi' => 'Block deposits', 'blocco_prelievi' => 'Block withdrawals (freeze withdrawals)', 'blocco_attivazioni' => 'Block new activations / purchases', 'pausa_rilasci' => 'Temporary release pause (claims refused, production kept)', 'freeze_DUX' => 'Freeze token DUX (swaps/transfers)', 'freeze_DRX' => 'Freeze token DRX', 'freeze_81X' => 'Freeze token 81X', 'freeze_ERIDAN' => 'Freeze token ERIDAN'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!demo_gettone_ok()) { demo_dico('no', 'Session expired.'); header('Location: admin-controllo.php'); exit; }
    try {
        switch ((string)($_POST['a'] ?? '')) {
            case 'flags':
                $chg = [];
                foreach ($FLAGS as $k => $lab) { $v = !empty($_POST['f_' . $k]) ? '1' : '0'; if (demo_cfg('v3_' . $k, '0') !== $v) { demo_cfg_set('v3_' . $k, $v); $chg[] = $k . '=' . $v; } }
                demo_cfg_set('v3_motivo', trim((string)($_POST['motivo'] ?? '')));
                v3_log($IO, 'flags', implode(', ', $chg) ?: 'no change' . ' · reason: ' . (string)($_POST['motivo'] ?? ''));
                demo_dico('ok', $chg ? 'Blocks updated: ' . implode(', ', $chg) . '. Active immediately in every action.' : 'No block changed (reason saved).'); break;
            case 'sched':
                $S = json_decode(demo_cfg('v3_sched', '[]'), true) ?: [];
                $q = strtotime((string)($_POST['quando'] ?? '')); if (!$q) throw new RuntimeException('Date not valid.');
                $S[] = ['quando' => $q, 'fascia' => (string)$_POST['fascia'], 'campo' => (string)$_POST['campo'], 'valore' => (float)str_replace(',', '.', (string)$_POST['valore']), 'chi' => $IO, 'creato' => time()];
                demo_cfg_set('v3_sched', json_encode($S)); v3_log($IO, 'sched_add', json_encode(end($S)));
                demo_dico('ok', 'Scheduled: ' . $_POST['fascia'] . ' ' . $_POST['campo'] . ' → ' . $_POST['valore'] . ' from ' . gmdate('d M Y H:i', $q) . ' UTC (new activations only).'); break;
            case 'sched_del':
                $S = json_decode(demo_cfg('v3_sched', '[]'), true) ?: []; $i = (int)$_POST['i']; if (isset($S[$i])) { v3_log($IO, 'sched_del', json_encode($S[$i])); array_splice($S, $i, 1); demo_cfg_set('v3_sched', json_encode(array_values($S))); }
                demo_dico('ok', 'Scheduled change removed.'); break;
            case 'params':
                foreach (['v3_cap' => [0, 1], 'v3_finestra_giorni' => [0, 30], 'v3_firme_giorno' => [0, 1000000], 'v3_cap_giorno' => [0, 10000000], 'v3_cap_settimana' => [0, 100000000], 'v3_cap_mese' => [0, 1000000000], 'v3_cap_globale_giorno' => [0, 100000000000], 'pool_pct_rev' => [0, 0.12], 'pool_pct_cm' => [0, 0.30], 'pool_budget' => [0, 0.12], 'comp_cap' => [0, 0.25], 'bonus_scale' => [0, 1]] as $k => [$lo, $hi]) {
                    if (!isset($_POST[$k])) continue; $v = (float)str_replace(',', '.', (string)$_POST[$k]); if ($v < $lo || $v > $hi) throw new RuntimeException($k . ' out of range [' . $lo . ', ' . $hi . '] — caps can only go down, never above policy.');
                    demo_cfg_set($k, (string)$v); }
                v3_log($IO, 'params', json_encode(array_intersect_key($_POST, array_flip(['v3_cap', 'v3_finestra_giorni', 'v3_firme_giorno', 'v3_cap_giorno', 'v3_cap_settimana', 'v3_cap_mese', 'v3_cap_globale_giorno', 'pool_pct_rev', 'pool_pct_cm', 'pool_budget', 'comp_cap', 'bonus_scale']))));
                demo_dico('ok', 'Parameters saved.'); break;
            case 'academy':
                $t = (float)str_replace(',', '.', (string)$_POST['av_tasso']); if ($t <= 0 || $t > 0.25) throw new RuntimeException('Rate must be between 0 and 25% of the vault per draw.');
                demo_cfg_set('av_tasso', (string)$t); demo_cfg_set('av_prezzo', (string)max(1, (int)$_POST['av_prezzo'])); demo_cfg_set('av_vincitori', (string)max(1, (int)$_POST['av_vincitori'])); demo_cfg_set('av_intervallo_giorni', (string)max(1, (int)$_POST['av_intervallo']));
                v3_log($IO, 'academy_cfg', 'rate ' . $t . ' price ' . (int)$_POST['av_prezzo'] . ' winners ' . (int)$_POST['av_vincitori'] . ' interval ' . (int)$_POST['av_intervallo']);
                demo_dico('ok', 'Academy Vault settings saved.'); break;
            case 'airdrop':
                $r = av_run('admin:' . $IO, !empty($_POST['force']));
                demo_dico('ok', 'Airdrop round #' . $r['round'] . ' done: ' . led_umano($r['budget'], 'DUX') . ' DUX to ' . count($r['vincitori']) . ' winners. Seed ' . substr($r['seed'], 0, 16) . '…'); break;
            /* ---- V4 (17-08-2026) ---- */
            case 'v4_tes':   // interruttori "verificata" delle tre tesorerie: peso 100% nel semaforo/pool solo se = 1
                $chg = []; foreach (['shield', 'flow', 'recovery'] as $k) { $v = !empty($_POST['ver_' . $k]) ? '1' : '0'; if (demo_cfg('tes_verified_' . $k, '0') !== $v) { demo_cfg_set('tes_verified_' . $k, $v); $chg[] = $k . '=' . $v; } }
                demo_cfg_set('v4_tes_nota', trim((string)($_POST['nota'] ?? '')));
                v3_log($IO, 'v4_treasury_verified', (implode(', ', $chg) ?: 'no change') . ' · proof: ' . (string)($_POST['nota'] ?? ''));
                demo_dico('ok', $chg ? 'Treasury verification updated: ' . implode(', ', $chg) . '. Verified vaults now weigh 100% in coverage and pool; the others 0.' : 'No verification flag changed (note saved).'); break;
            case 'v4_params':
                foreach (['v4_budget_mese' => [0, 1e12], 'v4_cm_pct' => [0, 1], 'v4_intervallo_giorni' => [1, 90]] as $k => [$lo, $hi]) { if (!isset($_POST[$k])) continue; $v = (float)str_replace(',', '.', (string)$_POST[$k]); if ($v < $lo || $v > $hi) throw new RuntimeException($k . ' out of range'); demo_cfg_set($k, (string)$v); }
                demo_cfg_set('v4_on', !empty($_POST['v4_on']) ? '1' : '0');
                v3_log($IO, 'v4_params', json_encode(array_intersect_key($_POST, array_flip(['v4_budget_mese', 'v4_cm_pct', 'v4_intervallo_giorni', 'v4_on']))));
                demo_dico('ok', 'Funded reward parameters saved.'); break;
            case 'v4_run':
                $r = v4_funded_run('admin:' . $IO, !empty($_POST['force']), !empty($_POST['dry']));
                demo_dico('ok', ($r['dry'] ? '[DRY] ' : '') . 'Cash Reward Pool run: pool ' . led_umano($r['pool'], 'DUX') . ' DUX (bound by ' . $r['vincolo'] . ', semaphore ' . $r['semaforo']['stato'] . ') · eligible ' . led_umano($r['idonei'], 'DUX') . ' · funded ' . led_umano($r['finanziato'], 'DUX') . ' · rate ' . number_format($r['tasso'] * 100, 2) . '% · ' . $r['utenti'] . ' users.'); break;
            case 'v4_kyc':
                $uid = trim((string)($_POST['uid'] ?? '')); $t = (string)($_POST['tier'] ?? 'new');
                if ($uid === '' || demo_trova_utente($uid) === null && $uid !== 'admin') throw new RuntimeException('User not found.');
                $uid = $uid === 'admin' ? 'admin' : (string)demo_trova_utente($uid);
                v4_kyc_tier_set($uid, $t); v3_log($IO, 'v4_kyc_tier', $uid . ' → ' . $t);
                demo_dico('ok', 'KYC profile of ' . $uid . ' set to ' . $t . '. Effective limits = min(profile, V3 caps, 0.10% of monthly pool on the month).'); break;
        }
    } catch (Throwable $x) { demo_dico('no', $x->getMessage()); }
    header('Location: admin-controllo.php'); exit;
}
$LOG = led_db()->query('SELECT * FROM v3_log ORDER BY id DESC LIMIT 40')->fetchAll();
$SCH = json_decode(demo_cfg('v3_sched', '[]'), true) ?: [];
$FW = v3_finestra_prelievi();
$TIT = 'Control Room';
require __DIR__ . '/_testa.php';
?>
<section class="vista on" style="max-width:1400px">
  <div class="carta" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap"><div style="flex:1;min-width:220px"><div class="eti">Control Room — V3 + V4</div><div class="medio" style="font-size:16px">Stop · blocks · freeze · price list with a date · Academy Vault · three treasuries · Cash Reward Pool · KYC profiles · forecast</div><div class="sub">Every switch here is read by the real actions (_azioni.php). Nothing signs, nothing moves USDT.</div></div>
    <a class="b" href="admin-motore.php" style="width:auto;padding:10px 14px">Business Model Engine</a><a class="b" href="admin.php" style="width:auto;padding:10px 14px">← Command Center</a></div>

  <div class="griglia-prod">
    <div class="carta" style="margin:0"><div class="eti">STOP / BLOCKS / FREEZE (live)</div>
      <form method="post" onsubmit="return confirm('Apply these blocks now? They act immediately on every user action.')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="flags">
        <?php foreach ($FLAGS as $k => $lab): $on = v3_blocco($k); ?><label style="display:flex;gap:8px;align-items:center;text-transform:none;letter-spacing:0;font-size:12px;margin:8px 0;color:<?= $on ? '#e07b2a' : 'inherit' ?>"><input type="checkbox" name="f_<?= $k ?>" value="1" <?= $on ? 'checked' : '' ?> style="width:auto"><?= e($lab) ?><?= $on ? ' — ACTIVE' : '' ?></label><?php endforeach; ?>
        <label>Reason shown to users</label><input name="motivo" value="<?= e(demo_cfg('v3_motivo', '')) ?>" placeholder="e.g. weekly risk review — coverage below 1.2">
        <button class="b pieno">Apply blocks (asks confirmation)</button></form></div>
    <div class="carta" style="margin:0"><div class="eti">Price list with a date — new activations only</div>
      <form method="post" onsubmit="return confirm('Schedule this price-list change?')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="sched">
        <div class="due"><div><label>Tier</label><select name="fascia"><?php foreach (demo_fasce() as $k => $f): ?><option value="<?= e($k) ?>"><?= e($f['nome']) ?></option><?php endforeach; ?></select></div><div><label>Field</label><select name="campo"><option value="pd">pd (‰/day)</option><option value="molt">multiplier ×</option><option value="costo">price DUX</option></select></div></div>
        <div class="due"><div><label>Value</label><input name="valore" type="number" step="any" required></div><div><label>From (UTC)</label><input name="quando" type="datetime-local" required></div></div>
        <button class="b">Schedule</button></form>
      <?php foreach ($SCH as $i => $r): ?><div class="riga" style="padding:6px 0"><div class="mid"><div class="tit2"><?= e((string)$r['fascia']) ?> · <?= e((string)$r['campo']) ?> → <?= e((string)$r['valore']) ?></div><div class="sub">from <?= e(gmdate('d M Y H:i', (int)$r['quando'])) ?> UTC · <?= (int)$r['quando'] <= time() ? 'ACTIVE' : 'pending' ?> · by <?= e((string)($r['chi'] ?? '')) ?></div></div>
        <form method="post" style="margin:0" onsubmit="return confirm('Remove?')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="sched_del"><input type="hidden" name="i" value="<?= (int)$i ?>"><button class="b mini" style="width:auto">Remove</button></form></div><?php endforeach; ?>
      <div class="sub" style="margin-top:6px">Active products keep their frozen terms; only new activations take the scheduled values (demo_fasce → v3_listino_programmato).</div></div>
    <div class="carta" style="margin:0"><div class="eti">Policy parameters (caps only go down)</div>
      <form method="post" onsubmit="return confirm('Save parameters?')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="params">
        <div class="due"><div><label>V3 withdrawable cap (× cash paid)</label><input name="v3_cap" type="number" step="0.01" min="0" max="1" value="<?= e(demo_cfg('v3_cap', '0.50')) ?>"></div><div><label>Withdrawal window (days, 0 = none)</label><input name="v3_finestra_giorni" type="number" min="0" max="30" value="<?= (int)$FW['finestra_giorni'] ?>"></div></div>
        <?php $TT = v3_tetti_prelievo(); ?>
        <div class="due"><div><label>Withdrawal cap per user / day (DUX, 0 = none)</label><input name="v3_cap_giorno" type="number" min="0" value="<?= (int)$TT['giorno'] ?>"></div><div><label>Per user / week (DUX)</label><input name="v3_cap_settimana" type="number" min="0" value="<?= (int)$TT['settimana'] ?>"></div></div>
        <div class="due"><div><label>Per user / month (DUX)</label><input name="v3_cap_mese" type="number" min="0" value="<?= (int)$TT['mese'] ?>"></div><div><label>Global ecosystem cap / day (DUX)</label><input name="v3_cap_globale_giorno" type="number" min="0" value="<?= (int)$TT['globale_giorno'] ?>"></div></div>
        <div class="sub" style="margin:-4px 0 8px">Rolling windows on withdrawal requests (gross). Defaults 2,500 / 10,000 / 30,000 per user and 250,000 per day for the whole ecosystem — the engine's sustainable capacity; shown to users in Wallet and My tools.</div>
        <div class="due"><div><label>Signing capacity / day</label><input name="v3_firme_giorno" type="number" min="0" value="<?= (int)$FW['firme_giorno'] ?>"></div><div><label>Bonus scale (≤ 1)</label><input name="bonus_scale" type="number" step="0.05" min="0" max="1" value="<?= e(demo_cfg('bonus_scale', '1')) ?>"></div></div>
        <div class="due"><div><label>Pool % net revenue (≤ 12%)</label><input name="pool_pct_rev" type="number" step="0.01" min="0" max="0.12" value="<?= e(demo_cfg('pool_pct_rev', '0.12')) ?>"></div><div><label>Pool % contribution (≤ 30%)</label><input name="pool_pct_cm" type="number" step="0.01" min="0" max="0.30" value="<?= e(demo_cfg('pool_pct_cm', '0.30')) ?>"></div></div>
        <div class="due"><div><label>Pool budget % (≤ 12%)</label><input name="pool_budget" type="number" step="0.01" min="0" max="0.12" value="<?= e(demo_cfg('pool_budget', '0.10')) ?>"></div><div><label>Total comp cap (≤ 25%)</label><input name="comp_cap" type="number" step="0.01" min="0" max="0.25" value="<?= e(demo_cfg('comp_cap', '0.25')) ?>"></div></div>
        <button class="b">Save</button></form>
      <div class="sub" style="margin-top:6px">Rank thresholds are immutable and are not editable anywhere. Withdrawal review stays 72 h (code constant); the window and signing capacity are informational for users (strumenti.php).</div></div>
    <div class="carta" style="margin:0"><div class="eti">Academy Vault & Airdrop</div>
      <div class="sub" style="margin:4px 0 8px">Vault <b style="color:var(--oro-chiaro)"><?= e(led_umano(av_saldo(), 'DUX')) ?> DUX</b> · participants <?= count(av_partecipanti()) ?> · next draw <?= e(gmdate('d M Y H:i', av_prossimo_run())) ?> UTC · airdrops go to Deposit wallets (not withdrawable) · ERIDAN quota set aside (internal units)</div>
      <form method="post" onsubmit="return confirm('Save Academy settings?')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="academy">
        <div class="due"><div><label>Rate per draw (share of vault, ≤ 25%)</label><input name="av_tasso" type="number" step="0.005" min="0.001" max="0.25" value="<?= e((string)av_tasso()) ?>"></div><div><label>Premium module price (DUX)</label><input name="av_prezzo" type="number" min="1" value="<?= e(demo_cfg('av_prezzo', '25')) ?>"></div></div>
        <div class="due"><div><label>Winners per draw</label><input name="av_vincitori" type="number" min="1" value="<?= av_min_vincitori() ?>"></div><div><label>Interval (days)</label><input name="av_intervallo" type="number" min="1" value="<?= av_intervallo_giorni() ?>"></div></div>
        <button class="b">Save Academy settings</button></form>
      <form method="post" style="margin-top:8px" onsubmit="return confirm('Run the airdrop draw NOW? It moves vault DUX and treasury tokens to winners\' Deposit wallets.')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="airdrop"><label style="display:flex;gap:8px;align-items:center;text-transform:none;letter-spacing:0"><input type="checkbox" name="force" value="1" style="width:auto">force (ignore interval)</label><button class="b pieno">Run airdrop draw (asks confirmation)</button></form>
      <?php foreach (av_rounds(6) as $r): ?><div class="riga" style="padding:6px 0"><div class="mid"><div class="tit2">Round #<?= (int)$r['id'] ?> · <?= e(led_umano((string)$r['budget'], 'DUX')) ?> DUX · <?= (int)$r['vincitori'] ?>/<?= (int)$r['partecipanti'] ?></div><div class="sub"><?= e(gmdate('d M Y H:i', (int)$r['quando'])) ?> · seed <?= e(substr((string)$r['seed'], 0, 20)) ?>… · by <?= e((string)$r['chi']) ?></div></div></div><?php endforeach; ?>
      <div class="sub" style="margin-top:6px">Cron: <code>php demo/airdrop-run.php</code> (respects the interval; <code>--force</code> skips it).</div></div>
  </div>

  <?php /* ================= V4 (17-08-2026): tre tesorerie · Cash Reward Pool · profili KYC ================= */
    $T4 = v4_tesorerie_live(); $SEM = v4_semaforo($T4); $PC = v4_pool_calcolo($T4); $RUNS = v4_runs(8); $KT = v4_kyc_tiers();
    $KYCU = led_db()->query("SELECT k, v FROM demo_config WHERE k LIKE 'kyc_tier_%' ORDER BY k")->fetchAll(); ?>
  <div class="griglia-prod" style="margin-top:12px">
    <div class="carta" style="margin:0;border-color:rgba(217,180,90,.5)"><div class="eti">V4 · Three external treasuries — proof of control</div>
      <div class="sub" style="margin:4px 0 6px">Balances are read live from Polygon and shown on the home as they are. They weigh <b>0%</b> in the coverage semaphore and in the Cash Reward Pool until you register the proof of control (EIP-191 signature from the address, test tx, owner document, allowance check, multisig). Then set the switch: weight 100%.</div>
      <form method="post" onsubmit="return confirm('Change treasury verification flags? Verified vaults are counted 100% by the risk engine.')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="v4_tes">
        <?php foreach ($T4['tes'] as $k => $t): ?><label style="display:flex;gap:8px;align-items:flex-start;text-transform:none;letter-spacing:0;font-size:12px;margin:8px 0;color:<?= $t['verificata'] ? '#7fd08a' : 'inherit' ?>"><input type="checkbox" name="ver_<?= e($k) ?>" value="1" <?= $t['verificata'] ? 'checked' : '' ?> style="width:auto;margin-top:2px"><span>T<?= (int)$t['n'] ?> <?= e($t['nome']) ?> — <b><?= e(number_format($t['usdt'], 2)) ?> USDT</b> <?= $t['live'] ? '' : '(offline)' ?><br><span class="sub" style="font-family:monospace;word-break:break-all"><?= e($t['addr']) ?></span><br><span class="sub"><?= e($t['etichetta']) ?> · custody: <?= e($t['multisig']) ?></span></span></label><?php endforeach; ?>
        <label>Proof note (what was verified, by whom, when)</label><input name="nota" value="<?= e(demo_cfg('v4_tes_nota', '')) ?>" placeholder="e.g. EIP-191 signature verified 20 Aug 2026 by … · multisig 4/7 on Safe">
        <button class="b pieno">Save verification flags (asks confirmation)</button></form>
      <div class="sub" style="margin-top:8px">Coverage now: <b style="color:<?= e($SEM['colore']) ?>"><?= e($SEM['stato']) ?></b> <?= e((string)$SEM['coverage']) ?> = verified funds <?= e(number_format($SEM['fondi_verificati'], 2)) ?> ÷ 90-day funded obligations <?= e(number_format($SEM['obblighi']['totale'], 2)) ?> (queue <?= e(number_format($SEM['obblighi']['coda'], 2)) ?> · withdrawal wallets <?= e(number_format($SEM['obblighi']['withdrawal_wallet'], 2)) ?> · funded in Rewards <?= e(number_format($SEM['obblighi']['funded_in_rewards'], 2)) ?> · 3× last pool <?= e(number_format($SEM['obblighi']['pool_x3'], 2)) ?>) → <?= e($SEM['azione']) ?>.</div></div>
    <div class="carta" style="margin:0;border-color:rgba(217,180,90,.5)"><div class="eti">V4 · Cash Reward Pool — monthly funding run</div>
      <form method="post" onsubmit="return confirm('Save funded-reward parameters?')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="v4_params">
        <label style="display:flex;gap:8px;align-items:center;text-transform:none;letter-spacing:0;font-size:12px;margin:6px 0"><input type="checkbox" name="v4_on" value="1" <?= v4_on() ? 'checked' : '' ?> style="width:auto">V4 funded reward ON (Rewards → Withdrawal only for funded DUX; KYC tier limits active)</label>
        <div class="due"><div><label>Approved monthly budget (USD, 0 = nothing approved → pool 0)</label><input name="v4_budget_mese" type="number" min="0" step="1" value="<?= e(demo_cfg('v4_budget_mese', '0')) ?>"></div><div><label>Contribution margin % of cash-in [assumption]</label><input name="v4_cm_pct" type="number" min="0" max="1" step="0.01" value="<?= e(demo_cfg('v4_cm_pct', '0.35')) ?>"></div></div>
        <div class="due"><div><label>Run interval (days)</label><input name="v4_intervallo_giorni" type="number" min="1" max="90" value="<?= v4_intervallo_giorni() ?>"></div><div><label>Next run due</label><input value="<?= e(gmdate('d M Y H:i', v4_prossimo_run())) ?> UTC" readonly style="opacity:.6"></div></div>
        <button class="b">Save parameters</button></form>
      <div class="sub" style="margin:8px 0 4px">Pool preview now = MIN of:</div>
      <?php foreach ($PC['candidati'] as $k => $c): ?><div class="riga" style="padding:3px 0"><div class="mid"><div class="tit2" style="font-size:11px;<?= $PC['vincolo'] === $k ? 'color:var(--oro-chiaro)' : '' ?>"><?= e($k) ?><?= $PC['vincolo'] === $k ? ' ← binding' : '' ?></div><div class="sub"><?= e($c['nota']) ?></div></div><div class="val" style="font-size:11px"><?= e(number_format($c['v'], 2)) ?></div></div><?php endforeach; ?>
      <div class="sub" style="margin:4px 0 8px">× semaphore factor <?= e((string)$SEM['fattore']) ?> (<?= e($SEM['stato']) ?>) = <b style="color:var(--oro-chiaro)"><?= e(number_format($PC['pool'], 2)) ?> USD</b> for this run. Eligible points now: <?php $ID = v4_idonei_tutti(); $tI = '0'; foreach ($ID as $x) $tI = bigi_add($tI, $x); echo e(led_umano($tI, 'DUX')); ?> DUX from <?= count($ID) ?> users → rate ≈ <?= $tI !== '0' ? number_format(min(1, $PC['pool'] / ((float)$tI / 1e6)) * 100, 1) : '—' ?>%.</div>
      <form method="post" onsubmit="return confirm('Run the Cash Reward Pool conversion NOW? Eligible points become funded DUX up to the pool. Not reversible.')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="v4_run">
        <div style="display:flex;gap:12px;flex-wrap:wrap"><label style="display:flex;gap:8px;align-items:center;text-transform:none;letter-spacing:0"><input type="checkbox" name="force" value="1" style="width:auto">force (ignore interval)</label><label style="display:flex;gap:8px;align-items:center;text-transform:none;letter-spacing:0"><input type="checkbox" name="dry" value="1" style="width:auto">dry run (log only)</label></div>
        <button class="b pieno">Run funding now (asks confirmation)</button></form>
      <?php foreach ($RUNS as $r): ?><div class="riga" style="padding:5px 0"><div class="mid"><div class="tit2" style="font-size:11px"><?= (int)$r['dry'] ? '[DRY] ' : '' ?>Run #<?= (int)$r['id'] ?> · pool <?= e(led_umano((string)$r['pool'], 'DUX')) ?> · funded <?= e(led_umano((string)$r['finanziato'], 'DUX')) ?> of <?= e(led_umano((string)$r['idonei'], 'DUX')) ?> eligible · <?= number_format((float)$r['tasso'] * 100, 1) ?>% · <?= (int)$r['utenti'] ?> users</div><div class="sub"><?= e(gmdate('d M Y H:i', (int)$r['quando'])) ?> UTC · by <?= e((string)$r['chi']) ?> · <?= e(mb_strimwidth((string)$r['dettaglio'], 0, 90, '…')) ?></div></div></div><?php endforeach; ?>
      <div class="sub" style="margin-top:6px">Cron: <code>php demo/funded-run.php</code> (respects the interval; <code>--force</code>, <code>--dry</code>). Users see funded vs eligible in Wallet and My tools; the conversion is never guaranteed and can be zero.</div></div>
    <div class="carta" style="margin:0;border-color:rgba(217,180,90,.5)"><div class="eti">V4 · KYC profile per user (withdrawal limits)</div>
      <div class="sub" style="margin:4px 0 6px">Effective per-user limit = MIN(profile, V3 caps <?= number_format((int)v3_tetti_prelievo()['giorno']) ?>/<?= number_format((int)v3_tetti_prelievo()['settimana']) ?>/<?= number_format((int)v3_tetti_prelievo()['mese']) ?>, and on the month 0.10% of the global pool); the Withdrawal wallet holds only funded DUX. USD-equivalent (1 DUX = 1 USD).</div>
      <table class="tabella" style="font-size:11px"><thead><tr><th>Profile</th><th>Day</th><th>Week</th><th>Month</th></tr></thead><tbody><?php foreach ($KT as $k => $t): ?><tr><td><?= e($t['nome']) ?> <span class="sub">(<?= e($k) ?>)</span></td><td><?= $t['g'] ?></td><td><?= $t['s'] ?></td><td><?= $t['m'] ?></td></tr><?php endforeach; ?></tbody></table>
      <form method="post" style="margin-top:8px" onsubmit="return confirm('Set this KYC profile?')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="v4_kyc">
        <div class="due"><div><label>User (uid / username / email)</label><input name="uid" required placeholder="uid or username"></div><div><label>Profile</label><select name="tier"><?php foreach ($KT as $k => $t): ?><option value="<?= e($k) ?>"><?= e($t['nome']) ?></option><?php endforeach; ?></select></div></div>
        <button class="b">Set profile</button></form>
      <div class="wrows" style="max-height:160px;margin-top:8px"><?php foreach ($KYCU as $r): ?><div class="riga" style="padding:4px 0"><div class="mid"><div class="tit2" style="font-size:11px"><?= e(substr((string)$r['k'], 9)) ?></div></div><div class="val" style="font-size:11px"><?= e((string)$r['v']) ?></div></div><?php endforeach; if (!$KYCU): ?><div class="sub">Everyone is on the default profile "new" (25 / 100 / 250).</div><?php endif; ?></div></div>
  </div>

  <div class="carta" style="margin-top:12px"><div class="eti">Forecast on live numbers (engine v3 · users = live)</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin:8px 0"><select id="fVer" style="width:auto"><option value="v4">v4 funded</option><option value="v3">v3 blindata</option><option value="v2">v2</option><option value="v1">v1 as-is</option></select><input id="fPop" type="number" min="1" value="1000" style="width:140px" title="start users (live users if available)"><button class="b" id="fRun" style="width:auto;padding:10px 14px">Forecast 240 months</button><span class="sub" id="fMsg"></span></div>
    <div id="fOut"></div></div>

  <div class="carta"><div class="eti">Action calendar & log</div>
    <?php foreach ($SCH as $r): if ((int)$r['quando'] > time()): ?><div class="riga" style="padding:6px 0"><div class="mid"><div class="tit2">⏱ <?= e(gmdate('d M Y H:i', (int)$r['quando'])) ?> — <?= e((string)$r['fascia']) ?> <?= e((string)$r['campo']) ?> → <?= e((string)$r['valore']) ?></div><div class="sub">scheduled price-list change</div></div></div><?php endif; endforeach; ?>
    <div class="riga" style="padding:6px 0"><div class="mid"><div class="tit2">⏱ <?= e(gmdate('d M Y H:i', av_prossimo_run())) ?> — Academy airdrop draw</div><div class="sub">every <?= av_intervallo_giorni() ?> days</div></div></div>
    <div class="wrows" style="max-height:300px;margin-top:8px"><table class="tabella"><thead><tr><th>When (UTC)</th><th>Who</th><th>Action</th><th>Detail</th></tr></thead><tbody>
      <?php foreach ($LOG as $l): ?><tr><td><?= e(gmdate('d M Y H:i:s', (int)$l['quando'])) ?></td><td><?= e((string)$l['chi']) ?></td><td><?= e((string)$l['azione']) ?></td><td class="sub"><?= e(mb_strimwidth((string)$l['dettaglio'], 0, 160, '…')) ?></td></tr><?php endforeach; ?>
      <?php if (!$LOG): ?><tr><td colspan="4" class="sub">No action logged yet.</td></tr><?php endif; ?></tbody></table></div></div>
</section>
<script src="bmm-engine.js?v=<?= @filemtime(__DIR__ . '/bmm-engine.js') ?: 1 ?>"></script>
<script>
(function(){
  var CFG={};
  ['v1-as-is','v2-sustainable','v3-blindata'].forEach(function(n){ CFG[n.split('-')[0]] = null; });
  var RAW = { v1: <?= is_file(__DIR__ . '/../config/bmm/v1-as-is.json') ? file_get_contents(__DIR__ . '/../config/bmm/v1-as-is.json') : 'null' ?>, v2: <?= is_file(__DIR__ . '/../config/bmm/v2-sustainable.json') ? file_get_contents(__DIR__ . '/../config/bmm/v2-sustainable.json') : 'null' ?>, v3: <?= is_file(__DIR__ . '/../config/bmm/v3-blindata.json') ? file_get_contents(__DIR__ . '/../config/bmm/v3-blindata.json') : 'null' ?>, v4: <?= is_file(__DIR__ . '/../config/bmm/v4-funded.json') ? file_get_contents(__DIR__ . '/../config/bmm/v4-funded.json') : 'null' ?> };
  var COL={BLUE:'#FFF2B2',GREEN:'#D4AF37',YELLOW:'#F5DF88',ORANGE:'#C59A27',RED:'#AA771C',BLACK:'#070709',UNVERIFIED:'#71717a'};
  var fmt=function(n){ return n==null?'—':Number(n).toLocaleString('en-US',{maximumFractionDigits:0}); };
  fetch('bmm-live.php',{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(L){ if(L.ok&&L.users>0) document.getElementById('fPop').value=Math.max(L.users, 1); }).catch(function(){});
  document.getElementById('fRun').addEventListener('click', function(){
    var v=document.getElementById('fVer').value; var c=RAW[v]||BMM.defaultConfig(); c=BMM.mergeConfig(c,{population:{startUsers:+document.getElementById('fPop').value||1000}});
    var out=BMM.runModel(c), s=out.summary;
    var tl=''; out.months.forEach(function(m){ tl+='<div title="m'+m.month+' '+m.state+'" style="height:12px;background:'+COL[m.state]+'"></div>'; });
    document.getElementById('fOut').innerHTML='<div style="display:grid;grid-template-columns:repeat(60,1fr);gap:1px">'+tl+'</div><div class="sub" style="margin-top:6px">worst <b style="color:'+COL[s.worstState]+'">'+s.worstState+'</b> · first RED m'+(s.firstRed||'—')+' · BLACK m'+(s.firstBlack||'—')+' · end cash '+fmt(s.endCash)+' · treasury calls '+fmt(s.treasuryTotalCalls)+' · comp ratio '+(s.compRatioAvg*100).toFixed(1)+'% · break-even 12m m'+(s.firstEbitdaPositive12m||'—')+' · <a href="admin-motore.php" style="color:var(--oro-chiaro)">full engine →</a></div>';
    document.getElementById('fMsg').textContent='done';
  });
})();
</script>
<?php require __DIR__ . '/_piede.php'; ?>
