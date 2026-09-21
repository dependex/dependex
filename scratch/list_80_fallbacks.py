import sqlite3
import re

conn = sqlite3.connect('data/acat_community.sqlite')
conn.row_factory = sqlite3.Row
cursor = conn.cursor()

cursor.execute("""
    SELECT id, sic_id, entity_name, level, region, province, city, address 
    FROM cat_clubs_italy 
    WHERE ROUND(latitude, 2) = 42.5 AND ROUND(longitude, 2) = 12.5
    ORDER BY region, city, entity_name
""")
rows = cursor.fetchall()
print(f"Total: {len(rows)}")
for r in rows:
    print(f"[{r['id']}] {r['region']} | {r['province']} | {r['city']} | {r['address']} | {r['entity_name']}")
