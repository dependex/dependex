# DEPENDEX.SOCIAL — AUTONOMOUS REVENUE SYSTEM
**Dominio:** dependex.social · **Brand:** DEPENDEX — "Global Community · Reclaim Life & Sovereignty"  
**Payoff:** "AL CLUB. COL CLUB." · **Co-progetto:** oltre.social  
**Data audit:** 2026-09-06 · **Metodo:** SPEC → VERIFIER → ENVIRONMENT (Andrej Karpathy)

---

## 1. Executive Revenue Diagnosis
- **Sblocco Transazioni:** Sostituzione della finta modale `alert()` in `offers.php` con l'infrastruttura d'ordine ed incasso reale (`api/orders.php`, PayPal Smart Buttons e bonifico con causale).
- **Allineamento Mittente Email (ADR-005):** Passaggio dell'identità mittente da `info@dependex.support` (dominio NXDOMAIN privo di record DNS) a `info@dependex.social`, che dispone già di record MX Hostinger, SPF `include:_spf.mail.hostinger.com ~all`, DKIM `hostingermail-a` valido e DMARC impostato.
- **Lead Capture Integrata:** Attivazione dei magneti ad alto valore ("Cassetta Attrezzi Primo Giorno", ricerca Club per CAP, Guida Famiglia) con cattura email e arruolamento istantaneo nel Welcome Flow a 12 passi.
- **Conformità RFC 8058 & GDPR:** Pagine dedicate `/email/unsubscribe.php` (one-click) e `/email/preferences.php` con gestione immutabile dei consensi e soppressioni in ≤ 1 secondo.
- **Termini di Vendita Legali:** Pubblicazione dei termini completi con recesso e garanzie con nome proprio in `/terms.php`.

---

## 2. Scala del Valore (Offerta Irrifiutabile)
1. **Magnete Gratuito:** "Cassetta Attrezzi Primo Giorno" (Checklist 7 cose da fare nelle 24h + Mappa Club più vicini).
2. **Ingresso (LIV.1):** Starter Kit & Diagnosi (Check-up, Cortex 30 gg, mappa operativa, Diario 30 giorni) — **€ 27**.
3. **Core (LIV.2):** Protocollo Completo & Trasformazione (Schema 5 Passi, Piattaforma + Academy, Sessioni Revisione, Audit 1-a-1, Modulo Famiglia) — **€ 497** (o 3 × € 185).
4. **Ricorrente (LIV.4):** Club Permanente & Supporto (Stanze settimanali, Cortex 24/7, webinar mensili) — **€ 39/mese** o **€ 390/anno**.
5. **Massimizzante (LIV.3):** Programma Elite & Affiancamento (Organizzazioni e Club leader, team dedicato, audit) — **€ 1.997** su candidatura.
