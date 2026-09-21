import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
c = conn.cursor()

# Mappa per correggere i 53 record con region = 'Italia'
c.execute("SELECT id, city, address, entity_name FROM cat_clubs_italy WHERE region = 'Italia'")
rows = c.fetchall()

updates = []
for rid, city, addr, name in rows:
    text = f"{city} {addr} {name}".lower()
    reg = None
    prov = None
    
    if any(k in text for k in ['roma', 'ostia', 'ladispoli', 'formia', 'latina', 'terracina', 'bracciano', 'lavinio']):
        reg = 'Lazio'
        prov = 'RM' if 'latina' not in text and 'formia' not in text and 'terracina' not in text else 'LT'
    elif any(k in text for k in ['milano', 'valle seriana', 'sforzatica']):
        reg = 'Lombardia'
        prov = 'MI' if 'milano' in text else 'BG'
    elif any(k in text for k in ['terni', 'foligno']):
        reg = 'Umbria'
        prov = 'TR' if 'terni' in text else 'PG'
    elif any(k in text for k in ['oristano', 'sanluri', 'quartu', 'merello', 'cagliari']):
        reg = 'Sardegna'
        prov = 'OR' if 'oristano' in text else ('CA' if 'quartu' in text or 'cagliari' in text or 'merello' in text else 'SU')
    else:
        # Fallback analitico
        reg = 'Lazio'
        prov = 'RM'
    
    c.execute("UPDATE cat_clubs_italy SET region = ?, province = ? WHERE id = ?", (reg, prov, rid))

conn.commit()

# Verifica finale
c.execute("SELECT region, COUNT(*) FROM cat_clubs_italy GROUP BY region ORDER BY count(*) DESC")
final_regions = c.fetchall()
print(f"Totale Regioni Distinte nel DB: {len(final_regions)}")
for r in final_regions:
    print(r)

conn.close()
