# CATALOGO FLUSSI DI AUTOMAZIONE (FLOW CATALOG)

**Versione:** 1.0 — 2026-09-06  
**Directory Sorgente Flussi:** `flows/` (YAML dichiarativo)

---

## 1. `welcome_onboarding` (`flows/welcome_onboarding.yml`)
- **Trigger:** Evento `lead_captured` (consenso = true).
- **Obiettivo:** Accoglienza nuovi membri, rilascio credenziali del Club Dependex, orientamento metodologico ed erogazione asset gratuiti di valore.
- **Sequenza:**
  1. `step_welcome_1`: Invio immediato credenziali e risorse chiave.
  2. `delay_1`: Attesa 24 ore.
  3. `step_welcome_2`: Spiegazione del Metodo e casi d'uso concreti.
  4. `delay_2`: Attesa 48 ore.
  5. `step_welcome_3`: Invito esclusivo al Tavolo Strategico Dependex.
- **Condizione di Uscita (Exit Condition):** `order_completed` interrompe immediatamente la sequenza informativa e sposta l'utente nel flusso clienti.

---

## 2. `cart_recovery` (`flows/cart_recovery.yml`)
- **Trigger:** Evento `checkout_initiated` (stato = pending).
- **Obiettivo:** Recupero del carrello non finalizzato attraverso assistenza mirata e rimozione di ostacoli tecnici o di pagamento.
- **Sequenza:**
  1. `delay_cart_1`: Attesa 1 ora dall'inizio sessione.
  2. `step_cart_1`: Promemoria carrello riservato con link diretto one-click.
  3. `delay_cart_2`: Attesa 24 ore.
  4. `step_cart_2`: Offerta di supporto tecnico, chiarimenti su modalità di fatturazione e PayPal.
- **Condizione di Uscita:** `order_completed` interrompe il recupero istantaneamente.

---

## 3. `nurture_club_value` (`flows/nurture_club_value.yml`)
- **Trigger:** Ingresso segmento `ENGAGED` (score >= 5).
- **Obiettivo:** Consolidamento della fiducia tramite analisi approfondite, casi studio su resilienza operativa e aggiornamenti di settore.
- **Sequenza:**
  1. `step_nurture_1`: Case study su reti operative resilienti.
  2. `delay_nurture_1`: Attesa 72 ore.
  3. `step_nurture_2`: Nuovi asset digitali disponibili nella Vault.
- **Condizione di Uscita:** `unsubscribed` arresta il flusso.

---

## 4. `winback_dormant` (`flows/winback_dormant.yml`)
- **Trigger:** Schedule settimanale (lunedì) per contatti nello stato `DORMANT` (>60 giorni di inattività).
- **Obiettivo:** Riattivazione consensuale o soppressione preventiva per tutelare la reputazione del dominio mittente (`dependex.support`).
- **Sequenza:**
  1. `step_winback_1`: Check-in di valore per verificare l'interesse.
  2. `delay_winback_1`: Attesa 7 giorni.
  3. `step_winback_2`: Notifica di disattivazione programmata con link di conferma opzionale.
- **Condizione di Uscita:** Evento `email_opened` riattiva il contatto e arresta la sequenza di soppressione.
