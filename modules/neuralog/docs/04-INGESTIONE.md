# Ingestione

Tre porte d'ingresso: **file**, **testo da codice**, **URL** (spenta).

---

## 1. Dai file

```bash
php bin/brain ingest --dry           # cosa prenderebbe, senza scrivere niente
php bin/brain ingest --batch=20      # un lotto
php bin/brain ingest --all           # tutti i lotti finché la coda è vuota
php bin/brain ingest --path=/altra/cartella --all
```

Le cartelle da guardare stanno in `ingest.roots` (default `data/inbox`).

### Cosa entra e cosa no

```json
"include": ["*.md", "*.txt", "*.csv", "*.pdf", "*.docx", "*.xlsx", "..."],
"exclude": ["*/node_modules/*", "*/.git/*", "*/vendor/*", "*/cache/*"],
"secret_names": ["*.env", ".env*", "*secret*", "*.pem", "*.key", "*password*", "..."]
```

L'ordine dei controlli è: **prima i segreti**, poi le esclusioni, poi le
inclusioni. Un file che assomiglia a un segreto non viene neanche aperto.
Se il testo estratto contiene comunque un pattern da segreto (una chiave
incollata dentro un verbale, per dire), il file viene saltato e la cosa
finisce nel log.

### Come diventa conoscenza

1. si estrae il testo (vedi sotto);
2. si scrive un `.md` leggibile in `data/knowledge/` — serve a un umano per
   controllare **cosa** ha letto il cervello;
3. si spezza in chunk con sovrapposizione (`chunk_chars` 1200,
   `chunk_overlap` 150, massimo `max_chunks_per_file` 24);
4. ogni chunk diventa un nodo, i nodi si collegano (sequenza, concetti,
   entità, hub);
5. il file finisce nel registro `files` con il suo hash.

### Idempotenza e ripresa

L'hash è `md5(dimensione|data-di-modifica)`. Un file già digerito e non
cambiato viene saltato: puoi lanciare `ingest` ogni ora da cron senza rifare
niente. Un file **vuoto o illeggibile viene comunque registrato**, altrimenti
resterebbe in coda per sempre e quattro file morti bloccherebbero i lotti.

Se un file rimpicciolisce, i nodi in eccesso vengono cancellati con le loro
sinapsi e le loro righe di entità: nessun id fantasma resta in giro.

### I formati

| formato | come |
|---|---|
| md, txt, csv, json, yml, sql, log, php, js, css, xml | lettura diretta |
| html, htm | `strip_tags` + entità decodificate; `<script>` e `<style>` buttati prima |
| docx, xlsx, pptx | `ZipArchive` nativa: si legge l'XML interno e si ripulisce. **Nessuna libreria.** |
| pdf | `pdftotext` se il sistema ce l'ha; altrimenti estrazione parziale dai flussi non compressi |
| rtf, xls, doc | solo i caratteri stampabili: meglio di niente, peggio di un convertitore |

> Il PDF senza `pdftotext` è il punto debole: dei PDF compressi (cioè quasi
> tutti) esce poco o niente. Se i tuoi documenti sono PDF, controlla di avere
> `pdftotext` — `php bin/brain doctor` te lo dice.

---

## 2. Dal codice — l'ingresso vero

Qualunque cosa sia testo può diventare conoscenza: un record CRM, un ticket,
una scheda prodotto, il verbale di una riunione.

```php
brain_ingest_text($testo, [
    'path'         => 'crm/cliente/4471',   // OBBLIGATORIO, e stabile
    'title'        => 'Scheda cliente 4471',
    'source'       => 'crm',                 // raggruppa e crea l'hub
    'section'      => 'document',
    'visibility'   => 'admin',               // 'public' solo se lo può vedere chiunque
    'id_prefix'    => 'crm',
    'autolink'     => true,
    'force'        => false,                 // true = ridigerisci anche se non è cambiato
]);
```

Ritorna `['ok','node_ids','chunks','links','skipped']`.

**`path` è la chiave di tutto.** È l'identità del documento: stesso `path` +
stesso contenuto = niente lavoro; stesso `path` + contenuto diverso =
sostituzione pulita. Se lo cambi a ogni salvataggio, ti ritrovi il doppio dei
nodi e nessuno che se ne accorge.

Per cancellare: `brain_forget_path('crm/cliente/4471')`.

### Il pattern giusto in un'applicazione

```php
function salva_scheda(array $s): void {
    // ... salvataggio tuo ...
    brain_ingest_text($testoDellaScheda, ['path' => 'schede/'.$s['id'], /* ... */]);
}
function cancella_scheda(string $id): void {
    brain_forget_path('schede/'.$id);
}
```

---

## 3. Dagli URL — spenta

```json
"url": { "enabled": false, "allow_hosts": [], "max_bytes": 500000, "timeout_sec": 8 }
```

Anche accendendola, valgono: solo `https`, solo host nella lista, niente
redirect, niente indirizzi privati. Un cervello che scarica qualunque cosa dal
web è una porta aperta, non una funzione.

---

## Visibilità: la decisione più importante

Tutto nasce `admin`. È scomodo il primo giorno e ti salva il secondo.

```bash
# vedi cosa c'è, prima di aprire qualcosa
php bin/brain search "listino" --admin
```

Poi, dalla console o via PHP, promuovi **quello che hai letto**:

```php
brain_node_set_visibility('doc-listino-prezzi-md-1', 'public');
```

Un modo pratico: ingerisci con `visibility: 'public'` solo le cartelle nate
per il pubblico (schede prodotto, FAQ, listini), e lascia `admin` tutto il
resto.

---

## Numeri veri, misurati

Su questa macchina (PHP 8.4, SQLite, container Linux):

| operazione | numero |
|---|---|
| semina di 194 documenti finti | **412 ms** → 194 neuroni, 3.006 sinapsi |
| installazione dello schema | 37 ms |
| banco di prova, 12 domande su 346 nodi | 35 ms totali (~3 ms a domanda) |
| suite di 195 test | 31 ms |

L'autolink è la parte cara: per ogni documento fa fino a
`max_keywords × direct_links_per_keyword` query `LIKE`. Se ingerisci decine di
migliaia di file, abbassa `graph.max_keywords` a 4 e
`direct_links_per_keyword` a 2, oppure ingerisci con `autolink => false` e poi
lancia `php bin/brain reindex` una volta sola, di notte.
