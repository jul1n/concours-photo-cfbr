<?php
// db_connect.php
$dbPath = __DIR__ . '/data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Silent success - no echo here to avoid headers sent errors
} catch (PDOException $e) {
    // In production, log this instead of showing user
    die("Erreur de connexion base de données: " . $e->getMessage());
}
