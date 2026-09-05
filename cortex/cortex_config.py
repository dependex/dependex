import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
DATA_DIR = BASE_DIR.parent / "data"
SQLITE_PATH = DATA_DIR / "acat_community.sqlite"
MEMORY_DIR = BASE_DIR / "memory"
GRAPH_DIR = MEMORY_DIR / "graph"
CONTEXT_DIR = MEMORY_DIR / "context"
LEARNINGS_DIR = MEMORY_DIR / "learnings"

OLLAMA_HOST = os.getenv("OLLAMA_HOST", "http://localhost:11434")
CORTEX_LLM_MODEL = os.getenv("CORTEX_LLM_MODEL", "llama3.1:8b")
CORTEX_PORT = int(os.getenv("CORTEX_PORT", "8081"))
