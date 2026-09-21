import ftplib
import os

FTP_HOST = "ftp.dependex.social"
FTP_PASS = "h29031976T."

TARGETS = [
    {"name": "dependex.social", "user": "u173050672.dependex.social"},
    {"name": "oltre.social", "user": "u173050672.oltre.social"}
]

FILES = [
    ("piramide-rovesciata.php", "piramide-rovesciata.php"),
    ("organigramma.php", "organigramma.php"),
    ("api-organigramma.php", "api-organigramma.php"),
    ("_header.php", "_header.php"),
    ("_footer.php", "_footer.php"),
    ("data/acat_community.sqlite", "data/acat_community.sqlite"),
    # Aliases
    ("trova-club.php", "trova-club.php"),
    ("italy-map.php", "italy-map.php"),
    ("domande.php", "domande.php"),
    ("testimonianze.php", "testimonianze.php"),
    ("contatti.php", "contatti.php"),
    ("eventi.php", "eventi.php"),
    ("life-playground.php", "life-playground.php"),
    ("mappa-benessere.php", "mappa-benessere.php"),
    ("ruota-vita.php", "ruota-vita.php"),
    ("maslow.php", "maslow.php"),
    ("guida-famiglia.php", "guida-famiglia.php"),
    ("corso-taglio-po.php", "corso-taglio-po.php"),
    ("clip-motivazionali.php", "clip-motivazionali.php"),
    ("libri-kdp.php", "libri-kdp.php"),
    ("cookie.php", "cookie.php"),
    ("termini.php", "termini.php"),
    ("emergenze.php", "emergenze.php"),
]

for t in TARGETS:
    print(f"Uploading files to {t['name']}...")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(t['user'], FTP_PASS)
    
    # Crea cartella api se non esiste sul server remoto
    try:
        ftp.mkd("api")
    except Exception:
        pass
        
    for local_p, remote_p in FILES:
        if os.path.exists(local_p):
            with open(local_p, 'rb') as f:
                ftp.storbinary(f"STOR {remote_p}", f)
                print(f" -> Uploaded {remote_p}")

    # Upload file dentro api/
    for api_file in ["organigramma.php", "clubs.php"]:
        local_api = f"api/{api_file}"
        remote_api = f"api/{api_file}"
        if os.path.exists(local_api):
            with open(local_api, 'rb') as f:
                ftp.storbinary(f"STOR {remote_api}", f)
                print(f" -> Uploaded {remote_api}")

    ftp.quit()
    print(f"Done {t['name']}\n")

print("Deploy completato con successo su tutti i server!")
