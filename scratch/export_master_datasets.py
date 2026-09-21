import sqlite3
import csv
import os
import subprocess

DB_PATH = 'data/acat_community.sqlite'
conn = sqlite3.connect(DB_PATH)
conn.row_factory = sqlite3.Row
cur = conn.cursor()

# 1. Export CENSIMENTO_CLUB_CAT_ITALIA_2026.csv
cur.execute("SELECT * FROM cat_clubs_italy ORDER BY id ASC")
rows = cur.fetchall()
if rows:
    columns = [desc[0] for desc in cur.description]
    out_path = 'data/CENSIMENTO_CLUB_CAT_ITALIA_2026.csv'
    with open(out_path, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(columns)
        for r in rows:
            writer.writerow([r[c] for c in columns])
    print(f"Exported {len(rows)} records to {out_path}")

# 2. Export CRM_CLUB_CONTATTI_MASTER_2026.csv
cur.execute("SELECT * FROM crm_club_contacts ORDER BY id ASC")
rows = cur.fetchall()
if rows:
    columns = [desc[0] for desc in cur.description]
    out_path = 'data/CRM_CLUB_CONTATTI_MASTER_2026.csv'
    with open(out_path, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(columns)
        for r in rows:
            writer.writerow([r[c] for c in columns])
    print(f"Exported {len(rows)} records to {out_path}")

# 3. Export DEPENDEX_World_Registry_Master.csv
cur.execute("SELECT * FROM dependex_world_registry ORDER BY id ASC")
rows = cur.fetchall()
if rows:
    columns = [desc[0] for desc in cur.description]
    out_path = 'data/DEPENDEX_World_Registry_Master.csv'
    with open(out_path, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(columns)
        for r in rows:
            writer.writerow([r[c] for c in columns])
    print(f"Exported {len(rows)} records to {out_path}")

conn.close()

# 4. Run sitemap generation
print("Regenerating sitemap-clubs.xml...")
subprocess.run(['python', 'scratch/generate_sitemap_clubs.py'], check=True)
