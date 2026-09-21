import ftplib
import os
import time
import urllib.request

FTP_HOST = 'ftp.dependex.social'
FTP_PASS = 'h29031976T.'

FILES = [
    ('_header.php', '_header.php'),
    ('_footer.php', '_footer.php'),
    ('mappa-club.php', 'mappa-club.php'),
    ('pwa.php', 'pwa.php'),
    ('api-live-stats.php', 'api-live-stats.php'),
    ('world-club-explorer.php', 'world-club-explorer.php'),
    ('piramide-rovesciata.php', 'piramide-rovesciata.php'),
    ('organigramma.php', 'organigramma.php'),
    ('api-organigramma.php', 'api-organigramma.php'),
    ('assets/css/app.css', 'assets/css/app.css'),
    ('assets/css/rainbow-neon.css', 'assets/css/rainbow-neon.css'),
    ('data/acat_community.sqlite', 'data/acat_community.sqlite'),
    ('api/live-stats.php', 'api/live-stats.php'),
    ('api/clubs.php', 'api/clubs.php'),
    ('api/organigramma.php', 'api/organigramma.php'),
]

print("=== Upload su oltre.social con PASV mode e retry ===")
for attempt in range(3):
    try:
        ftp = ftplib.FTP()
        ftp.connect(FTP_HOST, 21, timeout=30)
        ftp.login('u173050672.oltre.social', FTP_PASS)
        ftp.set_pasv(True)
        
        for sub in ['api', 'data', 'assets', 'assets/css']:
            try:
                ftp.mkd(sub)
            except Exception:
                pass
                
        for local_p, remote_p in FILES:
            if os.path.exists(local_p):
                with open(local_p, 'rb') as f:
                    ftp.storbinary(f'STOR {remote_p}', f)
                print(f" [OK oltre.social] {remote_p}")
                
        ftp.quit()
        print("Upload su oltre.social completato con successo!")
        break
    except Exception as e:
        print(f"Tentativo {attempt+1} fallito ({e}), riprovo tra 3 secondi...")
        time.sleep(3)

print("\n=== VERIFICA HTTP LIVE SU https://dependex.social/ ===")
req = urllib.request.Request('https://dependex.social/', headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req, timeout=15) as resp:
    html = resp.read().decode('utf-8', errors='ignore')
    print('Home status:', resp.status)
    print('1. Live counter visits:', 'dxTotalVisits' in html)
    print('2. Live counter online:', 'dxLiveUsers' in html)
    print('3. Burger button present:', 'id="burgerBtn"' in html)
    print('4. Footer 4col present:', 'footer-4col-grid' in html)
    print('5. PWA in drawer:', 'pwa.php' in html)
    print('6. Italy map in drawer:', 'italy-map.php' in html)
    print('7. Trova club in drawer:', 'trova-club.php' in html)

print("\n=== VERIFICA API LIVE STATS ===")
req_api = urllib.request.Request('https://dependex.social/api/live-stats.php', headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req_api, timeout=10) as resp:
    body = resp.read().decode('utf-8')
    print('API response:', body)
