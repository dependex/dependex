import json
from pathlib import Path
from datetime import datetime
from typing import Dict, List, Any, Optional

class MemoryGraph:
    """
    Il grafo della conoscenza di Cortex.
    Ogni nodo e un'entita (cliente, prodotto, procedura, concetto, persona).
    Ogni arco e una relazione.
    """
    
    def __init__(self, memory_path: Optional[str] = None):
        if memory_path is None:
            self.memory_path = Path(__file__).resolve().parent.parent / "memory" / "graph"
        else:
            self.memory_path = Path(memory_path)
        self.memory_path.mkdir(parents=True, exist_ok=True)
        self.nodes_file = self.memory_path / "nodes.json"
        self.edges_file = self.memory_path / "edges.json"
        self.nodes: Dict[str, Dict[str, Any]] = {}
        self.edges: List[Dict[str, Any]] = []
        self.load()
    
    def load(self):
        """Carica il grafo da file JSON"""
        try:
            if self.nodes_file.exists() and self.edges_file.exists():
                with open(self.nodes_file, 'r', encoding='utf-8') as f:
                    node_list = json.load(f)
                with open(self.edges_file, 'r', encoding='utf-8') as f:
                    self.edges = json.load(f)
                
                self.nodes = {n['id']: {k: v for k, v in n.items() if k != 'id'} for n in node_list}
            else:
                self._initialize_empty_graph()
        except Exception:
            self._initialize_empty_graph()
    
    def save(self):
        """Salva il grafo su file JSON"""
        node_list = [{'id': k, **v} for k, v in self.nodes.items()]
        with open(self.nodes_file, 'w', encoding='utf-8') as f:
            json.dump(node_list, f, indent=2, ensure_ascii=False, default=str)
        with open(self.edges_file, 'w', encoding='utf-8') as f:
            json.dump(self.edges, f, indent=2, ensure_ascii=False, default=str)
    
    def _initialize_empty_graph(self):
        """Inizializza un grafo vuoto con nodi di base"""
        self.nodes = {
            "azienda": {"type": "entity", "label": "Azienda / Ecosistema DEPENDEX & OLTRE"},
            "know-how": {"type": "concept", "label": "Know-how Aziendale e Metodologia Hudolin"}
        }
        self.edges = [
            {"source": "azienda", "target": "know-how", "type": "possiede"}
        ]
        
        for category in ["club", "community", "academy", "lifestyle", "processi", "documenti"]:
            self.nodes[category] = {"type": "category", "label": category}
            self.edges.append({"source": "know-how", "target": category, "type": "contiene"})
        
        self.save()
    
    def add_entity(self, entity_id: str, entity_type: str, **attributes):
        """Aggiunge un'entita al grafo"""
        self.nodes[entity_id] = {"type": entity_type, **attributes}
        self.save()
    
    def add_relationship(self, source: str, target: str, rel_type: str, **attributes):
        """Aggiunge una relazione tra due entita"""
        self.edges.append({"source": source, "target": target, "type": rel_type, **attributes})
        self.save()
    
    def query_entity(self, entity_id: str) -> Optional[Dict]:
        """Recupera un'entita"""
        if entity_id in self.nodes:
            return {'id': entity_id, **self.nodes[entity_id]}
        return None
    
    def query_related(self, entity_id: str, rel_type: Optional[str] = None) -> List[Dict]:
        """Trova le entita collegate a entity_id"""
        results = []
        for e in self.edges:
            if e['source'] == entity_id and (rel_type is None or e.get('type') == rel_type):
                target_id = e['target']
                results.append({
                    'id': target_id,
                    'entity': self.nodes.get(target_id, {}),
                    'relationship': e
                })
        return results
    
    def add_learning(self, concept: str, source: str, content: str):
        """Aggiunge un apprendimento al grafo"""
        learning_id = f"learning_{datetime.now().strftime('%Y%m%d%H%M%S')}"
        self.nodes[learning_id] = {
            "type": "learning",
            "concept": concept,
            "source": source,
            "content": content,
            "timestamp": datetime.now().isoformat()
        }
        self.edges.append({"source": source, "target": learning_id, "type": "ha_prodotto"})
        self.edges.append({"source": learning_id, "target": "know-how", "type": "arricchisce"})
        self.save()
