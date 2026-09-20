import sqlite3

conn = sqlite3.connect(r"c:\81PLUS_GLOBAL_MASTER\dependex.social\data\acat_community.sqlite")
conn.row_factory = sqlite3.Row
cur = conn.cursor()

print("--- RICERCA IN cat_clubs_italy ---")
cur.execute("SELECT entity_name, level, city, province, address, phone, email, website, asl_serd_reference FROM cat_clubs_italy WHERE UPPER(province) IN ('RO', 'FE', 'ROVIGO', 'FERRARA') OR city LIKE '%Rovigo%' OR city LIKE '%Ferrara%' OR city LIKE '%Adria%' OR city LIKE '%Porto Viro%' OR city LIKE '%Comacchio%' OR city LIKE '%Cento%'")
rows = cur.fetchall()
print(f"Trovati in cat_clubs_italy: {len(rows)}")
for r in rows:
    print(dict(r))

conn.close()
