import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cursor = conn.cursor()

# Check total clubs
cursor.execute("SELECT count(*) FROM cat_clubs_italy")
total = cursor.fetchone()[0]
print(f"Total clubs in cat_clubs_italy: {total}")

# Check Taglio di Po
cursor.execute("SELECT id, sic_id, entity_name, level, city, address, latitude, longitude FROM cat_clubs_italy WHERE city = 'Taglio di Po'")
taglio_clubs = cursor.fetchall()
print(f"\nClubs in Taglio di Po: {len(taglio_clubs)}")
for c in taglio_clubs:
    print(" ", c)

# Check Ischia
cursor.execute("SELECT id, sic_id, entity_name, level, city, address, latitude, longitude FROM cat_clubs_italy WHERE city LIKE '%Ischia%' OR (latitude BETWEEN 40.70 AND 40.76 AND longitude BETWEEN 13.85 AND 14.00)")
ischia = cursor.fetchall()
print(f"\nEntities on Ischia island: {len(ischia)}")
for c in ischia:
    print(" ", c)

# Check Piemonte
cursor.execute("SELECT id, sic_id, entity_name, level, city, address, latitude, longitude FROM cat_clubs_italy WHERE entity_name LIKE '%Piemonte%'")
piemonte = cursor.fetchall()
print(f"\nPiemonte entities: {len(piemonte)}")
for c in piemonte:
    print(" ", c)

# Check breakdown by level
cursor.execute("SELECT level, count(*) FROM cat_clubs_italy GROUP BY level ORDER BY count(*) DESC")
levels = cursor.fetchall()
print("\nBreakdown by level:")
for lvl, cnt in levels:
    print(f"  {lvl}: {cnt}")
