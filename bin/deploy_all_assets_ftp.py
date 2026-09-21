# -*- coding: utf-8 -*-
"""
Deploy completo del sito web dependex.social e oltre.social.
Include tutti i file PHP, CSS, JS, database web, immagini, brand, eventi e documenti PDF del portale.
Esclude cartelle interne di compilazione KDP Amazon (file di stampa da 80+ MB), git e log.
"""

import os
import sys
import ftplib
import urllib.request

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

# Cartelle locali interne o pesanti di stampa KDP da non inviare al web host
EXCLUDE_PREFIXES = [
    ".git",
    ".github",
    "_ai_archive",
    "scratch",
    ".tmp",
    "__pycache__",
    "amazon",                   # Libri di stampa KDP da 80+ MB
    "acat-hudolin-kdp-factory", # Motore interno locale Typst/KDP
    "cortex",                   # Agenti interni locali
    "revenue-blueprints",       # Documenti interni
    "reports",                  # Report interni
    "tests"                     # Test locali
]

EXCLUDE_EXACT = {
    "apikeysAI.env",
    ".env"
}

def is_excluded(rel_path):
    norm = rel_path.replace("\\", "/")
    if any(norm == p or norm.startswith(p + "/") for p in EXCLUDE_PREFIXES):
        return True
    if os.path.basename(norm) in EXCLUDE_EXACT or norm.endswith(".log"):
        return True
    return False

def collect_web_files():
    files = []
    for root, dirs, filenames in os.walk(ROOT_DIR):
        dirs[:] = [d for d in dirs if not any(d == p or d.startswith(p) for p in EXCLUDE_PREFIXES)]
        for f in filenames:
            abs_p = os.path.join(root, f)
            rel_p = os.path.relpath(abs_p, ROOT_DIR)
            if is_excluded(rel_p):
                continue
            files.append(rel_p)
    return sorted(list(set(files)))

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
            except Exception:
                pass

def sync_target(target, file_list):
    print(f"\n=======================================================")
    print(f" SINCRONIZZAZIONE WEB COMPLETA: {target['name']}")
    print(f" Utente FTP: {target['user']}")
    print(f"=======================================================")

    ftp = ftplib.FTP(timeout=60)
    ftp.connect(FTP_HOST, 21)
    ftp.login(target["user"], FTP_PASS)
    ftp.set_pasv(True)
    ftp.voidcmd("TYPE I")

    root_pwd = ftp.pwd()
    print(f"Connesso! Root PWD: {root_pwd}")

    uploaded = 0
    skipped = 0
    errors = []

    for rel_p in file_list:
        local_p = os.path.join(ROOT_DIR, rel_p)
        if not os.path.exists(local_p):
            continue

        l_size = os.path.getsize(local_p)
        remote_normalized = rel_p.replace("\\", "/")
        remote_dir = os.path.dirname(remote_normalized)
        remote_name = os.path.basename(remote_normalized)

        # Controllo SIZE (richiede binary mode TYPE I)
        ftp.cwd(root_pwd)
        r_size = None
        try:
            r_size = ftp.size(remote_normalized)
        except Exception:
            pass

        FORCE_ALWAYS = {
            "index.php", "metodo.php", "_header.php", "_footer.php",
            "api-clubs-italy.php", "api-opendata-geojson.php", "mappa-club.php", "crm-clubs.php",
            "data/acat_community.sqlite", "data/CENSIMENTO_CLUB_CAT_ITALIA_2026.csv",
            "data/CRM_CLUB_CONTATTI_MASTER_2026.csv", "data/DEPENDEX_World_Registry_Master.csv",
            "sitemap-clubs.xml", "modules/welfare/HumanWelfareEngine.php"
        }

        if remote_normalized not in FORCE_ALWAYS and r_size is not None and r_size == l_size:
            skipped += 1
            continue

        if remote_dir:
            ensure_remote_dir(ftp, root_pwd.rstrip("/") + "/" + remote_dir)

        action = "NUOVO" if r_size is None else f"DIFF ({r_size} -> {l_size})"
        print(f" -> [{action}] {remote_normalized} ({l_size:,} bytes)...", end="", flush=True)

        try:
            with open(local_p, "rb") as fp:
                ftp.storbinary(f"STOR {remote_name}", fp, blocksize=65536)
            print(" [OK]")
            uploaded += 1
        except Exception as e:
            print(f" [ERRORE: {e}]")
            errors.append((rel_p, str(e)))

    ftp.cwd(root_pwd)
    ftp.quit()

    print(f"\nEsito {target['name']}: {uploaded} file caricati, {skipped} già allineati, {len(errors)} errori.")
    return uploaded, skipped, errors

def verify_live(url):
    test_urls = [
        f"{url}/index.php",
        f"{url}/parla-con-noi.php",
        f"{url}/storie.php",
        f"{url}/world-club-explorer.php",
        f"{url}/club/SIC-WS2VDS86-YRB6M7TV-B",
        f"{url}/club-public.php?id=SIC-WS2VDS86-YRB6M7TV-B",
        f"{url}/sitemap-clubs.xml",
        f"{url}/offers.php",
        f"{url}/metodo.php",
        f"{url}/events-public.php",
        f"{url}/assets/css/dependex-human-community.css",
        f"{url}/manuale_integrato_hudolin.pdf",
        f"{url}/Ruota_Vita.pdf"
    ]
    results = {}
    for t_url in test_urls:
        try:
            req = urllib.request.Request(
                t_url,
                headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DependexDeployVerifier/4.0'}
            )
            with urllib.request.urlopen(req, timeout=15) as resp:
                results[t_url] = {"status": resp.status, "bytes": resp.headers.get("Content-Length")}
        except Exception as e:
            results[t_url] = {"status": "FAILED", "error": str(e)}
    return results

if __name__ == "__main__":
    files = collect_web_files()
    print(f"File web totali verificati per il deploy: {len(files)}")

    total_up = 0
    total_skip = 0
    all_errs = []

    for t in TARGETS:
        u, s, errs = sync_target(t, files)
        total_up += u
        total_skip += s
        all_errs.extend(errs)

    print("\n=======================================================")
    print(" VERIFICA LIVE HTTP DEGLI ENDPOINT PUBBLICATI")
    print("=======================================================")
    for t in TARGETS:
        res = verify_live(t["url"])
        print(f"\nDominio: {t['name']}")
        for u, r in res.items():
            print(f" - {u}: {r}")

    if all_errs:
        print("\nErrori riscontrati durante il deploy:", all_errs)
        sys.exit(1)
    else:
        print(f"\nPUBBLICAZIONE COMPLETA ESEGUITA CON SUCCESSO! ({total_up} file caricati, {total_skip} file confermati identici)")
