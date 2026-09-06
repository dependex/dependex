"""
Generatore di KPI e Report di Deliverability per Universal Email Revenue OS.
Calcola metriche aggregate per il monitoraggio continuo e il circuit breaker.
"""

from typing import Dict, Any
import datetime
import json
from pathlib import Path

class KPIReporter:
    def __init__(self, db_path: Path = None):
        self.db_path = db_path

    def compute_daily_kpi(self, 
                          sent_count: int, 
                          delivered_count: int, 
                          opened_count: int, 
                          clicked_count: int, 
                          unsubscribed_count: int, 
                          bounced_count: int, 
                          complaint_count: int,
                          revenue_generated: float = 0.0) -> Dict[str, Any]:
        """
        Calcola i KPI standard e valuta lo stato dei circuit breaker.
        """
        def pct(part, total):
            return round((part / total) * 100, 2) if total > 0 else 0.0

        delivery_rate = pct(delivered_count, sent_count)
        open_rate = pct(opened_count, delivered_count)
        click_rate = pct(clicked_count, delivered_count)
        ctor = pct(clicked_count, opened_count)
        unsub_rate = pct(unsubscribed_count, delivered_count)
        bounce_rate = pct(bounced_count, sent_count)
        complaint_rate = pct(complaint_count, delivered_count)

        # Valutazione Circuit Breaker (soglie dure da MASTER_EMAIL_OS_PROMPT.md)
        circuit_breaker_tripped = False
        alerts = []

        if bounce_rate > 3.0:
            circuit_breaker_tripped = True
            alerts.append(f"CIRCUIT BREAKER: Tasso di rimbalzo critico ({bounce_rate}% > 3.0%)")

        if complaint_rate > 0.1:
            circuit_breaker_tripped = True
            alerts.append(f"CIRCUIT BREAKER: Tasso di spam complaint critico ({complaint_rate}% > 0.1%)")

        if unsub_rate > 1.5:
            alerts.append(f"ATTENZIONE: Tasso di disiscrizione elevato ({unsub_rate}%)")

        report = {
            "date": datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%d"),
            "timestamp": datetime.datetime.now(datetime.timezone.utc).isoformat(),
            "volume": {
                "sent": sent_count,
                "delivered": delivered_count,
                "opened": opened_count,
                "clicked": clicked_count,
                "unsubscribed": unsubscribed_count,
                "bounced": bounced_count,
                "complaints": complaint_count
            },
            "rates": {
                "delivery_rate_pct": delivery_rate,
                "open_rate_pct": open_rate,
                "click_rate_pct": click_rate,
                "click_to_open_rate_pct": ctor,
                "unsubscribe_rate_pct": unsub_rate,
                "bounce_rate_pct": bounce_rate,
                "complaint_rate_pct": complaint_rate
            },
            "revenue": {
                "total_eur": revenue_generated,
                "revenue_per_recipient": round(revenue_generated / sent_count, 3) if sent_count > 0 else 0.0
            },
            "circuit_breaker": {
                "is_tripped": circuit_breaker_tripped,
                "alerts": alerts
            }
        }
        return report
