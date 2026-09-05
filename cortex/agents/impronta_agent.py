from typing import Dict, Any
from cortex.agents.base_agent import BaseAgent

class ImprontaAgent(BaseAgent):
    """
    Guida l'utente nell'intervista di contesto per generare i 5 file fondamentali dell'Impronta:
    1. identita.md
    2. offerta.md
    3. clienti.md
    4. tono-di-voce.md
    5. come-lavoriamo.md
    """
    def execute(self, task: str, input_data: Dict[str, Any]) -> Dict[str, Any]:
        context_files = list(self.context.cache.keys())
        summary = self.context.get_context_summary(300)
        
        response = (
            f"🧠 **Impronta Aziendale CORTEX**\n\n"
            f"Stato attuale dei file di contesto: {len(context_files)} file caricati ({', '.join(context_files) or 'nessuno'}).\n\n"
            f"L'Impronta fissa il DNA dell'azienda per rendere ogni agente autonomo e coerente.\n"
            f"Possiamo generare o perfezionare i 5 pilastri:\n"
            f"1. **Identità**: Chi siamo, missione e valori fondanti.\n"
            f"2. **Offerta**: Servizi, Club, Academy e percorsi.\n"
            f"3. **Clienti / Famiglie**: Persone a cui ci rivolgiamo e bisogni reali.\n"
            f"4. **Tono di voce**: Linguaggio accogliente, chiaro, rigoroso.\n"
            f"5. **Come lavoriamo**: Protocolli, ruoli (Servitore-Insegnante) e riunioni."
        )
        return {
            "message": response,
            "context_files": context_files,
            "ready_files": ["identita", "offerta", "clienti", "tono-di-voce", "come-lavoriamo"]
        }
