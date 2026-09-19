# DEPENDEX & OLTRE · REPORT OPERATIVO DI CONTROLLO H24
**Data Generazione:** 19 Settembre 2026  
**Destinatario Primario:** `labomobile.lm@gmail.com`  
**Mittente Notifiche:** `info@dependex.support` (Hostinger SMTP SSL 465)  
**Infrastruttura:** Hostinger Native (Linux OS, PHP 8.3, SQLite, NGINX/LiteSpeed) — Nessun Container Docker.

---

## 1. STATO DEI SERVIZI E VERIFICA ONLINE (PROBE HTTP LIVE)

| Modulo & Scopo | Endpoint di Produzione | Status Code | Latenza | Dimensione HTML | Esito Operativo |
| :--- | :--- | :---: | :---: | :---: | :---: |
| **Accoglienza & Home** | `https://dependex.social/index.php` | **200 OK** | 2.221 ms | 258.487 bytes | **HEALTHY** |
| **Registro Globale 322+ Club** | `https://dependex.social/world-club-explorer.php` | **200 OK** | 2.404 ms | 404.720 bytes | **HEALTHY** |
| **Mappa 2D Interattiva Club** | `https://dependex.social/mappa-club.php` | **200 OK** | 2.006 ms | 327.092 bytes | **HEALTHY** |
| **Orientamento Maieutico 5.0** | `https://dependex.social/orientamento.php` | **200 OK** | 1.361 ms | 118.464 bytes | **HEALTHY** |
| **Life Playground 6.0** | `https://oltre.social/playground.php` | **200 OK** | 2.009 ms | 137.997 bytes | **HEALTHY** |
| **Motivational Clips 9:16** | `https://dependex.social/clips.php` | **200 OK** | 1.361 ms | 66.497 bytes | **HEALTHY** |
| **Eventi & Prenotazioni** | `https://dependex.social/events-public.php` | **200 OK** | 1.493 ms | 134.101 bytes | **HEALTHY** |
| **Canale Ascolto Anonimo** | `https://dependex.social/parla-con-noi.php` | **200 OK** | 1.159 ms | 61.168 bytes | **HEALTHY** |
| **Telemetria & Watchdog API** | `https://dependex.social/api.php?action=watchdog` | **200 OK** | 1.201 ms | 512 bytes | **HEALTHY** |
| **Portale Speculare Oltre** | `https://oltre.social/index.php` | **200 OK** | 2.555 ms | 258.454 bytes | **HEALTHY** |

---

## 2. METRICHE CHIAVE DI ECO-SISTEMA
- **Club Alcologici Territoriali (CAT) Attivi:** 322 censiti e georeferenziati con coordinate, contatti e orari d'incontro.
- **Micro-esperienze Life Playground:** 11 portali maieutici e 9 raggi di benessere (Bussola Dependex: Corpo, Mente, Relazioni, Famiglia, Lavoro, Risorse, Comunità, Significato, Territorio).
- **Regola Non Negoziabile:** *Screen Off → Life On* (la tecnologia serve solo a riportare la persona nel mondo reale e al Club).
- **Conformità Mobile-First:** Zero horizontal overflow (`scrollWidth == clientWidth`) su tutti i breakpoint standard.
- **Deontologia:** Zero diagnosi cliniche, zero wellness score, zero streak punitivi.

---

## 3. STATO TRASPORTO EMAIL SMTP HOSTINGER
- **Server:** `smtp.hostinger.com:465` (SSL diretto)
- **Account:** `info@dependex.support` / `info@dependex.social`
- **Diagnostica Outbound Hostinger:** L'accesso e l'autenticazione SMTP funzionano (`235 Authentication successful`). In caso di blocco `554 5.7.1 Outbound sending is disabled`, occorre semplicemente accedere a **hPanel Hostinger → Emails → Manage Email Accounts → Outbound Access** e confermare lo sblocco dell'invio in uscita della casella.
- **Watchdog H24:** Il demone locale monitora ininterrottamente tutti gli endpoint e tenterà l'invio continuo dell'alert email ad ogni variazione di stato.
