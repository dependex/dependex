<?php
/**
 * ECO-DEPOSIT — dal trasferimento sulla catena al DUX sul conto.
 *
 * IL CICLO DI VITA, per intero e senza scorciatoie:
 *
 *   CREATED -> AWAITING_TRANSFER -> DETECTED -> CONFIRMING -> FINALIZED
 *           -> CREDITED -> SWEEP_PENDING -> SWEPT -> RECONCILED -> COMPLETED
 *
 *   e le uscite di sicurezza, che esistono perche' la realta' esiste:
 *   EXPIRED · UNDERPAID · OVERPAID · WRONG_ASSET · REORGED · FROZEN · FAILED
 *
 * LA REGOLA DI ACCREDITO: 1 USDT = 1 DUX, in DUX_DEPOSIT.
 * DUX_DEPOSIT NON e' prelevabile. E' scritto qui, ed e' scritto in
 * pagina prima che l'utente versi: un DUX da deposito serve dentro,
 * non esce. Chi vuole uscire usa i DUX finanziati (V4).
 *
 * DUE COSE CHE QUESTO FILE NON FA, E NON DEVE FARE:
 *   · non parla con nessun RPC. L'indicizzatore e' un servizio a parte
 *     (demo/indexer-depositi.php e i suoi eredi): qui c'e' solo il
 *     GANCIO, cioe' l'interfaccia che quel servizio deve rispettare.
 *   · non firma niente. Lo sweep verso la tesoreria esce come ordine in
 *     coda; a firmare e' Mirco, dal PC.
 *
 * ANTI-DOPPIO-ACCREDITO: l'evento e' unico per (chain_id, tx_hash,
 * log_index) — vincolo nel database, non una buona intenzione nel codice
 * — e ogni scrittura porta una chiave di idempotenza derivata da quello.
 * Un indicizzatore che riparte e rilegge mille blocchi non accredita
 * niente due volte. E' l'errore piu' caro di questo mestiere.
 */
declare(strict_types=1);

require_once __DIR__ . '/eco-db.php';
require_once __DIR__ . '/eco-ledger.php';
require_once __DIR__ . '/eco-onboarding.php';
require_once __DIR__ . '/eco-custody.php';

/* ------------------------------------------------------------------ transizioni ammesse */
function eco_deposit_transizioni(): array
{
    return [
        'CREATED'           => ['AWAITING_TRANSFER', 'EXPIRED', 'FAILED'],
        'AWAITING_TRANSFER' => ['DETECTED', 'EXPIRED', 'WRONG_ASSET', 'FROZEN', 'FAILED'],
        'DETECTED'          => ['CONFIRMING', 'REORGED', 'WRONG_ASSET', 'UNDERPAID', 'OVERPAID', 'FROZEN', 'FAILED'],
        'CONFIRMING'        => ['FINALIZED', 'REORGED', 'FROZEN', 'FAILED'],
        'FINALIZED'         => ['CREDITED', 'FROZEN', 'FAILED'],
        'CREDITED'          => ['SWEEP_PENDING', 'FROZEN'],
        'SWEEP_PENDING'     => ['SWEPT', 'FAILED', 'FROZEN'],
        'SWEPT'             => ['RECONCILED', 'FAILED'],
        'RECONCILED'        => ['COMPLETED'],
        'COMPLETED'         => [],
        'UNDERPAID'         => ['CREDITED', 'FROZEN', 'FAILED'],
        'OVERPAID'          => ['CREDITED', 'FROZEN', 'FAILED'],
        'WRONG_ASSET'       => ['FROZEN', 'FAILED'],
        'REORGED'           => ['DETECTED', 'FAILED'],
        'FROZEN'            => ['DETECTED', 'CONFIRMING', 'FINALIZED', 'CREDITED', 'FAILED'],
        'EXPIRED'           => [],
        'FAILED'            => [],
    ];
}

function eco_deposit_transizione(int $intentId, string $nuovo, array $campi = []): array
{
    return eco_tx(function () use ($intentId, $nuovo, $campi) {
        $i = eco_uno('SELECT * FROM eco_deposit_intents WHERE intent_id=?' . eco_for_update(), [$intentId]);
        if (!$i) throw new RuntimeException('ECO-DEPOSIT: intento ' . $intentId . ' inesistente.');
        $da = (string)$i['state'];
        if ($da === $nuovo) return $i;                                   // idempotente
        $ok = eco_deposit_transizioni()[$da] ?? [];
        if (!in_array($nuovo, $ok, true))
            throw new RuntimeException('ECO-DEPOSIT: transizione ' . $da . ' -> ' . $nuovo . ' non ammessa. Gli stati non si saltano.');
        $set = ['state=?', 'updated_at=?']; $val = [$nuovo, eco_now()];
        foreach (['observed_base', 'credited_base', 'event_id', 'journal_tx_id', 'expires_at'] as $c)
            if (array_key_exists($c, $campi)) { $set[] = $c . '=?'; $val[] = $campi[$c]; }
        $val[] = $intentId;
        eco_esegui('UPDATE eco_deposit_intents SET ' . implode(',', $set) . ' WHERE intent_id=?', $val);
        eco_outbox_add('deposit', 'DepositIntentState:' . $intentId . ':' . $nuovo, ['intent_id' => $intentId, 'da' => $da, 'a' => $nuovo]);
        return eco_uno('SELECT * FROM eco_deposit_intents WHERE intent_id=?', [$intentId]);
    });
}

/* ------------------------------------------------------------------ intento */
/** Apre un intento di deposito. $expected null = "quanto vuoi". */
function eco_deposit_intent_crea(int $userId, string $asset = 'USDT', ?string $expectedBase = null, int $ttlOre = 72): array
{
    $a = eco_asset($asset);
    $addr = eco_deposit_address_assegna($userId, (int)($a['chain_id'] ?? eco_chain_id()));
    $t = eco_now();
    eco_esegui('INSERT INTO eco_deposit_intents (intent_uuid,user_id,address_id,asset_id,expected_base,observed_base,credited_base,state,expires_at,created_at,updated_at)
                VALUES (?,?,?,?,?,\'0\',\'0\',?,?,?,?)',
        [eco_uuid(), $userId, (int)$addr['address_id'], (int)$a['asset_id'], $expectedBase, 'CREATED', $t + $ttlOre * 3600, $t, $t]);
    $id = (int)eco_ultimo_id();
    eco_outbox_add('deposit', 'DepositIntentCreated:' . $id, ['intent_id' => $id, 'user_id' => $userId, 'asset' => $a['symbol']]);
    return eco_deposit_transizione($id, 'AWAITING_TRANSFER');
}

/* ------------------------------------------------------------------ eventi on-chain */
/**
 * Registra un evento letto dalla catena. Se c'e' gia', NON lo riscrive:
 * ritorna quello con duplicato = true. Questa e' la riga che impedisce
 * il doppio conteggio, e sta nel database (UNIQUE), non nella fiducia.
 */
function eco_deposit_evento(array $ev): array
{
    foreach (['chain_id', 'tx_hash', 'log_index', 'block_number', 'to_addr', 'amount_base'] as $k)
        if (!array_key_exists($k, $ev)) throw new InvalidArgumentException('ECO-DEPOSIT: evento senza ' . $k . '.');
    $chain = (int)$ev['chain_id']; $tx = strtolower((string)$ev['tx_hash']); $log = (int)$ev['log_index'];
    if (!preg_match('/^0x[0-9a-f]{64}$/', $tx)) throw new InvalidArgumentException('ECO-DEPOSIT: tx_hash non valido.');

    $g = eco_uno('SELECT * FROM eco_onchain_events WHERE chain_id=? AND tx_hash=? AND log_index=?', [$chain, $tx, $log]);
    if ($g) return ['event_id' => (int)$g['event_id'], 'duplicato' => true, 'evento' => $g];

    try {
        eco_esegui('INSERT INTO eco_onchain_events (chain_id,tx_hash,log_index,block_number,block_hash,contract,from_addr,to_addr,asset_id,amount_base,confirmations,finality,rpc_sources,seen_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$chain, $tx, $log, (int)$ev['block_number'], strtolower((string)($ev['block_hash'] ?? '')),
             strtolower((string)($ev['contract'] ?? '')), strtolower((string)($ev['from_addr'] ?? '')), strtolower((string)$ev['to_addr']),
             isset($ev['asset']) ? eco_asset_id((string)$ev['asset']) : null, bigi_pulisci((string)$ev['amount_base']),
             (int)($ev['confirmations'] ?? 0), (string)($ev['finality'] ?? 'SEEN'), (int)($ev['rpc_sources'] ?? 1), eco_now()]);
    } catch (PDOException $e) {
        $m = strtolower($e->getMessage());
        if (!str_contains($m, 'unique') && !str_contains($m, 'duplicate')) throw $e;
        $g = eco_uno('SELECT * FROM eco_onchain_events WHERE chain_id=? AND tx_hash=? AND log_index=?', [$chain, $tx, $log]);
        return ['event_id' => (int)$g['event_id'], 'duplicato' => true, 'evento' => $g];
    }
    $id = (int)eco_ultimo_id();
    return ['event_id' => $id, 'duplicato' => false, 'evento' => eco_uno('SELECT * FROM eco_onchain_events WHERE event_id=?', [$id])];
}

/* ------------------------------------------------------------------ il gancio di finalita' */
/**
 * L'indicizzatore vive fuori da qui. Deve rispettare questa interfaccia,
 * e deve rispondere leggendo DUE RPC diversi: se i due non concordano,
 * non si accredita. Un solo nodo che mente (o che e' indietro di venti
 * blocchi) e' abbastanza per regalare DUX a chi non ha versato.
 */
interface EcoFinalityCheck
{
    /** Quante conferme ha questa transazione, secondo la fonte piu' prudente delle due. */
    public function conferme(int $chainId, string $txHash): int;
    /** Vero solo se DUE fonti indipendenti vedono lo stesso blocco con lo stesso hash. */
    public function finale(int $chainId, string $txHash, int $blockNumber, string $blockHash): bool;
    /** Quante fonti RPC hanno risposto (>=2 per accreditare). */
    public function fonti(): int;
}

function eco_finality(?EcoFinalityCheck $set = null): ?EcoFinalityCheck
{
    static $h = null;
    if ($set !== null) $h = $set;
    return $h;
}
/** Conferme minime prima di considerare finale un deposito su Polygon. */
function eco_conferme_minime(): int { $v = (int)eco_env('ECO_MIN_CONFIRMATIONS', '128'); return $v > 0 ? $v : 128; }
function eco_fonti_minime(): int { $v = (int)eco_env('ECO_MIN_RPC_SOURCES', '2'); return $v > 0 ? $v : 2; }

/* ------------------------------------------------------------------ accredito */
/**
 * Vede l'evento e lo attribuisce: indirizzo -> utente -> intento.
 * Scrive la gamba PENDING (USDT visto ma non finale). Nessun DUX ancora.
 */
function eco_deposit_rileva(int $eventId): array
{
    return eco_tx(function () use ($eventId) {
        $ev = eco_uno('SELECT * FROM eco_onchain_events WHERE event_id=?', [$eventId]);
        if (!$ev) throw new RuntimeException('ECO-DEPOSIT: evento inesistente.');
        $ad = eco_uno('SELECT * FROM eco_deposit_addresses WHERE chain_id=? AND address_lc=?', [(int)$ev['chain_id'], strtolower((string)$ev['to_addr'])]);
        if (!$ad) return ['ok' => false, 'motivo' => 'indirizzo non nostro: nessun accredito, nessun intento'];

        $userId = (int)$ad['user_id'];
        $assetId = $ev['asset_id'] !== null ? (int)$ev['asset_id'] : eco_asset_id('USDT');
        $sym = (string)eco_valore('SELECT symbol FROM eco_asset_registry WHERE asset_id=?', [$assetId], 'USDT');

        $i = eco_uno("SELECT * FROM eco_deposit_intents WHERE user_id=? AND address_id=? AND asset_id=? AND state IN ('AWAITING_TRANSFER','CREATED') ORDER BY intent_id LIMIT 1" . eco_for_update(), [$userId, (int)$ad['address_id'], $assetId]);
        if (!$i) $i = eco_deposit_intent_crea($userId, $sym);

        $imp = bigi_pulisci((string)$ev['amount_base']);
        $r = eco_post([
            ['account' => eco_conto($userId, 'USDT_PENDING'), 'asset' => $sym, 'direction' => 'DR', 'amount' => $imp, 'memo' => 'deposit seen ' . substr((string)$ev['tx_hash'], 0, 12)],
            ['account' => eco_conto_sistema('EXTERNAL', $sym),  'asset' => $sym, 'direction' => 'CR', 'amount' => $imp, 'memo' => 'onchain in'],
        ], 'dep:seen:' . (int)$ev['chain_id'] . ':' . $ev['tx_hash'] . ':' . (int)$ev['log_index'], ['kind' => 'DEPOSIT_SEEN', 'author' => 'indexer', 'event_id' => $eventId]);

        eco_deposit_transizione((int)$i['intent_id'], 'DETECTED', ['observed_base' => $imp, 'event_id' => $eventId]);
        eco_deposit_transizione((int)$i['intent_id'], 'CONFIRMING');
        return ['ok' => true, 'intent_id' => (int)$i['intent_id'], 'journal_tx_id' => $r['journal_tx_id'], 'replay' => $r['replay']];
    }, true);
}

/**
 * Finalizza e accredita: USDT da PENDING a CONFIRMED, e 1 USDT = 1 DUX
 * in DUX_DEPOSIT. Un solo movimento, quattro righe, due asset.
 * Rifiuta se le conferme o le fonti RPC non bastano.
 */
function eco_deposit_accredita(int $eventId, bool $forzaAdmin = false): array
{
    return eco_tx(function () use ($eventId, $forzaAdmin) {
        $ev = eco_uno('SELECT * FROM eco_onchain_events WHERE event_id=?' . eco_for_update(), [$eventId]);
        if (!$ev) throw new RuntimeException('ECO-DEPOSIT: evento inesistente.');
        if ((string)$ev['finality'] === 'REORGED') throw new RuntimeException('ECO-DEPOSIT: evento riorganizzato: non si accredita.');

        $conf = (int)$ev['confirmations']; $fonti = (int)$ev['rpc_sources'];
        $H = eco_finality();
        if ($H) { $conf = max($conf, $H->conferme((int)$ev['chain_id'], (string)$ev['tx_hash'])); $fonti = max($fonti, $H->fonti()); }
        if (!$forzaAdmin && ($conf < eco_conferme_minime() || $fonti < eco_fonti_minime()))
            throw new RuntimeException('ECO-DEPOSIT: non finale (conferme ' . $conf . '/' . eco_conferme_minime() . ', fonti RPC ' . $fonti . '/' . eco_fonti_minime() . '). Non si accredita su un blocco che puo ancora sparire.');

        $ad = eco_uno('SELECT * FROM eco_deposit_addresses WHERE chain_id=? AND address_lc=?', [(int)$ev['chain_id'], strtolower((string)$ev['to_addr'])]);
        if (!$ad) return ['ok' => false, 'motivo' => 'indirizzo non nostro'];
        $userId = (int)$ad['user_id'];
        $assetId = $ev['asset_id'] !== null ? (int)$ev['asset_id'] : eco_asset_id('USDT');
        $sym = (string)eco_valore('SELECT symbol FROM eco_asset_registry WHERE asset_id=?', [$assetId], 'USDT');
        if ($sym !== 'USDT') {
            $i = eco_uno("SELECT * FROM eco_deposit_intents WHERE user_id=? AND event_id=? LIMIT 1", [$userId, $eventId]);
            if ($i) eco_deposit_transizione((int)$i['intent_id'], 'WRONG_ASSET');
            return ['ok' => false, 'motivo' => 'asset non accreditabile automaticamente: ' . $sym];
        }

        $imp = bigi_pulisci((string)$ev['amount_base']);          // USDT e DUX hanno tutti e due 6 decimali: 1 a 1, unita' base identiche
        $r = eco_post([
            ['account' => eco_conto($userId, 'USDT_CONFIRMED'), 'asset' => 'USDT', 'direction' => 'DR', 'amount' => $imp, 'memo' => 'deposit final'],
            ['account' => eco_conto($userId, 'USDT_PENDING'),   'asset' => 'USDT', 'direction' => 'CR', 'amount' => $imp, 'memo' => 'from pending'],
            ['account' => eco_conto($userId, 'DUX_DEPOSIT'),    'asset' => 'DUX',  'direction' => 'DR', 'amount' => $imp, 'memo' => '1 USDT = 1 DUX'],
            ['account' => eco_conto_sistema('MINT', 'DUX'),     'asset' => 'DUX',  'direction' => 'CR', 'amount' => $imp, 'memo' => 'dux issued on deposit'],
        ], 'dep:credit:' . (int)$ev['chain_id'] . ':' . $ev['tx_hash'] . ':' . (int)$ev['log_index'],
           ['kind' => 'DEPOSIT_CREDIT', 'author' => 'indexer', 'event_id' => $eventId, 'user_id' => $userId]);

        eco_esegui('UPDATE eco_onchain_events SET finality=?, confirmations=?, rpc_sources=? WHERE event_id=?', ['FINAL', $conf, $fonti, $eventId]);

        // Gli stati si fanno avanzare SOLO se l'intento e' ancora prima
        // dell'accredito. Se e' gia' oltre (perche' questa e' una
        // rilettura dello stesso evento) non si tocca niente: un replay
        // non deve ritentare transizioni gia' fatte.
        $i = eco_uno('SELECT * FROM eco_deposit_intents WHERE event_id=? ORDER BY intent_id LIMIT 1', [$eventId]);
        $prima = ['CREATED', 'AWAITING_TRANSFER', 'DETECTED', 'CONFIRMING', 'FINALIZED', 'UNDERPAID', 'OVERPAID'];
        if ($i && in_array((string)$i['state'], $prima, true)) {
            $iid = (int)$i['intent_id'];
            if (in_array((string)$i['state'], ['CREATED', 'AWAITING_TRANSFER'], true)) eco_deposit_transizione($iid, 'DETECTED', ['observed_base' => $imp, 'event_id' => $eventId]);
            if ((string)eco_uno('SELECT state FROM eco_deposit_intents WHERE intent_id=?', [$iid])['state'] === 'DETECTED') eco_deposit_transizione($iid, 'CONFIRMING');
            if ((string)eco_uno('SELECT state FROM eco_deposit_intents WHERE intent_id=?', [$iid])['state'] === 'CONFIRMING') eco_deposit_transizione($iid, 'FINALIZED');
            eco_deposit_transizione($iid, 'CREDITED', ['credited_base' => $imp, 'journal_tx_id' => $r['journal_tx_id']]);
            eco_sweep_accoda((int)$ad['address_id'], 'USDT', $imp);
            eco_deposit_transizione($iid, 'SWEEP_PENDING');
        }
        eco_outbox_add('deposit', 'DepositCredited:' . $eventId, ['event_id' => $eventId, 'user_id' => $userId, 'dux_base' => $imp]);
        return ['ok' => true, 'user_id' => $userId, 'journal_tx_id' => $r['journal_tx_id'], 'replay' => $r['replay'], 'dux_base' => $imp];
    }, true);
}

/** Mette in coda uno sweep verso la tesoreria. Nessuna firma qui: solo la coda. */
function eco_sweep_accoda(int $addressId, string $asset, string $importoBase): int
{
    $tes = eco_env('ECO_TREASURY_FLOW', '0xbde2aaa9e8d0afb90d42679c6e391e5c72be5f39');
    $t = eco_now();
    eco_esegui('INSERT INTO eco_sweep_orders (address_id,asset_id,amount_base,to_treasury,state,created_at,updated_at) VALUES (?,?,?,?,?,?,?)',
        [$addressId, eco_asset_id($asset), bigi_pulisci($importoBase), strtolower($tes), 'QUEUED', $t, $t]);
    return (int)eco_ultimo_id();
}

/** Quanto DUX ha in deposito un utente (unita' base). */
function eco_dux_deposito(int $userId): string { return eco_balance(eco_conto($userId, 'DUX_DEPOSIT')); }
