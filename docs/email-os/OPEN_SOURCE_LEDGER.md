# OPEN SOURCE COMPONENT LEDGER
**Universal Email Revenue OS — Harvest & Reuse Register**  
**Versione:** 1.0 — 2026-09-06

| Upstream Tool / Repo | Scopo Architetturale | Licenza | CVE / Security | Decisione | Motivazione Ingegneristica |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Pillow (PIL)** | Image & Banner Generation | HPND | PASS (Zero CVE attive) | **REUSE** | Generazione nativa di banner 1200x630 e 1080x1080 per email e social con branding Dependex |
| **ReportLab** | Generatore Ricevute PDF | BSD | PASS | **REUSE** | Emissione automatica di ricevute e conferme fiscali post-acquisto PayPal |
| **listmonk** | Architettura Email/Queue | AGPL-3.0 | PASS | **ADAPT** | Estratti i principi architetturali di suppression list, unsubscription RFC 8058 e consent ledger |
| **Dittofeed** | Customer Journey State Machine | Apache-2.0 | PASS | **ADAPT** | Adottato il modello di eventi e la segmentazione comportamentale basata su eventi temporizzati |
| **MJML** | Responsive Email Markup | MIT | PASS | **REUSE** | Utilizzato come standard per layout fluidi multi-client (Outlook, Gmail, Apple Mail) |
| **Boto3 (AWS SDK)** | Transport Amazon SES | Apache-2.0 | PASS | **ADAPT** | Layer di trasporto unificato con failover trasparente su Hostinger SMTP SSL :465 |
| **SQLite3 Engine** | Persistent Relational Store | Public Domain | PASS | **REUSE** | Zero dipendenze esterne, zero container, 100% ACID nativo per i 7.445 lead e tracciamento eventi |
