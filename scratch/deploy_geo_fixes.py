import ftplib
import os
import urllib.request
import re

FTP_HOST = 'ftp.dependex.social'
FTP_PASS = 'h29031976T.'

TARGETS = [
    {'name': 'dependex.social', 'user': 'u173050672.dependex.social'},
    {'name': 'oltre.social', 'user': 'u173050672.oltre.social'}
]

FILES_TO_DEPLOY = [
    ('data/acat_community.sqlite', 'data/acat_community.sqlite'),
    ('mappa-club.php', 'mappa-club.php'),
    ('_dependex-world-map.php', '_dependex-world-map.php'),
    ('assets/js/dependex-world-map.js', 'assets/js/dependex-world-map.js'),
]

for t in TARGETS:
    name = t['name']
    user = t['user']
    print(f"\n==========================================")
    print(f"DEPLOYING TO {name} ({user})")
    print(f"==========================================")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=90)
        ftp.login(user, FTP_PASS)
        ftp.set_pasv(True)
        
        for local_p, remote_p in FILES_TO_DEPLOY:
            if os.path.exists(local_p):
                file_size = os.path.getsize(local_p)
                print(f"Uploading {local_p} ({file_size} bytes) -> {remote_p} ...")
                with open(local_p, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_p}', f)
                print(f" [OK] {remote_p} uploaded successfully.")
            else:
                print(f" [MISSING] {local_p}")
                
        ftp.quit()
        print(f"Deploy to {name} complete!")
    except Exception as e:
        print(f"ERROR deploying to {name}: {e}")

print("\n=== VERIFYING LIVE HTTP ENDPOINTS ===")
for domain in ['https://dependex.social', 'https://oltre.social']:
    url = f"{domain}/mappa-club.php"
    print(f"Testing {url} ...")
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        with urllib.request.urlopen(req, timeout=15) as resp:
            content = resp.read().decode('utf-8', errors='ignore')
            print(f"  HTTP {resp.status} - Content Length: {len(content)} bytes")
            
            # Check Taglio di Po
            if 'Taglio di Po' in content:
                print("  [PASS] 'Taglio di Po' present")
            if 'Club CAT \\"Edera\\"' in content or 'Club CAT "Edera"' in content or 'Edera' in content:
                print("  [PASS] 'Edera' present")
            if 'ACAT Basso Polesine' in content:
                print("  [PASS] 'ACAT Basso Polesine' present")
            if 'marker-acat' in content and 'marker-local' in content:
                print("  [PASS] Distinct marker classes present in HTML")
            
            # Check Ischia Piemonte absence
            has_ischia_piemonte = bool(re.search(r'Piemonte.*41\.14', content))
            print(f"  [CHECK] Piemonte at 41.14: {has_ischia_piemonte} (should be False)")
            
    except Exception as e:
        print(f"  ERROR testing {url}: {e}")
