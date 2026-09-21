import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
cursor = conn.cursor()

print("=== crm_club_contacts matching AICAT ===")
cursor.execute("SELECT id, entity_name, region, province, city, primary_email, primary_phone FROM crm_club_contacts WHERE entity_name LIKE '%AICAT%' OR primary_email LIKE '%aicat%'")
for r in cursor.fetchall():
    print(r)

conn.close()
