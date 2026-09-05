# AI Provider Architecture

## Obiettivo
Supportare più provider AI tramite adapter, configurati successivamente attraverso `.env`.

Provider previsti:
- Groq
- Gemini
- OpenAI o compatibili
- altri provider configurabili

## Regola segreti
API key e token NON devono mai entrare nei Markdown di conoscenza né nel RAG.
I file `.env` sono esclusi dall'ingest Neuralog.

## Routing
Il router AI deve scegliere provider/modello per:
- chat;
- classificazione;
- estrazione;
- sintesi;
- traduzione;
- vision;
- embeddings quando disponibili;
- generazione documenti;
- assistenza OSINT.

## Fallback
Provider primario → secondario → modalità RAG/knowledge-only senza generazione esterna.

## Grounding
Le risposte relative al progetto devono essere grounded sulla knowledge base Neuralog/Cortex e citare le fonti interne quando utile.
