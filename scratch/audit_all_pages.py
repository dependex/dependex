import os
import subprocess
import json

root = r'c:\81PLUS_GLOBAL_MASTER\dependex.social'
php_pages = [f for f in os.listdir(root) if f.endswith('.php') and not f.startswith('_') and not f.startswith('.')]

print(f'Auditing {len(php_pages)} top-level PHP files with authenticated admin session & shutdown buffer handler...')

php_runner_template = """<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'dependex.social';
$_SERVER['REQUEST_URI'] = '/{page}';
$_SERVER['SCRIPT_NAME'] = '/{page}';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) Mobile/15E148';
$_GET = ['id' => '1', 'code' => 'TEST', 'slug' => 'test-slug', 'token' => 'TEST_TOKEN', 'sic' => 'SIC-TAGLIODIPO-RO-001', 'q' => 'Rovigo', 'event' => 'SIC-EVT-ACAT-BP-2026-COMM'];
$_POST = [];
session_start();
// Authenticated user session for admin/member pages, guest for login/register
if (in_array('{page}', ['login.php', 'register.php'])) {
    $_SESSION = [];
} else {
    $_SESSION['user_sic_id'] = 'SIC-ADM00001-DEPENDEX-9';
}

register_shutdown_function(function() {
    $err = error_get_last();
    $out = ob_get_contents();
    @ob_end_clean();
    
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "FATAL|" . $err['message'] . "|" . $err['file'] . ":" . $err['line'];
        return;
    }
    
    $len = strlen($out);
    $title = 'NO_TITLE';
    if (preg_match('#<title>(.*?)</title>#is', $out, $m)) {
        $title = trim($m[1]);
    }
    $hasVp = (stripos($out, 'name="viewport"') !== false || stripos($out, "name='viewport'") !== false) ? '1' : '0';
    $hasDesc = (stripos($out, 'name="description"') !== false || stripos($out, "name='description'") !== false) ? '1' : '0';
    $hasH1 = (stripos($out, '<h1') !== false) ? '1' : '0';
    $hasHtml = (stripos($out, '<!doctype html') !== false || stripos($out, '<html') !== false) ? '1' : '0';
    
    echo "AUDIT_RESULT|" . $len . "|" . $title . "|" . $hasVp . "|" . $hasDesc . "|" . $hasH1 . "|" . $hasHtml;
});

ob_start();
try {
    require __DIR__ . '/../{page}';
} catch (Throwable $e) {
    @ob_end_clean();
    echo "FATAL|" . $e->getMessage() . "|" . $e->getFile() . ":" . $e->getLine();
    exit(1);
}
"""

scratch_dir = os.path.join(root, 'scratch')
os.makedirs(scratch_dir, exist_ok=True)
runner_file = os.path.join(scratch_dir, 'page_runner.php')

results = []
for page in sorted(php_pages):
    with open(runner_file, 'w', encoding='utf-8') as rf:
        rf.write(php_runner_template.replace('{page}', page))
    
    res = subprocess.run(['php', runner_file], cwd=root, capture_output=True)
    out = res.stdout.decode('utf-8', errors='replace').strip()
    err = res.stderr.decode('utf-8', errors='replace').strip()
    results.append((page, out, err))

# Known non-HTML or backend libraries / endpoints
backend_libs = {
    'api.php', 'bootstrap.php', 'company-brain-start.php', 'config.php', 'db.php',
    'dr-env.php', 'dr-network-struttura.php', 'eco-db.php', 'eco-sic.php',
    'email-engine.php', 'mailer.php', 'network-engine.php'
}

api_endpoints = {
    'adapter.php', 'action.php', 'api-cortex.php', 'api-event-booking.php', 'api-lead.php',
    'cron.php', 'cron-sync.php', 'event-ics.php', 'feed.php', 'logout.php',
    'manifest.php', 'newsletter.php', 'preferences.php', 'robots.txt.php',
    'sitemap.php', 'webhook-paypal.php', 'welfare-compass.php'
}

html_audits = []
fatals = []
services = []

for page, out, err in results:
    if page in backend_libs:
        services.append((page, 'BACKEND_LIB'))
        continue
    if any(page.startswith(p) for p in ['api-', 'webhook-', 'cron', 'verify_', 'download_']) or page in api_endpoints:
        services.append((page, 'API_SERVICE'))
        continue
        
    if 'FATAL|' in out:
        fatals.append((page, out, err))
    elif 'AUDIT_RESULT|' in out:
        idx = out.find('AUDIT_RESULT|')
        parts = out[idx:].split('|')
        length = int(parts[1]) if len(parts) > 1 and parts[1].isdigit() else 0
        title = parts[2] if len(parts) > 2 else ''
        vp = parts[3] == '1' if len(parts) > 3 else False
        desc = parts[4] == '1' if len(parts) > 4 else False
        h1 = parts[5] == '1' if len(parts) > 5 else False
        is_html = parts[6] == '1' if len(parts) > 6 else False
        html_audits.append({
            'page': page,
            'len': length,
            'title': title,
            'vp': vp,
            'desc': desc,
            'h1': h1,
            'is_html': is_html
        })
    else:
        fatals.append((page, out, err))

print(f'\n=== AUDIT SUMMARY ===')
print(f'Total pages tested: {len(php_pages)}')
print(f'Backend internal libraries: {len([s for s in services if s[1] == "BACKEND_LIB"])}')
print(f'API endpoints / services / redirects: {len([s for s in services if s[1] == "API_SERVICE"])}')
print(f'HTML User-Facing Pages: {len(html_audits)}')
print(f'Fatal errors: {len(fatals)}')

for p, o, e in fatals:
    safe_o = o.encode('ascii', errors='replace').decode('ascii')
    safe_e = e.encode('ascii', errors='replace').decode('ascii')
    print(f'  [FAIL] {p}: {safe_o[:100]} {safe_e[:100]}')

no_vp = [a['page'] for a in html_audits if not a['vp']]
no_desc = [a['page'] for a in html_audits if not a['desc']]
no_h1 = [a['page'] for a in html_audits if not a['h1']]

print(f'\n=== SEO & MOBILE-FIRST METRICS ===')
print(f'Missing viewport meta: {len(no_vp)}')
for p in no_vp:
    print(f'  - {p}')
print(f'Missing meta description: {len(no_desc)}')
for p in no_desc:
    print(f'  - {p}')
print(f'Missing <h1>: {len(no_h1)}')
for p in no_h1:
    print(f'  - {p}')

all_perfect = len(fatals) == 0 and len(no_vp) == 0 and len(no_desc) == 0 and len(no_h1) == 0
print(f'\nAll user-facing HTML pages pass 100% check: {all_perfect}')
