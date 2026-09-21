import urllib.request
import ssl
import re
import json
import concurrent.futures
import time
from bs4 import BeautifulSoup

ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

def fetch_url(url):
    req = urllib.request.Request(url, headers=headers)
    try:
        with urllib.request.urlopen(req, context=ctx, timeout=8) as resp:
            return resp.read().decode('utf-8', errors='ignore')
    except Exception as e:
        return None

def parse_group(url, html):
    if not html:
        return None
    soup = BeautifulSoup(html, 'html.parser')
    
    # Check if category relates to alcohol / cat / acat / auto mutuo aiuto
    text_all = soup.get_text()
    is_alc = any(k in text_all.lower() for k in ['alcol', 'acat', 'aicat', 'arcat', 'apcat', 'hudolin', 'club alcologico', 'club alcolisti'])
    if not is_alc:
        return None

    # Title
    h2 = soup.find('h2')
    name = h2.text.strip() if h2 else ""
    if not name:
        h1 = soup.find('h1')
        name = h1.text.strip() if h1 else ""

    # Address block
    info_div = soup.find('div', class_='informazioni')
    street = ""
    cap_city = ""
    prov_reg = ""
    phone = ""
    email = ""
    incontri = ""

    if info_div:
        # Address
        addr_card = info_div.find('div', class_='scheda indirizzo')
        if addr_card:
            ps = addr_card.find_all('p')
            if len(ps) >= 1:
                street = ps[0].text.strip()
            if len(ps) >= 2:
                cap_city = ps[1].text.strip()
            if len(ps) >= 3:
                prov_reg = ps[2].text.strip()

        # Phone
        tel_card = info_div.find('div', class_='scheda telefono')
        if tel_card:
            tels = []
            for a in tel_card.find_all('a', href=re.compile(r'^tel:')):
                tels.append(a.text.strip())
            phone = " / ".join(tels) if tels else tel_card.text.replace('Telefono / Fax', '').replace('Tel:', '').replace('Fax:', '').strip()

        # Email
        email_card = info_div.find('div', class_='scheda email')
        if email_card:
            mail_a = email_card.find('a', href=re.compile(r'^mailto:'))
            if mail_a:
                email = mail_a.text.strip()

        # Incontri
        tip_card = info_div.find('div', class_='scheda tipologia')
        if tip_card:
            incontri = tip_card.text.replace('Incontri con il gruppo', '').strip()

    # Extract City, CAP, Province, Region
    cap = ""
    city = ""
    m_cap = re.search(r'(\d{5})\s+(.*)', cap_city)
    if m_cap:
        cap = m_cap.group(1).strip()
        city = m_cap.group(2).strip()
    else:
        city = cap_city

    province = ""
    region = ""
    if ',' in prov_reg:
        parts = prov_reg.split(',')
        province = parts[0].replace('Provincia di', '').strip()
        region = parts[1].strip()
    else:
        province = prov_reg.replace('Provincia di', '').strip()

    # Fallback email/phone if empty
    if not email:
        m_em = re.search(r'[\w\.-]+@[\w\.-]+\.\w+', text_all)
        if m_em and 'amalo.it' not in m_em.group(0):
            email = m_em.group(0)

    return {
        'url': url,
        'name': name,
        'street': street,
        'city': city,
        'cap': cap,
        'province': province,
        'region': region,
        'phone': phone,
        'email': email,
        'incontri': incontri
    }

def main():
    print("Loading sitemap...")
    xml_req = urllib.request.Request('https://www.amalo.it/wp-sitemap-posts-cpt-gruppi-1.xml', headers=headers)
    xml1 = urllib.request.urlopen(xml_req, context=ctx, timeout=10).read().decode('utf-8', errors='ignore')
    urls = re.findall(r'<loc>(.*?)</loc>', xml1)
    print(f"Total group URLs found: {len(urls)}")

    # Let's filter first URLs that have alcol, cat, acat, aicat, arcat, apcat, club, hudolin in slug OR scan all
    # To be thorough and census completely, scan all URLs in threads!
    print("Starting multi-threaded scraping (20 workers)...")
    results = []
    t0 = time.time()
    
    def process_url(u):
        h = fetch_url(u)
        g = parse_group(u, h)
        return g

    with concurrent.futures.ThreadPoolExecutor(max_workers=20) as executor:
        future_to_url = {executor.submit(process_url, u): u for u in urls}
        count = 0
        for future in concurrent.futures.as_completed(future_to_url):
            count += 1
            if count % 100 == 0:
                print(f"Processed {count}/{len(urls)}... Found {len(results)} alcohol/CAT groups so far.")
            res = future.result()
            if res and res.get('name'):
                results.append(res)

    print(f"Scraping completed in {time.time()-t0:.1f}s. Total relevant groups extracted: {len(results)}")
    with open('scratch/amalo_national_harvest.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    print("Saved to scratch/amalo_national_harvest.json")

if __name__ == '__main__':
    main()
