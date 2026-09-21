import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
c = conn.cursor()

c.execute('SELECT COUNT(*) FROM cat_clubs_italy')
total_clubs = c.fetchone()[0]

c.execute('SELECT COUNT(*) FROM crm_club_contacts')
total_crm = c.fetchone()[0]

c.execute("SELECT COUNT(DISTINCT region) FROM cat_clubs_italy WHERE region IS NOT NULL AND region != ''")
reg_count = c.fetchone()[0]

c.execute("SELECT SUM(families_count) FROM cat_clubs_italy WHERE level = 'LOCAL_CLUB'")
fam_count = c.fetchone()[0]

print(f"Total in cat_clubs_italy: {total_clubs}")
print(f"Total in crm_club_contacts: {total_crm}")
print(f"Distinct regions: {reg_count}")
print(f"Families count (LOCAL_CLUB): {fam_count}")

# Check any other duplicates or anomalies in national level
c.execute("SELECT id, entity_name, level, region, city FROM cat_clubs_italy WHERE level = 'NATIONAL'")
print("National entities:", c.fetchall())

conn.close()
