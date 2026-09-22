import os
import glob
import re
import urllib.request
import urllib.error
import time
import json

ROOT = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"
LIVE_BASE = "https://dependex.social"

print("================================================================")
print("DEPENDEX.SOCIAL — FULL SYSTEM & SITE DEEP AUDIT")
print("================================================================")

findings = {
    "banned_terms": [],
    "broken_internal_links": [],
    "live_probes": [],
    "mobile_viewport_issues": [],
    "seo_issues": [],
    "deontological_checks": [],
    "performance_insights": []
}

# 1. BANNED TERMS AUDIT (Hard Constraint 3)
print("\n[1] AUDIT BONIFICA TERMINOLOGICA (Hard Constraint 3)...")
banned_patterns = [
    (r"\bmagico\b", "magico"),
    (r"\bmagic\b", "magic"),
    (r"M\.A\.G\.I\.C\.", "M.A.G.I.C."),
    (r"giorgian\s+putanu", "giorgian putanu"),
    (r"\b81plus\b", "81plus")
]

exempt_files = ["clean_db_regions.py", "audit_full_census.py", "full_site_deep_audit.py", "TEST_REPORT_V4.json"]

scan_exts = [".php", ".html", ".js", ".css", ".md", ".json"]
for root_dir, dirs, files in os.walk(ROOT):
    # Skip .git, node_modules, tests/fixtures if any
    if ".git" in root_dir or "acat-hudolin-kdp-factory" in root_dir:
        continue
    for fname in files:
        if fname in exempt_files:
            continue
        ext = os.path.splitext(fname)[1].lower()
        if ext in scan_exts:
            fpath = os.path.join(root_dir, fname)
            rel_path = os.path.relpath(fpath, ROOT)
            try:
                with open(fpath, "r", encoding="utf-8", errors="ignore") as f:
                    content = f.read()
                for pattern, name in banned_patterns:
                    matches = re.finditer(pattern, content, re.IGNORECASE)
                    for m in matches:
                        # Extract snippet
                        start = max(0, m.start() - 30)
                        end = min(len(content), m.end() + 30)
                        snippet = content[start:end].replace("\n", " ")
                        findings["banned_terms"].append({
                            "file": rel_path,
                            "term": name,
                            "snippet": snippet.strip()
                        })
            except Exception as e:
                pass

print(f"  -> Trovate {len(findings['banned_terms'])} occorrenze di termini vietati.")

# 2. INTERNAL LINKS INTEGRITY AUDIT
print("\n[2] AUDIT INTEGRITÀ LINK INTERNI...")
internal_link_pattern = re.compile(r'href=["\']([a-zA-Z0-9_\-\.\/]+\.php(\?[^"\']*)?)["\']')
all_php_files = glob.glob(os.path.join(ROOT, "*.php"))

link_references = {}
for fpath in all_php_files:
    fname = os.path.basename(fpath)
    try:
        with open(fpath, "r", encoding="utf-8", errors="ignore") as f:
            c = f.read()
        for m in internal_link_pattern.finditer(c):
            raw_target = m.group(1).split("?")[0].lstrip("/")
            if raw_target not in link_references:
                link_references[raw_target] = []
            link_references[raw_target].append(fname)
    except Exception:
        pass

for target, sources in link_references.items():
    target_fpath = os.path.join(ROOT, target.replace("/", os.sep))
    if not os.path.exists(target_fpath):
        findings["broken_internal_links"].append({
            "target": target,
            "sources": list(set(sources))[:5]
        })

print(f"  -> Verificati {len(link_references)} link interni unici. {len(findings['broken_internal_links'])} potenzialmente mancanti/404.")

# 3. LIVE PROBE OF KEY ROUTES
print("\n[3] AUDIT LIVE SERVER HTTPS://DEPENDEX.SOCIAL...")
key_routes = [
    "/",
    "/world-club-explorer.php",
    "/mappa-club.php",
    "/organigramma.php",
    "/metodo.php",
    "/parla-con-noi.php",
    "/playground.php",
    "/domande-frequenti.php",
    "/privacy.php",
    "/terms.php",
    "/privacy-center.php",
    "/api-public-metrics.php",
    "/api-live-stats.php",
    "/manifest.webmanifest",
    "/sitemap.xml",
    "/robots.txt",
    "/orientamento.php",
    "/clips.php",
    "/crm-clubs.php",
    "/ruota-della-vita.php",
    "/piramide-maslow.php"
]

for r in key_routes:
    url = f"{LIVE_BASE}{r}"
    t0 = time.time()
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0 (DataIntegrityBot/1.0)"})
        with urllib.request.urlopen(req, timeout=10) as resp:
            dur = round((time.time() - t0) * 1000, 1)
            findings["live_probes"].append({
                "route": r,
                "status": resp.status,
                "latency_ms": dur,
                "content_type": resp.headers.get("Content-Type", "")
            })
            print(f"  [PASS] {r} -> {resp.status} ({dur}ms)")
    except urllib.error.HTTPError as e:
        dur = round((time.time() - t0) * 1000, 1)
        findings["live_probes"].append({
            "route": r,
            "status": e.code,
            "latency_ms": dur,
            "error": str(e)
        })
        print(f"  [FAIL] {r} -> HTTP {e.code} ({dur}ms)")
    except Exception as e:
        findings["live_probes"].append({
            "route": r,
            "status": 0,
            "error": str(e)
        })
        print(f"  [ERROR] {r} -> {e}")

# 4. MOBILE-FIRST & VIEWPORT SPEC AUDIT (Hard Constraint 6)
print("\n[4] AUDIT MOBILE-FIRST & VIEWPORT COMPLIANCE...")
fixed_width_pattern = re.compile(r'(?:width|max-width)\s*:\s*(?:8\d\d|9\d\d|1[0-2]\d\d)px', re.IGNORECASE)
fixed_height_pattern = re.compile(r'height\s*:\s*(?:7\d\d|8\d\d|9\d\d|1[0-2]\d\d)px', re.IGNORECASE)

for fpath in all_php_files:
    fname = os.path.basename(fpath)
    try:
        with open(fpath, "r", encoding="utf-8", errors="ignore") as f:
            c = f.read()
        # check fixed large height
        h_matches = fixed_height_pattern.findall(c)
        if h_matches:
            findings["mobile_viewport_issues"].append({
                "file": fname,
                "type": "fixed_large_height",
                "matches": h_matches[:3]
            })
    except Exception:
        pass

print(f"  -> Identificati {len(findings['mobile_viewport_issues'])} file con possibili altezze fisse rigide.")

# 5. DEONTOLOGY AUDIT (Wellness score, chakras)
print("\n[5] AUDIT DEONTOLOGIA HUDOLIN & OMNI-WELFARE (Hard Constraint 7 & 8)...")
wellness_pattern = re.compile(r'wellness[\s_\-]?score|punteggio[\s_\-]+benessere|indice[\s_\-]+felicit[aà]', re.IGNORECASE)
chakra_pattern = re.compile(r'\bchakra[s]?\b', re.IGNORECASE)

for fpath in all_php_files:
    fname = os.path.basename(fpath)
    try:
        with open(fpath, "r", encoding="utf-8", errors="ignore") as f:
            c = f.read()
        if wellness_pattern.search(c):
            findings["deontological_checks"].append({
                "file": fname,
                "issue": "wellness_score_detected"
            })
        if chakra_pattern.search(c):
            findings["deontological_checks"].append({
                "file": fname,
                "issue": "chakra_mention_detected"
            })
    except Exception:
        pass

print(f"  -> Rilevate {len(findings['deontological_checks'])} violazioni deontologiche.")

# 6. SEO & STRUCTURED DATA AUDIT
print("\n[6] AUDIT SEO & STRUCTURED DATA...")
with open(os.path.join(ROOT, "_header.php"), "r", encoding="utf-8") as f:
    h_content = f.read()

has_canonical = "link rel=\"canonical\"" in h_content
has_og = "og:title" in h_content
has_twitter = "twitter:card" in h_content
has_jsonld = "application/ld+json" in h_content

findings["seo_issues"].append({
    "header_has_canonical": has_canonical,
    "header_has_og": has_og,
    "header_has_twitter": has_twitter,
    "header_has_jsonld": has_jsonld
})
print(f"  -> SEO Global Check: Canonical={has_canonical}, OG={has_og}, Twitter={has_twitter}, JSON-LD={has_jsonld}")

# SAVE FULL REPORT JSON
out_path = os.path.join(ROOT, "scratch", "deep_audit_results.json")
with open(out_path, "w", encoding="utf-8") as f:
    json.dump(findings, f, indent=2, ensure_ascii=False)

print(f"\n[DONE] Risultati salvati in: {out_path}")
