from bs4 import BeautifulSoup

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\726\content.md'
with open(p, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
content = soup.find('div', class_='entry-content') or soup
for p_tag in content.find_all(['p', 'li', 'tr', 'h2', 'h3', 'h4']):
    t = p_tag.get_text().strip()
    if any(k in t.lower() for k in ['club', 'via', 'ore', 'martedì', 'mercoledì', 'giovedì', 'venerdì', 'lunedì', 'servitor']):
        print("---")
        print(t)
