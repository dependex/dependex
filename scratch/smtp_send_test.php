<?php
header('Content-Type: text/plain');

function test_smtp($user, $pass) {
    echo "=== Testing $user ===\n";
    $socket = stream_socket_client("ssl://smtp.hostinger.com:465", $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
    if (!$socket) {
        echo "Socket error: $errstr ($errno)\n";
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
        fputs($socket, $cmd . "\r\n");
        $res = $read();
        echo ">> " . (strpos($cmd, 'AUTH') !== false ? 'AUTH ...' : $cmd) . "\n<< " . trim($res) . "\n";
        return $res;
    };
    
    echo "<< " . trim($read()) . "\n";
    $send("EHLO dependex.social");
    $send("AUTH LOGIN");
    $send(base64_encode($user));
    $send(base64_encode($pass));
    $send("MAIL FROM: <$user>");
    $res = $send("RCPT TO: <labomobile.lm@gmail.com>");
    if (strpos($res, '250') !== false) {
        $send("DATA");
        fputs($socket, "Subject: Test from Hostinger Server\r\nFrom: $user\r\nTo: labomobile.lm@gmail.com\r\n\r\nTest OK\r\n.\r\n");
        $final = $read();
        echo "<< " . trim($final) . "\n";
        echo "SUCCESS FOR $user!\n";
    }
    $send("QUIT");
    fclose($socket);
}

test_smtp('info@dependex.support', 'h29031976T.');
test_smtp('info@dependex.social', 'h29031976T.');
