import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cursor = conn.cursor()

print("--- PIEMONTE IN dependex_world_registry ---")
cursor.execute("SELECT id, sic_id, entity_name, network_level, region, city, latitude, longitude FROM dependex_world_registry WHERE entity_name LIKE '%Piemonte%'")
for r in cursor.fetchall():
    print(r)

print("\n--- TAGLIO DI PO IN dependex_world_registry ---")
cursor.execute("SELECT id, sic_id, entity_name, network_level, region, city, address, latitude, longitude FROM dependex_world_registry WHERE city LIKE '%Taglio di Po%' OR address LIKE '%Taglio di Po%' OR entity_name LIKE '%Taglio di Po%' OR entity_name LIKE '%Basso Polesine%'")
for r in cursor.fetchall():
    print(r)

print("\n--- L'AQUILA / ABRUZZO COORDINATES IN dependex_world_registry ---")
cursor.execute("SELECT id, sic_id, entity_name, network_level, region, city, latitude, longitude FROM dependex_world_registry WHERE latitude BETWEEN 42.35 AND 42.36 AND longitude BETWEEN 13.39 AND 13.41")
for r in cursor.fetchall():
    print(r)
