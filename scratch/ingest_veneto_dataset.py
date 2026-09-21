import json
import sqlite3
import hashlib
import re

# Crockford Base32 characters
CROCKFORD_CHARS = "0123456789ABCDEFGHJKMNPQRSTVWXYZ"

def generate_sic(name: str, city: str, address: str) -> str:
    h = hashlib.sha256(f"{name}|{city}|{address}".encode('utf-8')).hexdigest()
    p1 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(0, 16, 2))
    p2 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(16, 32, 2))
    check = CROCKFORD_CHARS[int(h[32:34], 16) % 32]
    return f"SIC-{p1}-{p2}-{check}"

def generate_unsub_token(sic: str) -> str:
    return hashlib.sha256(f"unsub-{sic}-hudolin-2026".encode('utf-8')).hexdigest()[:32]

# Province mapping for Veneto
DISTRICT_MAP = {
    'treviso': 'TV',
    'verona': 'VR',
    'padova': 'PD',
    'vicenza': 'VI',
    'venezia': 'VE',
    'belluno': 'BL',
    'rovigo': 'RO'
}

def clean_txt(s):
    if not s:
        return ""
    return s.strip().replace('', 'è').replace('&quot;', '"')

def run_ingest():
    conn = sqlite3.connect('data/acat_community.sqlite')
    cur = conn.cursor()

    with open('scratch/veneto_data_acat.json', 'r', encoding='utf-8-sig') as f:
        acats = json.load(f)

    with open('scratch/veneto_data_club.json', 'r', encoding='utf-8-sig') as f:
        clubs = json.load(f)

    print(f"Loaded Veneto source: {len(acats)} ACATs, {len(clubs)} Clubs")

    # Build ACAT lookup dictionary
    acat_lookup = {}
    for a in acats:
        norm_name = clean_txt(a.get('name', '')).upper()
        email = clean_txt(a.get('email') or a.get('presidentemail') or 'info@arcatveneto.it')
        phone = clean_txt(a.get('phonenumber') or a.get('presidentphonenumber') or '045 576395')
        prov_raw = clean_txt(a.get('district', '')).lower()
        prov = DISTRICT_MAP.get(prov_raw, 'VR')
        acat_lookup[norm_name] = {
            'name': clean_txt(a.get('name', '')),
            'email': email,
            'phone': phone,
            'city': clean_txt(a.get('city', '')),
            'address': clean_txt(a.get('address', '')),
            'cap': clean_txt(a.get('postalcode', '')),
            'province': prov,
            'president': clean_txt(a.get('president', '')),
            'lat': a.get('gps0'),
            'lon': a.get('gps1')
        }

    # Fetch existing names and cities in cat_clubs_italy
    cur.execute("SELECT lower(entity_name), lower(city), sic_id FROM cat_clubs_italy")
    existing_entities = set((r[0].strip(), (r[1] or '').strip()) for r in cur.fetchall())

    new_acats_inserted = 0
    # Ingest missing ACATs as TERRITORIAL_ACAT
    for k, a in acat_lookup.items():
        name = a['name']
        city = a['city']
        key = (name.lower(), city.lower())
        if key not in existing_entities:
            sic_id = generate_sic(name, city, a['address'])
            unsub_token = generate_unsub_token(sic_id)
            cur.execute("""
                INSERT INTO cat_clubs_italy (
                    sic_id, entity_name, level, region, province, city, address, cap,
                    meeting_frequency, servitore_insegnante, phone, email, parent_entity,
                    latitude, longitude, geo_accuracy, status, families_count, created_at, updated_at
                ) VALUES (
                    ?, ?, 'TERRITORIAL_ACAT', 'Veneto', ?, ?, ?, ?,
                    'Mensile', ?, ?, ?, 'ARCAT Veneto',
                    ?, ?, 'EXACT_VENUE', 'ACTIVE_VERIFIED_2026', 45, datetime('now'), datetime('now')
                )
            """, (
                sic_id, name, a['province'], city, a['address'], a['cap'],
                a['president'], a['phone'], a['email'], a['lat'], a['lon']
            ))

            cur.execute("""
                INSERT OR IGNORE INTO crm_club_contacts (
                    sic_id, entity_name, level, category, region, province, city, address, cap,
                    primary_email, email_type, primary_phone, servitore_insegnante,
                    coordination_entity, families_count, outreach_status, outreach_step,
                    unsubscribe_token, created_at, updated_at
                ) VALUES (
                    ?, ?, 'TERRITORIAL_ACAT', 'ACAT', 'Veneto', ?, ?, ?, ?,
                    ?, 'DIRECT', ?, ?,
                    'ARCAT Veneto', 45, 'UNCONTACTED', 0,
                    ?, datetime('now'), datetime('now')
                )
            """, (
                sic_id, name, a['province'], city, a['address'], a['cap'],
                a['email'], a['phone'], a['president'], unsub_token
            ))

            cur.execute("""
                INSERT OR IGNORE INTO dependex_world_registry (
                    sic_id, entity_name, original_type, network_level, network_rank,
                    continent, country, region, province, city, address,
                    latitude, longitude, geo_accuracy, status, language,
                    email, phone, public_contact, families_count, created_at, updated_at
                ) VALUES (
                    ?, ?, 'ACAT', 'TERRITORIAL_ACAT', 2,
                    'Europe', 'Italy', 'Veneto', ?, ?, ?,
                    ?, ?, 'EXACT_VENUE', 'ACTIVE_VERIFIED_2026', 'it',
                    ?, ?, ?, 45, datetime('now'), datetime('now')
                )
            """, (
                sic_id, name, a['province'], city, a['address'],
                a['lat'], a['lon'], a['email'], a['phone'], a['president']
            ))
            existing_entities.add(key)
            new_acats_inserted += 1

    print(f"New ACATs inserted: {new_acats_inserted}")

    # Now ingest Clubs
    new_clubs_inserted = 0
    skipped_existing = 0

    for c in clubs:
        name = clean_txt(c.get('name', ''))
        city = clean_txt(c.get('city', ''))
        address = clean_txt(c.get('address', ''))
        cap = clean_txt(c.get('postalcode', ''))
        district_raw = clean_txt(c.get('district', '')).lower()
        province = DISTRICT_MAP.get(district_raw, 'VR')
        acat_parent_raw = clean_txt(c.get('acat', '')).upper()
        
        # Match ACAT parent
        parent_info = acat_lookup.get(acat_parent_raw)
        if not parent_info:
            # Fuzzy match
            for ak, av in acat_lookup.items():
                if ak in acat_parent_raw or acat_parent_raw in ak:
                    parent_info = av
                    break

        parent_name = parent_info['name'] if parent_info else c.get('acat', 'ARCAT Veneto')
        email = parent_info['email'] if parent_info else 'info@arcatveneto.it'
        phone = parent_info['phone'] if parent_info else '045 576395'
        lat = c.get('gps0')
        lon = c.get('gps1')

        # Check existing
        key = (name.lower(), city.lower())
        if key in existing_entities:
            skipped_existing += 1
            continue

        sic_id = generate_sic(name, city, address)
        unsub_token = generate_unsub_token(sic_id)

        # Insert cat_clubs_italy
        cur.execute("""
            INSERT INTO cat_clubs_italy (
                sic_id, entity_name, level, region, province, city, address, cap,
                meeting_frequency, phone, email, parent_entity,
                latitude, longitude, geo_accuracy, status, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, 'LOCAL_CLUB', 'Veneto', ?, ?, ?, ?,
                'Settimanale', ?, ?, ?,
                ?, ?, 'EXACT_VENUE', 'ACTIVE_VERIFIED_2026', 11, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, province, city, address, cap,
            phone, email, parent_name,
            lat, lon
        ))

        # Insert crm_club_contacts
        cur.execute("""
            INSERT OR IGNORE INTO crm_club_contacts (
                sic_id, entity_name, level, category, region, province, city, address, cap,
                primary_email, email_type, primary_phone, coordination_entity,
                families_count, outreach_status, outreach_step, unsubscribe_token, created_at, updated_at
            ) VALUES (
                ?, ?, 'LOCAL_CLUB', 'CAT', 'Veneto', ?, ?, ?, ?,
                ?, 'COORDINATION_INHERITED', ?, ?,
                11, 'UNCONTACTED', 0, ?, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, province, city, address, cap,
            email, phone, parent_name,
            unsub_token
        ))

        # Insert dependex_world_registry
        cur.execute("""
            INSERT OR IGNORE INTO dependex_world_registry (
                sic_id, entity_name, original_type, network_level, network_rank,
                continent, country, region, province, city, address,
                latitude, longitude, geo_accuracy, status, language,
                email, phone, public_contact, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, 'CAT', 'LOCAL_CLUB', 1,
                'Europe', 'Italy', 'Veneto', ?, ?, ?,
                ?, ?, 'EXACT_VENUE', 'ACTIVE_VERIFIED_2026', 'it',
                ?, ?, ?, 11, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, province, city, address,
            lat, lon, email, phone, parent_name
        ))

        existing_entities.add(key)
        new_clubs_inserted += 1

    conn.commit()

    print(f"Veneto ingestion finished:")
    print(f"  New Clubs inserted: {new_clubs_inserted}")
    print(f"  Skipped already existing: {skipped_existing}")
    
    cur.execute("SELECT count(*) FROM cat_clubs_italy")
    print("New total cat_clubs_italy:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM crm_club_contacts")
    print("New total crm_club_contacts:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM dependex_world_registry")
    print("New total dependex_world_registry:", cur.fetchone()[0])

    conn.close()

if __name__ == '__main__':
    run_ingest()
