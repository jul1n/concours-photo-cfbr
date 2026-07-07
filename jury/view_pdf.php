<?php
// jury_view_pdf.php
session_start();
if (!isset($_SESSION['jury_logged_in']) || $_SESSION['jury_logged_in'] !== true) {
    die("Accès refusé");
}

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/db.php';
require __DIR__ . '/../fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die("ID manquant");
}

$id = (int) $_GET['id'];

// Fetch participant
$stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
$stmt->execute([$id]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    die("Participant introuvable");
}

// Fetch photos for this participant
$stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE participant_id = ?");
$stmtPhotos->execute([$id]);
$photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

require_once(__DIR__ . '/../includes/PDFGenerator.php');

$pdfPath = get_participant_pdf_path($participant);

// 1. Try to serve stored PDF
if (file_exists($pdfPath)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Dossier_' . $participant['id'] . '.pdf"');
    header('Content-Length: ' . filesize($pdfPath));
    readfile($pdfPath);
    exit;
}

// 2. Fallback: Generate dynamically (Legacy or first-time view if not autosaved)
PDFGenerator::generateForParticipant($participant, $photos, 'I');
