import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
c = conn.cursor()
c.execute("SELECT name FROM sqlite_master WHERE type='table'")
tables = [row[0] for row in c.fetchall()]
print("Tables:", tables)

for t in tables:
    if any(k in t.lower() for k in ['club', 'acat', 'arcat', 'aicat', 'registry', 'dependex', 'geo', 'node']):
        try:
            c.execute(f"SELECT COUNT(*) FROM {t}")
            cnt = c.fetchone()[0]
            c.execute(f"PRAGMA table_info({t})")
            cols = [row[1] for row in c.fetchall()]
            print(f"Table '{t}': {cnt} rows | Cols: {cols}")
        except Exception as e:
            print(f"Error on {t}: {e}")
