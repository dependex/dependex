<?php
/* ============================================================================
   COMPANY BRAIN — ui/_ui.php
   Tema e servizi comuni delle pagine. NESSUN colore di marca nel codice: le
   variabili CSS --brain-* arrivano da config (ui.theme) e si sovrascrivono in
   config/brain.local.json senza toccare un solo file PHP.
============================================================================ */
require_once dirname(__DIR__) . '/brain.php';

/** Blocco :root con le variabili del tema. */
function brain_theme_css(): string {
    $vars = (array)brain_cfg('ui.theme', []);
    $out = ":root{";
    foreach ($vars as $k => $v) {
        $k = preg_replace('/[^a-z0-9\-]/i', '', (string)$k);
        $v = str_replace(['"', ';', '<', '>'], '', (string)$v);
        if ($k === '') { continue; }
        $out .= $k . ':' . $v . ';';
    }
    return $out . '}';
}

/** Escape per HTML. */
function brain_e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/** La stringa ?key=... da propagare ai fetch quando si entra con la chiave. */
function brain_key_qs(): string {
    if (PHP_SAPI === 'cli') { return ''; }
    $k = (string)($_GET['key'] ?? '');
    return ($k !== '' && brain_is_admin()) ? '?key=' . rawurlencode($k) : '';
}

/** Foglio di stile comune (piccolo, senza dipendenze). */
function brain_base_css(): string {
    return brain_theme_css() . <<<CSS
*{box-sizing:border-box}
html,body{margin:0;background:var(--brain-bg);color:var(--brain-fg);
  font:14px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif}
a{color:var(--brain-accent-2);text-decoration:none}
a:hover{text-decoration:underline}
h1,h2,h3{font-weight:650;letter-spacing:.2px;margin:0 0 10px}
h1{font-size:19px}h2{font-size:15px;color:var(--brain-muted);text-transform:uppercase;letter-spacing:1px}
.wrap{max-width:1180px;margin:0 auto;padding:18px}
.card{background:var(--brain-panel);border:1px solid var(--brain-line);
  border-radius:var(--brain-radius);padding:14px 16px;margin:0 0 14px}
.grid{display:grid;gap:14px}
.g4{grid-template-columns:repeat(auto-fit,minmax(180px,1fr))}
.g2{grid-template-columns:repeat(auto-fit,minmax(320px,1fr))}
.kpi{font-size:26px;font-weight:700;color:var(--brain-accent);font-variant-numeric:tabular-nums}
.k{color:var(--brain-muted);font-size:12px;text-transform:uppercase;letter-spacing:.8px}
table{width:100%;border-collapse:collapse;font-size:13px}
th,td{text-align:left;padding:6px 8px;border-bottom:1px solid var(--brain-line);vertical-align:top;
  overflow-wrap:anywhere;word-break:break-word}
th{color:var(--brain-muted);font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.6px}
button,.btn{background:var(--brain-panel);color:var(--brain-fg);border:1px solid var(--brain-line);
  border-radius:8px;padding:7px 12px;cursor:pointer;font-size:13px}
button:hover,.btn:hover{border-color:var(--brain-accent)}
input,textarea,select{background:var(--brain-bg);color:var(--brain-fg);border:1px solid var(--brain-line);
  border-radius:8px;padding:7px 10px;font:inherit;width:100%}
.row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.ok{color:var(--brain-ok)}.warn{color:var(--brain-warn)}.err{color:var(--brain-err)}
.mut{color:var(--brain-muted)}
.nw{white-space:nowrap;word-break:normal;overflow-wrap:normal}
code,pre{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px}
pre{white-space:pre-wrap;word-break:break-word;background:var(--brain-bg);
  border:1px solid var(--brain-line);border-radius:8px;padding:10px;max-height:340px;overflow:auto}
.pill{display:inline-flex;gap:6px;align-items:center;border:1px solid var(--brain-line);
  border-radius:999px;padding:4px 10px;font-size:12px;background:var(--brain-panel)}
.dot{width:8px;height:8px;border-radius:50%;background:var(--brain-ok);
  box-shadow:0 0 8px var(--brain-ok);animation:beat 1.6s infinite}
@keyframes beat{0%,100%{opacity:.35;transform:scale(.85)}50%{opacity:1;transform:scale(1.2)}}
CSS;
}
