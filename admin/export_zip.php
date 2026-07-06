<?php
// admin_export_zip.php
session_start();
require_once __DIR__ . '/../core/db.php';

// Security Check
$isUnlocked = (isset($_SESSION['admin_unlocked']) && $_SESSION['admin_unlocked'] === true) || (isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true);
if (!$isUnlocked) {
    die("Accès refusé. Veuillez déverrouiller depuis la page de résultats.");
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if ($limit <= 0)
    $limit = 10;

// Fetch Top N
try {
    $sql = "
        SELECT p.*, SUM(v.points) as total_points, pa.firstname, pa.lastname
        FROM photos p
        JOIN votes_tour2 v ON p.id = v.photo_id
        JOIN participants pa ON p.participant_id = pa.id
        GROUP BY p.id
        ORDER BY total_points DESC
        LIMIT $limit
    ";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur DB");
}

if (empty($results)) {
    die("Aucun résultat à exporter.");
}

// Create ZIP
$zip = new ZipArchive();
$zipName = "Export_Top_$limit" . "_ConcoursCFBR_" . date('Y-m-d_Hi') . ".zip";
$tempZipPath = sys_get_temp_dir() . '/' . $zipName;

if ($zip->open($tempZipPath, ZipArchive::CREATE) !== TRUE) {
    die("Impossible de créer le fichier ZIP.");
}

foreach ($results as $index => $row) {
    $rank = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
    $firstname = preg_replace('/[^a-zA-Z0-9]/', '', $row['firstname']); // Sanitize
    $lastname = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($row['lastname']));

    // Clean Title (limit length)
    $cleanTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['title'] ?: 'SansTitre');
    $cleanTitle = substr($cleanTitle, 0, 30);

    $originalPath = __DIR__ . '/../photos/originals/' . $row['filename_original'];
    $ext = pathinfo($originalPath, PATHINFO_EXTENSION);

    // New Name: 001_Marie_CURIE_Titre.jpg
    $newName = "{$rank}_{$firstname}_{$lastname}_{$cleanTitle}.$ext";

    if (file_exists($originalPath)) {
        $zip->addFile($originalPath, $newName);
    } else {
        // Fallback for missing file
        $zip->addFromString("MISSING_{$newName}.txt", "Fichier original introuvable sur le serveur.");
    }
}

$zip->close();

// Stream Download
if (file_exists($tempZipPath)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($tempZipPath));
    readfile($tempZipPath);
    unlink($tempZipPath); // Cleanup
    exit;
} else {
    die("Erreur lors de la génération du ZIP.");
}
?>