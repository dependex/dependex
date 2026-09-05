# Company Brain — cos'è, in una pagina

## Il problema

Un'organizzazione sa un sacco di cose che non stanno da nessuna parte di
utilizzabile: sono in un PDF, in una cartella condivisa, in una vecchia mail,
nella testa di chi lavora lì da otto anni. Quando qualcuno chiede "come
funziona il reso?", la risposta esiste — ma non si trova.

## Cosa fa questo modulo

Prende i tuoi documenti e li trasforma in **una rete di conoscenza
interrogabile**: ogni pezzo di testo diventa un nodo, i nodi si collegano fra
loro da soli, e una domanda in italiano corrente tira fuori i pezzi giusti
**con la fonte accanto**.

Poi si controlla da solo: dice quanti nodi sono isolati, quali collegamenti
sono rotti, e quanto spesso il recupero trova davvero la risposta attesa su un
banco di prova che scrivi tu.

## Cosa NON fa — e conviene saperlo subito

- **Non è un modello di intelligenza artificiale e non scrive risposte.**
  Prepara il contesto e il prompt; se vuoi una risposta scritta, il modello lo
  chiami tu, con la tua chiave, dove ti pare. Il cervello resta tuo.
- **Non usa embedding né ricerca semantica.** È un motore lessicale con un
  grafo: sinonimi presi da un dizionario che scrivi tu, frequenza dei termini,
  collegamenti fra i nodi. Funziona bene sulle domande fatte con le parole di
  casa; funziona meno bene sulle parafrasi lontane. È dichiarato, non nascosto.
- **Non garantisce che la risposta sia giusta.** Garantisce che ogni risposta
  sia riconducibile a un tuo documento. È una cosa diversa, ed è più utile.
- **Non manda niente a nessuno.** Nessuna telemetria, nessun servizio esterno.

## Cosa serve per farlo girare

PHP 8.1 e nient'altro. Niente composer, niente pacchetti, niente Docker,
niente Redis, niente servizi in abbonamento. Va su un hosting condiviso da
tre euro al mese. Il database può essere un file SQLite oppure il MySQL che hai
già: **lo stesso codice**, senza modifiche.

## Come è fatto, in quattro parole

| | |
|---|---|
| **Neuroni** | ogni pezzo di documento è un nodo, con la sua fonte e la sua visibilità |
| **Sinapsi** | i nodi si collegano da soli: per sequenza, per concetto, per entità in comune |
| **Muro** | ogni nodo nasce riservato; diventa pubblico solo se un umano lo decide |
| **Prove** | salute del grafo e hit-rate del recupero sono numeri, non opinioni |

## Il patto

Tre regole che il modulo si impone, e che puoi verificare leggendo il codice:

1. **Niente dati di progetto nel motore.** Sinonimi, entità, cartelle, colori,
   domande di prova: tutto in `config/`. Il codice non sa che azienda sei.
2. **In caso di dubbio si chiude, non si apre.** Manca la chiave? L'endpoint è
   chiuso. Manca la visibilità su un nodo? È riservato. Sempre.
3. **Quello che non è stato provato viene detto.** Nel README e nei rapporti
   c'è scritto cosa è stato misurato e cosa no.

## Da zero a funzionante

```bash
php bin/brain install      # crea le tabelle
php bin/brain demo-seed    # ~200 neuroni finti di un'azienda inventata
php bin/brain eval         # quanto trova? un numero, subito
php bin/brain health       # com'è messo il grafo?
```

Poi apri `ui/brain-3d.php` e guardi il cervello che pulsa — con i tuoi numeri
veri, non un'animazione finta.

Quando sei convinto: `php bin/brain demo-clear`, metti i tuoi documenti in
`data/inbox/`, `php bin/brain ingest --all`, e riscrivi il banco di prova con
le domande che ti fanno davvero i clienti.
