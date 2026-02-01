<?php
$dbPath = __DIR__ . '/data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $result = $pdo->query("PRAGMA table_info(participants)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    $exists = false;
    foreach ($columns as $col) {
        if ($col['name'] === 'identifiable_persons') {
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN identifiable_persons TEXT DEFAULT ''");
        echo "Column 'identifiable_persons' added successfully.\n";
    } else {
        echo "Column 'identifiable_persons' already exists.\n";
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>