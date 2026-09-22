import os
import re
import glob

PATTERNS = [
    r'\b54[0-9]\b',
    r'\b36[0-9]\b',
    r'\b37[0-9]\b',
    r'\b39[0-9]\b',
    r'\b1\.?7[0-9]{2}\b',
    r'\b17[0-9]{2}\b',
    r'\b2\.?0[0-9]{2}\b',
    r'\b51\.?[0-9]{3}\b',
    r'oltre\s+\d+',
    r'più\s+di\s+\d+',
    r'\d+\+\s*(?:Club|famiglie|nodi|persone|comuni)',
    r'\b(?:Club|famiglie|nodi|presidi|comuni)\b[^\.\n\<\>]{0,40}\b\d+',
]

regexes = [re.compile(p, re.IGNORECASE) for p in PATTERNS]

ignore_dirs = {'.git', 'scratch', '_ai_archive', 'node_modules', 'vendor', '.tmp.drivedownload', '.tmp.driveupload'}

findings = []

for root, dirs, files in os.walk('.'):
    dirs[:] = [d for d in dirs if d not in ignore_dirs]
    for f in files:
        if f.endswith(('.php', '.html', '.md', '.json', '.webmanifest', '.txt', '.js', '.css', '.xml')):
            path = os.path.join(root, f)
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as fh:
                    for i, line in enumerate(fh, 1):
                        for rx in regexes:
                            m = rx.search(line)
                            if m:
                                findings.append({
                                    'file': path.replace('\\', '/'),
                                    'line': i,
                                    'match': m.group(0),
                                    'snippet': line.strip()[:140]
                                })
                                break
            except Exception as e:
                pass

print(f"Total quantitative mentions found: {len(findings)}")

# Print unique files and interesting findings
files_count = {}
for item in findings:
    f = item['file']
    files_count[f] = files_count.get(f, 0) + 1

print("\nFiles with quantitative mentions (top 30):")
for f, count in sorted(files_count.items(), key=lambda x: x[1], reverse=True)[:30]:
    print(f"  {f}: {count}")

print("\nSample distinct claims:")
seen = set()
for item in findings:
    # filter out line numbers or repetitive xml locs
    if 'sitemap' in item['file'] or 'sample_group' in item['file']:
        continue
    snip = item['snippet']
    if snip not in seen and len(seen) < 50:
        seen.add(snip)
        print(f"[{item['file']}:{item['line']}] {snip}")
