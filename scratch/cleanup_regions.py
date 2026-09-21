import sqlite3
import re

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

# 1. Clean quoted regions like 'L'o'm'b'a'r'd'i'a' -> 'Lombardia'
tables = ['cat_clubs_italy', 'crm_club_contacts', 'dependex_world_registry']

for t in tables:
    cur.execute(f"SELECT id, region FROM {t}")
    rows = cur.fetchall()
    for row_id, reg in rows:
        if reg and "'" in reg and reg not in ["Valle d'Aosta"]:
            clean_reg = reg.replace("'", "").strip()
            cur.execute(f"UPDATE {t} SET region = ? WHERE id = ?", (clean_reg, row_id))

conn.commit()

# Standard region dictionary
STANDARD_REGIONS = {
    'abruzzo': 'Abruzzo',
    'basilicata': 'Basilicata',
    'calabria': 'Calabria',
    'campania': 'Campania',
    'emilia romagna': 'Emilia-Romagna',
    'emilia-romagna': 'Emilia-Romagna',
    'friuli venezia giulia': 'Friuli-Venezia Giulia',
    'friuli-venezia giulia': 'Friuli-Venezia Giulia',
    'lazio': 'Lazio',
    'liguria': 'Liguria',
    'lombardia': 'Lombardia',
    'marche': 'Marche',
    'molise': 'Molise',
    'piemonte': 'Piemonte',
    'puglia': 'Puglia',
    'sardegna': 'Sardegna',
    'sicilia': 'Sicilia',
    'toscana': 'Toscana',
    'trentino alto adige': 'Trentino-Alto Adige',
    'trentino-alto adige': 'Trentino-Alto Adige',
    'umbria': 'Umbria',
    'valle daosta': "Valle d'Aosta",
    "valle d'aosta": "Valle d'Aosta",
    'veneto': 'Veneto'
}

# 2. Normalize region names
for t in tables:
    cur.execute(f"SELECT id, region FROM {t}")
    rows = cur.fetchall()
    for row_id, reg in rows:
        if reg:
            low = reg.lower().strip()
            if low in STANDARD_REGIONS:
                std = STANDARD_REGIONS[low]
                if std != reg:
                    cur.execute(f"UPDATE {t} SET region = ? WHERE id = ?", (std, row_id))

conn.commit()

# 3. For any remaining 'Italia', infer region from city / province / cap
PROV_TO_REG = {
    'AG': 'Sicilia', 'AL': 'Piemonte', 'AN': 'Marche', 'AO': "Valle d'Aosta", 'AP': 'Marche',
    'AQ': 'Abruzzo', 'AR': 'Toscana', 'AT': 'Piemonte', 'AV': 'Campania', 'BA': 'Puglia',
    'BG': 'Lombardia', 'BI': 'Piemonte', 'BL': 'Veneto', 'BN': 'Campania', 'BO': 'Emilia-Romagna',
    'BR': 'Puglia', 'BS': 'Lombardia', 'BT': 'Puglia', 'BZ': 'Trentino-Alto Adige', 'CA': 'Sardegna',
    'CB': 'Molise', 'CE': 'Campania', 'CH': 'Abruzzo', 'CL': 'Sicilia', 'CN': 'Piemonte',
    'CO': 'Lombardia', 'CR': 'Lombardia', 'CS': 'Calabria', 'CT': 'Sicilia', 'CZ': 'Calabria',
    'EN': 'Sicilia', 'FC': 'Emilia-Romagna', 'FE': 'Emilia-Romagna', 'FG': 'Puglia', 'FI': 'Toscana',
    'FM': 'Marche', 'FR': 'Lazio', 'GE': 'Liguria', 'GO': 'Friuli-Venezia Giulia', 'GR': 'Toscana',
    'IM': 'Liguria', 'IS': 'Molise', 'KR': 'Calabria', 'LC': 'Lombardia', 'LE': 'Puglia',
    'LI': 'Toscana', 'LO': 'Lombardia', 'LT': 'Lazio', 'LU': 'Toscana', 'MB': 'Lombardia',
    'MC': 'Marche', 'ME': 'Sicilia', 'MI': 'Lombardia', 'MN': 'Lombardia', 'MO': 'Emilia-Romagna',
    'MS': 'Toscana', 'MT': 'Basilicata', 'NA': 'Campania', 'NO': 'Piemonte', 'NU': 'Sardegna',
    'OR': 'Sardegna', 'PA': 'Sicilia', 'PC': 'Emilia-Romagna', 'PD': 'Veneto', 'PE': 'Abruzzo',
    'PG': 'Umbria', 'PI': 'Toscana', 'PN': 'Friuli-Venezia Giulia', 'PO': 'Toscana', 'PR': 'Emilia-Romagna',
    'PT': 'Toscana', 'PU': 'Marche', 'PV': 'Lombardia', 'PZ': 'Basilicata', 'RA': 'Emilia-Romagna',
    'RC': 'Calabria', 'RE': 'Emilia-Romagna', 'RG': 'Sicilia', 'RI': 'Lazio', 'RM': 'Lazio',
    'RN': 'Emilia-Romagna', 'RO': 'Veneto', 'SA': 'Campania', 'SI': 'Toscana', 'SO': 'Lombardia',
    'SP': 'Liguria', 'SR': 'Sicilia', 'SS': 'Sardegna', 'SU': 'Sardegna', 'SV': 'Liguria',
    'TA': 'Puglia', 'TE': 'Abruzzo', 'TN': 'Trentino-Alto Adige', 'TO': 'Piemonte', 'TP': 'Sicilia',
    'TR': 'Umbria', 'TS': 'Friuli-Venezia Giulia', 'TV': 'Veneto', 'UD': 'Friuli-Venezia Giulia', 'VA': 'Lombardia',
    'VB': 'Piemonte', 'VC': 'Piemonte', 'VE': 'Veneto', 'VI': 'Veneto', 'VR': 'Veneto',
    'VT': 'Lazio', 'VV': 'Calabria'
}

# Major cities lookup
CITY_TO_REG = {
    'roma': ('Lazio', 'RM'),
    'milano': ('Lombardia', 'MI'),
    'napoli': ('Campania', 'NA'),
    'torino': ('Piemonte', 'TO'),
    'palermo': ('Sicilia', 'PA'),
    'genova': ('Liguria', 'GE'),
    'bologna': ('Emilia-Romagna', 'BO'),
    'firenze': ('Toscana', 'FI'),
    'bari': ('Puglia', 'BA'),
    'catania': ('Sicilia', 'CT'),
    'verona': ('Veneto', 'VR'),
    'venezia': ('Veneto', 'VE'),
    'messina': ('Sicilia', 'ME'),
    'padova': ('Veneto', 'PD'),
    'trieste': ('Friuli-Venezia Giulia', 'TS'),
    'brescia': ('Lombardia', 'BS'),
    'taranto': ('Puglia', 'TA'),
    'prato': ('Toscana', 'PO'),
    'parma': ('Emilia-Romagna', 'PR'),
    'modena': ('Emilia-Romagna', 'MO'),
    'reggio calabria': ('Calabria', 'RC'),
    'reggio emilia': ('Emilia-Romagna', 'RE'),
    'perugia': ('Umbria', 'PG'),
    'livorno': ('Toscana', 'LI'),
    'ravenna': ('Emilia-Romagna', 'RA'),
    'cagliari': ('Sardegna', 'CA'),
    'foggia': ('Puglia', 'FG'),
    'rimini': ('Emilia-Romagna', 'RN'),
    'salerno': ('Campania', 'SA'),
    'ferrara': ('Emilia-Romagna', 'FE'),
    'sassari': ('Sardegna', 'SS'),
    'latina': ('Lazio', 'LT'),
    'monza': ('Lombardia', 'MB'),
    'siracusa': ('Sicilia', 'SR'),
    'pescara': ('Abruzzo', 'PE'),
    'bergamo': ('Lombardia', 'BG'),
    'forlì': ('Emilia-Romagna', 'FC'),
    'trento': ('Trentino-Alto Adige', 'TN'),
    'vicenza': ('Veneto', 'VI'),
    'terni': ('Umbria', 'TR'),
    'bolzano': ('Trentino-Alto Adige', 'BZ'),
    'novara': ('Piemonte', 'NO'),
    'piacenza': ('Emilia-Romagna', 'PC'),
    'ancona': ('Marche', 'AN'),
    'andria': ('Puglia', 'BT'),
    'arezzo': ('Toscana', 'AR'),
    'udine': ('Friuli-Venezia Giulia', 'UD'),
    'cesena': ('Emilia-Romagna', 'FC'),
    'lecce': ('Puglia', 'LE'),
    'pesaro': ('Marche', 'PU'),
    'barletta': ('Puglia', 'BT'),
    'alessandria': ('Piemonte', 'AL'),
    'la spezia': ('Liguria', 'SP'),
    'pisa': ('Toscana', 'PI'),
    'pistoia': ('Toscana', 'PT'),
    'lucca': ('Toscana', 'LU'),
    'treviso': ('Veneto', 'TV'),
    'catanzaro': ('Calabria', 'CZ'),
    'como': ('Lombardia', 'CO'),
    'busto arsizio': ('Lombardia', 'VA'),
    'brindisi': ('Puglia', 'BR'),
    'grosseto': ('Toscana', 'GR'),
    'varese': ('Lombardia', 'VA'),
    'fiumicino': ('Lazio', 'RM'),
    'asti': ('Piemonte', 'AT'),
    'caserta': ('Campania', 'CE'),
    'ragusa': ('Sicilia', 'RG'),
    'pavia': ('Lombardia', 'PV'),
    'cremona': ('Lombardia', 'CR'),
    'carpi': ('Emilia-Romagna', 'MO'),
    'imola': ('Emilia-Romagna', 'BO'),
    'l\'aquila': ('Abruzzo', 'AQ'),
    'massa': ('Toscana', 'MS'),
    'trapani': ('Sicilia', 'TP'),
    'cosenza': ('Calabria', 'CS'),
    'potenza': ('Basilicata', 'PZ'),
    'castellammare di stabia': ('Campania', 'NA'),
    'crotone': ('Calabria', 'KR'),
    'aprifilia': ('Lazio', 'LT'),
    'viterbo': ('Lazio', 'VT'),
    'carrara': ('Toscana', 'MS'),
    'san severo': ('Puglia', 'FG'),
    'aversa': ('Campania', 'CE'),
    'caltanissetta': ('Sicilia', 'CL'),
    'vittoria': ('Sicilia', 'RG'),
    'savona': ('Liguria', 'SV'),
    'benevento': ('Campania', 'BN'),
    'matera': ('Basilicata', 'MT'),
    'pordenone': ('Friuli-Venezia Giulia', 'PN'),
    'cerignola': ('Puglia', 'FG'),
    'moncalieri': ('Piemonte', 'TO'),
    'scafati': ('Campania', 'SA'),
    'legnano': ('Lombardia', 'MI'),
    'faenza': ('Emilia-Romagna', 'RA'),
    'cuneo': ('Piemonte', 'CN'),
    'manfredonia': ('Puglia', 'FG'),
    'sanremo': ('Liguria', 'IM'),
    'avellino': ('Campania', 'AV'),
    'bitonto': ('Puglia', 'BA'),
    'bagheria': ('Sicilia', 'PA'),
    'portici': ('Campania', 'NA'),
    'teramo': ('Abruzzo', 'TE'),
    'ercolano': ('Campania', 'NA'),
    'bisceglie': ('Puglia', 'BT'),
    'siena': ('Toscana', 'SI'),
    'chieti': ('Abruzzo', 'CH'),
    'aversa': ('Campania', 'CE'),
    'casoria': ('Campania', 'NA'),
    'battipaglia': ('Campania', 'SA'),
    'cinisello balsamo': ('Lombardia', 'MI'),
    'sesto san giovanni': ('Lombardia', 'MI'),
    'guidonia montecelio': ('Lazio', 'RM'),
    'torre del greco': ('Lazio', 'NA'),
    'giugliano in campania': ('Campania', 'NA')
}

cur.execute("SELECT id, entity_name, city, address, province, region FROM cat_clubs_italy WHERE region = 'Italia'")
italia_rows = cur.fetchall()
print(f"Total rows currently in 'Italia': {len(italia_rows)}")

resolved = 0
for row_id, name, city, addr, prov, reg in italia_rows:
    found_reg = None
    found_prov = None

    # Check prov abbreviation
    if prov and prov.upper() in PROV_TO_REG:
        found_reg = PROV_TO_REG[prov.upper()]
        found_prov = prov.upper()

    # Check city in CITY_TO_REG
    if not found_reg and city:
        low_city = city.lower().strip()
        if low_city in CITY_TO_REG:
            found_reg, found_prov = CITY_TO_REG[low_city]
        else:
            for c_k, (r_v, p_v) in CITY_TO_REG.items():
                if c_k in low_city or low_city in c_k:
                    found_reg, found_prov = r_v, p_v
                    break

    # Check name for city mentions
    if not found_reg:
        low_name = name.lower()
        for c_k, (r_v, p_v) in CITY_TO_REG.items():
            if f" {c_k}" in low_name or f"-{c_k}" in low_name or f"({c_k})" in low_name:
                found_reg, found_prov = r_v, p_v
                break

    if found_reg:
        cur.execute("UPDATE cat_clubs_italy SET region = ?, province = COALESCE(NULLIF(province, ''), ?) WHERE id = ?", (found_reg, found_prov, row_id))
        cur.execute("UPDATE crm_club_contacts SET region = ?, province = COALESCE(NULLIF(province, ''), ?) WHERE club_id = ? OR id = ?", (found_reg, found_prov, row_id, row_id))
        resolved += 1

conn.commit()
print(f"Resolved from 'Italia' to specific region: {resolved}")

print("\nFinal Regional Distribution in cat_clubs_italy:")
for row in cur.execute("SELECT region, count(*) FROM cat_clubs_italy GROUP BY region ORDER BY count(*) DESC").fetchall():
    print(f"  {row[0]}: {row[1]}")

conn.close()
