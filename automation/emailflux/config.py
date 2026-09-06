"""
config.py — Configurazione Centralizzata Email Marketing DEPENDEX.SOCIAL / OLTRE.SOCIAL
Gestione parametri SMTP Hostinger, Database SQLite locale, Limiti di invio e Destinatari di test.
"""
import os
from pathlib import Path

# Percorsi base
BASE_DIR = Path(__file__).resolve().parent
DATA_DIR = BASE_DIR / "data"
TEMPLATES_DIR = BASE_DIR / "templates"
BANNERS_DIR = BASE_DIR / "banners"

DATA_DIR.mkdir(parents=True, exist_ok=True)
TEMPLATES_DIR.mkdir(parents=True, exist_ok=True)
BANNERS_DIR.mkdir(parents=True, exist_ok=True)

# Database SQLite dedicato per Email Marketing & CRM
DB_PATH = os.getenv("DEPENDEX_EMAIL_DB", str(DATA_DIR / "emailflux.db"))
LEADS_CSV = os.getenv("DEPENDEX_LEADS_CSV", str(DATA_DIR / "leads_master_7445.csv"))

# Configurazione SMTP Hostinger
SMTP_HOST = os.getenv("SMTP_HOST", "smtp.hostinger.com")
SMTP_PORT = int(os.getenv("SMTP_PORT", "465"))
SMTP_USER = os.getenv("SMTP_USER", "info@dependex.support")
SMTP_AUTH_FALLBACK = os.getenv("SMTP_AUTH_FALLBACK", "info@dependex.social")
SMTP_PASS = os.getenv("SMTP_PASS", "h29031976T.")

# Brand & Identità Mittente
BRAND_NAME = os.getenv("BRAND_NAME", "DEPENDEX")
FROM_EMAIL = os.getenv("FROM_EMAIL", "info@dependex.support")
SENDER_EMAIL = os.getenv("SENDER_EMAIL", "info@dependex.support")
MAIL_FROM = os.getenv("MAIL_FROM", f"{BRAND_NAME} · {BRAND_PAYOFF} <{SENDER_EMAIL}>")
REPLY_TO = os.getenv("REPLY_TO", "info@dependex.support")
BASE_URL = os.getenv("BASE_URL", "https://dependex.social")



# Test e Invii di Prova
TEST_RECIPIENT = os.getenv("TEST_RECIPIENT", "labomobile.lm@gmail.com")

# Regole di invio e Warm-up Dominio
DRY_RUN = os.getenv("DRY_RUN", "0") == "1"
DAILY_CAP = int(os.getenv("DAILY_CAP", "150"))       # Protezione reputazione IP/dominio
BATCH_SIZE = int(os.getenv("BATCH_SIZE", "30"))      # Invii per singolo batch
THROTTLE_SEC = float(os.getenv("THROTTLE_SEC", "4"))  # Pausa tra email successive
