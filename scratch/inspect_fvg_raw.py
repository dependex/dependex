import urllib.request
import ssl
import re
import json

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

req = urllib.request.Request("https://www.arcatfvg.it/acat-e-cat", headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req, context=ctx, timeout=12).read().decode('utf-8', errors='ignore')

# Find all script tags
scripts = re.findall(r'<script([^>]*)>(.*?)</script>', html, re.DOTALL)
print(f"Total script tags: {len(scripts)}")
for attrs, content in scripts:
    if 'acat' in content.lower() or 'pordenone' in content.lower() or 'udine' in content.lower():
        print("Found matching script with attrs:", attrs)
        print("Content sample:", content[:300])
        print("---")

# Also find Wix page model JSON
matches = re.findall(r'\"text\":\s*\"([^\"]*acat[^\"]*)\"', html, re.IGNORECASE)
print(f"Text matches with acat: {len(matches)}")
for m in matches[:10]:
    print("Match:", m)

# Look for downloadable PDFs on the page
pdfs = re.findall(r'href=[\"\'](https?://[^\"\']+\.pdf)[\"\']', html, re.IGNORECASE)
print("PDFs found:", set(pdfs))
wix_docs = re.findall(r'https?://[a-zA-Z0-9.\-]+/ugd/[a-zA-Z0-9_]+', html)
print("Wix documents found:", set(wix_docs))
