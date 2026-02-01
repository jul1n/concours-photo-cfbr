<?php
// jury_verify.php
require 'db_connect.php';

session_start();

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (!$token) {
    die("Jeton manquant.");
}

// Check token in DB
$stmt = $pdo->prepare("SELECT t.id, t.jury_id, t.used_at, j.name, j.email 
                       FROM jury_tokens t 
                       JOIN jury_members j ON t.jury_id = j.id 
                       WHERE t.token = ?");
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenData) {
    die("Lien invalide.");
}

if ($tokenData['used_at']) {
    die("Ce lien a déjà été utilisé. Veuillez en demander un nouveau.");
}

// Mark as used (Tracking)
$now = date('Y-m-d H:i:s');
$updateStmt = $pdo->prepare("UPDATE jury_tokens SET used_at = ? WHERE id = ?");
$updateStmt->execute([$now, $tokenData['id']]);

// Login User
$_SESSION['jury_logged_in'] = true;
$_SESSION['jury_id'] = $tokenData['jury_id'];
$_SESSION['jury_name'] = $tokenData['name'];

// Redirect to jury space
header("Location: jury_tour1.php");
exit;
