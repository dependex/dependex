import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

print('=== 1. cat_clubs_italy ===')
cur.execute('SELECT count(*) FROM cat_clubs_italy')
print('Total rows:', cur.fetchone()[0])

cur.execute('SELECT level, count(*) FROM cat_clubs_italy GROUP BY level')
print('Levels:', cur.fetchall())

cur.execute('SELECT region, count(*) FROM cat_clubs_italy GROUP BY region ORDER BY count(*) DESC')
print('Regions count:', len(cur.fetchall()))
cur.execute('SELECT region, count(*) FROM cat_clubs_italy GROUP BY region ORDER BY count(*) DESC')
for r in cur.fetchall():
    print(f"  {r[0]}: {r[1]}")

print('\n=== 2. crm_club_contacts ===')
cur.execute('SELECT count(*) FROM crm_club_contacts')
print('Total rows:', cur.fetchone()[0])

cur.execute('SELECT category, count(*) FROM crm_club_contacts GROUP BY category')
print('Categories:', cur.fetchall())

cur.execute('SELECT level, count(*) FROM crm_club_contacts GROUP BY level')
print('Levels:', cur.fetchall())

cur.execute('SELECT email_type, count(*) FROM crm_club_contacts GROUP BY email_type')
print('Email types:', cur.fetchall())

print('\n=== 3. dependex_world_registry ===')
cur.execute('SELECT count(*) FROM dependex_world_registry')
print('Total rows:', cur.fetchone()[0])

cur.execute('SELECT network_level, count(*) FROM dependex_world_registry GROUP BY network_level')
print('Network levels:', cur.fetchall())

cur.execute('SELECT country, count(*) FROM dependex_world_registry GROUP BY country ORDER BY count(*) DESC LIMIT 10')
print('Top 10 countries:')
for r in cur.fetchall():
    print(f"  {r[0]}: {r[1]}")

print('\n=== 4. Specific Search for AICAT, ARCAT, APCAT, ACAT ===')
for prefix in ['AICAT', 'ARCAT', 'APCAT', 'ACAT', 'CAT ']:
    cur.execute(f"SELECT count(*) FROM cat_clubs_italy WHERE entity_name LIKE '%{prefix}%'")
    c1 = cur.fetchone()[0]
    cur.execute(f"SELECT count(*) FROM crm_club_contacts WHERE entity_name LIKE '%{prefix}%'")
    c2 = cur.fetchone()[0]
    cur.execute(f"SELECT count(*) FROM dependex_world_registry WHERE entity_name LIKE '%{prefix}%'")
    c3 = cur.fetchone()[0]
    print(f"  Matches for '{prefix}': cat_clubs_italy={c1}, crm={c2}, world_registry={c3}")

cur.execute("SELECT sum(families_count) FROM cat_clubs_italy")
print('\nTotal families estimated in cat_clubs_italy:', cur.fetchone()[0])

conn.close()
