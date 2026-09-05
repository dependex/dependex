# DATABASE

Database iniziale: `data/acat_community.sqlite`.

## Principi
- SIC-ID come identificatore pubblico/universale
- chiavi tecniche interne integer consentite solo internamente
- audit separato
- dati sensibili separati da dati pubblici
- ledger DRX append-oriented e idempotente
- network mondiale normalizzato in `dependex_world_registry`
- fonti OSINT/versioning/status history separati dal record corrente

## Tabelle chiave
`users`, `families`, `club_memberships`, `network_entities`, `dependex_world_registry`, `dependex_world_edges`, `drx_ledger`, `ranks`, `academy_*`, `assessments`, `assessment_sessions`, `events`, `dao_topics`, `dao_votes`, `generated_documents`, `form_templates`, `form_submissions`, `treasury_*`, `projects`, `volunteer_actions`, `osint_sources`, `entity_source_links`, `entity_status_history`, `field_confidence`, `country_research_status`, `research_tasks`, `geocode_queue`.
