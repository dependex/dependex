<?php
/* ============================================================
   DESTINO RANDAGIO — EMAIL/CRM ENGINE · SCHEMA (idempotente)
   Estende il DB SQLite esistente (data/dr.sqlite) SENZA rompere nulla.
   - CREATE TABLE IF NOT EXISTS  -> non distruttivo
   - ALTER TABLE in try/catch    -> colonne aggiunte una volta sola
   Tabelle nuove (prefisso neutro, non collidono con mkt_*):
     leads · lead_events · automations · automation_runs
   Uso: require_once 'schema.php';  dr_crm_schema($pdo);
   NB: si integra con users / subscribers / orders / carts (db.php).
   ============================================================ */

if(!function_exists('dr_crm_schema')){
function dr_crm_schema($pdo){

  /* ---- LEADS: anagrafica commerciale unificata ---------------------------
     stato_lead: freddo | tiepido | caldo   (ricalcolato da dr_lead_score)
     tags:  array JSON di etichette (["vip","genesis",...])
     score: punteggio numerico 0..100+ (base del calcolo stato)            */
  $pdo->exec("CREATE TABLE IF NOT EXISTS leads(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE,
    nome TEXT,
    stato_lead TEXT DEFAULT 'freddo',
    score INTEGER DEFAULT 0,
    tags TEXT DEFAULT '[]',
    fonte TEXT,
    ultimo_comportamento TEXT,
    ultima_interazione TEXT,
    compleanno TEXT,
    consenso INTEGER DEFAULT 1,
    uid INTEGER DEFAULT 0,
    ltv_eur REAL DEFAULT 0,
    ordini INTEGER DEFAULT 0,
    created TEXT DEFAULT (datetime('now')),
    updated TEXT DEFAULT (datetime('now'))
  )");

  /* ---- LEAD_EVENTS: ogni comportamento tracciato -------------------------
     tipo:  apertura | click | acquisto | visita | iscrizione | carrello |
            invio | disiscrizione | login | referral | custom ...          */
  $pdo->exec("CREATE TABLE IF NOT EXISTS lead_events(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lead_id INTEGER,
    email TEXT,
    tipo TEXT,
    ref TEXT,
    valore REAL DEFAULT 0,
    meta TEXT,
    created TEXT DEFAULT (datetime('now'))
  )");

  /* ---- AUTOMATIONS: definizione dei flussi -------------------------------
     trigger:  signup | purchase | abandoned | winback | birthday | manual
     steps:    array JSON [{wait_h, tpl, subject, cond}, ...]              */
  $pdo->exec("CREATE TABLE IF NOT EXISTS automations(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT,
    trig TEXT,
    steps TEXT DEFAULT '[]',
    active INTEGER DEFAULT 1,
    created TEXT DEFAULT (datetime('now'))
  )");

  /* ---- AUTOMATION_RUNS: avanzamento di ogni lead in un flusso ----------- */
  $pdo->exec("CREATE TABLE IF NOT EXISTS automation_runs(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    automation_id INTEGER,
    lead_id INTEGER,
    email TEXT,
    step INTEGER DEFAULT 0,
    next_at TEXT,
    stato TEXT DEFAULT 'attivo',
    ctx TEXT,
    created TEXT DEFAULT (datetime('now')),
    updated TEXT DEFAULT (datetime('now'))
  )");

  /* ---- indici utili (idempotenti) --------------------------------------- */
  foreach([
    "CREATE INDEX IF NOT EXISTS idx_leads_stato ON leads(stato_lead)",
    "CREATE INDEX IF NOT EXISTS idx_events_email ON lead_events(email)",
    "CREATE INDEX IF NOT EXISTS idx_events_tipo ON lead_events(tipo)",
    "CREATE INDEX IF NOT EXISTS idx_runs_next ON automation_runs(next_at)",
  ] as $q){ try{ $pdo->exec($q); }catch(Exception $e){} }

  /* ---- ALTER non distruttivi su tabelle esistenti -----------------------
     (se in futuro il core cambia, restano innocui grazie al try/catch)     */
  foreach(['crm_synced INTEGER DEFAULT 0'] as $c){
    try{ $pdo->exec("ALTER TABLE subscribers ADD COLUMN ".$c); }catch(Exception $e){}
  }

  return true;
}
}

/* auto-esecuzione se caricato con $pdo già disponibile */
if(isset($pdo) && $pdo instanceof PDO){ dr_crm_schema($pdo); }
