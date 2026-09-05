# Architettura

## Il disegno in una figura

```
   documenti / testo / API                      domanda
            │                                      │
            ▼                                      ▼
   ┌─────────────────┐                    ┌──────────────────┐
   │    ingest/      │                    │      rag/        │
   │  walker, estr., │                    │ retrieve→rerank  │
   │  chunker        │                    │ →context→prompt  │
   └────────┬────────┘                    └────────┬─────────┘
            │  nodi                                │ legge
            ▼                                      ▼
   ┌──────────────────────────────────────────────────────────┐
   │                        graph/                            │
   │   nodes · links (a<b) · entities · hub · autolink        │
   └────────────────────────┬─────────────────────────────────┘
                            │
   ┌────────────────────────▼─────────────────────────────────┐
   │  core/  config · db (adattatore PDO) · schema · text ·    │
   │         security · log                                   │
   └────────────────────────┬─────────────────────────────────┘
                            ▼
                 SQLite  oppure  il MySQL dell'ospite
                       (tabelle con prefisso proprio)

   quality/  salute · banco di prova · feedback · riconciliazione
   api/v1/   JSON con cancello        ui/  3D, 2D, console, scheda
   bus/      orchestrazione fra agenti (PHP e Python, stesso formato)
```

## Le decisioni che contano

### 1. Un solo database

`brain_pdo()` prova, in ordine: PDO iniettato → `$GLOBALS['BRAIN_PDO']` →
`brain_host_pdo()` dell'ospite → DSN di config → file SQLite proprio.

Il motivo non è tecnico, è di igiene: due database che non si parlano
producono due verità. Se l'applicazione ospite ha già il suo, il cervello ci
vive dentro con un prefisso di tabella suo (`brain_` di default) e non
interferisce.

Tutte le differenze fra i driver stanno in **un solo file**, `core/db.php`:
`brain_upsert()`, `brain_insert_ignore()`, `brain_has_table()`. Il resto del
modulo scrive SQL portabile e non sa su cosa sta girando. Le date le scrive
sempre PHP (`brain_now()`, UTC): niente `datetime('now')` contro `NOW()`.

### 2. Le sinapsi sono non orientate e canoniche

Una sinapsi si salva sempre con `node_a < node_b`. Non è un dettaglio: senza
questa regola la stessa coppia esiste in due versi, l'indice unico non serve a
niente, i doppioni gonfiano la centralità e il reranking mente. `brain_link()`
scambia i capi da solo, rifiuta gli auto-collegamenti e tiene una cache in
memoria per non ripetere la stessa scrittura nello stesso processo.

### 3. Il grafo si connette senza pagare O(n²)

Quattro meccanismi, tutti O(n·k):

| meccanismo | cosa collega | costo |
|---|---|---|
| **sequenziale** | chunk *i* ↔ chunk *i+1* dello stesso documento | 1 per chunk |
| **concetto** | rappresentante ↔ nodo `kw-*` | ≤ `graph.max_keywords` |
| **diretto** | rappresentante ↔ pochi nodi con la stessa parola | ≤ keywords × `direct_links_per_keyword` |
| **entità** | nodi che condividono ≥ N entità del dizionario | ≤ `entity_max_links` |
| **hub** | rappresentante ↔ hub della fonte ↔ radice | 1 |

Gli hub e i nodi-concetto sono **tessuto connettivo, non risposte**: il
recupero li esclude via SQL (`rag.exclude_id_prefixes` + `section NOT IN`).
Senza quell'esclusione dominavano la classifica col loro peso alto e
riempivano il contesto di rumore.

### 4. Ogni nodo nasce riservato

`ingest.default_visibility = "admin"`. La promozione a `public` è un gesto
umano, uno per uno, dalla console o via API. Il motivo si vede appena si fa il
primo import: mille documenti interni diventerebbero citabili da chiunque nel
giro di un pomeriggio.

La diagnosi (`health`) conta i nodi rimasti senza visibilità e la riparazione
li **chiude**, non li apre.

### 5. Il testo non conosce nessuna lingua

`core/text.php` non contiene una sola regola italiana o inglese. Stopwords,
piegatura degli accenti, prefissi di elisione e sinonimi arrivano dai pacchetti
lingua (`config/synonyms.*.json`). Aggiungere il tedesco significa aggiungere un
file JSON e citarlo in `text.lang_packs`: non si tocca il codice.

## Lo schema

Tredici tabelle, prefisso configurabile:

| tabella | a cosa serve |
|---|---|
| `nodes` | i neuroni: id, percorso, contenuto, peso, visibilità, fonte, hash, punteggio dei voti, stato di revisione |
| `links` | le sinapsi, canoniche e uniche |
| `entities` / `node_entities` | le entità riconosciute e chi le contiene |
| `files` | il registro dell'ingestione (hash, dimensione, data): serve l'idempotenza |
| `knowledge` | l'indice dei .md di conoscenza leggibili da un umano |
| `chat_log` | memoria episodica: cosa è stato chiesto e se c'era una risposta |
| `feedback` | i voti e le correzioni (un voto per nodo/impronta/giorno) |
| `eval_runs` / `eval_questions` | il banco di prova e la sua storia |
| `activity` | cosa ha fatto il cervello: alimenta il feed e la `rev` del polling |
| `meta` | versione dello schema, istantanee dei contatori |
| `jobs` | coda per lavori differiti (predisposta) |

Indici pensati per il milione di nodi: unico su `(node_a,node_b)`, più
`visibility`, `section`, `source`, `weight`, `path` (con prefisso 191 su MySQL),
`node_entities(entity)`, e le date su `activity` e `chat_log`.

> Limite dichiarato: il recupero usa `LIKE '%termine%'`, che **non usa
> l'indice**. Fino a qualche decina di migliaia di nodi è istantaneo. Oltre,
> serve un indice full-text: su MySQL `ALTER TABLE brain_nodes ADD FULLTEXT
> ft_content (content)`, su SQLite una tabella FTS5 affiancata. Il posto in cui
> innestarlo è uno solo: la prima query di `brain_retrieve()`.

## Perché niente embedding

Perché il bersaglio è l'hosting condiviso: niente GPU, niente servizio esterno,
niente chiave da pagare, niente dato che esce dall'azienda. Un motore lessicale
con grafo, sinonimi curati e reranking arriva molto lontano su un dominio
chiuso — e quando sbaglia si capisce **perché** ha sbagliato, cosa che con un
vettore di 1536 numeri non succede.

Se un giorno vuoi gli embedding, il punto d'innesto è `rag/retrieve.php`: si
aggiunge un secondo canale di candidati e si fondono i punteggi. Il resto
(muro, rerank, diversità, contesto, prompt) resta identico.
