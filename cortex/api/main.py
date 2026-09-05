import os
import sys
from pathlib import Path
from typing import Optional, Dict, Any

# Ensure cortex root is in sys.path
CORTEX_ROOT = Path(__file__).resolve().parent.parent
if str(CORTEX_ROOT) not in sys.path:
    sys.path.insert(0, str(CORTEX_ROOT))

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

from core.memory_graph import MemoryGraph
from core.context_engine import ContextEngine
from services.llm_service import LLMService
from core.autonomous_learning import AutonomousLearning
from core.agent_orchestrator import AgentOrchestrator
from cortex_config import SQLITE_PATH, GRAPH_DIR, CONTEXT_DIR

app = FastAPI(
    title="CORTEX — Company Brain API",
    version="1.0.0",
    description="Digital Cognitive Brain for Dependex and Oltre"
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize singletons
graph = MemoryGraph(str(GRAPH_DIR))
context_engine = ContextEngine(str(CONTEXT_DIR))
llm_service = LLMService()
learning_engine = AutonomousLearning(graph, context_engine, llm_service, str(SQLITE_PATH))
orchestrator = AgentOrchestrator(graph, context_engine, llm_service, str(SQLITE_PATH))


class ChatRequest(BaseModel):
    message: str
    user_id: Optional[int] = None
    session_id: Optional[str] = None
    task_type: Optional[str] = None
    meta: Optional[Dict[str, Any]] = None


@app.get("/health")
def health():
    return {
        "status": "healthy",
        "service": "CORTEX Company Brain",
        "graph_nodes": len(graph.graph.nodes),
        "graph_edges": len(graph.graph.edges),
        "context_pillars": len(context_engine.pillars)
    }


@app.post("/api/chat")
def chat_endpoint(payload: ChatRequest):
    if not payload.message.strip():
        raise HTTPException(status_code=400, detail="Empty message")

    input_data = {
        "user_id": payload.user_id,
        "session_id": payload.session_id,
        "task_type": payload.task_type,
        **(payload.meta or {})
    }

    res = orchestrator.orchestrate(payload.message, input_data)
    if not res.get("success"):
        return {
            "success": False,
            "message": res.get("error", "Errore durante l'elaborazione di Cortex"),
            "agent": res.get("agent", "support"),
            "task_type": res.get("task_type", "support")
        }

    agent_result = res.get("result", {})
    return {
        "success": True,
        "message": agent_result.get("response", ""),
        "agent": res.get("agent"),
        "task_type": res.get("task_type"),
        "actions": agent_result.get("actions", []),
        "action": agent_result.get("action")
    }


@app.post("/api/learn")
def learn_endpoint():
    try:
        learning_engine.run_learning_loop()
        return {
            "success": True,
            "message": "Ciclo di autoapprendimento completato con successo",
            "timestamp": learning_engine.last_learning.isoformat()
        }
    except Exception as e:
        return {
            "success": False,
            "error": str(e)
        }


@app.get("/api/knowledge")
def knowledge_endpoint():
    return {
        "success": True,
        "graph_nodes": len(graph.graph.nodes),
        "graph_edges": len(graph.graph.edges),
        "pillars": list(context_engine.pillars.keys()),
        "last_learning": learning_engine.last_learning.isoformat()
    }


if __name__ == "__main__":
    import uvicorn
    from cortex_config import CORTEX_PORT
    uvicorn.run("api.main:app", host="0.0.0.0", port=CORTEX_PORT, reload=False)
