"""
Motore di Segmentazione Dinamica per Universal Email Revenue OS.
Classifica i contatti in cluster azionabili per campagne e percorsi mirati.
"""

from typing import Dict, Any, List

class SegmentationEngine:
    def evaluate_segments(self, contact: Dict[str, Any], scores: Dict[str, Any]) -> List[str]:
        """
        Determina i segmenti a cui appartiene un contatto.
        """
        segments = []
        state = contact.get("lifecycle_state", scores.get("suggested_lifecycle_state", "LEAD"))
        lead_score = scores.get("lead_score", 0)
        orders_count = scores.get("orders_count", 0)
        days_inactive = scores.get("days_inactive", 0)

        segments.append(f"state:{state.lower()}")

        if orders_count >= 1:
            segments.append("customers")
            if orders_count >= 2:
                segments.append("repeat_customers")
        else:
            segments.append("prospects")

        if lead_score >= 50:
            segments.append("hot_leads")
        elif lead_score >= 20:
            segments.append("warm_leads")
        else:
            segments.append("cold_leads")

        if days_inactive > 60:
            segments.append("inactivity_risk_high")
        elif days_inactive < 7:
            segments.append("active_recent")

        return segments
