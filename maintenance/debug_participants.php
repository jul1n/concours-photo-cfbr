<?php
require_once __DIR__ . '/../core/db.php';

try {
    $sql = "
        SELECT p.id as photo_id, p.title, p.participant_id, part.validation_status, part.firstname, part.lastname
        FROM photos p
        LEFT JOIN participants part ON p.participant_id = part.id
    ";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Photo / Participant Debug</h1>";
    echo "<table border='1'><tr><th>Photo ID</th><th>Title</th><th>Part ID</th><th>Status</th><th>Name</th></tr>";
    foreach ($results as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars($cell ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
