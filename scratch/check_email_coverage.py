import sqlite3

con = sqlite3.connect('data/acat_community.sqlite')
cur = con.cursor()

print("=== COPERTURA EMAIL NEL CRM MASTER (crm_club_contacts) ===")
cur.execute("SELECT COUNT(*) FROM crm_club_contacts")
tot_crm = cur.fetchone()[0]

cur.execute("SELECT COUNT(*) FROM crm_club_contacts WHERE primary_email IS NOT NULL AND TRIM(primary_email) != ''")
with_email = cur.fetchone()[0]

cur.execute("SELECT COUNT(*) FROM crm_club_contacts WHERE primary_email IS NULL OR TRIM(primary_email) = ''")
without_email = cur.fetchone()[0]

print(f"Totale Entita' nel CRM: {tot_crm}")
print(f"Entita' CON recapito email: {with_email} ({with_email/tot_crm*100:.1f}%)")
print(f"Entita' SENZA email: {without_email}")

print("\n--- DETTAGLIO PER LIVELLO GERARCHICO (level) ---")
cur.execute("""
    SELECT level, 
           COUNT(*) as total, 
           SUM(CASE WHEN primary_email IS NOT NULL AND TRIM(primary_email) != '' THEN 1 ELSE 0 END) as has_email,
           SUM(CASE WHEN primary_email IS NULL OR TRIM(primary_email) = '' THEN 1 ELSE 0 END) as missing_email
    FROM crm_club_contacts 
    GROUP BY level
    ORDER BY total DESC
""")
for row in cur.fetchall():
    print(f"Livello: {row[0]:<15} | Totale: {row[1]:<4} | Con Email: {row[2]:<4} | Senza Email: {row[3]:<4}")

print("\n--- DETTAGLIO PER TIPO EMAIL (email_type: DIRETTA vs EREDITATA) ---")
cur.execute("""
    SELECT email_type, COUNT(*) 
    FROM crm_club_contacts 
    GROUP BY email_type
    ORDER BY COUNT(*) DESC
""")
for row in cur.fetchall():
    print(f"Tipo Email: {str(row[0]):<20} | Conteggio: {row[1]}")

print("\n=== VERIFICA AICAT / ARCAT / APCAT / ACAT SPECIFICHE ===")
for prefix in ['AICAT', 'ARCAT', 'APCAT', 'ACAT']:
    cur.execute(f"SELECT COUNT(*), SUM(CASE WHEN primary_email IS NOT NULL AND TRIM(primary_email) != '' THEN 1 ELSE 0 END) FROM crm_club_contacts WHERE entity_name LIKE '%{prefix}%'")
    res = cur.fetchone()
    print(f"{prefix}: Trovati {res[0]} record | Con Email: {res[1]}")

print("\n=== TELEFONI E RECAPITI DIRETTO ===")
cur.execute("SELECT COUNT(*) FROM crm_club_contacts WHERE primary_phone IS NOT NULL AND TRIM(primary_phone) != ''")
with_tel = cur.fetchone()[0]
print(f"Copertura Telefonica: {with_tel} su {tot_crm} ({with_tel/tot_crm*100:.1f}%)")

print("\n=== TABELLA CAT_CLUBS_ITALY ===")
cur.execute("SELECT COUNT(*), SUM(CASE WHEN email IS NOT NULL AND TRIM(email) != '' THEN 1 ELSE 0 END), SUM(CASE WHEN phone IS NOT NULL AND TRIM(phone) != '' THEN 1 ELSE 0 END) FROM cat_clubs_italy")
cat_tot, cat_em, cat_tel = cur.fetchone()
print(f"cat_clubs_italy: Totale {cat_tot} | Con Email: {cat_em} | Con Telefono: {cat_tel}")

print("\n=== TABELLA DEPENDEX_WORLD_REGISTRY ===")
cur.execute("SELECT COUNT(*), SUM(CASE WHEN email IS NOT NULL AND TRIM(email) != '' THEN 1 ELSE 0 END) FROM dependex_world_registry")
w_tot, w_em = cur.fetchone()
print(f"dependex_world_registry: Totale {w_tot} | Con Email: {w_em}")
