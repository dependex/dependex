import urllib.request

req = urllib.request.Request('https://dependex.social/mappa-club.php', headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as resp:
        print('Status:', resp.status)
        content = resp.read().decode('utf-8', errors='ignore')
        print('Content length:', len(content))
        print('Leaflet present:', 'leaflet' in content.lower())
        print('id="map":', 'id="map"' in content)
        print('Error in content:', 'Fatal error' in content or 'Parse error' in content)
except Exception as e:
    print('Error:', e)
