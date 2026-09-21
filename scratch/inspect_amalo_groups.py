import urllib.request
import ssl
import re

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

headers = {'User-Agent': 'Mozilla/5.0'}

def get_xml(url):
    req = urllib.request.Request(url, headers=headers)
    try:
        return urllib.request.urlopen(req, context=ctx, timeout=10).read().decode('utf-8', errors='ignore')
    except Exception as e:
        print(f"Error {url}: {e}")
        return ""

xml1 = get_xml('https://www.amalo.it/wp-sitemap-posts-cpt-gruppi-1.xml')
urls1 = re.findall(r'<loc>(.*?)</loc>', xml1)
print(f"Total groups in amalo.it (cpt-gruppi-1): {len(urls1)}")

cat_slugs = [u for u in urls1 if any(k in u.lower() for k in ['cat', 'acat', 'alcol', 'hudolin', 'club'])]
print(f"Total with cat/acat/alcol/hudolin/club in slug: {len(cat_slugs)}")
for u in cat_slugs[:25]:
    print("  *", u)

xml_tip = get_xml('https://www.amalo.it/wp-sitemap-taxonomies-tipologie-gruppi-1.xml')
urls_tip = re.findall(r'<loc>(.*?)</loc>', xml_tip)
print(f"Tipologie: {urls_tip}")

xml_reg = get_xml('https://www.amalo.it/wp-sitemap-taxonomies-regioni-gruppi-1.xml')
urls_reg = re.findall(r'<loc>(.*?)</loc>', xml_reg)
print(f"Regioni taxonomies: {urls_reg}")
