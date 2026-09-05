<?php
/* ============================================================================
   COMPANY BRAIN — api/v1/_boot.php
   Avvio comune di tutte le API: carica il motore, decide se chi chiama e'
   admin, applica il rate limit e prepara la risposta JSON.
   Regola: se le API pubbliche sono spente in config, tutto quello che non e'
   admin riceve 403. Fail-closed, sempre.
============================================================================ */
require_once dirname(__DIR__, 2) . '/brain.php';

if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE && function_exists('brain_host_is_admin')) {
    @session_start();
}

/** true se chi chiama e' admin. */
$BRAIN_ADMIN = brain_is_admin();

/** Applica il gate d'ingresso di un endpoint. */
function brain_api_gate(string $bucket, bool $adminOnly = false): bool {
    $admin = brain_is_admin();
    if ($adminOnly && !$admin) { brain_json(['ok' => false, 'error' => 'forbidden'], 403); }
    if (!$admin && !brain_public_api_enabled()) { brain_json(['ok' => false, 'error' => 'api pubbliche disattivate'], 403); }
    if (!$admin && !brain_rate_limit($bucket)) { brain_json(['ok' => false, 'error' => 'troppe richieste, riprova tra poco'], 429); }
    if (!brain_schema_ready()) { brain_json(['ok' => false, 'error' => 'schema non installato'], 503); }
    return $admin;
}

/** Parametro di richiesta, ripulito. */
function brain_param(string $name, $default = '') {
    $v = $_REQUEST[$name] ?? $default;
    return is_string($v) ? trim($v) : $v;
}
