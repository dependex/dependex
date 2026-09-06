"""
Test Suite Completa per Universal Email Revenue OS (Metodo Karpathy: Verifier).
Verifica contratti di interfaccia, conformità privacy, zero parole vietate ed esecuzione corretta dei componenti.
"""

import unittest
import os
import sys
import tempfile
import json
from pathlib import Path

# Aggiungi root al path
ROOT_DIR = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(ROOT_DIR))

from src.events.normalizer import EventNormalizer
from src.events.store import EventStore
from src.consent.ledger import ConsentLedger
from src.scoring.calculator import ScoringCalculator
from src.journeys.state_machine import LifecycleStateMachine
from src.compiler.flow_compiler import FlowCompiler
from src.renderer.mjml_compiler import TemplateRenderer
from src.compliance.guard import ComplianceGuard
from src.reporting.kpi import KPIReporter
from src.optimizer.pr_generator import OptimizationEngine

class TestUniversalEmailRevenueOS(unittest.TestCase):

    def setUp(self):
        self.temp_dir = tempfile.TemporaryDirectory()
        self.temp_db = Path(self.temp_dir.name) / "test_os.db"

    def tearDown(self):
        import gc
        gc.collect()
        try:
            self.temp_dir.cleanup()
        except Exception:
            pass

    def test_01_event_normalization(self):
        normalizer = EventNormalizer()
        raw_event = {
            "event_name": "lead_captured",
            "user_identifier": "TEST.USER@DEPENDEX.SOCIAL",
            "properties": {"plan": "starter"}
        }
        valid, event, err = normalizer.normalize(raw_event)
        self.assertTrue(valid, f"Normalizzazione fallita: {err}")
        self.assertEqual(event["user_identifier"], "test.user@dependex.social")
        self.assertIn("event_id", event)
        self.assertIn("timestamp", event)

    def test_02_event_store_and_query(self):
        store = EventStore(db_path=self.temp_db)
        event = {
            "event_id": "evt-123",
            "event_name": "page_view",
            "user_identifier": "membro@dependex.social",
            "timestamp": "2026-09-06T10:00:00Z",
            "schema_version": "1.0.0",
            "properties": {"page": "/club"}
        }
        res = store.append(event)
        self.assertTrue(res)
        
        events = store.get_user_events("membro@dependex.social")
        self.assertEqual(len(events), 1)
        self.assertEqual(events[0]["event_name"], "page_view")

    def test_03_consent_and_suppression(self):
        ledger = ConsentLedger(db_path=self.temp_db)
        email = "lead_optout@example.com"
        
        # Inizialmente non soppresso
        self.assertFalse(ledger.is_suppressed(email))
        
        # Registra opt-out
        ledger.record_opt_out(email, reason="USER_UNSUBSCRIBE")
        self.assertTrue(ledger.is_suppressed(email))

    def test_04_lifecycle_state_machine(self):
        # Transizioni ammesse
        ok, next_st = LifecycleStateMachine.transition("LEAD", "ENGAGED")
        self.assertTrue(ok)
        self.assertEqual(next_st, "ENGAGED")

        ok, next_st = LifecycleStateMachine.transition("ENGAGED", "BUYER")
        self.assertTrue(ok)

        # Transizioni non ammesse
        ok, err = LifecycleStateMachine.transition("CHURNED", "VIP")
        self.assertFalse(ok)

    def test_05_scoring_calculator(self):
        calculator = ScoringCalculator()
        events = [
            {"event_name": "page_view", "timestamp": "2026-09-06T10:00:00Z"},
            {"event_name": "email_opened", "timestamp": "2026-09-06T10:05:00Z"},
            {"event_name": "checkout_initiated", "timestamp": "2026-09-06T10:10:00Z"}
        ]
        scores = calculator.compute_scores(events, current_state="LEAD")
        self.assertGreater(scores["lead_score"], 20)
        self.assertEqual(scores["suggested_lifecycle_state"], "HIGH_INTENT")

    def test_06_flow_compiler(self):
        compiler = FlowCompiler(flows_directory=ROOT_DIR / "flows")
        self.assertIn("welcome_onboarding", compiler.loaded_flows)
        self.assertIn("high_intent_core", compiler.loaded_flows)
        self.assertIn("post_purchase", compiler.loaded_flows)
        self.assertIn("permission_refresh", compiler.loaded_flows)
        
        # Prossimo step quando nessuno è completato
        next_step = compiler.get_next_action("welcome_onboarding", completed_step_ids=[])
        self.assertIsNotNone(next_step)
        self.assertEqual(next_step["step_id"], "welcome_step_01")

        # Prossimo step quando il primo è completato
        next_step_2 = compiler.get_next_action("welcome_onboarding", completed_step_ids=["welcome_step_01"])
        self.assertEqual(next_step_2["step_id"], "delay_01")

    def test_07_template_renderer_and_branding(self):
        renderer = TemplateRenderer()
        html = renderer.render(
            body_template="<h1>Ciao {{ first_name }}</h1><p>Ecco il tuo accesso al Club.</p>",
            variables={
                "subject": "Benvenuto",
                "first_name": "Mario",
                "unsubscribe_url": "https://dependex.social/unsub",
                "preferences_url": "https://dependex.social/pref"
            }
        )
        self.assertIn("Mario", html)
        self.assertIn("DEPENDEX · CLUB", html)
        self.assertIn("https://dependex.social/unsub", html)

    def test_08_compliance_guard_and_forbidden_words(self):
        guard = ComplianceGuard()

        # Verifica blocco termini vietati
        forbidden_subjects = [
            "Scopri il metodo magico",
            "La formula del magic marketing",
            "Presentato da giorgian putanu",
            "Nuovo protocollo M.A.G.I.C.",
            "Aggiornamento piattaforma 81plus"
        ]
        for subj in forbidden_subjects:
            valid, reason = guard.check_forbidden_terms(subj)
            self.assertFalse(valid, f"Doveva essere bloccato: '{subj}'")

        # Verifica testo conforme
        valid, _ = guard.check_forbidden_terms("Il Metodo Dependex per l'autonomia digitale")
        self.assertTrue(valid)

    def test_09_kpi_and_circuit_breaker(self):
        reporter = KPIReporter()
        # Scenario normale
        normal_kpi = reporter.compute_daily_kpi(100, 99, 35, 10, 0, 1, 0, 150.0)
        self.assertFalse(normal_kpi["circuit_breaker"]["is_tripped"])
        self.assertGreater(normal_kpi["rates"]["open_rate_pct"], 30.0)

        # Scenario critico (Bounce > 3%)
        critical_kpi = reporter.compute_daily_kpi(100, 90, 10, 1, 0, 10, 0, 0.0)
        self.assertTrue(critical_kpi["circuit_breaker"]["is_tripped"])

    def test_10_zero_forbidden_words_in_codebase(self):
        """Audit automatico di bonifica terminologica su tutti i sorgenti e configurazioni."""
        forbidden_regexes = [
            r"\bmagico\b",
            r"\bmagic\b",
            r"\bm\.a\.g\.i\.c\.\b",
            r"giorgian\s+putanu",
            r"\b81plus\b"
        ]
        import re
        patterns = [re.compile(p, re.IGNORECASE) for p in forbidden_regexes]

        target_dirs = [
            ROOT_DIR / "src",
            ROOT_DIR / "flows",
            ROOT_DIR / "config",
            ROOT_DIR / "schemas",
            ROOT_DIR / "docs" / "email-os"
        ]

        violations = []
        for d in target_dirs:
            if not d.exists():
                continue
            for f in d.rglob("*"):
                if f.is_file() and f.suffix in [".py", ".yml", ".json", ".md"]:
                    try:
                        content = f.read_text(encoding="utf-8")
                        for p in patterns:
                            m = p.search(content)
                            if m:
                                violations.append(f"{f.name}: rilevata parola vietata '{m.group(0)}'")
                    except Exception:
                        pass

        self.assertEqual(len(violations), 0, f"Rilevate violazioni di termini vietati: {violations}")

if __name__ == "__main__":
    unittest.main()
