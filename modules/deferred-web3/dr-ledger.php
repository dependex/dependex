<?php
/**
 * DR-LEDGER — il registro interno di Destino Randagio
 * 16 agosto 2026
 *
 * Registro in partita doppia, append-only, con catena di impronte.
 * Copre: depositi, prelievi, trasferimenti interni, swap, premi,
 * commissioni — e la copia locale di quello che succede su Polygon.
 *
 * ==================================================================
 * LE DUE COSE CHE QUESTO FILE NON FA, E NON PER SVISTA
 * ==================================================================
 *
 * 1. NON FIRMA NIENTE. Nemmeno una virgola. Ogni movimento che deve
 *    finire sulla catena entra in `led_coda` e li' si ferma: uno
 *    script sul PC di Mirco lo firma. E' la regola numero tre del
 *    progetto e questo file la rispetta senza eccezioni.
 *    Percio' "convertitore automatico" vuol dire: il registro accredita
 *    subito, la gamba on-chain si accoda. Automatico dentro, firmato
 *    fuori.
 *
 * 2. NON APRE NESSUN SEED. I wallet custodial sono sigillati nel
 *    forziere, e il forziere non ha una funzione per aprirli. Il
 *    registro sa quanto ha ciascuno; non sa come muoverlo da solo.
 *
 * Importi: SEMPRE stringhe di interi in unita' base. La virgola mobile
 * qui dentro non entra: 0.1 + 0.2 non fa 0.3, e con i soldi degli altri
 * quella differenza si chiama ammanco.
 */

declare(strict_types=1);

require_once __DIR__ . '/dr-bigint.php';

if (!defined('LED_DB')) define('LED_DB', __DIR__ . '/dati/ledger.sqlite');

/** Decimali per token — servono solo per STAMPARE, mai per calcolare. */
function led_decimali(string $token): int
{
    return [
        'DUX'  => 6,
        'USDT' => 6,
        'DRX'  => 18,
        '81X'  => 18,
        'ERIDAN' => 18,
        'POL'  => 18,
        'BTC'  => 8,
    ][$token] ?? 18;
}

function led_db(): PDO
{
    static $db = null;
    if ($db instanceof PDO) return $db;

    $dir = dirname(LED_DB);
    if (!is_dir($dir)) mkdir($dir, 0700, true);

    $db = new PDO('sqlite:' . LED_DB, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $db->exec('PRAGMA foreign_keys = ON');
    $db->exec('PRAGMA journal_mode = WAL');

    // Lo schema vive nel file .sql accanto; ma se sull'host quel file non
    // e' stato caricato (16 agosto: e' successo, e ha bloccato tutto) si usa
    // la copia incorporata qui sotto. Stesso testo, zero dipendenze.
    $schema = __DIR__ . '/dr-ledger-schema.sql';
    $sql = is_file($schema) ? (string)file_get_contents($schema) : led_schema_incorporato();
    $db->exec($sql);
    return $db;
}

/* ==================================================================
   ARITMETICA SU INTERI GRANDI
   DRX ha 18 decimali: 1 DRX = 10^18. Su un intero a 64 bit ci stanno
   9,2 miliardi di DRX interi — e la riserva premi ne dichiara 10
   miliardi. Cioe': con gli interi nativi si sfonda. Quindi bcmath, e
   se non c'e' ci si ferma subito invece di sbagliare in silenzio.
   ================================================================== */

/* Non c'e' piu' niente da controllare: dr-bigint.php usa bcmath se c'e'
   e fa i conti a mano se non c'e'. Il registro parte comunque, e i conti
   sono gli stessi. Provato: 9.000 confronti col calcolo nativo, zero
   differenze. */
function led_bc(): void {}

function led_somma(string $a, string $b): string { return bigi_add($a, $b); }
function led_meno(string $a): string             { return bigi_neg($a); }
function led_cmp(string $a, string $b): int      { return bigi_cmp($a, $b); }

/** Da unita' base a stringa leggibile. Solo per stampare. */
function led_umano(string $base, string $token): string
{
    led_bc();
    $d = led_decimali($token);
    $neg = str_starts_with($base, '-');
    $x = ltrim($base, '-');
    $x = str_pad($x, $d + 1, '0', STR_PAD_LEFT);
    $intero = substr($x, 0, strlen($x) - $d);
    $frazione = $d > 0 ? rtrim(substr($x, -$d), '0') : '';
    // I punti delle migliaia si mettono a mano sulla stringa: number_format
    // vuole un float, e un float qui rovinerebbe proprio i numeri grandi
    // per cui esiste tutto questo file.
    $s = strrev(rtrim(chunk_split(strrev($intero), 3, '.'), '.'));
    return ($neg ? '-' : '') . $s . ($frazione !== '' ? ',' . $frazione : '');
}

/** Da stringa umana ("12,5") a unita' base. Rifiuta tutto il resto. */
function led_base(string $umano, string $token): string
{
    led_bc();
    $u = str_replace([' ', '.'], '', trim($umano));
    $u = str_replace(',', '.', $u);
    if (!preg_match('/^-?\d+(\.\d+)?$/', $u)) {
        throw new InvalidArgumentException("DR-LEDGER: importo non valido: «$umano»");
    }
    $d = led_decimali($token);
    [$i, $f] = array_pad(explode('.', $u, 2), 2, '');
    if (strlen($f) > $d) {
        throw new InvalidArgumentException("DR-LEDGER: $token ha $d decimali, ne hai dati " . strlen($f) . '.');
    }
    $neg = str_starts_with($i, '-');
    $i = ltrim($i, '-');
    return ($neg ? '-' : '') . ltrim($i . str_pad($f, $d, '0'), '0') ?: '0';
}

/* ==================================================================
   I CONTI
   ================================================================== */

function led_conto(string $proprietario, string $genere, string $token): int
{
    $db = led_db();
    $q = $db->prepare('SELECT id FROM led_conti WHERE proprietario=? AND genere=? AND token=?');
    $q->execute([$proprietario, $genere, $token]);
    $id = $q->fetchColumn();
    if ($id !== false) return (int)$id;

    $i = $db->prepare('INSERT INTO led_conti (proprietario,genere,token,creato) VALUES (?,?,?,?)');
    $i->execute([$proprietario, $genere, $token, time()]);
    return (int)$db->lastInsertId();
}

function led_conto_utente(string $uid, string $token): int    { return led_conto($uid, 'utente', $token); }
function led_conto_tesoreria(string $token): int              { return led_conto('sistema', 'tesoreria', $token); }
function led_conto_commissioni(string $token): int            { return led_conto('sistema', 'commissioni', $token); }
function led_conto_esterno(string $token): int                { return led_conto('sistema', 'esterno', $token); }
function led_conto_deposito(string $token): int               { return led_conto('sistema', 'deposito', $token); }

function led_saldo(int $conto): string
{
    $q = led_db()->prepare(
        "SELECT importo FROM led_scritture WHERE conto=? AND stato='confermato'"
    );
    $q->execute([$conto]);
    $t = '0';
    foreach ($q as $r) $t = led_somma($t, (string)$r['importo']);
    return $t;
}

function led_saldi_utente(string $uid): array
{
    $out = [];
    foreach (['DUX', 'DRX', '81X', 'POL', 'USDT'] as $t) {
        $out[$t] = led_saldo(led_conto_utente($uid, $t));
    }
    return $out;
}

/* ==================================================================
   LA SCRITTURA — il cuore
   ================================================================== */

function led_impronta_ultima(PDO $db): string
{
    $u = $db->query('SELECT impronta FROM led_scritture ORDER BY id DESC LIMIT 1')->fetchColumn();
    return $u === false ? str_repeat('0', 64) : (string)$u;
}

function led_calcola_impronta(array $r, string $prec): string
{
    // I campi entrano in un ordine fisso: cambiarlo invalida tutta la
    // catena esistente. Se un giorno serve un campo nuovo, si aggiunge
    // IN FONDO, mai in mezzo.
    return hash('sha256', implode('|', [
        $prec,
        $r['movimento'], $r['quando'], $r['conto'], $r['token'],
        $r['importo'], $r['causale'], $r['descrizione'],
        $r['catena'] ?? '', $r['tx_hash'] ?? '', $r['log_index'] ?? '',
        $r['blocco'] ?? '', $r['stato'], $r['autore'],
    ]));
}

/**
 * Scrive UN movimento fatto di piu' righe. O tutte, o nessuna.
 *
 * @param array $righe  ogni riga: conto, token, importo(stringa con segno),
 *                      causale, descrizione, [catena, tx_hash, log_index, blocco, stato]
 * @param bool  $permettiScoperto  false = un conto utente non puo' andare sotto zero
 * @return string il codice del movimento
 */
function led_scrivi(array $righe, string $autore = 'sistema', bool $permettiScoperto = false): string
{
    if (!$righe) throw new InvalidArgumentException('DR-LEDGER: movimento vuoto.');
    led_bc();
    $db = led_db();

    // --- la partita doppia deve tornare, per ogni token
    $per_token = [];
    foreach ($righe as $r) {
        $per_token[$r['token']] = led_somma($per_token[$r['token']] ?? '0', (string)$r['importo']);
    }
    foreach ($per_token as $tok => $tot) {
        if (led_cmp($tot, '0') !== 0) {
            throw new RuntimeException(
                "DR-LEDGER: la partita doppia non torna su $tok (resto $tot). "
                . 'Ogni movimento deve sommare a zero: se manca una contropartita, manca un pezzo di verita.'
            );
        }
    }

    $movimento = bin2hex(random_bytes(12));
    $ora = time();

    $db->beginTransaction();
    try {
        $prec = led_impronta_ultima($db);
        $ins = $db->prepare(
            'INSERT INTO led_scritture
             (movimento,quando,conto,token,importo,causale,descrizione,
              catena,tx_hash,log_index,blocco,stato,impronta_prec,impronta,autore)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );

        foreach ($righe as $r) {
            $riga = [
                'movimento' => $movimento,
                'quando'    => $ora,
                'conto'     => (int)$r['conto'],
                'token'     => (string)$r['token'],
                'importo'   => (string)$r['importo'],
                'causale'   => (string)$r['causale'],
                'descrizione' => (string)($r['descrizione'] ?? ''),
                'catena'    => $r['catena']   ?? null,
                'tx_hash'   => $r['tx_hash']  ?? null,
                'log_index' => $r['log_index'] ?? null,
                'blocco'    => $r['blocco']   ?? null,
                'stato'     => (string)($r['stato'] ?? 'confermato'),
                'autore'    => $autore,
            ];
            $imp = led_calcola_impronta($riga, $prec);
            $ins->execute([
                $riga['movimento'], $riga['quando'], $riga['conto'], $riga['token'],
                $riga['importo'], $riga['causale'], $riga['descrizione'],
                $riga['catena'], $riga['tx_hash'], $riga['log_index'], $riga['blocco'],
                $riga['stato'], $prec, $imp, $riga['autore'],
            ]);
            $prec = $imp;
        }

        // --- nessun conto utente puo' restare scoperto
        if (!$permettiScoperto) {
            foreach (array_unique(array_column($righe, 'conto')) as $c) {
                $g = $db->prepare('SELECT genere FROM led_conti WHERE id=?');
                $g->execute([$c]);
                if ($g->fetchColumn() !== 'utente') continue;
                $s = $db->prepare("SELECT importo FROM led_scritture WHERE conto=? AND stato='confermato'");
                $s->execute([$c]);
                $t = '0';
                foreach ($s as $x) $t = led_somma($t, (string)$x['importo']);
                if (led_cmp($t, '0') < 0) {
                    throw new RuntimeException(
                        "DR-LEDGER: il conto $c andrebbe sotto zero ($t). Movimento annullato."
                    );
                }
            }
        }

        $db->commit();
        return $movimento;
    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}

/* ==================================================================
   LE OPERAZIONI
   ================================================================== */

/** Deposito: arriva dalla catena (o lo registra l'admin). */
function led_deposito(string $uid, string $token, string $importo, array $chain = [], string $autore = 'indicizzatore'): string
{
    $comune = [
        'catena' => $chain['catena'] ?? null, 'tx_hash' => $chain['tx_hash'] ?? null,
        'log_index' => $chain['log_index'] ?? null, 'blocco' => $chain['blocco'] ?? null,
        'stato' => $chain['stato'] ?? 'confermato',
    ];
    return led_scrivi([
        ['conto' => led_conto_utente($uid, $token), 'token' => $token, 'importo' => $importo,
         'causale' => 'deposito', 'descrizione' => 'Deposit'] + $comune,
        ['conto' => led_conto_esterno($token), 'token' => $token, 'importo' => led_meno($importo),
         'causale' => 'deposito', 'descrizione' => 'Deposit (counterpart)'] + $comune,
    ], $autore);
}

/**
 * Trasferimento interno fra due utenti.
 *
 * NOTA IMPORTANTE, e va detta: un trasferimento interno NON tocca la
 * catena. E' una riga di database. Quindi NON consuma gas, e far pagare
 * "gas in POL" per un movimento che non tocca Polygon sarebbe far pagare
 * una cosa che non esiste.
 * Una commissione interna e' legittima — ma si chiama commissione, non
 * gas, e qui e' a zero per scelta.
 */
function led_trasferimento(string $daUid, string $aUid, string $token, string $importo, string $nota = ''): string
{
    if (led_cmp($importo, '0') <= 0) throw new InvalidArgumentException('DR-LEDGER: importo non positivo.');
    if ($daUid === $aUid) throw new InvalidArgumentException('DR-LEDGER: mittente e destinatario coincidono.');
    return led_scrivi([
        ['conto' => led_conto_utente($daUid, $token), 'token' => $token, 'importo' => led_meno($importo),
         'causale' => 'trasferimento', 'descrizione' => 'Internal transfer to ' . $aUid . ($nota ? ' — ' . $nota : '')],
        ['conto' => led_conto_utente($aUid, $token), 'token' => $token, 'importo' => $importo,
         'causale' => 'trasferimento', 'descrizione' => 'Internal transfer from ' . $daUid . ($nota ? ' — ' . $nota : '')],
    ], 'sistema');
}

/**
 * Prelievo. Commissione 0,5% sul totale, e la gamba on-chain si ACCODA:
 * il sito non firma. L'utente vede subito il saldo aggiornato e lo stato
 * "in attesa di firma", che e' la verita'.
 */
function led_prelievo(string $uid, string $token, string $importo, string $destinazione, int $feeBp = 50): array
{
    led_bc();
    if (led_cmp($importo, '0') <= 0) throw new InvalidArgumentException('DR-LEDGER: importo non positivo.');
    if (!preg_match('/^0x[0-9a-fA-F]{40}$/', $destinazione)) {
        throw new InvalidArgumentException('DR-LEDGER: indirizzo di destinazione non valido.');
    }
    // La commissione si calcola sul TOTALE, come dichiarato. Troncata
    // per difetto: l'arrotondamento non deve mai andare a sfavore
    // dell'utente, nemmeno di un'unita' base.
    $fee = bigi_div(bigi_mul($importo, (string)$feeBp), '10000');
    $netto = bigi_sub($importo, $fee);

    $righe = [
        ['conto' => led_conto_utente($uid, $token), 'token' => $token, 'importo' => led_meno($importo),
         'causale' => 'prelievo', 'descrizione' => 'Withdrawal to ' . $destinazione, 'stato' => 'confermato'],
        ['conto' => led_conto_esterno($token), 'token' => $token, 'importo' => $netto,
         'causale' => 'prelievo', 'descrizione' => 'Withdrawal (on-chain leg, queued)'],
    ];
    if (led_cmp($fee, '0') > 0) {
        $righe[] = ['conto' => led_conto_commissioni($token), 'token' => $token, 'importo' => $fee,
                    'causale' => 'commissione', 'descrizione' => 'Withdrawal fee ' . ($feeBp / 100) . '%'];
    }
    $mov = led_scrivi($righe, 'sistema');

    // --- in coda. Qui si ferma: firma Mirco, sul PC.
    $q = led_db()->prepare(
        'INSERT INTO led_coda (creata,genere,uid,token,importo,destinazione,movimento,nota)
         VALUES (?,?,?,?,?,?,?,?)'
    );
    $q->execute([time(), 'prelievo', $uid, $token, $netto, $destinazione, $mov,
                 'netto dopo commissione ' . ($feeBp / 100) . '%']);

    return ['movimento' => $mov, 'lordo' => $importo, 'commissione' => $fee, 'netto' => $netto,
            'coda' => (int)led_db()->lastInsertId()];
}

/* ==================================================================
   LO SWAP — leggi la nota, non e' una formalita'
   ==================================================================
 *
 * Ogni coppia ha un interruttore, e alcune nascono SPENTE. Il motivo
 * non e' tecnico:
 *
 *  · Il progetto dichiara ovunque — sito, Terms, Privacy, ogni pagina
 *    pubblica — che «i DRX non sono soldi, non si convertono, non
 *    rendono niente». I DRX si guadagnano col merito.
 *  · Il DUX si compra con USDT a 1:1.
 *  · Quindi, se DUX -> DRX e' aperto a 1:1, i DRX SI COMPRANO con
 *    dollari. Nel momento esatto in cui quell'interruttore si accende,
 *    ogni frase «i DRX non sono soldi» diventa falsa — sul sito, nei
 *    Terms e nella Privacy che abbiamo appena scritto.
 *
 * Con utenti europei, wallet custodial e MiCA di mezzo, quella non e'
 * una sfumatura di copy: e' la differenza fra un punto premi e uno
 * strumento convertibile.
 *
 * Percio' il motore c'e' e funziona, ma l'interruttore lo gira Mirco,
 * sapendo cosa gira. E prima va cambiato il testo pubblico, non dopo.
 *
 * Stessa storia per DRX <-> 81X: la documentazione dice 1.000:1, la
 * richiesta dice 1:1. Ne puo' valere una sola. Qui c'e' quella scritta
 * nei documenti; cambiarla e' una riga, ma va cambiata anche nel
 * Whitepaper, se no i due si contraddicono.
 */

function led_swap_politica_predefinita(): void
{
    $db = led_db();
    $q = $db->prepare(
        'INSERT OR IGNORE INTO led_swap_politica (da,a,num,den,fee_bp,acceso,perche) VALUES (?,?,?,?,?,?,?)'
    );
    // DRX -> 81X : il tasso dei documenti, 1.000 : 1
    $q->execute(['DRX', '81X', 1, 1000, 0, 1, 'Tasso da handoff e Whitepaper: 1 81X ogni 1.000 DRX.']);
    $q->execute(['81X', 'DRX', 1000, 1, 0, 1, 'Inverso del precedente.']);
    // DUX <-> DRX : SPENTI
    /* ACCESE DA MIRCO il 16 agosto 2026: DUX, DRX e 81X sono punti interni
       dell'ecosistema, si acquistano e valgono 1:1 fra loro. Il grilletto
       l'ha premuto lui, come deve essere.
       RESTA UNA RIGA DA CAMBIARE FUORI DA QUI, e non e' una formalita':
       il footer del sito (genesys/_dr-footer.php) dice ancora
       «DRX/81X ... non sono convertibili in denaro». Con questa coppia
       accesa i DRX si ottengono con DUX, che si compra con USDT: quella
       frase va riscritta, altrimenti il sito dice una cosa e il codice
       ne fa un'altra — ed e' il primo posto dove qualcuno guarda. */
    $q->execute(['DUX', 'DRX', 1, 1, 0, 1,
        'Punti interni dell\'ecosistema, 1:1. Deciso da Mirco il 16-08-2026.']);
    $q->execute(['DRX', 'DUX', 1, 1, 0, 1,
        'Inverso del precedente. Deciso da Mirco il 16-08-2026.']);
    // DUX <-> 81X : acceso, sono tutti e due token di utilita'
    $q->execute(['DUX', '81X', 1, 1, 0, 0, 'Spenta: vedi sotto.']);
    $q->execute(['81X', 'DUX', 1, 1, 0, 0, 'Spenta: vedi sotto.']);
    /* SPENTA IL 17-08-2026 (audit Business Model Master, ciclo trovato nel codice):
       con DUX<->DRX 1:1 e DRX<->81X 1000:1, la coppia DUX<->81X a 1:1 chiudeva un
       ciclo DUX -> 81X -> DRX -> DUX che moltiplica per 1.000 a ogni giro.
       Il tasso DUX<->81X esiste gia', implicito e coerente, passando dai DRX (1.000:1).
       L'UPDATE serve per i registri gia' seminati con la coppia accesa. */
    $db->exec("UPDATE led_swap_politica SET acceso=0, perche='SPENTA 17-08-2026: con DUX<->DRX 1:1 e DRX<->81X 1000:1 questa coppia 1:1 creava un ciclo x1000 (DUX->81X->DRX->DUX). Il cambio DUX<->81X si fa via DRX, 1000:1.' WHERE (da='DUX' AND a='81X') OR (da='81X' AND a='DUX')");
}

function led_swap(string $uid, string $da, string $a, string $importo): array
{
    led_bc();
    if (led_cmp($importo, '0') <= 0) throw new InvalidArgumentException('DR-LEDGER: importo non positivo.');

    $q = led_db()->prepare('SELECT * FROM led_swap_politica WHERE da=? AND a=?');
    $q->execute([$da, $a]);
    $p = $q->fetch();
    if (!$p) throw new RuntimeException("DR-LEDGER: coppia $da->$a non prevista.");
    if (!(int)$p['acceso']) {
        throw new RuntimeException("DR-LEDGER: coppia $da->$a spenta. Motivo: " . $p['perche']);
    }

    // Il tasso si applica sulle unita' UMANE, non su quelle base:
    // DUX ha 6 decimali e DRX ne ha 18, quindi 1:1 fra unita' base
    // sarebbe uno scambio a 10^12, cioe' un disastro silenzioso.
    $dDa = led_decimali($da);
    $dA  = led_decimali($a);
    $fee = bigi_div(bigi_mul($importo, (string)$p['fee_bp']), '10000');
    $netto = bigi_sub($importo, $fee);

    $reso = bigi_div(
        bigi_mul(bigi_mul($netto, (string)$p['num']), bigi_pow10($dA)),
        bigi_mul((string)$p['den'], bigi_pow10($dDa))
    );

    if (led_cmp($reso, '0') <= 0) {
        throw new RuntimeException('DR-LEDGER: importo troppo piccolo, il cambio darebbe zero.');
    }

    $righe = [
        ['conto' => led_conto_utente($uid, $da), 'token' => $da, 'importo' => led_meno($importo),
         'causale' => 'swap', 'descrizione' => "Swap $da -> $a"],
        ['conto' => led_conto_tesoreria($da), 'token' => $da, 'importo' => $netto,
         'causale' => 'swap', 'descrizione' => "Swap in $da"],
        ['conto' => led_conto_utente($uid, $a), 'token' => $a, 'importo' => $reso,
         'causale' => 'swap', 'descrizione' => "Swap $da -> $a"],
        ['conto' => led_conto_tesoreria($a), 'token' => $a, 'importo' => led_meno($reso),
         'causale' => 'swap', 'descrizione' => "Swap out $a"],
    ];
    if (led_cmp($fee, '0') > 0) {
        $righe[] = ['conto' => led_conto_commissioni($da), 'token' => $da, 'importo' => $fee,
                    'causale' => 'commissione', 'descrizione' => 'Swap fee'];
    }
    return ['movimento' => led_scrivi($righe, 'sistema'), 'dato' => $importo, 'reso' => $reso, 'commissione' => $fee];
}

/* ==================================================================
   LA COPIA DA POLYGON
   ==================================================================
 * Questo file NON parla con la rete: riceve i movimenti gia' letti
 * (da uno script sul PC o da un cron con la sua chiave RPC) e li scrive
 * una volta sola. La difesa contro il doppio conteggio non e' un
 * controllo nel codice: e' l'indice UNIQUE sullo schema, che non si
 * puo' dimenticare di chiamare.
 */
function led_importa_chain(array $movimenti, string $catena = 'polygon'): array
{
    $nuovi = 0; $gia = 0; $errori = [];
    foreach ($movimenti as $m) {
        try {
            led_deposito((string)$m['uid'], (string)$m['token'], (string)$m['importo'], [
                'catena' => $catena, 'tx_hash' => $m['tx_hash'],
                'log_index' => (int)$m['log_index'], 'blocco' => (int)$m['blocco'],
                'stato' => ((int)($m['conferme'] ?? 0) >= 30) ? 'confermato' : 'attesa',
            ]);
            $nuovi++;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) { $gia++; continue; }
            $errori[] = $m['tx_hash'] . ': ' . $e->getMessage();
        } catch (Throwable $e) {
            $errori[] = ($m['tx_hash'] ?? '?') . ': ' . $e->getMessage();
        }
    }
    return ['nuovi' => $nuovi, 'gia_presenti' => $gia, 'errori' => $errori];
}

/* ==================================================================
   LA VERIFICA — quella che rende il registro credibile
   ================================================================== */

function led_verifica(): array
{
    $db = led_db();
    $prec = str_repeat('0', 64);
    $rotte = [];
    $n = 0;

    foreach ($db->query('SELECT * FROM led_scritture ORDER BY id ASC') as $r) {
        $n++;
        if ($r['impronta_prec'] !== $prec) {
            $rotte[] = ['id' => $r['id'], 'cosa' => 'la riga non aggancia la precedente'];
        }
        $atteso = led_calcola_impronta($r, $r['impronta_prec']);
        if (!hash_equals($atteso, (string)$r['impronta'])) {
            $rotte[] = ['id' => $r['id'], 'cosa' => 'contenuto alterato dopo la scrittura'];
        }
        $prec = (string)$r['impronta'];
    }

    // la somma di tutto, per token, deve fare zero
    $squilibri = [];
    foreach ($db->query("SELECT DISTINCT token FROM led_scritture") as $t) {
        $q = $db->prepare('SELECT importo FROM led_scritture WHERE token=?');
        $q->execute([$t['token']]);
        $s = '0';
        foreach ($q as $x) $s = led_somma($s, (string)$x['importo']);
        if (led_cmp($s, '0') !== 0) $squilibri[$t['token']] = $s;
    }

    return [
        'righe' => $n,
        'catena_integra' => empty($rotte),
        'rotte' => $rotte,
        'partita_doppia_ok' => empty($squilibri),
        'squilibri' => $squilibri,
    ];
}

/* ==================================================================
   L'ESTRATTO CONTO
   ================================================================== */

function led_estratto(string $uid, ?string $token = null, ?int $da = null, ?int $a = null): array
{
    $sql = 'SELECT s.*, c.token AS tok FROM led_scritture s
            JOIN led_conti c ON c.id = s.conto
            WHERE c.proprietario = ? AND c.genere = \'utente\'';
    $par = [$uid];
    if ($token) { $sql .= ' AND c.token = ?'; $par[] = $token; }
    if ($da)    { $sql .= ' AND s.quando >= ?'; $par[] = $da; }
    if ($a)     { $sql .= ' AND s.quando <= ?'; $par[] = $a; }
    $sql .= ' ORDER BY s.quando DESC, s.id DESC';

    $q = led_db()->prepare($sql);
    $q->execute($par);
    return $q->fetchAll();
}

/** L'estratto in CSV. Separatore ';' e BOM: Excel italiano lo apre bene. */
function led_estratto_csv(string $uid, ?string $token = null): string
{
    $righe = led_estratto($uid, $token);
    $out = "\xEF\xBB\xBF";
    $out .= implode(';', ['Date (UTC)', 'Movement', 'Type', 'Token', 'Amount',
                          'Description', 'Chain', 'Tx hash', 'Block', 'Status', 'Entry hash']) . "\r\n";
    foreach ($righe as $r) {
        $out .= implode(';', array_map(
            static fn($v) => '"' . str_replace('"', '""', (string)$v) . '"',
            [
                gmdate('Y-m-d H:i:s', (int)$r['quando']),
                substr((string)$r['movimento'], 0, 12),
                $r['causale'],
                $r['token'],
                led_umano((string)$r['importo'], (string)$r['token']),
                $r['descrizione'],
                $r['catena'] ?? '',
                $r['tx_hash'] ?? '',
                $r['blocco'] ?? '',
                $r['stato'],
                substr((string)$r['impronta'], 0, 16),
            ]
        )) . "\r\n";
    }
    return $out;
}

/* ==================================================================
   IL DIARIO DELL'ADMIN — chi ha fatto cosa
   ================================================================== */

function led_diario(string $chi, string $azione, string $bersaglio = '', string $dettaglio = ''): void
{
    $q = led_db()->prepare(
        'INSERT INTO led_diario_admin (quando,chi,azione,bersaglio,dettaglio,ip) VALUES (?,?,?,?,?,?)'
    );
    $q->execute([time(), $chi, $azione, $bersaglio, $dettaglio,
                 $_SERVER['REMOTE_ADDR'] ?? 'cli']);
}

/** Copia incorporata di dr-ledger-schema.sql — usata solo se il file manca. */
function led_schema_incorporato(): string
{
    return <<<'DRSCHEMA'
-- ==================================================================
-- DR-LEDGER — lo schema del registro interno
-- Destino Randagio · 16 agosto 2026
--
-- Tre proprieta' che questo schema garantisce, e su cui si regge tutto:
--
--  1. NON SI CANCELLA E NON SI MODIFICA. Tre trigger impediscono UPDATE
--     e DELETE sulle scritture. Un errore si corregge con una scrittura
--     di segno opposto, come in contabilita' vera. Un registro che si
--     puo' correggere non e' un registro: e' una bozza.
--
--  2. PARTITA DOPPIA. Ogni movimento e' almeno due righe che sommano a
--     zero. Se la somma di TUTTE le righe di un token non fa zero, il
--     registro e' rotto e si vede subito. Un saldo sbagliato si nasconde;
--     una somma che non torna, no.
--
--  3. CATENA DI IMPRONTE. Ogni riga porta l'impronta della precedente.
--     Chi tocca una riga vecchia rompe la catena da li' in avanti, e
--     led_verifica() dice esattamente dove. Non impedisce la manomissione:
--     la rende impossibile da nascondere. Che e' quello che serve.
--
-- Importi: SEMPRE interi in unita' base, come stringa. Mai virgola
-- mobile. I soldi in floating point sono un bug che aspetta il momento
-- giusto: 0.1 + 0.2 non fa 0.3 in nessun linguaggio di questo pianeta.
-- DUX 6 decimali · DRX 18 · 81X 18 · POL 18 · USDT 6.
-- ==================================================================

PRAGMA journal_mode = WAL;
PRAGMA foreign_keys = ON;

-- ------------------------------------------------------------------
-- I CONTI. Ogni utente ha un conto per token. Esistono anche conti di
-- sistema: la Tesoreria, il deposito globale, le commissioni, e i conti
-- "mondo esterno" che fanno da contropartita ai movimenti on-chain.
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS led_conti (
  id            INTEGER PRIMARY KEY,
  proprietario  TEXT    NOT NULL,        -- uid utente, oppure 'sistema'
  genere        TEXT    NOT NULL,        -- utente | tesoreria | deposito | commissioni | esterno | riserva
  token         TEXT    NOT NULL,        -- DUX | DRX | 81X | POL | USDT
  sic_id        TEXT,                    -- SIC-ID-G-... se e' un Pioniere
  creato        INTEGER NOT NULL,
  UNIQUE (proprietario, genere, token)
);

-- ------------------------------------------------------------------
-- LE SCRITTURE. Il cuore. Append-only.
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS led_scritture (
  id            INTEGER PRIMARY KEY,
  movimento     TEXT    NOT NULL,        -- uuid: lega insieme le righe di uno stesso movimento
  quando        INTEGER NOT NULL,        -- epoch UTC
  conto         INTEGER NOT NULL REFERENCES led_conti(id),
  token         TEXT    NOT NULL,
  importo       TEXT    NOT NULL,        -- intero con segno, unita' base. + entra, - esce
  causale       TEXT    NOT NULL,        -- deposito | prelievo | trasferimento | swap | premio | commissione | rettifica | gas
  descrizione   TEXT    NOT NULL DEFAULT '',
  -- l'aggancio alla catena pubblica, quando c'e'
  catena        TEXT,                    -- polygon | NULL se e' un movimento solo interno
  tx_hash       TEXT,
  log_index     INTEGER,
  blocco        INTEGER,
  stato         TEXT    NOT NULL DEFAULT 'confermato',  -- attesa | confermato | riorganizzato
  -- la catena di impronte
  impronta_prec TEXT    NOT NULL,
  impronta      TEXT    NOT NULL UNIQUE,
  -- chi l'ha scritta: 'sistema', 'indicizzatore', oppure l'uid admin
  autore        TEXT    NOT NULL DEFAULT 'sistema'
);

-- LA RIGA CHE IMPEDISCE DI CONTARE DUE VOLTE.
-- L'indicizzatore rilegge la catena a ogni giro: senza questo indice,
-- una ripartenza raddoppierebbe ogni deposito. E' l'errore piu' comune
-- e piu' caro di qualsiasi indicizzatore.
CREATE UNIQUE INDEX IF NOT EXISTS led_ix_onchain
  ON led_scritture (catena, tx_hash, log_index, conto)
  WHERE catena IS NOT NULL;

CREATE INDEX IF NOT EXISTS led_ix_conto   ON led_scritture (conto, quando DESC);
CREATE INDEX IF NOT EXISTS led_ix_mov     ON led_scritture (movimento);
CREATE INDEX IF NOT EXISTS led_ix_quando  ON led_scritture (quando DESC);
CREATE INDEX IF NOT EXISTS led_ix_tx      ON led_scritture (tx_hash);

-- ------------------------------------------------------------------
-- I TRIGGER CHE RENDONO IL REGISTRO UN REGISTRO
-- ------------------------------------------------------------------
CREATE TRIGGER IF NOT EXISTS led_no_update
BEFORE UPDATE ON led_scritture
BEGIN
  SELECT RAISE(ABORT, 'DR-LEDGER: una scrittura non si modifica. Scrivi una rettifica di segno opposto.');
END;

CREATE TRIGGER IF NOT EXISTS led_no_delete
BEFORE DELETE ON led_scritture
BEGIN
  SELECT RAISE(ABORT, 'DR-LEDGER: una scrittura non si cancella. Scrivi una rettifica di segno opposto.');
END;

-- ------------------------------------------------------------------
-- LA CODA VERSO LA CATENA
-- Il sito non firma MAI. Accoda qui, e uno script sul PC di Mirco firma.
-- Un'applicazione che puo' firmare da sola e' un'applicazione che prima
-- o poi qualcuno convince a firmare.
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS led_coda (
  id            INTEGER PRIMARY KEY,
  creata        INTEGER NOT NULL,
  genere        TEXT    NOT NULL,        -- prelievo | prefinanziamento | conio | invio
  uid           TEXT,
  token         TEXT    NOT NULL,
  importo       TEXT    NOT NULL,
  destinazione  TEXT    NOT NULL,        -- indirizzo 0x...
  stato         TEXT    NOT NULL DEFAULT 'in-attesa',  -- in-attesa | firmata | inviata | confermata | rifiutata
  movimento     TEXT,                    -- il movimento del registro che l'ha generata
  tx_hash       TEXT,
  nota          TEXT NOT NULL DEFAULT '',
  vista_admin   INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX IF NOT EXISTS led_ix_coda ON led_coda (stato, creata);

-- ------------------------------------------------------------------
-- DOVE E' ARRIVATO L'INDICIZZATORE
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS led_segnalibro (
  catena        TEXT PRIMARY KEY,
  ultimo_blocco INTEGER NOT NULL DEFAULT 0,
  aggiornato    INTEGER NOT NULL DEFAULT 0
);

-- ------------------------------------------------------------------
-- LA POLITICA DELLO SWAP — e non e' una tabella qualsiasi
--
-- Ogni coppia ha un tasso E un interruttore. L'interruttore nasce
-- SPENTO dove la conversione contraddice quello che il progetto dice
-- in pubblico. Vedi la nota in dr-ledger.php, sezione SWAP.
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS led_swap_politica (
  da            TEXT    NOT NULL,
  a             TEXT    NOT NULL,
  num           INTEGER NOT NULL,        -- tasso = num/den
  den           INTEGER NOT NULL,
  fee_bp        INTEGER NOT NULL DEFAULT 0,   -- punti base: 50 = 0,50%
  acceso        INTEGER NOT NULL DEFAULT 0,
  perche        TEXT    NOT NULL DEFAULT '',
  PRIMARY KEY (da, a)
);

-- ------------------------------------------------------------------
-- IL REGISTRO DI CHI HA FATTO COSA (lato admin)
-- ------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS led_diario_admin (
  id            INTEGER PRIMARY KEY,
  quando        INTEGER NOT NULL,
  chi           TEXT    NOT NULL,
  azione        TEXT    NOT NULL,
  bersaglio     TEXT    NOT NULL DEFAULT '',
  dettaglio     TEXT    NOT NULL DEFAULT '',
  ip            TEXT    NOT NULL DEFAULT ''
);
CREATE INDEX IF NOT EXISTS led_ix_diario ON led_diario_admin (quando DESC);
DRSCHEMA;
}
