import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
conn.row_factory = sqlite3.Row
cursor = conn.cursor()

cursor.execute("""
    SELECT id, sic_id, entity_name, level, region, province, city, address 
    FROM cat_clubs_italy 
    WHERE ROUND(latitude, 2) = 42.5 AND ROUND(longitude, 2) = 12.5
""")
rows = cursor.fetchall()
print(f"Total entities at center placeholder (42.5, 12.5): {len(rows)}")
for r in rows:
    print(dict(r))
