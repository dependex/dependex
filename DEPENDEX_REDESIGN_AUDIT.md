# DEPENDEX.SOCIAL — AUDIT STRATEGICO, UX, TECNICO E COMUNICATIVO
**Documento:** DEPENDEX_REDESIGN_AUDIT.md  
**Data:** 2026-09-18  
**Stato:** AUDIT GLOBALE COMPLETATO  
**Governance:** Conforme al protocollo Karpathy (SPEC → VERIFIER → ENVIRONMENT) e AGENTS.md

---

## 1. EXECUTIVE SUMMARY

DEPENDEX.SOCIAL custodisce un patrimonio straordinario: un registro mondiale normalizzato di oltre 540 nodi territoriali (Club Alcologici Territoriali, ACAT, ARCAT, AICAT, WACAT), 40 anni di solido impianto metodologico ecologico-sociale (Prof. Vladimir Hudolin) e un'infrastruttura PHP/SQLite autonoma, veloce e senza dipendenze pesanti da container.

Tuttavia, l'attuale esperienza utente presenta una frattura critica tra la sua missione profonda e la sua presentazione digitale:
1. **Punto di vista organizzativo e commerciale invece che umano:** La prima schermata attuale accoglie l'utente con claim di "reframing cognitivo", "frequenze neon arcobaleno", offerte editoriali "Amazon KDP a 3 Tier" e carrelli di checkout, anziché porsi come la **porta d'ingresso digitale accogliente verso una comunità umana reale**.
2. **Attrito cognitivo e terminologico:** Una persona in stato di sofferenza, paura o solitudine (o un suo familiare) viene investita da concetti complessi prima ancora di ricevere una risposta rassicurante a due domande primarie: *"Posso venire anche io?"* e *"Cosa succede davvero se entro in un Club?"*.
3. **Sovraccarico visivo "Cosmic Neon":** L'estetica attuale (sfondo nero totale con bagliori neon saturati e bordi arcobaleno accesi) evoca un'interfaccia da crypto-dashboard o gaming platform, stridendo con la calda empatia e sobrietà richiesta da una comunità multifamiliare di solidarietà.

---

## 2. AUDIT ARCHITETTURA ATTUALE E COMPONENTI

| Componente / File | Ruolo Attuale | Punti di Forza | Criticità Rilevate | Azione di Restyle |
| :--- | :--- | :--- | :--- | :--- |
| `_header.php` | Testata globale, drawer, contatori | SEO meta solidi, Schema.org ben impostato, contatori live | Drawer caotico, troppi link eterogenei, badge DRX confusi per un visitatore esterno | Semplificare la navigazione primaria su 6 pilastri + "Parla con noi"; progressive disclosure per il resto |
| `_footer.php` | Piede di pagina globale | Contatti chiari (Segreteria di accoglienza, email governance) | Molti link accatastati, elementi commerciali (crociere, KDP) sovradimensionati | Riorganizzare con gerarchia chiara, human touch e rispetto della privacy |
| `index.php` | Homepage pubblica | Molti dati, news ticker, box lead | Inizia con terminologia aggressiva ("Basta con la favola..."), manca la sequenza empatica Persona → Club | Riscrivere completamente secondo il flusso in 10 sezioni: "Hai bisogno di parlarne?", "Trova la tua comunità", "Cosa succede al Club" |
| `world-club-explorer.php` | Directory e ricerca nodi | Ricerca geografica funzionante, mappa integrata, censimento completo | Molto formale/tecnico, filtri orientati all'anagrafica amministrativa | Trasformare la ricerca in una scoperta empatica con feedback accogliente |
| `club-public.php` | Scheda del singolo Club | Schema.org NGO/Geo, link WhatsApp, gerarchia ACAT | Sembra una scheda anagrafica di un database; scarse risposte emotive al visitatore | Trasformare in un vero e proprio "Portone d'ingresso": "Conosci questo Club", "Contatta", "Come arrivare" |
| `metodo.php` | Metodo Hudolin | Contenuto teorico ricco e accurato | Monolitico e accademico, rischio di barriera all'ingresso | Strutturare sui 3 livelli progressivi: 1. Scopri, 2. Comprendi, 3. Approfondisci |
| `academy-public.php` | Formazione | Percorsi strutturati | Orientato a "DRX reward" e tokenica interna, poco leggibile per esterni | Riorganizzare nei 4 pilastri: Scopri, Impara, Formati, Contribuisci |
| `events-public.php` | Hub eventi | Evento Taglio di Po con prenotazione funzionante | Focalizzato su un singolo evento in layout 9:16 rigido | Aprire a calendario vivo della comunità nazionale/locale |
| `help.php` | Aiuto e orientamento | Numeri verdi (112, 800 974250), FAQ | Manca una pagina e un flusso dedicato "Parla con noi" multicanale | Integrare e potenziare con la nuova pagina dedicata `parla-con-noi.php` |
| `storie.php` *(Nuovo)* | Storie di comunità | Non presente | Manca lo storytelling umano reale (Prima → Il momento del cambiamento → Il Club → La comunità → Oggi) | Creare ex-novo con garanzie di anonimato e protezione privacy |

---

## 3. ANALISI DEI PROBLEMI SPECIFICI

### A. Comunicazione & Tono di Voce
- **Problema:** Toni a volte giudicanti o iperbolici ("Basta raccontarti la favola...", "Leve di potere personale"), formule mutuate dal marketing di persuasione che non si addicono a un servizio sociale di accoglienza per persone vulnerabili.
- **Risoluzione:** Tono caldo, piano, accogliente, non giudicante, propositivo: *"Non devi sapere già tutto. Puoi semplicemente iniziare."*

### B. User Experience (UX) e Flussi di Conversione
- **Problema:** L'utente che cerca aiuto deve navigare tra concetti di "Rank Seme", "Token DRX", "Iniziativa CIURMA", "Manuali KDP" prima di trovare il Club della propria città.
- **Risoluzione:** Il percorso primario diventa lineare:
  `"Ho bisogno di parlare"` → `"Posso venire?" (Sì)` → `"Trova una comunità"` → `"Cosa succede al Club"` → `"Contatta"` → `"Partecipa"`.

### C. Design Visivo & Accessibilità
- **Problema:** Palette eccessivamente scura e satura con gradienti al neon che affaticano la vista e generano problemi di contrasto su schermi a bassa luminosità o sotto luce solare.
- **Risoluzione:** Introduzione di una nuova estetica **Human + Digital + Community**: superfici chiare e ariose (o dark mode morbido e bilanciato), tipografia leggibile, accenti caldi di terra e verde speranza, focus states visibili per WCAG 2.2 AA, supporto pieno a `prefers-reduced-motion`.

### D. Architettura dei Dati e Integrità
- **Punto Fermo:** Nessun dato esistente verrà cancellato o corrotto. Il database SQLite `data/acat_community.sqlite` contiene tabelle stabili e collaudate (`dependex_world_registry`, `events`, `event_bookings`, `academy_courses`). La nuova interfaccia si appoggerà alle query esistenti valorizzando le informazioni già presenti.

---

## 4. CONCLUSIONE DELL'AUDIT

DEPENDEX non ha bisogno di nuove complessità tecniche: ha bisogno di **chiarezza, empatia e rigore umano**. La tecnologia deve retrocedere per fare spazio alla relazione. Il restyle globale trasformerà la piattaforma nel portale accogliente che la rete dei Club merita.
