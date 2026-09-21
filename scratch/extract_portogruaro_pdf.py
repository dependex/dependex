import urllib.request
import os

url = "https://www.acatportogruarese.it/wp-content/uploads/2026/01/elenco-club-agg.-17-dicembre-2025.pdf"
hdr = {'User-Agent': 'Mozilla/5.0'}
req = urllib.request.Request(url, headers=hdr)

out_pdf = "scratch/elenco_club_portogruaro.pdf"
with urllib.request.urlopen(req) as resp, open(out_pdf, 'wb') as f:
    f.write(resp.read())

print(f"Downloaded PDF: {os.path.getsize(out_pdf)} bytes")

import pypdf
reader = pypdf.PdfReader(out_pdf)
print(f"Total pages: {len(reader.pages)}")
for i, page in enumerate(reader.pages):
    print(f"--- PAGE {i+1} ---")
    print(page.extract_text())
