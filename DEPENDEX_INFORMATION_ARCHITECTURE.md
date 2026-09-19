# DEPENDEX.SOCIAL — ARCHITETTURA DELL'INFORMAZIONE
**Documento:** DEPENDEX_INFORMATION_ARCHITECTURE.md  
**Data:** 2026-09-18  
**Versione:** 2.0 (Global Restyle)

---

## 1. PRINCIPIO GUIDA: DAL SISTEMA ALLA PERSONA

La nuova architettura dell'informazione ribalta la prospettiva istituzionale mettendo al centro la persona che ha bisogno di ascolto, orientamento o comunità:

```text
PERSONA (Bisogno / Ricerca / Curiosità)
   ↓
"HAI BISOGNO DI PARLARNE?"
   ↓
"POSSO VENIRE? COSA SUCCEDE AL CLUB?" (Rassicurazione e Abbattimento Barriere)
   ↓
"TROVA LA TUA COMUNITÀ" (Ricerca Territoriale per Città / CAP / Regione)
   ↓
PORTA D'INGRESSO DEL CLUB (Contatto Diretto / Orari / Giorno / Sede)
   ↓
"PARTECIPA" (Primo Incontro Reale nel Cerchio Multifamiliare)
   ↓
"VIVI LA COMUNITÀ" (Eventi, Incontri, Relazioni)
   ↓
"IMPARA E APPROFONDISCI" (Metodo Hudolin, Formazione Servitori, Academy)
   ↓
"STORIE E CONDIVISIONE" (Testimonianze di Vita e Sovranità)
```

---

## 2. MAPPA DELLA NAVIGAZIONE PRINCIPALE

La barra di navigazione globale (desktop e mobile drawer) adotta 6 pilastri fondamentali più un'azione primaria di contatto empatico:

```text
┌────────────────────────────────────────────────────────────────────────────────────────┐
│  DEPENDEX · AL CLUB. COL CLUB.                                                         │
│                                                                                        │
│  [Home]  [Trova un Club]  [Vivi la Comunità]  [Storie]  [Impara]  [Rete]  [PARLA CON NOI]│
└────────────────────────────────────────────────────────────────────────────────────────┘
```

### Dettaglio dei Pilastri Primari

1. **HOME (`/` o `index.php`):**
   - Porta d'ingresso universale.
   - Flusso in 10 sezioni empatiche: dal bisogno di parlare alla ricerca del Club, fino alle storie e alla rete mondiale.

2. **TROVA UN CLUB (`world-club-explorer.php` / `club/:id`):**
   - Motore di ricerca rapido per Città, CAP, Regione o Nazione.
   - Vista a mappa interattiva 2D/3D (`world-map.php`).
   - Scheda accogliente del singolo Club (con orari, sede, WhatsApp, telefono, email e "Cosa aspettarsi al primo incontro").

3. **VIVI LA COMUNITÀ (`events-public.php` / `event-detail.php`):**
   - Calendario vivo degli incontri, corsi esperienziali (es. Corso Taglio di Po con Adelmo Di Salvatore), assemblee e momenti aperti alle famiglie.

4. **STORIE (`storie.php`):**
   - Racconti reali di cambiamento e rinascita da parte di persone e famiglie dei Club, protetti da rigoroso anonimato.
   - Struttura: *Prima → Il momento della svolta → L'arrivo al Club → La comunità oggi*.

5. **IMPARA (`metodo.php` e `academy-public.php`):**
   - **Livello 1: Scopri:** Cos'è un Club? Perché le famiglie? Cos'è l'approccio ecologico-sociale?
   - **Livello 2: Comprendi:** La figura e l'opera del Prof. Vladimir Hudolin, i principi scientifici e sistemici.
   - **Livello 3: Formati (Academy):** Moduli per Servitori-Insegnanti, corsi di sensibilizzazione, aggiornamento permanente.

6. **RETE MONDIALE (`global-network.php` o vista Explorer Rete):**
   - La mappa viva delle oltre 540 realtà territoriali: Club Locali (CAT), Associazioni Territoriali (ACAT), Federazioni Regionali (ARCAT), Associazione Nazionale (AICAT) e presenze internazionali.

7. **CTA DIRETTA: [ PARLA CON NOI ] (`parla-con-noi.php`):**
   - Non una fredda pagina "Contatti" aziendale, ma una porta d'ascolto:
     - Segreteria di accoglienza e ascolto orientativo (Rete dei Club)
     - Email di ascolto e orientamento (`info@dependex.support`)
     - Modulo riservato per essere ricontattati dal Club più vicino
     - Numeri di emergenza medica (112) e Telefono Verde Alcol ISS (800 632 000).

---

## 3. NAVIGAZIONE SECONDARIA (PROGRESSIVE DISCLOSURE)

Per preservare la massima pulizia visiva e non disorientare il nuovo arrivato, le risorse di approfondimento, amministrative o didattiche avanzate sono collocate in sottomenu contestuali o nel footer:

- **Ecosistema & Risorse:**
  - Guida Gratuita per la Famiglia (`guida-gratuita.php`)
  - Libri e Manuali Metodologici Amazon KDP (`offers.php`)
  - Viaggi Esperienziali e percorsi residenziali (`viaggi-esperienziali.php`)
- **Privacy, Trasparenza & Sicurezza:**
  - Riservatezza & Anonimato (`privacy.php`)
  - Centro Consensi GDPR (`privacy-center.php`)
  - Termini e Condizioni di Partecipazione (`terms.php`)
  - Statuto del Volontariato e Trasparenza ACAT Basso Polesine O.D.V.

---

## 4. GRAFO DELLE RELAZIONI INTERNE (CONNECTED KNOWLEDGE GRAPH)

Nessuna pagina deve essere un vicolo cieco. Ogni entità dialoga dinamicamente con le altre:

```text
          ┌──────────────┐
          │   PERSONA    │
          └──────┬───────┘
                 │
                 ▼
         ┌───────────────┐
         │  CITTA' / CAP │
         └───────┬───────┘
                 │
                 ▼
         ┌───────────────┐           ┌───────────────────┐
         │ SCHEDA CLUB   │ ◄───────► │ EVENTI COLLEGATI  │
         └───────┬───────┘           └─────────┬─────────┘
                 │                             │
                 ├─────────────────────────────┤
                 ▼                             ▼
         ┌───────────────┐           ┌───────────────────┐
         │ STORIE REALI  │ ◄───────► │ METODO HUDOLIN    │
         └───────┬───────┘           └─────────┬─────────┘
                 │                             │
                 └──────────────┬──────────────┘
                                ▼
                     ┌───────────────────┐
                     │  PARLA CON NOI    │
                     └───────────────────┘
```

- Dalla lettura di una **Storia**, l'utente può cliccare su *"Trova il Club più vicino a te"* o *"Scopri come funziona il primo incontro"*.
- Dalla scheda di un **Club**, l'utente vede gli **Eventi territoriali** previsti e il contatto diretto del Servitore.
- Dalla pagina del **Metodo Hudolin**, l'utente viene guidato a sperimentare il cerchio nel Club locale.
- Da qualsiasi pagina, il pulsante **"Parla con noi"** consente di sciogliere ogni dubbio in tempo reale.
