import sqlite3
import json

con = sqlite3.connect('data/acat_community.sqlite')
con.row_factory = sqlite3.Row
cur = con.cursor()

res = cur.execute("SELECT entity_name, region, province, city, phone, email, website FROM cat_clubs_italy WHERE entity_name LIKE '%APCAT%' OR notes LIKE '%APCAT%'").fetchall()
print(f"Total APCAT in cat_clubs_italy: {len(res)}")
for r in res:
    print(dict(r))

res_world = cur.execute("SELECT name, region, city, address, phone, email FROM dependex_world_registry WHERE name LIKE '%APCAT%'").fetchall()
print(f"\nTotal APCAT in dependex_world_registry: {len(res_world)}")
for r in res_world:
    print(dict(r))
