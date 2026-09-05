# Installazione

## Cosa serve

| | |
|---|---|
| PHP | 8.1 o superiore (`php -v`) |
| Estensioni obbligatorie | `pdo`, `pdo_sqlite` **oppure** `pdo_mysql`, `mbstring`, `json` |
| Estensioni facoltative | `zip` (per docx/xlsx), `curl` (per l'ingestione da URL, spenta di default) |
| Programmi facoltativi | `pdftotext` (per i PDF; senza, i PDF si leggono solo in parte) |
| Composer | **no** |
| Servizi esterni | **nessuno** |

Controlla tutto in una volta:

```bash
php bin/brain doctor
```

`doctor` non installa niente: guarda l'ambiente e dice cosa manca, compresa la
chiave amministratore e la scrivibilità della cartella `data/`.

## Installazione in 3 minuti (SQLite)

```bash
cp -r company-brain /percorso/del/tuo/progetto/
cd /percorso/del/tuo/progetto/company-brain
php bin/brain install
php bin/brain demo-seed        # facoltativo: dati finti per vedere se funziona
php bin/brain eval             # e per avere subito un numero
```

Il database finisce in `data/brain.sqlite`. Non c'è altro da fare.

## Con MySQL 8 (stesso codice)

In `config/brain.local.json`:

```json
{
  "db": {
    "use_host_pdo": false,
    "dsn": "mysql:host=localhost;dbname=miodb;charset=utf8mb4",
    "user": "utente",
    "pass": "",
    "table_prefix": "brain_"
  }
}
```

La password **non** si scrive nel file: si mette nell'ambiente.

```bash
export BRAIN_DB_PASS='...'
php bin/brain install
```

Variabili d'ambiente riconosciute: `BRAIN_DB_DSN`, `BRAIN_DB_USER`,
`BRAIN_DB_PASS`, `BRAIN_DB_PREFIX`, `BRAIN_SQLITE_PATH`, `BRAIN_LANGUAGE`,
`BRAIN_ADMIN_KEY`, `BRAIN_BUS_ROOT`.

> Provato davvero su SQLite. Il percorso MySQL è scritto per essere identico
> (tipi, upsert, indici sono già differenziati nel codice) ma **non è stato
> eseguito su un MySQL vero in questo ambiente**: prima di andare in produzione
> lancia `php bin/brain install && php tests/run.php` sul tuo MySQL.

## Dentro un'applicazione che esiste già

La regola è una sola: **un solo database**. Se l'app ha già un PDO, il cervello
ci vive dentro.

```php
$GLOBALS['BRAIN_PDO'] = $pdo;                 // PRIMA del require
function brain_host_is_admin(): bool {        // chi è admin, secondo la tua app
    return !empty($_SESSION['is_admin']);
}
require_once __DIR__ . '/company-brain/brain.php';
```

Esempio completo: `examples/install-in-existing-php-app.php`.
Per WordPress (che non usa PDO): `examples/wordpress-note.md`.

## La chiave amministratore

Non sta in nessun file. Solo nell'ambiente:

```bash
export BRAIN_ADMIN_KEY="$(openssl rand -hex 24)"
```

Su hosting condiviso, dove non puoi esportare variabili, mettila nel
`.htaccess` di **livello superiore** (mai dentro `company-brain/`):

```apache
SetEnv BRAIN_ADMIN_KEY "la-tua-chiave"
```

**Senza chiave gli endpoint riservati restano chiusi**, non aperti. È voluto.

## Permessi

```bash
chmod -R 755 company-brain
chmod -R 775 company-brain/data      # l'unica cartella in cui si scrive
```

Se `data/` sta sotto la document root, verifica che il `.htaccess` faccia
effetto (`curl https://tuosito/company-brain/data/brain.sqlite` deve dare 403).
Su nginx il `.htaccess` **non viene letto**: vedi `docs/06-SICUREZZA.md`.

## Verifica finale

```bash
php bin/brain doctor      # ambiente
php bin/brain health      # grafo: orfani, pendenti, doppioni
php bin/brain eval        # recupero: hit-rate e MRR
php tests/run.php         # 195 test sul codice
```

Se questi quattro comandi sono verdi, l'installazione è a posto.

## Disinstallare

```bash
php bin/brain export --out=backup.jsonl    # prima il backup
```

Poi cancella la cartella e, se hai usato MySQL, le tabelle con il tuo prefisso.
Il modulo non ha toccato nient'altro.
