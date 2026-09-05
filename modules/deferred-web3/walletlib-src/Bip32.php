<?php
/* ============================================================================
   DrWallet\Bip32 — derivazione BIP32/BIP44 VERA su secp256k1
   (Destino Randagio, 2026-08-01)

   - Master key: HMAC-SHA512(key="Bitcoin seed", seed) -> IL=chiave, IR=chain code.
   - CKDpriv hardened:  I = HMAC-SHA512(cc, 0x00 || kpar || ser32(i))
   - CKDpriv normale:   I = HMAC-SHA512(cc, serP(point(kpar)) || ser32(i))
   - child = (parse256(IL) + kpar) mod n, con i controlli della spec
     (IL >= n o child == 0 -> indice successivo; qui: eccezione, caso ~2^-127).
   - Path di MetaMask per Ethereum/Polygon: m/44'/60'/0'/0/0.

   La matematica di curva (point multiply secp256k1) e' di simplito/elliptic-php,
   libreria reale pure-PHP (usa GMP/bcmath via bigint-wrapper). Nessuna curva
   "fatta in casa".
============================================================================ */
namespace DrWallet;

use Elliptic\EC;

final class Bip32
{
    private const CURVE_N = 'fffffffffffffffffffffffffffffffebaaedce6af48a03bbfd25e8cd0364141';

    private static ?EC $ec = null;

    private static function ec(): EC
    {
        if (self::$ec === null) self::$ec = new EC('secp256k1');
        return self::$ec;
    }

    /** @return array{key:string,chain:string} chiave privata e chain code, binari 32 byte */
    public static function masterFromSeed(string $seed): array
    {
        if (strlen($seed) < 16 || strlen($seed) > 64) {
            throw new \InvalidArgumentException('BIP32: seed fuori range (16..64 byte)');
        }
        $I = hash_hmac('sha512', $seed, 'Bitcoin seed', true);
        $key = substr($I, 0, 32);
        $cc  = substr($I, 32);
        self::assertValidKey($key);
        return ['key' => $key, 'chain' => $cc];
    }

    /** Un passo di CKDpriv. $index >= 0x80000000 = hardened. */
    public static function ckdPriv(string $key, string $chain, int $index): array
    {
        $ser32 = pack('N', $index);
        if ($index >= 0x80000000) {
            $data = "\x00" . $key . $ser32;                    // hardened
        } else {
            $data = self::compressedPub($key) . $ser32;        // normale
        }
        $I  = hash_hmac('sha512', $data, $chain, true);
        $IL = substr($I, 0, 32);
        $cc = substr($I, 32);

        $n     = gmp_init(self::CURVE_N, 16);
        $ilNum = gmp_init(bin2hex($IL), 16);
        if (gmp_cmp($ilNum, $n) >= 0) {
            throw new \RuntimeException('BIP32: IL >= n (probabilita ~2^-127), rigenerare');
        }
        $child = gmp_mod(gmp_add($ilNum, gmp_init(bin2hex($key), 16)), $n);
        if (gmp_cmp($child, 0) === 0) {
            throw new \RuntimeException('BIP32: chiave figlia nulla, rigenerare');
        }
        $ck = hex2bin(str_pad(gmp_strval($child, 16), 64, '0', STR_PAD_LEFT));
        return ['key' => $ck, 'chain' => $cc];
    }

    /**
     * Deriva dal seed lungo un path ("m/44'/60'/0'/0/0").
     * Ritorna la chiave privata in hex (64 caratteri, senza 0x).
     */
    public static function derivePath(string $seed, string $path = "m/44'/60'/0'/0/0"): string
    {
        if (!preg_match("~^m(/\d+'?)*$~", $path)) {
            throw new \InvalidArgumentException('BIP32: path non valido: ' . $path);
        }
        $node = self::masterFromSeed($seed);
        $segs = explode('/', $path);
        array_shift($segs); // "m"
        foreach ($segs as $s) {
            $hardened = substr($s, -1) === "'";
            $i = (int)rtrim($s, "'");
            if ($hardened) $i += 0x80000000;
            $node = self::ckdPriv($node['key'], $node['chain'], $i);
        }
        return bin2hex($node['key']);
    }

    /** serP(point(k)): pubkey compressa 33 byte, per il CKD normale. */
    public static function compressedPub(string $privBin): string
    {
        $kp  = self::ec()->keyFromPrivate(bin2hex($privBin), 'hex');
        $hex = $kp->getPublic(true, 'hex');   // compressa: 02/03 + X
        return hex2bin($hex);
    }

    private static function assertValidKey(string $key): void
    {
        $n = gmp_init(self::CURVE_N, 16);
        $k = gmp_init(bin2hex($key), 16);
        if (gmp_cmp($k, 0) === 0 || gmp_cmp($k, $n) >= 0) {
            throw new \RuntimeException('BIP32: master key fuori dal gruppo (rigenerare il seed)');
        }
    }
}
