<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'dependex.social';
$_SERVER['REQUEST_URI'] = '/world-network-tree.php';
$_SERVER['SCRIPT_NAME'] = '/world-network-tree.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) Mobile/15E148';
$_GET = ['id' => '1', 'code' => 'TEST', 'slug' => 'test-slug', 'token' => 'TEST_TOKEN', 'sic' => 'SIC-TAGLIODIPO-RO-001', 'q' => 'Rovigo', 'event' => 'SIC-EVT-ACAT-BP-2026-COMM'];
$_POST = [];
session_start();
// Authenticated user session for admin/member pages, guest for login/register
if (in_array('world-network-tree.php', ['login.php', 'register.php'])) {
    $_SESSION = [];
} else {
    $_SESSION['user_sic_id'] = 'SIC-ADM00001-DEPENDEX-9';
}

register_shutdown_function(function() {
    $err = error_get_last();
    $out = ob_get_contents();
    @ob_end_clean();
    
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "FATAL|" . $err['message'] . "|" . $err['file'] . ":" . $err['line'];
        return;
    }
    
    $len = strlen($out);
    $title = 'NO_TITLE';
    if (preg_match('#<title>(.*?)</title>#is', $out, $m)) {
        $title = trim($m[1]);
    }
    $hasVp = (stripos($out, 'name="viewport"') !== false || stripos($out, "name='viewport'") !== false) ? '1' : '0';
    $hasDesc = (stripos($out, 'name="description"') !== false || stripos($out, "name='description'") !== false) ? '1' : '0';
    $hasH1 = (stripos($out, '<h1') !== false) ? '1' : '0';
    $hasHtml = (stripos($out, '<!doctype html') !== false || stripos($out, '<html') !== false) ? '1' : '0';
    
    echo "AUDIT_RESULT|" . $len . "|" . $title . "|" . $hasVp . "|" . $hasDesc . "|" . $hasH1 . "|" . $hasHtml;
});

ob_start();
try {
    require __DIR__ . '/../world-network-tree.php';
} catch (Throwable $e) {
    @ob_end_clean();
    echo "FATAL|" . $e->getMessage() . "|" . $e->getFile() . ":" . $e->getLine();
    exit(1);
}
