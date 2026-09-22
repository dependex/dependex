# DEFINIZIONI UFFICIALI DEI DATI E DELLE METRICHE — DEPENDEX.SOCIAL
**Versione:** 1.0 — 2026-09-22  
**Stato:** Disciplinare di Governance e Semantica dei Dati  
**Applicazione:** Frontend, Backend, API, SEO, Documentazione, Comunicati

---

## 1. INTRODUZIONE & PRINCIPIO DI TRASPARENZA

Ai sensi dei principi deontologici di DEPENDEX.SOCIAL e delle linee guida di salute pubblica, ogni indicatore numerico deve avere un significato inequivocabile. È fatto esplicito divieto di sommare entità di livello gerarchico differente o di utilizzare termini ambigui per gonfiare artificialmente le dimensioni della rete.

---

## 2. TASSONOMIA DEI CLUB E DEI PRESIDI

### 2.1. Presidio Territoriale (Definizione Ombrello)
Un qualsiasi record validato all'interno del censimento nazionale (`cat_clubs_italy`), comprensivo sia dei Club multifamiliari locali sia delle strutture di servizio e coordinamento.
- **Valore attuale nel DB:** **1.761 presidi**.
- **Componenti:** 1.414 Club Locali + 347 Entità di Coordinamento.

### 2.2. Club Locale (CAT — Club Alcologico Territoriale)
La cellula di base del Metodo Hudolin: una comunità multifamiliare autonoma (composta tipicamente da un minimo di 2 a un massimo consigliato di 10-12 famiglie) guidata da un servitore-insegnante, che si incontra settimanalmente.
- **Identificatore DB:** `level = 'LOCAL_CLUB'`.
- **Valore attuale nel DB:** **1.414 Club locali**.

### 2.3. Entità di Coordinamento (APCAT / ACAT / ARCAT / AICAT)
Strutture associative e federative senza scopo di lucro (ODV, APS o reti territoriali) che garantiscono supporto logistico, formazione dei servitori-insegnanti, collegamento con i Servizi Pubblici (SerD, ASL) e tutela istituzionale.
- **Identificatore DB:** `level IN ('PROVINCIAL_APCAT', 'TERRITORIAL_ACAT', 'TERRITORIAL', 'REGIONAL', 'NATIONAL', 'ASSOCIATION', 'FEDERATION_REGIONAL')`.
- **Valore attuale nel DB:** **347 entità**.

---

## 3. I 5 STATI PUBBLICI TRASPARENTI DEI CLUB

Nessun Club può essere genericamente etichettato come "attivo" se non esiste un criterio oggettivo e recente di riscontro. Si adottano esclusivamente i seguenti 5 stati:

| Stato Pubblico | Definizione Operativa | Criterio Tecnico | Significato per la Famiglia |
| :--- | :--- | :--- | :--- |
| **CENSITO** | Il Club è presente nelle banche dati storiche o negli elenchi regionali, con recapito telefonico/email valido, ma la sede o l'orario di riunione necessita di conferma diretta. | Record presente in `cat_clubs_italy`, `phone != ''`, ma `meeting_day` non ancora confermato per il 2026. | *"Il Club esiste sul territorio. Contatta il referente per concordare il primo incontro."* |
| **IN VERIFICA** | È in corso un'interazione con il servitore-insegnante o con l'ACAT territoriale per convalidare i dati di incontro per l'anno in corso. | Record in coda di aggiornamento o con richiesta di riscontro aperta. | *"Stiamo aggiornando le informazioni con i volontari locali."* |
| **VERIFICATO** | Giorno della settimana, orario e sede di riunione sono stati confermati direttamente dal referente, dall'ente di coordinamento o tramite fonte ufficiale 2025/2026. | `meeting_day IS NOT NULL` e orario definito; riscontro diretto registrato. | *"Informazioni verificate: giorno e orario di riunione confermati."* |
| **DATI DA AGGIORNARE** | I dati del Club non ricevono conferme da oltre 18 mesi o è stata segnalata una variazione logistica non ancora perfezionata. | Data di ultima verifica > 540 giorni o flag di revisione attivo. | *"I dati potrebbero essere cambiati. Ti invitiamo a fare una telefonata prima di recarti sul posto."* |
| **CONTATTO DA CONFERMARE** | Il numero di telefono o l'indirizzo email ha generato un errore di recapito (es. bounce email o numero non attivo) ed è in corso l'assegnazione al coordinamento provinciale. | Flag `contact_unreachable = 1` o mancata risposta a 3 solleciti. | *"Il contatto diretto è in aggiornamento: chiama il Numero Verde Nazionale AICAT 800 974250."* |

---

## 4. METODOLOGIA DI CALCOLO DELLE FAMIGLIE

### 4.1. L'Errore del "Minestrone" (Cosa NON Fare)
Nel database, ad ogni riga di Club o Associazione è storicamente associato un campo `families_count` (convenzionalmente valorizzato a 11 o 12 in linea con le raccomandazioni del Prof. Hudolin).  
Se si esegue una query cieca `SUM(families_count)` sull'intera tabella:
- Si sommano le 11 famiglie di un Club locale;
- Si sommano nuovamente le famiglie sul record dell'ACAT provinciale che coordina quel Club;
- Si sommano una terza volta sul record dell'ARCAT regionale;
- Si sommano una quarta volta sull'AICAT nazionale.  
Il risultato produceva un numero fittizio e iperbolico compreso tra **51.500 e 85.337**, destituito di fondamento statistico e potenzialmente fuorviante.

### 4.2. La Regola di Calcolo Rigorosa (Standard DEPENDEX)
Il conteggio delle famiglie stimate accolte si calcola **esclusivamente sui nodi foglia di base** (`level = 'LOCAL_CLUB'`), escludendo totalmente ogni livello gerarchico intermedio o nazionale:
$$\text{Famiglie Accolte (Stima)} = \sum_{c \in \text{LOCAL\_CLUB}} c.\text{families\_count} = 1.414 \times 11,08 = \mathbf{15.678 \text{ famiglie}}$$
Tale dato deve essere sempre presentato pubblicamente con la dicitura:  
> *"Stima calcolata sui 1.414 Club locali censiti (media consigliata di 10-12 famiglie per Club secondo il Metodo Hudolin)"*.

---

## 5. SEPARAZIONE TEMPORALE: "OGGI" vs "STORICAMENTE"

Ogni comunicazione deve separare nettamente lo stato presente dalle pietre miliari del passato:
1. **Anno di Origine Metodologica:** **1964** (Ospedale Universitario di Zagabria, Prof. Vladimir Hudolin).
2. **Anno di Diffusione in Italia:** **1979** (primo Club istituito a Conegliano Veneto dal Dott. Francesco Piani con il Prof. Hudolin).
3. **Anni di Attività:** Oltre 45 anni in Italia (1979–2026), oltre 60 anni nel mondo (1964–2026).
4. **Censimento Attuale:** Rilevazione continua 2026 con normalizzazione georeferenziata su 1.761 presidi.
