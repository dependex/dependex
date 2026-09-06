"""
db.py — Gestore Database SQLite per Emailflux Dependex.
Crea e mantiene lo schema relazionale per contatti, flussi, template e log degli invii.
"""
import sqlite3
from config import DB_PATH

def get_connection():
    conn = sqlite3.connect(DB_PATH, timeout=20.0)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA busy_timeout = 20000")
    conn.execute("PRAGMA foreign_keys = ON")
    init_schema(conn)
    return conn

def init_schema(conn):
    conn.executescript("""
    CREATE TABLE IF NOT EXISTS ghl_agency (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        dominio TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS ghl_sub_account (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        agency_id INTEGER,
        nome TEXT NOT NULL,
        hub_code TEXT DEFAULT 'DEPENDEX-01',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(agency_id) REFERENCES ghl_agency(id)
    );

    CREATE TABLE IF NOT EXISTS ghl_contact (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sub_account_id INTEGER DEFAULT 1,
        sic_id TEXT UNIQUE,
        email TEXT NOT NULL UNIQUE COLLATE NOCASE,
        nome TEXT,
        cognome TEXT,
        azienda TEXT,
        telefono TEXT,
        provincia TEXT,
        tag TEXT,
        flusso TEXT,
        consenso INTEGER DEFAULT 1,
        unsub INTEGER DEFAULT 0,
        status TEXT DEFAULT 'ATTIVO',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS ghl_template (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sub_account_id INTEGER DEFAULT 1,
        nome TEXT NOT NULL,
        canale TEXT DEFAULT 'email',
        oggetto TEXT NOT NULL,
        corpo TEXT NOT NULL,
        flow_key TEXT UNIQUE NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS ghl_workflow (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sub_account_id INTEGER DEFAULT 1,
        nome TEXT UNIQUE NOT NULL,
        trigger_json TEXT,
        condizioni_json TEXT,
        azioni_json TEXT NOT NULL,
        attivo INTEGER DEFAULT 1,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS ghl_workflow_enrollment (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        workflow_id INTEGER NOT NULL,
        contact_id INTEGER NOT NULL,
        step_corrente INTEGER DEFAULT 0,
        next_at TEXT NOT NULL,
        stato TEXT DEFAULT 'active',
        enrolled_at TEXT DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(workflow_id, contact_id),
        FOREIGN KEY(workflow_id) REFERENCES ghl_workflow(id),
        FOREIGN KEY(contact_id) REFERENCES ghl_contact(id)
    );

    CREATE TABLE IF NOT EXISTS ghl_send_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sub_account_id INTEGER DEFAULT 1,
        contact_id INTEGER,
        email TEXT NOT NULL,
        canale TEXT DEFAULT 'email',
        template_id INTEGER,
        workflow_id INTEGER,
        stato TEXT NOT NULL,
        provider_id TEXT,
        errore TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );

    CREATE INDEX IF NOT EXISTS idx_contact_email ON ghl_contact(email);
    CREATE INDEX IF NOT EXISTS idx_contact_sic ON ghl_contact(sic_id);
    CREATE INDEX IF NOT EXISTS idx_enr_due ON ghl_workflow_enrollment(next_at, stato);
    CREATE INDEX IF NOT EXISTS idx_log_created ON ghl_send_log(created_at);
    """)
    conn.commit()

def get_or_create_sub_account(conn):
    cur = conn.cursor()
    row = cur.execute("SELECT id FROM ghl_sub_account LIMIT 1").fetchone()
    if row:
        return row['id']
    
    cur.execute("INSERT OR IGNORE INTO ghl_agency(nome, dominio) VALUES('DEPENDEX SOCIAL NETWORK', 'dependex.social')")
    agency_id = cur.lastrowid or 1
    cur.execute("INSERT INTO ghl_sub_account(agency_id, nome, hub_code) VALUES(?, 'DEPENDEX COMMERCE & COMMUNITY', 'DPX-01')", (agency_id,))
    conn.commit()
    return cur.lastrowid
