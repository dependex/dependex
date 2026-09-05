# INSTALL — OLTRE / DEPENDEX

## Requisiti
- PHP 8.2+
- PDO SQLite abilitato
- HTTPS obbligatorio in produzione
- directory `data/` e `storage/` scrivibili dal processo PHP
- cron disponibile per worker email/geocoding/sync quando abilitati

## Prima installazione
1. Caricare il progetto completo sul document root.
2. Copiare `.env.example` in configurazione server/hosting e impostare i segreti fuori dal web root quando possibile.
3. Verificare permessi `data/` e `storage/`.
4. Aprire `/install.php` una sola volta.
5. Creare il primo SUPERADMIN.
6. Salvare il recovery code mostrato una sola volta.
7. Accedere da `/login.php`.
8. Proteggere o rinominare `install.php` dopo l'inizializzazione.

## Domini
- `oltre.social` → brand Italia OLTRE
- `dependex.social` → brand internazionale DEPENDEX

Il core riconosce l'host e seleziona brand/locale di default.
