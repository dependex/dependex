# OLTRE / DEPENDEX — ADVANCED MVP HOST QUICKSTART

Release: 2026-08-18

## Domini
- Italia: https://oltre.social
- Internazionale: https://dependex.social

## Requisiti minimi host
- PHP 8.2+
- PDO SQLite
- mbstring
- openssl
- JSON
- session support
- HTTPS obbligatorio in produzione

## Upload
Caricare l'intero contenuto dello ZIP dentro `public_html` del dominio di test/produzione.

## Permessi
Le directory seguenti devono essere scrivibili dal processo PHP:
- `data/`
- `storage/`
- eventuale `knowledge/generated/` se si usa il Knowledge Builder sull'host

## Primo controllo
Aprire:
- `/index.php`
- `/login.php`
- `/world-map.php` (alias stabile del World Club Explorer)
- `/global-network.php`

Per area admin:
- `/admin.php`
- `/ncke-admin.php`

## Company Brain
NCKE parte in modalità adaptive:
SQLite FTS5 + Neuralog Graph + Markdown knowledge.
I provider AI vengono rilevati a runtime dai file `.env*` autorizzati.

Le API key NON vengono copiate in DB/Markdown/log/frontend.

## Stato release
PHP controllati: 226
Errori PHP: 0
JS controllati: 2
Errori JS: 0
SQLite integrity: ok
Registry nodes: 546
Local clubs: 352
Countries: 36
NCKE documents: 91
NCKE chunks: 791

## Nota
Questa è una ADVANCED MVP: include praticamente tutto il lavoro svolto finora, ma alcune funzioni avanzate che dipendono da provider AI, geocoder esterno, SMTP o infrastruttura esterna richiedono le credenziali/config dell'host.
