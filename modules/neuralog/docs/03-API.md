# API

Due modi di usarlo: **da PHP** (chiami le funzioni) o **da HTTP** (`api/v1/`).
Le funzioni sono la strada principale; le API servono a chi sta fuori dal
processo PHP.

---

## API PHP

```php
require_once __DIR__ . '/company-brain/brain.php';
```

### Le cinque che userai davvero

| funzione | cosa fa |
|---|---|
| `brain_install()` | crea/aggiorna lo schema. Idempotente. |
| `brain_ingest_text($testo, $opts)` | fa entrare del testo nella conoscenza |
| `brain_search($q, $opts)` | recupero secco: righe ordinate per punteggio |
| `brain_ask($q, $opts)` | pacchetto pronto: contesto + fonti + system prompt |
| `brain_ask_complete($q, $risposta, $grounded)` | registra la conversazione e (se ancorata) impara |

`$opts` ricorrenti: `admin` (bool, apre l'area riservata), `n` (quanti
risultati), `visibility`, `source`, `path`, `title`, `section`.

```php
// far entrare qualcosa
brain_ingest_text("Il reso si accetta entro 30 giorni.", [
    'path'       => 'policy/resi',        // stabile: reingerire aggiorna, non duplica
    'title'      => 'Policy resi',
    'source'     => 'manuale',
    'visibility' => 'public',
]);

// chiedere
$pack = brain_ask('come funziona il reso?', ['admin' => false]);
$pack['grounded'];   // false = nel contesto non c'era niente: dillo, non inventare
$pack['sources'];    // [{node_id, label, path, score}]
$pack['context'];    // il blocco di testo con le fonti
$pack['prompt'];     // system prompt completo, da dare al TUO modello
```

**Il modulo non chiama nessun modello.** Prende `$pack['prompt']`, lo mandi tu
dove vuoi, e poi:

```php
brain_ask_complete($domanda, $rispostaDelModello, $pack['grounded']);
```

### Le altre

| funzione | cosa fa |
|---|---|
| `brain_forget_path($path)` | toglie tutto ciò che veniva da quel percorso |
| `brain_node_put()` / `brain_node_get()` / `brain_node_set_visibility()` | i nodi, uno per uno |
| `brain_link($a,$b)` / `brain_neighbors($id)` | le sinapsi |
| `brain_ingest_run($opts)` | un lotto di ingestione dal filesystem |
| `brain_health($fix)` | diagnosi (e riparazione) del grafo |
| `brain_eval_run()` | banco di prova del recupero |
| `brain_feedback_vote($nodeId,$voto,...)` | voto e correzione |
| `brain_reconcile($fix)` | registri contro grafo |
| `brain_stats()` | contatori per una dashboard |
| `brain_learn_pending()` / `brain_learn_approve()` / `brain_learn_reject()` | revisione di ciò che ha imparato |

---

## API HTTP — `api/v1/`

Tutte rispondono JSON con `Content-Type: application/json` e `Cache-Control:
no-store`. Tutte hanno `"ok": true|false`.

### Il cancello

| chi sei | come | cosa vedi |
|---|---|---|
| pubblico | niente | solo i nodi `visibility='public'`, con rate limit |
| admin | `?key=...` o header `X-Brain-Key` | tutto |
| admin (app ospite) | la tua `brain_host_is_admin()` | tutto |

Se `BRAIN_ADMIN_KEY` non è nell'ambiente, **nessuno è admin**. Se
`security.public_api` è `false`, chi non è admin prende 403 ovunque.

### `GET search.php?q=…&n=8`

```json
{"ok":true,"query":"reso","admin":false,"count":3,"ms":4,
 "results":[{"id":"doc-policy-resi-1","title":"Policy resi","path":"policy-resi.md",
             "section":"document","score":18.4,"snippet":"…","via_link":false}]}
```
Per il pubblico `path` è solo il nome del file: la struttura delle cartelle
interne non esce.

### `GET|POST ask.php?q=…`

Ritorna `question`, `grounded`, `sources`, `context`, `prompt`.
Con `POST ask.php?q=…&complete=1&answer=…&grounded=1` registra la risposta
finale (memoria + eventuale apprendimento in stato `pending`).

### `GET graph.php`

- `?stat=1` → risposta minima `{rev, stats}` per il polling
- `?n=400` / `?full=1` → nodi, sinapsi, colori, feed (il feed solo per admin)

`rev` cambia ad ogni attività: il client ridisegna solo quando serve.

### `GET health.php` — solo admin
Diagnosi completa. `?fix=1` ripara, ma solo in POST o con chiave esplicita: un
link aperto per sbaglio non deve far scattare una riparazione.

### `GET stats.php` — solo admin
Contatori, ultima esecuzione del banco di prova, domande rimaste senza
risposta, nodi più bocciati, quanti nodi appresi aspettano revisione.

### `POST feedback.php`
```
node_id=doc-…&vote=1|-1[&question=…&correction=…]
```
Pubblico, un voto per nodo/impronta/giorno. `GET ?list=1` (solo admin) legge le
correzioni, che **restano testo grezzo e non vengono mai pubblicate da sole**.

### Codici di errore

| codice | quando |
|---|---|
| 400 | parametro mancante o sbagliato |
| 403 | non sei admin, o le API pubbliche sono spente, o manca il token CSRF |
| 405 | scrittura tentata in GET |
| 429 | rate limit superato |
| 503 | schema non installato, o database occupato |

---

## Esempio: una chat sul tuo sito

```js
const r = await fetch('/company-brain/api/v1/ask.php?q=' + encodeURIComponent(q));
const d = await r.json();
if (!d.grounded) { mostra("Su questo non risulta niente."); return; }

// d.prompt va al TUO backend, che parla col modello con la TUA chiave
const risposta = await fetch('/mio-backend/chat', {
    method: 'POST',
    body: JSON.stringify({ system: d.prompt, user: q })
}).then(r => r.json());

mostraFonti(d.sources);   // ogni fonte con "utile / non utile"
```
