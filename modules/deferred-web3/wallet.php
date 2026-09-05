<?php
/**
 * WALLET — la pagina del portafoglio.
 * Overview (DUX spendable · DRX · 81X · POL · USDT · DUX offset) +
 * cinque azioni in popup: DEPOSIT (QR + indirizzo) · TRANSFER · WITHDRAW · SWAP · BIND.
 * Il PIN lo chiede il popup, non un campo nel modulo. Il deposito non lo chiede.
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
require_once __DIR__ . '/_azioni.php';
demo_esigi();
demo_semina();
$IO = demo_io();
demo_azioni($IO, 'wallet.php');

$S    = demo_stato($IO);
$G    = demo_gettone();
$A    = demo_account($IO);
$MOV  = led_estratto($IO);
$CODA = led_db()->prepare("SELECT * FROM led_coda WHERE uid=? ORDER BY id DESC LIMIT 8"); $CODA->execute([$IO]); $CODA = $CODA->fetchAll();
$VINC = demo_saldo_vincolato($IO);
$haPin = demo_pin_c_e($IO);
$bound = (string)($A['wallet_ext'] ?? '');
$PROJ = demo_env('WALLETCONNECT_PROJECT_ID', '');
$SPESA = soldi($S['saldi']['DUX'], 'DUX');
require __DIR__ . '/_testa.php';
?>
<section class="vista on">
  <?php $L = demo_strati($IO); ?>
  <?php require_once __DIR__ . '/_media.php'; echo media_hero('img_wallet_flow', '124px', ['caption' => 'Deposit · Rewards · Withdrawal · Offset', 'loading' => 'eager']); ?>
  <?php if (!$haPin): ?>
  <div class="avviso" style="border-color:rgba(217,180,90,.6);color:var(--oro-chiaro)">
    <b>No transaction PIN yet.</b> Transfers, withdrawals, swaps and moves between wallets will ask for it.
    <a href="account.php?s=pin" style="color:inherit">Create it in Account →</a></div>
  <?php endif; ?>

  <div class="azioni" style="grid-template-columns:repeat(4,1fr)">
    <button type="button" onclick="apri('velo_deposit');qrDep()"><?= dric_ui('scarica', 22) ?>Deposit</button>
    <button type="button" onclick="apri('velo_transfer')"><?= dric_ui('send', 22) ?>Transfer</button>
    <button type="button" onclick="apri('velo_withdraw')"><?= dric_ui('wallet', 22) ?>Withdraw</button>
    <button type="button" onclick="apri('velo_swap')"><?= dric_ui('swap', 22) ?>Swap</button>
  </div>

  <?php
    $tot = function(array $arr) { $t = '0'; foreach ($arr as $tk => $v) { if ($tk === 'ERIDAN') continue; $t = bigi_add($t, $tk === 'BTC' ? demo_btc_in_dux((string)$v) : demo_conv($v, $tk, 'DUX')); } return $t; };   // valore in DUX (1:1) per il display in $
    $vDep = $tot($L['deposito']); $vRew = $tot($L['rewards']); $vPre = $tot($L['prelievo']); $vOff = $tot($L['offset']);
  ?>
  <div class="wgrid">
  <!-- ===== 1. DEPOSIT WALLET ===== -->
  <div class="wcard w-dep">
    <div class="wtop"><span class="wico"><?= dric_ui('scarica', 18) ?></span><div style="flex:1;min-width:0"><div class="wname">Deposit</div><div class="wsub">what you put in · usable in every tool</div></div><span class="wnum">1</span></div>
    <div class="wbal"><?= e(dollari_base($vDep)) ?><small>value</small></div>
    <div class="wrows">
    <?php foreach (['DUX' => 'from USDT 1:1', 'DRX' => 'vault · staking · votes', '81X' => 'staking · unlock', 'ERIDAN' => 'on-chain with its contract', 'BTC' => 'bitcoin · deposit & hold', 'USDT' => 'converts to DUX'] as $t => $d): ?>
      <div class="riga"><?= dric_gettone($t, 26) ?><div class="mid"><div class="tit2"><?= e($t) ?></div><div class="sub"><?= e($d) ?></div></div><div class="val"><?= e(soldi($L['deposito'][$t], $t)) ?></div></div>
    <?php endforeach; ?>
    </div>
    <div class="wact"><button type="button" class="b mini pieno" onclick="apri('velo_deposit');qrDep()">Deposit</button><button type="button" class="b mini" onclick="apri('velo_swap')">Swap</button></div>
  </div>

  <!-- ===== 2. REWARDS WALLET ===== -->
  <div class="wcard w-rew">
    <div class="wtop"><span class="wico" style="color:#fff"><?= dric_ui('spunta', 18) ?></span><div style="flex:1;min-width:0"><div class="wname" style="color:#fff">Rewards</div><div class="wsub">what the tools produce · transferable</div></div><span class="wnum">2</span></div>
    <div class="wbal" style="color:#fff"><?= e(dollari_base($vRew)) ?><small>value</small></div>
    <div class="wrows">
      <div class="riga riga-verde"><?= dric_gettone('DUX', 26) ?><div class="mid"><div class="tit2">DUX <span class="tag-verde">earned</span></div><div class="sub"><?php if (function_exists('v4_stato_utente') && v4_on()): $S4w = v4_stato_utente($IO); ?>Reward Utility · funded <b style="color:#7fd08a"><?= e(led_umano($S4w['funded_disp'], 'DUX')) ?></b> → Withdrawal · eligible <?= e(led_umano($S4w['eligible'], 'DUX')) ?> waits for the pool<?php else: ?>→ Withdrawal to cash out<?php endif; ?></div></div><div class="val"><?= e(soldi($L['rewards']['DUX'], 'DUX')) ?></div></div>
      <?php foreach (['DRX' => 'votes · → Deposit for tools', '81X' => '→ Deposit for tools', 'ERIDAN' => '10% of tools · 5% of Prestige', 'BTC' => 'bitcoin rewards · missions & events'] as $t => $d): ?>
      <div class="riga"><?= dric_gettone($t, 26) ?><div class="mid"><div class="tit2"><?= $t ?> <span class="tag-verde">earned</span></div><div class="sub"><?= e($d) ?></div></div><div class="val"><?= e(soldi($L['rewards'][$t], $t)) ?></div></div>
      <?php endforeach; ?>
    </div>
    <div class="wact"><button type="button" class="b mini pieno" onclick="apri('velo_transfer')">Transfer</button><button type="button" class="b mini" onclick="apri('velo_sposta_prel')">→ Withdrawal</button><button type="button" class="b mini" onclick="apri('velo_sposta_dep')">→ Deposit</button></div>
  </div>

  <!-- ===== 3. WITHDRAWAL WALLET ===== -->
  <div class="wcard w-pre">
    <div class="wtop"><span class="wico" style="color:#e9e2d3"><?= dric_ui('wallet', 18) ?></span><div style="flex:1;min-width:0"><div class="wname" style="color:#e9e2d3">Withdrawal</div><div class="wsub">what leaves · 0.5% fee · 72h review</div></div><span class="wnum">3</span></div>
    <div class="wbal" style="color:#e9e2d3"><?= e(dollari_base($vPre)) ?><small>value</small></div>
    <div class="wrows">
      <div class="riga"><?= dric_gettone('DUX', 26) ?><div class="mid"><div class="tit2">DUX</div><div class="sub">ready to withdraw</div></div><div class="val"><?= e(soldi($L['prelievo']['DUX'], 'DUX')) ?></div></div>
      <div class="riga"><?= dric_gettone('ERIDAN', 26) ?><div class="mid"><div class="tit2">ERIDAN</div><div class="sub">converted from DUX at launch</div></div><div class="val"><?= e(soldi($L['prelievo']['ERIDAN'], 'ERIDAN')) ?></div></div>
      <div class="riga"><?= dric_gettone('BTC', 26) ?><div class="mid"><div class="tit2">BTC</div><div class="sub">bitcoin · withdraw to your BTC address</div></div><div class="val"><?= e(soldi($L['prelievo']['BTC'], 'BTC')) ?></div></div>
      <div class="riga"><?= dric_ui('link', 22) ?><div class="mid"><div class="tit2">External wallet</div><div class="sub"><?= $bound ? e(substr($bound, 0, 8)) . '…' . e(substr($bound, -6)) : 'not bound yet' ?></div></div><a class="b mini" href="account.php?s=bind" style="margin:0;text-decoration:none"><?= $bound ? 'Manage' : 'Bind' ?></a></div>
    </div>
    <div class="wact"><button type="button" class="b mini pieno" onclick="apri('velo_withdraw')">Withdraw</button><button type="button" class="b mini" disabled style="opacity:.5">→ ERIDAN</button></div>
  </div>

  <!-- ===== 4. OFFSET WALLET ===== -->
  <div class="wcard w-off">
    <div class="wtop"><span class="wico"><?= dric_ui('vincolo', 18) ?></span><div style="flex:1;min-width:0"><div class="wname">Offset</div><div class="wsub">vouchers · up to 10% of each activation</div></div><span class="wnum">4</span></div>
    <div class="wbal"><?= e(dollari_base($vOff)) ?><small>value</small></div>
    <div class="wrows">
      <div class="riga riga-off"><?= dric_gettone('DUX', 26) ?><div class="mid"><div class="tit2">DUX <span class="tag-off">offset</span></div><div class="sub">30% of tools · 20% classic · 15% prestige</div></div><div class="val"><?= e(soldi($VINC, 'DUX')) ?></div></div>
      <?php foreach (['DRX' => 'voucher on vault / staking', '81X' => 'voucher on vault / staking'] as $t => $d): ?>
      <div class="riga riga-off"><?= dric_gettone($t, 26) ?><div class="mid"><div class="tit2"><?= $t ?> <span class="tag-off">offset</span></div><div class="sub"><?= e($d) ?></div></div><div class="val"><?= e(soldi($L['offset'][$t] ?? '0', $t)) ?></div></div>
      <?php endforeach; ?>
    </div>
    <div class="wact"><a class="b mini pieno" href="prodotti.php">Use in a tool</a><a class="b mini" href="restricted.php">Details</a></div>
  </div>
  </div>
  <style>
    .wgrid{display:grid;grid-template-columns:1fr;gap:12px;margin-bottom:12px}
    @media(min-width:640px){.wgrid{grid-template-columns:repeat(2,1fr)}}
    @media(min-width:1024px){.wgrid{grid-template-columns:repeat(4,1fr)}}
    .wcard{position:relative;display:flex;flex-direction:column;height:352px;border-radius:18px;padding:14px 14px 12px;overflow:hidden;
      background:linear-gradient(160deg,#1a150c 0%,#0f0c07 45%,#15100a 100%);
      border:1px solid rgba(217,180,90,.35);
      box-shadow:inset 1px 1px 0 rgba(255,236,190,.28),inset -1px -1px 0 rgba(0,0,0,.7),0 10px 26px -12px rgba(0,0,0,.9),0 0 0 1px rgba(0,0,0,.6);
      transform:translateZ(0);transition:transform .25s,box-shadow .25s}
    .wcard:before{content:"";position:absolute;inset:0;background:radial-gradient(120% 60% at 0% 0%,rgba(242,219,164,.16),transparent 55%),radial-gradient(80% 50% at 100% 100%,rgba(217,180,90,.10),transparent 60%);pointer-events:none}
    .wcard:after{content:"";position:absolute;top:-60%;left:-40%;width:60%;height:220%;background:linear-gradient(105deg,transparent 0,rgba(255,255,255,.07) 45%,rgba(255,255,255,.14) 50%,rgba(255,255,255,.07) 55%,transparent 100%);transform:rotate(8deg);animation:wshine 7s ease-in-out infinite;pointer-events:none}
    @keyframes wshine{0%,60%{left:-60%}100%{left:140%}}
    .wcard:hover{transform:translateY(-3px);box-shadow:inset 1px 1px 0 rgba(255,236,190,.35),inset -1px -1px 0 rgba(0,0,0,.7),0 18px 34px -14px rgba(217,180,90,.35),0 0 0 1px rgba(0,0,0,.6)}
    .w-rew{border-color:rgba(255,255,255,.35)} .w-pre{border-color:rgba(233,226,211,.35)} .w-off{border-color:rgba(217,180,90,.25)}
    .wtop{display:flex;align-items:center;gap:8px;position:relative;z-index:1}
    .wico{width:32px;height:32px;border-radius:10px;display:grid;place-items:center;flex:none;color:var(--oro);background:linear-gradient(145deg,#2a2212,#0c0a06);box-shadow:inset 1px 1px 0 rgba(255,236,190,.25),inset -1px -1px 0 rgba(0,0,0,.8)}
    .wname{font-family:Cinzel,serif;font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:var(--oro-chiaro)}
    .wsub{font-size:9.5px;color:var(--tenue);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .wnum{font-family:Cinzel,serif;font-size:22px;color:rgba(217,180,90,.18);font-weight:700}
    .wbal{position:relative;z-index:1;font-family:Cinzel,serif;font-size:20px;color:var(--oro-chiaro);margin:6px 0 4px;letter-spacing:.02em;font-variant-numeric:tabular-nums;text-shadow:0 1px 0 rgba(0,0,0,.8)}
    .wbal small{font:600 9px Inter,sans-serif;letter-spacing:.14em;text-transform:uppercase;color:var(--tenue);margin-left:6px}
    .wcard .wrows{position:relative;z-index:1;flex:1;overflow:hidden;max-height:none;padding-right:0;-webkit-mask-image:none;scrollbar-width:none}
    .wcard .wrows::-webkit-scrollbar{display:none}
    .wcard .wrows .riga{padding:4px 0;min-height:36px;}
    .wcard .wrows .riga .sub{font-size:9px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis} .wcard .wrows .riga .tit2{font-size:12px} .wcard .wrows .riga .val{font-size:12px}
    .wcard .wrows .riga{border-bottom:1px solid rgba(217,180,90,.08);margin:0}
    .wrows .riga:last-child{border-bottom:0}
    .wact{position:relative;z-index:1;display:flex;gap:6px;margin-top:6px;flex-wrap:nowrap}
    .wact .b{margin:0;flex:1;text-decoration:none;text-align:center;font-size:9.5px;padding:8px 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .wgrid + .carta{margin-top:0}
  </style>
  <div class="griglia-prod">
  <div class="carta" id="swapCard">
    <div class="eti">Swap — internal, fee 0 (Deposit wallet)</div>
    <div class="sotto" style="margin:4px 0 2px">DUX ↔ DRX 1:1 · DRX ↔ 81X 1000:1 · instant, no gas.</div>
    <form method="post" data-pin="Confirm the swap"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="swap">
      <div class="due">
        <div><label for="cda">From</label><select id="cda" name="da"><option>DUX</option><option>DRX</option><option>81X</option></select></div>
        <div><label for="cad">To</label><select id="cad" name="ad"><option>DRX</option><option>DUX</option><option>81X</option></select></div>
      </div>
      <label for="cq">Amount</label><input id="cq" name="q" type="number" step="0.000001" min="0.000001" value="10" required>
      <button class="b"><?= dric_ui('swap', 16) ?>Swap — asks your PIN</button></form>
  </div>

  <div class="carta" id="transferCard">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px"><span class="pal" style="color:#fff"><?= dric_ui('send', 16) ?></span><div style="flex:1"><div class="eti" style="color:#fff">Transfer wallet — between members</div><div class="sub">DUX · DRX · 81X from your Rewards wallet → their Rewards wallet. Instant, no gas.</div></div></div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:6px 0 4px">
      <?php foreach (['DUX','DRX','81X'] as $t): ?><div class="td" style="background:rgba(217,180,90,.05);border:1px solid var(--bordo);border-radius:9px;padding:6px 8px;font-size:9px;color:var(--tenue);display:flex;align-items:center;gap:6px"><?= dric_gettone($t, 20) ?><div>sendable<b style="display:block;font-size:12px;color:#f2e9d8"><?= e(soldi($L['rewards'][$t], $t)) ?></b></div></div><?php endforeach; ?>
    </div>
    <form method="post" data-pin="Confirm the transfer"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="trasferisci">
      <label for="td2">Recipient</label><input id="td2" name="dest" placeholder="username, email or ID" list="utenti" required autocomplete="off">
      <div class="due">
        <div><label for="tt2">Token</label><select id="tt2" name="tok"><option>DUX</option><option>DRX</option><option>81X</option></select></div>
        <div><label for="tq2">Amount</label><input id="tq2" name="q" type="number" step="0.000001" min="0.000001" value="10" required></div>
      </div>
      <button class="b pieno"><?= dric_ui('lucchetto', 16) ?>Send — asks your PIN</button></form>
    <?php if ($CODA): ?>
    <details style="margin-top:10px"><summary class="sub" style="cursor:pointer;color:var(--oro-chiaro)">Signing queue (<?= count($CODA) ?>) — withdrawals and mints waiting for the offline signature</summary>
    <?php foreach ($CODA as $c): ?>
      <div class="riga"><span class="pal">≡</span>
        <div class="mid"><div class="tit2"><?= e(soldi((string)$c['importo'], (string)$c['token'])) ?> <?= e((string)$c['token']) ?> → <?= e(substr((string)$c['destinazione'], 0, 12)) ?>…</div>
          <div class="sub"><?= e(gmdate('d M H:i', (int)$c['creata'])) ?> · <?= e((string)$c['nota']) ?><?= $c['tx_hash'] ? ' · ' . e(substr((string)$c['tx_hash'], 0, 14)) . '…' : '' ?></div></div>
        <div class="val" style="color:var(--oro)"><?= $c['stato'] === 'in-attesa' ? (demo_coda_pronta($c) ? 'REVIEW DONE' : 'IN REVIEW') : 'EXECUTED' ?></div></div>
      <?php if ($c['stato'] === 'in-attesa' && !demo_coda_pronta($c)): ?>
        <div class="sub" style="margin:-4px 0 8px 41px;color:var(--tenue)">Review ends <?= e(gmdate('d M H:i', (int)$c['creata'] + DEMO_ATTESA_PRELIEVO)) ?> UTC (72h). Then it is signed and sent.</div>
      <?php endif; ?>
      <?php if ($c['stato'] === 'in-attesa' && (demo_coda_pronta($c) || is_file(__DIR__ . '/DEMO-APERTA.flag'))): ?>
        <form method="post" style="margin:-4px 0 8px 41px"><input type="hidden" name="csrf" value="<?= e($G) ?>">
          <input type="hidden" name="a" value="firma_finta"><input type="hidden" name="cid" value="<?= (int)$c['id'] ?>">
          <button class="b mini">Sign (simulate PC) →</button></form>
      <?php endif; ?>
    <?php endforeach; ?>
    </details>
    <?php endif; ?>
  </div>

  <div class="carta">
    <div class="eti" style="margin-bottom:8px">Transaction history (<?= count($MOV) ?>)</div>
    <div class="wrows" style="max-height:300px">
    <?php foreach (array_slice($MOV, 0, 60) as $m): $neg = str_starts_with((string)$m['importo'], '-'); ?>
      <div class="riga"><div class="mid">
        <div class="tit2"><?= e((string)$m['descrizione']) ?></div>
        <div class="sub"><?= e(gmdate('d M Y H:i', (int)$m['quando'])) ?> · <?= e(causale_en((string)$m['causale'])) ?><?= $m['tx_hash'] ? ' · on-chain' : '' ?></div></div>
        <div class="val <?= $neg ? 'meno' : 'piu' ?>"><?= $neg ? '' : '+' ?><?= e(soldi((string)$m['importo'], (string)$m['token'])) ?> <?= e((string)$m['token']) ?></div></div>
    <?php endforeach; ?>
    </div>
    <a class="b" style="margin-top:12px;text-decoration:none" href="estratto.php"><?= dric_ui('scarica', 18) ?>Download CSV</a>
  </div>

  </div>
  <div class="franco"><b>Deposited DUX never leave.</b> They activate memberships and internal tools. <b style="color:#ffffff">Earned DUX</b> — what memberships and tools produce — are withdrawable after a 72-hour review. DUX offset are membership-only. Figures in $ are a reading convention: 1 DUX = 1 USDT on deposit.</div>
</section>

<!-- ================= MOVE: Rewards -> Withdrawal ================= -->
<div class="velo" id="velo_sposta_prel"><div class="modale">
  <h3><?= dric_ui('wallet', 18) ?> Rewards → Withdrawal <button type="button" class="x" onclick="chiudi('velo_sposta_prel')"><?= dric_ui('chiudi', 20) ?></button></h3>
  <div class="sotto">Move earned DUX to your Withdrawal wallet. From there you withdraw (72h review) or convert to ERIDAN. Earned available: <b style="color:#ffffff"><?= e(soldi(demo_saldo_guadagnato($IO), 'DUX')) ?> DUX</b>.<?php if (function_exists('v4_funded_disp') && v4_on()): ?> <b>V4:</b> only funded DUX can move — funded now <b style="color:#7fd08a"><?= e(led_umano(v4_funded_disp($IO), 'DUX')) ?> DUX</b>; the rest is eligible for the next monthly Cash Reward Pool run (never guaranteed).<?php endif; ?></div>
  <form method="post" data-pin="Confirm the move to Withdrawal"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="sposta_prelievo">
    <label for="mq">DUX</label><input id="mq" name="q" type="number" step="0.000001" min="0.000001" value="<?= e(soldi(demo_saldo_guadagnato($IO), 'DUX') === '0' ? '1' : str_replace(',', '', soldi(demo_saldo_guadagnato($IO), 'DUX'))) ?>" required>
    <button class="b pieno"><?= dric_ui('lucchetto', 16) ?>Move — asks your PIN</button></form>
</div></div>
<!-- ================= MOVE: Rewards -> Deposit ================= -->
<div class="velo" id="velo_sposta_dep"><div class="modale">
  <h3><?= dric_ui('swap', 18) ?> Rewards → Deposit <button type="button" class="x" onclick="chiudi('velo_sposta_dep')"><?= dric_ui('chiudi', 20) ?></button></h3>
  <div class="sotto">Move earned DRX or 81X to your Deposit wallet to use them in the tools (vault, staking, gamification).</div>
  <form method="post" data-pin="Confirm the move to Deposit"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="sposta_deposito">
    <label for="mt">Token</label><select id="mt" name="tok"><option>DRX</option><option>81X</option></select>
    <label for="mq2">Amount</label><input id="mq2" name="q" type="number" step="0.000001" min="0.000001" value="1" required>
    <button class="b pieno"><?= dric_ui('lucchetto', 16) ?>Move — asks your PIN</button></form>
</div></div>

<?php $DEP = demo_indirizzo_deposito($IO); ?>
<!-- ================= DEPOSIT (popup) ================= -->
<div class="velo" id="velo_deposit"><div class="modale" style="text-align:center">
  <h3><?= dric_ui('scarica', 18) ?> Deposit USDT <button type="button" class="x" onclick="chiudi('velo_deposit')"><?= dric_ui('chiudi', 20) ?></button></h3>
  <div class="medio" style="justify-content:center;font-size:14px;margin:4px 0 2px;color:var(--oro-chiaro)"><?= dric_gettone('USDT', 24) ?> USDT · <b>POLYGON</b> network only</div>
  <div class="sotto" style="color:#f2dba4;font-weight:600">USDT sent from any other network is lost.</div>
  <div id="qrDep" style="background:#fff;padding:12px;border-radius:14px;width:212px;margin:12px auto 8px;display:grid;place-items:center;min-height:212px"></div>
  <div style="font:700 12px/1.6 Inter,monospace;word-break:break-all;color:var(--oro-chiaro)"><?= e($DEP['indirizzo']) ?></div>
  <button type="button" class="b pieno" onclick="copia('<?= e($DEP['indirizzo']) ?>',this)"><?= dric_ui('copia', 16) ?>Copy address</button>
  <div class="sotto" style="margin-top:8px"><?= $DEP['personale'] ? 'This address belongs to <b style="color:var(--oro-chiaro)">your account</b>. ' : 'Deposit address of BLOCKCHAINPLUS.DAO — credit is matched to your account. ' ?>1 USDT = 1 DUX after network confirmations. <a href="deposit.php" style="color:var(--oro-chiaro)">Full page →</a></div>
</div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>window.qrDep=function(){var q=document.getElementById('qrDep');if(q&&!q.dataset.ok&&window.QRCode){new QRCode(q,{text:<?= json_encode($DEP['indirizzo']) ?>,width:188,height:188,colorDark:'#000',colorLight:'#fff',correctLevel:QRCode.CorrectLevel.M});q.dataset.ok=1;}};</script>

<!-- ================= SWAP (popup) ================= -->
<div class="velo" id="velo_swap"><div class="modale">
  <h3><?= dric_ui('swap', 18) ?> Swap <button type="button" class="x" onclick="chiudi('velo_swap')"><?= dric_ui('chiudi', 20) ?></button></h3>
  <div class="sotto">Internal, fee 0, instant — inside your Deposit wallet. DUX ↔ DRX 1:1 · DRX ↔ 81X 1000:1.</div>
  <form method="post" data-pin="Confirm the swap"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="swap">
    <div class="due">
      <div><label for="sda">From</label><select id="sda" name="da"><option>DUX</option><option>DRX</option><option>81X</option></select></div>
      <div><label for="sad">To</label><select id="sad" name="ad"><option>DRX</option><option>DUX</option><option>81X</option></select></div>
    </div>
    <label for="sq">Amount</label><input id="sq" name="q" type="number" step="0.000001" min="0.000001" value="10" required>
    <button class="b pieno"><?= dric_ui('swap', 16) ?>Swap — asks your PIN</button></form>
</div></div>

<!-- ================= TRANSFER ================= -->
<div class="velo" id="velo_transfer"><div class="modale">
  <h3><?= dric_ui('send', 18) ?> Transfer <button type="button" class="x" onclick="chiudi('velo_transfer')"><?= dric_ui('chiudi', 20) ?></button></h3>
  <div class="sotto">Internal, instant, no gas. Only <b style="color:#ffffff">Rewards-wallet</b> tokens can be sent, and they land in the recipient's Rewards wallet. Recipient by username, email or ID.</div>
  <form method="post" data-pin="Confirm the transfer"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="trasferisci">
    <label for="td">Recipient</label><input id="td" name="dest" placeholder="username, email or ID" list="utenti" required autocomplete="off">
    <datalist id="utenti"><?php foreach (demo_persone() as $u => $p) if ($u !== $IO) echo '<option value="' . e($u) . '">' . e($p['nome']) . '</option>'; ?></datalist>
    <label for="tt">Token</label><select id="tt" name="tok"><option>DUX</option><option>DRX</option><option>81X</option></select>
    <label for="tq">Amount</label><input id="tq" name="q" type="number" step="0.000001" min="0.000001" value="100" required>
    <button class="b pieno"><?= dric_ui('lucchetto', 16) ?>Send — asks your PIN</button></form>
</div></div>

<!-- ================= WITHDRAW ================= -->
<div class="velo" id="velo_withdraw"><div class="modale">
  <h3><?= dric_ui('wallet', 18) ?> Withdraw <button type="button" class="x" onclick="chiudi('velo_withdraw')"><?= dric_ui('chiudi', 20) ?></button></h3>
  <?php $TT = function_exists('v3_tetti_prelievo') ? v3_tetti_prelievo() : null; if ($TT): ?><div class="sub" style="margin:0 0 6px;color:var(--oro-chiaro)">Limits (said first): per user <?= number_format($TT['giorno']) ?> DUX / day · <?= number_format($TT['settimana']) ?> / week · <?= number_format($TT['mese']) ?> / month · ecosystem <?= number_format($TT['globale_giorno']) ?> / day. Rolling windows on requests; they protect the treasury for everyone.</div><?php endif; ?>
  <?php /* V4 (17-08-2026): limiti del profilo KYC e stato funded */ if (function_exists('v4_tier_limiti')): $L4 = v4_tier_limiti($IO); $S4 = v4_stato_utente($IO); ?><div class="sub" style="margin:0 0 6px;color:var(--oro-chiaro)">Your profile <b><?= e($L4['tier_nome']) ?></b>: <?= number_format($L4['giorno'], 0) ?> USD-eq./day · <?= number_format($L4['settimana'], 0) ?>/week · <?= number_format($L4['mese'], 2) ?>/month (min of profile, V3 caps, 0.10% of the monthly pool). Only <b>funded</b> DUX can be moved here from Rewards: funded now <b style="color:#7fd08a"><?= e(led_umano($S4['funded_disp'], 'DUX')) ?></b> · eligible waiting for the next Cash Reward Pool run <b><?= e(led_umano($S4['eligible'], 'DUX')) ?></b> DUX. Details in My tools.</div><?php endif; ?>
  <div class="sotto">Withdrawals leave from the <b style="color:#e9e2d3">Withdrawal wallet</b>. Fee 0.5% on the gross. <b>Pending 72 hours</b> for automatic and manual review, then signed offline; you get the tx hash.</div>
  <div class="sotto" style="margin-top:6px">In your Withdrawal wallet: <b style="color:#e9e2d3"><?= e(soldi(demo_strati($IO)['prelievo']['DUX'], 'DUX')) ?> DUX</b> — need more? Move earned DUX from Rewards first.</div>
  <form method="post" data-pin="Confirm the withdrawal"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="preleva">
    <label for="wq">Amount</label><input id="wq" name="q" type="number" step="0.000001" min="0.000001" value="100" required>
    <label for="wi">Destination address</label><input id="wi" name="ind" placeholder="0x…" pattern="0x[0-9a-fA-F]{40}" value="<?= e($bound) ?>" required>
    <button class="b pieno"><?= dric_ui('lucchetto', 16) ?>Withdraw — asks your PIN</button></form>
  <div class="franco">Withdrawn DUX has no liquidity pool outside: it can be held or deposited back. That is the whole list.</div>
</div></div>

<!-- ================= SWAP ================= -->
<div class="velo" id="velo_swap"><div class="modale">
  <h3><?= dric_ui('swap', 18) ?> Swap <button type="button" class="x" onclick="chiudi('velo_swap')"><?= dric_ui('chiudi', 20) ?></button></h3>
  <div class="sotto">Internal swap. DUX ↔ DRX 1:1 · DRX ↔ 81X 1000:1 · fee 0.</div>
  <form method="post" data-pin="Confirm the swap"><input type="hidden" name="csrf" value="<?= e($G) ?>"><input type="hidden" name="a" value="swap">
    <div class="due">
      <div><label for="sda">From</label><select id="sda" name="da"><option>DUX</option><option>DRX</option><option>81X</option></select></div>
      <div><label for="sad">To</label><select id="sad" name="ad"><option>DRX</option><option>DUX</option><option>81X</option></select></div>
    </div>
    <label for="sq">Amount</label><input id="sq" name="q" type="number" step="0.000001" min="0.000001" value="10" required>
    <button class="b pieno"><?= dric_ui('lucchetto', 16) ?>Swap — asks your PIN</button></form>
  <div class="sotto" style="margin-top:8px">More on <a href="swap.php" style="color:var(--oro-chiaro)">Swap &amp; Gamification →</a></div>
</div></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>

</script>
<?php require __DIR__ . '/_piede.php'; ?>
