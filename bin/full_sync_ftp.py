# -*- coding: utf-8 -*-
"""
Sincronizzatore completo intelligente FTP per dependex.social e oltre.social.
Esegue una scansione ricorsiva sia locale che remota.
Carica file mancanti, nuovi o con dimensione differente.
Garantisce la pubblicazione totale di tutti i contenuti, asset, script e database.
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

# Cartelle e file da escludere dalla pubblicazione pubblica
EXCLUDE_DIRS = {
    ".git", 
    ".github", 
    "_ai_archive", 
    "scratch", 
    "__pycache__", 
    ".venv", 
    "venv", 
    ".idea", 
    ".vscode"
}

EXCLUDE_FILES = {
    "apikeysAI.env",
    ".env"
}

def get_remote_file_map(ftp, base_dir=""):
    """
    Raccoglie ricorsivamente la mappa di tutti i file remoti con le relative dimensioni in byte.
    """
    remote_map = {} # rel_path_unix -> size_in_bytes
    
    def _walk(curr_dir):
        try:
            entries = []
            ftp.retrlines("LIST", entries.append)
        except Exception:
            return

        for entry in entries:
            # Unix LIST output format: permissions links owner group size month day time/year name
            parts = entry.split(None, 8)
            if len(parts) < 9:
                continue
            perms = parts[0]
            size_str = parts[4]
            name = parts[8]
            
            if name in (".", ".."):
                continue

            rel_path = f"{curr_dir}/{name}".lstrip("/")
            
            if perms.startswith("d"):
                # Directory
                try:
                    ftp.cwd(name)
                    _walk(rel_path)
                    ftp.cwd("..")
                except Exception:
                    pass
            else:
                try:
                    size = int(size_str)
                except ValueError:
                    size = -1
                remote_map[rel_path] = size

    _walk(base_dir)
    return remote_map

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

def sync_target(target):
    print(f"\n=======================================================")
    print(f" SINCRONIZZAZIONE TOTALE SU: {target['name']}")
    print(f" Account FTP: {target['user']}")
    print(f"=======================================================")

    ftp = ftplib.FTP(timeout=30)
    ftp.connect(FTP_HOST, 21)
    ftp.login(target["user"], FTP_PASS)
    ftp.set_pasv(True)

    root_pwd = ftp.pwd()
    print(f"Connesso con successo. Root: {root_pwd}")

    print("Scansione file remoti in corso...")
    remote_files = get_remote_file_map(ftp)
    print(f"Rilevati {len(remote_files)} file esistenti sul server remoto.")

    # Scansione locale
    local_files = {} # rel_path_unix -> abs_path
    for root, dirs, files in os.walk(ROOT_DIR):
        dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS and not d.startswith(".tmp")]
        for f in files:
            if f.endswith(".log") or f.startswith(".tmp") or f in EXCLUDE_FILES:
                continue
            abs_p = os.path.join(root, f)
            rel_p = os.path.relpath(abs_p, ROOT_DIR).replace("\\", "/")
            local_files[rel_p] = abs_p

    print(f"Rilevati {len(local_files)} file locali candidati alla pubblicazione.")

    to_upload = []
    for rel_p, abs_p in local_files.items():
        local_size = os.path.getsize(abs_p)
        remote_size = remote_files.get(rel_p)
        
        # Carica se non esiste su remoto o se la dimensione differisce
        if remote_size is None or remote_size != local_size:
            to_upload.append((rel_p, abs_p, local_size, remote_size))

    print(f"File da aggiornare o creare su {target['name']}: {len(to_upload)}")

    uploaded_count = 0
    errors = []

    for rel_p, abs_p, l_size, r_size in to_upload:
        remote_dir = os.path.dirname(rel_p)
        remote_name = os.path.basename(rel_p)
        
        ftp.cwd(root_pwd)
        if remote_dir:
            ensure_remote_dir(ftp, root_pwd.rstrip("/") + "/" + remote_dir)

        action_desc = "NUOVO" if r_size is None else f"DIFF ({r_size} -> {l_size})"
        print(f" -> [{action_desc}] {rel_p} ({l_size:,} bytes)...", end="", flush=True)

        try:
            with open(abs_p, "rb") as fp:
                ftp.storbinary(f"STOR {remote_name}", fp)
            print(" [OK]")
            uploaded_count += 1
        except Exception as err:
            print(f" [ERRORE: {err}]")
            errors.append((rel_p, str(err)))

    ftp.cwd(root_pwd)
    ftp.quit()

    print(f"\nEsito {target['name']}: {uploaded_count} file caricati, {len(errors)} errori.")
    return uploaded_count, errors

def verify_live(url):
    test_urls = [
        f"{url}/world-club-explorer.php",
        f"{url}/index.php",
        f"{url}/offers.php",
        f"{url}/metodo.php",
        f"{url}/events-public.php"
    ]
    results = {}
    for t_url in test_urls:
        try:
            req = urllib.request.Request(
                t_url,
                headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) DependexDeployVerifier/2.0'}
            )
            with urllib.request.urlopen(req, timeout=15) as resp:
                status = resp.status
                body = resp.read().decode('utf-8', errors='ignore')
                results[t_url] = {"status": status, "bytes": len(body)}
        except Exception as e:
            results[t_url] = {"status": "FAILED", "error": str(e)}
    return results

if __name__ == "__main__":
    total_uploaded = 0
    all_errors = []

    for t in TARGETS:
        u_count, errs = sync_target(t)
        total_uploaded += u_count
        all_errors.extend(errs)

    print("\n=======================================================")
    print(" VERIFICA HTTP FINALE ONLINE SU TUTTE LE ROTTE CHIAVE")
    print("=======================================================")
    for t in TARGETS:
        res = verify_live(t["url"])
        print(f"\nDominio: {t['name']}")
        for u, r in res.items():
            print(f" - {u}: {r}")

    if all_errors:
        print("\nErrori rilevati durante la sincronizzazione:", all_errors)
        sys.exit(1)
    else:
        print(f"\nSINCRONIZZAZIONE COMPLETA E PUBBLICAZIONE TERMINATA CON SUCCESSO! ({total_uploaded} aggiornamenti applicati)")
