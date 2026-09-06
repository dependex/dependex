"""
Registro di Consenso e Suppression List conforme GDPR e RFC 8058.
Memorizza lo storico immutabile dei consensi, revoche e richieste di disiscrizione.
"""

import sqlite3
import datetime
from pathlib import Path
from typing import Dict, Any, Optional

DEFAULT_DB_PATH = Path(__file__).resolve().parent.parent.parent / "automation" / "emailflux" / "data" / "emailflux.db"

class ConsentLedger:
    def __init__(self, db_path: Path = DEFAULT_DB_PATH):
        self.db_path = db_path
        self._init_db()

    def _get_conn(self) -> sqlite3.Connection:
        conn = sqlite3.connect(str(self.db_path))
        conn.row_factory = sqlite3.Row
        return conn

    def _init_db(self):
        with self._get_conn() as conn:
            conn.execute("""
                CREATE TABLE IF NOT EXISTS consent_ledger (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email TEXT NOT NULL,
                    consent_status TEXT NOT NULL,
                    consent_source TEXT,
                    ip_address TEXT,
                    user_agent TEXT,
                    timestamp TEXT NOT NULL,
                    notes TEXT
                );
            """)
            conn.execute("""
                CREATE TABLE IF NOT EXISTS suppression_list (
                    email TEXT PRIMARY KEY,
                    reason TEXT NOT NULL,
                    suppressed_at TEXT NOT NULL,
                    metadata_json TEXT
                );
            """)
            conn.execute("CREATE INDEX IF NOT EXISTS idx_consent_email ON consent_ledger (email);")
            conn.commit()

    def record_consent(self, email: str, status: str, source: str = "web_form", ip: str = None, ua: str = None) -> bool:
        """Registra un evento di opt-in o aggiornamento consenso."""
        email = email.strip().lower()
        now = datetime.datetime.now(datetime.timezone.utc).isoformat()
        with self._get_conn() as conn:
            conn.execute("""
                INSERT INTO consent_ledger (email, consent_status, consent_source, ip_address, user_agent, timestamp)
                VALUES (?, ?, ?, ?, ?, ?)
            """, (email, status, source, ip, ua, now))
            
            # Se opt-in verificato, rimuovi eventuale soppressione non rigida
            if status in ["EXPLICIT_OPT_IN", "DOUBLE_OPT_IN"]:
                conn.execute("DELETE FROM suppression_list WHERE email = ? AND reason != 'HARD_BOUNCE'", (email,))
            conn.commit()
            return True

    def record_opt_out(self, email: str, reason: str = "USER_UNSUBSCRIBE") -> bool:
        """Registra opt-out immediato e inserisce in suppression list."""
        email = email.strip().lower()
        now = datetime.datetime.now(datetime.timezone.utc).isoformat()
        with self._get_conn() as conn:
            conn.execute("""
                INSERT INTO consent_ledger (email, consent_status, consent_source, timestamp, notes)
                VALUES (?, 'OPTED_OUT', 'unsubscribe_link', ?, ?)
            """, (email, now, reason))
            conn.execute("""
                INSERT OR REPLACE INTO suppression_list (email, reason, suppressed_at)
                VALUES (?, ?, ?)
            """, (email, reason, now))
            # Aggiorna anche la tabella contatti principale se presente
            try:
                conn.execute("UPDATE contacts SET status = 'unsubscribed' WHERE email = ?", (email,))
            except sqlite3.OperationalError:
                pass
            conn.commit()
            return True

    def is_suppressed(self, email: str) -> bool:
        """Verifica se l'indirizzo email è soppresso da qualsiasi invio."""
        email = email.strip().lower()
        with self._get_conn() as conn:
            cursor = conn.execute("SELECT 1 FROM suppression_list WHERE email = ?", (email,))
            if cursor.fetchone():
                return True
            try:
                cursor_c = conn.execute("SELECT status FROM contacts WHERE email = ?", (email,))
                row = cursor_c.fetchone()
                if row and row["status"] in ["unsubscribed", "bounced", "complained", "invalid"]:
                    return True
            except sqlite3.OperationalError:
                pass
        return False
