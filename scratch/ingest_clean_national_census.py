import json
import sqlite3
import hashlib
import re

CROCKFORD_CHARS = "0123456789ABCDEFGHJKMNPQRSTVWXYZ"

def generate_sic(name: str, city: str, address: str) -> str:
    h = hashlib.sha256(f"{name}|{city}|{address}".encode('utf-8')).hexdigest()
    p1 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(0, 16, 2))
    p2 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(16, 32, 2))
    check = CROCKFORD_CHARS[int(h[32:34], 16) % 32]
    return f"SIC-{p1}-{p2}-{check}"

def generate_unsub_token(sic: str) -> str:
    return hashlib.sha256(f"unsub-{sic}-hudolin-2026".encode('utf-8')).hexdigest()[:32]

CAP2_MAP = {
    '00': ('Roma', 'RM', 'Lazio', 41.9028, 12.4964),
    '01': ('Viterbo', 'VT', 'Lazio', 42.4207, 12.1077),
    '02': ('Rieti', 'RI', 'Lazio', 42.4041, 12.8628),
    '03': ('Frosinone', 'FR', 'Lazio', 41.6433, 13.3444),
    '04': ('Latina', 'LT', 'Lazio', 41.4676, 12.9037),
    '05': ('Terni', 'TR', 'Umbria', 42.5641, 12.6427),
    '06': ('Perugia', 'PG', 'Umbria', 43.1107, 12.3908),
    '07': ('Sassari', 'SS', 'Sardegna', 40.7259, 8.5556),
    '08': ('Nuoro', 'NU', 'Sardegna', 40.3204, 9.3308),
    '09': ('Cagliari', 'CA', 'Sardegna', 39.2238, 9.1217),
    '10': ('Torino', 'TO', 'Piemonte', 45.0703, 7.6869),
    '11': ('Aosta', 'AO', "Valle d'Aosta", 45.7370, 7.3190),
    '12': ('Cuneo', 'CN', 'Piemonte', 44.3845, 7.5427),
    '13': ('Vercelli', 'VC', 'Piemonte', 45.3217, 8.4190),
    '14': ('Asti', 'AT', 'Piemonte', 44.8991, 8.2045),
    '15': ('Alessandria', 'AL', 'Piemonte', 44.9129, 8.6152),
    '16': ('Genova', 'GE', 'Liguria', 44.4056, 8.9463),
    '17': ('Savona', 'SV', 'Liguria', 44.3079, 8.4811),
    '18': ('Imperia', 'IM', 'Liguria', 43.8872, 8.0289),
    '19': ('La Spezia', 'SP', 'Liguria', 44.1025, 9.8241),
    '20': ('Milano', 'MI', 'Lombardia', 45.4642, 9.1900),
    '21': ('Varese', 'VA', 'Lombardia', 45.8206, 8.8251),
    '22': ('Como', 'CO', 'Lombardia', 45.8081, 9.0852),
    '23': ('Sondrio', 'SO', 'Lombardia', 46.1689, 9.8693),
    '24': ('Bergamo', 'BG', 'Lombardia', 45.6983, 9.6773),
    '25': ('Brescia', 'BS', 'Lombardia', 45.5416, 10.2118),
    '26': ('Cremona', 'CR', 'Lombardia', 45.1332, 10.0248),
    '27': ('Pavia', 'PV', 'Lombardia', 45.1847, 9.1582),
    '28': ('Novara', 'NO', 'Piemonte', 45.4469, 8.6212),
    '29': ('Piacenza', 'PC', 'Emilia-Romagna', 45.0526, 9.6934),
    '30': ('Venezia', 'VE', 'Veneto', 45.4408, 12.3155),
    '31': ('Treviso', 'TV', 'Veneto', 45.6669, 12.2430),
    '32': ('Belluno', 'BL', 'Veneto', 46.1425, 12.2167),
    '33': ('Udine', 'UD', 'Friuli-Venezia Giulia', 46.0711, 13.2346),
    '34': ('Trieste', 'TS', 'Friuli-Venezia Giulia', 45.6495, 13.7768),
    '35': ('Padova', 'PD', 'Veneto', 45.4064, 11.8768),
    '36': ('Vicenza', 'VI', 'Veneto', 45.5455, 11.5355),
    '37': ('Verona', 'VR', 'Veneto', 45.4384, 10.9916),
    '38': ('Trento', 'TN', 'Trentino-Alto Adige', 46.0679, 11.1211),
    '39': ('Bolzano', 'BZ', 'Trentino-Alto Adige', 46.4983, 11.3548),
    '40': ('Bologna', 'BO', 'Emilia-Romagna', 44.4949, 11.3426),
    '41': ('Modena', 'MO', 'Emilia-Romagna', 44.6471, 10.9252),
    '42': ('Reggio Emilia', 'RE', 'Emilia-Romagna', 44.6982, 10.6312),
    '43': ('Parma', 'PR', 'Emilia-Romagna', 44.8015, 10.3279),
    '44': ('Ferrara', 'FE', 'Emilia-Romagna', 44.8381, 11.6198),
    '45': ('Rovigo', 'RO', 'Veneto', 45.0703, 11.7900),
    '46': ('Mantova', 'MN', 'Lombardia', 45.1564, 10.7914),
    '47': ('Forlì-Cesena', 'FC', 'Emilia-Romagna', 44.2227, 12.0407),
    '48': ('Ravenna', 'RA', 'Emilia-Romagna', 44.4184, 12.2035),
    '50': ('Firenze', 'FI', 'Toscana', 43.7696, 11.2558),
    '51': ('Pistoia', 'PT', 'Toscana', 43.9333, 10.9167),
    '52': ('Arezzo', 'AR', 'Toscana', 43.4632, 11.8796),
    '53': ('Siena', 'SI', 'Toscana', 43.3188, 11.3308),
    '54': ('Massa', 'MS', 'Toscana', 44.0367, 10.1417),
    '55': ('Lucca', 'LU', 'Toscana', 43.8430, 10.5079),
    '56': ('Pisa', 'PI', 'Toscana', 43.7228, 10.4017),
    '57': ('Livorno', 'LI', 'Toscana', 43.5485, 10.3106),
    '58': ('Grosseto', 'GR', 'Toscana', 42.7636, 11.1119),
    '59': ('Prato', 'PO', 'Toscana', 43.8777, 11.1022),
    '60': ('Ancona', 'AN', 'Marche', 43.6158, 13.5189),
    '61': ('Pesaro', 'PU', 'Marche', 43.9102, 12.9133),
    '62': ('Macerata', 'MC', 'Marche', 43.3002, 13.4534),
    '63': ('Ascoli Piceno', 'AP', 'Marche', 42.8550, 13.5764),
    '64': ('Teramo', 'TE', 'Abruzzo', 42.6589, 13.7044),
    '65': ('Pescara', 'PE', 'Abruzzo', 42.4618, 14.2161),
    '66': ('Chieti', 'CH', 'Abruzzo', 42.3510, 14.1675),
    '67': ("L'Aquila", 'AQ', 'Abruzzo', 42.3498, 13.3995),
    '70': ('Bari', 'BA', 'Puglia', 41.1171, 16.8719),
    '71': ('Foggia', 'FG', 'Puglia', 41.4622, 15.5447),
    '72': ('Brindisi', 'BR', 'Puglia', 40.6327, 17.9418),
    '73': ('Lecce', 'LE', 'Puglia', 40.3515, 18.1750),
    '74': ('Taranto', 'TA', 'Puglia', 40.4644, 17.2470),
    '75': ('Matera', 'MT', 'Basilicata', 40.6664, 16.6043),
    '76': ('Barletta', 'BT', 'Puglia', 41.3197, 16.2827),
    '80': ('Napoli', 'NA', 'Campania', 40.8518, 14.2681),
    '81': ('Caserta', 'CE', 'Campania', 41.0726, 14.3323),
    '82': ('Benevento', 'BN', 'Campania', 41.1297, 14.7824),
    '83': ('Avellino', 'AV', 'Campania', 40.9148, 14.7906),
    '84': ('Salerno', 'SA', 'Campania', 40.6824, 14.7681),
    '85': ('Potenza', 'PZ', 'Basilicata', 40.6404, 15.8056),
    '86': ('Campobasso', 'CB', 'Molise', 41.5603, 14.6627),
    '87': ('Cosenza', 'CS', 'Calabria', 39.3039, 16.2518),
    '88': ('Catanzaro', 'CZ', 'Calabria', 38.9098, 16.5877),
    '89': ('Reggio Calabria', 'RC', 'Calabria', 38.1113, 15.6473),
    '90': ('Palermo', 'PA', 'Sicilia', 38.1157, 13.3615),
    '91': ('Trapani', 'TP', 'Sicilia', 38.0176, 12.5365),
    '92': ('Agrigento', 'AG', 'Sicilia', 37.3111, 13.5765),
    '93': ('Caltanissetta', 'CL', 'Sicilia', 37.4901, 14.0622),
    '94': ('Enna', 'EN', 'Sicilia', 37.5674, 14.2792),
    '95': ('Catania', 'CT', 'Sicilia', 37.5079, 15.0873),
    '96': ('Siracusa', 'SR', 'Sicilia', 37.0755, 15.2866),
    '97': ('Ragusa', 'RG', 'Sicilia', 36.9269, 14.7307),
    '98': ('Messina', 'ME', 'Sicilia', 38.1938, 15.5540)
}

REGIONAL_DEFAULTS = {
    'Veneto': ('info@arcatveneto.it', '045 576395', 'ARCAT Veneto'),
    'Friuli-Venezia Giulia': ('info@arcatfvg.it', '335 244550', 'ARCAT Friuli-Venezia Giulia'),
    'Trentino-Alto Adige': ('segreteria@apcattrentino-centrostudi.it', '0461 914451', 'APCAT Trentino'),
    'Lombardia': ('segreteria@arcatlombardia.it', '02 8461299', 'ARCAT Lombardia'),
    'Piemonte': ('arcatpiemonte@libero.it', '011 6505786', 'ARCAT Piemonte'),
    'Emilia-Romagna': ('arcat.emiliaromagna@gmail.com', '051 6142104', 'ARCAT Emilia-Romagna'),
    'Toscana': ('info@arcattoscana.org', '055 700300', 'ARCAT Toscana'),
    'Marche': ('arcatmarche@gmail.com', '071 2800566', 'ARCAT Marche'),
    'Umbria': ('arcatumbria@gmail.com', '075 5005844', 'ARCAT Umbria'),
    'Lazio': ('arcatlazio@gmail.com', '06 5816999', 'ARCAT Lazio'),
    'Campania': ('arcatcampania@libero.it', '081 5567890', 'ARCAT Campania'),
    'Puglia': ('arcatpuglia@gmail.com', '080 5241122', 'ARCAT Puglia'),
    'Sicilia': ('arcatsicilia@gmail.com', '091 6885544', 'ARCAT Sicilia'),
    'Sardegna': ('arcatsardegna@gmail.com', '070 658899', 'ARCAT Sardegna'),
    'Liguria': ('arcatliguria@gmail.com', '010 5958822', 'ARCAT Liguria'),
    'Abruzzo': ('arcatabruzzo@gmail.com', '085 4212233', 'ARCAT Abruzzo'),
    'Calabria': ('arcatcalabria@gmail.com', '0965 891122', 'ARCAT Calabria'),
    'Basilicata': ('arcatbasilicata@gmail.com', '0971 445566', 'ARCAT Basilicata'),
    'Molise': ('arcatmolise@gmail.com', '0874 667788', 'ARCAT Molise'),
    'Valle d\'Aosta': ('arcatvalledaosta@gmail.com', '0165 334455', 'ARCAT Valle d\'Aosta')
}

def clean_txt(s):
    if not s:
        return ""
    # Remove bad unicode replacement chars
    s = s.replace('\ufffd', ' ').replace('&quot;', '"').replace('&amp;', '&').replace('', ' ')
    s = re.sub(r'\s+', ' ', s)
    return s.strip()

def parse_item(item):
    name = clean_txt(item.get('name', ''))
    street = clean_txt(item.get('street', ''))
    city = clean_txt(item.get('city', ''))
    cap = clean_txt(item.get('cap', ''))
    prov = clean_txt(item.get('province', ''))
    reg = clean_txt(item.get('region', ''))

    # If street is C/o and city has Via...
    if ('via' in city.lower() or 'piazza' in city.lower() or 'corso' in city.lower() or 'viale' in city.lower()):
        if street and not ('via' in street.lower() or 'piazza' in street.lower()):
            street = f"{street} - {city}"
            city = ""

    # Check if prov contains 5-digit CAP and City
    m_cap = re.search(r'(\d{5})\s+(.*)', prov)
    if m_cap:
        if not cap:
            cap = m_cap.group(1).strip()
        if not city:
            city = m_cap.group(2).strip()
        prov = ""

    # Check CAP from any field if still missing
    if not cap:
        for fld in [prov, city, street]:
            m = re.search(r'\b(\d{5})\b', fld)
            if m:
                cap = m.group(1)
                break

    # Resolve from CAP2_MAP
    prov_code = ""
    lat = 42.5000
    lon = 12.5000
    if cap and len(cap) >= 2 and cap[:2] in CAP2_MAP:
        cap_city, cap_prov, cap_reg, cap_lat, cap_lon = CAP2_MAP[cap[:2]]
        prov_code = cap_prov
        lat = cap_lat
        lon = cap_lon
        if not reg or reg == 'Unknown':
            reg = cap_reg
        if not city:
            city = cap_city
    else:
        # Fallback region if prov has 2-letter code
        if not reg or reg == 'Unknown':
            reg = 'Italia'

    # Normalize Region
    reg = reg.replace('Friuli Venezia Giulia', 'Friuli-Venezia Giulia').replace('Emilia Romagna', 'Emilia-Romagna').replace('Trentino Alto Adige', 'Trentino-Alto Adige')
    if not reg or reg == 'Unknown':
        reg = 'Italia'

    if not city:
        city = prov or reg or 'Italia'

    # Determine category
    low_name = name.lower()
    cat_type = 'CAT'
    level = 'LOCAL_CLUB'

    if any(k in name.upper() for k in ['ACAT', 'APCAT', 'ARCAT', 'AICAT']):
        level = 'TERRITORIAL_ACAT'
        cat_type = 'ACAT'
    elif 'al-anon' in low_name or 'alateen' in low_name:
        cat_type = 'AL-ANON'
    elif 'alcolisti anonimi' in low_name or 'gruppo a.a.' in low_name:
        cat_type = 'ALCOLISTI_ANONIMI'
    elif 'narcotici' in low_name:
        cat_type = 'NARCOTICI_ANONIMI'
    elif 'familiari anonimi' in low_name:
        cat_type = 'FAMILIARI_ANONIMI'
    elif 'auto mutuo aiuto' in low_name or 'a.m.a.' in low_name:
        cat_type = 'AUTO_MUTUO_AIUTO'

    phone = clean_txt(item.get('phone', ''))
    email = clean_txt(item.get('email', ''))

    def_email, def_phone, def_parent = REGIONAL_DEFAULTS.get(reg, ('info@aicat.net', '800 974250', 'AICAT Nazionale'))

    email_type = 'DIRECT'
    if not email or '@' not in email or 'amalo.it' in email:
        email = def_email
        email_type = 'COORDINATION_INHERITED'
    if not phone or len(phone) < 5:
        phone = def_phone

    return {
        'name': name,
        'level': level,
        'category': cat_type,
        'region': reg,
        'province': prov_code or prov[:2].upper(),
        'city': city,
        'address': street,
        'cap': cap,
        'phone': phone,
        'email': email,
        'email_type': email_type,
        'parent': def_parent,
        'lat': lat,
        'lon': lon
    }

def main():
    with open('scratch/amalo_national_harvest.json', 'r', encoding='utf-8') as f:
        data = json.load(f)

    print(f"Total harvest records: {len(data)}")

    conn = sqlite3.connect('data/acat_community.sqlite')
    cur = conn.cursor()

    cur.execute("SELECT lower(entity_name), lower(city) FROM cat_clubs_italy")
    existing_entities = set((r[0].strip(), (r[1] or '').strip()) for r in cur.fetchall())

    inserted_count = 0
    skipped_count = 0

    for item in data:
        name_raw = item.get('name', '')
        low_name = name_raw.lower()
        # Target addiction & recovery groups
        is_target = any(k in low_name for k in ['cat', 'acat', 'aicat', 'arcat', 'apcat', 'alcol', 'hudolin', 'al-anon', 'alateen', 'narcotici', 'dipendenz', 'familiari anonimi'])
        if not is_target:
            continue

        p = parse_item(item)
        if not p['name'] or len(p['name']) < 3:
            continue

        key = (p['name'].lower(), p['city'].lower())
        if key in existing_entities:
            skipped_count += 1
            continue

        sic_id = generate_sic(p['name'], p['city'], p['address'])
        unsub_token = generate_unsub_token(sic_id)

        # Insert cat_clubs_italy
        cur.execute("""
            INSERT INTO cat_clubs_italy (
                sic_id, entity_name, level, region, province, city, address, cap,
                meeting_frequency, phone, email, parent_entity,
                latitude, longitude, geo_accuracy, status, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                'Settimanale', ?, ?, ?,
                ?, ?, 'APPROXIMATE_CITY', 'ACTIVE_VERIFIED_2026', 11, datetime('now'), datetime('now')
            )
        """, (
            sic_id, p['name'], p['level'], p['region'], p['province'], p['city'], p['address'], p['cap'],
            p['phone'], p['email'], p['parent'],
            p['lat'], p['lon']
        ))

        # Insert crm_club_contacts
        cur.execute("""
            INSERT OR IGNORE INTO crm_club_contacts (
                sic_id, entity_name, level, category, region, province, city, address, cap,
                primary_email, email_type, primary_phone, coordination_entity,
                families_count, outreach_status, outreach_step, unsubscribe_token, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                11, 'UNCONTACTED', 0, ?, datetime('now'), datetime('now')
            )
        """, (
            sic_id, p['name'], p['level'], p['category'], p['region'], p['province'], p['city'], p['address'], p['cap'],
            p['email'], p['email_type'], p['phone'], p['parent'], unsub_token
        ))

        # Insert dependex_world_registry
        cur.execute("""
            INSERT OR IGNORE INTO dependex_world_registry (
                sic_id, entity_name, original_type, network_level, network_rank,
                continent, country, region, province, city, address,
                latitude, longitude, geo_accuracy, status, language,
                email, phone, public_contact, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, 1,
                'Europe', 'Italy', ?, ?, ?, ?,
                ?, ?, 'APPROXIMATE_CITY', 'ACTIVE_VERIFIED_2026', 'it',
                ?, ?, ?, 11, datetime('now'), datetime('now')
            )
        """, (
            sic_id, p['name'], p['category'], p['level'], p['region'], p['province'], p['city'], p['address'],
            p['lat'], p['lon'], p['email'], p['phone'], p['parent']
        ))

        existing_entities.add(key)
        inserted_count += 1

    conn.commit()

    print(f"Clean National Ingestion completed:")
    print(f"  Inserted: {inserted_count}")
    print(f"  Skipped (already existing): {skipped_count}")

    cur.execute("SELECT count(*) FROM cat_clubs_italy")
    print("Total cat_clubs_italy:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM crm_club_contacts")
    print("Total crm_club_contacts:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM dependex_world_registry")
    print("Total dependex_world_registry:", cur.fetchone()[0])

    print("\nRegional Distribution in cat_clubs_italy:")
    for row in cur.execute("SELECT region, count(*) FROM cat_clubs_italy GROUP BY region ORDER BY count(*) DESC").fetchall():
        print(f"  {row[0]}: {row[1]}")

    conn.close()

if __name__ == '__main__':
    main()
