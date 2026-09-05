<?php
/* ============================================================================
   COMPANY BRAIN — ingest/demo.php
   Semina di prova: ~200 neuroni di un'AZIENDA FINTA (prodotti, servizi,
   persone, policy, ticket, verbali). Serve a una cosa sola: far vedere che il
   cervello funziona PRIMA di avergli dato un solo dato vero, e dare al banco
   di prova (config/benchmark.sample.json) qualcosa su cui misurarsi.
   Tutti i nodi nascono con source='demo': si cancellano con un comando solo.
   NESSUN dato reale, nessun nome vero, nessuna cifra vera.
============================================================================ */
require_once __DIR__ . '/text.php';
require_once __DIR__ . '/../core/log.php';

/** I documenti "portanti": le domande del benchmark trovano risposta qui. */
function brain_demo_documents(): array {
    return [
        ['demo/listino-prezzi.md', 'Listino prezzi', <<<TXT
# Listino prezzi (valido per l'anno in corso)

Prodotto Alfa — modello base, 249 euro IVA esclusa.
Prodotto Beta — modello intermedio, 499 euro IVA esclusa.
Prodotto Gamma — modello professionale, 1190 euro IVA esclusa.

Sconto quantita': -5% da cinque pezzi, -10% da venti pezzi.
Il listino non comprende installazione e formazione, che si acquistano a parte.
TXT],
        ['demo/orari-e-sede.md', 'Orari e sede', <<<TXT
# Orari di apertura e sede

Gli uffici sono aperti dal lunedi' al venerdi', dalle 9:00 alle 18:00.
Il sabato e la domenica sono chiusi. Nei giorni festivi resta attiva solo la
casella di posta.

La sede operativa e' in Via Esempio 12, 00100 Citta' Esempio.
Il magazzino si trova nello stesso stabile, ingresso sul retro.
TXT],
        ['demo/policy-resi.md', 'Policy resi', <<<TXT
# Politica di reso

Il cliente puo' restituire un articolo entro 30 giorni dalla consegna, purche'
integro e nella confezione originale. Il reso si apre dall'area clienti oppure
scrivendo all'assistenza. Il rimborso avviene entro dieci giorni lavorativi
dalla ricezione della merce, con lo stesso mezzo di pagamento usato all'acquisto.
Le spese di restituzione sono a carico del cliente, salvo prodotto difettoso.
TXT],
        ['demo/policy-garanzia.md', 'Policy garanzia', <<<TXT
# Garanzia

Tutti i prodotti a catalogo hanno una garanzia di 24 mesi dalla data di
fattura. La garanzia copre i difetti di fabbricazione, non l'usura ne' i danni
da uso improprio. Per attivarla servono numero di serie e copia della fattura.
La riparazione in garanzia non prolunga la durata della garanzia stessa.
TXT],
        ['demo/persone-assistenza.md', 'Assistenza clienti — referenti', <<<TXT
# Assistenza clienti

Maria Rossi e' la responsabile dell'assistenza clienti: coordina le richieste
in entrata, i resi e le pratiche di garanzia.
Giulia Verdi segue la formazione dei clienti e i corsi di avviamento.
L'assistenza risponde negli orari di ufficio; fuori orario si lascia un
messaggio e si viene richiamati il primo giorno lavorativo utile.
TXT],
        ['demo/persone-magazzino.md', 'Magazzino e logistica — referenti', <<<TXT
# Magazzino

Luca Bianchi gestisce il magazzino: carichi, scarichi, inventario e
preparazione delle spedizioni. Ogni movimento viene registrato lo stesso
giorno. L'inventario completo si fa due volte l'anno.
TXT],
        ['demo/catalogo-prodotti.md', 'Catalogo prodotti', <<<TXT
# Catalogo

A catalogo ci sono tre linee:
- Prodotto Alfa: modello base, per chi comincia.
- Prodotto Beta: modello intermedio, il piu' venduto.
- Prodotto Gamma: modello professionale, con manutenzione inclusa il primo anno.

Ogni linea ha accessori dedicati. I ricambi restano disponibili per cinque anni
dalla fine della produzione.
TXT],
        ['demo/amministrazione-fatture.md', 'Fatturazione', <<<TXT
# Fatturazione e pagamenti

La fatturazione avviene in formato elettronico al momento della spedizione.
Per richiedere una copia della fattura basta indicare numero d'ordine e partita
IVA all'ufficio amministrativo. I pagamenti si accettano con bonifico bancario
o carta; per i clienti con contratto e' previsto il pagamento a trenta giorni
fine mese.
TXT],
        ['demo/privacy-gdpr.md', 'Privacy e trattamento dati', <<<TXT
# Privacy

I dati personali dei clienti sono trattati secondo il GDPR (Regolamento UE
2016/679). Si raccolgono solo i dati necessari a evadere l'ordine, gestire la
garanzia e la fatturazione. I dati non vengono ceduti a terzi per finalita'
commerciali. Il cliente puo' chiedere accesso, rettifica o cancellazione
scrivendo al titolare del trattamento.
TXT],
        ['demo/contatti.md', 'Contatti', <<<TXT
# Contatti

Supporto tecnico: supporto@example.com
Amministrazione: amministrazione@example.com
Centralino: +39 000 0000000, negli orari di ufficio.
Per le richieste tecniche conviene scrivere: la risposta arriva con lo storico
del caso allegato.
TXT],
        ['demo/logistica-spedizioni.md', 'Spedizioni', <<<TXT
# Spedizioni e consegna

Gli ordini confermati entro mezzogiorno partono in giornata. La consegna
avviene in 3 giorni lavorativi sul territorio nazionale, cinque per le isole.
Il corriere avvisa via messaggio il giorno della consegna. Se il pacco arriva
danneggiato va accettato con riserva e segnalato all'assistenza.
TXT],
        ['demo/servizi-installazione.md', 'Installazione e formazione', <<<TXT
# Servizi

L'installazione presso il cliente si prenota in fase d'ordine e viene eseguita
da un tecnico autorizzato. La formazione di base dura mezza giornata e si tiene
in sede o da remoto. La manutenzione programmata e' annuale e comprende
controllo, pulizia e aggiornamento del software.
TXT],
        ['demo/contratti-preventivi.md', 'Contratti e preventivi', <<<TXT
# Contratti e preventivi

Un preventivo resta valido trenta giorni dalla data di emissione. Il contratto
quadro per i clienti ricorrenti prevede condizioni riservate e un referente
dedicato. Le variazioni di contratto si concordano per iscritto: nessuna
modifica vale se non e' scritta.
TXT],
        ['demo/fornitori.md', 'Fornitori', <<<TXT
# Fornitori

I fornitori vengono valutati ogni anno su puntualita', qualita' e assistenza.
Un nuovo fornitore entra in elenco dopo una fornitura di prova andata a buon
fine. I pagamenti ai fornitori seguono le condizioni concordate in contratto.
TXT],
    ];
}

/** Semina i dati finti. Ritorna il resoconto. */
function brain_demo_seed(int $filler = 180, bool $quiet = true): array {
    $t0 = microtime(true);
    $docs = 0; $nodes = 0; $links = 0;

    foreach (brain_demo_documents() as [$path, $title, $text]) {
        $r = brain_ingest_text($text, [
            'path' => $path, 'title' => $title, 'source' => 'demo',
            'section' => 'document', 'visibility' => 'public', 'id_prefix' => 'demo', 'force' => true,
        ]);
        $docs++; $nodes += (int)($r['chunks'] ?? 0); $links += (int)($r['links'] ?? 0);
    }

    /* riempitivo: vita quotidiana di un'azienda qualunque. Vocabolario diverso
       dai documenti portanti, cosi' non falsa il banco di prova. */
    $temi = ['aggiornamento del portale', 'revisione delle procedure interne', 'incontro con il reparto commerciale',
             'analisi delle richieste ricevute', 'organizzazione della sala riunioni', 'verifica delle scorte minime',
             'pianificazione delle ferie', 'manutenzione dei mezzi aziendali', 'archiviazione dei documenti',
             'raccolta delle segnalazioni', 'formazione sulla sicurezza', 'rinnovo delle utenze'];
    $stati = ['aperto', 'in lavorazione', 'chiuso', 'sospeso'];
    for ($i = 0; $i < $filler; $i++) {
        $n = 3000 + $i;
        $tema = $temi[$i % count($temi)];
        if ($i % 3 === 0) {
            $path = 'demo/ticket/ticket-' . $n . '.md';
            $title = 'Ticket ' . $n;
            $text = "# Ticket $n\n\nOggetto: $tema.\nStato: " . $stati[$i % 4] . ".\n"
                  . "Il caso e' stato preso in carico dal reparto competente e aggiornato con le note\n"
                  . "raccolte durante la lavorazione. Nessuna azione ulteriore richiesta al cliente.\n";
        } elseif ($i % 3 === 1) {
            $path = 'demo/verbali/verbale-' . $n . '.md';
            $title = 'Verbale riunione ' . $n;
            $text = "# Verbale $n\n\nArgomento della riunione: $tema.\n"
                  . "Presenti i responsabili delle aree interessate. Si e' concordato di proseguire\n"
                  . "secondo quanto stabilito, con verifica alla riunione successiva.\n";
        } else {
            $path = 'demo/schede/scheda-' . $n . '.md';
            $title = 'Scheda interna ' . $n;
            $text = "# Scheda interna $n\n\nAmbito: $tema.\n"
                  . "Riferimento operativo per il personale. Il documento viene rivisto quando\n"
                  . "cambiano le procedure a cui si riferisce.\n";
        }
        $r = brain_ingest_text($text, [
            'path' => $path, 'title' => $title, 'source' => 'demo',
            'section' => 'document', 'visibility' => 'public', 'id_prefix' => 'demo', 'force' => true,
        ]);
        $docs++; $nodes += (int)($r['chunks'] ?? 0); $links += (int)($r['links'] ?? 0);
    }

    $counts = brain_counts();
    $out = ['ok' => true, 'documents' => $docs, 'nodes_created' => $nodes, 'links_created' => $links,
            'total_nodes' => $counts['nodes'], 'total_links' => $counts['links'],
            'ms' => (int)round((microtime(true) - $t0) * 1000)];
    brain_activity('demo-seed', json_encode($out));
    return $out;
}

/** Cancella tutto cio' che ha source='demo' (nodi, sinapsi, entita'). */
function brain_demo_clear(): array {
    $ids = array_column(brain_rows('SELECT id FROM ' . brain_t('nodes') . " WHERE source='demo'"), 'id');
    if ($ids) {
        foreach (array_chunk($ids, 400) as $chunk) {
            brain_links_drop_for($chunk);
            brain_entities_drop_for($chunk);
            $ph = implode(',', array_fill(0, count($chunk), '?'));
            brain_exec('DELETE FROM ' . brain_t('nodes') . ' WHERE id IN (' . $ph . ')', $chunk);
        }
    }
    brain_exec('DELETE FROM ' . brain_t('files') . " WHERE source_kind='demo'");
    brain_activity('demo-clear', count($ids) . ' nodi');
    return ['ok' => true, 'removed' => count($ids)];
}
