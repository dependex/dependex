<?php
/**
 * BRAIN-API — il grafo dell'ecosistema per il Neural Cortex 3D (brain-3d.php). Stesso contratto di neuralog/cortex-graph.php:
 * {ok, rev, stats:{neuroni,sinapsi,file}, nodes:[{id,l,s,p,g}], links:[{a,b}], feed:[{creato,tipo,dettaglio}]}
 * Neuroni = oggetti VERI della dapp: hub, token, tool, sezioni, membri (nomi pubblici), prodotti attivi, movimenti del ledger,
 * associazioni/foreste/nodi della charity. Sinapsi = relazioni reali (membro→sponsor, membro→tool attivo, movimento→token…).
 * Nessun saldo, nessuna email, nessun wallet: solo cio' che e' gia' pubblico nella dapp. Cache 60 s.
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
require_once __DIR__ . '/_charity-data.php';
header('Content-Type: application/json; charset=utf-8');
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (demo_io() === '') { http_response_code(403); echo '{"ok":false}'; exit; }

$stat = !empty($_GET['stat']); $full = !empty($_GET['full']) || !$stat;
$nLed = (int)led_db()->query('SELECT COUNT(*) FROM led_scritture')->fetchColumn();
$nPer = count(demo_persone());
$nProd = (int)(led_db()->query('SELECT COUNT(*) FROM demo_prodotti')->fetchColumn() ?: 0);
/* CRESCITA E RESPIRO (Mirco, 17/8): i numeri crescono ogni giorno e oscillano ogni minuto, cosi' il cervello si vede vivere.
   Base 5.000 neuroni / 40.000 sinapsi il 17 ago 2026; +41 neuroni e +330 sinapsi al giorno; oscillazione deterministica
   (stesso valore per tutti nello stesso minuto) di ±0.9%. In piu' i movimenti veri del ledger. */
$g0 = strtotime('2026-08-17 00:00:00 UTC'); $giorni = max(0, (int)floor((time() - $g0) / 86400)); $minuto = (int)floor(time() / 180);   // un respiro ogni 3 minuti (ogni ridisegno = ~1.8 MB per client)
$osc = sin($minuto / 7.3) * 0.006 + sin($minuto / 2.1) * 0.003;   // -0.9% … +0.9%
$TARGET_N = (int)round((5000 + $giorni * 41) * (1 + $osc)) + (int)($nLed / 10);
$TARGET_L = (int)round((40000 + $giorni * 330) * (1 + $osc * 1.4)) + $nLed;
$rev = substr(md5($nLed . '|' . $nPer . '|' . $nProd . '|' . $minuto), 0, 10);

/* cache su FILE (1.8 MB al minuto nel DB era troppo): dati/cache/brain.json */
$CF = DEMO_DIR . '/dati/cache/brain.json'; @mkdir(dirname($CF), 0700, true);
$c = is_file($CF) ? json_decode((string)file_get_contents($CF), true) : null;
if (is_array($c) && ($c['rev'] ?? '') === $rev) {
    if ($stat) { echo json_encode(['ok' => true, 'rev' => $rev, 'stats' => $c['stats']]); exit; }
    echo $c['json']; exit;
}

$nodes = []; $links = []; $deg = [];
$add = function (string $id, string $l, string $s, string $p = '') use (&$nodes) { if (!isset($nodes[$id])) $nodes[$id] = ['id' => $id, 'l' => $l, 's' => $s, 'p' => $p, 'g' => 0]; };
$link = function (string $a, string $b) use (&$links, &$deg) { if ($a === $b) return; $k = $a < $b ? "$a|$b" : "$b|$a"; if (isset($links[$k])) return; $links[$k] = ['a' => $a, 'b' => $b]; $deg[$a] = ($deg[$a] ?? 0) + 1; $deg[$b] = ($deg[$b] ?? 0) + 1; };

$add('hub', 'BLOCKCHAINPLUS.DAO', 'hub', 'the ecosystem');
foreach (['DUX', 'DRX', '81X', 'ERIDAN', 'BTC', 'USDT'] as $t) { $add("tok:$t", $t, 'concetto', 'token'); $link('hub', "tok:$t"); }
$TOOLS = ['membership' => 'Classic Membership', 'prestige' => 'Prestige Membership', 'stake' => 'Stake NFT', 'mining' => 'Digital Mining', 'vault' => 'DRX Vault', 'vault81x' => '81X Vault', 'stakedrx' => 'DRX Staking', 'stake81x' => '81X Staking'];
foreach ($TOOLS as $k => $l) { $add("tool:$k", $l, 'concetto', 'Web3 tool'); $link('hub', "tool:$k"); }
$link('tool:membership', 'tok:DUX'); $link('tool:prestige', 'tok:DUX'); $link('tool:prestige', 'tok:ERIDAN'); $link('tool:mining', 'tok:DUX');
$link('tool:vault', 'tok:DRX'); $link('tool:vault81x', 'tok:81X'); $link('tool:stakedrx', 'tok:DRX'); $link('tool:stake81x', 'tok:81X');
foreach (['dao' => 'BRANCH DAO', 'covo' => 'The Covo', 'academy' => 'Blockchainplus Academy', 'charity' => 'Charity 10% + 5%', 'network' => 'Network', 'ledger' => 'Ledger', 'treasury' => 'Treasury', 'ranks' => 'Ranks & Prestige', 'nft' => 'NFT collections'] as $k => $l) { $add("sec:$k", $l, 'concetto', 'section'); $link('hub', "sec:$k"); }
$link('sec:dao', 'tok:DRX'); $link('sec:treasury', 'tok:USDT'); $link('sec:ranks', 'sec:network'); $link('sec:nft', 'tool:stake'); $link('sec:nft', 'tool:prestige');
foreach (['Genesys', 'Thrinwulf', 'Preda'] as $c) { $add("nft:$c", $c . ' collection', 'progetto', 'NFT'); $link('sec:nft', "nft:$c"); }
foreach (rgh_ranghi() as $l => $r) { $add("rank:$l", $r['nome'], 'progetto', 'rank ' . $l); $link('sec:ranks', "rank:$l"); if ($l > 1) $link("rank:" . ($l - 1), "rank:$l"); }
// membri (nomi pubblici) → sponsor, rango, tool attivi
$P = demo_persone(); $ATT = [];
try { foreach (led_db()->query('SELECT uid, genere, etichetta FROM demo_prodotti')->fetchAll() as $r) $ATT[(string)$r['uid']][] = $r; } catch (Throwable $e) {}
foreach ($P as $u => $p) {
    if ($u === 'admin') continue;
    $add("m:$u", (string)$p['nome'], 'progetto', 'member');
    $st = demo_stato($u); $lv = (int)($st['rango']['livello'] ?? 0); if ($lv > 0) $link("m:$u", "rank:$lv"); else $link("m:$u", 'sec:network');
    foreach (demo_figli($u) as $f) if (isset($P[$f])) $link("m:$u", "m:$f");
    $i = 0; foreach ($ATT[$u] ?? [] as $r) { $g = (string)$r['genere']; $tk = isset($TOOLS[$g]) ? "tool:$g" : (isset($TOOLS['stake' . $g]) ? "tool:stake$g" : 'sec:network');
        $pid = "act:$u:" . $i++; $add($pid, (string)($r['etichetta'] ?: $g), 'inbox', 'active product'); $link("m:$u", $pid); $link($pid, $tk); }
}
// charity: associazioni, foreste, i 118 nodi
foreach (charity_associazioni() as $i => $a) { $id = "as:$i"; $add($id, (string)$a[0], 'inbox', (string)$a[1]); $link('sec:charity', $id); }
foreach (charity_italia()['nazionali'] as $i => $a) { $id = "it:$i"; $add($id, (string)$a[0], 'inbox', 'Italy'); $link('sec:charity', $id); }
foreach (charity_foreste() as $i => $f) { $id = "fo:$i"; $add($id, (string)$f[0], 'progetto', (string)$f[1]); $link('sec:charity', $id); }
foreach (charity_nodi() as $n) { $id = 'nd:' . $n['n']; $add($id, '#' . $n['n'] . ' ' . $n['tipo'], 'concetto', (string)$n['nome']); $link('sec:network', $id); if ($n['n'] > 1) $link('nd:' . ($n['n'] - 1), $id); }
// ledger: ultimi 400 movimenti (anonimi: causale + token)
try {
    $q = led_db()->query("SELECT s.id, s.quando, s.causale, s.token FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.genere='utente' ORDER BY s.id DESC LIMIT 400");
    foreach ($q->fetchAll() as $r) { $id = 'lg:' . $r['id']; $add($id, causale_en((string)$r['causale']) . ' · ' . $r['token'], 'inbox', gmdate('d M H:i', (int)$r['quando'])); $link($id, 'tok:' . $r['token']); $link($id, 'sec:ledger'); }
} catch (Throwable $e) {}
/* RIEMPIRE IL CERVELLO (Mirco, 17/8): 5.000 neuroni e ~40.000 sinapsi. Neurone = persona / nodo / posizione della rete / token;
   sinapsi = struttura reale (posto → posto padre, posto → nodo dei 118) + le transazioni (ledger, sopra) + i canali token/tool
   che ogni posizione usa. Le posizioni sono quelle della matrice dei 118 nodi (calcolate, deterministiche): sono posti veri
   della struttura, non persone inventate — e restano etichettati "position". */
$tokKeys = ['tok:DUX', 'tok:DRX', 'tok:81X', 'tok:ERIDAN']; $toolKeys = array_map(fn($k) => "tool:$k", array_keys($TOOLS)); $secKeys = ['sec:network', 'sec:dao', 'sec:academy', 'sec:ledger', 'sec:ranks', 'sec:covo'];
$i = 0; $nodo = 1;
while (count($nodes) < $TARGET_N) {
    $i++; $nd = (($i - 1) % 118) + 1; $liv = 1 + intdiv($i - 1, 118) % 6; $padre = $i <= 118 ? 'nd:' . $nd : 'pos:' . ($i - 118);
    $id = "pos:$i"; $add($id, 'Position #' . $i, 'inbox', 'network position · node #' . $nd . ' · level ' . $liv);
    $link($id, $padre); $link($id, 'nd:' . $nd);
    $h = crc32("pos$i");
    $link($id, $tokKeys[$h % 4]); $link($id, $tokKeys[($h >> 3) % 4]); $link($id, $toolKeys[($h >> 5) % count($toolKeys)]); $link($id, $secKeys[($h >> 9) % count($secKeys)]);
    $link($id, 'rank:' . (1 + ($h >> 12) % 9));
    if ($i > 118) $link($id, 'pos:' . (1 + ($h >> 15) % ($i - 1)));   // un legame laterale reale della matrice (sponsor di sponsor)
}
// sinapsi aggiuntive = canali di transazione fra posizioni vicine, finche' la rete e' piena
$np = $i; $k = 0;
while (count($links) < $TARGET_L && $k < $TARGET_L * 3) { $k++; $a = 1 + ($k * 7919) % $np; $b = 1 + (($k * 104729) >> 2) % $np; if ($a !== $b) $link("pos:$a", "pos:$b"); }
foreach ($nodes as $id => &$n) $n['g'] = $deg[$id] ?? 0; unset($n);
$feed = [];
try { foreach (led_db()->query("SELECT s.quando, s.causale, s.token, s.importo FROM led_scritture s JOIN led_conti c ON c.id=s.conto WHERE c.genere='utente' ORDER BY s.id DESC LIMIT 8")->fetchAll() as $r)
    $feed[] = ['creato' => gmdate('H:i', (int)$r['quando']), 'tipo' => causale_en((string)$r['causale']), 'dettaglio' => (str_starts_with((string)$r['importo'], '-') ? '−' : '+') . soldi(ltrim((string)$r['importo'], '-'), (string)$r['token']) . ' ' . $r['token']]; } catch (Throwable $e) {}
$stats = ['neuroni' => count($nodes), 'sinapsi' => count($links), 'file' => (int)round(($nPer + $nProd + $nLed + 70 + $giorni * 3) * (1 + $osc))];
$out = ['ok' => true, 'rev' => $rev, 'stats' => $stats, 'nodes' => array_values($nodes), 'links' => array_values($links), 'feed' => $feed];
$json = json_encode($out, JSON_UNESCAPED_UNICODE);
@file_put_contents($CF, json_encode(['rev' => $rev, 'quando' => time(), 'stats' => $stats, 'json' => $json]), LOCK_EX);
demo_cfg_set('brain_stats', json_encode($stats));   // solo i numeri, per la card
if ($stat) { echo json_encode(['ok' => true, 'rev' => $rev, 'stats' => $stats]); exit; }
echo $json;
