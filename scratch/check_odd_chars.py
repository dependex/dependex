import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
c = conn.cursor()

c.execute("SELECT id, entity_name, city, address, phone, email FROM cat_clubs_italy WHERE id IN (587, 647, 584, 607)")
for r in c.fetchall():
    print(r)

conn.close()
