import re

with open('scratch/fvg_text.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# find sections with ACAT or CAT or Club
lines = [l.strip() for l in text.split('  ') if len(l.strip()) > 3]
print(f"Total lines in text: {len(lines)}")

relevant = []
for l in lines:
    low = l.lower()
    if any(k in low for k in ['acat', 'cat', 'club', 'udine', 'pordenone', 'gorizia', 'trieste', 'cervignano', 'tolmezzo', 'spilimbergo', 'cividale', 'monfalcone']):
        relevant.append(l)

print(f"Relevant text chunks: {len(relevant)}")
for r in relevant[:30]:
    print("->", r[:120])
