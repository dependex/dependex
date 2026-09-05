import json
from pathlib import Path
from datetime import datetime, timedelta
from typing import Dict, List, Any, Optional
import sqlite3

class AutonomousLearning:
    """
    Cortex impara autonomamente da:
    1. Interazioni con gli utenti salvate in SQLite
    2. Nuovi documenti caricati nel contesto
    3. Risultati delle automazioni
    4. Feedback implicito ed esplicito
    """
    
    def __init__(self, graph, context_engine, llm_service, db_path: Optional[str] = None):
        self.graph = graph
        self.context = context_engine
        self.llm = llm_service
        if db_path is None:
            self.db_path = Path(__file__).resolve().parent.parent.parent / "data" / "acat_community.sqlite"
        else:
            self.db_path = Path(db_path)
        self.learning_interval = 3600  # 1 ora
        self.last_learning = datetime.now() - timedelta(hours=1)
    
    def run_learning_loop(self):
        """Loop principale di apprendimento"""
        if datetime.now() - self.last_learning < timedelta(seconds=self.learning_interval):
            return {"status": "skipped", "reason": "rate_limit", "next_in_seconds": self.learning_interval}
        
        self.learn_from_interactions()
        self.context.refresh()
        self.generate_insights()
        
        self.last_learning = datetime.now()
        return {"status": "completed", "timestamp": self.last_learning.isoformat()}
    
    def learn_from_interactions(self):
        """Impara dalle interazioni con gli utenti salvate in SQLite"""
        if not self.db_path.exists():
            return
        
        db = sqlite3.connect(str(self.db_path))
        cursor = db.cursor()
        
        try:
            cursor.execute("""
                SELECT id, user_id, task, result, feedback, created_at
                FROM cortex_interactions
                WHERE processed = 0
                ORDER BY created_at DESC
                LIMIT 100
            """)
            interactions = cursor.fetchall()
            
            for interaction in interactions:
                inter_id, user_id, task, result, feedback, created_at = interaction
                
                # Se il feedback e positivo, rafforza la conoscenza
                if feedback and int(feedback) > 0:
                    self.graph.add_learning(
                        concept=self._extract_concept(task or ""),
                        source=f"interaction_{inter_id}",
                        content=f"Task: {task}\nResult: {result}\nFeedback: {feedback}"
                    )
                # Se il feedback e negativo, impara cosa evitare
                elif feedback and int(feedback) < 0:
                    self.graph.add_learning(
                        concept="da_evitare",
                        source=f"interaction_{inter_id}",
                        content=f"Evita risposte come: {(result or '')[:100]}..."
                    )
                
                cursor.execute("UPDATE cortex_interactions SET processed = 1 WHERE id = ?", (inter_id,))
            
            db.commit()
        except Exception:
            pass
        finally:
            db.close()
    
    def _extract_concept(self, text: str) -> str:
        """Estrae il concetto principale da un testo"""
        clean = text.strip()
        words = [w for w in clean.split() if len(w) > 3]
        return " ".join(words[:3]) if words else "conoscenza_generale"
    
    def generate_insights(self):
        """Genera insight basati sui dati appresi"""
        if not self.db_path.exists():
            return
        
        db = sqlite3.connect(str(self.db_path))
        cursor = db.cursor()
        
        try:
            cursor.execute("""
                SELECT task, COUNT(*) as count
                FROM cortex_interactions
                GROUP BY task
                ORDER BY count DESC
                LIMIT 5
            """)
            top_queries = cursor.fetchall()
            
            if top_queries:
                summary = ", ".join([q[0][:30] for q in top_queries if q[0]])
                if summary:
                    self._save_insight(f"📊 Richieste frequenti nella rete: {summary}")
        except Exception:
            pass
        finally:
            db.close()
    
    def _save_insight(self, insight: str):
        """Salva un insight nel grafo"""
        insight_id = f"insight_{datetime.now().strftime('%Y%m%d%H%M%S')}"
        self.graph.nodes[insight_id] = {
            "type": "insight",
            "content": insight,
            "timestamp": datetime.now().isoformat()
        }
        self.graph.edges.append({"source": insight_id, "target": "know-how", "type": "arricchisce"})
        self.graph.save()


if __name__ == "__main__":
    import sys
    cortex_root = Path(__file__).resolve().parent.parent
    if str(cortex_root) not in sys.path:
        sys.path.insert(0, str(cortex_root))

    from core.memory_graph import MemoryGraph
    from core.context_engine import ContextEngine
    from services.llm_service import LLMService

    graph = MemoryGraph(str(cortex_root / "memory" / "graph"))
    context = ContextEngine(str(cortex_root / "memory" / "context"))
    llm = LLMService()
    learner = AutonomousLearning(graph, context, llm)
    res = learner.run_learning_loop()
    print(json.dumps({"success": True, "result": res}))
