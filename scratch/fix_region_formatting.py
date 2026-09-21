import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

STD_REGIONS = [
    'Abruzzo', 'Basilicata', 'Calabria', 'Campania', 'Emilia-Romagna',
    'Friuli-Venezia Giulia', 'Lazio', 'Liguria', 'Lombardia', 'Marche',
    'Molise', 'Piemonte', 'Puglia', 'Sardegna', 'Sicilia', 'Toscana',
    'Trentino-Alto Adige', 'Umbria', "Valle d'Aosta", 'Veneto'
]

# Map stripped versions
NORM_MAP = {}
for r in STD_REGIONS:
    stripped = r.replace('-', '').replace("'", "").replace(' ', '').lower()
    NORM_MAP[stripped] = r

# Also handle common variations
NORM_MAP['emiliaromagna'] = 'Emilia-Romagna'
NORM_MAP['friuliveneziagiulia'] = 'Friuli-Venezia Giulia'
NORM_MAP['trentinoaltoadige'] = 'Trentino-Alto Adige'
NORM_MAP['valledaosta'] = "Valle d'Aosta"

tables = ['cat_clubs_italy', 'crm_club_contacts', 'dependex_world_registry']

for t in tables:
    cur.execute(f"SELECT id, region FROM {t}")
    rows = cur.fetchall()
    for row_id, reg in rows:
        if not reg:
            continue
        stripped = reg.replace('-', '').replace("'", "").replace(' ', '').lower()
        if stripped in NORM_MAP:
            proper = NORM_MAP[stripped]
            if proper != reg:
                cur.execute(f"UPDATE {t} SET region = ? WHERE id = ?", (proper, row_id))

conn.commit()

# Also let's check entity_name if any have spaced out letters
for t in tables:
    cur.execute(f"SELECT id, entity_name FROM {t}")
    rows = cur.fetchall()
    for row_id, name in rows:
        if name and ' ' in name:
            # Check if name is like "A L C O L I S T I"
            words = name.split()
            # If majority of words are single characters
            single_chars = sum(1 for w in words if len(w) == 1)
            if len(words) > 5 and single_chars / len(words) > 0.6:
                # Reconstruct word
                fixed_name = "".join(words)
                cur.execute(f"UPDATE {t} SET entity_name = ? WHERE id = ?", (fixed_name, row_id))

conn.commit()

print("Regional distribution after normalization in cat_clubs_italy:")
for row in cur.execute("SELECT region, count(*) FROM cat_clubs_italy GROUP BY region ORDER BY count(*) DESC").fetchall():
    print(f"  {row[0]}: {row[1]}")

conn.close()
