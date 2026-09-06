<?php
/**
 * PING SEARCH ENGINES & INDEXNOW
 * Invia notifiche di aggiornamento sitemap e pagine a Google e Bing
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$domains = ['dependex.social', 'oltre.social'];
$urlsToNotify = [
    'https://%s/',
    'https://%s/guida-gratuita.php',
    'https://%s/metodo.php',
    'https://%s/offers.php',
    'https://%s/world-club-explorer.php',
    'https://%s/events-public.php',
    'https://%s/help.php'
];

echo "=== INIZIO SEGNALAZIONE MOTORI DI RICERCA & INDEXNOW ===\n";

foreach ($domains as $domain) {
    echo "\n--- DOMINIO: {$domain} ---\n";
    $sitemapUrl = "https://{$domain}/sitemap.xml";

    // 1. Google Sitemap Ping
    $googlePing = "https://www.google.com/ping?sitemap=" . urlencode($sitemapUrl);
    echo "1. Notifica Google Sitemap... ";
    $res = @file_get_contents($googlePing, false, stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true]
    ]));
    echo "Segnalato ($googlePing)\n";

    // 2. IndexNow (Bing / Yandex)
    $urlList = array_map(fn($tpl) => sprintf($tpl, $domain), $urlsToNotify);
    $indexNowPayload = json_encode([
        'host' => $domain,
        'key' => 'dependex_indexnow_key_2026',
        'keyLocation' => "https://{$domain}/dependex_indexnow_key_2026.txt",
        'urlList' => $urlList
    ], JSON_UNESCAPED_SLASHES);

    echo "2. Invio IndexNow ({count($urlList)} URLs)... ";
    $ch = curl_init('https://api.indexnow.org/indexnow');
    if ($ch) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $indexNowPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo "HTTP {$httpCode}\n";
    } else {
        echo "cURL non disponibile\n";
    }
}

echo "\nCompletato con successo!\n";
