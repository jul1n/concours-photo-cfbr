<?php
require_once __DIR__ . '/core/db.php';
try {
    $q = $pdo->query("SELECT sql FROM sqlite_master WHERE type IN ('view', 'trigger')");
    $items = $q->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        echo $item['sql'] . "\n---\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
