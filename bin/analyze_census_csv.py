import csv

with open('data/ACAT_Italia_Club_Census_V1.csv', 'r', encoding='utf-8', errors='ignore') as f:
    reader = csv.DictReader(f)
    rows = list(reader)

print(f"Total rows: {len(rows)}")
by_region = {}
by_level = {}
with_coords = 0
with_phone = 0
with_email = 0
with_address = 0

for r in rows:
    reg = r.get('region') or 'Unknown'
    lvl = r.get('level') or 'Unknown'
    by_region[reg] = by_region.get(reg, 0) + 1
    by_level[lvl] = by_level.get(lvl, 0) + 1
    if r.get('latitude') and r.get('longitude'):
        with_coords += 1
    if r.get('phone'):
        with_phone += 1
    if r.get('email'):
        with_email += 1
    if r.get('address'):
        with_address += 1

print("By Level:", by_level)
print("By Region:", by_region)
print(f"With phone: {with_phone}, with email: {with_email}, with address: {with_address}, with coords: {with_coords}")
