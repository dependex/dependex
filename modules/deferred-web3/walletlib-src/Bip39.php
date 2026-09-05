<?php
/* ============================================================================
   DrWallet\Bip39 — BIP39 VERO (Destino Randagio, 2026-08-01)

   - Wordlist inglese UFFICIALE completa (2048 parole, sha256 del file =
     2f5eed53a4727b4bf8880d8f3f199efc90e58503646d9ff8eff3a2ed3b24dbda,
     identico a bitcoin/bips/bip-0039/english.txt).
   - Entropy 128 bit da random_bytes() (CSPRNG del sistema, mai mt_rand).
   - Checksum: primi ENT/32 bit di SHA-256(entropy), come da spec.
   - mnemonic -> seed: PBKDF2-HMAC-SHA512, 2048 iterazioni, salt "mnemonic".

   NIENTE pseudo-codice: ogni passo e' la spec BIP39, provata contro i vettori
   ufficiali di Trezor (vedi test/test-vettori.php).
============================================================================ */
namespace DrWallet;

final class Bip39
{
    /** @var array<int,string>|null */
    private static ?array $words = null;
    /** @var array<string,int>|null */
    private static ?array $index = null;

    private const WORDLIST_SHA256 = '2f5eed53a4727b4bf8880d8f3f199efc90e58503646d9ff8eff3a2ed3b24dbda';

    /** Carica (una volta) la wordlist ufficiale e ne verifica l'hash. */
    private static function wordlist(): array
    {
        if (self::$words !== null) return self::$words;
        $file = __DIR__ . '/wordlist-english.txt';
        $raw  = @file_get_contents($file);
        if ($raw === false) {
            throw new \RuntimeException('BIP39: wordlist-english.txt mancante in walletlib/src');
        }
        if (hash('sha256', $raw) !== self::WORDLIST_SHA256) {
            throw new \RuntimeException('BIP39: wordlist corrotta (sha256 diverso da quella ufficiale)');
        }
        $w = preg_split('/\r?\n/', trim($raw));
        if (count($w) !== 2048) {
            throw new \RuntimeException('BIP39: la wordlist deve avere 2048 parole, trovate ' . count($w));
        }
        self::$words = $w;
        self::$index = array_flip($w);
        return $w;
    }

    /** Genera un mnemonic nuovo. $bits: 128 = 12 parole (default), 256 = 24. */
    public static function generate(int $bits = 128): string
    {
        if (!in_array($bits, [128, 160, 192, 224, 256], true)) {
            throw new \InvalidArgumentException('BIP39: entropy ammessa 128..256 bit');
        }
        return self::entropyToMnemonic(random_bytes(intdiv($bits, 8)));
    }

    /** Entropy binaria -> mnemonic (con checksum SHA-256 corretto). */
    public static function entropyToMnemonic(string $entropy): string
    {
        $ent = strlen($entropy) * 8;
        if (!in_array($ent, [128, 160, 192, 224, 256], true)) {
            throw new \InvalidArgumentException('BIP39: entropy di lunghezza non valida');
        }
        $csBits = intdiv($ent, 32);
        $hash   = hash('sha256', $entropy, true);

        /* bitstring = entropy || primi csBits del sha256 */
        $bits = '';
        foreach (str_split($entropy) as $c) $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        $hbits = str_pad(decbin(ord($hash[0])), 8, '0', STR_PAD_LEFT);
        if ($csBits > 8) { /* entropy 256 -> 8 bit dal primo byte non bastano mai qui, ma per completezza */
            foreach (str_split(substr($hash, 1)) as $c) $hbits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $bits .= substr($hbits, 0, $csBits);

        $words = self::wordlist();
        $out = [];
        foreach (str_split($bits, 11) as $chunk) {
            $out[] = $words[bindec($chunk)];
        }
        return implode(' ', $out);
    }

    /** Verifica parole + checksum. Ritorna true/false, mai eccezioni. */
    public static function validate(string $mnemonic): bool
    {
        try {
            self::mnemonicToEntropy($mnemonic);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** mnemonic -> entropy binaria (lancia se parola ignota o checksum errato). */
    public static function mnemonicToEntropy(string $mnemonic): string
    {
        self::wordlist();
        $parts = preg_split('/\s+/', strtolower(trim($mnemonic)));
        $n = count($parts);
        if (!in_array($n, [12, 15, 18, 21, 24], true)) {
            throw new \InvalidArgumentException('BIP39: numero di parole non valido (' . $n . ')');
        }
        $bits = '';
        foreach ($parts as $w) {
            if (!isset(self::$index[$w])) throw new \InvalidArgumentException('BIP39: parola fuori wordlist');
            $bits .= str_pad(decbin(self::$index[$w]), 11, '0', STR_PAD_LEFT);
        }
        $entBits = intdiv(strlen($bits) * 32, 33);
        $csBits  = strlen($bits) - $entBits;
        $entropy = '';
        foreach (str_split(substr($bits, 0, $entBits), 8) as $byte) {
            $entropy .= chr(bindec($byte));
        }
        $hash  = hash('sha256', $entropy, true);
        $hbits = '';
        for ($i = 0; $i < intdiv($csBits + 7, 8); $i++) {
            $hbits .= str_pad(decbin(ord($hash[$i])), 8, '0', STR_PAD_LEFT);
        }
        if (substr($bits, $entBits) !== substr($hbits, 0, $csBits)) {
            throw new \InvalidArgumentException('BIP39: checksum errato');
        }
        return $entropy;
    }

    /** mnemonic -> seed 64 byte (PBKDF2-HMAC-SHA512, 2048 iter, salt "mnemonic"+pass). */
    public static function toSeed(string $mnemonic, string $passphrase = ''): string
    {
        $m = self::nfkd(trim($mnemonic));
        $p = self::nfkd($passphrase);
        return hash_pbkdf2('sha512', $m, 'mnemonic' . $p, 2048, 64, true);
    }

    /** NFKD come da spec; per l'inglese (solo ASCII) e' un no-op, ma se c'e' intl la usiamo. */
    private static function nfkd(string $s): string
    {
        if (class_exists('\Normalizer')) {
            $n = \Normalizer::normalize($s, \Normalizer::FORM_KD);
            if ($n !== false) return $n;
        }
        return $s;
    }
}
