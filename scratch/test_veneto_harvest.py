import json
import sqlite3

con = sqlite3.connect('data/acat_community.sqlite')
cur = con.cursor()

with open('scratch/veneto_data_club.json', 'r', encoding='utf-8') as f:
    veneto_clubs = json.load(f)

with open('scratch/veneto_data_acat.json', 'r', encoding='utf-8') as f:
    veneto_acats = json.load(f)

print(f"File Veneto: {len(veneto_clubs)} club e {len(veneto_acats)} ACAT")

cur.execute("SELECT sic_id, entity_name, city, address FROM cat_clubs_italy WHERE region = 'Veneto'")
existing_veneto = cur.fetchall()
print(f"Già presenti nel DB per il Veneto: {len(existing_veneto)}")

existing_names = set(r[1].lower().strip() for r in existing_veneto)
existing_cities = set(r[2].lower().strip() for r in existing_veneto if r[2])

new_clubs = 0
for c in veneto_clubs:
    c_name = c['name'].lower().strip()
    c_city = c['city'].lower().strip()
    # check match
    match = any(e[1].lower().strip() == c_name and e[2].lower().strip() == c_city for e in existing_veneto)
    if not match:
        new_clubs += 1

print(f"Nuovi Club in Veneto pronti da integrare: {new_clubs} su {len(veneto_clubs)}!")
