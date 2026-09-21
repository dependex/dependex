import os

ALIASES = {
    'trova-club.php': 'world-club-explorer.php',
    'italy-map.php': 'mappa-club.php',
    'domande.php': 'domande-frequenti.php',
    'testimonianze.php': 'recensioni.php',
    'contatti.php': 'parla-con-noi.php',
    'eventi.php': 'events-public.php',
    'life-playground.php': 'playground.php',
    'mappa-benessere.php': 'orientamento.php',
    'ruota-vita.php': 'ruota-della-vita.php',
    'maslow.php': 'piramide-maslow.php',
    'guida-famiglia.php': 'guida-gratuita.php',
    'corso-taglio-po.php': 'evento-ottobre-taglio-di-po.php',
    'clip-motivazionali.php': 'clips.php',
    'libri-kdp.php': 'offers.php',
    'cookie.php': 'privacy-center.php',
    'termini.php': 'terms.php',
    'emergenze.php': 'help.php',
}

BASE_DIR = 'c:/81PLUS_GLOBAL_MASTER/dependex.social'

for alias, target in ALIASES.items():
    path = os.path.join(BASE_DIR, alias)
    content = f"""<?php
header("HTTP/1.1 301 Moved Permanently");
header("Location: /{target}");
exit;
"""
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Creato alias {alias} -> {target}")

print("Tutti gli alias generati con successo.")
