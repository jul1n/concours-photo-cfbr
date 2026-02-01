<?php
// init_db.php
require_once 'db_connect.php';

try {
    // $pdo is available from db_connect.php

    // DROP Tables for a Clean Reset (Requested by User)
    $pdo->exec("DROP TABLE IF EXISTS votes_tour2");
    $pdo->exec("DROP TABLE IF EXISTS votes_tour1");
    $pdo->exec("DROP TABLE IF EXISTS photos");
    $pdo->exec("DROP TABLE IF EXISTS participants");
    $pdo->exec("DROP TABLE IF EXISTS users"); // If admins exist here

    // Table Participants
    $pdo->exec("CREATE TABLE IF NOT EXISTS participants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        firstname TEXT,
        lastname TEXT,
        email TEXT,
        address TEXT,
        ip TEXT,
        candidacy_type TEXT DEFAULT 'individual',
        company TEXT,
        signature_log TEXT,
        instagram_auth INTEGER DEFAULT 0,
        agree_annex_a INTEGER DEFAULT 0,
        agree_annex_b INTEGER DEFAULT 0,
        validation_token TEXT,
        is_verified INTEGER DEFAULT 0,
        validation_status TEXT DEFAULT 'pending',
        jury_vote_1_by INTEGER,
        jury_vote_2_by INTEGER,
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
        title TEXT,
        description TEXT,
        category TEXT,
        location TEXT,
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
        "ALTER TABLE participants ADD COLUMN company TEXT",
        "ALTER TABLE participants ADD COLUMN agree_annex_a INTEGER DEFAULT 0",
        "ALTER TABLE participants ADD COLUMN agree_annex_b INTEGER DEFAULT 0",
        "ALTER TABLE photos ADD COLUMN title TEXT",
        "ALTER TABLE photos ADD COLUMN description TEXT",
        "ALTER TABLE photos ADD COLUMN category TEXT",
        "CREATE TABLE IF NOT EXISTS jury_members (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT)",
        "CREATE TABLE IF NOT EXISTS jury_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, jury_id INTEGER, token TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, used_at DATETIME, FOREIGN KEY (jury_id) REFERENCES jury_members(id))",
        // Jury Validation Columns
        "ALTER TABLE participants ADD COLUMN validation_status TEXT DEFAULT 'pending'",
        "ALTER TABLE participants ADD COLUMN jury_vote_1_by INTEGER",
        "ALTER TABLE participants ADD COLUMN jury_vote_2_by INTEGER"
    ];

    foreach ($schemaUpdates as $update) {
        try {
            $pdo->exec($update);
        } catch (Exception $e) {
            // Ignore if column exists
        }
    }

    // Default Jury Member
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM jury_members WHERE email = ?");
    $stmt->execute(['julien.houssin@cfe-energies.com']);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO jury_members (name, email) VALUES (?, ?)");
        $stmt->execute(['Julien HOUSSIN', 'julien.houssin@cfe-energies.com']);
        echo "Default jury member added.\n";
    }

    echo "Database initialized successfully at $dbPath";

} catch (PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
