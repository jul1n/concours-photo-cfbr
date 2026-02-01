<?php
// fix_db_schema.php
require_once 'db_connect.php';

echo "<h1>Database Fix Tool</h1>";

try {
    // 1. Fix Participants Table (Add Address)
    echo "Checking 'participants' table...<br>";

    // Check if column exists (SQLite specific)
    $cols = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
    $hasAddress = false;
    foreach ($cols as $col) {
        if ($col['name'] === 'address') {
            $hasAddress = true;
            break;
        }
    }

    if (!$hasAddress) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN address TEXT");
        echo "<span style='color:green'>SUCCESS: 'address' column added to 'participants'.</span><br>";
    } else {
        echo "<span style='color:blue'>INFO: 'address' column already exists in 'participants'.</span><br>";
    }

    // 2. Fix Candidacy Type
    $hasCandidacyType = false;
    foreach ($cols as $col) {
        if ($col['name'] === 'candidacy_type') {
            $hasCandidacyType = true;
            break;
        }
    }
    if (!$hasCandidacyType) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN candidacy_type TEXT DEFAULT 'individual'");
        echo "<span style='color:green'>SUCCESS: 'candidacy_type' column added to 'participants'.</span><br>";
    } else {
        echo "<span style='color:blue'>INFO: 'candidacy_type' column already exists.</span><br>";
    }

    // 2.5 Fix Validation Status
    $hasValidationStatus = false;
    foreach ($cols as $col) {
        if ($col['name'] === 'validation_status') {
            $hasValidationStatus = true;
            break;
        }
    }
    if (!$hasValidationStatus) {
        $pdo->exec("ALTER TABLE participants ADD COLUMN validation_status TEXT DEFAULT 'pending'");
        echo "<span style='color:green'>SUCCESS: 'validation_status' column added to 'participants'.</span><br>";
    } else {
        echo "<span style='color:blue'>INFO: 'validation_status' column already exists.</span><br>";
    }

    // 3. Fix Photos Table (Add Location)
    echo "Checking 'photos' table...<br>";
    $colsPhotos = $pdo->query("PRAGMA table_info(photos)")->fetchAll(PDO::FETCH_ASSOC);
    $hasLocation = false;
    foreach ($colsPhotos as $col) {
        if ($col['name'] === 'location') {
            $hasLocation = true;
            break;
        }
    }
    if (!$hasLocation) {
        $pdo->exec("ALTER TABLE photos ADD COLUMN location TEXT");
        echo "<span style='color:green'>SUCCESS: 'location' column added to 'photos'.</span><br>";
    } else {
        echo "<span style='color:blue'>INFO: 'location' column already exists in 'photos'.</span><br>";
    }


    echo "<hr><h3>Done! Database patching completed.</h3>";
    echo "<p>You can now try submitting the form again.</p>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>