import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
c = conn.cursor()

print("ALL records with 'AICAT' in entity_name:")
c.execute("SELECT id, sic_id, entity_name, level, region, province, city FROM cat_clubs_italy WHERE entity_name LIKE '%AICAT%' ORDER BY id")
rows = c.fetchall()
for r in rows:
    print(r)

print(f"\nTotal count: {len(rows)}")

conn.close()
