# Sicurezza

## Le brutte notizie per prime

Quattro cose che questo modulo **non** fa, e che devi sapere prima di
installarlo:

1. **Il `.htaccess` vale solo su Apache.** Su nginx, LiteSpeed o Caddy non
   viene nemmeno letto. Se sei su nginx e non aggiungi le regole nella
   configurazione del server, `data/brain.sqlite` è scaricabile da chiunque.
   Le regole pronte sono più sotto.
2. **La difesa CSRF sulle scritture è leggera.** Le API di scrittura accettano
   solo POST e, se c'è una sessione, verificano un token. Senza sessione il
   POST è l'unica barriera: un form auto-inviato da un sito terzo può votare.
   Il danno massimo è limitato (±1 al punteggio di un nodo, clampato a ±2 nel
   rerank, un voto al giorno per impronta), ma è un rischio accettato
   consapevolmente, non un buco che non abbiamo visto.
3. **Il rate limit è su file.** Funziona su hosting condiviso, dove non c'è
   Redis. Non regge un attacco distribuito: regge il clic ripetuto e lo script
   ingenuo.
4. **La guardia sui segreti si basa su pattern.** Riconosce `.env`, chiavi
   private, `api_key`, password scritte in chiaro. **Non** riconosce che
   `elenco-clienti.xlsx` è riservato. Quello lo decidi tu con la visibilità.

---

## Come si entra

| chi | come | cosa vede |
|---|---|---|
| riga di comando | ha già la shell | tutto |
| applicazione ospite | `brain_host_is_admin()` che scrivi tu | tutto |
| chiave | `?key=…` o header `X-Brain-Key`, confronto con `hash_equals` | tutto |
| pubblico | niente | solo `visibility='public'` |

La chiave sta **solo** nell'ambiente (`BRAIN_ADMIN_KEY`, nome configurabile).
Mai in un file, mai in config, mai nel database.

**Se la chiave manca, nessuno è admin.** Non "tutti sono admin perché manca il
controllo": il contrario. Fail-closed è la regola in ogni punto:

| situazione | comportamento |
|---|---|
| chiave assente | endpoint riservati chiusi |
| nodo senza visibilità | trattato come riservato; la riparazione lo chiude |
| schema non installato | 503, non "passa tutto" |
| database irraggiungibile | risultato vuoto, non eccezione con stack trace |

---

## Il muro sui dati

Doppia rete, già descritta in `05-RAG-E-QUALITA.md`:

1. in SQL, `visibility = 'public'` (uguale, non "diverso da admin");
2. in PHP, `brain_looks_secret()` su ogni riga prima che esca.

In più, per il pubblico, `api/v1/search.php` restituisce solo il **nome** del
file, non il percorso: la struttura delle cartelle interne non è
un'informazione da regalare.

---

## Cosa non finisce mai da nessuna parte

- **Nei log**: `brain_log()` e `brain_activity()` passano da `brain_redact()`.
  Un segreto che finisse in un messaggio d'errore diventa `[REDATTO]`.
- **Nel database**: gli IP non si salvano in chiaro. Si salva
  `substr(sha256(ip|contesto), 0, 24)`: basta per "un voto al giorno", non
  basta per risalire alla persona.
- **In rete**: nessuna chiamata in uscita. L'unica possibile è l'ingestione da
  URL, che è spenta e vincolata a una lista di host.

---

## Il filesystem

Sul progetto ospite il modulo **legge soltanto**. Scrive solo in `data/`
(database, `knowledge/`, `bus/`, `ratelimit/`, `brain.log`).

Non apre mai: `.env*`, `*secret*`, `*credential*`, `*password*`, `*.pem`,
`*.key`, `*.p12`, `*.keystore`, `*mnemonic*`, `*seed*`, `*.sqlite`, `*.db`.
L'elenco è in `ingest.secret_names`: allungalo con i nomi che contano da te.

---

## nginx: le regole che servono davvero

```nginx
location ^~ /company-brain/ {
    deny all;                                    # tutto chiuso
}
location ^~ /company-brain/api/ {
    allow all;
    include fastcgi_params;
    fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
location ^~ /company-brain/ui/ {
    allow all;
    include fastcgi_params;
    fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
location ~* /company-brain/.*\.(sqlite|db|jsonl|log|json|md|py)$ {
    deny all;                                    # mai, in nessun caso
}
```

Meglio ancora: sposta `data/` **fuori** dalla document root e punta
`paths.data_dir` a un percorso assoluto. Così non dipendi da nessuna regola di
server.

---

## Prova che funziona (fallo davvero)

```bash
curl -i https://tuosito/company-brain/data/brain.sqlite     # atteso: 403 o 404
curl -i https://tuosito/company-brain/config/brain.config.json  # atteso: 403 o 404
curl -i https://tuosito/company-brain/api/v1/health.php     # atteso: 403 senza chiave
curl -i https://tuosito/company-brain/ui/console.php        # atteso: pagina "accesso riservato"
curl -s  https://tuosito/company-brain/api/v1/search.php?q=test | head -c 200
# atteso: solo nodi pubblici, e nessun percorso interno
```

Se il primo comando ti restituisce un file binario, fermati e sistema il server
**prima** di mettere dentro qualunque documento.

---

## Se ti rubano la chiave

```bash
export BRAIN_ADMIN_KEY="$(openssl rand -hex 24)"   # cambiala
```

Non c'è nessuna sessione da invalidare: la chiave non viene mai salvata da
nessuna parte. Poi guarda `brain_activity_recent()` e la tabella `activity`
per capire cosa è stato fatto.
