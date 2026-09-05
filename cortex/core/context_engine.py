from pathlib import Path
from typing import Dict, Any

class ContextEngine:
    """
    Gestisce il contesto aziendale leggendo i file di contesto di primo livello (Impronta).
    """
    def __init__(self, context_path: Path = None):
        if context_path is None:
            self.context_path = Path(__file__).resolve().parent.parent / "memory" / "context"
        else:
            self.context_path = Path(context_path)
        self.context_path.mkdir(parents=True, exist_ok=True)
        self.cache: Dict[str, str] = {}
        self.refresh()

    def refresh(self):
        """Ricarica tutti i file markdown dal contesto"""
        self.cache = {}
        if not self.context_path.exists():
            return
        for md in self.context_path.glob("*.md"):
            try:
                with open(md, "r", encoding="utf-8") as f:
                    self.cache[md.stem] = f.read()
            except Exception:
                pass

    def get_context_summary(self, max_chars_per_section: int = 500) -> str:
        """Restituisce un riassunto strutturato del contesto aziendale"""
        parts = []
        for section, content in self.cache.items():
            snippet = content.strip()[:max_chars_per_section]
            parts.append(f"### {section.upper()}\n{snippet}...")
        return "\n\n".join(parts) if parts else "Nessun file di contesto ancora presente."

    def get_section(self, section: str) -> str:
        return self.cache.get(section, "")
