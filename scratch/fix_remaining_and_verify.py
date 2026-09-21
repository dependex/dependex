import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cursor = conn.cursor()

# 1. Update the remaining 10 regional coordinate mismatches
updates = [
    (116, 'Lonato del Garda', 'Via Antiche Mura, 2', 'BS', 45.4617, 10.4852),
    (158, 'San Giorgio Bigarello', 'Via Frida Kahlo, 2', 'MN', 45.1633, 10.8406),
    (274, 'Follonica', 'Via Bicocchi, 1', 'GR', 42.9238, 10.7587),
    (275, 'Castelnuovo di Garfagnana', 'Via Garibaldi, 24', 'LU', 44.1132, 10.4079),
    (282, 'Massa', 'Via Cairoli, 1', 'MS', 44.0366, 10.1417),
    (285, 'Sesto Fiorentino', 'Piazza Vittorio Veneto, 1', 'FI', 43.8329, 11.1974),
    (287, 'Montecatini Terme', 'Via Grocco, 1', 'PT', 43.8828, 10.7739),
    (288, 'Viareggio', 'Piazza Nieri e Paolini, 1', 'LU', 43.8667, 10.2333),
    (877, 'Padova', 'Via Tiziano Aspetti, 120 (Arcella)', 'PD', 45.4262, 11.8847),
    (3313, 'Nocera Inferiore', 'Via San Ciro, 1', 'SA', 40.7447, 14.6423),
    # And the 3 remaining 42.5, 12.5 fallbacks
    (2997, 'Acilia', 'Via di Acilia', 'RM', 41.7828, 12.3586),
    (3000, 'Civita Castellana', 'Via Petrarca', 'VT', 42.2961, 12.4144),
    (3007, 'Tivoli', 'Via Cinque Giornate', 'RM', 41.9608, 12.7986)
]

for cid, city, addr, prov, lat, lon in updates:
    cursor.execute("""
        UPDATE cat_clubs_italy SET
            city = ?, address = ?, province = ?, latitude = ?, longitude = ?, geo_accuracy = 'EXACT'
        WHERE id = ?
    """, (city, addr, prov, lat, lon, cid))

# Also sync them to dependex_world_registry by matching id or entity name / city
for cid, city, addr, prov, lat, lon in updates:
    cursor.execute("SELECT sic_id, entity_name FROM cat_clubs_italy WHERE id = ?", (cid,))
    row = cursor.fetchone()
    if row:
        sic_id, name = row
        cursor.execute("""
            UPDATE dependex_world_registry SET
                city = ?, address = ?, province = ?, latitude = ?, longitude = ?, geo_accuracy = 'EXACT'
            WHERE sic_id = ? OR entity_name = ?
        """, (city, addr, prov, lat, lon, sic_id, name))

conn.commit()

print("--- VERIFYING TAGLIO DI PO IN cat_clubs_italy ---")
cursor.execute("SELECT id, sic_id, entity_name, level, city, address, latitude, longitude FROM cat_clubs_italy WHERE city = 'Taglio di Po'")
for r in cursor.fetchall():
    print(r)

print("\n--- VERIFYING TAGLIO DI PO IN dependex_world_registry ---")
cursor.execute("SELECT id, sic_id, entity_name, network_level, city, address, latitude, longitude FROM dependex_world_registry WHERE city = 'Taglio di Po'")
for r in cursor.fetchall():
    print(r)

conn.close()
