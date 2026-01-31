<?php
// init_db.php

$dbPath = __DIR__ . '/data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Table Participants
    $pdo->exec("CREATE TABLE IF NOT EXISTS participants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT,
        ip TEXT,
        signature_log TEXT,
        instagram_auth INTEGER DEFAULT 0,
        validation_token TEXT,
        is_verified INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Table Photos
    $pdo->exec("CREATE TABLE IF NOT EXISTS photos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        participant_id INTEGER,
        filename_original TEXT,
        filename_4k TEXT,
        filename_thumb TEXT,
        width INTEGER,
        height INTEGER,
        is_upscale_suspect INTEGER DEFAULT 0,
        status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (participant_id) REFERENCES participants(id)
    )");

    // Table Votes Tour 1 (Qualification)
    $pdo->exec("CREATE TABLE IF NOT EXISTS votes_tour1 (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER,
        jury_ip TEXT,
        vote_value TEXT CHECK(vote_value IN ('oui', 'non')),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id)
    )");

    // Table Votes Tour 2 (Classement)
    $pdo->exec("CREATE TABLE IF NOT EXISTS votes_tour2 (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER,
        jury_ip TEXT,
        rank INTEGER,
        points INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id)
    )");

    // Update Schema for existing tables
    $schemaUpdates = [
        "ALTER TABLE participants ADD COLUMN instagram_auth INTEGER DEFAULT 0",
        "ALTER TABLE participants ADD COLUMN validation_token TEXT",
        "ALTER TABLE participants ADD COLUMN is_verified INTEGER DEFAULT 0",
        "ALTER TABLE participants ADD COLUMN firstname TEXT",
        "ALTER TABLE participants ADD COLUMN lastname TEXT",
        "ALTER TABLE photos ADD COLUMN title TEXT",
        "ALTER TABLE photos ADD COLUMN description TEXT"
    ];

    foreach ($schemaUpdates as $update) {
        try {
            $pdo->exec($update);
        } catch (Exception $e) {
            // Ignore if column exists
        }
    }

    echo "Database initialized successfully at $dbPath";

} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
