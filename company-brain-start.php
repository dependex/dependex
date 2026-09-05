<?php
declare(strict_types=1);
$root=__DIR__;
$steps=[];
function run_step(string $name,string $file,array &$steps): void {
    ob_start();
    try{require $file;$out=trim(ob_get_clean());$steps[]=['step'=>$name,'ok'=>true,'output'=>$out];}
    catch(Throwable $e){ob_end_clean();$steps[]=['step'=>$name,'ok'=>false,'error'=>$e->getMessage()];}
}
run_step('build_markdown',$root.'/knowledge/build.php',$steps);
run_step('ingest_markdown',$root.'/knowledge/ingest.php',$steps);
echo json_encode(['ok'=>!in_array(false,array_column($steps,'ok'),true),'steps'=>$steps],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
