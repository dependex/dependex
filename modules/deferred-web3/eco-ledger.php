<?php
/**
 * ECO-LEDGER — la partita doppia dell'economia.
 *
 * Le idee sono quelle di ledger/dr-ledger.php, che resta dov'e' e non si
 * tocca. Qui cambiano tre cose, e tutte e tre per una ragione:
 *
 *  1. TESTATA + RIGHE invece di sole righe. Un movimento e' una riga di
 *     eco_journal_tx con N righe in eco_journal_entries. La catena di
 *     impronte sta sulla TESTATA: una impronta per movimento, non una
 *     per riga. Su 5 milioni di utenti sono milioni di hash in meno.
 *
 *  2. DR/CR ESPLICITI. Ogni riga dice se e' dare o avere e porta un
 *     importo POSITIVO; il segno lo mette il codice in signed_base.
 *     Un importo negativo in una colonna di importi e' una trappola.
 *
 *  3. IDEMPOTENZA OBBLIGATORIA. Ogni eco_post() vuole una chiave. Se
 *     arriva due volte la stessa chiave, il secondo giro NON scrive:
 *     ritorna il movimento gia' fatto. E' la sola difesa vera contro il
 *     doppio accredito quando l'indicizzatore riparte.
 *
 * Importi: interi in unita' base, sempre stringhe, aritmetica bigi_*.
 * Mai float. Nemmeno per un totale da mostrare.
 */
declare(strict_types=1);

require_once __DIR__ . '/eco-db.php';

/* ------------------------------------------------------------------ asset */
function eco_asset(string $symbol): array
{
    static $c = [];
    $s = strtoupper($symbol);
    if (isset($c[$s])) return $c[$s];
    $r = eco_uno('SELECT * FROM eco_asset_registry WHERE symbol=?', [$s]);
    if (!$r) throw new RuntimeException('ECO-LEDGER: asset sconosciuto: ' . $s);
    return $c[$s] = $r;
}
function eco_asset_id(string $symbol): int { return (int)eco_asset($symbol)['asset_id']; }
function eco_asset_dec(string $symbol): int { return (int)eco_asset($symbol)['decimals']; }

/** Gli asset di casa. Idempotente: rilanciarla non duplica niente. */
function eco_asset_seed(): int
{
    $A = [
        [1, 'DUX',    6,  'UTILITY',  null, 0],   // punto interno. Non e' denaro, non si converte fuori.
        [2, 'DRX',    18, 'UTILITY',  null, 0],
        [3, '81X',    18, 'UTILITY',  null, 0],
        [4, 'ERIDAN', 18, 'EXTERNAL', null, 1],   // l'unico che esce dall'ecosistema
        [5, 'USDT',   6,  'STABLE',   137,  1],   // Polygon
        [6, 'POL',    18, 'GAS',      137,  1],
        [7, 'BTC',    8,  'REF',      null, 0],   // solo riferimento di prezzo
    ];
    $n = 0;
    foreach ($A as [$id, $sym, $dec, $kind, $chain, $tr]) {
        if (eco_valore('SELECT asset_id FROM eco_asset_registry WHERE asset_id=? OR symbol=?', [$id, $sym])) continue;
        eco_esegui('INSERT INTO eco_asset_registry (asset_id,symbol,decimals,kind,chain_id,contract,transferable,created_at) VALUES (?,?,?,?,?,NULL,?,?)',
            [$id, $sym, $dec, $kind, $chain, $tr, eco_now()]);
        $n++;
    }
    return $n;
}

/* ------------------------------------------------------------------ conti */
function eco_account_code(string $ownerKind, ?int $userId, string $bucket, string $asset): string
{
    return $ownerKind === 'USER' ? 'USER:' . (int)$userId . ':' . $bucket . ':' . strtoupper($asset)
                                 : 'SYS:' . $bucket . ':' . strtoupper($asset);
}

/** Prende (o crea) un conto. I conti di sistema possono andare sotto zero: sono le contropartite del mondo. */
function eco_account(string $ownerKind, ?int $userId, string $bucket, string $asset, ?bool $allowNeg = null): int
{
    $ownerKind = strtoupper($ownerKind);
    if (!in_array($ownerKind, ['USER', 'SYSTEM'], true)) throw new InvalidArgumentException('ECO-LEDGER: owner_kind.');
    if ($ownerKind === 'USER' && !$userId) throw new InvalidArgumentException('ECO-LEDGER: conto utente senza user_id.');
    if ($ownerKind === 'SYSTEM') $userId = null;
    $code = eco_account_code($ownerKind, $userId, $bucket, $asset);
    $id = eco_valore('SELECT account_id FROM eco_ledger_accounts WHERE account_code=?', [$code]);
    if ($id !== null) return (int)$id;
    $allowNeg ??= ($ownerKind === 'SYSTEM');
    try {
        eco_esegui('INSERT INTO eco_ledger_accounts (account_code,owner_kind,user_id,bucket,asset_id,allow_negative,created_at) VALUES (?,?,?,?,?,?,?)',
            [$code, $ownerKind, $userId, $bucket, eco_asset_id($asset), $allowNeg ? 1 : 0, eco_now()]);
    } catch (PDOException $e) {
        $m = strtolower($e->getMessage());
        if (!str_contains($m, 'unique') && !str_contains($m, 'duplicate')) throw $e;
    }
    return (int)eco_valore('SELECT account_id FROM eco_ledger_accounts WHERE account_code=?', [$code]);
}

function eco_account_id(string $code): ?int
{
    $v = eco_valore('SELECT account_id FROM eco_ledger_accounts WHERE account_code=?', [$code]);
    return $v === null ? null : (int)$v;
}

/* ------------------------------------------------------------------ impronte */
function eco_hash_ultimo(): string
{
    $v = eco_valore('SELECT hash_self FROM eco_journal_tx ORDER BY journal_tx_id DESC LIMIT 1' . eco_for_update(), [], null);
    return $v === null ? str_repeat('0', 64) : (string)$v;
}

/** Impronta delle righe: ordine fisso, campi fissi. Cambiarlo invalida la catena esistente. */
function eco_digest_righe(array $righe): string
{
    $s = '';
    foreach ($righe as $r) $s .= $r['seq'] . '|' . $r['account_id'] . '|' . $r['asset_id'] . '|' . $r['direction'] . '|' . $r['amount'] . '|' . $r['memo'] . "\n";
    return hash('sha256', $s);
}

/** Impronta della testata. Campi in ordine fisso: se serve un campo nuovo, si aggiunge IN FONDO. */
function eco_hash_tx(array $t, string $prev, string $digest): string
{
    return hash('sha256', implode('|', [
        $prev, $t['tx_uuid'], $t['idem_key'], $t['kind'], $t['created_at'],
        $t['entry_count'], $t['debit_total'], $t['credit_total'], $t['author'], $digest,
    ]));
}

/* ------------------------------------------------------------------ la scrittura */
/**
 * Scrive UN movimento. O tutto, o niente.
 *
 * @param array $entries  ogni riga: [
 *      'account'   => int account_id  oppure string account_code,
 *      'asset'     => 'DUX',
 *      'direction' => 'DR'|'CR',
 *      'amount'    => '1000000'   (intero POSITIVO, unita' base),
 *      'memo'      => ''          (facoltativo)
 *   ]
 * @param string $idempotency_key  chiave unica dell'operazione (non del tentativo)
 * @param array  $meta  ['kind'=>'DEPOSIT', 'author'=>'indexer', ...] finisce in meta_json
 *
 * @return array ['journal_tx_id'=>int, 'tx_uuid'=>string, 'hash_self'=>string, 'replay'=>bool]
 */
function eco_post(array $entries, string $idempotency_key, array $meta = []): array
{
    if ($idempotency_key === '' || strlen($idempotency_key) > 190) throw new InvalidArgumentException('ECO-LEDGER: chiave di idempotenza mancante o troppo lunga.');
    if (count($entries) < 2) throw new InvalidArgumentException('ECO-LEDGER: un movimento ha almeno due righe. Una riga sola non e una scrittura: e un desiderio.');

    $kind   = (string)($meta['kind'] ?? 'GENERIC');
    $author = (string)($meta['author'] ?? 'system');

    return eco_tx(function () use ($entries, $idempotency_key, $meta, $kind, $author) {

        // --- replay: stessa chiave = stesso risultato, senza riscrivere niente
        $gia = eco_uno('SELECT journal_tx_id, tx_uuid, hash_self FROM eco_journal_tx WHERE idem_key=?', [$idempotency_key]);
        if ($gia) return ['journal_tx_id' => (int)$gia['journal_tx_id'], 'tx_uuid' => (string)$gia['tx_uuid'], 'hash_self' => (string)$gia['hash_self'], 'replay' => true];

        // --- normalizzazione e controlli riga per riga
        $righe = []; $seq = 0; $perAsset = [];
        $dr = '0'; $cr = '0';
        foreach ($entries as $e) {
            $seq++;
            $acc = $e['account'] ?? null;
            $accId = is_int($acc) ? $acc : (is_string($acc) ? eco_account_id($acc) : null);
            if (!$accId) throw new InvalidArgumentException('ECO-LEDGER: conto inesistente alla riga ' . $seq . '.');
            $dir = strtoupper((string)($e['direction'] ?? ''));
            if ($dir !== 'DR' && $dir !== 'CR') throw new InvalidArgumentException('ECO-LEDGER: direction deve essere DR o CR (riga ' . $seq . ').');
            $amt = bigi_pulisci((string)($e['amount'] ?? '0'));
            if (!preg_match('/^\d+$/', $amt)) throw new InvalidArgumentException('ECO-LEDGER: importo non intero o negativo alla riga ' . $seq . '. Il segno lo mette la direzione, non l importo.');
            if ($amt === '0') throw new InvalidArgumentException('ECO-LEDGER: riga da zero alla riga ' . $seq . '. Una scrittura da zero non racconta niente.');
            if (strlen($amt) > 38) throw new InvalidArgumentException('ECO-LEDGER: importo oltre 38 cifre alla riga ' . $seq . '.');

            $A = eco_uno('SELECT account_id, asset_id, allow_negative, owner_kind FROM eco_ledger_accounts WHERE account_id=?', [$accId]);
            if (!$A) throw new InvalidArgumentException('ECO-LEDGER: conto ' . $accId . ' inesistente.');
            $assetId = isset($e['asset']) ? eco_asset_id((string)$e['asset']) : (int)$A['asset_id'];
            if ($assetId !== (int)$A['asset_id'])
                throw new InvalidArgumentException('ECO-LEDGER: la riga ' . $seq . ' porta un asset diverso da quello del conto. Un conto e di UN asset solo.');

            $righe[] = ['seq' => $seq, 'account_id' => $accId, 'asset_id' => $assetId, 'direction' => $dir,
                        'amount' => $amt, 'memo' => (string)($e['memo'] ?? ''), 'allow_negative' => (int)$A['allow_negative']];

            $perAsset[$assetId] ??= ['dr' => '0', 'cr' => '0'];
            $perAsset[$assetId][$dir === 'DR' ? 'dr' : 'cr'] = bigi_add($perAsset[$assetId][$dir === 'DR' ? 'dr' : 'cr'], $amt);
            if ($dir === 'DR') $dr = bigi_add($dr, $amt); else $cr = bigi_add($cr, $amt);
        }

        // --- partita doppia PER ASSET: non basta che il totale torni
        foreach ($perAsset as $aid => $t) {
            if (bigi_cmp($t['dr'], $t['cr']) !== 0) {
                $sym = (string)eco_valore('SELECT symbol FROM eco_asset_registry WHERE asset_id=?', [$aid], (string)$aid);
                throw new RuntimeException('ECO-LEDGER: dare != avere su ' . $sym . ' (DR ' . $t['dr'] . ' / CR ' . $t['cr'] . '). Manca una contropartita, cioe manca un pezzo di verita.');
            }
        }
        if (bigi_cmp($dr, $cr) !== 0) throw new RuntimeException('ECO-LEDGER: dare != avere sul totale (' . $dr . ' / ' . $cr . ').');

        // --- testata con impronta
        $t = ['tx_uuid' => eco_uuid(), 'idem_key' => $idempotency_key, 'kind' => $kind,
              'entry_count' => count($righe), 'debit_total' => $dr, 'credit_total' => $cr,
              'author' => $author, 'created_at' => eco_now()];
        $prev   = eco_hash_ultimo();
        $digest = eco_digest_righe($righe);
        $self   = eco_hash_tx($t, $prev, $digest);
        $meta['entries_digest'] = $digest;

        eco_esegui('INSERT INTO eco_journal_tx (tx_uuid,idem_key,kind,meta_json,entry_count,debit_total,credit_total,author,hash_prev,hash_self,created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [$t['tx_uuid'], $t['idem_key'], $t['kind'], json_encode($meta, JSON_UNESCAPED_UNICODE),
             $t['entry_count'], $t['debit_total'], $t['credit_total'], $t['author'], $prev, $self, $t['created_at']]);
        $txId = (int)eco_ultimo_id();

        // --- righe + saldi
        $ins = eco_db()->prepare('INSERT INTO eco_journal_entries (journal_tx_id,seq,account_id,asset_id,direction,amount_base,signed_base,memo,created_at) VALUES (?,?,?,?,?,?,?,?,?)');
        foreach ($righe as $r) {
            $signed = $r['direction'] === 'DR' ? $r['amount'] : bigi_neg($r['amount']);
            $ins->execute([$txId, $r['seq'], $r['account_id'], $r['asset_id'], $r['direction'], $r['amount'], $signed, $r['memo'], $t['created_at']]);
            $entryId = (int)eco_ultimo_id();
            eco_saldo_applica((int)$r['account_id'], (int)$r['asset_id'], $signed, $entryId, (bool)$r['allow_negative'], $t['created_at']);
        }

        eco_outbox_add('ledger', 'JournalPosted:' . $t['tx_uuid'],
            ['journal_tx_id' => $txId, 'kind' => $kind, 'entries' => count($righe), 'total' => $dr, 'hash' => $self]);

        eco_idem_registra($idempotency_key, 'ledger', (string)$txId, $digest);

        return ['journal_tx_id' => $txId, 'tx_uuid' => $t['tx_uuid'], 'hash_self' => $self, 'replay' => false];
    }, true);
}

/** Applica un delta al saldo materializzato. Sotto zero solo dove il conto lo permette. */
function eco_saldo_applica(int $accountId, int $assetId, string $delta, int $entryId, bool $allowNeg, int $quando): void
{
    $cur = eco_valore('SELECT balance_base FROM eco_balances WHERE account_id=?' . eco_for_update(), [$accountId], null);
    $nuovo = bigi_add($cur === null ? '0' : (string)$cur, $delta);
    if (!$allowNeg && bigi_cmp($nuovo, '0') < 0) {
        $code = (string)eco_valore('SELECT account_code FROM eco_ledger_accounts WHERE account_id=?', [$accountId], (string)$accountId);
        throw new RuntimeException('ECO-LEDGER: il conto ' . $code . ' andrebbe a ' . $nuovo . '. Movimento annullato: un saldo negativo su un conto utente e un buco, non un numero.');
    }
    if ($cur === null) eco_esegui('INSERT INTO eco_balances (account_id,asset_id,balance_base,last_entry_id,updated_at) VALUES (?,?,?,?,?)', [$accountId, $assetId, $nuovo, $entryId, $quando]);
    else               eco_esegui('UPDATE eco_balances SET balance_base=?, last_entry_id=?, updated_at=? WHERE account_id=?', [$nuovo, $entryId, $quando, $accountId]);
}

/* ------------------------------------------------------------------ letture */
/** Saldo di un conto (id o codice), in unita' base. */
function eco_balance($account): string
{
    $id = is_int($account) ? $account : eco_account_id((string)$account);
    if (!$id) return '0';
    $v = eco_valore('SELECT balance_base FROM eco_balances WHERE account_id=?', [$id], '0');
    return bigi_pulisci((string)$v);
}

/** Tutti i conti di un utente con saldo. */
function eco_saldi_utente(int $userId): array
{
    return eco_tutti('SELECT a.account_code, a.bucket, r.symbol, r.decimals, COALESCE(b.balance_base,\'0\') AS saldo
                      FROM eco_ledger_accounts a
                      JOIN eco_asset_registry r ON r.asset_id=a.asset_id
                      LEFT JOIN eco_balances b ON b.account_id=a.account_id
                      WHERE a.user_id=? ORDER BY r.symbol, a.bucket', [$userId]);
}

/* ------------------------------------------------------------------ manutenzione */
/**
 * Ricostruisce eco_balances leggendo TUTTE le righe del giornale.
 * E' la prova che i saldi non sono un'opinione: se il ricostruito non
 * coincide col vivo, i saldi erano sbagliati e ora si vede.
 */
function eco_rebuild_balances(int $chunk = 20000): array
{
    $t0 = microtime(true);
    $saldi = []; $ultimo = []; $asset = [];
    $min = 0; $n = 0;
    while (true) {
        $righe = eco_tutti('SELECT entry_id, account_id, asset_id, signed_base FROM eco_journal_entries WHERE entry_id>? ORDER BY entry_id LIMIT ' . (int)$chunk, [$min]);
        if (!$righe) break;
        foreach ($righe as $r) {
            $a = (int)$r['account_id'];
            $saldi[$a] = bigi_add($saldi[$a] ?? '0', (string)$r['signed_base']);
            $ultimo[$a] = (int)$r['entry_id'];
            $asset[$a] = (int)$r['asset_id'];
            $min = (int)$r['entry_id']; $n++;
        }
    }
    $now = eco_now();
    eco_tx(function () use ($saldi, $ultimo, $asset, $now) {
        eco_esegui('UPDATE eco_balances SET balance_base=\'0\', last_entry_id=0, updated_at=?', [$now]);
        foreach ($saldi as $a => $s) {
            $c = eco_valore('SELECT account_id FROM eco_balances WHERE account_id=?', [$a], null);
            if ($c === null) eco_esegui('INSERT INTO eco_balances (account_id,asset_id,balance_base,last_entry_id,updated_at) VALUES (?,?,?,?,?)', [$a, $asset[$a], $s, $ultimo[$a], $now]);
            else             eco_esegui('UPDATE eco_balances SET balance_base=?, last_entry_id=?, updated_at=? WHERE account_id=?', [$s, $ultimo[$a], $now, $a]);
        }
    });
    return ['conti' => count($saldi), 'righe' => $n, 'ms' => (int)round((microtime(true) - $t0) * 1000)];
}

/** Verifica la catena di impronte, movimento per movimento. Dice DOVE si rompe, non solo SE. */
function eco_verify_chain(int $chunk = 5000): array
{
    $prev = str_repeat('0', 64); $min = 0; $n = 0; $rotti = [];
    while (true) {
        $tx = eco_tutti('SELECT * FROM eco_journal_tx WHERE journal_tx_id>? ORDER BY journal_tx_id LIMIT ' . (int)$chunk, [$min]);
        if (!$tx) break;
        foreach ($tx as $t) {
            $min = (int)$t['journal_tx_id']; $n++;
            $righe = eco_tutti('SELECT seq, account_id, asset_id, direction, amount_base AS amount, memo FROM eco_journal_entries WHERE journal_tx_id=? ORDER BY seq', [$min]);
            foreach ($righe as $i => $r) { $righe[$i]['amount'] = bigi_pulisci((string)$r['amount']); }
            $digest = eco_digest_righe($righe);
            $atteso = eco_hash_tx([
                'tx_uuid' => $t['tx_uuid'], 'idem_key' => $t['idem_key'], 'kind' => $t['kind'],
                'created_at' => $t['created_at'], 'entry_count' => $t['entry_count'],
                'debit_total' => bigi_pulisci((string)$t['debit_total']), 'credit_total' => bigi_pulisci((string)$t['credit_total']),
                'author' => $t['author'],
            ], $prev, $digest);
            if ((string)$t['hash_prev'] !== $prev) $rotti[] = ['journal_tx_id' => $min, 'perche' => 'hash_prev non combacia col movimento precedente'];
            elseif ((string)$t['hash_self'] !== $atteso) $rotti[] = ['journal_tx_id' => $min, 'perche' => 'impronta ricalcolata diversa: righe o testata modificate'];
            $prev = (string)$t['hash_self'];
            if (count($rotti) > 50) return ['ok' => false, 'movimenti' => $n, 'rotti' => $rotti, 'nota' => 'fermato a 50 rotture'];
        }
    }
    return ['ok' => empty($rotti), 'movimenti' => $n, 'rotti' => $rotti];
}

/** La somma di TUTTE le righe, per asset, deve fare zero. Se non fa zero, il registro e rotto. */
function eco_totali_per_asset(): array
{
    $out = [];
    foreach (eco_tutti('SELECT asset_id, symbol FROM eco_asset_registry ORDER BY asset_id') as $a) {
        $s = '0'; $min = 0;
        while (true) {
            $r = eco_tutti('SELECT entry_id, signed_base FROM eco_journal_entries WHERE asset_id=? AND entry_id>? ORDER BY entry_id LIMIT 20000', [(int)$a['asset_id'], $min]);
            if (!$r) break;
            foreach ($r as $x) { $s = bigi_add($s, (string)$x['signed_base']); $min = (int)$x['entry_id']; }
        }
        $out[(string)$a['symbol']] = bigi_pulisci($s);
    }
    return $out;
}

/* ------------------------------------------------------------------ outbox e idempotenza */
/** Un evento per chi ascolta. Al posto di Kafka: una tabella, letta da un worker. */
function eco_outbox_add(string $topic, string $eventKey, array $payload): void
{
    try {
        eco_esegui('INSERT INTO eco_outbox (topic,event_key,payload_json,state,attempts,created_at) VALUES (?,?,?,?,0,?)',
            [$topic, $eventKey, json_encode($payload, JSON_UNESCAPED_UNICODE), 'PENDING', eco_now()]);
    } catch (PDOException $e) {
        $m = strtolower($e->getMessage());
        if (!str_contains($m, 'unique') && !str_contains($m, 'duplicate')) throw $e;   // stesso evento due volte = una riga sola
    }
}

function eco_idem_registra(string $key, string $scope, string $ref, string $fingerprint): void
{
    try {
        eco_esegui('INSERT INTO eco_idempotency (idem_key,scope,result_ref,fingerprint,created_at) VALUES (?,?,?,?,?)',
            [$key, $scope, $ref, $fingerprint, eco_now()]);
    } catch (PDOException $e) {
        $m = strtolower($e->getMessage());
        if (!str_contains($m, 'unique') && !str_contains($m, 'duplicate')) throw $e;
    }
}

function eco_idem_cerca(string $key): ?array { return eco_uno('SELECT * FROM eco_idempotency WHERE idem_key=?', [$key]); }
