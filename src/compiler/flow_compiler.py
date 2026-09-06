"""
Compilatore e interprete di Flussi Dichiarativi YAML per Universal Email Revenue OS.
Convalida la sintassi e calcola lo stato di avanzamento per ciascun contatto iscritto.
"""

from pathlib import Path
from typing import Dict, Any, List, Optional
import yaml

FLOWS_DIR = Path(__file__).resolve().parent.parent.parent / "flows"

class FlowCompiler:
    def __init__(self, flows_directory: Path = FLOWS_DIR):
        self.flows_dir = flows_directory
        self.loaded_flows: Dict[str, Dict[str, Any]] = {}
        self.reload_all()

    def reload_all(self):
        self.loaded_flows.clear()
        if not self.flows_dir.exists():
            return
        for file in self.flows_dir.glob("*.yml"):
            try:
                with open(file, "r", encoding="utf-8") as f:
                    flow_def = yaml.safe_load(f)
                    if flow_def and "flow_id" in flow_def:
                        self.loaded_flows[flow_def["flow_id"]] = flow_def
            except Exception as e:
                print(f"Errore caricamento flusso {file.name}: {e}")

    def get_flow(self, flow_id: str) -> Optional[Dict[str, Any]]:
        return self.loaded_flows.get(flow_id)

    def get_next_action(self, flow_id: str, completed_step_ids: List[str]) -> Optional[Dict[str, Any]]:
        """
        Determina il prossimo step eseguibile per un contatto all'interno di un flusso.
        """
        flow = self.get_flow(flow_id)
        if not flow:
            return None
            
        steps = flow.get("steps", [])
        for step in steps:
            sid = step.get("step_id")
            if sid not in completed_step_ids:
                return step
        return None  # Flusso completato
