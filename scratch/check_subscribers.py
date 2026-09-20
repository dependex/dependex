import sqlite3
import os
import glob

print("Ricerca iscritti evento Taglio di Po nei database...")

db_candidates = [
    r"c:\81PLUS_GLOBAL_MASTER\dependex.social\data\acat_community.sqlite",
    r"c:\81PLUS_GLOBAL_MASTER\dependex.social\data\dependex.db",
    r"c:\81PLUS_GLOBAL_MASTER\dependex.social\automation\emailflux\data\emailflux.db",
    r"c:\81PLUS_GLOBAL_MASTER\81plus.net\arm81.db",
    r"c:\81PLUS_GLOBAL_MASTER\81plus.net\orders81.sqlite"
]

# Aggiungi eventuali altri file .sqlite o .db trovati
for db_file in glob.glob(r"c:\81PLUS_GLOBAL_MASTER\dependex.social\data\*.sqlite"):
    if db_file not in db_candidates:
        db_candidates.append(db_file)

for db_path in db_candidates:
    if not os.path.exists(db_path):
        continue
    print(f"\n==========================================")
    print(f"DATABASE: {db_path}")
    print(f"==========================================")
    try:
        conn = sqlite3.connect(db_path)
        conn.row_factory = sqlite3.Row
        cur = conn.cursor()
        
        tables = [row[0] for row in cur.execute("SELECT name FROM sqlite_master WHERE type='table'").fetchall()]
        print(f"Tabelle ({len(tables)}): {', '.join(tables)}")
        
        for table in tables:
            # Esamina ogni tabella che possa contenere iscrizioni o eventi
            try:
                columns = [col[1] for col in cur.execute(f"PRAGMA table_info({table})").fetchall()]
                # Cerca righe con riferimento a 'taglio' o 'po' o in tabelle 'event_bookings', 'orders', 'leads'
                query = f"SELECT * FROM {table}"
                cur.execute(query)
                rows = cur.fetchall()
                matching_rows = []
                for r in rows:
                    r_dict = dict(r)
                    row_str = " ".join([str(v) for v in r_dict.values()]).lower()
                    if "taglio" in row_str or "po" in row_str or "ottobre" in row_str or "esperienziale" in row_str or "corso" in row_str:
                        matching_rows.append(r_dict)
                    elif table in ["event_bookings", "bookings", "iscrizioni", "evento_iscrizioni", "orders"]:
                        matching_rows.append(r_dict)
                
                if matching_rows:
                    print(f"\n--> Tabella '{table}' ({len(matching_rows)} record rilevanti trovati):")
                    for idx, mr in enumerate(matching_rows, 1):
                        print(f"  [{idx}] {mr}")
            except Exception as ex_tbl:
                pass
                
        conn.close()
    except Exception as e:
        print(f"Errore apertura {db_path}: {e}")
