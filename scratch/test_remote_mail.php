<?php
$to = 'labomobile.lm@gmail.com';
$subject = 'Test DEPENDEX Monitoring H24 - Hostinger Mail';
$message = 'Infrastruttura DEPENDEX.SOCIAL / OLTRE.SOCIAL - Test Mail Server.';
$headers = [
    'From' => 'DEPENDEX <info@dependex.support>',
    'Reply-To' => 'info@dependex.support',
    'X-Mailer' => 'PHP/' . phpversion()
];
$ok = mail($to, $subject, $message, $headers);
echo json_encode(['ok' => $ok, 'error' => error_get_last()]);
