import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cursor = conn.cursor()

# 1. Ispezione Taglio di Po
print("=== TAGLIO DI PO ===")
cursor.execute("SELECT id, entity_name, level, region, province, city, address, latitude, longitude FROM cat_clubs_italy WHERE city LIKE '%Taglio di Po%' OR entity_name LIKE '%Taglio di Po%' OR entity_name LIKE '%Basso Polesine%' OR entity_name LIKE '%Edera%'")
for row in cursor.fetchall():
    print(row)

# 2. Ispezione Piemonte con coordinate errate (Piemonte lat normale: 44.0 - 46.5, lon: 6.6 - 9.0)
print("\n=== PIEMONTE con coordinate anomale ===")
cursor.execute("SELECT id, entity_name, level, region, province, city, address, latitude, longitude FROM cat_clubs_italy WHERE region = 'Piemonte'")
piemonte_rows = cursor.fetchall()
for r in piemonte_rows:
    lat = float(r[7]) if r[7] else 0
    lon = float(r[8]) if r[8] else 0
    if lat < 44.0 or lat > 46.8 or lon < 6.5 or lon > 9.5:
        print(f"ANOMALO PIEMONTE: id={r[0]}, name={r[1]}, city={r[5]}, addr={r[6]}, lat={lat}, lon={lon}")

# 3. Ischia: cosa c'è a Ischia o in provincia di Napoli o lat ~ 40.7
print("\n=== ISCHIA / CASAMICCIOLA / FORIO / BARANO ===")
cursor.execute("SELECT id, entity_name, level, region, province, city, address, latitude, longitude FROM cat_clubs_italy WHERE city LIKE '%Ischia%' OR address LIKE '%Ischia%' OR entity_name LIKE '%Ischia%'")
for row in cursor.fetchall():
    print(row)

# 4. Ispezione di tutte le entità REGIONAL / AICAT / ARCAT / APCAT
print("\n=== TUTTE LE ARCAT (REGIONAL) ===")
cursor.execute("SELECT id, entity_name, level, region, province, city, address, latitude, longitude FROM cat_clubs_italy WHERE level = 'REGIONAL' OR entity_name LIKE '%ARCAT%'")
for row in cursor.fetchall():
    print(row)

# 5. Ispezione AICAT (NATIONAL)
print("\n=== TUTTE LE AICAT (NATIONAL) ===")
cursor.execute("SELECT id, entity_name, level, region, province, city, address, latitude, longitude FROM cat_clubs_italy WHERE level = 'NATIONAL' OR entity_name LIKE '%AICAT%'")
for row in cursor.fetchall():
    print(row)

# 6. Verifica coordinate fuori dall'Italia (Italia: lat 35.5 - 47.1, lon 6.5 - 18.6)
print("\n=== COORDINATE FUORI DALL'ITALIA O ANOMALE ===")
cursor.execute("SELECT id, entity_name, level, region, province, city, address, latitude, longitude FROM cat_clubs_italy")
all_rows = cursor.fetchall()
out_count = 0
for r in all_rows:
    try:
        lat = float(r[7]) if r[7] else 0
        lon = float(r[8]) if r[8] else 0
        if lat < 35.0 or lat > 47.5 or lon < 6.0 or lon > 19.0:
            print(f"FUORI ITALIA: id={r[0]}, name={r[1]}, region={r[3]}, city={r[5]}, lat={lat}, lon={lon}")
            out_count += 1
    except Exception as e:
        print(f"ERRORE COORD id={r[0]}: {e}")
print(f"Totale fuori Italia: {out_count}")
