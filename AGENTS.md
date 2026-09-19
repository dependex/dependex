# AGENTS.md — PROTOCOLLO DI GOVERNANCE DELL'ECOSISTEMA
**Repository:** DEPENDEX.SOCIAL · OLTRE.SOCIAL · UNIVERSAL EMAIL REVENUE OS  
**Metodo di Riferimento:** SPEC → VERIFIER → ENVIRONMENT (Andrej Karpathy)  
**Versione:** 1.0 — 2026-09-06

---

## 1. REGOLE DURE E NON NEGOZIABILI (HARD CONSTRAINTS)

1. **Metodo Karpathy (Spec → Verifier → Environment):**
   - Non iniziare mai a produrre codice o configurazioni finali senza una Spec dettagliata e approvata.
   - Definire sempre i criteri di verifica (Pass/Fail) prima di eseguire.
   - Eseguire auto-verifica sistematica dell'output prima di dichiarare qualsiasi risultato.
2. **Identità & Trasporto Email:**
   - Indirizzo mittente ufficiale: `info@dependex.support`.
   - Host SMTP primario: `smtp.hostinger.com:465` (SSL).
   - Indirizzo di prova prioritario: `labomobile.lm@gmail.com`.
3. **Bonifica Terminologica Rigorosa:**
   - È vietato l'uso delle seguenti parole nel codice sorgente, documentazione, database, template o prompt:
     `magico`, `magic`, `M.A.G.I.C.`, `giorgian putanu`.
   - Zero riferimenti residui a `81plus` all'interno del progetto.
4. **Control Plane & No Docker:**
   - GitHub Actions funge da piano di controllo (CI/CD, QA, schedule, compilation, reporting).
   - Nessun container Docker: esecuzione nativa su OS/VPS Linux + Python 3.11+ / PHP 8.2+ / SQLite / PostgreSQL / Redis.
5. **Privacy & Deliverability by Design:**
   - Tracciamento consensi GDPR conforme (double opt-in, provenance, timestamp, retention).
   - Unsubscribe one-click RFC 8058 (`List-Unsubscribe` e `List-Unsubscribe-Post`).
   - Nessun acquisto o scraping illecito di liste contatti.
6. **Mobile-First / Responsive / Viewport Master Spec (Vincolo Tecnico Assoluto):**
   - **MAI progettare prima desktop e poi adattare mobile.** DEPENDEX nasce **MOBILE-FIRST ASSOLUTO** e scala progressivamente: `Mobile ↓ Tablet ↓ Desktop ↓ Large Desktop`.
   - **ZERO HORIZONTAL OVERFLOW:** Nessuna sbordatura ammessa. Se `scrollWidth > clientWidth`, la build responsive è bloccata e considerata FAILED.
   - **Viewport Target:** Smartphone (9:19, 19:9, 9:16), Tablet (portrait/landscape 4:3, 16:10), Desktop (16:9, 16:10, ultrawide).
   - **Header Fisso/Sticky Globale:** Sempre visibile con safe-area (`env(safe-area-inset-top)`), compatto su mobile (Logo, ☰, [CTA]), mai coprente contenuti.
   - **Altezza Dinamica & Touch:** `min-height: 100dvh` con fallback `100vh` (MAI `height: 900px` fissa), touch target minimo 44×44px, ancore con `scroll-margin-top`.
   - **No Fixed Width & No Content Hidden:** Nessun `width: [px fissi]` strutturale; MAI usare `display: none` per nascondere contenuti critici per rimediare al layout.
   - Specifica integrale dei 37 articoli in [`docs/MOBILE_FIRST_VIEWPORT_SPEC.md`](file:///c:/81PLUS_GLOBAL_MASTER/dependex.social/docs/MOBILE_FIRST_VIEWPORT_SPEC.md).
7. **Human Welfare OS 4.0 & Human Welfare Engine 5.0 (Vincolo Deontologico Assoluto):**
   - I modelli concettuali (Hudolin = relazione/comunità, Maslow = bisogni, Ruota della Vita = fotografia privata, Tradizioni vediche = Dharma/Artha/Kama/Moksha, 7 Energie del Welfare = Radicamento, Vitalità, Autonomia, Relazione, Espressione, Consapevolezza, Significato, Salutogenesi = Sense of Coherence, Self-Determination = Autonomia/Competenza/Relazione, COM-B = Capability/Opportunity/Motivation) sono esclusivamente **lenti maieutiche di orientamento e consapevolezza**, MAI diagnosi cliniche o classificazioni.
   - **Divieto Assoluto di "Wellness Score":** È vietato calcolare punteggi di benessere (es. "72/100"), indici di felicità o percentuali di rischio sociale.
   - È vietato nominare i chakra come struttura di prodotto o promettere guarigioni; l'energia del benessere è usata come metafora organizzativa ed esperienziale.
   - **Trauma-Informed UX & Small Steps Engine:** Massimo 3 opzioni post-esplorazione (zero overwhelm), domande aperte maieutiche, stile motivazionale senza imposizioni ("Se vuoi, puoi iniziare da qui").
   - La persona sceglie liberamente da dove iniziare ("Questa parte della mia vita oggi chiede attenzione") e il sistema risponde sempre: *"Partiamo da lì. Vediamo cosa può aiutarti a rimettere in movimento la tua vita e quali persone e comunità possono accompagnarti"*.
   - Specifiche integrali in [`docs/HUMAN_WELFARE_OS_4.md`](file:///c:/81PLUS_GLOBAL_MASTER/dependex.social/docs/HUMAN_WELFARE_OS_4.md) e [`docs/HUMAN_WELFARE_ENGINE_5.md`](file:///c:/81PLUS_GLOBAL_MASTER/dependex.social/docs/HUMAN_WELFARE_ENGINE_5.md).
8. **Omni-Welfare Gamification Engine 6.0 (Life Playground & No Pain Gamification — 60 Articoli):**
   - **Zero Minestrone & Separazione Rigorosa delle Fonti:** Le pratiche olistiche, somatiche, vediche, di respiro, Metodo H+, 44 protocolli in 6 aree, Metodo ABC, BetterWay (6 pilastri e Life Surf: *"Non puoi fermare le onde, puoi imparare a navigarle"*), Ikigai, `mircopregnolato.it` come source library accreditata, `BEWAY.LIFE` come input esterno da acquisire/analizzare senza presumere, e Metodo Hudolin NON sono un'unica disciplina e non sono cure mediche. Ogni metodo mantiene esplicita la propria fonte originale.
   - **Non Gamificare la Sofferenza & Zero "Wellness Score":** Vietato assegnare punti o badge a dolore, ansia, traumi, ricadute o sintomi. Vietato calcolare punteggi numerici di benessere.
   - **No Streak Punitivi:** Nessuna punizione o colpevolizzazione per giorni di assenza ("Bentornato", mai "hai perso la streak").
   - **Esperienza Prima dei Termini Tecnici:** L'utente accede da *"Come ti senti oggi?"* o *"Cosa vuoi esplorare?"* tra 11 portali maieutici, mai da etichette astratte. Zero manuali, zero complessità (<10 secondi per capire l'attività).
   - **Bussola a 9 Raggi (The Dependex Compass):** Al centro l'**IO**; attorno: Corpo, Mente, Relazioni, Famiglia, Lavoro, Risorse, Comunità, Significato, Territorio (Delta del Po).
   - **Screen Off → Life On:** Le micro-missioni guidate (30s, 60s, 2m, 5m, 10m, 81 giorni) spingono sempre all'incontro e all'azione nella vita reale e nei Club multifamiliari.
   - Specifica integrale dei 60 articoli congelata in [`docs/OMNI_WELFARE_GAMIFICATION_6.md`](file:///c:/81PLUS_GLOBAL_MASTER/dependex.social/docs/OMNI_WELFARE_GAMIFICATION_6.md).

---

## 2. FORMATO DI RISPOSTA STANDARD

Ogni interazione deve rispettare rigorosamente la sequenza:

```text
**SPEC**
[Obiettivo, Contesto, Decisioni, Trade-off, Criteri di successo, Cosa NON fare]

**VERIFIER**
[Criteri di valutazione Pass/Fail, Controlli di sicurezza e conformità]

**ESECUZIONE**
[Codice, configurazioni, azioni svolte o richiesta mirata di approvazione]

**AUTO-VERIFICA**
[Valutazione oggettiva rispetto ai criteri definiti]

phase:
read_state:
gaps_found:
repos_evaluated:
components_reused:
files_created_or_changed:
tests:
verifier_pass:
risks:
next_action:
checkpoint:
```

---

## 3. MASTER PROMPT REFERENCE
Vedi il prompt completo in [`MASTER_EMAIL_OS_PROMPT.md`](file:///c:/81PLUS_GLOBAL_MASTER/dependex.social/MASTER_EMAIL_OS_PROMPT.md).
