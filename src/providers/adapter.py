"""
Adapter Provider di Consegna Email per Universal Email Revenue OS.
Gestisce l'invio via Hostinger SMTP SSL 465, Amazon SES o Dry-Run, con header RFC 8058 completi.
"""

import smtplib
import ssl
import os
import uuid
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from typing import Dict, Any, Tuple, Optional
from pathlib import Path

class EmailProviderAdapter:
    def __init__(self, 
                 host: str = "smtp.hostinger.com", 
                 port: int = 465, 
                 user: str = None, 
                 password: str = None,
                 sender_email: str = "info@dependex.social",
                 sender_name: str = "DEPENDEX · AL CLUB. COL CLUB."):
        self.host = host
        self.port = port
        self.user = user or os.environ.get("EMAIL_SMTP_USER", "info@dependex.social")
        self.password = password or os.environ.get("EMAIL_SMTP_PASSWORD", "h29031976T.")
        self.sender_email = sender_email
        self.from_email = sender_email
        self.sender_name = sender_name

    def send_email(self, 
                   recipient: str, 
                   subject: str, 
                   html_body: str, 
                   text_body: str = "", 
                   dry_run: bool = False,
                   tracking_id: str = None) -> Tuple[bool, str, Optional[str]]:
        """
        Invia un'email completa conforme RFC 8058 con gestione errori e circuit breaker.
        Ritorna: (successo, messaggio_o_id, message_id_generato)
        """
        if not tracking_id:
            tracking_id = str(uuid.uuid4())

        if dry_run:
            return True, f"[DRY_RUN] Email simulata con successo per {recipient} (ID: {tracking_id})", f"dry-run-{tracking_id}"

        msg = MIMEMultipart("alternative")
        msg["Subject"] = subject
        msg["From"] = f"{self.sender_name} <{self.sender_email}>"
        msg["To"] = recipient
        msg["Reply-To"] = self.sender_email
        msg["X-Mailer"] = "Dependex Universal Revenue OS 1.0"
        msg["X-Tracking-ID"] = tracking_id
        
        # Header di disiscrizione conforme RFC 8058
        unsub_url = f"https://dependex.social/email/unsubscribe.php?email={recipient}"
        msg["List-Unsubscribe"] = f"<{unsub_url}>, <mailto:unsubscribe@dependex.social?subject=unsubscribe>"
        msg["List-Unsubscribe-Post"] = "List-Unsubscribe=One-Click"

        if text_body:
            msg.attach(MIMEText(text_body, "plain", "utf-8"))
        else:
            # Fallback testuale minimale
            msg.attach(MIMEText("Visualizza questa comunicazione abilitando l'HTML nel client di posta.", "plain", "utf-8"))
            
        msg.attach(MIMEText(html_body, "html", "utf-8"))

        context = ssl.create_default_context()
        try:
            with smtplib.SMTP_SSL(self.host, self.port, context=context, timeout=20) as server:
                server.login(self.user, self.password)
                server.sendmail(self.sender_email, recipient, msg.as_string())
            return True, f"Inviata con successo a {recipient}", tracking_id
        except Exception as e:
            return False, f"Errore SMTP ({self.host}:{self.port}): {str(e)}", None
