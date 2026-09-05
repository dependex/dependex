# Protocollo del bus

- I log sono JSONL in sola aggiunta: non si modifica una riga scritta.
- Per cambiare lo stato di un TASK si scrive un messaggio nuovo con `ref` = id del task.
- Tipi: TASK, DONE, QUESTION, ANSWER, ALERT, DECISION, LOCK, FORWARD
- Stati: OPEN, IN_PROGRESS, DONE, BLOCKED, CANCELLED
- Prima di toccare un file condiviso si prende un LOCK; i lock scadono da soli.
- DASHBOARD.md e i brief sono generati: non si scrivono a mano.
