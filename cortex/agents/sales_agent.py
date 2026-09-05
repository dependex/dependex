from typing import Dict, Any
from cortex.agents.base_agent import BaseAgent

class SalesAgent(BaseAgent):
    """Gestisce proposte, quote associative, donazioni sostenitori e servizi convenzionati."""
    def execute(self, task: str, input_data: Dict[str, Any]) -> Dict[str, Any]:
        return {
            "message": (
                "💼 **Gestione Proposte & Sostegno Ecosistema**\n\n"
                "• Percorsi Academy e corsi di formazione per Servitori-Insegnanti.\n"
                "• Abbonamento e quote di adesione Club / Federazioni.\n"
                "• Erogazioni liberali e supporto progetti d'impatto sociale."
            ),
            "type": "sales_proposal"
        }
