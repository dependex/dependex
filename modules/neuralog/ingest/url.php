<?php
/* ============================================================================
   COMPANY BRAIN — ingest/url.php
   Ingestione da URL. SPENTA di default (ingest.url.enabled=false) e comunque
   limitata a una lista di host ammessi: un cervello che scarica qualunque cosa
   dal web e' una porta aperta, non una funzione.
   Non segue redirect verso host non ammessi, non tocca IP privati.
============================================================================ */
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/security.php';
require_once __DIR__ . '/documents.php';
require_once __DIR__ . '/text.php';

/** L'URL e' ammesso dalla config? */
function brain_url_allowed(string $url): bool {
    if (!brain_cfg('ingest.url.enabled', false)) { return false; }
    $p = parse_url($url);
    if (!$p || ($p['scheme'] ?? '') !== 'https') { return false; }
    $host = strtolower((string)($p['host'] ?? ''));
    if ($host === '') { return false; }
    $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : null;
    if ($ip && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) { return false; }
    foreach ((array)brain_cfg('ingest.url.allow_hosts', []) as $h) {
        $h = strtolower((string)$h);
        if ($h !== '' && ($host === $h || str_ends_with($host, '.' . $h))) { return true; }
    }
    return false;
}

/** Scarica e ingerisce una pagina. Ritorna il risultato di brain_ingest_text. */
function brain_ingest_url(string $url, array $opts = []): array {
    if (!brain_url_allowed($url)) { return ['ok' => false, 'error' => 'url non ammesso (ingest.url spenta o host fuori lista)']; }
    $max = (int)brain_cfg('ingest.url.max_bytes', 500000);
    $to  = (int)brain_cfg('ingest.url.timeout_sec', 8);
    $body = '';
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => $to, CURLOPT_CONNECTTIMEOUT => $to,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'CompanyBrain/' . brain_version(),
        ]);
        $body = (string)curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => $to, 'follow_location' => 0]]);
        $body = (string)@file_get_contents($url, false, $ctx, 0, $max);
    }
    if ($body === '') { return ['ok' => false, 'error' => 'risposta vuota']; }
    $text = brain_html_to_text(substr($body, 0, $max));
    if ($text === '') { return ['ok' => false, 'error' => 'nessun testo estratto']; }
    return brain_ingest_text($text, array_merge([
        'path' => 'url/' . brain_slug($url, 80), 'title' => $url,
        'source' => 'url', 'section' => 'document', 'id_prefix' => 'url',
    ], $opts));
}
