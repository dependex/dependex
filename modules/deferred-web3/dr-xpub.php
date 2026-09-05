<?php
/**
 * DR-XPUB — l'indirizzo di deposito di OGNI account, calcolato dalla chiave
 * pubblica estesa del ramo (m/44'/966'/0'/0), senza nessuna chiave privata.
 * DAO BRANCH · 16 agosto 2026
 *
 * Perche' esiste: se tutti versano sullo stesso indirizzo, il server deve
 * indovinare di chi e' ogni bonifico. Se ogni account ha il SUO indirizzo
 * (derivato dalla sua posizione), il deposito arriva gia' etichettato: e'
 * il wallet DI QUELL'ACCOUNT, la stessa matematica di dr-derivazione.js.
 *
 * Sul server sta SOLO l'xpub (catena + pubblica compressa, in hex) nel .env:
 *   DR_XPUB_CATENA=…64 hex…   DR_XPUB_PUB=…66 hex…
 * Con l'xpub si calcolano indirizzi, non si firma niente. Serve gmp (host: ok).
 */
declare(strict_types=1);
require_once __DIR__ . '/dr-firma.php';   // curva, keccak, conversioni

/** Punto dalla pubblica compressa (02/03 + x). */
function xpub_decomprimi(string $hex): ?array
{
    $hex = strtolower($hex);
    if (strlen($hex) !== 66) return null;
    $pref = substr($hex, 0, 2); $x = fir_hex2dec(substr($hex, 2));
    $p = fir_P();
    $y2 = fir_mod(bigi_add(fir_mulmod($x, fir_mulmod($x, $x, $p), $p), '7'), $p);
    $y = fir_pow($y2, bigi_div(bigi_add($p, '1'), '4'), $p);
    if (bigi_cmp(fir_mulmod($y, $y, $p), $y2) !== 0) return null;
    $dispari = bigi_cmp(fir_mod($y, '2'), '1') === 0;
    if (($pref === '03') !== $dispari) $y = bigi_sub($p, $y);
    return [$x, $y];
}
function xpub_comprimi(array $P): string
{
    $pari = bigi_cmp(fir_mod($P[1], '2'), '0') === 0;
    return ($pari ? '02' : '03') . fir_dec2hex($P[0]);
}
/** Figlio NON rafforzato: K_i = K + IL·G, con IL = HMAC-SHA512(catena, K||i)[0..32]. */
function xpub_figlio(string $catenaHex, string $pubHex, int $i): ?array
{
    if ($i < 0 || $i >= 0x80000000) return null;
    $dati = hex2bin($pubHex) . pack('N', $i);
    $I = hash_hmac('sha512', $dati, hex2bin($catenaHex), true);
    $IL = fir_hex2dec(bin2hex(substr($I, 0, 32)));
    if (bigi_cmp($IL, fir_N()) >= 0) return null;
    $K = xpub_decomprimi($pubHex); if (!$K) return null;
    $Ki = fir_somma(fir_molt($IL, [fir_Gx(), fir_Gy()]), $K);
    return $Ki ? ['pub' => xpub_comprimi($Ki), 'catena' => bin2hex(substr($I, 32)), 'punto' => $Ki] : null;
}
/** L'indirizzo EVM di un punto. */
function xpub_indirizzo_punto(array $P): string
{
    $pub = fir_dec2hex($P[0]) . fir_dec2hex($P[1]);
    return '0x' . substr(keccak256(hex2bin($pub)), -40);
}
/** L'indirizzo di deposito della posizione $i. Null se l'xpub non c'e' o gmp manca. */
function xpub_indirizzo(int $i, ?string $catena = null, ?string $pub = null): ?string
{
    $catena ??= function_exists('demo_env') ? demo_env('DR_XPUB_CATENA', '') : '';
    $pub    ??= function_exists('demo_env') ? demo_env('DR_XPUB_PUB', '') : '';
    if (!fir_pronta() || strlen($catena) !== 64 || strlen($pub) !== 66) return null;
    $f = xpub_figlio($catena, $pub, $i);
    return $f ? xpub_indirizzo_punto($f['punto']) : null;
}
/** EIP-55 checksum, cosi' l'indirizzo si copia con le maiuscole giuste. */
function xpub_checksum(string $addr): string
{
    $a = strtolower(substr($addr, 2)); $h = keccak256($a); $o = '0x';
    for ($i = 0; $i < 40; $i++) $o .= (hexdec($h[$i]) >= 8) ? strtoupper($a[$i]) : $a[$i];
    return $o;
}
