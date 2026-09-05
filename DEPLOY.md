# DEPLOY

## Produzione consigliata
- HTTPS/TLS
- PHP-FPM 8.2+
- SQLite su disco locale persistente; per crescita multi-server migrare a MySQL/MariaDB tramite adapter DB
- backup cifrato giornaliero del DB e degli upload
- rate limiting a livello web server/WAF
- cron per `bin/email-worker.php`, `bin/sync-neuralog.php` e geocoder solo con provider autorizzato

## Permessi
- codice: sola lettura dal processo web dove possibile
- `data/`: lettura/scrittura
- `storage/`: lettura/scrittura
- bloccare accesso HTTP diretto a `.sqlite`, log, file temporanei e configurazioni

## Geocoding
La build non usa coordinate sintetiche per indicazioni stradali. Le coordinate vengono promosse a `CITY/STREET/EXACT` solo dopo fonte verificata o provider geocoding configurato.
