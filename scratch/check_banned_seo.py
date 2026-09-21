banned = ['magico', 'magic', 'm.a.g.i.c.', 'giorgian putanu', '81plus']

files = ['llms.txt', 'llms-full.txt', 'sitemap-clubs.xml', 'sitemap.xml', 'robots.txt']
for fn in files:
    with open(fn, 'r', encoding='utf-8') as f:
        c = f.read().lower()
        for b in banned:
            if b in c:
                print(f"WARNING: Banned term '{b}' found in {fn}")

print("Banned words check completed. All files clean!")
