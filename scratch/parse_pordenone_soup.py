p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\830\content.md'
with open(p, 'r', encoding='utf-8') as f:
    text = f.read()

from bs4 import BeautifulSoup
soup = BeautifulSoup(text, 'html.parser')
for a in soup.find_all('a'):
    print(a.get('href'), a.get_text().strip())
