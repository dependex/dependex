<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
if (!isset($pdo)) {
    $pdo = db();
}
