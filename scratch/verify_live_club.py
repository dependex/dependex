import urllib.request
import json
import re

url = 'https://dependex.social/club/SIC-WS2VDS86-YRB6M7TV-B'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 DependexVerifier/1.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8')

m = re.search(r'<script type="application/ld\+json">(.*?)</script>', html, re.DOTALL)
print("Schema.org present:", bool(m))
if m:
    schema = json.loads(m.group(1))
    print("Schema graph types:", [x.get('@type') for x in schema.get('@graph', [])])

print("Has PHP Errors/Warnings:", any(err in html for err in ['Fatal error:', 'Parse error:', 'Warning:', 'Notice:']))
print("Has Title:", "<title>" in html)
print("Has Anti-Anxiety Section:", "Cosa succede al primo incontro" in html)
print("Has WhatsApp Action:", "wa.me" in html)
print("Has Directions Action:", "google.com/maps/dir" in html)
