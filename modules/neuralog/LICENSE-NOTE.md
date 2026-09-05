# Nota di licenza e di responsabilità

## Cosa contiene questa cartella

Codice PHP e Python scritto da zero per questo modulo. **Nessuna libreria di
terzi è inclusa**: niente composer, niente pacchetti npm, nessun binario.
Le uniche dipendenze sono quelle che PHP porta di serie (PDO, mbstring, json,
e — facoltative — ZipArchive e curl).

## L'unica cosa esterna, ed è facoltativa

`ui/brain-3d.php` può usare **Three.js** per la vista in WebGL. Three.js non è
incluso in questo pacchetto. Due modi per averlo:

- lo scarichi tu e lo metti in `ui/vendor/three.min.js` (consigliato: nessuna
  chiamata verso l'esterno, nessun problema di CSP o di privacy);
- lo lasci prendere dal CDN indicato in `ui.three_cdn` (allora il browser del
  tuo utente contatta quel dominio: dillo nella tua informativa).

Three.js ha licenza MIT: se lo includi, includi anche il suo file di licenza in
`ui/vendor/`. Se non lo usi affatto, la pagina **funziona lo stesso**: ripiega
sul rendering integrato su canvas 2D, che non richiede nulla.

## Licenza di questo modulo

Decidila tu quando lo installi in un progetto: qui non ne viene imposta nessuna.
Se lo distribuisci, aggiungi il tuo file `LICENSE` accanto a questo.

## Cosa questo modulo NON è

- **Non è un modello di intelligenza artificiale.** Non genera testo. Recupera
  i tuoi documenti e prepara il contesto e il prompt; la chiamata a un modello,
  se la vuoi, la fa la tua applicazione con la tua chiave.
- **Non è una ricerca semantica con embedding.** È un motore lessicale con un
  grafo: sinonimi da dizionario, frequenza dei termini, sinapsi fra i nodi. È
  detto apertamente perché la differenza conta quando scegli lo strumento.
- **Non garantisce che le risposte siano giuste.** Garantisce che ogni risposta
  possa essere ricondotta a un documento tuo, che è una cosa diversa e più utile.

## Dati e responsabilità

Il modulo scrive solo nelle proprie tabelle (prefisso configurabile) e nella
propria cartella `data/`. Sul filesystem del progetto ospite **legge soltanto**.
Non manda niente a nessuno: nessuna telemetria, nessuna chiamata in uscita
(tranne l'ingestione da URL, che è **spenta** e vincolata a una lista di host).

Se ci metti dentro dati personali, il titolare del trattamento sei tu: il muro
di visibilità (`public` / `private` / `admin`) e la guardia sui file segreti
sono strumenti, non un'esenzione.
