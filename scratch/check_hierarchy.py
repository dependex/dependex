import sqlite3
import json

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
c = conn.cursor()

c.execute("SELECT level, COUNT(*) FROM cat_clubs_italy GROUP BY level")
print("Counts by level:", c.fetchall())

# Verifica quanti hanno parent_sic_id
c.execute("SELECT COUNT(*) FROM cat_clubs_italy WHERE parent_sic_id IS NOT NULL AND parent_sic_id != ''")
print("Has parent_sic_id:", c.fetchone()[0])

# Livelli regionali
c.execute("SELECT id, entity_name, region, province FROM cat_clubs_italy WHERE level = 'REGIONAL'")
arcat = c.fetchall()
print(f"Regional entities ({len(arcat)}):", arcat[:5])

# Esempio gerarchia per una regione (es. Veneto)
c.execute("SELECT level, COUNT(*) FROM cat_clubs_italy WHERE region = 'Veneto' GROUP BY level")
print("Veneto breakdown by level:", c.fetchall())

conn.close()
