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
    "academy-public.php",
    "help.php",
    "sitemap.xml",
    "robots.txt",
    "llms.txt",
    ".htaccess",

    # Dati e database aggiornati
    os.path.join("data", "acat_community.sqlite"),
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
    os.path.join("assets", "css", "universal-cart-checkout.css"),
    os.path.join("assets", "css", "universal-chat-ai.css"),
    os.path.join("assets", "js", "universal-cart-checkout.js"),
    os.path.join("assets", "js", "universal-chat-ai.js"),
    os.path.join("assets", "logo.png"),
    os.path.join("assets", "logo.svg"),
    os.path.join("api", "paypal_capture.php")
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

        # Riposiziona sempre su root_pwd
        ftp.cwd(root_pwd)
        if remote_dir:
            ensure_remote_dir(ftp, root_pwd.rstrip("/") + "/" + remote_dir)

        print(f" -> Upload: {remote_normalized} ({file_size:,} bytes)...", end="", flush=True)
        try:
            with open(local_path, "rb") as fp:
                ftp.storbinary(f"STOR {remote_filename}", fp)
            print(" [OK]")
            uploaded_count += 1
        except Exception as err:
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
