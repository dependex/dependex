import sqlite3

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
c = conn.cursor()

c.execute("SELECT id, sic_id, entity_name, network_level, country, region, province, city FROM dependex_world_registry WHERE sic_id IN ('SIC-YSTTB8RX-P54Q4HAX-K', 'SIC-ID-C9F7A0285A20', 'SIC-A5AE0RB4-VN6Z09JB-X') OR entity_name LIKE '%AICAT%'")
rows = c.fetchall()
print(f"Total matching in dependex_world_registry: {len(rows)}")
for r in rows:
    print(r)

conn.close()
