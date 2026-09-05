from typing import Dict, Any

class BaseAgent:
    def __init__(self, graph, context, llm):
        self.graph = graph
        self.context = context
        self.llm = llm

    def execute(self, task: str, input_data: Dict[str, Any]) -> Dict[str, Any]:
        raise NotImplementedError
