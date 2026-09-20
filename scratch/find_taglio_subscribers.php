<?php
require_once __DIR__ . '/../bootstrap.php';

$pdo = db();
echo "DATABASE CONNESSO: " . DB_PATH . "\n\n";

$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo "TABELLE PRESENTI:\n" . implode(", ", $tables) . "\n\n";

// Cerca tabelle relative ad eventi o prenotazioni
foreach ($tables as $t) {
    if (stripos($t, 'event') !== false || stripos($t, 'book') !== false || stripos($t, 'order') !== false || stripos($t, 'user') !== false) {
        echo "=== CONTENUTO TABELLA: $t ===\n";
        $stmt = $pdo->query("SELECT * FROM $t LIMIT 20");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Totale righe trovate: " . count($rows) . "\n";
        foreach ($rows as $idx => $r) {
            echo "[$idx] " . json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "\n";
    }
}
