"""
Valutatore di Esperimenti e A/B Testing per Universal Email Revenue OS.
Calcola tassi di conversione delle varianti e significatività statistica orientativa.
"""

from typing import Dict, Any, List

class ExperimentEvaluator:
    def evaluate_variant_performance(self, variant_a_metrics: Dict[str, int], variant_b_metrics: Dict[str, int]) -> Dict[str, Any]:
        """
        Confronta due varianti (es. Oggetto A vs Oggetto B).
        metrics: { sent, opens, clicks, conversions }
        """
        def get_rate(num: int, denom: int) -> float:
            return round((num / denom) * 100, 2) if denom > 0 else 0.0

        sent_a = variant_a_metrics.get("sent", 0)
        sent_b = variant_b_metrics.get("sent", 0)

        open_rate_a = get_rate(variant_a_metrics.get("opens", 0), sent_a)
        open_rate_b = get_rate(variant_b_metrics.get("opens", 0), sent_b)

        click_rate_a = get_rate(variant_a_metrics.get("clicks", 0), sent_a)
        click_rate_b = get_rate(variant_b_metrics.get("clicks", 0), sent_b)

        winner = "TIE"
        if click_rate_b > click_rate_a:
            winner = "VARIANT_B"
        elif click_rate_a > click_rate_b:
            winner = "VARIANT_A"
        elif open_rate_b > open_rate_a:
            winner = "VARIANT_B"
        elif open_rate_a > open_rate_b:
            winner = "VARIANT_A"

        return {
            "variant_a": {
                "open_rate": open_rate_a,
                "click_rate": click_rate_a
            },
            "variant_b": {
                "open_rate": open_rate_b,
                "click_rate": click_rate_b
            },
            "recommended_winner": winner,
            "sample_size_adequate": (sent_a >= 100 and sent_b >= 100)
        }
