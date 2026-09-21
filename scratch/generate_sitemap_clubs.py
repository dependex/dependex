import sqlite3
import xml.etree.ElementTree as ET

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

sic_dict = {}

# 1. dependex_world_registry
cur.execute('SELECT sic_id, entity_name, updated_at, created_at FROM dependex_world_registry WHERE sic_id IS NOT NULL AND sic_id != ""')
for sic, name, upd, crt in cur.fetchall():
    sic = sic.strip()
    if sic:
        sic_dict[sic] = {'name': name or '', 'lastmod': (upd or crt or '2026-09-21')[:10]}

# 2. cat_clubs_italy
cur.execute('SELECT sic_id, entity_name, updated_at, created_at FROM cat_clubs_italy WHERE sic_id IS NOT NULL AND sic_id != ""')
for sic, name, upd, crt in cur.fetchall():
    sic = sic.strip()
    if sic:
        if sic not in sic_dict:
            sic_dict[sic] = {'name': name or '', 'lastmod': (upd or crt or '2026-09-21')[:10]}
        elif name:
            sic_dict[sic]['name'] = name

# 3. crm_club_contacts
cur.execute('SELECT sic_id, entity_name, updated_at, created_at FROM crm_club_contacts WHERE sic_id IS NOT NULL AND sic_id != ""')
for sic, name, upd, crt in cur.fetchall():
    sic = sic.strip()
    if sic:
        if sic not in sic_dict:
            sic_dict[sic] = {'name': name or '', 'lastmod': (upd or crt or '2026-09-21')[:10]}
        elif name:
            sic_dict[sic]['name'] = name

print(f"Total clubs to include in sitemap-clubs.xml: {len(sic_dict)}")

xml_lines = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
    '        xmlns:xhtml="http://www.w3.org/1999/xhtml">'
]

for sic in sorted(sic_dict.keys()):
    lastmod = '2026-09-21'
    url = f'https://dependex.social/club/{sic}'
    xml_lines.append('  <url>')
    xml_lines.append(f'    <loc>{url}</loc>')
    xml_lines.append(f'    <lastmod>{lastmod}</lastmod>')
    xml_lines.append('    <changefreq>weekly</changefreq>')
    xml_lines.append('    <priority>0.80</priority>')
    xml_lines.append(f'    <xhtml:link rel="alternate" hreflang="it" href="{url}"/>')
    xml_lines.append(f'    <xhtml:link rel="alternate" hreflang="x-default" href="{url}"/>')
    xml_lines.append('  </url>')

xml_lines.append('</urlset>')
xml_lines.append('')

xml_content = '\n'.join(xml_lines)

with open('sitemap-clubs.xml', 'w', encoding='utf-8') as f:
    f.write(xml_content)

# Validate XML
root = ET.fromstring(xml_content)
print(f"Successfully validated XML with {len(root)} URLs in sitemap-clubs.xml!")
