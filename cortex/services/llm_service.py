import os
from typing import Dict, Any, Optional

class LLMService:
    """
    Wrapper unificato per modelli locali (Ollama) e provider compatibili.
    Include graceful degradation quando il server LLM non e in esecuzione.
    """
    def __init__(self, host: Optional[str] = None, model: Optional[str] = None):
        self.host = host or os.getenv("OLLAMA_HOST", "http://localhost:11434")
        self.model = model or os.getenv("CORTEX_LLM_MODEL", "llama3.1:8b")

    def generate(self, prompt: str, system: Optional[str] = None) -> str:
        """Esegue generazione tramite Ollama o fallback euristico locale"""
        try:
            import urllib.request
            import json

            payload = {
                "model": self.model,
                "prompt": prompt,
                "stream": False
            }
            if system:
                payload["system"] = system

            data = json.dumps(payload).encode("utf-8")
            req = urllib.request.Request(
                f"{self.host}/api/generate",
                data=data,
                headers={"Content-Type": "application/json"},
                method="POST"
            )
            with urllib.request.urlopen(req, timeout=10) as response:
                res = json.loads(response.read().decode("utf-8"))
                return res.get("response", "").strip()
        except Exception:
            # Fallback euristico di base
            first_line = prompt.strip().split("\n")[0]
            return f"CORTEX local response: elaborato contesto per '{first_line[:60]}'."
