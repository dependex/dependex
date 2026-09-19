# DEPENDEX.SOCIAL
## MOBILE-FIRST / RESPONSIVE / VIEWPORT MASTER SPEC

Questa specifica è **OBBLIGATORIA** e integra il DEPENDEX.SOCIAL GLOBAL RESTYLE MASTER PROMPT e il protocollo di governance `AGENTS.md`.  
Non è una preferenza estetica. È un requisito architetturale, UX e frontend **vincolante e non negoziabile**.

---

# 1. MOBILE FIRST ASSOLUTO

DEPENDEX.SOCIAL deve essere progettato:

# MOBILE FIRST

Il primo layout da progettare, sviluppare e verificare è quello smartphone.

NON:

```text
Desktop → Tablet → Mobile
```

MA:

```text
Mobile
↓
Tablet
↓
Desktop
↓
Large Desktop
```

Ogni componente deve nascere funzionante su smartphone e successivamente espandersi.

---

# 2. VIEWPORT TARGET

Il sistema deve funzionare perfettamente nei principali rapporti e dimensioni dello schermo.

Target prioritari:

### SMARTPHONE
* 9:19
* 19:9
* 9:16
* 16:9 landscape

### TABLET
* portrait
* landscape
* 16:10
* 4:3

### DESKTOP
* 16:9
* 16:10
* ultrawide
* finestre ridimensionate.

Il requisito fondamentale è:

# NESSUNA SBORDATURA.

---

# 3. ZERO HORIZONTAL OVERFLOW

È VIETATO avere:

```text
horizontal overflow
horizontal scrolling
content clipped
buttons fuori viewport
cards più larghe dello schermo
testi che escono dal container
mappe che rompono il layout
immagini che superano il viewport
tabelle che creano overflow involontario
```

Il layout deve rispettare:

```css
max-width: 100%;
width: 100%;
box-sizing: border-box;
```

dove appropriato.

Usare:

```css
overflow-x: clip;
```

o una strategia equivalente solo come protezione finale.

NON usare `overflow-x: hidden` per nascondere problemi strutturali.

Il problema deve essere corretto alla radice.

---

# 4. VIEWPORT CONTRACT

Ogni pagina deve rispettare il seguente contratto:

```text
┌──────────────────────────────┐
│            HEADER            │
├──────────────────────────────┤
│                              │
│         PAGE CONTENT         │
│                              │
│                              │
│                              │
└──────────────────────────────┘
```

Nessun elemento importante deve essere:
* tagliato
* nascosto
* fuori schermo
* irraggiungibile
* coperto dall'header
* coperto da elementi fixed/sticky.

---

# 5. HEADER FISSO

L'header deve essere sempre visibile.

Implementare un:

# FIXED / STICKY GLOBAL HEADER

con comportamento coerente su:
* smartphone
* tablet
* desktop.

Preferenza:

```css
position: sticky;
top: 0;
```

oppure `fixed` quando tecnicamente necessario.

Se viene utilizzato `fixed`, il contenuto sottostante deve ricevere automaticamente lo spazio corretto.

MAI:

```text
header fixed
+
content che parte sotto l'header
+
primo contenuto nascosto.
```

---

# 6. HEADER MOBILE

Su smartphone l'header deve essere estremamente compatto.

Struttura indicativa:

```text
┌──────────────────────────────┐
│ DEPENDEX        ☰   [CTA]   │
└──────────────────────────────┘
```

Priorità:
1. logo/brand
2. menu
3. CTA principale.

Non inserire 8-10 elementi nell'header mobile.

La CTA principale può essere:

**TROVA UN CLUB**

oppure:

**PARLA CON NOI**

in base alla fase UX.

---

# 7. HEADER DESKTOP

Su desktop:

```text
┌──────────────────────────────────────────────────────┐
│ DEPENDEX   Trova Club  Comunità  Storie  Impara  [Parla] │
└──────────────────────────────────────────────────────┘
```

L'header deve restare leggibile senza occupare una porzione sproporzionata dello schermo.

---

# 8. SAFE AREA

Supportare dispositivi con notch e Dynamic Island.

Utilizzare quando necessario:

```css
padding-top: env(safe-area-inset-top);
padding-bottom: env(safe-area-inset-bottom);
padding-left: env(safe-area-inset-left);
padding-right: env(safe-area-inset-right);
```

Particolare attenzione a:
* iPhone
* Android con notch
* browser mobile con UI dinamica.

---

# 9. MOBILE VIEWPORT HEIGHT

NON assumere che:

```css
100vh
```

rappresenti sempre l'altezza visibile reale.

Preferire, dove appropriato:

```css
100dvh
```

con fallback compatibile.

Esempio:

```css
min-height: 100vh;
min-height: 100dvh;
```

Le hero full-screen devono essere testate con:
* browser address bar aperta
* browser address bar chiusa
* portrait
* landscape.

---

# 10. HERO RESPONSIVE

La hero non deve essere progettata con altezza fissa.

VIETATO:

```css
height: 900px;
```

quando questo causa problemi sui dispositivi.

Preferire:

```text
min-height
clamp()
responsive spacing
content-driven height
```

Il contenuto deve sempre rimanere completamente visibile.

Su smartphone:

```text
DEPENDEX

AL CLUB.
COL CLUB.

HAI BISOGNO
DI PARLARNE?

testo

[ TROVA IL TUO CLUB ]

[ PARLA CON NOI ]
```

Tutto deve stare dentro la viewport iniziale quando possibile, senza sacrificare accessibilità.

---

# 11. 9:19 MOBILE COMPOSITION

Il formato verticale prioritario è:

# 9:19

Progettare esplicitamente una composizione che funzioni in questa proporzione.

Il contenuto non deve essere semplicemente una versione compressa del desktop.

Deve essere una composizione verticale autonoma.

Gerarchia:

```text
BRAND
↓
MESSAGGIO
↓
CTA
↓
VISUAL
↓
NEXT ACTION
```

---

# 12. 16:9 DESKTOP COMPOSITION

Il formato orizzontale prioritario:

# 16:9

Deve avere una composizione specifica.

Esempio hero:

```text
┌────────────────────────────────────────────────────────┐
│ HEADER                                                 │
├────────────────────────────────────────────────────────┤
│                                                        │
│  TESTO                              VISUAL             │
│                                                        │
│  HAI BISOGNO                       MAPPA /             │
│  DI PARLARNE?                      COMMUNITY           │
│                                                        │
│  [ TROVA CLUB ] [ PARLA ]                             │
│                                                        │
└────────────────────────────────────────────────────────┘
```

Non semplicemente:

```text
mobile layout × 3
```

---

# 13. TABLET

Il tablet deve avere una vera composizione intermedia.

Non utilizzare automaticamente:

```text
desktop
```

su tablet.

Gestire:
* portrait
* landscape.

Quando lo spazio non è sufficiente:

```text
desktop navigation
↓
tablet navigation
↓
mobile navigation
```

Il breakpoint deve essere determinato dal contenuto, non da dispositivi specifici.

---

# 14. RESPONSIVE TYPOGRAPHY

Nessuna dimensione tipografica deve causare overflow.

Utilizzare `clamp()` dove appropriato.

Esempio concettuale:

```css
font-size: clamp(min, fluid, max);
```

Titoli molto lunghi devono:
* andare a capo naturalmente
* non essere troncati
* non uscire dal container.

NON utilizzare:

```text
white-space: nowrap
```

per contenuti editoriali importanti.

---

# 15. RESPONSIVE CONTAINERS

Definire un sistema coerente:

```text
viewport
↓
safe area
↓
page padding
↓
content max-width
↓
component
```

Desktop: contenuto centrato con `max-width`.  
Mobile: contenuto fluido con padding laterale sicuro.  
Nessun componente deve assumere una larghezza fissa incompatibile con il viewport.

---

# 16. GRID SYSTEM

Utilizzare una griglia responsive.

Indicativamente:
* **MOBILE:** 1 colonna
* **TABLET:** 2 colonne dove utile
* **DESKTOP:** 2–4 colonne secondo il contenuto

MA: la griglia deve essere **content-driven**. Non creare 4 colonne solo perché lo spazio lo permette. La leggibilità viene prima della densità.

---

# 17. BUTTONS

I pulsanti devono essere:
* completamente visibili
* tappabili
* responsive
* mai tagliati.

Su mobile: `[ TROVA IL TUO CLUB ]` deve poter diventare full-width quando appropriato.  
Touch target minimo:

# 44 × 44 px

preferibilmente superiore quando possibile.

---

# 18. MAPPA

La mappa è una componente critica. Deve essere responsive.

Mobile:
```text
MAPPA
↓
RISULTATI
↓
CLUB
```

Desktop:
```text
RISULTATI │ MAPPA
```

Non permettere mai che la mappa:
* allarghi la pagina
* crei overflow
* venga tagliata
* copra il contenuto.

---

# 19. CARDS

Le card devono adattarsi.

Mobile:
```text
┌────────────────────┐
│ CLUB               │
│                    │
│ città              │
│ giorno              │
│                    │
│ [ CONOSCI ]        │
└────────────────────┘
```

Desktop:
```text
┌──────────┐ ┌──────────┐ ┌──────────┐
│ CLUB     │ │ CLUB     │ │ CLUB     │
└──────────┘ └──────────┘ └──────────┘
```

Mai fissare larghezze che causano overflow.

---

# 20. IMMAGINI

Ogni immagine deve rispettare:

```css
max-width: 100%;
height: auto;
```

Dove appropriate. Utilizzare responsive images, `srcset`, lazy loading, aspect-ratio. Mai immagini che deformano il layout.

---

# 21. VIDEO

I video devono essere responsive.
* Per 16:9: `aspect-ratio: 16 / 9;`
* Per contenuti verticali: `aspect-ratio: 9 / 16;`

Mai video con dimensioni fisse superiori al container.

---

# 22. 9:16 / 16:9 CONTENT SYSTEM

DEPENDEX deve supportare nativamente entrambi i formati.
* **VERTICAL (9:16):** Ideale per stories, testimonianze, video reel, mobile content.
* **HORIZONTAL (16:9):** Ideale per desktop hero, video desktop, mappe, presentazioni, visual di rete.

Il CMS/content model deve poter definire entrambi.

---

# 23. NO FIXED WIDTH COMPONENTS

Evitare componenti come:

```css
width: 1200px;
width: 900px;
width: 700px;
```

quando non strettamente necessari.

Preferire:

```css
width: 100%;
max-width: ...;
```

I componenti devono adattarsi al container.

---

# 24. NO ABSOLUTE POSITIONING FOR STRUCTURE

Non utilizzare `position: absolute;` per costruire la struttura principale della pagina. Usarlo solo per elementi decorativi o overlay controllati.

Layout principale: CSS Grid, Flexbox, normal flow.

---

# 25. NO CONTENT HIDDEN TO FIX RESPONSIVENESS

NON risolvere un problema mobile facendo `display: none;` su contenuti importanti.  
Prima verificare se:
* può andare a capo
* può diventare una colonna
* può diventare uno scroll interno accessibile
* può cambiare ordine
* può essere trasformato in accordion.

Nascondere contenuti importanti è l'ultima soluzione.

---

# 26. OVERFLOW TEST

Ogni pagina deve essere verificata almeno a:

```text
320px, 360px, 375px, 390px, 414px, 430px, 768px, 820px, 1024px, 1280px, 1366px, 1440px, 1920px
```

e a diverse altezze viewport, in portrait e landscape.

---

# 27. DEVICE ORIENTATION

Ogni pagina importante deve funzionare in portrait e landscape senza perdita di contenuti.  
In particolare: Hero, Mappa, Scheda Club, Eventi, Academy, Form, Navigazione.

---

# 28. FIXED HEADER + SCROLL

Durante lo scroll l'header deve rimanere disponibile senza diventare invasivo:

```text
normal header
↓ scroll
compact header
```

La versione compatta mantiene: logo, menu, CTA principale.

---

# 29. ANCHOR SCROLL

Quando l'utente clicca un'ancora (`#trova-club`, `#eventi`, `#storie`), il contenuto non deve essere nascosto sotto l'header.  
Utilizzare:

```css
scroll-margin-top
```

coerente con l'altezza dell'header.

---

# 30. ACCESSIBILITY

Il fixed header non deve compromettere: keyboard navigation, screen reader, focus states, zoom, text resizing.  
Testare almeno: 200% text zoom, keyboard navigation, focus visibility.

---

# 31. BROWSER ZOOM

Il layout deve funzionare anche con 100%, 125%, 150%, 200% senza perdita delle funzioni principali.

---

# 32. FONT SCALING

Non utilizzare dimensioni assolute che impediscano il ridimensionamento. Preferire `rem`, `em`, `clamp()`.

---

# 33. SAFE DESIGN RULE

Ogni schermata deve poter essere descritta così:

> "Se faccio uno screenshot adesso, nessun elemento importante esce dal dispositivo."

Questa deve essere una regola di QA.

---

# 34. VISUAL QA

Per ogni release verificare:
* **MOBILE:** 9:19, 9:16
* **TABLET:** portrait, landscape
* **DESKTOP:** 16:9
* **LARGE:** 1920×1080

Verificare automaticamente: overflow, clipping, elementi sovrapposti, testo troncato, CTA fuori viewport, header che copre contenuti, immagini deformate, mappe rotte, card fuori griglia.

---

# 35. AUTOMATED QA

Aggiungere test automatici per individuare:

```javascript
document.documentElement.scrollWidth > document.documentElement.clientWidth
```

Se `scrollWidth > clientWidth`, la build responsive deve essere considerata **FAILED**. Non accettare il rilascio finché il problema non è risolto.

---

# 36. DEFINITION OF DONE — RESPONSIVE

DEPENDEX è responsive DONE solamente quando:
* **MOBILE:** nessuna sbordatura, header fisso, CTA accessibili, testo leggibile, mappe funzionanti, form utilizzabili, immagini corrette, nessun contenuto importante tagliato.
* **TABLET:** portrait OK, landscape OK, layout adattivo, header corretto.
* **DESKTOP:** 16:9 OK, contenuto centrato, nessun enorme spazio inutilizzato, navigazione leggibile.
* **LARGE DESKTOP:** nessun layout deformato, max-width applicato, contenuto leggibile.

---

# 37. PRINCIPIO FINALE

DEPENDEX deve sembrare progettato appositamente per ogni dispositivo. Non deve sembrare "un sito desktop che si è ristretto". Deve sembrare:

> **lo stesso mondo, adattato perfettamente allo spazio che hai davanti.**

# ONE DEPENDEX.
# EVERY SCREEN.
# ZERO FRICTION.
## MOBILE FIRST.
## FIXED HEADER.
## ZERO OVERFLOW.
## 9:19 · 9:16 · 16:9.
## PHONE · TABLET · DESKTOP.

**Tutto deve stare dentro lo schermo. Sempre.**
