from bs4 import BeautifulSoup

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\816\content.md'
with open(p, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
for p_tag in soup.find_all(['p', 'h1', 'h2', 'h3', 'h4', 'li', 'td', 'tr']):
    t = p_tag.get_text().strip()
    if any(k in t.lower() for k in ['club', 'modena', 'carpi', 'sassuolo', 'mirandola', 'vignola', 'castelfranco', 'formigine', 'maranello', 'soliera', 'nonantola', 'via', 'ore']):
        if len(t) < 250:
            print("-", t)
