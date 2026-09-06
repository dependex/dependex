# GUIDA ALLA CONFORMITÀ LEGALE E PRIVACY (COMPLIANCE SPEC)

**Versione:** 1.0 — 2026-09-06  
**Standard Normativi:** GDPR (Reg. UE 2016/679), Direttiva ePrivacy, RFC 8058, CAN-SPAM.

---

## 1. PRINCIPI FONDAMENTALI PRIVACY-BY-DESIGN

1. **Nessun Acquisto o Scraping Illecito:** Il sistema opera unicamente su contatti acquisiti consensualmente con tracciamento della sorgente (`consent_source`).
2. **Registro Immutabile dei Consensi (`src/consent/ledger.py`):**
   - Timestamp preciso in formato ISO 8601 (UTC).
   - Indirizzo IP e User-Agent dell'iscritto al momento dell'opt-in.
   - Stato del consenso: `EXPLICIT_OPT_IN`, `DOUBLE_OPT_IN`, `TRANSACTIONAL_ONLY`, `OPTED_OUT`.
3. **Disiscrizione Immediata RFC 8058 One-Click:**
   - Header SMTP inseriti in ogni email inviata:
     ```http
     List-Unsubscribe: <https://dependex.social/privacy-center.php?action=unsubscribe&email=...>, <mailto:unsubscribe@dependex.support?subject=unsubscribe>
     List-Unsubscribe-Post: List-Unsubscribe=One-Click
     ```
   - L'elaborazione della richiesta avviene entro 0 secondi (istantanea) nel database `emailflux.db`.
4. **Suppression List Inviolabile:**
   - Qualsiasi indirizzo contrassegnato con `unsubscribed`, `bounced`, o `complained` viene inserito nella tabella `suppression_list` e bloccato prima di qualsiasi invio tramite `ComplianceGuard`.
5. **Diritto all'Oblio e Portabilità:**
   - Endpoint in `privacy-center.php` per export dati in formato JSON e cancellazione completa dei dati personali.
