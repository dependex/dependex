import urllib.request
import ssl
import re

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request('https://www.amalo.it/sitemap.xml', headers={'User-Agent': 'Mozilla/5.0'})
try:
    sitemap = urllib.request.urlopen(req, context=ctx, timeout=10).read().decode('utf-8', errors='ignore')
    print("Sitemap length:", len(sitemap))
    locs = re.findall(r'<loc>(.*?)</loc>', sitemap)
    print(f"Total URLs in sitemap: {len(locs)}")
    for l in locs[:20]:
        print(" ", l)
except Exception as e:
    print("Sitemap error:", e)

# Also check search or gruppi
req2 = urllib.request.Request('https://www.amalo.it/gruppi/', headers={'User-Agent': 'Mozilla/5.0'})
try:
    g_html = urllib.request.urlopen(req2, context=ctx, timeout=10).read().decode('utf-8', errors='ignore')
    print("Gruppi length:", len(g_html))
    g_links = re.findall(r'href=[\"\'](https://www.amalo.it/[^\"]+)[\"\']', g_html)
    print("Gruppi links count:", len(g_links))
    for gl in set(g_links)[:20]:
        print("  gl:", gl)
except Exception as e:
    print("Gruppi error:", e)
