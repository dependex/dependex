from bs4 import BeautifulSoup

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\868\content.md'
with open(p, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

print("--- LINKS ---")
for a in soup.find_all('a', href=True):
    print(f"  {a.get_text().strip()} -> {a['href']}")

print("\n--- TEXT SNIPPETS ---")
for p_tag in soup.find_all(['p', 'h1', 'h2', 'h3', 'h4', 'li', 'td', 'div']):
    t = p_tag.get_text().strip()
    if any(k in t.lower() for k in ['club', 'via', 'ore', 'bari', 'lecce', 'taranto', 'brindisi', 'foggia', 'servitor']):
        if len(t) < 180 and len(t) > 10:
            print("-", t)
