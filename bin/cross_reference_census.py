import csv
import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
c = conn.cursor()

c.execute("SELECT COUNT(*) FROM network_entities")
ne_cnt = c.fetchone()[0]
print(f"Total rows in network_entities: {ne_cnt}")

c.execute("SELECT sic_id, entity_name FROM network_entities")
ne_dict = {row[0]: row[1] for row in c.fetchall()}

with open('data/ACAT_Italia_Club_Census_V1.csv', 'r', encoding='utf-8', errors='ignore') as f:
    reader = csv.DictReader(f)
    csv_rows = list(reader)

found_in_ne = sum(1 for r in csv_rows if (r.get('sic_id') or r.get('\ufeffsic_id')) in ne_dict)
print(f"Matches in network_entities by sic_id: {found_in_ne}/{len(csv_rows)}")

c.execute("SELECT LOWER(entity_name) FROM dependex_world_registry")
dwr_names = set(row[0] for row in c.fetchall())

found_by_name = sum(1 for r in csv_rows if r.get('entity_name', '').lower() in dwr_names)
print(f"Matches in dependex_world_registry by entity_name: {found_by_name}/{len(csv_rows)}")
