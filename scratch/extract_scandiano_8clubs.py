import re
import json

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\762\content.md'
with open(p, 'r', encoding='utf-8') as f:
    text = f.read()

# Look for all string arrays "A":["..."]
strings = re.findall(r'"A":\["([^"]+)"\]', text)
for s in strings:
    s_dec = s.replace('\\n', ' ').strip()
    if any(k in s_dec.lower() for k in ['club', 'via', 'piazza', 'servitor', 'luned', 'marted', 'mercoled', 'gioved', 'venerd', 'scandiano', 'castellarano', 'rubiera', 'casalgrande', 'cavriago', 'montecchio', 'bibbiano', 'sant\'ilario']):
        print("-", s_dec.encode('ascii', errors='replace').decode('ascii'))
