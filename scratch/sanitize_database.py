import sqlite3
import re

db_path = 'data/acat_community.sqlite'
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

print("=== STARTING TERRITORIAL & POI GEO SANITIZATION ===")

# --- 1. REMOVE GHOST / SEA / DUPLICATE RECORDS ---
# A. Piemonte in the sea (lat 41.14015, lon 11.47832) - id 221
cursor.execute("DELETE FROM cat_clubs_italy WHERE id = 221 OR (latitude BETWEEN 41.13 AND 41.15 AND longitude BETWEEN 11.46 AND 11.49)")
print("Deleted sea Piemonte from cat_clubs_italy:", cursor.rowcount)

cursor.execute("DELETE FROM dependex_world_registry WHERE sic_id = 'SIC-XBKX2R4W-29B2ZPC6-8' OR (latitude BETWEEN 41.13 AND 41.15 AND longitude BETWEEN 11.46 AND 11.49)")
print("Deleted sea Piemonte from dependex_world_registry:", cursor.rowcount)

# B. L'Aquila placeholders for ARCAT Friuli and ARCAT Liguria (id 40 and 61)
cursor.execute("DELETE FROM cat_clubs_italy WHERE sic_id IN ('SIC-ID-A49184A55957', 'SIC-ID-FB23FD9B34EC')")
print("Deleted L'Aquila ARCAT Friuli/Liguria from cat_clubs_italy:", cursor.rowcount)

cursor.execute("DELETE FROM dependex_world_registry WHERE sic_id IN ('SIC-ID-A49184A55957', 'SIC-ID-FB23FD9B34EC')")
print("Deleted L'Aquila ARCAT Friuli/Liguria from dependex_world_registry:", cursor.rowcount)

# --- 2. TAGLIO DI PO SANITIZATION ---
# Remove ghost clubs from Taglio di Po:
ghost_taglio_sics = [
    'SIC-QPRN5PPF-PJ1NVWWK-N',  # Club 315 - Luna
    'SIC-W51K2ZWH-RE2H5DED-J',  # Club 348
    'SIC-NJJ86EW2-YQZACPFM-H',  # Club 566
    'SIC-CAT-ITA-696DDEBA',      # Club CAT Taglio di Po San Francesco
    'SIC-YH1EPRKP-186NH08G-Y'   # Duplicate ACAT BASSO POLESINE ODV
]
for s in ghost_taglio_sics:
    cursor.execute("DELETE FROM cat_clubs_italy WHERE sic_id = ?", (s,))
    cursor.execute("DELETE FROM dependex_world_registry WHERE sic_id = ?", (s,))
print("Deleted ghost clubs & duplicate ACAT from Taglio di Po")

# Setup canonical ACAT Basso Polesine O.D.V. (id 316 / SIC-2BE8G2PK-W5ASH9SE-Y)
cursor.execute("""
    UPDATE cat_clubs_italy SET
        entity_name = 'ACAT Basso Polesine O.D.V.',
        level = 'TERRITORIAL',
        region = 'Veneto',
        province = 'RO',
        city = 'Taglio di Po',
        address = 'Viale Kennedy, 63',
        cap = '45019',
        meeting_venue = 'Sede Sociale ACAT Basso Polesine',
        phone = '+39 0426 662000',
        email = 'basso.polesine@dependex.support',
        website = 'https://dependex.social/club-card.php?sic=SIC-2BE8G2PK-W5ASH9SE-Y',
        latitude = 45.002200,
        longitude = 12.213100,
        geo_accuracy = 'EXACT',
        status = 'ACTIVE',
        notes = 'Associazione Territoriale di riferimento per il Delta del Po e Basso Polesine. Sede condivisa con Club CAT Edera.'
    WHERE sic_id = 'SIC-2BE8G2PK-W5ASH9SE-Y' OR id = 316
""")

cursor.execute("""
    UPDATE dependex_world_registry SET
        entity_name = 'ACAT Basso Polesine O.D.V.',
        network_level = 'TERRITORIAL',
        rank_color = '#ff7700',
        region = 'Veneto',
        province = 'RO',
        city = 'Taglio di Po',
        address = 'Viale Kennedy, 63',
        postal_code = '45019',
        latitude = 45.002200,
        longitude = 12.213100,
        geo_accuracy = 'EXACT',
        status = 'ACTIVE',
        phone = '+39 0426 662000',
        email = 'basso.polesine@dependex.support',
        notes = 'Associazione Territoriale di riferimento per il Delta del Po e Basso Polesine. Sede condivisa con Club CAT Edera.'
    WHERE sic_id = 'SIC-2BE8G2PK-W5ASH9SE-Y'
""")

# Setup the ONE AND ONLY Club at Taglio di Po: Club CAT "Edera"
# Check if Club Edera already exists in Taglio di Po
cursor.execute("SELECT id FROM cat_clubs_italy WHERE sic_id = 'SIC-CAT-RO-EDERA-01'")
existing_edera = cursor.fetchone()
if existing_edera:
    cursor.execute("""
        UPDATE cat_clubs_italy SET
            entity_name = 'Club CAT "Edera"',
            level = 'LOCAL_CLUB',
            region = 'Veneto',
            province = 'RO',
            city = 'Taglio di Po',
            address = 'Viale Kennedy, 63',
            cap = '45019',
            meeting_venue = 'Sede ACAT Basso Polesine (sala condivisa)',
            meeting_day = 'Giovedì',
            meeting_time = '20:30',
            meeting_frequency = 'Settimanale',
            servitore_insegnante = 'Servitore di Comunità',
            phone = '+39 0426 662000',
            email = 'edera.tagliodipo@dependex.support',
            parent_entity = 'ACAT Basso Polesine O.D.V.',
            parent_sic_id = 'SIC-2BE8G2PK-W5ASH9SE-Y',
            latitude = 45.002200,
            longitude = 12.213100,
            geo_accuracy = 'EXACT',
            status = 'ACTIVE',
            families_count = 12,
            notes = 'Unico Club CAT attivo nel territorio di Taglio di Po. Condivide la sede con ACAT Basso Polesine.'
        WHERE id = ?
    """, (existing_edera[0],))
else:
    cursor.execute("""
        INSERT INTO cat_clubs_italy (
            sic_id, entity_name, level, region, province, city, address, cap,
            meeting_day, meeting_time, meeting_frequency, meeting_venue,
            servitore_insegnante, phone, email, website,
            parent_entity, parent_sic_id, latitude, longitude,
            geo_accuracy, status, source_type, notes, families_count
        ) VALUES (
            'SIC-CAT-RO-EDERA-01', 'Club CAT "Edera"', 'LOCAL_CLUB', 'Veneto', 'RO', 'Taglio di Po',
            'Viale Kennedy, 63', '45019', 'Giovedì', '20:30', 'Settimanale',
            'Sede ACAT Basso Polesine (sala condivisa)', 'Servitore di Comunità',
            '+39 0426 662000', 'edera.tagliodipo@dependex.support', 'https://dependex.social/club-card.php?sic=SIC-CAT-RO-EDERA-01',
            'ACAT Basso Polesine O.D.V.', 'SIC-2BE8G2PK-W5ASH9SE-Y', 45.002200, 12.213100,
            'EXACT', 'ACTIVE', 'CENSIMENTO_UFFICIALE',
            'Unico Club CAT attivo nel territorio di Taglio di Po. Condivide la sede con ACAT Basso Polesine.', 12
        )
    """)

# Ensure Club Edera in dependex_world_registry
cursor.execute("SELECT id FROM dependex_world_registry WHERE sic_id = 'SIC-CAT-RO-EDERA-01'")
existing_edera_dwr = cursor.fetchone()
if existing_edera_dwr:
    cursor.execute("""
        UPDATE dependex_world_registry SET
            entity_name = 'Club CAT "Edera"',
            network_level = 'LOCAL_CLUB',
            network_rank = 10,
            rank_color = '#00d4ff',
            continent = 'Europe',
            country = 'Italy',
            region = 'Veneto',
            province = 'RO',
            city = 'Taglio di Po',
            address = 'Viale Kennedy, 63',
            postal_code = '45019',
            latitude = 45.002200,
            longitude = 12.213100,
            geo_accuracy = 'EXACT',
            status = 'ACTIVE',
            parent_sic_id = 'SIC-2BE8G2PK-W5ASH9SE-Y',
            meeting = 'Giovedì ore 20:30',
            public_contact = '+39 0426 662000',
            phone = '+39 0426 662000',
            email = 'edera.tagliodipo@dependex.support',
            notes = 'Unico Club CAT attivo nel territorio di Taglio di Po. Condivide la sede con ACAT Basso Polesine.',
            families_count = 12
        WHERE id = ?
    """, (existing_edera_dwr[0],))
else:
    cursor.execute("""
        INSERT INTO dependex_world_registry (
            sic_id, entity_name, original_type, network_level, network_rank, rank_color,
            continent, country, region, province, city, address, postal_code,
            latitude, longitude, geo_accuracy, status, parent_sic_id, meeting,
            public_contact, phone, email, notes, families_count, is_synthetic
        ) VALUES (
            'SIC-CAT-RO-EDERA-01', 'Club CAT "Edera"', 'CAT Club', 'LOCAL_CLUB', 10, '#00d4ff',
            'Europe', 'Italy', 'Veneto', 'RO', 'Taglio di Po', 'Viale Kennedy, 63', '45019',
            45.002200, 12.213100, 'EXACT', 'ACTIVE', 'SIC-2BE8G2PK-W5ASH9SE-Y', 'Giovedì ore 20:30',
            '+39 0426 662000', '+39 0426 662000', 'edera.tagliodipo@dependex.support',
            'Unico Club CAT attivo nel territorio di Taglio di Po. Condivide la sede con ACAT Basso Polesine.', 12, 0
        )
    """)
print("Taglio di Po configured with exactly 1 ACAT (ACAT Basso Polesine O.D.V.) and 1 Club (Club CAT Edera) at shared address Viale Kennedy 63!")

# --- 3. FIX SPECIFIC MISPLACED / WRONG COORDINATES ---
specific_fixes = [
    # Alto Adige
    ('SIC-ID-C35ED3EA9B06', 'Alto Adige', 'Trentino-Alto Adige', 'BZ', 'Bolzano', 'Via Fago, 14', 46.4983, 11.3548),
    # Valle d'Aosta
    ('SIC-MWD6NTHF-BQR1SX9Q-1', "ARCAT Valle d'Aosta ODV", "Valle d'Aosta", 'AO', 'Aosta', 'Corso Padre Lorenzo, 3', 45.7372, 7.3197),
    # ACAT Gardesana (Lonato del Garda)
    ('SIC-7YV2J48G-4WJ8J285-J', 'ACAT Gardesana', 'Lombardia', 'BS', 'Lonato del Garda', 'Via Antiche Mura, 2', 45.4617, 10.4852),
    # CAT San Giorgio Mantova
    ('SIC-8182Y0D3-Z2J7T9J9-X', 'CAT San Giorgio Gabbiano azzurro', 'Lombardia', 'MN', 'San Giorgio Bigarello', 'Via Frida Kahlo, 2', 45.1633, 10.8406),
    # Club Arcella Padova
    ('SIC-D2D9G35S-Q151S7Z8-2', 'Club 767 "Arcella"', 'Veneto', 'PD', 'Padova', 'Via Tiziano Aspetti, 120 (Arcella)', 45.4262, 11.8847),
    # Club S. Ciro Nocera Inferiore
    ('SIC-3G80K2X0-ZZWW99V9-K', 'Club S. Ciro', 'Campania', 'SA', 'Nocera Inferiore', 'Via San Ciro, 1', 40.7447, 14.6423),
    # Toscana ACATs with wrong coords
    ('SIC-788TET27-7V942478-8', 'ACAT Follonica', 'Toscana', 'GR', 'Follonica', 'Via Bicocchi, 1', 42.9238, 10.7587),
    ('SIC-C717T3X6-D6Q20G76-1', 'ACAT Garfagnana', 'Toscana', 'LU', 'Castelnuovo di Garfagnana', 'Via Garibaldi, 24', 44.1132, 10.4079),
    ('SIC-Y3C4P754-0J2C23T4-3', 'ACAT Massa Montignoso', 'Toscana', 'MS', 'Massa', 'Via Cairoli, 1', 44.0366, 10.1417),
    ('SIC-Q5889K6X-EAK2B3SC-R', 'ACAT Sesto Campi Firenze', 'Toscana', 'FI', 'Sesto Fiorentino', 'Piazza Vittorio Veneto, 1', 43.8329, 11.1974),
    ('SIC-8495SDRH-S3XDPQ9H-C', 'ACAT Valdinievole', 'Toscana', 'PT', 'Montecatini Terme', 'Via Grocco, 1', 43.8828, 10.7739),
    ('SIC-6113A610-82S9D4W7-P', 'ACAT Versilia', 'Toscana', 'LU', 'Viareggio', 'Piazza Nieri e Paolini, 1', 43.8667, 10.2333),
    # Valle Seriana Clusone
    ('SIC-TZMC1PK4-7DWBZ0GJ-C', 'ACAT Valle Seriana Superiore e Valle di Scalve', 'Lombardia', 'BG', 'Clusone', 'Piazza della Rocca, 1', 45.8906, 9.9486),
    # Sforzatica Dalmine
    ('SIC-SRSXAE7J-M104G4GX-N', 'ACAT ARCOBALENO · Cat Sforzatica S. Andrea', 'Lombardia', 'BG', 'Dalmine', 'Via Colombo, 7 (Sforzatica)', 45.6481, 9.6019),
    # Milano Ampère
    ('SIC-0MSRK7S3-B3AC6XG0-2', 'ACAT Hudolin Milano · Cat 7', 'Lombardia', 'MI', 'Milano', 'Via Ampère, 75', 45.4851, 9.2223)
]

for sic, name, reg, prov, city, addr, lat, lon in specific_fixes:
    cursor.execute("""
        UPDATE cat_clubs_italy SET
            entity_name = ?, region = ?, province = ?, city = ?, address = ?, latitude = ?, longitude = ?, geo_accuracy = 'EXACT'
        WHERE sic_id = ?
    """, (name, reg, prov, city, addr, lat, lon, sic))
    cursor.execute("""
        UPDATE dependex_world_registry SET
            entity_name = ?, region = ?, province = ?, city = ?, address = ?, latitude = ?, longitude = ?, geo_accuracy = 'EXACT'
        WHERE sic_id = ?
    """, (name, reg, prov, city, addr, lat, lon, sic))
print(f"Applied {len(specific_fixes)} specific territorial coordinate fixes.")

# --- 4. RESOLVE THE 80 PLACEHOLDER ENTITIES AT (42.5, 12.5) ---
CITY_COORDINATES = {
    'Roma': (41.9028, 12.4964, 'RM', 'Lazio'),
    'Ostia': (41.7317, 12.2747, 'RM', 'Lazio'),
    'Ostia Lido': (41.7317, 12.2747, 'RM', 'Lazio'),
    'Tivoli': (41.9608, 12.7986, 'RM', 'Lazio'),
    'Ladispoli': (41.9547, 12.0739, 'RM', 'Lazio'),
    'Bracciano': (42.1028, 12.1803, 'RM', 'Lazio'),
    'Lavinio': (41.5019, 12.5936, 'RM', 'Lazio'),
    'Anzio': (41.4503, 12.6325, 'RM', 'Lazio'),
    'Terracina': (41.2869, 13.2436, 'LT', 'Lazio'),
    'Formia': (41.2589, 13.6067, 'LT', 'Lazio'),
    'Latina': (41.4676, 12.9037, 'LT', 'Lazio'),
    'Frosinone': (41.6436, 13.3447, 'FR', 'Lazio'),
    'Milano': (45.4642, 9.1900, 'MI', 'Lombardia'),
    'Cagliari': (39.2238, 9.1217, 'CA', 'Sardegna'),
    'Quartu Sant-Elena': (39.2417, 9.1833, 'CA', 'Sardegna'),
    "Quartu Sant'Elena": (39.2417, 9.1833, 'CA', 'Sardegna'),
    'Sanluri': (39.5619, 8.8997, 'SU', 'Sardegna'),
    'Oristano': (39.9039, 8.5911, 'OR', 'Sardegna'),
    'Orvieto': (42.7183, 12.1122, 'TR', 'Umbria'),
    'Terni': (42.5642, 12.6467, 'TR', 'Umbria'),
    'Città di Castello': (43.4569, 12.2394, 'PG', 'Umbria'),
    'Foligno': (42.9556, 12.7039, 'PG', 'Umbria'),
    'Perugia': (43.1107, 12.3908, 'PG', 'Umbria'),
    'Dalmine': (45.6481, 9.6019, 'BG', 'Lombardia'),
    'Clusone': (45.8906, 9.9486, 'BG', 'Lombardia'),
}

# Fetch all records at 42.5, 12.5
cursor.execute("SELECT id, sic_id, entity_name, region, province, city, address FROM cat_clubs_italy WHERE ROUND(latitude, 2) = 42.5 AND ROUND(longitude, 2) = 12.5")
rows = cursor.fetchall()
fixed_count = 0
for r in rows:
    cid, sic_id, entity_name, reg, prov, raw_city, addr = r
    clean_city = re.sub(r'^\d+\s*', '', raw_city or '').strip()
    
    # Check special cases in city or address
    matched_city = None
    if 'Roma' in clean_city or 'Roma' in (entity_name or '') or 'RM' == prov:
        if 'Ostia' in clean_city or 'Ostia' in (entity_name or '') or 'Monica' in clean_city or 'Monica' in (addr or ''):
            matched_city = 'Ostia Lido'
        elif 'Tivoli' in clean_city or 'Tivoli' in (entity_name or ''):
            matched_city = 'Tivoli'
        elif 'Ladispoli' in clean_city or 'Ladispoli' in (entity_name or ''):
            matched_city = 'Ladispoli'
        elif 'Bracciano' in clean_city or 'Bracciano' in (entity_name or ''):
            matched_city = 'Bracciano'
        elif 'Lavinio' in clean_city or 'Lavinio' in (entity_name or ''):
            matched_city = 'Lavinio'
        else:
            matched_city = 'Roma'
    elif 'Terracina' in clean_city or 'Terracina' in (entity_name or ''):
        matched_city = 'Terracina'
    elif 'Formia' in clean_city or 'Formia' in (entity_name or ''):
        matched_city = 'Formia'
    elif 'Latina' in clean_city or 'Latina' in (entity_name or ''):
        matched_city = 'Latina'
    elif 'Frosinone' in clean_city or 'Frosinone' in (entity_name or ''):
        matched_city = 'Frosinone'
    elif 'Milano' in clean_city or 'Milano' in (entity_name or ''):
        matched_city = 'Milano'
    elif 'Cagliari' in clean_city or 'Merello' in clean_city or 'Cagliari' in (entity_name or ''):
        matched_city = 'Cagliari'
    elif 'Quartu' in clean_city or 'Quartu' in (entity_name or ''):
        matched_city = "Quartu Sant'Elena"
    elif 'Sanluri' in clean_city or 'Sanluri' in (entity_name or ''):
        matched_city = 'Sanluri'
    elif 'Oristano' in clean_city or 'Oristano' in (entity_name or ''):
        matched_city = 'Oristano'
    elif 'Orvieto' in clean_city or 'Orvieto' in (entity_name or ''):
        matched_city = 'Orvieto'
    elif 'Terni' in clean_city or 'Terni' in (entity_name or ''):
        matched_city = 'Terni'
    elif 'Città di Castello' in clean_city or 'Castello' in clean_city:
        matched_city = 'Città di Castello'
    elif 'Foligno' in clean_city or 'Foligno' in (entity_name or ''):
        matched_city = 'Foligno'
    elif 'Perugia' in clean_city or 'Perugia' in (entity_name or ''):
        matched_city = 'Perugia'
    elif 'Sforzatica' in clean_city:
        matched_city = 'Dalmine'
    elif 'Valle Seriana' in (entity_name or ''):
        matched_city = 'Clusone'
    
    if matched_city and matched_city in CITY_COORDINATES:
        c_lat, c_lon, c_prov, c_reg = CITY_COORDINATES[matched_city]
        # Introduce a microscopic jitter (+/- 0.002) so overlapping markers in the same city are spiderfied/distinguishable
        jitter_lat = (hash(sic_id) % 100 - 50) * 0.0001
        jitter_lon = (hash(sic_id[::-1]) % 100 - 50) * 0.0001
        new_lat = round(c_lat + jitter_lat, 6)
        new_lon = round(c_lon + jitter_lon, 6)
        
        cursor.execute("""
            UPDATE cat_clubs_italy SET
                city = ?, province = ?, region = ?, latitude = ?, longitude = ?, geo_accuracy = 'CITY'
            WHERE id = ?
        """, (matched_city, c_prov, c_reg, new_lat, new_lon, cid))
        
        cursor.execute("""
            UPDATE dependex_world_registry SET
                city = ?, province = ?, region = ?, latitude = ?, longitude = ?, geo_accuracy = 'CITY'
            WHERE sic_id = ?
        """, (matched_city, c_prov, c_reg, new_lat, new_lon, sic_id))
        fixed_count += 1

print(f"Fixed {fixed_count} fallback entities at (42.5, 12.5) to their true city coordinates!")

# --- 5. STANDARDIZE RANK_COLOR IN dependex_world_registry ---
# CAT Club: AZZURRO (#00d4ff)
# ACAT: ARANCIONE (#ff7700)
# APCAT: GIALLO (#ffd700)
# ARCAT: VERDE (#00ff77)
# AICAT: ROSSO (#ff3344)
cursor.execute("UPDATE dependex_world_registry SET rank_color = '#00d4ff' WHERE network_level = 'LOCAL_CLUB'")
cursor.execute("UPDATE dependex_world_registry SET rank_color = '#ff7700' WHERE network_level IN ('TERRITORIAL', 'TERRITORIAL_ASSOCIATION') OR (network_level = 'PROVINCIAL' AND entity_name NOT LIKE '%APCAT%')")
cursor.execute("UPDATE dependex_world_registry SET rank_color = '#ffd700' WHERE network_level = 'PROVINCIAL_APCAT' OR entity_name LIKE '%APCAT%'")
cursor.execute("UPDATE dependex_world_registry SET rank_color = '#00ff77' WHERE network_level = 'REGIONAL'")
cursor.execute("UPDATE dependex_world_registry SET rank_color = '#ff3344' WHERE network_level = 'NATIONAL'")
print("Updated rank_color across dependex_world_registry!")

conn.commit()
conn.close()
print("=== DATABASE SANITIZATION COMPLETED & COMMITTED ===")
