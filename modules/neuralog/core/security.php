<?php
/* ============================================================================
   COMPANY BRAIN — core/security.php
   Regole, in ordine di importanza:
   1) FAIL-CLOSED: se non si riesce a stabilire che sei admin, non sei admin.
   2) Nessun segreto nel codice: la chiave sta SOLO nell'ambiente
      (BRAIN_ADMIN_KEY, nome configurabile). Se non c'e', gli endpoint admin
      restano chiusi — non "aperti perche' manca la chiave".
   3) Il pubblico vede solo visibility='public'.
   4) Muro anti-fuga: anche un nodo pubblico per errore non esce se contiene
      pattern da segreto.
   5) Scritture solo in POST, rate limit su file, nessun segreto nei log.
============================================================================ */
require_once __DIR__ . '/config.php';

/** La chiave admin, presa SOLO dall'ambiente. Mai da file, mai da config. */
function brain_admin_key(): string {
    $var = (string)brain_cfg('security.admin_key_env', 'BRAIN_ADMIN_KEY');
    $k = getenv($var);
    if ($k === false || $k === '') { $k = $_SERVER[$var] ?? ''; }
    return (string)$k;
}

/**
 * Sei admin? Tre vie:
 *   - CLI (chi ha la shell ha gia' tutto)
 *   - callback dell'ospite brain_host_is_admin() (sessione dell'app)
 *   - chiave in ?key= / header X-Brain-Key, confrontata con hash_equals
 */
function brain_is_admin(): bool {
    if (PHP_SAPI === 'cli') { return true; }
    if (function_exists('brain_host_is_admin')) {
        try { if (brain_host_is_admin() === true) { return true; } } catch (Throwable $e) {}
    }
    $k = brain_admin_key();
    if ($k === '') { return false; }                       // fail-closed
    $given = (string)($_SERVER['HTTP_X_BRAIN_KEY'] ?? ($_REQUEST['key'] ?? ''));
    return $given !== '' && hash_equals($k, $given);
}

/** Blocca se non admin (JSON). */
function brain_require_admin(): void {
    if (brain_is_admin()) { return; }
    brain_json(['ok' => false, 'error' => 'forbidden'], 403);
}

/** Le API pubbliche sono attive? */
function brain_public_api_enabled(): bool {
    return (bool)brain_cfg('security.public_api', true);
}

/** Impronta anonima del chiamante (mai l'IP in chiaro nei dati). */
function brain_ip_hash(string $salt = ''): string {
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'cli');
    return substr(hash('sha256', $ip . '|' . $salt), 0, 24);
}

/**
 * Rate limit a finestra fissa, su file: funziona su hosting condiviso senza
 * Redis. Ritorna true se la richiesta e' consentita.
 */
function brain_rate_limit(string $bucket, ?int $max = null, ?int $window = null): bool {
    if (PHP_SAPI === 'cli') { return true; }
    $max    = $max    ?? (int)brain_cfg('security.rate_limit_max', 60);
    $window = $window ?? (int)brain_cfg('security.rate_limit_window_sec', 60);
    if ($max <= 0) { return true; }
    $dir = brain_data_dir() . '/ratelimit';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $slot = (int)floor(time() / max(1, $window));
    $file = $dir . '/' . preg_replace('/[^a-z0-9_.-]/i', '', $bucket) . '_' . brain_ip_hash($bucket) . '_' . $slot . '.cnt';
    $n = 0;
    $fh = @fopen($file, 'c+');
    if (!$fh) { return true; }                 // se non si puo' contare, non si blocca il servizio
    if (flock($fh, LOCK_EX)) {
        $n = (int)stream_get_contents($fh);
        $n++;
        ftruncate($fh, 0); rewind($fh); fwrite($fh, (string)$n);
        flock($fh, LOCK_UN);
    }
    fclose($fh);
    if ($n === 1) { brain_rate_gc($dir, $window); }
    return $n <= $max;
}

/** Pulizia dei contatori vecchi (1 su N chiamate, costo trascurabile). */
function brain_rate_gc(string $dir, int $window): void {
    if (mt_rand(1, 50) !== 1) { return; }
    $cut = time() - ($window * 4);
    foreach ((glob($dir . '/*.cnt') ?: []) as $f) {
        if (@filemtime($f) < $cut) { @unlink($f); }
    }
}

/** Le scritture passano solo in POST (CSRF-lite) salvo chiave esplicita. */
function brain_require_post(): void {
    if (PHP_SAPI === 'cli') { return; }
    if (!brain_cfg('security.require_post_for_write', true)) { return; }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { return; }
    brain_json(['ok' => false, 'error' => 'usa POST'], 405);
}

/**
 * Token CSRF-lite legato alla sessione dell'ospite quando c'e'; quando non c'e'
 * sessione il metodo POST resta l'unica barriera (dichiarato, non nascosto).
 */
function brain_csrf_token(): string {
    if (PHP_SAPI === 'cli') { return ''; }
    if (session_status() !== PHP_SESSION_ACTIVE) { return ''; }
    if (empty($_SESSION['brain_csrf'])) { $_SESSION['brain_csrf'] = bin2hex(random_bytes(16)); }
    return (string)$_SESSION['brain_csrf'];
}
function brain_csrf_ok(): bool {
    if (PHP_SAPI === 'cli') { return true; }
    $tok = brain_csrf_token();
    if ($tok === '') { return true; }               // niente sessione: nessun token da verificare
    $given = (string)($_POST['csrf'] ?? ($_SERVER['HTTP_X_BRAIN_CSRF'] ?? ''));
    return $given !== '' && hash_equals($tok, $given);
}

/** Pattern "questo e' un segreto" compilati dalla config. */
function brain_leak_regex(): string {
    static $re = null;
    if ($re !== null) { return $re; }
    $pats = (array)brain_cfg('security.leak_patterns', []);
    $pats = array_filter(array_map('strval', $pats));
    $re = $pats ? '/(' . implode('|', $pats) . ')/i' : '/(?!)/';
    return $re;
}

/** true se il testo contiene qualcosa che non deve mai uscire. */
function brain_looks_secret(string $text): bool {
    return (bool)preg_match(brain_leak_regex(), $text);
}

/** Redazione per log e messaggi d'errore. */
function brain_redact(string $text): string {
    return (string)preg_replace(brain_leak_regex(), '[REDATTO]', $text);
}

/** Il percorso/nome file e' da considerare segreto? (glob da config) */
function brain_is_secret_path(string $path): bool {
    $base = basename($path);
    foreach ((array)brain_cfg('ingest.secret_names', []) as $glob) {
        if (fnmatch((string)$glob, $base, FNM_CASEFOLD) || fnmatch((string)$glob, $path, FNM_CASEFOLD)) { return true; }
    }
    return false;
}

/** Risposta JSON standard e uscita. */
function brain_json($data, int $status = 200): void {
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Intestazione comune delle pagine UI (niente inline eval, niente CDN forzato). */
function brain_ui_headers(): void {
    if (headers_sent()) { return; }
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
}
