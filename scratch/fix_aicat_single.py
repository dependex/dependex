import sqlite3
import re

DB_PATH = 'c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite'

conn = sqlite3.connect(DB_PATH)
cursor = conn.cursor()

# 1. Controlla figli con parent_sic_id di 52 o 55
cursor.execute("SELECT id, entity_name, parent_sic_id FROM cat_clubs_italy WHERE parent_sic_id IN ('SIC-YSTTB8RX-P54Q4HAX-K', 'SIC-ID-C9F7A0285A20')")
rows = cursor.fetchall()
print(f"Figli che puntavano a 52 o 55: {len(rows)}")
for r in rows:
    print(r)

# Riassegna eventuali figli a 54 ('SIC-A5AE0RB4-VN6Z09JB-X')
cursor.execute("UPDATE cat_clubs_italy SET parent_sic_id = 'SIC-A5AE0RB4-VN6Z09JB-X' WHERE parent_sic_id IN ('SIC-YSTTB8RX-P54Q4HAX-K', 'SIC-ID-C9F7A0285A20')")

# 2. Aggiorna la scheda ID 54 come UNICA SCHEDA NAZIONALE UFFICIALE
clean_address = "Sede Legale: Via Chisimaio 40, 33100 Udine (UD) | Sede Operativa: Via Cave 178, 35136 Padova (PD)"
clean_phone = "800 974250"
clean_phone_sec = "0432 501234 / 366 3625248"
clean_notes = "AICAT - Associazione Nazionale dei Club Alcologici Territoriali (Metodo Hudolin). Coordina le federazioni regionali (ARCAT), le associazioni territoriali (ACAT) e tutti i Club della rete italiana. Numero Verde Gratuito 800 974250."

cursor.execute("""
    UPDATE cat_clubs_italy
    SET entity_name = 'AICAT - Associazione Italiana dei Club Alcologici Territoriali',
        level = 'NATIONAL',
        address = ?,
        phone = ?,
        phone_secondary = ?,
        email = 'segreteria@aicat.net',
        website = 'https://www.aicat.net',
        notes = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = 54
""", (clean_address, clean_phone, clean_phone_sec, clean_notes))

cursor.execute("""
    UPDATE crm_club_contacts
    SET entity_name = 'AICAT - Associazione Italiana dei Club Alcologici Territoriali',
        level = 'NATIONAL',
        address = ?,
        primary_phone = ?,
        phone_secondary = ?,
        primary_email = 'segreteria@aicat.net',
        website = 'https://www.aicat.net',
        notes = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = 54
""", (clean_address, clean_phone, clean_phone_sec, clean_notes))

# 3. Elimina le schede duplicate nazionali ID 52 e ID 55
cursor.execute("DELETE FROM cat_clubs_italy WHERE id IN (52, 55)")
cursor.execute("DELETE FROM crm_club_contacts WHERE id IN (52, 55)")
print("Cancellati ID 52 e 55 da cat_clubs_italy e crm_club_contacts")

# 4. Bonifica presidi territoriali toscani che avevano solo nome 'AICAT'
toscana_mapping = {
    2725: ("ACAT Aretina", "Provincia di Arezzo, Toscana", "info@arcattoscana.it"),
    2726: ("ACAT Empolese Valdelsa", "Empoli, Firenze", "acat.empolese@libero.it"),
    2727: ("ACAT Livorno", "Livorno, Toscana", "alcat_livorno@tiscali.it"),
    2730: ("ACAT Lucca", "Lucca, Toscana", "acatlucca@alice.it"),
    2731: ("ACAT Siena", "Siena, Toscana", "acat.siena@libero.it"),
}

for club_id, (new_name, new_city, email) in toscana_mapping.items():
    cursor.execute("""
        UPDATE cat_clubs_italy
        SET entity_name = ?,
            level = 'TERRITORIAL_ACAT',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    """, (new_name, club_id))
    cursor.execute("""
        UPDATE crm_club_contacts
        SET entity_name = ?,
            level = 'TERRITORIAL_ACAT',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    """, (new_name, club_id))
    print(f"Aggiornato presidio toscano ID {club_id} -> {new_name}")

# 5. Bonifica tutti i singoli club locali / ACAT che avevano prefisso 'AICAT - ' o caratteri corrotti
club_name_fixes = {
    2728: "Club Grosseto Green",
    3295: "Club Grosseto Hudolin",
    3296: "Club Grosseto Nord",
    3297: "ACAT Val di Cornia",
    3298: "Club Follonica",
    3299: "ACAT Isola d'Elba",
    3300: "ACAT Garfagnana",
    3302: "ACAT Versilia",
    2593: "Club La Speranza",
    2595: "Club Insieme",
    3301: "Club Il Gabbiano",
    3305: "Club Il Melograno",
    3306: "Club La Rivincita",
    3310: "Club S. Francesco d'Assisi",
    3312: "Club S. Maria del Rovo",
    3313: "Club S. Ciro",
    3316: "Club Il Timone",
    3317: "Club Nuova Vita",
    3318: "Club Avvenire",
    3319: "Club Abbraccio",
    3320: "Club Rinascere",
    3321: "Club Nuovi Percorsi",
    3322: "Club Degli Angeli",
    3323: "Club La Rosa di Greco",
    3326: "Club Il Girasole",
    3329: "Club La Luce",
}

for club_id, clean_name in club_name_fixes.items():
    cursor.execute("""
        UPDATE cat_clubs_italy
        SET entity_name = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    """, (clean_name, club_id))
    cursor.execute("""
        UPDATE crm_club_contacts
        SET entity_name = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    """, (clean_name, club_id))
    print(f"Bonificato Club ID {club_id} -> {clean_name}")

conn.commit()

# Verifica finale: quante entità contengono ora 'AICAT' nel nome?
cursor.execute("SELECT id, entity_name, level, region, province, city, phone, email FROM cat_clubs_italy WHERE entity_name LIKE '%AICAT%'")
results = cursor.fetchall()
print(f"\n=== VERIFICA FINALE: Entità con nome AICAT rimaste in cat_clubs_italy: {len(results)} ===")
for r in results:
    print(r)

cursor.execute("SELECT id, entity_name, level, region, province, city, primary_phone, primary_email FROM crm_club_contacts WHERE entity_name LIKE '%AICAT%'")
results_crm = cursor.fetchall()
print(f"\n=== VERIFICA FINALE: Entità con nome AICAT rimaste in crm_club_contacts: {len(results_crm)} ===")
for r in results_crm:
    print(r)

conn.close()
