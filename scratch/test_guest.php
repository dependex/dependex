<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'dependex.social';
$_SERVER['SCRIPT_NAME'] = '/login.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_GET = [];
$_POST = [];
// guest
session_start();
$_SESSION = [];

foreach (['login.php', 'register.php'] as $p) {
    ob_start();
    require $p;
    $out = ob_get_clean();
    $vp = (stripos($out, 'name="viewport"') !== false || stripos($out, "name='viewport'") !== false) ? 1 : 0;
    $desc = (stripos($out, 'name="description"') !== false || stripos($out, "name='description'") !== false) ? 1 : 0;
    $h1 = (stripos($out, '<h1') !== false) ? 1 : 0;
    echo "$p: len=" . strlen($out) . " vp=$vp desc=$desc h1=$h1\n";
}
