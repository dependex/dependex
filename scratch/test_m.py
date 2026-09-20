import urllib.request
import ftplib

code = """<?php
$to = 'labomobile.lm@gmail.com';
$subject = 'Collaudo Monitoraggio H24 DEPENDEX';
$message = "Report Iniziale Monitoraggio H24 - Server Hostinger OK.";
$headers = "From: info@dependex.support\r\nReply-To: info@dependex.support\r\n";
$ok = @mail($to, $subject, $message, $headers);
echo json_encode(['ok' => $ok, 'time' => date('Y-m-d H:i:s')]);
"""

with open("scratch/m.php", "w") as f:
    f.write(code)

ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
with open("scratch/m.php", "rb") as f:
    ftp.storbinary("STOR m.php", f)
ftp.quit()

res = urllib.request.urlopen("https://dependex.social/m.php?t=1").read().decode()
print("Result:", res)

ftp = ftplib.FTP("ftp.dependex.social", "u173050672.dependex.social", "h29031976T.")
ftp.set_pasv(True)
try:
    ftp.delete("m.php")
except:
    pass
ftp.quit()
