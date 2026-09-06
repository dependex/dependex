<?php
require_once __DIR__ . '/../bootstrap.php';

function testDomain(string $host) {
    $_SERVER['HTTP_HOST'] = $host;
    echo "Host: {$host}\n";
    echo "  - site_mode(): " . site_mode() . "\n";
    echo "  - site_locale(): " . site_locale() . "\n";
    $brand = site_brand();
    echo "  - site_brand(): " . json_encode($brand, JSON_UNESCAPED_SLASHES) . "\n";
}

testDomain('dependex.social');
testDomain('oltre.social');
