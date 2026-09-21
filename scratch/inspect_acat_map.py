import json

with open('scratch/veneto_data_acat.json', 'r', encoding='utf-8-sig') as f:
    acats = json.load(f)

print(f"Total ACATs in Veneto: {len(acats)}")
for a in acats:
    name = a.get('name', '').strip()
    phone = a.get('phonenumber') or a.get('presidentphonenumber') or ''
    email = a.get('email') or a.get('presidentemail') or ''
    print(f"[{name}] -> Phone: '{phone}', Email: '{email}', City: '{a.get('city')}'")
