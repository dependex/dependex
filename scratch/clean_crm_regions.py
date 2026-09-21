import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
c = conn.cursor()

# Allinea region e province in crm_club_contacts copiandole da cat_clubs_italy tramite sic_id
c.execute("""
    UPDATE crm_club_contacts 
    SET region = (SELECT cat_clubs_italy.region FROM cat_clubs_italy WHERE cat_clubs_italy.sic_id = crm_club_contacts.sic_id),
        province = (SELECT cat_clubs_italy.province FROM cat_clubs_italy WHERE cat_clubs_italy.sic_id = crm_club_contacts.sic_id)
    WHERE sic_id IN (SELECT sic_id FROM cat_clubs_italy)
""")
conn.commit()

c.execute("SELECT DISTINCT region FROM crm_club_contacts ORDER BY region")
regions = c.fetchall()
print(f"Regioni distinte in crm_club_contacts: {len(regions)}")
for r in regions:
    print(r)

conn.close()
