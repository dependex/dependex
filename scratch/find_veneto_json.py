import re

p1 = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\675\content.md'
p2 = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\685\content.md'

with open(p1, 'r', encoding='utf-8') as f:
    t1 = f.read()

with open(p2, 'r', encoding='utf-8') as f:
    t2 = f.read()

print("Files mentioned in HTML:")
for match in re.findall(r'/ricerca-dei-club/[a-zA-Z0-9_\-\.]+', t1):
    print(" ", match)

print("Files mentioned in main_script:")
for match in re.findall(r'[a-zA-Z0-9_\-\.]+\.json', t2):
    print(" ", match)
for match in re.findall(r'fetch\([^\)]+\)', t2):
    print("  fetch:", match)
