import re

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\762\content.md'
with open(p, 'r', encoding='utf-8') as f:
    text = f.read()

emails = set(re.findall(r'[\w\.-]+@[\w\.-]+\.\w+', text))
print("Emails found:", emails)

matches = [m.start() for m in re.finditer(r'club', text, re.IGNORECASE)]
print("Club mentions:", len(matches))
for idx in matches[:15]:
    snippet = text[max(0, idx-80):min(len(text), idx+120)].replace('\n', ' ')
    print("---", snippet.encode('ascii', errors='replace').decode('ascii'))
