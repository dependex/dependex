<?php
/**
 * IL CERVELLO — una sola funzione di risposta AI per chat, Eridany e Support.
 * Ordine: 1) chat.php del sito (Cortex RAG + Groq) · 2) Groq diretto con le FAQ · 3) FAQ locali.
 * Ritorna ['testo' => ..., 'fonte' => 'cortex'|'groq'|'faq'|'', 'sicuro' => bool].
 * Nessuna chiave nel file: GROQ_API_KEY arriva dal .env.
 */
declare(strict_types=1);
if (!defined('DR_CORTEX_CHAT')) define('DR_CORTEX_CHAT', 'https://destinorandagio.it/chat.php');
if (!defined('DR_SUPPORT_EMAIL')) define('DR_SUPPORT_EMAIL', demo_env('DR_SUPPORT_EMAIL', 'info@dependex.social'));   // casella umana del Support

function cerv_faq(): array
{
    return [
        ['q' => 'deposit usdt dux polygon address', 'a' => 'Deposit: send USDT on Polygon to the address in Wallet → Deposit. It becomes DUX 1:1 within minutes.'],
        ['q' => 'withdraw withdrawal fee 72 review earned', 'a' => 'Withdrawals leave only from the Withdrawal wallet: fee 0.5%, 72-hour review, then signed offline. Deposited DUX never leave.'],
        ['q' => 'pin forgot reset', 'a' => 'PIN: 6 digits, asked on every money move. 4 wrong attempts clear it; set a new one in Account Settings → Transaction PIN.'],
        ['q' => 'claim rewards offset split', 'a' => 'Claims never ask for the PIN. Tools pay 30% DUX offset · 30% DRX · 30% 81X · 10% ERIDAN; classic memberships 80/20; Prestige 80/15/5.'],
        ['q' => 'transfer send member', 'a' => 'Transfers: DUX, DRX and 81X from your Rewards wallet to another member’s Rewards wallet. Instant, no gas, PIN required.'],
        ['q' => 'membership basic pro elite restart', 'a' => 'Classic memberships: BASIC 500, PRO 1,000, ELITE 2,500 DUX (2/5/8‰ a day, boosters ×2/×3/×5); RESTART 150 DUX paid only with earned DUX.'],
        ['q' => 'prestige wolf nft', 'a' => 'Prestige memberships (Bronze/Silver/Gold/Diamond) need 10+ NFT: 3,000 → 25,000 DUX, 0.2–0.5% a day.'],
        ['q' => 'mining rig eridan', 'a' => 'Mining: rigs from 500 to 50,000 DUX, 365 days, DUX back at the end. Output split into DUX offset, DRX, 81X and ERIDAN.'],
        ['q' => 'vault staking lock', 'a' => 'Vault (12/18/24 months) and Staking (365 days) accept DRX or 81X. Capital back at the end.'],
        ['q' => 'dao vote drx', 'a' => 'DAO: voting needs DRX in your Rewards wallet; the DRX you commit are spent when you vote.'],
        ['q' => 'bind wallet metamask walletconnect', 'a' => 'Bind your external wallet in Account Settings → External wallet (MetaMask or WalletConnect). Withdrawals go there.'],
        ['q' => 'rank prestige score', 'a' => 'Rank (BRANCH OCEAN RANKS: Plankton at start, then Shrimp, Crab, Octopus, Fish, Dolphin, Shark, Whale, Humpback, Leviathan) is earned with turnover (yours + your branch); Prestige (Bronze/Silver/Gold/Diamond) depends on how many NFT you hold. Two scales, never confused.'],
        ['q' => 'what is branch community dao covo academy eridany', 'a' => 'BLOCKCHAINPLUS.DAO (B+) is the Web3 ecosystem you are using. BRANCH is its DAO and community — the real pack, the real community. The Covo is the private area where the pack meets, shares and builds. The Blockchainplus Academy teaches the blockchain; I am Eridany, its digital guide.'],
    ];
}

/* La conoscenza completa della dapp, per la persona AI: perche', per cosa, come, per chi, dove, quando. Mai il sito, mai il vecchio nome. */
function cerv_conoscenza(): string
{
    return <<<K
BLOCKCHAINPLUS.DAO (short form: B+) — "The Web3 Operating System", a Web3 ecosystem. Its DAO and community is called BRANCH ("BRANCH — The real pack. The real community."). The private community area is THE COVO ("Where the pack meets, shares and builds."). The learning space is the BLOCKCHAINPLUS ACADEMY ("Learn the blockchain. Understand what you use."), whose digital guide is Eridany (you) — Eridany is a guide, not a token; ERIDAN is the token. A mobile-first dapp (installable as an app icon on Android and iPhone: Account Settings → Install the app). Colours black/white/gold. Bottom bar: Home · Wallet · Network · Products (centre coin) · DAO · Account · Alerts. Header: live crypto ticker.
WHO: for people who want to join BRANCH — beginners welcome; nothing here requires prior crypto knowledge. Registration: username, email, password, optional referral code; login with username or email; forgot password = create a new one.
TOKENS (internal units of account, 1 DUX = 1 DRX = 1 81X = 1 ERIDAN): DUX (from USDT 1:1, spendable in every tool), DRX (merit: votes, ranks, vault, staking), 81X (utility: staking, gamification unlock 1 81X → 100 DUX offset), ERIDAN (fourth token, mined by rigs; internal quota until its contract is live on Polygon, then on-chain). Nothing here is an investment or a promise of earnings; no token is money.
WALLET (4 wallets, Wallet page): 1 Deposit (what you put in: DUX/DRX/81X/ERIDAN/BTC/USDT — usable in every tool; deposited DUX never leave), 2 Rewards (what tools produce: earned DUX, DRX, 81X, ERIDAN, BTC — the only transferable wallet; DUX can move to Withdrawal, DRX/81X back to Deposit), 3 Withdrawal (DUX ready to leave: fee 0.5%, 72-hour review, then signed offline, tx hash shown; bound external wallet; ERIDAN conversion when live; BTC balance withdrawable to your own bitcoin address when the bridge is live), 4 Offset (vouchers: DUX/DRX/81X, worth up to 10% of any activation, never withdrawable, never transferable). Transfer wallet: send DUX, DRX, 81X from your Rewards to another member's Rewards by username/email/ID, instant, no gas, PIN. Swap inside Deposit: DUX↔DRX 1:1, DRX↔81X 1000:1, fee 0.
BTC: every wallet shows a BTC (bitcoin) line with the official orange ₿ logo — Deposit (deposit and hold), Rewards (bitcoin rewards from missions and events), Withdrawal (to your BTC address). Values in $ use the live BTC price from the ticker. No yield is promised on BTC.
DEPOSIT: Wallet → Deposit shows YOUR personal Polygon address + QR. Send USDT (Polygon network only, never other chains). It becomes DUX 1:1 within minutes (12 confirmations). Only USDT, DUX, DRX, 81X on Polygon.
PIN: 6 digits, set in Account Settings → Transaction PIN. Asked for activations, stakes, swaps, transfers, withdrawals, moves between wallets, wallet binding, unlocks, DAO votes, mints. Never asked for claims and deposits. 4 wrong attempts clear it; then you set a new one (no recovery of the old one).
PRODUCTS (Products page, centre coin): Classic Membership BASIC 500 DUX (0.2%/day, ×2 ceiling), PRO 1,000 (0.5%, ×3), ELITE 2,500 (0.8%, ×5), RESTART 150 DUX (0.4%, ×1.5, paid only with earned DUX). Runs until the ceiling (capital × booster) is reached. Claims pay 80% DUX to Rewards + 20% DUX to Offset. Prestige Membership (needs 10+ NFT): Bronze 3,000 DUX 0.2%/day ×6, Silver 5,000 0.3% ×7, Gold 10,000 0.4% ×8, Diamond 25,000 0.5% ×10; claims pay 80% DUX Rewards + 15% DUX Offset + 5% ERIDAN. Stake NFT: packages of 1/5/10/20 NFT, 365 days. Mining: rigs 500/1,000/5,000/10,000/25,000/50,000 DUX, 365 days, 1,000 DUX = 1 unit a day × rig booster (×1 → ×2), DUX back at the end. Vault: lock DRX or 81X 12/18/24 months (1/2/3‰ a day), capital back. Staking: DRX or 81X 365 days, 1‰ a day, capital back. Every mining, vault, staking (and NFT stake) claim is split 30% DUX → Offset, 30% DRX → Rewards, 30% 81X → Rewards, 10% ERIDAN → Rewards. Quantity is free; parameters freeze at activation; claim any time (single or CLAIM ALL), no PIN. Offset vouchers cover max 10% of an activation; DUX are spent offset → deposited → earned.
RANKS & PRESTIGE — BRANCH OCEAN RANKS ("Grow your impact. Rise through the blue ocean."): everyone starts as Plankton; the rank ladder (9 steps: Shrimp → Crab → Octopus → Fish → Dolphin → Shark → Whale → Humpback → Leviathan (the crypto whale ladder)) is earned with turnover in $ (your activations + your branch's) — never bought. Prestige (Bronze 1 / Silver 5 / Gold 10 / Diamond 20 NFT) depends on NFT held. Two scales, never confused. Boosts: highest wins, they never multiply.
NETWORK: your branch only, level by level; star view 2D/3D, tap a name for the card (username, SIC-ID, rank, turnover, direct, below); referral link + QR in Account Settings.
DAO (BRANCH DAO): proposals (from rank Octopus) and votes; voting needs DRX in Rewards, the DRX you commit are spent (they go to the DAO treasury). Forum (The Covo): topics, chat, agents that answer if nobody does, BRANCH DAO, rules. Missions & gamification, Blockchainplus Academy (courses, timed lessons, quizzes, verifiable certificates), Events (webinar and live evenings), Achievements, Leaderboard (wolf colour = prestige).
NFT: on-chain collections on Polygon (GENESYS 118, THRINWULF 118 clips, PREDA); each has its own MINT button with its DUX price, paid only with Deposit DUX, one-shot, then queued for on-chain signing; Marketplace in DUX with 5% royalty to the treasury.
SECURITY: the site never holds signing keys — withdrawals and mints are queued and signed offline; PIN + password; 2FA and sessions in Account Settings → Security; screenshots and copying are discouraged by the app shield.
SUPPORT: Account Settings → Support: write the problem, the assistant answers at once; if it cannot solve it, or you press "Not solved", it goes by email to a human.
K;
}
function cerv_persona(string $nome = ''): string
{
    return "You are Eridany, the digital guide of the Blockchainplus Academy and the personal assistant inside BLOCKCHAINPLUS.DAO (B+), a Web3 ecosystem whose DAO and community is BRANCH — the real pack. You are a warm, patient, precise human-like guide: kind, encouraging, persuasive without pressure, tenacious in solving the person's problem, never robotic. "
         . "RULES: talk ONLY about BLOCKCHAINPLUS.DAO, BRANCH, The Covo, the Blockchainplus Academy and their features. Always call the platform BLOCKCHAINPLUS.DAO (or B+), never any other or older name (why, what for, how, for whom, where, when). NEVER mention any website, brand, company, artist or project outside the dapp — no exceptions. "
         . "Explain like to someone who knows nothing about crypto, memberships or wallets: clear, complete, step by step, with the exact page/button to press. Never promise earnings, never give financial advice; say plainly that DUX/DRX/81X/ERIDAN are internal units and NFT are pieces. "
         . "LANGUAGE: always answer in the language of the user's LAST message (English → English, French → French, Italian → Italian, and so on). "
         . ($nome !== '' ? "The user's name is {$nome}: use it naturally, so they feel welcome and at home. " : "You do not know the user's name yet: greet warmly, introduce yourself as Eridany, say you are here to help, and ask their name before anything else. ")
         . "If a question is outside the dapp, gently bring it back to what the dapp can do for them. Keep answers focused; use short paragraphs, not walls of text.

KNOWLEDGE OF THE DAPP:
" . cerv_conoscenza();
}
function cerv_rispondi(string $msg, array $hist = [], string $nome = ''): array
{
    $msg = trim(mb_substr($msg, 0, 1500));
    if ($msg === '') return ['testo' => '', 'fonte' => '', 'sicuro' => false];
    /* 1 · Groq con la persona Eridany (solo dapp, mai il sito) */
    $key = demo_env('GROQ_API_KEY', '');
    if ($key !== '') {
        $msgs = [['role' => 'system', 'content' => cerv_persona($nome) . "\n\nIf you cannot solve a support problem with this knowledge, end your answer with the exact tag [ESCALATE]."]];
        foreach (array_slice($hist, -12) as $h) $msgs[] = $h;
        $msgs[] = ['role' => 'user', 'content' => $msg];
        $body = json_encode(['model' => demo_env('GROQ_MODEL', 'llama-3.3-70b-versatile'), 'messages' => $msgs, 'temperature' => 0.4, 'max_tokens' => 700]);
        $c = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($c, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $key]]);
        $r = curl_exec($c); curl_close($c);
        $d = $r ? json_decode($r, true) : null;
        $t = trim((string)($d['choices'][0]['message']['content'] ?? ''));
        if ($t !== '') { $esc = str_contains($t, '[ESCALATE]'); return ['testo' => trim(str_replace('[ESCALATE]', '', $t)), 'fonte' => 'groq', 'sicuro' => !$esc]; }
    }
    /* 2 · FAQ locali (sempre disponibili, sempre solo dapp) */
    $best = null; $bs = 0; $w = preg_split('/\W+/u', mb_strtolower($msg));
    foreach (cerv_faq() as $f) { $s = 0; foreach ($w as $x) if (strlen($x) > 2 && str_contains($f['q'], $x)) $s++; if ($s > $bs) { $bs = $s; $best = $f; } }
    if ($best && $bs >= 2) return ['testo' => ($nome !== '' ? $nome . ', ' : '') . $best['a'], 'fonte' => 'faq', 'sicuro' => true];
    /* (nessun cervello esterno: Eridany parla SOLO della dapp — regola di Mirco) */
    if ($best) return ['testo' => ($nome !== '' ? $nome . ', ' : '') . $best['a'], 'fonte' => 'faq', 'sicuro' => false];
    return ['testo' => '', 'fonte' => '', 'sicuro' => false];
}

/** Manda il ticket alla casella umana. Ritorna true se mail() ha accettato la consegna. */
function cerv_escalation(array $t, string $uid): bool
{
    $a = function_exists('demo_account') ? demo_account($uid) : [];
    $sogg = '[BLOCKCHAINPLUS.DAO support] ticket #' . (int)$t['id'] . ' · ' . (string)$t['tipo'] . ' · ' . $uid;
    $corpo = "Ticket #" . (int)$t['id'] . "\nUser: " . $uid . " (" . (string)($a['nome_utente'] ?? '') . " · " . (string)($a['email'] ?? 'no email') . ")\nType: " . (string)$t['tipo'] . "\nOpened: " . gmdate('Y-m-d H:i', (int)$t['quando']) . " UTC\n\n" . (string)$t['testo'] . "\n\nAI answer given:\n" . (string)($t['risposta'] ?? '—') . "\n\nReply to the user from the Command Center or by email.";
    $hdr = 'From: BLOCKCHAINPLUS.DAO <no-reply@' . preg_replace('/^www\./', '', (string)($_SERVER['HTTP_HOST'] ?? 'blockchainplus.dao')) . ">\r\nContent-Type: text/plain; charset=UTF-8";
    if (!empty($a['email'])) $hdr .= "\r\nReply-To: " . $a['email'];
    $ok = false;
    try { $ok = @mail(DR_SUPPORT_EMAIL, $sogg, $corpo, $hdr); } catch (Throwable $e) { $ok = false; }
    if (function_exists('led_db')) led_db()->prepare('INSERT INTO idx_log (quando,testo) VALUES (?,?)')->execute([time(), 'support escalation #' . (int)$t['id'] . ' → ' . DR_SUPPORT_EMAIL . ' ' . ($ok ? 'sent' : 'mail() failed')]);
    return $ok;
}
