# SPECIFICA SCHEMA EVENTI (EVENT SCHEMA SPECIFICATION)

**Versione:** 1.0.0  
**JSON Schema Ufficiale:** `schemas/event_schema_v1.json`

---

## 1. STRUTTURA DEL PAYLOAD EVENTO
Ogni evento generato dal frontend, dall'e-commerce, da PayPal o da un webhook deve rispettare la seguente struttura JSON:

```json
{
  "$schema": "https://dependex.social/schemas/event_schema_v1.json",
  "event_id": "c1f8a85a-8b9a-4c28-bb88-3482a92c3001",
  "schema_version": "1.0.0",
  "event_name": "lead_captured",
  "user_identifier": "contatto@dominio.it",
  "timestamp": "2026-09-06T10:30:00Z",
  "properties": {
    "source": "club_registration_page",
    "plan_interest": "premium",
    "referrer": "direct"
  },
  "context": {
    "ip_address": "93.144.12.3",
    "user_agent": "Mozilla/5.0 ...",
    "locale": "it-IT"
  }
}
```

---

## 2. EVENTI STANDARD SUPPORTATI

| Evento | Trigger Tipico | Punteggio | Effetto Lifecycle |
|---|---|---|---|
| `page_view` | Navigazione pagine ad alto intento | +1 | Incrementa engagement |
| `lead_captured` | Compilazione form / iscrizione | +15 | Crea profilo `LEAD` |
| `email_opened` | Apertura tracciata da pixel | +2 | Attiva `ENGAGED` |
| `email_clicked` | Click su link tracciato | +5 | Sposta verso `INTERESTED` |
| `resource_downloaded` | Download documento/guida | +10 | Incrementa lead score |
| `cart_created` | Aggiunta prodotto al carrello | +15 | Prepara recovery |
| `checkout_initiated` | Inizio procedura pagamento | +25 | Attiva stato `HIGH_INTENT` |
| `order_completed` | Convalida acquisto o IPN PayPal | +50 | Transizione a `BUYER` |
| `email_bounced` | Rimbalzo hard/soft | -100 | Suppression list immediata |
| `unsubscribe_requested` | Click su disiscrizione | -50 | Revoca consenso marketing |
