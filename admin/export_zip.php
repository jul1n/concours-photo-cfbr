<?php
// admin_export_zip.php
session_start();
require_once __DIR__ . '/../core/db.php';

// Security Check
$isUnlocked = (isset($_SESSION['admin_unlocked']) && $_SESSION['admin_unlocked'] === true) || (isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true);
if (!$isUnlocked) {
    die("Accès refusé. Veuillez déverrouiller depuis la page de résultats.");
}
session_write_close();

// Check if ZipArchive extension is loaded
if (!class_exists('ZipArchive')) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Extension ZIP manquante</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
    </head>
    <body class="bg-gray-100 font-sans min-h-screen flex items-center justify-center p-4">
        <div class="max-w-xl w-full bg-white rounded-2xl shadow-xl border border-amber-100 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-8 text-center text-white">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-md mb-4">
                    <i class="fas fa-file-archive text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold">Extension ZIP non activée</h2>
                <p class="text-amber-100 text-sm mt-1">L'extension PHP "zip" est requise pour générer l'archive des photos.</p>
            </div>
            
            <div class="p-8">
                <h3 class="font-bold text-[#0A2240] mb-3 text-lg"><i class="fas fa-tools mr-2 text-amber-500"></i>Comment l'activer dans Laragon :</h3>
                <ol class="list-decimal list-inside space-y-2.5 text-gray-600 text-sm mb-8 border-b pb-6">
                    <li>Faites un <strong>clic droit</strong> sur l'icône de Laragon (dans la barre des tâches Windows).</li>
                    <li>Naviguez dans le menu : <strong>PHP</strong> &rarr; <strong>Extensions</strong>.</li>
                    <li>Cliquez sur <strong>zip</strong> dans la liste pour l'activer (une coche doit apparaître).</li>
                    <li>Cliquez sur <strong>"Reload"</strong> ou redémarrez les services de Laragon.</li>
                    <li>Actualisez cette page pour lancer le téléchargement.</li>
                </ol>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
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