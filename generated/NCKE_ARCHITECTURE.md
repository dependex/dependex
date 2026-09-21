# Neuralog-Cortex Knowledge Engine (NCKE)

NCKE è implementato in modalità **adaptive** per funzionare subito su hosting PHP/SQLite.

## Core immediato
- SQLite FTS5 full-text
- Neuralog graph
- metadata search
- hybrid fusion
- Markdown PageIndex
- temporal/statistical analysis

## Adapter progressivi
- Vector DB
- Neo4j
- external reranker
- WebSocket
- MCP
- observability stack
- Kubernetes/cloud-native deployment

Il sistema degrada elegantemente: senza vector DB usa FTS5 + grafo; senza LLM restituisce retrieval tracciabile.
