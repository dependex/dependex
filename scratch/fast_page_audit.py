# -*- coding: utf-8 -*-
import os
import glob
import re
import subprocess
import urllib.request
import urllib.error
import ssl

ROOT = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"

php_root_files = sorted(glob.glob(os.path.join(ROOT, "*.php")))
html_root_files = sorted(glob.glob(os.path.join(ROOT, "*.html")))

banned_patterns = [
    (re.compile(r'\bmagico\b', re.IGNORECASE), 'magico'),
    (re.compile(r'\bmagic\b', re.IGNORECASE), 'magic'),
    (re.compile(r'M\.A\.G\.I\.C\.', re.IGNORECASE), 'M.A.G.I.C.'),
    (re.compile(r'giorgian\s+putanu', re.IGNORECASE), 'giorgian putanu'),
    (re.compile(r'\b81plus\b', re.IGNORECASE), '81plus')
]

print(f"File PHP root analizzati: {len(php_root_files)}")
print(f"File HTML root analizzati: {len(html_root_files)}")
print("=" * 60)

# 1. PHP LINT
print("\n--- 1. PHP SYNTAX LINT (php -l) ---")
syn_errors = []
for p in php_root_files:
    res = subprocess.run(["php", "-l", p], capture_output=True, text=True, check=False)
    if res.returncode != 0:
        err = res.stderr.strip() or res.stdout.strip()
        syn_errors.append((os.path.basename(p), err))

if syn_errors:
    print(f"ERRORI SINTASSI PHP: {len(syn_errors)}")
    for f, err in syn_errors:
        print(f"  [SYNTAX ERROR] {f}: {err}")
else:
    print("TUTTI I FILE PHP SUPERANO LA SINTASSI (0 ERRORI SINTATTICI)!")

# 2. BROKEN INCLUDES / REQUIRES
print("\n--- 2. BROKEN INCLUDES / REQUIRES STATICI ---")
broken_inc = []
inc_regex = re.compile(r'''(?:require|include)(?:_once)?\s*(?:\(?\s*__DIR__\s*\.\s*)?['"]([^'"]+)['"]''', re.IGNORECASE)

for p in php_root_files:
    fn = os.path.basename(p)
    try:
        with open(p, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
    except Exception:
        continue
    for m in inc_regex.finditer(content):
        inc_target = m.group(1).lstrip("/\\")
        if inc_target.endswith(".php"):
            full_target = os.path.join(ROOT, inc_target)
            if not os.path.exists(full_target):
                broken_inc.append((fn, inc_target))

if broken_inc:
    print(f"BROKEN REQUIRES TROVATI: {len(broken_inc)}")
    for fn, inc in broken_inc:
        print(f"  [BROKEN REQUIRE] In {fn} -> non esiste '{inc}'")
else:
    print("NESSUN BROKEN REQUIRE STATICO TROVATO!")

# 3. SCANSIONE PAROLE VIETATE
print("\n--- 3. SCANSIONE PAROLE VIETATE (AGENTS.md) ---")
banned_matches = []
for p in php_root_files + html_root_files:
    fn = os.path.basename(p)
    try:
        with open(p, "r", encoding="utf-8", errors="ignore") as f:
            lines = f.readlines()
    except Exception:
        continue
    for idx, line in enumerate(lines, 1):
        clean = re.sub(r'c:[\\/]81plus[_\w\\]*', '', line, flags=re.IGNORECASE)
        if 'Zero riferimenti residui a `81plus`' in line or 'vietato l\'uso delle seguenti parole' in line:
            continue
        for pat, w in banned_patterns:
            if pat.search(clean):
                banned_matches.append((fn, idx, w, line.strip()[:85]))

if banned_matches:
    print(f"PAROLE VIETATE TROVATE: {len(banned_matches)}")
    for fn, l, w, snippet in banned_matches:
        print(f"  [BANNED WORD] {fn}:{l} [{w}] -> {snippet}")
else:
    print("NESSUNA PAROLA VIETATA TROVATA!")

# 4. AUDIT PAGINE PUBBLICHE LIVE (HTTP 200 vs ERRORI)
print("\n--- 4. AUDIT RESPONSE HTTP LIVE SU DEPENDEX.SOCIAL ---")
# Selezioniamo tutte le pagine principali dell'ecosistema
public_pages = [
    "index.php",
    "world-club-explorer.php",
    "mappa-club.php",
    "orientamento.php",
    "playground.php",
    "clips.php",
    "events-public.php",
    "parla-con-noi.php",
    "recensioni.php",
    "metodo.php",
    "ruota-della-vita.php",
    "piramide-maslow.php",
    "guida-gratuita.php",
    "domande-frequenti.php",
    "faq.php",
    "help.php",
    "privacy.php",
    "terms.php",
    "telemetria.php",
    "academy-public.php",
    "dashboard.php",
    "crociera-benessere-masterclass.php",
    "viaggi-esperienziali.php",
    "event-detail.php",
    "offers.php",
    "storie.php",
    "world-map.php",
    "login.php",
    "register.php",
    "cart.php"
]

ctx = ssl.create_default_context()
http_results = []
for pg in public_pages:
    url = f"https://dependex.social/{pg}?v=fastaudit"
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "Dependex-FastAudit/1.0"})
        with urllib.request.urlopen(req, context=ctx, timeout=7) as res:
            body = res.read().decode("utf-8", errors="ignore")
            fatal_err = None
            if "Fatal error" in body:
                m = re.search(r'Fatal error[:\s].*?(?=<br|</div>|\n)', body)
                fatal_err = m.group(0) if m else "Fatal error"
            elif "Warning: require" in body or "Warning: include" in body:
                m = re.search(r'Warning:.*?Failed opening required.*?(?=<br|</div>|\n)', body)
                fatal_err = m.group(0) if m else "Broken require"
            
            http_results.append({
                "page": pg,
                "status": res.status,
                "bytes": len(body),
                "error": fatal_err
            })
            status_symbol = "OK" if not fatal_err else "FAIL"
            print(f"  [{status_symbol}] {pg:35} -> HTTP {res.status} ({len(body):,} bytes) {('ERR: ' + fatal_err) if fatal_err else ''}")
    except urllib.error.HTTPError as he:
        http_results.append({
            "page": pg,
            "status": he.code,
            "bytes": 0,
            "error": f"HTTP {he.code}"
        })
        print(f"  [FAIL] {pg:35} -> HTTP {he.code}")
    except Exception as ex:
        http_results.append({
            "page": pg,
            "status": 0,
            "bytes": 0,
            "error": str(ex)[:60]
        })
        print(f"  [ERR]  {pg:35} -> {str(ex)[:60]}")

print("\n" + "=" * 60)
print("AUDIT COMPLETATO!")
