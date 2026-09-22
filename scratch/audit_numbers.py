import sqlite3
import re
import os
import glob

con = sqlite3.connect('data/acat_community.sqlite')
cur = con.cursor()

print("=== 1. DATABASE ACTUAL LIVE STATS ===")
tables = [
    'cat_clubs_italy', 'dependex_world_registry', 'crm_club_contacts',
    'network_entities', 'events', 'event_bookings', 'users', 'roles',
    'funnel_events', 'site_live_sessions'
]
for t in tables:
    cnt = cur.execute(f"SELECT count(*) FROM {t}").fetchone()[0]
    print(f"Table {t}: {cnt} rows")

print("\n--- cat_clubs_italy levels ---")
for r in cur.execute("SELECT level, count(*) FROM cat_clubs_italy GROUP BY level").fetchall():
    print(f"  {r[0]}: {r[1]}")

print("\n--- cat_clubs_italy status ---")
for r in cur.execute("SELECT status, count(*) FROM cat_clubs_italy GROUP BY status").fetchall():
    print(f"  {r[0]}: {r[1]}")

print("\n--- cat_clubs_italy meeting day set ---")
day_filled = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE meeting_day IS NOT NULL AND meeting_day != ''").fetchone()[0]
day_empty = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE meeting_day IS NULL OR meeting_day = ''").fetchone()[0]
print(f"  meeting_day populated: {day_filled}, empty: {day_empty}")

phone_filled = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE phone IS NOT NULL AND phone != ''").fetchone()[0]
email_filled = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE email IS NOT NULL AND email != ''").fetchone()[0]
address_filled = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE address IS NOT NULL AND address != ''").fetchone()[0]
coords_filled = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE latitude != 0.0 AND longitude != 0.0").fetchone()[0]
print(f"  phone populated: {phone_filled}, email: {email_filled}, address: {address_filled}, coords: {coords_filled}")

print("\n--- dependex_world_registry breakdown ---")
for r in cur.execute("SELECT country, count(*) FROM dependex_world_registry GROUP BY country ORDER BY count(*) DESC LIMIT 10").fetchall():
    print(f"  {r[0]}: {r[1]}")

for r in cur.execute("SELECT network_level, count(*) FROM dependex_world_registry WHERE country='Italy' GROUP BY network_level").fetchall():
    print(f"  Italy level {r[0]}: {r[1]}")

total_fam = cur.execute("SELECT SUM(families_count) FROM dependex_world_registry WHERE country='Italy' AND network_level != 'NATIONAL'").fetchone()[0]
print(f"  total_families (Italy, non-national): {total_fam}")

print("\n--- network_entities breakdown ---")
for r in cur.execute("SELECT level, count(*) FROM network_entities GROUP BY level").fetchall():
    print(f"  level {r[0]}: {r[1]}")
for r in cur.execute("SELECT country, count(*) FROM network_entities GROUP BY country").fetchall():
    print(f"  country {r[0]}: {r[1]}")

print("\n--- crm_club_contacts breakdown ---")
for r in cur.execute("SELECT outreach_status, count(*) FROM crm_club_contacts GROUP BY outreach_status").fetchall():
    print(f"  status {r[0]}: {r[1]}")
