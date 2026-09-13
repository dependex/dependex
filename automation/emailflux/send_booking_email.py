"""
send_booking_email.py — Invio notifica di avvenuta iscrizione/prenotazione evento via SMTP SSL
Mittente ufficiale: info@dependex.support (Hostinger SMTP SSL 465)
"""
import sys
import os

# Aggiunge il path per i moduli emailflux
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from transport import send_smtp

def main():
    if len(sys.argv) < 4:
        try:
            print("[ERRORE] Argomenti insufficienti: <to_email> <subject> <body>")
        except Exception:
            pass
        sys.exit(1)

    to_email = sys.argv[1].strip()
    subject = sys.argv[2].strip()
    body_text = sys.argv[3].strip()

    # Formattazione HTML Luxury Dark & Gold
    html_content = f"""
    <!DOCTYPE html>
    <html lang="it">
    <head>
      <meta charset="UTF-8">
      <title>{subject}</title>
      <style>
        body {{ font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #0b0d14; color: #f1f5f9; margin: 0; padding: 20px; }}
        .card {{ max-width: 600px; margin: 0 auto; background: #121624; border: 1px solid rgba(212,175,55,0.35); border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }}
        .badge {{ display: inline-block; background: rgba(212,175,55,0.15); color: #D4AF37; font-size: 11px; font-weight: bold; letter-spacing: 1px; padding: 4px 10px; border-radius: 999px; text-transform: uppercase; margin-bottom: 12px; }}
        h1 {{ color: #ffffff; font-size: 22px; margin-top: 0; line-height: 1.3; font-weight: 800; }}
        p {{ color: #cbd5e1; font-size: 15px; line-height: 1.6; }}
        .highlight-box {{ background: rgba(255,255,255,0.04); border-left: 4px solid #D4AF37; padding: 16px; border-radius: 8px; margin: 20px 0; }}
        .footer {{ font-size: 12px; color: #64748b; margin-top: 25px; text-align: center; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 15px; }}
        .btn {{ display: inline-block; background: #D4AF37; color: #0b0d14; text-decoration: none; font-weight: bold; padding: 12px 24px; border-radius: 8px; margin-top: 15px; }}
      </style>
    </head>
    <body>
      <div class="card">
        <div class="badge">ACAT BASSO POLESINE · CONFERMA PRENOTAZIONE</div>
        <h1>{subject}</h1>
        <div class="highlight-box">
          <pre style="white-space: pre-wrap; font-family: inherit; margin: 0; color: #e2e8f0;">{body_text}</pre>
        </div>
        <p style="font-size: 13px; color: #94a3b8;">
          Questa comunicazione è stata inviata automaticamente dalla segreteria organizzativa DEPENDEX per conto di ACAT Basso Polesine.
        </p>
        <div class="footer">
          ACAT Basso Polesine — Viale Kennedy 63, Taglio di Po (RO)<br>
          Metodo Hudolin O.D.V. · info@dependex.support · Tel. WhatsApp: 376 151 6301
        </div>
      </div>
    </body>
    </html>
    """

    res = send_smtp(to_email, subject, html_content, text_content=body_text)
    if res.get("status") == "SUCCESS":
        print(f"[OK] Email inviata a {to_email}")
        sys.exit(0)
    else:
        print(f"[WARN] Errore invio: {res.get('error')}")
        sys.exit(1)

if __name__ == "__main__":
    main()
