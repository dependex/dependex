# Runbook — cosa fare quando

## Ogni giorno (automatico)

```cron
0 * * * *   php bin/brain ingest --all      # ingestione, ogni ora
30 3 * * *  php bin/brain health --fix      # igiene del grafo, di notte
45 3 * * *  php bin/brain eval              # il numero, ogni notte
0 4 * * 0   php bin/brain export --out=data/backup-$(date +\%F).jsonl
```

## Ogni settimana (a mano, 10 minuti)

Apri `ui/console.php?key=…` e guarda **tre riquadri**:

1. **Dove manca conoscenza** — domande rimaste senza risposta. È l'elenco di
   cosa scrivere.
2. **Imparato dalle conversazioni** — approva, pubblica o rifiuta. Non lasciar
   crescere la coda: dopo un mese non la guarda più nessuno.
3. **Diagnosi** — se gli orfani crescono, qualcosa nell'ingestione non collega.

---

## Sintomi e cause

### "Non trova una cosa che c'è di sicuro"

```bash
php bin/brain search "le parole esatte del documento" --admin
```

| esito | causa | rimedio |
|---|---|---|
| lo trova con `--admin` ma non senza | è riservato | `brain_node_set_visibility($id,'public')` |
| non lo trova neanche con `--admin` | non è mai stato ingerito | `php bin/brain ingest --dry` e guarda se il file compare |
| il file compare ma il nodo no | testo non estratto (PDF senza `pdftotext`) | `php bin/brain doctor` |
| lo trova ma in fondo | la domanda usa parole diverse dal documento | aggiungi il sinonimo, poi rilancia `eval` |

Regola: **aggiungi la domanda al banco di prova prima di sistemare**. Così
quella cosa non si romperà più in silenzio.

### "Risponde con roba di un altro argomento"

Quasi sempre il dizionario dei sinonimi è troppo largo. Guarda i termini
generati:

```bash
php -r 'require "brain.php"; print_r(brain_query_terms_map("la tua domanda"));'
```

Se vedi espansioni che non c'entrano, togli quella voce dal pacchetto lingua.
In alternativa abbassa `rag.synonym_weight` (0,45 → 0,3) o
`rag.synonym_cap_ratio` (0,5 → 0,3).

### "È lento"

| numero di nodi | cosa fare |
|---|---|
| < 20.000 | non dovrebbe esserlo: guarda gli indici con `php bin/brain health` |
| 20.000 – 200.000 | abbassa `rag.candidate_limit` (150 → 80) e `rag.max_terms` (16 → 10) |
| > 200.000 | serve un indice full-text: `LIKE` non usa gli indici. Vedi `02-ARCHITETTURA.md` |

L'ingestione lenta è quasi sempre l'autolink: abbassa `graph.max_keywords` e
`graph.direct_links_per_keyword`, oppure ingerisci con `autolink => false` e
lancia `php bin/brain reindex` di notte.

### "Il grafo è pieno di orfani"

Normale subito dopo un `demo-clear` o una cancellazione in blocco.

```bash
php bin/brain health --fix
php bin/brain reindex
```

Se restano: quei nodi non hanno parole chiave in comune con nessuno. Verifica
che `graph.concept_nodes` sia `true` e che il dizionario non sia vuoto.

### "Il cervello 3D è vuoto o non si vede"

1. `api/v1/graph.php?stat=1` risponde? Se dà 403, manca la chiave o le API
   pubbliche sono spente.
2. Dice `nodes: 0`? Allora è il database a essere vuoto: `demo-seed` o `ingest`.
3. Si vede ma è in 2D? È il ripiego senza WebGL: normale se Three.js non è
   raggiungibile. Scarica `three.min.js` in `ui/vendor/` per avere la versione
   WebGL. **Il ripiego non è un errore**: disegna gli stessi dati.

### "Un utente ha votato male un nodo giusto"

Il voto è clampato a ±2 nel rerank: non può ribaltare niente. Se serve:

```sql
UPDATE brain_nodes SET feedback_score = 0 WHERE id = '…';
```

Guarda anche la correzione lasciata: `GET api/v1/feedback.php?list=1&key=…`.
Spesso non è un dispetto: è un documento davvero sbagliato.

### "Hit-rate crollata da ieri"

```bash
php -r 'require "brain.php"; print_r(brain_rows("SELECT ran_at,hit_rate,mrr FROM ".brain_t("eval_runs")." ORDER BY id DESC LIMIT 10"));'
```

Cerca cosa è cambiato in mezzo: un import massiccio (che diluisce il pool di
candidati), una modifica ai sinonimi, un `health --fix` che ha tolto sinapsi
vere. `activity` ha la cronologia.

### "Database is locked" (SQLite)

Succede con più scrittori insieme. Il modulo apre già in WAL con
`busy_timeout=8000`. Se persiste: non lanciare `ingest` e `health --fix`
nello stesso minuto (guarda il cron), oppure passa a MySQL.

---

## Migrazioni

### Da SQLite a MySQL

```bash
php bin/brain export --out=brain.jsonl                     # 1. esporta
# 2. punta config/brain.local.json al DSN MySQL
php bin/brain install                                       # 3. crea le tabelle
php bin/brain import --in=brain.jsonl                       # 4. importa
php bin/brain health --fix && php bin/brain eval            # 5. verifica
```

L'ultimo passo non è facoltativo: se l'hit-rate non è quella di prima,
qualcosa non è arrivato.

### Aggiornare il modulo

1. `php bin/brain export --out=data/backup-pre-update.jsonl`
2. sostituisci i file **tranne** `config/brain.local.json`, `config/dictionary.json`,
   `config/benchmark.json` e la cartella `data/`
3. `php bin/brain install` (le colonne nuove si aggiungono da sole)
4. `php tests/run.php && php bin/brain health && php bin/brain eval`

Se l'hit-rate cambia, il CHANGELOG dice perché.

---

## Ripristino da backup

```bash
php bin/brain install
php bin/brain import --in=data/backup-2026-08-18.jsonl
php bin/brain reconcile --fix     # allinea registri e grafo
php bin/brain health --fix
php bin/brain eval                # deve tornare il numero di prima
```

---

## Emergenza: togliere tutto dal pubblico, subito

```sql
UPDATE brain_nodes SET visibility = 'admin' WHERE visibility = 'public';
```

Oppure, in `config/brain.local.json`:

```json
{ "security": { "public_api": false } }
```

Ha effetto immediato: nessun riavvio, nessuna cache. Poi con calma si guarda
cosa era uscito, in `chat_log` e in `activity`.
