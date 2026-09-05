<?php
/* ============================================================================
   GENESYS/WALLET-HOOK-REGISTRAZIONE — aggancio al flusso di registrazione
   Destino Randagio · 2026-08-01 · destinazione: genesys/wallet-hook-registrazione.php

   DOVE SI AGGANCIA (LA riga da aggiungere, una sola):

   1) account.php — percorso "primo accesso col wallet esterno" (signup via
      MetaMask). SUBITO DOPO la riga che logga l'evento signup:
        if(function_exists('dr_log')) dr_log($pdo,'auth', $nuovo?'signup':'login', ['via'=>'wallet'], (int)$id);
      aggiungere:
        if($nuovo){ @include_once __DIR__.'/genesys/wallet-hook-registrazione.php'; if(function_exists('wh_hook_registrazione')) wh_hook_registrazione($pdo,(int)$id); }

   2) accedi.php (non presente nei file staged: e' l'unica pagina di login/
      registrazione classica, come dichiara account.php stesso) — stessa riga,
      subito dopo l'INSERT INTO users della registrazione, quando si conosce
      il nuovo id:
        @include_once __DIR__.'/genesys/wallet-hook-registrazione.php'; if(function_exists('wh_hook_registrazione')) wh_hook_registrazione($pdo,(int)$nuovoId);

   La funzione NON lancia MAI: una registrazione non puo' fallire perche' il
   wallet non si e' creato. Se WALLET_ENCRYPTION_KEY manca nel .env, il wallet
   NON viene creato (rifiuto esplicito, loggato) e il backfill lo recupera
   quando la chiave c'e'.

   BACKFILL ADMIN — utenti esistenti senza wallet, in lotti da 20:
     https://destinorandagio.it/genesys/wallet-hook-registrazione.php?backfill=1&key=GAS_CRON_KEY
   (stessa chiave cron del gas: un solo segreto da custodire; funziona anche
   loggati come admin senza chiave). Rilanciare finche' "senza_wallet" = 0.
============================================================================ */

if (!defined('DR_WALLET_HOOK')) {
  define('DR_WALLET_HOOK', 1);

  require_once __DIR__ . '/wallet-hybrid.php';

  /**
   * Crea il wallet on-chain per il nuovo utente e gli invia la seed via email
   * (UNA volta, transazionale). Ritorna sempre un array, non lancia mai.
   */
  function wh_hook_registrazione(PDO $pdo, int $uid): array {
    try {
      $r = wh_create_wallet_for_user($pdo, $uid);
      if (!empty($r['created']) && !empty($r['mnemonic'])) {
        $st = $pdo->prepare("SELECT email FROM users WHERE id=?");
        $st->execute([$uid]);
        $email = trim((string)$st->fetchColumn());
        if ($email !== '') {
          wh_send_seed_email($pdo, $uid, $email, $r['address'], $r['mnemonic']);
        } elseif (function_exists('dr_log')) {
          /* signup via wallet esterno: niente email in anagrafica. La seed resta
             cifrata in DB; quando l'utente aggiunge l'email si puo' reinviare. */
          dr_log($pdo, 'wallet', 'seed-email', ['esito'=>'saltata: utente senza email'], $uid);
        }
      }
      /* il mnemonic muore qui: mai oltre questa funzione */
      unset($r['mnemonic']);
      return $r;
    } catch (Throwable $e) {
      if (function_exists('dr_log')) {
        dr_log($pdo, 'wallet', 'crea-fallita', ['errore'=>$e->getMessage()], $uid);
      }
      return ['ok'=>false, 'created'=>false, 'errore'=>$e->getMessage()];
    }
  }
}

/* ============================================================================
   MODALITA' BACKFILL (endpoint diretto, solo admin o chiave cron)
============================================================================ */
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'wallet-hook-registrazione.php'
    && isset($_GET['backfill'])) {

  require_once dirname(__DIR__) . '/db.php';
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  header('Content-Type: application/json; charset=utf-8');

  $isAdmin = (($_SESSION['role'] ?? '') === 'admin');
  $attesa  = (string)dr_env('GAS_CRON_KEY', '');
  $chiave  = (string)($_GET['key'] ?? '');
  if (!$isAdmin && ($attesa === '' || !hash_equals($attesa, $chiave))) {
    http_response_code(403);
    echo json_encode(['ok'=>false, 'errore'=>'solo admin o chiave valida']); exit;
  }

  wh_tables($pdo);
  if (!wh_enc_ready()) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'errore'=>'WALLET_ENCRYPTION_KEY mancante nel .env: backfill rifiutato']); exit;
  }

  /* lotto da 20: su shared hosting non si tiene la richiesta aperta per ore */
  $q = $pdo->query("SELECT u.id FROM users u
                    LEFT JOIN wallet_onchain w ON w.user_id = u.id
                    WHERE w.id IS NULL ORDER BY u.id LIMIT 20");
  $esiti = [];
  foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $uid) {
    $r = wh_hook_registrazione($pdo, (int)$uid);
    $esiti[(int)$uid] = !empty($r['created']) ? 'creato ' . ($r['address'] ?? '')
                       : (!empty($r['ok']) ? 'gia_presente' : 'errore: ' . ($r['errore'] ?? '?'));
  }
  $restano = (int)$pdo->query("SELECT COUNT(*) FROM users u
                               LEFT JOIN wallet_onchain w ON w.user_id=u.id
                               WHERE w.id IS NULL")->fetchColumn();
  if (function_exists('dr_log')) {
    dr_log($pdo, 'wallet', 'backfill', ['creati'=>count($esiti), 'senza_wallet'=>$restano], 0, null, 'cron');
  }
  echo json_encode(['ok'=>true, 'lotto'=>$esiti, 'senza_wallet'=>$restano,
                    'nota'=>$restano > 0 ? 'rilancia per il prossimo lotto da 20' : 'backfill completo'],
                   JSON_UNESCAPED_UNICODE); exit;
}
