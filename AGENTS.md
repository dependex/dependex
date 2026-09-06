# AGENTS.md — PROTOCOLLO DI GOVERNANCE DELL'ECOSISTEMA
**Repository:** DEPENDEX.SOCIAL · OLTRE.SOCIAL · UNIVERSAL EMAIL REVENUE OS  
**Metodo di Riferimento:** SPEC → VERIFIER → ENVIRONMENT (Andrej Karpathy)  
**Versione:** 1.0 — 2026-09-06

---

## 1. REGOLE DURE E NON NEGOZIABILI (HARD CONSTRAINTS)

1. **Metodo Karpathy (Spec → Verifier → Environment):**
   - Non iniziare mai a produrre codice o configurazioni finali senza una Spec dettagliata e approvata.
   - Definire sempre i criteri di verifica (Pass/Fail) prima di eseguire.
   - Eseguire auto-verifica sistematica dell'output prima di dichiarare qualsiasi risultato.
2. **Identità & Trasporto Email:**
   - Indirizzo mittente ufficiale: `info@dependex.support`.
   - Host SMTP primario: `smtp.hostinger.com:465` (SSL).
   - Indirizzo di prova prioritario: `labomobile.lm@gmail.com`.
3. **Bonifica Terminologica Rigorosa:**
   - È vietato l'uso delle seguenti parole nel codice sorgente, documentazione, database, template o prompt:
     `magico`, `magic`, `M.A.G.I.C.`, `giorgian putanu`.
   - Zero riferimenti residui a `81plus` all'interno del progetto.
4. **Control Plane & No Docker:**
   - GitHub Actions funge da piano di controllo (CI/CD, QA, schedule, compilation, reporting).
   - Nessun container Docker: esecuzione nativa su OS/VPS Linux + Python 3.11+ / PHP 8.2+ / SQLite / PostgreSQL / Redis.
5. **Privacy & Deliverability by Design:**
   - Tracciamento consensi GDPR conforme (double opt-in, provenance, timestamp, retention).
   - Unsubscribe one-click RFC 8058 (`List-Unsubscribe` e `List-Unsubscribe-Post`).
   - Nessun acquisto o scraping illecito di liste contatti.

---

## 2. FORMATO DI RISPOSTA STANDARD

Ogni interazione deve rispettare rigorosamente la sequenza:

```text
**SPEC**
[Obiettivo, Contesto, Decisioni, Trade-off, Criteri di successo, Cosa NON fare]

**VERIFIER**
[Criteri di valutazione Pass/Fail, Controlli di sicurezza e conformità]

**ESECUZIONE**
[Codice, configurazioni, azioni svolte o richiesta mirata di approvazione]

**AUTO-VERIFICA**
[Valutazione oggettiva rispetto ai criteri definiti]

phase:
read_state:
gaps_found:
repos_evaluated:
components_reused:
files_created_or_changed:
tests:
verifier_pass:
risks:
next_action:
checkpoint:
```

---

## 3. MASTER PROMPT REFERENCE
Vedi il prompt completo in [`MASTER_EMAIL_OS_PROMPT.md`](file:///c:/81PLUS_GLOBAL_MASTER/dependex.social/MASTER_EMAIL_OS_PROMPT.md).
