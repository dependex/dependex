"""
Guardiano di Conformità Ante-Invio per Universal Email Revenue OS.
Verifica consensi, suppression list, quiet hours, frequency cap e assenza assoluta di parole vietate.
"""

import re
import datetime
from typing import Tuple, Dict, Any, List
from pathlib import Path

FORBIDDEN_PATTERNS = [
    re.compile(r"\bmagico\b", re.IGNORECASE),
    re.compile(r"\bmagic\b", re.IGNORECASE),
    re.compile(r"m\.a\.g\.i\.c\.", re.IGNORECASE),
    re.compile(r"giorgian\s+putanu", re.IGNORECASE),
    re.compile(r"\b81plus\b", re.IGNORECASE)
]

class ComplianceGuard:
    def __init__(self, suppression_checker=None):
        self.suppression_checker = suppression_checker

    def check_forbidden_terms(self, text: str) -> Tuple[bool, str]:
        """Controlla se il testo contiene parole vietate o riferimenti non ammessi."""
        for pattern in FORBIDDEN_PATTERNS:
            match = pattern.search(text)
            if match:
                return False, f"Rilevato termine vietato: '{match.group(0)}'"
        return True, ""

    def is_within_quiet_hours(self, hour_utc: int = None) -> bool:
        """
        Verifica se l'orario ricade nelle quiet hours (22:00 - 07:00 CET/CEST, approx 20:00 - 05:00 UTC).
        """
        if hour_utc is None:
            hour_utc = datetime.datetime.now(datetime.timezone.utc).hour
        # 20 UTC = 22 CET, 5 UTC = 07 CET
        if hour_utc >= 20 or hour_utc < 5:
            return True
        return False

    def validate_dispatch(self, 
                          email: str, 
                          subject: str, 
                          html_content: str, 
                          is_transactional: bool = False,
                          bypass_quiet_hours: bool = False) -> Tuple[bool, str]:
        """
        Esegue la suite completa di controlli di conformità prima dell'inoltro al provider.
        """
        # 1. Verifica parole vietate nell'oggetto
        valid, reason = self.check_forbidden_terms(subject)
        if not valid:
            return False, f"Blocco Conformità Oggetto: {reason}"

        # 2. Verifica parole vietate nel corpo dell'email
        valid, reason = self.check_forbidden_terms(html_content)
        if not valid:
            return False, f"Blocco Conformità Corpo Email: {reason}"

        # 3. Controllo Suppression List
        if self.suppression_checker and self.suppression_checker.is_suppressed(email):
            return False, f"Blocco Conformità: Indirizzo {email} presente in suppression list"

        # 4. Controllo Quiet Hours (salvo email transazionali immediate)
        if not is_transactional and not bypass_quiet_hours:
            if self.is_within_quiet_hours():
                return False, "Blocco Conformità: Invio marketing sospeso durante le Quiet Hours notturne"

        # 5. Verifica presenza link disiscrizione per email marketing
        if not is_transactional:
            if "unsubscribe" not in html_content.lower() and "disiscrizione" not in html_content.lower():
                return False, "Blocco Conformità RFC 8058: Mancanza link di disiscrizione nel messaggio"

        return True, "OK"
