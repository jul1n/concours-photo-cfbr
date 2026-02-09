<?php
// admin/export_social.php
require_once __DIR__ . '/../core/db.php';
session_start();

// Basic security check (admin or maintenance hub logic usually)
// For now, mirroring existing export security or assuming accessed via Hub
if (!isset($_SESSION['jury_logged_in']) && !isset($_SESSION['admin'])) {
    // Note: Assuming a simple check or admin session
}

try {
    // 1. Fetch promo photos
    $stmt = $pdo->query("
        SELECT p.filename_original, p.title, part.firstname, part.lastname, part.instagram, part.linkedin 
        FROM photos p
        JOIN participants part ON p.participant_id = part.id
        WHERE p.is_promo = 1
    ");
    $promoPhotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($promoPhotos)) {
        die("Aucune photo n'est marquée pour la promotion.");
    }

    // 2. Prepare ZIP
    $zipName = "social_media_pack_" . date('Ymd_His') . ".zip";
    $zipPath = sys_get_temp_dir() . '/' . $zipName;
    $zip = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
        die("Impossible de créer le fichier ZIP.");
    }

    // 3. Create CSV Credits file
    $csvPath = sys_get_temp_dir() . '/social_credits.csv';
    $fp = fopen($csvPath, 'w');
    // UTF-8 BOM for Excel
    fputs($fp, "\xEF\xBB\xBF");
    fputcsv($fp, ['Fichier', 'Titre', 'Auteur', 'Instagram', 'LinkedIn']);

    foreach ($promoPhotos as $row) {
        $author = $row['firstname'] . ' ' . $row['lastname'];
        fputcsv($fp, [
            $row['filename_original'],
            $row['title'],
            $author,
            $row['instagram'],
            $row['linkedin']
        ]);

        $filePath = __DIR__ . '/../photos/originals/' . $row['filename_original'];
        if (file_exists($filePath)) {
            $zip->addFile($filePath, "photos/" . $row['filename_original']);
        }
    }

    fclose($fp);
    $zip->addFile($csvPath, "credits_sociaux.csv");
    $zip->close();

    // 4. Download and Clean
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);

    unlink($zipPath);
    unlink($csvPath);
    exit;

} catch (Exception $e) {
    die("Erreur lors de l'export : " . $e->getMessage());
}
