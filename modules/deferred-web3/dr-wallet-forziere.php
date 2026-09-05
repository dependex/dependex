<?php
declare(strict_types=1);
/* ============================================================================
   IL FORZIERE — wallet custodial che il server non sa aprire.
   Destino Randagio · 2026-08-15 · Cowork

   IL PROBLEMA VERO, DETTO SENZA GIRI
   Oggi la seed e' cifrata con AES-256-GCM e una chiave nel .env. E' fatto
   bene. Ma ha un limite che nessuna cifratura simmetrica puo' togliere:
   **il server puo' aprirla**. Quindi chi entra nel server mentre gira ha il
   database E la chiave, e le apre tutte. Aggiungere cifratura sopra non
   cambia niente: e' sempre la stessa serratura con la stessa chiave nella
   stessa stanza.

   LA DIFFERENZA VERA: SCRIVERE SI', LEGGERE NO
   Qui si usa cifratura ASIMMETRICA. Il sito ha la chiave PUBBLICA e con
   quella puo' solo CHIUDERE. La chiave privata sta sul PC di Mirco, dentro
   C:\dependex_secret\, e non tocca mai il server.

   Risultato, e questa e' la frase che conta:
   **chi ruba il server si porta a casa dei lucchetti chiusi.**
   Non un database di seed cifrate con la chiave accanto: buste che senza il
   PC non si aprono. E' la stessa regola del resto del progetto — il sito
   accoda, il PC firma — applicata alla cosa piu' delicata che avete.

   COME E' FATTA UNA BUSTA (cifratura a busta, la stessa di PGP e di AWS KMS)
     1. per ogni wallet si tira una chiave a caso, usata una volta sola
     2. la seed si cifra con quella, in AES-256-GCM (veloce, autenticato)
     3. quella chiave si cifra con la chiave PUBBLICA (RSA-4096 OAEP/SHA-256)
     4. sul server restano: busta + chiave-di-sessione-cifrata. Nient'altro.
   Per aprire serve la chiave privata: solo sul PC, solo quando serve.

   COSA NON RISOLVE, e va detto invece di far finta:
   - se qualcuno entra nel server nel MOMENTO in cui l'utente crea il wallet,
     la seed passa in memoria in chiaro per un istante e la puo' prendere.
     Nessun sistema custodial evita questo: si riduce la finestra, non si
     chiude.
   - non e' un audit e non e' una certificazione. E' una scelta di
     architettura che sposta il rischio da "tutte le seed" a "le seed create
     mentre eri dentro".
   - non protegge da un amministratore che ha il PC.

   COSA SI GUADAGNA, in una riga:
   prima un furto del server = tutte le seed. Adesso = nessuna.
============================================================================ */

if (!function_exists('forz_pronto')) {

/* ------------------------------------------------------------ la chiave
   Sul server sta SOLO la pubblica. Se manca, il forziere si rifiuta di
   creare wallet: meglio nessun wallet che un wallet che non sai proteggere. */
function forz_pubblica(): string {
  $p = function_exists('dr_env') ? (string)dr_env('DR_FORZIERE_PUB', '') : '';
  if ($p === '') return '';
  /* si accetta sia il PEM intero sia il PEM in base64 (piu' comodo nel .env) */
  if (strpos($p, '-----BEGIN') === false) {
    $d = base64_decode($p, true);
    if ($d !== false && strpos($d, '-----BEGIN') !== false) $p = $d;
  }
  return $p;
}
function forz_pronto(): bool {
  $p = forz_pubblica();
  if ($p === '') return false;
  $k = @openssl_pkey_get_public($p);
  return $k !== false;
}

function forz_migra(PDO $pdo): void {
  static $f = false; if ($f) return; $f = true;
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS wallet_forziere (
      uid INTEGER PRIMARY KEY,
      indirizzo TEXT DEFAULT '',
      busta TEXT NOT NULL,          -- la seed cifrata (base64)
      chiave TEXT NOT NULL,         -- la chiave di sessione, cifrata con la pubblica
      iv TEXT NOT NULL, tag TEXT NOT NULL,
      impronta TEXT DEFAULT '',     -- quale chiave pubblica ha chiuso questa busta
      algo TEXT DEFAULT 'RSA-OAEP-SHA256+AES-256-GCM',
      creato TEXT DEFAULT (datetime('now')),
      aperto_il TEXT DEFAULT '',    -- quando e' stata aperta sul PC (per il registro)
      note TEXT DEFAULT ''
    )");
    /* LA CODA — i wallet si creano quando arriva la persona, non prima.
       Cinque milioni di wallet per cinque milioni di posizioni vuote sarebbero
       cinque milioni di bersagli per nessuno. */
    $pdo->exec("CREATE TABLE IF NOT EXISTS wallet_coda (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      uid INTEGER NOT NULL, posto INTEGER DEFAULT 0,
      stato TEXT DEFAULT 'in_attesa',   -- in_attesa | fatto | errore
      tentativi INTEGER DEFAULT 0, errore TEXT DEFAULT '',
      creato TEXT DEFAULT (datetime('now')), fatto_il TEXT DEFAULT ''
    )");
    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS ix_wc_uid ON wallet_coda(uid)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS wallet_forziere_log (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ts TEXT DEFAULT (datetime('now')), uid INTEGER DEFAULT 0,
      azione TEXT DEFAULT '', esito TEXT DEFAULT '', chi TEXT DEFAULT ''
    )");
  } catch (Throwable $e) {}
}

function forz_log(PDO $pdo, int $uid, string $az, string $esito, string $chi = 'sistema'): void {
  try {
    $pdo->prepare("INSERT INTO wallet_forziere_log(uid,azione,esito,chi) VALUES(?,?,?,?)")
        ->execute([$uid, $az, $esito, $chi]);
  } catch (Throwable $e) {}
}

/* l'impronta della chiave pubblica: serve a sapere QUALE chiave ha chiuso una
   busta, quando un domani se ne cambia una. Senza, alla rotazione non sapresti
   piu' quale privata apre cosa. */
function forz_impronta(): string {
  $p = forz_pubblica();
  return $p === '' ? '' : substr(hash('sha256', $p), 0, 16);
}

/* ============================================================================
   CHIUDI — il server sa fare solo questo.
   Non esiste in questo file una funzione che apra una busta. Non e' una
   dimenticanza: e' il punto.
============================================================================ */
function forz_chiudi(PDO $pdo, int $uid, string $seed, string $indirizzo = ''): array {
  if (!forz_pronto()) {
    forz_log($pdo, $uid, 'chiudi', 'senza-chiave');
    return ['ok'=>false, 'err'=>'DR_FORZIERE_PUB assente o non valida: il forziere non crea wallet senza chiave pubblica'];
  }
  if ($seed === '') return ['ok'=>false, 'err'=>'seed vuota'];
  forz_migra($pdo);

  /* 1. una chiave usata una volta sola, per questa busta e basta */
  $ks = random_bytes(32);
  $iv = random_bytes(12);
  $tag = '';

  /* 2. la seed dentro la busta. L'AAD lega la busta a QUESTO utente: se
        qualcuno copia la riga su un altro uid, la busta non si apre piu'. */
  $aad = 'dr-forziere|uid:' . $uid . '|' . forz_impronta();
  $cifrata = openssl_encrypt($seed, 'aes-256-gcm', $ks, OPENSSL_RAW_DATA, $iv, $tag, $aad, 16);
  if ($cifrata === false) {
    forz_log($pdo, $uid, 'chiudi', 'errore-aes');
    return ['ok'=>false, 'err'=>'cifratura fallita'];
  }

  /* 3. la chiave della busta si chiude con la PUBBLICA. Da qui in poi solo la
        privata — che sta sul PC — la puo' riaprire. */
  $ksCifrata = '';
  $pub = @openssl_pkey_get_public(forz_pubblica());
  if ($pub === false || !openssl_public_encrypt($ks, $ksCifrata, $pub, OPENSSL_PKCS1_OAEP_PADDING)) {
    forz_log($pdo, $uid, 'chiudi', 'errore-rsa');
    return ['ok'=>false, 'err'=>'chiusura con la chiave pubblica fallita'];
  }

  /* 4. si cancella la chiave dalla memoria appena possibile. Non e' una
        garanzia (PHP puo' averne fatto copie), ma e' quello che si puo' fare. */
  try {
    $pdo->prepare("INSERT OR REPLACE INTO wallet_forziere
      (uid,indirizzo,busta,chiave,iv,tag,impronta,creato)
      VALUES(?,?,?,?,?,?,?,datetime('now'))")
      ->execute([$uid, $indirizzo, base64_encode($cifrata), base64_encode($ksCifrata),
                 base64_encode($iv), base64_encode($tag), forz_impronta()]);
  } catch (Throwable $e) {
    forz_log($pdo, $uid, 'chiudi', 'errore-db');
    return ['ok'=>false, 'err'=>'non sono riuscito a salvare la busta'];
  }
  sodium_memzero($ks);
  sodium_memzero($seed);

  forz_log($pdo, $uid, 'chiudi', 'ok');
  return ['ok'=>true, 'uid'=>$uid, 'indirizzo'=>$indirizzo, 'impronta'=>forz_impronta(),
          'nota'=>'busta chiusa: da qui la apre solo la chiave privata sul PC'];
}

/* ============================================================================
   LA CODA — un wallet nasce con la persona.
   Cinque milioni di posizioni sono aritmetica e non costano niente. Cinque
   milioni di wallet sarebbero 2,5 GB di chiavi private cifrate e 4.992.128
   bersagli per gente che non esiste. Quindi: si accoda alla registrazione.
============================================================================ */
function forz_accoda(PDO $pdo, int $uid, int $posto = 0): bool {
  if ($uid <= 0) return false;
  forz_migra($pdo);
  try {
    /* ce l'ha gia'? allora non si fa niente */
    $s = $pdo->prepare("SELECT 1 FROM wallet_forziere WHERE uid=? LIMIT 1");
    $s->execute([$uid]);
    if ($s->fetchColumn()) return true;
    $pdo->prepare("INSERT OR IGNORE INTO wallet_coda(uid,posto) VALUES(?,?)")->execute([$uid, $posto]);
    return true;
  } catch (Throwable $e) { return false; }
}

function forz_coda_stato(PDO $pdo): array {
  forz_migra($pdo);
  $o = ['in_attesa'=>0, 'fatto'=>0, 'errore'=>0, 'buste'=>0];
  try {
    foreach ($pdo->query("SELECT stato, COUNT(*) c FROM wallet_coda GROUP BY stato") as $r) {
      $k = (string)$r['stato']; if (isset($o[$k])) $o[$k] = (int)$r['c'];
    }
    $o['buste'] = (int)$pdo->query("SELECT COUNT(*) FROM wallet_forziere")->fetchColumn();
  } catch (Throwable $e) {}
  return $o;
}

/* ============================================================================
   LO STATO DI SALUTE — quello che si guarda dal Preflight.
   Dice la verita' anche quando e' scomoda: se il forziere non e' configurato,
   lo scrive invece di far finta.
============================================================================ */
function forz_salute(PDO $pdo): array {
  forz_migra($pdo);
  $pronto = forz_pronto();
  $bit = 0;
  if ($pronto) {
    $k = @openssl_pkey_get_public(forz_pubblica());
    if ($k !== false) { $d = openssl_pkey_get_details($k); $bit = (int)($d['bits'] ?? 0); }
  }
  $c = forz_coda_stato($pdo);
  $avvisi = [];
  if (!$pronto)        $avvisi[] = 'DR_FORZIERE_PUB non c\'e\': i wallet nuovi non si creano';
  if ($pronto && $bit < 3072) $avvisi[] = 'la chiave pubblica e\' di ' . $bit . ' bit: sotto i 3072 non basta piu\'';
  if ($c['errore'] > 0) $avvisi[] = $c['errore'] . ' wallet in coda hanno dato errore';
  /* la privata non deve MAI stare qui: se c'e', e' un allarme rosso */
  if (function_exists('dr_env') && trim((string)dr_env('DR_FORZIERE_PRIV', '')) !== '') {
    $avvisi[] = 'ALLARME: la chiave PRIVATA e\' nel .env del server. Va tolta subito: '
              . 'con quella dentro, il forziere non serve piu\' a niente.';
  }
  return ['pronto'=>$pronto, 'bit'=>$bit, 'impronta'=>forz_impronta(),
          'coda'=>$c, 'avvisi'=>$avvisi,
          'in_una_riga'=>$pronto
            ? 'Il server puo\' chiudere le buste ma non aprirle: la privata sta sul PC.'
            : 'Forziere spento: manca la chiave pubblica.'];
}

} /* function_exists */
