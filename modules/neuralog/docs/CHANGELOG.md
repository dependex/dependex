# Changelog

Tutto quello che cambia il comportamento del recupero va segnato qui **con il
numero del banco di prova prima e dopo**. Un cambiamento senza numero non si
può difendere.

---

## 1.0 — prima versione

Modulo universale, senza nessun dato di progetto nel motore. Generalizzato a
partire da un'implementazione reale in produzione (RAG lessicale + grafo +
memoria + apprendimento + sistema immunitario + bus fra agenti), da cui sono
stati tolti tutti i dati di dominio e messi in `config/`.

### Motore

- **Adattatore PDO**: il cervello usa il database dell'applicazione ospite se
  c'è (`$GLOBALS['BRAIN_PDO']` o `brain_host_pdo()`), altrimenti apre il suo.
  SQLite e MySQL 8 con lo stesso codice: le differenze stanno tutte in
  `core/db.php`.
- **Schema `brain_*`** (prefisso configurabile), 13 tabelle, installatore
  idempotente con versione, colonne aggiunte in modo additivo sugli
  aggiornamenti.
- **Testo senza lingua nel codice**: stopwords, accenti, elisioni e sinonimi
  arrivano dai pacchetti `config/synonyms.*.json`. Forniti `it` e `en`.
- **Sinapsi canoniche** `node_a < node_b`, indice unico, niente auto-link.
- **Autolink O(n·k)**: sequenza, concetti, entità condivise, hub per fonte.
- **Ingestione**: walker con glob da config, guardia sui segreti, tetti di
  dimensione, idempotenza per hash, lotti con ripresa, estrattori nativi
  (ooxml via ZipArchive, PDF via pdftotext se presente).
- **`brain_ingest_text()`**: qualunque cosa sia testo diventa conoscenza.
- **Recupero ibrido**: espansione multi-query, IDF-lite sul pool, bonus sul
  percorso, espansione a un salto sulle sinapsi, rerank a quattro segnali,
  diversità di fonte.
- **Muro doppio**: visibilità in SQL (`= 'public'`) + guardia anti-fuga in PHP.
- **Apprendimento con freno**: le risposte ancorate diventano nodi riservati in
  stato `pending`; la promozione è un gesto umano.
- **Qualità**: `health` (con riparazione), `eval` (hit-rate + MRR-lite +
  tendenza), `feedback` (voto clampato + correzione mai pubblicata),
  `reconcile` (registri contro grafo).
- **UI**: cervello 3D (Three.js se c'è, altrimenti ripiego integrato su canvas
  2D — stessi dati, nessuna dipendenza), grafo 2D, console admin, scheda da
  incorporare. Colori e tema in `ui.theme`: nessuna tinta di marca nel codice.
- **Bus fra agenti**: JSONL in sola aggiunta, lock con scadenza, rilevamento di
  conflitti/orfani/doppioni, dashboard e brief. Due implementazioni (PHP e
  Python) con lo stesso formato: **verificato che si leggono a vicenda**.
- **CLI `bin/brain`**: install, doctor, ingest, reindex, health, eval, stats,
  search, ask, demo-seed, demo-clear, export, import, reconcile.

### Correzioni fatte durante lo sviluppo, con il numero

Tutte trovate dai test o dal banco di prova, non a occhio.

1. **Elisione e stopwords** — `l'orario` diventava `lorario`, stringa mai
   presente nei testi: il match falliva sempre. Ora la tokenizzazione spezza
   sugli apostrofi e scarta i prefissi lunghi (`dell`, `nell`, `sull`…), che
   hanno frequenza altissima e portano solo rumore.
2. **Sinonimi in una direzione sola** — chi scriveva "costo" non trovava i
   documenti che dicevano "prezzo". Reso bidirezionale: ogni parola del gruppo
   punta a tutto il gruppo.
3. **I sinonimi decidevano la classifica** *(la correzione che conta)* — appena
   resa bidirezionale l'espansione, la domanda *"come funziona il reso di un
   articolo"* faceva vincere `catalogo-prodotti.md`, che non contiene nessuna
   parola della domanda: "articolo" era stato espanso in
   "prodotto/prodotti/catalogo". Introdotti un **peso minore per i termini
   espansi** (`rag.synonym_weight`, 0,45) e soprattutto un **tetto** al loro
   contributo (`punteggio = originali + min(sinonimi, 0,5 × originali + 1,0)`).
   L'espansione allarga il recupero, non decide.
   **Hit-rate 0,917 → 1,0 · MRR 0,875 → 0,958.**
4. **Righe giganti mai spezzate** — un JSON o un CSV su una riga sola diventava
   un unico neurone da centinaia di KB, inutile da recuperare. Ora ogni chunk
   fuori misura viene ritagliato a forza, sempre con sovrapposizione.

### Misurato su questa macchina (PHP 8.4, SQLite, container Linux)

| | |
|---|---|
| installazione schema | 37 ms, 13 tabelle, 12 indici |
| `demo-seed` | 412 ms → 194 documenti, 194 neuroni, 3.006 sinapsi |
| grafo risultante | 346 nodi, 3.018 sinapsi, 28 entità, 0 orfani, 0 pendenti |
| `eval` | 12 domande, **hit-rate 1,0 · MRR-lite 0,958**, 35 ms |
| `tests/run.php` | **195 test, 0 falliti**, 31 ms |

### Non provato in questo ambiente — detto chiaramente

- **MySQL 8**: il codice differenzia già tipi, upsert e indici, ma non è stato
  eseguito su un MySQL vero qui. Prima della produzione: `php bin/brain
  install && php tests/run.php` sul tuo server.
- **Three.js in WebGL**: nel container non c'era né il file locale né la rete
  per il CDN, quindi è sempre stato usato il ripiego su canvas 2D (che è stato
  provato e funziona). Il percorso WebGL è scritto ma non è mai stato eseguito.
- **Apache e `.htaccess`**: le prove HTTP sono state fatte con il server
  integrato di PHP, che i `.htaccess` non li legge. Le regole vanno verificate
  sul tuo server (vedi `06-SICUREZZA.md`).
- **Carico reale**: nessuna prova sopra i ~350 nodi. I limiti del `LIKE` oltre
  le decine di migliaia di nodi sono dichiarati, non misurati.

---

## Come si scrive una voce nuova

```markdown
## 1.1 — titolo breve

### Cambiato
- cosa, e perché

### Effetto sul banco di prova
- hit-rate 0,92 → 0,95 · MRR 0,88 → 0,91 (12 domande, top-5)

### Non provato
- ...
```
