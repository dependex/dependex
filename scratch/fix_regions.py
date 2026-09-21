import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
c = conn.cursor()

c.execute("SELECT id, sic_id, entity_name, level, province, city, address FROM cat_clubs_italy WHERE region = 'Italia'")
rows = c.fetchall()
print(f"Record con region='Italia': {len(rows)}")
for r in rows:
    print(r)
