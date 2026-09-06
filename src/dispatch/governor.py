"""
Governatore di Dispacciamento (Dispatch Governor) per Universal Email Revenue OS.
Regola la velocità di invio (throttling), applica warming, batching e controlli di sicurezza.
"""

import time
import datetime
from typing import List, Dict, Any, Tuple
from pathlib import Path

class DispatchGovernor:
    def __init__(self, 
                 provider_adapter, 
                 compliance_guard, 
                 max_hourly_rate: int = 150, 
                 delay_between_sends: float = 1.0):
        self.provider = provider_adapter
        self.guard = compliance_guard
        self.max_hourly_rate = max_hourly_rate
        self.delay_between_sends = delay_between_sends
        self.sent_count_this_session = 0

    def dispatch_batch(self, 
                       messages: List[Dict[str, Any]], 
                       dry_run: bool = False) -> Dict[str, Any]:
        """
        Processa un lotto di messaggi applicando i controlli di conformità e throttling.
        Ogni messaggio in ingresso: { recipient, subject, html_content, variables, is_transactional }
        """
        results = {
            "total": len(messages),
            "sent": 0,
            "blocked": 0,
            "failed": 0,
            "details": []
        }

        for item in messages:
            recipient = item.get("recipient")
            subject = item.get("subject", "")
            html_content = item.get("html_content", "")
            is_transactional = item.get("is_transactional", False)
            
            # 1. Verifica di conformità pre-invio
            valid, reason = self.guard.validate_dispatch(
                email=recipient, 
                subject=subject, 
                html_content=html_content, 
                is_transactional=is_transactional
            )
            
            if not valid:
                results["blocked"] += 1
                results["details"].append({
                    "recipient": recipient,
                    "status": "BLOCKED",
                    "reason": reason
                })
                continue

            # 2. Invio tramite provider
            success, msg, msg_id = self.provider.send_email(
                recipient=recipient,
                subject=subject,
                html_body=html_content,
                dry_run=dry_run
            )

            if success:
                results["sent"] += 1
                self.sent_count_this_session += 1
                results["details"].append({
                    "recipient": recipient,
                    "status": "SENT",
                    "message_id": msg_id
                })
            else:
                results["failed"] += 1
                results["details"].append({
                    "recipient": recipient,
                    "status": "FAILED",
                    "error": msg
                })

            # Applicazione throttling
            if not dry_run and self.delay_between_sends > 0:
                time.sleep(self.delay_between_sends)

        return results
