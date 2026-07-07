<?php
// db.php — Connexion PDO SQLite + réglages de performance.
date_default_timezone_set('Europe/Paris');

$dbPath = __DIR__ . '/../data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- Réglages performance / intégrité ---
    // WAL : autorise les lectures concurrentes pendant une écriture (jury + public).
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA synchronous = NORMAL');
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000'); // attend 5s au lieu d'échouer si verrou
} catch (PDOException $e) {
    // Ne jamais exposer le détail technique à l'utilisateur.
    error_log('[DB] ' . $e->getMessage());
    http_response_code(500);
    die('Le service est momentanément indisponible. Merci de réessayer plus tard.');
}
