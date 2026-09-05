<?php
/* GUARDIE SUGLI INCLUDE FACOLTATIVI — 2026-08-15 (Cowork)
   Qui sotto c'erano otto `@require_once`. La chiocciola sembra dire "se non
   c'e', pazienza": non e' vero. In PHP un require di un file che non esiste
   resta un errore fatale, chiocciola o no — verificato, ed e' lo stesso guasto
   gia' corretto in dr-webinar.php.
   Questa e' la pagina piu' visitata del sito: il giorno che uno di quegli otto
   file manca (un deploy a meta', un file rinominato), va giu' tutta.
   Ora ognuno si carica solo se c'e' davvero. Una riga per include, nessuna
   logica cambiata. */

/* ============================================================================
   DASHBOARD UTENTE — Destino Randagio (grafica DeepSeek, dati REALI DR).
   Cablata a: drx.php, referral.php, billing.php, gamification.php,
   inc/dr-gami-widgets.php, orders, nft_holdings, onchain_hashes.
   Gate: se non loggato -> accedi.php. Mobile-first. Emoji-zero non applicato
   (dashboard privata, icone inline). ============================================ */
if (session_status() === PHP_SESSION_NONE) @session_start();
require __DIR__.'/db.php';
require_once __DIR__.'/drx.php';
if (is_file(__DIR__.'/referral.php')) { @require_once __DIR__.'/referral.php'; }
if (is_file(__DIR__.'/billing.php')) { @require_once __DIR__.'/billing.php'; }
if (is_file(__DIR__.'/dr-web3-db.php')) { @require_once __DIR__.'/dr-web3-db.php'; }
if (is_file(__DIR__.'/gamification.php')) { @require_once __DIR__.'/gamification.php'; }
require_once __DIR__.'/inc/dr-gami-widgets.php';
if (is_file(__DIR__.'/drx-explorer.php')) { @require_once __DIR__.'/drx-explorer.php'; }
if (is_file(__DIR__.'/drx-vault.php')) { @require_once __DIR__.'/drx-vault.php'; }
if (is_file(__DIR__.'/dr-gamification-drx.php')) { @require_once __DIR__.'/dr-gamification-drx.php'; } // CC-0055: daily login + airdrop claim + drg_leaderboard_render
if (is_file(__DIR__.'/dr-drx-scelta.php')) { @require_once __DIR__.'/dr-drx-scelta.php'; } // CC-0062: scelta wallet/carriera al claim
if (is_file(__DIR__.'/dr-fees.php')) { @require_once __DIR__.'/dr-fees.php'; } // CC-0063: fee 1% + burn on ogni claim
/* INNESTO PHP_GENESYS: Covo/calendario, carta NFT Pioniere, scala prestigio */
if (is_file(__DIR__.'/genesys/dr-calendario.php')) { @require_once __DIR__.'/genesys/dr-calendario.php'; }
if (is_file(__DIR__.'/genesys/dr-nft-card.php')) { @require_once __DIR__.'/genesys/dr-nft-card.php'; }
if (is_file(__DIR__.'/genesys/dr-prestigio.php')) { @require_once __DIR__.'/genesys/dr-prestigio.php'; }
/* FIX 2026-08-12 (Cowork): controllo dashboard - accende/spegne sezioni per utente/gruppo/tutti dal pannello admin */
if (is_file(__DIR__.'/genesys/dashboard-control-lib.php')) { @require_once __DIR__.'/genesys/dashboard-control-lib.php'; }

if (empty($_SESSION['uid'])) { header('Location: accedi.php'); exit; }
$uid = (int)$_SESSION['uid'];
/* FIX 2026-08-12: stato sezioni per QUESTO utente, risolto una volta sola qui.
   Fail-open per design (vedi dashctl_is_active): se la libreria non carica per
   qualsiasi motivo, $__dc torna un default che mostra tutto quello che era
   visibile prima di oggi -> mai una pagina rotta per un problema del
   pannello di controllo. */
$__dc = [];
try{ if(function_exists('dashctl_user_state')) $__dc = dashctl_user_state($pdo,$uid); }catch(Throwable $e){}
$__dcDefault = ['wallet'=>true,'rank'=>true,'missions'=>true,'network'=>true,'staking'=>false,'nft'=>false,'membership'=>false,'shop'=>false,'music'=>false,'games'=>false];
$__dc = array_merge($__dcDefault, $__dc);
/* GENESYS DRX — gamification login UNIVERSALE (copre login user/password E
   wallet-connect: entrambi atterrano qui). 1 DRX/giorno + 15 DRX per 3 login/
   settimana, UNICO per uid anche da IP diversi. Payout on-chain in tempo reale
   se abilitato. Non-fatale, idempotente per giorno. */
try { if (is_file(__DIR__.'/genesys-drx-economy.php')) { require_once __DIR__.'/genesys-drx-economy.php';
      if (function_exists('gdrx_login_gamify')) {
        $__via = (isset($_SESSION['login_via']) && $_SESSION['login_via']==='wallet') ? 'wallet' : 'password';
        gdrx_login_gamify($pdo, $uid, $__via);
      } } } catch (Throwable $e) {}
if (is_file(__DIR__.'/vol-lib.php')) { @require_once __DIR__.'/vol-lib.php'; }
$volMio = function_exists('vol_ha') ? vol_ha($pdo,$uid,0) : false;
$u = $pdo->query("SELECT * FROM users WHERE id=$uid")->fetch(PDO::FETCH_ASSOC) ?: [];

$eff  = function_exists('drx_effective') ? drx_effective($pdo,$uid) : ['idx'=>0,'rank'=>['name'=>'Randagio','min'=>0],'next'=>null,'bal'=>0];
$bal  = (float)($eff['bal'] ?? 0); $rank = $eff['rank']; $next = $eff['next'] ?? null;
$curMin=(int)($rank['min']??0); $nextMin=$next?(int)$next['min']:$curMin;
$pct  = $next && $nextMin>$curMin ? max(0,min(100,round(($bal-$curMin)/($nextMin-$curMin)*100))) : 100;
$earned=(float)$pdo->query("SELECT COALESCE(SUM(delta),0) FROM drx_ledger WHERE uid=$uid AND delta>0")->fetchColumn();
$burned=abs((float)$pdo->query("SELECT COALESCE(SUM(delta),0) FROM drx_ledger WHERE uid=$uid AND delta<0")->fetchColumn());
$net  = function_exists('dr_network_count') ? dr_network_count($pdo,$uid) : 0;
$refRew=0; try{ $refRew=(float)$pdo->query("SELECT COALESCE(SUM(drx),0) FROM referral_rewards WHERE uid=$uid")->fetchColumn(); }catch(Throwable $e){}
$l1   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE referrer_id=$uid")->fetchColumn();
$ms   = function_exists('dr_membership_state') ? dr_membership_state($pdo,$uid) : ['status'=>($u['membership_active']??0?'active':'none'),'tier'=>($u['membership_tier']??'')];
$reflink = function_exists('dr_ref_link') && !empty($u['internal_code']) ? dr_ref_link($u['internal_code']) : '';
$acts=[]; try{ $acts=$pdo->query("SELECT delta,reason,created FROM drx_ledger WHERE uid=$uid ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC); }catch(Throwable $e){}
/* AGGIUNTO 2026-08-01 (CC-0064): DRX da Kit Genesys / NFT senza ancora una
   scelta wallet/carriera registrata — vedi genesys/drx-claim-scelta.php.
   Solo lettura, non tocca l'accredito ne' il saldo: se la query fallisce
   (colonna 'sorgente' non ancora migrata) il banner resta semplicemente
   nascosto, nessun errore in pagina. */
$drxDaDecidere = ['n'=>0,'drx'=>0];
try{
  $r = $pdo->prepare("SELECT COUNT(DISTINCT l.ref) n, COALESCE(SUM(l.delta),0) drx
     FROM drx_ledger l
     WHERE l.uid=? AND l.delta>0 AND l.sorgente IN ('kit_genesys','nft_mint')
       AND NOT EXISTS (SELECT 1 FROM drx_claim_scelta s WHERE s.uid=l.uid AND s.ref=l.ref)");
  $r->execute([$uid]); $drxDaDecidere = $r->fetch(PDO::FETCH_ASSOC) ?: $drxDaDecidere;
}catch(Throwable $e){}
$hashes=[]; try{ $hashes=$pdo->query("SELECT kind,tx_hash,chain_status,explorer_url FROM onchain_hashes WHERE uid=$uid ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC); }catch(Throwable $e){}
$nftN=0; try{ $nftN=(int)$pdo->query("SELECT COUNT(*) FROM nft_holdings WHERE uid=$uid")->fetchColumn(); }catch(Throwable $e){}
$lifeWheel = dr_gw_life_wheel($pdo,$uid);
$maslow    = dr_gw_maslow($pdo,$uid);
$x81=0; if(function_exists('x81_balance')){ try{ $x81=(float)x81_balance($pdo,$uid); }catch(Throwable $e){} }
$email=$u['email']??''; $orders=[];
if($email){ try{ $st=$pdo->prepare("SELECT ref,total_eur,status,created FROM orders WHERE customer LIKE ? ORDER BY id DESC LIMIT 6"); $st->execute(['%'.$email.'%']); $orders=$st->fetchAll(PDO::FETCH_ASSOC); }catch(Throwable $e){} }
$downline=[]; try{ $st=$pdo->prepare("SELECT id,full_name,username,internal_code,COALESCE(rank_floor,0) rf,COALESCE(membership_active,0) ma FROM users WHERE referrer_id=? ORDER BY id DESC LIMIT 12"); $st->execute([$uid]); $downline=$st->fetchAll(PDO::FETCH_ASSOC); }catch(Throwable $e){}
/* SIC-ID rete Genesys (CC-0051 task (a) Cowork): letto in sola lettura da network_nodes
   (network-engine.php, gia' pronto lato Code). Se l'utente non e' un nodo di rete (non
   Pioniere, non piazzato), $netNode resta null e la card non si mostra: nessun impatto
   sugli utenti non-Genesys. Difensivo: tabella/colonne mancanti non rompono la pagina. */
if (is_file(__DIR__.'/network-engine.php')) { @require_once __DIR__.'/network-engine.php'; }
/* CC-0064 (Code, handoff): piramide dei 118 posti pre-costruita — widget utente
   (net_widget_utente) mostra il mio posto/SIC, leader contattabile, diretti e
   rete piu' profonda. Difensivo: se la tabella non e' pronta, la card non appare. */
if (is_file(__DIR__.'/dr-network-widget.php')) { @require_once __DIR__.'/dr-network-widget.php'; }
/* INNESTO 2026-08-12 (Cowork, lane "Sviluppo network"): pannello Networker B2B.
   Additivo, come le righe sopra: se il file manca, function_exists() sotto
   salta senza errori. Nessuna funzione ne' variabile esistente viene toccata. */
if (is_file(__DIR__.'/dr-b2b-widget-user.php')) { @require_once __DIR__.'/dr-b2b-widget-user.php'; }
/* ---------------------------------------------------------------------------
   LA RETE NELL'ALBERO — 2026-08-15 (Cowork)
   ATTENZIONE, e' il punto dove si fa confusione: qui sopra $l1 conta i
   `users.referrer_id` (chi ti sei portato dietro) e $net usa dr_network_count.
   Sono due alberi DIVERSI da quello delle POSIZIONI (`network_posti`), che e'
   quello che si vede nel tool. Se mostro un numero solo, la dashboard e il
   tool si contraddicono e l'utente non sa a chi credere.
   Quindi: si mostrano i numeri delle POSIZIONI, etichettati per quello che
   sono, con l'avviso quando non coincidono con gli invitati diretti.
   Tutto in try/catch: la dashboard non deve cadere per un conteggio.
--------------------------------------------------------------------------- */
$RETE = ['posto'=>0, 'rete'=>0, 'diretti'=>0, 'attivi'=>0, 'ok'=>false];
try{
  if (is_file(__DIR__.'/genesys/_dr-albero-lib.php')) {
    require_once __DIR__.'/genesys/_dr-albero-lib.php';
    if (function_exists('alb_attacca_rete')) {
      $rp = $pdo->query("SELECT posto, COALESCE(uid,0) uid, COALESCE(stato,'') stato
                         FROM network_posti
                         WHERE COALESCE(uid,0)={$uid} OR COALESCE(assigned_uid,0)={$uid}
                         ORDER BY posto LIMIT 1")->fetch(PDO::FETCH_ASSOC);
      if ($rp) {
        $rr = alb_attacca_rete($pdo, $rp);
        $RETE = ['posto'=>(int)$rp['posto'], 'rete'=>(int)$rr['rete'],
                 'diretti'=>(int)$rr['rete_diretti'], 'attivi'=>(int)$rr['rete_attivi'], 'ok'=>true];
      }
    }
  }
}catch(Throwable $e){}

$netNode=null; try{ $st=$pdo->prepare("SELECT sic_id,rango,rank_floor,status,attivazione_n,upline_uid FROM network_nodes WHERE uid=? LIMIT 1"); $st->execute([$uid]); $netNode=$st->fetch(PDO::FETCH_ASSOC) ?: null; }catch(Throwable $e){}
$sicNet = $netNode['sic_id'] ?? ($u['genesys_sic'] ?? ($u['sic_id'] ?? ''));
$netRankNomi = function_exists('net_rank_nomi') ? net_rank_nomi() : [];
$miss=[]; if(function_exists('dr_month_missions')){ try{ $miss=dr_month_missions($pdo,$uid); }catch(Throwable $e){} }
$ranks = function_exists('drx_ranks') ? drx_ranks() : [];
$avatar = !empty($u['avatar_url']) ? $u['avatar_url'] : 'assets/avatar-placeholder.png';
$sic = $u['internal_code'] ?? '—'; $name = $u['full_name'] ?: ($u['username'] ?? 'Randagio');
$wallet = $u['wallet'] ?? '';
$profPct = (int)($u['profile_complete'] ?? 0); if($profPct<=0){ $profPct = 60; }
function pe($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function n0($x){ return number_format((float)$x,0,',','.'); }
$radarLabels = array_map(function($a){return $a['label'];}, $lifeWheel['aree']);
$radarData   = array_map(function($a){return $a['value'];}, $lifeWheel['aree']);
$rankName = $rank['name'] ?? 'Randagio';
$nextName = $next ? ($next['name'] ?? '') : 'rango massimo';
?><!DOCTYPE html><html lang="it"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Il Mio Branco — Dashboard</title><meta name="robots" content="noindex,nofollow">
<link rel="icon" href="assets/favicon-192.webp">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400..800&family=Bebas+Neue&family=JetBrains+Mono&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<style>
:root{--black:#0D0D0D;--pnl:#16161F;--white:#F5F5F7;--gray:#9AA0A6;--gd:#3D4045;--gold:#D4AF37;--gold2:#F3D77A;--gold3:#b8942e;--bd:rgba(212,175,55,.28);--green:#26A17B;--orange:#FF7A1A;--red:#E5484D;--sb:260px;--tb:60px;--r:16px}
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%;background:var(--black);color:var(--white);font-family:'Inter',system-ui,Arial,sans-serif;font-size:14px;line-height:1.5;overflow:hidden}
a{color:var(--gold);text-decoration:none}button{cursor:pointer;font:inherit;border:0;background:none;color:inherit}
::-webkit-scrollbar{width:6px;height:6px}::-webkit-scrollbar-thumb{background:var(--gold3);border-radius:4px}
.app{display:flex;height:100vh;overflow:hidden}
.sb{width:var(--sb);flex:0 0 var(--sb);background:var(--pnl);border-right:1px solid var(--bd);display:flex;flex-direction:column;padding:18px 0 18px 14px;overflow-y:auto;z-index:100;transition:transform .25s}
.brand{display:flex;align-items:center;gap:10px;padding:0 12px 18px 0;border-bottom:1px solid var(--bd);margin-bottom:16px}
.brand img{height:38px}.brand span{font-family:'Bebas Neue',sans-serif;letter-spacing:2px;font-size:18px;color:var(--gold)}
.ng{margin-bottom:14px}.ng .lbl{font-size:10px;text-transform:uppercase;letter-spacing:2px;color:var(--gd);padding:0 12px;margin-bottom:5px}
/* menu a tendina 2026-08-13: gruppi nav collassabili + cuscinetto scroll (additivo, non tocca la regola sopra) */
.ng .lbl{cursor:pointer;display:flex;align-items:center;justify-content:space-between;user-select:none}
.ng .lbl .chv{transition:transform .2s;font-size:9px;opacity:.7}
.ng.collapsed .ni{display:none}
.ng.collapsed .lbl .chv{transform:rotate(-90deg)}
nav{padding-bottom:32px}
.ni{display:flex;align-items:center;gap:11px;padding:8px 12px;border-radius:9px;color:var(--gray);cursor:pointer;font-size:13px;font-weight:500;margin-bottom:2px;transition:.2s}
.ni:hover{background:rgba(212,175,55,.08);color:#fff}.ni.on{background:rgba(212,175,55,.15);color:var(--gold)}
.ni svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.8;flex-shrink:0}
.main{flex:1;display:flex;flex-direction:column;min-width:0}
.tb{height:var(--tb);flex:0 0 var(--tb);background:var(--pnl);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 20px;gap:14px}
.tb .burger{display:none;font-size:22px;color:#fff}
.tb .sear{flex:1;max-width:380px;background:var(--black);border:1px solid var(--bd);border-radius:40px;padding:6px 16px;display:flex;align-items:center;gap:8px;color:var(--gray)}
.tb .sear input{flex:1;background:transparent;border:0;color:#fff;font-size:13px;outline:none}
.prof{display:flex;align-items:center;gap:10px;cursor:pointer;padding:4px 8px 4px 4px;border-radius:30px}
.prof img{width:36px;height:36px;border-radius:50%;border:2px solid var(--gold);object-fit:cover}
.prof .nm{font-weight:600;font-size:13px}.prof .sc{font-size:11px;color:var(--gray);font-family:'JetBrains Mono',monospace}
.lg{margin-left:6px;color:var(--gray);font-size:12px;border:1px solid var(--bd);border-radius:20px;padding:5px 12px}
.pnls{flex:1;overflow-y:auto;padding:20px 22px 48px}
.pnl{display:none;animation:f .3s ease}.pnl.on{display:block}
@keyframes f{0%{opacity:0;transform:translateY(8px)}100%{opacity:1;transform:none}}
.pt{font-family:'Bebas Neue',sans-serif;font-size:30px;letter-spacing:1px;color:var(--gold);margin-bottom:6px}
.ps{color:var(--gray);font-size:13px;margin-bottom:18px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:16px}.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.c{background:var(--pnl);border:1px solid var(--bd);border-radius:var(--r);padding:18px;transition:.2s}.c:hover{border-color:rgba(212,175,55,.5)}
.cl{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gd);margin-bottom:3px}
.cv{font-family:'Bebas Neue',sans-serif;font-size:27px;letter-spacing:.5px}.cv.gold{color:var(--gold)}
.csb{font-size:12px;color:var(--gray);margin-top:2px}
.fb{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px}
.chip{display:inline-flex;align-items:center;gap:4px;background:rgba(212,175,55,.12);color:var(--gold);padding:2px 11px;border-radius:40px;font-size:12px;font-weight:600}
.chip.g{background:rgba(38,161,123,.15);color:var(--green)}.chip.o{background:rgba(255,122,26,.15);color:var(--orange)}
.btn{background:linear-gradient(135deg,#ffe08a,var(--gold) 55%,var(--gold3));color:#160f00;font-weight:800;padding:8px 18px;border-radius:40px;font-size:13px}
.btn.o{background:transparent;border:1px solid var(--bd);color:var(--gold)}.btn.sm{padding:5px 13px;font-size:12px}
.bar{height:6px;background:var(--black);border-radius:4px;overflow:hidden;margin-top:6px}.bar i{display:block;height:100%;background:linear-gradient(90deg,var(--gold3),var(--gold2))}
.row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px}
table{width:100%;border-collapse:collapse;font-size:12.5px}th{text-align:left;color:var(--gd);padding:8px 8px;border-bottom:1px solid var(--bd)}td{padding:8px 8px;border-bottom:1px solid rgba(255,255,255,.03);color:#cfd2d6}
.tw{overflow-x:auto}
.mas{display:flex;flex-direction:column-reverse;align-items:center;gap:6px}
.mas .lv{width:100%;max-width:320px;display:flex;justify-content:space-between;align-items:center;padding:9px 15px;border-radius:10px;background:var(--black);border:1px solid var(--bd)}
.mas .lv.ok{border-color:var(--gold);background:rgba(212,175,55,.1)}
.mas .lv:nth-child(1){max-width:320px}.mas .lv:nth-child(2){max-width:270px}.mas .lv:nth-child(3){max-width:225px}.mas .lv:nth-child(4){max-width:185px}.mas .lv:nth-child(5){max-width:150px}
.mt12{margin-top:12px}
.ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:150}.ov.on{display:block}
@media(max-width:1024px){.g4{grid-template-columns:repeat(2,1fr)}}
@media(max-width:768px){.sb{position:fixed;top:0;left:0;height:100%;transform:translateX(-100%);width:280px;z-index:200}.sb.open{transform:none}.tb .burger{display:block}.g2,.g3,.g4{grid-template-columns:1fr}.pnls{padding:14px}.prof .nm,.prof .sc{display:none}}
</style></head><body>
<div class="app">
  <div class="ov" id="ov"></div>
  <aside class="sb" id="sb">
    <div class="brand"><img src="assets/LOGO DR Corona ok.webp" alt="DR"><span>Destino Randagio</span></div>
    <nav>
      <div class="ng"><div class="lbl">Generale<span class="chv">▾</span></div>
        <div class="ni on" data-p="overview"><svg viewBox="0 0 24 24"><path d="M3 12l9-9 9 9"/><path d="M5 10v10h4V14h6v6h4V10"/></svg>Panoramica</div>
        <?php if($__dc['wallet']): ?>
        <div class="ni" data-p="wallet"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M16 12h3"/></svg>Wallet</div>
        <?php endif; ?>
        <?php if($__dc['rank']): ?>
        <div class="ni" data-p="rank"><svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5z"/><path d="M2 17l10 5 10-5"/></svg>Rango &amp; Carriera</div>
        <?php endif; ?>
      </div>
      <div class="ng"><div class="lbl">Community<span class="chv">▾</span></div>
        <?php if($__dc['missions']): ?>
        <div class="ni" data-p="missions"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>Missioni</div>
        <?php endif; ?>
        <?php if($__dc['network']): ?>
        <div class="ni" data-p="network"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Rete / Network</div>
        <?php endif; ?>
        <?php if($__dc['staking']): ?>
        <div class="ni" data-p="staking"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="12" rx="2"/><path d="M15 21l3-3 3 3"/></svg>Staking &amp; Rendita</div>
        <?php endif; ?>
      </div>
      <div class="ng"><div class="lbl">Web3 &amp; Shop<span class="chv">▾</span></div>
        <?php if($__dc['nft']): ?>
        <div class="ni" data-p="nft"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M21 15l-5-5-11 11"/></svg>NFT / Web3</div>
        <?php endif; ?>
        <?php if($__dc['membership']): ?>
        <div class="ni" data-p="membership"><svg viewBox="0 0 24 24"><path d="M12 2l3 6 6 .9-4.5 4.3 1 6.3L12 17.8 6.5 19.5l1-6.3L3 8.9 9 8z"/></svg>Membership</div>
        <?php endif; ?>
        <?php if($__dc['shop']): ?>
        <div class="ni" data-p="shop"><svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>Shop &amp; Ordini</div>
        <?php endif; ?>
      </div>
      <div class="ng"><div class="lbl">Intrattenimento<span class="chv">▾</span></div>
        <?php if($__dc['music']): ?>
        <div class="ni" data-p="music"><svg viewBox="0 0 24 24"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>Musica &amp; Fumetti</div>
        <?php endif; ?>
        <?php if($__dc['games']): ?>
        <div class="ni" data-p="games"><svg viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="4"/><path d="M7 12h4M9 10v4"/><circle cx="16" cy="11" r="1"/><circle cx="18" cy="14" r="1"/></svg>Giochi</div>
        <?php endif; ?>
      </div>
      <div class="ng"><div class="lbl">Account<span class="chv">▾</span></div>
        <div class="ni" data-p="profile"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 12 0v1"/></svg>Profilo &amp; Wallet</div>
      </div>
    </nav>
  </aside>
  <div class="main">
    <header class="tb">
      <button class="burger" id="burger">&#9776;</button>
      <div class="sear"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9AA0A6" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg><input placeholder="Cerca nel Branco..."></div>
      <div class="prof"><img src="<?=pe($avatar)?>" alt="avatar"><div><div class="nm"><?=pe($name)?></div><div class="sc"><?=pe($sic)?></div></div></div>
      <a class="lg" href="account.php?action=logout">Esci</a>
    </header>
    <?php if(function_exists('drx_scelta_modal_asset')) echo drx_scelta_modal_asset(); /* CC-0062: modale wallet/carriera condivisa per tutti i claim della pagina */ ?>
    <div class="pnls">

      <!-- OVERVIEW -->
      <section class="pnl on" id="p-overview">
        <h1 class="pt">Panoramica</h1><p class="ps">Il tuo riepilogo nel Branco</p>
        <?php /* INNESTO PHP_GENESYS: Covo + carta NFT + prestigio + accessi rapidi */
          echo '<div class="c" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap">'
             . '<a class="btn" href="dr-academy.php" style="background:#141109;border:1px solid #D4AF37;color:#e8d9a8;border-radius:9px;padding:9px 14px;text-decoration:none">🐺 L\'Academy (Covo + lezioni)</a>'
             /* INNESTO 2026-08-11: forum DAO + chat AI del Branco (covo/) */
             . '<a class="btn" href="covo/" style="background:#141109;border:1px solid #f59e0b;color:#fcd9a0;border-radius:9px;padding:9px 14px;text-decoration:none">💬 Forum del Branco</a>'
             /* INNESTO 2026-08-13: Tana DAO unificata (era genesys/dao-genesys.php, ora ritirato) */
             . '<a class="btn" href="covo/?tab=dao" style="background:#141109;border:1px solid #f59e0b;color:#fcd9a0;border-radius:9px;padding:9px 14px;text-decoration:none">🗳️ Tana DAO</a>'
             . '<a class="btn" href="genesys/dr-preda.php" style="background:#0f141a;border:1px solid #2b6cff;color:#bcd4ff;border-radius:9px;padding:9px 14px;text-decoration:none">⚔ PREDA (sconto 81X)</a>'
             . '<a class="btn" href="wallet-genesys.php" style="background:#0f141a;border:1px solid #39d98a;color:#b6f0d3;border-radius:9px;padding:9px 14px;text-decoration:none">👛 Il tuo Wallet</a>'
             . '</div>';
          try { if (function_exists('dr_calendario_blocco')) echo dr_calendario_blocco(); } catch (Throwable $e) {}
          try { if (function_exists('dr_nft_card'))        echo dr_nft_card($pdo, $uid); } catch (Throwable $e) {}
          try { if (function_exists('dr_prestigio_card'))  echo dr_prestigio_card($pdo, $uid); } catch (Throwable $e) {}
        ?>
        <div class="c" style="margin-bottom:18px">
          <div class="fb"><div style="display:flex;align-items:center;gap:14px">
            <img src="<?=pe($avatar)?>" style="width:60px;height:60px;border-radius:50%;border:3px solid var(--gold);object-fit:cover">
            <div><div style="font-size:19px;font-weight:700"><?=pe($name)?></div>
              <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:5px">
                <span class="chip"><?=pe($sic)?> <span style="cursor:pointer" onclick="cp('<?=pe($sic)?>')">&#128203;</span></span>
                <span class="chip"><?=pe($rankName)?></span>
                <span class="chip g"><?=n0($bal)?> DRX</span>
                <span class="chip o"><?= ($ms['status']??'')==='active'?'Membership attiva':'Non attiva' ?></span>
              </div>
              <div class="bar" style="width:200px"><i style="width:<?=$profPct?>%"></i></div>
              <span style="font-size:12px;color:var(--gray)">Profilo completo <?=$profPct?>%</span>
            </div></div></div>
        </div>
        <?php /* CC-0063 (Code, copy): invito a completare profilo + attivare Kit — stessa voce DR dei flussi email (email-flows.php: goal-gradient, niente urgenza falsa, "la scelta resta tua") */ ?>
        <?php if($profPct<100 || ($ms['status']??'')!=='active'): ?>
        <div class="c" style="margin-bottom:18px;border-color:rgba(212,175,55,.4)">
          <?php if($profPct<100): ?>
          <div class="fb"><span style="font-weight:600">Il tuo profilo è quasi pronto</span><span class="chip g">+1.000 DRX al completamento</span></div>
          <div class="csb mt12">Sei già al <?=$profPct?>%: l'ultimo tratto è sempre il più corto. Chiudilo e i DRX entrano nel wallet — credito-premio per sconti e vantaggi nel Branco, mai denaro, mai un investimento.</div>
          <a class="btn sm mt12" href="account.php#profilo">Completa il profilo</a>
          <?php endif; ?>
          <?php if(($ms['status']??'')!=='active'): ?>
          <div class="<?=$profPct<100?'mt12':'fb'?>" <?=$profPct<100?'style="border-top:1px solid var(--bd);padding-top:12px"':''?>><span style="font-weight:600">Nel Branco si entra col Kit</span></div>
          <div class="csb mt12">Da fuori vedi un profilo. Col <b>Kit del Branco</b> diventi Membro: giochi, DRX, NFT, rango, la tua Family. Nessuna fretta: la scelta resta tua.</div>
          <a class="btn o sm mt12" href="branco.html">Scopri il Kit del Branco</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="g4" style="margin-bottom:20px">
          <div class="c"><div class="cl">Saldo DRX</div><div class="cv gold"><?=n0($bal)?></div><div class="csb">&asymp; &euro;<?=number_format($bal*0.10,2,',','.')?></div></div>
          <div class="c"><div class="cl">Rango</div><div class="cv"><?=pe($rankName)?></div><div class="csb">Prossimo: <?=pe($nextName)?></div></div>
          <div class="c"><div class="cl">Rete</div><div class="cv"><?=n0($net)?></div><div class="csb"><?=n0($l1)?> diretti</div></div>
          <div class="c"><div class="cl">Reward referral</div><div class="cv gold">+<?=n0($refRew)?></div><div class="csb">DRX</div></div>
        </div>
        <div class="g2" style="margin-bottom:20px">
          <div class="c"><div class="fb"><span style="font-weight:600">Ruota della Vita</span><span class="chip"><?=$lifeWheel['overall']?>%</span></div>
            <canvas id="rwChart" height="230" style="margin-top:8px"></canvas></div>
          <div class="c"><div class="fb"><span style="font-weight:600">Piramide di Maslow</span><span class="chip <?=$maslow['level']>=1?'g':''?>"><?=$maslow['pct']?>%</span></div>
            <div class="mas mt12">
              <?php foreach($maslow['livelli'] as $lv): ?>
              <div class="lv <?=!empty($lv['done'])?'ok':''?>"><b style="color:<?=!empty($lv['done'])?'var(--gold2)':'var(--gray)'?>"><?=pe($lv['ic'])?> <?=pe($lv['label'])?></b><span><?=!empty($lv['done'])?'&#10003;':'&#128274;'?></span></div>
              <?php endforeach; ?>
            </div></div>
        </div>
        <div class="c"><div class="fb"><span style="font-weight:600">Prossimo obiettivo &mdash; <?=pe($nextName)?></span><span style="color:var(--gray);font-size:13px"><?=n0($bal)?> / <?=n0($nextMin)?> DRX</span></div>
          <div class="bar"><i style="width:<?=$pct?>%"></i></div></div>
      </section>

      <?php if($__dc['wallet']): ?>
      <!-- WALLET -->
      <section class="pnl" id="p-wallet">
        <h1 class="pt">Wallet</h1>
        <div class="g2">
          <div class="c"><div class="cl">Saldo DRX</div><div class="cv gold" style="font-size:42px"><?=n0($bal)?></div><div class="csb">&asymp; &euro;<?=number_format($bal*0.10,2,',','.')?></div>
            <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap"><span class="chip g">+<?=n0($earned)?> guadagnati</span><span class="chip o">&minus;<?=n0($burned)?> bruciati</span></div>
            <div style="margin-top:14px;display:flex;gap:8px"><a class="btn" href="drx-ricarica.php">Ricarica DRX</a><a class="btn o" href="explorer.php">Storico</a></div></div>
          <div class="c"><div class="cl">Altri saldi &amp; Swap</div>
            <div class="row"><span>81X</span><b style="font-family:'JetBrains Mono',monospace;color:var(--gold)"><?=n0($x81)?></b></div>
            <div class="row"><span>Controvalore DRX</span><b>&euro;<?=number_format($bal*0.10,2,',','.')?></b></div>
            <div style="margin-top:12px"><a class="btn" href="account.php#swap">Swap USDT &middot; DRX &middot; 81X</a></div>
            <div class="csb" style="margin-top:8px">Lo swap gira sul motore interno (dr-swap.php).</div></div>
        </div>
        <div class="c mt12"><div class="fb"><span style="font-weight:600">Wallet Polygon (Web3)</span>
          <button class="btn o sm" id="bindBtn">Collega / verifica wallet</button></div>
          <div style="font-family:'JetBrains Mono',monospace;color:var(--gray);font-size:12px;margin-top:6px" id="wAddr"><?= $wallet ? pe($wallet) : 'Nessun wallet collegato' ?></div>
          <div class="csb" id="wMsg" style="min-height:1em;margin-top:6px"></div>
          <div class="csb">Il binding lega il tuo SIC-ID a un indirizzo unico sulla rete Polygon.</div></div>
        <?php if(function_exists('drx_explorer_render')): ?>
        <div class="mt12"><?php echo drx_explorer_render($pdo,(int)$uid,false); ?></div>
        <?php endif; ?>
        <!-- CC-0058 (Code, handoff): stato richieste withdraw on-chain (bridge-out, claim airdrop/stake) -->
        <div class="c mt12" id="wdUserCard"><div class="fb"><span style="font-weight:600">Le mie richieste di prelievo</span><span class="chip" id="wdUserCount">&mdash;</span></div>
          <div class="csb mt12">Ogni prelievo verso il tuo wallet on-chain passa da un controllo di sicurezza: resta "in verifica" per almeno 48 ore prima di essere inviato. L'importo qui sotto è già al netto dell'1% trattenuto e bruciato on-chain (deflazione DRX).</div>
          <div class="tw mt12"><table id="wdUserTbl" style="display:none"><thead><tr><th>Data</th><th>DRX</th><th>Tipo</th><th>Stato</th><th>Tx</th></tr></thead><tbody id="wdUserRows"></tbody></table>
            <div class="csb" id="wdUserEmpty">Nessuna richiesta di prelievo ancora.</div></div>
        </div>
      </section>
      <?php endif; /* dashboard-control: wallet */ ?>

      <?php if($__dc['rank']): ?>
      <!-- RANK -->
      <section class="pnl" id="p-rank">
        <h1 class="pt">Rango &amp; Carriera</h1>
        <div class="g2">
          <div class="c"><div style="font-weight:600;margin-bottom:10px">Scala dei ranghi</div>
            <?php foreach($ranks as $i=>$r): $cur=($i==($eff['idx']??0)); ?>
            <div class="row"><span><?=pe($r['name'])?></span><span class="chip <?=$i<($eff['idx']??0)?'g':($cur?'':'')?>"><?= $i<($eff['idx']??0)?'raggiunto':($cur?'attuale':'&rarr; '.n0($r['min']).' DRX') ?></span></div>
            <?php endforeach; ?></div>
          <div class="c"><div class="cl">Progressione</div><div class="cv gold" style="font-size:22px"><?=n0($bal)?> / <?=n0($nextMin)?> DRX</div>
            <div class="bar"><i style="width:<?=$pct?>%"></i></div>
            <div class="csb"><?= $next ? 'Ti servono '.n0(max(0,$nextMin-$bal)).' DRX per salire' : 'Rango massimo raggiunto' ?></div>
            <a class="btn mt12" href="account.php#carriera">Sali di livello (brucia DRX)</a>
            <div class="mt12"><span class="csb">Simulatore crescita</span><canvas id="simChart" height="120"></canvas></div></div>
        </div>
      </section>
      <?php endif; /* dashboard-control: rank */ ?>

      <?php if($__dc['missions']): ?>
      <!-- MISSIONI -->
      <section class="pnl" id="p-missions">
        <h1 class="pt">Missioni &amp; Gamification</h1>
        <div class="g2">
          <div class="c"><div class="fb"><span style="font-weight:600">Missioni del mese</span><span class="chip">DRX</span></div>
            <?php if($miss): foreach($miss as $m): ?>
            <div class="row"><span><?= !empty($m['done'])?'&#10003;':'&#9744;' ?> <?=pe($m['t']??'')?></span><span class="csb">+<?=n0($m['drx']??0)?> DRX</span></div>
            <?php endforeach; else: ?><div class="csb">Nessuna missione attiva ora.</div><?php endif; ?>
            <a class="btn sm mt12" href="account.php#missioni">Conferma il mese</a></div>
          <div class="c"><div class="fb"><span style="font-weight:600">La tua Ruota della Vita</span><span class="chip"><?=$lifeWheel['overall']?>%</span></div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px">
              <?php foreach($lifeWheel['aree'] as $a): ?><span class="chip"><?=pe($a['label'])?> <?=$a['value']?></span><?php endforeach; ?>
            </div>
            <div class="csb mt12">Si aggiorna da sola con le tue azioni. Non dà DRX: dà chiarezza.</div></div>
        </div>
        <div class="g2 mt12">
          <div class="c" id="airdropCard" style="display:none">
            <div class="fb"><span style="font-weight:600">Airdrop da riscuotere</span><span class="chip g" id="airdropBadge">0 DRX</span></div>
            <div class="csb mt12">Hai airdrop del Branco in sospeso. Riscuotili quando vuoi: se hai un wallet collegato e la membership attiva partono anche in coda on-chain.</div>
            <button class="btn sm mt12" id="claimBtn" type="button">Riscuoti</button>
          </div>
          <div class="c">
            <?php if(function_exists('drg_leaderboard_render')) echo drg_leaderboard_render($pdo,10,$uid); ?>
            <a class="btn o sm mt12" href="classifica.php">Classifica completa</a>
          </div>
        </div>
        <?php if((int)($drxDaDecidere['n']??0) > 0): ?>
        <!-- CC-0064 (Cowork, 2026-08-01): DRX Kit Genesys/NFT senza scelta wallet/carriera -->
        <div class="g2 mt12">
          <div class="c" style="border-color:rgba(212,175,55,.5)">
            <div class="fb"><span style="font-weight:600">DRX da decidere: Wallet o Carriera?</span><span class="chip g"><?=n0($drxDaDecidere['drx'])?> DRX</span></div>
            <div class="csb mt12">Il Kit Genesys e/o i tuoi NFT hanno già accreditato DRX. Scegli se tenerli spendibili nel wallet o investirli nella carriera per salire di rango.</div>
            <a class="btn sm mt12" href="genesys/drx-claim-scelta.php">Decidi ora</a>
          </div>
        </div>
        <?php endif; ?>
      </section>
      <?php endif; /* dashboard-control: missions */ ?>

      <?php if($__dc['network']): ?>
      <!-- NETWORK -->
      <section class="pnl" id="p-network">
        <h1 class="pt">Rete / Network</h1>
        <?php if($netNode || $sicNet): ?>
        <div class="c mt12" style="margin-bottom:14px"><div class="fb"><span style="font-weight:600">Il tuo SIC-ID di rete</span><span class="chip g"><?=pe($netNode['status'] ?? 'Pro')?></span></div>
          <div style="font-family:'JetBrains Mono',monospace;color:var(--gold2);font-size:15px;margin-top:8px;word-break:break-all"><?=pe($sicNet ?: '—')?></div>
          <div class="csb" style="margin-top:8px">
            <?php if(!empty($netNode['attivazione_n'])): ?>Pioniere n. <?=n0($netNode['attivazione_n'])?> &middot; <?php endif; ?>
            Rango rete: <span class="chip"><?=pe($ranks[(int)($netNode['rank_floor'] ?? ($netNode['rango'] ?? 0))]['name'] ?? ($netRankNomi[(int)($netNode['rango'] ?? 0)] ?? '—'))?></span>
          </div>
        </div>
        <?php endif; ?>
        <!-- INNESTO 2026-08-15 (Cowork): l'albero della PROPRIA rete.
             Stesso strumento dell'Albero admin, ma con una porta diversa
             (genesys/dr-mia-rete-api.php) che si rifiuta di rispondere per un
             posto che non e' tuo o di un tuo discendente. Sola lettura: non
             c'e' un bottone che sposti niente, e nemmeno il codice per farlo. -->
        <a class="c mt12" href="genesys/mia-rete.php" style="display:block;margin-bottom:14px;text-decoration:none;
           background:linear-gradient(135deg,rgba(217,180,90,.14),rgba(217,180,90,.04));border-color:rgba(217,180,90,.4)">
          <div class="fb"><span style="font-weight:700;color:var(--gold2);font-size:15px">🌳 Guarda la tua rete</span>
            <span class="chip g">Albero &amp; Stella</span></div>
          <div class="csb" style="margin-top:8px">
            Tu in basso, e sopra di te tutti quelli che hai fatto crescere: diretti, indiretti,
            fino all'ultimo ramo. Si apre, si naviga, si cerca. Vedi solo la tua rete e nient'altro.
          </div>
        </a>
        <?php if($RETE['ok']): ?>
        <!-- I NUMERI DELL'ALBERO. Sono gli stessi che vedi passando col mouse
             sopra il tuo nodo nel tool: se qui dicesse una cosa e li' un'altra,
             il tool non varrebbe piu' niente. -->
        <div class="g3" style="margin-bottom:14px">
          <div class="c" style="border-color:rgba(217,180,90,.4)">
            <div class="cl">Persone nella tua rete</div>
            <div class="cv gold"><?=n0($RETE['rete'])?></div>
            <div class="csb" style="margin-top:4px">dirette e indirette, fino in fondo</div>
          </div>
          <div class="c"><div class="cl">Dirette (subito sotto di te)</div><div class="cv"><?=n0($RETE['diretti'])?></div></div>
          <div class="c"><div class="cl">Di quelle, attive</div><div class="cv"><?=n0($RETE['attivi'])?></div></div>
        </div>
        <div class="csb" style="margin:-6px 0 14px">
          La tua posizione nell'albero e' la <b>#<?=n0($RETE['posto'])?></b>.
          <?php if((int)$l1 !== (int)$RETE['diretti']): ?>
            <br><span style="color:#e0a08c">Qui sotto vedi <b><?=n0($l1)?></b> invitati diretti: quello e'
            un conteggio diverso — chi si e' iscritto col tuo link. Nell'albero delle posizioni
            hai <b><?=n0($RETE['diretti'])?></b> persone attaccate subito sotto. I due numeri
            possono non coincidere: una persona invitata da te puo' essere stata sistemata
            in un altro punto del ramo.</span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="g3" style="margin-bottom:14px">
          <div class="c"><div class="cl">Invitati diretti</div><div class="cv"><?=n0($l1)?></div></div>
          <div class="c"><div class="cl">Rete totale</div><div class="cv"><?=n0($net)?></div></div>
          <div class="c"><div class="cl">Reward referral</div><div class="cv gold">+<?=n0($refRew)?> DRX</div></div>
        </div>
        <div class="c"><div class="fb"><span style="font-weight:600">Il tuo link del Branco</span></div>
          <div style="display:flex;gap:8px;margin-top:8px"><input id="refl" value="<?=pe($reflink)?>" readonly style="flex:1;background:var(--black);border:1px solid var(--bd);color:#fff;border-radius:10px;padding:9px 12px;font-size:12px">
          <button class="btn sm" onclick="cp(document.getElementById('refl').value)">Copia</button></div></div>
        <div class="c mt12"><div style="font-weight:600;margin-bottom:6px">I tuoi downline diretti</div>
          <div class="tw"><table><thead><tr><th>Nome</th><th>SIC-ID</th><th>Rango</th><th>Membership</th></tr></thead><tbody>
            <?php if($downline): foreach($downline as $d): $rn=$ranks[$d['rf']]['name']??'Randagio'; ?>
            <tr><td><?=pe($d['full_name']?:$d['username'])?></td><td style="font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--gold2)"><?=pe($d['internal_code'])?></td><td><span class="chip"><?=pe($rn)?></span></td><td><span class="chip <?=$d['ma']?'g':'o'?>"><?=$d['ma']?'attiva':'&mdash;'?></span></td></tr>
            <?php endforeach; else: ?><tr><td colspan="4" class="csb">Nessun downline ancora. Invita il tuo primo membro!</td></tr><?php endif; ?>
          </tbody></table></div>
          <a class="btn o sm mt12" href="account.php#rete">Gestisci la rete &amp; regala DRX</a></div>
        <!-- CC-0057 (Code, handoff): compensi network da Kit venduti in downline (L1/L2), pending fino al 118 -->
        <div class="c mt12"><div class="fb"><span style="font-weight:600">I miei compensi (Kit venduti in rete)</span><span class="chip" id="compGate">&mdash;</span></div>
          <div class="csb mt12">Maturano SOLO da Kit Genesys venduti nella tua rete diretta (L1) e di 2&deg; livello (L2). Restano in sospeso finch&eacute; tutti i 118 Kit Pionieri non sono venduti, poi si sbloccano in automatico: nessuna promessa di rendimento, sono premi per azioni reali di crescita.</div></div>
        <div class="g3 mt12">
          <div class="c"><div class="cl">In sospeso</div><div class="cv" id="compPending">0</div></div>
          <div class="c"><div class="cl">Sbloccati</div><div class="cv gold" id="compPayable">0</div></div>
          <div class="c"><div class="cl">Pagati</div><div class="cv" id="compPaid">0</div></div>
        </div>
        <?php if(function_exists('net_widget_utente')): ?>
        <div class="mt12"><?php echo net_widget_utente($pdo,$uid); ?></div>
        <?php endif; ?>
        <?php /* INNESTO 2026-08-12 (Cowork): pannello Networker B2B. Un utente normale
                 vede l'invito a candidarsi; sblocca il pannello completo SOLO quando la
                 membership B2B e' attiva (gestita da admin-b2b.php, mai automatica). */
              if(function_exists('b2b_widget_utente')): ?>
        <div class="mt12"><?php echo b2b_widget_utente($pdo,$uid); ?></div>
        <?php endif; ?>
        <?php /* moduli Genesys nuovi (2026-08-01, Cowork) — link ai nuovi strumenti */ ?>
        <div class="g3 mt12">
          <div class="c"><div class="cl">Mappa del Branco</div><div class="csb" style="margin:6px 0">Il tuo network in 3D: downline, referente, zoom.</div><a class="btn sm" href="genesys/pipeline-network.php">Apri la mappa 3D</a></div>
          <div class="c"><div class="cl">Il tuo Wallet</div><div class="csb" style="margin:6px 0">Wallet on-chain Polygon + saldi interni.</div><a class="btn sm" href="genesys/wallet-branco.php">Apri il wallet</a></div>
          <div class="c"><div class="cl">Assistenza AI</div><div class="csb" style="margin:6px 0">Chat AI del Branco: risponde a tutto.</div><a class="btn sm" href="genesys/assistente-branco.php">Chiedi al Branco</a></div>
        </div>
        <div class="g3 mt12">
          <div class="c"><div class="cl">Tana DAO</div><div class="csb" style="margin:6px 0">Vota le proposte del Branco — unica DAO del progetto, dentro il Covo.</div><a class="btn sm" href="covo/?tab=dao">Vai alla DAO</a></div>
          <div class="c"><div class="cl">Supporto</div><div class="csb" style="margin:6px 0">Apri un ticket, ti rispondiamo di persona.</div><a class="btn sm" href="genesys/ticket.php">Apri un ticket</a></div>
          <div class="c"><div class="cl">📚 Biblioteca del Branco</div><div class="csb" style="margin:6px 0">Documenti vivi: tokenomics, verifica on-chain, rete, roadmap, legali.</div><a class="btn sm" href="genesys/biblioteca.php">Apri la Biblioteca</a> <a class="btn sm" href="genesys/doc-indice.html" style="opacity:.7">Vecchio indice</a></div>
        </div>
      </section>
      <?php endif; /* dashboard-control: network */ ?>

      <?php if($__dc['staking']): ?>
      <!-- STAKING -->
      <section class="pnl" id="p-staking">
        <h1 class="pt">Staking &amp; Rendita</h1>
        <div class="g3">
          <div class="c"><div class="cl">Staking NFT 10%</div><div class="cv">tier base</div><a class="btn sm mt12" href="account.php#staking">Apri</a></div>
          <div class="c"><div class="cl">Staking NFT 20%</div><div class="cv">tier medio</div><a class="btn sm mt12" href="account.php#staking">Apri</a></div>
          <div class="c"><div class="cl">Staking NFT 30%</div><div class="cv">tier alto</div><a class="btn sm mt12" href="account.php#staking">Apri</a></div>
        </div>
        <!-- CC-0056 (Code, handoff): stake NFT Genesys (Sigilli + Nodo Pioniere) -> DRX/giorno per rarita' -->
        <div class="c mt12" id="nftStakeCard">
          <div class="fb"><span style="font-weight:600">Stake NFT — DRX/giorno (Sigilli &amp; Nodo Pioniere)</span><span class="chip g" id="nftStakeMaturato">0 DRX maturati</span></div>
          <div class="csb mt12">Metti in stake i tuoi Sigilli e il Nodo Pioniere: maturano DRX ogni giorno in base alla rarit&agrave; (utilit&agrave; interna, non un investimento). Riscuoti quando vuoi.</div>
          <!-- CC-0060 (Code, handoff): badge grado bonus collezione + barra "ti mancano N NFT" (leva d'acquisto) -->
          <div class="mt12" id="nftStakeBonusWrap">
            <div class="fb"><span class="chip g" id="nftStakeBonusBadge">Lupo di Bronzo &middot; &times;1,00</span><span class="csb" id="nftStakeBonusN">0 NFT nella collezione</span></div>
            <div class="bar mt12" id="nftStakeBonusBar"><i id="nftStakeBonusFill" style="width:0%"></i></div>
            <div class="csb mt12" id="nftStakeBonusNext">Colleziona il tuo primo Sigillo o il Nodo Pioniere per attivare il bonus.</div>
          </div>
          <div class="tw mt12"><table id="nftStakeTbl" style="display:none"><thead><tr><th>NFT in stake</th><th>Rarit&agrave;</th><th>DRX/g</th><th>Maturato</th><th></th></tr></thead><tbody id="nftStakeRows"></tbody></table>
            <div class="csb" id="nftStakeEmpty">Nessun NFT in stake ora.</div></div>
          <div class="mt12" id="nftStakeAvailWrap" style="display:none">
            <div class="cl">Disponibili per lo stake</div>
            <div id="nftStakeAvail" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px"></div>
          </div>
          <button class="btn sm mt12" id="nftClaimBtn" type="button">Riscuoti maturato</button>
        </div>
        <div class="c mt12"><div class="fb"><span style="font-weight:600">La Rendita del Branco</span><a class="btn o sm" href="dr-rendita.php">Vai alla Rendita</a></div>
          <div class="csb mt12">Vault, Fondo del Branco e Reward Fedeltà: rendimento in DRX sul motore interno.</div></div>
        <?php if(function_exists('drx_vault_render')): ?>
        <div class="mt12"><?php echo drx_vault_render($pdo,(int)$uid); ?></div>
        <?php endif; ?>
      </section>
      <?php endif; /* dashboard-control: staking */ ?>

      <?php if($__dc['nft']): ?>
      <!-- NFT -->
      <section class="pnl" id="p-nft">
        <h1 class="pt">NFT / Web3</h1>
        <div class="g3">
          <div class="c"><div class="cl">NFT posseduti</div><div class="cv gold"><?=n0($nftN)?></div><a class="btn sm mt12" href="nft.php">Vai agli NFT</a></div>
          <div class="c"><div class="cl">Guardians</div><div class="cv">collezione</div><a class="btn o sm mt12" href="nft-guardians.php">Whitelist / tier</a></div>
          <div class="c"><div class="cl">Wallet Polygon</div><div class="cv" style="font-size:16px;font-family:'JetBrains Mono',monospace"><?= $wallet? pe(substr($wallet,0,6).'…'.substr($wallet,-4)) : 'non collegato' ?></div></div>
        </div>
        <div class="c mt12"><div class="fb"><span style="font-weight:600">Hash on-chain</span><span class="chip g"><?=count($hashes)?> registrati</span></div>
          <?php if($hashes): foreach($hashes as $h): ?>
          <div class="row"><span><?=pe($h['kind'])?> <span class="chip"><?=pe($h['chain_status'])?></span></span><?php if(!empty($h['explorer_url'])):?><a href="<?=pe($h['explorer_url'])?>" target="_blank" rel="noopener">apri &rsaquo;</a><?php endif;?></div>
          <?php endforeach; else: ?><div class="csb mt12">Nessun hash ancora.</div><?php endif; ?></div>
      </section>
      <?php endif; /* dashboard-control: nft */ ?>

      <?php if($__dc['membership']): ?>
      <!-- MEMBERSHIP -->
      <section class="pnl" id="p-membership">
        <h1 class="pt">Membership</h1>
        <div class="c"><div class="fb"><span style="font-weight:600">Stato attuale</span><span class="chip <?= ($ms['status']??'')==='active'?'g':'o' ?>"><?= pe($ms['status']??'—') ?></span></div>
          <div>Piano: <b><?= pe($ms['tier']?:'—') ?></b></div><?php if(!empty($ms['next_charge'])):?><div class="csb">Prossimo rinnovo: <?=pe(date('d/m/Y',strtotime($ms['next_charge'])))?></div><?php endif;?></div>
        <div class="c mt12"><div class="fb"><span style="font-weight:600">Gestione</span></div>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px"><a class="btn sm" href="membership.html">Gestisci / cambia piano</a><a class="btn o sm" href="account.php#membership">Metodo di rinnovo</a></div>
          <div class="csb mt12">Rinnovo intercambiabile Wallet &middot; USDT &middot; PayPal. Ogni rinnovo accredita DRX.</div></div>
      </section>
      <?php endif; /* dashboard-control: membership */ ?>

      <?php if($__dc['shop']): ?>
      <!-- SHOP -->
      <section class="pnl" id="p-shop">
        <h1 class="pt">Shop &amp; Ordini</h1>
        <div class="c"><div style="font-weight:600">I tuoi ordini recenti</div>
          <div class="tw"><table><thead><tr><th>Ref</th><th>Data</th><th>Totale</th><th>Stato</th></tr></thead><tbody>
            <?php if($orders): foreach($orders as $o): ?>
            <tr><td><?=pe($o['ref'])?></td><td><?=pe(substr($o['created'],0,10))?></td><td>&euro;<?=number_format((float)$o['total_eur'],2,',','.')?></td><td><span class="chip <?= in_array($o['status'],['paid','fulfilled','completed','shipped','delivered'])?'g':'o' ?>"><?=pe($o['status'])?></span></td></tr>
            <?php endforeach; else: ?><tr><td colspan="4" class="csb">Nessun ordine.</td></tr><?php endif; ?>
          </tbody></table></div></div>
        <div class="g2 mt12">
          <div class="c"><div class="cl">Spedizioni</div><a class="btn o sm mt12" href="account.php#spedizioni">Traccia i pacchi</a></div>
          <div class="c"><div class="cl">Mystery Box &amp; Gift</div><div style="display:flex;gap:8px;margin-top:8px"><a class="btn sm" href="mystery-box.php">Mystery Box</a><a class="btn o sm" href="giftcard.php">Gift</a></div></div>
        </div>
      </section>
      <?php endif; /* dashboard-control: shop */ ?>

      <?php if($__dc['music']): ?>
      <!-- MUSIC -->
      <section class="pnl" id="p-music">
        <span id="volumi"></span>
        <h1 class="pt">Musica &amp; Fumetti</h1>
        <div class="g3">
          <div class="c"><div style="font-weight:600">I miei album</div><div class="csb">La tua libreria, player anti-download.</div><a class="btn sm mt12" href="albums.html">Apri libreria</a></div>
          <div class="c"><div style="font-weight:600">I miei Volumi</div>
            <?php if($volMio): ?>
              <div class="csb">Volume Zero &mdash; lettore blindato, stampa in PDF.</div>
              <div style="display:flex;gap:8px;margin-top:12px"><a class="btn sm" href="leggi-volume.php?vol=0">Apri il volume</a><a class="btn o sm" href="vol-pdf.php?vol=0#toolbar=0" target="_blank" rel="noopener">Versione PDF</a></div>
            <?php else: ?>
              <div class="csb">Il fumetto del Branco: il Volume Zero, blindato, tuo per sempre.</div>
              <a class="btn sm mt12" href="vol0.php">Scopri il Volume Zero</a>
            <?php endif; ?>
          </div>
          <div class="c"><div style="font-weight:600">Canzoni su misura</div><div class="csb">Le tue richieste.</div><a class="btn o sm mt12" href="account.php#canzoni">Gestisci</a></div>
          <div class="c"><div style="font-weight:600">Tales del Branco</div><div class="csb">I fumetti del Delta.</div><a class="btn o sm mt12" href="tales.php">Vai ai Tales</a></div>
        </div>
      </section>
      <?php endif; /* dashboard-control: music */ ?>

      <?php if($__dc['games']): ?>
      <!-- GAMES -->
      <section class="pnl" id="p-games">
        <h1 class="pt">Giochi del Branco</h1>
        <div class="g4">
          <div class="c" style="text-align:center"><div style="font-weight:600">Ruota del Destino</div><a class="btn sm mt12" href="wheel.php">Gioca</a></div>
          <div class="c" style="text-align:center"><div style="font-weight:600">Slot</div><a class="btn sm mt12" href="giochi/slot.php">Gioca</a></div>
          <div class="c" style="text-align:center"><div style="font-weight:600">Dadi</div><a class="btn sm mt12" href="giochi/dadi.php">Gioca</a></div>
          <div class="c" style="text-align:center"><div style="font-weight:600">Memory</div><a class="btn sm mt12" href="giochi/memory.php">Gioca</a></div>
        </div>
        <div class="c mt12"><a class="btn o sm" href="game.php">Vai alla Sala Giochi &rarr;</a></div>
      </section>
      <?php endif; /* dashboard-control: games */ ?>

      <!-- PROFILE -->
      <section class="pnl" id="p-profile">
        <h1 class="pt">Profilo &amp; Wallet</h1>
        <div class="g2">
          <div class="c"><div class="cl">Nome</div><div><?=pe($name)?></div>
            <div class="cl mt12">Email</div><div><?=pe($email?:'—')?></div>
            <div class="cl mt12">SIC-ID</div><div style="font-family:'JetBrains Mono',monospace;color:var(--gold)"><?=pe($sic)?> <span style="cursor:pointer" onclick="cp('<?=pe($sic)?>')">&#128203;</span></div>
            <div class="cl mt12">Avatar</div>
            <div style="height:120px;background:var(--black);border-radius:10px;display:grid;place-items:center;margin-top:6px;overflow:hidden">
              <img src="<?=pe($avatar)?>" style="height:100%;object-fit:contain"></div>
            <a class="btn o sm mt12" href="account.php#avatarRPM">Personalizza avatar 3D (Ready Player Me) o foto</a></div>
          <div class="c"><div class="cl">Wallet &amp; binding SIC-ID</div>
            <div style="font-family:'JetBrains Mono',monospace;color:var(--gray);font-size:12px;margin-top:6px" id="wAddr2"><?= $wallet? pe($wallet):'Nessun wallet collegato' ?></div>
            <button class="btn sm mt12" id="bindBtn2">Collega wallet (MetaMask / Token Pocket)</button>
            <div class="csb" id="wMsg2" style="min-height:1em;margin-top:6px"></div>
            <div class="cl mt12">Documenti del Branco</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:6px"><a class="chip" href="genesys/biblioteca.php">📚 Biblioteca</a><a class="chip" href="ecosystem.php">Ecosystem</a><a class="chip" href="costituzione-branco.php">Costituzione</a><a class="chip" href="whitepaper.html">Whitepaper</a></div>
            <div class="cl mt12">Telegram</div><a class="btn o sm" href="account.php#telegram">Connetti il Branco su Telegram</a></div>
        </div>
      </section>

    </div>
  </div>
</div>
<script>
/* nav schede */
document.querySelectorAll('.ni').forEach(function(it){it.addEventListener('click',function(){
  document.querySelectorAll('.ni').forEach(function(i){i.classList.remove('on')});this.classList.add('on');
  document.querySelectorAll('.pnl').forEach(function(p){p.classList.remove('on')});
  var t=document.getElementById('p-'+this.dataset.p); if(t)t.classList.add('on');
  document.getElementById('sb').classList.remove('open');document.getElementById('ov').classList.remove('on');
});});
document.getElementById('burger').addEventListener('click',function(){document.getElementById('sb').classList.toggle('open');document.getElementById('ov').classList.toggle('on');});
document.getElementById('ov').addEventListener('click',function(){document.getElementById('sb').classList.remove('open');this.classList.remove('on');});
/* menu a tendina 2026-08-13: click sull'etichetta di gruppo comprime/espande le voci sotto (tutti aperti di default, stato non salvato) */
document.querySelectorAll('.ng .lbl').forEach(function(lbl){lbl.addEventListener('click',function(){lbl.closest('.ng').classList.toggle('collapsed');});});
function cp(t){navigator.clipboard.writeText(t).then(function(){alert('Copiato: '+t)});}
/* binding wallet reale: nonce -> firma -> bind (account.php) */
async function bindWallet(msgEl,addrEl){
  if(!window.ethereum){msgEl.textContent='Nessun wallet trovato (installa MetaMask).';return;}
  try{
    var accts=await window.ethereum.request({method:'eth_requestAccounts'});var addr=accts[0];
    msgEl.textContent='Preparo la firma…';
    var r=await fetch('account.php?action=wallet_nonce',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:''});
    var j=await r.json(); if(!j.ok){msgEl.textContent='Errore nonce.';return;}
    var sig=await window.ethereum.request({method:'personal_sign',params:[j.message,addr]});
    var r2=await fetch('account.php?action=wallet_bind_sig',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'address='+encodeURIComponent(addr)+'&provider=MetaMask&signature='+encodeURIComponent(sig)});
    var j2=await r2.json();
    if(j2.ok){addrEl.textContent=j2.wallet;msgEl.textContent='Wallet collegato al tuo SIC-ID ✓'+(j2.reward?(' (+'+j2.reward+' DRX)'):'');}
    else{msgEl.textContent=j2.error||'Firma non valida.';}
  }catch(e){msgEl.textContent='Operazione annullata.';}
}
var b1=document.getElementById('bindBtn');if(b1)b1.addEventListener('click',function(){bindWallet(document.getElementById('wMsg'),document.getElementById('wAddr'));});
var b2=document.getElementById('bindBtn2');if(b2)b2.addEventListener('click',function(){bindWallet(document.getElementById('wMsg2'),document.getElementById('wAddr2'));});
/* grafici */
document.addEventListener('DOMContentLoaded',function(){
  var rw=document.getElementById('rwChart');
  if(rw){new Chart(rw,{type:'radar',data:{labels:<?=json_encode($radarLabels,JSON_UNESCAPED_UNICODE)?>,datasets:[{data:<?=json_encode($radarData)?>,backgroundColor:'rgba(212,175,55,.15)',borderColor:'#D4AF37',pointBackgroundColor:'#D4AF37'}]},options:{responsive:true,maintainAspectRatio:false,scales:{r:{beginAtZero:true,max:10,ticks:{stepSize:2,color:'#9AA0A6'},grid:{color:'rgba(255,255,255,.06)'},pointLabels:{color:'#F5F5F7',font:{size:10}}}},plugins:{legend:{display:false}}}});}
  var sc=document.getElementById('simChart');
  if(sc){var base=<?=json_encode((float)$bal)?>;var proj=[];for(var i=0;i<12;i++){proj.push(Math.round(base*(1+i*0.12)));}
    new Chart(sc,{type:'line',data:{labels:['M1','M2','M3','M4','M5','M6','M7','M8','M9','M10','M11','M12'],datasets:[{label:'Proiezione DRX',data:proj,borderColor:'#D4AF37',backgroundColor:'rgba(212,175,55,.05)',fill:true,tension:.3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#9AA0A6',font:{size:9}}}},scales:{x:{grid:{display:false},ticks:{color:'#9AA0A6',font:{size:8}}},y:{grid:{color:'rgba(255,255,255,.04)'},ticks:{color:'#9AA0A6',font:{size:8}}}}}});}
});
/* CC-0055 (Code): accesso giornaliero +1 DRX + badge/claim airdrop */
function drToast(msg,ok){
  var t=document.createElement('div');
  t.textContent=msg;
  t.style.cssText='position:fixed;bottom:22px;left:50%;transform:translateX(-50%);max-width:86vw;text-align:center;background:'+(ok?'linear-gradient(135deg,#ffe08a,#D4AF37 55%,#b8942e)':'#16161F')+';color:'+(ok?'#160f00':'#F5F5F7')+';border:1px solid rgba(212,175,55,.4);padding:10px 20px;border-radius:40px;font-size:13px;font-weight:700;z-index:400;box-shadow:0 8px 24px rgba(0,0,0,.4);opacity:0;transition:opacity .25s';
  document.body.appendChild(t);
  requestAnimationFrame(function(){t.style.opacity='1';});
  setTimeout(function(){t.style.opacity='0';setTimeout(function(){t.remove();},300);},3600);
}
async function drgDaily(){
  try{
    var r=await fetch('dr-gamification-drx.php?action=daily');
    var j=await r.json();
    if(j&&j.awarded) drToast(j.msg||('+'+j.amount+' DRX per l\'accesso di oggi'),true);
  }catch(e){}
}
var drgUnclaimed=0;
async function drgStatus(){
  try{
    var r=await fetch('dr-gamification-drx.php?action=status');
    var j=await r.json();
    var card=document.getElementById('airdropCard'), badge=document.getElementById('airdropBadge');
    if(!card||!badge) return;
    if(j&&j.ok&&j.unclaimed&&j.unclaimed.n>0){
      drgUnclaimed=j.unclaimed.drx||0;
      badge.textContent=j.unclaimed.n+' · '+j.unclaimed.drx+' DRX';
      card.style.display='block';
    } else { card.style.display='none'; }
  }catch(e){}
}
/* CC-0062/CC-0063 (Cowork): scelta wallet/carriera + fee 1% mostrata nel toast */
function drgFeeMsg(j){ return (j&&j.fee>0) ? (' · trattenuto 1% ('+j.fee+' DRX bruciati)') : ''; }
async function drgClaim(){
  var btn=document.getElementById('claimBtn'); if(btn){btn.disabled=true;btn.textContent='Riscuoto…';}
  try{
    var scelta = window.drxSceltaChiedi ? await window.drxSceltaChiedi(drgUnclaimed) : 'wallet';
    var r=await fetch('dr-gamification-drx.php?action=claim',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'scelta='+encodeURIComponent(scelta)});
    var j=await r.json();
    if(j&&j.ok){ drToast('Riscosso: +'+j.riscosso+' DRX'+(j.onchain_accodato?' (in coda on-chain)':'')+drgFeeMsg(j),true); drgStatus(); }
    else { drToast((j&&j.msg)||'Nessun airdrop da riscuotere',false); }
  }catch(e){ drToast('Errore di rete, riprova.',false); }
  finally{ if(btn){btn.disabled=false;btn.textContent='Riscuoti';} }
}
document.addEventListener('DOMContentLoaded',function(){
  drgDaily(); drgStatus();
  var cb=document.getElementById('claimBtn'); if(cb) cb.addEventListener('click',drgClaim);
});
/* CC-0056 (Code, handoff): stake NFT Genesys - status/stake/unstake/claim */
function nftEsc(s){ var d=document.createElement('div'); d.textContent=(s==null?'':String(s)); return d.innerHTML; }
async function nftLoadStatus(){
  try{
    var r=await fetch('dr-nft-stake.php?action=status');
    var j=await r.json(); if(!j||!j.ok) return;
    var m=document.getElementById('nftStakeMaturato'); if(m) m.textContent=j.maturato+' DRX maturati';
    /* CC-0060 (Code, handoff): badge grado + barra "ti mancano N NFT" (bonus collezione) */
    if(j.bonus){
      var bb=document.getElementById('nftStakeBonusBadge'); if(bb) bb.textContent=nftEsc(j.bonus.grado)+' · ×'+nftEsc(j.bonus.mult.toFixed(2));
      var bn=document.getElementById('nftStakeBonusN'); if(bn) bn.textContent=j.bonus.n+(j.bonus.n===1?' NFT nella collezione':' NFT nella collezione');
      var bf=document.getElementById('nftStakeBonusFill'), bnext=document.getElementById('nftStakeBonusNext');
      if(j.prossimo_bonus){
        var pct=Math.max(2,Math.min(100, Math.round(100*j.bonus.n/j.prossimo_bonus.soglia)));
        if(bf) bf.style.width=pct+'%';
        if(bnext) bnext.textContent='Ti mancano '+j.prossimo_bonus.mancano+' NFT per salire a '+nftEsc(j.prossimo_bonus.grado)+' (×'+nftEsc(j.prossimo_bonus.mult.toFixed(2))+' su tutto lo stake)';
      }else{
        if(bf) bf.style.width='100%';
        if(bnext) bnext.textContent='Grado massimo raggiunto: bonus ×'+nftEsc(j.bonus.mult.toFixed(2))+' su tutto lo stake.';
      }
    }
    var rows=document.getElementById('nftStakeRows'), tbl=document.getElementById('nftStakeTbl'), empty=document.getElementById('nftStakeEmpty');
    if(rows){
      rows.innerHTML='';
      (j.posizioni||[]).forEach(function(p){
        var tr=document.createElement('tr');
        var lab=(p.src==='pioniere')?('Nodo Pioniere #'+nftEsc(p.src_id)):('Sigillo #'+nftEsc(p.src_id));
        tr.innerHTML='<td>'+lab+'</td><td><span class="chip">'+nftEsc(p.rarita)+'</span></td><td>'+nftEsc(p.rate)+'</td><td class="csb">'+nftEsc(p.pending)+'</td><td><button class="btn o sm" data-unstake="'+nftEsc(p.id)+'" type="button">Ritira</button></td>';
        rows.appendChild(tr);
      });
      var has=(j.posizioni||[]).length>0;
      if(tbl) tbl.style.display=has?'table':'none';
      if(empty) empty.style.display=has?'none':'block';
      rows.querySelectorAll('[data-unstake]').forEach(function(b){ b.addEventListener('click',function(){ nftUnstake(b.getAttribute('data-unstake')); }); });
    }
    var av=document.getElementById('nftStakeAvail'), avw=document.getElementById('nftStakeAvailWrap');
    if(av){
      av.innerHTML='';
      (j.disponibili||[]).forEach(function(d){
        var b=document.createElement('button'); b.type='button'; b.className='btn o sm';
        b.textContent=d.label+' ('+d.rate+' DRX/g)';
        b.addEventListener('click',function(){ nftStake(d.src,d.src_id); });
        av.appendChild(b);
      });
      if(avw) avw.style.display=(j.disponibili||[]).length?'block':'none';
    }
  }catch(e){}
}
async function nftStake(src,srcId){
  try{
    var r=await fetch('dr-nft-stake.php?action=stake',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'src='+encodeURIComponent(src)+'&src_id='+encodeURIComponent(srcId)});
    var j=await r.json();
    if(j&&j.ok) drToast('In stake: '+j.rarita+' ('+j.rate_giorno+' DRX/giorno)',true); else drToast((j&&j.msg)||'Errore stake',false);
  }catch(e){ drToast('Errore di rete, riprova.',false); }
  nftLoadStatus();
}
async function nftUnstake(id){
  try{
    var r=await fetch('dr-nft-stake.php?action=unstake',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+encodeURIComponent(id)});
    var j=await r.json();
    if(j&&j.ok) drToast('Ritirato, incassati '+j.ricompensa_finale+' DRX',true); else drToast((j&&j.msg)||'Errore',false);
  }catch(e){ drToast('Errore di rete, riprova.',false); }
  nftLoadStatus();
}
async function nftClaim(){
  var btn=document.getElementById('nftClaimBtn'); if(btn){btn.disabled=true;btn.textContent='Riscuoto…';}
  try{
    var maturatoEl=document.getElementById('nftStakeMaturato');
    var maturato=maturatoEl?parseInt(maturatoEl.textContent,10)||0:0;
    var scelta = window.drxSceltaChiedi ? await window.drxSceltaChiedi(maturato) : 'wallet';
    var r=await fetch('dr-nft-stake.php?action=claim',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'scelta='+encodeURIComponent(scelta)});
    var j=await r.json();
    if(j&&j.ok) drToast('Riscosso: +'+j.riscosso+' DRX'+(j.onchain_accodato?' (in coda on-chain)':'')+drgFeeMsg(j),true); else drToast((j&&j.msg)||'Niente da riscuotere ancora',false);
  }catch(e){ drToast('Errore di rete, riprova.',false); }
  finally{ if(btn){btn.disabled=false;btn.textContent='Riscuoti maturato';} }
  nftLoadStatus();
}
/* CC-0057 (Code, handoff): compensi network - stato personale (pending/payable/paid) */
async function compLoadStatus(){
  try{
    var r=await fetch('dr-compensi.php?action=my');
    var j=await r.json(); if(!j||!j.ok) return;
    var g=document.getElementById('compGate');
    if(g){ g.textContent=j.gate_aperto?'sbloccati':('in sospeso fino al '+j.gate+'° Kit'); g.className='chip '+(j.gate_aperto?'g':'o'); }
    var pe=document.getElementById('compPending'); if(pe) pe.textContent=j.pending;
    var pa=document.getElementById('compPayable'); if(pa) pa.textContent=j.payable;
    var pd=document.getElementById('compPaid'); if(pd) pd.textContent=j.paid;
  }catch(e){}
}
document.addEventListener('DOMContentLoaded',function(){
  nftLoadStatus(); compLoadStatus();
  /* BUG (Cowork, 2026-07-30): il bottone "Riscuoti maturato" non era mai stato agganciato a nftClaim() */
  var ncb=document.getElementById('nftClaimBtn'); if(ncb) ncb.addEventListener('click',nftClaim);
});
/* CC-0058 (Code, handoff): withdraw - stato richieste utente (?action=my) */
function wdUEsc(s){ var d=document.createElement('div'); d.textContent=(s==null?'':String(s)); return d.innerHTML; }
function wdUStatoLabel(st,after){
  if(st==='review'){
    var t=after?(new Date(after.replace(' ','T')+'Z').getTime()-Date.now()):0;
    if(t>0){ var h=Math.floor(t/3600000),m=Math.floor((t%3600000)/60000); return '<span class="chip o">in verifica ('+h+'h '+m+'m)</span>'; }
    return '<span class="chip o">in verifica</span>';
  }
  if(st==='queued') return '<span class="chip">in coda invio</span>';
  if(st==='sent') return '<span class="chip g">inviato</span>';
  if(st==='rejected') return '<span class="chip" style="border-color:#FB6B00;color:#FB6B00">rifiutato</span>';
  return '<span class="chip">'+wdUEsc(st)+'</span>';
}
async function wdUserLoad(){
  try{
    var r=await fetch('dr-withdraw.php?action=my'); var j=await r.json(); if(!j||!j.ok) return;
    var richieste=j.richieste||[];
    var cnt=document.getElementById('wdUserCount'); if(cnt) cnt.textContent=richieste.length;
    var rows=document.getElementById('wdUserRows'), tbl=document.getElementById('wdUserTbl'), empty=document.getElementById('wdUserEmpty');
    if(!rows) return;
    rows.innerHTML='';
    richieste.forEach(function(it){
      var tr=document.createElement('tr');
      tr.innerHTML='<td class="csb">'+wdUEsc((it.created||'').substr(0,16))+'</td>'
        +'<td class="gold">'+wdUEsc(it.amount)+'</td><td><span class="chip">'+wdUEsc(it.kind)+'</span></td>'
        +'<td>'+wdUStatoLabel(it.status,it.review_after)+'</td>'
        +'<td class="csb" style="font-family:\'JetBrains Mono\',monospace;font-size:11px">'+(it.txhash?wdUEsc(it.txhash.substr(0,10))+'…':'&mdash;')+'</td>';
      rows.appendChild(tr);
    });
    var has=richieste.length>0;
    if(tbl) tbl.style.display=has?'table':'none';
    if(empty) empty.style.display=has?'none':'block';
  }catch(e){}
}
document.addEventListener('DOMContentLoaded',function(){ wdUserLoad(); });
</script>
<script src="/inc.js?v=35" defer></script>
</body></html>
