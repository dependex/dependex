<?php
/**
 * STRUMENTI — user & networker tools (V3, 17-08-2026).
 *  · personal simulator: what a product releases with the 50% withdrawable cap, and when
 *  · my rank (definitive 10-level table): score, what is missing, monthly requirement ×3
 *  · my estimated weight in the Rank Pool
 *  · claim calendar (my active products), withdrawal status (pending / windows), system health (public state)
 * Read-only: nothing here moves anything.
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
$IO = demo_io(); $G = demo_gettone();
$S = demo_stato($IO);
$P = demo_prodotti($IO);
$CAP = function_exists('v3_cap') ? v3_cap() : 0.5;
$FW = function_exists('v3_finestra_prelievi') ? v3_finestra_prelievi() : ['review_ore' => 72, 'finestra_giorni' => 0, 'prossima_finestra' => null, 'firme_giorno' => 500];
/* --- ranghi definitivi (stesse soglie di config/bmm/rank_pool_config.json — immutabili) --- */
$RK = [[1, 'PLANKTON', 0, 999, 0, 0, 0], [2, 'SHRIMP', 1000, 4999, 0, 0, 0], [3, 'CRAB', 5000, 24999, 0, 0, 0], [4, 'OCTOPUS', 25000, 99999, 25000, 0.10, 0.02], [5, 'FISH', 100000, 299999, 100000, 0.08, 0.04], [6, 'DOLPHIN', 300000, 999999, 300000, 0.06, 0.06], [7, 'SHARK', 1000000, 2499999, 1000000, 0.05, 0.07], [8, 'WHALE', 2500000, 4999999, 2500000, 0.04, 0.08], [9, 'HUMPBACK', 5000000, 10000000, 5000000, 0.02, 0.10], [10, 'LEVIATHAN', 10000001, PHP_INT_MAX, 0, 0, 0]];
/* score = DUX attivati pagati cash (mio + rete): usiamo l'XP del registro (attivazioni) come proxy [dichiarato] */
$score = (float)$S['xp']['totale']; $mioScore = (float)$S['xp']['proprio'];
$liv = $RK[0]; foreach ($RK as $r) if ($score >= $r[2]) $liv = $r;
$next = null; foreach ($RK as $r) if ($r[0] === $liv[0] + 1) $next = $r;
// score degli ultimi 3 mesi (mensile ×3): dalle scritture 'membership'/'stake' del registro
$q = led_db()->prepare("SELECT s.quando, s.importo FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.proprietario=? AND c.genere IN ('utente') AND s.token='DUX' AND s.causale IN ('membership','stake') AND CAST(s.importo AS INTEGER)<0 AND s.quando>?"); $q->execute([$IO, time() - 92 * 86400]);
$m3 = [0.0, 0.0, 0.0]; foreach ($q as $r) { $i = min(2, (int)floor((time() - (int)$r['quando']) / (30.4 * 86400))); $m3[$i] += -(float)$r['importo'] / 1e6; }
// peso stimato nel pool: (speed + global) × il mio score mensile / (stessa cosa per tutta la classifica demo)
$CL = demo_classifica(); $totW = 0.0; $mioW = 0.0;
foreach ($CL as $u) { $sc = is_array($u['xp'] ?? null) ? (float)($u['xp']['totale'] ?? 0) : (float)($u['xp'] ?? 0); $lv = $RK[0]; foreach ($RK as $r) if ($sc >= $r[2]) $lv = $r; $w = ($lv[5] + $lv[6]) * $sc; $totW += $w; if (($u['uid'] ?? '') === $IO) $mioW = $w; }
if ($mioW === 0.0) $mioW = ($liv[5] + $liv[6]) * $score;
$coda = led_db()->prepare("SELECT * FROM led_coda WHERE uid=? ORDER BY id DESC LIMIT 10"); $coda->execute([$IO]); $CODA = $coda->fetchAll();
/* salute pubblica del sistema: copertura stimata dai totali (stessa formula del Risk Engine, senza numeri per-utente) */
$db = led_db(); $um = fn(float $b, string $t) => $b / (10 ** led_decimali($t));
$dep = $um((float)$db->query("SELECT SUM(CAST(importo AS REAL)) FROM led_scritture WHERE causale='deposito' AND token='USDT' AND CAST(importo AS REAL)>0")->fetchColumn(), 'USDT');
$paid = $um((float)$db->query("SELECT SUM(CAST(importo AS REAL)) FROM led_coda WHERE stato='confermata' AND token='DUX'")->fetchColumn(), 'DUX');
$queue = $um((float)$db->query("SELECT SUM(CAST(importo AS REAL)) FROM led_coda WHERE stato='in-attesa' AND token='DUX'")->fetchColumn(), 'DUX');
$rew = $um((float)$db->query("SELECT SUM(CAST(s.importo AS REAL)) FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.token='DUX' AND c.genere IN ('guadagnato','prelievo')")->fetchColumn(), 'DUX');
$cash = max(0.0, $dep - $paid); $near = $rew + $queue; $cov = $near > 0 ? $cash / $near : 99.0;
$stato = 'GREEN'; if ($cov < 1.5) $stato = 'YELLOW'; if ($cov < 1.2) $stato = 'ORANGE'; if ($cov < 1.0) $stato = 'RED'; if ($queue > $cash && $queue > 0) $stato = 'BLACK';
$blocchi = []; foreach (['blocco_depositi' => 'deposits blocked', 'blocco_prelievi' => 'withdrawals frozen', 'blocco_attivazioni' => 'activations paused', 'pausa_rilasci' => 'release pause'] as $k => $l) if (function_exists('v3_blocco') && v3_blocco($k)) $blocchi[] = $l;
$COL = ['GREEN' => '#2e8b57', 'YELLOW' => '#d9b45a', 'ORANGE' => '#e07b2a', 'RED' => '#c0392b', 'BLACK' => '#111'];
$F = demo_fasce();
$TIT = 'My tools';
require __DIR__ . '/_testa.php';
?>
<section class="vista on">
  <div class="carta" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap"><div style="flex:1;min-width:200px"><div class="eti">System health — public</div><div class="medio" style="font-size:18px;color:<?= $COL[$stato] ?>"><?= $stato ?></div><div class="sub">coverage <?= number_format(min($cov, 99), 2) ?> = operating cash / (rewards + withdrawal queue) · same rule as the admin risk engine · <?= $blocchi ? 'active blocks: ' . e(implode(', ', $blocchi)) : 'no block active' ?></div></div>
    <div class="sub" style="max-width:360px">GREEN ≥ 1.5 · YELLOW < 1.5 · ORANGE < 1.2 · RED < 1.0 · BLACK = queue above cash. Transparent by design: you see what the admin sees.</div></div>

  <div class="carta"><div class="eti">Personal simulator — what a product releases (V3 cap <?= (int)($CAP * 100) ?>% withdrawable)</div>
    <div class="griglia3" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:8px;margin-top:8px">
      <div><label>Product</label><select id="sP"><?php foreach ($F as $k => $f): if (!empty($f['prestige'])) continue; ?><option value="<?= e($k) ?>" data-p="<?= (int)$f['costo'] ?>" data-pd="<?= (int)$f['pd'] ?>" data-m="<?= (float)$f['molt'] ?>" data-d="<?= (int)$f['giorni'] ?>"><?= e($f['nome']) ?> — <?= (int)$f['costo'] ?> DUX</option><?php endforeach; ?>
        <?php foreach ($F as $k => $f): if (empty($f['prestige'])) continue; ?><option value="<?= e($k) ?>" data-p="<?= (int)$f['costo'] ?>" data-pd="<?= (int)$f['pd'] ?>" data-m="<?= (float)$f['molt'] ?>" data-d="<?= (int)$f['giorni'] ?>"><?= e($f['nome']) ?> — <?= (int)$f['costo'] ?> DUX (10+ NFT)</option><?php endforeach; ?></select></div>
      <div><label>Quantity</label><input id="sN" type="number" min="1" max="100" value="1"></div>
      <div><label>Paid with (cash share)</label><select id="sC"><option value="1">deposited DUX (cash) 100%</option><option value="0.9">90% cash + 10% offset</option><option value="0">rewards / offset / swapped (0% cash)</option></select></div>
    </div>
    <div id="sOut" class="sub" style="margin-top:8px;line-height:1.7"></div>
    <div class="sub" style="margin-top:4px">Not a yield: the ceiling is internal credit; only ≤ <?= (int)($CAP * 100) ?>% of the cash you paid becomes withdrawable DUX (0.5% fee, <?= (int)$FW['review_ore'] ?> h review). Activations paid with rewards, offset or swapped tokens release only Offset DUX.</div></div>

  <?php require_once __DIR__ . '/_media.php'; $__RK = [0=>'plankton',1=>'shrimp',2=>'crab',3=>'octopus',4=>'fish',5=>'dolphin',6=>'shark',7=>'whale',8=>'humpback',9=>'leviathan']; $__lvb = (int)($S['rango']['livello'] ?? 0); ?>
  <?php $__cr = media_get('rank_' . ($__RK[$__lvb] ?? 'plankton') . '_hero'); $__bg = ($__cr && media_has($__cr, 'source_original')) ? "background-image:linear-gradient(90deg,rgba(8,7,5,.94) 40%,rgba(8,7,5,.35)),url('" . e(media_url((string)$__cr['source_original'])) . "');background-size:cover;background-position:right center;" : ''; ?>
  <div class="carta" style="<?= $__bg ?>"><div class="eti" style="display:flex;align-items:center;gap:8px"><?= dric_rango($__lvb, 34) ?><?php $__pl = (int)($S['prestigio']['livello'] ?? 0); $__nf = (int)($S['prestigio']['nft'] ?? 0); echo $__pl > 0 ? dric_prestigio($__pl, $__nf, 28) : ''; ?><span>My rank — definitive ladder (thresholds are immutable)</span></div>
    <div class="medio" style="font-size:17px">Lv <?= $liv[0] ?> · <?= $liv[1] ?></div>
    <div class="sub">score <?= number_format($score, 0) ?> (own <?= number_format($mioScore, 0) ?> · network <?= number_format($score - $mioScore, 0) ?>) · shown as DUX activated in the ledger [proxy of cash sales: internal activations will not count in V3 scoring]</div>
    <?php if ($next): $manca = max(0, $next[2] - $score); ?>
      <div class="barra" style="margin:8px 0 4px"><i style="width:<?= min(100, (int)(100 * ($score - $liv[2]) / max(1, $next[2] - $liv[2]))) ?>%"></i></div>
      <div class="sub">next: <b><?= $next[1] ?></b> at <?= number_format($next[2]) ?> — missing <?= number_format($manca) ?><?= $next[4] ? ' · monthly requirement ' . number_format($next[4]) . ' for 3 consecutive months' : '' ?></div>
    <?php endif; ?>
    <div class="sub" style="margin-top:6px">Last 3 months (own activations): <?= implode(' · ', array_map(fn($x) => number_format($x, 0), array_reverse($m3))) ?> DUX<?= $liv[4] ? ' · requirement to keep ' . $liv[1] . ': ' . number_format($liv[4]) . '/month ×3 (' . (min($m3) >= $liv[4] ? 'met' : 'NOT met — demotion of one level at the quarterly check, never below CRAB') . ')' : '' ?></div>
    <div class="sub" style="margin-top:6px">Speed <?= (int)($liv[5] * 100) ?>% · Global <?= (int)($liv[6] * 100) ?>% (3 depths) — these are <b>Rank Points</b>: weights inside a funded Rank Pool = MIN(12% net cash revenue, 30% contribution margin, budget). Never a fixed % of volume.</div></div>

  <div class="carta"><div class="eti">My estimated weight in the Rank Pool</div>
    <div class="medio" style="font-size:17px"><?= $totW > 0 ? number_format(100 * $mioW / $totW, 2) . '%' : '—' ?></div>
    <div class="sub">= (Speed + Global of my level) × my score, over the same sum for everyone in the ranking. The pool itself depends on the month's margin: if the margin is zero the pool is zero.</div></div>

  <div class="carta"><div class="eti">Claim calendar — my active products</div>
    <?php $any = false; foreach ($P as $x) { if ((int)($x['attivo'] ?? 1) === 0 || !empty($x['finita'])) continue; $any = true; $cap = (string)($x['cash_pagato'] ?? ''); $tetto = $cap !== '' ? (float)$cap / 1e6 * $CAP : null; $dato = (float)($x['prelevabile_dato'] ?? 0) / 1e6; ?>
      <div class="riga" style="padding:7px 0"><div class="mid"><div class="tit2"><?= e((string)$x['etichetta']) ?> · <?= e((string)$x['capitale']) ?> <?= e((string)$x['token']) ?></div>
        <div class="sub">day <?= (int)$x['giorni_fatti'] ?>/<?= (int)$x['giorni'] ?> · <?= (int)$x['avanzamento'] ?>% · ends <?= e(gmdate('d M Y', (int)$x['scade'])) ?> · matured now <?= e(soldi((string)$x['maturato'], (string)$x['token'])) ?><?= $tetto !== null ? ' · withdrawable cap ' . number_format($tetto, 2) . ' DUX (given ' . number_format($dato, 2) . ')' : ($x['genere'] === 'membership' ? ' · pre-V3 contract: frozen terms, no cap' : '') ?></div></div></div>
    <?php } if (!$any) echo '<div class="sub" style="margin-top:4px">No active product. Claims never ask the PIN; production runs every day.</div>'; ?></div>

  <?php /* V4 (17-08-2026): stato funded/eligible e limiti del profilo KYC — detti prima, in chiaro */ if (function_exists('v4_stato_utente')): $V4 = v4_stato_utente($IO); $L4 = $V4['tier']; $LR = $V4['ultimo_run']; ?>
  <div class="carta" style="border-color:rgba(217,180,90,.5)"><div class="eti">My funded reward — V4 (said first: never guaranteed, can be zero)</div>
    <div class="griglia3" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:8px;margin-top:6px">
      <div class="carta" style="margin:0;padding:8px 10px"><div class="sub">Rewards DUX (utility)</div><b style="font-size:14px"><?= e(led_umano($V4['rewards'], 'DUX')) ?></b><div class="sub">matured under the <?= (int)($CAP * 100) ?>% cap = maximum convertible</div></div>
      <div class="carta" style="margin:0;padding:8px 10px"><div class="sub">Eligible, waiting for funding</div><b style="font-size:14px;color:#f2dba4"><?= e(led_umano($V4['eligible'], 'DUX')) ?></b><div class="sub">next Cash Reward Pool run <?= e(gmdate('d M Y', (int)$V4['prossimo_run'])) ?></div></div>
      <div class="carta" style="margin:0;padding:8px 10px"><div class="sub">Funded, ready to move → Withdrawal</div><b style="font-size:14px;color:#7fd08a"><?= e(led_umano($V4['funded_disp'], 'DUX')) ?></b><div class="sub">funded so far <?= e(led_umano($V4['funded_cum'], 'DUX')) ?> DUX</div></div>
      <div class="carta" style="margin:0;padding:8px 10px"><div class="sub">Last pool run</div><b style="font-size:14px"><?= $LR ? number_format((float)$LR['tasso'] * 100, 1) . '% funded' : 'none yet' ?></b><div class="sub"><?= $LR ? e(gmdate('d M Y', (int)$LR['quando'])) . ' · pool ' . e(led_umano((string)$LR['pool'], 'DUX')) . ' DUX for ' . (int)$LR['utenti'] . ' users' : 'no conversion has happened' ?></div></div>
    </div>
    <div class="sub" style="margin-top:8px">How it works: what your products release (with the <?= (int)($CAP * 100) ?>% cap) is <b>DUX Reward Utility</b> — usable in tools, Academy, NFT, events, and the maximum that can ever become withdrawable. Once a month the DAO funds a <b>Cash Reward Pool</b> = MIN(10% of net external revenue, 30% of contribution margin, 10% of Ocean Shield liquidity above target, real Flow Settlement Vault balance, approved budget) × treasury semaphore; pool ÷ eligible points = the share funded that month. Only <b>funded DUX</b> move to the Withdrawal wallet. The share can be 100%, 30% or 0%: it is not a yield, not a promise.</div>
    <div class="sub" style="margin-top:6px;color:var(--oro-chiaro)">My withdrawal profile: <b><?= e($L4['tier_nome']) ?></b> — <?= number_format($L4['giorno'], 0) ?> USD-eq./day (<?= e($L4['giorno_vincolo']) ?>) · <?= number_format($L4['settimana'], 0) ?>/week (<?= e($L4['settimana_vincolo']) ?>) · <?= number_format($L4['mese'], 2) ?>/month (<?= e($L4['mese_vincolo']) ?>). Profiles: New 25/100/250 · Verified 50/200/500 · Contributor 100/400/1,000 · Professional 250/1,000/2,500 · Business KYB 500/2,000/5,000 — always ≤ the V3 caps and ≤ 0.10% of the monthly global pool. Higher tier = KYC in Account, set by the admin.</div>
  </div>
  <?php endif; ?>
  <div class="carta"><div class="eti">My withdrawals — status & windows</div>
    <?php $TT = function_exists('v3_tetti_prelievo') ? v3_tetti_prelievo() : null; if ($TT): ?><div class="sub" style="margin:4px 0 2px;color:var(--oro-chiaro)">Your limits: <?= number_format($TT['giorno']) ?> DUX/day · <?= number_format($TT['settimana']) ?>/week · <?= number_format($TT['mese']) ?>/month — used today <?= led_umano(v3_prelevato_da($IO, 86400), 'DUX') ?>, this week <?= led_umano(v3_prelevato_da($IO, 7 * 86400), 'DUX') ?>, this month <?= led_umano(v3_prelevato_da($IO, 30 * 86400), 'DUX') ?> · ecosystem today <?= led_umano(v3_prelevato_da('', 86400), 'DUX') ?> / <?= number_format($TT['globale_giorno']) ?>.</div><?php endif; ?>
    <div class="sub" style="margin:4px 0 6px">Review <?= (int)$FW['review_ore'] ?> h · <?= $FW['finestra_giorni'] > 0 ? 'signing window every ' . (int)$FW['finestra_giorni'] . ' days (next ' . e(gmdate('d M Y', (int)$FW['prossima_finestra'])) . ')' : 'no fixed window: signed after review' ?> · signing capacity ≈ <?= number_format((int)$FW['firme_giorno']) ?> tx/day<?= function_exists('v3_blocco') && v3_blocco('blocco_prelievi') ? ' · <b style="color:#e07b2a">withdrawals currently frozen</b>' : '' ?></div>
    <?php if (!$CODA): ?><div class="sub">No withdrawal yet.</div><?php else: foreach ($CODA as $c): ?>
      <div class="riga" style="padding:6px 0"><div class="mid"><div class="tit2"><?= e(led_umano((string)$c['importo'], (string)$c['token'])) ?> <?= e((string)$c['token']) ?> · <?= e((string)$c['stato']) ?></div><div class="sub"><?= e(gmdate('d M Y H:i', (int)$c['creata'])) ?> · <?= (string)$c['stato'] === 'in-attesa' ? 'review until ' . e(gmdate('d M Y H:i', (int)$c['creata'] + DEMO_ATTESA_PRELIEVO)) . ' UTC' : e((string)($c['tx_hash'] ?? '')) ?></div></div></div>
    <?php endforeach; endif; ?></div>
</section>
<script>
(function(){ var P=document.getElementById('sP'),N=document.getElementById('sN'),C=document.getElementById('sC'),O=document.getElementById('sOut'),CAP=<?= json_encode($CAP) ?>;
  function f(n,d){return Number(n).toLocaleString('en-US',{maximumFractionDigits:d==null?0:d});}
  function go(){ var o=P.options[P.selectedIndex]; var p=+o.dataset.p, pd=+o.dataset.pd, m=+o.dataset.m, d=+o.dataset.d, n=Math.max(1,+N.value||1), cs=+C.value; var tot=p*m*n, cash=p*n*cs, w=Math.min(tot*0.8, cash*CAP), off=tot-w, day=p*n*pd/1000, dCap=w>0? Math.ceil(w/(day*0.8)) : 0;
    O.innerHTML='Total release <b>'+f(tot)+' DUX</b> over <b>'+d+' days</b> ('+f(day,2)+' DUX/day). Withdrawable part: <b>'+f(w)+' DUX</b> ('+(cash>0?f(100*w/cash,0)+'% of the '+f(cash)+' DUX you pay in cash':'0% — no cash paid')+'), reached around day '+dCap+'. The rest — <b>'+f(off)+' DUX</b> — is Offset DUX (+ DRX where the split says so): usable for tools, Academy, NFT and events, never withdrawable. Net if you withdraw everything withdrawable: '+f(w*0.995,2)+' DUX after the 0.5% fee.'; }
  [P,N,C].forEach(function(x){x.addEventListener('input',go);x.addEventListener('change',go);}); go(); })();
</script>
<?php require __DIR__ . '/_piede.php'; ?>
