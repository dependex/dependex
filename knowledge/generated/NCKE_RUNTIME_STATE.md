# NCKE Runtime State

- Status: **READY_RETRIEVAL**
- Mode: **ADAPTIVE**
- Last bootstrap: 2026-08-18 16:28:40
- Knowledge documents indexed: **91**
- Full-text chunks: **791**

## Runtime architecture
SQLite FTS5 + Neuralog Graph + Metadata + Markdown PageIndex are the immediate core.
External LLM/vector/realtime infrastructure is activated through adapters.

## Secret policy
Provider secrets are discovered from one or more `.env*` files at runtime and never written into this knowledge archive.
