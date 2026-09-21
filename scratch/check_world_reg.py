import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
c = conn.cursor()

c.execute("PRAGMA table_info(dependex_world_registry)")
cols = [row[1] for row in c.fetchall()]
print("Columns in dependex_world_registry:", cols)

c.execute("SELECT * FROM dependex_world_registry WHERE entity_name LIKE '%AICAT%'")
rows = c.fetchall()
print(f"\nFound {len(rows)} matching AICAT in dependex_world_registry:")
for r in rows:
    print(r)

conn.close()
