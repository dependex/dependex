<?php
/**
 * ECO-NETWORK — i 118 nodi Genesys e i posti delle persone sotto di loro.
 *
 * LA STRUTTURA (quella vera, non una metafora)
 *   MASTER            1 nodo, il fondatore. Non riceve piazzamenti.
 *   WORLD             9 nodi   (Genesys #1 - #9)
 *   NATIONAL         27 nodi   (Genesys #10 - #36)
 *   PRO              82 nodi   (Genesys #37 - #118)   <- QUI si piazzano le persone
 *   ----------------------------------------------------------------
 *   118 nodi Genesys in tutto (9 + 27 + 82), piu' il Master sopra.
 *
 * Sotto gli 82 Pro sta la rete delle persone: fino a 5.000.000 di posti.
 *
 * DUE LEGAMI DIVERSI, E NON VANNO MAI CONFUSI
 *   · placement_parent_node_id — DOVE SEI nell'albero. Lo decide il
 *     sistema riempiendo a livelli (BFS), non tu.
 *   · sponsor_node_id / sponsor_user_id — CHI TI HA PORTATO. Puo' stare
 *     in un altro ramo, in un altro nodo Pro, o non esserci affatto.
 *   Confonderli e' l'errore che rompe i calcoli di rete e i pagamenti.
 *
 * DUE REGISTRAZIONI NELLO STESSO ISTANTE NON PRENDONO LO STESSO POSTO
 *   MySQL: SELECT ... FOR UPDATE sulla riga candidata.
 *   SQLite: BEGIN IMMEDIATE, cioe' lock di scrittura da subito.
 *   In tutti e due i casi il secondo aspetta, rilegge, e trova il posto
 *   gia' occupato: prende il successivo.
 */
declare(strict_types=1);

require_once __DIR__ . '/eco-db.php';
require_once __DIR__ . '/eco-sic.php';

const ECO_NET_MASTER   = 1;      // node_id del Master
const ECO_NET_WORLD    = 9;
const ECO_NET_NATIONAL = 27;
const ECO_NET_PRO      = 82;
const ECO_NET_MAX_POS  = 5000000;

/** Quanti figli diretti per posizione. Default 5; si cambia solo con una migrazione ragionata. */
function eco_net_ampiezza(): int
{
    $v = (int)eco_env('ECO_NET_WIDTH', '5');
    return ($v >= 2 && $v <= 20) ? $v : 5;
}

/** UUID stabile di un nodo Genesys: stesso numero, stesso uuid, su ogni ambiente. */
function eco_net_uuid(int $genesysNo): string
{
    $h = md5('DR-GENESYS-NODE|v1|' . $genesysNo);
    return substr($h, 0, 8) . '-' . substr($h, 8, 4) . '-3' . substr($h, 13, 3) . '-a' . substr($h, 17, 3) . '-' . substr($h, 20, 12);
}

/**
 * Crea i 118 nodi (+ Master) se non ci sono. Idempotente: rilanciarla non fa nulla.
 * I Pro sono gli unici open_for_placement=1.
 */
function eco_net_seed(): array
{
    $n = (int)eco_valore('SELECT COUNT(*) FROM eco_network_nodes', [], 0);
    if ($n >= 1 + ECO_NET_WORLD + ECO_NET_NATIONAL + ECO_NET_PRO) return ['creati' => 0, 'gia' => $n];

    return eco_tx(function () {
        $t = eco_now(); $creati = 0;
        $ins = eco_db()->prepare('INSERT INTO eco_network_nodes (node_id,node_uuid,tier,parent_node_id,genesys_no,label,open_for_placement,child_count,created_at) VALUES (?,?,?,?,?,?,?,0,?)');
        $mettici = function (int $id, string $tier, ?int $parent, int $no, string $lab, int $open) use ($ins, $t, &$creati) {
            $c = (int)eco_valore('SELECT COUNT(*) FROM eco_network_nodes WHERE node_id=?', [$id], 0);
            if ($c) return;
            $ins->execute([$id, eco_net_uuid($no), $tier, $parent, $no, $lab, $open, $t]);
            $creati++;
        };
        $mettici(ECO_NET_MASTER, 'MASTER', null, 0, 'MASTER', 0);
        for ($i = 1; $i <= ECO_NET_WORLD; $i++)    $mettici(1 + $i, 'WORLD', ECO_NET_MASTER, $i, 'WORLD ' . $i, 0);
        for ($i = 1; $i <= ECO_NET_NATIONAL; $i++) $mettici(10 + $i, 'NATIONAL', 1 + (int)ceil($i / 3), ECO_NET_WORLD + $i, 'NATIONAL ' . $i, 0);
        for ($i = 1; $i <= ECO_NET_PRO; $i++)      $mettici(37 + $i, 'PRO', 10 + (($i - 1) % ECO_NET_NATIONAL) + 1, ECO_NET_WORLD + ECO_NET_NATIONAL + $i, 'PRO ' . $i, 1);
        return ['creati' => $creati, 'gia' => 0];
    }, true);
}

/** Gli 82 node_id dei nodi Pro (gli unici che accettano piazzamenti). */
function eco_net_pro_ids(): array
{
    static $c = [];
    if (!$c) $c = array_map('intval', array_column(eco_tutti("SELECT node_id FROM eco_network_nodes WHERE tier='PRO' AND open_for_placement=1 ORDER BY node_id"), 'node_id'));
    return $c;   // niente cache di una lista vuota: prima della migrazione non esiste ancora nulla
}

/** Il prefisso di ramo di una posizione (per la ricerca del primo posto libero sotto di lei). */
function eco_net_prefisso(array $pos): string { return (string)$pos['path']; }

/** Risolve un riferimento sponsor: SIC-ID, username o user_id numerico. null se non esiste. */
function eco_net_risolvi_sponsor(?string $ref): ?array
{
    $ref = trim((string)$ref);
    if ($ref === '') return null;
    if (eco_sic_valida($ref)) {
        $u = eco_sic_trova($ref);
        if ($u) return eco_uno('SELECT * FROM eco_network_positions WHERE user_id=?', [(int)$u['user_id']]);
        return null;
    }
    if (ctype_digit($ref)) {
        $p = eco_uno('SELECT * FROM eco_network_positions WHERE user_id=?', [(int)$ref]);
        if ($p) return $p;
    }
    $u = eco_uno('SELECT user_id FROM eco_users WHERE username_lc=?', [mb_strtolower($ref)]);
    if (!$u) return null;
    return eco_uno('SELECT * FROM eco_network_positions WHERE user_id=?', [(int)$u['user_id']]);
}

/**
 * ASSEGNA IL POSTO. Il cuore.
 *
 * · con sponsor  -> primo posto libero NEL SUO RAMO, riempiendo a livelli
 *                   (BFS): prima tutti i figli diretti, poi i nipoti.
 * · senza sponsor-> un nodo Pro a sorte fra gli 82, poi il primo libero li' sotto.
 *
 * Idempotente: se l'utente ha gia' un posto, torna quello e non tocca niente.
 * Da chiamare dentro eco_tx(..., immediate: true) — eco_onboard lo fa.
 *
 * @return array la riga di eco_network_positions
 */
function eco_assign_position(int $userId, ?string $sponsorRef = null): array
{
    if ($userId <= 0) throw new InvalidArgumentException('ECO-NET: user_id non valido.');
    $gia = eco_uno('SELECT * FROM eco_network_positions WHERE user_id=?', [$userId]);
    if ($gia) return $gia;

    // Tetto dei 5.000.000. Si guarda MAX(position_id), non COUNT(*):
    // COUNT(*) e' una scansione dell'indice, e messa dentro OGNI iscrizione
    // rende la registrazione quadratica. Misurato: a 40.000 utenti pesava
    // piu' di tutto il resto messo insieme. MAX su una chiave primaria e'
    // una lettura sola. Le posizioni non si cancellano mai, quindi
    // MAX(position_id) e i posti occupati sono la stessa cosa.
    $tot = (int)eco_valore('SELECT COALESCE(MAX(position_id),0) FROM eco_network_positions', [], 0);
    if ($tot >= ECO_NET_MAX_POS) throw new RuntimeException('ECO-NET: rete piena (5.000.000 di posti).');

    $W = eco_net_ampiezza();
    $sponsor = eco_net_risolvi_sponsor($sponsorRef);
    if ($sponsor && (int)$sponsor['user_id'] === $userId) $sponsor = null;      // niente auto-referral

    $genesysNodeId = 0; $padre = null; $comeSono = 'AUTO';

    if ($sponsor) {
        $comeSono = 'SPONSOR';
        $genesysNodeId = (int)$sponsor['genesys_node_id'];
        $padre = eco_net_primo_libero_sotto((string)$sponsor['path'], $W, (int)$sponsor['depth']);
        if (!$padre) $padre = $sponsor;                                          // lo sponsor stesso ha posto
    } else {
        $pro = eco_net_pro_ids();
        if (!$pro) throw new RuntimeException('ECO-NET: i nodi Pro non sono stati creati. Lancia eco-migrate.php.');
        $genesysNodeId = $pro[random_int(0, count($pro) - 1)];
        $nodo = eco_uno('SELECT * FROM eco_network_nodes WHERE node_id=?' . eco_for_update(), [$genesysNodeId]);
        if ((int)$nodo['child_count'] < $W) $padre = null;                       // si attacca al nodo Pro, profondita' 0
        else $padre = eco_net_primo_libero_sotto('P' . $genesysNodeId . '.', $W, 0);
    }

    if ($padre !== null) {
        $genesysNodeId = (int)$padre['genesys_node_id'];
        $depth = (int)$padre['depth'] + 1;
        $parentId = (int)$padre['position_id'];
        $prefisso = (string)$padre['path'];
    } else {
        $depth = 0; $parentId = null; $prefisso = 'P' . $genesysNodeId . '.';
    }

    $t = eco_now();
    eco_esegui('INSERT INTO eco_network_positions (user_id,genesys_node_id,placement_parent_node_id,sponsor_node_id,sponsor_user_id,depth,path,child_count,placed_by,created_at)
                VALUES (?,?,?,?,?,?,?,0,?,?)',
        [$userId, $genesysNodeId, $parentId,
         $sponsor ? (int)$sponsor['position_id'] : null,
         $sponsor ? (int)$sponsor['user_id'] : null,
         $depth, $prefisso . 'X.', $comeSono, $t]);
    $posId = (int)eco_ultimo_id();
    eco_esegui('UPDATE eco_network_positions SET path=? WHERE position_id=?', [$prefisso . $posId . '.', $posId]);

    if ($parentId !== null) eco_esegui('UPDATE eco_network_positions SET child_count=child_count+1 WHERE position_id=?', [$parentId]);
    else                    eco_esegui('UPDATE eco_network_nodes SET child_count=child_count+1 WHERE node_id=?', [$genesysNodeId]);

    return eco_uno('SELECT * FROM eco_network_positions WHERE position_id=?', [$posId]);
}

/**
 * L'intervallo di chiavi di un ramo: [prefisso, prefisso con l'ultimo
 * byte incrementato). Si usa al posto di LIKE 'prefisso%' perche' un
 * confronto di intervallo usa l'indice su OGNI motore, mentre LIKE su
 * SQLite non lo usa (LIKE e' insensibile alle maiuscole per default) e
 * su MySQL dipende dalla collation. Il path e' fatto di cifre, 'P' e
 * punti: l'intervallo e' esatto.
 */
function eco_net_range(string $prefisso): array
{
    return [$prefisso, substr($prefisso, 0, -1) . chr(ord(substr($prefisso, -1)) + 1)];
}

/**
 * IL PRIMO POSTO LIBERO SOTTO UN RAMO, riempiendo a livelli.
 *
 * Come e' scritta la ricerca, e perche' cosi':
 *   la via ovvia — WHERE path LIKE 'ramo%' AND child_count < W
 *   ORDER BY depth, position_id LIMIT 1 — e' CORRETTA ma legge tutto il
 *   ramo e poi ordina. Misurato: a 40.000 utenti costava gia' 7,4 ms per
 *   iscrizione contro i 2,2 ms iniziali, e cresceva lineare. A 5 milioni
 *   sarebbe stata inservibile.
 *
 *   Qui invece si scende per livelli, e per ogni livello si chiede il
 *   posto con meno figli: profondita' ed esattamente-quanti-figli sono
 *   due UGUAGLIANZE, quindi l'indice (depth, child_count, path) porta
 *   dritto alla prima riga utile. Ogni tentativo e' una ricerca
 *   nell'indice, non una scansione. Il numero di tentativi e' al
 *   massimo (profondita' del ramo x W), cioe' una manciata: con W=5 un
 *   ramo da 5 milioni di persone e' profondo 10.
 *
 *   A parita' di livello si preferisce il posto con MENO figli: l'albero
 *   viene fuori bilanciato invece che sbilanciato a sinistra. Resta
 *   comunque BFS, perche' il livello piu' alto viene sempre prima.
 *
 * Su MySQL la riga esce bloccata (FOR UPDATE): due processi non se la
 * contendono. Su SQLite ci pensa il BEGIN IMMEDIATE della transazione.
 */
function eco_net_primo_libero_sotto(string $prefisso, int $W, int $daProfondita = 0): ?array
{
    [$lo, $hi] = eco_net_range($prefisso);
    $sql = 'SELECT * FROM eco_network_positions WHERE depth=? AND child_count=? AND path>=? AND path<? ORDER BY path LIMIT 1' . eco_for_update();
    $fondo = $daProfondita + 32;                       // 5^32: molto oltre qualsiasi rete immaginabile
    for ($d = $daProfondita; $d <= $fondo; $d++) {
        for ($k = 0; $k < $W; $k++) {
            $r = eco_uno($sql, [$d, $k, $lo, $hi]);
            if ($r) return $r;
        }
    }
    // Rete di sicurezza: se per qualche motivo la scansione per livelli non
    // ha trovato niente, si torna alla ricerca lenta ma sicuramente corretta.
    $r = eco_uno('SELECT * FROM eco_network_positions WHERE path>=? AND path<? AND child_count<? ORDER BY depth ASC, position_id ASC LIMIT 1' . eco_for_update(), [$lo, $hi, $W]);
    return $r ?: null;
}

/** I figli diretti di una posizione. */
function eco_net_figli(int $positionId, int $limit = 200): array
{
    return eco_tutti('SELECT * FROM eco_network_positions WHERE placement_parent_node_id=? ORDER BY position_id LIMIT ' . (int)$limit, [$positionId]);
}

/** Quante persone ci sono sotto una posizione (ramo intero). Su 5M usa l'indice su path. */
function eco_net_dimensione_ramo(int $positionId): int
{
    $p = eco_uno('SELECT path FROM eco_network_positions WHERE position_id=?', [$positionId]);
    if (!$p) return 0;
    [$lo, $hi] = eco_net_range((string)$p['path']);
    return (int)eco_valore('SELECT COUNT(*) FROM eco_network_positions WHERE path>=? AND path<?', [$lo, $hi], 0) - 1;
}

/** Riepilogo di un nodo Pro: quanti posti, quanto profondi. */
function eco_net_stato_nodo(int $nodeId): array
{
    $r = eco_uno('SELECT COUNT(*) AS n, COALESCE(MAX(depth),-1) AS prof FROM eco_network_positions WHERE genesys_node_id=?', [$nodeId]);
    return ['node_id' => $nodeId, 'posizioni' => (int)($r['n'] ?? 0), 'profondita' => (int)($r['prof'] ?? -1)];
}
