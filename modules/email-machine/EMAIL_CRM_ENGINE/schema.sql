-- ============================================================
-- DESTINO RANDAGIO — EMAIL/CRM ENGINE · SCHEMA (riferimento SQL)
-- Versione dichiarativa dello schema creato da schema.php.
-- Tutto IF NOT EXISTS => sicuro da rieseguire, non distruttivo.
-- Il motore usa comunque schema.php (idempotente) a runtime; questo
-- file serve come documentazione / ispezione manuale del DB SQLite.
-- ============================================================

-- LEAD: anagrafica commerciale unificata (stato ricalcolato dal motore)
CREATE TABLE IF NOT EXISTS leads(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT UNIQUE,
  nome TEXT,
  stato_lead TEXT DEFAULT 'freddo',          -- freddo | tiepido | caldo
  score INTEGER DEFAULT 0,                    -- punteggio 0..120
  tags TEXT DEFAULT '[]',                     -- array JSON
  fonte TEXT,                                 -- signup, utente, evento, lista:...
  ultimo_comportamento TEXT,                  -- ultimo tipo evento
  ultima_interazione TEXT,                    -- datetime
  compleanno TEXT,                            -- YYYY-MM-DD
  consenso INTEGER DEFAULT 1,                 -- 1 = ok invii, 0 = revocato
  uid INTEGER DEFAULT 0,                      -- link a users.id (0 = non registrato)
  ltv_eur REAL DEFAULT 0,                     -- somma ordini pagati
  ordini INTEGER DEFAULT 0,                   -- n. ordini pagati
  created TEXT DEFAULT (datetime('now')),
  updated TEXT DEFAULT (datetime('now'))
);

-- LEAD_EVENTS: ogni comportamento (apertura, click, acquisto, visita, ...)
CREATE TABLE IF NOT EXISTS lead_events(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  lead_id INTEGER,
  email TEXT,
  tipo TEXT,                                  -- apertura|click|acquisto|visita|iscrizione|carrello|login|invio|ricorrenza|...
  ref TEXT,
  valore REAL DEFAULT 0,
  meta TEXT,                                  -- JSON opzionale
  created TEXT DEFAULT (datetime('now'))
);

-- AUTOMATIONS: definizione dei flussi (steps come array JSON)
CREATE TABLE IF NOT EXISTS automations(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nome TEXT,
  trig TEXT,                                  -- signup|purchase|abandoned|winback|birthday|manual
  steps TEXT DEFAULT '[]',                    -- [{wait_h,tpl,subject,cond}, ...]
  active INTEGER DEFAULT 1,
  created TEXT DEFAULT (datetime('now'))
);

-- AUTOMATION_RUNS: avanzamento di ogni lead dentro un flusso
CREATE TABLE IF NOT EXISTS automation_runs(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  automation_id INTEGER,
  lead_id INTEGER,
  email TEXT,
  step INTEGER DEFAULT 0,
  next_at TEXT,                               -- quando eseguire lo step corrente
  stato TEXT DEFAULT 'attivo',               -- attivo|completato|annullato|chiuso
  ctx TEXT,                                   -- JSON contesto (name, product, ...)
  created TEXT DEFAULT (datetime('now')),
  updated TEXT DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_leads_stato  ON leads(stato_lead);
CREATE INDEX IF NOT EXISTS idx_events_email ON lead_events(email);
CREATE INDEX IF NOT EXISTS idx_events_tipo  ON lead_events(tipo);
CREATE INDEX IF NOT EXISTS idx_runs_next    ON automation_runs(next_at);
