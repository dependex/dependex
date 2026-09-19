# -*- coding: utf-8 -*-
"""
Upload visual image assets to dependex.social and oltre.social via FTP.
"""
import os
import ftplib

FTP_HOST = "ftp.dependex.social"
FTP_PASS = "h29031976T."

TARGETS = [
    {
        "name": "dependex.social",
        "user": "u173050672.dependex.social",
    },
    {
        "name": "oltre.social",
        "user": "u173050672.oltre.social",
    }
]

ROOT_DIR = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"

IMAGE_FILES = [
    os.path.join("assets", "img", "dependex-rainbow-badge.webp"),
    os.path.join("assets", "img", "rainbow-portals.webp"),
    os.path.join("assets", "img", "rainbow-nebula-panorama.webp"),
    os.path.join("assets", "img", "dependex-badge-icon.webp")
]

def ensure_remote_dir(ftp, remote_dir_path):
    parts = [p for p in remote_dir_path.replace("\\", "/").split("/") if p]
    current = ""
    for part in parts:
        current += "/" + part
        try:
            ftp.cwd(current)
        except Exception:
            try:
                ftp.mkd(current)
                ftp.cwd(current)
            except Exception:
                pass
    ftp.cwd("/")

def main():
    for target in TARGETS:
        print(f"\nUploading visual assets to {target['name']}...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(target["user"], FTP_PASS)
        ftp.voidcmd("TYPE I")
        
        for rel_path in IMAGE_FILES:
            local_path = os.path.join(ROOT_DIR, rel_path)
            if not os.path.exists(local_path):
                print(f" [SKIP] Local not found: {rel_path}")
                continue
            remote_path = "/" + rel_path.replace("\\", "/")
            remote_dir = os.path.dirname(remote_path)
            ensure_remote_dir(ftp, remote_dir)
            with open(local_path, "rb") as f:
                ftp.storbinary(f"STOR {remote_path}", f)
            print(f" -> STOR {rel_path} ({os.path.getsize(local_path):,} bytes)... [OK]")
        
        ftp.quit()
        print(f"Done for {target['name']}.")

if __name__ == "__main__":
    main()
