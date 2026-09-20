# -*- coding: utf-8 -*-
"""
bin/h24_watchdog_daemon.py — Demone di Monitoraggio H24 per DEPENDEX.SOCIAL & OLTRE.SOCIAL
Monitora costantemente disponibilità, latenza, integrità dei 322+ Club, motori maieutici e status SSL.
Registra su log locale e genera snapshot JSON per la control tower.
"""

import time
import json
import os
import sys
import urllib.request
import urllib.error
import ssl
import smtplib
from datetime import datetime
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA_DIR = os.path.join(BASE_DIR, "data")
LOG_FILE = os.path.join(DATA_DIR, "h24_watchdog_live.log")
STATUS_JSON = os.path.join(DATA_DIR, "h24_watchdog_status.json")

TARGET_ENDPOINTS = [
    {"name": "Home Dependex", "url": "https://dependex.social/index.php", "min_bytes": 100000},
    {"name": "World Club Explorer (366+ Club & APCAT)", "url": "https://dependex.social/world-club-explorer.php", "min_bytes": 200000},
    {"name": "Mappa 2D Interattiva & APCAT", "url": "https://dependex.social/mappa-club.php", "min_bytes": 150000},
    {"name": "Club Locator Geodesico (API)", "url": "https://dependex.social/api-club-locator.php?lat=44.9961&lon=12.2133", "min_bytes": 300},
    {"name": "Ruota della Vita & Orientamento", "url": "https://dependex.social/ruota-della-vita.php", "min_bytes": 20000},
    {"name": "Orientamento Maieutico 5.0", "url": "https://dependex.social/orientamento.php", "min_bytes": 30000},
    {"name": "Life Playground 6.0", "url": "https://dependex.social/playground.php?v=2", "min_bytes": 40000},
    {"name": "Motivational Clips 9:16", "url": "https://dependex.social/clips.php", "min_bytes": 20000},
    {"name": "Eventi & Prenotazioni", "url": "https://dependex.social/events-public.php", "min_bytes": 30000},
    {"name": "Ascolto Anonimo", "url": "https://dependex.social/parla-con-noi.php", "min_bytes": 15000},
    {"name": "Telemetria & Watchdog API", "url": "https://dependex.social/api.php?action=watchdog", "min_bytes": 100},
    {"name": "OpenData GeoJSON RFC 7946", "url": "https://dependex.social/api-opendata-geojson.php", "min_bytes": 50000},
    {"name": "Feed Territoriale Atom/GeoRSS", "url": "https://dependex.social/api-feed-territorio.php", "min_bytes": 50000},
    {"name": "Home Oltre Social", "url": "https://oltre.social/index.php", "min_bytes": 100000},
    {"name": "Life Playground Oltre", "url": "https://oltre.social/playground.php?v=2", "min_bytes": 40000},
]

SMTP_HOST = "smtp.hostinger.com"
SMTP_PORT = 465
SMTP_USER = "info@dependex.support"
SMTP_FALLBACK = "info@dependex.social"
SMTP_PASS = "h29031976T."
ALERT_RECIPIENT = "labomobile.lm@gmail.com"

def log_msg(msg: str):
    ts = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    line = f"[{ts}] {msg}"
    print(line)
    try:
        os.makedirs(DATA_DIR, exist_ok=True)
        with open(LOG_FILE, "a", encoding="utf-8") as f:
            f.write(line + "\n")
    except Exception as e:
        print(f"Errore scrittura log: {e}")

def check_endpoint(ep):
    start = time.time()
    req = urllib.request.Request(
        ep["url"],
        headers={
            "User-Agent": "Dependex-Watchdog-H24/1.0",
            "Cache-Control": "no-cache, no-store, must-revalidate",
            "Pragma": "no-cache"
        }
    )
    try:
        ctx = ssl.create_default_context()
        with urllib.request.urlopen(req, context=ctx, timeout=12) as res:
            elapsed_ms = int((time.time() - start) * 1000)
            body = res.read()
            bytes_count = len(body)
            is_ok = (res.status == 200) and (bytes_count >= ep["min_bytes"])
            return {
                "name": ep["name"],
                "url": ep["url"],
                "status_code": res.status,
                "latency_ms": elapsed_ms,
                "bytes": bytes_count,
                "ok": is_ok,
                "error": None
            }
    except Exception as err:
        elapsed_ms = int((time.time() - start) * 1000)
        return {
            "name": ep["name"],
            "url": ep["url"],
            "status_code": getattr(err, "code", 0),
            "latency_ms": elapsed_ms,
            "bytes": 0,
            "ok": False,
            "error": str(err)
        }

def send_email_report(subject: str, html_body: str) -> bool:
    msg = MIMEMultipart("alternative")
    msg["Subject"] = subject
    msg["From"] = f"DEPENDEX H24 WATCHDOG <{SMTP_USER}>"
    msg["To"] = ALERT_RECIPIENT
    msg["Reply-To"] = "info@dependex.support"
    msg.attach(MIMEText(html_body, "html", "utf-8"))

    ctx = ssl.create_default_context()
    for usr in [SMTP_USER, SMTP_FALLBACK]:
        msg.replace_header("From", f"DEPENDEX H24 WATCHDOG <{usr}>")
        try:
            with smtplib.SMTP_SSL(SMTP_HOST, SMTP_PORT, context=ctx, timeout=15) as s:
                s.login(usr, SMTP_PASS)
                s.sendmail(usr, [ALERT_RECIPIENT], msg.as_string())
                log_msg(f"Email inviata con successo via {usr} a {ALERT_RECIPIENT}")
                return True
        except Exception as e:
            log_msg(f"Invio SMTP fallito via {usr}: {e}")
    return False

def run_single_audit():
    results = []
    all_ok = True
    total_latency = 0
    for ep in TARGET_ENDPOINTS:
        res = check_endpoint(ep)
        results.append(res)
        if not res["ok"]:
            all_ok = False
        total_latency += res["latency_ms"]

    avg_latency = int(total_latency / len(results)) if results else 0
    status_summary = {
        "timestamp": datetime.now().isoformat(),
        "healthy": all_ok,
        "avg_latency_ms": avg_latency,
        "total_endpoints": len(results),
        "successful_endpoints": sum(1 for r in results if r["ok"]),
        "endpoints": results
    }

    try:
        with open(STATUS_JSON, "w", encoding="utf-8") as f:
            json.dump(status_summary, f, indent=2, ensure_ascii=False)
    except Exception as e:
        log_msg(f"Errore salvataggio status json: {e}")

    log_msg(f"Audit completato: {status_summary['successful_endpoints']}/{status_summary['total_endpoints']} OK (Latenza media: {avg_latency}ms)")
    return status_summary

def main():
    log_msg("=== AVVIO DEMONE MONITORAGGIO H24 DEPENDEX & OLTRE ===")
    
    # Esegue il primo audit immediato
    initial_summary = run_single_audit()
    
    # Generazione report HTML iniziale
    html_report = f"""
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <style>
        body {{ font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0b0d14; color: #f8fafc; padding: 24px; margin: 0; }}
        .box {{ max-width: 680px; margin: 0 auto; background: #151928; border: 1px solid rgba(0,212,255,0.3); border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }}
        h1 {{ color: #00d4ff; font-size: 22px; margin-top: 0; }}
        .stat-grid {{ display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 20px 0; }}
        .stat {{ background: rgba(255,255,255,0.04); padding: 14px; border-radius: 10px; border-left: 3px solid #00d4ff; }}
        .stat-val {{ font-size: 20px; font-weight: 800; color: #fff; }}
        .stat-lbl {{ font-size: 11px; color: #94a3b8; text-transform: uppercase; }}
        table {{ width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }}
        th, td {{ padding: 10px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); }}
        th {{ color: #94a3b8; font-size: 11px; text-transform: uppercase; }}
        .badge-ok {{ color: #10b981; font-weight: bold; }}
        .badge-err {{ color: #ef4444; font-weight: bold; }}
        .footer {{ margin-top: 24px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 11px; color: #64748b; text-align: center; }}
      </style>
    </head>
    <body>
      <div class="box">
        <h1>DEPENDEX & OLTRE · Report Monitoraggio H24</h1>
        <p style="color:#cbd5e1; font-size:14px; line-height:1.5;">
          Audit periodico automatizzato dell'infrastruttura di orientamento e supporto multifamiliare ai Club Alcologici Territoriali.
        </p>
        <div class="stat-grid">
          <div class="stat">
            <div class="stat-val">{'HEALTHY' if initial_summary['healthy'] else 'DEGRADED'}</div>
            <div class="stat-lbl">Stato Generale</div>
          </div>
          <div class="stat">
            <div class="stat-val">{initial_summary['avg_latency_ms']} ms</div>
            <div class="stat-lbl">Latenza Media</div>
          </div>
        </div>
        <table>
          <thead>
            <tr>
              <th>Modulo / Endpoint</th>
              <th>Status</th>
              <th>Latenza</th>
              <th>Payload</th>
            </tr>
          </thead>
          <tbody>
    """
    for ep in initial_summary["endpoints"]:
        status_cls = "badge-ok" if ep["ok"] else "badge-err"
        status_txt = "ATTIVO (200)" if ep["ok"] else f"FAIL ({ep['status_code']})"
        html_report += f"""
            <tr>
              <td style="color:#f1f5f9; font-weight:600;">{ep['name']}</td>
              <td class="{status_cls}">{status_txt}</td>
              <td style="color:#cbd5e1;">{ep['latency_ms']}ms</td>
              <td style="color:#94a3b8;">{ep['bytes']:,} bytes</td>
            </tr>
        """
    html_report += f"""
          </tbody>
        </table>
        <div class="footer">
          Watchdog attivo H24 · Control Plane Hostinger Native · Mittente: info@dependex.support<br>
          Generato il: {datetime.now().strftime('%d/%m/%Y alle ore %H:%M:%S')}
        </div>
      </div>
    </body>
    </html>
    """

    # Tentativo invio report iniziale a labomobile.lm@gmail.com
    send_email_report(
        f"DEPENDEX & OLTRE · Report Operativo H24 [{datetime.now().strftime('%d/%m/%Y %H:%M')}]",
        html_report
    )

    # Ciclo di monitoraggio H24 (ogni 60 secondi)
    cycle = 1
    while True:
        try:
            time.sleep(60)
            summary = run_single_audit()
            cycle += 1
            # In caso di failure, invia subito notifica alert
            if not summary["healthy"]:
                log_msg("[ALERT] Uno o più endpoint hanno riscontrato anomalie! Invio notifica...")
                send_email_report("ALERT DEPENDEX · Rilevata Anomalia su Endpoint", html_report)
        except KeyboardInterrupt:
            log_msg("Demone interrotto dall'operatore.")
            break
        except Exception as err:
            log_msg(f"Errore nel ciclo di monitoraggio: {err}")
            time.sleep(10)

if __name__ == "__main__":
    main()
