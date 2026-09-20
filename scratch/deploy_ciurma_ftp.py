# -*- coding: utf-8 -*-
import os
import ftplib
import sys

FTP_HOST = "ftp.mircopregnolato.it"
FTP_USER = "u173050672.mircopregnolato.it"
FTP_PASS = "h29031976T."

ROOT_DIR = r"c:\81PLUS_GLOBAL_MASTER\mircopregnolato.it"

FILES_TO_UPLOAD = [
    "ciurma.html",
    "percorsi.html"
]

print("Connessione FTP a:", FTP_HOST)
try:
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, 21, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    print("Login FTP effettuato con successo!")

    # Cerchiamo la cartella pubblica se necessario (es. public_html o /)
    dirs = ftp.nlst()
    print("Directory root remota:", dirs)
    if "public_html" in dirs:
        ftp.cwd("public_html")
        print("CWD su public_html")

    for fname in FILES_TO_UPLOAD:
        local_path = os.path.join(ROOT_DIR, fname)
        if os.path.exists(local_path):
            with open(local_path, "rb") as fp:
                print(f"Caricamento {fname} ({os.path.getsize(local_path)} bytes)...")
                ftp.storbinary(f"STOR {fname}", fp)
                print(f" -> {fname} caricato con successo!")
        else:
            print(f"File non trovato: {local_path}")

    ftp.quit()
    print("Deploy FTP completato!")
except Exception as e:
    print("Errore FTP:", e)
