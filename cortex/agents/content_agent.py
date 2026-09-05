from typing import Dict, Any
from cortex.agents.base_agent import BaseAgent

class ContentAgent(BaseAgent):
    """Genera contenuti editoriali, articoli, newsletter e post allineati al tono aziendale."""
    def execute(self, task: str, input_data: Dict[str, Any]) -> Dict[str, Any]:
        tone = self.context.get_section("tono-di-voce") or "accogliente, autorevole, empatico e orientato al cambiamento"
        prompt = f"Genera un articolo o testo per il seguente obiettivo: {task}. Tono di voce: {tone}"
        content = self.llm.generate(prompt)
        return {
            "message": f"📝 **Bozza di Contenuto Generata**:\n\n{content}",
            "type": "editorial_draft"
        }
