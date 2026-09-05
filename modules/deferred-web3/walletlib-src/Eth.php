<?php
/* ============================================================================
   DrWallet\Eth — indirizzo Ethereum/Polygon VERO da chiave privata
   (Destino Randagio, 2026-08-01)

   - pubkey non compressa via secp256k1 (simplito/elliptic-php)
   - address = ultimi 20 byte di keccak256(pubkey senza il prefisso 04)
     (kornrunner/keccak = Keccak-256 "legacy" di Ethereum, NON sha3-256 FIPS)
   - EIP-55: checksum mixed-case dal keccak256 dell'indirizzo minuscolo.
============================================================================ */
namespace DrWallet;

use Elliptic\EC;
use kornrunner\Keccak;

final class Eth
{
    private static ?EC $ec = null;

    private static function ec(): EC
    {
        if (self::$ec === null) self::$ec = new EC('secp256k1');
        return self::$ec;
    }

    /** Chiave privata hex (64 char) -> pubkey NON compressa hex (130 char, con 04). */
    public static function publicKey(string $privHex): string
    {
        $privHex = strtolower(preg_replace('/^0x/i', '', trim($privHex)));
        if (!preg_match('/^[0-9a-f]{64}$/', $privHex)) {
            throw new \InvalidArgumentException('Eth: chiave privata non valida (attesi 64 hex)');
        }
        $kp = self::ec()->keyFromPrivate($privHex, 'hex');
        $pub = $kp->getPublic(false, 'hex');     // 04 + X + Y
        if (strlen($pub) !== 130 || substr($pub, 0, 2) !== '04') {
            throw new \RuntimeException('Eth: pubkey inattesa');
        }
        return $pub;
    }

    /** Chiave privata hex -> indirizzo EIP-55 (0x...). */
    public static function addressFromPriv(string $privHex): string
    {
        $pub  = self::publicKey($privHex);
        $hash = Keccak::hash(hex2bin(substr($pub, 2)), 256); // keccak256(X||Y)
        return self::toChecksum('0x' . substr($hash, -40));
    }

    /** EIP-55 checksum di un indirizzo qualsiasi. */
    public static function toChecksum(string $address): string
    {
        $addr = strtolower(preg_replace('/^0x/i', '', trim($address)));
        if (!preg_match('/^[0-9a-f]{40}$/', $addr)) {
            throw new \InvalidArgumentException('Eth: indirizzo non valido');
        }
        $hash = Keccak::hash($addr, 256);
        $out  = '';
        for ($i = 0; $i < 40; $i++) {
            $c = $addr[$i];
            $out .= (ctype_alpha($c) && intval($hash[$i], 16) >= 8) ? strtoupper($c) : $c;
        }
        return '0x' . $out;
    }

    /** true se l'indirizzo ha forma valida (checksum NON verificato). */
    public static function isAddress(string $address): bool
    {
        return (bool)preg_match('/^0x[0-9a-fA-F]{40}$/', trim($address));
    }

    /** Comodo: mnemonic -> [privHex, address] sul path MetaMask m/44'/60'/0'/0/0. */
    public static function walletFromMnemonic(string $mnemonic, string $path = "m/44'/60'/0'/0/0"): array
    {
        if (!Bip39::validate($mnemonic)) {
            throw new \InvalidArgumentException('Eth: mnemonic BIP39 non valido');
        }
        $seed = Bip39::toSeed($mnemonic);
        $priv = Bip32::derivePath($seed, $path);
        return ['priv' => $priv, 'address' => self::addressFromPriv($priv)];
    }
}
