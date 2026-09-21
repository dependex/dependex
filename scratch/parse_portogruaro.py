from bs4 import BeautifulSoup

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\800\content.md'
with open(p, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

print("PDF links:")
for a in soup.find_all('a', href=True):
    if a['href'].endswith('.pdf'):
        print(" ", a['href'], a.get_text().strip())

print("\nText snippets:")
for p_tag in soup.find_all(['p', 'h1', 'h2', 'h3', 'h4', 'li', 'td']):
    t = p_tag.get_text().strip()
    if any(k in t.lower() for k in ['club', 'caorle', 'portogruaro', 'concordia', 'annone', 'bibione', 'san michele', 'sedi', 'orari']):
        if len(t) < 250:
            print("-", t)
