import ftplib
import os
import urllib.request
import time

FTP_HOST = "ftp.dependex.social"
FTP_PASS = "h29031976T."

TARGETS = [
    {"name": "dependex.social", "user": "u173050672.dependex.social", "url": "https://dependex.social"},
    {"name": "oltre.social", "user": "u173050672.oltre.social", "url": "https://oltre.social"}
]

FILES_TO_UPLOAD = [
    "offline.html",
    "service-worker.js",
    "assets/js/dx-pwa-companion.js",
    "club-public.php",
    "mappa-club.php",
    "bootstrap.php",
    "_footer.php",
    "data/acat_community.sqlite"
]

def ensure_remote_dir(ftp, dir_path):
    parts = dir_path.strip("/").split("/")
    current = ""
    for part in parts:
        if not part:
            continue
        current = f"{current}/{part}" if current else part
        try:
            ftp.mkd(current)
            print(f"Created remote dir: {current}")
        except Exception:
            pass

base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

for t in TARGETS:
    print(f"\n==================================================")
    print(f"DEPLOYING TO {t['name']} ({t['user']})")
    print(f"==================================================")
    
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=60)
        ftp.login(t['user'], FTP_PASS)
        
        ensure_remote_dir(ftp, "assets/js")
        ensure_remote_dir(ftp, "data")
        
        for rel_path in FILES_TO_UPLOAD:
            local_path = os.path.join(base_dir, rel_path.replace("/", os.sep))
            if not os.path.exists(local_path):
                print(f"  [MISSING] {local_path}")
                continue
            
            remote_dir = os.path.dirname(rel_path)
            if remote_dir:
                ensure_remote_dir(ftp, remote_dir)
            
            with open(local_path, "rb") as f:
                ftp.storbinary(f"STOR {rel_path}", f)
            print(f"  [UPLOADED] {rel_path} ({os.path.getsize(local_path):,} bytes)")
        
        ftp.quit()
        print(f"  Deployment completed successfully for {t['name']}")
    except Exception as e:
        print(f"  [ERROR] Failed deployment to {t['name']}: {e}")

# Live HTTP verification
print("\n==================================================")
print("VERIFYING LIVE ENDPOINTS")
print("==================================================")
time.sleep(1)

test_urls = [
    ("https://dependex.social/offline.html", "800 974250"),
    ("https://dependex.social/service-worker.js", "dependex-pwa"),
    ("https://dependex.social/mappa-club.php", "chunkedLoading"),
    ("https://dependex.social/club-public.php?sic=SIC-CAT-001", "contact-bridge-card"),
    ("https://oltre.social/offline.html", "800 974250"),
    ("https://oltre.social/mappa-club.php", "chunkedLoading")
]

req_headers = {"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) DependexDeployVerifier/2.0"}

for url, needle in test_urls:
    try:
        req = urllib.request.Request(url, headers=req_headers)
        with urllib.request.urlopen(req, timeout=15) as resp:
            status = resp.status
            content = resp.read().decode('utf-8', errors='ignore')
            found = (needle in content)
            if status == 200 and found:
                print(f"  [LIVE OK 200] {url} -> trovata keyword '{needle}'")
            else:
                print(f"  [LIVE WARN] {url} -> status {status}, keyword '{needle}' presente: {found}")
    except Exception as e:
        print(f"  [LIVE ERR] {url} -> {e}")

print("\nDeployment and live verification completed!")
