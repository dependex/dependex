import ftplib
import urllib.request

FTP_HOST = 'ftp.dependex.social'
FTP_PASS = 'h29031976T.'

for target, user in [('dependex.social', 'u173050672.dependex.social'), ('oltre.social', 'u173050672.oltre.social')]:
    print(f"Upload su {target}...")
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, 21, timeout=30)
    ftp.login(user, FTP_PASS)
    ftp.set_pasv(True)
    with open('piramide-rovesciata.php', 'rb') as f:
        ftp.storbinary('STOR piramide-rovesciata.php', f)
    with open('organigramma.php', 'rb') as f:
        ftp.storbinary('STOR organigramma.php', f)
    ftp.quit()
    print(f"Completato {target}!")

req = urllib.request.Request('https://dependex.social/piramide-rovesciata.php', headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    c = resp.read().decode('utf-8')
    print("\nVerifica live dependex.social/piramide-rovesciata.php:")
    print("1. btnModeStar presente:", 'id="btnModeStar"' in c)
    print("2. Vista a Stella etichetta:", 'Vista a Stella' in c)
    print("3. Classe tree-star-mode presente:", 'tree-star-mode' in c)
