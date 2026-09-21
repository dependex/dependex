import sqlite3

DB_PATH = 'c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite'
conn = sqlite3.connect(DB_PATH)
cursor = conn.cursor()

def clean_str(s):
    if not isinstance(s, str):
        return s
    # Se la stringa contiene byte nulli o \ufffd alternati
    if '\x00' in s:
        s = s.replace('\x00', '')
    if '\ufffd' in s:
        s = s.replace('\ufffd', '')
    return s.strip()

tables = ['cat_clubs_italy', 'crm_club_contacts', 'dependex_world_registry']

for table in tables:
    cursor.execute(f"PRAGMA table_info({table})")
    cols = [c[1] for c in cursor.fetchall() if c[2] == 'TEXT']
    
    cursor.execute(f"SELECT id, {', '.join(cols)} FROM {table}")
    rows = cursor.fetchall()
    
    updated_count = 0
    for r in rows:
        row_id = r[0]
        vals = r[1:]
        new_vals = [clean_str(v) for v in vals]
        if new_vals != list(vals):
            set_clause = ', '.join([f"{c} = ?" for c in cols])
            cursor.execute(f"UPDATE {table} SET {set_clause} WHERE id = ?", (*new_vals, row_id))
            updated_count += 1
            
    print(f"Tabella {table}: puliti {updated_count} record con caratteri UTF-16/nulli")

conn.commit()

# Verifica ID 584, 587, 607, 647
cursor.execute("SELECT id, entity_name, city, address, phone, email FROM cat_clubs_italy WHERE id IN (584, 587, 607, 647)")
print("\nVerifica post-bonifica:")
for r in cursor.fetchall():
    print(r)

conn.close()
