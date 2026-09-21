# AI / Secrets Security Policy

1. I valori dei file `.env` NON vengono mai ingeriti da Neuralog/Cortex.
2. Le API key non vengono salvate in Markdown, DB, log, HTML o JavaScript.
3. Il Provider Resolver espone a Cortex solo provider READY/NOT READY e fingerprint non reversibili.
4. Prompt injection non può autorizzare lettura dei secret.
5. I dati sensibili personali restano esclusi dalla knowledge condivisa; gli insight community devono essere aggregati/de-identificati.
