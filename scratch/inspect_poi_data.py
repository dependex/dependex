import sqlite3
import json

db_path = 'data/acat_community.sqlite'
conn = sqlite3.connect(db_path)
conn.row_factory = sqlite3.Row
cursor = conn.cursor()

print("--- TABLES IN DATABASE ---")
cursor.execute("SELECT name FROM sqlite_master WHERE type='table'")
tables = [r[0] for r in cursor.fetchall()]
print("Tables:", tables)

for t in tables:
    cursor.execute(f"SELECT count(*) FROM {t}")
    print(f"  {t}: {cursor.fetchone()[0]} rows")

print("\n--- TAGLIO DI PO RECORDS BEFORE ---")
for t in ['cat_clubs_italy', 'crm_club_contacts', 'dependex_world_registry']:
    if t in tables:
        print(f"\nTable: {t}")
        try:
            cursor.execute(f"SELECT id, name, level, city, address, lat, lon FROM {t} WHERE city LIKE '%Taglio di Po%' OR address LIKE '%Taglio di Po%' OR name LIKE '%Basso Polesine%' OR name LIKE '%Edera%'")
            for r in cursor.fetchall():
                print(dict(r))
        except Exception as e:
            print("Error query:", e)

print("\n--- PIEMONTE ANOMALIES ---")
cursor.execute("SELECT id, name, level, city, address, lat, lon FROM cat_clubs_italy WHERE name LIKE '%Piemonte%'")
for r in cursor.fetchall():
    print(dict(r))
