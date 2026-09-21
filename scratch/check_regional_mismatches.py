import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
conn.row_factory = sqlite3.Row
cursor = conn.cursor()

# Region approximate bounding boxes [min_lat, max_lat, min_lon, max_lon]
REGION_BOUNDS = {
    'Abruzzo': (41.7, 42.9, 13.0, 14.8),
    'Basilicata': (39.9, 41.2, 15.3, 16.9),
    'Calabria': (37.9, 40.2, 15.6, 17.2),
    'Campania': (39.9, 41.6, 13.9, 15.8),
    'Emilia-Romagna': (43.7, 45.1, 9.2, 12.8),
    'Friuli-Venezia Giulia': (45.5, 46.7, 12.3, 13.9),
    'Lazio': (41.2, 42.9, 11.4, 14.0),
    'Liguria': (43.7, 44.7, 7.5, 10.1),
    'Lombardia': (44.6, 46.7, 8.4, 11.5),
    'Marche': (42.6, 44.0, 12.1, 14.0),
    'Molise': (41.3, 42.1, 13.9, 15.2),
    'Piemonte': (44.0, 46.5, 6.6, 9.3),
    'Puglia': (39.7, 42.0, 14.9, 18.6),
    'Sardegna': (38.8, 41.4, 8.1, 9.9),
    'Sicilia': (36.6, 38.4, 12.3, 15.7),
    'Toscana': (42.2, 44.5, 9.6, 12.4),
    'Trentino-Alto Adige': (45.6, 47.1, 10.4, 12.5),
    'Umbria': (42.3, 43.6, 11.8, 13.0),
    "Valle d'Aosta": (45.4, 46.0, 6.7, 7.9),
    'Veneto': (44.7, 46.7, 10.6, 13.1)
}

cursor.execute("SELECT id, sic_id, entity_name, level, region, city, latitude, longitude FROM cat_clubs_italy")
clubs = cursor.fetchall()

mismatches = []
for c in clubs:
    reg = c['region']
    lat = c['latitude']
    lon = c['longitude']
    if not reg or lat is None or lon is None:
        continue
    bounds = REGION_BOUNDS.get(reg)
    if not bounds:
        continue
    min_lat, max_lat, min_lon, max_lon = bounds
    if lat < min_lat or lat > max_lat or lon < min_lon or lon > max_lon:
        mismatches.append(c)

print(f"Total region/coordinate mismatches: {len(mismatches)}")
for m in mismatches:
    print(f"[{m['id']}] {m['region']} | {m['city']} | lat={m['latitude']}, lon={m['longitude']} | {m['entity_name']}")
