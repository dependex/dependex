import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cursor = conn.cursor()

print("--- COLUMNS IN cat_clubs_italy ---")
cursor.execute("PRAGMA table_info(cat_clubs_italy)")
cols = [c[1] for c in cursor.fetchall()]
print(cols)

print("\n--- COLUMNS IN dependex_world_registry ---")
cursor.execute("PRAGMA table_info(dependex_world_registry)")
cols_dwr = [c[1] for c in cursor.fetchall()]
print(cols_dwr)

print("\n--- TAGLIO DI PO IN cat_clubs_italy ---")
cursor.execute("""
    SELECT id, sic_id, entity_name, level, city, address, latitude, longitude 
    FROM cat_clubs_italy 
    WHERE city LIKE '%Taglio di Po%' 
       OR address LIKE '%Taglio di Po%' 
       OR entity_name LIKE '%Taglio di Po%' 
       OR entity_name LIKE '%Basso Polesine%' 
       OR entity_name LIKE '%Edera%'
""")
for r in cursor.fetchall():
    print(r)

print("\n--- TAGLIO DI PO IN dependex_world_registry ---")
cursor.execute("""
    SELECT id, sic_id, entity_name, network_level, city, address, latitude, longitude 
    FROM dependex_world_registry 
    WHERE city LIKE '%Taglio di Po%' 
       OR address LIKE '%Taglio di Po%' 
       OR entity_name LIKE '%Taglio di Po%' 
       OR entity_name LIKE '%Basso Polesine%' 
       OR entity_name LIKE '%Edera%'
""")
for r in cursor.fetchall():
    print(r)
