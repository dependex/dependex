import urllib.request
import ssl
import re
import json

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

def fetch(url):
    req = urllib.request.Request(url, headers=headers)
    try:
        with urllib.request.urlopen(req, context=ctx, timeout=12) as response:
            return response.read().decode('utf-8', errors='ignore')
    except Exception as e:
        print(f"Error fetching {url}: {e}")
        return ""

print("Fetching ARCAT FVG...")
html_fvg = fetch("https://www.arcatfvg.it/acat-e-cat")
print(f"FVG length: {len(html_fvg)}")

# find all links or text
links = re.findall(r'href=[\"\'](https?://[^\"\']+)[\"\']', html_fvg)
print("FVG links found:", len(links))
for l in links[:20]:
    print(" ", l)

# Let's check for page content in Wix JSON
wix_data = re.findall(r'<script id="wix-warmup-data"[^>]*>(.*?)</script>', html_fvg, re.DOTALL)
if wix_data:
    print("Found wix warmup data! Length:", len(wix_data[0]))
    with open('scratch/fvg_wix_warmup.json', 'w', encoding='utf-8') as f:
        f.write(wix_data[0])

# Also let's check plain text in html_fvg
clean_text = re.sub(r'<[^>]+>', ' ', html_fvg)
clean_text = re.sub(r'\s+', ' ', clean_text)
print("FVG sample text:", clean_text[:500])
with open('scratch/fvg_text.txt', 'w', encoding='utf-8') as f:
    f.write(clean_text)
