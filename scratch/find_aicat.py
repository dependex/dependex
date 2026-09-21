import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
cursor = conn.cursor()

cursor.execute("PRAGMA table_info(cat_clubs_italy)")
cols1 = [c[1] for c in cursor.fetchall()]
print("cat_clubs_italy columns:", cols1)

cursor.execute("PRAGMA table_info(crm_club_contacts)")
cols2 = [c[1] for c in cursor.fetchall()]
print("crm_club_contacts columns:", cols2)

print("\n=== cat_clubs_italy matching AICAT ===")
cursor.execute("SELECT id, entity_name, region, province, city, email, phone FROM cat_clubs_italy WHERE entity_name LIKE '%AICAT%' OR email LIKE '%aicat%'")
for r in cursor.fetchall():
    print(r)

print("\n=== crm_club_contacts matching AICAT ===")
cursor.execute("SELECT id, club_name, region, province, city, email, phone FROM crm_club_contacts WHERE club_name LIKE '%AICAT%' OR email LIKE '%aicat%'")
for r in cursor.fetchall():
    print(r)

conn.close()
