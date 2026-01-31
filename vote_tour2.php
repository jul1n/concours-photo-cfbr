<?php
// vote_tour2.php
$dbPath = __DIR__ . '/data/concours.db';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rankingOrder = json_decode($_POST['ranking_order'], true);
    $ip = $_SERVER['REMOTE_ADDR'];

    if (!$rankingOrder)
        die("Erreur de données");

    try {
        $pdo = new PDO("sqlite:$dbPath");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Barème simple : 1er = 10 pts, 2e = 9 pts... 10e = 1 pt.
        $pointsMap = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

        // Supprimer l'ancien vote de ce juré s'il recommence (pour la démo)
        $msg = "Vote enregistré !";

        // Transaction
        $pdo->beginTransaction();

        foreach ($rankingOrder as $index => $photoId) {
            $rank = $index + 1;
            $points = isset($pointsMap[$index]) ? $pointsMap[$index] : 0;

            $stmt = $pdo->prepare("INSERT INTO votes_tour2 (photo_id, jury_ip, rank, points) VALUES (?, ?, ?, ?)");
            $stmt->execute([$photoId, $ip, $rank, $points]);
        }

        $pdo->commit();

        echo "<h1>$msg</h1><p>Merci pour votre classement.</p><a href='admin_results.php'>Voir les résultats provisoires</a>";

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur DB : " . $e->getMessage());
    }
}
?>