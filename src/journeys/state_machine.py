"""
Macchina a Stati Finiti per il Ciclo di Vita Utente (Universal Email Revenue OS).
Governa le transizioni ammissibili tra gli stati del customer journey.
"""

from typing import Optional, Tuple

class LifecycleStateMachine:
    STATES = [
        "VISITOR",
        "LEAD",
        "ENGAGED",
        "INTERESTED",
        "HIGH_INTENT",
        "BUYER",
        "REPEAT_BUYER",
        "VIP",
        "DORMANT",
        "REACTIVATED",
        "CHURNED"
    ]

    ALLOWED_TRANSITIONS = {
        "VISITOR": ["LEAD", "DORMANT"],
        "LEAD": ["ENGAGED", "INTERESTED", "HIGH_INTENT", "BUYER", "DORMANT"],
        "ENGAGED": ["INTERESTED", "HIGH_INTENT", "BUYER", "DORMANT"],
        "INTERESTED": ["HIGH_INTENT", "BUYER", "ENGAGED", "DORMANT"],
        "HIGH_INTENT": ["BUYER", "INTERESTED", "DORMANT"],
        "BUYER": ["REPEAT_BUYER", "VIP", "ENGAGED", "DORMANT"],
        "REPEAT_BUYER": ["VIP", "DORMANT", "BUYER"],
        "VIP": ["DORMANT", "REPEAT_BUYER"],
        "DORMANT": ["REACTIVATED", "CHURNED"],
        "REACTIVATED": ["ENGAGED", "INTERESTED", "HIGH_INTENT", "BUYER", "DORMANT"],
        "CHURNED": ["LEAD"] # Possibile re-opt-in consensuale esplicito
    }

    @classmethod
    def can_transition(cls, current_state: str, next_state: str) -> bool:
        if current_state == next_state:
            return True
        allowed = cls.ALLOWED_TRANSITIONS.get(current_state, [])
        return next_state in allowed

    @classmethod
    def transition(cls, current_state: str, next_state: str) -> Tuple[bool, str]:
        """Esegue la transizione di stato se ammessa dalla matrice."""
        if current_state not in cls.STATES or next_state not in cls.STATES:
            return False, f"Stato non riconosciuto: {current_state} -> {next_state}"
        if cls.can_transition(current_state, next_state):
            return True, next_state
        return False, f"Transizione non consentita da {current_state} a {next_state}"
