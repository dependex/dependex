import urllib.request
import ftplib
import json

code = """<?php
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
"""

with open("scratch/test_remote_mail.php", "w") as f:
    f.write(code)

ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
with open("scratch/test_remote_mail.php", "rb") as f:
    ftp.storbinary("STOR test_remote_mail.php", f)
ftp.quit()

res = urllib.request.urlopen("https://dependex.social/test_remote_mail.php").read().decode()
print("Remote Hostinger mail() result:", res)

# Pulizia
ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
try:
    ftp.delete("test_remote_mail.php")
except:
    pass
ftp.quit()
