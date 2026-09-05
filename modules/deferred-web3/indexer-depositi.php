<?php
/**
 * INDEXER DEPOSITI — Polygon → Deposit wallet, per indirizzo di account.
 * DAO BRANCH · 17 agosto 2026
 *
 * Cosa fa: legge i Transfer ERC-20 (USDT, DUX, DRX, 81X) verso gli indirizzi di deposito
 * degli account (derivati dall'xpub, salvati in demo_account.dep_addr) e accredita il
 * DEPOSIT WALLET dell'account giusto. USDT diventa DUX 1:1 all'istante (led_swap).
 * Ogni accredito porta tx_hash+log_index: l'indice UNIQUE del registro impedisce i doppi.
 * I bonifici sull'indirizzo GLOBALE (senza account) finiscono in idx_non_attribuiti per
 * l'accredito manuale dell'admin: mai indovinare di chi sono i soldi.
 *
 * Config dal .env unico: POLYGON_RPC · USDT_POLYGON · DUX_ADDRESS · DRX_ADDR · X81_ADDR
 *                        DR_DEPOSITO_ADDR · DR_PONTE_KEY (per il lancio via web)
 * Lancio: CLI  php indexer-depositi.php
 *         web  indexer-depositi.php?key=<DR_PONTE_KEY>   (cron Hostinger ogni 5 minuti)
 * Sicurezza: legge soltanto (eth_getLogs). Nessuna chiave privata, nessuna firma.
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';

const IDX_CONFERME   = 12;      // blocchi di conferma prima di accreditare
const IDX_PASSO      = 1000;    // blocchi per chiamata (limite comodo per gli RPC pubblici)
const IDX_MAX_BLOCCHI = 20000;  // per corsa: il resto alla prossima
const IDX_TRANSFER   = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';

function idx_out(array $a): void { header('Content-Type: application/json; charset=utf-8'); echo json_encode($a, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); exit; }

/* --- accesso: CLI, oppure web con la chiave del ponte --- */
if (PHP_SAPI !== 'cli') {
    $k = demo_env('DR_PONTE_KEY', '');
    if ($k === '' || !hash_equals($k, (string)($_GET['key'] ?? ''))) { http_response_code(401); idx_out(['ok' => false, 'err' => 'key']); }
}

/* --- config --- */
$RPC = demo_env('POLYGON_RPC', demo_env('POLYGON_RPC_URL', 'https://polygon-bor-rpc.publicnode.com'));
$TOKENS = [   // contratto => [simbolo, decimali on-chain, token interno accreditato]
    strtolower(demo_env('USDT_POLYGON', '0xc2132D05D31c914a87C6611C10748AEb04B58e8F')) => ['USDT', 6, 'USDT'],
    strtolower(demo_env('DUX_ADDRESS', '')) => ['DUX', (int)demo_env('DUX_DECIMALS', '6'), 'DUX'],
    strtolower(demo_env('DRX_ADDR', ''))    => ['DRX', 18, 'DRX'],
    strtolower(demo_env('X81_ADDR', ''))    => ['81X', 18, '81X'],
];
unset($TOKENS['']);
$GLOBALE = strtolower(demo_env('DR_DEPOSITO_ADDR', DR_DEPOSITO_GLOBALE));

/* --- tabelle proprie --- */
$db = led_db();
$db->exec('CREATE TABLE IF NOT EXISTS idx_stato (contratto TEXT PRIMARY KEY, ultimo_blocco INTEGER NOT NULL, aggiornato INTEGER NOT NULL)');
$db->exec('CREATE TABLE IF NOT EXISTS idx_non_attribuiti (tx_hash TEXT, log_index INTEGER, contratto TEXT, simbolo TEXT, importo TEXT, da TEXT, a TEXT, blocco INTEGER, quando INTEGER, gestito INTEGER DEFAULT 0, PRIMARY KEY(tx_hash,log_index))');
$db->exec('CREATE TABLE IF NOT EXISTS idx_log (id INTEGER PRIMARY KEY, quando INTEGER, testo TEXT)');
demo_tab_account();

function idx_rpc(string $rpc, string $metodo, array $par = []) {
    $c = curl_init($rpc);
    curl_setopt_array($c, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => $metodo, 'params' => $par])]);
    $r = curl_exec($c); curl_close($c);
    $d = $r ? json_decode($r, true) : null;
    if (!is_array($d) || isset($d['error'])) throw new RuntimeException('RPC ' . $metodo . ': ' . ($d['error']['message'] ?? 'no answer'));
    return $d['result'];
}
function idx_hex2dec(string $h): string { return function_exists('fir_hex2dec') ? fir_hex2dec($h) : (string)hexdec($h); }
function idx_topic(string $addr): string { return '0x' . str_pad(substr(strtolower($addr), 2), 64, '0', STR_PAD_LEFT); }
function idx_log(string $t): void { led_db()->prepare('INSERT INTO idx_log (quando,testo) VALUES (?,?)')->execute([time(), $t]); }

require_once __DIR__ . '/dr-firma.php';   // fir_hex2dec (gmp) per gli importi a 18 decimali

/* --- gli indirizzi da sorvegliare: dep_addr di ogni account + globale --- */
$MAPPA = [];   // indirizzo lower => uid
foreach ($db->query("SELECT uid, dep_addr FROM demo_account WHERE dep_addr IS NOT NULL AND dep_addr<>''") as $r) $MAPPA[strtolower((string)$r['dep_addr'])] = (string)$r['uid'];
$INDIRIZZI = array_values(array_unique(array_merge(array_keys($MAPPA), [$GLOBALE])));

$esito = ['ok' => true, 'rpc' => preg_replace('#^(https?://[^/]+).*#', '$1', $RPC), 'indirizzi' => count($INDIRIZZI), 'token' => array_column($TOKENS, 0), 'accrediti' => 0, 'non_attribuiti' => 0, 'errori' => []];

try {
    $testa = (int)idx_hex2dec(idx_rpc($RPC, 'eth_blockNumber'));
    $fine = $testa - IDX_CONFERME;
    foreach ($TOKENS as $contratto => [$sim, $dec, $tokInterno]) {
        $q = $db->prepare('SELECT ultimo_blocco FROM idx_stato WHERE contratto=?'); $q->execute([$contratto]);
        $da = (int)($q->fetchColumn() ?: 0);
        if ($da === 0) $da = max(1, $fine - 5000);          // prima corsa: ultime ~3 ore
        $da++;
        $stop = min($fine, $da + IDX_MAX_BLOCCHI);
        for ($b = $da; $b <= $stop; $b += IDX_PASSO) {
            $a = min($stop, $b + IDX_PASSO - 1);
            foreach (array_chunk($INDIRIZZI, 100) as $gruppo) {
                $logs = idx_rpc($RPC, 'eth_getLogs', [[
                    'fromBlock' => '0x' . dechex($b), 'toBlock' => '0x' . dechex($a), 'address' => $contratto,
                    'topics' => [IDX_TRANSFER, null, array_map('idx_topic', $gruppo)],
                ]]);
                foreach ((array)$logs as $L) {
                    $a_addr = '0x' . substr((string)$L['topics'][2], -40);
                    $da_addr = '0x' . substr((string)$L['topics'][1], -40);
                    $imp = idx_hex2dec((string)$L['data']);
                    if (bigi_cmp($imp, '0') <= 0) continue;
                    // porta l'importo ai decimali INTERNI del token
                    $decInt = led_decimali($tokInterno);
                    if ($dec > $decInt) $imp = bigi_div($imp, bigi_pow10($dec - $decInt));
                    elseif ($dec < $decInt) $imp = bigi_mul($imp, bigi_pow10($decInt - $dec));
                    $tx = (string)$L['transactionHash']; $li = (int)idx_hex2dec((string)$L['logIndex']); $blk = (int)idx_hex2dec((string)$L['blockNumber']);
                    $uid = $MAPPA[strtolower($a_addr)] ?? null;
                    if ($uid === null) {
                        $db->prepare('INSERT OR IGNORE INTO idx_non_attribuiti (tx_hash,log_index,contratto,simbolo,importo,da,a,blocco,quando) VALUES (?,?,?,?,?,?,?,?,?)')
                           ->execute([$tx, $li, $contratto, $sim, $imp, $da_addr, $a_addr, $blk, time()]);
                        $esito['non_attribuiti']++;
                        continue;
                    }
                    try {
                        led_deposito($uid, $tokInterno, $imp, ['catena' => 'polygon', 'tx_hash' => $tx, 'log_index' => $li, 'blocco' => $blk, 'stato' => 'confermato'], 'indicizzatore');
                        if ($tokInterno === 'USDT') led_swap($uid, 'USDT', 'DUX', $imp);   // 1:1, subito
                        demo_notifica($uid, 'deposito', 'Deposit received: ' . led_umano($imp, $tokInterno) . ' ' . $sim, ($tokInterno === 'USDT' ? 'Converted 1:1 into DUX. ' : '') . 'Tx ' . substr($tx, 0, 14) . '… on Polygon.');
                        $esito['accrediti']++;
                    } catch (Throwable $e) {
                        // UNIQUE tx_hash+log_index gia' accreditato: e' la protezione anti-doppio, non un errore
                        if (stripos($e->getMessage(), 'unique') === false) { $esito['errori'][] = $tx . ': ' . $e->getMessage(); idx_log('errore ' . $tx . ' ' . $e->getMessage()); }
                    }
                }
            }
            $db->prepare('INSERT OR REPLACE INTO idx_stato (contratto,ultimo_blocco,aggiornato) VALUES (?,?,?)')->execute([$contratto, $a, time()]);
        }
        $esito['blocchi'][$sim] = ['da' => $da, 'a' => $stop, 'testa' => $testa];
    }
} catch (Throwable $e) {
    $esito['ok'] = false; $esito['errori'][] = $e->getMessage(); idx_log('fermo: ' . $e->getMessage());
}
idx_log('corsa: ' . $esito['accrediti'] . ' accrediti, ' . $esito['non_attribuiti'] . ' non attribuiti');
if (PHP_SAPI === 'cli') { echo json_encode($esito, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n"; exit; }
idx_out($esito);
