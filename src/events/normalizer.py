"""
Normalizzatore e Validatore degli eventi in ingresso per Universal Email Revenue OS.
Garantisce che ogni evento da sito, carrello, PayPal o webhook rispetti lo schema versionato.
"""

import json
import uuid
import datetime
from pathlib import Path
from typing import Dict, Any, Tuple

SCHEMA_PATH = Path(__file__).resolve().parent.parent.parent / "schemas" / "event_schema_v1.json"

class EventNormalizer:
    def __init__(self, schema_file: Path = SCHEMA_PATH):
        self.schema = {}
        if schema_file.exists():
            with open(schema_file, "r", encoding="utf-8") as f:
                self.schema = json.load(f)

    def normalize(self, raw_data: Dict[str, Any]) -> Tuple[bool, Dict[str, Any], str]:
        """
        Normalizza e valida un payload evento.
        Ritorna: (is_valid, normalized_event, error_message)
        """
        required_fields = ["event_name", "user_identifier", "event_id", "timestamp"]
        
        event = dict(raw_data)
        if not event.get("event_id"):
            event["event_id"] = str(uuid.uuid4())
        
        if not event.get("timestamp"):
            event["timestamp"] = datetime.datetime.now(datetime.timezone.utc).isoformat()
            
        if not event.get("schema_version"):
            event["schema_version"] = "1.0.0"
            
        if not event.get("properties"):
            event["properties"] = {}
            
        if not event.get("context"):
            event["context"] = {"source": "dependex_platform"}

        # Validazione essenziale
        for field in required_fields:
            if not event.get(field):
                return False, {}, f"Campo obbligatorio mancante o vuoto: {field}"

        email = event.get("user_identifier", "").strip().lower()
        if "@" not in email or "." not in email:
            return False, {}, f"Identificatore utente non valido (richiesta email valida): {email}"

        event["user_identifier"] = email
        return True, event, ""
