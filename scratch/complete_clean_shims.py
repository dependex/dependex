# -*- coding: utf-8 -*-
import os

ROOT = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"

# 1. Email machine shims
em_dir = os.path.join(ROOT, "modules", "email-machine")
os.makedirs(em_dir, exist_ok=True)
em_files = [
    "db.php",
    "mailer.php",
    "email-flows-stagionali.php",
    "email-flows-extra.php",
    "email-flows-webinar.php",
    "dr-feste.php",
    "gamification.php"
]
for ef in em_files:
    p = os.path.join(em_dir, ef)
    if not os.path.exists(p):
        with open(p, "w", encoding="utf-8") as f:
            f.write("<?php require_once dirname(__DIR__, 2) . '/bootstrap.php';\n")

# 2. Mailer in root
if not os.path.exists(os.path.join(ROOT, "mailer.php")):
    with open(os.path.join(ROOT, "mailer.php"), "w", encoding="utf-8") as f:
        f.write("<?php require_once __DIR__ . '/bootstrap.php';\n")

# 3. dr-network-struttura.php e network-engine.php in root
for f in ["dr-network-struttura.php", "network-engine.php"]:
    p = os.path.join(ROOT, f)
    if not os.path.exists(p):
        with open(p, "w", encoding="utf-8") as fp:
            fp.write("<?php require_once __DIR__ . '/bootstrap.php';\n")

print("Tutti gli shim aggiuntivi sono pronti!")
