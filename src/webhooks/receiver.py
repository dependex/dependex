"""
Ricevitore e Normalizzatore di Webhook per Universal Email Revenue OS.
Supporta eventi carrello, ordini completati, PayPal Webhook e notifiche provider di bounce/complaint.
"""

from typing import Dict, Any, Tuple
import datetime
import uuid

class WebhookReceiver:
    def __init__(self, event_normalizer, event_store, consent_ledger):
        self.normalizer = event_normalizer
        self.store = event_store
        self.consent = consent_ledger

    def handle_paypal_webhook(self, payload: Dict[str, Any]) -> Tuple[bool, str]:
        """Elabora eventi provenienti dall'API PayPal."""
        event_type = payload.get("event_type", "")
        resource = payload.get("resource", {})

        # Estrazione email acquirente da payload PayPal
        payer = resource.get("payer", {})
        email = payer.get("email_address") or resource.get("custom_id")
        
        if not email and "subscriber" in resource:
            email = resource.get("subscriber", {}).get("email_address")

        if not email:
            return False, "Impossibile trovare email pagatore nel payload PayPal"

        # Importo transazione
        amount = 0.0
        amount_obj = resource.get("amount", {})
        if "total" in amount_obj:
            amount = float(amount_obj.get("total", 0.0))
        elif "value" in amount_obj:
            amount = float(amount_obj.get("value", 0.0))

        if event_type in ["PAYMENT.SALE.COMPLETED", "CHECKOUT.ORDER.APPROVED"]:
            event_data = {
                "event_id": str(uuid.uuid4()),
                "event_name": "order_completed",
                "user_identifier": email,
                "timestamp": datetime.datetime.now(datetime.timezone.utc).isoformat(),
                "properties": {
                    "provider": "paypal",
                    "value": amount,
                    "currency": amount_obj.get("currency", "EUR"),
                    "transaction_id": resource.get("id")
                }
            }
            valid, norm_event, err = self.normalizer.normalize(event_data)
            if valid:
                self.store.append(norm_event)
                return True, "Evento ordine PayPal registrato con successo"
            return False, err

        return True, f"Evento PayPal {event_type} ricevuto e ignorato"

    def handle_provider_bounce(self, email: str, bounce_type: str = "hard") -> bool:
        """Tratta un rimbalzo registrandolo immediatamente nella suppression list."""
        self.consent.record_opt_out(email, reason=f"BOUNCE_{bounce_type.upper()}")
        event_data = {
            "event_id": str(uuid.uuid4()),
            "event_name": "email_bounced",
            "user_identifier": email,
            "timestamp": datetime.datetime.now(datetime.timezone.utc).isoformat(),
            "properties": {"bounce_type": bounce_type}
        }
        valid, norm_event, _ = self.normalizer.normalize(event_data)
        if valid:
            self.store.append(norm_event)
        return True
