import urllib.request
import urllib.error

base = "https://arcatveneto.it/ricerca-dei-club/"
candidates = [
    "clubs.json", "club.json", "data.json", "locations.json", "acat.json", "acats.json",
    "club_data.js", "clubs.js", "data.js", "locations.js", "acat.js", "acats.js",
    "club_data.json", "acat_data.json", "location_data.json"
]

hdr = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

for c in candidates:
    url = base + c
    req = urllib.request.Request(url, headers=hdr)
    try:
        with urllib.request.urlopen(req, timeout=5) as resp:
            content = resp.read()
            print(f"FOUND! {url} - {len(content)} bytes")
            # save first 200 chars
            print("  sample:", content[:200])
    except urllib.error.HTTPError as e:
        # print(f"  {c}: {e.code}")
        pass
    except Exception as e:
        # print(f"  {c}: {e}")
        pass
