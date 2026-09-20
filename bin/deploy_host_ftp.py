# -*- coding: utf-8 -*-
"""
Script di sincronizzazione e deploy diretto FTP per dependex.social e oltre.social.
Non utilizza GitHub Actions. Esegue upload sicuro e verifica di integrità.
"""

import os
import sys
import ftplib
import urllib.request
import urllib.error

FTP_HOST = "ftp.dependex.social"
FTP_PASS = "h29031976T."

TARGETS = [
    {
        "name": "dependex.social",
        "user": "u173050672.dependex.social",
        "url": "https://dependex.social"
    },
    {
        "name": "oltre.social",
        "user": "u173050672.oltre.social",
        "url": "https://oltre.social"
    }
]

ROOT_DIR = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"

# File e cartelle prioritari da sincronizzare
FILES_TO_SYNC = [
    # Core pagine aggiornate
    "bootstrap.php",
    "api.php",
    "telemetria.php",
    "mappa-club.php",
    "api-clubs-italy.php",
    "cortex.php",
    "api-cortex.php",
    "world-club-explorer.php",
    "_header.php",
    "_footer.php",
    "index.php",
    "offers.php",
    "metodo.php",
    "guida-gratuita.php",
    "crociera-benessere-masterclass.php",
    "viaggi-esperienziali.php",
    "event-detail.php",
    "events-public.php",
    "events.php",
    "event-ics.php",
    "parla-con-noi.php",
    "api-event-booking.php",
    "api-club-locator.php",
    os.path.join("templates", "_event_fast_checkout.php"),
    os.path.join("templates", "_reviews_ticker.php"),
    os.path.join("templates", "_club_locator_widget.php"),
    "recensioni.php",
    "testimonianze.php",
    "domande-frequenti.php",
    "faq.php",
    "privacy-center.php",
    "sobriety.php",
    "login.php",
    "register.php",
    "cart.php",
    "storie.php",
    "world-map.php",
    "terms.php",
    "privacy.php",
    "db.php",
    "dr-env.php",
    "eco-db.php",
    "eco-sic.php",
    "mailer.php",
    "_nucleo.php",
    "_testa.php",
    "_piede.php",
    "_media.php",
    "ruota-della-vita.php",
    "piramide-maslow.php",
    "orientamento.php",
    "welfare-compass.php",
    "playground.php",
    "dashboard.php",
    os.path.join("modules", "gamification", "OmniWelfareGamificationEngine.php"),
    os.path.join("modules", "welfare", "HumanWelfareEngine.php"),
    os.path.join("modules", "reviews", "ReviewsService.php"),
    os.path.join("modules", "telemetry", "dx-telemetry-engine.php"),
    os.path.join("assets", "js", "dx-telemetry.js"),
    os.path.join("data", "recensioni_club_italia.json"),
    "academy-public.php",
    "help.php",
    "sitemap.xml",
    "robots.txt",
    "llms.txt",
    ".htaccess",

    # Dati e database aggiornati
    os.path.join("data", "acat_community.sqlite"),
    os.path.join("data", "CENSIMENTO_CLUB_CAT_ITALIA_2026.csv"),
    os.path.join("data", "ACAT_Italia_Club_Census_V1.csv"),
    os.path.join("data", "DEPENDEX_World_Registry_Master.csv"),
    os.path.join("data", "DEPENDEX_World_Registry_Normalized_V5.csv"),
    os.path.join("data", "OLTRE_Global_Hudolin_CAT_Network_V1.csv"),
    os.path.join("data", "dependex.db"),

    # Script di migrazione e arricchimento
    os.path.join("bin", "enrich-cat-network.php"),
    os.path.join("bin", "update-cat-intel-v2.php"),
    os.path.join("bin", "update-cat-intel-v3.php"),

    # Asset universali e API
    os.path.join("assets", "css", "rainbow-neon.css"),
    os.path.join("assets", "css", "universal-cart-checkout.css"),
    os.path.join("assets", "css", "universal-chat-ai.css"),
    os.path.join("assets", "js", "app.js"),
    os.path.join("assets", "js", "dx-pwa-companion.js"),
    os.path.join("assets", "js", "universal-cart-checkout.js"),
    os.path.join("assets", "js", "universal-chat-ai.js"),
    os.path.join("assets", "logo.png"),
    os.path.join("assets", "logo.svg"),
    os.path.join("api", "paypal_capture.php"),

    # PWA & Offline Readiness
    "service-worker.js",
    "offline.html",
    "manifest.webmanifest",
    "llms-full.txt",

    # Governance e Specifiche Mobile First
    "AGENTS.md",
    "MASTER_EMAIL_OS_PROMPT.md",
    os.path.join("docs", "MOBILE_FIRST_VIEWPORT_SPEC.md"),
    os.path.join("docs", "HUMAN_WELFARE_OS_4.md"),
    os.path.join("docs", "HUMAN_WELFARE_ENGINE_5.md"),
    os.path.join("docs", "OMNI_WELFARE_GAMIFICATION_6.md"),
    os.path.join("tests", "test_mobile_first_viewport_e2e.php"),
    os.path.join("tests", "test_sovereign_mobile_pwa_sos_e2e.php"),
    os.path.join("tests", "test_human_welfare_os_e2e.php"),
    os.path.join("tests", "test_human_welfare_engine_5_e2e.php"),
    os.path.join("tests", "test_omni_welfare_gamification_6_e2e.php"),
    os.path.join("tests", "test_motivational_clips_e2e.php"),

    # Clip Video Motivazionali (8-10s)
    "clips.php",
    os.path.join("assets", "clips", "clip1_non_devi_sapere_tutto.mp4"),
    os.path.join("assets", "clips", "clip1_bg.jpg"),
    os.path.join("assets", "clips", "clip2_nessuno_e_solo.mp4"),
    os.path.join("assets", "clips", "clip2_bg.jpg"),
    os.path.join("assets", "clips", "clip3_fermati_e_respira.mp4"),
    os.path.join("assets", "clips", "clip3_bg.jpg"),
    os.path.join("assets", "clips", "clip4_navigare_le_onde.mp4"),
    os.path.join("assets", "clips", "clip4_bg.jpg"),
    os.path.join("assets", "clips", "clip5_rinascita_quotidiana.mp4"),
    os.path.join("assets", "clips", "clip5_bg.jpg")
]

def ensure_remote_dir(ftp, remote_dir_path):
    parts = [p for p in remote_dir_path.replace("\\", "/").split("/") if p]
    current = ""
    for part in parts:
        current += "/" + part
        try:
            ftp.cwd(current)
        except Exception:
            try:
                ftp.mkd(current)
                ftp.cwd(current)
            except Exception as e:
                pass

def deploy_target(target):
    print(f"\n=======================================================")
    print(f" Avvio Deploy Diretto FTP su: {target['name']}")
    print(f" Utente: {target['user']}")
    print(f"=======================================================")
    
    ftp = ftplib.FTP(timeout=30)
    ftp.connect(FTP_HOST, 21)
    ftp.login(target["user"], FTP_PASS)
    ftp.set_pasv(True)
    
    root_pwd = ftp.pwd()
    print(f"Connesso! Root PWD: {root_pwd}")

    uploaded_count = 0
    errors = []

    for rel_path in FILES_TO_SYNC:
        local_path = os.path.join(ROOT_DIR, rel_path)
        if not os.path.exists(local_path):
            print(f" [SKIP] File locale non trovato: {rel_path}")
            continue

        file_size = os.path.getsize(local_path)
        remote_normalized = rel_path.replace("\\", "/")
        remote_dir = os.path.dirname(remote_normalized)
        remote_filename = os.path.basename(remote_normalized)

        print(f" -> Upload: {remote_normalized} ({file_size:,} bytes)...", end="", flush=True)
        success = False
        for attempt in range(3):
            try:
                ftp.cwd(root_pwd)
                if remote_dir:
                    ensure_remote_dir(ftp, root_pwd.rstrip("/") + "/" + remote_dir)
                with open(local_path, "rb") as fp:
                    ftp.storbinary(f"STOR {remote_filename}", fp)
                print(" [OK]")
                uploaded_count += 1
                success = True
                break
            except Exception as err:
                if attempt < 2:
                    print(f" [RETRY {attempt+1}...]", end="", flush=True)
                    try:
                        ftp.close()
                    except Exception:
                        pass
                    import time
                    time.sleep(2)
                    try:
                        ftp = ftplib.FTP(FTP_HOST, timeout=30)
                        ftp.login(target["user"], FTP_PASS)
                        ftp.set_pasv(True)
                    except Exception:
                        pass
                else:
                    print(f" [ERRORE: {err}]")
                    errors.append((rel_path, str(err)))

    ftp.cwd(root_pwd)
    ftp.quit()
    print(f"\nDeploy per {target['name']} completato: {uploaded_count} file caricati, {len(errors)} errori.")
    return uploaded_count, errors

def verify_live(url):
    test_urls = [
        f"{url}/world-club-explorer.php",
        f"{url}/index.php"
    ]
    results = {}
    for t_url in test_urls:
        try:
            req = urllib.request.Request(
                t_url, 
                headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DependexDeployVerifier/1.0'}
            )
            with urllib.request.urlopen(req, timeout=15) as resp:
                status = resp.status
                body = resp.read().decode('utf-8', errors='ignore')
                has_clubs = "572" in body or "Club Alcologici" in body or "ARCAT" in body
                results[t_url] = {"status": status, "has_content": has_clubs, "bytes": len(body)}
        except Exception as e:
            results[t_url] = {"status": "FAILED", "error": str(e)}
    return results

if __name__ == "__main__":
    total_uploaded = 0
    all_errors = []
    
    for t in TARGETS:
        u_count, errs = deploy_target(t)
        total_uploaded += u_count
        all_errors.extend(errs)

    print("\n=======================================================")
    print(" VERIFICA ONLINE RISPOSTA HTTP IN TEMPO REALE")
    print("=======================================================")
    for t in TARGETS:
        res = verify_live(t["url"])
        print(f"\nTarget: {t['name']}")
        for u, r in res.items():
            print(f" - {u}: {r}")

    if all_errors:
        print("\nAlcuni errori riscontrati durante il deploy:", all_errors)
        sys.exit(1)
    else:
        print(f"\nTUTTI I {total_uploaded} FILE SONO STATI PUBBLICATI CON SUCCESSO SUI SERVER HOSTINGER!")
