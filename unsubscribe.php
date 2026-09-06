<?php
// Reindirizzamento permanente verso l'endpoint conforme RFC 8058
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /email/unsubscribe.php' . $qs, true, 301);
exit;
