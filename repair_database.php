<?php
require_once __DIR__ . '/core/auth.php';
require_maintenance();
/**
 * Database Repair Script
 * Attempts to fix SQLite database corruption by:
 * 1. Creating a backup of the corrupted database
 * 2. Attempting to dump and restore the database
 * 3. If that fails, creating a fresh database and migrating data
 */

$dbPath = __DIR__ . '/data/concours.db';
$backupPath = __DIR__ . '/data/concours_backup_' . date('Y-m-d_H-i-s') . '.db';

echo "=== SQLite Database Repair Tool ===\n\n";

// Step 1: Create backup
echo "1. Creating backup...\n";
if (file_exists($dbPath)) {
    if (copy($dbPath, $backupPath)) {
        echo "   ✓ Backup created: " . basename($backupPath) . "\n\n";
    } else {
        die("   ✗ Failed to create backup!\n");
    }
} else {
    die("   ✗ Database file not found at: $dbPath\n");
}

// Step 2: Attempt to dump and restore
echo "2. Attempting to dump and restore database...\n";
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Try to run integrity check
    echo "   - Running integrity check...\n";
    $result = $pdo->query("PRAGMA integrity_check")->fetch(PDO::FETCH_COLUMN);

    if ($result === 'ok') {
        echo "   ✓ Database integrity is OK! No corruption detected.\n";
        echo "   The error might be transient. Try the maintenance page again.\n";
        exit(0);
    } else {
        echo "   ✗ Integrity check failed: $result\n";
    }

    // Attempt to dump to SQL
    echo "   - Attempting to dump database to SQL...\n";
    $dumpPath = __DIR__ . '/data/dump_' . date('Y-m-d_H-i-s') . '.sql';
    $dumpFile = fopen($dumpPath, 'w');

    // Get all table names
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        try {
            // Get CREATE statement
            $createStmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetch(PDO::FETCH_COLUMN);
            fwrite($dumpFile, "$createStmt;\n\n");

            // Get all data
            $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $columns = implode(', ', array_keys($row));
                $values = implode(', ', array_map(function ($v) use ($pdo) {
                    return $pdo->quote($v);
                }, array_values($row)));
                fwrite($dumpFile, "INSERT INTO $table ($columns) VALUES ($values);\n");
            }
            fwrite($dumpFile, "\n");
            echo "   ✓ Dumped table: $table\n";
        } catch (Exception $e) {
            echo "   ⚠ Warning: Could not dump table '$table': " . $e->getMessage() . "\n";
        }
    }

    fclose($dumpFile);
    echo "   ✓ SQL dump created: " . basename($dumpPath) . "\n\n";

    // Create new database from dump
    echo "3. Creating new database from dump...\n";
    $newDbPath = __DIR__ . '/data/concours_repaired.db';
    if (file_exists($newDbPath)) {
        unlink($newDbPath);
    }

    $newPdo = new PDO("sqlite:$newDbPath");
    $newPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents($dumpPath);
    $newPdo->exec($sql);

    echo "   ✓ New database created: concours_repaired.db\n\n";

    echo "=== Repair Complete ===\n\n";
    echo "Next steps:\n";
    echo "1. Rename 'concours.db' to 'concours_old.db'\n";
    echo "2. Rename 'concours_repaired.db' to 'concours.db'\n";
    echo "3. Test the maintenance page again\n";
    echo "\nBackup location: " . basename($backupPath) . "\n";

} catch (Exception $e) {
    echo "\n✗ Repair failed: " . $e->getMessage() . "\n\n";
    echo "The database is severely corrupted. Options:\n";
    echo "1. Restore from a previous backup if available\n";
    echo "2. Create a fresh database (will lose all data)\n";
    echo "3. Contact support with the backup file: " . basename($backupPath) . "\n";
}
