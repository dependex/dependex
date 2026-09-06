"""
Generatore di Ottimizzazioni e Proposte Pull Request (Self-Learning OS).
Analizza i KPI operativi e genera raccomandazioni o modifiche di configurazione automatizzate.
"""

from typing import Dict, Any, List
import json
import datetime
from pathlib import Path

class OptimizationEngine:
    def generate_recommendations(self, kpi_data: Dict[str, Any]) -> List[Dict[str, Any]]:
        """
        Analizza i KPI e produce proposte di ottimizzazione.
        """
        recommendations = []
        rates = kpi_data.get("rates", {})
        open_rate = rates.get("open_rate_pct", 0.0)
        click_rate = rates.get("click_rate_pct", 0.0)
        ctor = rates.get("click_to_open_rate_pct", 0.0)

        # Se open rate < 20%, raccomanda A/B test su subject line o variazione orario
        if open_rate < 20.0 and kpi_data.get("volume", {}).get("sent", 0) > 50:
            recommendations.append({
                "type": "SUBJECT_OPTIMIZATION",
                "severity": "HIGH",
                "finding": f"Open rate al {open_rate}% inferiore alla soglia raccomandata del 20%",
                "action": "Attivare A/B test dichiarativo con oggetto con domanda diretta e pre-header personalizzato"
            })

        # Se CTOR < 10%, raccomanda miglioramento CTA
        if ctor < 10.0 and open_rate >= 15.0:
            recommendations.append({
                "type": "CTA_OPTIMIZATION",
                "severity": "MEDIUM",
                "finding": f"Click-to-Open rate al {ctor}% basso rispetto alle aperture",
                "action": "Spostare il pulsante CTA principale above-the-fold e renderlo più esplicito sui benefici"
            })

        return recommendations

    def format_pull_request_proposal(self, recommendations: List[Dict[str, Any]]) -> str:
        """Formatta le raccomandazioni in un testo pronto per GitHub PR o Issue."""
        date_str = datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%d")
        lines = [
            f"# Proposta Automatica di Ottimizzazione Email OS ({date_str})",
            "",
            "Questo report è generato automaticamente dall'Optimization Engine di Universal Email Revenue OS.",
            "",
            "## Raccomandazioni Rilevate:",
            ""
        ]
        for idx, rec in enumerate(recommendations, 1):
            lines.append(f"### {idx}. {rec['type']} (Priorità: {rec['severity']})")
            lines.append(f"- **Diagnosi:** {rec['finding']}")
            lines.append(f"- **Azione proposta:** {rec['action']}")
            lines.append("")
        return "\n".join(lines)
