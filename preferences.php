<?php
// Reindirizzamento permanente verso il centro preferenze
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: /email/preferences.php' . $qs, true, 301);
exit;
