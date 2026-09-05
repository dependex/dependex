<?php
/**
 * ADMIN — la plancia globale. Solo per l'admin della demo (sessione demo_admin o uid 'admin').
 * KPI · termometri · grafici (SVG, senza librerie) · utenti · depositi · coda prelievi · non attribuiti
 * · tool attivi · promo/booster · listino (override) · azioni recenti · registro.
 * Consigliato da PC. Nessuna chiave privata: la firma resta sullo script offline.
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
require_once __DIR__ . '/moduli.php';
demo_esigi();
$IO = demo_io();
if (empty($_SESSION['demo_admin']) && $IO !== 'admin') { http_response_code(403); echo 'Admin only.'; exit; }
$db = led_db(); $G = demo_gettone();
demo_tab_account(); demo_tab_prodotti(); demo_cfg_tab();
$db->exec('CREATE TABLE IF NOT EXISTS idx_non_attribuiti (tx_hash TEXT, log_index INTEGER, contratto TEXT, simbolo TEXT, importo TEXT, da TEXT, a TEXT, blocco INTEGER, quando INTEGER, gestito INTEGER DEFAULT 0, PRIMARY KEY(tx_hash,log_index))');
$db->exec('CREATE TABLE IF NOT EXISTS idx_log (id INTEGER PRIMARY KEY, quando INTEGER, testo TEXT)');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!demo_gettone_ok()) { demo_dico('no', 'Session expired.'); header('Location: admin.php'); exit; }
    try {
        switch ((string)($_POST['a'] ?? '')) {
            case 'promo':
                foreach (['promo_titolo','promo_testo','promo_boost','promo_fino'] as $k) demo_cfg_set($k, trim((string)($_POST[$k] ?? '')));
                demo_cfg_set('promo_on', !empty($_POST['promo_on']) ? '1' : '0');
                demo_dico('ok', 'Promo saved — visible in Home now.'); break;
            case 'mvp':
                demo_cfg_set('mvp', !empty($_POST['on']) ? '1' : '0'); demo_cfg_set('versione_forza', (string)max(0, min(12, (int)($_POST['forza'] ?? 0)))); demo_dico('ok', !empty($_POST['on']) ? 'MVP mode ON — only Classic Membership live, the rest shows COMING SOON.' : 'MVP mode OFF — every module is open.'); break;
            case 'listino':   // override dei parametri per le NUOVE attivazioni (le vecchie restano congelate)
                foreach (demo_fasce() as $k => $f) foreach (['costo','pd','molt'] as $c) if (isset($_POST[$k . '_' . $c])) demo_cfg_set('fascia_' . $k . '_' . $c, (string)max(0, (float)str_replace(',', '.', (string)$_POST[$k . '_' . $c])));
                foreach (demo_mining_taglie() as $k => $d) if (isset($_POST['rig_' . $k])) demo_cfg_set('rig_' . $k . '_molt', (string)max(0.1, (float)str_replace(',', '.', (string)$_POST['rig_' . $k])));   // booster dei rig
                if (function_exists('mod_mint_listino')) foreach (mod_mint_listino() as $k => $c) if (isset($_POST['mint_' . strtolower($k)])) demo_cfg_set('mint_' . strtolower($k), (string)max(1, (int)$_POST['mint_' . strtolower($k)]));   // prezzo mint per collezione
                demo_dico('ok', 'Price list saved for new activations. Active products keep their frozen terms.'); break;
            case 'firma':
                $cid = (int)$_POST['cid']; $hash = '0x' . bin2hex(random_bytes(32));
                $db->prepare("UPDATE led_coda SET stato='confermata', tx_hash=? WHERE id=? AND stato='in-attesa'")->execute([$hash, $cid]);
                $c = $db->prepare('SELECT uid FROM led_coda WHERE id=?'); $c->execute([$cid]); $u = (string)$c->fetchColumn();
                if ($u) demo_notifica($u, 'prelievo', 'Withdrawal EXECUTED', 'Tx ' . $hash . ' — Polygonscan.');
                demo_dico('ok', 'Marked as signed/executed (#' . $cid . '). In production the offline script does this.'); break;
            case 'rifiuta':
                $cid = (int)$_POST['cid'];
                $c = $db->prepare('SELECT * FROM led_coda WHERE id=? AND stato=?'); $c->execute([$cid, 'in-attesa']); $r = $c->fetch();
                if (!$r) throw new RuntimeException('Queue item not pending.');
                // rimborso: torna nel withdrawal wallet dell'utente (netto + fee)
                $fee = bigi_div(bigi_mul((string)$r['importo'], '50'), '9950');
                led_scrivi([
                    ['conto' => led_conto_esterno((string)$r['token']), 'token' => (string)$r['token'], 'importo' => led_meno((string)$r['importo']), 'causale' => 'prelievo', 'descrizione' => 'Withdrawal rejected — refund'],
                    ['conto' => demo_conto_prelievo((string)$r['uid'], (string)$r['token']), 'token' => (string)$r['token'], 'importo' => (string)$r['importo'], 'causale' => 'prelievo', 'descrizione' => 'Withdrawal rejected — refund'],
                ], 'admin');
                $db->prepare("UPDATE led_coda SET stato='rifiutata', nota=nota||' · rejected by admin' WHERE id=?")->execute([$cid]);
                demo_notifica((string)$r['uid'], 'prelievo', 'Withdrawal rejected', 'Refunded to your Withdrawal wallet (net amount). Contact support for details.');
                demo_dico('ok', 'Rejected and refunded (net).'); break;
            case 'attribuisci':
                $tx = (string)$_POST['tx']; $li = (int)$_POST['li']; $uid = trim((string)$_POST['uid']);
                $r = $db->prepare('SELECT * FROM idx_non_attribuiti WHERE tx_hash=? AND log_index=? AND gestito=0'); $r->execute([$tx, $li]); $d = $r->fetch();
                if (!$d) throw new RuntimeException('Not found or already handled.');
                $tok = $d['simbolo'] === 'USDT' ? 'USDT' : $d['simbolo'];
                led_deposito($uid, $tok, (string)$d['importo'], ['catena' => 'polygon', 'tx_hash' => $tx, 'log_index' => $li, 'blocco' => (int)$d['blocco'], 'stato' => 'confermato'], 'admin');
                if ($tok === 'USDT') led_swap($uid, 'USDT', 'DUX', (string)$d['importo']);
                $db->prepare('UPDATE idx_non_attribuiti SET gestito=1 WHERE tx_hash=? AND log_index=?')->execute([$tx, $li]);
                demo_notifica($uid, 'deposito', 'Deposit credited by admin', led_umano((string)$d['importo'], $tok) . ' ' . $tok . ' · tx ' . substr($tx, 0, 14) . '…');
                demo_dico('ok', 'Credited to ' . $uid . '.'); break;
            case 'premio':
                $uid = trim((string)$_POST['uid']); $tok = in_array($_POST['tok'] ?? '', ['DUX','DRX','81X'], true) ? (string)$_POST['tok'] : 'DRX'; $q = (string)$_POST['q'];
                $imp = led_base($q, $tok); if (led_cmp($imp, '0') <= 0) throw new RuntimeException('Amount must be positive.');
                $conto = $tok === 'DUX' ? demo_conto_vincolato($uid) : demo_conto_premio($uid, $tok);   // DUX admin = offset (mai prelevabili)
                led_scrivi([['conto' => $conto, 'token' => $tok, 'importo' => $imp, 'causale' => 'premio', 'descrizione' => 'Admin reward: ' . (string)($_POST['perche'] ?? '')],
                            ['conto' => led_conto_tesoreria($tok), 'token' => $tok, 'importo' => led_meno($imp), 'causale' => 'premio', 'descrizione' => 'Admin reward — ' . $uid]], 'admin');
                demo_notifica($uid, 'claim', 'Reward from BRANCH', $q . ' ' . $tok . ($tok === 'DUX' ? ' (offset)' : '') . ' — ' . (string)($_POST['perche'] ?? ''));
                demo_dico('ok', 'Reward sent.'); break;
        }
    } catch (Throwable $x) { demo_dico('no', $x->getMessage()); }
    header('Location: admin.php'); exit;
}

/* ---------- numeri ---------- */
$utenti = $db->query("SELECT COUNT(*) FROM demo_account")->fetchColumn();
$utentiVeri = $db->query("SELECT COUNT(*) FROM demo_account WHERE pw_hash IS NOT NULL")->fetchColumn();
$conPin = $db->query("SELECT COUNT(*) FROM demo_account WHERE pin_hash IS NOT NULL")->fetchColumn();
$conBind = $db->query("SELECT COUNT(*) FROM demo_account WHERE wallet_ext IS NOT NULL")->fetchColumn();
$dep = $db->query("SELECT token, SUM(CAST(importo AS REAL)) s, COUNT(*) n FROM led_scritture WHERE causale='deposito' AND CAST(importo AS REAL)>0 GROUP BY token")->fetchAll();
$depMap = []; foreach ($dep as $r) $depMap[$r['token']] = ['n' => (int)$r['n'], 's' => (float)$r['s']];
function um(float $base, string $tok): string { return number_format($base / (10 ** led_decimali($tok)), 2, '.', ','); }
$prod = $db->query("SELECT genere, COUNT(*) n, SUM(CASE WHEN attivo=1 THEN 1 ELSE 0 END) att, SUM(CAST(capitale AS REAL)) cap FROM demo_prodotti GROUP BY genere")->fetchAll();
$coda = $db->query("SELECT * FROM led_coda ORDER BY id DESC LIMIT 30")->fetchAll();
$pend = $db->query("SELECT COUNT(*) FROM led_coda WHERE stato='in-attesa'")->fetchColumn();
$non = $db->query("SELECT * FROM idx_non_attribuiti WHERE gestito=0 ORDER BY quando DESC LIMIT 20")->fetchAll();
$V = led_verifica();
$strati = ['utente' => 0.0, 'guadagnato' => 0.0, 'prelievo' => 0.0, 'vincolato' => 0.0];
foreach ($db->query("SELECT c.genere g, SUM(CAST(s.importo AS REAL)) s FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.token='DUX' AND c.genere IN ('utente','guadagnato','prelievo','vincolato') GROUP BY c.genere") as $r) $strati[$r['g']] = (float)$r['s'];
$tes = $db->query("SELECT c.token AS token, SUM(CAST(s.importo AS REAL)) s FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.genere='tesoreria' GROUP BY c.token")->fetchAll();
$fee = $db->query("SELECT SUM(CAST(s.importo AS REAL)) FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.genere='commissioni' AND c.token='DUX'")->fetchColumn();
// serie giornaliere (14 giorni): depositi USDT e attivazioni
$giorni = []; for ($i = 13; $i >= 0; $i--) $giorni[gmdate('Y-m-d', time() - $i * 86400)] = ['dep' => 0.0, 'att' => 0, 'utenti' => 0];
foreach ($db->query("SELECT date(quando,'unixepoch') d, SUM(CAST(importo AS REAL)) s FROM led_scritture WHERE causale='deposito' AND token='USDT' AND CAST(importo AS REAL)>0 AND quando>" . (time() - 14 * 86400) . " GROUP BY d") as $r) if (isset($giorni[$r['d']])) $giorni[$r['d']]['dep'] = (float)$r['s'] / 1e6;
foreach ($db->query("SELECT date(avviato,'unixepoch') d, COUNT(*) n FROM demo_prodotti WHERE avviato>" . (time() - 14 * 86400) . " GROUP BY d") as $r) if (isset($giorni[$r['d']])) $giorni[$r['d']]['att'] = (int)$r['n'];
foreach ($db->query("SELECT date(creato,'unixepoch') d, COUNT(*) n FROM demo_account WHERE creato>" . (time() - 14 * 86400) . " GROUP BY d") as $r) if (isset($giorni[$r['d']])) $giorni[$r['d']]['utenti'] = (int)$r['n'];
$ACC = $db->query("SELECT * FROM demo_account ORDER BY creato DESC, uid LIMIT 200")->fetchAll();
$AUD = $db->query("SELECT * FROM m_audit ORDER BY id DESC LIMIT 25")->fetchAll();
$IDXL = $db->query("SELECT * FROM idx_log ORDER BY id DESC LIMIT 6")->fetchAll();
$PR = demo_promo();
$CL = demo_classifica();
$rete = 0; foreach (demo_persone() as $u => $p) $rete++;

function barre(array $serie, string $k, string $eti): string {   // grafico a barre SVG puro
    $max = 0.0001; foreach ($serie as $v) $max = max($max, (float)$v[$k]);
    $w = 14 * 22; $h = 70; $o = '<svg viewBox="0 0 ' . $w . ' ' . ($h + 16) . '" width="100%" height="' . ($h + 16) . '" style="display:block">';
    $i = 0; foreach ($serie as $d => $v) { $hh = (float)$v[$k] / $max * $h; $o .= '<rect x="' . ($i * 22 + 3) . '" y="' . ($h - $hh) . '" width="16" height="' . $hh . '" rx="3" fill="url(#oro)"><title>' . e($d) . ': ' . e((string)$v[$k]) . '</title></rect>';
        if ($i % 2 === 0) $o .= '<text x="' . ($i * 22 + 11) . '" y="' . ($h + 12) . '" text-anchor="middle" font-size="7" fill="rgba(242,233,216,.5)">' . e(substr($d, 5)) . '</text>'; $i++; }
    return '<div class="eti">' . e($eti) . '</div>' . $o . '<defs><linearGradient id="oro" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f2dba4"/><stop offset="1" stop-color="#8a6f38"/></linearGradient></defs></svg>';
}
function termo(string $eti, float $pc, string $sotto = ''): string {
    $pc = max(0, min(100, $pc));
    return '<div style="margin:6px 0"><div style="display:flex;justify-content:space-between;font-size:10px;color:var(--tenue)"><span>' . e($eti) . '</span><b style="color:var(--oro-chiaro)">' . number_format($pc, 0) . '%</b></div><div class="barra" style="margin:4px 0"><i style="width:' . $pc . '%"></i></div>' . ($sotto ? '<div class="sub">' . e($sotto) . '</div>' : '') . '</div>';
}
$TIT = 'Admin — Command Center';
require __DIR__ . '/_testa.php';
?>
<section class="vista on" style="max-width:1400px">
  <div class="griglia3" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr))">
    <?php foreach ([['Users', (int)$utenti, $utentiVeri . ' registered · ' . $conPin . ' with PIN · ' . $conBind . ' bound'],
                    ['USDT deposited', '$' . um($depMap['USDT']['s'] ?? 0, 'USDT'), ($depMap['USDT']['n'] ?? 0) . ' deposits'],
                    ['DUX deposited layer', um($strati['utente'], 'DUX'), 'in Deposit wallets'],
                    ['DUX earned', um($strati['guadagnato'], 'DUX'), 'in Rewards wallets'],
                    ['DUX to withdraw', um($strati['prelievo'], 'DUX'), $pend . ' pending in queue'],
                    ['Fees collected', um((float)$fee, 'DUX') . ' DUX', '0.5% withdrawals'],
                    ['Ledger', $V['catena_integra'] && $V['partita_doppia_ok'] ? 'INTACT' : 'CHECK', (int)$V['righe'] . ' rows · hash chain · double entry'],
                    ['Network (demo)', $rete . ' nodes', 'live tree from the site bridge next']] as [$k, $v, $s]): ?>
      <div class="carta metal" style="margin:0;padding:12px 14px"><div class="eti"><?= e($k) ?></div><div class="medio" style="font-size:19px;margin:2px 0"><?= e((string)$v) ?></div><div class="sub"><?= e($s) ?></div></div>
    <?php endforeach; ?>
    <a class="carta metal" href="admin-media.php" style="margin:0;padding:12px 14px;text-decoration:none;color:inherit"><div class="eti">Media Library</div><div class="medio" style="font-size:19px;margin:2px 0">Open ›</div><div class="sub">registry · reports · needs review</div></a>
  </div>

  <div class="griglia-prod" style="margin-top:12px">
    <div class="carta" style="margin:0"><?= barre($giorni, 'dep', 'USDT deposits — last 14 days') ?></div>
    <div class="carta" style="margin:0"><?= barre($giorni, 'att', 'Product activations — last 14 days') ?></div>
    <div class="carta" style="margin:0"><?= barre($giorni, 'utenti', 'New users — last 14 days') ?></div>
    <div class="carta" style="margin:0"><div class="eti">Thermometers</div>
      <?php $totDux = max(1, $strati['utente'] + $strati['guadagnato'] + $strati['prelievo'] + $strati['vincolato']);
        echo termo('DUX at work (deposit layer)', 100 * $strati['utente'] / $totDux);
        echo termo('DUX earned by users', 100 * $strati['guadagnato'] / $totDux);
        echo termo('DUX heading out', 100 * $strati['prelievo'] / $totDux);
        echo termo('Users with PIN', $utenti ? 100 * $conPin / $utenti : 0);
        echo termo('Users with external wallet', $utenti ? 100 * $conBind / $utenti : 0); ?>
    </div>
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Tools active</div>
      <?php foreach ($prod as $p): ?><div class="riga" style="padding:6px 0"><div class="mid"><div class="tit2"><?= e(ucfirst((string)$p['genere'])) ?></div><div class="sub">capital <?= e(number_format((float)$p['cap'], 0, '.', ',')) ?></div></div><div class="val"><?= (int)$p['att'] ?> <span class="sub">/ <?= (int)$p['n'] ?></span></div></div><?php endforeach; ?>
      <?php if (!$prod): ?><div class="sub">No products yet.</div><?php endif; ?></div>
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Treasury</div>
      <?php foreach ($tes as $t): ?><div class="riga" style="padding:6px 0"><?= dric_gettone((string)$t['token'], 22) ?><div class="mid"><div class="tit2"><?= e((string)$t['token']) ?></div></div><div class="val"><?= e(um((float)$t['s'], (string)$t['token'])) ?></div></div><?php endforeach; ?>
      <div class="sub" style="margin-top:6px">Treasury spending needs a DAO proposal + offline signature. Nothing here moves it.</div></div>
  </div>

  <div class="griglia-prod" style="margin-top:12px">
    <!-- PROMO / BOOSTER -->
    <div class="carta" style="margin:0"><div class="eti">Promo card in Home — booster</div>
      <form method="post"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="promo">
        <label>Title</label><input name="promo_titolo" value="<?= e($PR['titolo']) ?>">
        <label>Text</label><input name="promo_testo" value="<?= e($PR['testo']) ?>">
        <div class="due"><div><label>Promo boost ×</label><input name="promo_boost" type="number" min="1" max="10" value="<?= (int)$PR['boost'] ?>"></div><div><label>Until (text)</label><input name="promo_fino" value="<?= e($PR['fino']) ?>" placeholder="30 Sep 2026"></div></div>
        <label class="chk" style="display:flex;gap:8px;align-items:center;text-transform:none;letter-spacing:0"><input type="checkbox" name="promo_on" value="1" <?= $PR['on'] ? 'checked' : '' ?> style="width:auto">Visible</label>
        <button class="b pieno">Save promo</button></form></div>
    <!-- LISTINO -->
    <div class="carta" style="margin:0"><div class="eti">Release mode</div>
      <div class="sotto" style="margin:4px 0 8px">MVP GENESYS (V1): only Classic Membership is live; Prestige, Stake NFT, Mining, Vault, Staking, DAO, Forum, Academy, NFT/Market/Events show <b>COMING SOON</b> with the roadmap month. Admin always sees everything.</div>
      <form method="post" style="display:flex;gap:8px;align-items:center"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="mvp">
        <label style="margin:0;display:flex;gap:6px;align-items:center"><input type="checkbox" name="on" value="1" <?= demo_mvp() ? 'checked' : '' ?> style="width:auto"> Release engine ON</label><label style="margin:0;display:flex;gap:6px;align-items:center">force version <input type="number" name="forza" min="0" max="12" value="<?= (int)demo_cfg('versione_forza', '0') ?>" style="width:64px;padding:6px"></label><button class="b mini" style="margin:0">Save</button></form>
      <div class="sub" style="margin-top:6px">Now: <b style="color:var(--oro-chiaro)"><?= demo_mvp() ? 'V' . demo_versione_attiva() . ' · ' . e(demo_roadmap()[demo_versione_attiva() - 1][2]) : 'ALL MODULES OPEN' ?></b> · engine: V1 on 6 Sep 2026 20:30, then the 6th of every month (0 = automatic by date) · users see the roadmap in Account Settings → Documents.</div></div>
    <div class="carta" style="margin:0"><div class="eti">Price list — new activations only</div>
      <form method="post"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="listino">
        <table class="tabella"><thead><tr><th>Tier</th><th>DUX</th><th>‰/day</th><th>×</th></tr></thead><tbody>
        <?php foreach (demo_fasce() as $k => $f): ?><tr><td><?= e($f['nome']) ?></td>
          <td><input name="<?= $k ?>_costo" value="<?= e(demo_cfg('fascia_' . $k . '_costo', (string)$f['costo'])) ?>" style="padding:6px;font-size:12px"></td>
          <td><input name="<?= $k ?>_pd" value="<?= e(demo_cfg('fascia_' . $k . '_pd', (string)$f['pd'])) ?>" style="padding:6px;font-size:12px"></td>
          <td><input name="<?= $k ?>_molt" value="<?= e(demo_cfg('fascia_' . $k . '_molt', (string)$f['molt'])) ?>" style="padding:6px;font-size:12px"></td></tr><?php endforeach; ?></tbody></table>
        <div class="eti" style="margin-top:8px">Mining rigs — booster (× on the 1‰ base)</div>
        <table class="tabella"><thead><tr><th>Rig</th><th>×</th></tr></thead><tbody>
        <?php foreach (demo_mining_taglie() as $k => $d): ?><tr><td><?= number_format($d, 0, '', ',') ?> DUX</td><td><input name="rig_<?= $k ?>" value="<?= e((string)demo_mining_boost_di((string)$k)) ?>" style="padding:6px;font-size:12px"></td></tr><?php endforeach; ?></tbody></table>
        <?php if (function_exists('mod_mint_listino')): ?><div class="eti" style="margin-top:8px">NFT mint — DUX per collection (Deposit DUX only)</div>
        <table class="tabella"><thead><tr><th>Collection</th><th>DUX</th></tr></thead><tbody>
        <?php foreach (mod_mint_listino() as $k => $c): ?><tr><td><?= e($c['nome']) ?></td><td><input name="mint_<?= strtolower($k) ?>" value="<?= e($c['dux']) ?>" style="padding:6px;font-size:12px"></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
        <button class="b">Save price list</button></form>
      <div class="sub" style="margin-top:6px">Overrides are read at activation time; running products keep their frozen terms.</div></div>
    <!-- REWARD MANUALE -->
    <div class="carta" style="margin:0"><div class="eti">Send a reward</div>
      <form method="post"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="premio">
        <label>User (uid / username)</label><input name="uid" list="lst" required><datalist id="lst"><?php foreach ($ACC as $x) echo '<option value="' . e((string)$x['uid']) . '">' . e((string)($x['nome_utente'] ?: $x['uid'])) . '</option>'; ?></datalist>
        <div class="due"><div><label>Token</label><select name="tok"><option>DRX</option><option>81X</option><option>DUX (offset)</option></select></div><div><label>Amount</label><input name="q" type="number" step="0.000001" min="0.000001" value="10"></div></div>
        <label>Reason (shown to the user)</label><input name="perche" placeholder="Community contribution">
        <button class="b">Send</button></form>
      <div class="sub" style="margin-top:6px">DUX from admin are always OFFSET (membership-only). DRX/81X land in the Rewards wallet.</div></div>
  </div>

  <div class="griglia-prod" style="margin-top:12px">
    <!-- CODA PRELIEVI -->
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Withdrawal queue (<?= (int)$pend ?> pending)</div>
      <?php if (!$coda): ?><div class="sub">Empty.</div><?php endif; ?>
      <?php foreach ($coda as $c): $pronta = demo_coda_pronta($c); ?>
        <div class="riga" style="padding:7px 0"><div class="mid"><div class="tit2">#<?= (int)$c['id'] ?> · <?= e((string)$c['uid']) ?> · <?= e(led_umano((string)$c['importo'], (string)$c['token'])) ?> <?= e((string)$c['token']) ?></div>
          <div class="sub"><?= e(substr((string)$c['destinazione'], 0, 14)) ?>… · <?= e(gmdate('d M H:i', (int)$c['creata'])) ?> · <?= e((string)$c['stato']) ?><?= $c['stato'] === 'in-attesa' ? ($pronta ? ' · review done' : ' · in review') : '' ?></div></div>
          <?php if ($c['stato'] === 'in-attesa'): ?><div style="display:flex;gap:4px">
            <form method="post"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="firma"><input type="hidden" name="cid" value="<?= (int)$c['id'] ?>"><button class="b mini pieno" style="margin:0">Mark signed</button></form>
            <form method="post" onsubmit="return confirm('Reject and refund #<?= (int)$c['id'] ?>?')"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="rifiuta"><input type="hidden" name="cid" value="<?= (int)$c['id'] ?>"><button class="b mini" style="margin:0">Reject</button></form></div><?php endif; ?></div>
      <?php endforeach; ?>
      <div class="sub" style="margin-top:6px">Real signing happens on the offline PC script; "Mark signed" records the result.</div></div>
    <!-- NON ATTRIBUITI -->
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Unattributed deposits (global address)</div>
      <?php if (!$non): ?><div class="sub">None. Indexer log: <?= $IDXL ? e((string)$IDXL[0]['testo']) . ' · ' . e(gmdate('d M H:i', (int)$IDXL[0]['quando'])) : 'never run' ?></div><?php endif; ?>
      <?php foreach ($non as $d): ?>
        <form method="post" class="riga" style="padding:7px 0"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="attribuisci"><input type="hidden" name="tx" value="<?= e((string)$d['tx_hash']) ?>"><input type="hidden" name="li" value="<?= (int)$d['log_index'] ?>">
          <div class="mid"><div class="tit2"><?= e(led_umano((string)$d['importo'], $d['simbolo'] === 'USDT' ? 'USDT' : (string)$d['simbolo'])) ?> <?= e((string)$d['simbolo']) ?> from <?= e(substr((string)$d['da'], 0, 12)) ?>…</div><div class="sub"><?= e(substr((string)$d['tx_hash'], 0, 18)) ?>… · block <?= (int)$d['blocco'] ?></div></div>
          <input name="uid" placeholder="uid" style="width:110px;padding:6px;font-size:12px"><button class="b mini pieno" style="margin:0 0 0 4px">Credit</button></form>
      <?php endforeach; ?></div>
    <!-- LEADERBOARD -->
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Leaderboard</div>
      <?php foreach (array_slice($CL, 0, 10) as $r): ?><div class="riga" style="padding:6px 0"><span class="pal" style="width:22px;height:22px;font-size:9px"><?= (int)$r['posizione'] ?></span><?= dric_lupo((int)$r['prest_liv'], 18) ?><div class="mid"><div class="tit2"><?= e($r['nome']) ?></div><div class="sub"><?= e($r['rango']) ?> · <?= (int)$r['persone'] ?> below</div></div><div class="val"><?= dollari($r['xp']) ?></div></div><?php endforeach; ?></div>
  </div>

  <div class="carta" style="margin-top:12px;padding:10px 12px"><div class="eti" style="margin:4px 0 6px">Users (<?= (int)$utenti ?>) — balances by layer</div>
    <div style="overflow-x:auto"><table class="tabella"><thead><tr><th>uid</th><th>username</th><th>email</th><th>SIC</th><th>posto</th><th>PIN</th><th>bound</th><th>Deposit DUX</th><th>Earned</th><th>Withdrawal</th><th>Offset</th><th>DRX</th><th>81X</th><th>joined</th></tr></thead><tbody>
    <?php foreach ($ACC as $x): $u = (string)$x['uid']; $L = demo_strati($u); ?>
      <tr><td><?= e($u) ?></td><td><?= e((string)($x['nome_utente'] ?? '')) ?></td><td><?= e((string)($x['email'] ?? '')) ?></td><td><?= e((string)($x['sic'] ?? '')) ?></td><td><?= (int)($x['posto'] ?? 0) ?></td><td><?= $x['pin_hash'] ? '✓' : '—' ?></td><td><?= $x['wallet_ext'] ? e(substr((string)$x['wallet_ext'], 0, 8)) . '…' : '—' ?></td>
        <td><?= e(soldi($L['deposito']['DUX'], 'DUX')) ?></td><td><?= e(soldi($L['rewards']['DUX'], 'DUX')) ?></td><td><?= e(soldi($L['prelievo']['DUX'], 'DUX')) ?></td><td><?= e(soldi(demo_saldo_vincolato($u), 'DUX')) ?></td><td><?= e(soldi(led_somma($L['deposito']['DRX'], $L['rewards']['DRX']), 'DRX')) ?></td><td><?= e(soldi(led_somma($L['deposito']['81X'], $L['rewards']['81X']), '81X')) ?></td><td><?= $x['creato'] ? e(gmdate('d M', (int)$x['creato'])) : '—' ?></td></tr>
    <?php endforeach; ?></tbody></table></div></div>

  <div class="griglia-prod" style="margin-top:12px">
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Recent actions (audit)</div>
      <?php foreach ($AUD as $a): ?><div class="riga" style="padding:5px 0"><div class="mid"><div class="tit2"><?= e((string)$a['uid']) ?> · <?= e((string)$a['azione']) ?></div><div class="sub"><?= e((string)($a['dettaglio'] ?? '')) ?> · <?= e(gmdate('d M H:i', (int)$a['quando'])) ?></div></div></div><?php endforeach; ?>
      <?php if (!$AUD): ?><div class="sub">Nothing yet.</div><?php endif; ?></div>
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Indexer runs</div>
      <?php foreach ($IDXL as $l): ?><div class="riga" style="padding:5px 0"><div class="mid"><div class="tit2"><?= e((string)$l['testo']) ?></div><div class="sub"><?= e(gmdate('d M H:i', (int)$l['quando'])) ?></div></div></div><?php endforeach; ?>
      <?php if (!$IDXL): ?><div class="sub">Never run. Cron: indexer-depositi.php?key=DR_PONTE_KEY every 5 minutes.</div><?php endif; ?>
      <div class="due" style="margin-top:8px"><a class="b mini" href="stress.php" style="text-decoration:none">Stress bench</a><a class="b mini" href="diagnosi.php" style="text-decoration:none" target="_blank">Diagnosis</a></div></div>
    <div class="carta" style="margin:0"><div class="eti" style="margin-bottom:6px">Links</div>
      <?php foreach ([['Site admin', 'https://destinorandagio.it/admin.php'], ['The Covo forum admin', 'https://destinorandagio.it/covo/admin.php'], ['Polygonscan treasury', 'https://polygonscan.com/address/' . DR_DEPOSITO_GLOBALE], ['Wallet 118 (site)', 'https://destinorandagio.it/admin-genesys-wallets.php']] as [$n, $u]): ?>
        <a class="riga" href="<?= e($u) ?>" target="_blank" rel="noopener" style="text-decoration:none;color:inherit;padding:6px 0"><?= dric_ui('link', 18) ?><div class="mid"><div class="tit2"><?= e($n) ?></div></div><span style="color:var(--oro)">›</span></a>
      <?php endforeach; ?></div>
  </div>
</section>
<?php require __DIR__ . '/_piede.php'; ?>
