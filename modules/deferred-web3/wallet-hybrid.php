<?php
/* ============================================================================
   GENESYS/WALLET-HYBRID — SISTEMA WALLET IBRIDO (libreria)
   Destino Randagio · 2026-08-01 · destinazione: genesys/wallet-hybrid.php

   IL DISEGNO (flusso di Mirco, implementato fedelmente):
   1. Alla registrazione ogni utente riceve un wallet Polygon on-chain VERO:
      seed phrase BIP39 di 12 parole (wordlist ufficiale 2048, entropy 128 bit
      da random_bytes, checksum SHA-256), derivazione BIP32/BIP44 sul path di
      MetaMask m/44'/60'/0'/0/0, secp256k1 reale, indirizzo EIP-55.
   2. La seed viene CIFRATA (AES-256-GCM, chiave da .env) e salvata in DB.
      Mai in chiaro, mai in dashboard. Va all'utente via email UNA volta.
   3. Il wallet "mirror" NON e' un secondo ledger: i saldi interni DRX/81X/
      USDT restano quelli dei motori esistenti (drx_balance, x81_balance,
      dr_wbal). La pagina wallet-branco.php li LEGGE e basta.
   4. L'utente puo' importare il wallet in MetaMask con la seed ricevuta.

   SICUREZZA — SCELTE NON NEGOZIABILI:
   - AES-256-GCM (autenticato: un ciphertext manomesso NON decifra, il tag
     salta), IV random 12 byte per ogni cifratura, AAD legata a utente+campo
     (un ciphertext copiato su un'altra riga non decifra).
   - La chiave viene da .env WALLET_ENCRYPTION_KEY (pattern dr_env). Se manca
     o e' corta, il sistema si RIFIUTA di creare wallet: MAI una chiave di
     default nel codice.
   - wh_decrypt e' SOLO server-side: nessun endpoint la espone.

   ONESTA' SUL MODELLO: e' un wallet CUSTODIAL — il server custodisce le seed
   cifrate. Backup regolari e cifrati del DB sono FONDAMENTALI: perdere il DB
   (o la WALLET_ENCRYPTION_KEY) = perdere l'accesso server-side ai wallet.
   L'email con la seed e' il punto piu' debole del design: viaggia e resta
   nella casella dell'utente. Il template lo dice chiaro e chiede di
   trascriverla su carta e cancellare l'email.

   USO
     require_once __DIR__.'/wallet-hybrid.php';   // da dentro genesys/
     wh_tables($pdo);
     $r = wh_create_wallet_for_user($pdo, $uid);  // ['created','address','mnemonic']
     if ($r['created'] && $email) wh_send_seed_email($pdo,$uid,$email,$r['address'],$r['mnemonic']);
     unset($r); // il chiamante SCARTA il mnemonic appena inviato
============================================================================ */

if (defined('DR_WALLET_HYBRID')) return;
define('DR_WALLET_HYBRID', 1);

require_once dirname(__DIR__) . '/dr-env.php';

/* walletlib vendorizzata: prima si cerca a ROOT del sito, poi dentro genesys/ */
$__wl = is_file(dirname(__DIR__) . '/walletlib/autoload.php')
      ? dirname(__DIR__) . '/walletlib/autoload.php'
      : __DIR__ . '/walletlib/autoload.php';
require_once $__wl;

@require_once dirname(__DIR__) . '/dr-log.php';   // dr_log: si logga TUTTO

/* ------------------------------------------------------------------ tabelle */
function wh_tables(PDO $pdo): void {
  /* UN wallet per utente: il vincolo lo fa il database, non un if. */
  $pdo->exec("CREATE TABLE IF NOT EXISTS wallet_onchain(
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL UNIQUE,
    address         TEXT NOT NULL,
    enc_mnemonic    TEXT NOT NULL,      -- seed BIP39 cifrata AES-256-GCM (mai in chiaro)
    enc_privkey     TEXT NOT NULL,      -- chiave privata derivata, cifrata (stesso schema)
    derivation_path TEXT NOT NULL DEFAULT 'm/44''/60''/0''/0/0',
    seed_email_sent INTEGER NOT NULL DEFAULT 0,
    created         TEXT DEFAULT (datetime('now')))");
  $pdo->exec("CREATE INDEX IF NOT EXISTS ix_wo_addr ON wallet_onchain(address)");

  /* UN funding per ordine: idempotenza garantita da UNIQUE(order_id). */
  $pdo->exec("CREATE TABLE IF NOT EXISTS wallet_fundings(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id   TEXT NOT NULL UNIQUE,
    uid        INTEGER NOT NULL,
    address    TEXT NOT NULL,
    amount_pol REAL NOT NULL,
    tx_hash    TEXT,
    status     TEXT NOT NULL DEFAULT 'created',  -- created|pending|confirmed|failed
    err        TEXT,
    tentativi  INTEGER NOT NULL DEFAULT 0,
    created    TEXT DEFAULT (datetime('now')),
    updated    TEXT DEFAULT (datetime('now')))");
  $pdo->exec("CREATE INDEX IF NOT EXISTS ix_wf_uid ON wallet_fundings(uid)");
  $pdo->exec("CREATE INDEX IF NOT EXISTS ix_wf_st  ON wallet_fundings(status)");
  /* I log degli eventi wallet vanno in dr_events via dr_log: NESSUNA tabella
     di log nuova, si riusa il watcher globale. */
}

/* ------------------------------------------------------- cifratura AES-GCM */
/** Chiave di cifratura da .env. Se manca/corta: ECCEZIONE (mai fallback). */
function wh_enc_key(): string {
  $raw = trim((string)dr_env('WALLET_ENCRYPTION_KEY', ''));
  if ($raw === '' || strlen($raw) < 32) {
    throw new RuntimeException(
      'WALLET_ENCRYPTION_KEY assente o troppo corta nel .env: creazione wallet RIFIUTATA. ' .
      'Generane una con: openssl rand -hex 32');
  }
  /* KDF deterministico: qualunque formato (hex/base64/frase >=32 char) diventa
     32 byte esatti. Nessuna chiave hardcoded, nessun default. */
  return hash('sha256', 'dr-wallet-v1|' . $raw, true);
}

/** true se la chiave e' configurata (per rifiutare PRIMA di generare seed). */
function wh_enc_ready(): bool {
  try { wh_enc_key(); return true; } catch (Throwable $e) { return false; }
}

/** Cifra AES-256-GCM. $aad lega il blob a contesto (uid+campo). */
function wh_encrypt(string $plain, string $aad = ''): string {
  $key = wh_enc_key();
  $iv  = random_bytes(12);
  $tag = '';
  $ct  = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, $aad, 16);
  if ($ct === false) throw new RuntimeException('wh_encrypt: cifratura fallita');
  return 'gcm1:' . base64_encode($iv) . ':' . base64_encode($tag) . ':' . base64_encode($ct);
}

/** Decifra — SOLO SERVER-SIDE. Tag GCM invalido (tamper) => eccezione. */
function wh_decrypt(string $blob, string $aad = ''): string {
  $p = explode(':', $blob);
  if (count($p) !== 4 || $p[0] !== 'gcm1') throw new RuntimeException('wh_decrypt: formato sconosciuto');
  $iv = base64_decode($p[1], true); $tag = base64_decode($p[2], true); $ct = base64_decode($p[3], true);
  if ($iv === false || $tag === false || $ct === false) throw new RuntimeException('wh_decrypt: base64 corrotto');
  $pt = openssl_decrypt($ct, 'aes-256-gcm', wh_enc_key(), OPENSSL_RAW_DATA, $iv, $tag, $aad);
  if ($pt === false) throw new RuntimeException('wh_decrypt: autenticazione GCM fallita (dato manomesso o chiave errata)');
  return $pt;
}

/* --------------------------------------------------------- creazione wallet */
/**
 * Genera il wallet on-chain per l'utente. Ritorna:
 *   ['ok'=>true,'created'=>true,'address'=>...,'mnemonic'=>...]  (nuovo: il
 *     chiamante manda l'email e POI scarta la variabile)
 *   ['ok'=>true,'created'=>false,'address'=>...]                 (gia' esisteva)
 * Lancia eccezione se WALLET_ENCRYPTION_KEY manca (mai wallet non cifrabili).
 */
function wh_create_wallet_for_user(PDO $pdo, int $uid): array {
  if ($uid <= 0) throw new InvalidArgumentException('wh_create_wallet_for_user: uid non valido');
  wh_tables($pdo);

  /* Rifiuto PRIMA di generare qualsiasi segreto se la chiave .env manca. */
  wh_enc_key();

  $st = $pdo->prepare("SELECT address FROM wallet_onchain WHERE user_id=?");
  $st->execute([$uid]);
  if ($addr = $st->fetchColumn()) return ['ok'=>true, 'created'=>false, 'address'=>$addr];

  $u = $pdo->prepare("SELECT id FROM users WHERE id=?"); $u->execute([$uid]);
  if (!$u->fetchColumn()) throw new RuntimeException('wh_create_wallet_for_user: utente inesistente');

  $path     = "m/44'/60'/0'/0/0";
  $mnemonic = \DrWallet\Bip39::generate(128);            // 12 parole, wordlist ufficiale
  $wallet   = \DrWallet\Eth::walletFromMnemonic($mnemonic, $path);
  $address  = $wallet['address'];

  try {
    $pdo->prepare("INSERT INTO wallet_onchain(user_id,address,enc_mnemonic,enc_privkey,derivation_path)
                   VALUES(?,?,?,?,?)")
        ->execute([$uid, $address,
                   wh_encrypt($mnemonic,       'dr-wallet|uid:'.$uid.'|mnemonic'),
                   wh_encrypt($wallet['priv'], 'dr-wallet|uid:'.$uid.'|privkey'),
                   $path]);
  } catch (PDOException $e) {
    /* corsa: due richieste insieme — vince chi ha scritto, l'altro rilegge */
    if (strpos($e->getMessage(), 'UNIQUE') === false) throw $e;
    $st->execute([$uid]);
    return ['ok'=>true, 'created'=>false, 'address'=>(string)$st->fetchColumn()];
  } finally {
    /* la chiave privata in chiaro non serve piu' a nessuno */
    $wallet['priv'] = str_repeat('0', 64); unset($wallet);
  }

  if (function_exists('dr_log')) {
    dr_log($pdo, 'wallet', 'crea', ['address'=>$address, 'path'=>$path], $uid);
  }
  return ['ok'=>true, 'created'=>true, 'address'=>$address, 'mnemonic'=>$mnemonic];
}

/** Il wallet dell'utente (SENZA campi cifrati: la seed non esce mai da qui). */
function wh_get_wallet(PDO $pdo, int $uid): ?array {
  wh_tables($pdo);
  $st = $pdo->prepare("SELECT user_id,address,derivation_path,seed_email_sent,created
                       FROM wallet_onchain WHERE user_id=?");
  $st->execute([$uid]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  return $r ?: null;
}

/* ------------------------------------------------------------- email seed  */
/**
 * Email TRANSAZIONALE con la seed: parte SUBITO via mail(), NON passa dal
 * gate marketing (mkt_armato): un interruttore marketing spento non puo'
 * lasciare un Pioniere senza la sua seed. Registrata in emails_log + dr_log
 * (MAI la seed nei log: solo l'esito).
 */
function wh_send_seed_email(PDO $pdo, int $uid, string $email, string $address, string $mnemonic): bool {
  $email = trim($email);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

  $parole = preg_split('/\s+/', trim($mnemonic));
  $celle = '';
  foreach ($parole as $i => $p) {
    $celle .= '<td style="padding:9px 12px;border:1px solid #3a3125;background:#151210;border-radius:8px;color:#EDE3CE;font-family:Consolas,Menlo,monospace;font-size:15px;white-space:nowrap">'
            . '<span style="color:#8a7a55;font-size:11px">' . ($i+1) . '.</span> '
            . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '</td>';
    if ($i % 3 === 2 && $i < 11) $celle .= '</tr><tr>';
  }

  $oggetto = 'Il tuo Wallet del Branco — le 12 parole (conservale ORA)';
  $addrSafe = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
  $anno = date('Y');
  $html = <<<HTML
<!doctype html><html lang="it"><body style="margin:0;padding:0;background:#0D0B0A">
<div style="max-width:640px;margin:0 auto;padding:28px 18px;font-family:Georgia,'Times New Roman',serif;color:#EDE3CE">
<p style="text-align:center;margin:0 0 6px"><img src="https://destinorandagio.it/assets/LOGO%20DR%20Corona%20ok.png" alt="Destino Randagio" width="64" style="opacity:.95"></p>
<h1 style="color:#D4AF37;font-size:22px;text-align:center;font-weight:600;letter-spacing:.06em;margin:8px 0 4px">IL TUO WALLET DEL BRANCO</h1>
<p style="text-align:center;color:#bfae8c;font-size:13px;margin:0 0 22px">Polygon &middot; creato alla tua registrazione &middot; custodiscilo come una chiave di casa</p>
<div style="border:1px solid #D4AF37;border-radius:14px;padding:18px 20px;background:linear-gradient(180deg,#171310,#100D0B)">
  <p style="margin:0 0 8px;color:#bfae8c;font-size:12px;letter-spacing:.08em">IL TUO INDIRIZZO PUBBLICO</p>
  <p style="margin:0;font-family:Consolas,Menlo,monospace;font-size:14px;color:#EDE3CE;word-break:break-all">{$addrSafe}</p>
</div>
<div style="border:1px solid #6b5a33;border-radius:14px;padding:18px 20px;margin-top:14px;background:#0f0d0b">
  <p style="margin:0 0 12px;color:#D4AF37;font-size:13px;letter-spacing:.08em">LE TUE 12 PAROLE SEGRETE (seed phrase)</p>
  <table role="presentation" cellspacing="6" cellpadding="0" style="border-collapse:separate;margin:0 auto"><tr>{$celle}</tr></table>
</div>
<div style="border:1px solid #7a2e2e;border-radius:14px;padding:16px 20px;margin-top:14px;background:#140d0d">
  <p style="margin:0 0 8px;color:#e08585;font-size:14px;font-weight:bold">&#9888;&#65039; LEGGI PRIMA DI FARE QUALSIASI COSA</p>
  <ul style="margin:0;padding-left:18px;color:#d8c9ab;font-size:13px;line-height:1.7">
    <li><b>MAI condividere</b> queste 12 parole. Con nessuno. Nemmeno con noi: <b>Destino Randagio non te le chieder&agrave; MAI</b> &mdash; chi lo fa &egrave; un truffatore.</li>
    <li><b>Scrivile su carta, adesso</b>, nell&#39;ordine esatto, e mettile in un posto sicuro. La carta non si hackera.</li>
    <li><b>Cancella questa email</b> (anche dal cestino) appena le hai trascritte: una casella email si pu&ograve; violare, e chi ha le parole ha il wallet.</li>
    <li>Questa &egrave; l&#39;<b>UNICA volta</b> che te le inviamo. Non compaiono nella dashboard e nessuno del Branco pu&ograve; rileggertele.</li>
  </ul>
</div>
<div style="border:1px solid #3a3125;border-radius:14px;padding:16px 20px;margin-top:14px;background:#0f0d0b">
  <p style="margin:0 0 8px;color:#D4AF37;font-size:13px;letter-spacing:.08em">IMPORTARLO IN METAMASK (quando vuoi)</p>
  <ol style="margin:0;padding-left:18px;color:#d8c9ab;font-size:13px;line-height:1.7">
    <li>Installa MetaMask (metamask.io) e scegli <i>Importa un wallet esistente</i>.</li>
    <li>Inserisci le 12 parole nell&#39;ordine esatto.</li>
    <li>Aggiungi la rete Polygon: vedrai lo stesso indirizzo qui sopra.</li>
  </ol>
</div>
<p style="text-align:center;color:#8a7a55;font-size:12px;margin:22px 0 0">Un lupo solo sopravvive. Un Branco prospera.<br>&copy; {$anno} Destino Randagio</p>
</div></body></html>
HTML;

  $headers = "MIME-Version: 1.0\r\n"
           . "Content-Type: text/html; charset=UTF-8\r\n"
           . "From: DEPENDEX <info@dependex.social>\r\n"
           . "Reply-To: info@dependex.social\r\n";
  $ok = @mail($email, $oggetto, $html, $headers);

  try {
    $pdo->prepare("INSERT INTO emails_log(kind,dest,subject,status) VALUES('wallet-seed',?,?,?)")
        ->execute([$email, $oggetto, $ok ? 'sent' : 'error']);
    if ($ok) $pdo->prepare("UPDATE wallet_onchain SET seed_email_sent=1 WHERE user_id=?")->execute([$uid]);
  } catch (Throwable $e) {}
  if (function_exists('dr_log')) {
    dr_log($pdo, 'wallet', 'seed-email', ['esito'=>$ok?'inviata':'fallita'], $uid); // MAI la seed nel log
  }
  return (bool)$ok;
}

/* ------------------------------------------------------------ RPC Polygon  */
/** Lista RPC con fallback (ordine richiesto da Mirco + riserva). */
function wh_rpc_endpoints(): array {
  $extra = trim((string)dr_env('POLYGON_RPC_EXTRA', ''));
  $list = [
    'https://polygon-bor-rpc.publicnode.com',
    'https://1rpc.io/matic',
    'https://polygon.drpc.org',
    'https://polygon-rpc.com',
  ];
  if ($extra !== '') array_unshift($list, $extra);
  return $list;
}

/** JSON-RPC con fallback tra i nodi. Ritorna result o null. $err raccoglie l'ultimo errore. */
function wh_rpc(string $method, array $params, ?string &$err = null) {
  /* Hook SOLO per i test automatici (mai impostato in produzione). */
  if (isset($GLOBALS['WH_RPC_OVERRIDE']) && is_callable($GLOBALS['WH_RPC_OVERRIDE'])) {
    return call_user_func($GLOBALS['WH_RPC_OVERRIDE'], $method, $params, $err);
  }
  foreach (wh_rpc_endpoints() as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 12,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_POSTFIELDS => json_encode(['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>$params]),
    ]);
    $r = curl_exec($ch); curl_close($ch);
    if (!$r) continue;
    $j = json_decode($r, true);
    if (isset($j['result'])) return $j['result'];
    if (isset($j['error']['message'])) $err = $j['error']['message'];
  }
  return null;
}

/** hex "0x..." -> stringa decimale (GMP: niente overflow float sui wei). */
function wh_hexdec_str(string $hex): string {
  $hex = preg_replace('/^0x/i', '', trim($hex));
  if ($hex === '' ) return '0';
  return gmp_strval(gmp_init($hex, 16), 10);
}

/** wei (stringa decimale) -> POL con 6 decimali leggibili. */
function wh_wei_to_pol(string $wei): string {
  $q = gmp_div_qr(gmp_init($wei, 10), gmp_init('1000000000000000000', 10));
  $int = gmp_strval($q[0]);
  $fr  = str_pad(gmp_strval($q[1]), 18, '0', STR_PAD_LEFT);
  return rtrim(rtrim($int . '.' . substr($fr, 0, 6), '0'), '.') ?: '0';
}

/**
 * Saldo POL on-chain di un indirizzo, con cache breve su file (60s):
 * la dashboard non deve martellare gli RPC pubblici a ogni refresh.
 * Ritorna ['pol'=>'1.05','wei'=>'…','cached'=>bool] o null se RPC giu'.
 */
function wh_pol_balance(string $address, int $ttl = 60): ?array {
  if (!\DrWallet\Eth::isAddress($address)) return null;
  $dataDir = getenv('DR_DATA_DIR') ?: (dirname(__DIR__) . '/data');
  $cf = $dataDir . '/.wh_bal_' . substr(hash('sha256', strtolower($address)), 0, 16) . '.json';
  if (is_file($cf) && (time() - (int)filemtime($cf)) < $ttl) {
    $c = json_decode((string)@file_get_contents($cf), true);
    if (is_array($c) && isset($c['wei'])) return ['pol'=>$c['pol'], 'wei'=>$c['wei'], 'cached'=>true];
  }
  $hex = wh_rpc('eth_getBalance', [$address, 'latest']);
  if (!is_string($hex)) return null;
  $wei = wh_hexdec_str($hex);
  $out = ['pol'=>wh_wei_to_pol($wei), 'wei'=>$wei];
  @file_put_contents($cf, json_encode($out));
  $out['cached'] = false;
  return $out;
}
