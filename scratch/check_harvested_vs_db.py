import sqlite3

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

# Test queries for some harvested clubs
test_terms = [
    'Portogruaro', 'Annone Veneto', 'Caorle', 'Concordia Sagittaria',
    'Villafranca', 'Lugagnano', 'Sommacampagna', 'Dossobuono', 'Valeggio',
    'Scandiano', 'Castellarano', 'Cavriago', 'Rubiera', 'Baiso', 'Carpineti',
    'Frosinone', 'Castelliri', 'Isola del Liri',
    'Arzachena', 'Calangianus', 'Ghilarza', 'La Maddalena',
    'Chieri', 'Andezeno', 'Cambiano',
    'Tricesimo', 'Tarcento', 'Buttrio'
]

print("=== CHECKING HARVESTED CLUBS IN DATABASE ===")
for term in test_terms:
    cur.execute(f"SELECT entity_name, city, meeting_day, meeting_time, servitore_insegnante, primary_phone, primary_email FROM crm_club_contacts WHERE entity_name LIKE '%{term}%' OR city LIKE '%{term}%'")
    res = cur.fetchall()
    print(f"Term '{term}': found {len(res)} entries in CRM")
    if res:
        for r in res[:2]:
            print(f"   -> {r[0]} ({r[1]}): day={r[2]}, time={r[3]}, serv={r[4]}, tel={r[5]}, mail={r[6]}")

conn.close()
