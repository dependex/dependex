# NCKE — Unified 20-Layer Specification

Neuralog-Cortex Knowledge Engine è il motore universale di accesso alla conoscenza per OLTRE/DEPENDEX.

## 20 layer

1. Data ingestion
2. Pre-processing e normalizzazione
3. Indexing full-text/vector/graph/temporal/metadata
4. Query analysis: intent, entity, complexity, language
5. Parallel search
6. Fusion & reranking
7. Response generation
8. Governance/Cortex traceability
9. Learning & optimization
10. Security & permissions
11. Multimodal
12. Realtime/event-driven
13. Human-in-the-loop
14. Export/reporting
15. Versioning/history
16. Personalization/context memory
17. Semantic federation
18. Explainability/XAI
19. Anomaly detection & predictive analytics
20. NL2SQL, code understanding, DevOps/observability

## Strategia adaptive

L'hosting PHP parte con SQLite FTS5 + Neuralog Graph + metadata + Markdown PageIndex.
Vector DB, Neo4j, external reranking, WebSocket, MCP, Prometheus e Kubernetes sono adapter progressivi.

## Regola di degradazione

- LLM non disponibile → retrieval-only con fonti.
- Vector DB non disponibile → FTS5 + Graph.
- Provider rate-limited → provider successivo.
- Confidenza < 70 → Human Review Queue.
- Dati mancanti → risposta esplicita di insufficienza, senza invenzione.

## Sicurezza

Le chiavi dei file `.env` vengono lette solo runtime e non entrano mai nella knowledge base.
