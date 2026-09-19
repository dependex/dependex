# DEPENDEX.SOCIAL — DESIGN SYSTEM "HUMAN + COMMUNITY"
**Documento:** DEPENDEX_DESIGN_SYSTEM.md  
**Data:** 2026-09-18  
**Versione:** 1.0  
**Concetto Visivo:** HUMAN + DIGITAL + COMMUNITY (La tecnologia al servizio della relazione)

---

## 1. PRINCIPI DEL DESIGN SYSTEM

1. **Empatia Visiva vs Freddezza Clinica:**
   Nessun elemento deve richiamare una cartella medica o un portale ospedaliero. Lo spazio deve trasmettere accoglienza, dignità, respiro e calore.
2. **Connessione vs Database:**
   I Club non sono record di un database; sono cerchi vivi di persone. Gli elementi visivi rappresentano nodi di una rete accogliente, cerchi di incontro, mani tese e ponti relazionali.
3. **Leggibilità & Accessibilità Universale (WCAG 2.2 AA):**
   - Contrasto minimo 4.5:1 per testo normale, 3:1 per testo grande e controlli grafici.
   - Bersagli di tocco (touch targets) su mobile mai inferiori a 48x48px.
   - Indicatori di stato chiari con supporto a lettori di schermo (ARIA).
   - Rispetto rigoroso di `prefers-reduced-motion`.
4. **Respiro e Spaziature Generose:**
   Eliminazione del senso di affollamento. Gli spazi vuoti permettono a chi legge in uno stato di agitazione o incertezza di comprendere senza essere sopraffatto.

---

## 2. PALETTE CROMATICA ARMONIZZATA

```text
┌─────────────────┬───────────────────┬───────────────────────────────────────────┐
│ Token           │ Valore Hex        │ Utilizzo                                  │
├─────────────────┼───────────────────┼───────────────────────────────────────────┤
│ --color-night   │ #0B132B / #0E1424 │ Sfondo dark mode riposante (blu notte profondo)│
│ --color-surface │ #162038 / #FFFFFF │ Superfici card e contenitori              │
│ --color-amber   │ #E0A96D / #D4AF37 │ Calore umano, speranza, accoglienza       │
│ --color-emerald │ #2A9D8F / #10B981 │ Rinascita, sobrietà serena, vita sana     │
│ --color-coral   │ #E76F51 / #FF5A5F │ Ascolto attivo, attenzione non giudicante │
│ --color-sky     │ #38BDF8 / #0284C7 │ Rete digitale, territori, collegamenti    │
│ --color-text    │ #F8FAFC / #1E293B │ Testo primario ad altissimo contrasto     │
│ --color-muted   │ #94A3B8 / #64748B │ Testo secondario e note informative       │
│ --color-wa      │ #25D366           │ Canale WhatsApp diretto di accoglienza    │
└─────────────────┴───────────────────┴───────────────────────────────────────────┘
```

---

## 3. TIPOGRAFIA & GERARCHIA

- **Headings Umani & Narrativi (`--font-display`):**
  Font con grazie humanist o serif moderno per trasmettere autenticità, riflessione e rispetto (es. Georgia, Merriweather, o serif di sistema ad alta leggibilità).
- **Interfaccia & Dati (`--font-sans`):**
  Sans-serif pulito e moderno (Inter, system-ui, -apple-system, Segoe UI, Roboto) con interlinea rilassata (1.6 - 1.75).
- **Scale:**
  - `Hero Title:` `clamp(2rem, 5vw, 3.4rem)` — Peso: 800 — Interlinea: 1.15
  - `Section Title:` `clamp(1.6rem, 3.5vw, 2.4rem)` — Peso: 700
  - `Card Title:` `1.25rem - 1.45rem` — Peso: 700
  - `Body Copy:` `1.05rem - 1.15rem` — Line-height: 1.7 (facilita la lettura a persone sotto stress emotivo).

---

## 4. COMPONENTI FONDAMENTALI

### A. Card "La Porta del Club" (`.club-door-card`)
- **Aspetto:** Superficie morbida con bordo delicato, non un record tabellare ma un portale d'accesso locale.
- **Contenuto essenziale:**
  - Nome del Club e Comune
  - Giorno e ora dell'incontro (con badge verde rassicurante)
  - Indirizzo leggibile con link a mappa
  - Azione primaria: `[ Conosci questo Club ]` o `[ Contatta ]`
  - Badge "Partecipazione Gratuita e Aperta alle Famiglie"

### B. Il Viaggio del Primo Arrivo (`.club-journey-stepper`)
- Visualizzazione in 6 tappe del percorso di accoglienza:
  1. Arrivi
  2. Trovi altre persone
  3. Ascolti
  4. Parli solo quando te la senti
  5. Conosci la comunità
  6. Decidi tu il tuo prossimo passo

### C. Barra di Ricerca Comunitaria (`.community-search-box`)
- Campo di ricerca principale ampio, con placeholder rassicurante: *"Inserisci la tua città, CAP o territorio..."*
- Nessun messaggio tecnico se non ci sono risultati, ma transizione dolce a: *"Non abbiamo trovato un Club esatto in questa frazione, ma ecco i più vicini o parlane subito con noi."*

### D. Box e Pulsante "Parla con Noi" (`.talk-gateway-box`)
- Presente in ogni pagina chiave.
- Canale WhatsApp diretto (Grazia Nicosia, Segreteria di accoglienza).
- Canale Email istituzionale (`info@dependex.support`).
- Numero verde AICAT gratuito (800 974250).

---

## 5. STATI DI ERRORE ED EMPTY STATES UMANI

- **Regola Dura:** Mai mostrare alert tecnici ("0 results found", "Database query exception", "Error 404").
- **Esempio Empty State Ricerca:**
  > *"Nessun Club trovato con questo termine di ricerca.*  
  > *Non preoccuparti: i Club sono presenti in tutta Italia e all'estero. Chiamaci o scrivici su WhatsApp per trovare subito il cerchio più vicino a te."*  
  > `[ Scrivi su WhatsApp ]` · `[ Parla con noi ]`
