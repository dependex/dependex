# -*- coding: utf-8 -*-
"""
audit_all_pages.py — Audit completo e capillare di TUTTE le pagine PHP e HTML.
1. PHP Lint (php -l) su ogni file
2. Verifica broken includes/requires statici
3. Scansione parole vietate (magico, magic, M.A.G.I.C., giorgian putanu, 81plus)
4. Esecuzione headless o probe HTTP live su dependex.social
5. Rilevazione errori HTML/PHP espliciti
"""

import os
import glob
import subprocess
import re
import urllib.request
import urllib.error
import ssl
from collections import defaultdict

ROOT_DIR = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"
BANNED_PATTERNS = [
    re.compile(r'\bmagico\b', re.IGNORECASE),
    re.compile(r'\bmagic\b', re.IGNORECASE),
    re.compile(r'M\.A\.G\.I\.C\.', re.IGNORECASE),
    re.compile(r'giorgian\s+putanu', re.IGNORECASE),
    re.compile(r'\b81plus\b', re.IGNORECASE),
]

# File speciali esenti dal controllo "81plus" perché fanno riferimento a percorsi file di sistema Windows (es. c:\81PLUS_GLOBAL_MASTER\...)
SYSTEM_PATH_WHITELIST = ["AGENTS.md"]

def find_all_files():
    php_files = glob.glob(os.path.join(ROOT_DIR, "*.php"))
    html_files = glob.glob(os.path.join(ROOT_DIR, "*.html"))
    
    # Includi anche sottocartelle pertinenti
    sub_dirs = ["templates", "modules", "bin"]
    for sd in sub_dirs:
        php_files.extend(glob.glob(os.path.join(ROOT_DIR, sd, "*.php")))
        php_files.extend(glob.glob(os.path.join(ROOT_DIR, sd, "**", "*.php"), recursive=True))
        html_files.extend(glob.glob(os.path.join(ROOT_DIR, sd, "*.html")))
        html_files.extend(glob.glob(os.path.join(ROOT_DIR, sd, "**", "*.html"), recursive=True))
        
    return sorted(list(set(php_files))), sorted(list(set(html_files)))

def check_php_syntax(file_path):
    try:
        res = subprocess.run(["php", "-l", file_path], capture_output=True, text=True, check=False)
        if res.returncode != 0:
            return False, res.stderr.strip() or res.stdout.strip()
        return True, ""
    except Exception as e:
        return False, str(e)

def check_banned_words(file_path, content):
    rel_path = os.path.relpath(file_path, ROOT_DIR)
    findings = []
    
    lines = content.splitlines()
    for idx, line in enumerate(lines, 1):
        # Ignora se è solo un percorso filesystem Windows tipo C:\81PLUS_GLOBAL_MASTER
        clean_line = re.sub(r'c:[\\/]81plus[_\w\\]*', '', line, flags=re.IGNORECASE)
        # Ignora la riga di AGENTS.md che menziona la regola stessa ("Zero riferimenti residui a 81plus")
        if "Zero riferimenti residui a `81plus`" in line or "È vietato l'uso delle seguenti parole" in line:
            continue
            
        for pat in BANNED_PATTERNS:
            m = pat.search(clean_line)
            if m:
                findings.append(f"Riga {idx}: trovato termine vietato '{m.group(0)}' in: {line.strip()[:80]}")
    return findings

def check_static_includes(file_path, content):
    """Verifica se i require / include puntano a file effettivamente esistenti."""
    rel_path = os.path.relpath(file_path, ROOT_DIR)
    dir_path = os.path.dirname(file_path)
    findings = []
    
    include_regex = re.compile(r'''(?:require|include)(?:_once)?\s*(?:\(?\s*__DIR__\s*\.\s*)?['"]([a-zA-Z0-9_\-./\\]+\.php)['"]''', re.IGNORECASE)
    for match in include_regex.finditer(content):
        inc = match.group(1).lstrip("/\\")
        
        # Tentativo 1: relativo alla dir corrente
        p1 = os.path.join(dir_path, inc)
        # Tentativo 2: relativo alla ROOT_DIR
        p2 = os.path.join(ROOT_DIR, inc)
        
        if not os.path.exists(p1) and not os.path.exists(p2):
            findings.append(f"Include non risolto: '{inc}' (non esiste né in {os.path.basename(dir_path)} né in root)")
            
    return findings

PUBLIC_PAGES = {
    "index.php", "world-club-explorer.php", "mappa-club.php", "orientamento.php",
    "playground.php", "clips.php", "events-public.php", "parla-con-noi.php",
    "recensioni.php", "metodo.php", "ruota-della-vita.php", "piramide-maslow.php",
    "guida-gratuita.php", "domande-frequenti.php", "faq.php", "help.php",
    "privacy.php", "privacy-center.php", "sobriety.php", "offline.html", "terms.php",
    "telemetria.php", "academy-public.php", "dashboard.php",
    "crociera-benessere-masterclass.php", "viaggi-esperienziali.php", "event-detail.php",
    "offers.php", "storie.php", "world-map.php", "login.php", "register.php", "cart.php"
}

def test_live_http(rel_name):
    """Testa la risposta HTTP live su dependex.social per le pagine pubbliche"""
    if os.path.dirname(rel_name) != "" or rel_name not in PUBLIC_PAGES:
        return None  # solo pagine pubbliche
        
    url = f"https://dependex.social/{rel_name}?t=audit"
    ctx = ssl.create_default_context()
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "Dependex-Audit/1.0"})
        with urllib.request.urlopen(req, context=ctx, timeout=8) as response:
            code = response.status
            body = response.read().decode('utf-8', errors='ignore')
            
            # Controllo errori PHP visualizzati
            errors = []
            if "Fatal error" in body:
                m = re.search(r'Fatal error[:\s].*?(?=<br|</div>|\n)', body)
                errors.append(f"Fatal error rilevato: {m.group(0) if m else 'Fatal error'}")
            if "Parse error" in body:
                m = re.search(r'Parse error[:\s].*?(?=<br|</div>|\n)', body)
                errors.append(f"Parse error rilevato: {m.group(0) if m else 'Parse error'}")
            if "Warning:" in body and "Failed opening required" in body:
                m = re.search(r'Warning:.*?Failed opening required.*?(?=<br|</div>|\n)', body)
                errors.append(f"Broken require in live: {m.group(0) if m else 'Broken require'}")
                
            return {
                "status": code,
                "bytes": len(body),
                "live_errors": errors
            }
    except urllib.error.HTTPError as he:
        return {
            "status": he.code,
            "bytes": 0,
            "live_errors": [f"HTTP {he.code}"]
        }
    except Exception as ex:
        return {
            "status": 0,
            "bytes": 0,
            "live_errors": [f"Errore connessione: {str(ex)[:60]}"]
        }

def main():
    print("=== AVVIO AUDIT CAPILLARE TUTTE LE PAGINE PHP ED HTML ===")
    php_files, html_files = find_all_files()
    print(f"File PHP trovati: {len(php_files)}")
    print(f"File HTML trovati: {len(html_files)}")
    print("---------------------------------------------------------")

    report = {
        "syntax_errors": [],
        "broken_includes": [],
        "banned_terms": [],
        "live_http_errors": [],
        "healthy_count": 0,
        "checked_files": 0
    }

    all_files = [(f, "PHP") for f in php_files] + [(f, "HTML") for f in html_files]

    for file_path, ftype in all_files:
        report["checked_files"] += 1
        rel_path = os.path.relpath(file_path, ROOT_DIR)
        file_healthy = True
        
        # 1. Syntax Check per PHP
        if ftype == "PHP":
            syn_ok, syn_err = check_php_syntax(file_path)
            if not syn_ok:
                file_healthy = False
                report["syntax_errors"].append((rel_path, syn_err))
                print(f"[SYNTAX ERROR] {rel_path}: {syn_err}")

        # Lettura contenuto
        try:
            with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
                content = f.read()
        except Exception as e:
            content = ""

        # 2. Controllo Include Statici (PHP)
        if ftype == "PHP":
            inc_errs = check_static_includes(file_path, content)
            if inc_errs:
                file_healthy = False
                for ie in inc_errs:
                    report["broken_includes"].append((rel_path, ie))
                    print(f"[BROKEN INCLUDE] {rel_path}: {ie}")

        # 3. Controllo Parole Vietate
        banned = check_banned_words(file_path, content)
        if banned:
            file_healthy = False
            for b in banned:
                report["banned_terms"].append((rel_path, b))
                print(f"[BANNED WORD] {rel_path}: {b}")

        # 4. Controllo Live HTTP se file pubblico root
        if os.path.dirname(rel_path) == "":
            live_res = test_live_http(rel_path)
            if live_res:
                if live_res["live_errors"]:
                    file_healthy = False
                    for le in live_res["live_errors"]:
                        report["live_http_errors"].append((rel_path, le))
                        print(f"[LIVE HTTP ERROR] {rel_path}: {le}")

        if file_healthy:
            report["healthy_count"] += 1

    print("\n=========================================================")
    print("                 RIEPILOGO FINALE AUDIT                  ")
    print("=========================================================")
    print(f"File totali analizzati: {report['checked_files']}")
    print(f"File completamente SANI (100% OK): {report['healthy_count']}")
    print(f"Errori di sintassi PHP: {len(report['syntax_errors'])}")
    print(f"Broken requires / includes: {len(report['broken_includes'])}")
    print(f"Occorrenze parole vietate: {len(report['banned_terms'])}")
    print(f"Errori riscontrati su HTTP Live: {len(report['live_http_errors'])}")

    # Salva report JSON dettagliato
    import json
    out_json = os.path.join(ROOT_DIR, "data", "AUDIT_PAGES_REPORT.json")
    with open(out_json, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2, ensure_ascii=False)
    print(f"\nReport dettagliato scritto in: {out_json}")

if __name__ == "__main__":
    main()
