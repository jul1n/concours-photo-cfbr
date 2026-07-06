<?php
// admin/backup_all.php
session_start();
require_once __DIR__ . '/../core/db.php';

// Security Check
$isUnlocked = (isset($_SESSION['admin_unlocked']) && $_SESSION['admin_unlocked'] === true) || (isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true);
if (!$isUnlocked) {
    die("Accès refusé. Veuillez déverrouiller depuis la page de résultats.");
}
session_write_close();

$dbFile = __DIR__ . '/../data/concours.db';
$pdfDir = __DIR__ . '/../uploads/pdfs/';
$photoDir = __DIR__ . '/../photos/originals/';

// Fallback: Direct Database Download
if (isset($_GET['download_db']) && $_GET['download_db'] === '1') {
    if (file_exists($dbFile)) {
        header('Content-Type: application/x-sqlite3');
        header('Content-Disposition: attachment; filename="concours_backup_' . date('Y-m-d_Hi') . '.db"');
        header('Content-Length: ' . filesize($dbFile));
        readfile($dbFile);
        exit;
    } else {
        die("Fichier de base de données introuvable.");
    }
}

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
                <p class="text-amber-100 text-sm mt-1">L'extension PHP "zip" est requise pour générer l'archive globale.</p>
            </div>
            
            <div class="p-8">
                <h3 class="font-bold text-[#0A2240] mb-3 text-lg"><i class="fas fa-tools mr-2 text-amber-500"></i>Comment l'activer dans Laragon :</h3>
                <ol class="list-decimal list-inside space-y-2.5 text-gray-600 text-sm mb-8 border-b pb-6">
                    <li>Faites un <strong>clic droit</strong> sur l'icône de Laragon (dans la barre des tâches Windows).</li>
                    <li>Naviguez dans le menu : <strong>PHP</strong> &rarr; <strong>Extensions</strong>.</li>
                    <li>Cliquez sur <strong>zip</strong> dans la liste pour l'activer (une coche doit apparaître).</li>
                    <li>Cliquez sur <strong>"Reload"</strong> ou redémarrez les services de Laragon.</li>
                    <li>Actualisez cette page pour lancer le téléchargement de l'archive complète.</li>
                </ol>
                
                <div class="text-center">
                    <p class="text-gray-500 text-xs mb-4">Alternative immédiate (sauvegarde de la base de données seule contenant tous les votes, notations et classements) :</p>
                    <a href="backup_all.php?download_db=1" 
                       class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3.5 rounded-xl transition duration-300 shadow-md hover:shadow-lg gap-2 w-full">
                        <i class="fas fa-database text-lg"></i>
                        Télécharger la base de données (.db)
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Create ZIP
$zip = new ZipArchive();
$zipName = "Sauvegarde_CFBR_Concours_" . date('Y-m-d_Hi') . ".zip";
$tempZipPath = sys_get_temp_dir() . '/' . $zipName;

if ($zip->open($tempZipPath, ZipArchive::CREATE) !== TRUE) {
    die("Impossible de créer le fichier ZIP.");
}

// 1. Add Database
if (file_exists($dbFile)) {
    $zip->addFile($dbFile, 'database/concours.db');
}

// 2. Add PDFs
if (is_dir($pdfDir)) {
    $files = scandir($pdfDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $zip->addFile($pdfDir . $file, 'pdfs/' . $file);
        }
    }
}

// 3. Add Photos (Originals)
if (is_dir($photoDir)) {
    $files = scandir($photoDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'tiff', 'tif'])) {
                $zip->addFile($photoDir . $file, 'photos/' . $file);
            }
        }
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
    die("Erreur lors de la génération du fichier de sauvegarde.");
}
?>
