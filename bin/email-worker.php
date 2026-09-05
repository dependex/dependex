<?php
require_once dirname(__DIR__).'/bootstrap.php';
$from=getenv('MAIL_FROM') ?: 'noreply@oltre.social';$rows=db()->query("SELECT * FROM email_queue WHERE status='PENDING' AND scheduled_at<=CURRENT_TIMESTAMP ORDER BY id LIMIT 50")->fetchAll();foreach($rows as $r){$headers=['MIME-Version: 1.0','Content-Type: text/html; charset=UTF-8','From: OLTRE <'.$from.'>'];$ok=@mail($r['to_email'],$r['subject'],$r['html_body'],implode("\r\n",$headers));if($ok)db()->prepare("UPDATE email_queue SET status='SENT',sent_at=CURRENT_TIMESTAMP,attempts=attempts+1 WHERE id=?")->execute([$r['id']]);else db()->prepare("UPDATE email_queue SET status='ERROR',attempts=attempts+1,last_error='mail() failed' WHERE id=?")->execute([$r['id']]);}
echo 'processed '.count($rows).PHP_EOL;
