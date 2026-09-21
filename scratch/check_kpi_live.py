import urllib.request
import re

html = urllib.request.urlopen('https://dependex.social/mappa-club.php').read().decode('utf-8')
matches = re.findall(r'<div class="kpi-item">[\s\S]*?</div>', html)
for m in matches:
    print(m.strip())
