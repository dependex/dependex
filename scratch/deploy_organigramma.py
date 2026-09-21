import ftplib
import os
import urllib.request
import time

FTP_HOST = 'ftp.dependex.social'
FTP_PASS = 'h29031976T.'

TARGETS = [
    {'name': 'dependex.social', 'user': 'u173050672.dependex.social'},
    {'name': 'oltre.social', 'user': 'u173050672.oltre.social'}
]

FILES = [
    ('_header.php', '_header.php'),
    ('_footer.php', '_footer.php'),
    ('api-organigramma.php', 'api-organigramma.php'),
    ('organigramma.php', 'organigramma.php'),
    ('piramide-rovesciata.php', 'piramide-rovesciata.php'),
]

for t in TARGETS:
    name = t['name']
    user = t['user']
    print(f"\n==========================================")
    print(f"CONNECTING TO {name} ({user})")
    print(f"==========================================")
    
    for local_p, remote_p in FILES:
        if not os.path.exists(local_p):
            print(f" [SKIP] Missing {local_p}")
            continue
            
        success = False
        for attempt in range(3):
            try:
                ftp = ftplib.FTP(FTP_HOST, timeout=60)
                ftp.login(user, FTP_PASS)
                ftp.set_pasv(True)
                
                size = os.path.getsize(local_p)
                print(f"Uploading {local_p} ({size} bytes) -> {remote_p} (attempt {attempt+1})...")
                with open(local_p, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_p}', f)
                ftp.quit()
                print(f"  [OK] {remote_p} uploaded successfully.")
                success = True
                break
            except Exception as e:
                print(f"  [RETRY] Attempt {attempt+1} failed: {e}")
                time.sleep(2)
        if not success:
            print(f"  [ERROR] Failed to upload {remote_p} to {name}")

print("\n=== VERIFYING LIVE HTTP ENDPOINTS ===")
for domain in ['https://dependex.social', 'https://oltre.social']:
    url = f"{domain}/organigramma.php"
    print(f"\nVerifying {url} ...")
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=20) as resp:
            content = resp.read().decode('utf-8', errors='ignore')
            print(f"  Status: {resp.status}")
            print(f"  Length: {len(content)} bytes")
            print(f"  'Organigramma della Rete' present: {'Organigramma della Rete' in content}")
            has_forbidden = 'piramide rovesciata' in content.lower() or 'piramide-rovesciata' in content.lower()
            print(f"  'piramide rovesciata' present: {has_forbidden}")
    except Exception as e:
        print(f"  Error: {e}")

    # Also test redirect of old URL
    old_url = f"{domain}/piramide-rovesciata.php"
    print(f"\nVerifying redirect of {old_url} ...")
    try:
        req = urllib.request.Request(old_url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=20) as resp:
            print(f"  Final URL: {resp.geturl()}")
            print(f"  Status: {resp.status}")
    except Exception as e:
        print(f"  Error: {e}")

print("\n=== ALL DEPLOYS AND CHECKS FINISHED ===")
