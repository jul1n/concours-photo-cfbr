<?php
// vote_tour1.php
require_once __DIR__ . '/../core/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photoId = intval($_POST['photo_id']);
    $value = $_POST['value']; // 'oui' or 'non'
    $ip = $_SERVER['REMOTE_ADDR'];

    try {
        // Enregistrer le vote
        // On pourrait vérifier si l'IP a déjà voté pour cette photo pour éviter doublons
        $stmt = $pdo->prepare("INSERT INTO votes_tour1 (photo_id, jury_ip, vote_value) VALUES (?, ?, ?)");
        $stmt->execute([$photoId, $ip, $value]);

        echo "OK";
    } catch (Exception $e) {
        http_response_code(500);
        echo "Error";
    }
}
?>