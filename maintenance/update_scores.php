<?php
// update_db_scores.php
require_once __DIR__ . '/../core/db.php';

try {
    echo "Updating Database Schema for New Scoring System...\n";

    // 1. Create jury_votes_analytics table
    $sql = "CREATE TABLE IF NOT EXISTS jury_votes_analytics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER NOT NULL,
        jury_identifier TEXT NOT NULL, -- IP or User ID
        score_aesthetic DECIMAL(4,2), -- 1.0 to 10.0
        score_theme DECIMAL(4,2),     -- 1.0 to 10.0
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id),
        UNIQUE(photo_id, jury_identifier)
    )";
    $pdo->exec($sql);
    echo "Table 'jury_votes_analytics' created or already exists.\n";

    echo "Update Complete.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>