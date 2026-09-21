import json

with open('scratch/amalo_national_harvest.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

print(f"Total: {len(data)}")
for i, d in enumerate(data[:15]):
    print(f"[{i}] NAME: {d.get('name')}")
    print(f"    STREET: {d.get('street')}")
    print(f"    CITY: {d.get('city')}")
    print(f"    CAP: {d.get('cap')}")
    print(f"    PROV: {d.get('province')}")
    print(f"    REG: {d.get('region')}")
    print(f"    TEL: {d.get('phone')}")
    print(f"    EMAIL: {d.get('email')}")
    print("-" * 50)
