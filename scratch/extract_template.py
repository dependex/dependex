# -*- coding: utf-8 -*-
import os

mp_dir = r"c:\81PLUS_GLOBAL_MASTER\mircopregnolato.it"
percorsi_file = os.path.join(mp_dir, "percorsi.html")

with open(percorsi_file, "r", encoding="utf-8", errors="ignore") as f:
    text = f.read()

idx = text.find('id="percorsi-sistemici"')
idx_end = text.find('</section>', idx)
print(text[idx_end-800:idx_end])






