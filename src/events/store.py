"""
Archiviazione eventi in SQLite locale (ACID, performante, zero container).
Consente l'ingestione idempotente e la query temporale degli eventi del funnel.
"""

import sqlite3
import json
from pathlib import Path
from typing import Dict, Any, List, Optional

DEFAULT_DB_PATH = Path(__file__).resolve().parent.parent.parent / "automation" / "emailflux" / "data" / "emailflux.db"

class EventStore:
    def __init__(self, db_path: Path = DEFAULT_DB_PATH):
        self.db_path = db_path
        self.db_path.parent.mkdir(parents=True, exist_ok=True)
        self._init_db()

    def _get_conn(self) -> sqlite3.Connection:
        conn = sqlite3.connect(str(self.db_path))
        conn.row_factory = sqlite3.Row
        return conn

    def _init_db(self):
        with self._get_conn() as conn:
            conn.execute("""
                CREATE TABLE IF NOT EXISTS event_log (
                    event_id TEXT PRIMARY KEY,
                    event_name TEXT NOT NULL,
                    user_identifier TEXT NOT NULL,
                    timestamp TEXT NOT NULL,
                    schema_version TEXT NOT NULL,
                    properties_json TEXT,
                    context_json TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            """)
            conn.execute("CREATE INDEX IF NOT EXISTS idx_events_user ON event_log (user_identifier);")
            conn.execute("CREATE INDEX IF NOT EXISTS idx_events_name ON event_log (event_name);")
            conn.execute("CREATE INDEX IF NOT EXISTS idx_events_timestamp ON event_log (timestamp);")
            conn.commit()

    def append(self, event: Dict[str, Any]) -> bool:
        """Inserisce un evento normalizzato in modo idempotente."""
        try:
            with self._get_conn() as conn:
                conn.execute("""
                    INSERT OR IGNORE INTO event_log 
                    (event_id, event_name, user_identifier, timestamp, schema_version, properties_json, context_json)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                """, (
                    event["event_id"],
                    event["event_name"],
                    event["user_identifier"],
                    event["timestamp"],
                    event.get("schema_version", "1.0.0"),
                    json.dumps(event.get("properties", {})),
                    json.dumps(event.get("context", {}))
                ))
                conn.commit()
                return True
        except Exception:
            return False

    def get_user_events(self, user_identifier: str, limit: int = 100) -> List[Dict[str, Any]]:
        """Recupera la sequenza cronologica degli eventi per un utente."""
        with self._get_conn() as conn:
            cursor = conn.execute("""
                SELECT * FROM event_log
                WHERE user_identifier = ?
                ORDER BY timestamp DESC
                LIMIT ?
            """, (user_identifier.strip().lower(), limit))
            rows = cursor.fetchall()
            return [
                {
                    "event_id": row["event_id"],
                    "event_name": row["event_name"],
                    "user_identifier": row["user_identifier"],
                    "timestamp": row["timestamp"],
                    "properties": json.loads(row["properties_json"] or "{}"),
                    "context": json.loads(row["context_json"] or "{}")
                }
                for row in rows
            ]
