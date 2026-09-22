# REPORT DI INTEGRITÀ DEI DATI & AUDIT CONFLITTI — DEPENDEX.SOCIAL
**Data:** 2026-09-22  
**Stato:** Audit Completato — Mappatura Conflitti e Piano di Risanamento  
**Ambito:** Tutte le superfici pubbliche (Web, PWA, SEO Meta, Schema.org, API, Admin)

---

## 1. CONFLITTI NUMERICI RILEVATI E DIAGNOSI

### Conflitto 1: "542 Club" vs "361 Club" vs "395 Club" vs "1.761 / 1.770 Club"
- **Dove appariva:**
  - "542": `index.php` (fallback variabile `$totalNodes`), `manifest.webmanifest`, `DEPENDEX_REDESIGN_AUDIT.md`.
  - "361": `README.md`, `TEST_REPORT_V4.json` (dati storici di agosto).
  - "395": `admin.php`, `dashboard.php`, `templates/_club_locator_widget.php`, test suite `test_crm_club_outreach_e2e.php`.
  - "1.770+": `index.php` (hero e copy), `_header.php` (title e metadesc).
- **Causa Tecnica:** Sfasamento temporale tra fasi successive di arricchimento del database. Quando la base dati è passata da 361 presidi pilota a 1.761 presidi nazionali, i template non erano collegati a una query dinamica unificata ma contenevano stringhe statiche o fallback hardcodati.
- **Soluzione di Risanamento:**
  - Sostituire qualsiasi testo statico con chiamate a `ClubMetricsService::getNationalSummary()`.
  - Esplicitare la scomposizione esatta: **1.761 presidi totali censiti in Italia = 1.414 Club locali (CAT) + 347 entità di coordinamento territoriale e regionale (APCAT, ACAT, ARCAT, AICAT)**.

---

### Conflitto 2: Il Calcolo Iperbolico delle Famiglie ("51.500" / "63.515" / "85.337" vs "15.678")
- **Dove appariva:**
  - `world-club-explorer.php` riga 50: `number_format($totalFamilies, 0, ',', '.') . " FAMIGLIE ACCOLTE"`.
  - `data/CENSIMENTO_FAMIGLIE_CLUB_ITALIA_2026.md`.
  - Documento strategico `STRATEGIA_EMAIL_MARKETING_CRM_CLUB_FLUX100_EMM.md`.
- **Causa Tecnica:** `world-club-explorer.php` eseguiva:
  ```sql
  SELECT SUM(families_count) FROM dependex_world_registry WHERE country='Italy' AND network_level != 'NATIONAL'
  ```
  Poiché il campo `families_count` era impostato su tutte le righe, la query sommava le 11 famiglie di ogni singolo Club, più le famiglie stimate per l'ACAT provinciale, più le famiglie dell'ARCAT regionale, determinando un quadruplo conteggio sistematico.
- **Soluzione di Risanamento:**
  - La query autoritativa per le famiglie stimate deve filtrare rigorosamente sui soli Club di base:
  ```sql
  SELECT SUM(families_count) FROM dependex_world_registry WHERE country='Italy' AND network_level = 'LOCAL_CLUB'
  ```
  - **Risultato corretto e documentato:** **15.678 famiglie**.
  - Vietato mostrare cifre superiori a 16.000 a meno di una certificazione puntuale riga per riga.

---

### Conflitto 3: Stati dei Club Gergali OSINT non Comprensibili al Pubblico
- **Dove appariva:** `cat_clubs_italy.status` e `dependex_world_registry.status` con stringhe come:
  `ACTIVE_VERIFIED_2026`, `VERIFIED_OFFICIAL_CURRENT`, `PUBLIC_SOCIAL_NEEDS_ADDRESS_VERIFICATION`, `OFFICIAL_LIST_CONTACT_MISSING`.
- **Causa Tecnica:** Codici tecnici generati dagli script di scraping e OSINT durante le sessioni di harvesting, mostrati direttamente nei badge frontend.
- **Soluzione di Risanamento:**
  - Traduzione semantica immediata nei **5 Stati Ufficiali Trasparenti**:
    1. `CENSITO` (1.476 presidi con recapito certo e giorno da confermare)
    2. `IN VERIFICA` (in corso di contatto)
    3. `VERIFICATO` (285 presidi con giorno, orario e sede confermati per il 2026)
    4. `DATI DA AGGIORNARE` (segnalazione di variazione logistica)
    5. `CONTATTO DA CONFERMARE` (recapito non raggiungibile)

---

## 2. REGOLE DI VALIDAZIONE AUTOMATICA (ASSERTION SUITE)

Nel file di test di data integrity (`tests/test_data_integrity.php`) devono essere applicati i seguenti vincoli bloccanti:

1. `ASSERT_TRUE($clubs_verificati <= $clubs_censiti)`: Un Club non può essere verificato se non è censito.
2. `ASSERT_TRUE($clubs_locali <= $totale_presidi)`: La somma dei Club locali non può eccedere il totale dei presidi registrati.
3. `ASSERT_TRUE($famiglie_stimate <= ($clubs_locali * 14))`: Nessun Club può dichiarare un numero sproporzionato di famiglie rispetto alla raccomandazione metodologica Hudolin (max 12-14 famiglie per cerchio).
4. `ASSERT_NO_HARDCODED_542_OR_361`: Nessun file pubblico PHP o HTML deve contenere le stringhe fisse "542 Club" o "361 Club".
