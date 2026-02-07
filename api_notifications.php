<?php
// api_notifications.php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['jury_logged_in'])) {
    echo json_encode(['count_to_rate' => 0]);
    exit;
}

$juryId = $_SESSION['jury_email'] ?? $_SESSION['jury_name'] ?? $_SERVER['REMOTE_ADDR'];
// If jury is identified by NAME or IP in the old system, ensure we map consistent IDs in DB
// (Ideally we use a unique ID, but we follow current logic)

try {
    // 1. Count Total Approved Photos (Available for rating)
    $sqlTotal = "SELECT COUNT(*) FROM participants JOIN photos ON participants.id = photos.participant_id WHERE validation_status = 'approved'";
    $totalApproved = $pdo->query($sqlTotal)->fetchColumn();

    // 2. Count My Votes (Photos I have rated)
    $sqlMyVotes = "SELECT COUNT(*) FROM jury_votes_analytics WHERE jury_identifier = ?";
    $stmt = $pdo->prepare($sqlMyVotes);
    $stmt->execute([$juryId]);
    $myVotes = $stmt->fetchColumn();

    $toRate = $totalApproved - $myVotes;
    if ($toRate < 0)
        $toRate = 0;

    echo json_encode([
        'count_to_rate' => $toRate,
        'total_qualified' => $totalApproved
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>