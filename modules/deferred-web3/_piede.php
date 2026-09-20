<?php
declare(strict_types=1);
$root = __DIR__;
while (!file_exists($root . '/_footer.php') && dirname($root) !== $root) {
    $root = dirname($root);
}
if (file_exists($root . '/_footer.php')) {
    require_once $root . '/_footer.php';
}
