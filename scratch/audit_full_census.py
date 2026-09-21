import sqlite3
import os

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

# All tables
cur.execute("SELECT name FROM sqlite_master WHERE type='table'")
tables = [r[0] for r in cur.fetchall()]
print(f"Total tables in DB: {len(tables)}")
club_tables = [t for t in tables if any(k in t.lower() for k in ['club', 'acat', 'cat', 'registry', 'crm'])]
print(f"Club-related tables: {club_tables}")

for t in club_tables:
    try:
        cur.execute(f"SELECT count(*) FROM {t}")
        cnt = cur.fetchone()[0]
        print(f"  Table '{t}': {cnt} rows")
    except Exception as e:
        print(f"  Table '{t}': error {e}")

print("\n=== crm_club_contacts EMAIL AUDIT ===")
cur.execute("SELECT count(*) FROM crm_club_contacts")
tot = cur.fetchone()[0]
cur.execute("SELECT count(DISTINCT primary_email) FROM crm_club_contacts")
dist_email = cur.fetchone()[0]
cur.execute("SELECT email_type, count(*) FROM crm_club_contacts GROUP BY email_type")
by_type = cur.fetchall()
print(f"Total rows: {tot}")
print(f"Distinct primary emails: {dist_email}")
print(f"By email_type: {by_type}")

print("\nTop repeated coordination emails (inherited):")
cur.execute("""
    SELECT primary_email, count(*), group_concat(DISTINCT entity_name) 
    FROM crm_club_contacts 
    WHERE email_type = 'COORDINATION_INHERITED'
    GROUP BY primary_email 
    ORDER BY count(*) DESC 
    LIMIT 10
""")
for email, cnt, sample in cur.fetchall():
    sample_short = sample[:80] + '...' if len(sample) > 80 else sample
    print(f"  {email}: {cnt} clubs (e.g. {sample_short})")

print("\n=== DIRECT EMAILS SAMPLE ===")
cur.execute("""
    SELECT entity_name, primary_email, city, region 
    FROM crm_club_contacts 
    WHERE email_type = 'DIRECT' 
    LIMIT 10
""")
for row in cur.fetchall():
    print(f"  {row[0]} ({row[2]}, {row[3]}): {row[1]}")

conn.close()
