<?php
// Compatibility Shim
$r = __DIR__;
while(!file_exists($r.'/bootstrap.php') && dirname($r)!==$r){$r=dirname($r);}
if(file_exists($r.'/bootstrap.php')){require_once $r.'/bootstrap.php';}
