<?php
// fix_jury_schema.php
$dbPath = __DIR__ . '/data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Analyzing database schema at $dbPath...\n<br>";

    // Get existing columns
    $result = $pdo->query("PRAGMA table_info(participants)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'name');

    $updates = [
        'validation_status' => "ALTER TABLE participants ADD COLUMN validation_status TEXT DEFAULT 'pending'",
        'jury_vote_1_by' => "ALTER TABLE participants ADD COLUMN jury_vote_1_by INTEGER",
        'jury_vote_2_by' => "ALTER TABLE participants ADD COLUMN jury_vote_2_by INTEGER"
    ];

    foreach ($updates as $col => $sql) {
        if (!in_array($col, $existingColumns)) {
            try {
                $pdo->exec($sql);
                echo "✅ Column '$col' added successfully.<br>\n";
            } catch (Exception $e) {
                echo "❌ Error adding column '$col': " . $e->getMessage() . "<br>\n";
            }
        } else {
            echo "ℹ️ Column '$col' already exists.<br>\n";
        }
    }

    echo "<br>Done. You can delete this file now.";

} catch (Exception $e) {
    die("Critical Error: " . $e->getMessage());
}
?>