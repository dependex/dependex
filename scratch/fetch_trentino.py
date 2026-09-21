import urllib.request
import ssl
import re

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request("https://www.apcattrentino-centrostudi.it/le-nostre-acat/", headers={'User-Agent': 'Mozilla/5.0'})
try:
    html = urllib.request.urlopen(req, context=ctx, timeout=10).read().decode('utf-8', errors='ignore')
    print("Page fetched! Length:", len(html))
    with open('scratch/trentino_acat_page.html', 'w', encoding='utf-8') as f:
        f.write(html)
    
    # extract all text
    clean = re.sub(r'<[^>]+>', '\n', html)
    clean = re.sub(r'\n+', '\n', clean)
    print("Sample content:\n", clean[:1500])
except Exception as e:
    print("Error:", e)
