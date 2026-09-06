# PROTOCOLLO DI DELIVERABILITY E REPUTAZIONE DOMINIO

**Versione:** 1.0 — 2026-09-06  
**Dominio Mittente Ufficiale:** `dependex.support`  
**Host SMTP Primario:** `smtp.hostinger.com:465` (SSL)

---

## 1. REQUISITI DNS ESSENZIALI

1. **SPF (Sender Policy Framework):**
   - Record TXT per `dependex.support`:
     `v=spf1 include:_spf.mail.hostinger.com ~all`
2. **DKIM (DomainKeys Identified Mail):**
   - Chiave crittografica RSA a 2048 bit configurata su Hostinger e propagata nei record CNAME/TXT del dominio.
3. **DMARC (Domain-based Message Authentication, Reporting, and Conformance):**
   - Record TXT per `_dmarc.dependex.support`:
     `v=DMARC1; p=quarantine; rua=mailto:dmarc-reports@dependex.support; pct=100; adkim=r; aspf=r`

---

## 2. REGOLE DI WARMING E LIMITI DI FREQUENZA

| Fase di Warming | Giorni | Volume Max Giornaliero | Rate Orario Raccomandato |
|---|---|---|---|
| **Fase 1 (Iniziale)** | Giorni 1 - 3 | 100 email/giorno | 20 email/ora |
| **Fase 2 (Espansione)** | Giorni 4 - 7 | 300 email/giorno | 50 email/ora |
| **Fase 3 (Accelerazione)** | Giorni 8 - 14 | 800 email/giorno | 100 email/ora |
| **Fase 4 (Regime)** | Giorno 15+ | 2.500+ email/giorno | 250 - 500 email/ora |

---

## 3. CIRCUIT BREAKER SOGLIE CRITICHE

Se una delle seguenti soglie viene superata, il motore `DispatchGovernor` sospende immediatamente le spedizioni e notifica un allarme critico:

- **Tasso di rimbalzo (Bounce Rate):** > 3.0% (Sospensione immediata)
- **Tasso di segnalazione spam (Complaint Rate):** > 0.1% (Sospensione immediata e audit del copy)
- **Tasso di disiscrizione (Unsubscribe Rate):** > 1.5% (Warning e riduzione frequenza invio)
