<?php
require_once __DIR__ . '/../bootstrap.php';

$pdo = db();
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "TABELLE IN " . DB_PATH . ":\n";
foreach ($tables as $t) {
    $count = $pdo->query("SELECT count(*) FROM $t")->fetchColumn();
    echo " - $t ($count righe)\n";
}
