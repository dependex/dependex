"""
Calcolatore di Lead Scoring, Engagement Scoring e Churn Risk per Universal Email Revenue OS.
Calcola dinamicamente i punteggi basandosi sugli eventi storici memorizzati.
"""

from typing import List, Dict, Any, Tuple
import datetime

class ScoringCalculator:
    WEIGHTS = {
        "page_view": 1,
        "email_opened": 2,
        "email_clicked": 5,
        "resource_downloaded": 10,
        "form_submitted": 15,
        "cart_created": 15,
        "checkout_initiated": 25,
        "order_completed": 50,
        "unsubscribe_requested": -50,
        "email_bounced": -100
    }

    def compute_scores(self, events: List[Dict[str, Any]], current_state: str = "LEAD") -> Dict[str, Any]:
        """
        Calcola i punteggi in base agli eventi recenti.
        Ritorna: lead_score, engagement_score, churn_risk_score, suggested_state.
        """
        lead_score = 0
        engagement_score = 0
        orders_count = 0
        total_revenue = 0.0
        has_checkout = False
        
        now = datetime.datetime.now(datetime.timezone.utc)
        last_activity_date = None

        for ev in events:
            name = ev.get("event_name", "")
            pts = self.WEIGHTS.get(name, 0)
            lead_score += pts
            
            # Engagement basato su aperture/click/visite
            if name in ["email_opened", "email_clicked", "page_view", "resource_downloaded"]:
                engagement_score += pts
                
            if name == "checkout_initiated":
                has_checkout = True
                
            if name == "order_completed":
                orders_count += 1
                props = ev.get("properties", {})
                total_revenue += float(props.get("value", 0.0) or props.get("total", 0.0) or 0.0)

            # Rilevamento data ultimo evento
            ts_str = ev.get("timestamp")
            if ts_str:
                try:
                    dt = datetime.datetime.fromisoformat(ts_str.replace("Z", "+00:00"))
                    if last_activity_date is None or dt > last_activity_date:
                        last_activity_date = dt
                except Exception:
                    pass

        # Calcolo churn risk basato sui giorni di inattività
        days_inactive = 0
        if last_activity_date:
            days_inactive = (now - last_activity_date).days
            
        churn_risk_score = min(100, max(0, days_inactive * 2))

        # Determina stato suggerito
        suggested_state = current_state
        if orders_count >= 3 or total_revenue >= 500.0:
            suggested_state = "VIP"
        elif orders_count >= 2:
            suggested_state = "REPEAT_BUYER"
        elif orders_count == 1:
            suggested_state = "BUYER"
        elif has_checkout:
            suggested_state = "HIGH_INTENT"
        elif engagement_score >= 20:
            suggested_state = "INTERESTED"
        elif engagement_score >= 5:
            suggested_state = "ENGAGED"
        elif days_inactive > 60:
            suggested_state = "DORMANT"

        return {
            "lead_score": max(0, lead_score),
            "engagement_score": max(0, engagement_score),
            "churn_risk_score": churn_risk_score,
            "orders_count": orders_count,
            "total_revenue": total_revenue,
            "days_inactive": days_inactive,
            "suggested_lifecycle_state": suggested_state
        }
