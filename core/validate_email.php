<?php
// validate.php
// Increase memory limit for PDF generation and email sending
ini_set('memory_limit', '512M');

require_once __DIR__ . '/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier le token
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE validation_token = ?");
    $stmt->execute([$token]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($participant) {
        // Valider
        $updateFor = $pdo->prepare("UPDATE participants SET is_verified = 1 WHERE id = ?");
        $updateFor->execute([$participant['id']]);

        // Fetch photos for this participant
        $stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE participant_id = ?");
        $stmtPhotos->execute([$participant['id']]);
        $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

        // --- NEW PDF CLASS USAGE (Refactored) ---
        require_once(__DIR__ . '/../includes/PDFGenerator.php');

        // Ensure directory exists for persistent storage
        $pdfDir = __DIR__ . '/../uploads/pdfs/';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        $pdfPath = $pdfDir . 'agreement_' . $participant['id'] . '.pdf';

        // 1. Generate for persistent storage AND get output
        $pdfOutput = PDFGenerator::generateForParticipant($participant, $photos, 'S');
        file_put_contents($pdfPath, $pdfOutput);

        // --- EMAIL WITH ATTACHMENT START ---
        // Fetch full email if needed
        $stmtEmail = $pdo->prepare("SELECT email FROM participants WHERE id = ?");
        $stmtEmail->execute([$participant['id']]);
        $userEmail = $stmtEmail->fetchColumn();

        $to = $userEmail;
        $subject = "Confirmation Inscription et Signature Reglement - Concours CFBR";
        $from = "no-reply@barrages-cfbr.eu";
        $boundary = md5(time());

        // Headers
        $headers = "From: $from\r\n";
        $headers .= "Cc: concoursphoto2026@barrages-cfbr.eu\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        $dossierLink = "http://" . $_SERVER['HTTP_HOST'] . str_replace('/core', '', dirname($_SERVER['PHP_SELF'])) . "/dossier.php?token=" . $participant['validation_token'];
        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= "Bonjour " . $participant['firstname'] . " " . $participant['lastname'] . ",\n\n";
        $message .= "Votre inscription est validée avec succès !\n\n";
        $message .= "Vous pouvez désormais consulter votre dossier et voir vos photos déposées via le lien suivant :\n";
        $message .= $dossierLink . "\n\n";
        $message .= "Veuillez trouver ci-joint votre règlement signé servant de preuve de dépôt.\n\n";
        $message .= "Cordialement,\nle comité d'organisation du concours photo des 100 ans du Cfbr\r\n";

        // Attachment
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: application/pdf; name=\"Ma participation au concours du Cfbr.pdf\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"Ma participation au concours du Cfbr.pdf\"\r\n\r\n";
        $message .= chunk_split(base64_encode($pdfOutput)) . "\r\n";
        $message .= "--$boundary--";

        @mail($to, $subject, $message, $headers);
        // --- EMAIL END ---

        ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Validation Confirmée</title>
            <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>

        <body class="bg-gray-100 flex items-center justify-center h-screen">
            <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md">
                <div class="text-green-500 text-6xl mb-4">
                    <i class="fas fa-check-circle"></i> ✓
                </div>
                <h1 class="text-2xl font-bold text-[#0A2240] mb-2">Inscription Validée !</h1>
                <p class="text-gray-600 mb-6">Merci <strong>
                        <?= htmlspecialchars($participant['firstname'] . ' ' . $participant['lastname']) ?>
                    </strong>. Votre signature électronique est maintenant confirmée.</p>
                <div class="flex flex-col space-y-3">
                    <a href="../dossier.php?token=<?= $participant['validation_token'] ?>"
                        class="bg-[#FF9900] text-[#0A2240] px-6 py-3 rounded-full font-bold hover:bg-[#0A2240] hover:text-white transition-colors shadow-lg">
                        <i class="fas fa-folder-open mr-2"></i> Consulter mon dossier
                    </a>
                    <a href="../index.php" class="text-gray-400 hover:text-[#0A2240] text-sm transition-colors">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </body>

        </html>
        <?php
    } else {
        echo "<h1 style='color:red;'>Token invalide ou expiré.</h1>";
    }
} else {
    echo "Aucun token fourni.";
}
?>