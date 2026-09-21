from bs4 import BeautifulSoup

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\830\content.md'
with open(p, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
for a in soup.find_all('a', href=True):
    href = a['href']
    t = a.get_text().strip()
    if any(k in href.lower() or k in t.lower() for k in ['club', 'dove', 'sedi', 'contatt', 'chi-siamo']):
        print(f"  {t} -> {href}")
