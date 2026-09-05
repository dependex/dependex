<?php
/* ============================================================================
   ESEMPIO 1 — innestare il cervello in un'applicazione PHP che esiste gia'.
   Il punto delicato e' uno solo: UN SOLO DATABASE. Se l'applicazione ha gia'
   il suo PDO, il cervello ci vive dentro (tabelle con prefisso proprio) e non
   apre un secondo file alle spalle di nessuno.
   Questo file non va deployato: copia le righe che ti servono nel bootstrap
   della tua applicazione.
============================================================================ */

/* ------------------------------------------------------------------ 1. il DB
   L'applicazione ospite ha gia' aperto il suo $pdo (MySQL o SQLite). */
require __DIR__ . '/../../bootstrap.php';   // <- il bootstrap VERO della tua app
/** @var PDO $pdo */

/* Ponte: il cervello usera' questo PDO e nessun altro. Va dichiarato PRIMA di
   caricare brain.php. Due modi equivalenti, scegline uno. */
$GLOBALS['BRAIN_PDO'] = $pdo;
// oppure:
// function brain_host_pdo(): PDO { global $pdo; return $pdo; }

/* --------------------------------------------------------- 2. chi e' admin
   Il cervello non conosce il tuo sistema di login: glielo dici tu. */
function brain_host_is_admin(): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
    return !empty($_SESSION['is_admin']) || (($_SESSION['role'] ?? '') === 'admin');
}

/* ---------------------------------------------------------- 3. il cervello */
require_once __DIR__ . '/../brain.php';

/* Prima volta: crea le tabelle (idempotente, si puo' chiamare ad ogni avvio,
   ma in produzione conviene farlo una volta sola da riga di comando). */
if (!brain_schema_ready()) { brain_install(); }

/* ------------------------------------------------- 4. dargli da mangiare
   Qualunque cosa nella tua applicazione sia testo puo' diventare conoscenza.
   Esempio: ogni scheda prodotto che salvi finisce anche nel cervello. */
function salva_prodotto(array $p): void {
    // ... il tuo salvataggio normale sul tuo database ...

    brain_ingest_text(
        "Prodotto: {$p['nome']}\nPrezzo: {$p['prezzo']} euro\n"
        . "Categoria: {$p['categoria']}\nDescrizione: {$p['descrizione']}",
        [
            'path'       => 'prodotti/' . $p['id'],       // stabile: reingerire aggiorna, non duplica
            'title'      => $p['nome'],
            'source'     => 'catalogo',
            'section'    => 'document',
            'visibility' => 'public',                      // questa scheda si puo' mostrare a tutti
        ]
    );
}

/* Un ticket di assistenza, invece, resta riservato. */
function salva_ticket(array $t): void {
    brain_ingest_text(
        "Ticket {$t['id']}\nCliente: {$t['cliente']}\nProblema: {$t['testo']}\nSoluzione: {$t['soluzione']}",
        ['path' => 'ticket/' . $t['id'], 'title' => 'Ticket ' . $t['id'],
         'source' => 'assistenza', 'visibility' => 'admin']     // niente pubblico
    );
}

/* Quando un prodotto viene cancellato, il cervello se ne dimentica. */
function cancella_prodotto(string $id): void {
    brain_forget_path('prodotti/' . $id);
}

/* --------------------------------------------------------- 5. usarlo
   a) ricerca secca nella conoscenza pubblica */
function cerca_pubblico(string $q): array {
    return brain_search($q, ['admin' => false, 'n' => 5]);
}

/* b) risposta assistita da un modello: il cervello prepara contesto e prompt,
      il modello lo chiami TU, con la TUA chiave, dove vuoi. */
function rispondi(string $domanda): string {
    $pack = brain_ask($domanda, ['admin' => brain_host_is_admin()]);

    if (!$pack['grounded']) {
        return "Su questo non risulta niente nei nostri documenti.";
    }

    // $pack['prompt']  -> system prompt gia' montato (regole + contesto + fonti)
    // $pack['context'] -> solo il contesto
    // $pack['sources'] -> le fonti, per i pulsanti "utile / non utile"
    $risposta = chiama_il_tuo_modello($pack['prompt'], $domanda);   // <- funzione tua

    // registra la conversazione e, se la risposta era ancorata, la fa imparare
    // (il nodo nasce riservato e in attesa di revisione: mai pubblicato da solo)
    brain_ask_complete($domanda, $risposta, $pack['grounded']);

    return $risposta;
}

/* --------------------------------------------------------- 6. manutenzione
   Da cron, una volta al giorno. Niente di tutto questo va lasciato al web. */
// php bin/brain ingest --all
// php bin/brain health --fix
// php bin/brain eval
