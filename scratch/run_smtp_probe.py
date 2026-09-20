import urllib.request
import ftplib

php_code = """<?php
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
        echo "CONNECTED! Banner: " . trim($banner) . "\n";
        fclose($fp);
    } else {
        echo "FAILED: $errstr ($errno)\n";
    }
}
"""

with open("scratch/smtp_probe.php", "w") as f:
    f.write(php_code)

ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
with open("scratch/smtp_probe.php", "rb") as f:
    ftp.storbinary("STOR smtp_probe.php", f)
ftp.quit()

res = urllib.request.urlopen("https://dependex.social/smtp_probe.php").read().decode()
print("SMTP probe results:\n" + res)

ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
try:
    ftp.delete("smtp_probe.php")
except:
    pass
ftp.quit()
