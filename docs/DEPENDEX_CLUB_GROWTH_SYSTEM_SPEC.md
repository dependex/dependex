# DEPENDEX CLUB GROWTH SYSTEM — ARCHITECTURAL AUDIT & MASTER SPEC
**Versione:** 1.0 — 2026-09-22  
**Stato:** Architectural Plan & Pre-Implementation Audit (FASE 0)  
**Metafora Guida:** *"Una sedia è già pronta."*  
**Deontologia:** Metodo Hudolin · Zero Medicalizzazione · Zero Classifiche · Data Minimization

---

## DEPENDEX CURRENT STATE MAP

### 1. Cosa Esiste
- **Database SQLite multi-tabella:** `data/acat_community.sqlite` contenente 1.761 Club in `cat_clubs_italy`, 1.768 contatti in `crm_club_contacts`, 2.064 entità mondiali in `dependex_world_registry`, e 577 in `network_entities`.
- **Directory e Mappa Club:** `world-club-explorer.php`, `mappa-club.php`, `club-public.php`, `widget-club.php`, `api-clubs-italy.php`, `api-club-locator.php`.
- **Orientamento & Ascolto:** `parla-con-noi.php`, `cortex.php`, `api-cortex.php`, `modules/cortex/cortex_service.php`.
- **Eventi & Formazione:** `events.php`, `events-public.php`, `event-detail.php`, `api-event-booking.php`, `events` table (es. Taglio di Po).
- **Infrastruttura di Governance:** `bootstrap.php` con SIC ID standard (`SIC-XXXXXXXX-XXXXXXXX-C`), auth basata su ruoli (`roles`, `user_roles`), audit log (`audit_log`), telemetria locale (`funnel_events`, `site_live_sessions`).
- **Sistema Notifiche & Email:** `email-engine.php`, `automation/emailflux`, SMTP Hostinger certificato su `info@dependex.support`.

### 2. Cosa Funziona
- **Calcolo Geodesico Haversine:** `api-club-locator.php` calcola la distanza in km per latitudine/longitudine o risolve da CAP/comune in modo istantaneo.
- **Scheda Club Pubblica (`club-public.php`):** Breadcrumb SEO, metadati OpenGraph, JSON-LD Schema.org (`NGO`/`CommunityCenter`), esportazione vCard `.vcf`, condivisione WhatsApp 1-tap, modulo self-service per il servitore.
- **Sicurezza di Base:** Sessioni SQLite WAL, protezione CSRF (`CSRF_KEY`), security header HTTP (`X-Frame-Options`, `nosniff`), rate limiting su bucket (`security_rate_limits`).

### 3. Cosa è Incompleto
- **Contact Bridge con i Club:** Attualmente `parla-con-noi.php` salva solo in `audit_log` in modo generico e non permette di selezionare un Club né di notificare il servitore del Club o creare una scheda accoglienza.
- **Pipeline Accoglienza del Club:** Non esiste un pannello leggero dove il Club può vedere i nuovi contatti senza login complesso.
- **Normalizzazione Stati dei Club:** Gli stati nel DB sono stringhe grezze da OSINT (`ACTIVE_VERIFIED_2026`, `VERIFIED_OFFICIAL_CURRENT`, etc.) invece dei 5 stati trasparenti definiti.
- **Frigo Check:** Mancante, non ancora implementato.
- **Cortex Orientatore:** Ha ancora categorie legacy (`sales`, `web3`, `impronta aziendale`) e necessita di specializzazione come puro orientatore maieutico ecologico-sociale.

### 4. Cosa è Duplicato
- **Molteplici Tabelle di Entità:** `cat_clubs_italy` (1.761) vs `dependex_world_registry` (2.064) vs `crm_club_contacts` (1.768) vs `network_entities` (577). Portano a discrepanze nei conteggi mostrati nelle varie pagine.
- **Pagine di Ricerca:** `mappa-club.php`, `world-club-explorer.php`, e `trova-club.php` (che fa redirect 301).

### 5. Cosa è Fragile
- **Numeri Hardcodati:** In varie viste compaiono conteggi fissi (es. "1.770+", "542", "395") che possono disallinearsi con il DB.
- **Verifica Servitore senza Token:** Il form in `club-public.php` permette modifiche con solo CSRF senza richiedere una verifica minima (email o token riservato del CRM).

### 6. Cosa Manca
- **Contact Bridge Minimale:** Modulo *"Voglio contattare questo Club / Vorrei essere ricontattato"* con consenso GDPR e zero dati sanitari.
- **Pipeline Privata Accoglienza:** Ciclo di vita contatto minimale per il referente: `RICEVUTO` → `CONTATTATO` → `PRIMA VISITA PREVISTA` → `PRIMA VISITA EFFETTUATA` → `RITORNO` → `NON PIÙ IN CONTATTO`.
- **Dashboard Club Accessibile via Token Privato:** Accesso zero-friction per il servitore tramite link sicuro senza password dimenticata.
- **Strumento "Frigo Check":** Autovalutazione volontaria a 10 domande con restituzione di sole aree di riflessione.

### 7. Cosa Può Essere Riutilizzato
- `api-club-locator.php` (eccellente per query geodesica e filtri).
- `club-public.php` (ottima struttura UI e Schema.org, da potenziare con Contact Bridge e 5 stati).
- `funnel_events` (schema perfetto per tracciare le fasi del funnel umano senza identificare la persona).
- `email-engine.php` (motore di notifica e trasporto SMTP già funzionante).
- Iconografia e Design System nativo (`assets/icons.php`, `assets/css/app.css`, `mobile-916.css`).

### 8. Cosa Deve Essere Rifattorizzato
- **Homepage (`index.php`):** Allineamento al pattern "PROBLEMA → SOLUZIONE → AZIONE" con CTA primaria *"TROVA UNA SEDIA NEL CLUB PIÙ VICINO"* e normalizzazione dei numeri di rete dal DB.
- **Cortex Service (`modules/cortex/cortex_service.php`):** Rimozione tassonomie commerciali e attivazione modalità Orientatore + Safety Protocol (112, SerD, Pronto Soccorso).
- **Unificazione Query Conteggi:** Helper globale `get_network_metrics(PDO $pdo)` per avere numeri sempre coerenti e verificabili.

### 9. Cosa Non Deve Essere Toccato
- Gestione SIC ID e algoritmi di check digit (`sic_id`, `sic_check`).
- Event Booking Engine (`api-event-booking.php`, `event-detail.php`) già perfettamente rodato per Taglio di Po e altri eventi.
- Schema e tabelle core dell'ACAT/ARCAT (`cat_clubs_italy`, `crm_club_contacts`).

---

## I 15 PUNTI OBBLIGATORI (ART. 30)

### 1. Current State Audit
Il sistema possiede un patrimonio informativo di 1.761 Club in Italia e 2.064 a livello globale. L'infrastruttura backend PHP nativo + SQLite WAL è veloce, priva di overhead e mobile-ready. Tuttavia, l'attuale UX orienta l'utente verso directory complesse o verso concetti generici anziché condurlo direttamente a un Club vicino e a un contatto umano immediato.

### 2. Gap Analysis
| Dimensione | Stato Attuale | Stato Desiderato (Growth System) |
|---|---|---|
| **Metafora UX** | Portale di rete / Directory / Gamification | *"Una sedia è già pronta"* (Digital Front Door) |
| **Homepage** | Ticker notizie, 4 porte, Life Playground | Problema → Soluzione → Azione con CTA primaria |
| **Stato Club** | Stringhe OSINT non standardizzate | 5 Stati chiari: CENSITO, IN VERIFICA, VERIFICATO, DATI DA AGGIORNARE, CONTATTO DA CONFERMARE |
| **Contatto Club** | Solo numero telefonico o form generico | Contact Bridge dedicato con preferenza canale (WhatsApp, Tel, Email) |
| **Dashboard Club** | Pannello admin complesso con login rigido | Dashboard minimale dell'accoglienza basata su token privato |
| **Organizzazione** | Nessun tool di resilienza | Frigo Check maieutico ("Non servono eroi") |
| **AI / Cortex** | Agenti multi-tasking misti (sales/web3) | Orientatore empatico + Protocollo Safety Emergenze |

### 3. User Journeys (I Percorsi Umani)
1. **Persona in difficoltà:** Entra su smartphone → Vede *"Se stai cercando aiuto... una sedia è già pronta"* → Clicca *"Trova una sedia nel Club più vicino"* → Inserisce città/CAP o usa GPS → Vede la scheda del Club con orario e via → Clicca *"Voglio contattare il Club"* (lascia WhatsApp o Telefono) → Il Club lo accoglie.
2. **Familiare preoccupato:** Clicca la porta *"Sono un familiare"* → Legge che il Club accoglie l'intera famiglia e che può partecipare anche da solo senza la persona con problemi → Trova il Club locale → Salva contatto o chiede di essere ricontattato.
3. **Servitore del Club:** Riceve notifica o accede con link sicuro alla Dashboard del Club → Vede: *3 nuovi contatti, 2 prime visite previste* → Aggiorna lo stato di accoglienza con 1 tap senza burocrazia.
4. **Club che vuole autovalutarsi:** Compila il *"Frigo Check"* in 3 minuti → Riceve feedback qualitativo ("Aree da osservare") su passaggio consegne e accoglienza condivisa.

### 4. Information Architecture
```text
DEPENDEX (Digital Front Door)
├── Homepage (Problema → Soluzione → Azione)
├── Club Finder (Filtri geografici, orari, stati di verifica, GPS)
│   └── Club Profile (/club/{sic_id})
│       ├── Scheda Operativa (Giorno, Ora, Sede, Mappa, vCard)
│       ├── Badge Stato Trasparente
│       ├── Contact Bridge ("Vorrei essere ricontattato")
│       └── Aggiornamento Self-Service
├── Club Private Portal (Token protetto)
│   ├── Dashboard Accoglienza (Nuovi contatti, Prime visite, Da ricontattare)
│   └── Frigo Check (Autovalutazione volontaria comunitaria)
├── Parla con Noi (Ascolto riservato & Numero Verde AICAT 800 974250)
├── Eventi & Incontri (/eventi.php, /event-detail.php)
└── Orientatore Cortex (/cortex.php - Solo orientamento e supporto umano)
```

### 5. UX Flows
- **Flow Contatto:** `Scheda Club` → `Modal/Form Contatto Bridge` → Richiesta Nome (opzionale), Contatto (Tel/WhatsApp/Email), Messaggio (opzionale), Consenso Privacy (obbligatorio) → `Conferma Immediata` con orario del prossimo incontro e frase di rassicurazione: *"La tua sedia è pronta. Il referente ti contatterà al recapito indicato."*
- **Flow Sicurezza Cortex:** Input con segnali di allarme o violenza → Risposta immediata con numeri di emergenza (112, 118, 1522) e SerD locale, senza diagnosi medica.

### 6. Database Changes
Creazione di due nuove tabelle dedicate e leggere in `acat_community.sqlite`:
1. `club_contact_requests`:
   - `id`, `sic_id` (PK), `club_sic_id` (FK), `contact_name`, `contact_channel` (WHATSAPP, PHONE, EMAIL), `contact_value`, `notes`, `consent_given` (1), `ip_hash`, `status` (RECEIVED, CONTACTED, FIRST_VISIT_SCHEDULED, FIRST_VISIT_DONE, RETURNED, DISENGAGED), `internal_notes`, `created_at`, `updated_at`.
2. `club_frigo_checks`:
   - `id`, `sic_id` (PK), `club_sic_id`, `answers_json`, `observations_json`, `created_at`.
3. Aggiunta colonna `verification_level` o normalizzazione vista per i 5 stati:
   - `CENSITO`, `IN_VERIFICA`, `VERIFICATO`, `DATI_DA_AGGIORNARE`, `CONTATTO_DA_CONFERMARE`.

### 7. API Changes
- `POST /api/contact-club.php`: Riceve la richiesta di contatto, valida, crea record in `club_contact_requests`, invia notifica email/link WhatsApp al referente del Club.
- `GET/POST /api/club-dashboard.php`: Endpoint protetto da token per visualizzare e aggiornare la pipeline dell'accoglienza del Club.
- `POST /api/frigo-check.php`: Riceve le 10 risposte, calcola le aree di riflessione (senza voto numerico) e le restituisce.
- `GET /api-club-locator.php`: Aggiornamento per restituire lo stato normalizzato (`verification_label`, `verification_badge_class`).

### 8. Security & Privacy Review
- **Data Minimization Rigorosa:** Il Contact Bridge non memorizza alcuna anamnesi, quantità di consumo o referti medici. I dati di contatto vengono conservati solo per il tempo necessario alla presa in carico del Club.
- **No Indici di Benessere:** Conforme all'art. 7 di AGENTS.md, nessun campo o score numerico verrà salvato.
- **Protezione Accesso Dashboard Club:** Autenticazione tramite magic-link token crittografato (SHA-256) per evitare che i servitori debbano ricordare password o espongano credenziali deboli.

### 9. SEO Plan
- Ottimizzazione delle pagine territoriali: URL leggibili come `/club/{sic_id}` con Canonical, Breadcrumbs e Schema.org `CommunityCenter` / `PostalAddress`.
- Titoli H1 e Meta Description calibrati sulle ricerche umane ad alto intento: *"Club alcologico [Comune]"*, *"Aiuto famiglia alcol [Comune]"*, *"Incontri gratuiti metodo Hudolin [Provincia]"*.
- Pagine territoriali generate solo in presenza di Club reali censiti (zero pagine doorway vuote).

### 10. Analytics Plan (Privacy-First)
- Misurazione del funnel umano tramite `funnel_events` aggregati:
  - Step 1: `SEARCH_CLUB` (città/CAP o GPS)
  - Step 2: `VIEW_CLUB_PROFILE`
  - Step 3: `CLICK_CONTACT_BRIDGE`
  - Step 4: `SUBMIT_CONTACT_BRIDGE`
  - Step 5: `FIRST_VISIT_DECLARED` (segnalata dal Club)
  - Step 6: `RETURN_DECLARED` (segnalata dal Club)
- Risposta alla domanda chiave: *"Dove si perdono le persone?"* senza memorizzare identità o profili invasivi.

### 11. MVP Roadmap
- **MVP-1:** Normalizzazione Metriche di Rete + Refactor Hero Homepage ("Una sedia è già pronta") + Club Finder a 5 stati.
- **MVP-2:** Contact Bridge su Scheda Club (`club-public.php`) con notifica al servitore.
- **MVP-3:** Dashboard dell'Accoglienza del Club (gestione stati contatto zero-friction).
- **MVP-4:** Modulo "Frigo Check" per i Club (autovalutazione qualitativa "Non servono eroi").
- **MVP-5:** Revisione Cortex Orientatore (disattivazione moduli legacy e attivazione safety protocol).

### 12. Files to Modify
- `index.php`: Riorganizzazione hero e blocchi d'azione Problema → Soluzione → Azione.
- `club-public.php`: Integrazione 5 stati, Contact Bridge form, e rimozione di termini non coerenti.
- `api-club-locator.php`: Aggiunta metadati stati verificati e supporto filtri avanzati.
- `modules/cortex/cortex_service.php` & `api-cortex.php`: Refactor Cortex Orientatore.
- `_header.php`: Coerenza navigazione primaria.

### 13. Files to Create
- `api/api-club-contact.php`: Gestore richieste di contatto riservate.
- `club-welcome-dashboard.php`: Dashboard minimale dell'accoglienza del Club.
- `frigo-check.php`: Interfaccia e motore maieutico del Frigo Check.
- `modules/clubs/ClubMetricsService.php`: Servizio centralizzato per il calcolo normalizzato e dinamico delle metriche di rete.

### 14. Risks & Mitigation
- **Rischio:** Referenti dei Club con scarsa alfabetizzazione digitale o email non controllate frequentemente.  
  **Mitigazione:** Predisporre notifica e contatto rapido via WhatsApp Web (click-to-chat) oltre che via email, e mostrare sempre in evidenza il Numero Verde Nazionale AICAT (800 974250) come paracadute.
- **Rischio:** Disallineamento tra numeri di Club nei diversi dataset.  
  **Mitigazione:** Definire una query autoritativa unica e documentata, esponendo chiaramente "Club Censiti" e "Club con Dati di Incontro Verificati".

### 15. Test Plan
- Test di ricerca Club per CAP, città, raggio GPS, provincia.
- Test di invio Contact Bridge (validazione recapito, CSRF, rate limit, notifica).
- Test accesso e aggiornamento pipeline nella Dashboard dell'Accoglienza.
- Test Frigo Check (completamento e restituzione aree di osservazione senza punteggio numerico).
- Test reattività mobile (zero horizontal overflow, touch target 44px, min-height 100dvh).
- Test sicurezza Cortex (prompt injection, domande di emergenza medica/crisi).
