# Recupero e qualità

## Cosa succede quando fai una domanda

```
"come funziona il reso di un articolo"
   │
   1. TERMINI      reso, articolo, funziona          (peso 1,0)
      + sinonimi   rimborso, restituzione, garanzia  (peso 0,45)
   │
   2. CANDIDATI    LIKE sui termini, muro di visibilità già in SQL
   │               → fino a rag.candidate_limit righe
   3. MURO         via i nodi che contengono pattern da segreto
   │
   4. PUNTEGGIO    frequenza × IDF-lite  +  bonus se il termine è nel percorso
   │               + peso del nodo (capato)
   │               + i sinonimi contribuiscono, ma con un TETTO
   5. SINAPSI      dai primi 3 si seguono i vicini a un salto (punteggio × 0,5)
   │
   6. RERANK       centralità + voti (clampati) + frase esatta + freschezza
   │
   7. DIVERSITÀ    max 2 chunk per documento
   │
   └→ top-K righe, ognuna con la sua fonte
```

### Perché i sinonimi hanno un tetto

Senza tetto, una voce di dizionario un po' larga ribalta la classifica. Caso
vero, misurato su questo modulo: la domanda *"come funziona il reso di un
articolo"* faceva vincere `catalogo-prodotti.md` — che non contiene **nessuna**
delle parole della domanda — solo perché "articolo" era stato espanso in
"prodotto/prodotti/catalogo" e quel documento ne era pieno.

La regola, ora esplicita nel codice:

```
punteggio = originali + min(sinonimi, 0,5 × originali + 1,0) + peso
```

**L'espansione allarga il recupero, non decide la classifica.** Un documento
che si trova solo per sinonimo resta in lista (recall salvo) ma non scavalca
chi contiene le parole vere. Effetto misurato sul banco di prova campione:
hit-rate da 0,917 a **1,0** e MRR da 0,875 a **0,958**.

### Perché IDF calcolata sul pool

I termini rari valgono più di quelli che stanno ovunque. Si calcola in PHP sui
candidati già recuperati: zero query in più, e si adatta da solo al tuo
dominio senza tabelle di frequenze da mantenere.

### Il reranking, detto per quello che è

Non è un modello neurale. È un'euristica a quattro segnali economici:

| segnale | perché |
|---|---|
| centralità | un nodo molto connesso è spesso il rappresentante giusto di un tema |
| voti | li dà la gente che legge; **clampati** a ±2, così i clic non ribaltano il motore |
| frase esatta | se la domanda compare così com'è, batte qualunque match sparso |
| freschezza | decadimento dolce (dimezzamento a 90 giorni) |

Un cross-encoder farebbe meglio. Serve una GPU o un servizio esterno: due cose
che questo modulo ha deciso di non avere.

---

## Il muro di visibilità

Due reti, non una.

1. **In SQL**: per il pubblico la clausola è `visibility = 'public'`. Non
   "diverso da admin": **uguale a public**. Un nodo senza visibilità non esce.
2. **In PHP**: prima di uscire, ogni riga passa da `brain_looks_secret()`. Se un
   segreto finisse per errore umano dentro un nodo promosso a pubblico, si
   ferma comunque qui.

I pattern sono in `security.leak_patterns` e sono affare tuo: aggiungi i
formati che nella tua azienda identificano un dato riservato (un codice
fiscale, un numero di contratto, un IBAN).

---

## Il prompt

`brain_build_prompt()` monta sempre le stesse regole:

- rispondi ancorato al contesto;
- cita la fonte fra parentesi quadre;
- se il contesto non basta, **dillo** (con il contatto, se configurato);
- niente credenziali, niente dati personali;
- se chi chiede è il pubblico, solo conoscenza pubblica.

`prompt.persona` e `prompt.extra_rules` sono i tuoi. Le regole di sicurezza no:
quelle ci sono sempre.

La memoria recente entra nel prompt **etichettata come dato, non come
istruzione**: senza quell'etichetta, una domanda scritta apposta da un utente
diventerebbe un ordine per tutte le conversazioni successive.

---

## Apprendimento continuo, col freno

Una risposta ancorata può diventare un nodo:

```php
brain_ask_complete($domanda, $risposta, $grounded = true);
```

Il nodo nasce `visibility: admin`, `review_state: pending`. Nessun contenuto
generato arriva al pubblico senza che un umano lo abbia approvato. Non si
impara se: la risposta è corta, non è ancorata, o contiene un pattern da
segreto.

Revisione: console → *"Imparato dalle conversazioni"* → approva / approva e
pubblica / rifiuta.

---

## Salute del grafo

```bash
php bin/brain health          # solo diagnosi
php bin/brain health --fix    # ripara
```

| cosa cerca | perché è un problema |
|---|---|
| orfani | nodi che nessun percorso raggiunge: nel grafo ci sono, ma non li trova nessuno |
| pendenti | sinapsi verso nodi cancellati: gonfiano la centralità e sballano il rerank |
| non canoniche / speculari | la stessa coppia due volte: gli stessi problemi, moltiplicati |
| auto-sinapsi | un nodo collegato a sé stesso conta doppio nella centralità |
| senza muro | nodi senza visibilità: rischio di fuga |
| entità orfane | righe che puntano a nodi morti: generano sinapsi verso id fantasma |

La riparazione: mette le coppie in ordine canonico, toglie doppioni e pendenti,
**chiude** i nodi senza muro, ripulisce le entità orfane e crea l'indice unico
anche su installazioni vecchie. Ogni esecuzione lascia un'istantanea in `meta`.

---

## Il banco di prova

```bash
php bin/brain eval
```

Domande fisse con la risposta attesa, in `config/benchmark.sample.json` (o
nella tabella `eval_questions`, che vince se popolata). Chiama la **funzione
vera** del recupero, la stessa della chat: non una simulazione.

- **HIT-RATE** = quante domande trovano l'atteso nei primi K
- **MRR-lite** = media di 1/posizione del primo risultato giusto

Misurato adesso, su 194 documenti finti + 12 domande campione:

```
HIT-RATE: 1,0     MRR-lite: 0,958     35 ms per 12 domande
```

**Sostituisci quelle domande con le tue.** Un banco di prova che non cambia mai
è l'unico modo onesto di dire "il motore è migliorato": lo rigiochi dopo ogni
modifica e vedi il numero muoversi. Le ultime 200 esecuzioni restano in
`eval_runs` per la tendenza.

Regola pratica: quando qualcuno segnala "non trova X", aggiungi X al banco di
prova **prima** di sistemare. Così quella cosa non si romperà mai più in
silenzio.

---

## Feedback

Voto pubblico (POST, uno per nodo/impronta/giorno) e correzione in testo libero.
Il voto sposta `feedback_score`; il rerank lo legge clampato. **La correzione
resta testo grezzo** in tabella: la si legge dalla console e si decide a mano.
Nessuna pubblicazione automatica di contenuto non rivisto.

## Riconciliazione

```bash
php bin/brain reconcile --fix
```

Confronta i registri col grafo: file che dicono 12 nodi e ne hanno 3, file
spariti dal disco ma ancora in memoria, voci di conoscenza senza nodi. Con
`--fix` rimette in coda ciò che va ridigerito e dimentica ciò che non esiste
più. Utile dopo un ripristino da backup o una migrazione.
