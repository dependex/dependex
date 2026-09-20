import urllib.request
import ftplib

php_code = """<?php
header('Content-Type: text/plain');

function test_smtp($user, $pass) {
    echo "=== Testing $user ===\\n";
    $socket = stream_socket_client("ssl://smtp.hostinger.com:465", $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        echo "Socket error: $errstr ($errno)\\n";
        return;
    }
    
    $read = function() use ($socket) {
        $res = '';
        while ($str = fgets($socket, 515)) {
            $res .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $res;
    };
    
    $send = function($cmd) use ($socket, $read) {
        fputs($socket, $cmd . "\\r\\n");
        $res = $read();
        echo ">> " . (strpos($cmd, 'AUTH') !== false ? 'AUTH ...' : $cmd) . "\\n<< " . trim($res) . "\\n";
        return $res;
    };
    
    echo "<< " . trim($read()) . "\\n";
    $send("EHLO dependex.social");
    $send("AUTH LOGIN");
    $send(base64_encode($user));
    $send(base64_encode($pass));
    $send("MAIL FROM: <$user>");
    $res = $send("RCPT TO: <labomobile.lm@gmail.com>");
    if (strpos($res, '250') !== false) {
        $send("DATA");
        fputs($socket, "Subject: Test from Hostinger Server\\r\\nFrom: $user\\r\\nTo: labomobile.lm@gmail.com\\r\\n\\r\\nTest OK\\r\\n.\\r\\n");
        $final = $read();
        echo "<< " . trim($final) . "\\n";
        echo "SUCCESS FOR $user!\\n";
    }
    $send("QUIT");
    fclose($socket);
}

test_smtp('info@dependex.support', 'h29031976T.');
test_smtp('info@dependex.social', 'h29031976T.');
"""

with open("scratch/smtp_send_test.php", "w") as f:
    f.write(php_code)

ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
with open("scratch/smtp_send_test.php", "rb") as f:
    ftp.storbinary("STOR smtp_send_test.php", f)
ftp.quit()

res = urllib.request.urlopen("https://dependex.social/smtp_send_test.php").read().decode()
print("SMTP send test results:\n" + res)

ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
try:
    ftp.delete("smtp_send_test.php")
except:
    pass
ftp.quit()
