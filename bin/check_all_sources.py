import csv
import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
c = conn.cursor()

c.execute("""
    SELECT sic_id, entity_name, network_level, region, province, city, address, 
           latitude, longitude, phone, email, website, meeting, status, source_type
    FROM dependex_world_registry
    WHERE country='Italy' OR country='IT'
""")
db_italy = c.fetchall()
print(f"Italian entries in dependex_world_registry: {len(db_italy)}")

# Check data/OLTRE_Global_Hudolin_CAT_Network_V1.csv
with open('data/OLTRE_Global_Hudolin_CAT_Network_V1.csv', 'r', encoding='utf-8', errors='ignore') as f:
    reader = csv.DictReader(f)
    oltre_rows = [r for r in reader if (r.get('country') or '').lower() in ['italy', 'italia', 'it']]
print(f"Italian entries in OLTRE_Global_Hudolin_CAT_Network_V1.csv: {len(oltre_rows)}")

# Check data/DEPENDEX_World_Registry_Master.csv
with open('data/DEPENDEX_World_Registry_Master.csv', 'r', encoding='utf-8', errors='ignore') as f:
    reader = csv.DictReader(f)
    master_it = [r for r in reader if (r.get('country') or '').lower() in ['italy', 'italia', 'it']]
print(f"Italian entries in DEPENDEX_World_Registry_Master.csv: {len(master_it)}")
