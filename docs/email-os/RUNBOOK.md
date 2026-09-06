# RUNBOOK OPERATIVO (PROCEDURE D'USO E TROUBLESHOOTING)

**Versione:** 1.0 — 2026-09-06  
**Ecosistema:** Dependex.social · Oltre.social

---

## 1. COMANDI OPERATIVI COMUNI

### Test di Consegna Diretto (Invio Singolo di Prova)
Per testare la corretta configurazione SMTP e l'allineamento con il mittente `info@dependex.support`:
```bash
python automation/emailflux/test_smtp_delivery.py
```
*(Destinatario di verifica predefinito: `labomobile.lm@gmail.com`)*

### Esecuzione Manuale di un Batch di Invio
```bash
python automation/emailflux/worker.py
```
*(Supporta variabili d'ambiente `DRY_RUN=true` e `BATCH_LIMIT=50`)*

### Importazione ed Iscrizione Nuovi Lead
```bash
python automation/emailflux/import_leads.py
python automation/emailflux/enroll.py
```

### Esecuzione della Test Suite Completa
```bash
python tests/test_email_os.py
```

---

## 2. GESTIONE ALLARMI E RECOVERY

### Caso 1: Invio Bloccato da Circuit Breaker (Bounce > 3%)
1. Ispezionare la tabella `suppression_list` nel database `automation/emailflux/data/emailflux.db`.
2. Verificare i log in `reports/daily_latest.json`.
3. Non forzare l'invio prima di aver pulito i contatti non validi.
4. Riavviare il dispatch con parametro `batch_limit=50`.

### Caso 2: Errore di Autenticazione SMTP
1. Verificare i secret configurati su GitHub Actions: `EMAIL_SMTP_USER` e `EMAIL_SMTP_PASSWORD`.
2. Su Hostinger, la porta richiesta è `465` (SSL).
3. Verificare che l'indirizzo mittente visibile sia `info@dependex.support`.

### Caso 3: Richiesta Manuale di Cancellazione Utente (GDPR Right to be Forgotten)
```python
from src.consent.ledger import ConsentLedger
ledger = ConsentLedger()
ledger.record_opt_out("utente@email.com", reason="GDPR_REQUEST_MANUAL")
```
