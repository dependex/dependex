# Il bus degli agenti

## A cosa serve

Quando più agenti (o più persone, o un agente e una persona) lavorano sullo
stesso progetto, il problema non è farli parlare: è **impedire che si pestino i
piedi** e avere una traccia di chi ha deciso cosa.

Il bus fa tre cose, tutte su file, senza server e senza code esterne:

1. i messaggi sono **JSONL in sola aggiunta**: non si riscrive il passato, si
   aggiunge un messaggio nuovo che cambia lo stato;
2. i **lock** dichiarano "questo file lo sto toccando io", con una scadenza,
   così un lock dimenticato si spegne da solo;
3. **dashboard e brief** si rigenerano dai messaggi: sono derivati, non verità.
   La verità è il log.

Due implementazioni, **stesso formato e stessa cartella**: `bus/bus.php` e
`bus/bus.py`. Un agente in PHP e uno in Python lavorano insieme senza sapere
l'uno dell'altro.

---

## Il vocabolario

| | |
|---|---|
| **tipi** | `TASK` `DONE` `QUESTION` `ANSWER` `ALERT` `DECISION` `LOCK` `FORWARD` |
| **stati** | `OPEN` `IN_PROGRESS` `DONE` `BLOCKED` `CANCELLED` |
| **priorità** | `LOW` `MEDIUM` `HIGH` `CRITICAL` |
| **attori** | da `bus.actors` in config (default `AGENT_A`, `AGENT_B`, `HUMAN`, `SYSTEM`) |

Gli attori sono tuoi: mettici `PROGETTISTA`, `SVILUPPO`, `REDAZIONE`,
`CLIENTE`. Il bus non sa cosa fanno, sa solo tenere il conto.

---

## In pratica

```bash
php bus/bus.php init

# un task
php bus/bus.php send --from=AGENT_A --to=AGENT_B --type=TASK \
    "sistemare il calcolo dell'IVA" --files=app/fattura.php --priority=HIGH

# prendo il file, per due ore
php bus/bus.php lock app/fattura.php --actor=AGENT_B --hours=2

# ...lavoro...

php bus/bus.php send --from=AGENT_B --to=AGENT_A --type=DONE \
    "IVA sistemata" --ref=m-2026...-abcd1234 --state=DONE
php bus/bus.php unlock app/fattura.php --actor=AGENT_B

php bus/bus.php dashboard     # rigenera DASHBOARD.md e i brief
php bus/bus.php brief AGENT_B # cosa deve fare, adesso
```

In Python, identico:

```bash
python3 bus/bus.py doctor
python3 bus/bus.py send --from HUMAN --to AGENT_A --type QUESTION "a che punto siamo?"
```

`--ref` è il collegamento a un task esistente: **è così che si chiude un
task**, non modificando la riga di prima. Il log resta integro.

---

## Cosa trova da solo

| anomalia | quando scatta |
|---|---|
| **conflitto su file** | due task attivi assegnati ad attori diversi che toccano lo stesso file |
| **conflitto di lock** | due lock attivi sullo stesso file con proprietari diversi |
| **orfani** | task aperti da più di `bus.orphan_hours` senza una sola risposta |
| **doppioni** | task attivi con lo stesso titolo normalizzato e riferimenti diversi |

```bash
php bus/bus.php doctor
```

I conflitti sono la funzione che ripaga il resto: due agenti che riscrivono lo
stesso file in parallelo producono un merge che poi qualcuno deve districare a
mano.

---

## I file

```
data/bus/
  outbox/agent_a.jsonl      una casella per attore, in sola aggiunta
  outbox/agent_b.jsonl
  LOCKS.json                stato dei lock (mutabile, scritto atomicamente)
  DASHBOARD.md              generato
  BRIEF_AGENT_A.md          generato, uno per attore
  DECISIONS.md              storico delle decisioni, in sola aggiunta
  PROTOCOL.md               le regole, per chi arriva dopo
  _archive/                 log ruotati
  _errors/invalid.jsonl     righe scartate, con il motivo
```

La radice si sposta con `BRAIN_BUS_ROOT` (o `bus.root` in config). Se la metti
in una cartella sincronizzata (Drive, Dropbox, un repo git), il bus funziona
anche fra macchine diverse — con il limite della sincronizzazione, che non è
istantanea.

---

## Le scritture

- **atomiche**: file temporaneo + `rename`. Un lettore non vede mai un file a
  metà.
- **con lock**: un `.write.lock` esclusivo, con scadenza a 60 secondi. Se un
  processo muore col lock in mano, il successivo lo rimuove.
- **fsync** sugli append: il messaggio è su disco quando la funzione ritorna.

Le righe non valide non bloccano niente: finiscono in `_errors/invalid.jsonl`
con il motivo, e il resto viene letto normalmente.

---

## Cosa non fa

- **Non è una coda di lavoro.** Non c'è nessuno che esegue i task: il bus li
  registra e li mostra. Chi lavora sono gli agenti.
- **Non instrada automaticamente.** La versione originale da cui deriva aveva
  una tabella di regole per decidere "questo va al progettista, questo allo
  sviluppo": quelle regole erano dati di quel progetto e sono state tolte. Se
  ti servono, sono venti righe da aggiungere sopra `bus_append()`.
- **Non regge la concorrenza spinta.** Va bene per una manciata di attori che
  scrivono ogni tanto. Con cento agenti a raffica serve un database, non dei
  file.
