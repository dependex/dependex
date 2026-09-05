<?php
/**
 * CHAT AI — l'assistente della dapp.
 *  · chiave GROQ_API_KEY nel .env (mai in pagina)
 *  · conoscenza: FAQ incorporate + (se c'e') il core neurale sul sito host,
 *    letto da DR_CORTEX_URL con cache 10 minuti
 *  · memoria: gli ultimi 12 scambi in sessione
 *  · se la chiave manca, risponde comunque dalle FAQ (ricerca per parole)
 */
declare(strict_types=1);
require_once __DIR__ . '/_nucleo.php';
demo_esigi();
header('Content-Type: application/json; charset=utf-8');
$in = json_decode((string)file_get_contents('php://input'), true) ?: [];
if (empty($in['csrf']) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$in['csrf'])) { echo json_encode(['errore' => 'Session expired.']); exit; }
$msg = trim(mb_substr((string)($in['msg'] ?? ''), 0, 1500));
if ($msg === '') { echo json_encode(['errore' => 'Empty message.']); exit; }

function chat_faq(): array
{
    return [
        ['q' => 'deposit usdt dux rate convert', 'a' => 'Deposit USDT on Polygon to your deposit address (Wallet → Deposit). It is converted 1:1 into DUX. Polygon network only.'],
        ['q' => 'membership tier basic pro elite activate how many', 'a' => 'Products → Membership: BASIC 500, PRO 1,000, ELITE 2,500 DUX. Pick tier and quantity, any mix. Each one produces DUX daily at the rate frozen on activation; the bar shows how much of its ceiling it has released.'],
        ['q' => 'claim pin', 'a' => 'Claims never ask for the PIN. Claim one membership or press CLAIM ALL. Claimed DUX are EARNED (green): reusable for memberships and withdrawable after a 72-hour review. Deposited DUX are never withdrawable.'],
        ['q' => 'withdraw fee address', 'a' => 'Wallet → Withdraw: only earned DUX (green), to any Polygon address, fee 0.5%, pending 72 hours for automatic and manual review. It asks your PIN, goes to the signing queue and you get the tx hash when executed. Withdrawn DUX has no liquidity pool outside: hold it or deposit it back.'],
        ['q' => 'pin forgot reset wrong', 'a' => 'Account → Transaction PIN: create a new one (only new + repeat, no old PIN). Four wrong attempts clear the PIN and send you there.'],
        ['q' => 'transfer send user internal gas', 'a' => 'Wallet → Transfer: recipient by username, email or ID. Instant, no gas, asks your PIN.'],
        ['q' => 'swap drx 81x rate', 'a' => 'Swap is internal, fee 0: DUX↔DRX 1:1, DRX↔81X 1000:1. It asks your PIN.'],
        ['q' => 'restricted offset dux gamification', 'a' => 'Offset DUX come from gamification (1 81X → 100 offset DUX) and from 20% of every claim. They work as a voucher, up to 10% of each activation, and never leave the dapp.'],
        ['q' => 'stake nft drx', 'a' => 'Products → Stake NFT: packages of 1/5/10/20 NFT, any quantity, produce DRX daily. DRX are not withdrawable: utility, ranks and DAO voting power.'],
        ['q' => 'rank turnover prestige', 'a' => 'Ranks (Plankton → Leviathan) grow with your turnover in $: your own activations plus your network. Prestige (Bronze/Silver/Gold/Diamond) depends on how many NFT you hold. They never mix.'],
        ['q' => 'bind wallet metamask walletconnect', 'a' => 'Wallet → Bind: sign one message with MetaMask or WalletConnect, the server verifies it, and the address is linked to your account. No gas, no transaction.'],
        ['q' => 'password change forgot login email', 'a' => 'You sign in with username or email + password. Forgot it? On the login page use "Forgot password" — you create a new one, the old one is never recovered. Change it any time in Account.'],
        ['q' => 'notifications', 'a' => 'The bell shows every event: deposits, claims, transfers, withdrawals (pending → executed with hash), security. Mark read, dismiss, trash.'],
    ];
}
function chat_cortex(): string
{
    $u = demo_env('DR_CORTEX_URL', '');
    if ($u === '') return '';
    $f = __DIR__ . '/dati/cache/cortex.txt'; @mkdir(dirname($f), 0700, true);
    if (is_file($f) && time() - filemtime($f) < 600) return (string)file_get_contents($f);
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'header' => 'X-DR-Key: ' . demo_env('DR_CORTEX_KEY', '')]]);
    $t = @file_get_contents($u, false, $ctx);
    if ($t === false) return is_file($f) ? (string)file_get_contents($f) : '';
    $t = mb_substr(strip_tags($t), 0, 12000);
    file_put_contents($f, $t, LOCK_EX);
    return $t;
}

$_SESSION['chat'] ??= [];
$_SESSION['chat'][] = ['r' => 'user', 'c' => $msg];
$_SESSION['chat'] = array_slice($_SESSION['chat'], -12);

/* Un solo cervello per chat, Eridany e Support: _cervello.php (persona Eridany, solo dapp, mai il sito). */
require_once __DIR__ . '/_cervello.php';
$hist = [];
foreach (array_slice($_SESSION['chat'], 0, -1) as $m) $hist[] = ['role' => $m['r'] === 'user' ? 'user' : 'assistant', 'content' => $m['c']];
/* onboarding: la prima cosa che Eridany chiede e' il nome; la prima risposta breve dell'utente E' il nome */
$nome = (string)($_SESSION['chat_nome'] ?? '');
if ($nome === '' && count($hist) <= 1 && mb_strlen($msg) <= 40 && preg_match('/^[\p{L}\'\-\. ]{2,40}$/u', $msg) && str_word_count($msg) <= 4 && !preg_match('/\b(how|what|why|where|when|deposit|wallet|pin|help|come|cosa|perch|dove|quando|aiuto|comment|pourquoi|que|wie|was|warum)\b/iu', $msg)) {
    $nome = trim(preg_replace('/^(i am|i\'m|my name is|mi chiamo|sono|je suis|je m\'appelle|ich bin|me llamo|soy)\s+/iu', '', $msg));
    $_SESSION['chat_nome'] = mb_convert_case(mb_substr($nome, 0, 30), MB_CASE_TITLE, 'UTF-8');
    $nome = $_SESSION['chat_nome'];
    $ai = cerv_rispondi('My name is ' . $nome . '. Greet me by name in the same language I used ("' . $msg . '"), tell me in two lines what you can help me with inside the dapp, and ask what I would like to do first.', $hist, $nome);
    $risposta = $ai['testo'] !== '' ? $ai['testo'] : 'Nice to meet you, ' . $nome . '! I can guide you through deposits, wallets, memberships, mining, vault, staking, the DAO and your account. What would you like to do first?';
} else {
    $ai = cerv_rispondi($msg, $hist, $nome);
    $risposta = $ai['testo'] !== '' ? $ai['testo'] : (($nome ? $nome . ', ' : '') . 'I can help with deposits, memberships, claims, withdrawals, PIN, transfers, swaps, staking, ranks and wallet binding. Ask me about one of these — or open Support in Account Settings and I will pass it to a human.');
}
$_SESSION['chat'][] = ['r' => 'assistant', 'c' => $risposta];
echo json_encode(['risposta' => $risposta]);
