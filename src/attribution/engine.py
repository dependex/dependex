"""
Motore di Attribuzione Entrate per Universal Email Revenue OS.
Calcola l'impatto economico delle comunicazioni (First-touch, Last-touch, Linear).
"""

from typing import List, Dict, Any

class AttributionEngine:
    def attribute_revenue(self, order_event: Dict[str, Any], recent_touchpoints: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Attribuisce il valore di un ordine ai touchpoint email precedenti.
        """
        revenue = float(order_event.get("properties", {}).get("value", 0.0))
        if not recent_touchpoints or revenue <= 0:
            return {
                "order_id": order_event.get("event_id"),
                "total_revenue": revenue,
                "attributed_campaigns": {}
            }

        # Last touch
        last_touch = recent_touchpoints[0]
        last_campaign = last_touch.get("properties", {}).get("campaign_id", "direct_unknown")

        # Linear distribution
        num_touchpoints = len(recent_touchpoints)
        split_val = round(revenue / num_touchpoints, 2)
        linear_attributions = {}
        for tp in recent_touchpoints:
            cid = tp.get("properties", {}).get("campaign_id", "default_flow")
            linear_attributions[cid] = linear_attributions.get(cid, 0.0) + split_val

        return {
            "order_id": order_event.get("event_id"),
            "total_revenue": revenue,
            "last_touch_campaign": last_campaign,
            "linear_attributions": linear_attributions
        }
