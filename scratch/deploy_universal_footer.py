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

# Files to deploy
FILES_TO_UPLOAD = [
    "_footer.php",
    "_header.php",
    "assets/css/app.css",
    "index.php",
    "world-club-explorer.php",
    "manifest.webmanifest",
    "admin.php",
    "dashboard.php",
    "club-public.php",
    "templates/_club_locator_widget.php",
    "sitemap.xml",
    "bootstrap.php",
    "api-public-metrics.php",
    "modules/clubs/ClubMetricsService.php",
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

for t in TARGETS:
    print(f"\n==================================================")
    print(f"DEPLOYING TO {t['name']} ({t['user']})")
    print(f"==================================================")
    
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(t['user'], FTP_PASS)
    
    # Ensure necessary subdirectories exist
    ensure_remote_dir(ftp, "modules/clubs")
    ensure_remote_dir(ftp, "templates")
    ensure_remote_dir(ftp, "assets/css")
    ensure_remote_dir(ftp, "data")
    
    for rel_path in FILES_TO_UPLOAD:
        if not os.path.exists(rel_path):
            print(f" [SKIP] Local file not found: {rel_path}")
            continue
            
        remote_dir = os.path.dirname(rel_path)
        if remote_dir:
            ensure_remote_dir(ftp, remote_dir)
            
        with open(rel_path, "rb") as f:
            print(f" Uploading {rel_path} ...", end="", flush=True)
            ftp.storbinary(f"STOR {rel_path}", f)
            print(" [DONE]")
            
    ftp.quit()
    print(f"Deployment to {t['name']} completed successfully!")

print("\nAll deployments finished. Verifying live response...")
time.sleep(2)

for t in TARGETS:
    test_url = f"{t['url']}/?t={int(time.time())}"
    print(f"Probing {test_url} ...")
    try:
        req = urllib.request.Request(test_url, headers={'User-Agent': 'Mozilla/5.0 (DataIntegrityVerifier/1.0)'})
        with urllib.request.urlopen(req, timeout=15) as res:
            body = res.read().decode('utf-8', errors='ignore')
            has_footer = 'site-footer' in body
            has_normative = 'Quadro Normativo' in body
            has_rfc = 'RFC 8058 One-Click Unsubscribe' in body
            print(f" -> Status: {res.status} | Footer detected: {has_footer} | Normative: {has_normative} | RFC 8058: {has_rfc}")
    except Exception as e:
        print(f" -> Probe failed on {t['name']}: {e}")
