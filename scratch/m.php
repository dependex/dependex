<?php
$to = 'labomobile.lm@gmail.com';
$subject = 'Collaudo Monitoraggio H24 DEPENDEX';
$message = "Report Iniziale Monitoraggio H24 - Server Hostinger OK.";
$headers = "From: info@dependex.support
Reply-To: info@dependex.support
";
$ok = @mail($to, $subject, $message, $headers);
echo json_encode(['ok' => $ok, 'time' => date('Y-m-d H:i:s')]);
