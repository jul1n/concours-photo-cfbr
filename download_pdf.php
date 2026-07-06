<?php
// download_pdf.php
require_once __DIR__ . '/core/db.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Token manquant.");
}

$stmt = $pdo->prepare("SELECT id FROM participants WHERE validation_token = ? LIMIT 1");
$stmt->execute([$token]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    die("Accès non autorisé.");
}

$filePath = __DIR__ . "/uploads/pdfs/agreement_" . $p['id'] . ".pdf";

if (file_exists($filePath)) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Ma participation au concours du Cfbr.pdf"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    die("Le PDF de participation n'a pas encore été généré. Veuillez valider votre e-mail.");
}
