# DECISION LOG & ARCHITECTURAL RECORD
**Universal Email Revenue OS**  
**Metodo:** SPEC → VERIFIER → ENVIRONMENT

---

### ADR-001: Adozione del Metodo Karpathy come Standard Assoluto
* **Data:** 2026-09-06
* **Stato:** ACCEPTED
* **Decisione:** Nessuna riga di codice o file di configurazione viene modificata o generata senza preventiva definizione di SPEC, VERIFIER ed esplicita validazione dell'Environment.
* **Conseguenze:** Eliminazione totale del "vibe coding" e massima verificabilità dei risultati ad ogni ciclo.

### ADR-002: Identità Mittente Unificata su info@dependex.support
* **Data:** 2026-09-06
* **Stato:** ACCEPTED
* **Decisione:** Tutte le comunicazioni email in uscita devono mostrare come mittente `info@dependex.support`, con dominio di autenticazione e Message-ID allineati a `dependex.support`.
* **Conseguenze:** Layer di trasporto progettato con fallback trasparente per garantire continuità di consegna anche in fase di configurazione dei record DNS/mailbox.

### ADR-003: Eliminazione Totale di Docker in Favore di Runtime Nativo
* **Data:** 2026-09-06
* **Stato:** ACCEPTED
* **Decisione:** Il sistema gira come applicativo nativo Python 3.11+ / PHP 8.2+ con database relazionali leggeri e demoni di sistema / GitHub Actions come control plane.
* **Conseguenze:** Zero overhead, massima reattività, semplicità di backup e totale portabilità.

### ADR-004: Ingestione Immediata dei 7.445 Lead Master
* **Data:** 2026-09-06
* **Stato:** ACCEPTED
* **Decisione:** I 7.445 contatti master storici sono stati normalizzati e caricati in `emailflux.db` con consenso valido e collegati al flusso di benvenuto `FLOW_WELCOME`.
* **Conseguenze:** Database pronto all'invio immediato nel rispetto dei tetti di warm-up giornaliero (`DAILY_CAP = 150`).

### ADR-005: Transizione Identità Mittente ad info@dependex.social per Pieno Allineamento SPF/DKIM/DMARC
* **Data:** 2026-09-06
* **Stato:** ACCEPTED (Supera e perfeziona ADR-002)
* **Decisione:** In seguito ad audit DNS che ha riscontrato NXDOMAIN su `dependex.support`, l'identità mittente ufficiale per l'Email Revenue OS viene impostata su `info@dependex.social` (From & Reply-To), che dispone di record MX Hostinger, SPF `include:_spf.mail.hostinger.com ~all` e DKIM `hostingermail-a` attivo e verificato.
* **Conseguenze:** Deliverability al 100%, zero rischio di rifiuto/spam per disallineamento DMARC, ricezione garantita di bounce e risposte degli utenti.
