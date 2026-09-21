import sqlite3

DB_PATH = 'c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite'
conn = sqlite3.connect(DB_PATH)
cursor = conn.cursor()

# 1. Controlla e aggiorna parent_sic_id in dependex_world_registry
cursor.execute("UPDATE dependex_world_registry SET parent_sic_id = 'SIC-A5AE0RB4-VN6Z09JB-X' WHERE parent_sic_id IN ('SIC-YSTTB8RX-P54Q4HAX-K', 'SIC-ID-C9F7A0285A20')")

# 2. Aggiorna la scheda ufficiale AICAT
clean_address = "Sede Legale: Via Chisimaio 40, 33100 Udine (UD) | Sede Operativa: Via Cave 178, 35136 Padova (PD)"
clean_phone = "800 974250"
clean_notes = "AICAT - Associazione Nazionale dei Club Alcologici Territoriali (Metodo Hudolin). Coordina le federazioni regionali (ARCAT), le associazioni territoriali (ACAT) e tutti i Club della rete italiana. Numero Verde Gratuito 800 974250."

cursor.execute("""
    UPDATE dependex_world_registry
    SET entity_name = 'AICAT - Associazione Italiana dei Club Alcologici Territoriali',
        network_level = 'NATIONAL',
        region = 'Friuli-Venezia Giulia',
        province = 'UD',
        city = 'Udine',
        address = ?,
        phone = ?,
        email = 'segreteria@aicat.net',
        website = 'https://www.aicat.net',
        notes = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-A5AE0RB4-VN6Z09JB-X'
""", (clean_address, clean_phone, clean_notes))

# 3. Elimina i duplicati nazionali da dependex_world_registry
cursor.execute("DELETE FROM dependex_world_registry WHERE sic_id IN ('SIC-YSTTB8RX-P54Q4HAX-K', 'SIC-ID-C9F7A0285A20')")
print("Cancellati record duplicati nazionali da dependex_world_registry")

# 4. Rinomina i 5 presidi toscani
toscana_world = {
    'SIC-MP5GKEZK-F0EBDHMG-T': ("ACAT Aretina", "TERRITORIAL"),
    'SIC-4K5J9WXD-RNPXEFRY-8': ("ACAT Empolese Valdelsa", "TERRITORIAL"),
    'SIC-JMAY2Y3Z-DT2BW138-K': ("ACAT Livorno", "TERRITORIAL"),
    'SIC-3MAD9H8Q-X36S80NK-W': ("ACAT Lucca", "TERRITORIAL"),
    'SIC-CBS33Z08-MJZG6S31-S': ("ACAT Siena", "TERRITORIAL"),
}

for sic_id, (name, level) in toscana_world.items():
    cursor.execute("""
        UPDATE dependex_world_registry
        SET entity_name = ?,
            network_level = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE sic_id = ?
    """, (name, level, sic_id))
    print(f"Aggiornato {sic_id} -> {name} ({level})")

# 5. Bonifica tutti i singoli club locali / ACAT
club_fixes_world = {
    'SIC-5NA1MVCS-RMHSRZJ0-3': "Club Grosseto Green",
    'SIC-HHVNRN1R-WY5FM0B2-N': "Club Grosseto Hudolin",
    'SIC-2NG0Y4XN-N5X3XZR5-R': "Club Grosseto Nord",
    'SIC-RTCRXDBA-RRGQ3H9P-7': "ACAT Val di Cornia",
    'SIC-6CS26NQD-0K1BJQ22-E': "Club Follonica",
    'SIC-SZQSZHFN-SQNEW1TY-0': "ACAT Isola d'Elba",
    'SIC-SR2WDNS2-MAVA30GP-C': "ACAT Garfagnana",
    'SIC-DVNBD2YY-DVHDKNB4-B': "ACAT Versilia",
    'SIC-1559E3FG-CCRT2BCW-5': "Club La Speranza",
    'SIC-7ZSJ0PCC-40XT3ZQ1-N': "Club Insieme",
    'SIC-J61HTK4K-F1Z3C0GV-Z': "Club Il Gabbiano",
    'SIC-ZDW4BYRS-TBKTBREE-A': "Club Il Melograno",
    'SIC-Y7E7FPZA-3K4XW4CS-1': "Club La Rivincita",
    'SIC-HW6E5GZ5-MA45VRGN-N': "Club S. Francesco d'Assisi",
    'SIC-FFARMEE9-JVYM7D6J-B': "Club S. Maria del Rovo",
    'SIC-KGQA5FGM-TRS8ZPVV-G': "Club S. Ciro",
    'SIC-BE2M3JWD-PJ1VMBM7-T': "Club Il Timone",
    'SIC-MZPTSXDQ-6RD0KD4Z-0': "Club Nuova Vita",
    'SIC-5HY4XN05-63MG42VA-0': "Club Avvenire",
    'SIC-TVY9M7RH-B6X0MPRQ-4': "Club Abbraccio",
    'SIC-3PQ101Y3-2NRCGSGW-D': "Club Rinascere",
    'SIC-4JVNQ3YZ-HJG0GM07-P': "Club Nuovi Percorsi",
    'SIC-NB3WV0S9-M018BN9X-5': "Club Degli Angeli",
    'SIC-4M4A22CR-S56DMGVK-8': "Club La Rosa di Greco",
    'SIC-AY3XN8G7-2HTR6XWQ-E': "Club Il Girasole",
    'SIC-X2D7A1GX-43HKWPG9-J': "Club La Luce",
}

for sic_id, clean_name in club_fixes_world.items():
    cursor.execute("""
        UPDATE dependex_world_registry
        SET entity_name = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE sic_id = ?
    """, (clean_name, sic_id))

conn.commit()

# Verifica finale
cursor.execute("SELECT id, sic_id, entity_name, network_level, country, region, city FROM dependex_world_registry WHERE entity_name LIKE '%AICAT%'")
results = cursor.fetchall()
print(f"\n=== VERIFICA FINALE: Entità con nome AICAT rimaste in dependex_world_registry: {len(results)} ===")
for r in results:
    print(r)

# Conteggio totale entità nazionali in Italia
cursor.execute("SELECT id, entity_name, network_level, country, region FROM dependex_world_registry WHERE network_level = 'NATIONAL' AND country = 'Italy'")
nats = cursor.fetchall()
print(f"\n=== Entità NATIONAL Italy in dependex_world_registry: {len(nats)} ===")
for n in nats:
    print(n)

conn.close()
