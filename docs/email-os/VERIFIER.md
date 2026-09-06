# UNIVERSAL EMAIL REVENUE OS — VERIFIER (P0 DISCOVERY)
**Criteri di Qualità, Sicurezza, Deliverability e Conformità**  
**Versione:** 1.0 — 2026-09-06

---

## 1. CRITERI DI ACCETTAZIONE PASS / FAIL (NON NEGOZIABILI)

| Area | Criterio Verifier | Condizione PASS | Condizione FAIL |
| :--- | :--- | :--- | :--- |
| **Provider Adapter** | Astrazione del trasporto | Separato dalla logica dei flussi, supporta Hostinger SMTP SSL e Amazon SES | Dipendenza rigida da singolo provider hardcoded |
| **Sender Identity** | Mittente ufficiale | Header `From` e `Reply-To` settati su `info@dependex.support` | Altro mittente non autorizzato |
| **Zero Banned Terms** | Bonifica terminologica rigorosa | 0 occorrenze di termini vietati dalla governance di progetto (regole di conformità) | ≥ 1 occorrenza rilevata |
| **Zero Legacy Brand** | Bonifica brand storico | 0 menzioni di marchi legacy non ammessi nel codice, template e doc | Riferimenti residui attivi |
| **Idempotenza** | Protezione invii duplicati | `send_id` univoco + deduplicazione su `(workflow_id, contact_id, step)` | Re-invio non intenzionale al medesimo step |
| **Frequency Governor** | Tetto giornaliero / settimanale | Max 1 email marketing al giorno per contatto; priorità transazionale | Invio simultaneo di più campagne concorrenti |
| **Suppression** | Rispetto unsubscribe e bounce | Controllata prima di ogni invio (`unsub=1` o `status!='ATTIVO'` blocca l'invio) | Invio a contatti disiscritti |
| **Unsubscribe RFC 8058** | Disiscrizione 1-click | Header `List-Unsubscribe` e `List-Unsubscribe-Post` presenti | Header assenti o link non funzionante |
| **Mobile & Plain Text** | Rendering universale | Template HTML fluido + parte testuale `text/plain` inclusa | Solo HTML o layout rotto su mobile |
| **No Docker** | Architettura nativa | Script ed esecutori Python/PHP diretti, orchestrati da GitHub Actions | Richiesta o dipendenza da runtime Docker |

---

## 2. SUITE DI TEST DI VERIFICA (AUTOMATED GATES)

1. **Lint & Syntax Test:** `python -m py_compile` su tutti i file `.py` e `php -l` sui file PHP.
2. **Delivery Test (Dry-Run & Real Single):** Invio di prova autenticato verso `labomobile.lm@gmail.com` con verifica headers.
3. **Database Integrity:** Foreign keys e indici attivi su `emailflux.db`.
4. **Security Scan:** Nessuna password o secret in chiaro committata nel repository pubblico.
