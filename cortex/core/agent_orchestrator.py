import json
from pathlib import Path
from typing import Dict, Any, Optional
import sqlite3

class AgentOrchestrator:
    """
    Orchestratore degli agenti Cortex.
    Decide quale agente usare in base al task e coordina l'esecuzione.
    """
    
    def __init__(self, graph, context, llm_service, db_path: Optional[str] = None):
        self.graph = graph
        self.context = context
        self.llm = llm_service
        if db_path is None:
            self.db_path = Path(__file__).resolve().parent.parent.parent / "data" / "acat_community.sqlite"
        else:
            self.db_path = Path(db_path)
        self.agents = {}
        self._load_agents()
    
    def _load_agents(self):
        """Carica tutti gli agenti disponibili"""
        from cortex.agents.impronta_agent import ImprontaAgent
        from cortex.agents.mappa_tesoro_agent import MappaTesoroAgent
        from cortex.agents.primo_ingranaggio_agent import PrimoIngranaggioAgent
        from cortex.agents.content_agent import ContentAgent
        from cortex.agents.sales_agent import SalesAgent
        from cortex.agents.support_agent import SupportAgent
        from cortex.agents.analytics_agent import AnalyticsAgent
        from cortex.agents.web3_agent import Web3Agent
        
        self.agents = {
            'impronta': ImprontaAgent(self.graph, self.context, self.llm),
            'mappa_tesoro': MappaTesoroAgent(self.graph, self.context, self.llm),
            'primo_ingranaggio': PrimoIngranaggioAgent(self.graph, self.context, self.llm),
            'content': ContentAgent(self.graph, self.context, self.llm),
            'sales': SalesAgent(self.graph, self.context, self.llm),
            'support': SupportAgent(self.graph, self.context, self.llm),
            'analytics': AnalyticsAgent(self.graph, self.context, self.llm),
            'web3': Web3Agent(self.graph, self.context, self.llm),
        }
    
    def orchestrate(self, task: str, input_data: Optional[Dict[str, Any]] = None) -> Dict[str, Any]:
        """
        Orchestra l'esecuzione di un task.
        Decide quale agente usare e gestisce il flusso.
        """
        input_data = input_data or {}
        task_type = self._classify_task(task, input_data)
        agent_name = self._select_agent(task_type)
        
        if not agent_name or agent_name not in self.agents:
            return {
                'success': False,
                'error': f'Nessun agente trovato per il task: {task_type}',
                'task_type': task_type
            }
        
        agent = self.agents[agent_name]
        try:
            result = agent.execute(task, input_data)
            self._log_interaction(task, task_type, agent_name, input_data, result)
            
            return {
                'success': True,
                'agent': agent_name,
                'task_type': task_type,
                'result': result
            }
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'agent': agent_name,
                'task_type': task_type
            }
    
    def _classify_task(self, task: str, input_data: Dict) -> str:
        """Classifica il task per scegliere l'agente giusto"""
        task_lower = task.lower()
        
        if any(w in task_lower for w in ['impronta', 'contesto', 'identita', 'identità', 'valori']):
            return 'impronta'
        if any(w in task_lower for w in ['mappa', 'automazione', 'tesoro', 'processi', 'sprechi']):
            return 'mappa_tesoro'
        if any(w in task_lower for w in ['ingranaggio', 'skill', 'prima skill', 'flusso']):
            return 'primo_ingranaggio'
        if any(w in task_lower for w in ['articolo', 'blog', 'contenuto', 'scrivere', 'post', 'newsletter']):
            return 'content'
        if any(w in task_lower for w in ['preventivo', 'offerta', 'vendita', 'quota', 'donazione', 'sostegno']):
            return 'sales'
        if any(w in task_lower for w in ['analisi', 'report', 'dati', 'statistiche', 'kpi', 'metrica']):
            return 'analytics'
        if any(w in task_lower for w in ['web3', 'blockchain', 'wallet', 'dao', 'attestato', 'crypto']):
            return 'web3'
        
        return 'support'
    
    def _select_agent(self, task_type: str) -> Optional[str]:
        mapping = {
            'impronta': 'impronta',
            'mappa_tesoro': 'mappa_tesoro',
            'primo_ingranaggio': 'primo_ingranaggio',
            'content': 'content',
            'sales': 'sales',
            'support': 'support',
            'analytics': 'analytics',
            'web3': 'web3',
        }
        return mapping.get(task_type, 'support')
    
    def _log_interaction(self, task: str, task_type: str, agent: str, input_data: Dict, result: Dict):
        """Registra l'interazione in SQLite per l'apprendimento"""
        if not self.db_path.exists():
            return
        
        try:
            db = sqlite3.connect(str(self.db_path))
            cursor = db.cursor()
            user_id = input_data.get('user_id')
            user_sic_id = input_data.get('user_sic_id')
            session_id = input_data.get('session_id')
            
            cursor.execute("""
                INSERT INTO cortex_interactions (user_id, user_sic_id, session_id, task, task_type, agent, input_data, result)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            """, (user_id, user_sic_id, session_id, task, task_type, agent, json.dumps(input_data, ensure_ascii=False), json.dumps(result, ensure_ascii=False)))
            
            db.commit()
            db.close()
        except Exception:
            pass
