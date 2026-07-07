<?php
// jury/toggle_promo.php
require_once __DIR__ . '/../core/auth.php';
require_jury(true); // 403 JSON si non authentifié
require_once __DIR__ . '/../core/db.php';

header('Content-Type: application/json');
csrf_check();

$photoId = intval($_POST['photo_id'] ?? 0);

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
        error_log('[toggle_promo] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Erreur interne']);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
