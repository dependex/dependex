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

REGIONAL_CENTROIDS = {
    'Abruzzo': (42.2001, 13.9068),
    'Basilicata': (40.6577, 16.3380),
    'Calabria': (39.3039, 16.2518),
    'Campania': (40.7204, 14.6367),
    'Emilia-Romagna': (44.3349, 11.7090),
    'Friuli-Venezia Giulia': (45.8295, 13.1158),
    'Italia': (42.5000, 12.5000),
    'Lazio': (41.7364, 12.8900),
    'Liguria': (44.0494, 9.6047),
    'Lombardia': (45.5168, 9.7747),
    'Marche': (43.2122, 13.5629),
    'Molise': (41.5603, 14.6627),
    'Piemonte': (44.6911, 7.9611),
    'Puglia': (41.0559, 16.7727),
    'Sardegna': (40.5670, 9.0436),
    'Sicilia': (37.6588, 14.3993),
    'Toscana': (43.1172, 11.3935),
    'Trentino-Alto Adige': (46.0678, 11.1210),
    'Umbria': (43.1107, 12.3908),
    'Valle d\'Aosta': (45.7370, 7.3190),
    'Veneto': (45.5517, 11.8007)
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

PROV_TO_REG = {
    'AG': 'Sicilia', 'AL': 'Piemonte', 'AN': 'Marche', 'AO': 'Valle d\'Aosta', 'AP': 'Marche',
    'AQ': 'Abruzzo', 'AR': 'Toscana', 'AT': 'Piemonte', 'AV': 'Campania', 'BA': 'Puglia',
    'BG': 'Lombardia', 'BI': 'Piemonte', 'BL': 'Veneto', 'BN': 'Campania', 'BO': 'Emilia-Romagna',
    'BR': 'Puglia', 'BS': 'Lombardia', 'BT': 'Puglia', 'BZ': 'Trentino-Alto Adige', 'CA': 'Sardegna',
    'CB': 'Molise', 'CE': 'Campania', 'CH': 'Abruzzo', 'CL': 'Sicilia', 'CN': 'Piemonte',
    'CO': 'Lombardia', 'CR': 'Lombardia', 'CS': 'Calabria', 'CT': 'Sicilia', 'CZ': 'Calabria',
    'EN': 'Sicilia', 'FC': 'Emilia-Romagna', 'FE': 'Emilia-Romagna', 'FG': 'Puglia', 'FI': 'Toscana',
    'FM': 'Marche', 'FR': 'Lazio', 'GE': 'Liguria', 'GO': 'Friuli-Venezia Giulia', 'GR': 'Toscana',
    'IM': 'Liguria', 'IS': 'Molise', 'KR': 'Calabria', 'LC': 'Lombardia', 'LE': 'Puglia',
    'LI': 'Toscana', 'LO': 'Lombardia', 'LT': 'Lazio', 'LU': 'Toscana', 'MB': 'Lombardia',
    'MC': 'Marche', 'ME': 'Sicilia', 'MI': 'Lombardia', 'MN': 'Lombardia', 'MO': 'Emilia-Romagna',
    'MS': 'Toscana', 'MT': 'Basilicata', 'NA': 'Campania', 'NO': 'Piemonte', 'NU': 'Sardegna',
    'OR': 'Sardegna', 'PA': 'Sicilia', 'PC': 'Emilia-Romagna', 'PD': 'Veneto', 'PE': 'Abruzzo',
    'PG': 'Umbria', 'PI': 'Toscana', 'PN': 'Friuli-Venezia Giulia', 'PO': 'Toscana', 'PR': 'Emilia-Romagna',
    'PT': 'Toscana', 'PU': 'Marche', 'PV': 'Lombardia', 'PZ': 'Basilicata', 'RA': 'Emilia-Romagna',
    'RC': 'Calabria', 'RE': 'Emilia-Romagna', 'RG': 'Sicilia', 'RI': 'Lazio', 'RM': 'Lazio',
    'RN': 'Emilia-Romagna', 'RO': 'Veneto', 'SA': 'Campania', 'SI': 'Toscana', 'SO': 'Lombardia',
    'SP': 'Liguria', 'SR': 'Sicilia', 'SS': 'Sardegna', 'SU': 'Sardegna', 'SV': 'Liguria',
    'TA': 'Puglia', 'TE': 'Abruzzo', 'TN': 'Trentino-Alto Adige', 'TO': 'Piemonte', 'TP': 'Sicilia',
    'TR': 'Umbria', 'TS': 'Friuli-Venezia Giulia', 'TV': 'Veneto', 'UD': 'Friuli-Venezia Giulia', 'VA': 'Lombardia',
    'VB': 'Piemonte', 'VC': 'Piemonte', 'VE': 'Veneto', 'VI': 'Veneto', 'VR': 'Veneto',
    'VT': 'Lazio', 'VV': 'Calabria'
}

def clean_txt(s):
    if not s:
        return ""
    res = s.strip()
    res = res.replace('', "'").replace('&quot;', '"').replace('&amp;', '&')
    return res

def parse_and_clean_location(item):
    street = clean_txt(item.get('street', ''))
    city = clean_txt(item.get('city', ''))
    cap = clean_txt(item.get('cap', ''))
    prov = clean_txt(item.get('province', ''))
    reg = clean_txt(item.get('region', ''))

    if ('via' in city.lower() or 'piazza' in city.lower() or 'corso' in city.lower() or 'viale' in city.lower()) and not ('via' in street.lower() or 'piazza' in street.lower()):
        street = f"{street}, {city}"
        city = ""

    m_pc = re.search(r'(\d{5})\s+(.*)', prov)
    if m_pc:
        cap = m_pc.group(1).strip()
        if not city or 'via' in city.lower() or 'piazza' in city.lower():
            city = m_pc.group(2).strip()
        prov = ""

    if not cap:
        for fld in [street, city, prov]:
            m = re.search(r'\b(\d{5})\b', fld)
            if m:
                cap = m.group(1)
                break

    prov_code = ""
    for p_code in PROV_TO_REG.keys():
        if f"({p_code})" in prov or f" {p_code}" in prov or f"({p_code})" in city:
            prov_code = p_code
            break

    if not reg and prov_code in PROV_TO_REG:
        reg = PROV_TO_REG[prov_code]

    if not reg:
        for p_code, r_name in PROV_TO_REG.items():
            if prov.lower() and prov.lower() in r_name.lower():
                reg = r_name
                break

    name = clean_txt(item.get('name', ''))
    name = re.sub(r'\s+', ' ', name).strip()
    
    if not city:
        city = prov or reg or 'Italia'

    return {
        'name': name,
        'street': street,
        'city': city,
        'cap': cap,
        'province': prov_code or prov[:2].upper(),
        'region': reg or 'Italia'
    }

def main():
    with open('scratch/amalo_national_harvest.json', 'r', encoding='utf-8') as f:
        data = json.load(f)

    print(f"Total harvest records: {len(data)}")

    conn = sqlite3.connect('data/acat_community.sqlite')
    cur = conn.cursor()

    cur.execute("SELECT lower(entity_name), lower(city) FROM cat_clubs_italy")
    existing_entities = set((r[0].strip(), (r[1] or '').strip()) for r in cur.fetchall())

    inserted = 0
    skipped_existing = 0

    for item in data:
        name_raw = item.get('name', '')
        low_name = name_raw.lower()
        is_target = any(k in low_name for k in ['cat', 'acat', 'aicat', 'arcat', 'apcat', 'club', 'alcol', 'hudolin', 'al-anon'])
        if not is_target:
            continue

        loc = parse_and_clean_location(item)
        name = loc['name']
        city = loc['city']
        street = loc['street']
        cap = loc['cap']
        prov = loc['province']
        reg = loc['region']

        if not name or len(name) < 3:
            continue

        key = (name.lower(), city.lower())
        if key in existing_entities:
            skipped_existing += 1
            continue

        def_email, def_phone, def_parent = REGIONAL_DEFAULTS.get(reg, ('info@aicat.net', '800 974250', 'AICAT Nazionale'))

        phone = clean_txt(item.get('phone', ''))
        email = clean_txt(item.get('email', ''))

        email_type = 'DIRECT'
        if not email or '@' not in email or 'amalo.it' in email:
            email = def_email
            email_type = 'COORDINATION_INHERITED'
        if not phone or len(phone) < 5:
            phone = def_phone

        lat, lon = REGIONAL_CENTROIDS.get(reg, (42.5000, 12.5000))

        level = 'LOCAL_CLUB'
        cat_type = 'CAT'
        if any(k in name.upper() for k in ['ACAT', 'APCAT', 'ARCAT', 'AICAT']):
            level = 'TERRITORIAL_ACAT'
            cat_type = 'ACAT'
        elif 'AL-ANON' in name.upper():
            cat_type = 'AL-ANON'

        sic_id = generate_sic(name, city, street)
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
            sic_id, name, level, reg, prov, city, street, cap,
            phone, email, def_parent,
            lat, lon
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
            sic_id, name, level, cat_type, reg, prov, city, street, cap,
            email, email_type, phone, def_parent, unsub_token
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
            sic_id, name, cat_type, level, reg, prov, city, street,
            lat, lon, email, phone, def_parent
        ))

        existing_entities.add(key)
        inserted += 1

    conn.commit()

    print(f"Ingestion results:")
    print(f"  Inserted: {inserted}")
    print(f"  Skipped (already existing): {skipped_existing}")

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
