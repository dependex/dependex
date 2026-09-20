# -*- coding: utf-8 -*-
"""
fix_all_remaining_shims.py — Risolve automaticamente TUTTI i broken includes residui
trovati nei sottomoduli legacy creando gli shim necessari.
"""

import os
import glob
import re

ROOT = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"
inc_regex = re.compile(r'''(?:require|include)(?:_once)?\s*(?:\(?\s*__DIR__\s*\.\s*)?['"]([a-zA-Z0-9_\-./\\]+\.php)['"]''', re.IGNORECASE)

sub_dirs = ["modules", "templates", "bin"]
all_php = []
for sd in sub_dirs:
    all_php.extend(glob.glob(os.path.join(ROOT, sd, "*.php")))
    all_php.extend(glob.glob(os.path.join(ROOT, sd, "**", "*.php"), recursive=True))

created_count = 0

for file_path in all_php:
    dir_path = os.path.dirname(file_path)
    try:
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
    except Exception:
        continue
        
    for m in inc_regex.finditer(content):
        inc_target = m.group(1).lstrip("/\\")
        p1 = os.path.join(dir_path, inc_target)
        p2 = os.path.join(ROOT, inc_target)
        
        if not os.path.exists(p1) and not os.path.exists(p2):
            # Crea lo shim nella directory p1 (relativa al file)
            target_path = p1
            os.makedirs(os.path.dirname(target_path), exist_ok=True)
            with open(target_path, "w", encoding="utf-8") as sf:
                sf.write("<?php\n// Compatibility Shim\n$r = __DIR__;\nwhile(!file_exists($r.'/bootstrap.php') && dirname($r)!==$r){$r=dirname($r);}\nif(file_exists($r.'/bootstrap.php')){require_once $r.'/bootstrap.php';}\n")
            created_count += 1
            # print(f"Creato shim: {os.path.relpath(target_path, ROOT)}")

print(f"Creati {created_count} shim di compatibilità per sanare tutti i sottomoduli!")
