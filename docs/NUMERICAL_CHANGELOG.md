# REGISTRO DELLE MODIFICHE NUMERICHE (CHANGELOG) — DEPENDEX.SOCIAL
**Versione:** 1.0 — 2026-09-22  
**Stato:** Registro Ufficiale Modifiche & Piano Correttivo di Allineamento

---

## CRONOLOGIA DELLE VARIAZIONI NUMERICHE

| ID Modifica | Data | File Coinvolto | Parametro / Metrica | Valore Precedente | Nuovo Valore Certificato | Motivazione e Fonte di Riscontro |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **CHG-001** | 2026-09-22 | `index.php` | `$totalNodes` (fallback) | `542` | `ClubMetricsService::getNationalSummary()['total_presidi']` (Live: 1.761) | Eliminato numero hardcoded risalente al DB pilota di agosto. Il dato riflette ora la query live. |
| **CHG-002** | 2026-09-22 | `index.php` | Copia hero e CTA | "1.770+ in Italia" | "1.761 presidi censiti in Italia" | Sostituito arrotondamento promozionale con il conteggio puntuale del database `cat_clubs_italy`. |
| **CHG-003** | 2026-09-22 | `manifest.webmanifest` | `"description"` | "Metodo Hudolin, 542 Club" | "Metodo Hudolin, 1.761 Presidi Censiti (1.414 Club Locali e 347 Coordinamenti Territoriali)" | Allineamento metadati PWA con l'effettivo patrimonio censito nel 2026. |
| **CHG-004** | 2026-09-22 | `world-club-explorer.php` | `$totalFamilies` | Query errata su tutti i livelli (produceva > 63.500) | `SELECT SUM(families_count) ... WHERE network_level='LOCAL_CLUB'` (15.678) | Eliminato il quadruplo conteggio dovuto alla somma delle famiglie su ACAT, ARCAT e AICAT. |
| **CHG-005** | 2026-09-22 | `admin.php` | Sottotitoli anagrafica | "395 censiti su suolo nazionale" | `<?= $totalClubsLive ?> censiti su suolo nazionale (1.761)` | Sostituito il vecchio conteggio del primo batch CRM con il valore reale della tabella `crm_club_contacts` (1.768) o `cat_clubs_italy` (1.761). |
| **CHG-006** | 2026-09-22 | `dashboard.php` | Link ricerca | "Trova Altri Club (395 in Italia)" | "Trova un Club (1.414 Club Locali in Italia)" | Aggiornato per riflettere i Club locali effettivamente operativi. |
| **CHG-007** | 2026-09-22 | `templates/_club_locator_widget.php` | Header widget | "395 CLUB & APCAT GEOREFERENZIATI" | "1.761 PRESIDI GEOREFERENZIATI IN ITALIA" | Aggiornato per riflettere l'intero dataset geocodificato al 100%. |
| **CHG-008** | 2026-09-22 | `_header.php` | Meta title & description | "Oltre 1.770 Club Alcologici Territoriali" | "Rete dei Club Territoriali · 1.761 Presidi Censiti (1.414 Club di base)" | Coerenza tra snippet Google e contenuti della pagina. |
| **CHG-009** | 2026-09-22 | `world-club-explorer.php` | `$totalNodes` | Query grezza `COUNT(*)` su `dependex_world_registry` | Dettagliato in: Nodi Totali (2.064), Nodi Italia (1.762), Nodi Esteri (302) | Distinzione trasparente tra rete italiana e nodi internazionali. |
| **CHG-010** | 2026-09-22 | `README.md` | Sezione Statistiche | "538 entità censite, 361 entità Italia" | Tabella aggiornata con i dati ufficiali del censimento 2026 | Documentazione di repository riallineata alla realtà del codice. |
