import ftplib

FTP_HOST = "ftp.dependex.social"
FTP_PASS = "h29031976T."

TARGETS = [
    {"name": "dependex.social", "user": "u173050672.dependex.social"},
    {"name": "oltre.social", "user": "u173050672.oltre.social"}
]

FILES = [
    ("assets/css/rainbow-neon.css", "assets/css/rainbow-neon.css"),
    ("assets/css/app.css", "assets/css/app.css"),
    ("assets/css/dependex-human-community.css", "assets/css/dependex-human-community.css")
]

for t in TARGETS:
    print(f"Uploading CSS to {t['name']}...")
    ftp = ftplib.FTP(FTP_HOST, timeout=30)
    ftp.login(t['user'], FTP_PASS)
    for local_p, remote_p in FILES:
        with open(local_p, 'rb') as f:
            ftp.storbinary(f"STOR {remote_p}", f)
            print(f" -> Uploaded {remote_p}")
    ftp.quit()
    print(f"Done {t['name']}\n")
