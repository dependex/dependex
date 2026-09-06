"""
test_smtp_delivery.py — Script di verifica e invio email di prova.
Invia una email di test formattata in Luxury Dark & Gold al destinatario di test:
labomobile.lm@gmail.com
"""
import sys
from config import SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, TEST_RECIPIENT
from transport import send_smtp
from templates_catalog import TEMPLATES

def run_test(recipient=None):
    to_email = recipient or TEST_RECIPIENT
    print(f"[*] Avvio test di invio SMTP a: {to_email}")
    print(f"[*] Server: {SMTP_HOST}:{SMTP_PORT} | Mittente: {SMTP_USER}")

    tpl = TEMPLATES.get("FLOW_TEST/01_COLLAUDO")
    if not tpl:
        print("[ERRORE] Template di collaudo non trovato.")
        return False

    # Rendering testuale
    nome = "Mirco"
    subject = tpl["oggetto"].replace("{nome}", nome)
    html = tpl["corpo"].replace("{nome}", nome)

    result = send_smtp(to_email, subject, html)
    if result["status"] == "SUCCESS":
        print(f"[OK] Email inviata con successo a {to_email}!")
        print(f"     Provider: {result['provider']}")
        return True
    else:
        print(f"[FALLITO] Errore invio SMTP a {to_email}: {result['error']}")
        return False

if __name__ == "__main__":
    target = sys.argv[1] if len(sys.argv) > 1 else None
    success = run_test(target)
    sys.exit(0 if success else 1)
