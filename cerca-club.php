<?php
// Alias permanente: reindirizzamento al motore ufficiale di ricerca Club
$qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("HTTP/1.1 301 Moved Permanently");
header("Location: /world-club-explorer.php" . $qs);
exit;
