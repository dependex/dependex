import ftplib
import socket

hosts = ["ftp.dependex.social", "dependex.social", "ftp.mircopregnolato.it", "ftp.cittadeldelta.eu"]
users = [
    "u173050672.dependex.social", 
    "u173050672-dependex", 
    "u173050672", 
    "u173050672.mircopregnolato.it"
]
pwd = "h29031976T."

for h in hosts:
    print(f"\n--- Checking {h} ---")
    try:
        ip = socket.gethostbyname(h)
        print(f"Resolved {h} -> {ip}")
    except Exception as e:
        print(f"Could not resolve {h}: {e}")
        continue

    for u in users:
        try:
            ftp = ftplib.FTP(timeout=5)
            ftp.connect(h, 21)
            ftp.login(u, pwd)
            print(f"SUCCESS: {h} | User: {u}")
            nl = ftp.nlst()
            print(f"Listing: {nl}")
            ftp.quit()
            break
        except Exception as e:
            print(f"Failed {h} with {u}: {e}")
