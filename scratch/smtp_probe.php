<?php
header('Content-Type: text/plain');

$hosts = [
    ['host' => 'ssl://smtp.hostinger.com', 'port' => 465],
    ['host' => 'smtp.hostinger.com', 'port' => 587],
    ['host' => 'localhost', 'port' => 25],
    ['host' => '127.0.0.1', 'port' => 587]
];

foreach ($hosts as $h) {
    echo "Testing " . $h['host'] . ":" . $h['port'] . "... ";
    $errno = 0;
    $errstr = '';
    $fp = @fsockopen($h['host'], $h['port'], $errno, $errstr, 5);
    if ($fp) {
        $banner = fgets($fp, 512);
        echo "CONNECTED! Banner: " . trim($banner) . "
";
        fclose($fp);
    } else {
        echo "FAILED: $errstr ($errno)
";
    }
}
