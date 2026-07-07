<?php
// jury/api_notifications.php
header('Content-Type: application/json');
require_once __DIR__ . '/../core/auth.php';
require_jury(true); // 403 JSON si non authentifié
require_once __DIR__ . '/../core/db.php';

$juryId = $_SESSION['jury_email'] ?? $_SERVER['REMOTE_ADDR'];

try {
    // Total approved photos
    $stmtTotalP = $pdo->query("SELECT COUNT(*) FROM photos p JOIN participants part ON p.participant_id = part.id WHERE part.validation_status = 'approved' AND p.status = 'approved'");
    $totalApprovedPhotos = intval($stmtTotalP->fetchColumn());

    // Rated by this jury
    $stmtRatedP = $pdo->prepare("SELECT COUNT(DISTINCT photo_id) FROM jury_votes_analytics WHERE jury_identifier = ?");
    $stmtRatedP->execute([$juryId]);
    $ratedPhotos = intval($stmtRatedP->fetchColumn());

    $pendingNotation = max(0, $totalApprovedPhotos - $ratedPhotos);

    echo json_encode(['count_to_rate' => $pendingNotation]);
} catch (Exception $e) {
    echo json_encode(['count_to_rate' => 0]);
}
?>
