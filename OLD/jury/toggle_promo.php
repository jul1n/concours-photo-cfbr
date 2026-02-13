<?php
// jury/toggle_promo.php
require_once __DIR__ . '/../core/db.php';
session_start();

if (!isset($_SESSION['jury_logged_in']) || $_SESSION['jury_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$photoId = $_POST['photo_id'] ?? 0;

if ($photoId) {
    try {
        // Get current state
        $stmt = $pdo->prepare("SELECT is_promo FROM photos WHERE id = ?");
        $stmt->execute([$photoId]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($photo) {
            $newState = $photo['is_promo'] ? 0 : 1;
            $update = $pdo->prepare("UPDATE photos SET is_promo = ? WHERE id = ?");
            $update->execute([$newState, $photoId]);

            echo json_encode(['status' => 'success', 'is_promo' => $newState]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
