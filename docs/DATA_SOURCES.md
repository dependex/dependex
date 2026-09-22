# ORIGINE E TRACCIABILITÀ DELLE FONTI DATI — DEPENDEX.SOCIAL
**Versione:** 1.0 — 2026-09-22  
**Stato:** Catalogo Ufficiale delle Fonti Dati (Data Provenance & Lineage)  
**Database Primario:** `data/acat_community.sqlite` (Engine: SQLite 3 WAL Mode)

---

## 1. MAPPA DELLE TABELLE E DELLE FONTI DI ORIGINE

| Dataset / Tabella | Righe Attuali | Tipologia Dati | File Sorgente di Ingestione | Autorità / Ente Originario | Aggiornamento | Ruolo nell'Architettura |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **`cat_clubs_italy`** | **1.761** | Anagrafica e coordinate geografiche Club e associazioni d'Italia | `data/CENSIMENTO_CLUB_CAT_ITALIA_2026.csv` | Elenchi ufficiali ARCAT regionali, censimento AICAT, registri territoriali ASL/SerD | Continuo (2026) | **Single Source of Truth per la Mappa 2D e il Club Locator Geodesico** |
| **`dependex_world_registry`** | **2.064** | Rete mondiale dei Club (38 Paesi, 1.762 Italia + 302 esteri) | `data/DEPENDEX_World_Registry_Master.csv` | WACAT (World Association of CAT), Eurocare, federazioni nazionali europee e latinoamericane | Continuo (2026) | **Single Source of Truth per l'Organigramma e la Rete Internazionale** |
| **`crm_club_contacts`** | **1.768** | Recapiti istituzionali, telefoni, email e token di aggiornamento RFC 8058 | `data/CRM_CLUB_CONTATTI_MASTER_2026.csv` | Segreterie provinciali APCAT/ACAT e censimento diretto servitori | Continuo (2026) | **Single Source of Truth per l'Outreach, Nurturing e Aggiornamento Self-Service** |
| **`events`** | **11** | Eventi formativi, interclub, convegni e scuole di alcologia | Ingestione diretta admin / `bootstrap.php` | ACAT Basso Polesine O.D.V., Coordinamento Polesano, docenti accreditati | Event-driven (2026) | **Hub Eventi e Formazione Continua** |
| **`event_bookings`** | **4** | Prenotazioni confermate per eventi live (es. Corso Taglio di Po) | Transazioni form online `api-event-booking.php` | Iscrizioni volontarie dirette degli utenti | Real-time | **Gestione Iscrizioni e Capienza Posti** |
| **`network_entities`** | **577** | Registro pilota storico (tabella originaria pre-censimento) | `data/OLTRE_Global_Hudolin_CAT_Network_V1.csv` | Snapshot storico consolidato ad agosto 2026 | **LEGACY / CONGELATO** | **Mantenuto per retrocompatibilità DAO; NON alimentare per nuove metriche** |

---

## 2. STORIA DELLA CONVERGENZA DEI NUMERI

Per comprendere l'origine delle discrepanze storiche precedentemente rilevate nel codice:

### Fase 1 — Agosto 2026 (Il Dataset Pilota "361 / 538 / 542")
- A metà agosto 2026, il progetto disponeva unicamente della tabella `network_entities`.
- Conteneva **538 entità**, di cui esattamente **361 in Italia** e **177 all'estero** (registrate nel report di commit `TEST_REPORT_V4.json`).
- A seguito dell'aggiunta manuale di 4 nuovi presidi pilota, il totale divenne **542**, valore che venne inserito come fallback nella homepage e nel manifest PWA.

### Fase 2 — Inizio Settembre 2026 (Il Primo CRM "395")
- Venne georeferenziata e normalizzata la prima tranche di Club e associazioni con recapiti certificati (395 record), salvata in `crm_club_contacts` (commit `92fbef7`).
- La cifra "395" fu inserita come testo statico in `admin.php` e `dashboard.php`.

### Fase 3 — Metà Settembre 2026 (Il Censimento Nazionale Completo "1.761 / 1.770")
- Tramite lo script `expand_and_ingest_all.py` e il parsing sistematico dei bollettini regionali (Veneto, FVG, Lombardia, Emilia-Romagna, Toscana, Puglia, ecc.), l'anagrafica nazionale è stata estesa a **1.770 record grezzi nel CSV**, normalizzati a **1.761 presidi effettivi nel database SQLite `cat_clubs_italy`**.
- La UI continuava tuttavia a richiamare in alcuni punti i numeri delle fasi precedenti (542, 361, 395).

---

## 3. PROTOCOLLO DI RISOLUZIONE (SINGLE SOURCE OF TRUTH)

D'ora in avanti, l'unica fonte di verità per le metriche pubbliche è il servizio centralizzato:
$$\text{Database SQLite} \longrightarrow \text{ClubMetricsService.php} \longrightarrow \text{UI / JSON-LD / API / Sitemap}$$
Nessun file frontend o script PHP deve definire costanti o fallback numerici manuali.
