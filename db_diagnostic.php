<?php require_once __DIR__ . '/core/auth.php'; require_maintenance(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Database Diagnostic Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include __DIR__ . '/includes/pwa_loader.php'; ?>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-red-600 mb-6">🔧 Database Diagnostic Tool</h1>

        <?php
        $dbPath = __DIR__ . '/data/concours.db';
        $backupDir = __DIR__ . '/data/backups/';

        // Create backup directory if it doesn't exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        echo "<div class='bg-blue-50 border-l-4 border-blue-500 p-4 mb-6'>";
        echo "<h2 class='font-bold text-blue-800 mb-2'>Database Information</h2>";
        echo "<p><strong>Path:</strong> " . htmlspecialchars($dbPath) . "</p>";
        echo "<p><strong>Exists:</strong> " . (file_exists($dbPath) ? '✓ Yes' : '✗ No') . "</p>";
        if (file_exists($dbPath)) {
            echo "<p><strong>Size:</strong> " . number_format(filesize($dbPath) / 1024, 2) . " KB</p>";
            echo "<p><strong>Last Modified:</strong> " . date('Y-m-d H:i:s', filemtime($dbPath)) . "</p>";
        }
        echo "</div>";

        // Handle actions
        if (isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'backup') {
                echo "<div class='bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6'>";
                echo "<h3 class='font-bold text-yellow-800 mb-2'>Creating Backup...</h3>";

                $backupFile = $backupDir . 'concours_backup_' . date('Y-m-d_H-i-s') . '.db';
                if (copy($dbPath, $backupFile)) {
                    echo "<p class='text-green-600'>✓ Backup created successfully!</p>";
                    echo "<p><strong>Location:</strong> " . htmlspecialchars($backupFile) . "</p>";
                } else {
                    echo "<p class='text-red-600'>✗ Failed to create backup!</p>";
                }
                echo "</div>";
            }

            if ($action === 'check') {
                echo "<div class='bg-purple-50 border-l-4 border-purple-500 p-4 mb-6'>";
                echo "<h3 class='font-bold text-purple-800 mb-2'>Running Integrity Check...</h3>";

                try {
                    $pdo = new PDO("sqlite:$dbPath");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $result = $pdo->query("PRAGMA integrity_check")->fetch(PDO::FETCH_COLUMN);

                    if ($result === 'ok') {
                        echo "<p class='text-green-600 font-bold'>✓ Database integrity is OK!</p>";
                        echo "<p class='mt-2'>The error might be transient. Try refreshing the maintenance page.</p>";
                    } else {
                        echo "<p class='text-red-600 font-bold'>✗ Integrity check failed!</p>";
                        echo "<pre class='mt-2 bg-gray-100 p-2 rounded'>" . htmlspecialchars($result) . "</pre>";
                    }
                } catch (Exception $e) {
                    echo "<p class='text-red-600 font-bold'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                echo "</div>";
            }

            if ($action === 'recreate') {
                echo "<div class='bg-red-50 border-l-4 border-red-500 p-4 mb-6'>";
                echo "<h3 class='font-bold text-red-800 mb-2'>Recreating Database...</h3>";

                // First, backup the old database
                $backupFile = $backupDir . 'concours_before_recreate_' . date('Y-m-d_H-i-s') . '.db';
                if (file_exists($dbPath)) {
                    copy($dbPath, $backupFile);
                    echo "<p>✓ Old database backed up to: " . basename($backupFile) . "</p>";
                    unlink($dbPath);
                }

                echo "<p class='text-green-600 mt-2'>✓ Database file deleted. Please go to the maintenance page to reinitialize.</p>";
                echo "<p class='mt-2'><a href='maintenance/?token=cfbr_repair_2026' class='text-blue-600 underline'>→ Go to Maintenance Page</a></p>";
                echo "</div>";
            }
        }
        ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <form method="POST" class="bg-blue-50 p-4 rounded">
                <h3 class="font-bold mb-2">1. Create Backup</h3>
                <p class="text-sm text-gray-600 mb-4">Always create a backup first!</p>
                <input type="hidden" name="action" value="backup">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    💾 Backup Now
                </button>
            </form>

            <form method="POST" class="bg-purple-50 p-4 rounded">
                <h3 class="font-bold mb-2">2. Check Integrity</h3>
                <p class="text-sm text-gray-600 mb-4">Run SQLite integrity check</p>
                <input type="hidden" name="action" value="check">
                <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                    🔍 Check Database
                </button>
            </form>

            <form method="POST" class="bg-red-50 p-4 rounded"
                onsubmit="return confirm('⚠️ This will DELETE the current database and create a fresh one. All data will be lost! Are you sure?');">
                <h3 class="font-bold mb-2">3. Recreate Database</h3>
                <p class="text-sm text-gray-600 mb-4">⚠️ Deletes all data!</p>
                <input type="hidden" name="action" value="recreate">
                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                    🔄 Recreate (DANGER)
                </button>
            </form>
        </div>

        <div class="bg-gray-50 p-4 rounded">
            <h3 class="font-bold mb-2">📋 Recommended Steps:</h3>
            <ol class="list-decimal list-inside space-y-2 text-sm">
                <li><strong>Create a backup</strong> - Always backup first!</li>
                <li><strong>Check integrity</strong> - See if the database can be repaired</li>
                <li>If integrity check fails:
                    <ul class="list-disc list-inside ml-6 mt-1">
                        <li>Option A: Restore from a previous backup (if available)</li>
                        <li>Option B: Recreate the database (loses all data)</li>
                    </ul>
                </li>
            </ol>
        </div>

        <?php
        // List existing backups
        if (is_dir($backupDir)) {
            $backups = glob($backupDir . '*.db');
            if (!empty($backups)) {
                echo "<div class='mt-6 bg-green-50 p-4 rounded'>";
                echo "<h3 class='font-bold mb-2'>📦 Existing Backups:</h3>";
                echo "<ul class='text-sm space-y-1'>";
                foreach ($backups as $backup) {
                    $size = number_format(filesize($backup) / 1024, 2);
                    $date = date('Y-m-d H:i:s', filemtime($backup));
                    echo "<li>• " . htmlspecialchars(basename($backup)) . " ({$size} KB, {$date})</li>";
                }
                echo "</ul>";
                echo "</div>";
            }
        }
        ?>
    </div>
</body>

</html>