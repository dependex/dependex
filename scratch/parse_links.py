import re

p = r'C:\Users\piano\.gemini\antigravity-ide\brain\4313b40b-a91a-4780-a6c4-71b3e1134442\.system_generated\steps\653\content.md'
with open(p, 'r', encoding='utf-8') as f:
    text = f.read()

links = set(re.findall(r'href=[\'"](https?://infoalcol\.it/[^\'"]+)[\'"]', text))
for l in sorted(links):
    if not any(x in l for x in ['.css', '.js', '.jpg', '.png', 'xmlrpc', 'feed', 'wp-content', 'wp-json']):
        print(l)
