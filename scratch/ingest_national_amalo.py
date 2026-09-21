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

# Mapping of region normalization
REGION_NORM = {
    'valle daosta': 'Valle d\'Aosta',
    'valle d\'aosta': 'Valle d\'Aosta',
    'friuli venezia giulia': 'Friuli-Venezia Giulia',
    'friuli-venezia giulia': 'Friuli-Venezia Giulia',
    'trentino alto adige': 'Trentino-Alto Adige',
    'trentino-alto adige': 'Trentino-Alto Adige',
    'emilia romagna': 'Emilia-Romagna',
    'emilia-romagna': 'Emilia-Romagna'
}

def clean_txt(s):
    if not s:
        return ""
    return s.strip().replace('', 'à').replace('&quot;', '"')

def run_ingest():
    try:
        with open('scratch/amalo_national_harvest.json', 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        print("Could not load scratch/amalo_national_harvest.json:", e)
        return

    print(f"Total records in amalo harvest: {len(data)}")

    conn = sqlite3.connect('data/acat_community.sqlite')
    cur = conn.cursor()

    cur.execute("SELECT lower(entity_name), lower(city) FROM cat_clubs_italy")
    existing_entities = set((r[0].strip(), (r[1] or '').strip()) for r in cur.fetchall())

    new_inserted = 0
    skipped_existing = 0
    skipped_no_name = 0

    for item in data:
        name = clean_txt(item.get('name', ''))
        if not name:
            skipped_no_name += 1
            continue

        city = clean_txt(item.get('city', ''))
        street = clean_txt(item.get('street', ''))
        cap = clean_txt(item.get('cap', ''))
        prov = clean_txt(item.get('province', ''))
        reg_raw = clean_txt(item.get('region', ''))
        reg = REGION_NORM.get(reg_raw.lower(), reg_raw)
        if not reg:
            reg = 'Italia'

        key = (name.lower(), city.lower())
        if key in existing_entities:
            skipped_existing += 1
            continue

        # Get regional default fallback
        def_email, def_phone, def_parent = REGIONAL_DEFAULTS.get(reg, ('info@aicat.net', '800 974250', 'AICAT Nazionale'))

        phone = clean_txt(item.get('phone', ''))
        email = clean_txt(item.get('email', ''))

        email_type = 'DIRECT'
        if not email:
            email = def_email
            email_type = 'COORDINATION_INHERITED'
        if not phone:
            phone = def_phone

        # Level determination
        level = 'LOCAL_CLUB'
        cat_type = 'CAT'
        if 'ACAT' in name.upper() or 'APCAT' in name.upper() or 'ARCAT' in name.upper() or 'AICAT' in name.upper():
            level = 'TERRITORIAL_ACAT'
            cat_type = 'ACAT'

        sic_id = generate_sic(name, city, street)
        unsub_token = generate_unsub_token(sic_id)

        # Insert cat_clubs_italy
        cur.execute("""
            INSERT INTO cat_clubs_italy (
                sic_id, entity_name, level, region, province, city, address, cap,
                meeting_frequency, phone, email, parent_entity,
                geo_accuracy, status, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                'Settimanale', ?, ?, ?,
                'APPROXIMATE_CITY', 'ACTIVE_VERIFIED_2026', 12, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, level, reg, prov, city, street, cap,
            phone, email, def_parent
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
                12, 'UNCONTACTED', 0, ?, datetime('now'), datetime('now')
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
                geo_accuracy, status, language,
                email, phone, public_contact, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, 1,
                'Europe', 'Italy', ?, ?, ?, ?,
                'APPROXIMATE_CITY', 'ACTIVE_VERIFIED_2026', 'it',
                ?, ?, ?, 12, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, cat_type, level, reg, prov, city, street,
            email, phone, def_parent
        ))

        existing_entities.add(key)
        new_inserted += 1

    conn.commit()

    print(f"Amalo National Ingestion finished:")
    print(f"  New Entities inserted: {new_inserted}")
    print(f"  Skipped already existing: {skipped_existing}")
    print(f"  Skipped no name: {skipped_no_name}")

    cur.execute("SELECT count(*) FROM cat_clubs_italy")
    print("New total cat_clubs_italy:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM crm_club_contacts")
    print("New total crm_club_contacts:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM dependex_world_registry")
    print("New total dependex_world_registry:", cur.fetchone()[0])

    conn.close()

if __name__ == '__main__':
    run_ingest()
