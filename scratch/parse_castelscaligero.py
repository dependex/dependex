import re
from bs4 import BeautifulSoup

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\718\content.md'
with open(p, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
links = set()
for a in soup.find_all('a', href=True):
    href = a['href']
    if 'acatcastelscaligero' in href or href.startswith('/'):
        links.add(href)

print("Links on acatcastelscaligero:")
for l in sorted(links):
    print(" ", l)
