<?php
// update_db_v3.php
require_once 'db_connect.php';

try {
    // Add candidacy_type to participants
    // We check if column exists first or just try/catch
    try {
        $pdo->exec("ALTER TABLE participants ADD COLUMN candidacy_type TEXT DEFAULT 'individual'");
        echo "Added candidacy_type to participants.<br>";
    } catch (Exception $e) {
        echo "Column candidacy_type likely exists or error: " . $e->getMessage() . "<br>";
    }

    try {
        $pdo->exec("ALTER TABLE participants ADD COLUMN address TEXT");
        echo "Added address to participants.<br>";
    } catch (Exception $e) {
        echo "Column address likely exists or error: " . $e->getMessage() . "<br>";
    }

    // Add location to photos
    try {
        $pdo->exec("ALTER TABLE photos ADD COLUMN location TEXT");
        echo "Added location to photos.<br>";
    } catch (Exception $e) {
        echo "Column location likely exists or error: " . $e->getMessage() . "<br>";
    }

    echo "Database update v3 complete.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>