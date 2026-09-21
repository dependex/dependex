import sqlite3
import json

conn = sqlite3.connect('data/acat_community.sqlite')
conn.row_factory = sqlite3.Row
cursor = conn.cursor()

print("--- AUDITING cat_clubs_italy FOR ANOMALIES ---")

# 1. Check all regional / national entities
cursor.execute("SELECT id, sic_id, entity_name, level, region, city, address, latitude, longitude FROM cat_clubs_italy WHERE level IN ('REGIONAL', 'NATIONAL')")
print("\nRegional and National entities:")
for r in cursor.fetchall():
    print(dict(r))

# 2. Check coordinates outside Italy bounding box (approx lat 35.4 to 47.1, lon 6.6 to 18.6)
cursor.execute("""
    SELECT id, entity_name, level, city, region, latitude, longitude 
    FROM cat_clubs_italy 
    WHERE latitude < 35.4 OR latitude > 47.5 OR longitude < 6.5 OR longitude > 18.8 OR latitude IS NULL OR longitude IS NULL
""")
out_of_bounds = cursor.fetchall()
print(f"\nOut of bounds / null coordinates: {len(out_of_bounds)}")
for r in out_of_bounds:
    print(dict(r))

# 3. Check clustering on suspicious default coordinates like (42.5, 12.5), (41.87, 12.56), (0,0), etc.
cursor.execute("""
    SELECT latitude, longitude, COUNT(*) as cnt 
    FROM cat_clubs_italy 
    GROUP BY ROUND(latitude, 3), ROUND(longitude, 3) 
    HAVING cnt > 4
    ORDER BY cnt DESC
""")
print("\nCoordinates with >4 entities (potential centroids/placeholders):")
for r in cursor.fetchall():
    print(dict(r))
