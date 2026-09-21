import sqlite3

con = sqlite3.connect('data/acat_community.sqlite')
cur = con.cursor()

print("Sincronizzazione email e telefoni tra crm_club_contacts e cat_clubs_italy...")
cur.execute("""
    UPDATE cat_clubs_italy
    SET email = (
        SELECT r.primary_email 
        FROM crm_club_contacts r 
        WHERE r.sic_id = cat_clubs_italy.sic_id
    )
    WHERE (email IS NULL OR TRIM(email) = '')
      AND EXISTS (
        SELECT 1 FROM crm_club_contacts r 
        WHERE r.sic_id = cat_clubs_italy.sic_id 
          AND r.primary_email IS NOT NULL 
          AND TRIM(r.primary_email) != ''
      )
""")
updated_emails = cur.rowcount
print(f"cat_clubs_italy: email aggiornate = {updated_emails}")

cur.execute("""
    UPDATE cat_clubs_italy
    SET phone = (
        SELECT r.primary_phone 
        FROM crm_club_contacts r 
        WHERE r.sic_id = cat_clubs_italy.sic_id
    )
    WHERE (phone IS NULL OR TRIM(phone) = '')
      AND EXISTS (
        SELECT 1 FROM crm_club_contacts r 
        WHERE r.sic_id = cat_clubs_italy.sic_id 
          AND r.primary_phone IS NOT NULL 
          AND TRIM(r.primary_phone) != ''
      )
""")
updated_phones = cur.rowcount
print(f"cat_clubs_italy: telefoni aggiornati = {updated_phones}")

print("Sincronizzazione email su dependex_world_registry...")
cur.execute("""
    UPDATE dependex_world_registry
    SET email = (
        SELECT r.primary_email 
        FROM crm_club_contacts r 
        WHERE r.sic_id = dependex_world_registry.sic_id
    )
    WHERE (email IS NULL OR TRIM(email) = '')
      AND EXISTS (
        SELECT 1 FROM crm_club_contacts r 
        WHERE r.sic_id = dependex_world_registry.sic_id 
          AND r.primary_email IS NOT NULL 
          AND TRIM(r.primary_email) != ''
      )
""")
updated_world = cur.rowcount
print(f"dependex_world_registry: email aggiornate = {updated_world}")

con.commit()

cur.execute("SELECT COUNT(*), SUM(CASE WHEN email IS NOT NULL AND TRIM(email) != '' THEN 1 ELSE 0 END) FROM cat_clubs_italy")
cat_tot, cat_em = cur.fetchone()
print(f"\nNuovo stato cat_clubs_italy: Totale {cat_tot} | Con Email: {cat_em} ({cat_em/cat_tot*100:.1f}%)")

cur.execute("SELECT COUNT(*), SUM(CASE WHEN email IS NOT NULL AND TRIM(email) != '' THEN 1 ELSE 0 END) FROM dependex_world_registry")
w_tot, w_em = cur.fetchone()
print(f"Nuovo stato dependex_world_registry: Totale {w_tot} | Con Email: {w_em}")
