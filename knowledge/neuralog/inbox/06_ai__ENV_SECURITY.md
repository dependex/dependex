# ENV & segreti AI

Le credenziali AI verranno fornite successivamente in `.env`.

## Divieto assoluto
`.env`, API key, password, bearer token, private key, seed e credenziali non devono mai essere ingeriti in Neuralog/Cortex.

## Uso
Il codice legge i segreti a runtime e passa soltanto il risultato necessario al provider adapter.

## Provider previsti
Groq, Gemini, OpenAI/compatibili e provider aggiuntivi.

## Separazione
Knowledge = Markdown/DB/documenti autorizzati.
Secrets = environment runtime, fuori dal knowledge graph.
