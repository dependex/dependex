<?php
/**
 * ECO-CUSTODY — il wallet di custodia e il suo indirizzo di deposito.
 *
 * QUI DENTRO NON C'E' NESSUNA CHIAVE PRIVATA. Non ce n'e' una cifrata,
 * non ce n'e' una "temporanea", non ce n'e' una nel .env. Il server
 * calcola un INDIRIZZO, non un potere di firma. Chi firma e' Mirco, dal
 * PC, con gli script di funding\. Se un giorno leggi in questo file la
 * parola "privateKey", qualcuno ha sbagliato e va fermato.
 *
 * COME NASCE L'INDIRIZZO — CREATE2, controfattuale
 *   indirizzo = ultimi 20 byte di
 *               keccak256( 0xff ++ factory(20) ++ salt(32) ++ initCodeHash(32) )
 *   salt      = keccak256( wallet_uuid | node_uuid | ambiente | versione )
 *
 * "Controfattuale" vuol dire: l'indirizzo esiste e si puo' pubblicare
 * PRIMA che il contratto sia deployato. L'utente puo' ricevere USDT su
 * quell'indirizzo oggi; il contratto lo si mette solo quando serve
 * spostare i fondi. E' la ragione per cui questo schema costa zero gas
 * per aprire un conto a 5 milioni di persone.
 *
 * SE LA FACTORY NON E' CONFIGURATA non si inventa niente: l'indirizzo
 * resta NULL, lo stato e' PENDING_FACTORY e nel record si salvano gli
 * ingressi del calcolo (salt e stringa di derivazione). Il giorno in cui
 * la factory viene deployata, eco_custody_ricalcola() riempie gli
 * indirizzi con gli stessi ingressi, e vengono uguali per costruzione.
 * Un indirizzo finto mostrato a un utente e' un deposito perso: mai.
 */
declare(strict_types=1);

require_once __DIR__ . '/eco-db.php';
require_once __DIR__ . '/../../demo/dr-keccak.php';   // keccak256() di Ethereum, gia' nel progetto

const ECO_CHAIN_POLYGON = 137;

function eco_chain_id(): int { $v = (int)eco_env('ECO_CHAIN_ID', (string)ECO_CHAIN_POLYGON); return $v > 0 ? $v : ECO_CHAIN_POLYGON; }
function eco_ambiente(): string { $v = strtolower(eco_env('ECO_ENV', 'demo')); return preg_match('/^[a-z0-9_-]{2,16}$/', $v) ? $v : 'demo'; }
function eco_addr_versione(): int { $v = (int)eco_env('ECO_ADDR_VERSION', '1'); return $v > 0 ? $v : 1; }
function eco_factory(): string { return strtolower(trim(eco_env('ECO_CREATE2_FACTORY', ''))); }
function eco_init_code_hash(): string { return strtolower(trim(eco_env('ECO_CREATE2_INIT_CODE_HASH', ''))); }

function eco_hex_ok(string $h, int $nibble): bool { return (bool)preg_match('/^0x[0-9a-f]{' . $nibble . '}$/', strtolower($h)); }

/** EIP-55: le maiuscole che fanno da checksum. Un indirizzo senza checksum e' un indirizzo che un giorno sbagli a copiare. */
function eco_eip55(string $addr): string
{
    $a = strtolower(preg_replace('/^0x/', '', $addr));
    $h = keccak256($a);
    $out = '0x';
    for ($i = 0; $i < 40; $i++) {
        $c = $a[$i];
        $out .= (ctype_alpha($c) && hexdec($h[$i]) >= 8) ? strtoupper($c) : $c;
    }
    return $out;
}

/** Il salt: deterministico, unico per wallet, legato all'ambiente e alla versione dello schema. */
function eco_custody_salt(string $walletUuid, string $nodeUuid, string $env, int $ver): array
{
    $input = $walletUuid . '|' . $nodeUuid . '|' . $env . '|v' . $ver;
    return ['input' => $input, 'salt' => '0x' . keccak256($input)];
}

/** CREATE2 puro. Ritorna l'indirizzo con checksum EIP-55, o null se un ingrediente manca. */
function eco_create2(string $factory, string $saltHex, string $initCodeHash): ?string
{
    $factory = strtolower(trim($factory)); $saltHex = strtolower(trim($saltHex)); $initCodeHash = strtolower(trim($initCodeHash));
    if (!eco_hex_ok($factory, 40) || !eco_hex_ok($saltHex, 64) || !eco_hex_ok($initCodeHash, 64)) return null;
    $bin = "\xff" . hex2bin(substr($factory, 2)) . hex2bin(substr($saltHex, 2)) . hex2bin(substr($initCodeHash, 2));
    $h = keccak256($bin);
    return eco_eip55('0x' . substr($h, 24));
}

/** Il wallet di custodia dell'utente. Uno solo, per sempre. Idempotente. */
function eco_custody_crea(int $userId): array
{
    $g = eco_uno('SELECT * FROM eco_custody_wallets WHERE user_id=?', [$userId]);
    if ($g) return $g;
    eco_esegui('INSERT INTO eco_custody_wallets (user_id,wallet_uuid,scheme,status,created_at) VALUES (?,?,?,?,?)',
        [$userId, eco_uuid(), 'CREATE2', 'ACTIVE', eco_now()]);
    return eco_uno('SELECT * FROM eco_custody_wallets WHERE user_id=?', [$userId]);
}

/**
 * L'indirizzo di deposito dell'utente su una catena. Idempotente per
 * (wallet, catena, versione di derivazione).
 */
function eco_deposit_address_assegna(int $userId, ?int $chainId = null): array
{
    $chainId ??= eco_chain_id();
    $w = eco_custody_crea($userId);
    $ver = eco_addr_versione();

    $g = eco_uno('SELECT * FROM eco_deposit_addresses WHERE wallet_id=? AND chain_id=? AND deriv_version=?', [(int)$w['wallet_id'], $chainId, $ver]);
    if ($g) return $g;

    $pos = eco_uno('SELECT genesys_node_id FROM eco_network_positions WHERE user_id=?', [$userId]);
    $nodeUuid = '';
    if ($pos) $nodeUuid = (string)eco_valore('SELECT node_uuid FROM eco_network_nodes WHERE node_id=?', [(int)$pos['genesys_node_id']], '');

    $S = eco_custody_salt((string)$w['wallet_uuid'], $nodeUuid, eco_ambiente(), $ver);
    $factory = eco_factory(); $ich = eco_init_code_hash();
    $addr = eco_create2($factory, $S['salt'], $ich);

    eco_esegui('INSERT INTO eco_deposit_addresses (wallet_id,user_id,chain_id,address,address_lc,address_status,factory_addr,salt_hex,init_code_hash,deriv_input,deriv_version,created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
        [(int)$w['wallet_id'], $userId, $chainId, $addr, $addr ? strtolower($addr) : null,
         $addr ? 'COUNTERFACTUAL' : 'PENDING_FACTORY',
         $factory !== '' ? $factory : null, $S['salt'], $ich !== '' ? $ich : null, $S['input'], $ver, eco_now()]);

    return eco_uno('SELECT * FROM eco_deposit_addresses WHERE wallet_id=? AND chain_id=? AND deriv_version=?', [(int)$w['wallet_id'], $chainId, $ver]);
}

/**
 * Il giorno in cui la factory viene deployata: si riempiono gli indirizzi
 * rimasti in PENDING_FACTORY, con gli STESSI ingressi salvati allora.
 * Non e' una rigenerazione: e' lo stesso calcolo, fatto finalmente.
 */
function eco_custody_ricalcola(int $limite = 5000): array
{
    $factory = eco_factory(); $ich = eco_init_code_hash();
    if (!eco_hex_ok($factory, 40) || !eco_hex_ok($ich, 64))
        return ['ok' => false, 'motivo' => 'ECO_CREATE2_FACTORY / ECO_CREATE2_INIT_CODE_HASH non configurati nel .env', 'aggiornati' => 0];

    $righe = eco_tutti("SELECT address_id, salt_hex FROM eco_deposit_addresses WHERE address_status='PENDING_FACTORY' ORDER BY address_id LIMIT " . (int)$limite);
    $n = 0;
    foreach ($righe as $r) {
        $a = eco_create2($factory, (string)$r['salt_hex'], $ich);
        if (!$a) continue;
        eco_esegui('UPDATE eco_deposit_addresses SET address=?, address_lc=?, address_status=?, factory_addr=?, init_code_hash=? WHERE address_id=?',
            [$a, strtolower($a), 'COUNTERFACTUAL', $factory, $ich, (int)$r['address_id']]);
        $n++;
    }
    return ['ok' => true, 'aggiornati' => $n, 'restanti' => (int)eco_valore("SELECT COUNT(*) FROM eco_deposit_addresses WHERE address_status='PENDING_FACTORY'", [], 0)];
}

/** L'indirizzo da mostrare all'utente, o la spiegazione onesta del perche' non c'e' ancora. */
function eco_deposit_address(int $userId, ?int $chainId = null): array
{
    $chainId ??= eco_chain_id();
    $r = eco_uno('SELECT d.* FROM eco_deposit_addresses d WHERE d.user_id=? AND d.chain_id=? ORDER BY d.deriv_version DESC LIMIT 1', [$userId, $chainId]);
    if (!$r) return ['pronto' => false, 'motivo' => 'nessun indirizzo assegnato', 'indirizzo' => null];
    if ($r['address_status'] === 'PENDING_FACTORY')
        return ['pronto' => false, 'motivo' => 'factory CREATE2 non ancora configurata: l indirizzo si calcola da salt ' . $r['salt_hex'] . ' quando la factory e in .env',
                'indirizzo' => null, 'salt' => $r['salt_hex'], 'deriv' => $r['deriv_input']];
    return ['pronto' => true, 'indirizzo' => (string)$r['address'], 'chain_id' => (int)$r['chain_id'], 'stato' => (string)$r['address_status']];
}
