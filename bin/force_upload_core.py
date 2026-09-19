# -*- coding: utf-8 -*-
"""
Force Upload di tutte le pagine web core e del database su dependex.social e oltre.social.
Sovrascrive direttamente tutti i file per aggiornare timestamp e forzare refresh cache server.
"""

import os
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

CORE_FILES = [
    "index.php",
    "parla-con-noi.php",
    "storie.php",
    "_header.php",
    "_footer.php",
    "world-club-explorer.php",
    "club-public.php",
    "metodo.php",
    "events-public.php",
    "event-detail.php",
    "academy-public.php",
    "offers.php",
    "guida-gratuita.php",
    "help.php",
    "crociera-benessere-masterclass.php",
    "viaggi-esperienziali.php",
    "bootstrap.php",
    "sitemap.xml",
    "robots.txt",
    "llms.txt",
    ".htaccess",
    os.path.join("assets", "css", "dependex-human-community.css"),
    os.path.join("assets", "css", "app.css"),
    os.path.join("assets", "css", "rainbow-neon.css"),
    os.path.join("data", "acat_community.sqlite")
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
            except Exception:
                pass

def run():
    for target in TARGETS:
        name = target["name"]
        user = target["user"]
        print(f"\n=======================================================")
        print(f" FORCE UPLOAD SUI SERVER: {name} ({user})")
        print(f"=======================================================")

        ftp = ftplib.FTP(timeout=60)
        ftp.connect(FTP_HOST, 21)
        ftp.login(user, FTP_PASS)
        ftp.set_pasv(True)
        ftp.voidcmd("TYPE I")
        root_pwd = ftp.pwd()

        uploaded = 0
        for rel_p in CORE_FILES:
            loc_p = os.path.join(ROOT_DIR, rel_p)
            if not os.path.exists(loc_p):
                print(f" [SKIP] {rel_p}")
                continue
            rel_norm = rel_p.replace("\\", "/")
            r_dir = os.path.dirname(rel_norm)
            r_name = os.path.basename(rel_norm)

            ftp.cwd(root_pwd)
            if r_dir:
                ensure_remote_dir(ftp, root_pwd.rstrip("/") + "/" + r_dir)

            sz = os.path.getsize(loc_p)
            print(f" -> STOR {rel_norm} ({sz:,} bytes)...", end="", flush=True)
            with open(loc_p, "rb") as fp:
                ftp.storbinary(f"STOR {r_name}", fp, blocksize=65536)
            print(" [OK]")
            uploaded += 1

        ftp.cwd(root_pwd)
        ftp.quit()
        print(f"Completato {name}: {uploaded} file caricati.")

    print("\n=======================================================")
    print(" VERIFICA ONLINE RISPOSTE HTTP LIVE")
    print("=======================================================")
    for target in TARGETS:
        url = target["url"]
        for path in ["index.php", "parla-con-noi.php", "storie.php", "world-club-explorer.php"]:
            t_url = f"{url}/{path}"
            try:
                req = urllib.request.Request(
                    t_url,
                    headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) DependexDeployVerifier/5.0"}
                )
                with urllib.request.urlopen(req, timeout=15) as resp:
                    print(f" - {t_url}: HTTP {resp.status}")
            except Exception as e:
                print(f" - {t_url}: ERRORE {e}")

if __name__ == "__main__":
    run()
