import urllib.request
import os

url = "https://www.acatromagna.com/_files/ugd/15da36_8913df170b0045d2899dd760dc34cdbf.pdf"
hdr = {'User-Agent': 'Mozilla/5.0'}
req = urllib.request.Request(url, headers=hdr)

out_pdf = "scratch/elenco_club_romagna.pdf"
with urllib.request.urlopen(req) as resp, open(out_pdf, 'wb') as f:
    f.write(resp.read())

print(f"Downloaded PDF: {os.path.getsize(out_pdf)} bytes")

# Try to extract text using pypdf or similar if available
try:
    import pypdf
    reader = pypdf.PdfReader(out_pdf)
    print(f"Total pages: {len(reader.pages)}")
    for i, page in enumerate(reader.pages):
        print(f"--- PAGE {i+1} ---")
        print(page.extract_text())
except Exception as e:
    print("pypdf error:", e)
