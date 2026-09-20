<?php
declare(strict_types=1);
$root = __DIR__;
while (!file_exists($root . '/_header.php') && dirname($root) !== $root) {
    $root = dirname($root);
}
if (file_exists($root . '/_header.php')) {
    require_once $root . '/_header.php';
}
