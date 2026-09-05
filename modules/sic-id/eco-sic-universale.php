<?php
/**
 * ECO-SIC — il SIC-ID: l'identita' pubblica di una persona nell'ecosistema.
 *
 * Formato:  SIC-XXXXXXXX-XXXXXXXX-C
 *           16 caratteri di dati + 1 carattere di controllo.
 *
 * Alfabeto: Crockford Base32 (0-9 A-Z senza I, L, O, U). Nessuna coppia
 * di caratteri che si confonde a voce o su carta: "uno" e "elle" non si
 * scambiano piu', "zero" e "o" nemmeno.
 *
 * DUE COSE DETTE CHIARE, PERCHE' NON SIANO UNA SORPRESA A POSTERIORI:
 *
 * 1. NON E' DERIVATO DA DATI PERSONALI. Niente email, niente nome,
 *    niente data di nascita, niente user_id. E' sorteggio puro dal
 *    CSPRNG del sistema (random_bytes). Da un SIC-ID non si torna
 *    indietro a nessun dato della persona, perche' non c'e' niente a
 *    cui tornare.
 *
 * 2. L'ENTROPIA MOSTRATA E' 80 BIT, NON 128. Il seme e' 128 bit
 *    (random_bytes(16)), ma 16 caratteri Base32 stanno in 80 bit: e' il
 *    formato chiesto, ed e' il formato che si scrive a mano su un
 *    foglio. Cosa vuol dire in pratica: con 5 milioni di SIC-ID emessi,
 *    la probabilita' di una collisione e' circa 1 su 10^10 (nascita:
 *    n^2/2^81). Non e' zero, quindi c'e' comunque il vincolo UNIQUE nel
 *    database e un ciclo di ritentativi: se il sorteggio ripesca un ID
 *    gia' usato, si rifa'. Un formato piu' lungo (26 caratteri) darebbe
 *    128 bit veri; si e' scelto il leggibile, e si dice perche'.
 */
declare(strict_types=1);

require_once __DIR__ . '/eco-db.php';

const ECO_SIC_ALFA = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';   // Crockford: senza I L O U
const ECO_SIC_DATI = 16;                                    // caratteri di dati (80 bit)

/** Valore 0..31 di un carattere Crockford, con le confusioni gia' risolte. -1 se non e' valido. */
function eco_sic_val(string $c): int
{
    $c = strtoupper($c);
    if ($c === 'I' || $c === 'L') $c = '1';
    if ($c === 'O') $c = '0';
    $p = strpos(ECO_SIC_ALFA, $c);
    return $p === false ? -1 : $p;
}

/** Luhn mod 32: prende tutti gli errori di un carattere singolo e quasi tutte le trasposizioni. */
function eco_sic_check(string $dati): string
{
    $n = 32; $somma = 0; $f = 2;
    for ($i = strlen($dati) - 1; $i >= 0; $i--) {
        $v = eco_sic_val($dati[$i]);
        if ($v < 0) throw new InvalidArgumentException('SIC: carattere fuori alfabeto.');
        $a = $f * $v; $f = ($f === 2) ? 1 : 2;
        $somma += intdiv($a, $n) + ($a % $n);
    }
    return ECO_SIC_ALFA[($n - ($somma % $n)) % $n];
}

/** Genera un SIC-ID nuovo. Seme 128 bit, ID 80 bit + controllo. */
function eco_sic_genera(): string
{
    $seme = random_bytes(16);                       // 128 bit di CSPRNG
    $b = hash('sha256', $seme, true);               // si mescola, poi si prendono 80 bit
    $bits = '';
    for ($i = 0; $i < 10; $i++) $bits .= str_pad(decbin(ord($b[$i])), 8, '0', STR_PAD_LEFT);
    $dati = '';
    for ($i = 0; $i < ECO_SIC_DATI; $i++) $dati .= ECO_SIC_ALFA[bindec(substr($bits, $i * 5, 5))];
    return 'SIC-' . substr($dati, 0, 8) . '-' . substr($dati, 8, 8) . '-' . eco_sic_check($dati);
}

/**
 * Normalizza: maiuscolo, via prefisso e trattini, I/L -> 1, O -> 0.
 * Ritorna i 17 caratteri (16 dati + controllo) o '' se la forma non regge.
 */
function eco_sic_normalizza(string $s): string
{
    $s = strtoupper(trim($s));
    $s = preg_replace('/^SIC[-_ ]?/', '', $s);
    $s = preg_replace('/[^0-9A-Z]/', '', (string)$s);
    $s = strtr((string)$s, ['I' => '1', 'L' => '1', 'O' => '0']);
    if (strlen($s) !== ECO_SIC_DATI + 1) return '';
    for ($i = 0; $i < strlen($s); $i++) if (eco_sic_val($s[$i]) < 0) return '';
    return $s;
}

/** Vero se la forma e il carattere di controllo tornano. Non dice se esiste: dice se e' scritto bene. */
function eco_sic_valida(string $s): bool
{
    $n = eco_sic_normalizza($s);
    if ($n === '') return false;
    return eco_sic_check(substr($n, 0, ECO_SIC_DATI)) === $n[ECO_SIC_DATI];
}

/** Forma canonica da mostrare: SIC-XXXXXXXX-XXXXXXXX-C. '' se non e' valido. */
function eco_sic_formatta(string $s): string
{
    $n = eco_sic_normalizza($s);
    if ($n === '') return '';
    return 'SIC-' . substr($n, 0, 8) . '-' . substr($n, 8, 8) . '-' . $n[16];
}

/** Chiave di ricerca: sha256 della forma normalizzata. Indicizzata, uguale su ogni motore. */
function eco_sic_hash(string $s): string
{
    $n = eco_sic_normalizza($s);
    if ($n === '') throw new InvalidArgumentException('SIC: formato non valido.');
    return hash('sha256', 'SIC1|' . $n);
}

/**
 * Emette il SIC-ID di un utente. Idempotente: se ce l'ha gia', torna quello.
 * Va chiamata dentro una transazione (eco_onboard lo fa).
 */
function eco_sic_emetti(int $userId): array
{
    $g = eco_uno('SELECT sic_id, sic_norm, sic_hash, issued_at FROM eco_sic_identities WHERE user_id=?', [$userId]);
    if ($g) return ['sic_id' => (string)$g['sic_id'], 'nuovo' => false];

    for ($t = 0; $t < 8; $t++) {
        $sic  = eco_sic_genera();
        $norm = eco_sic_normalizza($sic);
        $hash = eco_sic_hash($sic);
        try {
            eco_esegui('INSERT INTO eco_sic_identities (user_id,sic_id,sic_norm,sic_hash,version,issued_at) VALUES (?,?,?,?,1,?)',
                [$userId, $sic, $norm, $hash, eco_now()]);
            return ['sic_id' => $sic, 'nuovo' => true];
        } catch (PDOException $e) {
            // collisione sull'UNIQUE: si ripesca. Con 80 bit succede circa mai, ma "circa" non basta.
            $m = strtolower($e->getMessage());
            if (!str_contains($m, 'unique') && !str_contains($m, 'duplicate')) throw $e;
        }
    }
    throw new RuntimeException('SIC: 8 collisioni di fila. Qualcosa non va nel generatore, non nella fortuna.');
}

/** Cerca l'utente da un SIC-ID scritto comunque (con o senza trattini, minuscolo, con I al posto di 1). */
function eco_sic_trova(string $s): ?array
{
    if (!eco_sic_valida($s)) return null;
    return eco_uno('SELECT s.*, u.username, u.user_id FROM eco_sic_identities s JOIN eco_users u ON u.user_id=s.user_id WHERE s.sic_hash=?', [eco_sic_hash($s)]);
}
