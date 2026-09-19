import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
c = conn.cursor()

c.execute("SELECT COUNT(*) FROM dependex_world_registry WHERE country='Italy' OR country='IT'")
it_total = c.fetchone()[0]

c.execute("SELECT network_level, COUNT(*) FROM dependex_world_registry WHERE country='Italy' OR country='IT' GROUP BY network_level")
levels = c.fetchall()

c.execute("SELECT COUNT(*) FROM dependex_world_registry WHERE (country='Italy' OR country='IT') AND latitude IS NOT NULL AND longitude IS NOT NULL")
geocoded = c.fetchone()[0]

c.execute("SELECT region, COUNT(*) FROM dependex_world_registry WHERE country='Italy' OR country='IT' GROUP BY region ORDER BY COUNT(*) DESC")
regions = c.fetchall()

print(f"Total Italian entries in dependex_world_registry: {it_total}")
print(f"Geocoded Italian entries (with lat/lon): {geocoded}")
print(f"Missing lat/lon: {it_total - geocoded}")
print("\nBy Level:")
for lvl, cnt in levels:
    print(f"  {lvl}: {cnt}")
print("\nBy Region:")
for reg, cnt in regions:
    print(f"  {reg or 'NONE'}: {cnt}")
