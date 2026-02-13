<?php
require_once __DIR__ . '/core/db.php';
$tables = ['participants', 'photos', 'jury_members', 'jury_tokens', 'jury_votes_analytics'];
foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    try {
        $q = $pdo->query("PRAGMA table_info($table)");
        $cols = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo "Col: " . $c['name'] . " (" . $c['type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
