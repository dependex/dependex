"""
transport.py — Layer di trasporto SMTP per invio email affidabile via Hostinger.
Supporta crittografia SSL diretta (porta 465), fallback password, tracciamento e gestione unsubscription.
"""
import ssl
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.image import MIMEImage
from email.utils import formataddr, make_msgid
from config import SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_AUTH_FALLBACK, SMTP_PASS, MAIL_FROM, REPLY_TO, BASE_URL

def send_smtp(to_email, subject, html_content, text_content=None, list_unsub_url=None, inline_images=None):
    """
    Invia un'email via SMTP SSL su smtp.hostinger.com:465
    """
    if list_unsub_url is None:
        list_unsub_url = f"{BASE_URL}/privacy.php"

    msg = MIMEMultipart("related")
    msg["Subject"] = subject
    msg["From"] = MAIL_FROM
    msg["To"] = to_email
    msg["Reply-To"] = REPLY_TO
    msg["Message-ID"] = make_msgid(domain="dependex.support")
    msg["List-Unsubscribe"] = f"<{list_unsub_url}>"
    msg["List-Unsubscribe-Post"] = "List-Unsubscribe=One-Click"

    alt_part = MIMEMultipart("alternative")
    
    if text_content:
        alt_part.attach(MIMEText(text_content, "plain", "utf-8"))
    
    alt_part.attach(MIMEText(html_content, "html", "utf-8"))
    msg.attach(alt_part)

    # Immagini incorporate (CID) se presenti
    if inline_images:
        for cid, img_path in inline_images.items():
            try:
                with open(img_path, "rb") as f:
                    img_data = f.read()
                img = MIMEImage(img_data)
                img.add_header("Content-ID", f"<{cid}>")
                img.add_header("Content-Disposition", "inline", filename=img_path.name)
                msg.attach(img)
            except Exception as e:
                print(f"[WARN] Impossibile allegare immagine inline {img_path}: {e}")

    ctx = ssl.create_default_context()
    
    # Tentativo di connessione: prima come info@dependex.support, poi con account collegato autorizzato
    users_to_try = [SMTP_USER]
    if SMTP_AUTH_FALLBACK and SMTP_AUTH_FALLBACK != SMTP_USER:
        users_to_try.append(SMTP_AUTH_FALLBACK)

    passwords_to_try = [SMTP_PASS]
    if SMTP_PASS.endswith("."):
        passwords_to_try.append(SMTP_PASS[:-1])
    else:
        passwords_to_try.append(SMTP_PASS + ".")

    last_error = None
    for user in users_to_try:
        for pwd in passwords_to_try:
            try:
                with smtplib.SMTP_SSL(SMTP_HOST, SMTP_PORT, context=ctx, timeout=25) as server:
                    server.login(user, pwd)
                    server.sendmail(user, [to_email], msg.as_string())
                return {"status": "SUCCESS", "provider": f"hostinger-smtp ({user})", "error": None}
            except smtplib.SMTPAuthenticationError as e:
                last_error = f"AuthError ({user}): {e}"
                continue
            except Exception as e:
                last_error = f"SMTPException: {e}"
                break

    return {"status": "ERROR", "provider": "hostinger-smtp", "error": str(last_error)}

