<?php
/**
 * NET-API — l'API del tool Network della dapp. Stesso contratto di genesys/dr-mia-rete-api.php del sito
 * (azione=mia|figli|nodo|ramo|pesi|cerca|fermi|crescita), cosi' il disegno (network.php) e' lo stesso.
 *  · utente: il SUO ramo della rete della dapp (demo_figli / demo_persone). Solo lettura, whitelist di campi.
 *  · admin con &tutto=1: la rete VERA del sito (fino a 5.000.000 di posti) via genesys/dr-network-tree-api.php,
 *    chiave DR_ADMIN_KEY dal .env, mai nel browser.
 * Il "posto" numerico della dapp e' un indice stabile della persona (uid ↔ posto), perche' il disegno ragiona a numeri.
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
header('Content-Type: application/json; charset=utf-8');
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
$IO = demo_io();
if ($IO === '' || $IO === null) { http_response_code(403); echo '{"ok":false,"err":"login required"}'; exit; }
function na_out(array $d): void { echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
function na_err(string $m, int $c = 400): void { http_response_code($c); na_out(['ok' => false, 'err' => $m]); }

$az = preg_replace('/[^a-z]/', '', (string)($_GET['azione'] ?? 'mia'));
$tutto = !empty($_GET['tutto']) && demo_admin_sessione();

/* ======================= ADMIN: la rete del sito, in proxy ======================= */
if ($tutto) {
    $key = demo_env('DR_ADMIN_KEY', '');
    if ($key === '') na_err('DR_ADMIN_KEY missing in .env — the dapp cannot read the site network yet', 503);
    $mappa = ['mia' => 'vista', 'figli' => 'figli', 'nodo' => 'nodo', 'ramo' => 'ramo', 'pesi' => 'pesi', 'cerca' => 'cerca', 'fermi' => 'fermi', 'crescita' => 'crescita'];
    if (!isset($mappa[$az])) na_err('action');
    $q = ['azione' => $mappa[$az], 'key' => $key];
    foreach (['posto', 'limit', 'offset', 'q', 'posti', 'contiene', 'solo_occupati', 'giorni'] as $k) if (isset($_GET[$k])) $q[$k] = (string)$_GET[$k];
    $base = demo_env('DR_SITO_TREE_API', 'https://destinorandagio.it/genesys/dr-network-tree-api.php');
    $ch = curl_init($base . '?' . http_build_query($q));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_CONNECTTIMEOUT => 6, CURLOPT_HTTPHEADER => ['User-Agent: DAOBranch/1.0']]);
    $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($r === false || $code !== 200) na_err('site network unreachable (HTTP ' . $code . ')', 502);
    $d = json_decode($r, true); if (!is_array($d)) na_err('site network: bad answer', 502);
    if ($az === 'mia') {
        $rin = function (array $n) use (&$rin): array { if (isset($n['children'])) { $n['_figli'] = array_map($rin, $n['children']); unset($n['children']); } if ((int)($n['posto'] ?? -1) === 0) $n['io'] = 1; return $n; };
        $alb = isset($d['albero']) ? $rin($d['albero']) : null;
        na_out(['ok' => (bool)($d['ok'] ?? false), 'mio' => 0, 'albero' => $alb, 'totale' => (int)($alb['figli'] ?? 0),
                'kpi' => ['figli' => (int)($alb['figli'] ?? 0), 'occupati' => (int)($alb['rete'] ?? 0), 'attivi' => (int)($alb['rete_attivi'] ?? 0), 'liberi' => 0]]);
    }
    if ($az === 'figli' && isset($d['tot_figli']) && !isset($d['totale'])) $d['totale'] = (int)$d['tot_figli'];
    na_out($d);
}

/* ======================= UTENTE: il suo ramo nella dapp ======================= */
$P = demo_persone();
if (!isset($P[$IO])) $P[$IO] = ['nome' => demo_nome($IO), 'sic' => (string)(demo_account($IO)['sic'] ?? ''), 'nft' => 0];
$UIDS = array_keys($P); sort($UIDS, SORT_STRING);
$POSTO = []; foreach ($UIDS as $i => $u) $POSTO[$u] = $i + 1;   // posto 1..N, stabile finche' la lista non cambia
$MIO = $POSTO[$IO];
function na_uid(int $posto): ?string { global $UIDS; return $UIDS[$posto - 1] ?? null; }
function na_nel_ramo(string $radice, string $x): bool { if ($radice === $x) return true; foreach (demo_figli($radice) as $f) if (na_nel_ramo($f, $x)) return true; return false; }
function na_sotto(string $u, int &$att): int { $n = 0; foreach (demo_figli($u) as $f) { $n++; if (na_attivo($f)) $att++; $n += na_sotto($f, $att); } return $n; }
function na_attivo(string $u): bool { static $c = []; if (!isset($c[$u])) { $s = demo_stato($u); $c[$u] = ((float)($s['xp']['proprio'] ?? 0)) > 0; } return $c[$u]; }
function na_percorso(string $radice, string $x, array $acc = []): ?array { $acc[] = $radice; if ($radice === $x) return $acc; foreach (demo_figli($radice) as $f) { $r = na_percorso($f, $x, $acc); if ($r) return $r; } return null; }
function na_nodo(string $u, int $liv): array {
    global $P, $POSTO, $IO;
    $att = 0; $sotto = na_sotto($u, $att); $fig = demo_figli($u); $io = $u === $IO;
    $st = demo_stato($u);
    $n = ['posto' => $POSTO[$u], 'padre' => 0, 'livello' => $liv, 'tipo' => 'user', 'status' => '', 'stato' => na_attivo($u) ? 'attivo' : 'prenotato',
          'occupato' => 1, 'attivo' => na_attivo($u) ? 1 : 0, 'nome' => $io ? 'You' : (string)($P[$u]['nome'] ?? demo_nome($u)), 'figli' => count($fig),
          'preso_il' => '', 'io' => $io ? 1 : 0, 'rete' => $sotto, 'rete_attivi' => $att, 'rete_diretti' => count($fig),
          'rango' => (string)($st['rango']['nome'] ?? ''), 'prestigio' => (string)($st['prestigio']['nome'] ?? '')];
    if ($io) { $n['sic'] = (string)($P[$u]['sic'] ?? ''); $n['sic_personale'] = (string)($P[$u]['sic'] ?? ''); }
    return $n;
}
$posto = isset($_GET['posto']) ? (int)$_GET['posto'] : $MIO;
$u = na_uid($posto);
$soloOcc = !empty($_GET['solo_occupati']);   // nella dapp ogni posto e' una persona: il filtro non toglie nulla

switch ($az) {
    case 'mia': {
        $root = na_nodo($IO, 0);
        $fig = demo_figli($IO); $lim = 80;
        $root['_figli'] = array_map(fn($f) => na_nodo($f, 1), array_slice($fig, 0, $lim));
        na_out(['ok' => true, 'mio' => $MIO, 'albero' => $root, 'totale' => count($fig),
                'kpi' => ['figli' => count($fig), 'occupati' => $root['rete'], 'attivi' => $root['rete_attivi'], 'liberi' => 0]]);
    }
    case 'figli': {
        if ($u === null || !na_nel_ramo($IO, $u)) na_err('This position is not in your branch.', 403);
        $lim = isset($_GET['limit']) ? max(1, min(200, (int)$_GET['limit'])) : 80; $off = max(0, (int)($_GET['offset'] ?? 0));
        $fig = demo_figli($u); $liv = count(na_percorso($IO, $u) ?? [$IO]);
        if (!empty($_GET['contiene'])) { $cu = na_uid((int)$_GET['contiene']); $k = $cu ? array_search($cu, $fig, true) : false; if ($k !== false) $off = intdiv((int)$k, $lim) * $lim; }
        na_out(['ok' => true, 'posto' => $posto, 'offset' => $off, 'totale' => count($fig), 'figli' => array_map(fn($f) => na_nodo($f, $liv), array_slice($fig, $off, $lim))]);
    }
    case 'nodo': {
        if ($u === null || !na_nel_ramo($IO, $u)) na_err('This position is not in your branch.', 403);
        $perc = na_percorso($IO, $u) ?? [$IO]; $n = na_nodo($u, count($perc) - 1);
        $fig = demo_figli($u); $attF = 0; foreach ($fig as $f) if (na_attivo($f)) $attF++;
        $catena = []; foreach ($perc as $x) { if ($x === $u) break; $catena[] = ['posto' => $POSTO[$x], 'nome' => $x === $IO ? 'your position' : (string)($P[$x]['nome'] ?? '')]; }
        na_out(['ok' => true, 'nodo' => $n, 'kpi' => ['figli' => count($fig), 'occupati' => count($fig), 'attivi' => $attF, 'liberi' => 0], 'catena' => $catena]);
    }
    case 'ramo': {
        if ($u === null || !na_nel_ramo($IO, $u)) na_err('This position is not in your branch.', 403);
        $att = 0; $s = na_sotto($u, $att);
        na_out(['ok' => true, 'posto' => $posto, 'discendenti' => $s, 'occupati' => $s, 'attivi' => $att]);
    }
    case 'pesi': {
        $lista = trim((string)($_GET['posti'] ?? '')); $out = [];
        $ids = $lista === '' ? [$MIO] : array_slice(array_values(array_filter(array_map('intval', explode(',', $lista)), fn($x) => $x > 0)), 0, 400);
        foreach ($ids as $pz) { $x = na_uid($pz); if ($x === null || !na_nel_ramo($IO, $x)) continue; $att = 0; $s = na_sotto($x, $att); $out[$pz] = ['occupati' => $s + 1, 'attivi' => $att + (na_attivo($x) ? 1 : 0), 'posti' => $s + 1]; }
        na_out(['ok' => true, 'pesi' => $out]);
    }
    case 'cerca': {
        $q = trim((string)($_GET['q'] ?? '')); if ($q === '') na_out(['ok' => true, 'risultati' => []]);
        $ris = [];
        foreach ($P as $x => $p) { if (!na_nel_ramo($IO, $x)) continue;
            if (stripos((string)$p['nome'], $q) !== false || stripos((string)($p['sic'] ?? ''), $q) !== false || stripos($x, $q) !== false || (ctype_digit($q) && (int)$q === $POSTO[$x])) {
                $perc = na_percorso($IO, $x) ?? [$IO]; $n = na_nodo($x, count($perc) - 1); $n['percorso'] = array_map(fn($y) => $POSTO[$y], $perc); $ris[] = $n; if (count($ris) >= 20) break; } }
        na_out(['ok' => true, 'risultati' => $ris]);
    }
    case 'fermi': {
        // chi non porta nessuno: persone del ramo senza figli e non attive (niente date d'ingresso nella dapp: lo diciamo)
        $out = []; $tutti = []; $walk = function (string $x, int $liv) use (&$walk, &$tutti) { foreach (demo_figli($x) as $f) { $tutti[] = [$f, $liv + 1]; $walk($f, $liv + 1); } }; $walk($IO, 0);
        foreach ($tutti as [$x, $liv]) { if (count(demo_figli($x)) === 0 && !na_attivo($x)) $out[] = ['posto' => $POSTO[$x], 'nome' => (string)($P[$x]['nome'] ?? ''), 'entrato' => '', 'fermo_da' => 0, 'mai' => 1, 'livello' => $liv]; if (count($out) >= 60) break; }
        na_out(['ok' => true, 'fermi' => $out, 'quanti' => count($out), 'senza_data' => count($tutti), 'giorni' => (int)($_GET['giorni'] ?? 14)]);
    }
    case 'crescita': {
        na_out(['ok' => true, 'dati' => []]);   // la dapp non ha ancora la data d'ingresso per persona: niente curva finta
    }
    default: na_err('unknown action');
}
