p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\685\content.md'
with open(p, 'r', encoding='utf-8') as f:
    lines = f.readlines()

print(f"Total lines: {len(lines)}")
for i, line in enumerate(lines):
    if any(k in line for k in ['loadedClubData', 'loadedAcatData', 'fetch', 'ajax', 'XMLHttpRequest', 'http', '.json', '.php']):
        print(f"L{i+1}: {line.strip()[:120]}")
