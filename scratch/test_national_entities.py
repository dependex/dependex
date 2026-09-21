import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
c = conn.cursor()

c.execute("SELECT id, sic_id, entity_name, network_level, country, region, website, phone, email, notes FROM dependex_world_registry WHERE id = 24")
print("ID 24:", c.fetchall())

# Impostiamo network_level = 'COUNTRY' per il record paese 'Italy' così non è confuso con un ente operativo
c.execute("UPDATE dependex_world_registry SET network_level = 'COUNTRY' WHERE id = 24 AND entity_name = 'Italy'")
conn.commit()

c.execute("SELECT id, sic_id, entity_name, network_level, country, region FROM dependex_world_registry WHERE network_level IN ('NATIONAL','WORLD','CONTINENT') AND (country='Italy' OR network_level='WORLD' OR entity_name LIKE '%Eurocare%') ORDER BY network_rank DESC, entity_name")
print("\nNuovo elenco Nazionale & Internazionale:")
for r in c.fetchall():
    print(r)

conn.close()
