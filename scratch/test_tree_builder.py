import sqlite3
import json

conn = sqlite3.connect('c:/81PLUS_GLOBAL_MASTER/dependex.social/data/acat_community.sqlite')
cursor = conn.cursor()

# Estraiamo tutti i nodi
cursor.execute("""
    SELECT id, sic_id, entity_name, level, region, province, city, address, 
           phone, phone_secondary, email, website, servitore_insegnante,
           meeting_day, meeting_time, families_count, notes
    FROM cat_clubs_italy
    ORDER BY region, province, city, entity_name
""")
rows = cursor.fetchall()
columns = [
    'id', 'sic_id', 'entity_name', 'level', 'region', 'province', 'city', 'address',
    'phone', 'phone_secondary', 'email', 'website', 'servitore_insegnante',
    'meeting_day', 'meeting_time', 'families_count', 'notes'
]

items = [dict(zip(columns, r)) for r in rows]

# Nodo radice Nazionale AICAT
aicat_node = next((i for i in items if i['level'] == 'NATIONAL'), None)
if not aicat_node:
    aicat_node = {
        'id': 54,
        'sic_id': 'SIC-A5AE0RB4-VN6Z09JB-X',
        'entity_name': 'AICAT - Associazione Italiana dei Club Alcologici Territoriali',
        'level': 'NATIONAL',
        'city': 'Udine',
        'region': 'Italia',
        'phone': '800 974250',
        'email': 'segreteria@aicat.net'
    }

# Raggruppa per regione
regions_map = {}
for i in items:
    if i['level'] == 'NATIONAL':
        continue
    reg = i['region'] or 'Altra Regione'
    if reg not in regions_map:
        regions_map[reg] = {
            'regional': [],
            'territorial': {},
            'direct_clubs': []
        }
    
    lvl = i['level']
    prov = i['province'] or 'Senza Provincia'
    
    if 'REGIONAL' in lvl:
        regions_map[reg]['regional'].append(i)
    elif 'ACAT' in lvl or 'APCAT' in lvl or 'TERRITORIAL' in lvl or 'ASSOCIATION' in lvl:
        if prov not in regions_map[reg]['territorial']:
            regions_map[reg]['territorial'][prov] = {
                'acat_info': [],
                'clubs': []
            }
        regions_map[reg]['territorial'][prov]['acat_info'].append(i)
    else: # LOCAL_CLUB
        if prov not in regions_map[reg]['territorial']:
            regions_map[reg]['territorial'][prov] = {
                'acat_info': [],
                'clubs': []
            }
        regions_map[reg]['territorial'][prov]['clubs'].append(i)

# Costruiamo l'albero gerarchico standard D3
root = {
    'name': aicat_node['entity_name'],
    'type': 'NATIONAL',
    'sic_id': aicat_node['sic_id'],
    'phone': aicat_node['phone'],
    'email': aicat_node['email'],
    'city': aicat_node['city'],
    'total_clubs': len([i for i in items if i['level'] == 'LOCAL_CLUB']),
    'total_families': sum(i['families_count'] or 0 for i in items if i['level'] == 'LOCAL_CLUB'),
    'children': []
}

for reg_name in sorted(regions_map.keys()):
    rdata = regions_map[reg_name]
    reg_rep = rdata['regional'][0] if rdata['regional'] else None
    
    total_reg_clubs = sum(len(pdata['clubs']) for pdata in rdata['territorial'].values())
    total_reg_fam = sum(sum(c['families_count'] or 0 for c in pdata['clubs']) for pdata in rdata['territorial'].values())
    
    reg_node = {
        'name': reg_rep['entity_name'] if reg_rep else f"ARCAT {reg_name}",
        'type': 'REGIONAL',
        'region': reg_name,
        'sic_id': reg_rep['sic_id'] if reg_rep else '',
        'phone': reg_rep['phone'] if reg_rep else '800 974250',
        'email': reg_rep['email'] if reg_rep else '',
        'total_clubs': total_reg_clubs,
        'total_families': total_reg_fam,
        'children': []
    }
    
    for prov_code in sorted(rdata['territorial'].keys()):
        pdata = rdata['territorial'][prov_code]
        acat_first = pdata['acat_info'][0] if pdata['acat_info'] else None
        
        prov_node = {
            'name': acat_first['entity_name'] if acat_first else f"ACAT / Presidio {prov_code}",
            'type': 'TERRITORIAL_ACAT',
            'region': reg_name,
            'province': prov_code,
            'city': acat_first['city'] if acat_first else '',
            'sic_id': acat_first['sic_id'] if acat_first else '',
            'phone': acat_first['phone'] if acat_first else '',
            'email': acat_first['email'] if acat_first else '',
            'total_clubs': len(pdata['clubs']),
            'total_families': sum(c['families_count'] or 0 for c in pdata['clubs']),
            'children': []
        }
        
        for club in pdata['clubs']:
            club_node = {
                'name': club['entity_name'],
                'type': 'LOCAL_CLUB',
                'region': reg_name,
                'province': prov_code,
                'city': club['city'],
                'address': club['address'],
                'sic_id': club['sic_id'],
                'phone': club['phone'] or club['phone_secondary'] or '',
                'email': club['email'] or '',
                'website': club['website'] or '',
                'meeting_day': club['meeting_day'] or '',
                'meeting_time': club['meeting_time'] or '',
                'servitore_insegnante': club['servitore_insegnante'] or '',
                'families_count': club['families_count'] or 0,
                'notes': club['notes'] or ''
            }
            prov_node['children'].append(club_node)
            
        reg_node['children'].append(prov_node)
        
    root['children'].append(reg_node)

print(f"Radice: {root['name']}, Regioni figlie: {len(root['children'])}")
print(f"Totale Club in radice: {root['total_clubs']}, Totale Famiglie: {root['total_families']}")

conn.close()
