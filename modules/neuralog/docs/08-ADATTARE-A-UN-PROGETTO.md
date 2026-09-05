# Adattare il cervello a un progetto qualsiasi

Dieci passi. Vanno bene per un e-commerce, uno studio dentistico, una casa
editrice, un'officina. Alla fine c'è l'esempio completo di uno studio medico:
è quello che chiarisce tutto.

---

## I 10 passi

### 1. Copia e controlla l'ambiente

```bash
cp -r company-brain /percorso/del/progetto/
cd /percorso/del/progetto/company-brain
php bin/brain doctor
```

Se `doctor` è verde, vai avanti. Se manca `pdftotext` e i tuoi documenti sono
PDF, risolvi adesso: dopo ti chiederai perché il cervello "non ha capito
niente".

### 2. Crea `config/brain.local.json`

Non toccare `brain.config.json`: quello è il default del modulo e va
sovrascritto dagli aggiornamenti. Il tuo file sovrascrive solo le chiavi che
scrivi.

```json
{
  "brain": { "label": "Cervello dello Studio", "language": "it",
             "public_contact": "info@tuodominio.it" },
  "db":    { "table_prefix": "brain_" },
  "ingest":{ "roots": ["../documenti-studio"], "default_visibility": "admin" },
  "ui":    { "brand_label": "Studio X", "theme": { "--brain-accent": "#2f7d8f" } }
}
```

### 3. Metti la chiave nell'ambiente

```bash
export BRAIN_ADMIN_KEY="$(openssl rand -hex 24)"
```

Senza chiave, console e API riservate restano chiuse. È voluto.

### 4. Installa e prova a vuoto

```bash
php bin/brain install
php bin/brain demo-seed
php bin/brain eval          # deve dare un numero
php bin/brain health        # deve essere pulito
```

Apri `ui/brain-3d.php?key=...`: se il cervello pulsa, il motore funziona.
Adesso togli i dati finti: `php bin/brain demo-clear`.

### 5. Scrivi il **dizionario delle entità** — è il passo che fa la differenza

`config/dictionary.json` (poi punta lì `entities.dictionary`).

Ci vanno le **parole che nella tua azienda hanno un significato preciso**: nomi
di prodotti, servizi, reparti, ruoli, sedi, procedure, i cognomi delle persone
di riferimento. Tutto minuscolo, senza accenti, anche multi-parola.

Regola pratica: **20-60 voci scritte bene valgono più di 500 buttate dentro.**
Due documenti che condividono almeno due entità si collegano da soli: se metti
parole generiche ("cliente", "documento", "servizio"), colleghi tutto con tutto
e il grafo diventa una nebbia.

### 6. Scrivi i **sinonimi** — come parla la gente vera

`config/synonyms.it.json`, sezione `synonyms`. Non è un vocabolario: è
l'elenco delle **parole che i tuoi clienti usano al posto delle tue**.

```json
"appuntamento": ["visita", "prenotazione", "seduta", "quando venire"],
"tariffa":      ["costo", "prezzo", "quanto costa", "onorario"]
```

Da dove le prendi: dalle mail ricevute, dai messaggi WhatsApp, da chi risponde
al telefono. È la mezz'ora meglio spesa dell'intera installazione.

L'espansione è bidirezionale: chi scrive "quanto costa" trova anche i documenti
che dicono "tariffa", e viceversa.

### 7. Scegli cosa dare in pasto, e con quale visibilità

Due mucchi, non uno:

| mucchio | esempi | visibilità |
|---|---|---|
| **pubblico** | listini, FAQ, orari, schede servizio, condizioni | `public` |
| **interno** | protocolli, verbali, listini fornitori, casi | `admin` |

```bash
php bin/brain ingest --path=../documenti/pubblici --visibility=public --all
php bin/brain ingest --path=../documenti/interni  --all      # admin di default
php bin/brain ingest --dry                                    # prima, sempre
```

`--dry` ti dice cosa prenderebbe **senza scrivere niente**. Guardalo: è il
momento in cui ti accorgi che stavi per ingerire la cartella dei backup.

### 8. Riscrivi il banco di prova con le tue domande

`config/benchmark.json` (poi punta lì `eval.benchmark`).

Dieci-quindici domande **vere**, quelle che vi fanno davvero, con una
sottostringa che deve comparire nella risposta:

```json
{ "q": "quanto costa la prima visita", "expected": "80 euro", "tag": "tariffe" }
```

```bash
php bin/brain eval
```

Il primo numero sarà basso. È giusto così: adesso hai qualcosa da migliorare
che non è un'opinione.

### 9. Collega il cervello all'applicazione

```php
$GLOBALS['BRAIN_PDO'] = $pdo;                        // se ne hai già uno
function brain_host_is_admin(): bool { return !empty($_SESSION['is_admin']); }
require_once __DIR__ . '/company-brain/brain.php';
```

E fai entrare i dati che nascono nell'applicazione (schede, ticket, articoli)
con `brain_ingest_text()`, usando un `path` stabile. Vedi
`examples/install-in-existing-php-app.php`.

### 10. Mettilo in manutenzione automatica

```cron
0 * * * *   cd /percorso/company-brain && php bin/brain ingest --all   >> data/cron.log 2>&1
30 3 * * *  cd /percorso/company-brain && php bin/brain health --fix   >> data/cron.log 2>&1
45 3 * * *  cd /percorso/company-brain && php bin/brain eval           >> data/cron.log 2>&1
0  4 * * 0  cd /percorso/company-brain && php bin/brain export --out=data/backup-$(date +\%F).jsonl
```

Poi, una volta a settimana, apri la console e guarda due cose:
**"dove manca conoscenza"** (domande rimaste senza risposta) e i nodi più
bocciati. Sono l'elenco della spesa di cosa scrivere la settimana dopo.

---

## Esempio completo: uno studio dentistico

Studio con tre poltrone, due segretarie, un sito con un modulo di contatto.
Documenti: listino, protocolli clinici, informative privacy, note interne.

### Il dizionario (`config/dictionary.json`)

```json
{
  "entities": [
    "prima visita", "igiene dentale", "ablazione tartaro", "sbiancamento",
    "otturazione", "devitalizzazione", "estrazione", "impianto", "corona",
    "ortodonzia", "apparecchio", "mascherine trasparenti", "protesi mobile",
    "radiografia", "panoramica", "anestesia locale",
    "dottoressa bianchi", "dottor rossi", "igienista",
    "studio centro", "studio nord",
    "consenso informato", "informativa privacy", "richiamo di controllo",
    "convenzione", "fondo sanitario", "pagamento rateale"
  ],
  "aliases": {
    "ablazione tartaro": ["pulizia dei denti", "detartrasi"],
    "mascherine trasparenti": ["allineatori", "invisalign"],
    "prima visita": ["prima seduta", "visita iniziale"]
  }
}
```

Trenta voci. Le persone ci sono (chi le cerca per nome le trova), le
prestazioni ci sono, le sedi ci sono. Non c'è "dente": è ovunque, non
distinguerebbe niente.

### I sinonimi (`config/synonyms.it.json`)

```json
"appuntamento": ["visita", "prenotare", "prenotazione", "quando", "seduta"],
"tariffa":      ["costo", "prezzo", "quanto costa", "onorario", "preventivo"],
"dolore":       ["male", "fa male", "urgenza", "emergenza"],
"orario":       ["aperto", "apertura", "quando siete"],
"parcheggio":   ["posteggio", "dove parcheggio", "come arrivo"]
```

Presi dalle domande vere del modulo di contatto.

### Cosa si ingerisce

| documento | dove | visibilità | perché |
|---|---|---|---|
| listino prestazioni | `documenti/pubblici/` | `public` | è la domanda numero uno |
| orari e sedi | `documenti/pubblici/` | `public` | domanda numero due |
| FAQ e preparazione alle sedute | `documenti/pubblici/` | `public` | risparmia telefonate |
| informativa privacy | `documenti/pubblici/` | `public` | va data comunque |
| protocolli clinici | `documenti/interni/` | `admin` | serve allo staff, non ai pazienti |
| verbali e note fornitori | `documenti/interni/` | `admin` | non riguarda nessun altro |
| **cartelle cliniche** | **da nessuna parte** | — | dati sanitari: non entrano, punto |

Quest'ultima riga è la più importante di tutta la pagina. Il muro di
visibilità è un accorgimento tecnico, non un'autorizzazione al trattamento di
dati sanitari. Quello che non deve stare in un indice, non ci si mette.

### Il banco di prova (`config/benchmark.json`)

```json
{ "top_k": 5, "questions": [
  { "q": "quanto costa la prima visita",          "expected": "prima visita" },
  { "q": "quanto costa la pulizia dei denti",     "expected": "ablazione" },
  { "q": "a che ora aprite il sabato",            "expected": "sabato" },
  { "q": "dove si parcheggia",                    "expected": "parcheggio" },
  { "q": "si può pagare a rate",                  "expected": "rateale" },
  { "q": "quanto dura l'apparecchio trasparente", "expected": "mascherine" },
  { "q": "cosa faccio se ho un forte mal di denti","expected": "urgenza" },
  { "q": "siete convenzionati",                   "expected": "convenzione" },
  { "q": "chi fa l'igiene dentale",               "expected": "igienista" },
  { "q": "come si prenota una visita",            "expected": "prenot" }
]}
```

### Cosa aspettarsi, onestamente

| dopo | cosa succede |
|---|---|
| **il primo import** | hit-rate fra 0,5 e 0,7. Normale: i documenti sono scritti in "burocratese da studio", le domande in italiano da paziente |
| **dopo i sinonimi** | +0,1 / +0,2. È il singolo intervento che rende di più |
| **dopo aver scritto 5 FAQ** sulle domande fallite | 0,85-0,95. A questo punto il cervello è utile davvero |
| **dopo un mese di uso** | la lista "dove manca conoscenza" diventa la lista delle FAQ da scrivere. Il ciclo si chiude da solo |

Cosa **non** succede: il cervello non inizierà a rispondere a domande cliniche
che nessuno ha mai messo per iscritto. Non c'è magia: se una cosa non è scritta
da nessuna parte, non risulta. La cosa buona è che te lo dice, invece di
inventarsela.

### Dove finisce nel sito

- Una scheda di ricerca nella pagina "Contatti": `ui/embed-card.php` in un
  iframe, oppure `api/v1/search.php` con quattro righe di JavaScript.
- Il modulo di contatto, prima di inviare, mostra i tre risultati migliori:
  metà delle mail non parte più, perché la risposta era già lì.
- La console (`ui/console.php?key=…`) resta interna, alla segreteria.

---

## L'errore che fanno tutti

Ingerire tutto e mettere tutto pubblico "tanto poi sistemo". Non si sistema
più: nessuno rilegge mille documenti. Meglio partire da **dieci documenti
pubblici scelti bene** e allargare quando il banco di prova dice che vale la
pena.
