# UNIVERSAL EMAIL REVENUE OS — SPECIFICATION (P0 DISCOVERY)
**Progetto:** DEPENDEX.SOCIAL · OLTRE.SOCIAL  
**Data di Compilazione:** 2026-09-06  
**Status:** VALIDATED · BASELINE CONSOLIDATA

---

## 1. BRAND & IDENTITY
* **Brand Name:** DEPENDEX (co-brand: OLTRE)
* **Payoff:** AL CLUB. COL CLUB.
* **Domini Applicazione:** `https://dependex.social` | `https://oltre.social`
* **Dominio Ufficiale Email:** `dependex.support`
* **Indirizzo Mittente:** `info@dependex.support`
* **Reply-To:** `info@dependex.support`
* **Mercato & Lingua:** Italia (IT) — 100% Lingua Italiana istituzionale, empatica, neuro-linguistica senza giudizio.
* **Modello di Business:** Social-Impact Ecosystem (Rete volontaristica dei 542 Club Alcologici Territoriali Metodo Hudolin + Architettura di Valore Sovrano).

---

## 2. AUDIENCE & ICP (IDEAL CUSTOMER PROFILE)
* **Target 1 — Persona in Difficoltà (Clubber potenziale):**
  * Età 28–65, consapevolezza del problema, stanchezza da fallimenti solitari ("smetto quando voglio"), senso di colpa, ricerca di riservatezza totale.
* **Target 2 — Famiglie e Partner:**
  * Conviventi e figli che subiscono le tensioni, cercano un porto sicuro senza burocrazia né liste d'attesa.
* **Target 3 — Servitori-Insegnanti & Volontari:**
  * Figure formate o in formazione tramite Academy e moduli SAT per la facilitazione dei cerchi di Club.

---

## 3. PRODOTTI, OFFERTE & PRICING
1. **Accoglienza & Club Territoriale:** € 0 (100% Gratuito, Solidarietà Multifamiliare permanente).
2. **Starter Kit di Diagnosi & Orientamento:** € 27 una tantum (Valore reale € 190):
   * Check-up diagnostico, accesso 30gg Cortex AI, mappa operativa 542 Club, guida rapida primo giorno. Garanzia 30 giorni soddisfatto o rimborsato.
3. **Protocollo Completo & Trasformazione:** € 497 o 3 rate da € 185 (Valore accorpato € 2.588):
   * Academy completa, diario e registro, coaching di rete, cassetta attrezzi avanzata.
4. **Membership Operativa & Supporto Continuo:** € 97 / mese.

---

## 4. LEAD MAGNETS & FUNNEL
* **Magnete 1:** Mappa Interattiva 2D/3D Mondiale con 542 Club geolocalizzati e contatti diretti.
* **Magnete 2:** Test anonimo di autovalutazione del bisogno e orientamento con l'AI Cortex 24/7.
* **Magnete 3:** "La Guida dei 5 Passi del Metodo Hudolin" (disinnesco dell'ancora oraria delle 18:00).

---

## 5. INFRASTRUTTURA, DATABASE & CHECKOUT
* **Checkout Engine:** `modules/commerce/` nativo PHP/SQLite, PayPal REST API v2 (Client ID: `BAAKBi49VB018m1amrtC8kbKJMMEvZgZFsypE7shSG6-UOYOL3QkXMtwY6etxYNyFTmlj1AcnXlMePzhKQ`, mode: `live`).
* **Database Master:** SQLite (`data/acat_community.sqlite` + `data/commerce.sqlite` + `automation/emailflux/data/emailflux.db`).
* **Lista Iniziale Consolidata:** 7.445 lead master qualificati (`automation/emailflux/data/leads_master_7445.csv`).
* **Provider Email:** Hostinger SMTP SSL (`smtp.hostinger.com:465`, sender autenticato e autorizzato per `info@dependex.support`).

---

## 6. EVENT MODEL & TOUCHPOINTS
Eventi minimi tracciati nel Ledger:
* `lead_created`, `consent_granted`, `page_viewed`, `map_club_searched`, `cortex_session_started`, `cart_updated`, `checkout_started`, `purchase_completed`, `email_sent`, `email_delivered`, `email_opened`, `email_clicked`, `email_unsubscribed`, `email_bounced`.

---

## 7. KPI BASELINE
* **Deliverability Rate:** ≥ 98.5%
* **Unique Open Rate (Diagnostico):** ≥ 26.0%
* **Unique Click-Through Rate (CTR):** ≥ 3.5%
* **Unsubscribe Rate per Invio:** ≤ 0.20%
* **Spam Complaint Rate:** ≤ 0.04% (soglia critica 0.08%)
* **Hard Bounce Rate:** ≤ 0.8%
