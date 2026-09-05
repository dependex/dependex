# INTEGRATIONS

## Moduli forniti dall'utente
- SIC-ID Engine
- Neuralog/Cortex
- Network Visual Engine
- World Map
- Dashboard
- DRX Engine
- Email Machine
- Chat AI
- DB Model

Gli adapter vivono nel layer `modules/` e nel bridge host.

## Geocoding
Provider opzionale tramite `bin/geocode-clubs.php`. In produzione usare provider conforme alle condizioni d'uso; per batch ricorrenti preferire servizio dedicato/self-hosted.

## PDF
Il frontend supporta stampa/PDF browser. Il server può usare Chromium headless se disponibile; predisporre provider PDF dedicato sul server per automazioni massive.

## Web3
Deferito. L'adapter dovrà essere collegato solo dopo revisione compliance e sicurezza.
