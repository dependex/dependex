# OLTRE — DIPENDEX Site V2

Brand pubblico: **OLTRE**  
Ecosistema/metodo: **DIPENDEX**  
Payoff: **AL CLUB. COL CLUB.**  
Acronimo comunicativo: **ALCOL = Ascolto e Legami Creano Orientamento e Libertà**  
Token unico: **DRX = Dialogo · Relazioni · eXperienza**  
Identità universale: **SIC-ID-XXXXXXXXXXXX**

## Cosa contiene
- Sito/APP PHP mobile-first
- Index pubblica OLTRE
- Metodo DIPENDEX
- Privacy/Security page
- Login semplice
- Recupero password senza email: dispositivo fidato + recovery code + fallback admin
- Dashboard utente
- Club Hub
- Network Tree/Grafo interattivo
- Academy Hudolin/SAT/Lifestyle
- Eventi, Event Factory e Graphic Studio
- DAO Forum
- Rank DRX
- Leaderboard
- Document Factory
- Control Center
- DRX Wallet
- Cortex UI
- SQLite pre-popolato con censimento pubblico V1
- Loghi SVG OLTRE/DIPENDEX + styleboard PNG

## Installazione
1. Carica il contenuto su hosting PHP 8.2+.
2. Abilita `pdo_sqlite`.
3. Rendi scrivibili `data/` e `storage/`.
4. Apri `/install.php`.
5. Crea il SUPERADMIN e salva il recovery code.
6. Accedi da `/login.php`.
7. Dopo installazione, proteggi o rinomina `install.php`.

## Test
- SQLite integrity: `ok`
- Conteggi DB: `{"network_entities": 194, "clubs": 126, "ranks": 9, "academy_modules": 10}`
- I file PHP sono verificati con `php -l`.

## Note operative
Il censimento Club è V1 incrementale: non rappresenta ancora tutti i Club italiani. È pronto per essere esteso regione per regione senza cambiare struttura DB, mappa o Neuralog.


## V3 CORE additions
- Global Hudolin/CAT registry: 344 records, 248 individual club rows.
- Addiction Pathway Engine schema
- Human Profile / Assessments schema
- Lifestyle dimensions + time-series schema
- Mission Engine
- Sobriety + Daily Access + milestone engine
- Achievement / Certificate schema
- Club metrics + multiplication schema
- Social Impact / Fundraising / Volunteer schema
- Finance / Treasury schema
- Workflow / Form Factory schema
- Notifications schema
- International Network page/API
- Registry, Finance, Workflow, Integrations pages
- Adapter contracts awaiting user's modules: SIC-ID, Neuralog, Network Visual, Map, DRX, Email Machine, DB Model/Dashboard, Chat AI.
- Blockchain intentionally deferred.

SQLite integrity: ok


## V4 domain & network deployment
- Italia: https://oltre.social
- Internazionale: https://dependex.social
- Tutti i record censiti sono ora inseriti anche in `network_entities`.
- Tutti i Club hanno `map_enabled=1` e `network_enabled=1`.
- Scope:
  - `OLTRE_ITALY`
  - `DEPENDEX_GLOBAL`
- Views SQLite:
  - `v_oltre_italy_network`
  - `v_dependex_global_network`
  - `v_all_clubs`
- API:
  - `api.php?action=network&scope=ITALY`
  - `api.php?action=network&scope=GLOBAL`
- Statistiche V4: {"total_network_entities": 538, "total_clubs": 374, "italy_entities": 361, "italy_clubs": 224, "global_entities": 177, "global_clubs": 150}
- SQLite integrity: ok

Nota: i record storici/non verificati restano visibili solo se il frontend decide di mostrarli, mantenendo `verification_status` per distinguere rete corrente e presenza storica.


## MASTER BUILD — Core operativo in sviluppo
- SIC-ID legacy migrati al formato universale Crockford + checksum; vecchi ID mantenuti nei campi legacy dove previsto.
- DRX ledger idempotente, daily access +1, sobrietà +1/giorno, DRX qualificanti/non qualificanti.
- Academy a corsi/lezioni con progressi e reward.
- Event registration, DAO proposal/vote, Finance OS base reale, Document Factory stampabile.
- Geocode queue e provider adapter; coordinate casuali non vengono più considerate definitive.
- Multilingua scaffold 11 lingue.
- Blockchain resta disattivata.
