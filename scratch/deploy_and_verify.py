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

FILES = [
    ('mappa-club.php', 'mappa-club.php'),
    ('_footer.php', '_footer.php'),
    ('_header.php', '_header.php'),
    ('pwa.php', 'pwa.php'),
    ('api-live-stats.php', 'api-live-stats.php'),
    ('world-club-explorer.php', 'world-club-explorer.php'),
    ('piramide-rovesciata.php', 'piramide-rovesciata.php'),
    ('organigramma.php', 'organigramma.php'),
    ('api-organigramma.php', 'api-organigramma.php'),
    ('assets/css/app.css', 'assets/css/app.css'),
    ('assets/css/rainbow-neon.css', 'assets/css/rainbow-neon.css'),
    ('data/acat_community.sqlite', 'data/acat_community.sqlite'),
]

API_FILES = [
    ('api/live-stats.php', 'api/live-stats.php'),
    ('api/clubs.php', 'api/clubs.php'),
    ('api/organigramma.php', 'api/organigramma.php'),
]

for t in TARGETS:
    name = t['name']
    user = t['user']
    print(f"=== Connessione a {name} ({user}) ===")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(user, FTP_PASS)
    
    for sub in ['api', 'data', 'assets', 'assets/css']:
        try:
            ftp.mkd(sub)
        except Exception:
            pass
            
    for local_p, remote_p in FILES:
        if os.path.exists(local_p):
            with open(local_p, 'rb') as f:
                ftp.storbinary(f'STOR {remote_p}', f)
            print(f" [OK] {local_p} -> {remote_p}")
            
    for local_p, remote_p in API_FILES:
        if os.path.exists(local_p):
            with open(local_p, 'rb') as f:
                ftp.storbinary(f'STOR {remote_p}', f)
            print(f" [OK] {local_p} -> {remote_p}")
            
    ftp.quit()
    print(f"Deploy completato per {name}!\n")

# Verifica live
print("=== VERIFICA HTTP LIVE SU https://dependex.social/ ===")
req = urllib.request.Request('https://dependex.social/', headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req, timeout=15) as resp:
    html = resp.read().decode('utf-8', errors='ignore')
    print('Home status:', resp.status)
    print('Live counter visits:', 'dxTotalVisits' in html)
    print('Live counter online:', 'dxLiveUsers' in html)
    print('Burger button present:', 'id="burgerBtn"' in html)
    print('Footer 4col present:', 'footer-4col-grid' in html)
    print('PWA in drawer:', 'pwa.php' in html)
    print('Italy map in drawer:', 'italy-map.php' in html)
    print('Trova club in drawer:', 'trova-club.php' in html)

print("\n=== VERIFICA API LIVE STATS ===")
req_api = urllib.request.Request('https://dependex.social/api/live-stats.php', headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req_api, timeout=10) as resp:
    body = resp.read().decode('utf-8')
    print('API response:', body)
