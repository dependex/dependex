import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

cur.execute("UPDATE dependex_world_registry SET email = 'info@dependex.support' WHERE email IS NULL OR email = ''")
cur.execute("UPDATE dependex_world_registry SET phone = '+39 800 974250' WHERE phone IS NULL OR phone = ''")
conn.commit()

cur.execute("SELECT count(*) FROM dependex_world_registry WHERE email IS NULL OR email = ''")
print("Remaining missing emails in dependex_world_registry:", cur.fetchone()[0])
conn.close()
